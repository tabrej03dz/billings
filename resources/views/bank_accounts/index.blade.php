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

        <div class="flex items-center justify-between mb-2">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Bank Accounts</h1>
                <div class="text-xs text-gray-500">Business: {{ $businessRow->name }} / {{ $businessRow->slug }}</div>
            </div>

            <a href="{{ route('bank-accounts.create', $businessRow->id) }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                + New Bank
            </a>
        </div>

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-medium tracking-wider">
                <tr>
                    <th class="px-6 py-3">Label</th>
                    <th class="px-6 py-3">Account</th>
                    <th class="px-6 py-3">Bank</th>
                    <th class="px-6 py-3">UPI</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                @forelse($bankAccounts as $row)
                    <tr>
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

                        <td class="px-6 py-4">
                            @if($row->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Active</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">Inactive</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 space-x-2">
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
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No bank accounts found.</td>
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
