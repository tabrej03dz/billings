@php
    $invoice  = $ewayBill->invoice;
    $business = $invoice->business;
    $client   = $invoice->client;

    $totalQty = $invoice->items->sum('quantity');

    $taxableAmount = $invoice->items->sum(function ($item) {
        return (float)($item->quantity ?? 0) * (float)($item->rate ?? 0);
    });
@endphp

<x-layouts.app :title="'E-Way Bill'">

    <div class="max-w-7xl mx-auto px-4 py-6">

        {{-- Success Message --}}
        @if(session('success'))
            <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700">
                {{ session('success') }}
            </div>
        @endif


        {{-- ================= HEADER ================= --}}
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    E-Way Bill
                </h1>

                <div class="mt-1 text-sm text-gray-500 dark:text-neutral-400">
                    Invoice #{{ $invoice->invoice_number }}
                </div>
            </div>


            <div class="flex flex-wrap gap-2">

                <a
                    href="{{ route('invoices.preview', $invoice->id) }}"
                    class="inline-flex items-center justify-center rounded-lg border
                           border-gray-300 bg-white px-4 py-2 text-sm font-medium
                           text-gray-700 transition hover:bg-gray-50
                           dark:border-neutral-700 dark:bg-neutral-900
                           dark:text-neutral-200 dark:hover:bg-neutral-800"
                >
                    ← Back to Invoice
                </a>


                <a
                    href="{{ route('eway-bills.print', $ewayBill->id) }}"
                    target="_blank"
                    class="inline-flex items-center justify-center rounded-lg
                           bg-gray-800 px-4 py-2 text-sm font-semibold text-white
                           transition hover:bg-gray-900"
                >
                    Print E-Way Bill
                </a>

            </div>

        </div>


        {{-- ================= MAIN CARD ================= --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm
                    dark:border-neutral-800 dark:bg-neutral-900">


            {{-- ================= 1. E-WAY DETAILS ================= --}}
            <div class="bg-emerald-600 px-5 py-3 text-sm font-bold text-white">
                1. E-Way Bill Details
            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4">

                <div class="border-b border-r border-gray-200 p-4 dark:border-neutral-800">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                        E-Way Bill No.
                    </div>

                    <div class="mt-1 text-lg font-bold text-gray-900 dark:text-white">
                        {{ $ewayBill->eway_bill_no ?: '-' }}
                    </div>
                </div>


                <div class="border-b border-r border-gray-200 p-4 dark:border-neutral-800">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                        Status
                    </div>

                    <div class="mt-2">
                        <span
                            class="inline-flex rounded-full px-3 py-1 text-xs font-bold
                            {{ strtolower($ewayBill->status ?? '') === 'cancelled'
                                ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300'
                                : 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300'
                            }}"
                        >
                            {{ strtoupper($ewayBill->status ?? 'generated') }}
                        </span>
                    </div>
                </div>


                <div class="border-b border-r border-gray-200 p-4 dark:border-neutral-800">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                        Generated Date
                    </div>

                    <div class="mt-1 font-semibold text-gray-900 dark:text-white">
                        {{ $ewayBill->eway_bill_date
                            ? $ewayBill->eway_bill_date->format('d M Y, h:i A')
                            : '-' }}
                    </div>
                </div>


                <div class="border-b border-gray-200 p-4 dark:border-neutral-800">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                        Valid Upto
                    </div>

                    <div class="mt-1 font-semibold text-gray-900 dark:text-white">
                        {{ $ewayBill->valid_upto
                            ? $ewayBill->valid_upto->format('d M Y, h:i A')
                            : '-' }}
                    </div>
                </div>

            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4">

                <div class="border-b border-r border-gray-200 p-4 dark:border-neutral-800">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                        Supply Type
                    </div>

                    <div class="mt-1 font-semibold">
                        {{ $ewayBill->supply_type ?: '-' }}
                        @if($ewayBill->sub_supply_type)
                            - {{ $ewayBill->sub_supply_type }}
                        @endif
                    </div>
                </div>


                <div class="border-b border-r border-gray-200 p-4 dark:border-neutral-800">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                        Document Type
                    </div>

                    <div class="mt-1 font-semibold">
                        {{ $ewayBill->document_type ?: 'Tax Invoice' }}
                    </div>
                </div>


                <div class="border-b border-r border-gray-200 p-4 dark:border-neutral-800">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                        Document No.
                    </div>

                    <div class="mt-1 font-semibold">
                        {{ $ewayBill->document_no ?: $invoice->invoice_number }}
                    </div>
                </div>


                <div class="border-b border-gray-200 p-4 dark:border-neutral-800">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                        Document Date
                    </div>

                    <div class="mt-1 font-semibold">
                        {{ $ewayBill->document_date
                            ? $ewayBill->document_date->format('d M Y')
                            : optional($invoice->invoice_date)->format('d M Y') }}
                    </div>
                </div>

            </div>



            {{-- ================= 2. ADDRESS ================= --}}
            <div class="bg-emerald-600 px-5 py-3 text-sm font-bold text-white">
                2. Address Details
            </div>


            <div class="grid grid-cols-1 lg:grid-cols-2">

                {{-- FROM --}}
                <div class="border-b border-r border-gray-200 p-5 dark:border-neutral-800">

                    <div class="mb-4 inline-flex rounded-lg bg-emerald-50 px-3 py-1
                                text-sm font-bold text-emerald-700
                                dark:bg-emerald-950 dark:text-emerald-300">
                        From / Supplier
                    </div>


                    <div class="space-y-2 text-sm">

                        <div>
                            <span class="text-gray-500">Name:</span>

                            <span class="font-semibold">
                                {{ $ewayBill->from_name ?: ($business->name ?? '-') }}
                            </span>
                        </div>


                        <div>
                            <span class="text-gray-500">GSTIN:</span>

                            <span class="font-semibold">
                                {{ $ewayBill->from_gstin ?: '-' }}
                            </span>
                        </div>


                        <div>
                            <span class="text-gray-500">Address:</span>

                            <span class="font-semibold">
                                {{ $ewayBill->from_address ?: '-' }}
                            </span>
                        </div>


                        <div>
                            <span class="text-gray-500">Place:</span>

                            <span class="font-semibold">
                                {{ $ewayBill->from_place ?: '-' }}

                                @if($ewayBill->from_pincode)
                                    - {{ $ewayBill->from_pincode }}
                                @endif
                            </span>
                        </div>


                        <div>
                            <span class="text-gray-500">State:</span>

                            <span class="font-semibold">
                                {{ $ewayBill->from_state ?: '-' }}

                                @if($ewayBill->from_state_code)
                                    ({{ $ewayBill->from_state_code }})
                                @endif
                            </span>
                        </div>

                    </div>

                </div>


                {{-- TO --}}
                <div class="border-b border-gray-200 p-5 dark:border-neutral-800">

                    <div class="mb-4 inline-flex rounded-lg bg-blue-50 px-3 py-1
                                text-sm font-bold text-blue-700
                                dark:bg-blue-950 dark:text-blue-300">
                        To / Customer
                    </div>


                    <div class="space-y-2 text-sm">

                        <div>
                            <span class="text-gray-500">Name:</span>

                            <span class="font-semibold">
                                {{ $ewayBill->to_name ?: ($client->name ?? '-') }}
                            </span>
                        </div>


                        <div>
                            <span class="text-gray-500">GSTIN:</span>

                            <span class="font-semibold">
                                {{ $ewayBill->to_gstin ?: '-' }}
                            </span>
                        </div>


                        <div>
                            <span class="text-gray-500">Address:</span>

                            <span class="font-semibold">
                                {{ $ewayBill->to_address ?: '-' }}
                            </span>
                        </div>


                        <div>
                            <span class="text-gray-500">Place:</span>

                            <span class="font-semibold">
                                {{ $ewayBill->to_place ?: '-' }}

                                @if($ewayBill->to_pincode)
                                    - {{ $ewayBill->to_pincode }}
                                @endif
                            </span>
                        </div>


                        <div>
                            <span class="text-gray-500">State:</span>

                            <span class="font-semibold">
                                {{ $ewayBill->to_state ?: '-' }}

                                @if($ewayBill->to_state_code)
                                    ({{ $ewayBill->to_state_code }})
                                @endif
                            </span>
                        </div>

                    </div>

                </div>

            </div>



            {{-- ================= 3. GOODS ================= --}}
            <div class="bg-emerald-600 px-5 py-3 text-sm font-bold text-white">
                3. Goods Details
            </div>


            <div class="overflow-x-auto p-5">

                <table class="min-w-full overflow-hidden rounded-xl border border-gray-200 text-sm
                              dark:border-neutral-800">

                    <thead class="bg-emerald-50 text-gray-700 dark:bg-emerald-950/40 dark:text-neutral-200">

                        <tr>

                            <th class="border px-3 py-3 text-center">
                                #
                            </th>

                            <th class="border px-3 py-3 text-left">
                                HSN/SAC
                            </th>

                            <th class="border px-3 py-3 text-left">
                                Item
                            </th>

                            <th class="border px-3 py-3 text-center">
                                Qty
                            </th>

                            <th class="border px-3 py-3 text-right">
                                Rate
                            </th>

                            <th class="border px-3 py-3 text-center">
                                Tax
                            </th>

                            <th class="border px-3 py-3 text-right">
                                Amount
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    @foreach($invoice->items as $index => $item)

                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800/50">

                            <td class="border px-3 py-3 text-center">
                                {{ $index + 1 }}
                            </td>

                            <td class="border px-3 py-3">
                                {{ $item->sac_code ?: '-' }}
                            </td>

                            <td class="border px-3 py-3 font-medium">
                                {{ $item->description ?: '-' }}
                            </td>

                            <td class="border px-3 py-3 text-center">
                                {{ number_format((float)($item->quantity ?? 0), 2) }}
                            </td>

                            <td class="border px-3 py-3 text-right">
                                ₹{{ number_format((float)($item->rate ?? 0), 2) }}
                            </td>

                            <td class="border px-3 py-3 text-center">
                                {{ number_format((float)($item->tax_percent ?? 0), 2) }}%
                            </td>

                            <td class="border px-3 py-3 text-right font-semibold">
                                ₹{{ number_format((float)($item->amount ?? 0), 2) }}
                            </td>

                        </tr>

                    @endforeach


                    <tr class="bg-emerald-50 font-bold dark:bg-emerald-950/30">

                        <td colspan="3" class="border px-3 py-3 text-right">
                            Total
                        </td>

                        <td class="border px-3 py-3 text-center">
                            {{ number_format((float)$totalQty, 2) }}
                        </td>

                        <td class="border"></td>

                        <td class="border"></td>

                        <td class="border px-3 py-3 text-right">
                            ₹{{ number_format((float)($invoice->total ?? 0), 2) }}
                        </td>

                    </tr>

                    </tbody>

                </table>

            </div>



            {{-- ================= TAX SUMMARY ================= --}}
            <div class="grid grid-cols-2 gap-0 border-t border-gray-200
                        md:grid-cols-3 xl:grid-cols-6 dark:border-neutral-800">

                <div class="border-r border-gray-200 p-4 text-center dark:border-neutral-800">
                    <div class="text-xs text-gray-500">Taxable Amount</div>

                    <div class="mt-1 font-bold">
                        ₹{{ number_format(
                            (float)($invoice->subtotal ?? $taxableAmount),
                            2
                        ) }}
                    </div>
                </div>


                <div class="border-r border-gray-200 p-4 text-center dark:border-neutral-800">
                    <div class="text-xs text-gray-500">CGST</div>

                    <div class="mt-1 font-bold">
                        ₹{{ number_format((float)($invoice->cgst_amount ?? 0), 2) }}
                    </div>
                </div>


                <div class="border-r border-gray-200 p-4 text-center dark:border-neutral-800">
                    <div class="text-xs text-gray-500">SGST</div>

                    <div class="mt-1 font-bold">
                        ₹{{ number_format((float)($invoice->sgst_amount ?? 0), 2) }}
                    </div>
                </div>


                <div class="border-r border-gray-200 p-4 text-center dark:border-neutral-800">
                    <div class="text-xs text-gray-500">IGST</div>

                    <div class="mt-1 font-bold">
                        ₹{{ number_format((float)($invoice->igst_amount ?? 0), 2) }}
                    </div>
                </div>


                <div class="border-r border-gray-200 p-4 text-center dark:border-neutral-800">
                    <div class="text-xs text-gray-500">Other</div>

                    <div class="mt-1 font-bold">
                        ₹0.00
                    </div>
                </div>


                <div class="p-4 text-center">
                    <div class="text-xs text-gray-500">
                        Total Invoice Amount
                    </div>

                    <div class="mt-1 text-lg font-bold text-emerald-700 dark:text-emerald-300">
                        ₹{{ number_format((float)($invoice->total ?? 0), 2) }}
                    </div>
                </div>

            </div>



            {{-- ================= 4. TRANSPORTATION ================= --}}
            <div class="bg-emerald-600 px-5 py-3 text-sm font-bold text-white">
                4. Transportation Details
            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4">

                <div class="border-b border-r border-gray-200 p-4 dark:border-neutral-800">
                    <div class="text-xs text-gray-500">
                        Transport Mode
                    </div>

                    <div class="mt-1 font-semibold">
                        {{ $ewayBill->transport_mode ?: '-' }}
                    </div>
                </div>


                <div class="border-b border-r border-gray-200 p-4 dark:border-neutral-800">
                    <div class="text-xs text-gray-500">
                        Distance
                    </div>

                    <div class="mt-1 font-semibold">
                        {{ $ewayBill->distance ? number_format($ewayBill->distance) . ' KM' : '-' }}
                    </div>
                </div>


                <div class="border-b border-r border-gray-200 p-4 dark:border-neutral-800">
                    <div class="text-xs text-gray-500">
                        Transporter
                    </div>

                    <div class="mt-1 font-semibold">
                        {{ $ewayBill->transporter_name ?: '-' }}
                    </div>
                </div>


                <div class="border-b border-gray-200 p-4 dark:border-neutral-800">
                    <div class="text-xs text-gray-500">
                        Transporter ID / GSTIN
                    </div>

                    <div class="mt-1 font-semibold">
                        {{ $ewayBill->transporter_id ?: '-' }}
                    </div>
                </div>


                <div class="border-r border-gray-200 p-4 dark:border-neutral-800">
                    <div class="text-xs text-gray-500">
                        Vehicle No.
                    </div>

                    <div class="mt-1 font-semibold">
                        {{ $ewayBill->vehicle_no ?: '-' }}
                    </div>
                </div>


                <div class="border-r border-gray-200 p-4 dark:border-neutral-800">
                    <div class="text-xs text-gray-500">
                        Vehicle Type
                    </div>

                    <div class="mt-1 font-semibold">
                        {{ $ewayBill->vehicle_type ?: '-' }}
                    </div>
                </div>


                <div class="border-r border-gray-200 p-4 dark:border-neutral-800">
                    <div class="text-xs text-gray-500">
                        Transport Doc No.
                    </div>

                    <div class="mt-1 font-semibold">
                        {{ $ewayBill->transport_doc_no ?: '-' }}
                    </div>
                </div>


                <div class="p-4">
                    <div class="text-xs text-gray-500">
                        Transport Doc Date
                    </div>

                    <div class="mt-1 font-semibold">
                        {{ $ewayBill->transport_doc_date
                            ? $ewayBill->transport_doc_date->format('d M Y')
                            : '-' }}
                    </div>
                </div>

            </div>


        </div>

    </div>

</x-layouts.app>