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
        $today = Carbon::now(config('app.timezone'))->toDateString();

        $records = InstallmentReminder::query()
            ->whereDate('reminder_date', $today)->where('status', '!=', 'success')
            ->get();

        $this->info("Found: {$records->count()} birthday records for ".$today);

        // ✅ sender service resolve (change class to your actual service)
        $sender = app(\App\Services\WhatApiWhatsappService::class);

        foreach ($records as $r) {

            $phone = $r->phone ?? $r->contact_number ?? null;

            if ($phone) {

                // sirf digits rakho
                $phone = preg_replace('/\D+/', '', $phone);

                // agar 91 se start nahi hota → 91 prefix karo
                if (!str_starts_with($phone, '91')) {
                    $phone = '91' . $phone;
                }
            }
            if (empty($phone)) {
                $this->line("❌ Skipped: phone missing (ID: {$r->id})");
                $r->update(['status' => 'failed']);
                continue;
            }

            $name = trim((string)($r->name ?? 'Dear'));
            $message = "🎉 Happy Birthday {$name}! 🎂\nGod bless you with health, happiness & success.\n\n— Real Victory Groups";

            // ✅ safe api url (null-safe)
//            $url = "https://webhooks.1automations.com/webhook/694e1e278849903df2fd8ff4";
            // ?number=917753800444&AcNo=1234&amount=7272&date15/05/26
            $url = "https://webhooks.1automations.com/webhook/696f108302e28c7ee4b83fbf";
            $snmeNumber = $r->snme_number;
            $amount = $r->installment_amount;
            $date = $r->installment_date;

            if (empty($url)) {
                $this->line("❌ Skipped: installment  missing for {$phone} (ID: {$r->id})");
                $r->update([
                    'status'   => 'failed',
//                    'response' => 'wishes_api missing',
                ]);
                continue;
            }

            try {
                // ✅ call your sender (adjust method signature if different)
                $resp = $sender->runInstallmentReminders($phone, $message, $url, $snmeNumber, $amount, $date);

                $r->update([
                    'status'   => !empty($resp['ok']) ? 'success' : 'failed',
//                    'response' => $resp['raw'] ?? ($resp['message'] ?? null),
//                    'sent_at'  => !empty($resp['ok']) ? now() : null,
                ]);

                $this->line((!empty($resp['ok']) ? "✅ Sent: " : "❌ Failed: ").$phone);

            } catch (\Throwable $e) {

                $r->update([
                    'status'   => 'failed',
//                    'response' => $e->getMessage(),
                ]);

                $this->line("❌ Exception: {$phone} - ".$e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
