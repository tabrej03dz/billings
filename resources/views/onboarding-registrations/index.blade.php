<x-layouts.app :title="__('Onboarding Registrations')">
    <div class="flex flex-col gap-4">

        {{-- Success message --}}
        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- Error message --}}
        @if(session('error'))
            <div class="p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-lg">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                    Onboarding Registrations
                </h1>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    User registration और onboarding progress manage करें।
                </p>
            </div>

            <a href="{{ route('onboarding-registrations.index') }}"
               class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-slate-600 rounded-lg hover:bg-slate-700">
                Refresh
            </a>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">

            <div class="p-4 bg-white border border-gray-200 rounded-xl dark:bg-neutral-900 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $summary['total'] ?? 0 }}
                </p>
            </div>

            <div class="p-4 bg-white border border-gray-200 rounded-xl dark:bg-neutral-900 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">Verified</p>
                <p class="mt-1 text-2xl font-bold text-green-600">
                    {{ $summary['verified'] ?? 0 }}
                </p>
            </div>

            <div class="p-4 bg-white border border-gray-200 rounded-xl dark:bg-neutral-900 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">Unverified</p>
                <p class="mt-1 text-2xl font-bold text-orange-600">
                    {{ $summary['unverified'] ?? 0 }}
                </p>
            </div>

            <div class="p-4 bg-white border border-gray-200 rounded-xl dark:bg-neutral-900 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">Registered</p>
                <p class="mt-1 text-2xl font-bold text-blue-600">
                    {{ $summary['registered'] ?? 0 }}
                </p>
            </div>

            <div class="p-4 bg-white border border-gray-200 rounded-xl dark:bg-neutral-900 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">Completed</p>
                <p class="mt-1 text-2xl font-bold text-purple-600">
                    {{ $summary['completed'] ?? 0 }}
                </p>
            </div>

            <div class="p-4 bg-white border border-gray-200 rounded-xl dark:bg-neutral-900 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">Linked Users</p>
                <p class="mt-1 text-2xl font-bold text-cyan-600">
                    {{ $summary['linked_users'] ?? 0 }}
                </p>
            </div>

        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-neutral-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
            <form method="GET"
                  action="{{ route('onboarding-registrations.index') }}"
                  class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 items-end">

                <div class="xl:col-span-2">
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                        Search
                    </label>

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Name, phone, email..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                        Status
                    </label>

                    <select
                        name="registration_status"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                    >
                        <option value="">All Statuses</option>
                        <option value="registered" @selected(request('registration_status') === 'registered')>
                            Registered
                        </option>
                        <option value="phone_verified" @selected(request('registration_status') === 'phone_verified')>
                            Phone Verified
                        </option>
                        <option value="business_pending" @selected(request('registration_status') === 'business_pending')>
                            Business Pending
                        </option>
                        <option value="business_completed" @selected(request('registration_status') === 'business_completed')>
                            Business Completed
                        </option>
                        <option value="billing_pending" @selected(request('registration_status') === 'billing_pending')>
                            Billing Pending
                        </option>
                        <option value="billing_completed" @selected(request('registration_status') === 'billing_completed')>
                            Billing Completed
                        </option>
                        <option value="completed" @selected(request('registration_status') === 'completed')>
                            Completed
                        </option>
                        <option value="cancelled" @selected(request('registration_status') === 'cancelled')>
                            Cancelled
                        </option>
                        <option value="blocked" @selected(request('registration_status') === 'blocked')>
                            Blocked
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                        Phone Verification
                    </label>

                    <select
                        name="phone_verification"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                    >
                        <option value="">All</option>
                        <option value="verified" @selected(request('phone_verification') === 'verified')>
                            Verified
                        </option>
                        <option value="unverified" @selected(request('phone_verification') === 'unverified')>
                            Unverified
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                        User Link
                    </label>

                    <select
                        name="user_link_status"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                    >
                        <option value="">All</option>
                        <option value="linked" @selected(request('user_link_status') === 'linked')>
                            Linked
                        </option>
                        <option value="unlinked" @selected(request('user_link_status') === 'unlinked')>
                            Not Linked
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                        Last Step
                    </label>

                    <select
                        name="last_completed_step"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                    >
                        <option value="">All Steps</option>

                        @for($step = 1; $step <= 10; $step++)
                            <option value="{{ $step }}" @selected((string) request('last_completed_step') === (string) $step)>
                                Step {{ $step }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                        From Date
                    </label>

                    <input
                        type="date"
                        name="from_date"
                        value="{{ request('from_date') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                        To Date
                    </label>

                    <input
                        type="date"
                        name="to_date"
                        value="{{ request('to_date') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                        Sort By
                    </label>

                    <select
                        name="sort_by"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                    >
                        <option value="id" @selected(request('sort_by', 'id') === 'id')>ID</option>
                        <option value="name" @selected(request('sort_by') === 'name')>Name</option>
                        <option value="phone" @selected(request('sort_by') === 'phone')>Phone</option>
                        <option value="last_completed_step" @selected(request('sort_by') === 'last_completed_step')>
                            Last Step
                        </option>
                        <option value="registration_status" @selected(request('sort_by') === 'registration_status')>
                            Status
                        </option>
                        <option value="created_at" @selected(request('sort_by') === 'created_at')>
                            Created Date
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                        Direction
                    </label>

                    <select
                        name="sort_direction"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                    >
                        <option value="desc" @selected(request('sort_direction', 'desc') === 'desc')>
                            Descending
                        </option>
                        <option value="asc" @selected(request('sort_direction') === 'asc')>
                            Ascending
                        </option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        Filter
                    </button>

                    <a
                        href="{{ route('onboarding-registrations.index') }}"
                        class="flex-1 px-4 py-2 text-center bg-gray-500 text-white rounded-lg hover:bg-gray-600"
                    >
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
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Verified</th>
                        <th class="px-4 py-3">Last Step</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Created</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                    @forelse($registrations as $registration)
                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800">

                            <td class="px-4 py-3 font-medium">
                                #{{ $registration->id }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ $registration->name ?: 'No name' }}
                                </div>

                                @if($registration->user)
                                    <div class="text-xs text-green-600">
                                        Linked: {{ $registration->user->name }}
                                    </div>

                                    @if($registration->user->email)
                                        <div class="text-xs text-gray-500">
                                            {{ $registration->user->email }}
                                        </div>
                                    @endif
                                @else
                                    <div class="text-xs text-orange-600">
                                        User not linked
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ $registration->phone }}
                            </td>

                            <td class="px-4 py-3">
                                @if($registration->phone_verified_at)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                        Verified
                                    </span>

                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $registration->phone_verified_at->format('d M Y, h:i A') }}
                                    </div>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                        Unverified
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full">
                                    Step {{ $registration->last_completed_step }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                @php
                                    $statusClasses = match($registration->registration_status) {
                                        'completed' => 'bg-green-100 text-green-700',
                                        'registered' => 'bg-blue-100 text-blue-700',
                                        'phone_verified' => 'bg-cyan-100 text-cyan-700',
                                        'business_pending', 'billing_pending' => 'bg-yellow-100 text-yellow-700',
                                        'business_completed', 'billing_completed' => 'bg-purple-100 text-purple-700',
                                        'cancelled' => 'bg-gray-200 text-gray-700',
                                        'blocked' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp

                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses }}">
                                    {{ ucwords(str_replace('_', ' ', $registration->registration_status)) }}
                                </span>
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ $registration->created_at?->format('d M Y') }}

                                <div class="text-xs text-gray-500">
                                    {{ $registration->created_at?->format('h:i A') }}
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">

                                    <a href="{{ route('onboarding-registrations.show', $registration) }}"
                                       class="px-3 py-2 text-xs font-medium text-white bg-sky-600 rounded hover:bg-sky-700">
                                        View
                                    </a>

                                    <a href="{{ route('onboarding-registrations.edit', $registration) }}"
                                       class="px-3 py-2 text-xs font-medium text-white bg-yellow-600 rounded hover:bg-yellow-700">
                                        Edit
                                    </a>

                                    @if(!$registration->phone_verified_at)
                                        <form action="{{ route('onboarding-registrations.verify-phone', $registration) }}"
                                              method="POST">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="px-3 py-2 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700">
                                                Verify
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('onboarding-registrations.unverify-phone', $registration) }}"
                                              method="POST">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="px-3 py-2 text-xs font-medium text-white bg-orange-600 rounded hover:bg-orange-700">
                                                Unverify
                                            </button>
                                        </form>
                                    @endif

                                    @if($registration->registration_status !== 'completed')
                                        <form action="{{ route('onboarding-registrations.complete', $registration) }}"
                                              method="POST"
                                              onsubmit="return confirm('Mark this registration as completed?');">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="px-3 py-2 text-xs font-medium text-white bg-purple-600 rounded hover:bg-purple-700">
                                                Complete
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('onboarding-registrations.destroy', $registration) }}"
                                          method="POST"
                                          onsubmit="return confirm('Delete this onboarding registration?');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="px-3 py-2 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700">
                                            Delete
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8"
                                class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                No onboarding registrations found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $registrations->withQueryString()->links() }}
        </div>

    </div>
</x-layouts.app>