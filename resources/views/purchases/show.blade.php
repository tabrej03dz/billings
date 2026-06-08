<x-layouts.app :title="__('Purchase: '.$purchase->invoice_no)">
    <div class="space-y-6 max-w-6xl mx-auto">

        <div class="flex items-center justify-between gap-3 bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <div>
                <a href="{{ route('purchases.index') }}"
                   class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                    ← Back to Purchases
                </a>

                <h1 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">
                    Purchase Invoice
                    @if($purchase->invoice_no)
                        #{{ $purchase->invoice_no }}
                    @endif
                </h1>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Date:
                    {{ \Carbon\Carbon::parse($purchase->invoice_date)->format('d-m-Y') }}
                    @if($purchase->supplier)
                        • Supplier: {{ $purchase->supplier->name }}
                    @endif
                </p>
            </div>

            <div class="flex gap-2">
                @if($purchase->bill_file)
                    <a href="{{ asset('storage/'.$purchase->bill_file) }}"
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        View Bill
                    </a>
                @endif

                <a href="{{ route('purchases.edit', $purchase->id) }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                    Edit Purchase
                </a>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">

            <div class="md:col-span-2 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-[#F3F4F6] dark:bg-[#1A1D23] space-y-2">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Supplier Details
                </h2>

                <div class="text-sm text-gray-700 dark:text-gray-200 space-y-1">
                    @if($purchase->supplier)
                        <p><span class="font-medium">Name:</span> {{ $purchase->supplier->name }}</p>

                        @if($purchase->supplier->mobile)
                            <p><span class="font-medium">Mobile:</span> {{ $purchase->supplier->mobile }}</p>
                        @endif

                        @if($purchase->supplier->gstin)
                            <p><span class="font-medium">GSTIN:</span> {{ $purchase->supplier->gstin }}</p>
                        @endif

                        @if($purchase->supplier->pan)
                            <p><span class="font-medium">PAN:</span> {{ $purchase->supplier->pan }}</p>
                        @endif

                        @if($purchase->supplier->state)
                            <p><span class="font-medium">State:</span> {{ $purchase->supplier->state }}</p>
                        @endif

                        @if($purchase->supplier->address)
                            <p><span class="font-medium">Address:</span> {{ $purchase->supplier->address }}</p>
                        @endif
                    @else
                        <p class="text-gray-500">No supplier selected.</p>
                    @endif
                </div>
            </div>

            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-[#F3F4F6] dark:bg-[#1A1D23] space-y-3">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Purchase Summary
                </h2>

                <dl class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Subtotal</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            ₹ {{ number_format($purchase->subtotal, 2) }}
                        </dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Discount</dt>
                        <dd class="font-medium text-red-600">
                            ₹ {{ number_format($purchase->discount_amount, 2) }}
                        </dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Total GST</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            ₹ {{ number_format($purchase->cgst_amount + $purchase->sgst_amount + $purchase->igst_amount, 2) }}
                        </dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Round Off</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            ₹ {{ number_format($purchase->round_off, 2) }}
                        </dd>
                    </div>

                    <div class="flex justify-between border-t pt-2 dark:border-gray-700">
                        <dt class="text-gray-700 dark:text-gray-300 font-semibold">Grand Total</dt>
                        <dd class="font-bold text-gray-900 dark:text-white">
                            ₹ {{ number_format($purchase->total_amount, 2) }}
                        </dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Paid</dt>
                        <dd class="font-medium text-emerald-600">
                            ₹ {{ number_format($purchase->paid_amount, 2) }}
                        </dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Due</dt>
                        <dd class="font-medium text-red-600">
                            ₹ {{ number_format($purchase->due_amount, 2) }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-[#F3F4F6] dark:bg-[#1A1D23]">
                <p class="text-sm text-gray-500">Tax Type</p>
                <p class="font-semibold text-gray-900 dark:text-white">
                    {{ $purchase->tax_type === 'inter_state' ? 'Inter State - IGST' : 'Intra State - CGST + SGST' }}
                </p>
            </div>

            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-[#F3F4F6] dark:bg-[#1A1D23]">
                <p class="text-sm text-gray-500">CGST + SGST</p>
                <p class="font-semibold text-gray-900 dark:text-white">
                    ₹ {{ number_format($purchase->cgst_amount + $purchase->sgst_amount, 2) }}
                </p>
            </div>

            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-[#F3F4F6] dark:bg-[#1A1D23]">
                <p class="text-sm text-gray-500">IGST</p>
                <p class="font-semibold text-gray-900 dark:text-white">
                    ₹ {{ number_format($purchase->igst_amount, 2) }}
                </p>
            </div>
        </div>

        {{-- Bill File --}}
        <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-[#F3F4F6] dark:bg-[#1A1D23]">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                Bill File
            </h2>

            @if($purchase->bill_file)
                @php
                    $billUrl = asset('storage/'.$purchase->bill_file);
                    $extension = strtolower(pathinfo($purchase->bill_file, PATHINFO_EXTENSION));
                @endphp

                @if(in_array($extension, ['jpg', 'jpeg', 'png']))
                    <a href="{{ $billUrl }}" target="_blank">
                        <img src="{{ $billUrl }}"
                            alt="Purchase Bill"
                            class="max-h-96 rounded-lg border border-gray-300 dark:border-gray-700">
                    </a>
                @elseif($extension === 'pdf')
                    <iframe src="{{ $billUrl }}"
                            class="w-full h-[500px] rounded-lg border border-gray-300 dark:border-gray-700">
                    </iframe>
                @endif

                <div class="mt-3 flex gap-2">
                    <a href="{{ $billUrl }}"
                    target="_blank"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        View Bill
                    </a>

                    <a href="{{ $billUrl }}"
                    download
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-700 rounded-lg hover:bg-gray-800">
                        Download Bill
                    </a>
                </div>
            @else
                <p class="text-sm text-gray-500">
                    No bill file uploaded.
                </p>
            @endif
        </div>

        <div class="space-y-3">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                Purchase Items
            </h2>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                    <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium tracking-wider">
                        <tr>
                            <th class="px-4 py-3">Item</th>
                            <th class="px-4 py-3 text-right">Qty</th>
                            <th class="px-4 py-3">Unit</th>
                            <th class="px-4 py-3 text-right">Rate</th>
                            <th class="px-4 py-3 text-right">Taxable</th>
                            <th class="px-4 py-3 text-right">GST %</th>
                            <th class="px-4 py-3 text-right">CGST</th>
                            <th class="px-4 py-3 text-right">SGST</th>
                            <th class="px-4 py-3 text-right">IGST</th>
                            <th class="px-4 py-3 text-right">Total</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-[#F3F4F6] dark:bg-[#1A1D23] dark:divide-neutral-700">
                        @forelse($purchase->items as $row)
                            <tr>
                                <td class="px-4 py-3 font-medium">
                                    {{ $row->item->name ?? 'N/A' }}

                                    <div class="text-xs text-gray-500 mt-1">
                                        @if($row->gross_weight)
                                            Gross: {{ $row->gross_weight }}
                                        @endif

                                        @if($row->metal_weight)
                                            | Metal: {{ $row->metal_weight }}
                                        @endif

                                        @if($row->stone_weight)
                                            | Stone: {{ $row->stone_weight }}
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($row->qty, 3) }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ strtoupper($row->qty_unit) }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    ₹ {{ number_format($row->rate, 2) }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    ₹ {{ number_format($row->taxable_amount, 2) }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($row->gst_rate, 2) }}%
                                </td>

                                <td class="px-4 py-3 text-right">
                                    ₹ {{ number_format($row->cgst_amount, 2) }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    ₹ {{ number_format($row->sgst_amount, 2) }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    ₹ {{ number_format($row->igst_amount, 2) }}
                                </td>

                                <td class="px-4 py-3 text-right font-semibold">
                                    ₹ {{ number_format($row->total_amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-4 text-center text-gray-500">
                                    No items found in this purchase.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot class="bg-[#BFE0E0] dark:bg-[#354A54] font-semibold">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right">Total</td>
                            <td class="px-4 py-3 text-right">₹ {{ number_format($purchase->subtotal, 2) }}</td>
                            <td></td>
                            <td class="px-4 py-3 text-right">₹ {{ number_format($purchase->cgst_amount, 2) }}</td>
                            <td class="px-4 py-3 text-right">₹ {{ number_format($purchase->sgst_amount, 2) }}</td>
                            <td class="px-4 py-3 text-right">₹ {{ number_format($purchase->igst_amount, 2) }}</td>
                            <td class="px-4 py-3 text-right">₹ {{ number_format($purchase->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</x-layouts.app>