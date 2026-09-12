@php
    $isEdit = $purchase->exists;

    $oldItems = old(
        'items',
        $isEdit
            ? $purchase->items->map(function ($purchaseItem) {
                return [
                    'item_id' => $purchaseItem->item_id,
                    'qty' => $purchaseItem->qty,
                    'qty_unit' => $purchaseItem->qty_unit,
                    'rate' => $purchaseItem->rate,
                    'amount' => $purchaseItem->amount,
                    'gst_rate' => $purchaseItem->gst_rate,
                    'cgst_amount' => $purchaseItem->cgst_amount,
                    'sgst_amount' => $purchaseItem->sgst_amount,
                    'igst_amount' => $purchaseItem->igst_amount,
                    'total_amount' => $purchaseItem->total_amount,
                ];
            })->toArray()
            : [
                [
                    'item_id' => '',
                    'qty' => 1,
                    'qty_unit' => '',
                    'rate' => 0,
                    'amount' => 0,
                    'gst_rate' => 0,
                    'cgst_amount' => 0,
                    'sgst_amount' => 0,
                    'igst_amount' => 0,
                    'total_amount' => 0,
                ],
            ]
    );

    $inputClass = '
        w-full rounded-xl border border-slate-300
        bg-slate-50 px-3 py-2.5
        text-sm text-slate-900
        outline-none transition
        focus:border-teal-500 focus:bg-white
        focus:ring-4 focus:ring-teal-100
        dark:border-slate-600
        dark:bg-slate-800
        dark:text-white
        dark:focus:border-teal-400
        dark:focus:ring-teal-900/40
    ';
@endphp


