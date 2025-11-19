<x-layouts.app :title="__('Edit API Key')">
    <div class="max-w-3xl mx-auto p-6 bg-white dark:bg-neutral-900 rounded-xl shadow">
        <h1 class="text-xl font-semibold mb-4">Edit API Key</h1>

        <form action="{{ route('api-keys.update', $apiKey->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            @include('api_keys._form', ['apiKey' => $apiKey])
        </form>
    </div>
</x-layouts.app>
