<x-layouts.app :title="__('Plans')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3 bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Plans</h1>

            <div class="flex items-center gap-2 flex-wrap">
                <form method="GET" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search name / slug / description..."
                           class="border rounded px-3 py-2 text-sm w-64 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white" />
                    <button class="px-3 py-2 text-sm rounded bg-gray-500 hover:bg-gray-600 text-white">Search</button>

                    @if(request('search'))
                        <a href="{{ route('plans.index') }}" class="text-sm text-gray-700 dark:text-gray-200 hover:underline">Clear</a>
                    @endif
                </form>

                <a href="{{ route('plans.create') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                    + New Plan
                </a>
            </div>
        </div>

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Slug</th>
                        <th class="px-6 py-3">Price</th>
                        <th class="px-6 py-3">Duration</th>
                        <th class="px-6 py-3">Permissions</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                    @forelse($plans as $plan)
                        <tr>
                            <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $plan->name }}
                            </td>
                            <td class="px-6 py-3">{{ $plan->slug }}</td>
                            <td class="px-6 py-3">₹{{ number_format($plan->price, 2) }}</td>
                            <td class="px-6 py-3">{{ $plan->duration_days }} days</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                    {{ $plan->permissions->count() }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                @if($plan->status)
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-red-100 text-red-700">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3 space-x-2">
                                <a href="{{ route('plans.show', $plan->id) }}" class="bg-blue-500 text-white p-2 hover:bg-blue-600 rounded m-1 inline-block">
                                    View
                                </a>

                                <a href="{{ route('plans.edit', $plan->id) }}" class="bg-yellow-500 text-white p-2 hover:bg-yellow-600 rounded m-1 inline-block">
                                    Edit
                                </a>

                                <form action="{{ route('plans.toggleStatus', $plan->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="bg-indigo-500 text-white p-2 hover:bg-indigo-600 rounded m-1">
                                        {{ $plan->status ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>

                                <form action="{{ route('plans.destroy', $plan->id) }}" method="POST" class="inline-block"
                                      onsubmit="return confirm('Delete this plan?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 text-white p-2 hover:bg-red-600 rounded m-1">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No plans found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $plans->links() }}
        </div>
    </div>
</x-layouts.app>