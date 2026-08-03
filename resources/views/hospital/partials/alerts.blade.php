@if(session('success'))
    <div
        class="mb-4 flex items-start gap-3 rounded-xl border border-emerald-200
            bg-emerald-50 px-4 py-3 text-sm text-emerald-800
            dark:border-emerald-900/60 dark:bg-emerald-950/30
            dark:text-emerald-300"
        role="alert"
    >
        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="m5 12 4 4L19 6"/>
        </svg>

        <div class="min-w-0">
            <div class="font-bold">Success</div>
            <div>{{ session('success') }}</div>
        </div>
    </div>
@endif

@if(session('warning'))
    <div
        class="mb-4 flex items-start gap-3 rounded-xl border border-amber-200
            bg-amber-50 px-4 py-3 text-sm text-amber-800
            dark:border-amber-900/60 dark:bg-amber-950/30
            dark:text-amber-300"
        role="alert"
    >
        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v4m0 4h.01M10.3 4.3 2.7 17.5A2 2 0 0 0 4.4 20h15.2a2 2 0 0 0 1.7-2.5L13.7 4.3a2 2 0 0 0-3.4 0Z"/>
        </svg>

        <div class="min-w-0">
            <div class="font-bold">Warning</div>
            <div>{{ session('warning') }}</div>
        </div>
    </div>
@endif

@if($errors->any())
    <div
        class="mb-4 rounded-xl border border-red-200 bg-red-50
            px-4 py-3 text-sm text-red-800
            dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300"
        role="alert"
    >
        <div class="flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v4m0 4h.01M10.3 4.3 2.7 17.5A2 2 0 0 0 4.4 20h15.2a2 2 0 0 0 1.7-2.5L13.7 4.3a2 2 0 0 0-3.4 0Z"/>
            </svg>

            <div class="min-w-0">
                <div class="font-bold">
                    Please correct the following errors:
                </div>

                <ul class="mt-1 list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif