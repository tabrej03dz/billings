<x-layouts.app :title="__('Inventory Summary')">
    <div class="flex flex-col gap-4">

        {{-- Success flash (agar kabhi use ho) --}}
        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- PAGE HEADER + FILTERS --}}
        <div class="flex flex-wrap items-center justify-between gap-3 bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Inventory Summary
            </h1>

            <form method="GET" class="flex flex-wrap items-center gap-2">
                {{-- From Date --}}
                <input type="date" name="from" value="{{ request('from') }}"
                       class="border rounded px-3 py-2 text-sm w-40 dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100" />

                {{-- To Date --}}
                <input type="date" name="to" value="{{ request('to') }}"
                       class="border rounded px-3 py-2 text-sm w-40 dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100" />

                {{-- (Optional) Search field --}}
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Search name / SKU..."
                       class="border rounded px-3 py-2 text-sm w-56 dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100" />

                <button class="px-3 py-2 text-sm rounded bg-gray-100 hover:bg-gray-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 dark:text-gray-100">
                    Filter
                </button>

                @if(request('from') || request('to') || request('q'))
                    <a href="{{ route('inventory.summary') }}"
                       class="text-sm text-gray-600 hover:underline dark:text-gray-300">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- SMALL INFO ROW --}}
        <div class="text-xs text-gray-600 dark:text-gray-300">
            @php
                $totalItems = $items->count();
                $totalQty   = $items->sum(fn($i) => (int) ($i->stock_qty ?? 0));
                $totalValue = $items->sum(function ($i) {
                    $qty  = (int) ($i->stock_qty ?? 0);
                    $rate = (float) ($i->cost_price ?? $i->price ?? 0);
                    return $qty * $rate;
                });
            @endphp

            <div class="flex flex-wrap items-center gap-3">
                <span>
                    <span class="font-semibold">{{ number_format($totalItems) }}</span> items
                </span>
                <span class="hidden sm:inline h-4 w-px bg-gray-300 dark:bg-neutral-700"></span>
                <span>
                    Total stock: <span class="font-semibold">{{ number_format($totalQty) }}</span>
                </span>
                <span class="hidden sm:inline h-4 w-px bg-gray-300 dark:bg-neutral-700"></span>
                <span>
                    Valuation: <span class="font-semibold">₹ {{ number_format($totalValue, 2) }}</span>
                </span>

                @if(request('from') || request('to'))
                    <span class="hidden sm:inline h-4 w-px bg-gray-300 dark:bg-neutral-700"></span>
                    <span>
                        Range:
                        @if(request('from')) <span class="font-semibold">{{ request('from') }}</span> @endif
                        @if(request('from') && request('to')) – @endif
                        @if(request('to')) <span class="font-semibold">{{ request('to') }}</span> @endif
                    </span>
                @endif
            </div>
        </div>

        {{-- TABLE --}}
        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-[#BFE0E0] dark:bg-[#354A54]  text-xs uppercase font-medium tracking-wider">
                <tr>
                    <th class="px-6 py-3">Item</th>
                    <th class="px-6 py-3">SKU</th>
                    <th class="px-6 py-3">Category</th>
                    <th class="px-6 py-3 text-right">Stock Qty</th>
                    <th class="px-6 py-3 text-right">Gross Wt</th>
                    <th class="px-6 py-3 text-right">Metal Wt</th>
                    <th class="px-6 py-3 text-right">Stone Wt</th>
                    <th class="px-6 py-3 text-right">Valuation</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                @forelse ($items as $item)
                    @php
                        $stockQty  = (int) ($item->stock_qty ?? 0);
                        $rate      = (float) ($item->cost_price ?? $item->price ?? 0);
                        $valuation = $stockQty * $rate;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                        <td class="px-6 py-3">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ $item->name }}
                                </span>
                                @if($stockQty <= 0)
                                    <span class="mt-1 inline-flex w-fit px-2 py-0.5 text-[11px] rounded bg-red-100 text-red-700">
                                        Out of stock
                                    </span>
                                @elseif($stockQty <= 2)
                                    <span class="mt-1 inline-flex w-fit px-2 py-0.5 text-[11px] rounded bg-amber-100 text-amber-700">
                                        Low stock
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-3">
                            {{ $item->sku ?? '—' }}
                        </td>
                        <td class="px-6 py-3">
                            {{ $item->category?->name ?? '—' }}
                        </td>
                        <td class="px-6 py-3 text-right font-medium">
                            {{ $stockQty }}
                        </td>
                        <td class="px-6 py-3 text-right">
                            {{ number_format($item->total_gross_weight ?? 0, 3) }}
                        </td>
                        <td class="px-6 py-3 text-right">
                            {{ number_format($item->total_metal_weight ?? 0, 3) }}
                        </td>
                        <td class="px-6 py-3 text-right">
                            {{ number_format($item->total_stone_weight ?? 0, 3) }}
                        </td>
                        <td class="px-6 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-400">
                            ₹ {{ number_format($valuation, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No items found for this filter.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Agar baad me paginate karoge to yaha links() laga sakte ho --}}
        {{-- <div class="mt-4">
            {{ $items->links() }}
        </div> --}}
    </div>
</x-layouts.app>
