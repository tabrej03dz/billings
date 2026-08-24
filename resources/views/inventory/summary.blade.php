<x-layouts.app :title="__('Inventory Summary')">

    @php
        $formatQuantity = static function ($quantity) {
            $formatted = number_format((float) $quantity, 3, '.', '');
            return rtrim(rtrim($formatted, '0'), '.');
        };

        $totalItems = (int) ($stats->total_items ?? 0);
        $totalStock = (float) ($stats->total_stock ?? 0);
        $totalValue = (float) ($stats->total_value ?? 0);
    @endphp

    <div class="flex flex-col gap-5">

        {{-- Flash message --}}
        @if(session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header --}}
        <div class="rounded-2xl bg-[#BFE0E0] p-5 dark:bg-[#354A54]">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Inventory Summary
                    </h1>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        View item-wise opening, inward, outward and closing stock.
                    </p>
                </div>

                <form method="GET"
                      action="{{ route('inventory.summary') }}"
                      class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:w-auto lg:grid-cols-4">

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            From date
                        </label>

                        <input
                            type="date"
                            name="from"
                            value="{{ request('from') }}"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm
                                   focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200
                                   dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            To date
                        </label>

                        <input
                            type="date"
                            name="to"
                            value="{{ request('to') }}"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm
                                   focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200
                                   dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                        >
                    </div>

                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Search
                        </label>

                        <input
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Item, SKU or category"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm
                                   focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200
                                   dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                        >
                    </div>

                    <div class="flex items-end gap-2">
                        <button
                            type="submit"
                            class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white
                                   hover:bg-gray-800 dark:bg-teal-600 dark:hover:bg-teal-500"
                        >
                            Apply
                        </button>

                        @if(request()->hasAny(['from', 'to', 'q']))
                            <a
                                href="{{ route('inventory.summary') }}"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium
                                       text-gray-700 hover:bg-gray-50 dark:border-neutral-600
                                       dark:bg-neutral-900 dark:text-gray-200"
                            >
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Items
                </p>

                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($totalItems) }}
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Closing Stock
                </p>

                <p class="mt-2 text-2xl font-bold text-blue-700 dark:text-blue-400">
                    {{ $formatQuantity($totalStock) }}
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Stock Valuation
                </p>

                <p class="mt-2 text-2xl font-bold text-emerald-700 dark:text-emerald-400">
                    ₹ {{ number_format($totalValue, 2) }}
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Report Period
                </p>

                <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                    @if(request('from') && request('to'))
                        {{ \Carbon\Carbon::parse(request('from'))->format('d M Y') }}
                        –
                        {{ \Carbon\Carbon::parse(request('to'))->format('d M Y') }}
                    @elseif(request('from'))
                        {{ \Carbon\Carbon::parse(request('from'))->format('d M Y') }}
                        – Today
                    @elseif(request('to'))
                        Beginning –
                        {{ \Carbon\Carbon::parse(request('to'))->format('d M Y') }}
                    @else
                        Beginning – Today
                    @endif
                </p>
            </div>
        </div>

        {{-- Inventory table --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

            <div class="border-b border-gray-200 px-5 py-4 dark:border-neutral-700">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="font-semibold text-gray-900 dark:text-white">
                            Item-wise Stock
                        </h2>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Showing {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }}
                            of {{ number_format($items->total()) }} items
                        </p>
                    </div>

                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Valuation is calculated using cost price.
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">

                    <thead class="bg-[#BFE0E0] text-xs uppercase text-gray-700 dark:bg-[#354A54] dark:text-gray-200">
                        <tr>
                            <th class="whitespace-nowrap px-5 py-4">Item</th>
                            <th class="whitespace-nowrap px-5 py-4">SKU</th>
                            <th class="whitespace-nowrap px-5 py-4">Category</th>
                            <th class="whitespace-nowrap px-5 py-4">Unit</th>
                            <th class="whitespace-nowrap px-5 py-4 text-right">Opening</th>
                            <th class="whitespace-nowrap px-5 py-4 text-right">Stock In</th>
                            <th class="whitespace-nowrap px-5 py-4 text-right">Stock Out</th>
                            <th class="whitespace-nowrap px-5 py-4 text-right">Closing</th>
                            <th class="whitespace-nowrap px-5 py-4 text-right">Cost Price</th>
                            <th class="whitespace-nowrap px-5 py-4 text-right">Valuation</th>
                            <th class="whitespace-nowrap px-5 py-4 text-center">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">

                        @forelse($items as $item)
                            @php
                                $openingStock = (float) ($item->opening_stock ?? 0);
                                $stockIn      = (float) ($item->stock_in_qty ?? 0);
                                $stockOut     = abs((float) ($item->stock_out_qty ?? 0));
                                $closingStock = (float) ($item->closing_stock ?? 0);

                                $costPrice = (float) ($item->cost_price ?? $item->price ?? 0);
                                $valuation = $closingStock * $costPrice;

                                $unit = $item->unit
                                    ?? $item->unit_name
                                    ?? 'pcs';

                                $reorderLevel = (float) ($item->reorder_level ?? 5);
                            @endphp

                            <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800">

                                <td class="px-5 py-4">
                                    <div class="min-w-[180px]">
                                        <p class="font-semibold text-gray-900 dark:text-white">
                                            {{ $item->name }}
                                        </p>

                                        @if(!empty($item->description))
                                            <p class="mt-1 max-w-xs truncate text-xs text-gray-500 dark:text-gray-400">
                                                {{ $item->description }}
                                            </p>
                                        @endif
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-gray-600 dark:text-gray-300">
                                    {{ $item->sku ?: '—' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-gray-600 dark:text-gray-300">
                                    {{ $item->category?->name ?? 'Uncategorized' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-gray-600 dark:text-gray-300">
                                    {{ strtoupper($unit) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right font-medium">
                                    {{ $formatQuantity($openingStock) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-emerald-700 dark:text-emerald-400">
                                    +{{ $formatQuantity($stockIn) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-red-600 dark:text-red-400">
                                    -{{ $formatQuantity($stockOut) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right font-bold text-blue-700 dark:text-blue-400">
                                    {{ $formatQuantity($closingStock) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    ₹ {{ number_format($costPrice, 2) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right font-bold text-gray-900 dark:text-white">
                                    ₹ {{ number_format($valuation, 2) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-center">
                                    @if($closingStock <= 0)
                                        <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                            Out of stock
                                        </span>
                                    @elseif($closingStock <= $reorderLevel)
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                            Low stock
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                            In stock
                                        </span>
                                    @endif
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="11" class="px-5 py-12 text-center">
                                    <div class="text-gray-500 dark:text-gray-400">
                                        <p class="font-semibold">No inventory items found</p>
                                        <p class="mt-1 text-sm">
                                            Try changing the search or date filter.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                    @if($items->count())
                        <tfoot class="bg-gray-50 font-semibold dark:bg-neutral-800">
                            <tr>
                                <td colspan="7" class="px-5 py-4 text-right text-gray-700 dark:text-gray-200">
                                    Complete Inventory Total
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right text-blue-700 dark:text-blue-400">
                                    {{ $formatQuantity($totalStock) }}
                                </td>

                                <td class="px-5 py-4"></td>

                                <td class="whitespace-nowrap px-5 py-4 text-right text-emerald-700 dark:text-emerald-400">
                                    ₹ {{ number_format($totalValue, 2) }}
                                </td>

                                <td class="px-5 py-4"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            @if($items->hasPages())
                <div class="border-t border-gray-200 px-5 py-4 dark:border-neutral-700">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>