{{-- ==========================================
   FILE: resources/views/invoices/create_kapoor_style.blade.php
   NOTE: SERVICE => amount items.price * qty (NO metal rates)
========================================== --}}

<x-layouts.app :title="__('Create Sales Invoice')">

    <div x-data="invoiceForm()" x-init="init()" class="space-y-4 max-w-7xl mx-auto px-3 sm:px-6 py-4">

        {{-- errors --}}
        @if ($errors->any())
            <div class="p-3 rounded border border-red-300 bg-red-50 text-red-700">
                <ul class="list-disc ml-4">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- JSON payloads --}}
        <script type="application/json" id="clients-json">{!! $clientsJson !!}</script>
        <script type="application/json" id="items-json">{!! $itemsJson !!}</script>
        <script type="application/json" id="metal-rates-json">{!! $metalRatesJson !!}</script>

        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-neutral-100">Create Sales Invoice</h1>
            <button @click="$refs.form.requestSubmit()"
                    class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                Save
            </button>
        </div>

        <form x-ref="form" method="POST" action="{{ route('invoices.store') }}" @submit.prevent="beforeSubmit">
            @csrf

            {{-- TOP PANELS --}}
            <div class="grid lg:grid-cols-4 gap-4">

                {{-- LEFT: Bill To --}}
                <div class="lg:col-span-2 p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Bill To</div>
                    </div>

                    <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300 mb-1">Party</label>
                    <div class="flex gap-2">
                        <select name="client_id" x-model="clientId" required
                                class="flex-1 border rounded px-3 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                            <option value="">-- Select Client --</option>
                            <template x-for="c in clients" :key="c.id">
                                <option :value="c.id" x-text="c.mobile ? (c.name + ' (' + c.mobile + ')') : c.name"></option>
                            </template>
                        </select>

                        <button type="button"
                                class="px-3 py-2 rounded border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 hover:bg-gray-50 dark:hover:bg-neutral-800 text-sm"
                                @click="openClientModal()">
                            + New
                        </button>
                    </div>

                    {{-- details --}}
                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <div class="text-gray-500 dark:text-neutral-400">Name</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100" x-text="party.name || '-'"></div>
                        </div>
                        <div>
                            <div class="text-gray-500 dark:text-neutral-400">Phone</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100" x-text="party.mobile || '-'"></div>
                        </div>
                        <div class="col-span-2">
                            <div class="text-gray-500 dark:text-neutral-400">Add</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100" x-text="party.address || '-'"></div>
                        </div>
                        <div>
                            <div class="text-gray-500 dark:text-neutral-400">State</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100" x-text="party.state || '-'"></div>
                        </div>
                        <div>
                            <div class="text-gray-500 dark:text-neutral-400">State Code</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100" x-text="party.state_code || '-'"></div>
                        </div>
                        <div class="col-span-2">
                            <div class="text-gray-500 dark:text-neutral-400">GSTIN</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100" x-text="party.gstin || 'Unregistered'"></div>
                        </div>
                        <div class="col-span-2">
                            <div class="text-gray-500 dark:text-neutral-400">GST Type</div>
                            <div class="font-semibold" :class="isIntra() ? 'text-green-600' : 'text-purple-600'"
                                 x-text="isIntra() ? 'Intra State (CGST+SGST)' : 'Inter State (IGST)'"></div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Invoice meta --}}
                <div class="lg:col-span-2 p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Date</label>
                            <input type="date" name="invoice_date" x-model="hdr.date" required
                                   class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Bill No.</label>
                            <input :value="invoiceNo" readonly
                                   class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Invoice Prefix</label>
                            <input :value="computedPrefix" readonly
                                   class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200 text-sm">
                            <input type="hidden" name="invoice_prefix" :value="computedPrefix">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">GST No.</label>
                            <input type="text" name="gst_no" x-model="hdr.gst_no"
                                   class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Transport Mode</label>
                            <input type="text" name="transport_mode" x-model="hdr.transport_mode" placeholder="By Hand"
                                   class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= TABLE ================= --}}
            <div class="border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-separate border-spacing-0">
                        <thead class="bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200">
                        <tr class="[&>th]:px-3 [&>th]:py-2 [&>th]:font-medium text-left text-xs">
                            <th>S.No.</th>
                            <th>Description</th>
                            <th>HSN / SAC</th>
                            <th class="text-center">Qty</th>

                            {{-- Product-only columns --}}
                            <th x-show="hasProduct()">Making Rate</th>
                            <th x-show="hasProduct()">Gold Rate (₹/g)</th>
                            <th x-show="hasProduct()">Silver Rate (₹/g)</th>
                            <th x-show="hasProduct()">Silver Wt.(Gm)</th>
                            <th x-show="hasProduct()">Gold Wt.(Gm)</th>
                            <th x-show="hasProduct()">Gem Stone Wt.(Ct.)</th>
                            <th x-show="hasProduct()">Diamond Wt.(Ct.)</th>

                            {{-- Service-only column --}}
                            <th x-show="hasService()">Service Rate (₹)</th>

                            <th>Tax %</th>
                            <th>Amount</th>
                            <th></th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700 text-gray-900 dark:text-neutral-100">
                        <template x-for="(row, i) in items" :key="row._k">
                            <tr>
                                <td class="px-3 py-2 text-center text-xs" x-text="i+1"></td>

                                <td class="px-3 py-2">
                                    <select class="w-full border rounded px-2 py-1 mb-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-xs"
                                            @change="pickItem(i, $event.target.value)">
                                        <option value="">-- Select Item --</option>
                                        <template x-for="it in itemsData" :key="it.id">
                                            <option :value="it.id" x-text="it.sku ? (it.name + ' (' + it.sku + ')') : it.name"></option>
                                        </template>
                                    </select>

                                    <input type="text" x-model="row.description" required
                                           class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-xs">
                                    <div class="mt-1 text-[10px] text-gray-500 dark:text-neutral-400"
                                         x-show="row.item_type"
                                         x-text="row.item_type ? ('Type: ' + row.item_type.toUpperCase()) : ''"></div>
                                </td>

                                <td class="px-3 py-2">
                                    <input x-model="row.hsn"
                                           class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2 text-center">
                                    <input type="number" min="1" step="1"
                                           x-model.number="row.quantity" @input="calc()"
                                           class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs text-center">
                                </td>

                                {{-- Product-only cells --}}
                                <td class="px-3 py-2" x-show="row.item_type === 'product'">
                                    <input type="number" step="0.01" min="0"
                                           x-model.number="row.making_rate" @input="calc()"
                                           class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2" x-show="row.item_type === 'product'">
                                    <input type="number" step="0.01" min="0"
                                           x-model.number="row.gold_rate" @input="calc()"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                    <div class="mt-0.5 text-[10px] text-gray-500 dark:text-neutral-400"
                                         x-text="row.gold_purity ? ('Purity: ' + row.gold_purity) : ''"></div>
                                </td>

                                <td class="px-3 py-2" x-show="row.item_type === 'product'">
                                    <input type="number" step="0.01" min="0"
                                           x-model.number="row.silver_rate" @input="calc()"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                    <div class="mt-0.5 text-[10px] text-gray-500 dark:text-neutral-400"
                                         x-text="row.silver_purity ? ('Purity: ' + row.silver_purity) : ''"></div>
                                </td>

                                <td class="px-3 py-2" x-show="row.item_type === 'product'">
                                    <input type="number" step="0.001" min="0"
                                           x-model.number="row.silver_wt" @input="calc()"
                                           class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2" x-show="row.item_type === 'product'">
                                    <input type="number" step="0.001" min="0"
                                           x-model.number="row.gold_wt" @input="calc()"
                                           class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2" x-show="row.item_type === 'product'">
                                    <input type="number" step="0.001" min="0"
                                           x-model.number="row.gemstone_wt" @input="calc()"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2" x-show="row.item_type === 'product'">
                                    <input type="number" step="0.001" min="0"
                                           x-model.number="row.diamond_wt" @input="calc()"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Service-only cell --}}
                                <td class="px-3 py-2" x-show="row.item_type === 'service'">
                                    {{-- ✅ Service amount item.price se aayega, user edit allow/deny as you want --}}
                                    <input type="number" step="0.01" min="0"
                                           x-model.number="row.service_rate" @input="calc()"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                    <div class="mt-0.5 text-[10px] text-gray-500 dark:text-neutral-400">
                                        (Service = Item Price)
                                    </div>
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" max="100"
                                           x-model.number="row.tax_percent" @input="calc()"
                                           class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input readonly :value="lineAmount(row).toFixed(2)"
                                           class="w-28 bg-gray-50 dark:bg-neutral-800 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 text-xs">
                                </td>

                                <td class="px-3 py-2 text-right">
                                    <button type="button" @click="remove(i)"
                                            class="text-red-600 hover:underline text-lg leading-none">×</button>
                                </td>
                            </tr>
                        </template>

                        <tr>
                            <td colspan="30" class="px-3 py-2">
                                <button type="button" @click="add()" class="text-blue-600 hover:underline text-sm">
                                    + Add Item
                                </button>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================= BOTTOM ================= --}}
            <div class="grid lg:grid-cols-2 gap-4">
                {{-- Left --}}
                <div class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 space-y-3">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="hdr.reverse_charge" class="rounded border-gray-300 dark:border-neutral-700">
                        <span class="text-sm text-gray-800 dark:text-neutral-100">Reverse Charge (Y/N)</span>
                    </div>
                    <input type="hidden" name="reverse_charge" :value="hdr.reverse_charge ? 1 : 0">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Terms and Conditions</label>
                        <textarea name="terms" rows="4"
                                  class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">{{ old('terms', $defaultTerms) }}</textarea>
                    </div>

                    {{-- Payments --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Cash</span>
                            <input type="number" step="0.01" min="0" name="pay_cash"
                                   x-model.number="pay.cash" @input="calc()"
                                   class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">UPI / Online</span>
                            <input type="number" step="0.01" min="0" name="pay_upi"
                                   x-model.number="pay.upi" @input="calc()"
                                   class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Card</span>
                            <input type="number" step="0.01" min="0" name="pay_card"
                                   x-model.number="pay.card" @input="calc()"
                                   class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Cheque</span>
                            <input type="number" step="0.01" min="0" name="pay_cheque"
                                   x-model.number="pay.cheque" @input="calc()"
                                   class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Credit Sales/Excess Amt.</span>
                            <input type="number" step="0.01" min="0" name="credit_sales_excess"
                                   x-model.number="pay.credit_excess" @input="calc()"
                                   class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Advance</span>
                            <input type="number" step="0.01" min="0" name="advance_amount"
                                   x-model.number="pay.advance" @input="calc()"
                                   class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm text-right">
                        </div>

                        <div class="grid md:grid-cols-2 gap-2 pt-2">
                            <input type="text" name="online_mode" x-model="pay.online_mode" placeholder="online mode (upi/bank/neft...)"
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                            <input type="text" name="online_ref" x-model="pay.online_ref" placeholder="UTR/Txn Ref"
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                            <input type="text" name="upi_id" x-model="pay.upi_id" placeholder="UPI ID"
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                            <input type="text" name="card_last4" x-model="pay.card_last4" placeholder="Card last4"
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                            <input type="text" name="card_ref" x-model="pay.card_ref" placeholder="Card Ref"
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                            <input type="text" name="cheque_no" x-model="pay.cheque_no" placeholder="Cheque No"
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                            <input type="text" name="bank_name" x-model="pay.bank_name" placeholder="Bank Name"
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                        </div>
                    </div>
                </div>

                {{-- Right totals --}}
                <div class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 space-y-2">
                    <div class="flex justify-between font-semibold">
                        <span class="text-sm text-gray-800 dark:text-neutral-100">Balance Amount</span>
                        <span class="text-sm" x-text="money(balanceAmount())"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">CGST</span>
                        <span class="text-sm" x-text="money(cgst())"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">SGST</span>
                        <span class="text-sm" x-text="money(sgst())"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">IGST</span>
                        <span class="text-sm" x-text="money(igst())"></span>
                    </div>
                    <div class="flex justify-between font-semibold text-lg pt-3">
                        <span>Total Value</span>
                        <span x-text="money(grandTotal())"></span>
                    </div>

                    <input type="hidden" name="cgst_amount" :value="cgst()">
                    <input type="hidden" name="sgst_amount" :value="sgst()">
                    <input type="hidden" name="igst_amount" :value="igst()">
                </div>
            </div>

            {{-- Hidden JSON --}}
            <input type="hidden" id="items_json" name="items_json">

            <div class="text-right">
                <button @click="$refs.form.requestSubmit()"
                        class="mt-3 px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                    Save
                </button>
            </div>
        </form>
    </div>

    <script>
        function invoiceForm(){
            const CLIENTS = JSON.parse(document.getElementById('clients-json')?.textContent || '[]');
            const ITEMS = JSON.parse(document.getElementById('items-json')?.textContent || '[]');
            const METAL_RATES = JSON.parse(document.getElementById('metal-rates-json')?.textContent || '[]');

            const BIZ_STATE_CODE = @js($businessStateCode ?? '');
            const BIZ_GSTIN = @js($businessGstin ?? '');

            return {
                clients: CLIENTS,
                itemsData: ITEMS,
                metalRates: METAL_RATES,

                clientId: '',
                party: { name:'', address:'', state:'', state_code:'', mobile:'', gstin:'' },

                hdr: {
                    date: @js($today),
                    transport_mode: 'By Hand',
                    gst_no: BIZ_GSTIN,
                    reverse_charge: false,
                },

                basePrefix: @js($basePrefix),
                computedPrefix: @js($suggestedPrefix),
                invoiceNo: @js($initialInvoiceNo),

                pay: {
                    cash: 0, upi: 0, card: 0, cheque: 0, credit_excess: 0, advance: 0,
                    online_mode:'', online_ref:'', upi_id:'', card_last4:'', card_ref:'', cheque_no:'', bank_name:''
                },

                // ✅ IMPORTANT FIX: empty array
                items: [],

                blankRow(){
                    return {
                        _k: Date.now()+Math.random(),
                        item_id: null,
                        item_type: null,

                        description: '',
                        hsn: '',
                        quantity: 1,

                        making_rate: 0,
                        gold_purity: null,
                        silver_purity: null,
                        gold_rate: 0,
                        silver_rate: 0,
                        silver_wt: 0,
                        gold_wt: 0,
                        gemstone_wt: 0,
                        diamond_wt: 0,

                        service_rate: 0,
                        tax_percent: 0,
                    }
                },

                init(){
                    this.$watch('clientId', () => this.syncParty());

                    // ✅ IMPORTANT FIX: first row yaha push hoga
                    if (!this.items.length) this.items.push(this.blankRow());

                    this.syncParty();
                    this.calc();
                },

                syncParty(){
                    const c = this.clients.find(x => String(x.id) === String(this.clientId));
                    this.party = c ? {
                        name: c.name ?? '',
                        mobile: c.mobile ?? '',
                        address: c.address ?? '',
                        state: c.state ?? '',
                        state_code: c.state_code ?? '',
                        gstin: c.gstin ?? '',
                    } : { name:'', address:'', state:'', state_code:'', mobile:'', gstin:'' };

                    this.calc();
                },

                keyCode(v){ return String(v ?? '').trim().replace(/^0+/, ''); },

                isIntra(){
                    const bizCode = this.keyCode(BIZ_STATE_CODE);
                    const partyCode = this.keyCode(this.party.state_code ?? '');
                    if (!bizCode || !partyCode) return false;
                    return bizCode === partyCode;
                },

                hasProduct(){ return this.items.some(r => r.item_type === 'product'); },
                hasService(){ return this.items.some(r => r.item_type === 'service'); },

                findMetalRate(type, purity){
                    const t = String(type||'').toLowerCase().trim();
                    const pRaw = String(purity||'').trim();
                    if(!t) return 0;

                    const candidates = [];
                    if(pRaw){
                        candidates.push(pRaw);
                        candidates.push(pRaw.split('(')[0].trim());
                        candidates.push(pRaw.split(' ')[0].trim());
                        const m = pRaw.match(/\(([^)]+)\)/);
                        if(m && m[1]) candidates.push(String(m[1]).trim());
                    }
                    const uniq = [...new Set(candidates.filter(Boolean))];

                    const rec = this.metalRates.find(r => {
                        const rt = String(r.metal_type||'').toLowerCase().trim();
                        if(rt !== t) return false;
                        const rp = String(r.purity||'').trim();
                        if(!rp) return false;
                        const rpBase = rp.split('(')[0].trim();
                        const rpFirst = rp.split(' ')[0].trim();
                        const rpParen = (rp.match(/\(([^)]+)\)/)||[])[1] ? String((rp.match(/\(([^)]+)\)/)||[])[1]).trim() : '';
                        return uniq.includes(rp) || uniq.includes(rpBase) || uniq.includes(rpFirst) || (rpParen && uniq.includes(rpParen));
                    });

                    return rec ? Number(rec.rate_per_gram || rec.rate || 0) : 0;
                },

                pickItem(i, id){
                    const it = this.itemsData.find(x => String(x.id) === String(id));
                    if(!it) return;

                    const r = this.items[i];

                    r.item_id   = it.id;
                    r.item_type = (it.type || '').toLowerCase().trim() || 'product';

                    r.description = it.description || it.name || '';
                    r.tax_percent = Number(it.tax_rate ?? 0);

                    // ✅ SERVICE => items.price
                    if (r.item_type === 'service') {
                        r.hsn = it.sac || '';
                        r.quantity = Math.max(1, Number(r.quantity || 1));
                        r.service_rate = Number(it.price ?? 0);

                        r.making_rate = 0;
                        r.gold_purity = null;
                        r.silver_purity = null;
                        r.gold_rate = 0;
                        r.silver_rate = 0;
                        r.gold_wt = 0;
                        r.silver_wt = 0;
                        r.gemstone_wt = 0;
                        r.diamond_wt = 0;

                        this.calc();
                        return;
                    }

                    // ✅ PRODUCT
                    r.hsn = it.hsn || it.sac || '';
                    r.quantity = Number(it.quantity ?? 1) || 1;
                    r.service_rate = 0;

                    r.gold_wt = Number(it.gold_weight ?? it.gold_wt ?? 0);
                    r.silver_wt = Number(it.silver_weight ?? it.silver_wt ?? 0);

                    r.gold_purity = (it.gold_purity ?? it.purity ?? '').toString().trim() || null;
                    r.silver_purity = (it.silver_purity ?? '').toString().trim() || null;

                    r.gemstone_wt = Number(it.stone_weight ?? it.gemstone_wt ?? 0);
                    r.diamond_wt  = Number(it.diamond_weight ?? it.diamond_wt ?? 0);

                    r.making_rate = Number(it.making_charge ?? it.making_rate ?? 0);

                    r.gold_rate   = this.findMetalRate('gold', r.gold_purity);
                    r.silver_rate = this.findMetalRate('silver', r.silver_purity || '999');

                    this.calc();
                },

                add(){
                    this.items.push(this.blankRow());
                    this.calc();
                },

                remove(i){
                    this.items.splice(i,1);
                    if(!this.items.length) this.items.push(this.blankRow());
                    this.calc();
                },

                lineBase(r){
                    const qty = Math.max(1, Number(r.quantity || 1));
                    if (r.item_type === 'service') {
                        const rate = Number(r.service_rate || 0);
                        return Math.max(0, Number((rate * qty).toFixed(2)));
                    }
                    const goldAmt = Number(r.gold_wt||0) * Number(r.gold_rate||0);
                    const silvAmt = Number(r.silver_wt||0) * Number(r.silver_rate||0);
                    const making  = Number(r.making_rate||0);
                    return Math.max(0, Number(((goldAmt + silvAmt + making) * qty).toFixed(2)));
                },

                lineTax(r){
                    const base = this.lineBase(r);
                    const pct = Number(r.tax_percent||0);
                    return Number((base * (pct/100)).toFixed(2));
                },

                lineAmount(r){
                    return Number((this.lineBase(r) + this.lineTax(r)).toFixed(2));
                },

                subtotal(){
                    return Number(this.items.reduce((s,r)=> s + this.lineBase(r), 0).toFixed(2));
                },

                taxTotal(){
                    return Number(this.items.reduce((s,r)=> s + this.lineTax(r), 0).toFixed(2));
                },

                cgst(){ return this.isIntra() ? Number((this.taxTotal()/2).toFixed(2)) : 0; },
                sgst(){ return this.isIntra() ? Number((this.taxTotal()/2).toFixed(2)) : 0; },
                igst(){ return this.isIntra() ? 0 : Number(this.taxTotal().toFixed(2)); },

                grandTotal(){
                    return Number((this.subtotal() + this.taxTotal()).toFixed(2));
                },

                calc(){ this.grandTotal(); },

                beforeSubmit(){
                    const payload = this.items.map(r => ({
                        item_id: r.item_id ?? null,
                        item_type: r.item_type ?? null,
                        description: r.description || '',
                        hsn: r.hsn || '',
                        quantity: Math.max(1, Number(r.quantity ?? 1)),

                        making_rate: Number(r.making_rate||0),
                        gold_purity: r.gold_purity || null,
                        silver_purity: r.silver_purity || null,
                        gold_rate: Number(r.gold_rate||0),
                        silver_rate: Number(r.silver_rate||0),
                        silver_wt: Number(r.silver_wt||0),
                        gold_wt: Number(r.gold_wt||0),
                        gemstone_wt: Number(r.gemstone_wt||0),
                        diamond_wt: Number(r.diamond_wt||0),

                        service_rate: Number(r.service_rate||0),

                        tax_percent: Number(r.tax_percent||0),

                        base_amount: this.lineBase(r),
                        tax_amount: this.lineTax(r),
                        amount: this.lineAmount(r),

                        cgst_amount: this.isIntra() ? Number((this.lineTax(r)/2).toFixed(2)) : 0,
                        sgst_amount: this.isIntra() ? Number((this.lineTax(r)/2).toFixed(2)) : 0,
                        igst_amount: this.isIntra() ? 0 : Number(this.lineTax(r).toFixed(2)),
                    }));

                    document.getElementById('items_json').value = JSON.stringify(payload);
                    this.$refs.form.submit();
                },

                money(v){ return '₹ ' + Number(v||0).toFixed(2); },
                openClientModal(){},
            }
        }
    </script>


</x-layouts.app>
