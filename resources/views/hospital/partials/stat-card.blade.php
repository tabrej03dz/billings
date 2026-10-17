@props([
    'label',
    'value' => 0,
    'theme' => 'cyan',
    'href' => null,
    'hint' => null,
])

@php
    $themes = [
        'cyan' => [
            'badge' => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300',
            'ring' => 'hover:border-cyan-300 dark:hover:border-cyan-800',
        ],
        'sky' => [
            'badge' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300',
            'ring' => 'hover:border-sky-300 dark:hover:border-sky-800',
        ],
        'emerald' => [
            'badge' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
            'ring' => 'hover:border-emerald-300 dark:hover:border-emerald-800',
        ],
        'violet' => [
            'badge' => 'bg-violet-50 text-violet-700 dark:bg-violet-950/40 dark:text-violet-300',
            'ring' => 'hover:border-violet-300 dark:hover:border-violet-800',
        ],
        'amber' => [
            'badge' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
            'ring' => 'hover:border-amber-300 dark:hover:border-amber-800',
        ],
        'red' => [
            'badge' => 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300',
            'ring' => 'hover:border-red-300 dark:hover:border-red-800',
        ],
    ];

    $selected = $themes[$theme] ?? $themes['cyan'];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->class([
        'block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm',
        'transition dark:border-neutral-800 dark:bg-neutral-900',
        $selected['ring'],
    ]) }}
>
    <div
        class="inline-flex rounded-lg px-2.5 py-1
            text-[10px] font-black uppercase tracking-wide
            {{ $selected['badge'] }}"
    >
        {{ $label }}
    </div>

    <div class="mt-3 text-3xl font-black text-slate-900 dark:text-white">
        {{ $value }}
    </div>

    @if($hint)
        <div class="mt-1 text-xs text-slate-500 dark:text-neutral-400">
            {{ $hint }}
        </div>
    @endif
</{{ $tag }}>