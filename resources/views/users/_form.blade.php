@props(['user' => null, 'businesses' => [], 'roles' => [], 'pivotRoles' => []])

@php
    $isEdit = filled($user?->id);
@endphp

<div class="space-y-6">

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Name <span class="text-red-600">*</span></label>
            <input type="text" name="name" required
                   value="{{ old('name', $user->name ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2">
            @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Email <span class="text-red-600">*</span></label>
            <input type="email" name="email" required
                   value="{{ old('email', $user->email ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2">
            @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div @class(['md:col-span-2' => true])>
            <label class="block text-sm font-medium mb-1">
                {{ $isEdit ? 'Set New Password (optional)' : 'Password' }}
                @unless($isEdit) <span class="text-red-600">*</span> @endunless
            </label>
            <input type="password" name="password" {{ $isEdit ? '' : 'required' }}
            class="mt-1 w-full border rounded px-3 py-2" placeholder="{{ $isEdit ? 'Leave blank to keep current' : '' }}">
            @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">{{ $isEdit ? 'Confirm New Password' : 'Confirm Password' }} @unless($isEdit)<span class="text-red-600">*</span>@endunless</label>
            <input type="password" name="password_confirmation" {{ $isEdit ? '' : 'required' }}
            class="mt-1 w-full border rounded px-3 py-2">
        </div>
    </div>

    {{-- Assign to Businesses + Role --}}
    <div class="space-y-3">
        <h3 class="text-sm font-semibold">Assign to Businesses</h3>

        <div class="rounded border border-gray-200 dark:border-neutral-700 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-neutral-800 text-xs uppercase font-medium">
                <tr>
                    <th class="px-4 py-2">Select</th>
                    <th class="px-4 py-2 text-left">Business</th>
                    <th class="px-4 py-2">Role</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
                @foreach($businesses as $biz)
                    @php
                        $checked = old('businesses') ? in_array($biz->id, old('businesses', []))
                                 : (isset($pivotRoles[$biz->id]));
                        $roleVal = old("roles.$biz->id", $pivotRoles[$biz->id] ?? 'staff');
                    @endphp
                    <tr>
                        <td class="px-4 py-2 text-center">
                            <input type="checkbox" name="businesses[]" value="{{ $biz->id }}" {{ $checked ? 'checked' : '' }}
                            class="rounded border-gray-300">
                        </td>
                        <td class="px-4 py-2">{{ $biz->name }} <span class="text-xs text-gray-400">/{{ $biz->slug }}</span></td>
                        <td class="px-4 py-2">
                            <select name="roles[{{ $biz->id }}]" class="border rounded px-2 py-1">
                                @foreach($roles as $k => $label)
                                    <option value="{{ $k }}" @selected($roleVal === $k)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @endforeach
                @if($businesses->isEmpty())
                    <tr><td colspan="3" class="px-4 py-3 text-center text-gray-500">No businesses yet.</td></tr>
                @endif
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-500">Unchecked businesses won’t be linked. Role applies only if selected.</p>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
            {{ $isEdit ? 'Update User' : 'Create User' }}
        </button>
        <a href="{{ route('users.index') }}" class="text-gray-600 hover:underline">Cancel</a>
    </div>
</div>
