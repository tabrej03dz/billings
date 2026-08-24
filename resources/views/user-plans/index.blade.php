<x-layouts.app :title="__('User Plans')">

    <div class="flex flex-col gap-4">

        {{-- ============================================================
            SUCCESS MESSAGE
        ============================================================ --}}
        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif


        {{-- ============================================================
            HEADER + SEARCH
        ============================================================ --}}
        <div class="flex flex-wrap items-center justify-between gap-4 bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">

            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                    User Plans
                </h1>

                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    Manage regular and trial plans
                </p>
            </div>


            <div class="flex flex-wrap items-center gap-2">

                <form
                    method="GET"
                    action="{{ route('user-plans.index') }}"
                    class="flex flex-wrap items-center gap-2"
                >

                    {{-- Current Tab --}}
                    <input type="hidden" name="tab" value="{{ $tab }}">


                    {{-- Search --}}
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search user / email / plan / business..."
                        class="px-3 py-2 text-sm border rounded-lg
                               bg-white
                               dark:bg-neutral-800
                               dark:border-neutral-700
                               dark:text-white
                               min-w-[250px]"
                    >


                    {{-- Business Filter --}}
                    <select
                        name="business_id"
                        class="px-3 py-2 text-sm border rounded-lg
                               bg-white
                               dark:bg-neutral-800
                               dark:border-neutral-700
                               dark:text-white"
                    >
                        <option value="">
                            All Businesses
                        </option>

                        @foreach($businesses as $business)

                            <option
                                value="{{ $business->id }}"
                                {{ request('business_id') == $business->id ? 'selected' : '' }}
                            >
                                {{ $business->name
                                    ?? $business->business_name
                                    ?? 'Business #'.$business->id }}
                            </option>

                        @endforeach

                    </select>


                    {{-- Search Button --}}
                    <button
                        type="submit"
                        class="inline-flex items-center
                               px-4 py-2
                               text-sm font-medium
                               text-white
                               bg-blue-600
                               rounded-lg
                               hover:bg-blue-700"
                    >
                        Search
                    </button>


                    {{-- Reset --}}
                    @if(request('q') || request('business_id'))

                        <a
                            href="{{ route('user-plans.index', ['tab' => $tab]) }}"
                            class="inline-flex items-center
                                   px-4 py-2
                                   text-sm font-medium
                                   text-white
                                   bg-gray-500
                                   rounded-lg
                                   hover:bg-gray-600"
                        >
                            Reset
                        </a>

                    @endif

                </form>


                {{-- New User Plan --}}
                <a
                    href="{{ route(
                        'user-plans.create',
                        request('business_id')
                            ? ['business_id' => request('business_id')]
                            : []
                    ) }}"
                    class="inline-flex items-center
                           px-4 py-2
                           text-sm font-medium
                           text-white
                           bg-green-600
                           rounded-lg
                           hover:bg-green-700"
                >
                    + New User Plan
                </a>

            </div>
        </div>


        {{-- ============================================================
            TABS
        ============================================================ --}}
        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-2">

            <div class="flex flex-wrap gap-2">


                {{-- =======================
                    REGULAR TAB
                ======================== --}}
                <a
                    href="{{ route('user-plans.index', array_filter([
                        'tab' => 'regular',
                        'q' => request('q'),
                        'business_id' => request('business_id'),
                    ])) }}"
                    class="
                        inline-flex items-center gap-2
                        px-5 py-3
                        rounded-lg
                        font-semibold
                        text-sm
                        transition

                        {{ $tab === 'regular'
                            ? 'bg-blue-600 text-white shadow'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-neutral-800 dark:text-gray-300 dark:hover:bg-neutral-700'
                        }}
                    "
                >

                    <span>
                        Regular Plans
                    </span>

                    <span
                        class="
                            px-2 py-0.5
                            rounded-full
                            text-xs

                            {{ $tab === 'regular'
                                ? 'bg-white/20 text-white'
                                : 'bg-gray-200 text-gray-700 dark:bg-neutral-700 dark:text-gray-200'
                            }}
                        "
                    >
                        {{ $regularCount }}
                    </span>

                </a>


                {{-- =======================
                    TRIAL TAB
                ======================== --}}
                <a
                    href="{{ route('user-plans.index', array_filter([
                        'tab' => 'trial',
                        'q' => request('q'),
                        'business_id' => request('business_id'),
                    ])) }}"
                    class="
                        inline-flex items-center gap-2
                        px-5 py-3
                        rounded-lg
                        font-semibold
                        text-sm
                        transition

                        {{ $tab === 'trial'
                            ? 'bg-orange-500 text-white shadow'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-neutral-800 dark:text-gray-300 dark:hover:bg-neutral-700'
                        }}
                    "
                >

                    <span>
                        Trial Plans
                    </span>

                    <span
                        class="
                            px-2 py-0.5
                            rounded-full
                            text-xs

                            {{ $tab === 'trial'
                                ? 'bg-white/20 text-white'
                                : 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300'
                            }}
                        "
                    >
                        {{ $trialCount }}
                    </span>

                </a>

            </div>

        </div>


        {{-- ============================================================
            CURRENT TAB TITLE
        ============================================================ --}}
        <div class="flex items-center justify-between">

            <div>

                @if($tab === 'trial')

                    <h2 class="text-xl font-bold text-orange-600 dark:text-orange-400">
                        Trial Plans
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Plans having duration less than 30 days.
                    </p>

                @else

                    <h2 class="text-xl font-bold text-blue-600 dark:text-blue-400">
                        Regular Plans
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Plans having duration of 30 days or more.
                    </p>

                @endif

            </div>

        </div>


        {{-- ============================================================
            TABLE
        ============================================================ --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">

            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">

                <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium tracking-wider">

                    <tr>

                        <th class="px-6 py-3">
                            #
                        </th>

                        <th class="px-6 py-3">
                            Business
                        </th>

                        <th class="px-6 py-3">
                            User
                        </th>

                        <th class="px-6 py-3">
                            Email
                        </th>

                        <th class="px-6 py-3">
                            Plan
                        </th>

                        <th class="px-6 py-3">
                            Price
                        </th>

                        <th class="px-6 py-3">
                            Start Date
                        </th>

                        <th class="px-6 py-3">
                            Expiry Date
                        </th>

                        <th class="px-6 py-3">
                            Duration
                        </th>

                        <th class="px-6 py-3">
                            Type
                        </th>

                        <th class="px-6 py-3">
                            Status
                        </th>

                        <th class="px-6 py-3">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">

                    @forelse ($userPlans as $userPlan)

                        @php

                            $startDate = $userPlan->start_date
                                ? \Carbon\Carbon::parse($userPlan->start_date)
                                : null;

                            $expiryDate = $userPlan->expiry_date
                                ? \Carbon\Carbon::parse($userPlan->expiry_date)
                                : null;


                            $durationDays = null;

                            if ($startDate && $expiryDate) {
                                $durationDays = $startDate->diffInDays($expiryDate);
                            }


                            $isTrial = $durationDays !== null && $durationDays < 30;

                        @endphp


                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800 transition">

                            {{-- Number --}}
                            <td class="px-6 py-4">

                                {{ $userPlans->firstItem() + $loop->index }}

                            </td>


                            {{-- Business --}}
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">

                                {{ $userPlan->business->name
                                    ?? $userPlan->business->business_name
                                    ?? 'N/A'
                                }}

                            </td>


                            {{-- User --}}
                            <td class="px-6 py-4">

                                {{ $userPlan->user->name ?? 'N/A' }}

                            </td>


                            {{-- Email --}}
                            <td class="px-6 py-4">

                                {{ $userPlan->user->email ?? 'N/A' }}

                            </td>


                            {{-- Plan --}}
                            <td class="px-6 py-4 font-semibold">

                                {{ $userPlan->plan->name ?? 'N/A' }}

                            </td>


                            {{-- Price --}}
                            <td class="px-6 py-4">

                                ₹{{ number_format($userPlan->plan->price ?? 0, 2) }}

                            </td>


                            {{-- Start --}}
                            <td class="px-6 py-4 whitespace-nowrap">

                                {{ $startDate
                                    ? $startDate->format('d M Y')
                                    : 'N/A'
                                }}

                            </td>


                            {{-- Expiry --}}
                            <td class="px-6 py-4 whitespace-nowrap">

                                {{ $expiryDate
                                    ? $expiryDate->format('d M Y')
                                    : 'N/A'
                                }}

                            </td>


                            {{-- Duration --}}
                            <td class="px-6 py-4 whitespace-nowrap">

                                @if($durationDays !== null)

                                    <span class="font-semibold">
                                        {{ $durationDays }} Days
                                    </span>

                                @else

                                    <span class="text-gray-400">
                                        N/A
                                    </span>

                                @endif

                            </td>


                            {{-- Type --}}
                            <td class="px-6 py-4">

                                @if($isTrial)

                                    <span class="
                                        inline-flex
                                        items-center
                                        px-3 py-1
                                        text-xs
                                        font-bold
                                        text-orange-700
                                        bg-orange-100
                                        rounded-full
                                        dark:bg-orange-900/30
                                        dark:text-orange-300
                                    ">
                                        Trial
                                    </span>

                                @else

                                    <span class="
                                        inline-flex
                                        items-center
                                        px-3 py-1
                                        text-xs
                                        font-bold
                                        text-blue-700
                                        bg-blue-100
                                        rounded-full
                                        dark:bg-blue-900/30
                                        dark:text-blue-300
                                    ">
                                        Regular
                                    </span>

                                @endif

                            </td>


                            {{-- Status --}}
                            <td class="px-6 py-4">

                                @if($userPlan->status)

                                    <span class="
                                        px-3 py-1
                                        text-xs
                                        font-semibold
                                        text-green-700
                                        bg-green-100
                                        rounded-full
                                    ">
                                        Active
                                    </span>

                                @else

                                    <span class="
                                        px-3 py-1
                                        text-xs
                                        font-semibold
                                        text-red-700
                                        bg-red-100
                                        rounded-full
                                    ">
                                        Inactive
                                    </span>

                                @endif

                            </td>


                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap">

                                <div class="flex items-center gap-2">


                                    {{-- View --}}
                                    <a
                                        href="{{ route('user-plans.show', $userPlan->id) }}"
                                        class="
                                            bg-blue-600
                                            text-white
                                            px-3 py-2
                                            rounded-lg
                                            hover:bg-blue-700
                                        "
                                    >
                                        View
                                    </a>


                                    {{-- Edit --}}
                                    <a
                                        href="{{ route('user-plans.edit', $userPlan->id) }}"
                                        class="
                                            bg-yellow-500
                                            text-white
                                            px-3 py-2
                                            rounded-lg
                                            hover:bg-yellow-600
                                        "
                                    >
                                        Edit
                                    </a>


                                    {{-- Delete --}}
                                    <form
                                        action="{{ route('user-plans.destroy', $userPlan->id) }}"
                                        method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('Are you sure you want to delete this user plan?');"
                                    >

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="
                                                bg-red-600
                                                text-white
                                                px-3 py-2
                                                rounded-lg
                                                hover:bg-red-700
                                            "
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="12"
                                class="px-6 py-10 text-center text-gray-500 dark:text-gray-400"
                            >

                                @if($tab === 'trial')

                                    <div class="font-semibold text-lg">
                                        No trial plans found.
                                    </div>

                                    <div class="text-sm mt-1">
                                        Plans with duration less than 30 days will appear here.
                                    </div>

                                @else

                                    <div class="font-semibold text-lg">
                                        No regular plans found.
                                    </div>

                                    <div class="text-sm mt-1">
                                        Plans with duration 30 days or more will appear here.
                                    </div>

                                @endif

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- ============================================================
            PAGINATION
        ============================================================ --}}
        @if($userPlans->hasPages())

            <div class="mt-4">

                {{ $userPlans->links() }}

            </div>

        @endif


    </div>

</x-layouts.app>