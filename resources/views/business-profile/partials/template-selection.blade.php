<section
    id="template-selection"
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

                <p class="mt-1 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                    Select the design that will be used for your business invoices
                    and PDF documents.
                </p>
            </div>
        </div>
    </div>

    <div class="p-6">
        @if($billTemplates->isEmpty())
            <div
                class="rounded-2xl border border-dashed border-zinc-300
                       bg-zinc-50 px-6 py-12 text-center
                       dark:border-zinc-700 dark:bg-zinc-950"
            >
                <svg
                    class="mx-auto h-12 w-12 text-zinc-400"
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

                <h3 class="mt-4 font-black text-zinc-800 dark:text-white">
                    No invoice templates available
                </h3>

                <p class="mt-2 text-sm text-zinc-500">
                    Please add at least one bill template from the admin panel.
                </p>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($billTemplates as $template)
                    @php
                        $isSelected =
                            (string) old(
                                'pdf_template_id',
                                $business->pdf_template_id
                            ) === (string) $template->id;
                    @endphp

                    <label
                        class="invoice-template-card group relative cursor-pointer
                               overflow-hidden rounded-2xl border-2 bg-white
                               transition duration-200
                               dark:bg-zinc-950
                               {{ $isSelected
                                    ? 'border-indigo-600 ring-4 ring-indigo-100 dark:ring-indigo-950'
                                    : 'border-zinc-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-lg dark:border-zinc-700'
                               }}"
                        data-template-card
                    >
                        <input
                            type="radio"
                            name="pdf_template_id"
                            value="{{ $template->id }}"
                            class="template-radio sr-only"
                            @checked($isSelected)
                            required
                        >

                        <div
                            class="absolute right-3 top-3 z-10
                                   {{ $isSelected ? '' : 'hidden' }}"
                            data-selected-badge
                        >
                            <span
                                class="inline-flex items-center gap-1 rounded-full
                                       bg-indigo-600 px-3 py-1 text-xs
                                       font-bold text-white shadow-lg"
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

                        <div
                            class="aspect-[4/5] overflow-hidden bg-zinc-100
                                   dark:bg-zinc-900"
                        >
                            @if($template->preview)
                                <img
                                    src="{{ Storage::url($template->preview) }}"
                                    alt="{{ $template->name }}"
                                    loading="lazy"
                                    class="h-full w-full object-cover object-top
                                           transition duration-300 group-hover:scale-[1.03]"
                                >
                            @else
                                <div
                                    class="flex h-full flex-col items-center
                                           justify-center px-5 text-center"
                                >
                                    <svg
                                        class="h-12 w-12 text-zinc-400"
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

                                    <p class="mt-3 text-sm font-semibold text-zinc-500">
                                        Template preview unavailable
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3
                                        class="truncate text-base font-black
                                               text-zinc-900 dark:text-white"
                                    >
                                        {{ $template->name }}
                                    </h3>

                                    @if($template->page_name)
                                        <p
                                            class="mt-1 truncate text-xs
                                                   font-medium text-indigo-600
                                                   dark:text-indigo-300"
                                        >
                                            {{ $template->page_name }}
                                        </p>
                                    @endif
                                </div>

                                <span
                                    class="flex h-6 w-6 shrink-0 items-center
                                           justify-center rounded-full border-2
                                           {{ $isSelected
                                                ? 'border-indigo-600 bg-indigo-600'
                                                : 'border-zinc-300 dark:border-zinc-600'
                                           }}"
                                    data-radio-indicator
                                >
                                    <svg
                                        class="{{ $isSelected ? '' : 'hidden' }} h-4 w-4 text-white"
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
                                </span>
                            </div>

                            @if($template->description)
                                <p
                                    class="mt-3 line-clamp-3 text-sm
                                           leading-6 text-zinc-500"
                                >
                                    {{ $template->description }}
                                </p>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
        @endif

        @error('pdf_template_id')
            <p class="mt-4 text-sm font-medium text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>
</section>

@push('scripts')
    <script>
        (() => {
            const cards = document.querySelectorAll(
                '[data-template-card]'
            );

            if (!cards.length) {
                return;
            }

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

                    const selected = radio?.checked === true;

                    card.classList.toggle(
                        'border-indigo-600',
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

                    checkIcon?.classList.toggle(
                        'hidden',
                        !selected
                    );
                });
            };

            cards.forEach(card => {
                const radio = card.querySelector(
                    '.template-radio'
                );

                radio?.addEventListener(
                    'change',
                    updateTemplateCards
                );
            });

            updateTemplateCards();
        })();
    </script>
@endpush