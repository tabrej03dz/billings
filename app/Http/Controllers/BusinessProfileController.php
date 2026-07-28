<?php

namespace App\Http\Controllers;

use App\Models\BillTemplate;
use App\Models\Business;
use App\Models\BusinessType;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BusinessProfileController extends Controller
{
    /**
     * Business profile form show karega.
     */
    public function index(Request $request)
    {
        $business = $this->resolveBusiness($request);

        /*
        |--------------------------------------------------------------------------
        | Profile completion refresh
        |--------------------------------------------------------------------------
        */

        $business->refreshProfileCompletion();
        $business->refresh();

        /*
        |--------------------------------------------------------------------------
        | Business types
        |--------------------------------------------------------------------------
        */

        $businessTypes = BusinessType::query()
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Invoice templates
        |--------------------------------------------------------------------------
        */

        $billTemplates = BillTemplate::query()
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Missing profile fields
        |--------------------------------------------------------------------------
        */

        $missingFields = $business->missingProfileFields();
        
          $itemCount = Item::query()
            ->where('business_id', $business->id)
            ->count();

        return view(
            'business-profile.index',
            compact(
                'business',
                'businessTypes',
                'billTemplates',
                'missingFields', 'itemCount'
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
        $business = $this->resolveBusiness($request);

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        |
        | Sabhi fields optional hain.
        |
        */

        $data = $request->validate(
            [
                'name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                /*
                 * Bilkul BusinessController wale pattern par.
                 */
                'type' => [
                    'nullable',
                    'integer',
                    Rule::exists('business_types', 'id'),
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                    Rule::unique('businesses', 'email')
                        ->ignore($business->id),
                ],

                'mobile' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('businesses', 'mobile')
                        ->ignore($business->id),
                ],

                'gst_enabled' => [
                    'nullable',
                    'boolean',
                ],

                'gstin' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'state' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'state_code' => [
                    'nullable',
                    'string',
                    'max:20',
                ],

                'address' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'pdf_template_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('bill_templates', 'id'),
                ],

                'invoice_base_prefix' => [
                    'nullable',
                    'string',
                    'max:100',
                    'regex:/^[A-Za-z0-9_\/-]+$/',
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
                    'max:1000',
                ],

                'terms' => [
                    'nullable',
                    'string',
                    'max:5000',
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

                'remove_logo' => [
                    'nullable',
                    'boolean',
                ],

                'remove_signature' => [
                    'nullable',
                    'boolean',
                ],

                'remove_letter_head' => [
                    'nullable',
                    'boolean',
                ],
            ],
            [
                'type.exists' =>
                    'The selected business type is invalid.',

                'pdf_template_id.exists' =>
                    'The selected invoice template is invalid.',

                'email.email' =>
                    'Please enter a valid email address.',

                'email.unique' =>
                    'This email address is already being used.',

                'mobile.unique' =>
                    'This mobile number is already being used.',

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
        | Business type
        |--------------------------------------------------------------------------
        |
        | Request me business_type_id aayi hai to selected ID save hogi.
        | Empty select hone par null save hoga.
        |
        */

        $data['type'] =
            $request->filled('type')
                ? (int) $request->input('type')
                : null;

        /*
        |--------------------------------------------------------------------------
        | Basic fields normalize
        |--------------------------------------------------------------------------
        */

        $data['name'] = $request->filled('name')
            ? trim((string) $request->input('name'))
            : null;

        $data['email'] = $request->filled('email')
            ? strtolower(
                trim((string) $request->input('email'))
            )
            : null;

        $data['mobile'] = $request->filled('mobile')
            ? trim((string) $request->input('mobile'))
            : null;

        $data['address'] = $request->filled('address')
            ? trim((string) $request->input('address'))
            : null;

        /*
        |--------------------------------------------------------------------------
        | State and state code
        |--------------------------------------------------------------------------
        |
        | Form value:
        | 09,Uttar Pradesh
        |
        | Database:
        | state_code = 09
        | state      = Uttar Pradesh
        |
        */

        $stateInput = trim(
            (string) $request->input('state', '')
        );

        if ($stateInput === '') {
            $data['state'] = null;

            $data['state_code'] =
                $request->filled('state_code')
                    ? trim(
                        (string) $request->input('state_code')
                    )
                    : null;
        } elseif (str_contains($stateInput, ',')) {
            [$stateCode, $stateName] = array_pad(
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

            $data['state_code'] =
                $request->filled('state_code')
                    ? trim(
                        (string) $request->input('state_code')
                    )
                    : null;
        }

        /*
        |--------------------------------------------------------------------------
        | GST
        |--------------------------------------------------------------------------
        */

        $data['gst_enabled'] =
            $request->boolean('gst_enabled');

        $data['gstin'] =
            $data['gst_enabled'] &&
            $request->filled('gstin')
                ? strtoupper(
                    trim((string) $request->input('gstin'))
                )
                : null;

        /*
        |--------------------------------------------------------------------------
        | PDF template
        |--------------------------------------------------------------------------
        */

        $data['pdf_template_id'] =
            $request->filled('pdf_template_id')
                ? (int) $request->input('pdf_template_id')
                : null;

        /*
        |--------------------------------------------------------------------------
        | Invoice prefix
        |--------------------------------------------------------------------------
        */

        $data['invoice_base_prefix'] =
            $request->filled('invoice_base_prefix')
                ? strtoupper(
                    trim(
                        (string) $request->input(
                            'invoice_base_prefix'
                        )
                    )
                )
                : null;

        /*
        |--------------------------------------------------------------------------
        | Rounding settings
        |--------------------------------------------------------------------------
        */

        $data['rounding_mode'] =
            $request->filled('rounding_mode')
                ? (string) $request->input('rounding_mode')
                : 'none';

        if ($data['rounding_mode'] === 'none') {
            $data['rounding_step'] = 1;
        } else {
            $data['rounding_step'] =
                $request->filled('rounding_step')
                    ? (float) $request->input('rounding_step')
                    : 1;
        }

        /*
        |--------------------------------------------------------------------------
        | Terms
        |--------------------------------------------------------------------------
        */

        $data['terms'] =
            $request->filled('terms')
                ? trim((string) $request->input('terms'))
                : null;

        /*
        |--------------------------------------------------------------------------
        | Slug handling
        |--------------------------------------------------------------------------
        |
        | Existing slug ko bina zarurat change nahi karenge.
        | Agar slug empty hai aur name diya hai tab generate hoga.
        |
        */

        if (
            empty($business->slug) &&
            filled($data['name'] ?? null)
        ) {
            $baseSlug = Str::slug($data['name']);

            if ($baseSlug === '') {
                $baseSlug = 'business-' . $business->id;
            }

            $slug = $baseSlug;
            $counter = 1;

            while (
                Business::query()
                    ->where('slug', $slug)
                    ->where('id', '!=', $business->id)
                    ->exists()
            ) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $data['slug'] = $slug;
        }

        /*
        |--------------------------------------------------------------------------
        | Remove action fields
        |--------------------------------------------------------------------------
        |
        | Ye database columns nahi hain.
        |
        */

        unset(
            $data['remove_logo'],
            $data['remove_signature'],
            $data['remove_letter_head']
        );

        /*
        |--------------------------------------------------------------------------
        | Remove logo
        |--------------------------------------------------------------------------
        */

        if (
            $request->boolean('remove_logo') &&
            filled($business->logo)
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

        /*
        |--------------------------------------------------------------------------
        | Replace logo
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('logo')) {
            $oldLogo = $business->logo;

            $data['logo'] = $request
                ->file('logo')
                ->store(
                    'business_logos',
                    'public'
                );

            if (
                filled($oldLogo) &&
                Storage::disk('public')->exists($oldLogo)
            ) {
                Storage::disk('public')->delete($oldLogo);
            }
        } else {
            /*
             * File select nahi ki to existing logo preserve rahega.
             */
            if (!$request->boolean('remove_logo')) {
                unset($data['logo']);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Remove signature
        |--------------------------------------------------------------------------
        */

        if (
            $request->boolean('remove_signature') &&
            filled($business->signature)
        ) {
            if (
                Storage::disk('public')
                    ->exists($business->signature)
            ) {
                Storage::disk('public')
                    ->delete($business->signature);
            }

            $data['signature'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Replace signature
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('signature')) {
            $oldSignature = $business->signature;

            $data['signature'] = $request
                ->file('signature')
                ->store(
                    'business_signatures',
                    'public'
                );

            if (
                filled($oldSignature) &&
                Storage::disk('public')
                    ->exists($oldSignature)
            ) {
                Storage::disk('public')
                    ->delete($oldSignature);
            }
        } else {
            if (!$request->boolean('remove_signature')) {
                unset($data['signature']);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Remove letter head
        |--------------------------------------------------------------------------
        */

        if (
            $request->boolean('remove_letter_head') &&
            filled($business->letter_head)
        ) {
            if (
                Storage::disk('public')
                    ->exists($business->letter_head)
            ) {
                Storage::disk('public')
                    ->delete($business->letter_head);
            }

            $data['letter_head'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Replace letter head
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('letter_head')) {
            $oldLetterHead = $business->letter_head;

            $data['letter_head'] = $request
                ->file('letter_head')
                ->store(
                    'business_letter_heads',
                    'public'
                );

            if (
                filled($oldLetterHead) &&
                Storage::disk('public')
                    ->exists($oldLetterHead)
            ) {
                Storage::disk('public')
                    ->delete($oldLetterHead);
            }
        } else {
            if (!$request->boolean('remove_letter_head')) {
                unset($data['letter_head']);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Business update
        |--------------------------------------------------------------------------
        |
        | Bilkul BusinessController ki tarah direct update.
        | Kisi BusinessType relation ki zarurat nahi hai.
        |
        */

        $business->update($data);

        /*
        |--------------------------------------------------------------------------
        | Fresh business data
        |--------------------------------------------------------------------------
        */

        $business->refresh();

        /*
        |--------------------------------------------------------------------------
        | Profile completion refresh
        |--------------------------------------------------------------------------
        */

        $business->refreshProfileCompletion();
        $business->refresh();

        /*
        |--------------------------------------------------------------------------
        | Active business session update
        |--------------------------------------------------------------------------
        */

        session([
            'active_business_id' => $business->id,
            'active_business_name' =>
                $business->name ?? 'Business',
        ]);

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                'Business profile updated successfully.'
            );
    }

    /**
     * Invoice template ko alag request se select karega.
     */
    public function selectTemplate(
        Request $request,
        BillTemplate $billTemplate
    ) {
        $business = $this->resolveBusiness($request);

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
        $business = $this->resolveBusiness($request);

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
        Request $request
    ): Business {
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

        abort_if(
            !$activeBusinessId,
            404,
            'No business is attached to your account.'
        );

        /*
        |--------------------------------------------------------------------------
        | Super admin access
        |--------------------------------------------------------------------------
        */

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

            abort_unless(
                $hasAccess,
                403,
                'You are not allowed to access this business.'
            );
        }

        return Business::query()
            ->findOrFail($activeBusinessId);
    }
}