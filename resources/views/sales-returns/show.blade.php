
<x-layouts.app :title="__('Sales Return Details')">

    @php
        $isDraft = $salesReturn->status === 'draft';
        $isApproved = $salesReturn->status === 'approved';

        $statusClass = match($salesReturn->status) {
            'approved' => 'border-green-200 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300',
            'draft' => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
            'cancelled' => 'border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300',
            default => 'border-gray-200 bg-gray-50 text-gray-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200',
        };
    @endphp

    {{-- Header --}}
    <div class="mb-4 flex flex-col gap-3 bg-[#BFE0E0] p-6 dark:bg-[#354A54] sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Sales Return Details
            </h1>

            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span class="text-sm font-semibold text-gray-700 dark:text-neutral-200">
                    {{ $salesReturn->return_number }}
                </span>

                <span class="rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusClass }}">
                    {{ strtoupper($salesReturn->status) }}
                </span>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('sales-returns.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-200">
                All Returns
            </a>

            <a href="{{ route('invoices.index', ['type' => 'tax']) }}"
               class="rounded-lg bg-[#46837d] px-4 py-2 text-sm font-semibold text-white hover:bg-[#35655f]">
                Invoices
            </a>
        </div>
    </div>

    {{-- Success --}}
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    {{-- Errors --}}
    @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
            <div class="mb-2 font-bold">
                Unable to process sales return:
            </div>

            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Details --}}
    <div class="mb-4 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

        <div class="border-b border-gray-100 bg-gray-50 px-5 py-4 dark:border-neutral-800 dark:bg-neutral-800/60">
            <h2 class="font-bold text-gray-900 dark:text-white">
                Return Information
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Return Number
                </div>

                <div class="mt-1 font-semibold text-gray-900 dark:text-white">
                    {{ $salesReturn->return_number }}
                </div>
            </div>

            
            @if($salesReturn->credit_note_number)
                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                        Credit Note Number
                    </div>

                    <div class="mt-1 font-bold text-[#46837d] dark:text-emerald-300">
                        {{ $salesReturn->credit_note_number }}
                    </div>
                </div>
            @endif


            <div>
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Return Date
                </div>

                <div class="mt-1 font-semibold text-gray-900 dark:text-white">
                    {{ $salesReturn->return_date ? \Illuminate\Support\Carbon::parse($salesReturn->return_date)->format('d M Y') : '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Original Invoice
                </div>

                <div class="mt-1 font-semibold text-gray-900 dark:text-white">
                    {{ $salesReturn->invoice?->invoice_number ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Customer
                </div>

                <div class="mt-1 font-semibold text-gray-900 dark:text-white">
                    {{ $salesReturn->client?->name ?? '-' }}
                </div>

                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    {{ $salesReturn->client?->mobile ?? '' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Created By
                </div>

                <div class="mt-1 font-semibold text-gray-900 dark:text-white">
                    {{ $salesReturn->createdBy?->name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Created At
                </div>

                <div class="mt-1 font-semibold text-gray-900 dark:text-white">
                    {{ $salesReturn->created_at?->format('d M Y, h:i A') ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Refund Status
                </div>

                <div class="mt-1 font-semibold text-gray-900 dark:text-white">
                    {{ ucfirst($salesReturn->refund_status ?? 'pending') }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Return Status
                </div>

                <div class="mt-1">
                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusClass }}">
                        {{ ucfirst($salesReturn->status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Reason --}}
        <div class="border-t border-gray-100 px-5 py-4 dark:border-neutral-800">
            <div class="text-xs font-semibold text-gray-500 dark:text-neutral-400">
                Return Reason
            </div>

            <p class="mt-2 whitespace-pre-line text-sm text-gray-800 dark:text-neutral-200">{{ $salesReturn->reason ?: '-' }}</p>
        </div>
    </div>

    {{-- Items --}}
    <div class="mb-4 overflow-hidden rounded-2xl border border-gray-200 bg-[#BFE0E0] p-4 dark:border-neutral-800 dark:bg-[#354A54] sm:p-6">

        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">
                Returned Items
            </h2>

            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-[#46837d] dark:bg-neutral-800 dark:text-emerald-300">
                {{ $salesReturn->items->count() }} Items
            </span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-neutral-700 dark:bg-neutral-900">
            <table class="min-w-full text-sm">

                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600 dark:bg-neutral-800 dark:text-neutral-300">
                    <tr>
                        <th class="px-4 py-3 text-left">Item</th>
                        <th class="px-4 py-3 text-right">Return Qty</th>
                        <th class="px-4 py-3 text-right">Unit Price</th>
                        <th class="px-4 py-3 text-right">Taxable</th>
                        <th class="px-4 py-3 text-right">GST</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-center">Restock</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                    @forelse($salesReturn->items as $row)
                        @php
                            $gst = (float) $row->cgst_amount
                                 + (float) $row->sgst_amount
                                 + (float) $row->igst_amount;
                        @endphp

                        <tr class="hover:bg-gray-50/60 dark:hover:bg-neutral-800/40">

                            <td class="min-w-48 px-4 py-4">
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    {{ $row->invoiceItem?->description ?: ($row->invoiceItem?->item?->name ?? 'Item #' . $row->invoice_item_id) }}
                                </div>

                                @if($row->reason)
                                    <div class="mt-1 text-xs text-gray-500 dark:text-neutral-400">
                                        {{ $row->reason }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-4 text-right font-medium tabular-nums text-gray-800 dark:text-neutral-200">
                                {{ number_format((float) $row->quantity, 3) }}
                            </td>

                            <td class="px-4 py-4 text-right tabular-nums text-gray-700 dark:text-neutral-200">
                                ₹ {{ number_format((float) $row->unit_price, 2) }}
                            </td>

                            <td class="px-4 py-4 text-right tabular-nums text-gray-700 dark:text-neutral-200">
                                ₹ {{ number_format((float) $row->taxable_amount, 2) }}
                            </td>

                            <td class="px-4 py-4 text-right tabular-nums text-gray-700 dark:text-neutral-200">
                                ₹ {{ number_format($gst, 2) }}
                            </td>

                            <td class="px-4 py-4 text-right font-semibold tabular-nums text-gray-900 dark:text-white">
                                ₹ {{ number_format((float) $row->total_amount, 2) }}
                            </td>

                            <td class="px-4 py-4 text-center">
                                @if($row->restock)
                                    <span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-300">
                                        Yes
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-neutral-800 dark:text-neutral-300">
                                        No
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500 dark:text-neutral-400">
                                No return items found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Amount Summary --}}
    <div class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-3">

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900 lg:col-span-2">
            <h2 class="mb-4 text-base font-bold text-gray-900 dark:text-white">
                Tax Summary
            </h2>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between gap-3 border-b border-gray-100 pb-2 dark:border-neutral-800">
                    <span class="text-gray-600 dark:text-neutral-300">
                        Taxable Amount
                    </span>
                    <span class="font-semibold tabular-nums text-gray-900 dark:text-white">
                        ₹ {{ number_format((float) $salesReturn->taxable_amount, 2) }}
                    </span>
                </div>

                <div class="flex justify-between gap-3 border-b border-gray-100 pb-2 dark:border-neutral-800">
                    <span class="text-gray-600 dark:text-neutral-300">
                        CGST
                    </span>
                    <span class="font-semibold tabular-nums text-gray-900 dark:text-white">
                        ₹ {{ number_format((float) $salesReturn->cgst_amount, 2) }}
                    </span>
                </div>

                <div class="flex justify-between gap-3 border-b border-gray-100 pb-2 dark:border-neutral-800">
                    <span class="text-gray-600 dark:text-neutral-300">
                        SGST
                    </span>
                    <span class="font-semibold tabular-nums text-gray-900 dark:text-white">
                        ₹ {{ number_format((float) $salesReturn->sgst_amount, 2) }}
                    </span>
                </div>

                <div class="flex justify-between gap-3">
                    <span class="text-gray-600 dark:text-neutral-300">
                        IGST
                    </span>
                    <span class="font-semibold tabular-nums text-gray-900 dark:text-white">
                        ₹ {{ number_format((float) $salesReturn->igst_amount, 2) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm dark:border-emerald-900 dark:bg-emerald-950/30">
            <div class="text-xs font-bold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                Total Return Value
            </div>

            <div class="mt-3 text-3xl font-bold tabular-nums text-emerald-800 dark:text-emerald-200">
                ₹ {{ number_format((float) $salesReturn->total_amount, 2) }}
            </div>

            <div class="mt-4 border-t border-emerald-200 pt-3 text-xs text-emerald-700 dark:border-emerald-900 dark:text-emerald-300">
                Return Status:
                <strong>{{ ucfirst($salesReturn->status) }}</strong>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">
                    Return Actions
                </h2>

                <p class="mt-1 text-xs text-gray-500 dark:text-neutral-400">
                    Review the return details before taking any action.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('sales-returns.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-200">
                    Back to List
                </a>

                
                @if(
                    $salesReturn->status === 'approved'
                    && filled($salesReturn->credit_note_number)
                )
                    <a
                        href="{{ route('sales-returns.credit-note', $salesReturn->id) }}"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
                    >
                        Download Credit Note PDF
                    </a>
                @endif


                @if($isDraft && auth()->user()->can('create invoice'))
                    <form
                        action="{{ route('sales-returns.approve', $salesReturn->id) }}"
                        method="POST"
                        onsubmit="return confirm('Are you sure you want to approve this Sales Return? Selected stock will be restored.');"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-[#46837d] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#35655f]"
                        >
                            Approve Sales Return
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if($isDraft)
            <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300">
                <strong>Draft Return:</strong>
                This return has not been approved. Stock has not been restored, no refund has been processed, and no final credit note has been issued.
            </div>
        @elseif($isApproved)
            <div class="mt-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-900 dark:bg-green-950/30 dark:text-green-300">
                <strong>Approved Return:</strong>
                This return has been approved. Restock-enabled items should have their stock movements recorded. Refund settlement and credit note issuance must be completed separately.
            </div>
        @endif
    </div>

</x-layouts.app>
