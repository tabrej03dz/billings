<x-layouts.app :title="__('Items')">
@php
    $currentItemCount = (int) ($currentItemCount ?? 0);

    $shouldShowItemSuggestion =
        (bool) ($showItemSuggestion ?? true)
        && $currentItemCount === 0;

    $allowedFields = $allowedFields ?? [];

    $showField = function (string $field) use ($allowedFields) {
        return empty($allowedFields)
            || in_array($field, $allowedFields, true);
    };

    $hasAny = function (array $fields) use ($showField) {
        foreach ($fields as $field) {
            if ($showField($field)) {
                return true;
            }
        }

        return false;
    };

    /*
    |--------------------------------------------------------------------------
    | Grouped Table Columns
    |--------------------------------------------------------------------------
    |
    | Individual item fields ko alag-alag column nahi banayenge.
    | Related fields ek grouped column ke andar dikhayenge.
    |
    */
    $tableGroups = [
        'image' => [
            'label' => 'Image',
            'fields' => [],
            'system' => true,
        ],

        'item' => [
            'label' => 'Item Details',
            'fields' => [
                'name',
                'huid',
                'sku',
                'category_id',
                'type',
                'sac',
                'description',
            ],
            'system' => true,
        ],

        'pricing' => [
            'label' => 'Pricing',
            'fields' => [
                'price',
                'cost_price',
                'making_charge',
                'tax_rate',
            ],
        ],

        'stock' => [
            'label' => 'Stock',
            'fields' => [
                'stock_qty',
                'unit',
            ],
        ],

        'metal' => [
            'label' => 'Metal / Weight',
            'fields' => [
                'metal_type',
                'purity',
                'gross_weight',
                'metal_weight',
                'gold_weight',
                'gold_purity',
                'silver_weight',
                'silver_purity',
            ],
        ],

        'stone' => [
            'label' => 'Stone / Diamond',
            'fields' => [
                'stone_weight',
                'stone_charges',
                'diamond_weight',
                'diamond_charges',
            ],
        ],

        'barcode' => [
            'label' => 'Barcode',
            'fields' => [],
            'system' => true,
        ],

        'status' => [
            'label' => 'Status',
            'fields' => [
                'is_active',
            ],
            'system' => true,
        ],
    ];

    $availableGroups = collect($tableGroups)
        ->filter(function ($group) use ($hasAny) {
            if (!empty($group['system'])) {
                return true;
            }

            return $hasAny($group['fields'] ?? []);
        })
        ->all();

    $columnStorageKey =
        'item-table-groups-v4-user-'
        . auth()->id()
        . '-business-'
        . ($activeBusinessId ?? 'default');

    $formatQty = function ($value) {
        return rtrim(
            rtrim(
                number_format((float) ($value ?? 0), 4, '.', ''),
                '0'
            ),
            '.'
        );
    };

    $itemGuideStorageKey =
        'item-guide-v1-user-'
        . auth()->id()
        . '-business-'
        . ($activeBusinessId ?? 'default');
@endphp

