@props(['item' => null, 'categories' => collect()])

@php $isEdit = filled($item?->id); @endphp

<div class="space-y-6">

    {{-- BASIC DETAILS --}}
    <div class="border-b pb-4">
        <h3 class="font-semibold text-gray-700 mb-3">Basic Details</h3>

        <div class="grid md:grid-cols-2 gap-4">

            <div>
                <label class="block text-sm font-medium mb-1">Name <span class="text-red-600">*</span></label>
                <input type="text" name="name" required
                       value="{{ old('name', $item->name ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">SKU</label>
                <input type="text" name="sku"
                       value="{{ old('sku', $item->sku ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2" placeholder="Optional">
                @error('sku') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
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
                @error('category_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">SAC Code</label>
                <input type="text" name="sac"
                       value="{{ old('sac', $item->sac ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2" placeholder="Optional">
                @error('sac') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

        </div>
    </div>

    {{-- METAL + WEIGHTS DETAILS --}}
    <div class="border-b pb-4">
        <h3 class="font-semibold text-gray-700 mb-3">Metal & Weights</h3>

        <div class="grid md:grid-cols-3 gap-4">

            {{-- General Purity (optional - you can remove also if you want) --}}
{{--            <div>--}}
{{--                <label class="block text-sm font-medium mb-1">Purity (General)</label>--}}
{{--                <select name="purity"--}}
{{--                        class="mt-1 w-full border rounded px-3 py-2--}}
{{--                               bg-white text-gray-900--}}
{{--                               dark:bg-neutral-800 dark:text-white dark:border-neutral-600--}}
{{--                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500">--}}
{{--                    <option value="">Select Purity</option>--}}

{{--                    --}}{{-- Gold --}}
{{--                    <option value="24K (999)" @selected(old('purity', $item->purity ?? '') == '24K (999)')>24K (999)</option>--}}
{{--                    <option value="22K (916)" @selected(old('purity', $item->purity ?? '') == '22K (916)')>22K (916)</option>--}}
{{--                    <option value="18K (750)" @selected(old('purity', $item->purity ?? '') == '18K (750)')>18K (750)</option>--}}
{{--                    <option value="14K (585)" @selected(old('purity', $item->purity ?? '') == '14K (585)')>14K (585)</option>--}}

{{--                    --}}{{-- Silver --}}
{{--                    <option value="999" @selected(old('purity', $item->purity ?? '') == '999')>Silver 999</option>--}}
{{--                    <option value="925" @selected(old('purity', $item->purity ?? '') == '925')>Silver 925</option>--}}
{{--                </select>--}}
{{--                @error('purity') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror--}}
{{--            </div>--}}

            <div>
                <label class="block text-sm font-medium mb-1">Gross Weight (gm)</label>
                <input type="number" step="0.001" name="gross_weight"
                       value="{{ old('gross_weight', $item->gross_weight ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2">
                @error('gross_weight') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

{{--            <div>--}}
{{--                <label class="block text-sm font-medium mb-1">Metal Weight (gm)</label>--}}
{{--                <input type="number" step="0.001" name="metal_weight"--}}
{{--                       value="{{ old('metal_weight', $item->metal_weight ?? '') }}"--}}
{{--                       class="mt-1 w-full border rounded px-3 py-2">--}}
{{--                @error('metal_weight') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror--}}
{{--            </div>--}}

            <div>
                <label class="block text-sm font-medium mb-1">Gemstone Weight (gm)</label>
                <input type="number" step="0.001" name="stone_weight"
                       value="{{ old('stone_weight', $item->stone_weight ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2">
                @error('stone_weight') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Gemstone Charges (₹)</label>
                <input type="number" step="0.01" name="stone_charges"
                       value="{{ old('stone_charges', $item->stone_charges ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2">
                @error('stone_charges') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

        </div>

        {{-- GOLD / SILVER / DIAMOND DETAILS --}}
        <div class="mt-6">
            <h4 class="font-semibold text-gray-700 mb-3">Gold / Silver / Diamond Details</h4>

            <div class="grid md:grid-cols-3 gap-4">

                {{-- Gold Weight --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Gold Weight (gm)</label>
                    <input type="number" step="0.001" name="gold_weight"
                           value="{{ old('gold_weight', $item->gold_weight ?? '') }}"
                           class="mt-1 w-full border rounded px-3 py-2">
                    @error('gold_weight') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Gold Purity (Dropdown) --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Gold Purity</label>
                    <select name="gold_purity"
                            class="mt-1 w-full border rounded px-3 py-2
                                   bg-white text-gray-900
                                   dark:bg-neutral-800 dark:text-white dark:border-neutral-600
                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Gold Purity</option>
                        <option value="24K (999)" @selected(old('gold_purity', $item->gold_purity ?? '') == '24K (999)')>24K (999)</option>
                        <option value="22K (916)" @selected(old('gold_purity', $item->gold_purity ?? '') == '22K (916)')>22K (916)</option>
                        <option value="18K (750)" @selected(old('gold_purity', $item->gold_purity ?? '') == '18K (750)')>18K (750)</option>
                        <option value="14K (585)" @selected(old('gold_purity', $item->gold_purity ?? '') == '14K (585)')>14K (585)</option>
                    </select>
                    @error('gold_purity') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="hidden md:block"></div>

                {{-- Silver Weight --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Silver Weight (gm)</label>
                    <input type="number" step="0.001" name="silver_weight"
                           value="{{ old('silver_weight', $item->silver_weight ?? '') }}"
                           class="mt-1 w-full border rounded px-3 py-2">
                    @error('silver_weight') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Silver Purity (Dropdown) --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Silver Purity</label>
                    <select name="silver_purity"
                            class="mt-1 w-full border rounded px-3 py-2
                                   bg-white text-gray-900
                                   dark:bg-neutral-800 dark:text-white dark:border-neutral-600
                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Silver Purity</option>
                        <option value="999" @selected(old('silver_purity', $item->silver_purity ?? '') == '999')>Silver 999</option>
                        <option value="925" @selected(old('silver_purity', $item->silver_purity ?? '') == '925')>Silver 925</option>
                    </select>
                    @error('silver_purity') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="hidden md:block"></div>

                {{-- Diamond Weight --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Diamond Weight (ct)</label>
                    <input type="number" step="0.001" name="diamond_weight"
                           value="{{ old('diamond_weight', $item->diamond_weight ?? '') }}"
                           class="mt-1 w-full border rounded px-3 py-2">
                    @error('diamond_weight') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Diamond Charges --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Diamond Charges (₹)</label>
                    <input type="number" step="0.01" name="diamond_charges"
                           value="{{ old('diamond_charges', $item->diamond_charges ?? '') }}"
                           class="mt-1 w-full border rounded px-3 py-2">
                    @error('diamond_charges') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>
    </div>

    {{-- PRICING --}}
    <div class="border-b pb-4">
        <h3 class="font-semibold text-gray-700 mb-3">Pricing</h3>

        <div class="grid md:grid-cols-2 gap-4">

            <div>
                <label class="block text-sm font-medium mb-1">Price (₹)</label>
                <input type="number" step="0.01" min="0" name="price"
                       value="{{ old('price', $item->price ?? 0) }}"
                       class="mt-1 w-full border rounded px-3 py-2">
                @error('price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Cost Price (₹)</label>
                <input type="number" step="0.01" min="0" name="cost_price"
                       value="{{ old('cost_price', $item->cost_price ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2">
                @error('cost_price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Making Charge (₹)</label>
                <input type="number" step="0.01" min="0" name="making_charge"
                       value="{{ old('making_charge', $item->making_charge ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2">
                @error('making_charge') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Tax % <span class="text-red-600">*</span></label>
                <input type="number" step="0.01" min="0" max="100" name="tax_rate" required
                       value="{{ old('tax_rate', $item->tax_rate ?? 0) }}"
                       class="mt-1 w-full border rounded px-3 py-2">
                @error('tax_rate') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

        </div>
    </div>

    {{-- STOCK DETAILS --}}
    <div class="border-b pb-4">
        <h3 class="font-semibold text-gray-700 mb-3">Stock Details</h3>

        <div class="grid md:grid-cols-2 gap-4">

            <div>
                <label class="block text-sm font-medium mb-1">Stock Qty <span class="text-red-600">*</span></label>
                <input type="number" step="1" min="0" name="stock_qty" required
                       value="{{ old('stock_qty', $item->stock_qty ?? 0) }}"
                       class="mt-1 w-full border rounded px-3 py-2">
                @error('stock_qty') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Unit</label>
                <input type="text" name="unit"
                       value="{{ old('unit', $item->unit ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2"
                       placeholder="pcs / gm / ml ...">
                @error('unit') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

        </div>
    </div>

    {{-- DESCRIPTION --}}
    <div>
        <label class="block text-sm font-medium mb-1">Description</label>
        <textarea name="description" rows="3" class="mt-1 w-full border rounded px-3 py-2"
                  placeholder="Optional">{{ old('description', $item->description ?? '') }}</textarea>
        @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- STATUS --}}
    <div>
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300"
                {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
            <span class="text-sm">Active</span>
        </label>
        @error('is_active') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- BUTTONS --}}
    <div class="flex items-center gap-3">
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
            {{ $isEdit ? 'Update Item' : 'Create Item' }}
        </button>
        <a href="{{ route('items.index') }}" class="text-gray-600 hover:underline">Cancel</a>
    </div>

</div>
