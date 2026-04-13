<x-layouts.app :title="__('Dashboard')">
    <div x-data="{ showRatesForm: false }" class="flex flex-col gap-4">

        {{-- FLASH MESSAGE --}}
        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-3 rounded-lg bg-red-50 text-red-700 border border-red-200 text-sm">
                <ul class="list-disc ml-4">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- PAGE HEADER --}}
        {{-- <div class="flex flex-wrap bg-[#BFE0E0] dark:bg-[#354A54] items-center justify-between gap-3 p-6">
            <h1 class="text-2xl font-bold text-black dark:text-white">
                Dashboard
            </h1>

            <div class="text-sm text-gray-500 dark:text-gray-50 p-3 border-2 border-[#FCB055]">
                Today:
                <span class="font-semibold text-grey-900">
                    {{ isset($today) ? $today->format('d M Y') : now()->format('d M Y') }}
                </span>
            </div>
        </div> --}}

        {{-- PAGE HEADER --}}
        <div class="flex flex-wrap bg-[#BFE0E0] dark:bg-[#354A54] items-center justify-between gap-3 p-6">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-black dark:text-white">
                    Dashboard
                </h1>

                {{-- Notification Icon --}}
                <a href="{{ route('bill-requests.index') }}"
                class="relative inline-flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-neutral-700 transition"
                title="Bill Requests">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.857 17H19l-1.405-1.405A2.032 2.032 0 0117 14.158V11a5.002 5.002 0 00-4-4.9V5a1 1 0 10-2 0v1.1A5.002 5.002 0 007 11v3.159c0 .538-.214 1.055-.595 1.436L5 17h4.143m5.714 0a3 3 0 11-5.714 0m5.714 0H9.143" />
                    </svg>

                    {{-- Optional red dot --}}
                    <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full"></span>
                </a>
            </div>

            <div class="text-sm text-gray-500 dark:text-gray-50 p-3 border-2 border-[#FCB055]">
                Today:
                <span class="font-semibold text-grey-900">
                    {{ isset($today) ? $today->format('d M Y') : now()->format('d M Y') }}
                </span>
            </div>
        </div>

        {{-- FILTER BAR (Month / From-To) --}}
        <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-xl p-4">
            <form method="GET" action="{{ url()->current() }}" class="flex flex-col lg:flex-row gap-3 lg:items-end">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full">
                    {{-- Preset --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Preset</label>
                        <select name="preset"
                                class="w-full border rounded px-2 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100">
                            <option value="">Custom</option>
                            <option value="today" {{ ($preset ?? '') === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="7d" {{ ($preset ?? '') === '7d' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="month" {{ ($preset ?? '') === 'month' ? 'selected' : '' }}>This Month</option>
                        </select>
                    </div>

                    {{-- From --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">From</label>
                        <input type="date" name="from"
                               value="{{ isset($from) ? \Carbon\Carbon::parse($from)->toDateString() : request('from') }}"
                               class="w-full border rounded px-2 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100">
                    </div>

                    {{-- To --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">To</label>
                        <input type="date" name="to"
                               value="{{ isset($to) ? \Carbon\Carbon::parse($to)->toDateString() : request('to') }}"
                               class="w-full border rounded px-2 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100">
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 rounded text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700">
                        Apply
                    </button>

                    <a href="{{ url()->current() }}"
                       class="px-4 py-2 rounded text-sm font-semibold border border-gray-300 dark:border-neutral-700
                      text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-neutral-800">
                        Reset
                    </a>
                </div>
            </form>

            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Showing data from
                <span class="font-semibold">{{ isset($from) ? \Carbon\Carbon::parse($from)->format('d M Y') : '—' }}</span>
                to
                <span class="font-semibold">{{ isset($to) ? \Carbon\Carbon::parse($to)->format('d M Y') : '—' }}</span>
            </div>
        </div>


        {{-- TOP SUMMARY CARDS: SMALL COMPACT BOXES --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

            {{-- Today Sales --}}
            <div class="bg-[#33D39A] dark:bg-[#E6F7F1] rounded-lg border border-grey-900 dark:border p-3 shadow-sm">
                <p class="text-[10px] font-semibold text-gray-50 dark:text-gray-500 uppercase tracking-wider">
                    Today Sales
                </p>
                <p class="mt-1 text-lg font-bold text-gray-50 dark:text-black leading-none">
                    ₹ {{ number_format($todaySalesAmount ?? 0, 2) }}
                </p>
                <p class="mt-1 text-[11px] text-[#F5F5F5] dark:text-gray-500">
                    {{ $todaySalesCount ?? 0 }} invoice(s)
                </p>
            </div>

            {{-- Month Sales --}}
            <div class="bg-[#37BEF7] dark:bg-[#658282] rounded-lg border border-grey-200 dark:border  p-3 shadow-sm">
                <p class="text-[10px] font-semibold text-gray-50 dark:text-gray-50 uppercase tracking-wider">
                    This Month Sales
                </p>
                <p class="mt-1 text-lg font-bold text-gray-50 dark:text-white leading-none">
                    ₹ {{ number_format($monthSalesAmount ?? 0, 2) }}
                </p>
                <p class="mt-1 text-[11px] text-[#E0F3FF] dark:text-gray-50">
                    Total from 1st of month
                </p>
            </div>

            {{-- Month Purchases --}}
            <div class="bg-[#00bcac] dark:bg-[#354a54] rounded-lg border border-grey-200 dark:border  p-3 shadow-sm">
                <p class="text-[10px] font-semibold text-gray-50 dark:text-gray-400 uppercase tracking-wider">
                    This Month Purchases
                </p>
                <p class="mt-1 text-lg font-bold text-gray-50 dark:text-white leading-none">
                    ₹ {{ number_format($monthPurchasesAmount ?? 0, 2) }}
                </p>
                <p class="mt-1 text-[11px] text-gray-50 dark:text-gray-400">
                    Today: ₹ {{ number_format($todayPurchasesAmount ?? 0, 2) }}
                </p>
            </div>

            {{-- Pending Amount --}}
            <div class="bg-[#bc5b6a] dark:bg-[#3C3433] rounded-lg border border-grey-200 dark:border p-3 shadow-sm">
                <p class="text-[10px] font-semibold text-red-50 dark:text-red-50 uppercase tracking-wider">
                    Pending Amount
                </p>
                <p class="mt-1 text-lg font-bold text-red-50 dark:text-red-50 leading-none">
                    ₹ {{ number_format($totalPendingAmount ?? 0, 2) }}
                </p>
                <p class="mt-1 text-[11px] text-gray-50 dark:text-gray-50">
                    Today: ₹ {{ number_format($todayPendingAmount ?? 0, 2) }}
                </p>
            </div>
            {{-- Items / Stock --}}
            <div class="bg-[#ffd055] dark:bg-[#3C3433] rounded-lg border border-grey-200 dark:border  p-3 shadow-sm">
                <p class="text-[10px] font-semibold text-gray-50 dark:text-gray-50 uppercase tracking-wider">
                    Items / Stock
                </p>
                <p class="mt-1 text-lg font-bold text-gray-50 dark:text-white leading-none">
                    {{ number_format($totalItems ?? 0) }} items
                </p>
                <p class="mt-1 text-[11px] text-gray-50 dark:text-gray-50">
                    Stock: {{ number_format($totalStockQty ?? 0) }} •
                    Low:
                    <span class="font-semibold text-red-50 dark:text-[#E5533D] p-2">
                {{ $lowStockCount ?? 0 }}
            </span>
                </p>
            </div>




        </div>


        {{-- SECOND ROW: TOTALS + RATES + FORM BUTTON --}}
        <div class="flex flex-wrap gap-4">

            {{-- Lifetime Sales --}}
            <div class="w-full sm:w-1/2 xl:w-1/3">
                <div class="bg-green-600 dark:bg-[#54696C] rounded-xl border border-grey-200 dark:border  shadow-sm px-4 py-3 h-full">
                    <p class="text-xs font-medium text-gray-50 dark:text-gray-50 uppercase tracking-wide">
                        Total Sales (All Time)
                    </p>
                    <p class="mt-2 text-xl font-bold text-gray-50 dark:text-white">
                        ₹ {{ number_format($totalSalesAmount ?? 0, 2) }}
                    </p>
                </div>
            </div>

            {{-- Lifetime Purchases --}}
            <div class="w-full sm:w-1/2 xl:w-1/3">
                <div class="bg-[#1E90FF] dark:bg-[#E6F7F1] rounded-xl border border-grey-900 dark:border  shadow-sm px-4 py-3 h-full">
                    <p class="text-xs font-medium text-gray-50 dark:text-gray-500 uppercase tracking-wide">
                        Total Purchases (All Time)
                    </p>
                    <p class="mt-2 text-xl font-bold text-gray-50 dark:text-black">
                        ₹ {{ number_format($totalPurchasesAmount ?? 0, 2) }}
                    </p>
                </div>
            </div>

            @if($business->type == 'jewellery')
            {{-- Today Metal Rates + button --}}
            <div class="w-full xl:w-1/3">
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-red-200 dark:border p-3 dark:border-neutral-700 shadow-sm px-4 py-3 h-full">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            Today Metal Rates
                        </p>

                        <button type="button"
                                @click="showRatesForm = !showRatesForm"
                                class="inline-flex items-center px-3 py-1.5 rounded text-xs font-medium
                                       bg-indigo-600 text-white hover:bg-indigo-700">
                            Set Metal Rates
                        </button>
                    </div>

                    {{-- Existing rates list --}}
                    @if(isset($todayMetalRates) && $todayMetalRates->count())
                        <div class="mt-1 space-y-1 text-xs text-gray-700 dark:text-gray-200">
                            @foreach($todayMetalRates as $rate)
                                <div class="flex items-center justify-between">
                                    <span>
                                        {{ strtoupper($rate->metal_type) }}
                                        @if($rate->purity)
                                            - {{ $rate->purity }}
                                        @endif
                                    </span>
                                    <span class="font-semibold">
                                        ₹ {{ number_format($rate->rate_per_gram ?? 0, 2) }}/gm
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            No active rates set for today.
                        </p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- METAL RATES FORM (toggle) --}}
        <div x-show="showRatesForm"
             x-transition
             class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm px-4 py-4">

            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                    Set Today Metal Rates (per gram)
                </h2>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Date: {{ isset($today) ? $today->format('d M Y') : now()->format('d M Y') }}
                </span>
            </div>

            <form method="POST" action="{{ route('metal-rates.store-today') }}" class="space-y-4">
                @csrf

                {{-- GOLD RATES --}}
                <div>
                    <h3 class="text-xs font-semibold text-amber-700 dark:text-amber-300 uppercase mb-2">
                        Gold Rates (₹/gm)
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        @foreach($goldPurities as $purity)
                            @php
                                $key = 'gold|'.$purity;
                                $value = $rateMap[$key] ?? '';
                            @endphp
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    {{ $purity }}
                                </label>
                                <input type="number" step="0.01" min="0"
                                       name="rates[gold][{{ $purity }}]"
                                       value="{{ old('rates.gold.'.$purity, $value) }}"
                                       class="w-full border rounded px-2 py-1.5 text-sm
                                              dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100" />
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ADD MORE GOLD PURITIES --}}
                <div x-data="{ goldRows: [] }" class="mt-3">

                    <h4 class="text-xs font-semibold text-amber-700 dark:text-amber-300 mb-1">
                        Add Custom Gold Purity
                    </h4>

                    <template x-for="(row, index) in goldRows" :key="index">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-2">

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Custom Purity
                                </label>
                                <input type="text"
                                       x-model="row.purity"
                                       placeholder="e.g. 23K"
                                       name="custom[gold][purity][]"
                                       class="w-full border rounded px-2 py-1.5 text-sm
                              dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Rate (₹/gm)
                                </label>
                                <input type="number" step="0.01" min="0"
                                       x-model="row.rate"
                                       name="custom[gold][rate][]"
                                       class="w-full border rounded px-2 py-1.5 text-sm
                              dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100">
                            </div>

                            <button type="button"
                                    @click="goldRows.splice(index, 1)"
                                    class="self-end text-xs bg-red-500 text-white px-2 py-1 rounded">
                                Remove
                            </button>
                        </div>
                    </template>

                    <button type="button"
                            @click="goldRows.push({ purity:'', rate:'' })"
                            class="mt-1 px-3 py-1.5 rounded text-xs font-medium
                   bg-amber-600 text-white hover:bg-amber-700">
                        + Add More Gold Purity
                    </button>
                </div>


                {{-- SILVER RATES --}}
                <div>
                    <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-2">
                        Silver Rates (₹/gm)
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        @foreach($silverPurities as $purity)
                            @php
                                $key = 'silver|'.$purity;
                                $value = $rateMap[$key] ?? '';
                            @endphp
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    {{ $purity }}
                                </label>
                                <input type="number" step="0.01" min="0"
                                       name="rates[silver][{{ $purity }}]"
                                       value="{{ old('rates.silver.'.$purity, $value) }}"
                                       class="w-full border rounded px-2 py-1.5 text-sm
                                              dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100" />
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ADD MORE SILVER PURITIES --}}
                <div x-data="{ silverRows: [] }" class="mt-3">

                    <h4 class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Add Custom Silver Purity
                    </h4>

                    <template x-for="(row, index) in silverRows" :key="index">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-2">

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Custom Purity
                                </label>
                                <input type="text"
                                       x-model="row.purity"
                                       placeholder="e.g. 999"
                                       name="custom[silver][purity][]"
                                       class="w-full border rounded px-2 py-1.5 text-sm
                              dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Rate (₹/gm)
                                </label>
                                <input type="number" step="0.01" min="0"
                                       x-model="row.rate"
                                       name="custom[silver][rate][]"
                                       class="w-full border rounded px-2 py-1.5 text-sm
                              dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100">
                            </div>

                            <button type="button"
                                    @click="silverRows.splice(index, 1)"
                                    class="self-end text-xs bg-red-500 text-white px-2 py-1 rounded">
                                Remove
                            </button>
                        </div>
                    </template>

                    <button type="button"
                            @click="silverRows.push({ purity:'', rate:'' })"
                            class="mt-1 px-3 py-1.5 rounded text-xs font-medium
                   bg-slate-700 text-white hover:bg-slate-800">
                        + Add More Silver Purity
                    </button>
                </div>


                <div class="flex items-center justify-end gap-2 pt-2 border-t border-dashed border-gray-200 dark:border-neutral-700">
                    <button type="button"
                            @click="showRatesForm = false"
                            class="px-3 py-1.5 rounded text-xs border text-gray-600 dark:text-gray-200
                                   bg-white dark:bg-neutral-900 hover:bg-gray-50 dark:hover:bg-neutral-800">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-1.5 rounded text-xs font-semibold
                                   bg-indigo-600 text-white hover:bg-indigo-700">
                        Save Rates
                    </button>
                </div>
            </form>
        </div>

        {{-- LOWER SECTION: RECENT SALES & PURCHASES --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- Recent Sales --}}
            <div class="bg-[#BFE0E0] dark:bg-[#354A54] rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                        Recent Sales
                    </h2>
                    <a href="{{ route('invoices.index') }}"
                       class="text-xs text-[#FA5252] dark:text-red-700 hover:underline">
                        View all
                    </a>
                </div>

                <div class="overflow-auto">
                    <table class="min-w-full text-xs text-left text-gray-700 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-neutral-800">
                        <tr>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Invoice</th>
                            <th class="px-4 py-2">Client</th>
                            <th class="px-4 py-2 text-right">Amount</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 dark:bg-neutral-900 dark:divide-neutral-700">
                        @forelse($recentInvoices ?? [] as $inv)
                            <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                                <td class="px-4 py-2">
                                    {{ \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y') }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $inv->invoice_number ?? $inv->id }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $inv->client->name ?? '—' }}
                                </td>
                                <td class="px-4 py-2 text-right font-semibold">
                                    ₹ {{ number_format($inv->total ?? 0, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No recent sales found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Purchases --}}
            <div class="bg-[#BFE0E0] dark:bg-[#354A54] rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                        Recent Purchases
                    </h2>
                    <a href="{{ route('purchases.index') }}"
                       class="text-xs text-[#FA5252] dark:text-red-700 hover:underline">
                        View all
                    </a>
                </div>

                <div class="overflow-auto">
                    <table class="min-w-full text-xs text-left text-gray-700 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-neutral-800">
                        <tr>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Invoice No</th>
                            <th class="px-4 py-2">Supplier</th>
                            <th class="px-4 py-2 text-right">Amount</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 dark:bg-neutral-900 dark:divide-neutral-700">
                        @forelse($recentPurchases ?? [] as $pur)
                            <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                                <td class="px-4 py-2">
                                    {{ $pur->invoice_date
                                        ? \Carbon\Carbon::parse($pur->invoice_date)->format('d M Y')
                                        : '—' }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $pur->invoice_no ?? $pur->id }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $pur->supplier->name ?? '—' }}
                                </td>
                                <td class="px-4 py-2 text-right font-semibold">
                                    ₹ {{ number_format($pur->total_amount ?? 0, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No recent purchases found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- LOW STOCK ITEMS --}}
        <div class="bg-[#BFE0E0] dark:bg-[#354A54] rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                    Low Stock Items
                </h2>
                <a href="{{ route('items.index') }}"
                   class="text-xs text-[#FA5252] dark:text-red-700 hover:underline">
                    Manage items
                </a>
            </div>

            <div class="overflow-auto">
                <table class="min-w-full text-xs text-left text-gray-700 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-neutral-800">
                    <tr>
                        <th class="px-4 py-2">Item</th>
                        <th class="px-4 py-2">SKU</th>
                        <th class="px-4 py-2">Category</th>
                        <th class="px-4 py-2 text-right">Stock</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100 dark:bg-neutral-900 dark:divide-neutral-700">
                    @forelse($lowStockItems ?? [] as $it)
                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                            <td class="px-4 py-2">
                                {{ $it->name }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $it->sku ?? '—' }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $it->category->name ?? '—' }}
                            </td>
                            <td class="px-4 py-2 text-right font-semibold text-red-600 dark:text-red-400">
                                {{ $it->stock_qty }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                No low stock items found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.app>
