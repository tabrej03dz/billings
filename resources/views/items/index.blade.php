<x-layouts.app :title="__('Items')">
    @php
        /*
         * Suggestion sirf tab dikhana hai jab active business me ek bhi item na ho.
         *
         * Controller se $currentItemCount me UNFILTERED business item count bhejein.
         * Filter/search ke baad $items empty hone par bhi suggestion dobara nahi aayega.
         */
        $currentItemCount = (int) ($currentItemCount ?? 0);

        $shouldShowItemSuggestion =
            (bool) ($showItemSuggestion ?? true)
            && $currentItemCount === 0;
    @endphp
    <div class="flex flex-col gap-4">
        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-3 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-red-700">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Compact responsive page header --}}
        <section class="overflow-hidden rounded-2xl border border-cyan-200 bg-[#DDF4F4] shadow-sm dark:border-slate-700 dark:bg-[#263A44]">
            <div class="p-3 sm:p-5">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <h1 class="text-lg font-bold text-slate-900 dark:text-white sm:text-2xl">
                            Items
                        </h1>

                        {{-- Description is hidden on mobile --}}
                        <p class="mt-1 hidden text-sm text-slate-600 dark:text-slate-300 sm:block">
                            Search, create and manage your products or services.
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        {{-- Mobile filter toggle --}}
                        <button
                            type="button"
                            id="mobileFilterToggle"
                            aria-expanded="false"
                            aria-controls="itemFilterPanel"
                            class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-white sm:hidden"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h16M7 12h10m-7 7h4"/>
                            </svg>
                            Filter
                        </button>

                       <div class="relative inline-flex flex-col items-end">
                            @if($shouldShowItemSuggestion)
                                <div
                                    class="pointer-events-none relative z-50 mb-5
                                        flex w-max max-w-[250px] justify-end sm:max-w-none"
                                >
                                    <div
                                        class="relative rounded-xl border border-emerald-400
                                            bg-slate-900 px-3 py-2 text-center
                                            text-[11px] font-semibold leading-5 text-white
                                            shadow-2xl
                                            dark:border-emerald-300
                                            dark:bg-white dark:text-slate-900
                                            sm:px-4 sm:text-xs"
                                    >
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="flex h-5 w-5 shrink-0 items-center
                                                    justify-center rounded-full
                                                    bg-emerald-500 text-[10px] text-white"
                                            >
                                                1
                                            </span>

                                            <span>
                                                Yahan click karke apna pehla item add karein
                                            </span>
                                        </div>

                                        {{-- Proper nukila arrow --}}
                                        <span
                                            class="item-tooltip-arrow"
                                            aria-hidden="true"
                                        ></span>
                                    </div>
                                </div>
                            @endif

                            <a
                                href="{{ route('items.create') }}"
                                class="relative inline-flex h-10 items-center justify-center
                                    gap-1.5 rounded-xl bg-emerald-600 px-3
                                    text-xs font-bold text-white shadow-md
                                    transition hover:bg-emerald-700
                                    sm:h-11 sm:px-4 sm:text-sm
                                    {{ ($shouldShowItemSuggestion)
                                        ? 'item-suggestion-blink ring-4 ring-emerald-300/70 dark:ring-emerald-700/70'
                                        : ''
                                    }}"
                            >
                                @if($shouldShowItemSuggestion)
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
                                @endif

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
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>

                                Add Item
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Hidden by default on mobile; always visible from sm breakpoint --}}
                <div
                    id="itemFilterPanel"
                    class="mt-3 hidden rounded-xl border border-cyan-200 bg-white/80 p-3 dark:border-slate-600 dark:bg-slate-900/30 sm:block sm:border-0 sm:bg-transparent sm:p-0 dark:sm:bg-transparent"
                >
                    <form method="GET" class="grid grid-cols-1 gap-2.5 sm:grid-cols-4 lg:grid-cols-12">
                        <div class="sm:col-span-4 lg:col-span-4">
                            <label for="item-search" class="sr-only">Search items</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/>
                                </svg>

                                <input
                                    id="item-search"
                                    type="text"
                                    name="q"
                                    value="{{ $q }}"
                                    placeholder="Search name, SKU or description"
                                    class="h-11 w-full rounded-xl border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-cyan-600 focus:ring-2 focus:ring-cyan-600/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                                />
                            </div>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <label for="category-filter" class="sr-only">Category</label>
                            <select
                                id="category-filter"
                                name="category_id"
                                class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none transition focus:border-cyan-600 focus:ring-2 focus:ring-cyan-600/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            >
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected($category_id==$cat->id)>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-2">
                            <label for="status-filter" class="sr-only">Status</label>
                            <select
                                id="status-filter"
                                name="active"
                                class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none transition focus:border-cyan-600 focus:ring-2 focus:ring-cyan-600/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            >
                                <option value="">Any Status</option>
                                <option value="1" @selected($active==='1')>Active</option>
                                <option value="0" @selected($active==='0')>Inactive</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2 sm:col-span-4 lg:col-span-3">
                            <button
                                type="submit"
                                class="inline-flex h-11 items-center justify-center rounded-xl bg-slate-900 px-3 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-slate-600 dark:hover:bg-slate-500"
                            >
                                Apply Filter
                            </button>

                            <a
                                href="{{ route('items.index') }}"
                                class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            >
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Extra actions: desktop/tablet only --}}
            <div class="hidden flex-wrap gap-2 border-t border-cyan-200 bg-white/60 p-3 dark:border-slate-700 dark:bg-slate-900/20 sm:flex">
                <form
                    method="POST"
                    action="{{ route('items.barcodes.generate-missing') }}"
                    onsubmit="return confirm('Generate barcodes for all items that do not have a barcode?')"
                >
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                    >
                        Generate Barcodes
                    </button>
                </form>

                <a
                    href="{{ route('items.ai.create') }}"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700"
                >
                    AI Photo Entry
                </a>
            </div>
        </section>

        @php
            $itemGuideStorageKey =
                'item-guide-v1-user-'
                . auth()->id()
                . '-business-'
                . ($activeBusinessId ?? 'default');
        @endphp

        @if($shouldShowItemSuggestion)
            <div
                id="itemSuggestionGuide"
                data-storage-key="{{ $itemGuideStorageKey }}"
                class="relative hidden overflow-hidden rounded-2xl
                    border border-emerald-200
                    bg-gradient-to-br from-emerald-50
                    via-white to-cyan-50
                    p-4 shadow-sm
                    dark:border-emerald-900/70
                    dark:from-emerald-950/50
                    dark:via-neutral-900
                    dark:to-cyan-950/30
                    sm:block sm:p-6"
            >
                {{-- Decoration --}}
                <div
                    class="pointer-events-none absolute -right-16 -top-16
                        h-40 w-40 rounded-full
                        bg-emerald-200/40 blur-3xl
                        dark:bg-emerald-700/20"
                ></div>

                <div
                    class="pointer-events-none absolute -bottom-16 left-1/3
                        h-36 w-36 rounded-full
                        bg-cyan-200/40 blur-3xl
                        dark:bg-cyan-700/20"
                ></div>

                {{-- Close button --}}
                <button
                    type="button"
                    onclick="dismissItemGuide()"
                    aria-label="Close item guide"
                    title="Hide this guide"
                    class="absolute right-3 top-3 z-20
                        inline-flex h-9 w-9 items-center justify-center
                        rounded-full border border-gray-200
                        bg-white/90 text-gray-500 shadow-sm
                        transition hover:bg-white hover:text-red-600
                        dark:border-neutral-700
                        dark:bg-neutral-800
                        dark:text-neutral-300
                        dark:hover:text-red-400"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18 18 6M6 6l12 12"
                        />
                    </svg>
                </button>

                <div class="relative z-10 pr-8">
                    <div class="flex flex-col gap-4 sm:gap-5 lg:flex-row lg:items-start">

                        {{-- Icon --}}
                        <div
                            class="flex h-14 w-14 shrink-0 items-center
                                justify-center rounded-2xl
                                bg-emerald-600 text-white
                                shadow-lg shadow-emerald-600/20"
                        >
                            <svg
                                class="h-7 w-7"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M20 7 9 18l-5-5M9 7h11M4 7h.01"
                                />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2
                                    class="text-base font-bold leading-6 text-gray-900
                                        dark:text-white sm:text-xl"
                                >
                                    How to create and manage Items
                                </h2>

                                <span
                                    class="rounded-full bg-emerald-100
                                        px-2.5 py-1 text-[11px]
                                        font-bold uppercase tracking-wide
                                        text-emerald-700
                                        dark:bg-emerald-900/60
                                        dark:text-emerald-300"
                                >
                                    Quick Guide
                                </span>
                            </div>

                            <p
                                class="mt-2 max-w-3xl text-sm leading-6
                                    text-gray-600 dark:text-neutral-300"
                            >
                                Apne products ya services ko item master me add karein.
                                Inhi items ko invoice banate waqt search aur select kiya
                                ja sakta hai.
                            </p>

                            {{-- Steps --}}
                            <div class="mt-4 grid gap-3 md:mt-5 md:grid-cols-3">

                                <div
                                    class="rounded-xl border border-emerald-100
                                        bg-white/80 p-4 shadow-sm
                                        dark:border-emerald-900/50
                                        dark:bg-neutral-800/70"
                                >
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="flex h-8 w-8 shrink-0 items-center
                                                justify-center rounded-full
                                                bg-emerald-100 text-sm font-bold
                                                text-emerald-700
                                                dark:bg-emerald-900/70
                                                dark:text-emerald-300"
                                        >
                                            1
                                        </span>

                                        <div>
                                            <h3
                                                class="text-sm font-bold
                                                    text-gray-900 dark:text-white"
                                            >
                                                Create item
                                            </h3>

                                            <p
                                                class="mt-1 text-xs leading-5
                                                    text-gray-500
                                                    dark:text-neutral-400"
                                            >
                                                New Item button se product ya service
                                                ka naam, price aur tax add karein.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="rounded-xl border border-emerald-100
                                        bg-white/80 p-4 shadow-sm
                                        dark:border-emerald-900/50
                                        dark:bg-neutral-800/70"
                                >
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="flex h-8 w-8 shrink-0 items-center
                                                justify-center rounded-full
                                                bg-emerald-100 text-sm font-bold
                                                text-emerald-700
                                                dark:bg-emerald-900/70
                                                dark:text-emerald-300"
                                        >
                                            2
                                        </span>

                                        <div>
                                            <h3
                                                class="text-sm font-bold
                                                    text-gray-900 dark:text-white"
                                            >
                                                Add barcode
                                            </h3>

                                            <p
                                                class="mt-1 text-xs leading-5
                                                    text-gray-500
                                                    dark:text-neutral-400"
                                            >
                                                Barcode generate karke label print karein
                                                aur invoice me scanner use karein.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="rounded-xl border border-emerald-100
                                        bg-white/80 p-4 shadow-sm
                                        dark:border-emerald-900/50
                                        dark:bg-neutral-800/70"
                                >
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="flex h-8 w-8 shrink-0 items-center
                                                justify-center rounded-full
                                                bg-emerald-100 text-sm font-bold
                                                text-emerald-700
                                                dark:bg-emerald-900/70
                                                dark:text-emerald-300"
                                        >
                                            3
                                        </span>

                                        <div>
                                            <h3
                                                class="text-sm font-bold
                                                    text-gray-900 dark:text-white"
                                            >
                                                Use in invoice
                                            </h3>

                                            <p
                                                class="mt-1 text-xs leading-5
                                                    text-gray-500
                                                    dark:text-neutral-400"
                                            >
                                                Invoice create page par item search ya
                                                barcode scan karke quickly add karein.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tip --}}
                            <div
                                class="mt-4 rounded-xl border border-amber-200
                                    bg-amber-50 p-3 text-sm text-amber-800
                                    dark:border-amber-900/60
                                    dark:bg-amber-950/30
                                    dark:text-amber-300"
                            >
                                <strong>Helpful tip:</strong>
                                Item ka SKU aur barcode unique rakhein. Isse search,
                                billing aur stock management easy rahega.
                            </div>

                            <div
                                class="mt-5 flex flex-col gap-3
                                    sm:flex-row sm:items-center"
                            >
                                <a
                                    href="{{ route('items.create') }}"
                                    class="inline-flex items-center justify-center
                                        gap-2 rounded-xl bg-emerald-600
                                        px-4 py-2.5 text-sm font-semibold
                                        text-white shadow-sm transition
                                        hover:bg-emerald-700"
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
                                            d="M12 4v16m8-8H4"
                                        />
                                    </svg>

                                    Create First Item
                                </a>

                                <button
                                    type="button"
                                    onclick="dismissItemGuide()"
                                    class="inline-flex items-center justify-center
                                        rounded-xl border border-gray-300
                                        bg-white px-4 py-2.5
                                        text-sm font-semibold text-gray-700
                                        transition hover:bg-gray-50
                                        dark:border-neutral-600
                                        dark:bg-neutral-800
                                        dark:text-neutral-200
                                        dark:hover:bg-neutral-700"
                                >
                                    Got it, hide this guide
                                </button>

                                <span
                                    class="text-xs text-gray-500
                                        dark:text-neutral-400"
                                >
                                    Current items: {{ $currentItemCount }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reopen button --}}
            <div
                id="itemSuggestionReopen"
                class="hidden justify-end sm:flex"
            >
                <button
                    type="button"
                    onclick="showItemGuide()"
                    class="inline-flex items-center gap-2
                        rounded-xl border border-emerald-200
                        bg-emerald-50 px-3 py-2
                        text-xs font-semibold text-emerald-700
                        transition hover:bg-emerald-100
                        dark:border-emerald-900
                        dark:bg-emerald-950/40
                        dark:text-emerald-300"
                >
                    Show Item Guide
                </button>
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-neutral-900">
            <form
                id="barcodeBulkForm"
                method="POST"
                action="{{ route('items.barcodes.print') }}"
                target="_blank"
            >
                @csrf

                <div class="hidden border-b border-slate-200 bg-purple-50 p-4 dark:border-slate-700 dark:bg-slate-800 md:block">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white">
                                Barcode Label Printing
                            </div>
                            <div class="mt-0.5 text-xs text-slate-600 dark:text-slate-300">
                                Select items below and print their barcode labels.
                            </div>
                        </div>

                        <div class="grid grid-cols-[100px_1fr] gap-2 sm:flex sm:items-end">
                            <div>
                                <label for="barcode-quantity" class="mb-1 block text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                                    Copies
                                </label>
                                <input
                                    id="barcode-quantity"
                                    type="number"
                                    name="quantity"
                                    value="1"
                                    min="1"
                                    max="200"
                                    class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none focus:border-purple-600 focus:ring-2 focus:ring-purple-600/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white sm:w-24"
                                >
                            </div>

                            <button
                                type="submit"
                                class="mt-auto inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-purple-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-purple-700 sm:px-4 sm:text-sm"
                            >
                                🖨 Print Selected
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Clean mobile item cards: only important information --}}
            <div class="mobile-items-list divide-y divide-slate-200 dark:divide-slate-700 lg:hidden">
                @forelse ($items as $it)
                    <article class="p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h3 class="break-words text-sm font-bold text-slate-900 dark:text-white">
                                    {{ $it->name }}
                                </h3>

                                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                                    @if($it->sku)
                                        <span>SKU: {{ $it->sku }}</span>
                                    @endif

                                    @if($it->category?->name)
                                        <span>{{ $it->category->name }}</span>
                                    @endif
                                </div>
                            </div>

                            @if($it->is_active)
                                <span class="shrink-0 rounded-full bg-green-100 px-2 py-1 text-[10px] font-bold text-green-700">
                                    Active
                                </span>
                            @else
                                <span class="shrink-0 rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-600">
                                    Inactive
                                </span>
                            @endif
                        </div>

                        <div class="mt-3 flex items-end justify-between gap-3">
                            <div class="flex gap-5">
                                <div>
                                    <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                        Price
                                    </div>
                                    <div class="mt-0.5 text-base font-bold text-slate-900 dark:text-white">
                                        {{ number_format($it->price, 2) }}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                        Stock
                                    </div>
                                    <div class="mt-0.5 text-base font-bold text-slate-900 dark:text-white">
                                        {{ $it->stock_qty }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                <a
                                    href="{{ route('items.edit', $it->id) }}"
                                    class="inline-flex h-9 items-center justify-center rounded-lg bg-amber-500 px-3 text-xs font-bold text-white transition hover:bg-amber-600"
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
                                        class="inline-flex h-9 items-center justify-center rounded-lg bg-red-600 px-3 text-xs font-bold text-white transition hover:bg-red-700"
                                    >
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="p-8 text-center">
                        <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            No items found
                        </div>

                        <a
                            href="{{ route('items.create') }}"
                            class="mt-3 inline-flex rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white"
                        >
                            Create Item
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- Desktop table --}}
            <div class="desktop-items-table w-full overflow-x-auto">
                <table class="min-w-[1100px] w-full text-left text-sm text-slate-700 dark:text-slate-300">
                    <thead class="bg-[#DDF4F4] text-xs font-semibold uppercase tracking-wider dark:bg-[#354A54]">
                        <tr>
                            <th class="px-3 py-3">
                                <input
                                    type="checkbox"
                                    id="selectAllBarcodeItems"
                                    class="h-4 w-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500"
                                >
                            </th>
                            <th>Image</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">SKU</th>
                            <th class="px-4 py-3">Barcode</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Price</th>
                            <th class="px-4 py-3">Tax %</th>
                            <th class="px-4 py-3">Stock</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-neutral-900">
                        @forelse ($items as $it)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                <td class="px-3 py-3">
                                    <input
                                        class="barcode-item-checkbox h-4 w-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500"
                                        type="checkbox"
                                        form="barcodeBulkForm"
                                        name="item_ids[]"
                                        value="{{ $it->id }}"
                                    >
                                </td>
                                <td>
                                  @if($it->image)
                                    <img
                                        src="{{ asset('storage/' . $it->image) }}"
                                        alt="{{ $it->name }}"
                                        class="mb-3 h-24 w-24 rounded-xl border border-slate-200 object-cover shadow-sm dark:border-slate-700"
                                    >
                                  @else
                                    <div class="mb-3 flex h-24 w-24 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-xs text-slate-400 dark:border-slate-700 dark:bg-slate-800">
                                        No Image
                                    </div>
                                  @endif
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $it->name }}</td>
                                <td class="px-4 py-3">{{ $it->sku ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if($it->barcode)
                                        <div class="flex min-w-[170px] flex-col gap-2">
                                            <span class="font-mono text-xs">{{ $it->barcode }}</span>
                                            <a
                                                href="{{ route('items.barcode.print', [
                                                    'item' => $it->id,
                                                    'quantity' => 1,
                                                    'print' => 1
                                                ]) }}"
                                                target="_blank"
                                                class="inline-flex w-fit items-center rounded-lg bg-purple-600 px-3 py-2 text-xs font-semibold text-white hover:bg-purple-700"
                                            >
                                                🖨 Print
                                            </a>
                                        </div>
                                    @else
                                        <div class="flex min-w-[170px] flex-col gap-2">
                                            <span class="text-xs text-red-600">Not generated</span>
                                            <form action="{{ route('items.barcode.generate', $it->id) }}" method="POST">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700"
                                                >
                                                    Generate
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $it->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ number_format($it->price,2) }}</td>
                                <td class="px-4 py-3">{{ rtrim(rtrim(number_format($it->tax_rate,2), '0'), '.') }}</td>
                                <td class="px-4 py-3">{{ $it->stock_qty }}</td>
                                <td class="px-4 py-3">
                                    @if($it->is_active)
                                        <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700">Active</span>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex min-w-[150px] items-center gap-2">
                                        <a
                                            href="{{ route('items.edit', $it->id) }}"
                                            class="rounded-lg bg-amber-500 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-600"
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
                                                class="rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-10 text-center text-slate-500">
                                    No items found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>


    <style>
        @keyframes itemSuggestionBlink {
            0%,
            100% {
                transform: scale(1);
                box-shadow:
                    0 0 0 0 rgba(16, 185, 129, 0.65),
                    0 8px 18px rgba(16, 185, 129, 0.25);
            }

            50% {
                transform: scale(1.06);
                box-shadow:
                    0 0 0 10px rgba(16, 185, 129, 0),
                    0 12px 25px rgba(16, 185, 129, 0.45);
            }
        }

        .item-suggestion-blink {
            animation: itemSuggestionBlink 1.25s ease-in-out infinite;
            transform-origin: center;
        }

        @media (prefers-reduced-motion: reduce) {
            .item-suggestion-blink {
                animation: none;
            }
        }




        .item-tooltip-arrow {
            position: absolute;
            right: 32px;
            bottom: -16px;
            width: 0;
            height: 0;
            border-left: 11px solid transparent;
            border-right: 11px solid transparent;
            border-top: 17px solid #0f172a;
            z-index: 60;
            display: block;
        }

        .dark .item-tooltip-arrow {
            border-top-color: #ffffff;
        }

        /* Item list visibility fix:
           - Mobile/tablet: cards visible
           - Laptop/desktop (1024px+): table visible
           Explicit CSS prevents items disappearing because of breakpoint/class conflicts. */
        .desktop-items-table {
            display: none;
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .mobile-items-list {
            display: block;
        }

        @media (min-width: 1024px) {
            .desktop-items-table {
                display: block !important;
            }

            .mobile-items-list {
                display: none !important;
            }
        }

        .desktop-items-table table {
            width: 100%;
            min-width: 1100px;
            border-collapse: collapse;
        }

        .desktop-items-table th,
        .desktop-items-table td {
            white-space: nowrap;
            vertical-align: middle;
        }

        .desktop-items-table td:nth-child(2) {
            min-width: 190px;
            white-space: normal;
            word-break: break-word;
        }

    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const mobileFilterToggle = document.getElementById('mobileFilterToggle');
        const itemFilterPanel = document.getElementById('itemFilterPanel');

        if (mobileFilterToggle && itemFilterPanel) {
            mobileFilterToggle.addEventListener('click', function () {
                const isHidden = itemFilterPanel.classList.contains('hidden');

                itemFilterPanel.classList.toggle('hidden');
                mobileFilterToggle.setAttribute(
                    'aria-expanded',
                    isHidden ? 'true' : 'false'
                );
            });
        }

        const selectAll = document.getElementById('selectAllBarcodeItems');

        if (selectAll) {
            selectAll.addEventListener('change', function () {
            document
                .querySelectorAll('.barcode-item-checkbox')
                .forEach(function (checkbox) {
                    checkbox.checked = selectAll.checked;
                });
            });
        }

        const bulkForm = document.getElementById('barcodeBulkForm');

        bulkForm?.addEventListener('submit', function (event) {
            const selectedItems = document.querySelectorAll(
                '.barcode-item-checkbox:checked'
            );

            if (selectedItems.length === 0) {
                event.preventDefault();
                alert('Please select at least one item.');
            }
        });
    });
