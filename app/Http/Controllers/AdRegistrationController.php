<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessType;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Throwable;

class AdRegistrationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Landing page
    |--------------------------------------------------------------------------
    */

    public function show(): View
    {
        $businessTypes = BusinessType::query()
            ->orderBy('name')
            ->get();

        $plans = Plan::query()
            ->with('planFeatures')
            ->when(
                \Schema::hasColumn('plans', 'status'),
                function ($query) {
                    $query->where(function ($statusQuery) {
                        $statusQuery
                            ->where('status', 1)
                            ->orWhere('status', 'active');
                    });
                }
            )
            ->orderBy('price')
            ->get();

        return view('ad-register', [
            'businessTypes' => $businessTypes,
            'plans' => $plans,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Save popup step temporarily in session
    |--------------------------------------------------------------------------
    |
    | Is method me database registration record create nahi hoga.
    | User jo details bharega wo session me temporarily save hongi.
    | Final submit par User aur Business create honge.
    |
    */

    public function saveStep(Request $request): JsonResponse
    {
        $verifiedPhone = preg_replace(
            '/\D/',
            '',
            (string) session('register_phone_verified')
        );

        $submittedPhone = preg_replace(
            '/\D/',
            '',
            (string) $request->input('phone')
        );

        if (
            strlen($verifiedPhone) !== 10 ||
            $verifiedPhone !== $submittedPhone
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Pehle mobile number OTP se verify kijiye.',
            ], 422);
        }

        $validated = $request->validate([
            'step' => [
                'required',
                'integer',
                'between:1,3',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'required',
                'digits:10',
                'regex:/^[6-9][0-9]{9}$/',
            ],

            'business_name' => [
                'nullable',
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
                'digits:10',
            ],

            'gstin' => [
                'nullable',
                'string',
                'max:15',
            ],

            'business_type_id' => [
                'nullable',
                'exists:business_types,id',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'state' => [
                'nullable',
                'string',
                'max:255',
            ],

            'state_code' => [
                'nullable',
                'string',
                'max:10',
            ],

            'gst_enabled' => [
                'nullable',
                Rule::in(['0', '1', 0, 1]),
            ],

            'invoice_base_prefix' => [
                'nullable',
                'string',
                'max:100',
            ],

            'rounding_mode' => [
                'nullable',
                Rule::in([
                    'none',
                    'nearest',
                    'up',
                    'down',
                ]),
            ],

            'rounding_step' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'business_skipped' => [
                'nullable',
                Rule::in(['0', '1', 0, 1]),
            ],

            'billing_skipped' => [
                'nullable',
                Rule::in(['0', '1', 0, 1]),
            ],

            'plan_id' => [
                'nullable',
                'exists:plans,id',
            ],
        ]);

        $existingData = session(
            'ad_registration_data',
            []
        );

        $mergedData = array_merge(
            $existingData,
            collect($validated)
                ->except(['step'])
                ->toArray()
        );

        session([
            'ad_registration_data' => $mergedData,
            'ad_registration_step' => (int) $validated['step'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Details temporarily save ho gayi hain.',
            'step' => (int) $validated['step'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Complete registration
    |--------------------------------------------------------------------------
    */

    public function complete(
        Request $request
    ): RedirectResponse {
        $businessSkipped = $request->boolean(
            'business_skipped'
        );

        $billingSkipped = $request->boolean(
            'billing_skipped'
        );

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
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
                'regex:/^[6-9][0-9]{9}$/',
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
                'max:15',
                Rule::unique('businesses', 'gstin'),
            ],

            'business_type_id' => [
                'nullable',
                'required_unless:business_skipped,1',
                'exists:business_types,id',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'state' => [
                'nullable',
                'string',
                'max:255',
            ],

            'state_code' => [
                'nullable',
                'string',
                'max:10',
            ],

            'gst_enabled' => [
                'nullable',
                'required_unless:billing_skipped,1',
                Rule::in(['0', '1', 0, 1]),
            ],

            'invoice_base_prefix' => [
                'nullable',
                'string',
                'max:100',
            ],

            'rounding_mode' => [
                'nullable',
                'required_unless:billing_skipped,1',
                Rule::in([
                    'none',
                    'nearest',
                    'up',
                    'down',
                ]),
            ],

            'rounding_step' => [
                'nullable',
                'required_unless:billing_skipped,1',
                'numeric',
                'min:0',
            ],

            'plan_id' => [
                'nullable',
                'exists:plans,id',
            ],
        ], [
            'name.required' =>
                'Full name required hai.',

            'phone.required' =>
                'Mobile number required hai.',

            'phone.unique' =>
                'Ye mobile number already registered hai.',

            'business_name.required_unless' =>
                'Business details skip nahi kar rahe hain to business name required hai.',

            'mobile.required_unless' =>
                'Business details skip nahi kar rahe hain to business mobile required hai.',

            'business_type_id.required_unless' =>
                'Business details skip nahi kar rahe hain to business type required hai.',

            'gst_enabled.required_unless' =>
                'Billing settings skip nahi kar rahe hain to GST setting required hai.',

            'rounding_mode.required_unless' =>
                'Billing settings skip nahi kar rahe hain to rounding mode required hai.',

            'rounding_step.required_unless' =>
                'Billing settings skip nahi kar rahe hain to rounding value required hai.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Verify OTP session
        |--------------------------------------------------------------------------
        */

        $verifiedPhone = preg_replace(
            '/\D/',
            '',
            (string) session('register_phone_verified')
        );

        $submittedPhone = preg_replace(
            '/\D/',
            '',
            (string) $validated['phone']
        );

        if (
            strlen($verifiedPhone) !== 10 ||
            $verifiedPhone !== $submittedPhone
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'phone' =>
                        'Pehle mobile number OTP se verify kijiye.',
                ]);
        }

        DB::beginTransaction();

        try {
            $name = trim($validated['name']);

            /*
            |--------------------------------------------------------------------------
            | Create user
            |--------------------------------------------------------------------------
            */

            $user = User::create([
                'name' => $name,

                'email' =>
                    $submittedPhone . '@noemail.local',

                'phone' => $submittedPhone,

                'password' => Hash::make(
                    Str::random(40)
                ),

                'phone_verified_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Resolve business type
            |--------------------------------------------------------------------------
            */

            $defaultBusinessTypeId =
                BusinessType::query()
                    ->orderBy('id')
                    ->value('id');

            $businessTypeId = $businessSkipped
                ? $defaultBusinessTypeId
                : ($validated['business_type_id'] ?? null);

            if (!$businessTypeId) {
                throw new \RuntimeException(
                    'Koi business type available nahi hai. Admin panel se ek business type create kijiye.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Business data
            |--------------------------------------------------------------------------
            */

            if ($businessSkipped) {
                $businessName =
                    $name . "'s Business";

                $businessEmail = null;
                $businessGstin = null;
                $businessAddress = null;
                $businessState = null;
                $businessStateCode = null;
            } else {
                $businessName = trim(
                    (string) $validated['business_name']
                );

                $businessEmail = filled(
                    $validated['business_email'] ?? null
                )
                    ? strtolower(
                        trim($validated['business_email'])
                    )
                    : null;

                $businessGstin = filled(
                    $validated['gstin'] ?? null
                )
                    ? strtoupper(
                        trim($validated['gstin'])
                    )
                    : null;

                $businessAddress = filled(
                    $validated['address'] ?? null
                )
                    ? trim($validated['address'])
                    : null;

                $businessState = filled(
                    $validated['state'] ?? null
                )
                    ? trim($validated['state'])
                    : null;

                $businessStateCode = filled(
                    $validated['state_code'] ?? null
                )
                    ? trim($validated['state_code'])
                    : null;
            }

            /*
            |--------------------------------------------------------------------------
            | Billing data
            |--------------------------------------------------------------------------
            */

            if ($billingSkipped) {
                $gstEnabled = 0;
                $invoicePrefix = 'RV/SL';
                $roundingMode = 'nearest';
                $roundingStep = 1.00;
            } else {
                $gstEnabled = (int) (
                    $validated['gst_enabled'] ?? 0
                );

                $invoicePrefix = filled(
                    $validated['invoice_base_prefix']
                        ?? null
                )
                    ? trim(
                        $validated['invoice_base_prefix']
                    )
                    : 'RV/SL';

                $roundingMode =
                    $validated['rounding_mode']
                    ?? 'nearest';

                $roundingStep = (float) (
                    $validated['rounding_step']
                    ?? 1.00
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Unique business slug
            |--------------------------------------------------------------------------
            */

            $baseSlug = Str::slug($businessName);

            if ($baseSlug === '') {
                $baseSlug =
                    'business-' . $submittedPhone;
            }

            $slug = $baseSlug;
            $suffix = 1;

            while (
                Business::query()
                    ->where('slug', $slug)
                    ->exists()
            ) {
                $slug =
                    $baseSlug . '-' . $suffix;

                $suffix++;
            }

            /*
            |--------------------------------------------------------------------------
            | Create business
            |--------------------------------------------------------------------------
            */

            $business = Business::create([
                'name' => $businessName,
                'slug' => $slug,

                'email' => $businessEmail,
                'mobile' => $submittedPhone,

                'gstin' => $businessGstin,
                'gst_enabled' => $gstEnabled,

                'address' => $businessAddress,
                'state' => $businessState,
                'state_code' => $businessStateCode,

                'business_type_id' =>
                    $businessTypeId,

                'invoice_base_prefix' =>
                    $invoicePrefix,

                'rounding_mode' =>
                    $roundingMode,

                'rounding_step' =>
                    $roundingStep,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Attach owner to business
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
                'current_business_id' =>
                    $business->id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Owner role
            |--------------------------------------------------------------------------
            */

            if (
                method_exists($user, 'assignRole') &&
                Role::query()
                    ->where('name', 'owner')
                    ->where('guard_name', 'web')
                    ->exists()
            ) {
                $user->assignRole('owner');
            }

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Login
            |--------------------------------------------------------------------------
            */

            event(new Registered($user));

            Auth::login($user, true);

            $request->session()->regenerate();

            session([
                'active_business_id' =>
                    $business->id,

                'active_business_name' =>
                    $business->name,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Keep clicked pricing plan only for highlighting
            |--------------------------------------------------------------------------
            |
            | Plan abhi activate nahi hoga.
            | User choose-plan page par plan confirm karega.
            |
            */

            if (!empty($validated['plan_id'])) {
                session([
                    'suggested_plan_id' =>
                        (int) $validated['plan_id'],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Cleanup
            |--------------------------------------------------------------------------
            */

            session()->forget([
                'register_phone_verified',
                'register_phone_otp',
                'register_phone_otp_expires_at',
                'ad_registration_data',
                'ad_registration_step',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Registration → Choose Plan
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('plan.choose')
                ->with(
                    'success',
                    'Registration successful. Ab apna plan choose kijiye.'
                );

        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'registration' =>
                        app()->environment('local')
                            ? $exception->getMessage()
                            : 'Registration complete nahi ho paya. Dobara try kijiye.',
                ]);
        }
    }
}