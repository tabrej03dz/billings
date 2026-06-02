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
use Spatie\Permission\PermissionRegistrar;

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

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name'                 => ['required', 'string', 'max:255'],
    //         'email'                => ['required', 'email', 'max:255', 'unique:users,email'],
    //         'password'             => ['required', 'confirmed', 'min:6'],

    //         'business_name'        => ['required', 'string', 'max:255'],
    //         'business_email'       => ['required', 'email', 'max:255'],
    //         'mobile'               => ['required', 'string', 'max:20'],
    //         'gstin'                => ['nullable', 'string', 'max:15'],
    //         'type'                 => ['required', 'string', 'max:100'],
    //         'address'              => ['nullable', 'string'],
    //         'state'                => ['nullable', 'string', 'max:100'],
    //         'state_code'           => ['nullable', 'string', 'max:10'],

    //         'gst_enabled'          => ['nullable', 'in:0,1'],
    //         'invoice_base_prefix'  => ['nullable', 'string', 'max:50'],
    //         'rounding_mode'        => ['nullable', 'in:none,nearest,up,down'],
    //         'rounding_step'        => ['nullable', 'numeric'],
    //         'terms'                => ['accepted'],
    //         'current_step' => ['nullable', 'integer', 'min:1', 'max:3'],
    //     ]);

    //     $email = strtolower(trim($request->email));
    //     $verifiedEmail = session('register_email_verified');
    //     $isOtpVerified = Cache::get('register_otp_verified_' . $email);

    //     if ($verifiedEmail !== $email || !$isOtpVerified) {
    //         throw ValidationException::withMessages([
    //             'email' => ['Pehle email OTP verify kijiye.'],
    //         ]);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         $user = User::create([
    //             'name'     => $request->name,
    //             'email'    => $email,
    //             'password' => Hash::make($request->password),
    //         ]);

    //         // Agar aapke project me role system hai
    //         // if (method_exists($user, 'assignRole')) {
    //         //     $user->assignRole('admin');
    //         // }

    //         // Business model ke fields apne table ke hisab se adjust kar lena
    //         $business = Business::create([
    //             'name'                => $request->business_name,
    //             'email'               => $request->business_email,
    //             'mobile'              => $request->mobile,
    //             'gstin'               => $request->gstin,
    //             'type'                => $request->type,
    //             'address'             => $request->address,
    //             'state'               => $request->state,
    //             'state_code'          => $request->state_code,
    //             'gst_enabled'         => $request->gst_enabled ?? 1,
    //             'invoice_base_prefix' => $request->invoice_base_prefix ?? 'RV/SL',
    //             'rounding_mode'       => $request->rounding_mode ?? 'nearest',
    //             'rounding_step'       => $request->rounding_step ?? 1.00,
    //             'created_by'          => $user->id,
    //         ]);

    //         // Agar owner mapping field hai to use update kar sakte ho
    //         if (\Schema::hasColumn('businesses', 'owner_id')) {
    //             $business->owner_id = $user->id;
    //             $business->save();
    //         }

    //         // User ko business se map karna ho to
    //         if (\Schema::hasColumn('users', 'business_id')) {
    //             $user->business_id = $business->id;
    //             $user->save();
    //         }

    //         DB::commit();

    //         Cache::forget('register_otp_' . $email);
    //         Cache::forget('register_otp_verified_' . $email);
    //         session()->forget('register_email_verified');

    //         Auth::login($user);

    //         return redirect()->route('plan.choose')->with('success', 'Account successfully create ho gaya.');
    //     } catch (\Throwable $e) {
    //         DB::rollBack();

    //         return back()
    //             ->withInput()
    //             ->withErrors([
    //                 'error' => 'Registration failed: ' . $e->getMessage(),
    //             ]);
    //     }
    // }


    //     public function store(Request $request)
    // {
    //     $request->validate([
    //         // user step
    //         'name' => ['required', 'string', 'max:255'],
    //         'email' => ['required', 'email', 'max:255', 'unique:users,email'],
    //         'password' => ['required', 'confirmed', Password::min(6)],

    //         // business step
    //         'business_name' => ['required', 'string', 'max:255'],
    //         'business_email' => ['required', 'email', 'max:255', 'unique:businesses,email'],
    //         'mobile' => ['required', 'string', 'max:20', 'unique:businesses,mobile'],
    //         'gstin' => ['nullable', 'string', 'max:255', 'unique:businesses,gstin'],
    //         'address' => ['nullable', 'string', 'max:255'],
    //         'state' => ['nullable', 'string', 'max:255'],
    //         'state_code' => ['nullable', 'string', 'max:255'],
    //         'business_type_id' => ['required', 'string', 'max:255'],

    //         // billing step
    //         'gst_enabled' => ['nullable', 'in:0,1'],
    //         'invoice_base_prefix' => ['nullable', 'string', 'max:255'],
    //         'rounding_mode' => ['nullable', 'in:none,nearest,up,down'],
    //         'rounding_step' => ['nullable', 'numeric', 'min:0'],

    //         'terms' => ['required', 'accepted'],
    //     ]);

    //     DB::beginTransaction();

    //     try {
    //         $user = User::create([
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'password' => Hash::make($request->password),
    //         ]);

    //         $slug = $this->generateUniqueBusinessSlug($request->business_name);

    //         $business = Business::create([
    //             'name' => $request->business_name,
    //             'slug' => $slug,
    //             'email' => $request->business_email,
    //             'mobile' => $request->mobile,
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


    //         // 6) Important permissions
    //         // $importantPermissions = [
    //         //     'show users',
    //         //     'create user',
    //         //     'edit user',
    //         //     'delete user',

    //         //     'show businesses',
    //         //     'create business',
    //         //     'edit business',
    //         //     'delete business',

    //         //     'show clients',
    //         //     'create client',
    //         //     'edit client',
    //         //     'delete client',

    //         //     'show invoices',
    //         //     'create invoice',
    //         //     'edit invoice',
    //         //     'delete invoice',
    //         //     'download invoice',

    //         //     'show invoices menu',
    //         //     'show proformas',
    //         //     'create proforma',
    //         //     'edit proforma',
    //         //     'delete proforma',

    //         //     'show quotations',
    //         //     'create quotation',
    //         //     'edit quotation',
    //         //     'delete quotation',

    //         //     'show categories',
    //         //     'create category',
    //         //     'edit category',
    //         //     'delete category',

    //         //     'show items',
    //         //     'create item',
    //         //     'edit item',
    //         //     'delete item',

    //         //     'show additional charges',
    //         //     'create additional charge',
    //         //     'edit additional charge',
    //         //     'delete additional charge',

    //         //     'show purchases',
    //         //     'show inventory',
    //         //     'show invoice sends',
    //         //     'show bank balance',
    //         //     'show installment reminders',
    //         // ];

    //         // // sirf wahi permissions assign hongi jo DB me available hain
    //         // $existingPermissions = Permission::whereIn('name', $importantPermissions)
    //         //     ->pluck('name')
    //         //     ->toArray();

    //         // // direct permissions
    //         // if (!empty($existingPermissions)) {
    //         //     $user->givePermissionTo($existingPermissions);
    //         // }

    //         // // optional: owner role assign karo agar role table me hai
    //         // $ownerRole = Role::where('name', 'owner')->first();
    //         // if ($ownerRole) {
    //         //     $user->assignRole($ownerRole);
    //         // }

    //         DB::commit();

    //         event(new Registered($user));
    //         Auth::login($user);

    //         $planId = $request->input('plan_id');
    //         $isTrial = (int) $request->input('trial', 0) === 1;

    //         if ($planId && $isTrial) {
    //             return redirect()->route('bill-templates.choose')
    //                 ->with('success', 'Registration successful. Free trial started. Please choose your bill template.');
    //         }

    //         if ($planId && ! $isTrial) {
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
    //                 'register_error' => 'Registration failed: ' . $e->getMessage()
    //             ]);
    //     }
    // }



