<?php

// namespace App\Http\Controllers;

// use App\Models\BillTemplate;
// use App\Models\Business;
// use App\Models\BusinessType;
// use App\Models\Item;
// use App\Models\Plan;
// use App\Models\UserPlan;
// use Carbon\Carbon;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Str;
// use Illuminate\Validation\Rule;
// use Spatie\Permission\PermissionRegistrar;

// class BusinessProfileController extends Controller
// {
//     /**
//      * Business profile form show karega.
//      */
//     public function index(Request $request)
//     {
//         $business = $this->resolveBusiness($request, false);

//         if ($business) {
//             $business->refreshProfileCompletion();
//             $business->refresh();

//             $missingFields =
//                 $business->missingProfileFields();

//             $itemCount = Item::query()
//                 ->where('business_id', $business->id)
//                 ->count();
//         } else {
//             $business = new Business([
//                 'name' => null,
//                 'email' => null,
//                 'mobile' => $request->user()?->phone,
//                 'gst_enabled' => false,
//                 'invoice_base_prefix' => 'RV/SL',
//                 'rounding_mode' => 'nearest',
//                 'rounding_step' => 1,
//                 'profile_completion' => 0,
//             ]);

//             $business->id = null;
//             $business->profile_completion = 0;

//             $missingFields = [
//                 'Business Name',
//                 'Business Type',
//                 'Mobile Number',
//                 'Business Address',
//             ];

//             $itemCount = 0;
//         }

//         $businessTypes = BusinessType::query()
//             ->orderBy('name')
//             ->get();

//         $billTemplates = BillTemplate::query()
//             ->orderBy('name')
//             ->get();

//         return view(
//             'business-profile.index',
//             compact(
//                 'business',
//                 'businessTypes',
//                 'billTemplates',
//                 'missingFields',
//                 'itemCount'
//             )
//         );
//     }

//     /**
//      * Business profile update karega.
//      *
//      * Yahan koi bhi field required nahi hai.
//      */
//     public function update(Request $request)
//     {
//         $user = $request->user();

//         abort_unless(
//             $user,
//             401,
//             'Please login to continue.'
//         );

//         $business = $this->resolveBusiness(
//             $request,
//             false
//         );

//         $isCreating = !$business;

//         if ($isCreating) {
//             $business = new Business();
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Validation
//         |--------------------------------------------------------------------------
//         |
//         | Sabhi fields optional hain.
//         |
//         */

//         $data = $request->validate(
//             [
//                 'name' => [
//                     'nullable',
//                     'string',
//                     'max:255',
//                 ],

//                 /*
//                  * Bilkul BusinessController wale pattern par.
//                  */
//                 'type' => [
//                     'nullable',
//                     'integer',
//                     Rule::exists('business_types', 'id'),
//                 ],

//                 'email' => [
//                     'nullable',
//                     'email',
//                     'max:255',
//                     Rule::unique('businesses', 'email')
//                         ->when(
//                             $business->exists,
//                             fn ($rule) =>
//                                 $rule->ignore($business->id)
//                         ),
//                 ],

//                 'mobile' => [
//                     'nullable',
//                     'string',
//                     'max:20',
//                     Rule::unique('businesses', 'mobile')
//                         ->when(
//                             $business->exists,
//                             fn ($rule) =>
//                                 $rule->ignore($business->id)
//                         ),
//                 ],

//                 'gst_enabled' => [
//                     'nullable',
//                     'boolean',
//                 ],

//                 'gstin' => [
//                     'nullable',
//                     'string',
//                     'max:50',
//                 ],

//                 'state' => [
//                     'nullable',
//                     'string',
//                     'max:255',
//                 ],

//                 'state_code' => [
//                     'nullable',
//                     'string',
//                     'max:20',
//                 ],

//                 'address' => [
//                     'nullable',
//                     'string',
//                     'max:1000',
//                 ],

//                 'pdf_template_id' => [
//                     'nullable',
//                     'integer',
//                     Rule::exists('bill_templates', 'id'),
//                 ],

//                 'invoice_base_prefix' => [
//                     'nullable',
//                     'string',
//                     'max:100',
//                     'regex:/^[A-Za-z0-9_\/-]+$/',
//                 ],

//                 'rounding_mode' => [
//                     'nullable',
//                     Rule::in([
//                         'none',
//                         'nearest',
//                         'up',
//                         'down',
//                     ]),
//                 ],

//                 'rounding_step' => [
//                     'nullable',
//                     'numeric',
//                     'min:0.01',
//                     'max:1000',
//                 ],

