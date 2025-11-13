@props(['item' => null, 'categories' => collect()])

@php $isEdit = filled($item?->id); @endphp

<div class="space-y-6">

    {{-- BASIC DETAILS --}}
    <div class="grid md:grid-cols-2 gap-4">

        <div>
            <label class="block text-sm font-medium mb-1">Name <span class="text-red-600">*</span></label>
            <input type="text" name="name" required
                   value="{{ old('name', $item->name ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">SKU</label>
            <input type="text" name="sku"
                   value="{{ old('sku', $item->sku ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2" placeholder="Optional">
        </div>

        {{-- Category --}}
        <div>
            <label class="block text-sm font-medium mb-1">Category</label>
            <select name="category_id" class="mt-1 w-full border rounded px-3 py-2">
                <option value="">— None —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id', $item->category_id ?? '') == $cat->id)>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- SAC Code --}}
        <div>
            <label class="block text-sm font-medium mb-1">SAC Code</label>
            <input type="text" name="sac"
                   value="{{ old('sac', $item->sac ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2" placeholder="Optional">
        </div>
    </div>

    {{-- PRICING --}}
    <div class="grid md:grid-cols-2 gap-4">

        <div>
            <label class="block text-sm font-medium mb-1">Price (₹) <span class="text-red-600">*</span></label>
            <input type="number" step="0.01" min="0" name="price" required
                   value="{{ old('price', $item->price ?? 0) }}"
                   class="mt-1 w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Cost Price (₹)</label>
            <input type="number" step="0.01" min="0" name="cost_price"
                   value="{{ old('cost_price', $item->cost_price ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Tax % <span class="text-red-600">*</span></label>
            <input type="number" step="0.01" min="0" max="100" name="tax_rate" required
                   value="{{ old('tax_rate', $item->tax_rate ?? 0) }}"
                   class="mt-1 w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Making Charge (₹)</label>
            <input type="number" step="0.01" min="0" name="making_charge"
                   value="{{ old('making_charge', $item->making_charge ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2" placeholder="Optional">
        </div>
    </div>

    {{-- STOCK --}}
    <div class="grid md:grid-cols-2 gap-4">

        <div>
            <label class="block text-sm font-medium mb-1">Stock Qty <span class="text-red-600">*</span></label>
            <input type="number" step="1" min="0" name="stock_qty" required
                   value="{{ old('stock_qty', $item->stock_qty ?? 0) }}"
                   class="mt-1 w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Unit</label>
            <input type="text" name="unit"
                   value="{{ old('unit', $item->unit ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2" placeholder="pcs/gm/ml...">
        </div>

    </div>

    {{-- METAL DETAILS --}}
    <div class="border-t pt-4">
        <h3 class="font-semibold text-gray-700 mb-2">Metal Details</h3>

        <div class="grid md:grid-cols-3 gap-4">

            {{-- Metal Type --}}
            <div>
                <label class="block text-sm font-medium mb-1">Metal Type</label>
                <select name="metal_type" class="mt-1 w-full border rounded px-3 py-2">
                    <option value="gold" @selected(old('metal_type', $item->metal_type ?? '')=='gold')>Gold</option>
                    <option value="silver" @selected(old('metal_type', $item->metal_type ?? '')=='silver')>Silver</option>
                    <option value="other" @selected(old('metal_type', $item->metal_type ?? '')=='other')>Other</option>
                </select>
            </div>

            {{-- Purity --}}
            <div>
                <label class="block text-sm font-medium mb-1">Purity</label>
                <input type="text" name="purity"
                       value="{{ old('purity', $item->purity ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2"
                       placeholder="e.g. 22K, 18K, 999">
            </div>

            {{-- Gross Weight --}}
            <div>
                <label class="block text-sm font-medium mb-1">Gross Weight</label>
                <input type="number" step="0.001" name="gross_weight"
                       value="{{ old('gross_weight', $item->gross_weight ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2" placeholder="gm">
            </div>

            {{-- Metal Weight --}}
            <div>
                <label class="block text-sm font-medium mb-1">Metal Weight</label>
                <input type="number" step="0.001" name="metal_weight"
                       value="{{ old('metal_weight', $item->metal_weight ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2" placeholder="gm">
            </div>

            {{-- Stone Weight --}}
            <div>
                <label class="block text-sm font-medium mb-1">Stone Weight</label>
                <input type="number" step="0.001" name="stone_weight"
                       value="{{ old('stone_weight', $item->stone_weight ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2" placeholder="gm">
            </div>

            {{-- Stone Charges --}}
            <div>
                <label class="block text-sm font-medium mb-1">Stone Charges (₹)</label>
                <input type="number" step="0.01" name="stone_charges"
                       value="{{ old('stone_charges', $item->stone_charges ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2">
            </div>
        </div>
    </div>

    {{-- DESCRIPTION --}}
    <div>
        <label class="block text-sm font-medium mb-1">Description</label>
        <textarea name="description" rows="3" class="mt-1 w-full border rounded px-3 py-2"
                  placeholder="Optional">{{ old('description', $item->description ?? '') }}</textarea>
    </div>

    {{-- STATUS --}}
    <div>
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300"
                {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
            <span class="text-sm">Active</span>
        </label>
    </div>

    {{-- BUTTONS --}}
    <div class="flex items-center gap-3">
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
            {{ $isEdit ? 'Update Item' : 'Create Item' }}
        </button>
        <a href="{{ route('items.index') }}" class="text-gray-600 hover:underline">Cancel</a>
    </div>
</div>
