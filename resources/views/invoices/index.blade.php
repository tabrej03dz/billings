<x-layouts.app :title="__('Invoices')">
    @php
        $activeType = request('type', $type ?? 'tax'); // tax/proforma/quotation
        $activeType = in_array($activeType, ['tax','proforma','quotation'], true) ? $activeType : 'tax';
    @endphp


    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4 bg-[#BFE0E0] dark:bg-[#354A54] p-6">
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
               class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-[#D7B059] text-white text-sm font-medium hover:bg-amber-500">
                + Proforma
            </a>
            @endcan
            @can('create quotation')
            <a href="{{ route('invoices.create', 'quotation') }}"
               class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-cyan-600 text-white text-sm font-medium hover:bg-cyan-700">
                + quotation
            </a>
                @endcan

            @can('create invoice')
                <a href="{{ route('invoices.create', 'tax') }}"
                class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-[#46837d] text-white text-sm font-medium hover:bg-[#46837d]">
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
            : 'bg-[#46837d] dark:bg-[#46837d] text-gray-50 dark:text-neutral-200 border-gray-200 dark:border-neutral-700 hover:bg-[#46837d] dark:hover:bg-[#46837d]' }}">
            Tax Invoices
            <span class="text-[11px] px-2 py-0.5 rounded-full border
            {{ $activeType === 'tax' ? 'border-white/30 bg-white/10' : 'border-gray-200 dark:border-neutral-700 bg-neutral-800 dark:bg-neutral-800' }}">
            {{ $taxCount ?? '' }}
        </span>
        </a>
        @endcan

        @can('show proformas')

        <a href="{{ route('invoices.index', array_merge(request()->except('page'), ['type' => 'proforma'])) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold border flex items-center gap-2
       {{ $activeType === 'proforma'
            ? 'bg-indigo-600 text-white border-indigo-600'
            : 'bg-[#D7B059] dark:bg-[#D7B059] text-gray-50 dark:text-neutral-200 border-gray-200 dark:border-neutral-700 hover:bg-gray-50 dark:hover:bg-[#D7B059]' }}">
            Proforma
            <span class="text-[11px] px-2 py-0.5 rounded-full border
            {{ $activeType === 'proforma' ? 'border-white/30 bg-white/10' : 'border-gray-200 dark:border-neutral-700 bg-neutral-900 dark:bg-neutral-800' }}">
            {{ $proCount ?? '' }}
        </span>
        </a>
            @endcan

            @can('show quotations')
                {{-- ✅ NEW: Quotation tab --}}
                <a href="{{ route('invoices.index', array_merge(request()->except('page'), ['type' => 'quotation'])) }}"
                   class="px-4 py-2 rounded-lg text-sm font-semibold border flex items-center gap-2
               {{ $activeType === 'quotation'
                    ? 'bg-cyan-600 text-white border-cyan-600'
                    : 'bg-white dark:bg-cyan-600 text-gray-700 dark:text-neutral-200 border-gray-200 dark:border-neutral-700 hover:bg-gray-50 dark:hover:bg-cyan-800' }}">
                    Quotation
                    <span class="text-[11px] px-2 py-0.5 rounded-full border
                    {{ $activeType === 'quotation' ? 'border-white/30 bg-white/10' : 'border-gray-200 dark:border-neutral-700 bg-neutral-900 dark:bg-neutral-800' }}">
                    {{ $quoCount ?? '' }}
                </span>
                </a>
            @endcan
    </div>


    {{-- ✅ Filters --}}
    <form method="GET"
          action="{{ route('invoices.index') }}"
          class="mb-4 grid gap-3 md:grid-cols-5 bg-gray-100 dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg p-3">

        {{-- ✅ keep tab type on filter submit --}}
        <input type="hidden" name="type" value="{{ $activeType }}">

        {{-- Search --}}
        <div class="md:col-span-2 ">
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
               class="inline-flex items-center px-3 py-1.5 rounded-md border border-red-300 dark:border-red-700 text-xs font-medium text-red-700 dark:text-red-600 hover:bg-gray-50 dark:hover:bg-grey-400">
                Reset
            </a>

            <div class="flex items-center gap-2 text-green-600 font-bold dark:text-green-400">
                {{-- <a href="{{ route('invoices.export', array_merge(request()->query(), ['type' => $activeType])) }}">

                📄 Download Full Report
                </a> --}}

                <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-md bg-amber-600 text-white text-sm font-medium hover:bg-amber-700">
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

    <div x-data="paymentInModal()">

        <div class="overflow-hidden border border-gray-200 dark:border-neutral-800 rounded-2xl bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <div class="overflow-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-neutral-800/60 text-xs uppercase tracking-wide text-gray-600 dark:text-neutral-300">
                    <tr>
                        <th class="px-4 py-3 text-left">Invoice</th>
                        <th class="px-4 py-3 text-left">Date</th>
    {{--                    <th class="px-4 py-3 text-left">Client</th>--}}
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Received</th>
                        <th class="px-4 py-3 text-right">Balance</th>
                        <th class="px-4 py-3 text-left">Audit</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-neutral-800/40">

                            {{-- Invoice --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 dark:text-white">
                                            {{ $inv->invoice_number }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-neutral-400 truncate">
                                            {{ $inv->client->name ?? '-' }}
                                        </div>
                                    </div>

                                    @php
                                        $type = $inv->invoice_type;
                                        $badge = match($type){
                                            'proforma' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-200 dark:border-indigo-800',
                                            'quotation'=> 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-200 dark:border-amber-800',
                                            default    => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-200 dark:border-blue-800',
                                        };
                                    @endphp

                                    <span class="shrink-0 text-[10px] px-2 py-0.5 rounded-full border {{ $badge }}">
                                    {{ strtoupper($type) }}
                                </span>
                                </div>
                            </td>

                            {{-- Invoice Date --}}
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-neutral-200">
                                {{ optional($inv->invoice_date)->format('d M Y') }}
                            </td>

                            {{-- Client --}}
    {{--                        <td class="px-4 py-3 text-gray-700 dark:text-neutral-200">--}}
    {{--                            {{ $inv->client->name ?? '-' }}--}}
    {{--                        </td>--}}

                            {{-- Amounts --}}
                            <td class="px-4 py-3 text-right font-medium tabular-nums">
                                ₹ {{ number_format((float)$inv->total, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-emerald-700 dark:text-emerald-300">
                                ₹ {{ number_format((float)$inv->received_amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-rose-700 dark:text-rose-300">
                                ₹ {{ number_format((float)$inv->balance, 2) }}
                            </td>

                            {{-- Audit --}}
                            <td class="px-4 py-3">
                                <div class="space-y-2 text-xs">

                                    {{-- Created --}}
                                    <div class="flex gap-2">

                                        <div>
                                            <div class="font-medium text-gray-800 dark:text-neutral-200">
                                                <span class="mt-0.5 px-1.5 py-0.5 rounded border text-[10px]
                                                    border-gray-200 dark:border-neutral-700 text-gray-500 dark:text-neutral-400">
                                                    Created:
                                                </span> &nbsp;
                                                {{ $inv->createdBy?->name ?? 'N/A' }}
                                            </div>
                                            <div class="text-gray-500 dark:text-neutral-400">
                                                {{ optional($inv->created_at)->format('d M Y, h:i A') }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Updated --}}
                                    <div class="flex gap-2">

                                        <div>

                                            <div class="font-medium text-gray-800 dark:text-neutral-200">
                                                <span class="mt-0.5 px-1.5 py-0.5 rounded border text-[10px]
                                                    border-gray-200 dark:border-neutral-700 text-gray-500 dark:text-neutral-400">
                                                    Updated:
                                                </span> &nbsp;
                                                {{ $inv->updatedBy?->name ?? 'N/A' }}
                                            </div>
                                            <div class="text-gray-500 dark:text-neutral-400">
                                                {{ optional($inv->updated_at)->format('d M Y, h:i A') }}
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 text-right">
                                <div class="relative inline-block" x-data="{open:false}">
                                    <button @click="open=!open"
                                            class="w-9 h-9 rounded-lg border border-gray-200 dark:border-neutral-700
                                               hover:bg-gray-50 dark:hover:bg-neutral-800">
                                        ⋮
                                    </button>

                                    <div x-show="open" @click.outside="open=false" x-transition
                                         class="absolute right-0 mt-2 w-52 rounded-xl border
                                            border-gray-200 dark:border-neutral-700
                                            bg-white dark:bg-neutral-900 shadow-lg z-50">

                                        <a href="{{ route('invoices.show',$inv->id) }}"
                                           class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-neutral-800">
                                            View
                                        </a>

                                        @can('edit invoice')
                                        <a href="{{ route('invoices.edit',$inv->id) }}"
                                           class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 dark:hover:bg-neutral-800">
                                            Edit
                                        </a>
                                        @endcan




                                        {{-- ✅ Convert to Tax Invoice Button --}}
                                        @if(in_array(strtolower($inv->invoice_type), ['quotation','proforma']))

                                        <form action="{{ route('invoices.convertToTax', $inv->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to convert this to Tax Invoice?');"
                                            class="inline-block">

                                            @csrf

                                            <button type="submit"
                                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg
                                                    bg-[#46837d] text-white text-sm font-semibold
                                                    hover:bg-[#35655f] transition shadow">

                                                {{-- icon --}}
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 17v-6h13M9 5v6h13M5 5h.01M5 17h.01"/>
                                                </svg>

                                                Convert to Tax Invoice
                                            </button>

                                        </form>

                                        @endif







                                        <a href="{{ route('invoices.download',$inv->id) }}"
                                           class="block px-4 py-2 text-sm text-emerald-700 dark:text-emerald-300
                                              hover:bg-gray-50 dark:hover:bg-neutral-800">
                                            Download
                                        </a>

                                        <a href="{{ route('invoices.send',$inv->id) }}"
                                           class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-neutral-800">
                                            Send to WhatsApp
                                        </a>

                                        <div class="h-px bg-gray-100 dark:bg-neutral-800"></div>

                                        {{-- Delete --}}
                                        @can('delete invoice')
                                            <form method="POST" action="{{ route('invoices.destroy',$inv->id) }}"
                                                  onsubmit="return confirm('Delete this invoice?')">
                                                @csrf @method('DELETE')
                                                <button class="w-full text-left px-4 py-2 text-sm text-red-600
                                                       hover:bg-gray-50 dark:hover:bg-neutral-800">
                                                    Delete
                                                </button>
                                            </form>
                                        @endcan
                                        <button type="button"
                                                @click="$dispatch('open-payment-in', {
                                                    id: {{ $inv->id }},
                                                    invoice: @js($inv->invoice_number),
                                                    balance: {{ (float)$inv->balance }},
                                                    received: {{ (float)$inv->received_amount }},
                                                    total: {{ (float)$inv->total }}
                                                })"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-neutral-800">
                                            Payment In
                                        </button>

                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-neutral-400">
                                No invoices found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $invoices->links() }}
        </div>

        {{-- ✅ Payment In Modal --}}
        <div
            x-on:open-payment-in.window="open($event.detail)"
        >
            <div x-show="show" x-transition
                 class="fixed inset-0 z-[9999] flex items-center justify-center">
                {{-- backdrop --}}
                <div class="absolute inset-0 bg-black/50" @click="close()"></div>

                {{-- modal --}}
                <div class="relative w-full max-w-md mx-3 rounded-2xl bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 shadow-xl">
                    <div class="p-4 border-b border-gray-100 dark:border-neutral-800 flex items-center justify-between">
                        <div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white">Payment In</div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400" x-text="`Invoice: ${invoiceNo}`"></div>
                        </div>
                        <button type="button" @click="close()"
                                class="w-9 h-9 rounded-lg border border-gray-200 dark:border-neutral-700 hover:bg-gray-50 dark:hover:bg-neutral-800">
                            ✕
                        </button>
                    </div>

                    <form method="POST" :action="actionUrl" class="p-4 space-y-4">
                        @csrf

                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <div class="p-2 rounded-lg bg-gray-50 dark:bg-neutral-800">
                                <div class="text-gray-500 dark:text-neutral-400">Total</div>
                                <div class="font-semibold text-gray-900 dark:text-white" x-text="money(total)"></div>
                            </div>
                            <div class="p-2 rounded-lg bg-gray-50 dark:bg-neutral-800">
                                <div class="text-gray-500 dark:text-neutral-400">Received</div>
                                <div class="font-semibold text-emerald-700 dark:text-emerald-300" x-text="money(received)"></div>
                            </div>
                            <div class="p-2 rounded-lg bg-gray-50 dark:bg-neutral-800">
                                <div class="text-gray-500 dark:text-neutral-400">Balance</div>
                                <div class="font-semibold text-rose-700 dark:text-rose-300" x-text="money(balance)"></div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                                Amount to add
                            </label>
                            <input type="number" step="0.01" min="0.01" name="amount" x-model.number="amount" required
                                   class="w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 focus:ring-blue-500 focus:border-blue-500">
                            <div class="mt-1 text-[11px] text-gray-500 dark:text-neutral-400">
                                New balance will be updated after submit.
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button type="button" @click="close()"
                                    class="px-4 py-2 rounded-lg border border-gray-300 dark:border-neutral-700 text-sm hover:bg-gray-50 dark:hover:bg-neutral-800">
                                Cancel
                            </button>

                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                                Save Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function paymentInModal() {
                return {
                    show: false,
                    invoiceId: null,
                    invoiceNo: '',
                    total: 0,
                    received: 0,
                    balance: 0,
                    amount: null,

                    get actionUrl() {
                        // /invoices/{id}/payment-in
                        return `{{ url('invoices') }}/${this.invoiceId}/payment-in`;
                    },

                    open(d) {
                        this.invoiceId = d.id;
                        this.invoiceNo = d.invoice || '';
                        this.total = Number(d.total || 0);
                        this.received = Number(d.received || 0);
                        this.balance = Number(d.balance || 0);
                        this.amount = null;
                        this.show = true;
                    },

                    close() {
                        this.show = false;
                    },

                    money(v) {
                        let n = Number(v || 0);
                        return '₹ ' + n.toFixed(2);
                    }
                }
            }
        </script>

    </div>

</x-layouts.app>
