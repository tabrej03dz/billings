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
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $user = $request->user();

        if (
            $user->hasRole('super admin')
            || $user->can('view all businesses')
        ) {
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


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        Business $business
    ) {
        $this->authorizeBusinessAccess(
            $request,
            $business
        );

        return response()->json([
            'status' => true,
            'message' => 'Business fetched',
            'data' => $business->load([
                'businessType',
                'billTemplate',
            ]),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | - Final Business Type ID businesses.type column me save hogi.
    | - business_type_id sirf API compatibility input hai.
    | - pdf_template_id bill_templates.id hoga.
    |
    */

    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Normalize boolean values
        |--------------------------------------------------------------------------
        */

        $request->merge([
            'gst_enabled' =>
                $request->boolean('gst_enabled'),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

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
                'nullable',
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
            | Bill Template
            |--------------------------------------------------------------------------
            */

            'pdf_template_id' => [
                'nullable',
                'integer',
                'exists:bill_templates,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Business Type
            |--------------------------------------------------------------------------
            |
            | Canonical DB field = businesses.type
            |
            */

            'type' => [
                'nullable',
                'integer',
                'exists:business_types,id',
            ],

            /*
             * Flutter / old API compatibility.
             * Isko create() se pehle type me map karke unset karenge.
             */
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
        | Map Business Type
        |--------------------------------------------------------------------------
        |
        | Priority:
        | 1. business_type_id
        | 2. type
        |
        | Final save businesses.type me hi hoga.
        |
        */

        if ($request->filled('business_type_id')) {

            $data['type'] =
                (int) $request->input(
                    'business_type_id'
                );

        } elseif ($request->filled('type')) {

            $data['type'] =
                (int) $request->input('type');

        } else {

            unset($data['type']);
        }

        unset($data['business_type_id']);

        /*
        |--------------------------------------------------------------------------
        | Bill Template Safety
        |--------------------------------------------------------------------------
        */

        if (
            array_key_exists(
                'pdf_template_id',
                $data
            )
        ) {
            if (
                $data['pdf_template_id'] === null
                || $data['pdf_template_id'] === ''
            ) {
                unset($data['pdf_template_id']);
            } else {
                $data['pdf_template_id'] =
                    (int) $data['pdf_template_id'];
            }
        }

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

        while (
            Business::where(
                'slug',
                $slug
            )->exists()
        ) {
            $slug =
                $baseSlug . '-' . $counter;

            $counter++;
        }

        $data['slug'] = $slug;

        /*
        |--------------------------------------------------------------------------
        | Billing Defaults
        |--------------------------------------------------------------------------
        */

        $data['invoice_base_prefix'] =
            filled(
                $data['invoice_base_prefix']
                ?? null
            )
                ? strtoupper(
                    trim(
                        $data[
                            'invoice_base_prefix'
                        ]
                    )
                )
                : 'INV';

        $data['rounding_mode'] =
            $data['rounding_mode']
            ?? 'none';

        if (
            $data['rounding_mode']
            === 'none'
        ) {
            $data['rounding_step'] =
                1.00;
        } else {
            $data['rounding_step'] =
                $data['rounding_step']
                ?? 1.00;
        }

        /*
        |--------------------------------------------------------------------------
        | GST Handling
        |--------------------------------------------------------------------------
        */

        if (!$data['gst_enabled']) {
            $data['gstin'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Upload files
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('logo')) {
            $data['logo'] =
                $request
                    ->file('logo')
                    ->store(
                        'business_logos',
                        'public'
                    );
        }

        if ($request->hasFile('signature')) {
            $data['signature'] =
                $request
                    ->file('signature')
                    ->store(
                        'business_signatures',
                        'public'
                    );
        }

        if (
            $request->hasFile(
                'letter_head'
            )
        ) {
            $data['letter_head'] =
                $request
                    ->file('letter_head')
                    ->store(
                        'business_letter_heads',
                        'public'
                    );
        }

        /*
        |--------------------------------------------------------------------------
        | User assignment
        |--------------------------------------------------------------------------
        */

        $assignUserId =
            !empty($data['user_id'])
                ? (int) $data['user_id']
                : (int) $request
                    ->user()
                    ->id;

        unset($data['user_id']);

        DB::beginTransaction();

        try {

            $business =
                Business::create($data);

            DB::table(
                'business_user'
            )->updateOrInsert(
                [
                    'business_id' =>
                        $business->id,

                    'user_id' =>
                        $assignUserId,
                ],
                [
                    'role' => 'owner',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $business
                ->refreshProfileCompletion();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' =>
                    'Business and billing setup saved successfully.',

                'data' =>
                    $business->fresh([
                        'businessType',
                        'billTemplate',
                    ]),
            ], 201);

        } catch (\Throwable $exception) {

            DB::rollBack();

            foreach (
                [
                    'logo',
                    'signature',
                    'letter_head',
                ] as $field
            ) {
                if (!empty($data[$field])) {
                    Storage::disk(
                        'public'
                    )->delete(
                        $data[$field]
                    );
                }
            }

            report($exception);

            return response()->json([
                'status' => false,
                'message' =>
                    'Business save nahi ho saka.',

                'error' =>
                    config('app.debug')
                        ? $exception->getMessage()
                        : 'Internal server error.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    |
    | Partial update safe:
    | Missing/null/empty business type ya bill template existing value ko
    | accidental NULL nahi karega.
    |
    */

    public function update(
        Request $request,
        Business $business
    ) {
        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        $this->authorizeBusinessAccess(
            $request,
            $business
        );

        /*
        |--------------------------------------------------------------------------
        | Normalize boolean
        |--------------------------------------------------------------------------
        */

        if ($request->has('gst_enabled')) {
            $request->merge([
                'gst_enabled' =>
                    $request->boolean(
                        'gst_enabled'
                    ),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize null-like values BEFORE validation
        |--------------------------------------------------------------------------
        |
        | Flutter kabhi:
        | null
        | ""
        | "null"
        | bhej sakta hai.
        |
        */

        foreach (
            [
                'type',
                'business_type_id',
                'pdf_template_id',
            ] as $field
        ) {
            if ($request->exists($field)) {

                $value =
                    $request->input($field);

                if (
                    $value === null
                    || $value === ''
                    || (
                        is_string($value)
                        && strtolower(
                            trim($value)
                        ) === 'null'
                    )
                ) {
                    $request->request
                        ->remove($field);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([

            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'alpha_dash',
                'max:255',
                Rule::unique(
                    'businesses',
                    'slug'
                )->ignore(
                    $business->id
                ),
            ],

            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique(
                    'businesses',
                    'email'
                )->ignore(
                    $business->id
                ),
            ],

            'mobile' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                Rule::unique(
                    'businesses',
                    'mobile'
                )->ignore(
                    $business->id
                ),
            ],

            'gstin' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
            ],

            'address' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],

            'gst_enabled' => [
                'sometimes',
                'boolean',
            ],

            'invoice_base_prefix' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
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
                'max:999999.99',
            ],

            'terms' => [
                'sometimes',
                'nullable',
                'string',
                'max:5000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Bill Template
            |--------------------------------------------------------------------------
            */

            'pdf_template_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:bill_templates,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Business Type
            |--------------------------------------------------------------------------
            */

            'type' => [
                'sometimes',
                'required',
                'integer',
                'exists:business_types,id',
            ],

            /*
             * Compatibility input.
             */
            'business_type_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:business_types,id',
            ],

            'state' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'state_code' => [
                'sometimes',
                'nullable',
                'string',
                'max:10',
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
        ]);

        /*
        |--------------------------------------------------------------------------
        | Business Type Mapping
        |--------------------------------------------------------------------------
        |
        | Final save ALWAYS businesses.type.
        |
        */

        if (
            array_key_exists(
                'business_type_id',
                $data
            )
        ) {
            $data['type'] =
                (int) $data[
                    'business_type_id'
                ];

            unset(
                $data['business_type_id']
            );

        } elseif (
            array_key_exists(
                'type',
                $data
            )
        ) {
            $data['type'] =
                (int) $data['type'];
        }

        /*
        |--------------------------------------------------------------------------
        | Protect existing business type
        |--------------------------------------------------------------------------
        */

        if (
            array_key_exists(
                'type',
                $data
            )
            && (
                $data['type'] === null
                || $data['type'] === ''
            )
        ) {
            unset($data['type']);
        }

        /*
        |--------------------------------------------------------------------------
        | Protect existing Bill Template
        |--------------------------------------------------------------------------
        */

        if (
            array_key_exists(
                'pdf_template_id',
                $data
            )
        ) {
            if (
                $data[
                    'pdf_template_id'
                ] === null
                || $data[
                    'pdf_template_id'
                ] === ''
            ) {
                unset(
                    $data[
                        'pdf_template_id'
                    ]
                );
            } else {
                $data[
                    'pdf_template_id'
                ] =
                    (int) $data[
                        'pdf_template_id'
                    ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Slug handling
        |--------------------------------------------------------------------------
        */

        if (
            array_key_exists(
                'name',
                $data
            )
            && trim(
                (string) $data['name']
            ) !== trim(
                (string) $business->name
            )
        ) {
            $baseSlug =
                Str::slug(
                    $data['name']
                );

            if ($baseSlug === '') {
                $baseSlug = 'business';
            }

            $slug = $baseSlug;
            $counter = 1;

            while (
                Business::where(
                    'slug',
                    $slug
                )
                    ->where(
                        'id',
                        '!=',
                        $business->id
                    )
                    ->exists()
            ) {
                $slug =
                    $baseSlug
                    . '-'
                    . $counter;

                $counter++;
            }

            $data['slug'] =
                $slug;

        } elseif (
            !$request->filled('slug')
        ) {
            unset($data['slug']);

        } elseif (
            array_key_exists(
                'slug',
                $data
            )
        ) {
            $data['slug'] =
                Str::slug(
                    $data['slug']
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Billing values
        |--------------------------------------------------------------------------
        */

        if (
            array_key_exists(
                'invoice_base_prefix',
                $data
            )
        ) {
            $data[
                'invoice_base_prefix'
            ] =
                filled(
                    $data[
                        'invoice_base_prefix'
                    ]
                )
                    ? strtoupper(
                        trim(
                            $data[
                                'invoice_base_prefix'
                            ]
                        )
                    )
                    : 'INV';
        }

        if (
            array_key_exists(
                'rounding_mode',
                $data
            )
            && $data[
                'rounding_mode'
            ] === 'none'
        ) {
            $data[
                'rounding_step'
            ] = 1.00;
        }

        /*
        |--------------------------------------------------------------------------
        | GST handling
        |--------------------------------------------------------------------------
        */

        if (
            array_key_exists(
                'gst_enabled',
                $data
            )
            && $data[
                'gst_enabled'
            ] === false
        ) {
            $data['gstin'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | File handling
        |--------------------------------------------------------------------------
        */

        $oldFiles = [
            'logo' =>
                $business->logo,

            'signature' =>
                $business->signature,

            'letter_head' =>
                $business->letter_head,
        ];

        $newUploadedFiles = [];

        DB::beginTransaction();

        try {

            if (
                $request->hasFile(
                    'logo'
                )
            ) {
                $data['logo'] =
                    $request
                        ->file('logo')
                        ->store(
                            'business_logos',
                            'public'
                        );

                $newUploadedFiles[] =
                    $data['logo'];
            }

            if (
                $request->hasFile(
                    'signature'
                )
            ) {
                $data['signature'] =
                    $request
                        ->file(
                            'signature'
                        )
                        ->store(
                            'business_signatures',
                            'public'
                        );

                $newUploadedFiles[] =
                    $data['signature'];
            }

            if (
                $request->hasFile(
                    'letter_head'
                )
            ) {
                $data['letter_head'] =
                    $request
                        ->file(
                            'letter_head'
                        )
                        ->store(
                            'business_letter_heads',
                            'public'
                        );

                $newUploadedFiles[] =
                    $data['letter_head'];
            }

            /*
            |--------------------------------------------------------------------------
            | Final update
            |--------------------------------------------------------------------------
            */

            if (!empty($data)) {
                $business->update($data);
            }

            $business
                ->refreshProfileCompletion();

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Delete replaced old files AFTER commit
            |--------------------------------------------------------------------------
            */

            foreach (
                [
                    'logo',
                    'signature',
                    'letter_head',
                ] as $field
            ) {
                if (
                    $request->hasFile(
                        $field
                    )
                    && filled(
                        $oldFiles[$field]
                    )
                    && Storage::disk(
                        'public'
                    )->exists(
                        $oldFiles[$field]
                    )
                ) {
                    Storage::disk(
                        'public'
                    )->delete(
                        $oldFiles[$field]
                    );
                }
            }

            return response()->json([
                'status' => true,
                'message' =>
                    'Business updated successfully.',

                'data' =>
                    $business->fresh([
                        'businessType',
                        'billTemplate',
                    ]),
            ], 200);

        } catch (\Throwable $exception) {

            DB::rollBack();

            foreach (
                $newUploadedFiles
                as $uploadedFile
            ) {
                if (
                    filled($uploadedFile)
                    && Storage::disk(
                        'public'
                    )->exists(
                        $uploadedFile
                    )
                ) {
                    Storage::disk(
                        'public'
                    )->delete(
                        $uploadedFile
                    );
                }
            }

            report($exception);

            return response()->json([
                'status' => false,
                'message' =>
                    'Business update nahi ho saka.',

                'error' =>
                    config('app.debug')
                        ? $exception->getMessage()
                        : 'Internal server error.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        Business $business
    ) {
        $this->authorizeBusinessAccess(
            $request,
            $business
        );

        foreach (
            [
                'logo',
                'signature',
                'letter_head',
            ] as $field
        ) {
            if (
                filled(
                    $business->{$field}
                )
            ) {
                Storage::disk(
                    'public'
                )->delete(
                    $business->{$field}
                );
            }
        }

        $business->delete();

        return response()->json([
            'status' => true,
            'message' =>
                'Business deleted successfully.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | AUTHORIZE BUSINESS ACCESS
    |--------------------------------------------------------------------------
    */

    private function authorizeBusinessAccess(
        Request $request,
        Business $business
    ): void {
        $user = $request->user();

        abort_unless(
            $user,
            401,
            'Unauthenticated.'
        );

        /*
         * Super admin / global permission
         */
        if (
            $user->hasRole(
                'super admin'
            )
            || $user->hasRole(
                'super_admin'
            )
            || $user->can(
                'view all businesses'
            )
        ) {
            return;
        }

        /*
         * Pivot membership
         */
        $pivotMembershipExists =
            DB::table(
                'business_user'
            )
                ->where(
                    'business_id',
                    $business->id
                )
                ->where(
                    'user_id',
                    $user->id
                )
                ->exists();

        /*
         * Direct business assignment
         */
        $matchesBusinessId =
            !is_null(
                $user->business_id
            )
            && (int) $user->business_id
                ===
                (int) $business->id;

        /*
         * Current selected business
         */
        $matchesCurrentBusinessId =
            !is_null(
                $user->current_business_id
            )
            && (int)
                $user->current_business_id
                ===
                (int) $business->id;

        $hasAccess =
            $pivotMembershipExists
            || $matchesBusinessId
            || $matchesCurrentBusinessId;

        abort_unless(
            $hasAccess,
            403,
            'Aap is business se belong nahi karte hain.'
        );
    }
}