//                 'terms' => [
//                     'nullable',
//                     'string',
//                     'max:5000',
//                 ],

//                 'logo' => [
//                     'nullable',
//                     'image',
//                     'mimes:jpg,jpeg,png,webp',
//                     'max:2048',
//                 ],

//                 'signature' => [
//                     'nullable',
//                     'image',
//                     'mimes:jpg,jpeg,png,webp',
//                     'max:2048',
//                 ],

//                 'letter_head' => [
//                     'nullable',
//                     'image',
//                     'mimes:jpg,jpeg,png,webp',
//                     'max:4096',
//                 ],

//                 'remove_logo' => [
//                     'nullable',
//                     'boolean',
//                 ],

//                 'remove_signature' => [
//                     'nullable',
//                     'boolean',
//                 ],

//                 'remove_letter_head' => [
//                     'nullable',
//                     'boolean',
//                 ],
//             ],
//             [
//                 'type.exists' =>
//                     'The selected business type is invalid.',

//                 'pdf_template_id.exists' =>
//                     'The selected invoice template is invalid.',

//                 'email.email' =>
//                     'Please enter a valid email address.',

//                 'email.unique' =>
//                     'This email address is already being used.',

//                 'mobile.unique' =>
//                     'This mobile number is already being used.',

//                 'invoice_base_prefix.regex' =>
//                     'Invoice prefix may contain only letters, numbers, slash, hyphen and underscore.',

//                 'logo.image' =>
//                     'Logo must be a valid image.',

//                 'signature.image' =>
//                     'Signature must be a valid image.',

//                 'letter_head.image' =>
//                     'Letter head must be a valid image.',
//             ]
//         );

//         /*
//         |--------------------------------------------------------------------------
//         | Business type
//         |--------------------------------------------------------------------------
//         |
//         | Request me business_type_id aayi hai to selected ID save hogi.
//         | Empty select hone par null save hoga.
//         |
//         */

//         $data['type'] =
//             $request->filled('type')
//                 ? (int) $request->input('type')
//                 : null;

//         /*
//         |--------------------------------------------------------------------------
//         | Basic fields normalize
//         |--------------------------------------------------------------------------
//         */

//         $data['name'] = $request->filled('name')
//             ? trim((string) $request->input('name'))
//             : null;

//         $data['email'] = $request->filled('email')
//             ? strtolower(
//                 trim((string) $request->input('email'))
//             )
//             : null;

//         $data['mobile'] = $request->filled('mobile')
//             ? trim((string) $request->input('mobile'))
//             : null;

//         $data['address'] = $request->filled('address')
//             ? trim((string) $request->input('address'))
//             : null;

//         /*
//         |--------------------------------------------------------------------------
//         | State and state code
//         |--------------------------------------------------------------------------
//         |
//         | Form value:
//         | 09,Uttar Pradesh
//         |
//         | Database:
//         | state_code = 09
//         | state      = Uttar Pradesh
//         |
//         */

//         $stateInput = trim(
//             (string) $request->input('state', '')
//         );

//         if ($stateInput === '') {
//             $data['state'] = null;

//             $data['state_code'] =
//                 $request->filled('state_code')
//                     ? trim(
//                         (string) $request->input('state_code')
//                     )
//                     : null;
//         } elseif (str_contains($stateInput, ',')) {
//             [$stateCode, $stateName] = array_pad(
//                 explode(',', $stateInput, 2),
//                 2,
//                 null
//             );

//             $data['state_code'] =
//                 trim((string) $stateCode) !== ''
//                     ? trim((string) $stateCode)
//                     : null;

//             $data['state'] =
//                 trim((string) $stateName) !== ''
//                     ? trim((string) $stateName)
//                     : null;
//         } else {
//             $data['state'] = $stateInput;

//             $data['state_code'] =
//                 $request->filled('state_code')
//                     ? trim(
//                         (string) $request->input('state_code')
//                     )
//                     : null;
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | GST
//         |--------------------------------------------------------------------------
//         */

//         $data['gst_enabled'] =
//             $request->boolean('gst_enabled');

//         $data['gstin'] =
//             $data['gst_enabled'] &&
//             $request->filled('gstin')
//                 ? strtoupper(
//                     trim((string) $request->input('gstin'))
//                 )
//                 : null;

//         /*
//         |--------------------------------------------------------------------------
//         | PDF template
//         |--------------------------------------------------------------------------
//         */

//         $data['pdf_template_id'] =
//             $request->filled('pdf_template_id')
//                 ? (int) $request->input('pdf_template_id')
//                 : null;

