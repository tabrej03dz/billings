<x-layouts.app :title="__('Onboarding Registration Details')">
    <div class="flex flex-col gap-4">

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

        {{-- Header --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-lg">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                    Registration #{{ $onboardingRegistration->id }}
                </h1>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    {{ $onboardingRegistration->name ?: 'No name available' }}
                    ·
                    {{ $onboardingRegistration->phone }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('onboarding-registrations.index') }}"
                   class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                    ← Back
                </a>

                <a href="{{ route('onboarding-registrations.edit', $onboardingRegistration) }}"
                   class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700">
                    Edit Registration
                </a>
            </div>
        </div>

        {{-- Primary information --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

            <div class="lg:col-span-2 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">

                <div class="px-5 py-4 bg-gray-50 dark:bg-neutral-800 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">
                        Registration Information
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2">

                    <div class="p-5 border-b md:border-r border-gray-200 dark:border-gray-700">
                        <p class="text-xs uppercase text-gray-500">Name</p>
                        <p class="mt-1 font-medium text-gray-900 dark:text-white">
                            {{ $onboardingRegistration->name ?: 'Not provided' }}
                        </p>
                    </div>

                    <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                        <p class="text-xs uppercase text-gray-500">Phone</p>
                        <p class="mt-1 font-medium text-gray-900 dark:text-white">
                            {{ $onboardingRegistration->phone }}
                        </p>
                    </div>

                    <div class="p-5 border-b md:border-r border-gray-200 dark:border-gray-700">
                        <p class="text-xs uppercase text-gray-500">Phone Verification</p>

                        @if($onboardingRegistration->phone_verified_at)
                            <span class="inline-flex mt-2 px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                Verified
                            </span>

                            <p class="mt-2 text-sm text-gray-500">
                                {{ $onboardingRegistration->phone_verified_at->format('d M Y, h:i A') }}
                            </p>
                        @else
                            <span class="inline-flex mt-2 px-3 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                Unverified
                            </span>
                        @endif
                    </div>

                    <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                        <p class="text-xs uppercase text-gray-500">Last Completed Step</p>

                        <span class="inline-flex mt-2 px-3 py-1 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full">
                            Step {{ $onboardingRegistration->last_completed_step }}
                        </span>
                    </div>

                    <div class="p-5 border-b md:border-r border-gray-200 dark:border-gray-700">
                        <p class="text-xs uppercase text-gray-500">Registration Status</p>

                        <p class="mt-1 font-medium text-gray-900 dark:text-white">
                            {{ ucwords(str_replace('_', ' ', $onboardingRegistration->registration_status)) }}
                        </p>
                    </div>

                    <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                        <p class="text-xs uppercase text-gray-500">Completed At</p>

                        <p class="mt-1 font-medium text-gray-900 dark:text-white">
                            {{ $onboardingRegistration->completed_at?->format('d M Y, h:i A') ?: 'Not completed' }}
                        </p>
                    </div>

                    <div class="p-5 md:border-r border-gray-200 dark:border-gray-700">
                        <p class="text-xs uppercase text-gray-500">Created At</p>

                        <p class="mt-1 font-medium text-gray-900 dark:text-white">
                            {{ $onboardingRegistration->created_at?->format('d M Y, h:i A') }}
                        </p>
                    </div>

                    <div class="p-5">
                        <p class="text-xs uppercase text-gray-500">Updated At</p>

                        <p class="mt-1 font-medium text-gray-900 dark:text-white">
                            {{ $onboardingRegistration->updated_at?->format('d M Y, h:i A') }}
                        </p>
                    </div>

                </div>
            </div>

            {{-- Linked User --}}
            <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">

                <div class="px-5 py-4 bg-gray-50 dark:bg-neutral-800 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">
                        Linked User
                    </h2>
                </div>

                <div class="p-5">
                    @if($onboardingRegistration->user)

                        <div class="flex items-center justify-center w-16 h-16 mx-auto text-xl font-bold text-white bg-blue-600 rounded-full">
                            {{ strtoupper(substr($onboardingRegistration->user->name ?? 'U', 0, 1)) }}
                        </div>

                        <div class="mt-4 text-center">
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $onboardingRegistration->user->name }}
                            </p>

                            <p class="mt-1 text-sm text-gray-500">
                                {{ $onboardingRegistration->user->email ?: 'No email' }}
                            </p>

                            @if($onboardingRegistration->user->phone)
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $onboardingRegistration->user->phone }}
                                </p>
                            @endif

                            <p class="mt-3 text-xs text-green-600">
                                User ID: {{ $onboardingRegistration->user_id }}
                            </p>
                        </div>

                    @else
                        <div class="py-8 text-center">
                            <div class="flex items-center justify-center w-16 h-16 mx-auto text-2xl text-orange-600 bg-orange-100 rounded-full">
                                !
                            </div>

                            <p class="mt-4 font-medium text-gray-900 dark:text-white">
                                User not linked
                            </p>

                            <p class="mt-1 text-sm text-gray-500">
                                Registration अभी किसी user account से linked नहीं है।
                            </p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Business Data --}}
        <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 bg-gray-50 dark:bg-neutral-800 border-b border-gray-200 dark:border-gray-700">
                <h2 class="font-semibold text-gray-900 dark:text-white">
                    Business Data
                </h2>

                <span class="text-xs text-gray-500">
                    JSON Data
                </span>
            </div>

            <div class="p-5">
                @if(!empty($onboardingRegistration->business_data))
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($onboardingRegistration->business_data as $key => $value)
                            <div class="p-4 border border-gray-200 rounded-lg dark:border-gray-700">
                                <p class="text-xs font-medium uppercase text-gray-500">
                                    {{ ucwords(str_replace('_', ' ', $key)) }}
                                </p>

                                <div class="mt-1 text-sm text-gray-900 break-words dark:text-white">
                                    @if(is_array($value))
                                        <pre class="whitespace-pre-wrap font-sans">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @elseif(is_bool($value))
                                        {{ $value ? 'Yes' : 'No' }}
                                    @else
                                        {{ $value !== null && $value !== '' ? $value : 'Not provided' }}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <details class="mt-5">
                        <summary class="cursor-pointer text-sm font-medium text-blue-600">
                            View complete JSON
                        </summary>

                        <pre class="p-4 mt-3 overflow-auto text-xs text-gray-100 bg-gray-900 rounded-lg">{{ json_encode($onboardingRegistration->business_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </details>
                @else
                    <p class="py-6 text-center text-gray-500">
                        No business data available.
                    </p>
                @endif
            </div>
        </div>

        {{-- Billing Data --}}
        <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 bg-gray-50 dark:bg-neutral-800 border-b border-gray-200 dark:border-gray-700">
                <h2 class="font-semibold text-gray-900 dark:text-white">
                    Billing Data
                </h2>

                <span class="text-xs text-gray-500">
                    JSON Data
                </span>
            </div>

            <div class="p-5">
                @if(!empty($onboardingRegistration->billing_data))
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($onboardingRegistration->billing_data as $key => $value)
                            <div class="p-4 border border-gray-200 rounded-lg dark:border-gray-700">
                                <p class="text-xs font-medium uppercase text-gray-500">
                                    {{ ucwords(str_replace('_', ' ', $key)) }}
                                </p>

                                <div class="mt-1 text-sm text-gray-900 break-words dark:text-white">
                                    @if(is_array($value))
                                        <pre class="whitespace-pre-wrap font-sans">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @elseif(is_bool($value))
                                        {{ $value ? 'Yes' : 'No' }}
                                    @else
                                        {{ $value !== null && $value !== '' ? $value : 'Not provided' }}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <details class="mt-5">
                        <summary class="cursor-pointer text-sm font-medium text-blue-600">
                            View complete JSON
                        </summary>

                        <pre class="p-4 mt-3 overflow-auto text-xs text-gray-100 bg-gray-900 rounded-lg">{{ json_encode($onboardingRegistration->billing_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </details>
                @else
                    <p class="py-6 text-center text-gray-500">
                        No billing data available.
                    </p>
                @endif
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
            <h2 class="font-semibold text-gray-900 dark:text-white">
                Quick Actions
            </h2>

            <div class="flex flex-wrap gap-3 mt-4">

                @if(!$onboardingRegistration->phone_verified_at)
                    <form action="{{ route('onboarding-registrations.verify-phone', $onboardingRegistration) }}"
                          method="POST">
                        @csrf
                        @method('PATCH')

                        <button class="px-4 py-2 text-sm text-white bg-green-600 rounded-lg hover:bg-green-700">
                            Mark Phone Verified
                        </button>
                    </form>
                @else
                    <form action="{{ route('onboarding-registrations.unverify-phone', $onboardingRegistration) }}"
                          method="POST">
                        @csrf
                        @method('PATCH')

                        <button class="px-4 py-2 text-sm text-white bg-orange-600 rounded-lg hover:bg-orange-700">
                            Mark Phone Unverified
                        </button>
                    </form>
                @endif

                @if($onboardingRegistration->registration_status !== 'completed')
                    <form action="{{ route('onboarding-registrations.complete', $onboardingRegistration) }}"
                          method="POST"
                          onsubmit="return confirm('Mark registration as completed?');">
                        @csrf
                        @method('PATCH')

                        <button class="px-4 py-2 text-sm text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                            Mark Completed
                        </button>
                    </form>
                @endif

                <form action="{{ route('onboarding-registrations.destroy', $onboardingRegistration) }}"
                      method="POST"
                      onsubmit="return confirm('Delete this onboarding registration?');">
                    @csrf
                    @method('DELETE')

                    <button class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700">
                        Delete Registration
                    </button>
                </form>

            </div>
        </div>

    </div>
</x-layouts.app>