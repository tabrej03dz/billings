<x-layouts.app :title="__('Business User Plans')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3 mb-4 bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                    {{ $business->name ?? $business->business_name ?? 'Business' }} Plans
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    Showing only this business plans
                </p>
            </div>

            <a href="{{ route('user-plans.create', ['business_id' => $businessId]) }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                + New User Plan
            </a>
        </div>

        <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
            <form method="GET" action="{{ route('user-plans.index1', $businessId) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="Search user / email / plan..."
                       class="w-full px-3 py-2 text-sm border rounded-lg dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">

                <button type="submit"
                        class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Search
                </button>

                @if(request('q'))
                    <a href="{{ route('user-plans.index1', $businessId) }}"
                       class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-gray-500 rounded-lg hover:bg-gray-600">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium tracking-wider">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Business</th>
                        <th class="px-6 py-3">User</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Plan</th>
                        <th class="px-6 py-3">Price</th>
                        <th class="px-6 py-3">Start Date</th>
                        <th class="px-6 py-3">Expiry Date</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                    @forelse ($userPlans as $userPlan)
                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                            <td class="px-6 py-4">
                                {{ $userPlans->firstItem() + $loop->index }}
                            </td>

                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $userPlan->business->name ?? $userPlan->business->business_name ?? 'N/A' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $userPlan->user->name ?? 'N/A' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $userPlan->user->email ?? 'N/A' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $userPlan->plan->name ?? 'N/A' }}
                            </td>

                            <td class="px-6 py-4">
                                ₹{{ number_format($userPlan->plan->price ?? 0, 2) }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $userPlan->start_date ? \Carbon\Carbon::parse($userPlan->start_date)->format('d M Y') : 'N/A' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $userPlan->expiry_date ? \Carbon\Carbon::parse($userPlan->expiry_date)->format('d M Y') : 'N/A' }}
                            </td>

                            <td class="px-6 py-4">
                                @if($userPlan->status)
                                    <span class="px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                        Active
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2 whitespace-nowrap">
                                    <a href="{{ route('user-plans.show', $userPlan->id) }}"
                                       class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700">
                                        View
                                    </a>

                                    <a href="{{ route('user-plans.edit', $userPlan->id) }}"
                                       class="bg-yellow-500 text-white px-3 py-2 rounded-lg hover:bg-yellow-600">
                                        Edit
                                    </a>

                                    <form action="{{ route('user-plans.destroy', $userPlan->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this user plan?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-6 text-center text-gray-500">
                                No user plans found for this business.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $userPlans->withQueryString()->links() }}
        </div>
    </div>
</x-layouts.app>