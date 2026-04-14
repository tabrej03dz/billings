<?php

namespace App\Http\Controllers;

use App\Models\BillRequest;
use App\Models\Business;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BillRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = BillRequest::query();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_phone1', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('gst_number', 'like', "%{$search}%")
                    ->orWhere('package_name', 'like', "%{$search}%")
                    ->orWhere('payment_method', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhere('source_software', 'like', "%{$search}%")
                    ->orWhere('source_request_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('requested_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('requested_at', '<=', $request->to_date);
        }

        $billRequests = $query->latest('id')->paginate(20)->withQueryString();

        return view('bill-requests.index', compact('billRequests'));
    }

    public function createInvoice(Request $request, BillRequest $billRequest)
    {
        $bid = auth()->user()->current_business_id ?? session('active_business_id');

        if (!$bid) {
            return redirect()
                ->route('bill-requests.index')
                ->with('error', 'Active business select/attach nahi hai.');
        }

        if ($billRequest->status === 'processed') {
            return redirect()
                ->route('bill-requests.index')
                ->with('error', 'Is bill request se invoice pehle hi create ho chuka hai.');
        }

        try {
            $invoice = DB::transaction(function () use ($billRequest, $bid) {
                $business = Business::findOrFail($bid);

                /*
                |--------------------------------------------------------------------------
                | 1. CLIENT FIND / CREATE
                |--------------------------------------------------------------------------
                */
                $client = null;

                // GSTIN se client check
                if (!empty($billRequest->gst_number)) {
                    $client = Client::where('business_id', $bid)
                        ->where('gstin', trim($billRequest->gst_number))
                        ->first();
                }

                // Mobile se fallback
                if (!$client && !empty($billRequest->customer_phone)) {
                    $client = Client::where('business_id', $bid)
                        ->where('mobile', trim($billRequest->customer_phone))
                        ->first();
                }

                // Email se fallback
                if (!$client && !empty($billRequest->customer_email)) {
                    $client = Client::where('business_id', $bid)
                        ->where('email', trim($billRequest->customer_email))
                        ->first();
                }

                // Client create
                if (!$client) {
                    $client = Client::create([
                        'business_id' => $bid,
                        'name'        => $billRequest->customer_name ?: 'Walk-in Customer',
                        'address'     => $billRequest->address,
                        'gstin'       => $billRequest->gst_number,
                        'mobile'      => $billRequest->customer_phone ?: $billRequest->customer_phone1,
                        'state'       => $billRequest->state,
                        'city'        => $billRequest->city,
                        'pincode'     => $billRequest->pin,
                        'state_code'  => null,
                        'email'       => $billRequest->customer_email,
                        'is_save'     => 1,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | 2. ITEM MATCH BY PACKAGE NAME
                |--------------------------------------------------------------------------
                */
                $packageName = trim((string)($billRequest->package_name ?? ''));

                $matchedItem = null;

                if ($packageName !== '') {
                    // 1. Exact name match
                    $matchedItem = Item::where('business_id', $bid)
                        ->where('is_active', 1)
                        ->where('type', 'service')
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($packageName)])
                        ->first();

                    // 2. Partial name match
                    if (!$matchedItem) {
                        $matchedItem = Item::where('business_id', $bid)
                            ->where('is_active', 1)
                            ->where('type', 'service')
                            ->where('name', 'like', '%' . $packageName . '%')
                            ->first();
                    }

                    // 3. Description match
                    if (!$matchedItem) {
                        $matchedItem = Item::where('business_id', $bid)
                            ->where('is_active', 1)
                            ->where('type', 'service')
                            ->where('description', 'like', '%' . $packageName . '%')
                            ->first();
                    }
                }

                // 4. Final fallback
                if (!$matchedItem) {
                    $matchedItem = Item::where('business_id', $bid)
                        ->where('is_active', 1)
                        ->where('type', 'service')
                        ->first();
                }

                if (!$matchedItem) {
                    throw new \Exception('Service item nahi mila. Items table me service type item create kijiye.');
                }

                /*
                |--------------------------------------------------------------------------
                | 3. AMOUNT RESOLVE
                |--------------------------------------------------------------------------
                */
                $subtotal = (float) (
                    $billRequest->selling_price
                    ?? $billRequest->payment_amount
                    ?? $billRequest->package_price
                    ?? $matchedItem->price
                    ?? 0
                );

                if ($subtotal <= 0) {
                    throw new \Exception('Bill request amount invalid hai. selling_price/payment_amount/package_price me valid amount hona chahiye.');
                }

                $receivedAmount = (float) ($billRequest->payment_amount ?? 0);

                $invoiceDate = $billRequest->payment_date
                    ? Carbon::parse($billRequest->payment_date)->toDateString()
                    : ($billRequest->requested_at
                        ? Carbon::parse($billRequest->requested_at)->toDateString()
                        : now()->toDateString());

                /*
                |--------------------------------------------------------------------------
                | 4. INVOICE NUMBER
                |--------------------------------------------------------------------------
                */
                $taxBase = optional(
                    auth()->user()->businesses()->where('businesses.id', $bid)->first()
                )->invoice_base_prefix ?? 'INV';

                $prefix = \App\Services\InvoiceNumber::previewPrefix($invoiceDate, $taxBase);
                $alloc  = \App\Services\InvoiceNumber::next((int)$bid, $invoiceDate, $prefix, 3, 'tax');
                $invoiceNumber = $alloc['full'];

                \App\Services\InvoiceNumber::syncNextSeqIfMatches(
                    (int)$bid,
                    $invoiceDate,
                    $invoiceNumber,
                    3,
                    'tax'
                );

                /*
                |--------------------------------------------------------------------------
                | 5. GST LOGIC
                |--------------------------------------------------------------------------
                */
                $taxPercent = (float)($matchedItem->tax_rate ?? 0);
                if ($taxPercent < 0) {
                    $taxPercent = 0;
                }

                $bizCode   = $this->normCode($business->state_code ?? '');
                $partyCode = $this->normCode($client->state_code ?? '');
                $isIntra   = ($bizCode !== '' && $partyCode !== '') ? ($bizCode === $partyCode) : false;

                $taxAmount = round($subtotal * ($taxPercent / 100), 2);

                if ($isIntra) {
                    $cgstPercent = round($taxPercent / 2, 2);
                    $sgstPercent = round($taxPercent / 2, 2);
                    $igstPercent = 0;

                    $cgstAmount = round($taxAmount / 2, 2);
                    $sgstAmount = round($taxAmount / 2, 2);
                    $igstAmount = 0;
                } else {
                    $cgstPercent = 0;
                    $sgstPercent = 0;
                    $igstPercent = round($taxPercent, 2);

                    $cgstAmount = 0;
                    $sgstAmount = 0;
                    $igstAmount = round($taxAmount, 2);
                }

                $grandTotal = round($subtotal + $taxAmount, 2);
                $balance    = max(0, round($grandTotal - $receivedAmount, 2));

                /*
                |--------------------------------------------------------------------------
                | 6. ITEMS JSON
                |--------------------------------------------------------------------------
                */
                $itemDescription = $billRequest->package_name
                    ?: ($matchedItem->description ?: $matchedItem->name);

                $itemsJson = [
                    [
                        'item_id'       => $matchedItem->id,
                        'item_type'     => 'service',
                        'description'   => $itemDescription,
                        'hsn'           => $matchedItem->sac ?? '',
                        'qty'           => 1,
                        'tax_percent'   => round($taxPercent, 2),
                        'service_rate'  => round($subtotal, 2),
                        'rate'          => round($subtotal, 2),
                        'tax_amount'    => round($taxAmount, 2),
                        'amount'        => round($grandTotal, 2),
                        'making_charge' => round($subtotal, 2),

                        'gold_wt'       => 0,
                        'silver_wt'     => 0,
                        'gold_rate'     => 0,
                        'silver_rate'   => 0,
                        'gemstone_wt'   => 0,
                        'diamond_wt'    => 0,
                        'making_rate'   => 0,
                        'stone_charges' => 0,
                    ]
                ];

                /*
                |--------------------------------------------------------------------------
                | 7. CREATE INVOICE
                |--------------------------------------------------------------------------
                */
                $invoice = Invoice::create([
                    'business_id'     => $bid,
                    'invoice_type'    => 'tax',
                    'invoice_prefix'  => $prefix,
                    'invoice_number'  => $invoiceNumber,
                    'client_id'       => $client->id,
                    'invoice_date'    => $invoiceDate,
                    'payment_terms'   => 0,
                    'due_date'        => null,

                    'subtotal'        => round($subtotal, 2),
                    'tax_amount'      => round($taxAmount, 2),

                    'cgst_percent'    => $cgstPercent,
                    'cgst_amount'     => $cgstAmount,
                    'sgst_percent'    => $sgstPercent,
                    'sgst_amount'     => $sgstAmount,
                    'igst_percent'    => $igstPercent,
                    'igst_amount'     => $igstAmount,

                    'discount_total'  => 0,
                    'charge_total'    => 0,
                    'tcs_percent'     => 0,
                    'tcs_amount'      => 0,
                    'round_off'       => 0,
                    'less_amount'     => 0,

                    'total'           => $grandTotal,
                    'received_amount' => $receivedAmount,
                    'balance'         => $balance,

                    'payment_method'  => $billRequest->payment_method,
                    'transport_mode'  => null,
                    'reverse_charge'  => 0,

                    'place_of_supply_state' => $client->state,
                    'place_of_supply_code'  => $client->state_code,

                    'notes'           => 'Created from Bill Request ID: ' . ($billRequest->source_request_id ?: $billRequest->id),
                    'terms'           => null,

                    'charges_json'    => json_encode([]),
                    'items_json'      => json_encode($itemsJson),

                    'amount_in_words' => '',
                    'pdf_url'         => null,
                    'signature'       => null,
                    'user_id'         => auth()->id(),
                    'created_by'      => auth()->id(),
                    'updated_by'      => auth()->id(),
                    'kots_json'       => json_encode([]),
                ]);

                /*
                |--------------------------------------------------------------------------
                | 8. INVOICE ITEM
                |--------------------------------------------------------------------------
                */
                InvoiceItem::create([
                    'invoice_id'      => $invoice->id,
                    'item_id'         => $matchedItem->id,
                    'description'     => $itemDescription,
                    'sac_code'        => $matchedItem->sac ?? null,
                    'hsn_code'        => null,
                    'quantity'        => 1,

                    'gold_wt'         => 0,
                    'silver_wt'       => 0,
                    'gold_rate'       => 0,
                    'silver_rate'     => 0,
                    'gemstone_wt_ct'  => 0,
                    'diamond_wt_ct'   => 0,

                    'making_charge'   => round($subtotal, 2),
                    'making_rate'     => null,
                    'discount'        => 0,
                    'tax_percent'     => round($taxPercent, 2),
                    'rate'            => round($subtotal, 2),
                    'amount'          => round($grandTotal, 2),
                ]);

                /*
                |--------------------------------------------------------------------------
                | 9. PAYMENT ROW
                |--------------------------------------------------------------------------
                */
                if ($receivedAmount > 0) {
                    $method = strtolower(trim((string) $billRequest->payment_method));

                    InvoicePayment::create([
                        'business_id' => $bid,
                        'invoice_id'  => $invoice->id,
                        'client_id'   => $client->id,
                        'total_value' => $grandTotal,

                        'cash_amount'   => $method === 'cash' ? $receivedAmount : 0,
                        'online_amount' => in_array($method, ['upi', 'online', 'bank'], true) ? $receivedAmount : 0,
                        'card_amount'   => $method === 'card' ? $receivedAmount : 0,
                        'cheque_amount' => $method === 'cheque' ? $receivedAmount : 0,

                        'online_mode' => $method === 'upi' ? 'upi' : null,
                        'online_ref'  => $billRequest->transaction_id,
                        'upi_id'      => null,
                        'card_last4'  => null,
                        'card_ref'    => null,
                        'cheque_no'   => null,
                        'bank_name'   => $billRequest->bank,

                        'credit_sales_excess_amount' => 0,
                        'advance_amount'             => 0,
                        'received_total'             => $receivedAmount,
                        'notes'                      => 'Created from bill request',
                        'meta'                       => null,
                        'paid_at'                    => now(),
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | 10. UPDATE BILL REQUEST
                |--------------------------------------------------------------------------
                */
                $oldApi = [];
                if (!empty($billRequest->api_response)) {
                    $decoded = json_decode($billRequest->api_response, true);
                    if (is_array($decoded)) {
                        $oldApi = $decoded;
                    }
                }

                $oldApi['matched_item_id']         = $matchedItem->id;
                $oldApi['matched_item_name']       = $matchedItem->name;
                $oldApi['created_invoice_id']      = $invoice->id;
                $oldApi['created_invoice_number']  = $invoice->invoice_number;
                $oldApi['created_client_id']       = $client->id;
                $oldApi['processed_at']            = now()->toDateTimeString();

                $billRequest->update([
                    'status'       => 'processed',
                    'remarks'      => 'Invoice created successfully. Invoice No: ' . $invoice->invoice_number,
                    'api_response' => json_encode($oldApi),
                ]);

                return $invoice;
            });


            $this->pushSaveInvoiceApi($billRequest->fresh(), $invoice->fresh());


            return redirect()
                ->route('invoices.preview', $invoice->id)
                ->with('success', 'Invoice created successfully from bill request.');
        } catch (\Throwable $e) {
            Log::error('Bill request to invoice failed', [
                'bill_request_id' => $billRequest->id,
                'error' => $e->getMessage(),
            ]);

            try {
                $billRequest->update([
                    'status'  => 'failed',
                    'remarks' => $e->getMessage(),
                ]);
            } catch (\Throwable $inner) {
            }

            return redirect()
                ->route('bill-requests.index')
                ->with('error', 'Invoice create failed: ' . $e->getMessage());
        }
    }

    public function destroy(BillRequest $billRequest)
    {
        try {
            $billRequest->delete();

            return redirect()
                ->route('bill-requests.index')
                ->with('success', 'Bill request deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Bill request delete failed', [
                'bill_request_id' => $billRequest->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('bill-requests.index')
                ->with('error', 'Bill request delete failed: ' . $e->getMessage());
        }
    }

    private function normCode($v): string
    {
        $s = trim((string) $v);
        $s = preg_replace('/\D+/', '', $s);
        $s = ltrim($s, '0');

        return $s;
    }


    private function pushSaveInvoiceApi(BillRequest $billRequest, Invoice $invoice): void
    {
        try {
            // $endpoint = config('services.billing.save_invoice_url'); // .env/config se lo

            $endpoint = 'https://post.realvictorygroups.com/api/invoice-url-save'; // .env/config se lo

            if (!$endpoint) {
                Log::warning('save-invoice api url missing in config/services');
                return;
            }

            // pdf url resolve
            $pdfUrl = null;

            if (!empty($invoice->pdf_url)) {
                $pdfUrl = Storage::disk('public')->url($invoice->pdf_url);
            } else {
                // fallback: invoice show route
                $pdfUrl = route('invoices.show', $invoice->id);
            }

            $payload = [
                'source_request_id'      => $billRequest->source_request_id,
                'source_user_package_id' => $billRequest->source_user_package_id,
                // 'source_user_package_id' => '3911',
                'invoice_id'             => $invoice->id,
                'invoice_number'         => $invoice->invoice_number,
                'pdf_url'                => $pdfUrl,
            ];

            $response = Http::timeout(30)
            ->withoutVerifying()
            ->acceptJson()
            ->post($endpoint, $payload);

            $oldApi = [];
            if (!empty($billRequest->api_response)) {
                $decoded = json_decode($billRequest->api_response, true);
                if (is_array($decoded)) {
                    $oldApi = $decoded;
                }
            }

            $oldApi['save_invoice_api'] = [
                'url'        => $endpoint,
                'payload'    => $payload,
                'status'     => $response->status(),
                'ok'         => $response->successful(),
                'response'   => $response->json() ?? $response->body(),
                'called_at'  => now()->toDateTimeString(),
            ];

            $billRequest->update([
                'api_response' => json_encode($oldApi),
            ]);
        } catch (\Throwable $e) {
            Log::error('save-invoice api call failed', [
                'bill_request_id' => $billRequest->id,
                'invoice_id'      => $invoice->id,
                'error'           => $e->getMessage(),
            ]);

            $oldApi = [];
            if (!empty($billRequest->api_response)) {
                $decoded = json_decode($billRequest->api_response, true);
                if (is_array($decoded)) {
                    $oldApi = $decoded;
                }
            }

            $oldApi['save_invoice_api'] = [
                'ok'        => false,
                'error'     => $e->getMessage(),
                'called_at' => now()->toDateTimeString(),
            ];

            $billRequest->update([
                'api_response' => json_encode($oldApi),
            ]);
        }
    }


    public function show(BillRequest $billRequest)
    {
        $apiResponse = null;

        if (!empty($billRequest->api_response)) {
            $decoded = json_decode($billRequest->api_response, true);
            $apiResponse = is_array($decoded) ? $decoded : null;
        }

        return view('bill-requests.show', compact('billRequest', 'apiResponse'));
    }
}