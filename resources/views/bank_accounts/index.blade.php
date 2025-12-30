<x-layouts.app :title="__('Bank Accounts')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                <ul class="list-disc ml-5">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-1">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Bank Accounts</h1>
                <div class="text-xs text-gray-500">
                    Business: {{ $businessRow->name }} / {{ $businessRow->slug }}
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('bank-accounts.create', $businessRow->id) }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    + New Bank
                </a>
            </div>
        </div>

        {{-- ✅ Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Cash Balance</div>
                <div class="mt-1 text-xl font-bold text-gray-900 dark:text-white">
                    ₹ {{ number_format((float)($cashBalance ?? 0), 2) }}
                </div>
                <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                    (Invoice Payments → Cash)
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Total Bank Balance</div>
                <div class="mt-1 text-xl font-bold text-gray-900 dark:text-white">
                    ₹ {{ number_format((float)($totalBankBalance ?? 0), 2) }}
                </div>
                <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                    (Sum of all bank accounts)
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Grand Total (Cash + Bank)</div>
                <div class="mt-1 text-xl font-bold text-emerald-600">
                    ₹ {{ number_format(((float)($cashBalance ?? 0) + (float)($totalBankBalance ?? 0)), 2) }}
                </div>
                <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                    Overall available amount
                </div>
            </div>
        </div>

        {{-- ✅ Bank table --}}
        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-medium tracking-wider">
                <tr>
                    <th class="px-6 py-3">Label</th>
                    <th class="px-6 py-3">Account</th>
                    <th class="px-6 py-3">Bank</th>
                    <th class="px-6 py-3">UPI</th>
                    <th class="px-6 py-3">Balance</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                @forelse($bankAccounts as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800/60">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                            {{ $row->label ?: 'N/A' }}
                            @if($row->is_default)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-indigo-100 text-indigo-700">
                                    Default
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $row->account_holder ?: '—' }}</div>
                            <div class="text-xs text-gray-500">
                                A/C: {{ $row->account_no ?: '—' }}
                                @if($row->ifsc) • IFSC: {{ $row->ifsc }} @endif
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="text-sm">{{ $row->bank_name ?: '—' }}</div>
                            <div class="text-xs text-gray-500">{{ $row->branch ?: '' }}</div>
                        </td>

                        <td class="px-6 py-4">
                            {{ $row->upi_id ?: '—' }}
                        </td>

                        {{-- ✅ Balance --}}
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900 dark:text-white">
                                ₹ {{ number_format((float)($row->balance ?? 0), 2) }}
                            </div>
                            <div class="text-[11px] text-gray-500">
                                {{ $row->is_default ? 'Default account' : '' }}
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            @if($row->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Active</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">Inactive</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 space-x-2 whitespace-nowrap">
                            <a href="{{ route('bank-accounts.edit', $row->id) }}"
                               class="text-yellow-600 hover:underline">Edit</a>

                            @if(!$row->is_default)
                                <form action="{{ route('bank-accounts.default', $row->id) }}" method="POST" class="inline-block"
                                      onsubmit="return confirm('Make this default?');">
                                    @csrf
                                    <button class="text-indigo-600 hover:underline" type="submit">Make Default</button>
                                </form>
                            @endif

                            <form action="{{ route('bank-accounts.destroy', $row->id) }}" method="POST" class="inline-block"
                                  onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No bank accounts found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $bankAccounts->withQueryString()->links() }}
        </div>
    </div>
</x-layouts.app>
