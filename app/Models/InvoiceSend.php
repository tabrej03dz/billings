<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSend extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'meta'    => 'array',
        'sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }


    public function sendQueuedPdfs(Request $request)
    {
        $user = $request->user();

        $apiKey = ApiKey::where('user_id', $user->id)->latest('id')->first();
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp API not set. Please set from API Settings.',
            ], 422);
        }

        // ✅ avoid double click / parallel sending
        $lock = Cache::lock("wa_send_user_{$user->id}", 180); // 3 min
        if (!$lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Sending already in progress. Please wait.',
            ], 429);
        }

        try {
            $limit = (int) $request->input('limit', 50);
            $limit = max(1, min(200, $limit));

            $rows = InvoiceSend::query()
                ->where('user_id', $user->id)
                ->where('channel', 'whatsapp')
                ->where('status', 'queued')
                ->orderBy('id')
                ->limit($limit)
                ->get();

            if ($rows->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No queued PDFs found.',
                    'sent'    => 0,
                    'failed'  => 0,
                ]);
            }

            $baseUrl = strtok($apiKey->base_url, '?');

            $sent = 0;
            $failed = 0;
            $results = [];

            foreach ($rows as $row) {

                // mark sending
                $row->update([
                    'status' => 'sending',
                    'error_message' => null,
                    'response_code' => null,
                ]);

                $phone = preg_replace('/\D+/', '', (string)$row->recipient_phone);
                $pdfUrl = (string)$row->file_url;

                $endpoint = $baseUrl . '?' . http_build_query([
                        'number' => $phone,
                        'pdf'    => $pdfUrl,
                    ]);

                // ✅ rate limit protection
                sleep(3);

                $status = null;
                $body   = null;
                $ok     = false;

                try {
                    $resp = Http::timeout(20)->get($endpoint);
                    $status = $resp->status();
                    $body   = $resp->body();
                    $ok     = $resp->successful();
                } catch (\Throwable $e) {
                    $body = $e->getMessage();
                    $ok   = false;
                }

                if ($ok) {
                    $row->update([
                        'status'        => 'success',
                        'response_code' => $status,
                        'sent_at'       => now(),
                        'meta'          => array_merge((array)($row->meta ?? []), [
                            'endpoint' => $endpoint,
                        ]),
                    ]);
                    $sent++;
                    $results[] = ['id' => $row->id, 'ok' => true, 'phone' => $phone];
                } else {
                    $row->update([
                        'status'        => 'failed',
                        'response_code' => $status,
                        'error_message' => Str::limit((string)($body ?? 'Unknown error'), 500),
                        'sent_at'       => now(),
                        'meta'          => array_merge((array)($row->meta ?? []), [
                            'endpoint' => $endpoint,
                        ]),
                    ]);
                    $failed++;
                    $results[] = ['id' => $row->id, 'ok' => false, 'phone' => $phone];
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Done. Sent: {$sent}, Failed: {$failed}",
                'sent'    => $sent,
                'failed'  => $failed,
                'results' => $results,
            ]);

        } finally {
            optional($lock)->release();
        }
    }

}
