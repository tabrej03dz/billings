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
use App\Models\OnboardingRegistration;
use App\Models\UserPlan;
use Carbon\Carbon;
use Spatie\Permission\PermissionRegistrar;



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
        'step' => ['required', 'integer', 'between:1,3'],

        'name' => ['required', 'string', 'max:255'],

        'phone' => [
            'required',
            'digits:10',
            'regex:/^[6-9][0-9]{9}$/',
        ],

        'business_name' => ['nullable', 'string', 'max:255'],
        'business_email' => ['nullable', 'email', 'max:255'],
        'mobile' => ['nullable', 'digits:10'],
        'gstin' => ['nullable', 'string', 'max:15'],

        'type' => [
            'nullable',
            'exists:business_types,id',
        ],

        'address' => ['nullable', 'string', 'max:1000'],
        'state' => ['nullable', 'string', 'max:255'],
        'state_code' => ['nullable', 'string', 'max:10'],

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

    $step = (int) $validated['step'];

    $existingSessionData = session(
        'ad_registration_data',
        []
    );

    $mergedData = array_merge(
        $existingSessionData,
        collect($validated)
            ->except('step')
            ->toArray()
    );

    $onboardingRegistration = OnboardingRegistration::query()
        ->updateOrCreate(
            [
                'phone' => $submittedPhone,
            ],
            [
                'name' => trim($mergedData['name']),

                'phone' => $submittedPhone,

                'business_name' => filled(
                    $mergedData['business_name'] ?? null
                )
                    ? trim($mergedData['business_name'])
                    : null,

                'business_email' => filled(
                    $mergedData['business_email'] ?? null
                )
                    ? strtolower(
                        trim($mergedData['business_email'])
                    )
                    : null,

                'mobile' => filled(
                    $mergedData['mobile'] ?? null
                )
                    ? preg_replace(
                        '/\D/',
                        '',
                        $mergedData['mobile']
                    )
                    : $submittedPhone,

                'gstin' => filled(
                    $mergedData['gstin'] ?? null
                )
                    ? strtoupper(
                        trim($mergedData['gstin'])
                    )
                    : null,

                'type' =>
                    $mergedData['type']
                    ?? null,

                'address' => filled(
                    $mergedData['address'] ?? null
                )
                    ? trim($mergedData['address'])
                    : null,

                'state' => filled(
                    $mergedData['state'] ?? null
                )
                    ? trim($mergedData['state'])
                    : null,

                'state_code' => filled(
                    $mergedData['state_code'] ?? null
                )
                    ? trim($mergedData['state_code'])
                    : null,

                'gst_enabled' => (int) (
                    $mergedData['gst_enabled'] ?? 0
                ),

                'invoice_base_prefix' => filled(
                    $mergedData['invoice_base_prefix']
                    ?? null
                )
                    ? strtoupper(
                        trim(
                            $mergedData[
                                'invoice_base_prefix'
                            ]
                        )
                    )
                    : 'RV/SL',

                'rounding_mode' =>
                    $mergedData['rounding_mode']
                    ?? 'nearest',

                'rounding_step' => (float) (
                    $mergedData['rounding_step']
                    ?? 1
                ),

                'plan_id' =>
                    $mergedData['plan_id']
                    ?? null,

                'business_skipped' => (bool) (
                    $mergedData['business_skipped']
                    ?? false
                ),

                'billing_skipped' => (bool) (
                    $mergedData['billing_skipped']
                    ?? false
                ),

                'phone_verified_at' => now(),

                'current_step' => $step,

                'status' => $step >= 3
                    ? 'in_progress'
                    : 'draft',

                'last_activity_at' => now(),
            ]
        );

    session([
        'ad_registration_data' => $mergedData,
        'ad_registration_step' => $step,
        'onboarding_registration_id' =>
            $onboardingRegistration->id,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Details database me save ho gayi hain.',
        'step' => $step,
        'registration_id' => $onboardingRegistration->id,
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | Complete registration
    |--------------------------------------------------------------------------
    */

    // public function complete(Request $request): RedirectResponse
    // {
    //     /*
    //      * Business aur billing details hamesha optional hain.
    //      * Skip hone par incomplete values ko validation se pehle clear/default
    //      * kar diya jayega.
    //      */
    //     $businessSkipped = $request->boolean('business_skipped');
    //     $billingSkipped = $request->boolean('billing_skipped');

    //     if ($businessSkipped) {
    //         $request->merge([
    //             'business_name' => null,
    //             'business_email' => null,
    //             'business_type_id' => null,
    //             'gstin' => null,
    //             'address' => null,
    //             'state' => null,
    //             'state_code' => null,
    //         ]);
    //     }

    //     if ($billingSkipped) {
    //         $request->merge([
    //             'gst_enabled' => 0,
    //             'invoice_base_prefix' => 'RV/SL',
    //             'rounding_mode' => 'nearest',
    //             'rounding_step' => 1.00,
    //         ]);
    //     }

    //     $validated = $request->validate([
    //         'name' => ['required', 'string', 'max:255'],
    //         'phone' => [
    //             'required',
    //             'digits:10',
    //             'regex:/^[6-9][0-9]{9}$/',
    //         ],
    //         'business_name' => ['nullable', 'string', 'max:255'],
    //         'business_email' => ['nullable', 'email', 'max:255'],
    //         'mobile' => ['nullable', 'digits:10'],
    //         'gstin' => [
    //             'nullable',
    //             'string',
    //             'max:15',
    //             Rule::unique('businesses', 'gstin'),
    //         ],
    //         'business_type_id' => [
    //             'nullable',
    //             'exists:business_types,id',
    //         ],
    //         'address' => ['nullable', 'string', 'max:1000'],
    //         'state' => ['nullable', 'string', 'max:255'],
    //         'state_code' => ['nullable', 'string', 'max:10'],
    //         'gst_enabled' => [
    //             'nullable',
    //             Rule::in(['0', '1', 0, 1]),
    //         ],
    //         'invoice_base_prefix' => ['nullable', 'string', 'max:100'],
    //         'rounding_mode' => [
    //             'nullable',
    //             Rule::in(['none', 'nearest', 'up', 'down']),
    //         ],
    //         'rounding_step' => ['nullable', 'numeric', 'min:0'],
    //         'plan_id' => ['nullable', 'exists:plans,id'],
    //     ]);

    //     $verifiedPhone = preg_replace(
    //         '/\D/',
    //         '',
    //         (string) session('register_phone_verified')
    //     );

    //     $submittedPhone = preg_replace(
    //         '/\D/',
    //         '',
    //         (string) $validated['phone']
    //     );

    //     if (
    //         strlen($verifiedPhone) !== 10 ||
    //         $verifiedPhone !== $submittedPhone
    //     ) {
    //         return back()
    //             ->withInput()
    //             ->withErrors([
    //                 'phone' => 'Pehle mobile number OTP se verify kijiye.',
    //             ]);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         $name = trim($validated['name']);

    //         /*
    //          * OTP verify method ne user pehle create kar diya ho to wahi user
    //          * update hoga. Duplicate phone ke karan registration nahi rukega.
    //          */
    //         $user = User::query()
    //             ->where('phone', $submittedPhone)
    //             ->first();

    //         if (!$user) {
    //             $user = new User();
    //             $user->phone = $submittedPhone;
    //             $user->email = $submittedPhone . '@noemail.local';
    //             $user->password = Hash::make(Str::random(40));
    //         }

    //         $user->name = $name;

    //         if (empty($user->email)) {
    //             $user->email = $submittedPhone . '@noemail.local';
    //         }

    //         if (empty($user->password)) {
    //             $user->password = Hash::make(Str::random(40));
    //         }

    //         if (empty($user->phone_verified_at)) {
    //             $user->phone_verified_at = now();
    //         }

    //         $user->save();

    //         /*
    //          * Business type optional hai. Pehla available type default hoga.
    //          */
    //         $defaultBusinessTypeId = BusinessType::query()
    //             ->orderBy('id')
    //             ->value('id');

    //         $businessTypeId =
    //             $validated['business_type_id']
    //             ?? $defaultBusinessTypeId;

    //         if (!$businessTypeId) {
    //             throw new \RuntimeException(
    //                 'Admin panel se kam se kam ek business type create kijiye.'
    //             );
    //         }

    //         /*
    //          * Business name/details blank hon tab bhi default business banega.
    //          */
    //         $businessName = filled($validated['business_name'] ?? null)
    //             ? trim($validated['business_name'])
    //             : $name . "'s Business";

    //         $businessEmail = filled($validated['business_email'] ?? null)
    //             ? strtolower(trim($validated['business_email']))
    //             : null;

    //         $businessGstin = filled($validated['gstin'] ?? null)
    //             ? strtoupper(trim($validated['gstin']))
    //             : null;

    //         $businessAddress = filled($validated['address'] ?? null)
    //             ? trim($validated['address'])
    //             : null;

    //         $businessState = filled($validated['state'] ?? null)
    //             ? trim($validated['state'])
    //             : null;

    //         $businessStateCode = filled($validated['state_code'] ?? null)
    //             ? trim($validated['state_code'])
    //             : null;

    //         $gstEnabled = (int) ($validated['gst_enabled'] ?? 0);

    //         $invoicePrefix = filled(
    //             $validated['invoice_base_prefix'] ?? null
    //         )
    //             ? strtoupper(trim($validated['invoice_base_prefix']))
    //             : 'RV/SL';

    //         $roundingMode =
    //             $validated['rounding_mode'] ?? 'nearest';

    //         $roundingStep =
    //             (float) ($validated['rounding_step'] ?? 1.00);

    //         /*
    //          * User ke paas pehle se business ho to duplicate create na karein.
    //          */
    //         $existingBusinessId = DB::table('business_user')
    //             ->where('user_id', $user->id)
    //             ->value('business_id');

    //         $business = $existingBusinessId
    //             ? Business::find($existingBusinessId)
    //             : null;

    //         if (!$business) {
    //             $baseSlug = Str::slug($businessName);

    //             if ($baseSlug === '') {
    //                 $baseSlug = 'business-' . $submittedPhone;
    //             }

    //             $slug = $baseSlug;
    //             $suffix = 1;

    //             while (
    //                 Business::query()
    //                     ->where('slug', $slug)
    //                     ->exists()
    //             ) {
    //                 $slug = $baseSlug . '-' . $suffix;
    //                 $suffix++;
    //             }

    //             $business = Business::create([
    //                 'name' => $businessName,
    //                 'slug' => $slug,
    //                 'email' => $businessEmail,
    //                 'mobile' => $submittedPhone,
    //                 'gstin' => $businessGstin,
    //                 'gst_enabled' => $gstEnabled,
    //                 'address' => $businessAddress,
    //                 'state' => $businessState,
    //                 'state_code' => $businessStateCode,
    //                 'business_type_id' => $businessTypeId,
    //                 'invoice_base_prefix' => $invoicePrefix,
    //                 'rounding_mode' => $roundingMode,
    //                 'rounding_step' => $roundingStep,
    //             ]);

    //             DB::table('business_user')->insert([
    //                 'business_id' => $business->id,
    //                 'user_id' => $user->id,
    //                 'role' => 'owner',
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    //         }

    //         $user->current_business_id = $business->id;
    //         $user->save();

    //         if (
    //             method_exists($user, 'assignRole') &&
    //             Role::query()
    //                 ->where('name', 'owner')
    //                 ->where('guard_name', 'web')
    //                 ->exists() &&
    //             !$user->hasRole('owner')
    //         ) {
    //             $user->assignRole('owner');
    //         }

    //         DB::commit();

    //         event(new Registered($user));

    //         Auth::login($user, true);
    //         $request->session()->regenerate();

    //         session([
    //             'active_business_id' => $business->id,
    //             'active_business_name' => $business->name,
    //         ]);

    //         if (!empty($validated['plan_id'])) {
    //             session([
    //                 'suggested_plan_id' => (int) $validated['plan_id'],
    //             ]);
    //         }

    //         $request->session()->forget([
    //             'register_phone_verified',
    //             'register_phone_otp',
    //             'register_phone_otp_expires_at',
    //             'ad_registration_data',
    //             'ad_registration_step',
    //         ]);

    //         /*
    //          * Registration ke baad har haal me choose plan.
    //          */
    //         return redirect()
    //             ->route('plan.choose')
    //             ->with(
    //                 'success',
    //                 'Registration successful. Ab apna plan choose kijiye.'
    //             );

    //     } catch (Throwable $exception) {
    //         DB::rollBack();

    //         report($exception);

    //         return back()
    //             ->withInput()
    //             ->withErrors([
    //                 'registration' => app()->environment('local')
    //                     ? $exception->getMessage()
    //                     : 'Registration complete nahi ho paya. Dobara try kijiye.',
    //             ]);
    //     }
    // }


    // public function complete(Request $request): RedirectResponse
    // {
    //     $businessSkipped = $request->boolean('business_skipped');
    //     $billingSkipped = $request->boolean('billing_skipped');

    //     if ($businessSkipped) {
    //         $request->merge([
    //             'business_name' => null,
    //             'business_email' => null,
    //             'type' => null,
    //             'gstin' => null,
    //             'address' => null,
    //             'state' => null,
    //             'state_code' => null,
    //         ]);
    //     }

    //     if ($billingSkipped) {
    //         $request->merge([
    //             'gst_enabled' => 0,
    //             'invoice_base_prefix' => 'RV/SL',
    //             'rounding_mode' => 'nearest',
    //             'rounding_step' => 1.00,
    //         ]);
    //     }

    //     $validated = $request->validate([
    //         'name' => [
    //             'required',
    //             'string',
    //             'max:255',
    //         ],

    //         'phone' => [
    //             'required',
    //             'digits:10',
    //             'regex:/^[6-9][0-9]{9}$/',
    //         ],

    //         'business_name' => [
    //             'nullable',
    //             'string',
    //             'max:255',
    //         ],

    //         'business_email' => [
    //             'nullable',
    //             'email',
    //             'max:255',
    //         ],

    //         'mobile' => [
    //             'nullable',
    //             'digits:10',
    //         ],

    //         'gstin' => [
    //             'nullable',
    //             'string',
    //             'max:15',
    //             Rule::unique('businesses', 'gstin'),
    //         ],

    //         'type' => [
    //             'nullable',
    //             'exists:business_types,id',
    //         ],

    //         'address' => [
    //             'nullable',
    //             'string',
    //             'max:1000',
    //         ],

    //         'state' => [
    //             'nullable',
    //             'string',
    //             'max:255',
    //         ],

    //         'state_code' => [
    //             'nullable',
    //             'string',
    //             'max:10',
    //         ],

    //         'gst_enabled' => [
    //             'nullable',
    //             Rule::in(['0', '1', 0, 1]),
    //         ],

    //         'invoice_base_prefix' => [
    //             'nullable',
    //             'string',
    //             'max:100',
    //         ],

    //         'rounding_mode' => [
    //             'nullable',
    //             Rule::in([
    //                 'none',
    //                 'nearest',
    //                 'up',
    //                 'down',
    //             ]),
    //         ],

    //         'rounding_step' => [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //         ],

    //         'plan_id' => [
    //             'nullable',
    //             'exists:plans,id',
    //         ],
    //     ]);

    //     $verifiedPhone = preg_replace(
    //         '/\D/',
    //         '',
    //         (string) session('register_phone_verified')
    //     );

    //     $submittedPhone = preg_replace(
    //         '/\D/',
    //         '',
    //         (string) $validated['phone']
    //     );

    //     if (
    //         strlen($verifiedPhone) !== 10 ||
    //         $verifiedPhone !== $submittedPhone
    //     ) {
    //         return back()
    //             ->withInput()
    //             ->withErrors([
    //                 'phone' => 'Pehle mobile number OTP se verify kijiye.',
    //             ]);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         $name = trim($validated['name']);

    //         $user = User::query()
    //             ->where('phone', $submittedPhone)
    //             ->first();

    //         if (!$user) {
    //             $user = new User();
    //             $user->phone = $submittedPhone;
    //             $user->email =
    //                 $submittedPhone . '@noemail.local';
    //             $user->password = Hash::make(
    //                 Str::random(40)
    //             );
    //         }

    //         $user->name = $name;

    //         if (empty($user->email)) {
    //             $user->email =
    //                 $submittedPhone . '@noemail.local';
    //         }

    //         if (empty($user->password)) {
    //             $user->password = Hash::make(
    //                 Str::random(40)
    //             );
    //         }

    //         if (empty($user->phone_verified_at)) {
    //             $user->phone_verified_at = now();
    //         }

    //         $user->save();

    //         $defaultBusinessTypeId =
    //             BusinessType::query()
    //                 ->orderBy('id')
    //                 ->value('id');

    //         $businessTypeId =
    //             $validated['type']
    //             ?? $defaultBusinessTypeId;

    //         if (!$businessTypeId) {
    //             throw new \RuntimeException(
    //                 'Admin panel se kam se kam ek business type create kijiye.'
    //             );
    //         }

    //         $businessName = filled(
    //             $validated['business_name'] ?? null
    //         )
    //             ? trim($validated['business_name'])
    //             : $name . "'s Business";

    //         $businessEmail = filled(
    //             $validated['business_email'] ?? null
    //         )
    //             ? strtolower(
    //                 trim($validated['business_email'])
    //             )
    //             : null;

    //         $businessGstin = filled(
    //             $validated['gstin'] ?? null
    //         )
    //             ? strtoupper(trim($validated['gstin']))
    //             : null;

    //         $businessAddress = filled(
    //             $validated['address'] ?? null
    //         )
    //             ? trim($validated['address'])
    //             : null;

    //         $businessState = filled(
    //             $validated['state'] ?? null
    //         )
    //             ? trim($validated['state'])
    //             : null;

    //         $businessStateCode = filled(
    //             $validated['state_code'] ?? null
    //         )
    //             ? trim($validated['state_code'])
    //             : null;

    //         $gstEnabled = (int) (
    //             $validated['gst_enabled'] ?? 0
    //         );

    //         $invoicePrefix = filled(
    //             $validated['invoice_base_prefix'] ?? null
    //         )
    //             ? strtoupper(
    //                 trim($validated['invoice_base_prefix'])
    //             )
    //             : 'RV/SL';

    //         $roundingMode =
    //             $validated['rounding_mode']
    //             ?? 'nearest';

    //         $roundingStep = (float) (
    //             $validated['rounding_step']
    //             ?? 1.00
    //         );

    //         $existingBusinessId = DB::table(
    //             'business_user'
    //         )
    //             ->where('user_id', $user->id)
    //             ->value('business_id');

    //         $business = $existingBusinessId
    //             ? Business::query()->find(
    //                 $existingBusinessId
    //             )
    //             : null;

    //         if (!$business) {
    //             $baseSlug = Str::slug($businessName);

    //             if ($baseSlug === '') {
    //                 $baseSlug =
    //                     'business-' . $submittedPhone;
    //             }

    //             $slug = $baseSlug;
    //             $suffix = 1;

    //             while (
    //                 Business::query()
    //                     ->where('slug', $slug)
    //                     ->exists()
    //             ) {
    //                 $slug =
    //                     $baseSlug . '-' . $suffix;

    //                 $suffix++;
    //             }

    //             $business = Business::query()->create([
    //                 'name' => $businessName,
    //                 'slug' => $slug,
    //                 'email' => $businessEmail,
    //                 'mobile' => $submittedPhone,
    //                 'gstin' => $businessGstin,
    //                 'gst_enabled' => $gstEnabled,
    //                 'address' => $businessAddress,
    //                 'state' => $businessState,
    //                 'state_code' => $businessStateCode,
    //                 'type' =>
    //                     $businessTypeId,
    //                 'invoice_base_prefix' =>
    //                     $invoicePrefix,
    //                 'rounding_mode' => $roundingMode,
    //                 'rounding_step' => $roundingStep,
    //             ]);

    //             DB::table('business_user')->insert([
    //                 'business_id' => $business->id,
    //                 'user_id' => $user->id,
    //                 'role' => 'owner',
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    //         } else {
    //             /*
    //             * Existing business ho to form ki latest
    //             * details update kar do.
    //             */
    //             $business->update([
    //                 'name' => $businessName,
    //                 'email' => $businessEmail,
    //                 'mobile' => $submittedPhone,
    //                 'gstin' => $businessGstin,
    //                 'gst_enabled' => $gstEnabled,
    //                 'address' => $businessAddress,
    //                 'state' => $businessState,
    //                 'state_code' => $businessStateCode,
    //                 'type' =>
    //                     $businessTypeId,
    //                 'invoice_base_prefix' =>
    //                     $invoicePrefix,
    //                 'rounding_mode' => $roundingMode,
    //                 'rounding_step' => $roundingStep,
    //             ]);
    //         }

    //         $user->current_business_id =
    //             $business->id;

    //         $user->save();

    //         /*
    //         * Onboarding record ko actual User ke
    //         * saath link karna.
    //         */
    //         $onboardingRegistrationId = session(
    //             'onboarding_registration_id'
    //         );

    //         $onboardingRegistration =
    //             $onboardingRegistrationId
    //                 ? OnboardingRegistration::query()
    //                     ->find($onboardingRegistrationId)
    //                 : null;

    //         /*
    //         * Session ID se record na mile to phone
    //         * number ke through record find karo.
    //         */
    //         if (!$onboardingRegistration) {
    //             $onboardingRegistration =
    //                 OnboardingRegistration::query()
    //                     ->where(
    //                         'phone',
    //                         $submittedPhone
    //                     )
    //                     ->latest('id')
    //                     ->first();
    //         }

    //         /*
    //         * Record bilkul na ho to create kar do.
    //         */
    //         if (!$onboardingRegistration) {
    //             $onboardingRegistration =
    //                 new OnboardingRegistration();

    //             $onboardingRegistration->phone =
    //                 $submittedPhone;
    //         }

    //         $onboardingRegistration->user_id =
    //             $user->id;

    //         $onboardingRegistration->name =
    //             $user->name;

    //         $onboardingRegistration->phone =
    //             $submittedPhone;

    //         $onboardingRegistration->phone_verified_at =
    //             $onboardingRegistration->phone_verified_at
    //             ?: now();

    //         /*
    //         * Aapke onboarding listing/controller me
    //         * ye columns use ho rahe hain.
    //         */
    //         $onboardingRegistration->last_completed_step =
    //             3;

    //         $onboardingRegistration->registration_status =
    //             'completed';

    //         $onboardingRegistration->completed_at =
    //             now();

    //         /*
    //         * Business aur billing details JSON me
    //         * preserve karna.
    //         */
    //         $onboardingRegistration->business_data = [
    //             'business_id' => $business->id,
    //             'business_name' => $business->name,
    //             'business_email' => $business->email,
    //             'mobile' => $business->mobile,
    //             'gstin' => $business->gstin,
    //             'type' =>
    //                 $business->type,
    //             'address' => $business->address,
    //             'state' => $business->state,
    //             'state_code' => $business->state_code,
    //         ];

    //         $onboardingRegistration->billing_data = [
    //             'gst_enabled' =>
    //                 (bool) $business->gst_enabled,
    //             'invoice_base_prefix' =>
    //                 $business->invoice_base_prefix,
    //             'rounding_mode' =>
    //                 $business->rounding_mode,
    //             'rounding_step' =>
    //                 $business->rounding_step,
    //         ];

    //         $onboardingRegistration->save();

    //         if (
    //             method_exists($user, 'assignRole') &&
    //             Role::query()
    //                 ->where('name', 'owner')
    //                 ->where('guard_name', 'web')
    //                 ->exists() &&
    //             !$user->hasRole('owner')
    //         ) {
    //             $user->assignRole('owner');
    //         }

    //         DB::commit();

    //         event(new Registered($user));

    //         Auth::login($user, true);

    //         $request->session()->regenerate();

    //         session([
    //             'active_business_id' => $business->id,
    //             'active_business_name' =>
    //                 $business->name,
    //         ]);

    //         if (!empty($validated['plan_id'])) {
    //             session([
    //                 'suggested_plan_id' =>
    //                     (int) $validated['plan_id'],
    //             ]);
    //         }

    //         $request->session()->forget([
    //             'register_phone_verified',
    //             'register_phone_otp',
    //             'register_phone_otp_expires_at',
    //             'ad_registration_data',
    //             'ad_registration_step',
    //             'onboarding_registration_id',
    //         ]);

    //         return redirect()
    //             ->route('plan.choose')
    //             ->with(
    //                 'success',
    //                 'Registration successful. Ab apna plan choose kijiye.'
    //             );

    //     } catch (Throwable $exception) {
    //         DB::rollBack();

    //         report($exception);

    //         return back()
    //             ->withInput()
    //             ->withErrors([
    //                 'registration' =>
    //                     app()->environment('local')
    //                         ? $exception->getMessage()
    //                         : 'Registration complete nahi ho paya. Dobara try kijiye.',
    //             ]);
    //     }
    // }


    // public function complete(Request $request): RedirectResponse
    // {
    //     $planId = $request->integer('plan_id') ?: null;

    //     $isTrial =
    //         (int) $request->input('trial', 0) === 1;

    //     $paymentDone =
    //         session('payment_done') === true ||
    //         (int) $request->input('payment_done', 0) === 1;

    //     $businessSkipped = $request->boolean('business_skipped');
    //     $billingSkipped = $request->boolean('billing_skipped');

    //     if ($businessSkipped) {
    //         $request->merge([
    //             'business_name' => null,
    //             'business_email' => null,
    //             'type' => null,
    //             'gstin' => null,
    //             'address' => null,
    //             'state' => null,
    //             'state_code' => null,
    //         ]);
    //     }

    //     if ($billingSkipped) {
    //         $request->merge([
    //             'gst_enabled' => 0,
    //             'invoice_base_prefix' => 'RV/SL',
    //             'rounding_mode' => 'nearest',
    //             'rounding_step' => 1.00,
    //         ]);
    //     }

    //     $validated = $request->validate([
    //         'name' => [
    //             'required',
    //             'string',
    //             'max:255',
    //         ],

    //         'phone' => [
    //             'required',
    //             'digits:10',
    //             'regex:/^[6-9][0-9]{9}$/',
    //         ],

    //         'business_name' => [
    //             'nullable',
    //             'string',
    //             'max:255',
    //         ],

    //         'business_email' => [
    //             'nullable',
    //             'email',
    //             'max:255',
    //         ],

    //         'mobile' => [
    //             'nullable',
    //             'digits:10',
    //         ],

    //         'gstin' => [
    //             'nullable',
    //             'string',
    //             'max:15',
    //             Rule::unique('businesses', 'gstin'),
    //         ],

    //         'type' => [
    //             'nullable',
    //             'exists:business_types,id',
    //         ],

    //         'address' => [
    //             'nullable',
    //             'string',
    //             'max:1000',
    //         ],

    //         'state' => [
    //             'nullable',
    //             'string',
    //             'max:255',
    //         ],

    //         'state_code' => [
    //             'nullable',
    //             'string',
    //             'max:10',
    //         ],

    //         'gst_enabled' => [
    //             'nullable',
    //             Rule::in(['0', '1', 0, 1]),
    //         ],

    //         'invoice_base_prefix' => [
    //             'nullable',
    //             'string',
    //             'max:100',
    //         ],

    //         'rounding_mode' => [
    //             'nullable',
    //             Rule::in([
    //                 'none',
    //                 'nearest',
    //                 'up',
    //                 'down',
    //             ]),
    //         ],

    //         'rounding_step' => [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //         ],

    //         'plan_id' => [
    //             'nullable',
    //             'exists:plans,id',
    //         ],

    //         'trial' => [
    //             'nullable',
    //             Rule::in(['0', '1', 0, 1]),
    //         ],

    //         'payment_done' => [
    //             'nullable',
    //             Rule::in(['0', '1', 0, 1]),
    //         ],
    //     ]);

    //     $planId = !empty($validated['plan_id'])
    //         ? (int) $validated['plan_id']
    //         : null;

    //     $verifiedPhone = preg_replace(
    //         '/\D/',
    //         '',
    //         (string) session('register_phone_verified')
    //     );

    //     $submittedPhone = preg_replace(
    //         '/\D/',
    //         '',
    //         (string) $validated['phone']
    //     );

    //     if (
    //         strlen($verifiedPhone) !== 10 ||
    //         $verifiedPhone !== $submittedPhone
    //     ) {
    //         return back()
    //             ->withInput()
    //             ->withErrors([
    //                 'phone' => 'Pehle mobile number OTP se verify kijiye.',
    //             ]);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         $name = trim($validated['name']);

    //         $user = User::query()
    //             ->where('phone', $submittedPhone)
    //             ->first();

    //         if (!$user) {
    //             $user = new User();
    //             $user->phone = $submittedPhone;
    //             $user->email =
    //                 $submittedPhone . '@noemail.local';
    //             $user->password = Hash::make(
    //                 Str::random(40)
    //             );
    //         }

    //         $user->name = $name;

    //         if (empty($user->email)) {
    //             $user->email =
    //                 $submittedPhone . '@noemail.local';
    //         }

    //         if (empty($user->password)) {
    //             $user->password = Hash::make(
    //                 Str::random(40)
    //             );
    //         }

    //         if (empty($user->phone_verified_at)) {
    //             $user->phone_verified_at = now();
    //         }

    //         $user->save();

    //         $defaultBusinessTypeId =
    //             BusinessType::query()
    //                 ->orderBy('id')
    //                 ->value('id');

    //         $businessTypeId =
    //             $validated['type']
    //             ?? $defaultBusinessTypeId;

    //         if (!$businessTypeId) {
    //             throw new \RuntimeException(
    //                 'Admin panel se kam se kam ek business type create kijiye.'
    //             );
    //         }

    //         $businessName = filled(
    //             $validated['business_name'] ?? null
    //         )
    //             ? trim($validated['business_name'])
    //             : $name . "'s Business";

    //         $businessEmail = filled(
    //             $validated['business_email'] ?? null
    //         )
    //             ? strtolower(
    //                 trim($validated['business_email'])
    //             )
    //             : null;

    //         $businessGstin = filled(
    //             $validated['gstin'] ?? null
    //         )
    //             ? strtoupper(trim($validated['gstin']))
    //             : null;

    //         $businessAddress = filled(
    //             $validated['address'] ?? null
    //         )
    //             ? trim($validated['address'])
    //             : null;

    //         $businessState = filled(
    //             $validated['state'] ?? null
    //         )
    //             ? trim($validated['state'])
    //             : null;

    //         $businessStateCode = filled(
    //             $validated['state_code'] ?? null
    //         )
    //             ? trim($validated['state_code'])
    //             : null;

    //         $gstEnabled = (int) (
    //             $validated['gst_enabled'] ?? 0
    //         );

    //         $invoicePrefix = filled(
    //             $validated['invoice_base_prefix'] ?? null
    //         )
    //             ? strtoupper(
    //                 trim($validated['invoice_base_prefix'])
    //             )
    //             : 'RV/SL';

    //         $roundingMode =
    //             $validated['rounding_mode']
    //             ?? 'nearest';

    //         $roundingStep = (float) (
    //             $validated['rounding_step']
    //             ?? 1.00
    //         );

    //         $existingBusinessId = DB::table(
    //             'business_user'
    //         )
    //             ->where('user_id', $user->id)
    //             ->value('business_id');

    //         $business = $existingBusinessId
    //             ? Business::query()->find(
    //                 $existingBusinessId
    //             )
    //             : null;

    //         if (!$business) {
    //             $baseSlug = Str::slug($businessName);

    //             if ($baseSlug === '') {
    //                 $baseSlug =
    //                     'business-' . $submittedPhone;
    //             }

    //             $slug = $baseSlug;
    //             $suffix = 1;

    //             while (
    //                 Business::query()
    //                     ->where('slug', $slug)
    //                     ->exists()
    //             ) {
    //                 $slug =
    //                     $baseSlug . '-' . $suffix;

    //                 $suffix++;
    //             }

    //             $business = Business::query()->create([
    //                 'name' => $businessName,
    //                 'slug' => $slug,
    //                 'email' => $businessEmail,
    //                 'mobile' => $submittedPhone,
    //                 'gstin' => $businessGstin,
    //                 'gst_enabled' => $gstEnabled,
    //                 'address' => $businessAddress,
    //                 'state' => $businessState,
    //                 'state_code' => $businessStateCode,
    //                 'type' =>
    //                     $businessTypeId,
    //                 'invoice_base_prefix' =>
    //                     $invoicePrefix,
    //                 'rounding_mode' => $roundingMode,
    //                 'rounding_step' => $roundingStep,
    //             ]);

    //             DB::table('business_user')->insert([
    //                 'business_id' => $business->id,
    //                 'user_id' => $user->id,
    //                 'role' => 'owner',
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    //         } else {
    //             /*
    //             * Existing business ho to form ki latest
    //             * details update kar do.
    //             */
    //             $business->update([
    //                 'name' => $businessName,
    //                 'email' => $businessEmail,
    //                 'mobile' => $submittedPhone,
    //                 'gstin' => $businessGstin,
    //                 'gst_enabled' => $gstEnabled,
    //                 'address' => $businessAddress,
    //                 'state' => $businessState,
    //                 'state_code' => $businessStateCode,
    //                 'type' =>
    //                     $businessTypeId,
    //                 'invoice_base_prefix' =>
    //                     $invoicePrefix,
    //                 'rounding_mode' => $roundingMode,
    //                 'rounding_step' => $roundingStep,
    //             ]);
    //         }

    //         $user->current_business_id =
    //             $business->id;

    //         $user->save();

    //         /*
    //         * Onboarding record ko actual User ke
    //         * saath link karna.
    //         */
    //         $onboardingRegistrationId = session(
    //             'onboarding_registration_id'
    //         );

    //         $onboardingRegistration =
    //             $onboardingRegistrationId
    //                 ? OnboardingRegistration::query()
    //                     ->find($onboardingRegistrationId)
    //                 : null;

    //         /*
    //         * Session ID se record na mile to phone
    //         * number ke through record find karo.
    //         */
    //         if (!$onboardingRegistration) {
    //             $onboardingRegistration =
    //                 OnboardingRegistration::query()
    //                     ->where(
    //                         'phone',
    //                         $submittedPhone
    //                     )
    //                     ->latest('id')
    //                     ->first();
    //         }

    //         /*
    //         * Record bilkul na ho to create kar do.
    //         */
    //         if (!$onboardingRegistration) {
    //             $onboardingRegistration =
    //                 new OnboardingRegistration();

    //             $onboardingRegistration->phone =
    //                 $submittedPhone;
    //         }

    //         $onboardingRegistration->user_id =
    //             $user->id;

    //         $onboardingRegistration->name =
    //             $user->name;

    //         $onboardingRegistration->phone =
    //             $submittedPhone;

    //         $onboardingRegistration->phone_verified_at =
    //             $onboardingRegistration->phone_verified_at
    //             ?: now();

    //         /*
    //         * Aapke onboarding listing/controller me
    //         * ye columns use ho rahe hain.
    //         */
    //         $onboardingRegistration->last_completed_step =
    //             3;

    //         $onboardingRegistration->registration_status =
    //             'completed';

    //         $onboardingRegistration->completed_at =
    //             now();

    //         /*
    //         * Business aur billing details JSON me
    //         * preserve karna.
    //         */
    //         $onboardingRegistration->business_data = [
    //             'business_id' => $business->id,
    //             'business_name' => $business->name,
    //             'business_email' => $business->email,
    //             'mobile' => $business->mobile,
    //             'gstin' => $business->gstin,
    //             'type' =>
    //                 $business->type,
    //             'address' => $business->address,
    //             'state' => $business->state,
    //             'state_code' => $business->state_code,
    //         ];

    //         $onboardingRegistration->billing_data = [
    //             'gst_enabled' =>
    //                 (bool) $business->gst_enabled,
    //             'invoice_base_prefix' =>
    //                 $business->invoice_base_prefix,
    //             'rounding_mode' =>
    //                 $business->rounding_mode,
    //             'rounding_step' =>
    //                 $business->rounding_step,
    //         ];

    //         $onboardingRegistration->save();

    //         if (
    //             method_exists($user, 'assignRole') &&
    //             Role::query()
    //                 ->where('name', 'owner')
    //                 ->where('guard_name', 'web')
    //                 ->exists() &&
    //             !$user->hasRole('owner')
    //         ) {
    //             $user->assignRole('owner');
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Selected plan activation
    //         |--------------------------------------------------------------------------
    //         |
    //         | Trial ya completed payment ke case me wahi selected plan
    //         | registration ke saath activate hoga.
    //         |
    //         */

    //         if ($planId && ($paymentDone || $isTrial)) {
    //             $plan = Plan::query()
    //                 ->with('permissions')
    //                 ->findOrFail($planId);

    //             UserPlan::query()
    //                 ->where('business_id', $business->id)
    //                 ->where('status', 1)
    //                 ->update([
    //                     'status' => 0,
    //                 ]);

    //             UserPlan::query()->create([
    //                 'business_id' => $business->id,
    //                 'user_id' => $user->id,
    //                 'plan_id' => $plan->id,
    //                 'start_date' => Carbon::today(),
    //                 'expiry_date' => Carbon::today()->addDays(
    //                     (int) ($plan->duration_days ?? 30)
    //                 ),
    //                 'status' => 1,
    //             ]);

    //             app(PermissionRegistrar::class)
    //                 ->forgetCachedPermissions();

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

    //         Auth::login($user, true);

    //         $request->session()->regenerate();

    //         session([
    //             'active_business_id' => $business->id,
    //             'active_business_name' =>
    //                 $business->name,
    //         ]);

    //         $request->session()->forget([
    //             'register_phone_verified',
    //             'register_phone_otp',
    //             'register_phone_otp_expires_at',
    //             'ad_registration_data',
    //             'ad_registration_step',
    //             'onboarding_registration_id',
    //         ]);

    //         $registrationMessage =
    //             'Registration successful.';

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Paid selected plan
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($planId && $paymentDone) {
    //             $request->session()->forget([
    //                 'paid_plan_id',
    //                 'paid_razorpay_order_id',
    //                 'paid_razorpay_payment_id',
    //                 'paid_razorpay_signature',
    //                 'payment_done',
    //                 'paid_name',
    //                 'paid_email',
    //             ]);

    //             return redirect()
    //                 ->route('business-profile.index')
    //                 ->with(
    //                     'success',
    //                     $registrationMessage .
    //                     ' Selected plan activate ho gaya hai. Ab bill template choose kijiye.'
    //                 );
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Trial selected plan
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($planId && $isTrial) {
    //             return redirect()
    //                 ->route('business-profile.index')
    //                 ->with(
    //                     'success',
    //                     $registrationMessage .
    //                     ' Free trial plan activate ho gaya hai. Ab bill template choose kijiye.'
    //                 );
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Selected plan without payment
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($planId) {
    //             return redirect()
    //                 ->route('plan.payment', [
    //                     'plan' => $planId,
    //                     'trial' => 0,
    //                 ])
    //                 ->with(
    //                     'success',
    //                     $registrationMessage .
    //                     ' Selected plan ka payment complete kijiye.'
    //                 );
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | No selected plan
    //         |--------------------------------------------------------------------------
    //         */

    //         return redirect()
    //             ->route('plan.choose')
    //             ->with(
    //                 'success',
    //                 $registrationMessage .
    //                 ' Ab apna plan choose kijiye.'
    //             );

    //     } catch (Throwable $exception) {
    //         DB::rollBack();

    //         report($exception);

    //         return back()
    //             ->withInput()
    //             ->withErrors([
    //                 'registration' =>
    //                     app()->environment('local')
    //                         ? $exception->getMessage()
    //                         : 'Registration complete nahi ho paya. Dobara try kijiye.',
    //             ]);
    //     }
    // }

//     public function complete(Request $request): RedirectResponse
// {
//     $planId = $request->integer('plan_id') ?: null;

//     $isTrial =
//         (int) $request->input('trial', 0) === 1;

//     $paymentDone =
//         session('payment_done') === true ||
//         (int) $request->input('payment_done', 0) === 1;

//     $businessSkipped =
//         $request->boolean('business_skipped');

//     $billingSkipped =
//         $request->boolean('billing_skipped');

//     /*
//     |--------------------------------------------------------------------------
//     | Business details check
//     |--------------------------------------------------------------------------
//     |
//     | Business skip kiya ho ya saari business fields blank hon,
//     | to abhi business create nahi hoga.
//     |
//     */

//     $hasBusinessDetails =
//         filled($request->input('business_name')) ||
//         filled($request->input('business_email')) ||
//         filled($request->input('type')) ||
//         filled($request->input('gstin')) ||
//         filled($request->input('address')) ||
//         filled($request->input('state')) ||
//         filled($request->input('state_code'));

//     $shouldCreateBusiness =
//         !$businessSkipped && $hasBusinessDetails;

//     if (!$shouldCreateBusiness) {
//         $businessSkipped = true;

//         $request->merge([
//             'business_skipped' => 1,
//             'business_name' => null,
//             'business_email' => null,
//             'mobile' => null,
//             'type' => null,
//             'gstin' => null,
//             'address' => null,
//             'state' => null,
//             'state_code' => null,
//         ]);
//     }

//     if ($billingSkipped) {
//         $request->merge([
//             'gst_enabled' => 0,
//             'invoice_base_prefix' => 'RV/SL',
//             'rounding_mode' => 'nearest',
//             'rounding_step' => 1.00,
//         ]);
//     }

//     $validated = $request->validate([
//         'name' => [
//             'required',
//             'string',
//             'max:255',
//         ],

//         'phone' => [
//             'required',
//             'digits:10',
//             'regex:/^[6-9][0-9]{9}$/',
//         ],

//         'business_skipped' => [
//             'nullable',
//             'boolean',
//         ],

//         'billing_skipped' => [
//             'nullable',
//             'boolean',
//         ],

//         'business_name' => [
//             'nullable',
//             'string',
//             'max:255',
//         ],

//         'business_email' => [
//             'nullable',
//             'email',
//             'max:255',
//         ],

//         'mobile' => [
//             'nullable',
//             'digits:10',
//         ],

//         'gstin' => [
//             'nullable',
//             'string',
//             'max:15',
//             Rule::unique('businesses', 'gstin'),
//         ],

//         'type' => [
//             'nullable',
//             'exists:business_types,id',
//         ],

//         'address' => [
//             'nullable',
//             'string',
//             'max:1000',
//         ],

//         'state' => [
//             'nullable',
//             'string',
//             'max:255',
//         ],

//         'state_code' => [
//             'nullable',
//             'string',
//             'max:10',
//         ],

//         'gst_enabled' => [
//             'nullable',
//             Rule::in(['0', '1', 0, 1]),
//         ],

//         'invoice_base_prefix' => [
//             'nullable',
//             'string',
//             'max:100',
//         ],

//         'rounding_mode' => [
//             'nullable',
//             Rule::in([
//                 'none',
//                 'nearest',
//                 'up',
//                 'down',
//             ]),
//         ],

//         'rounding_step' => [
//             'nullable',
//             'numeric',
//             'min:0',
//         ],

//         'plan_id' => [
//             'nullable',
//             'exists:plans,id',
//         ],

//         'trial' => [
//             'nullable',
//             Rule::in(['0', '1', 0, 1]),
//         ],

//         'payment_done' => [
//             'nullable',
//             Rule::in(['0', '1', 0, 1]),
//         ],
//     ]);

//     $planId = !empty($validated['plan_id'])
//         ? (int) $validated['plan_id']
//         : null;

//     $verifiedPhone = preg_replace(
//         '/\D/',
//         '',
//         (string) session('register_phone_verified')
//     );

//     $submittedPhone = preg_replace(
//         '/\D/',
//         '',
//         (string) $validated['phone']
//     );

//     if (
//         strlen($verifiedPhone) !== 10 ||
//         $verifiedPhone !== $submittedPhone
//     ) {
//         return back()
//             ->withInput()
//             ->withErrors([
//                 'phone' =>
//                     'Pehle mobile number OTP se verify kijiye.',
//             ]);
//     }

//     DB::beginTransaction();

//     try {
//         $name = trim($validated['name']);

//         $user = User::query()
//             ->where('phone', $submittedPhone)
//             ->first();

//         if (!$user) {
//             $user = new User();
//             $user->phone = $submittedPhone;
//             $user->email =
//                 $submittedPhone . '@noemail.local';
//             $user->password = Hash::make(
//                 Str::random(40)
//             );
//         }

//         $user->name = $name;

//         if (empty($user->email)) {
//             $user->email =
//                 $submittedPhone . '@noemail.local';
//         }

//         if (empty($user->password)) {
//             $user->password = Hash::make(
//                 Str::random(40)
//             );
//         }

//         if (empty($user->phone_verified_at)) {
//             $user->phone_verified_at = now();
//         }

//         $user->save();

//         $business = null;

//         /*
//         |--------------------------------------------------------------------------
//         | Business create/update
//         |--------------------------------------------------------------------------
//         |
//         | Sirf tab chalega jab user ne business skip nahi kiya aur
//         | kam se kam ek business detail bhari hai.
//         |
//         */

//         if ($shouldCreateBusiness) {
//             $defaultBusinessTypeId =
//                 BusinessType::query()
//                     ->orderBy('id')
//                     ->value('id');

//             $businessTypeId =
//                 $validated['type']
//                 ?? $defaultBusinessTypeId;

//             if (!$businessTypeId) {
//                 throw new \RuntimeException(
//                     'Admin panel se kam se kam ek business type create kijiye.'
//                 );
//             }

//             $businessName = filled(
//                 $validated['business_name'] ?? null
//             )
//                 ? trim($validated['business_name'])
//                 : $name . "'s Business";

//             $businessEmail = filled(
//                 $validated['business_email'] ?? null
//             )
//                 ? strtolower(
//                     trim($validated['business_email'])
//                 )
//                 : null;

//             $businessGstin = filled(
//                 $validated['gstin'] ?? null
//             )
//                 ? strtoupper(
//                     trim($validated['gstin'])
//                 )
//                 : null;

//             $businessAddress = filled(
//                 $validated['address'] ?? null
//             )
//                 ? trim($validated['address'])
//                 : null;

//             $businessState = filled(
//                 $validated['state'] ?? null
//             )
//                 ? trim($validated['state'])
//                 : null;

//             $businessStateCode = filled(
//                 $validated['state_code'] ?? null
//             )
//                 ? trim($validated['state_code'])
//                 : null;

//             $gstEnabled = (int) (
//                 $validated['gst_enabled'] ?? 0
//             );

//             $invoicePrefix = filled(
//                 $validated['invoice_base_prefix'] ?? null
//             )
//                 ? strtoupper(
//                     trim($validated['invoice_base_prefix'])
//                 )
//                 : 'RV/SL';

//             $roundingMode =
//                 $validated['rounding_mode']
//                 ?? 'nearest';

//             $roundingStep = (float) (
//                 $validated['rounding_step']
//                 ?? 1.00
//             );

//             $existingBusinessId = DB::table(
//                 'business_user'
//             )
//                 ->where('user_id', $user->id)
//                 ->value('business_id');

//             $business = $existingBusinessId
//                 ? Business::query()->find(
//                     $existingBusinessId
//                 )
//                 : null;

//             if (!$business) {
//                 $baseSlug = Str::slug($businessName);

//                 if ($baseSlug === '') {
//                     $baseSlug =
//                         'business-' . $submittedPhone;
//                 }

//                 $slug = $baseSlug;
//                 $suffix = 1;

//                 while (
//                     Business::query()
//                         ->where('slug', $slug)
//                         ->exists()
//                 ) {
//                     $slug =
//                         $baseSlug . '-' . $suffix;

//                     $suffix++;
//                 }

//                 $business = Business::query()->create([
//                     'name' => $businessName,
//                     'slug' => $slug,
//                     'email' => $businessEmail,
//                     'mobile' => $submittedPhone,
//                     'gstin' => $businessGstin,
//                     'gst_enabled' => $gstEnabled,
//                     'address' => $businessAddress,
//                     'state' => $businessState,
//                     'state_code' => $businessStateCode,
//                     'type' => $businessTypeId,
//                     'invoice_base_prefix' =>
//                         $invoicePrefix,
//                     'rounding_mode' => $roundingMode,
//                     'rounding_step' => $roundingStep,
//                 ]);

//                 DB::table('business_user')->insert([
//                     'business_id' => $business->id,
//                     'user_id' => $user->id,
//                     'role' => 'owner',
//                     'created_at' => now(),
//                     'updated_at' => now(),
//                 ]);
//             } else {
//                 $business->update([
//                     'name' => $businessName,
//                     'email' => $businessEmail,
//                     'mobile' => $submittedPhone,
//                     'gstin' => $businessGstin,
//                     'gst_enabled' => $gstEnabled,
//                     'address' => $businessAddress,
//                     'state' => $businessState,
//                     'state_code' => $businessStateCode,
//                     'type' => $businessTypeId,
//                     'invoice_base_prefix' =>
//                         $invoicePrefix,
//                     'rounding_mode' => $roundingMode,
//                     'rounding_step' => $roundingStep,
//                 ]);
//             }

//             $user->current_business_id =
//                 $business->id;

//             $user->save();
//         } else {
//             /*
//             * Business skip hua hai, isliye kisi purane/temporary
//             * business ko current business mat banao.
//             */
//             $user->current_business_id = null;
//             $user->save();
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Onboarding registration
//         |--------------------------------------------------------------------------
//         */

//         $onboardingRegistrationId = session(
//             'onboarding_registration_id'
//         );

//         $onboardingRegistration =
//             $onboardingRegistrationId
//                 ? OnboardingRegistration::query()
//                     ->find($onboardingRegistrationId)
//                 : null;

//         if (!$onboardingRegistration) {
//             $onboardingRegistration =
//                 OnboardingRegistration::query()
//                     ->where('phone', $submittedPhone)
//                     ->latest('id')
//                     ->first();
//         }

//         if (!$onboardingRegistration) {
//             $onboardingRegistration =
//                 new OnboardingRegistration();

//             $onboardingRegistration->phone =
//                 $submittedPhone;
//         }

//         $onboardingRegistration->user_id =
//             $user->id;

//         $onboardingRegistration->name =
//             $user->name;

//         $onboardingRegistration->phone =
//             $submittedPhone;

//         $onboardingRegistration->phone_verified_at =
//             $onboardingRegistration->phone_verified_at
//             ?: now();

//         /*
//         * Business pending hone par registration ko fully completed
//         * mark nahi kar rahe. Business profile submit hone ke baad
//         * aap ise completed kar sakte hain.
//         */
//         $onboardingRegistration->last_completed_step =
//             $business ? 3 : 1;

//         $onboardingRegistration->registration_status =
//             $business
//                 ? 'completed'
//                 : 'business_pending';

//         $onboardingRegistration->completed_at =
//             $business ? now() : null;

//         $onboardingRegistration->business_data =
//             $business
//                 ? [
//                     'business_id' => $business->id,
//                     'business_name' => $business->name,
//                     'business_email' => $business->email,
//                     'mobile' => $business->mobile,
//                     'gstin' => $business->gstin,
//                     'type' => $business->type,
//                     'address' => $business->address,
//                     'state' => $business->state,
//                     'state_code' => $business->state_code,
//                 ]
//                 : null;

//         $onboardingRegistration->billing_data = [
//             'gst_enabled' => (bool) (
//                 $validated['gst_enabled'] ?? 0
//             ),
//             'invoice_base_prefix' =>
//                 $validated['invoice_base_prefix']
//                 ?? 'RV/SL',
//             'rounding_mode' =>
//                 $validated['rounding_mode']
//                 ?? 'nearest',
//             'rounding_step' => (float) (
//                 $validated['rounding_step']
//                 ?? 1.00
//             ),
//         ];

//         $onboardingRegistration->save();

//         if (
//             method_exists($user, 'assignRole') &&
//             Role::query()
//                 ->where('name', 'owner')
//                 ->where('guard_name', 'web')
//                 ->exists() &&
//             !$user->hasRole('owner')
//         ) {
//             $user->assignRole('owner');
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Plan activation
//         |--------------------------------------------------------------------------
//         |
//         | UserPlan ko business_id chahiye, isliye business create hone ke
//         | baad hi plan activate hoga. Business pending ho to selected plan
//         | session me preserve rahega.
//         |
//         */

//         if (
//             $business &&
//             $planId &&
//             ($paymentDone || $isTrial)
//         ) {
//             $plan = Plan::query()
//                 ->with('permissions')
//                 ->findOrFail($planId);

//             UserPlan::query()
//                 ->where('business_id', $business->id)
//                 ->where('status', 1)
//                 ->update([
//                     'status' => 0,
//                 ]);

//             UserPlan::query()->create([
//                 'business_id' => $business->id,
//                 'user_id' => $user->id,
//                 'plan_id' => $plan->id,
//                 'start_date' => Carbon::today(),
//                 'expiry_date' =>
//                     Carbon::today()->addDays(
//                         (int) (
//                             $plan->duration_days ?? 30
//                         )
//                     ),
//                 'status' => 1,
//             ]);

//             app(PermissionRegistrar::class)
//                 ->forgetCachedPermissions();

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

//         Auth::login($user, true);

//         $request->session()->regenerate();

//         /*
//         |--------------------------------------------------------------------------
//         | Business skipped/pending
//         |--------------------------------------------------------------------------
//         */

//         if (!$business) {
//             $request->session()->forget([
//                 'active_business_id',
//                 'active_business_name',
//             ]);

//             session([
//                 'pending_registration_plan_id' =>
//                     $planId,
//                 'pending_registration_trial' =>
//                     $isTrial ? 1 : 0,
//                 'pending_registration_payment_done' =>
//                     $paymentDone ? 1 : 0,
//                 'pending_registration_billing_data' => [
//                     'gst_enabled' => (int) (
//                         $validated['gst_enabled'] ?? 0
//                     ),
//                     'invoice_base_prefix' =>
//                         $validated['invoice_base_prefix']
//                         ?? 'RV/SL',
//                     'rounding_mode' =>
//                         $validated['rounding_mode']
//                         ?? 'nearest',
//                     'rounding_step' => (float) (
//                         $validated['rounding_step']
//                         ?? 1.00
//                     ),
//                 ],
//             ]);
//         } else {
//             session([
//                 'active_business_id' =>
//                     $business->id,
//                 'active_business_name' =>
//                     $business->name,
//             ]);
//         }

//         $request->session()->forget([
//             'register_phone_verified',
//             'register_phone_otp',
//             'register_phone_otp_expires_at',
//             'ad_registration_data',
//             'ad_registration_step',
//             'onboarding_registration_id',
//         ]);

//         $registrationMessage =
//             'Registration successful.';

//         /*
//         |--------------------------------------------------------------------------
//         | Business nahi bana
//         |--------------------------------------------------------------------------
//         */

//         if (!$business) {
//             return redirect()
//                 ->route('business-profile.index')
//                 ->with(
//                     'success',
//                     $registrationMessage .
//                     ' Pehle apni business profile complete kijiye. Selected plan safe rakha gaya hai.'
//                 );
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Paid selected plan
//         |--------------------------------------------------------------------------
//         */

//         if ($planId && $paymentDone) {
//             $request->session()->forget([
//                 'paid_plan_id',
//                 'paid_razorpay_order_id',
//                 'paid_razorpay_payment_id',
//                 'paid_razorpay_signature',
//                 'payment_done',
//                 'paid_name',
//                 'paid_email',
//             ]);

//             return redirect()
//                 ->route('business-profile.index')
//                 ->with(
//                     'success',
//                     $registrationMessage .
//                     ' Selected plan activate ho gaya hai.'
//                 );
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Trial selected plan
//         |--------------------------------------------------------------------------
//         */

//         if ($planId && $isTrial) {
//             return redirect()
//                 ->route('business-profile.index')
//                 ->with(
//                     'success',
//                     $registrationMessage .
//                     ' Free trial plan activate ho gaya hai.'
//                 );
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Selected plan without payment
//         |--------------------------------------------------------------------------
//         */

//         if ($planId) {
//             return redirect()
//                 ->route('plan.payment', [
//                     'plan' => $planId,
//                     'trial' => 0,
//                 ])
//                 ->with(
//                     'success',
//                     $registrationMessage .
//                     ' Selected plan ka payment complete kijiye.'
//                 );
//         }

//         return redirect()
//             ->route('plan.choose')
//             ->with(
//                 'success',
//                 $registrationMessage .
//                 ' Ab apna plan choose kijiye.'
//             );

//     } catch (Throwable $exception) {
//         DB::rollBack();

//         report($exception);

//         return back()
//             ->withInput()
//             ->withErrors([
//                 'registration' =>
//                     app()->environment('local')
//                         ? $exception->getMessage()
//                         : 'Registration complete nahi ho paya. Dobara try kijiye.',
//             ]);
//     }
// }

public function complete(Request $request): RedirectResponse
{
    $planId = $request->integer('plan_id') ?: null;

    $isTrial = (int) $request->input('trial', 0) === 1;

    $paymentDone =
        session('payment_done') === true ||
        (int) $request->input('payment_done', 0) === 1;

    $billingSkipped = $request->boolean('billing_skipped');

    /*
    |--------------------------------------------------------------------------
    | Check actual business details
    |--------------------------------------------------------------------------
    |
    | Business mobile field automatically verified phone se fill hoti hai.
    | Isliye mobile aur billing fields ko business detail nahi maana jayega.
    |
    | User ne in actual business fields me se koi field fill ki ho tabhi
    | business create/update hoga.
    |
    */

    $hasBusinessDetails =
        filled($request->input('business_name')) ||
        filled($request->input('business_email')) ||
        filled($request->input('type')) ||
        filled($request->input('gstin')) ||
        filled($request->input('address')) ||
        filled($request->input('state')) ||
        filled($request->input('state_code'));

    $shouldCreateBusiness = $hasBusinessDetails;
    $businessSkipped = !$shouldCreateBusiness;

    /*
    |--------------------------------------------------------------------------
    | Blank business form
    |--------------------------------------------------------------------------
    |
    | Saari business fields blank hain to request se auto-filled/stale
    | business values clear kar denge. Isse dummy business create nahi hoga.
    |
    */

    if (!$shouldCreateBusiness) {
        $request->merge([
            'business_skipped' => 1,
            'business_name' => null,
            'business_email' => null,
            'mobile' => null,
            'type' => null,
            'gstin' => null,
            'address' => null,
            'state' => null,
            'state_code' => null,
        ]);
    } else {
        $request->merge([
            'business_skipped' => 0,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Default billing values when billing step is skipped
    |--------------------------------------------------------------------------
    */

    if ($billingSkipped) {
        $request->merge([
            'gst_enabled' => 0,
            'invoice_base_prefix' => 'RV/SL',
            'rounding_mode' => 'nearest',
            'rounding_step' => 1.00,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    |
    | Agar user business ki koi bhi detail fill karta hai to Business Name
    | required hoga. Isse "User's Business" jaisa dummy naam nahi banega.
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
            'regex:/^[6-9][0-9]{9}$/',
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
            Rule::requiredIf($shouldCreateBusiness),
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
            Rule::unique('businesses', 'gstin'),
        ],

        'type' => [
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

        'plan_id' => [
            'nullable',
            'exists:plans,id',
        ],

        'trial' => [
            'nullable',
            Rule::in(['0', '1', 0, 1]),
        ],

        'payment_done' => [
            'nullable',
            Rule::in(['0', '1', 0, 1]),
        ],
    ], [
        'business_name.required' =>
            'Business ki koi detail bharne par Business Name bharna zaroori hai.',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Resolve validated plan
    |--------------------------------------------------------------------------
    */

    $planId = !empty($validated['plan_id'])
        ? (int) $validated['plan_id']
        : null;

    /*
    |--------------------------------------------------------------------------
    | Verify submitted phone with OTP verified phone
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
                'phone' => 'Pehle mobile number OTP se verify kijiye.',
            ]);
    }

    DB::beginTransaction();

    try {
        /*
        |--------------------------------------------------------------------------
        | Create or update user
        |--------------------------------------------------------------------------
        */

        $name = trim($validated['name']);

        $user = User::query()
            ->where('phone', $submittedPhone)
            ->first();

        if (!$user) {
            $user = new User();
            $user->phone = $submittedPhone;
            $user->email = $submittedPhone . '@noemail.local';
            $user->password = Hash::make(Str::random(40));
        }

        $user->name = $name;

        if (empty($user->email)) {
            $user->email = $submittedPhone . '@noemail.local';
        }

        if (empty($user->password)) {
            $user->password = Hash::make(Str::random(40));
        }

        if (empty($user->phone_verified_at)) {
            $user->phone_verified_at = now();
        }

        $user->save();

        /*
        |--------------------------------------------------------------------------
        | Business create/update
        |--------------------------------------------------------------------------
        |
        | Business sirf tab create/update hoga jab actual business details
        | fill ki gayi hain.
        |
        */

        $business = null;

        if ($shouldCreateBusiness) {
            $defaultBusinessTypeId = BusinessType::query()
                ->orderBy('id')
                ->value('id');

            $businessTypeId =
                $validated['type'] ?? $defaultBusinessTypeId;

            if (!$businessTypeId) {
                throw new \RuntimeException(
                    'Admin panel se kam se kam ek business type create kijiye.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Prepare business values
            |--------------------------------------------------------------------------
            |
            | Business Name validation ke through required ho chuka hai.
            | Yahan koi dummy/fallback business name use nahi hoga.
            |
            */

            $businessName = trim(
                (string) $validated['business_name']
            );

            $businessEmail = filled(
                $validated['business_email'] ?? null
            )
                ? strtolower(trim($validated['business_email']))
                : null;

            $businessGstin = filled(
                $validated['gstin'] ?? null
            )
                ? strtoupper(trim($validated['gstin']))
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

            $gstEnabled = (int) (
                $validated['gst_enabled'] ?? 0
            );

            $invoicePrefix = filled(
                $validated['invoice_base_prefix'] ?? null
            )
                ? strtoupper(
                    trim($validated['invoice_base_prefix'])
                )
                : 'RV/SL';

            $roundingMode =
                $validated['rounding_mode'] ?? 'nearest';

            $roundingStep = (float) (
                $validated['rounding_step'] ?? 1.00
            );

            /*
            |--------------------------------------------------------------------------
            | Find user's existing business
            |--------------------------------------------------------------------------
            */

            $existingBusinessId = DB::table('business_user')
                ->where('user_id', $user->id)
                ->value('business_id');

            $business = $existingBusinessId
                ? Business::query()->find($existingBusinessId)
                : null;

            /*
            |--------------------------------------------------------------------------
            | Create new business
            |--------------------------------------------------------------------------
            */

            if (!$business) {
                $baseSlug = Str::slug($businessName);

                if ($baseSlug === '') {
                    $baseSlug = 'business-' . $submittedPhone;
                }

                $slug = $baseSlug;
                $suffix = 1;

                while (
                    Business::query()
                        ->where('slug', $slug)
                        ->exists()
                ) {
                    $slug = $baseSlug . '-' . $suffix;
                    $suffix++;
                }

                $business = Business::query()->create([
                    'name' => $businessName,
                    'slug' => $slug,
                    'email' => $businessEmail,
                    'mobile' => $submittedPhone,
                    'gstin' => $businessGstin,
                    'gst_enabled' => $gstEnabled,
                    'address' => $businessAddress,
                    'state' => $businessState,
                    'state_code' => $businessStateCode,
                    'type' => $businessTypeId,
                    'invoice_base_prefix' => $invoicePrefix,
                    'rounding_mode' => $roundingMode,
                    'rounding_step' => $roundingStep,
                ]);

                DB::table('business_user')->insert([
                    'business_id' => $business->id,
                    'user_id' => $user->id,
                    'role' => 'owner',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                /*
                |--------------------------------------------------------------------------
                | Update existing business
                |--------------------------------------------------------------------------
                */

                $business->update([
                    'name' => $businessName,
                    'email' => $businessEmail,
                    'mobile' => $submittedPhone,
                    'gstin' => $businessGstin,
                    'gst_enabled' => $gstEnabled,
                    'address' => $businessAddress,
                    'state' => $businessState,
                    'state_code' => $businessStateCode,

                    /*
                    * Type request me aayi hai to update karo.
                    * Nahi aayi to existing type preserve karo.
                    */
                    'type' => filled($validated['type'] ?? null)
                        ? (int) $validated['type']
                        : $business->type,

                    'invoice_base_prefix' => $invoicePrefix,
                    'rounding_mode' => $roundingMode,
                    'rounding_step' => $roundingStep,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Set active business on user
            |--------------------------------------------------------------------------
            */

            $user->current_business_id = $business->id;
            $user->save();
        }

        /*
        |--------------------------------------------------------------------------
        | Important blank-business behaviour
        |--------------------------------------------------------------------------
        |
        | Business fields blank hone par:
        |
        | 1. Business create nahi hoga.
        | 2. business_user pivot create nahi hoga.
        | 3. Existing user's current_business_id null nahi kiya jayega.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Onboarding registration
        |--------------------------------------------------------------------------
        */

        $onboardingRegistrationId = session(
            'onboarding_registration_id'
        );

        $onboardingRegistration = $onboardingRegistrationId
            ? OnboardingRegistration::query()
                ->find($onboardingRegistrationId)
            : null;

        if (!$onboardingRegistration) {
            $onboardingRegistration =
                OnboardingRegistration::query()
                    ->where('phone', $submittedPhone)
                    ->latest('id')
                    ->first();
        }

        if (!$onboardingRegistration) {
            $onboardingRegistration =
                new OnboardingRegistration();

            $onboardingRegistration->phone =
                $submittedPhone;
        }

        $onboardingRegistration->user_id = $user->id;
        $onboardingRegistration->name = $user->name;
        $onboardingRegistration->phone = $submittedPhone;

        $onboardingRegistration->phone_verified_at =
            $onboardingRegistration->phone_verified_at ?: now();

        /*
        |--------------------------------------------------------------------------
        | Onboarding completion status
        |--------------------------------------------------------------------------
        */

        $onboardingRegistration->last_completed_step =
            $business ? 3 : 1;

        $onboardingRegistration->registration_status =
            $business ? 'completed' : 'business_pending';

        $onboardingRegistration->completed_at =
            $business ? now() : null;

        $onboardingRegistration->business_data = $business
            ? [
                'business_id' => $business->id,
                'business_name' => $business->name,
                'business_email' => $business->email,
                'mobile' => $business->mobile,
                'gstin' => $business->gstin,
                'type' => $business->type,
                'address' => $business->address,
                'state' => $business->state,
                'state_code' => $business->state_code,
            ]
            : null;

        $onboardingRegistration->billing_data = [
            'gst_enabled' => (bool) (
                $validated['gst_enabled'] ?? 0
            ),

            'invoice_base_prefix' =>
                $validated['invoice_base_prefix'] ?? 'RV/SL',

            'rounding_mode' =>
                $validated['rounding_mode'] ?? 'nearest',

            'rounding_step' => (float) (
                $validated['rounding_step'] ?? 1.00
            ),
        ];

        $onboardingRegistration->save();

        /*
        |--------------------------------------------------------------------------
        | Assign owner role
        |--------------------------------------------------------------------------
        */

        if (
            method_exists($user, 'assignRole') &&
            Role::query()
                ->where('name', 'owner')
                ->where('guard_name', 'web')
                ->exists() &&
            !$user->hasRole('owner')
        ) {
            $user->assignRole('owner');
        }

        /*
        |--------------------------------------------------------------------------
        | Activate selected plan
        |--------------------------------------------------------------------------
        |
        | UserPlan me business_id required hai. Isliye selected plan tabhi
        | activate hoga jab business available ho.
        |
        | Business pending hone par selected plan session me preserve rahega.
        |
        */

        if (
            $business &&
            $planId &&
            ($paymentDone || $isTrial)
        ) {
            $plan = Plan::query()
                ->with('permissions')
                ->findOrFail($planId);

            UserPlan::query()
                ->where('business_id', $business->id)
                ->where('status', 1)
                ->update([
                    'status' => 0,
                ]);

            UserPlan::query()->create([
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
        | Login registered user
        |--------------------------------------------------------------------------
        */

        event(new Registered($user));

        Auth::login($user, true);

        $request->session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | Business pending session data
        |--------------------------------------------------------------------------
        |
        | Business create nahi hua hai to selected plan, payment/trial aur
        | billing settings ko session me preserve karenge.
        |
        */

        if (!$business) {
            $request->session()->forget([
                'active_business_id',
                'active_business_name',
            ]);

            session([
                'pending_registration_plan_id' => $planId,

                'pending_registration_trial' =>
                    $isTrial ? 1 : 0,

                'pending_registration_payment_done' =>
                    $paymentDone ? 1 : 0,

                'pending_registration_billing_data' => [
                    'gst_enabled' => (int) (
                        $validated['gst_enabled'] ?? 0
                    ),

                    'invoice_base_prefix' =>
                        $validated['invoice_base_prefix']
                        ?? 'RV/SL',

                    'rounding_mode' =>
                        $validated['rounding_mode']
                        ?? 'nearest',

                    'rounding_step' => (float) (
                        $validated['rounding_step'] ?? 1.00
                    ),
                ],
            ]);
        } else {
            session([
                'active_business_id' => $business->id,
                'active_business_name' => $business->name,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Registration session cleanup
        |--------------------------------------------------------------------------
        */

        $request->session()->forget([
            'register_phone_verified',
            'register_phone_otp',
            'register_phone_otp_expires_at',
            'ad_registration_data',
            'ad_registration_step',
            'onboarding_registration_id',
        ]);

        $registrationMessage = 'Registration successful.';

        /*
        |--------------------------------------------------------------------------
        | Redirect 1: First choose plan
        |--------------------------------------------------------------------------
        |
        | Ye condition business pending condition se pehle honi zaroori hai.
        |
        | Plan registration form se select nahi hua hai to pehle plan choose
        | page open hoga. Business blank hone ke karan plan choose bypass
        | nahi hoga.
        |
        */

        if (!$planId) {
            return redirect()
                ->route('plan.choose')
                ->with(
                    'success',
                    $registrationMessage .
                    ' Ab apna plan choose kijiye.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Redirect 2: Plan selected but business profile pending
        |--------------------------------------------------------------------------
        |
        | Plan select ho chuka hai, lekin business fields blank thi.
        | Dummy business create nahi hoga. Ab business profile fill karwayenge.
        |
        */

        if (!$business) {
            return redirect()
                ->route('business-profile.index')
                ->with(
                    'success',
                    $registrationMessage .
                    ' Plan select ho gaya hai. Ab apni business profile complete kijiye.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Redirect 3: Paid plan activated
        |--------------------------------------------------------------------------
        */

        if ($paymentDone) {
            $request->session()->forget([
                'paid_plan_id',
                'paid_razorpay_order_id',
                'paid_razorpay_payment_id',
                'paid_razorpay_signature',
                'payment_done',
                'paid_name',
                'paid_email',
            ]);

            return redirect()
                ->route('business-profile.index')
                ->with(
                    'success',
                    $registrationMessage .
                    ' Selected plan activate ho gaya hai.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Redirect 4: Trial plan activated
        |--------------------------------------------------------------------------
        */

        if ($isTrial) {
            return redirect()
                ->route('business-profile.index')
                ->with(
                    'success',
                    $registrationMessage .
                    ' Free trial plan activate ho gaya hai.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Redirect 5: Selected plan payment
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('plan.payment', [
                'plan' => $planId,
                'trial' => 0,
            ])
            ->with(
                'success',
                $registrationMessage .
                ' Selected plan ka payment complete kijiye.'
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