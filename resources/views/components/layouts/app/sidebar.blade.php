<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
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

            <form action="{{ route('business.switch') }}" method="POST">
                @csrf
                <select name="business_id" onchange="this.form.submit()" class="text-sm border rounded px-2 py-1">
                    @foreach(auth()->user()->businesses as $b)
                        <option value="{{ $b->id }}" @selected(session('active_business_id')==$b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </form>


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


            @can('show items')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="home" :href="route('items.index')" :current="request()->routeIs('items.index')" wire:navigate>{{ __('Items') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            @endcan

            @can('show invoices')
            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="home" :href="route('invoices.index')" :current="request()->routeIs('invoices.index')" wire:navigate>{{ __('Invoices') }}</flux:navlist.item>
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



            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist>

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
