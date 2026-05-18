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

            'pdf_template_id' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:100'], // optional in store (aap chahe to required kar do)
            'state' => ['nullable', 'string', 'max:100'],
            'state_code' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'exists:users,id'], // optional: agar aap chahte ho ki business create karte time hi kisi user ko assign karna, to is field ko required kar do
        ]);



        // Slug
        $data['slug'] = Str::slug($data['name']);

        if (Business::where('slug', $data['slug'])->exists()) {
            $data['slug'] .= '-' . Str::lower(Str::random(6));
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

        BusinessUser::create([
            'business_id' => $business->id,
            'user_id' => $request->user_id ?? $request->user()->id, // agar request me user_id nahi hai, to current user ko assign kar do
            'role' => 'user',
        ]);

//        // Attach current user as OWNER
//        $request->user()->businesses()->syncWithoutDetaching([
//            $business->id => ['role' => 'owner']
//        ]);

        return response()->json([
            'status' => true,
            'message' => 'Business created successfully.',
            'data' => $business,
        ], 201);
    }

    public function update(Request $request, Business $business)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],

            // slug request se optional hai, par aap generate kar rahe ho -> isko ignore bhi kar sakte ho
            // agar user slug bhej hi nahi raha, to is field ko hata bhi sakte ho
            'slug'    => ['nullable', 'alpha_dash', 'max:255', Rule::unique('businesses', 'slug')->ignore($business->id)],

            'email'   => ['required', 'email', 'max:255', Rule::unique('businesses', 'email')->ignore($business->id)],
            'mobile'  => ['nullable', 'string', 'max:20', Rule::unique('businesses', 'mobile')->ignore($business->id)],

            'gstin'   => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'terms'   => ['nullable', 'string', 'max:1000'],

            'logo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'signature'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'letter_head' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'pdf_template_id' => ['nullable', 'string', 'max:100'],
            'type'            => ['nullable', 'string', 'max:100'],
            'state'           => ['nullable', 'string', 'max:100'],
            'state_code'      => ['nullable', 'string', 'max:100'],
        ]);

        // ✅ Slug (request se nahi lena)
        // NOTE: Agar aap chahte ho ki name change hone par hi slug regenerate ho, to condition laga do.
        if (isset($data['name']) && $data['name'] !== $business->name) {
            $baseSlug = Str::slug($data['name']);
            $slug = $baseSlug;
            $counter = 1;

            while (Business::where('slug', $slug)->where('id', '!=', $business->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $data['slug'] = $slug;
        } else {
            // name same hai -> existing slug keep
            unset($data['slug']); // important: validation me slug tha, but we don't want to overwrite
        }

        // ✅ Files (replace + old delete)
        if ($request->hasFile('logo')) {
            if ($business->logo && Storage::disk('public')->exists($business->logo)) {
                Storage::disk('public')->delete($business->logo);
            }
            $data['logo'] = $request->file('logo')->store('business_logos', 'public');
        }

        if ($request->hasFile('signature')) {
            if ($business->signature && Storage::disk('public')->exists($business->signature)) {
                Storage::disk('public')->delete($business->signature);
            }
            $data['signature'] = $request->file('signature')->store('business_signatures', 'public');
        }

        if ($request->hasFile('letter_head')) {
            if ($business->letter_head && Storage::disk('public')->exists($business->letter_head)) {
                Storage::disk('public')->delete($business->letter_head);
            }
            $data['letter_head'] = $request->file('letter_head')->store('business_letter_heads', 'public');
        }

        // ✅ Update
        $business->update($data);

        return response()->json([
            'status'  => true,
            'message' => 'Business updated successfully.',
            'data'    => $business->fresh(),
        ], 200);
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
