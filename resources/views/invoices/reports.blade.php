<x-layouts.app :title="__('Invoice Reports')">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6 bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-2xl">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100">
                Invoice Reports
            </h1>
            <p class="text-sm text-gray-600 dark:text-neutral-300 mt-1">
                Download tax, proforma, and quotation reports with smart filters and format selection.
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-800 rounded-2xl shadow-sm p-5">
        <form method="GET" action="{{ route('invoices.reports.page') }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
            
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                    Report Type
                </label>
                <select name="type"
                        class="block w-full rounded-xl border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="tax" {{ ($activeType ?? 'tax') === 'tax' ? 'selected' : '' }}>Tax</option>
                    <option value="proforma" {{ ($activeType ?? '') === 'proforma' ? 'selected' : '' }}>Proforma</option>
                    <option value="quotation" {{ ($activeType ?? '') === 'quotation' ? 'selected' : '' }}>Quotation</option>
                </select>
            </div>

            <div class="xl:col-span-2">
                <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                    Search
                </label>
                <input type="text"
                       name="search"
                       value="{{ $filters['search'] ?? '' }}"
                       placeholder="Invoice no, client name, mobile..."
                       class="block w-full rounded-xl border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                    Date Range
                </label>
                <select name="date_range" id="date_range"
                        class="block w-full rounded-xl border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 text-sm focus:ring-blue-500 focus:border-blue-500">

                    <option value="last_month"
                        {{ ($filters['date_range'] ?? '') === 'last_month' ? 'selected' : '' }}>
                        Last Month
                    </option>

                    <option value="quarter"
                        {{ ($filters['date_range'] ?? 'quarter') === 'quarter' ? 'selected' : '' }}>
                        Last Quarter
                    </option>

                    <option value="half_year"
                        {{ ($filters['date_range'] ?? '') === 'half_year' ? 'selected' : '' }}>
                        Last Half-Year
                    </option>

                    <option value="last_year"
                        {{ ($filters['date_range'] ?? '') === 'last_year' ? 'selected' : '' }}>
                        Last Year
                    </option>

                    <option value="custom"
                        {{ ($filters['date_range'] ?? '') === 'custom' ? 'selected' : '' }}>
                        Custom Range
                    </option>
                </select>
            </div>

            <div id="fromDateWrap">
                <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                    From Date
                </label>
                <input type="date"
                       name="from_date"
                       id="from_date"
                       value="{{ $filters['from_date'] ?? '' }}"
                       class="block w-full rounded-xl border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div id="toDateWrap">
                <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                    To Date
                </label>
                <input type="date"
                       name="to_date"
                       id="to_date"
                       value="{{ $filters['to_date'] ?? '' }}"
                       class="block w-full rounded-xl border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                    Status
                </label>
                <select name="status"
                        class="block w-full rounded-xl border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All</option>
                    <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="partial" {{ ($filters['status'] ?? '') === 'partial' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="unpaid" {{ ($filters['status'] ?? '') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">
                    Download Format
                </label>
                <select name="file_format"
                        class="block w-full rounded-xl border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="excel" {{ ($filters['file_format'] ?? 'excel') === 'excel' ? 'selected' : '' }}>Excel (.xlsx)</option>
                    <option value="pdf" {{ ($filters['file_format'] ?? '') === 'pdf' ? 'selected' : '' }}>PDF (.pdf)</option>
                </select>
            </div>

            <div class="md:col-span-2 xl:col-span-6 flex flex-wrap items-center justify-between gap-3 pt-2">
                <a href="{{ route('invoices.reports.page') }}"
                   class="inline-flex items-center px-4 py-2 rounded-xl border border-red-300 text-sm font-medium text-red-700 hover:bg-red-50">
                    Reset
                </a>

                <div class="flex flex-wrap items-center gap-3">
                    {{-- Apply filters on same page --}}
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 rounded-xl bg-amber-600 text-white text-sm font-medium hover:bg-amber-700">
                        Apply Filters
                    </button>

                    {{-- Download with current form values --}}
                    <button type="submit"
                            formaction="{{ route('invoices.reports.download') }}"
                            formmethod="GET"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-700 shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 16v-8m0 8l-3-3m3 3l3-3M5 20h14" />
                        </svg>
                        Download Report
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <div class="rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-5">
            <div class="text-sm text-gray-500 dark:text-neutral-400">Selected Type</div>
            <div class="mt-2 text-lg font-bold text-gray-900 dark:text-white uppercase">
                {{ $activeType }}
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-5">
            <div class="text-sm text-gray-500 dark:text-neutral-400">Download Format</div>
            <div class="mt-2 text-lg font-bold text-gray-900 dark:text-white uppercase">
                {{ $filters['file_format'] ?? 'excel' }}
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-5">
            <div class="text-sm text-gray-500 dark:text-neutral-400">Quick Note</div>
            <div class="mt-2 text-sm text-gray-700 dark:text-neutral-300">
                Tax / Proforma / Quotation form se select karke direct Excel ya PDF download kar sakte ho.
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dateRange = document.getElementById('date_range');
            const fromWrap = document.getElementById('fromDateWrap');
            const toWrap = document.getElementById('toDateWrap');
            const fromDate = document.getElementById('from_date');
            const toDate = document.getElementById('to_date');

            function toggleCustomDates() {
                const isCustom = dateRange.value === 'custom';

                fromWrap.style.display = isCustom ? 'block' : 'none';
                toWrap.style.display = isCustom ? 'block' : 'none';

                fromDate.disabled = !isCustom;
                toDate.disabled = !isCustom;
            }

            toggleCustomDates();
            dateRange.addEventListener('change', toggleCustomDates);
        });
    </script>
</x-layouts.app>