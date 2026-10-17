<x-layouts.app :title="__('Purchases')">

    <div class="min-h-screen bg-slate-50 dark:bg-neutral-950">

        {{-- ============================================================= --}}
        {{-- HEADER --}}
        {{-- ============================================================= --}}
        <div
            class="mb-6 rounded-2xl border border-slate-200 bg-white
                   p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900
                   sm:p-6"
        >
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                <div>
                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-11 w-11 items-center justify-center
                                   rounded-xl bg-emerald-50 text-emerald-600
                                   dark:bg-emerald-500/10 dark:text-emerald-400"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                                class="h-6 w-6"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3.75 7.5h16.5M6 3.75h12a2.25 2.25 0 012.25 2.25v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M8.25 12h7.5M8.25 15.75h4.5"
                                />
                            </svg>
                        </div>

                        <div>
                            <h1
                                class="text-xl font-bold tracking-tight text-slate-900
                                       dark:text-white sm:text-2xl"
                            >
                                Purchases
                            </h1>

                            <p class="mt-1 text-sm text-slate-500 dark:text-neutral-400">
                                Manage purchase bills, suppliers, payments and dues
                            </p>
                        </div>

                    </div>
                </div>

                <a
                    href="{{ route('purchases.create') }}"
                    class="inline-flex items-center justify-center gap-2
                           rounded-xl bg-emerald-600 px-4 py-2.5
                           text-sm font-semibold text-white shadow-sm
                           transition hover:bg-emerald-700
                           focus:outline-none focus:ring-2 focus:ring-emerald-500
                           focus:ring-offset-2 dark:focus:ring-offset-neutral-900"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 4.5v15m7.5-7.5h-15"
                        />
                    </svg>

                    New Purchase
                </a>

            </div>
        </div>


        {{-- ============================================================= --}}
        {{-- SUCCESS MESSAGE --}}
        {{-- ============================================================= --}}
        @if(session('success'))

            <div
                class="mb-5 flex items-start gap-3 rounded-xl border
                       border-emerald-200 bg-emerald-50 px-4 py-3
                       text-sm text-emerald-800
                       dark:border-emerald-900/50
                       dark:bg-emerald-950/40
                       dark:text-emerald-300"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    class="mt-0.5 h-5 w-5 shrink-0"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 12.75l2.25 2.25L15 9.75m6 2.25a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>

                <span>
                    {{ session('success') }}
                </span>
            </div>

        @endif


        {{-- ============================================================= --}}
        {{-- SUMMARY CARDS --}}
        {{-- ============================================================= --}}
        <div
            class="mb-6 grid grid-cols-1 gap-4
                   sm:grid-cols-2 xl:grid-cols-4"
        >

            {{-- Total Purchases --}}
            <div
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="flex items-center justify-between">

                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wider
                                   text-slate-500 dark:text-neutral-400"
                        >
                            Total Purchases
                        </p>

                        <p
                            class="mt-2 text-2xl font-bold text-slate-900
                                   dark:text-white"
                        >
                            {{ number_format($summary['total_purchases'] ?? 0) }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center
                               rounded-xl bg-blue-50 text-blue-600
                               dark:bg-blue-500/10 dark:text-blue-400"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            class="h-6 w-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M6.75 3.75h10.5A2.25 2.25 0 0119.5 6v12a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 18V6a2.25 2.25 0 012.25-2.25z"
                            />
                        </svg>
                    </div>

                </div>
            </div>


            {{-- Total Amount --}}
            <div
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="flex items-center justify-between">

                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wider
                                   text-slate-500 dark:text-neutral-400"
                        >
                            Purchase Amount
                        </p>

                        <p
                            class="mt-2 text-2xl font-bold text-slate-900
                                   dark:text-white"
                        >
                            ₹{{ number_format($summary['total_amount'] ?? 0, 2) }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center
                               rounded-xl bg-violet-50 text-violet-600
                               dark:bg-violet-500/10 dark:text-violet-400"
                    >
                        <span class="text-xl font-bold">₹</span>
                    </div>

                </div>
            </div>


            {{-- Paid --}}
            <div
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="flex items-center justify-between">

                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wider
                                   text-slate-500 dark:text-neutral-400"
                        >
                            Total Paid
                        </p>

                        <p
                            class="mt-2 text-2xl font-bold text-emerald-600
                                   dark:text-emerald-400"
                        >
                            ₹{{ number_format($summary['paid_amount'] ?? 0, 2) }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center
                               rounded-xl bg-emerald-50 text-emerald-600
                               dark:bg-emerald-500/10 dark:text-emerald-400"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            class="h-6 w-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4.5 12.75l6 6 9-13.5"
                            />
                        </svg>
                    </div>

                </div>
            </div>


            {{-- Due --}}
            <div
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="flex items-center justify-between">

                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wider
                                   text-slate-500 dark:text-neutral-400"
                        >
                            Total Due
                        </p>

                        <p
                            class="mt-2 text-2xl font-bold text-rose-600
                                   dark:text-rose-400"
                        >
                            ₹{{ number_format($summary['due_amount'] ?? 0, 2) }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center
                               rounded-xl bg-rose-50 text-rose-600
                               dark:bg-rose-500/10 dark:text-rose-400"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            class="h-6 w-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 6v6l4 2"
                            />

                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                            />
                        </svg>
                    </div>

                </div>
            </div>

        </div>


        {{-- ============================================================= --}}
        {{-- TABLE --}}
        {{-- ============================================================= --}}
        <div
            class="overflow-hidden rounded-2xl border
                   border-slate-200 bg-white shadow-sm
                   dark:border-neutral-800 dark:bg-neutral-900"
        >

            {{-- Table Heading --}}
            <div
                class="flex flex-col gap-2 border-b border-slate-200
                       px-5 py-4 dark:border-neutral-800
                       sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="font-semibold text-slate-900
                               dark:text-white"
                    >
                        Purchase History
                    </h2>

                    <p
                        class="mt-0.5 text-xs text-slate-500
                               dark:text-neutral-400"
                    >
                        All supplier purchase transactions
                    </p>
                </div>

                <div
                    class="text-xs font-medium text-slate-500
                           dark:text-neutral-400"
                >
                    Showing {{ $purchases->firstItem() ?? 0 }}
                    -
                    {{ $purchases->lastItem() ?? 0 }}
                    of {{ $purchases->total() }}
                </div>
            </div>


            {{-- Scrollable Table --}}
            <div class="overflow-x-auto">

                <table class="min-w-[1450px] w-full text-sm">

                    <thead
                        class="bg-slate-50 text-xs uppercase
                               tracking-wide text-slate-500
                               dark:bg-neutral-800/70
                               dark:text-neutral-400"
                    >
                        <tr>

                            <th class="whitespace-nowrap px-5 py-3 text-left font-semibold">
                                Invoice
                            </th>

                            <th class="whitespace-nowrap px-5 py-3 text-left font-semibold">
                                Date
                            </th>

                            <th class="whitespace-nowrap px-5 py-3 text-left font-semibold">
                                Supplier
                            </th>

                            <th class="whitespace-nowrap px-5 py-3 text-center font-semibold">
                                Items
                            </th>

                            <th class="whitespace-nowrap px-5 py-3 text-left font-semibold">
                                Tax
                            </th>

                            <th class="whitespace-nowrap px-5 py-3 text-right font-semibold">
                                Subtotal
                            </th>

                            <th class="whitespace-nowrap px-5 py-3 text-right font-semibold">
                                GST
                            </th>

                            <th class="whitespace-nowrap px-5 py-3 text-right font-semibold">
                                Total
                            </th>

                            <th class="whitespace-nowrap px-5 py-3 text-right font-semibold">
                                Paid
                            </th>

                            <th class="whitespace-nowrap px-5 py-3 text-right font-semibold">
                                Due
                            </th>

                            <th class="whitespace-nowrap px-5 py-3 text-center font-semibold">
                                Status
                            </th>

                            <th class="whitespace-nowrap px-5 py-3 text-center font-semibold">
                                Bill
                            </th>

                            <th class="whitespace-nowrap px-5 py-3 text-right font-semibold">
                                Actions
                            </th>

                        </tr>
                    </thead>


                    <tbody
                        class="divide-y divide-slate-100
                               dark:divide-neutral-800"
                    >

                    @forelse($purchases as $p)

                        @php
                            $gstAmount =
                                (float) ($p->cgst_amount ?? 0)
                                + (float) ($p->sgst_amount ?? 0)
                                + (float) ($p->igst_amount ?? 0);

                            $isPaid = (float) $p->due_amount <= 0;

                            $isPartial =
                                (float) $p->paid_amount > 0
                                && (float) $p->due_amount > 0;
                        @endphp


                        <tr
                            class="transition
                                   hover:bg-slate-50/80
                                   dark:hover:bg-neutral-800/40"
                        >

                            {{-- Invoice --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                <div
                                    class="font-semibold text-slate-900
                                           dark:text-white"
                                >
                                    {{ $p->invoice_no ?: 'PUR-' . $p->id }}
                                </div>

                                <div
                                    class="mt-1 text-xs text-slate-400
                                           dark:text-neutral-500"
                                >
                                    ID #{{ $p->id }}
                                </div>

                            </td>


                            {{-- Date --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                <div
                                    class="font-medium text-slate-700
                                           dark:text-neutral-200"
                                >
                                    {{ $p->invoice_date
                                        ? \Carbon\Carbon::parse($p->invoice_date)->format('d M Y')
                                        : '-' }}
                                </div>

                                @if($p->invoice_date)

                                    <div
                                        class="mt-1 text-xs text-slate-400
                                               dark:text-neutral-500"
                                    >
                                        {{ \Carbon\Carbon::parse($p->invoice_date)->format('D') }}
                                    </div>

                                @endif

                            </td>


                            {{-- Supplier --}}
                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center
                                               justify-center rounded-full
                                               bg-indigo-50 text-xs font-bold
                                               uppercase text-indigo-600
                                               dark:bg-indigo-500/10
                                               dark:text-indigo-400"
                                    >
                                        {{ strtoupper(
                                            substr($p->supplier->name ?? 'NA', 0, 2)
                                        ) }}
                                    </div>

                                    <div>

                                        <div
                                            class="max-w-[180px] truncate
                                                   font-medium text-slate-800
                                                   dark:text-neutral-200"
                                        >
                                            {{ $p->supplier->name ?? 'Walk-in Supplier' }}
                                        </div>

                                        @if(!empty($p->supplier?->mobile))

                                            <div
                                                class="mt-1 text-xs text-slate-400
                                                       dark:text-neutral-500"
                                            >
                                                {{ $p->supplier->mobile }}
                                            </div>

                                        @endif

                                    </div>

                                </div>

                            </td>


                            {{-- Items --}}
                            <td class="whitespace-nowrap px-5 py-4 text-center">

                                <span
                                    class="inline-flex min-w-8 items-center
                                           justify-center rounded-lg
                                           bg-slate-100 px-2.5 py-1
                                           text-xs font-semibold text-slate-700
                                           dark:bg-neutral-800
                                           dark:text-neutral-300"
                                >
                                    {{ $p->items_count ?? 0 }}
                                </span>

                            </td>


                            {{-- Tax --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                @if($p->tax_type === 'inter_state')

                                    <span
                                        class="inline-flex rounded-full
                                               bg-purple-50 px-2.5 py-1
                                               text-xs font-semibold
                                               text-purple-700
                                               dark:bg-purple-500/10
                                               dark:text-purple-400"
                                    >
                                        IGST
                                    </span>

                                @else

                                    <span
                                        class="inline-flex rounded-full
                                               bg-cyan-50 px-2.5 py-1
                                               text-xs font-semibold
                                               text-cyan-700
                                               dark:bg-cyan-500/10
                                               dark:text-cyan-400"
                                    >
                                        CGST + SGST
                                    </span>

                                @endif

                            </td>


                            {{-- Subtotal --}}
                            <td
                                class="whitespace-nowrap px-5 py-4
                                       text-right font-medium text-slate-700
                                       dark:text-neutral-300"
                            >
                                ₹{{ number_format($p->subtotal ?? 0, 2) }}
                            </td>


                            {{-- GST --}}
                            <td
                                class="whitespace-nowrap px-5 py-4
                                       text-right text-slate-600
                                       dark:text-neutral-400"
                            >
                                ₹{{ number_format($gstAmount, 2) }}
                            </td>


                            {{-- Total --}}
                            <td
                                class="whitespace-nowrap px-5 py-4
                                       text-right font-bold text-slate-900
                                       dark:text-white"
                            >
                                ₹{{ number_format($p->total_amount ?? 0, 2) }}
                            </td>


                            {{-- Paid --}}
                            <td
                                class="whitespace-nowrap px-5 py-4
                                       text-right font-semibold text-emerald-600
                                       dark:text-emerald-400"
                            >
                                ₹{{ number_format($p->paid_amount ?? 0, 2) }}
                            </td>


                            {{-- Due --}}
                            <td
                                class="whitespace-nowrap px-5 py-4
                                       text-right"
                            >

                                @if((float) $p->due_amount > 0)

                                    <span
                                        class="font-bold text-rose-600
                                               dark:text-rose-400"
                                    >
                                        ₹{{ number_format($p->due_amount, 2) }}
                                    </span>

                                @else

                                    <span
                                        class="font-semibold text-slate-400
                                               dark:text-neutral-500"
                                    >
                                        ₹0.00
                                    </span>

                                @endif

                            </td>


                            {{-- Payment Status --}}
                            <td class="whitespace-nowrap px-5 py-4 text-center">

                                @if($isPaid)

                                    <span
                                        class="inline-flex items-center gap-1.5
                                               rounded-full bg-emerald-50
                                               px-2.5 py-1 text-xs font-semibold
                                               text-emerald-700
                                               dark:bg-emerald-500/10
                                               dark:text-emerald-400"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full
                                                   bg-emerald-500"
                                        ></span>

                                        Paid
                                    </span>

                                @elseif($isPartial)

                                    <span
                                        class="inline-flex items-center gap-1.5
                                               rounded-full bg-amber-50
                                               px-2.5 py-1 text-xs font-semibold
                                               text-amber-700
                                               dark:bg-amber-500/10
                                               dark:text-amber-400"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full
                                                   bg-amber-500"
                                        ></span>

                                        Partial
                                    </span>

                                @else

                                    <span
                                        class="inline-flex items-center gap-1.5
                                               rounded-full bg-rose-50
                                               px-2.5 py-1 text-xs font-semibold
                                               text-rose-700
                                               dark:bg-rose-500/10
                                               dark:text-rose-400"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full
                                                   bg-rose-500"
                                        ></span>

                                        Unpaid
                                    </span>

                                @endif

                            </td>


                            {{-- Bill --}}
                            <td class="whitespace-nowrap px-5 py-4 text-center">

                                @if($p->bill_file)

                                    <a
                                        href="{{ asset('storage/' . $p->bill_file) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5
                                               rounded-lg border border-slate-200
                                               bg-white px-2.5 py-1.5
                                               text-xs font-semibold
                                               text-slate-700
                                               transition hover:bg-slate-50
                                               dark:border-neutral-700
                                               dark:bg-neutral-800
                                               dark:text-neutral-300
                                               dark:hover:bg-neutral-700"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.8"
                                            stroke="currentColor"
                                            class="h-4 w-4"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M15.75 17.25v2.25A2.25 2.25 0 0113.5 21.75h-9A2.25 2.25 0 012.25 19.5v-9A2.25 2.25 0 014.5 8.25h2.25"
                                            />

                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M15 2.25h6.75V9m0-6.75L10.5 13.5"
                                            />
                                        </svg>

                                        Open
                                    </a>

                                @else

                                    <span
                                        class="text-xs text-slate-400
                                               dark:text-neutral-600"
                                    >
                                        No file
                                    </span>

                                @endif

                            </td>


                            {{-- Actions --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                <div
                                    class="flex items-center justify-end gap-2"
                                >

                                    {{-- View --}}
                                    <a
                                        href="{{ route('purchases.show', $p->id) }}"
                                        title="View Purchase"
                                        class="inline-flex h-9 w-9 items-center
                                               justify-center rounded-lg
                                               border border-slate-200
                                               text-slate-600 transition
                                               hover:border-blue-200
                                               hover:bg-blue-50
                                               hover:text-blue-600
                                               dark:border-neutral-700
                                               dark:text-neutral-300
                                               dark:hover:border-blue-900
                                               dark:hover:bg-blue-500/10
                                               dark:hover:text-blue-400"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.8"
                                            stroke="currentColor"
                                            class="h-4.5 w-4.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12z"
                                            />

                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="2.25"
                                            />
                                        </svg>
                                    </a>


                                    {{-- Edit --}}
                                    <a
                                        href="{{ route('purchases.edit', $p->id) }}"
                                        title="Edit Purchase"
                                        class="inline-flex h-9 w-9 items-center
                                               justify-center rounded-lg
                                               border border-slate-200
                                               text-slate-600 transition
                                               hover:border-amber-200
                                               hover:bg-amber-50
                                               hover:text-amber-600
                                               dark:border-neutral-700
                                               dark:text-neutral-300
                                               dark:hover:border-amber-900
                                               dark:hover:bg-amber-500/10
                                               dark:hover:text-amber-400"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.8"
                                            stroke="currentColor"
                                            class="h-4.5 w-4.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M16.862 3.487a2.25 2.25 0 013.182 3.182L8.25 18.462l-4.5 1.125 1.125-4.5L16.862 3.487z"
                                            />
                                        </svg>
                                    </a>


                                    {{-- Delete --}}
                                    <form
                                        action="{{ route('purchases.destroy', $p->id) }}"
                                        method="POST"
                                        class="inline-flex"
                                        onsubmit="return confirm(
                                            'Are you sure you want to delete this purchase? Stock will also be reverted.'
                                        )"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            title="Delete Purchase"
                                            class="inline-flex h-9 w-9 items-center
                                                   justify-center rounded-lg
                                                   border border-slate-200
                                                   text-slate-600 transition
                                                   hover:border-rose-200
                                                   hover:bg-rose-50
                                                   hover:text-rose-600
                                                   dark:border-neutral-700
                                                   dark:text-neutral-300
                                                   dark:hover:border-rose-900
                                                   dark:hover:bg-rose-500/10
                                                   dark:hover:text-rose-400"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke-width="1.8"
                                                stroke="currentColor"
                                                class="h-4.5 w-4.5"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M6 7.5h12m-10.5 0l.75 11.25h7.5L16.5 7.5M9.75 7.5V4.875A1.125 1.125 0 0110.875 3.75h2.25a1.125 1.125 0 011.125 1.125V7.5"
                                                />
                                            </svg>
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="13" class="px-6 py-16">

                                <div
                                    class="mx-auto flex max-w-sm
                                           flex-col items-center text-center"
                                >

                                    <div
                                        class="flex h-14 w-14 items-center
                                               justify-center rounded-2xl
                                               bg-slate-100 text-slate-400
                                               dark:bg-neutral-800
                                               dark:text-neutral-500"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="h-7 w-7"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M6.75 3.75h10.5A2.25 2.25 0 0119.5 6v12a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 18V6a2.25 2.25 0 012.25-2.25z"
                                            />
                                        </svg>
                                    </div>

                                    <h3
                                        class="mt-4 font-semibold
                                               text-slate-800
                                               dark:text-neutral-200"
                                    >
                                        No purchases found
                                    </h3>

                                    <p
                                        class="mt-1 text-sm
                                               text-slate-500
                                               dark:text-neutral-500"
                                    >
                                        Your purchase transactions will appear here.
                                    </p>

                                    <a
                                        href="{{ route('purchases.create') }}"
                                        class="mt-5 inline-flex items-center
                                               rounded-lg bg-emerald-600
                                               px-4 py-2 text-sm
                                               font-semibold text-white
                                               hover:bg-emerald-700"
                                    >
                                        + Create Purchase
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- ============================================================= --}}
        {{-- PAGINATION --}}
        {{-- ============================================================= --}}
        @if($purchases->hasPages())

            <div class="mt-5">

                {{ $purchases->links() }}

            </div>

        @endif

    </div>

</x-layouts.app>