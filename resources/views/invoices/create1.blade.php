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

        <div class="flex items-center justify-between ">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-neutral-100">Create Sales Invoice</h1>
            <button @click="$refs.form.requestSubmit()"
                    class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                Save
            </button>
        </div>

        <form x-ref="form" method="POST" action="{{ route('invoices.store') }}" @submit.prevent="beforeSubmit">
            @csrf

            {{-- Client + Right Panel --}}
            <div class="grid lg:grid-cols-4 gap-4">
                {{-- Bill To --}}
                <div class="lg:col-span-2 p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-neutral-300">
                        Bill To (Client)
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
                </div>

                {{-- RIGHT PANEL --}}
                <div class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 space-y-3 lg:col-span-2">
                    <div class="grid md:grid-cols-3 gap-3">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Invoice Prefix</label>
                            <input :value="computedPrefix" readonly
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                          bg-gray-50 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200 text-sm">
                            <input type="hidden" name="invoice_prefix" :value="computedPrefix">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Invoice Number</label>
                            <input :value="invoiceNo" readonly
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                          bg-gray-50 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Sales Invoice Date</label>
                            <input type="date" name="invoice_date" x-model="hdr.date"
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                          bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm"
                                   value="{{ $today }}" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-separate border-spacing-0">
                        <thead class="bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200">
                        <tr class="[&>th]:px-3 [&>th]:py-2 [&>th]:font-medium text-left text-xs">
                            <th>No</th>
                            <th>Item / Description</th>
                            <th>HSN/SAC</th>
                            <th>Qty</th>

                            <th>Gold Wt (g)</th>
                            <th>Gold Rate</th>
                            <th>Gold Amt (₹)</th>

                            <th>Silver Wt (g)</th>
                            <th>Silver Rate</th>
                            <th>Silver Amt (₹)</th>

                            <th>Stone Wt (g)</th>
                            <th>Stone Charges (₹)</th>

                            <th>Diamond Wt (g)</th>
                            <th>Diamond Charges (₹)</th>

                            <th>Making Charge</th>
                            <th>Discount</th>
                            <th>Tax %</th>
                            <th>Amount (₹)</th>
                            <th></th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700 text-gray-900 dark:text-neutral-100">
                        <template x-for="(row, i) in items" :key="row._k">
                            <tr>
                                <td class="px-3 py-2 text-center text-xs" x-text="i+1"></td>

                                {{-- Item + Desc --}}
                                <td class="px-3 py-2">
                                    <select class="w-full border rounded px-2 py-1 mb-1 border-gray-300 dark:border-neutral-700
                                                   bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-xs"
                                            @change="pickItem(i, $event.target.value)">
                                        <option value="">-- Select Item --</option>
                                        <template x-for="it in itemsData" :key="it.id">
                                            <option :value="it.id"
                                                    x-text="it.sku ? (it.name + ' (' + it.sku + ')') : it.name"></option>
                                        </template>
                                    </select>

                                    <input type="text" x-model="row.description" placeholder="Description" required
                                           class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-xs">

                                    <p class="mt-1 text-[11px] text-gray-500">
                                        <span x-show="row.gold_purity">Gold: <span x-text="row.gold_purity"></span></span>
                                        <span x-show="row.silver_purity" class="ml-2">Silver: <span x-text="row.silver_purity"></span></span>
                                    </p>
                                </td>

                                {{-- HSN/SAC --}}
                                <td class="px-3 py-2">
                                    <input x-model="row.sac"
                                           class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Qty --}}
                                <td class="px-3 py-2">
                                    <input type="number" min="1" x-model.number="row.qty" @input="calc()"
                                           class="w-16 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Gold Wt --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.001" min="0" x-model.number="row.gold_weight"
                                           @input="recalcGold(i)"
                                           class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Gold Rate --}}
                                <td class="px-3 py-2">
                                    <input readonly :value="Number(row.gold_rate||0).toFixed(2)"
                                           class="w-24 bg-gray-50 dark:bg-neutral-800 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 text-xs">
                                </td>

                                {{-- Gold Amt --}}
                                <td class="px-3 py-2">
                                    <input readonly :value="Number(row.gold_amount||0).toFixed(2)"
                                           class="w-28 bg-gray-50 dark:bg-neutral-800 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 text-xs">
                                </td>

                                {{-- Silver Wt --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.001" min="0" x-model.number="row.silver_weight"
                                           @input="recalcSilver(i)"
                                           class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Silver Rate --}}
                                <td class="px-3 py-2">
                                    <input readonly :value="Number(row.silver_rate||0).toFixed(2)"
                                           class="w-24 bg-gray-50 dark:bg-neutral-800 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 text-xs">
                                </td>

                                {{-- Silver Amt --}}
                                <td class="px-3 py-2">
                                    <input readonly :value="Number(row.silver_amount||0).toFixed(2)"
                                           class="w-28 bg-gray-50 dark:bg-neutral-800 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 text-xs">
                                </td>

                                {{-- Stone Wt --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.001" min="0" x-model.number="row.stone_weight"
                                           @input="calc()"
                                           class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Stone Charges --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" x-model.number="row.stone_charges"
                                           @input="calc()"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Diamond Wt --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.001" min="0" x-model.number="row.diamond_weight"
                                           @input="calc()"
                                           class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Diamond Charges --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" x-model.number="row.diamond_charges"
                                           @input="calc()"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Making --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" x-model.number="row.making_charge"
                                           @input="calc()"
                                           class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Discount --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" x-model.number="row.discount"
                                           @input="calc()"
                                           class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Tax --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" max="100" x-model.number="row.tax_percent"
                                           @input="calc()"
                                           class="w-16 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Amount --}}
                                <td class="px-3 py-2">
                                    <input readonly :value="lineAmount(row).toFixed(2)"
                                           class="w-28 bg-gray-50 dark:bg-neutral-800 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 text-xs">
                                </td>

                                {{-- Remove --}}
                                <td class="px-3 py-2 text-right">
                                    <button type="button" @click="remove(i)" class="text-red-600 hover:underline text-lg leading-none">×</button>
                                </td>
                            </tr>
                        </template>

                        <tr>
                            <td colspan="19" class="px-3 py-2">
                                <button type="button" @click="add()" class="text-blue-600 hover:underline text-sm">
                                    + Add Item
                                </button>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Notes + Totals --}}
            <div class="grid lg:grid-cols-2 gap-4">
                {{-- Terms/Notes --}}
                <div class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Terms and Conditions</label>
                    <textarea name="terms" rows="4"
                              class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">{{ old('terms', $defaultTerms) }}</textarea>

                    <label class="mt-3 block text-sm font-medium text-gray-700 dark:text-neutral-300">Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm"></textarea>
                </div>

                {{-- Totals --}}
                <div class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">SUBTOTAL</span>
                        <span class="font-medium" x-text="money(subtotal())"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">TAX</span>
                        <span class="font-medium" x-text="money(taxTotal())"></span>
                    </div>

                    {{-- Discount total --}}
                    <div>
                        <a href="#" @click.prevent="u.discountEnabled=!u.discountEnabled" class="text-blue-600 hover:underline text-sm">
                            + Add Invoice Discount
                        </a>
                        <template x-if="u.discountEnabled">
                            <div class="mt-2 flex justify-between items-center">
                                <input type="number" min="0" step="0.01" x-model.number="u.discount_total" @input="calc()"
                                       class="w-32 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                                <span x-text="'- ' + money(u.discount_total)"></span>
                            </div>
                        </template>
                        <input type="hidden" name="discount_total" :value="u.discount_total">
                    </div>

                    {{-- Additional Charges --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-neutral-300">Additional Charges</span>
                            <a href="#" @click.prevent="mod.charges=!mod.charges" class="text-blue-600 hover:underline text-sm">
                                <span x-text="mod.charges ? 'Hide' : '+ Add'"></span>
                            </a>
                        </div>

                        <template x-if="mod.charges">
                            <div class="space-y-2">
                                <div class="flex gap-2">
                                    <select x-model="chargePickerId"
                                            class="border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                                        <option value="">-- Select a charge --</option>
                                        <template x-for="c in chargesData" :key="c.id">
                                            <option :value="c.id" x-text="c.name + ' (' + money(c.amount) + ')'"></option>
                                        </template>
                                    </select>
                                    <button type="button" @click="addCharge()"
                                            class="px-3 py-1 rounded bg-gray-800 text-white disabled:opacity-50 text-sm"
                                            :disabled="!chargePickerId">
                                        Add
                                    </button>
                                </div>

                                <div class="rounded border border-gray-200 dark:border-neutral-700 divide-y divide-gray-200 dark:divide-neutral-700">
                                    <template x-if="chargesSelected.length===0">
                                        <div class="px-3 py-2 text-sm text-gray-500 dark:text-neutral-400">No charges added.</div>
                                    </template>

                                    <template x-for="(r, ci) in chargesSelected" :key="r._k">
                                        <div class="flex items-center justify-between px-3 py-2">
                                            <div class="flex-1">
                                                <div class="font-medium" x-text="r.name"></div>
                                                <div class="text-xs text-gray-500">Editable for this invoice</div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input type="number" step="0.01" min="0" x-model.number="r.amount" @input="calc()"
                                                       class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-right text-sm">
                                                <button type="button" class="text-red-600 hover:underline text-sm" @click="removeCharge(ci)">
                                                    Remove
                                                </button>
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
                            <label for="tcs" class="text-gray-700 dark:text-neutral-300 text-sm">Apply TCS</label>
                        </div>
                        <div class="flex items-center gap-1">
                            <input type="number" min="0" max="100" step="0.01" x-model.number="u.tcs_percent" @input="calc()"
                                   class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                            <span class="text-gray-700 dark:text-neutral-300 text-sm">%</span>
                        </div>
                        <input type="hidden" name="tcs_percent" :value="u.tcsEnabled ? u.tcs_percent : 0">
                    </div>

                    {{-- Round off --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="round" x-model="u.autoRound" class="rounded border-gray-300 dark:border-neutral-700">
                            <label for="round" class="text-gray-700 dark:text-neutral-300 text-sm">Auto Round Off</label>
                        </div>
                        <div class="flex items-center gap-1">
                            <select x-model="u.roundSign"
                                    class="border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                                <option value="+">+ Add</option>
                                <option value="-">- Less</option>
                            </select>
                            <input type="number" step="0.01" x-model.number="u.round_off" @input="calc()"
                                   class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                        </div>
                        <input type="hidden" name="round_off"
                               :value="u.autoRound
                                        ? (Math.round(totalBeforeExtras()-Number(u.discount_total||0)+chargeTotal()+tcsAmount())
                                            - (totalBeforeExtras()-Number(u.discount_total||0)+chargeTotal()+tcsAmount())).toFixed(2)
                                        : u.round_off">
                    </div>

                    <div class="flex items-center justify-between font-semibold text-lg pt-3">
                        <span>Total Amount</span>
                        <span x-text="money(grandTotal())"></span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span>Amount Received</span>
                        <div class="flex items-center gap-2">
                            <input type="number" step="0.01" min="0" x-model.number="u.received" @input="calc()"
                                   class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                            <select name="payment_method"
                                    class="border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                                <option>Cash</option>
                                <option>UPI</option>
                                <option>Card</option>
                                <option>NEFT</option>
                            </select>
                        </div>
                        <input type="hidden" name="amount_received" :value="u.received">
                    </div>

                    <div class="flex items-center justify-between text-green-600 dark:text-green-400">
                        <span>Balance Amount</span>
                        <span x-text="money(balance())"></span>
                    </div>
                </div>
            </div>

            {{-- Hidden payloads --}}
            <input type="hidden" id="items_json" name="items_json">

            <div class="text-right">
                <button @click="$refs.form.requestSubmit()" class="mt-3 px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                    Save
                </button>
            </div>
        </form>

        {{-- Client Create Modal --}}
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
                        <div>
                            <label class="block text-sm mb-1">GSTIN</label>
                            <input x-model="clientForm.gstin" class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">PAN</label>
                            <input x-model="clientForm.pan" class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm mb-1">State</label>
                            <input x-model="clientForm.state" class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm mb-1">Address</label>
                            <textarea x-model="clientForm.address" rows="2"
                                      class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm"></textarea>
                        </div>
                    </div>

                    <div class="mt-2 flex justify-end gap-2">
                        <button type="button" class="px-3 py-2 rounded border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 hover:bg-gray-50 dark:hover:bg-neutral-800 text-sm"
                                @click="mod.client=false">
                            Cancel
                        </button>
                        <button type="submit" class="px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 text-sm">
                            Save Party
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- Alpine JS --}}
    <script>
        function invoiceForm(){
            const CLIENTS     = JSON.parse(document.getElementById('clients-json')?.textContent || '[]');
            const ITEMS       = JSON.parse(document.getElementById('items-json')?.textContent || '[]');
            const CHARGES     = JSON.parse(document.getElementById('charges-json')?.textContent || '[]');
            const METAL_RATES = JSON.parse(document.getElementById('metal-rates-json')?.textContent || '[]');

            return {
                clients: CLIENTS,
                itemsData: ITEMS,
                chargesData: CHARGES,
                metalRates: METAL_RATES,

                clientId: '',

                mod: { client:false, charges:false },
                clientForm: { name:'', mobile:'', gstin:'', pan:'', state:'', address:'' },

                hdr: { date: '{{ $today }}' },

                basePrefix: @js($basePrefix),
                computedPrefix: @js($suggestedPrefix),
                invoiceNo: @js($initialInvoiceNo),

                items: [{
                    _k: Date.now(),
                    item_id: null,
                    description: '',
                    sac: '',
                    qty: 1,

                    gold_weight: 0,
                    gold_purity: null,
                    gold_rate: 0,
                    gold_amount: 0,

                    silver_weight: 0,
                    silver_purity: null,
                    silver_rate: 0,
                    silver_amount: 0,

                    stone_weight: 0,
                    stone_charges: 0,

                    diamond_weight: 0,
                    diamond_charges: 0,

                    making_charge: 0,
                    discount: 0,
                    tax_percent: 0
                }],

                chargePickerId: '',
                chargesSelected: [],

                u: {
                    discountEnabled:false,
                    discount_total:0,
                    tcsEnabled:false,
                    tcs_percent:0,
                    autoRound:false,
                    roundSign:'+',
                    round_off:0,
                    received:0
                },

                init(){
                    this.$watch('hdr.date', ()=>{
                        this.refreshPrefix();
                        this.fetchPreview();
                    });
                    this.$watch('computedPrefix', ()=>{ this.fetchPreview(); });

                    this.refreshPrefix();
                    this.calc();
                    this.fetchPreview();
                },

                async fetchPreview(){
                    try{
                        const res = await fetch('{{ route('invoices.preview-number') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type':'application/json',
                                'X-CSRF-TOKEN':'{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                invoice_date: this.hdr.date,
                                invoice_prefix: this.computedPrefix
                            })
                        });
                        const data = await res.json();
                        if(data?.ok && data?.number){
                            this.invoiceNo = data.number;
                        }
                    }catch(e){}
                },

                openClientModal(){
                    this.mod.client = true;
                    this.clientForm = { name:'', mobile:'', gstin:'', pan:'', state:'', address:'' };
                },

                async saveClient(){
                    try{
                        const res = await fetch('{{ route('clients.quick-store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type':'application/json',
                                'X-CSRF-TOKEN':'{{ csrf_token() }}'
                            },
                            body: JSON.stringify(this.clientForm)
                        });
                        const data = await res.json();
                        if(!data.ok) throw new Error('Failed');
                        this.clients.push(data.client);
                        this.clientId = String(data.client.id);
                        this.mod.client = false;
                    }catch(e){
                        alert('Could not save party. Please check details.');
                    }
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

                // tolerant purity match
                findMetalRate(type, purity){
                    if(!type || !purity) return 0;
                    const t = String(type).toLowerCase().trim();
                    const pRaw = String(purity).trim();

                    const candidates = [];
                    candidates.push(pRaw);
                    candidates.push(pRaw.split('(')[0].trim());
                    candidates.push(pRaw.split(' ')[0].trim());

                    const unique = [...new Set(candidates.filter(Boolean))];

                    const rec = this.metalRates.find(r => {
                        const rt = String(r.metal_type).toLowerCase().trim();
                        if(rt !== t) return false;

                        const rp = String(r.purity ?? '').trim();
                        const rp1 = rp.split('(')[0].trim();
                        const rp2 = rp.split(' ')[0].trim();

                        return unique.includes(rp) || unique.includes(rp1) || unique.includes(rp2);
                    });

                    return rec ? Number(rec.rate_per_gram || 0) : 0;
                },

                pickItem(i, id){
                    const it = this.itemsData.find(x => String(x.id) === String(id));
                    if(!it) return;

                    const r = this.items[i];
                    r.item_id = it.id;
                    r.description = it.description || it.name;
                    r.sac = it.sac || '';

                    r.gold_weight = Number(it.gold_weight || 0) || 0;
                    r.gold_purity = it.gold_purity || null;
                    r.gold_rate   = this.findMetalRate('gold', r.gold_purity);
                    r.gold_amount = Number((r.gold_weight * r.gold_rate).toFixed(2));

                    r.silver_weight = Number(it.silver_weight || 0) || 0;
                    r.silver_purity = it.silver_purity || null;
                    r.silver_rate   = this.findMetalRate('silver', r.silver_purity);
                    r.silver_amount = Number((r.silver_weight * r.silver_rate).toFixed(2));

                    r.stone_weight  = Number(it.stone_weight || 0) || 0;
                    r.stone_charges = Number(it.stone_charges || 0) || 0;

                    r.diamond_weight  = Number(it.diamond_weight || 0) || 0;
                    r.diamond_charges = Number(it.diamond_charges || 0) || 0;

                    r.making_charge = Number(it.making_charge || 0) || 0;
                    r.tax_percent   = Number(it.tax_rate || 0) || 0;

                    this.calc();
                },

                recalcGold(i){
                    const r = this.items[i];
                    r.gold_rate = this.findMetalRate('gold', r.gold_purity);
                    r.gold_amount = Number((Number(r.gold_weight||0) * Number(r.gold_rate||0)).toFixed(2));
                    this.calc();
                },

                recalcSilver(i){
                    const r = this.items[i];
                    r.silver_rate = this.findMetalRate('silver', r.silver_purity);
                    r.silver_amount = Number((Number(r.silver_weight||0) * Number(r.silver_rate||0)).toFixed(2));
                    this.calc();
                },

                add(){
                    this.items.push({
                        _k: Date.now()+Math.random(),
                        item_id: null,
                        description: '',
                        sac: '',
                        qty: 1,

                        gold_weight: 0,
                        gold_purity: null,
                        gold_rate: 0,
                        gold_amount: 0,

                        silver_weight: 0,
                        silver_purity: null,
                        silver_rate: 0,
                        silver_amount: 0,

                        stone_weight: 0,
                        stone_charges: 0,

                        diamond_weight: 0,
                        diamond_charges: 0,

                        making_charge: 0,
                        discount: 0,
                        tax_percent: 0
                    });
                    this.calc();
                },

                remove(i){
                    this.items.splice(i, 1);
                    this.calc();
                },

                addCharge(){
                    const id = String(this.chargePickerId || '');
                    if(!id) return;

                    const found = this.chargesData.find(c => String(c.id) === id);
                    if(!found) return;

                    if(this.chargesSelected.find(x => String(x.id) === id)) {
                        this.chargePickerId = '';
                        return;
                    }

                    this.chargesSelected.push({
                        _k: Date.now()+Math.random(),
                        id: found.id,
                        name: found.name,
                        amount: Number(found.amount||0)
                    });

                    this.chargePickerId = '';
                    this.calc();
                },

                removeCharge(i){
                    this.chargesSelected.splice(i, 1);
                    this.calc();
                },

                chargeTotal(){
                    return Number(this.chargesSelected.reduce((s, r) => s + Number(r.amount||0), 0).toFixed(2));
                },

                // calculations
                basePerUnit(r){
                    return Number(
                        (Number(r.gold_amount||0)
                            + Number(r.silver_amount||0)
                            + Number(r.stone_charges||0)
                            + Number(r.diamond_charges||0)
                            + Number(r.making_charge||0)
                        ).toFixed(2)
                    );
                },

                lineBase(r){
                    const qty = Number(r.qty||0);
                    const disc = Number(r.discount||0);
                    return Math.max(0, qty * this.basePerUnit(r) - disc);
                },

                lineTax(r){
                    const pct = Number(r.tax_percent||0);
                    return Number((this.lineBase(r) * (pct/100)).toFixed(2));
                },

                lineAmount(r){
                    return Number((this.lineBase(r) + this.lineTax(r)).toFixed(2));
                },

                subtotal(){
                    return Number(this.items.reduce((s, r) => s + this.lineBase(r), 0).toFixed(2));
                },

                taxTotal(){
                    return Number(this.items.reduce((s, r) => s + this.lineTax(r), 0).toFixed(2));
                },

                totalBeforeExtras(){
                    return this.subtotal() + this.taxTotal();
                },

                tcsAmount(){
                    if(!this.u.tcsEnabled) return 0;
                    const base = Math.max(0, this.totalBeforeExtras() - Number(this.u.discount_total||0));
                    return Number((base * (Number(this.u.tcs_percent||0)/100)).toFixed(2));
                },

                grandTotal(){
                    let t = this.totalBeforeExtras()
                        - Number(this.u.discount_total||0)
                        + this.chargeTotal()
                        + this.tcsAmount();

                    if(this.u.autoRound){
                        const r = Math.round(t);
                        this.u.round_off = Math.abs(r-t).toFixed(2);
                        this.u.roundSign = (r-t)>=0 ? '+' : '-';
                        t = r;
                    } else {
                        t = t + (Number(this.u.round_off||0) * (this.u.roundSign==='+'?1:-1));
                    }
                    return Number(t.toFixed(2));
                },

                balance(){
                    return Math.max(0, Number((this.grandTotal() - Number(this.u.received||0)).toFixed(2)));
                },

                calc(){
                    // trigger
                    this.grandTotal();
                },

                beforeSubmit(){
                    const payload = this.items.map(r => ({
                        item_id: r.item_id ?? null,
                        description: r.description || '',
                        sac: r.sac || '',
                        qty: Number(r.qty||0),

                        gold_weight: Number(r.gold_weight||0),
                        gold_purity: r.gold_purity || null,
                        gold_rate: Number(r.gold_rate||0),
                        gold_amount: Number(r.gold_amount||0),

                        silver_weight: Number(r.silver_weight||0),
                        silver_purity: r.silver_purity || null,
                        silver_rate: Number(r.silver_rate||0),
                        silver_amount: Number(r.silver_amount||0),

                        stone_weight: Number(r.stone_weight||0),
                        stone_charges: Number(r.stone_charges||0),

                        diamond_weight: Number(r.diamond_weight||0),
                        diamond_charges: Number(r.diamond_charges||0),

                        making_charge: Number(r.making_charge||0),
                        discount: Number(r.discount||0),
                        tax_percent: Number(r.tax_percent||0),

                        amount: this.lineAmount(r)
                    }));

                    document.getElementById('items_json').value = JSON.stringify(payload);

                    document.getElementById('charges_json').value = JSON.stringify(
                        this.chargesSelected.map(r => ({
                            id: r.id,
                            name: r.name,
                            amount: Number(r.amount||0)
                        }))
                    );

                    this.$refs.form.submit();
                },

                money(v){
                    return '₹ ' + Number(v||0).toFixed(2);
                }
            }
        }
    </script>
</x-layouts.app>
