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
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold">Assign Businesses</h3>
            <span class="text-xs text-gray-500">Select one or more businesses</span>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium text-gray-700 dark:text-white">
                    <tr>
                        <th class="px-4 py-3 text-center w-20">Select</th>
                        <th class="px-4 py-3 text-left">Business</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-700 bg-white dark:bg-neutral-900">
                    @forelse($businesses as $biz)
                        <tr>
                            <td class="px-4 py-3 text-center">
                                <input
                                    type="checkbox"
                                    name="businesses[]"
                                    value="{{ $biz->id }}"
                                    {{ in_array($biz->id, $oldBusinesses) ? 'checked' : '' }}
                                    class="rounded border-gray-300"
                                >
                            </td>
                            <td class="px-4 py-3 text-gray-800 dark:text-gray-100">
                                <div class="font-medium">{{ $biz->name }}</div>
                                @if(!empty($biz->slug))
                                    <div class="text-xs text-gray-400">/{{ $biz->slug }}</div>
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