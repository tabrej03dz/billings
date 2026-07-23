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

    $validated = $request->validate(
        [
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
                'size:15',
            ],

            /*
            |--------------------------------------------------------------------------
            | State
            |--------------------------------------------------------------------------
            |
            | Form se state "09,Uttar Pradesh" format me aayegi.
            |
            */
            'state' => [
                'required',
                'string',
                'regex:/^\d{2},.+$/',
            ],

            'address' => [
                'required',
                'string',
                'max:1000',
            ],

            'pdf_template_id' => [
                'required',
                'integer',
                Rule::exists('bill_templates', 'id'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Invoice prefix
            |--------------------------------------------------------------------------
            |
            | Allowed examples:
            | RV/SL
            | INV/2026
            | RV-SL
            | RV_SL
            |
            */
            'invoice_base_prefix' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[A-Za-z0-9_\/-]+$/',
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
        ],
        [
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

            'gstin.size' =>
                'GST number must contain exactly 15 characters.',

            'state.required' =>
                'Please select a state.',

            'state.regex' =>
                'The selected state is invalid.',

            'invoice_base_prefix.regex' =>
                'Invoice prefix may contain only letters, numbers, slash, hyphen and underscore.',
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | State aur state code separate karein
    |--------------------------------------------------------------------------
    |
    | Example:
    | 09,Uttar Pradesh
    |
    | state_code = 09
    | state      = Uttar Pradesh
    |
    */

    [$stateCode, $stateName] = array_pad(
        explode(',', $validated['state'], 2),
        2,
        null
    );

    $stateCode = trim((string) $stateCode);
    $stateName = trim((string) $stateName);

    if (
        !preg_match('/^\d{2}$/', $stateCode) ||
        $stateName === ''
    ) {
        return back()
            ->withErrors([
                'state' => 'The selected state is invalid.',
            ])
            ->withInput();
    }

    $validated['state_code'] = $stateCode;
    $validated['state'] = $stateName;

    /*
    |--------------------------------------------------------------------------
    | Normalize basic values
    |--------------------------------------------------------------------------
    */

    $validated['name'] = trim($validated['name']);

    $validated['email'] = strtolower(
        trim($validated['email'])
    );

    $validated['mobile'] = trim(
        $validated['mobile']
    );

    $validated['address'] = trim(
        $validated['address']
    );

    /*
    |--------------------------------------------------------------------------
    | GST normalize karein
    |--------------------------------------------------------------------------
    */

    $validated['gst_enabled'] =
        $request->boolean('gst_enabled');

    if ($validated['gst_enabled']) {
        $validated['gstin'] = strtoupper(
            trim((string) $validated['gstin'])
        );
    } else {
        $validated['gstin'] = null;
    }

    /*
    |--------------------------------------------------------------------------
    | Invoice prefix normalize karein
    |--------------------------------------------------------------------------
    |
    | Slash ko remove nahi kiya jayega.
    | RV/SL database me RV/SL hi save hoga.
    |
    */

    $validated['invoice_base_prefix'] =
        filled($validated['invoice_base_prefix'] ?? null)
            ? strtoupper(
                trim($validated['invoice_base_prefix'])
            )
            : null;

    /*
    |--------------------------------------------------------------------------
    | Rounding settings
    |--------------------------------------------------------------------------
    */

    if (
        ($validated['rounding_mode'] ?? 'none') === 'none'
    ) {
        $validated['rounding_step'] = 1;
    } else {
        $validated['rounding_step'] =
            (float) $validated['rounding_step'];
    }

    /*
    |--------------------------------------------------------------------------
    | Terms normalize karein
    |--------------------------------------------------------------------------
    */

    $validated['terms'] =
        filled($validated['terms'] ?? null)
            ? trim($validated['terms'])
            : null;

    /*
    |--------------------------------------------------------------------------
    | Files ko validated array se remove karein
    |--------------------------------------------------------------------------
    */

    unset(
        $validated['logo'],
        $validated['signature'],
        $validated['letter_head']
    );

    $newFiles = [];
    $oldFiles = [];

    try {
        /*
        |--------------------------------------------------------------------------
        | Logo upload
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('logo')) {
            $newFiles['logo'] = $request
                ->file('logo')
                ->store(
                    'business_logos',
                    'public'
                );

            $validated['logo'] =
                $newFiles['logo'];

            if ($business->logo) {
                $oldFiles[] = $business->logo;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Signature upload
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('signature')) {
            $newFiles['signature'] = $request
                ->file('signature')
                ->store(
                    'business_signatures',
                    'public'
                );

            $validated['signature'] =
                $newFiles['signature'];

            if ($business->signature) {
                $oldFiles[] = $business->signature;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Letterhead upload
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('letter_head')) {
            $newFiles['letter_head'] = $request
                ->file('letter_head')
                ->store(
                    'business_letter_heads',
                    'public'
                );

            $validated['letter_head'] =
                $newFiles['letter_head'];

            if ($business->letter_head) {
                $oldFiles[] = $business->letter_head;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Business update
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $business,
            $validated
        ) {
            $business->fill($validated);
            $business->save();

            $business->refreshProfileCompletion();
        });

        /*
        |--------------------------------------------------------------------------
        | Old files delete karein
        |--------------------------------------------------------------------------
        |
        | Database update successful hone ke baad hi old files delete hongi.
        |
        */

        foreach ($oldFiles as $oldFile) {
            if (
                $oldFile &&
                Storage::disk('public')->exists($oldFile)
            ) {
                Storage::disk('public')->delete(
                    $oldFile
                );
            }
        }
    } catch (\Throwable $exception) {
        /*
        |--------------------------------------------------------------------------
        | Failed update cleanup
        |--------------------------------------------------------------------------
        |
        | Update fail hone par newly uploaded orphan files delete hongi.
        |
        */

        foreach ($newFiles as $newFile) {
            if (
                $newFile &&
                Storage::disk('public')->exists($newFile)
            ) {
                Storage::disk('public')->delete(
                    $newFile
                );
            }
        }

        report($exception);

        return back()
            ->withInput()
            ->with(
                'error',
                'Business profile could not be saved. Please try again.'
            );
    }

    $business->refresh();

    $message = $business->profile_setup_completed
        ? 'Business profile setup completed successfully.'
        : 'Business profile saved successfully. Please complete the remaining fields.';

    return redirect()
        ->route('dashboard')
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