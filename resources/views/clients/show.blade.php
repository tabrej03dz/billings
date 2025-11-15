<x-layouts.app :title="__('Client: '.$client->name)">
    <div class="space-y-6 max-w-6xl mx-auto">

        {{-- Back + heading --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <a href="{{ route('clients.index') }}"
                   class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                    ← Back to Clients
                </a>
                <h1 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">
                    {{ $client->name }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Mobile: {{ $client->mobile }}
                    @if($client->gstin) • GSTIN: {{ $client->gstin }} @endif
                    @if($client->pan) • PAN: {{ $client->pan }} @endif
                </p>
            </div>

            <a href="{{ route('clients.edit', $client->id) }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white
                      bg-blue-600 rounded-lg hover:bg-blue-700">
                Edit Client
            </a>
        </div>

        {{-- Client details card --}}
        <div class="grid md:grid-cols-3 gap-4">
            <div class="md:col-span-2 p-4 rounded-xl border border-gray-200 dark:border-gray-700
                        bg-white dark:bg-neutral-900 space-y-2">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Client Details
                </h2>
                <div class="text-sm text-gray-700 dark:text-gray-200 space-y-1">
                    <p><span class="font-medium">Name:</span> {{ $client->name }}</p>
                    <p><span class="font-medium">Mobile:</span> {{ $client->mobile }}</p>
                    @if($client->gstin)
                        <p><span class="font-medium">GSTIN:</span> {{ $client->gstin }}</p>
                    @endif
                    @if($client->pan)
                        <p><span class="font-medium">PAN:</span> {{ $client->pan }}</p>
                    @endif
                    @if($client->state)
                        <p><span class="font-medium">State:</span> {{ $client->state }}</p>
                    @endif
                    @if($client->address)
                        <p><span class="font-medium">Address:</span>
                            {{ $client->address }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Purchase summary --}}
            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700
                        bg-white dark:bg-neutral-900 space-y-3">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Purchase Summary
                </h2>
                <dl class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Total Invoices</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            {{ $summary['total_invoices'] }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Total Purchases</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            ₹ {{ number_format($summary['total_amount'], 2) }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Amount Received</dt>
                        <dd class="font-medium text-emerald-600 dark:text-emerald-400">
                            ₹ {{ number_format($summary['total_received'], 2) }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Balance Pending</dt>
                        <dd class="font-medium text-red-600 dark:text-red-400">
                            ₹ {{ number_format($summary['total_balance'], 2) }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Invoices table --}}
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Purchase History (Invoices)
                </h2>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                    <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-medium tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Invoice No.</th>
                        <th class="px-4 py-3">Items</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                        <th class="px-4 py-3 text-right">Tax</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Received</th>
                        <th class="px-4 py-3 text-right">Balance</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                    @forelse($invoices as $inv)
                        <tr>
                            <td class="px-4 py-3">
                                {{ \Carbon\Carbon::parse($inv->invoice_date)->format('d-m-Y') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $inv->invoice_prefix }}{{ $inv->invoice_number }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $inv->items_count }} item{{ $inv->items_count == 1 ? '' : 's' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                ₹ {{ number_format($inv->subtotal, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                ₹ {{ number_format($inv->tax_amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                ₹ {{ number_format($inv->total, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                ₹ {{ number_format($inv->received_amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                ₹ {{ number_format($inv->balance, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('invoices.show', $inv->id) }}"
                                   class="text-blue-600 hover:underline">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-4 text-center text-gray-500">
                                No invoices found for this client.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $invoices->links() }}
            </div>
        </div>

        {{-- Recent items (optional but useful) --}}
        <div class="space-y-3">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                Recent Items Purchased
            </h2>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                    <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-medium tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Invoice No.</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Qty</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                    @forelse($recentItems as $item)
                        <tr>
                            <td class="px-4 py-3">
                                {{ optional($item->invoice)->invoice_date
                                    ? \Carbon\Carbon::parse($item->invoice->invoice_date)->format('d-m-Y')
                                    : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($item->invoice)
                                    {{ $item->invoice->invoice_prefix }}{{ $item->invoice->invoice_number }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                {{ $item->description }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $item->quantity }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                ₹ {{ number_format($item->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">
                                No recent items found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.app>