<div class="space-y-5">

    {{-- ========================================================= --}}
    {{-- PURCHASE DETAILS --}}
    {{-- ========================================================= --}}
    <section
        class="rounded-2xl border border-slate-200
               bg-white p-5 shadow-sm
               dark:border-slate-700 dark:bg-[#1b2128]"
    >

        <div
            class="mb-5 flex items-center justify-between
                   border-b border-slate-100 pb-4
                   dark:border-slate-700"
        >

            <div>
                <h2
                    class="font-bold text-slate-900
                           dark:text-white"
                >
                    Purchase Details
                </h2>

                <p
                    class="mt-1 text-xs text-slate-500
                           dark:text-slate-400"
                >
                    Supplier, invoice and tax information
                </p>
            </div>

        </div>


        <div
            class="grid grid-cols-1 gap-4
                   md:grid-cols-2 xl:grid-cols-4"
        >

            {{-- Supplier --}}
            <div class="xl:col-span-2">

                <div
                    class="mb-1.5 flex items-center
                           justify-between gap-3"
                >

                    <label
                        for="supplier_id"
                        class="text-sm font-semibold
                               text-slate-700
                               dark:text-slate-200"
                    >
                        Supplier
                    </label>

                    <button
                        type="button"
                        id="open-supplier-modal-btn"
                        class="rounded-lg bg-blue-50
                               px-2.5 py-1 text-xs
                               font-semibold text-blue-700
                               hover:bg-blue-100
                               dark:bg-blue-500/10
                               dark:text-blue-400"
                    >
                        + Add Supplier
                    </button>

                </div>


                <select
                    name="supplier_id"
                    id="supplier_id"
                    class="{{ $inputClass }}"
                >

                    <option value="">
                        — Select Supplier —
                    </option>

                    @foreach($suppliers as $supplier)

                        <option
                            value="{{ $supplier->id }}"
                            @selected(
                                (string) old(
                                    'supplier_id',
                                    $purchase->supplier_id ?? ''
                                ) === (string) $supplier->id
                            )
                        >
                            {{ $supplier->name }}

                            @if($supplier->mobile)
                                - {{ $supplier->mobile }}
                            @endif
                        </option>

                    @endforeach

                </select>

                @error('supplier_id')
                    <p class="mt-1 text-xs text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Invoice --}}
            <div>

                <label
                    class="mb-1.5 block text-sm
                           font-semibold text-slate-700
                           dark:text-slate-200"
                >
                    Invoice Number
                </label>

                <input
                    type="text"
                    name="invoice_no"
                    value="{{ old(
                        'invoice_no',
                        $purchase->invoice_no ?? ''
                    ) }}"
                    placeholder="Supplier invoice no."
                    class="{{ $inputClass }}"
                >

                @error('invoice_no')
                    <p class="mt-1 text-xs text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Date --}}
            <div>

                <label
                    class="mb-1.5 block text-sm
                           font-semibold text-slate-700
                           dark:text-slate-200"
                >
                    Purchase Date
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="date"
                    name="invoice_date"
                    required
                    value="{{ old(
                        'invoice_date',
                        $purchase->invoice_date
                            ? \Carbon\Carbon::parse($purchase->invoice_date)->format('Y-m-d')
                            : now()->format('Y-m-d')
                    ) }}"
                    class="{{ $inputClass }}"
                >

                @error('invoice_date')
                    <p class="mt-1 text-xs text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Tax Type --}}
            <div>

                <label
                    class="mb-1.5 block text-sm
                           font-semibold text-slate-700
                           dark:text-slate-200"
                >
                    Tax Type
                </label>

                <select
                    name="tax_type"
                    id="purchase-tax-type"
                    class="{{ $inputClass }}"
                >

                    <option
                        value="intra_state"
                        @selected(
                            old(
                                'tax_type',
                                $purchase->tax_type ?? 'intra_state'
                            ) === 'intra_state'
                        )
                    >
                        Intra State - CGST + SGST
                    </option>

                    <option
                        value="inter_state"
                        @selected(
                            old(
                                'tax_type',
                                $purchase->tax_type ?? ''
                            ) === 'inter_state'
                        )
                    >
                        Inter State - IGST
                    </option>

                </select>

            </div>


            {{-- Bill File --}}
            <div class="md:col-span-2">

                <label
                    class="mb-1.5 block text-sm
                           font-semibold text-slate-700
                           dark:text-slate-200"
                >
                    Purchase Bill
                </label>

                <input
                    type="file"
                    name="bill_file"
                    id="purchase-bill-file"
                    accept=".jpg,.jpeg,.png,.pdf"
                    class="block w-full rounded-xl
                           border border-slate-300
                           bg-slate-50 px-3 py-2
                           text-sm text-slate-700
                           file:mr-3 file:rounded-lg
                           file:border-0
                           file:bg-slate-200
                           file:px-3 file:py-1.5
                           file:text-xs file:font-semibold
                           dark:border-slate-600
                           dark:bg-slate-800
                           dark:text-slate-200
                           dark:file:bg-slate-700
                           dark:file:text-white"
                >

                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        id="scan-purchase-bill-btn"
                        data-item-create-url="{{ \Illuminate\Support\Facades\Route::has('items.create') ? route('items.create') : '' }}"
                        class="inline-flex items-center justify-center gap-2
                               rounded-xl bg-violet-600 px-4 py-2.5
                               text-sm font-semibold text-white shadow-sm
                               transition hover:bg-violet-700
                               disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             fill="none" viewBox="0 0 24 24"
                             stroke-width="1.8" stroke="currentColor"
                             class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.456-2.456L14.25 6l1.035-.259a3.375 3.375 0 0 0 2.456-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423L16.5 15.75l.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                        </svg>
                        Scan Bill & Auto Fill
                    </button>

                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        JPG, PNG or PDF — details will be filled before saving.
                    </span>
                </div>

                <div
                    id="purchase-bill-scan-status"
                    class="mt-3 hidden rounded-xl border px-4 py-3 text-sm"
                ></div>

                <div
                    id="purchase-bill-suggestions"
                    class="mt-3 hidden rounded-xl border border-amber-200
                           bg-amber-50 p-4 text-sm text-amber-900
                           dark:border-amber-900/60 dark:bg-amber-950/30
                           dark:text-amber-200"
                ></div>

                @if(!empty($purchase->bill_file))

                    <a
                        href="{{ asset(
                            'storage/'.$purchase->bill_file
                        ) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-2 inline-flex text-xs
                               font-semibold text-blue-600
                               hover:underline
                               dark:text-blue-400"
                    >
                        View existing bill ↗
                    </a>

                @endif

                @error('bill_file')
                    <p class="mt-1 text-xs text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

        </div>

    </section>



    {{-- ========================================================= --}}
    {{-- ITEMS --}}
    {{-- ========================================================= --}}
    <section
        class="overflow-hidden rounded-2xl
               border border-slate-200 bg-white
               shadow-sm
               dark:border-slate-700
               dark:bg-[#1b2128]"
    >

        <div
            class="flex items-center justify-between
                   border-b border-slate-200
                   px-5 py-4
                   dark:border-slate-700"
        >

            <div>

                <h2
                    class="font-bold text-slate-900
                           dark:text-white"
                >
                    Purchase Items
                </h2>

                <p
                    class="mt-1 text-xs text-slate-500
                           dark:text-slate-400"
                >
                    Unit is loaded automatically from Unit Master
                </p>

            </div>


            <button
                type="button"
                id="purchase-add-row"
                class="inline-flex items-center gap-2
                       rounded-xl bg-teal-600
                       px-3.5 py-2
                       text-xs font-semibold text-white
                       hover:bg-teal-700"
            >
                + Add Item
            </button>

        </div>


        @if($units->isEmpty())

            <div
                class="border-b border-amber-200
                       bg-amber-50 px-5 py-3
                       text-sm text-amber-700
                       dark:border-amber-900
                       dark:bg-amber-950/30
                       dark:text-amber-300"
            >
                No unit is available in Unit Master.
                Please create units from Items/Unit Master first.
            </div>

        @endif


        <div class="overflow-x-auto">

            <table
                class="min-w-[1500px] w-full
                       text-sm"
            >

                <thead
                    class="bg-slate-50 text-xs
                           uppercase tracking-wide
                           text-slate-500
                           dark:bg-slate-800
                           dark:text-slate-400"
                >

                    <tr>

                        <th
                            class="w-[440px] min-w-[440px] px-4 py-3
                                text-left font-semibold"
                        >
                            Item
                        </th>

                        <th class="px-3 py-3 text-left">
                            Qty
                        </th>

                        <th
                            class="w-[145px] px-3 py-3
                                   text-left"
                        >
                            Unit
                        </th>

                        <th class="px-3 py-3 text-right">
                            Rate
                        </th>

                        <th class="px-3 py-3 text-right">
                            Taxable
                        </th>

                        <th class="px-3 py-3 text-right">
                            GST %
                        </th>

                        <th class="px-3 py-3 text-right">
                            CGST
                        </th>

                        <th class="px-3 py-3 text-right">
                            SGST
                        </th>

                        <th class="px-3 py-3 text-right">
                            IGST
                        </th>

                        <th class="px-3 py-3 text-right">
                            Total
                        </th>

                        <th class="px-3 py-3 text-center">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody
                    id="purchase-items-body"
                    class="divide-y divide-slate-100
                           dark:divide-slate-700"
                >

                    @foreach($oldItems as $i => $row)

                        @php
                            $currentUnit =
                                $row['qty_unit'] ?? '';
                        @endphp

                        <tr
                            class="purchase-item-row
                                   hover:bg-slate-50/70
                                   dark:hover:bg-slate-800/40"
                        >

                            {{-- Item --}}
                            <td class="px-4 py-3">

                                <select
                                    name="items[{{ $i }}][item_id]"
                                    class="purchase-item-select
                                        w-[420px] min-w-[420px]
                                        rounded-lg border
                                        border-slate-300
                                        bg-white px-3 py-2
                                        text-sm text-slate-900
                                        dark:border-slate-600
                                        dark:bg-slate-800
                                        dark:text-white"
                                    required
                                >

                                    <option value="">
                                        — Select Item —
                                    </option>

                                    @foreach($items as $item)

                                        <option
                                            value="{{ $item->id }}"

                                            data-unit="{{ $item->unit ?? '' }}"

                                            data-rate="{{ $item->cost_price
                                                ?? $item->price
                                                ?? 0 }}"

                                            data-gst="{{ $item->tax_rate
                                                ?? 0 }}"

                                            @selected(
                                                (string) (
                                                    $row['item_id']
                                                    ?? ''
                                                )
                                                ===
                                                (string) $item->id
                                            )
                                        >
                                            {{ $item->name }}

                                            @if($item->sku)
                                                ({{ $item->sku }})
                                            @endif
                                        </option>

                                    @endforeach

                                </select>

                            </td>


                            {{-- Qty --}}
                            <td class="px-3 py-3">

                                <input
                                    type="number"
                                    name="items[{{ $i }}][qty]"
                                    min="0.001"
                                    step="0.001"
                                    value="{{ $row['qty'] ?? 1 }}"
                                    class="purchase-qty-input
                                           w-24 rounded-lg
                                           border border-slate-300
                                           bg-white px-2.5 py-2
                                           text-right
                                           dark:border-slate-600
                                           dark:bg-slate-800"
                                    required
                                >

                            </td>


                            {{-- Dynamic Unit --}}
                            <td class="px-3 py-3">

                                <select
                                    name="items[{{ $i }}][qty_unit]"
                                    class="purchase-unit-select
                                           w-32 rounded-lg
                                           border border-slate-300
                                           bg-white px-2.5 py-2
                                           dark:border-slate-600
                                           dark:bg-slate-800"
                                    required
                                >

                                    <option value="">
                                        Select
                                    </option>

                                    {{-- Existing old unit support --}}
                                    @if(
                                        filled($currentUnit)
                                        &&
                                        !$units
                                            ->pluck('name')
                                            ->contains($currentUnit)
                                    )

                                        <option
                                            value="{{ $currentUnit }}"
                                            selected
                                        >
                                            {{ $currentUnit }}
                                        </option>

                                    @endif


                                    @foreach($units as $unit)

                                        <option
                                            value="{{ $unit->name }}"
                                            @selected(
                                                (string) $currentUnit
                                                ===
                                                (string) $unit->name
                                            )
                                        >
                                            {{ $unit->name }}
                                        </option>

                                    @endforeach

                                </select>

                            </td>


                            {{-- Rate --}}
                            <td class="px-3 py-3">

                                <input
                                    type="number"
                                    name="items[{{ $i }}][rate]"
                                    min="0"
                                    step="0.01"
                                    value="{{ $row['rate'] ?? 0 }}"
                                    class="purchase-rate-input
                                           w-28 rounded-lg
                                           border border-slate-300
                                           bg-white px-2.5 py-2
                                           text-right
                                           dark:border-slate-600
                                           dark:bg-slate-800"
                                    required
                                >

                            </td>


                            {{-- Taxable --}}
                            <td class="px-3 py-3">

                                <input
                                    type="number"
                                    tabindex="-1"
                                    readonly
                                    value="{{ $row['amount'] ?? 0 }}"
                                    class="purchase-amount-input
                                           w-28 rounded-lg
                                           border border-slate-200
                                           bg-slate-100 px-2.5 py-2
                                           text-right
                                           dark:border-slate-700
                                           dark:bg-slate-700"
                                >

                            </td>


                            {{-- GST --}}
                            <td class="px-3 py-3">

                                <input
                                    type="number"
                                    name="items[{{ $i }}][gst_rate]"
                                    min="0"
                                    step="0.01"
                                    value="{{ $row['gst_rate'] ?? 0 }}"
                                    class="purchase-gst-input
                                           w-20 rounded-lg
                                           border border-slate-300
                                           bg-white px-2.5 py-2
                                           text-right
                                           dark:border-slate-600
                                           dark:bg-slate-800"
                                >

                            </td>


                            {{-- CGST --}}
                            <td class="px-3 py-3">

                                <input
                                    type="text"
                                    tabindex="-1"
                                    readonly
                                    value="{{ number_format(
                                        (float) (
                                            $row['cgst_amount']
                                            ?? 0
                                        ),
                                        2,
                                        '.',
                                        ''
                                    ) }}"
                                    class="purchase-cgst-input
                                           w-24 rounded-lg
                                           border border-slate-200
                                           bg-slate-100 px-2.5 py-2
                                           text-right
                                           dark:border-slate-700
                                           dark:bg-slate-700"
                                >

                            </td>


                            {{-- SGST --}}
                            <td class="px-3 py-3">

                                <input
                                    type="text"
                                    tabindex="-1"
                                    readonly
                                    value="{{ number_format(
                                        (float) (
                                            $row['sgst_amount']
                                            ?? 0
                                        ),
                                        2,
                                        '.',
                                        ''
                                    ) }}"
                                    class="purchase-sgst-input
                                           w-24 rounded-lg
                                           border border-slate-200
                                           bg-slate-100 px-2.5 py-2
                                           text-right
                                           dark:border-slate-700
                                           dark:bg-slate-700"
                                >

                            </td>


                            {{-- IGST --}}
                            <td class="px-3 py-3">

                                <input
                                    type="text"
                                    tabindex="-1"
                                    readonly
                                    value="{{ number_format(
                                        (float) (
                                            $row['igst_amount']
                                            ?? 0
                                        ),
                                        2,
                                        '.',
                                        ''
                                    ) }}"
                                    class="purchase-igst-input
                                           w-24 rounded-lg
                                           border border-slate-200
                                           bg-slate-100 px-2.5 py-2
                                           text-right
                                           dark:border-slate-700
                                           dark:bg-slate-700"
                                >

                            </td>


                            {{-- Total --}}
                            <td class="px-3 py-3">

                                <input
                                    type="text"
                                    tabindex="-1"
                                    readonly
                                    value="{{ number_format(
                                        (float) (
                                            $row['total_amount']
                                            ?? 0
                                        ),
                                        2,
                                        '.',
                                        ''
                                    ) }}"
                                    class="purchase-line-total-input
                                           w-32 rounded-lg
                                           border border-slate-200
                                           bg-slate-100 px-2.5 py-2
                                           text-right font-semibold
                                           dark:border-slate-700
                                           dark:bg-slate-700"
                                >

                            </td>


                            <td class="px-3 py-3 text-center">

                                <button
                                    type="button"
                                    class="purchase-remove-row
                                           rounded-lg bg-red-50
                                           px-2.5 py-2
                                           text-xs font-semibold
                                           text-red-600
                                           hover:bg-red-100
                                           dark:bg-red-500/10
                                           dark:text-red-400"
                                >
                                    Remove
                                </button>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </section>



    {{-- ========================================================= --}}
    {{-- TOTALS --}}
    {{-- ========================================================= --}}
    <section
        class="rounded-2xl border border-slate-200
               bg-white p-5 shadow-sm
               dark:border-slate-700 dark:bg-[#1b2128]"
    >

        <div class="grid gap-5 lg:grid-cols-2">

            <div>

                <h2
                    class="font-bold text-slate-900
                           dark:text-white"
                >
                    Payment Adjustment
                </h2>

                <p
                    class="mt-1 text-xs text-slate-500
                           dark:text-slate-400"
                >
                    Apply discount, round-off and payment
                </p>


                <div
                    class="mt-5 grid grid-cols-1
                           gap-4 sm:grid-cols-3"
                >

                    <div>

                        <label
                            class="mb-1.5 block text-sm
                                   font-semibold text-slate-700
                                   dark:text-slate-200"
                        >
                            Discount
                        </label>

                        <input
                            id="purchase-discount"
                            type="number"
                            name="discount_amount"
                            min="0"
                            step="0.01"
                            value="{{ old(
                                'discount_amount',
                                $purchase->discount_amount ?? 0
                            ) }}"
                            class="{{ $inputClass }}"
                        >

                    </div>


                    <div>

                        <label
                            class="mb-1.5 block text-sm
                                   font-semibold text-slate-700
                                   dark:text-slate-200"
                        >
                            Round Off
                        </label>

                        <input
                            id="purchase-round-off"
                            type="number"
                            name="round_off"
                            step="0.01"
                            value="{{ old(
                                'round_off',
                                $purchase->round_off ?? 0
                            ) }}"
                            class="{{ $inputClass }}"
                        >

                    </div>


                    <div>

                        <label
                            class="mb-1.5 block text-sm
                                   font-semibold text-slate-700
                                   dark:text-slate-200"
                        >
                            Paid Amount
                        </label>

                        <input
                            id="purchase-paid"
                            type="number"
                            name="paid_amount"
                            min="0"
                            step="0.01"
                            value="{{ old(
                                'paid_amount',
                                $purchase->paid_amount ?? 0
                            ) }}"
                            class="{{ $inputClass }}"
                        >

                    </div>

                </div>

            </div>


            <div
                class="rounded-2xl bg-slate-50
                       p-5 dark:bg-slate-800/70"
            >

                <div class="space-y-3">

                    <div
                        class="flex items-center
                               justify-between"
                    >
                        <span
                            class="text-sm text-slate-500
                                   dark:text-slate-400"
                        >
                            Subtotal
                        </span>

                        <strong
                            class="text-slate-800
                                   dark:text-white"
                        >
                            ₹
                            <span id="summary-subtotal">
                                0.00
                            </span>
                        </strong>
                    </div>


                    <div
                        class="flex items-center
                               justify-between"
                    >
                        <span
                            class="text-sm text-slate-500
                                   dark:text-slate-400"
                        >
                            CGST
                        </span>

                        <strong>
                            ₹
                            <span id="summary-cgst">
                                0.00
                            </span>
                        </strong>
                    </div>


                    <div
                        class="flex items-center
                               justify-between"
                    >
                        <span
                            class="text-sm text-slate-500
                                   dark:text-slate-400"
                        >
                            SGST
                        </span>

                        <strong>
                            ₹
                            <span id="summary-sgst">
                                0.00
                            </span>
                        </strong>
                    </div>


                    <div
                        class="flex items-center
                               justify-between"
                    >
                        <span
                            class="text-sm text-slate-500
                                   dark:text-slate-400"
                        >
                            IGST
                        </span>

                        <strong>
                            ₹
                            <span id="summary-igst">
                                0.00
                            </span>
                        </strong>
                    </div>


                    <div
                        class="border-t border-slate-200
                               pt-3 dark:border-slate-700"
                    >

                        <div
                            class="flex items-center
                                   justify-between"
                        >

                            <span
                                class="font-semibold
                                       text-slate-700
                                       dark:text-slate-200"
                            >
                                Grand Total
                            </span>

                            <span
                                class="text-xl font-bold
                                       text-slate-900
                                       dark:text-white"
                            >
                                ₹
                                <span id="summary-grand-total">
                                    0.00
                                </span>
                            </span>

                        </div>

                    </div>


                    <div
                        class="flex items-center justify-between
                               rounded-xl bg-red-50
                               px-3 py-2
                               dark:bg-red-500/10"
                    >

                        <span
                            class="text-sm font-semibold
                                   text-red-600
                                   dark:text-red-400"
                        >
                            Due Amount
                        </span>

                        <span
                            class="font-bold text-red-600
                                   dark:text-red-400"
                        >
                            ₹
                            <span id="summary-due">
                                0.00
                            </span>
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </section>

