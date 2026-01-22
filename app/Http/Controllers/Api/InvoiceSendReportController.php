<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceSend;
use App\Services\InvoiceSendService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InvoiceSendReportController extends Controller
{
    public function index(Request $request, InvoiceSendService $sender)
    {
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $channel = $request->query('channel'); // whatsapp/email
        $from    = $request->query('from') ?: $monthStart->toDateString();
        $to      = $request->query('to')   ?: $today->toDateString();

        $autoSend = filter_var($request->query('auto_send', false), FILTER_VALIDATE_BOOLEAN);
        $perPage  = (int) $request->query('per_page', 30);
        if ($perPage < 1 || $perPage > 200) $perPage = 30;

        $authUser = $request->user();

        // ✅ 1) OPTIONAL: Send all "uploaded" PDFs for this user
        $sendResult = null;
        if ($autoSend) {
            $sendResult = $sender->sendUploadedForUser($authUser);
        }

        // ✅ 2) REPORT QUERY
        $query = InvoiceSend::query()->with('user');

        // ✅ Normal user => only own data + date filter on sent_at
        if (! $authUser->hasRole('super admin')) {
            $query->where('user_id', $authUser->id);
        }
        // ✅ Super admin => full data (no date filter), web jaisa


        // per-user aggregated
        $perUser = (clone $query)
            ->selectRaw('user_id,
            count(*) as total,
            sum(case when status = "success" then 1 else 0 end) as success_count,
            sum(case when status = "failed" then 1 else 0 end) as failed_count
        ')
            ->groupBy('user_id')
            ->with('user')
            ->get();

        // latest sends
        $latestSends = (clone $query)
            ->latest('sent_at') // null sent_at last
            ->with(['user', 'invoice'])
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'ok' => true,
            'filters' => [
                'from' => $from,
                'to' => $to,
                'channel' => $channel,
                'per_page' => $perPage,
                'auto_send' => $autoSend,
                'is_super_admin' => $authUser->hasRole('super admin'),
            ],
            'send_result' => $sendResult,
            'per_user' => $perUser,
            'latest_sends' => $latestSends,
        ]);
    }


    public function uploadAndSend(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'pdf'   => ['required', 'file', 'mimes:pdf', 'max:5120'], // 5MB
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $pdfFile = $request->file('pdf');

        // ✅ phone decide: input > filename
        $phone = preg_replace('/\D+/', '', (string)$request->input('phone', ''));

        if (empty($phone)) {
            $originalName  = $pdfFile->getClientOriginalName(); // 9876543210.pdf
            $nameNoExt     = pathinfo($originalName, PATHINFO_FILENAME);
            $phoneFromFile = preg_replace('/\D+/', '', $nameNoExt);

            if (strlen($phoneFromFile) === 10) $phoneFromFile = '91' . $phoneFromFile;
            $phone = $phoneFromFile;
        }

        // normalize: 10 digit => add 91
        if (strlen($phone) === 10) $phone = '91' . $phone;

        if (empty($phone) || strlen($phone) < 12) {
            return response()->json([
                'success' => false,
                'message' => 'Valid phone not found. Provide phone input or name file as 10 digit number.',
            ], 422);
        }

        // ✅ store pdf
        $pdfPath = $pdfFile->store('no-business-pdfs', 'public');
        $pdfUrl  = asset('storage/' . $pdfPath);

        // ✅ create queue/log row first
        $row = InvoiceSend::create([
            'business_id'         => null,
            'user_id'             => $user->id,
            'invoice_id'          => null,
            'channel'             => 'whatsapp',
            'recipient_phone'     => $phone,
            'recipient_email'     => null,
            'file_url'            => $pdfUrl,
            'status'              => 'uploaded',
            'response_code'       => null,
            'provider_message_id' => null,
            'error_message'       => null,
            'meta'                => [
                'pdf_path'    => $pdfPath,
                'pdf_url'     => $pdfUrl,
                'source_name' => $pdfFile->getClientOriginalName(),
            ],
            'sent_at'             => null,
        ]);

        // ✅ SEND NOW (same request)
        try {
            // ✅ your webhook url in config/services.php -> services.whatapi.webhook_url
            $webhookUrl = $user->api->base_url;

            if (!$webhookUrl) {
                throw new \Exception("Webhook URL missing in config('services.whatapi.webhook_url')");
            }

            // ✅ provider payload (change keys if your provider requires)
            $payload = [
                'number' => $phone,
                'pdf'    => $pdfUrl,   // if provider wants "Document" then replace
            ];

            Log::info('WA PDF SEND REQ', ['invoice_send_id' => $row->id, 'url' => $webhookUrl, 'payload' => $payload]);

            $res  = Http::timeout(60)->acceptJson()->post($webhookUrl, $payload);
            $body = $res->json();

            Log::info('WA PDF SEND RES', [
                'invoice_send_id' => $row->id,
                'status'          => $res->status(),
                'body'            => $body,
            ]);

            if ($res->successful()) {
                $row->update([
                    'status'              => 'success',
                    'response_code'       => $res->status(),
                    'provider_message_id' => $body['message_id'] ?? $body['id'] ?? null,
                    'error_message'       => null,
                    'sent_at'             => Carbon::now(),
                    'meta'                => array_merge(($row->meta ?? []), [
                        'provider_raw' => $body,
                    ]),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Uploaded & sent',
                    'id'      => $row->id,
                    'phone'   => $phone,
                    'pdf_url' => $pdfUrl,
                    'status'  => 'success',
                ], 200);
            }

            // failed response
            $row->update([
                'status'        => 'failed',
                'response_code' => $res->status(),
                'error_message' => is_string($res->body()) ? $res->body() : 'Send failed',
                'meta'          => array_merge(($row->meta ?? []), [
                    'provider_raw' => $body,
                ]),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Uploaded but send failed',
                'id'      => $row->id,
                'phone'   => $phone,
                'pdf_url' => $pdfUrl,
                'status'  => 'failed',
                'error'   => $row->error_message,
            ], 500);

        } catch (\Throwable $e) {
            Log::error('uploadAndSend exception', ['invoice_send_id' => $row->id, 'err' => $e->getMessage()]);

            $row->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Uploaded but send failed (exception)',
                'id'      => $row->id,
                'phone'   => $phone,
                'pdf_url' => $pdfUrl,
                'status'  => 'failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


}
