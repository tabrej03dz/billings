<x-layouts.app :title="__('Hospital Control Center')">
<div class="min-h-screen bg-slate-50 p-3 sm:p-5 dark:bg-neutral-950">
    <div class="mb-5 overflow-hidden rounded-3xl bg-gradient-to-r from-cyan-800 via-teal-700 to-emerald-700 p-6 text-white shadow-xl">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-[.22em] text-cyan-100">Hospital Management</div>
                <h1 class="mt-1 text-2xl font-black sm:text-3xl">Hospital Control Center</h1>
                <p class="mt-2 max-w-2xl text-sm text-cyan-50">Doctors, patients, departments, wards, rooms, beds aur visits ko ek jagah se manage karein.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('hospital.patients.index') }}" class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-cyan-800 shadow">+ Register Patient</a>
                <a href="{{ route('invoices.create', 'tax') }}" class="rounded-xl bg-black/15 px-4 py-2 text-sm font-bold ring-1 ring-white/30">Create Hospital Bill</a>
            </div>
        </div>
    </div>

    @include('hospital.partials.nav')

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach([
            ['Patients', $stats['patients'], 'bg-sky-50 text-sky-700'],
            ['Doctors', $stats['doctors'], 'bg-emerald-50 text-emerald-700'],
            ['Today Visits', $stats['today_visits'], 'bg-violet-50 text-violet-700'],
            ['Active Visits', $stats['active_visits'], 'bg-amber-50 text-amber-700'],
            ['Available Beds', $stats['available_beds'], 'bg-cyan-50 text-cyan-700'],
        ] as [$label,$value,$theme])
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <div class="inline-flex rounded-lg px-2.5 py-1 text-[10px] font-black uppercase {{ $theme }}">{{ $label }}</div>
                <div class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-3">
        <div class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex items-center justify-between border-b px-4 py-3 dark:border-neutral-800">
                <h2 class="font-bold text-slate-900 dark:text-white">Recent Patient Visits</h2>
                <a href="{{ route('hospital.visits.index') }}" class="text-xs font-bold text-cyan-700">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 dark:bg-neutral-800">
                    <tr><th class="p-3">Visit No.</th><th class="p-3">Patient</th><th class="p-3">Type</th><th class="p-3">Doctor</th><th class="p-3">Status</th></tr>
                    </thead>
                    <tbody>
                    @forelse($recentVisits as $visit)
                        <tr class="border-t dark:border-neutral-800">
                            <td class="p-3 font-bold">{{ $visit->visit_number }}</td>
                            <td class="p-3">{{ $visit->patient?->name ?? '-' }}</td>
                            <td class="p-3 uppercase">{{ $visit->visit_type }}</td>
                            <td class="p-3">{{ $visit->doctor?->name ?? '-' }}</td>
                            <td class="p-3"><span class="rounded-full bg-cyan-50 px-2 py-1 font-bold text-cyan-700">{{ str_replace('_',' ', $visit->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-8 text-center text-slate-500">No visit records found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="border-b px-4 py-3 dark:border-neutral-800"><h2 class="font-bold text-slate-900 dark:text-white">Ward Occupancy</h2></div>
            <div class="space-y-4 p-4">
                @forelse($wards as $ward)
                    @php
                        $total = max(1, $ward->beds_count);
                        $percent = min(100, round(($ward->occupied_beds_count / $total) * 100));
                    @endphp
                    <div>
                        <div class="mb-1 flex justify-between text-xs">
                            <span class="font-bold">{{ $ward->name }}</span>
                            <span>{{ $ward->occupied_beds_count }}/{{ $ward->beds_count }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-cyan-600" style="width:{{ $percent }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-slate-500">No ward configured.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
</x-layouts.app>