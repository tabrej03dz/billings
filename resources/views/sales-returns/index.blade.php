
<x-layouts.app :title="__('Sales Returns')">

    {{-- Page Header --}}
    <div class="mb-4 flex flex-col gap-3 bg-[#BFE0E0] p-6 dark:bg-[#354A54] sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100">
                Sales Returns
            </h1>
            <p class="mt-1 text-xs text-gray-600 dark:text-neutral-300">
                Manage customer returns, approvals and stock adjustments.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('invoices.index', ['type' => 'tax']) }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-200">
                View Invoices
            </a>
        </div>
    </div>

    {{-- Messages --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Status Summary --}}
    @php
        $pageReturns = $returns->getCollection();

        $draftCount = $pageReturns->where('status', 'draft')->count();
        $approvedCount = $pageReturns->where('status', 'approved')->count();
        $pageAmount = $pageReturns->sum('total_amount');
    @endphp

    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="text-xs font-medium text-gray-500 dark:text-neutral-400">
                Total Returns
            </div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                {{ $returns->total() }}
            </div>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm dark:border-amber-900 dark:bg-amber-950/30">
            <div class="text-xs font-medium text-amber-700 dark:text-amber-300">
                Drafts (This Page)
            </div>
            <div class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-200">
                {{ $draftCount }}
            </div>
        </div>

        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-900 dark:bg-emerald-950/30">
            <div class="text-xs font-medium text-emerald-700 dark:text-emerald-300">
                Approved (This Page)
            </div>
            <div class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-200">
                {{ $approvedCount }}
            </div>
        </div>

        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 shadow-sm dark:border-blue-900 dark:bg-blue-950/30">
            <div class="text-xs font-medium text-blue-700 dark:text-blue-300">
                Return Value (This Page)
            </div>
            <div class="mt-2 text-xl font-bold text-blue-800 dark:text-blue-200">
                ₹ {{ number_format((float) $pageAmount, 2) }}
            </div>
        </div>
    </div>

    {{-- Return Table --}}
    <div class="rounded-2xl border border-gray-200 bg-[#BFE0E0] p-4 dark:border-neutral-800 dark:bg-[#354A54] sm:p-6">

        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">
                Sales Return Records
            </h2>
            <span class="text-xs text-gray-600 dark:text-neutral-300">
                {{ $returns->total() }} records
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600 dark:bg-neutral-800/60 dark:text-neutral-300">
                    <tr>
                        <th class="px-4 py-3 text-left">Return No.</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Invoice</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                    @forelse($returns as $return)
                        @php
                            $statusClass = match($return->status) {
                                'approved' => 'border-green-200 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300',
                                'draft' => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                                'cancelled' => 'border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300',
                                default => 'border-gray-200 bg-gray-50 text-gray-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200',
                            };
                        @endphp

                        <tr class="hover:bg-white/50 dark:hover:bg-neutral-800/40">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    {{ $return->return_number }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400">
                                    #{{ $return->id }}
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-neutral-200">
                                {{ $return->return_date ? \Illuminate\Support\Carbon::parse($return->return_date)->format('d M Y') : '-' }}
                            </td>

                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-neutral-200">
                                {{ $return->invoice?->invoice_number ?? '-' }}
                            </td>

                            <td class="px-4 py-3 text-gray-700 dark:text-neutral-200">
                                <div class="font-medium">
                                    {{ $return->client?->name ?? '-' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400">
                                    {{ $return->client?->mobile ?? '' }}
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold tabular-nums text-gray-900 dark:text-white">
                                ₹ {{ number_format((float) $return->total_amount, 2) }}
                            </td>

                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ ucfirst($return->status) }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('sales-returns.show', $return->id) }}"
                                   class="inline-flex items-center justify-center rounded-lg bg-[#46837d] px-3 py-2 text-xs font-semibold text-white hover:bg-[#35655f]">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-neutral-400">
                                <div class="text-base font-semibold">
                                    No sales returns found.
                                </div>
                                <p class="mt-1 text-xs">
                                    Open a Tax Invoice and choose Sales Return to create one.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $returns->links() }}
    </div>

</x-layouts.app>