<div class="min-h-screen bg-slate-50 dark:bg-[#0f1419]">
    <div class="mx-auto flex max-w-[1600px] flex-col gap-4 px-3 py-4 sm:px-5 lg:px-6">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                <ul class="list-disc space-y-1 pl-5 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- =========================================================
             TOP HEADER
        ========================================================== --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-[#171c22]">
            <div class="border-b border-slate-200 bg-gradient-to-r from-cyan-50 via-white to-emerald-50 p-4 dark:border-slate-700 dark:from-[#22333c] dark:via-[#1c262d] dark:to-[#20352e] sm:p-5">

                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-600 text-white shadow-sm">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7H4m16 0-2-3H6L4 7m16 0v12H4V7m5 4h6"/>
                                </svg>
                            </span>

                            <div>
                                <h1 class="text-xl font-bold text-slate-900 dark:text-white sm:text-2xl">
                                    Items
                                </h1>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400 sm:text-sm">
                                    Products, services, pricing aur stock ek hi jagah manage karein.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:justify-end">

                        <a
                            href="{{ route('items.import.form') }}"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300 sm:h-11 sm:text-sm"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V4m0 0-4 4m4-4 4 4M5 20h14"/>
                            </svg>
                            Import
                        </a>

                        <a
                            href="{{ route('items.create') }}"
                            class="relative inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-3 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700 sm:h-11 sm:px-4 sm:text-sm
                            {{ $shouldShowItemSuggestion ? 'item-suggestion-blink' : '' }}"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Item
                        </a>

                        <form
                            method="POST"
                            action="{{ route('items.barcodes.generate-missing') }}"
                            class="col-span-2 sm:col-auto"
                            onsubmit="return confirm('Generate barcodes for all items that do not have a barcode?')"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-3 text-xs font-bold text-blue-700 transition hover:bg-blue-100 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-300 sm:h-11 sm:w-auto sm:text-sm"
                            >
                                Generate Missing Barcodes
                            </button>
                        </form>

                        <a
                            href="{{ route('items.ai.create') }}"
                            class="col-span-2 inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-3 text-xs font-bold text-indigo-700 transition hover:bg-indigo-100 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-300 sm:col-auto sm:h-11 sm:text-sm"
                        >
                            AI Photo Entry
                        </a>
                    </div>
                </div>
            </div>



            {{-- =====================================================
                 COMPACT FILTER BAR
            ====================================================== --}}
            <div class="p-3 sm:p-4">

                <form method="GET" id="itemCompactFilterForm">
                    <div class="flex flex-col gap-2 lg:flex-row lg:items-end">

                        {{-- Search --}}
                        <div class="min-w-0 flex-1">
                            <label for="item-search" class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Search Items
                            </label>

                            <div class="relative">
                                <svg
                                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
                                    />
                                </svg>

                                <input
                                    id="item-search"
                                    type="text"
                                    name="q"
                                    value="{{ $q }}"
                                    placeholder="Name, SKU, barcode, HUID..."
                                    class="h-10 w-full rounded-xl border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:ring-cyan-900/30"
                                >
                            </div>
                        </div>

                        {{-- Compact actions --}}
                        <div class="flex shrink-0 items-center gap-2">

                            <button
                                type="submit"
                                class="inline-flex h-10 items-center justify-center rounded-xl bg-slate-900 px-4 text-xs font-bold text-white transition hover:bg-slate-700 dark:bg-cyan-700 dark:hover:bg-cyan-600 sm:text-sm"
                            >
                                Search
                            </button>

                            <button
                                type="button"
                                id="advancedFilterToggle"
                                aria-expanded="false"
                                aria-controls="advancedFilterPanel"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-3 text-xs font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:hover:bg-slate-700 sm:text-sm"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 5h16M7 12h10m-7 7h4"
                                    />
                                </svg>

                                Filters

                                @if(
                                    filled($category_id)
                                    || ($active !== null && $active !== '')
                                )
                                    <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-cyan-600 px-1 text-[10px] text-white">
                                        {{ (filled($category_id) ? 1 : 0) + (($active !== null && $active !== '') ? 1 : 0) }}
                                    </span>
                                @endif
                            </button>

                            @if(
                                filled($q)
                                || filled($category_id)
                                || ($active !== null && $active !== '')
                            )
                                <a
                                    href="{{ route('items.index') }}"
                                    class="inline-flex h-10 items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 text-xs font-bold text-red-600 transition hover:bg-red-100 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300 sm:text-sm"
                                    title="Clear filters"
                                >
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>


                    {{-- Advanced filters - hidden by default --}}
                    <div
                        id="advancedFilterPanel"
                        class="mt-3 hidden rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900/40"
                    >
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(220px,320px)_minmax(180px,240px)_auto] lg:items-end">

                            {{-- Category --}}
                            <div>
                                <label for="category-filter" class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Category
                                </label>

                                <select
                                    id="category-filter"
                                    name="category_id"
                                    class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:ring-cyan-900/30"
                                >
                                    <option value="">All Categories</option>

                                    @foreach($categories as $cat)
                                        <option
                                            value="{{ $cat->id }}"
                                            @selected((string)$category_id === (string)$cat->id)
                                        >
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Status --}}
                            <div>
                                <label for="status-filter" class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Status
                                </label>

                                <select
                                    id="status-filter"
                                    name="active"
                                    class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:ring-cyan-900/30"
                                >
                                    <option value="">Any Status</option>
                                    <option value="1" @selected($active === '1')>Active</option>
                                    <option value="0" @selected($active === '0')>Inactive</option>
                                </select>
                            </div>

                            <div class="flex gap-2">
                                <button
                                    type="submit"
                                    class="inline-flex h-10 items-center justify-center rounded-xl bg-cyan-600 px-4 text-xs font-bold text-white transition hover:bg-cyan-700 sm:text-sm"
                                >
                                    Apply Filters
                                </button>

                                <button
                                    type="button"
                                    id="advancedFilterClose"
                                    class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-xs font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-white sm:text-sm"
                                >
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </section>


        {{-- =========================================================
             FIRST ITEM GUIDE
        ========================================================== --}}
        @if($shouldShowItemSuggestion)
            <section
                id="itemSuggestionGuide"
                data-storage-key="{{ $itemGuideStorageKey }}"
                class="relative hidden overflow-hidden rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-900 dark:bg-emerald-950/30 sm:block"
            >
                <button
                    type="button"
                    onclick="dismissItemGuide()"
                    class="absolute right-3 top-3 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm dark:bg-slate-800 dark:text-slate-300"
                >
                    ×
                </button>

                <div class="pr-10">
                    <div class="text-sm font-bold text-emerald-800 dark:text-emerald-300">
                        Start by creating your first item
                    </div>

                    <p class="mt-1 text-xs leading-5 text-emerald-700 dark:text-emerald-400">
                        Item create karne ke baad aap yahin se search, barcode print, edit aur stock manage kar sakte hain.
                    </p>
                </div>
            </section>
        @endif


        {{-- =========================================================
             LIST SECTION
        ========================================================== --}}
        <section class="overflow-visible rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-[#171c22]">

            {{-- List toolbar --}}
            <div class="flex flex-col gap-3 border-b border-slate-200 p-3 dark:border-slate-700 sm:flex-row sm:items-center sm:justify-between sm:p-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">
                            Item List
                        </h2>

                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            {{ $items->total() }}
                        </span>
                    </div>

                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Columns ab content ke hisaab se flexible width lenge; kam data wale columns compact aur zyada data wale columns wider rahenge.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">

                    {{-- Group chooser --}}
                    <div class="relative">
                        <button
                            type="button"
                            id="columnChooserButton"
                            class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-white sm:text-sm"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                            Display
                        </button>

                        <div
                            id="columnChooserMenu"
                            class="absolute right-0 z-[100] mt-2 hidden w-64 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-800"
                        >
                            <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">
                                <div class="text-sm font-bold text-slate-900 dark:text-white">
                                    Show / Hide Groups
                                </div>
                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    Business type ke available groups
                                </div>
                            </div>

                            <div class="max-h-72 overflow-y-auto p-2">
                                @foreach($availableGroups as $groupKey => $groupConfig)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-700">
                                        <input
                                            type="checkbox"
                                            class="item-column-toggle h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                                            value="{{ $groupKey }}"
                                            checked
                                        >

                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                            {{ $groupConfig['label'] }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="grid grid-cols-2 gap-2 border-t border-slate-200 p-3 dark:border-slate-700">
                                <button
                                    type="button"
                                    id="showAllColumns"
                                    class="rounded-lg bg-cyan-600 px-3 py-2 text-xs font-bold text-white hover:bg-cyan-700"
                                >
                                    Show All
                                </button>

                                <button
                                    type="button"
                                    id="resetColumns"
                                    class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:text-white dark:hover:bg-slate-700"
                                >
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Barcode print --}}
                    <form
                        id="barcodeBulkForm"
                        method="POST"
                        action="{{ route('items.barcodes.print') }}"
                        target="_blank"
                        class="flex items-center gap-2"
                    >
                        @csrf

                        <input
                            type="number"
                            name="quantity"
                            value="1"
                            min="1"
                            max="200"
                            title="Barcode copies"
                            class="h-10 w-16 rounded-xl border border-slate-300 bg-white px-2 text-center text-xs font-bold text-slate-800 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        >

                        <button
                            type="submit"
                            class="inline-flex h-10 items-center justify-center rounded-xl bg-purple-600 px-3 text-xs font-bold text-white transition hover:bg-purple-700"
                        >
                            Print Selected
                        </button>
                    </form>
                </div>
            </div>


            {{-- =====================================================
                 MOBILE / TABLET CARDS
            ====================================================== --}}
            <div class="mobile-items-list divide-y divide-slate-200/80 dark:divide-slate-700/80 lg:hidden">
                @forelse($items as $it)
                    <article class="p-3 sm:p-4">

                        <div class="flex gap-3">
                            <div class="shrink-0">
                                @if($it->image)
                                    <img
                                        src="{{ asset('storage/' . $it->image) }}"
                                        alt="{{ $it->name }}"
                                        class="h-16 w-16 rounded-xl border border-slate-200 object-cover dark:border-slate-700"
                                    >
                                @else
                                    <div class="flex h-16 w-16 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-[10px] text-slate-400 dark:border-slate-700 dark:bg-slate-800">
                                        No Image
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-sm font-bold text-slate-900 dark:text-white">
                                            {{ $it->name ?? 'Unnamed Item' }}
                                        </h3>

                                        <div class="mt-1 flex flex-wrap gap-x-2 gap-y-1 text-[11px] text-slate-500 dark:text-slate-400">
                                            @if($showField('sku') && $it->sku)
                                                <span>SKU: {{ $it->sku }}</span>
                                            @endif

                                            @if($showField('category_id') && $it->category?->name)
                                                <span>• {{ $it->category->name }}</span>
                                            @endif

                                            @if($showField('huid') && $it->huid)
                                                <span>• HUID: {{ $it->huid }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($it->is_active)
                                        <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold text-emerald-700">
                                            Active
                                        </span>
                                    @else
                                        <span class="shrink-0 rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-600">
                                            Inactive
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-3 grid grid-cols-2 gap-2 text-xs sm:grid-cols-3">

                                    @if($showField('price'))
                                        <div class="rounded-lg bg-slate-50 p-2 dark:bg-slate-800">
                                            <div class="text-[10px] font-bold uppercase text-slate-400">
                                                Price
                                            </div>
                                            <div class="mt-0.5 font-bold text-slate-900 dark:text-white">
                                                ₹{{ number_format((float)($it->price ?? 0), 2) }}
                                            </div>
                                        </div>
                                    @endif

                                    @if($showField('stock_qty'))
                                        <div class="rounded-lg bg-slate-50 p-2 dark:bg-slate-800">
                                            <div class="text-[10px] font-bold uppercase text-slate-400">
                                                Stock
                                            </div>
                                            <div class="mt-0.5 font-bold text-slate-900 dark:text-white">
                                                {{ $formatQty($it->stock_qty) }}
                                                @if($showField('unit') && $it->unit)
                                                    {{ $it->unit }}
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if($showField('metal_type') || $showField('gross_weight'))
                                        <div class="rounded-lg bg-slate-50 p-2 dark:bg-slate-800">
                                            <div class="text-[10px] font-bold uppercase text-slate-400">
                                                Metal
                                            </div>
                                            <div class="mt-0.5 font-bold text-slate-900 dark:text-white">
                                                {{ $it->metal_type ? ucfirst($it->metal_type) : '—' }}

                                                @if($showField('gross_weight') && $it->gross_weight)
                                                    · {{ $it->gross_weight }}g
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-2 border-t border-slate-100 pt-3 dark:border-slate-700">
                            <div class="flex items-center gap-2">
                                <input
                                    class="barcode-item-checkbox h-4 w-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500"
                                    type="checkbox"
                                    form="barcodeBulkForm"
                                    name="item_ids[]"
                                    value="{{ $it->id }}"
                                >

                                @if($it->barcode)
                                    <span class="font-mono text-[10px] text-slate-500 dark:text-slate-400">
                                        {{ $it->barcode }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                <a
                                    href="{{ route('items.edit', $it->id) }}"
                                    class="inline-flex h-8 items-center justify-center rounded-lg bg-amber-500 px-3 text-xs font-bold text-white hover:bg-amber-600"
                                >
                                    Edit
                                </a>

                                <form
                                    action="{{ route('items.destroy', $it->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Delete this item?');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="inline-flex h-8 items-center justify-center rounded-lg bg-red-600 px-3 text-xs font-bold text-white hover:bg-red-700"
                                    >
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="p-10 text-center">
                        <div class="text-sm font-bold text-slate-700 dark:text-slate-200">
                            No items found
                        </div>

                        <a
                            href="{{ route('items.create') }}"
                            class="mt-3 inline-flex rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white"
                        >
                            Create Item
                        </a>
                    </div>
                @endforelse
            </div>


            {{-- =====================================================
                 DESKTOP GROUPED TABLE
            ====================================================== --}}
            <div class="desktop-items-table hidden lg:block">
                <div class="w-full overflow-x-auto">
                    <table
                        id="itemsDynamicTable"
                        class="min-w-full table-auto text-left text-sm text-slate-700 dark:text-slate-300"
                    >

                        <thead class="bg-slate-100 text-[11px] font-bold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                            <tr>
                                <th class="w-px whitespace-nowrap px-3 py-3">
                                    <input
                                        type="checkbox"
                                        id="selectAllBarcodeItems"
                                        class="h-4 w-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500"
                                    >
                                </th>

                                @foreach($availableGroups as $groupKey => $groupConfig)
                                    <th
                                        class="item-table-column px-3 py-3"
                                        data-column="{{ $groupKey }}"
                                    >
                                        {{ $groupConfig['label'] }}
                                    </th>
                                @endforeach

                                <th class="w-px whitespace-nowrap px-3 py-3 text-right">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200/80 bg-white dark:divide-slate-700/80 dark:bg-[#171c22]">
                            @forelse($items as $it)
                                <tr class="align-top border-b border-slate-200/70 transition last:border-b-0 hover:bg-slate-50 dark:border-slate-700/70 dark:hover:bg-slate-800/50">

                                    {{-- select --}}
                                    <td class="w-px whitespace-nowrap px-3 py-4">
                                        <input
                                            class="barcode-item-checkbox h-4 w-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500"
                                            type="checkbox"
                                            form="barcodeBulkForm"
                                            name="item_ids[]"
                                            value="{{ $it->id }}"
                                        >
                                    </td>


                                    {{-- IMAGE --}}
                                    @if(isset($availableGroups['image']))
                                        <td
                                            class="item-table-column px-3 py-4"
                                            data-column="image"
                                        >
                                            @if($it->image)
                                                <a
                                                    href="{{ asset('storage/' . $it->image) }}"
                                                    target="_blank"
                                                    class="inline-block"
                                                    title="Open image"
                                                >
                                                    <img
                                                        src="{{ asset('storage/' . $it->image) }}"
                                                        alt="{{ $it->name }}"
                                                        class="h-14 w-14 rounded-xl border border-slate-200 object-cover shadow-sm transition hover:scale-105 dark:border-slate-700"
                                                        loading="lazy"
                                                    >
                                                </a>
                                            @else
                                                <div
                                                    class="flex h-14 w-14 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 px-1 text-center text-[9px] font-medium leading-3 text-slate-400 dark:border-slate-600 dark:bg-slate-800"
                                                >
                                                    No Image
                                                </div>
                                            @endif
                                        </td>
                                    @endif


                                    {{-- ITEM DETAILS --}}
                                    @if(isset($availableGroups['item']))
                                        <td
                                            class="item-table-column px-3 py-4"
                                            data-column="item"
                                        >
                                            <div class="min-w-0">
                                                    @if($showField('name'))
                                                        <div class="truncate font-bold text-slate-900 dark:text-white" title="{{ $it->name }}">
                                                            {{ $it->name ?? '—' }}
                                                        </div>
                                                    @endif

                                                    <div class="mt-1 space-y-0.5 text-[11px] leading-4 text-slate-500 dark:text-slate-400">
                                                        @if($showField('sku') && $it->sku)
                                                            <div>
                                                                <span class="font-semibold">SKU:</span> {{ $it->sku }}
                                                            </div>
                                                        @endif

                                                        @if($showField('huid') && $it->huid)
                                                            <div>
                                                                <span class="font-semibold">HUID:</span> {{ $it->huid }}
                                                            </div>
                                                        @endif

                                                        @if($showField('category_id') && $it->category?->name)
                                                            <div>
                                                                {{ $it->category->name }}
                                                            </div>
                                                        @endif

                                                        @if($showField('type') && $it->type)
                                                            <div class="capitalize">
                                                                {{ $it->type }}
                                                            </div>
                                                        @endif

                                                        @if($showField('sac') && $it->sac)
                                                            <div>
                                                                SAC: {{ $it->sac }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    @if($showField('description') && $it->description)
                                                        <div
                                                            class="mt-1 line-clamp-2 text-[11px] leading-4 text-slate-400"
                                                            title="{{ $it->description }}"
                                                        >
                                                            {{ $it->description }}
                                                        </div>
                                                    @endif
                                            </div>
                                        </td>
                                    @endif


                                    {{-- PRICING --}}
                                    @if(isset($availableGroups['pricing']))
                                        <td
                                            class="item-table-column px-3 py-4"
                                            data-column="pricing"
                                        >
                                            <div class="space-y-1.5 text-xs">

                                                @if($showField('price'))
                                                    <div>
                                                        <div class="text-[10px] font-bold uppercase text-slate-400">
                                                            Sale
                                                        </div>
                                                        <div class="font-bold text-slate-900 dark:text-white">
                                                            ₹{{ number_format((float)($it->price ?? 0), 2) }}
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($showField('cost_price') && $it->cost_price !== null)
                                                    <div class="text-slate-500 dark:text-slate-400">
                                                        Cost: ₹{{ number_format((float)$it->cost_price, 2) }}
                                                    </div>
                                                @endif

                                                @if($showField('making_charge') && $it->making_charge !== null)
                                                    <div class="text-slate-500 dark:text-slate-400">
                                                        MC:
                                                        @if($it->making_charge_type === 'percentage')
                                                            {{ rtrim(rtrim(number_format((float)$it->making_charge, 2), '0'), '.') }}%
                                                        @elseif($it->making_charge_type === 'per_gram')
                                                            ₹{{ number_format((float)$it->making_charge, 2) }}/g
                                                        @else
                                                            ₹{{ number_format((float)$it->making_charge, 2) }}
                                                        @endif
                                                    </div>
                                                @endif

                                                @if($showField('tax_rate'))
                                                    <div class="text-slate-500 dark:text-slate-400">
                                                        Tax: {{ rtrim(rtrim(number_format((float)($it->tax_rate ?? 0), 2), '0'), '.') }}%
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    @endif


                                    {{-- STOCK --}}
                                    @if(isset($availableGroups['stock']))
                                        <td
                                            class="item-table-column px-3 py-4"
                                            data-column="stock"
                                        >
                                            @if($showField('stock_qty'))
                                                <div class="text-base font-bold text-slate-900 dark:text-white">
                                                    {{ $formatQty($it->stock_qty) }}
                                                </div>
                                            @endif

                                            @if($showField('unit') && $it->unit)
                                                <div class="mt-1 text-[11px] font-semibold uppercase text-slate-500 dark:text-slate-400">
                                                    {{ $it->unit }}
                                                </div>
                                            @endif
                                        </td>
                                    @endif


                                    {{-- METAL --}}
                                    @if(isset($availableGroups['metal']))
                                        <td
                                            class="item-table-column px-3 py-4"
                                            data-column="metal"
                                        >
                                            <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-[11px] leading-4">

                                                @if($showField('metal_type') && $it->metal_type)
                                                    <div class="col-span-2">
                                                        <span class="font-bold text-slate-700 dark:text-slate-200">
                                                            {{ ucfirst($it->metal_type) }}
                                                        </span>

                                                        @if($showField('purity') && $it->purity)
                                                            <span class="text-slate-500">
                                                                · {{ $it->purity }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif

                                                @if($showField('gross_weight') && $it->gross_weight !== null)
                                                    <div>
                                                        <span class="text-slate-400">Gross</span>
                                                        <div class="font-semibold text-slate-700 dark:text-slate-200">
                                                            {{ $it->gross_weight }}
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($showField('metal_weight') && $it->metal_weight !== null)
                                                    <div>
                                                        <span class="text-slate-400">Metal</span>
                                                        <div class="font-semibold text-slate-700 dark:text-slate-200">
                                                            {{ $it->metal_weight }}
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($showField('gold_weight') && $it->gold_weight !== null)
                                                    <div>
                                                        <span class="text-slate-400">Gold</span>
                                                        <div class="font-semibold text-amber-700 dark:text-amber-300">
                                                            {{ $it->gold_weight }}
                                                            @if($showField('gold_purity') && $it->gold_purity)
                                                                · {{ $it->gold_purity }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($showField('silver_weight') && $it->silver_weight !== null)
                                                    <div>
                                                        <span class="text-slate-400">Silver</span>
                                                        <div class="font-semibold text-slate-700 dark:text-slate-200">
                                                            {{ $it->silver_weight }}
                                                            @if($showField('silver_purity') && $it->silver_purity)
                                                                · {{ $it->silver_purity }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    @endif


                                    {{-- STONE / DIAMOND --}}
                                    @if(isset($availableGroups['stone']))
                                        <td
                                            class="item-table-column px-3 py-4"
                                            data-column="stone"
                                        >
                                            <div class="space-y-1 text-[11px] leading-4">

                                                @if($showField('stone_weight') && $it->stone_weight !== null)
                                                    <div>
                                                        <span class="text-slate-400">Stone:</span>
                                                        <span class="font-semibold text-slate-700 dark:text-slate-200">
                                                            {{ $it->stone_weight }}
                                                        </span>
                                                    </div>
                                                @endif

                                                @if($showField('stone_charges') && $it->stone_charges !== null)
                                                    <div>
                                                        <span class="text-slate-400">Stone Chg:</span>
                                                        <span class="font-semibold text-slate-700 dark:text-slate-200">
                                                            ₹{{ number_format((float)$it->stone_charges, 2) }}
                                                        </span>
                                                    </div>
                                                @endif

                                                @if($showField('diamond_weight') && $it->diamond_weight !== null)
                                                    <div>
                                                        <span class="text-slate-400">Diamond:</span>
                                                        <span class="font-semibold text-slate-700 dark:text-slate-200">
                                                            {{ $it->diamond_weight }}
                                                        </span>
                                                    </div>
                                                @endif

                                                @if($showField('diamond_charges') && $it->diamond_charges !== null)
                                                    <div>
                                                        <span class="text-slate-400">Diamond Chg:</span>
                                                        <span class="font-semibold text-slate-700 dark:text-slate-200">
                                                            ₹{{ number_format((float)$it->diamond_charges, 2) }}
                                                        </span>
                                                    </div>
                                                @endif

                                                @if(
                                                    (!$showField('stone_weight') || $it->stone_weight === null)
                                                    && (!$showField('stone_charges') || $it->stone_charges === null)
                                                    && (!$showField('diamond_weight') || $it->diamond_weight === null)
                                                    && (!$showField('diamond_charges') || $it->diamond_charges === null)
                                                )
                                                    <span class="text-slate-400">—</span>
                                                @endif
                                            </div>
                                        </td>
                                    @endif


                                    {{-- BARCODE --}}
                                    @if(isset($availableGroups['barcode']))
                                        <td
                                            class="item-table-column px-3 py-4"
                                            data-column="barcode"
                                        >
                                            @if($it->barcode)
                                                <div class="break-all font-mono text-[10px] text-slate-600 dark:text-slate-300">
                                                    {{ $it->barcode }}
                                                </div>

                                                <a
                                                    href="{{ route('items.barcode.print', [
                                                        'item' => $it->id,
                                                        'quantity' => 1,
                                                        'print' => 1
                                                    ]) }}"
                                                    target="_blank"
                                                    class="mt-2 inline-flex rounded-lg bg-purple-100 px-2 py-1 text-[10px] font-bold text-purple-700 hover:bg-purple-200 dark:bg-purple-900/40 dark:text-purple-300"
                                                >
                                                    Print
                                                </a>
                                            @else
                                                <form
                                                    action="{{ route('items.barcode.generate', $it->id) }}"
                                                    method="POST"
                                                >
                                                    @csrf

                                                    <button
                                                        type="submit"
                                                        class="rounded-lg bg-blue-100 px-2 py-1 text-[10px] font-bold text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300"
                                                    >
                                                        Generate
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    @endif


                                    {{-- STATUS --}}
                                    @if(isset($availableGroups['status']))
                                        <td
                                            class="item-table-column px-3 py-4"
                                            data-column="status"
                                        >
                                            @if($it->is_active)
                                                <span class="inline-flex rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold text-emerald-700">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-600">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                    @endif


                                    {{-- ACTIONS --}}
                                    <td class="w-px whitespace-nowrap px-3 py-4">
                                        <div class="flex justify-end gap-1.5">

                                            @can('create invoice')
                                                <a
                                                    href="{{ route('invoices.create', [
                                                        'type' => 'tax',
                                                        'item_id' => $it->id,
                                                    ]) }}"
                                                    class="inline-flex h-8 items-center justify-center
                                                        rounded-lg bg-emerald-600 px-2.5
                                                        text-[10px] font-bold text-white
                                                        hover:bg-emerald-700"
                                                    title="Sell this item / Create invoice"
                                                >
                                                    Sell / Invoice
                                                </a>
                                            @endcan

                                            <a
                                                href="{{ route('items.edit', $it->id) }}"
                                                class="inline-flex h-8 items-center justify-center
                                                    rounded-lg bg-amber-500 px-2.5
                                                    text-[10px] font-bold text-white
                                                    hover:bg-amber-600"
                                            >
                                                Edit
                                            </a>

                                            <form
                                                action="{{ route('items.destroy', $it->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Delete this item?');"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="inline-flex h-8 items-center justify-center
                                                        rounded-lg bg-red-600 px-2.5
                                                        text-[10px] font-bold text-white
                                                        hover:bg-red-700"
                                                >
                                                    Del
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="{{ count($availableGroups) + 2 }}"
                                        class="px-6 py-12 text-center text-sm text-slate-500"
                                    >
                                        No items found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>


        {{-- Pagination --}}
        <div>
            {{ $items->links() }}
        </div>
    </div>
