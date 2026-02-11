<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        // Show only businesses the user belongs to,
        // unless they have a permission to view all.
        if ($request->user()->hasRole('super admin') ||$request->user()->can('view all businesses')) {
            $businesses = Business::latest()->paginate(15);
        } else {
            $businesses = $request->user()
                ->businesses()
                ->withPivot('role')
                ->latest('business_user.created_at')
                ->paginate(15);
        }

        return view('businesses.index', compact('businesses'));
    }

    public function create()
    {
        // $this->authorize('create', Business::class); // if using policies
        return view('businesses.create');
    }

    public function store(Request $request)
    {
        // Validation
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'slug'    => ['nullable', 'alpha_dash', 'max:255', 'unique:businesses,slug'],
            'email'   => ['required', 'email', 'max:255', 'unique:businesses,email'],
            'mobile' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('businesses', 'mobile')->ignore($business->id ?? null),
            ],
            'gstin'   => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'terms' => ['nullable', 'string', 'max:1000'],
            'logo'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'signature'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'state'   => ['required', 'string', 'max:100'], // "09,Uttar Pradesh"
                'pdf_template_id'   => ['required', 'string', 'max:100'],
                'invoice_base_prefix'   => ['nullable', 'string', 'max:100'],
        ]);

        // ✅ Split state_code & state_name
        if (!empty($data['state']) && str_contains($data['state'], ',')) {
            [$stateCode, $stateName] = explode(',', $data['state'], 2);
            $data['state_code'] = $stateCode;   // 09
            $data['state']      = $stateName;   // Uttar Pradesh
        }

        // Auto-generate slug if not provided
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        // Ensure slug uniqueness if name duplicates cause same slug
        if (Business::where('slug', $data['slug'])->exists()) {
            $data['slug'] = Str::slug($data['name'].'-'.Str::random(6));
        }

        // Handle logo
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('business_logos', 'public');
        }
        if ($request->hasFile('signature')) {
            $data['signature'] = $request->file('signature')->store('business_signatures', 'public');
        }

        if ($request->hasFile('letter_head')) {
            $data['letter_head'] = $request->file('letter_head')->store('business_letter_heads', 'public');
        }

        $business = Business::create($data);

        // Attach current user as OWNER in pivot
        $request->user()->businesses()->syncWithoutDetaching([
            $business->id => ['role' => 'owner']
        ]);

        return redirect()
            ->route('businesses.index')
            ->with('success', 'Business created successfully.');
    }

    public function edit(Business $business)
    {
        // $this->authorize('update', $business);
        return view('businesses.edit', compact('business'));
    }

    public function update(Request $request, Business $business)
    {
        // $this->authorize('update', $business);

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'slug'    => [
                'nullable', 'alpha_dash', 'max:255',
                Rule::unique('businesses', 'slug')->ignore($business->id),
            ],
            'email'   => [
                'required', 'email', 'max:255',
                Rule::unique('businesses', 'email')->ignore($business->id),
            ],
            'mobile' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('businesses', 'mobile')->ignore($business->id ?? null),
            ],
            'gstin'   => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'terms' => ['nullable', 'string', 'max:1000'],
            'logo'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable','boolean'],
            'signature'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_signature' => ['nullable','boolean'],
            'state'   => ['required', 'string', 'max:100'], // "09,Uttar Pradesh"
            'pdf_template_id'   => ['required', 'string', 'max:100'], // "09,Uttar Pradesh"
            'type' => ['required', 'string', 'max:100'],
            'invoice_base_prefix'   => ['nullable', 'string', 'max:100'],
        ]);

        // ✅ Split state_code & state_name
        if (!empty($data['state']) && str_contains($data['state'], ',')) {
            [$stateCode, $stateName] = explode(',', $data['state'], 2);
            $data['state_code'] = $stateCode;   // 09
            $data['state']      = $stateName;   // Uttar Pradesh
        }

        // Slug fallback
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        // If still collides (e.g., changing name), adjust
        if (Business::where('slug', $data['slug'])->where('id','!=',$business->id)->exists()) {
            $data['slug'] = Str::slug($data['name'].'-'.Str::random(6));
        }

        // Replace logo
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


        // Replace signature
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

        // replace letter head
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
