<?php

namespace App\Http\Controllers;

use App\Models\BillTemplate;
use App\Models\Business;
use App\Models\BusinessType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BusinessController extends Controller
{
    // public function index(Request $request)
    // {
    //     // Show only businesses the user belongs to,
    //     // unless they have a permission to view all.
    //     if ($request->user()->hasRole('super admin') ||$request->user()->can('view all businesses')) {
    //         $businesses = Business::latest()->paginate(15);
    //     } else {
    //         $businesses = $request->user()
    //             ->businesses()
    //             ->withPivot('role')
    //             ->latest('business_user.created_at')
    //             ->paginate(15);
    //     }

    //     return view('businesses.index', compact('businesses'));
    // }


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

    public function create()
    {
        $billTemplates = BillTemplate::all();

        $businessTypes = BusinessType::query()
            ->orderBy('name')
            ->get();

        return view('businesses.create', compact('billTemplates', 'businessTypes'));
    }

    // public function store(Request $request)
    // {
    //     // Validation
    //     $data = $request->validate([
    //         'name'    => ['required', 'string', 'max:255'],
    //         'slug'    => ['nullable', 'alpha_dash', 'max:255', 'unique:businesses,slug'],
    //         'email'   => ['required', 'email', 'max:255', 'unique:businesses,email'],
    //         'mobile' => [
    //             'nullable',
    //             'string',
    //             'max:20',
    //             Rule::unique('businesses', 'mobile')->ignore($business->id ?? null),
    //         ],
    //         'gstin'   => ['nullable', 'string', 'max:50'],
    //         'address' => ['nullable', 'string', 'max:1000'],
    //         'terms' => ['nullable', 'string', 'max:1000'],
    //         'logo'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    //         'signature'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    //         'state'   => ['required', 'string', 'max:100'], // "09,Uttar Pradesh"
    //             'pdf_template_id'   => ['required', 'string', 'max:100'],
    //             'invoice_base_prefix'   => ['nullable', 'string', 'max:100'],
    //     ]);

    //     // ✅ Split state_code & state_name
    //     if (!empty($data['state']) && str_contains($data['state'], ',')) {
    //         [$stateCode, $stateName] = explode(',', $data['state'], 2);
    //         $data['state_code'] = $stateCode;   // 09
    //         $data['state']      = $stateName;   // Uttar Pradesh
    //     }

    //     // Auto-generate slug if not provided
    //     $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

    //     // Ensure slug uniqueness if name duplicates cause same slug
    //     if (Business::where('slug', $data['slug'])->exists()) {
    //         $data['slug'] = Str::slug($data['name'].'-'.Str::random(6));
    //     }

    //     // Handle logo
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

    //     // Attach current user as OWNER in pivot
    //     $request->user()->businesses()->syncWithoutDetaching([
    //         $business->id => ['role' => 'owner']
    //     ]);

    //     return redirect()
    //         ->route('businesses.index')
    //         ->with('success', 'Business created successfully.');
    // }

    public function store(Request $request)
{
    $data = $request->validate([
        'name'  => ['required', 'string', 'max:255'],
        'slug'  => ['nullable', 'alpha_dash', 'max:255', 'unique:businesses,slug'],
        'email' => ['required', 'email', 'max:255', 'unique:businesses,email'],

        'mobile' => [
            'nullable',
            'string',
            'max:20',
            Rule::unique('businesses', 'mobile'),
        ],

        // ✅ Dynamic Business Type
        'business_type_id' => [
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

        'state' => ['required', 'string', 'max:100'],

        'pdf_template_id'     => ['required', 'integer', Rule::exists('bill_templates', 'id')],
        'invoice_base_prefix' => ['nullable', 'string', 'max:100'],
    ]);

    // ✅ Split state_code & state_name
    if (!empty($data['state']) && str_contains($data['state'], ',')) {
        [$stateCode, $stateName] = explode(',', $data['state'], 2);

        $data['state_code'] = trim($stateCode);
        $data['state'] = trim($stateName);
    }

    // ✅ Auto-generate slug if empty
    $data['slug'] = !empty($data['slug'])
        ? Str::slug($data['slug'])
        : Str::slug($data['name']);

    // ✅ Ensure slug uniqueness
    $originalSlug = $data['slug'];
    $counter = 1;

    while (Business::where('slug', $data['slug'])->exists()) {
        $data['slug'] = $originalSlug . '-' . $counter;
        $counter++;
    }

    // ✅ Upload files
    if ($request->hasFile('logo')) {
        $data['logo'] = $request->file('logo')->store('business_logos', 'public');
    }

    if ($request->hasFile('signature')) {
        $data['signature'] = $request->file('signature')->store('business_signatures', 'public');
    }

    if ($request->hasFile('letter_head')) {
        $data['letter_head'] = $request->file('letter_head')->store('business_letter_heads', 'public');
    }

    // ✅ Create business
    $business = Business::create($data);

    // ✅ Attach current user as owner
    $request->user()->businesses()->syncWithoutDetaching([
        $business->id => ['role' => 'owner'],
    ]);

    return redirect()
        ->route('businesses.index')
        ->with('success', 'Business created successfully.');
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

    // public function update(Request $request, Business $business)
    // {
    //     // $this->authorize('update', $business);

    //     $data = $request->validate([
    //         'name'    => ['required', 'string', 'max:255'],
    //         'slug'    => [
    //             'nullable', 'alpha_dash', 'max:255',
    //             Rule::unique('businesses', 'slug')->ignore($business->id),
    //         ],
    //         'email'   => [
    //             'required', 'email', 'max:255',
    //             Rule::unique('businesses', 'email')->ignore($business->id),
    //         ],
    //         'mobile' => [
    //             'nullable',
    //             'string',
    //             'max:20',
    //             Rule::unique('businesses', 'mobile')->ignore($business->id ?? null),
    //         ],
    //         'gstin'   => ['nullable', 'string', 'max:50'],
    //         'address' => ['nullable', 'string', 'max:1000'],
    //         'terms' => ['nullable', 'string', 'max:1000'],
    //         'logo'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    //         'remove_logo' => ['nullable','boolean'],
    //         'signature'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    //         'remove_signature' => ['nullable','boolean'],
    //         'state'   => ['required', 'string', 'max:100'], // "09,Uttar Pradesh"
    //         'pdf_template_id'   => ['required', 'string', 'max:100'], // "09,Uttar Pradesh"
    //         'type' => ['required', 'string', 'max:100'],
    //         'invoice_base_prefix'   => ['nullable', 'string', 'max:100'],
    //     ]);

    //     // ✅ Split state_code & state_name
    //     if (!empty($data['state']) && str_contains($data['state'], ',')) {
    //         [$stateCode, $stateName] = explode(',', $data['state'], 2);
    //         $data['state_code'] = $stateCode;   // 09
    //         $data['state']      = $stateName;   // Uttar Pradesh
    //     }

    //     // Slug fallback
    //     $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

    //     // If still collides (e.g., changing name), adjust
    //     if (Business::where('slug', $data['slug'])->where('id','!=',$business->id)->exists()) {
    //         $data['slug'] = Str::slug($data['name'].'-'.Str::random(6));
    //     }

    //     // Replace logo
    //     if ($request->boolean('remove_logo') && $business->logo) {
    //         Storage::disk('public')->delete($business->logo);
    //         $data['logo'] = null;
    //     }
    //     if ($request->hasFile('logo')) {
    //         if ($business->logo) {
    //             Storage::disk('public')->delete($business->logo);
    //         }
    //         $data['logo'] = $request->file('logo')->store('business_logos', 'public');
    //     }


    //     // Replace signature
    //     if ($request->boolean('remove_signature') && $business->signature) {
    //         Storage::disk('public')->delete($business->signature);
    //         $data['signature'] = null;
    //     }
    //     if ($request->hasFile('signature')) {
    //         if ($business->signature) {
    //             Storage::disk('public')->delete($business->signature);
    //         }
    //         $data['signature'] = $request->file('signature')->store('business_signatures', 'public');
    //     }

    //     // replace letter head
    //     if ($request->boolean('remove_letter_head') && $business->letter_head) {
    //         Storage::disk('public')->delete($business->letter_head);
    //         $data['letter_head'] = null;
    //     }
    //     if ($request->hasFile('letter_head')) {
    //         if ($business->letter_head) {
    //             Storage::disk('public')->delete($business->letter_head);
    //         }
    //         $data['letter_head'] = $request->file('letter_head')->store('business_letter_heads', 'public');
    //     }

    //     $business->update($data);

    //     return redirect()
    //         ->route('businesses.index')
    //         ->with('success', 'Business updated successfully.');
    // }


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
            'business_type_id' => [
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
