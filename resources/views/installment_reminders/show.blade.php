<x-layouts.app :title="__('Reminder Details')">
    <div class="max-w-5xl mx-auto px-3 sm:px-4 lg:px-6 py-6 space-y-6">

        {{-- ========================= HEADER ========================= --}}
        @php
            $r = $installmentReminder;

            $badge = match($r->status) {
                'sent' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/20',
                'failed' => 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:border-rose-500/20',
                default => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20',
            };
        @endphp

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-2xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 text-white shadow-lg">
                        <i class="fas fa-eye"></i>
                    </span>
                    <span>Reminder Details</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    View full reminder information & logs.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('installment-reminders.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200
                          text-slate-700 text-sm font-semibold shadow-sm hover:bg-slate-50
                          dark:bg-slate-900 dark:border-slate-800 dark:text-slate-200 dark:hover:bg-slate-800">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Back
                </a>

                <a href="{{ route('installment-reminders.edit', ['installmentReminder' => $r->id]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                          bg-indigo-600 text-white text-sm font-semibold shadow-md hover:bg-indigo-700">
                    <i class="fas fa-pen text-xs"></i>
                    Edit
                </a>

                <form method="POST"
                      action="{{ route('installment-reminders.destroy', ['installmentReminder' => $r->id]) }}"
                      onsubmit="return confirm('Delete this reminder?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                                   bg-rose-600 text-white text-sm font-semibold shadow-md hover:bg-rose-700">
                        <i class="fas fa-trash text-xs"></i>
                        Delete
                    </button>
                </form>
            </div>
        </div>

        {{-- ========================= STATUS STRIP ========================= --}}
        <div class="rounded-2xl border border-slate-100 bg-white dark:bg-slate-900 dark:border-slate-800 p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                        <i class="fas fa-bell text-slate-600 dark:text-slate-200"></i>
                    </div>

                    <div>
                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            Contact: {{ $r->contact_number }}
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">
                            Reminder ID: #{{ $r->id }} {{ $r->user_id ? "• User ID: {$r->user_id}" : '' }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[12px] border {{ $badge }}">
                        <span class="h-1.5 w-1.5 rounded-full bg-current mr-2 opacity-70"></span>
                        {{ ucfirst($r->status) }}
                    </span>

                    @if($r->sent_at)
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            Sent at: {{ \Carbon\Carbon::parse($r->sent_at)->format('d M Y, h:i A') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ========================= DETAILS GRID ========================= --}}
        <div class="grid gap-4 lg:grid-cols-2">

            {{-- Reminder Info --}}
            <div class="rounded-2xl border border-slate-100 bg-white dark:bg-slate-900 dark:border-slate-800 p-4 sm:p-5">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-2xl bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                        <i class="fas fa-calendar-alt text-xs"></i>
                    </span>
                    Reminder Info
                </h2>

                <dl class="mt-4 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-slate-500 dark:text-slate-400">Reminder Date</dt>
                        <dd class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ \Carbon\Carbon::parse($r->reminder_date)->format('d M Y') }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-slate-500 dark:text-slate-400">Reminder Time</dt>
                        <dd class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ \Carbon\Carbon::parse($r->reminder_time)->format('h:i A') }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-slate-500 dark:text-slate-400">SNME Number</dt>
                        <dd class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ $r->snme_number ?? '—' }}
                        </dd>
                    </div>

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800"></div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-slate-500 dark:text-slate-400">Installment Date</dt>
                        <dd class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ \Carbon\Carbon::parse($r->installment_date)->format('d M Y') }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-slate-500 dark:text-slate-400">Installment Amount</dt>
                        <dd class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                            ₹ {{ number_format((float)$r->installment_amount, 2) }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Meta + Logs --}}
            <div class="rounded-2xl border border-slate-100 bg-white dark:bg-slate-900 dark:border-slate-800 p-4 sm:p-5">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-2xl bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        <i class="fas fa-info-circle text-xs"></i>
                    </span>
                    Meta & Logs
                </h2>

                <dl class="mt-4 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-slate-500 dark:text-slate-400">Status</dt>
                        <dd>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] border {{ $badge }}">
                                {{ ucfirst($r->status) }}
                            </span>
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-slate-500 dark:text-slate-400">Sent At</dt>
                        <dd class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ $r->sent_at ? \Carbon\Carbon::parse($r->sent_at)->format('d M Y, h:i A') : '—' }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-slate-500 dark:text-slate-400">Created</dt>
                        <dd class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ $r->created_at ? $r->created_at->format('d M Y, h:i A') : '—' }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-slate-500 dark:text-slate-400">Updated</dt>
                        <dd class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ $r->updated_at ? $r->updated_at->format('d M Y, h:i A') : '—' }}
                        </dd>
                    </div>
                </dl>

                {{-- Response Box --}}
                <div class="mt-5">
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">
                        Provider Response / Log
                    </label>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-700 whitespace-pre-wrap
                                dark:bg-slate-950 dark:text-slate-200 dark:border-slate-800 min-h-[120px]">
                        {{ $r->response ?? '—' }}
                    </div>

                    <p class="mt-2 text-[11px] text-slate-400 dark:text-slate-500">
                        Tip: response JSON ho to yahin easily copy ho jayega.
                    </p>
                </div>
            </div>

        </div>

    </div>
</x-layouts.app>
