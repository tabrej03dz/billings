<section
    id="invoice-settings"
    class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm
           dark:border-zinc-800 dark:bg-zinc-900"
>
    <div
        class="border-b border-zinc-200 bg-zinc-50 px-6 py-5
               dark:border-zinc-800 dark:bg-zinc-900"
    >
        <div class="flex items-start gap-4">
            <div
                class="flex h-12 w-12 shrink-0 items-center justify-center
                       rounded-2xl bg-emerald-100 text-emerald-600
                       dark:bg-emerald-950/60 dark:text-emerald-300"
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
                        d="M9 12h6m-6 4h6M9 8h2m-5 13h12a2 2 0 002-2V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2z"
                    />
                </svg>
            </div>

            <div>
                <h2 class="text-xl font-black text-zinc-900 dark:text-white">
                    Invoice Settings
                </h2>

                <p class="mt-1 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                    Configure invoice number prefix, amount rounding and default
                    terms shown on invoices.
                </p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 p-6 md:grid-cols-2">

        {{-- Invoice Prefix --}}
        <div>
            <label
                for="invoice_base_prefix"
                class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-200"
            >
                Invoice Number Prefix
            </label>

            <input
                id="invoice_base_prefix"
                type="text"
                name="invoice_base_prefix"
                value="{{ old(
                    'invoice_base_prefix',
                    $business->invoice_base_prefix
                ) }}"
                maxlength="30"
                placeholder="Example: INV, TAX, RVG"
                class="w-full rounded-xl border px-4 py-3 text-sm uppercase
                       outline-none transition
                       {{ $errors->has('invoice_base_prefix')
                            ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:bg-red-950/20'
                            : 'border-zinc-300 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-950 dark:focus:ring-indigo-950'
                       }}
                       text-zinc-900 placeholder:text-zinc-400 dark:text-white"
            >

            <p class="mt-2 text-xs leading-5 text-zinc-500">
                Example: prefix `INV` can generate invoice numbers such as
                `INV-0001`.
            </p>

            @error('invoice_base_prefix')
                <p class="mt-2 text-sm font-medium text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Rounding Mode --}}
        <div>
            <label
                for="rounding_mode"
                class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-200"
            >
                Invoice Total Rounding
            </label>

            <select
                id="rounding_mode"
                name="rounding_mode"
                class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition
                       {{ $errors->has('rounding_mode')
                            ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:bg-red-950/20'
                            : 'border-zinc-300 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-950 dark:focus:ring-indigo-950'
                       }}
                       text-zinc-900 dark:text-white"
            >
                <option
                    value="none"
                    @selected(
                        old(
                            'rounding_mode',
                            $business->rounding_mode ?? 'none'
                        ) === 'none'
                    )
                >
                    No rounding
                </option>

                <option
                    value="nearest"
                    @selected(
                        old(
                            'rounding_mode',
                            $business->rounding_mode
                        ) === 'nearest'
                    )
                >
                    Round to nearest value
                </option>

                <option
                    value="up"
                    @selected(
                        old(
                            'rounding_mode',
                            $business->rounding_mode
                        ) === 'up'
                    )
                >
                    Always round up
                </option>

                <option
                    value="down"
                    @selected(
                        old(
                            'rounding_mode',
                            $business->rounding_mode
                        ) === 'down'
                    )
                >
                    Always round down
                </option>
            </select>

            @error('rounding_mode')
                <p class="mt-2 text-sm font-medium text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Rounding Step --}}
        <div id="roundingStepWrapper">
            <label
                for="rounding_step"
                class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-200"
            >
                Rounding Step
            </label>

            <input
                id="rounding_step"
                type="number"
                name="rounding_step"
                value="{{ old(
                    'rounding_step',
                    $business->rounding_step ?? 1
                ) }}"
                min="0.01"
                max="1000"
                step="0.01"
                inputmode="decimal"
                placeholder="1.00"
                class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition
                       {{ $errors->has('rounding_step')
                            ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:bg-red-950/20'
                            : 'border-zinc-300 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-950 dark:focus:ring-indigo-950'
                       }}
                       text-zinc-900 placeholder:text-zinc-400 dark:text-white"
            >

            <p class="mt-2 text-xs leading-5 text-zinc-500">
                Use `1.00` for whole rupee rounding or `0.50` for fifty-paise steps.
            </p>

            @error('rounding_step')
                <p class="mt-2 text-sm font-medium text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Preview --}}
        <div>
            <label
                class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-200"
            >
                Invoice Number Preview
            </label>

            <div
                class="flex min-h-[50px] items-center rounded-xl border
                       border-zinc-200 bg-zinc-50 px-4 py-3
                       dark:border-zinc-700 dark:bg-zinc-950"
            >
                <span
                    id="invoiceNumberPreview"
                    class="font-mono text-sm font-black text-indigo-600
                           dark:text-indigo-300"
                >
                    {{ filled($business->invoice_base_prefix)
                        ? strtoupper($business->invoice_base_prefix) . '-0001'
                        : 'INV-0001'
                    }}
                </span>
            </div>

            <p class="mt-2 text-xs leading-5 text-zinc-500">
                This is only a preview. Actual invoice numbering will continue
                according to your invoice logic.
            </p>
        </div>

        {{-- Terms --}}
        <div class="md:col-span-2">
            <label
                for="terms"
                class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-200"
            >
                Default Terms and Conditions
            </label>

            <textarea
                id="terms"
                name="terms"
                rows="7"
                maxlength="5000"
                placeholder="Enter the terms and conditions that should appear on invoices..."
                class="w-full resize-y rounded-xl border px-4 py-3 text-sm
                       leading-6 outline-none transition
                       {{ $errors->has('terms')
                            ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:bg-red-950/20'
                            : 'border-zinc-300 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-950 dark:focus:ring-indigo-950'
                       }}
                       text-zinc-900 placeholder:text-zinc-400 dark:text-white"
            >{{ old('terms', $business->terms) }}</textarea>

            <div class="mt-2 flex items-center justify-between gap-4">
                <p class="text-xs leading-5 text-zinc-500">
                    These terms will be automatically added to newly created invoices.
                </p>

                <span
                    id="termsCharacterCount"
                    class="shrink-0 text-xs font-semibold text-zinc-500"
                >
                    0 / 5000
                </span>
            </div>

            @error('terms')
                <p class="mt-2 text-sm font-medium text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>
