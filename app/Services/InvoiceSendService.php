<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\InvoiceSend;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class InvoiceSendService
{
    public function sendUploadedForUser($user): array
    {
        $uploadedPdfs = InvoiceSend::query()
            ->where('status', 'uploaded')
            ->where('user_id', $user->id)
            ->where('channel', 'whatsapp')
            ->get();

        if ($uploadedPdfs->count() === 0) {
            return ['sentOk' => 0, 'sentFail' => 0, 'message' => null];
        }

        $lock = Cache::lock("invoice_send_uploaded_user_{$user->id}", 180);

        if (! $lock->get()) {
            return ['sentOk' => 0, 'sentFail' => 0, 'message' => 'Sending already in progress. Please wait.'];
        }

        try {
            $apiKey = ApiKey::where('user_id', $user->id)->latest('id')->first();

            if (! $apiKey) {
                InvoiceSend::where('status', 'uploaded')
                    ->where('user_id', $user->id)
                    ->update([
                        'status'        => 'failed',
                        'error_message' => 'WhatsApp API not set. Please set from API Settings.',
                    ]);

                return ['sentOk' => 0, 'sentFail' => $uploadedPdfs->count(), 'message' => 'WhatsApp API not set. Please set from API Settings.'];
            }

            $baseUrl = strtok((string)$apiKey->base_url, '?');

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
                        ->retry(2, 200)
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
                } else {
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
            }

            return ['sentOk' => $sentOk, 'sentFail' => $sentFail, 'message' => null];

        } finally {
            optional($lock)->release();
        }
    }
}
