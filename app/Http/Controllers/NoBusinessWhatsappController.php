<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\Invoice;
use App\Models\InvoiceSend;
use App\Services\InvoiceSendService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;



class NoBusinessWhatsappController extends Controller
{

    public function index(Request $request, InvoiceSendService $sender)
    {
        $user = $request->user();

        // ✅ send uploaded first (same as invoice-sends)
        $result = $sender->sendUploadedForUser($user);

        if ($result['message']) {
            session()->flash('info', $result['message']);
        } else {
            if ($result['sentOk'] > 0) {
                session()->flash('success', "{$result['sentOk']} PDF sent successfully" . ($result['sentFail'] ? ", {$result['sentFail']} failed." : "."));
            } elseif ($result['sentFail'] > 0) {
                session()->flash('error', "{$result['sentFail']} PDF send failed. Please retry.");
            }
        }

        // user level API key (business_id = null)
        $apiKey = ApiKey::where('user_id', $user->id)
            ->whereNull('business_id')
            ->latest('id')
            ->first();

        return view('no-business.whatsapp.drop', compact('apiKey'));
    }
    public function drop(){

        $authUser = auth()->user();
        $uploadedPdfs = InvoiceSend::query()
            ->where('status', 'uploaded')
            ->where('user_id', $authUser->id)
            ->where('channel', 'whatsapp') // (optional) agar sirf whatsapp wale upload hote hain
            ->get();

        if ($uploadedPdfs->count() > 0) {

            // ✅ avoid double run / parallel (optional but recommended)
            $lock = Cache::lock("invoice_send_uploaded_user_{$authUser->id}", 180); // 3 min
            if ($lock->get()) {
                try {
                    $apiKey = ApiKey::where('user_id', $authUser->id)->latest('id')->first();
                    if (!$apiKey) {
                        // mark all uploaded as failed (so it doesn't keep trying silently)
                        InvoiceSend::where('status', 'uploaded')
                            ->where('user_id', $authUser->id)
                            ->update([
                                'status'        => 'failed',
                                'error_message' => 'WhatsApp API not set. Please set from API Settings.',
                            ]);
                        return redirect()->route('no-business.api-settings')
                            ->with('error', 'WhatsApp API not set. Please set from API Settings.');
                    }

                    $baseUrl = strtok($apiKey->base_url, '?');

                    $sentOk = 0;
                    $sentFail = 0;

                    foreach ($uploadedPdfs as $pdf) {
                        $phone  = preg_replace('/\D+/', '', (string) $pdf->recipient_phone);
                        $pdfUrl = (string) $pdf->file_url;

                        if (!$phone || !$pdfUrl) {
                            $pdf->update([
                                'status'        => 'failed',
                                'error_message' => 'Phone or PDF URL missing.',
                            ]);
                            $sentFail++;
                            continue;
                        }

                        // mark sending
                        $pdf->update([
                            'status'        => 'sending',
                            'error_message' => null,
                            'response_code' => null,
                        ]);

                        $status = null;
                        $body   = null;
                        $ok     = false;

                        try {
                            $resp = Http::timeout(30)
                                ->retry(2, 200) // optional retry
                                ->get($baseUrl, [
                                    'number' => $phone,
                                    'pdf'    => $pdfUrl,
                                ]);

                            $status = $resp->status();
                            $body   = $resp->body();
                            $ok     = $resp->successful();
                        } catch (\Throwable $e) {
                            $body = $e->getMessage();
                            $ok   = false;
                        }

                        $endpoint = $baseUrl . '?' . http_build_query(['number' => $phone, 'pdf' => $pdfUrl]);

                        if ($ok) {
                            $pdf->update([
                                'status'        => 'success',
                                'response_code' => $status,
                                'sent_at'       => now(),
                                'meta'          => array_merge((array) ($pdf->meta ?? []), [
                                    'endpoint' => $endpoint,
                                    'response' => Str::limit((string) $body, 1000),
                                ]),
                            ]);
                            $sentOk++;
                            continue;
                        }

                        $pdf->update([
                            'status'        => 'failed',
                            'response_code' => $status,
                            'error_message' => Str::limit((string) ($body ?? 'Unknown error'), 500),
                            'meta'          => array_merge((array) ($pdf->meta ?? []), [
                                'endpoint' => $endpoint,
                            ]),
                        ]);
                        $sentFail++;
                    }

                    if ($sentOk > 0) {
                        session()->flash('success', "{$sentOk} PDF sent successfully" . ($sentFail ? ", {$sentFail} failed." : "."));
                    } elseif ($sentFail > 0) {
                        session()->flash('error', "{$sentFail} PDF send failed. Please retry.");
                    }

                } finally {
                    optional($lock)->release();
                }
            } else {
                session()->flash('info', 'Sending already in progress. Please wait.');
            }
        }

        return view('no-business.whatsapp.drop');
    }

