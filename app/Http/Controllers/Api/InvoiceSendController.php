<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceSendController extends Controller
{
    public function uploadAndSendPdf(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'pdf'   => ['required', 'file', 'mimes:pdf', 'max:5120'], // 5MB
            'phone' => ['nullable', 'string', 'max:20'],
            'caption' => ['nullable', 'string', 'max:200'],
        ]);

        // ✅ API config required
        $apiKey = ApiKey::where('user_id', $user->id)->latest('id')->first();
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp API not set. Please set from API Settings.',
            ], 422);
        }

        $pdfFile = $request->file('pdf');

        // ✅ phone decide: input > filename
        $phone = preg_replace('/\D+/', '', (string) ($request->phone ?? ''));

        if (empty($phone)) {
            $originalName  = $pdfFile->getClientOriginalName(); // 9876543210.pdf
            $nameNoExt     = pathinfo($originalName, PATHINFO_FILENAME);
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

        // ✅ create row first (log)
        $row = InvoiceSend::create([
            'business_id'         => null,
            'user_id'             => $user->id,
            'invoice_id'          => null,
            'channel'             => 'whatsapp',
            'recipient_phone'     => $phone,
            'recipient_email'     => null,
            'file_url'            => $pdfUrl,
            'status'              => 'uploading',
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

        // ✅ send now
        try {
            $caption = (string) ($request->caption ?? 'Invoice');

            $result = app(\App\Services\WhatsAppService::class)
                ->sendDocument(
                    apiKey: $apiKey,
                    toPhone: $phone,
                    fileUrl: $pdfUrl,
                    filename: $pdfFile->getClientOriginalName(),
                    caption: $caption
                );

            // ✅ update success
            $row->update([
                'status'              => 'sent',
                'response_code'       => $result['code'] ?? 200,
                'provider_message_id' => $result['message_id'] ?? null,
                'error_message'       => null,
                'sent_at'             => now(),
                'meta'                => array_merge($row->meta ?? [], [
                    'provider_response' => $result,
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Uploaded & sent',
                'id'      => $row->id,
                'phone'   => $phone,
                'pdf_url' => $pdfUrl,
                'provider_message_id' => $row->provider_message_id,
            ]);

        } catch (\Throwable $e) {
            Log::error('WhatsApp PDF send failed', [
                'row_id' => $row->id,
                'error' => $e->getMessage(),
            ]);

            $row->update([
                'status'        => 'failed',
                'response_code' => 500,
                'error_message' => $e->getMessage(),
                'meta'          => array_merge($row->meta ?? [], [
                    'exception' => get_class($e),
                ]),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload ok but send failed',
                'id'      => $row->id,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
