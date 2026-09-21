<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\PlanPayment;
use App\Models\PlanPaymentLink;
use App\Models\User;
use App\Models\UserPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class PlanPaymentLinkController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Generate Razorpay Payment Link
    |--------------------------------------------------------------------------
    */

public function generate(Request $request, Plan $plan)
{
    try {

        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'customer_name' => [
                'required',
                'string',
                'max:255',
            ],

            'customer_phone' => [
                'required',
                'string',
                'max:20',
            ],

            'customer_email' => [
                'nullable',
                'email',
                'max:255',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Razorpay Credentials
        |--------------------------------------------------------------------------
        */

        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');

        if (empty($key) || empty($secret)) {

            return response()->json([
                'status' => false,
                'message' => 'Razorpay key ya secret configure nahi hai. .env aur config/services.php check karo.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Phone
        |--------------------------------------------------------------------------
        */

        $phone = $this->normalizePhone(
            $validated['customer_phone']
        );


        /*
        |--------------------------------------------------------------------------
        | Find Existing User
        |--------------------------------------------------------------------------
        */

        $user = $this->findUserByPhone(
            $phone
        );


        /*
        |--------------------------------------------------------------------------
        | Amount
        |--------------------------------------------------------------------------
        */

        $baseAmount = round(
            (float) $plan->price,
            2
        );

        $gstRate = round(
            (float) ($plan->tax ?? 18),
            2
        );

        $gstAmount = round(
            ($baseAmount * $gstRate) / 100,
            2
        );

        $totalAmount = round(
            $baseAmount + $gstAmount,
            2
        );


        if ($baseAmount <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Plan amount valid nahi hai.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Existing Payment Link
        |--------------------------------------------------------------------------
        */

        $existingLink = PlanPaymentLink::query()
            ->where('plan_id', $plan->id)
            ->where('customer_phone', $phone)
            ->whereIn('status', [
                'created',
                'issued',
            ])
            ->whereNotNull('short_url')
            ->latest('id')
            ->first();


        if ($existingLink) {

            return response()->json([
                'status' => true,
                'message' => 'Is customer ke liye payment link pehle se available hai.',
                'payment_link' => $existingLink->short_url,
                'existing' => true,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Unique Reference
        |--------------------------------------------------------------------------
        */

        $referenceId =
            'PL' .
            $plan->id .
            now()->format('YmdHis') .
            Str::upper(
                Str::random(4)
            );


        /*
        |--------------------------------------------------------------------------
        | Local Record
        |--------------------------------------------------------------------------
        */

        $paymentLink = new PlanPaymentLink();

        $paymentLink->plan_id =
            $plan->id;

        $paymentLink->user_id =
            $user?->id;

        $paymentLink->customer_name =
            $validated['customer_name'];

        $paymentLink->customer_phone =
            $phone;

        $paymentLink->customer_email =
            $validated['customer_email'] ?? null;

        $paymentLink->base_amount =
            $baseAmount;

        $paymentLink->gst_rate =
            $gstRate;

        $paymentLink->gst_amount =
            $gstAmount;

        $paymentLink->total_amount =
            $totalAmount;

        $paymentLink->razorpay_reference_id =
            $referenceId;

        $paymentLink->status =
            'creating';

        $paymentLink->generated_by =
            Auth::id();

        $paymentLink->save();


        /*
        |--------------------------------------------------------------------------
        | Razorpay Payload
        |--------------------------------------------------------------------------
        */

        $payload = [

            'amount' => (int) round(
                $totalAmount * 100
            ),

            'currency' => 'INR',

            'accept_partial' => false,

            'reference_id' =>
                $referenceId,

            'description' =>
                'Payment for ' .
                $plan->name,

            'customer' => [

                'name' =>
                    $validated['customer_name'],

                'contact' =>
                    '+91' . $phone,
            ],

            'notify' => [
                'sms' => false,
                'email' => false,
            ],

            'reminder_enable' =>
                true,

            'notes' => [

                'type' =>
                    'plan_payment',

                'plan_id' =>
                    (string) $plan->id,

                'local_payment_link_id' =>
                    (string) $paymentLink->id,

                'customer_phone' =>
                    $phone,

                'user_id' =>
                    $user
                        ? (string) $user->id
                        : '',
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | Add Email If Available
        |--------------------------------------------------------------------------
        */

        if (
            !empty(
                $validated['customer_email']
            )
        ) {

            $payload['customer']['email'] =
                $validated['customer_email'];
        }


        /*
        |--------------------------------------------------------------------------
        | Razorpay Payment Link API
        |--------------------------------------------------------------------------
        */

        $response = Http::withBasicAuth(
            $key,
            $secret
        )
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post(
                'https://api.razorpay.com/v1/payment_links',
                $payload
            );


        /*
        |--------------------------------------------------------------------------
        | Razorpay Error
        |--------------------------------------------------------------------------
        */

        if ($response->failed()) {

            $responseData =
                $response->json();

            $errorMessage =
                data_get(
                    $responseData,
                    'error.description'
                )
                ??
                data_get(
                    $responseData,
                    'error.reason'
                )
                ??
                $response->body();


            $paymentLink->status =
                'failed';

            $paymentLink->gateway_response =
                $responseData
                ?: [
                    'body' => $response->body(),
                ];

            $paymentLink->save();


            Log::error(
                'Razorpay Payment Link Error',
                [
                    'status_code' =>
                        $response->status(),

                    'plan_id' =>
                        $plan->id,

                    'local_payment_link_id' =>
                        $paymentLink->id,

                    'payload' =>
                        $payload,

                    'response' =>
                        $response->body(),
                ]
            );


            return response()->json([
                'status' => false,

                'message' =>
                    'Razorpay Error: ' .
                    $errorMessage,

                'razorpay_status_code' =>
                    $response->status(),
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Razorpay Successful Response
        |--------------------------------------------------------------------------
        */

        $razorpayLink =
            $response->json();


        if (
            empty(
                $razorpayLink['id']
            )
            ||
            empty(
                $razorpayLink['short_url']
            )
        ) {

            $paymentLink->status =
                'failed';

            $paymentLink->gateway_response =
                $razorpayLink;

            $paymentLink->save();


            return response()->json([
                'status' => false,

                'message' =>
                    'Razorpay se payment link URL receive nahi hua.',

                'response' =>
                    $razorpayLink,
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Update Local Record
        |--------------------------------------------------------------------------
        */

        $paymentLink->razorpay_payment_link_id =
            $razorpayLink['id'];

        $paymentLink->short_url =
            $razorpayLink['short_url'];

        $paymentLink->status =
            $razorpayLink['status']
            ?? 'created';

        $paymentLink->gateway_response =
            $razorpayLink;

        $paymentLink->save();


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'status' =>
                true,

            'message' =>
                'Payment link successfully generate ho gaya.',

            'payment_link' =>
                $paymentLink->short_url,

            'payment_link_id' =>
                $paymentLink->id,

            'razorpay_payment_link_id' =>
                $paymentLink
                    ->razorpay_payment_link_id,

            'amount' =>
                $paymentLink->total_amount,
        ]);

    } catch (
        \Illuminate\Validation\ValidationException $e
    ) {

        return response()->json([
            'status' => false,

            'message' =>
                collect(
                    $e->errors()
                )
                    ->flatten()
                    ->first()
                ?? 'Validation error.',

            'errors' =>
                $e->errors(),
        ], 422);

    } catch (\Throwable $e) {

        Log::error(
            'Plan Payment Link Generate Exception',
            [
                'plan_id' =>
                    $plan->id,

                'message' =>
                    $e->getMessage(),

                'file' =>
                    $e->getFile(),

                'line' =>
                    $e->getLine(),

                'trace' =>
                    $e->getTraceAsString(),
            ]
        );


        return response()->json([

            'status' =>
                false,

            'message' =>
                $e->getMessage(),

        ], 500);
    }
}


    /*
    |--------------------------------------------------------------------------
    | Razorpay Callback
    |--------------------------------------------------------------------------
    */

    public function callback(
        Request $request
    ) {
        try {

            /*
            |--------------------------------------------------------------------------
            | Payment Link ID
            |--------------------------------------------------------------------------
            */

            $razorpayPaymentLinkId =
                $request->input(
                    'razorpay_payment_link_id'
                );


            if (
                empty(
                    $razorpayPaymentLinkId
                )
            ) {
                return redirect()
                    ->route('home')
                    ->with(
                        'error',
                        'Payment link id missing hai.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Local Payment Link
            |--------------------------------------------------------------------------
            */

            $paymentLink =
                PlanPaymentLink::query()
                    ->with('plan')
                    ->where(
                        'razorpay_payment_link_id',
                        $razorpayPaymentLinkId
                    )
                    ->firstOrFail();


            /*
            |--------------------------------------------------------------------------
            | Fetch Directly From Razorpay
            |--------------------------------------------------------------------------
            |
            | Browser request par blindly trust nahi karenge.
            | Razorpay API se actual status fetch hoga.
            |
            */

            $response =
                Http::withBasicAuth(
                    config(
                        'services.razorpay.key'
                    ),
                    config(
                        'services.razorpay.secret'
                    )
                )
                    ->acceptJson()
                    ->timeout(30)
                    ->get(
                        'https://api.razorpay.com/v1/payment_links/'
                        . $razorpayPaymentLinkId
                    );


            if ($response->failed()) {

                throw new \RuntimeException(
                    'Razorpay payment verify nahi ho paya.'
                );
            }


            $razorpayLink =
                $response->json();


            /*
            |--------------------------------------------------------------------------
            | Reference Match Check
            |--------------------------------------------------------------------------
            */

            if (
                ($razorpayLink['reference_id'] ?? null)
                !==
                $paymentLink->razorpay_reference_id
            ) {

                throw new \RuntimeException(
                    'Payment reference mismatch.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Payment Status
            |--------------------------------------------------------------------------
            */

            $status =
                $razorpayLink['status']
                ?? 'unknown';


            $paymentLink->update([

                'status' =>
                    $status,

                'gateway_response' =>
                    $razorpayLink,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Not Paid
            |--------------------------------------------------------------------------
            */

            if ($status !== 'paid') {

                return redirect()
                    ->route('plans.payment', [
                        'plan' =>
                            $paymentLink->plan_id,
                    ])
                    ->with(
                        'error',
                        'Payment abhi complete nahi hua.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Get Captured Payment
            |--------------------------------------------------------------------------
            */

            $razorpayPaymentId =
                data_get(
                    $razorpayLink,
                    'payments.0.payment_id'
                )
                ?: data_get(
                    $razorpayLink,
                    'payments.0.id'
                )
                ?: $request->input(
                    'razorpay_payment_id'
                );


            /*
            |--------------------------------------------------------------------------
            | Process Payment Only Once
            |--------------------------------------------------------------------------
            */

            DB::transaction(
                function () use (
                    $paymentLink,
                    $razorpayLink,
                    $razorpayPaymentId
                ) {

                    $lockedLink =
                        PlanPaymentLink::query()
                            ->with('plan')
                            ->lockForUpdate()
                            ->findOrFail(
                                $paymentLink->id
                            );


                    if (
                        $lockedLink->paid_at
                    ) {
                        return;
                    }


                    $plan =
                        $lockedLink->plan;


                    /*
                    |--------------------------------------------------------------------------
                    | Save Payment
                    |--------------------------------------------------------------------------
                    */

                    PlanPayment::updateOrCreate(

                        [
                            'transaction_id' =>
                                $razorpayPaymentId
                                ?: $lockedLink
                                    ->razorpay_payment_link_id,
                        ],

                        [
                            'plan_id' =>
                                $plan->id,

                            'user_id' =>
                                $lockedLink->user_id,

                            'payment_status' =>
                                'success',

                            'payment_gateway' =>
                                'razorpay',

                            'payment_method' =>
                                'payment_link',

                            'amount' =>
                                $lockedLink
                                    ->total_amount,

                            'name' =>
                                $lockedLink
                                    ->customer_name,

                            'email' =>
                                $lockedLink
                                    ->customer_email,

                            'gateway_response' => [

                                'razorpay_payment_link_id' =>
                                    $lockedLink
                                        ->razorpay_payment_link_id,

                                'razorpay_reference_id' =>
                                    $lockedLink
                                        ->razorpay_reference_id,

                                'razorpay_payment_id' =>
                                    $razorpayPaymentId,

                                'payment_link_data' =>
                                    $razorpayLink,
                            ],
                        ]
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Update Local Payment Link
                    |--------------------------------------------------------------------------
                    */

                    $lockedLink->update([

                        'status' =>
                            'paid',

                        'razorpay_payment_id' =>
                            $razorpayPaymentId,

                        'paid_at' =>
                            now(),

                        'gateway_response' =>
                            $razorpayLink,
                    ]);
                }
            );


            /*
            |--------------------------------------------------------------------------
            | Logged In Existing User
            |--------------------------------------------------------------------------
            */

            if (
                $paymentLink->user_id
            ) {

                $user =
                    User::find(
                        $paymentLink->user_id
                    );


                if ($user) {

                    $this->activatePlan(
                        $user,
                        $paymentLink->plan
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Guest Payment
            |--------------------------------------------------------------------------
            */

            if (
                ! $paymentLink->user_id
            ) {

                session([

                    'paid_plan_id' =>
                        $paymentLink->plan_id,

                    'paid_razorpay_payment_id' =>
                        $razorpayPaymentId,

                    'paid_name' =>
                        $paymentLink
                            ->customer_name,

                    'paid_email' =>
                        $paymentLink
                            ->customer_email,

                    'paid_phone' =>
                        $paymentLink
                            ->customer_phone,

                    'payment_done' =>
                        true,

                    'email_verified' =>
                        true,

                    'payment_link_id' =>
                        $paymentLink->id,
                ]);


                return redirect()
                    ->route(
                        'user.register',
                        [
                            'plan_id' =>
                                $paymentLink
                                    ->plan_id,

                            'trial' =>
                                0,

                            'payment_done' =>
                                1,
                        ]
                    )
                    ->with(
                        'success',
                        'Payment successful. Ab account create karein.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Existing Customer
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('dashboard')
                ->with(
                    'success',
                    'Payment successful. Plan activate ho gaya.'
                );


        } catch (\Throwable $e) {

            Log::error(
                'Payment link callback failed',
                [
                    'message' =>
                        $e->getMessage(),

                    'request' =>
                        $request->all(),
                ]
            );


            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'Payment verify nahi ho paya: '
                    . $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Activate Plan For Existing User
    |--------------------------------------------------------------------------
    */

    private function activatePlan(
        User $user,
        Plan $plan
    ): void {

        $plan->loadMissing(
            'permissions'
        );


        /*
        |--------------------------------------------------------------------------
        | Find Business
        |--------------------------------------------------------------------------
        */

        $businessId =
            $user->current_business_id
            ?? session(
                'active_business_id'
            )
            ?? $user->businesses()
                ->pluck('businesses.id')
                ->first();


        DB::transaction(
            function () use (
                $user,
                $plan,
                $businessId
            ) {

                /*
                |--------------------------------------------------------------------------
                | Disable Existing Plan
                |--------------------------------------------------------------------------
                */

                if ($businessId) {

                    UserPlan::query()
                        ->where(
                            'business_id',
                            $businessId
                        )
                        ->where(
                            'status',
                            'active'
                        )
                        ->update([
                            'status' =>
                                'inactive',
                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | New User Plan
                    |--------------------------------------------------------------------------
                    */

                    UserPlan::create([

                        'business_id' =>
                            $businessId,

                        'user_id' =>
                            $user->id,

                        'plan_id' =>
                            $plan->id,

                        'number_of_office' =>
                            $plan->number_of_office
                            ?? 1,

                        'number_of_user' =>
                            $plan->number_of_user
                            ?? 1,

                        'start_date' =>
                            Carbon::today(),

                        'expiry_date' =>
                            Carbon::today()
                                ->addDays(
                                    (int) (
                                        $plan
                                            ->duration_days
                                        ?? 30
                                    )
                                ),

                        'status' =>
                            'active',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Assign Plan Permissions
                |--------------------------------------------------------------------------
                */

                $permissions =
                    $plan->permissions
                        ->pluck('name')
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray();


                $user->syncPermissions(
                    $permissions
                );


                app(
                    PermissionRegistrar::class
                )->forgetCachedPermissions();
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Phone
    |--------------------------------------------------------------------------
    */

    private function normalizePhone(
        string $phone
    ): string {

        $phone =
            preg_replace(
                '/\D+/',
                '',
                $phone
            );


        if (
            strlen($phone) === 12
            &&
            str_starts_with(
                $phone,
                '91'
            )
        ) {
            $phone =
                substr(
                    $phone,
                    2
                );
        }


        if (
            strlen($phone) === 11
            &&
            str_starts_with(
                $phone,
                '0'
            )
        ) {
            $phone =
                substr(
                    $phone,
                    1
                );
        }


        if (
            ! preg_match(
                '/^[6-9][0-9]{9}$/',
                $phone
            )
        ) {
            throw new \InvalidArgumentException(
                'Valid 10 digit mobile number enter karein.'
            );
        }


        return $phone;
    }


    /*
    |--------------------------------------------------------------------------
    | Existing User Find By Mobile
    |--------------------------------------------------------------------------
    */

    private function findUserByPhone(
        string $phone
    ): ?User {

        return User::query()

            ->where(
                'phone',
                $phone
            )

            ->orWhere(
                'phone',
                '91' . $phone
            )

            ->orWhere(
                'phone',
                '+91' . $phone
            )

            ->first();
    }
}