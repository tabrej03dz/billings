<x-layouts.app :title="__('Create Item')">
    <div class="max-w-3xl mx-auto p-6 bg-white dark:bg-neutral-900 rounded-xl shadow">
        <h1 class="text-xl font-semibold mb-4">Create Item</h1>

        <form action="{{ route('items.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('items._form', ['item' => null, 'categories' => $categories])
        </form>
    </div>
</x-layouts.app>
