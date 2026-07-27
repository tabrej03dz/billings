<style>
    /*
     * Slider card widths are kept in plain CSS instead of dynamic Tailwind
     * calc classes, so they work even when Tailwind has not compiled those
     * arbitrary classes.
     */
    #template-selection [data-template-slider] {
        scroll-snap-type: x mandatory;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    #template-selection [data-template-slider]::-webkit-scrollbar {
        display: none;
    }

    #template-selection .invoice-template-slide {
        flex: 0 0 100%;
        width: 100%;
        max-width: 100%;
        scroll-snap-align: start;
    }

    @media (min-width: 640px) {
        #template-selection .invoice-template-slide {
            flex-basis: calc((100% - 16px) / 2);
            width: calc((100% - 16px) / 2);
            max-width: calc((100% - 16px) / 2);
        }
    }

    @media (min-width: 1024px) {
        #template-selection .invoice-template-slide {
            flex-basis: calc((100% - 32px) / 3);
            width: calc((100% - 32px) / 3);
            max-width: calc((100% - 32px) / 3);
        }
    }
</style>

<section
    id="template-selection"
    class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm
           dark:border-zinc-800 dark:bg-zinc-900"
>
    {{-- Header --}}
    <div
        class="border-b border-zinc-200 bg-zinc-50 px-6 py-5
               dark:border-zinc-800 dark:bg-zinc-900"
    >
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-4">
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center
                           rounded-2xl bg-cyan-100 text-cyan-600
                           dark:bg-cyan-950/60 dark:text-cyan-300"
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
                        Choose Invoice Template
                    </h2>

                    <p class="mt-1 max-w-2xl text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                        Select the invoice design that will be used for your
                        invoices and PDF documents. You can preview each template
                        before selecting it.
                    </p>
                </div>
            </div>

            @if($billTemplates->isNotEmpty())
                <div
                    class="inline-flex shrink-0 items-center gap-2 rounded-xl
                           border border-zinc-200 bg-white px-4 py-2
                           text-sm font-semibold text-zinc-600 shadow-sm
                           dark:border-zinc-700 dark:bg-zinc-950
                           dark:text-zinc-300"
                >
                    <svg
                        class="h-4 w-4 text-indigo-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                        />
                    </svg>

                    {{ $billTemplates->count() }}
                    {{ $billTemplates->count() === 1 ? 'Template' : 'Templates' }}
                </div>
            @endif
        </div>
    </div>

    <div class="p-3 sm:p-4">
        @if($billTemplates->isEmpty())
            {{-- Empty State --}}
            <div
                class="rounded-3xl border border-dashed border-zinc-300
                       bg-zinc-50 px-6 py-14 text-center
                       dark:border-zinc-700 dark:bg-zinc-950"
            >
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center
                           rounded-2xl bg-zinc-200 text-zinc-500
                           dark:bg-zinc-800 dark:text-zinc-400"
                >
                    <svg
                        class="h-8 w-8"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M9 12h6m-6 4h6M9 8h2m-5 13h12a2 2 0 002-2V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2z"
                        />
                    </svg>
                </div>

                <h3 class="mt-5 text-lg font-black text-zinc-800 dark:text-white">
                    No invoice templates available
                </h3>

                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-zinc-500">
                    Please add at least one bill template from the admin panel
                    before completing your business profile.
                </p>
            </div>
        @else
            {{-- Compact Template Slider --}}
            <div class="relative">
                <div
                    data-template-slider
                    class="flex snap-x snap-mandatory gap-4 overflow-x-auto
                           scroll-smooth pb-2 [scrollbar-width:none]
                           [&::-webkit-scrollbar]:hidden"
                >
                @foreach($billTemplates as $template)
                    @php
                        $isSelected =
                            (string) old(
                                'pdf_template_id',
                                $business->pdf_template_id
                            ) === (string) $template->id;

                        $preview = $template->preview;

                        $previewExtension = $preview
                            ? strtolower(pathinfo($preview, PATHINFO_EXTENSION))
                            : null;

                        $isImage = in_array(
                            $previewExtension,
                            ['jpg', 'jpeg', 'png', 'webp', 'gif']
                        );

                        $isPdf = $previewExtension === 'pdf';

                        $previewUrl = $preview
                            ? Storage::url($preview)
                            : null;
                    @endphp

                    <article
                        class="invoice-template-card invoice-template-slide group relative flex h-full
                               shrink-0 snap-start flex-col overflow-hidden
                               rounded-2xl border
                               bg-white transition duration-300
                               dark:bg-zinc-950
                               {{ $isSelected
                                    ? 'border-indigo-600 shadow-lg ring-4 ring-indigo-100 dark:ring-indigo-950'
                                    : 'border-zinc-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-xl dark:border-zinc-700'
                               }}"
                        data-template-card
                    >
                        {{-- Actual Radio --}}
                        <input
                            id="invoice-template-{{ $template->id }}"
                            type="radio"
                            name="pdf_template_id"
                            value="{{ $template->id }}"
                            class="template-radio sr-only"
                            @checked($isSelected)
                            required
                        >

                        {{-- Selected Badge --}}
                        <div
                            class="absolute right-3 top-3 z-20
                                   {{ $isSelected ? '' : 'hidden' }}"
                            data-selected-badge
                        >
                            <span
                                class="inline-flex items-center gap-1.5
                                       rounded-full bg-indigo-600 px-2.5 py-1
                                       text-[11px] font-bold text-white shadow-lg"
                            >
                                <svg
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.704 5.29a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3.25-3.25a1 1 0 011.414-1.414l2.543 2.543 6.543-6.543a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>

                                Selected
                            </span>
                        </div>

                        {{-- File Type Badge --}}
                        @if($preview && ($isImage || $isPdf))
                            <div class="absolute left-3 top-3 z-20">
                                <span
                                    class="inline-flex items-center gap-1
                                           rounded-full bg-zinc-950/80
                                           px-2.5 py-1 text-[11px]
                                           font-bold uppercase tracking-wide
                                           text-white backdrop-blur"
                                >
                                    @if($isPdf)
                                        <svg
                                            class="h-3.5 w-3.5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 21h10a2 2 0 002-2V9.5L13.5 4H7a2 2 0 00-2 2v13a2 2 0 002 2z"
                                            />
                                        </svg>

                                        PDF
                                    @else
                                        <svg
                                            class="h-3.5 w-3.5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4 16l4-4a2 2 0 012.828 0L14 15.172l2-2a2 2 0 012.828 0L20 14.344M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>

                                        Image
                                    @endif
                                </span>
                            </div>
                        @endif

                        {{-- Preview Area --}}
                        <div
                            class="relative overflow-hidden border-b
                                   border-zinc-200 bg-zinc-100
                                   dark:border-zinc-800 dark:bg-zinc-900"
                        >
                            <div
                                class="mx-auto flex h-[245px] max-w-[210px]
                                       items-center justify-center p-3"
                            >
                                <div
                                    class="relative h-full w-full overflow-hidden
                                           rounded-lg border border-zinc-200
                                           bg-white shadow-md
                                           dark:border-zinc-700"
                                >
                                    @if($preview && $isImage)
                                        <img
                                            src="{{ $previewUrl }}"
                                            alt="{{ $template->name }}"
                                            loading="lazy"
                                            class="h-full w-full object-cover object-top
                                                   transition duration-500
                                                   group-hover:scale-[1.03]"
                                        >

                                    @elseif($preview && $isPdf)
                                        <iframe
                                            src="{{ $previewUrl }}#toolbar=0&navpanes=0&scrollbar=0&view=FitH"
                                            title="{{ $template->name }} preview"
                                            class="pointer-events-none h-full w-full"
                                            style="border: 0;"
                                            loading="lazy"
                                        ></iframe>

                                    @else
                                        <div
                                            class="flex h-full flex-col items-center
                                                   justify-center px-6 text-center"
                                        >
                                            <div
                                                class="flex h-14 w-14 items-center
                                                       justify-center rounded-2xl
                                                       bg-zinc-100 text-zinc-400
                                                       dark:bg-zinc-800"
                                            >
                                                <svg
                                                    class="h-7 w-7"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M9 12h6m-6 4h6M9 8h2m-5 13h12a2 2 0 002-2V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2z"
                                                    />
                                                </svg>
                                            </div>

                                            <p
                                                class="mt-4 text-sm font-bold
                                                       text-zinc-500"
                                            >
                                                Preview unavailable
                                            </p>

                                            <p
                                                class="mt-1 text-xs leading-5
                                                       text-zinc-400"
                                            >
                                                No image or PDF preview has
                                                been uploaded for this template.
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Preview Overlay --}}
                                    @if($preview && ($isImage || $isPdf))
                                        <div
                                            class="absolute inset-0 flex items-center
                                                   justify-center bg-zinc-950/0
                                                   opacity-0 transition duration-300
                                                   group-hover:bg-zinc-950/40
                                                   group-hover:opacity-100"
                                        >
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-2
                                                       rounded-xl bg-white px-4 py-2.5
                                                       text-sm font-bold
                                                       text-zinc-800 shadow-xl
                                                       transition hover:scale-105
                                                       hover:bg-zinc-100"
                                                data-preview-button
                                                data-preview-url="{{ $previewUrl }}"
                                                data-preview-type="{{ $isPdf ? 'pdf' : 'image' }}"
                                                data-preview-title="{{ $template->name }}"
                                            >
                                                <svg
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                    />

                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                    />
                                                </svg>

                                                Full Preview
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Card Content --}}
                        <div class="flex flex-1 flex-col p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <h3
                                        class="truncate text-base font-black
                                               text-zinc-900 dark:text-white"
                                        title="{{ $template->name }}"
                                    >
                                        {{ $template->name }}
                                    </h3>

                                    @if($template->page_name)
                                        <div
                                            class="mt-2 inline-flex max-w-full
                                                   items-center gap-1.5 rounded-lg
                                                   bg-indigo-50 px-2.5 py-1
                                                   text-xs font-semibold
                                                   text-indigo-700
                                                   dark:bg-indigo-950/50
                                                   dark:text-indigo-300"
                                        >
                                            <svg
                                                class="h-3.5 w-3.5 shrink-0"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4 6h16M4 12h16M4 18h7"
                                                />
                                            </svg>

                                            <span class="truncate">
                                                {{ $template->page_name }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Radio Indicator --}}
                                <label
                                    for="invoice-template-{{ $template->id }}"
                                    class="flex h-8 w-8 shrink-0 cursor-pointer
                                           items-center justify-center rounded-full
                                           border-2 transition
                                           {{ $isSelected
                                                ? 'border-indigo-600 bg-indigo-600'
                                                : 'border-zinc-300 bg-white hover:border-indigo-400 dark:border-zinc-600 dark:bg-zinc-900'
                                           }}"
                                    data-radio-indicator
                                    title="Select this template"
                                >
                                    <svg
                                        class="{{ $isSelected ? '' : 'hidden' }}
                                               h-5 w-5 text-white"
                                        data-check-icon
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M16.704 5.29a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3.25-3.25a1 1 0 011.414-1.414l2.543 2.543 6.543-6.543a1 1 0 011.414 0z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </label>
                            </div>

                            <p
                                class="mt-3 line-clamp-2 min-h-[40px]
                                       text-xs leading-5 text-zinc-500
                                       dark:text-zinc-400"
                            >
                                {{ $template->description
                                    ? \Illuminate\Support\Str::limit(
                                        $template->description,
                                        150
                                    )
                                    : 'No description is available for this invoice template.'
                                }}
                            </p>

                            {{-- Card Actions --}}
                            <div
                                class="mt-auto grid grid-cols-2 gap-2
                                       border-t border-zinc-100 pt-3
                                       dark:border-zinc-800"
                            >
                                @if($preview && ($isImage || $isPdf))
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center
                                               gap-2 rounded-lg border
                                               border-zinc-300 bg-white px-2 py-2
                                               text-xs font-bold text-zinc-700
                                               transition hover:border-indigo-300
                                               hover:bg-indigo-50
                                               hover:text-indigo-700
                                               dark:border-zinc-700
                                               dark:bg-zinc-900
                                               dark:text-zinc-200
                                               dark:hover:bg-zinc-800"
                                        data-preview-button
                                        data-preview-url="{{ $previewUrl }}"
                                        data-preview-type="{{ $isPdf ? 'pdf' : 'image' }}"
                                        data-preview-title="{{ $template->name }}"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />

                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                            />
                                        </svg>

                                        Preview
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        disabled
                                        class="inline-flex cursor-not-allowed
                                               items-center justify-center gap-2
                                               rounded-lg border border-zinc-200
                                               bg-zinc-100 px-4 py-2.5
                                               text-sm font-bold text-zinc-400
                                               dark:border-zinc-800
                                               dark:bg-zinc-900"
                                    >
                                        No Preview
                                    </button>
                                @endif

                                <label
                                    for="invoice-template-{{ $template->id }}"
                                    class="inline-flex cursor-pointer items-center
                                           justify-center gap-2 rounded-lg px-2
                                           py-2 text-xs font-bold text-white
                                           transition
                                           {{ $isSelected
                                                ? 'bg-emerald-600 hover:bg-emerald-700'
                                                : 'bg-indigo-600 hover:bg-indigo-700'
                                           }}"
                                    data-select-button
                                >
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M16.704 5.29a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3.25-3.25a1 1 0 011.414-1.414l2.543 2.543 6.543-6.543a1 1 0 011.414 0z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>

                                    <span data-select-button-text>
                                        {{ $isSelected
                                            ? 'Selected'
                                            : 'Select Template'
                                        }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </article>
                @endforeach
                </div>

                {{-- Bottom compact arrows --}}
                <div class="mt-3 flex items-center justify-center gap-3">
                    <button
                        type="button"
                        data-template-slider-prev
                        class="inline-flex h-8 w-8 items-center justify-center rounded-full
                               border border-zinc-300 bg-white text-zinc-700 shadow-sm
                               transition hover:border-indigo-400 hover:bg-indigo-50
                               hover:text-indigo-700 disabled:cursor-not-allowed
                               disabled:opacity-35 dark:border-zinc-700 dark:bg-zinc-950
                               dark:text-zinc-200 dark:hover:bg-zinc-800"
                        aria-label="Previous invoice template"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <span
                        data-template-slider-status
                        class="min-w-[54px] text-center text-xs font-bold text-zinc-500 dark:text-zinc-400"
                    >1 / {{ $billTemplates->count() }}</span>

                    <button
                        type="button"
                        data-template-slider-next
                        class="inline-flex h-8 w-8 items-center justify-center rounded-full
                               border border-zinc-300 bg-white text-zinc-700 shadow-sm
                               transition hover:border-indigo-400 hover:bg-indigo-50
                               hover:text-indigo-700 disabled:cursor-not-allowed
                               disabled:opacity-35 dark:border-zinc-700 dark:bg-zinc-950
                               dark:text-zinc-200 dark:hover:bg-zinc-800"
                        aria-label="Next invoice template"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        @error('pdf_template_id')
            <div
                class="mt-5 rounded-xl border border-red-200
                       bg-red-50 px-4 py-3 text-sm font-semibold
                       text-red-600 dark:border-red-900
                       dark:bg-red-950/30 dark:text-red-300"
            >
                {{ $message }}
            </div>
        @enderror
    </div>
</section>

{{-- Full Preview Modal --}}
<div
    id="invoiceTemplatePreviewModal"
    class="fixed inset-0 z-[100] hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="invoiceTemplatePreviewTitle"
>
    {{-- Backdrop --}}
    <div
        class="absolute inset-0 bg-zinc-950/80 backdrop-blur-sm"
        data-preview-close
    ></div>

    {{-- Modal Panel --}}
    <div
        class="relative flex min-h-full items-center justify-center
               p-3 sm:p-6"
    >
        <div
            class="relative flex h-[92vh] w-full max-w-6xl
                   flex-col overflow-hidden rounded-3xl
                   border border-white/10 bg-white shadow-2xl
                   dark:bg-zinc-950"
        >
            {{-- Modal Header --}}
            <div
                class="flex shrink-0 items-center justify-between gap-4
                       border-b border-zinc-200 px-5 py-4
                       dark:border-zinc-800"
            >
                <div class="min-w-0">
                    <p
                        class="text-xs font-bold uppercase tracking-[0.16em]
                               text-indigo-600 dark:text-indigo-300"
                    >
                        Invoice Template Preview
                    </p>

                    <h3
                        id="invoiceTemplatePreviewTitle"
                        class="mt-1 truncate text-base font-black
                               text-zinc-900 dark:text-white"
                    >
                        Template Preview
                    </h3>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <a
                        id="invoiceTemplateOpenNewTab"
                        href="#"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-xl
                               border border-zinc-300 bg-white px-3 py-2
                               text-sm font-bold text-zinc-700 transition
                               hover:bg-zinc-100 dark:border-zinc-700
                               dark:bg-zinc-900 dark:text-zinc-200
                               dark:hover:bg-zinc-800"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M14 3h7v7m0-7L10 14M5 5h5M5 5v14h14v-5"
                            />
                        </svg>

                        <span class="hidden sm:inline">
                            Open
                        </span>
                    </a>

                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center
                               justify-center rounded-xl bg-zinc-100
                               text-zinc-600 transition hover:bg-red-50
                               hover:text-red-600 dark:bg-zinc-900
                               dark:text-zinc-300 dark:hover:bg-red-950/40"
                        data-preview-close
                        aria-label="Close preview"
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
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div
                id="invoiceTemplatePreviewBody"
                class="min-h-0 flex-1 overflow-auto bg-zinc-100 p-3
                       sm:p-5 dark:bg-zinc-900"
            >
                {{-- Image Preview --}}
                <div
                    id="invoiceTemplateImageWrapper"
                    class="hidden h-full min-h-[500px] items-start
                           justify-center overflow-auto"
                >
                    <img
                        id="invoiceTemplatePreviewImage"
                        src=""
                        alt="Invoice template preview"
                        class="max-w-full rounded-xl bg-white shadow-xl"
                    >
                </div>

                {{-- PDF Preview --}}
                <div
                    id="invoiceTemplatePdfWrapper"
                    class="hidden h-full min-h-[500px] overflow-hidden
                           rounded-xl bg-white shadow-xl"
                >
                    <iframe
                        id="invoiceTemplatePreviewPdf"
                        src=""
                        title="Invoice template PDF preview"
                        class="h-full min-h-[500px] w-full"
                        style="border: 0;"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
        (() => {
            const initialiseInvoiceTemplateSelection = () => {
                const section = document.getElementById(
                    'template-selection'
                );

                if (!section) {
                    return;
                }

                const cards = section.querySelectorAll(
                    '[data-template-card]'
                );

                const templateSlider = section.querySelector(
                    '[data-template-slider]'
                );

                const previousButtons = section.querySelectorAll(
                    '[data-template-slider-prev]'
                );

                const nextButtons = section.querySelectorAll(
                    '[data-template-slider-next]'
                );

                const sliderStatus = section.querySelector(
                    '[data-template-slider-status]'
                );

                const modal = document.getElementById(
                    'invoiceTemplatePreviewModal'
                );

                const modalTitle = document.getElementById(
                    'invoiceTemplatePreviewTitle'
                );

                const openNewTabLink = document.getElementById(
                    'invoiceTemplateOpenNewTab'
                );

                const imageWrapper = document.getElementById(
                    'invoiceTemplateImageWrapper'
                );

                const previewImage = document.getElementById(
                    'invoiceTemplatePreviewImage'
                );

                const pdfWrapper = document.getElementById(
                    'invoiceTemplatePdfWrapper'
                );

                const previewPdf = document.getElementById(
                    'invoiceTemplatePreviewPdf'
                );

                /*
                 * Slider navigation and arrow states.
                 */
                const getSliderStep = () => {
                    const firstCard = templateSlider?.querySelector(
                        '[data-template-card]'
                    );

                    if (!firstCard) {
                        return templateSlider?.clientWidth || 320;
                    }

                    const sliderStyles = window.getComputedStyle(
                        templateSlider
                    );

                    const gap = parseFloat(
                        sliderStyles.columnGap || sliderStyles.gap || 0
                    );

                    return firstCard.getBoundingClientRect().width + gap;
                };

                const updateSliderArrows = () => {
                    if (!templateSlider) {
                        return;
                    }

                    const maxScrollLeft = Math.max(
                        0,
                        templateSlider.scrollWidth -
                            templateSlider.clientWidth
                    );

                    const atStart = templateSlider.scrollLeft <= 2;
                    const atEnd =
                        templateSlider.scrollLeft >=
                        maxScrollLeft - 2;

                    previousButtons.forEach(button => {
                        button.disabled = atStart;
                    });

                    nextButtons.forEach(button => {
                        button.disabled = atEnd;
                    });

                    if (sliderStatus && cards.length) {
                        const cardsPerView =
                            window.innerWidth >= 1024
                                ? 3
                                : window.innerWidth >= 640
                                    ? 2
                                    : 1;

                        const pageWidth = getSliderStep() * cardsPerView;
                        const totalPages = Math.max(
                            1,
                            Math.ceil(cards.length / cardsPerView)
                        );

                        const currentPage = Math.min(
                            totalPages - 1,
                            Math.max(
                                0,
                                Math.round(templateSlider.scrollLeft / pageWidth)
                            )
                        );

                        sliderStatus.textContent =
                            `${currentPage + 1} / ${totalPages}`;
                    }
                };

                const moveTemplateSlider = direction => {
                    if (!templateSlider) {
                        return;
                    }

                    const cardsPerView =
                        window.innerWidth >= 1024
                            ? 3
                            : window.innerWidth >= 640
                                ? 2
                                : 1;

                    templateSlider.scrollBy({
                        left: getSliderStep() * cardsPerView * direction,
                        behavior: 'smooth'
                    });
                };

                previousButtons.forEach(button => {
                    if (
                        button.dataset.sliderListenerInitialised ===
                        'true'
                    ) {
                        return;
                    }

                    button.dataset.sliderListenerInitialised = 'true';

                    button.addEventListener('click', event => {
                        event.preventDefault();
                        event.stopPropagation();
                        moveTemplateSlider(-1);
                    });
                });

                nextButtons.forEach(button => {
                    if (
                        button.dataset.sliderListenerInitialised ===
                        'true'
                    ) {
                        return;
                    }

                    button.dataset.sliderListenerInitialised = 'true';

                    button.addEventListener('click', event => {
                        event.preventDefault();
                        event.stopPropagation();
                        moveTemplateSlider(1);
                    });
                });

                if (
                    templateSlider &&
                    templateSlider.dataset.scrollListenerInitialised !==
                        'true'
                ) {
                    templateSlider.dataset.scrollListenerInitialised =
                        'true';

                    templateSlider.addEventListener(
                        'scroll',
                        updateSliderArrows,
                        { passive: true }
                    );

                    window.addEventListener(
                        'resize',
                        updateSliderArrows
                    );
                }

                /*
                 * Template card selected state update.
                 */
                const updateTemplateCards = () => {
                    cards.forEach(card => {
                        const radio = card.querySelector(
                            '.template-radio'
                        );

                        const badge = card.querySelector(
                            '[data-selected-badge]'
                        );

                        const indicator = card.querySelector(
                            '[data-radio-indicator]'
                        );

                        const checkIcon = card.querySelector(
                            '[data-check-icon]'
                        );

                        const selectButton = card.querySelector(
                            '[data-select-button]'
                        );

                        const selectButtonText = card.querySelector(
                            '[data-select-button-text]'
                        );

                        const selected = radio?.checked === true;

                        card.classList.toggle(
                            'border-indigo-600',
                            selected
                        );

                        card.classList.toggle(
                            'shadow-lg',
                            selected
                        );

                        card.classList.toggle(
                            'ring-4',
                            selected
                        );

                        card.classList.toggle(
                            'ring-indigo-100',
                            selected
                        );

                        card.classList.toggle(
                            'dark:ring-indigo-950',
                            selected
                        );

                        card.classList.toggle(
                            'border-zinc-200',
                            !selected
                        );

                        card.classList.toggle(
                            'dark:border-zinc-700',
                            !selected
                        );

                        badge?.classList.toggle(
                            'hidden',
                            !selected
                        );

                        indicator?.classList.toggle(
                            'border-indigo-600',
                            selected
                        );

                        indicator?.classList.toggle(
                            'bg-indigo-600',
                            selected
                        );

                        indicator?.classList.toggle(
                            'border-zinc-300',
                            !selected
                        );

                        indicator?.classList.toggle(
                            'dark:border-zinc-600',
                            !selected
                        );

                        checkIcon?.classList.toggle(
                            'hidden',
                            !selected
                        );

                        selectButton?.classList.toggle(
                            'bg-emerald-600',
                            selected
                        );

                        selectButton?.classList.toggle(
                            'hover:bg-emerald-700',
                            selected
                        );

                        selectButton?.classList.toggle(
                            'bg-indigo-600',
                            !selected
                        );

                        selectButton?.classList.toggle(
                            'hover:bg-indigo-700',
                            !selected
                        );

                        if (selectButtonText) {
                            selectButtonText.textContent =
                                selected
                                    ? 'Selected'
                                    : 'Select Template';
                        }
                    });
                };

                /*
                 * Radio aur select button listeners.
                 *
                 * Explicit click handling isliye rakha gaya hai kyunki kuch
                 * layouts/components mein label click hidden radio ko reliably
                 * trigger nahi karta. Ab button, circle aur card par click se
                 * template turant select hoga.
                 */
                cards.forEach(card => {
                    const radio = card.querySelector(
                        '.template-radio'
                    );

                    if (!radio) {
                        return;
                    }

                    const selectTemplate = event => {
                        if (
                            event?.target?.closest(
                                '[data-preview-button]'
                            )
                        ) {
                            return;
                        }

                        if (event) {
                            event.preventDefault();
                            event.stopPropagation();
                        }

                        radio.checked = true;

                        radio.dispatchEvent(
                            new Event('change', {
                                bubbles: true
                            })
                        );
                    };

                    if (
                        radio.dataset.listenerInitialised !== 'true'
                    ) {
                        radio.dataset.listenerInitialised = 'true';

                        radio.addEventListener(
                            'change',
                            updateTemplateCards
                        );
                    }

                    card
                        .querySelectorAll(
                            '[data-select-button], [data-radio-indicator]'
                        )
                        .forEach(button => {
                            if (
                                button.dataset.selectListenerInitialised ===
                                'true'
                            ) {
                                return;
                            }

                            button.dataset.selectListenerInitialised =
                                'true';

                            button.addEventListener(
                                'click',
                                selectTemplate
                            );
                        });

                    if (
                        card.dataset.cardSelectListenerInitialised !==
                        'true'
                    ) {
                        card.dataset.cardSelectListenerInitialised =
                            'true';

                        card.addEventListener(
                            'click',
                            event => {
                                if (
                                    event.target.closest(
                                        '[data-preview-button]'
                                    )
                                ) {
                                    return;
                                }

                                selectTemplate(event);
                            }
                        );
                    }
                });

                /*
                 * Open preview modal.
                 */
                const openPreviewModal = ({
                    url,
                    type,
                    title
                }) => {
                    if (
                        !modal ||
                        !url ||
                        !type
                    ) {
                        return;
                    }

                    if (modalTitle) {
                        modalTitle.textContent =
                            title || 'Template Preview';
                    }

                    if (openNewTabLink) {
                        openNewTabLink.href = url;
                    }

                    imageWrapper?.classList.add('hidden');
                    imageWrapper?.classList.remove('flex');

                    pdfWrapper?.classList.add('hidden');

                    if (previewImage) {
                        previewImage.src = '';
                    }

                    if (previewPdf) {
                        previewPdf.src = '';
                    }

                    if (type === 'pdf') {
                        if (previewPdf) {
                            previewPdf.src =
                                `${url}#toolbar=1&navpanes=0&view=FitH`;
                        }

                        pdfWrapper?.classList.remove('hidden');
                    } else {
                        if (previewImage) {
                            previewImage.src = url;
                            previewImage.alt =
                                title || 'Invoice template preview';
                        }

                        imageWrapper?.classList.remove('hidden');
                        imageWrapper?.classList.add('flex');
                    }

                    modal.classList.remove('hidden');

                    document.documentElement.classList.add(
                        'overflow-hidden'
                    );

                    document.body.classList.add(
                        'overflow-hidden'
                    );
                };

                /*
                 * Close preview modal.
                 */
                const closePreviewModal = () => {
                    if (!modal) {
                        return;
                    }

                    modal.classList.add('hidden');

                    if (previewImage) {
                        previewImage.src = '';
                    }

                    if (previewPdf) {
                        previewPdf.src = '';
                    }

                    document.documentElement.classList.remove(
                        'overflow-hidden'
                    );

                    document.body.classList.remove(
                        'overflow-hidden'
                    );
                };

                /*
                 * Preview buttons.
                 */
                section
                    .querySelectorAll('[data-preview-button]')
                    .forEach(button => {
                        if (
                            button.dataset.listenerInitialised ===
                            'true'
                        ) {
                            return;
                        }

                        button.dataset.listenerInitialised = 'true';

                        button.addEventListener(
                            'click',
                            event => {
                                event.preventDefault();
                                event.stopPropagation();

                                openPreviewModal({
                                    url:
                                        button.dataset.previewUrl,
                                    type:
                                        button.dataset.previewType,
                                    title:
                                        button.dataset.previewTitle
                                });
                            }
                        );
                    });

                /*
                 * Modal close buttons.
                 */
                modal
                    ?.querySelectorAll('[data-preview-close]')
                    .forEach(button => {
                        if (
                            button.dataset.listenerInitialised ===
                            'true'
                        ) {
                            return;
                        }

                        button.dataset.listenerInitialised = 'true';

                        button.addEventListener(
                            'click',
                            closePreviewModal
                        );
                    });

                /*
                 * Escape key se modal close.
                 */
                if (
                    document.documentElement.dataset
                        .templatePreviewEscapeInitialised !== 'true'
                ) {
                    document.documentElement.dataset
                        .templatePreviewEscapeInitialised = 'true';

                    document.addEventListener(
                        'keydown',
                        event => {
                            if (
                                event.key === 'Escape' &&
                                modal &&
                                !modal.classList.contains('hidden')
                            ) {
                                closePreviewModal();
                            }
                        }
                    );
                }

                updateTemplateCards();

                requestAnimationFrame(updateSliderArrows);
            };

            document.addEventListener(
                'DOMContentLoaded',
                initialiseInvoiceTemplateSelection
            );

            document.addEventListener(
                'livewire:navigated',
                initialiseInvoiceTemplateSelection
            );

            initialiseInvoiceTemplateSelection();
        })();
</script>