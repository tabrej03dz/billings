<x-layouts.app :title="__('Business Profile Setup')">
    @php
        $businessItemCount = (int) ($itemCount ?? 0);

        $addItemUrl = Route::has('items.create')
            ? route('items.create')
            : (Route::has('items.index')
                ? route('items.index')
                : url('/items/create'));
    @endphp

    <style>
        @keyframes itemCtaPulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(79, 70, 229, .45);
            }
            50% {
                transform: scale(1.025);
                box-shadow: 0 0 0 12px rgba(79, 70, 229, 0);
            }
        }

        .item-cta-blink {
            animation: itemCtaPulse 1.35s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .item-cta-blink {
                animation: none;
            }
        }

        @media (max-width: 639px) {
            #businessProfileForm > section {
                border-radius: 1rem !important;
            }

            #businessProfileForm > section > div:first-child {
                padding: .9rem 1rem !important;
            }

            #businessProfileForm > section > div:first-child h2 {
                font-size: 1rem !important;
                line-height: 1.35rem !important;
            }

            #businessProfileForm > section > div:first-child p {
                font-size: .75rem !important;
                line-height: 1.15rem !important;
            }

            #businessProfileForm input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
            #businessProfileForm select,
            #businessProfileForm textarea {
                min-height: 46px;
                font-size: 16px !important;
            }

            #businessProfileForm textarea {
                min-height: 100px;
            }

            #businessProfileForm .grid.p-6,
            #businessProfileForm > section > .p-6 {
                padding: 1rem !important;
                gap: 1rem !important;
            }

            #template-selection .invoice-template-slide {
                flex-basis: 88% !important;
                width: 88% !important;
                max-width: 88% !important;
            }
        }
    </style>
    <div class="mb-5"><x-billing-setup-guide :step="2" /></div>

    <div class="min-h-screen bg-zinc-50 py-3 dark:bg-zinc-950 sm:py-6">
        <div class="mx-auto max-w-7xl px-3 pb-10 sm:px-6 sm:pb-12 lg:px-8">

            {{-- Success Message --}}
            @if(session('success'))
                <div
                    class="mb-6 rounded-2xl border border-emerald-200
                           bg-emerald-50 p-4 text-emerald-700
                           dark:border-emerald-900 dark:bg-emerald-950/40
                           dark:text-emerald-300"
                >
                    {{ session('success') }}
                </div>
            @endif

            {{-- Validation Errors --}}
            @if($errors->any())
                <div
                    id="validationErrors"
                    class="mb-6 rounded-2xl border border-red-200
                           bg-red-50 p-5 text-red-700
                           dark:border-red-900 dark:bg-red-950/30
                           dark:text-red-300"
                >
                    <h2 class="font-bold">
                        Business profile could not be saved
                    </h2>

                    <ul class="mt-3 list-inside list-disc space-y-1 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Header --}}
            <div
                class="mb-5 overflow-hidden rounded-2xl sm:rounded-3xl
                       bg-gradient-to-r from-indigo-600 to-violet-600
                       p-4 text-white shadow-xl sm:p-6"
            >
                <div
                    class="flex flex-col justify-between gap-6
                           lg:flex-row lg:items-center"
                >
                    <div>
                        <p
                            class="text-sm font-semibold uppercase
                                   tracking-[0.18em] text-indigo-100"
                        >
                            Business Setup
                        </p>

                        <h1 class="mt-2 text-2xl font-black sm:text-3xl">
                            Complete your business profile
                        </h1>

                        <p class="mt-2 max-w-2xl text-indigo-100">
                            Add your business information, branding and invoice
                            preferences. These details will automatically be used
                            across invoices, PDFs and reports.
                        </p>
                    </div>

                    <div
                        class="min-w-[220px] rounded-2xl
                               bg-white/10 p-5 backdrop-blur"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-indigo-100">
                                Completion
                            </span>

                            <span class="text-2xl font-black">
                                {{ (int) ($business->profile_completion ?? 0) }}%
                            </span>
                        </div>

                        <div
                            class="mt-3 h-2.5 overflow-hidden
                                   rounded-full bg-white/20"
                        >
                            <div
                                class="h-full rounded-full bg-white
                                       transition-all duration-500"
                                style="width: {{ min(
                                    100,
                                    max(
                                        0,
                                        (int) ($business->profile_completion ?? 0)
                                    )
                                ) }}%"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Missing Fields --}}
            @if(!empty($missingFields) && count($missingFields))
                <div
                    class="mb-6 rounded-2xl border border-amber-200
                           bg-amber-50 p-5
                           dark:border-amber-900 dark:bg-amber-950/30"
                >
                    <h2
                        class="font-bold text-amber-900
                               dark:text-amber-200"
                    >
                        Complete these fields
                    </h2>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($missingFields as $field)
                            <span
                                class="rounded-full bg-white px-3 py-1
                                       text-xs font-semibold text-amber-700
                                       shadow-sm dark:bg-zinc-900
                                       dark:text-amber-300"
                            >
                                {{ $field }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Mobile Quick Navigation --}}
            <div class="mb-5 rounded-2xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="mb-2 px-1 text-xs font-bold uppercase tracking-wider text-zinc-500">
                    Complete setup step-by-step
                </p>

                <div class="flex gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    <a href="#basic-information" class="shrink-0 rounded-full bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300">
                        1. Business Details
                    </a>
                    <a href="#branding" class="shrink-0 rounded-full bg-violet-50 px-3 py-2 text-xs font-bold text-violet-700 dark:bg-violet-950/50 dark:text-violet-300">
                        2. Branding
                    </a>
                    <a href="#template-selection" class="shrink-0 rounded-full bg-cyan-50 px-3 py-2 text-xs font-bold text-cyan-700 dark:bg-cyan-950/50 dark:text-cyan-300">
                        3. Template
                    </a>
                    <a href="#invoice-settings" class="shrink-0 rounded-full bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                        4. Invoice Settings
                    </a>
                </div>
            </div>

            {{-- Show only when this business has no items --}}
            @if($business->exists && $businessItemCount === 0)
                <div class="mb-6 overflow-hidden rounded-2xl border-2 border-indigo-300 bg-gradient-to-br from-indigo-50 via-white to-violet-50 p-4 shadow-lg dark:border-indigo-800 dark:from-indigo-950/60 dark:via-zinc-900 dark:to-violet-950/50 sm:p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <h2 class="text-base font-black text-zinc-900 dark:text-white sm:text-lg">
                                    Add your first item
                                </h2>
                                <p class="mt-1 text-sm leading-5 text-zinc-600 dark:text-zinc-300">
                                    No item is available in this business yet. Add an item now so invoice creation is easy and error-free.
                                </p>
                            </div>
                        </div>

                        <a
                            href="{{ $addItemUrl }}"
                            class="item-cta-blink inline-flex min-h-12 w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-black text-white shadow-lg transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200 sm:w-auto dark:focus:ring-indigo-950"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Item Now
                        </a>
                    </div>
                </div>
            @endif

            {{-- Main Form --}}
            <form
                id="businessProfileForm"
                method="POST"
                action="{{ route('business-profile.update') }}"
                enctype="multipart/form-data"
                class="space-y-6"
            >
                @csrf

                @method('PUT')

                @include('business-profile.partials.basic-information')

                @include('business-profile.partials.branding')

                @include('business-profile.partials.template-selection')

                @include('business-profile.partials.invoice-settings')

                {{-- Save Section --}}
                <section
                    id="businessProfileSaveBar"
                    class="overflow-hidden rounded-2xl border
                           border-zinc-200 bg-white shadow-sm
                           dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <div
                        class="flex flex-col gap-4 p-4 sm:p-6
                               md:flex-row md:items-center
                               md:justify-between"
                    >
                        <div class="min-w-0">
                            <h2
                                class="text-base font-black text-zinc-900
                                       dark:text-white sm:text-lg"
                            >
                                Save your business setup
                            </h2>

                            <p
                                class="mt-1 text-sm leading-6 text-zinc-500
                                       dark:text-zinc-400"
                            >
                                Review the details above and save your business profile.
                            </p>
                        </div>

                        <button
                            id="businessProfileSubmitButton"
                            type="submit"
                            name="action"
                            value="save"
                            class="relative inline-flex min-h-12 w-full
                                   shrink-0 cursor-pointer items-center
                                   justify-center gap-2 rounded-xl
                                   bg-indigo-600 px-6 py-3
                                   text-sm font-black text-white
                                   shadow-lg transition
                                   hover:bg-indigo-700
                                   active:scale-[0.98]
                                   disabled:cursor-not-allowed
                                   disabled:opacity-60
                                   md:w-auto md:min-w-[240px]"
                        >
                            <svg
                                id="businessProfileSubmitSpinner"
                                class="hidden h-5 w-5 animate-spin"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>

                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0
                                    C5.373 0 0 5.373 0 12h4z"
                                ></path>
                            </svg>

                            <span id="businessProfileSubmitText">
                                {{ $business->exists
                                    ? 'Save Business Profile'
                                    : 'Create Business Profile' }}
                            </span>
                        </button>
                    </div>
                </section>
            </form>
        </div>
    </div>

    {{-- <script>
        (() => {
            function initialiseBusinessProfileForm() {
                const form = document.getElementById('businessProfileForm');
                const submitButton = document.getElementById(
                    'businessProfileSubmitButton'
                );
                const spinner = document.getElementById(
                    'businessProfileSubmitSpinner'
                );

                if (!form || !submitButton) {
                    return;
                }

                /*
                 * Browser back / Livewire navigation ke baad button reset.
                 */
                submitButton.disabled = false;
                submitButton.value = 'Save Business Profile';
                submitButton.classList.remove('pl-12');
                spinner?.classList.add('hidden');
                form.dataset.isSubmitting = 'false';

                if (form.dataset.submitInitialised === 'true') {
                    return;
                }

                form.dataset.submitInitialised = 'true';

                form.addEventListener('submit', function (event) {
                    const selectedTemplate = form.querySelector(
                        'input[name="pdf_template_id"]:checked'
                    );

                    /*
                     * Actual radio button ko validate karein.
                     * Purana selectedTemplateId hidden input is page me hai hi nahi.
                     */
                    if (!selectedTemplate) {
                        event.preventDefault();

                        let templateError = document.getElementById(
                            'templateSelectionClientError'
                        );

                        if (!templateError) {
                            templateError = document.createElement('div');
                            templateError.id =
                                'templateSelectionClientError';
                            templateError.className =
                                'mt-4 rounded-xl border border-red-200 ' +
                                'bg-red-50 p-4 text-sm font-semibold ' +
                                'text-red-700 dark:border-red-900 ' +
                                'dark:bg-red-950/30 dark:text-red-300';
                            templateError.textContent =
                                'Please select an invoice template.';

                            document
                                .getElementById('template-selection')
                                ?.appendChild(templateError);
                        } else {
                            templateError.classList.remove('hidden');
                        }

                        document
                            .getElementById('template-selection')
                            ?.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });

                        return;
                    }

                    document
                        .getElementById('templateSelectionClientError')
                        ?.classList.add('hidden');

                    /*
                     * Native required validation.
                     */
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        form.reportValidity();

                        const firstInvalidField =
                            form.querySelector(':invalid');

                        firstInvalidField?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });

                        firstInvalidField?.focus();
                        return;
                    }

                    /*
                     * Double submit rokna.
                     */
                    if (form.dataset.isSubmitting === 'true') {
                        event.preventDefault();
                        return;
                    }

                    form.dataset.isSubmitting = 'true';
                    submitButton.disabled = true;
                    submitButton.value = 'Saving...';
                    submitButton.classList.add('pl-12');
                    spinner?.classList.remove('hidden');
                });

                form
                    .querySelectorAll('input[name="pdf_template_id"]')
                    .forEach(function (radio) {
                        radio.addEventListener('change', function () {
                            document
                                .getElementById(
                                    'templateSelectionClientError'
                                )
                                ?.classList.add('hidden');
                        });
                    });
            }

            document.addEventListener(
                'DOMContentLoaded',
                initialiseBusinessProfileForm
            );

            document.addEventListener(
                'livewire:navigated',
                initialiseBusinessProfileForm
            );

            window.addEventListener(
                'pageshow',
                initialiseBusinessProfileForm
            );

            initialiseBusinessProfileForm();
        })();
    </script> --}}

    <script>
    (() => {
        function initialiseBusinessProfileForm() {
            const form = document.getElementById('businessProfileForm');
            const submitButton = document.getElementById(
                'businessProfileSubmitButton'
            );
            const spinner = document.getElementById(
                'businessProfileSubmitSpinner'
            );

            if (!form || !submitButton) {
                return;
            }

            // Sabhi HTML required validations hata do.
            form.querySelectorAll('[required]').forEach(function (field) {
                field.removeAttribute('required');
            });

            // Browser ki native validation completely disable.
            form.setAttribute('novalidate', 'novalidate');

            // Back navigation ke baad button reset.
            submitButton.disabled = false;
            submitButton.value = 'Save Business Profile';
            submitButton.classList.remove('pl-12');
            spinner?.classList.add('hidden');
            form.dataset.isSubmitting = 'false';

            if (form.dataset.submitInitialised === 'true') {
                return;
            }

            form.dataset.submitInitialised = 'true';

            form.addEventListener('submit', function (event) {
                // Koi field ya template required nahi hai.

                if (form.dataset.isSubmitting === 'true') {
                    event.preventDefault();
                    return;
                }

                form.dataset.isSubmitting = 'true';
                submitButton.disabled = true;
                submitButton.value = 'Saving...';
                submitButton.classList.add('pl-12');
                spinner?.classList.remove('hidden');
            });
        }

        document.addEventListener(
            'DOMContentLoaded',
            initialiseBusinessProfileForm
        );

        document.addEventListener(
            'livewire:navigated',
            initialiseBusinessProfileForm
        );

        window.addEventListener(
            'pageshow',
            initialiseBusinessProfileForm
        );

        initialiseBusinessProfileForm();
    })();
