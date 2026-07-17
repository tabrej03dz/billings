<?php

namespace App\Http\Controllers;

use App\Models\BillTemplate;
use App\Models\Business;
use App\Models\BusinessType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BusinessProfileController extends Controller
{
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

        $businessTypes = BusinessType::query()
            ->orderBy('name')
            ->get();

        $billTemplates = BillTemplate::query()
            ->orderBy('name')
            ->get();

        $missingFields = $business->missingProfileFields();

        return view('business-profile.index', compact(
            'business',
            'businessTypes',
            'billTemplates',
            'missingFields'
        ));
    }

    public function update(Request $request)
    {
        $business = $this->resolveBusiness($request);

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        |
        | Sirf wahi fields validate ho rahi hain jo aapke businesses table aur
        | Business Profile form me maujood hain.
        |
        */

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'business_type_id' => [
                'required',
                'integer',
                Rule::exists('business_types', 'id'),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('businesses', 'email')
                    ->ignore($business->id),
            ],

            'mobile' => [
                'required',
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
                Rule::requiredIf(
                    $request->boolean('gst_enabled')
                ),
                'nullable',
                'string',
                'max:15',
            ],

            'address' => [
                'required',
                'string',
                'max:1000',
            ],

            'state' => [
                'required',
                'string',
                'max:255',
            ],

            'state_code' => [
                Rule::requiredIf(
                    $request->boolean('gst_enabled')
                ),
                'nullable',
                'digits:2',
            ],

            'pdf_template_id' => [
                'required',
                Rule::exists('bill_templates', 'id'),
            ],

            'invoice_base_prefix' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[A-Za-z0-9_-]+$/',
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
                Rule::requiredIf(
                    $request->input('rounding_mode') !== 'none'
                ),
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
        ], [
            'business_type_id.required' =>
                'Please select a business type.',

            'business_type_id.exists' =>
                'The selected business type is invalid.',

            'pdf_template_id.required' =>
                'Please select an invoice template.',

            'pdf_template_id.exists' =>
                'The selected invoice template is invalid.',

            'gstin.required' =>
                'GST number is required when GST is enabled.',

            'state_code.required' =>
                'State code is required when GST is enabled.',

            'state_code.digits' =>
                'State code must contain exactly 2 digits.',

            'invoice_base_prefix.regex' =>
                'Invoice prefix may contain only letters, numbers, hyphen and underscore.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Normalize request values
        |--------------------------------------------------------------------------
        */

        $validated['gst_enabled'] =
            $request->boolean('gst_enabled');

        if (!$validated['gst_enabled']) {
            $validated['gstin'] = null;
            $validated['state_code'] = null;
        } else {
            $validated['gstin'] = strtoupper(
                trim((string) ($validated['gstin'] ?? ''))
            );
        }

        $validated['invoice_base_prefix'] =
            filled($validated['invoice_base_prefix'] ?? null)
                ? strtoupper(
                    trim($validated['invoice_base_prefix'])
                )
                : null;

        if (
            ($validated['rounding_mode'] ?? 'none')
            === 'none'
        ) {
            $validated['rounding_step'] = 1;
        }

        /*
        |--------------------------------------------------------------------------
        | Files ko validation data se temporarily remove karein
        |--------------------------------------------------------------------------
        */

        unset(
            $validated['logo'],
            $validated['signature'],
            $validated['letter_head']
        );

        DB::transaction(function () use (
            $request,
            $business,
            &$validated
        ) {
            /*
            |--------------------------------------------------------------------------
            | Upload business logo
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('logo')) {
                $oldLogo = $business->logo;

                $validated['logo'] = $request
                    ->file('logo')
                    ->store('business_logos', 'public');

                if ($oldLogo) {
                    Storage::disk('public')->delete($oldLogo);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Upload signature
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('signature')) {
                $oldSignature = $business->signature;

                $validated['signature'] = $request
                    ->file('signature')
                    ->store(
                        'business_signatures',
                        'public'
                    );

                if ($oldSignature) {
                    Storage::disk('public')->delete(
                        $oldSignature
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Upload letterhead
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('letter_head')) {
                $oldLetterHead = $business->letter_head;

                $validated['letter_head'] = $request
                    ->file('letter_head')
                    ->store(
                        'business_letter_heads',
                        'public'
                    );

                if ($oldLetterHead) {
                    Storage::disk('public')->delete(
                        $oldLetterHead
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Update business
            |--------------------------------------------------------------------------
            */

            $business->fill($validated);
            $business->save();

            $business->refreshProfileCompletion();
        });

        $business->refresh();

        $message = $business->profile_setup_completed
            ? 'Business profile setup completed successfully.'
            : 'Business profile saved successfully. Please complete the remaining fields.';

        return redirect()
            ->route('business-profile.index')
            ->with('success', $message);
    }

    public function selectTemplate(
        Request $request,
        BillTemplate $billTemplate
    ) {
        $business = $this->resolveBusiness($request);

        $business->forceFill([
            'pdf_template_id' => $billTemplate->id,
        ])->save();

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

    public function dismissSuggestion(Request $request)
    {
        $business = $this->resolveBusiness($request);

        $business->forceFill([
            'profile_suggestion_dismissed_at' => now(),
        ])->save();

        return response()->json([
            'success' => true,
        ]);
    }

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
        |
        | Aapke project me role ka naam super_admin ya super admin dono me se
        | koi ho sakta hai, isliye dono check kiye gaye hain.
        |
        */

        $isSuperAdmin =
            $user->hasRole('super_admin')
            || $user->hasRole('super admin');

        $canViewAllBusinesses =
            $user->can('view all businesses');

        if (
            !$isSuperAdmin
            && !$canViewAllBusinesses
        ) {
            $hasAccess = $user->businesses()
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