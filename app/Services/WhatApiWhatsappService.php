<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatApiWhatsappService
{
    public function sendBirthdayWish(string $phone, string $message, $url): array
    {
//        $url = config('services.whatapi.webhook_url');
        $url = 'https://webhooks.1automations.com/webhook/6946555ae60589cc861361d7';

        // ✅ Basic sanitation (only digits)
        $to = preg_replace('/\D+/', '', $phone);

        // If 10 digit => add 91 (India)
        if (strlen($to) === 10) $to = '91' . $to;

        // ✅ Payload (adjust keys if your provider expects different)
        $payload = [
            'to'      => $to,
            'message' => $message,
            'pdf' => 'https://post.realvictorygroups.com/storage/images/2025-12-20/Jewellery/BrckLBbXfHxGdR8cOA8xj6jKxPJAaR77Dr0waMZM.jpg',
        ];

        $res = Http::timeout(60)
            ->withoutVerifying()   // 🔴 SSL verification OFF
            ->acceptJson()
            ->post($url, $payload);

        return [
            'ok' => $res->successful(),
            'status' => $res->status(),
            'body' => $res->body(),
        ];
    }
}
