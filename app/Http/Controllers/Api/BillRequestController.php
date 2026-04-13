<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\BillRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BillRequestController extends Controller
{
public function store(Request $request)
{
    // =========================
    // OPTIONAL TOKEN CHECK
    // =========================
    // $incomingToken = $request->bearerToken();
    // $expectedToken = config('services.postimage.billing_api_token');
    //
    // if (!$incomingToken || !$expectedToken || !hash_equals($expectedToken, $incomingToken)) {
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Unauthorized request.',
    //     ], 401);
    // }

    try {
        /*
        |--------------------------------------------------------------------------
        | Parse request safely
        |--------------------------------------------------------------------------
        */
        $data = $request->all();

        if (empty($data)) {
            $json = json_decode($request->getContent(), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $data = $json;
                $request->merge($data);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize boolean
        |--------------------------------------------------------------------------
        */
        $requestForBill = $request->input('request_for_bill');

        if (in_array($requestForBill, ['1', 1, true, 'true', 'yes', 'on'], true)) {
            $requestForBill = true;
        } elseif (in_array($requestForBill, ['0', 0, false, 'false', 'no', 'off'], true)) {
            $requestForBill = false;
        } else {
            $requestForBill = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */
        $validator = \Validator::make([
            'request_for_bill'              => $requestForBill,

            'customer_id'                   => $request->input('customer_id'),
            'customer_name'                 => $request->input('customer_name'),
            'customer_email'                => $request->input('customer_email'),
            'customer_phone'                => $request->input('customer_phone'),
            'customer_phone1'               => $request->input('customer_phone1'),
            'customer_business_name'        => $request->input('customer_business_name'),
            'customer_country'              => $request->input('customer_country'),
            'customer_state'                => $request->input('customer_state'),
            'customer_city'                 => $request->input('customer_city'),
            'customer_pin'                  => $request->input('customer_pin'),
            'customer_address'              => $request->input('customer_address'),
            'customer_gst_number'           => $request->input('customer_gst_number'),

            'package_id'                    => $request->input('package_id'),
            'package_name'                  => $request->input('package_name'),
            'package_description'           => $request->input('package_description'),
            'package_price'                 => $request->input('package_price'),
            'package_duration'              => $request->input('package_duration'),

            'user_package_id'               => $request->input('user_package_id'),
            'user_package_user_id'          => $request->input('user_package_user_id'),
            'user_package_package_id'       => $request->input('user_package_package_id'),
            'user_package_selling_price'    => $request->input('user_package_selling_price'),
            'user_package_start_date'       => $request->input('user_package_start_date'),
            'user_package_expiry_date'      => $request->input('user_package_expiry_date'),
            'user_package_status'           => $request->input('user_package_status'),

            'payment_id'                    => $request->input('payment_id'),
            'payment_amount'                => $request->input('payment_amount'),
            'payment_method'                => $request->input('payment_method'),
            'payment_transaction_id'        => $request->input('payment_transaction_id'),
            'payment_bank'                  => $request->input('payment_bank'),
            'payment_date'                  => $request->input('payment_date'),
            'payment_activated_by'          => $request->input('payment_activated_by'),
            'payment_customer_type'         => $request->input('payment_customer_type'),
            'payment_old_customer_user_id'  => $request->input('payment_old_customer_user_id'),

            'meta_created_by'               => $request->input('meta_created_by'),
            'meta_created_by_name'          => $request->input('meta_created_by_name'),
            'meta_request_date'             => $request->input('meta_request_date'),
            'meta_source_software'          => $request->input('meta_source_software'),
        ], [
            'request_for_bill'              => ['required', 'boolean'],

            'customer_id'                   => ['nullable', 'integer'],
            'customer_name'                 => ['required', 'string', 'max:255'],
            'customer_email'                => ['nullable', 'email', 'max:255'],
            'customer_phone'                => ['nullable', 'string', 'max:255'],
            'customer_phone1'               => ['nullable', 'string', 'max:255'],
            'customer_business_name'        => ['nullable', 'string', 'max:255'],
            'customer_country'              => ['nullable', 'string', 'max:255'],
            'customer_state'                => ['nullable', 'string', 'max:255'],
            'customer_city'                 => ['nullable', 'string', 'max:255'],
            'customer_pin'                  => ['nullable', 'string', 'max:255'],
            'customer_address'              => ['nullable', 'string'],
            'customer_gst_number'           => ['required', 'string', 'size:15'],

            'package_id'                    => ['nullable', 'integer'],
            'package_name'                  => ['nullable', 'string', 'max:255'],
            'package_description'           => ['nullable', 'string'],
            'package_price'                 => ['nullable', 'numeric'],
            'package_duration'              => ['nullable', 'integer'],

            'user_package_id'               => ['nullable', 'integer'],
            'user_package_user_id'          => ['nullable', 'integer'],
            'user_package_package_id'       => ['nullable', 'integer'],
            'user_package_selling_price'    => ['nullable', 'numeric'],
            'user_package_start_date'       => ['nullable', 'date'],
            'user_package_expiry_date'      => ['nullable', 'date'],
            'user_package_status'           => ['nullable', 'string', 'max:255'],

            'payment_id'                    => ['nullable', 'integer'],
            'payment_amount'                => ['nullable', 'numeric'],
            'payment_method'                => ['nullable', 'string', 'max:255'],
            'payment_transaction_id'        => ['nullable', 'string', 'max:255'],
            'payment_bank'                  => ['nullable', 'string', 'max:255'],
            'payment_date'                  => ['nullable', 'date'],
            'payment_activated_by'          => ['nullable', 'string', 'max:255'],
            'payment_customer_type'         => ['nullable', 'string', 'max:255'],
            'payment_old_customer_user_id'  => ['nullable', 'integer'],

            'meta_created_by'               => ['nullable', 'integer'],
            'meta_created_by_name'          => ['nullable', 'string', 'max:255'],
            'meta_request_date'             => ['nullable', 'date'],
            'meta_source_software'          => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
                'received_payload' => $request->all(),
            ], 422);
        }

        $validated = $validator->validated();

        /*
        |--------------------------------------------------------------------------
        | Build payload for storage/log
        |--------------------------------------------------------------------------
        */
        $fullPayload = [
            'request_for_bill' => $validated['request_for_bill'],

            'customer_id' => $validated['customer_id'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'customer_phone1' => $validated['customer_phone1'] ?? null,
            'customer_business_name' => $validated['customer_business_name'] ?? null,
            'customer_country' => $validated['customer_country'] ?? null,
            'customer_state' => $validated['customer_state'] ?? null,
            'customer_city' => $validated['customer_city'] ?? null,
            'customer_pin' => $validated['customer_pin'] ?? null,
            'customer_address' => $validated['customer_address'] ?? null,
            'customer_gst_number' => strtoupper(trim($validated['customer_gst_number'] ?? '')),

            'package_id' => $validated['package_id'] ?? null,
            'package_name' => $validated['package_name'] ?? null,
            'package_description' => $validated['package_description'] ?? null,
            'package_price' => $validated['package_price'] ?? null,
            'package_duration' => $validated['package_duration'] ?? null,

            'user_package_id' => $validated['user_package_id'] ?? null,
            'user_package_user_id' => $validated['user_package_user_id'] ?? null,
            'user_package_package_id' => $validated['user_package_package_id'] ?? null,
            'user_package_selling_price' => $validated['user_package_selling_price'] ?? null,
            'user_package_start_date' => $validated['user_package_start_date'] ?? null,
            'user_package_expiry_date' => $validated['user_package_expiry_date'] ?? null,
            'user_package_status' => $validated['user_package_status'] ?? null,

            'payment_id' => $validated['payment_id'] ?? null,
            'payment_amount' => $validated['payment_amount'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
            'payment_transaction_id' => $validated['payment_transaction_id'] ?? null,
            'payment_bank' => $validated['payment_bank'] ?? null,
            'payment_date' => $validated['payment_date'] ?? null,
            'payment_activated_by' => $validated['payment_activated_by'] ?? null,
            'payment_customer_type' => $validated['payment_customer_type'] ?? null,
            'payment_old_customer_user_id' => $validated['payment_old_customer_user_id'] ?? null,

            'meta_created_by' => $validated['meta_created_by'] ?? null,
            'meta_created_by_name' => $validated['meta_created_by_name'] ?? null,
            'meta_request_date' => $validated['meta_request_date'] ?? null,
            'meta_source_software' => $validated['meta_source_software'] ?? 'postimage',
        ];

        /*
        |--------------------------------------------------------------------------
        | Insert into bill_requests table
        |--------------------------------------------------------------------------
        */
        $billRequest = \App\Models\BillRequest::create([
            'source_software'        => $validated['meta_source_software'] ?? 'postimage',
            'source_request_id'      => $request->header('X-Request-Id'),

            'source_customer_id'     => $validated['customer_id'] ?? null,
            'source_package_id'      => $validated['package_id'] ?? null,
            'source_user_package_id' => $validated['source_user_package_id'] ?? null,
            'source_payment_id'      => $validated['payment_id'] ?? null,

            'customer_name'          => $validated['customer_name'] ?? null,
            'customer_email'         => $validated['customer_email'] ?? null,
            'customer_phone'         => $validated['customer_phone'] ?? null,
            'customer_phone1'        => $validated['customer_phone1'] ?? null,
            'business_name'          => $validated['customer_business_name'] ?? null,
            'country'                => $validated['customer_country'] ?? null,
            'state'                  => $validated['customer_state'] ?? null,
            'city'                   => $validated['customer_city'] ?? null,
            'pin'                    => $validated['customer_pin'] ?? null,
            'address'                => $validated['customer_address'] ?? null,
            'gst_number'             => strtoupper(trim($validated['customer_gst_number'] ?? '')),

            'package_name'           => $validated['package_name'] ?? null,
            'package_price'          => $validated['package_price'] ?? null,
            'package_duration'       => $validated['package_duration'] ?? null,
            'selling_price'          => $validated['user_package_selling_price'] ?? null,

            'payment_amount'         => $validated['payment_amount'] ?? null,
            'payment_method'         => $validated['payment_method'] ?? null,
            'transaction_id'         => $validated['payment_transaction_id'] ?? null,
            'bank'                   => $validated['payment_bank'] ?? null,
            'payment_date'           => $validated['payment_date'] ?? null,

            'activated_by'           => $validated['payment_activated_by'] ?? null,
            'customer_type'          => $validated['payment_customer_type'] ?? null,
            'old_customer_user_id'   => $validated['payment_old_customer_user_id'] ?? null,

            'status'                 => 'pending',
            'remarks'                => null,
            'full_payload'           => json_encode($fullPayload, JSON_UNESCAPED_UNICODE),
            'api_response'           => json_encode([
                'received_from' => $validated['meta_source_software'] ?? 'postimage',
                'received_at'   => now()->toDateTimeString(),
            ], JSON_UNESCAPED_UNICODE),
            'requested_at'           => $validated['meta_request_date'] ?? now(),
        ]);

        \Log::info('Billing request received successfully', [
            'billing_request_id' => $billRequest->id,
            'customer_name'      => $billRequest->customer_name,
            'source_customer_id' => $billRequest->source_customer_id,
        ]);

        return response()->json([
            'success'            => true,
            'message'            => 'Billing request received successfully.',
            'billing_request_id' => $billRequest->id,
            'status'             => $billRequest->status,
            'data'               => $billRequest,
        ], 201);

    } catch (\Throwable $e) {
        \Log::error('Billing request API failed', [
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
            'payload' => $request->all(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong while saving billing request.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}
}
