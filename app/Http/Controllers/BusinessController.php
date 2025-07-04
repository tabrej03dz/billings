<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    // Show all businesses
    public function index()
    {
        $businesses = Business::latest()->get();
        return view('businesses.index', compact('businesses'));
    }

    // Show create form
    public function create()
    {
        return view('businesses.form', ['business' => new Business()]);
    }

    // Store new business
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:businesses,email',
            'mobile'  => 'required|string|unique:businesses,mobile',
            'gstin'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'logo'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Generate unique slug
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;

        while (Business::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $validated['slug'] = $slug;

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        Business::create($validated);

        return redirect()->route('businesses.index')->with('success', 'Business created successfully.');
    }

    // Show single business (optional, if needed)

    // Show edit form
    public function edit(Business $business)
    {
        return view('businesses.form', compact('business'));
    }

    // Update existing business
    public function update(Request $request, Business $business)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:businesses,email,' . $business->id,
            'mobile'  => 'required|string|unique:businesses,mobile,' . $business->id,
            'gstin'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'logo'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // If name changed or slug is missing, regenerate slug
        if ($business->name !== $validated['name'] || empty($business->slug)) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;

            while (Business::where('slug', $slug)->where('id', '!=', $business->id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $validated['slug'] = $slug;
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if it exists
            if ($business->logo && Storage::disk('public')->exists($business->logo)) {
                Storage::disk('public')->delete($business->logo);
            }

            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $business->update($validated);

        return redirect()->route('businesses.index')->with('success', 'Business updated successfully.');
    }

    // Delete a business
    public function destroy(Business $business)
    {
        if ($business->logo && Storage::disk('public')->exists($business->logo)) {
            Storage::disk('public')->delete($business->logo);
        }

        $business->delete();

        return redirect()->route('businesses.index')->with('success', 'Business deleted successfully.');
    }
}