//         /*
//         |--------------------------------------------------------------------------
//         | Invoice prefix
//         |--------------------------------------------------------------------------
//         */

//         $data['invoice_base_prefix'] =
//             $request->filled('invoice_base_prefix')
//                 ? strtoupper(
//                     trim(
//                         (string) $request->input(
//                             'invoice_base_prefix'
//                         )
//                     )
//                 )
//                 : null;

//         /*
//         |--------------------------------------------------------------------------
//         | Rounding settings
//         |--------------------------------------------------------------------------
//         */

//         $data['rounding_mode'] =
//             $request->filled('rounding_mode')
//                 ? (string) $request->input('rounding_mode')
//                 : 'none';

//         if ($data['rounding_mode'] === 'none') {
//             $data['rounding_step'] = 1;
//         } else {
//             $data['rounding_step'] =
//                 $request->filled('rounding_step')
//                     ? (float) $request->input('rounding_step')
//                     : 1;
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Terms
//         |--------------------------------------------------------------------------
//         */

//         $data['terms'] =
//             $request->filled('terms')
//                 ? trim((string) $request->input('terms'))
//                 : null;

//         /*
//         |--------------------------------------------------------------------------
//         | Slug handling
//         |--------------------------------------------------------------------------
//         |
//         | Existing slug ko bina zarurat change nahi karenge.
//         | Agar slug empty hai aur name diya hai tab generate hoga.
//         |
//         */

//         if (
//             empty($business->slug) &&
//             filled($data['name'] ?? null)
//         ) {
//             $baseSlug = Str::slug($data['name']);

//             if ($baseSlug === '') {
//                 $baseSlug =
//                     'business-' .
//                     ($business->id ?: Str::lower(Str::random(8)));
//             }

//             $slug = $baseSlug;
//             $counter = 1;

//             while (
//                 Business::query()
//                     ->where('slug', $slug)
//                     ->when(
//                         $business->exists,
//                         fn ($query) =>
//                             $query->where(
//                                 'id',
//                                 '!=',
//                                 $business->id
//                             )
//                     )
//                     ->exists()
//             ) {
//                 $slug = $baseSlug . '-' . $counter;
//                 $counter++;
//             }

//             $data['slug'] = $slug;
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Remove action fields
//         |--------------------------------------------------------------------------
//         |
//         | Ye database columns nahi hain.
//         |
//         */

//         unset(
//             $data['remove_logo'],
//             $data['remove_signature'],
//             $data['remove_letter_head']
//         );

//         /*
//         |--------------------------------------------------------------------------
//         | Remove logo
//         |--------------------------------------------------------------------------
//         */

//         if (
//             $request->boolean('remove_logo') &&
//             filled($business->logo)
//         ) {
//             if (
//                 Storage::disk('public')
//                     ->exists($business->logo)
//             ) {
//                 Storage::disk('public')
//                     ->delete($business->logo);
//             }

//             $data['logo'] = null;
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Replace logo
//         |--------------------------------------------------------------------------
//         */

//         if ($request->hasFile('logo')) {
//             $oldLogo = $business->logo;

//             $data['logo'] = $request
//                 ->file('logo')
//                 ->store(
//                     'business_logos',
//                     'public'
//                 );

//             if (
//                 filled($oldLogo) &&
//                 Storage::disk('public')->exists($oldLogo)
//             ) {
//                 Storage::disk('public')->delete($oldLogo);
//             }
//         } else {
//             /*
//              * File select nahi ki to existing logo preserve rahega.
//              */
//             if (!$request->boolean('remove_logo')) {
//                 unset($data['logo']);
//             }
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Remove signature
//         |--------------------------------------------------------------------------
//         */

//         if (
//             $request->boolean('remove_signature') &&
//             filled($business->signature)
//         ) {
//             if (
//                 Storage::disk('public')
//                     ->exists($business->signature)
//             ) {
//                 Storage::disk('public')
//                     ->delete($business->signature);
//             }

//             $data['signature'] = null;
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Replace signature
//         |--------------------------------------------------------------------------
//         */

//         if ($request->hasFile('signature')) {
//             $oldSignature = $business->signature;

//             $data['signature'] = $request
//                 ->file('signature')
//                 ->store(
//                     'business_signatures',
//                     'public'
//                 );

//             if (
//                 filled($oldSignature) &&
//                 Storage::disk('public')
//                     ->exists($oldSignature)
//             ) {
//                 Storage::disk('public')
//                     ->delete($oldSignature);
//             }
//         } else {
//             if (!$request->boolean('remove_signature')) {
//                 unset($data['signature']);
//             }
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Remove letter head
//         |--------------------------------------------------------------------------
//         */

