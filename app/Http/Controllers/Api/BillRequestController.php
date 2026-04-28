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
use App\Models\Item;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class BillRequestController extends Controller
{
// public function store(Request $request)
// {
//     // =========================
//     // OPTIONAL TOKEN CHECK
//     // =========================
//     // $incomingToken = $request->bearerToken();
//     // $expectedToken = config('services.postimage.billing_api_token');
//     //
//     // if (!$incomingToken || !$expectedToken || !hash_equals($expectedToken, $incomingToken)) {
//     //     return response()->json([
//     //         'success' => false,
//     //         'message' => 'Unauthorized request.',
//     //     ], 401);
//     // }

//     try {
//         /*
//         |--------------------------------------------------------------------------
//         | Parse request safely
//         |--------------------------------------------------------------------------
//         */
//         $data = $request->all();

//         if (empty($data)) {
//             $json = json_decode($request->getContent(), true);
//             if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
//                 $data = $json;
//                 $request->merge($data);
//             }
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Normalize boolean
//         |--------------------------------------------------------------------------
//         */
//         $requestForBill = $request->input('request_for_bill');

//         if (in_array($requestForBill, ['1', 1, true, 'true', 'yes', 'on'], true)) {
//             $requestForBill = true;
//         } elseif (in_array($requestForBill, ['0', 0, false, 'false', 'no', 'off'], true)) {
//             $requestForBill = false;
//         } else {
//             $requestForBill = null;
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Validation
//         |--------------------------------------------------------------------------
//         */
//         // $validator = \Validator::make([
//         //     'request_for_bill'              => $requestForBill,

//         //     'customer_id'                   => $request->input('customer_id'),
//         //     'customer_name'                 => $request->input('customer_name'),
//         //     'customer_email'                => $request->input('customer_email'),
//         //     'customer_phone'                => $request->input('customer_phone'),
//         //     'customer_phone1'               => $request->input('customer_phone1'),
//         //     'customer_business_name'        => $request->input('customer_business_name'),
//         //     'customer_country'              => $request->input('customer_country'),
//         //     'customer_state'                => $request->input('customer_state'),
//         //     'customer_city'                 => $request->input('customer_city'),
//         //     'customer_pin'                  => $request->input('customer_pin'),
//         //     'customer_address'              => $request->input('customer_address'),
//         //     'customer_gst_number'           => $request->input('customer_gst_number'),

//         //     'package_id'                    => $request->input('package_id'),
//         //     'package_name'                  => $request->input('package_name'),
//         //     'package_description'           => $request->input('package_description'),
//         //     'package_price'                 => $request->input('package_price'),
//         //     'package_duration'              => $request->input('package_duration'),

//         //     'user_package_id'               => $request->input('user_package_id'),
//         //     'user_package_user_id'          => $request->input('user_package_user_id'),
//         //     'user_package_package_id'       => $request->input('user_package_package_id'),
//         //     'user_package_selling_price'    => $request->input('user_package_selling_price'),
//         //     'user_package_start_date'       => $request->input('user_package_start_date'),
//         //     'user_package_expiry_date'      => $request->input('user_package_expiry_date'),
//         //     'user_package_status'           => $request->input('user_package_status'),

//         //     'payment_id'                    => $request->input('payment_id'),
//         //     'payment_amount'                => $request->input('payment_amount'),
//         //     'payment_method'                => $request->input('payment_method'),
//         //     'payment_transaction_id'        => $request->input('payment_transaction_id'),
//         //     'payment_bank'                  => $request->input('payment_bank'),
//         //     'payment_date'                  => $request->input('payment_date'),
//         //     'payment_activated_by'          => $request->input('payment_activated_by'),
//         //     'payment_customer_type'         => $request->input('payment_customer_type'),
//         //     'payment_old_customer_user_id'  => $request->input('payment_old_customer_user_id'),

//         //     'meta_created_by'               => $request->input('meta_created_by'),
//         //     'meta_created_by_name'          => $request->input('meta_created_by_name'),
//         //     'meta_request_date'             => $request->input('meta_request_date'),
//         //     'meta_source_software'          => $request->input('meta_source_software'),
//         // ], [
//         //     'request_for_bill'              => ['required', 'boolean'],

//         //     'customer_id'                   => ['nullable', 'integer'],
//         //     'customer_name'                 => ['required', 'string', 'max:255'],
//         //     'customer_email'                => ['nullable', 'email', 'max:255'],
//         //     'customer_phone'                => ['nullable', 'string', 'max:255'],
//         //     'customer_phone1'               => ['nullable', 'string', 'max:255'],
//         //     'customer_business_name'        => ['nullable', 'string', 'max:255'],
//         //     'customer_country'              => ['nullable', 'string', 'max:255'],
//         //     'customer_state'                => ['nullable', 'string', 'max:255'],
//         //     'customer_city'                 => ['nullable', 'string', 'max:255'],
//         //     'customer_pin'                  => ['nullable', 'string', 'max:255'],
//         //     'customer_address'              => ['nullable', 'string'],
//         //     'customer_gst_number'           => ['required', 'string', 'size:15'],

//         //     'package_id'                    => ['nullable', 'integer'],
//         //     'package_name'                  => ['nullable', 'string', 'max:255'],
//         //     'package_description'           => ['nullable', 'string'],
//         //     'package_price'                 => ['nullable', 'numeric'],
//         //     'package_duration'              => ['nullable', 'integer'],

//         //     'user_package_id'               => ['nullable', 'integer'],
//         //     'user_package_user_id'          => ['nullable', 'integer'],
//         //     'user_package_package_id'       => ['nullable', 'integer'],
//         //     'user_package_selling_price'    => ['nullable', 'numeric'],
//         //     'user_package_start_date'       => ['nullable', 'date'],
//         //     'user_package_expiry_date'      => ['nullable', 'date'],
//         //     'user_package_status'           => ['nullable', 'string', 'max:255'],

//         //     'payment_id'                    => ['nullable', 'integer'],
//         //     'payment_amount'                => ['nullable', 'numeric'],
//         //     'payment_method'                => ['nullable', 'string', 'max:255'],
//         //     'payment_transaction_id'        => ['nullable', 'string', 'max:255'],
//         //     'payment_bank'                  => ['nullable', 'string', 'max:255'],
//         //     'payment_date'                  => ['nullable', 'date'],
//         //     'payment_activated_by'          => ['nullable', 'string', 'max:255'],
//         //     'payment_customer_type'         => ['nullable', 'string', 'max:255'],
//         //     'payment_old_customer_user_id'  => ['nullable', 'integer'],

//         //     'meta_created_by'               => ['nullable', 'integer'],
//         //     'meta_created_by_name'          => ['nullable', 'string', 'max:255'],
//         //     'meta_request_date'             => ['nullable', 'date'],
//         //     'meta_source_software'          => ['nullable', 'string', 'max:255'],
//         // ]);


//         $validator = \Validator::make([
//             'request_for_bill'        => $requestForBill,

//             'source_software'         => $request->input('source_software'),
//             'source_customer_id'      => $request->input('source_customer_id'),
//             'source_package_id'       => $request->input('source_package_id'),
//             'source_user_package_id'  => $request->input('source_user_package_id'),
//             'source_payment_id'       => $request->input('source_payment_id'),

//             'customer_name'           => $request->input('customer_name'),
//             'customer_email'          => $request->input('customer_email'),
//             'customer_phone'          => $request->input('customer_phone'),
//             'customer_phone1'         => $request->input('customer_phone1'),
//             'business_name'           => $request->input('business_name'),
//             'country'                 => $request->input('country'),
//             'state'                   => $request->input('state'),
//             'city'                    => $request->input('city'),
//             'pin'                     => $request->input('pin'),
//             'address'                 => $request->input('address'),
//             'customer_gst_number'     => $request->input('customer_gst_number'),

//             'package_id'              => $request->input('package_id'),
//             'package_name'            => $request->input('package_name'),
//             'package_description'     => $request->input('package_description'),
//             'package_price'           => $request->input('package_price'),
//             'package_duration'        => $request->input('package_duration'),

//             'start_date'              => $request->input('start_date'),
//             'expiry_date'             => $request->input('expiry_date'),
//             'status'                  => $request->input('status'),

//             'payment_amount'          => $request->input('payment_amount'),
//             'payment_method'          => $request->input('payment_method'),
//             'transaction_id'          => $request->input('transaction_id'),
//             'bank'                    => $request->input('bank'),
//             'payment_date'            => $request->input('payment_date'),
//             'activated_by'            => $request->input('activated_by'),
//             'customer_type'           => $request->input('customer_type'),
//             'old_customer_user_id'    => $request->input('old_customer_user_id'),

//             'created_by'              => $request->input('created_by'),
//             'created_by_name'         => $request->input('created_by_name'),
//             'request_date'            => $request->input('request_date'),
//         ], [
//             'request_for_bill'        => ['required', 'boolean'],

//             'source_software'         => ['nullable', 'string', 'max:255'],
//             'source_customer_id'      => ['nullable', 'integer'],
//             'source_package_id'       => ['nullable', 'integer'],
//             'source_user_package_id'  => ['nullable', 'integer'],
//             'source_payment_id'       => ['nullable', 'integer'],

//             'customer_name'           => ['required', 'string', 'max:255'],
//             'customer_email'          => ['nullable', 'email', 'max:255'],
//             'customer_phone'          => ['nullable', 'string', 'max:255'],
//             'customer_phone1'         => ['nullable', 'string', 'max:255'],
//             'business_name'           => ['nullable', 'string', 'max:255'],
//             'country'                 => ['nullable', 'string', 'max:255'],
//             'state'                   => ['nullable', 'string', 'max:255'],
//             'city'                    => ['nullable', 'string', 'max:255'],
//             'pin'                     => ['nullable', 'string', 'max:255'],
//             'address'                 => ['nullable', 'string'],
//             'customer_gst_number'     => ['required', 'string', 'size:15'],

//             'package_id'              => ['nullable', 'integer'],
//             'package_name'            => ['nullable', 'string', 'max:255'],
//             'package_description'     => ['nullable', 'string'],
//             'package_price'           => ['nullable', 'numeric'],
//             'package_duration'        => ['nullable', 'integer'],

//             'start_date'              => ['nullable', 'date'],
//             'expiry_date'             => ['nullable', 'date'],
//             'status'                  => ['nullable', 'string', 'max:255'],

//             'payment_amount'          => ['nullable', 'numeric'],
//             'payment_method'          => ['nullable', 'string', 'max:255'],
//             'transaction_id'          => ['nullable', 'string', 'max:255'],
//             'bank'                    => ['nullable', 'string', 'max:255'],
//             'payment_date'            => ['nullable', 'date'],
//             'activated_by'            => ['nullable', 'string', 'max:255'],
//             'customer_type'           => ['nullable', 'string', 'max:255'],
//             'old_customer_user_id'    => ['nullable', 'integer'],

//             'created_by'              => ['nullable', 'integer'],
//             'created_by_name'         => ['nullable', 'string', 'max:255'],
//             'request_date'            => ['nullable', 'date'],
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Validation failed.',
//                 'errors'  => $validator->errors(),
//                 'received_payload' => $request->all(),
//             ], 422);
//         }

//         $validated = $validator->validated();

//         /*
//         |--------------------------------------------------------------------------
//         | Build payload for storage/log
//         |--------------------------------------------------------------------------
//         */
//         $fullPayload = [
//             'request_for_bill' => $validated['request_for_bill'],

//             'customer_id' => $validated['customer_id'] ?? null,
//             'customer_name' => $validated['customer_name'] ?? null,
//             'customer_email' => $validated['customer_email'] ?? null,
//             'customer_phone' => $validated['customer_phone'] ?? null,
//             'customer_phone1' => $validated['customer_phone1'] ?? null,
//             'customer_business_name' => $validated['customer_business_name'] ?? null,
//             'customer_country' => $validated['customer_country'] ?? null,
//             'customer_state' => $validated['customer_state'] ?? null,
//             'customer_city' => $validated['customer_city'] ?? null,
//             'customer_pin' => $validated['customer_pin'] ?? null,
//             'customer_address' => $validated['customer_address'] ?? null,
//             'customer_gst_number' => strtoupper(trim($validated['customer_gst_number'] ?? '')),

//             'package_id' => $validated['package_id'] ?? null,
//             'package_name' => $validated['package_name'] ?? null,
//             'package_description' => $validated['package_description'] ?? null,
//             'package_price' => $validated['package_price'] ?? null,
//             'package_duration' => $validated['package_duration'] ?? null,

//             'user_package_id' => $validated['user_package_id'] ?? null,
//             'user_package_user_id' => $validated['user_package_user_id'] ?? null,
//             'user_package_package_id' => $validated['user_package_package_id'] ?? null,
//             'user_package_selling_price' => $validated['user_package_selling_price'] ?? null,
//             'user_package_start_date' => $validated['user_package_start_date'] ?? null,
//             'user_package_expiry_date' => $validated['user_package_expiry_date'] ?? null,
//             'user_package_status' => $validated['user_package_status'] ?? null,

//             'payment_id' => $validated['payment_id'] ?? null,
//             'payment_amount' => $validated['payment_amount'] ?? null,
//             'payment_method' => $validated['payment_method'] ?? null,
//             'payment_transaction_id' => $validated['payment_transaction_id'] ?? null,
//             'payment_bank' => $validated['payment_bank'] ?? null,
//             'payment_date' => $validated['payment_date'] ?? null,
//             'payment_activated_by' => $validated['payment_activated_by'] ?? null,
//             'payment_customer_type' => $validated['payment_customer_type'] ?? null,
//             'payment_old_customer_user_id' => $validated['payment_old_customer_user_id'] ?? null,

//             'meta_created_by' => $validated['meta_created_by'] ?? null,
//             'meta_created_by_name' => $validated['meta_created_by_name'] ?? null,
//             'meta_request_date' => $validated['meta_request_date'] ?? null,
//             'meta_source_software' => $validated['meta_source_software'] ?? 'postimage',
//         ];

//         /*
//         |--------------------------------------------------------------------------
//         | Insert into bill_requests table
//         |--------------------------------------------------------------------------
//         */
//         // $billRequest = \App\Models\BillRequest::create([
//         //     'source_software'        => $validated['meta_source_software'] ?? 'postimage',
//         //     'source_request_id'      => $request->header('X-Request-Id'),

//         //     'source_customer_id'     => $validated['customer_id'] ?? null,
//         //     'source_package_id'      => $validated['package_id'] ?? null,
//         //     'source_user_package_id' => $validated['user_package_id'] ?? null,
//         //     'source_payment_id'      => $validated['payment_id'] ?? null,

//         //     'customer_name'          => $validated['customer_name'] ?? null,
//         //     'customer_email'         => $validated['customer_email'] ?? null,
//         //     'customer_phone'         => $validated['customer_phone'] ?? null,
//         //     'customer_phone1'        => $validated['customer_phone1'] ?? null,
//         //     'business_name'          => $validated['customer_business_name'] ?? null,
//         //     'country'                => $validated['customer_country'] ?? null,
//         //     'state'                  => $validated['customer_state'] ?? null,
//         //     'city'                   => $validated['customer_city'] ?? null,
//         //     'pin'                    => $validated['customer_pin'] ?? null,
//         //     'address'                => $validated['customer_address'] ?? null,
//         //     'gst_number'             => strtoupper(trim($validated['customer_gst_number'] ?? '')),

//         //     'package_name'           => $validated['package_name'] ?? null,
//         //     'package_price'          => $validated['package_price'] ?? null,
//         //     'package_duration'       => $validated['package_duration'] ?? null,
//         //     'selling_price'          => $validated['user_package_selling_price'] ?? null,

//         //     'payment_amount'         => $validated['payment_amount'] ?? null,
//         //     'payment_method'         => $validated['payment_method'] ?? null,
//         //     'transaction_id'         => $validated['payment_transaction_id'] ?? null,
//         //     'bank'                   => $validated['payment_bank'] ?? null,
//         //     'payment_date'           => $validated['payment_date'] ?? null,

//         //     'activated_by'           => $validated['payment_activated_by'] ?? null,
//         //     'customer_type'          => $validated['payment_customer_type'] ?? null,
//         //     'old_customer_user_id'   => $validated['payment_old_customer_user_id'] ?? null,

//         //     'status'                 => 'pending',
//         //     'remarks'                => null,
//         //     'full_payload'           => json_encode($fullPayload, JSON_UNESCAPED_UNICODE),
//         //     'api_response'           => json_encode([
//         //         'received_from' => $validated['meta_source_software'] ?? 'postimage',
//         //         'received_at'   => now()->toDateTimeString(),
//         //     ], JSON_UNESCAPED_UNICODE),
//         //     'requested_at'           => $validated['meta_request_date'] ?? now(),
//         // ]);


//         $billRequest = \App\Models\BillRequest::create([
//             'source_software'        => $validated['source_software'] ?? 'postimage',
//             'source_request_id'      => $request->header('X-Request-Id'),

//             'source_customer_id'     => $validated['source_customer_id'] ?? null,
//             'source_package_id'      => $validated['source_package_id'] ?? null,
//             'source_user_package_id' => $validated['source_user_package_id'] ?? null,
//             'source_payment_id'      => $validated['source_payment_id'] ?? null,

//             'customer_name'          => $validated['customer_name'] ?? null,
//             'customer_email'         => $validated['customer_email'] ?? null,
//             'customer_phone'         => $validated['customer_phone'] ?? null,
//             'customer_phone1'        => $validated['customer_phone1'] ?? null,
//             'business_name'          => $validated['business_name'] ?? null,
//             'country'                => $validated['country'] ?? null,
//             'state'                  => $validated['state'] ?? null,
//             'city'                   => $validated['city'] ?? null,
//             'pin'                    => $validated['pin'] ?? null,
//             'address'                => $validated['address'] ?? null,
//             'gst_number'             => strtoupper(trim($validated['customer_gst_number'] ?? '')),

//             'package_name'           => $validated['package_name'] ?? null,
//             'package_price'          => $validated['package_price'] ?? null,
//             'package_duration'       => $validated['package_duration'] ?? null,
//             'selling_price'          => $validated['package_price'] ?? null,

//             'payment_amount'         => $validated['payment_amount'] ?? null,
//             'payment_method'         => $validated['payment_method'] ?? null,
//             'transaction_id'         => $validated['transaction_id'] ?? null,
//             'bank'                   => $validated['bank'] ?? null,
//             'payment_date'           => $validated['payment_date'] ?? null,

//             'activated_by'           => $validated['activated_by'] ?? null,
//             'customer_type'          => $validated['customer_type'] ?? null,
//             'old_customer_user_id'   => $validated['old_customer_user_id'] ?? null,

//             'status'                 => 'pending',
//             'remarks'                => null,
//             'full_payload'           => json_encode($request->all(), JSON_UNESCAPED_UNICODE),
//             'api_response'           => json_encode([
//                 'received_from' => $validated['source_software'] ?? 'postimage',
//                 'received_at'   => now()->toDateTimeString(),
//             ], JSON_UNESCAPED_UNICODE),
//             'requested_at'           => $validated['request_date'] ?? now(),
//         ]);

//         \Log::info('Billing request received successfully', [
//             'billing_request_id' => $billRequest->id,
//             'customer_name'      => $billRequest->customer_name,
//             'source_customer_id' => $billRequest->source_customer_id,
//         ]);

//         return response()->json([
//             'success'            => true,
//             'message'            => 'Billing request received successfully.',
//             'billing_request_id' => $billRequest->id,
//             'status'             => $billRequest->status,
//             'data'               => $billRequest,
//         ], 201);

//     } catch (\Throwable $e) {
//         \Log::error('Billing request API failed', [
//             'message' => $e->getMessage(),
//             'line'    => $e->getLine(),
//             'file'    => $e->getFile(),
//             'payload' => $request->all(),
//         ]);

//         return response()->json([
//             'success' => false,
//             'message' => 'Something went wrong while saving billing request.',
//             'error'   => $e->getMessage(),
//         ], 500);
//     }
// }

    // public function store(Request $request)
    // {
    //     // =========================
    //     // OPTIONAL TOKEN CHECK
    //     // =========================
    //     // $incomingToken = $request->bearerToken();
    //     // $expectedToken = config('services.postimage.billing_api_token');
    //     //
    //     // if (!$incomingToken || !$expectedToken || !hash_equals($expectedToken, $incomingToken)) {
    //     //     return response()->json([
    //     //         'success' => false,
    //     //         'message' => 'Unauthorized request.',
    //     //     ], 401);
    //     // }

    //     try {
    //         /*
    //         |--------------------------------------------------------------------------
    //         | Parse request safely
    //         |--------------------------------------------------------------------------
    //         */
    //         $data = $request->all();

    //         if (empty($data)) {
    //             $json = json_decode($request->getContent(), true);
    //             if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
    //                 $data = $json;
    //                 $request->merge($data);
    //             }
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Normalize boolean
    //         |--------------------------------------------------------------------------
    //         */
    //         $requestForBill = $request->input('request_for_bill');

    //         if (in_array($requestForBill, ['1', 1, true, 'true', 'yes', 'on'], true)) {
    //             $requestForBill = true;
    //         } elseif (in_array($requestForBill, ['0', 0, false, 'false', 'no', 'off'], true)) {
    //             $requestForBill = false;
    //         } else {
    //             $requestForBill = null;
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Validation
    //         |--------------------------------------------------------------------------
    //         */
    //         $validator = \Validator::make([
    //             'request_for_bill'        => $requestForBill,

    //             'source_software'         => $request->input('source_software'),
    //             'source_customer_id'      => $request->input('source_customer_id'),
    //             'source_package_id'       => $request->input('source_package_id'),
    //             'source_user_package_id'  => $request->input('source_user_package_id'),
    //             'source_payment_id'       => $request->input('source_payment_id'),

    //             'customer_name'           => $request->input('customer_name'),
    //             'customer_email'          => $request->input('customer_email'),
    //             'customer_phone'          => $request->input('customer_phone'),
    //             'customer_phone1'         => $request->input('customer_phone1'),
    //             'country'                 => $request->input('country'),
    //             'state'                   => $request->input('state'),
    //             'city'                    => $request->input('city'),
    //             'pin'                     => $request->input('pin'),
    //             'address'                 => $request->input('address'),
    //             'customer_gst_number'     => $request->input('customer_gst_number'),

    //             'package_id'              => $request->input('package_id'),
    //             'package_name'            => $request->input('package_name'),
    //             'package_description'     => $request->input('package_description'),
    //             'package_price'           => $request->input('package_price'),
    //             'package_duration'        => $request->input('package_duration'),

    //             'start_date'              => $request->input('start_date'),
    //             'expiry_date'             => $request->input('expiry_date'),
    //             'status'                  => $request->input('status'),

    //             'payment_amount'          => $request->input('payment_amount'),
    //             'payment_method'          => $request->input('payment_method'),
    //             'transaction_id'          => $request->input('transaction_id'),
    //             'bank'                    => $request->input('bank'),
    //             'payment_date'            => $request->input('payment_date'),
    //             'activated_by'            => $request->input('activated_by'),
    //             'customer_type'           => $request->input('customer_type'),
    //             'old_customer_user_id'    => $request->input('old_customer_user_id'),

    //             'created_by'              => $request->input('created_by'),
    //             'created_by_name'         => $request->input('created_by_name'),
    //             'request_date'            => $request->input('request_date'),
    //         ], [
    //             'request_for_bill'        => ['required', 'boolean'],

    //             'source_software'         => ['nullable', 'string', 'max:255'],
    //             'source_customer_id'      => ['nullable', 'integer'],
    //             'source_package_id'       => ['nullable', 'integer'],
    //             'source_user_package_id'  => ['nullable', 'integer'],
    //             'source_payment_id'       => ['nullable', 'integer'],

    //             'customer_name'           => ['required', 'string', 'max:255'],
    //             'customer_email'          => ['nullable', 'email', 'max:255'],
    //             'customer_phone'          => ['nullable', 'string', 'max:255'],
    //             'customer_phone1'         => ['nullable', 'string', 'max:255'],
    //             'country'                 => ['nullable', 'string', 'max:255'],
    //             'state'                   => ['nullable', 'string', 'max:255'],
    //             'city'                    => ['nullable', 'string', 'max:255'],
    //             'pin'                     => ['nullable', 'string', 'max:255'],
    //             'address'                 => ['nullable', 'string'],

    //             // GST OPTIONAL
    //             'customer_gst_number'     => ['nullable', 'string', 'size:15'],

    //             'package_id'              => ['nullable', 'integer'],
    //             'package_name'            => ['nullable', 'string', 'max:255'],
    //             'package_description'     => ['nullable', 'string'],
    //             'package_price'           => ['nullable', 'numeric'],
    //             'package_duration'        => ['nullable', 'integer'],

    //             'start_date'              => ['nullable', 'date'],
    //             'expiry_date'             => ['nullable', 'date'],
    //             'status'                  => ['nullable', 'string', 'max:255'],

    //             'payment_amount'          => ['nullable', 'numeric'],
    //             'payment_method'          => ['nullable', 'string', 'max:255'],
    //             'transaction_id'          => ['nullable', 'string', 'max:255'],
    //             'bank'                    => ['nullable', 'string', 'max:255'],
    //             'payment_date'            => ['nullable', 'date'],
    //             'activated_by'            => ['nullable', 'string', 'max:255'],
    //             'customer_type'           => ['nullable', 'string', 'max:255'],
    //             'old_customer_user_id'    => ['nullable', 'integer'],

    //             'created_by'              => ['nullable', 'integer'],
    //             'created_by_name'         => ['nullable', 'string', 'max:255'],
    //             'request_date'            => ['nullable', 'date'],
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Validation failed.',
    //                 'errors'  => $validator->errors(),
    //                 'received_payload' => $request->all(),
    //             ], 422);
    //         }

    //         $validated = $validator->validated();

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Normalize GST
    //         |--------------------------------------------------------------------------
    //         */
    //         $gstNumber = null;
    //         if (!empty($validated['customer_gst_number'])) {
    //             $gstNumber = strtoupper(trim($validated['customer_gst_number']));
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Build payload for storage/log
    //         |--------------------------------------------------------------------------
    //         */
    //         $fullPayload = [
    //             'request_for_bill'        => $validated['request_for_bill'],

    //             'source_software'         => $validated['source_software'] ?? null,
    //             'source_customer_id'      => $validated['source_customer_id'] ?? null,
    //             'source_package_id'       => $validated['source_package_id'] ?? null,
    //             'source_user_package_id'  => $validated['source_user_package_id'] ?? null,
    //             'source_payment_id'       => $validated['source_payment_id'] ?? null,

    //             'customer_name'           => $validated['customer_name'] ?? null,
    //             'customer_email'          => $validated['customer_email'] ?? null,
    //             'customer_phone'          => $validated['customer_phone'] ?? null,
    //             'customer_phone1'         => $validated['customer_phone1'] ?? null,
    //             'country'                 => $validated['country'] ?? null,
    //             'state'                   => $validated['state'] ?? null,
    //             'city'                    => $validated['city'] ?? null,
    //             'pin'                     => $validated['pin'] ?? null,
    //             'address'                 => $validated['address'] ?? null,
    //             'customer_gst_number'     => $gstNumber,

    //             'package_id'              => $validated['package_id'] ?? null,
    //             'package_name'            => $validated['package_name'] ?? null,
    //             'package_description'     => $validated['package_description'] ?? null,
    //             'package_price'           => $validated['package_price'] ?? null,
    //             'package_duration'        => $validated['package_duration'] ?? null,

    //             'start_date'              => $validated['start_date'] ?? null,
    //             'expiry_date'             => $validated['expiry_date'] ?? null,
    //             'status'                  => $validated['status'] ?? null,

    //             'payment_amount'          => $validated['payment_amount'] ?? null,
    //             'payment_method'          => $validated['payment_method'] ?? null,
    //             'transaction_id'          => $validated['transaction_id'] ?? null,
    //             'bank'                    => $validated['bank'] ?? null,
    //             'payment_date'            => $validated['payment_date'] ?? null,
    //             'activated_by'            => $validated['activated_by'] ?? null,
    //             'customer_type'           => $validated['customer_type'] ?? null,
    //             'old_customer_user_id'    => $validated['old_customer_user_id'] ?? null,

    //             'created_by'              => $validated['created_by'] ?? null,
    //             'created_by_name'         => $validated['created_by_name'] ?? null,
    //             'request_date'            => $validated['request_date'] ?? null,
    //         ];

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Insert into bill_requests table
    //         |--------------------------------------------------------------------------
    //         */
    //         $billRequest = \App\Models\BillRequest::create([
    //             'source_software'        => $validated['source_software'] ?? 'postimage',
    //             'source_request_id'      => $request->header('X-Request-Id'),

    //             'source_customer_id'     => $validated['source_customer_id'] ?? null,
    //             'source_package_id'      => $validated['source_package_id'] ?? null,
    //             'source_user_package_id' => $validated['source_user_package_id'] ?? null,
    //             'source_payment_id'      => $validated['source_payment_id'] ?? null,

    //             'customer_name'          => $validated['customer_name'] ?? null,
    //             'customer_email'         => $validated['customer_email'] ?? null,
    //             'customer_phone'         => $validated['customer_phone'] ?? null,
    //             'customer_phone1'        => $validated['customer_phone1'] ?? null,
    //             'country'                => $validated['country'] ?? null,
    //             'state'                  => $validated['state'] ?? null,
    //             'city'                   => $validated['city'] ?? null,
    //             'pin'                    => $validated['pin'] ?? null,
    //             'address'                => $validated['address'] ?? null,
    //             'gst_number'             => $gstNumber,

    //             'package_name'           => $validated['package_name'] ?? null,
    //             'package_price'          => $validated['package_price'] ?? null,
    //             'package_duration'       => $validated['package_duration'] ?? null,
    //             'selling_price'          => $validated['package_price'] ?? null,

    //             'payment_amount'         => $validated['payment_amount'] ?? null,
    //             'payment_method'         => $validated['payment_method'] ?? null,
    //             'transaction_id'         => $validated['transaction_id'] ?? null,
    //             'bank'                   => $validated['bank'] ?? null,
    //             'payment_date'           => $validated['payment_date'] ?? null,

    //             'activated_by'           => $validated['activated_by'] ?? null,
    //             'customer_type'          => $validated['customer_type'] ?? null,
    //             'old_customer_user_id'   => $validated['old_customer_user_id'] ?? null,

    //             'status'                 => 'pending',
    //             'remarks'                => null,
    //             'full_payload'           => json_encode($fullPayload, JSON_UNESCAPED_UNICODE),
    //             'api_response'           => json_encode([
    //                 'received_from' => $validated['source_software'] ?? 'postimage',
    //                 'received_at'   => now()->toDateTimeString(),
    //             ], JSON_UNESCAPED_UNICODE),
    //             'requested_at'           => $validated['request_date'] ?? now(),
    //         ]);

    //         \Log::info('Billing request received successfully', [
    //             'billing_request_id' => $billRequest->id,
    //             'customer_name'      => $billRequest->customer_name,
    //             'source_customer_id' => $billRequest->source_customer_id,
    //         ]);

    //         return response()->json([
    //             'success'            => true,
    //             'message'            => 'Billing request received successfully.',
    //             'billing_request_id' => $billRequest->id,
    //             'status'             => $billRequest->status,
    //             'data'               => $billRequest,
    //         ], 201);

    //     } catch (\Throwable $e) {
    //         \Log::error('Billing request API failed', [
    //             'message' => $e->getMessage(),
    //             'line'    => $e->getLine(),
    //             'file'    => $e->getFile(),
    //             'payload' => $request->all(),
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Something went wrong while saving billing request.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }








    // public function store(Request $request)
    // {
    //     try {
    //         $data = $request->all();

    //         if (empty($data)) {
    //             $json = json_decode($request->getContent(), true);

    //             if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
    //                 $data = $json;
    //                 $request->merge($data);
    //             }
    //         }

    //         $requestForBill = $request->input('request_for_bill');

    //         if (in_array($requestForBill, ['1', 1, true, 'true', 'yes', 'on'], true)) {
    //             $requestForBill = true;
    //         } elseif (in_array($requestForBill, ['0', 0, false, 'false', 'no', 'off'], true)) {
    //             $requestForBill = false;
    //         } else {
    //             $requestForBill = null;
    //         }

    //         $validator = Validator::make([
    //             'request_for_bill'       => $requestForBill,
    //             'business_id'            => $request->input('business_id'),

    //             'source_software'        => $request->input('source_software'),
    //             'source_customer_id'     => $request->input('source_customer_id'),
    //             'source_package_id'      => $request->input('source_package_id'),
    //             'source_user_package_id' => $request->input('source_user_package_id'),
    //             'source_payment_id'      => $request->input('source_payment_id'),

    //             'customer_name'          => $request->input('customer_name'),
    //             'customer_email'         => $request->input('customer_email'),
    //             'customer_phone'         => $request->input('customer_phone'),
    //             'customer_phone1'        => $request->input('customer_phone1'),
    //             'country'                => $request->input('country'),
    //             'state'                  => $request->input('state'),
    //             'city'                   => $request->input('city'),
    //             'pin'                    => $request->input('pin'),
    //             'address'                => $request->input('address'),
    //             'customer_gst_number'    => $request->input('customer_gst_number'),

    //             'package_id'             => $request->input('package_id'),
    //             'package_name'           => $request->input('package_name'),
    //             'package_description'    => $request->input('package_description'),
    //             'package_price'          => $request->input('package_price'),
    //             'package_duration'       => $request->input('package_duration'),

    //             'start_date'             => $request->input('start_date'),
    //             'expiry_date'            => $request->input('expiry_date'),
    //             'status'                 => $request->input('status'),

    //             'payment_amount'         => $request->input('payment_amount'),
    //             'payment_method'         => $request->input('payment_method'),
    //             'transaction_id'         => $request->input('transaction_id'),
    //             'bank'                   => $request->input('bank'),
    //             'payment_date'           => $request->input('payment_date'),
    //             'activated_by'           => $request->input('activated_by'),
    //             'customer_type'          => $request->input('customer_type'),
    //             'old_customer_user_id'   => $request->input('old_customer_user_id'),

    //             'created_by'             => $request->input('created_by'),
    //             'created_by_name'        => $request->input('created_by_name'),
    //             'request_date'           => $request->input('request_date'),
    //         ], [
    //             'request_for_bill'       => ['required', 'boolean'],
    //             'business_id'            => ['nullable', 'integer'],

    //             'source_software'        => ['nullable', 'string', 'max:255'],
    //             'source_customer_id'     => ['nullable', 'integer'],
    //             'source_package_id'      => ['nullable', 'integer'],
    //             'source_user_package_id' => ['nullable', 'integer'],
    //             'source_payment_id'      => ['nullable', 'integer'],

    //             'customer_name'          => ['required', 'string', 'max:255'],
    //             'customer_email'         => ['nullable', 'email', 'max:255'],
    //             'customer_phone'         => ['nullable', 'string', 'max:255'],
    //             'customer_phone1'        => ['nullable', 'string', 'max:255'],
    //             'country'                => ['nullable', 'string', 'max:255'],
    //             'state'                  => ['nullable', 'string', 'max:255'],
    //             'city'                   => ['nullable', 'string', 'max:255'],
    //             'pin'                    => ['nullable', 'string', 'max:255'],
    //             'address'                => ['nullable', 'string'],
    //             'customer_gst_number'    => ['nullable', 'string', 'size:15'],

    //             'package_id'             => ['nullable', 'integer'],
    //             'package_name'           => ['nullable'],
    //             'package_description'    => ['nullable', 'string'],
    //             'package_price'          => ['nullable', 'numeric'],
    //             'package_duration'       => ['nullable', 'integer'],

    //             'start_date'             => ['nullable', 'date'],
    //             'expiry_date'            => ['nullable', 'date'],
    //             'status'                 => ['nullable', 'string', 'max:255'],

    //             'payment_amount'         => ['nullable', 'numeric'],
    //             'payment_method'         => ['nullable', 'string', 'max:255'],
    //             'transaction_id'         => ['nullable', 'string', 'max:255'],
    //             'bank'                   => ['nullable', 'string', 'max:255'],
    //             'payment_date'           => ['nullable', 'date'],
    //             'activated_by'           => ['nullable', 'string', 'max:255'],
    //             'customer_type'          => ['nullable', 'string', 'max:255'],
    //             'old_customer_user_id'   => ['nullable', 'integer'],

    //             'created_by'             => ['nullable', 'integer'],
    //             'created_by_name'        => ['nullable', 'string', 'max:255'],
    //             'request_date'           => ['nullable', 'date'],
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success'          => false,
    //                 'message'          => 'Validation failed.',
    //                 'errors'           => $validator->errors(),
    //                 'received_payload' => $request->all(),
    //             ], 422);
    //         }

    //         $validated = $validator->validated();

    //         $gstNumber = null;

    //         if (!empty($validated['customer_gst_number'])) {
    //             $gstNumber = strtoupper(trim($validated['customer_gst_number']));
    //         }

    //         $fullPayload = [
    //             'request_for_bill'       => $validated['request_for_bill'],

    //             'business_id'            => $validated['business_id'] ?? null,
    //             'source_software'        => $validated['source_software'] ?? null,
    //             'source_customer_id'     => $validated['source_customer_id'] ?? null,
    //             'source_package_id'      => $validated['source_package_id'] ?? null,
    //             'source_user_package_id' => $validated['source_user_package_id'] ?? null,
    //             'source_payment_id'      => $validated['source_payment_id'] ?? null,

    //             'customer_name'          => $validated['customer_name'] ?? null,
    //             'customer_email'         => $validated['customer_email'] ?? null,
    //             'customer_phone'         => $validated['customer_phone'] ?? null,
    //             'customer_phone1'        => $validated['customer_phone1'] ?? null,
    //             'country'                => $validated['country'] ?? null,
    //             'state'                  => $validated['state'] ?? null,
    //             'city'                   => $validated['city'] ?? null,
    //             'pin'                    => $validated['pin'] ?? null,
    //             'address'                => $validated['address'] ?? null,
    //             'customer_gst_number'    => $gstNumber,

    //             'package_id'             => $validated['package_id'] ?? null,
    //             'package_name'           => $validated['package_name'] ?? null,
    //             'package_description'    => $validated['package_description'] ?? null,
    //             'package_price'          => $validated['package_price'] ?? null,
    //             'package_duration'       => $validated['package_duration'] ?? null,

    //             'start_date'             => $validated['start_date'] ?? null,
    //             'expiry_date'            => $validated['expiry_date'] ?? null,
    //             'status'                 => $validated['status'] ?? null,

    //             'payment_amount'         => $validated['payment_amount'] ?? null,
    //             'payment_method'         => $validated['payment_method'] ?? null,
    //             'transaction_id'         => $validated['transaction_id'] ?? null,
    //             'bank'                   => $validated['bank'] ?? null,
    //             'payment_date'           => $validated['payment_date'] ?? null,
    //             'activated_by'           => $validated['activated_by'] ?? null,
    //             'customer_type'          => $validated['customer_type'] ?? null,
    //             'old_customer_user_id'   => $validated['old_customer_user_id'] ?? null,

    //             'created_by'             => $validated['created_by'] ?? null,
    //             'created_by_name'        => $validated['created_by_name'] ?? null,
    //             'request_date'           => $validated['request_date'] ?? null,
    //         ];

    //         $billRequest = null;
    //         $invoice = null;

    //         DB::transaction(function () use (&$billRequest, &$invoice, $validated, $request, $gstNumber, $fullPayload) {
    //             $billRequest = BillRequest::create([
    //                 'source_software'        => $validated['source_software'] ?? 'postimage',
    //                 'source_request_id'      => $request->header('X-Request-Id'),

    //                 'source_customer_id'     => $validated['source_customer_id'] ?? null,
    //                 'source_package_id'      => $validated['source_package_id'] ?? null,
    //                 'source_user_package_id' => $validated['source_user_package_id'] ?? null,
    //                 'source_payment_id'      => $validated['source_payment_id'] ?? null,

    //                 'customer_name'          => $validated['customer_name'] ?? null,
    //                 'customer_email'         => $validated['customer_email'] ?? null,
    //                 'customer_phone'         => $validated['customer_phone'] ?? null,
    //                 'customer_phone1'        => $validated['customer_phone1'] ?? null,
    //                 'country'                => $validated['country'] ?? null,
    //                 'state'                  => $validated['state'] ?? null,
    //                 'city'                   => $validated['city'] ?? null,
    //                 'pin'                    => $validated['pin'] ?? null,
    //                 'address'                => $validated['address'] ?? null,
    //                 'gst_number'             => $gstNumber,

    //                 'package_name'           => $validated['package_name'] ?? null,
    //                 'package_price'          => $validated['package_price'] ?? null,
    //                 'package_duration'       => $validated['package_duration'] ?? null,
    //                 'selling_price'          => $validated['package_price'] ?? null,

    //                 'payment_amount'         => $validated['payment_amount'] ?? null,
    //                 'payment_method'         => $validated['payment_method'] ?? null,
    //                 'transaction_id'         => $validated['transaction_id'] ?? null,
    //                 'bank'                   => $validated['bank'] ?? null,
    //                 'payment_date'           => $validated['payment_date'] ?? null,

    //                 'activated_by'           => $validated['activated_by'] ?? null,
    //                 'customer_type'          => $validated['customer_type'] ?? null,
    //                 'old_customer_user_id'   => $validated['old_customer_user_id'] ?? null,

    //                 'status'                 => 'pending',
    //                 'remarks'                => null,
    //                 'full_payload'           => json_encode($fullPayload, JSON_UNESCAPED_UNICODE),
    //                 'api_response'           => json_encode([
    //                     'received_from' => $validated['source_software'] ?? 'postimage',
    //                     'received_at'   => now()->toDateTimeString(),
    //                 ], JSON_UNESCAPED_UNICODE),
    //                 'requested_at'           => $validated['request_date'] ?? now(),
    //             ]);

    //             $invoice = $this->createQuotationInvoiceFromBillRequest(
    //                 $billRequest,
    //                 $validated['business_id'] ?? null
    //             );
    //         });

    //         $this->pushSaveInvoiceApi($billRequest->fresh(), $invoice->fresh());

    //         Log::info('Billing request and quotation created successfully', [
    //             'billing_request_id' => $billRequest->id,
    //             'invoice_id'         => $invoice->id,
    //             'invoice_number'     => $invoice->invoice_number,
    //             'customer_name'      => $billRequest->customer_name,
    //             'source_customer_id' => $billRequest->source_customer_id,
    //         ]);

    //         return response()->json([
    //             'success'            => true,
    //             'message'            => 'Billing request received and quotation created successfully.',
    //             'billing_request_id' => $billRequest->id,
    //             'invoice_id'         => $invoice->id,
    //             'invoice_number'     => $invoice->invoice_number,
    //             'invoice_type'       => $invoice->invoice_type,
    //             'status'             => $billRequest->fresh()->status,
    //             'data'               => [
    //                 'bill_request' => $billRequest->fresh(),
    //                 'invoice'      => $invoice->fresh(),
    //             ],
    //         ], 201);

    //     } catch (\Throwable $e) {
    //         Log::error('Billing request API failed', [
    //             'message' => $e->getMessage(),
    //             'line'    => $e->getLine(),
    //             'file'    => $e->getFile(),
    //             'payload' => $request->all(),
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Something went wrong while saving billing request and quotation.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // private function createQuotationInvoiceFromBillRequest(BillRequest $billRequest, $businessId = null)
    // {
    //     $bid = $businessId
    //         ?? optional(auth()->user())->current_business_id
    //         ?? session('active_business_id')
    //         ?? session('active_office_id');

    //     if (!$bid) {
    //         throw new \Exception('Active business select/attach nahi hai.');
    //     }

    //     if ($billRequest->status === 'processed') {
    //         throw new \Exception('Is bill request se invoice pehle hi create ho chuka hai.');
    //     }

    //     $business = Business::findOrFail($bid);

    //     $client = null;

    //     if (!empty($billRequest->gst_number)) {
    //         $client = Client::where('business_id', $bid)
    //             ->where('gstin', trim($billRequest->gst_number))
    //             ->first();
    //     }

    //     if (!$client && !empty($billRequest->customer_phone)) {
    //         $client = Client::where('business_id', $bid)
    //             ->where('mobile', trim($billRequest->customer_phone))
    //             ->first();
    //     }

    //     if (!$client && !empty($billRequest->customer_email)) {
    //         $client = Client::where('business_id', $bid)
    //             ->where('email', trim($billRequest->customer_email))
    //             ->first();
    //     }

    //     if (!$client) {
    //         $client = Client::create([
    //             'business_id' => $bid,
    //             'name'        => $billRequest->customer_name ?: 'Walk-in Customer',
    //             'address'     => $billRequest->address,
    //             'gstin'       => $billRequest->gst_number,
    //             'mobile'      => $billRequest->customer_phone ?: $billRequest->customer_phone1,
    //             'state'       => $billRequest->state,
    //             'city'        => $billRequest->city,
    //             'pincode'     => $billRequest->pin,
    //             'state_code'  => null,
    //             'email'       => $billRequest->customer_email,
    //             'is_save'     => 1,
    //         ]);
    //     }

    //     $itemId = trim((string) ($billRequest->package_name ?? ''));

    //     $matchedItem = Item::where('business_id', $bid)
    //         ->where('is_active', 1)
    //         ->where('type', 'service')
    //         ->where('id', (int) $itemId)
    //         ->first();

    //     if (!$matchedItem) {
    //         $matchedItem = Item::where('business_id', $bid)
    //             ->where('is_active', 1)
    //             ->where('type', 'service')
    //             ->where(function ($q) {
    //                 $q->where('name', 'like', '%Yearly Social Media Creative%')
    //                 ->where('name', 'like', '%Basic Package%');
    //             })
    //             ->first();
    //     }

    //     if (!$matchedItem) {
    //         throw new \Exception('Selected service item nahi mila. Item inactive ho sakta hai ya business/type mismatch hai.');
    //     }

    //     $grossAmount = (float) (
    //         $billRequest->selling_price
    //         ?? $billRequest->payment_amount
    //         ?? $billRequest->package_price
    //         ?? $matchedItem->price
    //         ?? 0
    //     );

    //     if ($grossAmount <= 0) {
    //         throw new \Exception('Bill request amount invalid hai.');
    //     }

    //     $invoiceDate = now(config('app.timezone'))->toDateString();

    //     $taxPercent = (float) ($matchedItem->tax_rate ?? 0);

    //     if ($taxPercent < 0) {
    //         $taxPercent = 0;
    //     }

    //     if ($taxPercent > 0) {
    //         $subtotal  = round($grossAmount * 100 / (100 + $taxPercent), 2);
    //         $taxAmount = round($grossAmount - $subtotal, 2);
    //     } else {
    //         $subtotal  = round($grossAmount, 2);
    //         $taxAmount = 0;
    //     }

    //     $grandTotal = round($grossAmount, 2);

    //     $bizCode = $this->normCode($business->state_code ?? '');
    //     $partyCode = $this->normCode($client->state_code ?? '');

    //     $isIntra = ($bizCode !== '' && $partyCode !== '')
    //         ? ($bizCode === $partyCode)
    //         : false;

    //     if ($isIntra) {
    //         $cgstPercent = round($taxPercent / 2, 2);
    //         $sgstPercent = round($taxPercent / 2, 2);
    //         $igstPercent = 0;

    //         $cgstAmount = round($taxAmount / 2, 2);
    //         $sgstAmount = round($taxAmount - $cgstAmount, 2);
    //         $igstAmount = 0;
    //     } else {
    //         $cgstPercent = 0;
    //         $sgstPercent = 0;
    //         $igstPercent = round($taxPercent, 2);

    //         $cgstAmount = 0;
    //         $sgstAmount = 0;
    //         $igstAmount = round($taxAmount, 2);
    //     }

    //     $taxBase = optional(
    //         optional(auth()->user())->businesses()->where('businesses.id', $bid)->first()
    //     )->quotation_base_prefix ?? 'QUO';

    //     $prefix = \App\Services\InvoiceNumber::previewPrefix($invoiceDate, $taxBase);

    //     $alloc = \App\Services\InvoiceNumber::next(
    //         (int) $bid,
    //         $invoiceDate,
    //         $prefix,
    //         3,
    //         'quotation'
    //     );

    //     $invoiceNumber = $alloc['full'];

    //     \App\Services\InvoiceNumber::syncNextSeqIfMatches(
    //         (int) $bid,
    //         $invoiceDate,
    //         $invoiceNumber,
    //         3,
    //         'quotation'
    //     );

    //     $itemDescription = $matchedItem->description ?: $matchedItem->name;

    //     $itemsJson = [
    //         [
    //             'item_id'       => $matchedItem->id,
    //             'item_type'     => 'service',
    //             'description'   => $itemDescription,
    //             'hsn'           => $matchedItem->sac ?? '',
    //             'qty'           => 1,
    //             'tax_percent'   => round($taxPercent, 2),

    //             'service_rate'  => round($subtotal, 2),
    //             'rate'          => round($subtotal, 2),
    //             'tax_amount'    => round($taxAmount, 2),
    //             'amount'        => round($grandTotal, 2),
    //             'making_charge' => round($subtotal, 2),

    //             'gold_wt'       => 0,
    //             'silver_wt'     => 0,
    //             'gold_rate'     => 0,
    //             'silver_rate'   => 0,
    //             'gemstone_wt'   => 0,
    //             'diamond_wt'    => 0,
    //             'making_rate'   => 0,
    //             'stone_charges' => 0,
    //         ],
    //     ];

    //     $userId = auth()->id() ?? $billRequest->old_customer_user_id ?? null;

    //     $invoice = Invoice::create([
    //         'business_id'     => $bid,
    //         'invoice_type'    => 'quotation',
    //         'invoice_prefix'  => $prefix,
    //         'invoice_number'  => $invoiceNumber,
    //         'client_id'       => $client->id,
    //         'invoice_date'    => $invoiceDate,
    //         'payment_terms'   => 0,
    //         'due_date'        => null,

    //         'subtotal'        => round($subtotal, 2),
    //         'tax_amount'      => round($taxAmount, 2),

    //         'cgst_percent'    => $cgstPercent,
    //         'cgst_amount'     => $cgstAmount,
    //         'sgst_percent'    => $sgstPercent,
    //         'sgst_amount'     => $sgstAmount,
    //         'igst_percent'    => $igstPercent,
    //         'igst_amount'     => $igstAmount,

    //         'discount_total'  => 0,
    //         'charge_total'    => 0,
    //         'tcs_percent'     => 0,
    //         'tcs_amount'      => 0,
    //         'round_off'       => 0,
    //         'less_amount'     => 0,

    //         'total'           => $grandTotal,
    //         'received_amount' => 0,
    //         'balance'         => $grandTotal,

    //         'payment_method'  => null,
    //         'transport_mode'  => null,
    //         'reverse_charge'  => 0,

    //         'place_of_supply_state' => $client->state,
    //         'place_of_supply_code'  => $client->state_code,

    //         'notes'           => 'Created from Bill Request ID: ' . ($billRequest->source_request_id ?: $billRequest->id),
    //         'terms'           => null,

    //         'charges_json'    => json_encode([]),
    //         'items_json'      => json_encode($itemsJson),

    //         'amount_in_words' => '',
    //         'pdf_url'         => null,
    //         'signature'       => null,
    //         'user_id'         => $userId,
    //         'created_by'      => $userId,
    //         'updated_by'      => $userId,
    //         'kots_json'       => json_encode([]),
    //     ]);

    //     InvoiceItem::create([
    //         'invoice_id'      => $invoice->id,
    //         'item_id'         => $matchedItem->id,
    //         'description'     => $itemDescription,
    //         'sac_code'        => $matchedItem->sac ?? null,
    //         'hsn_code'        => null,
    //         'quantity'        => 1,

    //         'gold_wt'         => 0,
    //         'silver_wt'       => 0,
    //         'gold_rate'       => 0,
    //         'silver_rate'     => 0,
    //         'gemstone_wt_ct'  => 0,
    //         'diamond_wt_ct'   => 0,

    //         'making_charge'   => round($subtotal, 2),
    //         'making_rate'     => null,
    //         'discount'        => 0,
    //         'tax_percent'     => round($taxPercent, 2),
    //         'rate'            => round($subtotal, 2),
    //         'amount'          => round($grandTotal, 2),
    //     ]);

    //     $oldApi = [];

    //     if (!empty($billRequest->api_response)) {
    //         $decoded = json_decode($billRequest->api_response, true);

    //         if (is_array($decoded)) {
    //             $oldApi = $decoded;
    //         }
    //     }

    //     $oldApi['matched_item_id']        = $matchedItem->id;
    //     $oldApi['matched_item_name']      = $matchedItem->name;
    //     $oldApi['created_invoice_id']     = $invoice->id;
    //     $oldApi['created_invoice_number'] = $invoice->invoice_number;
    //     $oldApi['created_client_id']      = $client->id;
    //     $oldApi['invoice_type']           = 'quotation';
    //     $oldApi['invoice_date']           = $invoiceDate;
    //     $oldApi['gst_included']           = true;
    //     $oldApi['gross_amount']           = $grandTotal;
    //     $oldApi['taxable_amount']         = $subtotal;
    //     $oldApi['tax_amount']             = $taxAmount;
    //     $oldApi['processed_at']           = now(config('app.timezone'))->toDateTimeString();

    //     $billRequest->update([
    //         'status'       => 'processed',
    //         'remarks'      => 'Quotation created successfully. Quotation No: ' . $invoice->invoice_number,
    //         'api_response' => json_encode($oldApi, JSON_UNESCAPED_UNICODE),
    //     ]);

    //     return $invoice;
    // }





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

            'start_date'             => $request->input('start_date'),
            'expiry_date'            => $request->input('expiry_date'),
            'status'                 => $request->input('status'),

            'payment_amount'         => $request->input('payment_amount'),
            'payment_method'         => $request->input('payment_method'),
            'transaction_id'         => $request->input('transaction_id'),
            'bank'                   => $request->input('bank'),
            'payment_date'           => $request->input('payment_date'),
            'activated_by'           => $request->input('activated_by'),
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

        $gstNumber = !empty($validated['customer_gst_number'])
            ? strtoupper(trim($validated['customer_gst_number']))
            : null;

        $fullPayload = [
            'request_for_bill'       => $validated['request_for_bill'],
            'source_software'        => $validated['source_software'] ?? null,
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
            'package_price'          => $validated['package_price'] ?? null,
            'package_duration'       => $validated['package_duration'] ?? null,

            'payment_amount'         => $validated['payment_amount'] ?? null,
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

        DB::transaction(function () use (&$billRequest, &$invoice, $validated, $request, $gstNumber, $fullPayload) {
            $billRequest = BillRequest::create([
                'source_software'        => $validated['source_software'] ?? 'postimage',
                'source_request_id'      => $request->header('X-Request-Id'),

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
                'package_price'          => $validated['package_price'] ?? null,
                'package_duration'       => $validated['package_duration'] ?? null,
                'selling_price'          => $validated['package_price'] ?? null,

                'payment_amount'         => $validated['payment_amount'] ?? null,
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
                    'received_from' => $validated['source_software'] ?? 'postimage',
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
        throw new \Exception('Selected service item nahi mila. Item inactive ho sakta hai ya business/type mismatch hai.');
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

    $invoiceDate = now(config('app.timezone'))->toDateString();

    $taxPercent = max(0, (float) ($matchedItem->tax_rate ?? 0));

    if ($taxPercent > 0) {
        $subtotal  = round($grossAmount * 100 / (100 + $taxPercent), 2);
        $taxAmount = round($grossAmount - $subtotal, 2);
    } else {
        $subtotal  = round($grossAmount, 2);
        $taxAmount = 0;
    }

    $grandTotal = round($grossAmount, 2);

    $bizCode   = $this->normCode($business->state_code ?? '');
    $partyCode = $this->normCode($client->state_code ?? '');

    $isIntra = ($bizCode !== '' && $partyCode !== '')
        ? ($bizCode === $partyCode)
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

    $itemDescription = $matchedItem->description ?: $matchedItem->name;

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
        ],
    ];

    $invoice = Invoice::create([
        'business_id'     => $bid,
        'bill_request_id' => $billRequest->id,

        'invoice_type'    => 'quotation',
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
        'items_json'      => json_encode($itemsJson),

        'amount_in_words' => '',
        'pdf_url'         => null,
        'signature'       => null,

        'user_id'         => $userId,
        'created_by'      => $userId,
        'updated_by'      => $userId,

        'kots_json'       => json_encode([]),
    ]);

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

    $oldApi = [];

    if (!empty($billRequest->api_response)) {
        $decoded = json_decode($billRequest->api_response, true);

        if (is_array($decoded)) {
            $oldApi = $decoded;
        }
    }

    $oldApi['matched_item_id']        = $matchedItem->id;
    $oldApi['matched_item_name']      = $matchedItem->name;
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
        'status'       => 'processed',
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


    private function normCode($code): string
    {
        $code = trim((string) $code);

        if ($code === '') {
            return '';
        }

        $code = preg_replace('/\D/', '', $code);

        return str_pad($code, 2, '0', STR_PAD_LEFT);
    }




}