</script>



@if($shouldShowItemSuggestion)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        initializeItemGuide();
    });

    function getItemGuideElements() {
        return {
            guide: document.getElementById('itemSuggestionGuide'),
            reopen: document.getElementById('itemSuggestionReopen')
        };
    }

    function initializeItemGuide() {
        const elements = getItemGuideElements();

        if (!elements.guide) {
            return;
        }

        const storageKey = elements.guide.dataset.storageKey;
        const dismissed = storageKey
            ? localStorage.getItem(storageKey) === '1'
            : false;

        if (dismissed) {
            elements.guide.classList.add('hidden');

            if (elements.reopen) {
                elements.reopen.classList.remove('hidden');
                elements.reopen.classList.add('flex');
            }

            return;
        }

        elements.guide.classList.remove('hidden');

        if (elements.reopen) {
            elements.reopen.classList.add('hidden');
            elements.reopen.classList.remove('flex');
        }
    }

    function dismissItemGuide() {
        const elements = getItemGuideElements();

        if (!elements.guide) {
            return;
        }

        const storageKey = elements.guide.dataset.storageKey;

        if (storageKey) {
            localStorage.setItem(storageKey, '1');
        }

        elements.guide.classList.add('hidden');

        if (elements.reopen) {
            elements.reopen.classList.remove('hidden');
            elements.reopen.classList.add('flex');
        }
    }

    function showItemGuide() {
        const elements = getItemGuideElements();

        if (!elements.guide) {
            return;
        }

        const storageKey = elements.guide.dataset.storageKey;

        if (storageKey) {
            localStorage.removeItem(storageKey);
        }

        elements.guide.classList.remove('hidden');

        if (elements.reopen) {
            elements.reopen.classList.add('hidden');
            elements.reopen.classList.remove('flex');
        }

        elements.guide.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
</script>
@endif
</x-layouts.app>