<?php

namespace App\Http\Controllers;

use App\Models\BillRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BillRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = BillRequest::query();

        // Search
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

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date filter
        if ($request->filled('from_date')) {
            $query->whereDate('requested_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('requested_at', '<=', $request->to_date);
        }

        $billRequests = $query->latest('id')->paginate(20)->withQueryString();

        return view('bill-requests.index', compact('billRequests'));
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

                // =========================
                // 1. Client find/create
                // =========================
                $client = Client::where('business_id', $bid)
                    ->where(function ($q) use ($billRequest) {
                        if (!empty($billRequest->customer_phone)) {
                            $q->orWhere('phone', $billRequest->customer_phone);
                        }

                        if (!empty($billRequest->customer_phone1)) {
                            $q->orWhere('phone1', $billRequest->customer_phone1);
                        }

                        if (!empty($billRequest->customer_email)) {
                            $q->orWhere('email', $billRequest->customer_email);
                        }
                    })
                    ->first();

                if (!$client) {
                    $client = new Client();
                    $client->business_id = $bid;
                    $client->name        = $billRequest->customer_name ?: 'Walk-in Customer';
                    $client->email       = $billRequest->customer_email;
                    $client->phone       = $billRequest->customer_phone;
                    $client->phone1      = $billRequest->customer_phone1;
                    $client->gst_no      = $billRequest->gst_number;
                    $client->address     = $billRequest->address;
                    $client->country     = $billRequest->country;
                    $client->state       = $billRequest->state;
                    $client->city        = $billRequest->city;
                    $client->pin_code    = $billRequest->pin;
                    $client->save();
                }

                // =========================
                // 2. Invoice item choose
                // =========================
                // Package/service invoice ke liye koi service item use karo
                $serviceItem = Item::where('business_id', $bid)
                    ->where(function ($q) {
                        $q->where('type', 'service')
                          ->orWhere('item_type', 'service');
                    })
                    ->first();

                if (!$serviceItem) {
                    throw new \Exception('Please create at least one service item first for package billing.');
                }

                // =========================
                // 3. Amount decide
                // =========================
                $subtotal = (float) (
                    $billRequest->selling_price
                    ?? $billRequest->payment_amount
                    ?? $billRequest->package_price
                    ?? 0
                );

                if ($subtotal <= 0) {
                    throw new \Exception('Bill request amount invalid hai. selling_price/payment_amount/package_price me valid amount hona chahiye.');
                }

                $receivedAmount = (float)($billRequest->payment_amount ?? 0);
                $balance        = max(0, round($subtotal - $receivedAmount, 2));

                $invoiceDate = $billRequest->payment_date
                    ? Carbon::parse($billRequest->payment_date)->toDateString()
                    : ($billRequest->requested_at
                        ? Carbon::parse($billRequest->requested_at)->toDateString()
                        : now()->toDateString());

                // =========================
                // 4. Invoice number
                // =========================
                $prefix = $this->computePrefix($invoiceDate, 'INV');
                $alloc  = \App\Services\InvoiceNumber::next((int)$bid, $invoiceDate, $prefix, 3, 'tax');
                $invoiceNumber = $alloc['full'];

                \App\Services\InvoiceNumber::syncNextSeqIfMatches((int)$bid, $invoiceDate, $invoiceNumber, 3, 'tax');

                // =========================
                // 5. GST state logic
                // =========================
                $bizCode   = $this->normCode($business->state_code ?? '');
                $partyCode = $this->normCode($client->state_code ?? '');
                $isIntra   = ($bizCode !== '' && $partyCode !== '') ? ($bizCode === $partyCode) : false;

                // abhi simple package invoice without tax breakup
                $taxAmount    = 0;
                $cgstPercent  = 0;
                $sgstPercent  = 0;
                $igstPercent  = 0;
                $cgstAmount   = 0;
                $sgstAmount   = 0;
                $igstAmount   = 0;
                $grandTotal   = round($subtotal, 2);

                // =========================
                // 6. Invoice create
                // =========================
                $invoice = Invoice::create([
                    'business_id'     => $bid,
                    'client_id'       => $client->id,
                    'invoice_date'    => $invoiceDate,

                    'invoice_prefix'  => $prefix,
                    'invoice_number'  => $invoiceNumber,
                    'invoice_type'    => 'tax',

                    'subtotal'        => $subtotal,
                    'discount_total'  => 0,
                    'charge_total'    => 0,
                    'less_amount'     => 0,

                    'tax_amount'      => $taxAmount,

                    'cgst_percent'    => $cgstPercent,
                    'cgst_amount'     => $cgstAmount,
                    'sgst_percent'    => $sgstPercent,
                    'sgst_amount'     => $sgstAmount,
                    'igst_percent'    => $igstPercent,
                    'igst_amount'     => $igstAmount,

                    'tcs_percent'     => 0,
                    'tcs_amount'      => 0,
                    'round_off'       => 0,

                    'total'           => $grandTotal,
                    'received_amount' => $receivedAmount,
                    'balance'         => $balance,

                    'payment_method'  => $billRequest->payment_method,
                    'gst_no'          => $billRequest->gst_number,
                    'transport_mode'  => null,
                    'reverse_charge'  => 0,

                    'place_of_supply_state' => $client->state ?? null,
                    'place_of_supply_code'  => $client->state_code ?? null,

                    'notes' => 'Created from Bill Request ID: ' . ($billRequest->source_request_id ?: $billRequest->id),
                    'terms' => null,

                    'charges_json'    => json_encode([]),
                    'items_json'      => json_encode([
                        [
                            'item_id'       => $serviceItem->id,
                            'item_type'     => 'service',
                            'description'   => $billRequest->package_name ?: 'Package Billing',
                            'hsn'           => $serviceItem->hsn_code ?? null,
                            'qty'           => 1,
                            'tax_percent'   => 0,
                            'service_rate'  => $subtotal,
                            'rate'          => $subtotal,
                            'tax_amount'    => 0,
                            'amount'        => $subtotal,
                            'making_charge' => $subtotal,
                            'gold_wt'       => 0,
                            'silver_wt'     => 0,
                            'gold_rate'     => 0,
                            'silver_rate'   => 0,
                            'gemstone_wt'   => 0,
                            'diamond_wt'    => 0,
                            'making_rate'   => 0,
                            'stone_charges' => 0,
                        ]
                    ]),

                    'amount_in_words' => '',
                    'signature_path'  => null,
                    'created_by'      => auth()->id(),
                    'updated_by'      => auth()->id(),
                    'kots_json'       => json_encode([]),
                ]);

                // =========================
                // 7. Invoice item row
                // =========================
                InvoiceItem::create([
                    'invoice_id'      => $invoice->id,
                    'item_id'         => $serviceItem->id,
                    'description'     => $billRequest->package_name ?: 'Package Billing',
                    'sac_code'        => $serviceItem->sac_code ?? null,
                    'hsn_code'        => $serviceItem->hsn_code ?? null,
                    'quantity'        => 1,

                    'gold_wt'         => 0,
                    'silver_wt'       => 0,
                    'gold_rate'       => 0,
                    'silver_rate'     => 0,
                    'gemstone_wt_ct'  => 0,
                    'diamond_wt_ct'   => 0,

                    'making_charge'   => $subtotal,
                    'making_rate'     => null,
                    'discount'        => 0,
                    'tax_percent'     => 0,
                    'rate'            => $subtotal,
                    'amount'          => $subtotal,
                ]);

                // =========================
                // 8. Payment row
                // =========================
                if ($receivedAmount > 0) {
                    InvoicePayment::create([
                        'business_id'                => $bid,
                        'invoice_id'                 => $invoice->id,
                        'client_id'                  => $client->id,
                        'total_value'                => $grandTotal,

                        'cash_amount'                => strtolower((string)$billRequest->payment_method) === 'cash' ? $receivedAmount : 0,
                        'online_amount'              => in_array(strtolower((string)$billRequest->payment_method), ['upi', 'online', 'bank']) ? $receivedAmount : 0,
                        'card_amount'                => strtolower((string)$billRequest->payment_method) === 'card' ? $receivedAmount : 0,
                        'cheque_amount'              => strtolower((string)$billRequest->payment_method) === 'cheque' ? $receivedAmount : 0,

                        'online_mode'                => strtolower((string)$billRequest->payment_method) === 'upi' ? 'upi' : null,
                        'online_ref'                 => $billRequest->transaction_id,
                        'upi_id'                     => null,
                        'card_last4'                 => null,
                        'card_ref'                   => null,
                        'cheque_no'                  => null,
                        'bank_name'                  => $billRequest->bank,

                        'credit_sales_excess_amount' => 0,
                        'advance_amount'             => 0,
                        'received_total'             => $receivedAmount,
                        'notes'                      => 'Created from bill request',
                        'meta'                       => null,
                        'paid_at'                    => now(),
                    ]);
                }

                // =========================
                // 9. Bill request update
                // =========================
                $oldApi = [];
                if (!empty($billRequest->api_response)) {
                    $decoded = json_decode($billRequest->api_response, true);
                    if (is_array($decoded)) {
                        $oldApi = $decoded;
                    }
                }

                $oldApi['created_invoice_id']     = $invoice->id;
                $oldApi['created_invoice_number'] = $invoice->invoice_number;
                $oldApi['created_client_id']      = $client->id;
                $oldApi['processed_at']           = now()->toDateTimeString();

                $billRequest->update([
                    'status'       => 'processed',
                    'remarks'      => 'Invoice created successfully. Invoice No: ' . $invoice->invoice_number,
                    'api_response' => json_encode($oldApi),
                ]);

                return $invoice;
            });

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
                // ignore secondary failure
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

    private function computePrefix(string $date, string $base = 'INV'): string
    {
        $ts = strtotime($date);
        $y  = (int)date('Y', $ts);
        $m  = (int)date('n', $ts);

        $startYY = ($m >= 4) ? ($y % 100) : (($y - 1) % 100);
        $a = str_pad((string)$startYY, 2, '0', STR_PAD_LEFT);
        $b = str_pad((string)(($startYY + 1) % 100), 2, '0', STR_PAD_LEFT);

        $fy = "{$a}-{$b}";
        $base = rtrim($base, '/');

        return "{$base}/{$fy}/";
    }

    private function normCode($v): string
    {
        $s = trim((string)$v);
        $s = preg_replace('/\D+/', '', $s);
        $s = ltrim($s, '0');

        return $s;
    }
}