//         if (
//             $request->boolean('remove_letter_head') &&
//             filled($business->letter_head)
//         ) {
//             if (
//                 Storage::disk('public')
//                     ->exists($business->letter_head)
//             ) {
//                 Storage::disk('public')
//                     ->delete($business->letter_head);
//             }

//             $data['letter_head'] = null;
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Replace letter head
//         |--------------------------------------------------------------------------
//         */

//         if ($request->hasFile('letter_head')) {
//             $oldLetterHead = $business->letter_head;

//             $data['letter_head'] = $request
//                 ->file('letter_head')
//                 ->store(
//                     'business_letter_heads',
//                     'public'
//                 );

//             if (
//                 filled($oldLetterHead) &&
//                 Storage::disk('public')
//                     ->exists($oldLetterHead)
//             ) {
//                 Storage::disk('public')
//                     ->delete($oldLetterHead);
//             }
//         } else {
//             if (!$request->boolean('remove_letter_head')) {
//                 unset($data['letter_head']);
//             }
//         }

//         if ($isCreating) {
//             if (!filled($data['name'] ?? null)) {
//                 return back()
//                     ->withInput()
//                     ->withErrors([
//                         'name' =>
//                             'Business create karne ke liye business name enter kijiye.',
//                     ]);
//             }

//             if (!filled($data['mobile'] ?? null)) {
//                 $data['mobile'] =
//                     $user->phone ?: null;
//             }

//             if (empty($data['type'])) {
//                 $data['type'] =
//                     BusinessType::query()
//                         ->orderBy('id')
//                         ->value('id');
//             }

//             if (empty($data['type'])) {
//                 return back()
//                     ->withInput()
//                     ->withErrors([
//                         'type' =>
//                             'Admin panel se kam se kam ek business type create kijiye.',
//                     ]);
//             }

//             if (empty($data['slug'])) {
//                 $baseSlug = Str::slug($data['name']);

//                 if ($baseSlug === '') {
//                     $baseSlug =
//                         'business-' .
//                         Str::lower(Str::random(8));
//                 }

//                 $slug = $baseSlug;
//                 $counter = 1;

//                 while (
//                     Business::query()
//                         ->where('slug', $slug)
//                         ->exists()
//                 ) {
//                     $slug =
//                         $baseSlug . '-' . $counter;

//                     $counter++;
//                 }

//                 $data['slug'] = $slug;
//             }

//             $business = Business::query()
//                 ->create($data);

//             DB::table('business_user')->updateOrInsert(
//                 [
//                     'business_id' => $business->id,
//                     'user_id' => $user->id,
//                 ],
//                 [
//                     'role' => 'owner',
//                     'created_at' => now(),
//                     'updated_at' => now(),
//                 ]
//             );

//             $user->current_business_id =
//                 $business->id;

//             $user->save();
//         } else {
//             $business->update($data);
//         }

//         $business->refresh();
//         $business->refreshProfileCompletion();
//         $business->refresh();

//         session([
//             'active_business_id' => $business->id,
//             'active_business_name' =>
//                 $business->name ?? 'Business',
//         ]);

//         $pendingPlanId = session(
//             'pending_registration_plan_id'
//         );

//         $pendingTrial =
//             (int) session(
//                 'pending_registration_trial',
//                 0
//             ) === 1;

//         $pendingPaymentDone =
//             (int) session(
//                 'pending_registration_payment_done',
//                 0
//             ) === 1;

//         if (
//             $pendingPlanId &&
//             ($pendingTrial || $pendingPaymentDone)
//         ) {
//             $plan = Plan::query()
//                 ->with('permissions')
//                 ->find($pendingPlanId);

//             if ($plan) {
//                 UserPlan::query()
//                     ->where(
//                         'business_id',
//                         $business->id
//                     )
//                     ->where('status', 1)
//                     ->update([
//                         'status' => 0,
//                     ]);

//                 UserPlan::query()->create([
//                     'business_id' =>
//                         $business->id,
//                     'user_id' => $user->id,
//                     'plan_id' => $plan->id,
//                     'start_date' => Carbon::today(),
//                     'expiry_date' =>
//                         Carbon::today()->addDays(
//                             (int) (
//                                 $plan->duration_days
//                                 ?? 30
//                             )
//                         ),
//                     'status' => 1,
//                 ]);

//                 app(PermissionRegistrar::class)
//                     ->forgetCachedPermissions();

