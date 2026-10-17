{{-- <x-layouts.app.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar> --}}


<x-layouts.app.sidebar :title="$title ?? null">
    @php
        $isFullWidthInvoicePage = request()->routeIs('invoices.create');
    @endphp

    <flux:main
        :class="$isFullWidthInvoicePage
            ? '!w-full !max-w-none !min-w-0 !p-0 sm:!p-0 lg:!p-0'
            : ''"
    >
        <div @class([
            'w-full min-w-0',
            'max-w-none' => $isFullWidthInvoicePage,
        ])>
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts.app.sidebar>