<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\InvoiceSend;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class InvoiceSendController extends Controller
{
//    public function index(Request $request)
//    {
//        $today      = Carbon::today();
//        $monthStart = Carbon::now()->startOfMonth();
//
//        // filters (optional)
//        $channel = $request->get('channel'); // whatsapp/email
//        $from    = $request->get('from') ?: $monthStart->toDateString();
//        $to      = $request->get('to')   ?: $today->toDateString();
//
//        $authUser = $request->user();
//
//        $query = InvoiceSend::with('user')
//            ->whereBetween('sent_at', [$from.' 00:00:00', $to.' 23:59:59']);
//
//        // ✅ Role-based visibility
//        // Super admin => all records, others => only own
//        if (! $authUser->hasRole('super admin')) {
//            $query->where('user_id', $authUser->id);
//        }
//
//        if ($channel) {
//            $query->where('channel', $channel);
//        }
//
//        // per-user aggregated
//        $perUser = (clone $query)
//            ->selectRaw('user_id, count(*) as total, sum(case when status = "success" then 1 else 0 end) as success_count')
//            ->groupBy('user_id')
//            ->with('user')
//            ->get();
//
//        // total rows list (latest)
//        $latestSends = (clone $query)
//            ->latest('sent_at')
//            ->limit(50)
//            ->with(['user', 'invoice'])
//            ->get();
//
//        return view('reports.invoice_sends', compact(
//            'perUser',
//            'latestSends',
//            'from',
//            'to',
//            'channel'
//        ));
//    }

    public function index(Request $request)
    {
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        // filters (optional)
        $channel = $request->get('channel'); // whatsapp/email
        $from    = $request->get('from') ?: $monthStart->toDateString();
        $to      = $request->get('to')   ?: $today->toDateString();

        $authUser = $request->user();

        // -----------------------------
        // ✅ 1) FIRST: Send all "uploaded" PDFs for this user
        // -----------------------------
        $uploadedPdfs = InvoiceSend::query()
            ->where('status', 'uploaded')
            ->where('user_id', $authUser->id)
            ->where('channel', 'whatsapp') // (optional) agar sirf whatsapp wale upload hote hain
            ->get();

        if ($uploadedPdfs->count() > 0) {

            // ✅ avoid double run / parallel (optional but recommended)
            $lock = Cache::lock("invoice_send_uploaded_user_{$authUser->id}", 180); // 3 min
            if ($lock->get()) {
                try {
                    $apiKey = ApiKey::where('user_id', $authUser->id)->latest('id')->first();
                    if (!$apiKey) {
                        // mark all uploaded as failed (so it doesn't keep trying silently)
                        InvoiceSend::where('status', 'uploaded')
                            ->where('user_id', $authUser->id)
                            ->update([
                                'status'        => 'failed',
                                'error_message' => 'WhatsApp API not set. Please set from API Settings.',
                            ]);

                        return back()->with('error', 'WhatsApp API not set. Please set from API Settings.');
                    }

                    $baseUrl = strtok($apiKey->base_url, '?');

                    $sentOk = 0;
                    $sentFail = 0;

                    foreach ($uploadedPdfs as $pdf) {
                        $phone  = preg_replace('/\D+/', '', (string) $pdf->recipient_phone);
                        $pdfUrl = (string) $pdf->file_url;

                        if (!$phone || !$pdfUrl) {
                            $pdf->update([
                                'status'        => 'failed',
                                'error_message' => 'Phone or PDF URL missing.',
                            ]);
                            $sentFail++;
                            continue;
                        }

                        // mark sending
                        $pdf->update([
                            'status'        => 'sending',
                            'error_message' => null,
                            'response_code' => null,
                        ]);

                        $status = null;
                        $body   = null;
                        $ok     = false;

                        try {
                            $resp = Http::timeout(30)
                                ->retry(2, 200) // optional retry
                                ->get($baseUrl, [
                                    'number' => $phone,
                                    'pdf'    => $pdfUrl,
                                ]);

                            $status = $resp->status();
                            $body   = $resp->body();
                            $ok     = $resp->successful();
                        } catch (\Throwable $e) {
                            $body = $e->getMessage();
                            $ok   = false;
                        }

                        $endpoint = $baseUrl . '?' . http_build_query(['number' => $phone, 'pdf' => $pdfUrl]);

                        if ($ok) {
                            $pdf->update([
                                'status'        => 'success',
                                'response_code' => $status,
                                'sent_at'       => now(),
                                'meta'          => array_merge((array) ($pdf->meta ?? []), [
                                    'endpoint' => $endpoint,
                                    'response' => Str::limit((string) $body, 1000),
                                ]),
                            ]);
                            $sentOk++;
                            continue;
                        }

                        $pdf->update([
                            'status'        => 'failed',
                            'response_code' => $status,
                            'error_message' => Str::limit((string) ($body ?? 'Unknown error'), 500),
                            'meta'          => array_merge((array) ($pdf->meta ?? []), [
                                'endpoint' => $endpoint,
                            ]),
                        ]);
                        $sentFail++;
                    }

                    if ($sentOk > 0) {
                        session()->flash('success', "{$sentOk} PDF sent successfully" . ($sentFail ? ", {$sentFail} failed." : "."));
                    } elseif ($sentFail > 0) {
                        session()->flash('error', "{$sentFail} PDF send failed. Please retry.");
                    }

                } finally {
                    optional($lock)->release();
                }
            } else {
                session()->flash('info', 'Sending already in progress. Please wait.');
            }
        }

        // -----------------------------
        // ✅ 2) REPORT QUERY (after send)
        // -----------------------------
        $query = InvoiceSend::with('user')
            ->whereBetween('sent_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        // ✅ Role-based visibility
        if (! $authUser->hasRole('super admin')) {
            $query->where('user_id', $authUser->id);
        }

        if ($channel) {
            $query->where('channel', $channel);
        }

        // per-user aggregated
        $perUser = (clone $query)
            ->selectRaw('user_id, count(*) as total, sum(case when status = "success" then 1 else 0 end) as success_count')
            ->groupBy('user_id')
            ->with('user')
            ->get();

        // latest sends
        $latestSends = (clone $query)
            ->latest('sent_at')
            ->with(['user', 'invoice'])
            ->paginate(30)          // per page
            ->withQueryString();

        return view('reports.invoice_sends', compact(
            'perUser',
            'latestSends',
            'from',
            'to',
            'channel'
        ));
    }


}
