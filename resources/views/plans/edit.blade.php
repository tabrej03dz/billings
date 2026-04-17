<x-layouts.app :title="__('Edit Plan')">
    <div class="max-w-5xl mx-auto flex flex-col gap-4">

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
                <strong class="block mb-2">Please fix the following errors:</strong>
                <ul class="list-disc ml-5 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Plan</h1>

            <a href="{{ route('plans.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                Back
            </a>
        </div>

        <form action="{{ route('plans.update', $plan->id) }}" method="POST"
              class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-white">Plan Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $plan->name) }}"
                           class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-white">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $plan->slug) }}"
                           class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-white">Price <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $plan->price) }}"
                           class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-white">Duration Days <span class="text-red-500">*</span></label>
                    <input type="number" name="duration_days" value="{{ old('duration_days', $plan->duration_days) }}"
                           class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 dark:text-white">Description</label>
                <textarea name="description" rows="4"
                          class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">{{ old('description', $plan->description) }}</textarea>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" id="status" name="status" value="1"
                       {{ old('status', $plan->status) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-green-600 shadow-sm">
                <label for="status" class="text-sm font-medium dark:text-white">Active</label>
            </div>

            <div>
                <label class="block text-sm font-medium mb-3 dark:text-white">Assign Permissions</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 border rounded-xl p-4 dark:border-neutral-700">
                    @foreach($permissions as $permission)
                        <label class="flex items-center gap-2 text-sm dark:text-gray-200">
                            <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}"
                                   {{ in_array($permission->id, old('permission_ids', $selectedPermissions ?? [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm">
                            <span>{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="px-5 py-2.5 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700">
                    Update Plan
                </button>

                <a href="{{ route('plans.index') }}"
                   class="px-5 py-2.5 rounded-lg bg-gray-500 text-white font-medium hover:bg-gray-600">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.app>