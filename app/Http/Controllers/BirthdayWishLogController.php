<?php

namespace App\Http\Controllers;

use App\Models\BirthdayWishLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BirthdayWishLogController extends Controller
{
    public function index(Request $request)
    {
        $q = BirthdayWishLog::query()
            // relations agar banaye ho to uncomment:
            // ->with(['birthdayRecord', 'business'])
            ->latest('id');

        // ✅ Filters
        if ($request->filled('business_id')) {
            $q->where('business_id', $request->business_id);
        }

        if ($request->filled('birthday_record_id')) {
            $q->where('birthday_record_id', $request->birthday_record_id);
        }

        if ($request->filled('phone')) {
            $phone = trim($request->phone);
            $q->where('phone', 'like', "%{$phone}%");
        }

        if ($request->filled('status')) {
            $q->where('status', $request->status); // pending/success/failed
        }

        // date filter (wish_date)
        $from = $request->filled('from') ? Carbon::parse($request->from)->toDateString() : null;
        $to   = $request->filled('to')   ? Carbon::parse($request->to)->toDateString()   : null;

        if ($from && $to) {
            $q->whereBetween('wish_date', [$from, $to]);
        } elseif ($from) {
            $q->whereDate('wish_date', '>=', $from);
        } elseif ($to) {
            $q->whereDate('wish_date', '<=', $to);
        }

        // year filter
        if ($request->filled('wish_year')) {
            $q->where('wish_year', (int) $request->wish_year);
        }

        // per page
        $perPage = (int) ($request->get('per_page', 50));
        $perPage = max(1, min(200, $perPage));

        $logs = $q->paginate($perPage)->withQueryString();

        // If you want JSON API:
        // return response()->json($logs);

        // If blade view:
        return view('birthday_wish_logs.index', compact('logs'));
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
