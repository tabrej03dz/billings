{{-- @php
    $dashboardUser = auth()->user();

    $dashboardBusinessId =
        session('active_business_id')
        ?? $dashboardUser->current_business_id
        ?? $dashboardUser->businesses->first()?->id;

    $dashboardBusiness = $dashboardBusinessId
        ? App\Models\Business::find($dashboardBusinessId)
        : null;
@endphp

@if(
    $dashboardBusiness
    && $dashboardBusiness->isProfileIncomplete()
    && !$dashboardBusiness->profile_suggestion_dismissed_at
)
    <div
        id="businessProfileSuggestion"
        class="mb-6 overflow-hidden rounded-3xl border border-indigo-200 bg-gradient-to-r from-indigo-50 to-violet-50 p-5 shadow-sm dark:border-indigo-900 dark:from-indigo-950/40 dark:to-violet-950/40"
    >
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
            <div
                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-lg"
            >
                <svg
                    class="h-7 w-7"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 21h18M5 21V7l7-4 7 4v14"
                    />
                </svg>
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2
                            class="text-lg font-black text-zinc-900 dark:text-white"
                        >
                            Complete your business profile
                        </h2>

                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                            Your profile is
                            <strong>
                                {{ $dashboardBusiness->profile_completion }}%
                            </strong>
                            complete. Add the remaining details to create
                            professional invoices.
                        </p>
                    </div>

                    <button
                        type="button"
                        onclick="dismissBusinessSuggestion()"
                        class="text-zinc-400 hover:text-zinc-700"
                    >
                        ✕
                    </button>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <a
                        href="{{ route('business-profile.index') }}"
                        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700"
                    >
                        Complete Setup
                    </a>

                    <div class="min-w-[180px] flex-1">
                        <div
                            class="h-2 overflow-hidden rounded-full bg-white dark:bg-zinc-800"
                        >
                            <div
                                class="h-full rounded-full bg-indigo-600"
                                style="width: {{ $dashboardBusiness->profile_completion }}%"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function dismissBusinessSuggestion() {
            const response = await fetch(
                @json(route('business-profile.suggestion.dismiss')),
                {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token())
                    }
                }
            );

            if (response.ok) {
                document
                    .getElementById('businessProfileSuggestion')
                    ?.remove();
            }
        }
    </script>
@endif --}}