//                 $permissions = $plan
//                     ->permissions()
//                     ->where('guard_name', 'web')
//                     ->pluck('name')
//                     ->toArray();

//                 if (!empty($permissions)) {
//                     $user->syncPermissions(
//                         $permissions
//                     );
//                 }
//             }
//         }

//         session()->forget([
//             'pending_registration_plan_id',
//             'pending_registration_trial',
//             'pending_registration_payment_done',
//             'pending_registration_billing_data',
//         ]);

//         return redirect()
//             ->route('dashboard')
//             ->with(
//                 'success',
//                 $isCreating
//                     ? 'Business profile created successfully.'
//                     : 'Business profile updated successfully.'
//             );
//     }

//     /**
//      * Invoice template ko alag request se select karega.
//      */
//     public function selectTemplate(
//         Request $request,
//         BillTemplate $billTemplate
//     ) {
//         $business = $this->resolveBusiness(
//             $request,
//             true
//         );

//         $business->update([
//             'pdf_template_id' => $billTemplate->id,
//         ]);

//         $business->refreshProfileCompletion();
//         $business->refresh();

//         return response()->json([
//             'success' => true,
//             'message' =>
//                 'Invoice template selected successfully.',
//             'template_id' => $billTemplate->id,
//             'completion' =>
//                 $business->profile_completion,
//         ]);
//     }

//     /**
//      * Profile suggestion dismiss karega.
//      */
//     public function dismissSuggestion(Request $request)
//     {
//         $business = $this->resolveBusiness(
//             $request,
//             true
//         );

//         $business->update([
//             'profile_suggestion_dismissed_at' => now(),
//         ]);

//         return response()->json([
//             'success' => true,
//         ]);
//     }

//     /**
//      * Current active business nikalega.
//      */
//     private function resolveBusiness(
//         Request $request,
//         bool $required = true
//     ): ?Business {
//         $user = $request->user();

//         abort_unless(
//             $user,
//             401,
//             'Please login to continue.'
//         );

//         $activeBusinessId =
//             session('active_business_id')
//             ?? $user->current_business_id
//             ?? $user->businesses()
//                 ->value('businesses.id');

//         if (!$activeBusinessId) {
//             if ($required) {
//                 abort(
//                     404,
//                     'No business is attached to your account.'
//                 );
//             }

//             return null;
//         }

//         $isSuperAdmin =
//             $user->hasRole('super_admin') ||
//             $user->hasRole('super admin');

//         $canViewAllBusinesses =
//             $user->can('view all businesses');

//         if (
//             !$isSuperAdmin &&
//             !$canViewAllBusinesses
//         ) {
//             $hasAccess = $user
//                 ->businesses()
//                 ->where(
//                     'businesses.id',
//                     $activeBusinessId
//                 )
//                 ->exists();

//             if (!$hasAccess) {
//                 if (!$required) {
//                     session()->forget([
//                         'active_business_id',
//                         'active_business_name',
//                     ]);

//                     if (
//                         (int) $user->current_business_id
//                         === (int) $activeBusinessId
//                     ) {
//                         $user->current_business_id =
//                             null;

//                         $user->save();
//                     }

//                     return null;
//                 }

//                 abort(
//                     403,
//                     'You are not allowed to access this business.'
//                 );
//             }
//         }

//         $business = Business::query()
//             ->find($activeBusinessId);

//         if (!$business && $required) {
//             abort(
//                 404,
//                 'Business not found.'
//             );
//         }

