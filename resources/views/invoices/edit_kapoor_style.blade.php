<x-layouts.app :title="__('Edit Sales Invoice')">
    <div x-data="invoiceForm()" x-init="init()" class="space-y-4 max-w-7xl mx-auto px-3 sm:px-6 py-4" style="margin: -35px">
        <style>
            .invoice-table th,
            .invoice-table td {
                padding: 4px 6px !important;   /* 👈 yahin se space kam hota hai */
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
        <script type="application/json" id="banks-json">{!! $banksJson !!}</script>
        <script type="application/json" id="invoice-json">{!! $invoiceJson !!}</script>
        <script type="application/json" id="allowed-fields-json">
            {!! json_encode($allowedFields ?? []) !!}
        </script>

        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-neutral-100">Edit Sales Invoice</h1>
            <button type="button"
                    @click="submitForm()"
                    :disabled="saving"
                    class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed">
                <span x-show="!saving">Update</span>
                <span x-show="saving">Updating...</span>
            </button>

        </div>

        <form x-ref="form"
              method="POST"
              action="{{ route('invoices.update', $invoice) }}"
              enctype="multipart/form-data"
              @submit.prevent="beforeSubmit">
            @csrf
            @method('PUT')

            {{-- TOP PANELS --}}
            {{-- TOP PANELS - COMPACT --}}
            <div class="border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4">

                <div class="grid lg:grid-cols-12 gap-4">

                    {{-- LEFT: Bill To --}}
                    <div class="lg:col-span-8">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-lg font-semibold text-gray-800 dark:text-neutral-100">
                                Bill To
                            </div>
                        </div>

                        <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300 mb-1">
                            Party
                        </label>

                        <div class="flex items-center gap-2 w-full">
                            <div class="relative flex-1 min-w-0" @click.outside="closeClientDD()">

                                <input type="text"
                                    class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                                        bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm"
                                    placeholder="Search client by name / mobile..."
                                    x-model="clientSearch"
                                    @focus="openClientDD()"
                                    @input="openClientDD()"
                                    @keydown.arrow-down.prevent="clientDDDown()"
                                    @keydown.arrow-up.prevent="clientDDUp()"
                                    @keydown.enter.prevent="clientDDPick()">

                                <input type="hidden" name="client_id" :value="clientId">

                                <div x-show="clientDD.open"
                                    x-transition.opacity
                                    class="fixed mt-1 rounded border border-gray-200 dark:border-neutral-700
                                        bg-white dark:bg-neutral-900 shadow-2xl z-[999999]
                                        max-h-72 overflow-auto"
                                    :style="clientDD.style"
                                    style="display:none;"
                                    @mousedown.prevent.stop>

                                    <template x-if="filteredClients().length === 0">
                                        <div class="px-3 py-2 text-xs text-gray-500 dark:text-neutral-400">
                                            No results
                                        </div>
                                    </template>

                                    <template x-for="(c, idx) in filteredClients().slice(0,80)" :key="c.id">
                                        <div class="px-3 py-2 text-xs cursor-pointer flex items-center justify-between gap-3
                                            hover:bg-gray-100 dark:hover:bg-neutral-800"
                                            :class="idx === clientDD.hi ? 'bg-gray-100 dark:bg-neutral-800' : ''"
                                            @mouseenter="clientDD.hi = idx"
                                            @mousedown.prevent.stop="selectClientFromDD(c)">

                                            <div class="truncate"
                                                x-text="c.mobile ? (c.name + ' (' + c.mobile + ')') : c.name"></div>

                                            <div class="text-[11px] text-gray-500 dark:text-neutral-400 whitespace-nowrap"
                                                x-text="c.state_code ? ('GST ' + c.state_code) : ''"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <button type="button"
                                class="w-[90px] shrink-0 px-4 py-2 rounded-lg bg-[#4C8DFF] hover:bg-[#6CA8FF] text-white text-sm font-medium"
                                @click="openClientModal()">
                                + New
                            </button>
                        </div>

                        {{-- Compact Party Details --}}
                        <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                            <div>
                                <div class="text-gray-500 dark:text-neutral-400">Name</div>
                                <div class="font-semibold text-gray-800 dark:text-neutral-100 truncate"
                                    x-text="party.name || '-'"></div>
                            </div>

                            <div>
                                <div class="text-gray-500 dark:text-neutral-400">Phone</div>
                                <div class="font-semibold text-gray-800 dark:text-neutral-100 truncate"
                                    x-text="party.mobile || '-'"></div>
                            </div>

                            <div>
                                <div class="text-gray-500 dark:text-neutral-400">State</div>
                                <div class="font-semibold text-gray-800 dark:text-neutral-100 truncate"
                                    x-text="party.state || '-'"></div>
                            </div>

                            <div>
                                <div class="text-gray-500 dark:text-neutral-400">State Code</div>
                                <div class="font-semibold text-gray-800 dark:text-neutral-100 truncate"
                                    x-text="party.state_code || '-'"></div>
                            </div>

                            <div>
                                <div class="text-gray-500 dark:text-neutral-400">Pin</div>
                                <div class="font-semibold text-gray-800 dark:text-neutral-100 truncate"
                                    x-text="party.pincode || '-'"></div>
                            </div>

                            <div>
                                <div class="text-gray-500 dark:text-neutral-400">GSTIN</div>
                                <div class="font-semibold text-gray-800 dark:text-neutral-100 truncate"
                                    x-text="party.gstin || 'Unregistered'"></div>
                            </div>

                            <div class="md:col-span-2">
                                <div class="text-gray-500 dark:text-neutral-400">GST Type</div>
                                <div class="font-semibold truncate"
                                    :class="isIntra() ? 'text-green-600' : 'text-purple-600'"
                                    x-text="isIntra() ? 'Intra State (CGST+SGST)' : 'Inter State (IGST)'">
                                </div>
                            </div>

                            <div class="col-span-2 md:col-span-4">
                                <div class="text-gray-500 dark:text-neutral-400">Address</div>
                                <div class="font-semibold text-gray-800 dark:text-neutral-100 truncate"
                                    x-text="party.address || '-'"></div>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT: Invoice Details --}}
                    <div class="lg:col-span-4 lg:border-l lg:pl-4 border-gray-200 dark:border-neutral-700">
                        <div class="text-lg font-semibold text-gray-800 dark:text-neutral-100 mb-2">
                            Invoice Details
                        </div>

                        <div class="grid md:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">
                                    Date
                                </label>
                                <input type="date"
                                    name="invoice_date"
                                    x-model="hdr.date"
                                    required
                                    class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">
                                    Bill No.
                                </label>
                                <input :value="invoiceNo"
                                    readonly
                                    class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200 text-sm">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">
                                    Invoice Prefix
                                </label>
                                <input :value="computedPrefix"
                                    readonly
                                    class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200 text-sm">

                                <input type="hidden" name="invoice_prefix" :value="computedPrefix">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">
                                    Transport Mode
                                </label>
                                <input type="text"
                                    name="transport_mode"
                                    x-model="hdr.transport_mode"
                                    placeholder="By Hand"
                                    class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ================= TABLE ================= --}}
            {{-- ================= ITEMS ================= --}}
            <div class="border rounded border-gray-200 dark:border-neutral-700 bg-[#BFE0E0] dark:bg-neutral-900 my-4 overflow-visible">

                <div class="bg-[#BFE0E0] dark:bg-neutral-800 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-neutral-100">
                        Invoice Items
                    </h2>
                </div>

                <div class="p-3 space-y-3 bg-[#F3F4F6] dark:bg-[#1A1D23]">
                    <template x-for="(row, i) in items" :key="row._k">
                        <div class="rounded-xl border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-3">

                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs font-semibold text-gray-700 dark:text-neutral-200">
                                    Item #<span x-text="i + 1"></span>
                                </div>

                                <button type="button" @click="remove(i)"
                                    class="text-red-600 hover:text-red-700 text-xl leading-none">
                                    ×
                                </button>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-4">

                                {{-- LEFT: Description --}}
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-neutral-300 mb-1">
                                        Description
                                    </label>

                                    <div class="relative mb-2" @click.outside="closeItemDD(i)">
                                        <div class="flex items-center gap-1">
                                            <input type="text"
                                                :id="'item_search_' + i"
                                                class="flex-1 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-xs"
                                                placeholder="Search item..."
                                                x-model="row.search"
                                                @focus="openItemDD(i)"
                                                @input.debounce.50ms="openItemDD(i)"
                                                @keydown.escape.prevent="closeItemDD(i)"
                                                @keydown.arrow-down.prevent="itemDDDown(i)"
                                                @keydown.arrow-up.prevent="itemDDUp(i)"
                                                @keydown.enter.prevent="itemDDPick(i)">

                                            <button type="button"
                                                class="px-3 py-1 rounded border border-gray-300 dark:border-neutral-700 text-xs whitespace-nowrap bg-sky-600 text-white hover:bg-sky-700"
                                                @click="openItemModal(i)">
                                                + New
                                            </button>
                                        </div>

                                        <input type="hidden" :name="'item_id_'+i" :value="row.item_id">

                                        <div x-show="row.ddOpen"
                                            x-transition.opacity
                                            class="fixed mt-1 rounded border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-2xl z-[999999] overflow-hidden"
                                            :style="row.ddStyle + ';width:900px;max-width:95vw;'"
                                            style="display:none;"
                                            @mousedown.prevent.stop>

                                            <div class="grid grid-cols-12 h-72">
                                                <div class="col-span-7 overflow-auto border-r border-gray-200 dark:border-neutral-700">
                                                    <template x-if="filteredItems(row.search).length === 0">
                                                        <div class="px-3 py-2 text-xs text-gray-500 dark:text-neutral-400">
                                                            No results
                                                        </div>
                                                    </template>

                                                    <template x-for="(it, idx) in filteredItems(row.search).slice(0,80)" :key="it.id">
                                                        <div class="px-3 py-2 text-xs cursor-pointer flex items-start justify-between gap-3 hover:bg-gray-100 dark:hover:bg-neutral-800"
                                                            :class="idx === row.ddHi ? 'bg-gray-100 dark:bg-neutral-800' : ''"
                                                            @mouseenter="
                                                                row.ddHi = idx;
                                                                row.ddPreviewName = it.sku ? (it.name + ' (' + it.sku + ')') : it.name;
                                                                row.ddPreview = it.description || it.desc || it.long_description || '';
                                                            "
                                                            @mousedown.prevent.stop="selectItemFromDD(i, it)">

                                                            <div class="flex-1 pr-3 whitespace-normal break-words leading-4"
                                                                x-text="it.sku ? (it.name + ' (' + it.sku + ')') : it.name"></div>

                                                            <div class="text-[11px] text-gray-500 dark:text-neutral-400 whitespace-nowrap w-[110px] text-right"
                                                                x-text="it.price ? '₹ ' + Number(it.price).toFixed(2) : ''"></div>
                                                        </div>
                                                    </template>
                                                </div>

                                                <div class="col-span-5 p-3 overflow-auto">
                                                    <div class="text-[11px] text-gray-500 dark:text-neutral-400 mb-1">
                                                        Preview
                                                    </div>
                                                    <div class="text-xs font-semibold text-gray-900 dark:text-neutral-100 mb-2"
                                                        x-text="row.ddPreviewName || 'Hover on an item'"></div>
                                                    <div class="text-xs text-gray-700 dark:text-neutral-200 whitespace-pre-line"
                                                        x-text="row.ddPreview || 'No description available'"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <textarea x-model="row.description"
                                        required
                                        rows="6"
                                        class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-xs resize-y leading-tight min-h-[160px]"
                                        placeholder="Enter description..."></textarea>
                                </div>

                                {{-- RIGHT: Fields - 4 in one row --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 content-start">

                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-neutral-300 mb-1">HSN / SAC</label>
                                        <input x-model="row.hsn"
                                            class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-neutral-300 mb-1">Qty</label>
                                        <input type="number" min="1" step="1"
                                            x-model.number="row.quantity"
                                            @input="onAutoChange(row)"
                                            class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs text-center">
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-neutral-300 mb-1">Price</label>
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.fixed_price"
                                            @input="onAutoChange(row)"
                                            class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs text-right">
                                    </div>

                                    <div x-show="showItemField('making_charge')">
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-neutral-300 mb-1">Making Charge</label>
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.making_rate"
                                            @input="onAutoChange(row)"
                                            class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                    </div>

                                    <div x-show="showItemField('making_charge')">
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-neutral-300 mb-1">Making Type</label>
                                        <select x-model="row.making_charge_type"
                                            @change="onAutoChange(row)"
                                            class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                            <option value="percentage">Percent (%)</option>
                                            <option value="fixed">Fixed Amount (₹)</option>
                                            <option value="per_gram">Per Gram</option>
                                            <option value="per_product">Whole Product</option>
                                        </select>

                                        <div class="mt-0.5 text-[10px] text-blue-600"
                                            x-text="makingTypeLabel(row)">
                                        </div>
                                    </div>

                                    <div x-show="showItemField('gold_purity')">
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-neutral-300 mb-1">Gold Rate (₹/g)</label>
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.gold_rate"
                                            @input="onAutoChange(row)"
                                            class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                        <div class="mt-0.5 text-[10px] text-gray-500 dark:text-neutral-400"
                                            x-show="hasValue(row.gold_purity)"
                                            x-text="'Purity: ' + row.gold_purity"></div>
                                    </div>

                                    <div x-show="showItemField('gold_weight')">
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-neutral-300 mb-1">Gold Wt.(Gm)</label>
                                        <input type="number" step="0.001" min="0"
                                            x-model.number="row.gold_wt"
                                            @input="onAutoChange(row)"
                                            class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                    </div>

                                    <div x-show="showItemField('silver_purity')">
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-neutral-300 mb-1">Silver Rate (₹/g)</label>
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.silver_rate"
                                            @input="onAutoChange(row)"
                                            class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                        <div class="mt-0.5 text-[10px] text-gray-500 dark:text-neutral-400"
                                            x-show="hasValue(row.silver_purity)"
                                            x-text="'Purity: ' + row.silver_purity"></div>
                                    </div>

                                    <div x-show="showItemField('silver_weight')">
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-neutral-300 mb-1">Silver Wt.(Gm)</label>
                                        <input type="number" step="0.001" min="0"
                                            x-model.number="row.silver_wt"
                                            @input="onAutoChange(row)"
                                            class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                    </div>

                                    <div x-show="showItemField('stone_weight')">
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-neutral-300 mb-1">Gem Stone Wt.(Ct.)</label>
                                        <input type="number" step="0.001" min="0"
                                            x-model.number="row.gemstone_wt"
                                            @input="onAutoChange(row)"
                                            class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                    </div>

                                    <div x-show="showItemField('stone_charges')">
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-neutral-300 mb-1">Gemstone Charge</label>
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.gemstone_charge"
                                            @input="onAutoChange(row)"
                                            class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                    </div>

                                    <div x-show="showItemField('diamond_weight')">
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-neutral-300 mb-1">Diamond Wt.(Ct.)</label>
                                        <input type="number" step="0.001" min="0"
                                            x-model.number="row.diamond_wt"
                                            @input="onAutoChange(row)"
                                            class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                    </div>

                                    <div x-show="showItemField('diamond_charges')">
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-neutral-300 mb-1">Diamond Charge</label>
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.diamond_charge"
                                            @input="onAutoChange(row)"
                                            class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-neutral-300 mb-1">Tax %</label>
                                        <input type="number" step="0.01" min="0" max="100"
                                            x-model.number="row.tax_percent"
                                            @input="onAutoChange(row)"
                                            class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs">
                                    </div>

                                    <div class="col-span-1">
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-neutral-300 mb-1">Amount</label>
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.manual_amount"
                                            @input="onAmountEdit(row)"
                                            class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs text-right">

                                        <div class="mt-0.5 text-[10px]"
                                            :class="row.amount_mode === 'manual_user' ? 'text-orange-600' : 'text-gray-500 dark:text-neutral-400'"
                                            x-text="row.amount_mode === 'manual_user' ? 'Manual' : ('Auto: ₹ ' + lineAmount(row).toFixed(2))">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </template>

                    <div>
                        <button type="button"
                            @click="add()"
                            class="bg-green-500 px-4 py-2 rounded-lg text-white hover:bg-green-600 text-sm">
                            + Add Item
                        </button>
                    </div>
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
                                    <option :value="String(b.id)"
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

                    {{-- Signature upload (optional update) --}}
                    <div class="pt-2">
                        <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 mb-2">
                            Signature (Optional)
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

                    {{-- Hidden inputs (ONLY ONCE) --}}
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
                        class="mt-3 px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed">
                    <span x-show="!saving">Update</span>
                    <span x-show="saving">Updating...</span>
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
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Mobile</label>
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
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Pin</label>
                            <input x-model="newClient.pincode" class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700" placeholder="6 digit pin">
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Address</label>
                            <input x-model="newClient.address" class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700" placeholder="Full address">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">Client GSTIN (Optional)</label>

                            <input type="text"
                                   x-model="newClient.gstin"
                                   @input.debounce.250ms="onClientGstinInput()"
                                   placeholder="Eg: 09CYMPP9152J2ZK"
                                   class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 text-sm">

                            <div class="mt-1 text-xs"
                                 x-show="clientGstCheck.touched"
                                 :class="clientGstCheck.ok ? 'text-green-600' : 'text-red-600'"
                                 x-text="clientGstCheck.msg"
                                 style="display:none;"></div>
                        </div>

                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-neutral-200">
                            <input type="checkbox" class="rounded border-gray-300 dark:border-neutral-700"
                                   x-model="clientAutoSelect">
                            Save & Auto Select Client
                        </label>

                        <div class="text-xs text-gray-500 dark:text-neutral-400">
                            (Unchecked = sirf save, select nahi hoga)
                        </div>
                    </div>


                    <div class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-red-600" x-text="newClientError"></div>
                        <div class="flex gap-2">
                            <button type="button" class="px-4 py-2 rounded-xl border dark:border-neutral-700" @click="closeClientModal()">Cancel</button>
                            <button type="button"
                                    class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
                                    :disabled="savingClient"
                                    @click="saveClient()">
                                <span x-show="!savingClient">Save Client</span>
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

                    {{-- (modal body same as your code) --}}
                    {{-- ... keep unchanged ... --}}

                    <div class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-red-600" x-text="newItemError"></div>
                        <div class="flex gap-2">
                            <button type="button" class="px-4 py-2 rounded-xl border dark:border-neutral-700" @click="closeItemModal()">Cancel</button>
                            <button type="button"
                                    class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
                                    :disabled="savingItem"
                                    @click="saveItem()">
                                <span x-show="!savingItem">Save Item</span>
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

                <div class="relative w-full max-w-4xl bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">
                                Load Last Invoice?
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-neutral-400">
                                Is client ka last invoice mila hai. Kya aap uska data load karna chahte hain?
                            </p>
                        </div>

                        <button type="button"
                            class="text-2xl leading-none text-gray-500 hover:text-red-600"
                            @click="cancelApplyLastInvoice()">×</button>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4 text-sm">
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-neutral-800">
                            <div class="text-xs text-gray-500 dark:text-neutral-400">Invoice No.</div>
                            <div class="font-semibold text-gray-900 dark:text-neutral-100"
                                x-text="pendingInvoicePreview?.invoice_number || '-'"></div>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-neutral-800">
                            <div class="text-xs text-gray-500 dark:text-neutral-400">Invoice Date</div>
                            <div class="font-semibold text-gray-900 dark:text-neutral-100"
                                x-text="pendingInvoicePreview?.invoice_date || '-'"></div>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-neutral-800">
                            <div class="text-xs text-gray-500 dark:text-neutral-400">Items</div>
                            <div class="font-semibold text-gray-900 dark:text-neutral-100"
                                x-text="pendingInvoicePreview?.items_count || 0"></div>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-neutral-800">
                            <div class="text-xs text-gray-500 dark:text-neutral-400">Discount</div>
                            <div class="font-semibold text-gray-900 dark:text-neutral-100"
                                x-text="'₹ ' + Number(pendingInvoicePreview?.discount_total || 0).toFixed(2)"></div>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-end gap-2">
                        <button type="button"
                            class="px-4 py-2 rounded-xl border border-gray-300 dark:border-neutral-700"
                            @click="cancelApplyLastInvoice()">
                            No
                        </button>

                        <button type="button"
                            class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700"
                            @click="confirmApplyLastInvoice()">
                            Yes, Load
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>




<script>
    function invoiceForm() {
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
        const INVOICE = readJSON('invoice-json', {});
        const ALLOWED_FIELDS = readJSON('allowed-fields-json', []);

        const showAllowedField = (field) => {
            return ALLOWED_FIELDS.length === 0 || ALLOWED_FIELDS.includes(field);
        };
        const CATEGORIES = readJSON('categories-json', []);

        const BIZ_STATE_CODE = @js($businessStateCode ?? '');
        const BIZ_GSTIN = @js($businessGstin ?? '');
        const DEFAULT_TERMS = @js($defaultTerms ?? '');
        const TODAY = @js($today);
        const DOC_TYPE = @js($docType);

        const n = (v, d = 0) => {
            const x = Number(v);
            return Number.isFinite(x) ? x : d;
        };

        const s = (v) => (v ?? '').toString();
        const lower = (v) => s(v).toLowerCase();
        const money = (v) => '₹ ' + n(v).toFixed(2);

        const normalizeGstin = (v) => s(v).toUpperCase().replace(/[^0-9A-Z]/g, '').trim();

        const validateGstinLocal = (gstin) => {
            const g = normalizeGstin(gstin);
            if (!g) return { ok: true, empty: true, message: '' };

            if (g.length !== 15) {
                return { ok: false, message: 'GSTIN must be 15 characters.' };
            }

            const re = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/;
            if (!re.test(g)) {
                return { ok: false, message: 'GSTIN format invalid hai.' };
            }

            return { ok: true, message: 'GSTIN looks valid.' };
        };

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

            // ✅ IMPORTANT
            fixed_price: 0,

            price_priority: false,

            tax_percent: 0,

            amount_mode: 'auto',
            manual_amount: 0,
        });

        const chargeTemplate = () => ({
            _k: Date.now() + Math.random(),
            name: '',
            amount: 0,
        });

        return {
            clients: CLIENTS,


            hasValue(v) {
                if (v === null || v === undefined) return false;
                if (String(v).trim() === '') return false;
                return Number(v) > 0 || isNaN(Number(v));
            },

            showItemField(field) {
                return showAllowedField(field);
            },

            itemsData: ITEMS,
            metalRates: METAL_RATES,
            banks: BANKS,
            categories: CATEGORIES,

            saving: false,
            savingClient: false,
            savingItem: false,

            clientId: '',
            clientSearch: '',
            party: {
                name: '',
                address: '',
                state: '',
                state_code: '',
                mobile: '',
                gstin: '',
                pincode: '',
            },

            clientDD: {
                open: false,
                hi: 0,
                style: '',
            },

            hdr: {
                date: TODAY,
                transport_mode: 'By Hand',
                gst_no: BIZ_GSTIN,
                reverse_charge: false,
                terms: DEFAULT_TERMS,
            },

            basePrefix: @js($basePrefix ?? ''),
            computedPrefix: INVOICE.invoice_prefix || @js($suggestedPrefix ?? ''),
            invoiceNo: INVOICE.invoice_number || @js($invoice->invoice_number ?? ''),

            items: [],

            ui: {
                showCharges: false,
                showDiscount: false,
            },

            charges: [],

            discount: {
                type: 'flat',
                value: 0,
            },

            tcs: {
                apply: false,
                percent: 0,
            },

            roundOff: {
                enabled: false,
            },

            payment: {
                received: 0,
                mode: 'cash',
                markFullyPaid: false,
                bank_account_id: '',
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

            modals: {
                client: false,
                item: false,
            },

            newClientError: '',
            newItemError: '',
            activeRowIndex: null,
            clientAutoSelect: true,
            itemAutoSelect: true,

            newClient: {
                name: '',
                mobile: '',
                address: '',
                state: '',
                state_code: '',
                gstin: '',
                pincode: '',
                state_pick: '',
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
                diamond_weight: 0,
            },

            clientGstCheck: {
                touched: false,
                ok: true,
                msg: '',
            },

            confirmLoadModal: false,
            pendingInvoicePreview: null,
            pendingInvoiceData: null,

            money,

            makingTypeLabel(row) {
                const type = row.making_charge_type || 'percentage';

                if (type === 'percentage') return 'Percent (%)';
                if (type === 'fixed') return 'Fixed Amount';
                if (type === 'per_gram') return '₹ / Gram';
                if (type === 'per_product') return 'Whole Product';

                return 'Percent (%)';
            },

            csrf() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            },

            inferItemType(it) {
                const type = lower(it?.type).trim();
                return ['product', 'service'].includes(type) ? type : 'product';
            },

            findMasterItem(itemId) {
                return (this.itemsData || []).find(x => String(x.id) === String(itemId));
            },

            getItemSearchLabel(itemId) {
                const it = this.findMasterItem(itemId);
                if (!it) return '';
                return it.sku ? `${it.name} (${it.sku})` : (it.name || '');
            },

            syncParty() {
                const c = (this.clients || []).find(x => String(x.id) === String(this.clientId));

                if (!c) {
                    this.party = {
                        name: '',
                        address: '',
                        state: '',
                        state_code: '',
                        mobile: '',
                        gstin: '',
                        pincode: '',
                    };
                    return;
                }

                this.party = {
                    name: c.name || '',
                    address: c.address || '',
                    state: c.state || '',
                    state_code: c.state_code || '',
                    mobile: c.mobile || '',
                    gstin: c.gstin || '',
                    pincode: c.pincode || '',
                };

                this.clientSearch = c.mobile ? `${c.name} (${c.mobile})` : (c.name || '');
            },

            openClientDD() {
                this.clientDD.open = true;
                this.clientDD.hi = 0;
                this.$nextTick(() => this.setClientDDPos());
            },

            closeClientDD() {
                this.clientDD.open = false;
            },

            setClientDDPos() {
                const input = document.querySelector('input[x-model="clientSearch"]');
                if (!input) return;
                const r = input.getBoundingClientRect();
                this.clientDD.style = `top:${r.bottom + 4}px;left:${r.left}px;width:${r.width}px;`;
            },

            filteredClients() {
                const q = lower(this.clientSearch).trim();
                const list = this.clients || [];

                if (!q) return list;

                return list.filter(c =>
                    lower(c.name).includes(q) ||
                    lower(c.mobile).includes(q) ||
                    lower(c.gstin).includes(q) ||
                    lower(c.state_code).includes(q)
                );
            },

            selectClientFromDD(c) {
                this.clientId = String(c.id);
                this.clientSearch = c.mobile ? `${c.name} (${c.mobile})` : (c.name || '');
                this.closeClientDD();
                this.syncParty();
            },

            clientDDDown() {
                const list = this.filteredClients();
                if (!list.length) return;
                this.clientDD.hi = Math.min(list.length - 1, this.clientDD.hi + 1);
            },

            clientDDUp() {
                this.clientDD.hi = Math.max(0, this.clientDD.hi - 1);
            },

            clientDDPick() {
                const c = this.filteredClients()[this.clientDD.hi];
                if (c) this.selectClientFromDD(c);
            },

            filteredItems(q) {
                const query = lower(q).trim();
                const list = this.itemsData || [];

                if (!query) return list;

                return list.filter(it =>
                    lower(it.name).includes(query) ||
                    lower(it.sku).includes(query) ||
                    lower(it.description || it.desc || it.long_description).includes(query)
                );
            },

            openItemDD(i) {
                const row = this.items[i];
                if (!row) return;

                row.ddOpen = true;
                row.ddHi = 0;

                this.$nextTick(() => this.setItemDDPos(i));
            },

            closeItemDD(i) {
                const row = this.items[i];
                if (!row) return;

                row.ddOpen = false;
            },

            setItemDDPos(i) {
                const input = document.getElementById('item_search_' + i);
                if (!input) return;

                const r = input.getBoundingClientRect();
                this.items[i].ddStyle = `top:${r.bottom + 4}px;left:${r.left}px;`;
            },

            itemDDDown(i) {
                const row = this.items[i];
                if (!row) return;

                const len = this.filteredItems(row.search).length;
                if (!len) return;

                row.ddHi = Math.min(len - 1, row.ddHi + 1);
            },

            itemDDUp(i) {
                const row = this.items[i];
                if (!row) return;

                row.ddHi = Math.max(0, row.ddHi - 1);
            },

            itemDDPick(i) {
                const row = this.items[i];
                if (!row) return;

                const it = this.filteredItems(row.search)[row.ddHi];
                if (it) this.selectItemFromDD(i, it);
            },

            itemDDEnter(i) {
                this.itemDDPick(i);
            },

            selectItemFromDD(i, it) {
                const row = this.items[i];
                if (!row) return;

                row.search = it.sku ? `${it.name} (${it.sku})` : (it.name || '');
                row.ddOpen = false;

                this.pickItem(i, it.id);
            },

            findMetalRate(type, purity) {
                const t = lower(type);
                const p = s(purity).trim();

                const rec = (this.metalRates || []).find(r =>
                    lower(r.metal_type) === t &&
                    s(r.purity).trim() === p
                );

                return rec ? n(rec.rate_per_gram ?? rec.rate, 0) : 0;
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
                r.fixed_price = 0;

                r.gemstone_charge = 0;
                r.diamond_charge = 0;
            },

            resetRowForProduct(r) {
                r.service_rate = 0;
            },

            pickItem(i, id) {
                const it = this.findMasterItem(id);
                if (!it) return;

                const r = this.items[i];
                if (!r) return;

                r.item_id = String(it.id);
                r.item_type = this.inferItemType(it);

                r.search = it.sku ? `${it.name} (${it.sku})` : (it.name || '');
                r.description = it.description || it.name || '';
                r.tax_percent = n(it.tax_rate, 0);

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
                r.quantity = Math.max(1, n(r.quantity, 1));

                this.resetRowForProduct(r);

                // ✅ IMPORTANT: product price priority
                r.fixed_price = n(it.price, 0);

                r.gold_wt = n(it.gold_weight ?? it.gold_wt, 0);
                r.silver_wt = n(it.silver_weight ?? it.silver_wt, 0);

                r.gold_purity = s(it.gold_purity ?? it.purity).trim() || null;
                r.silver_purity = s(it.silver_purity).trim() || null;

                r.gemstone_wt = n(it.stone_weight ?? it.gemstone_wt, 0);
                r.diamond_wt = n(it.diamond_weight ?? it.diamond_wt, 0);

                r.gemstone_charge = n(it.gemstone_charge ?? it.stone_charge, 0);
                r.diamond_charge = n(it.diamond_charge, 0);

                r.making_rate = n(it.making_charge ?? it.making_rate, 0);
                // r.making_charge_type = it.making_charge_type || 'percent';

                r.making_charge_type = it.making_charge_type || 'percentage';
                r.gold_rate = this.findMetalRate('gold', r.gold_purity);
                r.silver_rate = this.findMetalRate('silver', r.silver_purity || '999');

                r.manual_amount = this.lineAmount(r);
                this.calc();
            },

            add() {
                this.items.push(rowTemplate());
                this.calc();
            },

            remove(i) {
                this.items.splice(i, 1);
                if (!this.items.length) this.items.push(rowTemplate());
                this.calc();
            },

            hasProduct() {
                return (this.items || []).some(r => r.item_type === 'product');
            },

            hasService() {
                return (this.items || []).some(r => r.item_type === 'service');
            },

            // onAutoChange(row) {
            //     if (!row) return;

            //     row.amount_mode = 'auto';
            //     row.manual_amount = this.lineAmount(row);

            //     this.calc();
            // },

            onAutoChange(row) {
                if (!row) return;

                const hasJewelleryValue =
                    n(row.gold_rate, 0) > 0 ||
                    n(row.gold_wt, 0) > 0 ||
                    n(row.silver_rate, 0) > 0 ||
                    n(row.silver_wt, 0) > 0 ||
                    n(row.gemstone_wt, 0) > 0 ||
                    n(row.gemstone_charge, 0) > 0 ||
                    n(row.diamond_wt, 0) > 0 ||
                    n(row.diamond_charge, 0) > 0 ||
                    n(row.making_rate, 0) > 0;

                if (hasJewelleryValue) {
                    row.item_type = 'product';
                }

                row.amount_mode = 'auto';
                row.manual_amount = this.lineAmount(row);

                this.calc();
            },


            

            onAmountEdit(row) {
                if (!row) return;

                const total = n(row.manual_amount, 0);
                // row.amount_mode = total > 0 ? 'manual' : 'auto';
                row.amount_mode = total > 0 ? 'manual_user' : 'auto';

                if (row.amount_mode === 'manual') {
                    const qty = Math.max(1, n(row.quantity, 1));
                    const pct = n(row.tax_percent, 0);

                    const baseAfterTax = pct > 0 ? total / (1 + pct / 100) : total;

                    const makingPercent = showAllowedField('making_charge')
                        ? n(row.making_rate, 0)
                        : 0;

                    const baseBeforeMaking = makingPercent > 0
                        ? baseAfterTax / (1 + makingPercent / 100)
                        : baseAfterTax;

                    row.fixed_price = n((baseBeforeMaking / qty).toFixed(2), 0);
                }

                this.calc();
            },

            // ✅ MAIN FIX: Making Rate ko percentage maana jayega.
            // Formula: productBase + (productBase * making_rate / 100), uske baad tax.
            lineBase(r) {
                const qty = Math.max(1, n(r.quantity, 1));
                const pct = n(r.tax_percent, 0);

                // if (r.amount_mode === 'manual' || r.amount_mode === 'manual_user') {
                //     const total = n(r.manual_amount ?? r.amount, 0);
                //     const base = pct > 0 ? total / (1 + pct / 100) : total;
                //     return Math.max(0, n(base.toFixed(2), 0));
                // }

                if (r.amount_mode === 'manual_user') {
                    const total = n(r.manual_amount ?? r.amount, 0);
                    const base = pct > 0 ? total / (1 + pct / 100) : total;
                    return Math.max(0, n(base.toFixed(2), 0));
                }

                const goldAmt = showAllowedField('gold_weight') || showAllowedField('gold_purity')
                    ? n(r.gold_wt, 0) * n(r.gold_rate, 0)
                    : 0;

                const silverAmt = showAllowedField('silver_weight') || showAllowedField('silver_purity')
                    ? n(r.silver_wt, 0) * n(r.silver_rate, 0)
                    : 0;

                const gemstoneCharge = showAllowedField('stone_charges')
                    ? n(r.gemstone_charge, 0)
                    : 0;

                const diamondCharge = showAllowedField('diamond_charges')
                    ? n(r.diamond_charge, 0)
                    : 0;

                const metalBase = goldAmt + silverAmt + gemstoneCharge + diamondCharge;

                // const basePrice = n(r.fixed_price, 0) > 0
                //     ? n(r.fixed_price, 0)
                //     : metalBase;

                const fixedPrice = n(r.fixed_price, 0);

                // ✅ Price field me value hai to price priority.
                // ✅ Price empty/0 hai to gold/silver/stone/diamond se amount banega.
                const basePrice = fixedPrice > 0
                    ? fixedPrice
                    : metalBase;

                let makingAmount = 0;

                // if (showAllowedField('making_charge')) {
                //     const makingType = r.making_charge_type || 'percent';
                //     const makingRate = n(r.making_rate, 0);

                //     if (makingType === 'fixed') {
                //         makingAmount = makingRate;
                //     } else {
                //         makingAmount = productBase * (makingRate / 100);
                //     }
                // }

                if (showAllowedField('making_charge')) {
                    const makingType = r.making_charge_type || 'percentage';
                    const makingRate = n(r.making_rate, 0);

                    if (makingType === 'percentage') {
                        makingAmount = basePrice * (makingRate / 100);
                    } else if (makingType === 'fixed') {
                        makingAmount = makingRate;
                    } else if (makingType === 'per_gram') {
                        const totalWeight = n(r.gold_wt, 0) + n(r.silver_wt, 0);
                        makingAmount = totalWeight * makingRate;
                    } else if (makingType === 'per_product') {
                        makingAmount = makingRate;
                    }
                }

                return Math.max(0, n(((basePrice + makingAmount) * qty).toFixed(2), 0));
            },

            lineTax(r) {
                const base = this.lineBase(r);
                const pct = n(r.tax_percent, 0);
                return n((base * (pct / 100)).toFixed(2), 0);
            },

            lineAmount(r) {
                if (r.amount_mode === 'manual' || r.amount_mode === 'manual_user') {
                    return n(n(r.manual_amount ?? r.amount, 0).toFixed(2), 0);
                }

                return n((this.lineBase(r) + this.lineTax(r)).toFixed(2), 0);
            },

            // subtotal() {
            //     return n((this.items || []).reduce((sum, r) => sum + this.lineBase(r), 0).toFixed(2), 0);
            // },


            subtotal() {
            return n((this.items || []).reduce((sum, r) => {
                if (r.amount_mode === 'manual' || r.amount_mode === 'manual_user') {
                    const pct = n(r.tax_percent, 0);
                    const total = n(r.manual_amount ?? r.amount, 0);

                    if (pct > 0) {
                        return sum + n((total / (1 + pct / 100)).toFixed(2), 0);
                    }

                    return sum + total;
                }

                return sum + this.lineBase(r);
            }, 0).toFixed(2), 0);
        },

            avgTaxPercentRaw() {
                const baseSum = (this.items || []).reduce((sum, r) => sum + this.lineBase(r), 0);
                if (baseSum <= 0) return 0;

                const weighted = (this.items || []).reduce((sum, r) => {
                    return sum + this.lineBase(r) * n(r.tax_percent, 0);
                }, 0);

                return weighted / baseSum;
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

            chargesPayload() {
                return (this.charges || [])
                    .filter(c => s(c.name).trim() || n(c.amount, 0) > 0)
                    .map(c => ({
                        name: s(c.name).trim(),
                        amount: n(c.amount, 0),
                    }));
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
                return n(Math.max(0, this.subtotal() - this.discountAmount() + this.chargesTotal()).toFixed(2), 0);
            },

            sgst() {
                if (!this.isIntra()) return 0;
                return n((this.itemsTaxTotal() / 2).toFixed(2), 0);
            },

            cgst() {
                if (!this.isIntra()) return 0;
                return n((this.itemsTaxTotal() / 2).toFixed(2), 0);
            },

            igst() {
                if (this.isIntra()) return 0;
                return n(this.itemsTaxTotal().toFixed(2), 0);
            },

            tcsAmount() {
                if (!this.tcs.apply) return 0;

                const pct = n(this.tcs.percent, 0);
                if (pct <= 0) return 0;

                return n((this.taxableAmount() * (pct / 100)).toFixed(2), 0);
            },

            totalBeforeRound() {
                return n((this.taxableAmount() + this.itemsTaxTotal() + this.tcsAmount()).toFixed(2), 0);
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

            balanceAmount() {
                const total = this.totalPayable();
                const received = n(this.payment.received, 0);
                const advance = n(this.pay.advance, 0);
                const credit = n(this.pay.credit_excess, 0);

                return n(Math.max(0, total - received - advance - credit).toFixed(2), 0);
            },

            isIntra() {
                const b = s(BIZ_STATE_CODE).replace(/\D+/g, '').replace(/^0+/, '');
                const p = s(this.party.state_code).replace(/\D+/g, '').replace(/^0+/, '');

                return b !== '' && p !== '' && b === p;
            },

            addCharge() {
                this.charges.push(chargeTemplate());
                this.calc();
            },

            removeCharge(i) {
                this.charges.splice(i, 1);
                if (!this.charges.length) this.charges.push(chargeTemplate());
                this.calc();
            },

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

                if (this.payment.mode === 'cash') {
                    this.pay.cash = amt;
                } else if (this.payment.mode === 'upi' || this.payment.mode === 'bank') {
                    this.pay.upi = amt;
                } else if (this.payment.mode === 'card') {
                    this.pay.card = amt;
                } else if (this.payment.mode === 'cheque') {
                    this.pay.cheque = amt;
                }

                this.calc();
            },

            calc() {
                return this.totalPayable();
            },

            init() {
                this.$watch('clientId', () => this.syncParty());

                const cid = INVOICE.client_id ?? INVOICE.client?.id ?? '';
                this.clientId = cid ? String(cid) : '';

                this.hdr.date = INVOICE.invoice_date || TODAY;
                this.hdr.transport_mode = INVOICE.transport_mode || 'By Hand';
                this.hdr.gst_no = INVOICE.gst_no || BIZ_GSTIN;
                this.hdr.reverse_charge = !!Number(INVOICE.reverse_charge || 0);
                this.hdr.terms = INVOICE.terms || DEFAULT_TERMS;

                this.computedPrefix = INVOICE.invoice_prefix || this.computedPrefix;
                this.invoiceNo = INVOICE.invoice_number || this.invoiceNo;

                this.discount = {
                    type: INVOICE.discount_type || 'flat',
                    value: n(INVOICE.discount_value ?? INVOICE.discount_total, 0),
                };

                let chargeData = INVOICE.charges_json || [];

                if (typeof chargeData === 'string') {
                    try {
                        chargeData = JSON.parse(chargeData || '[]');
                    } catch (e) {
                        chargeData = [];
                    }
                }

                this.charges = Array.isArray(chargeData) && chargeData.length
                    ? chargeData.map(c => ({
                        _k: Date.now() + Math.random(),
                        name: c.name || '',
                        amount: n(c.amount, 0),
                    }))
                    : [chargeTemplate()];

                this.tcs = {
                    apply: n(INVOICE.tcs_percent, 0) > 0,
                    percent: n(INVOICE.tcs_percent, 0),
                };

                this.roundOff = {
                    enabled: n(INVOICE.round_off, 0) !== 0,
                };

                this.payment.mode = INVOICE.payment_method || 'cash';
                this.payment.bank_account_id = INVOICE.bank_account_id ? String(INVOICE.bank_account_id) : '';
                this.payment.received = n(INVOICE.received ?? INVOICE.received_amount, 0);

                this.pay.cash = n(INVOICE.pay_cash, 0);
                this.pay.upi = n(INVOICE.pay_upi, 0);
                this.pay.card = n(INVOICE.pay_card, 0);
                this.pay.cheque = n(INVOICE.pay_cheque, 0);
                this.pay.credit_excess = n(INVOICE.credit_sales_excess, 0);
                this.pay.advance = n(INVOICE.advance_amount, 0);

                this.pay.online_mode = INVOICE.online_mode || '';
                this.pay.online_ref = INVOICE.online_ref || '';
                this.pay.upi_id = INVOICE.upi_id || '';
                this.pay.card_last4 = INVOICE.card_last4 || '';
                this.pay.card_ref = INVOICE.card_ref || '';
                this.pay.cheque_no = INVOICE.cheque_no || '';
                this.pay.bank_name = INVOICE.bank_name || '';

                const invoiceRows = Array.isArray(INVOICE.items) ? INVOICE.items : [];

                this.items = invoiceRows.length ? invoiceRows.map(old => {
                    const r = rowTemplate();
                    const master = this.findMasterItem(old.item_id);

                    r.item_id = old.item_id ? String(old.item_id) : null;

                    r.item_type = lower(
                        master?.type ||
                        old.item_type ||
                        'product'
                    ).trim();

                    if (!['product', 'service'].includes(r.item_type)) {
                        r.item_type = 'product';
                    }

                    r.search = master
                        ? (master.sku ? `${master.name} (${master.sku})` : master.name)
                        : (old.search || '');

                    r.description = old.description || master?.description || master?.name || '';
                    r.hsn = old.hsn || old.hsn_code || old.sac_code || master?.hsn || master?.sac || '';
                    r.quantity = Math.max(1, n(old.quantity, old.qty ?? 1));

                    r.tax_percent = n(old.tax_percent, master?.tax_rate ?? 0);

                    r.making_rate = n(old.making_rate ?? old.making_charge, 0);
                    r.making_charge_type = old.making_charge_type || master?.making_charge_type || 'percent';

                    r.gold_purity = old.gold_purity || master?.gold_purity || null;
                    r.silver_purity = old.silver_purity || master?.silver_purity || null;

                    r.gold_rate = n(old.gold_rate, 0);
                    r.silver_rate = n(old.silver_rate, 0);

                    r.silver_wt = n(old.silver_wt, 0);
                    r.gold_wt = n(old.gold_wt, 0);

                    r.gemstone_wt = n(old.gemstone_wt ?? old.gemstone_wt_ct, 0);
                    r.diamond_wt = n(old.diamond_wt ?? old.diamond_wt_ct, 0);

                    r.gemstone_charge = n(old.gemstone_charge ?? old.stone_charge, 0);
                    r.diamond_charge = n(old.diamond_charge, 0);

                    // ✅ EDIT PAGE IMPORTANT FIX:
                    // Jo amount invoice create time save hua tha, wahi edit page par lock rahega.
                    // Isse making charge / price dobara add nahi hoga.
                    if (r.item_type === 'product') {
                        r.fixed_price = n(old.fixed_price, 0);

                        if (r.fixed_price <= 0 && n(old.rate, 0) > 0) {
                            r.fixed_price = n((n(old.rate, 0) / r.quantity).toFixed(2), 0);
                        }

                        if (r.fixed_price <= 0 && master) {
                            r.fixed_price = n(master.price, 0);
                        }

                        r.service_rate = 0;
                    } else {
                        r.fixed_price = 0;

                        r.service_rate = n(old.service_rate, 0);

                        if (r.service_rate <= 0 && n(old.rate, 0) > 0) {
                            r.service_rate = n((n(old.rate, 0) / r.quantity).toFixed(2), 0);
                        }

                        if (r.service_rate <= 0 && master) {
                            r.service_rate = n(master.price, 0);
                        }
                    }

                    const savedAmount = n(old.manual_amount ?? old.amount, 0);
                    r.amount_mode = 'manual_user';
                    r.manual_amount = savedAmount;
                    r.amount = savedAmount;

                    return r;
                }) : [rowTemplate()];

                this.syncParty();
                this.onReceivedInput();
                this.calc();
            },

            beforeSubmit() {
                const payload = (this.items || []).map(r => ({
                    item_id: r.item_id ?? null,
                    item_type: r.item_type ?? null,
                    description: r.description || '',
                    hsn: r.hsn || '',
                    quantity: Math.max(1, n(r.quantity, 1)),

                    making_charge_type: r.making_charge_type || 'percent',
                    making_rate: n(r.making_rate),
                    making_charge: n(r.making_rate),
                    // making_charge_type: r.making_charge_type || 'percent',

                    making_charge_type: r.making_charge_type || 'percentage',

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
                    amount: this.lineAmount(r),
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
                    const ok = confirm("⚠️ GSTIN invalid lag raha hai.\n\n" + res.message + "\n\nPhir bhi Update karna hai?");
                    if (!ok) return;
                }

                this.saving = true;
                this.$refs.form.requestSubmit();
            },

            blankRow() {
                return rowTemplate();
            },

            openClientModal() {
                this.modals.client = true;
            },

            closeClientModal() {
                this.modals.client = false;
            },

            openItemModal(i) {
                this.activeRowIndex = i;
                this.modals.item = true;
            },

            closeItemModal() {
                this.modals.item = false;
            },

            applyClientState() {
                const val = this.newClient.state_pick || '';
                const [code, name] = val.split(',');
                this.newClient.state_code = code || '';
                this.newClient.state = name || '';
            },

            onClientGstinInput() {
                const g = normalizeGstin(this.newClient.gstin);
                const res = validateGstinLocal(g);

                this.clientGstCheck.touched = !!g;
                this.clientGstCheck.ok = res.ok;
                this.clientGstCheck.msg = res.message;
            },

            cancelApplyLastInvoice() {
                this.confirmLoadModal = false;
                this.pendingInvoicePreview = null;
                this.pendingInvoiceData = null;
            },

            confirmApplyLastInvoice() {
                this.cancelApplyLastInvoice();
            },

            scrollItemDDIntoView() {},
        };
    }
</script>


</x-layouts.app>
