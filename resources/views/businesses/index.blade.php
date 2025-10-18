<x-layouts.app :title="__('Businesses')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Businesses</h1>
            @can('create business')
                <a href="{{ route('businesses.create') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    + New Business
                </a>
            @endcan
        </div>

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-medium tracking-wider">
                <tr>
                    <th class="px-6 py-3">Logo</th>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Mobile</th>
                    <th class="px-6 py-3">GSTIN</th>
                    <th class="px-6 py-3">Address</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                @forelse ($businesses as $business)
                    <tr>
                        <td class="px-6 py-4">
                            @if($business->logo)
                                <img src="{{ asset('storage/' . $business->logo) }}" alt="Logo" class="w-10 h-10 rounded-full object-cover">
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                            {{ $business->name }}
                            <div class="text-xs text-gray-500">/{{ $business->slug }}</div>
                        </td>
                        <td class="px-6 py-4">{{ $business->email }}</td>
                        <td class="px-6 py-4">{{ $business->mobile }}</td>
                        <td class="px-6 py-4">{{ $business->gstin ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $business->address ?? 'N/A' }}</td>
                        <td class="px-6 py-4 space-x-2">
                            @can('edit business')
                                <a href="{{ route('businesses.edit', $business->id) }}" class="text-yellow-600 hover:underline">Edit</a>
                            @endcan

                            @can('delete business')
                                <form action="{{ route('businesses.delete', $business->id) }}" method="POST" class="inline-block"
                                      onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No businesses found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $businesses->withQueryString()->links() }}
        </div>
    </div>
</x-layouts.app>