</script>

<script>
    (() => {
        function initialiseBusinessProfileForm() {
            const form = document.getElementById('businessProfileForm');
            const button = document.getElementById(
                'businessProfileSubmitButton'
            );
            const buttonText = document.getElementById(
                'businessProfileSubmitText'
            );
            const spinner = document.getElementById(
                'businessProfileSubmitSpinner'
            );

            if (!form || !button) {
                return;
            }

            button.disabled = false;
            spinner?.classList.add('hidden');

            if (buttonText) {
                buttonText.textContent = 'Save Business Profile';
            }

            form.dataset.isSubmitting = 'false';

            if (form.dataset.submitInitialised === 'true') {
                return;
            }

            form.dataset.submitInitialised = 'true';

            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    form.reportValidity();

                    const invalidField = form.querySelector(':invalid');

                    invalidField?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    invalidField?.focus();
                    return;
                }

                if (form.dataset.isSubmitting === 'true') {
                    event.preventDefault();
                    return;
                }

                form.dataset.isSubmitting = 'true';
                button.disabled = true;

                spinner?.classList.remove('hidden');

                if (buttonText) {
                    buttonText.textContent = 'Saving...';
                }
            });
        }

        document.addEventListener(
            'DOMContentLoaded',
            initialiseBusinessProfileForm
        );

        document.addEventListener(
            'livewire:navigated',
            initialiseBusinessProfileForm
        );

        window.addEventListener(
            'pageshow',
            initialiseBusinessProfileForm
        );

        initialiseBusinessProfileForm();
    })();
</script>

</x-layouts.app>