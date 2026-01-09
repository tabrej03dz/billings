{{-- ==========================================
   FILE: resources/views/invoices/create_kapoor_style.blade.php
   NOTE: SERVICE => amount = service_rate * qty (NO metal rates)
   NOTE: Amount field editable (Manual override)
   UI UPDATE: ✅ Screenshot-like dropdown (Party + Item)
========================================== --}}

<x-layouts.app :title="__('Create Sales Invoice')">

    <div x-data="invoiceForm()" x-init="init()" class="space-y-4 max-w-7xl  px-3 sm:px-6 py-4" style="margin: -35px">

        <style>
            .invoice-table th,
            .invoice-table td {
                padding: 4px 6px !important;
                vertical-align: top;
            }

            .invoice-table input,
            .invoice-table select,
            .invoice-table textarea {
                padding: 2px 6px !important;
                height: 26px;
                font-size: 12px;
            }
        </style>

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
            <button type="button"
                    @click="submitForm()"
                    :disabled="saving"
                    class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center gap-2">
                <svg x-show="saving" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span x-text="saving ? 'Saving...' : 'Save'"></span>
            </button>
        </div>

        <form x-ref="form" method="POST" action="{{ route('invoices.store', $docType) }}" enctype="multipart/form-data" @submit.prevent="beforeSubmit">
            @csrf

            {{-- TOP PANELS --}}
            <div class="grid lg:grid-cols-4 gap-4">

                {{-- LEFT: Bill To --}}
                <div class="lg:col-span-2 p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Bill To</div>
                    </div>

                    {{-- ✅ PARTY: Screenshot-like dropdown --}}
                    <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300 mb-1">Party</label>

                    <div class="relative"
                         @keydown.escape="clientDD.close()"
                         @keydown.arrow-down.prevent="clientDD.down()"
                         @keydown.arrow-up.prevent="clientDD.up()"
                         @keydown.enter.prevent="clientDD.enter()"
                         @click.outside="clientDD.close()">

                        <input type="text"
                               x-model="clientDD.q"
                               placeholder="Search party by name or number"
                               @focus="clientDD.open()"
                               @input="clientDD.open()"
                               class="mb-2 w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                                      bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">

                        {{-- backend required --}}
                        <input type="hidden" name="client_id" :value="clientId" required>

                        <div x-show="clientDD.isOpen" x-transition
                             class="absolute z-50 mt-1 w-full rounded border border-gray-200 dark:border-neutral-700
                                    bg-white dark:bg-neutral-900 shadow-lg overflow-hidden">

                            <div class="max-h-56 overflow-auto">
                                <template x-if="clientDD.filtered().length === 0">
                                    <div class="px-3 py-2 text-sm text-gray-500 dark:text-neutral-400">
                                        No results
                                    </div>
                                </template>

                                <template x-for="(c, idx) in clientDD.filtered()" :key="c.id">
                                    <div
                                        @mouseenter="clientDD.hi = idx"
                                        @mousedown.prevent="clientDD.select(c)"
                                        class="px-3 py-2 cursor-pointer flex items-center justify-between gap-3 border-b border-gray-100 dark:border-neutral-800"
                                        :class="idx === clientDD.hi ? 'bg-gray-100 dark:bg-neutral-800' : ''">

                                        <div class="min-w-0">
                                            <div class="text-sm text-gray-900 dark:text-neutral-100 truncate"
                                                 x-text="c.mobile ? (c.name + ' (' + c.mobile + ')') : c.name"></div>
                                        </div>

                                        <div class="text-sm text-gray-700 dark:text-neutral-200 shrink-0"
                                             x-text="Number(c.balance ?? 0).toFixed(1)"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button"
                                class="px-3 py-2 rounded border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900
                                       text-gray-900 dark:text-neutral-100 hover:bg-gray-50 dark:hover:bg-neutral-800 text-sm"
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
                        <div>
                            <div class="text-gray-500 dark:text-neutral-400">Pin</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100" x-text="party.pincode || '-'"></div>
                        </div>
                        <div>
                            <div class="text-gray-500 dark:text-neutral-400">GSTIN</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100" x-text="party.gstin || 'Unregistered'"></div>
                        </div>

                        <div>
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
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Transport Mode</label>
                            <input type="text" name="transport_mode" x-model="hdr.transport_mode" placeholder="By Hand"
                                   class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= TABLE ================= --}}
            <div class="border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-visible">
            <div class="overflow-x-auto overflow-y-visible">
                    <table class="min-w-full text-sm border-separate border-spacing-0 invoice-table">
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
                            <th>Amount (Editable)</th>
                            <th></th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700 text-gray-900 dark:text-neutral-100">
                        <template x-for="(row, i) in items" :key="row._k">
                            <tr>
                                <td class="px-3 py-2 text-center text-xs" x-text="i+1"></td>

                                <td class="px-2 py-1 w-[260px] max-w-[260px] align-top">

                                    <div class="relative mb-1" @click.away="closeItemDD(i)">
                                        <div class="flex items-center gap-1">
                                            <input type="text"
                                                   :id="'item_search_'+i"
                                                   x-model="row.search"
                                                   placeholder="Search item by name or sku"
                                                   @focus="openItemDD(i)"
                                                   @input.debounce.50ms="openItemDD(i)"
                                                   @keydown.escape.prevent="closeItemDD(i)"
                                                   @keydown.arrow-down.prevent="itemDDDown(i)"
                                                   @keydown.arrow-up.prevent="itemDDUp(i)"
                                                   @keydown.enter.prevent="itemDDEnter(i)"
                                                   class="flex-1 border rounded px-2 py-1
                      border-gray-300 dark:border-neutral-700
                      bg-white dark:bg-neutral-900
                      text-gray-900 dark:text-neutral-100 text-xs" />

                                            <button type="button"
                                                    class="px-3 py-1 rounded border
                       border-gray-300 dark:border-neutral-700
                       text-xs whitespace-nowrap
                       hover:bg-gray-50 dark:hover:bg-neutral-800"
                                                    @click="openItemModal(i)">
                                                + New
                                            </button>
                                        </div>

                                        <!-- ✅ DROPDOWN (absolute overlay) -->
                                        <div x-show="row.ddOpen"
                                             x-transition.opacity
                                             class="absolute left-0 right-0 mt-1 rounded border border-gray-200 dark:border-neutral-700
                                                bg-white dark:bg-neutral-900 shadow-2xl
                                                z-[999999] isolate
                                                max-h-56 overflow-auto"
                                             style="display:none; transform: translateZ(0);"
                                             @mousedown.prevent>

                                        <template x-if="filteredItems(row.search).length === 0">
                                                <div class="px-3 py-2 text-xs text-gray-500 dark:text-neutral-400">
                                                    No results
                                                </div>
                                            </template>

                                            <template x-for="(it, idx) in filteredItems(row.search).slice(0, 80)" :key="it.id">
                                                <div
                                                    class="px-3 py-2 text-xs cursor-pointer flex items-center justify-between gap-3
                       hover:bg-gray-100 dark:hover:bg-neutral-800"
                                                    :class="idx === row.ddHi ? 'bg-gray-100 dark:bg-neutral-800' : ''"
                                                    @mouseenter="row.ddHi = idx"
                                                    @click="selectItemFromDD(i, it)"
                                                >
                                                    <div class="truncate"
                                                         x-text="it.sku ? (it.name + ' (' + it.sku + ')') : it.name"></div>

                                                    <div class="text-[11px] text-gray-500 dark:text-neutral-400 whitespace-nowrap"
                                                         x-text="it.price ? it.price : ''"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- ✅ backend compatibility -->
                                    <input type="hidden" :name="'items['+i+'][item_id]'" :value="row.item_id">


                                    <textarea x-model="row.description" required rows="2"
                                              class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-xs resize-none"
                                              placeholder="Enter description..."></textarea>

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
                                           x-model.number="row.quantity" @input="onAutoChange(row)"
                                           class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs text-center">
                                </td>

                                {{-- Product-only cells --}}
                                <td class="px-3 py-2" x-show="row.item_type === 'product'">
                                    <input type="number" step="0.01" min="0"
                                           x-model.number="row.making_rate" @input="onAutoChange(row)"
                                           class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2" x-show="row.item_type === 'product'">
                                    <input type="number" step="0.01" min="0"
                                           x-model.number="row.gold_rate" @input="onAutoChange(row)"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                    <div class="mt-0.5 text-[10px] text-gray-500 dark:text-neutral-400"
                                         x-text="row.gold_purity ? ('Purity: ' + row.gold_purity) : ''"></div>
                                </td>

                                <td class="px-3 py-2" x-show="row.item_type === 'product'">
                                    <input type="number" step="0.01" min="0"
                                           x-model.number="row.silver_rate" @input="onAutoChange(row)"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                    <div class="mt-0.5 text-[10px] text-gray-500 dark:text-neutral-400"
                                         x-text="row.silver_purity ? ('Purity: ' + row.silver_purity) : ''"></div>
                                </td>

                                <td class="px-3 py-2" x-show="row.item_type === 'product'">
                                    <input type="number" step="0.001" min="0"
                                           x-model.number="row.silver_wt" @input="onAutoChange(row)"
                                           class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2" x-show="row.item_type === 'product'">
                                    <input type="number" step="0.001" min="0"
                                           x-model.number="row.gold_wt" @input="onAutoChange(row)"
                                           class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2" x-show="row.item_type === 'product'">
                                    <input type="number" step="0.001" min="0"
                                           x-model.number="row.gemstone_wt" @input="onAutoChange(row)"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                <td class="px-3 py-2" x-show="row.item_type === 'product'">
                                    <input type="number" step="0.001" min="0"
                                           x-model.number="row.diamond_wt" @input="onAutoChange(row)"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- Service-only cell --}}
                                <td class="px-3 py-2" x-show="row.item_type === 'service'">
                                    <input type="number" step="0.01" min="0"
                                           x-model.number="row.service_rate" @input="onAutoChange(row)"
                                           class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                    <div class="mt-0.5 text-[10px] text-gray-500 dark:text-neutral-400">
                                        (Service = Item Price)
                                    </div>
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" max="100"
                                           x-model.number="row.tax_percent" @input="onAutoChange(row)"
                                           class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                </td>

                                {{-- ✅ Amount editable --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0"
                                           x-model.number="row.manual_amount"
                                           @input="onAmountEdit(row)"
                                           class="w-32 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs text-right">
                                    <div class="mt-0.5 text-[10px]"
                                         :class="row.amount_mode==='manual' ? 'text-orange-600' : 'text-gray-500 dark:text-neutral-400'"
                                         x-text="row.amount_mode==='manual' ? 'Manual' : ('Auto: ₹ '+ lineAmount(row).toFixed(2))">
                                    </div>
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

                    {{-- Old payment split fields (backend compatible) --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Cash</span>
                            <input type="number" step="0.01" min="0" name="pay_cash"
                                   x-model.number="pay.cash"
                                   class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">UPI / Online</span>
                            <input type="number" step="0.01" min="0" name="pay_upi"
                                   x-model.number="pay.upi"
                                   class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Card</span>
                            <input type="number" step="0.01" min="0" name="pay_card"
                                   x-model.number="pay.card"
                                   class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Cheque</span>
                            <input type="number" step="0.01" min="0" name="pay_cheque"
                                   x-model.number="pay.cheque"
                                   class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm text-right">
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Credit Sales/Excess Amt.</span>
                            <input type="number" step="0.01" min="0" name="credit_sales_excess"
                                   x-model.number="pay.credit_excess"
                                   class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Advance</span>
                            <input type="number" step="0.01" min="0" name="advance_amount"
                                   x-model.number="pay.advance"
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
                <div class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 space-y-3">

                    {{-- Additional Charges --}}
                    <div class="flex items-center justify-between">
                        <button type="button" class="text-sm text-blue-600 hover:underline"
                                @click="ui.showCharges = !ui.showCharges">
                            + Add Additional Charges
                        </button>
                        <div class="text-sm font-semibold" x-text="money(chargesTotal())"></div>
                    </div>

                    <div x-show="ui.showCharges" class="space-y-2" style="display:none;">
                        <template x-for="(c, idx) in charges" :key="c._k">
                            <div class="grid grid-cols-12 gap-2">
                                <input class="col-span-7 border rounded-lg px-2 py-1 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                       placeholder="Charge name (Packing/Transport...)"
                                       x-model="c.name">
                                <input type="number" step="0.01" min="0"
                                       class="col-span-4 border rounded-lg px-2 py-1 text-sm text-right dark:bg-neutral-900 dark:border-neutral-700"
                                       x-model.number="c.amount" @input="calc()">
                                <button type="button" class="col-span-1 text-red-600 text-lg leading-none"
                                        @click="removeCharge(idx)">×</button>
                            </div>
                        </template>

                        <button type="button" class="text-xs px-2 py-1 rounded border dark:border-neutral-700"
                                @click="addCharge()">+ Add</button>
                    </div>

                    {{-- Taxable + Tax --}}
                    <div class="flex justify-between pt-2">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">Taxable Amount</span>
                        <span class="text-sm font-medium" x-text="money(taxableAmount())"></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">SGST</span>
                        <span class="text-sm" x-text="money(sgst())"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">CGST</span>
                        <span class="text-sm" x-text="money(cgst())"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">IGST</span>
                        <span class="text-sm" x-text="money(igst())"></span>
                    </div>

                    {{-- Discount --}}
                    <div class="flex items-center justify-between pt-1">
                        <button type="button" class="text-sm text-blue-600 hover:underline"
                                @click="ui.showDiscount = !ui.showDiscount">
                            + Add Discount
                        </button>
                        <div class="text-sm font-semibold text-red-600" x-text="'- ' + money(discountAmount())"></div>
                    </div>

                    <div x-show="ui.showDiscount" class="grid grid-cols-12 gap-2" style="display:none;">
                        <select class="col-span-4 border rounded-lg px-2 py-1 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                x-model="discount.type" @change="calc()">
                            <option value="flat">Flat ₹</option>
                            <option value="percent">%</option>
                        </select>

                        <input type="number" step="0.01" min="0"
                               class="col-span-8 border rounded-lg px-2 py-1 text-sm text-right dark:bg-neutral-900 dark:border-neutral-700"
                               x-model.number="discount.value" @input="calc()"
                               placeholder="Discount value">
                    </div>

                    {{-- Apply TCS --}}
                    <label class="flex items-center gap-2 pt-1 text-sm text-gray-700 dark:text-neutral-200">
                        <input type="checkbox" class="rounded" x-model="tcs.apply" @change="calc()">
                        Apply TCS
                        <span class="ml-auto font-semibold" x-text="money(tcsAmount())"></span>
                    </label>

                    <div x-show="tcs.apply" class="flex items-center justify-between" style="display:none;">
                        <span class="text-xs text-gray-500 dark:text-neutral-400">TCS %</span>
                        <input type="number" step="0.01" min="0"
                               class="w-28 border rounded-lg px-2 py-1 text-sm text-right dark:bg-neutral-900 dark:border-neutral-700"
                               x-model.number="tcs.percent" @input="calc()">
                    </div>

                    <hr class="border-gray-200 dark:border-neutral-700">

                    {{-- Auto Round Off --}}
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-neutral-200">
                        <input type="checkbox" class="rounded" x-model="roundOff.enabled" @change="calc()">
                        Auto Round Off
                        <span class="ml-auto text-xs text-gray-500 dark:text-neutral-400"
                              x-text="roundOff.enabled ? ('Adj: ' + money(roundOffAmount())) : ''"></span>
                    </label>

                    {{-- Total Amount --}}
                    <div class="flex items-center justify-between pt-1">
                        <div class="text-base font-semibold text-gray-900 dark:text-neutral-100">Total Amount</div>
                        <div class="text-xl font-bold" x-text="money(totalPayable())"></div>
                    </div>

                    <hr class="border-gray-200 dark:border-neutral-700">

                    {{-- Mark fully paid --}}
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-neutral-200 justify-end">
                        Mark as fully paid
                        <input type="checkbox" class="rounded" x-model="payment.markFullyPaid" @change="toggleFullyPaid()">
                    </label>

                    {{-- Amount Received + Mode --}}
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm text-gray-600 dark:text-neutral-300">Amount Received</div>

                        <div class="flex items-center gap-2 w-[320px] max-w-full">
                            <div class="flex-1 flex items-center border rounded-lg overflow-hidden dark:border-neutral-700">
                                <span class="px-3 text-gray-500 dark:text-neutral-400">₹</span>
                                <input type="number" step="0.01" min="0"
                                       class="w-full px-2 py-2 text-sm bg-transparent text-right outline-none dark:text-neutral-100"
                                       x-model.number="payment.received" @input="onReceivedInput()">
                            </div>

                            <select class="w-36 border rounded-lg px-2 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                    x-model="payment.mode" @change="onReceivedInput()">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="card">Card</option>
                                <option value="cheque">Cheque</option>
                                <option value="bank">Bank</option>
                            </select>
                        </div>
                    </div>

                    <div x-show="payment.mode !== 'cash'" style="display:none;" class="flex items-center justify-between gap-3">
                        <div class="text-sm text-gray-600 dark:text-neutral-300">
                            Select Bank
                        </div>

                        <div class="w-[320px] max-w-full">
                            <select class="w-full border rounded-lg px-2 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                    x-model="payment.bank_account_id">
                                <option value="">-- Select Bank Account --</option>
                                <template x-for="b in banks" :key="b.id">
                                    <option :value="b.id"
                                            x-text="(b.bank_name || 'Bank') + ' - ' + (b.account_no ? ('****' + String(b.account_no).slice(-4)) : '')">
                                    </option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="bank_account_id" :value="payment.mode !== 'cash' ? payment.bank_account_id : ''">

                    {{-- Balance Amount --}}
                    <div class="flex justify-between pt-1">
                        <span class="text-sm font-semibold text-green-600">Balance Amount</span>
                        <span class="text-sm font-bold text-green-600" x-text="money(balanceAmount())"></span>
                    </div>

                    {{-- ================= SIGNATURE UPLOAD ================= --}}
                    <div class="pt-2">
                        <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 mb-2">
                            Signature
                        </div>

                        <div class="grid gap-2">
                            <input type="file"
                                   name="signature"
                                   accept="image/*"
                                   class="w-full text-sm file:mr-3 file:px-3 file:py-2 file:rounded-lg
                                          file:border-0 file:bg-gray-100 file:text-gray-700
                                          dark:file:bg-neutral-800 dark:file:text-neutral-200
                                          border border-gray-300 dark:border-neutral-700
                                          rounded-lg px-3 py-2 bg-white dark:bg-neutral-900
                                          text-gray-900 dark:text-neutral-100">

                            <div class="text-[11px] text-gray-500 dark:text-neutral-400">
                                Allowed: JPG/PNG/WebP • Max 2MB (recommended: transparent PNG)
                            </div>
                        </div>
                    </div>

                    {{-- ✅ Hidden inputs (ONLY ONCE) --}}
                    <input type="hidden" name="charges_json" :value="JSON.stringify(chargesPayload())">
                    <input type="hidden" name="discount_total" :value="discountAmount()">
                    <input type="hidden" name="charge_total" :value="chargesTotal()">
                    <input type="hidden" name="tcs_percent" :value="tcs.apply ? tcs.percent : 0">
                    <input type="hidden" name="tcs_amount" :value="tcsAmount()">
                    <input type="hidden" name="round_off" :value="roundOffAmount()">
                    <input type="hidden" name="less_amount" :value="discountAmount()">
                    <input type="hidden" name="cgst_percent" :value="isIntra() ? (avgTaxPercent()/2) : 0">
                    <input type="hidden" name="sgst_percent" :value="isIntra() ? (avgTaxPercent()/2) : 0">
                    <input type="hidden" name="igst_percent" :value="isIntra() ? 0 : avgTaxPercent()">
                    <input type="hidden" name="payment_method" :value="payment.mode">
                </div>
            </div>

            {{-- Hidden JSON --}}
            <input type="hidden" id="items_json" name="items_json">

            <div class="text-right">
                <button type="button"
                        @click="submitForm()"
                        :disabled="saving"
                        class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center gap-2">
                    <svg x-show="saving" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span x-text="saving ? 'Saving...' : 'Save'"></span>
                </button>
            </div>

            {{-- =================== CLIENT MODAL =================== --}}
            <div x-show="modals.client" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center px-3" style="display:none;">
                <div class="absolute inset-0 bg-black/50" @click="closeClientModal()"></div>

                <div class="relative w-full max-w-2xl bg-white dark:bg-neutral-900 rounded-2xl shadow-xl border border-gray-200 dark:border-neutral-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">Add New Client</h3>
                        <button type="button" class="text-2xl leading-none text-gray-500 hover:text-red-600" @click="closeClientModal()">×</button>
                    </div>

                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Name *</label>
                            <input x-model="newClient.name" class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700" placeholder="Client name">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Mobile </label>
                            <input x-model="newClient.mobile" class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700" placeholder="10 digit mobile">
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">
                                State (GST Code)
                            </label>

                            <select
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm
                                       bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100"
                                x-model="newClient.state_pick"
                                @change="applyClientState()"
                            >
                                <option value="">-- Select State --</option>

                                <template x-for="st in states" :key="st.code">
                                    <option :value="st.code + ',' + st.name" x-text="st.name + ' (' + st.code + ')'"></option>
                                </template>
                            </select>

                            <div class="mt-1 grid grid-cols-2 gap-2 text-[11px] text-gray-500 dark:text-neutral-400">
                                <div>
                                    State: <span class="font-semibold" x-text="newClient.state || '-'"></span>
                                </div>
                                <div>
                                    Code: <span class="font-semibold" x-text="newClient.state_code || '-'"></span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Pin </label>
                            <input x-model="newClient.pincode" class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700" placeholder="6 digit pin">
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Address</label>
                            <input x-model="newClient.address" class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700" placeholder="Full address">
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">GSTIN</label>

                            <input x-model="newClient.gstin"
                                   @input.debounce.350ms="onGstinInput('client')"
                                   class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                   placeholder="optional">

                            <div class="mt-1 text-[11px]"
                                 x-show="clientGstin.status !== 'idle'"
                                 :class="clientGstin.status==='valid' ? 'text-green-600' : (clientGstin.status==='checking' ? 'text-blue-600' : 'text-red-600')"
                                 x-text="clientGstin.message">
                            </div>
                        </div>

                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <div class="text-sm text-red-600" x-text="newClientError"></div>

                            <label class="mt-2 inline-flex items-center gap-2 text-sm text-gray-700 dark:text-neutral-200 select-none">
                                <input type="checkbox" class="rounded border-gray-300 dark:border-neutral-700"
                                       x-model="clientAutoSelect">
                                Save for future
                            </label>
                        </div>

                        <div class="flex gap-2 shrink-0">
                            <button type="button" class="px-4 py-2 rounded-xl border dark:border-neutral-700" @click="closeClientModal()">
                                Cancel
                            </button>

                            <button type="button"
                                    class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
                                    :disabled="savingClient"
                                    @click="saveClient()">
                                <span x-show="!savingClient">Add Client</span>
                                <span x-show="savingClient">Saving...</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            {{-- =================== ITEM MODAL =================== --}}
            <div x-show="modals.item" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center px-3" style="display:none;">
                <div class="absolute inset-0 bg-black/50" @click="closeItemModal()"></div>

                <div class="relative w-full max-w-3xl bg-white dark:bg-neutral-900 rounded-2xl shadow-xl border border-gray-200 dark:border-neutral-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">Create New Item</h3>
                        <button type="button" class="text-2xl leading-none text-gray-500 hover:text-red-600" @click="closeItemModal()">×</button>
                    </div>

                    <div class="grid md:grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Type *</label>
                            <select x-model="newItem.type" class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                <option value="product">Product</option>
                                <option value="service">Service</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Name *</label>
                            <input x-model="newItem.name" class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700" placeholder="Item name">
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">SKU</label>
                            <input x-model="newItem.sku" class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700" placeholder="optional">
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Tax %</label>
                            <input type="number" step="0.01" min="0" max="100" x-model.number="newItem.tax_rate"
                                   class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                        </div>

                        <div x-show="newItem.type==='product'">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">HSN</label>
                            <input x-model="newItem.hsn" class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                        </div>

                        <div x-show="newItem.type==='service'">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">SAC</label>
                            <input x-model="newItem.sac" class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                        </div>

                        <div class="md:col-span-3">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Description</label>
                            <input x-model="newItem.description" class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                        </div>

                        {{-- SERVICE fields --}}
                        <div x-show="newItem.type==='service'">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Service Price (₹) *</label>
                            <input type="number" step="0.01" min="0" x-model.number="newItem.price"
                                   class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                        </div>

                        {{-- PRODUCT fields --}}
                        <template x-if="newItem.type==='product'">
                            <div class="md:col-span-3 grid md:grid-cols-3 gap-3 p-3 rounded-xl border border-gray-200 dark:border-neutral-700">
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Making Charge</label>
                                    <input type="number" step="0.01" min="0" x-model.number="newItem.making_charge"
                                           class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Gold Weight (g)</label>
                                    <input type="number" step="0.001" min="0" x-model.number="newItem.gold_weight"
                                           class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Gold Purity</label>
                                    <input x-model="newItem.gold_purity" placeholder="e.g. 22K / 916"
                                           class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Silver Weight (g)</label>
                                    <input type="number" step="0.001" min="0" x-model.number="newItem.silver_weight"
                                           class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Silver Purity</label>
                                    <input x-model="newItem.silver_purity" placeholder="e.g. 999"
                                           class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div></div>

                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Stone Wt (ct)</label>
                                    <input type="number" step="0.001" min="0" x-model.number="newItem.stone_weight"
                                           class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Diamond Wt (ct)</label>
                                    <input type="number" step="0.001" min="0" x-model.number="newItem.diamond_weight"
                                           class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div></div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <div class="text-sm text-red-600" x-text="newItemError"></div>

                            <label class="mt-2 inline-flex items-center gap-2 text-sm text-gray-700 dark:text-neutral-200 select-none">
                                <input type="checkbox" class="rounded border-gray-300 dark:border-neutral-700"
                                       x-model="itemAutoSelect">
                                Save for future
                            </label>
                        </div>

                        <div class="flex gap-2 shrink-0">
                            <button type="button" class="px-4 py-2 rounded-xl border dark:border-neutral-700" @click="closeItemModal()">
                                Cancel
                            </button>

                            <button type="button"
                                    class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
                                    :disabled="savingItem"
                                    @click="saveItem()">
                                <span x-show="!savingItem" x-text="itemAutoSelect ? 'Save Item' : 'Use for this Invoice'"></span>
                                <span x-show="savingItem">Saving...</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </form>
    </div>

    <script type="application/json" id="banks-json">{!! $banksJson !!}</script>

    <script>
        function invoiceForm(){
            const CLIENTS = JSON.parse(document.getElementById('clients-json')?.textContent || '[]');
            const ITEMS = JSON.parse(document.getElementById('items-json')?.textContent || '[]');
            const METAL_RATES = JSON.parse(document.getElementById('metal-rates-json')?.textContent || '[]');

            const BIZ_STATE_CODE = @js($businessStateCode ?? '');
            const BIZ_GSTIN      = @js($businessGstin ?? '');
            const BANKS = JSON.parse(document.getElementById('banks-json')?.textContent || '[]');

            return {
                clients: CLIENTS,
                itemsData: ITEMS,
                metalRates: METAL_RATES,

                clientId: '',
                party: { name:'', address:'', state:'', state_code:'', mobile:'', gstin:'', pincode:'' },

                // ✅ PARTY dropdown controller (screenshot style)
                clientDD: {
                    isOpen: false,
                    q: '',
                    hi: 0,
                    parent: null, // ✅ will be set in init()

                    open(){ this.isOpen = true; this.hi = 0; },
                    close(){ this.isOpen = false; },

                    filtered(){
                        const q = (this.q || '').toString().toLowerCase().trim();
                        const list = (this.parent?.clients || []);
                        if(!q) return list;

                        return list.filter(c => {
                            const name = (c.name || '').toString().toLowerCase();
                            const mob  = (c.mobile || '').toString().toLowerCase();
                            return name.includes(q) || mob.includes(q);
                        });
                    },

                    down(){
                        if(!this.isOpen) this.open();
                        const list = this.filtered();
                        if(!list.length) return;
                        this.hi = Math.min(list.length - 1, this.hi + 1);
                    },
                    up(){
                        if(!this.isOpen) this.open();
                        const list = this.filtered();
                        if(!list.length) return;
                        this.hi = Math.max(0, this.hi - 1);
                    },
                    enter(){
                        const list = this.filtered();
                        if(!this.isOpen || !list.length) return;
                        this.select(list[this.hi]);
                    },
                    select(c){
                        this.parent.clientId = c.id;
                        this.q = c.mobile ? (c.name + ' (' + c.mobile + ')') : c.name;
                        this.close();
                    }
                },


                // search helper (items)
                filteredItems(q) {
                    const s = (q || '').toString().toLowerCase().trim();
                    if (!s) return this.itemsData || [];
                    return (this.itemsData || []).filter(it => {
                        const name = (it.name || '').toString().toLowerCase();
                        const sku  = (it.sku || '').toString().toLowerCase();
                        const desc = (it.description || '').toString().toLowerCase();
                        return name.includes(s) || sku.includes(s) || desc.includes(s);
                    });
                },

                states: [
                    {code:'01', name:'Jammu and Kashmir'},
                    {code:'02', name:'Himachal Pradesh'},
                    {code:'03', name:'Punjab'},
                    {code:'04', name:'Chandigarh'},
                    {code:'05', name:'Uttarakhand'},
                    {code:'06', name:'Haryana'},
                    {code:'07', name:'Delhi'},
                    {code:'08', name:'Rajasthan'},
                    {code:'09', name:'Uttar Pradesh'},
                    {code:'10', name:'Bihar'},
                    {code:'11', name:'Sikkim'},
                    {code:'12', name:'Arunachal Pradesh'},
                    {code:'13', name:'Nagaland'},
                    {code:'14', name:'Manipur'},
                    {code:'15', name:'Mizoram'},
                    {code:'16', name:'Tripura'},
                    {code:'17', name:'Meghalaya'},
                    {code:'18', name:'Assam'},
                    {code:'19', name:'West Bengal'},
                    {code:'20', name:'Jharkhand'},
                    {code:'21', name:'Odisha'},
                    {code:'22', name:'Chhattisgarh'},
                    {code:'23', name:'Madhya Pradesh'},
                    {code:'24', name:'Gujarat'},
                    {code:'26', name:'Dadra and Nagar Haveli and Daman and Diu'},
                    {code:'27', name:'Maharashtra'},
                    {code:'29', name:'Karnataka'},
                    {code:'30', name:'Goa'},
                    {code:'31', name:'Lakshadweep'},
                    {code:'32', name:'Kerala'},
                    {code:'33', name:'Tamil Nadu'},
                    {code:'34', name:'Puducherry'},
                    {code:'35', name:'Andaman and Nicobar Islands'},
                    {code:'36', name:'Telangana'},
                    {code:'37', name:'Andhra Pradesh'},
                    {code:'38', name:'Ladakh'},
                ],

                banks: BANKS,
                payment: { received:0, mode:'cash', markFullyPaid:false, bank_account_id:'' },

                hdr: {
                    date: @js($today),
                    transport_mode: 'By Hand',
                    gst_no: BIZ_GSTIN,
                    reverse_charge: false,
                },

                basePrefix: @js($basePrefix),
                computedPrefix: @js($suggestedPrefix),
                invoiceNo: @js($initialInvoiceNo),

                // backend compatible payment split
                pay: {
                    cash: 0, upi: 0, card: 0, cheque: 0,
                    credit_excess: 0, advance: 0,
                    online_mode:'', online_ref:'', upi_id:'',
                    card_last4:'', card_ref:'', cheque_no:'', bank_name:'',
                },

                ui: { showCharges:false, showDiscount:false },

                // Charges
                charges: [],
                blankCharge(){ return { _k: Date.now()+Math.random(), name:'', amount:0 }; },
                addCharge(){ this.charges.push(this.blankCharge()); this.calc(); },
                removeCharge(i){ this.charges.splice(i,1); this.calc(); },
                chargesTotal(){
                    return Number(this.charges.reduce((s,c)=> s + Number(c.amount||0), 0).toFixed(2));
                },
                chargesPayload(){
                    return this.charges
                        .filter(c => (String(c.name||'').trim() || Number(c.amount||0) > 0))
                        .map(c => ({ name: String(c.name||'').trim(), amount: Number(c.amount||0) }));
                },

                // Discount
                discount: { type:'flat', value:0 },
                discountAmount(){
                    const base = this.subtotal();
                    const v = Number(this.discount.value||0);
                    if(this.discount.type === 'percent'){
                        return Number((base * (v/100)).toFixed(2));
                    }
                    return Number(v.toFixed(2));
                },

                // TCS
                tcs: { apply:false, percent:0 },
                tcsAmount(){
                    if(!this.tcs.apply) return 0;
                    const pct = Number(this.tcs.percent||0);
                    if(pct<=0) return 0;
                    const base = this.taxableAmount();
                    return Number((base * (pct/100)).toFixed(2));
                },

                // Round Off
                roundOff: { enabled:false },
                roundOffAmount(){
                    if(!this.roundOff.enabled) return 0;
                    const raw = this.totalBeforeRound();
                    const rounded = Math.round(raw);
                    return Number((rounded - raw).toFixed(2));
                },

                toggleFullyPaid(){
                    if(this.payment.markFullyPaid){
                        this.payment.received = this.totalPayable();
                    }
                    this.onReceivedInput();
                },

                onReceivedInput(){
                    const amt = Number(this.payment.received||0);

                    this.pay.cash = 0; this.pay.upi = 0; this.pay.card = 0; this.pay.cheque = 0;

                    if(this.payment.mode === 'cash')   this.pay.cash = amt;
                    if(this.payment.mode === 'upi')    this.pay.upi = amt;
                    if(this.payment.mode === 'card')   this.pay.card = amt;
                    if(this.payment.mode === 'cheque') this.pay.cheque = amt;
                    if(this.payment.mode === 'bank')   this.pay.upi = amt;

                    if (this.payment.mode === 'cash') {
                        this.payment.bank_account_id = '';
                    }

                    this.calc();
                },

                // rows
                items: [],
                blankRow(){
                    return {
                        _k: Date.now()+Math.random(),
                        item_id: null,
                        item_type: null,

                        search: '',
                        ddOpen: false, // ✅
                        ddHi: 0,       // ✅

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

                        amount_mode: 'auto',
                        manual_amount: 0,
                    }
                },

                // ✅ ITEM dropdown controls (screenshot style)
                rowDDOpen(i){
                    const r = this.items[i];
                    if(!r) return;
                    r.ddOpen = true;
                    r.hi = 0;
                },
                rowDDClose(i){
                    const r = this.items[i];
                    if(!r) return;
                    r.ddOpen = false;
                },
                rowDDDown(i){
                    const r = this.items[i];
                    if(!r) return;
                    if(!r.ddOpen) this.rowDDOpen(i);
                    const list = this.filteredItems(r.search) || [];
                    if(!list.length) return;
                    r.hi = Math.min(list.length - 1, (r.hi || 0) + 1);
                },
                rowDDUp(i){
                    const r = this.items[i];
                    if(!r) return;
                    if(!r.ddOpen) this.rowDDOpen(i);
                    const list = this.filteredItems(r.search) || [];
                    if(!list.length) return;
                    r.hi = Math.max(0, (r.hi || 0) - 1);
                },
                rowDDEnter(i){
                    const r = this.items[i];
                    if(!r || !r.ddOpen) return;
                    const list = this.filteredItems(r.search) || [];
                    if(!list.length) return;
                    const it = list[r.hi || 0];
                    this.selectRowItem(i, it);
                },
                selectRowItem(i, it){
                    const r = this.items[i];
                    if(!r || !it) return;
                    r.item_id = it.id;
                    r.search = it.sku ? (it.name + ' (' + it.sku + ')') : it.name;
                    this.pickItem(i, it.id); // ✅ existing logic
                    r.ddOpen = false;
                },

                // modals + ajax
                modals: { client:false, item:false },
                savingClient:false,
                savingItem:false,
                newClientError:'',
                newItemError:'',
                activeRowIndex: null,
                clientAutoSelect: true,
                itemAutoSelect: true,

                newClient: { name:'', mobile:'', address:'', state:'', state_code:'', gstin:'', pincode:'', state_pick:'' },

                newItem: {
                    type:'product',
                    name:'', sku:'', description:'',
                    tax_rate:0,
                    hsn:'', sac:'',
                    price:0,
                    making_charge:0,
                    gold_weight:0, gold_purity:'',
                    silver_weight:0, silver_purity:'',
                    stone_weight:0, diamond_weight:0
                },

                csrf(){ return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''; },

                openClientModal(){
                    this.newClientError = '';
                    this.newClient = { name:'', mobile:'', address:'', state:'', state_code:'', gstin:'', pincode:'', state_pick:'' };
                    this.clientAutoSelect = true;
                    this.modals.client = true;
                },
                closeClientModal(){ this.modals.client = false; },

                applyClientState(){
                    const v = String(this.newClient.state_pick || '').trim();
                    if(!v){
                        this.newClient.state = '';
                        this.newClient.state_code = '';
                        return;
                    }
                    const parts = v.split(',');
                    this.newClient.state_code = (parts[0] || '').trim();
                    this.newClient.state = (parts.slice(1).join(',') || '').trim();
                },

                openItemModal(rowIndex=null){
                    this.activeRowIndex = rowIndex;
                    this.newItemError = '';
                    this.itemAutoSelect = true;

                    this.newItem = {
                        type:'product',
                        name:'', sku:'', description:'',
                        tax_rate:0, hsn:'', sac:'',
                        price:0,
                        making_charge:0,
                        gold_weight:0, gold_purity:'',
                        silver_weight:0, silver_purity:'',
                        stone_weight:0, diamond_weight:0
                    };
                    this.modals.item = true;
                },
                closeItemModal(){ this.modals.item = false; this.activeRowIndex = null; },

                async saveClient(){
                    this.newClientError = '';

                    if(!String(this.newClient.name || '').trim()){
                        this.newClientError = 'Name is required.';
                        return;
                    }

                    const mob = String(this.newClient.mobile || '').replace(/\D/g, '');
                    if(mob && mob.length < 10){
                        this.newClientError = 'Mobile must be 10 digits (optional).';
                        return;
                    }
                    this.newClient.mobile = mob;

                    try{
                        this.savingClient = true;
                        const res = await fetch(@js(route('clients.quick-store')), {
                            method:'POST',
                            credentials:'same-origin',
                            headers:{
                                'Content-Type':'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                                'X-Requested-With':'XMLHttpRequest',
                                'Accept':'application/json',
                            },
                            body: JSON.stringify({
                                ...this.newClient,
                                is_save: this.clientAutoSelect ? 1 : 0,
                            })
                        });

                        const data = await res.json().catch(()=> ({}));
                        if(!res.ok){
                            this.newClientError = data?.message || 'Failed to save client.';
                            return;
                        }

                        this.clients.unshift(data.client);

                        if(this.clientAutoSelect){
                            this.clientId = data.client.id;
                            this.clientDD.q = data.client.mobile ? (data.client.name + ' (' + data.client.mobile + ')') : data.client.name;
                        }

                        this.modals.client = false;

                    }catch(e){
                        this.newClientError = 'Network error.';
                    }finally{
                        this.savingClient = false;
                    }
                },

                async saveItem(){
                    this.newItemError = '';

                    if(!String(this.newItem.name || '').trim()){
                        this.newItemError = 'Item name is required.';
                        return;
                    }

                    if(this.newItem.type === 'service' && Number(this.newItem.price||0) <= 0){
                        this.newItemError = 'Service price is required.';
                        return;
                    }

                    try{
                        this.savingItem = true;

                        const res = await fetch(@js(route('items.store.ajax')), {
                            method:'POST',
                            credentials:'same-origin',
                            headers:{
                                'Content-Type':'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                                'X-Requested-With':'XMLHttpRequest',
                                'Accept':'application/json',
                            },
                            body: JSON.stringify({
                                ...this.newItem,
                                is_save: this.itemAutoSelect ? 1 : 0
                            })
                        });

                        const data = await res.json().catch(()=> ({}));
                        if(!res.ok){
                            this.newItemError = data?.message || 'Failed to save item.';
                            return;
                        }

                        this.itemsData.unshift(data.item);

                        if(this.activeRowIndex !== null && this.items[this.activeRowIndex]){
                            this.pickItem(this.activeRowIndex, data.item.id);
                        }

                        this.modals.item = false;

                    }catch(e){
                        this.newItemError = 'Network error.';
                    }finally{
                        this.savingItem = false;
                    }
                },

                init(){
                    this.clientDD.parent = this; // ✅ IMPORTANT
                    this.$watch('clientId', () => this.syncParty());

                    if (!this.items.length) this.items.push(this.blankRow());
                    if (!this.charges.length) this.charges.push(this.blankCharge());

                    this.syncParty();

                    // ✅ keep party input text in sync
                    const cc = this.clients.find(x => String(x.id) === String(this.clientId));
                    this.clientDD.q = cc ? (cc.mobile ? (cc.name + ' (' + cc.mobile + ')') : cc.name) : '';

                    this.onReceivedInput();
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
                        pincode: c.pincode ?? '',
                    } : { name:'', address:'', state:'', state_code:'', mobile:'', gstin:'', pincode:'' };

                    // ✅ update textbox too
                    if(c){
                        this.clientDD.q = c.mobile ? (c.name + ' (' + c.mobile + ')') : c.name;
                    }

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

                    r.search = it.sku ? (it.name + ' (' + it.sku + ')') : it.name;

                    r.description = it.description || it.name || '';
                    r.tax_percent = Number(it.tax_rate ?? 0);

                    r.amount_mode = 'auto';
                    r.manual_amount = 0;

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

                        r.manual_amount = this.lineAmount(r);
                        this.calc();
                        return;
                    }

                    // PRODUCT
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

                    r.manual_amount = this.lineAmount(r);
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

                onAmountEdit(r){
                    const total = Number(r.manual_amount || 0);
                    r.amount_mode = (total > 0) ? 'manual' : 'auto';

                    if (r.item_type === 'service') {
                        const qty = Math.max(1, Number(r.quantity || 1));
                        const pct = Number(r.tax_percent || 0);
                        const base = (pct > 0) ? (total / (1 + (pct/100))) : total;
                        r.service_rate = Number((base / qty).toFixed(2));
                    }

                    this.calc();
                },

                onAutoChange(r){
                    if(r.amount_mode !== 'manual'){
                        r.manual_amount = this.lineAmount(r);
                    } else {
                        if (r.item_type === 'service') {
                            const total = Number(r.manual_amount || 0);
                            const qty = Math.max(1, Number(r.quantity || 1));
                            const pct = Number(r.tax_percent || 0);
                            const base = (pct > 0) ? (total / (1 + (pct/100))) : total;
                            r.service_rate = Number((base / qty).toFixed(2));
                        }
                    }
                    this.calc();
                },

                lineBase(r){
                    const qty = Math.max(1, Number(r.quantity || 1));
                    const pct = Number(r.tax_percent||0);

                    if(r.amount_mode === 'manual'){
                        const total = Number(r.manual_amount||0);
                        const base = (pct > 0) ? (total / (1 + (pct/100))) : total;
                        return Math.max(0, Number(base.toFixed(2)));
                    }

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
                    const pct = Number(r.tax_percent||0);
                    const base = this.lineBase(r);
                    return Number((base * (pct/100)).toFixed(2));
                },

                lineAmount(r){
                    if(r.amount_mode === 'manual'){
                        return Number((Number(r.manual_amount||0)).toFixed(2));
                    }
                    return Number((this.lineBase(r) + this.lineTax(r)).toFixed(2));
                },

                subtotal(){
                    return Number(this.items.reduce((s,r)=> s + this.lineBase(r), 0).toFixed(2));
                },

                avgTaxPercentRaw(){
                    const baseSum = this.items.reduce((s,r)=> s + this.lineBase(r), 0);
                    if(baseSum <= 0) return 0;
                    const weighted = this.items.reduce((s,r)=> s + (this.lineBase(r) * Number(r.tax_percent||0)), 0);
                    return (weighted / baseSum);
                },
                avgTaxPercent(){
                    return Number(this.avgTaxPercentRaw().toFixed(2));
                },

                itemsTaxTotal(){
                    return Number(this.items.reduce((s,r)=> s + this.lineTax(r), 0).toFixed(2));
                },

                chargesTaxTotal(){
                    const pct = this.avgTaxPercentRaw();
                    return Number((this.chargesTotal() * (pct/100)).toFixed(2));
                },

                taxableAmount(){
                    const val = this.subtotal() + this.chargesTotal() - this.discountAmount();
                    return Number(Math.max(0,val).toFixed(2));
                },

                taxOnTaxable(){
                    return Number((this.itemsTaxTotal() + this.chargesTaxTotal()).toFixed(2));
                },

                cgst(){ return this.isIntra() ? Number((this.taxOnTaxable()/2).toFixed(2)) : 0; },
                sgst(){ return this.isIntra() ? Number((this.taxOnTaxable()/2).toFixed(2)) : 0; },
                igst(){ return this.isIntra() ? 0 : Number(this.taxOnTaxable().toFixed(2)); },

                totalBeforeRound(){
                    return Number((this.taxableAmount() + this.taxOnTaxable() + this.tcsAmount()).toFixed(2));
                },

                totalPayable(){
                    return Number((this.totalBeforeRound() + this.roundOffAmount()).toFixed(2));
                },

                receivedTotal(){
                    const t = Number(this.pay.cash||0) + Number(this.pay.upi||0) + Number(this.pay.card||0) + Number(this.pay.cheque||0);
                    return Number(t.toFixed(2));
                },

                balanceAmount(){
                    const paid = this.receivedTotal() + Number(this.pay.credit_excess||0) + Number(this.pay.advance||0);
                    const bal = this.totalPayable() - paid;
                    return Number(Math.max(0, bal).toFixed(2));
                },

                calc(){
                    return this.totalPayable();
                },

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

                        discount: 0,
                        tax_percent: Number(r.tax_percent||0),

                        rate: this.lineBase(r),
                        tax_amount: this.lineTax(r),
                        amount: this.lineAmount(r),
                    }));

                    document.getElementById('items_json').value = JSON.stringify(payload);

                    this.onReceivedInput();
                    this.$refs.form.submit();
                },

                saving: false,
                submitForm(){
                    if(this.saving) return;

                    const g = this.normalizeGstin(this.hdr.gst_no);
                    const res = this.validateGstinLocal(g);

                    if(g && !res.ok){
                        const ok = confirm("⚠️ GSTIN invalid lag raha hai.\n\n" + res.message + "\n\nPhir bhi Save karna hai?");
                        if(!ok) return;
                    }

                    this.saving = true;
                    this.$refs.form.requestSubmit();
                },

                money(v){ return '₹ ' + Number(v||0).toFixed(2); },

                gstin: { status:'idle', message:'', value:'' },
                clientGstin: { status:'idle', message:'', value:'' },

                normalizeGstin(v){
                    return String(v||'').toUpperCase().replace(/[^0-9A-Z]/g,'').trim();
                },

                validateGstinLocal(gstin){
                    const g = this.normalizeGstin(gstin);
                    if(!g) return { ok:true, empty:true, message:'' };

                    if(g.length !== 15) return { ok:false, message:'GSTIN must be 15 characters.' };

                    const re = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/;
                    if(!re.test(g)) return { ok:false, message:'GSTIN format is invalid (check state/PAN/etc).' };

                    const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    const mod = 36;
                    const codePoint = (c) => chars.indexOf(c);

                    let sum = 0;
                    let f = 2;

                    for (let i = 13; i >= 0; i--) {
                        const v = codePoint(g[i]);
                        if (v === -1) return { ok:false, message:'GSTIN has invalid characters.' };

                        const p = v * f;
                        f = (f === 2) ? 1 : 2;

                        sum += Math.floor(p / mod) + (p % mod);
                    }

                    const checkCodePoint = (mod - (sum % mod)) % mod;
                    const expected = chars[checkCodePoint];
                    const actual = g[14];

                    if (expected !== actual) {
                        return { ok:false, message:'GSTIN checksum mismatch (likely wrong GSTIN).' };
                    }

                    return { ok:true, empty:false, message:'GSTIN looks valid.' };
                },

                onGstinInput(scope){
                    if(scope === 'hdr'){
                        const g = this.normalizeGstin(this.hdr.gst_no);
                        this.hdr.gst_no = g;

                        const res = this.validateGstinLocal(g);
                        if(res.empty){
                            this.gstin = { status:'idle', message:'', value:'' };
                            return;
                        }
                        this.gstin = {
                            status: res.ok ? 'valid' : 'invalid',
                            message: res.ok ? '✅ ' + res.message : '⚠️ ' + res.message,
                            value: g
                        };
                        return;
                    }

                    if(scope === 'client'){
                        const g = this.normalizeGstin(this.newClient.gstin);
                        this.newClient.gstin = g;

                        const res = this.validateGstinLocal(g);
                        if(res.empty){
                            this.clientGstin = { status:'idle', message:'', value:'' };
                            return;
                        }
                        this.clientGstin = {
                            status: res.ok ? 'valid' : 'invalid',
                            message: res.ok ? '✅ ' + res.message : '⚠️ ' + res.message,
                            value: g
                        };
                    }
                },



                // addons method

                openItemDD(i){
                    const r = this.items[i];
                    if(!r) return;
                    r.ddOpen = true;
                    r.ddHi = 0;
                },

                closeItemDD(i, keepText = true){
                    const r = this.items[i];
                    if(!r) return;

                    // small delay not needed because we use mousedown.prevent
                    r.ddOpen = false;

                    // keep selected label in input
                    if(keepText && r.item_id){
                        const it = this.itemsData.find(x => String(x.id) === String(r.item_id));
                        if(it){
                            r.search = it.sku ? (it.name + ' (' + it.sku + ')') : it.name;
                        }
                    }
                },

                selectItemFromDD(i, it){
                    const r = this.items[i];
                    if(!r) return;

                    r.item_id = it.id;
                    this.pickItem(i, it.id);   // ✅ existing logic (rates/tax etc)
                    r.ddOpen = false;

                    // focus back to input for smooth flow
                    this.$nextTick(() => {
                        const el = document.getElementById('item_search_'+i);
                        if(el) el.focus({preventScroll:true});
                    });
                },

                itemDDDown(i){
                    const r = this.items[i];
                    if(!r) return;
                    const list = this.filteredItems(r.search);
                    if(!r.ddOpen) r.ddOpen = true;
                    if(!list.length) return;
                    r.ddHi = Math.min(list.length - 1, (r.ddHi || 0) + 1);
                    this.scrollItemDDIntoView(i);
                },

                itemDDUp(i){
                    const r = this.items[i];
                    if(!r) return;
                    const list = this.filteredItems(r.search);
                    if(!r.ddOpen) r.ddOpen = true;
                    if(!list.length) return;
                    r.ddHi = Math.max(0, (r.ddHi || 0) - 1);
                    this.scrollItemDDIntoView(i);
                },

                itemDDEnter(i){
                    const r = this.items[i];
                    if(!r) return;
                    const list = this.filteredItems(r.search);
                    if(!r.ddOpen || !list.length) return;
                    const it = list[r.ddHi || 0];
                    if(it) this.selectItemFromDD(i, it);
                },

                scrollItemDDIntoView(i){
                    // optional smooth scroll in dropdown
                    this.$nextTick(() => {
                        // find dropdown container: nearest absolute list inside same td
                        // simplest: do nothing; works fine without scroll sync
                    });
                },

            }
        }
    </script>

</x-layouts.app>
