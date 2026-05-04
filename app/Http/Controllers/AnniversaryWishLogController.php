<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\AnniversaryWishLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AnniversaryWishLogController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $q = AnniversaryWishLog::query()->latest('id');

        if (!$user->hasRole('super admin')) {
            $q->whereHas('anniversary', function ($qr) use ($user) {
                $qr->where('user_id', $user->id);
            });
        }

        if ($request->filled('business_id')) {
            $q->where('business_id', $request->business_id);
        }

        if ($request->filled('anniversary_id')) {
            $q->where('anniversary_id', $request->anniversary_id);
        }

        if ($request->filled('phone')) {
            $q->where('phone', 'like', '%' . trim($request->phone) . '%');
        }

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        $from = $request->filled('from') ? Carbon::parse($request->from)->toDateString() : null;
        $to   = $request->filled('to') ? Carbon::parse($request->to)->toDateString() : null;

        if ($from && $to) {
            $q->whereBetween('wish_date', [$from, $to]);
        } elseif ($from) {
            $q->whereDate('wish_date', '>=', $from);
        } elseif ($to) {
            $q->whereDate('wish_date', '<=', $to);
        }

        if ($request->filled('wish_year')) {
            $q->where('wish_year', (int) $request->wish_year);
        }

        $perPage = max(1, min(200, (int) $request->get('per_page', 50)));

        $logs = $q->paginate($perPage)->withQueryString();

        return view('anniversary_wish_logs.index', compact('logs'));
    }

    public function resend(AnniversaryWishLog $anniversaryWishLog)
    {
        try {
            $raw = DB::table('anniversaries')
                ->where('id', $anniversaryWishLog->anniversary_id)
                ->first();

            if (!$raw) {
                $anniversaryWishLog->update([
                    'status' => 'failed',
                    'response' => 'Anniversary record not found',
                ]);

                return back()->with('success', 'Resend failed ❌ (Anniversary record not found)');
            }

            $url = ApiKey::where('user_id', $raw->user_id)->first()?->wishes_api;

            if (!$url) {
                $anniversaryWishLog->update([
                    'status' => 'failed',
                    'response' => 'Webhook URL missing',
                ]);

                return back()->with('success', 'Resend failed ❌ (Webhook URL missing)');
            }

            $to = preg_replace('/\D+/', '', (string) $anniversaryWishLog->phone);

            if (strlen($to) === 10) {
                $to = '91' . $to;
            }

            $videoRelPath = 'videos/anniversary-wish.mp4';

            if (!Storage::disk('public')->exists($videoRelPath)) {
                $anniversaryWishLog->update([
                    'status' => 'failed',
                    'response' => 'Video missing: storage/app/public/videos/anniversary-wish.mp4',
                ]);

                return back()->with('success', 'Resend failed ❌ (Video missing)');
            }

            $name = $raw->name ?? 'Dear';
            $videoUrl = asset('storage/' . $videoRelPath);
            $abs = Storage::disk('public')->path($videoRelPath);

            Log::info('ANNIVERSARY WA RESEND REQ', [
                'log_id' => $anniversaryWishLog->id,
                'url' => $url,
                'number' => $to,
                'videoUrl' => $videoUrl,
            ]);

            $res = Http::timeout(60)
                ->acceptJson()
                ->asJson()
                ->post($url, [
                    'number' => $to,
                    'Video'  => $videoUrl,
                    'name'   => $name,
                    'image'  => asset('asset/img/anniversary-wish.jpeg'),
                ]);

            if (!$res->successful()) {
                $fh = fopen($abs, 'r');

                if ($fh === false) {
                    $anniversaryWishLog->update([
                        'status' => 'failed',
                        'response' => 'Could not open video file',
                    ]);

                    return back()->with('success', 'Resend failed ❌ (Could not open video file)');
                }

                try {
                    $res = Http::timeout(120)
                        ->acceptJson()
                        ->asMultipart()
                        ->attach('Video', $fh, 'anniversary-wish.mp4')
                        ->post($url, [
                            'number' => $to,
                            'name'   => $name,
                        ]);
                } finally {
                    fclose($fh);
                }
            }

            $anniversaryWishLog->update([
                'status' => $res->successful() ? 'success' : 'failed',
                'response' => $res->body(),
            ]);

            Log::info('ANNIVERSARY WA RESEND RES', [
                'log_id' => $anniversaryWishLog->id,
                'status' => $res->status(),
                'body' => $res->body(),
            ]);

            if (!$res->successful()) {
                return back()->with('success', 'Resend failed ❌ | ' . $res->status() . ' | ' . $res->body());
            }

            return back()->with('success', 'Resent successfully ✅');

        } catch (\Throwable $e) {
            Log::error('Anniversary resend error', [
                'log_id' => $anniversaryWishLog->id ?? null,
                'err' => $e->getMessage(),
            ]);

            $anniversaryWishLog->update([
                'status' => 'failed',
                'response' => $e->getMessage(),
            ]);

            return back()->with('success', 'Resend failed ❌ (exception): ' . $e->getMessage());
        }
    }

    public function show(AnniversaryWishLog $anniversaryWishLog)
    {
        return view('anniversary_wish_logs.show', [
            'log' => $anniversaryWishLog
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'anniversary_id' => ['required', 'integer', 'exists:anniversaries,id'],
            'business_id'   => ['nullable', 'integer'],
            'phone'         => ['required', 'string', 'max:30'],
            'wish_date'     => ['required', 'date'],
            'wish_year'     => ['required', 'integer', 'min:2000', 'max:2100'],
            'status'        => ['nullable', 'string', Rule::in(['pending', 'success', 'failed'])],
            'message'       => ['nullable', 'string'],
            'response'      => ['nullable', 'string'],
        ]);

        $data['status'] = $data['status'] ?? 'pending';

        $log = AnniversaryWishLog::create($data);

        return redirect()
            ->route('anniversary-wish-logs.show', $log->id)
            ->with('success', 'Anniversary wish log created successfully.');
    }

    public function update(Request $request, AnniversaryWishLog $anniversaryWishLog)
    {
        $data = $request->validate([
            'business_id' => ['nullable', 'integer'],
            'phone'      => ['sometimes', 'string', 'max:30'],
            'wish_date'  => ['sometimes', 'date'],
            'wish_year'  => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'status'     => ['sometimes', 'string', Rule::in(['pending', 'success', 'failed'])],
            'message'    => ['nullable', 'string'],
            'response'   => ['nullable', 'string'],
        ]);

        $anniversaryWishLog->update($data);

        return back()->with('success', 'Anniversary wish log updated.');
    }

    public function destroy(AnniversaryWishLog $anniversaryWishLog)
    {
        $anniversaryWishLog->delete();

        return redirect()
            ->route('anniversary-wish-logs.index')
            ->with('success', 'Anniversary wish log deleted.');
    }

    public function markSuccess(Request $request, AnniversaryWishLog $anniversaryWishLog)
    {
        $data = $request->validate([
            'response' => ['nullable', 'string'],
        ]);

        $anniversaryWishLog->update([
            'status' => 'success',
            'response' => $data['response'] ?? $anniversaryWishLog->response,
        ]);

        return back()->with('success', 'Marked as success.');
    }

    public function markFailed(Request $request, AnniversaryWishLog $anniversaryWishLog)
    {
        $data = $request->validate([
            'response' => ['nullable', 'string'],
        ]);

        $anniversaryWishLog->update([
            'status' => 'failed',
            'response' => $data['response'] ?? $anniversaryWishLog->response,
        ]);

        return back()->with('success', 'Marked as failed.');
    }
}
