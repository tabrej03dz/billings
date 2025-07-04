<x-layouts.app :title="__('Clients')">
    <div class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">User's Permissions</h1>
        </div>

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-semibold text-gray-700 dark:text-white">
                <tr>
                    <th class="px-6 py-3">#</th>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Action</th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse ($permissions as $permission)
                    <tr>
                        <td class="px-6 py-4">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $permission->name }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('users.permission.remove', ['user' => $user->id, 'permission' => $permission->name]) }}"
                               class="text-yellow-600 hover:underline">Remove</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No clients found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
