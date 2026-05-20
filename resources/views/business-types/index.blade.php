<x-layouts.app :title="__('Business Types')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between mb-4 bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Business Types</h1>

            <a href="{{ route('business-types.create') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-500 rounded-lg hover:bg-green-700">
                + New Business Type
            </a>
        </div>

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Slug</th>
                        <th class="px-6 py-3">Fields</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                    @forelse ($businessTypes as $businessType)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $businessType->name }}
                            </td>

                            <td class="px-6 py-4">
                                /{{ $businessType->slug }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $businessType->item_fields_count ?? $businessType->itemFields->count() }}
                            </td>

                            <td class="px-6 py-4">
                                @if($businessType->status)
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Inactive</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 space-x-2">
                                <a href="{{ route('business-types.show', $businessType->id) }}"
                                   class="bg-blue-600 text-white p-2 hover:underline m-3">
                                    View
                                </a>

                                <a href="{{ route('business-types.edit', $businessType->id) }}"
                                   class="bg-yellow-500 text-white p-2 hover:underline m-3">
                                    Edit
                                </a>

                                <form action="{{ route('business-types.destroy', $businessType->id) }}"
                                      method="POST"
                                      class="inline-block"
                                      onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="bg-red-600 p-2 m-3 text-white hover:underline">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No business types found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $businessTypes->withQueryString()->links() }}
        </div>
    </div>
</x-layouts.app>