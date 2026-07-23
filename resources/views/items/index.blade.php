<x-layouts.app :title="__('Items')">
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

        <div class="flex flex-wrap items-center justify-between gap-3  bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <h1 class="text-2xl font-bold text-black dark:text-white">Items</h1>

            <div class="flex items-center gap-2">
                <form method="GET" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="q" value="{{ $q }}"
                           placeholder="Search name / SKU / desc..."
                           class="border border-black dark:border-white rounded px-3 py-2 text-sm w-56" />

                    <select name="category_id" class="border border-black dark:border-white rounded px-2 py-2 text-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($category_id==$cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <select name="active" class="border border-black dark:border-white rounded px-2 py-2 text-sm ">
                        <option value="">Any Status</option>
                        <option value="1" @selected($active==='1')>Active</option>
                        <option value="0" @selected($active==='0')>Inactive</option>
                    </select>

                    <button class="px-3 py-2 text-sm rounded dark:bg-gray-500 dark:hover:bg-gray-400 border border-black dark:border-white">Filter</button>
                    @if($q!=='' || $category_id || $active!=='')
                        <a href="{{ route('items.index') }}" class="text-sm text-gray-900 dark:text-white border border-black dark:border-white p-2 hover:underline">Clear</a>
                    @endif
                </form>

                <a href="{{ route('item.create') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                    + New Item
                </a>

                <form
                    method="POST"
                    action="{{ route('items.barcodes.generate-missing') }}"
                    onsubmit="return confirm('Generate barcodes for all items that do not have a barcode?')"
                >
                    @csrf

                    <button
                        type="submit"
                        class="rounded bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700"
                    >
                        Generate Missing Barcodes
                    </button>
                </form>


                <a href="{{ route('items.ai.create') }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                    📷 AI Photo Entry
                </a>
            </div>
        </div>

        @php
            $itemGuideStorageKey =
                'item-guide-v1-user-'
                . auth()->id()
                . '-business-'
                . ($activeBusinessId ?? 'default');
        @endphp

        @if($showItemSuggestion ?? false)
            <div
                id="itemSuggestionGuide"
                data-storage-key="{{ $itemGuideStorageKey }}"
                class="relative overflow-hidden rounded-2xl
                    border border-emerald-200
                    bg-gradient-to-br from-emerald-50
                    via-white to-cyan-50
                    p-5 shadow-sm
                    dark:border-emerald-900/70
                    dark:from-emerald-950/50
                    dark:via-neutral-900
                    dark:to-cyan-950/30
                    sm:p-6"
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
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start">

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
                                    class="text-lg font-bold text-gray-900
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
                            <div class="mt-5 grid gap-3 md:grid-cols-3">

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
                                    href="{{ route('item.create') }}"
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
                                    Current items: {{ $currentItemCount ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reopen button --}}
            <div
                id="itemSuggestionReopen"
                class="hidden justify-end"
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

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <form
                id="barcodeBulkForm"
                method="POST"
                action="{{ route('items.barcodes.print') }}"
                target="_blank"
            >
                @csrf

                <div class="flex flex-wrap items-center gap-3 border-b border-gray-200 bg-purple-50 p-4 dark:border-gray-700 dark:bg-gray-800">

                    <div>
                        <div class="text-sm font-bold text-gray-900 dark:text-white">
                            Barcode Label Printing
                        </div>

                        <div class="text-xs text-gray-600 dark:text-gray-300">
                            Items select karein aur print button dabayein
                        </div>
                    </div>

                    <label class="ml-auto text-sm font-medium text-gray-900 dark:text-white">
                        Copies per item
                    </label>

                    <input
                        type="number"
                        name="quantity"
                        value="1"
                        min="1"
                        max="200"
                        class="w-24 rounded border border-gray-400 bg-white px-3 py-2 text-gray-900"
                    >

                    <button
                        type="submit"
                        class="rounded-md bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-purple-700"
                    >
                        🖨 Print Selected Barcodes
                    </button>

                </div>
            </form>
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium tracking-wider">
                <tr>
                    <th class="px-3 py-3">
                        <input
                            type="checkbox"
                            id="selectAllBarcodeItems"
                        >
                    </th>

                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">SKU</th>
                    <th class="px-6 py-3">Barcode</th>
                    <th class="px-6 py-3">Category</th>
                    <th class="px-6 py-3">Price</th>
                    <th class="px-6 py-3">Tax %</th>
                    <th class="px-6 py-3">Stock</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                @forelse ($items as $it)
                    <tr>
                        <td class="px-3 py-3">
                            <input
                                class="barcode-item-checkbox"
                                type="checkbox"
                                form="barcodeBulkForm"
                                name="item_ids[]"
                                value="{{ $it->id }}"
                            >
                        </td>
                        <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">{{ $it->name }}</td>
                        <td class="px-6 py-3">{{ $it->sku ?? '—' }}</td>
                        <td class="px-6 py-3">
                            @if($it->barcode)

                                <div class="flex min-w-[170px] flex-col gap-2">
                                    <span class="font-mono text-xs text-gray-700 dark:text-gray-300">
                                        {{ $it->barcode }}
                                    </span>

                                    <a
                                        href="{{ route('items.barcode.print', [
                                            'item' => $it->id,
                                            'quantity' => 1,
                                            'print' => 1
                                        ]) }}"
                                        target="_blank"
                                        class="inline-flex w-fit items-center rounded-md bg-purple-600 px-3 py-2 text-xs font-semibold text-white hover:bg-purple-700"
                                    >
                                        🖨 Print Barcode
                                    </a>
                                </div>

                            @else

                                <div class="flex min-w-[170px] flex-col gap-2">
                                    <span class="text-xs text-red-600">
                                        Barcode not generated
                                    </span>

                                    <form
                                        action="{{ route('items.barcode.generate', $it->id) }}"
                                        method="POST"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="rounded-md bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700"
                                        >
                                            Generate Barcode
                                        </button>
                                    </form>
                                </div>

                            @endif
                        </td>
                        <td class="px-6 py-3">{{ $it->category?->name ?? '—' }}</td>
                        <td class="px-6 py-3">{{ number_format($it->price,2) }}</td>
                        <td class="px-6 py-3">{{ rtrim(rtrim(number_format($it->tax_rate,2), '0'), '.') }}</td>
                        <td class="px-6 py-3">{{ $it->stock_qty }}</td>
                        <td class="px-6 py-3">
                            @if($it->is_active)
                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex min-w-[150px] items-center gap-2">

                                <a
                                    href="{{ route('items.edit', $it->id) }}"
                                    class="rounded bg-yellow-600 px-3 py-2 text-xs font-semibold text-white hover:bg-yellow-700"
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
                                        class="rounded bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700"
                                    >
                                        Delete
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">No items found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>



    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAllBarcodeItems');

        if (!selectAll) {
            return;
        }

        selectAll.addEventListener('change', function () {
            document
                .querySelectorAll('.barcode-item-checkbox')
                .forEach(function (checkbox) {
                    checkbox.checked = selectAll.checked;
                });
        });

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



@if($showItemSuggestion ?? false)
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
