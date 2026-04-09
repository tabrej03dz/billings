<x-layouts.app :title="__('Permissions & Roles')">
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div
        x-data="permissionRolePage()"
        x-init="init()"
        class="flex flex-col gap-6"
    >
        <!-- Header -->
        <div class="flex flex-wrap gap-3 justify-between items-center bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Assign Permissions
            </h1>

            <div class="flex flex-wrap items-center gap-3">
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-neutral-800 text-amber-700 dark:text-gray-200">
                    Selected: <span x-text="selectedCount"></span>
                </span>

                <button
                    type="button"
                    @click="submitAssign()"
                    :disabled="selectedCount === 0 || (!document.getElementById('user_id')?.value && !document.getElementById('role_id')?.value)"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-blue-600 text-white font-semibold hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    title="Select at least one user or role and one permission"
                >
                    Assign Selected
                </button>

                <button
                    type="button"
                    @click="openPermissionModal = true; $nextTick(() => document.getElementById('perm-name')?.focus())"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" />
                    </svg>
                    Create Permission
                </button>

                <button
                    type="button"
                    @click="openRoleModal = true; $nextTick(() => document.getElementById('role-name')?.focus())"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-violet-600 text-white font-semibold hover:bg-violet-700 transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" />
                    </svg>
                    Create Role
                </button>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 text-green-700 px-4 py-3 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <!-- Error Messages -->
        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Assign Form -->
        <form id="assignForm" action="{{ route('permissions.assign') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Target Selection -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-5">
                <div class="grid md:grid-cols-3 gap-4">
                    <!-- User Select -->
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-amber-700 dark:text-gray-300 mb-2">
                            Select User
                        </label>
                        <select
                            name="user_id"
                            id="user_id"
                            class="w-full p-3 rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm focus:ring focus:ring-indigo-200 dark:bg-neutral-800 dark:text-white"
                        >
                            <option value="">-- Select User --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Role Select -->
                    <div>
                        <label for="role_id" class="block text-sm font-medium text-violet-700 dark:text-gray-300 mb-2">
                            Select Role
                        </label>
                        <select
                            name="role_id"
                            id="role_id"
                            class="w-full p-3 rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm focus:ring focus:ring-indigo-200 dark:bg-neutral-800 dark:text-white"
                        >
                            <option value="">-- Select Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Guard -->
                    <div>
                        <label for="guard_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Guard
                        </label>
                        <select
                            name="guard_name"
                            id="guard_name"
                            class="w-full p-3 rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm focus:ring focus:ring-indigo-200 dark:bg-neutral-800 dark:text-white"
                        >
                            <option value="web" {{ old('guard_name', 'web') == 'web' ? 'selected' : '' }}>web</option>
                            <option value="api" {{ old('guard_name') == 'api' ? 'selected' : '' }}>api</option>
                        </select>
                    </div>
                </div>

                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    User, Role, ya dono me se jo bhi select karoge us par selected permissions assign ho jayengi.
                </p>
            </div>

            <!-- Permissions Table -->
            <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                    <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-semibold text-gray-700 dark:text-white">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="allPermissions" @change="toggleAllPermissions($event)">
                                    <label for="allPermissions">All</label>
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left">Permission Name</th>
                            <th class="px-6 py-3 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-700">
                        @forelse ($permissions as $permission)
                            <tr>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-200">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="px-6 py-4">
                                    <input
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permission->name }}"
                                        id="permission_{{ $permission->id }}"
                                        class="permission-checkbox"
                                        @change="updateCount()"
                                        {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}
                                    >
                                </td>

                                <td class="px-6 py-4 text-gray-900 dark:text-white">
                                    <label for="permission_{{ $permission->id }}" class="cursor-pointer">
                                        {{ $permission->name }}
                                    </label>
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
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No permissions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Roles Table -->
            <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900">
                <div class="px-6 py-4 bg-[#E9DDFF] dark:bg-[#4B3B63] border-b border-gray-200 dark:border-neutral-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Available Roles</h2>
                </div>

                <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                    <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-semibold text-gray-700 dark:text-white">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">Role Name</th>
                            <th class="px-6 py-3 text-left">Guard</th>
                            <th class="px-6 py-3 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-700">
                        @forelse ($roles as $role)
                            <tr>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-200">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="px-6 py-4 text-gray-900 dark:text-white">
                                    {{ $role->name }}
                                </td>

                                <td class="px-6 py-4 text-gray-700 dark:text-gray-200">
                                    {{ $role->guard_name }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <button
                                        type="button"
                                        @click="selectedRole = '{{ $role->id }}'; delRoleOpen = true"
                                        class="px-3 py-1 rounded-md bg-red-600 text-white hover:bg-red-700 text-sm font-medium transition"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No roles found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bottom Assign Button -->
            <div class="mt-6">
                <button
                    type="submit"
                    :disabled="selectedCount === 0 || (!document.getElementById('user_id')?.value && !document.getElementById('role_id')?.value)"
                    class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Assign Permissions
                </button>
            </div>
        </form>

        <!-- Create Permission Modal -->
        <div
            x-show="openPermissionModal"
            x-transition
            x-cloak
            @keydown.escape.window="openPermissionModal = false"
            class="fixed inset-0 z-50 flex items-center justify-center"
            aria-modal="true"
            role="dialog"
        >
            <div class="absolute inset-0 bg-black/50" @click="openPermissionModal = false"></div>

            <div class="relative w-full max-w-md mx-4 rounded-xl bg-white dark:bg-neutral-900 shadow-2xl border border-gray-200 dark:border-neutral-700">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-neutral-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Create New Permission</h2>
                    <button @click="openPermissionModal = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white">✕</button>
                </div>

                <form action="{{ route('permissions.store') }}" method="POST" class="px-5 py-4 space-y-4">
                    @csrf

                    <div>
                        <label for="perm-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Permission Name
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="perm-name"
                            required
                            class="mt-1 p-3 block w-full rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm focus:ring focus:ring-indigo-200 dark:bg-neutral-800 dark:text-white"
                            placeholder="e.g. create invoice"
                        >
                    </div>

                    <div>
                        <label for="permission_guard" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Guard
                        </label>
                        <select
                            name="guard_name"
                            id="permission_guard"
                            class="mt-1 p-3 block w-full rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm focus:ring focus:ring-indigo-200 dark:bg-neutral-800 dark:text-white"
                        >
                            <option value="web" selected>web</option>
                            <option value="api">api</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="openPermissionModal = false"
                            class="px-4 py-2 rounded-md border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-neutral-800"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="px-5 py-2 rounded-md bg-emerald-600 text-white font-semibold hover:bg-emerald-700"
                        >
                            Save Permission
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create Role Modal -->
        <div
            x-show="openRoleModal"
            x-transition
            x-cloak
            @keydown.escape.window="openRoleModal = false"
            class="fixed inset-0 z-50 flex items-center justify-center"
            aria-modal="true"
            role="dialog"
        >
            <div class="absolute inset-0 bg-black/50" @click="openRoleModal = false"></div>

            <div class="relative w-full max-w-md mx-4 rounded-xl bg-white dark:bg-neutral-900 shadow-2xl border border-gray-200 dark:border-neutral-700">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-neutral-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Create New Role</h2>
                    <button @click="openRoleModal = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white">✕</button>
                </div>

                <form action="{{ route('roles.store') }}" method="POST" class="px-5 py-4 space-y-4">
                    @csrf

                    <div>
                        <label for="role-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Role Name
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="role-name"
                            required
                            class="mt-1 p-3 block w-full rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm focus:ring focus:ring-indigo-200 dark:bg-neutral-800 dark:text-white"
                            placeholder="e.g. manager"
                        >
                    </div>

                    <div>
                        <label for="role_guard" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Guard
                        </label>
                        <select
                            name="guard_name"
                            id="role_guard"
                            class="mt-1 p-3 block w-full rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm focus:ring focus:ring-indigo-200 dark:bg-neutral-800 dark:text-white"
                        >
                            <option value="web" selected>web</option>
                            <option value="api">api</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="openRoleModal = false"
                            class="px-4 py-2 rounded-md border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-neutral-800"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="px-5 py-2 rounded-md bg-violet-600 text-white font-semibold hover:bg-violet-700"
                        >
                            Save Role
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Permission Modal -->
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
                            class="px-4 py-2 bg-gray-200 dark:bg-neutral-700 rounded-md text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-neutral-600"
                        >
                            Cancel
                        </button>

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

        <!-- Delete Role Modal -->
        <div
            x-show="delRoleOpen"
            x-transition
            x-cloak
            @keydown.escape.window="delRoleOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center"
        >
            <div class="absolute inset-0 bg-black/50" @click="delRoleOpen = false"></div>

            <div class="relative bg-white dark:bg-neutral-900 rounded-lg shadow-xl w-full max-w-sm border border-gray-200 dark:border-neutral-700">
                <div class="p-5 text-center">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete Role?</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                        Are you sure you want to delete this role? This action cannot be undone.
                    </p>

                    <div class="flex justify-center gap-3">
                        <button
                            @click="delRoleOpen = false"
                            class="px-4 py-2 bg-gray-200 dark:bg-neutral-700 rounded-md text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-neutral-600"
                        >
                            Cancel
                        </button>

                        <form
                            :action="`{{ route('roles.destroy', ':id') }}`.replace(':id', selectedRole ?? '')"
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
        function permissionRolePage() {
            return {
                openPermissionModal: false,
                openRoleModal: false,
                delOpen: false,
                delRoleOpen: false,
                selectedPermission: null,
                selectedRole: null,
                selectedCount: 0,

                init() {
                    this.updateCount();
                },

                submitAssign() {
                    document.getElementById('assignForm')?.requestSubmit();
                },

                updateCount() {
                    this.selectedCount = document.querySelectorAll('input[name="permissions[]"]:checked').length;
                },

                toggleAllPermissions(event) {
                    const isChecked = event.target.checked;
                    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
                        cb.checked = isChecked;
                    });
                    this.updateCount();
                }
            }
        }
    </script>
</x-layouts.app>