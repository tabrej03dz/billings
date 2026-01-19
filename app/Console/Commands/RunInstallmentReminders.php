<?php

namespace App\Console\Commands;

use App\Models\InstallmentReminder;
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
        $today = Carbon::now()->timezone(config('app.timezone'));

        $records = InstallmentReminder::query()
            ->whereMonth('date_of_birth', $today)
            ->get();

        $this->info("Found: {$records->count()} installment reminders for ".$today->toDateString());

        foreach ($records as $r) {



            $name = $r->name ?: 'Dear';
            $message = "🎉 Happy Birthday {$name}! 🎂\nGod bless you with health, happiness & success.\n\n— Real Victory Groups";


            $url = $r->user->api->wishes_api;

            try {
                $resp = $sender->runInstallmentReminders($r->phone, $url);

                $r->update([
                    'status'   => $resp['ok'] ? 'success' : 'failed',
                ]);

                $this->line(($resp['ok'] ? "✅ Sent: " : "❌ Failed: ").$r->phone);

            } catch (\Throwable $e) {
                $log->update([
                    'status'   => 'failed',
                    'response' => $e->getMessage(),
                ]);
                $this->line("❌ Exception: {$r->phone} - ".$e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
