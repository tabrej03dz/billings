<x-layouts.app :title="__('Demo Request Details')">
    <div class="flex flex-col gap-4">

        <div class="flex flex-wrap items-center justify-between gap-3 mb-1 bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Demo Request Details</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">View complete enquiry information.</p>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('demo-requests.edit', $demoRequest->id) }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700">
                    Edit
                </a>

                <a href="{{ route('demo-requests.index') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-800 dark:text-white bg-gray-100 dark:bg-neutral-800 rounded-lg border">
                    Back
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">

                <div>
                    <p class="text-xs text-gray-500">Name</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $demoRequest->name }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Mobile</p>
                    <a href="tel:{{ $demoRequest->mobile }}" class="font-semibold text-blue-600 hover:underline">
                        {{ $demoRequest->mobile }}
                    </a>
                </div>

                <div>
                    <p class="text-xs text-gray-500">City</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $demoRequest->city ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Business Name</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $demoRequest->business_name ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Status</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ ucfirst($demoRequest->status) }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Updated By</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ optional($demoRequest->updatedBy)->name ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Created At</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $demoRequest->created_at ? $demoRequest->created_at->format('d M, Y h:i A') : '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Updated At</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $demoRequest->updated_at ? $demoRequest->updated_at->format('d M, Y h:i A') : '-' }}
                    </p>
                </div>

                <div class="md:col-span-2">
                    <p class="text-xs text-gray-500">Message</p>
                    <p class="mt-1 text-gray-800 dark:text-gray-200">{{ $demoRequest->message ?? '-' }}</p>
                </div>

                <div class="md:col-span-2">
                    <p class="text-xs text-gray-500">Note</p>
                    <p class="mt-1 text-gray-800 dark:text-gray-200">{{ $demoRequest->note ?? '-' }}</p>
                </div>

            </div>
        </div>
    </div>
</x-layouts.app>