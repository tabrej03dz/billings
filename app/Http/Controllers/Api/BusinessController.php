<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('super admin') || $user->can('view all businesses')) {
            $businesses = Business::latest()->get();
        } else {
            $businesses = $user->businesses()
                ->withPivot('role')
                ->latest('business_user.created_at')
                ->get();
        }

        return response()->json([
            'status' => true,
            'message' => 'Businesses fetched',
            'data' => $businesses,
        ]);
    }

    public function show(Request $request, Business $business)
    {
        $this->authorizeBusinessAccess($request, $business);

        return response()->json([
            'status' => true,
            'message' => 'Business fetched',
            'data' => $business,
        ]);
    }

    // public function store(Request $request)
    // {

    //     // ✅ IMPORTANT: store() me $business variable exist nahi karta, isliye ignore hata diya.
    //     $data = $request->validate([
    //         'name'    => ['required', 'string', 'max:255'],
    //         'slug'    => ['nullable', 'alpha_dash', 'max:255', 'unique:businesses,slug'],
    //         'email'   => ['required', 'email', 'max:255', 'unique:businesses,email'],

    //         'mobile'  => ['nullable', 'string', 'max:20', 'unique:businesses,mobile'],
    //         'gstin'   => ['nullable', 'string', 'max:50'],
    //         'address' => ['nullable', 'string', 'max:1000'],
    //         'terms'   => ['nullable', 'string', 'max:1000'],

    //         'logo'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    //         'signature'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    //         'letter_head'=> ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

    //         'pdf_template_id' => ['nullable', 'string', 'max:100'],
    //         'type' => ['nullable', 'string', 'max:100'], // optional in store (aap chahe to required kar do)
    //         'state' => ['nullable', 'string', 'max:100'],
    //         'state_code' => ['nullable', 'string', 'max:100'],
    //     ]);



    //     // Slug
    //     $data['slug'] = Str::slug($data['name']);

    //     if (Business::where('slug', $data['slug'])->exists()) {
    //         $data['slug'] .= '-' . Str::lower(Str::random(6));
    //     }

    //     // Files
    //     if ($request->hasFile('logo')) {
    //         $data['logo'] = $request->file('logo')->store('business_logos', 'public');
    //     }
    //     if ($request->hasFile('signature')) {
    //         $data['signature'] = $request->file('signature')->store('business_signatures', 'public');
    //     }
    //     if ($request->hasFile('letter_head')) {
    //         $data['letter_head'] = $request->file('letter_head')->store('business_letter_heads', 'public');
    //     }


    //     $business = Business::create($data);

    //     //        // Attach current user as OWNER
    //     //        $request->user()->businesses()->syncWithoutDetaching([
    //     //            $business->id => ['role' => 'owner']
    //     //        ]);

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Business created successfully.',
    //         'data' => $business,
    //     ], 201);
    // }


    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'name'    => ['required', 'string', 'max:255'],
    //         'slug'    => ['nullable', 'alpha_dash', 'max:255', 'unique:businesses,slug'],
    //         'email'   => ['required', 'email', 'max:255', 'unique:businesses,email'],
    //         'mobile'  => ['nullable', 'string', 'max:20', 'unique:businesses,mobile'],
    //         'gstin'   => ['nullable', 'string', 'max:50'],
    //         'address' => ['nullable', 'string', 'max:1000'],
    //         'terms'   => ['nullable', 'string', 'max:1000'],

    //         'logo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    //         'signature'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    //         'letter_head' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

    //         'pdf_template_id' => ['nullable', 'string', 'max:100'],
    //         'type'       => ['nullable', 'string', 'max:100'],
    //         'state'      => ['nullable', 'string', 'max:100'],
    //         'state_code' => ['nullable', 'string', 'max:100'],
    //         'user_id'    => ['nullable', 'exists:users,id'],
    //     ]);

    //     $data['slug'] = Str::slug($data['name']);

    //     if (Business::where('slug', $data['slug'])->exists()) {
    //         $data['slug'] .= '-' . Str::lower(Str::random(6));
    //     }

    //     if ($request->hasFile('logo')) {
    //         $data['logo'] = $request->file('logo')->store('business_logos', 'public');
    //     }

    //     if ($request->hasFile('signature')) {
    //         $data['signature'] = $request->file('signature')->store('business_signatures', 'public');
    //     }

    //     if ($request->hasFile('letter_head')) {
    //         $data['letter_head'] = $request->file('letter_head')->store('business_letter_heads', 'public');
    //     }

    //     $business = Business::create($data);

    //     $assignUserId = $request->filled('user_id')
    //         ? $request->user_id
    //         : auth()->id();

    //     DB::table('business_user')->updateOrInsert(
    //         [
    //             'business_id' => $business->id,
    //             'user_id'     => $assignUserId,
    //         ],
    //         [
    //             'role'       => 'owner',
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]
    //     );

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Business created successfully.',
    //         'data'    => $business,
    //     ], 201);
    // }


    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Normalize mobile-app values
        |--------------------------------------------------------------------------
        |
        | Mobile app kabhi bool ko true/false, 1/0 ya string ke roop me bhej
        | sakti hai. merge() se GST value proper boolean ban jayegi.
        |
        */

        $request->merge([
            'gst_enabled' => $request->boolean('gst_enabled'),
        ]);

        $data = $request->validate([
            /*
            |--------------------------------------------------------------------------
            | Basic business details
            |--------------------------------------------------------------------------
            */

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
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
                'unique:businesses,mobile',
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

            /*
            |--------------------------------------------------------------------------
            | Billing setup fields
            |--------------------------------------------------------------------------
            */

            'gst_enabled' => [
                'required',
                'boolean',
            ],

            'invoice_base_prefix' => [
                'nullable',
                'string',
                'max:50',
            ],

            'rounding_mode' => [
                'required',
                Rule::in([
                    'none',
                    'nearest',
                    'up',
                    'down',
                ]),
            ],

            'rounding_step' => [
                'required_unless:rounding_mode,none',
                'nullable',
                'numeric',
                'min:0.01',
                'max:999999.99',
            ],

            'terms' => [
                'nullable',
                'string',
                'max:5000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Other business fields
            |--------------------------------------------------------------------------
            */

            'pdf_template_id' => [
                'nullable',
                'string',
                'max:100',
            ],

            'type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'business_type_id' => [
                'nullable',
                'integer',
                'exists:business_types,id',
            ],

            'state' => [
                'nullable',
                'string',
                'max:100',
            ],

            'state_code' => [
                'nullable',
                'string',
                'max:10',
            ],

            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Files
            |--------------------------------------------------------------------------
            */

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
                'max:4096',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Generate unique slug
        |--------------------------------------------------------------------------
        */

        $baseSlug = Str::slug(
            $request->filled('slug')
                ? $request->input('slug')
                : $data['name']
        );

        if ($baseSlug === '') {
            $baseSlug = 'business';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (Business::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $data['slug'] = $slug;

        /*
        |--------------------------------------------------------------------------
        | Billing defaults
        |--------------------------------------------------------------------------
        */

        $data['invoice_base_prefix'] = filled(
            $data['invoice_base_prefix'] ?? null
        )
            ? strtoupper(trim($data['invoice_base_prefix']))
            : 'INV';

        $data['rounding_mode'] = $data['rounding_mode'] ?? 'none';

        if ($data['rounding_mode'] === 'none') {
            $data['rounding_step'] = 1.00;
        } else {
            $data['rounding_step'] = $data['rounding_step'] ?? 1.00;
        }

        /*
        |--------------------------------------------------------------------------
        | Conditional GST values
        |--------------------------------------------------------------------------
        */

        if (!$data['gst_enabled']) {
            $data['gstin'] = null;

            // GST disabled ho to state/state code ko optional rakha gaya hai.
            $data['state'] = $data['state'] ?? null;
            $data['state_code'] = $data['state_code'] ?? null;
        }

        /*
        |--------------------------------------------------------------------------
        | Store uploaded files
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('logo')) {
            $data['logo'] = $request
                ->file('logo')
                ->store('business_logos', 'public');
        }

        if ($request->hasFile('signature')) {
            $data['signature'] = $request
                ->file('signature')
                ->store('business_signatures', 'public');
        }

        if ($request->hasFile('letter_head')) {
            $data['letter_head'] = $request
                ->file('letter_head')
                ->store('business_letter_heads', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | user_id business table me save nahi karna
        |--------------------------------------------------------------------------
        |
        | user_id pivot table business_user ke liye hai. Agar businesses table
        | me user_id column nahi hai to create() se pehle ise remove karna zaroori
        | hai.
        |
        */

        $assignUserId = !empty($data['user_id'])
            ? (int) $data['user_id']
            : (int) $request->user()->id;

        unset($data['user_id']);

        DB::beginTransaction();

        try {
            $business = Business::create($data);

            DB::table('business_user')->updateOrInsert(
                [
                    'business_id' => $business->id,
                    'user_id' => $assignUserId,
                ],
                [
                    'role' => 'owner',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Business and billing setup saved successfully.',
                'data' => $business->fresh(),
            ], 201);
        } catch (\Throwable $exception) {
            DB::rollBack();

            /*
            |--------------------------------------------------------------------------
            | Uploaded files rollback
            |--------------------------------------------------------------------------
            */

            foreach (['logo', 'signature', 'letter_head'] as $field) {
                if (!empty($data[$field])) {
                    Storage::disk('public')->delete($data[$field]);
                }
            }

            report($exception);

            return response()->json([
                'status' => false,
                'message' => 'Business save nahi ho saka.',
                'error' => config('app.debug')
                    ? $exception->getMessage()
                    : 'Internal server error.',
            ], 500);
        }
    }

    // public function update(Request $request, Business $business)
    // {
    //     $data = $request->validate([
    //         'name'    => ['required', 'string', 'max:255'],

    //         // slug request se optional hai, par aap generate kar rahe ho -> isko ignore bhi kar sakte ho
    //         // agar user slug bhej hi nahi raha, to is field ko hata bhi sakte ho
    //         'slug'    => ['nullable', 'alpha_dash', 'max:255', Rule::unique('businesses', 'slug')->ignore($business->id)],

    //         'email'   => ['required', 'email', 'max:255', Rule::unique('businesses', 'email')->ignore($business->id)],
    //         'mobile'  => ['nullable', 'string', 'max:20', Rule::unique('businesses', 'mobile')->ignore($business->id)],

    //         'gstin'   => ['nullable', 'string', 'max:50'],
    //         'address' => ['nullable', 'string', 'max:1000'],
    //         'terms'   => ['nullable', 'string', 'max:1000'],

    //         'logo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    //         'signature'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    //         'letter_head' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

    //         'pdf_template_id' => ['nullable', 'string', 'max:100'],
    //         'type'            => ['nullable', 'string', 'max:100'],
    //         'state'           => ['nullable', 'string', 'max:100'],
    //         'state_code'      => ['nullable', 'string', 'max:100'],
    //     ]);

    //     // ✅ Slug (request se nahi lena)
    //     // NOTE: Agar aap chahte ho ki name change hone par hi slug regenerate ho, to condition laga do.
    //     if (isset($data['name']) && $data['name'] !== $business->name) {
    //         $baseSlug = Str::slug($data['name']);
    //         $slug = $baseSlug;
    //         $counter = 1;

    //         while (Business::where('slug', $slug)->where('id', '!=', $business->id)->exists()) {
    //             $slug = $baseSlug . '-' . $counter;
    //             $counter++;
    //         }

    //         $data['slug'] = $slug;
    //     } else {
    //         // name same hai -> existing slug keep
    //         unset($data['slug']); // important: validation me slug tha, but we don't want to overwrite
    //     }

    //     // ✅ Files (replace + old delete)
    //     if ($request->hasFile('logo')) {
    //         if ($business->logo && Storage::disk('public')->exists($business->logo)) {
    //             Storage::disk('public')->delete($business->logo);
    //         }
    //         $data['logo'] = $request->file('logo')->store('business_logos', 'public');
    //     }

    //     if ($request->hasFile('signature')) {
    //         if ($business->signature && Storage::disk('public')->exists($business->signature)) {
    //             Storage::disk('public')->delete($business->signature);
    //         }
    //         $data['signature'] = $request->file('signature')->store('business_signatures', 'public');
    //     }

    //     if ($request->hasFile('letter_head')) {
    //         if ($business->letter_head && Storage::disk('public')->exists($business->letter_head)) {
    //             Storage::disk('public')->delete($business->letter_head);
    //         }
    //         $data['letter_head'] = $request->file('letter_head')->store('business_letter_heads', 'public');
    //     }

    //     // ✅ Update
    //     $business->update($data);

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Business updated successfully.',
    //         'data'    => $business->fresh(),
    //     ], 200);
    // }


public function update(Request $request, Business $business)
{
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthenticated.',
        ], 401);
    }

    /*
    |--------------------------------------------------------------------------
    | Check business access
    |--------------------------------------------------------------------------
    |
    | User ko access tab milega jab:
    |
    | 1. business_user pivot table me membership ho
    | OR
    | 2. users.business_id requested business ke equal ho
    | OR
    | 3. users.current_business_id requested business ke equal ho
    |
    */

    $pivotMembership = DB::table('business_user')
        ->where('business_id', $business->id)
        ->where('user_id', $user->id)
        ->first();

    $belongsThroughBusinessId =
        isset($user->business_id)
        && (int) $user->business_id === (int) $business->id;

    $belongsThroughCurrentBusiness =
        isset($user->current_business_id)
        && (int) $user->current_business_id === (int) $business->id;

    $belongsToBusiness =
        $pivotMembership
        || $belongsThroughBusinessId
        || $belongsThroughCurrentBusiness;

    if (!$belongsToBusiness) {
        return response()->json([
            'status' => false,
            'message' => 'Aap is business se belong nahi karte hain.',
            'requested_business_id' => (int) $business->id,
            'logged_in_user_id' => (int) $user->id,
            'user_business_id' => $user->business_id ?? null,
            'current_business_id' => $user->current_business_id ?? null,
        ], 403);
    }

    /*
    |--------------------------------------------------------------------------
    | Pivot role check
    |--------------------------------------------------------------------------
    |
    | Agar pivot membership available hai to staff ko update se rokna hai.
    | Direct business_id/current_business_id wala user owner maana jayega.
    |
    */

    if (
        $pivotMembership
        && !in_array($pivotMembership->role, ['owner', 'admin'], true)
    ) {
        return response()->json([
            'status' => false,
            'message' => 'Sirf business owner ya admin business update kar sakta hai.',
            'your_business_role' => $pivotMembership->role,
        ], 403);
    }

    if ($request->has('gst_enabled')) {
        $request->merge([
            'gst_enabled' => $request->boolean('gst_enabled'),
        ]);
    }

    $data = $request->validate([
        'name' => [
            'required',
            'string',
            'max:255',
        ],

        'slug' => [
            'nullable',
            'string',
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

        'gst_enabled' => [
            'sometimes',
            'boolean',
        ],

        'invoice_base_prefix' => [
            'nullable',
            'string',
            'max:50',
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
            'min:0.01',
            'max:999999.99',
        ],

        'terms' => [
            'nullable',
            'string',
            'max:5000',
        ],

        'pdf_template_id' => [
            'nullable',
            'integer',
            'exists:bill_templates,id',
        ],

        'type' => [
            'nullable',
            'string',
            'max:100',
        ],

        'business_type_id' => [
            'nullable',
            'integer',
            'exists:business_types,id',
        ],

        'state' => [
            'nullable',
            'string',
            'max:100',
        ],

        'state_code' => [
            'nullable',
            'string',
            'max:10',
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
            'max:4096',
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | Generate slug when business name changes
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists('name', $data)
        && $data['name'] !== $business->name
    ) {
        $baseSlug = Str::slug($data['name']);

        if ($baseSlug === '') {
            $baseSlug = 'business';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            Business::where('slug', $slug)
                ->where('id', '!=', $business->id)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $data['slug'] = $slug;
    } else {
        unset($data['slug']);
    }

    if (array_key_exists('invoice_base_prefix', $data)) {
        $data['invoice_base_prefix'] = filled($data['invoice_base_prefix'])
            ? strtoupper(trim($data['invoice_base_prefix']))
            : 'INV';
    }

    if (
        isset($data['rounding_mode'])
        && $data['rounding_mode'] === 'none'
    ) {
        $data['rounding_step'] = 1.00;
    }

    if (
        array_key_exists('gst_enabled', $data)
        && !$data['gst_enabled']
    ) {
        $data['gstin'] = null;
    }

    $oldFiles = [
        'logo' => $business->logo,
        'signature' => $business->signature,
        'letter_head' => $business->letter_head,
    ];

    $newUploadedFiles = [];

    DB::beginTransaction();

    try {
        if ($request->hasFile('logo')) {
            $data['logo'] = $request
                ->file('logo')
                ->store('business_logos', 'public');

            $newUploadedFiles[] = $data['logo'];
        }

        if ($request->hasFile('signature')) {
            $data['signature'] = $request
                ->file('signature')
                ->store('business_signatures', 'public');

            $newUploadedFiles[] = $data['signature'];
        }

        if ($request->hasFile('letter_head')) {
            $data['letter_head'] = $request
                ->file('letter_head')
                ->store('business_letter_heads', 'public');

            $newUploadedFiles[] = $data['letter_head'];
        }

        $business->update($data);

        $business->refreshProfileCompletion();

        DB::commit();

        foreach (['logo', 'signature', 'letter_head'] as $field) {
            if (
                $request->hasFile($field)
                && !empty($oldFiles[$field])
                && Storage::disk('public')->exists($oldFiles[$field])
            ) {
                Storage::disk('public')->delete($oldFiles[$field]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Business updated successfully.',
            'data' => $business->fresh([
                'businessType',
                'billTemplate',
            ]),
        ], 200);
    } catch (\Throwable $exception) {
        DB::rollBack();

        foreach ($newUploadedFiles as $uploadedFile) {
            if (
                $uploadedFile
                && Storage::disk('public')->exists($uploadedFile)
            ) {
                Storage::disk('public')->delete($uploadedFile);
            }
        }

        report($exception);

        return response()->json([
            'status' => false,
            'message' => 'Business update nahi ho saka.',
            'error' => config('app.debug')
                ? $exception->getMessage()
                : 'Internal server error.',
        ], 500);
    }
}



    public function destroy(Request $request, Business $business)
    {
        $this->authorizeBusinessAccess($request, $business);

        if ($business->logo) Storage::disk('public')->delete($business->logo);
        if ($business->signature) Storage::disk('public')->delete($business->signature);
        if ($business->letter_head) Storage::disk('public')->delete($business->letter_head);

        $business->delete();

        return response()->json([
            'status' => true,
            'message' => 'Business deleted successfully.',
        ]);
    }

    // private function authorizeBusinessAccess(Request $request, Business $business): void
    // {
    //     $user = $request->user();

    //     if ($user->hasRole('super admin') || $user->can('view all businesses')) {
    //         return;
    //     }

    //     $belongs = $user->businesses()->where('businesses.id', $business->id)->exists();
    //     abort_unless($belongs, 403, 'Unauthorized business access.');
    // }

    private function authorizeBusinessAccess(
    Request $request,
    Business $business
): void {
    $user = $request->user();

    abort_unless($user, 401, 'Unauthenticated.');

    $pivotMembershipExists = DB::table('business_user')
        ->where('business_id', $business->id)
        ->where('user_id', $user->id)
        ->exists();

    $belongsThroughBusinessId =
        isset($user->business_id)
        && (int) $user->business_id === (int) $business->id;

    $belongsThroughCurrentBusiness =
        isset($user->current_business_id)
        && (int) $user->current_business_id === (int) $business->id;

    $hasAccess =
        $pivotMembershipExists
        || $belongsThroughBusinessId
        || $belongsThroughCurrentBusiness;

    abort_unless(
        $hasAccess,
        403,
        'Aap is business se belong nahi karte hain.'
    );
}
}
