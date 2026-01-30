<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DrivePdfJob;
use App\Models\InvoiceSend;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DriveScanPdfs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:drive-scan-pdfs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    // public function handle(\App\Services\GoogleDriveService $gdrive)
    // {
    //     $folderId = env('GOOGLE_DRIVE_FOLDER_ID');
    //     if (!$folderId) {
    //         $this->error("GOOGLE_DRIVE_FOLDER_ID missing");
    //         return 1;
    //     }

    //     // ✅ kis user ke under send karna hai (jis user ka ApiKey set hai)
    //     // simplest: env se set kar do
    //     $userId = (int) env('DRIVE_USER_ID', 0);
    //     if (!$userId) {
    //         $this->error("DRIVE_USER_ID missing (set in .env)");
    //         return 1;
    //     }

        

    //     $pageToken = null;
    //     $newCount = 0;

    //     do {
    //         $res = $gdrive->listPdfsInFolder($folderId, 100, $pageToken);
    //         $files = $res->getFiles();

    //         foreach ($files as $f) {
    //             $driveId   = $f->getId();
    //             $fileName  = $f->getName();
    //             $mimeType  = $f->getMimeType();
    //             $size      = (int) ($f->getSize() ?? 0);
    //             $modified  = $f->getModifiedTime();
    //             $modifiedRaw = $f->getModifiedTime(); // "2026-01-13T08:06:54.000Z"
    //             $modified = $modifiedRaw ? Carbon::parse($modifiedRaw)->setTimezone('Asia/Kolkata') : null;

    //            $fileName = $f->getName();
    //             $nameOnly = pathinfo($fileName, PATHINFO_FILENAME);

    //             // 10 to 13 digit (with or without 91)
    //             preg_match('/\b\d{10,13}\b/', $nameOnly, $m);

    //             $numberFromFile = $m[0] ?? env('DRIVE_SEND_DEFAULT_TO');


    //             // ✅ 1) duplicate check (DrivePdfJob)
    //             $exists = DrivePdfJob::where('drive_file_id', $driveId)->exists();
    //             if ($exists) continue;

    //             // ✅ 2) DrivePdfJob create
    //             $job = DrivePdfJob::create([
    //                 'drive_file_id'     => $driveId,
    //                 'file_name'         => $fileName,
    //                 'mime_type'         => $mimeType,
    //                 'size'              => $size,
    //                 'drive_modified_at' => $modified?->format('Y-m-d H:i:s'),
    //                 'to_number'         => $numberFromFile,
    //                 'caption'           => "Auto PDF: " . $fileName,
    //                 'status'            => 'pending',
    //             ]);

    //             // ✅ 3) InvoiceSend duplicate check (recommended)
    //             $already = InvoiceSend::where('user_id', $userId)
    //                 ->where('source', 'drive')
    //                 ->where('source_id', $driveId)
    //                 ->exists();

    //             if ($already) {
    //                 // job bhi pending na rahe
    //                 $job->update(['status' => 'sent', 'sent_at' => now()]);
    //                 continue;
    //             }

    //             // ✅ 4) Download PDF bytes from Drive
    //             try {
    //                 $bytes = $gdrive->downloadFile($driveId);
    //             } catch (\Throwable $e) {
    //                 $job->update([
    //                     'status'     => 'failed',
    //                     'attempts'   => 1,
    //                     'last_error' => $e->getMessage(),
    //                 ]);
    //                 continue;
    //             }

    //             // ✅ 5) Save to public disk so URL is public
    //             $base = Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) ?: 'pdf';
    //             $relPath = "autosend/drive/{$userId}/{$driveId}-{$base}.pdf";

    //             Storage::disk('public')->put($relPath, $bytes);
    //             $pdfUrl = Storage::disk('public')->url($relPath);

    //             // ✅ 6) Create InvoiceSend row (status uploaded) -> your existing sender will pick it
    //             InvoiceSend::create([
    //                 'user_id'         => $userId,
    //                 'channel'         => 'whatsapp',
    //                 'recipient_phone' => $numberFromFile,
    //                 'file_url'        => $pdfUrl,
    //                 'status'          => 'uploaded',
    //                 'meta'            => [
    //                     'drive_file_name' => $fileName,
    //                     'drive_rel_path'  => $relPath,
    //                 ],
    //                 'source'          => 'drive',
    //                 'source_id'       => $driveId,
    //             ]);

    //             // ✅ 7) Mark drive job ready (optional)
    //             $job->update([
    //                 'status' => 'queued', // ya 'pending' rehne do
    //             ]);

    //             $newCount++;
    //         }

    //         $pageToken = $res->getNextPageToken();
    //     } while ($pageToken);

    //     $this->info("Scan done. New files: {$newCount}");
    //     return 0;
    // }


    public function handle(\App\Services\GoogleDriveService $gdrive)
    {

        $users = User::whereNotNull('google_drive_folder_id')->get();

        $totalUsers = 0;
        $totalFiles = 0;
        foreach($users as $user){
            // $folderId = env('GOOGLE_DRIVE_FOLDER_ID');
            $folderId = $user->google_drive_folder_id;
            if (!$folderId) {
                $this->error("GOOGLE_DRIVE_FOLDER_ID missing");
                return 1;
            }

            // ✅ kis user ke under send karna hai (jis user ka ApiKey set hai)
            // simplest: env se set kar do
            // $userId = (int) env('DRIVE_USER_ID', 0);
            $userId = $user->id;
            if (!$userId) {
                $this->error("DRIVE_USER_ID missing (set in .env)");
                return 1;
            }

            $pageToken = null;
            $totalUsers++;
            $newCount = 0;

            do {
                $res = $gdrive->listPdfsInFolder($folderId, 100, $pageToken);
                $files = $res->getFiles();

                foreach ($files as $f) {
                    $driveId   = $f->getId();
                    $fileName  = $f->getName();
                    $mimeType  = $f->getMimeType();
                    $size      = (int) ($f->getSize() ?? 0);
                    $modified  = $f->getModifiedTime();
                    $modifiedRaw = $f->getModifiedTime(); // "2026-01-13T08:06:54.000Z"
                    $modified = $modifiedRaw ? Carbon::parse($modifiedRaw)->setTimezone('Asia/Kolkata') : null;

                $fileName = $f->getName();
                    $nameOnly = pathinfo($fileName, PATHINFO_FILENAME);

                    // 10 to 13 digit (with or without 91)
                    preg_match('/\b\d{10,13}\b/', $nameOnly, $m);

                    $numberFromFile = $m[0] ?? env('DRIVE_SEND_DEFAULT_TO');


                    // ✅ 1) duplicate check (DrivePdfJob)
                    $exists = DrivePdfJob::where('user_id', $userId)->where('drive_file_id', $driveId)->exists();
                    if ($exists) continue;

                    // ✅ 2) DrivePdfJob create
                    $job = DrivePdfJob::create([
                        'user_id'           => $userId,
                        'drive_file_id'     => $driveId,
                        'file_name'         => $fileName,
                        'mime_type'         => $mimeType,
                        'size'              => $size,
                        'drive_modified_at' => $modified?->format('Y-m-d H:i:s'),
                        'to_number'         => $numberFromFile,
                        'caption'           => "Auto PDF: " . $fileName,
                        'status'            => 'pending',
                    ]);

                    $newCount++;
                    $totalFiles++;


                    // ✅ 3) InvoiceSend duplicate check (recommended)
                    $already = InvoiceSend::where('user_id', $userId)
                        ->where('source', 'drive')
                        ->where('source_id', $driveId)
                        ->exists();

                    if ($already) {
                        // job bhi pending na rahe
                        $job->update(['status' => 'sent', 'sent_at' => now()]);
                        continue;
                    }

                    // ✅ 4) Download PDF bytes from Drive
                    try {
                        $bytes = $gdrive->downloadFile($driveId);
                    } catch (\Throwable $e) {
                        $job->update([
                            'status'     => 'failed',
                            'attempts'   => 1,
                            'last_error' => $e->getMessage(),
                        ]);
                        continue;
                    }

                    // ✅ 5) Save to public disk so URL is public
                    $base = Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) ?: 'pdf';
                    $relPath = "autosend/drive/{$userId}/{$driveId}-{$base}.pdf";

                    Storage::disk('public')->put($relPath, $bytes);
                    $pdfUrl = Storage::disk('public')->url($relPath);

                    // ✅ 6) Create InvoiceSend row (status uploaded) -> your existing sender will pick it
                    InvoiceSend::create([
                        'user_id'         => $userId,
                        'channel'         => 'whatsapp',
                        'recipient_phone' => $numberFromFile,
                        'file_url'        => $pdfUrl,
                        'status'          => 'uploaded',
                        'meta'            => [
                            'drive_file_name' => $fileName,
                            'drive_rel_path'  => $relPath,
                        ],
                        'source'          => 'drive',
                        'source_id'       => $driveId,
                    ]);

                    // ✅ 7) Mark drive job ready (optional)
                    $job->update([
                        'status' => 'queued', // ya 'pending' rehne do
                    ]);

                    $newCount++;
                }

                $pageToken = $res->getNextPageToken();
            } while ($pageToken);

            // $this->info("Scan done. New files: {$newCount}");
            // return 0;
        }
        // $this->info("All users scanned.");
        $this->info("Total users: {$totalUsers}");
        $this->info("Total new PDFs: {$totalFiles}");
        return 0;

        
    }


}
