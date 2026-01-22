@props(['item' => null, 'categories' => collect()])

@php
    $isEdit = filled($item?->id);
    $currentType = old('type', $item->type ?? ''); // ✅ default blank so all hidden on create
@endphp

<style>
    /* ✅ Fix dropdown option visibility on dark themes */
    select.rv-select{
        background:#fff !important;
        color:#0f172a !important; /* slate-900 */
    }
    select.rv-select option{
        background:#fff !important;
        color:#1A1D23 !important;
    }
</style>


<div class="space-y-6 ">

    {{-- ================= BASIC DETAILS ================= --}}
    <div class="border-b pb-4">
        <h3 class="font-semibold text-gray-700 mb-3">Basic Details</h3>

        <div class="grid md:grid-cols-2 gap-4">

            <div>
                <label class="block text-sm font-medium mb-1">Name <span class="text-red-600">*</span></label>
                <input type="text" name="name" required
                       value="{{ old('name', $item->name ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">SKU</label>
                <input type="text" name="sku"
                       value="{{ old('sku', $item->sku ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2 bg-slate-200" placeholder="Optional">
                @error('sku') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Category</label>
                <select name="category_id" class="rv-select mt-1 w-full border rounded px-3 py-2 ">
                    <option value="">— None —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            @selected(old('category_id', $item->category_id ?? '') == $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Type <span class="text-red-600">*</span></label>

                <select id="typeSelect" name="type" required class="rv-select mt-1 w-full border rounded px-3 py-2">
                    <option value="">— Select Type —</option>
                    <option value="product" @selected($currentType == 'product')>Product</option>
                    <option value="service" @selected($currentType == 'service')>Service</option>
                </select>

                @error('type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

        </div>
    </div>

    {{-- ================= SERVICE FIELDS (HIDDEN BY DEFAULT) ================= --}}
    <div id="serviceFields" class="border-b pb-4 hidden">
        <h3 class="font-semibold text-gray-700 mb-3">Service Details</h3>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">SAC Code</label>
                <input type="text" name="sac"
                       value="{{ old('sac', $item->sac ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2 bg-slate-200" placeholder="Optional">
                @error('sac') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Service Duration (mins)</label>
                <input type="number" name="service_duration"
                       value="{{ old('service_duration', $item->service_duration ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2" placeholder="Optional">
                @error('service_duration') <p class="text-xs text-red-600 mt-1 bg-slate-200">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- ================= PRODUCT FIELDS (HIDDEN BY DEFAULT) ================= --}}
    <div id="productFields" class="space-y-6 hidden">

        {{-- METAL + WEIGHTS --}}
        <div class="border-b pb-4">
            <h3 class="font-semibold text-gray-700 mb-3">Metal & Weights</h3>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Gross Weight (gm)</label>
                    <input type="number" step="0.001" name="gross_weight"
                           value="{{ old('gross_weight', $item->gross_weight ?? '') }}"
                           class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                    @error('gross_weight') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Gemstone Weight (gm)</label>
                    <input type="number" step="0.001" name="stone_weight"
                           value="{{ old('stone_weight', $item->stone_weight ?? '') }}"
                           class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                    @error('stone_weight') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Gemstone Charges (₹)</label>
                    <input type="number" step="0.01" name="stone_charges"
                           value="{{ old('stone_charges', $item->stone_charges ?? '') }}"
                           class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                    @error('stone_charges') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- GOLD / SILVER / DIAMOND --}}
            <div class="mt-6">
                <h4 class="font-semibold text-gray-700 mb-3">Gold / Silver / Diamond Details</h4>

                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Gold Weight (gm)</label>
                        <input type="number" step="0.001" name="gold_weight"
                               value="{{ old('gold_weight', $item->gold_weight ?? '') }}"
                               class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                        @error('gold_weight') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Gold Purity</label>
                        <select name="gold_purity" class="rv-select mt-1 w-full border rounded px-3 py-2">
                            <option value="">Select Gold Purity</option>
                            <option value="24K (999)" @selected(old('gold_purity', $item->gold_purity ?? '') == '24K (999)')>24K (999)</option>
                            <option value="22K (916)" @selected(old('gold_purity', $item->gold_purity ?? '') == '22K (916)')>22K (916)</option>
                            <option value="18K (750)" @selected(old('gold_purity', $item->gold_purity ?? '') == '18K (750)')>18K (750)</option>
                            <option value="14K (585)" @selected(old('gold_purity', $item->gold_purity ?? '') == '14K (585)')>14K (585)</option>
                        </select>
                        @error('gold_purity') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="hidden md:block"></div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Silver Weight (gm)</label>
                        <input type="number" step="0.001" name="silver_weight"
                               value="{{ old('silver_weight', $item->silver_weight ?? '') }}"
                               class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 ">
                        @error('silver_weight') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Silver Purity</label>
                        <select name="silver_purity" class="rv-select mt-1 w-full border rounded px-3 py-2">
                            <option value="">Select Silver Purity</option>
                            <option value="999" @selected(old('silver_purity', $item->silver_purity ?? '') == '999')>Silver 999</option>
                            <option value="925" @selected(old('silver_purity', $item->silver_purity ?? '') == '925')>Silver 925</option>
                        </select>
                        @error('silver_purity') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="hidden md:block"></div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Diamond Weight (ct)</label>
                        <input type="number" step="0.001" name="diamond_weight"
                               value="{{ old('diamond_weight', $item->diamond_weight ?? '') }}"
                               class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                        @error('diamond_weight') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Diamond Charges (₹)</label>
                        <input type="number" step="0.01" name="diamond_charges"
                               value="{{ old('diamond_charges', $item->diamond_charges ?? '') }}"
                               class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                        @error('diamond_charges') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- STOCK --}}
        <div class="border-b pb-4" id="stockBlock">
            <h3 class="font-semibold text-gray-700 mb-3">Stock Details</h3>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Stock Qty <span class="text-red-600">*</span></label>
                    <input id="stockQty" type="number" step="1" min="0" name="stock_qty"
                           value="{{ old('stock_qty', $item->stock_qty ?? 0) }}"
                           class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                    @error('stock_qty') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Unit</label>
                    <input type="text" name="unit"
                           value="{{ old('unit', $item->unit ?? '') }}"
                           class="mt-1 w-full border rounded px-3 py-2 bg-slate-200" placeholder="pcs / gm / ml ...">
                    @error('unit') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

    </div>

    {{-- ================= PRICING (COMMON) ================= --}}
    <div class="border-b pb-4">
        <h3 class="font-semibold text-gray-700 mb-3">Pricing</h3>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Price (₹)</label>
                <input type="number" step="0.01" min="0" name="price"
                       value="{{ old('price', $item->price ?? 0) }}"
                       class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                @error('price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Cost Price (₹)</label>
                <input type="number" step="0.01" min="0" name="cost_price"
                       value="{{ old('cost_price', $item->cost_price ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                @error('cost_price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- ✅ Making charge block needs id to toggle --}}
            <div id="makingChargeBlock" class="hidden">
                <label class="block text-sm font-medium mb-1">Making Charge (₹)</label>
                <input type="number" step="0.01" min="0" name="making_charge"
                       value="{{ old('making_charge', $item->making_charge ?? '') }}"
                       class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                @error('making_charge') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Tax % <span class="text-red-600">*</span></label>
                <input type="number" step="0.01" min="0" max="100" name="tax_rate" required
                       value="{{ old('tax_rate', $item->tax_rate ?? 0) }}"
                       class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                @error('tax_rate') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- DESCRIPTION --}}
    <div>
        <label class="block text-sm font-medium mb-1">Description</label>
        <textarea name="description" rows="3" class="mt-1 w-full border rounded px-3 py-2 bg-slate-200"
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
        <button type="submit" class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700">
            {{ $isEdit ? 'Update Item' : 'Create Item' }}
        </button>
        <a href="{{ route('items.index') }}" class="bg-red-400 p-2 rounded-sm text-gray-50 hover:underline">Cancel</a>
    </div>

