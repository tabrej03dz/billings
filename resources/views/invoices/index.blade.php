<x-layouts.app :title="__('Invoices')">
    @php
        $activeType = request('type', $type ?? 'tax'); // tax/proforma/quotation
        $activeType = in_array($activeType, ['tax','proforma','quotation'], true) ? $activeType : 'tax';
    @endphp


    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100">
                Invoices
            </h1>
            <div class="text-xs text-gray-500 dark:text-neutral-400">
                Showing: <span class="font-semibold">{{ strtoupper($activeType) }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @can('create proforma')
            <a href="{{ route('invoices.create', 'proforma') }}"
               class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                + Proforma
            </a>
            @endcan
            @can('create quotation')
            <a href="{{ route('invoices.create', 'quotation') }}"
               class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                + quotation
            </a>
                @endcan

            @can('create invoice')
            <a href="{{ route('invoices.create', 'tax') }}"
               class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                + Tax Invoice
            </a>
            @endcan
        </div>
    </div>

    {{-- ✅ Tabs --}}
    {{-- ✅ Tabs --}}
    <div class="mb-4 flex items-center gap-2 flex-wrap">
        @can('show invoices')
        <a href="{{ route('invoices.index', array_merge(request()->except('page'), ['type' => 'tax'])) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold border flex items-center gap-2
       {{ $activeType === 'tax'
            ? 'bg-blue-600 text-white border-blue-600'
            : 'bg-white dark:bg-neutral-900 text-gray-700 dark:text-neutral-200 border-gray-200 dark:border-neutral-700 hover:bg-gray-50 dark:hover:bg-neutral-800' }}">
            Tax Invoices
            <span class="text-[11px] px-2 py-0.5 rounded-full border
            {{ $activeType === 'tax' ? 'border-white/30 bg-white/10' : 'border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800' }}">
            {{ $taxCount ?? '' }}
        </span>
        </a>
        @endcan

        @can('show proformas')

        <a href="{{ route('invoices.index', array_merge(request()->except('page'), ['type' => 'proforma'])) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold border flex items-center gap-2
       {{ $activeType === 'proforma'
            ? 'bg-indigo-600 text-white border-indigo-600'
            : 'bg-white dark:bg-neutral-900 text-gray-700 dark:text-neutral-200 border-gray-200 dark:border-neutral-700 hover:bg-gray-50 dark:hover:bg-neutral-800' }}">
            Proforma
            <span class="text-[11px] px-2 py-0.5 rounded-full border
            {{ $activeType === 'proforma' ? 'border-white/30 bg-white/10' : 'border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800' }}">
            {{ $proCount ?? '' }}
        </span>
        </a>
            @endcan

            @can('show quotations')
                {{-- ✅ NEW: Quotation tab --}}
                <a href="{{ route('invoices.index', array_merge(request()->except('page'), ['type' => 'quotation'])) }}"
                   class="px-4 py-2 rounded-lg text-sm font-semibold border flex items-center gap-2
               {{ $activeType === 'quotation'
                    ? 'bg-amber-600 text-white border-amber-600'
                    : 'bg-white dark:bg-neutral-900 text-gray-700 dark:text-neutral-200 border-gray-200 dark:border-neutral-700 hover:bg-gray-50 dark:hover:bg-neutral-800' }}">
                    Quotation
                    <span class="text-[11px] px-2 py-0.5 rounded-full border
                    {{ $activeType === 'quotation' ? 'border-white/30 bg-white/10' : 'border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800' }}">
                    {{ $quoCount ?? '' }}
                </span>
                </a>
            @endcan
    </div>


    {{-- ✅ Filters --}}
    <form method="GET"
          action="{{ route('invoices.index') }}"
          class="mb-4 grid gap-3 md:grid-cols-5 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg p-3">

        {{-- ✅ keep tab type on filter submit --}}
        <input type="hidden" name="type" value="{{ $activeType }}">

        {{-- Search --}}
        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                Search (Invoice / Client)
            </label>
            <input type="text" name="search"
                   value="{{ request('search') }}"
                   class="block w-full rounded-md border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 text-sm focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Invoice no, client name...">
        </div>

        {{-- From date --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                From date
            </label>
            <input type="date" name="from_date"
                   value="{{ request('from_date') }}"
                   class="block w-full rounded-md border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 text-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        {{-- To date --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                To date
            </label>
            <input type="date" name="to_date"
                   value="{{ request('to_date') }}"
                   class="block w-full rounded-md border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 text-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        {{-- Status --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                Status
            </label>
            <select name="status"
                    class="block w-full rounded-md border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">All</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partially Paid</option>
                <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
            </select>
        </div>

        {{-- Buttons --}}
        <div class="md:col-span-5 flex items-center justify-between gap-2 pt-1">
            <a href="{{ route('invoices.index', ['type' => $activeType]) }}"
               class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 dark:border-neutral-700 text-xs font-medium text-gray-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-800">
                Reset
            </a>

            <div class="flex items-center gap-2">
                <a href="{{ route('invoices.export', request()->query()) }}"
                   class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 text-sm font-medium">
                    📄 Download Full Report
                </a>

                <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                    Apply Filters
                </button>
            </div>
        </div>
    </form>

    @if(session('success'))
        <div class="p-2 mb-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-auto border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
        <table class="min-w-full text-sm border-separate border-spacing-0">
            <thead class="bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200">
            <tr class="[&>th]:px-4 [&>th]:py-2 [&>th]:font-medium text-left">
                <th>Invoice</th>
                <th>Date</th>
                <th>Client</th>
                <th>Total</th>
                <th>Received</th>
                <th>Balance</th>
                <th class="text-right">Actions</th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700 text-gray-900 dark:text-neutral-100">
            @forelse($invoices as $inv)
                <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800/60">
                    <td class="px-4 py-2">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold">{{ $inv->invoice_number }}</span>

                            <span class="text-[10px] px-2 py-0.5 rounded-full border
                                {{ $inv->invoice_type === 'proforma'
                                    ? 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-200 dark:border-indigo-800'
                                    : 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-200 dark:border-blue-800' }}">
                                {{ strtoupper($inv->invoice_type) }}
                            </span>
                        </div>
                    </td>

                    <td class="px-4 py-2">
                        {{ \Illuminate\Support\Carbon::parse($inv->invoice_date)->format('d M Y') }}
                    </td>

                    <td class="px-4 py-2">
                        {{ $inv->client->name ?? '-' }}
                    </td>

                    <td class="px-4 py-2">
                        ₹ {{ number_format((float)$inv->total, 2) }}
                    </td>

                    <td class="px-4 py-2">
                        ₹ {{ number_format((float)$inv->received_amount, 2) }}
                    </td>

                    <td class="px-4 py-2">
                        ₹ {{ number_format((float)$inv->balance, 2) }}
                    </td>

                    <td class="px-4 py-2 text-right space-x-3 whitespace-nowrap">
                        <a href="{{ route('invoices.show',$inv->id) }}" class="text-gray-700 dark:text-neutral-300 hover:underline">View</a>
                        <a href="{{ route('invoices.download',$inv->id) }}" class="text-emerald-600 hover:underline">Download</a>
                        {{-- ✅ Edit button (type-wise permission) --}}
                        @if($inv->invoice_type === 'tax')
                            @can('edit invoice')

                                <a href="{{ route('invoices.edit',$inv->id) }}" class="text-blue-600 hover:underline">Edit</a>
                            @endcan
                        @elseif($inv->invoice_type === 'proforma')
                            @can('edit proforma')
                                <a href="{{ route('invoices.edit',$inv->id) }}" class="text-blue-600 hover:underline">Edit</a>
                            @endcan
                        @elseif($inv->invoice_type === 'quotation')
                            @can('edit quotation')
                                <a href="{{ route('invoices.edit',$inv->id) }}" class="text-blue-600 hover:underline">Edit</a>
                            @endcan
                        @endif

                        <a href="{{ route('invoices.send',$inv->id) }}" class="text-blue-600 hover:underline">Send to Whtsp</a>

                        @can('	delete quotation')
                        @if(in_array($inv->invoice_type, ['quotation','proforma']))
                            <form method="POST" action="{{ route('invoices.convertToTax', $inv) }}" method="POST" class="inline">
                                @csrf
                                <button class="px-3 py-2 rounded bg-emerald-600 text-white">Convert to Tax Invoice</button>
                            </form>
                        @endif
                        @endcan

                        {{-- ✅ Delete button (type-wise permission) --}}
                        @if($inv->invoice_type === 'tax')
                            @can('delete invoice')
                                <form action="{{ route('invoices.destroy',$inv->id) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Delete this Tax Invoice?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline">Delete</button>
                                </form>
                            @endcan

                        @elseif($inv->invoice_type === 'proforma')
                            @can('delete proforma')
                                <form action="{{ route('invoices.destroy',$inv->id) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Delete this Proforma?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline">Delete</button>
                                </form>
                            @endcan

                        @elseif($inv->invoice_type === 'quotation')
                            @can('delete quotation')
                                <form action="{{ route('invoices.destroy',$inv->id) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Delete this Quotation?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline">Delete</button>
                                </form>
                            @endcan
                        @endif

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-3 text-center text-gray-500 dark:text-neutral-400">
                        No invoices found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $invoices->links() }}
    </div>
</x-layouts.app>
