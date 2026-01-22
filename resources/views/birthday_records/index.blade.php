<x-layouts.app :title="__('Birthday Records')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if(session('import_errors'))
            <div class="p-3 rounded-lg bg-yellow-50 text-yellow-800 border border-yellow-200">
                <div class="font-semibold mb-1">Import Warnings:</div>
                <ul class="list-disc ml-5 text-sm space-y-1">
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3 mb-1 bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Birthday Records</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage birthdays & import via Excel.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('birthday-records.importForm') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-800 dark:text-white bg-gray-100 dark:bg-neutral-800 rounded-lg hover:bg-gray-200 dark:hover:bg-neutral-700 border border-gray-200 dark:border-gray-700">
                    Upload Excel
                </a>

                <a href="{{ route('birthday-records.create') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                    + New Record
                </a>
            </div>
        </div>

        {{-- Filters --}}
            {{-- Filters --}}
         <div class="bg-slate-200 dark:bg-transparent">
               <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-neutral-900 ">

                <div class="md:col-span-2">
                    <label class="text-xs text-gray-600 dark:text-gray-400">Search (Name/Phone)</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g. Rahul / 9876...">
                </div>

                <div>
                    <label class="text-xs text-gray-600 dark:text-gray-400">Month</label>
                    <select name="month"
                            class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All</option>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" @selected((int)request('month') === $m)>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs text-gray-600 dark:text-gray-400">Day</label>
                    <select name="day"
                            class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All</option>
                        @foreach(range(1,31) as $d)
                            <option value="{{ $d }}" @selected((int)request('day') === $d)>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs text-gray-600 dark:text-gray-400">Upcoming in days</label>
                    <input type="number" min="1" max="365" name="upcoming_days" value="{{ request('upcoming_days') }}"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g. 30">
                </div>


                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700">
                        Apply
                    </button>
                    <a href="{{ route('birthday-records.index') }}"
                       class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-gray-800 dark:text-white bg-gray-100 dark:bg-neutral-800 rounded-lg hover:bg-gray-200 dark:hover:bg-neutral-700 border border-gray-200 dark:border-gray-700">
                        Reset
                    </a>
                </div>
            </form>
         </div>

            {{-- Table --}}
        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium tracking-wider">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Phone</th>
                    <th class="px-6 py-3">Date of Birth</th>
                    <th class="px-6 py-3">Added By</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                @forelse ($records as $r)
                    <tr>
{{--                        <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">--}}
{{--                            {{ $r->name ?? '-' }}--}}
{{--                        </td>--}}
                        <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">
                            <div class="flex items-center gap-2">
                                <span>{{ $r->name ?? '-' }}</span>

                                @php
                                    $today = \Carbon\Carbon::today();
                                    $dob = $r->date_of_birth ? \Carbon\Carbon::parse($r->date_of_birth) : null;
                                    $next = $dob ? $dob->copy()->year($today->year) : null;
                                    if($next && $next->lt($today)) $next->addYear();
                                    $daysLeft = $next ? $today->diffInDays($next, false) : null;
                                @endphp

                                @if($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 30)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 border border-green-200">
                                        Upcoming {{ $daysLeft }}d
                                    </span>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-3">
                            {{ $r->phone }}
                        </td>
                        <td class="px-6 py-3">
                            {{ optional($r->date_of_birth)->format('d M, Y') }}
                        </td>
                        <td class="px-6 py-3">
                            {{ optional($r->user)->name ?? '-' }}
                        </td>
                        <td class="px-6 py-3 space-x-3">
                            <a href="{{ route('birthday-records.show', $r->id) }}" class="text-blue-600 hover:underline">View</a>
                            <a href="{{ route('birthday-records.edit', $r->id) }}" class="text-yellow-600 hover:underline">Edit</a>

                            <form action="{{ route('birthday-records.destroy', $r->id) }}" method="POST" class="inline-block"
                                  onsubmit="return confirm('Delete this record?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                            <a href="{{ route('birthday-records.send', $r->id) }}" class="text-yellow-600 hover:underline">Send</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No records found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $records->withQueryString()->links() }}
        </div>
    </div>
</x-layouts.app>
