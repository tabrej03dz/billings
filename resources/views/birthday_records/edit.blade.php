<x-layouts.app :title="__('Edit Birthday Record')">
    <div class="flex flex-col gap-4">

        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Birthday Record</h1>
            <a href="{{ route('birthday-records.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-800 dark:text-white bg-gray-100 dark:bg-neutral-800 rounded-lg hover:bg-gray-200 dark:hover:bg-neutral-700 border border-gray-200 dark:border-gray-700">
                Back
            </a>
        </div>

        @if($errors->any())
            <div class="p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                <ul class="list-disc ml-5 text-sm space-y-1">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('birthday-records.update', $record->id) }}" method="POST"
              class="p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Name (optional)</label>
                    <input type="text" name="name" value="{{ old('name', $record->name) }}"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Phone *</label>
                    <input type="text" name="phone" value="{{ old('phone', $record->phone) }}"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Date of Birth *</label>
                    <input type="date" name="date_of_birth"
                           value="{{ old('date_of_birth', optional($record->date_of_birth)->format('Y-m-d')) }}"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>

{{--                <div class="md:col-span-3">--}}
{{--                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Business ID (optional)</label>--}}
{{--                    <input type="number" name="business_id" value="{{ old('business_id', $record->business_id) }}"--}}
{{--                           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">--}}
{{--                </div>--}}
            </div>

            <div class="mt-5 flex items-center gap-2">
                <button type="submit"
                        class="inline-flex items-center px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Update
                </button>
                <a href="{{ route('birthday-records.index') }}"
                   class="inline-flex items-center px-5 py-2 text-sm font-medium text-gray-800 dark:text-white bg-gray-100 dark:bg-neutral-800 rounded-lg hover:bg-gray-200 dark:hover:bg-neutral-700 border border-gray-200 dark:border-gray-700">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.app>
