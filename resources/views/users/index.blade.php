<x-layouts.app :title="__('Users')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between mb-2">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Users</h1>
            <a href="{{ route('users.create') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                + New User
            </a>
        </div>

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-medium tracking-wider">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Businesses</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                @forelse ($users as $u)
                    <tr>
                        <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">{{ $u->name }}</td>
                        <td class="px-6 py-3">{{ $u->email }}</td>
                        <td class="px-6 py-3">{{ $u->businesses_count }}</td>
                        <td class="px-6 py-3 space-x-2">
                            <a href="{{ route('users.edit', $u->id) }}" class="text-yellow-600 hover:underline">Edit</a>
                            <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="inline-block"
                                  onsubmit="return confirm('Delete this user?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No users found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->withQueryString()->links() }}
        </div>
    </div>
</x-layouts.app>
