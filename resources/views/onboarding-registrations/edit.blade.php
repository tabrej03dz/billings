<x-layouts.app :title="__('Edit Onboarding Registration')">
    <div class="flex flex-col gap-4">

        @if(session('error'))
            <div class="p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-lg bg-red-50 text-red-700 border border-red-200">
                <p class="font-semibold">Please fix the following errors:</p>

                <ul class="mt-2 list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-lg">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                    Edit Registration
                </h1>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    Registration #{{ $onboardingRegistration->id }}
                    ·
                    {{ $onboardingRegistration->phone }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('onboarding-registrations.show', $onboardingRegistration) }}"
                   class="px-4 py-2 text-sm font-medium text-white bg-sky-600 rounded-lg hover:bg-sky-700">
                    View
                </a>

                <a href="{{ route('onboarding-registrations.index') }}"
                   class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                    ← Back
                </a>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('onboarding-registrations.update', $onboardingRegistration) }}"
            class="flex flex-col gap-4"
        >
            @csrf
            @method('PUT')

            {{-- Basic Details --}}
            <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">

                <div class="px-5 py-4 bg-gray-50 dark:bg-neutral-800 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">
                        Basic Information
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                    <div>
                        <label for="name"
                               class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $onboardingRegistration->name) }}"
                            placeholder="Enter customer name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                        >

                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone"
                               class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Phone Number
                            <span class="text-red-600">*</span>
                        </label>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            maxlength="10"
                            inputmode="numeric"
                            value="{{ old('phone', $onboardingRegistration->phone) }}"
                            placeholder="10 digit phone number"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                            required
                        >

                        @error('phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="user_id"
                               class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Linked User ID
                        </label>

                        <input
                            type="number"
                            id="user_id"
                            name="user_id"
                            min="1"
                            value="{{ old('user_id', $onboardingRegistration->user_id) }}"
                            placeholder="Enter existing user ID"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                        >

                        <p class="mt-1 text-xs text-gray-500">
                            User unlink करने के लिए field blank कर दें।
                        </p>

                        @if($onboardingRegistration->user)
                            <div class="p-2 mt-2 text-sm text-green-700 border border-green-200 rounded-lg bg-green-50">
                                Currently linked:
                                <strong>{{ $onboardingRegistration->user->name }}</strong>

                                @if($onboardingRegistration->user->email)
                                    · {{ $onboardingRegistration->user->email }}
                                @endif
                            </div>
                        @endif

                        @error('user_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone_verified_at"
                               class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Phone Verified At
                        </label>

                        <input
                            type="datetime-local"
                            id="phone_verified_at"
                            name="phone_verified_at"
                            value="{{ old(
                                'phone_verified_at',
                                $onboardingRegistration->phone_verified_at?->format('Y-m-d\TH:i')
                            ) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                        >

                        <p class="mt-1 text-xs text-gray-500">
                            Unverified करने के लिए field blank छोड़ दें।
                        </p>

                        @error('phone_verified_at')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- Registration Progress --}}
            <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">

                <div class="px-5 py-4 bg-gray-50 dark:bg-neutral-800 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">
                        Registration Progress
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-3">

                    <div>
                        <label for="last_completed_step"
                               class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Last Completed Step
                            <span class="text-red-600">*</span>
                        </label>

                        <input
                            type="number"
                            id="last_completed_step"
                            name="last_completed_step"
                            min="1"
                            max="255"
                            value="{{ old('last_completed_step', $onboardingRegistration->last_completed_step) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                            required
                        >

                        @error('last_completed_step')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="registration_status"
                               class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Registration Status
                            <span class="text-red-600">*</span>
                        </label>

                        <select
                            id="registration_status"
                            name="registration_status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                            required
                        >
                            @php
                                $selectedStatus = old(
                                    'registration_status',
                                    $onboardingRegistration->registration_status
                                );
                            @endphp

                            <option value="registered" @selected($selectedStatus === 'registered')>
                                Registered
                            </option>

                            <option value="phone_verified" @selected($selectedStatus === 'phone_verified')>
                                Phone Verified
                            </option>

                            <option value="business_pending" @selected($selectedStatus === 'business_pending')>
                                Business Pending
                            </option>

                            <option value="business_completed" @selected($selectedStatus === 'business_completed')>
                                Business Completed
                            </option>

                            <option value="billing_pending" @selected($selectedStatus === 'billing_pending')>
                                Billing Pending
                            </option>

                            <option value="billing_completed" @selected($selectedStatus === 'billing_completed')>
                                Billing Completed
                            </option>

                            <option value="completed" @selected($selectedStatus === 'completed')>
                                Completed
                            </option>

                            <option value="cancelled" @selected($selectedStatus === 'cancelled')>
                                Cancelled
                            </option>

                            <option value="blocked" @selected($selectedStatus === 'blocked')>
                                Blocked
                            </option>
                        </select>

                        @error('registration_status')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="completed_at"
                               class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Completed At
                        </label>

                        <input
                            type="datetime-local"
                            id="completed_at"
                            name="completed_at"
                            value="{{ old(
                                'completed_at',
                                $onboardingRegistration->completed_at?->format('Y-m-d\TH:i')
                            ) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                        >

                        <p class="mt-1 text-xs text-gray-500">
                            Completed status select करने पर blank value automatically current time हो सकती है।
                        </p>

                        @error('completed_at')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- Business Data --}}
            <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">

                <div class="px-5 py-4 bg-gray-50 dark:bg-neutral-800 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">
                        Business Data
                    </h2>

                    <p class="mt-1 text-xs text-gray-500">
                        Valid JSON format में data enter करें।
                    </p>
                </div>

                <div class="p-5">
                    <textarea
                        id="business_data"
                        name="business_data"
                        rows="14"
                        spellcheck="false"
                        placeholder='{
    "business_name": "Example Business",
    "business_type": "Retail",
    "gst_enabled": true
}'
                        class="w-full px-3 py-3 font-mono text-sm border border-gray-300 rounded-lg dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                    >{{ old(
                        'business_data',
                        $onboardingRegistration->business_data
                            ? json_encode(
                                $onboardingRegistration->business_data,
                                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                            )
                            : ''
                    ) }}</textarea>

                    @error('business_data')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Billing Data --}}
            <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">

                <div class="px-5 py-4 bg-gray-50 dark:bg-neutral-800 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">
                        Billing Data
                    </h2>

                    <p class="mt-1 text-xs text-gray-500">
                        Valid JSON format में data enter करें।
                    </p>
                </div>

                <div class="p-5">
                    <textarea
                        id="billing_data"
                        name="billing_data"
                        rows="14"
                        spellcheck="false"
                        placeholder='{
    "invoice_prefix": "INV",
    "currency": "INR",
    "tax_enabled": true
}'
                        class="w-full px-3 py-3 font-mono text-sm border border-gray-300 rounded-lg dark:bg-neutral-800 dark:border-gray-600 dark:text-white"
                    >{{ old(
                        'billing_data',
                        $onboardingRegistration->billing_data
                            ? json_encode(
                                $onboardingRegistration->billing_data,
                                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                            )
                            : ''
                    ) }}</textarea>

                    @error('billing_data')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Buttons --}}
            <div class="sticky bottom-0 z-10 flex flex-col gap-3 p-4 bg-white border border-gray-200 rounded-xl shadow-lg dark:bg-neutral-900 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-end">

                <a href="{{ route('onboarding-registrations.show', $onboardingRegistration) }}"
                   class="px-5 py-2 text-sm font-medium text-center text-white bg-gray-500 rounded-lg hover:bg-gray-600">
                    Cancel
                </a>

                <button
                    type="submit"
                    class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
                >
                    Update Registration
                </button>

            </div>

        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const phoneInput = document.getElementById('phone');
            const statusInput = document.getElementById('registration_status');
            const completedAtInput = document.getElementById('completed_at');

            phoneInput?.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 10);
            });

            statusInput?.addEventListener('change', function () {
                if (this.value === 'completed' && !completedAtInput.value) {
                    const now = new Date();
                    const timezoneOffset = now.getTimezoneOffset() * 60000;
                    const localDateTime = new Date(now.getTime() - timezoneOffset)
                        .toISOString()
                        .slice(0, 16);

                    completedAtInput.value = localDateTime;
                }

                if (this.value !== 'completed') {
                    completedAtInput.value = '';
                }
            });
        });
    </script>
</x-layouts.app>