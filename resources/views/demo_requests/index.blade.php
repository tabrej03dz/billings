<x-layouts.app :title="__('Demo Requests')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3 mb-1 bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Demo Requests</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage demo request and callback enquiries.</p>
            </div>

            <a href="{{ route('demo-requests.create') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                + New Request
            </a>
        </div>

        <div class="bg-slate-200 dark:bg-transparent">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-neutral-900">

                <div class="md:col-span-2">
                    <label class="text-xs text-gray-600 dark:text-gray-400">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                           placeholder="Name / Mobile / City / Business">
                </div>

                <div>
                    <label class="text-xs text-gray-600 dark:text-gray-400">Status</label>
                    <select name="status"
                            class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
                        <option value="">All</option>
                        <option value="pending" @selected(request('status') == 'pending')>Pending</option>
                        <option value="contacted" @selected(request('status') == 'contacted')>Contacted</option>
                        <option value="converted" @selected(request('status') == 'converted')>Converted</option>
                        <option value="rejected" @selected(request('status') == 'rejected')>Rejected</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700">
                        Apply
                    </button>

                    <a href="{{ route('demo-requests.index') }}"
                       class="w-full text-center px-4 py-2 text-sm font-medium text-gray-800 dark:text-white bg-gray-100 dark:bg-neutral-800 rounded-lg border">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Mobile</th>
                        <th class="px-6 py-3">City</th>
                        <th class="px-6 py-3">Business</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Updated By</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                    @forelse ($demoRequests as $r)
                        <tr>
                            <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $r->name }}
                            </td>

                            <td class="px-6 py-3">
                                <a href="tel:{{ $r->mobile }}" class="text-blue-600 hover:underline">
                                    {{ $r->mobile }}
                                </a>
                            </td>

                            <td class="px-6 py-3">{{ $r->city ?? '-' }}</td>
                            <td class="px-6 py-3">{{ $r->business_name ?? '-' }}</td>

                            <td class="px-6 py-3">
                                @php
                                    $statusClass = match($r->status) {
                                        'contacted' => 'bg-blue-100 text-blue-700 border-blue-200',
                                        'converted' => 'bg-green-100 text-green-700 border-green-200',
                                        'rejected' => 'bg-red-100 text-red-700 border-red-200',
                                        default => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                    };
                                @endphp

                                <span class="text-xs px-2 py-1 rounded-full border {{ $statusClass }}">
                                    {{ ucfirst($r->status) }}
                                </span>
                            </td>

                            <td class="px-6 py-3">
                                {{ optional($r->updatedBy)->name ?? '-' }}
                            </td>

                            <td class="px-6 py-3">
                                {{ $r->created_at ? $r->created_at->format('d M, Y') : '-' }}
                            </td>

                            <td class="px-6 py-3 space-x-3 whitespace-nowrap">
                                <a href="{{ route('demo-requests.show', $r->id) }}" class="text-blue-600 hover:underline">View</a>
                                <a href="{{ route('demo-requests.edit', $r->id) }}" class="text-yellow-600 hover:underline">Edit</a>

                                <form action="{{ route('demo-requests.destroy', $r->id) }}" method="POST" class="inline-block"
                                      onsubmit="return confirm('Delete this demo request?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">No demo requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $demoRequests->withQueryString()->links() }}
        </div>
    </div>
</x-layouts.app>