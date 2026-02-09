<?php

namespace App\Http\Controllers;

use App\Models\BirthdayWishLog;
use App\Services\WhatApiWhatsappService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;


class BirthdayWishLogController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();

        $q = BirthdayWishLog::query()
            ->latest('id');

        // 🔐 ROLE BASED ACCESS
        if (!$user->hasRole('super admin')) {
            $q->whereHas('record', function ($qr) use ($user) {
                $qr->where('user_id', $user->id);
            });
        }

        // ================= FILTERS =================

        if ($request->filled('business_id')) {
            $q->where('business_id', $request->business_id);
        }

        if ($request->filled('birthday_record_id')) {
            $q->where('birthday_record_id', $request->birthday_record_id);
        }

        if ($request->filled('phone')) {
            $q->where('phone', 'like', '%'.trim($request->phone).'%');
        }

        if ($request->filled('status')) {
            $q->where('status', $request->status); // pending/success/failed
        }

        // 📅 wish_date filter
        $from = $request->filled('from') ? Carbon::parse($request->from)->toDateString() : null;
        $to   = $request->filled('to')   ? Carbon::parse($request->to)->toDateString()   : null;

        if ($from && $to) {
            $q->whereBetween('wish_date', [$from, $to]);
        } elseif ($from) {
            $q->whereDate('wish_date', '>=', $from);
        } elseif ($to) {
            $q->whereDate('wish_date', '<=', $to);
        }

        if ($request->filled('wish_year')) {
            $q->where('wish_year', (int)$request->wish_year);
        }

        // pagination
        $perPage = max(1, min(200, (int)$request->get('per_page', 50)));

        $logs = $q->paginate($perPage)->withQueryString();

        return view('birthday_wish_logs.index', compact('logs'));
    }


    public function resend(BirthdayWishLog $birthdayWishLog)
    {
        dd($birthdayWishLog);
        try {
            // relations load (avoid null issues)
            $birthdayWishLog->loadMissing('birthdayRecord.user.api');

            // webhook url
            $url = optional(optional(optional($birthdayWishLog->birthdayRecord)->user)->api)->wishes_api;
        

            if (!$url) {
                return back()->with('success', 'Resend failed ❌ (Webhook URL missing)');
            }

            // ✅ Basic sanitation (only digits)
            $to = preg_replace('/\D+/', '', (string)$birthdayWishLog->phone);

            // If 10 digit => add 91 (India)
            if (strlen($to) === 10) $to = '91' . $to;

            // ✅ Video public URL
            $videoUrl = url(Storage::url('videos/birthday-wish.mp4'));

            // ✅ Payload
            $payload = [
                'number' => $to,
                'Video'  => $videoUrl,
            ];

            Log::info('WA RESEND REQ', [
                'log_id' => $birthdayWishLog->id,
                'url' => $url,
                'payload' => $payload,
            ]);

            $res = Http::timeout(60)->acceptJson()->post($url, $payload);

            Log::info('WA RESEND RES', [
                'log_id' => $birthdayWishLog->id,
                'status' => $res->status(),
                'body'   => $res->body(),
            ]);

            // ✅ Update existing log only (NO create)
            $birthdayWishLog->status = $res->successful() ? 'success' : 'failed';

            // save response fields only if columns exist
            if (Schema::hasColumn($birthdayWishLog->getTable(), 'response_body')) {
                $birthdayWishLog->response_body = $res->body();
            }
            if (Schema::hasColumn($birthdayWishLog->getTable(), 'response_status')) {
                $birthdayWishLog->response_status = $res->status();
            }

            $birthdayWishLog->save();

            return back()->with('success', $res->successful() ? 'Resent successfully ✅' : 'Resend failed ❌');

        } catch (\Throwable $e) {

            Log::error('Birthday resend error', [
                'log_id' => $log->id ?? null,
                'err' => $e->getMessage(),
            ]);

            // Existing log update
            $log->status = 'failed';
            $log->save();

            return back()->with('success', 'Resend failed ❌ (exception)');
        }
    }


    /**
     * GET /birthday-wish-logs/{log}
     * single log details
     */
    public function show(BirthdayWishLog $birthdayWishLog)
    {
        // relations load if needed:
        // $birthdayWishLog->load(['birthdayRecord', 'business']);

        // JSON:
        // return response()->json($birthdayWishLog);

        return view('birthday_wish_logs.show', ['log' => $birthdayWishLog]);
    }

    /**
     * POST /birthday-wish-logs
     * create log (manual / testing)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'birthday_record_id' => ['required', 'integer', 'exists:birthday_records,id'],
            'business_id'        => ['nullable', 'integer', 'exists:businesses,id'],
            'phone'              => ['required', 'string', 'max:30'],
            'wish_date'          => ['required', 'date'],
            'wish_year'          => ['required', 'integer', 'min:2000', 'max:2100'],
            'status'             => ['nullable', 'string', Rule::in(['pending', 'success', 'failed'])],
            'message'            => ['nullable', 'string'],
            'response'           => ['nullable', 'string'],
        ]);

        $data['status'] = $data['status'] ?? 'pending';

        $log = BirthdayWishLog::create($data);

        // JSON:
        // return response()->json(['success' => true, 'data' => $log], 201);

        return redirect()
            ->route('birthday-wish-logs.show', $log->id)
            ->with('success', 'Wish log created successfully.');
    }

    /**
     * PUT/PATCH /birthday-wish-logs/{log}
     * update log
     */
    public function update(Request $request, BirthdayWishLog $birthdayWishLog)
    {
        $data = $request->validate([
            'business_id' => ['nullable', 'integer', 'exists:businesses,id'],
            'phone'       => ['sometimes', 'string', 'max:30'],
            'wish_date'   => ['sometimes', 'date'],
            'wish_year'   => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'status'      => ['sometimes', 'string', Rule::in(['pending', 'success', 'failed'])],
            'message'     => ['nullable', 'string'],
            'response'    => ['nullable', 'string'],
        ]);

        $birthdayWishLog->update($data);

        // JSON:
        // return response()->json(['success' => true, 'data' => $birthdayWishLog]);

        return back()->with('success', 'Wish log updated.');
    }

    /**
     * DELETE /birthday-wish-logs/{log}
     */
    public function destroy(BirthdayWishLog $birthdayWishLog)
    {
        $birthdayWishLog->delete();

        // JSON:
        // return response()->json(['success' => true]);

        return redirect()->route('birthday-wish-logs.index')->with('success', 'Wish log deleted.');
    }

    /**
     * POST /birthday-wish-logs/{log}/success
     * Quick helper
     */
    public function markSuccess(Request $request, BirthdayWishLog $birthdayWishLog)
    {
        $data = $request->validate([
            'response' => ['nullable', 'string'],
        ]);

        $birthdayWishLog->update([
            'status'   => 'success',
            'response' => $data['response'] ?? $birthdayWishLog->response,
        ]);

        return back()->with('success', 'Marked as success.');
    }

    /**
     * POST /birthday-wish-logs/{log}/failed
     * Quick helper
     */
    public function markFailed(Request $request, BirthdayWishLog $birthdayWishLog)
    {
        $data = $request->validate([
            'response' => ['nullable', 'string'],
        ]);

        $birthdayWishLog->update([
            'status'   => 'failed',
            'response' => $data['response'] ?? $birthdayWishLog->response,
        ]);

        return back()->with('success', 'Marked as failed.');
    }
}