</section>

@push('scripts')
    <script>
        (() => {
            const prefixInput = document.getElementById(
                'invoice_base_prefix'
            );

            const preview = document.getElementById(
                'invoiceNumberPreview'
            );

            const roundingMode = document.getElementById(
                'rounding_mode'
            );

            const roundingStepWrapper = document.getElementById(
                'roundingStepWrapper'
            );

            const roundingStepInput = document.getElementById(
                'rounding_step'
            );

            const termsInput = document.getElementById('terms');

            const termsCharacterCount = document.getElementById(
                'termsCharacterCount'
            );

            const updatePrefixPreview = () => {
                if (!prefixInput || !preview) {
                    return;
                }

                const cleanedPrefix = prefixInput.value
                    .toUpperCase()
                    .replace(/[^A-Z0-9_-]/g, '')
                    .slice(0, 30);

                prefixInput.value = cleanedPrefix;

                preview.textContent =
                    `${cleanedPrefix || 'INV'}-0001`;
            };

            const updateRoundingFields = () => {
                if (
                    !roundingMode ||
                    !roundingStepWrapper ||
                    !roundingStepInput
                ) {
                    return;
                }

                const enabled = roundingMode.value !== 'none';

                roundingStepWrapper.classList.toggle(
                    'opacity-50',
                    !enabled
                );

                roundingStepInput.disabled = !enabled;

                if (enabled && !roundingStepInput.value) {
                    roundingStepInput.value = '1.00';
                }
            };

            const updateTermsCount = () => {
                if (!termsInput || !termsCharacterCount) {
                    return;
                }

                termsCharacterCount.textContent =
                    `${termsInput.value.length} / 5000`;
            };

            prefixInput?.addEventListener(
                'input',
                updatePrefixPreview
            );

            roundingMode?.addEventListener(
                'change',
                updateRoundingFields
            );

            termsInput?.addEventListener(
                'input',
                updateTermsCount
            );

            updatePrefixPreview();
            updateRoundingFields();
            updateTermsCount();
        })();
    </script>
@endpush