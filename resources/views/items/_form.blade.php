@props([
    'item' => null,
    'categories' => collect(),
    'units' => collect(),
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


    .category-tooltip-arrow {
        position: absolute;
        right: 38px;
        bottom: -17px;
        width: 0;
        height: 0;
        border-left: 11px solid transparent;
        border-right: 11px solid transparent;
        border-top: 18px solid #0f172a;
        z-index: 50;
        display: block;
    }

    .dark .category-tooltip-arrow {
        border-top-color: #ffffff;
    }

    @media (max-width: 420px) {
        .category-tooltip-arrow {
            right: 32px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .category-blink {
            animation: none;
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
                                <div
                                    class="pointer-events-none relative z-40 mb-4
                                        w-[220px] max-w-[calc(100vw-3rem)]"
                                >
                                    <div
                                        class="relative rounded-xl border border-orange-400
                                            bg-slate-900 px-3 py-2
                                            text-left text-[11px] font-semibold leading-4
                                            text-white shadow-xl
                                            dark:border-orange-300
                                            dark:bg-white dark:text-slate-900"
                                    >
                                        <div class="flex items-start gap-2">
                                            <span
                                                class="flex h-5 w-5 shrink-0 items-center
                                                    justify-center rounded-full
                                                    bg-orange-500 text-[10px] text-white"
                                            >
                                                1
                                            </span>

                                            <span>
                                                Yahan click karke pehle category add karein
                                            </span>
                                        </div>

                                        <span
                                            class="category-tooltip-arrow"
                                            aria-hidden="true"
                                        ></span>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    id="openCategoryModal"
                                    class="category-blink relative inline-flex items-center gap-1
                                        rounded-lg bg-orange-500 px-3 py-1.5
                                        text-xs font-semibold text-white shadow-lg
                                        hover:bg-orange-600"
                                >
                                    <span class="text-base leading-none">+</span>
                                    Add Category

                                    <span class="absolute -right-1 -top-1 flex h-3 w-3">
                                        <span
                                            class="absolute inline-flex h-full w-full
                                                animate-ping rounded-full
                                                bg-yellow-300 opacity-75"
                                        ></span>
                                        <span
                                            class="relative inline-flex h-3 w-3
                                                rounded-full bg-yellow-400"
                                        ></span>
                                    </span>
                                </button>
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


            {{-- HUID --}}
            @if($showField('huid'))
                <div>
                    <label
                        for="huid"
                        class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                    >
                        HUID
                    </label>

                    <input
                        type="text"
                        id="huid"
                        name="huid"
                        value="{{ old('huid', $item->huid ?? '') }}"
                        maxlength="50"
                        autocomplete="off"
                        placeholder="Example: AB12CD"
                        class="mt-1 w-full rounded-xl border border-slate-300
                            bg-slate-50 px-3.5 py-2.5
                            font-semibold uppercase tracking-wider
                            text-slate-900 outline-none transition
                            placeholder:text-slate-400
                            focus:border-teal-500 focus:bg-white
                            focus:ring-4 focus:ring-teal-100
                            dark:border-slate-600 dark:bg-slate-800
                            dark:text-white
                            dark:focus:border-teal-400
                            dark:focus:ring-teal-900/40"
                    >

                    @error('huid')
                        <p class="mt-1 text-xs font-medium text-red-600">
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

            {{-- Barcode --}}
            <div>
                <div class="mb-1.5 flex items-center justify-between gap-2">

                    <label
                        for="barcode"
                        class="block text-sm font-semibold text-slate-700 dark:text-slate-200"
                    >
                        Barcode
                        <span class="text-red-600">*</span>
                    </label>

                    @if(!$isEdit)
                        <button
                            type="button"
                            id="generateBarcodeButton"
                            class="inline-flex items-center gap-1 rounded-lg
                                bg-purple-100 px-2.5 py-1
                                text-xs font-bold text-purple-700
                                transition hover:bg-purple-200
                                dark:bg-purple-900/40
                                dark:text-purple-300"
                        >
                            ↻ Generate New
                        </button>
                    @endif

                </div>

                <div class="relative">

                    <input
                        id="barcode"
                        type="text"
                        name="barcode"
                        required
                        autocomplete="off"

                        value="{{ old(
                            'barcode',
                            $item->barcode ?? ($generatedBarcode ?? '')
                        ) }}"

                        class="mt-1 w-full rounded-xl
                            border border-slate-300
                            bg-slate-50
                            px-3.5 py-2.5 pr-12
                            font-mono
                            font-semibold
                            tracking-wider
                            text-slate-900
                            outline-none
                            transition
                            focus:border-purple-500
                            focus:bg-white
                            focus:ring-4
                            focus:ring-purple-100

                            dark:border-slate-600
                            dark:bg-slate-800
                            dark:text-white
                            dark:focus:border-purple-400
                            dark:focus:ring-purple-900/40"

                        placeholder="Barcode"
                    >

                    <span
                        class="pointer-events-none
                            absolute right-3 top-1/2
                            -translate-y-1/2
                            text-lg"
                    >
                        ▥
                    </span>

                </div>

                <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                    Barcode automatically generated hai. Zarurat ho to manually change bhi kar sakte hain.
                </p>

                @error('barcode')
                    <p class="mt-1 text-xs font-medium text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

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

                <div class="grid gap-4 md:grid-cols-3">

                    @if($showField('gold_weight'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Gold Weight (gm)</label>
                            <input type="number" step="0.001" min="0" name="gold_weight"
                                   value="{{ old('gold_weight', $item->gold_weight ?? '') }}"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                            @error('gold_weight') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if($showField('gold_purity'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Gold Purity</label>
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
                            @error('gold_purity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if($showField('silver_weight'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Silver Weight (gm)</label>
                            <input type="number" step="0.001" min="0" name="silver_weight"
                                   value="{{ old('silver_weight', $item->silver_weight ?? '') }}"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                            @error('silver_weight') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if($showField('silver_purity'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Silver Purity</label>
                            <select name="silver_purity"
                                    class="rv-select mt-1 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                                <option value="">Select Silver Purity</option>
                                <option value="999" @selected(old('silver_purity', $item->silver_purity ?? '') == '999')>Silver 999</option>
                                <option value="925" @selected(old('silver_purity', $item->silver_purity ?? '') == '925')>Silver 925</option>
                            </select>
                            @error('silver_purity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if($showField('gross_weight'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Gross Weight (gm)</label>
                            <input type="number" step="0.001" min="0" name="gross_weight"
                                   value="{{ old('gross_weight', $item->gross_weight ?? '') }}"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                            @error('gross_weight') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if($showField('stone_weight'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Gemstone Weight (gm)</label>
                            <input type="number" step="0.001" min="0" name="stone_weight"
                                   value="{{ old('stone_weight', $item->stone_weight ?? '') }}"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                            @error('stone_weight') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if($showField('stone_charges'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Gemstone Charges (₹)</label>
                            <input type="number" step="0.01" min="0" name="stone_charges"
                                   value="{{ old('stone_charges', $item->stone_charges ?? '') }}"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                            @error('stone_charges') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if($showField('diamond_weight'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Diamond Weight (ct)</label>
                            <input type="number" step="0.001" min="0" name="diamond_weight"
                                   value="{{ old('diamond_weight', $item->diamond_weight ?? '') }}"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                            @error('diamond_weight') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if($showField('diamond_charges'))
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Diamond Charges (₹)</label>
                            <input type="number" step="0.01" min="0" name="diamond_charges"
                                   value="{{ old('diamond_charges', $item->diamond_charges ?? '') }}"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                            @error('diamond_charges') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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

                        {{-- @if($showField('unit'))
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Unit</label>
                                <input id="unitField" type="text" name="unit"
                                       value="{{ old('unit', $item->unit ?? '') }}"
                                       class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40"
                                       placeholder="pcs / gm / ml ...">
                            </div>
                        @endif --}}


                        @if($showField('unit'))
                            <div>
                                <div class="mb-1.5 flex items-center justify-between gap-3">
                                    <label
                                        for="unitSelect"
                                        class="block text-sm font-semibold
                                            text-slate-700 dark:text-slate-200"
                                    >
                                        Unit
                                    </label>

                                    <button
                                        type="button"
                                        id="openUnitModal"
                                        class="inline-flex shrink-0 items-center gap-1
                                            rounded-lg bg-teal-600 px-3 py-1.5
                                            text-xs font-semibold text-white
                                            hover:bg-teal-700"
                                    >
                                        <span class="text-base leading-none">+</span>
                                        Add Unit
                                    </button>
                                </div>

                                <select
                                    id="unitSelect"
                                    name="unit"
                                    class="rv-select mt-1 w-full rounded-xl
                                        border border-slate-300 px-3.5 py-2.5
                                        outline-none transition focus:border-teal-500
                                        focus:ring-4 focus:ring-teal-100
                                        dark:border-slate-600
                                        dark:focus:border-teal-400
                                        dark:focus:ring-teal-900/40"
                                >
                                    <option value="">— Select Unit —</option>

                                    @php
                                        $selectedUnit = old('unit', $item->unit ?? '');
                                    @endphp

                                    @foreach($units as $unit)
                                        <option
                                            value="{{ $unit->name }}"
                                            data-unit-id="{{ $unit->id }}"
                                            @selected(
                                                strtolower((string) $selectedUnit) ===
                                                strtolower((string) $unit->name)
                                            )
                                        >
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach

                                    {{--
                                        Purana item kisi aisi unit ko use kar raha ho jo ab
                                        units table me available nahi hai, to value lose nahi hogi.
                                    --}}
                                    @if(
                                        filled($selectedUnit) &&
                                        !$units->contains(
                                            fn ($unit) =>
                                                strtolower($unit->name) ===
                                                strtolower($selectedUnit)
                                        )
                                    )
                                        <option value="{{ $selectedUnit }}" selected>
                                            {{ $selectedUnit }}
                                        </option>
                                    @endif
                                </select>

                                <p
                                    id="emptyUnitMessage"
                                    @class([
                                        'mt-2 text-xs font-medium text-orange-600',
                                        'hidden' => $units->isNotEmpty(),
                                    ])
                                >
                                    Abhi koi unit available nahi hai. Add Unit button se unit banayein.
                                </p>

                                @error('unit')
                                    <p class="mt-1 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
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

            <div class="grid gap-4 md:grid-cols-2">
                @if($showField('price'))
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Price (₹)</label>
                        <input type="number" step="0.01" min="0" name="price"
                               value="{{ old('price', $item->price ?? 0) }}"
                               class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                        @error('price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif

                @if($showField('cost_price'))
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Cost Price (₹)</label>
                        <input type="number" step="0.01" min="0" name="cost_price"
                               value="{{ old('cost_price', $item->cost_price ?? '') }}"
                               class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                        @error('cost_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif

                @if($showField('making_charge'))
                    <div id="makingChargeBlock" class="hidden md:col-span-2">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Making Charge Type</label>
                                <select id="makingChargeType" name="making_charge_type"
                                        class="rv-select mt-1 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:focus:border-teal-400 dark:focus:ring-teal-900/40">
                                    <option value="percentage" @selected(old('making_charge_type', $item->making_charge_type ?? 'percentage') === 'percentage')>Percent (%)</option>
                                    <option value="fixed" @selected(old('making_charge_type', $item->making_charge_type ?? '') === 'fixed')>Fixed Amount (₹)</option>
                                    <option value="per_gram" @selected(old('making_charge_type', $item->making_charge_type ?? '') === 'per_gram')>Per Gram Making Charge</option>
                                    <option value="per_product" @selected(old('making_charge_type', $item->making_charge_type ?? '') === 'per_product')>Whole Product Making Charge</option>
                                </select>
                                @error('making_charge_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label id="makingChargeLabel" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Making Charge (%)</label>
                                <input id="makingChargeField" type="number" step="0.01" min="0"
                                       name="making_charge"
                                       value="{{ old('making_charge', $item->making_charge ?? '') }}"
                                       class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-900/40"
                                       placeholder="Example: 10">
                                @error('making_charge') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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
                        @error('tax_rate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Item Image --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-[#1b2128]">
        <h3 class="mb-4 text-base font-bold text-slate-800 dark:text-slate-100">Item Image</h3>

        <div class="grid gap-4 md:grid-cols-[1fr_180px] md:items-start">
            <div>
                <label for="image" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">
                    Upload Image
                </label>

                <input
                    id="image"
                    type="file"
                    name="image"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900
                           file:mr-4 file:rounded-lg file:border-0 file:bg-teal-600 file:px-4 file:py-2 file:font-semibold file:text-white
                           hover:file:bg-teal-700
                           dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                >

                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                    JPG, JPEG, PNG ya WEBP. Maximum size 2 MB.
                </p>

                @error('image')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-center md:justify-end">
                <div class="h-40 w-40 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800">
                    <img
                        id="imagePreview"
                        src="{{ $item?->image ? asset('storage/' . $item->image) : '' }}"
                        alt="Item image preview"
                        class="{{ $item?->image ? '' : 'hidden' }} h-full w-full object-cover"
                    >

                    <div
                        id="imagePlaceholder"
                        class="{{ $item?->image ? 'hidden' : '' }} flex h-full w-full items-center justify-center p-3 text-center text-xs font-medium text-slate-400"
                    >
                        Image preview
                    </div>
                </div>
            </div>
        </div>
    </div>

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


{{-- Create Unit Modal --}}
<div
    id="unitModal"
    class="fixed inset-0 z-[9999] hidden items-center justify-center p-4"
>
    <div
        id="unitModalOverlay"
        class="absolute inset-0 bg-black/60 backdrop-blur-sm"
    ></div>

    <div
        class="relative z-10 w-full max-w-md overflow-hidden rounded-2xl
               bg-white shadow-2xl dark:bg-[#1A1D23]"
    >
        <div
            class="flex items-center justify-between border-b
                   border-gray-200 px-5 py-4 dark:border-gray-700"
        >
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    Create Unit
                </h2>

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Nayi item unit add karein
                </p>
            </div>

            <button
                type="button"
                id="closeUnitModal"
                class="flex h-9 w-9 items-center justify-center rounded-full
                       bg-gray-100 text-gray-600 hover:bg-red-100
                       hover:text-red-600 dark:bg-gray-800 dark:text-gray-300"
            >
                <span class="text-2xl leading-none">&times;</span>
            </button>
        </div>

        <div id="unitCreateForm" class="space-y-4 p-5">
            @csrf

            <div
                id="unitSuccessMessage"
                class="hidden rounded-lg bg-green-100 px-4 py-3
                       text-sm font-medium text-green-700"
            ></div>

            <div
                id="unitGeneralError"
                class="hidden rounded-lg bg-red-100 px-4 py-3
                       text-sm font-medium text-red-700"
            ></div>

            <div>
                <label
                    for="unitName"
                    class="mb-1 block text-sm font-medium
                           text-gray-700 dark:text-gray-200"
                >
                    Unit Name
                    <span class="text-red-600">*</span>
                </label>

                <input
                    type="text"
                    id="unitName"
                    name="unit_name"
                    autocomplete="off"
                    placeholder="Example: Piece, Kg, Gram, Box"
                    class="w-full rounded-lg border border-gray-300
                        bg-slate-100 px-3 py-2.5 text-gray-900
                        outline-none focus:border-teal-500
                        focus:ring-2 focus:ring-teal-200
                        dark:border-gray-600 dark:bg-gray-800
                        dark:text-white"
                >

                <p
                    id="unitNameError"
                    class="mt-1 hidden text-xs font-medium text-red-600"
                ></p>
            </div>

            <div>
                <label
                    for="unitDescription"
                    class="mb-1 block text-sm font-medium
                           text-gray-700 dark:text-gray-200"
                >
                    Description
                </label>

                <textarea
                    id="unitDescription"
                    name="unit_description"
                    rows="3"
                    placeholder="Optional unit description"
                    class="w-full rounded-lg border border-gray-300
                        bg-slate-100 px-3 py-2.5 text-gray-900
                        outline-none focus:border-teal-500
                        focus:ring-2 focus:ring-teal-200
                        dark:border-gray-600 dark:bg-gray-800
                        dark:text-white"
                ></textarea>

                <p
                    id="unitDescriptionError"
                    class="mt-1 hidden text-xs font-medium text-red-600"
                ></p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button
                    type="button"
                    id="cancelUnitModal"
                    class="rounded-lg bg-gray-200 px-4 py-2
                           text-sm font-semibold text-gray-700
                           hover:bg-gray-300 dark:bg-gray-700
                           dark:text-white dark:hover:bg-gray-600"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    id="unitSubmitButton"
                    class="inline-flex items-center justify-center gap-2
                           rounded-lg bg-teal-600 px-5 py-2
                           text-sm font-semibold text-white
                           hover:bg-teal-700 disabled:cursor-not-allowed
                           disabled:opacity-60"
                >
                    <svg
                        id="unitLoader"
                        class="hidden h-4 w-4 animate-spin"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>

                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                        ></path>
                    </svg>

                    <span id="unitSubmitText">
                        Create Unit
                    </span>
                </button>
            </div>
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


<script>
document.addEventListener('DOMContentLoaded', function () {
    const unitModal = document.getElementById('unitModal');
    const openUnitModal = document.getElementById('openUnitModal');
    const closeUnitModal = document.getElementById('closeUnitModal');
    const cancelUnitModal = document.getElementById('cancelUnitModal');
    const unitModalOverlay = document.getElementById('unitModalOverlay');

    const unitContainer = document.getElementById('unitCreateForm');
    const unitName = document.getElementById('unitName');
    const unitDescription = document.getElementById('unitDescription');

    const unitNameError = document.getElementById('unitNameError');
    const unitDescriptionError = document.getElementById(
        'unitDescriptionError'
    );

    const unitGeneralError = document.getElementById('unitGeneralError');
    const unitSuccessMessage = document.getElementById(
        'unitSuccessMessage'
    );

    const unitSubmitButton = document.getElementById('unitSubmitButton');
    const unitSubmitText = document.getElementById('unitSubmitText');
    const unitLoader = document.getElementById('unitLoader');

    const unitSelect = document.getElementById('unitSelect');
    const emptyUnitMessage = document.getElementById('emptyUnitMessage');

    function showUnitModal() {
        if (!unitModal) {
            return;
        }

        clearUnitMessages();

        unitModal.classList.remove('hidden');
        unitModal.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        setTimeout(function () {
            unitName?.focus();
        }, 100);
    }

    function hideUnitModal() {
        if (!unitModal) {
            return;
        }

        unitModal.classList.add('hidden');
        unitModal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');

        clearUnitMessages();

        if (unitName) {
            unitName.value = '';
        }

        if (unitDescription) {
            unitDescription.value = '';
        }
    }

    function clearUnitMessages() {
        [
            unitNameError,
            unitDescriptionError,
            unitGeneralError,
            unitSuccessMessage
        ].forEach(function (element) {
            if (!element) {
                return;
            }

            element.textContent = '';
            element.classList.add('hidden');
        });
    }

    function setUnitLoading(loading) {
        if (!unitSubmitButton) {
            return;
        }

        unitSubmitButton.disabled = loading;

        if (loading) {
            unitLoader?.classList.remove('hidden');

            if (unitSubmitText) {
                unitSubmitText.textContent = 'Creating...';
            }
        } else {
            unitLoader?.classList.add('hidden');

            if (unitSubmitText) {
                unitSubmitText.textContent = 'Create Unit';
            }
        }
    }

    openUnitModal?.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        showUnitModal();
    });

    closeUnitModal?.addEventListener('click', function (event) {
        event.preventDefault();
        hideUnitModal();
    });

    cancelUnitModal?.addEventListener('click', function (event) {
        event.preventDefault();
        hideUnitModal();
    });

    unitModalOverlay?.addEventListener('click', function () {
        hideUnitModal();
    });

    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape' &&
            unitModal &&
            !unitModal.classList.contains('hidden')
        ) {
            hideUnitModal();
        }
    });

    unitSubmitButton?.addEventListener('click', async function (event) {
        event.preventDefault();
        event.stopPropagation();

        clearUnitMessages();

        const name = unitName?.value.trim() || '';
        const description = unitDescription?.value.trim() || '';

        if (!name) {
            if (unitNameError) {
                unitNameError.textContent = 'Unit name is required.';
                unitNameError.classList.remove('hidden');
            }

            unitName?.focus();
            return;
        }

        setUnitLoading(true);

        try {
            const formData = new FormData();

            formData.append('name', name);
            formData.append('description', description);

            const csrfToken = document.querySelector(
                'meta[name="csrf-token"]'
            )?.getAttribute('content');

            const response = await fetch(
                "{{ route('units.quick-store') }}",
                {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: formData
                }
            );

            let result;

            try {
                result = await response.json();
            } catch (jsonError) {
                throw new Error(
                    'Server se valid response nahi mila.'
                );
            }

            if (!response.ok) {
                if (response.status === 422 && result.errors) {
                    if (result.errors.name?.length) {
                        unitNameError.textContent =
                            result.errors.name[0];

                        unitNameError.classList.remove('hidden');
                    }

                    if (result.errors.description?.length) {
                        unitDescriptionError.textContent =
                            result.errors.description[0];

                        unitDescriptionError.classList.remove('hidden');
                    }

                    return;
                }

                throw new Error(
                    result.message || 'Unit create nahi ho saki.'
                );
            }

            if (!result.unit?.name) {
                throw new Error(
                    'Unit create hui lekin valid details nahi mili.'
                );
            }

            if (unitSelect) {
                let option = Array.from(unitSelect.options).find(
                    function (existingOption) {
                        return existingOption.value
                            .trim()
                            .toLowerCase() ===
                            result.unit.name.trim().toLowerCase();
                    }
                );

                if (!option) {
                    option = new Option(
                        result.unit.name,
                        result.unit.name,
                        true,
                        true
                    );

                    option.dataset.unitId = result.unit.id || '';

                    unitSelect.add(option);
                }

                unitSelect.value = result.unit.name;

                unitSelect.dispatchEvent(
                    new Event('change', {
                        bubbles: true
                    })
                );
            }

            emptyUnitMessage?.classList.add('hidden');

            if (unitSuccessMessage) {
                unitSuccessMessage.textContent =
                    result.message || 'Unit successfully created.';

                unitSuccessMessage.classList.remove('hidden');
            }

            setTimeout(function () {
                hideUnitModal();
            }, 600);

        } catch (error) {
            if (unitGeneralError) {
                unitGeneralError.textContent =
                    error.message ||
                    'Something went wrong. Please try again.';

                unitGeneralError.classList.remove('hidden');
            }
        } finally {
            setUnitLoading(false);
        }
    });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const imagePlaceholder = document.getElementById('imagePlaceholder');

    imageInput?.addEventListener('change', function (event) {
        const file = event.target.files?.[0];

        if (!file) {
            return;
        }

        if (!file.type.startsWith('image/')) {
            event.target.value = '';
            return;
        }

        const reader = new FileReader();

        reader.onload = function (e) {
            if (imagePreview) {
                imagePreview.src = e.target?.result || '';
                imagePreview.classList.remove('hidden');
            }

            imagePlaceholder?.classList.add('hidden');
        };

        reader.readAsDataURL(file);
    });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const barcodeInput =
        document.getElementById('barcode');

    const generateButton =
        document.getElementById('generateBarcodeButton');

    if (!barcodeInput || !generateButton) {
        return;
    }

    generateButton.addEventListener('click', function () {

        /*
         * Client side par naya 12-digit number.
         *
         * Final duplicate checking backend validation
         * se bhi hogi.
         */

        const firstDigit =
            Math.floor(Math.random() * 9) + 1;

        let barcode =
            firstDigit.toString();

        for (let i = 0; i < 11; i++) {
            barcode += Math.floor(
                Math.random() * 10
            ).toString();
        }

        barcodeInput.value = barcode;

        barcodeInput.dispatchEvent(
            new Event('change')
        );

        barcodeInput.focus();
        barcodeInput.select();
    });

});
</script>