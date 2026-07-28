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
        background-color: #ffffff !important;
        color: #0f172a !important;
    }

    select.rv-select option {
        background-color: #ffffff !important;
        color: #0f172a !important;
    }

    .category-blink {
        animation: categoryBlink 1.25s infinite;
    }

    @keyframes categoryBlink {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(249, 115, 22, .55);
        }

        50% {
            transform: scale(1.035);
            box-shadow: 0 0 0 9px rgba(249, 115, 22, 0);
        }
    }
</style>

<div class="space-y-6">

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-[#1b2128]">
        <h3 class="mb-4 text-base font-bold text-slate-800 dark:text-slate-100">Basic Details</h3>

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
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Name <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="name" required
                           value="{{ old('name', $item->name ?? '') }}"
                           class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                    @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            @endif
            @if($showField('category_id'))
                <div>
                    <div class="mb-1 flex items-start justify-between gap-3">
                        <label class="pt-1.5 text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Category
                        </label>

                        <div class="flex shrink-0 flex-col items-end">
                            @if($categories->isEmpty())
                                <button
                                    type="button"
                                    id="openCategoryModal"
                                    class="category-blink inline-flex items-center gap-1
                                        rounded-lg bg-orange-500 px-3 py-1.5
                                        text-xs font-semibold text-white shadow-lg
                                        hover:bg-orange-600"
                                >
                                    <span class="text-base leading-none">+</span>
                                    Add Category
                                </button>

                                <div class="relative mt-2 w-[220px] max-w-[calc(100vw-3rem)]">
                                    {{-- Arrow exactly Add Category button ke center ki taraf --}}
                                    <span
                                        class="absolute -top-2 right-[42px] z-10
                                            h-0 w-0
                                            border-l-[7px] border-r-[7px]
                                            border-b-[8px]
                                            border-l-transparent border-r-transparent
                                            border-b-slate-900
                                            dark:border-b-white"
                                        aria-hidden="true"
                                    ></span>

                                    <div
                                        class="relative z-20 rounded-xl
                                            bg-slate-900 px-3 py-2
                                            text-left text-[11px] font-semibold leading-4
                                            text-white shadow-lg
                                            dark:bg-white dark:text-slate-900"
                                    >
                                        <div class="flex items-start gap-2">
                                            <svg
                                                class="mt-0.5 h-3.5 w-3.5 shrink-0"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                                />
                                            </svg>

                                            <span>
                                                Next Step: Pehle category add karein
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <button
                                    type="button"
                                    id="openCategoryModal"
                                    class="inline-flex items-center gap-1
                                        rounded-lg bg-blue-600 px-3 py-1.5
                                        text-xs font-semibold text-white
                                        hover:bg-blue-700"
                                >
                                    <span class="text-base leading-none">+</span>
                                    Add Category
                                </button>
                            @endif
                        </div>
                    </div>

                    <select
                        name="category_id"
                        class="rv-select mt-1 w-full rounded-xl border border-slate-300
                            px-3.5 py-2.5 outline-none transition
                            focus:border-teal-500 focus:ring-4 focus:ring-teal-100
                            dark:border-slate-600 dark:focus:border-teal-400
                            dark:focus:ring-teal-900/40"
                    >
                        <option value="">— None —</option>

                        @foreach($categories as $cat)
                            <option
                                value="{{ $cat->id }}"
                                @selected(
                                    (string) old(
                                        'category_id',
                                        $item->category_id ?? ''
                                    ) === (string) $cat->id
                                )
                            >
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>

                    @if($categories->isEmpty())
                        <p class="mt-2 text-xs font-medium text-orange-600">
                            Abhi koi category nahi bani hai. Pehle category create karein.
                        </p>
                    @endif

                    @error('category_id')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            @endif

            @if($showField('sku'))
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">SKU</label>
                    <input type="text" name="sku"
                        value="{{ old('sku', $item->sku ?? '') }}"
                        class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40"
                        placeholder="Example: ITEM-001">
                    @error('sku') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            @endif

            @if($showField('type'))
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Type <span class="text-red-600">*</span>
                    </label>
                    <select id="typeSelect" name="type" required class="rv-select mt-1 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
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
    <div id="serviceFields" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-[#1b2128]">
            <h3 class="mb-4 text-base font-bold text-slate-800 dark:text-slate-100">Service Details</h3>

            <div class="grid md:grid-cols-2 gap-4">
                @if($showField('sac'))
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">SAC Code</label>
                        <input id="sacField" type="text" name="sac"
                               value="{{ old('sac', $item->sac ?? '') }}"
                               class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40"
                               placeholder="Optional">
                        @error('sac') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

                @if($showField('service_duration'))
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Service Duration (mins)</label>
                        <input type="number" name="service_duration"
                               value="{{ old('service_duration', $item->service_duration ?? '') }}"
                               class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40"
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

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-[#1b2128]">
                <h3 class="mb-4 text-base font-bold text-slate-800 dark:text-slate-100">Metal & Weights</h3>

                <div class="grid md:grid-cols-3 gap-4">

                    @if($showField('gross_weight'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Gross Weight (gm)</label>
                            <input type="number" step="0.001" name="gross_weight"
                                   value="{{ old('gross_weight', $item->gross_weight ?? '') }}"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                        </div>
                    @endif

                    @if($showField('stone_weight'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Gemstone Weight (gm)</label>
                            <input type="number" step="0.001" name="stone_weight"
                                   value="{{ old('stone_weight', $item->stone_weight ?? '') }}"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                        </div>
                    @endif

                    @if($showField('stone_charges'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Gemstone Charges (₹)</label>
                            <input type="number" step="0.01" name="stone_charges"
                                   value="{{ old('stone_charges', $item->stone_charges ?? '') }}"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                        </div>
                    @endif

                    @if($showField('gold_weight'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Gold Weight (gm)</label>
                            <input type="number" step="0.001" name="gold_weight"
                                   value="{{ old('gold_weight', $item->gold_weight ?? '') }}"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                        </div>
                    @endif

                    @if($showField('gold_purity'))
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Gold Purity
                        </label>

                        <select name="gold_purity"
                                class="rv-select mt-1 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                            <option value="">Select Gold Purity</option>
                            <option value="24K (999)" @selected(old('gold_purity', $item->gold_purity ?? '') == '24K (999)')>24K (999)</option>
                            <option value="22K (916)" @selected(old('gold_purity', $item->gold_purity ?? '') == '22K (916)')>22K (916)</option>
                            <option value="20K (833)" @selected(old('gold_purity', $item->gold_purity ?? '') == '20K (833)')>20K (833)</option>
                            <option value="18K (750)" @selected(old('gold_purity', $item->gold_purity ?? '') == '18K (750)')>18K (750)</option>
                            <option value="16K (667)" @selected(old('gold_purity', $item->gold_purity ?? '') == '16K (667)')>16K (667)</option>
                            <option value="14K (585)" @selected(old('gold_purity', $item->gold_purity ?? '') == '14K (585)')>14K (585)</option>
                        </select>
                    </div>
                @endif

                    @if($showField('silver_weight'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Silver Weight (gm)</label>
                            <input type="number" step="0.001" name="silver_weight"
                                   value="{{ old('silver_weight', $item->silver_weight ?? '') }}"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                        </div>
                    @endif

                    @if($showField('silver_purity'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Silver Purity</label>
                            <select name="silver_purity" class="rv-select mt-1 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                                <option value="">Select Silver Purity</option>
                                <option value="999" @selected(old('silver_purity', $item->silver_purity ?? '') == '999')>Silver 999</option>
                                <option value="925" @selected(old('silver_purity', $item->silver_purity ?? '') == '925')>Silver 925</option>
                            </select>
                        </div>
                    @endif

                    @if($showField('diamond_weight'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Diamond Weight (ct)</label>
                            <input type="number" step="0.001" name="diamond_weight"
                                   value="{{ old('diamond_weight', $item->diamond_weight ?? '') }}"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                        </div>
                    @endif

                    @if($showField('diamond_charges'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Diamond Charges (₹)</label>
                            <input type="number" step="0.01" name="diamond_charges"
                                   value="{{ old('diamond_charges', $item->diamond_charges ?? '') }}"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                        </div>
                    @endif
                </div>
            </div>

            @if($showField('stock_qty') || $showField('unit'))
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-[#1b2128]" id="stockBlock">
                    <h3 class="mb-4 text-base font-bold text-slate-800 dark:text-slate-100">Stock Details</h3>

                    <div class="grid md:grid-cols-2 gap-4">
                        @if($showField('stock_qty'))
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    Stock Qty <span class="text-red-600">*</span>
                                </label>
                                <input id="stockQty" type="number" step="1" min="0" name="stock_qty"
                                       value="{{ old('stock_qty', $item->stock_qty ?? 0) }}"
                                       class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                            </div>
                        @endif

                        @if($showField('unit'))
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Unit</label>
                                <input id="unitField" type="text" name="unit"
                                       value="{{ old('unit', $item->unit ?? '') }}"
                                       class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40"
                                       placeholder="pcs / gm / ml ...">
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if($showField('price') || $showField('cost_price') || $showField('making_charge') || $showField('tax_rate'))
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-[#1b2128]">
            <h3 class="mb-4 text-base font-bold text-slate-800 dark:text-slate-100">Pricing</h3>

            <div class="grid md:grid-cols-2 gap-4">

                @if($showField('price'))
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Price (₹)</label>
                        <input type="number" step="0.01" min="0" name="price"
                               value="{{ old('price', $item->price ?? 0) }}"
                               class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                    </div>
                @endif

                @if($showField('cost_price'))
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Cost Price (₹)</label>
                        <input type="number" step="0.01" min="0" name="cost_price"
                               value="{{ old('cost_price', $item->cost_price ?? '') }}"
                               class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                    </div>
                @endif

                {{-- @if($showField('making_charge'))
                    <div id="makingChargeBlock" class="hidden">
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Making Charge (%)</label>
                        <input id="makingChargeField" type="number" step="0.01" min="0" max="100" name="making_charge"
                               value="{{ old('making_charge', $item->making_charge ?? '') }}"
                               class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40"
                               placeholder="Example: 10">
                    </div>
                @endif --}}

                @if($showField('making_charge'))
                    <div id="makingChargeBlock" class="hidden md:col-span-2">
                        <div class="grid md:grid-cols-2 gap-4">
                            
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Making Charge Type</label>
                                <select id="makingChargeType" name="making_charge_type"
                                        class="rv-select mt-1 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                                    <option value="percentage"
                                        @selected(old('making_charge_type', $item->making_charge_type ?? 'percentage') === 'percentage')>
                                        Percent (%)
                                    </option>

                                    <option value="fixed"
                                        @selected(old('making_charge_type', $item->making_charge_type ?? '') === 'fixed')>
                                        Fixed Amount (₹)
                                    </option>

                                    <option value="per_gram"
                                        @selected(old('making_charge_type', $item->making_charge_type ?? '') === 'per_gram')>
                                        Per Gram Making Charge
                                    </option>

                                    <option value="per_product"
                                        @selected(old('making_charge_type', $item->making_charge_type ?? '') === 'per_product')>
                                        Whole Product Making Charge
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label id="makingChargeLabel" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    Making Charge (%)
                                </label>
                                <input id="makingChargeField" type="number" step="0.01" min="0"
                                    name="making_charge"
                                    value="{{ old('making_charge', $item->making_charge ?? '') }}"
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40"
                                    placeholder="Example: 10">
                            </div>

                        </div>
                    </div>
                @endif

                @if($showField('tax_rate'))
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Tax % <span class="text-red-600">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" max="100" name="tax_rate" required
                               value="{{ old('tax_rate', $item->tax_rate ?? 0) }}"
                               class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($showField('description'))
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Description</label>
            <textarea name="description" rows="3"
                      class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40"
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

    <div class="sticky bottom-0 z-20 -mx-5 mt-8 border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur-md dark:border-slate-700 dark:bg-[#171c22]/95">
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
            <a href="{{ route('items.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Cancel
            </a>

            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-teal-600 to-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-teal-600/20 transition hover:-translate-y-0.5 hover:from-teal-700 hover:to-emerald-700 focus:outline-none focus:ring-4 focus:ring-teal-200 dark:focus:ring-teal-900">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ $isEdit ? 'Update Item' : 'Create Item' }}
            </button>
        </div>
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
    const makingChargeField = document.getElementById('makingChargeField');

    function updateMakingChargeUI() {
        if (!makingChargeType || !makingChargeField || !makingChargeLabel) return;

        makingChargeField.removeAttribute('max');

        if (makingChargeType.value === 'percentage') {
            makingChargeLabel.innerText = 'Making Charge (%)';
            makingChargeField.placeholder = 'Example: 10';
            makingChargeField.setAttribute('max', '100');
        } else if (makingChargeType.value === 'fixed') {
            makingChargeLabel.innerText = 'Fixed Making Charge (₹)';
            makingChargeField.placeholder = 'Example: 500';
        } else if (makingChargeType.value === 'per_gram') {
            makingChargeLabel.innerText = 'Making Charge (₹ / Gram)';
            makingChargeField.placeholder = 'Example: 500 per gram';
        } else {
            makingChargeLabel.innerText = 'Whole Product Making Charge (₹)';
            makingChargeField.placeholder = 'Example: 5000 total making';
        }
    }

    makingChargeType?.addEventListener('change', updateMakingChargeUI);
    updateMakingChargeUI();
</script>