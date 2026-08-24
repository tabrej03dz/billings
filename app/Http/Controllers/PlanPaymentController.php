<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\PlanPayment;
use App\Models\UserPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Spatie\Permission\PermissionRegistrar;

class PlanPaymentController extends Controller
{
    // public function show(Plan $plan)
    // {
    //     return view('plans.payment', compact('plan'));
    // }


    public function show(Plan $plan)
    {
        return view('plans.payment', compact('plan'));
    }

    // public function createOrder(Request $request, Plan $plan)
    // {
    //     $amount = (int) ($plan->price * 100);

    //     $api = new \Razorpay\Api\Api(
    //         config('services.razorpay.key'),
    //         config('services.razorpay.secret')
    //     );

    //     $order = $api->order->create([
    //         'receipt' => 'plan_' . $plan->id . '_' . time(),
    //         'amount' => $amount,
    //         'currency' => 'INR',
    //         'notes' => [
    //             'plan_id' => (string) $plan->id,
    //             'plan_name' => preg_replace('/[^A-Za-z0-9 ]/', '', $plan->name),
    //         ],
    //     ]);

    //     return response()->json([
    //         'key' => config('services.razorpay.key'),
    //         'order_id' => $order['id'],
    //         'amount' => $amount,
    //         'plan_name' => preg_replace('/[^A-Za-z0-9 ]/', '', $plan->name),
    //     ]);
    // }

