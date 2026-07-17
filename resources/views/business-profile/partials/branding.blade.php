<section
    id="branding"
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
                       rounded-2xl bg-violet-100 text-violet-600
                       dark:bg-violet-950/60 dark:text-violet-300"
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
                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4v2m0-6V4"
                    />
                </svg>
            </div>

            <div>
                <h2 class="text-xl font-black text-zinc-900 dark:text-white">
                    Business Branding
                </h2>

                <p class="mt-1 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                    Upload your business logo, signature and letterhead for
                    professional invoices and documents.
                </p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 p-6 lg:grid-cols-3">

        {{-- Logo --}}
        <div
            class="rounded-2xl border border-zinc-200 p-5
                   dark:border-zinc-700"
        >
            <div class="mb-4">
                <h3 class="font-black text-zinc-900 dark:text-white">
                    Business Logo
                </h3>

                <p class="mt-1 text-xs leading-5 text-zinc-500">
                    Recommended: square PNG or WebP, maximum 2 MB.
                </p>
            </div>

            <div
                class="mb-4 flex aspect-square max-h-52 items-center justify-center
                       overflow-hidden rounded-2xl border-2 border-dashed
                       border-zinc-300 bg-zinc-50
                       dark:border-zinc-700 dark:bg-zinc-950"
            >
                <img
                    id="logoPreview"
                    src="{{ $business->logo ? Storage::url($business->logo) : '' }}"
                    alt="Business logo preview"
                    class="{{ $business->logo ? '' : 'hidden' }} h-full w-full object-contain p-4"
                >

                <div
                    id="logoPlaceholder"
                    class="{{ $business->logo ? 'hidden' : '' }} px-5 text-center"
                >
                    <svg
                        class="mx-auto h-10 w-10 text-zinc-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M3 16l4-4a2 2 0 012.828 0L13 15.172l2-2a2 2 0 012.828 0L21 16m-6-6h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>

                    <p class="mt-2 text-sm font-semibold text-zinc-500">
                        No logo selected
                    </p>
                </div>
            </div>

            <label
                for="logo"
                class="flex cursor-pointer items-center justify-center gap-2
                       rounded-xl border border-zinc-300 bg-white px-4 py-3
                       text-sm font-bold text-zinc-700 transition
                       hover:border-indigo-400 hover:bg-indigo-50
                       dark:border-zinc-700 dark:bg-zinc-950
                       dark:text-zinc-200 dark:hover:bg-zinc-800"
            >
                Choose Logo
            </label>

            <input
                id="logo"
                type="file"
                name="logo"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                class="hidden"
            >

            <p
                id="logoFileName"
                class="mt-2 truncate text-center text-xs text-zinc-500"
            ></p>

            @error('logo')
                <p class="mt-2 text-sm font-medium text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Signature --}}
        <div
            class="rounded-2xl border border-zinc-200 p-5
                   dark:border-zinc-700"
        >
            <div class="mb-4">
                <h3 class="font-black text-zinc-900 dark:text-white">
                    Authorized Signature
                </h3>

                <p class="mt-1 text-xs leading-5 text-zinc-500">
                    Upload a clean signature image with a transparent background.
                </p>
            </div>

            <div
                class="mb-4 flex aspect-square max-h-52 items-center justify-center
                       overflow-hidden rounded-2xl border-2 border-dashed
                       border-zinc-300 bg-zinc-50
                       dark:border-zinc-700 dark:bg-zinc-950"
            >
                <img
                    id="signaturePreview"
                    src="{{ $business->signature ? Storage::url($business->signature) : '' }}"
                    alt="Signature preview"
                    class="{{ $business->signature ? '' : 'hidden' }} h-full w-full object-contain p-4"
                >

                <div
                    id="signaturePlaceholder"
                    class="{{ $business->signature ? 'hidden' : '' }} px-5 text-center"
                >
                    <svg
                        class="mx-auto h-10 w-10 text-zinc-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M11 5l7 7-7 7M5 5v14"
                        />
                    </svg>

                    <p class="mt-2 text-sm font-semibold text-zinc-500">
                        No signature selected
                    </p>
                </div>
            </div>

            <label
                for="signature"
                class="flex cursor-pointer items-center justify-center gap-2
                       rounded-xl border border-zinc-300 bg-white px-4 py-3
                       text-sm font-bold text-zinc-700 transition
                       hover:border-indigo-400 hover:bg-indigo-50
                       dark:border-zinc-700 dark:bg-zinc-950
                       dark:text-zinc-200 dark:hover:bg-zinc-800"
            >
                Choose Signature
            </label>

            <input
                id="signature"
                type="file"
                name="signature"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                class="hidden"
            >

            <p
                id="signatureFileName"
                class="mt-2 truncate text-center text-xs text-zinc-500"
            ></p>

            @error('signature')
                <p class="mt-2 text-sm font-medium text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Letter Head --}}
        <div
            class="rounded-2xl border border-zinc-200 p-5
                   dark:border-zinc-700"
        >
            <div class="mb-4">
                <h3 class="font-black text-zinc-900 dark:text-white">
                    Letterhead
                </h3>

                <p class="mt-1 text-xs leading-5 text-zinc-500">
                    Upload an A4-sized letterhead image, maximum 4 MB.
                </p>
            </div>

            <div
                class="mb-4 flex aspect-square max-h-52 items-center justify-center
                       overflow-hidden rounded-2xl border-2 border-dashed
                       border-zinc-300 bg-zinc-50
                       dark:border-zinc-700 dark:bg-zinc-950"
            >
                <img
                    id="letterHeadPreview"
                    src="{{ $business->letter_head ? Storage::url($business->letter_head) : '' }}"
                    alt="Letterhead preview"
                    class="{{ $business->letter_head ? '' : 'hidden' }} h-full w-full object-contain p-3"
                >

                <div
                    id="letterHeadPlaceholder"
                    class="{{ $business->letter_head ? 'hidden' : '' }} px-5 text-center"
                >
                    <svg
                        class="mx-auto h-10 w-10 text-zinc-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M9 12h6m-6 4h6M8 3h5l5 5v13H8a2 2 0 01-2-2V5a2 2 0 012-2z"
                        />
                    </svg>

                    <p class="mt-2 text-sm font-semibold text-zinc-500">
                        No letterhead selected
                    </p>
                </div>
            </div>

            <label
                for="letter_head"
                class="flex cursor-pointer items-center justify-center gap-2
                       rounded-xl border border-zinc-300 bg-white px-4 py-3
                       text-sm font-bold text-zinc-700 transition
                       hover:border-indigo-400 hover:bg-indigo-50
                       dark:border-zinc-700 dark:bg-zinc-950
                       dark:text-zinc-200 dark:hover:bg-zinc-800"
            >
                Choose Letterhead
            </label>

            <input
                id="letter_head"
                type="file"
                name="letter_head"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                class="hidden"
            >

            <p
                id="letterHeadFileName"
                class="mt-2 truncate text-center text-xs text-zinc-500"
            ></p>

            @error('letter_head')
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
            const setupImagePreview = ({
                inputId,
                previewId,
                placeholderId,
                fileNameId
            }) => {
                const input = document.getElementById(inputId);
                const preview = document.getElementById(previewId);
                const placeholder = document.getElementById(placeholderId);
                const fileName = document.getElementById(fileNameId);

                if (!input || !preview) {
                    return;
                }

                input.addEventListener('change', () => {
                    const file = input.files?.[0];

                    if (!file) {
                        return;
                    }

                    if (!file.type.startsWith('image/')) {
                        input.value = '';

                        window.alert('Please select a valid image file.');

                        return;
                    }

                    const reader = new FileReader();

                    reader.onload = event => {
                        preview.src = event.target.result;
                        preview.classList.remove('hidden');
                        placeholder?.classList.add('hidden');

                        if (fileName) {
                            fileName.textContent = file.name;
                        }
                    };

                    reader.readAsDataURL(file);
                });
            };

            setupImagePreview({
                inputId: 'logo',
                previewId: 'logoPreview',
                placeholderId: 'logoPlaceholder',
                fileNameId: 'logoFileName'
            });

            setupImagePreview({
                inputId: 'signature',
                previewId: 'signaturePreview',
                placeholderId: 'signaturePlaceholder',
                fileNameId: 'signatureFileName'
            });

            setupImagePreview({
                inputId: 'letter_head',
                previewId: 'letterHeadPreview',
                placeholderId: 'letterHeadPlaceholder',
                fileNameId: 'letterHeadFileName'
            });
        })();
    </script>
@endpush