</div>


<style>
    @keyframes itemSuggestionBlink {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, .35);
        }

        50% {
            box-shadow: 0 0 0 7px rgba(16, 185, 129, 0);
        }
    }

    .item-suggestion-blink {
        animation: itemSuggestionBlink 1.4s ease-in-out infinite;
    }

    /*
    |--------------------------------------------------------------------------
    | Flexible Desktop Table
    |--------------------------------------------------------------------------
    | table-layout:auto browser ko content ke hisaab se width distribute
    | karne deta hai. Compact columns sirf utni jagah lenge jitni zaroori hai,
    | jabki Item / Metal jaise content-heavy columns remaining space use karenge.
    */
    #itemsDynamicTable {
        table-layout: auto;
        width: 100%;
    }

    #itemsDynamicTable th,
    #itemsDynamicTable td {
        vertical-align: top;
    }

    #itemsDynamicTable td {
        overflow-wrap: break-word;
        word-break: normal;
    }

    /*
    | Compact columns:
    | width:1% + nowrap ka matlab fixed width nahi hai.
    | Browser inhe content ke minimum required width tak rakhega.
    */
    #itemsDynamicTable [data-column="image"],
    #itemsDynamicTable [data-column="stock"],
    #itemsDynamicTable [data-column="barcode"],
    #itemsDynamicTable [data-column="status"] {
        width: 1%;
        white-space: nowrap;
    }

    /*
    | Medium content columns.
    | Ye fixed nahi hain; sirf unnecessary squeezing ko rokne ke liye
    | soft minimum diya gaya hai.
    */
    #itemsDynamicTable [data-column="pricing"] {
        min-width: 120px;
        white-space: nowrap;
    }

    #itemsDynamicTable [data-column="stone"] {
        min-width: 135px;
    }

    #itemsDynamicTable [data-column="metal"] {
        min-width: 155px;
    }

    /*
    | Item Details ko sabse zyada flexible space milega.
    */
    #itemsDynamicTable [data-column="item"] {
        min-width: 210px;
        width: auto;
    }

    #itemsDynamicTable [data-column="item"] .truncate {
        white-space: normal;
        overflow: visible;
        text-overflow: clip;
    }

    /*
    | Small desktop par table horizontally scroll ho sakti hai,
    | lekin columns fixed percentage width me congest nahi honge.
    */
    @media (min-width: 1024px) {
        #itemsDynamicTable {
            min-width: max-content;
        }

        #itemsDynamicTable [data-column="item"] {
            max-width: 360px;
        }

        #itemsDynamicTable [data-column="metal"] {
            max-width: 260px;
        }

        #itemsDynamicTable [data-column="stone"] {
            max-width: 220px;
        }
    }

    @media (min-width: 1440px) {
        #itemsDynamicTable {
            min-width: 100%;
        }

        #itemsDynamicTable [data-column="item"] {
            max-width: 460px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .item-suggestion-blink {
            animation: none;
        }
    }
