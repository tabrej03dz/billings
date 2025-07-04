<x-layouts.app :title="__('Create Invoice')">
    <div class="flex flex-col gap-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Create Invoice</h1>

        <form action="{{ route('invoices.store') }}" method="POST">
            @csrf

            <!-- Invoice Info -->
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Number</label>
                    <input type="text" name="invoice_number" class="border border-gray-300 rounded-md px-3 py-2 w-full" required>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Client</label>
                    <select name="client_id" class="border border-gray-300 rounded-md px-3 py-2 w-full" required>
                        <option disabled selected>Select Client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Date</label>
                    <input type="date" name="invoice_date" class="border border-gray-300 rounded-md px-3 py-2 w-full" required>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="grid md:grid-cols-3 gap-4 mt-6">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Subtotal</label>
                    <input type="number" step="0.01" name="subtotal" readonly class="subtotal border border-gray-300 rounded-md px-3 py-2 w-full">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Tax Amount</label>
                    <input type="number" step="0.01" name="tax_amount" readonly class="tax-amount border border-gray-300 rounded-md px-3 py-2 w-full">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Total</label>
                    <input type="number" step="0.01" name="total" readonly class="total border border-gray-300 rounded-md px-3 py-2 w-full">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Received Amount</label>
                    <input type="number" step="0.01" name="received_amount" class="received-amount border border-gray-300 rounded-md px-3 py-2 w-full">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Balance</label>
                    <input type="number" step="0.01" name="balance" readonly class="balance border border-gray-300 rounded-md px-3 py-2 w-full">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Amount in Words</label>
                    <input type="text" name="amount_in_words" readonly class="amount-in-words border border-gray-300 rounded-md px-3 py-2 w-full">
                </div>
            </div>

            <!-- Items -->
            <h2 class="mt-8 mb-2 text-lg font-semibold text-gray-800 dark:text-white">Invoice Items</h2>
            <div id="items" class="space-y-4">
                <div class="grid md:grid-cols-6 gap-3 item-row">
                    <input type="text" name="items[0][description]" placeholder="Description" class="item-description border border-gray-300 rounded-md px-3 py-2 w-full">
                    <input type="text" name="items[0][sac_code]" placeholder="SAC Code" class="item-sac border border-gray-300 rounded-md px-3 py-2 w-full">
                    <input type="number" name="items[0][quantity]" placeholder="Qty" class="item-qty border border-gray-300 rounded-md px-3 py-2 w-full" min="0">
                    <input type="number" step="0.01" name="items[0][rate]" placeholder="Rate" class="item-rate border border-gray-300 rounded-md px-3 py-2 w-full" min="0">
                    <input type="number" step="0.01" name="items[0][tax_percent]" placeholder="Tax %" class="item-tax border border-gray-300 rounded-md px-3 py-2 w-full" min="0">
                    <input type="number" step="0.01" name="items[0][amount]" placeholder="Amount" readonly class="item-amount border border-gray-300 rounded-md px-3 py-2 w-full">
                </div>
            </div>

            <button type="button" onclick="addItemRow()" class="mt-4 text-sm text-blue-600 hover:underline">+ Add Item</button>

            <div class="mt-8">
                <button type="submit" class="px-6 py-2 text-white bg-green-600 hover:bg-green-700 rounded-lg">Save Invoice</button>
            </div>
        </form>
    </div>

    <script>
        let itemIndex = 1;

        function addItemRow() {
            const container = document.getElementById('items');
            const row = document.createElement('div');
            row.classList.add('grid', 'md:grid-cols-6', 'gap-3', 'item-row');
            row.innerHTML = `
                <input type="text" name="items[${itemIndex}][description]" placeholder="Description" class="item-description border border-gray-300 rounded-md px-3 py-2 w-full">
                <input type="text" name="items[${itemIndex}][sac_code]" placeholder="SAC Code" class="item-sac border border-gray-300 rounded-md px-3 py-2 w-full">
                <input type="number" name="items[${itemIndex}][quantity]" placeholder="Qty" class="item-qty border border-gray-300 rounded-md px-3 py-2 w-full" min="0">
                <input type="number" step="0.01" name="items[${itemIndex}][rate]" placeholder="Rate" class="item-rate border border-gray-300 rounded-md px-3 py-2 w-full" min="0">
                <input type="number" step="0.01" name="items[${itemIndex}][tax_percent]" placeholder="Tax %" class="item-tax border border-gray-300 rounded-md px-3 py-2 w-full" min="0">
                <input type="number" step="0.01" name="items[${itemIndex}][amount]" placeholder="Amount" readonly class="item-amount border border-gray-300 rounded-md px-3 py-2 w-full">
            `;
            container.appendChild(row);
            itemIndex++;
            attachListeners();
        }

        function attachListeners() {
            document.querySelectorAll('.item-qty, .item-rate, .item-tax, .received-amount').forEach(input => {
                input.removeEventListener('input', calculateTotals);
                input.addEventListener('input', calculateTotals);
            });
        }

        function calculateTotals() {
            let subtotal = 0, totalTax = 0;

            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
                const rate = parseFloat(row.querySelector('.item-rate')?.value) || 0;
                const taxPercent = parseFloat(row.querySelector('.item-tax')?.value) || 0;

                const base = qty * rate;
                const tax = base * (taxPercent / 100);
                const total = base + tax;

                row.querySelector('.item-amount').value = total.toFixed(2);

                subtotal += base;
                totalTax += tax;
            });

            const total = subtotal + totalTax;
            const received = parseFloat(document.querySelector('.received-amount')?.value) || 0;

            document.querySelector('.subtotal').value = subtotal.toFixed(2);
            document.querySelector('.tax-amount').value = totalTax.toFixed(2);
            document.querySelector('.total').value = total.toFixed(2);
            document.querySelector('.balance').value = (total - received).toFixed(2);
            document.querySelector('.amount-in-words').value = numberToWords(Math.round(total)) + " Rupees Only";
        }

        function numberToWords(num) {
            const a = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten",
                "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen"];
            const b = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];
            if ((num = num.toString()).length > 9) return 'Overflow';
            let n = ('000000000' + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{3})$/);
            if (!n) return;
            let str = '';
            str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + ' Crore ' : '';
            str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + ' Lakh ' : '';
            str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + ' Thousand ' : '';
            str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + '' : '';
            return str.trim();
        }

        document.addEventListener('DOMContentLoaded', () => {
            attachListeners();
        });
    </script>
</x-layouts.app>
