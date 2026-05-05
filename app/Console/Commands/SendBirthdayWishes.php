<?php

namespace App\Console\Commands;

use App\Models\Anniversary;
use App\Models\AnniversaryWishLog;
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





// public function handle(WhatApiWhatsappService $sender, MediaManagerService $mm)
// {
//     $today = Carbon::now()->timezone(config('app.timezone'));
//     $month = (int) $today->format('m');
//     $day   = (int) $today->format('d');
//     $year  = (int) $today->format('Y');

//     $records = BirthdayRecord::query()
//         ->whereMonth('date_of_birth', $month)
//         ->whereDay('date_of_birth', $day)
//         ->with(['user.api'])
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

//         $name = $r->name ?? 'Dear';
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

//         $apiKey = optional($r->user)->api;
//         if (!$apiKey) {
//             $log->update(['status'=>'failed','response'=>'Api key missing']);
//             $this->line("❌ ApiKey missing: {$r->phone}");
//             continue;
//         }

//         $url = $apiKey->wishes_api;
//         if (!$url) {
//             $log->update(['status'=>'failed','response'=>'wishes_api missing']);
//             $this->line("❌ wishes_api missing: {$r->phone}");
//             continue;
//         }

//         // ✅ Absolute path
//         $absolutePath = public_path('asset/video/birthday-wish.mp4');

//         // ✅ Decide: upload only if > 5 days OR missing
//         $needUpload = false;

//         if (empty($apiKey->birthday_wish_media_manager_video_url)) {
//             $needUpload = true;
//         }

//         if (!$needUpload) {
//             if (empty($apiKey->birthday_wish_video_url_updated_on)) {
//                 $needUpload = true;
//             } else {
//                 try {
//                     $last = Carbon::parse($apiKey->birthday_wish_video_url_updated_on, config('app.timezone'));
//                     if ($last->diffInDays($today) > 5) {
//                         $needUpload = true;
//                     }
//                 } catch (\Throwable $e) {
//                     $needUpload = true;
//                 }
//             }
//         }

//         // ✅ Default: use old url
//         $liveUrl = $apiKey->birthday_wish_media_manager_video_url;

//         // ✅ Upload only when needed
//         if ($needUpload) {
//             $mmRes = $mm->upload($absolutePath);
//             $newUrl = ($mmRes['ok'] ?? false) ? ($mmRes['remote_url'] ?? null) : null;

//             if (!empty($newUrl)) {
//                 $liveUrl = $newUrl;

//                 $update = [
//                     'birthday_wish_media_manager_video_url' => $liveUrl,
//                     'birthday_wish_video_url_updated_on'    => $today->toDateString(),
//                 ];

//                 // ✅ save absolute path only if null
//                 if (empty($apiKey->birthday_wish_video_absolute_path)) {
//                     $update['birthday_wish_video_absolute_path'] = $absolutePath;
//                 }

//                 $apiKey->update($update);

//             } else {
//                 $log->update([
//                     'response' => 'MediaManager upload failed: '.(($mmRes['raw'] ?? '') ?: 'unknown'),
//                 ]);
//             }
//         } else {
//             // ✅ ensure absolute path saved if null (no upload case)
//             if (empty($apiKey->birthday_wish_video_absolute_path)) {
//                 $apiKey->update(['birthday_wish_video_absolute_path' => $absolutePath]);
//             }
//         }

//         // ✅ Send using media manager url (fallback local asset)
//         $publicVideo = $liveUrl ?: asset('asset/video/birthday-wish.mp4');

//         try {
//             $resp = $sender->sendBirthdayWish($r->phone, $url, $publicVideo, $name);

//             $log->update([
//                 'status'   => ($resp['ok'] ?? false) ? 'success' : 'failed',
//                 'response' => "HTTP ".($resp['status'] ?? 0)." | ".($resp['body'] ?? ''),
//             ]);

//             $this->line((($resp['ok'] ?? false) ? "✅ Sent: " : "❌ Failed: ").$r->phone);

//         } catch (\Throwable $e) {
//             $log->update([
//                 'status'   => 'failed',
//                 'response' => $e->getMessage(),
//             ]);
//             $this->line("❌ Exception: {$r->phone} - ".$e->getMessage());
//         }

//         sleep(6);
//     }

//     return self::SUCCESS;
// }



