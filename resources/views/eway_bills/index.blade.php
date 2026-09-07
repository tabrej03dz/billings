<x-layouts.app :title="'Manage E-Way Bills'">

    <div class="max-w-7xl mx-auto px-4 py-6">

        {{-- HEADER --}}
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">

            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Manage E-Way Bills
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-neutral-400">
                    View, edit, print and delete saved E-Way Bills.
                </p>
            </div>

        </div>


        {{-- SUCCESS --}}
        @if(session('success'))

            <div class="mb-4 rounded-lg border border-gray-300 bg-white px-4 py-3
                        text-sm text-gray-800 shadow-sm
                        dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200">

                {{ session('success') }}

            </div>

        @endif


        {{-- ERROR --}}
        @if(session('error'))

            <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3
                        text-sm text-red-700 dark:border-red-800 dark:bg-red-950/20">

                {{ session('error') }}

            </div>

        @endif


        {{-- FILTERS --}}
        <form
            method="GET"
            action="{{ route('eway-bills.index') }}"
            class="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm
                   dark:border-neutral-700 dark:bg-neutral-900"
        >

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">


                {{-- SEARCH --}}
                <div class="lg:col-span-2">

                    <label
                        class="mb-1 block text-xs font-medium text-gray-600
                               dark:text-neutral-300"
                    >
                        Search
                    </label>

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="EWB no, invoice, customer, vehicle..."
                        class="w-full rounded-lg border-gray-300 text-sm
                               dark:border-neutral-700 dark:bg-neutral-800
                               dark:text-white"
                    >

                </div>


                {{-- STATUS --}}
                <div>

                    <label
                        class="mb-1 block text-xs font-medium text-gray-600
                               dark:text-neutral-300"
                    >
                        Status
                    </label>

                    <select
                        name="status"
                        class="w-full rounded-lg border-gray-300 text-sm
                               dark:border-neutral-700 dark:bg-neutral-800
                               dark:text-white"
                    >

                        <option value="">
                            All Status
                        </option>

                        <option
                            value="draft"
                            @selected(request('status') === 'draft')
                        >
                            Draft
                        </option>

                        <option
                            value="generated"
                            @selected(request('status') === 'generated')
                        >
                            Generated
                        </option>

                        <option
                            value="cancelled"
                            @selected(request('status') === 'cancelled')
                        >
                            Cancelled
                        </option>

                        <option
                            value="failed"
                            @selected(request('status') === 'failed')
                        >
                            Failed
                        </option>

                    </select>

                </div>


                {{-- FROM --}}
                <div>

                    <label
                        class="mb-1 block text-xs font-medium text-gray-600
                               dark:text-neutral-300"
                    >
                        From Date
                    </label>

                    <input
                        type="date"
                        name="from_date"
                        value="{{ request('from_date') }}"
                        class="w-full rounded-lg border-gray-300 text-sm
                               dark:border-neutral-700 dark:bg-neutral-800
                               dark:text-white"
                    >

                </div>


                {{-- TO --}}
                <div>

                    <label
                        class="mb-1 block text-xs font-medium text-gray-600
                               dark:text-neutral-300"
                    >
                        To Date
                    </label>

                    <input
                        type="date"
                        name="to_date"
                        value="{{ request('to_date') }}"
                        class="w-full rounded-lg border-gray-300 text-sm
                               dark:border-neutral-700 dark:bg-neutral-800
                               dark:text-white"
                    >

                </div>

            </div>


            <div class="mt-4 flex flex-wrap items-center justify-end gap-2">

                <a
                    href="{{ route('eway-bills.index') }}"
                    class="rounded-lg border border-gray-300 px-4 py-2
                           text-sm font-medium text-gray-700
                           hover:bg-gray-50
                           dark:border-neutral-700 dark:text-neutral-200
                           dark:hover:bg-neutral-800"
                >
                    Reset
                </a>


                <button
                    type="submit"
                    class="rounded-lg bg-gray-800 px-4 py-2
                           text-sm font-semibold text-white
                           hover:bg-gray-900"
                >
                    Apply Filters
                </button>

            </div>

        </form>


        {{-- TABLE --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm
                    dark:border-neutral-700 dark:bg-neutral-900">

            <div class="overflow-x-auto">

                <table class="min-w-full text-sm">

                    <thead
                        class="border-b border-gray-200 bg-gray-100
                               text-xs uppercase tracking-wide text-gray-600
                               dark:border-neutral-700 dark:bg-neutral-800
                               dark:text-neutral-300"
                    >

                    <tr>

                        <th class="px-4 py-3 text-left">
                            E-Way Bill
                        </th>

                        <th class="px-4 py-3 text-left">
                            Invoice
                        </th>

                        <th class="px-4 py-3 text-left">
                            Customer
                        </th>

                        <th class="px-4 py-3 text-left">
                            Vehicle
                        </th>

                        <th class="px-4 py-3 text-left">
                            Distance
                        </th>

                        <th class="px-4 py-3 text-left">
                            Status
                        </th>

                        <th class="px-4 py-3 text-left">
                            Created
                        </th>

                        <th class="px-4 py-3 text-right">
                            Actions
                        </th>

                    </tr>

                    </thead>


                    <tbody
                        class="divide-y divide-gray-100 dark:divide-neutral-800"
                    >

                    @forelse($ewayBills as $ewayBill)

                        @php
                            $invoice = $ewayBill->invoice;
                        @endphp


                        <tr
                            class="hover:bg-gray-50
                                   dark:hover:bg-neutral-800/40"
                        >

                            {{-- EWB --}}
                            <td class="px-4 py-3">

                                <div class="font-bold text-gray-900 dark:text-white">
                                    {{ $ewayBill->eway_bill_no ?: 'Not Assigned' }}
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    ID: {{ $ewayBill->id }}
                                </div>

                            </td>


                            {{-- INVOICE --}}
                            <td class="px-4 py-3">

                                <div class="font-semibold">
                                    {{ $invoice?->invoice_number ?: '-' }}
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $ewayBill->document_date
                                        ? $ewayBill->document_date->format('d M Y')
                                        : '-' }}
                                </div>

                            </td>


                            {{-- CUSTOMER --}}
                            <td class="px-4 py-3">

                                <div class="font-medium">
                                    {{ $ewayBill->to_name
                                        ?: $invoice?->client?->name
                                        ?: '-' }}
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $ewayBill->to_place ?: '-' }}
                                </div>

                            </td>


                            {{-- VEHICLE --}}
                            <td class="px-4 py-3">

                                <div class="font-semibold">
                                    {{ $ewayBill->vehicle_no ?: '-' }}
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $ewayBill->transport_mode ?: '-' }}
                                </div>

                            </td>


                            {{-- DISTANCE --}}
                            <td class="px-4 py-3 whitespace-nowrap">

                                @if($ewayBill->distance)
                                    {{ number_format($ewayBill->distance) }} KM
                                @else
                                    -
                                @endif

                            </td>


                            {{-- STATUS --}}
                            <td class="px-4 py-3">

                                @php

                                    $status =
                                        strtolower(
                                            $ewayBill->status
                                            ?? 'draft'
                                        );

                                    $statusClass =
                                        match($status) {

                                            'generated' =>
                                                'border-gray-300 text-gray-800 dark:border-neutral-600 dark:text-neutral-200',

                                            'cancelled' =>
                                                'border-red-300 text-red-700 dark:border-red-800 dark:text-red-300',

                                            'failed' =>
                                                'border-red-300 text-red-700 dark:border-red-800 dark:text-red-300',

                                            default =>
                                                'border-gray-300 text-gray-600 dark:border-neutral-600 dark:text-neutral-300',
                                        };

                                @endphp

                                <span
                                    class="inline-flex rounded-full border px-2.5 py-1
                                           text-[11px] font-bold uppercase
                                           {{ $statusClass }}"
                                >

                                    {{ $status }}

                                </span>

                            </td>


                            {{-- CREATED --}}
                            <td class="px-4 py-3 whitespace-nowrap">

                                <div>
                                    {{ optional($ewayBill->created_at)
                                        ->format('d M Y') }}
                                </div>

                                <div class="text-xs text-gray-500">
                                    {{ optional($ewayBill->created_at)
                                        ->format('h:i A') }}
                                </div>

                            </td>


                            {{-- ACTIONS --}}
                            {{-- <td class="px-4 py-3 text-right">

                                <div
                                    class="relative inline-block"
                                    x-data="{ open: false }"
                                >

                                    <button
                                        type="button"
                                        @click="open = !open"
                                        class="flex h-9 w-9 items-center justify-center
                                               rounded-lg border border-gray-300
                                               hover:bg-gray-50
                                               dark:border-neutral-700
                                               dark:hover:bg-neutral-800"
                                    >
                                        ⋮
                                    </button>


                                    <div
                                        x-cloak
                                        x-show="open"
                                        @click.outside="open = false"
                                        x-transition
                                        class="absolute right-0 z-50 mt-2 w-48
                                               overflow-hidden rounded-xl border
                                               border-gray-200 bg-white shadow-xl
                                               dark:border-neutral-700
                                               dark:bg-neutral-900"
                                    >

                                        {{-- SHOW --}}
                                        {{-- <a
                                            href="{{ route(
                                                'eway-bills.show',
                                                $ewayBill->id
                                            ) }}"
                                            class="block px-4 py-2.5 text-left text-sm
                                                   hover:bg-gray-50
                                                   dark:hover:bg-neutral-800"
                                        >
                                            View
                                        </a> --}}


                                        {{-- EDIT --}}
                                        {{-- <a
                                            href="{{ route(
                                                'eway-bills.edit',
                                                $ewayBill->id
                                            ) }}"
                                            class="block px-4 py-2.5 text-left text-sm
                                                   text-blue-600
                                                   hover:bg-gray-50
                                                   dark:text-blue-400
                                                   dark:hover:bg-neutral-800"
                                        >
                                            Edit
                                        </a> --}}


                                        {{-- PRINT --}}
                                        {{-- <a
                                            href="{{ route(
                                                'eway-bills.print',
                                                $ewayBill->id
                                            ) }}"
                                            target="_blank"
                                            class="block px-4 py-2.5 text-left text-sm
                                                   hover:bg-gray-50
                                                   dark:hover:bg-neutral-800"
                                        >
                                            Print
                                        </a> --}}


                                        {{-- <div
                                            class="border-t border-gray-100
                                                   dark:border-neutral-800"
                                        ></div> --}}


                                        {{-- DELETE --}}
                                        {{-- <form
                                            method="POST"
                                            action="{{ route(
                                                'eway-bills.destroy',
                                                $ewayBill->id
                                            ) }}"
                                            onsubmit="return confirm(
                                                'Are you sure you want to delete this E-Way Bill?'
                                            );"
                                        >

                                            @csrf
                                            @method('DELETE')


                                            <button
                                                type="submit"
                                                class="block w-full px-4 py-2.5
                                                       text-left text-sm text-red-600
                                                       hover:bg-red-50
                                                       dark:text-red-400
                                                       dark:hover:bg-red-950/20"
                                            >
                                                Delete
                                            </button>

                                        </form>

                                    </div>

                                </div> --}}

                            {{-- </td> --}} 

                            {{-- ACTIONS --}}
                            <td class="px-4 py-3">

                                <div class="flex flex-wrap items-center justify-end gap-2">

                                    {{-- VIEW --}}
                                    <a
                                        href="{{ route('eway-bills.show', $ewayBill->id) }}"
                                        class="inline-flex items-center justify-center
                                            rounded-lg border border-gray-300
                                            bg-white px-3 py-2
                                            text-xs font-semibold text-gray-700
                                            hover:bg-gray-50
                                            dark:border-neutral-700
                                            dark:bg-neutral-900
                                            dark:text-neutral-200
                                            dark:hover:bg-neutral-800"
                                    >
                                        View
                                    </a>


                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route('eway-bills.edit', $ewayBill->id) }}"
                                        class="inline-flex items-center justify-center
                                            rounded-lg border border-blue-300
                                            bg-white px-3 py-2
                                            text-xs font-semibold text-blue-600
                                            hover:bg-blue-50
                                            dark:border-blue-800
                                            dark:bg-neutral-900
                                            dark:text-blue-400
                                            dark:hover:bg-blue-950/20"
                                    >
                                        Edit
                                    </a>


                                    {{-- PRINT --}}
                                    <a
                                        href="{{ route('eway-bills.print', $ewayBill->id) }}"
                                        target="_blank"
                                        class="inline-flex items-center justify-center
                                            rounded-lg border border-gray-300
                                            bg-gray-800 px-3 py-2
                                            text-xs font-semibold text-white
                                            hover:bg-gray-900
                                            dark:border-neutral-700"
                                    >
                                        Print
                                    </a>


                                    {{-- DELETE --}}
                                    <form
                                        method="POST"
                                        action="{{ route('eway-bills.destroy', $ewayBill->id) }}"
                                        onsubmit="return confirm('Are you sure you want to delete this E-Way Bill?');"
                                        class="inline"
                                    >

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center
                                                rounded-lg border border-red-300
                                                bg-white px-3 py-2
                                                text-xs font-semibold text-red-600
                                                hover:bg-red-50
                                                dark:border-red-800
                                                dark:bg-neutral-900
                                                dark:text-red-400
                                                dark:hover:bg-red-950/20"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>
                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="px-4 py-12 text-center
                                       text-gray-500 dark:text-neutral-400"
                            >

                                No E-Way Bills found.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- PAGINATION --}}
        <div class="mt-5">

            {{ $ewayBills->links() }}

        </div>

    </div>

</x-layouts.app>