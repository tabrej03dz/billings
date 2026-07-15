<x-layouts.app :title="__('User Activity Details')">

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
        <div class="rounded-3xl bg-slate-950 p-6 text-white shadow-xl">

            <div class="flex flex-wrap items-center justify-between gap-5">

                <div>
                    <a
                        href="{{ route('super-admin.user-activity.index', [
                            'from' => request('from'),
                            'to' => request('to'),
                        ]) }}"
                        class="text-sm font-semibold text-cyan-300 hover:underline"
                    >
                        ← Back to analytics
                    </a>

                    <h1 class="mt-3 text-2xl font-black sm:text-3xl">
                        {{ $user->name }}
                    </h1>

                    <p class="mt-1 text-sm text-slate-300">
                        {{ $user->email }}
                    </p>
                </div>

                <form
                    method="GET"
                    action="{{ route('super-admin.user-activity.show', $user) }}"
                    class="flex flex-wrap gap-2"
                >
                    <input
                        type="date"
                        name="from"
                        value="{{ request('from', $from->format('Y-m-d')) }}"
                        class="rounded-xl border-0 px-3 py-2 text-slate-900"
                    >

                    <input
                        type="date"
                        name="to"
                        value="{{ request('to', $to->format('Y-m-d')) }}"
                        class="rounded-xl border-0 px-3 py-2 text-slate-900"
                    >

                    <button
                        type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-2 font-bold text-white hover:bg-blue-700"
                    >
                        Apply
                    </button>
                </form>

            </div>

        </div>

        {{-- Summary --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm font-semibold text-slate-500">
                    Total Active Usage
                </p>

                <p class="mt-2 text-2xl font-black">
                    {{ $formatDuration($totals['total_seconds']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm font-semibold text-slate-500">
                    Page Views
                </p>

                <p class="mt-2 text-2xl font-black">
                    {{ number_format($totals['page_views']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm font-semibold text-slate-500">
                    Active Days
                </p>

                <p class="mt-2 text-2xl font-black">
                    {{ number_format($totals['active_days']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm font-semibold text-slate-500">
                    Last Seen
                </p>

                <p class="mt-2 text-lg font-black">
                    {{ $totals['last_seen']
                        ? \Carbon\Carbon::parse($totals['last_seen'])->diffForHumans()
                        : '-'
                    }}
                </p>
            </div>

        </div>

        <div class="grid gap-6 xl:grid-cols-3">

            {{-- Page-wise Usage --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900 xl:col-span-2">

                <div class="border-b border-slate-200 p-5 dark:border-slate-700">
                    <h2 class="text-lg font-black">
                        Page-wise Usage
                    </h2>
                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full text-sm">

                        <thead class="bg-slate-50 text-left dark:bg-slate-800">
                            <tr>
                                <th class="p-4">
                                    Page
                                </th>

                                <th class="p-4">
                                    Total Time
                                </th>

                                <th class="p-4">
                                    Visits
                                </th>

                                <th class="p-4">
                                    Average
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($pageStats as $page)

                                <tr>
                                    <td class="p-4">
                                        <strong>
                                            {{ $page->route_name ?: $page->path }}
                                        </strong>

                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ $page->path }}
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap p-4 font-bold">
                                        {{ $formatDuration($page->total_seconds) }}
                                    </td>

                                    <td class="whitespace-nowrap p-4">
                                        {{ number_format($page->page_views) }}
                                    </td>

                                    <td class="whitespace-nowrap p-4">
                                        {{ $formatDuration($page->average_seconds) }}
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td
                                        colspan="4"
                                        class="p-10 text-center text-slate-500"
                                    >
                                        No page usage found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>

                </div>

            </div>

            {{-- Device and Browser Usage --}}
            <div class="space-y-6">

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">

                    <h2 class="text-lg font-black">
                        Device Usage
                    </h2>

                    <div class="mt-4 space-y-3">
                        @forelse($deviceStats as $device)

                            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800">

                                <div class="flex items-center justify-between gap-3">

                                    <span class="font-bold capitalize">
                                        {{ $device->device_type ?: 'Unknown' }}
                                    </span>

                                    <span class="font-black">
                                        {{ $formatDuration($device->total_seconds) }}
                                    </span>

                                </div>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $device->page_views }} page visits
                                </p>

                            </div>

                        @empty
                            <p class="py-5 text-center text-sm text-slate-500">
                                No device data found.
                            </p>
                        @endforelse
                    </div>

                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">

                    <h2 class="text-lg font-black">
                        Browser Usage
                    </h2>

                    <div class="mt-4 space-y-3">
                        @forelse($browserStats as $browser)

                            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800">

                                <div class="flex items-center justify-between gap-3">

                                    <span class="font-bold">
                                        {{ $browser->browser ?: 'Unknown' }}
                                    </span>

                                    <span class="font-black">
                                        {{ $formatDuration($browser->total_seconds) }}
                                    </span>

                                </div>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $browser->page_views }} page visits
                                </p>

                            </div>

                        @empty
                            <p class="py-5 text-center text-sm text-slate-500">
                                No browser data found.
                            </p>
                        @endforelse
                    </div>

                </div>

            </div>

        </div>

        {{-- Complete Activity Log --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">

            <div class="border-b border-slate-200 p-5 dark:border-slate-700">
                <h2 class="text-lg font-black">
                    Complete Activity Log
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Har page visit ka date, duration, device aur IP address.
                </p>
            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full text-sm">

                    <thead class="bg-slate-50 text-left dark:bg-slate-800">
                        <tr>
                            <th class="whitespace-nowrap p-4">
                                Date / Time
                            </th>

                            <th class="p-4">
                                Page
                            </th>

                            <th class="whitespace-nowrap p-4">
                                Duration
                            </th>

                            <th class="whitespace-nowrap p-4">
                                Device
                            </th>

                            <th class="whitespace-nowrap p-4">
                                IP Address
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($activities as $activity)

                            <tr>

                                <td class="whitespace-nowrap p-4">
                                    {{ optional($activity->started_at)
                                        ->format('d M Y, h:i A')
                                    }}
                                </td>

                                <td class="p-4">
                                    <strong>
                                        {{ $activity->page_title
                                            ?: ($activity->route_name ?: $activity->path)
                                        }}
                                    </strong>

                                    <div class="mt-1 max-w-md truncate text-xs text-slate-500">
                                        {{ $activity->path }}
                                    </div>

                                    @if($activity->route_name)
                                        <div class="mt-1 text-xs text-blue-500">
                                            {{ $activity->route_name }}
                                        </div>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap p-4 font-bold">
                                    {{ $formatDuration($activity->duration_seconds) }}
                                </td>

                                <td class="whitespace-nowrap p-4">
                                    <span class="capitalize">
                                        {{ $activity->device_type ?: 'Unknown' }}
                                    </span>

                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $activity->browser }}
                                        /
                                        {{ $activity->platform }}
                                    </div>
                                </td>

                                <td class="whitespace-nowrap p-4">
                                    {{ $activity->ip_address ?: '-' }}
                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td
                                    colspan="5"
                                    class="p-10 text-center text-slate-500"
                                >
                                    Is user ki koi activity nahi mili.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>

            <div class="border-t border-slate-200 p-4 dark:border-slate-700">
                {{ $activities->links() }}
            </div>

        </div>

    </div>

</x-layouts.app>