    public function saveApi(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'base_url' => ['required', 'string', 'max:255'],
            'wishes_api' => ['nullable', 'string', 'max:255'],
            'installment_reminder_api' => ['nullable', 'string', 'max:255'],
            'wish_at' => ['nullable'],
            'key'      => ['nullable', 'string', 'max:255'],
            'secret'   => ['nullable', 'string', 'max:255'],
        ]);

        $apiKey = ApiKey::updateOrCreate(
            [
                'user_id'     => $user->id,
                'business_id' => null,
            ],
            $data + [
                'user_id'     => $user->id,
                'business_id' => null,
            ]
        );


        return redirect()
            ->route('no-business.whatsapp')
            ->with('success', 'WhatsApp API settings saved successfully.');
    }


    public function sendInvoiceWhatsapp(Request $request)
    {
        $user = $request->user();


        // 1) API config lao (user-level, no business)
        $apiKey = ApiKey::where('user_id', $user->id)
//            ->whereNull('business_id')
            ->latest('id')
            ->first();

        if (!$apiKey) {
            return back()->withErrors([
                'api' => 'Please set your WhatsApp API first from the right side form.',
            ])->withInput();
        }

        // 2) Input validate
        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
            'pdf'   => ['nullable', 'file', 'mimes:pdf', 'max:5120'],             // 5 MB
            'excel' => ['nullable', 'file', 'mimes:xls,xlsx', 'max:5120'],        // 5 MB
        ]);

        // Ensure: at least 1 file
        if (!$request->hasFile('pdf') && !$request->hasFile('excel')) {
            return back()->withErrors([
                'file' => 'Please upload either a PDF or Excel file.',
            ])->withInput();
        }

        // 3) Phone clean (sirf digits)
        $phone = preg_replace('/\D+/', '', $data['phone']);

        // 4) File process:
        //    - Agar Excel hai: usko store karo, Excel -> PDF convert karo
        //    - Agar sirf PDF hai: PDF ko hi store karke bhejo
        $storedPdfRelativePath = null;   // public disk relative path
        $originalStoredPath    = null;   // log ke liye (Excel/PDF jo aya)

        if ($request->hasFile('excel')) {

            // 4.a) Excel store karo
            $excelPath = $request->file('excel')->store('no-business-excels', 'public');
            $originalStoredPath = $excelPath;

            // 4.b) Excel -> PDF convert
            $absoluteExcelPath = Storage::disk('public')->path($excelPath);

            $storedPdfRelativePath = $this->convertExcelToPdf($absoluteExcelPath);
            // e.g. returns something like "no-business-pdfs/invoice-1733222323.pdf"

        } else {
            $pdfFile = $request->file('pdf');

            // 1️⃣ Original file name (with extension)
            $uploadedOriginalName = $pdfFile->getClientOriginalName();
            // e.g. "9876543210.pdf" OR "919876543210.pdf"

            // 2️⃣ Extension hatao
            $filenameWithoutExt = pathinfo($uploadedOriginalName, PATHINFO_FILENAME);
            // result: "9876543210" OR "919876543210"

            // 3️⃣ Sirf digits rakho (safety)
            $phoneFromFile = preg_replace('/\D+/', '', $filenameWithoutExt);

            // 4️⃣ 10 digit hua to 91 add karo
            if (strlen($phoneFromFile) === 10) {
                $phoneFromFile = '91' . $phoneFromFile;
            }

            // 5️⃣ Final phone number (API ke liye)
            $phone = $phoneFromFile;



            // 4.c) Simple PDF upload
            $pdfPath = $pdfFile->store('no-business-pdfs', 'public');
            $originalStoredPath = $pdfPath;
            $storedPdfRelativePath = $pdfPath;
        }

        // 4.d) Public URL banao
        $pdfUrl = asset('storage/' . $storedPdfRelativePath);

        // NOTE: pehle tum yaha logo hard-code kar rahe the, wo hata diya:
