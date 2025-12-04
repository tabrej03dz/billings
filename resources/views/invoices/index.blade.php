<x-layouts.app :title="__('Invoices')">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100">Invoices</h1>
        <a href="{{ route('invoices.create') }}"
           class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
            + New
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET"
          action="{{ route('invoices.index') }}"
          class="mb-4 grid gap-3 md:grid-cols-5 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg p-3">

        {{-- Search --}}
        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                Search (Invoice / Client)
            </label>
            <input type="text" name="search"
                   value="{{ request('search') }}"
                   class="block w-full rounded-md border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 text-sm focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Invoice no, client name, email...">
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
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partially Paid</option>
                <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
            </select>
        </div>

        {{-- Buttons --}}
        <div class="md:col-span-5 flex items-center justify-end gap-2 pt-1">
            <a href="{{ route('invoices.index') }}"
               class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 dark:border-neutral-700 text-xs font-medium text-gray-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-800">
                Reset
            </a>
            <button type="submit"
                    class="inline-flex items-center px-4 py-1.5 rounded-md bg-blue-600 text-white text-xs font-medium hover:bg-blue-700">
                Apply Filters
            </button>
        </div>
    </form>
    <a href="{{ route('invoices.export', request()->query()) }}"
       class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 text-sm font-medium">
        📄 Download Full Report
    </a>


@if(session('success'))
        <div class="p-2 mb-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-auto border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
        <table class="min-w-full text-sm border-separate border-spacing-0">
            <thead class="bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200">
            <tr class="[&>th]:px-4 [&>th]:py-2 [&>th]:font-medium text-left">
                <th>#</th>
                <th>Date</th>
                <th>Client</th>
                <th>Total</th>
                <th>Received</th>
                <th>Balance</th>
                <th></th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700 text-gray-900 dark:text-neutral-100">
            @forelse($invoices as $inv)
                <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800/60">
                    <td class="px-4 py-2">{{ $inv->invoice_number }}</td>
                    <td class="px-4 py-2">{{ \Illuminate\Support\Carbon::parse($inv->invoice_date)->format('d M Y') }}</td>
                    <td class="px-4 py-2">{{ $inv->client->name }}</td>
                    <td class="px-4 py-2">₹ {{ number_format($inv->total,2) }}</td>
                    <td class="px-4 py-2">₹ {{ number_format($inv->received_amount,2) }}</td>
                    <td class="px-4 py-2">₹ {{ number_format($inv->balance,2) }}</td>
                    <td class="px-4 py-2 space-x-3">
                        <a href="{{ route('invoices.show',$inv->id) }}" class="text-gray-700 dark:text-neutral-300 hover:underline">View</a>
                        <a href="{{ route('invoices.download',$inv->id) }}" class="text-emerald-600 hover:underline">Download</a>
                        <a href="{{ route('invoices.edit',$inv->id) }}" class="text-blue-600 hover:underline">Edit</a>
                        <a href="{{ route('invoices.send',$inv->id) }}" class="text-blue-600 hover:underline">Send to Whtsp</a>
                        <form action="{{ route('invoices.destroy',$inv->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-3 text-center text-gray-500 dark:text-neutral-400">
                        No invoices.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $invoices->links() }}
    </div>
</x-layouts.app>
