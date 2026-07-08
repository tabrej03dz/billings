<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\BillRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Item;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class BillRequestController extends Controller
{

    public function store(Request $request)
    {
        try {
            $data = $request->all();

            if (empty($data)) {
                $json = json_decode($request->getContent(), true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                    $data = $json;
                    $request->merge($data);
                }
            }

            $requestForBill = $request->input('request_for_bill');

            if (in_array($requestForBill, ['1', 1, true, 'true', 'yes', 'on'], true)) {
                $requestForBill = true;
            } elseif (in_array($requestForBill, ['0', 0, false, 'false', 'no', 'off'], true)) {
                $requestForBill = false;
            } else {
                $requestForBill = null;
            }

            $validator = Validator::make([
                'request_for_bill'       => $requestForBill,

                'source_software'        => $request->input('source_software'),
                'source_customer_id'     => $request->input('source_customer_id'),
                'source_package_id'      => $request->input('source_package_id'),
                'source_user_package_id' => $request->input('source_user_package_id'),
                'source_payment_id'      => $request->input('source_payment_id'),

                'customer_name'          => $request->input('customer_name'),
                'customer_email'         => $request->input('customer_email'),
                'customer_phone'         => $request->input('customer_phone'),
                'customer_phone1'        => $request->input('customer_phone1'),
                'country'                => $request->input('country'),
                'state'                  => $request->input('state'),
                'city'                   => $request->input('city'),
                'pin'                    => $request->input('pin'),
                'address'                => $request->input('address'),
                'customer_gst_number'    => $request->input('customer_gst_number'),

                'package_id'             => $request->input('package_id'),
                'package_name'           => $request->input('package_name'),
                'package_description'    => $request->input('package_description'),
                'package_price'          => $request->input('package_price'),
                'package_duration'       => $request->input('package_duration'),

                'items'                  => $request->input('items'),

                'start_date'             => $request->input('start_date'),
                'expiry_date'            => $request->input('expiry_date'),
                'status'                 => $request->input('status'),

                'payment_amount'         => $request->input('payment_amount'),
                'payment_method'         => $request->input('payment_method'),
                'transaction_id'         => $request->input('transaction_id'),
                'bank'                   => $request->input('bank'),
                'payment_date'           => $request->input('payment_date'),
                'activated_by'           => $request->input('activated_by') ?? $request->input('created_by') ?? $request->input('created_by_name'),
                'customer_type'          => $request->input('customer_type'),
                'old_customer_user_id'   => $request->input('old_customer_user_id'),

                'created_by'             => $request->input('created_by'),
                'created_by_name'        => $request->input('created_by_name'),
                'request_date'           => $request->input('request_date'),
            ], [
                'request_for_bill'       => ['required', 'boolean'],

                'source_software'        => ['nullable', 'string', 'max:255'],
                'source_customer_id'     => ['nullable', 'integer'],
                'source_package_id'      => ['nullable', 'integer'],
                'source_user_package_id' => ['nullable', 'integer'],
                'source_payment_id'      => ['nullable', 'integer'],

                'customer_name'          => ['required', 'string', 'max:255'],
                'customer_email'         => ['nullable', 'email', 'max:255'],
                'customer_phone'         => ['nullable', 'string', 'max:255'],
                'customer_phone1'        => ['nullable', 'string', 'max:255'],
                'country'                => ['nullable', 'string', 'max:255'],
                'state'                  => ['nullable', 'string', 'max:255'],
                'city'                   => ['nullable', 'string', 'max:255'],
                'pin'                    => ['nullable', 'string', 'max:255'],
                'address'                => ['nullable', 'string'],
                'customer_gst_number'    => ['nullable', 'string', 'size:15'],

                'package_id'             => ['nullable', 'integer'],
                'package_name'           => ['nullable'],
                'package_description'    => ['nullable', 'string'],
                'package_price'          => ['nullable', 'numeric'],
                'package_duration'       => ['nullable', 'integer'],

                'items'                  => ['nullable', 'array'],
                'items.*.item_id'        => ['required_with:items', 'integer'],
                'items.*.qty'            => ['nullable', 'numeric', 'min:1'],
                'items.*.price'          => ['required_with:items', 'numeric', 'min:0'],
                'items.*.description'    => ['nullable', 'string'],
                'items.*.line_total'     => ['nullable', 'numeric', 'min:0'],

                'start_date'             => ['nullable', 'date'],
                'expiry_date'            => ['nullable', 'date'],
                'status'                 => ['nullable', 'string', 'max:255'],

                'payment_amount'         => ['nullable', 'numeric'],
                'payment_method'         => ['nullable', 'string', 'max:255'],
                'transaction_id'         => ['nullable', 'string', 'max:255'],
                'bank'                   => ['nullable', 'string', 'max:255'],
                'payment_date'           => ['nullable', 'date'],
                'activated_by'           => ['nullable', 'string', 'max:255'],
                'customer_type'          => ['nullable', 'string', 'max:255'],
                'old_customer_user_id'   => ['nullable', 'integer'],

                'created_by'             => ['nullable', 'integer'],
                'created_by_name'        => ['nullable', 'string', 'max:255'],
                'request_date'           => ['nullable', 'date'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success'          => false,
                    'message'          => 'Validation failed.',
                    'errors'           => $validator->errors(),
                    'received_payload' => $request->all(),
                ], 422);
            }

            $validated = $validator->validated();

            $sourceRequestId = trim((string) $request->header('X-Request-Id'));
            $sourceSoftware  = $validated['source_software'] ?? 'postimage';

            $idempotencyKey = null;

            if (!empty($validated['source_user_package_id'])) {
                $idempotencyKey = $sourceSoftware . ':user_package:' . $validated['source_user_package_id'];
            } elseif ($sourceRequestId !== '') {
                $idempotencyKey = $sourceSoftware . ':request:' . $sourceRequestId;
            }

            $existingBillRequest = null;

            if ($idempotencyKey) {
                $existingBillRequest = BillRequest::where('idempotency_key', $idempotencyKey)->first();
            }

            if (!$existingBillRequest && !empty($validated['source_user_package_id'])) {
                $existingBillRequest = BillRequest::where('source_software', $sourceSoftware)
                    ->where('source_user_package_id', $validated['source_user_package_id'])
                    ->first();
            }

            if (!$existingBillRequest && $sourceRequestId !== '') {
                $existingBillRequest = BillRequest::where('source_request_id', $sourceRequestId)->first();
            }

            if ($existingBillRequest) {
                $existingInvoice = Invoice::withoutGlobalScope('business_id')
                    ->where('bil_request_id', $existingBillRequest->id)
                    ->where('invoice_type', 'quotation')
                    ->first();

                return response()->json([
                    'success'            => true,
                    'message'            => 'Duplicate request ignored. Existing billing request returned.',
                    'billing_request_id' => $existingBillRequest->id,
                    'invoice_id'         => $existingInvoice?->id,
                    'invoice_number'     => $existingInvoice?->invoice_number,
                    'invoice_type'       => $existingInvoice?->invoice_type,
                    'status'             => $existingBillRequest->status,
                    'data'               => [
                        'bill_request' => $existingBillRequest,
                        'invoice'      => $existingInvoice,
                    ],
                ], 200);
            }

            $items = collect($validated['items'] ?? [])
                ->filter(fn ($row) => !empty($row['item_id']))
                ->map(function ($row) {
                    $qty = max(1, (float) ($row['qty'] ?? 1));
                    $price = (float) ($row['price'] ?? 0);

                    return [
                        'item_id'     => (int) $row['item_id'],
                        'qty'         => $qty,
                        'price'       => $price,
                        'description' => trim((string) ($row['description'] ?? '')),
                        'line_total'  => round($qty * $price, 2),
                    ];
                })
                ->filter(fn ($row) => $row['item_id'] > 0 && $row['price'] > 0)
                ->values()
                ->toArray();

            if ($validated['request_for_bill'] && empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => [
                        'items' => ['Bill ke liye kam se kam ek valid item required hai.'],
                    ],
                    'received_payload' => $request->all(),
                ], 422);
            }

            $itemsTotal = collect($items)->sum('line_total');

            $gstNumber = !empty($validated['customer_gst_number'])
                ? strtoupper(trim($validated['customer_gst_number']))
                : null;

            $fullPayload = [
                'request_for_bill'       => $validated['request_for_bill'],
                // 'source_software'        => $validated['source_software'] ?? null,
                'source_software' => $sourceSoftware,
                'source_customer_id'     => $validated['source_customer_id'] ?? null,
                'source_package_id'      => $validated['source_package_id'] ?? null,
                'source_user_package_id' => $validated['source_user_package_id'] ?? null,
                'source_payment_id'      => $validated['source_payment_id'] ?? null,

                'customer_name'          => $validated['customer_name'] ?? null,
                'customer_email'         => $validated['customer_email'] ?? null,
                'customer_phone'         => $validated['customer_phone'] ?? null,
                'customer_phone1'        => $validated['customer_phone1'] ?? null,
                'country'                => $validated['country'] ?? null,
                'state'                  => $validated['state'] ?? null,
                'city'                   => $validated['city'] ?? null,
                'pin'                    => $validated['pin'] ?? null,
                'address'                => $validated['address'] ?? null,
                'customer_gst_number'    => $gstNumber,

                'package_id'             => $validated['package_id'] ?? null,
                'package_name'           => $validated['package_name'] ?? null,
                'package_description'    => $validated['package_description'] ?? null,
                'package_price'          => $itemsTotal > 0 ? $itemsTotal : ($validated['package_price'] ?? null),
                'package_duration'       => $validated['package_duration'] ?? null,

                'items'                  => $items,

                'payment_amount'         => $validated['payment_amount'] ?? ($itemsTotal > 0 ? $itemsTotal : null),
                'payment_method'         => $validated['payment_method'] ?? null,
                'transaction_id'         => $validated['transaction_id'] ?? null,
                'bank'                   => $validated['bank'] ?? null,
                'payment_date'           => $validated['payment_date'] ?? null,
                'activated_by'           => $validated['activated_by'] ?? null,
                'customer_type'          => $validated['customer_type'] ?? null,
                'old_customer_user_id'   => $validated['old_customer_user_id'] ?? null,

                'created_by'             => $validated['created_by'] ?? null,
                'created_by_name'        => $validated['created_by_name'] ?? null,
                'request_date'           => $validated['request_date'] ?? null,
            ];

            $billRequest = null;
            $invoice = null;

            DB::transaction(function () use (&$billRequest, &$invoice, $validated, $request, $gstNumber, $fullPayload, $itemsTotal, $sourceRequestId, $sourceSoftware, $idempotencyKey) {
                $billRequest = BillRequest::create([
                    // 'source_software'        => $validated['source_software'] ?? 'postimage',
                    // 'source_request_id'      => $request->header('X-Request-Id'),
                    'source_software'        => $sourceSoftware,
                    'source_request_id'      => $sourceRequestId,
                    'idempotency_key'        => $idempotencyKey,
                    'source_customer_id'     => $validated['source_customer_id'] ?? null,
                    'source_package_id'      => $validated['source_package_id'] ?? null,
                    'source_user_package_id' => $validated['source_user_package_id'] ?? null,
                    'source_payment_id'      => $validated['source_payment_id'] ?? null,

                    'customer_name'          => $validated['customer_name'] ?? null,
                    'customer_email'         => $validated['customer_email'] ?? null,
                    'customer_phone'         => $validated['customer_phone'] ?? null,
                    'customer_phone1'        => $validated['customer_phone1'] ?? null,
                    'country'                => $validated['country'] ?? null,
                    'state'                  => $validated['state'] ?? null,
                    'city'                   => $validated['city'] ?? null,
                    'pin'                    => $validated['pin'] ?? null,
                    'address'                => $validated['address'] ?? null,
                    'gst_number'             => $gstNumber,

                    'package_name'           => $validated['package_name'] ?? null,
                    'package_price'          => $itemsTotal > 0 ? $itemsTotal : ($validated['package_price'] ?? null),
                    'package_duration'       => $validated['package_duration'] ?? null,
                    'selling_price'          => $itemsTotal > 0 ? $itemsTotal : ($validated['package_price'] ?? null),

                    'payment_amount'         => $validated['payment_amount'] ?? ($itemsTotal > 0 ? $itemsTotal : null),
                    'payment_method'         => $validated['payment_method'] ?? null,
                    'transaction_id'         => $validated['transaction_id'] ?? null,
                    'bank'                   => $validated['bank'] ?? null,
                    'payment_date'           => $validated['payment_date'] ?? null,

                    'activated_by'           => $validated['activated_by'] ?? null,
                    'customer_type'          => $validated['customer_type'] ?? null,
                    'old_customer_user_id'   => $validated['old_customer_user_id'] ?? null,

                    'status'                 => 'pending',
                    'remarks'                => null,
                    'full_payload'           => json_encode($fullPayload, JSON_UNESCAPED_UNICODE),
                    'api_response'           => json_encode([
                        // 'received_from' => $validated['source_software'] ?? 'postimage',
                        'received_from' => $sourceSoftware,
                        'received_at'   => now()->toDateTimeString(),
                    ], JSON_UNESCAPED_UNICODE),
                    'requested_at'           => $validated['request_date'] ?? now(),
                ]);

                if ($validated['request_for_bill']) {
                    $invoice = $this->createQuotationInvoiceFromBillRequest($billRequest);
                }
            });

            if ($invoice) {
                $this->pushSaveInvoiceApi($billRequest->fresh(), $invoice->fresh());
            }

            return response()->json([
                'success'            => true,
                'message'            => $invoice
                    ? 'Billing request received and quotation created successfully.'
                    : 'Billing request received successfully.',
                'billing_request_id' => $billRequest->id,
                'invoice_id'         => $invoice?->id,
                'invoice_number'     => $invoice?->invoice_number,
                'invoice_type'       => $invoice?->invoice_type,
                'status'             => $billRequest->fresh()->status,
                'data'               => [
                    'bill_request' => $billRequest->fresh(),
                    'invoice'      => $invoice ? $invoice->fresh() : null,
                ],
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Billing request API failed', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while saving billing request and quotation.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function createQuotationInvoiceFromBillRequest(BillRequest $billRequest)
    {
        $existingInvoice = Invoice::withoutGlobalScope('business_id')
            ->where('bil_request_id', $billRequest->id)
            ->where('invoice_type', 'quotation')
            ->first();

        if ($existingInvoice) {
            return $existingInvoice;
        }
        $bid = 1;
        $userId = 1;

        $business = Business::findOrFail($bid);

        $client = null;

        if (!empty($billRequest->gst_number)) {
            $client = Client::where('business_id', $bid)
                ->where('gstin', trim($billRequest->gst_number))
                ->first();
        }

        if (!$client && !empty($billRequest->customer_phone)) {
            $client = Client::where('business_id', $bid)
                ->where('mobile', trim($billRequest->customer_phone))
                ->first();
        }

        if (!$client && !empty($billRequest->customer_email)) {
            $client = Client::where('business_id', $bid)
                ->where('email', trim($billRequest->customer_email))
                ->first();
        }

        $states = [
            ['code'=>'01','name'=>'Jammu and Kashmir'],
            ['code'=>'02','name'=>'Himachal Pradesh'],
            ['code'=>'03','name'=>'Punjab'],
            ['code'=>'04','name'=>'Chandigarh'],
            ['code'=>'05','name'=>'Uttarakhand'],
            ['code'=>'06','name'=>'Haryana'],
            ['code'=>'07','name'=>'Delhi'],
            ['code'=>'08','name'=>'Rajasthan'],
            ['code'=>'09','name'=>'Uttar Pradesh'],
            ['code'=>'10','name'=>'Bihar'],
            ['code'=>'11','name'=>'Sikkim'],
            ['code'=>'12','name'=>'Arunachal Pradesh'],
            ['code'=>'13','name'=>'Nagaland'],
            ['code'=>'14','name'=>'Manipur'],
            ['code'=>'15','name'=>'Mizoram'],
            ['code'=>'16','name'=>'Tripura'],
            ['code'=>'17','name'=>'Meghalaya'],
            ['code'=>'18','name'=>'Assam'],
            ['code'=>'19','name'=>'West Bengal'],
            ['code'=>'20','name'=>'Jharkhand'],
            ['code'=>'21','name'=>'Odisha'],
            ['code'=>'22','name'=>'Chhattisgarh'],
            ['code'=>'23','name'=>'Madhya Pradesh'],
            ['code'=>'24','name'=>'Gujarat'],
            ['code'=>'26','name'=>'Dadra and Nagar Haveli and Daman and Diu'],
            ['code'=>'27','name'=>'Maharashtra'],
            ['code'=>'29','name'=>'Karnataka'],
            ['code'=>'30','name'=>'Goa'],
            ['code'=>'31','name'=>'Lakshadweep'],
            ['code'=>'32','name'=>'Kerala'],
            ['code'=>'33','name'=>'Tamil Nadu'],
            ['code'=>'34','name'=>'Puducherry'],
            ['code'=>'35','name'=>'Andaman and Nicobar Islands'],
            ['code'=>'36','name'=>'Telangana'],
            ['code'=>'37','name'=>'Andhra Pradesh'],
            ['code'=>'38','name'=>'Ladakh'],
        ];

        $stateCode = null;

        foreach ($states as $st) {
            if (
                strtolower(trim($st['name'])) ==
                strtolower(trim($billRequest->state))
            ) {
                $stateCode = $st['code'];
                break;
            }
        }

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
                'state_code'  => $stateCode,
                'email'       => $billRequest->customer_email,
                'is_save'     => 1,
            ]);
        }

        $payload = [];

        if (!empty($billRequest->full_payload)) {
            $decoded = json_decode($billRequest->full_payload, true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        $requestItems = $payload['items'] ?? [];

        if (empty($requestItems)) {
            $requestItems = [[
                'item_id'     => (int) ($billRequest->package_name ?? 0),
                'qty'         => 1,
                'price'       => (float) ($billRequest->package_price ?? $billRequest->selling_price ?? $billRequest->payment_amount ?? 0),
                'description' => (string) ($billRequest->package_description ?? ''),
            ]];
        }

        $invoiceDate = now(config('app.timezone'))->toDateString();

        $bizCode   = $this->normCode($business->state_code ?? '');
        $partyCode = $this->normCode($client->state_code ?? '');

        $isIntra = ($bizCode !== '' && $partyCode !== '')
            ? ($bizCode === $partyCode)
            : false;

        $itemsJson = [];
        $invoiceRows = [];

        $subtotal = 0;
        $taxAmount = 0;
        $grandTotal = 0;

        $totalTaxPercentForInvoice = 0;
        $totalCgstAmount = 0;
        $totalSgstAmount = 0;
        $totalIgstAmount = 0;

        foreach ($requestItems as $row) {
            $itemId = (int) ($row['item_id'] ?? 0);

            if ($itemId <= 0) {
                throw new \Exception('Selected service item id invalid hai.');
            }

            $matchedItem = Item::where('business_id', $bid)
                ->where('is_active', 1)
                ->where('type', 'service')
                ->where('id', $itemId)
                ->first();

            if (!$matchedItem) {
                throw new \Exception('Selected service item nahi mila. Item ID: ' . $itemId);
            }

            $qty = max(1, (float) ($row['qty'] ?? 1));
            $price = (float) ($row['price'] ?? 0);

            if ($price <= 0) {
                throw new \Exception('Bill request amount invalid hai. Item: ' . $matchedItem->name);
            }

            $grossAmount = round($qty * $price, 2);

            $taxPercent = max(0, (float) ($matchedItem->tax_rate ?? 0));

            if ($taxPercent > 0) {
                $lineSubtotal = round($grossAmount * 100 / (100 + $taxPercent), 2);
                $lineTaxAmount = round($grossAmount - $lineSubtotal, 2);
            } else {
                $lineSubtotal = round($grossAmount, 2);
                $lineTaxAmount = 0;
            }

            if ($isIntra) {
                $lineCgstAmount = round($lineTaxAmount / 2, 2);
                $lineSgstAmount = round($lineTaxAmount - $lineCgstAmount, 2);
                $lineIgstAmount = 0;
            } else {
                $lineCgstAmount = 0;
                $lineSgstAmount = 0;
                $lineIgstAmount = round($lineTaxAmount, 2);
            }

            $itemDescription = trim((string) ($row['description'] ?? ''));

            if ($itemDescription === '') {
                $itemDescription = $matchedItem->description ?: $matchedItem->name;
            }

            $rateWithoutTax = round($lineSubtotal / $qty, 2);

            $subtotal += $lineSubtotal;
            $taxAmount += $lineTaxAmount;
            $grandTotal += $grossAmount;

            $totalCgstAmount += $lineCgstAmount;
            $totalSgstAmount += $lineSgstAmount;
            $totalIgstAmount += $lineIgstAmount;

            $totalTaxPercentForInvoice = max($totalTaxPercentForInvoice, $taxPercent);

            $itemsJson[] = [
                'item_id'       => $matchedItem->id,
                'item_type'     => 'service',
                'description'   => $itemDescription,
                'hsn'           => $matchedItem->sac ?? '',
                'qty'           => $qty,
                'tax_percent'   => round($taxPercent, 2),
                'service_rate'  => $rateWithoutTax,
                'rate'          => $rateWithoutTax,
                'tax_amount'    => round($lineTaxAmount, 2),
                'amount'        => round($grossAmount, 2),
                'making_charge' => round($lineSubtotal, 2),
                'gold_wt'       => 0,
                'silver_wt'     => 0,
                'gold_rate'     => 0,
                'silver_rate'   => 0,
                'gemstone_wt'   => 0,
                'diamond_wt'    => 0,
                'making_rate'   => 0,
                'stone_charges' => 0,
            ];

            $invoiceRows[] = [
                'matched_item'     => $matchedItem,
                'description'      => $itemDescription,
                'qty'              => $qty,
                'rate_without_tax' => $rateWithoutTax,
                'line_subtotal'    => round($lineSubtotal, 2),
                'tax_percent'      => round($taxPercent, 2),
                'line_tax_amount'  => round($lineTaxAmount, 2),
                'gross_amount'     => round($grossAmount, 2),
            ];
        }

        if (empty($invoiceRows)) {
            throw new \Exception('Invoice ke liye koi valid item nahi mila.');
        }

        $subtotal = round($subtotal, 2);
        $taxAmount = round($taxAmount, 2);
        $grandTotal = round($grandTotal, 2);

        if ($isIntra) {
            $cgstPercent = round($totalTaxPercentForInvoice / 2, 2);
            $sgstPercent = round($totalTaxPercentForInvoice / 2, 2);
            $igstPercent = 0;

            $cgstAmount = round($totalCgstAmount, 2);
            $sgstAmount = round($totalSgstAmount, 2);
            $igstAmount = 0;
        } else {
            $cgstPercent = 0;
            $sgstPercent = 0;
            $igstPercent = round($totalTaxPercentForInvoice, 2);

            $cgstAmount = 0;
            $sgstAmount = 0;
            $igstAmount = round($totalIgstAmount, 2);
        }

        $taxBase = $business->quotation_base_prefix ?? 'QT';
        $prefix = \App\Services\InvoiceNumber::previewPrefix($invoiceDate, $taxBase);

        $alloc = \App\Services\InvoiceNumber::next(
            $bid,
            $invoiceDate,
            $prefix,
            3,
            'quotation'
        );

        $invoiceNumber = $alloc['full'];

        \App\Services\InvoiceNumber::syncNextSeqIfMatches(
            $bid,
            $invoiceDate,
            $invoiceNumber,
            3,
            'quotation'
        );

        $invoice = Invoice::create([
            'business_id'     => $bid,
            'bil_request_id'  => $billRequest->id,

            'invoice_type'    => 'quotation',
            'invoice_prefix'  => $prefix,
            'invoice_number'  => $invoiceNumber,
            'client_id'       => $client->id,
            'invoice_date'    => $invoiceDate,
            'payment_terms'   => 0,
            'due_date'        => null,

            'subtotal'        => $subtotal,
            'tax_amount'      => $taxAmount,

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
            'received_amount' => 0,
            'balance'         => $grandTotal,

            'payment_method'  => null,
            'transport_mode'  => null,
            'reverse_charge'  => 0,

            'place_of_supply_state' => $client->state,
            'place_of_supply_code'  => $client->state_code,

            'notes'           => 'Created from Bill Request ID: ' . ($billRequest->source_request_id ?: $billRequest->id),
            'terms'           => null,

            'charges_json'    => json_encode([]),
            'items_json'      => json_encode($itemsJson, JSON_UNESCAPED_UNICODE),

            'amount_in_words' => '',
            'pdf_url'         => null,
            'signature'       => null,

            'user_id'         => $userId,
            'created_by'      => $userId,
            'updated_by'      => $userId,

            'kots_json'       => json_encode([]),
        ]);

        foreach ($invoiceRows as $row) {
            InvoiceItem::create([
                'invoice_id'      => $invoice->id,
                'item_id'         => $row['matched_item']->id,
                'description'     => $row['description'],
                'sac_code'        => $row['matched_item']->sac ?? null,
                'hsn_code'        => null,
                'quantity'        => $row['qty'],
                'gold_wt'         => 0,
                'silver_wt'       => 0,
                'gold_rate'       => 0,
                'silver_rate'     => 0,
                'gemstone_wt_ct'  => 0,
                'diamond_wt_ct'   => 0,
                'making_charge'   => $row['line_subtotal'],
                'making_rate'     => null,
                'discount'        => 0,
                'tax_percent'     => $row['tax_percent'],
                'rate'            => $row['rate_without_tax'],
                'amount'          => $row['gross_amount'],
            ]);
        }

        $oldApi = [];

        if (!empty($billRequest->api_response)) {
            $decoded = json_decode($billRequest->api_response, true);

            if (is_array($decoded)) {
                $oldApi = $decoded;
            }
        }

        $oldApi['matched_items'] = collect($invoiceRows)->map(function ($row) {
            return [
                'matched_item_id'   => $row['matched_item']->id,
                'matched_item_name' => $row['matched_item']->name,
                'qty'               => $row['qty'],
                'rate'              => $row['rate_without_tax'],
                'amount'            => $row['gross_amount'],
            ];
        })->values()->toArray();

        $oldApi['created_invoice_id']     = $invoice->id;
        $oldApi['created_invoice_number'] = $invoice->invoice_number;
        $oldApi['created_client_id']      = $client->id;
        $oldApi['invoice_type']           = 'quotation';
        $oldApi['invoice_date']           = $invoiceDate;
        $oldApi['gst_included']           = true;
        $oldApi['gross_amount']           = $grandTotal;
        $oldApi['taxable_amount']         = $subtotal;
        $oldApi['tax_amount']             = $taxAmount;
        $oldApi['processed_at']           = now(config('app.timezone'))->toDateTimeString();

        $billRequest->update([
            'remarks'      => 'Quotation created successfully. Quotation No: ' . $invoice->invoice_number,
            'api_response' => json_encode($oldApi, JSON_UNESCAPED_UNICODE),
        ]);

        return $invoice;
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
                'bil_request_id' => $billRequest->id,
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

    private function normCode($code): string
    {
        $code = trim((string) $code);

        if ($code === '') {
            return '';
        }

        $code = preg_replace('/\D/', '', $code);

        return str_pad($code, 2, '0', STR_PAD_LEFT);
    }






    public function index(Request $request)
    {
        $statuses = ['pending', 'processed', 'failed'];

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

        if ($request->filled('status') && in_array($request->status, $statuses)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('requested_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('requested_at', '<=', $request->to_date);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        $perPage = $request->get('per_page', 20);

        $billRequests = $query->latest('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Bill requests fetched successfully.',
            'data' => $billRequests,
        ]);
    }

    public function show(BillRequest $billRequest)
    {
        $apiResponse = null;

        if (!empty($billRequest->api_response)) {
            $decoded = json_decode($billRequest->api_response, true);
            $apiResponse = is_array($decoded) ? $decoded : null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Bill request details fetched successfully.',
            'data' => [
                'bill_request' => $billRequest,
                'api_response' => $apiResponse,
            ],
        ]);
    }

    public function showInvoice(BillRequest $billRequest)
    {
        $invoice = Invoice::withoutGlobalScope('business_id')
            ->where('bil_request_id', $billRequest->id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found for this bill request.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Invoice fetched successfully.',
            'data' => [
                'invoice' => $invoice,
                'preview_url' => route('invoices.preview', $invoice->id),
                'show_url' => route('invoices.show', $invoice->id),
            ],
        ]);
    }

    public function destroy(BillRequest $billRequest)
    {
        try {
            $billRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bill request deleted successfully.',
            ]);

        } catch (\Throwable $e) {
            Log::error('Bill request delete failed', [
                'bill_request_id' => $billRequest->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Bill request delete failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createInvoice(Request $request, BillRequest $billRequest)
    {
        // $request->validate([
        //     'business_id' => 'required|exists:businesses,id',
        //     'user_id' => 'nullable|integer',
        // ]);

        $bid = $request->business_id;
        $userId = $request->user_id;
        // $bid = 2;
        // $userId = 5;

        try {
            $invoice = DB::transaction(function () use ($billRequest, $bid, $userId) {
                $business = Business::findOrFail($bid);

                $client = null;

                if (!empty($billRequest->gst_number)) {
                    $client = Client::where('business_id', $bid)
                        ->where('gstin', trim($billRequest->gst_number))
                        ->first();
                }

                if (!$client && !empty($billRequest->customer_phone)) {
                    $client = Client::where('business_id', $bid)
                        ->where('mobile', trim($billRequest->customer_phone))
                        ->first();
                }

                if (!$client && !empty($billRequest->customer_email)) {
                    $client = Client::where('business_id', $bid)
                        ->where('email', trim($billRequest->customer_email))
                        ->first();
                }

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

                $itemId = trim((string) ($billRequest->package_name ?? ''));

                $matchedItem = Item::where('business_id', $bid)
                    ->where('is_active', 1)
                    ->where('type', 'service')
                    ->where('id', (int) $itemId)
                    ->first();

                if (!$matchedItem) {
                    $matchedItem = Item::where('business_id', $bid)
                        ->where('is_active', 1)
                        ->where('type', 'service')
                        ->where(function ($q) {
                            $q->where('name', 'like', '%Yearly Social Media Creative%')
                              ->where('name', 'like', '%Basic Package%');
                        })
                        ->first();
                }

                if (!$matchedItem) {
                    throw new \Exception('Selected service item nahi mila.');
                }

                $grossAmount = (float) (
                    $billRequest->selling_price
                    ?? $billRequest->payment_amount
                    ?? $billRequest->package_price
                    ?? $matchedItem->price
                    ?? 0
                );

                if ($grossAmount <= 0) {
                    throw new \Exception('Bill request amount invalid hai.');
                }

                $receivedAmount = (float) ($billRequest->payment_amount ?? 0);
                $invoiceDate = now(config('app.timezone'))->toDateString();

                $taxPercent = (float) ($matchedItem->tax_rate ?? 0);

                if ($taxPercent > 0) {
                    $subtotal = round($grossAmount * 100 / (100 + $taxPercent), 2);
                    $taxAmount = round($grossAmount - $subtotal, 2);
                } else {
                    $subtotal = round($grossAmount, 2);
                    $taxAmount = 0;
                }

                $grandTotal = round($grossAmount, 2);
                $balance = max(0, round($grandTotal - $receivedAmount, 2));

                $businessStateCode = $this->normCode($business->state_code ?? '');
                $clientStateCode = $this->normCode($client->state_code ?? '');

                $isIntra = ($businessStateCode !== '' && $clientStateCode !== '')
                    ? ($businessStateCode === $clientStateCode)
                    : false;

                if ($isIntra) {
                    $cgstPercent = round($taxPercent / 2, 2);
                    $sgstPercent = round($taxPercent / 2, 2);
                    $igstPercent = 0;

                    $cgstAmount = round($taxAmount / 2, 2);
                    $sgstAmount = round($taxAmount - $cgstAmount, 2);
                    $igstAmount = 0;
                } else {
                    $cgstPercent = 0;
                    $sgstPercent = 0;
                    $igstPercent = round($taxPercent, 2);

                    $cgstAmount = 0;
                    $sgstAmount = 0;
                    $igstAmount = round($taxAmount, 2);
                }

                $existingInvoice = Invoice::where('business_id', $bid)
                    ->where('bil_request_id', $billRequest->id)
                    ->first();

                $taxBase = 'INV';
                $prefix = \App\Services\InvoiceNumber::previewPrefix($invoiceDate, $taxBase);

                $alloc = \App\Services\InvoiceNumber::next(
                    (int) $bid,
                    $invoiceDate,
                    $prefix,
                    3,
                    'tax'
                );

                $invoiceNumber = $alloc['full'];

                \App\Services\InvoiceNumber::syncNextSeqIfMatches(
                    (int) $bid,
                    $invoiceDate,
                    $invoiceNumber,
                    3,
                    'tax'
                );

                $itemDescription = $matchedItem->description ?: $matchedItem->name;

                $itemsJson = [
                    [
                        'item_id' => $matchedItem->id,
                        'item_type' => 'service',
                        'description' => $itemDescription,
                        'hsn' => $matchedItem->sac ?? '',
                        'qty' => 1,
                        'tax_percent' => round($taxPercent, 2),
                        'service_rate' => round($subtotal, 2),
                        'rate' => round($subtotal, 2),
                        'tax_amount' => round($taxAmount, 2),
                        'amount' => round($grandTotal, 2),
                        'making_charge' => round($subtotal, 2),
                    ],
                ];

                $invoiceData = [
                    'business_id' => $bid,
                    'bil_request_id' => $billRequest->id,
                    'invoice_type' => 'tax',
                    'invoice_prefix' => $prefix,
                    'invoice_number' => $invoiceNumber,
                    'client_id' => $client->id,
                    'invoice_date' => $invoiceDate,
                    'payment_terms' => 0,

                    'subtotal' => round($subtotal, 2),
                    'tax_amount' => round($taxAmount, 2),

                    'cgst_percent' => $cgstPercent,
                    'cgst_amount' => $cgstAmount,
                    'sgst_percent' => $sgstPercent,
                    'sgst_amount' => $sgstAmount,
                    'igst_percent' => $igstPercent,
                    'igst_amount' => $igstAmount,

                    'discount_total' => 0,
                    'charge_total' => 0,
                    'tcs_percent' => 0,
                    'tcs_amount' => 0,
                    'round_off' => 0,
                    'less_amount' => 0,

                    'total' => $grandTotal,
                    'received_amount' => $receivedAmount,
                    'balance' => $balance,

                    'payment_method' => $billRequest->payment_method,
                    'reverse_charge' => 0,

                    'place_of_supply_state' => $client->state,
                    'place_of_supply_code' => $client->state_code,

                    'notes' => 'Created from Bill Request ID: ' . ($billRequest->source_request_id ?: $billRequest->id),

                    'charges_json' => json_encode([]),
                    'items_json' => json_encode($itemsJson),

                    'amount_in_words' => '',
                    'pdf_url' => null,
                    'signature' => null,
                    'user_id' => $userId,
                    'updated_by' => $userId,
                    'kots_json' => json_encode([]),
                ];

                if ($existingInvoice) {
                    $invoice = $existingInvoice;
                    $invoice->update($invoiceData);
                    InvoiceItem::where('invoice_id', $invoice->id)->delete();
                } else {
                    $invoiceData['created_by'] = $userId;
                    $invoice = Invoice::create($invoiceData);
                }

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_id' => $matchedItem->id,
                    'description' => $itemDescription,
                    'sac_code' => $matchedItem->sac ?? null,
                    'quantity' => 1,
                    'making_charge' => round($subtotal, 2),
                    'discount' => 0,
                    'tax_percent' => round($taxPercent, 2),
                    'rate' => round($subtotal, 2),
                    'amount' => round($grandTotal, 2),
                ]);

                if ($receivedAmount > 0) {
                    $method = strtolower(trim((string) $billRequest->payment_method));

                    InvoicePayment::where('invoice_id', $invoice->id)->delete();

                    InvoicePayment::create([
                        'business_id' => $bid,
                        'invoice_id' => $invoice->id,
                        'client_id' => $client->id,
                        'total_value' => $grandTotal,

                        'cash_amount' => $method === 'cash' ? $receivedAmount : 0,
                        'online_amount' => in_array($method, ['upi', 'online', 'bank'], true) ? $receivedAmount : 0,
                        'card_amount' => $method === 'card' ? $receivedAmount : 0,
                        'cheque_amount' => $method === 'cheque' ? $receivedAmount : 0,

                        'online_mode' => $method === 'upi' ? 'upi' : null,
                        'online_ref' => $billRequest->transaction_id,
                        'bank_name' => $billRequest->bank,

                        'received_total' => $receivedAmount,
                        'notes' => 'Created from bill request',
                        'paid_at' => now(config('app.timezone')),
                    ]);
                }

                $oldApi = [];

                if (!empty($billRequest->api_response)) {
                    $decoded = json_decode($billRequest->api_response, true);
                    if (is_array($decoded)) {
                        $oldApi = $decoded;
                    }
                }

                $oldApi['created_invoice_id'] = $invoice->id;
                $oldApi['created_invoice_number'] = $invoice->invoice_number;
                $oldApi['created_client_id'] = $client->id;
                $oldApi['invoice_type'] = 'tax';
                $oldApi['processed_at'] = now(config('app.timezone'))->toDateTimeString();

                $billRequest->update([
                    'status' => 'processed',
                    'remarks' => 'Tax invoice created successfully. Invoice No: ' . $invoice->invoice_number,
                    'api_response' => json_encode($oldApi, JSON_UNESCAPED_UNICODE),
                ]);

                return $invoice;
            });

            $this->pushSaveInvoiceApi($billRequest->fresh(), $invoice->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully from bill request.',
                'data' => [
                    'bill_request' => $billRequest->fresh(),
                    'invoice' => $invoice->fresh(),
                    'preview_url' => route('invoices.preview', $invoice->id),
                    'show_url' => route('invoices.show', $invoice->id),
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('Bill request to invoice API failed', [
                'bill_request_id' => $billRequest->id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            $billRequest->update([
                'status' => 'failed',
                'remarks' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invoice create/update failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



}
