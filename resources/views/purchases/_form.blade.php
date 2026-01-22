@php
    $isEdit = $purchase->exists;

    $oldItems = old(
        'items',
        $isEdit ? $purchase->items->toArray() : [['item_id' => null, 'qty' => 1, 'rate' => 0, 'amount' => 0]],
    );
@endphp

<div class=" max-w-3xl mx-auto bg-[#BFE0E0] dark:bg-[#354A54] p-6 text-center text-xl font-bold my-2">
    Create Purchase
</div>

<div class="space-y-6 text-gray-900 dark:text-neutral-100 max-w-3xl mx-auto p-6 bg-[#F3F4F6] dark:bg-[#1A1D23]">

    {{-- TOP SECTION: Supplier + Invoice No + Date --}}
    <div class="grid md:grid-cols-3 gap-4">
        {{-- Supplier --}}
        <div>
            <label class="block text-sm font-medium mb-1">Supplier</label>
            <select name="supplier_id"
                class="w-full border rounded px-3 py-2
                           bg-white      {{-- dropdown hamesha white --}}
                           text-gray-900 {{-- text hamesha dark --}}
                           border-gray-300 dark:border-neutral-600">
                <option value="">Select supplier...</option>
                @foreach ($suppliers as $sup)
                    <option value="{{ $sup->id }}" @selected(old('supplier_id', $purchase->supplier_id) == $sup->id)>
                        {{ $sup->name }}
                    </option>
                @endforeach
            </select>
            @error('supplier_id')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Invoice no --}}
        <div>
            <label class="block text-sm font-medium mb-1">Purchase Invoice #</label>
            <input type="text" name="invoice_no"
                class="w-full border rounded px-3 py-2
                          bg-transparent dark:bg-neutral-900
                          text-gray-900 dark:text-neutral-100
                          border-gray-300 dark:border-neutral-600"
                value="{{ old('invoice_no', $purchase->invoice_no) }}">
            @error('invoice_no')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Date --}}
        <div>
            <label class="block text-sm font-medium mb-1">
                Purchase Date <span class="text-red-600">*</span>
            </label>
            <input type="date" name="invoice_date"
                class="w-full border rounded px-3 py-2
                          bg-transparent dark:bg-neutral-900
                          text-gray-900 dark:text-neutral-100
                          border-gray-300 dark:border-neutral-600"
                value="{{ old('invoice_date', optional($purchase->invoice_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                required>
            @error('invoice_date')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- ITEMS TABLE --}}
    <div class="border rounded-lg overflow-hidden border-gray-200 dark:border-neutral-700 ">
        <div class="flex items-center justify-between px-3 py-2 bg-gray-100 dark:bg-neutral-800">
            <h3 class="font-semibold text-sm text-gray-900 dark:text-neutral-100">Items</h3>
            <button type="button" id="purchase-add-row"
                class="text-xs px-2 py-1 border rounded
                           bg-sky-400 dark:bg-sky-900
                           text-gray-900 dark:text-neutral-100
                           border-gray-300 dark:border-neutral-600
                           hover:bg-sky-500 dark:hover:bg-sky-800">
                + Add Item
            </button>
        </div>

        <table class="min-w-full text-xs">
            <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-gray-700 dark:text-neutral-200">
                <tr class="[&>th]:px-3 [&>th]:py-2 text-left">
                    <th style="width: 30%">Item</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Amount</th>
                    <th></th>
                </tr>
            </thead>

            <tbody id="purchase-items-body" class="divide-y divide-gray-200 dark:divide-neutral-800">
                @foreach ($oldItems as $i => $row)
                    <tr class="purchase-item-row">
                        {{-- Item select --}}
                        <td class="px-3 py-1">
                            <select name="items[{{ $i }}][item_id]"
                                class="w-full border rounded px-2 py-1
                                       bg-white      {{-- dropdown white --}}
                                       text-gray-900 {{-- text dark --}}
                                       border-gray-300 dark:border-neutral-600"
                                required>
                                <option value="">Select item...</option>
                                @foreach ($items as $it)
                                    <option value="{{ $it->id }}" @selected(($row['item_id'] ?? null) == $it->id)>
                                        {{ $it->name }} @if ($it->sku)
                                            ({{ $it->sku }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('items.' . $i . '.item_id')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </td>

                        {{-- Qty --}}
                        <td class="px-3 py-1">
                            <input type="number" min="1" name="items[{{ $i }}][qty]"
                                class="w-20 border rounded px-2 py-1
                                      purchase-qty-input
                                      bg-transparent dark:bg-neutral-900
                                      text-gray-900 dark:text-neutral-100
                                      border-gray-300 dark:border-neutral-600"
                                value="{{ $row['qty'] ?? 1 }}" required>
                            @error('items.' . $i . '.qty')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </td>

                        {{-- Rate --}}
                        <td class="px-3 py-1">
                            <input type="number" step="0.01" min="0" name="items[{{ $i }}][rate]"
                                class="w-24 border rounded px-2 py-1
                                      purchase-rate-input
                                      bg-transparent dark:bg-neutral-900
                                      text-gray-900 dark:text-neutral-100
                                      border-gray-300 dark:border-neutral-600"
                                value="{{ $row['rate'] ?? 0 }}" required>
                            @error('items.' . $i . '.rate')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </td>

                        {{-- Amount --}}
                        <td class="px-3 py-1">
                            <input type="number" step="0.01" min="0"
                                name="items[{{ $i }}][amount]"
                                class="w-28 border rounded px-2 py-1
                                      purchase-amount-input
                                      bg-transparent dark:bg-neutral-900
                                      text-gray-900 dark:text-neutral-100
                                      border-gray-300 dark:border-neutral-600"
                                value="{{ $row['amount'] ?? 0 }}" required>
                            @error('items.' . $i . '.amount')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </td>

                        {{-- Remove --}}
                        <td class="px-3 py-1 text-right">
                            <button type="button" class="text-red-600 text-xs purchase-remove-row">
                                Remove
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- TOTAL SUMMARY --}}
    <div class="grid md:grid-cols-3 gap-4 max-w-3xl mx-auto ">
        <div class="md:col-span-2"></div>

        <div class="space-y-2">
            <div>
                <label class="block text-sm font-medium mb-1">Total Amount</label>
                <input type="number" step="0.01" name="total_amount"
                    class="w-full border rounded px-3 py-2
                              bg-gray-100 dark:bg-neutral-800
                              text-gray-900 dark:text-neutral-100
                              border-gray-300 dark:border-neutral-600"
                    x-purchase-total-input value="{{ old('total_amount', $purchase->total_amount ?? 0) }}" readonly>
            </div>
        </div>
    </div>
