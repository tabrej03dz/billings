@props([
    'user' => null,
    'businesses' => collect(),
    'roles' => collect(),
    'selectedBusinesses' => [],
    'selectedRoles' => [],
])

@php
    $isEdit = filled($user?->id);

    $oldBusinesses = old('businesses', $selectedBusinesses ?? []);
    $oldRoles = old('roles', $selectedRoles ?? []);
@endphp

<div class="space-y-6">

    {{-- Basic Details --}}
    <div class="grid md:grid-cols-2 gap-4">

        <div>
            <label class="block text-sm font-medium mb-1">
                Name <span class="text-red-600">*</span>
            </label>
            <input
                type="text"
                name="name"
                required
                value="{{ old('name', $user->name ?? '') }}"
                class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833] dark:border-neutral-700 dark:text-white"
            >
            @error('name')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">
                Email <span class="text-red-600">*</span>
            </label>
            <input
                type="email"
                name="email"
                required
                value="{{ old('email', $user->email ?? '') }}"
                class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833] dark:border-neutral-700 dark:text-white"
            >
            @error('email')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">
                Phone <span class="text-red-600">*</span>
            </label>
            <input
                type="text"
                name="phone"
                required
                value="{{ old('phone', $user->phone ?? '') }}"
                class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833] dark:border-neutral-700 dark:text-white"
            >
            @error('phone')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">
                Google Drive Folder Id <span class="text-red-600">(Optional)</span>
            </label>
            <input
                type="text"
                name="google_drive_folder_id"
                value="{{ old('google_drive_folder_id', $user->google_drive_folder_id ?? '') }}"
                class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833] dark:border-neutral-700 dark:text-white"
            >
            @error('google_drive_folder_id')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">
                {{ $isEdit ? 'Set New Password (optional)' : 'Password' }}
                @unless($isEdit)
                    <span class="text-red-600">*</span>
                @endunless
            </label>
            <input
                type="password"
                name="password"
                {{ $isEdit ? '' : 'required' }}
                placeholder="{{ $isEdit ? 'Leave blank to keep current password' : '' }}"
                class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833] dark:border-neutral-700 dark:text-white"
            >
            @error('password')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">
                {{ $isEdit ? 'Confirm New Password' : 'Confirm Password' }}
                @unless($isEdit)
                    <span class="text-red-600">*</span>
                @endunless
            </label>
            <input
                type="password"
                name="password_confirmation"
                {{ $isEdit ? '' : 'required' }}
                class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833] dark:border-neutral-700 dark:text-white"
            >
        </div>
    </div>


    {{-- Businesses --}}
    <div class="space-y-3" id="business-section">

        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold">Assign Businesses</h3>
            <span class="text-xs text-gray-500">
                Newest Businesses First
            </span>
        </div>

        {{-- Selected Business Counter --}}
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-300">
                Selected Businesses:
                <strong id="selected-business-count">0</strong>
            </span>

            <span class="text-xs text-gray-500">
                Total: {{ $businesses->total() }}
            </span>
        </div>

        {{-- Hidden inputs for selected businesses --}}
        <div id="selected-business-inputs"></div>

        <div class="rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">

            <table class="min-w-full text-sm">
                <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium text-gray-700 dark:text-white">
                    <tr>
                        <th class="px-4 py-3 text-center w-20">
                            Select
                        </th>
                        <th class="px-4 py-3 text-left">
                            Business
                        </th>
                    </tr>
                </thead>

                <tbody
                    id="business-table-body"
                    class="divide-y divide-gray-100 dark:divide-neutral-700 bg-white dark:bg-neutral-900"
                >
                    @forelse($businesses as $biz)
                        <tr>
                            <td class="px-4 py-3 text-center">
                                <input
                                    type="checkbox"
                                    class="business-checkbox rounded border-gray-300"
                                    value="{{ $biz->id }}"
                                    @checked(in_array($biz->id, $oldBusinesses))
                                >
                            </td>

                            <td class="px-4 py-3 text-gray-800 dark:text-gray-100">
                                <div class="font-medium">
                                    {{ $biz->name }}
                                </div>

                                @if(!empty($biz->slug))
                                    <div class="text-xs text-gray-400">
                                        /{{ $biz->slug }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-4 text-center text-gray-500">
                                No businesses found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div id="business-pagination" class="mt-4">
            {{ $businesses->links() }}
        </div>

        @error('businesses')
            <p class="text-red-600 text-xs">{{ $message }}</p>
        @enderror

        @error('businesses.*')
            <p class="text-red-600 text-xs">{{ $message }}</p>
        @enderror
    </div>


    {{-- Roles --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold">Assign Roles</h3>
            <span class="text-xs text-gray-500">Roles are loaded dynamically from Spatie roles table</span>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse($roles as $role)
                <label class="flex items-center gap-3 rounded-xl border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-4 py-3 cursor-pointer hover:border-indigo-400 transition">
                    <input
                        type="checkbox"
                        name="roles[]"
                        value="{{ $role->name }}"
                        {{ in_array($role->name, $oldRoles) ? 'checked' : '' }}
                        class="rounded border-gray-300"
                    >
                    <div>
                        <div class="text-sm font-medium text-gray-800 dark:text-gray-100">
                            {{ ucwords(str_replace(['-', '_'], ' ', $role->name)) }}
                        </div>
                        @if(!empty($role->guard_name))
                            <div class="text-xs text-gray-400">
                                Guard: {{ $role->guard_name }}
                            </div>
                        @endif
                    </div>
                </label>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 rounded-xl border border-dashed border-gray-300 dark:border-neutral-700 px-4 py-6 text-center text-sm text-gray-500">
                    No roles found in Spatie roles table.
                </div>
            @endforelse
        </div>

        @error('roles')
            <p class="text-red-600 text-xs">{{ $message }}</p>
        @enderror

        @error('roles.*')
            <p class="text-red-600 text-xs">{{ $message }}</p>
        @enderror
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3 pt-2">
        <button
            type="submit"
            class="px-5 py-2.5 rounded bg-green-600 text-white hover:bg-green-700 transition"
        >
            {{ $isEdit ? 'Update User' : 'Create User' }}
        </button>

        <a
            href="{{ route('users.index') }}"
            class="px-5 py-2.5 rounded bg-red-500 text-white hover:bg-red-600 transition"
        >
            Cancel
        </a>
    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function () {

    const section = document.getElementById('business-section');

    if (!section) return;

    const tableBody = document.getElementById('business-table-body');
    const pagination = document.getElementById('business-pagination');
    const inputContainer = document.getElementById('selected-business-inputs');
    const selectedCount = document.getElementById('selected-business-count');

    // Previously selected businesses, including validation old input
    const selectedBusinesses = new Set(
        @json(array_values($oldBusinesses)).map(String)
    );

    let loading = false;

    // Create actual inputs for form submission
    function updateHiddenInputs() {
        inputContainer.innerHTML = '';

        selectedBusinesses.forEach(function (id) {
            const input = document.createElement('input');

            input.type = 'hidden';
            input.name = 'businesses[]';
            input.value = id;

            inputContainer.appendChild(input);
        });

        selectedCount.textContent = selectedBusinesses.size;
    }

    // Maintain selection after pagination
    function restoreCheckboxes() {
        tableBody.querySelectorAll('.business-checkbox')
            .forEach(function (checkbox) {
                checkbox.checked = selectedBusinesses.has(
                    String(checkbox.value)
                );
            });
    }

    // Save checkbox changes
    tableBody.addEventListener('change', function (event) {

        if (!event.target.matches('.business-checkbox')) return;

        const id = String(event.target.value);

        if (event.target.checked) {
            selectedBusinesses.add(id);
        } else {
            selectedBusinesses.delete(id);
        }

        updateHiddenInputs();
    });

    // AJAX Pagination: prevent full page reload
    pagination.addEventListener('click', async function (event) {

        const link = event.target.closest('a[href]');

        if (!link || !pagination.contains(link)) return;

        event.preventDefault();

        if (loading) return;

        loading = true;

        try {
            const response = await fetch(link.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Unable to load businesses');
            }

            const html = await response.text();

            const parser = new DOMParser();
            const documentNew = parser.parseFromString(
                html,
                'text/html'
            );

            const newBody = documentNew.getElementById(
                'business-table-body'
            );

            const newPagination = documentNew.getElementById(
                'business-pagination'
            );

            if (!newBody || !newPagination) {
                throw new Error('Pagination content missing');
            }

            tableBody.innerHTML = newBody.innerHTML;
            pagination.innerHTML = newPagination.innerHTML;

            restoreCheckboxes();

            window.history.replaceState(
                {},
                '',
                link.href
            );

        } catch (error) {
            console.error(error);
            alert('Business list load nahi ho payi. Please try again.');
        } finally {
            loading = false;
        }
    });

    // Initialize selected values
    updateHiddenInputs();
    restoreCheckboxes();

});
</script>
