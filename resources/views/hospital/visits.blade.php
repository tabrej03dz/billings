<x-layouts.app :title="__('Patient Visits')">
<div class="min-h-screen bg-slate-50 p-3 sm:p-5 dark:bg-neutral-950">
@include('hospital.partials.alerts')
@include('hospital.partials.nav')

<div class="mb-4 rounded-2xl border bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-xl font-black dark:text-white">Patient Visits</h1>
            <p class="text-xs text-slate-500">OPD, IPD, emergency, day-care aur diagnostic history.</p>
        </div>

        <form method="GET" class="flex flex-wrap gap-2">
            <input name="search" value="{{ request('search') }}" placeholder="Patient / visit no." class="rounded-lg border px-3 py-2 text-xs">
            <select name="type" class="rounded-lg border px-3 py-2 text-xs">
                <option value="">All types</option>
                @foreach(['opd','ipd','emergency','day_care','diagnostic','pharmacy'] as $type)
                    <option value="{{ $type }}" @selected(request('type')===$type)>{{ strtoupper($type) }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-lg border px-3 py-2 text-xs">
                <option value="">All status</option>
                @foreach(['registered','in_consultation','admitted','discharged','cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-cyan-700 px-4 py-2 font-bold text-white">Filter</button>
        </form>
    </div>
</div>

<div class="overflow-hidden rounded-2xl border bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead class="bg-cyan-800 text-white">
                <tr><th class="p-3 text-left">Visit</th><th class="p-3 text-left">Patient</th><th class="p-3">Doctor</th><th class="p-3">Department</th><th class="p-3">Location</th><th class="p-3">Status</th></tr>
            </thead>
            <tbody>
            @forelse($visits as $visit)
                <tr class="border-t dark:border-neutral-800">
                    <td class="p-3">
                        <div class="font-bold dark:text-white">{{ $visit->visit_number }}</div>
                        <div>{{ strtoupper($visit->visit_type) }} • {{ $visit->visit_at?->format('d M Y h:i A') }}</div>
                    </td>
                    <td class="p-3">
                        <b class="dark:text-white">{{ $visit->patient?->name }}</b>
                        <div>{{ $visit->patient?->mobile }}</div>
                    </td>
                    <td class="p-3 text-center">{{ $visit->doctor?->name ?: '-' }}</td>
                    <td class="p-3 text-center">{{ $visit->department?->name ?: '-' }}</td>
                    <td class="p-3 text-center">
                        {{ $visit->ward?->name ?: '-' }}
                        {{ $visit->room ? '/ '.$visit->room->room_number : '' }}
                        {{ $visit->bed ? '/ '.$visit->bed->bed_number : '' }}
                    </td>
                    <td class="p-3 text-center">
                        <span class="rounded-full bg-cyan-50 px-2 py-1 font-bold text-cyan-700">
                            {{ ucfirst(str_replace('_',' ',$visit->status)) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-10 text-center">No visits found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $visits->links() }}</div>
</div>
</div>
</x-layouts.app>