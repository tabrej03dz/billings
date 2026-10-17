<x-layouts.app :title="__('Edit Business Type')">
    <div class="flex flex-col gap-4">

        <div class="flex items-center justify-between mb-4 bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Business Type</h1>

            <a href="{{ route('business-types.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                Back
            </a>
        </div>

        @if($errors->any())
            <div class="p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                <ul class="list-disc ml-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('business-types.update', $businessType->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                            Business Type Name
                        </label>
                        <input type="text"
                               name="name"
                               value="{{ old('name', $businessType->name) }}"
                               class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                            Status
                        </label>
                        <select name="status"
                                class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                            <option value="1" {{ old('status', $businessType->status) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $businessType->status) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-3">
                        Select Item Fields
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach($itemColumns as $field => $label)
                            <div class="border rounded-lg p-3 dark:border-neutral-700">
                                <label class="flex items-center gap-2 text-gray-800 dark:text-gray-200">
                                    <input type="checkbox"
                                           name="fields[]"
                                           value="{{ $field }}"
                                           {{ in_array($field, old('fields', $selectedFields)) ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>

                                <label class="flex items-center gap-2 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    <input type="checkbox"
                                           name="required_fields[]"
                                           value="{{ $field }}"
                                           {{ in_array($field, old('required_fields', $requiredFields)) ? 'checked' : '' }}>
                                    <span>Required</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit"
                            class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Update Business Type
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>