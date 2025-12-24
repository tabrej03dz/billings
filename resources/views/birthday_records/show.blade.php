<x-layouts.app :title="__('Birthday Record')">
    <div class="flex flex-col gap-4">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Birthday Record</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">View details</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('birthday-records.edit', $record->id) }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700">
                    Edit
                </a>
                <a href="{{ route('birthday-records.index') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-800 dark:text-white bg-gray-100 dark:bg-neutral-800 rounded-lg hover:bg-gray-200 dark:hover:bg-neutral-700 border border-gray-200 dark:border-gray-700">
                    Back
                </a>
            </div>
        </div>

        <div class="p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Name</dt>
                    <dd class="text-base font-medium text-gray-900 dark:text-white">{{ $record->name ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Phone</dt>
                    <dd class="text-base font-medium text-gray-900 dark:text-white">{{ $record->phone }}</dd>
                </div>

                <div>
                    <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Date of Birth</dt>
                    <dd class="text-base font-medium text-gray-900 dark:text-white">
                        {{ optional($record->date_of_birth)->format('d M, Y') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Business ID</dt>
                    <dd class="text-base font-medium text-gray-900 dark:text-white">{{ $record->business_id ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Added By</dt>
                    <dd class="text-base font-medium text-gray-900 dark:text-white">{{ optional($record->user)->name ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Created</dt>
                    <dd class="text-base font-medium text-gray-900 dark:text-white">{{ optional($record->created_at)->format('d M, Y h:i A') }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex items-center justify-between">
                <form action="{{ route('birthday-records.destroy', $record->id) }}" method="POST"
                      onsubmit="return confirm('Delete this record?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-600 hover:underline text-sm">Delete Record</button>
                </form>

                <a href="{{ route('birthday-records.index') }}" class="text-blue-600 hover:underline text-sm">
                    Back to list
                </a>
            </div>
        </div>

    </div>
</x-layouts.app>
