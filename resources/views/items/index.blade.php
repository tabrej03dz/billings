<x-layouts.app :title="__('Items')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3  bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <h1 class="text-2xl font-bold text-black dark:text-white">Items</h1>

            <div class="flex items-center gap-2">
                <form method="GET" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="q" value="{{ $q }}"
                           placeholder="Search name / SKU / desc..."
                           class="border border-black dark:border-white rounded px-3 py-2 text-sm w-56" />

                    <select name="category_id" class="border border-black dark:border-white rounded px-2 py-2 text-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($category_id==$cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <select name="active" class="border border-black dark:border-white rounded px-2 py-2 text-sm ">
                        <option value="">Any Status</option>
                        <option value="1" @selected($active==='1')>Active</option>
                        <option value="0" @selected($active==='0')>Inactive</option>
                    </select>

                    <button class="px-3 py-2 text-sm rounded dark:bg-gray-500 dark:hover:bg-gray-400 border border-black dark:border-white">Filter</button>
                    @if($q!=='' || $category_id || $active!=='')
                        <a href="{{ route('items.index') }}" class="text-sm text-gray-900 dark:text-white border border-black dark:border-white p-2 hover:underline">Clear</a>
                    @endif
                </form>

                <a href="{{ route('item.create') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                    + New Item
                </a>


                <a href="{{ route('items.ai.create') }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                    📷 AI Photo Entry
                </a>
            </div>
        </div>

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium tracking-wider">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">SKU</th>
                    <th class="px-6 py-3">Category</th>
                    <th class="px-6 py-3">Price</th>
                    <th class="px-6 py-3">Tax %</th>
                    <th class="px-6 py-3">Stock</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                @forelse ($items as $it)
                    <tr>
                        <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">{{ $it->name }}</td>
                        <td class="px-6 py-3">{{ $it->sku ?? '—' }}</td>
                        <td class="px-6 py-3">{{ $it->category?->name ?? '—' }}</td>
                        <td class="px-6 py-3">{{ number_format($it->price,2) }}</td>
                        <td class="px-6 py-3">{{ rtrim(rtrim(number_format($it->tax_rate,2), '0'), '.') }}</td>
                        <td class="px-6 py-3">{{ $it->stock_qty }}</td>
                        <td class="px-6 py-3">
                            @if($it->is_active)
                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 space-x-2">
                            <a href="{{ route('items.edit', $it->id) }}" class="bg-yellow-600 hover:underline p-2 text-white ">Edit</a>
                            <form action="{{ route('items.destroy', $it->id) }}" method="POST" class="inline-block"
                                  onsubmit="return confirm('Delete this item?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:underline p-2 text-white ">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">No items found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>
</x-layouts.app>
