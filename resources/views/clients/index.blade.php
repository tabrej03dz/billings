<x-layouts.app :title="__('Clients')">

    @php
        $activeBusinessId =
            auth()->user()->current_business_id
            ?? session('active_business_id')
            ?? 'default';

        /*
        |--------------------------------------------------------------------------
        | Versioned localStorage key
        |--------------------------------------------------------------------------
        | v4 use kiya gaya hai taaki purane testing ke dismissed values
        | current guide ko hide na karein.
        */
        $clientGuideStorageKey =
            'client-guide-v4-user-'
            . auth()->id()
            . '-business-'
            . $activeBusinessId;

        $shouldShowGuide = (bool) $showClientSuggestion;
    @endphp

    <div class="flex flex-col gap-5">

        {{-- Success Alert --}}
        @if(session('success'))
            <div
                class="flex items-start gap-3 rounded-2xl border border-emerald-200
                       bg-emerald-50 p-4 text-emerald-800 shadow-sm
                       dark:border-emerald-900/70 dark:bg-emerald-950/40
                       dark:text-emerald-300"
            >
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center
                           rounded-full bg-emerald-100 dark:bg-emerald-900/60"
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
                    <p class="font-semibold">Success</p>

                    <p class="mt-0.5 text-sm">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Header --}}
        <div
            class="rounded-2xl border border-cyan-100 bg-[#BFE0E0]
                   p-4 shadow-sm dark:border-slate-700 dark:bg-[#354A54]
                   sm:p-6"
        >
            <div
                class="flex flex-col gap-4
                       xl:flex-row xl:items-center xl:justify-between"
            >
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                            Clients
                        </h1>

                        <span
                            class="inline-flex items-center rounded-full
                                   bg-white/70 px-3 py-1 text-xs font-semibold
                                   text-slate-700 shadow-sm
                                   dark:bg-slate-800/70 dark:text-slate-200"
                        >
                            {{ number_format($totalClients) }}

                            {{ Str::plural('Client', $totalClients) }}
                        </span>
                    </div>

                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                        Manage customer details, GST information and billing records.
                    </p>
                </div>

                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">

                    {{-- Search Form --}}
                    <form
                        method="GET"
                        action="{{ route('clients.index') }}"
                        class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto"
                    >
                        <div class="relative w-full sm:w-80">
                            <div
                                class="pointer-events-none absolute inset-y-0 left-0
                                       flex items-center pl-3 text-slate-400"
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
                                        d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0
                                           7 7 0 0 1 14 0Z"
                                    />
                                </svg>
                            </div>

                            <input
                                type="text"
                                name="q"
                                value="{{ $q }}"
                                placeholder="Search name, mobile, GSTIN, PAN..."
                                class="block w-full rounded-xl border
                                       border-white/70 bg-white py-2.5
                                       pl-10 pr-4 text-sm text-slate-900
                                       shadow-sm outline-none transition
                                       placeholder:text-slate-400
                                       focus:border-cyan-500
                                       focus:ring-2 focus:ring-cyan-500/20
                                       dark:border-slate-600 dark:bg-slate-800
                                       dark:text-white"
                            >
                        </div>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center
                                   rounded-xl bg-slate-700 px-4 py-2.5
                                   text-sm font-semibold text-white
                                   shadow-sm transition hover:bg-slate-800"
                        >
                            Search
                        </button>

                        @if($q !== '')
                            <a
                                href="{{ route('clients.index') }}"
                                class="inline-flex items-center justify-center
                                       rounded-xl border border-slate-300
                                       bg-white/70 px-4 py-2.5
                                       text-sm font-semibold text-slate-700
                                       transition hover:bg-white
                                       dark:border-slate-600
                                       dark:bg-slate-800/70
                                       dark:text-slate-200"
                            >
                                Clear
                            </a>
                        @endif
                    </form>

                    <a
                        href="{{ route('clients.create') }}"
                        class="inline-flex items-center justify-center gap-2
                               rounded-xl bg-emerald-600 px-4 py-2.5
                               text-sm font-semibold text-white shadow-sm
                               transition hover:bg-emerald-700"
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

                        New Client
                    </a>
                </div>
            </div>
        </div>

        {{--
        |--------------------------------------------------------------------------
        | Client Suggestion Guide
        |--------------------------------------------------------------------------
        | Important:
        | New user ke liye ye div server side se visible render ho raha hai.
        | Is par x-cloak, x-show ya Alpine ka koi code nahi hai.
        --}}
        @if($shouldShowGuide)
            <div
                id="clientSuggestionGuide"
                data-storage-key="{{ $clientGuideStorageKey }}"
                class="relative overflow-hidden rounded-2xl border
                       border-indigo-200 bg-gradient-to-br
                       from-indigo-50 via-white to-cyan-50
                       p-5 shadow-sm
                       dark:border-indigo-900/70
                       dark:from-indigo-950/60
                       dark:via-slate-900
                       dark:to-cyan-950/40
                       sm:p-6"
            >
                {{-- Decoration --}}
                <div
                    class="pointer-events-none absolute -right-16 -top-16
                           h-40 w-40 rounded-full bg-indigo-200/40 blur-2xl
                           dark:bg-indigo-700/20"
                ></div>

                <div
                    class="pointer-events-none absolute -bottom-16 left-1/3
                           h-32 w-32 rounded-full bg-cyan-200/40 blur-2xl
                           dark:bg-cyan-700/20"
                ></div>

                {{-- Close Button --}}
                <button
                    type="button"
                    onclick="dismissClientGuide()"
                    aria-label="Close client guide"
                    title="Hide suggestions"
                    class="absolute right-3 top-3 z-20 inline-flex
                           h-9 w-9 items-center justify-center rounded-full
                           border border-slate-200 bg-white/90
                           text-slate-500 shadow-sm transition
                           hover:bg-white hover:text-red-600
                           dark:border-slate-700 dark:bg-slate-800
                           dark:text-slate-300 dark:hover:text-red-400"
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
                            d="M6 18 18 6M6 6l12 12"
                        />
                    </svg>
                </button>

                <div class="relative z-10 pr-8">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start">

                        {{-- Guide Icon --}}
                        <div
                            class="flex h-14 w-14 shrink-0 items-center
                                   justify-center rounded-2xl bg-indigo-600
                                   text-white shadow-lg shadow-indigo-600/20"
                        >
                            <svg
                                class="h-7 w-7"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477
                                       9.246 5 7.5 5S4.168 5.477 3
                                       6.253v13C4.168 18.477
                                       5.754 18 7.5 18s3.332.477
                                       4.5 1.253m0-13C13.168 5.477
                                       14.754 5 16.5 5s3.332.477
                                       4.5 1.253v13C19.832 18.477
                                       18.246 18 16.5 18s-3.332.477
                                       -4.5 1.253"
                                />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2
                                    class="text-lg font-bold text-slate-900
                                           dark:text-white sm:text-xl"
                                >
                                    Welcome to Client Management
                                </h2>

                                <span
                                    class="rounded-full bg-indigo-100
                                           px-2.5 py-1 text-[11px]
                                           font-bold uppercase tracking-wide
                                           text-indigo-700
                                           dark:bg-indigo-900/60
                                           dark:text-indigo-300"
                                >
                                    Quick Guide
                                </span>
                            </div>

                            <p
                                class="mt-2 max-w-3xl text-sm leading-6
                                       text-slate-600 dark:text-slate-300"
                            >
                                Add your customers here before creating invoices.
                                Client details will automatically become available
                                while preparing bills.
                            </p>

                            {{-- Guide Cards --}}
                            <div class="mt-5 grid gap-3 md:grid-cols-3">

                                <div
                                    class="rounded-xl border border-indigo-100
                                           bg-white/80 p-4 shadow-sm
                                           dark:border-indigo-900/50
                                           dark:bg-slate-800/70"
                                >
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="flex h-8 w-8 shrink-0
                                                   items-center justify-center
                                                   rounded-full bg-indigo-100
                                                   text-sm font-bold
                                                   text-indigo-700
                                                   dark:bg-indigo-900/70
                                                   dark:text-indigo-300"
                                        >
                                            1
                                        </span>

                                        <div>
                                            <h3
                                                class="text-sm font-bold
                                                       text-slate-900
                                                       dark:text-white"
                                            >
                                                Add a client
                                            </h3>

                                            <p
                                                class="mt-1 text-xs leading-5
                                                       text-slate-500
                                                       dark:text-slate-400"
                                            >
                                                Click New Client and enter the
                                                customer’s basic details.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="rounded-xl border border-cyan-100
                                           bg-white/80 p-4 shadow-sm
                                           dark:border-cyan-900/50
                                           dark:bg-slate-800/70"
                                >
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="flex h-8 w-8 shrink-0
                                                   items-center justify-center
                                                   rounded-full bg-cyan-100
                                                   text-sm font-bold
                                                   text-cyan-700
                                                   dark:bg-cyan-900/70
                                                   dark:text-cyan-300"
                                        >
                                            2
                                        </span>

                                        <div>
                                            <h3
                                                class="text-sm font-bold
                                                       text-slate-900
                                                       dark:text-white"
                                            >
                                                GST details
                                            </h3>

                                            <p
                                                class="mt-1 text-xs leading-5
                                                       text-slate-500
                                                       dark:text-slate-400"
                                            >
                                                GSTIN and PAN are optional for
                                                non-GST customers.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="rounded-xl border border-emerald-100
                                           bg-white/80 p-4 shadow-sm
                                           dark:border-emerald-900/50
                                           dark:bg-slate-800/70"
                                >
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="flex h-8 w-8 shrink-0
                                                   items-center justify-center
                                                   rounded-full bg-emerald-100
                                                   text-sm font-bold
                                                   text-emerald-700
                                                   dark:bg-emerald-900/70
                                                   dark:text-emerald-300"
                                        >
                                            3
                                        </span>

                                        <div>
                                            <h3
                                                class="text-sm font-bold
                                                       text-slate-900
                                                       dark:text-white"
                                            >
                                                View records
                                            </h3>

                                            <p
                                                class="mt-1 text-xs leading-5
                                                       text-slate-500
                                                       dark:text-slate-400"
                                            >
                                                View invoices, purchases and
                                                outstanding balances.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="mt-5 flex flex-col gap-3
                                       sm:flex-row sm:items-center"
                            >
                                <a
                                    href="{{ route('clients.create') }}"
                                    class="inline-flex items-center justify-center
                                           gap-2 rounded-xl bg-indigo-600
                                           px-4 py-2.5 text-sm font-semibold
                                           text-white shadow-sm transition
                                           hover:bg-indigo-700"
                                >
                                    <svg
                                        class="h-4 w-4"
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

                                    Add Your First Client
                                </a>

                                <button
                                    type="button"
                                    onclick="dismissClientGuide()"
                                    class="inline-flex items-center justify-center
                                           rounded-xl border border-slate-300
                                           bg-white px-4 py-2.5
                                           text-sm font-semibold text-slate-700
                                           transition hover:bg-slate-50
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-200
                                           dark:hover:bg-slate-700"
                                >
                                    Got it, hide this guide
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reopen Guide --}}
            <div
                id="clientSuggestionReopen"
                class="hidden justify-end"
            >
                <button
                    type="button"
                    onclick="showClientGuide()"
                    class="inline-flex items-center gap-2 rounded-xl
                           border border-indigo-200 bg-indigo-50
                           px-3 py-2 text-xs font-semibold
                           text-indigo-700 transition hover:bg-indigo-100
                           dark:border-indigo-900
                           dark:bg-indigo-950/40
                           dark:text-indigo-300"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8.228 9a3.001 3.001 0 1 1
                               5.824 1c0 2-3 2-3 4m.01 4h.01
                               M21 12a9 9 0 1 1-18 0
                               9 9 0 0 1 18 0Z"
                        />
                    </svg>

                    Show Client Guide
                </button>
            </div>
        @endif

        {{-- Client Table --}}
        <div
            class="overflow-hidden rounded-2xl border border-slate-200
                   bg-white shadow-sm
                   dark:border-slate-700 dark:bg-neutral-900"
        >
            <div class="overflow-x-auto">
                <table
                    class="min-w-[1000px] w-full text-left text-sm
                           text-slate-700 dark:text-slate-300"
                >
                    <thead
                        class="bg-[#BFE0E0] text-xs font-semibold uppercase
                               tracking-wider text-slate-700
                               dark:bg-[#354A54] dark:text-slate-200"
                    >
                        <tr>
                            <th class="px-5 py-4">Client</th>
                            <th class="px-5 py-4">Mobile</th>
                            <th class="px-5 py-4">GSTIN / PAN</th>
                            <th class="px-5 py-4">State</th>
                            <th class="px-5 py-4">Address</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody
                        class="divide-y divide-slate-100
                               dark:divide-slate-800"
                    >
                        @forelse($clients as $client)
                            <tr
                                class="transition hover:bg-slate-50/80
                                       dark:hover:bg-slate-800/50"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 shrink-0
                                                   items-center justify-center
                                                   rounded-full bg-cyan-100
                                                   text-sm font-bold
                                                   text-cyan-700
                                                   dark:bg-cyan-900/50
                                                   dark:text-cyan-300"
                                        >
                                            {{ Str::upper(Str::substr($client->name, 0, 1)) }}
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="font-semibold text-slate-900
                                                       dark:text-white"
                                            >
                                                {{ $client->name }}
                                            </p>

                                            <p class="text-xs text-slate-400">
                                                Client #{{ $client->id }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    {{ $client->mobile ?? '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="space-y-1">
                                        <p>
                                            <span class="text-xs text-slate-400">
                                                GST:
                                            </span>

                                            {{ $client->gstin ?? '—' }}
                                        </p>

                                        <p>
                                            <span class="text-xs text-slate-400">
                                                PAN:
                                            </span>

                                            {{ $client->pan ?? '—' }}
                                        </p>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    {{ $client->state ?? '—' }}
                                </td>

                                <td class="max-w-xs px-5 py-4">
                                    {{ $client->address
                                        ? Str::limit($client->address, 80)
                                        : '—'
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ route('clients.show', $client->id) }}"
                                            class="rounded-lg bg-blue-50
                                                   px-3 py-2 text-xs font-semibold
                                                   text-blue-700
                                                   hover:bg-blue-100
                                                   dark:bg-blue-950/50
                                                   dark:text-blue-300"
                                        >
                                            Record
                                        </a>

                                        <a
                                            href="{{ route('clients.edit', $client->id) }}"
                                            class="rounded-lg bg-amber-50
                                                   px-3 py-2 text-xs font-semibold
                                                   text-amber-700
                                                   hover:bg-amber-100
                                                   dark:bg-amber-950/50
                                                   dark:text-amber-300"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            action="{{ route('clients.destroy', $client->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Delete this client?');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="rounded-lg bg-red-50
                                                       px-3 py-2 text-xs
                                                       font-semibold text-red-700
                                                       hover:bg-red-100
                                                       dark:bg-red-950/50
                                                       dark:text-red-300"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center">
                                    <div
                                        class="mx-auto flex h-16 w-16
                                               items-center justify-center
                                               rounded-full bg-slate-100
                                               text-slate-400
                                               dark:bg-slate-800"
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
                                                d="M17 20h5v-2a3 3 0 0 0-5.356-1.857
                                                   M17 20H7m10 0v-2
                                                   c0-.656-.126-1.283-.356-1.857
                                                   M7 20H2v-2a3 3 0 0 1
                                                   5.356-1.857M7 20v-2
                                                   c0-.656.126-1.283.356-1.857
                                                   m0 0a5.002 5.002 0 0 1
                                                   9.288 0M15 7a3 3 0 1 1-6 0
                                                   3 3 0 0 1 6 0Z"
                                            />
                                        </svg>
                                    </div>

                                    <h3
                                        class="mt-4 text-base font-bold
                                               text-slate-900 dark:text-white"
                                    >
                                        @if($q !== '')
                                            No matching clients found
                                        @else
                                            No clients added yet
                                        @endif
                                    </h3>

                                    <p
                                        class="mx-auto mt-1 max-w-md text-sm
                                               text-slate-500
                                               dark:text-slate-400"
                                    >
                                        @if($q !== '')
                                            Try searching with another keyword.
                                        @else
                                            Add your first client to start
                                            creating invoices.
                                        @endif
                                    </p>

                                    @if($q === '')
                                        <a
                                            href="{{ route('clients.create') }}"
                                            class="mt-5 inline-flex rounded-xl
                                                   bg-emerald-600 px-4 py-2.5
                                                   text-sm font-semibold
                                                   text-white
                                                   hover:bg-emerald-700"
                                        >
                                            Add First Client
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($clients->hasPages())
            <div class="mt-1">
                {{ $clients->links() }}
            </div>
        @endif
    </div>

    {{--
    |--------------------------------------------------------------------------
    | Plain JavaScript
    |--------------------------------------------------------------------------
    | Alpine, x-cloak, x-show ya @push('scripts') par dependency nahi hai.
    --}}
    @if($shouldShowGuide)
        <script>
            (function () {
                const guideId = 'clientSuggestionGuide';
                const reopenId = 'clientSuggestionReopen';

                function getElements() {
                    return {
                        guide: document.getElementById(guideId),
                        reopen: document.getElementById(reopenId)
                    };
                }

                function getStorageKey() {
                    const guide = document.getElementById(guideId);

                    return guide
                        ? guide.getAttribute('data-storage-key')
                        : null;
                }

                window.dismissClientGuide = function () {
                    const elements = getElements();
                    const storageKey = getStorageKey();

                    if (elements.guide) {
                        elements.guide.style.display = 'none';
                    }

                    if (elements.reopen) {
                        elements.reopen.classList.remove('hidden');
                        elements.reopen.style.display = 'flex';
                    }

                    if (storageKey) {
                        try {
                            localStorage.setItem(storageKey, '1');
                        } catch (error) {
                            console.warn('Guide preference could not be saved.');
                        }
                    }
                };

                window.showClientGuide = function () {
                    const elements = getElements();
                    const storageKey = getStorageKey();

                    if (elements.guide) {
                        elements.guide.style.display = 'block';
                    }

                    if (elements.reopen) {
                        elements.reopen.style.display = 'none';
                    }

                    if (storageKey) {
                        try {
                            localStorage.removeItem(storageKey);
                        } catch (error) {
                            console.warn('Guide preference could not be removed.');
                        }
                    }

                    if (elements.guide) {
                        elements.guide.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                };

                function initializeClientGuide() {
                    const elements = getElements();
                    const storageKey = getStorageKey();

                    if (!elements.guide) {
                        return;
                    }

                    let dismissed = false;

                    if (storageKey) {
                        try {
                            dismissed =
                                localStorage.getItem(storageKey) === '1';
                        } catch (error) {
                            dismissed = false;
                        }
                    }

                    if (dismissed) {
                        elements.guide.style.display = 'none';

                        if (elements.reopen) {
                            elements.reopen.classList.remove('hidden');
                            elements.reopen.style.display = 'flex';
                        }
                    } else {
                        elements.guide.style.display = 'block';

                        if (elements.reopen) {
                            elements.reopen.style.display = 'none';
                        }
                    }
                }

                if (document.readyState === 'loading') {
                    document.addEventListener(
                        'DOMContentLoaded',
                        initializeClientGuide,
                        { once: true }
                    );
                } else {
                    initializeClientGuide();
                }

                /*
                |--------------------------------------------------------------------------
                | Flux / Livewire wire:navigate support
                |--------------------------------------------------------------------------
                */
                document.addEventListener(
                    'livewire:navigated',
                    initializeClientGuide
                );
            })();
        </script>
    @endif

</x-layouts.app>