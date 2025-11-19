<x-layouts.app :title="__('Create API Key')">
    <div class="max-w-3xl mx-auto p-6 bg-white dark:bg-neutral-900 rounded-xl shadow">
        <h1 class="text-xl font-semibold mb-4">Create API Key</h1>

        <form action="{{ route('api-keys.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('api_keys._form', ['apiKey' => null])
        </form>
    </div>
</x-layouts.app>
