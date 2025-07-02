<x-layouts.app :title="__('Create Invoice')">
    <div class="flex flex-col gap-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Create Invoice</h1>

        <form action="{{ route('invoices.store') }}" method="POST">
            @csrf

            <!-- Invoice Info -->
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Number</label>
                    <input type="text" name="invoice_number" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Client</label>
                    <select name="client_id" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                        <option disabled selected>Select Client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Date</label>
                    <input type="date" name="invoice_date" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="grid md:grid-cols-3 gap-4 mt-6">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Subtotal</label>
                    <input type="number" step="0.01" name="subtotal" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Tax Amount</label>
                    <input type="number" step="0.01" name="tax_amount" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Total</label>
                    <input type="number" step="0.01" name="total" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Received Amount</label>
                    <input type="number" step="0.01" name="received_amount" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Balance</label>
                    <input type="number" step="0.01" name="balance" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Amount in Words</label>
                    <input type="text" name="amount_in_words" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                </div>
            </div>

            <!-- Items -->
            <h2 class="mt-8 mb-2 text-lg font-semibold text-gray-800 dark:text-white">Invoice Items</h2>
            <div id="items" class="space-y-4">
                <div class="grid md:grid-cols-6 gap-3 item-row">
                    <input type="text" name="items[0][description]" placeholder="Description" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                    <input type="text" name="items[0][sac_code]" placeholder="SAC Code" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300">
                    <input type="number" name="items[0][quantity]" placeholder="Qty" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                    <input type="number" step="0.01" name="items[0][rate]" placeholder="Rate" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                    <input type="number" step="0.01" name="items[0][tax_percent]" placeholder="Tax %" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                    <input type="number" step="0.01" name="items[0][amount]" placeholder="Amount" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                </div>
            </div>

            <button type="button" onclick="addItemRow()" class="mt-4 text-sm text-blue-600 hover:underline">
                + Add Item
            </button>

            <div class="mt-8">
                <button type="submit" class="px-6 py-2 text-white bg-green-600 hover:bg-green-700 rounded-lg">
                    Save Invoice
                </button>
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
                <input type="text" name="items[${itemIndex}][description]" placeholder="Description" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                <input type="text" name="items[${itemIndex}][sac_code]" placeholder="SAC Code" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300">
                <input type="number" name="items[${itemIndex}][quantity]" placeholder="Qty" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                <input type="number" step="0.01" name="items[${itemIndex}][rate]" placeholder="Rate" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                <input type="number" step="0.01" name="items[${itemIndex}][tax_percent]" placeholder="Tax %" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
                <input type="number" step="0.01" name="items[${itemIndex}][amount]" placeholder="Amount" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring focus:ring-blue-300" required>
            `;
            container.appendChild(row);
            itemIndex++;
        }
    </script>
</x-layouts.app>
