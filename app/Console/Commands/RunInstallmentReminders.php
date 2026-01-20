<?php

namespace App\Console\Commands;

use App\Models\InstallmentReminder;
use App\Services\WhatApiWhatsappService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RunInstallmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-installment-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'installment reminders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now(config('app.timezone'));

        // ✅ Month + Day match (same day & month)
        $records = InstallmentReminder::query()
            ->whereNotNull('date_of_birth')
            ->whereMonth('date_of_birth', $today->month)
            ->whereDay('date_of_birth', $today->day)
            ->get();

        $this->info("Found: {$records->count()} birthday records for ".$today->toDateString());

        // ✅ sender service resolve (change class to your actual service)
        $sender = app(\App\Services\WhatApiWhatsappService::class);

        foreach ($records as $r) {

            $phone = $r->phone ?? $r->contact_number ?? null;
            if (empty($phone)) {
                $this->line("❌ Skipped: phone missing (ID: {$r->id})");
                $r->update(['status' => 'failed']);
                continue;
            }

            $name = trim((string)($r->name ?? 'Dear'));
            $message = "🎉 Happy Birthday {$name}! 🎂\nGod bless you with health, happiness & success.\n\n— Real Victory Groups";

            // ✅ safe api url (null-safe)
            $url = data_get($r, 'user.api.wishes_api');

            if (empty($url)) {
                $this->line("❌ Skipped: wishes_api missing for {$phone} (ID: {$r->id})");
                $r->update([
                    'status'   => 'failed',
                    'response' => 'wishes_api missing',
                ]);
                continue;
            }

            try {
                // ✅ call your sender (adjust method signature if different)
                $resp = $sender->runInstallmentReminders($phone, $message, $url);

                $r->update([
                    'status'   => !empty($resp['ok']) ? 'success' : 'failed',
                    'response' => $resp['raw'] ?? ($resp['message'] ?? null),
                    'sent_at'  => !empty($resp['ok']) ? now() : null,
                ]);

                $this->line((!empty($resp['ok']) ? "✅ Sent: " : "❌ Failed: ").$phone);

            } catch (\Throwable $e) {

                $r->update([
                    'status'   => 'failed',
                    'response' => $e->getMessage(),
                ]);

                $this->line("❌ Exception: {$phone} - ".$e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
