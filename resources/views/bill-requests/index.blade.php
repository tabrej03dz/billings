<x-layouts.app :title="__('Bill Requests')">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4 bg-[#BFE0E0] dark:bg-[#354A54] p-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100">
                Bill Requests
            </h1>
            <div class="text-xs text-gray-500 dark:text-neutral-400">
                Manage all billing requests from one place
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-[#46837d] text-white text-sm font-medium hover:bg-[#35655f]">
                Back to Dashboard
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET"
          action="{{ route('bill-requests.index') }}"
          class="mb-4 grid gap-3 md:grid-cols-5 bg-gray-100 dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg p-3">

        {{-- Search --}}
        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                Search
            </label>
            <input type="text" name="search"
                   value="{{ request('search') }}"
                   class="block w-full rounded-md border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 text-sm focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Customer, request id, phone, business name...">
        </div>

        {{-- From date --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                From date
            </label>
            <input type="date" name="from_date"
                   value="{{ request('from_date') }}"
                   class="block w-full rounded-md border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 text-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        {{-- To date --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                To date
            </label>
            <input type="date" name="to_date"
                   value="{{ request('to_date') }}"
                   class="block w-full rounded-md border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 text-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        {{-- Status --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                Status
            </label>
            <select name="status"
                    class="block w-full rounded-md border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">All</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>

        {{-- Buttons --}}
        <div class="md:col-span-5 flex items-center justify-between gap-2 pt-1">
            <a href="{{ route('bill-requests.index') }}"
               class="inline-flex items-center px-3 py-1.5 rounded-md border border-red-300 dark:border-red-700 text-xs font-medium text-red-700 dark:text-red-600 hover:bg-gray-50 dark:hover:bg-neutral-800">
                Reset
            </a>

            <button type="submit"
                    class="inline-flex items-center px-4 py-2 rounded-md bg-amber-600 text-white text-sm font-medium hover:bg-amber-700">
                Apply Filters
            </button>
        </div>
    </form>

    @if(session('success'))
        <div class="p-2 mb-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-2 mb-3 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-hidden border border-gray-200 dark:border-neutral-800 rounded-2xl bg-[#BFE0E0] dark:bg-[#354A54] p-6">
        <div class="overflow-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-neutral-800/60 text-xs uppercase tracking-wide text-gray-600 dark:text-neutral-300">
                <tr>
                    <th class="px-4 py-3 text-left">Request ID</th>
                    <th class="px-4 py-3 text-left">Customer</th>
                    <th class="px-4 py-3 text-left">Business</th>
                    <th class="px-4 py-3 text-left">Package</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Requested At</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                @forelse($billRequests as $request)
                    <tr class="hover:bg-gray-50/60 dark:hover:bg-neutral-800/40">
                        {{-- Request ID --}}
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-900 dark:text-white">
                                {{ $request->source_request_id ?? $request->id }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                {{ $request->source_software ?? 'System' }}
                            </div>
                        </td>

                        {{-- Customer --}}
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ $request->customer_name ?? '-' }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                {{ $request->customer_phone ?? '-' }}
                            </div>
                        </td>

                        {{-- Business --}}
                        <td class="px-4 py-3 text-gray-700 dark:text-neutral-200">
                            <div>{{ $request->business_name ?? '-' }}</div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                {{ $request->city ?? '' }}{{ $request->state ? ', '.$request->state : '' }}
                            </div>
                        </td>

                        {{-- Package --}}
                        <td class="px-4 py-3 text-gray-700 dark:text-neutral-200">
                            <div>{{ $request->package_name ?? '-' }}</div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                {{ $request->package_duration ?? '-' }}
                            </div>
                        </td>

                        {{-- Amount --}}
                        <td class="px-4 py-3 text-right font-medium tabular-nums">
                            ₹ {{ number_format((float)($request->payment_amount ?? $request->selling_price ?? 0), 2) }}
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-3">
                            @php
                                $status = strtolower($request->status ?? 'pending');

                                $statusClass = match($status) {
                                    'approved'  => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-200 dark:border-emerald-800',
                                    'rejected'  => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-200 dark:border-rose-800',
                                    'completed' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-200 dark:border-blue-800',
                                    default     => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-200 dark:border-amber-800',
                                };
                            @endphp

                            <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-semibold {{ $statusClass }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>

                        {{-- Requested At --}}
                        <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-neutral-200">
                            {{ optional($request->requested_at ?? $request->created_at)->format('d M Y, h:i A') }}
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-3 text-right">
                            <div class="relative inline-block" x-data="{open:false}">
                                <button @click="open=!open"
                                        class="w-9 h-9 rounded-lg border border-gray-200 dark:border-neutral-700 hover:bg-gray-50 dark:hover:bg-neutral-800">
                                    ⋮
                                </button>

                                <div x-show="open" @click.outside="open=false" x-transition
                                     class="absolute right-0 mt-2 w-52 rounded-xl border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-lg z-50">

                                    <a href="{{ route('bill-requests.show', $request->id) }}"
                                       class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-neutral-800">
                                        View
                                    </a>

                                    @if($request->status !== 'processed')
                                        <form method="POST" action="{{ route('bill-requests.create-invoice', $request->id) }}"
                                            onsubmit="return confirm('Is bill request se invoice create karna hai?')">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 dark:hover:bg-neutral-800">
                                                Create Invoice
                                            </button>
                                        </form>
                                    @endif

                                    @if(Route::has('bill-requests.approve'))
                                        <form method="POST" action="{{ route('bill-requests.approve', $request->id) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full text-left px-4 py-2 text-sm text-emerald-700 dark:text-emerald-300 hover:bg-gray-50 dark:hover:bg-neutral-800">
                                                Approve
                                            </button>
                                        </form>
                                    @endif

                                    @if(Route::has('bill-requests.reject'))
                                        <form method="POST" action="{{ route('bill-requests.reject', $request->id) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full text-left px-4 py-2 text-sm text-rose-700 dark:text-rose-300 hover:bg-gray-50 dark:hover:bg-neutral-800">
                                                Reject
                                            </button>
                                        </form>
                                    @endif

                                    @if(Route::has('bill-requests.destroy'))
                                        <div class="h-px bg-gray-100 dark:bg-neutral-800"></div>

                                        <form method="POST" action="{{ route('bill-requests.destroy', $request->id) }}"
                                              onsubmit="return confirm('Delete this bill request?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 dark:hover:bg-neutral-800">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-neutral-400">
                            No bill requests found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $billRequests->withQueryString()->links() }}
    </div>
</x-layouts.app>