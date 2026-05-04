<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 ">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Platform')" class="grid">

                <flux:navlist.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                            <!-- Background card -->
                            <rect x="6" y="6" width="52" height="52" rx="10" fill="#EEF2FF" />

                            <!-- Top bar -->
                            <rect x="14" y="14" width="36" height="8" rx="4" fill="#6366F1" />

                            <!-- Widgets -->
                            <rect x="14" y="28" width="14" height="12" rx="4" fill="#22C55E" />
                            <rect x="32" y="28" width="18" height="12" rx="4" fill="#F59E0B" />

                            <!-- Chart bars -->
                            <rect x="18" y="46" width="4" height="8" rx="2" fill="#6366F1" />
                            <rect x="26" y="44" width="4" height="10" rx="2" fill="#22C55E" />
                            <rect x="34" y="42" width="4" height="12" rx="2" fill="#EF4444" />
                            <rect x="42" y="45" width="4" height="9" rx="2" fill="#0EA5E9" />
                        </svg>
                    </x-slot:icon>
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

        @php
            $user = auth()->user();

            // ✅ active business id priority: session -> user column -> first attached business
            $activeBusinessId =
                session('active_business_id') ??
                ($user->current_business_id ?? (null ?? optional($user->businesses->first())->id));
            $business = App\Models\Business::find($activeBusinessId);
        @endphp

        @role('super admin')


            <form action="{{ route('business.switch') }}" method="POST" class="inline-block">
                @csrf

                <select name="business_id" onchange="this.form.submit()"
                    class="w-full text-sm border border-gray-300 rounded-lg px-4 py-2
           bg-white text-gray-900
           dark:bg-neutral-800 dark:text-white dark:border-neutral-600
           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
           shadow-sm hover:border-blue-400 transition-all duration-200">

                    @forelse($user->businesses as $b)
                        <option  value="{{ $b->id }}" @selected((string) $activeBusinessId === (string) $b->id)>
                            {{ $b->name }}
                        </option>
                    @empty
                        <option value="">No business attached</option>
                    @endforelse
                </select>
            </form>
        @endrole

        @can('show invoices menu')
            @php
                $type = request('type'); // quotation | tax | proforma | null
                $isInvoiceRoute = request()->routeIs('invoices.*');
            @endphp

            <details class="group {{ $isInvoiceRoute ? 'open' : '' }}">
                {{-- auto open --}}
                @if ($isInvoiceRoute)
                    <script>
                        document.currentScript.parentElement.setAttribute('open', 'open')
                    </script>
                @endif

                {{-- SUMMARY --}}
                <summary
                    class="list-none cursor-pointer select-none
                        flex items-center justify-between
                        px-3 py-2 rounded-lg text-sm font-medium
                        {{ $isInvoiceRoute ? 'bg-white/15 text-white' : 'text-zinc-300 hover:text-white hover:bg-white/10' }}
                        focus:outline-none focus:ring-2 focus:ring-white/20">
                    <span class="flex items-center gap-2">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-white/10 text-white">
                            {{-- 🧾 --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64">
                                <!-- Background -->
                                <rect x="8" y="4" width="40" height="56" rx="4" fill="#4F46E5" />

                                <!-- Folded corner -->
                                <polygon points="40,4 56,20 40,20" fill="#6366F1" />

                                <!-- Paper overlay -->
                                <rect x="14" y="14" width="36" height="44" rx="3" fill="#EEF2FF" />

                                <!-- Invoice lines -->
                                <rect x="18" y="20" width="28" height="4" rx="2" fill="#6366F1" />
                                <rect x="18" y="28" width="28" height="3" rx="1.5" fill="#9CA3AF" />
                                <rect x="18" y="34" width="28" height="3" rx="1.5" fill="#9CA3AF" />
                                <rect x="18" y="40" width="20" height="3" rx="1.5" fill="#9CA3AF" />

                                <!-- Amount badge -->
                                <circle cx="44" cy="48" r="8" fill="#22C55E" />
                                <text x="44" y="52" text-anchor="middle" font-size="10" fill="#ffffff"
                                    font-family="Arial, sans-serif">$</text>
                            </svg>

                        </span>
                        <span class="text-black dark:text-white">Invoices</span>
                    </span>

                    <svg class="h-4 w-4 transition-transform duration-200 group-open:rotate-180 text-black dark:text-white"
                        viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                            clip-rule="evenodd" />
                    </svg>
                </summary>

                {{-- SUB MENU --}}
                <div class="mt-1 ml-2 pl-2 border-l border-white/10 space-y-1">

                    <flux:navlist variant="outline">
                        <flux:navlist.group class="grid">

                            {{-- Quotation --}}
                            @can('show quotations')
                                <flux:navlist.item icon="document-text"
                                    :href="route('invoices.index', ['type' => 'quotation'])"
                                    :current="$isInvoiceRoute && $type === 'quotation'" wire:navigate>
                                    Quotation
                                </flux:navlist.item>
                            @endcan

                            @can('create invoice')
                                {{-- Create Invoice --}}
                                <flux:navlist.item icon="plus" :href="route('invoices.create', ['type' => 'tax'])"
                                    :current="request()->routeIs('invoices.create') && $type === 'tax'" wire:navigate>
                                    Create Invoice
                                </flux:navlist.item>
                            @endcan

                            @can('show invoices')
                                {{-- All Invoices --}}
                                <flux:navlist.item icon="list-bullet" :href="route('invoices.index')"
                                    :current="$isInvoiceRoute && empty($type)" wire:navigate>
                                    Invoices
                                </flux:navlist.item>
                            @endcan

                            @can('show proformas')
                                {{-- Proforma --}}
                                <flux:navlist.item icon="document" :href="route('invoices.index', ['type' => 'proforma'])"
                                    :current="$isInvoiceRoute && $type === 'proforma'" wire:navigate>
                                    Proforma
                                </flux:navlist.item>
                            @endcan

                        </flux:navlist.group>
                    </flux:navlist>

                </div>
            </details>

            <style>
                summary::-webkit-details-marker {
                    display: none;
                }
            </style>
        @endcan


        @can('download reports')
        <a href="{{ route('invoices.reports.page') }}"
        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium
                text-gray-700 dark:text-neutral-200
                hover:bg-gray-100 dark:hover:bg-neutral-800
                {{ request()->routeIs('invoices.reports.page') ? 'bg-gray-100 dark:bg-neutral-800 font-semibold' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 16v-8m0 8l-3-3m3 3l3-3M5 20h14" />
            </svg>
            <span>Invoice Reports</span>
        </a>
        @endcan



        @can('show bill requests')
            
            <a href="{{ route('bill-requests.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium
                        text-gray-700 dark:text-neutral-200
                        hover:bg-gray-100 dark:hover:bg-neutral-800
                        {{ request()->routeIs('invoices.reports.page') ? 'bg-gray-100 dark:bg-neutral-800 font-semibold' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 16v-8m0 8l-3-3m3 3l3-3M5 20h14" />
                </svg>
                <span>Bill Requests</span>
            </a>
        @endcan
        

        @can('show businesses')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item :href="route('businesses.index')" :current="request()->routeIs('businesses.index')"
                        wire:navigate>{{ __('Businesses') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">
                                <rect x="8" y="8" width="20" height="40" rx="4" fill="#0EA5E9" />
                                <rect x="32" y="16" width="20" height="32" rx="4" fill="#22C55E" />
                            </svg>
                        </x-slot:icon>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        @can('show users')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item :href="route('api-keys.index')" :current="request()->routeIs('api-keys*')"
                        wire:navigate>{{ __('Api Key') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#EEF2FF" />
                                <rect x="14" y="14" width="36" height="8" rx="4" fill="#6366F1" />
                            </svg>
                        </x-slot:icon>

                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item :href="route('users.index')" :current="request()->routeIs('users.index')"
                        wire:navigate>{{ __('Users') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                                <!-- Background -->
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#ECFEFF" />

                                <!-- Main user -->
                                <circle cx="32" cy="26" r="8" fill="#0EA5E9" />
                                <path d="M18 46c0-7 6-12 14-12s14 5 14 12" fill="#38BDF8" />

                                <!-- Side user -->
                                <circle cx="20" cy="28" r="6" fill="#22C55E" />
                                <path d="M10 46c0-5 4-9 10-9" fill="#4ADE80" />

                            </svg>
                        </x-slot:icon>

                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        @can('show clients')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item :href="route('clients.index')" :current="request()->routeIs('clients.index')"
                        wire:navigate>{{ __('Clients') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                                <!-- Background -->
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#F0FDF4" />

                                <!-- Client avatar -->
                                <circle cx="32" cy="26" r="8" fill="#22C55E" />
                                <path d="M18 46c0-7 6-12 14-12s14 5 14 12" fill="#4ADE80" />

                                <!-- Star / badge -->
                                <polygon points="46,20 48,24 52,24 49,27 50,31 46,29 42,31 43,27 40,24 44,24"
                                    fill="#F59E0B" />

                            </svg>
                        </x-slot:icon>

                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        @can('show categories')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item :href="route('categories.index')" :current="request()->routeIs('categories.index')"
                        wire:navigate>{{ __('Categories') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                                <!-- Background -->
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEF3C7" />

                                <!-- Folder -->
                                <path d="M12 20h20l6 6v22H12V20z" fill="#F59E0B" />
                                <path d="M12 20v-4h16l6 6v22H12V20z" fill="#FBBF24" />

                                <!-- Tabs / labels -->
                                <rect x="18" y="28" width="16" height="4" rx="1" fill="#FCD34D" />
                                <rect x="18" y="36" width="16" height="4" rx="1" fill="#FCD34D" />
                                <rect x="18" y="44" width="16" height="4" rx="1" fill="#FCD34D" />

                            </svg>
                        </x-slot:icon>

                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan


        @if ($business?->type == 'jewellery')
            @can('show metal rates')
                <flux:navlist variant="outline">
                    <flux:navlist.group class="grid">
                        <flux:navlist.item icon="home" :href="route('metal-rates.index')"
                            :current="request()->routeIs('metal-rates.index')" wire:navigate>{{ __('Metal Rates') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
            @endcan
        @endif

        @can('show items')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item :href="route('items.index')" :current="request()->routeIs('items.index')"
                        wire:navigate>{{ __('Items') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                                <!-- Background -->
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#EFF6FF" />

                                <!-- Box / Item -->
                                <rect x="20" y="20" width="24" height="24" rx="4" fill="#3B82F6" />
                                <path d="M20 20l12 12 12-12" stroke="#93C5FD" stroke-width="2" fill="none" />
                                <line x1="32" y1="32" x2="32" y2="44" stroke="#93C5FD"
                                    stroke-width="2" />

                                <!-- Label / tag -->
                                <rect x="22" y="46" width="20" height="4" rx="1" fill="#60A5FA" />
                            </svg>
                        </x-slot:icon>

                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        @can('show purchases')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item :href="route('purchases.index')" :current="request()->routeIs('purchases.index')"
                        wire:navigate>{{ __('Purchases') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                                <!-- Background -->
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEF3F2" />

                                <!-- Shopping cart -->
                                <path d="M18 44h28l-4-20H16l2 10" fill="#F87171" />
                                <circle cx="22" cy="48" r="3" fill="#B91C1C" />
                                <circle cx="42" cy="48" r="3" fill="#B91C1C" />

                                <!-- Purchase bag / box inside cart -->
                                <rect x="26" y="28" width="12" height="12" rx="2" fill="#FCA5A5" />

                            </svg>
                        </x-slot:icon>

                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        @can('show inventory')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item :href="route('inventory.summary')"
                        :current="request()->routeIs('inventory.summary')" wire:navigate>{{ __('Inventory') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                                <!-- Background -->
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#ECFDF5" />

                                <!-- Warehouse / stacked boxes -->
                                <rect x="16" y="24" width="12" height="12" rx="2" fill="#10B981" />
                                <rect x="36" y="24" width="12" height="12" rx="2" fill="#34D399" />
                                <rect x="26" y="38" width="12" height="12" rx="2" fill="#6EE7B7" />

                                <!-- Labels -->
                                <line x1="18" y1="28" x2="26" y2="28" stroke="#FFFFFF"
                                    stroke-width="2" />
                                <line x1="18" y1="32" x2="26" y2="32" stroke="#FFFFFF"
                                    stroke-width="2" />
                                <line x1="38" y1="28" x2="46" y2="28" stroke="#FFFFFF"
                                    stroke-width="2" />
                                <line x1="38" y1="32" x2="46" y2="32" stroke="#FFFFFF"
                                    stroke-width="2" />
                            </svg>
                        </x-slot:icon>

                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan


        @can('show bank balance')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="home" :href="route('bank-accounts.index')"
                        :current="request()->routeIs('bank-accounts.index')" wire:navigate>{{ __('Banks & Balance') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        @can('show plan')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="home" :href="route('plans.index')"
                        :current="request()->routeIs('plans.index')" wire:navigate>{{ __('Plan management') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            @endcan

            @can('show user plan')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="home" :href="route('user-plans.index')"
                        :current="request()->routeIs('user-plans.index')" wire:navigate>{{ __('User Plan') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan


        @can('show bill templates')
        <flux:navlist variant="outline">
            <flux:navlist.group class="grid">
                <flux:navlist.item icon="home" :href="route('bill-templates.index')"
                    :current="request()->routeIs('bill-templates.index')" wire:navigate>{{ __('Bill Template') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>
        @endcan

        @can('choose templates')
            
        <flux:navlist variant="outline">
            <flux:navlist.group class="grid">
                <flux:navlist.item icon="home" :href="route('bill-templates.choose')"
                    :current="request()->routeIs('bill-templates.choose')" wire:navigate>{{ __('Choose Template') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

        @endcan


        @can('show additional charges')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item :href="route('additional-charges.index')"
                        :current="request()->routeIs('additional-charges.index')" wire:navigate>
                        {{ __('Additional Charge') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                                <!-- Background -->
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEF3C7" />

                                <!-- Coin / money -->
                                <circle cx="32" cy="28" r="12" fill="#F59E0B" />
                                <text x="32" y="32" text-anchor="middle" font-size="10" fill="#FFF"
                                    font-family="Arial, sans-serif">$</text>

                                <!-- Plus sign -->
                                <rect x="30" y="18" width="4" height="8" rx="1" fill="#EF4444" />
                                <rect x="28" y="22" width="8" height="4" rx="1" fill="#EF4444" />
                            </svg>
                        </x-slot:icon>

                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan


        @can('show permissions')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item :href="route('permissions.index')"
                        :current="request()->routeIs('permissions.index')" wire:navigate>{{ __('Permissions') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                                <!-- Background -->
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#EFF6FF" />

                                <!-- Shield -->
                                <path d="M32 14 L46 24 V40 C46 46 32 50 32 50 C32 50 18 46 18 40 V24 Z" fill="#3B82F6" />

                                <!-- Key -->
                                <circle cx="32" cy="28" r="4" fill="#FBBF24" />
                                <rect x="34" y="28" width="8" height="2" rx="1" fill="#FBBF24" />
                                <rect x="40" y="28" width="2" height="4" rx="1" fill="#FBBF24" />

                            </svg>
                        </x-slot:icon>

                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        @can('show roles')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item :href="route('roles.index')" :current="request()->routeIs('roles.index')"
                        wire:navigate>{{ __('Roles') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                                <!-- Background -->
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEF3F2" />

                                <!-- User silhouette -->
                                <circle cx="32" cy="24" r="8" fill="#EF4444" />
                                <path d="M24 44c0-6 8-10 16-10s16 4 16 10v4H24v-4z" fill="#FCA5A5" />

                                <!-- Shield / badge -->
                                <path d="M28 34 L36 34 L36 42 L28 42 Z" fill="#F59E0B" />
                                <text x="32" y="39" text-anchor="middle" font-size="8" fill="#FFF"
                                    font-family="Arial, sans-serif">R</text>

                            </svg>
                        </x-slot:icon>

                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan
        @can('show invoice sends')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item :href="route('invoice-sends.index')"
                        :current="request()->routeIs('invoice-sends.index')" wire:navigate>{{ __('Invoice Sends') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                                <!-- Background -->
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#EFF6FF" />

                                <!-- Invoice paper -->
                                <rect x="16" y="16" width="32" height="32" rx="3" fill="#3B82F6" />
                                <line x1="20" y1="22" x2="44" y2="22" stroke="#EFF6FF"
                                    stroke-width="2" />
                                <line x1="20" y1="28" x2="44" y2="28" stroke="#93C5FD"
                                    stroke-width="2" />
                                <line x1="20" y1="34" x2="36" y2="34" stroke="#93C5FD"
                                    stroke-width="2" />

                                <!-- Send arrow -->
                                <path d="M44 32 L52 28 L52 36 Z" fill="#10B981" />
                            </svg>
                        </x-slot:icon>

                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        @can('show installment reminders')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="home" :href="route('installment-reminders.index')"
                        :current="request()->routeIs('installment-reminders.*')" wire:navigate>
                        {{ __('Installment Reminders') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan
        @can('show birthday records')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item :href="route('birthday-records.index')"
                        :current="request()->routeIs('birthday-records.index')" wire:navigate>{{ __('Birthday Records') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                                <!-- Background -->
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEF9C3" />

                                <!-- Cake base -->
                                <rect x="20" y="28" width="24" height="16" rx="3" fill="#FBBF24" />

                                <!-- Cake frosting -->
                                <path d="M20 28 Q32 18 44 28 Z" fill="#F59E0B" />

                                <!-- Candles -->
                                <rect x="26" y="20" width="2" height="8" fill="#EF4444" />
                                <rect x="32" y="18" width="2" height="10" fill="#3B82F6" />
                                <rect x="38" y="20" width="2" height="8" fill="#10B981" />

                                <!-- Small spark / flame -->
                                <circle cx="27" cy="18" r="1" fill="#FCD34D" />
                                <circle cx="33" cy="16" r="1" fill="#FCD34D" />
                                <circle cx="39" cy="18" r="1" fill="#FCD34D" />
                            </svg>
                        </x-slot:icon>

                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        @can('show anniversary records')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item :href="route('anniversaries.index')"
                        :current="request()->routeIs('anniversaries.index')" wire:navigate>{{ __('Anniversary Records') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                                <!-- Background -->
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEF9C3" />

                                <!-- Cake base -->
                                <rect x="20" y="28" width="24" height="16" rx="3" fill="#FBBF24" />

                                <!-- Cake frosting -->
                                <path d="M20 28 Q32 18 44 28 Z" fill="#F59E0B" />

                                <!-- Candles -->
                                <rect x="26" y="20" width="2" height="8" fill="#EF4444" />
                                <rect x="32" y="18" width="2" height="10" fill="#3B82F6" />
                                <rect x="38" y="20" width="2" height="8" fill="#10B981" />

                                <!-- Small spark / flame -->
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
                    <flux:navlist.item :href="route('anniversary-wish-logs.index')"
                        :current="request()->routeIs('anniversary-wish-logs.index')" wire:navigate>{{ __('Anniversary Logs') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                                <!-- Background -->
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEF9C3" />

                                <!-- Cake base -->
                                <rect x="20" y="28" width="24" height="16" rx="3" fill="#FBBF24" />

                                <!-- Cake frosting -->
                                <path d="M20 28 Q32 18 44 28 Z" fill="#F59E0B" />

                                <!-- Candles -->
                                <rect x="26" y="20" width="2" height="8" fill="#EF4444" />
                                <rect x="32" y="18" width="2" height="10" fill="#3B82F6" />
                                <rect x="38" y="20" width="2" height="8" fill="#10B981" />

                                <!-- Small spark / flame -->
                                <circle cx="27" cy="18" r="1" fill="#FCD34D" />
                                <circle cx="33" cy="16" r="1" fill="#FCD34D" />
                                <circle cx="39" cy="18" r="1" fill="#FCD34D" />
                            </svg>
                        </x-slot:icon>

                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan

        @can('show wishes logs')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item :href="route('birthday-wish-logs.index')"
                        :current="request()->routeIs('birthday-wish-logs.index')" wire:navigate>{{ __('Wishes logs') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                                <!-- Background -->
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#EFF6FF" />

                                <!-- Head -->
                                <circle cx="32" cy="22" r="10" fill="#3B82F6" />

                                <!-- Shoulders / torso -->
                                <path d="M20 44c0-6 24-6 24 0v4H20v-4z" fill="#60A5FA" />

                                <!-- Small badge / indicator -->
                                <circle cx="44" cy="14" r="4" fill="#FBBF24" />
                                <text x="44" y="16" text-anchor="middle" font-size="6" fill="#FFF"
                                    font-family="Arial, sans-serif">P</text>
                            </svg>
                        </x-slot:icon>

                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
        @endcan





        <flux:spacer />

        {{--            <flux:navlist variant="outline"> --}}
        {{--                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank"> --}}
        {{--                {{ __('Repository') }} --}}
        {{--                </flux:navlist.item> --}}

        {{--                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank"> --}}
        {{--                {{ __('Documentation') }} --}}
        {{--                </flux:navlist.item> --}}
        {{--            </flux:navlist> --}}

        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                icon:trailing="chevrons-up-down" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                        {{ __('Settings') }}</flux:menu.item>
                </flux:menu.radio.group>


                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>


                

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" wire:navigate>
                        {{ __('Settings') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                                <!-- Background -->
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#F3F4F6" />

                                <!-- Gear / settings -->
                                <circle cx="32" cy="32" r="10" fill="#6366F1" />

                                <!-- Gear teeth -->
                                <rect x="30" y="16" width="4" height="6" rx="1" fill="#4F46E5" />
                                <rect x="30" y="42" width="4" height="6" rx="1" fill="#4F46E5" />
                                <rect x="16" y="30" width="6" height="4" rx="1" fill="#4F46E5" />
                                <rect x="42" y="30" width="6" height="4" rx="1" fill="#4F46E5" />
                                <rect x="20" y="20" width="4" height="8" rx="1"
                                    transform="rotate(-45 22 24)" fill="#4F46E5" />
                                <rect x="40" y="40" width="4" height="8" rx="1"
                                    transform="rotate(-45 42 44)" fill="#4F46E5" />
                                <rect x="20" y="40" width="4" height="8" rx="1"
                                    transform="rotate(45 22 44)" fill="#4F46E5" />
                                <rect x="40" y="20" width="4" height="8" rx="1"
                                    transform="rotate(45 42 24)" fill="#4F46E5" />
                            </svg>
                        </x-slot:icon>

                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" class="w-full">
                        {{ __('Log Out') }}
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 64 64">

                                <!-- Background -->
                                <rect x="6" y="6" width="52" height="52" rx="10" fill="#FEE2E2" />

                                <!-- Door / frame -->
                                <rect x="18" y="16" width="20" height="32" rx="2" fill="#EF4444" />

                                <!-- Arrow -->
                                <path d="M42 32 L34 24 L34 28 L26 28 L26 36 L34 36 L34 40 Z" fill="#FFF" />
                            </svg>
                        </x-slot:icon>

                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>
