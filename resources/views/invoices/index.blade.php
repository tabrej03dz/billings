<x-layouts.app :title="__('Invoices')">
    <div class="flex items-center justify-between mb-3">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100">Invoices</h1>
        <a href="{{ route('invoices.create') }}" class="px-3 py-2 rounded bg-blue-600 text-white">+ New</a>
    </div>

    @if(session('success'))
        <div class="p-2 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded">
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

    <div class="mt-4">{{ $invoices->links() }}</div>
</x-layouts.app>
