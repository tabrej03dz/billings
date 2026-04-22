<x-layouts.app :title="__('User Plan Details')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">User Plan Details</h1>

            <a href="{{ route('user-plans.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                Back
            </a>
        </div>

        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">User Name</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $userPlan->user->name ?? 'N/A' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">User Email</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $userPlan->user->email ?? 'N/A' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Plan Name</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $userPlan->plan->name ?? 'N/A' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Plan Price</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        ₹{{ number_format($userPlan->plan->price ?? 0, 2) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Duration</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $userPlan->plan->duration_days ?? 0 }} Days
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                    <p class="mt-1">
                        @if($userPlan->status)
                            <span class="px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                Active
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                Inactive
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Start Date</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $userPlan->start_date ? \Carbon\Carbon::parse($userPlan->start_date)->format('d M Y') : 'N/A' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Expiry Date</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $userPlan->expiry_date ? \Carbon\Carbon::parse($userPlan->expiry_date)->format('d M Y') : 'N/A' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Created At</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $userPlan->created_at ? $userPlan->created_at->format('d M Y h:i A') : 'N/A' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Updated At</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $userPlan->updated_at ? $userPlan->updated_at->format('d M Y h:i A') : 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('user-plans.edit', $userPlan->id) }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-yellow-500 rounded-lg hover:bg-yellow-600">
                    Edit
                </a>

                <form action="{{ route('user-plans.destroy', $userPlan->id) }}"
                      method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this user plan?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>