<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\BillRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BillRequestController extends Controller
{
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\BillRequest;

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

    /*
    |--------------------------------------------------------------------------
    | Normalize request
    |--------------------------------------------------------------------------
    | Ye code raw JSON, nested form-data, aur flat form-data tino ko support karega.
    */
    $customerInput = $request->input('customer', []);
    $packageInput = $request->input('package', []);
    $userPackageInput = $request->input('user_package', []);
    $paymentInput = $request->input('payment', []);
    $metaInput = $request->input('meta', []);

    // Agar nested array nahi aayi to flat keys se build kar do
    if (!is_array($customerInput) || empty($customerInput)) {
        $customerInput = [
            'id'            => $request->input('customer_id'),
            'name'          => $request->input('customer_name'),
            'email'         => $request->input('customer_email'),
            'phone'         => $request->input('customer_phone'),
            'phone1'        => $request->input('customer_phone1'),
            'business_name' => $request->input('customer_business_name'),
            'country'       => $request->input('customer_country'),
            'state'         => $request->input('customer_state'),
            'city'          => $request->input('customer_city'),
            'pin'           => $request->input('customer_pin'),
            'address'       => $request->input('customer_address'),
            'gst_number'    => $request->input('customer_gst_number'),
        ];
    }

    if (!is_array($packageInput) || empty($packageInput)) {
        $packageInput = [
            'id'          => $request->input('package_id'),
            'name'        => $request->input('package_name'),
            'description' => $request->input('package_description'),
            'price'       => $request->input('package_price'),
            'duration'    => $request->input('package_duration'),
        ];
    }

    if (!is_array($userPackageInput) || empty($userPackageInput)) {
        $userPackageInput = [
            'id'            => $request->input('user_package_id'),
            'user_id'       => $request->input('user_package_user_id'),
            'package_id'    => $request->input('user_package_package_id'),
            'selling_price' => $request->input('user_package_selling_price'),
            'start_date'    => $request->input('user_package_start_date'),
            'expiry_date'   => $request->input('user_package_expiry_date'),
            'status'        => $request->input('user_package_status'),
        ];
    }

    if (!is_array($paymentInput) || empty($paymentInput)) {
        $paymentInput = [
            'id'                   => $request->input('payment_id'),
            'amount'               => $request->input('payment_amount'),
            'payment_method'       => $request->input('payment_method'),
            'transaction_id'       => $request->input('payment_transaction_id'),
            'bank'                 => $request->input('payment_bank'),
            'payment_date'         => $request->input('payment_date'),
            'activated_by'         => $request->input('payment_activated_by'),
            'customer_type'        => $request->input('payment_customer_type'),
            'old_customer_user_id' => $request->input('payment_old_customer_user_id'),
        ];
    }

    if (!is_array($metaInput) || empty($metaInput)) {
        $metaInput = [
            'created_by'      => $request->input('meta_created_by'),
            'created_by_name' => $request->input('meta_created_by_name'),
            'request_date'    => $request->input('meta_request_date'),
            'source_software' => $request->input('meta_source_software'),
        ];
    }

    // Main payload normalize kar do
    $normalized = [
        'request_for_bill' => $request->input('request_for_bill'),
        'customer'         => $customerInput,
        'package'          => $packageInput,
        'user_package'     => $userPackageInput,
        'payment'          => $paymentInput,
        'meta'             => $metaInput,
    ];

    // request_for_bill ko boolean-friendly bana do
    if (in_array($normalized['request_for_bill'], ['1', 1, true, 'true', 'yes', 'on'], true)) {
        $normalized['request_for_bill'] = true;
    } elseif (in_array($normalized['request_for_bill'], ['0', 0, false, 'false', 'no', 'off'], true)) {
        $normalized['request_for_bill'] = false;
    }

    $validator = Validator::make($normalized, [
        'request_for_bill'             => ['required', 'boolean'],

        'customer.id'                  => ['nullable', 'integer'],
        'customer.name'                => ['required', 'string', 'max:255'],
        'customer.email'               => ['nullable', 'email', 'max:255'],
        'customer.phone'               => ['nullable', 'string', 'max:50'],
        'customer.phone1'              => ['nullable', 'string', 'max:50'],
        'customer.business_name'       => ['nullable', 'string', 'max:255'],
        'customer.country'             => ['nullable', 'string', 'max:255'],
        'customer.state'               => ['nullable', 'string', 'max:255'],
        'customer.city'                => ['nullable', 'string', 'max:255'],
        'customer.pin'                 => ['nullable', 'string', 'max:50'],
        'customer.address'             => ['nullable', 'string'],
        'customer.gst_number'          => ['required', 'string', 'size:15'],

        'package.id'                   => ['nullable', 'integer'],
        'package.name'                 => ['nullable', 'string', 'max:255'],
        'package.description'          => ['nullable', 'string'],
        'package.price'                => ['nullable', 'numeric'],
        'package.duration'             => ['nullable', 'integer'],

        'user_package.id'              => ['nullable', 'integer'],
        'user_package.user_id'         => ['nullable', 'integer'],
        'user_package.package_id'      => ['nullable', 'integer'],
        'user_package.selling_price'   => ['nullable', 'numeric'],
        'user_package.start_date'      => ['nullable', 'date'],
        'user_package.expiry_date'     => ['nullable', 'date'],
        'user_package.status'          => ['nullable', 'string', 'max:50'],

        'payment.id'                   => ['nullable', 'integer'],
        'payment.amount'               => ['nullable', 'numeric'],
        'payment.payment_method'       => ['nullable', 'string', 'max:255'],
        'payment.transaction_id'       => ['nullable', 'string', 'max:255'],
        'payment.bank'                 => ['nullable', 'string', 'max:255'],
        'payment.payment_date'         => ['nullable', 'date'],
        'payment.activated_by'         => ['nullable', 'string', 'max:255'],
        'payment.customer_type'        => ['nullable', 'string', 'max:50'],
        'payment.old_customer_user_id' => ['nullable', 'integer'],

        'meta.created_by'              => ['nullable', 'integer'],
        'meta.created_by_name'         => ['nullable', 'string', 'max:255'],
        'meta.request_date'            => ['nullable', 'date'],
        'meta.source_software'         => ['nullable', 'string', 'max:100'],
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
            'received_payload' => $normalized, // debug ke liye useful
        ], 422);
    }

    try {
        $customer = $normalized['customer'];
        $package = $normalized['package'];
        $userPackage = $normalized['user_package'];
        $payment = $normalized['payment'];
        $meta = $normalized['meta'];

        $billRequest = BillRequest::create([
            'source_software'        => $meta['source_software'] ?? 'postimage',
            'source_request_id'      => $request->header('X-Request-Id'),

            'source_customer_id'     => $customer['id'] ?? null,
            'source_package_id'      => $package['id'] ?? null,
            'source_user_package_id' => $userPackage['id'] ?? null,
            'source_payment_id'      => $payment['id'] ?? null,

            'customer_name'          => $customer['name'] ?? null,
            'customer_email'         => $customer['email'] ?? null,
            'customer_phone'         => $customer['phone'] ?? null,
            'customer_phone1'        => $customer['phone1'] ?? null,
            'business_name'          => $customer['business_name'] ?? null,
            'country'                => $customer['country'] ?? null,
            'state'                  => $customer['state'] ?? null,
            'city'                   => $customer['city'] ?? null,
            'pin'                    => $customer['pin'] ?? null,
            'address'                => $customer['address'] ?? null,
            'gst_number'             => strtoupper(trim($customer['gst_number'] ?? '')),

            'package_name'           => $package['name'] ?? null,
            'package_price'          => $package['price'] ?? null,
            'package_duration'       => $package['duration'] ?? null,
            'selling_price'          => $userPackage['selling_price'] ?? null,

            'payment_amount'         => $payment['amount'] ?? null,
            'payment_method'         => $payment['payment_method'] ?? null,
            'transaction_id'         => $payment['transaction_id'] ?? null,
            'bank'                   => $payment['bank'] ?? null,
            'payment_date'           => $payment['payment_date'] ?? null,

            'activated_by'           => $payment['activated_by'] ?? null,
            'customer_type'          => $payment['customer_type'] ?? null,
            'old_customer_user_id'   => $payment['old_customer_user_id'] ?? null,

            'status'                 => 'pending',
            'requested_at'           => $meta['request_date'] ?? now(),
            'full_payload'           => $normalized,
            'api_response'           => [
                'received_from' => $meta['source_software'] ?? 'postimage',
                'received_at'   => now()->toDateTimeString(),
            ],
        ]);

        Log::info('Billing request received successfully', [
            'billing_request_id' => $billRequest->id,
            'customer_name'      => $billRequest->customer_name,
            'source_customer_id' => $billRequest->source_customer_id,
        ]);

        return response()->json([
            'success'            => true,
            'message'            => 'Billing request received successfully.',
            'billing_request_id' => $billRequest->id,
            'status'             => $billRequest->status,
        ], 201);

    } catch (\Throwable $e) {
        Log::error('Billing request API failed', [
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong while saving billing request.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}
}
