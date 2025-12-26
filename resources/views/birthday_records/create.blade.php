<x-layouts.app :title="__('Add Birthday Record')">
    <div class="flex flex-col gap-4">

        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Add Birthday Record</h1>
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

        <form action="{{ route('birthday-records.store') }}" method="POST"
              class="p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Name (optional)</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g. Rahul Sharma">
                </div>

                <div class="md:col-span-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Phone *</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g. 9876543210" required>
                </div>

                <div class="md:col-span-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Date of Birth *</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>

{{--                <div class="md:col-span-3">--}}
{{--                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Business ID (optional)</label>--}}
{{--                    <input type="number" name="business_id" value="{{ old('business_id') }}"--}}
{{--                           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"--}}
{{--                           placeholder="If multi-business, pass business_id">--}}
{{--                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">--}}
{{--                        Agar aap multi-business use kar rahe ho to business_id fill karo, warna blank.--}}
{{--                    </p>--}}
{{--                </div>--}}
            </div>

            <div class="mt-5 flex items-center gap-2">
                <button type="submit"
                        class="inline-flex items-center px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Save
                </button>
                <a href="{{ route('birthday-records.index') }}"
                   class="inline-flex items-center px-5 py-2 text-sm font-medium text-gray-800 dark:text-white bg-gray-100 dark:bg-neutral-800 rounded-lg hover:bg-gray-200 dark:hover:bg-neutral-700 border border-gray-200 dark:border-gray-700">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.app>
