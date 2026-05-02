<x-layouts.app :title="__('Anniversary Details')">
    <div class="max-w-3xl mx-auto">

        <div class="bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl mb-4">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Anniversary Details</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">View anniversary record information.</p>
        </div>

        <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6 space-y-4">

            <div>
                <p class="text-xs text-gray-500">Name</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ $record->name ?? '-' }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Phone</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ $record->phone }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Date of Anniversary</p>
                <p class="font-medium text-gray-900 dark:text-white">
                    {{ $record->date_of_anniversary ? \Carbon\Carbon::parse($record->date_of_anniversary)->format('d M, Y') : '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Business ID</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ $record->business_id ?? '-' }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Added By</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ optional($record->user)->name ?? '-' }}</p>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <a href="{{ route('anniversaries.index') }}"
                   class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-neutral-800 text-gray-800 dark:text-white border">
                    Back
                </a>

                <a href="{{ route('anniversaries.edit', $record->id) }}"
                   class="px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white">
                    Edit
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>