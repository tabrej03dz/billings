<x-layouts.app :title="isset($user) && $user->id ? __('Edit User') : __('Create User')">
    @php $isEdit = isset($user) && $user->id; @endphp

    <div class="max-w-4xl mx-auto py-10">
        <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 shadow-xl rounded-2xl p-8">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white mb-8">
                {{ $isEdit ? 'Edit User' : 'Create New User' }}
            </h2>

            @if ($errors->any())
                <div class="mb-6 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 p-4 rounded-lg">
                    <ul class="space-y-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ $isEdit ? route('users.update', $user->id) : route('users.store') }}"
                  method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @csrf

                <!-- Name -->
                <div class="col-span-1">
                    <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Name</label>
                    <input type="text" name="name" id="name" required
                           value="{{ old('name', $user->name ?? '') }}"
                           class="w-full rounded-xl border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white px-4 py-2 shadow-sm focus:ring-blue-500 focus:outline-none">
                </div>

                <!-- Email -->
                <div class="col-span-1">
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" name="email" id="email" required
                           value="{{ old('email', $user->email ?? '') }}"
                           class="w-full rounded-xl border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white px-4 py-2 shadow-sm focus:ring-blue-500 focus:outline-none">
                </div>

                <!-- Password -->
                <div class="col-span-1">
                    <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        {{ $isEdit ? 'New Password (optional)' : 'Password' }}
                    </label>
                    <input type="password" name="password" id="password"
                           class="w-full rounded-xl border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white px-4 py-2 shadow-sm focus:ring-blue-500 focus:outline-none">
                </div>

                <!-- Confirm Password -->
                <div class="col-span-1">
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="w-full rounded-xl border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white px-4 py-2 shadow-sm focus:ring-blue-500 focus:outline-none">
                </div>

                <div class="col-span-1">
                    <label for="role" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Role</label>
                    <select name="role" id="role"
                            class="w-full rounded-xl border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white px-4 py-2 shadow-sm focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Select Role --</option>
                        <option value="admin"
                            {{ old('role', isset($user) ? $user->getRoleNames()->first() : '') == 'admin' ? 'selected' : '' }}>
                            Admin
                        </option>
                        <option value="user"
                            {{ old('role', isset($user) ? $user->getRoleNames()->first() : '') == 'user' ? 'selected' : '' }}>
                            User
                        </option>
                        <!-- Add more roles as needed -->
                    </select>
                </div>


                @role('super admin')
                <!-- Business Selection -->
                <div class="col-span-1 ">
                    <label for="business_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Business</label>
                    <select name="business_id" id="business_id"
                            class="w-full rounded-xl border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white px-4 py-2 shadow-sm focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Select Business --</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}"
                                {{ old('business_id', $user->business_id ?? '') == $business->id ? 'selected' : '' }}>
                                {{ $business->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endrole

                <!-- Submit Button -->
                <div class="col-span-1 sm:col-span-2 flex justify-start pt-4">
                    <button type="submit"
                            class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white font-semibold px-6 py-2 rounded-xl shadow hover:from-indigo-600 hover:to-blue-700 transition">
                        {{ $isEdit ? 'Update User' : 'Create User' }}
                    </button>
                    <a href="{{ route('users.index') }}"
                       class="ml-4 text-gray-600 dark:text-gray-300 hover:underline font-medium transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
