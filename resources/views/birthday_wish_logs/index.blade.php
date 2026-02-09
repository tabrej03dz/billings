<x-layouts.app :title="__('Birthday Wish Logs')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3 mb-1">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Birthday Wish Logs</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Wish attempts ka record (success/failed/pending).</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('birthday-wish-logs.index') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-800 dark:text-white bg-gray-100 dark:bg-neutral-800 rounded-lg hover:bg-gray-200 dark:hover:bg-neutral-700 border border-gray-200 dark:border-gray-700">
                    Refresh
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900">
            <div class="md:col-span-2">
                <label class="text-xs text-gray-600 dark:text-gray-400">Search (Phone)</label>
                <input type="text" name="phone" value="{{ request('phone') }}"
                       class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="e.g. 9876...">
            </div>

            <div>
                <label class="text-xs text-gray-600 dark:text-gray-400">Status</label>
                <select name="status"
                        class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="success" @selected(request('status') === 'success')>Success</option>
                    <option value="failed"  @selected(request('status') === 'failed')>Failed</option>
                </select>
            </div>

            <div>
                <label class="text-xs text-gray-600 dark:text-gray-400">Business ID</label>
                <input type="number" name="business_id" value="{{ request('business_id') }}"
                       class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="e.g. 1">
            </div>

            <div>
                <label class="text-xs text-gray-600 dark:text-gray-400">Wish Date From</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="text-xs text-gray-600 dark:text-gray-400">Wish Date To</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="text-xs text-gray-600 dark:text-gray-400">Record ID</label>
                <input type="number" name="birthday_record_id" value="{{ request('birthday_record_id') }}"
                       class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="e.g. 10">
            </div>

            <div>
                <label class="text-xs text-gray-600 dark:text-gray-400">Wish Year</label>
                <input type="number" name="wish_year" value="{{ request('wish_year') }}"
                       class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="e.g. {{ now()->year }}">
            </div>

            <div class="md:col-span-2 flex items-end gap-2">
                <button type="submit"
                        class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Apply
                </button>
                <a href="{{ route('birthday-wish-logs.index') }}"
                   class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-gray-800 dark:text-white bg-gray-100 dark:bg-neutral-800 rounded-lg hover:bg-gray-200 dark:hover:bg-neutral-700 border border-gray-200 dark:border-gray-700">
                    Reset
                </a>
            </div>
        </form>

        {{-- Table --}}
        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-medium tracking-wider">
                <tr>
                    <th class="px-6 py-3">Wish Date</th>
                    <th class="px-6 py-3">Year</th>
                    <th class="px-6 py-3">Phone</th>
{{--                    <th class="px-6 py-3">Business</th>--}}
{{--                    <th class="px-6 py-3">Record ID</th>--}}
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                @forelse ($logs as $log)
                    <tr>
                        <td class="px-6 py-3">
                            {{ optional($log->wish_date)->format('d M, Y') ?? '-' }}
                        </td>

                        <td class="px-6 py-3">
                            {{ $log->wish_year ?? '-' }}
                        </td>

                        <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">
                            {{ $log->phone }}
                        </td>

{{--                        <td class="px-6 py-3">--}}
{{--                            {{ $log->business_id ?? '-' }}--}}
{{--                        </td>--}}

{{--                        <td class="px-6 py-3">--}}
{{--                            {{ $log->birthday_record_id }}--}}
{{--                        </td>--}}

                        <td class="px-6 py-3">
                            @php
                                $badge = match($log->status) {
                                  'success' => 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800',
                                  'failed'  => 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800',
                                  default   => 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-300 dark:border-yellow-800',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs border {{ $badge }}">
                                {{ strtoupper($log->status) }}
                            </span>
                        </td>

                        <td class="px-6 py-3 space-x-3">
                            <a href="{{ route('birthday-wish-logs.show', $log->id) }}" class="text-blue-600 hover:underline">View</a>
{{--                            <a href="{{ route('birthday-wish-logs.edit', $log->id) }}" class="text-yellow-600 hover:underline">Edit</a>--}}

                            <form action="{{ route('birthday-wish-logs.destroy', $log->id) }}" method="POST" class="inline-block"
                                  onsubmit="return confirm('Delete this log?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                            <form action="{{ route('birthday-wish-logs.resend', $log->id) }}" method="POST" class="inline-block"
                                onsubmit="return confirm('Resend birthday wish to {{ $log->phone }}?');">
                                @csrf
                                <button type="submit" class="text-indigo-600 hover:underline">
                                    Resend
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No logs found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>
</x-layouts.app>