</style>


<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Compact Advanced Filters
    |--------------------------------------------------------------------------
    */
    const advancedFilterToggle =
        document.getElementById('advancedFilterToggle');

    const advancedFilterPanel =
        document.getElementById('advancedFilterPanel');

    const advancedFilterClose =
        document.getElementById('advancedFilterClose');

    const hasActiveAdvancedFilters =
        @json(
            filled($category_id)
            || ($active !== null && $active !== '')
        );

    function openAdvancedFilters() {

        if (!advancedFilterPanel) {
            return;
        }

        advancedFilterPanel.classList.remove('hidden');

        advancedFilterToggle?.setAttribute(
            'aria-expanded',
            'true'
        );
    }

    function closeAdvancedFilters() {

        if (!advancedFilterPanel) {
            return;
        }

        advancedFilterPanel.classList.add('hidden');

        advancedFilterToggle?.setAttribute(
            'aria-expanded',
            'false'
        );
    }

    if (hasActiveAdvancedFilters) {
        openAdvancedFilters();
    }

    advancedFilterToggle?.addEventListener(
        'click',
        function () {

            if (!advancedFilterPanel) {
                return;
            }

            const isHidden =
                advancedFilterPanel
                    .classList
                    .contains('hidden');

            if (isHidden) {
                openAdvancedFilters();
            } else {
                closeAdvancedFilters();
            }
        }
    );

    advancedFilterClose?.addEventListener(
        'click',
        closeAdvancedFilters
    );


    /*
    |--------------------------------------------------------------------------
    | Select All
    |--------------------------------------------------------------------------
    */
    const selectAll = document.getElementById('selectAllBarcodeItems');

    selectAll?.addEventListener('change', function () {

        document
            .querySelectorAll('.barcode-item-checkbox')
            .forEach(function (checkbox) {

                checkbox.checked =
                    selectAll.checked;
            });
    });


    /*
    |--------------------------------------------------------------------------
    | Barcode Bulk Print Validation
    |--------------------------------------------------------------------------
    */
    const bulkForm = document.getElementById('barcodeBulkForm');

    bulkForm?.addEventListener('submit', function (event) {

        const selected =
            document.querySelectorAll(
                '.barcode-item-checkbox:checked'
            );

        if (selected.length === 0) {

            event.preventDefault();

            alert('Please select at least one item.');
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Group Visibility
    |--------------------------------------------------------------------------
    */
    const storageKey =
        @json($columnStorageKey);

    const availableColumns =
        @json(array_keys($availableGroups));

    const chooserButton =
        document.getElementById(
            'columnChooserButton'
        );

    const chooserMenu =
        document.getElementById(
            'columnChooserMenu'
        );

    const toggles =
        Array.from(
            document.querySelectorAll(
                '.item-column-toggle'
            )
        );

    const showAllButton =
        document.getElementById(
            'showAllColumns'
        );

    const resetButton =
        document.getElementById(
            'resetColumns'
        );

    const defaultColumns =
        [...availableColumns];


    function getSavedColumns() {

        try {

            const saved =
                JSON.parse(
                    localStorage.getItem(
                        storageKey
                    )
                );

            if (!Array.isArray(saved)) {
                return defaultColumns;
            }

            return saved.filter(column =>
                availableColumns.includes(
                    column
                )
            );

        } catch (error) {

            return defaultColumns;
        }
    }


    function saveColumns(columns) {

        localStorage.setItem(
            storageKey,
            JSON.stringify(columns)
        );
    }


    function applyColumns(columns) {

        const visibleSet =
            new Set(columns);

        document
            .querySelectorAll(
                '.item-table-column'
            )
            .forEach(function (element) {

                const column =
                    element.dataset.column;

                element.classList.toggle(
                    'hidden',
                    !visibleSet.has(column)
                );
            });


        toggles.forEach(function (toggle) {

            toggle.checked =
                visibleSet.has(
                    toggle.value
                );
        });
    }


    function selectedColumns() {

        return toggles
            .filter(toggle =>
                toggle.checked
            )
            .map(toggle =>
                toggle.value
            )
            .filter(column =>
                availableColumns.includes(
                    column
                )
            );
    }


    applyColumns(
        getSavedColumns()
    );


    chooserButton?.addEventListener(
        'click',
        function (event) {

            event.stopPropagation();

            chooserMenu?.classList.toggle(
                'hidden'
            );
        }
    );


    chooserMenu?.addEventListener(
        'click',
        function (event) {

            event.stopPropagation();
        }
    );


    document.addEventListener(
        'click',
        function () {

            chooserMenu?.classList.add(
                'hidden'
            );
        }
    );


    toggles.forEach(function (toggle) {

        toggle.addEventListener(
            'change',
            function () {

                const columns =
                    selectedColumns();

                saveColumns(columns);

                applyColumns(columns);
            }
        );
    });


    showAllButton?.addEventListener(
        'click',
        function () {

            const columns =
                [...availableColumns];

            saveColumns(columns);

            applyColumns(columns);
        }
    );


    resetButton?.addEventListener(
        'click',
        function () {

            localStorage.removeItem(
                storageKey
            );

            applyColumns(
                defaultColumns
            );
        }
    );
});
</script>


@if($shouldShowItemSuggestion)
<script>
    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const guide =
                document.getElementById(
                    'itemSuggestionGuide'
                );

            if (!guide) {
                return;
            }

            const storageKey =
                guide.dataset.storageKey;

            if (
                storageKey
                && localStorage.getItem(
                    storageKey
                ) === '1'
            ) {
                guide.classList.add(
                    'hidden'
                );
            } else {
                guide.classList.remove(
                    'hidden'
                );
            }
        }
    );


    function dismissItemGuide() {

        const guide =
            document.getElementById(
                'itemSuggestionGuide'
            );

        if (!guide) {
            return;
        }

        const storageKey =
            guide.dataset.storageKey;

        if (storageKey) {
            localStorage.setItem(
                storageKey,
                '1'
            );
        }

        guide.classList.add(
            'hidden'
        );
    }
</script>
@endif

</x-layouts.app>