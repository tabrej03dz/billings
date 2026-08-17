<x-layouts.app :title="__('Client: '.$client->name)">

    <div class="space-y-6 max-w-7xl mx-auto">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <div class="flex flex-col md:flex-row md:items-center md:justify-between
                    gap-4 bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">

            <div>

                <a href="{{ route('clients.index') }}"
                   class="inline-flex items-center text-sm text-gray-500
                          dark:text-gray-300 hover:text-gray-700">

                    ← Back to Clients

                </a>

                <h1 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">
                    {{ $client->name }}
                </h1>

                <p class="text-sm text-gray-500 dark:text-gray-300">

                    Mobile:
                    {{ $client->mobile ?? '-' }}

                    @if($client->gstin)
                        • GSTIN: {{ $client->gstin }}
                    @endif

                    @if($client->pan)
                        • PAN: {{ $client->pan }}
                    @endif

                </p>

            </div>


            <div class="flex gap-2 flex-wrap">

                <a href="{{ route('clients.edit', $client->id) }}"
                   class="inline-flex items-center px-4 py-2 text-sm
                          font-medium text-white bg-green-600
                          rounded-lg hover:bg-green-700">

                    Edit Client

                </a>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- SIMPLE REPORT FILTERS --}}
        {{-- ========================================================= --}}

        <div class="rounded-xl border border-gray-200 dark:border-gray-700
                    bg-white dark:bg-[#1A1D23] p-5">

            <div class="flex flex-col lg:flex-row lg:items-center
                        lg:justify-between gap-4 mb-4">

                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                        Purchase Report
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Purchase history filter karein aur PDF ya Excel download karein.
                    </p>
                </div>

                {{-- Export Buttons --}}
                <div class="flex flex-wrap gap-2">

                    <a href="{{ route(
                            'clients.report.pdf',
                            array_merge(
                                ['client' => $client->id],
                                request()->query()
                            )
                        ) }}"
                    class="inline-flex items-center justify-center
                            px-4 py-2 rounded-lg bg-red-600
                            text-white text-sm font-medium
                            hover:bg-red-700">

                        Download PDF

                    </a>

                    <a href="{{ route(
                            'clients.report.excel',
                            array_merge(
                                ['client' => $client->id],
                                request()->query()
                            )
                        ) }}"
                    class="inline-flex items-center justify-center
                            px-4 py-2 rounded-lg bg-emerald-600
                            text-white text-sm font-medium
                            hover:bg-emerald-700">

                        Download Excel

                    </a>

                </div>

            </div>


            <form method="GET"
                action="{{ route('clients.show', $client->id) }}">

                <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">

                    {{-- Period --}}
                    <div>

                        <label class="block mb-1 text-sm font-medium
                                    text-gray-700 dark:text-gray-300">
                            Period
                        </label>

                        <select name="period"
                                class="w-full rounded-lg border-gray-300
                                    dark:bg-gray-800 dark:border-gray-600
                                    dark:text-white">

                            <option value="">
                                All Time
                            </option>

                            <option value="today"
                                @selected(request('period') === 'today')>
                                Today
                            </option>

                            <option value="this_week"
                                @selected(request('period') === 'this_week')>
                                This Week
                            </option>

                            <option value="this_month"
                                @selected(request('period') === 'this_month')>
                                This Month
                            </option>

                            <option value="last_month"
                                @selected(request('period') === 'last_month')>
                                Last Month
                            </option>

                            <option value="this_year"
                                @selected(request('period') === 'this_year')>
                                This Year
                            </option>

                        </select>

                    </div>


                    {{-- From Date --}}
                    <div>

                        <label class="block mb-1 text-sm font-medium
                                    text-gray-700 dark:text-gray-300">
                            From Date
                        </label>

                        <input type="date"
                            name="date_from"
                            value="{{ request('date_from') }}"
                            class="w-full rounded-lg border-gray-300
                                    dark:bg-gray-800 dark:border-gray-600
                                    dark:text-white">

                    </div>


                    {{-- To Date --}}
                    <div>

                        <label class="block mb-1 text-sm font-medium
                                    text-gray-700 dark:text-gray-300">
                            To Date
                        </label>

                        <input type="date"
                            name="date_to"
                            value="{{ request('date_to') }}"
                            class="w-full rounded-lg border-gray-300
                                    dark:bg-gray-800 dark:border-gray-600
                                    dark:text-white">

                    </div>


                    {{-- Payment Status --}}
                    <div>

                        <label class="block mb-1 text-sm font-medium
                                    text-gray-700 dark:text-gray-300">
                            Payment Status
                        </label>

                        <select name="payment_status"
                                class="w-full rounded-lg border-gray-300
                                    dark:bg-gray-800 dark:border-gray-600
                                    dark:text-white">

                            <option value="">
                                All
                            </option>

                            <option value="paid"
                                @selected(request('payment_status') === 'paid')>
                                Paid
                            </option>

                            <option value="partial"
                                @selected(request('payment_status') === 'partial')>
                                Partial
                            </option>

                            <option value="unpaid"
                                @selected(request('payment_status') === 'unpaid')>
                                Unpaid
                            </option>

                            <option value="due"
                                @selected(request('payment_status') === 'due')>
                                Balance Due
                            </option>

                        </select>

                    </div>


                    {{-- Search --}}
                    <div>

                        <label class="block mb-1 text-sm font-medium
                                    text-gray-700 dark:text-gray-300">
                            Search
                        </label>

                        <input type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Invoice / Item..."
                            class="w-full rounded-lg border-gray-300
                                    dark:bg-gray-800 dark:border-gray-600
                                    dark:text-white">

                    </div>

                </div>


                {{-- Buttons --}}
                <div class="flex flex-wrap gap-3 mt-4">

                    <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-blue-600
                                text-white font-medium hover:bg-blue-700">

                        Apply Filter

                    </button>

                    <a href="{{ route('clients.show', $client->id) }}"
                    class="px-5 py-2.5 rounded-lg
                            bg-gray-200 dark:bg-gray-700
                            text-gray-800 dark:text-white
                            font-medium">

                        Reset

                    </a>

                </div>

            </form>

        </div>


        {{-- ========================================================= --}}
        {{-- CLIENT + SUMMARY --}}
        {{-- ========================================================= --}}

        <div class="grid lg:grid-cols-3 gap-4">


            {{-- Client --}}
            <div class="lg:col-span-1 p-5 rounded-xl border
                        border-gray-200 dark:border-gray-700
                        bg-[#F3F4F6] dark:bg-[#1A1D23]">

                <h2 class="font-semibold text-gray-800 dark:text-white mb-3">
                    Client Details
                </h2>

                <div class="text-sm space-y-2
                            text-gray-700 dark:text-gray-200">

                    <p>
                        <strong>Name:</strong>
                        {{ $client->name }}
                    </p>

                    <p>
                        <strong>Mobile:</strong>
                        {{ $client->mobile ?? '-' }}
                    </p>

                    @if($client->gstin)

                        <p>
                            <strong>GSTIN:</strong>
                            {{ $client->gstin }}
                        </p>

                    @endif

                    @if($client->pan)

                        <p>
                            <strong>PAN:</strong>
                            {{ $client->pan }}
                        </p>

                    @endif

                    @if($client->state)

                        <p>
                            <strong>State:</strong>
                            {{ $client->state }}
                        </p>

                    @endif

                    @if($client->address)

                        <p>
                            <strong>Address:</strong>
                            {{ $client->address }}
                        </p>

                    @endif

                </div>

            </div>


            {{-- Summary --}}
            <div class="lg:col-span-2 grid sm:grid-cols-2 lg:grid-cols-3 gap-3">


                <div class="p-4 rounded-xl border border-gray-200
                            dark:border-gray-700 bg-white dark:bg-[#1A1D23]">

                    <p class="text-sm text-gray-500">
                        Total Invoices
                    </p>

                    <p class="text-2xl font-bold dark:text-white">
                        {{ $summary['total_invoices'] }}
                    </p>

                </div>


                <div class="p-4 rounded-xl border border-gray-200
                            dark:border-gray-700 bg-white dark:bg-[#1A1D23]">

                    <p class="text-sm text-gray-500">
                        Subtotal
                    </p>

                    <p class="text-xl font-bold dark:text-white">
                        ₹ {{ number_format($summary['total_subtotal'], 2) }}
                    </p>

                </div>


                <div class="p-4 rounded-xl border border-gray-200
                            dark:border-gray-700 bg-white dark:bg-[#1A1D23]">

                    <p class="text-sm text-gray-500">
                        Tax
                    </p>

                    <p class="text-xl font-bold dark:text-white">
                        ₹ {{ number_format($summary['total_tax'], 2) }}
                    </p>

                </div>


                <div class="p-4 rounded-xl border border-gray-200
                            dark:border-gray-700 bg-white dark:bg-[#1A1D23]">

                    <p class="text-sm text-gray-500">
                        Total Purchase
                    </p>

                    <p class="text-xl font-bold dark:text-white">
                        ₹ {{ number_format($summary['total_amount'], 2) }}
                    </p>

                </div>


                <div class="p-4 rounded-xl border border-emerald-200
                            dark:border-emerald-700 bg-emerald-50
                            dark:bg-[#1A1D23]">

                    <p class="text-sm text-emerald-600">
                        Amount Received
                    </p>

                    <p class="text-xl font-bold text-emerald-600">
                        ₹ {{ number_format($summary['total_received'], 2) }}
                    </p>

                </div>


                <div class="p-4 rounded-xl border border-red-200
                            dark:border-red-700 bg-red-50
                            dark:bg-[#1A1D23]">

                    <p class="text-sm text-red-600">
                        Balance Pending
                    </p>

                    <p class="text-xl font-bold text-red-600">
                        ₹ {{ number_format($summary['total_balance'], 2) }}
                    </p>

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- INVOICE HISTORY --}}
        {{-- ========================================================= --}}

        <div class="space-y-3">

            <div class="flex items-center justify-between">

                <h2 class="text-lg font-semibold
                           text-gray-800 dark:text-white">
                    Purchase History
                </h2>

                <span class="text-sm text-gray-500">
                    {{ $invoices->total() }} record(s)
                </span>

            </div>


            <div class="overflow-x-auto rounded-xl
                        border border-gray-200 dark:border-gray-700">

                <table class="min-w-full text-sm text-left
                              text-gray-700 dark:text-gray-300">

                    <thead class="bg-[#BFE0E0] dark:bg-[#354A54]
                                  text-xs uppercase font-medium tracking-wider">

                    <tr>

                        <th class="px-4 py-3">
                            Date
                        </th>

                        <th class="px-4 py-3">
                            Invoice
                        </th>

                        <th class="px-4 py-3">
                            Items
                        </th>

                        <th class="px-4 py-3 text-right">
                            Subtotal
                        </th>

                        <th class="px-4 py-3 text-right">
                            Tax
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

                        <th class="px-4 py-3 text-center">
                            Status
                        </th>

                        <th class="px-4 py-3 text-right">
                            Action
                        </th>

                    </tr>

                    </thead>


                    <tbody class="divide-y divide-gray-200
                                  dark:divide-neutral-700
                                  bg-[#F3F4F6]
                                  dark:bg-[#1A1D23]">


                    @forelse($invoices as $invoice)

                        <tr>


                            <td class="px-4 py-3">

                                {{ \Carbon\Carbon::parse(
                                    $invoice->invoice_date
                                )->format('d-m-Y') }}

                            </td>


                            <td class="px-4 py-3 font-medium">

                                {{ $invoice->invoice_prefix }}{{ $invoice->invoice_number }}

                            </td>


                            <td class="px-4 py-3">

                                {{ $invoice->items_count }}

                                {{ $invoice->items_count == 1 ? 'item' : 'items' }}

                            </td>


                            <td class="px-4 py-3 text-right">

                                ₹ {{ number_format($invoice->subtotal, 2) }}

                            </td>


                            <td class="px-4 py-3 text-right">

                                ₹ {{ number_format($invoice->tax_amount, 2) }}

                            </td>


                            <td class="px-4 py-3 text-right font-medium">

                                ₹ {{ number_format($invoice->total, 2) }}

                            </td>


                            <td class="px-4 py-3 text-right text-emerald-600">

                                ₹ {{ number_format(
                                    $invoice->received_amount,
                                    2
                                ) }}

                            </td>


                            <td class="px-4 py-3 text-right text-red-600">

                                ₹ {{ number_format(
                                    $invoice->balance,
                                    2
                                ) }}

                            </td>


                            <td class="px-4 py-3 text-center">

                                @if($invoice->balance <= 0)

                                    <span class="px-2 py-1 rounded-full
                                                 bg-green-100 text-green-700
                                                 text-xs font-medium">

                                        Paid

                                    </span>

                                @elseif(
                                    $invoice->received_amount > 0
                                    && $invoice->balance > 0
                                )

                                    <span class="px-2 py-1 rounded-full
                                                 bg-yellow-100 text-yellow-700
                                                 text-xs font-medium">

                                        Partial

                                    </span>

                                @else

                                    <span class="px-2 py-1 rounded-full
                                                 bg-red-100 text-red-700
                                                 text-xs font-medium">

                                        Unpaid

                                    </span>

                                @endif

                            </td>


                            <td class="px-4 py-3 text-right">

                                <a
                                    href="{{ route(
                                        'invoices.show',
                                        $invoice->id
                                    ) }}"
                                    class="text-blue-600 hover:underline">

                                    View

                                </a>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td colspan="10"
                                class="px-4 py-8 text-center
                                       text-gray-500">

                                No invoices found for selected filters.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>


                    @if($invoices->count())

                        <tfoot class="bg-gray-100
                                      dark:bg-gray-800 font-semibold">

                        <tr>

                            <td colspan="3"
                                class="px-4 py-3">
                                Filtered Total
                            </td>

                            <td class="px-4 py-3 text-right">

                                ₹ {{ number_format(
                                    $summary['total_subtotal'],
                                    2
                                ) }}

                            </td>

                            <td class="px-4 py-3 text-right">

                                ₹ {{ number_format(
                                    $summary['total_tax'],
                                    2
                                ) }}

                            </td>

                            <td class="px-4 py-3 text-right">

                                ₹ {{ number_format(
                                    $summary['total_amount'],
                                    2
                                ) }}

                            </td>

                            <td class="px-4 py-3 text-right
                                       text-emerald-600">

                                ₹ {{ number_format(
                                    $summary['total_received'],
                                    2
                                ) }}

                            </td>

                            <td class="px-4 py-3 text-right
                                       text-red-600">

                                ₹ {{ number_format(
                                    $summary['total_balance'],
                                    2
                                ) }}

                            </td>

                            <td colspan="2"></td>

                        </tr>

                        </tfoot>

                    @endif

                </table>

            </div>


            {{-- Pagination --}}
            <div>

                {{ $invoices->links() }}

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- RECENT ITEMS --}}
        {{-- ========================================================= --}}

        <div class="space-y-3">

            <h2 class="text-lg font-semibold
                       text-gray-800 dark:text-white">

                Recent Items From Filtered Report

            </h2>


            <div class="overflow-x-auto rounded-xl
                        border border-gray-200 dark:border-gray-700">

                <table class="min-w-full text-sm text-left
                              text-gray-700 dark:text-gray-300">

                    <thead class="bg-[#BFE0E0]
                                  dark:bg-[#354A54]
                                  text-xs uppercase font-medium">

                    <tr>

                        <th class="px-4 py-3">
                            Date
                        </th>

                        <th class="px-4 py-3">
                            Invoice No.
                        </th>

                        <th class="px-4 py-3">
                            Description
                        </th>

                        <th class="px-4 py-3">
                            Qty
                        </th>

                        <th class="px-4 py-3 text-right">
                            Amount
                        </th>

                    </tr>

                    </thead>


                    <tbody class="divide-y divide-gray-200
                                  dark:divide-neutral-700
                                  bg-[#F3F4F6]
                                  dark:bg-[#1A1D23]">


                    @forelse($recentItems as $item)

                        <tr>


                            <td class="px-4 py-3">

                                {{ optional($item->invoice)->invoice_date
                                    ? \Carbon\Carbon::parse(
                                        $item->invoice->invoice_date
                                      )->format('d-m-Y')
                                    : '-'
                                }}

                            </td>


                            <td class="px-4 py-3">

                                @if($item->invoice)

                                    {{ $item->invoice->invoice_prefix }}{{ $item->invoice->invoice_number }}

                                @else

                                    —

                                @endif

                            </td>


                            <td class="px-4 py-3">

                                {{ $item->description }}

                            </td>


                            <td class="px-4 py-3">

                                {{ $item->quantity }}

                            </td>


                            <td class="px-4 py-3 text-right">

                                ₹ {{ number_format($item->amount, 2) }}

                            </td>


                        </tr>


                    @empty

                        <tr>

                            <td colspan="5"
                                class="px-4 py-5 text-center
                                       text-gray-500">

                                No items found.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>


    </div>

</x-layouts.app>