public function handle(WhatApiWhatsappService $sender, MediaManagerService $mm)
{
    // $today = Carbon::now()->timezone(config('app.timezone'));
    // $currentHour   = (int) $today->format('H');
    // $currentMinute = (int) $today->format('i');
    // $month = (int) $today->format('m');
    // $day   = (int) $today->format('d');
    // $year  = (int) $today->format('Y');

    // $records = BirthdayRecord::query()
    //     ->whereMonth('date_of_birth', $month)
    //     ->whereDay('date_of_birth', $day)
    //     ->with(['user.api'])
    //     ->get();


    $today = Carbon::now(config('app.timezone'));

    $month = (int) $today->format('m');
    $day   = (int) $today->format('d');
    $year  = (int) $today->format('Y');

    $startTime = $today->copy()->subHours(1)->format('H:i:s');
    $endTime   = $today->format('H:i:s');

    $records = BirthdayRecord::query()
        ->whereMonth('date_of_birth', $month)
        ->whereDay('date_of_birth', $day)
        ->whereNotNull('wish_time')
        ->whereBetween('wish_time', [$startTime, $endTime])
        ->with(['user.api'])
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

        $name = $r->name ?? 'Dear';
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

        // ✅ Absolute path
        $absolutePath = public_path('asset/video/birthday-wish.mp4');

        // ✅ Decide: upload only if > 5 days OR missing
        $needUpload = false;

        if (empty($apiKey->birthday_wish_media_manager_video_url)) {
            $needUpload = true;
        }

        if (!$needUpload) {
            if (empty($apiKey->birthday_wish_video_url_updated_on)) {
                $needUpload = true;
            } else {
                try {
                    $last = Carbon::parse($apiKey->birthday_wish_video_url_updated_on, config('app.timezone'));
                    if ($last->diffInDays($today) > 5) {
                        $needUpload = true;
                    }
                } catch (\Throwable $e) {
                    $needUpload = true;
                }
            }
        }

        // ✅ Default: use old url
        $liveUrl = $apiKey->birthday_wish_media_manager_video_url;

        // ✅ Upload only when needed
        if ($needUpload) {
            $mmRes = $mm->upload($absolutePath);
            $newUrl = ($mmRes['ok'] ?? false) ? ($mmRes['remote_url'] ?? null) : null;

            if (!empty($newUrl)) {
                $liveUrl = $newUrl;

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
                $log->update([
                    'response' => 'MediaManager upload failed: '.(($mmRes['raw'] ?? '') ?: 'unknown'),
                ]);
            }
        } else {
            // ✅ ensure absolute path saved if null (no upload case)
            if (empty($apiKey->birthday_wish_video_absolute_path)) {
                $apiKey->update(['birthday_wish_video_absolute_path' => $absolutePath]);
            }
        }

        // ✅ Send using media manager url (fallback local asset)
        $publicVideo = $liveUrl ?: asset('asset/video/birthday-wish.mp4');

        try {
            $resp = $sender->sendBirthdayWish($r->phone, $url, $publicVideo, $name);

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






    // $anniversaries = Anniversary::query()
    //     ->whereMonth('date_of_anniversary', $month)
    //     ->whereDay('date_of_anniversary', $day)
    //     ->whereRaw('HOUR(wish_time) = ?', [$currentHour])
    //     ->whereRaw('MINUTE(wish_time) = ?', [$currentMinute])
    //     ->with(['user.api'])
    //     ->get();


    $now = now('Asia/Kolkata');

    $startTime = $now->copy()->subHours(1)->format('H:i:s');
    $endTime   = $now->format('H:i:s');

    $anniversaries = Anniversary::query()
        ->whereMonth('date_of_anniversary', $month)
        ->whereDay('date_of_anniversary', $day)
        ->whereNotNull('wish_time')
        ->whereBetween('wish_time', [$startTime, $endTime])
        ->with(['user.api'])
        ->get();

    $this->info("Found: {$anniversaries->count()} anniversaries for ".$today->toDateString().' '.$today->format('H:i'));

    foreach ($anniversaries as $a) {

        $already = AnniversaryWishLog::where('anniversary_id', $a->id)
            ->where('wish_year', $year)
            ->exists();

        if ($already) {
            $this->line("Skip anniversary already sent: {$a->phone}");
            continue;
        }

        $name = $a->name ?? 'Dear';

        $message = "💐 Happy Anniversary {$name}! 🎉\nWishing you a lifetime of love, happiness & togetherness.\n\n— Real Victory Groups";

        $log = AnniversaryWishLog::create([
            'anniversary_id' => $a->id,
            'business_id'    => $a->business_id,
            'phone'          => $a->phone,
            'wish_date'      => $today->toDateString(),
            'wish_year'      => $year,
            'status'         => 'pending',
            'message'        => $message,
        ]);

        $apiKey = optional($a->user)->api;

        if (!$apiKey) {
            $log->update([
                'status'   => 'failed',
                'response' => 'Api key missing',
            ]);

            $this->line("❌ Anniversary ApiKey missing: {$a->phone}");
            continue;
        }

        $url = $apiKey->anniversary_api;

        if (!$url) {
            $log->update([
                'status'   => 'failed',
                'response' => 'anniversary_api missing',
            ]);

            $this->line("❌ Anniversary API missing: {$a->phone}");
            continue;
        }

        try {
            $resp = $sender->sendAnniversaryWish($a->phone, $url);

            $status = ($resp['ok'] ?? false) ? 'success' : 'failed';

            $log->update([
                'status'   => $status,
                'response' => "HTTP ".($resp['status'] ?? 0)." | ".($resp['body'] ?? ''),
            ]);

            $a->update([
                'status'   => $status,
                'response' => "HTTP ".($resp['status'] ?? 0)." | ".($resp['body'] ?? ''),
                'sent_at'  => $status === 'success' ? now() : null,
            ]);

            $this->line(($status === 'success' ? "✅ Anniversary Sent: " : "❌ Anniversary Failed: ").$a->phone);

        } catch (\Throwable $e) {

            $log->update([
                'status'   => 'failed',
                'response' => $e->getMessage(),
            ]);

            $a->update([
                'status'   => 'failed',
                'response' => $e->getMessage(),
            ]);

            $this->line("❌ Anniversary Exception: {$a->phone} - ".$e->getMessage());
        }

        sleep(6);
    }

    return self::SUCCESS;
}







}
