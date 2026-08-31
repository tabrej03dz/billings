<x-layouts.app :title="__('Super Admin Dashboard')">
    @php
        $number = fn ($value) => number_format((int) $value);
        $money = fn ($value) => '₹' . number_format((float) $value, 2);
    @endphp

    <div class="flex flex-col gap-6">

        {{-- PRIMARY COUNTS --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('businesses.index') }}"
               class="group rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-blue-900/60 dark:bg-blue-950/30">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-blue-700 dark:text-blue-300">Total Businesses</p>
                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $number($totalBusinesses) }}</p>
                        <p class="mt-2 text-xs text-slate-500 dark:text-neutral-400">
                            +{{ $number($newBusinessesThisMonth) }} this month
                        </p>
                    </div>
                    <div class="rounded-xl bg-blue-600 p-3 text-white shadow-sm">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4" />
                        </svg>
                    </div>
                </div>
            </a>

            <a href="{{ route('users.index') }}"
               class="group rounded-2xl border border-violet-200 bg-violet-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-violet-900/60 dark:bg-violet-950/30">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-violet-700 dark:text-violet-300">Total Users</p>
                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $number($totalUsers) }}</p>
                        <p class="mt-2 text-xs text-slate-500 dark:text-neutral-400">
                            +{{ $number($newUsersThisMonth) }} this month
                        </p>
                    </div>
                    <div class="rounded-xl bg-violet-600 p-3 text-white shadow-sm">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H2v-2a4 4 0 014-4h3m4 6v-2a4 4 0 00-8 0v2m8-10a4 4 0 11-8 0 4 4 0 018 0zm8 0a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
            </a>

            <a href="{{ route('plans.index') }}"
               class="group rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-emerald-900/60 dark:bg-emerald-950/30">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Total Plans</p>
                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $number($totalPlans) }}</p>
                        <p class="mt-2 text-xs text-slate-500 dark:text-neutral-400">
                            {{ $number($activePlanMasters) }} active plan types
                        </p>
                    </div>
                    <div class="rounded-xl bg-emerald-600 p-3 text-white shadow-sm">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12V8a2 2 0 00-2-2h-3V4a2 2 0 00-2-2h-2a2 2 0 00-2 2v2H6a2 2 0 00-2 2v4m16 0v8H4v-8m16 0H4" />
                        </svg>
                    </div>
                </div>
            </a>

            <a href="{{ route('user-plans.index') }}"
               class="group rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-amber-900/60 dark:bg-amber-950/30">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-amber-700 dark:text-amber-300">Assigned Plans</p>
                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $number($totalAssignedPlans) }}</p>
                        <p class="mt-2 text-xs text-slate-500 dark:text-neutral-400">
                            All plan subscriptions
                        </p>
                    </div>
                    <div class="rounded-xl bg-amber-500 p-3 text-white shadow-sm">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5-3a11 11 0 01-8-4 11 11 0 01-8 4c0 5.5 3.8 10.7 8 12 4.2-1.3 8-6.5 8-12z" />
                        </svg>
                    </div>
                </div>
            </a>
        </section>


        {{-- BUSINESS & USER TREND --}}
        <section class="grid gap-6 xl:grid-cols-3">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900 xl:col-span-2">
                <div class="flex flex-col gap-3 border-b border-slate-200 p-5 dark:border-neutral-700 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                            Business & User Trend
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-neutral-400">
                            Daily new business aur user registration comparison
                        </p>
                    </div>

                    <div class="inline-flex w-fit rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-300">
                        {{ $trendDays }} day(s)
                    </div>
                </div>

                <div class="p-5">
                    <div class="mb-4 flex justify-end gap-5 text-xs font-semibold">
                        <span class="inline-flex items-center gap-2 text-slate-600 dark:text-neutral-300">
                            <span class="h-3 w-3 rounded-full border-[3px] border-indigo-600 bg-white dark:bg-neutral-900"></span>
                            Businesses
                        </span>

                        <span class="inline-flex items-center gap-2 text-slate-600 dark:text-neutral-300">
                            <span class="h-3 w-3 rounded-full border-[3px] border-amber-500 bg-white dark:bg-neutral-900"></span>
                            Users
                        </span>
                    </div>

                    <div class="relative h-[330px] w-full">
                        <canvas id="businessUserTrendChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- GROWTH SUMMARY --}}
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">

                {{-- BUSINESS GROWTH --}}
                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm dark:border-blue-900/60 dark:bg-blue-950/30">
                    <p class="text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-300">
                        Business Growth
                    </p>

                    <div class="mt-3 flex items-end justify-between gap-3">
                        <div>
                            <p class="text-3xl font-black text-slate-900 dark:text-white">
                                {{ $number($newBusinessesThisMonth) }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500 dark:text-neutral-400">
                                new this month
                            </p>
                        </div>

                        @if($businessGrowthPercentage === null)
                            <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-700 dark:bg-blue-900/60 dark:text-blue-300">
                                New growth
                            </span>
                        @else
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold
                                {{ $businessGrowthPercentage >= 0
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                                    : 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300' }}">

                                {{ $businessGrowthPercentage >= 0 ? '↑' : '↓' }}
                                {{ abs($businessGrowthPercentage) }}%
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 border-t border-blue-200 pt-4 text-xs text-slate-600 dark:border-blue-900/60 dark:text-neutral-300">
                        Previous month:
                        <strong>{{ $number($newBusinessesPreviousMonth) }}</strong>
                        <br>
                        Last 12 months:
                        <strong>{{ $number($businessesLast12Months) }}</strong>
                    </div>
                </div>

                {{-- USER GROWTH --}}
                <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5 shadow-sm dark:border-violet-900/60 dark:bg-violet-950/30">
                    <p class="text-xs font-bold uppercase tracking-wider text-violet-700 dark:text-violet-300">
                        User Growth
                    </p>

                    <div class="mt-3 flex items-end justify-between gap-3">
                        <div>
                            <p class="text-3xl font-black text-slate-900 dark:text-white">
                                {{ $number($newUsersThisMonth) }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500 dark:text-neutral-400">
                                new this month
                            </p>
                        </div>

                        @if($userGrowthPercentage === null)
                            <span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-bold text-violet-700 dark:bg-violet-900/60 dark:text-violet-300">
                                New growth
                            </span>
                        @else
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold
                                {{ $userGrowthPercentage >= 0
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                                    : 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300' }}">

                                {{ $userGrowthPercentage >= 0 ? '↑' : '↓' }}
                                {{ abs($userGrowthPercentage) }}%
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 border-t border-violet-200 pt-4 text-xs text-slate-600 dark:border-violet-900/60 dark:text-neutral-300">
                        Previous month:
                        <strong>{{ $number($newUsersPreviousMonth) }}</strong>
                        <br>
                        Last 12 months:
                        <strong>{{ $number($usersLast12Months) }}</strong>
                    </div>
                </div>
            </div>
        </section>


        {{-- PLAN STATUS GRAPH --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Plan Status Overview</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-neutral-400">
                    Active, expired aur upcoming renewals ka graphical comparison
                </p>
            </div>

            @php
                $planGraphMax = max(
                    1,
                    $activePlans,
                    $expiredPlans,
                    $expiringIn7Days,
                    $expiringIn30Days,
                    $businessesWithoutActivePlan
                );

                $planGraphRows = [
                    ['label' => 'Active Plans', 'value' => $activePlans, 'bar' => 'bg-emerald-500'],
                    ['label' => 'Expired Plans', 'value' => $expiredPlans, 'bar' => 'bg-red-500'],
                    ['label' => 'Expiring in 7 Days', 'value' => $expiringIn7Days, 'bar' => 'bg-orange-500'],
                    ['label' => 'Expiring in 30 Days', 'value' => $expiringIn30Days, 'bar' => 'bg-yellow-500'],
                    ['label' => 'Businesses Without Active Plan', 'value' => $businessesWithoutActivePlan, 'bar' => 'bg-slate-500'],
                ];
            @endphp

            <div class="space-y-4">
                @foreach($planGraphRows as $row)
                    @php
                        $width = max(2, ($row['value'] / $planGraphMax) * 100);
                    @endphp

                    <div>
                        <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                            <span class="font-semibold text-slate-700 dark:text-neutral-200">
                                {{ $row['label'] }}
                            </span>
                            <span class="font-black text-slate-900 dark:text-white">
                                {{ $number($row['value']) }}
                            </span>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-neutral-800">
                            <div
                                class="h-full rounded-full {{ $row['bar'] }} transition-all"
                                style="width: {{ $width }}%;">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- PLAN HEALTH --}}
        <section>
            <div class="mb-3 flex items-end justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Plan Health</h2>
                    <p class="text-sm text-slate-500 dark:text-neutral-400">Overall subscription expiry status</p>
                </div>
                <a href="{{ route('user-plans.index') }}" class="text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">
                    View all plans
                </a>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm dark:border-emerald-900/50 dark:bg-neutral-900">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Active</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $number($activePlans) }}</p>
                    <p class="mt-2 text-xs text-slate-500 dark:text-neutral-400">Currently valid subscriptions</p>
                </div>

                <div class="rounded-2xl border border-red-200 bg-white p-5 shadow-sm dark:border-red-900/50 dark:bg-neutral-900">
                    <p class="text-xs font-bold uppercase tracking-wider text-red-600">Expired</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $number($expiredPlans) }}</p>
                    <p class="mt-2 text-xs text-slate-500 dark:text-neutral-400">Expiry date already passed</p>
                </div>

                <div class="rounded-2xl border border-orange-200 bg-white p-5 shadow-sm dark:border-orange-900/50 dark:bg-neutral-900">
                    <p class="text-xs font-bold uppercase tracking-wider text-orange-600">Expiring in 7 Days</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $number($expiringIn7Days) }}</p>
                    <p class="mt-2 text-xs text-slate-500 dark:text-neutral-400">Needs immediate follow-up</p>
                </div>

                <div class="rounded-2xl border border-yellow-200 bg-white p-5 shadow-sm dark:border-yellow-900/50 dark:bg-neutral-900">
                    <p class="text-xs font-bold uppercase tracking-wider text-yellow-600">Expiring in 30 Days</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $number($expiringIn30Days) }}</p>
                    <p class="mt-2 text-xs text-slate-500 dark:text-neutral-400">Upcoming renewals</p>
                </div>

                <div class="rounded-2xl border border-slate-300 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-neutral-300">No Active Plan</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $number($businessesWithoutActivePlan) }}</p>
                    <p class="mt-2 text-xs text-slate-500 dark:text-neutral-400">
                        {{ $number($businessesWithActivePlan) }} businesses covered
                    </p>
                </div>
            </div>
        </section>

        {{-- EXPIRING PLANS TABLE --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="flex flex-col gap-2 border-b border-slate-200 p-5 dark:border-neutral-700 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 dark:text-white">Plans Expiring in Next 30 Days</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-neutral-400">Renewal follow-up ke liye priority list</p>
                </div>
                <span class="inline-flex w-fit rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-700 dark:bg-orange-950/60 dark:text-orange-300">
                    {{ $number($expiringIn30Days) }} upcoming
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-neutral-700">
                    <thead class="bg-slate-50 dark:bg-neutral-800/80">
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">
                            <th class="px-5 py-3">Business</th>
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">Plan</th>
                            <th class="px-5 py-3">Expiry</th>
                            <th class="px-5 py-3">Days Left</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-neutral-800">
                        @forelse($expiringPlans as $userPlan)
                            @php
                                $expiry = $userPlan->expiry_date
                                    ? \Carbon\Carbon::parse($userPlan->expiry_date)->startOfDay()
                                    : null;

                                $daysLeft = $expiry
                                    ? max(0, $today->diffInDays($expiry, false))
                                    : null;
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/60">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-900 dark:text-white">
                                        {{ $userPlan->business->name ?? 'N/A' }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500 dark:text-neutral-400">
                                        {{ $userPlan->business->mobile ?? $userPlan->business->email ?? '' }}
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-slate-700 dark:text-neutral-200">{{ $userPlan->user->name ?? 'N/A' }}</div>
                                    <div class="mt-1 text-xs text-slate-500 dark:text-neutral-400">{{ $userPlan->user->email ?? '' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-800 dark:text-neutral-100">{{ $userPlan->plan->name ?? 'N/A' }}</div>
                                    @if($userPlan->plan)
                                        <div class="mt-1 text-xs text-slate-500 dark:text-neutral-400">{{ $money($userPlan->plan->price) }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-700 dark:text-neutral-200">
                                    {{ $expiry?->format('d M Y') ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-4">
                                    @if($daysLeft !== null)
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold
                                            {{ $daysLeft <= 3
                                                ? 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300'
                                                : ($daysLeft <= 7
                                                    ? 'bg-orange-100 text-orange-700 dark:bg-orange-950/60 dark:text-orange-300'
                                                    : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950/60 dark:text-yellow-300') }}">
                                            {{ $daysLeft }} day{{ $daysLeft == 1 ? '' : 's' }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-slate-500 dark:text-neutral-400">
                                    Next 30 days me koi plan expire nahi ho raha.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            {{-- LATEST BUSINESSES --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900 xl:col-span-2">
                <div class="flex items-center justify-between border-b border-slate-200 p-5 dark:border-neutral-700">
                    <div>
                        <h2 class="font-bold text-slate-900 dark:text-white">Latest Businesses</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-neutral-400">Recently registered businesses</p>
                    </div>
                    <a href="{{ route('businesses.index') }}" class="text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">View all</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-neutral-700">
                        <thead class="bg-slate-50 dark:bg-neutral-800/80">
                            <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">
                                <th class="px-5 py-3">Business</th>
                                <th class="px-5 py-3">Users</th>
                                <th class="px-5 py-3">Latest Plan</th>
                                <th class="px-5 py-3">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-neutral-800">
                            @forelse($latestBusinesses as $business)
                                @php
                                    $businessPlan = $latestBusinessPlans->get($business->id);
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/60">
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-900 dark:text-white">{{ $business->name }}</div>
                                        <div class="mt-1 text-xs text-slate-500 dark:text-neutral-400">{{ $business->email }}</div>
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-slate-700 dark:text-neutral-200">
                                        {{ $number($business->users_count) }}
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($businessPlan?->plan)
                                            <div class="font-semibold text-slate-800 dark:text-neutral-100">{{ $businessPlan->plan->name }}</div>
                                            <div class="mt-1 text-xs text-slate-500 dark:text-neutral-400">
                                                @if($businessPlan->expiry_date)
                                                    Expires {{ \Carbon\Carbon::parse($businessPlan->expiry_date)->format('d M Y') }}
                                                @else
                                                    No expiry
                                                @endif
                                            </div>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-neutral-800 dark:text-neutral-300">
                                                No plan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-slate-600 dark:text-neutral-300">
                                        {{ $business->created_at?->format('d M Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-slate-500 dark:text-neutral-400">No businesses found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PLAN DISTRIBUTION --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <h2 class="font-bold text-slate-900 dark:text-white">Plan Distribution</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-neutral-400">Most assigned plan types</p>

                <div class="mt-5 space-y-3">
                    @forelse($planDistribution as $row)
                        @php
                            $percentage = $totalAssignedPlans > 0
                                ? min(100, round(($row->assigned_count / $totalAssignedPlans) * 100))
                                : 0;
                        @endphp
                        <div class="rounded-xl border border-slate-100 p-3 dark:border-neutral-800">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <span class="truncate text-sm font-semibold text-slate-800 dark:text-neutral-100">{{ $row->name }}</span>
                                <span class="text-xs font-bold text-slate-500 dark:text-neutral-400">{{ $row->assigned_count }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-neutral-800">
                                <div class="h-full rounded-full bg-indigo-600" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl bg-slate-50 p-5 text-center text-sm text-slate-500 dark:bg-neutral-800 dark:text-neutral-400">
                            No plan data found.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- RECENTLY EXPIRED PLANS --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="flex items-center justify-between border-b border-slate-200 p-5 dark:border-neutral-700">
                <div>
                    <h2 class="font-bold text-slate-900 dark:text-white">Recently Expired Plans</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-neutral-400">Latest expired plan subscriptions</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-neutral-700">
                    <thead class="bg-slate-50 dark:bg-neutral-800/80">
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">
                            <th class="px-5 py-3">Business</th>
                            <th class="px-5 py-3">Plan</th>
                            <th class="px-5 py-3">Expired On</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-neutral-800">
                        @forelse($recentlyExpiredPlans as $userPlan)
                            <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/60">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-900 dark:text-white">{{ $userPlan->business->name ?? 'N/A' }}</div>
                                    <div class="mt-1 text-xs text-slate-500 dark:text-neutral-400">{{ $userPlan->user->name ?? $userPlan->user->email ?? '' }}</div>
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-700 dark:text-neutral-200">{{ $userPlan->plan->name ?? 'N/A' }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700 dark:bg-red-950/60 dark:text-red-300">
                                        {{ $userPlan->expiry_date ? \Carbon\Carbon::parse($userPlan->expiry_date)->format('d M Y') : 'N/A' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-10 text-center text-slate-500 dark:text-neutral-400">No expired plans found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('businessUserTrendChart');

            if (!canvas || typeof Chart === 'undefined') {
                return;
            }

            const labels = @json($trendLabels);
            const businessData = @json($businessTrendData);
            const userData = @json($userTrendData);

            const ctx = canvas.getContext('2d');

            /*
            |--------------------------------------------------------------------------
            | Business Gradient
            |--------------------------------------------------------------------------
            */
            const businessGradient = ctx.createLinearGradient(0, 0, 0, 320);
            businessGradient.addColorStop(0, 'rgba(79, 70, 229, 0.28)');
            businessGradient.addColorStop(1, 'rgba(79, 70, 229, 0.02)');

            new Chart(ctx, {
                type: 'line',

                data: {
                    labels: labels,

                    datasets: [
                        {
                            label: 'Businesses',
                            data: businessData,

                            borderColor: '#4f46e5',
                            backgroundColor: businessGradient,

                            borderWidth: 2.5,

                            pointRadius: 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#4f46e5',
                            pointBorderWidth: 2,

                            tension: 0.38,
                            cubicInterpolationMode: 'monotone',

                            fill: true
                        },

                        {
                            label: 'Users',
                            data: userData,

                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.04)',

                            borderWidth: 2.5,

                            pointRadius: 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#f59e0b',
                            pointBorderWidth: 2,

                            tension: 0.38,
                            cubicInterpolationMode: 'monotone',

                            fill: false
                        }
                    ]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    interaction: {
                        mode: 'index',
                        intersect: false
                    },

                    plugins: {
                        legend: {
                            display: false
                        },

                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(30, 41, 59, 0.92)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: 'rgba(255,255,255,0.12)',
                            borderWidth: 1,
                            displayColors: true,
                            padding: 12,
                            cornerRadius: 10,

                            callbacks: {
                                title: function (items) {
                                    return items.length ? items[0].label : '';
                                },

                                label: function (context) {
                                    return context.dataset.label + ': ' + context.parsed.y;
                                }
                            }
                        }
                    },

                    scales: {
                        x: {
                            grid: {
                                display: false
                            },

                            border: {
                                display: false
                            },

                            ticks: {
                                color: '#64748b',
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 10,
                                font: {
                                    size: 11
                                }
                            }
                        },

                        y: {
                            beginAtZero: true,

                            suggestedMax: 5,

                            grid: {
                                color: 'rgba(148, 163, 184, 0.20)',
                                drawTicks: false
                            },

                            border: {
                                display: false
                            },

                            ticks: {
                                precision: 0,
                                stepSize: 1,
                                color: '#64748b',
                                padding: 8,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },

                    animation: {
                        duration: 700
                    }
                }
            });
        });
    </script>

</x-layouts.app>