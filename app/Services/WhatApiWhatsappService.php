<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatApiWhatsappService
{
    public function sendBirthdayWish(string $phone, string $message, $url): array
    {
//        $url = config('services.whatapi.webhook_url');
//        $url = 'https://webhooks.1automations.com/webhook/694e1e278849903df2fd8ff4';

        // ✅ Basic sanitation (only digits)
        $to = preg_replace('/\D+/', '', $phone);

        // If 10 digit => add 91 (India)
        if (strlen($to) === 10) $to = '91' . $to;

        // ✅ Payload (adjust keys if your provider expects different)
        $payload = [
            'number'      => $to,
            'video' => asset('asset/video/birthday wish.mp4'),
        ];

        Log::info('WA WEBHOOK REQ', ['url'=>$url, 'payload'=>$payload]);

        $res = Http::timeout(60)->withoutVerifying()->acceptJson()->post($url, $payload);

        Log::info('WA WEBHOOK RES', [
            'status' => $res->status(),
            'body'   => $res->body(),
        ]);

        return [
            'ok'     => $res->successful(),
            'status' => $res->status(),
            'body'   => $res->body(),
        ];

    }
}
