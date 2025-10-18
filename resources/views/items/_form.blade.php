@props(['item' => null, 'categories' => collect()])

@php $isEdit = filled($item?->id); @endphp

<div class="space-y-6">
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Name <span class="text-red-600">*</span></label>
            <input type="text" name="name" required
                   value="{{ old('name', $item->name ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2">
            @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">SKU</label>
            <input type="text" name="sku"
                   value="{{ old('sku', $item->sku ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2" placeholder="Optional">
            @error('sku') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

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
            @error('category_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Unit</label>
            <input type="text" name="unit"
                   value="{{ old('unit', $item->unit ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2" placeholder="pcs/kg/ltr...">
            @error('unit') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Price <span class="text-red-600">*</span></label>
            <input type="number" step="0.01" min="0" name="price" required
                   value="{{ old('price', $item->price ?? 0) }}"
                   class="mt-1 w-full border rounded px-3 py-2">
            @error('price') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Cost Price</label>
            <input type="number" step="0.01" min="0" name="cost_price"
                   value="{{ old('cost_price', $item->cost_price ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2">
            @error('cost_price') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- ✅ Added Making Charge field --}}
        <div>
            <label class="block text-sm font-medium mb-1">Making Charge</label>
            <input type="number" step="0.01" min="0" name="making_charge"
                   value="{{ old('making_charge', $item->making_charge ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2" placeholder="Enter making charge">
            @error('making_charge') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Tax % <span class="text-red-600">*</span></label>
            <input type="number" step="0.01" min="0" max="100" name="tax_rate" required
                   value="{{ old('tax_rate', $item->tax_rate ?? 0) }}"
                   class="mt-1 w-full border rounded px-3 py-2">
            @error('tax_rate') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Stock Qty <span class="text-red-600">*</span></label>
            <input type="number" step="1" min="0" name="stock_qty" required
                   value="{{ old('stock_qty', $item->stock_qty ?? 0) }}"
                   class="mt-1 w-full border rounded px-3 py-2">
            @error('stock_qty') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="3" class="mt-1 w-full border rounded px-3 py-2"
                      placeholder="Optional">{{ old('description', $item->description ?? '') }}</textarea>
            @error('description') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300"
                    {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm">Active</span>
            </label>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
            {{ $isEdit ? 'Update Item' : 'Create Item' }}
        </button>
        <a href="{{ route('items.index') }}" class="text-gray-600 hover:underline">Cancel</a>
    </div>
</div>
