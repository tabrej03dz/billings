<x-layouts.app :title="__('Dashboard')">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-6 space-y-8">

        {{-- ========================= PAGE HEADER ========================= --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-2xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 text-white shadow-lg">
                        <i class="fas fa-paper-plane"></i>
                    </span>
                    <span>Invoice Send Report</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    User-wise summary & latest send logs for invoices (WhatsApp / Email / SMS).
                </p>
            </div>
        </div>

        {{-- ========================= FILTER BAR ========================= --}}
        <div class="rounded-2xl shadow-sm border border-slate-100 bg-white
                    dark:bg-slate-900 dark:border-slate-800 p-4 sm:p-5">
            <form method="GET" action="{{ url()->current() }}" class="grid gap-4 md:grid-cols-5 items-end">
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                        From Date
                    </label>
                    <input type="date"
                           name="from"
                           value="{{ $from }}"
                           class="w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-900
                                  focus:ring-indigo-500 focus:border-indigo-500
                                  dark:bg-slate-950 dark:text-slate-100 dark:border-slate-700 dark:focus:border-indigo-400">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                        To Date
                    </label>
                    <input type="date"
                           name="to"
                           value="{{ $to }}"
                           class="w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-900
                                  focus:ring-indigo-500 focus:border-indigo-500
                                  dark:bg-slate-950 dark:text-slate-100 dark:border-slate-700 dark:focus:border-indigo-400">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                        Channel
                    </label>
                    <select name="channel"
                            class="w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-900
                                   focus:ring-indigo-500 focus:border-indigo-500
                                   dark:bg-slate-950 dark:text-slate-100 dark:border-slate-700 dark:focus:border-indigo-400">
                        <option value="">All</option>
                        <option value="whatsapp" {{ $channel === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="email" {{ $channel === 'email' ? 'selected' : '' }}>Email</option>
                        <option value="sms" {{ $channel === 'sms' ? 'selected' : '' }}>SMS</option>
                    </select>
                </div>

                <div class="flex gap-2 md:col-span-2 justify-end">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium
                                   bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm">
                        <i class="fas fa-filter mr-1.5 text-xs"></i> Apply Filters
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

        @php
            $totalSends    = $perUser->sum('total');
            $totalSuccess  = $perUser->sum('success_count');
            $totalFailed   = $totalSends - $totalSuccess;
        @endphp

        {{-- ========================= SUMMARY CARDS ========================= --}}
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl shadow-sm border border-slate-100 bg-white p-4
                        dark:bg-slate-900 dark:border-slate-800">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">
                    Total Sends
                </p>
                <div class="flex items-end justify-between">
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                        {{ number_format($totalSends) }}
                    </p>
                </div>
                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                    Date range: {{ $from }} to {{ $to }}
                </p>
            </div>

            <div class="rounded-2xl shadow-sm border border-emerald-100 bg-white p-4
                        dark:bg-slate-900 dark:border-slate-800">
                <p class="text-xs font-medium text-emerald-600 uppercase tracking-wide mb-1">
                    Successful Sends
                </p>
                <div class="flex items-end justify-between">
                    <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">
                        {{ number_format($totalSuccess) }}
                    </p>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100
                                 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/20">
                        {{ $totalSends > 0 ? round(($totalSuccess / max($totalSends,1)) * 100, 1) : 0 }}%
                    </span>
                </div>
                <p class="mt-1 text-xs text-emerald-500 dark:text-emerald-300/80">
                    Delivered by provider successfully.
                </p>
            </div>

            <div class="rounded-2xl shadow-sm border border-rose-100 bg-white p-4
                        dark:bg-slate-900 dark:border-slate-800">
                <p class="text-xs font-medium text-rose-600 uppercase tracking-wide mb-1">
                    Failed Sends
                </p>
                <div class="flex items-end justify-between">
                    <p class="text-2xl font-bold text-rose-700 dark:text-rose-400">
                        {{ number_format($totalFailed) }}
                    </p>
                </div>
                <p class="mt-1 text-xs text-rose-500 dark:text-rose-300/80">
                    Check error messages & response codes below.
                </p>
            </div>
        </div>

        {{-- ========================= PER USER SUMMARY ========================= --}}
        <div class="rounded-2xl shadow-sm border border-slate-100 bg-white overflow-hidden
                    dark:bg-slate-900 dark:border-slate-800">
            <div class="px-4 sm:px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-2
                        dark:border-slate-800">
                <div>
                    <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                        User wise summary
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        How many invoices each user sent in selected date range.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide">#</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide">User</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide">Total Sends</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide">Success</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide">Failed</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide">Success %</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($perUser as $index => $row)
                        @php
                            $failed = $row->total - $row->success_count;
                            $rate   = $row->total > 0 ? round(($row->success_count / $row->total) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-2 text-xs text-slate-500 dark:text-slate-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex flex-col">
                                    <span class="font-medium text-slate-800 dark:text-slate-100">
                                        {{ $row->user->name ?? 'Unknown User #'.$row->user_id }}
                                    </span>
                                    @if($row->user && $row->user->email)
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500">
                                            {{ $row->user->email }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-2 text-right font-medium text-slate-800 dark:text-slate-100">
                                {{ $row->total }}
                            </td>
                            <td class="px-4 py-2 text-right text-emerald-600 dark:text-emerald-400 font-medium">
                                {{ $row->success_count }}
                            </td>
                            <td class="px-4 py-2 text-right text-rose-500 dark:text-rose-400 font-medium">
                                {{ $failed }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px]
                                             {{ $rate >= 80 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}
                                             dark:bg-slate-800 dark:text-slate-200 dark:border dark:border-slate-700">
                                    {{ $rate }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-center text-sm text-slate-500 dark:text-slate-400">
                                No records found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ========================= LATEST SENDS TABLE ========================= --}}
        <div class="rounded-2xl shadow-sm border border-slate-100 bg-white overflow-hidden
                    dark:bg-slate-900 dark:border-slate-800">
            <div class="px-4 sm:px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-2
                        dark:border-slate-800">
                <div>
                    <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                        Latest sends (last {{ $latestSends->count() }})
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Detailed logs of each send attempt.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs sm:text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">Sent At</th>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">User</th>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">Invoice</th>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">Channel</th>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">Recipient</th>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">Status</th>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">Provider</th>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">File</th>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">Error</th>
                        <th class="px-3 sm:px-4 py-2 text-left font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wide text-[11px]">Action</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($latestSends as $send)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="px-3 sm:px-4 py-2 align-top whitespace-nowrap">
                                <span class="text-slate-800 dark:text-slate-100 font-medium">
                                    {{ optional($send->sent_at)->format('d M Y') ?? '-' }}
                                </span>
                                <span class="block text-[11px] text-slate-400 dark:text-slate-500">
                                    {{ optional($send->sent_at)->format('h:i A') ?? '' }}
                                </span>
                            </td>

                            <td class="px-3 sm:px-4 py-2 align-top">
                                <div class="flex flex-col">
                                    <span class="font-medium text-slate-800 dark:text-slate-100">
                                        {{ $send->user->name ?? 'Unknown' }}
                                    </span>
                                    @if($send->user && $send->user->email)
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500">
                                            {{ $send->user->email }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-3 sm:px-4 py-2 align-top">
                                @if($send->invoice)
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-800 dark:text-slate-100">
                                            #{{ $send->invoice->id }}
                                        </span>
                                        @if($send->invoice->invoice_number ?? false)
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500">
                                                {{ $send->invoice->invoice_number }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400 dark:text-slate-500">N/A</span>
                                @endif
                            </td>

                            <td class="px-3 sm:px-4 py-2 align-top whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px]
                                             bg-slate-100 text-slate-700 border border-slate-200
                                             dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700">
                                    {{ ucfirst($send->channel) }}
                                </span>
                            </td>

                            <td class="px-3 sm:px-4 py-2 align-top text-[11px] sm:text-xs">
                                @if($send->recipient_phone)
                                    <div class="text-slate-800 dark:text-slate-100">
                                        {{ $send->recipient_phone }}
                                    </div>
                                @endif
                                @if($send->recipient_email)
                                    <div class="text-slate-500 dark:text-slate-400">
                                        {{ $send->recipient_email }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-3 sm:px-4 py-2 align-top">
                                @php
                                    $badgeClasses = match($send->status) {
                                        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/20',
                                        'failed'  => 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:border-rose-500/20',
                                        default   => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20',
                                    };
                                @endphp
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] border {{ $badgeClasses }}">
                                        {{ ucfirst($send->status) }}
                                    </span>
                                    @if($send->response_code)
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500">
                                            Code: {{ $send->response_code }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-3 sm:px-4 py-2 align-top text-[11px] sm:text-xs">
                                @if($send->provider_message_id)
                                    <span class="text-slate-700 dark:text-slate-200">
                                        {{ Str::limit($send->provider_message_id, 20) }}
                                    </span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>

                            <td class="px-3 sm:px-4 py-2 align-top text-[11px] sm:text-xs">
                                @if($send->file_url)
                                    <a href="{{ $send->file_url }}" target="_blank"
                                       class="inline-flex items-center px-2 py-0.5 rounded-full border border-slate-200 text-[11px]
                                              text-slate-700 hover:bg-slate-50
                                              dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                        View
                                    </a>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>

                            <td class="px-3 sm:px-4 py-2 align-top text-[11px] sm:text-xs max-w-xs">
                                @if($send->error_message)
                                    <span class="block text-rose-500 truncate" title="{{ $send->error_message }}">
                                        {{ Str::limit($send->error_message, 80) }}
                                    </span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-4 py-2 align-top text-[11px] sm:text-xs max-w-xs">
                                <a href="{{ route('no-business.pdfs.retry', ['invoice' => $send->id]) }}"
                                   class="text-indigo-600 hover:underline">
                                    Resend
                                </a>
                                @if($send->status !== 'success')
                                    <a href="{{ route('no-business.pdfs.retry', ['invoice' => $send->id]) }}"
                                       class="text-indigo-600 hover:underline">
                                        Resend
                                    </a>
                                @else
                                    <span class="text-gray-400 cursor-not-allowed">
                                        Sent
                                    </span>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-4 text-center text-sm text-slate-500 dark:text-slate-400">
                                No send logs found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
                <div class="px-4 sm:px-5 py-3 border-t border-slate-100 dark:border-slate-800">
                    {{ $latestSends->links() }}
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
