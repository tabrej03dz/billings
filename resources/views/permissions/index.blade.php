<x-layouts.app :title="__('Permissions')">
    <div
        x-data="{
            open: false,
            delOpen: false,
            selectedPermission: null,
            selectedCount: 0,
            submitAssign(){ document.getElementById('assignForm')?.requestSubmit(); },
            updateCount(){
                this.selectedCount = document.querySelectorAll('input[name=&quot;permissions[]&quot;]:checked').length;
            }
        }"
        x-init="updateCount()"
        class="flex flex-col gap-6"
    >

        <!-- Header -->
        <div class="flex flex-wrap gap-3 justify-between items-center bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Assign Permissions</h1>

            <div class="flex items-center gap-3">
                <!-- Selected counter -->
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-neutral-800 text-amber-700 dark:text-gray-200">
                    Selected: <span x-text="selectedCount"></span>
                </span>

                <!-- Assign (header) -->
                <button
                    type="button"
                    @click="submitAssign()"
                    :disabled="selectedCount === 0 || !document.getElementById('user')?.value"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-blue-600 text-white font-semibold hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    title="Select a user and at least one permission"
                >
                    Assign Selected
                </button>

                <!-- Create Permission Button -->
                <button
                    type="button"
                    @click="open = true; $nextTick(() => document.getElementById('perm-name')?.focus())"
                    class="inline-flex items-center gap-2 px-4 py-3 rounded-md bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" />
                    </svg>
                    Create Permission
                </button>
            </div>
        </div>

        <!-- Assign Form -->
        <form id="assignForm" action="{{ route('permissions.assign') }}" method="POST">
            @csrf

            <div class="grid md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="user" class="block text-sm font-medium text-amber-700 dark:text-gray-300 p-2">Select User</label>
                    <select name="user" id="user" required class="mt-1 p-3 block w-full border-gray-300 dark:border-neutral-600 rounded-md shadow-sm focus:ring focus:ring-indigo-200 dark:bg-neutral-800 dark:text-white border border-gray-300">
                        <option value="">-- Select User --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                    <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-semibold text-gray-700 dark:text-white">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">
                            <input type="checkbox" id="all">
                            <label for="all">All</label>
                        </th>
                        <th class="px-6 py-3">Permission Name</th>
                        <th class="px-6 py-3 text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-700">
                    @forelse ($permissions as $permission)
                        <tr>
                            <td class="px-6 py-4">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->name }}"
                                    id="permission_{{ $permission->id }}"
                                    @change="updateCount()"
                                >
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">
                                <label for="permission_{{ $permission->id }}">{{ $permission->name }}</label>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button
                                    type="button"
                                    @click="selectedPermission = '{{ $permission->id }}'; delOpen = true"
                                    class="px-3 py-1 rounded-md bg-red-600 text-white hover:bg-red-700 text-sm font-medium transition"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No permissions found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bottom Assign Button (kept) -->
            <div class="mt-6">
                <button
                    type="submit"
                    class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition"
                >
                    Assign Permissions
                </button>
            </div>
        </form>

        <!-- Create Permission Modal -->
        <div
            x-show="open"
            x-transition
            x-cloak
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-50 flex items-center justify-center"
            aria-modal="true" role="dialog"
        >
            <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
            <div class="relative w-full max-w-md mx-4 rounded-xl bg-white dark:bg-neutral-900 shadow-2xl border border-gray-200 dark:border-neutral-700">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-neutral-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Create New Permission</h2>
                    <button @click="open = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white">✕</button>
                </div>

                <form action="{{ route('permissions.store') }}" method="POST" class="px-5 py-4 space-y-4">
                    @csrf
                    <div>
                        <label for="perm-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Permission Name</label>
                        <input
                            type="text"
                            name="name"
                            id="perm-name"
                            required
                            class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-neutral-600 shadow-sm focus:ring focus:ring-indigo-200 dark:bg-neutral-800 dark:text-white"
                            placeholder="e.g. invoice.create"
                        >
                    </div>

                    <div>
                        <label for="guard" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Guard</label>
                        <select
                            name="guard_name"
                            id="guard"
                            class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-neutral-600 shadow-sm focus:ring focus:ring-indigo-200 dark:bg-neutral-800 dark:text-white"
                        >
                            <option value="web" selected>web</option>
                            <option value="api">api</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="open = false" class="px-4 py-2 rounded-md border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-neutral-800">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-md bg-emerald-600 text-white font-semibold hover:bg-emerald-700">
                            Save Permission
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Permission Modal (POST destroy) -->
        <div
            x-show="delOpen"
            x-transition
            x-cloak
            @keydown.escape.window="delOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center"
        >
            <div class="absolute inset-0 bg-black/50" @click="delOpen = false"></div>

            <div class="relative bg-white dark:bg-neutral-900 rounded-lg shadow-xl w-full max-w-sm border border-gray-200 dark:border-neutral-700">
                <div class="p-5 text-center">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete Permission?</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                        Are you sure you want to delete this permission? This action cannot be undone.
                    </p>

                    <div class="flex justify-center gap-3">
                        <button
                            @click="delOpen = false"
                            class="px-4 py-2 bg-gray-200 dark:bg-neutral-700 rounded-md text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-neutral-600">
                            Cancel
                        </button>

                        <!-- Route binding with placeholder replacement -->
                        <form
                            :action="`{{ route('permissions.destroy', ':id') }}`.replace(':id', selectedPermission ?? '')"
                            method="POST"
                        >
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Toggle all permissions + keep count in sync
        document.getElementById('all').addEventListener('change', function () {
            const isChecked = this.checked;
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = isChecked);
            // trigger Alpine count update
            document.querySelector('[x-data]')?._x_dataStack?.[0]?.updateCount?.();
        });
    </script>
</x-layouts.app>
