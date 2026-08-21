<?php

namespace App\Http\Controllers;

use App\Models\BillTemplate;
use App\Models\Business;
use App\Models\BusinessType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;


use App\Models\Plan;
use App\Models\User;
use App\Models\UserPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;


class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $status = $request->query('status');
        $perPage = (int) $request->query('per_page', 15);

        $perPage = in_array($perPage, [10, 15, 25, 50, 100]) ? $perPage : 15;

        if ($request->user()->hasRole('super admin') || $request->user()->can('view all businesses')) {
            $query = Business::query()->latest();
        } else {
            $query = $request->user()
                ->businesses()
                ->withPivot('role')
                ->latest('business_user.created_at');
        }

        $businesses = $query
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('gstin', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', function ($q) {
                $q->where('is_active', 1);
            })
            ->when($status === 'inactive', function ($q) {
                $q->where('is_active', 0);
            })
            ->paginate($perPage)
            ->withQueryString();

        return view('businesses.index', compact('businesses', 'search', 'status', 'perPage'));
    }

    // public function create()
    // {
    //     $billTemplates = BillTemplate::all();

    //     $businessTypes = BusinessType::query()
    //         ->orderBy('name')
    //         ->get();

    //     return view('businesses.create', compact('billTemplates', 'businessTypes'));
    // }


    public function create()
    {
        $billTemplates = BillTemplate::query()
            ->orderBy('name')
            ->get();

        $businessTypes = BusinessType::query()
            ->orderBy('name')
            ->get();

        /*
        * Sirf active plans.
        *
        * Aapke UserPlanController me bhi
        * status = 1 wale plan load ho rahe hain.
        */
        $plans = Plan::query()
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        /*
        * Spatie roles
        */
        $roles = Role::query()
            ->orderBy('name')
            ->get();

        return view('businesses.create', compact(
            'billTemplates',
            'businessTypes',
            'plans',
            'roles'
        ));
    }

    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'name'  => ['required', 'string', 'max:255'],
    //         'slug'  => ['nullable', 'alpha_dash', 'max:255', 'unique:businesses,slug'],
    //         'email' => ['required', 'email', 'max:255', 'unique:businesses,email'],

    //         'mobile' => [
    //             'nullable',
    //             'string',
    //             'max:20',
    //             Rule::unique('businesses', 'mobile'),
    //         ],

    //         // ✅ Dynamic Business Type
    //         // 'business_type_id' => [
    //         //     'required',
    //         //     'integer',
    //         //     Rule::exists('business_types', 'id'),
    //         // ],

    //          'type' => ['required', 'exists:business_types,id'],

    //         'gstin'   => ['nullable', 'string', 'max:50'],
    //         'address' => ['nullable', 'string', 'max:1000'],
    //         'terms'   => ['nullable', 'string', 'max:1000'],

    //         'logo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    //         'signature'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    //         'letter_head' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

    //         'state' => ['required', 'string', 'max:100'],

    //         'pdf_template_id'     => ['required', 'integer', Rule::exists('bill_templates', 'id')],
    //         'invoice_base_prefix' => ['nullable', 'string', 'max:100'],
    //     ]);

    //     // ✅ Split state_code & state_name
    //     if (!empty($data['state']) && str_contains($data['state'], ',')) {
    //         [$stateCode, $stateName] = explode(',', $data['state'], 2);

    //         $data['state_code'] = trim($stateCode);
    //         $data['state'] = trim($stateName);
    //     }

    //     // ✅ Auto-generate slug if empty
    //     $data['slug'] = !empty($data['slug'])
    //         ? Str::slug($data['slug'])
    //         : Str::slug($data['name']);

    //     // ✅ Ensure slug uniqueness
    //     $originalSlug = $data['slug'];
    //     $counter = 1;

    //     while (Business::where('slug', $data['slug'])->exists()) {
    //         $data['slug'] = $originalSlug . '-' . $counter;
    //         $counter++;
    //     }

    //     // ✅ Upload files
    //     if ($request->hasFile('logo')) {
    //         $data['logo'] = $request->file('logo')->store('business_logos', 'public');
    //     }

    //     if ($request->hasFile('signature')) {
    //         $data['signature'] = $request->file('signature')->store('business_signatures', 'public');
    //     }

    //     if ($request->hasFile('letter_head')) {
    //         $data['letter_head'] = $request->file('letter_head')->store('business_letter_heads', 'public');
    //     }

    //     // ✅ Create business
    //     $business = Business::create($data);

    //     // ✅ Attach current user as owner
    //     $request->user()->businesses()->syncWithoutDetaching([
    //         $business->id => ['role' => 'owner'],
    //     ]);

    //     return redirect()
    //         ->route('businesses.index')
    //         ->with('success', 'Business created successfully.');
    // }


    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            /*
            * BUSINESS
            */
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'alpha_dash',
                'max:255',
                'unique:businesses,slug',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:businesses,email',
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('businesses', 'mobile'),
            ],

            'type' => [
                'required',
                'integer',
                Rule::exists('business_types', 'id'),
            ],

            'gstin' => [
                'nullable',
                'string',
                'max:50',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'terms' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'state' => [
                'required',
                'string',
                'max:100',
            ],

            'pdf_template_id' => [
                'required',
                'integer',
                Rule::exists('bill_templates', 'id'),
            ],

            'invoice_base_prefix' => [
                'nullable',
                'string',
                'max:100',
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'signature' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'letter_head' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],


            /*
            * USER
            */
            'user_name' => [
                'required',
                'string',
                'max:255',
            ],

            'user_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'user_phone' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'phone'),
            ],

            'user_google_drive_folder_id' => [
                'nullable',
                Rule::unique('users', 'google_drive_folder_id'),
            ],

            'user_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],


            /*
            * ROLES
            */
            'roles' => [
                'required',
                'array',
                'min:1',
            ],

            'roles.*' => [
                'required',
                'string',
                Rule::exists('roles', 'name'),
            ],


            /*
            * PLAN
            */
            'plan_id' => [
                'required',
                'integer',
                Rule::exists('plans', 'id'),
            ],

            'number_of_office' => [
                'required',
                'integer',
                'min:1',
            ],

            'number_of_user' => [
                'required',
                'integer',
                'min:1',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'expiry_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'plan_status' => [
                'nullable',
                'boolean',
            ],

        ], [

            /*
            * Custom messages
            */
            'user_name.required' =>
                'User name required hai.',

            'user_email.required' =>
                'User login email required hai.',

            'user_email.unique' =>
                'Is email ka user already exist karta hai.',

            'user_phone.required' =>
                'User phone required hai.',

            'user_phone.unique' =>
                'Is phone number ka user already exist karta hai.',

            'user_password.required' =>
                'User password required hai.',

            'user_password.confirmed' =>
                'Password aur confirm password same nahi hai.',

            'user_password.min' =>
                'Password minimum 8 characters ka hona chahiye.',

            'roles.required' =>
                'Kam se kam ek role select karna zaroori hai.',

            'plan_id.required' =>
                'Plan select karna zaroori hai.',

            'number_of_office.required' =>
                'Number of offices required hai.',

            'number_of_office.min' =>
                'Kam se kam 1 office hona chahiye.',

            'number_of_user.required' =>
                'Number of users required hai.',

            'number_of_user.min' =>
                'Kam se kam 1 user hona chahiye.',

            'expiry_date.after_or_equal' =>
                'Expiry date start date se pehle nahi ho sakti.',

        ]);


        /*
        |--------------------------------------------------------------------------
        | PREPARE STATE
        |--------------------------------------------------------------------------
        */

        $stateCode = null;
        $stateName = $validated['state'];

        if (
            !empty($validated['state']) &&
            str_contains($validated['state'], ',')
        ) {

            [$stateCode, $stateName] = explode(
                ',',
                $validated['state'],
                2
            );

            $stateCode = trim($stateCode);
            $stateName = trim($stateName);
        }


        /*
        |--------------------------------------------------------------------------
        | PREPARE SLUG
        |--------------------------------------------------------------------------
        */

        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        /*
        * Agar kisi wajah se empty slug ban gaya
        */
        if (empty($slug)) {
            $slug = 'business';
        }

        $originalSlug = $slug;
        $counter = 1;

        while (
            Business::query()
                ->where('slug', $slug)
                ->exists()
        ) {

            $slug = $originalSlug . '-' . $counter;

            $counter++;
        }


        /*
        |--------------------------------------------------------------------------
        | FILE PATH VARIABLES
        |--------------------------------------------------------------------------
        |
        | Transaction fail hone ki situation me uploaded
        | files cleanup kar sakein.
        |
        */

        $logoPath = null;
        $signaturePath = null;
        $letterHeadPath = null;


        try {

            /*
            |--------------------------------------------------------------------------
            | UPLOAD FILES
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('logo')) {

                $logoPath = $request
                    ->file('logo')
                    ->store(
                        'business_logos',
                        'public'
                    );
            }


            if ($request->hasFile('signature')) {

                $signaturePath = $request
                    ->file('signature')
                    ->store(
                        'business_signatures',
                        'public'
                    );
            }


            if ($request->hasFile('letter_head')) {

                $letterHeadPath = $request
                    ->file('letter_head')
                    ->store(
                        'business_letter_heads',
                        'public'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | SINGLE DATABASE TRANSACTION
            |--------------------------------------------------------------------------
            */

            $result = DB::transaction(function () use (
                $request,
                $validated,
                $slug,
                $stateCode,
                $stateName,
                $logoPath,
                $signaturePath,
                $letterHeadPath
            ) {

                /*
                |--------------------------------------------------------------------------
                | 1. CREATE BUSINESS
                |--------------------------------------------------------------------------
                */

                $business = Business::create([

                    'name' => $validated['name'],

                    'slug' => $slug,

                    'email' => $validated['email'],

                    'mobile' => $validated['mobile'] ?? null,

                    'type' => $validated['type'],

                    'gstin' => $validated['gstin'] ?? null,

                    'address' => $validated['address'] ?? null,

                    'terms' => $validated['terms'] ?? null,

                    'state_code' => $stateCode,

                    'state' => $stateName,

                    'pdf_template_id' =>
                        $validated['pdf_template_id'],

                    'invoice_base_prefix' =>
                        $validated['invoice_base_prefix'] ?? null,

                    'logo' => $logoPath,

                    'signature' => $signaturePath,

                    'letter_head' => $letterHeadPath,
                ]);


                /*
                |--------------------------------------------------------------------------
                | 2. ATTACH CURRENT LOGGED-IN USER
                |--------------------------------------------------------------------------
                |
                | Aapke existing system ka ye behavior preserve kiya gaya hai.
                |
                */

                if ($request->user()) {

                    $request->user()
                        ->businesses()
                        ->syncWithoutDetaching([

                            $business->id => [
                                'role' => 'owner',
                            ],

                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | 3. CREATE NEW USER
                |--------------------------------------------------------------------------
                */

                $user = User::create([

                    'name' =>
                        $validated['user_name'],

                    'email' =>
                        $validated['user_email'],

                    'phone' =>
                        $validated['user_phone'],

                    'google_drive_folder_id' =>
                        $validated['user_google_drive_folder_id'] ?? null,

                    'password' =>
                        Hash::make(
                            $validated['user_password']
                        ),
                ]);


                /*
                |--------------------------------------------------------------------------
                | 4. ATTACH NEW USER TO BUSINESS
                |--------------------------------------------------------------------------
                |
                | Ye newly created user isi business ka owner/member ban jayega.
                |
                */

                $user->businesses()
                    ->syncWithoutDetaching([

                        $business->id => [
                            'role' => 'owner',
                        ],

                    ]);


                /*
                |--------------------------------------------------------------------------
                | 5. SET CURRENT BUSINESS
                |--------------------------------------------------------------------------
                |
                | Agar users table me current_business_id column available hai
                | to automatically set karenge.
                |
                */

                if (
                    \Illuminate\Support\Facades\Schema::hasColumn(
                        'users',
                        'current_business_id'
                    )
                ) {

                    $user->current_business_id = $business->id;

                    $user->save();
                }


                /*
                |--------------------------------------------------------------------------
                | 6. ASSIGN SPATIE ROLE
                |--------------------------------------------------------------------------
                */

                $user->assignRole(
                    $validated['roles']
                );


                /*
                |--------------------------------------------------------------------------
                | 7. GET PLAN WITH PERMISSIONS
                |--------------------------------------------------------------------------
                */

                $plan = Plan::query()
                    ->with('permissions')
                    ->findOrFail(
                        $validated['plan_id']
                    );


                /*
                |--------------------------------------------------------------------------
                | 8. PLAN START DATE
                |--------------------------------------------------------------------------
                */

                $startDate = !empty($validated['start_date'])
                    ? Carbon::parse(
                        $validated['start_date']
                    )->startOfDay()
                    : now()->startOfDay();


                /*
                |--------------------------------------------------------------------------
                | 9. PLAN EXPIRY DATE
                |--------------------------------------------------------------------------
                */

                if (!empty($validated['expiry_date'])) {

                    $expiryDate = Carbon::parse(
                        $validated['expiry_date']
                    )->startOfDay();

                } else {

                    /*
                    * Aapke existing UserPlanController
                    * ke same duration_days logic ko use kar rahe hain.
                    */

                    $durationDays = max(
                        0,
                        (int) ($plan->duration_days ?? 0)
                    );

                    $expiryDate = $startDate
                        ->copy()
                        ->addDays($durationDays);
                }


                /*
                |--------------------------------------------------------------------------
                | 10. CREATE USER PLAN
                |--------------------------------------------------------------------------
                */

                $userPlan = UserPlan::create([

                    'business_id' =>
                        $business->id,

                    'user_id' =>
                        $user->id,

                    'plan_id' =>
                        $plan->id,

                    'number_of_office' =>
                        $validated['number_of_office'],

                    'number_of_user' =>
                        $validated['number_of_user'],

                    'start_date' =>
                        $startDate->toDateString(),

                    'expiry_date' =>
                        $expiryDate->toDateString(),

                    'status' =>
                        $request->has('plan_status')
                            ? $request->boolean('plan_status')
                            : true,
                ]);


                /*
                |--------------------------------------------------------------------------
                | 11. ASSIGN PLAN PERMISSIONS
                |--------------------------------------------------------------------------
                |
                | Same behavior jo UserPlanController me
                | givePermissionTo() se ho raha hai.
                |
                */

                if ($plan->permissions->isNotEmpty()) {

                    $user->givePermissionTo(
                        $plan->permissions
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | 12. CLEAR SPATIE PERMISSION CACHE
                |--------------------------------------------------------------------------
                */

                app(
                    PermissionRegistrar::class
                )->forgetCachedPermissions();


                /*
                * Relation cache clear
                */
                $user->unsetRelation('permissions');
                $user->unsetRelation('roles');


                /*
                |--------------------------------------------------------------------------
                | RETURN CREATED DATA
                |--------------------------------------------------------------------------
                */

                return [

                    'business' => $business,

                    'user' => $user,

                    'userPlan' => $userPlan,

                ];

            });


            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('businesses.index')
                ->with(
                    'success',
                    'Business, user and plan created successfully. Plan permissions and role have also been assigned.'
                );

        } catch (\Throwable $e) {


            /*
            |--------------------------------------------------------------------------
            | REMOVE UPLOADED FILES IF DATABASE FAILED
            |--------------------------------------------------------------------------
            */

            if ($logoPath) {

                Storage::disk('public')
                    ->delete($logoPath);
            }


            if ($signaturePath) {

                Storage::disk('public')
                    ->delete($signaturePath);
            }


            if ($letterHeadPath) {

                Storage::disk('public')
                    ->delete($letterHeadPath);
            }


            /*
            * Error ko Laravel log me store karega.
            */
            report($e);


            return back()
                ->withInput()
                ->with(
                    'error',
                    'Business create nahi ho paya. Error: ' . $e->getMessage()
                );
        }
    }

    // public function edit(Business $business)
    // {
    //     // $this->authorize('update', $business);
    //     $billTemplates = BillTemplate::all();
    //     return view('businesses.edit', compact('business', 'billTemplates'));
    // }


    public function edit(Business $business)
    {
        $billTemplates = BillTemplate::all();

        $businessTypes = BusinessType::query()
            ->orderBy('name')
            ->get();

        return view('businesses.edit', compact(
            'business',
            'billTemplates',
            'businessTypes'
        ));
    }


    public function update(Request $request, Business $business)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'slug' => [
                'nullable',
                'alpha_dash',
                'max:255',
                Rule::unique('businesses', 'slug')->ignore($business->id),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('businesses', 'email')->ignore($business->id),
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('businesses', 'mobile')->ignore($business->id),
            ],

            // ✅ Dynamic Business Type
            'type' => [
                'required',
                'integer',
                Rule::exists('business_types', 'id'),
            ],

            'gstin'   => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'terms'   => ['nullable', 'string', 'max:1000'],

            'logo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'signature'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'letter_head' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            'remove_logo'        => ['nullable', 'boolean'],
            'remove_signature'   => ['nullable', 'boolean'],
            'remove_letter_head' => ['nullable', 'boolean'],

            'state' => ['required', 'string', 'max:100'],

            'pdf_template_id' => [
                'required',
                'integer',
                Rule::exists('bill_templates', 'id'),
            ],

            'invoice_base_prefix' => ['nullable', 'string', 'max:100'],
        ]);

        // ✅ Split state_code & state_name
        if (!empty($data['state']) && str_contains($data['state'], ',')) {
            [$stateCode, $stateName] = explode(',', $data['state'], 2);

            $data['state_code'] = trim($stateCode);
            $data['state'] = trim($stateName);
        }

        // ✅ Slug fallback
        $data['slug'] = !empty($data['slug'])
            ? Str::slug($data['slug'])
            : Str::slug($data['name']);

        // ✅ Ensure slug uniqueness
        $originalSlug = $data['slug'];
        $counter = 1;

        while (
            Business::where('slug', $data['slug'])
                ->where('id', '!=', $business->id)
                ->exists()
        ) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // ✅ Replace/remove logo
        if ($request->boolean('remove_logo') && $business->logo) {
            Storage::disk('public')->delete($business->logo);
            $data['logo'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($business->logo) {
                Storage::disk('public')->delete($business->logo);
            }

            $data['logo'] = $request->file('logo')->store('business_logos', 'public');
        }

        // ✅ Replace/remove signature
        if ($request->boolean('remove_signature') && $business->signature) {
            Storage::disk('public')->delete($business->signature);
            $data['signature'] = null;
        }

        if ($request->hasFile('signature')) {
            if ($business->signature) {
                Storage::disk('public')->delete($business->signature);
            }

            $data['signature'] = $request->file('signature')->store('business_signatures', 'public');
        }

        // ✅ Replace/remove letter head
        if ($request->boolean('remove_letter_head') && $business->letter_head) {
            Storage::disk('public')->delete($business->letter_head);
            $data['letter_head'] = null;
        }

        if ($request->hasFile('letter_head')) {
            if ($business->letter_head) {
                Storage::disk('public')->delete($business->letter_head);
            }

            $data['letter_head'] = $request->file('letter_head')->store('business_letter_heads', 'public');
        }


        $business->update($data);

        return redirect()
            ->route('businesses.index')
            ->with('success', 'Business updated successfully.');
    }

    public function destroy(Business $business)
    {
        // $this->authorize('delete', $business);

        if ($business->logo) {
            Storage::disk('public')->delete($business->logo);
        }
        $business->delete();

        return redirect()
            ->route('businesses.index')
            ->with('success', 'Business deleted successfully.');
    }

}
