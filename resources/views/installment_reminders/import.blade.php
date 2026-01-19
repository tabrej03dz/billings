<x-layouts.app :title="__('Import Installment Reminders')">
    <div class="max-w-3xl mx-auto px-3 sm:px-4 lg:px-6 py-6 space-y-6">

        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-2xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 text-white shadow-lg">
                        <i class="fas fa-file-excel"></i>
                    </span>
                    <span>Import Reminders</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Upload Excel/CSV file to import reminders.
                </p>
            </div>

            <a href="{{ route('installment-reminders.index') }}"
               class="inline-flex items-center px-3 py-2 rounded-lg text-xs sm:text-sm font-medium
                      bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200
                      dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:border-slate-700">
                Back
            </a>
        </div>

        @if(session('error'))
            <div class="rounded-xl border border-rose-100 bg-rose-50 text-rose-800 px-4 py-3 text-sm
                        dark:bg-rose-500/10 dark:text-rose-300 dark:border-rose-500/20">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-rose-100 bg-rose-50 text-rose-800 px-4 py-3 text-sm
                        dark:bg-rose-500/10 dark:text-rose-300 dark:border-rose-500/20">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl shadow-sm border border-slate-100 bg-white dark:bg-slate-900 dark:border-slate-800 p-5 space-y-4">
            <form method="POST" action="{{ route('installment-reminders.import-store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Excel/CSV File</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv"
                           class="w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-900
                                  focus:ring-indigo-500 focus:border-indigo-500
                                  dark:bg-slate-950 dark:text-slate-100 dark:border-slate-700 dark:focus:border-indigo-400">
                    <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                        Required headings: <b>contact_number, reminder_date, reminder_time, snme_number, installment_amount, installment_date, status</b>
                    </p>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold
                                   bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm">
                        <i class="fas fa-upload mr-1.5 text-xs"></i> Import
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white dark:bg-slate-900 dark:border-slate-800 p-5">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-2">Sample Format</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs divide-y divide-slate-100 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-3 py-2 text-left">contact_number</th>
                        <th class="px-3 py-2 text-left">reminder_date</th>
                        <th class="px-3 py-2 text-left">reminder_time</th>
                        <th class="px-3 py-2 text-left">snme_number</th>
                        <th class="px-3 py-2 text-left">installment_amount</th>
                        <th class="px-3 py-2 text-left">installment_date</th>
                        <th class="px-3 py-2 text-left">status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="border-t border-slate-100 dark:border-slate-800">
                        <td class="px-3 py-2">9876543210</td>
                        <td class="px-3 py-2">2026-01-20</td>
                        <td class="px-3 py-2">10:30</td>
                        <td class="px-3 py-2">SN123</td>
                        <td class="px-3 py-2">1500</td>
                        <td class="px-3 py-2">2026-01-25</td>
                        <td class="px-3 py-2">uploaded</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.app>
