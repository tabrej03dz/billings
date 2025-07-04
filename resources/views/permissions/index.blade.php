<x-layouts.app :title="__('Permissions')">
    <div class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Assign Permissions</h1>
        </div>

        <form action="{{ route('permissions.assign') }}" method="POST">
            @csrf

            <div class="grid md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="user" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select User</label>
                    <select name="user" id="user" required class="mt-1 block w-full border-gray-300 dark:border-neutral-600 rounded-md shadow-sm focus:ring focus:ring-indigo-200 dark:bg-neutral-800 dark:text-white">
                        <option value="">-- Select User --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                    <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-semibold text-gray-700 dark:text-white">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">
                            <input type="checkbox" id="all">
                            <label for="all">All</label>
                        </th>
                        <th class="px-6 py-3">Permission Name</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-700">
                    @forelse ($permissions as $permission)
                        <tr>
                            <td class="px-6 py-4">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="permission_{{ $permission->id }}">
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">
                                <label for="permission_{{ $permission->id }}">{{ $permission->name }}</label>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">No permissions found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                    Assign Permissions
                </button>
            </div>
        </form>
    </div>

    <script>
        // When the "All" checkbox is toggled
        document.getElementById('all').addEventListener('change', function () {
            const isChecked = this.checked;
            const checkboxes = document.querySelectorAll('input[name="permissions[]"]');

            checkboxes.forEach(function (checkbox) {
                checkbox.checked = isChecked;
            });
        });
    </script>

</x-layouts.app>
