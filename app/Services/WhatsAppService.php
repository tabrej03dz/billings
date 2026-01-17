<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\InvoiceSend;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WhatsAppService
{
    /**
     * Single PDF send using gateway: base_url?number=...&pdf=...
     */
    public function sendPdfViaGateway(ApiKey $apiKey, string $phone, string $pdfUrl): array
    {
        $phone  = preg_replace('/\D+/', '', (string) $phone);
        $pdfUrl = (string) $pdfUrl;

        if (!$phone || strlen($phone) < 10) {
            return [
                'ok' => false,
                'code' => null,
                'body' => 'Invalid phone number.',
                'endpoint' => null,
            ];
        }

        if (!$pdfUrl) {
            return [
                'ok' => false,
                'code' => null,
                'body' => 'PDF URL missing.',
                'endpoint' => null,
            ];
        }

        $baseUrl = strtok((string) $apiKey->base_url, '?'); // ✅ same as your code

        if (!$baseUrl) {
            return [
                'ok' => false,
                'code' => null,
                'body' => 'WhatsApp base_url missing in ApiKey.',
                'endpoint' => null,
            ];
        }

        $status = null;
        $body   = null;
        $ok     = false;

        try {
            $resp = Http::timeout(30)
                ->retry(2, 200) // ✅ same retry
                ->get($baseUrl, [
                    'number' => $phone,
                    'pdf'    => $pdfUrl,
                ]);

            $status = $resp->status();
            $body   = $resp->body();
            $ok     = $resp->successful();

        } catch (\Throwable $e) {
            $status = 500;
            $body   = $e->getMessage();
            $ok     = false;
        }

        $endpoint = $baseUrl . '?' . http_build_query([
                'number' => $phone,
                'pdf'    => $pdfUrl
            ]);

        return [
            'ok' => $ok,
            'code' => $status,
            'body' => $body,
            'endpoint' => $endpoint,
        ];
    }

    /**
     * Bulk: send all "uploaded" PDFs for a user (exactly like your index() block)
     * Returns summary counts.
     */
    public function sendAllUploadedForUser(int $userId): array
    {
        $uploaded = InvoiceSend::query()
            ->where('status', 'uploaded')
            ->where('user_id', $userId)
            ->where('channel', 'whatsapp')
            ->get();

        if ($uploaded->isEmpty()) {
            return ['sent_ok' => 0, 'sent_fail' => 0, 'message' => 'No uploaded PDFs.'];
        }

        $lock = Cache::lock("invoice_send_uploaded_user_{$userId}", 180);

        if (!$lock->get()) {
            return ['sent_ok' => 0, 'sent_fail' => 0, 'message' => 'Sending already in progress.'];
        }

        try {
            $apiKey = ApiKey::where('user_id', $userId)->latest('id')->first();

            if (!$apiKey) {
                InvoiceSend::where('status', 'uploaded')
                    ->where('user_id', $userId)
                    ->update([
                        'status'        => 'failed',
                        'error_message' => 'WhatsApp API not set. Please set from API Settings.',
                    ]);

                return ['sent_ok' => 0, 'sent_fail' => $uploaded->count(), 'message' => 'WhatsApp API not set.'];
            }

            $sentOk = 0;
            $sentFail = 0;

            foreach ($uploaded as $row) {

                $phone  = preg_replace('/\D+/', '', (string) $row->recipient_phone);
                $pdfUrl = (string) $row->file_url;

                if (!$phone || !$pdfUrl) {
                    $row->update([
                        'status'        => 'failed',
                        'error_message' => 'Phone or PDF URL missing.',
                    ]);
                    $sentFail++;
                    continue;
                }

                // ✅ mark sending
                $row->update([
                    'status'        => 'sending',
                    'error_message' => null,
                    'response_code' => null,
                ]);

                $res = $this->sendPdfViaGateway($apiKey, $phone, $pdfUrl);

                if ($res['ok']) {
                    $row->update([
                        'status'        => 'success',
                        'response_code' => $res['code'],
                        'sent_at'       => now(),
                        'meta'          => array_merge((array) ($row->meta ?? []), [
                            'endpoint' => $res['endpoint'],
                            'response' => Str::limit((string) ($res['body'] ?? ''), 1000),
                        ]),
                    ]);
                    $sentOk++;
                    continue;
                }

                $row->update([
                    'status'        => 'failed',
                    'response_code' => $res['code'],
                    'error_message' => Str::limit((string) ($res['body'] ?? 'Unknown error'), 500),
                    'meta'          => array_merge((array) ($row->meta ?? []), [
                        'endpoint' => $res['endpoint'],
                    ]),
                ]);
                $sentFail++;
            }

            return [
                'sent_ok' => $sentOk,
                'sent_fail' => $sentFail,
                'message' => $sentOk
                    ? "{$sentOk} PDF sent successfully" . ($sentFail ? ", {$sentFail} failed." : ".")
                    : ($sentFail ? "{$sentFail} PDF send failed. Please retry." : "Nothing sent."),
            ];

        } finally {
            optional($lock)->release();
        }
    }
}
