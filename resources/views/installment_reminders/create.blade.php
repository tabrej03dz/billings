<x-layouts.app :title="__('Create Installment Reminder')">
    <div class="max-w-4xl mx-auto px-3 sm:px-4 lg:px-6 py-6 space-y-6">

        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-2xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 text-white shadow-lg">
                        <i class="fas fa-plus"></i>
                    </span>
                    <span>Create Reminder</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Add a new installment reminder.
                </p>
            </div>

            <a href="{{ route('installment-reminders.index') }}"
               class="inline-flex items-center px-3 py-2 rounded-lg text-xs sm:text-sm font-medium
                      bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200
                      dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:border-slate-700">
                Back
            </a>
        </div>

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

        <div class="rounded-2xl shadow-sm border border-slate-100 bg-white dark:bg-slate-900 dark:border-slate-800 p-5">
            <form method="POST" action="{{ route('installment-reminders.store') }}" class="grid gap-4 md:grid-cols-2">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Contact Number</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number') }}" placeholder="e.g. 9876543210"
                           class="w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-900
                                  focus:ring-indigo-500 focus:border-indigo-500
                                  dark:bg-slate-950 dark:text-slate-100 dark:border-slate-700 dark:focus:border-indigo-400">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">SNME Number (optional)</label>
                    <input type="text" name="snme_number" value="{{ old('snme_number') }}" placeholder="optional"
                           class="w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-900
                                  focus:ring-indigo-500 focus:border-indigo-500
                                  dark:bg-slate-950 dark:text-slate-100 dark:border-slate-700 dark:focus:border-indigo-400">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Reminder Date</label>
                    <input type="date" name="reminder_date" value="{{ old('reminder_date') }}"
                           class="w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-900
                                  focus:ring-indigo-500 focus:border-indigo-500
                                  dark:bg-slate-950 dark:text-slate-100 dark:border-slate-700 dark:focus:border-indigo-400">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Reminder Time</label>
                    <input type="time" name="reminder_time" value="{{ old('reminder_time') }}"
                           class="w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-900
                                  focus:ring-indigo-500 focus:border-indigo-500
                                  dark:bg-slate-950 dark:text-slate-100 dark:border-slate-700 dark:focus:border-indigo-400">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Installment Date</label>
                    <input type="date" name="installment_date" value="{{ old('installment_date') }}"
                           class="w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-900
                                  focus:ring-indigo-500 focus:border-indigo-500
                                  dark:bg-slate-950 dark:text-slate-100 dark:border-slate-700 dark:focus:border-indigo-400">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Installment Amount</label>
                    <input type="number" step="0.01" name="installment_amount" value="{{ old('installment_amount') }}" placeholder="e.g. 1500"
                           class="w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-900
                                  focus:ring-indigo-500 focus:border-indigo-500
                                  dark:bg-slate-950 dark:text-slate-100 dark:border-slate-700 dark:focus:border-indigo-400">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Status</label>
                    <select name="status"
                            class="w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-900
                                   focus:ring-indigo-500 focus:border-indigo-500
                                   dark:bg-slate-950 dark:text-slate-100 dark:border-slate-700 dark:focus:border-indigo-400">
                        <option value="uploaded" {{ old('status','uploaded') === 'uploaded' ? 'selected' : '' }}>Uploaded</option>
                        <option value="sent" {{ old('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ old('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex justify-end gap-2 pt-2">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold
                                   bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm">
                        <i class="fas fa-save mr-1.5 text-xs"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
