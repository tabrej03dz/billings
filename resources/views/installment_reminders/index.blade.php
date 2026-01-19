<x-layouts.app :title="__('Installment Reminders')">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-6 space-y-8">

        {{-- ========================= PAGE HEADER ========================= --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-2xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 text-white shadow-lg">
                        <i class="fas fa-bell"></i>
                    </span>
                    <span>Installment Reminders</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Reminder list, filters & quick actions.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('installment-reminders.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                          bg-gradient-to-r from-indigo-600 to-purple-600
                          text-white text-sm font-semibold shadow-md
                          hover:from-indigo-700 hover:to-purple-700 transition-all">
                    <i class="fas fa-plus text-xs"></i>
                    Add Reminder
                </a>
                <a href="{{ route('installment-reminders.import-form') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
          bg-slate-900 text-white text-sm font-semibold shadow-md
          hover:bg-slate-800 transition-all">
                    <i class="fas fa-file-import text-xs"></i>
                    Import Excel
                </a>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm
                        dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/20">
                {{ session('success') }}
            </div>
        @endif

        {{-- ========================= FILTER BAR ========================= --}}
        @php
            $from    = request('from', now()->startOfMonth()->toDateString());
            $to      = request('to', now()->toDateString());
            $status  = request('status');
            $qtext   = request('q');

            // Summary (current page items) - aap chahe to controller se proper totals pass kara do
            $totalRows = $reminders->total();
            $uploaded  = \Illuminate\Support\Arr::get($reminders->getCollection()->groupBy('status')->map->count(), 'uploaded', 0);
            $sent      = \Illuminate\Support\Arr::get($reminders->getCollection()->groupBy('status')->map->count(), 'sent', 0);
            $failed    = \Illuminate\Support\Arr::get($reminders->getCollection()->groupBy('status')->map->count(), 'failed', 0);
        @endphp

        <div class="rounded-2xl shadow-sm border border-slate-100 bg-white dark:bg-slate-900 dark:border-slate-800 p-4 sm:p-5">
            <form method="GET" action="{{ url()->current() }}" class="grid gap-4 md:grid-cols-6 items-end">
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">From Date</label>
                    <input type="date" name="from" value="{{ $from }}"
                           class="w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-900
                                  focus:ring-indigo-500 focus:border-indigo-500
                                  dark:bg-slate-950 dark:text-slate-100 dark:border-slate-700 dark:focus:border-indigo-400">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">To Date</label>
                    <input type="date" name="to" value="{{ $to }}"
                           class="w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-900
                                  focus:ring-indigo-500 focus:border-indigo-500
                                  dark:bg-slate-950 dark:text-slate-100 dark:border-slate-700 dark:focus:border-indigo-400">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Status</label>
                    <select name="status"
                            class="w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-900
                                   focus:ring-indigo-500 focus:border-indigo-500
                                   dark:bg-slate-950 dark:text-slate-100 dark:border-slate-700 dark:focus:border-indigo-400">
                        <option value="">All</option>
                        <option value="uploaded" {{ $status === 'uploaded' ? 'selected' : '' }}>Uploaded</option>
                        <option value="sent" {{ $status === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Search</label>
                    <input type="text" name="q" value="{{ $qtext }}" placeholder="Contact / SNME"
                           class="w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-900
                                  focus:ring-indigo-500 focus:border-indigo-500
                                  dark:bg-slate-950 dark:text-slate-100 dark:border-slate-700 dark:focus:border-indigo-400">
                </div>

                <div class="flex gap-2 justify-end">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium
                                   bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm">
                        <i class="fas fa-filter mr-1.5 text-xs"></i> Apply
                    </button>

                    <a href="{{ url()->current() }}"
                       class="inline-flex items-center px-3 py-2 rounded-lg text-xs sm:text-sm font-medium
                              bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200
                              dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:border-slate-700">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- ========================= SUMMARY CARDS ========================= --}}
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl shadow-sm border border-slate-100 bg-white p-4 dark:bg-slate-900 dark:border-slate-800">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">Total Reminders</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($totalRows) }}</p>
                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Date range: {{ $from }} to {{ $to }}</p>
            </div>

            <div class="rounded-2xl shadow-sm border border-emerald-100 bg-white p-4 dark:bg-slate-900 dark:border-slate-800">
                <p class="text-xs font-medium text-emerald-600 uppercase tracking-wide mb-1">Uploaded (page)</p>
                <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ number_format($uploaded) }}</p>
                <p class="mt-1 text-xs text-emerald-500 dark:text-emerald-300/80">Ready to send.</p>
            </div>

            <div class="rounded-2xl shadow-sm border border-rose-100 bg-white p-4 dark:bg-slate-900 dark:border-slate-800">
                <p class="text-xs font-medium text-rose-600 uppercase tracking-wide mb-1">Failed (page)</p>
                <p class="text-2xl font-bold text-rose-700 dark:text-rose-400">{{ number_format($failed) }}</p>
                <p class="mt-1 text-xs text-rose-500 dark:text-rose-300/80">Need retry / check logs.</p>
            </div>
        </div>

        {{-- ========================= TABLE ========================= --}}
        <div class="rounded-2xl shadow-sm border border-slate-100 bg-white overflow-hidden dark:bg-slate-900 dark:border-slate-800">
            <div class="px-4 sm:px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-2 dark:border-slate-800">
                <div>
                    <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                        Reminder List ({{ $reminders->count() }} shown)
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Contact, installment amount/date, schedule, status & actions.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs sm:text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">#</th>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">Contact</th>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">SNME</th>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">Reminder</th>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">Installment</th>
                        <th class="px-3 sm:px-4 py-2 text-right font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">Amount</th>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">Status</th>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">Action</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($reminders as $i => $r)
                        @php
                            $badge = match($r->status) {
                                'sent' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/20',
                                'failed' => 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:border-rose-500/20',
                                default => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20',
                            };
                        @endphp

                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="px-3 sm:px-4 py-2 text-slate-500 dark:text-slate-400">
                                {{ $reminders->firstItem() + $i }}
                            </td>

                            <td class="px-3 sm:px-4 py-2">
                                <div class="flex flex-col">
                                    <span class="font-medium text-slate-800 dark:text-slate-100">
                                        {{ $r->contact_number }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-3 sm:px-4 py-2 text-slate-700 dark:text-slate-200">
                                {{ $r->snme_number ?? '—' }}
                            </td>

                            <td class="px-3 sm:px-4 py-2">
                                <span class="text-slate-800 dark:text-slate-100 font-medium">
                                    {{ \Carbon\Carbon::parse($r->reminder_date)->format('d M Y') }}
                                </span>
                                <span class="block text-[11px] text-slate-400 dark:text-slate-500">
                                    {{ \Carbon\Carbon::parse($r->reminder_time)->format('h:i A') }}
                                </span>
                            </td>

                            <td class="px-3 sm:px-4 py-2">
                                <span class="text-slate-800 dark:text-slate-100 font-medium">
                                    {{ \Carbon\Carbon::parse($r->installment_date)->format('d M Y') }}
                                </span>
                            </td>

                            <td class="px-3 sm:px-4 py-2 text-right font-semibold text-slate-800 dark:text-slate-100">
                                ₹ {{ number_format((float)$r->installment_amount, 2) }}
                            </td>

                            <td class="px-3 sm:px-4 py-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] border {{ $badge }}">
                                    {{ ucfirst($r->status) }}
                                </span>
                            </td>

                            <td class="px-3 sm:px-4 py-2 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('installment-reminders.edit', $r) }}" class="text-indigo-600 hover:underline">
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('installment-reminders.destroy', $r) }}"
                                          onsubmit="return confirm('Delete this reminder?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:underline">
                                            Delete
                                        </button>
                                    </form>

                                    {{-- Optional quick status change --}}
                                    <form method="POST" class="hidden sm:block">
                                        @csrf
                                        <select name="status"
                                                onchange="this.form.submit()"
                                                class="rounded-lg border border-slate-200 bg-white text-[11px] px-2 py-1
                                                       dark:bg-slate-950 dark:text-slate-100 dark:border-slate-700">
                                            <option value="uploaded" {{ $r->status==='uploaded'?'selected':'' }}>Uploaded</option>
                                            <option value="sent" {{ $r->status==='sent'?'selected':'' }}>Sent</option>
                                            <option value="failed" {{ $r->status==='failed'?'selected':'' }}>Failed</option>
                                        </select>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-4 text-center text-sm text-slate-500 dark:text-slate-400">
                                No reminders found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="px-4 sm:px-5 py-3 border-t border-slate-100 dark:border-slate-800">
                    {{ $reminders->links() }}
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
