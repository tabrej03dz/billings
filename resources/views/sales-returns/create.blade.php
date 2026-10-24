
<x-layouts.app :title="__('Create Sales Return')">

    @php
        $oldItems = old('items', []);
    @endphp

    {{-- Header --}}
    <div class="mb-4 flex flex-col gap-3 bg-[#BFE0E0] p-6 dark:bg-[#354A54] sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Create Sales Return
            </h1>
            <p class="mt-1 text-xs text-gray-600 dark:text-neutral-300">
                Select items from the original invoice to create a return draft.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('sales-returns.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-200">
                All Returns
            </a>

            <a href="{{ route('invoices.index', ['type' => 'tax']) }}"
               class="rounded-lg bg-[#46837d] px-4 py-2 text-sm font-semibold text-white hover:bg-[#35655f]">
                Back to Invoices
            </a>
        </div>
    </div>

    {{-- Validation --}}
    @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
            <div class="mb-2 font-bold">Please correct these errors:</div>
            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Original Invoice --}}
    <div class="mb-4 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

        <div class="border-b border-gray-100 bg-gray-50 px-5 py-4 dark:border-neutral-800 dark:bg-neutral-800/60">
            <h2 class="font-bold text-gray-900 dark:text-white">
                Original Invoice Details
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Invoice Number
                </div>
                <div class="mt-1 font-semibold text-gray-900 dark:text-white">
                    {{ $invoice->invoice_number }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Invoice Date
                </div>
                <div class="mt-1 font-semibold text-gray-900 dark:text-white">
                    {{ $invoice->invoice_date?->format('d M Y') ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Customer
                </div>
                <div class="mt-1 font-semibold text-gray-900 dark:text-white">
                    {{ $invoice->client?->name ?? '-' }}
                </div>
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    {{ $invoice->client?->mobile ?? '' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Original Invoice Total
                </div>
                <div class="mt-1 text-xl font-bold text-[#46837d] dark:text-emerald-300">
                    ₹ {{ number_format((float) $invoice->total, 2) }}
                </div>
            </div>
        </div>
    </div>

    <form
        id="returnForm"
        action="{{ route('sales-returns.store', $invoice->id) }}"
        method="POST"
    >
        @csrf

        {{-- Return Information --}}
        <div class="mb-4 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="border-b border-gray-100 bg-gray-50 px-5 py-4 dark:border-neutral-800 dark:bg-neutral-800/60">
                <h2 class="font-bold text-gray-900 dark:text-white">
                    Return Information
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">
                <div>
                    <label for="return_date"
                           class="mb-1 block text-xs font-semibold text-gray-600 dark:text-neutral-300">
                        Return Date *
                    </label>

                    <input
                        id="return_date"
                        type="date"
                        name="return_date"
                        value="{{ old('return_date', date('Y-m-d')) }}"
                        required
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-[#46837d] focus:ring-[#46837d] dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                    >
                </div>

                <div class="md:col-span-2">
                    <label for="reason"
                           class="mb-1 block text-xs font-semibold text-gray-600 dark:text-neutral-300">
                        Return Reason *
                    </label>

                    <textarea
                        id="reason"
                        name="reason"
                        rows="2"
                        maxlength="2000"
                        required
                        placeholder="Enter reason for returning the items..."
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-[#46837d] focus:ring-[#46837d] dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                    >{{ old('reason') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Item Selection --}}
        <div class="mb-4 overflow-hidden rounded-2xl border border-gray-200 bg-[#BFE0E0] p-4 dark:border-neutral-800 dark:bg-[#354A54] sm:p-6">

            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">
                        Select Items to Return
                    </h2>
                    <p class="mt-1 text-xs text-gray-600 dark:text-neutral-300">
                        Only select the products the customer has returned.
                    </p>
                </div>

                <span id="selectedCount"
                      class="rounded-full bg-white px-3 py-1 text-xs font-bold text-[#46837d] dark:bg-neutral-800 dark:text-emerald-300">
                    0 Items Selected
                </span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-neutral-700 dark:bg-neutral-900">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600 dark:bg-neutral-800 dark:text-neutral-300">
                        <tr>
                            <th class="px-4 py-3 text-center">Select</th>
                            <th class="px-4 py-3 text-left">Item</th>
                            <th class="px-4 py-3 text-right">Sold Qty</th>
                            <th class="px-4 py-3 text-right">Available Qty</th>
                            <th class="px-4 py-3 text-left">Return Qty</th>
                            <th class="px-4 py-3 text-right">Original Amount</th>
                            <th class="px-4 py-3 text-center">Restock</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                        @forelse($invoice->items as $index => $item)
                            @php
                                $available = max(
                                    0,
                                    round((float) ($item->returnable_quantity ?? 0), 3)
                                );

                                $oldRow = $oldItems[$index] ?? [];
                                $isSelected = $available > 0
                                    && array_key_exists('invoice_item_id', $oldRow);
                            @endphp

                            <tr class="return-row hover:bg-gray-50/60 dark:hover:bg-neutral-800/40"
                                data-index="{{ $index }}">

                                <td class="px-4 py-4 text-center">
                                    <input
                                        type="checkbox"
                                        class="item-checkbox h-4 w-4 rounded border-gray-300 text-[#46837d] focus:ring-[#46837d]"
                                        data-index="{{ $index }}"
                                        @checked($isSelected)
                                        @disabled($available <= 0)
                                    >
                                </td>

                                <td class="min-w-48 px-4 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        {{ $item->description ?: ($item->item?->name ?? 'Item #' . $item->id) }}
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500 dark:text-neutral-400">
                                        Invoice Item #{{ $item->id }}
                                    </div>

                                    @if($available <= 0)
                                        <span class="mt-2 inline-flex rounded-full bg-red-50 px-2 py-1 text-[11px] font-semibold text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                            Fully Returned
                                        </span>
                                    @endif

                                    <input
                                        type="hidden"
                                        name="items[{{ $index }}][invoice_item_id]"
                                        value="{{ $item->id }}"
                                        class="item-field-{{ $index }}"
                                        @disabled(!$isSelected)
                                    >
                                </td>

                                <td class="px-4 py-4 text-right tabular-nums text-gray-700 dark:text-neutral-200">
                                    {{ number_format((float) $item->quantity, 3) }}
                                </td>

                                <td class="px-4 py-4 text-right font-semibold tabular-nums text-emerald-700 dark:text-emerald-300">
                                    {{ number_format($available, 3) }}
                                </td>

                                <td class="min-w-40 px-4 py-4">
                                    <input
                                        type="number"
                                        name="items[{{ $index }}][quantity]"
                                        value="{{ $isSelected ? ($oldRow['quantity'] ?? '') : '' }}"
                                        min="0.001"
                                        max="{{ $available }}"
                                        step="0.001"
                                        placeholder="0.000"
                                        data-amount="{{ (float) $item->amount }}"
                                        data-sold-qty="{{ (float) $item->quantity }}"
                                        data-available="{{ $available }}"
                                        class="return-qty item-field-{{ $index }} w-full rounded-lg border-gray-300 text-sm focus:border-[#46837d] focus:ring-[#46837d] dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                        @disabled(!$isSelected)
                                    >
                                </td>

                                <td class="px-4 py-4 text-right font-medium tabular-nums text-gray-900 dark:text-white">
                                    ₹ {{ number_format((float) $item->amount, 2) }}
                                </td>

                                <td class="px-4 py-4 text-center">
                                    <input
                                        type="hidden"
                                        name="items[{{ $index }}][restock]"
                                        value="0"
                                        class="item-field-{{ $index }}"
                                        @disabled(!$isSelected)
                                    >

                                    <input
                                        type="checkbox"
                                        name="items[{{ $index }}][restock]"
                                        value="1"
                                        class="item-field-{{ $index }} h-4 w-4 rounded border-gray-300 text-[#46837d] focus:ring-[#46837d]"
                                        @checked($isSelected && (string) ($oldRow['restock'] ?? '0') === '1')
                                        @disabled(!$isSelected)
                                    >
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-gray-500 dark:text-neutral-400">
                                    No invoice items found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-3 text-xs leading-6 text-blue-800 dark:border-blue-900 dark:bg-blue-950/30 dark:text-blue-300">
                <strong>Restock:</strong> Select this only when the returned product is physically received and can be sold again. Uncheck it for damaged or non-reusable items.
            </div>
        </div>

        {{-- Summary --}}
        <div class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-3">

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900 lg:col-span-2">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">
                    Return Summary
                </h3>

                <p class="mt-2 text-sm text-gray-600 dark:text-neutral-300">
                    Please verify the selected items and quantities before saving the return draft.
                </p>

                <p class="mt-3 text-xs leading-5 text-amber-700 dark:text-amber-300">
                    Saving a draft does not restore stock, issue a credit note or make a refund.
                </p>
            </div>

            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm dark:border-emerald-900 dark:bg-emerald-950/30">
                <div class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                    Estimated Return Amount
                </div>

                <div id="returnTotal"
                     class="mt-2 text-3xl font-bold tabular-nums text-emerald-800 dark:text-emerald-200">
                    ₹ 0.00
                </div>

                <p class="mt-2 text-xs text-emerald-700 dark:text-emerald-300">
                    Final amount is calculated on the server.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 pb-6">
            <a href="{{ route('sales-returns.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                Cancel
            </a>

            <button
                id="submitButton"
                type="submit"
                class="rounded-lg bg-[#46837d] px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#35655f] disabled:cursor-not-allowed disabled:opacity-50"
            >
                Save Return Draft
            </button>
        </div>

    </form>

    <script>
        (function () {
            function initializeReturnForm() {
                const form = document.getElementById('returnForm');

                if (!form || form.dataset.initialized === '1') {
                    return;
                }

                form.dataset.initialized = '1';

                const checkboxes = form.querySelectorAll('.item-checkbox');
                const totalElement = document.getElementById('returnTotal');
                const countElement = document.getElementById('selectedCount');
                const submitButton = document.getElementById('submitButton');

                function updateSummary() {
                    let total = 0;
                    let count = 0;

                    checkboxes.forEach(function (checkbox) {
                        if (!checkbox.checked || checkbox.disabled) {
                            return;
                        }

                        count++;

                        const index = checkbox.dataset.index;
                        const qtyInput = form.querySelector(
                            '.return-qty.item-field-' + index
                        );

                        if (!qtyInput) {
                            return;
                        }

                        const qty = Number(qtyInput.value || 0);
                        const soldQty = Number(qtyInput.dataset.soldQty || 0);
                        const available = Number(qtyInput.dataset.available || 0);
                        const amount = Number(qtyInput.dataset.amount || 0);

                        if (qty > 0 && qty <= available && soldQty > 0) {
                            total += (amount / soldQty) * qty;
                        }
                    });

                    totalElement.textContent = '₹ ' + total.toLocaleString(
                        'en-IN',
                        {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }
                    );

                    countElement.textContent = count + ' Items Selected';

                    submitButton.disabled = count === 0;
                }

                function toggleRow(checkbox) {
                    const index = checkbox.dataset.index;
                    const selected = checkbox.checked && !checkbox.disabled;

                    form.querySelectorAll('.item-field-' + index)
                        .forEach(function (field) {
                            field.disabled = !selected;
                        });

                    const qtyInput = form.querySelector(
                        '.return-qty.item-field-' + index
                    );

                    if (qtyInput) {
                        if (!selected) {
                            qtyInput.value = '';
                            qtyInput.required = false;
                        } else {
                            qtyInput.required = true;
                        }
                    }

                    updateSummary();
                }

                checkboxes.forEach(function (checkbox) {
                    checkbox.addEventListener('change', function () {
                        toggleRow(checkbox);
                    });

                    toggleRow(checkbox);
                });

                form.querySelectorAll('.return-qty').forEach(function (input) {
                    input.addEventListener('input', updateSummary);
                });

                form.addEventListener('submit', function (event) {
                    let selected = 0;
                    let invalid = false;

                    checkboxes.forEach(function (checkbox) {
                        if (!checkbox.checked || checkbox.disabled) {
                            return;
                        }

                        selected++;

                        const index = checkbox.dataset.index;
                        const qtyInput = form.querySelector(
                            '.return-qty.item-field-' + index
                        );

                        const qty = Number(qtyInput.value);
                        const available = Number(qtyInput.dataset.available);

                        if (
                            !Number.isFinite(qty) ||
                            qty <= 0 ||
                            qty > available ||
                            Math.abs(qty * 1000 - Math.round(qty * 1000)) > 0.000001
                        ) {
                            invalid = true;
                        }
                    });

                    if (selected === 0) {
                        event.preventDefault();
                        alert('Please select at least one item.');
                        return;
                    }

                    if (invalid) {
                        event.preventDefault();
                        alert('Return quantity cannot exceed available quantity and must have at most 3 decimal places.');
                    }
                });

                updateSummary();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeReturnForm);
            } else {
                initializeReturnForm();
            }

            document.addEventListener('livewire:navigated', initializeReturnForm);
        })();
    </script>

</x-layouts.app>
