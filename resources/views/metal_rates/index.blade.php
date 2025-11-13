<x-layouts.app :title="__('Metal Rates')">
    <div class="flex flex-col gap-4">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- Header + Actions --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Metal Rates
            </h1>

            <div class="flex items-center gap-2">
                {{-- Filters --}}
                <form method="GET" class="flex flex-wrap items-center gap-2">

                    {{-- Date From --}}
                    <input type="date" name="from_date" value="{{ $from_date }}"
                           class="border rounded px-3 py-2 text-sm w-40"
                           placeholder="From date">

                    {{-- Date To --}}
                    <input type="date" name="to_date" value="{{ $to_date }}"
                           class="border rounded px-3 py-2 text-sm w-40"
                           placeholder="To date">

                    {{-- Metal Type --}}
                    <select name="metal_type" class="border rounded px-2 py-2 text-sm">
                        <option value="">All Metals</option>
                        <option value="gold" @selected($metal_type === 'gold')>Gold</option>
                        <option value="silver" @selected($metal_type === 'silver')>Silver</option>
                    </select>

                    {{-- Purity --}}
                    <input type="text" name="purity" value="{{ $purity }}"
                           placeholder="Purity (e.g. 22K, 999)"
                           class="border rounded px-3 py-2 text-sm w-40" />

                    {{-- Active --}}
                    <select name="active" class="border rounded px-2 py-2 text-sm">
                        <option value="">Any Status</option>
                        <option value="1" @selected($active === '1')>Active</option>
                        <option value="0" @selected($active === '0')>Inactive</option>
                    </select>

                    <button class="px-3 py-2 text-sm rounded bg-gray-100 hover:bg-gray-200">
                        Filter
                    </button>

                    @if(($from_date ?? '') !== '' ||
                        ($to_date ?? '') !== '' ||
                        ($metal_type ?? '') !== '' ||
                        ($purity ?? '') !== '' ||
                        ($active ?? '') !== '')
                        <a href="{{ route('metal-rates.index') }}"
                           class="text-sm text-gray-600 hover:underline">
                            Clear
                        </a>
                    @endif
                </form>

                {{-- Add New --}}
                <a href="{{ route('metal-rates.create') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    + New Rate
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-medium tracking-wider">
                <tr>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3">Metal</th>
                    <th class="px-6 py-3">Purity</th>
                    <th class="px-6 py-3">Rate / gram</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Created At</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                @forelse ($rates as $rate)
                    <tr>
                        {{-- Date --}}
                        <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($rate->rate_date)->format('d-m-Y') }}
                        </td>

                        {{-- Metal --}}
                        <td class="px-6 py-3">
                            @if($rate->metal_type === 'gold')
                                <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">
                                    Gold
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-slate-100 text-slate-800">
                                    Silver
                                </span>
                            @endif
                        </td>

                        {{-- Purity --}}
                        <td class="px-6 py-3">
                            {{ $rate->purity ?? '—' }}
                        </td>

                        {{-- Rate per gram --}}
                        <td class="px-6 py-3">
                            ₹ {{ number_format($rate->rate_per_gram, 2) }}
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-3">
                            @if($rate->is_active)
                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                    Active
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">
                                    Inactive
                                </span>
                            @endif
                        </td>

                        {{-- Created at --}}
                        <td class="px-6 py-3 text-xs text-gray-500">
                            {{ $rate->created_at?->format('d-m-Y H:i') ?? '—' }}
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-3 space-x-2 whitespace-nowrap">
                            {{-- Edit --}}
                            <a href="{{ route('metal-rates.edit', $rate->id) }}"
                               class="text-yellow-600 hover:underline">
                                Edit
                            </a>

                            {{-- Toggle Active --}}
                            <form action="{{ route('metal-rates.toggle', $rate->id) }}"
                                  method="POST"
                                  class="inline-block">
                                @csrf
                                <button type="submit"
                                        class="text-xs px-2 py-1 rounded border
                                        @if($rate->is_active)
                                            border-gray-300 text-gray-700 hover:bg-gray-100
                                        @else
                                            border-green-400 text-green-700 hover:bg-green-50
                                        @endif">
                                    @if($rate->is_active)
                                        Deactivate
                                    @else
                                        Activate
                                    @endif
                                </button>
                            </form>

                            {{-- Delete --}}
                            <form action="{{ route('metal-rates.destroy', $rate->id) }}"
                                  method="POST"
                                  class="inline-block"
                                  onsubmit="return confirm('Delete this rate?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-sm">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No metal rates found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $rates->links() }}
        </div>
    </div>
</x-layouts.app>
