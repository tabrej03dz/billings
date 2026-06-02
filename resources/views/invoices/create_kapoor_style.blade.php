<x-layouts.app :title="__('Create Sales Invoice')">
    <div x-data="invoiceForm()" x-init="init()" class="space-y-4 max-w-7xl  px-3 sm:px-6 py-4"
        style="margin: -35px">
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
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- JSON payloads --}}
        <script type="application/json" id="clients-json">{!! $clientsJson !!}</script>
        <script type="application/json" id="items-json">{!! $itemsJson !!}</script>
        <script type="application/json" id="categories-json">{!! $categoriesJson !!}</script>
        <script type="application/json" id="metal-rates-json">{!! $metalRatesJson !!}</script>

        <script type="application/json" id="allowed-fields-json">
            {!! json_encode($allowedFields ?? []) !!}
        </script>
        <div class="flex items-center justify-between bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-neutral-100">Create Sales Invoice</h1>
            <button type="button" @click="submitForm()" :disabled="saving"
                class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center gap-2">
                <svg x-show="saving" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span x-text="saving ? 'Saving...' : 'Save'"></span>
            </button>
        </div>

        <form x-ref="form" method="POST" action="{{ route('invoices.store', $docType) }}"
            enctype="multipart/form-data" @submit.prevent="beforeSubmit">
            @csrf
            {{-- TOP PANELS --}}
            <div class="grid lg:grid-cols-4 gap-4">

                {{-- LEFT: Bill To --}}
                <div
                    class="lg:col-span-2 p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-[#1A1D23] ">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100 text-xl">Bill To</div>
                    </div>

                    {{-- ✅ PARTY: Screenshot-like dropdown --}}
                    <label class="block text-xs font-medium text-[#9AA0AC] dark:text-[#9AA0AC] mb-1">Party</label>

                    <div class="relative" @keydown.escape="clientDD.close()"
                        @keydown.arrow-down.prevent="clientDD.down()" @keydown.arrow-up.prevent="clientDD.up()"
                        @keydown.enter.prevent="clientDD.enter()" @click.outside="clientDD.close()">

                        <input type="text" x-model="clientDD.q" placeholder="Search party by name or number or GSTIN"
                            @focus="clientDD.open()" @input="clientDD.open()"
                            class="mb-2 w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                                      bg-white dark:bg-[#242833] text-gray-900 dark:text-neutral-100 text-sm">

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
                                    <div @mouseenter="clientDD.hi = idx" @mousedown.prevent="clientDD.select(c)"
                                        class="px-3 py-2 cursor-pointer flex items-center justify-between gap-3 border-b border-gray-100 dark:border-neutral-800"
                                        :class="idx === clientDD.hi ? 'bg-gray-100 dark:bg-neutral-800' : ''">

                                        <div class="min-w-0">
                                            <div class="text-sm text-gray-900 dark: text-[#9AA0AC] truncate"
                                                x-text="c.mobile ? (c.name + ' (' + c.mobile + ')') : c.name"></div>
                                        </div>

                                        <div class="text-sm text-gray-700 dark: text-[#9AA0AC] shrink-0"
                                            x-text="Number(c.balance ?? 0).toFixed(1)"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button"
                            class="px-3 py-2 rounded border border-gray-300 dark:border-neutral-700 bg-[#4C8DFF] hover:bg-[#6CA8FF]
                                       text-gray-900 dark: text-[#9AA0AC]  text-sm text-white px-4 py-2 rounded-lg text-sm font-medium"
                            @click="openClientModal()">
                            + New
                        </button>
                    </div>

                    {{-- details --}}
                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <div class="text-gray-500 dark: text-[#9AA0AC]">Name</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100" x-text="party.name || '-'">
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-500 dark: text-[#9AA0AC]">Phone</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100" x-text="party.mobile || '-'">
                            </div>
                        </div>
                        <div class="col-span-2">
                            <div class="text-gray-500 dark: text-[#9AA0AC]">Add</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100"
                                x-text="party.address || '-'"></div>
                        </div>
                        <div>
                            <div class="text-gray-500 dark: text-[#9AA0AC]">State</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100" x-text="party.state || '-'">
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-500 dark: text-[#9AA0AC]">State Code</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100"
                                x-text="party.state_code || '-'"></div>
                        </div>
                        <div>
                            <div class="text-gray-500 dark: text-[#9AA0AC]">Pin</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100"
                                x-text="party.pincode || '-'"></div>
                        </div>
                        <div>
                            <div class="text-gray-500 dark: text-[#9AA0AC]">GSTIN</div>
                            <div class="font-semibold text-gray-800 dark:text-neutral-100"
                                x-text="party.gstin || 'Unregistered'"></div>
                        </div>

                        <div>
                            <div class="text-gray-500 dark: text-[#9AA0AC]">GST Type</div>
                            <div class="font-semibold" :class="isIntra() ? 'text-green-600' : 'text-purple-600'"
                                x-text="isIntra() ? 'Intra State (CGST+SGST)' : 'Inter State (IGST)'"></div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Invoice meta --}}
                <div
                    class="lg:col-span-2 p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-[#1A1D23]">
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark: text-[#9AA0AC]">Date</label>
                            <input type="date" name="invoice_date" x-model="hdr.date" required
                                class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-gray-900 dark:text-neutral-100 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark: text-[#9AA0AC]">Bill No.</label>
                            <input :value="invoiceNo" name="invoice_number"
                                class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-gray-50 dark:bg-[#242833] text-gray-700 dark: text-[#9AA0AC] text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Invoice
                                Prefix</label>
                            <input :value="computedPrefix" readonly
                                class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-gray-50 dark:bg-[#242833] text-gray-700 dark: text-[#9AA0AC] text-sm">
                            <input type="hidden" name="invoice_prefix" :value="computedPrefix">
                        </div>

                        @if(Str::contains(strtolower($businessName), 'krinoscco'))
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-[#9AA0AC]">
                                    Kitchen Order Ticket
                                </label>
                                <input type="text" name="kot" x-model="hdr.kot"
                                    class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-gray-900 dark:text-neutral-100 text-sm">
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ================= TABLE ================= --}}
            <div class="border rounded border-gray-200 dark:border-neutral-700 bg-[#BFE0E0] dark:bg-neutral-900 overflow-visible my-4">
                <div class="overflow-x-auto overflow-y-visible">
                    <table class="min-w-full text-sm border-separate border-spacing-0 invoice-table">
                        <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-gray-700 dark:text-neutral-200">
                            <tr class="[&>th]:px-3 [&>th]:py-2 [&>th]:font-medium text-left text-xs">
                                <th>S.No.</th>
                                <th>Description</th>
                                <th>HSN / SAC</th>
                                <th class="text-center">Qty</th>
                                <th>Rate / Price</th>

                                <th x-show="showItemField('making_charge')">Making Rate</th>
                                <th x-show="showItemField('making_charge')">Making Type</th>
                                <th x-show="showItemField('gold_purity')">Gold Rate (₹/g)</th>
                                <th x-show="showItemField('gold_weight')">Gold Wt.(Gm)</th>
                                <th x-show="showItemField('silver_purity')">Silver Rate (₹/g)</th>
                                <th x-show="showItemField('silver_weight')">Silver Wt.(Gm)</th>
                                <th x-show="showItemField('stone_weight')">Gem Stone Wt.(Ct.)</th>
                                <th x-show="showItemField('stone_charges')">Gemstone Charge</th>
                                <th x-show="showItemField('diamond_weight')">Diamond Wt.(Ct.)</th>
                                <th x-show="showItemField('diamond_charges')">Diamond Charge</th>

                                <th>Tax %</th>
                                <th>Amount (Editable)</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700 text-gray-900 dark:text-neutral-100 bg-[#F3F4F6] dark:bg-[#1A1D23]">
                            <template x-for="(row, i) in items" :key="row._k">
                                <tr>
                                    <td class="px-3 py-2 text-center text-xs" x-text="i + 1"></td>

                                    <td class="px-2 py-1 w-[260px] max-w-[260px] align-top">
                                        <div class="relative mb-1" @click.away="closeItemDD(i)">
                                            <div class="flex items-center gap-1">
                                                <input type="text"
                                                    :id="'item_search_' + i"
                                                    x-model="row.search"
                                                    placeholder="Search item by name or sku"
                                                    @focus="openItemDD(i)"
                                                    @input.debounce.50ms="openItemDD(i)"
                                                    @keydown.escape.prevent="closeItemDD(i)"
                                                    @keydown.arrow-down.prevent="itemDDDown(i)"
                                                    @keydown.arrow-up.prevent="itemDDUp(i)"
                                                    @keydown.enter.prevent="itemDDEnter(i)"
                                                    class="flex-1 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-gray-900 dark:text-neutral-100 text-xs">

                                                <button type="button"
                                                    class="px-3 py-1 rounded border border-gray-300 dark:border-neutral-700 text-xs whitespace-nowrap bg-sky-600 text-white hover:bg-sky-700"
                                                    @click="openItemModal(i)">
                                                    + New
                                                </button>
                                            </div>

                                            <div x-show="row.ddOpen"
                                                x-transition.opacity
                                                class="fixed mt-1 rounded border border-gray-200 dark:border-neutral-700 bg-white dark:bg-[#242833] shadow-2xl z-[999999] max-h-56 overflow-hidden"
                                                :style="row.ddStyle + ';width:900px;max-width:95vw;'"
                                                style="display:none;"
                                                @mousedown.prevent>

                                                <div class="grid grid-cols-12 h-56">
                                                    <div class="col-span-7 overflow-auto border-r border-gray-200 dark:border-neutral-700">
                                                        <template x-if="filteredItems(row.search).length === 0">
                                                            <div class="px-3 py-2 text-xs text-gray-500 dark:text-neutral-400">No results</div>
                                                        </template>

                                                        <template x-for="(it, idx) in filteredItems(row.search).slice(0, 80)" :key="it.id">
                                                            <div class="px-3 py-2 text-xs cursor-pointer flex items-start justify-between gap-3 hover:bg-gray-100 dark:hover:bg-neutral-800"
                                                                :class="idx === row.ddHi ? 'bg-gray-100 dark:bg-[#242833]' : ''"
                                                                @mouseenter="
                                                                    row.ddHi = idx;
                                                                    row.ddPreviewName = it.sku ? (it.name + ' (' + it.sku + ')') : it.name;
                                                                    row.ddPreview = it.description || it.desc || it.long_description || '';
                                                                "
                                                                @click="selectItemFromDD(i, it)">
                                                                <div class="flex-1 pr-3 whitespace-normal break-words leading-4"
                                                                    x-text="it.sku ? (it.name + ' (' + it.sku + ')') : it.name"></div>

                                                                <div class="text-[11px] text-gray-500 dark:text-neutral-400 whitespace-nowrap w-[90px] text-right"
                                                                    x-text="it.price ? it.price : ''"></div>
                                                            </div>
                                                        </template>
                                                    </div>

                                                    <div class="col-span-5 p-3 overflow-auto">
                                                        <div class="text-[11px] text-gray-500 dark:text-neutral-400 mb-1">Preview</div>
                                                        <div class="text-xs font-semibold text-gray-900 dark:text-neutral-100 mb-2"
                                                            x-text="row.ddPreviewName || 'Hover on an item'"></div>
                                                        <div class="text-xs text-gray-700 dark:text-neutral-200 whitespace-pre-line"
                                                            x-text="row.ddPreview || 'No description available'"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" :name="'items[' + i + '][item_id]'" :value="row.item_id">

                                        <textarea x-model="row.description"
                                            rows="5"
                                            class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-gray-900 dark:text-neutral-100 text-xs resize-y min-h-[160px]"
                                            placeholder="Enter description..."></textarea>
                                    </td>

                                    <td class="px-3 py-2">
                                        <input x-model="row.hsn"
                                            class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-xs">
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        <input type="number" min="1" step="1"
                                            x-model.number="row.quantity"
                                            @input="onAutoChange(row)"
                                            class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-xs text-center">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.fixed_price"
                                            @input="
                                                row.service_rate = Number(row.fixed_price || 0);
                                                onAutoChange(row);
                                            "
                                            class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-xs text-right">
                                    </td>

                                    <td class="px-3 py-2" x-show="showItemField('making_charge')">
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.making_rate"
                                            @input="onAutoChange(row)"
                                            class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-xs">
                                    </td>

                                    <td class="px-3 py-2" x-show="showItemField('making_charge')">
                                        <select x-model="row.making_charge_type"
                                            @change="onAutoChange(row)"
                                            class="w-36 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-xs">

                                            <option value="percentage">Percent (%)</option>
                                            <option value="fixed">Fixed Amount (₹)</option>
                                            <option value="per_gram">Per Gram</option>
                                            <option value="per_product">Whole Product</option>
                                        </select>

                                        <div class="mt-0.5 text-[10px] text-blue-600"
                                            x-text="makingTypeLabel(row)">
                                        </div>
                                    </td>

                                    <td class="px-3 py-2" x-show="showItemField('gold_purity')">
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.gold_rate"
                                            @input="onAutoChange(row)"
                                            class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-xs">
                                        <div class="mt-0.5 text-[10px] text-gray-500 dark:text-neutral-400"
                                            x-show="hasValue(row.gold_purity)"
                                            x-text="'Purity: ' + row.gold_purity"></div>
                                    </td>

                                    <td class="px-3 py-2" x-show="showItemField('gold_weight')">
                                        <input type="number" step="0.001" min="0"
                                            x-model.number="row.gold_wt"
                                            @input="onAutoChange(row)"
                                            class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-xs">
                                    </td>

                                    <td class="px-3 py-2" x-show="showItemField('silver_purity')">
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.silver_rate"
                                            @input="onAutoChange(row)"
                                            class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-xs">
                                        <div class="mt-0.5 text-[10px] text-gray-500 dark:text-neutral-400"
                                            x-show="hasValue(row.silver_purity)"
                                            x-text="'Purity: ' + row.silver_purity"></div>
                                    </td>

                                    <td class="px-3 py-2" x-show="showItemField('silver_weight')">
                                        <input type="number" step="0.001" min="0"
                                            x-model.number="row.silver_wt"
                                            @input="onAutoChange(row)"
                                            class="w-24 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-xs">
                                    </td>

                                    <td class="px-3 py-2" x-show="showItemField('stone_weight')">
                                        <input type="number" step="0.001" min="0"
                                            x-model.number="row.gemstone_wt"
                                            @input="onAutoChange(row)"
                                            class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-xs">
                                    </td>

                                    <td class="px-3 py-2" x-show="showItemField('stone_charges')">
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.gemstone_charge"
                                            @input="onAutoChange(row)"
                                            class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-xs">
                                    </td>

                                    <td class="px-3 py-2" x-show="showItemField('diamond_weight')">
                                        <input type="number" step="0.001" min="0"
                                            x-model.number="row.diamond_wt"
                                            @input="onAutoChange(row)"
                                            class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-xs">
                                    </td>

                                    <td class="px-3 py-2" x-show="showItemField('diamond_charges')">
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.diamond_charge"
                                            @input="onAutoChange(row)"
                                            class="w-28 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-xs">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0" max="100"
                                            x-model.number="row.tax_percent"
                                            @input="onAutoChange(row)"
                                            class="w-20 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-xs">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.manual_amount"
                                            @input="onAmountEdit(row)"
                                            class="w-32 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-xs text-right">

                                        <div class="mt-0.5 text-[10px]"
                                            :class="row.amount_mode === 'manual' ? 'text-orange-600' : 'text-gray-500 dark:text-neutral-400'"
                                            x-text="row.amount_mode === 'manual' ? 'Manual' : ('Auto: ₹ ' + lineAmount(row).toFixed(2))">
                                        </div>
                                    </td>

                                    <td class="px-3 py-2 text-right">
                                        <button type="button" @click="remove(i)"
                                            class="text-red-600 hover:underline text-lg leading-none">×</button>
                                    </td>
                                </tr>
                            </template>

                            <tr>
                                <td colspan="18" class="px-3 py-2">
                                    <button type="button"
                                        @click="add()"
                                        class="bg-green-500 px-4 py-2 rounded-lg text-white hover:bg-green-600 text-sm">
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
                <div
                    class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-[#1A1D23] space-y-3">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="hdr.reverse_charge"
                            class="rounded border-gray-300 dark:border-neutral-700">
                        <span class="text-sm text-gray-800 dark:text-neutral-100">Reverse Charge (Y/N)</span>
                    </div>
                    <input class="bg-[#242833]" type="hidden" name="reverse_charge"
                        :value="hdr.reverse_charge ? 1 : 0">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Terms and
                            Conditions</label>
                            <textarea name="terms" rows="4" x-model="hdr.terms"
                                class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-gray-900 dark:text-neutral-100 text-sm"></textarea>
                    </div>

                    {{-- Old payment split fields (backend compatible) --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Cash</span>
                            <input type="number" step="0.01" min="0" name="pay_cash"
                                x-model.number="pay.cash"
                                class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">UPI / Online</span>
                            <input type="number" step="0.01" min="0" name="pay_upi"
                                x-model.number="pay.upi"
                                class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Card</span>
                            <input type="number" step="0.01" min="0" name="pay_card"
                                x-model.number="pay.card"
                                class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Cheque</span>
                            <input type="number" step="0.01" min="0" name="pay_cheque"
                                x-model.number="pay.cheque"
                                class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm text-right">
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Credit Sales/Excess Amt.</span>
                            <input type="number" step="0.01" min="0" name="credit_sales_excess"
                                x-model.number="pay.credit_excess"
                                class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Advance</span>
                            <input type="number" step="0.01" min="0" name="advance_amount"
                                x-model.number="pay.advance"
                                class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm text-right">
                        </div>

                        <div class="grid md:grid-cols-2 gap-2 pt-2">
                            <input type="text" name="online_mode" x-model="pay.online_mode"
                                placeholder="online mode (upi/bank/neft...)"
                                class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm">

                            <input type="text" name="online_ref" x-model="pay.online_ref"
                                placeholder="UTR/Txn Ref"
                                class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm">

                            <input type="text" name="upi_id" x-model="pay.upi_id" placeholder="UPI ID"
                                class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm">

                            <input type="text" name="card_last4" x-model="pay.card_last4"
                                placeholder="Card last4"
                                class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm">

                            <input type="text" name="card_ref" x-model="pay.card_ref" placeholder="Card Ref"
                                class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm">

                            <input type="text" name="cheque_no" x-model="pay.cheque_no" placeholder="Cheque No"
                                class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm">

                            <input type="text" name="bank_name" x-model="pay.bank_name" placeholder="Bank Name"
                                class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm">
                        </div>
                    </div>
                </div>

                {{-- Right totals --}}
                <div
                    class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-[#1A1D23] space-y-3">

                    {{-- Additional Charges --}}
                    <div class="flex items-center justify-between">
                        <button type="button" class="text-sm  bg-amber-600 p-2 rounded-lg text-white hover:underline"
                            @click="ui.showCharges = !ui.showCharges">
                            + Add Additional Charges
                        </button>
                        <div class="text-sm font-semibold" x-text="money(chargesTotal())"></div>
                    </div>

                    <div x-show="ui.showCharges" class="space-y-2" style="display:none;">
                        <template x-for="(c, idx) in charges" :key="c._k">
                            <div class="grid grid-cols-12 gap-2">
                                <input
                                    class="col-span-7 border rounded-lg px-2 py-1 text-sm dark:bg-[#242833]dark:border-neutral-700"
                                    placeholder="Charge name (Packing/Transport...)" x-model="c.name">
                                <input type="number" step="0.01" min="0"
                                    class="col-span-4 border rounded-lg px-2 py-1 text-sm text-right dark:bg-[#242833] dark:border-neutral-700"
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
                        <button type="button"
                            class="bg-emerald-600 p-2 rounded-lg text-sm text-white hover:underline"
                            @click="ui.showDiscount = !ui.showDiscount">
                            + Add Discount
                        </button>
                        <div class="text-sm font-semibold text-red-600" x-text="'- ' + money(discountAmount())"></div>
                    </div>

                    <div x-show="ui.showDiscount" class="grid grid-cols-12 gap-2" style="display:none;">
                        <select
                            class="col-span-4 border rounded-lg px-2 py-1 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                            x-model="discount.type" @change="calc()">
                            <option value="flat">Flat ₹</option>
                            <option value="percent">%</option>
                        </select>

                        <input type="number" step="0.01" min="0"
                            class="col-span-8 border rounded-lg px-2 py-1 text-sm text-right dark:bg-[#242833] dark:border-neutral-700"
                            x-model.number="discount.value" @input="calc()" placeholder="Discount value">
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
                        <input class="bg-[#242833]" type="checkbox" class="rounded" x-model="payment.markFullyPaid"
                            @change="toggleFullyPaid()">
                    </label>

                    {{-- Amount Received + Mode --}}
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm text-gray-600 dark:text-neutral-300">Amount Received</div>

                        <div class="flex items-center gap-2 w-[320px] max-w-full">
                            <div
                                class="flex-1 flex items-center border rounded-lg overflow-hidden dark:border-neutral-700">
                                <span class="px-3 text-gray-500 dark:text-neutral-400">₹</span>
                                <input type="number" step="0.01" min="0"
                                    class="w-full px-2 py-2 text-sm bg-[#F3F4F6] dark:bg-[#242833] text-right outline-none dark:text-neutral-100"
                                    x-model.number="payment.received" @input="onReceivedInput()">
                            </div>

                            <select
                                class="w-36 border rounded-lg px-2 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                x-model="payment.mode" @change="onReceivedInput()">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="card">Card</option>
                                <option value="cheque">Cheque</option>
                                <option value="bank">Bank</option>
                            </select>
                        </div>
                    </div>

                    <div x-show="payment.mode !== 'cash'" style="display:none;"
                        class="flex items-center justify-between gap-3">
                        <div class="text-sm text-gray-600 dark:text-neutral-300">
                            Select Bank
                        </div>

                        <div class="w-[320px] max-w-full">
                            <select
                                class="w-full border rounded-lg px-2 py-2 text-sm dark:bg-[#242833] dark:border-neutral-700"
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

                    <input type="hidden" name="bank_account_id"
                        :value="payment.mode !== 'cash' ? payment.bank_account_id : ''">

                    {{-- Balance Amount --}}
                    <div class="flex justify-between pt-1">
                        <span class="text-sm font-semibold text-green-600">Balance Amount</span>
                        <span class="text-sm font-bold text-green-600" x-text="money(balanceAmount())"></span>
                    </div>

                    {{-- ================= SIGNATURE UPLOAD ================= --}}
                   @php
                        $businessSignature = $businessSignature ?? null;

                        $businessSignatureUrl = $businessSignature
                            ? (\Illuminate\Support\Str::startsWith($businessSignature, ['http://', 'https://'])
                                ? $businessSignature
                                : asset('storage/' . $businessSignature))
                            : null;
                    @endphp

                    <div class="pt-2">
                        <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 mb-2">
                            Signature
                        </div>

                        @if($businessSignatureUrl)
                            <div class="mb-3 p-3 rounded-xl border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-[#242833]">
                                <div class="text-xs font-semibold text-gray-600 dark:text-neutral-300 mb-2">
                                    Current Business Signature
                                </div>

                                <img src="{{ $businessSignatureUrl }}"
                                    alt="Business Signature"
                                    class="h-20 max-w-xs object-contain bg-white rounded-lg border border-gray-200 p-2">

                                <label class="mt-3 inline-flex items-center gap-2 text-sm text-red-600">
                                    <input type="checkbox" name="remove_signature" value="1"
                                        class="rounded border-gray-300 dark:border-neutral-700">
                                    Remove this signature
                                </label>
                            </div>
                        @endif

                        <div class="grid gap-2">
                            <input type="file" name="signature" accept="image/*"
                                class="w-full text-sm file:mr-3 file:px-3 file:py-2 file:rounded-lg
                                    file:border-0 file:bg-gray-100 file:text-gray-700
                                    dark:file:bg-neutral-800 dark:file:text-neutral-200
                                    border border-gray-300 dark:border-neutral-700
                                    rounded-lg px-3 py-2 bg-white dark:bg-neutral-900
                                    text-gray-900 dark:text-neutral-100">

                            <div class="text-[11px] text-gray-500 dark:text-neutral-400">
                                Allowed: JPG/PNG/WebP • Max 2MB. New upload karoge to old signature replace ho jayega.
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
                    <input type="hidden" name="cgst_percent" :value="isIntra() ? (avgTaxPercent() / 2) : 0">
                    <input type="hidden" name="sgst_percent" :value="isIntra() ? (avgTaxPercent() / 2) : 0">
                    <input type="hidden" name="igst_percent" :value="isIntra() ? 0 : avgTaxPercent()">
                    <input type="hidden" name="payment_method" :value="payment.mode">
                </div>
            </div>

            {{-- Hidden JSON --}}
            <input type="hidden" id="items_json" name="items_json">

            <div class="text-right">
                <button type="button" @click="submitForm()" :disabled="saving"
                    class="px-4 py-2 rounded bg-green-800 text-white hover:bg-green-900 disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center gap-2">
                    <svg x-show="saving" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span x-text="saving ? 'Saving...' : 'Save'"></span>
                </button>
            </div>

            {{-- =================== CLIENT MODAL =================== --}}
            <div x-show="modals.client" x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center px-3" style="display:none;">
                <div class="absolute inset-0 bg-black/50" @click="closeClientModal()"></div>

                <div
                    class="relative w-full max-w-2xl bg-white dark:bg-neutral-900 rounded-2xl shadow-xl border border-gray-200 dark:border-neutral-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">Add New Client</h3>
                        <button type="button" class="text-2xl leading-none text-gray-500 hover:text-red-600"
                            @click="closeClientModal()">×</button>
                    </div>

                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Name *</label>
                            <input x-model="newClient.name"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                placeholder="Client name">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Mobile </label>
                            <input x-model="newClient.mobile"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                placeholder="10 digit mobile">
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
                                x-model="newClient.state_pick" @change="applyClientState()">
                                <option value="">-- Select State --</option>

                                <template x-for="st in states" :key="st.code">
                                    <option :value="st.code + ',' + st.name" x-text="st.name + ' (' + st.code + ')'">
                                    </option>
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
                            <input x-model="newClient.pincode"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                placeholder="6 digit pin">
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Address</label>
                            <input x-model="newClient.address"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                placeholder="Full address">
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">GSTIN</label>

                            <input x-model="newClient.gstin" @input.debounce.350ms="onGstinInput('client')"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                placeholder="optional">

                            <div class="mt-1 text-[11px]" x-show="clientGstin.status !== 'idle'"
                                :class="clientGstin.status === 'valid' ? 'text-green-600' :
                                    (clientGstin.status==='checking' ?
                                    'text-blue-600' : 'text-red-600')"
                                x-text="clientGstin.message">
                            </div>
                        </div>

                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <div class="text-sm text-red-600" x-text="newClientError"></div>

                            <label
                                class="mt-2 inline-flex items-center gap-2 text-sm text-gray-700 dark:text-neutral-200 select-none">
                                <input type="checkbox" class="rounded border-gray-300 dark:border-neutral-700"
                                    x-model="clientAutoSelect">
                                Save for future
                            </label>
                        </div>

                        <div class="flex gap-2 shrink-0">
                            <button type="button" class="px-4 py-2 rounded-xl border dark:border-neutral-700"
                                @click="closeClientModal()">
                                Cancel
                            </button>

                            <button type="button"
                                class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
                                :disabled="savingClient" @click="saveClient()">
                                <span x-show="!savingClient">Add Client</span>
                                <span x-show="savingClient">Saving...</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            {{-- =================== ITEM MODAL =================== --}}
            <div x-show="modals.item" x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center px-3" style="display:none;">
                <div class="absolute inset-0 bg-black/50" @click="closeItemModal()"></div>

                <div
                    class="relative w-full max-w-3xl bg-white dark:bg-neutral-900 rounded-2xl shadow-xl border border-gray-200 dark:border-neutral-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">Create New Item</h3>
                        <button type="button" class="text-2xl leading-none text-gray-500 hover:text-red-600"
                            @click="closeItemModal()">×</button>
                    </div>

                    <div class="grid md:grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Type *</label>
                            <select x-model="newItem.type"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                <option value="product">Product</option>
                                <option value="service">Service</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Category</label>
                            <select x-model="newItem.category_id"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                <option value="">-- Select Category --</option>
                                <template x-for="cat in categories" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Name *</label>
                            <input x-model="newItem.name"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                placeholder="Item name">
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">SKU</label>
                            <input x-model="newItem.sku"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                placeholder="optional">
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Tax %</label>
                            <input type="number" step="0.01" min="0" max="100"
                                x-model.number="newItem.tax_rate"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                        </div>

                        <div x-show="newItem.type==='product'">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">HSN</label>
                            <input x-model="newItem.hsn"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                        </div>

                        <div x-show="newItem.type==='service'">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">SAC</label>
                            <input x-model="newItem.sac"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                        </div>

                        <div class="md:col-span-3">
                            <label
                                class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Description</label>
                            <input x-model="newItem.description"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                        </div>

                        {{-- SERVICE fields --}}
                        <div x-show="newItem.type==='service'">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Service Price (₹)
                                *</label>
                            <input type="number" step="0.01" min="0" x-model.number="newItem.price"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                        </div>

                        {{-- PRODUCT fields --}}
                        <template x-if="newItem.type==='product'">
                            <div
                                class="md:col-span-3 grid md:grid-cols-3 gap-3 p-3 rounded-xl border border-gray-200 dark:border-neutral-700">
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Making
                                        Charge</label>
                                    <input type="number" step="0.01" min="0"
                                        x-model.number="newItem.making_charge"
                                        class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Gold
                                        Weight (g)</label>
                                    <input type="number" step="0.001" min="0"
                                        x-model.number="newItem.gold_weight"
                                        class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Gold
                                        Purity</label>
                                    <input x-model="newItem.gold_purity" placeholder="e.g. 22K / 916"
                                        class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Silver
                                        Weight (g)</label>
                                    <input type="number" step="0.001" min="0"
                                        x-model.number="newItem.silver_weight"
                                        class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Silver
                                        Purity</label>
                                    <input x-model="newItem.silver_purity" placeholder="e.g. 999"
                                        class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div></div>

                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Stone Wt
                                        (ct)</label>
                                    <input type="number" step="0.001" min="0"
                                        x-model.number="newItem.stone_weight"
                                        class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Diamond Wt
                                        (ct)</label>
                                    <input type="number" step="0.001" min="0"
                                        x-model.number="newItem.diamond_weight"
                                        class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div></div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <div class="text-sm text-red-600" x-text="newItemError"></div>

                            <label
                                class="mt-2 inline-flex items-center gap-2 text-sm text-gray-700 dark:text-neutral-200 select-none">
                                <input type="checkbox" class="rounded border-gray-300 dark:border-neutral-700"
                                    x-model="itemAutoSelect">
                                Save for future
                            </label>
                        </div>

                        <div class="flex gap-2 shrink-0">
                            <button type="button" class="px-4 py-2 rounded-xl border dark:border-neutral-700"
                                @click="closeItemModal()">
                                Cancel
                            </button>

                            <button type="button"
                                class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
                                :disabled="savingItem" @click="saveItem()">
                                <span x-show="!savingItem"
                                    x-text="itemAutoSelect ? 'Save Item' : 'Use for this Invoice'"></span>
                                <span x-show="savingItem">Saving...</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            {{-- =================== PREVIOUS INVOICE CONFIRM MODAL =================== --}}
            <div x-show="confirmLoadModal" x-transition.opacity
                class="fixed inset-0 z-[9999] flex items-center justify-center px-3"
                style="display:none;">

                <div class="absolute inset-0 bg-black/50" @click="cancelApplyLastInvoice()"></div>

                <div class="relative w-full max-w-4xl bg-white dark:bg-neutral-900 rounded-2xl shadow-xl border border-gray-200 dark:border-neutral-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">
                            Previous Invoice Found
                        </h3>
                        <button type="button"
                            class="text-2xl leading-none text-gray-500 hover:text-red-600"
                            @click="cancelApplyLastInvoice()">×</button>
                    </div>

                    <template x-if="pendingInvoicePreview">
                        <div class="space-y-4">
                            <div class="grid md:grid-cols-4 gap-3 text-sm">
                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">Invoice No</div>
                                    <div class="font-semibold text-gray-900 dark:text-neutral-100"
                                        x-text="pendingInvoicePreview.invoice_number || '-'"></div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">Invoice Date</div>
                                    <div class="font-semibold text-gray-900 dark:text-neutral-100"
                                        x-text="pendingInvoicePreview.invoice_date || '-'"></div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">Items Count</div>
                                    <div class="font-semibold text-gray-900 dark:text-neutral-100"
                                        x-text="pendingInvoicePreview.items_count || 0"></div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">Round Off</div>
                                    <div class="font-semibold text-gray-900 dark:text-neutral-100">
                                        ₹ <span x-text="Number(pendingInvoicePreview.round_off || 0).toFixed(2)"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="border rounded-xl border-gray-200 dark:border-neutral-700 p-3 max-h-72 overflow-auto">
                                <div class="text-sm font-semibold mb-3 text-gray-900 dark:text-neutral-100">Previous Items</div>

                                <template x-if="pendingInvoicePreview.items && pendingInvoicePreview.items.length">
                                    <div class="space-y-2">
                                        <template x-for="(it, idx) in pendingInvoicePreview.items" :key="idx">
                                            <div class="border-b border-gray-200 dark:border-neutral-700 pb-2">
                                                <div class="font-medium text-sm text-gray-900 dark:text-neutral-100"
                                                    x-text="(idx + 1) + '. ' + (it.description || '-')"></div>

                                                <div class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
                                                    Type:
                                                    <span x-text="it.item_type || '-'"></span>
                                                    |
                                                    Qty:
                                                    <span x-text="it.quantity || 0"></span>
                                                    |
                                                    Tax:
                                                    <span x-text="it.tax_percent || 0"></span>%
                                                    |
                                                    Amount:
                                                    ₹<span x-text="Number(it.manual_amount || 0).toFixed(2)"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="!pendingInvoicePreview.items || pendingInvoicePreview.items.length === 0">
                                    <div class="text-sm text-gray-500 dark:text-neutral-400">No previous items found.</div>
                                </template>
                            </div>

                            <div class="grid md:grid-cols-3 gap-3 text-sm">
                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">Discount</div>
                                    <div class="font-semibold text-gray-900 dark:text-neutral-100">
                                        ₹ <span x-text="Number(pendingInvoicePreview.discount_total || 0).toFixed(2)"></span>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">Charges</div>
                                    <div class="font-semibold text-gray-900 dark:text-neutral-100">
                                        ₹ <span x-text="Number(pendingInvoicePreview.charge_total || 0).toFixed(2)"></span>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">TCS</div>
                                    <div class="font-semibold text-gray-900 dark:text-neutral-100">
                                        <span x-text="Number(pendingInvoicePreview.tcs_percent || 0).toFixed(2)"></span>%
                                        /
                                        ₹ <span x-text="Number(pendingInvoicePreview.tcs_amount || 0).toFixed(2)"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-3 text-sm">
                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">Payment Summary</div>
                                    <div class="font-medium text-gray-900 dark:text-neutral-100 leading-6">
                                        Cash: ₹<span x-text="Number(pendingInvoicePreview.pay_cash || 0).toFixed(2)"></span>,
                                        UPI: ₹<span x-text="Number(pendingInvoicePreview.pay_upi || 0).toFixed(2)"></span>,
                                        Card: ₹<span x-text="Number(pendingInvoicePreview.pay_card || 0).toFixed(2)"></span>,
                                        Cheque: ₹<span x-text="Number(pendingInvoicePreview.pay_cheque || 0).toFixed(2)"></span>
                                    </div>
                                </div>

                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">Terms</div>
                                    <div class="font-medium text-gray-900 dark:text-neutral-100 whitespace-pre-line"
                                        x-text="pendingInvoicePreview.terms || '-'"></div>
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button"
                                    class="px-4 py-2 rounded-xl border border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200"
                                    @click="cancelApplyLastInvoice()">
                                    No, Not Now
                                </button>

                                <button type="button"
                                    class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700"
                                    @click="confirmApplyLastInvoice()">
                                    Continieu With These Details
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </form>
    </div>

    <script type="application/json" id="banks-json">{!! $banksJson !!}</script>
    <script>

        function invoiceForm() {
            // ---------- SAFE JSON READ ----------
            const readJSON = (id, fallback) => {
                try {
                    const el = document.getElementById(id);
                    return JSON.parse(el?.textContent || JSON.stringify(fallback));
                } catch (e) {
                    return fallback;
                }
            };

            const CLIENTS = readJSON('clients-json', []);
            const ITEMS = readJSON('items-json', []);
            const METAL_RATES = readJSON('metal-rates-json', []);
            const BANKS = readJSON('banks-json', []);
            // const CATEGORIES = JSON.parse(document.getElementById('categories-json')?.textContent || '[]');
            const CATEGORIES = readJSON('categories-json', []);
            const ALLOWED_FIELDS = readJSON('allowed-fields-json', []);

            const showAllowedField = (field) => {
                return ALLOWED_FIELDS.length === 0 || ALLOWED_FIELDS.includes(field);
            };

            const BIZ_STATE_CODE = @js($businessStateCode ?? '');
            const BIZ_GSTIN = @js($businessGstin ?? '');
            const DEFAULT_TERMS = @js($defaultTerms);
            const TODAY = @js($today);
            const DOC_TYPE = @js($docType);
            const LAST_CLIENT_INVOICE_BASE_URL = @js(url('/invoices/client'));

            // ---------- HELPERS ----------
            const n = (v, d = 0) => {
                const x = Number(v);
                return Number.isFinite(x) ? x : d;
            };

            const clamp = (v, min, max) => Math.min(max, Math.max(min, v));
            const s = (v) => (v ?? '').toString();
            const lower = (v) => s(v).toLowerCase();
            const money = (v) => '₹ ' + n(v).toFixed(2);

            const normalizeGstin = (v) => s(v).toUpperCase().replace(/[^0-9A-Z]/g, '').trim();

            const validateGstinLocal = (gstin) => {
                const g = normalizeGstin(gstin);
                if (!g) return { ok: true, empty: true, message: '' };

                if (g.length !== 15) {
                    return { ok: false, empty: false, message: 'GSTIN must be 15 characters.' };
                }

                const re = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/;
                if (!re.test(g)) {
                    return { ok: false, empty: false, message: 'GSTIN format is invalid (check state/PAN/etc).' };
                }

                const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                const mod = 36;
                const codePoint = (c) => chars.indexOf(c);

                let sum = 0;
                let f = 2;

                for (let i = 13; i >= 0; i--) {
                    const v = codePoint(g[i]);
                    if (v === -1) {
                        return { ok: false, empty: false, message: 'GSTIN has invalid characters.' };
                    }

                    const p = v * f;
                    f = (f === 2) ? 1 : 2;
                    sum += Math.floor(p / mod) + (p % mod);
                }

                const checkCodePoint = (mod - (sum % mod)) % mod;
                const expected = chars[checkCodePoint];
                const actual = g[14];

                if (expected !== actual) {
                    return { ok: false, empty: false, message: 'GSTIN checksum mismatch (likely wrong GSTIN).' };
                }

                return { ok: true, empty: false, message: 'GSTIN looks valid.' };
            };

            const keyCode = (v) => s(v).trim().replace(/^0+/, '');

            // ---------- CONSTANTS ----------
            const STATES = [
                { code: '01', name: 'Jammu and Kashmir' },
                { code: '02', name: 'Himachal Pradesh' },
                { code: '03', name: 'Punjab' },
                { code: '04', name: 'Chandigarh' },
                { code: '05', name: 'Uttarakhand' },
                { code: '06', name: 'Haryana' },
                { code: '07', name: 'Delhi' },
                { code: '08', name: 'Rajasthan' },
                { code: '09', name: 'Uttar Pradesh' },
                { code: '10', name: 'Bihar' },
                { code: '11', name: 'Sikkim' },
                { code: '12', name: 'Arunachal Pradesh' },
                { code: '13', name: 'Nagaland' },
                { code: '14', name: 'Manipur' },
                { code: '15', name: 'Mizoram' },
                { code: '16', name: 'Tripura' },
                { code: '17', name: 'Meghalaya' },
                { code: '18', name: 'Assam' },
                { code: '19', name: 'West Bengal' },
                { code: '20', name: 'Jharkhand' },
                { code: '21', name: 'Odisha' },
                { code: '22', name: 'Chhattisgarh' },
                { code: '23', name: 'Madhya Pradesh' },
                { code: '24', name: 'Gujarat' },
                { code: '26', name: 'Dadra and Nagar Haveli and Daman and Diu' },
                { code: '27', name: 'Maharashtra' },
                { code: '29', name: 'Karnataka' },
                { code: '30', name: 'Goa' },
                { code: '31', name: 'Lakshadweep' },
                { code: '32', name: 'Kerala' },
                { code: '33', name: 'Tamil Nadu' },
                { code: '34', name: 'Puducherry' },
                { code: '35', name: 'Andaman and Nicobar Islands' },
                { code: '36', name: 'Telangana' },
                { code: '37', name: 'Andhra Pradesh' },
                { code: '38', name: 'Ladakh' },
            ];

            // ---------- FACTORIES ----------
            const blankParty = () => ({
                name: '',
                address: '',
                state: '',
                state_code: '',
                mobile: '',
                gstin: '',
                pincode: ''
            });

            const rowTemplate = () => ({
                _k: Date.now() + Math.random(),
                item_id: null,
                item_type: null,

                search: '',
                ddOpen: false,
                ddHi: 0,
                ddStyle: '',
                ddPreviewName: '',
                ddPreview: '',

                description: '',
                hsn: '',
                quantity: 1,

                // making_charge_type: 'percent',
                making_charge_type: 'percentage',
                making_rate: 0,
                gold_purity: null,
                silver_purity: null,
                gold_rate: 0,
                silver_rate: 0,
                silver_wt: 0,
                gold_wt: 0,
                gemstone_wt: 0,
                diamond_wt: 0,
                gemstone_charge: 0,
                diamond_charge: 0,

                service_rate: 0,
                fixed_price: 0, // ✅ items.price: agar price set hai to metal calculation skip hogi
                tax_percent: 0,

                amount_mode: 'auto',
                manual_amount: 0,
            });

            const chargeTemplate = () => ({
                _k: Date.now() + Math.random(),
                name: '',
                amount: 0,
            });

            // ---------- CLIENT DROPDOWN CONTROLLER ----------
            const createClientDD = (ctx) => ({
                isOpen: false,
                q: '',
                hi: 0,

                open() {
                    this.isOpen = true;
                    this.hi = 0;
                },
                close() {
                    this.isOpen = false;
                },

                filtered() {
                    const q = lower(this.q).trim();
                    const list = ctx.clients || [];
                    if (!q) return list;

                    return list.filter(c => {
                        const name = lower(c.name);
                        const mob = lower(c.mobile);
                        const gstin = lower(c.gstin);
                        const code = lower(c.state_code);

                        return (
                            name.includes(q) ||
                            mob.includes(q) ||
                            gstin.includes(q) ||
                            code.includes(q)
                        );
                    });
                },

                down() {
                    if (!this.isOpen) this.open();
                    const list = this.filtered();
                    if (!list.length) return;
                    this.hi = Math.min(list.length - 1, this.hi + 1);
                },

                up() {
                    if (!this.isOpen) this.open();
                    const list = this.filtered();
                    if (!list.length) return;
                    this.hi = Math.max(0, this.hi - 1);
                },

                enter() {
                    const list = this.filtered();
                    if (!this.isOpen || !list.length) return;
                    this.select(list[this.hi]);
                },

                select(c) {
                    ctx.clientId = c.id;
                    this.q = c.mobile ? (c.name + ' (' + c.mobile + ')') : c.name;
                    this.close();
                }
            });

           

            return {
                // DATA
                clients: CLIENTS,
                itemsData: ITEMS,
                categories: CATEGORIES,

                 showItemField(field) {
                    return showAllowedField(field);
                },



                hasValue(v) {
                    if (v === null || v === undefined) return false;
                    if (String(v).trim() === '') return false;
                    return Number(v) > 0 || isNaN(Number(v));
                },


                metalRates: METAL_RATES,
                banks: BANKS,
                states: STATES,

                clientId: '',
                party: blankParty(),
                clientDD: null,

                hdr: {
                    date: TODAY,
                    transport_mode: 'By Hand',
                    gst_no: BIZ_GSTIN,
                    reverse_charge: false,
                    terms: DEFAULT_TERMS,
                    kot_input: '',
                    kots: [],
                },

                basePrefix: @js($basePrefix),
                computedPrefix: @js($suggestedPrefix),
                invoiceNo: @js($initialInvoiceNo),

                payment: {
                    received: 0,
                    mode: 'cash',
                    markFullyPaid: false,
                    bank_account_id: ''
                },

                pay: {
                    cash: 0,
                    upi: 0,
                    card: 0,
                    cheque: 0,
                    credit_excess: 0,
                    advance: 0,
                    online_mode: '',
                    online_ref: '',
                    upi_id: '',
                    card_last4: '',
                    card_ref: '',
                    cheque_no: '',
                    bank_name: '',
                },

                ui: {
                    showCharges: false,
                    showDiscount: false
                },

                charges: [],
                discount: {
                    type: 'flat',
                    value: 0
                },
                tcs: {
                    apply: false,
                    percent: 0
                },
                roundOff: {
                    enabled: false
                },

                items: [],

                saving: false,
                savingClient: false,
                savingItem: false,
                newClientError: '',
                newItemError: '',
                activeRowIndex: null,
                clientAutoSelect: true,
                itemAutoSelect: true,

                gstin: {
                    status: 'idle',
                    message: '',
                    value: ''
                },
                clientGstin: {
                    status: 'idle',
                    message: '',
                    value: ''
                },

                modals: {
                    client: false,
                    item: false
                },

                lastInvoiceInfo: {
                    found: false,
                    invoice_number: '',
                    invoice_id: null,
                },

                loadingLastInvoice: false,

                confirmLoadModal: false,
                pendingInvoicePreview: null,
                pendingInvoiceData: null,

                newClient: {
                    name: '',
                    mobile: '',
                    address: '',
                    state: '',
                    state_code: '',
                    gstin: '',
                    pincode: '',
                    state_pick: ''
                },

                newItem: {

                    type: 'product',
                    name: '',
                    sku: '',
                    description: '',
                    category_id: '',
                    tax_rate: 0,
                    hsn: '',
                    sac: '',
                    price: 0,
                    making_charge: 0,
                    gold_weight: 0,
                    gold_purity: '',
                    silver_weight: 0,
                    silver_purity: '',
                    stone_weight: 0,
                    diamond_weight: 0
                    
                },

                // ---------- UTILS ----------
                csrf() {
                    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                },

                money,

                makingTypeLabel(row) {
                    const type = row.making_charge_type || 'percentage';

                    if (type === 'percentage') return 'Making: % of product amount';
                    if (type === 'fixed') return 'Making: fixed amount';
                    if (type === 'per_gram') return 'Making: ₹ per gram';
                    if (type === 'per_product') return 'Making: whole product';

                    return 'Making: % of product amount';
                },

                filteredItems(q) {
                    const query = lower(q).trim();
                    const list = this.itemsData || [];
                    if (!query) return list;

                    return list.filter(it => {
                        const name = lower(it.name);
                        const sku = lower(it.sku);
                        const desc = lower(it.description || it.desc || it.long_description);
                        return name.includes(query) || sku.includes(query) || desc.includes(query);
                    });
                },

                getItemSearchLabel(itemId) {
                    const it = (this.itemsData || []).find(x => String(x.id) === String(itemId));
                    if (!it) return '';
                    return it.sku ? `${it.name} (${it.sku})` : (it.name || '');
                },

                normalizeItemType(v) {
                    const t = s(v).toLowerCase().trim();
                    return (t === 'product' || t === 'service') ? t : 'service';
                },

                inferItemType(it) {
                    const explicitType = this.normalizeItemType(it?.type || it?.item_type || '');

                    if (explicitType === 'product' || explicitType === 'service') {
                        return explicitType;
                    }

                    const hasServicePrice =
                        Number(it?.price || 0) > 0 &&
                        Number(it?.gold_weight ?? it?.gold_wt ?? 0) <= 0 &&
                        Number(it?.silver_weight ?? it?.silver_wt ?? 0) <= 0 &&
                        Number(it?.making_charge ?? it?.making_rate ?? 0) <= 0;

                    if (hasServicePrice) {
                        return 'service';
                    }

                    const hasProductData =
                        Number(it?.gold_weight ?? it?.gold_wt ?? 0) > 0 ||
                        Number(it?.silver_weight ?? it?.silver_wt ?? 0) > 0 ||
                        Number(it?.stone_weight ?? it?.gemstone_wt ?? 0) > 0 ||
                        Number(it?.diamond_weight ?? it?.diamond_wt ?? 0) > 0 ||
                        Number(it?.making_charge ?? it?.making_rate ?? 0) > 0 ||
                        !!(it?.gold_purity) ||
                        !!(it?.silver_purity);

                    if (hasProductData) {
                        return 'product';
                    }

                    if ((it?.sac || '') && !(it?.hsn || '')) {
                        return 'service';
                    }

                    return 'service';
                },

                resetRowForService(r) {
                    r.making_rate = 0;
                    r.gold_purity = null;
                    r.silver_purity = null;
                    r.gold_rate = 0;
                    r.silver_rate = 0;
                    r.gold_wt = 0;
                    r.silver_wt = 0;
                    r.gemstone_wt = 0;
                    r.diamond_wt = 0;
                },

                resetRowForProduct(r) {
                    r.service_rate = 0;
                },

                init() {
                    this.clientDD = createClientDD(this);

                    this.$watch('clientId', () => this.syncParty());

                    if (!this.items.length) this.items.push(rowTemplate());
                    if (!this.charges.length) this.charges.push(chargeTemplate());

                    this.syncParty();
                    this.onReceivedInput();
                    this.calc();

                    const reposition = () => {
                        for (let idx = 0; idx < this.items.length; idx++) {
                            if (this.items[idx]?.ddOpen) this.setItemDDPos(idx);
                        }
                    };

                    window.addEventListener('scroll', reposition, true);
                    window.addEventListener('resize', reposition);
                },

                // ---------- PARTY ----------
                async syncParty() {
                    const c = (this.clients || []).find(x => String(x.id) === String(this.clientId));

                    this.party = c ? {
                        name: c.name ?? '',
                        mobile: c.mobile ?? '',
                        address: c.address ?? '',
                        state: c.state ?? '',
                        state_code: c.state_code ?? '',
                        gstin: c.gstin ?? '',
                        pincode: c.pincode ?? '',
                    } : blankParty();

                    if (c && this.clientDD) {
                        this.clientDD.q = c.mobile ? (c.name + ' (' + c.mobile + ')') : c.name;
                    }

                    this.calc();
                    await this.previewLastInvoiceForClient();
                },

                isIntra() {
                    const bizCode = keyCode(BIZ_STATE_CODE);
                    const partyCode = keyCode(this.party.state_code);
                    if (!bizCode || !partyCode) return false;
                    return bizCode === partyCode;
                },

                hasProduct() {
                    return (this.items || []).some(r => this.normalizeItemType(r.item_type) === 'product');
                },

                hasService() {
                    return (this.items || []).some(r => this.normalizeItemType(r.item_type) === 'service');
                },

                // ---------- LAST INVOICE PREVIEW / APPLY ----------
                resetInvoiceDataKeepParty() {
                    this.items = [rowTemplate()];
                    this.charges = [chargeTemplate()];

                    this.discount = { type: 'flat', value: 0 };
                    this.tcs = { apply: false, percent: 0 };
                    this.roundOff = { enabled: false };

                    this.pay = {
                        cash: 0,
                        upi: 0,
                        card: 0,
                        cheque: 0,
                        credit_excess: 0,
                        advance: 0,
                        online_mode: '',
                        online_ref: '',
                        upi_id: '',
                        card_last4: '',
                        card_ref: '',
                        cheque_no: '',
                        bank_name: '',
                    };

                    this.payment = {
                        received: 0,
                        mode: 'cash',
                        markFullyPaid: false,
                        bank_account_id: ''
                    };

                    this.hdr.date = TODAY;
                    this.hdr.reverse_charge = false;
                    this.hdr.terms = DEFAULT_TERMS;

                    this.lastInvoiceInfo = {
                        found: false,
                        invoice_number: '',
                        invoice_id: null,
                    };

                    this.onReceivedInput();
                    this.calc();
                },

                async previewLastInvoiceForClient() {
                    if (!this.clientId) {
                        this.resetInvoiceDataKeepParty();
                        this.pendingInvoicePreview = null;
                        this.pendingInvoiceData = null;
                        this.confirmLoadModal = false;
                        return;
                    }

                    try {
                        this.loadingLastInvoice = true;

                        const url = `${LAST_CLIENT_INVOICE_BASE_URL}/${this.clientId}/last?doc_type=${encodeURIComponent(DOC_TYPE)}`;

                        const res = await fetch(url, {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });

                        const data = await res.json().catch(() => ({}));

                        if (!res.ok || !data?.found || !data?.invoice) {
                            this.resetInvoiceDataKeepParty();
                            this.pendingInvoicePreview = null;
                            this.pendingInvoiceData = null;
                            this.confirmLoadModal = false;
                            return;
                        }

                        const inv = data.invoice;

                        this.pendingInvoiceData = inv;
                        this.pendingInvoicePreview = {
                            invoice_number: inv.invoice_number || '',
                            invoice_date: inv.invoice_date || '',
                            terms: inv.terms || '',
                            reverse_charge: !!inv.reverse_charge,
                            pay_cash: Number(inv.pay_cash || 0),
                            pay_upi: Number(inv.pay_upi || 0),
                            pay_card: Number(inv.pay_card || 0),
                            pay_cheque: Number(inv.pay_cheque || 0),
                            credit_sales_excess: Number(inv.credit_sales_excess || 0),
                            advance_amount: Number(inv.advance_amount || 0),
                            discount_total: Number(inv.discount_total || 0),
                            charge_total: Number(inv.charge_total || 0),
                            tcs_percent: Number(inv.tcs_percent || 0),
                            tcs_amount: Number(inv.tcs_amount || 0),
                            round_off: Number(inv.round_off || 0),
                            items_count: Array.isArray(inv.items) ? inv.items.length : 0,
                            items: Array.isArray(inv.items) ? inv.items : [],
                        };

                        this.confirmLoadModal = true;
                    } catch (e) {
                        console.error('Failed to preview previous invoice:', e);
                        this.resetInvoiceDataKeepParty();
                        this.pendingInvoicePreview = null;
                        this.pendingInvoiceData = null;
                        this.confirmLoadModal = false;
                    } finally {
                        this.loadingLastInvoice = false;
                    }
                },

                confirmApplyLastInvoice() {
                    if (!this.pendingInvoiceData) {
                        this.confirmLoadModal = false;
                        return;
                    }

                    this.applyLastInvoiceData(this.pendingInvoiceData);
                    this.confirmLoadModal = false;
                },

                cancelApplyLastInvoice() {
                    this.resetInvoiceDataKeepParty();
                    this.pendingInvoicePreview = null;
                    this.pendingInvoiceData = null;
                    this.confirmLoadModal = false;
                },

                applyLastInvoiceData(inv) {
                    this.lastInvoiceInfo = {
                        found: true,
                        invoice_number: inv.invoice_number || '',
                        invoice_id: inv.id || null,
                    };

                    this.hdr.date = TODAY;
                    this.hdr.reverse_charge = !!inv.reverse_charge;
                    this.hdr.terms = inv.terms || DEFAULT_TERMS;

                    this.pay.cash = Number(inv.pay_cash || 0);
                    this.pay.upi = Number(inv.pay_upi || 0);
                    this.pay.card = Number(inv.pay_card || 0);
                    this.pay.cheque = Number(inv.pay_cheque || 0);
                    this.pay.credit_excess = Number(inv.credit_sales_excess || 0);
                    this.pay.advance = Number(inv.advance_amount || 0);
                    this.pay.online_mode = inv.online_mode || '';
                    this.pay.online_ref = inv.online_ref || '';
                    this.pay.upi_id = inv.upi_id || '';
                    this.pay.card_last4 = inv.card_last4 || '';
                    this.pay.card_ref = inv.card_ref || '';
                    this.pay.cheque_no = inv.cheque_no || '';
                    this.pay.bank_name = inv.bank_name || '';

                    const received =
                        Number(inv.pay_cash || 0) +
                        Number(inv.pay_upi || 0) +
                        Number(inv.pay_card || 0) +
                        Number(inv.pay_cheque || 0);

                    this.payment.received = received;
                    this.payment.markFullyPaid = false;
                    this.payment.bank_account_id = inv.bank_account_id || '';

                    if (Number(inv.pay_cash || 0) > 0) this.payment.mode = 'cash';
                    else if (Number(inv.pay_upi || 0) > 0) this.payment.mode = 'upi';
                    else if (Number(inv.pay_card || 0) > 0) this.payment.mode = 'card';
                    else if (Number(inv.pay_cheque || 0) > 0) this.payment.mode = 'cheque';
                    else this.payment.mode = 'cash';

                    this.discount = {
                        type: 'flat',
                        value: Number(inv.discount_total || 0),
                    };

                    const oldCharges = Array.isArray(inv.charges_json) ? inv.charges_json : [];
                    this.charges = oldCharges.length
                        ? oldCharges.map(c => ({
                            _k: Date.now() + Math.random(),
                            name: c.name || '',
                            amount: Number(c.amount || 0),
                        }))
                        : [chargeTemplate()];

                    this.tcs.apply = Number(inv.tcs_percent || 0) > 0;
                    this.tcs.percent = Number(inv.tcs_percent || 0);
                    this.roundOff.enabled = Number(inv.round_off || 0) !== 0;

                    const oldItems = Array.isArray(inv.items) ? inv.items : [];
                    this.items = oldItems.length
                        ? oldItems.map(it => {
                            const type = this.normalizeItemType(it.item_type);

                            const row = {
                                _k: Date.now() + Math.random(),
                                item_id: it.item_id ?? null,
                                item_type: type,

                                search: this.getItemSearchLabel(it.item_id),
                                ddOpen: false,
                                ddHi: 0,
                                ddStyle: '',
                                ddPreviewName: '',
                                ddPreview: '',

                                description: it.description || '',
                                hsn: it.hsn || '',
                                quantity: Number(it.quantity || 1),

                                making_rate: Number(it.making_rate || 0),
                                gold_purity: it.gold_purity || null,
                                silver_purity: it.silver_purity || null,
                                gold_rate: Number(it.gold_rate || 0),
                                silver_rate: Number(it.silver_rate || 0),
                                silver_wt: Number(it.silver_wt || 0),
                                gold_wt: Number(it.gold_wt || 0),
                                gemstone_wt: Number(it.gemstone_wt || 0),
                                diamond_wt: Number(it.diamond_wt || 0),

                                service_rate: Number(it.service_rate || 0),
                                fixed_price: Number(it.fixed_price || it.price || 0),
                                tax_percent: Number(it.tax_percent || 0),

                                amount_mode: 'manual',
                                manual_amount: Number(it.manual_amount || 0),
                            };

                            if (type === 'service') {
                                this.resetRowForService(row);
                            } else {
                                this.resetRowForProduct(row);
                            }

                            return row;
                        })
                        : [rowTemplate()];

                    this.onReceivedInput();
                    this.calc();

                    this.pendingInvoicePreview = null;
                    this.pendingInvoiceData = null;
                },

                // ---------- METAL RATE ----------
                findMetalRate(type, purity) {
                    const t = lower(type).trim();
                    const pRaw = s(purity).trim();
                    if (!t) return 0;

                    const candidates = [];
                    if (pRaw) {
                        candidates.push(pRaw);
                        candidates.push(pRaw.split('(')[0].trim());
                        candidates.push(pRaw.split(' ')[0].trim());
                        const m = pRaw.match(/\(([^)]+)\)/);
                        if (m?.[1]) candidates.push(s(m[1]).trim());
                    }

                    const uniq = [...new Set(candidates.filter(Boolean))];

                    const rec = (this.metalRates || []).find(r => {
                        const rt = lower(r.metal_type).trim();
                        if (rt !== t) return false;

                        const rp = s(r.purity).trim();
                        if (!rp) return false;

                        const rpBase = rp.split('(')[0].trim();
                        const rpFirst = rp.split(' ')[0].trim();
                        const rpParen = (rp.match(/\(([^)]+)\)/) || [])[1]
                            ? s((rp.match(/\(([^)]+)\)/) || [])[1]).trim()
                            : '';

                        return uniq.includes(rp) || uniq.includes(rpBase) || uniq.includes(rpFirst) || (rpParen && uniq.includes(rpParen));
                    });

                    return rec ? n(rec.rate_per_gram ?? rec.rate ?? 0) : 0;
                },

                // ---------- ROW ACTIONS ----------
                add() {
                    this.items.push(rowTemplate());
                    this.calc();
                },

                remove(i) {
                    this.items.splice(i, 1);
                    if (!this.items.length) this.items.push(rowTemplate());
                    this.calc();
                },

                pickItem(i, id) {
                    const it = (this.itemsData || []).find(x => String(x.id) === String(id));
                    if (!it) return;

                    const r = this.items[i];
                    if (!r) return;

                    r.item_id = it.id;
                    r.item_type = this.inferItemType(it);

                    r.search = it.sku ? (it.name + ' (' + it.sku + ')') : it.name;
                    r.description = it.description || it.name || '';
                    r.tax_percent = n(it.tax_rate, 0);
                    r.fixed_price = n(it.price, 0); // ✅ price field priority

                    r.amount_mode = 'auto';
                    r.manual_amount = 0;

                    if (r.item_type === 'service') {
                        r.hsn = it.sac || '';
                        r.quantity = Math.max(1, n(r.quantity, 1));
                        r.service_rate = n(it.price, 0);

                        this.resetRowForService(r);

                        r.manual_amount = this.lineAmount(r);
                        this.calc();
                        return;
                    }

                    r.hsn = it.hsn || it.sac || '';
                    r.quantity = n(it.quantity, 1) || 1;

                    this.resetRowForProduct(r);

                    r.gold_wt = n(it.gold_weight ?? it.gold_wt, 0);
                    r.silver_wt = n(it.silver_weight ?? it.silver_wt, 0);

                    r.gold_purity = s(it.gold_purity ?? it.purity).trim() || null;
                    r.silver_purity = s(it.silver_purity).trim() || null;

                    r.gemstone_wt = n(it.stone_weight ?? it.gemstone_wt, 0);
                    r.diamond_wt = n(it.diamond_weight ?? it.diamond_wt, 0);

                    r.gemstone_charge = n(it.gemstone_charge ?? it.stone_charge ?? 0, 0);
                    r.diamond_charge = n(it.diamond_charge ?? 0, 0);

                    // r.making_charge_type = it.making_charge_type || 'percent';

                    r.making_charge_type = it.making_charge_type || 'percentage';
                    r.making_rate = n(it.making_charge ?? it.making_rate, 0);
                    r.gold_rate = this.findMetalRate('gold', r.gold_purity);
                    r.silver_rate = this.findMetalRate('silver', r.silver_purity || '999');

                    r.manual_amount = this.lineAmount(r);
                    this.calc();
                },


                onAmountEdit(r) {
                    const total = n(r.manual_amount, 0);

                    if (total <= 0) {
                        r.amount_mode = 'auto';
                        r.manual_amount = this.lineAmount(r);
                        this.calc();
                        return;
                    }

                    r.amount_mode = 'manual';

                    const qty = Math.max(1, n(r.quantity, 1));
                    const pct = n(r.tax_percent, 0);

                    // total amount me se tax reverse
                    let base = pct > 0 ? (total / (1 + (pct / 100))) : total;

                    // making charge bhi reverse
                    const makingPercent = n(r.making_rate, 0);
                    if (makingPercent > 0) {
                        base = base / (1 + (makingPercent / 100));
                    }

                    const perUnit = n((base / qty).toFixed(2), 0);

                    // dono update karo, item type check nahi
                    r.fixed_price = perUnit;
                    r.service_rate = perUnit;

                    this.calc();
                },

                onAutoChange(r) {
                    if (r.amount_mode !== 'manual') {
                        r.manual_amount = this.lineAmount(r);
                    } else {
                        this.onAmountEdit(r);
                        return;
                    }

                    this.calc();
                },

                // ---------- LINE CALCS ----------
                lineBase(r) {
                    const qty = Math.max(1, n(r.quantity, 1));
                    const pct = n(r.tax_percent, 0);

                    // ✅ Manual amount me user final amount enter karta hai.
                    // Is case me tax reverse-calculate hoga, making % auto apply nahi hoga.
                    if (r.amount_mode === 'manual') {
                        const total = n(r.manual_amount, 0);
                        const base = (pct > 0) ? (total / (1 + (pct / 100))) : total;
                        return Math.max(0, n(base.toFixed(2), 0));
                    }

                    // ✅ Service item: only service rate * qty, making charge nahi lagega.
                    if (r.item_type === 'service') {
                        const rate = n(r.service_rate, 0);
                        return Math.max(0, n((rate * qty).toFixed(2), 0));
                    }

                    // ✅ Product base:
                    // Agar item price set hai to price ko base maanenge.
                    // Agar price nahi hai to gold/silver weight * rate se base banega.
                    const goldAmt = n(r.gold_wt, 0) * n(r.gold_rate, 0);
                    const silvAmt = n(r.silver_wt, 0) * n(r.silver_rate, 0);
                    // const metalBase = goldAmt + silvAmt;


                    const gemstoneCharge = n(r.gemstone_charge, 0);
                    const diamondCharge = n(r.diamond_charge, 0);

                    const extraCharges = gemstoneCharge + diamondCharge;

                    const productBase =
                        n(r.fixed_price, 0) +
                        goldAmt +
                        silvAmt +
                        extraCharges;

                    // ✅ making_rate percentage hai.
                    // const makingType = r.making_charge_type || 'percent';
                    // const makingRate = n(r.making_rate, 0);

                    // let makingAmount = 0;

                    // if (makingType === 'fixed') {
                    //     makingAmount = makingRate;
                    // } else {
                    //     makingAmount = productBase * (makingRate / 100);
                    // }

                    const makingType = r.making_charge_type || 'percentage';
                    const makingRate = n(r.making_rate, 0);

                    let makingAmount = 0;

                    if (makingType === 'percentage') {
                        makingAmount = productBase * (makingRate / 100);
                    } else if (makingType === 'fixed') {
                        makingAmount = makingRate;
                    } else if (makingType === 'per_gram') {
                        const totalWeight = n(r.gold_wt, 0) + n(r.silver_wt, 0);
                        makingAmount = totalWeight * makingRate;
                    } else if (makingType === 'per_product') {
                        makingAmount = makingRate;
                    }

                    return Math.max(0, n(((productBase + makingAmount) * qty).toFixed(2), 0));
                },

                lineTax(r) {
                    const pct = n(r.tax_percent, 0);
                    const base = this.lineBase(r);
                    return n((base * (pct / 100)).toFixed(2), 0);
                },

                lineAmount(r) {
                    if (r.amount_mode === 'manual') {
                        return n(n(r.manual_amount, 0).toFixed(2), 0);
                    }
                    return n((this.lineBase(r) + this.lineTax(r)).toFixed(2), 0);
                },

                subtotal() {
                    return n((this.items || []).reduce((sum, r) => sum + this.lineBase(r), 0).toFixed(2), 0);
                },

                avgTaxPercentRaw() {
                    const baseSum = (this.items || []).reduce((sum, r) => sum + this.lineBase(r), 0);
                    if (baseSum <= 0) return 0;

                    const weighted = (this.items || []).reduce((sum, r) => sum + (this.lineBase(r) * n(r.tax_percent, 0)), 0);
                    return (weighted / baseSum);
                },

                avgTaxPercent() {
                    return n(this.avgTaxPercentRaw().toFixed(2), 0);
                },

                itemsTaxTotal() {
                    return n((this.items || []).reduce((sum, r) => sum + this.lineTax(r), 0).toFixed(2), 0);
                },

                chargesTotal() {
                    return n((this.charges || []).reduce((sum, c) => sum + n(c.amount, 0), 0).toFixed(2), 0);
                },

                chargesTaxTotal() {
                    const pct = this.avgTaxPercentRaw();
                    return n((this.chargesTotal() * (pct / 100)).toFixed(2), 0);
                },

                discountAmount() {
                    const base = this.subtotal();
                    const v = n(this.discount.value, 0);
                    if (this.discount.type === 'percent') {
                        return n((base * (v / 100)).toFixed(2), 0);
                    }
                    return n(v.toFixed(2), 0);
                },

                taxableAmount() {
                    const val = this.subtotal() + this.chargesTotal() - this.discountAmount();
                    return n(Math.max(0, val).toFixed(2), 0);
                },

                taxOnTaxable() {
                    return n((this.itemsTaxTotal() + this.chargesTaxTotal()).toFixed(2), 0);
                },

                cgst() {
                    return this.isIntra() ? n((this.taxOnTaxable() / 2).toFixed(2), 0) : 0;
                },

                sgst() {
                    return this.isIntra() ? n((this.taxOnTaxable() / 2).toFixed(2), 0) : 0;
                },

                igst() {
                    return this.isIntra() ? 0 : n(this.taxOnTaxable().toFixed(2), 0);
                },

                tcsAmount() {
                    if (!this.tcs.apply) return 0;
                    const pct = n(this.tcs.percent, 0);
                    if (pct <= 0) return 0;
                    const base = this.taxableAmount();
                    return n((base * (pct / 100)).toFixed(2), 0);
                },

                totalBeforeRound() {
                    return n((this.taxableAmount() + this.taxOnTaxable() + this.tcsAmount()).toFixed(2), 0);
                },

                roundOffAmount() {
                    if (!this.roundOff.enabled) return 0;
                    const raw = this.totalBeforeRound();
                    const rounded = Math.round(raw);
                    return n((rounded - raw).toFixed(2), 0);
                },

                totalPayable() {
                    return n((this.totalBeforeRound() + this.roundOffAmount()).toFixed(2), 0);
                },

                // ---------- PAYMENT ----------
                toggleFullyPaid() {
                    if (this.payment.markFullyPaid) {
                        this.payment.received = this.totalPayable();
                    }
                    this.onReceivedInput();
                },

                onReceivedInput() {
                    const amt = n(this.payment.received, 0);

                    this.pay.cash = 0;
                    this.pay.upi = 0;
                    this.pay.card = 0;
                    this.pay.cheque = 0;

                    if (this.payment.mode === 'cash') this.pay.cash = amt;
                    if (this.payment.mode === 'upi') this.pay.upi = amt;
                    if (this.payment.mode === 'card') this.pay.card = amt;
                    if (this.payment.mode === 'cheque') this.pay.cheque = amt;
                    if (this.payment.mode === 'bank') this.pay.upi = amt;

                    if (this.payment.mode === 'cash') {
                        this.payment.bank_account_id = '';
                    }

                    this.calc();
                },

                receivedTotal() {
                    const t = n(this.pay.cash) + n(this.pay.upi) + n(this.pay.card) + n(this.pay.cheque);
                    return n(t.toFixed(2), 0);
                },

                balanceAmount() {
                    const paid = this.receivedTotal() + n(this.pay.credit_excess) + n(this.pay.advance);
                    const bal = this.totalPayable() - paid;
                    return n(Math.max(0, bal).toFixed(2), 0);
                },

                // ---------- CHARGES ----------
                blankCharge() {
                    return chargeTemplate();
                },

                addCharge() {
                    this.charges.push(chargeTemplate());
                    this.calc();
                },

                removeCharge(i) {
                    this.charges.splice(i, 1);
                    this.calc();
                },

                chargesPayload() {
                    return (this.charges || [])
                        .filter(c => s(c.name).trim() || n(c.amount) > 0)
                        .map(c => ({
                            name: s(c.name).trim(),
                            amount: n(c.amount)
                        }));
                },

                // ---------- GST INPUT ----------
                normalizeGstin,
                validateGstinLocal,

                onGstinInput(scope) {
                    if (scope === 'hdr') {
                        const g = normalizeGstin(this.hdr.gst_no);
                        this.hdr.gst_no = g;

                        const res = validateGstinLocal(g);
                        if (res.empty) {
                            this.gstin = { status: 'idle', message: '', value: '' };
                            return;
                        }

                        this.gstin = {
                            status: res.ok ? 'valid' : 'invalid',
                            message: res.ok ? '✅ ' + res.message : '⚠️ ' + res.message,
                            value: g
                        };
                        return;
                    }

                    if (scope === 'client') {
                        const g = normalizeGstin(this.newClient.gstin);
                        this.newClient.gstin = g;

                        const res = validateGstinLocal(g);
                        if (res.empty) {
                            this.clientGstin = { status: 'idle', message: '', value: '' };
                            return;
                        }

                        this.clientGstin = {
                            status: res.ok ? 'valid' : 'invalid',
                            message: res.ok ? '✅ ' + res.message : '⚠️ ' + res.message,
                            value: g
                        };
                    }
                },

                // ---------- ITEM DROPDOWN ----------
                openItemDD(i) {
                    const r = this.items[i];
                    if (!r) return;
                    r.ddOpen = true;
                    r.ddHi = 0;
                    this.$nextTick(() => this.setItemDDPos(i));
                },

                closeItemDD(i, keepText = true) {
                    const r = this.items[i];
                    if (!r) return;
                    r.ddOpen = false;

                    if (keepText && r.item_id) {
                        const it = (this.itemsData || []).find(x => String(x.id) === String(r.item_id));
                        if (it) {
                            r.search = it.sku ? (it.name + ' (' + it.sku + ')') : it.name;
                        }
                    }
                },

                selectItemFromDD(i, it) {
                    const r = this.items[i];
                    if (!r || !it) return;

                    r.item_id = it.id;
                    this.pickItem(i, it.id);
                    r.ddOpen = false;

                    this.$nextTick(() => {
                        const el = document.getElementById('item_search_' + i);
                        if (el) el.focus({ preventScroll: true });
                    });
                },

                itemDDDown(i) {
                    const r = this.items[i];
                    if (!r) return;
                    const list = this.filteredItems(r.search);
                    if (!r.ddOpen) r.ddOpen = true;
                    if (!list.length) return;
                    r.ddHi = Math.min(list.length - 1, (r.ddHi || 0) + 1);
                },

                itemDDUp(i) {
                    const r = this.items[i];
                    if (!r) return;
                    const list = this.filteredItems(r.search);
                    if (!r.ddOpen) r.ddOpen = true;
                    if (!list.length) return;
                    r.ddHi = Math.max(0, (r.ddHi || 0) - 1);
                },

                itemDDEnter(i) {
                    const r = this.items[i];
                    if (!r) return;
                    const list = this.filteredItems(r.search);
                    if (!r.ddOpen || !list.length) return;
                    const it = list[r.ddHi || 0];
                    if (it) this.selectItemFromDD(i, it);
                },

                setItemDDPos(i) {
                    const r = this.items[i];
                    if (!r) return;

                    const input = document.getElementById('item_search_' + i);
                    if (!input) return;

                    const rect = input.getBoundingClientRect();
                    const top = rect.bottom + 4;
                    const left = rect.left;
                    const width = rect.width;
                    const maxH = clamp(window.innerHeight - top - 12, 160, 280);

                    r.ddStyle = `top:${top}px; left:${left}px; width:${width}px; max-height:${maxH}px;`;
                },

                // ---------- KOT ----------
                syncKotsFromInput() {
                    const raw = s(this.hdr.kot_input).trim();
                    if (!raw) {
                        this.hdr.kots = [];
                        return;
                    }

                    const parts = raw.split(/[, \n\t]+/).map(x => x.trim()).filter(Boolean);
                    const seen = new Set();
                    this.hdr.kots = parts.filter(x => !seen.has(x) && seen.add(x));
                    this.hdr.kot_input = this.hdr.kots.join(', ');
                },

                removeKot(idx) {
                    this.hdr.kots.splice(idx, 1);
                    this.hdr.kot_input = this.hdr.kots.join(', ');
                },

                // ---------- MODALS ----------
                openClientModal() {
                    this.newClientError = '';
                    this.newClient = {
                        name: '',
                        mobile: '',
                        address: '',
                        state: '',
                        state_code: '',
                        gstin: '',
                        pincode: '',
                        state_pick: ''
                    };
                    this.clientAutoSelect = true;
                    this.modals.client = true;
                },

                closeClientModal() {
                    this.modals.client = false;
                },

                applyClientState() {
                    const v = s(this.newClient.state_pick).trim();
                    if (!v) {
                        this.newClient.state = '';
                        this.newClient.state_code = '';
                        return;
                    }

                    const parts = v.split(',');
                    this.newClient.state_code = s(parts[0]).trim();
                    this.newClient.state = s(parts.slice(1).join(',')).trim();
                },

                openItemModal(rowIndex = null) {
                    this.activeRowIndex = rowIndex;
                    this.newItemError = '';
                    this.itemAutoSelect = true;

                    this.newItem = {
                        type: 'product',
                        name: '',
                        sku: '',
                        description: '',
                        category_id: '',
                        tax_rate: 0,
                        hsn: '',
                        sac: '',
                        price: 0,
                        making_charge: 0,
                        gold_weight: 0,
                        gold_purity: '',
                        silver_weight: 0,
                        silver_purity: '',
                        stone_weight: 0,
                        diamond_weight: 0
                    };

                    this.modals.item = true;
                },

                closeItemModal() {
                    this.modals.item = false;
                    this.activeRowIndex = null;
                },

                async saveClient() {
                    this.newClientError = '';

                    if (!s(this.newClient.name).trim()) {
                        this.newClientError = 'Name is required.';
                        return;
                    }

                    const mob = s(this.newClient.mobile).replace(/\D/g, '');
                    if (mob && mob.length < 10) {
                        this.newClientError = 'Mobile must be 10 digits (optional).';
                        return;
                    }

                    this.newClient.mobile = mob;

                    try {
                        this.savingClient = true;

                        const res = await fetch(@js(route('clients.quick-store')), {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                ...this.newClient,
                                is_save: this.clientAutoSelect ? 1 : 0
                            })
                        });

                        const data = await res.json().catch(() => ({}));

                        if (!res.ok) {
                            this.newClientError = data?.message || 'Failed to save client.';
                            return;
                        }

                        this.clients.unshift(data.client);

                        if (this.clientAutoSelect) {
                            this.clientId = data.client.id;
                            this.clientDD.q = data.client.mobile
                                ? (data.client.name + ' (' + data.client.mobile + ')')
                                : data.client.name;
                        }

                        this.modals.client = false;
                    } catch (e) {
                        this.newClientError = 'Network error.';
                    } finally {
                        this.savingClient = false;
                    }
                },

                async saveItem() {
                    this.newItemError = '';

                    if (!String(this.newItem.name || '').trim()) {
                        this.newItemError = 'Item name is required.';
                        return;
                    }

                    if (!this.newItem.category_id) {
                        this.newItemError = 'Please select category.';
                        return;
                    }

                    if (this.newItem.type === 'service' && Number(this.newItem.price || 0) <= 0) {
                        this.newItemError = 'Service price is required.';
                        return;
                    }

                    try {
                        this.savingItem = true;

                        const res = await fetch(@js(route('items.store.ajax')), {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                ...this.newItem,
                                is_save: this.itemAutoSelect ? 1 : 0
                            })
                        });

                        const data = await res.json().catch(() => ({}));

                        if (!res.ok) {
                            if (data?.errors?.category_id?.length) {
                                this.newItemError = data.errors.category_id[0];
                            } else {
                                this.newItemError = data?.message || 'Failed to save item.';
                            }
                            return;
                        }

                        data.item.type = data.item.type || this.newItem.type;
                        this.itemsData.unshift(data.item);

                        if (this.activeRowIndex !== null && this.items[this.activeRowIndex]) {
                            this.pickItem(this.activeRowIndex, data.item.id);
                        }

                        this.modals.item = false;
                    } catch (e) {
                        this.newItemError = 'Network error.';
                    } finally {
                        this.savingItem = false;
                    }
                },

                // ---------- FINAL SUBMIT ----------
                calc() {
                    return this.totalPayable();
                },

                beforeSubmit() {
                    const payload = (this.items || []).map(r => ({
                        item_id: r.item_id ?? null,
                        item_type: r.item_type ?? null,
                        description: r.description || '',
                        hsn: r.hsn || '',
                        quantity: Math.max(1, n(r.quantity, 1)),

                        // making_charge_type: r.making_charge_type || 'percent',

                        making_charge_type: r.making_charge_type || 'percentage',
                        making_rate: n(r.making_rate),
                        making_charge: n(r.making_rate),
                        gold_purity: r.gold_purity || null,
                        silver_purity: r.silver_purity || null,
                        gold_rate: n(r.gold_rate),
                        silver_rate: n(r.silver_rate),
                        silver_wt: n(r.silver_wt),
                        gold_wt: n(r.gold_wt),
                        gemstone_wt: n(r.gemstone_wt),
                        diamond_wt: n(r.diamond_wt),

                        gemstone_charge: n(r.gemstone_charge),
                        diamond_charge: n(r.diamond_charge),

                        service_rate: n(r.service_rate),
                        fixed_price: n(r.fixed_price),

                        discount: 0,
                        tax_percent: n(r.tax_percent),

                        amount_mode: r.amount_mode || 'auto',
                        manual_amount: n(r.manual_amount),

                        rate: this.lineBase(r),
                        tax_amount: this.lineTax(r),
                        amount: n(r.manual_amount) > 0 ? n(r.manual_amount) : this.lineAmount(r),
                    }));

                    document.getElementById('items_json').value = JSON.stringify(payload);

                    this.onReceivedInput();
                    this.$refs.form.submit();
                },

                submitForm() {
                    if (this.saving) return;

                    const g = normalizeGstin(this.hdr.gst_no);
                    const res = validateGstinLocal(g);

                    if (g && !res.ok) {
                        const ok = confirm("⚠️ GSTIN invalid lag raha hai.\n\n" + res.message + "\n\nPhir bhi Save karna hai?");
                        if (!ok) return;
                    }
                    this.saving = true;
                    this.$refs.form.requestSubmit();
                },
            };
        }
    </script>
</x-layouts.app>
