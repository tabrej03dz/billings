<x-layouts.app :title="__('User Activity Analytics')">

    @php
        $formatDuration = function ($seconds) {
            $seconds = (int) $seconds;

            $hours = intdiv($seconds, 3600);
            $minutes = intdiv($seconds % 3600, 60);
            $remainingSeconds = $seconds % 60;

            if ($hours > 0) {
                return $hours . 'h ' . $minutes . 'm';
            }

            if ($minutes > 0) {
                return $minutes . 'm ' . $remainingSeconds . 's';
            }

            return $remainingSeconds . 's';
        };
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
                        Dekhein kaunsa user software kitna use kar raha hai,
                        kis page par kitna active time spend kar raha hai aur
                        last activity kab hui.
                    </p>
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
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-600 dark:bg-slate-800"
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
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-600 dark:bg-slate-800"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                    User
                </label>

                <select
                    name="user_id"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-600 dark:bg-slate-800"
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
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm font-semibold text-slate-500">
                    Total Page Views
                </p>

                <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                    {{ number_format($summary['page_views']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm font-semibold text-slate-500">
                    Active Users
                </p>

                <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                    {{ number_format($summary['active_users']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm font-semibold text-slate-500">
                    Average Page Time
                </p>

                <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                    {{ $formatDuration($summary['average_seconds']) }}
                </p>
            </div>

        </div>

        <div class="grid gap-6 xl:grid-cols-2">

            {{-- User Usage --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">

                <div class="border-b border-slate-200 p-5 dark:border-slate-700">
                    <h2 class="text-lg font-black text-slate-950 dark:text-white">
                        User-wise Usage
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        User name par click karke complete details dekhein.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">

                        <thead class="bg-slate-50 text-left dark:bg-slate-800">
                            <tr>
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
                            @forelse($userUsage as $row)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">

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
                                            {{ $row->user?->email }}
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap p-4 font-bold">
                                        {{ $formatDuration($row->total_seconds) }}
                                    </td>

                                    <td class="whitespace-nowrap p-4">
                                        {{ number_format($row->page_views) }}
                                    </td>

                                    <td class="whitespace-nowrap p-4">
                                        {{ number_format($row->active_days) }}
                                    </td>

                                    <td class="whitespace-nowrap p-4 text-xs">
                                        {{ $row->last_seen_at
                                            ? \Carbon\Carbon::parse($row->last_seen_at)->diffForHumans()
                                            : '-'
                                        }}
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="5"
                                        class="p-10 text-center text-slate-500"
                                    >
                                        Is period mein koi activity nahi mili.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                <div class="border-t border-slate-200 p-4 dark:border-slate-700">
                    {{ $userUsage->links() }}
                </div>

            </div>

            {{-- Top Pages --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">

                <div class="border-b border-slate-200 p-5 dark:border-slate-700">
                    <h2 class="text-lg font-black text-slate-950 dark:text-white">
                        Most Used Pages
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Sabse jyada time spend kiye gaye pages.
                    </p>
                </div>

                <div class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($topPages as $page)

                        <div class="flex items-center justify-between gap-5 p-4">

                            <div class="min-w-0">
                                <p class="truncate font-bold text-slate-950 dark:text-white">
                                    {{ $page->route_name ?: $page->path }}
                                </p>

                                <p class="mt-1 truncate text-xs text-slate-500">
                                    {{ $page->path }}
                                </p>
                            </div>

                            <div class="shrink-0 text-right">
                                <p class="font-black text-slate-950 dark:text-white">
                                    {{ $formatDuration($page->total_seconds) }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $page->page_views }} visits
                                    ·
                                    {{ $page->unique_users }} users
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
                                page visits
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

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartElement = document.getElementById(
                'dailyUsageChart'
            );

            if (!chartElement) {
                return;
            }

            new Chart(chartElement, {
                type: 'line',

                data: {
                    labels: @json(
                        $dailyUsage->pluck('activity_date')
                    ),

                    datasets: [
                        {
                            label: 'Usage in minutes',

                            data: @json(
                                $dailyUsage
                                    ->pluck('total_seconds')
                                    ->map(
                                        fn ($seconds) =>
                                        round($seconds / 60, 2)
                                    )
                            ),

                            borderWidth: 3,
                            tension: 0.35,
                            fill: false,
                        }
                    ],
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    scales: {
                        y: {
                            beginAtZero: true,
                        },
                    },
                },
            });
        });
    </script>

</x-layouts.app>