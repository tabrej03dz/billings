@props([
    'item' => null,
    'categories' => collect(),
    'allowedFields' => []
])

@php
    $isEdit = filled($item?->id);
    $currentType = old('type', $item->type ?? '');

    $showField = function ($field) use ($allowedFields) {
        return empty($allowedFields) || in_array($field, $allowedFields);
    };
@endphp

<style>
    select.rv-select {
        background: #fff !important;
        color: #0f172a !important;
    }

    select.rv-select option {
        background: #fff !important;
        color: #1A1D23 !important;
    }
</style>

<div class="space-y-6">

    <div class="border-b pb-4">
        <h3 class="font-semibold text-gray-700 mb-3">Basic Details</h3>

        <div class="grid md:grid-cols-2 gap-4">

            @if($errors->any())
                <div class="md:col-span-2 bg-red-100 text-red-700 p-3 rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($showField('name'))
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Name <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="name" required
                           value="{{ old('name', $item->name ?? '') }}"
                           class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                    @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            @endif

            @if($showField('category_id'))
                <div>
                    <label class="block text-sm font-medium mb-1">Category</label>
                    <select name="category_id" class="rv-select mt-1 w-full border rounded px-3 py-2">
                        <option value="">— None —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}"
                                @selected((string) old('category_id', $item->category_id ?? '') === (string) $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            @endif

            @if($showField('sku'))
                <div>
                    <label class="block text-sm font-medium mb-1">SKU</label>
                    <input type="text" name="sku"
                        value="{{ old('sku', $item->sku ?? '') }}"
                        class="mt-1 w-full border rounded px-3 py-2 bg-slate-200"
                        placeholder="Example: ITEM-001">
                    @error('sku') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            @endif

            @if($showField('type'))
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Type <span class="text-red-600">*</span>
                    </label>
                    <select id="typeSelect" name="type" required class="rv-select mt-1 w-full border rounded px-3 py-2">
                        <option value="">— Select Type —</option>
                        <option value="product" @selected($currentType === 'product')>Product</option>
                        <option value="service" @selected($currentType === 'service')>Service</option>
                    </select>
                    @error('type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            @else
                <input type="hidden" id="typeSelect" name="type" value="{{ $currentType ?: 'product' }}">
            @endif

        </div>
    </div>

    @if($showField('sac') || $showField('service_duration'))
    <div id="serviceFields" class="border-b pb-4">
            <h3 class="font-semibold text-gray-700 mb-3">Service Details</h3>

            <div class="grid md:grid-cols-2 gap-4">
                @if($showField('sac'))
                    <div>
                        <label class="block text-sm font-medium mb-1">SAC Code</label>
                        <input id="sacField" type="text" name="sac"
                               value="{{ old('sac', $item->sac ?? '') }}"
                               class="mt-1 w-full border rounded px-3 py-2 bg-slate-200"
                               placeholder="Optional">
                        @error('sac') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

                @if($showField('service_duration'))
                    <div>
                        <label class="block text-sm font-medium mb-1">Service Duration (mins)</label>
                        <input type="number" name="service_duration"
                               value="{{ old('service_duration', $item->service_duration ?? '') }}"
                               class="mt-1 w-full border rounded px-3 py-2 bg-slate-200"
                               placeholder="Optional">
                        @error('service_duration') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if(
        $showField('gross_weight') ||
        $showField('stone_weight') ||
        $showField('stone_charges') ||
        $showField('gold_weight') ||
        $showField('gold_purity') ||
        $showField('silver_weight') ||
        $showField('silver_purity') ||
        $showField('diamond_weight') ||
        $showField('diamond_charges') ||
        $showField('stock_qty') ||
        $showField('unit')
    )
        <div id="productFields" class="space-y-6 hidden">

            <div class="border-b pb-4">
                <h3 class="font-semibold text-gray-700 mb-3">Metal & Weights</h3>

                <div class="grid md:grid-cols-3 gap-4">

                    @if($showField('gross_weight'))
                        <div>
                            <label class="block text-sm font-medium mb-1">Gross Weight (gm)</label>
                            <input type="number" step="0.001" name="gross_weight"
                                   value="{{ old('gross_weight', $item->gross_weight ?? '') }}"
                                   class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                        </div>
                    @endif

                    @if($showField('stone_weight'))
                        <div>
                            <label class="block text-sm font-medium mb-1">Gemstone Weight (gm)</label>
                            <input type="number" step="0.001" name="stone_weight"
                                   value="{{ old('stone_weight', $item->stone_weight ?? '') }}"
                                   class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                        </div>
                    @endif

                    @if($showField('stone_charges'))
                        <div>
                            <label class="block text-sm font-medium mb-1">Gemstone Charges (₹)</label>
                            <input type="number" step="0.01" name="stone_charges"
                                   value="{{ old('stone_charges', $item->stone_charges ?? '') }}"
                                   class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                        </div>
                    @endif

                    @if($showField('gold_weight'))
                        <div>
                            <label class="block text-sm font-medium mb-1">Gold Weight (gm)</label>
                            <input type="number" step="0.001" name="gold_weight"
                                   value="{{ old('gold_weight', $item->gold_weight ?? '') }}"
                                   class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                        </div>
                    @endif

                    @if($showField('gold_purity'))
                        <div>
                            <label class="block text-sm font-medium mb-1">Gold Purity</label>
                            <select name="gold_purity" class="rv-select mt-1 w-full border rounded px-3 py-2">
                                <option value="">Select Gold Purity</option>
                                <option value="24K (999)" @selected(old('gold_purity', $item->gold_purity ?? '') == '24K (999)')>24K (999)</option>
                                <option value="22K (916)" @selected(old('gold_purity', $item->gold_purity ?? '') == '22K (916)')>22K (916)</option>
                                <option value="18K (750)" @selected(old('gold_purity', $item->gold_purity ?? '') == '18K (750)')>18K (750)</option>
                                <option value="14K (585)" @selected(old('gold_purity', $item->gold_purity ?? '') == '14K (585)')>14K (585)</option>
                            </select>
                        </div>
                    @endif

                    @if($showField('silver_weight'))
                        <div>
                            <label class="block text-sm font-medium mb-1">Silver Weight (gm)</label>
                            <input type="number" step="0.001" name="silver_weight"
                                   value="{{ old('silver_weight', $item->silver_weight ?? '') }}"
                                   class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                        </div>
                    @endif

                    @if($showField('silver_purity'))
                        <div>
                            <label class="block text-sm font-medium mb-1">Silver Purity</label>
                            <select name="silver_purity" class="rv-select mt-1 w-full border rounded px-3 py-2">
                                <option value="">Select Silver Purity</option>
                                <option value="999" @selected(old('silver_purity', $item->silver_purity ?? '') == '999')>Silver 999</option>
                                <option value="925" @selected(old('silver_purity', $item->silver_purity ?? '') == '925')>Silver 925</option>
                            </select>
                        </div>
                    @endif

                    @if($showField('diamond_weight'))
                        <div>
                            <label class="block text-sm font-medium mb-1">Diamond Weight (ct)</label>
                            <input type="number" step="0.001" name="diamond_weight"
                                   value="{{ old('diamond_weight', $item->diamond_weight ?? '') }}"
                                   class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                        </div>
                    @endif

                    @if($showField('diamond_charges'))
                        <div>
                            <label class="block text-sm font-medium mb-1">Diamond Charges (₹)</label>
                            <input type="number" step="0.01" name="diamond_charges"
                                   value="{{ old('diamond_charges', $item->diamond_charges ?? '') }}"
                                   class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                        </div>
                    @endif
                </div>
            </div>

            @if($showField('stock_qty') || $showField('unit'))
                <div class="border-b pb-4" id="stockBlock">
                    <h3 class="font-semibold text-gray-700 mb-3">Stock Details</h3>

                    <div class="grid md:grid-cols-2 gap-4">
                        @if($showField('stock_qty'))
                            <div>
                                <label class="block text-sm font-medium mb-1">
                                    Stock Qty <span class="text-red-600">*</span>
                                </label>
                                <input id="stockQty" type="number" step="1" min="0" name="stock_qty"
                                       value="{{ old('stock_qty', $item->stock_qty ?? 0) }}"
                                       class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                            </div>
                        @endif

                        @if($showField('unit'))
                            <div>
                                <label class="block text-sm font-medium mb-1">Unit</label>
                                <input id="unitField" type="text" name="unit"
                                       value="{{ old('unit', $item->unit ?? '') }}"
                                       class="mt-1 w-full border rounded px-3 py-2 bg-slate-200"
                                       placeholder="pcs / gm / ml ...">
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if($showField('price') || $showField('cost_price') || $showField('making_charge') || $showField('tax_rate'))
        <div class="border-b pb-4">
            <h3 class="font-semibold text-gray-700 mb-3">Pricing</h3>

            <div class="grid md:grid-cols-2 gap-4">

                @if($showField('price'))
                    <div>
                        <label class="block text-sm font-medium mb-1">Price (₹)</label>
                        <input type="number" step="0.01" min="0" name="price"
                               value="{{ old('price', $item->price ?? 0) }}"
                               class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                    </div>
                @endif

                @if($showField('cost_price'))
                    <div>
                        <label class="block text-sm font-medium mb-1">Cost Price (₹)</label>
                        <input type="number" step="0.01" min="0" name="cost_price"
                               value="{{ old('cost_price', $item->cost_price ?? '') }}"
                               class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                    </div>
                @endif

                {{-- @if($showField('making_charge'))
                    <div id="makingChargeBlock" class="hidden">
                        <label class="block text-sm font-medium mb-1">Making Charge (%)</label>
                        <input id="makingChargeField" type="number" step="0.01" min="0" max="100" name="making_charge"
                               value="{{ old('making_charge', $item->making_charge ?? '') }}"
                               class="mt-1 w-full border rounded px-3 py-2 bg-slate-200"
                               placeholder="Example: 10">
                    </div>
                @endif --}}

                @if($showField('making_charge'))
                    <div id="makingChargeBlock" class="hidden md:col-span-2">
                        <div class="grid md:grid-cols-2 gap-4">
                            
                            <div>
                                <label class="block text-sm font-medium mb-1">Making Charge Type</label>
                                <select id="makingChargeType" name="making_charge_type"
                                        class="rv-select mt-1 w-full border rounded px-3 py-2">
                                    <option value="percentage"
                                        @selected(old('making_charge_type', $item->making_charge_type ?? 'percentage') === 'percentage')>
                                        Percent (%)
                                    </option>
                                    <option value="fixed"
                                        @selected(old('making_charge_type', $item->making_charge_type ?? '') === 'fixed')>
                                        Fixed Amount (₹)
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label id="makingChargeLabel" class="block text-sm font-medium mb-1">
                                    Making Charge (%)
                                </label>
                                <input id="makingChargeField" type="number" step="0.01" min="0"
                                    name="making_charge"
                                    value="{{ old('making_charge', $item->making_charge ?? '') }}"
                                    class="mt-1 w-full border rounded px-3 py-2 bg-slate-200"
                                    placeholder="Example: 10">
                            </div>

                        </div>
                    </div>
                @endif

                @if($showField('tax_rate'))
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Tax % <span class="text-red-600">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" max="100" name="tax_rate" required
                               value="{{ old('tax_rate', $item->tax_rate ?? 0) }}"
                               class="mt-1 w-full border rounded px-3 py-2 bg-slate-200">
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($showField('description'))
        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="3"
                      class="mt-1 w-full border rounded px-3 py-2 bg-slate-200"
                      placeholder="Optional">{{ old('description', $item->description ?? '') }}</textarea>
        </div>
    @endif

    @if($showField('is_active'))
        <div>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300"
                    {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm">Active</span>
            </label>
        </div>
    @else
        <input type="hidden" name="is_active" value="1">
    @endif

    <div class="flex items-center gap-3">
        <button type="submit" class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700">
            {{ $isEdit ? 'Update Item' : 'Create Item' }}
        </button>

        <a href="{{ route('items.index') }}" class="bg-red-400 p-2 rounded-sm text-gray-50 hover:underline">
            Cancel
        </a>
    </div>
</div>

<script>
    (function () {
        const typeSelect = document.getElementById('typeSelect');
        const productFields = document.getElementById('productFields');
        const serviceFields = document.getElementById('serviceFields');
        const makingChargeBlock = document.getElementById('makingChargeBlock');

        const stockQty = document.getElementById('stockQty');
        const sacField = document.getElementById('sacField');
        const makingChargeField = document.getElementById('makingChargeField');
        const unitField = document.getElementById('unitField');

        function setDisabledInside(container, disabled) {
            if (!container) return;

            container.querySelectorAll('input, select, textarea').forEach(el => {
                el.disabled = disabled;
            });
        }

        // function toggleByType(val) {
        //     if (!val) {
        //         productFields?.classList.add('hidden');
        //         // serviceFields?.classList.add('hidden');
        //         makingChargeBlock?.classList.add('hidden');

        //         setDisabledInside(productFields, true);
        //         // setDisabledInside(serviceFields, true);

        //         if (makingChargeField) makingChargeField.disabled = true;
        //         if (stockQty) stockQty.required = false;
        //         if (sacField) sacField.required = false;

        //         return;
        //     }

        //     if (val === 'service') {
        //         serviceFields?.classList.remove('hidden');
        //         productFields?.classList.add('hidden');
        //         makingChargeBlock?.classList.add('hidden');

        //         setDisabledInside(serviceFields, false);
        //         setDisabledInside(productFields, true);

        //         if (makingChargeField) {
        //             makingChargeField.disabled = true;
        //             makingChargeField.value = '';
        //         }

        //         if (stockQty) {
        //             stockQty.required = false;
        //             stockQty.value = '';
        //         }

        //         if (unitField) unitField.value = '';
        //         if (sacField) sacField.required = true;

        //     } else {
        //         productFields?.classList.remove('hidden');
        //         serviceFields?.classList.add('hidden');
        //         makingChargeBlock?.classList.remove('hidden');

        //         setDisabledInside(productFields, false);
        //         setDisabledInside(serviceFields, true);

        //         if (makingChargeField) makingChargeField.disabled = false;
        //         if (stockQty) stockQty.required = true;

        //         if (sacField) {
        //             sacField.required = false;
        //             sacField.value = '';
        //         }
        //     }
        // }

        function toggleByType(val) {

    // SAC section hamesha visible rahega
    if (serviceFields) {
        serviceFields.classList.remove('hidden');
    }

    setDisabledInside(serviceFields, false);

    if (!val) {
        productFields?.classList.add('hidden');
        makingChargeBlock?.classList.add('hidden');

        setDisabledInside(productFields, true);

        if (makingChargeField) makingChargeField.disabled = true;
        if (stockQty) stockQty.required = false;

        return;
    }

    if (val === 'service') {

        productFields?.classList.add('hidden');
        makingChargeBlock?.classList.add('hidden');

        setDisabledInside(productFields, true);

        if (makingChargeField) {
            makingChargeField.disabled = true;
            makingChargeField.value = '';
        }

        if (stockQty) {
            stockQty.required = false;
            stockQty.value = '';
        }

        if (unitField) unitField.value = '';

    } else {

        productFields?.classList.remove('hidden');
        makingChargeBlock?.classList.remove('hidden');

        setDisabledInside(productFields, false);

        if (makingChargeField) makingChargeField.disabled = false;
        if (stockQty) stockQty.required = true;
    }
}

        typeSelect?.addEventListener('change', function () {
            toggleByType(this.value);
        });

        toggleByType(typeSelect?.value || 'product');
    })();
</script>

<script>
    const makingChargeType = document.getElementById('makingChargeType');
const makingChargeLabel = document.getElementById('makingChargeLabel');

function updateMakingChargeUI() {
    if (!makingChargeType || !makingChargeField || !makingChargeLabel) return;

    if (makingChargeType.value === 'fixed') {
        makingChargeLabel.innerText = 'Making Charge (₹)';
        makingChargeField.placeholder = 'Example: 500';
        makingChargeField.removeAttribute('max');
    } else {
        makingChargeLabel.innerText = 'Making Charge (%)';
        makingChargeField.placeholder = 'Example: 10';
        makingChargeField.setAttribute('max', '100');
    }
}

makingChargeType?.addEventListener('change', updateMakingChargeUI);
updateMakingChargeUI();
</script>