<section
    id="branding"
    class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm
           dark:border-zinc-800 dark:bg-zinc-900"
>
    {{-- Compact Header --}}
    <div
        class="flex items-center gap-2.5 border-b border-zinc-200
               bg-zinc-50 px-3 py-2.5
               dark:border-zinc-800 dark:bg-zinc-900"
    >
        <div
            class="flex h-8 w-8 shrink-0 items-center justify-center
                   rounded-lg bg-violet-100 text-violet-600
                   dark:bg-violet-950/60 dark:text-violet-300"
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
                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4v2m0-6V4"
                />
            </svg>
        </div>

        <div class="min-w-0">
            <h2 class="text-sm font-black text-zinc-900 dark:text-white">
                Business Branding
            </h2>

            <p class="truncate text-[11px] leading-4 text-zinc-500 dark:text-zinc-400">
                Add logo, signature and letterhead to invoices.
            </p>
        </div>
    </div>

    {{-- Three compact upload cards --}}
    <div class="grid gap-2.5 p-3 md:grid-cols-3">

        {{-- Business Logo --}}
        <div
            class="flex min-w-0 items-center gap-3 rounded-xl border
                   border-zinc-200 bg-white p-2.5
                   dark:border-zinc-700 dark:bg-zinc-950"
        >
            <button
                type="button"
                data-file-trigger="logo"
                class="relative flex h-16 w-16 shrink-0 cursor-pointer
                       items-center justify-center overflow-hidden rounded-lg
                       border border-dashed border-zinc-300 bg-zinc-50
                       transition hover:border-violet-400 hover:bg-violet-50
                       dark:border-zinc-700 dark:bg-zinc-900
                       dark:hover:bg-zinc-800"
                aria-label="Choose business logo"
            >
                <img
                    id="logoPreview"
                    src="{{ $business->logo ? Storage::url($business->logo) : '' }}"
                    alt="Business logo preview"
                    class="{{ $business->logo ? '' : 'hidden' }}
                           h-full w-full object-contain p-1"
                >

                <span
                    id="logoPlaceholder"
                    class="{{ $business->logo ? 'hidden' : 'flex' }}
                           h-full w-full flex-col items-center justify-center
                           text-zinc-400"
                >
                    <svg
                        class="h-5 w-5"
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

                    <span class="mt-1 text-[9px] font-bold">Preview</span>
                </span>
            </button>

            <div class="min-w-0 flex-1">
                <h3 class="truncate text-xs font-black text-zinc-900 dark:text-white">
                    Business Logo
                </h3>

                <p class="mt-0.5 truncate text-[10px] text-zinc-500">
                    PNG, JPG or WebP · Max 2 MB
                </p>

                <label
                    for="logo"
                    class="mt-2 inline-flex cursor-pointer items-center gap-1.5
                           rounded-md bg-violet-600 px-2.5 py-1.5
                           text-[10px] font-bold text-white transition
                           hover:bg-violet-700"
                >
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4v16m8-8H4" />
                    </svg>
                    Choose
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
                    class="mt-1 max-w-full truncate text-[9px] text-zinc-500"
                >
                    {{ $business->logo ? basename($business->logo) : '' }}
                </p>

                @error('logo')
                    <p class="mt-1 text-[10px] font-medium text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        {{-- Authorized Signature --}}
        <div
            class="flex min-w-0 items-center gap-3 rounded-xl border
                   border-zinc-200 bg-white p-2.5
                   dark:border-zinc-700 dark:bg-zinc-950"
        >
            <button
                type="button"
                data-file-trigger="signature"
                class="relative flex h-16 w-16 shrink-0 cursor-pointer
                       items-center justify-center overflow-hidden rounded-lg
                       border border-dashed border-zinc-300 bg-zinc-50
                       transition hover:border-violet-400 hover:bg-violet-50
                       dark:border-zinc-700 dark:bg-zinc-900
                       dark:hover:bg-zinc-800"
                aria-label="Choose authorized signature"
            >
                <img
                    id="signaturePreview"
                    src="{{ $business->signature ? Storage::url($business->signature) : '' }}"
                    alt="Signature preview"
                    class="{{ $business->signature ? '' : 'hidden' }}
                           h-full w-full object-contain p-1"
                >

                <span
                    id="signaturePlaceholder"
                    class="{{ $business->signature ? 'hidden' : 'flex' }}
                           h-full w-full flex-col items-center justify-center
                           text-zinc-400"
                >
                    <svg
                        class="h-5 w-5"
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

                    <span class="mt-1 text-[9px] font-bold">Preview</span>
                </span>
            </button>

            <div class="min-w-0 flex-1">
                <h3 class="truncate text-xs font-black text-zinc-900 dark:text-white">
                    Signature
                </h3>

                <p class="mt-0.5 truncate text-[10px] text-zinc-500">
                    Transparent image preferred
                </p>

                <label
                    for="signature"
                    class="mt-2 inline-flex cursor-pointer items-center gap-1.5
                           rounded-md bg-violet-600 px-2.5 py-1.5
                           text-[10px] font-bold text-white transition
                           hover:bg-violet-700"
                >
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4v16m8-8H4" />
                    </svg>
                    Choose
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
                    class="mt-1 max-w-full truncate text-[9px] text-zinc-500"
                >
                    {{ $business->signature ? basename($business->signature) : '' }}
                </p>

                @error('signature')
                    <p class="mt-1 text-[10px] font-medium text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        {{-- Letterhead --}}
        <div
            class="flex min-w-0 items-center gap-3 rounded-xl border
                   border-zinc-200 bg-white p-2.5
                   dark:border-zinc-700 dark:bg-zinc-950"
        >
            <button
                type="button"
                data-file-trigger="letter_head"
                class="relative flex h-16 w-16 shrink-0 cursor-pointer
                       items-center justify-center overflow-hidden rounded-lg
                       border border-dashed border-zinc-300 bg-zinc-50
                       transition hover:border-violet-400 hover:bg-violet-50
                       dark:border-zinc-700 dark:bg-zinc-900
                       dark:hover:bg-zinc-800"
                aria-label="Choose letterhead"
            >
                <img
                    id="letterHeadPreview"
                    src="{{ $business->letter_head ? Storage::url($business->letter_head) : '' }}"
                    alt="Letterhead preview"
                    class="{{ $business->letter_head ? '' : 'hidden' }}
                           h-full w-full object-contain p-1"
                >

                <span
                    id="letterHeadPlaceholder"
                    class="{{ $business->letter_head ? 'hidden' : 'flex' }}
                           h-full w-full flex-col items-center justify-center
                           text-zinc-400"
                >
                    <svg
                        class="h-5 w-5"
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

                    <span class="mt-1 text-[9px] font-bold">Preview</span>
                </span>
            </button>

            <div class="min-w-0 flex-1">
                <h3 class="truncate text-xs font-black text-zinc-900 dark:text-white">
                    Letterhead
                </h3>

                <p class="mt-0.5 truncate text-[10px] text-zinc-500">
                    A4 image · Max 4 MB
                </p>

                <label
                    for="letter_head"
                    class="mt-2 inline-flex cursor-pointer items-center gap-1.5
                           rounded-md bg-violet-600 px-2.5 py-1.5
                           text-[10px] font-bold text-white transition
                           hover:bg-violet-700"
                >
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4v16m8-8H4" />
                    </svg>
                    Choose
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
                    class="mt-1 max-w-full truncate text-[9px] text-zinc-500"
                >
                    {{ $business->letter_head ? basename($business->letter_head) : '' }}
                </p>

                @error('letter_head')
                    <p class="mt-1 text-[10px] font-medium text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    (() => {
        const initialiseBrandingUploads = () => {
            const brandingSection = document.getElementById('branding');

            if (!brandingSection) {
                return;
            }

            const fields = [
                {
                    inputId: 'logo',
                    previewId: 'logoPreview',
                    placeholderId: 'logoPlaceholder',
                    fileNameId: 'logoFileName',
                    maxSize: 2 * 1024 * 1024,
                    maxSizeText: '2 MB'
                },
                {
                    inputId: 'signature',
                    previewId: 'signaturePreview',
                    placeholderId: 'signaturePlaceholder',
                    fileNameId: 'signatureFileName',
                    maxSize: 2 * 1024 * 1024,
                    maxSizeText: '2 MB'
                },
                {
                    inputId: 'letter_head',
                    previewId: 'letterHeadPreview',
                    placeholderId: 'letterHeadPlaceholder',
                    fileNameId: 'letterHeadFileName',
                    maxSize: 4 * 1024 * 1024,
                    maxSizeText: '4 MB'
                }
            ];

            fields.forEach(field => {
                const input = document.getElementById(field.inputId);
                const preview = document.getElementById(field.previewId);
                const placeholder = document.getElementById(
                    field.placeholderId
                );
                const fileName = document.getElementById(field.fileNameId);

                if (!input || !preview) {
                    return;
                }

                if (input.dataset.previewInitialised === 'true') {
                    return;
                }

                input.dataset.previewInitialised = 'true';

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

                    if (file.size > field.maxSize) {
                        input.value = '';
                        window.alert(
                            `Image size must not exceed ${field.maxSizeText}.`
                        );
                        return;
                    }

                    const objectUrl = URL.createObjectURL(file);

                    preview.onload = () => {
                        URL.revokeObjectURL(objectUrl);
                    };

                    preview.src = objectUrl;
                    preview.classList.remove('hidden');

                    placeholder?.classList.add('hidden');
                    placeholder?.classList.remove('flex');

                    if (fileName) {
                        fileName.textContent = file.name;
                        fileName.title = file.name;
                    }
                });
            });

            brandingSection
                .querySelectorAll('[data-file-trigger]')
                .forEach(trigger => {
                    if (trigger.dataset.triggerInitialised === 'true') {
                        return;
                    }

                    trigger.dataset.triggerInitialised = 'true';

                    trigger.addEventListener('click', () => {
                        const inputId = trigger.dataset.fileTrigger;
                        document.getElementById(inputId)?.click();
                    });
                });
        };

        document.addEventListener(
            'DOMContentLoaded',
            initialiseBrandingUploads
        );

        document.addEventListener(
            'livewire:navigated',
            initialiseBrandingUploads
        );

        initialiseBrandingUploads();
    })();
</script>
@endpush