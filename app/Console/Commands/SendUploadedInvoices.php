<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InvoiceSendService;
use App\Models\InvoiceSend;

class SendUploadedInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-uploaded-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(InvoiceSendService $sender)
    {
        $this->info("Sending pending invoices (sent_at = NULL)...");

        InvoiceSend::query()
            ->whereNull('sent_at')
            ->where('status', 'uploaded') // optional but recommended
            ->orderBy('id')
            ->chunkById(50, function ($invoices) use ($sender) {

                foreach ($invoices as $invoiceSend) {
                    $this->info("-> InvoiceSend #{$invoiceSend->id}");

                    try {
                        // yahi main call
                        $result = $sender->sendSingleInvoice($invoiceSend);

                        $this->line(
                            "   sentOk=" . ($result['sentOk'] ?? 0) .
                            " sentFail=" . ($result['sentFail'] ?? 0)
                        );
                    } catch (\Throwable $e) {
                        $this->error("   ERROR: " . $e->getMessage());

                        // optional: mark failed
                        $invoiceSend->update([
                            'status' => 'failed',
                            'error'  => $e->getMessage(),
                        ]);
                    }
                }

            });

        $this->info("All pending invoices processed.");
        return self::SUCCESS;
    }
}
