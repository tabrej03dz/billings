<x-layouts.app :title="__('Bill Template Details')">
    <div class="flex flex-col gap-4">

        <div class="flex flex-wrap items-center justify-between gap-3 bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Bill Template Details</h1>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    View complete information of the selected bill template.
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('bill-templates.edit', $billTemplate->id) }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-yellow-500 rounded-lg hover:bg-yellow-600">
                    Edit
                </a>

                <a href="{{ route('bill-templates.index') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                    Back
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Template Name</p>
                    <div class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ $billTemplate->name }}
                    </div>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Page Name</p>
                    <div class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ $billTemplate->page_name }}
                    </div>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Created At</p>
                    <div class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ $billTemplate->created_at?->format('d M Y h:i A') }}
                    </div>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Updated At</p>
                    <div class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ $billTemplate->updated_at?->format('d M Y h:i A') }}
                    </div>
                </div>

                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Description</p>
                    <div class="text-sm leading-7 text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-neutral-800 rounded-lg p-4 border border-gray-200 dark:border-neutral-700 min-h-[120px]">
                        {{ $billTemplate->description ?: 'No description available.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>