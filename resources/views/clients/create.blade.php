<x-layouts.app :title="__('Create Client')">
    <div class="max-w-3xl mx-auto p-6 bg-white dark:bg-neutral-900 rounded-xl shadow">
        <h1 class="text-xl font-semibold mb-4">Create Client</h1>

        <form action="{{ route('clients.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('clients._form', ['client' => null])
        </form>
    </div>
</x-layouts.app>
