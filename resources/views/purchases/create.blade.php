<x-layouts.app :title="__('New Purchase')">
    <form action="{{ route('purchases.store') }}" method="POST" class="space-y-4">
        @csrf

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

        <div class="flex justify-end gap-2 max-w-3xl mx-auto">
            <a href="{{ route('purchases.index') }}" class="px-3 py-2 border rounded">Cancel</a>
            <button class="px-4 py-2 rounded bg-green-600 text-white">Save Purchase</button>
        </div>
    </form>
</x-layouts.app>