//     public function store(Request $request)
// {
//     $planId = $request->input('plan_id');
//     $isTrial = (int) $request->input('trial', 0) === 1;
//     $paymentDone = session('payment_done') === true || (int) $request->input('payment_done', 0) === 1;

//     $request->validate([
//         'name' => ['required', 'string', 'max:255'],
//         'email' => ['required', 'email', 'max:255', 'unique:users,email'],
//         'password' => ['required', 'confirmed', Password::min(6)],

//         'business_name' => ['required', 'string', 'max:255'],
//         'business_email' => ['required', 'email', 'max:255', 'unique:businesses,email'],
//         'mobile' => ['required', 'string', 'max:20', 'unique:businesses,mobile'],
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

//     DB::beginTransaction();

//     try {
//         $user = User::create([
//             'name' => $request->name,
//             'email' => strtolower(trim($request->email)),
//             'password' => Hash::make($request->password),
//         ]);

//         $slug = $this->generateUniqueBusinessSlug($request->business_name);

//         $business = Business::create([
//             'name' => $request->business_name,
//             'slug' => $slug,
//             'email' => $request->business_email,
//             'mobile' => $request->mobile,
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

//         if ($planId && $paymentDone) {
//             $plan = Plan::with('permissions')->findOrFail($planId);

//             UserPlan::where('user_id', $user->id)
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

