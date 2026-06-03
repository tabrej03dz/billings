<x-layouts.app :title="__('Businesses')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between mb-4 bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Businesses</h1>

            <form method="GET" action="{{ route('businesses.index') }}"
                class="grid grid-cols-1 md:grid-cols-4 gap-3 bg-white dark:bg-neutral-900 p-4 rounded-xl border border-gray-200 dark:border-gray-700">

                <input type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search name, email, mobile, GSTIN..."
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-neutral-800 dark:text-white">

                <select name="status"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-neutral-800 dark:text-white">
                    <option value="">All Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>

                <select name="per_page"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-neutral-800 dark:text-white">
                    @foreach([10, 15, 25, 50, 100] as $limit)
                        <option value="{{ $limit }}" @selected(request('per_page', 15) == $limit)>
                            {{ $limit }} Per Page
                        </option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Filter
                    </button>

                    <a href="{{ route('businesses.index') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                        Reset
                    </a>
                </div>
            </form>

            @can('create business')
                <a href="{{ route('businesses.create') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-500 rounded-lg hover:bg-green-700">
                    + New Business
                </a>
            @endcan
        </div>

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium tracking-wider">
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
                                <a href="{{ route('businesses.edit', $business->id) }}" class=" bg-yellow-500 text-white p-2 hover:underline m-3">Edit</a>
                            @endcan

                            @can('delete business')
                                <form action="{{ route('businesses.delete', $business->id) }}" method="POST" class="inline-block"
                                      onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-600 p-2 m-3 text-white hover:underline">Delete</button>
                                </form>

                            @endcan

                            @can('edit business')
                                <a href="{{ route('user-plans.index1', $business->id) }}" class=" bg-yellow-500 text-white p-2 hover:underline m-3">Edit</a>
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
