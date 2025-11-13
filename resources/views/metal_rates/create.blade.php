<x-layouts.app :title="__('Create Metal Rate')">
    <div class="max-w-3xl mx-auto p-6 bg-white dark:bg-neutral-900 rounded-xl shadow">
        <h1 class="text-xl font-semibold mb-4">Create Metal Rate</h1>

        <form action="{{ route('metal-rates.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('metal_rates._form', ['metalRate' => null])
        </form>
    </div>
</x-layouts.app>