</div>



{{-- ============================================================= --}}
{{-- SUPPLIER MODAL --}}
{{-- ============================================================= --}}
<div
    id="supplier-modal"
    class="fixed inset-0 z-[100]
           hidden items-center justify-center
           bg-black/60 p-4"
>

    <div
        class="w-full max-w-lg rounded-2xl
               bg-white shadow-2xl
               dark:bg-slate-900"
    >

        <div
            class="flex items-center justify-between
                   border-b border-slate-200
                   px-5 py-4
                   dark:border-slate-700"
        >

            <div>

                <h3
                    class="font-bold text-slate-900
                           dark:text-white"
                >
                    Add Supplier
                </h3>

                <p
                    class="mt-1 text-xs text-slate-500
                           dark:text-slate-400"
                >
                    Supplier will be selected automatically
                </p>

            </div>

            <button
                type="button"
                id="close-supplier-modal-btn"
                class="text-2xl text-slate-500
                       hover:text-slate-800"
            >
                ×
            </button>

        </div>


        <div class="space-y-4 p-5">

            <div
                id="supplier-error"
                class="hidden rounded-xl
                       border border-red-200
                       bg-red-50 p-3
                       text-sm text-red-700"
            ></div>


            <div>

                <label class="mb-1 block text-sm font-semibold">
                    Supplier Name *
                </label>

                <input
                    id="supplier-name"
                    type="text"
                    class="{{ $inputClass }}"
                >

            </div>


            <div>

                <label class="mb-1 block text-sm font-semibold">
                    Mobile
                </label>

                <input
                    id="supplier-mobile"
                    type="text"
                    class="{{ $inputClass }}"
                >

            </div>


            <div class="grid gap-4 sm:grid-cols-2">

                <div>

                    <label class="mb-1 block text-sm font-semibold">
                        Email
                    </label>

                    <input
                        id="supplier-email"
                        type="email"
                        class="{{ $inputClass }}"
                    >

                </div>


                <div>

                    <label class="mb-1 block text-sm font-semibold">
                        GSTIN
                    </label>

                    <input
                        id="supplier-gstin"
                        type="text"
                        class="{{ $inputClass }}"
                    >

                </div>

            </div>


            <div>

                <label class="mb-1 block text-sm font-semibold">
                    Address
                </label>

                <textarea
                    id="supplier-address"
                    rows="2"
                    class="{{ $inputClass }}"
                ></textarea>

            </div>

        </div>


        <div
            class="flex justify-end gap-2
                   border-t border-slate-200
                   px-5 py-4
                   dark:border-slate-700"
        >

            <button
                type="button"
                id="supplier-modal-cancel"
                class="rounded-xl border
                       border-slate-300
                       px-4 py-2
                       text-sm font-semibold"
            >
                Cancel
            </button>

            <button
                type="button"
                id="save-supplier-btn"
                class="rounded-xl bg-blue-600
                       px-4 py-2
                       text-sm font-semibold
                       text-white
                       hover:bg-blue-700"
            >
                Save Supplier
            </button>

        </div>

    </div>