//         return $business;
//     }
// }

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

            $missingFields =
                $business->missingProfileFields();

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
     * Business profile update karega.
     *
     * Yahan koi bhi field required nahi hai.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        abort_unless($user, 401, 'Please login to continue.');

        $business = $this->resolveBusiness($request, false);
        $isCreating = !$business;

        if ($isCreating) {
            $business = new Business();
        }

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT: Partial update safe logic
        |--------------------------------------------------------------------------
        |
        | Existing business me sirf wahi field update hogi jo request me
        | actually bheji gayi hai. Missing field ko null/false/default karke
        | overwrite nahi kiya jayega.
        |
        */

        $effectiveBusinessTypeId = $request->has('type')
            ? ($request->filled('type') ? (int) $request->input('type') : null)
            : ($business->exists ? $business->type : null);

        $validated = $request->validate(
            [
                'name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'type' => [
                    'sometimes',
                    'nullable',
                    'integer',
                    Rule::exists('business_types', 'id'),
                ],
                'email' => [
                    'sometimes',
                    'nullable',
                    'email',
                    'max:255',
                    Rule::unique('businesses', 'email')->when(
                        $business->exists,
                        fn ($rule) => $rule->ignore($business->id)
                    ),
                ],
                'mobile' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('businesses', 'mobile')->when(
                        $business->exists,
                        fn ($rule) => $rule->ignore($business->id)
                    ),
                ],
                'gst_enabled' => ['sometimes', 'nullable', 'boolean'],
                'gstin' => ['sometimes', 'nullable', 'string', 'max:50'],
                'state' => ['sometimes', 'nullable', 'string', 'max:255'],
                'state_code' => ['sometimes', 'nullable', 'string', 'max:20'],
                'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
                'pdf_template_id' => [
                    'sometimes',
                    'nullable',
                    'integer',
                    function (string $attribute, mixed $value, \Closure $fail) use ($effectiveBusinessTypeId) {
                        if ($value === null || $value === '') {
                            return;
                        }

                        if (!$effectiveBusinessTypeId) {
                            $fail('Please select a business type before choosing an invoice template.');
                            return;
                        }

                        $templateExists = BillTemplate::query()
                            ->whereKey((int) $value)
                            ->where(function ($query) use ($effectiveBusinessTypeId) {
                                $query->whereNull('business_type_id')
                                    ->orWhere('business_type_id', $effectiveBusinessTypeId);
                            })
                            ->exists();

                        if (!$templateExists) {
                            $fail('The selected invoice template does not belong to the selected business type.');
                        }
                    },
                ],
                'invoice_base_prefix' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:100',
                    'regex:/^[A-Za-z0-9_\\/-]+$/',
                ],
                'rounding_mode' => [
                    'sometimes',
                    'nullable',
                    Rule::in(['none', 'nearest', 'up', 'down']),
                ],
                'rounding_step' => ['sometimes', 'nullable', 'numeric', 'min:0.01', 'max:1000'],
                'terms' => ['sometimes', 'nullable', 'string', 'max:5000'],
                'logo' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'signature' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'letter_head' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'remove_logo' => ['sometimes', 'nullable', 'boolean'],
                'remove_signature' => ['sometimes', 'nullable', 'boolean'],
                'remove_letter_head' => ['sometimes', 'nullable', 'boolean'],
            ],
            [
                'type.exists' => 'The selected business type is invalid.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email address is already being used.',
                'mobile.unique' => 'This mobile number is already being used.',
                'invoice_base_prefix.regex' => 'Invoice prefix may contain only letters, numbers, slash, hyphen and underscore.',
                'logo.image' => 'Logo must be a valid image.',
                'signature.image' => 'Signature must be a valid image.',
                'letter_head.image' => 'Letter head must be a valid image.',
            ]
        );

        $data = [];

        // Basic fields: request me aaye tabhi update honge.
        if ($request->has('name')) {
            $data['name'] = $request->filled('name')
                ? trim((string) $request->input('name'))
                : null;
        }

        if ($request->has('type')) {
            $data['type'] = $request->filled('type')
                ? (int) $request->input('type')
                : null;
        }

        if ($request->has('email')) {
            $data['email'] = $request->filled('email')
                ? strtolower(trim((string) $request->input('email')))
                : null;
        }

        if ($request->has('mobile')) {
            $data['mobile'] = $request->filled('mobile')
                ? trim((string) $request->input('mobile'))
                : null;
        }

        if ($request->has('address')) {
            $data['address'] = $request->filled('address')
                ? trim((string) $request->input('address'))
                : null;
        }

        // State ko sirf tab touch karo jab state/state_code request me ho.
        if ($request->has('state')) {
            $stateInput = trim((string) $request->input('state', ''));

            if ($stateInput === '') {
                $data['state'] = null;

                if ($request->has('state_code')) {
                    $data['state_code'] = $request->filled('state_code')
                        ? trim((string) $request->input('state_code'))
                        : null;
                }
            } elseif (str_contains($stateInput, ',')) {
                [$stateCode, $stateName] = array_pad(
                    explode(',', $stateInput, 2),
                    2,
                    null
                );

                $data['state_code'] = trim((string) $stateCode) !== ''
                    ? trim((string) $stateCode)
                    : null;

                $data['state'] = trim((string) $stateName) !== ''
                    ? trim((string) $stateName)
                    : null;
            } else {
                $data['state'] = $stateInput;

                if ($request->has('state_code')) {
                    $data['state_code'] = $request->filled('state_code')
                        ? trim((string) $request->input('state_code'))
                        : null;
                }
            }
        } elseif ($request->has('state_code')) {
            $data['state_code'] = $request->filled('state_code')
                ? trim((string) $request->input('state_code'))
                : null;
        }

        // GST: checkbox/API field absent ho to existing value preserve rahegi.
        if ($request->has('gst_enabled')) {
            $gstEnabled = $request->boolean('gst_enabled');
            $data['gst_enabled'] = $gstEnabled;

            if (!$gstEnabled) {
                $data['gstin'] = null;
            } elseif ($request->has('gstin')) {
                $data['gstin'] = $request->filled('gstin')
                    ? strtoupper(trim((string) $request->input('gstin')))
                    : null;
            }
        } elseif ($request->has('gstin')) {
            $data['gstin'] = $request->filled('gstin')
                ? strtoupper(trim((string) $request->input('gstin')))
                : null;
        }

        // Bill template: absent field = preserve existing template.
        if ($request->has('pdf_template_id')) {
            $data['pdf_template_id'] = $request->filled('pdf_template_id')
                ? (int) $request->input('pdf_template_id')
                : null;
        }

        if ($request->has('invoice_base_prefix')) {
            $data['invoice_base_prefix'] = $request->filled('invoice_base_prefix')
                ? strtoupper(trim((string) $request->input('invoice_base_prefix')))
                : null;
        }

        // Rounding: field absent ho to old setting preserve hogi.
        if ($request->has('rounding_mode')) {
            $roundingMode = $request->filled('rounding_mode')
                ? (string) $request->input('rounding_mode')
                : 'none';

            $data['rounding_mode'] = $roundingMode;

            if ($roundingMode === 'none') {
                $data['rounding_step'] = 1;
            } elseif ($request->has('rounding_step')) {
                $data['rounding_step'] = $request->filled('rounding_step')
                    ? (float) $request->input('rounding_step')
                    : 1;
            }
        } elseif ($request->has('rounding_step')) {
            $data['rounding_step'] = $request->filled('rounding_step')
                ? (float) $request->input('rounding_step')
                : 1;
        }

        if ($request->has('terms')) {
            $data['terms'] = $request->filled('terms')
                ? trim((string) $request->input('terms'))
                : null;
        }

        // Existing slug bina zarurat change nahi hoga.
        if (
            empty($business->slug) &&
            filled($data['name'] ?? ($business->name ?? null))
        ) {
            $nameForSlug = $data['name'] ?? $business->name;
            $baseSlug = Str::slug($nameForSlug);

            if ($baseSlug === '') {
                $baseSlug = 'business-' . ($business->id ?: Str::lower(Str::random(8)));
            }

            $slug = $baseSlug;
            $counter = 1;

            while (
                Business::query()
                    ->where('slug', $slug)
                    ->when(
                        $business->exists,
                        fn ($query) => $query->where('id', '!=', $business->id)
                    )
                    ->exists()
            ) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $data['slug'] = $slug;
        }

        // File remove/replace actions.
        if ($request->boolean('remove_logo') && filled($business->logo)) {
            if (Storage::disk('public')->exists($business->logo)) {
                Storage::disk('public')->delete($business->logo);
            }
            $data['logo'] = null;
        }

        if ($request->hasFile('logo')) {
            $oldLogo = $business->logo;
            $data['logo'] = $request->file('logo')->store('business_logos', 'public');

            if (filled($oldLogo) && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
        }

        if ($request->boolean('remove_signature') && filled($business->signature)) {
            if (Storage::disk('public')->exists($business->signature)) {
                Storage::disk('public')->delete($business->signature);
            }
            $data['signature'] = null;
        }

        if ($request->hasFile('signature')) {
            $oldSignature = $business->signature;
            $data['signature'] = $request->file('signature')->store('business_signatures', 'public');

            if (filled($oldSignature) && Storage::disk('public')->exists($oldSignature)) {
                Storage::disk('public')->delete($oldSignature);
            }
        }

        if ($request->boolean('remove_letter_head') && filled($business->letter_head)) {
            if (Storage::disk('public')->exists($business->letter_head)) {
                Storage::disk('public')->delete($business->letter_head);
            }
            $data['letter_head'] = null;
        }

        if ($request->hasFile('letter_head')) {
            $oldLetterHead = $business->letter_head;
            $data['letter_head'] = $request->file('letter_head')->store('business_letter_heads', 'public');

            if (filled($oldLetterHead) && Storage::disk('public')->exists($oldLetterHead)) {
                Storage::disk('public')->delete($oldLetterHead);
            }
        }

        if ($isCreating) {
            if (!filled($data['name'] ?? null)) {
                return back()->withInput()->withErrors([
                    'name' => 'Business create karne ke liye business name enter kijiye.',
                ]);
            }

            if (!filled($data['mobile'] ?? null)) {
                $data['mobile'] = $user->phone ?: null;
            }

            if (empty($data['type'])) {
                $data['type'] = BusinessType::query()->orderBy('id')->value('id');
            }

            if (empty($data['type'])) {
                return back()->withInput()->withErrors([
                    'type' => 'Admin panel se kam se kam ek business type create kijiye.',
                ]);
            }

            // Create ke defaults sirf new business ke liye.
            $data['gst_enabled'] = $data['gst_enabled'] ?? false;
            $data['invoice_base_prefix'] = $data['invoice_base_prefix'] ?? 'RV/SL';
            $data['rounding_mode'] = $data['rounding_mode'] ?? 'nearest';
            $data['rounding_step'] = $data['rounding_step'] ?? 1;

            if (empty($data['slug'])) {
                $baseSlug = Str::slug($data['name']);

                if ($baseSlug === '') {
                    $baseSlug = 'business-' . Str::lower(Str::random(8));
                }

                $slug = $baseSlug;
                $counter = 1;

                while (Business::query()->where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }

                $data['slug'] = $slug;
            }

            $business = Business::query()->create($data);

            DB::table('business_user')->updateOrInsert(
                [
                    'business_id' => $business->id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => 'owner',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $user->current_business_id = $business->id;
            $user->save();
        } else {
            // Empty $data ho to unnecessary UPDATE query bhi nahi chalegi.
            if (!empty($data)) {
                $business->update($data);
            }
        }

        $business->refresh();
        $business->refreshProfileCompletion();
        $business->refresh();

        session([
            'active_business_id' => $business->id,
            'active_business_name' => $business->name ?? 'Business',
        ]);

        $pendingPlanId = session('pending_registration_plan_id');
        $pendingTrial = (int) session('pending_registration_trial', 0) === 1;
        $pendingPaymentDone = (int) session('pending_registration_payment_done', 0) === 1;

        if ($pendingPlanId && ($pendingTrial || $pendingPaymentDone)) {
            $plan = Plan::query()->with('permissions')->find($pendingPlanId);

            if ($plan) {
                UserPlan::query()
                    ->where('business_id', $business->id)
                    ->where('status', 1)
                    ->update(['status' => 0]);

                UserPlan::query()->create([
                    'business_id' => $business->id,
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'start_date' => Carbon::today(),
                    'expiry_date' => Carbon::today()->addDays((int) ($plan->duration_days ?? 30)),
                    'status' => 1,
                ]);

                app(PermissionRegistrar::class)->forgetCachedPermissions();

                $permissions = $plan
                    ->permissions()
                    ->where('guard_name', 'web')
                    ->pluck('name')
                    ->toArray();

                if (!empty($permissions)) {
                    $user->syncPermissions($permissions);
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
     * Invoice template ko alag request se select karega.
     */
    public function selectTemplate(
        Request $request,
        BillTemplate $billTemplate
    ) {
        $business = $this->resolveBusiness(
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

        $isTemplateAllowed = BillTemplate::query()
            ->whereKey($billTemplate->id)
            ->where(function ($query) use ($business) {
                $query->whereNull('business_type_id')
                    ->orWhere('business_type_id', (int) $business->type);
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
            'pdf_template_id' => $billTemplate->id,
        ]);

        $business->refreshProfileCompletion();
        $business->refresh();

        return response()->json([
            'success' => true,
            'message' =>
                'Invoice template selected successfully.',
            'template_id' => $billTemplate->id,
            'completion' =>
                $business->profile_completion,
        ]);
    }

    /**
     * Profile suggestion dismiss karega.
     */
    public function dismissSuggestion(Request $request)
    {
        $business = $this->resolveBusiness(
            $request,
            true
        );

        $business->update([
            'profile_suggestion_dismissed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Current active business nikalega.
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
            ?? $user->businesses()
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
            $user->hasRole('super_admin') ||
            $user->hasRole('super admin');

        $canViewAllBusinesses =
            $user->can('view all businesses');

        if (
            !$isSuperAdmin &&
            !$canViewAllBusinesses
        ) {
            $hasAccess = $user
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
                        (int) $user->current_business_id
                        === (int) $activeBusinessId
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

        $business = Business::query()
            ->find($activeBusinessId);

        if (!$business && $required) {
            abort(
                404,
                'Business not found.'
            );
        }

        return $business;
    }
}