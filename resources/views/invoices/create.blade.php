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

        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-neutral-100">Create Sales Invoice</h1>
            <button @click="$refs.form.requestSubmit()" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Save</button>
        </div>

        <form x-ref="form" method="POST" action="{{ route('invoices.store') }}" @submit.prevent="beforeSubmit">
            @csrf

            {{-- Client + Right Panel --}}
            <div class="grid lg:grid-cols-4 gap-4">
                {{-- Bill To --}}
                <div class="lg:col-span-2 p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-neutral-300">Bill To (Client)</label>
                    <div class="flex gap-2">
                        <select name="client_id" x-model="clientId"
                                class="flex-1 border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                           bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100" required>
                            <option value="">-- Select Client --</option>
                            <template x-for="c in clients" :key="c.id">
                                <option :value="c.id" x-text="c.mobile ? (c.name + ' (' + c.mobile + ')') : c.name"></option>
                            </template>
                        </select>
                        <button type="button"
                                class="px-3 py-2 rounded border border-gray-300 dark:border-neutral-700
                           bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 hover:bg-gray-50 dark:hover:bg-neutral-800"
                                @click="openClientModal()">+ New</button>
                    </div>
                </div>

                {{-- RIGHT PANEL --}}
                <div class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 space-y-3 lg:col-span-2">
                    <div class="grid md:grid-cols-3 gap-3">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Invoice Prefix</label>
                            <input :value="computedPrefix" readonly
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                    bg-gray-50 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200">
                            <input type="hidden" name="invoice_prefix" :value="computedPrefix">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Invoice Number</label>
                            <input :value="invoiceNo" readonly
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                    bg-gray-50 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Sales Invoice Date</label>
                            <input type="date" name="invoice_date" x-model="hdr.date"
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                    bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100"
                                   value="{{ $today }}" required>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Payment Terms</label>
                            <div class="flex overflow-hidden rounded border border-gray-300 dark:border-neutral-700 max-w-[190px]">
                                <input type="number" min="0" x-model.number="hdr.terms"
                                       class="w-24 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                      border-0 px-2 py-1 focus:ring-0 focus:outline-none">
                                <span class="px-2 py-1 text-sm bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-neutral-300 border-l border-gray-300 dark:border-neutral-700">days</span>
                            </div>
                            <input type="hidden" name="payment_terms" :value="hdr.terms">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Due Date</label>
                            <input type="date" name="due_date" x-model="hdr.due" readonly
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                    bg-gray-50 dark:bg-neutral-800 text-gray-900 dark:text-neutral-100">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                <table class="min-w-full text-sm border-separate border-spacing-0">
                    <thead class="bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200">
                    <tr class="[&>th]:px-3 [&>th]:py-2 [&>th]:font-medium text-left">
                        <th>No</th><th>Item / Description</th><th>HSN/SAC</th><th>Qty</th>
                        <th>Price (₹)</th><th>Making Charge</th><th>Discount</th><th>Tax %</th><th>Amount (₹)</th><th></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-neutral-700 text-gray-900 dark:text-neutral-100">
                    <template x-for="(row, i) in items" :key="row._k">
                        <tr>
                            <td class="px-3 py-2 text-center" x-text="i+1"></td>
                            <td class="px-3 py-2">
                                <select class="w-full border rounded px-2 py-1 mb-1 border-gray-300 dark:border-neutral-700
                                 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100"
                                        @change="pickItem(i, $event.target.value)">
                                    <option value="">-- Select Item --</option>
                                    <template x-for="it in itemsData" :key="it.id">
                                        <option :value="it.id" x-text="it.sku ? (it.name + ' (' + it.sku + ')') : it.name"></option>
                                    </template>
                                </select>
                                <input type="text" x-model="row.description" placeholder="Description" required
                                       class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100">
                            </td>
                            <td class="px-3 py-2">
                                <input x-model="row.sac" class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" min="1" x-model.number="row.qty" @input="calc()"
                                       class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" step="0.01" min="0" x-model.number="row.price" @input="calc()"
                                       class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" step="0.01" min="0" x-model.number="row.making_charge" @input="calc()"
                                       class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" step="0.01" min="0" x-model.number="row.discount" @input="calc()"
                                       class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" step="0.01" min="0" max="100" x-model.number="row.tax_percent" @input="calc()"
                                       class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                            </td>
                            <td class="px-3 py-2">
                                <input readonly :value="lineAmount(row).toFixed(2)"
                                       class="w-28 bg-gray-50 dark:bg-neutral-800 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700">
                            </td>
                            <td class="px-3 py-2 text-right">
                                <button type="button" @click="remove(i)" class="text-red-600 hover:underline">×</button>
                            </td>
                        </tr>
                    </template>
                    <tr>
                        <td colspan="9" class="px-3 py-2">
                            <button type="button" @click="add()" class="text-blue-600 hover:underline">+ Add Item</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            {{-- Notes + Totals --}}
            <div class="grid lg:grid-cols-2 gap-4">
                <div class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
{{--                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Terms and Conditions</label>--}}
{{--                    <textarea name="terms" rows="4"--}}
{{--                              class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700--}}
{{--                           bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100"></textarea>--}}
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Terms and Conditions</label>
                    <textarea name="terms" rows="4"
                              class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100">{{ old('terms', $defaultTerms) }}</textarea>

                    <label class="mt-3 block text-sm font-medium text-gray-700 dark:text-neutral-300">Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                           bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100"></textarea>
                </div>

                <div class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">SUBTOTAL</span>
                        <span class="font-medium" x-text="money(subtotal())"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">Tax</span>
                        <span class="font-medium" x-text="money(taxTotal())"></span>
                    </div>

                    {{-- Discount --}}
                    <div>
                        <a href="#" @click.prevent="u.discountEnabled=!u.discountEnabled" class="text-blue-600 hover:underline">+ Add Discount</a>
                        <template x-if="u.discountEnabled">
                            <div class="mt-2 flex justify-between items-center">
                                <input type="number" min="0" step="0.01" x-model.number="u.discount_total" @input="calc()"
                                       class="w-32 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                                <span x-text="'- ' + money(u.discount_total)"></span>
                            </div>
                        </template>
                        <input type="hidden" name="discount_total" :value="u.discount_total">
                    </div>

                    {{-- Additional Charges --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-neutral-300">Additional Charges</span>
                            <a href="#" @click.prevent="mod.charges=!mod.charges" class="text-blue-600 hover:underline">
                                <span x-text="mod.charges ? 'Hide' : '+ Add'"></span>
                            </a>
                        </div>

                        <template x-if="mod.charges">
                            <div class="space-y-2">
                                <div class="flex gap-2">
                                    <select x-model="chargePickerId"
                                            class="border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                                        <option value="">-- Select a charge --</option>
                                        <template x-for="c in chargesData" :key="c.id">
                                            <option :value="c.id" x-text="c.name + ' (' + money(c.amount) + ')'"></option>
                                        </template>
                                    </select>
                                    <button type="button" @click="addCharge()"
                                            class="px-3 py-1 rounded bg-gray-800 text-white disabled:opacity-50"
                                            :disabled="!chargePickerId">
                                        Add
                                    </button>
                                </div>

                                <div class="rounded border border-gray-200 dark:border-neutral-700 divide-y">
                                    <template x-if="chargesSelected.length===0">
                                        <div class="px-3 py-2 text-sm text-gray-500 dark:text-neutral-400">No additional charges added.</div>
                                    </template>

                                    <template x-for="(r, i) in chargesSelected" :key="r._k">
                                        <div class="flex items-center justify-between px-3 py-2">
                                            <div class="flex-1">
                                                <div class="font-medium" x-text="r.name"></div>
                                                <div class="text-xs text-gray-500">Editable for this invoice</div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input type="number" step="0.01" min="0" x-model.number="r.amount" @input="calc()"
                                                       class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-right">
                                                <button type="button" class="text-red-600 hover:underline" @click="removeCharge(i)">Remove</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-neutral-300">Charges Total</span>
                            <span class="font-medium" x-text="money(chargeTotal())"></span>
                        </div>

                        <input type="hidden" name="charge_total" :value="chargeTotal()">
                        <input type="hidden" name="charges_json" id="charges_json">
                    </div>

                    {{-- TCS --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="tcs" x-model="u.tcsEnabled" class="rounded border-gray-300 dark:border-neutral-700">
                            <label for="tcs" class="text-gray-700 dark:text-neutral-300">Apply TCS</label>
                        </div>
                        <div class="flex items-center gap-1">
                            <input type="number" min="0" max="100" step="0.01" x-model.number="u.tcs_percent" @input="calc()"
                                   class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                            <span class="text-gray-700 dark:text-neutral-300">%</span>
                        </div>
                        <input type="hidden" name="tcs_percent" :value="u.tcsEnabled ? u.tcs_percent : 0">
                    </div>

                    {{-- Round Off --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="round" x-model="u.autoRound" class="rounded border-gray-300 dark:border-neutral-700">
                            <label for="round" class="text-gray-700 dark:text-neutral-300">Auto Round Off</label>
                        </div>
                        <div class="flex items-center gap-1">
                            <select x-model="u.roundSign" class="border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                                <option value="+">+ Add</option><option value="-">- Less</option>
                            </select>
                            <input type="number" step="0.01" x-model.number="u.round_off" @input="calc()"
                                   class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                        </div>
                        <input type="hidden" name="round_off"
                               :value="u.autoRound ? (Math.round(totalBeforeExtras()-Number(u.discount_total||0)+chargeTotal()+tcsAmount()) - (totalBeforeExtras()-Number(u.discount_total||0)+chargeTotal()+tcsAmount())).toFixed(2) : u.round_off">
                    </div>

                    <div class="flex items-center justify-between font-semibold text-lg pt-3">
                        <span>Total Amount</span><span x-text="money(grandTotal())"></span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span>Amount Received</span>
                        <div class="flex items-center gap-2">
                            <input type="number" step="0.01" min="0" x-model.number="u.received" @input="calc()"
                                   class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                            <select name="payment_method" class="border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                                <option>Cash</option><option>UPI</option><option>Card</option><option>NEFT</option>
                            </select>
                        </div>
                        <input type="hidden" name="amount_received" :value="u.received">
                    </div>

                    <div class="flex items-center justify-between text-green-600 dark:text-green-400">
                        <span>Balance Amount</span><span x-text="money(balance())"></span>
                    </div>
                </div>
            </div>

            {{-- Hidden: all line items as JSON --}}
            <input type="hidden" id="items_json" name="items_json">
            <div class="text-right">
                <button @click="$refs.form.requestSubmit()" class="mt-3 px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Save</button>
            </div>
        </form>

        {{-- Client (Party) Create Modal --}}
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
                            <input x-model="clientForm.name"
                                   class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                            bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100" required>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Mobile *</label>
                            <input x-model="clientForm.mobile"
                                   class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                            bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100" required>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">GSTIN</label>
                            <input x-model="clientForm.gstin"
                                   class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                            bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">PAN</label>
                            <input x-model="clientForm.pan"
                                   class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                            bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm mb-1">State</label>
                            <input x-model="clientForm.state"
                                   class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                            bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm mb-1">Address</label>
                            <textarea x-model="clientForm.address" rows="2"
                                      class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                               bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100"></textarea>
                        </div>
                    </div>
                    <div class="mt-2 flex justify-end gap-2">
                        <button type="button"
                                class="px-3 py-2 rounded border border-gray-300 dark:border-neutral-700
                           bg-white dark:bg-neutral-900 hover:bg-gray-50 dark:hover:bg-neutral-800"
                                @click="mod.client=false">Cancel</button>
                        <button type="submit" class="px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Save Party</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- Alpine component --}}
    <script>
        function invoiceForm(){
            const CLIENTS  = JSON.parse(document.getElementById('clients-json')?.textContent || '[]');
            const ITEMS    = JSON.parse(document.getElementById('items-json')?.textContent || '[]');
            const CHARGES  = JSON.parse(document.getElementById('charges-json')?.textContent || '[]');

            return {
                // masters
                clients: CLIENTS,
                itemsData: ITEMS,
                chargesData: CHARGES,

                clientId: '',

                // modals/toggles
                mod: { client:false, charges:false },

                // quick client form
                clientForm: { name:'', mobile:'', gstin:'', pan:'', state:'', address:'' },

                // header
                hdr: { date: '{{ $today }}', terms: 30, due: '' },

                // prefix & number
                basePrefix: @js($basePrefix),
                computedPrefix: @js($suggestedPrefix),
                prefixEditable: false,
                invoiceNo: @js($initialInvoiceNo),

                // items
                items: [{ _k: Date.now(), item_id:null, description:'', sac:'', qty:1, price:0, making_charge:0, discount:0, tax_percent:0 }],

                // charges
                chargePickerId: '',
                chargesSelected: [],

                // user totals/settings
                u: { discountEnabled:false, discount_total:0,
                    tcsEnabled:false, tcs_percent:0,
                    autoRound:false, roundSign:'+', round_off:0, received:0 },

                init(){
                    this.$watch('hdr.date',  ()=>{ this.refreshPrefix(); this.calcDue(); this.fetchPreview(); });
                    this.$watch('computedPrefix', ()=>{ this.fetchPreview(); });
                    this.refreshPrefix(); this.calcDue(); this.calc();
                    this.fetchPreview();
                },

                // number preview
                async fetchPreview(){
                    try{
                        const res = await fetch('{{ route('invoices.preview-number') }}', {
                            method: 'POST',
                            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                            body: JSON.stringify({ invoice_date: this.hdr.date, invoice_prefix: this.computedPrefix })
                        });
                        const data = await res.json();
                        if(data?.ok && data?.number){ this.invoiceNo = data.number; }
                    }catch(e){}
                },

                // client quick-add
                openClientModal(){ this.mod.client = true; this.clientForm = { name:'', mobile:'', gstin:'', pan:'', state:'', address:'' }; },
                async saveClient(){
                    try{
                        const res = await fetch('{{ route('clients.quick-store') }}', {
                            method: 'POST',
                            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                            body: JSON.stringify(this.clientForm)
                        });
                        const data = await res.json();
                        if(!data.ok) throw new Error('Failed');
                        this.clients.push(data.client);
                        this.clientId = String(data.client.id);
                        this.mod.client = false;
                    }catch(e){ alert('Could not save party. Please check details.'); }
                },

                // prefix & due
                refreshPrefix(){
                    if (this.prefixEditable) return;
                    const d = new Date(this.hdr.date || new Date());
                    const yy = d.getFullYear() % 100; const mm = d.getMonth() + 1;
                    const start = (mm >= 4) ? yy : (yy - 1);
                    const a = String((start+100)%100).padStart(2,'0');
                    const b = String((start+1+100)%100).padStart(2,'0');
                    const fy = `${a}-${b}`;
                    const base = (this.basePrefix || 'RV/SL').replace(/\/+$/,'');
                    this.computedPrefix = `${base}/${fy}/`;
                },
                calcDue(){
                    if(!this.hdr.date) return;
                    const d=new Date(this.hdr.date);
                    d.setDate(d.getDate() + (parseInt(this.hdr.terms||0)));
                    this.hdr.due = d.toISOString().slice(0,10);
                },

                // items handlers
                pickItem(i,id){
                    const it=this.itemsData.find(x=> String(x.id)===String(id));
                    if(!it) return;
                    const r=this.items[i];
                    r.item_id=it.id;
                    r.description=it.description||it.name;
                    r.price=Number(it.price)||0;
                    r.making_charge=Number(it.making_charge)||0;
                    r.tax_percent=Number(it.tax_rate)||0;
                    this.calc();
                },
                add(){ this.items.push({_k: Date.now()+Math.random(), item_id:null, description:'', sac:'', qty:1, price:0, making_charge:0, discount:0, tax_percent:0}); this.calc(); },
                remove(i){ this.items.splice(i,1); this.calc(); },

                // charges
                addCharge(){
                    const id = String(this.chargePickerId || '');
                    if(!id) return;
                    const found = this.chargesData.find(c => String(c.id) === id);
                    if(!found) return;

                    const exists = this.chargesSelected.find(x => String(x.id) === id);
                    if(exists) { this.chargePickerId=''; return; }

                    this.chargesSelected.push({_k: Date.now()+Math.random(), id: found.id, name: found.name, amount: Number(found.amount||0)});
                    this.chargePickerId=''; this.calc();
                },
                removeCharge(i){ this.chargesSelected.splice(i,1); this.calc(); },
                chargeTotal(){ return Number(this.chargesSelected.reduce((s, r) => s + Number(r.amount||0), 0).toFixed(2)); },

                // totals
                lineAmount(r){
                    const base = Math.max(0, (Number(r.qty||0) * (Number(r.price||0)+Number(r.making_charge||0))) - Number(r.discount||0));
                    const tax  = base * (Number(r.tax_percent||0)/100);
                    return Number((base+tax).toFixed(2));
                },
                subtotal(){ return Number(this.items.reduce((s,r)=> s + Math.max(0,(r.qty||0)*((r.price||0)+(r.making_charge||0))-(r.discount||0)),0).toFixed(2)); },
                taxTotal(){ return Number(this.items.reduce((s,r)=>{ const b=Math.max(0,(r.qty||0)*((r.price||0)+(r.making_charge||0))-(r.discount||0)); return s + b*((r.tax_percent||0)/100); },0).toFixed(2)); },
                totalBeforeExtras(){ return this.subtotal()+this.taxTotal(); },
                tcsAmount(){ if(!this.u.tcsEnabled) return 0; const base=Math.max(0,this.subtotal()+this.taxTotal()-Number(this.u.discount_total||0)); return Number((base*((this.u.tcs_percent||0)/100)).toFixed(2)); },
                grandTotal(){
                    let t=this.totalBeforeExtras()-Number(this.u.discount_total||0)+this.chargeTotal()+this.tcsAmount();
                    if(this.u.autoRound){ const r=Math.round(t); this.u.round_off=Math.abs(r-t).toFixed(2); this.u.roundSign=(r-t)>=0?'+':'-'; t=r; }
                    else { t = t + (Number(this.u.round_off||0) * (this.u.roundSign==='+'?1:-1)); }
                    return Number(t.toFixed(2));
                },
                balance(){ return Math.max(0, Number((this.grandTotal() - Number(this.u.received||0)).toFixed(2))); },
                calc(){ this.grandTotal(); },

                // submit
                beforeSubmit(){
                    const payload = this.items.map(r=>({
                        item_id: r.item_id ?? null,
                        description: r.description || '',
                        sac: r.sac || '',
                        qty: Number(r.qty||0),
                        price: Number(r.price||0),
                        making_charge: Number(r.making_charge||0), // ✅ FIX: correct key
                        discount: Number(r.discount||0),
                        tax_percent: Number(r.tax_percent||0),
                        amount: this.lineAmount(r)
                    }));
                    document.getElementById('items_json').value = JSON.stringify(payload);

                    document.getElementById('charges_json').value = JSON.stringify(
                        this.chargesSelected.map(r => ({ id: r.id, name: r.name, amount: Number(r.amount||0) }))
                    );

                    this.$refs.form.submit();
                },

                money(v){ return '₹ ' + Number(v||0).toFixed(2); }
            }
        }
    </script>
</x-layouts.app>
