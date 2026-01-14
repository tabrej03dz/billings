<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\Invoice;
use App\Models\InvoiceSend;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;




class NoBusinessWhatsappController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // user level API key (business_id = null)
        $apiKey = ApiKey::where('user_id', $user->id)
            ->whereNull('business_id')
            ->latest('id')
            ->first();

//        return view('no-business.whatsapp', compact('apiKey'));
        return view('no-business.whatsapp.drop', compact('apiKey'));
    }

    public function drop(){
        return view('no-business.whatsapp.drop');
    }

    public function saveApi(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'base_url' => ['required', 'string', 'max:255'],
            'wishes_api' => ['nullable', 'string', 'max:255'],
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

//    public function sendPdf(Request $request)
//    {
//        $user = $request->user();
//
//        $apiKey = ApiKey::where('user_id', $user->id)
//            ->whereNull('business_id')
//            ->latest('id')
//            ->first();
//
//        if (!$apiKey) {
//            return back()->withErrors(['api' => 'Please set your WhatsApp API first.']);
//        }
//
//        $data = $request->validate([
//            'phone' => ['required', 'string', 'max:20'],
//            'pdf'   => ['required', 'file', 'mimes:pdf', 'max:5120'], // 5MB
//        ]);
//
//        // 1) PDF ko store karo
//        $path = $request->file('pdf')->store('no-business-pdfs', 'public');
//        $pdfUrl = asset('storage/' . $path); // yahi URL WhatsApp provider ko doge
//
//        $phone = $data['phone'];
//
//        // 2) WhatsApp API call – isko tum apne provider ke hisaab se change karoge
//        // yaha generic example diya hai:
//        $endpoint = rtrim($apiKey->base_url, '/') . '/send-pdf'; // example path
//
//        $payload = [
//            'api_key'    => $apiKey->key,
//            'api_secret' => $apiKey->secret,
//            'phone'      => $phone,
//            'media_url'  => $pdfUrl,
//            'caption'    => 'Your document from Real Victory Groups',
//        ];
//
//        try {
//            $response = Http::asJson()->post($endpoint, $payload);
//
//            if ($response->failed()) {
//                return back()->withErrors([
//                    'whatsapp' => 'WhatsApp API error: ' . $response->body(),
//                ]);
//            }
//        } catch (\Throwable $e) {
//            return back()->withErrors([
//                'whatsapp' => 'Failed to call WhatsApp API: ' . $e->getMessage(),
//            ]);
//        }
//
//        return back()->with('success', 'PDF sent successfully on WhatsApp!');
//    }

//    public function sendInvoiceWhatsapp(Request $request)
//    {
//        $user = $request->user();
//
//        // 1) API config lao (user-level, no business)
//        $apiKey = ApiKey::where('user_id', $user->id)
//            ->whereNull('business_id')
//            ->latest('id')
//            ->first();
//
//        if (!$apiKey) {
//            return back()->withErrors([
//                'api' => 'Please set your WhatsApp API first from the right side form.',
//            ])->withInput();
//        }
//
//        // 2) Input validate
//        $data = $request->validate([
//            'phone' => ['required', 'string', 'max:20'],
//            'pdf'   => ['required', 'file', 'mimes:pdf', 'max:5120'], // 5 MB
//        ]);
//
//        // 3) Phone clean (sirf digits)
//        $phone = preg_replace('/\D+/', '', $data['phone']);
//
//        // 4) PDF ko store karo (public disk)
//        $path   = $request->file('pdf')->store('no-business-pdfs', 'public');
//        $pdfUrl = asset('storage/' . $path);
//        $pdfUrl = 'https://post.realvictorygroups.com/assets/logo1.png';
//
//        // 5) WhatsApp message text
//        $message = 'Your document from Real Victory Groups';
//
//        /**
//         * 6) whatapi.in base URL
//         * Database me sirf itna store karo:
//         * https://webhook.whatapi.in/webhook/6878c6bae3591ae351d0aae6
//         */
//        $baseUrl = $apiKey->base_url;
////        $baseUrl = 'https://webhook.whatapi.in/webhook/68886786de405dae96e96a73';
//
//        // Agar kisi ne galti se query ke sath save kar diya ho to usko strip kar do
//        $baseUrl = strtok($baseUrl, '?');
//
//        // 7) Final endpoint build karo (sample):
//        // https://webhook.whatapi.in/webhook/XXXX?number=91XXXXXXXXXX&image=MEDIA_URL&text=hi
//        $query = [
//            'number' => $phone,
//            'file'  => $pdfUrl,   // whatapi pe agar 'document' ya 'file' param chahiye ho to yaha change kar lena
//            'text'   => $message,
//        ];
//
//        $endpoint = $baseUrl . '?' . http_build_query($query);
//
//        // 8) WhatsApp API call (GET webhook)
//        $response = null;
//        $success  = false;
//        $status   = null;
//        $body     = null;
//
//        try {
//            $client = Http::timeout(20);
//
//            // local / development pe SSL verify off (curl error 60 fix)
//            if (app()->environment('local', 'development')) {
//                $client = $client->withoutVerifying();
//            }
//
//            $response = $client->get($endpoint);
//
//            $status   = $response->status();
//            $body     = $response->body();
//            $success  = $response->successful();
//        } catch (\Throwable $e) {
//            $body    = $e->getMessage();
//            $status  = null;
//            $success = false;
//        }
//
//        // 9) Log: kisne kya send kiya
//        InvoiceSend::create([
//            'business_id'         => null,
//            'user_id'             => $user->id,
//            'invoice_id'          => null,
//            'channel'             => 'whatsapp',
//            'recipient_phone'     => $phone,
//            'recipient_email'     => null,
//            'file_url'            => $pdfUrl,
//            'status'              => $success ? 'success' : 'failed',
//            'response_code'       => $status,
//            'provider_message_id' => null,
//            'error_message'       => $success ? null : \Illuminate\Support\Str::limit($body ?? '', 500),
//            'meta'                => [
//                'endpoint' => $endpoint,
//                'pdf_path' => $path,
//                'pdf_url'  => $pdfUrl,
//            ],
//            'sent_at'             => now(),
//        ]);
//
//        if (!$success) {
//            return back()->withErrors([
//                'whatsapp' => 'WhatsApp API error: ' . ($body ?? 'Unknown error'),
//            ])->withInput();
//        }
//
//        return back()->with('success', 'PDF WhatsApp par send ho gaya!');
//    }



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
//        dd($endpoint);
//        $query = [
//            'number' => $phone,
//            'file'   => $pdfUrl,
//            'text'   => $message,
//        ];
//
//        $endpoint = $baseUrl . '?' . http_build_query($query);

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






//    public function sendInvoiceWhatsapp(Request $request)
//    {
//        $user = $request->user();
//
//        // 1) API config lao (user-level, no business)
//        $apiKey = ApiKey::where('user_id', $user->id)
//            // ->whereNull('business_id')
//            ->latest('id')
//            ->first();
//
//        if (!$apiKey) {
//            return back()->withErrors([
//                'api' => 'Please set your WhatsApp API first from the right side form.',
//            ])->withInput();
//        }
//
//        // 2) Input validate
//        $data = $request->validate([
//            'phone' => ['nullable', 'string', 'max:20'],
//            'pdf'   => ['nullable', 'file', 'mimes:pdf', 'max:5120'],      // 5 MB
//            'excel' => ['nullable', 'file', 'mimes:xls,xlsx', 'max:5120'], // 5 MB
//        ]);
//
//        // Ensure: at least 1 file
//        if (!$request->hasFile('pdf') && !$request->hasFile('excel')) {
//            return back()->withErrors([
//                'file' => 'Please upload either a PDF or Excel file.',
//            ])->withInput();
//        }
//
//        // 3) Phone clean (sirf digits)
//        $phone = preg_replace('/\D+/', '', (string)($data['phone'] ?? ''));
//
//        // 4) File process:
//        //    - Agar Excel hai: usko store karo, Excel -> PDF convert karo
//        //    - Agar sirf PDF hai: PDF ko hi store karke bhejo
//        $storedPdfRelativePath = null;   // public disk relative path
//        $originalStoredPath    = null;   // log ke liye (Excel/PDF jo aya)
//
//        // ✅ NEW: original upload file name (user ke upload ka name)
//        $uploadedOriginalName  = null;   // e.g. "invoice_dec.xlsx" / "invoice.pdf"
//
//        if ($request->hasFile('excel')) {
//
//            $excelFile = $request->file('excel');
//
//            // ✅ original name
//            $uploadedOriginalName = $excelFile->getClientOriginalName();
//
//            // 4.a) Excel store karo
//            $excelPath = $excelFile->store('no-business-excels', 'public');
//            $originalStoredPath = $excelPath;
//
//            // 4.b) Excel -> PDF convert
//            $absoluteExcelPath = Storage::disk('public')->path($excelPath);
//
//            $storedPdfRelativePath = $this->convertExcelToPdf($absoluteExcelPath);
//            // e.g. returns "no-business-pdfs/invoice-1733222323.pdf"
//
//        } else {
//
//            $pdfFile = $request->file('pdf');
//
//            // ✅ original name
//            $uploadedOriginalName = $pdfFile->getClientOriginalName();
//
//            // 4.c) Simple PDF upload
//            $pdfPath = $pdfFile->store('no-business-pdfs', 'public');
//            $originalStoredPath = $pdfPath;
//            $storedPdfRelativePath = $pdfPath;
//        }
//
//        // 4.d) Public URL banao
//        $pdfUrl = asset('storage/' . $storedPdfRelativePath);
//
//        // 5) WhatsApp message text
//        $message = 'Your invoice from Real Victory Groups';
//        // (optional) file name include:
//        // $message .= $uploadedOriginalName ? "\nFile: {$uploadedOriginalName}" : '';
//
//        /**
//         * 6) whatapi.in base URL
//         * Database me sirf itna store karo:
//         * https://webhook.whatapi.in/webhook/6878c6bae3591ae351d0aae6
//         */
//        $baseUrl = $apiKey->base_url;
//
//        // Agar kisi ne galti se query ke sath save kar diya ho to usko strip kar do
//        $baseUrl = strtok($baseUrl, '?');
//
//        // 7) Final endpoint build karo
//        $query = [
//            'number' => $phone,
//            'file'   => $pdfUrl,
//            'text'   => $message,
//        ];
//
//        $endpoint = $baseUrl . '?' . http_build_query($query);
//
//        // 8) WhatsApp API call (GET webhook)
//        $success  = false;
//        $status   = null;
//        $body     = null;
//
//        try {
//            $client = Http::timeout(20);
//
//            // local / development pe SSL verify off (curl error 60 fix)
//            if (app()->environment('local', 'development')) {
//                $client = $client->withoutVerifying();
//            }
//
//            $response = $client->get($endpoint);
//
//            $status  = $response->status();
//            $body    = $response->body();
//            $success = $response->successful();
//
//        } catch (\Throwable $e) {
//            $body    = $e->getMessage();
//            $status  = null;
//            $success = false;
//        }
//
//        // 9) Log: kisne kya send kiya
//        InvoiceSend::create([
//            'business_id'         => null,
//            'user_id'             => $user->id,
//            'invoice_id'          => null,
//            'channel'             => 'whatsapp',
//            'recipient_phone'     => $phone,
//            'recipient_email'     => null,
//            'file_url'            => $pdfUrl,
//            'status'              => $success ? 'success' : 'failed',
//            'response_code'       => $status,
//            'provider_message_id' => null,
//            'error_message'       => $success ? null : Str::limit((string)($body ?? ''), 500),
//            'meta'                => [
//                'endpoint'        => $endpoint,
//                'source_path'     => $originalStoredPath,        // Excel ya PDF
//                'pdf_path'        => $storedPdfRelativePath,     // final PDF
//                'pdf_url'         => $pdfUrl,
//                'original_name'   => $uploadedOriginalName,      // ✅ user upload file name
//            ],
//            'sent_at'             => now(),
//        ]);
//
//        if (!$success) {
//            return back()->withErrors([
//                'whatsapp' => 'WhatsApp API error: ' . ($body ?? 'Unknown error'),
//            ])->withInput();
//        }
//
//        return back()->with('success', 'Invoice WhatsApp par send ho gaya!');
//    }












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



//    public function sendInvoiceWhatsappDropzone(Request $request)
//    {
//        $user = $request->user();
//
//        // 1) API config
//        $apiKey = ApiKey::where('user_id', $user->id)->latest('id')->first();
//        if (!$apiKey) {
//            return response()->json([
//                'success' => false,
//                'message' => 'WhatsApp API not set. Please set from API Settings.',
//            ], 422);
//        }
//
//        // 2) validate (single pdf)
//        $request->validate([
//            'pdf' => ['required', 'file', 'mimes:pdf', 'max:5120'], // 5MB
//            'phone' => ['nullable', 'string', 'max:20'],            // optional (agar file name se nikalna hai)
//        ]);
//
//        $pdfFile = $request->file('pdf');
//
//        // 3) phone decide:
//        // priority: request phone (agar user input de) else file name
//        $phone = preg_replace('/\D+/', '', (string)($request->phone ?? ''));
//
//        if (empty($phone)) {
//            $uploadedOriginalName = $pdfFile->getClientOriginalName();          // 9876543210.pdf
//            $filenameWithoutExt   = pathinfo($uploadedOriginalName, PATHINFO_FILENAME);
//            $phoneFromFile        = preg_replace('/\D+/', '', $filenameWithoutExt);
//
//            if (strlen($phoneFromFile) === 10) {
//                $phoneFromFile = '91' . $phoneFromFile;
//            }
//            $phone = $phoneFromFile;
//        }
//
//        if (empty($phone) || strlen($phone) < 10) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Valid phone not found. Provide phone input or name file as 10/12 digit number.',
//            ], 422);
//        }
//
//        // 4) store pdf
//        $pdfPath = $pdfFile->store('no-business-pdfs', 'public');
//        $pdfUrl  = asset('storage/' . $pdfPath);
//
//        // 5) build endpoint (whatapi)
//        $baseUrl = strtok($apiKey->base_url, '?');
//
//        $query = [
//            'number' => $phone,
////            'text'   => 'Your invoice from Real Victory Groups',
//            'pdf'    => $pdfUrl, // (tumhare provider spec ke hisab se)
//        ];
//        $endpoint = $baseUrl . '?' . http_build_query($query);
//
//        // 6) call whatsapp api
//        $response = null;
//        $success  = false;
//        $status   = null;
//        $body     = null;
//
//        // ⏳ IMPORTANT: rate-limit protection
//        sleep(3); // 👈 3 seconds delay before API hit
//        try {
//            $client = Http::timeout(20);
////            if (app()->environment('local', 'development')) {
////                $client = $client->withoutVerifying();
////            }
//            $response = $client->get($endpoint);
//
//            $status  = $response->status();
//            $body    = $response->body();
//            $success = $response->successful();
//        } catch (\Throwable $e) {
//            $body    = $e->getMessage();
//            $status  = null;
//            $success = false;
//        }
//
//
//        // 7) log
//        InvoiceSend::create([
//            'business_id'         => null,
//            'user_id'             => $user->id,
//            'invoice_id'          => null,
//            'channel'             => 'whatsapp',
//            'recipient_phone'     => $phone,
//            'recipient_email'     => null,
//            'file_url'            => $pdfUrl,
//            'status'              => $success ? 'success' : 'failed',
//            'response_code'       => $status,
//            'provider_message_id' => null,
//            'error_message'       => $success ? null : Str::limit($body ?? '', 500),
//            'meta'                => [
//                'endpoint'    => $endpoint,
//                'pdf_path'    => $pdfPath,
//                'pdf_url'     => $pdfUrl,
//                'source_name' => $pdfFile->getClientOriginalName(),
//            ],
//            'sent_at'             => now(),
//        ]);
//
//        if (!$success) {
//            return response()->json([
//                'success' => false,
//                'message' => 'WhatsApp API error: ' . ($body ?? 'Unknown error'),
//            ], 422);
//        }
//
//        return response()->json([
//            'success' => true,
//            'message' => 'Sent successfully',
//            'phone'   => $phone,
//            'pdf_url' => $pdfUrl,
//            'file_id' => null,
//        ]);
//    }

//    public function sendInvoiceWhatsappDropzone(Request $request)
//    {
//        $user = $request->user();
//
//        $apiKey = ApiKey::where('user_id', $user->id)->latest('id')->first();
//        if (!$apiKey) {
//            return response()->json([
//                'success' => false,
//                'message' => 'WhatsApp API not set. Please set from API Settings.',
//            ], 422);
//        }
//
//        $request->validate([
//            'pdf'   => ['required', 'file', 'mimes:pdf', 'max:5120'],
//            'phone' => ['nullable', 'string', 'max:20'],
//        ]);
//
//        $pdfFile = $request->file('pdf');
//
//        // phone resolve
//        $phone = preg_replace('/\D+/', '', (string)($request->phone ?? ''));
//        if ($phone === '') {
//            $name = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
//            $phone = preg_replace('/\D+/', '', $name);
//            if (strlen($phone) === 10) $phone = '91' . $phone;
//        }
//
//        if ($phone === '' || strlen($phone) < 10) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Valid phone not found. Provide phone input or name file as 10/12 digit number.',
//            ], 422);
//        }
//
//        // store pdf
//        $pdfPath = $pdfFile->store('no-business-pdfs', 'public');
//        $pdfUrl  = asset('storage/' . $pdfPath);
//
//        // ✅ build endpoint safely
//        $baseUrl = strtok($apiKey->base_url, '?');
//
//        $query = [
//            'number' => $phone,
//            // 'text' => 'Your invoice from Real Victory Groups',
//            'pdf'   => $pdfUrl,
//        ];
//
//        $endpoint = $baseUrl . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
//
//        // optional: log endpoint
//        \Log::info('WA_ENDPOINT', ['endpoint' => $endpoint]);
//
//        $success     = false;
//        $status      = null;
//        $body        = null;
//        $json        = null;
//        $providerMsg = null;
//
//        // rate limit
//        sleep(3);
//
//        try {
//            $client = Http::timeout(30)->connectTimeout(15);
//
//            // ✅ if your server has SSL issues, temporarily enable:
//            // $client = $client->withoutVerifying();
//
//            $response = $client->get($endpoint);
//
//            $status = $response->status();
//            $body   = $response->body();
//
//            // JSON parse (if possible)
//            $json = null;
//            try {
//                $json = $response->json();
//            } catch (\Throwable $e) {}
//
//            // ✅ true success detection:
//            // 1) HTTP 2xx
//            // 2) If JSON exists, ensure it doesn't say error/false
//            $success = $response->successful();
//
//            if (is_array($json)) {
//                // adjust keys based on your provider's response
//                if (($json['success'] ?? null) === false) $success = false;
//                if (($json['status']  ?? null) === 'error') $success = false;
//                if (isset($json['error']) && $json['error']) $success = false;
//
//                $providerMsg = $json['message_id'] ?? $json['id'] ?? $json['msgid'] ?? null;
//            }
//
//            Log::info('WA_RESPONSE', [
//                'http' => $status,
//                'success_final' => $success,
//                'body' => \Illuminate\Support\Str::limit((string)$body, 500),
//                'json' => $json,
//            ]);
//
//        } catch (\Throwable $e) {
//            $success = false;
//            $status  = null;
//            $body    = $e->getMessage();
//            Log::error('WA_EXCEPTION', ['msg' => $body]);
//        }
//
//        // ✅ DB log always stores response (even for success)
//        InvoiceSend::create([
//            'business_id'         => null,
//            'user_id'             => $user->id,
//            'invoice_id'          => null,
//            'channel'             => 'whatsapp',
//            'recipient_phone'     => $phone,
//            'recipient_email'     => null,
//            'file_url'            => $pdfUrl,
//            'status'              => $success ? 'success' : 'failed',
//            'response_code'       => $status,
//            'provider_message_id' => $providerMsg,
//            'error_message'       => $success ? null : \Illuminate\Support\Str::limit((string)$body, 500),
//            'meta'                => [
//                'endpoint'      => $endpoint,
//                'pdf_path'      => $pdfPath,
//                'pdf_url'       => $pdfUrl,
//                'source_name'   => $pdfFile->getClientOriginalName(),
//                'api_http'      => $status,
//                'api_body'      => \Illuminate\Support\Str::limit((string)$body, 2000),
//                'api_json'      => $json,
//            ],
//            'sent_at'             => now(),
//        ]);
//
//        if (!$success) {
//            return response()->json([
//                'success' => false,
//                'message' => 'WhatsApp API error: ' . ($body ?? 'Unknown error'),
//            ], 422);
//        }
//
//        return response()->json([
//            'success' => true,
//            'message' => 'Sent successfully',
//            'phone'   => $phone,
//            'pdf_url' => $pdfUrl,
//            'provider_message_id' => $providerMsg,
//        ]);
//    }


    public function sendInvoiceWhatsappDropzone(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        /**
         * ✅ Server-side sequential guarantee (per user)
         * - Redis/Cache lock required (aapke server par Redis already hai to best)
         * - 120 sec lock window (PDF upload + API call)
         */
        $lock = Cache::lock('wa_send_lock_user_' . $user->id, 120);

        if (!$lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Another PDF is being processed. Please wait and retry.',
            ], 429);
        }

        try {
            // 1) WhatsApp API config
            $apiKey = ApiKey::where('user_id', $user->id)->latest('id')->first();
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'WhatsApp API not set. Please set from API Settings.',
                ], 422);
            }

            // 2) validate
            $request->validate([
                'pdf'   => ['required', 'file', 'mimes:pdf', 'max:5120'], // 5MB
                'phone' => ['nullable', 'string', 'max:20'],
            ]);

            $pdfFile = $request->file('pdf');

            // 3) phone resolve: input > file-name
            $phone = preg_replace('/\D+/', '', (string)($request->input('phone', '')));

            if ($phone === '') {
                $name  = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
                $phone = preg_replace('/\D+/', '', $name);

                // 10 digit => add 91
                if (strlen($phone) === 10) {
                    $phone = '91' . $phone;
                }
            }

            if ($phone === '' || strlen($phone) < 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Valid phone not found. Provide phone input or name file as 10/12 digit number.',
                ], 422);
            }

            // 4) store pdf
            $pdfPath = $pdfFile->store('no-business-pdfs', 'public');
            $pdfUrl  = asset('storage/' . $pdfPath);

            // 5) build endpoint safely
            // base_url example: https://provider.com/send?key=xxx
            // we keep base path and add our own query
            $baseUrl = (string)$apiKey->base_url;
            $baseUrl = trim($baseUrl);
            $baseUrl = strtok($baseUrl, '?'); // remove existing query (safe)

            if (!$baseUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid WhatsApp API base url.',
                ], 422);
            }

            $query = [
                'number' => $phone,
                // 'text' => 'Your invoice from Real Victory Groups',
                'pdf'   => $pdfUrl,
            ];

            $endpoint = $baseUrl . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);

            Log::info('WA_ENDPOINT', [
                'user_id' => $user->id,
                'phone'   => $phone,
                'endpoint'=> $endpoint,
            ]);

            // 6) call provider
            $success     = false;
            $status      = null;
            $body        = null;
            $json        = null;
            $providerMsg = null;

            // optional provider rate-limit gap
            // usko kam/zyada apne provider ke hisab se karo
            // sleep(1);

            try {
                $client = Http::timeout(30)->connectTimeout(15);

                // ⚠️ If SSL issue on server, temporarily use:
                // $client = $client->withoutVerifying();

                $response = $client->get($endpoint);

                $status = $response->status();
                $body   = $response->body();

                // try parse json
                try {
                    $json = $response->json();
                } catch (\Throwable $e) {
                    $json = null;
                }

                // base success: HTTP 2xx
                $success = $response->successful();

                // if JSON has explicit failure flags, override
                if (is_array($json)) {
                    if (($json['success'] ?? null) === false) $success = false;
                    if (($json['status'] ?? null) === 'error') $success = false;
                    if (!empty($json['error'])) $success = false;

                    $providerMsg = $json['message_id']
                        ?? $json['id']
                        ?? $json['msgid']
                        ?? null;
                }

                Log::info('WA_RESPONSE', [
                    'user_id'        => $user->id,
                    'http'           => $status,
                    'success_final'  => $success,
                    'body'           => Str::limit((string)$body, 500),
                    'json'           => $json,
                ]);

            } catch (\Throwable $e) {
                $success = false;
                $status  = null;
                $body    = $e->getMessage();

                Log::error('WA_EXCEPTION', [
                    'user_id' => $user->id,
                    'msg'     => $body,
                ]);
            }

            // 7) always store log in DB (success/fail)
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
                'provider_message_id' => $providerMsg,
                'error_message'       => $success ? null : Str::limit((string)$body, 500),
                'meta'                => [
                    'endpoint'    => $endpoint,
                    'pdf_path'    => $pdfPath,
                    'pdf_url'     => $pdfUrl,
                    'source_name' => $pdfFile->getClientOriginalName(),
                    'api_http'    => $status,
                    'api_body'    => Str::limit((string)$body, 2000),
                    'api_json'    => $json,
                ],
                'sent_at'             => now(),
            ]);

            // 8) response to Dropzone
            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'WhatsApp API error: ' . ($body ?: 'Unknown error'),
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sent successfully',
                'phone'   => $phone,
                'pdf_url' => $pdfUrl,
                'provider_message_id' => $providerMsg,
            ]);

        } finally {
            optional($lock)->release();
        }
    }


}
