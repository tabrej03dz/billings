<x-layouts.app :title="__('Supplier Purchase Record')">

    <div class="flex flex-col gap-5">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <div
            class="
                rounded-2xl
                border
                border-emerald-100
                bg-[#BFE0E0]
                p-5
                shadow-sm

                dark:border-slate-700
                dark:bg-[#354A54]
            "
        >

            <div
                class="
                    flex
                    flex-col
                    gap-4

                    md:flex-row
                    md:items-center
                    md:justify-between
                "
            >

                <div>

                    <div
                        class="
                            flex
                            items-center
                            gap-3
                        "
                    >

                        <div
                            class="
                                flex
                                h-12
                                w-12
                                items-center
                                justify-center
                                rounded-full
                                bg-emerald-600
                                text-lg
                                font-bold
                                text-white
                            "
                        >
                            {{ Str::upper(
                                Str::substr(
                                    $supplier->name,
                                    0,
                                    1
                                )
                            ) }}
                        </div>


                        <div>

                            <h1
                                class="
                                    text-2xl
                                    font-bold
                                    text-slate-900

                                    dark:text-white
                                "
                            >
                                {{ $supplier->name }}
                            </h1>


                            <p
                                class="
                                    text-sm
                                    text-slate-600

                                    dark:text-slate-300
                                "
                            >
                                Supplier Purchase Record
                            </p>

                        </div>

                    </div>

                </div>


                <div
                    class="
                        flex
                        gap-2
                    "
                >

                    <a
                        href="{{ route(
                            'clients.index',
                            ['type' => 'supplier']
                        ) }}"
                        class="
                            rounded-xl
                            border
                            border-slate-300
                            bg-white
                            px-4
                            py-2
                            text-sm
                            font-semibold
                            text-slate-700
                            hover:bg-slate-50

                            dark:border-slate-600
                            dark:bg-slate-800
                            dark:text-white
                        "
                    >
                        ← Suppliers
                    </a>


                    <a
                        href="{{ route(
                            'clients.edit',
                            $supplier->id
                        ) }}"
                        class="
                            rounded-xl
                            bg-amber-500
                            px-4
                            py-2
                            text-sm
                            font-semibold
                            text-white
                            hover:bg-amber-600
                        "
                    >
                        Edit Supplier
                    </a>

                </div>

            </div>



            {{-- Supplier Details --}}
            <div
                class="
                    mt-5
                    grid
                    gap-3
                    text-sm

                    sm:grid-cols-2
                    lg:grid-cols-4
                "
            >

                <div
                    class="
                        rounded-xl
                        bg-white/60
                        p-3

                        dark:bg-slate-800/60
                    "
                >

                    <p class="text-xs text-slate-500">
                        Mobile
                    </p>

                    <p class="font-semibold">
                        {{ $supplier->mobile ?? '—' }}
                    </p>

                </div>


                <div
                    class="
                        rounded-xl
                        bg-white/60
                        p-3

                        dark:bg-slate-800/60
                    "
                >

                    <p class="text-xs text-slate-500">
                        GSTIN
                    </p>

                    <p class="font-semibold">
                        {{ $supplier->gstin ?? '—' }}
                    </p>

                </div>


                <div
                    class="
                        rounded-xl
                        bg-white/60
                        p-3

                        dark:bg-slate-800/60
                    "
                >

                    <p class="text-xs text-slate-500">
                        State
                    </p>

                    <p class="font-semibold">
                        {{ $supplier->state ?? '—' }}
                    </p>

                </div>


                <div
                    class="
                        rounded-xl
                        bg-white/60
                        p-3

                        dark:bg-slate-800/60
                    "
                >

                    <p class="text-xs text-slate-500">
                        Address
                    </p>

                    <p class="font-semibold">
                        {{ $supplier->address ?? '—' }}
                    </p>

                </div>

            </div>

        </div>



        {{-- ========================================================= --}}
        {{-- SUMMARY --}}
        {{-- ========================================================= --}}

        <div
            class="
                grid
                gap-4

                sm:grid-cols-2
                lg:grid-cols-4
            "
        >

            {{-- Purchase Count --}}
            <div
                class="
                    rounded-2xl
                    border
                    border-slate-200
                    bg-white
                    p-5
                    shadow-sm

                    dark:border-slate-700
                    dark:bg-neutral-900
                "
            >

                <p class="text-sm text-slate-500">
                    Total Purchases
                </p>

                <p
                    class="
                        mt-2
                        text-2xl
                        font-bold
                        text-slate-900

                        dark:text-white
                    "
                >
                    {{ number_format(
                        $summary['total_purchases']
                    ) }}
                </p>

            </div>



            {{-- Total --}}
            <div
                class="
                    rounded-2xl
                    border
                    border-blue-200
                    bg-blue-50
                    p-5
                    shadow-sm

                    dark:border-blue-900
                    dark:bg-blue-950/30
                "
            >

                <p class="text-sm text-blue-600">
                    Total Purchase Amount
                </p>

                <p
                    class="
                        mt-2
                        text-2xl
                        font-bold
                        text-blue-700

                        dark:text-blue-300
                    "
                >
                    ₹{{ number_format(
                        $summary['total_amount'],
                        2
                    ) }}
                </p>

            </div>



            {{-- Paid --}}
            <div
                class="
                    rounded-2xl
                    border
                    border-emerald-200
                    bg-emerald-50
                    p-5
                    shadow-sm

                    dark:border-emerald-900
                    dark:bg-emerald-950/30
                "
            >

                <p class="text-sm text-emerald-600">
                    Paid Amount
                </p>

                <p
                    class="
                        mt-2
                        text-2xl
                        font-bold
                        text-emerald-700

                        dark:text-emerald-300
                    "
                >
                    ₹{{ number_format(
                        $summary['paid_amount'],
                        2
                    ) }}
                </p>

            </div>



            {{-- Due --}}
            <div
                class="
                    rounded-2xl
                    border
                    border-red-200
                    bg-red-50
                    p-5
                    shadow-sm

                    dark:border-red-900
                    dark:bg-red-950/30
                "
            >

                <p class="text-sm text-red-600">
                    Due Amount
                </p>

                <p
                    class="
                        mt-2
                        text-2xl
                        font-bold
                        text-red-700

                        dark:text-red-300
                    "
                >
                    ₹{{ number_format(
                        $summary['due_amount'],
                        2
                    ) }}
                </p>

            </div>

        </div>



        {{-- ========================================================= --}}
        {{-- FILTER --}}
        {{-- ========================================================= --}}

        <div
            class="
                rounded-2xl
                border
                border-slate-200
                bg-white
                p-4
                shadow-sm

                dark:border-slate-700
                dark:bg-neutral-900
            "
        >

            <form
                method="GET"
                action="{{ route(
                    'clients.show',
                    $supplier->id
                ) }}"
                class="
                    grid
                    gap-3

                    md:grid-cols-4
                "
            >

                <input
                    type="hidden"
                    name="view"
                    value="supplier"
                >


                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-semibold
                            text-slate-500
                        "
                    >
                        Search Invoice
                    </label>


                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Invoice number..."
                        class="
                            w-full
                            rounded-lg
                            border
                            border-slate-300
                            px-3
                            py-2

                            dark:border-slate-600
                            dark:bg-slate-800
                        "
                    >

                </div>



                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-semibold
                            text-slate-500
                        "
                    >
                        From Date
                    </label>


                    <input
                        type="date"
                        name="date_from"
                        value="{{ request('date_from') }}"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-slate-300
                            px-3
                            py-2

                            dark:border-slate-600
                            dark:bg-slate-800
                        "
                    >

                </div>



                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-semibold
                            text-slate-500
                        "
                    >
                        To Date
                    </label>


                    <input
                        type="date"
                        name="date_to"
                        value="{{ request('date_to') }}"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-slate-300
                            px-3
                            py-2

                            dark:border-slate-600
                            dark:bg-slate-800
                        "
                    >

                </div>



                <div
                    class="
                        flex
                        items-end
                        gap-2
                    "
                >

                    <button
                        type="submit"
                        class="
                            rounded-lg
                            bg-blue-600
                            px-4
                            py-2
                            text-sm
                            font-semibold
                            text-white
                            hover:bg-blue-700
                        "
                    >
                        Filter
                    </button>


                    <a
                        href="{{ route(
                            'clients.show',
                            [
                                'client' => $supplier->id,
                                'view' => 'supplier'
                            ]
                        ) }}"
                        class="
                            rounded-lg
                            border
                            border-slate-300
                            px-4
                            py-2
                            text-sm
                            font-semibold
                        "
                    >
                        Clear
                    </a>

                </div>

            </form>

        </div>



        {{-- ========================================================= --}}
        {{-- PURCHASES TABLE --}}
        {{-- ========================================================= --}}

        <div
            class="
                overflow-hidden
                rounded-2xl
                border
                border-slate-200
                bg-white
                shadow-sm

                dark:border-slate-700
                dark:bg-neutral-900
            "
        >

            <div
                class="
                    border-b
                    border-slate-200
                    px-5
                    py-4

                    dark:border-slate-700
                "
            >

                <h2
                    class="
                        text-lg
                        font-bold
                        text-slate-900

                        dark:text-white
                    "
                >
                    Purchase History
                </h2>

            </div>


            <div class="overflow-x-auto">

                <table
                    class="
                        min-w-[900px]
                        w-full
                        text-left
                        text-sm
                    "
                >

                    <thead
                        class="
                            bg-[#BFE0E0]
                            text-xs
                            uppercase
                            text-slate-700

                            dark:bg-[#354A54]
                            dark:text-slate-200
                        "
                    >

                        <tr>

                            <th class="px-5 py-4">
                                Date
                            </th>

                            <th class="px-5 py-4">
                                Invoice #
                            </th>

                            <th class="px-5 py-4 text-right">
                                Total
                            </th>

                            <th class="px-5 py-4 text-right">
                                Paid
                            </th>

                            <th class="px-5 py-4 text-right">
                                Due
                            </th>

                            <th class="px-5 py-4 text-right">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody
                        class="
                            divide-y
                            divide-slate-100

                            dark:divide-slate-800
                        "
                    >

                        @forelse($purchases as $purchase)

                            <tr
                                class="
                                    hover:bg-slate-50

                                    dark:hover:bg-slate-800/40
                                "
                            >

                                <td class="px-5 py-4">

                                    {{ $purchase->invoice_date
                                        ? \Carbon\Carbon::parse(
                                            $purchase->invoice_date
                                        )->format('d-m-Y')
                                        : '—'
                                    }}

                                </td>


                                <td
                                    class="
                                        px-5
                                        py-4
                                        font-semibold
                                    "
                                >
                                    {{ $purchase->invoice_no ?: '—' }}
                                </td>


                                <td
                                    class="
                                        px-5
                                        py-4
                                        text-right
                                        font-semibold
                                    "
                                >
                                    ₹{{ number_format(
                                        $purchase->total_amount,
                                        2
                                    ) }}
                                </td>


                                <td
                                    class="
                                        px-5
                                        py-4
                                        text-right
                                        text-emerald-600
                                        font-semibold
                                    "
                                >
                                    ₹{{ number_format(
                                        $purchase->paid_amount,
                                        2
                                    ) }}
                                </td>


                                <td
                                    class="
                                        px-5
                                        py-4
                                        text-right
                                        text-red-600
                                        font-semibold
                                    "
                                >
                                    ₹{{ number_format(
                                        $purchase->due_amount,
                                        2
                                    ) }}
                                </td>


                                <td class="px-5 py-4">

                                    <div
                                        class="
                                            flex
                                            justify-end
                                            gap-2
                                        "
                                    >

                                        <a
                                            href="{{ route(
                                                'purchases.show',
                                                $purchase->id
                                            ) }}"
                                            class="
                                                rounded-lg
                                                bg-blue-50
                                                px-3
                                                py-2
                                                text-xs
                                                font-semibold
                                                text-blue-700
                                                hover:bg-blue-100
                                            "
                                        >
                                            View
                                        </a>


                                        <a
                                            href="{{ route(
                                                'purchases.edit',
                                                $purchase->id
                                            ) }}"
                                            class="
                                                rounded-lg
                                                bg-amber-50
                                                px-3
                                                py-2
                                                text-xs
                                                font-semibold
                                                text-amber-700
                                                hover:bg-amber-100
                                            "
                                        >
                                            Edit
                                        </a>

                                    </div>

                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="
                                        px-6
                                        py-12
                                        text-center
                                        text-slate-500
                                    "
                                >

                                    <p
                                        class="
                                            text-base
                                            font-semibold
                                            text-slate-700

                                            dark:text-slate-300
                                        "
                                    >
                                        No purchase records found.
                                    </p>


                                    <p class="mt-1 text-sm">
                                        Purchases created with this supplier
                                        will appear here.
                                    </p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>



        {{-- Pagination --}}
        @if($purchases->hasPages())

            <div>
                {{ $purchases->links() }}
            </div>

        @endif
    </div>
</x-layouts.app>