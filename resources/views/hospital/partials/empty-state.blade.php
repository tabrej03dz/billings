@props([
    'title' => 'No records found',
    'description' => null,
])

<div
    {{ $attributes->class([
        'flex flex-col items-center justify-center px-6 py-12 text-center',
        'text-slate-500 dark:text-neutral-400',
    ]) }}
>
    <div
        class="flex h-12 w-12 items-center justify-center rounded-2xl
            bg-cyan-50 text-cyan-700
            dark:bg-cyan-950/40 dark:text-cyan-300"
    >
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 3h6v4h4v6h-4v4H9v-4H5V7h4V3Z"/>
        </svg>
    </div>

    <h3 class="mt-3 text-sm font-bold text-slate-800 dark:text-neutral-100">
        {{ $title }}
    </h3>

    @if($description)
        <p class="mt-1 max-w-md text-xs leading-5">
            {{ $description }}
        </p>
    @endif

    @if(isset($action))
        <div class="mt-4">
            {{ $action }}
        </div>
    @endif
</div>