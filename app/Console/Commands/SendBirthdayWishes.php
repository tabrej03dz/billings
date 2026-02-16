<?php

namespace App\Console\Commands;

use App\Models\BirthdayRecord;
use App\Models\BirthdayWishLog;
use App\Services\WhatApiWhatsappService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Services\MediaManagerService;

class SendBirthdayWishes extends Command
{
    protected $signature = 'app:send-birthday-wishes';
    protected $description = 'Send automatic birthday wishes on WhatsApp';

    // public function handle(WhatApiWhatsappService $sender)
    // {
    //     $today = Carbon::now()->timezone(config('app.timezone'));
    //     $month = (int) $today->format('m');
    //     $day   = (int) $today->format('d');
    //     $year  = (int) $today->format('Y');

    //     $records = BirthdayRecord::query()
    //         ->whereMonth('date_of_birth', $month)
    //         ->whereDay('date_of_birth', $day)
    //         ->get();

    //     $this->info("Found: {$records->count()} birthdays for ".$today->toDateString());

    //     foreach ($records as $r) {

    //         $already = BirthdayWishLog::where('birthday_record_id', $r->id)
    //             ->where('wish_year', $year)
    //             ->exists();

    //         if ($already) {
    //             $this->line("Skip (already sent): {$r->phone}");
    //             continue;
    //         }

    //         $name = $r->name ?: 'Dear';
    //         $message = "🎉 Happy Birthday {$name}! 🎂\nGod bless you with health, happiness & success.\n\n— Real Victory Groups";

    //         $log = BirthdayWishLog::create([
    //             'birthday_record_id' => $r->id,
    //             'business_id'        => $r->business_id,
    //             'phone'              => $r->phone,
    //             'wish_date'          => $today->toDateString(),
    //             'wish_year'          => $year,
    //             'status'             => 'pending',
    //             'message'            => $message,
    //         ]);
    //         $apiKey = $r->user->api;
    //         $url = $apiKey->wishes_api;

    //         if($apiKey->birthday_wish_video_url_updated_on > 6){
    //             $publicVideo = asset('asset/video/birthday-wish.mp4');
    //         }

    //         //      if (!$url){
    //         //       $url = 'https://webhooks.1automations.com/webhook/694e1e278849903df2fd8ff4';
    //         //      }
    //         try {
    //             $resp = $sender->sendBirthdayWish($r->phone, $url);

    //             $log->update([
    //                 'status'   => $resp['ok'] ? 'success' : 'failed',
    //                 'response' => "HTTP {$resp['status']} | ".$resp['body'],
    //             ]);

    //             $this->line(($resp['ok'] ? "✅ Sent: " : "❌ Failed: ").$r->phone);

    //         } catch (\Throwable $e) {
    //             $log->update([
    //                 'status'   => 'failed',
    //                 'response' => $e->getMessage(),
    //             ]);
    //             $this->line("❌ Exception: {$r->phone} - ".$e->getMessage());
    //         }

    //          sleep(6);   // 3 seconds delay before next API call
    //     }

    //     return self::SUCCESS;
    // }





    public function handle(WhatApiWhatsappService $sender, MediaManagerService $mm)
{
    $today = Carbon::now()->timezone(config('app.timezone'));
    $month = (int) $today->format('m');
    $day   = (int) $today->format('d');
    $year  = (int) $today->format('Y');

    $records = BirthdayRecord::query()
        ->whereMonth('date_of_birth', $month)
        ->whereDay('date_of_birth', $day)
        ->with(['user.api']) // ✅
        ->get();

    $this->info("Found: {$records->count()} birthdays for ".$today->toDateString());

    foreach ($records as $r) {

        $already = BirthdayWishLog::where('birthday_record_id', $r->id)
            ->where('wish_year', $year)
            ->exists();

        if ($already) {
            $this->line("Skip (already sent): {$r->phone}");
            continue;
        }

        $name = $r->name ?: 'Dear';
        $message = "🎉 Happy Birthday {$name}! 🎂\nGod bless you with health, happiness & success.\n\n— Real Victory Groups";

        $log = BirthdayWishLog::create([
            'birthday_record_id' => $r->id,
            'business_id'        => $r->business_id,
            'phone'              => $r->phone,
            'wish_date'          => $today->toDateString(),
            'wish_year'          => $year,
            'status'             => 'pending',
            'message'            => $message,
        ]);

        $apiKey = optional($r->user)->api;
        if (!$apiKey) {
            $log->update(['status'=>'failed','response'=>'Api key missing']);
            $this->line("❌ ApiKey missing: {$r->phone}");
            continue;
        }

        $url = $apiKey->wishes_api;
        if (!$url) {
            $log->update(['status'=>'failed','response'=>'wishes_api missing']);
            $this->line("❌ wishes_api missing: {$r->phone}");
            continue;
        }

        // ✅ 1) Absolute path
        $absolutePath = public_path('asset/video/birthday-wish.mp4');

        // ✅ 2) Upload to Media Manager
        $mmRes = $mm->upload($absolutePath);
        $liveUrl = ($mmRes['ok'] ?? false) ? ($mmRes['remote_url'] ?? null) : null;

        // ✅ 3) Save in api_keys table
        if (!empty($liveUrl)) {

            $update = [
                'birthday_wish_media_manager_video_url' => $liveUrl,
                'birthday_wish_video_url_updated_on'    => $today->toDateString(),
            ];

            // ✅ save absolute path only if null
            if (empty($apiKey->birthday_wish_video_absolute_path)) {
                $update['birthday_wish_video_absolute_path'] = $absolutePath;
            }

            $apiKey->update($update);
        } else {
            // optional: log in wish log response for debug
            $log->update([
                'response' => 'MediaManager upload failed: '.(($mmRes['raw'] ?? '') ?: 'unknown'),
            ]);
        }

        // ✅ Now decide which video url to use for sending (prefer live url)
        $publicVideo = $liveUrl ?: asset('asset/video/birthday-wish.mp4');

        try {
            // NOTE: if your sendBirthdayWish supports video/text, pass it
            $resp = $sender->sendBirthdayWish($r->phone, $url, $publicVideo, $message);

            $log->update([
                'status'   => ($resp['ok'] ?? false) ? 'success' : 'failed',
                'response' => "HTTP ".($resp['status'] ?? 0)." | ".($resp['body'] ?? ''),
            ]);

            $this->line((($resp['ok'] ?? false) ? "✅ Sent: " : "❌ Failed: ").$r->phone);

        } catch (\Throwable $e) {
            $log->update([
                'status'   => 'failed',
                'response' => $e->getMessage(),
            ]);
            $this->line("❌ Exception: {$r->phone} - ".$e->getMessage());
        }

        sleep(6);
    }

    return self::SUCCESS;
}


}
