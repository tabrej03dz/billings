<x-layouts.app :title="__('Edit Metal Rate')">
    <div class="max-w-3xl mx-auto p-6 bg-white dark:bg-neutral-900 rounded-xl shadow">
        <h1 class="text-xl font-semibold mb-4">Edit Metal Rate</h1>

        <form action="{{ route('metal-rates.update', $metalRate->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            @include('metal_rates._form', ['metalRate' => $metalRate])
        </form>
    </div>
</x-layouts.app>
