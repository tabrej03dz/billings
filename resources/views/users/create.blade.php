<x-layouts.app :title="__('Create User')">
    <div class="max-w-3xl mx-auto p-6 bg-white dark:bg-neutral-900 rounded-xl shadow">
        <h1 class="text-xl font-semibold mb-4">Create User</h1>

        <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('users._form', ['user' => null])
        </form>
    </div>
</x-layouts.app>