</div>

{{-- JS for dynamic rows + auto total --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const body = document.getElementById('purchase-items-body');
        const addBtn = document.getElementById('purchase-add-row');

        function recalcPurchaseTotals() {
            let total = 0;
            body.querySelectorAll('.purchase-item-row').forEach(function(row) {
                const qty = parseFloat(row.querySelector('.purchase-qty-input').value || 0);
                const rate = parseFloat(row.querySelector('.purchase-rate-input').value || 0);
                const amountInput = row.querySelector('.purchase-amount-input');
                const amount = qty * rate;
                amountInput.value = amount.toFixed(2);
                total += amount;
            });
            const totalInput = document.querySelector('input[x-purchase-total-input]');
            if (totalInput) {
                totalInput.value = total.toFixed(2);
            }
        }

        body.addEventListener('input', function(e) {
            if (e.target.classList.contains('purchase-qty-input') ||
                e.target.classList.contains('purchase-rate-input')) {
                recalcPurchaseTotals();
            }
        });

        body.addEventListener('click', function(e) {
            if (e.target.classList.contains('purchase-remove-row')) {
                const row = e.target.closest('.purchase-item-row');
                row.remove();
                recalcPurchaseTotals();
            }
        });

        addBtn.addEventListener('click', function() {
            const index = body.querySelectorAll('.purchase-item-row').length;
            const template = `
            <tr class="purchase-item-row">
                <td class="px-3 py-1">
                    <select name="items[${index}][item_id]"
                            class="w-full border rounded px-2 py-1
                                   bg-white
                                   text-gray-900
                                   border-gray-300 dark:border-neutral-600"
                            required>
                        <option value="">Select item...</option>
                        @foreach ($items as $it)
            <option value="{{ $it->id }}">
                                {{ $it->name }} @if ($it->sku) ({{ $it->sku }}) @endif
            </option>
@endforeach
            </select>
        </td>
        <td class="px-3 py-1">
            <input type="number" min="1"
                   name="items[${index}][qty]"
                           class="w-20 border rounded px-2 py-1
                                  purchase-qty-input
                                  bg-transparent dark:bg-neutral-900
                                  text-gray-900 dark:text-neutral-100
                                  border-gray-300 dark:border-neutral-600"
                           value="1" required>
                </td>
                <td class="px-3 py-1">
                    <input type="number" step="0.01" min="0"
                           name="items[${index}][rate]"
                           class="w-24 border rounded px-2 py-1
                                  purchase-rate-input
                                  bg-transparent dark:bg-neutral-900
                                  text-gray-900 dark:text-neutral-100
                                  border-gray-300 dark:border-neutral-600"
                           value="0" required>
                </td>
                <td class="px-3 py-1">
                    <input type="number" step="0.01" min="0"
                           name="items[${index}][amount]"
                           class="w-28 border rounded px-2 py-1
                                  purchase-amount-input
                                  bg-transparent dark:bg-neutral-900
                                  text-gray-900 dark:text-neutral-100
                                  border-gray-300 dark:border-neutral-600"
                           value="0" required>
                </td>
                <td class="px-3 py-1 text-right">
                    <button type="button"
                            class="text-red-600 text-xs purchase-remove-row">
                        Remove
                    </button>
                </td>
            </tr>
        `;
            body.insertAdjacentHTML('beforeend', template);
            recalcPurchaseTotals();
        });

        recalcPurchaseTotals();
    });
</script>
