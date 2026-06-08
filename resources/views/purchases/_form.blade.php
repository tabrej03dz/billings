@php
    $isEdit = $purchase->exists;

    $oldItems = old(
        'items',
        $isEdit
            ? $purchase->items->toArray()
            : [['item_id' => null, 'qty' => 1, 'rate' => 0, 'gst_rate' => 3]]
    );
@endphp

<div class="max-w-6xl mx-auto bg-[#BFE0E0] dark:bg-[#354A54] p-6 text-center text-xl font-bold my-2">
    {{ $isEdit ? 'Edit Purchase' : 'Create Purchase' }}
</div>

<div class="space-y-6 text-gray-900 dark:text-neutral-100 max-w-6xl mx-auto p-6 bg-[#F3F4F6] dark:bg-[#1A1D23]">

    <div class="grid md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Supplier</label>
            <select name="supplier_id" class="w-full border rounded px-3 py-2 bg-white text-gray-900 border-gray-300">
                <option value="">Select supplier...</option>
                @foreach ($suppliers as $sup)
                    <option value="{{ $sup->id }}" @selected(old('supplier_id', $purchase->supplier_id) == $sup->id)>
                        {{ $sup->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Invoice #</label>
            <input type="text" name="invoice_no"
                   class="w-full border rounded px-3 py-2 bg-white text-gray-900 border-gray-300"
                   value="{{ old('invoice_no', $purchase->invoice_no) }}">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Purchase Date</label>
            <input type="date" name="invoice_date"
                   class="w-full border rounded px-3 py-2 bg-white text-gray-900 border-gray-300"
                   value="{{ old('invoice_date', optional($purchase->invoice_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                   required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Upload Bill</label>
            <input type="file" name="bill_file" accept=".jpg,.jpeg,.png,.pdf"
                   class="w-full border rounded px-3 py-2 bg-white text-gray-900 border-gray-300">

            @if (!empty($purchase->bill_file))
                <a href="{{ asset('storage/' . $purchase->bill_file) }}"
                   target="_blank"
                   class="inline-block mt-1 text-xs text-blue-600 underline">
                    View Uploaded Bill
                </a>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Tax Type</label>
            <select name="tax_type" id="purchase-tax-type"
                    class="w-full border rounded px-3 py-2 bg-white text-gray-900 border-gray-300">
                <option value="intra_state" @selected(old('tax_type', $purchase->tax_type ?? 'intra_state') == 'intra_state')>
                    Intra State - CGST + SGST
                </option>
                <option value="inter_state" @selected(old('tax_type', $purchase->tax_type ?? '') == 'inter_state')>
                    Inter State - IGST
                </option>
            </select>
        </div>
    </div>

    <div class="border rounded-lg overflow-x-auto border-gray-200 dark:border-neutral-700">
        <div class="flex items-center justify-between px-3 py-2 bg-gray-100 dark:bg-neutral-800">
            <h3 class="font-semibold text-sm">Items</h3>
            <button type="button" id="purchase-add-row"
                    class="text-xs px-2 py-1 border rounded bg-sky-400 hover:bg-sky-500">
                + Add Item
            </button>
        </div>

        <table class="w-max min-w-full text-xs">
            <thead class="bg-[#BFE0E0] dark:bg-[#354A54]">
                <tr class="[&>th]:px-3 [&>th]:py-2 text-left">
                    <th class="w-[380px] min-w-[380px]">Item</th>
                    <th class="w-[90px] min-w-[90px]">Qty</th>
                    <th class="w-[120px] min-w-[120px]">Unit</th>
                    <th class="w-[110px] min-w-[110px]">Rate</th>
                    <th class="w-[130px] min-w-[130px]">Taxable</th>
                    <th class="w-[90px] min-w-[90px]">GST %</th>
                    <th class="w-[110px] min-w-[110px]">CGST</th>
                    <th class="w-[110px] min-w-[110px]">SGST</th>
                    <th class="w-[110px] min-w-[110px]">IGST</th>
                    <th class="w-[130px] min-w-[130px]">Total</th>
                    <th class="w-[80px] min-w-[80px]"></th>
                </tr>
            </thead>

            <tbody id="purchase-items-body" class="divide-y divide-gray-200">
                @foreach ($oldItems as $i => $row)
                    <tr class="purchase-item-row">
                        <td class="px-3 py-1 w-[380px] min-w-[380px]">
                            <select name="items[{{ $i }}][item_id]"
                                    class="w-full min-w-[350px] border rounded px-2 py-1 bg-white text-gray-900"
                                    required>
                                <option value="">Select item...</option>
                                @foreach ($items as $it)
                                    <option value="{{ $it->id }}" @selected(($row['item_id'] ?? null) == $it->id)>
                                        {{ $it->name }} @if ($it->sku) ({{ $it->sku }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td class="px-3 py-1">
                            <input type="number" min="1" name="items[{{ $i }}][qty]"
                                   class="w-20 border rounded px-2 py-1 purchase-qty-input"
                                   value="{{ $row['qty'] ?? 1 }}" required>
                        </td>

                        <td class="px-3 py-1">
                            <select name="items[{{ $i }}][qty_unit]"
                                    class="w-28 border rounded px-2 py-1 bg-white text-gray-900"
                                    required>

                                <option value="pcs"
                                    @selected(($row['qty_unit'] ?? 'pcs') == 'pcs')>
                                    Pcs
                                </option>

                                <option value="gram"
                                    @selected(($row['qty_unit'] ?? '') == 'gram')>
                                    Gram
                                </option>

                                <option value="kg"
                                    @selected(($row['qty_unit'] ?? '') == 'kg')>
                                    Kg
                                </option>

                                <option value="carat"
                                    @selected(($row['qty_unit'] ?? '') == 'carat')>
                                    Carat
                                </option>
                            </select>
                        </td>

                        <td class="px-3 py-1">
                            <input type="number" step="0.01" min="0" name="items[{{ $i }}][rate]"
                                   class="w-24 border rounded px-2 py-1 purchase-rate-input"
                                   value="{{ $row['rate'] ?? 0 }}" required>
                        </td>

                        <td class="px-3 py-1">
                            <input type="number" step="0.01" name="items[{{ $i }}][amount]"
                                   class="w-28 border rounded px-2 py-1 purchase-amount-input bg-gray-100"
                                   value="{{ $row['amount'] ?? 0 }}" readonly>
                        </td>

                        <td class="px-3 py-1">
                            <input type="number" step="0.01" min="0" name="items[{{ $i }}][gst_rate]"
                                   class="w-20 border rounded px-2 py-1 purchase-gst-input"
                                   value="{{ $row['gst_rate'] ?? 3 }}">
                        </td>

                        <td class="px-3 py-1">
                            <input type="number" step="0.01"
                                   class="w-24 border rounded px-2 py-1 purchase-cgst-input bg-gray-100"
                                   value="{{ $row['cgst_amount'] ?? 0 }}" readonly>
                        </td>

                        <td class="px-3 py-1">
                            <input type="number" step="0.01"
                                   class="w-24 border rounded px-2 py-1 purchase-sgst-input bg-gray-100"
                                   value="{{ $row['sgst_amount'] ?? 0 }}" readonly>
                        </td>

                        <td class="px-3 py-1">
                            <input type="number" step="0.01"
                                   class="w-24 border rounded px-2 py-1 purchase-igst-input bg-gray-100"
                                   value="{{ $row['igst_amount'] ?? 0 }}" readonly>
                        </td>

                        <td class="px-3 py-1">
                            <input type="number" step="0.01"
                                   class="w-28 border rounded px-2 py-1 purchase-line-total-input bg-gray-100"
                                   value="{{ $row['total_amount'] ?? 0 }}" readonly>
                        </td>

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
    <div class="border rounded-lg border-gray-200 dark:border-neutral-700 overflow-hidden">
        <div class="px-4 py-3 bg-gray-100 dark:bg-neutral-800">
            <h3 class="font-semibold text-sm">Purchase Summary</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-white dark:bg-neutral-900">
            <div>
                <label class="block text-sm font-medium mb-1">Subtotal</label>
                <input type="number" step="0.01" name="subtotal" id="purchase-subtotal"
                    class="w-full border rounded px-3 py-2 bg-gray-100 font-semibold" readonly
                    value="{{ old('subtotal', $purchase->subtotal ?? 0) }}">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">CGST</label>
                <input type="number" step="0.01" id="purchase-cgst-total"
                    class="w-full border rounded px-3 py-2 bg-gray-100" readonly
                    value="{{ old('cgst_amount', $purchase->cgst_amount ?? 0) }}">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">SGST</label>
                <input type="number" step="0.01" id="purchase-sgst-total"
                    class="w-full border rounded px-3 py-2 bg-gray-100" readonly
                    value="{{ old('sgst_amount', $purchase->sgst_amount ?? 0) }}">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">IGST</label>
                <input type="number" step="0.01" id="purchase-igst-total"
                    class="w-full border rounded px-3 py-2 bg-gray-100" readonly
                    value="{{ old('igst_amount', $purchase->igst_amount ?? 0) }}">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Discount</label>
                <input type="number" step="0.01" min="0" name="discount_amount" id="purchase-discount"
                    class="w-full border rounded px-3 py-2 bg-white"
                    value="{{ old('discount_amount', $purchase->discount_amount ?? 0) }}">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Round Off</label>
                <input type="number" step="0.01" name="round_off" id="purchase-round-off"
                    class="w-full border rounded px-3 py-2 bg-white"
                    value="{{ old('round_off', $purchase->round_off ?? 0) }}">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Paid Amount</label>
                <input type="number" step="0.01" min="0" name="paid_amount" id="purchase-paid"
                    class="w-full border rounded px-3 py-2 bg-white"
                    value="{{ old('paid_amount', $purchase->paid_amount ?? 0) }}">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Due Amount</label>
                <input type="number" step="0.01" id="purchase-due"
                    class="w-full border rounded px-3 py-2 bg-red-50 font-bold text-red-700" readonly
                    value="{{ old('due_amount', $purchase->due_amount ?? 0) }}">
            </div>

            <div class="md:col-span-4">
                <label class="block text-sm font-medium mb-1">Grand Total</label>
                <input type="number" step="0.01" name="total_amount" id="purchase-grand-total"
                    class="w-full border rounded px-4 py-3 bg-green-50 text-green-700 text-xl font-bold" readonly
                    value="{{ old('total_amount', $purchase->total_amount ?? 0) }}">
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const body = document.getElementById('purchase-items-body');
        const addBtn = document.getElementById('purchase-add-row');
        const taxTypeInput = document.getElementById('purchase-tax-type');

        function numberValue(selector) {
            const el = document.querySelector(selector);
            return parseFloat(el?.value || 0);
        }

        function recalcPurchaseTotals() {
            let subtotal = 0;
            let cgstTotal = 0;
            let sgstTotal = 0;
            let igstTotal = 0;

            const taxType = taxTypeInput.value;

            body.querySelectorAll('.purchase-item-row').forEach(function (row) {
                const qty = parseFloat(row.querySelector('.purchase-qty-input')?.value || 0);
                const rate = parseFloat(row.querySelector('.purchase-rate-input')?.value || 0);
                const gstRate = parseFloat(row.querySelector('.purchase-gst-input')?.value || 0);

                const amount = qty * rate;

                let cgst = 0;
                let sgst = 0;
                let igst = 0;

                if (taxType === 'intra_state') {
                    cgst = amount * (gstRate / 2) / 100;
                    sgst = amount * (gstRate / 2) / 100;
                } else {
                    igst = amount * gstRate / 100;
                }

                const lineTotal = amount + cgst + sgst + igst;

                row.querySelector('.purchase-amount-input').value = amount.toFixed(2);
                row.querySelector('.purchase-cgst-input').value = cgst.toFixed(2);
                row.querySelector('.purchase-sgst-input').value = sgst.toFixed(2);
                row.querySelector('.purchase-igst-input').value = igst.toFixed(2);
                row.querySelector('.purchase-line-total-input').value = lineTotal.toFixed(2);

                subtotal += amount;
                cgstTotal += cgst;
                sgstTotal += sgst;
                igstTotal += igst;
            });

            const discount = numberValue('#purchase-discount');
            const roundOff = numberValue('#purchase-round-off');
            const paid = numberValue('#purchase-paid');

            const grandTotal = subtotal + cgstTotal + sgstTotal + igstTotal - discount + roundOff;
            const due = grandTotal - paid;

            document.getElementById('purchase-subtotal').value = subtotal.toFixed(2);
            document.getElementById('purchase-cgst-total').value = cgstTotal.toFixed(2);
            document.getElementById('purchase-sgst-total').value = sgstTotal.toFixed(2);
            document.getElementById('purchase-igst-total').value = igstTotal.toFixed(2);
            document.getElementById('purchase-grand-total').value = grandTotal.toFixed(2);
            document.getElementById('purchase-due').value = due.toFixed(2);
        }

        body.addEventListener('input', function (e) {
            if (
                e.target.classList.contains('purchase-qty-input') ||
                e.target.classList.contains('purchase-rate-input') ||
                e.target.classList.contains('purchase-gst-input')
            ) {
                recalcPurchaseTotals();
            }
        });

        taxTypeInput.addEventListener('change', recalcPurchaseTotals);

        ['#purchase-discount', '#purchase-round-off', '#purchase-paid'].forEach(function (selector) {
            document.querySelector(selector)?.addEventListener('input', recalcPurchaseTotals);
        });

        body.addEventListener('click', function (e) {
            if (e.target.classList.contains('purchase-remove-row')) {
                e.target.closest('.purchase-item-row').remove();
                recalcPurchaseTotals();
            }
        });

        addBtn.addEventListener('click', function () {
            const index = body.querySelectorAll('.purchase-item-row').length;

            const template = `
                <tr class="purchase-item-row">
                    <td class="px-3 py-1 w-[380px] min-w-[380px]">
                        <select name="items[${index}][item_id]"
                                class="w-full min-w-[350px] border rounded px-2 py-1 bg-white text-gray-900"
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
                        <input type="number" min="1" name="items[${index}][qty]" class="w-20 border rounded px-2 py-1 purchase-qty-input" value="1" required>
                    </td>

                    <td class="px-3 py-1">
                        <select name="items[${index}][qty_unit]"
                                class="w-28 border rounded px-2 py-1 bg-white text-gray-900"
                                required>

                            <option value="pcs">Pcs</option>
                            <option value="gram">Gram</option>
                            <option value="kg">Kg</option>
                            <option value="carat">Carat</option>

                        </select>
                    </td>

                    <td class="px-3 py-1">
                        <input type="number" step="0.01" min="0" name="items[${index}][rate]" class="w-24 border rounded px-2 py-1 purchase-rate-input" value="0" required>
                    </td>

                    <td class="px-3 py-1">
                        <input type="number" step="0.01" name="items[${index}][amount]" class="w-28 border rounded px-2 py-1 purchase-amount-input bg-gray-100" value="0" readonly>
                    </td>

                    <td class="px-3 py-1">
                        <input type="number" step="0.01" min="0" name="items[${index}][gst_rate]" class="w-20 border rounded px-2 py-1 purchase-gst-input" value="3">
                    </td>

                    <td class="px-3 py-1">
                        <input type="number" step="0.01" class="w-24 border rounded px-2 py-1 purchase-cgst-input bg-gray-100" value="0" readonly>
                    </td>

                    <td class="px-3 py-1">
                        <input type="number" step="0.01" class="w-24 border rounded px-2 py-1 purchase-sgst-input bg-gray-100" value="0" readonly>
                    </td>

                    <td class="px-3 py-1">
                        <input type="number" step="0.01" class="w-24 border rounded px-2 py-1 purchase-igst-input bg-gray-100" value="0" readonly>
                    </td>

                    <td class="px-3 py-1">
                        <input type="number" step="0.01" class="w-28 border rounded px-2 py-1 purchase-line-total-input bg-gray-100" value="0" readonly>
                    </td>

                    <td class="px-3 py-1 text-right">
                        <button type="button" class="text-red-600 text-xs purchase-remove-row">Remove</button>
                    </td>
                </tr>
            `;

            body.insertAdjacentHTML('beforeend', template);
            recalcPurchaseTotals();
        });

        recalcPurchaseTotals();
    });
</script>