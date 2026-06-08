<x-layouts.app :title="__('Purchases')">
    <div class="flex items-center justify-between mb-3  bg-[#BFE0E0] dark:bg-[#354A54] p-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100">Purchases</h1>
        <a href="{{ route('purchases.create') }}" class="px-3 py-2 rounded bg-green-600 text-white">+ New Purchase</a>
    </div>

    @if(session('success'))
        <div class="p-2 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded mb-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-auto border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
        <table class="min-w-full text-sm border-separate border-spacing-0">
            <thead class="bg-[#BFE0E0] dark:bg-[#354A54]  text-gray-700 dark:text-neutral-200">
            <tr class="[&>th]:px-4 [&>th]:py-2 [&>th]:font-medium text-left">
                <th>#</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Total Amount</th>
                <th></th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700 text-gray-900 dark:text-neutral-100">
            @forelse($purchases as $p)
                <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800/60">
                    <td class="px-4 py-2">{{ $p->invoice_no ?? $p->id }}</td>
                    <td class="px-4 py-2">
                        {{ optional($p->invoice_date)->format('d M Y') }}
                    </td>
                    <td class="px-4 py-2">{{ $p->supplier->name ?? '-' }}</td>
                    <td class="px-4 py-2">₹ {{ number_format($p->total_amount, 2) }}</td>
                    <td class="px-4 py-2 space-x-3">
                        {{-- Agar show page banaya hoga to --}}
                        {{-- <a href="{{ route('purchases.show', $p->id) }}" class="text-gray-700 dark:text-neutral-300 hover:underline">View</a> --}}
                        <a href="{{ route('purchases.edit', $p->id) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('purchases.destroy', $p->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this purchase?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Delete</button>
                        </form>
                        <a href="{{ route('purchases.show', $p->id) }}"
                            class="text-blue-600 hover:underline">
                                View
                            </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-3 text-center text-gray-500 dark:text-neutral-400">
                        No purchases found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $purchases->links() }}
    </div>
</x-layouts.app>
