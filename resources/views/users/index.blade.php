<x-layouts.app :title="__('Users')">
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Users</h1>
            <a href="{{ route('users.create') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow">
                + New User
            </a>
        </div>

        @if (session('success'))
            <div class="p-4 bg-green-100 text-green-800 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-medium tracking-wider">
                <tr>
                    <th class="px-6 py-3">#</th>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Business</th>
                    <th class="px-6 py-3">Role</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                @forelse ($users as $index => $user)
                    <tr>
                        <td class="px-6 py-4">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $user->name }}</td>
                        <td class="px-6 py-4">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            {{ $user->business->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $user->getRoleNames() }}
                        </td>
                        <td class="px-6 py-4 flex items-center gap-3">
                            <a href="{{ route('users.edit', $user->id) }}"
                               class="text-yellow-600 hover:underline">Edit</a>

                            <form action="{{ route('users.delete', $user->id) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this user?');">
                                @csrf
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>

                            <a href="{{ route('users.permissions', $user->id) }}"
                               class="text-yellow-600 hover:underline">Permissions</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No users found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
