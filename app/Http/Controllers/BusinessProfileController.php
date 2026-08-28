<?php

namespace App\Http\Controllers;

use App\Models\BillTemplate;
use App\Models\Business;
use App\Models\BusinessType;
use App\Models\Item;
use App\Models\Plan;
use App\Models\UserPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;

class BusinessProfileController extends Controller
{
    /**
     * Business profile form show karega.
     */
    public function index(Request $request)
    {
        $business = $this->resolveBusiness($request, false);

        if ($business) {
            $business->refreshProfileCompletion();
            $business->refresh();

            $missingFields = $business->missingProfileFields();

            $itemCount = Item::query()
                ->where('business_id', $business->id)
                ->count();
        } else {
            $business = new Business([
                'name' => null,
                'email' => null,
                'mobile' => $request->user()?->phone,
                'gst_enabled' => false,
                'invoice_base_prefix' => 'RV/SL',
                'rounding_mode' => 'nearest',
                'rounding_step' => 1,
                'profile_completion' => 0,
            ]);

            $business->id = null;
            $business->profile_completion = 0;

            $missingFields = [
                'Business Name',
                'Business Type',
                'Mobile Number',
                'Business Address',
            ];

            $itemCount = 0;
        }

        $businessTypes = BusinessType::query()
            ->orderBy('name')
            ->get();

        $billTemplates = BillTemplate::query()
            ->orderBy('name')
            ->get();

        return view(
            'business-profile.index',
            compact(
                'business',
                'businessTypes',
                'billTemplates',
                'missingFields',
                'itemCount'
            )
        );
    }

    /**
     * Business profile create/update.
     *
     * IMPORTANT:
     * - businesses.type me BusinessType ID save hoti hai.
     * - businesses.pdf_template_id me BillTemplate ID save hoti hai.
     * - Existing business me blank/missing type ya template existing value ko
     *   null nahi karega.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        abort_unless(
            $user,
            401,
            'Please login to continue.'
        );

        $business = $this->resolveBusiness($request, false);

        $isCreating = !$business;

        if ($isCreating) {
            $business = new Business();
        }

        /*
        |--------------------------------------------------------------------------
        | Protect Type / Bill Template From Accidental NULL
        |--------------------------------------------------------------------------
        |
        | HTML select ya API se "", null ya "null" aa sakta hai.
        | Existing business ke case me in values ko request se hata denge,
        | taki old type/template preserve rahe.
        |
        */

        if (!$isCreating) {
            foreach (['type', 'pdf_template_id'] as $field) {
                if (!$request->exists($field)) {
                    continue;
                }

                $value = $request->input($field);

                $isNullLike =
                    $value === null
                    || $value === ''
                    || (
                        is_string($value)
                        && strtolower(trim($value)) === 'null'
                    );

                if ($isNullLike) {
                    $request->request->remove($field);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Effective Business Type
        |--------------------------------------------------------------------------
        |
        | Template validation ke liye selected/new type use hogi.
        | Agar type request me nahi hai to existing type use hogi.
        |
        */

        $effectiveBusinessTypeId =
            $request->filled('type')
                ? (int) $request->input('type')
                : (
                    $business->exists
                        ? (int) $business->type
                        : null
                );

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $request->validate(
            [
                'name' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:255',
                ],

                'type' => [
                    'sometimes',
                    'required',
                    'integer',
                    Rule::exists('business_types', 'id'),
                ],

                'email' => [
                    'sometimes',
                    'nullable',
                    'email',
                    'max:255',
                    Rule::unique('businesses', 'email')
                        ->when(
                            $business->exists,
                            fn ($rule) =>
                                $rule->ignore($business->id)
                        ),
                ],

                'mobile' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('businesses', 'mobile')
                        ->when(
                            $business->exists,
                            fn ($rule) =>
                                $rule->ignore($business->id)
                        ),
                ],

                'gst_enabled' => [
                    'sometimes',
                    'nullable',
                    'boolean',
                ],

                'gstin' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:50',
                ],

                'state' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:255',
                ],

                'state_code' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:20',
                ],

