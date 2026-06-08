<x-layouts.app :title="__('Edit Purchase #'.($purchase->invoice_no ?? $purchase->id))">
    <form action="{{ route('purchases.update', $purchase) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="p-3 rounded border border-red-300 bg-red-50 text-red-700 text-sm">
                <ul class="list-disc ml-4">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('purchases._form', ['purchase' => $purchase, 'suppliers' => $suppliers, 'items' => $items])

        <div class="flex justify-end gap-2">
            <a href="{{ route('purchases.index') }}" class="px-3 py-2 border rounded">Cancel</a>
            <button class="px-4 py-2 rounded bg-blue-600 text-white">Update Purchase</button>
        </div>
    </form>
</x-layouts.app>
