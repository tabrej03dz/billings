<x-layouts.app :title="__('Create User Plan')">

    {{-- Searchable Select CSS --}}
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.default.min.css"
    >

    <style>
        .ts-wrapper {
            width: 100%;
        }

        .ts-control {
            min-height: 44px !important;
            border-radius: 0.75rem !important;
            border: 1px solid #d1d5db !important;
            padding: 9px 12px !important;
            background: #ffffff !important;
            box-shadow: none !important;
            font-size: 14px !important;
        }

        .ts-wrapper.focus .ts-control {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12) !important;
        }

        .ts-dropdown {
            border-radius: 0.75rem !important;
            border: 1px solid #e5e7eb !important;
            overflow: hidden !important;
            margin-top: 5px !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.10) !important;
        }

        .ts-dropdown .option {
            padding: 10px 12px !important;
            font-size: 14px !important;
        }

        .ts-dropdown .active {
            background: #eff6ff !important;
            color: #1d4ed8 !important;
        }

        .dark .ts-control {
            background: #262626 !important;
            border-color: #404040 !important;
            color: #ffffff !important;
        }

        .dark .ts-control input,
        .dark .ts-control .item {
            color: #ffffff !important;
        }

        .dark .ts-dropdown {
            background: #262626 !important;
            border-color: #404040 !important;
            color: white !important;
        }

        .dark .ts-dropdown .option {
            color: #e5e7eb !important;
        }

        .dark .ts-dropdown .active {
            background: #404040 !important;
            color: #ffffff !important;
        }
    </style>


    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-4">

        <div class="flex flex-col gap-5">

            {{-- Header --}}
            <div
                class="relative overflow-hidden rounded-2xl
                       border border-cyan-100
                       bg-gradient-to-r from-cyan-50 via-blue-50 to-indigo-50
                       dark:border-neutral-700
                       dark:from-[#293d46]
                       dark:via-[#30434c]
                       dark:to-[#303946]"
            >

                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 sm:p-6">

                    <div class="flex items-center gap-4">

                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center
                                   rounded-xl bg-blue-600 text-white shadow-lg"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                        </div>

                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                                Create User Plan
                            </h1>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                @if($selectedBusinessId)
                                    Selected business ke liye subscription plan assign karein.
                                @else
                                    Business ya user ke liye subscription plan assign karein.
                                @endif
                            </p>
                        </div>

                    </div>


                    <a
                        href="{{ $selectedBusinessId
                            ? route('user-plans.index1', $selectedBusinessId)
                            : route('user-plans.index') }}"
                        class="inline-flex items-center justify-center gap-2
                               rounded-xl border border-gray-200
                               bg-white px-4 py-2.5
                               text-sm font-medium text-gray-700
                               hover:bg-gray-50
                               dark:border-neutral-700
                               dark:bg-neutral-800
                               dark:text-gray-200
                               dark:hover:bg-neutral-700"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>

                        Back
                    </a>

                </div>
            </div>


            {{-- Validation Errors --}}
            @if ($errors->any())
                <div
                    class="rounded-xl border border-red-200
                           bg-red-50 p-4
                           dark:border-red-900/50
                           dark:bg-red-950/20"
                >
                    <p class="font-semibold text-red-700 dark:text-red-400">
                        Please fix the following errors:
                    </p>

                    <ul class="mt-2 list-disc pl-5 space-y-1 text-sm text-red-600 dark:text-red-400">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <form
                action="{{ route('user-plans.store') }}"
                method="POST"
                id="userPlanForm"
                class="space-y-5"
            >
                @csrf


                {{-- ================================================= --}}
                {{-- Business / User --}}
                {{-- ================================================= --}}

                <div
                    class="overflow-hidden rounded-2xl
                           border border-gray-200
                           bg-white shadow-sm
                           dark:border-neutral-700
                           dark:bg-neutral-900"
                >

                    <div class="border-b border-gray-100 px-5 py-4 dark:border-neutral-800">

                        <h2 class="font-semibold text-gray-900 dark:text-white">
                            @if($selectedBusinessId)
                                Business
                            @else
                                Business & User
                            @endif
                        </h2>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            @if($selectedBusinessId)
                                Plan selected business par assign hoga.
                            @else
                                Business aur optional user select karein.
                            @endif
                        </p>

                    </div>


                    <div class="p-5">

                        @if($selectedBusinessId)

                            {{-- ================================================= --}}
                            {{-- BUSINESS PAGE SE AAYE HAIN --}}
                            {{-- Business ID Hidden --}}
                            {{-- User List Bilkul Nahi --}}
                            {{-- ================================================= --}}

                            <input
                                type="hidden"
                                name="business_id"
                                id="business_id"
                                value="{{ old('business_id', $selectedBusinessId) }}"
                            >

                            <div class="grid grid-cols-1 gap-5">

                                <div>

                                    <label
                                        class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200"
                                    >
                                        Business
                                    </label>

                                    <div
                                        class="flex min-h-[44px] items-center gap-3
                                               rounded-xl border border-gray-200
                                               bg-gray-50 px-4
                                               dark:border-neutral-700
                                               dark:bg-neutral-800"
                                    >

                                        <div
                                            class="flex h-9 w-9 shrink-0 items-center justify-center
                                                   rounded-lg bg-blue-100 text-blue-700
                                                   dark:bg-blue-900/40 dark:text-blue-300"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"
                                                />
                                            </svg>
                                        </div>

                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{
                                                    $selectedBusiness->name
                                                    ?? $selectedBusiness->business_name
                                                    ?? 'Business #' . $selectedBusinessId
                                                }}
                                            </p>

                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Business ID: {{ $selectedBusinessId }}
                                            </p>
                                        </div>

                                    </div>

                                    @error('business_id')
                                        <p class="mt-1.5 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror

                                </div>

                            </div>


                            <div
                                class="mt-5 rounded-xl
                                       border border-green-100
                                       bg-green-50 p-4
                                       text-sm text-green-700
                                       dark:border-green-900/40
                                       dark:bg-green-950/20
                                       dark:text-green-300"
                            >
                                Yeh plan directly
                                <strong>
                                    {{
                                        $selectedBusiness->name
                                        ?? $selectedBusiness->business_name
                                        ?? 'selected business'
                                    }}
                                </strong>
                                par assign hoga.
                            </div>


                        @else

                            {{-- ================================================= --}}
                            {{-- NORMAL CREATE PAGE --}}
                            {{-- Business + User Dropdown --}}
                            {{-- ================================================= --}}

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                                {{-- Business --}}
                                <div>

                                    <label
                                        for="business_id"
                                        class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200"
                                    >
                                        Business
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select
                                        name="business_id"
                                        id="business_id"
                                        placeholder="Search business..."
                                    >

                                        <option value="">
                                            Select Business
                                        </option>

                                        @foreach($businesses as $business)

                                            <option
                                                value="{{ $business->id }}"
                                                {{
                                                    old('business_id') == $business->id
                                                        ? 'selected'
                                                        : ''
                                                }}
                                            >
                                                {{
                                                    $business->name
                                                    ?? $business->business_name
                                                    ?? 'Business #' . $business->id
                                                }}
                                            </option>

                                        @endforeach

                                    </select>

                                    @error('business_id')
                                        <p class="mt-1.5 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror

                                </div>


                                {{-- User --}}
                                <div>

                                    <label
                                        for="user_id"
                                        class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200"
                                    >
                                        User

                                        <span class="font-normal text-gray-400">
                                            (Optional)
                                        </span>
                                    </label>

                                    <select
                                        name="user_id"
                                        id="user_id"
                                        placeholder="Search user..."
                                    >

                                        <option value="">
                                            Select User
                                        </option>

                                        @foreach($users as $user)

                                            <option
                                                value="{{ $user->id }}"
                                                {{
                                                    old('user_id') == $user->id
                                                        ? 'selected'
                                                        : ''
                                                }}
                                            >
                                                {{ $user->name }}

                                                @if($user->email)
                                                    ({{ $user->email }})
                                                @endif
                                            </option>

                                        @endforeach

                                    </select>

                                    @error('user_id')
                                        <p class="mt-1.5 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror

                                </div>

                            </div>


                            <div
                                class="mt-5 rounded-xl
                                       border border-blue-100
                                       bg-blue-50 p-4
                                       text-sm text-blue-700
                                       dark:border-blue-900/40
                                       dark:bg-blue-950/20
                                       dark:text-blue-300"
                            >
                                Agar sirf <strong>Business</strong> select karenge aur
                                User blank rakhenge to plan business level par assign hoga.
                                User select karenge to specific user ke liye plan assign hoga.
                            </div>

                        @endif

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- Plan Information --}}
                {{-- ================================================= --}}

                <div
                    class="overflow-hidden rounded-2xl
                           border border-gray-200
                           bg-white shadow-sm
                           dark:border-neutral-700
                           dark:bg-neutral-900"
                >

                    <div class="border-b border-gray-100 px-5 py-4 dark:border-neutral-800">

                        <h2 class="font-semibold text-gray-900 dark:text-white">
                            Plan Details
                        </h2>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Plan select karne par office, user aur expiry automatically fill honge.
                        </p>

                    </div>


                    <div class="p-5">

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

                            {{-- Plan --}}
                            <div>

                                <label
                                    for="plan_id"
                                    class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200"
                                >
                                    Plan
                                    <span class="text-red-500">*</span>
                                </label>

                                <select
                                    name="plan_id"
                                    id="plan_id"
                                    required
                                    class="w-full rounded-xl
                                           border-gray-300
                                           dark:border-neutral-700
                                           dark:bg-neutral-800
                                           dark:text-white"
                                >

                                    <option value="">
                                        Select Plan
                                    </option>

                                    @foreach($plans as $plan)

                                        <option
                                            value="{{ $plan->id }}"

                                            data-duration="{{ (int) ($plan->duration_days ?? 0) }}"

                                            data-office="{{ (int) ($plan->number_of_office ?? 1) }}"

                                            data-user="{{ (int) ($plan->number_of_user ?? 1) }}"

                                            data-price="{{ (float) ($plan->price ?? 0) }}"

                                            {{
                                                old('plan_id') == $plan->id
                                                    ? 'selected'
                                                    : ''
                                            }}
                                        >
                                            {{ $plan->name }}
                                            -
                                            ₹{{ number_format($plan->price ?? 0, 2) }}
                                            /
                                            {{ $plan->duration_days ?? 0 }} Days
                                        </option>

                                    @endforeach

                                </select>

                                @error('plan_id')
                                    <p class="mt-1.5 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Number Of Offices --}}
                            <div>

                                <label
                                    for="number_of_office"
                                    class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200"
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
                                    value="{{ old('number_of_office', 1) }}"
                                    class="w-full rounded-xl
                                           border-gray-300
                                           dark:border-neutral-700
                                           dark:bg-neutral-800
                                           dark:text-white"
                                    placeholder="Example: 1"
                                >

                                @error('number_of_office')
                                    <p class="mt-1.5 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Number Of Users --}}
                            <div>

                                <label
                                    for="number_of_user"
                                    class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200"
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
                                    value="{{ old('number_of_user', 1) }}"
                                    class="w-full rounded-xl
                                           border-gray-300
                                           dark:border-neutral-700
                                           dark:bg-neutral-800
                                           dark:text-white"
                                    placeholder="Example: 5"
                                >

                                @error('number_of_user')
                                    <p class="mt-1.5 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>


                        {{-- Plan Preview --}}
                        <div
                            id="planPreview"
                            class="hidden mt-5 grid grid-cols-2 lg:grid-cols-4 gap-3"
                        >

                            <div class="rounded-xl bg-gray-50 p-4 dark:bg-neutral-800">

                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Price
                                </p>

                                <p
                                    id="previewPrice"
                                    class="mt-1 text-lg font-bold text-gray-900 dark:text-white"
                                >
                                    ₹0.00
                                </p>

                            </div>


                            <div class="rounded-xl bg-gray-50 p-4 dark:bg-neutral-800">

                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Duration
                                </p>

                                <p
                                    id="previewDuration"
                                    class="mt-1 text-lg font-bold text-gray-900 dark:text-white"
                                >
                                    0 Days
                                </p>

                            </div>


                            <div class="rounded-xl bg-gray-50 p-4 dark:bg-neutral-800">

                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Offices
                                </p>

                                <p
                                    id="previewOffice"
                                    class="mt-1 text-lg font-bold text-gray-900 dark:text-white"
                                >
                                    1
                                </p>

                            </div>


                            <div class="rounded-xl bg-gray-50 p-4 dark:bg-neutral-800">

                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Users
                                </p>

                                <p
                                    id="previewUser"
                                    class="mt-1 text-lg font-bold text-gray-900 dark:text-white"
                                >
                                    1
                                </p>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- Date / Status --}}
                {{-- ================================================= --}}

                <div
                    class="overflow-hidden rounded-2xl
                           border border-gray-200
                           bg-white shadow-sm
                           dark:border-neutral-700
                           dark:bg-neutral-900"
                >

                    <div class="border-b border-gray-100 px-5 py-4 dark:border-neutral-800">

                        <h2 class="font-semibold text-gray-900 dark:text-white">
                            Plan Validity
                        </h2>

                    </div>


                    <div class="p-5">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                            {{-- Start Date --}}
                            <div>

                                <label
                                    for="start_date"
                                    class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200"
                                >
                                    Start Date
                                </label>

                                <input
                                    type="date"
                                    name="start_date"
                                    id="start_date"
                                    value="{{ old('start_date', now()->toDateString()) }}"
                                    class="w-full rounded-xl
                                           border-gray-300
                                           dark:border-neutral-700
                                           dark:bg-neutral-800
                                           dark:text-white"
                                >

                                @error('start_date')
                                    <p class="mt-1.5 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Expiry Date --}}
                            <div>

                                <label
                                    for="expiry_date"
                                    class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200"
                                >
                                    Expiry Date
                                </label>

                                <input
                                    type="date"
                                    name="expiry_date"
                                    id="expiry_date"
                                    value="{{ old('expiry_date') }}"
                                    class="w-full rounded-xl
                                           border-gray-300
                                           dark:border-neutral-700
                                           dark:bg-neutral-800
                                           dark:text-white"
                                >

                                @error('expiry_date')
                                    <p class="mt-1.5 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Status --}}
                            <div>

                                <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    Status
                                </label>

                                <label
                                    class="flex min-h-[44px]
                                           cursor-pointer items-center justify-between
                                           rounded-xl border border-gray-300
                                           px-4
                                           dark:border-neutral-700
                                           dark:bg-neutral-800"
                                >

                                    <div>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white">
                                            Active
                                        </p>

                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Plan immediately active rahega.
                                        </p>
                                    </div>


                                    <div>

                                        <input
                                            type="hidden"
                                            name="status"
                                            value="0"
                                        >

                                        <input
                                            type="checkbox"
                                            name="status"
                                            value="1"
                                            {{ old('status', 1) ? 'checked' : '' }}
                                            class="h-5 w-5 rounded
                                                   border-gray-300
                                                   text-green-600
                                                   focus:ring-green-500"
                                        >

                                    </div>

                                </label>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- Buttons --}}
                {{-- ================================================= --}}

                <div
                    class="flex flex-col-reverse sm:flex-row
                           sm:items-center sm:justify-end
                           gap-3
                           rounded-2xl
                           border border-gray-200
                           bg-white p-4
                           shadow-sm
                           dark:border-neutral-700
                           dark:bg-neutral-900"
                >

                    <a
                        href="{{ $selectedBusinessId
                            ? route('user-plans.index1', $selectedBusinessId)
                            : route('user-plans.index') }}"
                        class="inline-flex items-center justify-center
                               rounded-xl bg-gray-100
                               px-5 py-2.5
                               text-sm font-semibold
                               text-gray-700
                               hover:bg-gray-200
                               dark:bg-neutral-800
                               dark:text-gray-200
                               dark:hover:bg-neutral-700"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        id="saveButton"
                        class="inline-flex items-center justify-center gap-2
                               rounded-xl bg-green-600
                               px-6 py-2.5
                               text-sm font-semibold text-white
                               hover:bg-green-700
                               focus:ring-4
                               focus:ring-green-200"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>

                        Save User Plan

                    </button>

                </div>

            </form>

        </div>

    </div>


    {{-- Tom Select --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>


    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /*
            |--------------------------------------------------------------------------
            | Searchable Business
            |--------------------------------------------------------------------------
            */

            const businessSelect = document.querySelector('select#business_id');

            if (businessSelect) {
                new TomSelect(businessSelect, {
                    create: false,
                    allowEmptyOption: true,
                    maxItems: 1,

                    placeholder: 'Search business name...',

                    searchField: ['text'],

                    plugins: {
                        clear_button: {
                            title: 'Clear'
                        }
                    }
                });
            }


            /*
            |--------------------------------------------------------------------------
            | Searchable User
            |--------------------------------------------------------------------------
            */

            const userSelect = document.querySelector('select#user_id');

            if (userSelect) {
                new TomSelect(userSelect, {
                    create: false,
                    allowEmptyOption: true,
                    maxItems: 1,

                    placeholder: 'Search name or email...',

                    searchField: ['text'],

                    plugins: {
                        clear_button: {
                            title: 'Clear'
                        }
                    }
                });
            }


            /*
            |--------------------------------------------------------------------------
            | Plan
            |--------------------------------------------------------------------------
            */

            const planSelect =
                document.getElementById('plan_id');

            const officeInput =
                document.getElementById('number_of_office');

            const userInput =
                document.getElementById('number_of_user');

            const startDateInput =
                document.getElementById('start_date');

            const expiryDateInput =
                document.getElementById('expiry_date');


            const planPreview =
                document.getElementById('planPreview');

            const previewPrice =
                document.getElementById('previewPrice');

            const previewDuration =
                document.getElementById('previewDuration');

            const previewOffice =
                document.getElementById('previewOffice');

            const previewUser =
                document.getElementById('previewUser');


            function getSelectedPlan() {

                if (!planSelect || !planSelect.value) {
                    return null;
                }

                return planSelect.options[
                    planSelect.selectedIndex
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | Office + User
            |--------------------------------------------------------------------------
            */

            function updatePlanLimits() {

                const option =
                    getSelectedPlan();

                if (!option) {
                    return;
                }


                let offices = parseInt(
                    option.dataset.office || 1
                );

                let users = parseInt(
                    option.dataset.user || 1
                );


                if (offices < 1) {
                    offices = 1;
                }

                if (users < 1) {
                    users = 1;
                }


                if (officeInput) {
                    officeInput.value = offices;
                }

                if (userInput) {
                    userInput.value = users;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Expiry Date
            |--------------------------------------------------------------------------
            */

            function updateExpiryDate() {

                const option =
                    getSelectedPlan();

                if (!option) {
                    return;
                }


                const duration =
                    parseInt(
                        option.dataset.duration || 0
                    );


                const startDate =
                    startDateInput
                        ? startDateInput.value
                        : null;


                if (
                    duration <= 0 ||
                    !startDate
                ) {
                    return;
                }


                /*
                 * Date manually parse kar rahe hain
                 * timezone problem avoid karne ke liye.
                 */

                const parts =
                    startDate.split('-');


                if (parts.length !== 3) {
                    return;
                }


                const date = new Date(
                    parseInt(parts[0]),
                    parseInt(parts[1]) - 1,
                    parseInt(parts[2])
                );


                date.setDate(
                    date.getDate() + duration
                );


                const year =
                    date.getFullYear();


                const month =
                    String(
                        date.getMonth() + 1
                    ).padStart(2, '0');


                const day =
                    String(
                        date.getDate()
                    ).padStart(2, '0');


                if (expiryDateInput) {
                    expiryDateInput.value =
                        `${year}-${month}-${day}`;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Preview
            |--------------------------------------------------------------------------
            */

            function updatePreview() {

                const option =
                    getSelectedPlan();


                if (!option) {

                    if (planPreview) {
                        planPreview.classList.add(
                            'hidden'
                        );
                    }

                    return;
                }


                const price =
                    parseFloat(
                        option.dataset.price || 0
                    );


                const duration =
                    parseInt(
                        option.dataset.duration || 0
                    );


                const offices =
                    parseInt(
                        option.dataset.office || 1
                    );


                const users =
                    parseInt(
                        option.dataset.user || 1
                    );


                if (previewPrice) {
                    previewPrice.textContent =
                        '₹' + price.toLocaleString(
                            'en-IN',
                            {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }
                        );
                }


                if (previewDuration) {
                    previewDuration.textContent =
                        duration + ' Days';
                }


                if (previewOffice) {
                    previewOffice.textContent =
                        offices;
                }


                if (previewUser) {
                    previewUser.textContent =
                        users;
                }


                if (planPreview) {
                    planPreview.classList.remove(
                        'hidden'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Plan Change
            |--------------------------------------------------------------------------
            */

            if (planSelect) {

                planSelect.addEventListener(
                    'change',
                    function () {

                        updatePlanLimits();

                        updateExpiryDate();

                        updatePreview();
                    }
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Start Date Change
            |--------------------------------------------------------------------------
            */

            if (startDateInput) {

                startDateInput.addEventListener(
                    'change',
                    function () {

                        updateExpiryDate();
                    }
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Initial Load
            |--------------------------------------------------------------------------
            */

            const oldOffice =
                @json(old('number_of_office') !== null);

            const oldUser =
                @json(old('number_of_user') !== null);


            if (planSelect && planSelect.value) {

                /*
                 * Agar validation error ke baad page open hua hai
                 * to manually entered values overwrite nahi honge.
                 */

                if (!oldOffice && !oldUser) {
                    updatePlanLimits();
                }


                if (
                    expiryDateInput &&
                    !expiryDateInput.value
                ) {
                    updateExpiryDate();
                }


                updatePreview();
            }


            /*
            |--------------------------------------------------------------------------
            | Double Submit Prevent
            |--------------------------------------------------------------------------
            */

            const form =
                document.getElementById('userPlanForm');

            const saveButton =
                document.getElementById('saveButton');


            if (form && saveButton) {

                form.addEventListener(
                    'submit',
                    function () {

                        saveButton.disabled = true;

                        saveButton.classList.add(
                            'opacity-60',
                            'cursor-not-allowed'
                        );

                        saveButton.innerHTML =
                            'Saving...';
                    }
                );
            }

        });
    </script>

</x-layouts.app>