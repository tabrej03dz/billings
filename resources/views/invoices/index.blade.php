<x-layouts.app :title="__('Invoices')">
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Invoices</h1>
            @can('create invoice')
            <a href="{{ route('invoices.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-green-600 bg-blue-600 rounded-lg hover:bg-blue-700">
                + New Invoice
            </a>
            @endcan
        </div>

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-medium tracking-wider">
                <tr>
                    <th scope="col" class="px-6 py-3">Invoice No</th>
                    <th scope="col" class="px-6 py-3">Client</th>
                    <th scope="col" class="px-6 py-3">Date</th>
                    <th scope="col" class="px-6 py-3">Total</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                @forelse ($invoices as $invoice)
                    <tr>
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</td>
                        <td class="px-6 py-4">{{ $invoice->client->name }}</td>
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td>
                        <td class="px-6 py-4">₹ {{ number_format($invoice->total, 2) }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('invoices.show', $invoice->id) }}"
                               class="text-blue-600 hover:underline mr-2">View</a>
                            @can('download invoice')
                            <a href="{{ route('invoices.download', $invoice->id) }}"
                               class="text-green-600 hover:underline">Download</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No invoices found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
