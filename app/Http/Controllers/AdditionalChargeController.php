<?php

namespace App\Http\Controllers;

use App\Models\AdditionalCharge;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdditionalChargeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $charges = AdditionalCharge::query()
            ->when($search !== '', fn ($q) =>
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('amount', 'like', "%{$search}%")
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('additional-charges.index', compact('charges', 'search'));
    }

    public function create()
    {
        // Reuse one form for create/edit if you like: additional-charges/form.blade.php
        return view('additional-charges.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:190', 'unique:additional_charges,name'],
            'amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        AdditionalCharge::create($data);

        return redirect()
            ->route('additional-charges.index')
            ->with('success', 'Additional charge created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdditionalCharge $additional_charge)
    {
        // Route-model binding param name must match your route key (additional-charge or additional_charge).
        // In routes/web.php, use: Route::resource('additional-charges', AdditionalChargeController::class);
        // Then receive here as ($additional_charge)
        return view('additional-charges.edit', ['charge' => $additional_charge]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdditionalCharge $additional_charge)
    {
        $data = $request->validate([
            'name'   => [
                'required', 'string', 'max:190',
                Rule::unique('additional_charges', 'name')->ignore($additional_charge->id),
            ],
            'amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $additional_charge->update($data);

        return redirect()
            ->route('additional-charges.index')
            ->with('success', 'Additional charge updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdditionalCharge $additional_charge)
    {
        $additional_charge->delete();

        return redirect()
            ->route('additional-charges.index')
            ->with('success', 'Additional charge deleted successfully.');
    }
}
