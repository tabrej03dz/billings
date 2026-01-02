<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
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

    public function store(Request $request)
    {

        // ✅ IMPORTANT: store() me $business variable exist nahi karta, isliye ignore hata diya.
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'slug'    => ['nullable', 'alpha_dash', 'max:255', 'unique:businesses,slug'],
            'email'   => ['required', 'email', 'max:255', 'unique:businesses,email'],

            'mobile'  => ['nullable', 'string', 'max:20', 'unique:businesses,mobile'],
            'gstin'   => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'terms'   => ['nullable', 'string', 'max:1000'],

            'logo'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'signature'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'letter_head'=> ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'state'   => ['nullable', 'string', 'max:100'], // "09,Uttar Pradesh"
            'pdf_template_id' => ['required', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:100'], // optional in store (aap chahe to required kar do)
        ]);
        return response($request->all());

        // ✅ Split state_code & state_name
        if (!empty($data['state']) && str_contains($data['state'], ',')) {
            [$stateCode, $stateName] = explode(',', $data['state'], 2);
            $data['state_code'] = trim($stateCode);
            $data['state']      = trim($stateName);
        }

        // Slug
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        if (Business::where('slug', $data['slug'])->exists()) {
            $data['slug'] = Str::slug($data['name'] . '-' . Str::random(6));
        }

        // Files
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

        // Attach current user as OWNER
        $request->user()->businesses()->syncWithoutDetaching([
            $business->id => ['role' => 'owner']
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Business created successfully.',
            'data' => $business,
        ], 201);
    }

    public function update(Request $request, Business $business)
    {
        $this->authorizeBusinessAccess($request, $business);

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'slug'    => ['nullable', 'alpha_dash', 'max:255', Rule::unique('businesses', 'slug')->ignore($business->id)],
            'email'   => ['required', 'email', 'max:255', Rule::unique('businesses', 'email')->ignore($business->id)],

            'mobile'  => ['nullable', 'string', 'max:20', Rule::unique('businesses', 'mobile')->ignore($business->id)],
            'gstin'   => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'terms'   => ['nullable', 'string', 'max:1000'],

            'logo'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],

            'signature'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_signature' => ['nullable', 'boolean'],

            'letter_head'=> ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_letter_head' => ['nullable', 'boolean'],

            'state'   => ['required', 'string', 'max:100'],
            'pdf_template_id' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'max:100'],
        ]);

        // ✅ Split state_code & state_name
        if (!empty($data['state']) && str_contains($data['state'], ',')) {
            [$stateCode, $stateName] = explode(',', $data['state'], 2);
            $data['state_code'] = trim($stateCode);
            $data['state']      = trim($stateName);
        }

        // Slug fallback
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        if (Business::where('slug', $data['slug'])->where('id', '!=', $business->id)->exists()) {
            $data['slug'] = Str::slug($data['name'] . '-' . Str::random(6));
        }

        // Remove/Replace logo
        if ($request->boolean('remove_logo') && $business->logo) {
            Storage::disk('public')->delete($business->logo);
            $data['logo'] = null;
        }
        if ($request->hasFile('logo')) {
            if ($business->logo) Storage::disk('public')->delete($business->logo);
            $data['logo'] = $request->file('logo')->store('business_logos', 'public');
        }

        // Remove/Replace signature
        if ($request->boolean('remove_signature') && $business->signature) {
            Storage::disk('public')->delete($business->signature);
            $data['signature'] = null;
        }
        if ($request->hasFile('signature')) {
            if ($business->signature) Storage::disk('public')->delete($business->signature);
            $data['signature'] = $request->file('signature')->store('business_signatures', 'public');
        }

        // Remove/Replace letter head
        if ($request->boolean('remove_letter_head') && $business->letter_head) {
            Storage::disk('public')->delete($business->letter_head);
            $data['letter_head'] = null;
        }
        if ($request->hasFile('letter_head')) {
            if ($business->letter_head) Storage::disk('public')->delete($business->letter_head);
            $data['letter_head'] = $request->file('letter_head')->store('business_letter_heads', 'public');
        }

        $business->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Business updated successfully.',
            'data' => $business->fresh(),
        ]);
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

    private function authorizeBusinessAccess(Request $request, Business $business): void
    {
        $user = $request->user();

        if ($user->hasRole('super admin') || $user->can('view all businesses')) {
            return;
        }

        $belongs = $user->businesses()->where('businesses.id', $business->id)->exists();
        abort_unless($belongs, 403, 'Unauthorized business access.');
    }
}
