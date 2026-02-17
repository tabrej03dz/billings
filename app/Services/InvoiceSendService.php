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
                $pdfUrl = (string) ($pdf->media_manager_file_url ?? $pdf->file_url);

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
                sleep(5);
            }

            return ['sentOk' => $sentOk, 'sentFail' => $sentFail, 'message' => null];

        } finally {
            optional($lock)->release();
        }
    }


    public function sendSingleInvoice(InvoiceSend $pdf): array
{
    // ✅ Only WhatsApp uploaded items
    if ($pdf->channel !== 'whatsapp') {
        return ['sentOk' => 0, 'sentFail' => 1, 'message' => 'Channel is not whatsapp'];
    }

    // ✅ Must be uploaded & not sent
    if ($pdf->status !== 'uploaded' || !is_null($pdf->sent_at)) {
        return ['sentOk' => 0, 'sentFail' => 0, 'message' => 'Already processed'];
    }

    // ✅ Per-invoice lock (best)
    $lock = Cache::lock("invoice_send_row_{$pdf->id}", 180);

    if (! $lock->get()) {
        return ['sentOk' => 0, 'sentFail' => 0, 'message' => 'This invoice is already being sent'];
    }

    try {
        // ✅ Atomic: uploaded -> sending (prevents double send even without lock)
        $updated = InvoiceSend::where('id', $pdf->id)
            ->where('status', 'uploaded')
            ->whereNull('sent_at')
            ->update([
                'status'        => 'sending',
                'error_message' => null,
                'response_code' => null,
            ]);

        if ($updated === 0) {
            return ['sentOk' => 0, 'sentFail' => 0, 'message' => 'Already picked by another process'];
        }

        // refresh latest
        $pdf->refresh();

        $apiKey = ApiKey::where('user_id', $pdf->user_id)->latest('id')->first();

        if (! $apiKey) {
            $pdf->update([
                'status'        => 'failed',
                'error_message' => 'WhatsApp API not set. Please set from API Settings.',
            ]);

            return ['sentOk' => 0, 'sentFail' => 1, 'message' => 'WhatsApp API not set'];
        }

        $baseUrl = strtok((string) $apiKey->base_url, '?');

        $phone  = preg_replace('/\D+/', '', (string) $pdf->recipient_phone);
        $pdfUrl = (string) ($pdf->media_manager_file_url ?? $pdf->file_url);

        if (! $phone || ! $pdfUrl) {
            $pdf->update([
                'status'        => 'failed',
                'error_message' => 'Phone or PDF URL missing.',
            ]);
            return ['sentOk' => 0, 'sentFail' => 1, 'message' => 'Phone or PDF URL missing'];
        }

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

            return ['sentOk' => 1, 'sentFail' => 0, 'message' => null];
        }

        $pdf->update([
            'status'        => 'failed',
            'response_code' => $status,
            'error_message' => Str::limit((string) ($body ?? 'Unknown error'), 500),
            'meta'          => array_merge((array) ($pdf->meta ?? []), [
                'endpoint' => $endpoint,
            ]),
        ]);

        return ['sentOk' => 0, 'sentFail' => 1, 'message' => 'Failed'];
    }
    finally {
        optional($lock)->release();
    }
}
}
