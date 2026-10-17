<x-layouts.app :title="__('Banner Sliders')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3 bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Banner Sliders</h1>

            <div class="flex items-center gap-2">
                <form method="GET" class="flex items-center gap-2">
                    <input type="text" name="q" value="{{ $q ?? '' }}"
                           placeholder="Search title/subtitle..."
                           class="border rounded px-3 py-2 text-sm w-64" />

                    <button class="px-3 py-2 text-sm rounded bg-gray-500 text-white hover:bg-gray-600">
                        Search
                    </button>

                    @if(($q ?? '') !== '')
                        <a href="{{ route('banner-sliders.index') }}"
                           class="text-sm text-gray-600 hover:underline">
                            Clear
                        </a>
                    @endif
                </form>

                <a href="{{ route('banner-sliders.create') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                    + New Banner
                </a>
            </div>
        </div>

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Image</th>
                        <th class="px-6 py-3">Title</th>
                        <th class="px-6 py-3">Subtitle</th>
                        <th class="px-6 py-3">Button</th>
                        <th class="px-6 py-3">Order</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                    @forelse ($banners as $banner)
                        <tr>
                            <td class="px-6 py-3">
                                @if($banner->image)
                                    <img src="{{ asset('storage/' . $banner->image) }}"
                                         class="h-16 w-28 object-cover rounded border">
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $banner->title ?? '—' }}
                            </td>

                            <td class="px-6 py-3">
                                {{ $banner->subtitle ?? '—' }}
                            </td>

                            <td class="px-6 py-3">
                                {{ $banner->button_text ?? '—' }}
                            </td>

                            <td class="px-6 py-3">
                                {{ $banner->sort_order }}
                            </td>

                            <td class="px-6 py-3">
                                @if($banner->is_active)
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                        Active
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-3 space-x-2 whitespace-nowrap">
                                <a href="{{ route('banner-sliders.show', $banner->id) }}"
                                   class="bg-blue-500 text-white p-2 hover:underline m-1">
                                    View
                                </a>

                                <a href="{{ route('banner-sliders.edit', $banner->id) }}"
                                   class="bg-yellow-500 text-white p-2 hover:underline m-1">
                                    Edit
                                </a>

                                <form action="{{ route('banner-sliders.destroy', $banner->id) }}"
                                      method="POST"
                                      class="inline-block"
                                      onsubmit="return confirm('Delete this banner?');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="bg-red-500 text-white p-2 hover:underline m-1">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No banners found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $banners->links() }}
        </div>
    </div>
</x-layouts.app>