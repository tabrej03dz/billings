@php
    $hospitalLinks = [
        [
            'route' => 'hospital.dashboard',
            'label' => 'Overview',
            'icon' => 'home',
            'patterns' => ['hospital.dashboard'],
        ],
        [
            'route' => 'hospital.patients.index',
            'label' => 'Patients',
            'icon' => 'patients',
            'patterns' => ['hospital.patients.*'],
        ],
        [
            'route' => 'hospital.doctors.index',
            'label' => 'Doctors',
            'icon' => 'doctor',
            'patterns' => ['hospital.doctors.*'],
        ],
        [
            'route' => 'hospital.departments.index',
            'label' => 'Departments',
            'icon' => 'department',
            'patterns' => ['hospital.departments.*'],
        ],
        [
            'route' => 'hospital.wards.index',
            'label' => 'Wards',
            'icon' => 'ward',
            'patterns' => ['hospital.wards.*'],
        ],
        [
            'route' => 'hospital.rooms.index',
            'label' => 'Rooms',
            'icon' => 'room',
            'patterns' => ['hospital.rooms.*'],
        ],
        [
            'route' => 'hospital.beds.index',
            'label' => 'Beds',
            'icon' => 'bed',
            'patterns' => ['hospital.beds.*'],
        ],
        [
            'route' => 'hospital.visits.index',
            'label' => 'Visits',
            'icon' => 'visit',
            'patterns' => ['hospital.visits.*'],
        ],
    ];

    $routeIsActive = static function (array $patterns): bool {
        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    };
@endphp

<div
    class="mb-4 overflow-x-auto rounded-2xl border border-cyan-200
        bg-white p-2 shadow-sm
        dark:border-cyan-900/60 dark:bg-neutral-900"
>
    <nav
        aria-label="Hospital management navigation"
        class="flex min-w-max items-center gap-1"
    >
        @foreach($hospitalLinks as $link)
            @php
                $active = $routeIsActive($link['patterns']);
            @endphp

            <a
                href="{{ route($link['route']) }}"
                @class([
                    'inline-flex items-center gap-2 rounded-xl px-3 py-2',
                    'text-xs font-semibold transition',
                    'bg-cyan-700 text-white shadow-sm' => $active,
                    'text-slate-600 hover:bg-cyan-50 hover:text-cyan-800' => !$active,
                    'dark:text-neutral-300 dark:hover:bg-cyan-950/40 dark:hover:text-cyan-200' => !$active,
                ])
            >
                @switch($link['icon'])
                    @case('home')
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m3 11 9-8 9 8v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9Z"/>
                        </svg>
                        @break

                    @case('patients')
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8 1v6m3-3h-6"/>
                        </svg>
                        @break

                    @case('doctor')
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 3h6v4h4v6h-4v4H9v-4H5V7h4V3Z"/>
                        </svg>
                        @break

                    @case('department')
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z"/>
                        </svg>
                        @break

                    @case('ward')
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 21V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v16M8 7h2m4 0h2M8 11h2m4 0h2M8 15h2m4 0h2"/>
                        </svg>
                        @break

                    @case('room')
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 21h18M5 21V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v17M9 12h.01"/>
                        </svg>
                        @break

                    @case('bed')
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 20v-8m18 8v-8M3 16h18M5 12V7a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v5m0 0V9a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3"/>
                        </svg>
                        @break

                    @case('visit')
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        @break
                @endswitch

                <span>{{ $link['label'] }}</span>
            </a>
        @endforeach
    </nav>
</div>