<x-layouts.app :title="__('Additional Charges')">
    <div class="flex items-center justify-between mb-3">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100">Additional Charges</h1>

        <div class="flex items-center gap-2">
            <form method="GET" class="hidden sm:block">
                <input type="text" name="search" value="{{ $search ?? '' }}"
                       placeholder="Search name or amount"
                       class="px-3 py-2 rounded border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                <button class="px-3 py-2 rounded bg-gray-800 text-white text-sm">Search</button>
            </form>

            <a href="{{ route('additional-charges.create') }}"
               class="px-3 py-2 rounded bg-blue-600 text-white">+ New</a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-2 mb-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-auto border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
        <table class="min-w-full text-sm border-separate border-spacing-0">
            <thead class="bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200">
            <tr class="[&>th]:px-4 [&>th]:py-2 [&>th]:font-medium text-left">
                <th style="width: 90px;">#ID</th>
                <th>Name</th>
                <th style="width: 160px;">Amount</th>
                <th style="width: 170px;">Created</th>
                <th style="width: 160px;"></th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700 text-gray-900 dark:text-neutral-100">
            @forelse($charges as $charge)
                <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800/60">
                    <td class="px-4 py-2">#{{ $charge->id }}</td>
                    <td class="px-4 py-2">{{ $charge->name }}</td>
                    <td class="px-4 py-2">₹ {{ number_format($charge->amount, 2) }}</td>
                    <td class="px-4 py-2">{{ $charge->created_at->format('d M Y, h:i A') }}</td>
                    <td class="px-4 py-2 space-x-3">
                        <a href="{{ route('additional-charges.edit', $charge->id) }}"
                           class="text-blue-600 hover:underline">Edit</a>

                        <form action="{{ route('additional-charges.destroy', $charge->id) }}"
                              method="POST" class="inline"
                              onsubmit="return confirm('Delete this charge?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-3 text-center text-gray-500 dark:text-neutral-400">
                        No additional charges found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex items-center justify-between">
        <div class="text-xs text-gray-500 dark:text-neutral-400">
            Showing {{ $charges->firstItem() ?? 0 }}–{{ $charges->lastItem() ?? 0 }} of {{ $charges->total() }}
        </div>
        {{ $charges->links() }}
    </div>
</x-layouts.app>
