<x-layouts.app :title="__('Upload Birthday Excel')">
    <div class="flex flex-col gap-4">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Upload Excel</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Bulk import birthday records.</p>
            </div>

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

        <div class="p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900">
            <div class="mb-4">
                <div class="text-sm font-semibold text-gray-800 dark:text-white">Excel Columns Required</div>
                <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    Header row must include:
                    <span class="font-semibold">phone</span>,
                    <span class="font-semibold">date_of_birth</span>
                    (optional: <span class="font-semibold">name</span>)
                </div>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    date_of_birth formats supported: <b>YYYY-MM-DD</b>, <b>DD-MM-YYYY</b>, or Excel date.
                </div>
            </div>

            <form action="{{ route('birthday-records.import') }}" method="POST" enctype="multipart/form-data"
                  class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf

                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Excel File *</label>
                    <input type="file" name="file" required
                           class="mt-1 w-full rounded-lg border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Allowed: xlsx, xls, csv</p>
                </div>

                <div class="md:col-span-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Business ID (optional)</label>
                    <input type="number" name="business_id" value="{{ old('business_id') }}"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g. 1">
                </div>

                <div class="md:col-span-3 flex items-center gap-2 mt-1">
                    <button type="submit"
                            class="inline-flex items-center px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Import Now
                    </button>

                    <a href="{{ route('birthday-records.index') }}"
                       class="inline-flex items-center px-5 py-2 text-sm font-medium text-gray-800 dark:text-white bg-gray-100 dark:bg-neutral-800 rounded-lg hover:bg-gray-200 dark:hover:bg-neutral-700 border border-gray-200 dark:border-gray-700">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

    </div>
</x-layouts.app>
