<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\Invoice;
use App\Models\InvoiceSend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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

        return view('no-business.whatsapp', compact('apiKey'));
    }

    public function saveApi(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'base_url' => ['required', 'string', 'max:255'],
            'key'      => ['nullable', 'string', 'max:255'],
            'secret'   => ['nullable', 'string', 'max:255'],
        ]);

        ApiKey::updateOrCreate(
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

    public function sendInvoiceWhatsapp(Request $request)
    {
        $user = $request->user();

        // 1) API config lao (user-level, no business)
        $apiKey = ApiKey::where('user_id', $user->id)
            ->whereNull('business_id')
            ->latest('id')
            ->first();

        if (!$apiKey) {
            return back()->withErrors([
                'api' => 'Please set your WhatsApp API first from the right side form.',
            ])->withInput();
        }

        // 2) Input validate
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'pdf'   => ['required', 'file', 'mimes:pdf', 'max:5120'], // 5 MB
        ]);

        // 3) Phone clean (sirf digits)
        $phone = preg_replace('/\D+/', '', $data['phone']);

        // 4) PDF ko store karo (public disk)
        $path   = $request->file('pdf')->store('no-business-pdfs', 'public');
        $pdfUrl = asset('storage/' . $path);
        $pdfUrl = 'https://post.realvictorygroups.com/assets/logo1.png';

        // 5) WhatsApp message text
        $message = 'Your document from Real Victory Groups';

        /**
         * 6) whatapi.in base URL
         * Database me sirf itna store karo:
         * https://webhook.whatapi.in/webhook/6878c6bae3591ae351d0aae6
         */
        $baseUrl = $apiKey->base_url;
//        $baseUrl = 'https://webhook.whatapi.in/webhook/68886786de405dae96e96a73';

        // Agar kisi ne galti se query ke sath save kar diya ho to usko strip kar do
        $baseUrl = strtok($baseUrl, '?');

        // 7) Final endpoint build karo (sample):
        // https://webhook.whatapi.in/webhook/XXXX?number=91XXXXXXXXXX&image=MEDIA_URL&text=hi
        $query = [
            'number' => $phone,
            'file'  => $pdfUrl,   // whatapi pe agar 'document' ya 'file' param chahiye ho to yaha change kar lena
            'text'   => $message,
        ];

        $endpoint = $baseUrl . '?' . http_build_query($query);

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
            'error_message'       => $success ? null : \Illuminate\Support\Str::limit($body ?? '', 500),
            'meta'                => [
                'endpoint' => $endpoint,
                'pdf_path' => $path,
                'pdf_url'  => $pdfUrl,
            ],
            'sent_at'             => now(),
        ]);

        if (!$success) {
            return back()->withErrors([
                'whatsapp' => 'WhatsApp API error: ' . ($body ?? 'Unknown error'),
            ])->withInput();
        }

        return back()->with('success', 'PDF WhatsApp par send ho gaya!');
    }


}
