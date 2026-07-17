<section
    id="basic-information"
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
                       rounded-2xl bg-indigo-100 text-indigo-600
                       dark:bg-indigo-950/60 dark:text-indigo-300"
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
                        d="M3 21h18M5 21V7l7-4 7 4v14M9 9h2m2 0h2M9 13h2m2 0h2M9 17h6"
                    />
                </svg>
            </div>

            <div>
                <h2 class="text-xl font-black text-zinc-900 dark:text-white">
                    Basic Business Information
                </h2>

                <p class="mt-1 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                    Add the basic details that will be displayed on invoices,
                    reports and business documents.
                </p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 p-6 md:grid-cols-2">

        {{-- Business Name --}}
        <div class="md:col-span-1">
            <label
                for="business_name"
                class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-200"
            >
                Business Name
                <span class="text-red-500">*</span>
            </label>

            <input
                id="business_name"
                type="text"
                name="name"
                value="{{ old('name', $business->name) }}"
                required
                maxlength="255"
                autocomplete="organization"
                placeholder="Enter business name"
                class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition
                       {{ $errors->has('name')
                            ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:bg-red-950/20'
                            : 'border-zinc-300 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-950 dark:focus:ring-indigo-950'
                       }}
                       text-zinc-900 placeholder:text-zinc-400 dark:text-white"
            >

            @error('name')
                <p class="mt-2 text-sm font-medium text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Business Type --}}
        <div class="md:col-span-1">
            <label
                for="business_type_id"
                class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-200"
            >
                Business Type
                <span class="text-red-500">*</span>
            </label>

            <select
                id="business_type_id"
                name="business_type_id"
                required
                class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition
                       {{ $errors->has('business_type_id')
                            ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:bg-red-950/20'
                            : 'border-zinc-300 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-950 dark:focus:ring-indigo-950'
                       }}
                       text-zinc-900 dark:text-white"
            >
                <option value="">
                    Select business type
                </option>

                @foreach($businessTypes as $businessType)
                    <option
                        value="{{ $businessType->id }}"
                        @selected(
                            (string) old(
                                'business_type_id',
                                $business->business_type_id
                            ) === (string) $businessType->id
                        )
                    >
                        {{ $businessType->name }}
                    </option>
                @endforeach
            </select>

            @error('business_type_id')
                <p class="mt-2 text-sm font-medium text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label
                for="business_email"
                class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-200"
            >
                Email Address
                <span class="text-red-500">*</span>
            </label>

            <input
                id="business_email"
                type="email"
                name="email"
                value="{{ old('email', $business->email) }}"
                required
                maxlength="255"
                autocomplete="email"
                placeholder="business@example.com"
                class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition
                       {{ $errors->has('email')
                            ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:bg-red-950/20'
                            : 'border-zinc-300 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-950 dark:focus:ring-indigo-950'
                       }}
                       text-zinc-900 placeholder:text-zinc-400 dark:text-white"
            >

            @error('email')
                <p class="mt-2 text-sm font-medium text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Mobile --}}
        <div>
            <label
                for="business_mobile"
                class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-200"
            >
                Mobile Number
                <span class="text-red-500">*</span>
            </label>

            <input
                id="business_mobile"
                type="text"
                name="mobile"
                value="{{ old('mobile', $business->mobile) }}"
                required
                maxlength="20"
                inputmode="tel"
                autocomplete="tel"
                placeholder="Enter mobile number"
                class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition
                       {{ $errors->has('mobile')
                            ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:bg-red-950/20'
                            : 'border-zinc-300 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-950 dark:focus:ring-indigo-950'
                       }}
                       text-zinc-900 placeholder:text-zinc-400 dark:text-white"
            >

            @error('mobile')
                <p class="mt-2 text-sm font-medium text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- GST Toggle --}}
        <div class="md:col-span-2">
            <div
                class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4
                       dark:border-zinc-700 dark:bg-zinc-950"
            >
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <label
                            for="gst_enabled"
                            class="block cursor-pointer text-sm font-bold
                                   text-zinc-800 dark:text-zinc-100"
                        >
                            Enable GST
                        </label>

                        <p class="mt-1 text-xs leading-5 text-zinc-500">
                            Enable this option only when the business has a GST number.
                        </p>
                    </div>

                    <label class="relative inline-flex cursor-pointer items-center">
                        <input
                            id="gst_enabled"
                            type="checkbox"
                            name="gst_enabled"
                            value="1"
                            class="peer sr-only"
                            @checked(
                                old(
                                    'gst_enabled',
                                    (bool) $business->gst_enabled
                                )
                            )
                        >

                        <div
                            class="h-7 w-12 rounded-full bg-zinc-300 transition
                                   after:absolute after:left-1 after:top-1 after:h-5
                                   after:w-5 after:rounded-full after:bg-white
                                   after:shadow after:transition-all
                                   peer-checked:bg-indigo-600
                                   peer-checked:after:translate-x-5
                                   dark:bg-zinc-700"
                        ></div>
                    </label>
                </div>

                <div
                    id="gstFieldsContainer"
                    class="mt-5 grid gap-5 md:grid-cols-2"
                >
                    {{-- GSTIN --}}
                    <div>
                        <label
                            for="gstin"
                            class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-200"
                        >
                            GST Number
                        </label>

                        <input
                            id="gstin"
                            type="text"
                            name="gstin"
                            value="{{ old('gstin', $business->gstin) }}"
                            maxlength="15"
                            autocomplete="off"
                            placeholder="Example: 09ABCDE1234F1Z5"
                            class="w-full rounded-xl border px-4 py-3 text-sm uppercase outline-none transition
                                   {{ $errors->has('gstin')
                                        ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:bg-red-950/20'
                                        : 'border-zinc-300 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-900 dark:focus:ring-indigo-950'
                                   }}
                                   text-zinc-900 placeholder:text-zinc-400 dark:text-white"
                        >

                        @error('gstin')
                            <p class="mt-2 text-sm font-medium text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- State Code --}}
                    <div>
                        <label
                            for="state_code"
                            class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-200"
                        >
                            State Code
                        </label>

                        <input
                            id="state_code"
                            type="text"
                            name="state_code"
                            value="{{ old('state_code', $business->state_code) }}"
                            maxlength="2"
                            inputmode="numeric"
                            placeholder="Example: 09"
                            class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition
                                   {{ $errors->has('state_code')
                                        ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:bg-red-950/20'
                                        : 'border-zinc-300 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-900 dark:focus:ring-indigo-950'
                                   }}
                                   text-zinc-900 placeholder:text-zinc-400 dark:text-white"
                        >

                        @error('state_code')
                            <p class="mt-2 text-sm font-medium text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- State --}}
        <div>
            <label
                for="business_state"
                class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-200"
            >
                State
                <span class="text-red-500">*</span>
            </label>

            <input
                id="business_state"
                type="text"
                name="state"
                value="{{ old('state', $business->state) }}"
                required
                maxlength="255"
                placeholder="Enter state name"
                class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition
                       {{ $errors->has('state')
                            ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:bg-red-950/20'
                            : 'border-zinc-300 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-950 dark:focus:ring-indigo-950'
                       }}
                       text-zinc-900 placeholder:text-zinc-400 dark:text-white"
            >

            @error('state')
                <p class="mt-2 text-sm font-medium text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Address --}}
        <div class="md:col-span-2">
            <label
                for="business_address"
                class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-200"
            >
                Complete Business Address
                <span class="text-red-500">*</span>
            </label>

            <textarea
                id="business_address"
                name="address"
                rows="4"
                required
                maxlength="1000"
                placeholder="Enter complete business address"
                class="w-full resize-y rounded-xl border px-4 py-3 text-sm outline-none transition
                       {{ $errors->has('address')
                            ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:bg-red-950/20'
                            : 'border-zinc-300 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-950 dark:focus:ring-indigo-950'
                       }}
                       text-zinc-900 placeholder:text-zinc-400 dark:text-white"
            >{{ old('address', $business->address) }}</textarea>

            @error('address')
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
            const gstToggle = document.getElementById('gst_enabled');
            const gstContainer = document.getElementById('gstFieldsContainer');
            const gstInput = document.getElementById('gstin');
            const stateCodeInput = document.getElementById('state_code');

            if (!gstToggle || !gstContainer) {
                return;
            }

            const updateGstFields = () => {
                const enabled = gstToggle.checked;

                gstContainer.classList.toggle('hidden', !enabled);

                if (gstInput) {
                    gstInput.disabled = !enabled;
                    gstInput.required = enabled;
                }

                if (stateCodeInput) {
                    stateCodeInput.disabled = !enabled;
                }

                if (!enabled) {
                    if (gstInput) {
                        gstInput.value = '';
                    }

                    if (stateCodeInput) {
                        stateCodeInput.value = '';
                    }
                }
            };

            updateGstFields();

            gstToggle.addEventListener('change', updateGstFields);

            gstInput?.addEventListener('input', function () {
                this.value = this.value
                    .toUpperCase()
                    .replace(/[^0-9A-Z]/g, '')
                    .slice(0, 15);

                const detectedStateCode = this.value.slice(0, 2);

                if (
                    stateCodeInput &&
                    /^\d{2}$/.test(detectedStateCode)
                ) {
                    stateCodeInput.value = detectedStateCode;
                }
            });

            stateCodeInput?.addEventListener('input', function () {
                this.value = this.value
                    .replace(/\D/g, '')
                    .slice(0, 2);
            });
        })();
    </script>
@endpush