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
        <script type="application/json" id="charges-json">{!! $chargesJson !!}</script>
        <script type="application/json" id="metal-rates-json">{!! $metalRatesJson !!}</script>

        {{-- Initial state for Alpine --}}
        <script>
            window.__INV_EDIT__ = {
                hdr: @json($hdr),
                u: @json($u),
                items: @json($initialItems),
                charges: @json($initialCharges),
                basePrefix: @json($basePrefix),
                invoicePrefix: @json($invoicePrefix),
                invoiceNumber: @json($invoiceNumber),
                paymentMethod: @json($paymentMethod),

                // 🔹 client_id as string
                client_id: @json($invoice->client_id ? (string) $invoice->client_id : ''),
            };
        </script>

        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-neutral-100">
                Edit Sales Invoice #{{ $invoice->invoice_number }}
            </h1>
            <button @click="$refs.form.requestSubmit()"
                    class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                Update
            </button>
        </div>

        <form x-ref="form" method="POST"
              action="{{ route('invoices.update', $invoice->id) }}"
              @submit.prevent="beforeSubmit">
            @csrf
            @method('PUT')

            {{-- Client + Right Panel --}}
            <div class="grid lg:grid-cols-4 gap-4">
                {{-- Bill To --}}
                <div
                    class="lg:col-span-2 p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-neutral-300">
                        Bill To (Client)
                    </label>
                    <div class="flex gap-2">
                        {{-- x-model as string, options value also string --}}
                        <select name="client_id"
                                x-model="clientId"
                                required
                                class="flex-1 border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
               bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                            <option value="">-- Select Client --</option>
                            <template x-for="c in clients" :key="c.id">
                                <option
                                    :value="String(c.id)"
                                    :selected="String(c.id) === clientId"   {{-- 🔹 force selected --}}
                                    x-text="c.mobile ? (c.name + ' (' + c.mobile + ')') : c.name">
                                </option>
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
                <div
                    class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 space-y-3 lg:col-span-2">
                    <div class="grid md:grid-cols-3 gap-3">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">
                                Invoice Prefix
                            </label>
                            <input :value="computedPrefix" readonly
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                          bg-gray-50 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200 text-sm">
                            <input type="hidden" name="invoice_prefix" :value="computedPrefix">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">
                                Invoice Number
                            </label>
                            <input :value="invoiceNo" readonly
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                          bg-gray-50 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">
                                Sales Invoice Date
                            </label>
                            <input type="date" name="invoice_date" x-model="hdr.date"
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                          bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm"
                                   required>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">
                                Payment Terms
                            </label>
                            <div
                                class="flex overflow-hidden rounded border border-gray-300 dark:border-neutral-700 max-w-[190px]">
                                <input type="number" min="0" x-model.number="hdr.terms"
                                       class="w-24 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                                              border-0 px-2 py-1 text-sm focus:ring-0 focus:outline-none">
                                <span
                                    class="px-2 py-1 text-xs bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-neutral-300 border-l border-gray-300 dark:border-neutral-700">
                                    days
                                </span>
                            </div>
                            <input type="hidden" name="payment_terms" :value="hdr.terms">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">
                                Due Date
                            </label>
                            <input type="date" name="due_date" x-model="hdr.due"
                                   class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                          bg-gray-50 dark:bg-neutral-800 text-gray-900 dark:text-neutral-100 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-separate border-spacing-0">
                        <thead class="bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200">
                        <tr class="[&>th]:px-3 [&>th]:py-2 [&>th]:font-medium text-left text-xs">
                            <th>No</th>
                            <th>Item / Description</th>
                            <th>HSN/SAC</th>
                            <th>Qty</th>
                            <th>Metal Wt (g)</th>
                            <th>Stone Charges (₹)</th>
                            <th>Price (₹)</th>
                            <th>Making Charge</th>
                            <th>Discount</th>
                            <th>Tax %</th>
                            <th>Amount (₹)</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-200 dark:divide-neutral-700 text-gray-900 dark:text-neutral-100">
                        <template x-for="(row, i) in items" :key="row._k">
                            <tr>
                                {{-- No --}}
                                <td class="px-3 py-2 text-center text-xs" x-text="i+1"></td>

                                {{-- Item + Description + metal info --}}
                                <td class="px-3 py-2">
                                    <select
                                        class="w-full border rounded px-2 py-1 mb-1 border-gray-300 dark:border-neutral-700
                                               bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-xs"
                                        @change="pickItem(i, $event.target.value)"
                                        :value="row.item_id ? String(row.item_id) : ''">
                                        <option value="">-- Select Item --</option>
                                        <template x-for="it in itemsData" :key="it.id">
                                            <option :value="String(it.id)"
                                                    x-text="it.sku ? (it.name + ' (' + it.sku + ')') : it.name">
                                            </option>
                                        </template>
                                    </select>
                                    <input type="text" x-model="row.description" placeholder="Description" required
                                           class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-xs">
                                    <p class="mt-1 text-[11px] text-gray-500"
                                       x-show="row.metal_type && row.purity">
                                        <span x-text="row.metal_type ? row.metal_type.toUpperCase() : ''"></span>
                                        <span x-text="' • ' + (row.purity || '')"></span>
                                        <span x-show="row.metal_rate">
                                            • Rate: ₹ <span x-text="row.metal_rate.toFixed(2)"></span>/g
                                        </span>
                                    </p>
                                </td>

                                {{-- HSN/SAC --}}
                                <td class="px-3 py-2">
                                    <input x-model="row.sac"
                                           class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Qty --}}
                                <td class="px-3 py-2">
                                    <input type="number" min="1" x-model.number="row.qty" @input="calc()"
                                           class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Metal Weight --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.001" min="0"
                                           x-model.number="row.metal_weight"
                                           @input="recalcMetal(i, $event.target.value)"
                                           class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Stone Charges --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0"
                                           x-model.number="row.stone_charges" @input="calc()"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Price --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0"
                                           x-model.number="row.price" @input="calc()"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-xs">
                                    <p class="text-[11px] text-gray-500"
                                       x-show="row.metal_rate && row.metal_weight">
                                        Metal =
                                        <span x-text="(row.metal_weight * row.metal_rate).toFixed(2)"></span>
                                    </p>
                                </td>

                                {{-- Making charge --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0"
                                           x-model.number="row.making_charge" @input="calc()"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Discount --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0"
                                           x-model.number="row.discount" @input="calc()"
                                           class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Tax % --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" max="100"
                                           x-model.number="row.tax_percent" @input="calc()"
                                           class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                  bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Amount --}}
                                <td class="px-3 py-2">
                                    <input readonly :value="lineAmount(row).toFixed(2)"
                                           class="w-28 bg-gray-50 dark:bg-neutral-800 border rounded px-2 py-1
                                                  border-gray-300 dark:border-neutral-700 text-xs">
                                </td>

                                {{-- Remove --}}
                                <td class="px-3 py-2 text-right">
                                    <button type="button" @click="remove(i)"
                                            class="text-red-600 hover:underline text-lg leading-none">
                                        ×
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <tr>
                            <td colspan="12" class="px-3 py-2">
                                <button type="button" @click="add()"
                                        class="text-blue-600 hover:underline text-sm">
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
                {{-- Terms & Notes --}}
                <div
                    class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">
                        Terms and Conditions
                    </label>
                    <textarea name="terms" rows="4"
                              class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                                     bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">{{ old('terms', $invoice->terms) }}</textarea>

                    <label class="mt-3 block text-sm font-medium text-gray-700 dark:text-neutral-300">
                        Notes
                    </label>
                    <textarea name="notes" rows="3"
                              class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                                     bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">{{ old('notes', $invoice->notes) }}</textarea>
                </div>

                {{-- Totals --}}
                <div
                    class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 space-y-2">
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
                        <a href="#" @click.prevent="u.discountEnabled=!u.discountEnabled"
                           class="text-blue-600 hover:underline text-sm">
                            + Add Discount
                        </a>
                        <template x-if="u.discountEnabled">
                            <div class="mt-2 flex justify-between items-center">
                                <input type="number" min="0" step="0.01"
                                       x-model.number="u.discount_total" @input="calc()
                                       "
                                       class="w-32 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                              bg-white dark:bg-neutral-900 text-sm">
                                <span x-text="'- ' + money(u.discount_total)"></span>
                            </div>
                        </template>
                        <input type="hidden" name="discount_total" :value="u.discount_total">
                    </div>

                    {{-- Additional Charges --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-neutral-300">
                                Additional Charges
                            </span>
                            <a href="#" @click.prevent="mod.charges=!mod.charges"
                               class="text-blue-600 hover:underline text-sm">
                                <span x-text="mod.charges ? 'Hide' : '+ Add'"></span>
                            </a>
                        </div>

                        <template x-if="mod.charges">
                            <div class="space-y-2">
                                <div class="flex gap-2">
                                    <select x-model="chargePickerId"
                                            class="border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                   bg-white dark:bg-neutral-900 text-sm">
                                        <option value="">-- Select a charge --</option>
                                        <template x-for="c in chargesData" :key="c.id">
                                            <option :value="String(c.id)"
                                                    x-text="c.name + ' (' + money(c.amount) + ')'"></option>
                                        </template>
                                    </select>
                                    <button type="button" @click="addCharge()"
                                            class="px-3 py-1 rounded bg-gray-800 text-white disabled:opacity-50 text-sm"
                                            :disabled="!chargePickerId">
                                        Add
                                    </button>
                                </div>

                                <div
                                    class="rounded border border-gray-200 dark:border-neutral-700 divide-y divide-gray-200 dark:divide-neutral-700">
                                    <template x-if="chargesSelected.length===0">
                                        <div
                                            class="px-3 py-2 text-sm text-gray-500 dark:text-neutral-400">
                                            No additional charges added.
                                        </div>
                                    </template>

                                    <template x-for="(r, i) in chargesSelected" :key="r._k">
                                        <div class="flex items-center justify-between px-3 py-2">
                                            <div class="flex-1">
                                                <div class="font-medium" x-text="r.name"></div>
                                                <div class="text-xs text-gray-500">
                                                    Editable for this invoice
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input type="number" step="0.01" min="0"
                                                       x-model.number="r.amount" @input="calc()"
                                                       class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                                              bg-white dark:bg-neutral-900 text-right text-sm">
                                                <button type="button"
                                                        class="text-red-600 hover:underline text-sm"
                                                        @click="removeCharge(i)">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-neutral-300">
                                Charges Total
                            </span>
                            <span class="font-medium" x-text="money(chargeTotal())"></span>
                        </div>

                        <input type="hidden" name="charge_total" :value="chargeTotal()">
                        <input type="hidden" name="charges_json" id="charges_json">
                    </div>

                    {{-- TCS --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="tcs" x-model="u.tcsEnabled"
                                   class="rounded border-gray-300 dark:border-neutral-700">
                            <label for="tcs" class="text-gray-700 dark:text-neutral-300 text-sm">
                                Apply TCS
                            </label>
                        </div>
                        <div class="flex items-center gap-1">
                            <input type="number" min="0" max="100" step="0.01"
                                   x-model.number="u.tcs_percent" @input="calc()"
                                   class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                          bg-white dark:bg-neutral-900 text-sm">
                            <span class="text-gray-700 dark:text-neutral-300 text-sm">%</span>
                        </div>
                        <input type="hidden" name="tcs_percent"
                               :value="u.tcsEnabled ? u.tcs_percent : 0">
                    </div>

                    {{-- Round Off --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="round" x-model="u.autoRound"
                                   class="rounded border-gray-300 dark:border-neutral-700">
                            <label for="round" class="text-gray-700 dark:text-neutral-300 text-sm">
                                Auto Round Off
                            </label>
                        </div>
                        <div class="flex items-center gap-1">
                            <select x-model="u.roundSign"
                                    class="border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                           bg-white dark:bg-neutral-900 text-sm">
                                <option value="+">+ Add</option>
                                <option value="-">- Less</option>
                            </select>
                            <input type="number" step="0.01"
                                   x-model.number="u.round_off" @input="calc()"
                                   class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                          bg-white dark:bg-neutral-900 text-sm">
                        </div>
                        <input type="hidden" name="round_off"
                               :value="u.autoRound
                                        ? (Math.round(totalBeforeExtras()-Number(u.discount_total||0)+chargeTotal()+tcsAmount())
                                            - (totalBeforeExtras()-Number(u.discount_total||0)+chargeTotal()+tcsAmount())).toFixed(2)
                                        : (u.roundSign==='+'?u.round_off:-u.round_off)">
                    </div>

                    <div class="flex items-center justify-between font-semibold text-lg pt-3">
                        <span>Total Amount</span>
                        <span x-text="money(grandTotal())"></span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span>Amount Received</span>
                        <div class="flex items-center gap-2">
                            <input type="number" step="0.01" min="0"
                                   x-model.number="u.received" @input="calc()"
                                   class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                          bg-white dark:bg-neutral-900 text-sm">

                            <select name="payment_method"
                                    class="border rounded px-2 py-1 border-gray-300 dark:border-neutral-700
                                           bg-white dark:bg-neutral-900 text-sm">
                                <option value="Cash"  {{ $paymentMethod === 'Cash'  ? 'selected' : '' }}>Cash</option>
                                <option value="UPI"   {{ $paymentMethod === 'UPI'   ? 'selected' : '' }}>UPI</option>
                                <option value="Card"  {{ $paymentMethod === 'Card'  ? 'selected' : '' }}>Card</option>
                                <option value="NEFT"  {{ $paymentMethod === 'NEFT'  ? 'selected' : '' }}>NEFT</option>
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

            {{-- Hidden items JSON --}}
            <input type="hidden" id="items_json" name="items_json">

            <div class="text-right">
                <button @click="$refs.form.requestSubmit()"
                        class="mt-3 px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                    Update
                </button>
            </div>
        </form>

        {{-- Client Modal same as create --}}
        <div x-show="mod.client"
             class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
             style="display:none">
            <div
                class="bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 rounded-lg w-full max-w-lg p-4 border border-gray-200 dark:border-neutral-700 shadow-xl">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold">Add Party</h3>
                    <button class="text-xl" @click="mod.client=false">&times;</button>
                </div>
                <form @submit.prevent="saveClient" class="space-y-3">
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm mb-1">Name *</label>
                            <input x-model="clientForm.name" required
                                   class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                                          bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Mobile *</label>
                            <input x-model="clientForm.mobile" required
                                   class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                                          bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">GSTIN</label>
                            <input x-model="clientForm.gstin"
                                   class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                                          bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">PAN</label>
                            <input x-model="clientForm.pan"
                                   class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                                          bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm mb-1">State</label>
                            <input x-model="clientForm.state"
                                   class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                                          bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm mb-1">Address</label>
                            <textarea x-model="clientForm.address" rows="2"
                                      class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                                             bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm"></textarea>
                        </div>
                    </div>
                    <div class="mt-2 flex justify-end gap-2">
                        <button type="button"
                                class="px-3 py-2 rounded border border-gray-300 dark:border-neutral-700
                                       bg-white dark:bg-neutral-900 hover:bg-gray-50 dark:hover:bg-neutral-800 text-sm"
                                @click="mod.client=false">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 text-sm">
                            Save Party
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- Alpine component --}}
    <script>
        function invoiceFormEdit(){
            const CLIENTS     = JSON.parse(document.getElementById('clients-json')?.textContent || '[]');
            const ITEMS       = JSON.parse(document.getElementById('items-json')?.textContent || '[]');
            const CHARGES     = JSON.parse(document.getElementById('charges-json')?.textContent || '[]');
            const METAL_RATES = JSON.parse(document.getElementById('metal-rates-json')?.textContent || '[]');
            const INIT        = window.__INV_EDIT__ || {};

            const seedItems = (INIT.items || []).map(r => ({
                _k: Date.now() + Math.random(),
                item_id: r.item_id ?? null,
                description: r.description ?? '',
                sac: r.sac ?? '',
                qty: Number(r.qty || 1),

                metal_type:    r.metal_type ?? null,
                purity:        r.purity ?? null,
                metal_weight:  Number(r.metal_weight || 0),
                metal_rate:    Number(r.metal_rate || 0),
                stone_charges: Number(r.stone_charges || 0),

                price:         Number(r.price || 0),
                making_charge: Number(r.making_charge || 0),
                discount:      Number(r.discount || 0),
                tax_percent:   Number(r.tax_percent || 0),
            }));
            if (seedItems.length === 0) {
                seedItems.push({
                    _k: Date.now(),
                    item_id: null,
                    description: '',
                    sac: '',
                    qty: 1,
                    metal_type: null,
                    purity: null,
                    metal_weight: 0,
                    metal_rate: 0,
                    stone_charges: 0,
                    price: 0,
                    making_charge: 0,
                    discount: 0,
                    tax_percent: 0
                });
            }

            const seedCharges = (INIT.charges || []).map(r => ({
                _k: Date.now() + Math.random(),
                id: r.id ?? null,
                name: r.name ?? '',
                amount: Number(r.amount || 0),
            }));

            return {
                // masters
                clients: CLIENTS,
                itemsData: ITEMS,
                chargesData: CHARGES,
                metalRates: METAL_RATES,

                // 🔹 clientId as string for select
                clientId: (INIT.client_id ?? '').toString(),

                // modals/toggles
                mod: { client:false, charges: seedCharges.length > 0 },

                // quick client form
                clientForm: { name:'', mobile:'', gstin:'', pan:'', state:'', address:'' },

                // header
                hdr: {
                    date:  INIT.hdr?.date || '{{ now()->toDateString() }}',
                    terms: Number(INIT.hdr?.terms || 30),
                    due:   INIT.hdr?.due || ''
                },

                // prefix & number
                basePrefix: INIT.basePrefix || 'RV/SL',
                computedPrefix: INIT.invoicePrefix || (INIT.basePrefix || 'RV/SL'),
                invoiceNo: INIT.invoiceNumber || '',

                // items
                items: seedItems,

                // charges
                chargePickerId: '',
                chargesSelected: seedCharges,

                // user totals/settings
                u: {
                    discountEnabled: !!INIT.u?.discountEnabled,
                    discount_total:  Number(INIT.u?.discount_total || 0),
                    tcsEnabled:      !!INIT.u?.tcsEnabled,
                    tcs_percent:     Number(INIT.u?.tcs_percent || 0),
                    autoRound:       !!INIT.u?.autoRound,
                    roundSign:       (INIT.u?.roundSign === '-' ? '-' : '+'),
                    round_off:       Number(INIT.u?.round_off || 0),
                    received:        Number(INIT.u?.received || 0),
                },

                init(){
                    this.$watch('hdr.terms', ()=> this.calcDue());
                    this.$watch('hdr.date',  ()=> this.calcDue());
                    this.calcDue();
                    this.calc();
                },

                // client quick-add
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
                        this.clientId = String(data.client.id); // new client select
                        this.mod.client = false;
                    }catch(e){
                        alert('Could not save party. Please check details.');
                    }
                },

                // due date
                calcDue(){
                    if(!this.hdr.date) return;
                    const d = new Date(this.hdr.date);
                    d.setDate(d.getDate() + (parseInt(this.hdr.terms||0)));
                    this.hdr.due = d.toISOString().slice(0,10);
                },

                // Metal rate helper
                findMetalRate(type, purity){
                    if (!type) return 0;

                    const t = String(type).toLowerCase().trim();
                    const pRaw = (purity ?? '').toString().trim();

                    const candidates = [];
                    if (pRaw) {
                        candidates.push(pRaw);
                        candidates.push(pRaw.split('(')[0].trim());
                        candidates.push(pRaw.split(' ')[0].trim());
                    }

                    const seen = new Set();
                    const purityOptions = candidates
                        .map(v => v.trim())
                        .filter(v => v && !seen.has(v) && seen.add(v));

                    const rec = this.metalRates.find(r => {
                        if (!r) return false;
                        const rt = String(r.metal_type).toLowerCase().trim();
                        if (rt !== t) return false;

                        const rp = (r.purity ?? '').toString().trim();
                        const rpShort1 = rp.split('(')[0].trim();
                        const rpShort2 = rp.split(' ')[0].trim();

                        return purityOptions.includes(rp)
                            || purityOptions.includes(rpShort1)
                            || purityOptions.includes(rpShort2);
                    });

                    return rec ? Number(rec.rate_per_gram || 0) : 0;
                },

                // items handlers
                pickItem(i, id){
                    const it = this.itemsData.find(x => String(x.id) === String(id));
                    if(!it) return;
                    const r = this.items[i];

                    r.item_id       = it.id;
                    r.description   = it.description || it.name;
                    r.sac           = it.sac || '';
                    r.metal_type    = it.metal_type || null;
                    r.purity        = it.purity || null;
                    r.metal_weight  = Number(it.metal_weight || it.gross_weight || 0) || 0;
                    r.stone_charges = Number(it.stone_charges || 0) || 0;

                    const rate = this.findMetalRate(r.metal_type, r.purity);
                    r.metal_rate = rate;

                    if (rate > 0 && r.metal_weight > 0) {
                        const metalAmount = r.metal_weight * rate;
                        r.price = Number(metalAmount.toFixed(2));
                    } else {
                        r.price = Number(it.price || 0);
                    }

                    r.making_charge = Number(it.making_charge || 0);
                    r.tax_percent   = Number(it.tax_rate || 0);

                    this.calc();
                },
                add(){
                    this.items.push({
                        _k: Date.now()+Math.random(),
                        item_id: null,
                        description: '',
                        sac: '',
                        qty: 1,
                        metal_type: null,
                        purity: null,
                        metal_weight: 0,
                        metal_rate: 0,
                        stone_charges: 0,
                        price: 0,
                        making_charge: 0,
                        discount: 0,
                        tax_percent: 0
                    });
                    this.calc();
                },
                remove(i){
                    this.items.splice(i,1);
                    this.calc();
                },

                // Metal weight change -> recalc price
                recalcMetal(index, weightVal){
                    const r = this.items[index];
                    if (!r) return;

                    const w = Number(weightVal || 0);
                    r.metal_weight = w;

                    const rate = this.findMetalRate(r.metal_type, r.purity);
                    r.metal_rate = rate;

                    if (rate > 0 && w > 0) {
                        const metalAmount = w * rate;
                        r.price = Number(metalAmount.toFixed(2));
                    }
                    this.calc();
                },

                // charges
                addCharge(){
                    const id = String(this.chargePickerId || '');
                    if(!id) return;
                    const found = this.chargesData.find(c => String(c.id) === id);
                    if(!found) return;

                    const exists = this.chargesSelected.find(x => String(x.id) === id);
                    if(exists) {
                        this.chargePickerId='';
                        return;
                    }

                    this.chargesSelected.push({
                        _k: Date.now()+Math.random(),
                        id: found.id,
                        name: found.name,
                        amount: Number(found.amount||0)
                    });
                    this.chargePickerId='';
                    this.calc();
                },
                removeCharge(i){
                    this.chargesSelected.splice(i,1);
                    this.calc();
                },
                chargeTotal(){
                    return Number(this.chargesSelected.reduce((s, r) =>
                        s + Number(r.amount||0), 0
                    ).toFixed(2));
                },

                // totals
                lineAmount(r){
                    const qty = Number(r.qty || 0);
                    const basePerUnit =
                        Number(r.price || 0) +
                        Number(r.making_charge || 0) +
                        Number(r.stone_charges || 0);

                    const discount = Number(r.discount || 0);
                    const taxPct   = Number(r.tax_percent || 0);

                    const base = Math.max(0, qty * basePerUnit - discount);
                    const tax  = base * (taxPct / 100);

                    return Number((base + tax).toFixed(2));
                },
                subtotal(){
                    return Number(this.items.reduce((s, r) => {
                        const qty = Number(r.qty || 0);
                        const basePerUnit =
                            Number(r.price || 0) +
                            Number(r.making_charge || 0) +
                            Number(r.stone_charges || 0);
                        const discount = Number(r.discount || 0);

                        return s + Math.max(0, qty * basePerUnit - discount);
                    }, 0).toFixed(2));
                },
                taxTotal(){
                    return Number(this.items.reduce((s, r) => {
                        const qty = Number(r.qty || 0);
                        const basePerUnit =
                            Number(r.price || 0) +
                            Number(r.making_charge || 0) +
                            Number(r.stone_charges || 0);
                        const discount = Number(r.discount || 0);
                        const taxPct   = Number(r.tax_percent || 0);

                        const base = Math.max(0, qty * basePerUnit - discount);
                        return s + base * (taxPct / 100);
                    }, 0).toFixed(2));
                },
                totalBeforeExtras(){
                    return this.subtotal() + this.taxTotal();
                },
                tcsAmount(){
                    if(!this.u.tcsEnabled) return 0;
                    const base = Math.max(0,
                        this.subtotal() + this.taxTotal() - Number(this.u.discount_total||0)
                    );
                    return Number((base * ((this.u.tcs_percent||0)/100)).toFixed(2));
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
                    return Math.max(0,
                        Number((this.grandTotal() - Number(this.u.received||0)).toFixed(2))
                    );
                },
                calc(){
                    this.grandTotal();
                },

                // submit
                beforeSubmit(){
                    const payload = this.items.map(r => ({
                        item_id:       r.item_id ?? null,
                        description:   r.description || '',
                        sac:           r.sac || '',
                        qty:           Number(r.qty || 0),
                        metal_type:    r.metal_type || null,
                        purity:        r.purity || null,
                        metal_weight:  Number(r.metal_weight || 0),
                        metal_rate:    Number(r.metal_rate || 0),
                        stone_charges: Number(r.stone_charges || 0),
                        price:         Number(r.price || 0),
                        making_charge: Number(r.making_charge || 0),
                        discount:      Number(r.discount || 0),
                        tax_percent:   Number(r.tax_percent || 0),
                        amount:        this.lineAmount(r)
                    }));

                    document.getElementById('items_json').value = JSON.stringify(payload);

                    document.getElementById('charges_json').value = JSON.stringify(
                        this.chargesSelected.map(r => ({
                            id: r.id,
                            name: r.name,
                            amount: Number(r.amount || 0)
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
