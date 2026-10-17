@props([
    'show' => 'open',
    'maxWidth' => '2xl',
])

@php
    $maxWidthClass = match($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        default => 'max-w-2xl',
    };
@endphp

<div
    x-show="{{ $show }}"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-[100] overflow-y-auto bg-black/55 p-4"
    style="display: none;"
>
    <div class="flex min-h-full items-center justify-center">
        <div
            x-transition
            {{ $attributes->class([
                'w-full rounded-2xl bg-white p-5 shadow-2xl',
                'dark:bg-neutral-900',
                $maxWidthClass,
            ]) }}
        >
            {{ $slot }}
        </div>
    </div>
</div>