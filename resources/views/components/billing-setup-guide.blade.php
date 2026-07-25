@props(['step' => 1])

@php
    $steps = [
        1 => ['title' => 'Choose Bill Template', 'text' => 'Select how your invoice PDF should look.'],
        2 => ['title' => 'Complete Business Profile', 'text' => 'Add your business, tax and invoice information.'],
        3 => ['title' => 'Create First Item', 'text' => 'Add a product or service. You can create its category here.'],
        4 => ['title' => 'Create First Invoice', 'text' => 'Create the client, select the item and save the invoice.'],
    ];
@endphp

<div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-900 dark:bg-indigo-950/40">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Guided Billing Setup</p>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Step {{ $step }} of 4</h2>
        </div>
        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-indigo-700 shadow-sm dark:bg-slate-900 dark:text-indigo-300">
            {{ $steps[$step]['title'] }}
        </span>
    </div>

    <div class="grid gap-2 sm:grid-cols-4">
        @foreach($steps as $number => $details)
            <div class="rounded-xl border p-3 {{ $number === $step ? 'border-indigo-500 bg-white shadow-sm dark:bg-slate-900' : ($number < $step ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/30' : 'border-slate-200 bg-white/60 dark:border-slate-700 dark:bg-slate-900/40') }}">
                <div class="mb-1 flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold {{ $number < $step ? 'bg-emerald-600 text-white' : ($number === $step ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-200') }}">
                        {{ $number < $step ? '✓' : $number }}
                    </span>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ $details['title'] }}</span>
                </div>
                <p class="text-[11px] leading-4 text-slate-500 dark:text-slate-400">{{ $details['text'] }}</p>
            </div>
        @endforeach
    </div>
</div>
