<x-layouts.app :title="__('Edit Item')">
    <div class="max-w-3xl mx-auto p-6 bg-white dark:bg-neutral-900 rounded-xl shadow">
        <h1 class="text-xl font-semibold mb-4">Edit Item</h1>

        <form action="{{ route('items.update', $item->id) }}" method="POST" class="space-y-6">
            @csrf @method('PUT')
            @include('items._form', ['item' => $item, 'categories' => $categories])
        </form>
    </div>
</x-layouts.app>