</div>



{{-- ============================================================= --}}
{{-- ROW TEMPLATE --}}
{{-- ============================================================= --}}
<template id="purchase-row-template">

    <tr
        class="purchase-item-row
               hover:bg-slate-50/70
               dark:hover:bg-slate-800/40"
    >

        <td class="px-4 py-3">

            <select
                data-name="item_id"
                class="purchase-item-select
                       w-full min-w-[270px]
                       rounded-lg border
                       border-slate-300
                       bg-white px-2.5 py-2
                       dark:border-slate-600
                       dark:bg-slate-800"
                required
            >

                <option value="">
                    — Select Item —
                </option>

                @foreach($items as $item)

                    <option
                        value="{{ $item->id }}"
                        data-unit="{{ $item->unit ?? '' }}"
                        data-rate="{{ $item->cost_price
                            ?? $item->price
                            ?? 0 }}"
                        data-gst="{{ $item->tax_rate ?? 0 }}"
                    >
                        {{ $item->name }}

                        @if($item->sku)
                            ({{ $item->sku }})
                        @endif
                    </option>

                @endforeach

            </select>

        </td>


        <td class="px-3 py-3">

            <input
                data-name="qty"
                type="number"
                min="0.001"
                step="0.001"
                value="1"
                class="purchase-qty-input
                       w-24 rounded-lg border
                       border-slate-300 px-2.5 py-2
                       text-right
                       dark:border-slate-600
                       dark:bg-slate-800"
                required
            >

        </td>


        <td class="px-3 py-3">

            <select
                data-name="qty_unit"
                class="purchase-unit-select
                       w-32 rounded-lg
                       border border-slate-300
                       bg-white px-2.5 py-2
                       dark:border-slate-600
                       dark:bg-slate-800"
                required
            >

                <option value="">
                    Select
                </option>

                @foreach($units as $unit)
                    <option value="{{ $unit->name }}">
                        {{ $unit->name }}
                    </option>
                @endforeach

            </select>

        </td>


        <td class="px-3 py-3">

            <input
                data-name="rate"
                type="number"
                min="0"
                step="0.01"
                value="0"
                class="purchase-rate-input
                       w-28 rounded-lg border
                       border-slate-300 px-2.5 py-2
                       text-right
                       dark:border-slate-600
                       dark:bg-slate-800"
                required
            >

        </td>


        <td class="px-3 py-3">

            <input
                type="text"
                value="0.00"
                readonly
                tabindex="-1"
                class="purchase-amount-input
                       w-28 rounded-lg
                       border border-slate-200
                       bg-slate-100 px-2.5 py-2
                       text-right
                       dark:border-slate-700
                       dark:bg-slate-700"
            >

        </td>


        <td class="px-3 py-3">

            <input
                data-name="gst_rate"
                type="number"
                min="0"
                step="0.01"
                value="0"
                class="purchase-gst-input
                       w-20 rounded-lg
                       border border-slate-300
                       px-2.5 py-2 text-right
                       dark:border-slate-600
                       dark:bg-slate-800"
            >

        </td>


        <td class="px-3 py-3">

            <input
                type="text"
                value="0.00"
                readonly
                tabindex="-1"
                class="purchase-cgst-input
                       w-24 rounded-lg
                       border border-slate-200
                       bg-slate-100 px-2.5 py-2
                       text-right
                       dark:border-slate-700
                       dark:bg-slate-700"
            >

        </td>


        <td class="px-3 py-3">

            <input
                type="text"
                value="0.00"
                readonly
                tabindex="-1"
                class="purchase-sgst-input
                       w-24 rounded-lg
                       border border-slate-200
                       bg-slate-100 px-2.5 py-2
                       text-right
                       dark:border-slate-700
                       dark:bg-slate-700"
            >

        </td>


        <td class="px-3 py-3">

            <input
                type="text"
                value="0.00"
                readonly
                tabindex="-1"
                class="purchase-igst-input
                       w-24 rounded-lg
                       border border-slate-200
                       bg-slate-100 px-2.5 py-2
                       text-right
                       dark:border-slate-700
                       dark:bg-slate-700"
            >

        </td>


        <td class="px-3 py-3">

            <input
                type="text"
                value="0.00"
                readonly
                tabindex="-1"
                class="purchase-line-total-input
                       w-32 rounded-lg
                       border border-slate-200
                       bg-slate-100 px-2.5 py-2
                       text-right font-semibold
                       dark:border-slate-700
                       dark:bg-slate-700"
            >

        </td>


        <td class="px-3 py-3 text-center">

            <button
                type="button"
                class="purchase-remove-row
                       rounded-lg bg-red-50
                       px-2.5 py-2
                       text-xs font-semibold
                       text-red-600
                       hover:bg-red-100
                       dark:bg-red-500/10
                       dark:text-red-400"
            >
                Remove
            </button>

        </td>

    </tr>

