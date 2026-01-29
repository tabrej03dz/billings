<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DriveDispatchPending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:drive-dispatch-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
{
    $jobs = \App\Models\DrivePdfJob::where('status','pending')
        ->where('attempts','<',3)
        ->orderBy('id')
        ->limit(10)
        ->get();

    foreach ($jobs as $j) {
        $j->update(['status' => 'sending']);
        \App\Jobs\SendDrivePdfToWhatsappJob::dispatch($j->id);
    }

    $this->info("Dispatched: ".$jobs->count());
    return 0;
}

}
