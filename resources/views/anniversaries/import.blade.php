<x-layouts.app :title="__('Import Anniversary Records')">
    <div class="max-w-3xl mx-auto">

        <div class="bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl mb-4">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Import Anniversary Records</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Upload Excel/CSV file to bulk import anniversary records.
            </p>
        </div>

        @if($errors->any())
            <div class="p-3 mb-4 rounded-lg bg-red-50 text-red-700 border border-red-200">
                <ul class="list-disc ml-5 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('anniversaries.import') }}" method="POST" enctype="multipart/form-data"
              class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6 space-y-4">
            @csrf

            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Business ID</label>
                <input type="number" name="business_id" value="{{ old('business_id') }}"
                       class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                       placeholder="Optional">
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Excel / CSV File</label>
                <input type="file" name="file" required accept=".xlsx,.xls,.csv"
                       class="mt-1 w-full rounded-lg border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white p-2">
                <p class="text-xs text-gray-500 mt-2">
                    Required columns: phone, date_of_anniversary. Optional column: name.
                </p>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-3 text-sm">
                <div class="font-semibold mb-1">Accepted Header Names:</div>
                <p><strong>Phone:</strong> phone, mobile, mobile_no, contact, contact_no</p>
                <p><strong>Date:</strong> date_of_anniversary, anniversary_date, anniversary, doa</p>
                <p><strong>Name:</strong> name, full_name, customer_name</p>
                <p><strong>Wish Time:</strong> wish_time, time, send_time, whatsapp_time</p>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('anniversaries.index') }}"
                   class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-neutral-800 text-gray-800 dark:text-white border">
                    Back
                </a>

                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white">
                    Import Records
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>