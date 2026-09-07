@php
    $invoice = $ewayBill->invoice;
@endphp

<x-layouts.app :title="'E-Way Bill'">

    <div class="max-w-5xl mx-auto py-6">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex justify-between items-center mb-5">

            <div>
                <h1 class="text-2xl font-bold">
                    E-Way Bill
                </h1>

                <div class="text-gray-500">
                    Invoice #{{ $invoice->invoice_number }}
                </div>
            </div>

            <div class="flex gap-2">

                <a
                    href="{{ route('eway-bills.print', $ewayBill->id) }}"
                    target="_blank"
                    class="px-4 py-2 bg-gray-800 text-white rounded-lg"
                >
                    Print
                </a>

                <a
                    href="{{ route('invoices.preview', $invoice->id) }}"
                    class="px-4 py-2 border rounded-lg"
                >
                    Back to Invoice
                </a>

            </div>

        </div>


        <div class="bg-white shadow rounded-xl overflow-hidden">

            <div class="p-5 border-b">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <div>
                        <div class="text-xs text-gray-500">
                            E-Way Bill No.
                        </div>

                        <div class="font-bold text-lg">
                            {{ $ewayBill->eway_bill_no ?: '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">
                            Document No.
                        </div>

                        <div class="font-semibold">
                            {{ $ewayBill->document_no }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">
                            Document Date
                        </div>

                        <div class="font-semibold">
                            {{ optional($ewayBill->document_date)->format('d-m-Y') }}
                        </div>
                    </div>

                </div>
            </div>


            <div class="grid grid-cols-1 md:grid-cols-2">

                <div class="p-5 border-r">

                    <h3 class="font-bold mb-3">
                        From
                    </h3>

                    <div>
                        {{ $ewayBill->from_name }}
                    </div>

                    <div>
                        {{ $ewayBill->from_address }}
                    </div>

                    <div>
                        {{ $ewayBill->from_place }}
                        - {{ $ewayBill->from_pincode }}
                    </div>

                    <div>
                        {{ $ewayBill->from_state }}
                    </div>

                    @if($ewayBill->from_gstin)
                        <div>
                            GSTIN:
                            {{ $ewayBill->from_gstin }}
                        </div>
                    @endif

                </div>


                <div class="p-5">

                    <h3 class="font-bold mb-3">
                        To
                    </h3>

                    <div>
                        {{ $ewayBill->to_name }}
                    </div>

                    <div>
                        {{ $ewayBill->to_address }}
                    </div>

                    <div>
                        {{ $ewayBill->to_place }}
                        - {{ $ewayBill->to_pincode }}
                    </div>

                    <div>
                        {{ $ewayBill->to_state }}
                    </div>

                    @if($ewayBill->to_gstin)
                        <div>
                            GSTIN:
                            {{ $ewayBill->to_gstin }}
                        </div>
                    @endif

                </div>

            </div>


            <div class="p-5 border-t">

                <h3 class="font-bold mb-3">
                    Transportation
                </h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                    <div>
                        <div class="text-xs text-gray-500">
                            Mode
                        </div>

                        <div>
                            {{ $ewayBill->transport_mode }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">
                            Distance
                        </div>

                        <div>
                            {{ $ewayBill->distance }} KM
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">
                            Vehicle No.
                        </div>

                        <div>
                            {{ $ewayBill->vehicle_no ?: '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">
                            Vehicle Type
                        </div>

                        <div>
                            {{ $ewayBill->vehicle_type ?: '-' }}
                        </div>
                    </div>

                </div>

            </div>


            <div class="p-5 border-t">

                <h3 class="font-bold mb-3">
                    Items
                </h3>

                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead>
                        <tr>
                            <th class="border p-2">Item</th>
                            <th class="border p-2">HSN</th>
                            <th class="border p-2">Qty</th>
                            <th class="border p-2">Rate</th>
                            <th class="border p-2">Tax</th>
                            <th class="border p-2">Amount</th>
                        </tr>
                        </thead>

                        <tbody>

                        @foreach($invoice->items as $item)

                            <tr>
                                <td class="border p-2">
                                    {{ $item->description }}
                                </td>

                                <td class="border p-2">
                                    {{ $item->sac_code }}
                                </td>

                                <td class="border p-2">
                                    {{ $item->quantity }}
                                </td>

                                <td class="border p-2">
                                    ₹{{ number_format($item->rate, 2) }}
                                </td>

                                <td class="border p-2">
                                    {{ $item->tax_percent }}%
                                </td>

                                <td class="border p-2">
                                    ₹{{ number_format($item->amount, 2) }}
                                </td>
                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

                <div class="text-right mt-5 text-xl font-bold">
                    Invoice Total:
                    ₹{{ number_format($invoice->total ?? 0, 2) }}
                </div>

            </div>

        </div>

    </div>

</x-layouts.app>