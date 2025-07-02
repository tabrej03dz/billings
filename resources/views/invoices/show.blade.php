<x-layouts.app :title="'Invoice ' . $invoice->invoice_number">
    <div class="bg-white dark:bg-neutral-900 p-6 rounded shadow-lg">
        <h2 class="text-xl font-bold mb-4">Invoice #{{ $invoice->invoice_number }}</h2>

        <p><strong>Client:</strong> {{ $invoice->client->name }}</p>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</p>
        <p><strong>Total:</strong> ₹ {{ number_format($invoice->total, 2) }}</p>

        <h3 class="mt-4 font-semibold">Items:</h3>
        <ul class="list-disc ml-5">
            @foreach($invoice->items as $item)
                <li>{{ $item->description }} - Qty: {{ $item->quantity }}, Rate: ₹{{ $item->rate }}</li>
            @endforeach
        </ul>
    </div>
</x-layouts.app>