                'address' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'pdf_template_id' => [
                    'sometimes',
                    'required',
                    'integer',
                    function (
                        string $attribute,
                        mixed $value,
                        \Closure $fail
                    ) use ($effectiveBusinessTypeId) {
                        if (!$effectiveBusinessTypeId) {
                            $fail(
                                'Please select a business type before choosing an invoice template.'
                            );

                            return;
                        }

                        $templateExists = BillTemplate::query()
                            ->whereKey((int) $value)
                            ->where(function ($query) use (
                                $effectiveBusinessTypeId
                            ) {
                                $query
                                    ->whereNull('business_type_id')
                                    ->orWhere(
                                        'business_type_id',
                                        $effectiveBusinessTypeId
                                    );
                            })
                            ->exists();

                        if (!$templateExists) {
                            $fail(
                                'The selected invoice template does not belong to the selected business type.'
                            );
                        }
                    },
                ],

                'invoice_base_prefix' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:100',
                    'regex:/^[A-Za-z0-9_\/-]+$/',
                ],

                'rounding_mode' => [
                    'sometimes',
                    'nullable',
                    Rule::in([
                        'none',
                        'nearest',
                        'up',
                        'down',
                    ]),
                ],

                'rounding_step' => [
                    'sometimes',
                    'nullable',
                    'numeric',
                    'min:0.01',
                    'max:1000',
                ],

                'terms' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:5000',
                ],

                'logo' => [
                    'sometimes',
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ],

                'signature' => [
                    'sometimes',
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ],

                'letter_head' => [
                    'sometimes',
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:4096',
                ],

                'remove_logo' => [
                    'sometimes',
                    'nullable',
                    'boolean',
                ],

                'remove_signature' => [
                    'sometimes',
                    'nullable',
                    'boolean',
                ],

                'remove_letter_head' => [
                    'sometimes',
                    'nullable',
                    'boolean',
                ],
            ],
            [
                'type.exists' =>
                    'The selected business type is invalid.',

                'type.required' =>
                    'Please select a business type.',

                'email.email' =>
                    'Please enter a valid email address.',

                'email.unique' =>
                    'This email address is already being used.',

                'mobile.unique' =>
                    'This mobile number is already being used.',

                'pdf_template_id.required' =>
                    'Please select a valid invoice template.',

                'invoice_base_prefix.regex' =>
                    'Invoice prefix may contain only letters, numbers, slash, hyphen and underscore.',

                'logo.image' =>
                    'Logo must be a valid image.',

                'signature.image' =>
                    'Signature must be a valid image.',

                'letter_head.image' =>
                    'Letter head must be a valid image.',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Build Safe Update Data
        |--------------------------------------------------------------------------
        |
        | Existing business me sirf present fields update hongi.
        |
        */

        $data = [];

        if ($request->has('name')) {
            $data['name'] =
                $request->filled('name')
                    ? trim((string) $request->input('name'))
                    : null;
        }

        /*
        |--------------------------------------------------------------------------
        | Business Type
        |--------------------------------------------------------------------------
        |
        | Blank existing type ko kabhi null nahi karega.
        |
        */

        if ($request->filled('type')) {
            $data['type'] =
                (int) $request->input('type');
        }

        if ($request->has('email')) {
            $data['email'] =
                $request->filled('email')
                    ? strtolower(
                        trim(
                            (string) $request->input('email')
                        )
                    )
                    : null;
        }

        if ($request->has('mobile')) {
            $data['mobile'] =
                $request->filled('mobile')
                    ? trim(
                        (string) $request->input('mobile')
                    )
                    : null;
        }

        if ($request->has('address')) {
            $data['address'] =
                $request->filled('address')
                    ? trim(
                        (string) $request->input('address')
                    )
                    : null;
        }

        /*
        |--------------------------------------------------------------------------
        | State / State Code
        |--------------------------------------------------------------------------
        */

        if ($request->has('state')) {
            $stateInput = trim(
                (string) $request->input('state', '')
            );

            if ($stateInput === '') {
                $data['state'] = null;

                if ($request->has('state_code')) {
                    $data['state_code'] =
                        $request->filled('state_code')
                            ? trim(
                                (string) $request->input(
                                    'state_code'
                                )
                            )
                            : null;
                }
            } elseif (str_contains($stateInput, ',')) {
                [
                    $stateCode,
                    $stateName,
                ] = array_pad(
                    explode(',', $stateInput, 2),
                    2,
                    null
                );

                $data['state_code'] =
                    trim((string) $stateCode) !== ''
                        ? trim((string) $stateCode)
                        : null;

                $data['state'] =
                    trim((string) $stateName) !== ''
                        ? trim((string) $stateName)
                        : null;
            } else {
                $data['state'] = $stateInput;

                if ($request->has('state_code')) {
                    $data['state_code'] =
                        $request->filled('state_code')
                            ? trim(
                                (string) $request->input(
                                    'state_code'
                                )
                            )
                            : null;
                }
            }
        } elseif ($request->has('state_code')) {
            $data['state_code'] =
                $request->filled('state_code')
                    ? trim(
                        (string) $request->input(
                            'state_code'
                        )
                    )
                    : null;
        }

        /*
        |--------------------------------------------------------------------------
        | GST
        |--------------------------------------------------------------------------
        */

        if ($request->has('gst_enabled')) {
            $gstEnabled =
                $request->boolean('gst_enabled');

            $data['gst_enabled'] =
                $gstEnabled;

            if (!$gstEnabled) {
                $data['gstin'] = null;
            } elseif ($request->has('gstin')) {
                $data['gstin'] =
                    $request->filled('gstin')
                        ? strtoupper(
                            trim(
                                (string) $request->input(
                                    'gstin'
                                )
                            )
                        )
                        : null;
            }
        } elseif ($request->has('gstin')) {
            $data['gstin'] =
                $request->filled('gstin')
                    ? strtoupper(
                        trim(
                            (string) $request->input(
                                'gstin'
                            )
                        )
                    )
                    : null;
        }

        /*
        |--------------------------------------------------------------------------
        | Bill Template
        |--------------------------------------------------------------------------
        |
        | Blank existing template ko null nahi karega.
        |
        */

        if ($request->filled('pdf_template_id')) {
            $data['pdf_template_id'] =
                (int) $request->input(
                    'pdf_template_id'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Invalid Existing Template After Type Change
        |--------------------------------------------------------------------------
        |
        | Agar business type change hui aur request me template nahi bheja,
        | to existing template target business type ke compatible honi chahiye.
        | Silently null/change nahi karenge.
        |
        */

        if (
            $business->exists
            && array_key_exists('type', $data)
            && !array_key_exists('pdf_template_id', $data)
            && filled($business->pdf_template_id)
        ) {
            $existingTemplateAllowed =
                BillTemplate::query()
                    ->whereKey(
                        (int) $business->pdf_template_id
                    )
                    ->where(function ($query) use ($data) {
                        $query
                            ->whereNull('business_type_id')
                            ->orWhere(
                                'business_type_id',
                                (int) $data['type']
                            );
                    })
                    ->exists();

            if (!$existingTemplateAllowed) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'pdf_template_id' =>
                            'Business type change karne ke saath us business type ka valid invoice template bhi select kijiye.',
                    ]);
            }
        }

        if ($request->has('invoice_base_prefix')) {
            $data['invoice_base_prefix'] =
                $request->filled(
                    'invoice_base_prefix'
                )
                    ? strtoupper(
                        trim(
                            (string) $request->input(
                                'invoice_base_prefix'
                            )
                        )
                    )
                    : null;
        }

        /*
        |--------------------------------------------------------------------------
        | Rounding
        |--------------------------------------------------------------------------
        */

        if ($request->has('rounding_mode')) {
            $roundingMode =
                $request->filled('rounding_mode')
                    ? (string) $request->input(
                        'rounding_mode'
                    )
                    : 'none';

            $data['rounding_mode'] =
                $roundingMode;

            if ($roundingMode === 'none') {
                $data['rounding_step'] = 1;
            } elseif (
                $request->has('rounding_step')
            ) {
                $data['rounding_step'] =
                    $request->filled(
                        'rounding_step'
                    )
                        ? (float) $request->input(
                            'rounding_step'
                        )
                        : 1;
            }
        } elseif ($request->has('rounding_step')) {
            $data['rounding_step'] =
                $request->filled('rounding_step')
                    ? (float) $request->input(
                        'rounding_step'
                    )
                    : 1;
        }

        if ($request->has('terms')) {
            $data['terms'] =
                $request->filled('terms')
                    ? trim(
                        (string) $request->input(
                            'terms'
                        )
                    )
                    : null;
        }

        /*
        |--------------------------------------------------------------------------
        | Slug
        |--------------------------------------------------------------------------
        */

        if (
            empty($business->slug)
            && filled(
                $data['name']
                ?? ($business->name ?? null)
            )
        ) {
            $nameForSlug =
                $data['name']
                ?? $business->name;

            $baseSlug =
                Str::slug($nameForSlug);

            if ($baseSlug === '') {
                $baseSlug =
                    'business-'
                    . (
                        $business->id
                        ?: Str::lower(
                            Str::random(8)
                        )
                    );
            }

            $slug = $baseSlug;
            $counter = 1;

            while (
                Business::query()
                    ->where('slug', $slug)
                    ->when(
                        $business->exists,
                        fn ($query) =>
                            $query->where(
                                'id',
                                '!=',
                                $business->id
                            )
                    )
                    ->exists()
            ) {
                $slug =
                    $baseSlug
                    . '-'
                    . $counter;

                $counter++;
            }

            $data['slug'] = $slug;
        }

        /*
        |--------------------------------------------------------------------------
        | Files
        |--------------------------------------------------------------------------
        */

        if (
            $request->boolean('remove_logo')
            && filled($business->logo)
        ) {
            if (
                Storage::disk('public')
                    ->exists($business->logo)
            ) {
                Storage::disk('public')
                    ->delete($business->logo);
            }

            $data['logo'] = null;
        }

        if ($request->hasFile('logo')) {
            $oldLogo = $business->logo;

            $data['logo'] =
                $request
                    ->file('logo')
                    ->store(
                        'business_logos',
                        'public'
                    );

            if (
                filled($oldLogo)
                && Storage::disk('public')
                    ->exists($oldLogo)
            ) {
                Storage::disk('public')
                    ->delete($oldLogo);
            }
        }

        if (
            $request->boolean('remove_signature')
            && filled($business->signature)
        ) {
            if (
                Storage::disk('public')
                    ->exists(
                        $business->signature
                    )
            ) {
                Storage::disk('public')
                    ->delete(
                        $business->signature
                    );
            }

            $data['signature'] = null;
        }

        if ($request->hasFile('signature')) {
            $oldSignature =
                $business->signature;

            $data['signature'] =
                $request
                    ->file('signature')
                    ->store(
                        'business_signatures',
                        'public'
                    );

            if (
                filled($oldSignature)
                && Storage::disk('public')
                    ->exists($oldSignature)
            ) {
                Storage::disk('public')
                    ->delete($oldSignature);
            }
        }

        if (
            $request->boolean(
                'remove_letter_head'
            )
            && filled(
                $business->letter_head
            )
        ) {
            if (
                Storage::disk('public')
                    ->exists(
                        $business->letter_head
                    )
            ) {
                Storage::disk('public')
                    ->delete(
                        $business->letter_head
                    );
            }

            $data['letter_head'] = null;
        }

        if (
            $request->hasFile(
                'letter_head'
            )
        ) {
            $oldLetterHead =
                $business->letter_head;

            $data['letter_head'] =
                $request
                    ->file('letter_head')
                    ->store(
                        'business_letter_heads',
                        'public'
                    );

            if (
                filled($oldLetterHead)
                && Storage::disk('public')
                    ->exists($oldLetterHead)
            ) {
                Storage::disk('public')
                    ->delete(
                        $oldLetterHead
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Create / Update
        |--------------------------------------------------------------------------
        */

        if ($isCreating) {
            if (
                !filled(
                    $data['name'] ?? null
                )
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'name' =>
                            'Business create karne ke liye business name enter kijiye.',
                    ]);
            }

            if (
                !filled(
                    $data['mobile'] ?? null
                )
            ) {
                $data['mobile'] =
                    $user->phone ?: null;
            }

            /*
             * Create ke waqt type blank ho to default first BusinessType.
             */
            if (empty($data['type'])) {
                $data['type'] =
                    BusinessType::query()
                        ->orderBy('id')
                        ->value('id');
            }

            if (empty($data['type'])) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'type' =>
                            'Admin panel se kam se kam ek business type create kijiye.',
                    ]);
            }

            /*
             * Agar create request me template hai to final type ke against
             * compatibility dobara ensure karo.
             */
            if (
                filled(
                    $data['pdf_template_id']
                    ?? null
                )
            ) {
                $templateAllowed =
                    BillTemplate::query()
                        ->whereKey(
                            (int) $data[
                                'pdf_template_id'
                            ]
                        )
                        ->where(function ($query) use ($data) {
                            $query
                                ->whereNull(
                                    'business_type_id'
                                )
                                ->orWhere(
                                    'business_type_id',
                                    (int) $data['type']
                                );
                        })
                        ->exists();

                if (!$templateAllowed) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'pdf_template_id' =>
                                'The selected invoice template does not belong to the selected business type.',
                        ]);
                }
            }

            $data['gst_enabled'] =
                $data['gst_enabled']
                ?? false;

            $data['invoice_base_prefix'] =
                $data['invoice_base_prefix']
                ?? 'RV/SL';

            $data['rounding_mode'] =
                $data['rounding_mode']
                ?? 'nearest';

            $data['rounding_step'] =
                $data['rounding_step']
                ?? 1;

            if (empty($data['slug'])) {
                $baseSlug =
                    Str::slug(
                        $data['name']
                    );

                if ($baseSlug === '') {
                    $baseSlug =
                        'business-'
                        . Str::lower(
                            Str::random(8)
                        );
                }

                $slug = $baseSlug;
                $counter = 1;

                while (
                    Business::query()
                        ->where(
                            'slug',
                            $slug
                        )
                        ->exists()
                ) {
                    $slug =
                        $baseSlug
                        . '-'
                        . $counter;

                    $counter++;
                }

                $data['slug'] = $slug;
            }

            $business =
                Business::query()
                    ->create($data);

            DB::table(
                'business_user'
            )->updateOrInsert(
                [
                    'business_id' =>
                        $business->id,

                    'user_id' =>
                        $user->id,
                ],
                [
                    'role' => 'owner',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $user->current_business_id =
                $business->id;

            $user->save();
        } else {
            if (!empty($data)) {
                $business->update($data);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Refresh Profile
        |--------------------------------------------------------------------------
        */

        $business->refresh();
        $business->refreshProfileCompletion();
        $business->refresh();

        session([
            'active_business_id' =>
                $business->id,

            'active_business_name' =>
                $business->name
                ?? 'Business',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Pending Plan
        |--------------------------------------------------------------------------
        */

        $pendingPlanId =
            session(
                'pending_registration_plan_id'
            );

        $pendingTrial =
            (int) session(
                'pending_registration_trial',
                0
            ) === 1;

        $pendingPaymentDone =
            (int) session(
                'pending_registration_payment_done',
                0
            ) === 1;

        if (
            $pendingPlanId
            && (
                $pendingTrial
                || $pendingPaymentDone
            )
        ) {
            $plan = Plan::query()
                ->with('permissions')
                ->find($pendingPlanId);

            if ($plan) {
                UserPlan::query()
                    ->where(
                        'business_id',
                        $business->id
                    )
                    ->where(
                        'status',
                        1
                    )
                    ->update([
                        'status' => 0,
                    ]);

                UserPlan::query()
                    ->create([
                        'business_id' =>
                            $business->id,

                        'user_id' =>
                            $user->id,

                        'plan_id' =>
                            $plan->id,

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

                        'status' => 1,
                    ]);

                app(
                    PermissionRegistrar::class
                )->forgetCachedPermissions();

                $permissions =
                    $plan
                        ->permissions()
                        ->where(
                            'guard_name',
                            'web'
                        )
                        ->pluck('name')
                        ->toArray();

                if (!empty($permissions)) {
                    $user->syncPermissions(
                        $permissions
                    );
                }
            }
        }

        session()->forget([
            'pending_registration_plan_id',
            'pending_registration_trial',
            'pending_registration_payment_done',
            'pending_registration_billing_data',
        ]);

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                $isCreating
                    ? 'Business profile created successfully.'
                    : 'Business profile updated successfully.'
            );
    }

    /**
     * Invoice template select.
     */
    public function selectTemplate(
        Request $request,
        BillTemplate $billTemplate
    ) {
        $business =
            $this->resolveBusiness(
                $request,
                true
            );

        if (!$business->type) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Please select a business type before choosing an invoice template.',
            ], 422);
        }

        $isTemplateAllowed =
            BillTemplate::query()
                ->whereKey(
                    $billTemplate->id
                )
                ->where(function ($query) use ($business) {
                    $query
                        ->whereNull(
                            'business_type_id'
                        )
                        ->orWhere(
                            'business_type_id',
                            (int) $business->type
                        );
                })
                ->exists();

        if (!$isTemplateAllowed) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This invoice template is not available for the selected business type.',
            ], 422);
        }

        $business->update([
            'pdf_template_id' =>
                $billTemplate->id,
        ]);

        $business->refreshProfileCompletion();
        $business->refresh();

        return response()->json([
            'success' => true,
            'message' =>
                'Invoice template selected successfully.',

            'template_id' =>
                $billTemplate->id,

            'completion' =>
                $business->profile_completion,
        ]);
    }

    /**
     * Profile suggestion dismiss.
     */
    public function dismissSuggestion(
        Request $request
    ) {
        $business =
            $this->resolveBusiness(
                $request,
                true
            );

        $business->update([
            'profile_suggestion_dismissed_at' =>
                now(),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Current active business resolve.
     */
    private function resolveBusiness(
        Request $request,
        bool $required = true
    ): ?Business {
        $user = $request->user();

        abort_unless(
            $user,
            401,
            'Please login to continue.'
        );

        $activeBusinessId =
            session('active_business_id')
            ?? $user->current_business_id
            ?? $user
                ->businesses()
                ->value('businesses.id');

        if (!$activeBusinessId) {
            if ($required) {
                abort(
                    404,
                    'No business is attached to your account.'
                );
            }

            return null;
        }

        $isSuperAdmin =
            $user->hasRole('super_admin')
            || $user->hasRole('super admin');

        $canViewAllBusinesses =
            $user->can('view all businesses');

        if (
            !$isSuperAdmin
            && !$canViewAllBusinesses
        ) {
            $hasAccess =
                $user
                    ->businesses()
                    ->where(
                        'businesses.id',
                        $activeBusinessId
                    )
                    ->exists();

            if (!$hasAccess) {
                if (!$required) {
                    session()->forget([
                        'active_business_id',
                        'active_business_name',
                    ]);

                    if (
                        (int) $user
                            ->current_business_id
                        ===
                        (int) $activeBusinessId
                    ) {
                        $user->current_business_id =
                            null;

                        $user->save();
                    }

                    return null;
                }

                abort(
                    403,
                    'You are not allowed to access this business.'
                );
            }
        }

        $business =
            Business::query()
                ->find($activeBusinessId);

        if (
            !$business
            && $required
        ) {
            abort(
                404,
                'Business not found.'
            );
        }

        return $business;
    }
}
