<x-layouts.app :title="__('Business Profile Setup')">
    <div class="mb-5"><x-billing-setup-guide :step="2" /></div>

    <div class="min-h-screen bg-zinc-50 py-6 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">

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
                class="mb-6 overflow-hidden rounded-3xl
                       bg-gradient-to-r from-indigo-600 to-violet-600
                       p-6 text-white shadow-xl"
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

                        <h1 class="mt-2 text-3xl font-black">
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
                                {{ (int) $business->profile_completion }}%
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
                                        (int) $business->profile_completion
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

                {{-- Save Bar --}}
                <div
                    class="sticky bottom-4 z-40 flex flex-col gap-4
                           rounded-2xl border border-zinc-200
                           bg-white/95 p-4 shadow-xl backdrop-blur
                           sm:flex-row sm:items-center
                           sm:justify-between
                           dark:border-zinc-700 dark:bg-zinc-900/95"
                >
                    <div>
                        <p
                            class="text-sm font-bold
                                   text-zinc-800 dark:text-white"
                        >
                            Save your business setup
                        </p>

                        <p class="mt-1 text-xs text-zinc-500">
                            Complete the required fields and save your profile.
                        </p>
                    </div>

                    <div class="relative min-w-[220px]">
                        {{-- Spinner --}}
                        <svg
                            id="businessProfileSubmitSpinner"
                            class="pointer-events-none absolute
                                   left-5 top-1/2 hidden h-5 w-5
                                   -translate-y-1/2 animate-spin
                                   text-white"
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

                        <input
                            id="businessProfileSubmitButton"
                            type="submit"
                            name="action"
                            value="Save Business Profile"
                            class="w-full cursor-pointer rounded-xl
                                   bg-indigo-600 px-6 py-3
                                   text-center font-bold text-white
                                   shadow-sm transition
                                   hover:bg-indigo-700
                                   active:scale-[0.98]
                                   focus:outline-none focus:ring-4
                                   focus:ring-indigo-200
                                   disabled:cursor-not-allowed
                                   disabled:opacity-60
                                   dark:focus:ring-indigo-950"
                        >
                    </div>
                </div>
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

</x-layouts.app>