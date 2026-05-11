<x-layouts.app :title="__('Plan Details')">
    <div class="max-w-5xl mx-auto flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Plan Details</h1>

            <div class="flex items-center gap-2">
                <a href="{{ route('plans.edit', $plan->id) }}"
                   class="px-4 py-2 text-sm font-medium text-white bg-yellow-500 rounded-lg hover:bg-yellow-600">
                    Edit
                </a>

                <a href="{{ route('plans.index') }}"
                   class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                    Back
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Plan Name</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Subtitle</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $plan->subtitle ?: '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Slug</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ $plan->slug }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Price</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        ₹{{ number_format($plan->price, 2) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Duration</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $plan->duration_days }} days
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sort Order</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $plan->sort_order }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                    <p class="mt-1">
                        @if($plan->status)
                            <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-red-100 text-red-700">
                                Inactive
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Recommended</p>
                    <p class="mt-1">
                        @if($plan->is_recommended)
                            <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">
                                Yes
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">
                                No
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Created At</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $plan->created_at?->format('d M Y h:i A') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Updated At</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $plan->updated_at?->format('d M Y h:i A') }}
                    </p>
                </div>

                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Description</p>
                    <div class="mt-1 text-base text-gray-900 dark:text-white">
                        {{ $plan->description ?: '—' }}
                    </div>
                </div>

                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Plan Features</p>

                    @if($plan->planFeatures && $plan->planFeatures->count())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($plan->planFeatures as $feature)
                                <div class="rounded-lg border border-gray-200 dark:border-neutral-700 p-4 bg-gray-50 dark:bg-neutral-800">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                @if($feature->icon)
                                                    <span class="mr-1">{{ $feature->icon }}</span>
                                                @endif

                                                {{ $feature->title }}
                                            </p>

                                            @if($feature->description)
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                                    {{ $feature->description }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="flex flex-col items-end gap-1">
                                            @if($feature->is_active)
                                                <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-red-100 text-red-700">
                                                    Inactive
                                                </span>
                                            @endif

                                            <span class="text-xs text-gray-500">
                                                Order: {{ $feature->sort_order }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No features added.</p>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Permissions</p>

                    <div class="flex flex-wrap gap-2">
                        @forelse($plan->permissions as $permission)
                            <span class="inline-flex items-center px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-700">
                                {{ $permission->name }}
                            </span>
                        @empty
                            <span class="text-gray-500">No permissions assigned.</span>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-layouts.app>