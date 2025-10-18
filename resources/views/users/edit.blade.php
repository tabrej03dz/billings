<x-layouts.app :title="__('Edit User')">
    <div class="max-w-3xl mx-auto p-6 bg-white dark:bg-neutral-900 rounded-xl shadow">
        <h1 class="text-xl font-semibold mb-4">Edit User</h1>

        <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf @method('PUT')
            @include('users._form', ['user' => $user])
        </form>
    </div>
</x-layouts.app>
