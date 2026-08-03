@props([
    'status',
])

@php
    $normalized = strtolower(trim((string) $status));

    $classes = match($normalized) {
        'active',
        'available',
        'paid',
        'discharged' =>
            'bg-emerald-50 text-emerald-700 ring-emerald-200 '
            . 'dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-900',

        'occupied',
        'emergency',
        'cancelled',
        'overdue' =>
            'bg-red-50 text-red-700 ring-red-200 '
            . 'dark:bg-red-950/40 dark:text-red-300 dark:ring-red-900',

        'reserved',
        'registered',
        'pending',
        'in_consultation' =>
            'bg-amber-50 text-amber-700 ring-amber-200 '
            . 'dark:bg-amber-950/40 dark:text-amber-300 dark:ring-amber-900',

        'admitted',
        'ipd' =>
            'bg-violet-50 text-violet-700 ring-violet-200 '
            . 'dark:bg-violet-950/40 dark:text-violet-300 dark:ring-violet-900',

        'maintenance',
        'inactive' =>
            'bg-slate-100 text-slate-700 ring-slate-200 '
            . 'dark:bg-neutral-800 dark:text-neutral-300 dark:ring-neutral-700',

        default =>
            'bg-cyan-50 text-cyan-700 ring-cyan-200 '
            . 'dark:bg-cyan-950/40 dark:text-cyan-300 dark:ring-cyan-900',
    };

    $label = ucwords(str_replace('_', ' ', $normalized));
@endphp

<span
    {{ $attributes->class([
        'inline-flex items-center rounded-full px-2.5 py-1',
        'text-[10px] font-bold ring-1 ring-inset',
        $classes,
    ]) }}
>
    {{ $label }}
</span>