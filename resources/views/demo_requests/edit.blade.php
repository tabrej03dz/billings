<x-layouts.app :title="__('Edit Demo Request')">
    <div class="flex flex-col gap-4">

        <div class="flex flex-wrap items-center justify-between gap-3 mb-1 bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Demo Request</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Update request status and details.</p>
            </div>

            <a href="{{ route('demo-requests.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-800 dark:text-white bg-gray-100 dark:bg-neutral-800 rounded-lg border">
                Back
            </a>
        </div>

        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <form action="{{ route('demo-requests.update', $demoRequest->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                @method('PUT')

                @include('demo_requests._form', ['demoRequest' => $demoRequest])

                <div class="md:col-span-2 flex justify-end gap-2">
                    <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        Update Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>