    public function createOrder(Request $request, Plan $plan)
{
    try {

        /*
        |--------------------------------------------------------------------------
        | Base Amount
        |--------------------------------------------------------------------------
        */
        $basePrice = round((float) $plan->price, 2);


        /*
        |--------------------------------------------------------------------------
        | GST Rate
        |--------------------------------------------------------------------------
        | Plan me tax available ho to wahi use hoga.
        | Nahi ho to default 18%.
        */
        $gstRate = round((float) ($plan->tax ?? 18), 2);


        /*
        |--------------------------------------------------------------------------
        | GST Amount
        |--------------------------------------------------------------------------
        */
        $gstAmount = round(
            ($basePrice * $gstRate) / 100,
            2
        );


        /*
        |--------------------------------------------------------------------------
        | Final Payable Amount
        |--------------------------------------------------------------------------
        */
        $totalPayable = round(
            $basePrice + $gstAmount,
            2
        );


        /*
        |--------------------------------------------------------------------------
        | Razorpay Amount in Paise
        |--------------------------------------------------------------------------
        | Example:
        | ₹5900 = 590000 paise
        */
        $amount = (int) round(
            $totalPayable * 100
        );


        /*
        |--------------------------------------------------------------------------
        | Razorpay API
        |--------------------------------------------------------------------------
        */
        $api = new \Razorpay\Api\Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );


        /*
        |--------------------------------------------------------------------------
        | Safe Plan Name
        |--------------------------------------------------------------------------
        */
        $safePlanName = preg_replace(
            '/[^A-Za-z0-9 ]/',
            '',
            $plan->name
        );


        /*
        |--------------------------------------------------------------------------
        | Create Razorpay Order
        |--------------------------------------------------------------------------
        */
        $order = $api->order->create([

            'receipt' => 'plan_' . $plan->id . '_' . time(),

            'amount' => $amount,

            'currency' => 'INR',

            'notes' => [

                'plan_id' => (string) $plan->id,

                'plan_name' => $safePlanName,

                'base_price' => number_format(
                    $basePrice,
                    2,
                    '.',
                    ''
                ),

                'gst_rate' => number_format(
                    $gstRate,
                    2,
                    '.',
                    ''
                ),

                'gst_amount' => number_format(
                    $gstAmount,
                    2,
                    '.',
                    ''
                ),

                'total_payable' => number_format(
                    $totalPayable,
                    2,
                    '.',
                    ''
                ),
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Send Data to Frontend
        |--------------------------------------------------------------------------
        */
        return response()->json([

            'success' => true,

            'key' => config('services.razorpay.key'),

            'order_id' => $order['id'],

            /*
             * Razorpay amount PAISA me hai.
             */
            'amount' => $amount,

            'currency' => 'INR',

            'plan_name' => $safePlanName,

            /*
             * Ye values frontend/debugging ke liye rupees me hain.
             */
            'base_price' => $basePrice,

            'gst_rate' => $gstRate,

            'gst_amount' => $gstAmount,

            'total_payable' => $totalPayable,
        ]);

    } catch (\Throwable $e) {

        report($e);

        return response()->json([
            'success' => false,
            'message' => 'Razorpay order create nahi ho paaya.',
        ], 500);
    }
}

    // public function success(Request $request, Plan $plan)
    // {
    //     $request->validate([
    //         'razorpay_order_id' => 'required',
    //         'razorpay_payment_id' => 'required',
    //         'razorpay_signature' => 'required',
    //     ]);

    //     $signature = hash_hmac(
    //         'sha256',
    //         $request->razorpay_order_id . '|' . $request->razorpay_payment_id,
    //         config('services.razorpay.secret')
    //     );

    //     if ($signature !== $request->razorpay_signature) {
    //         return back()->with('error', 'Payment verification failed.');
    //     }

    //     $user = Auth::user();

    //     $plan = Plan::with('permissions')->findOrFail($plan->id);

    //     $businessId = $user->current_business_id
    //         ?? session('active_business_id')
    //         ?? $user->businesses()->pluck('businesses.id')->first();

    //     if (!$businessId) {
    //         return back()->with('error', 'Business not found. Please select business first.');
    //     }

    //     DB::transaction(function () use ($businessId, $user, $plan) {
    //         UserPlan::where('business_id', $businessId)
    //             ->where('status', 1)
    //             ->update([
    //                 'status' => 0,
    //             ]);

    //         UserPlan::create([
    //             'business_id' => $businessId,
    //             'user_id'     => $user->id,
    //             'plan_id'     => $plan->id,
    //             'start_date'  => Carbon::today(),
    //             'expiry_date' => Carbon::today()->addDays((int) ($plan->duration_days ?? 30)),
    //             'status'      => 1,
    //         ]);

    //         $permissions = $plan->permissions->pluck('name')->toArray();

    //         $user->syncPermissions($permissions);

    //         app(PermissionRegistrar::class)->forgetCachedPermissions();
    //     });

    //     return redirect()
    //         ->route('bill-templates.choose')
    //         ->with('success', 'Payment successful. Plan activated and permissions assigned.');
    // }

    // public function success(Request $request, Plan $plan)
    // {
    //     $request->validate([
    //         'razorpay_order_id' => 'required',
    //         'razorpay_payment_id' => 'required',
    //         'razorpay_signature' => 'required',
    //         'name' => 'nullable|string|max:255',
    //         'email' => 'nullable|email|max:255',

    //     ]);

    //     $signature = hash_hmac(
    //         'sha256',
    //         $request->razorpay_order_id . '|' . $request->razorpay_payment_id,
    //         config('services.razorpay.secret')
    //     );

    //     if (! hash_equals($signature, $request->razorpay_signature)) {
    //         return back()->with('error', 'Payment verification failed.');
    //     }

    //     $plan = Plan::with('permissions')->findOrFail($plan->id);

    //     PlanPayment::updateOrCreate(
    //         [
    //             'transaction_id' => $request->razorpay_payment_id,
    //         ],
    //         [
    //             'plan_id' => $plan->id,
    //             'user_id' => Auth::id(),
    //             'payment_status' => 'success',
    //             'payment_gateway' => 'razorpay',
    //             'payment_method' => 'online',
    //             'amount' => $plan->price,
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'gateway_response' => [
    //                 'razorpay_order_id' => $request->razorpay_order_id,
    //                 'razorpay_payment_id' => $request->razorpay_payment_id,
    //                 'razorpay_signature' => $request->razorpay_signature,
    //             ],
    //         ]
    //     );

    //     session([
    //         'paid_plan_id' => $plan->id,
    //         'paid_razorpay_order_id' => $request->razorpay_order_id,
    //         'paid_razorpay_payment_id' => $request->razorpay_payment_id,
    //         'paid_razorpay_signature' => $request->razorpay_signature,
    //         'paid_name' => $request->name,
    //         'paid_email' => $request->email,
    //         'payment_done' => true,
    //         'email_verified' => true,
    //     ]);

    //     if (! Auth::check()) {
    //         return redirect()
    //             ->route('user.register', [
    //                 'plan_id' => $plan->id,
    //                 'trial' => 0,
    //                 'payment_done' => 1,
    //             ])
    //             ->with('success', 'Payment successful. Please create your account.');
    //     }

    //     $user = Auth::user();

    //     $businessId = $user->current_business_id
    //         ?? session('active_business_id')
    //         ?? $user->businesses()->pluck('businesses.id')->first();

    //     if (! $businessId) {
    //         return redirect()
    //             ->route('businesses.create')
    //             ->with('success', 'Payment successful. Now add your business details.');
    //     }

    //     DB::transaction(function () use ($businessId, $user, $plan) {
    //         UserPlan::where('business_id', $businessId)
    //             ->where('status', 1)
    //             ->update(['status' => 0]);

    //         UserPlan::create([
    //             'business_id' => $businessId,
    //             'user_id'     => $user->id,
    //             'plan_id'     => $plan->id,
    //             'start_date'  => Carbon::today(),
    //             'expiry_date' => Carbon::today()->addDays((int) ($plan->duration_days ?? 30)),
    //             'status'      => 1,
    //         ]);

    //         $permissions = $plan->permissions->pluck('name')->toArray();

    //         if (! empty($permissions)) {
    //             $user->syncPermissions($permissions);
    //         }

    //         app(PermissionRegistrar::class)->forgetCachedPermissions();
    //     });

    //     session()->forget([
    //         'paid_plan_id',
    //         'paid_razorpay_order_id',
    //         'paid_razorpay_payment_id',
    //         'paid_razorpay_signature',
    //         'payment_done',
    //     ]);

    //     return redirect()
    //         ->route('bill-templates.choose')
    //         ->with('success', 'Payment successful. Plan activated and permissions assigned.');
    // }

    public function success(Request $request, Plan $plan)
{
    $request->validate([
        'razorpay_order_id'   => 'required|string',
        'razorpay_payment_id' => 'required|string',
        'razorpay_signature'  => 'required|string',
        'name'                => 'nullable|string|max:255',
        'email'               => 'nullable|email|max:255',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Verify Razorpay Signature
    |--------------------------------------------------------------------------
    */
    $signature = hash_hmac(
        'sha256',
        $request->razorpay_order_id . '|' . $request->razorpay_payment_id,
        config('services.razorpay.secret')
    );

    if (! hash_equals($signature, $request->razorpay_signature)) {
        return back()->with('error', 'Payment verification failed.');
    }


    /*
    |--------------------------------------------------------------------------
    | Load Plan With Permissions
    |--------------------------------------------------------------------------
    */
    $plan = Plan::with('permissions')->findOrFail($plan->id);


    /*
    |--------------------------------------------------------------------------
    | Calculate GST Included Amount
    |--------------------------------------------------------------------------
    */
    $basePrice = round((float) $plan->price, 2);

    $gstRate = round(
        (float) ($plan->tax ?? 18),
        2
    );

    $gstAmount = round(
        ($basePrice * $gstRate) / 100,
        2
    );

    $totalPayable = round(
        $basePrice + $gstAmount,
        2
    );


    /*
    |--------------------------------------------------------------------------
    | Save Payment
    |--------------------------------------------------------------------------
    */
    PlanPayment::updateOrCreate(
        [
            'transaction_id' => $request->razorpay_payment_id,
        ],
        [
            'plan_id' => $plan->id,

            'user_id' => Auth::id(),

            'payment_status' => 'success',

            'payment_gateway' => 'razorpay',

            'payment_method' => 'online',

            'amount' => $totalPayable,

            'name' => $request->name,

            'email' => $request->email,

            'gateway_response' => [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,

                'base_price' => $basePrice,
                'gst_rate' => $gstRate,
                'gst_amount' => $gstAmount,
                'total_payable' => $totalPayable,
            ],
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Store Payment Data In Session
    |--------------------------------------------------------------------------
    */
    session([
        'paid_plan_id' => $plan->id,

        'paid_razorpay_order_id' => $request->razorpay_order_id,

        'paid_razorpay_payment_id' => $request->razorpay_payment_id,

        'paid_razorpay_signature' => $request->razorpay_signature,

        'paid_name' => $request->name,

        'paid_email' => $request->email,

        'payment_done' => true,

        'email_verified' => true,
    ]);


    /*
    |--------------------------------------------------------------------------
    | Guest User
    |--------------------------------------------------------------------------
    |
    | Agar user login nahi hai to abhi permission assign nahi ki ja sakti,
    | kyunki User model hi available nahi hai.
    |
    | Registration ke baad session ke paid_plan_id se permission assign
    | karni hogi.
    |
    */
    if (! Auth::check()) {

        return redirect()
            ->route('user.register', [
                'plan_id' => $plan->id,
                'trial' => 0,
                'payment_done' => 1,
            ])
            ->with(
                'success',
                'Payment successful. Please create your account.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Logged In User
    |--------------------------------------------------------------------------
    */
    $user = Auth::user();


    /*
    |--------------------------------------------------------------------------
    | Find Current Business
    |--------------------------------------------------------------------------
    */
    $businessId = $user->current_business_id
        ?? session('active_business_id')
        ?? $user->businesses()->pluck('businesses.id')->first();


    if (! $businessId) {

        /*
         * User logged in hai, isliye plan permissions abhi assign kar dete hain.
         * Business create hone ke baad UserPlan create kiya ja sakta hai.
         */

        $permissions = $plan->permissions
            ->pluck('name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (! empty($permissions)) {

            /*
             * IMPORTANT:
             * syncPermissions ka matlab:
             * user ki purani direct permissions remove hongi
             * aur selected plan ki permissions assign hongi.
             */
            $user->syncPermissions($permissions);
        } else {

            /*
             * Agar selected plan me koi permission nahi hai
             * to direct permissions clear kar do.
             */
            $user->syncPermissions([]);
        }


        /*
         * Clear Spatie Permission Cache
         */
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();


        return redirect()
            ->route('businesses.create')
            ->with(
                'success',
                'Payment successful. Plan permissions assigned. Now add your business details.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Activate Plan + Assign Permissions
    |--------------------------------------------------------------------------
    */
    DB::transaction(function () use (
        $businessId,
        $user,
        $plan
    ) {

        /*
         * Disable Previous Active Plans
         */
        UserPlan::where('business_id', $businessId)
            ->where('status', 1)
            ->update([
                'status' => 0,
            ]);


        /*
         * Create New Active Plan
         */
        UserPlan::create([
            'business_id' => $businessId,

            'user_id' => $user->id,

            'plan_id' => $plan->id,

            'start_date' => Carbon::today(),

            'expiry_date' => Carbon::today()
                ->addDays(
                    (int) ($plan->duration_days ?? 30)
                ),

            'status' => 1,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Selected Plan Permissions
        |--------------------------------------------------------------------------
        */
        $permissions = $plan->permissions
            ->pluck('name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Assign Permissions To User
        |--------------------------------------------------------------------------
        |
        | syncPermissions() selected plan ki direct permissions user ko assign
        | karega.
        |
        | Agar pehle kisi doosre plan ki direct permissions thi to wo remove
        | ho jayengi.
        |
        */
        $user->syncPermissions($permissions);


        /*
        |--------------------------------------------------------------------------
        | Clear Permission Cache
        |--------------------------------------------------------------------------
        */
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    });


    /*
    |--------------------------------------------------------------------------
    | Clear Payment Session
    |--------------------------------------------------------------------------
    */
    session()->forget([
        'paid_plan_id',

        'paid_razorpay_order_id',

        'paid_razorpay_payment_id',

        'paid_razorpay_signature',

        'payment_done',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */
    return redirect()
        ->route('bill-templates.choose')
        ->with(
            'success',
            'Payment successful. Plan activated and permissions assigned.'
        );
}

}
