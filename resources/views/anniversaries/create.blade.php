<x-layouts.app :title="__('Add Anniversary')">
    <div class="max-w-3xl mx-auto">

        <div class="bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl mb-4">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Add Anniversary Record</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Create new anniversary record.</p>
        </div>

        <form action="{{ route('anniversaries.store') }}" method="POST"
              class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6 space-y-4">
            @csrf

            @include('anniversaries.form')

            <div class="flex justify-end gap-2">
                <a href="{{ route('anniversaries.index') }}"
                   class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-neutral-800 text-gray-800 dark:text-white border">
                    Back
                </a>

                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white">
                    Save Record
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>