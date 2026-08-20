@php
    $isEdit = $purchase->exists;

    $oldItems = old(
        'items',
        $isEdit
            ? $purchase->items->toArray()
            : [
                [
                    'item_id' => null,
                    'qty' => 1,
                    'qty_unit' => 'pcs',
                    'rate' => 0,
                    'gst_rate' => 3,
                ]
            ]
    );
@endphp


{{-- ========================================================= --}}
{{-- PAGE HEADER --}}
{{-- ========================================================= --}}

<div
    class="
        max-w-6xl
        mx-auto
        bg-[#BFE0E0]
        dark:bg-[#354A54]
        p-6
        text-center
        text-xl
        font-bold
        my-2
        rounded-sm
    "
>
    {{ $isEdit ? 'Edit Purchase' : 'Create Purchase' }}
</div>


<div
    class="
        space-y-6
        text-gray-900
        dark:text-neutral-100
        max-w-6xl
        mx-auto
        p-6
        bg-[#F3F4F6]
        dark:bg-[#1A1D23]
    "
>

    {{-- ========================================================= --}}
    {{-- PURCHASE BASIC DETAILS --}}
    {{-- ========================================================= --}}

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Supplier --}}
        <div>

            <label
                for="supplier_id"
                class="block text-sm font-medium mb-1"
            >
                Supplier
            </label>

            <div class="flex items-center gap-2">

                <select
                    name="supplier_id"
                    id="supplier_id"
                    class="
                        min-w-0
                        flex-1
                        border
                        rounded
                        px-3
                        py-2
                        bg-white
                        text-gray-900
                        border-gray-300
                        dark:bg-neutral-800
                        dark:text-white
                        dark:border-neutral-600
                        focus:ring-2
                        focus:ring-blue-500
                        focus:border-blue-500
                    "
                >

                    <option value="">
                        Select supplier...
                    </option>

                    @foreach ($suppliers as $sup)

                        <option
                            value="{{ $sup->id }}"
                            @selected(
                                old(
                                    'supplier_id',
                                    $purchase->supplier_id
                                ) == $sup->id
                            )
                        >
                            {{ $sup->name }}
                        </option>

                    @endforeach

                </select>


                <button
                    type="button"
                    id="open-supplier-modal-btn"
                    class="
                        shrink-0
                        whitespace-nowrap
                        rounded
                        bg-blue-600
                        px-3
                        py-2
                        text-sm
                        font-semibold
                        text-white
                        hover:bg-blue-700
                        focus:outline-none
                        focus:ring-2
                        focus:ring-blue-500
                    "
                >
                    + Add
                </button>

            </div>


            @error('supplier_id')
                <p class="mt-1 text-xs text-red-600">
                    {{ $message }}
                </p>
            @enderror

        </div>



        {{-- Invoice Number --}}
        <div>

            <label
                class="block text-sm font-medium mb-1"
            >
                Invoice #
            </label>

            <input
                type="text"
                name="invoice_no"
                class="
                    w-full
                    border
                    rounded
                    px-3
                    py-2
                    bg-white
                    text-gray-900
                    border-gray-300
                    dark:bg-neutral-800
                    dark:text-white
                    dark:border-neutral-600
                "
                value="{{ old('invoice_no', $purchase->invoice_no) }}"
            >

            @error('invoice_no')
                <p class="mt-1 text-xs text-red-600">
                    {{ $message }}
                </p>
            @enderror

        </div>



        {{-- Purchase Date --}}
        <div>

            <label
                class="block text-sm font-medium mb-1"
            >
                Purchase Date
            </label>

            <input
                type="date"
                name="invoice_date"
                class="
                    w-full
                    border
                    rounded
                    px-3
                    py-2
                    bg-white
                    text-gray-900
                    border-gray-300
                    dark:bg-neutral-800
                    dark:text-white
                    dark:border-neutral-600
                "
                value="{{ old(
                    'invoice_date',
                    optional($purchase->invoice_date)->format('Y-m-d')
                    ?? now()->format('Y-m-d')
                ) }}"
                required
            >

            @error('invoice_date')
                <p class="mt-1 text-xs text-red-600">
                    {{ $message }}
                </p>
            @enderror

        </div>



        {{-- Bill File --}}
        <div>

            <label
                class="block text-sm font-medium mb-1"
            >
                Upload Bill
            </label>

            <input
                type="file"
                name="bill_file"
                accept=".jpg,.jpeg,.png,.pdf"
                class="
                    w-full
                    border
                    rounded
                    px-3
                    py-2
                    bg-white
                    text-gray-900
                    border-gray-300
                    dark:bg-neutral-800
                    dark:text-white
                    dark:border-neutral-600
                "
            >

            @if (!empty($purchase->bill_file))

                <a
                    href="{{ asset('storage/' . $purchase->bill_file) }}"
                    target="_blank"
                    class="
                        inline-block
                        mt-1
                        text-xs
                        text-blue-600
                        dark:text-blue-400
                        underline
                    "
                >
                    View Uploaded Bill
                </a>

            @endif


            @error('bill_file')
                <p class="mt-1 text-xs text-red-600">
                    {{ $message }}
                </p>
            @enderror

        </div>



        {{-- Tax Type --}}
        <div>

            <label
                for="purchase-tax-type"
                class="block text-sm font-medium mb-1"
            >
                Tax Type
            </label>

            <select
                name="tax_type"
                id="purchase-tax-type"
                class="
                    w-full
                    border
                    rounded
                    px-3
                    py-2
                    bg-white
                    text-gray-900
                    border-gray-300
                    dark:bg-neutral-800
                    dark:text-white
                    dark:border-neutral-600
                "
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

    </div>



    {{-- ========================================================= --}}
    {{-- ITEMS --}}
    {{-- ========================================================= --}}

    <div
        class="
            border
            rounded-lg
            overflow-hidden
            border-gray-200
            dark:border-neutral-700
        "
    >

        <div
            class="
                flex
                items-center
                justify-between
                px-3
                py-2
                bg-gray-100
                dark:bg-neutral-800
            "
        >

            <h3 class="font-semibold text-sm">
                Items
            </h3>


            <button
                type="button"
                id="purchase-add-row"
                class="
                    text-xs
                    px-3
                    py-2
                    rounded
                    bg-sky-500
                    text-white
                    hover:bg-sky-600
                "
            >
                + Add Item
            </button>

        </div>


        <div class="overflow-x-auto">

            <table class="w-max min-w-full text-xs">

                <thead
                    class="
                        bg-[#BFE0E0]
                        dark:bg-[#354A54]
                    "
                >

                    <tr class="[&>th]:px-3 [&>th]:py-2 text-left">

                        <th class="w-[380px] min-w-[380px]">
                            Item
                        </th>

                        <th class="w-[90px] min-w-[90px]">
                            Qty
                        </th>

                        <th class="w-[120px] min-w-[120px]">
                            Unit
                        </th>

                        <th class="w-[110px] min-w-[110px]">
                            Rate
                        </th>

                        <th class="w-[130px] min-w-[130px]">
                            Taxable
                        </th>

                        <th class="w-[90px] min-w-[90px]">
                            GST %
                        </th>

                        <th class="w-[110px] min-w-[110px]">
                            CGST
                        </th>

                        <th class="w-[110px] min-w-[110px]">
                            SGST
                        </th>

                        <th class="w-[110px] min-w-[110px]">
                            IGST
                        </th>

                        <th class="w-[130px] min-w-[130px]">
                            Total
                        </th>

                        <th class="w-[80px] min-w-[80px]"></th>

                    </tr>

                </thead>



                <tbody
                    id="purchase-items-body"
                    class="
                        divide-y
                        divide-gray-200
                        dark:divide-neutral-700
                    "
                >

                    @foreach ($oldItems as $i => $row)

                        <tr
                            class="
                                purchase-item-row
                                bg-white
                                dark:bg-neutral-900
                            "
                        >

                            {{-- Item --}}
                            <td class="px-3 py-2 w-[380px] min-w-[380px]">

                                <select
                                    name="items[{{ $i }}][item_id]"
                                    class="
                                        w-full
                                        min-w-[350px]
                                        border
                                        rounded
                                        px-2
                                        py-2
                                        bg-white
                                        text-gray-900
                                        dark:bg-neutral-800
                                        dark:text-white
                                        dark:border-neutral-600
                                    "
                                    required
                                >

                                    <option value="">
                                        Select item...
                                    </option>

                                    @foreach ($items as $it)

                                        <option
                                            value="{{ $it->id }}"
                                            @selected(
                                                ($row['item_id'] ?? null)
                                                == $it->id
                                            )
                                        >
                                            {{ $it->name }}

                                            @if ($it->sku)
                                                ({{ $it->sku }})
                                            @endif
                                        </option>

                                    @endforeach

                                </select>

                            </td>



                            {{-- Qty --}}
                            <td class="px-3 py-2">

                                <input
                                    type="number"
                                    min="0.001"
                                    step="0.001"
                                    name="items[{{ $i }}][qty]"
                                    class="
                                        w-20
                                        border
                                        rounded
                                        px-2
                                        py-2
                                        purchase-qty-input
                                        dark:bg-neutral-800
                                        dark:border-neutral-600
                                    "
                                    value="{{ $row['qty'] ?? 1 }}"
                                    required
                                >

                            </td>



                            {{-- Unit --}}
                            <td class="px-3 py-2">

                                <select
                                    name="items[{{ $i }}][qty_unit]"
                                    class="
                                        w-28
                                        border
                                        rounded
                                        px-2
                                        py-2
                                        bg-white
                                        text-gray-900
                                        dark:bg-neutral-800
                                        dark:text-white
                                        dark:border-neutral-600
                                    "
                                    required
                                >

                                    @php
                                        $selectedUnit =
                                            $row['qty_unit'] ?? 'pcs';
                                    @endphp

                                    <option
                                        value="pcs"
                                        @selected($selectedUnit === 'pcs')
                                    >
                                        Pcs
                                    </option>

                                    <option
                                        value="gram"
                                        @selected($selectedUnit === 'gram')
                                    >
                                        Gram
                                    </option>

                                    <option
                                        value="kg"
                                        @selected($selectedUnit === 'kg')
                                    >
                                        Kg
                                    </option>

                                    <option
                                        value="carat"
                                        @selected($selectedUnit === 'carat')
                                    >
                                        Carat
                                    </option>

                                    <option
                                        value="pair"
                                        @selected($selectedUnit === 'pair')
                                    >
                                        Pair
                                    </option>

                                    <option
                                        value="set"
                                        @selected($selectedUnit === 'set')
                                    >
                                        Set
                                    </option>

                                    <option
                                        value="dozen"
                                        @selected($selectedUnit === 'dozen')
                                    >
                                        Dozen
                                    </option>

                                </select>

                            </td>



                            {{-- Rate --}}
                            <td class="px-3 py-2">

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="items[{{ $i }}][rate]"
                                    class="
                                        w-24
                                        border
                                        rounded
                                        px-2
                                        py-2
                                        purchase-rate-input
                                        dark:bg-neutral-800
                                        dark:border-neutral-600
                                    "
                                    value="{{ $row['rate'] ?? 0 }}"
                                    required
                                >

                            </td>



                            {{-- Amount --}}
                            <td class="px-3 py-2">

                                <input
                                    type="number"
                                    step="0.01"
                                    name="items[{{ $i }}][amount]"
                                    class="
                                        w-28
                                        border
                                        rounded
                                        px-2
                                        py-2
                                        purchase-amount-input
                                        bg-gray-100
                                        dark:bg-neutral-700
                                        dark:border-neutral-600
                                    "
                                    value="{{ $row['amount'] ?? 0 }}"
                                    readonly
                                >

                            </td>



                            {{-- GST --}}
                            <td class="px-3 py-2">

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="items[{{ $i }}][gst_rate]"
                                    class="
                                        w-20
                                        border
                                        rounded
                                        px-2
                                        py-2
                                        purchase-gst-input
                                        dark:bg-neutral-800
                                        dark:border-neutral-600
                                    "
                                    value="{{ $row['gst_rate'] ?? 3 }}"
                                >

                            </td>



                            {{-- CGST --}}
                            <td class="px-3 py-2">

                                <input
                                    type="number"
                                    step="0.01"
                                    name="items[{{ $i }}][cgst_amount]"
                                    class="
                                        w-24
                                        border
                                        rounded
                                        px-2
                                        py-2
                                        purchase-cgst-input
                                        bg-gray-100
                                        dark:bg-neutral-700
                                        dark:border-neutral-600
                                    "
                                    value="{{ $row['cgst_amount'] ?? 0 }}"
                                    readonly
                                >

                            </td>



                            {{-- SGST --}}
                            <td class="px-3 py-2">

                                <input
                                    type="number"
                                    step="0.01"
                                    name="items[{{ $i }}][sgst_amount]"
                                    class="
                                        w-24
                                        border
                                        rounded
                                        px-2
                                        py-2
                                        purchase-sgst-input
                                        bg-gray-100
                                        dark:bg-neutral-700
                                        dark:border-neutral-600
                                    "
                                    value="{{ $row['sgst_amount'] ?? 0 }}"
                                    readonly
                                >

                            </td>



                            {{-- IGST --}}
                            <td class="px-3 py-2">

                                <input
                                    type="number"
                                    step="0.01"
                                    name="items[{{ $i }}][igst_amount]"
                                    class="
                                        w-24
                                        border
                                        rounded
                                        px-2
                                        py-2
                                        purchase-igst-input
                                        bg-gray-100
                                        dark:bg-neutral-700
                                        dark:border-neutral-600
                                    "
                                    value="{{ $row['igst_amount'] ?? 0 }}"
                                    readonly
                                >

                            </td>



                            {{-- Total --}}
                            <td class="px-3 py-2">

                                <input
                                    type="number"
                                    step="0.01"
                                    name="items[{{ $i }}][total_amount]"
                                    class="
                                        w-28
                                        border
                                        rounded
                                        px-2
                                        py-2
                                        purchase-line-total-input
                                        bg-gray-100
                                        dark:bg-neutral-700
                                        dark:border-neutral-600
                                    "
                                    value="{{ $row['total_amount'] ?? 0 }}"
                                    readonly
                                >

                            </td>



                            {{-- Remove --}}
                            <td class="px-3 py-2 text-right">

                                <button
                                    type="button"
                                    class="
                                        text-red-600
                                        hover:text-red-800
                                        text-xs
                                        font-medium
                                        purchase-remove-row
                                    "
                                >
                                    Remove
                                </button>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>



    {{-- ========================================================= --}}
    {{-- PURCHASE SUMMARY --}}
    {{-- ========================================================= --}}

    <div
        class="
            border
            rounded-lg
            border-gray-200
            dark:border-neutral-700
            overflow-hidden
        "
    >

        <div
            class="
                px-4
                py-3
                bg-gray-100
                dark:bg-neutral-800
            "
        >
            <h3 class="font-semibold text-sm">
                Purchase Summary
            </h3>
        </div>


        <div
            class="
                grid
                grid-cols-1
                md:grid-cols-4
                gap-4
                p-4
                bg-white
                dark:bg-neutral-900
            "
        >

            {{-- Subtotal --}}
            <div>

                <label class="block text-sm font-medium mb-1">
                    Subtotal
                </label>

                <input
                    type="number"
                    step="0.01"
                    name="subtotal"
                    id="purchase-subtotal"
                    class="
                        w-full
                        border
                        rounded
                        px-3
                        py-2
                        bg-gray-100
                        font-semibold
                        dark:bg-neutral-700
                        dark:border-neutral-600
                    "
                    readonly
                    value="{{ old(
                        'subtotal',
                        $purchase->subtotal ?? 0
                    ) }}"
                >

            </div>



            {{-- CGST --}}
            <div>

                <label class="block text-sm font-medium mb-1">
                    CGST
                </label>

                <input
                    type="number"
                    step="0.01"
                    name="cgst_amount"
                    id="purchase-cgst-total"
                    class="
                        w-full
                        border
                        rounded
                        px-3
                        py-2
                        bg-gray-100
                        dark:bg-neutral-700
                        dark:border-neutral-600
                    "
                    readonly
                    value="{{ old(
                        'cgst_amount',
                        $purchase->cgst_amount ?? 0
                    ) }}"
                >

            </div>



            {{-- SGST --}}
            <div>

                <label class="block text-sm font-medium mb-1">
                    SGST
                </label>

                <input
                    type="number"
                    step="0.01"
                    name="sgst_amount"
                    id="purchase-sgst-total"
                    class="
                        w-full
                        border
                        rounded
                        px-3
                        py-2
                        bg-gray-100
                        dark:bg-neutral-700
                        dark:border-neutral-600
                    "
                    readonly
                    value="{{ old(
                        'sgst_amount',
                        $purchase->sgst_amount ?? 0
                    ) }}"
                >

            </div>



            {{-- IGST --}}
            <div>

                <label class="block text-sm font-medium mb-1">
                    IGST
                </label>

                <input
                    type="number"
                    step="0.01"
                    name="igst_amount"
                    id="purchase-igst-total"
                    class="
                        w-full
                        border
                        rounded
                        px-3
                        py-2
                        bg-gray-100
                        dark:bg-neutral-700
                        dark:border-neutral-600
                    "
                    readonly
                    value="{{ old(
                        'igst_amount',
                        $purchase->igst_amount ?? 0
                    ) }}"
                >

            </div>



            {{-- Discount --}}
            <div>

                <label class="block text-sm font-medium mb-1">
                    Discount
                </label>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="discount_amount"
                    id="purchase-discount"
                    class="
                        w-full
                        border
                        rounded
                        px-3
                        py-2
                        bg-white
                        dark:bg-neutral-800
                        dark:border-neutral-600
                    "
                    value="{{ old(
                        'discount_amount',
                        $purchase->discount_amount ?? 0
                    ) }}"
                >

            </div>



            {{-- Round Off --}}
            <div>

                <label class="block text-sm font-medium mb-1">
                    Round Off
                </label>

                <input
                    type="number"
                    step="0.01"
                    name="round_off"
                    id="purchase-round-off"
                    class="
                        w-full
                        border
                        rounded
                        px-3
                        py-2
                        bg-white
                        dark:bg-neutral-800
                        dark:border-neutral-600
                    "
                    value="{{ old(
                        'round_off',
                        $purchase->round_off ?? 0
                    ) }}"
                >

            </div>



            {{-- Paid Amount --}}
            <div>

                <label class="block text-sm font-medium mb-1">
                    Paid Amount
                </label>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="paid_amount"
                    id="purchase-paid"
                    class="
                        w-full
                        border
                        rounded
                        px-3
                        py-2
                        bg-white
                        dark:bg-neutral-800
                        dark:border-neutral-600
                    "
                    value="{{ old(
                        'paid_amount',
                        $purchase->paid_amount ?? 0
                    ) }}"
                >

            </div>



            {{-- Due --}}
            <div>

                <label class="block text-sm font-medium mb-1">
                    Due Amount
                </label>

                <input
                    type="number"
                    step="0.01"
                    name="due_amount"
                    id="purchase-due"
                    class="
                        w-full
                        border
                        rounded
                        px-3
                        py-2
                        bg-red-50
                        font-bold
                        text-red-700
                        dark:bg-red-950/30
                        dark:border-red-900
                        dark:text-red-400
                    "
                    readonly
                    value="{{ old(
                        'due_amount',
                        $purchase->due_amount ?? 0
                    ) }}"
                >

            </div>



            {{-- Grand Total --}}
            <div class="md:col-span-4">

                <label class="block text-sm font-medium mb-1">
                    Grand Total
                </label>

                <input
                    type="number"
                    step="0.01"
                    name="total_amount"
                    id="purchase-grand-total"
                    class="
                        w-full
                        border
                        rounded
                        px-4
                        py-3
                        bg-green-50
                        text-green-700
                        text-xl
                        font-bold
                        dark:bg-green-950/30
                        dark:border-green-900
                        dark:text-green-400
                    "
                    readonly
                    value="{{ old(
                        'total_amount',
                        $purchase->total_amount ?? 0
                    ) }}"
                >

            </div>

        </div>

    </div>

