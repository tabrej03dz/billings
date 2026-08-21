<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusinessType;
use App\Models\BusinessTypeItemField;
use Illuminate\Support\Str;

class BusinessTypeController extends Controller
{
    public function index()
    {
        $businessTypes = BusinessType::withCount('itemFields')
            ->latest()
            ->paginate(20);

        return view('business-types.index', compact('businessTypes'));
    }

    public function create()
    {
        $itemColumns = $this->itemColumns();

        return view('business-types.create', compact('itemColumns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:business_types,name',
            'status' => 'nullable|boolean',
            'fields' => 'nullable|array',
            'fields.*' => 'string',
            'required_fields' => 'nullable|array',
            'required_fields.*' => 'string',
        ]);

        $businessType = BusinessType::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => $request->boolean('status'),
        ]);

        $this->syncItemFields($businessType, $request);

        return redirect()
            ->route('business-types.index')
            ->with('success', 'Business Type created successfully.');
    }

    public function show(BusinessType $businessType)
    {
        $businessType->load('itemFields');

        return view('business-types.show', compact('businessType'));
    }

    public function edit(BusinessType $businessType)
    {
        $businessType->load('itemFields');

        $itemColumns = $this->itemColumns();

        $selectedFields = $businessType->itemFields
            ->pluck('field_name')
            ->toArray();

        $requiredFields = $businessType->itemFields
            ->where('is_required', 1)
            ->pluck('field_name')
            ->toArray();

        return view('business-types.edit', compact(
            'businessType',
            'itemColumns',
            'selectedFields',
            'requiredFields'
        ));
    }

    public function update(Request $request, BusinessType $businessType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:business_types,name,' . $businessType->id,
            'status' => 'nullable|boolean',
            'fields' => 'nullable|array',
            'fields.*' => 'string',
            'required_fields' => 'nullable|array',
            'required_fields.*' => 'string',
        ]);

        $businessType->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => $request->boolean('status'),
        ]);

        $this->syncItemFields($businessType, $request);

        return redirect()
            ->route('business-types.index')
            ->with('success', 'Business Type updated successfully.');
    }

    public function destroy(BusinessType $businessType)
    {
        $businessType->itemFields()->delete();
        $businessType->delete();

        return redirect()
            ->route('business-types.index')
            ->with('success', 'Business Type deleted successfully.');
    }

    private function syncItemFields(BusinessType $businessType, Request $request): void
    {
        $selectedFields = $request->fields ?? [];
        $requiredFields = $request->required_fields ?? [];
        $itemColumns = $this->itemColumns();

        $businessType->itemFields()->delete();

        foreach ($selectedFields as $index => $field) {
            if (! array_key_exists($field, $itemColumns)) {
                continue;
            }

            BusinessTypeItemField::create([
                'business_type_id' => $businessType->id,
                'field_name' => $field,
                'label' => $itemColumns[$field],
                'is_required' => in_array($field, $requiredFields),
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function itemColumns(): array
    {
        return [
            'barcode' => 'Barcode',
            'category_id' => 'Category',
            'name' => 'Item Name',
            'sku' => 'SKU',
            'description' => 'Description',
            'price' => 'Price',
            'cost_price' => 'Cost Price',
            'stock_qty' => 'Stock Qty',
            'unit' => 'Unit',
            'tax_rate' => 'Tax Rate',
            'making_charge' => 'Making Charge',
            'weight' => 'Weight',
            'metal_type' => 'Metal Type',
            'purity' => 'Purity',
            'gross_weight' => 'Gross Weight',
            'metal_weight' => 'Metal Weight',
            'stone_weight' => 'Stone Weight',
            'stone_charges' => 'Stone Charges',
            'sac' => 'SAC',
            'gold_weight' => 'Gold Weight',
            'gold_purity' => 'Gold Purity',
            'silver_weight' => 'Silver Weight',
            'silver_purity' => 'Silver Purity',
            'diamond_weight' => 'Diamond Weight',
            'diamond_charges' => 'Diamond Charges',
            'type' => 'Type',
            'image' => 'Image',
        ];
    }
}
