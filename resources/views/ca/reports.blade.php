<x-layouts.app>

    <div class="space-y-6">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    CA Invoice Reports
                </h1>

                <p class="mt-1 text-sm text-gray-600 dark:text-neutral-300">
                    Assigned businesses me se business choose karke
                    filtered invoice report preview aur download karein.
                </p>
            </div>

            @if(isset($business) && $business)
                <div
                    class="rounded-xl bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700
                    dark:bg-indigo-950/40 dark:text-indigo-300"
                >
                    {{ $business->name }}
                </div>
            @endif
        </div>


        {{-- ========================================================= --}}
        {{-- SUCCESS / ERROR MESSAGE --}}
        {{-- ========================================================= --}}

        @if(session('success'))
            <div
                class="rounded-xl border border-green-200 bg-green-50 px-4 py-3
                text-sm font-medium text-green-700
                dark:border-green-900 dark:bg-green-950/40 dark:text-green-300"
            >
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div
                class="rounded-xl border border-red-200 bg-red-50 px-4 py-3
                text-sm font-medium text-red-700
                dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
            >
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div
                class="rounded-xl border border-red-200 bg-red-50 px-4 py-3
                text-sm text-red-700
                dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
            >
                <ul class="list-inside list-disc space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- ========================================================= --}}
        {{-- FILTER FORM --}}
        {{-- ========================================================= --}}

        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm
            dark:border-neutral-800 dark:bg-neutral-900"
        >

            <form
                method="GET"
                action="{{ route('ca.reports') }}"
                class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-7"
            >

                {{-- Business --}}
                <div class="xl:col-span-2">
                    <label
                        for="business_id"
                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-neutral-300"
                    >
                        Business
                    </label>

                    <select
                        id="business_id"
                        name="business_id"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5
                        text-sm text-gray-900
                        focus:border-indigo-500 focus:ring-indigo-500
                        dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                    >
                        @forelse($businesses ?? [] as $item)
                            <option
                                value="{{ $item->id }}"
                                @selected(
                                    (int)($filters['business_id'] ?? 0)
                                    ===
                                    (int)$item->id
                                )
                            >
                                {{ $item->name }}
                            </option>
                        @empty
                            <option value="">
                                No assigned business
                            </option>
                        @endforelse
                    </select>
                </div>


                {{-- Report Type --}}
                <div>
                    <label
                        for="type"
                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-neutral-300"
                    >
                        Report Type
                    </label>

                    <select
                        id="type"
                        name="type"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5
                        text-sm text-gray-900
                        focus:border-indigo-500 focus:ring-indigo-500
                        dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                    >
                        <option
                            value="tax"
                            @selected(($activeType ?? 'tax') === 'tax')
                        >
                            Tax Invoice
                        </option>

                        <option
                            value="proforma"
                            @selected(($activeType ?? '') === 'proforma')
                        >
                            Proforma
                        </option>

                        <option
                            value="quotation"
                            @selected(($activeType ?? '') === 'quotation')
                        >
                            Quotation
                        </option>
                    </select>
                </div>


                {{-- Search --}}
                <div class="xl:col-span-2">
                    <label
                        for="search"
                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-neutral-300"
                    >
                        Search
                    </label>

                    <input
                        id="search"
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Invoice, client, phone, GSTIN, PAN..."
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5
                        text-sm text-gray-900
                        placeholder:text-gray-400
                        focus:border-indigo-500 focus:ring-indigo-500
                        dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                    >
                </div>


                {{-- Payment Status --}}
                <div>
                    <label
                        for="status"
                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-neutral-300"
                    >
                        Status
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5
                        text-sm text-gray-900
                        focus:border-indigo-500 focus:ring-indigo-500
                        dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                    >
                        <option value="">
                            All
                        </option>

                        <option
                            value="paid"
                            @selected(($filters['status'] ?? '') === 'paid')
                        >
                            Paid
                        </option>

                        <option
                            value="partial"
                            @selected(($filters['status'] ?? '') === 'partial')
                        >
                            Partial
                        </option>

                        <option
                            value="unpaid"
                            @selected(($filters['status'] ?? '') === 'unpaid')
                        >
                            Unpaid
                        </option>
                    </select>
                </div>


                {{-- Date Range --}}
                <div>
                    <label
                        for="ca_date_range"
                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-neutral-300"
                    >
                        Date Range
                    </label>

                    <select
                        id="ca_date_range"
                        name="date_range"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5
                        text-sm text-gray-900
                        focus:border-indigo-500 focus:ring-indigo-500
                        dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                    >

                        <option
                            value="last_month"
                            @selected(($filters['date_range'] ?? 'last_month') === 'last_month')
                        >
                            Last Month
                        </option>

                        <option
                            value="quarter"
                            @selected(($filters['date_range'] ?? '') === 'quarter')
                        >
                            Last Quarter
                        </option>

                        <option
                            value="half_year"
                            @selected(($filters['date_range'] ?? '') === 'half_year')
                        >
                            Last Half-Year
                        </option>

                        <option
                            value="last_year"
                            @selected(($filters['date_range'] ?? '') === 'last_year')
                        >
                            Last Year
                        </option>

                        <option
                            value="all"
                            @selected(($filters['date_range'] ?? '') === 'all')
                        >
                            All Time
                        </option>

                        <option
                            value="custom"
                            @selected(($filters['date_range'] ?? '') === 'custom')
                        >
                            Custom
                        </option>
                    </select>
                </div>


                {{-- From Date --}}
                <div id="ca_from_wrap">
                    <label
                        for="from_date"
                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-neutral-300"
                    >
                        From
                    </label>

                    <input
                        id="from_date"
                        type="date"
                        name="from_date"
                        value="{{ $filters['from_date'] ?? '' }}"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5
                        text-sm text-gray-900
                        focus:border-indigo-500 focus:ring-indigo-500
                        dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                    >
                </div>


                {{-- To Date --}}
                <div id="ca_to_wrap">
                    <label
                        for="to_date"
                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-neutral-300"
                    >
                        To
                    </label>

                    <input
                        id="to_date"
                        type="date"
                        name="to_date"
                        value="{{ $filters['to_date'] ?? '' }}"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5
                        text-sm text-gray-900
                        focus:border-indigo-500 focus:ring-indigo-500
                        dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                    >
                </div>


                {{-- File Format --}}
                <div>
                    <label
                        for="file_format"
                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-neutral-300"
                    >
                        Format
                    </label>

                    <select
                        id="file_format"
                        name="file_format"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5
                        text-sm text-gray-900
                        focus:border-indigo-500 focus:ring-indigo-500
                        dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                    >
                        <option
                            value="excel"
                            @selected(($filters['file_format'] ?? 'excel') === 'excel')
                        >
                            Excel
                        </option>

                        <option
                            value="pdf"
                            @selected(($filters['file_format'] ?? '') === 'pdf')
                        >
                            PDF
                        </option>
                    </select>
                </div>


                {{-- Buttons --}}
                <div class="flex flex-wrap items-end gap-2 md:col-span-2 xl:col-span-4">

                    <button
                        type="submit"
                        class="rounded-xl bg-amber-600 px-4 py-2.5
                        text-sm font-semibold text-white
                        transition hover:bg-amber-700"
                    >
                        Apply Filters
                    </button>

                    <button
                        type="submit"
                        formaction="{{ route('ca.reports.download') }}"
                        class="rounded-xl bg-green-600 px-4 py-2.5
                        text-sm font-semibold text-white
                        transition hover:bg-green-700"
                    >
                        Download Report
                    </button>

                    <a
                        href="{{ route('ca.reports') }}"
                        class="rounded-xl border border-gray-300 px-4 py-2.5
                        text-sm font-semibold text-gray-700
                        transition hover:bg-gray-50
                        dark:border-neutral-700 dark:text-neutral-200
                        dark:hover:bg-neutral-800"
                    >
                        Reset
                    </a>

                </div>

            </form>

        </div>


        {{-- ========================================================= --}}
        {{-- SUMMARY --}}
        {{-- ========================================================= --}}

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

            <div
                class="rounded-2xl border border-gray-200 bg-white p-4
                dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Invoices
                </div>

                <div class="mt-1 text-xl font-bold text-gray-900 dark:text-white">
                    {{ number_format((int)($summary['invoice_count'] ?? 0)) }}
                </div>
            </div>


            <div
                class="rounded-2xl border border-gray-200 bg-white p-4
                dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Total
                </div>

                <div class="mt-1 text-xl font-bold text-gray-900 dark:text-white">
                    ₹{{ number_format((float)($summary['total'] ?? 0), 2) }}
                </div>
            </div>


            <div
                class="rounded-2xl border border-gray-200 bg-white p-4
                dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Received
                </div>

                <div class="mt-1 text-xl font-bold text-green-600">
                    ₹{{ number_format((float)($summary['received'] ?? 0), 2) }}
                </div>
            </div>


            <div
                class="rounded-2xl border border-gray-200 bg-white p-4
                dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Balance
                </div>

                <div class="mt-1 text-xl font-bold text-red-600">
                    ₹{{ number_format((float)($summary['balance'] ?? 0), 2) }}
                </div>
            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- REPORT TABLE --}}
        {{-- ========================================================= --}}

        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white
            dark:border-neutral-800 dark:bg-neutral-900"
        >

            <div class="overflow-x-auto">

                <table class="min-w-full text-sm">

                    <thead
                        class="bg-gray-50 text-left text-xs uppercase text-gray-500
                        dark:bg-neutral-800 dark:text-neutral-400"
                    >
                        <tr>
                            <th class="px-4 py-3">
                                Date
                            </th>

                            <th class="px-4 py-3">
                                Invoice
                            </th>

                            <th class="px-4 py-3">
                                Client
                            </th>

                            <th class="px-4 py-3 text-right">
                                Total
                            </th>

                            <th class="px-4 py-3 text-right">
                                Received
                            </th>

                            <th class="px-4 py-3 text-right">
                                Balance
                            </th>
                        </tr>
                    </thead>


                    <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">

                        @forelse($rows ?? [] as $row)

                            @php
                                $invoiceDate = null;

                                if (!empty($row->invoice_date)) {
                                    try {
                                        $invoiceDate = \Illuminate\Support\Carbon::parse(
                                            $row->invoice_date
                                        )->format('d-m-Y');
                                    } catch (\Throwable $e) {
                                        $invoiceDate = $row->invoice_date;
                                    }
                                }

                                $total = (float)($row->total ?? 0);

                                $received = (float)(
                                    $row->received_amount
                                    ?? $row->paid_amount
                                    ?? 0
                                );

                                $balance = isset($row->balance)
                                    ? (float)$row->balance
                                    : max(0, $total - $received);
                            @endphp


                            <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800/50">

                                <td class="whitespace-nowrap px-4 py-3">
                                    {{ $invoiceDate ?: '-' }}
                                </td>


                                <td class="whitespace-nowrap px-4 py-3 font-medium">
                                    {{ $row->invoice_number ?? $row->invoice_no ?? '-' }}
                                </td>


                                <td class="px-4 py-3">

                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $row->client?->name ?? '-' }}
                                    </div>

                                    @if(
                                        !empty($row->client?->phone) ||
                                        !empty($row->client?->mobile)
                                    )
                                        <div class="mt-0.5 text-xs text-gray-500">
                                            {{
                                                $row->client?->phone
                                                ?? $row->client?->mobile
                                            }}
                                        </div>
                                    @endif

                                </td>


                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    ₹{{ number_format($total, 2) }}
                                </td>


                                <td class="whitespace-nowrap px-4 py-3 text-right text-green-600">
                                    ₹{{ number_format($received, 2) }}
                                </td>


                                <td class="whitespace-nowrap px-4 py-3 text-right text-red-600">
                                    ₹{{ number_format($balance, 2) }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="6"
                                    class="px-4 py-12 text-center text-gray-500
                                    dark:text-neutral-400"
                                >
                                    Selected filters me koi invoice nahi mila.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- ===================================================== --}}
            {{-- PAGINATION --}}
            {{-- Collection aur paginator dono me safe --}}
            {{-- ===================================================== --}}

            @if(
                isset($rows)
                &&
                (
                    $rows instanceof \Illuminate\Pagination\LengthAwarePaginator
                    ||
                    $rows instanceof \Illuminate\Pagination\Paginator
                )
            )

                <div
                    class="border-t border-gray-200 px-4 py-3
                    dark:border-neutral-800"
                >
                    {{ $rows->withQueryString()->links() }}
                </div>

            @endif

        </div>

    </div>


    {{-- ============================================================= --}}
    {{-- CUSTOM DATE JS --}}
    {{-- ============================================================= --}}

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const range = document.getElementById('ca_date_range');
            const fromWrap = document.getElementById('ca_from_wrap');
            const toWrap = document.getElementById('ca_to_wrap');

            if (!range || !fromWrap || !toWrap) {
                return;
            }

            function syncCustomDateFields() {

                const showCustom =
                    range.value === 'custom';

                fromWrap.style.display =
                    showCustom ? 'block' : 'none';

                toWrap.style.display =
                    showCustom ? 'block' : 'none';
            }

            syncCustomDateFields();

            range.addEventListener(
                'change',
                syncCustomDateFields
            );

        });
    </script>

</x-layouts.app>