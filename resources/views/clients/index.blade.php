<x-layouts.app :title="__('Clients')">
    <div class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Clients</h1>
            <a href="{{ route('clients.create') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                + Add Client
            </a>
        </div>

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-semibold text-gray-700 dark:text-white">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Mobile</th>
                    <th class="px-6 py-3">GSTIN</th>
                    <th class="px-6 py-3">State</th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse ($clients as $client)
                    <tr>
                        <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $client->name }}</td>
                        <td class="px-6 py-4">{{ $client->mobile }}</td>
                        <td class="px-6 py-4">{{ $client->gstin }}</td>
                        <td class="px-6 py-4">{{ $client->state }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No clients found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
