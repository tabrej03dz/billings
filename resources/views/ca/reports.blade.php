
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">CA Invoice Reports</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-neutral-300">Assigned businesses me se business choose karke filtered report preview/download karein.</p>
            </div>
            <div class="rounded-xl bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700">{{ $business->name }}</div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <form method="GET" action="{{ route('ca.reports.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-7">
                <div class="xl:col-span-2">
                    <label class="mb-1 block text-xs font-medium">Business</label>
                    <select name="business_id" class="w-full rounded-xl border-gray-300 text-sm dark:border-neutral-700 dark:bg-neutral-800">
                        @foreach($businesses as $item)
                            <option value="{{ $item->id }}" @selected((int)$filters['business_id'] === (int)$item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium">Report Type</label>
                    <select name="type" class="w-full rounded-xl border-gray-300 text-sm dark:border-neutral-700 dark:bg-neutral-800">
                        <option value="tax" @selected($activeType === 'tax')>Tax</option>
                        <option value="proforma" @selected($activeType === 'proforma')>Proforma</option>
                        <option value="quotation" @selected($activeType === 'quotation')>Quotation</option>
                    </select>
                </div>
                <div class="xl:col-span-2">
                    <label class="mb-1 block text-xs font-medium">Search</label>
                    <input name="search" value="{{ $filters['search'] }}" placeholder="Invoice, client, mobile, GSTIN, PAN..." class="w-full rounded-xl border-gray-300 text-sm dark:border-neutral-700 dark:bg-neutral-800">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium">Status</label>
                    <select name="status" class="w-full rounded-xl border-gray-300 text-sm dark:border-neutral-700 dark:bg-neutral-800">
                        <option value="">All</option><option value="paid" @selected($filters['status']==='paid')>Paid</option><option value="partial" @selected($filters['status']==='partial')>Partial</option><option value="unpaid" @selected($filters['status']==='unpaid')>Unpaid</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium">Date Range</label>
                    <select name="date_range" id="ca_date_range" class="w-full rounded-xl border-gray-300 text-sm dark:border-neutral-700 dark:bg-neutral-800">
                        <option value="last_month" @selected($filters['date_range']==='last_month')>Last Month</option>
                        <option value="quarter" @selected($filters['date_range']==='quarter')>Last Quarter</option>
                        <option value="half_year" @selected($filters['date_range']==='half_year')>Last Half-Year</option>
                        <option value="last_year" @selected($filters['date_range']==='last_year')>Last Year</option>
                        <option value="all" @selected($filters['date_range']==='all')>All Time</option>
                        <option value="custom" @selected($filters['date_range']==='custom')>Custom</option>
                    </select>
                </div>
                <div id="ca_from_wrap">
                    <label class="mb-1 block text-xs font-medium">From</label>
                    <input type="date" name="from_date" value="{{ $filters['from_date'] }}" class="w-full rounded-xl border-gray-300 text-sm dark:border-neutral-700 dark:bg-neutral-800">
                </div>
                <div id="ca_to_wrap">
                    <label class="mb-1 block text-xs font-medium">To</label>
                    <input type="date" name="to_date" value="{{ $filters['to_date'] }}" class="w-full rounded-xl border-gray-300 text-sm dark:border-neutral-700 dark:bg-neutral-800">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium">Format</label>
                    <select name="file_format" class="w-full rounded-xl border-gray-300 text-sm dark:border-neutral-700 dark:bg-neutral-800"><option value="excel">Excel</option><option value="pdf" @selected($filters['file_format']==='pdf')>PDF</option></select>
                </div>
                <div class="flex items-end gap-2 md:col-span-2 xl:col-span-4">
                    <button class="rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white">Apply Filters</button>
                    <button formaction="{{ route('ca.reports.download') }}" class="rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white">Download Report</button>
                    <a href="{{ route('ca.reports.index') }}" class="rounded-xl border px-4 py-2.5 text-sm font-semibold">Reset</a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900"><div class="text-xs text-gray-500">Invoices</div><div class="mt-1 text-xl font-bold">{{ number_format($summary['invoice_count']) }}</div></div>
            <div class="rounded-2xl border bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900"><div class="text-xs text-gray-500">Total</div><div class="mt-1 text-xl font-bold">₹{{ number_format($summary['total'],2) }}</div></div>
            <div class="rounded-2xl border bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900"><div class="text-xs text-gray-500">Received</div><div class="mt-1 text-xl font-bold">₹{{ number_format($summary['received'],2) }}</div></div>
            <div class="rounded-2xl border bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900"><div class="text-xs text-gray-500">Balance</div><div class="mt-1 text-xl font-bold">₹{{ number_format($summary['balance'],2) }}</div></div>
        </div>

        <div class="overflow-hidden rounded-2xl border bg-white dark:border-neutral-800 dark:bg-neutral-900">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-neutral-800"><tr><th class="px-4 py-3">Date</th><th class="px-4 py-3">Invoice</th><th class="px-4 py-3">Client</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3 text-right">Received</th><th class="px-4 py-3 text-right">Balance</th></tr></thead>
                    <tbody class="divide-y dark:divide-neutral-800">
                        @forelse($rows as $row)
                            <tr><td class="px-4 py-3">{{ optional($row->invoice_date)->format('d-m-Y') }}</td><td class="px-4 py-3 font-medium">{{ $row->invoice_number }}</td><td class="px-4 py-3">{{ $row->client?->name ?? '-' }}<div class="text-xs text-gray-500">{{ $row->client?->mobile }}</div></td><td class="px-4 py-3 text-right">{{ number_format((float)$row->total,2) }}</td><td class="px-4 py-3 text-right">{{ number_format((float)$row->received_amount,2) }}</td><td class="px-4 py-3 text-right">{{ number_format((float)$row->balance,2) }}</td></tr>
                        @empty<tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">Selected filters me koi invoice nahi mila.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t px-4 py-3 dark:border-neutral-800">{{ $rows->links() }}</div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const range = document.getElementById('ca_date_range');
            const from = document.getElementById('ca_from_wrap');
            const to = document.getElementById('ca_to_wrap');
            const sync = () => { const show = range.value === 'custom'; from.style.display = show ? 'block' : 'none'; to.style.display = show ? 'block' : 'none'; };
            sync(); range.addEventListener('change', sync);
        });
    </script>
</x-layouts.app>
