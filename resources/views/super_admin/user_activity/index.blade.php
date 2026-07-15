<x-layouts.app :title="__('User Activity Analytics')">

    @php
        /*
        |--------------------------------------------------------------------------
        | Duration Formatter
        |--------------------------------------------------------------------------
        */

        $formatDuration = function ($seconds) {
            $seconds = max((int) $seconds, 0);

            $days = intdiv($seconds, 86400);
            $hours = intdiv($seconds % 86400, 3600);
            $minutes = intdiv($seconds % 3600, 60);
            $remainingSeconds = $seconds % 60;

            if ($days > 0) {
                return $days . 'd '
                    . $hours . 'h '
                    . $minutes . 'm';
            }

            if ($hours > 0) {
                return $hours . 'h '
                    . $minutes . 'm';
            }

            if ($minutes > 0) {
                return $minutes . 'm '
                    . $remainingSeconds . 's';
            }

            return $remainingSeconds . 's';
        };

        /*
        |--------------------------------------------------------------------------
        | Rank Position With Pagination
        |--------------------------------------------------------------------------
        */

        $firstItemNumber = $userUsage->firstItem() ?: 1;

        $selectedUser = $selectedUserId
            ? $users->firstWhere('id', $selectedUserId)
            : null;
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6">

        {{-- Header --}}
        <div class="overflow-hidden rounded-3xl bg-slate-950 p-6 text-white shadow-xl sm:p-8">

            <div class="flex flex-wrap items-start justify-between gap-5">

                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-cyan-300">
                        Super Admin Analytics
                    </p>

                    <h1 class="mt-3 text-2xl font-black sm:text-4xl">
                        User Software Usage
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                        Dekhein kaunsa user software sabse zyada use kar raha hai,
                        kis page par kitna active time spend kar raha hai aur
                        uski latest activity kab hui.
                    </p>

                    @if($selectedUser)
                        <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-blue-500/20 px-4 py-2 text-sm text-blue-100">
                            Filtered User:
                            <strong>
                                {{ $selectedUser->name }}
                            </strong>
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">

                    <p class="text-xs text-slate-300">
                        Selected Period
                    </p>

                    <p class="mt-1 font-bold">
                        {{ $from->format('d M Y') }}
                        -
                        {{ $to->format('d M Y') }}
                    </p>

                </div>

            </div>

        </div>

        {{-- Filters --}}
        <form
            method="GET"
            action="{{ route('super-admin.user-activity.index') }}"
            class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-4 dark:border-slate-700 dark:bg-slate-900"
        >

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                    From Date
                </label>

                <input
                    type="date"
                    name="from"
                    value="{{ request('from', $from->format('Y-m-d')) }}"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                    To Date
                </label>

                <input
                    type="date"
                    name="to"
                    value="{{ request('to', $to->format('Y-m-d')) }}"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                    User
                </label>

                <select
                    name="user_id"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                >
                    <option value="">
                        All Users
                    </option>

                    @foreach($users as $filterUser)
                        <option
                            value="{{ $filterUser->id }}"
                            @selected(
                                (string) request('user_id')
                                ===
                                (string) $filterUser->id
                            )
                        >
                            {{ $filterUser->name }}

                            @if($filterUser->email)
                                ({{ $filterUser->email }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">

                <button
                    type="submit"
                    class="rounded-xl bg-blue-600 px-5 py-2.5 font-bold text-white transition hover:bg-blue-700"
                >
                    Apply
                </button>

                <a
                    href="{{ route('super-admin.user-activity.index') }}"
                    class="rounded-xl border border-slate-300 px-5 py-2.5 font-bold text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:text-white dark:hover:bg-slate-800"
                >
                    Reset
                </a>

            </div>

        </form>

        {{-- Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">

                <p class="text-sm font-semibold text-slate-500">
                    Total Active Usage
                </p>

                <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                    {{ $formatDuration($summary['total_seconds']) }}
                </p>

                <p class="mt-2 text-xs text-slate-400">
                    Actual active software time
                </p>

            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">

                <p class="text-sm font-semibold text-slate-500">
                    Total Page Views
                </p>

                <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                    {{ number_format($summary['page_views']) }}
                </p>

                <p class="mt-2 text-xs text-slate-400">
                    Total tracked visits
                </p>

            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">

                <p class="text-sm font-semibold text-slate-500">
                    Active Users
                </p>

                <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                    {{ number_format($summary['active_users']) }}
                </p>

                <p class="mt-2 text-xs text-slate-400">
                    Unique users in selected period
                </p>

            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">

                <p class="text-sm font-semibold text-slate-500">
                    Average Page Time
                </p>

                <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                    {{ $formatDuration($summary['average_seconds']) }}
                </p>

                <p class="mt-2 text-xs text-slate-400">
                    Average active time per visit
                </p>

            </div>

        </div>

        <div class="grid gap-6 xl:grid-cols-2">

            {{-- User-wise Usage --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-5 dark:border-slate-700">

                    <div>
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">
                            User-wise Usage Ranking
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Sabse zyada active time wala user sabse upar hai.
                        </p>
                    </div>

                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                        High to Low
                    </span>

                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full text-sm">

                        <thead class="bg-slate-50 text-left dark:bg-slate-800">
                            <tr>
                                <th class="whitespace-nowrap p-4">
                                    Rank
                                </th>

                                <th class="whitespace-nowrap p-4">
                                    User
                                </th>

                                <th class="whitespace-nowrap p-4">
                                    Usage
                                </th>

                                <th class="whitespace-nowrap p-4">
                                    Views
                                </th>

                                <th class="whitespace-nowrap p-4">
                                    Active Days
                                </th>

                                <th class="whitespace-nowrap p-4">
                                    Last Seen
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">

                            @forelse($userUsage as $index => $row)

                                @php
                                    $rank = $firstItemNumber + $index;
                                @endphp

                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">

                                    <td class="whitespace-nowrap p-4">

                                        @if($rank === 1)
                                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-amber-100 font-black text-amber-700">
                                                1
                                            </span>
                                        @elseif($rank === 2)
                                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 font-black text-slate-700">
                                                2
                                            </span>
                                        @elseif($rank === 3)
                                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-orange-100 font-black text-orange-700">
                                                3
                                            </span>
                                        @else
                                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                {{ $rank }}
                                            </span>
                                        @endif

                                    </td>

                                    <td class="p-4">

                                        <a
                                            href="{{ route('super-admin.user-activity.show', [
                                                'user' => $row->user_id,
                                                'from' => request('from', $from->format('Y-m-d')),
                                                'to' => request('to', $to->format('Y-m-d')),
                                            ]) }}"
                                            class="font-bold text-blue-600 hover:underline"
                                        >
                                            {{ $row->user?->name ?? 'Deleted User' }}
                                        </a>

                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ $row->user?->email ?: 'No email' }}
                                        </div>

                                    </td>

                                    <td class="whitespace-nowrap p-4">

                                        <span class="font-black text-slate-950 dark:text-white">
                                            {{ $formatDuration($row->total_seconds) }}
                                        </span>

                                        @if($rank === 1 && (int) $row->total_seconds > 0)
                                            <div class="mt-1 text-xs font-bold text-emerald-600">
                                                Highest Usage
                                            </div>
                                        @endif

                                    </td>

                                    <td class="whitespace-nowrap p-4">
                                        {{ number_format($row->page_views) }}
                                    </td>

                                    <td class="whitespace-nowrap p-4">
                                        {{ number_format($row->active_days) }}
                                    </td>

                                    <td class="whitespace-nowrap p-4 text-xs">

                                        @if($row->last_seen_at)
                                            <span
                                                title="{{ \Carbon\Carbon::parse($row->last_seen_at)->format('d M Y, h:i A') }}"
                                            >
                                                {{ \Carbon\Carbon::parse($row->last_seen_at)->diffForHumans() }}
                                            </span>
                                        @else
                                            -
                                        @endif

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td
                                        colspan="6"
                                        class="p-10 text-center text-slate-500"
                                    >
                                        Is selected period mein koi user activity nahi mili.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                @if($userUsage->hasPages())
                    <div class="border-t border-slate-200 p-4 dark:border-slate-700">
                        {{ $userUsage->links() }}
                    </div>
                @endif

            </div>

            {{-- Top Pages --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">

                <div class="flex items-center justify-between gap-3 border-b border-slate-200 p-5 dark:border-slate-700">

                    <div>
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">
                            Most Used Pages
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Sabse zyada active time spend kiye gaye pages.
                        </p>
                    </div>

                    <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700 dark:bg-violet-900/30 dark:text-violet-300">
                        Top 15
                    </span>

                </div>

                <div class="divide-y divide-slate-200 dark:divide-slate-700">

                    @forelse($topPages as $index => $page)

                        <div class="flex items-center justify-between gap-5 p-4">

                            <div class="flex min-w-0 items-center gap-3">

                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-black text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $index + 1 }}
                                </span>

                                <div class="min-w-0">

                                    <p class="truncate font-bold text-slate-950 dark:text-white">
                                        {{ $page->route_name ?: $page->path }}
                                    </p>

                                    <p class="mt-1 truncate text-xs text-slate-500">
                                        {{ $page->path }}
                                    </p>

                                </div>

                            </div>

                            <div class="shrink-0 text-right">

                                <p class="font-black text-slate-950 dark:text-white">
                                    {{ $formatDuration($page->total_seconds) }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ number_format($page->page_views) }}
                                    visits ·
                                    {{ number_format($page->unique_users) }}
                                    users
                                </p>

                            </div>

                        </div>

                    @empty

                        <div class="p-10 text-center text-slate-500">
                            Koi page usage data nahi mila.
                        </div>

                    @endforelse

                </div>

            </div>

        </div>

        <div class="grid gap-6 xl:grid-cols-3">

            {{-- Daily Chart --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900 xl:col-span-2">

                <h2 class="text-lg font-black text-slate-950 dark:text-white">
                    Daily Usage
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Date-wise active software usage in minutes.
                </p>

                <div class="mt-5 h-80">
                    <canvas id="dailyUsageChart"></canvas>
                </div>

            </div>

            {{-- Device Usage --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">

                <h2 class="text-lg font-black text-slate-950 dark:text-white">
                    Device Usage
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Usage grouped by device.
                </p>

                <div class="mt-5 space-y-3">

                    @forelse($deviceUsage as $device)

                        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800">

                            <div class="flex items-center justify-between gap-3">

                                <span class="font-bold capitalize">
                                    {{ $device->device_type ?: 'Unknown' }}
                                </span>

                                <span class="font-black">
                                    {{ $formatDuration($device->total_seconds) }}
                                </span>

                            </div>

                            <p class="mt-2 text-xs text-slate-500">
                                {{ number_format($device->page_views) }}
                                page visits ·
                                {{ number_format($device->unique_users) }}
                                users
                            </p>

                        </div>

                    @empty

                        <p class="py-8 text-center text-sm text-slate-500">
                            No device data found.
                        </p>

                    @endforelse

                </div>

            </div>

        </div>

        {{-- Browser Usage --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">

            <h2 class="text-lg font-black text-slate-950 dark:text-white">
                Browser Usage
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Browser ke according total active usage.
            </p>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">

                @forelse($browserUsage as $browser)

                    <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800">

                        <p class="font-bold text-slate-950 dark:text-white">
                            {{ $browser->browser ?: 'Unknown' }}
                        </p>

                        <p class="mt-2 text-lg font-black">
                            {{ $formatDuration($browser->total_seconds) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ number_format($browser->page_views) }}
                            visits
                        </p>

                    </div>

                @empty

                    <p class="text-sm text-slate-500">
                        Browser usage data nahi mila.
                    </p>

                @endforelse

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const chartElement =
                    document.getElementById(
                        'dailyUsageChart'
                    );

                if (!chartElement) {
                    return;
                }

                new Chart(chartElement, {
                    type: 'line',

                    data: {
                        labels: @json(
                            $dailyUsage->pluck(
                                'activity_date'
                            )
                        ),

                        datasets: [
                            {
                                label: 'Usage in minutes',

                                data: @json(
                                    $dailyUsage
                                        ->pluck('total_seconds')
                                        ->map(
                                            fn ($seconds) =>
                                                round(
                                                    $seconds / 60,
                                                    2
                                                )
                                        )
                                        ->values()
                                ),

                                borderWidth: 3,
                                tension: 0.35,
                                fill: false,
                            },
                            {
                                label: 'Page views',

                                data: @json(
                                    $dailyUsage
                                        ->pluck('page_views')
                                        ->map(
                                            fn ($views) =>
                                                (int) $views
                                        )
                                        ->values()
                                ),

                                borderWidth: 2,
                                tension: 0.35,
                                fill: false,
                            },
                        ],
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },

                        plugins: {
                            legend: {
                                display: true,
                            },
                        },

                        scales: {
                            y: {
                                beginAtZero: true,
                            },
                        },
                    },
                });
            }
        );
    </script>

</x-layouts.app>