<x-layouts.app :title="__('Dashboard')">
    <div x-data="{ showRatesForm: false }" class="flex flex-col gap-4">
        {{-- FLASH MESSAGE --}}
        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-3 rounded-lg bg-red-50 text-red-700 border border-red-200 text-sm">
                <ul class="list-disc ml-4">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- PAGE HEADER --}}
        {{-- <div class="flex flex-wrap bg-[#BFE0E0] dark:bg-[#354A54] items-center justify-between gap-3 p-6">
            <h1 class="text-2xl font-bold text-black dark:text-white">
                Dashboard
            </h1>

            <div class="text-sm text-gray-500 dark:text-gray-50 p-3 border-2 border-[#FCB055]">
                Today:
                <span class="font-semibold text-grey-900">
                    {{ isset($today) ? $today->format('d M Y') : now()->format('d M Y') }}
                </span>
            </div>
        </div> --}}

        {{-- PAGE HEADER --}}
        <div class="flex flex-wrap bg-[#BFE0E0] dark:bg-[#354A54] items-center justify-between gap-3 p-6">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-black dark:text-white">
                    Dashboard
                </h1>

                {{-- Notification Icon --}}
                <a href="{{ route('bill-requests.index') }}"
                class="relative inline-flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-neutral-700 transition"
                title="Bill Requests">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.857 17H19l-1.405-1.405A2.032 2.032 0 0117 14.158V11a5.002 5.002 0 00-4-4.9V5a1 1 0 10-2 0v1.1A5.002 5.002 0 007 11v3.159c0 .538-.214 1.055-.595 1.436L5 17h4.143m5.714 0a3 3 0 11-5.714 0m5.714 0H9.143" />
                    </svg>

                    {{-- Optional red dot --}}
                    <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full"></span>
                </a>
            </div>

            <div class="text-sm text-gray-500 dark:text-gray-50 p-3 border-2 border-[#FCB055]">
                Today:
                <span class="font-semibold text-grey-900">
                    {{ isset($today) ? $today->format('d M Y') : now()->format('d M Y') }}
                </span>
            </div>
        </div>

        {{-- GETTING STARTED GUIDE: ITEM / PURCHASE / INVOICE --}}
        @php
            $dashboardGuideStorageKey =
                'dashboard-setup-guide-v2-user-'
                . auth()->id()
                . '-business-'
                . ($bid ?? 'default');

            $itemSetupDone = (int) ($dashboardItemCount ?? 0) > 0;
            $purchaseSetupDone = (int) ($dashboardPurchaseCount ?? 0) > 0;
            $invoiceSetupDone = (int) ($dashboardInvoiceCount ?? 0) > 0;

            $completedSetupSteps = collect([
                $itemSetupDone,
                $purchaseSetupDone,
                $invoiceSetupDone,
            ])->filter()->count();

            $dashboardSetupProgress = (int) round(
                ($completedSetupSteps / 3) * 100
            );

            $purchaseCreateRoute = \Illuminate\Support\Facades\Route::has('purchases.create')
                ? route('purchases.create')
                : route('purchases.index');
        @endphp

        @if($showDashboardSuggestion ?? false)
            <section
                id="dashboardSetupGuide"
                data-storage-key="{{ $dashboardGuideStorageKey }}"
                class="relative overflow-hidden rounded-2xl border border-indigo-200
                       bg-gradient-to-br from-indigo-50 via-white to-cyan-50 p-5 shadow-sm
                       dark:border-indigo-900/70 dark:from-indigo-950/50
                       dark:via-neutral-900 dark:to-cyan-950/30 sm:p-6"
            >
                <div class="pointer-events-none absolute -right-16 -top-16 h-40 w-40
                            rounded-full bg-indigo-200/40 blur-3xl dark:bg-indigo-700/20"></div>

                <div class="pointer-events-none absolute -bottom-16 left-1/3 h-36 w-36
                            rounded-full bg-cyan-200/40 blur-3xl dark:bg-cyan-700/20"></div>

                <button
                    type="button"
                    onclick="dismissDashboardSetupGuide()"
                    aria-label="Close setup guide"
                    title="Hide this guide"
                    class="absolute right-3 top-3 z-20 inline-flex h-9 w-9 items-center
                           justify-center rounded-full border border-gray-200 bg-white/90
                           text-gray-500 shadow-sm transition hover:bg-white hover:text-red-600
                           dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300
                           dark:hover:text-red-400"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="relative z-10 pr-8">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center
                                    rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6M5 5h14v14H5z" />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white sm:text-xl">
                                    Complete your billing setup
                                </h2>

                                <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-[11px]
                                             font-bold uppercase tracking-wide text-indigo-700
                                             dark:bg-indigo-900/60 dark:text-indigo-300">
                                    Getting Started
                                </span>
                            </div>

                            <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600 dark:text-neutral-300">
                                Billing ko properly start karne ke liye item add karein, purchase entry karein
                                aur customer ke liye tax invoice create karein.
                            </p>

                            <div class="mt-4">
                                <div class="mb-1 flex items-center justify-between text-xs">
                                    <span class="font-semibold text-gray-700 dark:text-neutral-300">
                                        Setup progress
                                    </span>

                                    <span class="font-bold text-indigo-700 dark:text-indigo-300">
                                        {{ $dashboardSetupProgress }}%
                                    </span>
                                </div>

                                <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-neutral-700">
                                    <div class="h-full rounded-full bg-indigo-600 transition-all"
                                         style="width: {{ $dashboardSetupProgress }}%"></div>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-3 md:grid-cols-3">
                                {{-- ITEM STEP --}}
                                <article class="rounded-xl border p-4 shadow-sm
                                    {{ $itemSetupDone
                                        ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/60 dark:bg-emerald-950/30'
                                        : 'border-indigo-100 bg-white/80 dark:border-indigo-900/50 dark:bg-neutral-800/70' }}">
                                    <div class="flex items-start gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full
                                                     text-sm font-bold
                                            {{ $itemSetupDone
                                                ? 'bg-emerald-600 text-white'
                                                : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/70 dark:text-indigo-300' }}">
                                            {{ $itemSetupDone ? '✓' : '1' }}
                                        </span>

                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Add Item</h3>
                                            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-neutral-400">
                                                @if($isServiceBusiness ?? false)
                                                    Service ka naam, rate aur tax details add karein.
                                                @else
                                                    Product ya service ka naam, rate, tax, barcode aur stock add karein.
                                                @endif
                                            </p>

                                            <div class="mt-3">
                                                @if($itemSetupDone)
                                                    <span class="inline-flex rounded-lg bg-emerald-100 px-3 py-1.5
                                                                 text-xs font-semibold text-emerald-700
                                                                 dark:bg-emerald-900/50 dark:text-emerald-300">
                                                        {{ $dashboardItemCount }} item added
                                                    </span>
                                                @else
                                                    @can('create item')
                                                        <a href="{{ route('items.create') }}"
                                                           class="inline-flex rounded-lg bg-indigo-600 px-3 py-1.5
                                                                  text-xs font-semibold text-white hover:bg-indigo-700">
                                                            + Add Item
                                                        </a>
                                                    @endcan
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </article>

                                {{-- PURCHASE STEP --}}
                                <article class="rounded-xl border p-4 shadow-sm
                                    {{ $purchaseSetupDone
                                        ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/60 dark:bg-emerald-950/30'
                                        : 'border-indigo-100 bg-white/80 dark:border-indigo-900/50 dark:bg-neutral-800/70' }}">
                                    <div class="flex items-start gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full
                                                     text-sm font-bold
                                            {{ $purchaseSetupDone
                                                ? 'bg-emerald-600 text-white'
                                                : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/70 dark:text-indigo-300' }}">
                                            {{ $purchaseSetupDone ? '✓' : '2' }}
                                        </span>

                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Add Purchase</h3>
                                            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-neutral-400">
                                                Supplier purchase record karke stock aur cost price maintain karein.
                                            </p>

                                            <div class="mt-3">
                                                @if($purchaseSetupDone)
                                                    <span class="inline-flex rounded-lg bg-emerald-100 px-3 py-1.5
                                                                 text-xs font-semibold text-emerald-700
                                                                 dark:bg-emerald-900/50 dark:text-emerald-300">
                                                        {{ $dashboardPurchaseCount }} purchase added
                                                    </span>
                                                @else
                                                    @can('create purchase')
                                                        <a href="{{ $purchaseCreateRoute }}"
                                                           class="inline-flex rounded-lg bg-indigo-600 px-3 py-1.5
                                                                  text-xs font-semibold text-white hover:bg-indigo-700">
                                                            + Add Purchase
                                                        </a>
                                                    @endcan
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </article>

                                {{-- INVOICE STEP --}}
                                <article class="rounded-xl border p-4 shadow-sm
                                    {{ $invoiceSetupDone
                                        ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/60 dark:bg-emerald-950/30'
                                        : 'border-indigo-100 bg-white/80 dark:border-indigo-900/50 dark:bg-neutral-800/70' }}">
                                    <div class="flex items-start gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full
                                                     text-sm font-bold
                                            {{ $invoiceSetupDone
                                                ? 'bg-emerald-600 text-white'
                                                : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/70 dark:text-indigo-300' }}">
                                            {{ $invoiceSetupDone ? '✓' : '3' }}
                                        </span>

                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Create Invoice</h3>
                                            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-neutral-400">
                                                Customer select karke items add karein aur final tax invoice save karein.
                                            </p>

                                            <div class="mt-3">
                                                @if($invoiceSetupDone)
                                                    <span class="inline-flex rounded-lg bg-emerald-100 px-3 py-1.5
                                                                 text-xs font-semibold text-emerald-700
                                                                 dark:bg-emerald-900/50 dark:text-emerald-300">
                                                        {{ $dashboardInvoiceCount }} invoice created
                                                    </span>
                                                @else
                                                    @can('create invoice')
                                                        <a href="{{ route('invoices.create', 'tax') }}"
                                                           class="inline-flex rounded-lg bg-indigo-600 px-3 py-1.5
                                                                  text-xs font-semibold text-white hover:bg-indigo-700">
                                                            + Create Invoice
                                                        </a>
                                                    @endcan
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            </div>

                            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3
                                        text-sm text-amber-800 dark:border-amber-900/60
                                        dark:bg-amber-950/30 dark:text-amber-300">
                                <strong>Recommended order:</strong>
                                Item → Purchase → Invoice. Purchase optional ho sakta hai, lekin stock aur profit
                                calculation ke liye useful hai.
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                @if(!$itemSetupDone)
                                    @can('create item')
                                        <a href="{{ route('items.create') }}"
                                           class="inline-flex items-center justify-center rounded-xl bg-indigo-600
                                                  px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                                            Start with Item
                                        </a>
                                    @endcan
                                @elseif(!$purchaseSetupDone)
                                    @can('create purchase')
                                        <a href="{{ $purchaseCreateRoute }}"
                                           class="inline-flex items-center justify-center rounded-xl bg-indigo-600
                                                  px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                                            Add Purchase
                                        </a>
                                    @endcan
                                @elseif(!$invoiceSetupDone)
                                    @can('create invoice')
                                        <a href="{{ route('invoices.create', 'tax') }}"
                                           class="inline-flex items-center justify-center rounded-xl bg-indigo-600
                                                  px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                                            Create Invoice
                                        </a>
                                    @endcan
                                @endif

                                <button type="button" onclick="dismissDashboardSetupGuide()"
                                        class="inline-flex items-center justify-center rounded-xl border
                                               border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold
                                               text-gray-700 transition hover:bg-gray-50
                                               dark:border-neutral-600 dark:bg-neutral-800
                                               dark:text-neutral-200 dark:hover:bg-neutral-700">
                                    Hide guide
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div id="dashboardSetupGuideReopen" class="hidden justify-end">
                <button type="button" onclick="showDashboardSetupGuide()"
                        class="inline-flex items-center gap-2 rounded-xl border border-indigo-200
                               bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700
                               hover:bg-indigo-100 dark:border-indigo-900
                               dark:bg-indigo-950/40 dark:text-indigo-300">
                    Show Setup Guide
                </button>
            </div>
        @endif

        {{-- FILTER BAR (Month / From-To) --}}
        <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-xl p-4">
            <form method="GET" action="{{ url()->current() }}" class="flex flex-col lg:flex-row gap-3 lg:items-end">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full">
                    {{-- Preset --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Preset</label>
                        <select name="preset"
                                class="w-full border rounded px-2 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100">
                            <option value="">Custom</option>
                            <option value="today" {{ ($preset ?? '') === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="7d" {{ ($preset ?? '') === '7d' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="month" {{ ($preset ?? '') === 'month' ? 'selected' : '' }}>This Month</option>
                        </select>
                    </div>

                    {{-- From --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">From</label>
                        <input type="date" name="from"
                               value="{{ isset($from) ? \Carbon\Carbon::parse($from)->toDateString() : request('from') }}"
                               class="w-full border rounded px-2 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100">
                    </div>

                    {{-- To --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">To</label>
                        <input type="date" name="to"
                               value="{{ isset($to) ? \Carbon\Carbon::parse($to)->toDateString() : request('to') }}"
                               class="w-full border rounded px-2 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100">
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 rounded text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700">
                        Apply
                    </button>

                    <a href="{{ url()->current() }}"
                       class="px-4 py-2 rounded text-sm font-semibold border border-gray-300 dark:border-neutral-700
                      text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-neutral-800">
                        Reset
                    </a>
                </div>
            </form>

            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Showing data from
                <span class="font-semibold">{{ isset($from) ? \Carbon\Carbon::parse($from)->format('d M Y') : '—' }}</span>
                to
                <span class="font-semibold">{{ isset($to) ? \Carbon\Carbon::parse($to)->format('d M Y') : '—' }}</span>
            </div>
        </div>


        {{-- TOP SUMMARY CARDS: SMALL COMPACT BOXES --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            

            {{-- Today Sales --}}
            <div class="bg-[#33D39A] dark:bg-[#E6F7F1] rounded-lg border border-grey-900 dark:border p-3 shadow-sm">
                <p class="text-[10px] font-semibold text-gray-50 dark:text-gray-500 uppercase tracking-wider">
                    Today Sales
                </p>
                <p class="mt-1 text-lg font-bold text-gray-50 dark:text-black leading-none">
                    ₹ {{ number_format($todaySalesAmount ?? 0, 2) }}
                </p>
                <p class="mt-1 text-[11px] text-[#F5F5F5] dark:text-gray-500">
                    {{ $todaySalesCount ?? 0 }} invoice(s)
                </p>
            </div>

            {{-- Today Gross Profit --}}
            <div class="bg-[#7C3AED] dark:bg-[#4C3B68] rounded-lg border border-purple-200 dark:border-purple-800 p-3 shadow-sm">
                <p class="text-[10px] font-semibold text-white uppercase tracking-wider">
                    Today Gross Profit
                </p>

                <p class="mt-1 text-lg font-bold text-white leading-none">
                    ₹ {{ number_format($todayProfitAmount ?? 0, 2) }}
                </p>

                <p class="mt-1 text-[11px] text-purple-100">
                    Sale: ₹ {{ number_format($todayItemSaleAmount ?? 0, 2) }}
                </p>

                <p class="mt-1 text-[11px] text-purple-100">
                    Cost: ₹ {{ number_format($todayItemCostAmount ?? 0, 2) }}
                    • Margin: {{ number_format($todayProfitPercent ?? 0, 2) }}%
                </p>
            </div>

            {{-- Month Sales --}}
            <div class="bg-[#37BEF7] dark:bg-[#658282] rounded-lg border border-grey-200 dark:border  p-3 shadow-sm">
                <p class="text-[10px] font-semibold text-gray-50 dark:text-gray-50 uppercase tracking-wider">
                    This Month Sales
                </p>
                <p class="mt-1 text-lg font-bold text-gray-50 dark:text-white leading-none">
                    ₹ {{ number_format($monthSalesAmount ?? 0, 2) }}
                </p>
                <p class="mt-1 text-[11px] text-[#E0F3FF] dark:text-gray-50">
                    Total from 1st of month
                </p>
            </div>

            {{-- Month Purchases --}}
            <div class="bg-[#00bcac] dark:bg-[#354a54] rounded-lg border border-grey-200 dark:border  p-3 shadow-sm">
                <p class="text-[10px] font-semibold text-gray-50 dark:text-gray-400 uppercase tracking-wider">
                    This Month Purchases
                </p>
                <p class="mt-1 text-lg font-bold text-gray-50 dark:text-white leading-none">
                    ₹ {{ number_format($monthPurchasesAmount ?? 0, 2) }}
                </p>
                <p class="mt-1 text-[11px] text-gray-50 dark:text-gray-400">
                    Today: ₹ {{ number_format($todayPurchasesAmount ?? 0, 2) }}
                </p>
            </div>

            {{-- Pending Amount --}}
            <div
                class="overflow-hidden rounded-lg border border-gray-200
                    bg-[#bc5b6a] shadow-sm
                    dark:border-neutral-700 dark:bg-[#3C3433]"
            >
                {{-- All Pending Invoices --}}
                <a
                    href="{{ route('invoices.index', [
                        'type'   => 'tax',
                        'status' => 'pending',
                    ]) }}"
                    class="group block p-3 transition
                        hover:bg-black/10
                        focus:outline-none focus:ring-2
                        focus:ring-inset focus:ring-white/70"
                    title="View all pending invoices"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p
                                class="text-[10px] font-semibold uppercase
                                    tracking-wider text-red-50"
                            >
                                Pending Amount
                            </p>

                            <p
                                class="mt-1 text-lg font-bold leading-none
                                    text-red-50"
                            >
                                ₹ {{ number_format($totalPendingAmount ?? 0, 2) }}
                            </p>
                        </div>

                        <svg
                            class="h-5 w-5 text-white/70 transition
                                group-hover:translate-x-1
                                group-hover:text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </div>

                    <p class="mt-2 text-[10px] font-medium text-white/80">
                        Click to view all pending invoices
                    </p>
                </a>

                {{-- Today's Pending Invoices --}}
                <a
                    href="{{ route('invoices.index', [
                        'type'      => 'tax',
                        'status'    => 'pending',
                        'from_date' => now()->toDateString(),
                        'to_date'   => now()->toDateString(),
                    ]) }}"
                    class="flex items-center justify-between border-t
                        border-white/20 px-3 py-2
                        text-[11px] text-gray-50 transition
                        hover:bg-black/10"
                    title="View today's pending invoices"
                >
                    <span>Today Pending</span>

                    <span class="font-bold">
                        ₹ {{ number_format($todayPendingAmount ?? 0, 2) }}
                    </span>
                </a>
            </div>
            @unless($isServiceBusiness ?? false)
                {{-- Items / Stock --}}
                <div
                    class="overflow-hidden rounded-lg border border-gray-200
                           bg-[#ffd055] shadow-sm
                           dark:border-neutral-700 dark:bg-[#3C3433]"
                >
                    {{-- All Items --}}
                    <a
                        href="{{ route('items.index') }}"
                        class="group block p-3 transition hover:bg-black/10"
                        title="View all items"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-white">
                                    Items / Stock
                                </p>

                                <p class="mt-1 text-lg font-bold leading-none text-white">
                                    {{ number_format($totalItems ?? 0) }} items
                                </p>
                            </div>

                            <svg
                                class="h-5 w-5 text-white/70 transition
                                       group-hover:translate-x-1 group-hover:text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </div>

                        <p class="mt-2 text-[10px] text-white/80">
                            Total quantity:
                            <strong>{{ number_format($totalStockQty ?? 0) }}</strong>
                        </p>
                    </a>

                    <div class="grid grid-cols-3 border-t border-white/20 text-center">
                        <a
                            href="{{ route('items.index', ['stock_status' => 'in_stock']) }}"
                            class="border-r border-white/20 px-2 py-2
                                   text-white transition hover:bg-black/10"
                            title="View healthy-stock items"
                        >
                            <span class="block text-[10px] text-white/75">Healthy</span>
                            <span class="mt-1 block text-sm font-bold">
                                {{ number_format($healthyStockCount ?? 0) }}
                            </span>
                        </a>

                        <a
                            href="{{ route('items.index', ['stock_status' => 'low_stock']) }}"
                            class="border-r border-white/20 px-2 py-2
                                   text-white transition hover:bg-black/10"
                            title="View low-stock items"
                        >
                            <span class="block text-[10px] text-white/75">Low</span>
                            <span class="mt-1 block text-sm font-bold text-red-100">
                                {{ number_format($lowStockCount ?? 0) }}
                            </span>
                        </a>

                        <a
                            href="{{ route('items.index', ['stock_status' => 'out_of_stock']) }}"
                            class="px-2 py-2 text-white transition hover:bg-black/10"
                            title="View out-of-stock items"
                        >
                            <span class="block text-[10px] text-white/75">Out</span>
                            <span class="mt-1 block text-sm font-bold text-red-100">
                                {{ number_format($outOfStockCount ?? 0) }}
                            </span>
                        </a>
                    </div>
                </div>
            @endunless




        </div>



        {{-- DASHBOARD CHARTS --}}
        <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            {{-- Sales vs Purchase Trend --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900 xl:col-span-2 sm:p-5">
                <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">
                            Sales & Purchase Trend
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Selected date range ka daily comparison
                        </p>
                    </div>

                    <span class="mt-2 inline-flex w-fit rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 sm:mt-0">
                        {{ count($chartLabels ?? []) }} day(s)
                    </span>
                </div>

                <div class="relative h-[280px] w-full sm:h-[330px]">
                    <canvas id="salesPurchaseTrendChart"></canvas>
                </div>
            </div>

            {{-- Payment Collection Doughnut --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900 sm:p-5">
                <div class="mb-4">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">
                        Payment Collection
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Collected amount vs pending amount
                    </p>
                </div>

                <div class="relative mx-auto h-[220px] max-w-[280px]">
                    <canvas id="paymentCollectionChart"></canvas>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-center">
                    <div class="rounded-xl bg-emerald-50 p-3 dark:bg-emerald-950/30">
                        <p class="text-[11px] font-semibold uppercase text-emerald-700 dark:text-emerald-300">
                            Collected
                        </p>
                        <p class="mt-1 text-sm font-bold text-emerald-800 dark:text-emerald-200">
                            ₹ {{ number_format($filteredCollectedAmount ?? 0, 2) }}
                        </p>
                    </div>

                    {{-- <div class="rounded-xl bg-rose-50 p-3 dark:bg-rose-950/30">
                        <p class="text-[11px] font-semibold uppercase text-rose-700 dark:text-rose-300">
                            Pending
                        </p>
                        <p class="mt-1 text-sm font-bold text-rose-800 dark:text-rose-200">
                            ₹ {{ number_format($filteredPendingAmount ?? 0, 2) }}
                        </p>
                    </div> --}}

                    <a
                        href="{{ route('invoices.index', [
                            'type'      => 'tax',
                            'status'    => 'pending',
                            'from_date' => isset($from)
                                ? \Carbon\Carbon::parse($from)->toDateString()
                                : null,
                            'to_date'   => isset($to)
                                ? \Carbon\Carbon::parse($to)->toDateString()
                                : null,
                        ]) }}"
                        class="group rounded-xl bg-rose-50 p-3
                            text-center transition
                            hover:bg-rose-100 hover:shadow-sm
                            dark:bg-rose-950/30
                            dark:hover:bg-rose-950/50"
                        title="View pending invoices for selected date range"
                    >
                        <div class="flex items-center justify-center gap-1">
                            <p
                                class="text-[11px] font-semibold uppercase
                                    text-rose-700 dark:text-rose-300"
                            >
                                Pending
                            </p>

                            <svg
                                class="h-3.5 w-3.5 text-rose-600 transition
                                    group-hover:translate-x-0.5
                                    dark:text-rose-300"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </div>

                        <p
                            class="mt-1 text-sm font-bold
                                text-rose-800 dark:text-rose-200"
                        >
                            ₹ {{ number_format($filteredPendingAmount ?? 0, 2) }}
                        </p>
                    </a>
                </div>
            </div>
        </section>

        @unless($isServiceBusiness ?? false)
            {{-- STOCK STATUS CHART --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm
                            dark:border-neutral-700 dark:bg-neutral-900 sm:p-5">
                <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">
                            Stock Status Overview
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Healthy, low-stock aur out-of-stock items
                        </p>
                    </div>

                    <a
                        href="{{ route('items.index') }}"
                        class="mt-2 text-xs font-semibold text-indigo-600
                               hover:underline dark:text-indigo-400 sm:mt-0"
                    >
                        Manage stock
                    </a>
                </div>

                <div class="relative h-[240px] w-full sm:h-[280px]">
                    <canvas id="stockStatusChart"></canvas>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                    <a
                        href="{{ route('items.index', ['stock_status' => 'in_stock']) }}"
                        class="rounded-xl bg-emerald-50 p-3 transition
                               hover:bg-emerald-100 hover:shadow-sm
                               dark:bg-emerald-950/30 dark:hover:bg-emerald-950/50"
                    >
                        <p class="text-[11px] font-semibold uppercase
                                  text-emerald-700 dark:text-emerald-300">
                            Healthy
                        </p>
                        <p class="mt-1 text-sm font-bold
                                  text-emerald-800 dark:text-emerald-200">
                            {{ number_format($healthyStockCount ?? 0) }}
                        </p>
                    </a>

                    <a
                        href="{{ route('items.index', ['stock_status' => 'low_stock']) }}"
                        class="rounded-xl bg-amber-50 p-3 transition
                               hover:bg-amber-100 hover:shadow-sm
                               dark:bg-amber-950/30 dark:hover:bg-amber-950/50"
                    >
                        <p class="text-[11px] font-semibold uppercase
                                  text-amber-700 dark:text-amber-300">
                            Low Stock
                        </p>
                        <p class="mt-1 text-sm font-bold
                                  text-amber-800 dark:text-amber-200">
                            {{ number_format($lowStockCount ?? 0) }}
                        </p>
                    </a>

                    <a
                        href="{{ route('items.index', ['stock_status' => 'out_of_stock']) }}"
                        class="rounded-xl bg-rose-50 p-3 transition
                               hover:bg-rose-100 hover:shadow-sm
                               dark:bg-rose-950/30 dark:hover:bg-rose-950/50"
                    >
                        <p class="text-[11px] font-semibold uppercase
                                  text-rose-700 dark:text-rose-300">
                            Out of Stock
                        </p>
                        <p class="mt-1 text-sm font-bold
                                  text-rose-800 dark:text-rose-200">
                            {{ number_format($outOfStockCount ?? 0) }}
                        </p>
                    </a>
                </div>
            </section>
        @endunless

        {{-- SECOND ROW: TOTALS + RATES + FORM BUTTON --}}
        <div class="flex flex-wrap gap-4">

            {{-- Lifetime Sales --}}
            <div class="w-full sm:w-1/2 xl:w-1/3">
                <div class="bg-green-600 dark:bg-[#54696C] rounded-xl border border-grey-200 dark:border  shadow-sm px-4 py-3 h-full">
                    <p class="text-xs font-medium text-gray-50 dark:text-gray-50 uppercase tracking-wide">
                        Total Sales (All Time)
                    </p>
                    <p class="mt-2 text-xl font-bold text-gray-50 dark:text-white">
                        ₹ {{ number_format($totalSalesAmount ?? 0, 2) }}
                    </p>
                </div>
            </div>

            {{-- Lifetime Purchases --}}
            <div class="w-full sm:w-1/2 xl:w-1/3">
                <div class="bg-[#1E90FF] dark:bg-[#E6F7F1] rounded-xl border border-grey-900 dark:border  shadow-sm px-4 py-3 h-full">
                    <p class="text-xs font-medium text-gray-50 dark:text-gray-500 uppercase tracking-wide">
                        Total Purchases (All Time)
                    </p>
                    <p class="mt-2 text-xl font-bold text-gray-50 dark:text-black">
                        ₹ {{ number_format($totalPurchasesAmount ?? 0, 2) }}
                    </p>
                </div>
            </div>

            {{-- @if($business->type == 'jewellery') --}}
            @can('show metal rates')
                
            {{-- Today Metal Rates + button --}}
            <div class="w-full xl:w-1/3">
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-red-200 dark:border p-3 dark:border-neutral-700 shadow-sm px-4 py-3 h-full">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            Today Metal Rates
                        </p>

                        <button type="button"
                                @click="showRatesForm = !showRatesForm"
                                class="inline-flex items-center px-3 py-1.5 rounded text-xs font-medium
                                       bg-indigo-600 text-white hover:bg-indigo-700">
                            Set Metal Rates
                        </button>
                    </div>

                    {{-- Existing rates list --}}
                    @if(isset($todayMetalRates) && $todayMetalRates->count())
                        <div class="mt-1 space-y-1 text-xs text-gray-700 dark:text-gray-200">
                            @foreach($todayMetalRates as $rate)
                                <div class="flex items-center justify-between">
                                    <span>
                                        {{ strtoupper($rate->metal_type) }}
                                        @if($rate->purity)
                                            - {{ $rate->purity }}
                                        @endif
                                    </span>
                                    <span class="font-semibold">
                                        ₹ {{ number_format($rate->rate_per_gram ?? 0, 2) }}/gm
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            No active rates set for today.
                        </p>
                    @endif
                </div>
            </div>
            {{-- @endif --}}
            @endcan

        </div>

        {{-- METAL RATES FORM (toggle) --}}
        <div x-show="showRatesForm"
             x-transition
             class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm px-4 py-4">

            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                    Set Today Metal Rates (per gram)
                </h2>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Date: {{ isset($today) ? $today->format('d M Y') : now()->format('d M Y') }}
                </span>
            </div>

            <form method="POST" action="{{ route('metal-rates.store-today') }}" class="space-y-4">
                @csrf

                {{-- GOLD RATES --}}
                <div>
                    <h3 class="text-xs font-semibold text-amber-700 dark:text-amber-300 uppercase mb-2">
                        Gold Rates (₹/gm)
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        @foreach($goldPurities as $purity)
                            @php
                                $key = 'gold|'.$purity;
                                $value = $rateMap[$key] ?? '';
                            @endphp
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    {{ $purity }}
                                </label>
                                <input type="number" step="0.01" min="0"
                                       name="rates[gold][{{ $purity }}]"
                                       value="{{ old('rates.gold.'.$purity, $value) }}"
                                       class="w-full border rounded px-2 py-1.5 text-sm
                                              dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100" />
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ADD MORE GOLD PURITIES --}}
                <div x-data="{ goldRows: [] }" class="mt-3">

                    <h4 class="text-xs font-semibold text-amber-700 dark:text-amber-300 mb-1">
                        Add Custom Gold Purity
                    </h4>

                    <template x-for="(row, index) in goldRows" :key="index">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-2">

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Custom Purity
                                </label>
                                <input type="text"
                                       x-model="row.purity"
                                       placeholder="e.g. 23K"
                                       name="custom[gold][purity][]"
                                       class="w-full border rounded px-2 py-1.5 text-sm
                              dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Rate (₹/gm)
                                </label>
                                <input type="number" step="0.01" min="0"
                                       x-model="row.rate"
                                       name="custom[gold][rate][]"
                                       class="w-full border rounded px-2 py-1.5 text-sm
                              dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100">
                            </div>

                            <button type="button"
                                    @click="goldRows.splice(index, 1)"
                                    class="self-end text-xs bg-red-500 text-white px-2 py-1 rounded">
                                Remove
                            </button>
                        </div>
                    </template>

                    <button type="button"
                            @click="goldRows.push({ purity:'', rate:'' })"
                            class="mt-1 px-3 py-1.5 rounded text-xs font-medium
                   bg-amber-600 text-white hover:bg-amber-700">
                        + Add More Gold Purity
                    </button>
                </div>


                {{-- SILVER RATES --}}
                <div>
                    <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-2">
                        Silver Rates (₹/gm)
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        @foreach($silverPurities as $purity)
                            @php
                                $key = 'silver|'.$purity;
                                $value = $rateMap[$key] ?? '';
                            @endphp
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    {{ $purity }}
                                </label>
                                <input type="number" step="0.01" min="0"
                                       name="rates[silver][{{ $purity }}]"
                                       value="{{ old('rates.silver.'.$purity, $value) }}"
                                       class="w-full border rounded px-2 py-1.5 text-sm
                                              dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100" />
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ADD MORE SILVER PURITIES --}}
                <div x-data="{ silverRows: [] }" class="mt-3">

                    <h4 class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Add Custom Silver Purity
                    </h4>

                    <template x-for="(row, index) in silverRows" :key="index">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-2">

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Custom Purity
                                </label>
                                <input type="text"
                                       x-model="row.purity"
                                       placeholder="e.g. 999"
                                       name="custom[silver][purity][]"
                                       class="w-full border rounded px-2 py-1.5 text-sm
                              dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Rate (₹/gm)
                                </label>
                                <input type="number" step="0.01" min="0"
                                       x-model="row.rate"
                                       name="custom[silver][rate][]"
                                       class="w-full border rounded px-2 py-1.5 text-sm
                              dark:bg-neutral-900 dark:border-neutral-700 dark:text-gray-100">
                            </div>

                            <button type="button"
                                    @click="silverRows.splice(index, 1)"
                                    class="self-end text-xs bg-red-500 text-white px-2 py-1 rounded">
                                Remove
                            </button>
                        </div>
                    </template>

                    <button type="button"
                            @click="silverRows.push({ purity:'', rate:'' })"
                            class="mt-1 px-3 py-1.5 rounded text-xs font-medium
                   bg-slate-700 text-white hover:bg-slate-800">
                        + Add More Silver Purity
                    </button>
                </div>


                <div class="flex items-center justify-end gap-2 pt-2 border-t border-dashed border-gray-200 dark:border-neutral-700">
                    <button type="button"
                            @click="showRatesForm = false"
                            class="px-3 py-1.5 rounded text-xs border text-gray-600 dark:text-gray-200
                                   bg-white dark:bg-neutral-900 hover:bg-gray-50 dark:hover:bg-neutral-800">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-1.5 rounded text-xs font-semibold
                                   bg-indigo-600 text-white hover:bg-indigo-700">
                        Save Rates
                    </button>
                </div>
            </form>
        </div>

        {{-- LOWER SECTION: RECENT SALES & PURCHASES --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- Recent Sales --}}
            <div class="bg-[#BFE0E0] dark:bg-[#354A54] rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                        Recent Sales
                    </h2>
                    <a href="{{ route('invoices.index') }}"
                       class="text-xs text-[#FA5252] dark:text-red-700 hover:underline">
                        View all
                    </a>
                </div>

                <div class="overflow-auto">
                    <table class="min-w-full text-xs text-left text-gray-700 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-neutral-800">
                        <tr>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Invoice</th>
                            <th class="px-4 py-2">Client</th>
                            <th class="px-4 py-2 text-right">Amount</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 dark:bg-neutral-900 dark:divide-neutral-700">
                        @forelse($recentInvoices ?? [] as $inv)
                            <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                                <td class="px-4 py-2">
                                    {{ \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y') }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $inv->invoice_number ?? $inv->id }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $inv->client->name ?? '—' }}
                                </td>
                                <td class="px-4 py-2 text-right font-semibold">
                                    ₹ {{ number_format($inv->total ?? 0, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No recent sales found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Purchases --}}
            <div class="bg-[#BFE0E0] dark:bg-[#354A54] rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                        Recent Purchases
                    </h2>
                    <a href="{{ route('purchases.index') }}"
                       class="text-xs text-[#FA5252] dark:text-red-700 hover:underline">
                        View all
                    </a>
                </div>

                <div class="overflow-auto">
                    <table class="min-w-full text-xs text-left text-gray-700 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-neutral-800">
                        <tr>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Invoice No</th>
                            <th class="px-4 py-2">Supplier</th>
                            <th class="px-4 py-2 text-right">Amount</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 dark:bg-neutral-900 dark:divide-neutral-700">
                        @forelse($recentPurchases ?? [] as $pur)
                            <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                                <td class="px-4 py-2">
                                    {{ $pur->invoice_date
                                        ? \Carbon\Carbon::parse($pur->invoice_date)->format('d M Y')
                                        : '—' }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $pur->invoice_no ?? $pur->id }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $pur->supplier->name ?? '—' }}
                                </td>
                                <td class="px-4 py-2 text-right font-semibold">
                                    ₹ {{ number_format($pur->total_amount ?? 0, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No recent purchases found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @unless($isServiceBusiness ?? false)
        {{-- LOW STOCK ITEMS --}}
        <div class="bg-[#BFE0E0] dark:bg-[#354A54] rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                    Low Stock Items
                </h2>
                <a href="{{ route('items.index', ['stock_status' => 'low_stock']) }}"
                   class="text-xs text-[#FA5252] dark:text-red-700 hover:underline">
                    Manage low stock
                </a>
            </div>

            <div class="overflow-auto">
                <table class="min-w-full text-xs text-left text-gray-700 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-neutral-800">
                    <tr>
                        <th class="px-4 py-2">Item</th>
                        <th class="px-4 py-2">SKU</th>
                        <th class="px-4 py-2">Category</th>
                        <th class="px-4 py-2 text-right">Stock</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100 dark:bg-neutral-900 dark:divide-neutral-700">
                    @forelse($lowStockItems ?? [] as $it)
                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                            <td class="px-4 py-2">
                                {{ $it->name }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $it->sku ?? '—' }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $it->category->name ?? '—' }}
                            </td>
                            <td class="px-4 py-2 text-right font-semibold text-red-600 dark:text-red-400">
                                {{ $it->stock_qty }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                No low stock items found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endunless

    </div>



    @if($showDashboardSuggestion ?? false)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                initializeDashboardSetupGuide();
            });

            function dashboardSetupGuideElements() {
                return {
                    guide: document.getElementById('dashboardSetupGuide'),
                    reopen: document.getElementById('dashboardSetupGuideReopen')
                };
            }

            function initializeDashboardSetupGuide() {
                const elements = dashboardSetupGuideElements();

                if (!elements.guide) {
                    return;
                }

                const storageKey = elements.guide.dataset.storageKey;
                const dismissed = storageKey
                    ? localStorage.getItem(storageKey) === '1'
                    : false;

                if (dismissed) {
                    elements.guide.classList.add('hidden');

                    if (elements.reopen) {
                        elements.reopen.classList.remove('hidden');
                        elements.reopen.classList.add('flex');
                    }

                    return;
                }

                elements.guide.classList.remove('hidden');

                if (elements.reopen) {
                    elements.reopen.classList.add('hidden');
                    elements.reopen.classList.remove('flex');
                }
            }

            function dismissDashboardSetupGuide() {
                const elements = dashboardSetupGuideElements();

                if (!elements.guide) {
                    return;
                }

                const storageKey = elements.guide.dataset.storageKey;

                if (storageKey) {
                    localStorage.setItem(storageKey, '1');
                }

                elements.guide.classList.add('hidden');

                if (elements.reopen) {
                    elements.reopen.classList.remove('hidden');
                    elements.reopen.classList.add('flex');
                }
            }

            function showDashboardSetupGuide() {
                const elements = dashboardSetupGuideElements();

                if (!elements.guide) {
                    return;
                }

                const storageKey = elements.guide.dataset.storageKey;

                if (storageKey) {
                    localStorage.removeItem(storageKey);
                }

                elements.guide.classList.remove('hidden');

                if (elements.reopen) {
                    elements.reopen.classList.add('hidden');
                    elements.reopen.classList.remove('flex');
                }

                elements.guide.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        </script>
    @endif

    {{-- CHART.JS: wire:navigate compatible --}}
    <script data-navigate-once src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <script>
        (() => {
            const chartData = {
                labels: @json($chartLabels ?? []),
                sales: @json($salesChartData ?? []),
                purchases: @json($purchaseChartData ?? []),
                payments: @json($paymentChartData ?? [0, 0]),
                stock: @json($stockChartData ?? [0, 0, 0]),
            };

            function destroyDashboardCharts() {
                [
                    'salesPurchaseTrendChart',
                    'paymentCollectionChart',
                    'stockStatusChart'
                ].forEach((canvasId) => {
                    const canvas = document.getElementById(canvasId);

                    if (!canvas || typeof Chart === 'undefined') {
                        return;
                    }

                    const existingChart = Chart.getChart(canvas);

                    if (existingChart) {
                        existingChart.destroy();
                    }
                });
            }

            function initializeDashboardCharts() {
                if (typeof Chart === 'undefined') {
                    return;
                }

                const trendCanvas = document.getElementById('salesPurchaseTrendChart');
                const paymentCanvas = document.getElementById('paymentCollectionChart');
                const stockCanvas = document.getElementById('stockStatusChart');

                // Dashboard DOM abhi page par nahi hai.
                if (!trendCanvas && !paymentCanvas && !stockCanvas) {
                    return;
                }

                destroyDashboardCharts();

                const isDarkMode = document.documentElement.classList.contains('dark');
                const textColor = isDarkMode ? '#d4d4d8' : '#4b5563';
                const gridColor = isDarkMode
                    ? 'rgba(115, 115, 115, 0.22)'
                    : 'rgba(209, 213, 219, 0.55)';

                Chart.defaults.color = textColor;
                Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, sans-serif';

                const currencyFormatter = (value) => {
                    return '₹ ' + new Intl.NumberFormat('en-IN', {
                        maximumFractionDigits: 2
                    }).format(Number(value || 0));
                };

                const commonTooltip = {
                    backgroundColor: isDarkMode ? '#171717' : '#111827',
                    padding: 12,
                    cornerRadius: 10,
                    titleSpacing: 6,
                    bodySpacing: 6
                };

                if (trendCanvas) {
                    new Chart(trendCanvas, {
                        type: 'line',
                        data: {
                            labels: chartData.labels,
                            datasets: [
                                {
                                    label: 'Sales',
                                    data: chartData.sales,
                                    borderColor: '#4f46e5',
                                    backgroundColor: 'rgba(79, 70, 229, 0.12)',
                                    fill: true,
                                    tension: 0.35,
                                    borderWidth: 2.5,
                                    pointRadius: 3,
                                    pointHoverRadius: 5
                                },
                                {
                                    label: 'Purchases',
                                    data: chartData.purchases,
                                    borderColor: '#f59e0b',
                                    backgroundColor: 'rgba(245, 158, 11, 0.08)',
                                    fill: true,
                                    tension: 0.35,
                                    borderWidth: 2.5,
                                    pointRadius: 3,
                                    pointHoverRadius: 5
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'end',
                                    labels: {
                                        usePointStyle: true,
                                        boxWidth: 8,
                                        padding: 18
                                    }
                                },
                                tooltip: {
                                    ...commonTooltip,
                                    callbacks: {
                                        label(context) {
                                            return context.dataset.label + ': ' + currencyFormatter(context.raw);
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: {
                                        maxRotation: 0,
                                        autoSkip: true,
                                        maxTicksLimit: 10
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: { color: gridColor },
                                    ticks: {
                                        callback(value) {
                                            return currencyFormatter(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                if (paymentCanvas) {
                    const paymentValues = chartData.payments.map(Number);
                    const hasPaymentData = paymentValues.some((value) => value > 0);

                    new Chart(paymentCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: hasPaymentData
                                ? ['Collected', 'Pending']
                                : ['No data'],
                            datasets: [{
                                data: hasPaymentData ? paymentValues : [1],
                                backgroundColor: hasPaymentData
                                    ? ['#10b981', '#f43f5e']
                                    : ['#d1d5db'],
                                borderWidth: 0,
                                hoverOffset: 7
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '68%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 18
                                    }
                                },
                                tooltip: {
                                    ...commonTooltip,
                                    callbacks: {
                                        label(context) {
                                            if (!hasPaymentData) {
                                                return 'No payment data';
                                            }

                                            return context.label + ': ' + currencyFormatter(context.raw);
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                if (stockCanvas) {
                    new Chart(stockCanvas, {
                        type: 'bar',
                        data: {
                            labels: ['Healthy Stock', 'Low Stock', 'Out of Stock'],
                            datasets: [{
                                label: 'Items',
                                data: chartData.stock,
                                backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
                                borderRadius: 10,
                                borderSkipped: false,
                                maxBarThickness: 72
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    ...commonTooltip,
                                    callbacks: {
                                        label(context) {
                                            return context.raw + ' item(s)';
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0 },
                                    grid: { color: gridColor }
                                }
                            }
                        }
                    });
                }
            }

            function bootDashboardCharts() {
                // wire:navigate ke DOM replacement ko complete hone dein.
                window.requestAnimationFrame(() => {
                    window.requestAnimationFrame(initializeDashboardCharts);
                });
            }

            // Normal refresh.
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootDashboardCharts, { once: true });
            } else {
                bootDashboardCharts();
            }

            // Flux/Livewire wire:navigate ke baad.
            document.addEventListener('livewire:navigated', bootDashboardCharts);

            // Page leave hone se pehle old Chart instances clear karein.
            document.addEventListener('livewire:navigating', destroyDashboardCharts);
        })();
    </script>

</x-layouts.app>