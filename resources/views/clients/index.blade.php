<x-layouts.app :title="__('Clients & Suppliers')">

    @php

        $type =
            $type ?? 'client';

        $isSupplier =
            $type === 'supplier';

        $currentTotal =
            $isSupplier
                ? $totalSuppliers
                : $totalClients;

        $singularLabel =
            $isSupplier
                ? 'Supplier'
                : 'Client';

        $pluralLabel =
            $isSupplier
                ? 'Suppliers'
                : 'Clients';

    @endphp


    <div class="flex flex-col gap-5">


        {{-- ========================================================= --}}
        {{-- SUCCESS MESSAGE --}}
        {{-- ========================================================= --}}

        @if(session('success'))

            <div
                class="
                    flex
                    items-start
                    gap-3
                    rounded-2xl
                    border
                    border-emerald-200
                    bg-emerald-50
                    p-4
                    text-emerald-800
                    shadow-sm

                    dark:border-emerald-900/70
                    dark:bg-emerald-950/40
                    dark:text-emerald-300
                "
            >

                <div
                    class="
                        flex
                        h-9
                        w-9
                        shrink-0
                        items-center
                        justify-center
                        rounded-full
                        bg-emerald-100

                        dark:bg-emerald-900/60
                    "
                >

                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>

                </div>


                <div>

                    <p class="font-semibold">
                        Success
                    </p>

                    <p class="mt-0.5 text-sm">
                        {{ session('success') }}
                    </p>

                </div>

            </div>

        @endif



        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <div
            class="
                rounded-2xl
                border
                border-cyan-100
                bg-[#BFE0E0]
                p-4
                shadow-sm

                dark:border-slate-700
                dark:bg-[#354A54]

                sm:p-6
            "
        >

            <div
                class="
                    flex
                    flex-col
                    gap-4

                    xl:flex-row
                    xl:items-center
                    xl:justify-between
                "
            >

                {{-- Title --}}
                <div>

                    <div
                        class="
                            flex
                            flex-wrap
                            items-center
                            gap-3
                        "
                    >

                        <h1
                            class="
                                text-2xl
                                font-bold
                                text-slate-900
                                dark:text-white
                            "
                        >
                            {{ $pluralLabel }}
                        </h1>


                        <span
                            class="
                                inline-flex
                                items-center
                                rounded-full
                                bg-white/70
                                px-3
                                py-1
                                text-xs
                                font-semibold
                                text-slate-700
                                shadow-sm

                                dark:bg-slate-800/70
                                dark:text-slate-200
                            "
                        >

                            {{ number_format($currentTotal) }}

                            {{ Str::plural(
                                $singularLabel,
                                $currentTotal
                            ) }}

                        </span>

                    </div>


                    <p
                        class="
                            mt-1
                            text-sm
                            text-slate-600
                            dark:text-slate-300
                        "
                    >

                        @if($isSupplier)

                            Manage suppliers, GST details,
                            purchase history and outstanding dues.

                        @else

                            Manage customers, GST details,
                            invoices and billing records.

                        @endif

                    </p>

                </div>



                {{-- Search + Create --}}
                <div
                    class="
                        flex
                        flex-col
                        gap-3

                        lg:flex-row
                        lg:items-center
                    "
                >

                    {{-- Search --}}
                    <form
                        method="GET"
                        action="{{ route('clients.index') }}"
                        class="
                            flex
                            w-full
                            flex-col
                            gap-2

                            sm:flex-row
                            lg:w-auto
                        "
                    >

                        <input
                            type="hidden"
                            name="type"
                            value="{{ $type }}"
                        >


                        <div
                            class="
                                relative
                                w-full
                                sm:w-80
                            "
                        >

                            <div
                                class="
                                    pointer-events-none
                                    absolute
                                    inset-y-0
                                    left-0
                                    flex
                                    items-center
                                    pl-3
                                    text-slate-400
                                "
                            >

                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
                                    />
                                </svg>

                            </div>


                            <input
                                type="text"
                                name="q"
                                value="{{ $q }}"
                                placeholder="Search name, mobile, GSTIN, PAN..."
                                class="
                                    block
                                    w-full
                                    rounded-xl
                                    border
                                    border-white/70
                                    bg-white
                                    py-2.5
                                    pl-10
                                    pr-4
                                    text-sm
                                    text-slate-900
                                    shadow-sm
                                    outline-none
                                    transition

                                    placeholder:text-slate-400

                                    focus:border-cyan-500
                                    focus:ring-2
                                    focus:ring-cyan-500/20

                                    dark:border-slate-600
                                    dark:bg-slate-800
                                    dark:text-white
                                "
                            >

                        </div>


                        <button
                            type="submit"
                            class="
                                inline-flex
                                items-center
                                justify-center
                                rounded-xl
                                bg-slate-700
                                px-4
                                py-2.5
                                text-sm
                                font-semibold
                                text-white
                                shadow-sm
                                transition
                                hover:bg-slate-800
                            "
                        >
                            Search
                        </button>


                        @if($q !== '')

                            <a
                                href="{{ route(
                                    'clients.index',
                                    ['type' => $type]
                                ) }}"
                                class="
                                    inline-flex
                                    items-center
                                    justify-center
                                    rounded-xl
                                    border
                                    border-slate-300
                                    bg-white/70
                                    px-4
                                    py-2.5
                                    text-sm
                                    font-semibold
                                    text-slate-700
                                    transition
                                    hover:bg-white

                                    dark:border-slate-600
                                    dark:bg-slate-800/70
                                    dark:text-slate-200
                                "
                            >
                                Clear
                            </a>

                        @endif

                    </form>



                    {{-- Add --}}
                    <a
                        href="{{ route(
                            'clients.create',
                            ['type' => $type]
                        ) }}"
                        class="
                            inline-flex
                            items-center
                            justify-center
                            gap-2
                            rounded-xl
                            px-4
                            py-2.5
                            text-sm
                            font-semibold
                            text-white
                            shadow-sm
                            transition

                            {{ $isSupplier
                                ? 'bg-emerald-600 hover:bg-emerald-700'
                                : 'bg-blue-600 hover:bg-blue-700'
                            }}
                        "
                    >

                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>


                        New {{ $singularLabel }}

                    </a>

                </div>

            </div>

        </div>



        {{-- ========================================================= --}}
        {{-- CLIENT / SUPPLIER TABS --}}
        {{-- ========================================================= --}}

        <div
            class="
                grid
                grid-cols-2
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

            {{-- Clients --}}
            <a
                href="{{ route(
                    'clients.index',
                    ['type' => 'client']
                ) }}"
                class="
                    flex
                    items-center
                    justify-center
                    gap-3
                    px-5
                    py-4
                    text-sm
                    font-bold
                    transition

                    {{ !$isSupplier
                        ? 'bg-blue-600 text-white'
                        : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'
                    }}
                "
            >

                Clients


                <span
                    class="
                        rounded-full
                        px-2.5
                        py-1
                        text-xs

                        {{ !$isSupplier
                            ? 'bg-white/20 text-white'
                            : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'
                        }}
                    "
                >
                    {{ number_format($totalClients) }}
                </span>

            </a>



            {{-- Suppliers --}}
            <a
                href="{{ route(
                    'clients.index',
                    ['type' => 'supplier']
                ) }}"
                class="
                    flex
                    items-center
                    justify-center
                    gap-3
                    px-5
                    py-4
                    text-sm
                    font-bold
                    transition

                    {{ $isSupplier
                        ? 'bg-emerald-600 text-white'
                        : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'
                    }}
                "
            >

                Suppliers


                <span
                    class="
                        rounded-full
                        px-2.5
                        py-1
                        text-xs

                        {{ $isSupplier
                            ? 'bg-white/20 text-white'
                            : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                        }}
                    "
                >
                    {{ number_format($totalSuppliers) }}
                </span>

            </a>

        </div>



        {{-- ========================================================= --}}
        {{-- CLIENT GUIDE --}}
        {{-- ========================================================= --}}

        @if(!$isSupplier && $showClientSuggestion)

            <div
                class="
                    rounded-2xl
                    border
                    border-indigo-200
                    bg-indigo-50
                    p-5

                    dark:border-indigo-900
                    dark:bg-indigo-950/30
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
                    Welcome to Client Management
                </h2>


                <p
                    class="
                        mt-1
                        text-sm
                        text-slate-600
                        dark:text-slate-300
                    "
                >
                    Add customers here before preparing invoices.
                    Their GST and billing information will be
                    available automatically while creating bills.
                </p>

            </div>

        @endif



        {{-- ========================================================= --}}
        {{-- TABLE --}}
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

            <div class="overflow-x-auto">

                <table
                    class="
                        min-w-[1000px]
                        w-full
                        text-left
                        text-sm
                        text-slate-700

                        dark:text-slate-300
                    "
                >

                    <thead
                        class="
                            bg-[#BFE0E0]
                            text-xs
                            font-semibold
                            uppercase
                            tracking-wider
                            text-slate-700

                            dark:bg-[#354A54]
                            dark:text-slate-200
                        "
                    >

                        <tr>

                            <th class="px-5 py-4">
                                {{ $singularLabel }}
                            </th>

                            <th class="px-5 py-4">
                                Mobile
                            </th>

                            <th class="px-5 py-4">
                                GSTIN / PAN
                            </th>

                            <th class="px-5 py-4">
                                State
                            </th>

                            <th class="px-5 py-4">
                                Address
                            </th>

                            <th class="px-5 py-4 text-right">
                                Actions
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

                        @forelse($clients as $client)

                            <tr
                                class="
                                    transition
                                    hover:bg-slate-50/80

                                    dark:hover:bg-slate-800/50
                                "
                            >

                                {{-- Name --}}
                                <td class="px-5 py-4">

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
                                                h-10
                                                w-10
                                                shrink-0
                                                items-center
                                                justify-center
                                                rounded-full
                                                text-sm
                                                font-bold

                                                {{ $isSupplier
                                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300'
                                                    : 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/50 dark:text-cyan-300'
                                                }}
                                            "
                                        >
                                            {{ Str::upper(
                                                Str::substr(
                                                    $client->name,
                                                    0,
                                                    1
                                                )
                                            ) }}
                                        </div>


                                        <div class="min-w-0">

                                            <p
                                                class="
                                                    font-semibold
                                                    text-slate-900

                                                    dark:text-white
                                                "
                                            >
                                                {{ $client->name }}
                                            </p>


                                            <p
                                                class="
                                                    text-xs
                                                    text-slate-400
                                                "
                                            >
                                                {{ $singularLabel }}
                                                #{{ $client->id }}
                                            </p>

                                        </div>

                                    </div>

                                </td>



                                {{-- Mobile --}}
                                <td class="px-5 py-4">
                                    {{ $client->mobile ?? '—' }}
                                </td>



                                {{-- GST/PAN --}}
                                <td class="px-5 py-4">

                                    <div class="space-y-1">

                                        <p>

                                            <span
                                                class="
                                                    text-xs
                                                    text-slate-400
                                                "
                                            >
                                                GST:
                                            </span>

                                            {{ $client->gstin ?? '—' }}

                                        </p>


                                        <p>

                                            <span
                                                class="
                                                    text-xs
                                                    text-slate-400
                                                "
                                            >
                                                PAN:
                                            </span>

                                            {{ $client->pan ?? '—' }}

                                        </p>

                                    </div>

                                </td>



                                {{-- State --}}
                                <td class="px-5 py-4">
                                    {{ $client->state ?? '—' }}
                                </td>



                                {{-- Address --}}
                                <td
                                    class="
                                        max-w-xs
                                        px-5
                                        py-4
                                    "
                                >

                                    {{ $client->address
                                        ? Str::limit(
                                            $client->address,
                                            80
                                        )
                                        : '—'
                                    }}

                                </td>



                                {{-- Actions --}}
                                <td class="px-5 py-4">

                                    <div
                                        class="
                                            flex
                                            justify-end
                                            gap-2
                                        "
                                    >

                                        {{-- ================================= --}}
                                        {{-- RECORD --}}
                                        {{-- ================================= --}}

                                        @if($isSupplier)

                                            <a
                                                href="{{ route(
                                                    'clients.show',
                                                    [
                                                        'client' => $client->id,
                                                        'view' => 'supplier'
                                                    ]
                                                ) }}"
                                                class="
                                                    rounded-lg
                                                    bg-emerald-50
                                                    px-3
                                                    py-2
                                                    text-xs
                                                    font-semibold
                                                    text-emerald-700
                                                    hover:bg-emerald-100

                                                    dark:bg-emerald-950/50
                                                    dark:text-emerald-300
                                                "
                                            >
                                                Purchase Record
                                            </a>

                                        @else

                                            <a
                                                href="{{ route(
                                                    'clients.show',
                                                    $client->id
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

                                                    dark:bg-blue-950/50
                                                    dark:text-blue-300
                                                "
                                            >
                                                Record
                                            </a>

                                        @endif



                                        {{-- Edit --}}
                                        <a
                                            href="{{ route(
                                                'clients.edit',
                                                $client->id
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

                                                dark:bg-amber-950/50
                                                dark:text-amber-300
                                            "
                                        >
                                            Edit
                                        </a>



                                        {{-- Delete --}}
                                        <form
                                            action="{{ route(
                                                'clients.destroy',
                                                $client->id
                                            ) }}"
                                            method="POST"
                                            onsubmit="return confirm(
                                                'Delete this {{ strtolower($singularLabel) }}?'
                                            );"
                                        >

                                            @csrf
                                            @method('DELETE')


                                            <button
                                                type="submit"
                                                class="
                                                    rounded-lg
                                                    bg-red-50
                                                    px-3
                                                    py-2
                                                    text-xs
                                                    font-semibold
                                                    text-red-700
                                                    hover:bg-red-100

                                                    dark:bg-red-950/50
                                                    dark:text-red-300
                                                "
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
                                    colspan="6"
                                    class="
                                        px-6
                                        py-14
                                        text-center
                                    "
                                >

                                    <div
                                        class="
                                            mx-auto
                                            flex
                                            h-16
                                            w-16
                                            items-center
                                            justify-center
                                            rounded-full
                                            bg-slate-100
                                            text-slate-400

                                            dark:bg-slate-800
                                        "
                                    >

                                        <svg
                                            class="h-8 w-8"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857"
                                            />
                                        </svg>

                                    </div>


                                    <h3
                                        class="
                                            mt-4
                                            text-base
                                            font-bold
                                            text-slate-900

                                            dark:text-white
                                        "
                                    >

                                        @if($q !== '')

                                            No matching
                                            {{ strtolower($pluralLabel) }}
                                            found

                                        @else

                                            No
                                            {{ strtolower($pluralLabel) }}
                                            added yet

                                        @endif

                                    </h3>


                                    <p
                                        class="
                                            mx-auto
                                            mt-1
                                            max-w-md
                                            text-sm
                                            text-slate-500

                                            dark:text-slate-400
                                        "
                                    >

                                        @if($q !== '')

                                            Try searching with another keyword.

                                        @elseif($isSupplier)

                                            Add your first supplier
                                            to start recording purchases.

                                        @else

                                            Add your first client
                                            to start creating invoices.

                                        @endif

                                    </p>


                                    @if($q === '')

                                        <a
                                            href="{{ route(
                                                'clients.create',
                                                ['type' => $type]
                                            ) }}"
                                            class="
                                                mt-5
                                                inline-flex
                                                rounded-xl
                                                bg-emerald-600
                                                px-4
                                                py-2.5
                                                text-sm
                                                font-semibold
                                                text-white
                                                hover:bg-emerald-700
                                            "
                                        >
                                            Add First {{ $singularLabel }}
                                        </a>

                                    @endif

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>



        {{-- ========================================================= --}}
        {{-- PAGINATION --}}
        {{-- ========================================================= --}}

        @if($clients->hasPages())

            <div class="mt-1">
                {{ $clients->links() }}
            </div>

        @endif

    </div>

</x-layouts.app>