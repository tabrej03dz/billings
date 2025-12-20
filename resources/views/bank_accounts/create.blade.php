<x-layouts.app :title="__('Add Bank Account')">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Add Bank Account</h1>
                <div class="text-xs text-gray-500">Business: {{ $businessRow->name }} / {{ $businessRow->slug }}</div>
            </div>

            <a href="{{ route('bank-accounts.index', $businessRow->id) }}"
               class="text-sm px-3 py-2 rounded-lg border hover:bg-gray-50 dark:hover:bg-neutral-800">
                ← Back
            </a>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900 p-5">
            <form action="{{ route('bank-accounts.store', $businessRow->id) }}" method="POST" class="space-y-4">
                @csrf
                @include('bank_accounts._form', ['bankAccount' => $bankAccount])
            </form>
        </div>
    </div>
</x-layouts.app>