//             $permissions = $plan->permissions->pluck('name')->toArray();

//             if (!empty($permissions)) {
//                 $user->syncPermissions($permissions);
//             }

//             app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
//         }

//         DB::commit();

//         event(new Registered($user));
//         Auth::login($user);

//         if ($planId && $paymentDone) {
//             session()->forget([
//                 'paid_plan_id',
//                 'paid_razorpay_order_id',
//                 'paid_razorpay_payment_id',
//                 'paid_razorpay_signature',
//                 'payment_done',
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
    $paymentDone = session('payment_done') === true || (int) $request->input('payment_done', 0) === 1;

    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'confirmed', Password::min(6)],

        'business_name' => ['required', 'string', 'max:255'],
        'business_email' => ['required', 'email', 'max:255', 'unique:businesses,email'],
        'mobile' => ['required', 'string', 'max:20', 'unique:businesses,mobile'],
        'gstin' => ['nullable', 'string', 'max:255', 'unique:businesses,gstin'],
        'address' => ['nullable', 'string', 'max:255'],
        'state' => ['nullable', 'string', 'max:255'],
        'state_code' => ['nullable', 'string', 'max:255'],
        'business_type_id' => ['required', 'string', 'max:255'],

        'gst_enabled' => ['nullable', 'in:0,1'],
        'invoice_base_prefix' => ['nullable', 'string', 'max:255'],
        'rounding_mode' => ['nullable', 'in:none,nearest,up,down'],
        'rounding_step' => ['nullable', 'numeric', 'min:0'],

        'terms' => ['required', 'accepted'],
    ]);

    DB::beginTransaction();

    try {
        $user = User::create([
            'name' => $request->name,
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
        ]);

        $slug = $this->generateUniqueBusinessSlug($request->business_name);

        $business = Business::create([
            'name' => $request->business_name,
            'slug' => $slug,
            'email' => $request->business_email,
            'mobile' => $request->mobile,
            'gstin' => $request->gstin,
            'gst_enabled' => $request->gst_enabled ?? 1,
            'address' => $request->address,
            'state' => $request->state,
            'state_code' => $request->state_code,
            'business_type_id' => $request->business_type_id,
            'invoice_base_prefix' => $request->invoice_base_prefix ?: 'RV/SL',
            'rounding_mode' => $request->rounding_mode ?: 'nearest',
            'rounding_step' => $request->rounding_step ?: 1.00,
        ]);

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

        session(['active_business_id' => $business->id]);

        if ($planId && ($paymentDone || $isTrial)) {
            $plan = Plan::with('permissions')->findOrFail($planId);

            UserPlan::where('business_id', $business->id)
                ->where('status', 1)
                ->update(['status' => 0]);

            UserPlan::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'start_date' => Carbon::today(),
                'expiry_date' => Carbon::today()->addDays((int) ($plan->duration_days ?? 30)),
                'status' => 1,
            ]);

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $permissions = $plan->permissions()
                ->where('guard_name', 'web')
                ->pluck('name')
                ->toArray();

            if (!empty($permissions)) {
                $user->syncPermissions($permissions);
            }
        }

        DB::commit();

        event(new Registered($user));
        Auth::login($user);

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

            return redirect()->route('bill-templates.choose')
                ->with('success', 'Registration successful. Payment already completed. Please choose your bill template.');
        }

        if ($planId && $isTrial) {
            return redirect()->route('bill-templates.choose')
                ->with('success', 'Registration successful. Free trial started. Please choose your bill template.');
        }

        if ($planId && !$isTrial) {
            return redirect()->route('plan.payment', $planId)
                ->with('success', 'Registration successful. Please complete payment to start your plan.');
        }

        return redirect()->route('plan.choose')
            ->with('success', 'Registration successful. Your business has been created.');
    } catch (\Throwable $e) {
        DB::rollBack();

        return back()
            ->withInput()
            ->withErrors([
                'register_error' => 'Registration failed: ' . $e->getMessage(),
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
