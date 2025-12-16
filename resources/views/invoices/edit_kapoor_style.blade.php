<x-layouts.app :title="__('Edit Sales Invoice')">
    <div x-data="invoiceFormEdit()" x-init="init()" class="space-y-4 max-w-7xl mx-auto px-3 sm:px-6 py-4">

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
        <script type="application/json" id="prefill-items-json">{!! $prefillItemsJson !!}</script>
        <script type="application/json" id="prefill-payment-json">{!! $prefillPaymentJson !!}</script>

        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-neutral-100">Edit Sales Invoice</h1>
            <button @click="$refs.form.requestSubmit()"
                    class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                Update
            </button>
        </div>

        <form x-ref="form" method="POST" action="{{ route('invoices.update', $invoice->id) }}" @submit.prevent="beforeSubmit">
            @csrf
            @method('PUT')

            {{-- TOP PANELS --}}
            <div class="grid lg:grid-cols-4 gap-4">

                {{-- LEFT: Bill To --}}
                <div class="lg:col-span-2 p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                    <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100 mb-2">Bill To</div>

                    <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300 mb-1">Party</label>

                    <div class="flex gap-2">
                        <select x-ref="clientSelect"
                                name="client_id"
                                x-model="clientId"
                                @change="syncParty()"
                                required
                                class="flex-1 border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                                   bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                            <option value="">-- Select Client --</option>
                            <template x-for="c in clients" :key="c.id">
                                <option :value="String(c.id)"
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

                    {{-- details --}}
                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <div class="text-gray-500 dark:text-neutral-400">Name</div>
                            <div class="font-semibold" x-text="party.name || '-'"></div>
                        </div>

                        <div>
                            <div class="text-gray-500 dark:text-neutral-400">Phone</div>
                            <div class="font-semibold" x-text="party.mobile || '-'"></div>
                        </div>

                        <div class="col-span-2">
                            <div class="text-gray-500 dark:text-neutral-400">Address</div>
                            <div class="font-semibold" x-text="party.address || '-'"></div>
                        </div>

                        <div>
                            <div class="text-gray-500 dark:text-neutral-400">State</div>
                            <div class="font-semibold" x-text="party.state || '-'"></div>
                        </div>

                        <div>
                            <div class="text-gray-500 dark:text-neutral-400">State Code</div>
                            <div class="font-semibold" x-text="party.state_code || '-'"></div>
                        </div>

                        <div class="col-span-2">
                            <div class="text-gray-500 dark:text-neutral-400">GST Type</div>
                            <div class="font-semibold"
                                 :class="isIntra() ? 'text-green-600' : 'text-purple-600'"
                                 x-text="isIntra() ? 'Intra State (CGST+SGST)' : 'Inter State (IGST)'"></div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Invoice meta --}}
                <div class="lg:col-span-2 p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                    <div class="grid md:grid-cols-2 gap-3">

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Date</label>
                            {{-- ✅ DATE FIX: must be YYYY-MM-DD --}}
                            <input type="date"
                                   name="invoice_date"
                                   x-model="hdr.date"
                                   required
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

            {{-- ITEMS TABLE --}}
            <div class="border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-separate border-spacing-0">
                        <thead class="bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200">
                        <tr class="[&>th]:px-3 [&>th]:py-2 [&>th]:font-medium text-left text-xs">
                            <th>S.No.</th>
                            <th>Description</th>
                            <th>HSN</th>
                            <th class="text-center">Qty</th>
                            <th>Making</th>
                            <th>Gold Rate</th>
                            <th>Silver Rate</th>
                            <th>Silver Wt</th>
                            <th>Gold Wt</th>
                            <th>Gem Ct</th>
                            <th>Dia Ct</th>
                            <th>Tax %</th>
                            <th>Amount</th>
                            <th></th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                        <template x-for="(row, i) in lines" :key="row._k">
                            <tr>
                                <td class="px-3 py-2 text-center text-xs" x-text="i+1"></td>

                                <td class="px-3 py-2">
                                    <select class="w-full border rounded px-2 py-1 mb-1 text-xs"
                                            x-model="row.item_id"
                                            @change="pickItem(i, row.item_id)">
                                        <option value="">-- Select Item --</option>
                                        <template x-for="it in itemsData" :key="it.id">
                                            <option :value="String(it.id)"
                                                    x-text="it.sku ? (it.name + ' (' + it.sku + ')') : it.name"></option>
                                        </template>
                                    </select>

                                    <input type="text" x-model="row.description" class="w-full border rounded px-2 py-1 text-xs" required>
                                </td>

                                <td class="px-3 py-2">
                                    <input x-model="row.hsn" class="w-24 border rounded px-2 py-1 text-xs">
                                </td>

                                <td class="px-3 py-2 text-center">
                                    <input type="number" min="1" step="1" x-model.number="row.quantity" @input="calc()"
                                           class="w-20 border rounded px-2 py-1 text-xs text-center">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" x-model.number="row.making_rate" @input="calc()"
                                           class="w-24 border rounded px-2 py-1 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" x-model.number="row.gold_rate" @input="calc()"
                                           class="w-24 border rounded px-2 py-1 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" x-model.number="row.silver_rate" @input="calc()"
                                           class="w-24 border rounded px-2 py-1 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.001" min="0" x-model.number="row.silver_wt" @input="calc()"
                                           class="w-20 border rounded px-2 py-1 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.001" min="0" x-model.number="row.gold_wt" @input="calc()"
                                           class="w-20 border rounded px-2 py-1 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.001" min="0" x-model.number="row.gemstone_wt" @input="calc()"
                                           class="w-20 border rounded px-2 py-1 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.001" min="0" x-model.number="row.diamond_wt" @input="calc()"
                                           class="w-20 border rounded px-2 py-1 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" max="100" x-model.number="row.tax_percent" @input="calc()"
                                           class="w-16 border rounded px-2 py-1 text-xs">
                                </td>

                                <td class="px-3 py-2">
                                    <input readonly :value="lineAmount(row).toFixed(2)"
                                           class="w-28 bg-gray-50 border rounded px-2 py-1 text-xs">
                                </td>

                                <td class="px-3 py-2 text-right">
                                    <button type="button" @click="remove(i)" class="text-red-600 text-lg leading-none">×</button>
                                </td>
                            </tr>
                        </template>

                        <tr>
                            <td colspan="14" class="px-3 py-2">
                                <button type="button" @click="add()" class="text-blue-600 hover:underline text-sm">
                                    + Add Item
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- BOTTOM --}}
            <div class="grid lg:grid-cols-2 gap-4">

                {{-- Left --}}
                <div class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 space-y-3">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="hdr.reverse_charge" class="rounded">
                        <span class="text-sm">Reverse Charge (Y/N)</span>
                    </div>
                    <input type="hidden" name="reverse_charge" :value="hdr.reverse_charge ? 1 : 0">

                    <div>
                        <label class="block text-sm font-medium">Terms</label>
                        <textarea name="terms" rows="4" class="w-full border rounded px-3 py-2">{{ old('terms', $invoice->terms ?? $defaultTerms) }}</textarea>
                    </div>

                    {{-- Payment --}}
                    <div class="space-y-2">
                        <div class="flex justify-between gap-3">
                            <span>Cash</span>
                            <input type="number" step="0.01" min="0" name="pay_cash" x-model.number="pay.cash" @input="calc()"
                                   class="w-56 border rounded px-2 py-1 text-right">
                        </div>

                        <div class="flex justify-between gap-3">
                            <span>UPI / Online</span>
                            <input type="number" step="0.01" min="0" name="pay_upi" x-model.number="pay.upi" @input="calc()"
                                   class="w-56 border rounded px-2 py-1 text-right">
                        </div>

                        <div class="flex justify-between gap-3">
                            <span>Card</span>
                            <input type="number" step="0.01" min="0" name="pay_card" x-model.number="pay.card" @input="calc()"
                                   class="w-56 border rounded px-2 py-1 text-right">
                        </div>

                        <div class="flex justify-between gap-3">
                            <span>Cheque</span>
                            <input type="number" step="0.01" min="0" name="pay_cheque" x-model.number="pay.cheque" @input="calc()"
                                   class="w-56 border rounded px-2 py-1 text-right">
                        </div>

                        <div class="flex justify-between gap-3">
                            <span>Credit Excess</span>
                            <input type="number" step="0.01" min="0" name="credit_sales_excess" x-model.number="pay.credit_excess" @input="calc()"
                                   class="w-56 border rounded px-2 py-1 text-right">
                        </div>

                        <div class="flex justify-between gap-3">
                            <span>Advance</span>
                            <input type="number" step="0.01" min="0" name="advance_amount" x-model.number="pay.advance" @input="calc()"
                                   class="w-56 border rounded px-2 py-1 text-right">
                        </div>

                        <div class="grid md:grid-cols-2 gap-2 pt-2">
                            <input type="text" name="online_mode" x-model="pay.online_mode" placeholder="Online mode"
                                   class="w-full border rounded px-2 py-1">
                            <input type="text" name="online_ref" x-model="pay.online_ref" placeholder="UTR/Txn"
                                   class="w-full border rounded px-2 py-1">
                            <input type="text" name="upi_id" x-model="pay.upi_id" placeholder="UPI ID"
                                   class="w-full border rounded px-2 py-1">
                            <input type="text" name="card_last4" x-model="pay.card_last4" placeholder="Card last4"
                                   class="w-full border rounded px-2 py-1">
                            <input type="text" name="card_ref" x-model="pay.card_ref" placeholder="Card ref"
                                   class="w-full border rounded px-2 py-1">
                            <input type="text" name="cheque_no" x-model="pay.cheque_no" placeholder="Cheque no"
                                   class="w-full border rounded px-2 py-1">
                            <input type="text" name="bank_name" x-model="pay.bank_name" placeholder="Bank name"
                                   class="w-full border rounded px-2 py-1">
                        </div>
                    </div>
                </div>

                {{-- Right totals --}}
                <div class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 space-y-2">

                <div class="flex justify-between font-semibold">
                        <span>Balance Amount</span>
                        <span x-text="money(balanceAmount())"></span>
                    </div>

                    <div class="flex justify-between">
                        <span>CGST</span>
                        <span x-text="money(cgst())"></span>
                    </div>

                    <div class="flex justify-between">
                        <span>SGST</span>
                        <span x-text="money(sgst())"></span>
                    </div>

                    <div class="flex justify-between">
                        <span>IGST</span>
                        <span x-text="money(igst())"></span>
                    </div>

                    <div class="flex justify-between font-semibold text-lg pt-3">
                        <span>Total Value</span>
                        <span x-text="money(grandTotal())"></span>
                    </div>

                    {{-- send to backend --}}
                    <input type="hidden" name="cgst_amount" :value="cgst()">
                    <input type="hidden" name="sgst_amount" :value="sgst()">
                    <input type="hidden" name="igst_amount" :value="igst()">
                </div>

            </div>

            {{-- Hidden JSON --}}
            <input type="hidden" id="items_json" name="items_json">

            <div class="text-right">
                <button type="submit"
                        class="mt-3 px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                    Update
                </button>
            </div>

        </form>
    </div>

    <script>
        function invoiceFormEdit(){

            const CLIENTS      = JSON.parse(document.getElementById('clients-json')?.textContent || '[]');
            const ITEMS        = JSON.parse(document.getElementById('items-json')?.textContent || '[]');
            const PREFILL      = JSON.parse(document.getElementById('prefill-items-json')?.textContent || '[]');
            const PREFILL_PAY  = JSON.parse(document.getElementById('prefill-payment-json')?.textContent || '{}');

            const BIZ_STATE_CODE = @js($businessStateCode ?? '');

            return {
                clients: CLIENTS,
                itemsData: ITEMS,

                clientId: String(@js($invoice->client_id ?? '')),
                party: { name:'', mobile:'', address:'', state:'', state_code:'', gstin:'' },

                hdr: {
                    // ✅ DATE FIX (server already sends correct format too)
                    date: @js(\Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d')),
                    gst_no: @js($invoice->gst_no ?? ''),
                    transport_mode: @js($invoice->transport_mode ?? 'By Hand'),
                    reverse_charge: @js((bool)$invoice->reverse_charge),
                },

                computedPrefix: @js($suggestedPrefix),
                invoiceNo: @js($invoice->invoice_number),

                pay: {
                    cash: Number(PREFILL_PAY.cash ?? 0),
                    upi: Number(PREFILL_PAY.upi ?? 0),
                    card: Number(PREFILL_PAY.card ?? 0),
                    cheque: Number(PREFILL_PAY.cheque ?? 0),
                    credit_excess: Number(PREFILL_PAY.credit_excess ?? 0),
                    advance: Number(PREFILL_PAY.advance ?? 0),

                    online_mode: PREFILL_PAY.online_mode ?? '',
                    online_ref: PREFILL_PAY.online_ref ?? '',
                    upi_id: PREFILL_PAY.upi_id ?? '',

                    card_last4: PREFILL_PAY.card_last4 ?? '',
                    card_ref: PREFILL_PAY.card_ref ?? '',

                    cheque_no: PREFILL_PAY.cheque_no ?? '',
                    bank_name: PREFILL_PAY.bank_name ?? ''
                },

                lines: (Array.isArray(PREFILL) && PREFILL.length ? PREFILL : [{
                    item_id:null, description:'', hsn:'', quantity:1,
                    making_rate:0, gold_rate:0, silver_rate:0,
                    gold_wt:0, silver_wt:0, gemstone_wt:0, diamond_wt:0, tax_percent:0
                }]).map((r, idx) => ({
                    _k: Date.now()+idx+Math.random(),
                    item_id: r.item_id ? String(r.item_id) : null,
                    description: r.description ?? '',
                    hsn: (r.hsn ?? r.hsn_code ?? r.sac_code ?? ''),
                    quantity: Number(r.quantity ?? 1) || 1,
                    making_rate: Number(r.making_rate ?? 0),
                    gold_rate: Number(r.gold_rate ?? 0),
                    silver_rate: Number(r.silver_rate ?? 0),
                    gold_wt: Number(r.gold_wt ?? 0),
                    silver_wt: Number(r.silver_wt ?? 0),
                    gemstone_wt: Number(r.gemstone_wt ?? r.gemstone_wt_ct ?? 0),
                    diamond_wt: Number(r.diamond_wt ?? r.diamond_wt_ct ?? 0),
                    tax_percent: Number(r.tax_percent ?? 0),
                })),

                init(){
                    this.$nextTick(() => {
                        this.forceClientSelect();
                        this.syncParty();
                        this.calc();
                    });
                },

                forceClientSelect(){
                    this.$nextTick(() => {
                        if (this.$refs.clientSelect) {
                            this.$refs.clientSelect.value = String(this.clientId || '');
                        }
                    });
                },

                syncParty(){
                    const c = this.clients.find(x => String(x.id) === String(this.clientId));
                    this.party = c ? {...c} : { name:'', mobile:'', address:'', state:'', state_code:'', gstin:'' };
                    this.calc();
                },

                keyCode(v){
                    let s = String(v || '').trim();
                    s = s.replace(/\D+/g,'');
                    s = s.replace(/^0+/,'');
                    return s;
                },

                // ✅ ONLY STATE CODE CHECK
                isIntra(){
                    const biz   = this.keyCode(BIZ_STATE_CODE);
                    const party = this.keyCode(this.party.state_code || this.party.code || '');
                    if(!biz || !party) return false; // missing => IGST
                    return biz === party;
                },

                // calc
                lineBase(r){
                    const qty     = Math.max(1, Number(r.quantity || 1));
                    const goldAmt = Number(r.gold_wt||0)   * Number(r.gold_rate||0);
                    const silvAmt = Number(r.silver_wt||0) * Number(r.silver_rate||0);
                    const making  = Number(r.making_rate||0);
                    return Math.max(0, Number(((goldAmt + silvAmt + making) * qty).toFixed(2)));
                },

                lineTax(r){
                    const base = this.lineBase(r);
                    const pct  = Number(r.tax_percent||0);
                    return Number((base * (pct/100)).toFixed(2));
                },

                lineAmount(r){
                    return Number((this.lineBase(r) + this.lineTax(r)).toFixed(2));
                },

                subtotal(){
                    return Number(this.lines.reduce((s,r)=> s + this.lineBase(r), 0).toFixed(2));
                },

                taxTotal(){
                    return Number(this.lines.reduce((s,r)=> s + this.lineTax(r), 0).toFixed(2));
                },

                cgst(){ return this.isIntra() ? Number((this.taxTotal()/2).toFixed(2)) : 0; },
                sgst(){ return this.isIntra() ? Number((this.taxTotal()/2).toFixed(2)) : 0; },
                igst(){ return this.isIntra() ? 0 : Number(this.taxTotal().toFixed(2)); },

                grandTotal(){
                    return Number((this.subtotal() + this.taxTotal()).toFixed(2));
                },

                balanceAmount(){
                    const received =
                        Number(this.pay.cash||0) +
                        Number(this.pay.upi||0) +
                        Number(this.pay.card||0) +
                        Number(this.pay.cheque||0);

                    const advance = Number(this.pay.advance||0);
                    const credit  = Number(this.pay.credit_excess||0);

                    return Math.max(0, Number((this.grandTotal() - received - advance - credit).toFixed(2)));
                },

                calc(){ this.grandTotal(); },

                add(){
                    this.lines.push({
                        _k: Date.now()+Math.random(),
                        item_id: null,
                        description: '',
                        hsn: '',
                        quantity: 1,
                        making_rate: 0,
                        gold_rate: 0,
                        silver_rate: 0,
                        gold_wt: 0,
                        silver_wt: 0,
                        gemstone_wt: 0,
                        diamond_wt: 0,
                        tax_percent: 0,
                    });
                    this.calc();
                },

                remove(i){ this.lines.splice(i,1); this.calc(); },

                pickItem(i, id){
                    const it = this.itemsData.find(x => String(x.id) === String(id));
                    if(!it) return;

                    const r = this.lines[i];

                    r.item_id     = String(it.id);
                    r.description = it.description || it.name || '';
                    r.hsn         = it.sac || r.hsn || '';

                    r.quantity    = Math.max(1, Number(r.quantity ?? 1));

                    r.gold_wt     = Number(it.gold_weight ?? r.gold_wt ?? 0);
                    r.silver_wt   = Number(it.silver_weight ?? r.silver_wt ?? 0);

                    r.making_rate = Number(it.making_charge ?? r.making_rate ?? 0);
                    r.tax_percent = Number(it.tax_rate ?? r.tax_percent ?? 0);

                    this.calc();
                },

                beforeSubmit(){
                    const payload = this.lines.map(r => ({
                        item_id: r.item_id ?? null,
                        description: r.description || '',
                        hsn: r.hsn || '',
                        quantity: Math.max(1, Number(r.quantity ?? 1)),

                        making_rate: Number(r.making_rate||0),
                        gold_rate: Number(r.gold_rate||0),
                        silver_rate: Number(r.silver_rate||0),

                        silver_wt: Number(r.silver_wt||0),
                        gold_wt: Number(r.gold_wt||0),
                        gemstone_wt: Number(r.gemstone_wt||0),
                        diamond_wt: Number(r.diamond_wt||0),

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
