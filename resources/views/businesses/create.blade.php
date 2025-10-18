<x-layouts.app :title="__('Create Business')">
    <div class="max-w-3xl mx-auto p-6 bg-white dark:bg-neutral-900 rounded-xl shadow">
        <h1 class="text-xl font-semibold mb-4">Create Business</h1>

        <form action="{{ route('businesses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @include('businesses._form', ['business' => null])
        </form>
    </div>
</x-layouts.app>