</div>



{{-- ========================================================= --}}
{{-- ADD SUPPLIER MODAL --}}
{{-- ========================================================= --}}

<div
    id="supplierModal"
    class="
        fixed
        inset-0
        z-[9999]
        hidden
        items-center
        justify-center
        bg-black/50
        p-4
    "
>

    <div
        class="
            w-full
            max-w-lg
            max-h-[90vh]
            overflow-y-auto
            rounded-xl
            bg-white
            dark:bg-neutral-900
            shadow-2xl
        "
    >

        {{-- Header --}}
        <div
            class="
                sticky
                top-0
                z-10
                flex
                items-center
                justify-between
                border-b
                border-gray-200
                dark:border-neutral-700
                bg-white
                dark:bg-neutral-900
                px-5
                py-4
            "
        >

            <div>

                <h2
                    class="
                        text-lg
                        font-semibold
                        text-gray-900
                        dark:text-white
                    "
                >
                    Add New Supplier
                </h2>

                <p class="text-xs text-gray-500 mt-1">
                    Create supplier without leaving purchase
                </p>

            </div>


            <button
                type="button"
                id="close-supplier-modal-btn"
                class="
                    flex
                    h-9
                    w-9
                    items-center
                    justify-center
                    rounded-full
                    text-2xl
                    text-gray-500
                    hover:bg-gray-100
                    dark:hover:bg-neutral-800
                "
            >
                &times;
            </button>

        </div>



        {{-- Body --}}
        <div class="p-5">

            <div
                id="supplier-error"
                class="
                    hidden
                    mb-4
                    rounded
                    border
                    border-red-300
                    bg-red-50
                    p-3
                    text-sm
                    text-red-700
                "
            ></div>


            <div class="space-y-4">

                {{-- Name --}}
                <div>

                    <label
                        for="supplier-name"
                        class="block text-sm font-medium mb-1"
                    >
                        Supplier Name
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        id="supplier-name"
                        class="
                            w-full
                            border
                            rounded
                            px-3
                            py-2
                            bg-white
                            text-gray-900
                            dark:bg-neutral-800
                            dark:text-white
                            dark:border-neutral-600
                        "
                        placeholder="Enter supplier name"
                    >

                </div>



                {{-- Mobile --}}
                <div>

                    <label
                        for="supplier-mobile"
                        class="block text-sm font-medium mb-1"
                    >
                        Mobile Number
                    </label>

                    <input
                        type="text"
                        id="supplier-mobile"
                        maxlength="20"
                        class="
                            w-full
                            border
                            rounded
                            px-3
                            py-2
                            bg-white
                            text-gray-900
                            dark:bg-neutral-800
                            dark:text-white
                            dark:border-neutral-600
                        "
                        placeholder="Enter mobile number"
                    >

                </div>



                {{-- Email --}}
                <div>

                    <label
                        for="supplier-email"
                        class="block text-sm font-medium mb-1"
                    >
                        Email
                    </label>

                    <input
                        type="email"
                        id="supplier-email"
                        class="
                            w-full
                            border
                            rounded
                            px-3
                            py-2
                            bg-white
                            text-gray-900
                            dark:bg-neutral-800
                            dark:text-white
                            dark:border-neutral-600
                        "
                        placeholder="supplier@example.com"
                    >

                </div>



                {{-- GSTIN --}}
                <div>

                    <label
                        for="supplier-gstin"
                        class="block text-sm font-medium mb-1"
                    >
                        GSTIN
                    </label>

                    <input
                        type="text"
                        id="supplier-gstin"
                        maxlength="30"
                        class="
                            w-full
                            border
                            rounded
                            px-3
                            py-2
                            uppercase
                            bg-white
                            text-gray-900
                            dark:bg-neutral-800
                            dark:text-white
                            dark:border-neutral-600
                        "
                        placeholder="Enter GSTIN"
                    >

                </div>



                {{-- Address --}}
                <div>

                    <label
                        for="supplier-address"
                        class="block text-sm font-medium mb-1"
                    >
                        Address
                    </label>

                    <textarea
                        id="supplier-address"
                        rows="3"
                        class="
                            w-full
                            border
                            rounded
                            px-3
                            py-2
                            bg-white
                            text-gray-900
                            dark:bg-neutral-800
                            dark:text-white
                            dark:border-neutral-600
                        "
                        placeholder="Enter supplier address"
                    ></textarea>

                </div>

            </div>

        </div>



        {{-- Footer --}}
        <div
            class="
                sticky
                bottom-0
                flex
                justify-end
                gap-2
                border-t
                border-gray-200
                dark:border-neutral-700
                bg-white
                dark:bg-neutral-900
                px-5
                py-4
            "
        >

            <button
                type="button"
                id="cancel-supplier-btn"
                class="
                    rounded
                    border
                    border-gray-300
                    px-4
                    py-2
                    text-sm
                    font-medium
                    dark:border-neutral-600
                "
            >
                Cancel
            </button>


            <button
                type="button"
                id="save-supplier-btn"
                class="
                    rounded
                    bg-blue-600
                    px-4
                    py-2
                    text-sm
                    font-semibold
                    text-white
                    hover:bg-blue-700
                    disabled:opacity-50
                    disabled:cursor-not-allowed
                "
            >
                Save Supplier
            </button>

        </div>

    </div>

