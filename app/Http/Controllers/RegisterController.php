<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\RegisterOtpMail;
use App\Models\User;
use App\Models\Business;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use Illuminate\Auth\Events\Registered;

use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Plan;
use App\Models\UserPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function sendEmailOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        $email = strtolower(trim($request->email));

        // 6 digit OTP
        $otp = (string) random_int(100000, 999999);

        // 10 min ke liye store
        Cache::put('register_otp_' . $email, $otp, now()->addMinutes(10));
        Cache::put('register_otp_verified_' . $email, false, now()->addMinutes(10));

        Mail::to($email)->send(new RegisterOtpMail($otp));

        return response()->json([
            'success' => true,
            'message' => 'OTP email par bhej diya gaya hai.',
        ]);
    }

    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp'   => ['required', 'digits:6'],
        ]);

        $email = strtolower(trim($request->email));
        $savedOtp = Cache::get('register_otp_' . $email);

        if (!$savedOtp) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expire ho gaya hai. Dobara send kijiye.',
            ], 422);
        }

        if ((string) $savedOtp !== (string) $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP.',
            ], 422);
        }

        Cache::put('register_otp_verified_' . $email, true, now()->addMinutes(30));
        session(['register_email_verified' => $email]);

        return response()->json([
            'success' => true,
            'message' => 'Email successfully verify ho gaya.',
        ]);
    }



    public function sendPhoneOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'digits:10', 'unique:users,phone'],
        ]);

        $phone = trim($request->phone);

        $otp = (string) random_int(100000, 999999);

        Cache::put('register_phone_otp_' . $phone, $otp, now()->addMinutes(10));
        Cache::put('register_phone_otp_verified_' . $phone, false, now()->addMinutes(10));

        session()->forget([
            'register_phone_verified',
        ]);

        $msg = "Dear Customer, {$otp} this is your login verification OTP. Please do not share with anyone. Best Regards, Real Victory Groups https://myvictory.in/";

        try {
            $response = Http::timeout(15)->get(env('KUTILITY_URL'), [
                'key' => env('KUTILITY_KEY'),
                'campaign' => '12754',
                'routeid' => '7',
                'type' => 'text',
                'contacts' => $phone,
                'senderid' => 'RVGRPS',
                'msg' => $msg,
                'template_id' => '1707178057481157648',
                'pe_id' => '1701164032595209992',
            ]);

            if (! $response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'SMS gateway se OTP send nahi hua. Please try again.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP phone number par bhej diya gaya hai.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'OTP bhejne me problem aayi. Please try again.',
            ], 500);
        }
    }

    public function verifyPhoneOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'digits:10'],
            'otp'   => ['required', 'digits:6'],
        ]);

        $phone = trim($request->phone);

        $savedOtp = Cache::get('register_phone_otp_' . $phone);

        if (! $savedOtp) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expire ho gaya hai. Dobara send kijiye.',
            ], 422);
        }

        if ((string) $savedOtp !== (string) $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP.',
            ], 422);
        }

        Cache::put('register_phone_otp_verified_' . $phone, true, now()->addMinutes(30));

        session([
            'register_phone_verified' => $phone,
        ]);

        Cache::forget('register_phone_otp_' . $phone);

        return response()->json([
            'success' => true,
            'message' => 'Phone number successfully verify ho gaya.',
        ]);
    }


// public function store(Request $request)
// {
//     $planId = $request->input('plan_id');
//     $isTrial = (int) $request->input('trial', 0) === 1;
//     $paymentDone = session('payment_done') === true || (int) $request->input('payment_done', 0) === 1;

//     $request->validate([
//         'name' => ['required', 'string', 'max:255'],
//         'password' => ['required', 'confirmed', Password::min(6)],

//         'phone' => ['required', 'digits:10', 'unique:users,phone'],

//         'business_name' => ['required', 'string', 'max:255'],

//         // Business email nullable hai
//         'business_email' => ['nullable', 'email', 'max:255'],

