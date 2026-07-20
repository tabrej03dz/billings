<x-layouts.app :title="__('Items')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-3 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-red-700">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
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

                <form
                    method="POST"
                    action="{{ route('items.barcodes.generate-missing') }}"
                    onsubmit="return confirm('Generate barcodes for all items that do not have a barcode?')"
                >
                    @csrf

                    <button
                        type="submit"
                        class="rounded bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700"
                    >
                        Generate Missing Barcodes
                    </button>
                </form>


                <a href="{{ route('items.ai.create') }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                    📷 AI Photo Entry
                </a>
            </div>
        </div>

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <form
                id="barcodeBulkForm"
                method="POST"
                action="{{ route('items.barcodes.print') }}"
                target="_blank"
            >
                @csrf

                <div class="flex flex-wrap items-center gap-3 border-b border-gray-200 bg-purple-50 p-4 dark:border-gray-700 dark:bg-gray-800">

                    <div>
                        <div class="text-sm font-bold text-gray-900 dark:text-white">
                            Barcode Label Printing
                        </div>

                        <div class="text-xs text-gray-600 dark:text-gray-300">
                            Items select karein aur print button dabayein
                        </div>
                    </div>

                    <label class="ml-auto text-sm font-medium text-gray-900 dark:text-white">
                        Copies per item
                    </label>

                    <input
                        type="number"
                        name="quantity"
                        value="1"
                        min="1"
                        max="200"
                        class="w-24 rounded border border-gray-400 bg-white px-3 py-2 text-gray-900"
                    >

                    <button
                        type="submit"
                        class="rounded-md bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-purple-700"
                    >
                        🖨 Print Selected Barcodes
                    </button>

                </div>
            </form>
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium tracking-wider">
                <tr>
                    <th class="px-3 py-3">
                        <input
                            type="checkbox"
                            id="selectAllBarcodeItems"
                        >
                    </th>

                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">SKU</th>
                    <th class="px-6 py-3">Barcode</th>
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
                        <td class="px-3 py-3">
                            <input
                                class="barcode-item-checkbox"
                                type="checkbox"
                                form="barcodeBulkForm"
                                name="item_ids[]"
                                value="{{ $it->id }}"
                            >
                        </td>
                        <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">{{ $it->name }}</td>
                        <td class="px-6 py-3">{{ $it->sku ?? '—' }}</td>
                        <td class="px-6 py-3">
                            @if($it->barcode)

                                <div class="flex min-w-[170px] flex-col gap-2">
                                    <span class="font-mono text-xs text-gray-700 dark:text-gray-300">
                                        {{ $it->barcode }}
                                    </span>

                                    <a
                                        href="{{ route('items.barcode.print', [
                                            'item' => $it->id,
                                            'quantity' => 1,
                                            'print' => 1
                                        ]) }}"
                                        target="_blank"
                                        class="inline-flex w-fit items-center rounded-md bg-purple-600 px-3 py-2 text-xs font-semibold text-white hover:bg-purple-700"
                                    >
                                        🖨 Print Barcode
                                    </a>
                                </div>

                            @else

                                <div class="flex min-w-[170px] flex-col gap-2">
                                    <span class="text-xs text-red-600">
                                        Barcode not generated
                                    </span>

                                    <form
                                        action="{{ route('items.barcode.generate', $it->id) }}"
                                        method="POST"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="rounded-md bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700"
                                        >
                                            Generate Barcode
                                        </button>
                                    </form>
                                </div>

                            @endif
                        </td>
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
                        <td class="px-6 py-3">
                            <div class="flex min-w-[150px] items-center gap-2">

                                <a
                                    href="{{ route('items.edit', $it->id) }}"
                                    class="rounded bg-yellow-600 px-3 py-2 text-xs font-semibold text-white hover:bg-yellow-700"
                                >
                                    Edit
                                </a>

                                <form
                                    action="{{ route('items.destroy', $it->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Delete this item?');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="rounded bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700"
                                    >
                                        Delete
                                    </button>
                                </form>

                            </div>
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



    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAllBarcodeItems');

        if (!selectAll) {
            return;
        }

        selectAll.addEventListener('change', function () {
            document
                .querySelectorAll('.barcode-item-checkbox')
                .forEach(function (checkbox) {
                    checkbox.checked = selectAll.checked;
                });
        });

        const bulkForm = document.getElementById('barcodeBulkForm');

        bulkForm?.addEventListener('submit', function (event) {
            const selectedItems = document.querySelectorAll(
                '.barcode-item-checkbox:checked'
            );

            if (selectedItems.length === 0) {
                event.preventDefault();
                alert('Please select at least one item.');
            }
        });
    });
</script>
</x-layouts.app>
