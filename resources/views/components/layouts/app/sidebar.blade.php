<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">

    @php
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Active Business
        |--------------------------------------------------------------------------
        */

        $activeBusinessId =
            session('active_business_id')
            ?? $user?->current_business_id
            ?? $user?->businesses?->first()?->id;

        $business = $activeBusinessId
            ? \App\Models\Business::find($activeBusinessId)
            : null;

        /*
        |--------------------------------------------------------------------------
        | Profile Completion
        |--------------------------------------------------------------------------
        */

        $profileIncomplete = false;

        if ($business && method_exists($business, 'isProfileIncomplete')) {
            $profileIncomplete = $business->isProfileIncomplete();
        }

        $profileCompletion = (int) ($business?->profile_completion ?? 0);
        $profileCompletion = max(0, min(100, $profileCompletion));

        /*
        |--------------------------------------------------------------------------
        | Business Details
        |--------------------------------------------------------------------------
        */

        $businessName = $business?->name ?: config('app.name', 'Billing Software');

        $businessInitial = \Illuminate\Support\Str::upper(
            \Illuminate\Support\Str::substr(trim($businessName), 0, 1)
        );

        if ($businessInitial === '') {
            $businessInitial = 'B';
        }

        /*
        |--------------------------------------------------------------------------
        | Business Logo
        |--------------------------------------------------------------------------
        |
        | businesses table me logo column ka naam "logo" maana gaya hai.
        |
        */

        $businessLogo = null;

        if ($business && filled($business->logo)) {
            $storedLogo = trim((string) $business->logo);

            if (
                \Illuminate\Support\Str::startsWith(
                    $storedLogo,
                    ['http://', 'https://']
                )
            ) {
                $businessLogo = $storedLogo;
            } elseif (
                \Illuminate\Support\Str::startsWith(
                    $storedLogo,
                    ['/storage/', 'storage/']
                )
            ) {
                $businessLogo = asset(ltrim($storedLogo, '/'));
            } elseif (
                \Illuminate\Support\Str::startsWith(
                    $storedLogo,
                    ['/uploads/', 'uploads/', '/images/', 'images/']
                )
            ) {
                $businessLogo = asset(ltrim($storedLogo, '/'));
            } else {
                $businessLogo = \Illuminate\Support\Facades\Storage::disk('public')
                    ->url($storedLogo);
            }
        }
    @endphp

    <flux:sidebar
        sticky
        stashable
        class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900"
    >
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        {{-- ============================================================= --}}
        {{-- ACTIVE BUSINESS LOGO --}}
        {{-- ============================================================= --}}

        <a
            href="{{ route('dashboard') }}"
            wire:navigate
            class="group mb-3 flex items-center gap-3 rounded-xl border border-transparent px-2 py-2 transition-all duration-200 hover:border-zinc-200 hover:bg-white hover:shadow-sm dark:hover:border-zinc-700 dark:hover:bg-zinc-800"
        >
            <div
                class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
            >
                @if($businessLogo)
                    <img
                        src="{{ $businessLogo }}"
                        alt="{{ $businessName }} Logo"
                        class="h-full w-full object-contain p-1"
                        loading="eager"
                        onerror="
                            this.style.display='none';
                            this.nextElementSibling.style.display='flex';
                        "
                    >

                    <div
                        class="hidden h-full w-full items-center justify-center bg-gradient-to-br from-indigo-500 to-violet-600 text-lg font-black text-white"
                    >
                        {{ $businessInitial }}
                    </div>
                @else
                    <div
                        class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-500 to-violet-600 text-lg font-black text-white"
                    >
                        {{ $businessInitial }}
                    </div>
                @endif
            </div>

            <div class="min-w-0 flex-1">
                <p
                    class="truncate text-sm font-bold text-zinc-900 dark:text-white"
                    title="{{ $businessName }}"
                >
                    {{ $businessName }}
                </p>

                <div class="mt-0.5 flex items-center gap-1.5">
                    @if($business)
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500"></span>

                        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                            Active Business
                        </p>
                    @else
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500"></span>

                        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                            No business selected
                        </p>
                    @endif
                </div>
            </div>
        </a>

        {{-- ============================================================= --}}
        {{-- DASHBOARD --}}
        {{-- ============================================================= --}}

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Platform')" class="grid">

                <flux:navlist.item
                    :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')"
                    wire:navigate
                >
                    {{ __('Dashboard') }}

                    <x-slot:icon>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 64 64"
                        >
                            <rect x="6" y="6" width="52" height="52" rx="10" fill="#EEF2FF" />
                            <rect x="14" y="14" width="36" height="8" rx="4" fill="#6366F1" />

                            <rect x="14" y="28" width="14" height="12" rx="4" fill="#22C55E" />
                            <rect x="32" y="28" width="18" height="12" rx="4" fill="#F59E0B" />

                            <rect x="18" y="46" width="4" height="8" rx="2" fill="#6366F1" />
                            <rect x="26" y="44" width="4" height="10" rx="2" fill="#22C55E" />
                            <rect x="34" y="42" width="4" height="12" rx="2" fill="#EF4444" />
                            <rect x="42" y="45" width="4" height="9" rx="2" fill="#0EA5E9" />
                        </svg>
                    </x-slot:icon>
                </flux:navlist.item>

            </flux:navlist.group>
        </flux:navlist>

        {{-- ============================================================= --}}
        {{-- BUSINESS PROFILE --}}
        {{-- ============================================================= --}}

        @if($business)
            <div class="relative mt-3">
                @if($profileIncomplete)
                    <span
                        class="absolute -right-1 -top-1 z-20 flex h-3 w-3"
                        aria-hidden="true"
                    >
                        <span
                            class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"
                        ></span>

                        <span
                            class="relative inline-flex h-3 w-3 rounded-full bg-amber-500"
                        ></span>
                    </span>
                @endif

                <a
                    href="{{ route('business-profile.index') }}"
                    wire:navigate
                    class="
                        group relative flex items-center gap-3 rounded-xl
                        border px-3 py-3 transition-all duration-300

                        {{ request()->routeIs('business-profile.*')
                            ? 'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300'
                            : 'border-zinc-200 bg-white text-zinc-700 hover:border-indigo-300 hover:bg-indigo-50/60 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800'
                        }}

                        {{ $profileIncomplete
                            ? 'business-profile-attention'
                            : ''
                        }}
                    "
                >
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                        {{ $profileIncomplete
                            ? 'bg-amber-100 text-amber-600 dark:bg-amber-950/50'
                            : 'bg-indigo-100 text-indigo-600 dark:bg-indigo-950/50'
                        }}"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 21h18M5 21V7l7-4 7 4v14M9 9h2m2 0h2M9 13h2m2 0h2M9 17h6"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate text-sm font-semibold">
                                Business Profile
                            </span>

                            @if($profileIncomplete)
                                <span
                                    class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-950/60 dark:text-amber-300"
                                >
                                    Setup
                                </span>
                            @else
                                <svg
                                    class="h-4 w-4 text-emerald-500"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.704 5.29a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3.25-3.25a1 1 0 011.414-1.414l2.543 2.543 6.543-6.543a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            @endif
                        </div>

                        @if($profileIncomplete)
                            <div class="mt-2">
                                <div class="mb-1 flex items-center justify-between text-[10px]">
                                    <span class="text-zinc-500">
                                        Profile completion
                                    </span>

                                    <span class="font-bold text-amber-600">
                                        {{ $profileCompletion }}%
                                    </span>
                                </div>

                                <div class="h-1.5 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                    <div
                                        class="h-full rounded-full bg-amber-500 transition-all duration-500"
                                        style="width: {{ $profileCompletion }}%"
                                    ></div>
                                </div>
                            </div>
                        @else
                            <p class="mt-0.5 truncate text-xs text-zinc-500">
                                Profile setup complete
                            </p>
                        @endif
                    </div>
                </a>
            </div>
        @endif

        {{-- ============================================================= --}}
        {{-- BUSINESS SWITCH --}}
        {{-- ============================================================= --}}

        @can('view all businesses')
            <form
                action="{{ route('business.switch') }}"
                method="POST"
                class="mt-3 block w-full"
            >
                @csrf

                <select
                    name="business_id"
                    onchange="this.form.submit()"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm transition-all duration-200 hover:border-blue-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white"
                >
                    @forelse($user->businesses as $b)
                        <option
                            value="{{ $b->id }}"
                            @selected((string) $activeBusinessId === (string) $b->id)
                        >
                            {{ $b->name }}
                        </option>
                    @empty
                        <option value="">No business attached</option>
                    @endforelse
                </select>
            </form>
        @endcan

        {{-- ============================================================= --}}
        {{-- INVOICES --}}
        {{-- ============================================================= --}}


         @can('create invoice')
            <flux:navlist.item
                icon="plus"
                :href="route('invoices.create', ['type' => 'tax'])"
                :current="request()->routeIs('invoices.create')"
                wire:navigate
            >
                Create Invoice
            </flux:navlist.item>
        @endcan

        @can('show invoices menu')
            @php
                $type = request('type');
                $isInvoiceRoute = request()->routeIs('invoices.*');
            @endphp

            <details class="group {{ $isInvoiceRoute ? 'open' : '' }}">
                @if($isInvoiceRoute)
                    <script>
                        document.currentScript.parentElement.setAttribute('open', 'open');
                    </script>
                @endif

                <summary
                    class="flex cursor-pointer list-none select-none items-center justify-between rounded-lg px-3 py-2 text-sm font-medium
                    {{ $isInvoiceRoute
                        ? 'bg-white/15 text-white'
                        : 'text-zinc-300 hover:bg-white/10 hover:text-white'
                    }}
                    focus:outline-none focus:ring-2 focus:ring-white/20"
                >
                    <span class="flex items-center gap-2">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-white/10 text-white">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="64"
                                height="64"
                                viewBox="0 0 64 64"
                            >
                                <rect x="8" y="4" width="40" height="56" rx="4" fill="#4F46E5" />
                                <polygon points="40,4 56,20 40,20" fill="#6366F1" />
                                <rect x="14" y="14" width="36" height="44" rx="3" fill="#EEF2FF" />

                                <rect x="18" y="20" width="28" height="4" rx="2" fill="#6366F1" />
                                <rect x="18" y="28" width="28" height="3" rx="1.5" fill="#9CA3AF" />
                                <rect x="18" y="34" width="28" height="3" rx="1.5" fill="#9CA3AF" />
                                <rect x="18" y="40" width="20" height="3" rx="1.5" fill="#9CA3AF" />

                                <circle cx="44" cy="48" r="8" fill="#22C55E" />

                                <text
                                    x="44"
                                    y="52"
                                    text-anchor="middle"
                                    font-size="10"
                                    fill="#ffffff"
                                    font-family="Arial, sans-serif"
                                >
                                    ₹
                                </text>
                            </svg>
                        </span>

                        <span class="text-black dark:text-white">
                            Invoices
                        </span>
                    </span>

                    <svg
                        class="h-4 w-4 text-black transition-transform duration-200 group-open:rotate-180 dark:text-white"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </summary>

                <div class="mt-1 ml-2 space-y-1 border-l border-white/10 pl-2">
                    <flux:navlist variant="outline">
                        <flux:navlist.group class="grid">

                            @can('show quotations')
                                <flux:navlist.item
                                    icon="document-text"
                                    :href="route('invoices.index', ['type' => 'quotation'])"
                                    :current="$isInvoiceRoute && $type === 'quotation'"
                                    wire:navigate
                                >
                                    Quotation
                                </flux:navlist.item>
                            @endcan

                            @can('create invoice')
                                <flux:navlist.item
                                    icon="plus"
                                    :href="route('invoices.create', ['type' => 'tax'])"
                                    :current="request()->routeIs('invoices.create') && $type === 'tax'"
                                    wire:navigate
                                >
                                    Create Invoice
                                </flux:navlist.item>
                            @endcan

                            @can('show invoices')
                                <flux:navlist.item
                                    icon="list-bullet"
                                    :href="route('invoices.index')"
                                    :current="$isInvoiceRoute && empty($type)"
                                    wire:navigate
                                >
                                    Invoices
                                </flux:navlist.item>
                            @endcan

                            @can('show proformas')
                                <flux:navlist.item
                                    icon="document"
                                    :href="route('invoices.index', ['type' => 'proforma'])"
                                    :current="$isInvoiceRoute && $type === 'proforma'"
                                    wire:navigate
                                >
                                    Proforma
                                </flux:navlist.item>
                            @endcan

                        </flux:navlist.group>
                    </flux:navlist>
                </div>
            </details>
        @endcan

        {{-- ============================================================= --}}
        {{-- INVOICE REPORTS --}}
        {{-- ============================================================= --}}

        @can('download reports')
            <a
                href="{{ route('invoices.reports.page') }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-neutral-200 dark:hover:bg-neutral-800
                {{ request()->routeIs('invoices.reports.page')
                    ? 'bg-gray-100 font-semibold dark:bg-neutral-800'
                    : ''
                }}"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-green-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 16v-8m0 8l-3-3m3 3l3-3M5 20h14"
                    />
                </svg>

                <span>Invoice Reports</span>
            </a>
        @endcan

        {{-- ============================================================= --}}
        {{-- BILL REQUESTS --}}
        {{-- ============================================================= --}}

        @can('show bill requests')
            <a
                href="{{ route('bill-requests.index') }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-neutral-200 dark:hover:bg-neutral-800
                {{ request()->routeIs('bill-requests.*')
                    ? 'bg-gray-100 font-semibold dark:bg-neutral-800'
                    : ''
                }}"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-green-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 16v-8m0 8l-3-3m3 3l3-3M5 20h14"
                    />
                </svg>

                <span>Bill Requests</span>
            </a>
        @endcan

        {{-- ============================================================= --}}
        {{-- BUSINESS TYPES --}}
        {{-- ============================================================= --}}

        @can('show business types')
            <a
                href="{{ route('business-types.index') }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-neutral-200 dark:hover:bg-neutral-800
                {{ request()->routeIs('business-types.*')
                    ? 'bg-gray-100 font-semibold dark:bg-neutral-800'
                    : ''
                }}"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-green-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 16v-8m0 8l-3-3m3 3l3-3M5 20h14"
                    />
                </svg>

                <span>Business Types</span>
            </a>
        @endcan

        {{-- ============================================================= --}}
        {{-- DEMO REQUEST --}}
        {{-- ============================================================= --}}

        @can('show demo requests')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('demo-requests.index')"
                        :current="request()->routeIs('demo-requests.*')"
                        wire:navigate
                    >
                        {{ __('Demo Request') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="8" y="8" width="20" height="40" rx="4" fill="#0EA5E9" />
                                <rect x="32" y="16" width="20" height="32" rx="4" fill="#22C55E" />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- BUSINESSES --}}
        {{-- ============================================================= --}}

        @can('show businesses')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('businesses.index')"
                        :current="request()->routeIs('businesses.*')"
                        wire:navigate
                    >
                        {{ __('Businesses') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="8" y="8" width="20" height="40" rx="4" fill="#0EA5E9" />
                                <rect x="32" y="16" width="20" height="32" rx="4" fill="#22C55E" />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- IMPERSONATION --}}
        {{-- ============================================================= --}}

        @if(session()->has('impersonator_id'))
            <div
                class="mt-3 rounded-xl border border-yellow-300 bg-yellow-100 px-3 py-3 text-yellow-900"
            >
                <div class="text-xs leading-5">
                    You are logged in as
                    <strong>{{ auth()->user()->name }}</strong>
                    from Super Admin account.
                </div>

                <form
                    action="{{ route('impersonate.exit') }}"
                    method="POST"
                    class="mt-2"
                >
                    @csrf

                    <button
                        type="submit"
                        class="w-full rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700"
                    >
                        Exit User
                    </button>
                </form>
            </div>
        @endif

        {{-- ============================================================= --}}
        {{-- API KEYS AND USERS --}}
        {{-- ============================================================= --}}

        @can('show api keys')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('api-keys.index')"
                        :current="request()->routeIs('api-keys*')"
                        wire:navigate
                    >
                        {{ __('Api Key') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#EEF2FF" />
                                <rect x="14" y="14" width="36" height="8" rx="4" fill="#6366F1" />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('users.index')"
                        :current="request()->routeIs('users.*')"
                        wire:navigate
                    >
                        {{ __('Users') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#ECFEFF" />

                                <circle cx="32" cy="26" r="8" fill="#0EA5E9" />
                                <path d="M18 46c0-7 6-12 14-12s14 5 14 12" fill="#38BDF8" />

                                <circle cx="20" cy="28" r="6" fill="#22C55E" />
                                <path d="M10 46c0-5 4-9 10-9" fill="#4ADE80" />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- RECYCLE BIN --}}
        {{-- ============================================================= --}}

        @can('show recycle bin')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('recycle.index')"
                        :current="request()->routeIs('recycle.*')"
                        wire:navigate
                    >
                        {{ __('Recycle Bin') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEE2E2" />

                                <path d="M22 22h20l-2 26H24L22 22z" fill="#EF4444" />
                                <rect x="20" y="18" width="24" height="4" rx="2" fill="#B91C1C" />
                                <rect x="27" y="14" width="10" height="4" rx="2" fill="#F87171" />

                                <line x1="28" y1="28" x2="28" y2="44" stroke="#fff" stroke-width="2" />
                                <line x1="32" y1="28" x2="32" y2="44" stroke="#fff" stroke-width="2" />
                                <line x1="36" y1="28" x2="36" y2="44" stroke="#fff" stroke-width="2" />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- CLIENTS --}}
        {{-- ============================================================= --}}

        @can('show clients')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('clients.index')"
                        :current="request()->routeIs('clients.*')"
                        wire:navigate
                    >
                        {{ __('Clients') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#F0FDF4" />

                                <circle cx="32" cy="26" r="8" fill="#22C55E" />
                                <path d="M18 46c0-7 6-12 14-12s14 5 14 12" fill="#4ADE80" />

                                <polygon
                                    points="46,20 48,24 52,24 49,27 50,31 46,29 42,31 43,27 40,24 44,24"
                                    fill="#F59E0B"
                                />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- BANNER SLIDERS --}}
        {{-- ============================================================= --}}

        @can('show banner sliders')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('banner-sliders.index')"
                        :current="request()->routeIs('banner-sliders.*')"
                        wire:navigate
                    >
                        {{ __('Banner Sliders') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#F0FDF4" />

                                <circle cx="32" cy="26" r="8" fill="#22C55E" />
                                <path d="M18 46c0-7 6-12 14-12s14 5 14 12" fill="#4ADE80" />

                                <polygon
                                    points="46,20 48,24 52,24 49,27 50,31 46,29 42,31 43,27 40,24 44,24"
                                    fill="#F59E0B"
                                />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- CATEGORIES --}}
        {{-- ============================================================= --}}

        @can('show categories')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('categories.index')"
                        :current="request()->routeIs('categories.*')"
                        wire:navigate
                    >
                        {{ __('Categories') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEF3C7" />

                                <path d="M12 20h20l6 6v22H12V20z" fill="#F59E0B" />
                                <path d="M12 20v-4h16l6 6v22H12V20z" fill="#FBBF24" />

                                <rect x="18" y="28" width="16" height="4" rx="1" fill="#FCD34D" />
                                <rect x="18" y="36" width="16" height="4" rx="1" fill="#FCD34D" />
                                <rect x="18" y="44" width="16" height="4" rx="1" fill="#FCD34D" />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- METAL RATES --}}
        {{-- ============================================================= --}}

        @if($business?->type === 'jewellery')
            @can('show metal rates')
                <flux:navlist variant="outline">
                    <flux:navlist.group class="grid">
                        <flux:navlist.item
                            icon="home"
                            :href="route('metal-rates.index')"
                            :current="request()->routeIs('metal-rates.*')"
                            wire:navigate
                        >
                            {{ __('Metal Rates') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
            @endcan
        @endif

        {{-- ============================================================= --}}
        {{-- ITEMS --}}
        {{-- ============================================================= --}}

        @can('show items')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('items.index')"
                        :current="request()->routeIs('items.*')"
                        wire:navigate
                    >
                        {{ __('Items') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#EFF6FF" />

                                <rect x="20" y="20" width="24" height="24" rx="4" fill="#3B82F6" />

                                <path
                                    d="M20 20l12 12 12-12"
                                    stroke="#93C5FD"
                                    stroke-width="2"
                                    fill="none"
                                />

                                <line
                                    x1="32"
                                    y1="32"
                                    x2="32"
                                    y2="44"
                                    stroke="#93C5FD"
                                    stroke-width="2"
                                />

                                <rect x="22" y="46" width="20" height="4" rx="1" fill="#60A5FA" />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- PURCHASES --}}
        {{-- ============================================================= --}}

        @can('show purchases')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('purchases.index')"
                        :current="request()->routeIs('purchases.*')"
                        wire:navigate
                    >
                        {{ __('Purchases') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEF3F2" />

                                <path d="M18 44h28l-4-20H16l2 10" fill="#F87171" />

                                <circle cx="22" cy="48" r="3" fill="#B91C1C" />
                                <circle cx="42" cy="48" r="3" fill="#B91C1C" />

                                <rect x="26" y="28" width="12" height="12" rx="2" fill="#FCA5A5" />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- INVENTORY --}}
        {{-- ============================================================= --}}

        @can('show inventory')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('inventory.summary')"
                        :current="request()->routeIs('inventory.*')"
                        wire:navigate
                    >
                        {{ __('Inventory') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#ECFDF5" />

                                <rect x="16" y="24" width="12" height="12" rx="2" fill="#10B981" />
                                <rect x="36" y="24" width="12" height="12" rx="2" fill="#34D399" />
                                <rect x="26" y="38" width="12" height="12" rx="2" fill="#6EE7B7" />

                                <line
                                    x1="18"
                                    y1="28"
                                    x2="26"
                                    y2="28"
                                    stroke="#FFFFFF"
                                    stroke-width="2"
                                />

                                <line
                                    x1="18"
                                    y1="32"
                                    x2="26"
                                    y2="32"
                                    stroke="#FFFFFF"
                                    stroke-width="2"
                                />

                                <line
                                    x1="38"
                                    y1="28"
                                    x2="46"
                                    y2="28"
                                    stroke="#FFFFFF"
                                    stroke-width="2"
                                />

                                <line
                                    x1="38"
                                    y1="32"
                                    x2="46"
                                    y2="32"
                                    stroke="#FFFFFF"
                                    stroke-width="2"
                                />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- BANK BALANCE --}}
        {{-- ============================================================= --}}

        @can('show bank balance')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        icon="home"
                        :href="route('bank-accounts.index')"
                        :current="request()->routeIs('bank-accounts.*')"
                        wire:navigate
                    >
                        {{ __('Banks & Balance') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- PLAN MANAGEMENT --}}
        {{-- ============================================================= --}}

        @can('show plan')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        icon="home"
                        :href="route('plans.index')"
                        :current="request()->routeIs('plans.*')"
                        wire:navigate
                    >
                        {{ __('Plan management') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- USER PLAN --}}
        {{-- ============================================================= --}}

        @can('show user plan')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        icon="home"
                        :href="route('user-plans.index')"
                        :current="request()->routeIs('user-plans.*')"
                        wire:navigate
                    >
                        {{ __('User Plan') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- BILL TEMPLATES --}}
        {{-- ============================================================= --}}

        @can('show bill templates')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        icon="home"
                        :href="route('bill-templates.index')"
                        :current="request()->routeIs('bill-templates.index')"
                        wire:navigate
                    >
                        {{ __('Bill Template') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- CHOOSE TEMPLATE --}}
        {{-- ============================================================= --}}

        @can('choose templates')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        icon="home"
                        :href="route('bill-templates.choose')"
                        :current="request()->routeIs('bill-templates.choose')"
                        wire:navigate
                    >
                        {{ __('Choose Template') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- ADDITIONAL CHARGES --}}
        {{-- ============================================================= --}}

        @can('show additional charges')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('additional-charges.index')"
                        :current="request()->routeIs('additional-charges.*')"
                        wire:navigate
                    >
                        {{ __('Additional Charge') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEF3C7" />

                                <circle cx="32" cy="28" r="12" fill="#F59E0B" />

                                <text
                                    x="32"
                                    y="32"
                                    text-anchor="middle"
                                    font-size="10"
                                    fill="#FFF"
                                    font-family="Arial, sans-serif"
                                >
                                    ₹
                                </text>

                                <rect x="30" y="18" width="4" height="8" rx="1" fill="#EF4444" />
                                <rect x="28" y="22" width="8" height="4" rx="1" fill="#EF4444" />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- PERMISSIONS --}}
        {{-- ============================================================= --}}

        @can('show permissions')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('permissions.index')"
                        :current="request()->routeIs('permissions.*')"
                        wire:navigate
                    >
                        {{ __('Permissions') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#EFF6FF" />

                                <path
                                    d="M32 14 L46 24 V40 C46 46 32 50 32 50 C32 50 18 46 18 40 V24 Z"
                                    fill="#3B82F6"
                                />

                                <circle cx="32" cy="28" r="4" fill="#FBBF24" />
                                <rect x="34" y="28" width="8" height="2" rx="1" fill="#FBBF24" />
                                <rect x="40" y="28" width="2" height="4" rx="1" fill="#FBBF24" />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- ROLES --}}
        {{-- ============================================================= --}}

        @can('show roles')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('roles.index')"
                        :current="request()->routeIs('roles.*')"
                        wire:navigate
                    >
                        {{ __('Roles') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEF3F2" />

                                <circle cx="32" cy="24" r="8" fill="#EF4444" />

                                <path
                                    d="M24 44c0-6 8-10 16-10s16 4 16 10v4H24v-4z"
                                    fill="#FCA5A5"
                                />

                                <path d="M28 34 L36 34 L36 42 L28 42 Z" fill="#F59E0B" />

                                <text
                                    x="32"
                                    y="39"
                                    text-anchor="middle"
                                    font-size="8"
                                    fill="#FFF"
                                    font-family="Arial, sans-serif"
                                >
                                    R
                                </text>
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- INVOICE SENDS --}}
        {{-- ============================================================= --}}

        @can('show invoice sends')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('invoice-sends.index')"
                        :current="request()->routeIs('invoice-sends.*')"
                        wire:navigate
                    >
                        {{ __('Invoice Sends') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#EFF6FF" />

                                <rect x="16" y="16" width="32" height="32" rx="3" fill="#3B82F6" />

                                <line
                                    x1="20"
                                    y1="22"
                                    x2="44"
                                    y2="22"
                                    stroke="#EFF6FF"
                                    stroke-width="2"
                                />

                                <line
                                    x1="20"
                                    y1="28"
                                    x2="44"
                                    y2="28"
                                    stroke="#93C5FD"
                                    stroke-width="2"
                                />

                                <line
                                    x1="20"
                                    y1="34"
                                    x2="36"
                                    y2="34"
                                    stroke="#93C5FD"
                                    stroke-width="2"
                                />

                                <path d="M44 32 L52 28 L52 36 Z" fill="#10B981" />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- INSTALLMENT REMINDERS --}}
        {{-- ============================================================= --}}

        @can('show installment reminders')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        icon="home"
                        :href="route('installment-reminders.index')"
                        :current="request()->routeIs('installment-reminders.*')"
                        wire:navigate
                    >
                        {{ __('Installment Reminders') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- BIRTHDAY RECORDS --}}
        {{-- ============================================================= --}}

        @can('show birthday records')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('birthday-records.index')"
                        :current="request()->routeIs('birthday-records.*')"
                        wire:navigate
                    >
                        {{ __('Birthday Records') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEF9C3" />

                                <rect x="20" y="28" width="24" height="16" rx="3" fill="#FBBF24" />
                                <path d="M20 28 Q32 18 44 28 Z" fill="#F59E0B" />

                                <rect x="26" y="20" width="2" height="8" fill="#EF4444" />
                                <rect x="32" y="18" width="2" height="10" fill="#3B82F6" />
                                <rect x="38" y="20" width="2" height="8" fill="#10B981" />

                                <circle cx="27" cy="18" r="1" fill="#FCD34D" />
                                <circle cx="33" cy="16" r="1" fill="#FCD34D" />
                                <circle cx="39" cy="18" r="1" fill="#FCD34D" />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- BIRTHDAY WISH LOGS --}}
        {{-- ============================================================= --}}

        @can('show wishes logs')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('birthday-wish-logs.index')"
                        :current="request()->routeIs('birthday-wish-logs.*')"
                        wire:navigate
                    >
                        {{ __('Wishes logs') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#EFF6FF" />

                                <circle cx="32" cy="22" r="10" fill="#3B82F6" />

                                <path
                                    d="M20 44c0-6 24-6 24 0v4H20v-4z"
                                    fill="#60A5FA"
                                />

                                <circle cx="44" cy="14" r="4" fill="#FBBF24" />

                                <text
                                    x="44"
                                    y="16"
                                    text-anchor="middle"
                                    font-size="6"
                                    fill="#FFF"
                                    font-family="Arial, sans-serif"
                                >
                                    P
                                </text>
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- ANNIVERSARY RECORDS AND LOGS --}}
        {{-- ============================================================= --}}

        @can('show anniversary records')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('anniversaries.index')"
                        :current="request()->routeIs('anniversaries.*')"
                        wire:navigate
                    >
                        {{ __('Anniversary Records') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEF9C3" />

                                <rect x="20" y="28" width="24" height="16" rx="3" fill="#FBBF24" />
                                <path d="M20 28 Q32 18 44 28 Z" fill="#F59E0B" />

                                <rect x="26" y="20" width="2" height="8" fill="#EF4444" />
                                <rect x="32" y="18" width="2" height="10" fill="#3B82F6" />
                                <rect x="38" y="20" width="2" height="8" fill="#10B981" />

                                <circle cx="27" cy="18" r="1" fill="#FCD34D" />
                                <circle cx="33" cy="16" r="1" fill="#FCD34D" />
                                <circle cx="39" cy="18" r="1" fill="#FCD34D" />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item
                        :href="route('anniversary-wish-logs.index')"
                        :current="request()->routeIs('anniversary-wish-logs.*')"
                        wire:navigate
                    >
                        {{ __('Anniversary Logs') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEF9C3" />

                                <rect x="20" y="28" width="24" height="16" rx="3" fill="#FBBF24" />
                                <path d="M20 28 Q32 18 44 28 Z" fill="#F59E0B" />

                                <rect x="26" y="20" width="2" height="8" fill="#EF4444" />
                                <rect x="32" y="18" width="2" height="10" fill="#3B82F6" />
                                <rect x="38" y="20" width="2" height="8" fill="#10B981" />

                                <circle cx="27" cy="18" r="1" fill="#FCD34D" />
                                <circle cx="33" cy="16" r="1" fill="#FCD34D" />
                                <circle cx="39" cy="18" r="1" fill="#FCD34D" />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        {{-- ============================================================= --}}
        {{-- USER ACTIVITY --}}
        {{-- ============================================================= --}}

        @role('super admin')
            <a
                href="{{ route('super-admin.user-activity.index') }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                {{ request()->routeIs('super-admin.user-activity.*')
                    ? 'bg-blue-600 text-white'
                    : 'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800'
                }}"
            >
                <span>Uses/Activity</span>
            </a>
        @endrole

        <flux:spacer />

        {{-- ============================================================= --}}
        {{-- DESKTOP USER MENU --}}
        {{-- ============================================================= --}}

        <flux:dropdown
            class="hidden lg:block"
            position="bottom"
            align="start"
        >
            <flux:profile
                :name="$user->name"
                :initials="$user->initials()"
                icon:trailing="chevrons-up-down"
            />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                >
                                    {{ $user->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">
                                    {{ $user->name }}
                                </span>

                                <span class="truncate text-xs">
                                    {{ $user->email }}
                                </span>

                                @if($business)
                                    <span class="truncate text-[11px] text-indigo-600 dark:text-indigo-400">
                                        {{ $business->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item
                        :href="route('settings.profile')"
                        icon="cog"
                        wire:navigate
                    >
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="w-full"
                >
                    @csrf

                    <flux:menu.item
                        as="button"
                        type="submit"
                        icon="arrow-right-start-on-rectangle"
                        class="w-full"
                    >
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    {{-- ============================================================= --}}
    {{-- MOBILE HEADER --}}
    {{-- ============================================================= --}}

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle
            class="lg:hidden"
            icon="bars-2"
            inset="left"
        />

        <a
            href="{{ route('dashboard') }}"
            wire:navigate
            class="ml-2 flex min-w-0 items-center gap-2"
        >
            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800"
            >
                @if($businessLogo)
                    <img
                        src="{{ $businessLogo }}"
                        alt="{{ $businessName }} Logo"
                        class="h-full w-full object-contain p-1"
                        onerror="
                            this.style.display='none';
                            this.nextElementSibling.style.display='flex';
                        "
                    >

                    <div
                        class="hidden h-full w-full items-center justify-center bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-black text-white"
                    >
                        {{ $businessInitial }}
                    </div>
                @else
                    <div
                        class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-black text-white"
                    >
                        {{ $businessInitial }}
                    </div>
                @endif
            </div>

            <span class="max-w-[140px] truncate text-sm font-bold text-zinc-900 dark:text-white">
                {{ $businessName }}
            </span>
        </a>

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile
                :initials="$user->initials()"
                icon-trailing="chevron-down"
            />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                >
                                    {{ $user->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">
                                    {{ $user->name }}
                                </span>

                                <span class="truncate text-xs">
                                    {{ $user->email }}
                                </span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item
                        :href="route('settings.profile')"
                        wire:navigate
                    >
                        {{ __('Settings') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#F3F4F6" />
                                <circle cx="32" cy="32" r="10" fill="#6366F1" />
                            </svg>
                        </x-slot:icon>
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="w-full"
                >
                    @csrf

                    <flux:menu.item
                        as="button"
                        type="submit"
                        class="w-full"
                    >
                        {{ __('Log Out') }}

                        <x-slot:icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 64 64"
                            >
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEE2E2" />

                                <rect x="18" y="16" width="20" height="32" rx="2" fill="#EF4444" />

                                <path
                                    d="M42 32 L34 24 L34 28 L26 28 L26 36 L34 36 L34 40 Z"
                                    fill="#FFF"
                                />
                            </svg>
                        </x-slot:icon>
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <style>
        summary::-webkit-details-marker {
            display: none;
        }

        @keyframes businessProfileGlow {
            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0);
                transform: translateY(0);
            }

            50% {
                box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.12);
                transform: translateY(-1px);
            }
        }

        .business-profile-attention {
            animation: businessProfileGlow 1.8s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .business-profile-attention {
                animation: none;
            }
        }
    </style>

    {{ $slot }}

        <x-floating-help />

    @fluxScripts

    <x-user-activity-tracker />
</body>

</html>