//         // User phone aur business mobile same ho sakta hai
//         'mobile' => ['required', 'string', 'max:20'],

//         'gstin' => ['nullable', 'string', 'max:255', 'unique:businesses,gstin'],
//         'address' => ['nullable', 'string', 'max:255'],
//         'state' => ['nullable', 'string', 'max:255'],
//         'state_code' => ['nullable', 'string', 'max:255'],
//         'business_type_id' => ['required', 'string', 'max:255'],

//         'gst_enabled' => ['nullable', 'in:0,1'],
//         'invoice_base_prefix' => ['nullable', 'string', 'max:255'],
//         'rounding_mode' => ['nullable', 'in:none,nearest,up,down'],
//         'rounding_step' => ['nullable', 'numeric', 'min:0'],

//         'terms' => ['required', 'accepted'],
//     ]);

//     if (session('register_phone_verified') !== $request->phone) {
//         return back()
//             ->withInput()
//             ->withErrors([
//                 'phone' => 'Pehle phone OTP verify kijiye.',
//             ]);
//     }

//     DB::beginTransaction();

//     try {
//         $verifiedPhone = trim($request->phone);

//         /*
//          | User email form se hata diya hai.
//          | Agar users.email database me nullable nahi hai,
//          | to ye fake unique email save hoga.
//          */
//         $userEmail = $verifiedPhone . '@noemail.local';

//         /*
//          | Business email optional hai.
//          | User dalega to save hoga, warna null save hoga.
//          */
//         $businessEmail = $request->filled('business_email')
//             ? strtolower(trim($request->business_email))
//             : null;

//         $user = User::create([
//             'name' => $request->name,
//             'email' => $userEmail,
//             'phone' => $verifiedPhone,
//             'password' => Hash::make($request->password),
//         ]);

//         $slug = $this->generateUniqueBusinessSlug($request->business_name);

//         $business = Business::create([
//             'name' => $request->business_name,
//             'slug' => $slug,
//             'email' => $businessEmail,
//             'mobile' => $verifiedPhone,
//             'gstin' => $request->gstin,
//             'gst_enabled' => $request->gst_enabled ?? 1,
//             'address' => $request->address,
//             'state' => $request->state,
//             'state_code' => $request->state_code,
//             'business_type_id' => $request->business_type_id,
//             'invoice_base_prefix' => $request->invoice_base_prefix ?: 'RV/SL',
//             'rounding_mode' => $request->rounding_mode ?: 'nearest',
//             'rounding_step' => $request->rounding_step ?: 1.00,
//         ]);

//         DB::table('business_user')->insert([
//             'business_id' => $business->id,
//             'user_id' => $user->id,
//             'role' => 'owner',
//             'created_at' => now(),
//             'updated_at' => now(),
//         ]);

//         $user->update([
//             'current_business_id' => $business->id,
//         ]);

//         session(['active_business_id' => $business->id]);

//         if ($planId && ($paymentDone || $isTrial)) {
//             $plan = Plan::with('permissions')->findOrFail($planId);

//             UserPlan::where('business_id', $business->id)
//                 ->where('status', 1)
//                 ->update(['status' => 0]);

//             UserPlan::create([
//                 'business_id' => $business->id,
//                 'user_id' => $user->id,
//                 'plan_id' => $plan->id,
//                 'start_date' => Carbon::today(),
//                 'expiry_date' => Carbon::today()->addDays((int) ($plan->duration_days ?? 30)),
//                 'status' => 1,
//             ]);

//             app(PermissionRegistrar::class)->forgetCachedPermissions();

//             $permissions = $plan->permissions()
//                 ->where('guard_name', 'web')
//                 ->pluck('name')
//                 ->toArray();

//             if (!empty($permissions)) {
//                 $user->syncPermissions($permissions);
//             }
//         }

//         DB::commit();

//         event(new Registered($user));
//         Auth::login($user);

//         session()->forget([
//             'register_phone_verified',
//             'register_phone_otp',
//             'register_phone_otp_expires_at',
//         ]);

