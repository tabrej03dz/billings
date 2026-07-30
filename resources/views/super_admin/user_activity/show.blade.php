<x-layouts.app :title="__('User Activity Details')">

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
        | Total Error Count
        |--------------------------------------------------------------------------
        */

        $totalErrors = (int) (
            $totals['total_errors']
            ?? $activities->sum('error_count')
            ?? 0
        );
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6">

        @if(session('success'))
            <div
                class="rounded-2xl border border-emerald-300 bg-emerald-50 p-4 font-semibold text-emerald-700 shadow-sm dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300"
            >
                {{ session('success') }}
            </div>
        @endif

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

                    <div class="mt-3 flex flex-wrap items-center gap-3">

                        <h1 class="text-2xl font-black sm:text-3xl">
                            {{ $user->name }}
                        </h1>

                        @if($totalErrors > 0)
                            <span class="inline-flex items-center gap-1 rounded-full bg-red-500 px-3 py-1 text-xs font-black text-white">
                                ⚠ {{ number_format($totalErrors) }}
                                {{ $totalErrors === 1 ? 'Error' : 'Errors' }}
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-bold text-emerald-200">
                                No Error
                            </span>
                        @endif

                    </div>

                    <p class="mt-1 text-sm text-slate-300">
                        {{ $user->email ?: 'No email available' }}
                    </p>

                    @if($totalErrors > 0)
                        <p class="mt-3 text-sm font-semibold text-red-300">
                            Is user ko selected period mein software errors mile hain.
                        </p>
                    @endif
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


                <form
                    method="POST"
                    action="{{ route('super-admin.user-activity.clear-user', [
                        'user' => $user->id,
                        'from' => request('from', $from->format('Y-m-d')),
                        'to' => request('to', $to->format('Y-m-d')),
                    ]) }}"
                    onsubmit="return confirm(
                        'Kya aap selected date range ki is user ki saari activity aur error logs permanently clear karna chahte hain?'
                    )"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2 font-bold text-white shadow-sm transition hover:bg-red-700"
                    >
                        🗑 Clear Activity
                    </button>
                </form>

            </div>

        </div>

        {{-- Error Warning --}}
        @if($totalErrors > 0)
            <div class="rounded-2xl border border-red-300 bg-red-50 p-5 text-red-800 shadow-sm dark:border-red-800 dark:bg-red-950/30 dark:text-red-200">

                <div class="flex items-start gap-3">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-600 text-lg text-white">
                        !
                    </div>

                    <div>
                        <h2 class="font-black">
                            Error detected for this user
                        </h2>

                        <p class="mt-1 text-sm">
                            Selected date range mein is user ko
                            {{ number_format($totalErrors) }}
                            {{ $totalErrors === 1 ? 'error' : 'errors' }}
                            mile hain. Neeche red highlighted activity rows mein
                            complete details dekhein.
                        </p>
                    </div>

                </div>

            </div>
        @endif

        {{-- Summary --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">

                <p class="text-sm font-semibold text-slate-500">
                    Total Active Usage
                </p>

                <p class="mt-2 text-2xl font-black">
                    {{ $formatDuration($totals['total_seconds'] ?? 0) }}
                </p>

            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">

                <p class="text-sm font-semibold text-slate-500">
                    Page Views
                </p>

                <p class="mt-2 text-2xl font-black">
                    {{ number_format($totals['page_views'] ?? 0) }}
                </p>

            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">

                <p class="text-sm font-semibold text-slate-500">
                    Active Days
                </p>

                <p class="mt-2 text-2xl font-black">
                    {{ number_format($totals['active_days'] ?? 0) }}
                </p>

            </div>

            <div
                class="rounded-2xl border p-5 shadow-sm
                {{ $totalErrors > 0
                    ? 'border-red-300 bg-red-50 dark:border-red-800 dark:bg-red-950/30'
                    : 'border-emerald-300 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-950/30'
                }}"
            >

                <p
                    class="text-sm font-semibold
                    {{ $totalErrors > 0
                        ? 'text-red-600 dark:text-red-300'
                        : 'text-emerald-600 dark:text-emerald-300'
                    }}"
                >
                    Total Errors
                </p>

                <p
                    class="mt-2 text-2xl font-black
                    {{ $totalErrors > 0
                        ? 'text-red-700 dark:text-red-200'
                        : 'text-emerald-700 dark:text-emerald-200'
                    }}"
                >
                    {{ number_format($totalErrors) }}
                </p>

            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">

                <p class="text-sm font-semibold text-slate-500">
                    Last Seen
                </p>

                <p class="mt-2 text-lg font-black">
                    {{ !empty($totals['last_seen'])
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

                    <p class="mt-1 text-sm text-slate-500">
                        Page ke according total usage, visits aur errors.
                    </p>

                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full text-sm">

                        <thead class="bg-slate-50 text-left dark:bg-slate-800">
                            <tr>
                                <th class="p-4">
                                    Page
                                </th>

                                <th class="whitespace-nowrap p-4">
                                    Total Time
                                </th>

                                <th class="whitespace-nowrap p-4">
                                    Visits
                                </th>

                                <th class="whitespace-nowrap p-4">
                                    Average
                                </th>

                                <th class="whitespace-nowrap p-4">
                                    Errors
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">

                            @forelse($pageStats as $page)

                                @php
                                    $pageErrors = (int) ($page->total_errors ?? 0);
                                    $pageHasErrors = $pageErrors > 0;
                                @endphp

                                <tr
                                    class="
                                        transition
                                        {{ $pageHasErrors
                                            ? 'border-l-4 border-red-500 bg-red-50 hover:bg-red-100 dark:bg-red-950/30 dark:hover:bg-red-950/50'
                                            : 'hover:bg-slate-50 dark:hover:bg-slate-800/50'
                                        }}
                                    "
                                >

                                    <td class="p-4">

                                        <strong class="text-slate-950 dark:text-white">
                                            {{ $page->route_name ?: $page->path }}
                                        </strong>

                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ $page->path }}
                                        </div>

                                        @if($pageHasErrors)
                                            <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-black text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                                ⚠ Error detected
                                            </div>
                                        @endif

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

                                    <td class="whitespace-nowrap p-4">

                                        @if($pageHasErrors)
                                            <span class="inline-flex rounded-full bg-red-600 px-3 py-1 text-xs font-black text-white">
                                                {{ number_format($pageErrors) }}
                                                {{ $pageErrors === 1 ? 'Error' : 'Errors' }}
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                                No Error
                                            </span>
                                        @endif

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td
                                        colspan="5"
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

                {{-- Device Usage --}}
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
                                    {{ number_format($device->page_views) }}
                                    page visits
                                </p>

                            </div>

                        @empty

                            <p class="py-5 text-center text-sm text-slate-500">
                                No device data found.
                            </p>

                        @endforelse

                    </div>

                </div>

                {{-- Browser Usage --}}
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
                                    {{ number_format($browser->page_views) }}
                                    page visits
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
                    Har page visit ka date, duration, device, IP aur error details.
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
                                Page / Error Details
                            </th>

                            <th class="whitespace-nowrap p-4">
                                Duration
                            </th>

                            <th class="whitespace-nowrap p-4">
                                Errors
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

                            @php
                                $activityErrorCount = (int) ($activity->error_count ?? 0);
                                $activityHasErrors = $activityErrorCount > 0;

                                $rawActivityErrors = $activity->errors;

                                if (is_string($rawActivityErrors)) {
                                    $rawActivityErrors = json_decode(
                                        $rawActivityErrors,
                                        true
                                    );
                                }

                                $activityErrors = is_array($rawActivityErrors)
                                    ? array_reverse($rawActivityErrors)
                                    : [];

                                $activityHasErrors =
                                    $activityErrorCount > 0
                                    || count($activityErrors) > 0;

                                $activityErrors = array_reverse($activityErrors);
                            @endphp

                            <tr
                                class="
                                    align-top transition
                                    {{ $activityHasErrors
                                        ? 'border-l-4 border-red-500 bg-red-50 hover:bg-red-100 dark:bg-red-950/30 dark:hover:bg-red-950/50'
                                        : 'hover:bg-slate-50 dark:hover:bg-slate-800/50'
                                    }}
                                "
                            >

                                <td class="whitespace-nowrap p-4">

                                    {{ optional($activity->started_at)
                                        ->format('d M Y, h:i A')
                                    }}

                                    @if($activity->last_error_at)
                                        <div class="mt-2 text-xs font-semibold text-red-600 dark:text-red-300">
                                            Last error:
                                            {{ $activity->last_error_at->diffForHumans() }}
                                        </div>
                                    @endif

                                </td>

                                <td class="min-w-[360px] p-4">

                                    <strong class="text-slate-950 dark:text-white">
                                        {{ $activity->page_title
                                            ?: ($activity->route_name ?: $activity->path)
                                        }}
                                    </strong>

                                    <div class="mt-1 max-w-xl break-all text-xs text-slate-500">
                                        {{ $activity->path }}
                                    </div>

                                    @if($activity->route_name)
                                        <div class="mt-1 text-xs text-blue-500">
                                            {{ $activity->route_name }}
                                        </div>
                                    @endif

                                    @if($activityHasErrors)

                                        <div class="mt-4 space-y-3">

                                            <div class="inline-flex items-center gap-1 rounded-full bg-red-600 px-3 py-1 text-xs font-black text-white">
                                                ⚠ Error detected on this page
                                            </div>

                                            @foreach($activityErrors as $errorIndex => $error)

                                                @php
                                                    $errorType = $error['error_type']
                                                        ?? 'unknown_error';

                                                    $errorMessage = $error['message']
                                                        ?? 'Unknown error';

                                                    $errorCount = (int) (
                                                        $error['count']
                                                        ?? 1
                                                    );

                                                    $httpStatus = $error['http_status']
                                                        ?? null;

                                                    $sourceFile = $error['source_file']
                                                        ?? null;

                                                    $sourceLine = $error['source_line']
                                                        ?? null;

                                                    $sourceColumn = $error['source_column']
                                                        ?? null;

                                                    $stackTrace = $error['stack_trace']
                                                        ?? null;

                                                    $requestUrl = $error['request_url']
                                                        ?? null;

                                                    $requestMethod = $error['request_method']
                                                        ?? null;

                                                    $firstSeenAt = $error['first_seen_at']
                                                        ?? null;

                                                    $lastSeenAt = $error['last_seen_at']
                                                        ?? null;
                                                @endphp

                                                <details
                                                    class="overflow-hidden rounded-xl border border-red-200 bg-white dark:border-red-900 dark:bg-slate-950"
                                                    @if($errorIndex === 0)
                                                        open
                                                    @endif
                                                >

                                                    <summary class="cursor-pointer list-none p-4">

                                                        <div class="flex flex-wrap items-start justify-between gap-3">

                                                            <div class="min-w-0">

                                                                <div class="flex flex-wrap items-center gap-2">

                                                                    <span class="rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-black text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                                                        {{ strtoupper(
                                                                            str_replace(
                                                                                '_',
                                                                                ' ',
                                                                                $errorType
                                                                            )
                                                                        ) }}
                                                                    </span>

                                                                    @if($httpStatus)
                                                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-black text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                                                            HTTP {{ $httpStatus }}
                                                                        </span>
                                                                    @endif

                                                                    @if($errorCount > 1)
                                                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                                            Repeated {{ $errorCount }} times
                                                                        </span>
                                                                    @endif

                                                                </div>

                                                                <p class="mt-3 break-words font-bold text-red-700 dark:text-red-300">
                                                                    {{ $errorMessage }}
                                                                </p>

                                                            </div>

                                                            <span class="shrink-0 text-xs font-bold text-blue-600">
                                                                View details
                                                            </span>

                                                        </div>

                                                    </summary>

                                                    <div class="border-t border-red-100 p-4 dark:border-red-900">

                                                        <div class="grid gap-3 text-xs sm:grid-cols-2">

                                                            <div>
                                                                <span class="font-bold text-slate-500">
                                                                    Error Type:
                                                                </span>

                                                                <div class="mt-1 break-words">
                                                                    {{ $errorType }}
                                                                </div>
                                                            </div>

                                                            <div>
                                                                <span class="font-bold text-slate-500">
                                                                    HTTP Status:
                                                                </span>

                                                                <div class="mt-1">
                                                                    {{ $httpStatus ?: '-' }}
                                                                </div>
                                                            </div>

                                                            <div>
                                                                <span class="font-bold text-slate-500">
                                                                    Request Method:
                                                                </span>

                                                                <div class="mt-1">
                                                                    {{ $requestMethod ?: '-' }}
                                                                </div>
                                                            </div>

                                                            <div>
                                                                <span class="font-bold text-slate-500">
                                                                    Occurrence:
                                                                </span>

                                                                <div class="mt-1">
                                                                    {{ number_format($errorCount) }}
                                                                </div>
                                                            </div>

                                                            <div>
                                                                <span class="font-bold text-slate-500">
                                                                    First Seen:
                                                                </span>

                                                                <div class="mt-1">
                                                                    {{ $firstSeenAt
                                                                        ? \Carbon\Carbon::parse($firstSeenAt)->format('d M Y, h:i:s A')
                                                                        : '-'
                                                                    }}
                                                                </div>
                                                            </div>

                                                            <div>
                                                                <span class="font-bold text-slate-500">
                                                                    Last Seen:
                                                                </span>

                                                                <div class="mt-1">
                                                                    {{ $lastSeenAt
                                                                        ? \Carbon\Carbon::parse($lastSeenAt)->format('d M Y, h:i:s A')
                                                                        : '-'
                                                                    }}
                                                                </div>
                                                            </div>

                                                        </div>

                                                        @if($requestUrl)
                                                            <div class="mt-4">

                                                                <p class="text-xs font-bold text-slate-500">
                                                                    Request URL
                                                                </p>

                                                                <div class="mt-1 break-all rounded-lg bg-slate-100 p-3 font-mono text-xs dark:bg-slate-900">
                                                                    {{ $requestUrl }}
                                                                </div>

                                                            </div>
                                                        @endif

                                                        @if($sourceFile)
                                                            <div class="mt-4">

                                                                <p class="text-xs font-bold text-slate-500">
                                                                    Source
                                                                </p>

                                                                <div class="mt-1 break-all rounded-lg bg-slate-950 p-3 font-mono text-xs text-slate-200">

                                                                    {{ $sourceFile }}

                                                                    @if($sourceLine)
                                                                        :{{ $sourceLine }}
                                                                    @endif

                                                                    @if($sourceColumn)
                                                                        :{{ $sourceColumn }}
                                                                    @endif

                                                                </div>

                                                            </div>
                                                        @endif

                                                        @if($stackTrace)
                                                            <div class="mt-4">

                                                                <p class="text-xs font-bold text-slate-500">
                                                                    Stack Trace
                                                                </p>

                                                                <pre class="mt-1 max-h-80 overflow-auto whitespace-pre-wrap break-words rounded-lg bg-slate-950 p-3 font-mono text-xs text-slate-200">{{ $stackTrace }}</pre>

                                                            </div>
                                                        @endif

                                                    </div>

                                                </details>

                                            @endforeach

                                        </div>

                                    @endif

                                </td>

                                <td class="whitespace-nowrap p-4 font-bold">
                                    {{ $formatDuration($activity->duration_seconds) }}
                                </td>

                                <td class="whitespace-nowrap p-4">

                                    @if($activityHasErrors)
                                        <span class="inline-flex rounded-full bg-red-600 px-3 py-1 text-xs font-black text-white">
                                            {{ number_format($activityErrorCount) }}
                                            {{ $activityErrorCount === 1 ? 'Error' : 'Errors' }}
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                            No Error
                                        </span>
                                    @endif

                                </td>

                                <td class="whitespace-nowrap p-4">

                                    <span class="capitalize">
                                        {{ $activity->device_type ?: 'Unknown' }}
                                    </span>

                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $activity->browser ?: 'Unknown Browser' }}
                                        /
                                        {{ $activity->platform ?: 'Unknown Platform' }}
                                    </div>

                                </td>

                                <td class="whitespace-nowrap p-4">
                                    {{ $activity->ip_address ?: '-' }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="6"
                                    class="p-10 text-center text-slate-500"
                                >
                                    Is user ki koi activity nahi mili.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if($activities->hasPages())
                <div class="border-t border-slate-200 p-4 dark:border-slate-700">
                    {{ $activities->links() }}
                </div>
            @endif

        </div>

    </div>

</x-layouts.app>