
<x-layouts.app :title="__('Edit User Plan')">

    <div class="flex flex-col gap-5">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-[#BFE0E0] dark:bg-[#354A54] p-6">

            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Edit User Plan
                </h1>

                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    Update subscription, limits, dates and permissions.
                </p>
            </div>

            <a
                href="{{ route('user-plans.index', ['business_id' => $userPlan->business_id]) }}"
                class="inline-flex items-center justify-center rounded-xl bg-gray-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-900 transition"
            >
                Back to Plans
            </a>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="rounded-xl border border-red-300 bg-red-50 p-5 dark:border-red-800 dark:bg-red-950/30">

                <h3 class="font-bold text-red-700 dark:text-red-400 mb-2">
                    Please fix the following errors
                </h3>

                <ul class="list-disc list-inside space-y-1 text-sm text-red-600 dark:text-red-400">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>
        @endif

        {{-- Edit Form --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

            <form
                action="{{ route('user-plans.update', $userPlan->id) }}"
                method="POST"
                class="space-y-7"
            >
                @csrf
                @method('PUT')

                {{-- Subscription Details --}}
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                        Subscription Details
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Select the business, user and subscription plan.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Business --}}
                    <div>
                        <label
                            for="business_id"
                            class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200"
                        >
                            Business
                        </label>

                        <select
                            name="business_id"
                            id="business_id"
                            class="w-full rounded-xl border-gray-300 bg-white dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                        >
                            <option value="">Select Business</option>

                            @foreach ($businesses as $business)
                                <option
                                    value="{{ $business->id }}"
                                    @selected(old('business_id', $userPlan->business_id) == $business->id)
                                >
                                    {{ $business->name ?? $business->business_name ?? 'Business #'.$business->id }}
                                </option>
                            @endforeach

                        </select>

                        @error('business_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- User --}}
                    <div>
                        <label
                            for="user_id"
                            class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200"
                        >
                            User
                        </label>

                        <select
                            name="user_id"
                            id="user_id"
                            class="w-full rounded-xl border-gray-300 bg-white dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                        >
                            <option value="">Select User</option>

                            @foreach ($users as $user)
                                <option
                                    value="{{ $user->id }}"
                                    @selected(old('user_id', $userPlan->user_id) == $user->id)
                                >
                                    {{ $user->name }}
                                    {{ $user->email ? '('.$user->email.')' : '' }}
                                </option>
                            @endforeach

                        </select>

                        @error('user_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Plan --}}
                    <div>
                        <label
                            for="plan_id"
                            class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200"
                        >
                            Subscription Plan
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            name="plan_id"
                            id="plan_id"
                            required
                            class="w-full rounded-xl border-gray-300 bg-white dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                        >
                            <option value="">Select Plan</option>

                            @foreach ($plans as $plan)
                                <option
                                    value="{{ $plan->id }}"
                                    data-duration="{{ $plan->duration_days ?? 0 }}"
                                    @selected(old('plan_id', $userPlan->plan_id) == $plan->id)
                                >
                                    {{ $plan->name }}
                                    - ₹{{ number_format($plan->price ?? 0, 2) }}
                                    / {{ $plan->duration_days ?? 0 }} days
                                </option>
                            @endforeach

                        </select>

                        @error('plan_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            Subscription Status
                        </label>

                        <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-neutral-700 dark:bg-neutral-800">

                            <input
                                type="hidden"
                                name="status"
                                value="0"
                            >

                            <label class="inline-flex items-center gap-3 cursor-pointer">

                                <input
                                    type="checkbox"
                                    name="status"
                                    id="status"
                                    value="1"
                                    @checked(old('status', $userPlan->status))
                                    class="h-5 w-5 rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >

                                <span
                                    id="status_text"
                                    class="text-sm font-semibold text-gray-700 dark:text-gray-200"
                                >
                                    {{ old('status', $userPlan->status) ? 'Active' : 'Inactive' }}
                                </span>

                            </label>

                        </div>

                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="border-t border-gray-200 dark:border-neutral-700"></div>

                {{-- Usage Limits --}}
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                        Usage Limits
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Configure allowed offices and users for this subscription.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Number of Offices --}}
                    <div>
                        <label
                            for="number_of_office"
                            class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200"
                        >
                            Number of Offices
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="number"
                            name="number_of_office"
                            id="number_of_office"
                            min="1"
                            required
                            value="{{ old('number_of_office', $userPlan->number_of_office ?? 1) }}"
                            class="w-full rounded-xl border-gray-300 bg-white dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                        >

                        @error('number_of_office')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Number of Users --}}
                    <div>
                        <label
                            for="number_of_user"
                            class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200"
                        >
                            Number of Users
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="number"
                            name="number_of_user"
                            id="number_of_user"
                            min="1"
                            required
                            value="{{ old('number_of_user', $userPlan->number_of_user ?? 1) }}"
                            class="w-full rounded-xl border-gray-300 bg-white dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                        >

                        @error('number_of_user')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="border-t border-gray-200 dark:border-neutral-700"></div>

                {{-- Subscription Period --}}
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                        Subscription Period
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Expiry date is automatically calculated when plan or start date changes.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Start Date --}}
                    <div>
                        <label
                            for="start_date"
                            class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200"
                        >
                            Start Date
                        </label>

                        <input
                            type="date"
                            name="start_date"
                            id="start_date"
                            value="{{ old('start_date', $userPlan->start_date ? \Carbon\Carbon::parse($userPlan->start_date)->format('Y-m-d') : now()->toDateString()) }}"
                            class="w-full rounded-xl border-gray-300 bg-white dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                        >

                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Expiry Date --}}
                    <div>
                        <label
                            for="expiry_date"
                            class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200"
                        >
                            Expiry Date
                        </label>

                        <input
                            type="date"
                            name="expiry_date"
                            id="expiry_date"
                            value="{{ old('expiry_date', $userPlan->expiry_date ? \Carbon\Carbon::parse($userPlan->expiry_date)->format('Y-m-d') : '') }}"
                            class="w-full rounded-xl border-gray-300 bg-white dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                        >

                        @error('expiry_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-3 border-t border-gray-200 pt-6 dark:border-neutral-700">

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-green-600 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-green-700 transition"
                    >
                        Update User Plan
                    </button>

                    <a
                        href="{{ route('user-plans.index', ['business_id' => $userPlan->business_id]) }}"
                        class="inline-flex items-center justify-center rounded-xl bg-gray-100 px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-200 dark:bg-neutral-800 dark:text-gray-200 dark:hover:bg-neutral-700 transition"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const planSelect = document.getElementById('plan_id');
            const startDateInput = document.getElementById('start_date');
            const expiryDateInput = document.getElementById('expiry_date');
            const statusCheckbox = document.getElementById('status');
            const statusText = document.getElementById('status_text');

            function updateExpiryDate() {

                const selectedOption =
                    planSelect.options[planSelect.selectedIndex];

                const duration = parseInt(
                    selectedOption?.dataset.duration || '0',
                    10
                );

                const startDate = startDateInput.value;

                if (!startDate || !selectedOption?.value) {
                    expiryDateInput.value = '';
                    return;
                }

                const date = new Date(startDate + 'T00:00:00');

                if (isNaN(date.getTime())) {
                    return;
                }

                date.setDate(
                    date.getDate() + Math.max(0, duration)
                );

                const year = date.getFullYear();

                const month = String(
                    date.getMonth() + 1
                ).padStart(2, '0');

                const day = String(
                    date.getDate()
                ).padStart(2, '0');

                expiryDateInput.value =
                    `${year}-${month}-${day}`;
            }

            if (planSelect) {
                planSelect.addEventListener(
                    'change',
                    updateExpiryDate
                );
            }

            if (startDateInput) {
                startDateInput.addEventListener(
                    'change',
                    updateExpiryDate
                );
            }

            function updateStatusText() {
                if (statusText && statusCheckbox) {
                    statusText.textContent =
                        statusCheckbox.checked
                            ? 'Active'
                            : 'Inactive';
                }
            }

            if (statusCheckbox) {
                statusCheckbox.addEventListener(
                    'change',
                    updateStatusText
                );
            }

            // Existing expiry date preserve on initial load.
            // Recalculate only when plan or start date changes.

        });
    </script>

</x-layouts.app>
