<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class SendDrivePdfToWhatsappJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(\App\Services\GoogleDriveService $gdrive)
    {
        $job = \App\Models\DrivePdfJob::find($this->jobId);
        if (!$job) return;

        try {
            // 1) download from drive
            $bytes = $gdrive->downloadFile($job->drive_file_id);

            // 2) save locally (temp)
            $localPath = storage_path('app/drive_tmp/'.$job->drive_file_id.'.pdf');
            if (!is_dir(dirname($localPath))) mkdir(dirname($localPath), 0775, true);
            file_put_contents($localPath, $bytes);

            // 3) make public url OR upload to WhatsApp media endpoint
            // EASIEST: store in public disk and generate URL
            $publicRel = 'autosend/'.$job->drive_file_id.'.pdf';
            Storage::disk('public')->put($publicRel, $bytes);
            $publicUrl = Storage::disk('public')->url($publicRel);

            // 4) call your existing WhatsApp send PDF function
            // Example:
            // app(WhatsappService::class)->sendPdf($job->to_number, $publicUrl, $job->caption);

            $ok = app(\App\Services\WhatApiWhatsappService::class)
                ->sendMediaToNumber($job->to_number, false, $job->size, $publicUrl, $job->caption);

            if (!$ok) {
                throw new \Exception("WhatsApp send failed");
            }

            // 5) mark sent
            $job->update([
                'status' => 'sent',
                'sent_at' => now(),
                'last_error' => null,
            ]);

        } catch (\Throwable $e) {
            $job->update([
                'status' => 'failed',
                'attempts' => $job->attempts + 1,
                'last_error' => $e->getMessage(),
            ]);
            throw $e; // queue retry policy use kar rahe ho to
        }
    }

}