//         if ($planId && $paymentDone) {
//             session()->forget([
//                 'paid_plan_id',
//                 'paid_razorpay_order_id',
//                 'paid_razorpay_payment_id',
//                 'paid_razorpay_signature',
//                 'payment_done',
//                 'paid_name',
//                 'paid_email',
//             ]);

//             return redirect()->route('bill-templates.choose')
//                 ->with('success', 'Registration successful. Payment already completed. Please choose your bill template.');
//         }

//         if ($planId && $isTrial) {
//             return redirect()->route('bill-templates.choose')
//                 ->with('success', 'Registration successful. Free trial started. Please choose your bill template.');
//         }

//         if ($planId && !$isTrial) {
//             return redirect()->route('plan.payment', $planId)
//                 ->with('success', 'Registration successful. Please complete payment to start your plan.');
//         }

//         return redirect()->route('plan.choose')
//             ->with('success', 'Registration successful. Your business has been created.');

//     } catch (\Throwable $e) {
//         DB::rollBack();

//         return back()
//             ->withInput()
//             ->withErrors([
//                 'register_error' => 'Registration failed: ' . $e->getMessage(),
//             ]);
//     }
// }



public function store(Request $request)
{
    $planId = $request->input('plan_id');

    $isTrial = (int) $request->input('trial', 0) === 1;

    $paymentDone =
        session('payment_done') === true ||
        (int) $request->input('payment_done', 0) === 1;

    /*
    |--------------------------------------------------------------------------
    | Skip flags
    |--------------------------------------------------------------------------
    */

    $businessSkipped = $request->boolean('business_skipped');
    $billingSkipped = $request->boolean('billing_skipped');

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    |
    | Password field form se remove kar diya gaya hai.
    | Business aur billing fields skip flags ke according conditional hain.
    |
    */

    $validated = $request->validate([
        'name' => [
            'required',
            'string',
            'max:255',
        ],

        'phone' => [
            'required',
            'digits:10',
            'unique:users,phone',
        ],

        'business_skipped' => [
            'nullable',
            'boolean',
        ],

        'billing_skipped' => [
            'nullable',
            'boolean',
        ],

        'business_name' => [
            'nullable',
            'required_unless:business_skipped,1',
            'string',
            'max:255',
        ],

        'business_email' => [
            'nullable',
            'email',
            'max:255',
        ],

        'mobile' => [
            'nullable',
            'required_unless:business_skipped,1',
            'digits:10',
        ],

        'gstin' => [
            'nullable',
            'string',
            'max:255',
            Rule::unique('businesses', 'gstin'),
        ],

        'address' => [
            'nullable',
            'string',
            'max:255',
        ],

        'state' => [
            'nullable',
            'string',
            'max:255',
        ],

        'state_code' => [
            'nullable',
            'string',
            'max:255',
        ],

        'business_type_id' => [
            'nullable',
            'required_unless:business_skipped,1',
            'integer',
            'exists:business_types,id',
        ],

        'gst_enabled' => [
            'nullable',
            'required_unless:billing_skipped,1',
            'in:0,1',
        ],

        'invoice_base_prefix' => [
            'nullable',
            'string',
            'max:255',
        ],

        'rounding_mode' => [
            'nullable',
            'required_unless:billing_skipped,1',
            'in:none,nearest,up,down',
        ],

        'rounding_step' => [
            'nullable',
            'required_unless:billing_skipped,1',
            'numeric',
            'min:0',
        ],

        'terms' => [
            'required',
            'accepted',
        ],
    ], [
        'business_name.required_unless' =>
            'Business skip nahi kar rahe hain to business name required hai.',

        'mobile.required_unless' =>
            'Business skip nahi kar rahe hain to business mobile required hai.',

        'business_type_id.required_unless' =>
            'Business skip nahi kar rahe hain to business type required hai.',

        'gst_enabled.required_unless' =>
            'Billing setup skip nahi kar rahe hain to GST setting required hai.',

        'rounding_mode.required_unless' =>
            'Billing setup skip nahi kar rahe hain to rounding mode required hai.',

        'rounding_step.required_unless' =>
            'Billing setup skip nahi kar rahe hain to rounding step required hai.',
    ]);

    /*
    |--------------------------------------------------------------------------
    | OTP verification
    |--------------------------------------------------------------------------
    */

    $verifiedSessionPhone = trim(
        (string) session('register_phone_verified')
    );

    $submittedPhone = trim(
        (string) $validated['phone']
    );

    if ($verifiedSessionPhone !== $submittedPhone) {
        return back()
            ->withInput()
            ->withErrors([
                'phone' => 'Pehle phone OTP verify kijiye.',
            ]);
    }

    DB::beginTransaction();

    try {
        $verifiedPhone = $submittedPhone;

        /*
        |--------------------------------------------------------------------------
        | User email
        |--------------------------------------------------------------------------
        |
        | User email form me nahi hai, isliye verified phone se unique internal
        | email create ki ja rahi hai.
        |
        */

        $userEmail = $verifiedPhone . '@noemail.local';

        /*
        |--------------------------------------------------------------------------
        | Password
        |--------------------------------------------------------------------------
        |
        | Password fields remove hain. Database column required ho sakta hai,
        | isliye secure random password generate karke hash save hoga.
        |
        */

        $generatedPassword = Str::random(40);

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => $userEmail,
            'phone' => $verifiedPhone,
            'password' => Hash::make($generatedPassword),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Default business type
        |--------------------------------------------------------------------------
        |
        | Business skip karne par business_type_id form se nahi aayega.
        | Pehla available business type default ke roop me use hoga.
        |
        */

        $defaultBusinessTypeId = DB::table('business_types')
            ->orderBy('id')
            ->value('id');

        $businessTypeId = $businessSkipped
            ? $defaultBusinessTypeId
            : (int) $validated['business_type_id'];

        if (!$businessTypeId) {
            throw new \RuntimeException(
                'Koi business type available nahi hai. Admin panel se kam se kam ek business type create kijiye.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Business information
        |--------------------------------------------------------------------------
        */

        if ($businessSkipped) {
            $businessName = trim($validated['name']) . "'s Business";
            $businessEmail = null;
            $businessGstin = null;
            $businessAddress = null;
            $businessState = null;
            $businessStateCode = null;
        } else {
            $businessName = trim($validated['business_name']);

            $businessEmail = !empty($validated['business_email'])
                ? strtolower(trim($validated['business_email']))
                : null;

            $businessGstin = !empty($validated['gstin'])
                ? strtoupper(trim($validated['gstin']))
                : null;

            $businessAddress = !empty($validated['address'])
                ? trim($validated['address'])
                : null;

            $businessState = !empty($validated['state'])
                ? trim($validated['state'])
                : null;

            $businessStateCode = !empty($validated['state_code'])
                ? trim($validated['state_code'])
                : null;
        }

        /*
        |--------------------------------------------------------------------------
        | Billing preferences
        |--------------------------------------------------------------------------
        |
        | Billing skip hone par safe defaults save honge.
        |
        */

        if ($billingSkipped) {
            $gstEnabled = 0;
            $invoiceBasePrefix = 'RV/SL';
            $roundingMode = 'nearest';
            $roundingStep = 1.00;
        } else {
            $gstEnabled = (int) ($validated['gst_enabled'] ?? 0);

            $invoiceBasePrefix = !empty($validated['invoice_base_prefix'])
                ? trim($validated['invoice_base_prefix'])
                : 'RV/SL';

            $roundingMode =
                $validated['rounding_mode'] ?? 'nearest';

            $roundingStep =
                (float) ($validated['rounding_step'] ?? 1.00);
        }

        /*
        |--------------------------------------------------------------------------
        | Create business
        |--------------------------------------------------------------------------
        */

        $slug = $this->generateUniqueBusinessSlug($businessName);

        $business = Business::create([
            'name' => $businessName,
            'slug' => $slug,

            'email' => $businessEmail,
            'mobile' => $verifiedPhone,

            'gstin' => $businessGstin,
            'gst_enabled' => $gstEnabled,

            'address' => $businessAddress,
            'state' => $businessState,
            'state_code' => $businessStateCode,

            'type' => (int) $businessTypeId,

            'invoice_base_prefix' => $invoiceBasePrefix,
            'rounding_mode' => $roundingMode,
            'rounding_step' => $roundingStep,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Attach owner
        |--------------------------------------------------------------------------
        */

        DB::table('business_user')->insert([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user->update([
            'current_business_id' => $business->id,
        ]);

        session([
            'active_business_id' => $business->id,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Plan activation
        |--------------------------------------------------------------------------
        */

        if ($planId && ($paymentDone || $isTrial)) {
            $plan = Plan::with('permissions')
                ->findOrFail($planId);

            UserPlan::where('business_id', $business->id)
                ->where('status', 1)
                ->update([
                    'status' => 0,
                ]);

            UserPlan::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'start_date' => Carbon::today(),
                'expiry_date' => Carbon::today()->addDays(
                    (int) ($plan->duration_days ?? 30)
                ),
                'status' => 1,
            ]);

            app(PermissionRegistrar::class)
                ->forgetCachedPermissions();

            $permissions = $plan->permissions()
                ->where('guard_name', 'web')
                ->pluck('name')
                ->toArray();

            if (!empty($permissions)) {
                $user->syncPermissions($permissions);
            }
        }

        DB::commit();

        /*
        |--------------------------------------------------------------------------
        | Login and session cleanup
        |--------------------------------------------------------------------------
        */

        event(new Registered($user));

        Auth::login($user);

        session()->forget([
            'register_phone_verified',
            'register_phone_otp',
            'register_phone_otp_expires_at',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Redirect messages
        |--------------------------------------------------------------------------
        */

        $registrationMessage = 'Registration successful.';

        if ($businessSkipped && $billingSkipped) {
            $registrationMessage .=
                ' Business details aur billing preferences default settings ke saath skip kar di gayi hain.';
        } elseif ($businessSkipped) {
            $registrationMessage .=
                ' Business details abhi skip kar di gayi hain.';
        } elseif ($billingSkipped) {
            $registrationMessage .=
                ' Billing preferences default settings ke saath skip kar di gayi hain.';
        } else {
            $registrationMessage .=
                ' Your business has been created successfully.';
        }

        /*
        |--------------------------------------------------------------------------
        | Paid plan
        |--------------------------------------------------------------------------
        */

        if ($planId && $paymentDone) {
            session()->forget([
                'paid_plan_id',
                'paid_razorpay_order_id',
                'paid_razorpay_payment_id',
                'paid_razorpay_signature',
                'payment_done',
                'paid_name',
                'paid_email',
            ]);

            return redirect()
                ->route('bill-templates.choose')
                ->with(
                    'success',
                    $registrationMessage .
                    ' Payment already completed. Please choose your bill template.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Trial plan
        |--------------------------------------------------------------------------
        */

        if ($planId && $isTrial) {
            return redirect()
                ->route('bill-templates.choose')
                ->with(
                    'success',
                    $registrationMessage .
                    ' Free trial started. Please choose your bill template.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Unpaid selected plan
        |--------------------------------------------------------------------------
        */

        if ($planId && !$isTrial) {
            return redirect()
                ->route('plan.payment', $planId)
                ->with(
                    'success',
                    $registrationMessage .
                    ' Please complete payment to start your plan.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | No selected plan
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('plan.choose')
            ->with('success', $registrationMessage);

    } catch (\Throwable $e) {
        DB::rollBack();

        report($e);

        return back()
            ->withInput()
            ->withErrors([
                'register_error' =>
                    'Registration failed: ' . $e->getMessage(),
            ]);
    }
}

        private function generateUniqueBusinessSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base ?: 'business';
        $count = 1;

        while (Business::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
