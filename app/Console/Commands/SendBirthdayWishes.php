<?php

namespace App\Console\Commands;

use App\Models\BirthdayRecord;
use App\Models\BirthdayWishLog;
use App\Services\WhatApiWhatsappService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendBirthdayWishes extends Command
{
    protected $signature = 'app:send-birthday-wishes';
    protected $description = 'Send automatic birthday wishes on WhatsApp';

    public function handle(WhatApiWhatsappService $sender)
    {
        $today = Carbon::now()->timezone(config('app.timezone'));
        $month = (int) $today->format('m');
        $day   = (int) $today->format('d');
        $year  = (int) $today->format('Y');

        $records = BirthdayRecord::query()
            ->whereMonth('date_of_birth', $month)
            ->whereDay('date_of_birth', $day)
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
            $url = $r->user->api->wishes_api;
//            if (!$url){
//                $url = 'https://webhooks.1automations.com/webhook/694e1e278849903df2fd8ff4';
//            }
            try {
                $resp = $sender->sendBirthdayWish($r->phone, $url);

                $log->update([
                    'status'   => $resp['ok'] ? 'success' : 'failed',
                    'response' => "HTTP {$resp['status']} | ".$resp['body'],
                ]);

                $this->line(($resp['ok'] ? "✅ Sent: " : "❌ Failed: ").$r->phone);

            } catch (\Throwable $e) {
                $log->update([
                    'status'   => 'failed',
                    'response' => $e->getMessage(),
                ]);
                $this->line("❌ Exception: {$r->phone} - ".$e->getMessage());
            }

             sleep(3);   // 3 seconds delay before next API call
        }

        return self::SUCCESS;
    }
}