//         $pdfUrl = 'https://post.realvictorygroups.com/storage/images/2025-12-20/Jewellery/BrckLBbXfHxGdR8cOA8xj6jKxPJAaR77Dr0waMZM.jpg';

        // 5) WhatsApp message text
        $message = 'Your invoice from Real Victory Groups';

        /**
         * 6) whatapi.in base URL
         * Database me sirf itna store karo:
         * https://webhook.whatapi.in/webhook/6878c6bae3591ae351d0aae6
         */
        $baseUrl = $apiKey->base_url;
//        $baseUrl = 'https://webhooks.1automations.com/webhook/6946555ae60589cc861361d7';

        // Agar kisi ne galti se query ke sath save kar diya ho to usko strip kar do
        $baseUrl = strtok($baseUrl, '?');

        // 7) Final endpoint build karo
        // whatapi spec ke hisab se param ka naam 'file' rakha hai (document send)

        $query = [
            'number' => $phone,
            'text'   => $message,
            'pdf'   => $pdfUrl,
        ];
        $endpoint = $baseUrl.'?'.http_build_query($query);


        // 8) WhatsApp API call (GET webhook)
        $response = null;
        $success  = false;
        $status   = null;
        $body     = null;

        try {
            $client = Http::timeout(20);

            // local / development pe SSL verify off (curl error 60 fix)
            if (app()->environment('local', 'development')) {
                $client = $client->withoutVerifying();
            }

            $response = $client->get($endpoint);

            $status   = $response->status();
            $body     = $response->body();
            $success  = $response->successful();
        } catch (\Throwable $e) {
            $body    = $e->getMessage();
            $status  = null;
            $success = false;
        }

        // 9) Log: kisne kya send kiya
        InvoiceSend::create([
            'business_id'         => null,
            'user_id'             => $user->id,
            'invoice_id'          => null,
            'channel'             => 'whatsapp',
            'recipient_phone'     => $phone,
            'recipient_email'     => null,
            'file_url'            => $pdfUrl,
            'status'              => $success ? 'success' : 'failed',
            'response_code'       => $status,
            'provider_message_id' => null,
            'error_message'       => $success ? null : Str::limit($body ?? '', 500),
            'meta'                => [
                'endpoint'     => $endpoint,
                'source_path'  => $originalStoredPath,      // Excel ya PDF
                'pdf_path'     => $storedPdfRelativePath,   // final PDF
                'pdf_url'      => $pdfUrl,
            ],
            'sent_at'             => now(),
        ]);

        if (!$success) {
            return back()->withErrors([
                'whatsapp' => 'WhatsApp API error: ' . ($body ?? 'Unknown error'),
            ])->withInput();
        }

        return back()->with('success', 'Invoice WhatsApp par send ho gaya!');
    }


    protected function convertExcelToPdf(string $absoluteExcelPath): string
    {
        // 1) Excel read karo
        $spreadsheet = IOFactory::load($absoluteExcelPath);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, true); // array of rows

        // 2) Yaha tum apna mapping kar sakte ho
        // Example: header row + items nikal lo
        $headerRow = $rows[1] ?? [];
        $dataRows  = array_slice($rows, 1); // row 2 se aage

        // 3) View render karo (invoice style)
        $html = view('no-business.invoice-from-excel', [
            'header' => $headerRow,
            'rows'   => $dataRows,
        ])->render();

        // 4) PDF generate karo
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

        // 5) Storage path decide karo
        $fileName = 'invoice-' . time() . '-' . uniqid() . '.pdf';
        $relativePath = 'no-business-pdfs/' . $fileName;

        Storage::disk('public')->put($relativePath, $pdf->output());

        return $relativePath;
    }


    public function apiSettings(Request $request)
    {
        $user = auth()->user();

        $apiKey = ApiKey::withoutGlobalScopes()->where('user_id', $user->id)->first();

        return view('no_business.api_settings', compact('apiKey'));
    }


    public function uploadPdfQueue(Request $request)
    {
        $user = $request->user();


        $request->validate([
            'pdf'   => ['required', 'file', 'mimes:pdf', 'max:5120'], // 5MB
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $pdfFile = $request->file('pdf');

        // ✅ phone decide: input > filename
        $phone = preg_replace('/\D+/', '', (string)($request->phone ?? ''));

        if (empty($phone)) {
            $originalName = $pdfFile->getClientOriginalName(); // 9876543210.pdf
            $nameNoExt    = pathinfo($originalName, PATHINFO_FILENAME);
            $phoneFromFile = preg_replace('/\D+/', '', $nameNoExt);

            if (strlen($phoneFromFile) === 10) {
                $phoneFromFile = '91' . $phoneFromFile;
            }
            $phone = $phoneFromFile;
        }

        if (empty($phone) || strlen($phone) < 10) {
            return response()->json([
                'success' => false,
                'message' => 'Valid phone not found. Provide phone input or name file as 10/12 digit number.',
            ], 422);
        }

        // ✅ store pdf
        $pdfPath = $pdfFile->store('no-business-pdfs', 'public');
        $pdfUrl  = asset('storage/' . $pdfPath);

        // ✅ ONLY QUEUE RECORD (no API call)
        $row = InvoiceSend::create([
            'business_id'         => null,
            'user_id'             => $user->id,
            'invoice_id'          => null,
            'channel'             => 'whatsapp',
            'recipient_phone'     => $phone,
            'recipient_email'     => null,
            'file_url'            => $pdfUrl,
            'status' => 'uploaded',    // ✅ important
            'response_code'       => null,
            'provider_message_id' => null,
            'error_message'       => null,
            'meta'                => [
                'pdf_path'    => $pdfPath,
                'pdf_url'     => $pdfUrl,
                'source_name' => $pdfFile->getClientOriginalName(),
            ],
            'sent_at'             => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Uploaded & queued',
            'id'      => $row->id,
            'phone'   => $phone,
            'pdf_url' => $pdfUrl,
        ]);
    }


    public function sendQueuedPdfs(Request $request)
    {
        $user = $request->user();

        $apiKey = ApiKey::where('user_id', $user->id)->latest('id')->first();
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp API not set. Please set from API Settings.',
            ], 422);
        }

        // ✅ avoid double click / parallel sending
        $lock = Cache::lock("wa_send_user_{$user->id}", 180); // 3 min
        if (!$lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Sending already in progress. Please wait.',
            ], 429);
        }

        try {
            $limit = (int) $request->input('limit', 50);
            $limit = max(1, min(200, $limit));

            $rows = InvoiceSend::query()
                ->where('user_id', $user->id)
                ->where('channel', 'whatsapp')
                ->where('status', 'queued')
                ->orderBy('id')
                ->limit($limit)
                ->get();

            if ($rows->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No queued PDFs found.',
                    'sent'    => 0,
                    'failed'  => 0,
                ]);
            }

            $baseUrl = strtok($apiKey->base_url, '?');

            $sent = 0;
            $failed = 0;
            $results = [];

            foreach ($rows as $row) {

                // mark sending
                $row->update([
                    'status' => 'sending',
                    'error_message' => null,
                    'response_code' => null,
                ]);

                $phone = preg_replace('/\D+/', '', (string)$row->recipient_phone);
                $pdfUrl = (string)$row->file_url;

                $endpoint = $baseUrl . '?' . http_build_query([
                        'number' => $phone,
                        'pdf'    => $pdfUrl,
                    ]);

                // ✅ rate limit protection
                sleep(3);

                $status = null;
                $body   = null;
                $ok     = false;

                try {
                    $resp = Http::timeout(30)->get($endpoint);
                    $status = $resp->status();
                    $body   = $resp->body();
                    $ok     = $resp->successful();
                } catch (\Throwable $e) {
                    $body = $e->getMessage();
                    $ok   = false;
                }

                if ($ok) {
                    $row->update([
                        'status'        => 'success',
                        'response_code' => $status,
                        'sent_at'       => now(),
                        'meta'          => array_merge((array)($row->meta ?? []), [
                            'endpoint' => $endpoint,
                        ]),
                    ]);
                    $sent++;
                    $results[] = ['id' => $row->id, 'ok' => true, 'phone' => $phone];
                } else {
                    $row->update([
                        'status'        => 'failed',
                        'response_code' => $status,
                        'error_message' => Str::limit((string)($body ?? 'Unknown error'), 500),
                        'sent_at'       => now(),
                        'meta'          => array_merge((array)($row->meta ?? []), [
                            'endpoint' => $endpoint,
                        ]),
                    ]);
                    $failed++;
                    $results[] = ['id' => $row->id, 'ok' => false, 'phone' => $phone];
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Done. Sent: {$sent}, Failed: {$failed}",
                'sent'    => $sent,
                'failed'  => $failed,
                'results' => $results,
            ]);

        } finally {
            optional($lock)->release();
        }
    }

    public function sendPdfRetry(InvoiceSend $invoice)
    {
        $user = auth()->user();

        $apiKey = ApiKey::where('user_id', $user->id)->latest('id')->first();
        if (!$apiKey) {
            return back()->with('error', 'WhatsApp API not set. Please set from API Settings.');
        }

        // ✅ Double click / parallel send avoid
        $lock = Cache::lock("wa_send_invoice_{$invoice->id}", 180);
        if (!$lock->get()) {
            return back()->with('error', 'This invoice is already sending. Please wait.');
        }

        try {
            $baseUrl = strtok($apiKey->base_url, '?');

            $phone  = preg_replace('/\D+/', '', (string) $invoice->recipient_phone);
            $pdfUrl = (string) $invoice->file_url;

            if (!$phone || !$pdfUrl) {
                $invoice->update([
                    'status'        => 'failed',
                    'error_message' => 'Phone or PDF URL missing.',
                ]);
                return back()->with('error', 'Phone or PDF URL missing.');
            }

            // mark sending
            $invoice->update([
                'status'        => 'sending',
                'error_message' => null,
                'response_code' => null,
            ]);

            $status = null;
            $body   = null;
            $ok     = false;

            try {
                // ✅ Best practice: params separately
                $resp = Http::timeout(30)->get($baseUrl, [
                    'number' => $phone,
                    'pdf'    => $pdfUrl,
                ]);

                $status = $resp->status();
                $body   = $resp->body();
                $ok     = $resp->successful(); // 200-299
            } catch (\Throwable $e) {
                $body = $e->getMessage();
                $ok   = false;
            }

            $endpoint = $baseUrl . '?' . http_build_query(['number' => $phone, 'pdf' => $pdfUrl]);

            if ($ok) {
                $invoice->update([
                    'status'        => 'success',
                    'response_code' => $status,
                    'sent_at'       => now(),
                    'meta'          => array_merge((array) ($invoice->meta ?? []), [
                        'endpoint' => $endpoint,
                        'response' => Str::limit((string) $body, 1000),
                    ]),
                ]);

                return back()->with('success', 'PDF sent successfully.');
            }

            $invoice->update([
                'status'        => 'failed',
                'response_code' => $status,
                'error_message' => Str::limit((string) ($body ?? 'Unknown error'), 500),
                'meta'          => array_merge((array) ($invoice->meta ?? []), [
                    'endpoint' => $endpoint,
                ]),
            ]);

            return back()->with('error', 'PDF send failed. Please retry.');
        } finally {
            optional($lock)->release();
        }
    }
}
