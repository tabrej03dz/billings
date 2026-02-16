<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class WhatApiWhatsappService
{
    public function sendBirthdayWish(string $phone, $url): array
    {


        // ✅ Basic sanitation (only digits)
        $to = preg_replace('/\D+/', '', $phone);

        // If 10 digit => add 91 (India)
        if (strlen($to) === 10) $to = '91' . $to;

        // ✅ Payload (adjust keys if your provider expects different)
        $payload = [
            'number'      => $to,
            // 'Video' => asset('asset/video/birthday-wish.mp4'),
            // 'Video' => url(Storage::url('videos/birthday-wish.mp4')),
            'Video' => "https://confidentialcontent.s3.eu-west-1.wasabisys.com/filez/a1cbf89e-4cb8-4aa6-a0f7-96675d7d359d.mp4", //
        ];

        Log::info('WA WEBHOOK REQ', ['url'=>$url, 'payload'=>$payload]);

        $res = Http::timeout(60)->acceptJson()->post($url, $payload);

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

    public function runInstallmentReminders(string $phone, $url, $snmeNumber, $amount, $date): array
    {

        // ✅ Basic sanitation (only digits)
        $to = preg_replace('/\D+/', '', $phone);

        // If 10 digit => add 91 (India)
        if (strlen($to) === 10) $to = '91' . $to;

        // ✅ Payload (adjust keys if your provider expects different)
        $payload = [
            'number'      => $to,
//            'Video' => asset('asset/video/birthday-wish.mp4'),
            'AcNo' => $snmeNumber,
            'amount' => $amount,
            'date' => now('Asia/Kolkata')->addDay()->format('d/m/Y'),
        ];

        Log::info('WA WEBHOOK REQ', ['url'=>$url, 'payload'=>$payload]);

        $res = Http::timeout(60)->acceptJson()->post($url, $payload);

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
