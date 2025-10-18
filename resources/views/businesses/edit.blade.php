<x-layouts.app :title="__('Edit Business')">
    <div class="max-w-3xl mx-auto p-6 bg-white dark:bg-neutral-900 rounded-xl shadow">
        <h1 class="text-xl font-semibold mb-4">Edit Business</h1>

        <form action="{{ route('businesses.update', $business->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            @include('businesses._form', ['business' => $business])
        </form>
    </div>
</x-layouts.app>