</div>

{{-- ✅ TOGGLE SCRIPT (DEFAULT HIDE ALL UNTIL TYPE SELECTED) --}}
<script>
    (function () {
        const typeSelect = document.getElementById('typeSelect');
        const productFields = document.getElementById('productFields');
        const serviceFields = document.getElementById('serviceFields');
        const makingChargeBlock = document.getElementById('makingChargeBlock');
        const stockQty = document.getElementById('stockQty');

        function toggleByType(val) {

            // ✅ If nothing selected => hide everything
            if (!val) {
                productFields?.classList.add('hidden');
                serviceFields?.classList.add('hidden');
                makingChargeBlock?.classList.add('hidden');
                if (stockQty) stockQty.required = false;
                return;
            }

            if (val === 'service') {
                serviceFields?.classList.remove('hidden');
                productFields?.classList.add('hidden');
                makingChargeBlock?.classList.add('hidden'); // ✅ hide making charge for service
                if (stockQty) stockQty.required = false;
            } else { // product
                productFields?.classList.remove('hidden');
                serviceFields?.classList.add('hidden');
                makingChargeBlock?.classList.remove('hidden'); // ✅ show making charge for product
                if (stockQty) stockQty.required = true;
            }
        }

        // change handler
        typeSelect?.addEventListener('change', (e) => toggleByType(e.target.value));

        // on load: edit mode has value, create mode blank
        toggleByType(typeSelect?.value || '');
    })();
</script>
