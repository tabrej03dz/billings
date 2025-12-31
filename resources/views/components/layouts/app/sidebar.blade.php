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
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            @php
                $user = auth()->user();

                // ✅ active business id priority: session -> user column -> first attached business
                $activeBusinessId =
                    session('active_business_id')
                    ?? ($user->current_business_id ?? null)
                    ?? optional($user->businesses->first())->id;
                $business = App\Models\Business::find($activeBusinessId);

            @endphp

            @role('super admin')


            <form action="{{ route('business.switch') }}" method="POST" class="inline-block">
                @csrf

                <select name="business_id"
                        onchange="this.form.submit()"
                        class="text-sm border rounded px-2 py-1 bg-white text-gray-900
                       dark:bg-neutral-800 dark:text-white dark:border-neutral-600
                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                    @forelse($user->businesses as $b)
                        <option value="{{ $b->id }}" @selected((string)$activeBusinessId === (string)$b->id)>
                            {{ $b->name }}
                        </option>
                    @empty
                        <option value="">No business attached</option>
                    @endforelse
                </select>
            </form>
            @endrole

            @can('show invoices')
                @php
                    $type = request('type'); // quotation | tax | proforma | null
                    $isInvoiceRoute = request()->routeIs('invoices.*');
                @endphp

                <details class="group {{ $isInvoiceRoute ? 'open' : '' }}">
                    {{-- auto open --}}
                    @if($isInvoiceRoute)
                        <script>
                            document.currentScript.parentElement.setAttribute('open','open')
                        </script>
                    @endif

                    {{-- SUMMARY --}}
                    <summary
                        class="list-none cursor-pointer select-none
            flex items-center justify-between
            px-3 py-2 rounded-lg text-sm font-medium
            {{ $isInvoiceRoute ? 'bg-white/15 text-white' : 'text-zinc-300 hover:text-white hover:bg-white/10' }}
            focus:outline-none focus:ring-2 focus:ring-white/20"
                    >
            <span class="flex items-center gap-2">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-white/10 text-white">
                    🧾
                </span>
                <span>Invoices</span>
            </span>

                        <svg class="h-4 w-4 transition-transform duration-200 group-open:rotate-180"
                             viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </summary>

                    {{-- SUB MENU --}}
                    <div class="mt-1 ml-2 pl-2 border-l border-white/10 space-y-1">

                        <flux:navlist variant="outline">
                            <flux:navlist.group class="grid">

                                {{-- Quotation --}}
                                <flux:navlist.item
                                    icon="document-text"
                                    :href="route('invoices.index', ['type' => 'quotation'])"
                                    :current="$isInvoiceRoute && $type === 'quotation'"
                                    wire:navigate>
                                    Quotation
                                </flux:navlist.item>

                                {{-- Create Invoice --}}
                                <flux:navlist.item
                                    icon="plus"
                                    :href="route('invoices.create', ['type' => 'tax'])"
                                    :current="request()->routeIs('invoices.create') && $type === 'tax'"
                                    wire:navigate>
                                    Create Invoice
                                </flux:navlist.item>

                                {{-- All Invoices --}}
                                <flux:navlist.item
                                    icon="list-bullet"
                                    :href="route('invoices.index')"
                                    :current="$isInvoiceRoute && empty($type)"
                                    wire:navigate>
                                    Invoices
                                </flux:navlist.item>

                                {{-- Proforma --}}
                                <flux:navlist.item
                                    icon="document"
                                    :href="route('invoices.index', ['type' => 'proforma'])"
                                    :current="$isInvoiceRoute && $type === 'proforma'"
                                    wire:navigate>
                                    Proforma
                                </flux:navlist.item>

                            </flux:navlist.group>
                        </flux:navlist>

                    </div>
                </details>

                <style>
                    summary::-webkit-details-marker { display: none; }
                </style>
            @endcan


        @can('show businesses')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="home" :href="route('businesses.index')" :current="request()->routeIs('businesses.index')" wire:navigate>{{ __('Businesses') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            @endcan

            @can('show users')
                <flux:navlist variant="outline">
                    <flux:navlist.group class="grid">
                        <flux:navlist.item icon="user" :href="route('api-keys.index')" :current="request()->routeIs('api-keys*')" wire:navigate>{{ __('Api Key') }}</flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="user" :href="route('users.index')" :current="request()->routeIs('users.index')" wire:navigate>{{ __('Users') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            @endcan

            @can('show clients')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="home" :href="route('clients.index')" :current="request()->routeIs('clients.index')" wire:navigate>{{ __('Clients') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            @endcan

            @can('show categories')
                <flux:navlist variant="outline">
                    <flux:navlist.group class="grid">
                        <flux:navlist.item icon="home" :href="route('categories.index')" :current="request()->routeIs('categories.index')" wire:navigate>{{ __('Categories') }}</flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
            @endcan


            @if($business?->type == 'jewellery')
            @can('show metal rates')
                <flux:navlist variant="outline">
                    <flux:navlist.group class="grid">
                        <flux:navlist.item icon="home" :href="route('metal-rates.index')" :current="request()->routeIs('metal-rates.index')" wire:navigate>{{ __('Metal Rates') }}</flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
            @endcan
            @endif

            @can('show items')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="home" :href="route('items.index')" :current="request()->routeIs('items.index')" wire:navigate>{{ __('Items') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            @endcan

            @can('show purchases')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="home" :href="route('purchases.index')" :current="request()->routeIs('purchases.index')" wire:navigate>{{ __('Purchases') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            @endcan

            @can('show inventory')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="home" :href="route('inventory.summary')" :current="request()->routeIs('inventory.summary')" wire:navigate>{{ __('Inventory') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            @endcan


            @can('show invoices')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="home" :href="route('bank-accounts.index')" :current="request()->routeIs('bank-accounts.index')" wire:navigate>{{ __('Banks & Balance') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            @endcan

            @can('show additional charges')
                <flux:navlist variant="outline">
                    <flux:navlist.group class="grid">
                        <flux:navlist.item icon="home" :href="route('additional-charges.index')" :current="request()->routeIs('additional-charges.index')" wire:navigate>{{ __('Additional Charge') }}</flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
            @endcan


            @can('show permissions')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="home" :href="route('permissions.index')" :current="request()->routeIs('permissions.index')" wire:navigate>{{ __('Permissions') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            @endcan

            @can('show roles')
                <flux:navlist variant="outline">
                    <flux:navlist.group class="grid">
                        <flux:navlist.item icon="home" :href="route('roles.index')" :current="request()->routeIs('roles.index')" wire:navigate>{{ __('Roles') }}</flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
            @endcan
            @can('show invoice sends')
                <flux:navlist variant="outline">
                    <flux:navlist.group class="grid">
                        <flux:navlist.item icon="home" :href="route('invoice-sends.index')" :current="request()->routeIs('invoice-sends.index')" wire:navigate>{{ __('Invoice Sends') }}</flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
            @endcan
            @can('show birthday records')
                <flux:navlist variant="outline">
                    <flux:navlist.group class="grid">
                        <flux:navlist.item icon="home" :href="route('birthday-records.index')" :current="request()->routeIs('birthday-records.index')" wire:navigate>{{ __('Birthday Records') }}</flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
            @endcan

            @can('show wishes logs')
                <flux:navlist variant="outline">
                    <flux:navlist.group class="grid">
                        <flux:navlist.item icon="home" :href="route('birthday-wish-logs.index')" :current="request()->routeIs('birthday-wish-logs.index')" wire:navigate>{{ __('Wishes logs') }}</flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
            @endcan





            <flux:spacer />

{{--            <flux:navlist variant="outline">--}}
{{--                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">--}}
{{--                {{ __('Repository') }}--}}
{{--                </flux:navlist.item>--}}

{{--                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">--}}
{{--                {{ __('Documentation') }}--}}
{{--                </flux:navlist.item>--}}
{{--            </flux:navlist>--}}

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
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
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
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
                <flux:profile
                    :initials="auth()->user()->initials()"
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
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
