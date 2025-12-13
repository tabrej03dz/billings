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
        <script type="application/json" id="charges-json">{!! $chargesJson !!}</script>
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

                    <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300 mb-1">
                        Party
                    </label>

                    <div class="flex gap-2">
                        <select name="client_id" x-model="clientId" required
                                class="flex-1 border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                                       bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                            <option value="">-- Select Client --</option>
                            <template x-for="c in clients" :key="c.id">
                                <option :value="c.id"
                                        x-text="c.mobile ? (c.name + ' (' + c.mobile + ')') : c.name"></option>
                            </template>
                        </select>

                        <button type="button"
                                class="px-3 py-2 rounded border border-gray-300 dark:border-neutral-700
                                       bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 hover:bg-gray-50 dark:hover:bg-neutral-800 text-sm"
                                @click="openClientModal()">
                            + New
                        </button>
                    </div>

                    {{-- ✅ Kapoor style details --}}
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
                            <div class="text-gray-500 dark:text-neutral-400">Code</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100" x-text="party.code || '-'"></div>
                        </div>

                        <div class="col-span-2">
                            <div class="text-gray-500 dark:text-neutral-400">GSTIN</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100" x-text="party.gstin || 'Unregistered'"></div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Invoice meta --}}
                <div class="lg:col-span-2 p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                    <div class="grid md:grid-cols-2 gap-3">

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Date</label>
                            <input type="date" name="invoice_date" x-model="hdr.date" required
                                   class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700
                                          bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Bill No.</label>
                            <input :value="invoiceNo" readonly
                                   class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700
                                          bg-gray-50 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200 text-sm">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Invoice Prefix</label>
                            <input :value="computedPrefix" readonly
                                   class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700
                                          bg-gray-50 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200 text-sm">
                            <input type="hidden" name="invoice_prefix" :value="computedPrefix">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">GST No.</label>
                            <input type="text" name="gst_no" x-model="hdr.gst_no"
                                   class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700
                                          bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Transport Mode</label>
                            <input type="text" name="transport_mode" x-model="hdr.transport_mode" placeholder="By Hand"
                                   class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700
                                          bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                        </div>

                    </div>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-separate border-spacing-0">
                        <thead class="bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200">
                        <tr class="[&>th]:px-3 [&>th]:py-2 [&>th]:font-medium text-left text-xs">
                            <th>S.No.</th>
                            <th>Description</th>
                            <th>HSN CODE</th>
                            <th>Making Rate</th>
                            <th>Gold/Silver Rate (₹/g)</th>
                            <th>Silver Wt.(Gm)</th>
                            <th>Gold Wt.(Gm)</th>
                            <th>Gem Stone Wt.(Ct.)</th>
                            <th>Diamond Wt.(Ct.)</th>
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
                                    <select class="w-full border rounded px-2 py-1 mb-1 border-gray-300 dark:border-neutral-700
                                                   bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-xs"
                                            @change="pickItem(i, $event.target.value)">
                                        <option value="">-- Select Item --</option>
                                        <template x-for="it in itemsData" :key="it.id">
                                            <option :value="it.id" x-text="it.sku ? (it.name + ' (' + it.sku + ')') : it.name"></option>
                                        </template>
                                    </select>

                                    <input type="text" x-model="row.description" required
                                           class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input x-model="row.hsn"
                                           class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" x-model.number="row.making_rate" @input="calc()"
                                           class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" x-model.number="row.metal_rate" @input="calc()"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.001" min="0" x-model.number="row.silver_wt" @input="calc()"
                                           class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.001" min="0" x-model.number="row.gold_wt" @input="calc()"
                                           class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.001" min="0" x-model.number="row.gemstone_wt" @input="calc()"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.001" min="0" x-model.number="row.diamond_wt" @input="calc()"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" max="100" x-model.number="row.tax_percent" @input="calc()"
                                           class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input readonly :value="lineAmount(row).toFixed(2)"
                                           class="w-28 bg-gray-50 dark:bg-neutral-800 border rounded px-2 py-1
                                                  border-gray-300 dark:border-neutral-700 text-xs">
                                </td>

                                <td class="px-3 py-2 text-right">
                                    <button type="button" @click="remove(i)"
                                            class="text-red-600 hover:underline text-lg leading-none">×</button>
                                </td>
                            </tr>
                        </template>

                        <tr>
                            <td colspan="12" class="px-3 py-2">
                                <button type="button" @click="add()" class="text-blue-600 hover:underline text-sm">
                                    + Add Item
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- BOTTOM: Reverse charge + Totals box + Payments --}}
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
                                  class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                                         bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">{{ old('terms', $defaultTerms) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Payment Adjustments</label>

                        <div class="mt-2 space-y-2 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-gray-600 dark:text-neutral-300">Credit/Debit Card no.</span>
                                <input type="text" name="pay_card_no" x-model="pay.card_no"
                                       class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                              bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <span class="text-gray-600 dark:text-neutral-300">UPI</span>
                                <input type="number" step="0.01" min="0" name="pay_upi" x-model.number="pay.upi" @input="calc()"
                                       class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                              bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm text-right">
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <span class="text-gray-600 dark:text-neutral-300">Cash</span>
                                <input type="number" step="0.01" min="0" name="pay_cash" x-model.number="pay.cash" @input="calc()"
                                       class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                              bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm text-right">
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <span class="text-gray-600 dark:text-neutral-300">Credit Sales/Excess Amt.</span>
                                <input type="number" step="0.01" min="0" name="credit_excess" x-model.number="pay.credit_excess"
                                       class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                              bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm text-right">
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <span class="text-gray-600 dark:text-neutral-300">Advance</span>
                                <input type="number" step="0.01" min="0" name="advance" x-model.number="pay.advance"
                                       class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                              bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm text-right">
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <span class="text-gray-600 dark:text-neutral-300">Purchase</span>
                                <input type="text" name="purchase_ref" x-model="pay.purchase_ref"
                                       class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                              bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right totals --}}
                <div class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">Less</span>
                        <input type="number" min="0" step="0.01" name="less"
                               x-model.number="u.less" @input="calc()"
                               class="w-40 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                      bg-white dark:bg-neutral-900 text-sm text-right">
                    </div>

                    <div class="flex justify-between font-semibold">
                        <span class="text-sm text-gray-800 dark:text-neutral-100">Balance Amount</span>
                        <span class="text-sm" x-text="money(balanceAmount())"></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">CGST : 1.5%</span>
                        <span class="text-sm" x-text="money(cgst())"></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">SGST : 1.5%</span>
                        <span class="text-sm" x-text="money(sgst())"></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">IGST :</span>
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

        {{-- Client modal (same as your old; keep if needed) --}}
        <div x-show="mod.client" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" style="display:none">
            <div class="bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 rounded-lg w-full max-w-lg p-4 border border-gray-200 dark:border-neutral-700 shadow-xl">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold">Add Party</h3>
                    <button class="text-xl" @click="mod.client=false">&times;</button>
                </div>

                <form @submit.prevent="saveClient" class="space-y-3">
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm mb-1">Name *</label>
                            <input x-model="clientForm.name" required class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Mobile *</label>
                            <input x-model="clientForm.mobile" required class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm mb-1">GSTIN</label>
                            <input x-model="clientForm.gstin" class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm mb-1">State</label>
                            <input x-model="clientForm.state" class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm mb-1">Code</label>
                            <input x-model="clientForm.code" class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm mb-1">Address</label>
                            <textarea x-model="clientForm.address" rows="2" class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm"></textarea>
                        </div>
                    </div>

                    <div class="mt-2 flex justify-end gap-2">
                        <button type="button" class="px-3 py-2 rounded border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm" @click="mod.client=false">Cancel</button>
                        <button type="submit" class="px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 text-sm">Save Party</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function invoiceForm(){
            const CLIENTS     = JSON.parse(document.getElementById('clients-json')?.textContent || '[]');
            const ITEMS       = JSON.parse(document.getElementById('items-json')?.textContent || '[]');
            const METAL_RATES = JSON.parse(document.getElementById('metal-rates-json')?.textContent || '[]');

            return {
                clients: CLIENTS,
                itemsData: ITEMS,
                metalRates: METAL_RATES,

                clientId: '',
                party: { name:'', address:'', state:'', code:'', mobile:'', gstin:'' },

                mod: { client:false },
                clientForm: { name:'', mobile:'', gstin:'', state:'', code:'', address:'' },

                hdr: {
                    date: '{{ $today }}',
                    transport_mode: 'By Hand',
                    gst_no: @js($businessGstin ?? ''),
                    reverse_charge: false,
                },

                basePrefix: @js($basePrefix),
                computedPrefix: @js($suggestedPrefix),
                invoiceNo: @js($initialInvoiceNo),

                // payment adjustments
                pay: {
                    card_no: '',
                    upi: 0,
                    cash: 0,
                    credit_excess: 0,
                    advance: 0,
                    purchase_ref: '',
                },

                // totals
                u: { less: 0 },

                items: [{
                    _k: Date.now(),
                    item_id: null,
                    description: '',
                    hsn: '',
                    making_rate: 0,
                    metal_type: null,
                    purity: null,
                    metal_rate: 0,
                    silver_wt: 0,
                    gold_wt: 0,
                    gemstone_wt: 0,
                    diamond_wt: 0,
                    tax_percent: 0,
                }],

                init(){
                    this.$watch('clientId', () => this.syncParty());
                    this.$watch('hdr.date', () => { this.refreshPrefix(); this.fetchPreview(); });

                    this.syncParty();
                    this.refreshPrefix();
                    this.fetchPreview();
                    this.calc();
                },

                syncParty(){
                    const c = this.clients.find(x => String(x.id) === String(this.clientId));
                    this.party = c ? {...c} : { name:'', address:'', state:'', code:'', mobile:'', gstin:'' };
                    this.calc();
                },

                refreshPrefix(){
                    const d  = new Date(this.hdr.date || new Date());
                    const yy = d.getFullYear() % 100;
                    const mm = d.getMonth() + 1;
                    const start = (mm >= 4) ? yy : (yy - 1);
                    const a = String((start+100)%100).padStart(2,'0');
                    const b = String((start+1+100)%100).padStart(2,'0');
                    const fy = `${a}-${b}`;
                    const base = (this.basePrefix || 'RV/SL').replace(/\/+$/,'');
                    this.computedPrefix = `${base}/${fy}/`;
                },

                async fetchPreview(){
                    try{
                        const res = await fetch('{{ route('invoices.preview-number') }}', {
                            method: 'POST',
                            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                            body: JSON.stringify({ invoice_date: this.hdr.date, invoice_prefix: this.computedPrefix })
                        });
                        const data = await res.json();
                        if(data?.ok && data?.number) this.invoiceNo = data.number;
                    }catch(e){}
                },

                // metal rate find (same logic)
                findMetalRate(type, purity){
                    if(!type) return 0;
                    const t = String(type).toLowerCase().trim();
                    const pRaw = (purity ?? '').toString().trim();

                    const candidates = [];
                    if (pRaw) {
                        candidates.push(pRaw);
                        candidates.push(pRaw.split('(')[0].trim());
                        candidates.push(pRaw.split(' ')[0].trim());
                    }
                    const seen = new Set();
                    const purityOptions = candidates.map(v=>v.trim()).filter(v=>v && !seen.has(v) && seen.add(v));

                    const rec = this.metalRates.find(r => {
                        const rt = String(r.metal_type).toLowerCase().trim();
                        if(rt !== t) return false;
                        const rp = (r.purity ?? '').toString().trim();
                        return purityOptions.includes(rp) || purityOptions.includes(rp.split('(')[0].trim()) || purityOptions.includes(rp.split(' ')[0].trim());
                    });
                    return rec ? Number(rec.rate_per_gram || 0) : 0;
                },

                pickItem(i, id){
                    const it = this.itemsData.find(x => String(x.id) === String(id));
                    if(!it) return;
                    const r = this.items[i];

                    r.item_id      = it.id;
                    r.description  = it.description || it.name;
                    r.hsn          = it.sac || ''; // using sac as HSN/SAC
                    r.metal_type   = it.metal_type || null;
                    r.purity       = it.purity || null;

                    r.gold_wt      = Number(it.gold_wt || 0);
                    r.silver_wt    = Number(it.silver_wt || 0);
                    r.gemstone_wt  = Number(it.gemstone_wt || 0);
                    r.diamond_wt   = Number(it.diamond_wt || 0);

                    r.making_rate  = Number(it.making_rate || 0);
                    r.tax_percent  = Number(it.tax_rate || 0);

                    r.metal_rate   = this.findMetalRate(r.metal_type, r.purity);

                    this.calc();
                },

                add(){
                    this.items.push({
                        _k: Date.now()+Math.random(),
                        item_id: null, description:'', hsn:'',
                        making_rate: 0,
                        metal_type:null, purity:null, metal_rate:0,
                        silver_wt:0, gold_wt:0, gemstone_wt:0, diamond_wt:0,
                        tax_percent:0
                    });
                    this.calc();
                },

                remove(i){ this.items.splice(i,1); this.calc(); },

                // base amount (Kapoor style approx):
                // MetalAmount = (gold_wt + silver_wt) * metal_rate
                // + making_rate (direct)
                lineBase(r){
                    const metalWt = Number(r.gold_wt||0) + Number(r.silver_wt||0);
                    const metalAmt = metalWt * Number(r.metal_rate||0);
                    const making = Number(r.making_rate||0);
                    return Math.max(0, Number((metalAmt + making).toFixed(2)));
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

                // ✅ GST split: same-state => CGST+SGST, else IGST
                isIntra(){
                    const bizState = (@js($businessState ?? '') || '').toString().toLowerCase().trim();
                    const partyState = (this.party.state || '').toString().toLowerCase().trim();
                    if(!bizState || !partyState) return true;
                    return bizState === partyState;
                },

                cgst(){
                    if(!this.isIntra()) return 0;
                    return Number((this.taxTotal()/2).toFixed(2));
                },

                sgst(){
                    if(!this.isIntra()) return 0;
                    return Number((this.taxTotal()/2).toFixed(2));
                },

                igst(){
                    if(this.isIntra()) return 0;
                    return Number(this.taxTotal().toFixed(2));
                },

                grandTotal(){
                    return Number((this.subtotal() + this.taxTotal() - Number(this.u.less||0)).toFixed(2));
                },

                balanceAmount(){
                    // received = UPI + Cash (you can add Card amount too if you want)
                    const received = Number(this.pay.upi||0) + Number(this.pay.cash||0);
                    return Math.max(0, Number((this.grandTotal() - received).toFixed(2)));
                },

                calc(){ this.grandTotal(); },

                // submit JSON
                beforeSubmit(){
                    const payload = this.items.map(r => ({
                        item_id: r.item_id ?? null,
                        description: r.description || '',
                        hsn: r.hsn || '',
                        making_rate: Number(r.making_rate||0),
                        metal_rate: Number(r.metal_rate||0),
                        silver_wt: Number(r.silver_wt||0),
                        gold_wt: Number(r.gold_wt||0),
                        gemstone_wt: Number(r.gemstone_wt||0),
                        diamond_wt: Number(r.diamond_wt||0),
                        tax_percent: Number(r.tax_percent||0),
                        amount: this.lineAmount(r),
                    }));

                    document.getElementById('items_json').value = JSON.stringify(payload);
                    this.$refs.form.submit();
                },

                money(v){ return '₹ ' + Number(v||0).toFixed(2); },

                // client modal placeholder (optional)
                openClientModal(){ this.mod.client = true; },
                async saveClient(){ alert('hook your quick-store here'); },
            }
        }
    </script>
</x-layouts.app>
