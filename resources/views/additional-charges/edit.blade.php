<x-layouts.app :title="__('Edit Additional Charge')">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-5">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100">Edit Additional Charge</h1>
            <a href="{{ route('additional-charges.index') }}"
               class="text-sm text-blue-600 hover:underline">← Back to list</a>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-300 rounded">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('additional-charges.update', $charge->id) }}"
              class="bg-white dark:bg-neutral-900 p-6 rounded-lg border border-gray-200 dark:border-neutral-700 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Name <span class="text-red-600">*</span></label>
                <input type="text" name="name" required
                       value="{{ old('name', $charge->name) }}"
                       class="w-full border rounded px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Amount (₹) <span class="text-red-600">*</span></label>
                <input type="number" name="amount" required step="0.01" min="0"
                       value="{{ old('amount', $charge->amount) }}"
                       class="w-full border rounded px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100">
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('additional-charges.index') }}"
                   class="px-4 py-2 text-gray-600 dark:text-neutral-300 hover:text-gray-900">Cancel</a>
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow">
                    Update
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