</template>



<script>
document.addEventListener('DOMContentLoaded', function () {

    const body =
        document.getElementById('purchase-items-body');

    const addRowButton =
        document.getElementById('purchase-add-row');

    const rowTemplate =
        document.getElementById('purchase-row-template');

    const taxType =
        document.getElementById('purchase-tax-type');

    const billFileInput =
        document.getElementById('purchase-bill-file');

    const scanBillButton =
        document.getElementById('scan-purchase-bill-btn');

    const scanStatus =
        document.getElementById('purchase-bill-scan-status');

    const scanSuggestions =
        document.getElementById('purchase-bill-suggestions');


    let nextIndex =
        {{ count($oldItems) }};


    /*
    |--------------------------------------------------------------------------
    | Re-index new row
    |--------------------------------------------------------------------------
    */
    function prepareNewRow(row, index)
    {
        row.querySelectorAll('[data-name]')
            .forEach(function (field) {

                const fieldName =
                    field.dataset.name;

                field.name =
                    `items[${index}][${fieldName}]`;

            });
    }


    /*
    |--------------------------------------------------------------------------
    | Number
    |--------------------------------------------------------------------------
    */
    function toNumber(value)
    {
        const number =
            parseFloat(value);

        return Number.isFinite(number)
            ? number
            : 0;
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Row
    |--------------------------------------------------------------------------
    */
    function calculateRow(row)
    {
        const qty =
            toNumber(
                row.querySelector(
                    '.purchase-qty-input'
                )?.value
            );

        const rate =
            toNumber(
                row.querySelector(
                    '.purchase-rate-input'
                )?.value
            );

        const gstRate =
            toNumber(
                row.querySelector(
                    '.purchase-gst-input'
                )?.value
            );


        const taxable =
            qty * rate;


        let cgst = 0;
        let sgst = 0;
        let igst = 0;


        if (
            taxType?.value ===
            'inter_state'
        ) {

            igst =
                taxable *
                gstRate /
                100;

        } else {

            cgst =
                taxable *
                (gstRate / 2) /
                100;

            sgst =
                taxable *
                (gstRate / 2) /
                100;
        }


        const total =
            taxable +
            cgst +
            sgst +
            igst;


        row.querySelector(
            '.purchase-amount-input'
        ).value =
            taxable.toFixed(2);


        row.querySelector(
            '.purchase-cgst-input'
        ).value =
            cgst.toFixed(2);


        row.querySelector(
            '.purchase-sgst-input'
        ).value =
            sgst.toFixed(2);


        row.querySelector(
            '.purchase-igst-input'
        ).value =
            igst.toFixed(2);


        row.querySelector(
            '.purchase-line-total-input'
        ).value =
            total.toFixed(2);


        return {
            taxable,
            cgst,
            sgst,
            igst,
            total,
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Purchase
    |--------------------------------------------------------------------------
    */
    function calculatePurchase()
    {
        let subtotal = 0;
        let cgstTotal = 0;
        let sgstTotal = 0;
        let igstTotal = 0;


        body
            ?.querySelectorAll(
                '.purchase-item-row'
            )
            .forEach(function (row) {

                const values =
                    calculateRow(row);

                subtotal +=
                    values.taxable;

                cgstTotal +=
                    values.cgst;

                sgstTotal +=
                    values.sgst;

                igstTotal +=
                    values.igst;

            });


        const discount =
            toNumber(
                document.getElementById(
                    'purchase-discount'
                )?.value
            );

        const roundOff =
            toNumber(
                document.getElementById(
                    'purchase-round-off'
                )?.value
            );

        const paid =
            toNumber(
                document.getElementById(
                    'purchase-paid'
                )?.value
            );


        const grandTotal =
            subtotal +
            cgstTotal +
            sgstTotal +
            igstTotal -
            discount +
            roundOff;


        const due =
            grandTotal -
            paid;


        document.getElementById(
            'summary-subtotal'
        ).textContent =
            subtotal.toFixed(2);


        document.getElementById(
            'summary-cgst'
        ).textContent =
            cgstTotal.toFixed(2);


        document.getElementById(
            'summary-sgst'
        ).textContent =
            sgstTotal.toFixed(2);


        document.getElementById(
            'summary-igst'
        ).textContent =
            igstTotal.toFixed(2);


        document.getElementById(
            'summary-grand-total'
        ).textContent =
            grandTotal.toFixed(2);


        document.getElementById(
            'summary-due'
        ).textContent =
            due.toFixed(2);
    }


    /*
    |--------------------------------------------------------------------------
    | Add Item
    |--------------------------------------------------------------------------
    */
    addRowButton?.addEventListener(
        'click',
        function () {

            const fragment =
                rowTemplate.content
                    .cloneNode(true);

            const row =
                fragment.querySelector(
                    '.purchase-item-row'
                );

            prepareNewRow(
                row,
                nextIndex++
            );


            body.appendChild(
                fragment
            );


            calculatePurchase();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Remove
    |--------------------------------------------------------------------------
    */
    body?.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '.purchase-remove-row'
                );

            if (!button) {
                return;
            }


            const rows =
                body.querySelectorAll(
                    '.purchase-item-row'
                );


            if (rows.length <= 1) {

                alert(
                    'At least one purchase item is required.'
                );

                return;
            }


            button
                .closest(
                    '.purchase-item-row'
                )
                ?.remove();


            calculatePurchase();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Item Change
    |--------------------------------------------------------------------------
    | Selected item:
    |
    | Unit       -> item.unit
    | Rate       -> item.cost_price
    | GST        -> item.tax_rate
    |--------------------------------------------------------------------------
    */
    body?.addEventListener(
        'change',
        function (event) {

            if (
                !event.target.classList
                    .contains(
                        'purchase-item-select'
                    )
            ) {
                return;
            }


            const select =
                event.target;

            const row =
                select.closest(
                    '.purchase-item-row'
                );

            const option =
                select.options[
                    select.selectedIndex
                ];


            if (!row || !option) {
                return;
            }


            const itemUnit =
                option.dataset.unit || '';

            const itemRate =
                option.dataset.rate || '0';

            const itemGst =
                option.dataset.gst || '0';


            const unitSelect =
                row.querySelector(
                    '.purchase-unit-select'
                );


            /*
            |--------------------------------------------------------------------------
            | Unit auto select
            |--------------------------------------------------------------------------
            */
            if (
                unitSelect &&
                itemUnit
            ) {

                const matchingOption =
                    Array.from(
                        unitSelect.options
                    ).find(function (unitOption) {

                        return (
                            unitOption.value
                                .trim()
                                .toLowerCase()
                            ===
                            itemUnit
                                .trim()
                                .toLowerCase()
                        );

                    });


                if (matchingOption) {

                    unitSelect.value =
                        matchingOption.value;

                } else {

                    /*
                     * Old/legacy unit present on item
                     * but Unit Master me missing hai.
                     */
                    const option =
                        new Option(
                            itemUnit,
                            itemUnit,
                            true,
                            true
                        );

                    unitSelect.add(option);
                }
            }


            const rateInput =
                row.querySelector(
                    '.purchase-rate-input'
                );

            const gstInput =
                row.querySelector(
                    '.purchase-gst-input'
                );


            if (rateInput) {
                rateInput.value =
                    toNumber(itemRate)
                        .toFixed(2);
            }


            if (gstInput) {
                gstInput.value =
                    toNumber(itemGst);
            }


            calculatePurchase();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Live Calculation
    |--------------------------------------------------------------------------
    */
    body?.addEventListener(
        'input',
        function (event) {

            if (
                event.target.matches(
                    '.purchase-qty-input, ' +
                    '.purchase-rate-input, ' +
                    '.purchase-gst-input'
                )
            ) {

                calculatePurchase();

            }

        }
    );


    taxType?.addEventListener(
        'change',
        calculatePurchase
    );


    [
        'purchase-discount',
        'purchase-round-off',
        'purchase-paid',
    ].forEach(function (id) {

        document
            .getElementById(id)
            ?.addEventListener(
                'input',
                calculatePurchase
            );

    });



    /*
    |--------------------------------------------------------------------------
    | AI PURCHASE BILL SCAN
    |--------------------------------------------------------------------------
    */
    function escapeHtml(value)
    {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }


    function showScanStatus(message, type = 'info')
    {
        if (!scanStatus) {
            return;
        }

        scanStatus.className =
            'mt-3 rounded-xl border px-4 py-3 text-sm';

        const classes = {
            info: [
                'border-blue-200', 'bg-blue-50', 'text-blue-700',
                'dark:border-blue-900/60', 'dark:bg-blue-950/30', 'dark:text-blue-300'
            ],
            success: [
                'border-emerald-200', 'bg-emerald-50', 'text-emerald-700',
                'dark:border-emerald-900/60', 'dark:bg-emerald-950/30', 'dark:text-emerald-300'
            ],
            error: [
                'border-red-200', 'bg-red-50', 'text-red-700',
                'dark:border-red-900/60', 'dark:bg-red-950/30', 'dark:text-red-300'
            ],
        };

        scanStatus.classList.add(
            ...(classes[type] || classes.info)
        );

        scanStatus.textContent = message;
        scanStatus.classList.remove('hidden');
    }


    function hideScanSuggestions()
    {
        if (!scanSuggestions) {
            return;
        }

        scanSuggestions.innerHTML = '';
        scanSuggestions.classList.add('hidden');
    }


    function ensureUnitOption(unitSelect, unit)
    {
        if (!unitSelect || !unit) {
            return;
        }

        const wanted = String(unit).trim();

        if (!wanted) {
            return;
        }

        const existing = Array.from(unitSelect.options)
            .find(function (option) {
                return option.value.trim().toLowerCase()
                    === wanted.toLowerCase();
            });

        if (existing) {
            unitSelect.value = existing.value;
            return;
        }

        unitSelect.add(
            new Option(wanted, wanted, true, true)
        );
    }


    function setInputValue(selector, value)
    {
        const element = document.querySelector(selector);

        if (!element || value === null || value === undefined) {
            return;
        }

        element.value = value;
    }


    function createScannedRow(rowData)
    {
        const fragment = rowTemplate.content.cloneNode(true);
        const row = fragment.querySelector('.purchase-item-row');

        prepareNewRow(row, nextIndex++);

        const itemSelect = row.querySelector('.purchase-item-select');
        const qtyInput = row.querySelector('.purchase-qty-input');
        const unitSelect = row.querySelector('.purchase-unit-select');
        const rateInput = row.querySelector('.purchase-rate-input');
        const gstInput = row.querySelector('.purchase-gst-input');

        const matched = rowData.matched_item || null;

        if (matched && itemSelect) {
            itemSelect.value = String(matched.id);

            // Safety: if the item was not present in current select for any reason,
            // add it so the submitted value still points to the matched item.
            if (itemSelect.value !== String(matched.id)) {
                const option = new Option(
                    matched.name + (matched.sku ? ` (${matched.sku})` : ''),
                    matched.id,
                    true,
                    true
                );

                option.dataset.unit = matched.unit || '';
                option.dataset.rate = matched.rate || 0;
                option.dataset.gst = matched.gst_rate || 0;
                itemSelect.add(option);
            }
        }

        if (!matched && itemSelect) {
            itemSelect.value = '';
            itemSelect.classList.add(
                'border-amber-400',
                'ring-2',
                'ring-amber-100'
            );
            itemSelect.title =
                `Bill item not found in Item Master: ${rowData.bill_name || ''}`;
        }

        if (qtyInput) {
            qtyInput.value = toNumber(rowData.qty) > 0
                ? toNumber(rowData.qty)
                : 1;
        }

        const unit = rowData.unit || matched?.unit || '';
        ensureUnitOption(unitSelect, unit);

        let rate = rowData.rate;

        if (
            (rate === null || rate === undefined)
            && toNumber(rowData.taxable_amount) > 0
            && toNumber(rowData.qty) > 0
        ) {
            rate = toNumber(rowData.taxable_amount) / toNumber(rowData.qty);
        }

        if (rate === null || rate === undefined) {
            rate = matched?.rate || 0;
        }

        if (rateInput) {
            rateInput.value = toNumber(rate).toFixed(2);
        }

        const gstRate =
            rowData.gst_rate !== null && rowData.gst_rate !== undefined
                ? rowData.gst_rate
                : (matched?.gst_rate || 0);

        if (gstInput) {
            gstInput.value = toNumber(gstRate);
        }

        row.dataset.billItemName = rowData.bill_name || '';
        row.dataset.billItemSku = rowData.bill_sku || '';

        return fragment;
    }


    function applyScannedBill(result)
    {
        const purchase = result.purchase || {};
        const supplier = result.supplier || {};
        const scannedItems = Array.isArray(result.items)
            ? result.items
            : [];

        setInputValue(
            '[name="invoice_no"]',
            purchase.invoice_no
        );

        if (purchase.invoice_date) {
            setInputValue(
                '[name="invoice_date"]',
                purchase.invoice_date
            );
        }

        if (
            taxType
            && ['intra_state', 'inter_state'].includes(purchase.tax_type)
        ) {
            taxType.value = purchase.tax_type;
        }

        setInputValue(
            '#purchase-discount',
            purchase.discount_amount ?? 0
        );

        setInputValue(
            '#purchase-round-off',
            purchase.round_off ?? 0
        );

        if (purchase.paid_amount !== null && purchase.paid_amount !== undefined) {
            setInputValue(
                '#purchase-paid',
                purchase.paid_amount
            );
        }

        const supplierSelect =
            document.getElementById('supplier_id');

        if (supplier.match?.id && supplierSelect) {
            supplierSelect.value = String(supplier.match.id);
        }

        if (scannedItems.length > 0 && body && rowTemplate) {
            body.innerHTML = '';
            nextIndex = 0;

            scannedItems.forEach(function (rowData) {
                body.appendChild(
                    createScannedRow(rowData)
                );
            });
        }

        renderScanSuggestions(result);
        calculatePurchase();
    }


    function renderScanSuggestions(result)
    {
        if (!scanSuggestions) {
            return;
        }

        const supplier = result.supplier || {};
        const rows = Array.isArray(result.items)
            ? result.items
            : [];

        const missingItems = rows.filter(
            row => row.needs_item_creation
        );

        const parts = [];

        if (supplier.needs_creation) {
            parts.push(`
                <div class="mb-3 rounded-lg border border-amber-300 bg-white/70 p-3 dark:bg-slate-900/40">
                    <div class="font-semibold">Supplier not found in Supplier Master</div>
                    <div class="mt-1 text-xs">
                        ${escapeHtml(supplier.extracted_name || 'Unknown supplier')}
                        ${supplier.extracted_gstin ? ' • GSTIN: ' + escapeHtml(supplier.extracted_gstin) : ''}
                    </div>
                    <button type="button"
                            id="scan-create-supplier-btn"
                            class="mt-2 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                        + Add this Supplier
                    </button>
                </div>
            `);
        }

        if (missingItems.length > 0) {
            const createBaseUrl = scanBillButton?.dataset.itemCreateUrl || '';

            const cards = missingItems.map(function (row) {
                let createLink = '';

                if (createBaseUrl) {
                    const url = new URL(createBaseUrl, window.location.origin);
                    url.searchParams.set('name', row.bill_name || '');

                    if (row.bill_sku) {
                        url.searchParams.set('sku', row.bill_sku);
                    }

                    if (row.unit) {
                        url.searchParams.set('unit', row.unit);
                    }

                    if (row.rate !== null && row.rate !== undefined) {
                        url.searchParams.set('cost_price', row.rate);
                    }

                    if (row.gst_rate !== null && row.gst_rate !== undefined) {
                        url.searchParams.set('tax_rate', row.gst_rate);
                    }

                    createLink = `
                        <a href="${url.toString()}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="mt-2 inline-flex rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">
                            Create Item ↗
                        </a>
                    `;
                }

                return `
                    <div class="rounded-lg border border-amber-300 bg-white/70 p-3 dark:bg-slate-900/40">
                        <div class="font-semibold">${escapeHtml(row.bill_name || 'Unnamed item')}</div>
                        <div class="mt-1 text-xs">
                            Qty: ${escapeHtml(String(row.qty ?? 1))}
                            ${row.unit ? ' • Unit: ' + escapeHtml(row.unit) : ''}
                            ${row.bill_sku ? ' • Code: ' + escapeHtml(row.bill_sku) : ''}
                        </div>
                        <div class="mt-1 text-xs font-semibold text-amber-700 dark:text-amber-300">
                            Item not found — create it in Item Master or manually select the correct item.
                        </div>
                        ${createLink}
                    </div>
                `;
            }).join('');

            parts.push(`
                <div>
                    <div class="mb-2 font-bold">
                        ${missingItems.length} item(s) need your attention
                    </div>
                    <div class="grid gap-2 md:grid-cols-2">
                        ${cards}
                    </div>
                    <div class="mt-3 text-xs">
                        If you create an item in a new tab, come back and scan the bill again so it can be matched automatically.
                    </div>
                </div>
            `);
        }

        if (parts.length === 0) {
            scanSuggestions.innerHTML = `
                <div class="font-semibold text-emerald-700 dark:text-emerald-300">
                    All bill items and supplier were matched successfully.
                </div>
            `;
        } else {
            scanSuggestions.innerHTML = parts.join('');
        }

        scanSuggestions.classList.remove('hidden');

        const createSupplierButton =
            document.getElementById('scan-create-supplier-btn');

        createSupplierButton?.addEventListener('click', function () {
            const nameInput = document.getElementById('supplier-name');
            const mobileInput = document.getElementById('supplier-mobile');
            const gstinInput = document.getElementById('supplier-gstin');

            if (nameInput) {
                nameInput.value = supplier.extracted_name || '';
            }

            if (mobileInput) {
                mobileInput.value = supplier.extracted_mobile || '';
            }

            if (gstinInput) {
                gstinInput.value = supplier.extracted_gstin || '';
            }

            openSupplierModal();
        });
    }


    billFileInput?.addEventListener('change', function () {
        hideScanSuggestions();

        if (scanStatus) {
            scanStatus.classList.add('hidden');
        }
    });


    scanBillButton?.addEventListener('click', async function () {
        const file = billFileInput?.files?.[0];

        if (!file) {
            showScanStatus(
                'Please choose a purchase bill first.',
                'error'
            );
            return;
        }

        const oldHtml = scanBillButton.innerHTML;

        scanBillButton.disabled = true;
        scanBillButton.innerHTML = 'Scanning bill...';
        hideScanSuggestions();
        showScanStatus(
            'Reading invoice, matching supplier and checking Item Master...',
            'info'
        );

        try {
            const formData = new FormData();
            formData.append('bill_file', file);

            const response = await fetch(
                "{{ route('purchases.scan-bill') }}",
                {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    },
                    body: formData,
                }
            );

            let result = {};

            try {
                result = await response.json();
            } catch (jsonError) {
                throw new Error(
                    'Server returned an invalid response while scanning the bill.'
                );
            }

            if (!response.ok || !result.success) {
                const validationMessages = result.errors
                    ? Object.values(result.errors).flat().join(' ')
                    : '';

                throw new Error(
                    validationMessages
                    || result.message
                    || 'Unable to scan the purchase bill.'
                );
            }

            applyScannedBill(result);

            const missing = toNumber(result.missing_items_count);

            showScanStatus(
                missing > 0
                    ? `Bill scanned. ${missing} item(s) were not found in Item Master.`
                    : 'Bill scanned successfully. Please review the filled details before saving.',
                missing > 0 ? 'info' : 'success'
            );

        } catch (error) {
            console.error(error);

            showScanStatus(
                error.message || 'Something went wrong while scanning the bill.',
                'error'
            );
        } finally {
            scanBillButton.disabled = false;
            scanBillButton.innerHTML = oldHtml;
        }
    });


    /*
    |--------------------------------------------------------------------------
    | SUPPLIER MODAL
    |--------------------------------------------------------------------------
    */
    const supplierModal =
        document.getElementById(
            'supplier-modal'
        );

    const openSupplierButton =
        document.getElementById(
            'open-supplier-modal-btn'
        );

    const closeSupplierButton =
        document.getElementById(
            'close-supplier-modal-btn'
        );

    const cancelSupplierButton =
        document.getElementById(
            'supplier-modal-cancel'
        );

    const saveSupplierButton =
        document.getElementById(
            'save-supplier-btn'
        );

    const supplierError =
        document.getElementById(
            'supplier-error'
        );


    function openSupplierModal()
    {
        supplierModal
            ?.classList
            .remove('hidden');

        supplierModal
            ?.classList
            .add('flex');


        setTimeout(function () {

            document
                .getElementById(
                    'supplier-name'
                )
                ?.focus();

        }, 100);
    }


    function closeSupplierModal()
    {
        supplierModal
            ?.classList
            .add('hidden');

        supplierModal
            ?.classList
            .remove('flex');

        supplierError
            ?.classList
            .add('hidden');
    }


    openSupplierButton
        ?.addEventListener(
            'click',
            openSupplierModal
        );


    closeSupplierButton
        ?.addEventListener(
            'click',
            closeSupplierModal
        );


    cancelSupplierButton
        ?.addEventListener(
            'click',
            closeSupplierModal
        );


    supplierModal
        ?.addEventListener(
            'click',
            function (event) {

                if (
                    event.target ===
                    supplierModal
                ) {
                    closeSupplierModal();
                }

            }
        );


    /*
    |--------------------------------------------------------------------------
    | Save Supplier
    |--------------------------------------------------------------------------
    */
    saveSupplierButton
        ?.addEventListener(
            'click',
            async function () {

                const name =
                    document
                        .getElementById(
                            'supplier-name'
                        )
                        ?.value
                        .trim() || '';

                const mobile =
                    document
                        .getElementById(
                            'supplier-mobile'
                        )
                        ?.value
                        .trim() || '';

                const email =
                    document
                        .getElementById(
                            'supplier-email'
                        )
                        ?.value
                        .trim() || '';

                const gstin =
                    document
                        .getElementById(
                            'supplier-gstin'
                        )
                        ?.value
                        .trim() || '';

                const address =
                    document
                        .getElementById(
                            'supplier-address'
                        )
                        ?.value
                        .trim() || '';


                supplierError.innerHTML =
                    '';

                supplierError.classList
                    .add('hidden');


                if (!name) {

                    supplierError.innerHTML =
                        'Supplier name is required.';

                    supplierError.classList
                        .remove('hidden');

                    return;
                }


                const oldButtonText =
                    saveSupplierButton
                        .innerHTML;


                saveSupplierButton.disabled =
                    true;

                saveSupplierButton.innerHTML =
                    'Saving...';


                try {

                    const response =
                        await fetch(
                            "{{ route('purchases.suppliers.store') }}",
                            {
                                method: 'POST',

                                headers: {
                                    'Content-Type':
                                        'application/json',

                                    'Accept':
                                        'application/json',

                                    'X-Requested-With':
                                        'XMLHttpRequest',

                                    'X-CSRF-TOKEN':
                                        "{{ csrf_token() }}",
                                },

                                body: JSON.stringify({
                                    name,
                                    mobile,
                                    email,
                                    gstin,
                                    address,
                                }),
                            }
                        );


                    const result =
                        await response.json();


                    if (!response.ok) {

                        const messages = [];

                        if (result.errors) {

                            Object.values(
                                result.errors
                            ).forEach(function (errors) {

                                errors.forEach(
                                    function (error) {
                                        messages.push(
                                            error
                                        );
                                    }
                                );

                            });

                        } else {

                            messages.push(
                                result.message
                                ||
                                'Unable to create supplier.'
                            );
                        }


                        supplierError.innerHTML =
                            messages.join('<br>');

                        supplierError.classList
                            .remove('hidden');

                        return;
                    }


                    const supplier =
                        result.supplier;


                    const supplierSelect =
                        document.getElementById(
                            'supplier_id'
                        );


                    if (
                        supplier &&
                        supplierSelect
                    ) {

                        const option =
                            new Option(
                                supplier.name +
                                (
                                    supplier.mobile
                                        ? ' - ' +
                                            supplier.mobile
                                        : ''
                                ),
                                supplier.id,
                                true,
                                true
                            );


                        supplierSelect.add(
                            option
                        );
                    }


                    document.getElementById(
                        'supplier-name'
                    ).value = '';

                    document.getElementById(
                        'supplier-mobile'
                    ).value = '';

                    document.getElementById(
                        'supplier-email'
                    ).value = '';

                    document.getElementById(
                        'supplier-gstin'
                    ).value = '';

                    document.getElementById(
                        'supplier-address'
                    ).value = '';


                    closeSupplierModal();

                } catch (error) {

                    console.error(error);

                    supplierError.innerHTML =
                        'Something went wrong while creating supplier.';

                    supplierError.classList
                        .remove('hidden');

                } finally {

                    saveSupplierButton.disabled =
                        false;

                    saveSupplierButton.innerHTML =
                        oldButtonText;
                }

            }
        );


    /*
    |--------------------------------------------------------------------------
    | Initial Calculate
    |--------------------------------------------------------------------------
    */
    calculatePurchase();

});
</script>