</div>



{{-- ========================================================= --}}
{{-- JAVASCRIPT --}}
{{-- ========================================================= --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ELEMENTS
    |--------------------------------------------------------------------------
    */

    const body =
        document.getElementById('purchase-items-body');

    const addBtn =
        document.getElementById('purchase-add-row');

    const taxTypeInput =
        document.getElementById('purchase-tax-type');

    const supplierSelect =
        document.getElementById('supplier_id');

    const supplierModal =
        document.getElementById('supplierModal');

    const openSupplierBtn =
        document.getElementById('open-supplier-modal-btn');

    const closeSupplierBtn =
        document.getElementById('close-supplier-modal-btn');

    const cancelSupplierBtn =
        document.getElementById('cancel-supplier-btn');

    const saveSupplierBtn =
        document.getElementById('save-supplier-btn');

    const supplierError =
        document.getElementById('supplier-error');



    /*
    |--------------------------------------------------------------------------
    | SUPPLIER MODAL
    |--------------------------------------------------------------------------
    */

    function openSupplierModal()
    {
        supplierError.innerHTML = '';
        supplierError.classList.add('hidden');

        supplierModal.classList.remove('hidden');
        supplierModal.classList.add('flex');

        document.body.style.overflow = 'hidden';

        setTimeout(function () {
            document
                .getElementById('supplier-name')
                ?.focus();
        }, 100);
    }


    function closeSupplierModal()
    {
        supplierModal.classList.add('hidden');
        supplierModal.classList.remove('flex');

        document.body.style.overflow = '';
    }


    function clearSupplierForm()
    {
        document.getElementById('supplier-name').value = '';
        document.getElementById('supplier-mobile').value = '';
        document.getElementById('supplier-email').value = '';
        document.getElementById('supplier-gstin').value = '';
        document.getElementById('supplier-address').value = '';
    }


    openSupplierBtn?.addEventListener(
        'click',
        openSupplierModal
    );


    closeSupplierBtn?.addEventListener(
        'click',
        closeSupplierModal
    );


    cancelSupplierBtn?.addEventListener(
        'click',
        closeSupplierModal
    );


    supplierModal?.addEventListener(
        'click',
        function (event) {

            if (event.target === supplierModal) {
                closeSupplierModal();
            }

        }
    );


    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape' &&
                supplierModal &&
                !supplierModal.classList.contains('hidden')
            ) {
                closeSupplierModal();
            }

        }
    );



    /*
    |--------------------------------------------------------------------------
    | CREATE SUPPLIER
    |--------------------------------------------------------------------------
    */

    saveSupplierBtn?.addEventListener(
        'click',
        async function () {

            const name =
                document
                    .getElementById('supplier-name')
                    .value
                    .trim();

            const mobile =
                document
                    .getElementById('supplier-mobile')
                    .value
                    .trim();

            const email =
                document
                    .getElementById('supplier-email')
                    .value
                    .trim();

            const gstin =
                document
                    .getElementById('supplier-gstin')
                    .value
                    .trim();

            const address =
                document
                    .getElementById('supplier-address')
                    .value
                    .trim();


            supplierError.innerHTML = '';
            supplierError.classList.add('hidden');


            if (!name) {

                supplierError.innerHTML =
                    'Supplier name is required.';

                supplierError.classList.remove('hidden');

                document
                    .getElementById('supplier-name')
                    .focus();

                return;
            }


            const originalText =
                saveSupplierBtn.innerHTML;


            saveSupplierBtn.disabled = true;
            saveSupplierBtn.innerHTML = 'Saving...';


            try {

                const response = await fetch(
                    "{{ route('purchases.suppliers.store') }}",
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },

                        body: JSON.stringify({
                            name: name,
                            mobile: mobile,
                            email: email,
                            gstin: gstin,
                            address: address
                        })
                    }
                );


                let result = null;


                try {

                    result = await response.json();

                } catch (e) {

                    throw new Error(
                        'Server returned an invalid response.'
                    );

                }


                if (!response.ok) {

                    let messages = [];


                    if (result?.errors) {

                        Object.values(result.errors)
                            .forEach(function (errors) {

                                if (Array.isArray(errors)) {

                                    errors.forEach(function (message) {
                                        messages.push(message);
                                    });

                                } else {

                                    messages.push(errors);

                                }

                            });

                    } else {

                        messages.push(
                            result?.message ||
                            'Supplier could not be created.'
                        );

                    }


                    supplierError.innerHTML =
                        messages.join('<br>');

                    supplierError.classList.remove('hidden');

                    return;
                }


                if (
                    !result?.supplier ||
                    !result.supplier.id
                ) {

                    throw new Error(
                        'Supplier information missing from response.'
                    );

                }


                const supplier =
                    result.supplier;


                /*
                |--------------------------------------------------------------------------
                | Remove duplicate option
                |--------------------------------------------------------------------------
                */

                const oldOption =
                    supplierSelect.querySelector(
                        'option[value="' +
                        supplier.id +
                        '"]'
                    );


                if (oldOption) {
                    oldOption.remove();
                }


                /*
                |--------------------------------------------------------------------------
                | Add new supplier in dropdown
                |--------------------------------------------------------------------------
                */

                const option =
                    document.createElement('option');


                option.value =
                    supplier.id;


                option.textContent =
                    supplier.name +
                    (
                        supplier.mobile
                            ? ' - ' + supplier.mobile
                            : ''
                    );


                option.selected = true;


                supplierSelect.appendChild(option);


                /*
                |--------------------------------------------------------------------------
                | Trigger change event
                |--------------------------------------------------------------------------
                */

                supplierSelect.dispatchEvent(
                    new Event(
                        'change',
                        {
                            bubbles: true
                        }
                    )
                );


                clearSupplierForm();

                closeSupplierModal();


            } catch (error) {

                console.error(
                    'Supplier creation error:',
                    error
                );


                supplierError.innerHTML =
                    error.message ||
                    'Something went wrong while creating supplier.';


                supplierError.classList.remove('hidden');

            } finally {

                saveSupplierBtn.disabled = false;
                saveSupplierBtn.innerHTML = originalText;

            }

        }
    );



    /*
    |--------------------------------------------------------------------------
    | PURCHASE CALCULATIONS
    |--------------------------------------------------------------------------
    */

    function numberValue(selector)
    {
        const element =
            document.querySelector(selector);

        return parseFloat(
            element?.value || 0
        );
    }



    function recalcPurchaseTotals()
    {
        let subtotal = 0;

        let cgstTotal = 0;

        let sgstTotal = 0;

        let igstTotal = 0;


        const taxType =
            taxTypeInput?.value || 'intra_state';


        body
            .querySelectorAll('.purchase-item-row')
            .forEach(function (row) {

                const qty =
                    parseFloat(
                        row
                            .querySelector('.purchase-qty-input')
                            ?.value || 0
                    );


                const rate =
                    parseFloat(
                        row
                            .querySelector('.purchase-rate-input')
                            ?.value || 0
                    );


                const gstRate =
                    parseFloat(
                        row
                            .querySelector('.purchase-gst-input')
                            ?.value || 0
                    );


                const amount =
                    qty * rate;


                let cgst = 0;

                let sgst = 0;

                let igst = 0;


                if (taxType === 'intra_state') {

                    cgst =
                        amount *
                        (gstRate / 2) /
                        100;


                    sgst =
                        amount *
                        (gstRate / 2) /
                        100;

                } else {

                    igst =
                        amount *
                        gstRate /
                        100;

                }


                const lineTotal =
                    amount +
                    cgst +
                    sgst +
                    igst;


                const amountInput =
                    row.querySelector(
                        '.purchase-amount-input'
                    );


                const cgstInput =
                    row.querySelector(
                        '.purchase-cgst-input'
                    );


                const sgstInput =
                    row.querySelector(
                        '.purchase-sgst-input'
                    );


                const igstInput =
                    row.querySelector(
                        '.purchase-igst-input'
                    );


                const lineTotalInput =
                    row.querySelector(
                        '.purchase-line-total-input'
                    );


                if (amountInput) {
                    amountInput.value =
                        amount.toFixed(2);
                }


                if (cgstInput) {
                    cgstInput.value =
                        cgst.toFixed(2);
                }


                if (sgstInput) {
                    sgstInput.value =
                        sgst.toFixed(2);
                }


                if (igstInput) {
                    igstInput.value =
                        igst.toFixed(2);
                }


                if (lineTotalInput) {
                    lineTotalInput.value =
                        lineTotal.toFixed(2);
                }


                subtotal += amount;

                cgstTotal += cgst;

                sgstTotal += sgst;

                igstTotal += igst;

            });


        const discount =
            numberValue('#purchase-discount');


        const roundOff =
            numberValue('#purchase-round-off');


        const paid =
            numberValue('#purchase-paid');


        const grandTotal =
            subtotal +
            cgstTotal +
            sgstTotal +
            igstTotal -
            discount +
            roundOff;


        const due =
            grandTotal - paid;


        document
            .getElementById('purchase-subtotal')
            .value =
            subtotal.toFixed(2);


        document
            .getElementById('purchase-cgst-total')
            .value =
            cgstTotal.toFixed(2);


        document
            .getElementById('purchase-sgst-total')
            .value =
            sgstTotal.toFixed(2);


        document
            .getElementById('purchase-igst-total')
            .value =
            igstTotal.toFixed(2);


        document
            .getElementById('purchase-grand-total')
            .value =
            grandTotal.toFixed(2);


        document
            .getElementById('purchase-due')
            .value =
            due.toFixed(2);
    }



    /*
    |--------------------------------------------------------------------------
    | ITEM INPUT CHANGE
    |--------------------------------------------------------------------------
    */

    body?.addEventListener(
        'input',
        function (event) {

            if (
                event.target.classList.contains(
                    'purchase-qty-input'
                ) ||
                event.target.classList.contains(
                    'purchase-rate-input'
                ) ||
                event.target.classList.contains(
                    'purchase-gst-input'
                )
            ) {

                recalcPurchaseTotals();

            }

        }
    );



    taxTypeInput?.addEventListener(
        'change',
        recalcPurchaseTotals
    );



    [
        '#purchase-discount',
        '#purchase-round-off',
        '#purchase-paid'
    ].forEach(function (selector) {

        document
            .querySelector(selector)
            ?.addEventListener(
                'input',
                recalcPurchaseTotals
            );

    });



    /*
    |--------------------------------------------------------------------------
    | REMOVE ITEM
    |--------------------------------------------------------------------------
    */

    body?.addEventListener(
        'click',
        function (event) {

            if (
                !event.target.classList.contains(
                    'purchase-remove-row'
                )
            ) {
                return;
            }


            const rows =
                body.querySelectorAll(
                    '.purchase-item-row'
                );


            /*
             * At least one item row should remain.
             */

            if (rows.length <= 1) {

                alert(
                    'At least one purchase item is required.'
                );

                return;
            }


            event.target
                .closest('.purchase-item-row')
                ?.remove();


            recalcPurchaseTotals();

        }
    );



    /*
    |--------------------------------------------------------------------------
    | ADD NEW ITEM
    |--------------------------------------------------------------------------
    */

    let nextRowIndex =
        {{ count($oldItems) }};


    addBtn?.addEventListener(
        'click',
        function () {

            const index =
                nextRowIndex++;


            const template = `
                <tr
                    class="
                        purchase-item-row
                        bg-white
                        dark:bg-neutral-900
                    "
                >

                    <td class="px-3 py-2 w-[380px] min-w-[380px]">

                        <select
                            name="items[${index}][item_id]"
                            class="
                                w-full
                                min-w-[350px]
                                border
                                rounded
                                px-2
                                py-2
                                bg-white
                                text-gray-900
                                dark:bg-neutral-800
                                dark:text-white
                                dark:border-neutral-600
                            "
                            required
                        >

                            <option value="">
                                Select item...
                            </option>

                            @foreach ($items as $it)

                                <option value="{{ $it->id }}">
                                    {{ $it->name }}
                                    @if ($it->sku)
                                        ({{ $it->sku }})
                                    @endif
                                </option>

                            @endforeach

                        </select>

                    </td>


                    <td class="px-3 py-2">

                        <input
                            type="number"
                            min="0.001"
                            step="0.001"
                            name="items[${index}][qty]"
                            class="
                                w-20
                                border
                                rounded
                                px-2
                                py-2
                                purchase-qty-input
                                dark:bg-neutral-800
                                dark:border-neutral-600
                            "
                            value="1"
                            required
                        >

                    </td>


                    <td class="px-3 py-2">

                        <select
                            name="items[${index}][qty_unit]"
                            class="
                                w-28
                                border
                                rounded
                                px-2
                                py-2
                                bg-white
                                text-gray-900
                                dark:bg-neutral-800
                                dark:text-white
                                dark:border-neutral-600
                            "
                            required
                        >

                            <option value="pcs">Pcs</option>
                            <option value="gram">Gram</option>
                            <option value="kg">Kg</option>
                            <option value="carat">Carat</option>
                            <option value="pair">Pair</option>
                            <option value="set">Set</option>
                            <option value="dozen">Dozen</option>

                        </select>

                    </td>


                    <td class="px-3 py-2">

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="items[${index}][rate]"
                            class="
                                w-24
                                border
                                rounded
                                px-2
                                py-2
                                purchase-rate-input
                                dark:bg-neutral-800
                                dark:border-neutral-600
                            "
                            value="0"
                            required
                        >

                    </td>


                    <td class="px-3 py-2">

                        <input
                            type="number"
                            step="0.01"
                            name="items[${index}][amount]"
                            class="
                                w-28
                                border
                                rounded
                                px-2
                                py-2
                                purchase-amount-input
                                bg-gray-100
                                dark:bg-neutral-700
                                dark:border-neutral-600
                            "
                            value="0.00"
                            readonly
                        >

                    </td>


                    <td class="px-3 py-2">

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="items[${index}][gst_rate]"
                            class="
                                w-20
                                border
                                rounded
                                px-2
                                py-2
                                purchase-gst-input
                                dark:bg-neutral-800
                                dark:border-neutral-600
                            "
                            value="3"
                        >

                    </td>


                    <td class="px-3 py-2">

                        <input
                            type="number"
                            step="0.01"
                            name="items[${index}][cgst_amount]"
                            class="
                                w-24
                                border
                                rounded
                                px-2
                                py-2
                                purchase-cgst-input
                                bg-gray-100
                                dark:bg-neutral-700
                                dark:border-neutral-600
                            "
                            value="0.00"
                            readonly
                        >

                    </td>


                    <td class="px-3 py-2">

                        <input
                            type="number"
                            step="0.01"
                            name="items[${index}][sgst_amount]"
                            class="
                                w-24
                                border
                                rounded
                                px-2
                                py-2
                                purchase-sgst-input
                                bg-gray-100
                                dark:bg-neutral-700
                                dark:border-neutral-600
                            "
                            value="0.00"
                            readonly
                        >

                    </td>


                    <td class="px-3 py-2">

                        <input
                            type="number"
                            step="0.01"
                            name="items[${index}][igst_amount]"
                            class="
                                w-24
                                border
                                rounded
                                px-2
                                py-2
                                purchase-igst-input
                                bg-gray-100
                                dark:bg-neutral-700
                                dark:border-neutral-600
                            "
                            value="0.00"
                            readonly
                        >

                    </td>


                    <td class="px-3 py-2">

                        <input
                            type="number"
                            step="0.01"
                            name="items[${index}][total_amount]"
                            class="
                                w-28
                                border
                                rounded
                                px-2
                                py-2
                                purchase-line-total-input
                                bg-gray-100
                                dark:bg-neutral-700
                                dark:border-neutral-600
                            "
                            value="0.00"
                            readonly
                        >

                    </td>


                    <td class="px-3 py-2 text-right">

                        <button
                            type="button"
                            class="
                                text-red-600
                                hover:text-red-800
                                text-xs
                                font-medium
                                purchase-remove-row
                            "
                        >
                            Remove
                        </button>

                    </td>

                </tr>
            `;


            body.insertAdjacentHTML(
                'beforeend',
                template
            );


            recalcPurchaseTotals();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | INITIAL CALCULATION
    |--------------------------------------------------------------------------
    */

    recalcPurchaseTotals();

});
</script>