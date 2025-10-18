<x-layouts.app :title="__('Edit Client')">
    <div class="max-w-3xl mx-auto p-6 bg-white dark:bg-neutral-900 rounded-xl shadow">
        <h1 class="text-xl font-semibold mb-4">Edit Client</h1>

        <form action="{{ route('clients.update', $client->id) }}" method="POST" class="space-y-6">
            @csrf @method('PUT')
            @include('clients._form', ['client' => $client])
        </form>
    </div>
</x-layouts.app>
