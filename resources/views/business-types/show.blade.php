<x-layouts.app :title="__('Business Type Details')">
    <div class="flex flex-col gap-4">

        <div class="flex items-center justify-between mb-4 bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Business Type Details</h1>

            <a href="{{ route('business-types.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                Back
            </a>
        </div>

        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $businessType->name }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Slug</p>
                    <p class="font-semibold text-gray-900 dark:text-white">/{{ $businessType->slug }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    @if($businessType->status)
                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Active</span>
                    @else
                        <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Inactive</span>
                    @endif
                </div>

                <div>
                    <p class="text-sm text-gray-500">Total Fields</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $businessType->itemFields->count() }}</p>
                </div>
            </div>

            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-3">
                Selected Item Fields
            </h2>

            <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                    <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Sort</th>
                            <th class="px-6 py-3">Field Name</th>
                            <th class="px-6 py-3">Label</th>
                            <th class="px-6 py-3">Required</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                        @forelse($businessType->itemFields->sortBy('sort_order') as $field)
                            <tr>
                                <td class="px-6 py-4">{{ $field->sort_order }}</td>
                                <td class="px-6 py-4">{{ $field->field_name }}</td>
                                <td class="px-6 py-4">{{ $field->label }}</td>
                                <td class="px-6 py-4">
                                    @if($field->is_required)
                                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Yes</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">No</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    No fields selected.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <a href="{{ route('business-types.edit', $businessType->id) }}"
                   class="px-5 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                    Edit Business Type
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>