{{-- Dedicated professional hospital billing view. Keep separate from general sales invoice view. --}}
{{-- Hospital billing view derived from the existing invoice create view. --}}
<x-layouts.app :title="__('Create Hospital Bill')">

    @php
        $activeDocType = in_array(
            $docType ?? 'proforma',
            ['tax', 'proforma', 'quotation'],
            true
        ) ? $docType : 'proforma';

        $createGuideStorageKey =
            'invoice-create-guide-v1-user-'
            . auth()->id()
            . '-business-'
            . ($activeBusinessId ?? 'default')
            . '-type-'
            . $activeDocType;

        $createGuideConfiguration = match($activeDocType) {
            'proforma' => [
                'title' => 'Create your Proforma Invoice',
                'description' => 'Customer को final tax invoice देने से पहले preliminary invoice तैयार करें.',
                'badge' => 'Proforma Guide',
                'icon_color' => 'bg-indigo-600',
                'steps' => [
                    [
                        'title' => 'Select customer',
                        'text' => 'Existing party search करें या + Service से customer जोड़ें.',
                    ],
                    [
                        'title' => 'Add items',
                        'text' => 'Items, quantity, price, GST और discount enter करें.',
                    ],
                    [
                        'title' => 'Save proforma',
                        'text' => 'Save करने के बाद customer को share या Tax Invoice में convert करें.',
                    ],
                ],
                'tip' => 'Proforma final tax bill नहीं होता. Customer approval के बाद इसे Tax Invoice में convert किया जा सकता है.',
            ],

            'quotation' => [
                'title' => 'Create your Quotation',
                'description' => 'Customer को products या services का price estimate भेजने के लिए quotation तैयार करें.',
                'badge' => 'Quotation Guide',
                'icon_color' => 'bg-cyan-600',
                'steps' => [
                    [
                        'title' => 'Select customer',
                        'text' => 'Quotation मांगने वाले customer को select करें.',
                    ],
                    [
                        'title' => 'Enter quotation items',
                        'text' => 'Item details, rate, tax, discount और terms enter करें.',
                    ],
                    [
                        'title' => 'Save and share',
                        'text' => 'Quotation save करके WhatsApp या PDF के माध्यम से share करें.',
                    ],
                ],
                'tip' => 'Customer quotation approve कर दे तो इसे सीधे Tax Invoice में convert किया जा सकता है.',
            ],

            default => [
                'title' => 'Create your Tax Invoice',
                'description' => 'Customer का final GST invoice बनाएं, payment record करें और invoice share करें.',
                'badge' => 'Invoice Guide',
                'icon_color' => 'bg-emerald-600',
                'steps' => [
                    [
                        'title' => 'Select customer',
                        'text' => 'Existing customer select करें या नया customer create करें.',
                    ],
                    [
                        'title' => 'Add invoice items',
                        'text' => 'Products, quantity, rate, GST और discount fill करें.',
                    ],
                    [
                        'title' => 'Save invoice',
                        'text' => 'Payment details check करके invoice save करें.',
                    ],
                ],
                'tip' => 'Invoice save होने के बाद View, Edit, Download, WhatsApp और Payment In options मिलेंगे.',
            ],
        };
    @endphp

    @if(false) {{-- Hospital view uses its own workflow header --}}
        <div
            id="createInvoiceSuggestionGuide"
            data-storage-key="{{ $createGuideStorageKey }}"
            class="relative mx-2 mb-3 overflow-hidden rounded-2xl
                border border-indigo-200
                bg-gradient-to-br from-indigo-50 via-white to-cyan-50
                p-5 shadow-sm
                dark:border-indigo-900/70
                dark:from-indigo-950/60
                dark:via-neutral-900
                dark:to-cyan-950/40"
        >
            {{-- Decorative backgrounds --}}
            <div
                class="pointer-events-none absolute -right-16 -top-16
                    h-40 w-40 rounded-full bg-indigo-200/40
                    blur-3xl dark:bg-indigo-700/20"
            ></div>

            <div
                class="pointer-events-none absolute -bottom-16 left-1/3
                    h-36 w-36 rounded-full bg-cyan-200/40
                    blur-3xl dark:bg-cyan-700/20"
            ></div>

            {{-- Close --}}
            <button
                type="button"
                onclick="dismissCreateInvoiceGuide()"
                aria-label="Close create invoice guide"
                title="Hide this guide"
                class="absolute right-3 top-3 z-20
                    inline-flex h-9 w-9 items-center justify-center
                    rounded-full border border-gray-200
                    bg-white/90 text-gray-500 shadow-sm transition
                    hover:bg-white hover:text-red-600
                    dark:border-neutral-700 dark:bg-neutral-800
                    dark:text-neutral-300 dark:hover:text-red-400"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18 18 6M6 6l12 12"
                    />
                </svg>
            </button>

            <div class="relative z-10 pr-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start">

                    {{-- Icon --}}
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center
                            rounded-2xl text-white shadow-lg
                            {{ $createGuideConfiguration['icon_color'] }}"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ $createGuideConfiguration['title'] }}
                            </h2>

                            <span
                                class="rounded-full bg-indigo-100 px-2.5 py-1
                                    text-[10px] font-bold uppercase tracking-wide
                                    text-indigo-700
                                    dark:bg-indigo-900/60 dark:text-indigo-300"
                            >
                                {{ $createGuideConfiguration['badge'] }}
                            </span>
                        </div>

                        <p class="mt-1 text-sm text-gray-600 dark:text-neutral-300">
                            {{ $createGuideConfiguration['description'] }}
                        </p>

                        {{-- Steps --}}
                        <div class="mt-4 grid gap-3 md:grid-cols-3">
                            @foreach($createGuideConfiguration['steps'] as $stepIndex => $step)
                                <div
                                    class="rounded-xl border border-indigo-100
                                        bg-white/80 p-3 shadow-sm
                                        dark:border-indigo-900/50
                                        dark:bg-neutral-800/70"
                                >
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="flex h-7 w-7 shrink-0 items-center
                                                justify-center rounded-full
                                                bg-indigo-100 text-xs font-bold
                                                text-indigo-700
                                                dark:bg-indigo-900/70
                                                dark:text-indigo-300"
                                        >
                                            {{ $stepIndex + 1 }}
                                        </span>

                                        <div>
                                            <h3 class="text-xs font-bold text-gray-900 dark:text-white">
                                                {{ $step['title'] }}
                                            </h3>

                                            <p class="mt-1 text-[11px] leading-5 text-gray-500 dark:text-neutral-400">
                                                {{ $step['text'] }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Tip --}}
                        <div
                            class="mt-3 rounded-xl border border-amber-200
                                bg-amber-50 p-3 text-xs text-amber-800
                                dark:border-amber-900/60
                                dark:bg-amber-950/30
                                dark:text-amber-300"
                        >
                            <strong>Helpful tip:</strong>
                            {{ $createGuideConfiguration['tip'] }}
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <button
                                type="button"
                                onclick="focusCreateInvoiceForm()"
                                class="inline-flex items-center justify-center
                                    rounded-xl bg-indigo-600 px-4 py-2
                                    text-xs font-semibold text-white
                                    transition hover:bg-indigo-700"
                            >
                                Start Creating
                            </button>

                            <button
                                type="button"
                                onclick="dismissCreateInvoiceGuide()"
                                class="inline-flex items-center justify-center
                                    rounded-xl border border-gray-300
                                    bg-white px-4 py-2 text-xs font-semibold
                                    text-gray-700 transition hover:bg-gray-50
                                    dark:border-neutral-600
                                    dark:bg-neutral-800
                                    dark:text-neutral-200
                                    dark:hover:bg-neutral-700"
                            >
                                Got it, hide guide
                            </button>

                            <span class="text-[11px] text-gray-500 dark:text-neutral-400">
                                Existing {{ ucfirst($activeDocType) }} records:
                                {{ $currentTypeCount ?? 0 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reopen Guide --}}
        <div
            id="createInvoiceSuggestionReopen"
            class="mx-2 mb-3 hidden justify-end"
        >
            <button
                type="button"
                onclick="showCreateInvoiceGuide()"
                class="inline-flex items-center gap-2 rounded-xl
                    border border-indigo-200 bg-indigo-50
                    px-3 py-2 text-xs font-semibold text-indigo-700
                    transition hover:bg-indigo-100
                    dark:border-indigo-900
                    dark:bg-indigo-950/40
                    dark:text-indigo-300"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8.228 9a3.001 3.001 0 1 1
                        5.824 1c0 2-3 2-3 4m.01 4h.01
                        M21 12a9 9 0 1 1-18 0
                        9 9 0 0 1 18 0Z"
                    />
                </svg>

                Show Create Guide
            </button>
        </div>
    @endif
    <div x-data="invoiceForm()" x-init="init()"
        class="invoice-create-page w-full max-w-none min-w-0 space-y-1.5 px-1.5 py-1.5 sm:px-2 lg:px-2">

        {{-- ================= BARCODE SCANNER ================= --}}
        <div
            class="hidden rounded-lg border border-blue-200 bg-blue-50 p-3
                dark:border-blue-900 dark:bg-[#1A1D23]"
        >
            <div class="mb-2 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">
                        Barcode Scanner
                    </h2>

                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        USB scanner se scan karein ya barcode manually enter karein.
                    </p>
                </div>

                <span
                    x-show="barcodeScanning"
                    class="text-xs font-semibold text-blue-600"
                >
                    Searching...
                </span>
            </div>


            {{-- ========================================================= --}}
            {{-- CREATE FIRST CLIENT SUGGESTION --}}
            {{-- ========================================================= --}}

            <div
                x-show="Array.isArray(clients) && clients.length === 0"
                x-transition
                class="client-create-suggestion relative overflow-hidden rounded-xl
                    border border-orange-300
                    bg-gradient-to-r from-orange-50 via-amber-50 to-yellow-50
                    p-4 shadow-md
                    dark:border-orange-900/70
                    dark:from-orange-950/50
                    dark:via-amber-950/30
                    dark:to-neutral-900"
                style="display: none;"
            >
                {{-- Decorative glow --}}
                <div
                    class="pointer-events-none absolute -right-12 -top-12
                        h-32 w-32 rounded-full bg-orange-300/30 blur-3xl
                        dark:bg-orange-600/20"
                ></div>

                <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex min-w-0 items-start gap-3">

                        {{-- Animated Icon --}}
                        <div class="relative shrink-0">
                            <span
                                class="absolute inset-0 animate-ping rounded-xl
                                    bg-orange-400 opacity-30"
                            ></span>

                            <div
                                class="relative flex h-11 w-11 items-center justify-center
                                    rounded-xl bg-orange-500 text-white shadow-lg"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3
                                        M13 7a4 4 0 11-8 0 4 4 0 018 0Z
                                        M3 21v-2a4 4 0 014-4h4a4 4 0 014 4v2"
                                    />
                                </svg>
                            </div>
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-sm font-bold text-orange-900 dark:text-orange-200">
                                    Pehle Patient Register Karein
                                </h2>

                                <span
                                    class="rounded-full bg-orange-200 px-2 py-0.5
                                        text-[10px] font-bold uppercase tracking-wide
                                        text-orange-800
                                        dark:bg-orange-900/60 dark:text-orange-300"
                                >
                                    Required Step
                                </span>
                            </div>

                            <p class="mt-1 text-xs leading-5 text-orange-800 dark:text-orange-300">
                                Abhi is business me koi patient available nahi hai.
                                Invoice banane ke liye pehle patient register karna zaroori hai.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="openClientModal()"
                        class="client-suggestion-button inline-flex shrink-0 items-center
                            justify-center gap-2 rounded-xl bg-orange-600 px-5 py-2.5
                            text-xs font-bold text-white shadow-lg
                            transition hover:bg-orange-700 hover:shadow-xl
                            focus:outline-none focus:ring-4 focus:ring-orange-200
                            dark:focus:ring-orange-900"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>

                        Register First Patient
                    </button>

                </div>
            </div>
            <form
                class="flex flex-wrap items-center gap-2"
                @submit.prevent="scanBarcode()"
            >
                <div class="relative min-w-[260px] flex-1">
                    <span
                        class="pointer-events-none absolute inset-y-0 left-0
                            flex items-center pl-3 text-gray-500"
                    >
                        ▦
                    </span>

                    <input
                        x-ref="barcodeInput"
                        x-model.trim="barcodeInput"
                        type="text"
                        inputmode="text"
                        autocomplete="off"
                        placeholder="Scan barcode and press Enter"
                        @keydown.enter.prevent="scanBarcode()"
                        class="w-full rounded-lg border border-gray-300 bg-white
                            py-2 pl-9 pr-3 text-sm text-gray-900 outline-none
                            focus:border-blue-500 focus:ring-2 focus:ring-blue-200
                            dark:border-neutral-700 dark:bg-[#242833]
                            dark:text-white"
                    >
                </div>

                <button
                    type="submit"
                    :disabled="barcodeScanning"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600
                        px-4 py-2 text-sm font-semibold text-white
                        hover:bg-blue-700 disabled:cursor-not-allowed
                        disabled:opacity-50"
                >
                    <svg
                        x-show="barcodeScanning"
                        class="h-4 w-4 animate-spin"
                        viewBox="0 0 24 24"
                        fill="none"
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
                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                        ></path>
                    </svg>

                    <span x-text="barcodeScanning ? 'Searching...' : 'Add Charge'"></span>
                </button>

                <button
                    type="button"
                    @click="clearBarcodeScanner()"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2
                        text-sm font-semibold text-gray-700 hover:bg-gray-100
                        dark:border-neutral-700 dark:bg-[#242833]
                        dark:text-white"
                >
                    Clear
                </button>
            </form>

            <div
                x-show="barcodeMessage"
                x-transition
                class="mt-2 rounded-lg px-3 py-2 text-sm"
                :class="barcodeError
                    ? 'border border-red-200 bg-red-50 text-red-700'
                    : 'border border-green-200 bg-green-50 text-green-700'"
                x-text="barcodeMessage"
            ></div>
        </div>


        <style>
            /*
             * Invoice item table ko browser compress na kare.
             * Extra fields horizontal scroll me jayengi, inputs chhote nahi honge.
             */
            .invoice-table {
                width: max-content !important;
                min-width: 100% !important;
                table-layout: auto !important;
            }

            .invoice-table th,
            .invoice-table td {
                padding: 7px 8px !important;
                vertical-align: top;
                white-space: nowrap;
                box-sizing: border-box;
            }

            .invoice-table input,
            .invoice-table select {
                width: 100% !important;
                min-width: 0;
                padding: 6px 8px !important;
                height: 36px;
                font-size: 13px;
                box-sizing: border-box;
            }

            .invoice-table textarea {
                width: 100% !important;
                padding: 7px 9px !important;
                min-height: 58px;
                height: auto;
                font-size: 13px;
                white-space: normal;
                box-sizing: border-box;
            }

            /* Serial number */
            .invoice-table th:nth-child(1),
            .invoice-table td:nth-child(1) {
                width: 52px !important;
                min-width: 52px !important;
                max-width: 52px !important;
            }

            /* Item search + description */
            .invoice-table th:nth-child(2),
            .invoice-table td:nth-child(2) {
                width: 390px !important;
                min-width: 390px !important;
                max-width: 390px !important;
            }

            /* HSN/SAC */
            .invoice-table th:nth-child(3),
            .invoice-table td:nth-child(3) {
                width: 125px !important;
                min-width: 125px !important;
            }

            /* Qty */
            .invoice-table th:nth-child(4),
            .invoice-table td:nth-child(4) {
                width: 90px !important;
                min-width: 90px !important;
            }

            /* Unit Rate */
            .invoice-table th:nth-child(5),
            .invoice-table td:nth-child(5) {
                width: 145px !important;
                min-width: 145px !important;
            }

            /* Making Rate */
            .invoice-table th:nth-child(6),
            .invoice-table td:nth-child(6) {
                width: 135px !important;
                min-width: 135px !important;
            }

            /* Making Type */
            .invoice-table th:nth-child(7),
            .invoice-table td:nth-child(7) {
                width: 180px !important;
                min-width: 180px !important;
            }

            /* Gold/Silver/Stone/Diamond fields */
            .invoice-table th:nth-child(n+8):nth-child(-n+15),
            .invoice-table td:nth-child(n+8):nth-child(-n+15) {
                width: 145px !important;
                min-width: 145px !important;
            }

            /* Tax */
            .invoice-table th:nth-child(16),
            .invoice-table td:nth-child(16) {
                width: 100px !important;
                min-width: 100px !important;
            }

            /* Amount */
            .invoice-table th:nth-child(17),
            .invoice-table td:nth-child(17) {
                width: 155px !important;
                min-width: 155px !important;
            }

            /* Action */
            .invoice-table th:nth-child(18),
            .invoice-table td:nth-child(18) {
                width: 75px !important;
                min-width: 75px !important;
                max-width: 75px !important;
            }

            .invoice-table tbody tr:last-child td {
                border-bottom: 0;
            }

            /* Smooth and clearly visible horizontal scrollbar */
            .invoice-items-scroll {
                overflow-x: auto;
                overflow-y: visible;
                scrollbar-width: auto;
                scrollbar-color: #9ca3af #e5e7eb;
            }

            .invoice-items-scroll::-webkit-scrollbar {
                height: 12px;
            }

            .invoice-items-scroll::-webkit-scrollbar-track {
                background: #e5e7eb;
                border-radius: 999px;
            }

            .invoice-items-scroll::-webkit-scrollbar-thumb {
                background: #9ca3af;
                border: 2px solid #e5e7eb;
                border-radius: 999px;
            }

            .invoice-items-scroll::-webkit-scrollbar-thumb:hover {
                background: #6b7280;
            }

            @media (max-width: 640px) {
                .invoice-table th:nth-child(2),
                .invoice-table td:nth-child(2) {
                    width: 300px !important;
                    min-width: 300px !important;
                    max-width: 300px !important;
                }
            }

            /* Full-width, compact invoice screen */
            .invoice-create-page {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                font-size: 12px;
                overflow-x: hidden;
            }

            .invoice-create-page h1 { font-size: 16px !important; }
            .invoice-create-page h2,
            .invoice-create-page h3 { line-height: 1.25; }

            .invoice-create-page label,
            .invoice-create-page p,
            .invoice-create-page span,
            .invoice-create-page button,
            .invoice-create-page input,
            .invoice-create-page select,
            .invoice-create-page textarea {
                font-size: 11px;
            }

            .invoice-create-page input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
            .invoice-create-page select {
                min-height: 30px;
                padding-top: 4px !important;
                padding-bottom: 4px !important;
            }

            .invoice-create-page textarea {
                line-height: 1.35;
            }

            .invoice-table th,
            .invoice-table td {
                padding: 5px 6px !important;
            }

            .invoice-table th {
                font-size: 9.5px !important;
                line-height: 1.15;
            }

            .invoice-table input,
            .invoice-table select {
                height: 31px !important;
                min-height: 31px !important;
                padding: 4px 6px !important;
                font-size: 11px !important;
            }

            .invoice-table textarea {
                min-height: 44px !important;
                padding: 5px 6px !important;
                font-size: 11px !important;
            }

            /* Keep fields readable, but use screen space efficiently */
            .invoice-table th:nth-child(2),
            .invoice-table td:nth-child(2) {
                width: 340px !important;
                min-width: 340px !important;
                max-width: 340px !important;
            }

            .invoice-table th:nth-child(n+3):nth-child(-n+18),
            .invoice-table td:nth-child(n+3):nth-child(-n+18) {
                padding-left: 5px !important;
                padding-right: 5px !important;
            }

            @media (min-width: 1536px) {
                .invoice-create-page { padding-left: 10px !important; padding-right: 10px !important; }
            }
        

            /* =========================================================
             * ULTRA COMPACT DESKTOP INVOICE UI
             * ========================================================= */
            .invoice-create-page {
                font-size: 12px;
                line-height: 1.25;
            }

            .invoice-create-page > div,
            .invoice-create-page form > div {
                border-radius: 8px !important;
            }

            .invoice-create-page h1 {
                font-size: 15px !important;
                line-height: 20px !important;
            }

            .invoice-create-page h2,
            .invoice-create-page .text-lg {
                font-size: 13px !important;
                line-height: 18px !important;
            }

            .invoice-create-page label,
            .invoice-create-page .text-sm {
                font-size: 11px !important;
            }

            .invoice-create-page .text-xs {
                font-size: 10px !important;
            }

            .invoice-create-page input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
            .invoice-create-page select {
                min-height: 30px;
                font-size: 11px !important;
                line-height: 16px;
                border-radius: 6px !important;
            }

            .invoice-create-page button {
                font-size: 11px !important;
                border-radius: 6px !important;
            }

            /* Barcode area */
            .invoice-create-page > div:first-child {
                padding: 8px 10px !important;
            }

            .invoice-create-page > div:first-child form {
                gap: 6px !important;
            }

            .invoice-create-page > div:first-child input {
                height: 30px !important;
                padding-top: 4px !important;
                padding-bottom: 4px !important;
            }

            .invoice-create-page > div:first-child button {
                min-height: 30px !important;
                padding: 4px 12px !important;
            }

            /* Main form cards */
            .invoice-create-page form > .border,
            .invoice-create-page form > .my-4,
            .invoice-create-page form > .grid {
                margin-top: 6px !important;
                margin-bottom: 6px !important;
            }

            /* Invoice table sizing */
            .invoice-table {
                font-size: 10px !important;
            }

            .invoice-table th,
            .invoice-table td {
                padding: 4px 5px !important;
            }

            .invoice-table thead th {
                height: 26px;
                font-size: 9px !important;
                line-height: 11px !important;
                letter-spacing: .01em !important;
            }

            .invoice-table input,
            .invoice-table select {
                height: 30px !important;
                min-height: 30px !important;
                padding: 4px 6px !important;
                font-size: 10px !important;
            }

            .invoice-table textarea {
                min-height: 40px !important;
                height: 40px !important;
                padding: 5px 6px !important;
                font-size: 10px !important;
                line-height: 14px !important;
                resize: vertical;
            }

            .invoice-table th:nth-child(1),
            .invoice-table td:nth-child(1) {
                width: 38px !important;
                min-width: 38px !important;
                max-width: 38px !important;
            }

            .invoice-table th:nth-child(2),
            .invoice-table td:nth-child(2) {
                left: 38px !important;
                width: 300px !important;
                min-width: 300px !important;
                max-width: 300px !important;
            }

            .invoice-table th:nth-child(3),
            .invoice-table td:nth-child(3) {
                width: 100px !important;
                min-width: 100px !important;
            }

            .invoice-table th:nth-child(4),
            .invoice-table td:nth-child(4) {
                width: 70px !important;
                min-width: 70px !important;
            }

            .invoice-table th:nth-child(5),
            .invoice-table td:nth-child(5) {
                width: 115px !important;
                min-width: 115px !important;
            }

            .invoice-table th:nth-child(6),
            .invoice-table td:nth-child(6) {
                width: 105px !important;
                min-width: 105px !important;
            }

            .invoice-table th:nth-child(7),
            .invoice-table td:nth-child(7) {
                width: 135px !important;
                min-width: 135px !important;
            }

            .invoice-table th:nth-child(n+8):nth-child(-n+15),
            .invoice-table td:nth-child(n+8):nth-child(-n+15) {
                width: 110px !important;
                min-width: 110px !important;
            }

            .invoice-table th:nth-child(16),
            .invoice-table td:nth-child(16) {
                width: 75px !important;
                min-width: 75px !important;
            }

            .invoice-table th:nth-child(17),
            .invoice-table td:nth-child(17) {
                width: 120px !important;
                min-width: 120px !important;
            }

            .invoice-table th:nth-child(18),
            .invoice-table td:nth-child(18) {
                width: 52px !important;
                min-width: 52px !important;
                max-width: 52px !important;
            }

            .invoice-items-scroll::-webkit-scrollbar {
                height: 8px;
            }

            /* Compact table footer */
            .invoice-items-scroll + div {
                min-height: 42px;
                padding-top: 7px !important;
                padding-bottom: 7px !important;
            }

            @media (min-width: 1024px) {
                .invoice-create-page {
                    width: 100% !important;
                    max-width: none !important;
                }
            }

            @media (max-width: 640px) {
                .invoice-table th:nth-child(2),
                .invoice-table td:nth-child(2) {
                    width: 260px !important;
                    min-width: 260px !important;
                    max-width: 260px !important;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | First Client Suggestion
            |--------------------------------------------------------------------------
            */

            @keyframes clientSuggestionGlow {
                0%,
                100% {
                    box-shadow:
                        0 0 0 0 rgba(249, 115, 22, 0),
                        0 4px 12px rgba(249, 115, 22, 0.08);
                }

                50% {
                    box-shadow:
                        0 0 0 5px rgba(249, 115, 22, 0.14),
                        0 8px 22px rgba(249, 115, 22, 0.18);
                }
            }

            .client-create-suggestion {
                animation: clientSuggestionGlow 1.5s ease-in-out infinite;
            }

            @keyframes clientButtonPulse {
                0%,
                100% {
                    transform: scale(1);
                }

                50% {
                    transform: scale(1.04);
                }
            }

            .client-suggestion-button {
                animation: clientButtonPulse 1.2s ease-in-out infinite;
            }

            @media (prefers-reduced-motion: reduce) {
                .client-create-suggestion,
                .client-suggestion-button {
                    animation: none;
                }
            }



            /* =========================================================
             * CLIENT FIRST SUGGESTION TOOLTIP
             * ========================================================= */
            .client-guide-wrap {
                position: relative;
                padding-right: 0;
                padding-left: 188px;
                min-height: 38px;
                overflow: visible !important;
                z-index: 999;
            }

            .client-guide-tooltip {
                position: absolute;
                right: calc(100% + 10px);
                top: 50%;
                z-index: 99999;
                display: block;
                width: 178px;
                transform: translateY(-50%);
                border-radius: 8px;
                background: #111827;
                padding: 7px 10px;
                color: #ffffff;
                font-size: 10px;
                line-height: 1.2;
                box-shadow: 0 8px 20px rgba(17, 24, 39, 0.28);
                pointer-events: none;
            }

            .client-guide-arrow {
                position: absolute;
                right: -7px;
                top: 50%;
                width: 14px;
                height: 14px;
                transform: translateY(-50%) rotate(45deg);
                border-radius: 2px;
                background: #111827;
            }

            @keyframes clientGuidePulse {
                0%, 100% {
                    transform: scale(1);
                    box-shadow: 0 0 0 0 rgba(234, 88, 12, 0.60);
                }

                50% {
                    transform: scale(1.04);
                    box-shadow: 0 0 0 8px rgba(234, 88, 12, 0);
                }
            }

            .client-guide-button {
                animation: clientGuidePulse 1.2s ease-in-out infinite;
            }

            @media (max-width: 640px) {
                .client-guide-wrap {
                    width: 100%;
                    padding-left: 0;
                    padding-top: 52px;
                    justify-content: flex-end;
                }

                .client-guide-tooltip {
                    right: 0;
                    top: 0;
                    width: 188px;
                    transform: none;
                }

                .client-guide-arrow {
                    right: 24px;
                    top: auto;
                    bottom: -7px;
                    transform: rotate(45deg);
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .client-guide-button {
                    animation: none;
                }
            }



            /* =========================================================
             * BLUE + NEW ITEM BUTTON TOOLTIP
             * Shows only when database item catalog is empty
             * ========================================================= */
            .new-item-guide-wrap {
                position: relative;
                overflow: visible !important;
                z-index: 1000;
            }

            .new-item-guide-tooltip {
                position: absolute;
                right: calc(100% + 10px);
                top: 50%;
                z-index: 999999;
                display: block;
                width: 180px;
                transform: translateY(-50%);
                border-radius: 8px;
                background: #111827;
                padding: 7px 10px;
                color: #ffffff;
                font-size: 10px;
                line-height: 1.2;
                white-space: normal;
                box-shadow: 0 8px 20px rgba(17, 24, 39, 0.30);
                pointer-events: none;
            }

            .new-item-guide-arrow {
                position: absolute;
                right: -7px;
                top: 50%;
                width: 14px;
                height: 14px;
                transform: translateY(-50%) rotate(45deg);
                border-radius: 2px;
                background: #111827;
            }

            @keyframes newItemGuidePulse {
                0%,
                100% {
                    transform: scale(1);
                    box-shadow: 0 0 0 0 rgba(2, 132, 199, 0.65);
                }

                50% {
                    transform: scale(1.06);
                    box-shadow: 0 0 0 7px rgba(2, 132, 199, 0);
                }
            }

            .new-item-guide-button {
                animation: newItemGuidePulse 1.2s ease-in-out infinite;
            }

            @media (max-width: 640px) {
                .new-item-guide-tooltip {
                    right: 0;
                    top: calc(100% + 10px);
                    width: 185px;
                    transform: none;
                }

                .new-item-guide-arrow {
                    right: 18px;
                    top: -7px;
                    transform: rotate(45deg);
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .new-item-guide-button {
                    animation: none;
                }
            }



            @keyframes clientNewButtonAttention {
                0%,
                100% {
                    box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.65);
                    transform: scale(1);
                }

                50% {
                    box-shadow: 0 0 0 8px rgba(249, 115, 22, 0);
                    transform: scale(1.04);
                }
            }

            .client-new-button-attention {
                animation: clientNewButtonAttention 1.2s ease-in-out infinite;
            }


            /* =========================================================
             * MOBILE INVOICE ITEMS TABLE FIX
             * All item fields remain accessible by horizontal swipe.
             * Sticky item/action columns are disabled on small screens.
             * ========================================================= */
            @media (max-width: 640px) {
                .invoice-create-page {
                    overflow-x: visible !important;
                }

                .invoice-items-scroll {
                    display: block !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    overflow-x: auto !important;
                    overflow-y: visible !important;
                    -webkit-overflow-scrolling: touch;
                    touch-action: pan-x pan-y;
                    overscroll-behavior-x: contain;
                    scrollbar-width: auto;
                    scrollbar-color: #6b7280 #e5e7eb;
                }

                .invoice-items-scroll::-webkit-scrollbar {
                    display: block !important;
                    height: 9px !important;
                }

                .invoice-items-scroll::-webkit-scrollbar-track {
                    background: #e5e7eb;
                    border-radius: 999px;
                }

                .invoice-items-scroll::-webkit-scrollbar-thumb {
                    background: #6b7280;
                    border: 2px solid #e5e7eb;
                    border-radius: 999px;
                }

                .invoice-table {
                    display: table !important;
                    width: max-content !important;
                    min-width: max-content !important;
                    table-layout: auto !important;
                }

                /*
                 * Mobile पर sticky columns overlap कर रहे थे.
                 * इसलिए सभी headers/cells normal scrolling columns रहेंगे.
                 */
                .invoice-table thead,
                .invoice-table th,
                .invoice-table td {
                    position: static !important;
                    left: auto !important;
                    right: auto !important;
                }

                .invoice-table th,
                .invoice-table td {
                    white-space: nowrap !important;
                }

                .invoice-table th:nth-child(1),
                .invoice-table td:nth-child(1) {
                    width: 38px !important;
                    min-width: 38px !important;
                    max-width: 38px !important;
                }

                .invoice-table th:nth-child(2),
                .invoice-table td:nth-child(2) {
                    width: 245px !important;
                    min-width: 245px !important;
                    max-width: 245px !important;
                }

                .invoice-table th:nth-child(3),
                .invoice-table td:nth-child(3) {
                    width: 95px !important;
                    min-width: 95px !important;
                }

                .invoice-table th:nth-child(4),
                .invoice-table td:nth-child(4) {
                    width: 75px !important;
                    min-width: 75px !important;
                }

                .invoice-table th:nth-child(5),
                .invoice-table td:nth-child(5) {
                    width: 110px !important;
                    min-width: 110px !important;
                }

                .invoice-table th:nth-child(6),
                .invoice-table td:nth-child(6) {
                    width: 105px !important;
                    min-width: 105px !important;
                }

                .invoice-table th:nth-child(7),
                .invoice-table td:nth-child(7) {
                    width: 135px !important;
                    min-width: 135px !important;
                }

                .invoice-table th:nth-child(n+8):nth-child(-n+15),
                .invoice-table td:nth-child(n+8):nth-child(-n+15) {
                    width: 110px !important;
                    min-width: 110px !important;
                }

                .invoice-table th:nth-child(16),
                .invoice-table td:nth-child(16) {
                    width: 80px !important;
                    min-width: 80px !important;
                }

                .invoice-table th:nth-child(17),
                .invoice-table td:nth-child(17) {
                    width: 120px !important;
                    min-width: 120px !important;
                }

                .invoice-table th:nth-child(18),
                .invoice-table td:nth-child(18) {
                    width: 58px !important;
                    min-width: 58px !important;
                    max-width: 58px !important;
                }

                .invoice-table input,
                .invoice-table select {
                    width: 100% !important;
                    min-width: 0 !important;
                    height: 34px !important;
                    font-size: 11px !important;
                }

                .invoice-table textarea {
                    width: 100% !important;
                    min-width: 0 !important;
                    min-height: 48px !important;
                    white-space: normal !important;
                    font-size: 11px !important;
                }

                .invoice-table td:nth-child(2) > div,
                .invoice-table td:nth-child(2) textarea {
                    max-width: 100% !important;
                }

                .new-item-guide-tooltip {
                    right: 0 !important;
                    left: auto !important;
                    top: calc(100% + 10px) !important;
                    width: 180px !important;
                    transform: none !important;
                }

                .new-item-guide-arrow {
                    top: -7px !important;
                    right: 18px !important;
                    left: auto !important;
                    transform: rotate(45deg) !important;
                }
            }

            /* =========================================================
             * MOBILE SIDEBAR OVERLAP FIX
             * Creates one low-level stacking context for this page only.
             * Existing desktop/table/dropdown behavior remains unchanged.
             * ========================================================= */
            @media (max-width: 1023px) {
                .invoice-create-page {
                    position: relative !important;
                    z-index: 0 !important;
                    isolation: isolate !important;
                }
            }


            /* =========================================================
             * PROFESSIONAL HOSPITAL BILLING THEME
             * ========================================================= */
            .hospital-hero { isolation: isolate; }
            .hospital-card { position: relative; }
            .hospital-card::before {
                content: '';
                position: absolute;
                inset: 0 auto 0 0;
                width: 4px;
                border-radius: 999px;
                background: linear-gradient(to bottom, #0891b2, #0d9488);
            }
            .patient-card::before { background: linear-gradient(to bottom, #0284c7, #06b6d4); }
            .clinical-card::before { background: linear-gradient(to bottom, #0d9488, #10b981); }
            .invoice-create-page input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
            .invoice-create-page select,
            .invoice-create-page textarea {
                border-color: #cbd5e1;
                background-color: #fff;
                transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
            }
            .invoice-create-page input:focus,
            .invoice-create-page select:focus,
            .invoice-create-page textarea:focus {
                border-color: #0891b2 !important;
                box-shadow: 0 0 0 3px rgba(8,145,178,.12) !important;
                outline: none !important;
            }
            .invoice-table thead {
                background: linear-gradient(90deg, #0e7490, #0f766e) !important;
                color: #fff !important;
            }
            .invoice-table thead th {
                background: transparent !important;
                color: #fff !important;
                border-color: rgba(255,255,255,.18) !important;
            }
            .invoice-table tbody tr:nth-child(even) { background: rgba(236,254,255,.35); }
            .invoice-table tbody tr:hover { background: rgba(207,250,254,.6) !important; }
            .invoice-table td { border-color: #e2e8f0 !important; }
            /* Dark controls only when the application itself has .dark class. */
            .dark .invoice-create-page input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
            .dark .invoice-create-page select,
            .dark .invoice-create-page textarea {
                background-color: #202832;
                border-color: #374151;
                color: #f8fafc;
            }
            .dark .invoice-table tbody tr:nth-child(even) {
                background: rgba(8,47,73,.16);
            }
            @media (max-width: 640px) {
                .hospital-hero { border-radius: 14px !important; padding: 16px !important; }
                .hospital-card { border-radius: 14px !important; }
            }

            /* =========================================================
             * LIGHT MODE FORM VISIBILITY FIX
             * Browser dark preference must not make light-mode fields black.
             * ========================================================= */
            html:not(.dark) .invoice-create-page input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
            html:not(.dark) .invoice-create-page select,
            html:not(.dark) .invoice-create-page textarea,
            body:not(.dark) .invoice-create-page input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
            body:not(.dark) .invoice-create-page select,
            body:not(.dark) .invoice-create-page textarea {
                background-color: #ffffff !important;
                color: #0f172a !important;
                border-color: #cbd5e1 !important;
                -webkit-text-fill-color: #0f172a !important;
                color-scheme: light;
            }

            html:not(.dark) .invoice-create-page input::placeholder,
            html:not(.dark) .invoice-create-page textarea::placeholder,
            body:not(.dark) .invoice-create-page input::placeholder,
            body:not(.dark) .invoice-create-page textarea::placeholder {
                color: #64748b !important;
                opacity: 1 !important;
                -webkit-text-fill-color: #64748b !important;
            }

            html:not(.dark) .invoice-create-page select option,
            body:not(.dark) .invoice-create-page select option {
                background: #ffffff !important;
                color: #0f172a !important;
            }

            html:not(.dark) .invoice-table input:not([type="checkbox"]):not([type="radio"]),
            html:not(.dark) .invoice-table select,
            html:not(.dark) .invoice-table textarea,
            body:not(.dark) .invoice-table input:not([type="checkbox"]):not([type="radio"]),
            body:not(.dark) .invoice-table select,
            body:not(.dark) .invoice-table textarea {
                background-color: #ffffff !important;
                color: #0f172a !important;
                border-color: #cbd5e1 !important;
                -webkit-text-fill-color: #0f172a !important;
            }

            html:not(.dark) .invoice-table input::placeholder,
            html:not(.dark) .invoice-table textarea::placeholder,
            body:not(.dark) .invoice-table input::placeholder,
            body:not(.dark) .invoice-table textarea::placeholder {
                color: #64748b !important;
                opacity: 1 !important;
            }

            html:not(.dark) input[type="date"],
            html:not(.dark) input[type="datetime-local"],
            html:not(.dark) input[type="time"],
            body:not(.dark) input[type="date"],
            body:not(.dark) input[type="datetime-local"],
            body:not(.dark) input[type="time"] {
                color-scheme: light !important;
            }

            .dark .invoice-create-page input::placeholder,
            .dark .invoice-create-page textarea::placeholder {
                color: #94a3b8 !important;
                opacity: 1 !important;
            }
        </style>
        {{-- errors --}}
        @if ($errors->any())
            <div class="p-3 rounded border border-red-300 bg-red-50 text-red-700">
                <ul class="list-disc ml-4">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- JSON payloads --}}
        <script type="application/json" id="clients-json">{!! $clientsJson !!}</script>
        <script type="application/json" id="items-json">{!! $itemsJson !!}</script>
        <script type="application/json" id="categories-json">{!! $categoriesJson !!}</script>
        <script type="application/json" id="metal-rates-json">{!! $metalRatesJson !!}</script>
        <script type="application/json" id="doctors-json">{!! json_encode($doctors ?? []) !!}</script>
        <script type="application/json" id="departments-json">{!! json_encode($departments ?? []) !!}</script>
        <script type="application/json" id="wards-json">{!! json_encode($wards ?? []) !!}</script>
        <script type="application/json" id="rooms-json">{!! json_encode($rooms ?? []) !!}</script>
        <script type="application/json" id="beds-json">{!! json_encode($beds ?? []) !!}</script>

        <script type="application/json" id="allowed-fields-json">
            {!! json_encode($allowedFields ?? []) !!}
        </script>
        <div class="hospital-hero relative overflow-hidden rounded-2xl border border-cyan-200 bg-gradient-to-r from-cyan-700 via-teal-700 to-emerald-700 px-5 py-5 text-white shadow-lg dark:border-cyan-900">
            <div class="pointer-events-none absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute bottom-0 right-1/3 h-24 w-24 rounded-full bg-cyan-200/10 blur-xl"></div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/25">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3h6v4h4v6h-4v4H9v-4H5V7h4V3Z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl font-extrabold tracking-tight sm:text-2xl">Create Hospital Bill</h1>
                            <span class="rounded-full bg-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-[.18em] ring-1 ring-white/20">Patient Billing</span>
                        </div>
                        <p class="mt-1 max-w-2xl text-xs leading-5 text-cyan-50 sm:text-sm">
                            Patient encounter, doctor consultation, admission and hospital service charges ko ek hi bill me manage karein.
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2 text-[10px] font-semibold text-white/90">
                            <span class="rounded-full bg-emerald-400/20 px-3 py-1 ring-1 ring-white/15">OPD</span>
                            <span class="rounded-full bg-violet-400/20 px-3 py-1 ring-1 ring-white/15">IPD</span>
                            <span class="rounded-full bg-red-400/20 px-3 py-1 ring-1 ring-white/15">Emergency</span>
                            <span class="rounded-full bg-sky-400/20 px-3 py-1 ring-1 ring-white/15">Diagnostics</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="rounded-xl bg-black/10 px-4 py-2 text-right ring-1 ring-white/15">
                        <div class="text-[10px] uppercase tracking-wide text-cyan-100">Bill Number</div>
                        <div class="text-sm font-bold" x-text="invoiceNo || 'Auto'"></div>
                    </div>
                    <button type="button" @click="submitForm()" :disabled="saving"
                        class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-teal-800 shadow-md transition hover:bg-cyan-50 disabled:cursor-not-allowed disabled:opacity-60">
                        <svg x-show="saving" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span x-text="saving ? 'Saving Bill...' : 'Save Hospital Bill'"></span>
                    </button>
                </div>
            </div>
        </div>

        <form x-ref="form" method="POST" action="{{ route('invoices.store', $docType) }}"
            enctype="multipart/form-data" @submit.prevent="beforeSubmit">
            @csrf
            {{-- TOP PANELS --}}
            {{-- TOP PANELS - COMPACT --}}
            <div class="hospital-card patient-card relative z-40 overflow-visible rounded-2xl border border-cyan-200 bg-white p-4 shadow-sm dark:border-cyan-900/60 dark:bg-[#151b22]">

                <div class="grid lg:grid-cols-12 gap-3">

                    {{-- LEFT: Patient identity --}}
                    <div class="lg:col-span-7">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">
                                Patient Registration & Billing Identity
                            </div>
                        </div>

                        {{-- <label class="block text-xs font-medium text-[#9AA0AC] dark:text-[#9AA0AC] mb-1">
                            Party
                        </label> --}}

                        <div class="relative z-50 flex flex-wrap items-center gap-2 w-full overflow-visible">
                            <div class="relative flex-1 min-w-0"
                                @keydown.escape="clientDD.close()"
                                @keydown.arrow-down.prevent="clientDD.down()"
                                @keydown.arrow-up.prevent="clientDD.up()"
                                @keydown.enter.prevent="clientDD.enter()"
                                @click.outside="clientDD.close()">

                                <input type="text"
                                    x-model="clientDD.q"
                                    placeholder="Search patient by name, mobile or UHID"
                                    @focus="clientDD.open()"
                                    @input="clientDD.open()"
                                    class="w-full min-w-[420px] border rounded px-3 py-2 border-gray-300 dark:border-neutral-700
                                        bg-white dark:bg-[#242833] text-gray-900 dark:text-neutral-100 text-sm">

                                <input type="hidden" name="client_id" :value="clientId" required>

                                <div x-show="clientDD.isOpen" x-transition
                                    class="absolute z-50 mt-1 w-full rounded border border-gray-200 dark:border-neutral-700
                                        bg-white dark:bg-neutral-900 shadow-lg overflow-hidden">

                                    <div class="max-h-56 overflow-auto">
                                        <template x-if="clientDD.filtered().length === 0">
                                            <div class="px-3 py-2 text-sm text-gray-500 dark:text-neutral-400">
                                                No matching record
                                            </div>
                                        </template>

                                        <template x-for="(c, idx) in clientDD.filtered()" :key="c.id">
                                            <div @mouseenter="clientDD.hi = idx"
                                                @mousedown.prevent="clientDD.select(c)"
                                                class="px-3 py-2 cursor-pointer flex items-center justify-between gap-3 border-b border-gray-100 dark:border-neutral-800"
                                                :class="idx === clientDD.hi ? 'bg-gray-100 dark:bg-neutral-800' : ''">

                                                <div class="min-w-0">
                                                    <div class="text-sm text-gray-900 dark:text-[#9AA0AC] truncate"
                                                        x-text="c.mobile ? (c.name + ' (' + c.mobile + ')') : c.name">
                                                    </div>
                                                </div>

                                                <div class="text-sm text-gray-700 dark:text-[#9AA0AC] shrink-0"
                                                    x-text="Number(c.balance ?? 0).toFixed(1)">
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Client button with first-step tooltip --}}
                            <div class="client-guide-wrap relative flex shrink-0 items-center">

                                <div
                                    x-show="Array.isArray(clients) && clients.length === 0"
                                    x-transition.opacity.duration.250ms
                                    class="client-guide-tooltip"
                                    style="display: none;"
                                >
                                    <div class="font-bold leading-tight">
                                        First Step: Register Patient
                                    </div>

                                    <div class="mt-0.5 text-[9px] font-medium leading-tight text-white/85">
                                        Hospital bill banane se pehle patient register karein
                                    </div>

                                    <span class="client-guide-arrow" aria-hidden="true"></span>
                                </div>

                                <button
                                    type="button"
                                    @click="openClientModal()"
                                    :class="clients.length === 0
                                        ? 'client-guide-button bg-orange-600 hover:bg-orange-700'
                                        : 'bg-[#4C8DFF] hover:bg-[#6CA8FF]'"
                                    class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap
                                        rounded-lg px-4 py-2 text-sm font-semibold text-white
                                        transition"
                                >
                                    <span class="text-base leading-none">+</span>

                                    <span x-text="clients.length === 0 ? 'Add Patient' : 'New'"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Selected patient summary --}}
                        <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">

                            <div>
                                <div class="text-gray-500 dark:text-[#9AA0AC]">Patient Name</div>
                                <div class="font-semibold text-gray-800 dark:text-neutral-100 truncate"
                                    x-text="party.name || '-'"></div>
                            </div>

                            <div>
                                <div class="text-gray-500 dark:text-[#9AA0AC]">Mobile Number</div>
                                <div class="font-semibold text-gray-800 dark:text-neutral-100 truncate"
                                    x-text="party.mobile || '-'"></div>
                            </div>

                            <div>
                                <div class="text-gray-500 dark:text-[#9AA0AC]">State</div>
                                <div class="font-semibold text-gray-800 dark:text-neutral-100 truncate"
                                    x-text="party.state || '-'"></div>
                            </div>

                            <div>
                                <div class="text-gray-500 dark:text-[#9AA0AC]">State Code</div>
                                <div class="font-semibold text-gray-800 dark:text-neutral-100 truncate"
                                    x-text="party.state_code || '-'"></div>
                            </div>

                            <div>
                                <div class="text-gray-500 dark:text-[#9AA0AC]">Pin</div>
                                <div class="font-semibold text-gray-800 dark:text-neutral-100 truncate"
                                    x-text="party.pincode || '-'"></div>
                            </div>

                            <div>
                                <div class="text-gray-500 dark:text-[#9AA0AC]">UHID / GSTIN</div>
                                <div class="font-semibold text-gray-800 dark:text-neutral-100 truncate"
                                    x-text="party.gstin || 'Unregistered'"></div>
                            </div>

                            <div class="md:col-span-2">
                                <div class="text-gray-500 dark:text-[#9AA0AC]">Billing Tax Type</div>
                                <div class="font-semibold truncate"
                                    :class="isIntra() ? 'text-green-600' : 'text-purple-600'"
                                    x-text="isIntra() ? 'Intra State (CGST+SGST)' : 'Inter State (IGST)'">
                                </div>
                            </div>

                            <div class="col-span-2 md:col-span-4">
                                <div class="text-gray-500 dark:text-[#9AA0AC]">Address</div>
                                <div class="font-semibold text-gray-800 dark:text-neutral-100 truncate"
                                    x-text="party.address || '-'"></div>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT: Invoice Details --}}
                    <div class="lg:col-span-5 lg:border-l lg:pl-4 border-gray-200 dark:border-neutral-700">
                        <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100 mb-2">
                            Invoice Details
                        </div>

                        <div class="grid md:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-[#9AA0AC]">
                                    Date
                                </label>
                                <input type="date"
                                    name="invoice_date"
                                    x-model="hdr.date"
                                    required
                                    class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-gray-900 dark:text-neutral-100 text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-[#9AA0AC]">
                                    Bill No.
                                </label>
                                <input :value="invoiceNo"
                                    name="invoice_number"
                                    class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-gray-50 dark:bg-[#242833] text-gray-700 dark:text-[#9AA0AC] text-sm">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300">
                                    Invoice Prefix
                                </label>
                                <input :value="computedPrefix"
                                    readonly
                                    class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-gray-50 dark:bg-[#242833] text-gray-700 dark:text-[#9AA0AC] text-sm">

                                <input type="hidden" name="invoice_prefix" :value="computedPrefix">
                            </div>

                            @if(Str::contains(strtolower($businessName), 'krinoscco'))
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-[#9AA0AC]">
                                        Kitchen Order Ticket
                                    </label>
                                    <input type="text"
                                        name="kot"
                                        x-model="hdr.kot"
                                        class="w-full border rounded px-2 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-gray-900 dark:text-neutral-100 text-sm">
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>


            {{-- ================= HOSPITAL / CLINICAL DETAILS ================= --}}
            <div class="my-2 overflow-hidden rounded-lg border border-cyan-200 bg-white shadow-sm dark:border-cyan-900/60 dark:bg-[#1A1D23]">
                <div class="flex flex-wrap items-center justify-between gap-3 bg-gradient-to-r from-cyan-700 to-blue-700 px-4 py-3 text-white">
                    <div>
                        <h2 class="text-sm font-bold">Hospital / Clinical Information</h2>
                        <p class="mt-0.5 text-[10px] text-cyan-100">OPD, IPD, emergency, doctor, ward, insurance and diagnosis details.</p>
                    </div>
                    <span class="rounded-full bg-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-wide" x-text="hospital.visit_type.toUpperCase()"></span>
                </div>

                <div class="grid gap-3 p-3 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">UHID / Patient ID</label>
                        <input type="text" name="patient_uhid" x-model.trim="hospital.patient_uhid" placeholder="Auto or manual UHID"
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Visit Type <span class="text-red-500">*</span></label>
                        <select name="visit_type" x-model="hospital.visit_type" @change="onHospitalVisitTypeChange()" required
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                            <option value="opd">OPD</option>
                            <option value="ipd">IPD / Admission</option>
                            <option value="emergency">Emergency</option>
                            <option value="day_care">Day Care</option>
                            <option value="diagnostic">Diagnostic / Lab</option>
                            <option value="pharmacy">Pharmacy</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Visit / OPD / IPD No.</label>
                        <input type="text" name="visit_number" x-model.trim="hospital.visit_number" placeholder="e.g. OPD-2026-001"
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Visit Date & Time</label>
                        <input type="datetime-local" name="visit_at" x-model="hospital.visit_at"
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Age</label>
                        <input type="number" min="0" max="150" name="patient_age" x-model.number="hospital.patient_age" placeholder="Age"
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Gender</label>
                        <select name="patient_gender" x-model="hospital.patient_gender"
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                            <option value="">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Blood Group</label>
                        <select name="blood_group" x-model="hospital.blood_group"
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                            <option value="">Select</option>
                            <template x-for="group in ['A+','A-','B+','B-','AB+','AB-','O+','O-']" :key="group">
                                <option :value="group" x-text="group"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Guardian / Attendant</label>
                        <input type="text" name="guardian_name" x-model.trim="hospital.guardian_name" placeholder="Guardian name"
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Doctor</label>
                        <select name="doctor_id" x-model="hospital.doctor_id"
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                            <option value="">Select doctor</option>
                            <template x-for="doctor in doctors" :key="doctor.id">
                                <option :value="doctor.id" x-text="doctor.specialization ? doctor.name + ' - ' + doctor.specialization : doctor.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Department</label>
                        <select name="department_id" x-model="hospital.department_id"
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                            <option value="">Select department</option>
                            <template x-for="department in departments" :key="department.id">
                                <option :value="department.id" x-text="department.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Referred By</label>
                        <input type="text" name="referred_by" x-model.trim="hospital.referred_by" placeholder="Referring doctor / source"
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Billing Category</label>
                        <select name="billing_category" x-model="hospital.billing_category"
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                            <option value="cash">Cash</option>
                            <option value="credit">Credit</option>
                            <option value="insurance">Insurance / TPA</option>
                            <option value="corporate">Corporate</option>
                            <option value="government_scheme">Government Scheme</option>
                            <option value="charity">Charity / Concession</option>
                        </select>
                    </div>
                </div>

                <div x-show="['ipd','emergency','day_care'].includes(hospital.visit_type)" x-transition class="border-t border-cyan-100 bg-cyan-50/60 p-3 dark:border-cyan-900/50 dark:bg-cyan-950/10">
                    <div class="mb-2 text-xs font-bold uppercase tracking-wide text-cyan-800 dark:text-cyan-300">Admission / Bed Details</div>
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Admission At</label>
                            <input type="datetime-local" name="admitted_at" x-model="hospital.admitted_at"
                                class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Discharge At</label>
                            <input type="datetime-local" name="discharged_at" x-model="hospital.discharged_at"
                                class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Ward</label>
                            <select name="ward_id" x-model="hospital.ward_id" @change="hospital.room_id=''; hospital.bed_id=''"
                                class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                                <option value="">Select ward</option>
                                <template x-for="ward in wards" :key="ward.id"><option :value="ward.id" x-text="ward.name"></option></template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Room</label>
                            <select name="room_id" x-model="hospital.room_id" @change="hospital.bed_id=''"
                                class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                                <option value="">Select room</option>
                                <template x-for="room in filteredHospitalRooms()" :key="room.id"><option :value="room.id" x-text="room.room_number || room.name"></option></template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Bed</label>
                            <select name="bed_id" x-model="hospital.bed_id"
                                class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                                <option value="">Select bed</option>
                                <template x-for="bed in filteredHospitalBeds()" :key="bed.id"><option :value="bed.id" x-text="bed.bed_number || bed.name"></option></template>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 border-t border-gray-200 p-3 md:grid-cols-2 xl:grid-cols-4 dark:border-neutral-700">
                    <div x-show="['insurance','corporate','government_scheme'].includes(hospital.billing_category)">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Insurance / TPA / Company</label>
                        <input type="text" name="insurance_provider" x-model.trim="hospital.insurance_provider" placeholder="Provider / TPA / company"
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                    </div>
                    <div x-show="['insurance','corporate','government_scheme'].includes(hospital.billing_category)">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Policy / Claim / Employee No.</label>
                        <input type="text" name="insurance_policy_number" x-model.trim="hospital.insurance_policy_number" placeholder="Policy or claim number"
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Chief Complaint / Reason for Visit</label>
                        <textarea name="chief_complaint" x-model.trim="hospital.chief_complaint" rows="2" placeholder="Patient complaint / reason for visit"
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Diagnosis</label>
                        <textarea name="diagnosis" x-model.trim="hospital.diagnosis" rows="2" placeholder="Provisional / final diagnosis"
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300">Clinical / Billing Notes</label>
                        <textarea name="notes" x-model.trim="hospital.notes" rows="2" placeholder="Clinical notes, package details or billing remarks"
                            class="mt-1 w-full rounded border border-gray-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-[#242833] dark:text-white"></textarea>
                    </div>
                </div>
            </div>

            {{-- ================= TABLE ================= --}}
            {{-- ================= ITEMS: HORIZONTAL TABULAR LAYOUT ================= --}}
            <div class="my-2 overflow-visible rounded-lg border border-gray-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-[#1A1D23]">
                <div class="relative z-50 flex flex-wrap items-center justify-between gap-3 overflow-visible bg-gradient-to-r from-cyan-700 to-teal-700 px-4 py-3 text-white dark:from-cyan-900 dark:to-teal-900">
                    <div>
                        <h2 class="text-sm font-extrabold text-white">Hospital Services / Charges</h2>
                        <p class="mt-0.5 text-[11px] text-cyan-50">
                            Consultation, tests, procedures, medicines, room, nursing aur package charges add karein.
                        </p>
                    </div>

                    {{-- Normal Add Charge button: no blink and no tooltip --}}
                    <button
                        type="button"
                        @click="add()"
                        class="inline-flex items-center gap-2 rounded-md bg-green-600
                            px-3 py-1.5 text-sm font-semibold text-white
                            transition hover:bg-green-700"
                    >
                        <span class="text-base leading-none">+</span>
                        Add Charge
                    </button>
                </div>

                <div
                    class="flex items-center justify-between border-t border-gray-200
                        bg-blue-50 px-3 py-2 text-[10px] font-medium text-blue-700
                        sm:hidden dark:border-neutral-700 dark:bg-blue-950/30
                        dark:text-blue-300"
                >
                    <span>Qty/Days, Rate, Tax aur Amount dekhne ke liye table ko swipe karein</span>
                    <span class="ml-2 shrink-0 whitespace-nowrap text-sm">← Swipe →</span>
                </div>

                <div class="invoice-items-scroll bg-[#F3F4F6] dark:bg-[#1A1D23]">
                    <table class="invoice-table min-w-max w-full border-collapse text-xs">
                        <thead class="sticky top-0 z-30 bg-gray-100 text-left text-[11px] font-bold uppercase tracking-wide text-gray-600 dark:bg-[#242833] dark:text-neutral-300">
                            <tr>
                                <th class="sticky left-0 z-40 w-12 min-w-[48px] border-b border-r border-gray-300 bg-gray-100 text-center dark:border-neutral-700 dark:bg-[#242833]">#</th>
                                <th class="sticky left-[48px] z-40 w-[330px] min-w-[330px] border-b border-r border-gray-300 bg-gray-100 dark:border-neutral-700 dark:bg-[#242833]">Service / Particulars</th>
                                <th class="w-[105px] min-w-[105px] border-b border-r border-gray-300 dark:border-neutral-700">Service Code / SAC</th>
                                <th class="w-[75px] min-w-[75px] border-b border-r border-gray-300 text-center dark:border-neutral-700">Qty</th>
                                <th class="w-[115px] min-w-[115px] border-b border-r border-gray-300 text-right dark:border-neutral-700">Unit Rate</th>

                                <th x-show="false"
                                    class="w-[105px] min-w-[105px] border-b border-r border-gray-300 dark:border-neutral-700">Making Rate</th>
                                <th x-show="false"
                                    class="w-[140px] min-w-[140px] border-b border-r border-gray-300 dark:border-neutral-700">Making Type</th>
                                <th x-show="false"
                                    class="w-[115px] min-w-[115px] border-b border-r border-gray-300 dark:border-neutral-700">Gold Rate</th>
                                <th x-show="false"
                                    class="w-[105px] min-w-[105px] border-b border-r border-gray-300 dark:border-neutral-700">Gold Wt.</th>
                                <th x-show="false"
                                    class="w-[115px] min-w-[115px] border-b border-r border-gray-300 dark:border-neutral-700">Silver Rate</th>
                                <th x-show="false"
                                    class="w-[105px] min-w-[105px] border-b border-r border-gray-300 dark:border-neutral-700">Silver Wt.</th>
                                <th x-show="false"
                                    class="w-[110px] min-w-[110px] border-b border-r border-gray-300 dark:border-neutral-700">Stone Wt.</th>
                                <th x-show="false"
                                    class="w-[125px] min-w-[125px] border-b border-r border-gray-300 dark:border-neutral-700">Stone Charge</th>
                                <th x-show="false"
                                    class="w-[115px] min-w-[115px] border-b border-r border-gray-300 dark:border-neutral-700">Diamond Wt.</th>
                                <th x-show="false"
                                    class="w-[135px] min-w-[135px] border-b border-r border-gray-300 dark:border-neutral-700">Diamond Charge</th>

                                <th class="w-[85px] min-w-[85px] border-b border-r border-gray-300 text-center dark:border-neutral-700">Tax %</th>
                                <th class="w-[135px] min-w-[135px] border-b border-r border-gray-300 text-right dark:border-neutral-700">Amount</th>
                                <th class="sticky right-0 z-40 w-[60px] min-w-[60px] border-b border-l border-gray-300 bg-gray-100 text-center dark:border-neutral-700 dark:bg-[#242833]">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-neutral-700 dark:bg-[#1A1D23]">
                            <template x-for="(row, i) in items" :key="row._k">
                                <tr class="group align-top hover:bg-blue-50/50 dark:hover:bg-[#20242D]">
                                    <td class="sticky left-0 z-20 border-r border-gray-200 bg-white text-center font-bold text-gray-500 group-hover:bg-blue-50 dark:border-neutral-700 dark:bg-[#1A1D23] dark:text-neutral-400 dark:group-hover:bg-[#20242D]">
                                        <span x-text="i + 1"></span>
                                    </td>

                                    <td class="sticky left-[48px] z-20 border-r border-gray-200 bg-white group-hover:bg-blue-50 dark:border-neutral-700 dark:bg-[#1A1D23] dark:group-hover:bg-[#20242D]">
                                        <div class="relative" @click.away="closeItemDD(i)">
                                            <div class="flex items-center gap-1.5">
                                                <input type="text"
                                                    :id="'item_search_' + i"
                                                    x-model="row.search"
                                                    placeholder="Search service, test, medicine or code"
                                                    autocomplete="off"
                                                    @focus="openItemDD(i)"
                                                    @input.debounce.50ms="openItemDD(i)"
                                                    @keydown.escape.prevent="closeItemDD(i)"
                                                    @keydown.arrow-down.prevent="itemDDDown(i)"
                                                    @keydown.arrow-up.prevent="itemDDUp(i)"
                                                    @keydown.enter.prevent="itemDDEnter(i)"
                                                    class="min-w-0 flex-1 rounded-md border border-gray-300 bg-white px-2 py-1 text-xs text-gray-900 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-200 dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100">

                                                {{-- Create first hospital service guide --}}
                                                <div class="new-item-guide-wrap relative shrink-0">
                                                    <div
                                                        x-show="
                                                            i === 0 &&
                                                            clients.length > 0 &&
                                                            itemCatalogCount === 0
                                                        "
                                                        x-transition.opacity.duration.250ms
                                                        class="new-item-guide-tooltip"
                                                        style="display: none;"
                                                    >
                                                        <div class="font-bold leading-tight">
                                                            Next Step: Create Service
                                                        </div>

                                                        <div class="mt-0.5 text-[9px] font-medium leading-tight text-white/85">
                                                            Pehle consultation, test, medicine ya hospital service create karein
                                                        </div>

                                                        <span
                                                            class="new-item-guide-arrow"
                                                            aria-hidden="true"
                                                        ></span>
                                                    </div>

                                                    <button
                                                        type="button"
                                                        @click="openItemModal(i)"
                                                        title="Create new hospital service"
                                                        :class="
                                                            i === 0 &&
                                                            clients.length > 0 &&
                                                            itemCatalogCount === 0
                                                                ? 'new-item-guide-button'
                                                                : ''
                                                        "
                                                        class="h-[28px] shrink-0 rounded-md bg-sky-600
                                                            px-2.5 text-xs font-bold text-white
                                                            transition hover:bg-sky-700"
                                                    >
                                                        + Service
                                                    </button>
                                                </div>
                                            </div>

                                            <input type="hidden" :name="'items[' + i + '][item_id]'" :value="row.item_id">

                                            <div x-show="row.ddOpen"
                                                x-transition.opacity
                                                class="fixed z-[999999] mt-1 max-h-72 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-2xl dark:border-neutral-700 dark:bg-[#242833]"
                                                :style="row.ddStyle + ';width:900px;max-width:95vw;'"
                                                style="display:none;"
                                                @mousedown.prevent>

                                                <div class="grid h-64 grid-cols-12">
                                                    <div class="col-span-7 overflow-auto border-r border-gray-200 dark:border-neutral-700">
                                                        <template x-if="filteredItems(row.search).length === 0">
                                                            <div class="px-3 py-3 text-xs text-gray-500 dark:text-neutral-400">No matching record</div>
                                                        </template>

                                                        <template x-for="(it, idx) in filteredItems(row.search).slice(0, 80)" :key="it.id">
                                                            <button type="button"
                                                                class="flex w-full items-start justify-between gap-3 border-b border-gray-100 px-3 py-2 text-left text-xs hover:bg-gray-100 dark:border-neutral-800 dark:hover:bg-neutral-800"
                                                                :class="idx === row.ddHi ? 'bg-gray-100 dark:bg-neutral-800' : ''"
                                                                @mouseenter="
                                                                    row.ddHi = idx;
                                                                    row.ddPreviewName = it.sku ? (it.name + ' (' + it.sku + ')') : it.name;
                                                                    row.ddPreview = it.description || it.desc || it.long_description || '';
                                                                "
                                                                @click="selectItemFromDD(i, it)">
                                                                <span class="flex-1 whitespace-normal break-words leading-4 text-gray-900 dark:text-neutral-100"
                                                                    x-text="it.sku ? (it.name + ' (' + it.sku + ')') : it.name"></span>
                                                                <span class="w-[95px] shrink-0 text-right text-[11px] text-gray-500 dark:text-neutral-400"
                                                                    x-text="it.price ? ('₹ ' + Number(it.price).toFixed(2)) : ''"></span>
                                                            </button>
                                                        </template>
                                                    </div>

                                                    <div class="col-span-5 overflow-auto p-3">
                                                        <div class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Preview</div>
                                                        <div class="mb-2 text-sm font-semibold text-gray-900 dark:text-neutral-100"
                                                            x-text="row.ddPreviewName || 'Hover on an item'"></div>
                                                        <div class="whitespace-pre-line text-xs leading-5 text-gray-700 dark:text-neutral-200"
                                                            x-text="row.ddPreview || 'No description available'"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <textarea x-model="row.description"
                                            rows="2"
                                            placeholder="Description"
                                            class="mt-1.5 min-h-[48px] w-full resize-y rounded-md border border-gray-300 bg-white px-2 py-1.5 text-xs leading-4 text-gray-900 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-200 dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100"></textarea>
                                    </td>

                                    <td class="border-r border-gray-200 dark:border-neutral-700">
                                        <input x-model="row.hsn" placeholder="HSN/SAC"
                                            class="w-full rounded-md border border-gray-300 bg-white text-xs dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100">
                                    </td>

                                    <td class="border-r border-gray-200 dark:border-neutral-700">
                                        <input type="number" min="1" step="1"
                                            x-model.number="row.quantity"
                                            @input="onAutoChange(row)"
                                            class="w-full rounded-md border border-gray-300 bg-white text-center text-xs dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100">
                                    </td>

                                    <td class="border-r border-gray-200 dark:border-neutral-700">
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.fixed_price"
                                            @input="row.service_rate = Number(row.fixed_price || 0); onAutoChange(row);"
                                            class="w-full rounded-md border border-gray-300 bg-white text-right text-xs dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100">
                                    </td>

                                    <td x-show="false" class="border-r border-gray-200 dark:border-neutral-700">
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.making_rate"
                                            @input="onAutoChange(row)"
                                            class="w-full rounded-md border border-gray-300 bg-white text-right text-xs dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100">
                                    </td>

                                    <td x-show="false" class="border-r border-gray-200 dark:border-neutral-700">
                                        <select x-model="row.making_charge_type"
                                            @change="onAutoChange(row)"
                                            class="w-full rounded-md border border-gray-300 bg-white text-xs dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100">
                                            <option value="percentage">Percent (%)</option>
                                            <option value="fixed">Fixed Amount (₹)</option>
                                            <option value="per_gram">Per Gram</option>
                                            <option value="per_product">Whole Product</option>
                                        </select>
                                        <div class="mt-1 whitespace-nowrap text-[10px] text-blue-600" x-text="makingTypeLabel(row)"></div>
                                    </td>

                                    <td x-show="false" class="border-r border-gray-200 dark:border-neutral-700">
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.gold_rate"
                                            @input="onAutoChange(row)"
                                            class="w-full rounded-md border border-gray-300 bg-white text-right text-xs dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100">
                                        <div x-show="hasValue(row.gold_purity)" class="mt-1 whitespace-nowrap text-[10px] text-gray-500 dark:text-neutral-400"
                                            x-text="'Purity: ' + row.gold_purity"></div>
                                    </td>

                                    <td x-show="false" class="border-r border-gray-200 dark:border-neutral-700">
                                        <input type="number" step="0.001" min="0"
                                            x-model.number="row.gold_wt"
                                            @input="onAutoChange(row)"
                                            class="w-full rounded-md border border-gray-300 bg-white text-right text-xs dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100">
                                    </td>

                                    <td x-show="false" class="border-r border-gray-200 dark:border-neutral-700">
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.silver_rate"
                                            @input="onAutoChange(row)"
                                            class="w-full rounded-md border border-gray-300 bg-white text-right text-xs dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100">
                                        <div x-show="hasValue(row.silver_purity)" class="mt-1 whitespace-nowrap text-[10px] text-gray-500 dark:text-neutral-400"
                                            x-text="'Purity: ' + row.silver_purity"></div>
                                    </td>

                                    <td x-show="false" class="border-r border-gray-200 dark:border-neutral-700">
                                        <input type="number" step="0.001" min="0"
                                            x-model.number="row.silver_wt"
                                            @input="onAutoChange(row)"
                                            class="w-full rounded-md border border-gray-300 bg-white text-right text-xs dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100">
                                    </td>

                                    <td x-show="false" class="border-r border-gray-200 dark:border-neutral-700">
                                        <input type="number" step="0.001" min="0"
                                            x-model.number="row.gemstone_wt"
                                            @input="onAutoChange(row)"
                                            class="w-full rounded-md border border-gray-300 bg-white text-right text-xs dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100">
                                    </td>

                                    <td x-show="false" class="border-r border-gray-200 dark:border-neutral-700">
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.gemstone_charge"
                                            @input="onAutoChange(row)"
                                            class="w-full rounded-md border border-gray-300 bg-white text-right text-xs dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100">
                                    </td>

                                    <td x-show="false" class="border-r border-gray-200 dark:border-neutral-700">
                                        <input type="number" step="0.001" min="0"
                                            x-model.number="row.diamond_wt"
                                            @input="onAutoChange(row)"
                                            class="w-full rounded-md border border-gray-300 bg-white text-right text-xs dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100">
                                    </td>

                                    <td x-show="false" class="border-r border-gray-200 dark:border-neutral-700">
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.diamond_charge"
                                            @input="onAutoChange(row)"
                                            class="w-full rounded-md border border-gray-300 bg-white text-right text-xs dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100">
                                    </td>

                                    <td class="border-r border-gray-200 dark:border-neutral-700">
                                        <input type="number" step="0.01" min="0" max="100"
                                            x-model.number="row.tax_percent"
                                            @input="onAutoChange(row)"
                                            class="w-full rounded-md border border-gray-300 bg-white text-center text-xs dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100">
                                    </td>

                                    <td class="border-r border-gray-200 dark:border-neutral-700">
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="row.manual_amount"
                                            @input="onAmountEdit(row)"
                                            class="w-full rounded-md border border-gray-300 bg-white text-right text-xs font-semibold dark:border-neutral-700 dark:bg-[#242833] dark:text-neutral-100">
                                        <div class="mt-1 whitespace-nowrap text-right text-[10px]"
                                            :class="row.amount_mode === 'manual' ? 'text-orange-600' : 'text-gray-500 dark:text-neutral-400'"
                                            x-text="row.amount_mode === 'manual' ? 'Manual amount' : ('Auto: ₹ ' + lineAmount(row).toFixed(2))"></div>
                                    </td>

                                    <td class="sticky right-0 z-20 border-l border-gray-200 bg-white text-center group-hover:bg-blue-50 dark:border-neutral-700 dark:bg-[#1A1D23] dark:group-hover:bg-[#20242D]">
                                        <button type="button"
                                            @click="remove(i)"
                                            title="Remove item"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-lg font-bold leading-none text-red-600 hover:bg-red-100 dark:border-red-900/50 dark:bg-red-950/30">
                                            ×
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <tr x-show="items.length === 0">
                                <td colspan="18" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-neutral-400">
                                    Abhi koi item add nahi hai. “Add Charge” par click karein.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-200 bg-white px-4 py-3 dark:border-neutral-700 dark:bg-[#1A1D23]">
                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                        Total rows: <span class="font-bold text-gray-800 dark:text-neutral-100" x-text="items.length"></span>
                    </div>

                    <button type="button"
                        @click="add()"
                        class="inline-flex items-center gap-2 rounded-lg border border-green-600 bg-white px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-50 dark:bg-[#242833] dark:text-green-400">
                        + Add Another Item
                    </button>
                </div>
            </div>

            {{-- ================= BOTTOM ================= --}}
            <div class="grid lg:grid-cols-2 gap-4">
                {{-- Left --}}
                <div
                    class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-[#1A1D23] space-y-3">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="hdr.reverse_charge"
                            class="rounded border-gray-300 dark:border-neutral-700">
                        <span class="text-sm text-gray-800 dark:text-neutral-100">Reverse Charge (Y/N)</span>
                    </div>
                    <input class="bg-[#242833]" type="hidden" name="reverse_charge"
                        :value="hdr.reverse_charge ? 1 : 0">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Terms and
                            Conditions</label>
                            <textarea name="terms" rows="4" x-model="hdr.terms"
                                class="w-full border rounded px-3 py-2 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-gray-900 dark:text-neutral-100 text-sm"></textarea>
                    </div>

                    {{-- Old payment split fields (backend compatible) --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Cash</span>
                            <input type="number" step="0.01" min="0" name="pay_cash"
                                x-model.number="pay.cash"
                                class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">UPI / Online</span>
                            <input type="number" step="0.01" min="0" name="pay_upi"
                                x-model.number="pay.upi"
                                class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Card</span>
                            <input type="number" step="0.01" min="0" name="pay_card"
                                x-model.number="pay.card"
                                class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Cheque</span>
                            <input type="number" step="0.01" min="0" name="pay_cheque"
                                x-model.number="pay.cheque"
                                class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm text-right">
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Credit Sales/Excess Amt.</span>
                            <input type="number" step="0.01" min="0" name="credit_sales_excess"
                                x-model.number="pay.credit_excess"
                                class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm text-right">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-600 dark:text-neutral-300">Advance</span>
                            <input type="number" step="0.01" min="0" name="advance_amount"
                                x-model.number="pay.advance"
                                class="w-56 border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm text-right">
                        </div>

                        <div class="grid md:grid-cols-2 gap-2 pt-2">
                            <input type="text" name="online_mode" x-model="pay.online_mode"
                                placeholder="online mode (upi/bank/neft...)"
                                class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm">

                            <input type="text" name="online_ref" x-model="pay.online_ref"
                                placeholder="UTR/Txn Ref"
                                class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm">

                            <input type="text" name="upi_id" x-model="pay.upi_id" placeholder="UPI ID"
                                class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm">

                            <input type="text" name="card_last4" x-model="pay.card_last4"
                                placeholder="Card last4"
                                class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm">

                            <input type="text" name="card_ref" x-model="pay.card_ref" placeholder="Card Ref"
                                class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm">

                            <input type="text" name="cheque_no" x-model="pay.cheque_no" placeholder="Cheque No"
                                class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm">

                            <input type="text" name="bank_name" x-model="pay.bank_name" placeholder="Bank Name"
                                class="w-full border rounded px-2 py-1 border-gray-300 dark:border-neutral-700 bg-white dark:bg-[#242833] text-sm">
                        </div>
                    </div>
                </div>

                {{-- Right totals --}}
                <div
                    class="p-4 border rounded border-gray-200 dark:border-neutral-700 bg-white dark:bg-[#1A1D23] space-y-3">

                    {{-- Additional Charges --}}
                    <div class="flex items-center justify-between">
                        <button type="button" class="text-sm  bg-amber-600 p-2 rounded-lg text-white hover:underline"
                            @click="ui.showCharges = !ui.showCharges">
                            + Add Additional Charges
                        </button>
                        <div class="text-sm font-semibold" x-text="money(chargesTotal())"></div>
                    </div>

                    <div x-show="ui.showCharges" class="space-y-2" style="display:none;">
                        <template x-for="(c, idx) in charges" :key="c._k">
                            <div class="grid grid-cols-12 gap-2">
                                <input
                                    class="col-span-7 border rounded-lg px-2 py-1 text-sm dark:bg-[#242833]dark:border-neutral-700"
                                    placeholder="Charge name (Packing/Transport...)" x-model="c.name">
                                <input type="number" step="0.01" min="0"
                                    class="col-span-4 border rounded-lg px-2 py-1 text-sm text-right dark:bg-[#242833] dark:border-neutral-700"
                                    x-model.number="c.amount" @input="calc()">
                                <button type="button" class="col-span-1 text-red-600 text-lg leading-none"
                                    @click="removeCharge(idx)">×</button>
                            </div>
                        </template>

                        <button type="button" class="text-xs px-2 py-1 rounded border dark:border-neutral-700"
                            @click="addCharge()">+ Add</button>
                    </div>

                    {{-- Taxable + Tax --}}
                    <div class="flex justify-between pt-2">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">Taxable Amount</span>
                        <span class="text-sm font-medium" x-text="money(taxableAmount())"></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">SGST</span>
                        <span class="text-sm" x-text="money(sgst())"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">CGST</span>
                        <span class="text-sm" x-text="money(cgst())"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-300">IGST</span>
                        <span class="text-sm" x-text="money(igst())"></span>
                    </div>

                    {{-- Discount --}}
                    <div class="flex items-center justify-between pt-1">
                        <button type="button"
                            class="bg-emerald-600 p-2 rounded-lg text-sm text-white hover:underline"
                            @click="ui.showDiscount = !ui.showDiscount">
                            + Add Discount
                        </button>
                        <div class="text-sm font-semibold text-red-600" x-text="'- ' + money(discountAmount())"></div>
                    </div>

                    <div x-show="ui.showDiscount" class="grid grid-cols-12 gap-2" style="display:none;">
                        <select
                            class="col-span-4 border rounded-lg px-2 py-1 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                            x-model="discount.type" @change="calc()">
                            <option value="flat">Flat ₹</option>
                            <option value="percent">%</option>
                        </select>

                        <input type="number" step="0.01" min="0"
                            class="col-span-8 border rounded-lg px-2 py-1 text-sm text-right dark:bg-[#242833] dark:border-neutral-700"
                            x-model.number="discount.value" @input="calc()" placeholder="Discount value">
                    </div>

                    {{-- Apply TCS --}}
                    <label class="flex items-center gap-2 pt-1 text-sm text-gray-700 dark:text-neutral-200">
                        <input type="checkbox" class="rounded" x-model="tcs.apply" @change="calc()">
                        Apply TCS
                        <span class="ml-auto font-semibold" x-text="money(tcsAmount())"></span>
                    </label>

                    <div x-show="tcs.apply" class="flex items-center justify-between" style="display:none;">
                        <span class="text-xs text-gray-500 dark:text-neutral-400">TCS %</span>
                        <input type="number" step="0.01" min="0"
                            class="w-28 border rounded-lg px-2 py-1 text-sm text-right dark:bg-neutral-900 dark:border-neutral-700"
                            x-model.number="tcs.percent" @input="calc()">
                    </div>

                    <hr class="border-gray-200 dark:border-neutral-700">

                    {{-- Auto Round Off --}}
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-neutral-200">
                        <input type="checkbox" class="rounded" x-model="roundOff.enabled" @change="calc()">
                        Auto Round Off
                        <span class="ml-auto text-xs text-gray-500 dark:text-neutral-400"
                            x-text="roundOff.enabled ? ('Adj: ' + money(roundOffAmount())) : ''"></span>
                    </label>

                    {{-- Total Amount --}}
                    <div class="flex items-center justify-between pt-1">
                        <div class="text-base font-semibold text-gray-900 dark:text-neutral-100">Total Amount</div>
                        <div class="text-xl font-bold" x-text="money(totalPayable())"></div>
                    </div>

                    <hr class="border-gray-200 dark:border-neutral-700">

                    {{-- Mark fully paid --}}
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-neutral-200 justify-end">
                        Mark as fully paid
                        <input class="bg-[#242833]" type="checkbox" class="rounded" x-model="payment.markFullyPaid"
                            @change="toggleFullyPaid()">
                    </label>

                    {{-- Amount Received + Mode --}}
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm text-gray-600 dark:text-neutral-300">Amount Received</div>

                        <div class="flex items-center gap-2 w-[320px] max-w-full">
                            <div
                                class="flex-1 flex items-center border rounded-lg overflow-hidden dark:border-neutral-700">
                                <span class="px-3 text-gray-500 dark:text-neutral-400">₹</span>
                                <input type="number" step="0.01" min="0"
                                    class="w-full px-2 py-2 text-sm bg-[#F3F4F6] dark:bg-[#242833] text-right outline-none dark:text-neutral-100"
                                    x-model.number="payment.received" @input="onReceivedInput()">
                            </div>

                            <select
                                class="w-36 border rounded-lg px-2 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                x-model="payment.mode" @change="onReceivedInput()">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="card">Card</option>
                                <option value="cheque">Cheque</option>
                                <option value="bank">Bank</option>
                            </select>
                        </div>
                    </div>

                    <div x-show="payment.mode !== 'cash'" style="display:none;"
                        class="flex items-center justify-between gap-3">
                        <div class="text-sm text-gray-600 dark:text-neutral-300">
                            Select Bank
                        </div>

                        <div class="w-[320px] max-w-full">
                            <select
                                class="w-full border rounded-lg px-2 py-2 text-sm dark:bg-[#242833] dark:border-neutral-700"
                                x-model="payment.bank_account_id">
                                <option value="">-- Select Bank Account --</option>
                                <template x-for="b in banks" :key="b.id">
                                    <option :value="b.id"
                                        x-text="(b.bank_name || 'Bank') + ' - ' + (b.account_no ? ('****' + String(b.account_no).slice(-4)) : '')">
                                    </option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="bank_account_id"
                        :value="payment.mode !== 'cash' ? payment.bank_account_id : ''">

                    {{-- Balance Amount --}}
                    <div class="flex justify-between pt-1">
                        <span class="text-sm font-semibold text-green-600">Balance Amount</span>
                        <span class="text-sm font-bold text-green-600" x-text="money(balanceAmount())"></span>
                    </div>

                    {{-- ================= SIGNATURE UPLOAD ================= --}}
                   @php
                        $businessSignature = $businessSignature ?? null;

                        $businessSignatureUrl = $businessSignature
                            ? (\Illuminate\Support\Str::startsWith($businessSignature, ['http://', 'https://'])
                                ? $businessSignature
                                : asset('storage/' . $businessSignature))
                            : null;
                    @endphp

                    <div class="pt-2">
                        <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 mb-2">
                            Signature
                        </div>

                        @if($businessSignatureUrl)
                            <div class="mb-3 p-3 rounded-xl border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-[#242833]">
                                <div class="text-xs font-semibold text-gray-600 dark:text-neutral-300 mb-2">
                                    Current Business Signature
                                </div>

                                <img src="{{ $businessSignatureUrl }}"
                                    alt="Business Signature"
                                    class="h-20 max-w-xs object-contain bg-white rounded-lg border border-gray-200 p-2">

                                <label class="mt-3 inline-flex items-center gap-2 text-sm text-red-600">
                                    <input type="checkbox" name="remove_signature" value="1"
                                        class="rounded border-gray-300 dark:border-neutral-700">
                                    Remove this signature
                                </label>
                            </div>
                        @endif

                        <div class="grid gap-2">
                            <input type="file" name="signature" accept="image/*"
                                class="w-full text-sm file:mr-3 file:px-3 file:py-2 file:rounded-lg
                                    file:border-0 file:bg-gray-100 file:text-gray-700
                                    dark:file:bg-neutral-800 dark:file:text-neutral-200
                                    border border-gray-300 dark:border-neutral-700
                                    rounded-lg px-3 py-2 bg-white dark:bg-neutral-900
                                    text-gray-900 dark:text-neutral-100">

                            <div class="text-[11px] text-gray-500 dark:text-neutral-400">
                                Allowed: JPG/PNG/WebP • Max 2MB. New upload karoge to old signature replace ho jayega.
                            </div>
                        </div>
                    </div>

                    {{-- ✅ Hidden inputs (ONLY ONCE) --}}
                    <input type="hidden" name="charges_json" :value="JSON.stringify(chargesPayload())">
                    <input type="hidden" name="discount_total" :value="discountAmount()">
                    <input type="hidden" name="charge_total" :value="chargesTotal()">
                    <input type="hidden" name="tcs_percent" :value="tcs.apply ? tcs.percent : 0">
                    <input type="hidden" name="tcs_amount" :value="tcsAmount()">
                    <input type="hidden" name="round_off" :value="roundOffAmount()">
                    <input type="hidden" name="less_amount" :value="discountAmount()">
                    <input type="hidden" name="cgst_percent" :value="isIntra() ? (avgTaxPercent() / 2) : 0">
                    <input type="hidden" name="sgst_percent" :value="isIntra() ? (avgTaxPercent() / 2) : 0">
                    <input type="hidden" name="igst_percent" :value="isIntra() ? 0 : avgTaxPercent()">
                    <input type="hidden" name="payment_method" :value="payment.mode">
                </div>
            </div>

            {{-- Hidden JSON --}}
            <input type="hidden" id="items_json" name="items_json">

            <div class="text-left">
                <button type="button" @click="submitForm()" :disabled="saving"
                    class="px-4 py-2 rounded bg-green-800 text-white hover:bg-green-900 disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center gap-2">
                    <svg x-show="saving" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span x-text="saving ? 'Saving...' : 'Save'"></span>
                </button>
            </div>

            <br>
            <br>
            <br>
            <br>
        

            {{-- =================== CLIENT MODAL =================== --}}
            <div x-show="modals.client" x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center px-3" style="display:none;">
                <div class="absolute inset-0 bg-black/50" @click="closeClientModal()"></div>

                <div
                    class="relative w-full max-w-2xl bg-white dark:bg-neutral-900 rounded-2xl shadow-xl border border-gray-200 dark:border-neutral-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">Add New Client</h3>
                        <button type="button" class="text-2xl leading-none text-gray-500 hover:text-red-600"
                            @click="closeClientModal()">×</button>
                    </div>

                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Name *</label>
                            <input x-model="newClient.name"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                placeholder="Client name">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Mobile </label>
                            <input x-model="newClient.mobile"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                placeholder="10 digit mobile">
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">
                                State (GST Code)
                            </label>

                            <select
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm
                                       bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100"
                                x-model="newClient.state_pick" @change="applyClientState()">
                                <option value="">-- Select State --</option>

                                <template x-for="st in states" :key="st.code">
                                    <option :value="st.code + ',' + st.name" x-text="st.name + ' (' + st.code + ')'">
                                    </option>
                                </template>
                            </select>

                            <div class="mt-1 grid grid-cols-2 gap-2 text-[11px] text-gray-500 dark:text-neutral-400">
                                <div>
                                    State: <span class="font-semibold" x-text="newClient.state || '-'"></span>
                                </div>
                                <div>
                                    Code: <span class="font-semibold" x-text="newClient.state_code || '-'"></span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Pin </label>
                            <input x-model="newClient.pincode"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                placeholder="6 digit pin">
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Address</label>
                            <input x-model="newClient.address"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                placeholder="Full address">
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">GSTIN</label>

                            <input x-model="newClient.gstin" @input.debounce.350ms="onGstinInput('client')"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                placeholder="optional">

                            <div class="mt-1 text-[11px]" x-show="clientGstin.status !== 'idle'"
                                :class="clientGstin.status === 'valid' ? 'text-green-600' :
                                    (clientGstin.status==='checking' ?
                                    'text-blue-600' : 'text-red-600')"
                                x-text="clientGstin.message">
                            </div>
                        </div>

                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <div class="text-sm text-red-600" x-text="newClientError"></div>

                            <label
                                class="mt-2 inline-flex items-center gap-2 text-sm text-gray-700 dark:text-neutral-200 select-none">
                                <input type="checkbox" class="rounded border-gray-300 dark:border-neutral-700"
                                    x-model="clientAutoSelect">
                                Save for future
                            </label>
                        </div>

                        <div class="flex gap-2 shrink-0">
                            <button type="button" class="px-4 py-2 rounded-xl border dark:border-neutral-700"
                                @click="closeClientModal()">
                                Cancel
                            </button>

                            <button type="button"
                                class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
                                :disabled="savingClient" @click="saveClient()">
                                <span x-show="!savingClient">Add Client</span>
                                <span x-show="savingClient">Saving...</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            {{-- =================== ITEM MODAL =================== --}}
            <div x-show="modals.item" x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center px-3" style="display:none;">
                <div class="absolute inset-0 bg-black/50" @click="closeItemModal()"></div>

                <div
                    class="relative w-full max-w-3xl bg-white dark:bg-neutral-900 rounded-2xl shadow-xl border border-gray-200 dark:border-neutral-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">Create New Item</h3>
                        <button type="button" class="text-2xl leading-none text-gray-500 hover:text-red-600"
                            @click="closeItemModal()">×</button>
                    </div>

                    <div class="grid md:grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Type *</label>
                            <select x-model="newItem.type"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                <option value="product">Product</option>
                                <option value="service">Service</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Category</label>
                            <select x-model="newItem.category_id"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                <option value="">-- Select Category --</option>
                                <template x-for="cat in categories" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Name *</label>
                            <input x-model="newItem.name"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                placeholder="Item name">
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">SKU</label>
                            <input x-model="newItem.sku"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700"
                                placeholder="optional">
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Tax %</label>
                            <input type="number" step="0.01" min="0" max="100"
                                x-model.number="newItem.tax_rate"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                        </div>

                        <div x-show="newItem.type==='product'">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">HSN</label>
                            <input x-model="newItem.hsn"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                        </div>

                        <div x-show="newItem.type==='service'">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">SAC</label>
                            <input x-model="newItem.sac"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                        </div>

                        <div class="md:col-span-3">
                            <label
                                class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Description</label>
                            <input x-model="newItem.description"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                        </div>

                        {{-- SERVICE fields --}}
                        <div x-show="newItem.type==='service'">
                            <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Service Price (₹)
                                *</label>
                            <input type="number" step="0.01" min="0" x-model.number="newItem.price"
                                class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                        </div>

                        {{-- PRODUCT fields --}}
                        <template x-if="newItem.type==='product'">
                            <div
                                class="md:col-span-3 grid md:grid-cols-3 gap-3 p-3 rounded-xl border border-gray-200 dark:border-neutral-700">
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Making
                                        Charge</label>
                                    <input type="number" step="0.01" min="0"
                                        x-model.number="newItem.making_charge"
                                        class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Gold
                                        Weight (g)</label>
                                    <input type="number" step="0.001" min="0"
                                        x-model.number="newItem.gold_weight"
                                        class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Gold
                                        Purity</label>
                                    <input x-model="newItem.gold_purity" placeholder="e.g. 22K / 916"
                                        class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Silver
                                        Weight (g)</label>
                                    <input type="number" step="0.001" min="0"
                                        x-model.number="newItem.silver_weight"
                                        class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Silver
                                        Purity</label>
                                    <input x-model="newItem.silver_purity" placeholder="e.g. 999"
                                        class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div></div>

                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Stone Wt
                                        (ct)</label>
                                    <input type="number" step="0.001" min="0"
                                        x-model.number="newItem.stone_weight"
                                        class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 dark:text-neutral-300">Diamond Wt
                                        (ct)</label>
                                    <input type="number" step="0.001" min="0"
                                        x-model.number="newItem.diamond_weight"
                                        class="mt-1 w-full border rounded-xl px-3 py-2 text-sm dark:bg-neutral-900 dark:border-neutral-700">
                                </div>
                                <div></div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <div class="text-sm text-red-600" x-text="newItemError"></div>

                            <label
                                class="mt-2 inline-flex items-center gap-2 text-sm text-gray-700 dark:text-neutral-200 select-none">
                                <input type="checkbox" class="rounded border-gray-300 dark:border-neutral-700"
                                    x-model="itemAutoSelect">
                                Save for future
                            </label>
                        </div>

                        <div class="flex gap-2 shrink-0">
                            <button type="button" class="px-4 py-2 rounded-xl border dark:border-neutral-700"
                                @click="closeItemModal()">
                                Cancel
                            </button>

                            <button type="button"
                                class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
                                :disabled="savingItem" @click="saveItem()">
                                <span x-show="!savingItem"
                                    x-text="itemAutoSelect ? 'Save Item' : 'Use for this Invoice'"></span>
                                <span x-show="savingItem">Saving...</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            {{-- =================== PREVIOUS INVOICE CONFIRM MODAL =================== --}}
            <div x-show="confirmLoadModal" x-transition.opacity
                class="fixed inset-0 z-[9999] flex items-center justify-center px-3"
                style="display:none;">

                <div class="absolute inset-0 bg-black/50" @click="cancelApplyLastInvoice()"></div>

                <div class="relative w-full max-w-4xl bg-white dark:bg-neutral-900 rounded-2xl shadow-xl border border-gray-200 dark:border-neutral-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">
                            Previous Invoice Found
                        </h3>
                        <button type="button"
                            class="text-2xl leading-none text-gray-500 hover:text-red-600"
                            @click="cancelApplyLastInvoice()">×</button>
                    </div>

                    <template x-if="pendingInvoicePreview">
                        <div class="space-y-4">
                            <div class="grid md:grid-cols-4 gap-3 text-sm">
                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">Invoice No</div>
                                    <div class="font-semibold text-gray-900 dark:text-neutral-100"
                                        x-text="pendingInvoicePreview.invoice_number || '-'"></div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">Invoice Date</div>
                                    <div class="font-semibold text-gray-900 dark:text-neutral-100"
                                        x-text="pendingInvoicePreview.invoice_date || '-'"></div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">Items Count</div>
                                    <div class="font-semibold text-gray-900 dark:text-neutral-100"
                                        x-text="pendingInvoicePreview.items_count || 0"></div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">Round Off</div>
                                    <div class="font-semibold text-gray-900 dark:text-neutral-100">
                                        ₹ <span x-text="Number(pendingInvoicePreview.round_off || 0).toFixed(2)"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="border rounded-xl border-gray-200 dark:border-neutral-700 p-3 max-h-72 overflow-auto">
                                <div class="text-sm font-semibold mb-3 text-gray-900 dark:text-neutral-100">Previous Items</div>

                                <template x-if="pendingInvoicePreview.items && pendingInvoicePreview.items.length">
                                    <div class="space-y-2">
                                        <template x-for="(it, idx) in pendingInvoicePreview.items" :key="idx">
                                            <div class="border-b border-gray-200 dark:border-neutral-700 pb-2">
                                                <div class="font-medium text-sm text-gray-900 dark:text-neutral-100"
                                                    x-text="(idx + 1) + '. ' + (it.description || '-')"></div>

                                                <div class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
                                                    Type:
                                                    <span x-text="it.item_type || '-'"></span>
                                                    |
                                                    Qty:
                                                    <span x-text="it.quantity || 0"></span>
                                                    |
                                                    Tax:
                                                    <span x-text="it.tax_percent || 0"></span>%
                                                    |
                                                    Amount:
                                                    ₹<span x-text="Number(it.manual_amount || 0).toFixed(2)"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="!pendingInvoicePreview.items || pendingInvoicePreview.items.length === 0">
                                    <div class="text-sm text-gray-500 dark:text-neutral-400">No previous items found.</div>
                                </template>
                            </div>

                            <div class="grid md:grid-cols-3 gap-3 text-sm">
                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">Discount</div>
                                    <div class="font-semibold text-gray-900 dark:text-neutral-100">
                                        ₹ <span x-text="Number(pendingInvoicePreview.discount_total || 0).toFixed(2)"></span>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">Charges</div>
                                    <div class="font-semibold text-gray-900 dark:text-neutral-100">
                                        ₹ <span x-text="Number(pendingInvoicePreview.charge_total || 0).toFixed(2)"></span>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">TCS</div>
                                    <div class="font-semibold text-gray-900 dark:text-neutral-100">
                                        <span x-text="Number(pendingInvoicePreview.tcs_percent || 0).toFixed(2)"></span>%
                                        /
                                        ₹ <span x-text="Number(pendingInvoicePreview.tcs_amount || 0).toFixed(2)"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-3 text-sm">
                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">Payment Summary</div>
                                    <div class="font-medium text-gray-900 dark:text-neutral-100 leading-6">
                                        Cash: ₹<span x-text="Number(pendingInvoicePreview.pay_cash || 0).toFixed(2)"></span>,
                                        UPI: ₹<span x-text="Number(pendingInvoicePreview.pay_upi || 0).toFixed(2)"></span>,
                                        Card: ₹<span x-text="Number(pendingInvoicePreview.pay_card || 0).toFixed(2)"></span>,
                                        Cheque: ₹<span x-text="Number(pendingInvoicePreview.pay_cheque || 0).toFixed(2)"></span>
                                    </div>
                                </div>

                                <div>
                                    <div class="text-gray-500 dark:text-neutral-400">Terms</div>
                                    <div class="font-medium text-gray-900 dark:text-neutral-100 whitespace-pre-line"
                                        x-text="pendingInvoicePreview.terms || '-'"></div>
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button"
                                    class="px-4 py-2 rounded-xl border border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200"
                                    @click="cancelApplyLastInvoice()">
                                    No, Not Now
                                </button>

                                <button type="button"
                                    class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700"
                                    @click="confirmApplyLastInvoice()">
                                    Continieu With These Details
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </form>
    </div>

    <script type="application/json" id="banks-json">{!! $banksJson !!}</script>
    <script>

        function invoiceForm() {
            // ---------- SAFE JSON READ ----------
            const readJSON = (id, fallback) => {
                try {
                    const el = document.getElementById(id);
                    return JSON.parse(el?.textContent || JSON.stringify(fallback));
                } catch (e) {
                    return fallback;
                }
            };

            const CLIENTS = readJSON('clients-json', []);
            const ITEMS = readJSON('items-json', []);
            const METAL_RATES = readJSON('metal-rates-json', []);
            const BANKS = readJSON('banks-json', []);
            const DOCTORS = readJSON('doctors-json', []);
            const DEPARTMENTS = readJSON('departments-json', []);
            const WARDS = readJSON('wards-json', []);
            const ROOMS = readJSON('rooms-json', []);
            const BEDS = readJSON('beds-json', []);
            // const CATEGORIES = JSON.parse(document.getElementById('categories-json')?.textContent || '[]');
            const CATEGORIES = readJSON('categories-json', []);
            const ALLOWED_FIELDS = readJSON('allowed-fields-json', []);

            const showAllowedField = (field) => {
                return ALLOWED_FIELDS.length === 0 || ALLOWED_FIELDS.includes(field);
            };

            const BIZ_STATE_CODE = @js($businessStateCode ?? '');
            const BIZ_GSTIN = @js($businessGstin ?? '');
            const DEFAULT_TERMS = @js($defaultTerms);
            const TODAY = @js($today);
            const DOC_TYPE = @js($docType);
            const LAST_CLIENT_INVOICE_BASE_URL = @js(url('/invoices/client'));

            // ---------- HELPERS ----------
            const n = (v, d = 0) => {
                const x = Number(v);
                return Number.isFinite(x) ? x : d;
            };

            const clamp = (v, min, max) => Math.min(max, Math.max(min, v));
            const s = (v) => (v ?? '').toString();
            const lower = (v) => s(v).toLowerCase();
            const money = (v) => '₹ ' + n(v).toFixed(2);

            const normalizeGstin = (v) => s(v).toUpperCase().replace(/[^0-9A-Z]/g, '').trim();

            const validateGstinLocal = (gstin) => {
                const g = normalizeGstin(gstin);
                if (!g) return { ok: true, empty: true, message: '' };

                if (g.length !== 15) {
                    return { ok: false, empty: false, message: 'GSTIN must be 15 characters.' };
                }

                const re = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/;
                if (!re.test(g)) {
                    return { ok: false, empty: false, message: 'GSTIN format is invalid (check state/PAN/etc).' };
                }

                const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                const mod = 36;
                const codePoint = (c) => chars.indexOf(c);

                let sum = 0;
                let f = 2;

                for (let i = 13; i >= 0; i--) {
                    const v = codePoint(g[i]);
                    if (v === -1) {
                        return { ok: false, empty: false, message: 'GSTIN has invalid characters.' };
                    }

                    const p = v * f;
                    f = (f === 2) ? 1 : 2;
                    sum += Math.floor(p / mod) + (p % mod);
                }

                const checkCodePoint = (mod - (sum % mod)) % mod;
                const expected = chars[checkCodePoint];
                const actual = g[14];

                if (expected !== actual) {
                    return { ok: false, empty: false, message: 'GSTIN checksum mismatch (likely wrong GSTIN).' };
                }

                return { ok: true, empty: false, message: 'GSTIN looks valid.' };
            };

            const keyCode = (v) => s(v).trim().replace(/^0+/, '');

            // ---------- CONSTANTS ----------
            const STATES = [
                { code: '01', name: 'Jammu and Kashmir' },
                { code: '02', name: 'Himachal Pradesh' },
                { code: '03', name: 'Punjab' },
                { code: '04', name: 'Chandigarh' },
                { code: '05', name: 'Uttarakhand' },
                { code: '06', name: 'Haryana' },
                { code: '07', name: 'Delhi' },
                { code: '08', name: 'Rajasthan' },
                { code: '09', name: 'Uttar Pradesh' },
                { code: '10', name: 'Bihar' },
                { code: '11', name: 'Sikkim' },
                { code: '12', name: 'Arunachal Pradesh' },
                { code: '13', name: 'Nagaland' },
                { code: '14', name: 'Manipur' },
                { code: '15', name: 'Mizoram' },
                { code: '16', name: 'Tripura' },
                { code: '17', name: 'Meghalaya' },
                { code: '18', name: 'Assam' },
                { code: '19', name: 'West Bengal' },
                { code: '20', name: 'Jharkhand' },
                { code: '21', name: 'Odisha' },
                { code: '22', name: 'Chhattisgarh' },
                { code: '23', name: 'Madhya Pradesh' },
                { code: '24', name: 'Gujarat' },
                { code: '26', name: 'Dadra and Nagar Haveli and Daman and Diu' },
                { code: '27', name: 'Maharashtra' },
                { code: '29', name: 'Karnataka' },
                { code: '30', name: 'Goa' },
                { code: '31', name: 'Lakshadweep' },
                { code: '32', name: 'Kerala' },
                { code: '33', name: 'Tamil Nadu' },
                { code: '34', name: 'Puducherry' },
                { code: '35', name: 'Andaman and Nicobar Islands' },
                { code: '36', name: 'Telangana' },
                { code: '37', name: 'Andhra Pradesh' },
                { code: '38', name: 'Ladakh' },
            ];

            // ---------- FACTORIES ----------
            const blankParty = () => ({
                name: '',
                address: '',
                state: '',
                state_code: '',
                mobile: '',
                gstin: '',
                pincode: ''
            });

            const rowTemplate = () => ({
                _k: Date.now() + Math.random(),
                item_id: null,
                item_type: null,

                search: '',
                ddOpen: false,
                ddHi: 0,
                ddStyle: '',
                ddPreviewName: '',
                ddPreview: '',

                description: '',
                hsn: '',
                quantity: 1,

                // making_charge_type: 'percent',
                making_charge_type: 'percentage',
                making_rate: 0,
                gold_purity: null,
                silver_purity: null,
                gold_rate: 0,
                silver_rate: 0,
                silver_wt: 0,
                gold_wt: 0,
                gemstone_wt: 0,
                diamond_wt: 0,
                gemstone_charge: 0,
                diamond_charge: 0,

                service_rate: 0,
                fixed_price: 0, // ✅ items.price: agar price set hai to metal calculation skip hogi
                tax_percent: 0,

                amount_mode: 'auto',
                manual_amount: 0,
            });

            const chargeTemplate = () => ({
                _k: Date.now() + Math.random(),
                name: '',
                amount: 0,
            });

            // ---------- CLIENT DROPDOWN CONTROLLER ----------
            const createClientDD = (ctx) => ({
                isOpen: false,
                q: '',
                hi: 0,

                open() {
                    this.isOpen = true;
                    this.hi = 0;
                },
                close() {
                    this.isOpen = false;
                },

                filtered() {
                    const q = lower(this.q).trim();
                    const list = ctx.clients || [];
                    if (!q) return list;

                    return list.filter(c => {
                        const name = lower(c.name);
                        const mob = lower(c.mobile);
                        const gstin = lower(c.gstin);
                        const code = lower(c.state_code);

                        return (
                            name.includes(q) ||
                            mob.includes(q) ||
                            gstin.includes(q) ||
                            code.includes(q)
                        );
                    });
                },

                down() {
                    if (!this.isOpen) this.open();
                    const list = this.filtered();
                    if (!list.length) return;
                    this.hi = Math.min(list.length - 1, this.hi + 1);
                },

                up() {
                    if (!this.isOpen) this.open();
                    const list = this.filtered();
                    if (!list.length) return;
                    this.hi = Math.max(0, this.hi - 1);
                },

                enter() {
                    const list = this.filtered();
                    if (!this.isOpen || !list.length) return;
                    this.select(list[this.hi]);
                },

                select(c) {
                    ctx.clientId = c.id;
                    this.q = c.mobile ? (c.name + ' (' + c.mobile + ')') : c.name;
                    this.close();
                }
            });

           

            return {
                // DATA
                clients: CLIENTS,
                itemCatalogCount: ITEMS.length,
                itemsData: ITEMS,
                categories: CATEGORIES,

                 showItemField(field) {
                    return showAllowedField(field);
                },



                hasValue(v) {
                    if (v === null || v === undefined) return false;
                    if (String(v).trim() === '') return false;
                    return Number(v) > 0 || isNaN(Number(v));
                },


                metalRates: METAL_RATES,
                banks: BANKS,
                states: STATES,

                clientId: '',
                party: blankParty(),
                clientDD: null,

                doctors: DOCTORS,
                departments: DEPARTMENTS,
                wards: WARDS,
                rooms: ROOMS,
                beds: BEDS,

                hospital: {
                    patient_uhid: '',
                    patient_age: '',
                    patient_gender: '',
                    blood_group: '',
                    guardian_name: '',
                    visit_type: 'opd',
                    visit_number: '',
                    visit_at: TODAY + 'T' + new Date().toTimeString().slice(0, 5),
                    doctor_id: '',
                    department_id: '',
                    referred_by: '',
                    billing_category: 'cash',
                    ward_id: '',
                    room_id: '',
                    bed_id: '',
                    admitted_at: '',
                    discharged_at: '',
                    insurance_provider: '',
                    insurance_policy_number: '',
                    chief_complaint: '',
                    diagnosis: '',
                    notes: '',
                },

                onHospitalVisitTypeChange() {
                    if (!['ipd', 'emergency', 'day_care'].includes(this.hospital.visit_type)) {
                        this.hospital.ward_id = '';
                        this.hospital.room_id = '';
                        this.hospital.bed_id = '';
                        this.hospital.admitted_at = '';
                        this.hospital.discharged_at = '';
                    }
                },

                filteredHospitalRooms() {
                    const wardId = String(this.hospital.ward_id || '');
                    if (!wardId) return this.rooms;
                    return this.rooms.filter(room => String(room.ward_id || '') === wardId);
                },

                filteredHospitalBeds() {
                    const roomId = String(this.hospital.room_id || '');
                    const wardId = String(this.hospital.ward_id || '');
                    return this.beds.filter(bed => {
                        if (roomId) return String(bed.room_id || '') === roomId;
                        if (wardId && bed.ward_id !== undefined) return String(bed.ward_id || '') === wardId;
                        return !roomId && !wardId;
                    });
                },

                hdr: {
                    date: TODAY,
                    transport_mode: 'By Hand',
                    gst_no: BIZ_GSTIN,
                    reverse_charge: false,
                    terms: DEFAULT_TERMS,
                    kot_input: '',
                    kots: [],
                },

                basePrefix: @js($basePrefix),
                computedPrefix: @js($suggestedPrefix),
                invoiceNo: @js($initialInvoiceNo),

                payment: {
                    received: 0,
                    mode: 'cash',
                    markFullyPaid: false,
                    bank_account_id: ''
                },

                pay: {
                    cash: 0,
                    upi: 0,
                    card: 0,
                    cheque: 0,
                    credit_excess: 0,
                    advance: 0,
                    online_mode: '',
                    online_ref: '',
                    upi_id: '',
                    card_last4: '',
                    card_ref: '',
                    cheque_no: '',
                    bank_name: '',
                },

                ui: {
                    showCharges: false,
                    showDiscount: false
                },

                charges: [],
                discount: {
                    type: 'flat',
                    value: 0
                },
                tcs: {
                    apply: false,
                    percent: 0
                },
                roundOff: {
                    enabled: false
                },

                barcodeInput: '',
                barcodeMessage: '',
                barcodeError: false,
                barcodeScanning: false,
                lastScannedBarcode: '',
                lastScannedAt: 0,



                items: [],
                barcodeInput: '',
                barcodeMessage: '',
                barcodeError: false,

                saving: false,
                savingClient: false,
                savingItem: false,
                newClientError: '',
                newItemError: '',
                activeRowIndex: null,
                clientAutoSelect: true,
                itemAutoSelect: true,

                gstin: {
                    status: 'idle',
                    message: '',
                    value: ''
                },
                clientGstin: {
                    status: 'idle',
                    message: '',
                    value: ''
                },

                modals: {
                    client: false,
                    item: false
                },

                lastInvoiceInfo: {
                    found: false,
                    invoice_number: '',
                    invoice_id: null,
                },

                loadingLastInvoice: false,

                confirmLoadModal: false,
                pendingInvoicePreview: null,
                pendingInvoiceData: null,

                newClient: {
                    name: '',
                    mobile: '',
                    address: '',
                    state: '',
                    state_code: '',
                    gstin: '',
                    pincode: '',
                    state_pick: ''
                },

                newItem: {

                    type: 'product',
                    name: '',
                    sku: '',
                    description: '',
                    category_id: '',
                    tax_rate: 0,
                    hsn: '',
                    sac: '',
                    price: 0,
                    making_charge: 0,
                    gold_weight: 0,
                    gold_purity: '',
                    silver_weight: 0,
                    silver_purity: '',
                    stone_weight: 0,
                    diamond_weight: 0
                    
                },

                // ---------- UTILS ----------
                csrf() {
                    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                },

                money,

                makingTypeLabel(row) {
                    const type = row.making_charge_type || 'percentage';

                    if (type === 'percentage') return 'Making: % of product amount';
                    if (type === 'fixed') return 'Making: fixed amount';
                    if (type === 'per_gram') return 'Making: ₹ per gram';
                    if (type === 'per_product') return 'Making: whole product';

                    return 'Making: % of product amount';
                },

                filteredItems(q) {
                    const query = lower(q).trim();
                    const list = this.itemsData || [];
                    if (!query) return list;

                    return list.filter(it => {
                        // const name = lower(it.name);
                        // const sku = lower(it.sku);
                        // const desc = lower(it.description || it.desc || it.long_description);
                        // return name.includes(query) || sku.includes(query) || desc.includes(query);
                        const name = lower(it.name);
                        const sku = lower(it.sku);
                        const barcode = lower(it.barcode);
                        const desc = lower(it.description);

                        return name.includes(query)
                            || sku.includes(query)
                            || barcode.includes(query)
                            || desc.includes(query);
                    });
                },

                getItemSearchLabel(itemId) {
                    const it = (this.itemsData || []).find(x => String(x.id) === String(itemId));
                    if (!it) return '';
                    return it.sku ? `${it.name} (${it.sku})` : (it.name || '');
                },

                normalizeItemType(v) {
                    const t = s(v).toLowerCase().trim();

                    if (t === 'product' || t === 'service') {
                        return t;
                    }

                    return '';
                },

                // inferItemType(it) {
                //     const explicitType = this.normalizeItemType(it?.type || it?.item_type || '');

                //     if (explicitType === 'product' || explicitType === 'service') {
                //         return explicitType;
                //     }

                //     const hasServicePrice =
                //         Number(it?.price || 0) > 0 &&
                //         Number(it?.gold_weight ?? it?.gold_wt ?? 0) <= 0 &&
                //         Number(it?.silver_weight ?? it?.silver_wt ?? 0) <= 0 &&
                //         Number(it?.making_charge ?? it?.making_rate ?? 0) <= 0;

                //     if (hasServicePrice) {
                //         return 'service';
                //     }

                //     const hasProductData =
                //         Number(it?.gold_weight ?? it?.gold_wt ?? 0) > 0 ||
                //         Number(it?.silver_weight ?? it?.silver_wt ?? 0) > 0 ||
                //         Number(it?.stone_weight ?? it?.gemstone_wt ?? 0) > 0 ||
                //         Number(it?.diamond_weight ?? it?.diamond_wt ?? 0) > 0 ||
                //         Number(it?.making_charge ?? it?.making_rate ?? 0) > 0 ||
                //         !!(it?.gold_purity) ||
                //         !!(it?.silver_purity);

                //     if (hasProductData) {
                //         return 'product';
                //     }

                //     if ((it?.sac || '') && !(it?.hsn || '')) {
                //         return 'service';
                //     }

                //     return 'service';
                // },

                inferItemType(it) {
                    const explicitType = this.normalizeItemType(it?.type || it?.item_type || '');

                    if (explicitType) {
                        return explicitType;
                    }

                    const hasProductData =
                        Number(it?.gold_weight ?? it?.gold_wt ?? 0) > 0 ||
                        Number(it?.silver_weight ?? it?.silver_wt ?? 0) > 0 ||
                        Number(it?.stone_weight ?? it?.gemstone_wt ?? 0) > 0 ||
                        Number(it?.diamond_weight ?? it?.diamond_wt ?? 0) > 0 ||
                        Number(it?.making_charge ?? it?.making_rate ?? 0) > 0 ||
                        !!(it?.gold_purity) ||
                        !!(it?.silver_purity) ||
                        !!(it?.hsn);

                    if (hasProductData) {
                        return 'product';
                    }

                    if ((it?.sac || '') && !(it?.hsn || '')) {
                        return 'service';
                    }

                    // ✅ Blank / empty item ko product treat karo,
                    // taki baad me gold rate + gold weight daalne par amount calculate ho.
                    return 'product';
                },

                resetRowForService(r) {
                    r.making_rate = 0;
                    r.gold_purity = null;
                    r.silver_purity = null;
                    r.gold_rate = 0;
                    r.silver_rate = 0;
                    r.gold_wt = 0;
                    r.silver_wt = 0;
                    r.gemstone_wt = 0;
                    r.diamond_wt = 0;
                },

                resetRowForProduct(r) {
                    r.service_rate = 0;
                },

                // init() {
                //     this.clientDD = createClientDD(this);

                //     this.$watch('clientId', () => this.syncParty());

                //     if (!this.items.length) this.items.push(rowTemplate());
                //     if (!this.charges.length) this.charges.push(chargeTemplate());

                //     this.syncParty();
                //     this.onReceivedInput();
                //     this.calc();

                //     const reposition = () => {
                //         for (let idx = 0; idx < this.items.length; idx++) {
                //             if (this.items[idx]?.ddOpen) this.setItemDDPos(idx);
                //         }
                //     };

                //     window.addEventListener('scroll', reposition, true);
                //     window.addEventListener('resize', reposition);
                // },


                init() {
                    this.clientDD = createClientDD(this);

                    this.$watch('clientId', () => this.syncParty());

                    if (!this.items.length) {
                        this.items.push(rowTemplate());
                    }

                    if (!this.charges.length) {
                        this.charges.push(chargeTemplate());
                    }

                    this.syncParty();
                    this.onReceivedInput();
                    this.calc();

                    const reposition = () => {
                        for (
                            let index = 0;
                            index < this.items.length;
                            index++
                        ) {
                            if (this.items[index]?.ddOpen) {
                                this.setItemDDPos(index);
                            }
                        }
                    };

                    window.addEventListener(
                        'scroll',
                        reposition,
                        true
                    );

                    window.addEventListener(
                        'resize',
                        reposition
                    );

                    this.$nextTick(() => {
                        setTimeout(() => {
                            this.$refs.barcodeInput?.focus();
                        }, 300);
                    });
                },


                filteredItems(query) {
                    const search = lower(query).trim();

                    if (!search) {
                        return this.itemsData || [];
                    }

                    return (this.itemsData || []).filter((item) => {
                        const name = lower(item.name);
                        const sku = lower(item.sku);
                        const barcode = lower(item.barcode);
                        const description = lower(
                            item.description
                            || item.desc
                            || item.long_description
                        );

                        return name.includes(search)
                            || sku.includes(search)
                            || barcode.includes(search)
                            || description.includes(search);
                    });
                },
                // ---------- PARTY ----------
                async syncParty() {
                    const c = (this.clients || []).find(x => String(x.id) === String(this.clientId));

                    this.party = c ? {
                        name: c.name ?? '',
                        mobile: c.mobile ?? '',
                        address: c.address ?? '',
                        state: c.state ?? '',
                        state_code: c.state_code ?? '',
                        gstin: c.gstin ?? '',
                        pincode: c.pincode ?? '',
                    } : blankParty();

                    if (c && this.clientDD) {
                        this.clientDD.q = c.mobile ? (c.name + ' (' + c.mobile + ')') : c.name;
                    }

                    this.calc();
                    await this.previewLastInvoiceForClient();
                },

                isIntra() {
                    const bizCode = keyCode(BIZ_STATE_CODE);
                    const partyCode = keyCode(this.party.state_code);
                    if (!bizCode || !partyCode) return false;
                    return bizCode === partyCode;
                },

                hasProduct() {
                    return (this.items || []).some(r => this.normalizeItemType(r.item_type) === 'product');
                },

                hasService() {
                    return (this.items || []).some(r => this.normalizeItemType(r.item_type) === 'service');
                },

                // ---------- LAST INVOICE PREVIEW / APPLY ----------
                resetInvoiceDataKeepParty() {
                    this.items = [rowTemplate()];
                    this.charges = [chargeTemplate()];

                    this.discount = { type: 'flat', value: 0 };
                    this.tcs = { apply: false, percent: 0 };
                    this.roundOff = { enabled: false };

                    this.pay = {
                        cash: 0,
                        upi: 0,
                        card: 0,
                        cheque: 0,
                        credit_excess: 0,
                        advance: 0,
                        online_mode: '',
                        online_ref: '',
                        upi_id: '',
                        card_last4: '',
                        card_ref: '',
                        cheque_no: '',
                        bank_name: '',
                    };

                    this.payment = {
                        received: 0,
                        mode: 'cash',
                        markFullyPaid: false,
                        bank_account_id: ''
                    };

                    this.hdr.date = TODAY;
                    this.hdr.reverse_charge = false;
                    this.hdr.terms = DEFAULT_TERMS;

                    this.lastInvoiceInfo = {
                        found: false,
                        invoice_number: '',
                        invoice_id: null,
                    };

                    this.onReceivedInput();
                    this.calc();
                },

                async previewLastInvoiceForClient() {
                    if (!this.clientId) {
                        this.resetInvoiceDataKeepParty();
                        this.pendingInvoicePreview = null;
                        this.pendingInvoiceData = null;
                        this.confirmLoadModal = false;
                        return;
                    }

                    try {
                        this.loadingLastInvoice = true;

                        const url = `${LAST_CLIENT_INVOICE_BASE_URL}/${this.clientId}/last?doc_type=${encodeURIComponent(DOC_TYPE)}`;

                        const res = await fetch(url, {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });

                        const data = await res.json().catch(() => ({}));

                        if (!res.ok || !data?.found || !data?.invoice) {
                            this.resetInvoiceDataKeepParty();
                            this.pendingInvoicePreview = null;
                            this.pendingInvoiceData = null;
                            this.confirmLoadModal = false;
                            return;
                        }

                        const inv = data.invoice;

                        this.pendingInvoiceData = inv;
                        this.pendingInvoicePreview = {
                            invoice_number: inv.invoice_number || '',
                            invoice_date: inv.invoice_date || '',
                            terms: inv.terms || '',
                            reverse_charge: !!inv.reverse_charge,
                            pay_cash: Number(inv.pay_cash || 0),
                            pay_upi: Number(inv.pay_upi || 0),
                            pay_card: Number(inv.pay_card || 0),
                            pay_cheque: Number(inv.pay_cheque || 0),
                            credit_sales_excess: Number(inv.credit_sales_excess || 0),
                            advance_amount: Number(inv.advance_amount || 0),
                            discount_total: Number(inv.discount_total || 0),
                            charge_total: Number(inv.charge_total || 0),
                            tcs_percent: Number(inv.tcs_percent || 0),
                            tcs_amount: Number(inv.tcs_amount || 0),
                            round_off: Number(inv.round_off || 0),
                            items_count: Array.isArray(inv.items) ? inv.items.length : 0,
                            items: Array.isArray(inv.items) ? inv.items : [],
                        };

                        this.confirmLoadModal = true;
                    } catch (e) {
                        console.error('Failed to preview previous invoice:', e);
                        this.resetInvoiceDataKeepParty();
                        this.pendingInvoicePreview = null;
                        this.pendingInvoiceData = null;
                        this.confirmLoadModal = false;
                    } finally {
                        this.loadingLastInvoice = false;
                    }
                },

                confirmApplyLastInvoice() {
                    if (!this.pendingInvoiceData) {
                        this.confirmLoadModal = false;
                        return;
                    }

                    this.applyLastInvoiceData(this.pendingInvoiceData);
                    this.confirmLoadModal = false;
                },

                cancelApplyLastInvoice() {
                    this.resetInvoiceDataKeepParty();
                    this.pendingInvoicePreview = null;
                    this.pendingInvoiceData = null;
                    this.confirmLoadModal = false;
                },

                applyLastInvoiceData(inv) {
                    this.lastInvoiceInfo = {
                        found: true,
                        invoice_number: inv.invoice_number || '',
                        invoice_id: inv.id || null,
                    };

                    this.hdr.date = TODAY;
                    this.hdr.reverse_charge = !!inv.reverse_charge;
                    this.hdr.terms = inv.terms || DEFAULT_TERMS;

                    this.pay.cash = Number(inv.pay_cash || 0);
                    this.pay.upi = Number(inv.pay_upi || 0);
                    this.pay.card = Number(inv.pay_card || 0);
                    this.pay.cheque = Number(inv.pay_cheque || 0);
                    this.pay.credit_excess = Number(inv.credit_sales_excess || 0);
                    this.pay.advance = Number(inv.advance_amount || 0);
                    this.pay.online_mode = inv.online_mode || '';
                    this.pay.online_ref = inv.online_ref || '';
                    this.pay.upi_id = inv.upi_id || '';
                    this.pay.card_last4 = inv.card_last4 || '';
                    this.pay.card_ref = inv.card_ref || '';
                    this.pay.cheque_no = inv.cheque_no || '';
                    this.pay.bank_name = inv.bank_name || '';

                    const received =
                        Number(inv.pay_cash || 0) +
                        Number(inv.pay_upi || 0) +
                        Number(inv.pay_card || 0) +
                        Number(inv.pay_cheque || 0);

                    this.payment.received = received;
                    this.payment.markFullyPaid = false;
                    this.payment.bank_account_id = inv.bank_account_id || '';

                    if (Number(inv.pay_cash || 0) > 0) this.payment.mode = 'cash';
                    else if (Number(inv.pay_upi || 0) > 0) this.payment.mode = 'upi';
                    else if (Number(inv.pay_card || 0) > 0) this.payment.mode = 'card';
                    else if (Number(inv.pay_cheque || 0) > 0) this.payment.mode = 'cheque';
                    else this.payment.mode = 'cash';

                    this.discount = {
                        type: 'flat',
                        value: Number(inv.discount_total || 0),
                    };

                    const oldCharges = Array.isArray(inv.charges_json) ? inv.charges_json : [];
                    this.charges = oldCharges.length
                        ? oldCharges.map(c => ({
                            _k: Date.now() + Math.random(),
                            name: c.name || '',
                            amount: Number(c.amount || 0),
                        }))
                        : [chargeTemplate()];

                    this.tcs.apply = Number(inv.tcs_percent || 0) > 0;
                    this.tcs.percent = Number(inv.tcs_percent || 0);
                    this.roundOff.enabled = Number(inv.round_off || 0) !== 0;

                    const oldItems = Array.isArray(inv.items) ? inv.items : [];
                    this.items = oldItems.length
                        ? oldItems.map(it => {
                            const type = this.normalizeItemType(it.item_type);

                            const row = {
                                _k: Date.now() + Math.random(),
                                item_id: it.item_id ?? null,
                                item_type: type,

                                search: this.getItemSearchLabel(it.item_id),
                                ddOpen: false,
                                ddHi: 0,
                                ddStyle: '',
                                ddPreviewName: '',
                                ddPreview: '',

                                description: it.description || '',
                                hsn: it.hsn || '',
                                quantity: Number(it.quantity || 1),

                                making_rate: Number(it.making_rate || 0),
                                gold_purity: it.gold_purity || null,
                                silver_purity: it.silver_purity || null,
                                gold_rate: Number(it.gold_rate || 0),
                                silver_rate: Number(it.silver_rate || 0),
                                silver_wt: Number(it.silver_wt || 0),
                                gold_wt: Number(it.gold_wt || 0),
                                gemstone_wt: Number(it.gemstone_wt || 0),
                                diamond_wt: Number(it.diamond_wt || 0),

                                service_rate: Number(it.service_rate || 0),
                                fixed_price: Number(it.fixed_price || it.price || 0),
                                tax_percent: Number(it.tax_percent || 0),

                                amount_mode: 'manual',
                                manual_amount: Number(it.manual_amount || 0),
                            };

                            if (type === 'service') {
                                this.resetRowForService(row);
                            } else {
                                this.resetRowForProduct(row);
                            }

                            return row;
                        })
                        : [rowTemplate()];

                    this.onReceivedInput();
                    this.calc();

                    this.pendingInvoicePreview = null;
                    this.pendingInvoiceData = null;
                },

                // ---------- METAL RATE ----------
                findMetalRate(type, purity) {
                    const t = lower(type).trim();
                    const pRaw = s(purity).trim();
                    if (!t) return 0;

                    const candidates = [];
                    if (pRaw) {
                        candidates.push(pRaw);
                        candidates.push(pRaw.split('(')[0].trim());
                        candidates.push(pRaw.split(' ')[0].trim());
                        const m = pRaw.match(/\(([^)]+)\)/);
                        if (m?.[1]) candidates.push(s(m[1]).trim());
                    }

                    const uniq = [...new Set(candidates.filter(Boolean))];

                    const rec = (this.metalRates || []).find(r => {
                        const rt = lower(r.metal_type).trim();
                        if (rt !== t) return false;

                        const rp = s(r.purity).trim();
                        if (!rp) return false;

                        const rpBase = rp.split('(')[0].trim();
                        const rpFirst = rp.split(' ')[0].trim();
                        const rpParen = (rp.match(/\(([^)]+)\)/) || [])[1]
                            ? s((rp.match(/\(([^)]+)\)/) || [])[1]).trim()
                            : '';

                        return uniq.includes(rp) || uniq.includes(rpBase) || uniq.includes(rpFirst) || (rpParen && uniq.includes(rpParen));
                    });

                    return rec ? n(rec.rate_per_gram ?? rec.rate ?? 0) : 0;
                },

                async scanBarcode() {
                    if (this.barcodeScanning) {
                        return;
                    }

                    const code = String(this.barcodeInput || '').trim();

                    if (!code) {
                        this.barcodeError = true;
                        this.barcodeMessage = 'Please scan or enter a barcode.';

                        this.$nextTick(() => {
                            this.$refs.barcodeInput?.focus();
                        });

                        return;
                    }

                    /*
                    * Scanner kabhi-kabhi same Enter event do baar bhej deta hai.
                    * 800 milliseconds ke andar duplicate scan ko block karenge.
                    */
                    const now = Date.now();

                    if (
                        this.lastScannedBarcode === code
                        && now - this.lastScannedAt < 800
                    ) {
                        return;
                    }

                    this.lastScannedBarcode = code;
                    this.lastScannedAt = now;

                    this.barcodeScanning = true;
                    this.barcodeError = false;
                    this.barcodeMessage = 'Searching item...';

                    try {
                        /*
                        * Pehle already loaded items JSON me item search karenge.
                        */
                        let item = (this.itemsData || []).find((record) => {
                            const recordBarcode = String(
                                record?.barcode || ''
                            ).trim().toLowerCase();

                            const recordSku = String(
                                record?.sku || ''
                            ).trim().toLowerCase();

                            const scanCode = code.toLowerCase();

                            return recordBarcode === scanCode
                                || recordSku === scanCode;
                        });

                        /*
                        * Local JSON me nahi mila to backend lookup route call hoga.
                        */
                        if (!item) {
                            const lookupUrl = new URL(
                                @js(route('items.barcode.lookup')),
                                window.location.origin
                            );

                            lookupUrl.searchParams.set('barcode', code);

                            const response = await fetch(lookupUrl.toString(), {
                                method: 'GET',
                                credentials: 'same-origin',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            let responseData = {};

                            try {
                                responseData = await response.json();
                            } catch (jsonError) {
                                throw new Error(
                                    'Invalid server response received.'
                                );
                            }

                            if (!response.ok || responseData.ok === false) {
                                throw new Error(
                                    responseData.message
                                    || responseData.msg
                                    || 'Item not found for this barcode.'
                                );
                            }

                            item = responseData.item
                                || responseData.data?.item
                                || responseData.data;

                            if (!item || !item.id) {
                                throw new Error(
                                    'Valid item information was not received.'
                                );
                            }

                            /*
                            * Fetched item ko current items list me add karenge,
                            * jisse existing pickItem() method use kar sake.
                            */
                            const itemAlreadyAvailable = (
                                this.itemsData || []
                            ).some((existingItem) => {
                                return String(existingItem.id)
                                    === String(item.id);
                            });

                            if (!itemAlreadyAvailable) {
                                this.itemsData.unshift(item);
                            }
                        }

                        /*
                        * Same item invoice me already added hai to quantity +1.
                        */
                        const existingRowIndex = this.items.findIndex((row) => {
                            return String(row.item_id || '')
                                === String(item.id);
                        });

                        if (existingRowIndex >= 0) {
                            const existingRow = this.items[existingRowIndex];

                            existingRow.quantity = Math.max(
                                1,
                                Number(existingRow.quantity || 0) + 1
                            );

                            /*
                            * Manual amount ko dobara auto calculation par lana hai.
                            */
                            existingRow.amount_mode = 'auto';
                            existingRow.manual_amount = this.lineAmount(existingRow);

                            this.onAutoChange(existingRow);
                            this.calc();

                            this.barcodeError = false;
                            this.barcodeMessage =
                                `${item.name} quantity increased to ${existingRow.quantity}.`;

                            this.resetBarcodeInput();

                            return;
                        }

                        /*
                        * Empty invoice row search karein.
                        */
                        let rowIndex = this.items.findIndex((row) => {
                            return !row.item_id;
                        });

                        /*
                        * Empty row nahi hai to new row create karein.
                        */
                        if (rowIndex < 0) {
                            this.add();
                            rowIndex = this.items.length - 1;
                        }

                        /*
                        * Existing pickItem method item ki complete detail fill karega.
                        */
                        this.pickItem(rowIndex, item.id);

                        const selectedRow = this.items[rowIndex];

                        if (!selectedRow || !selectedRow.item_id) {
                            throw new Error(
                                'Item could not be added to invoice.'
                            );
                        }

                        selectedRow.quantity = 1;
                        selectedRow.amount_mode = 'auto';
                        selectedRow.manual_amount = this.lineAmount(selectedRow);

                        this.onAutoChange(selectedRow);
                        this.calc();

                        this.barcodeError = false;
                        this.barcodeMessage =
                            `${item.name} added successfully.`;

                        this.resetBarcodeInput();

                    } catch (error) {
                        console.error('Barcode scanner error:', error);

                        this.barcodeError = true;
                        this.barcodeMessage =
                            error?.message
                            || 'Unable to find item for this barcode.';

                        this.resetBarcodeInput(false);

                    } finally {
                        this.barcodeScanning = false;
                    }
                },

                resetBarcodeInput(clearMessage = false) {
                    this.barcodeInput = '';

                    if (clearMessage) {
                        this.barcodeMessage = '';
                        this.barcodeError = false;
                    }

                    this.$nextTick(() => {
                        this.$refs.barcodeInput?.focus();
                    });
                },

                clearBarcodeScanner() {
                    this.barcodeInput = '';
                    this.barcodeMessage = '';
                    this.barcodeError = false;
                    this.barcodeScanning = false;
                    this.lastScannedBarcode = '';
                    this.lastScannedAt = 0;

                    this.$nextTick(() => {
                        this.$refs.barcodeInput?.focus();
                    });
                },


                // ---------- ROW ACTIONS ----------
                add() {
                    this.items.push(rowTemplate());
                    this.calc();
                },

                remove(i) {
                    this.items.splice(i, 1);
                    if (!this.items.length) this.items.push(rowTemplate());
                    this.calc();
                },

                pickItem(i, id) {
                    const it = (this.itemsData || []).find(x => String(x.id) === String(id));
                    if (!it) return;

                    const r = this.items[i];
                    if (!r) return;

                    r.item_id = it.id;
                    r.item_type = this.inferItemType(it);

                    r.search = it.sku ? (it.name + ' (' + it.sku + ')') : it.name;
                    r.description = it.description || it.name || '';
                    r.tax_percent = n(it.tax_rate, 0);
                    r.fixed_price = n(it.price, 0); // ✅ price field priority

                    r.amount_mode = 'auto';
                    r.manual_amount = 0;

                    if (r.item_type === 'service') {
                        r.hsn = it.sac || '';
                        r.quantity = Math.max(1, n(r.quantity, 1));
                        r.service_rate = n(it.price, 0);

                        this.resetRowForService(r);

                        r.manual_amount = this.lineAmount(r);
                        this.calc();
                        return;
                    }

                    r.hsn = it.hsn || it.sac || '';
                    r.quantity = n(it.quantity, 1) || 1;

                    this.resetRowForProduct(r);

                    r.gold_wt = n(it.gold_weight ?? it.gold_wt, 0);
                    r.silver_wt = n(it.silver_weight ?? it.silver_wt, 0);

                    r.gold_purity = s(it.gold_purity ?? it.purity).trim() || null;
                    r.silver_purity = s(it.silver_purity).trim() || null;

                    r.gemstone_wt = n(it.stone_weight ?? it.gemstone_wt, 0);
                    r.diamond_wt = n(it.diamond_weight ?? it.diamond_wt, 0);

                    r.gemstone_charge = n(it.gemstone_charge ?? it.stone_charge ?? 0, 0);
                    r.diamond_charge = n(it.diamond_charge ?? 0, 0);

                    // r.making_charge_type = it.making_charge_type || 'percent';

                    r.making_charge_type = it.making_charge_type || 'percentage';
                    r.making_rate = n(it.making_charge ?? it.making_rate, 0);
                    r.gold_rate = this.findMetalRate('gold', r.gold_purity);
                    r.silver_rate = this.findMetalRate('silver', r.silver_purity || '999');

                    r.manual_amount = this.lineAmount(r);
                    this.calc();
                },


                onAmountEdit(r) {
                    const total = n(r.manual_amount, 0);

                    if (total <= 0) {
                        r.amount_mode = 'auto';
                        r.manual_amount = this.lineAmount(r);
                        this.calc();
                        return;
                    }

                    r.amount_mode = 'manual';

                    const qty = Math.max(1, n(r.quantity, 1));
                    const pct = n(r.tax_percent, 0);

                    // total amount me se tax reverse
                    let base = pct > 0 ? (total / (1 + (pct / 100))) : total;

                    // making charge bhi reverse
                    const makingPercent = n(r.making_rate, 0);
                    if (makingPercent > 0) {
                        base = base / (1 + (makingPercent / 100));
                    }

                    const perUnit = n((base / qty).toFixed(2), 0);

                    // dono update karo, item type check nahi
                    r.fixed_price = perUnit;
                    r.service_rate = perUnit;

                    this.calc();
                },

                // onAutoChange(r) {
                //     if (r.amount_mode !== 'manual') {
                //         r.manual_amount = this.lineAmount(r);
                //     } else {
                //         this.onAmountEdit(r);
                //         return;
                //     }

                //     this.calc();
                // },

                onAutoChange(r) {
                    // ✅ Agar service type hai lekin user ne metal/stone/diamond field bhar diya,
                    // to row ko product bana do.
                    const hasMetalOrJewelleryValue =
                        n(r.gold_rate, 0) > 0 ||
                        n(r.gold_wt, 0) > 0 ||
                        n(r.silver_rate, 0) > 0 ||
                        n(r.silver_wt, 0) > 0 ||
                        n(r.gemstone_wt, 0) > 0 ||
                        n(r.gemstone_charge, 0) > 0 ||
                        n(r.diamond_wt, 0) > 0 ||
                        n(r.diamond_charge, 0) > 0 ||
                        n(r.making_rate, 0) > 0;

                    if (hasMetalOrJewelleryValue) {
                        r.item_type = 'product';
                    }

                    if (r.amount_mode !== 'manual') {
                        r.manual_amount = this.lineAmount(r);
                    } else {
                        this.onAmountEdit(r);
                        return;
                    }

                    this.calc();
                },

                // ---------- LINE CALCS ----------
                lineBase(r) {
                    const qty = Math.max(1, n(r.quantity, 1));
                    const pct = n(r.tax_percent, 0);

                    // ✅ Manual amount me user final amount enter karta hai.
                    // Is case me tax reverse-calculate hoga, making % auto apply nahi hoga.
                    if (r.amount_mode === 'manual') {
                        const total = n(r.manual_amount, 0);
                        const base = (pct > 0) ? (total / (1 + (pct / 100))) : total;
                        return Math.max(0, n(base.toFixed(2), 0));
                    }

                    // ✅ Service item: only service rate * qty, making charge nahi lagega.
                    if (r.item_type === 'service') {
                        const rate = n(r.service_rate, 0);
                        return Math.max(0, n((rate * qty).toFixed(2), 0));
                    }

                    // ✅ Product base:
                    // Agar item price set hai to price ko base maanenge.
                    // Agar price nahi hai to gold/silver weight * rate se base banega.
                    const goldAmt = n(r.gold_wt, 0) * n(r.gold_rate, 0);
                    const silvAmt = n(r.silver_wt, 0) * n(r.silver_rate, 0);
                    // const metalBase = goldAmt + silvAmt;


                    const gemstoneCharge = n(r.gemstone_charge, 0);
                    const diamondCharge = n(r.diamond_charge, 0);

                    const extraCharges = gemstoneCharge + diamondCharge;

                    const productBase =
                        n(r.fixed_price, 0) +
                        goldAmt +
                        silvAmt +
                        extraCharges;

                    // ✅ making_rate percentage hai.
                    // const makingType = r.making_charge_type || 'percent';
                    // const makingRate = n(r.making_rate, 0);

                    // let makingAmount = 0;

                    // if (makingType === 'fixed') {
                    //     makingAmount = makingRate;
                    // } else {
                    //     makingAmount = productBase * (makingRate / 100);
                    // }

                    const makingType = r.making_charge_type || 'percentage';
                    const makingRate = n(r.making_rate, 0);

                    let makingAmount = 0;

                    if (makingType === 'percentage') {
                        makingAmount = productBase * (makingRate / 100);
                    } else if (makingType === 'fixed') {
                        makingAmount = makingRate;
                    } else if (makingType === 'per_gram') {
                        const totalWeight = n(r.gold_wt, 0) + n(r.silver_wt, 0);
                        makingAmount = totalWeight * makingRate;
                    } else if (makingType === 'per_product') {
                        makingAmount = makingRate;
                    }

                    return Math.max(0, n(((productBase + makingAmount) * qty).toFixed(2), 0));
                },

                lineTax(r) {
                    const pct = n(r.tax_percent, 0);
                    const base = this.lineBase(r);
                    return n((base * (pct / 100)).toFixed(2), 0);
                },

                lineAmount(r) {
                    if (r.amount_mode === 'manual') {
                        return n(n(r.manual_amount, 0).toFixed(2), 0);
                    }
                    return n((this.lineBase(r) + this.lineTax(r)).toFixed(2), 0);
                },

                subtotal() {
                    return n((this.items || []).reduce((sum, r) => sum + this.lineBase(r), 0).toFixed(2), 0);
                },

                avgTaxPercentRaw() {
                    const baseSum = (this.items || []).reduce((sum, r) => sum + this.lineBase(r), 0);
                    if (baseSum <= 0) return 0;

                    const weighted = (this.items || []).reduce((sum, r) => sum + (this.lineBase(r) * n(r.tax_percent, 0)), 0);
                    return (weighted / baseSum);
                },

                avgTaxPercent() {
                    return n(this.avgTaxPercentRaw().toFixed(2), 0);
                },

                itemsTaxTotal() {
                    return n((this.items || []).reduce((sum, r) => sum + this.lineTax(r), 0).toFixed(2), 0);
                },

                chargesTotal() {
                    return n((this.charges || []).reduce((sum, c) => sum + n(c.amount, 0), 0).toFixed(2), 0);
                },

                chargesTaxTotal() {
                    const pct = this.avgTaxPercentRaw();
                    return n((this.chargesTotal() * (pct / 100)).toFixed(2), 0);
                },

                discountAmount() {
                    const base = this.subtotal();
                    const v = n(this.discount.value, 0);
                    if (this.discount.type === 'percent') {
                        return n((base * (v / 100)).toFixed(2), 0);
                    }
                    return n(v.toFixed(2), 0);
                },

                taxableAmount() {
                    const val = this.subtotal() + this.chargesTotal() - this.discountAmount();
                    return n(Math.max(0, val).toFixed(2), 0);
                },

                taxOnTaxable() {
                    return n((this.itemsTaxTotal() + this.chargesTaxTotal()).toFixed(2), 0);
                },

                cgst() {
                    return this.isIntra() ? n((this.taxOnTaxable() / 2).toFixed(2), 0) : 0;
                },

                sgst() {
                    return this.isIntra() ? n((this.taxOnTaxable() / 2).toFixed(2), 0) : 0;
                },

                igst() {
                    return this.isIntra() ? 0 : n(this.taxOnTaxable().toFixed(2), 0);
                },

                tcsAmount() {
                    if (!this.tcs.apply) return 0;
                    const pct = n(this.tcs.percent, 0);
                    if (pct <= 0) return 0;
                    const base = this.taxableAmount();
                    return n((base * (pct / 100)).toFixed(2), 0);
                },

                totalBeforeRound() {
                    return n((this.taxableAmount() + this.taxOnTaxable() + this.tcsAmount()).toFixed(2), 0);
                },

                roundOffAmount() {
                    if (!this.roundOff.enabled) return 0;
                    const raw = this.totalBeforeRound();
                    const rounded = Math.round(raw);
                    return n((rounded - raw).toFixed(2), 0);
                },

                totalPayable() {
                    return n((this.totalBeforeRound() + this.roundOffAmount()).toFixed(2), 0);
                },

                // ---------- PAYMENT ----------
                toggleFullyPaid() {
                    if (this.payment.markFullyPaid) {
                        this.payment.received = this.totalPayable();
                    }
                    this.onReceivedInput();
                },

                onReceivedInput() {
                    const amt = n(this.payment.received, 0);

                    this.pay.cash = 0;
                    this.pay.upi = 0;
                    this.pay.card = 0;
                    this.pay.cheque = 0;

                    if (this.payment.mode === 'cash') this.pay.cash = amt;
                    if (this.payment.mode === 'upi') this.pay.upi = amt;
                    if (this.payment.mode === 'card') this.pay.card = amt;
                    if (this.payment.mode === 'cheque') this.pay.cheque = amt;
                    if (this.payment.mode === 'bank') this.pay.upi = amt;

                    if (this.payment.mode === 'cash') {
                        this.payment.bank_account_id = '';
                    }

                    this.calc();
                },

                receivedTotal() {
                    const t = n(this.pay.cash) + n(this.pay.upi) + n(this.pay.card) + n(this.pay.cheque);
                    return n(t.toFixed(2), 0);
                },

                balanceAmount() {
                    const paid = this.receivedTotal() + n(this.pay.credit_excess) + n(this.pay.advance);
                    const bal = this.totalPayable() - paid;
                    return n(Math.max(0, bal).toFixed(2), 0);
                },

                // ---------- CHARGES ----------
                blankCharge() {
                    return chargeTemplate();
                },

                addCharge() {
                    this.charges.push(chargeTemplate());
                    this.calc();
                },

                removeCharge(i) {
                    this.charges.splice(i, 1);
                    this.calc();
                },

                chargesPayload() {
                    return (this.charges || [])
                        .filter(c => s(c.name).trim() || n(c.amount) > 0)
                        .map(c => ({
                            name: s(c.name).trim(),
                            amount: n(c.amount)
                        }));
                },

                // ---------- GST INPUT ----------
                normalizeGstin,
                validateGstinLocal,

                onGstinInput(scope) {
                    if (scope === 'hdr') {
                        const g = normalizeGstin(this.hdr.gst_no);
                        this.hdr.gst_no = g;

                        const res = validateGstinLocal(g);
                        if (res.empty) {
                            this.gstin = { status: 'idle', message: '', value: '' };
                            return;
                        }

                        this.gstin = {
                            status: res.ok ? 'valid' : 'invalid',
                            message: res.ok ? '✅ ' + res.message : '⚠️ ' + res.message,
                            value: g
                        };
                        return;
                    }

                    if (scope === 'client') {
                        const g = normalizeGstin(this.newClient.gstin);
                        this.newClient.gstin = g;

                        const res = validateGstinLocal(g);
                        if (res.empty) {
                            this.clientGstin = { status: 'idle', message: '', value: '' };
                            return;
                        }

                        this.clientGstin = {
                            status: res.ok ? 'valid' : 'invalid',
                            message: res.ok ? '✅ ' + res.message : '⚠️ ' + res.message,
                            value: g
                        };
                    }
                },

                // ---------- ITEM DROPDOWN ----------
                openItemDD(i) {
                    const r = this.items[i];
                    if (!r) return;
                    r.ddOpen = true;
                    r.ddHi = 0;
                    this.$nextTick(() => this.setItemDDPos(i));
                },

                closeItemDD(i, keepText = true) {
                    const r = this.items[i];
                    if (!r) return;
                    r.ddOpen = false;

                    if (keepText && r.item_id) {
                        const it = (this.itemsData || []).find(x => String(x.id) === String(r.item_id));
                        if (it) {
                            r.search = it.sku ? (it.name + ' (' + it.sku + ')') : it.name;
                        }
                    }
                },

                selectItemFromDD(i, it) {
                    const r = this.items[i];
                    if (!r || !it) return;

                    r.item_id = it.id;
                    this.pickItem(i, it.id);
                    r.ddOpen = false;

                    this.$nextTick(() => {
                        const el = document.getElementById('item_search_' + i);
                        if (el) el.focus({ preventScroll: true });
                    });
                },

                itemDDDown(i) {
                    const r = this.items[i];
                    if (!r) return;
                    const list = this.filteredItems(r.search);
                    if (!r.ddOpen) r.ddOpen = true;
                    if (!list.length) return;
                    r.ddHi = Math.min(list.length - 1, (r.ddHi || 0) + 1);
                },

                itemDDUp(i) {
                    const r = this.items[i];
                    if (!r) return;
                    const list = this.filteredItems(r.search);
                    if (!r.ddOpen) r.ddOpen = true;
                    if (!list.length) return;
                    r.ddHi = Math.max(0, (r.ddHi || 0) - 1);
                },

                itemDDEnter(i) {
                    const r = this.items[i];
                    if (!r) return;
                    const list = this.filteredItems(r.search);
                    if (!r.ddOpen || !list.length) return;
                    const it = list[r.ddHi || 0];
                    if (it) this.selectItemFromDD(i, it);
                },

                setItemDDPos(i) {
                    const r = this.items[i];
                    if (!r) return;

                    const input = document.getElementById('item_search_' + i);
                    if (!input) return;

                    const rect = input.getBoundingClientRect();
                    const top = rect.bottom + 4;
                    const left = rect.left;
                    const width = rect.width;
                    const maxH = clamp(window.innerHeight - top - 12, 160, 280);

                    r.ddStyle = `top:${top}px; left:${left}px; width:${width}px; max-height:${maxH}px;`;
                },

                // ---------- KOT ----------
                syncKotsFromInput() {
                    const raw = s(this.hdr.kot_input).trim();
                    if (!raw) {
                        this.hdr.kots = [];
                        return;
                    }

                    const parts = raw.split(/[, \n\t]+/).map(x => x.trim()).filter(Boolean);
                    const seen = new Set();
                    this.hdr.kots = parts.filter(x => !seen.has(x) && seen.add(x));
                    this.hdr.kot_input = this.hdr.kots.join(', ');
                },

                removeKot(idx) {
                    this.hdr.kots.splice(idx, 1);
                    this.hdr.kot_input = this.hdr.kots.join(', ');
                },

                // ---------- MODALS ----------
                openClientModal() {
                    this.newClientError = '';
                    this.newClient = {
                        name: '',
                        mobile: '',
                        address: '',
                        state: '',
                        state_code: '',
                        gstin: '',
                        pincode: '',
                        state_pick: ''
                    };
                    this.clientAutoSelect = true;
                    this.modals.client = true;
                },

                closeClientModal() {
                    this.modals.client = false;
                },

                applyClientState() {
                    const v = s(this.newClient.state_pick).trim();
                    if (!v) {
                        this.newClient.state = '';
                        this.newClient.state_code = '';
                        return;
                    }

                    const parts = v.split(',');
                    this.newClient.state_code = s(parts[0]).trim();
                    this.newClient.state = s(parts.slice(1).join(',')).trim();
                },

                openItemModal(rowIndex = null) {
                    this.activeRowIndex = rowIndex;
                    this.newItemError = '';
                    this.itemAutoSelect = true;

                    this.newItem = {
                        type: 'product',
                        name: '',
                        sku: '',
                        description: '',
                        category_id: '',
                        tax_rate: 0,
                        hsn: '',
                        sac: '',
                        price: 0,
                        making_charge: 0,
                        gold_weight: 0,
                        gold_purity: '',
                        silver_weight: 0,
                        silver_purity: '',
                        stone_weight: 0,
                        diamond_weight: 0
                    };

                    this.modals.item = true;
                },

                closeItemModal() {
                    this.modals.item = false;
                    this.activeRowIndex = null;
                },

                async saveClient() {
                    this.newClientError = '';

                    if (!s(this.newClient.name).trim()) {
                        this.newClientError = 'Name is required.';
                        return;
                    }

                    const mob = s(this.newClient.mobile).replace(/\D/g, '');
                    if (mob && mob.length < 10) {
                        this.newClientError = 'Mobile must be 10 digits (optional).';
                        return;
                    }

                    this.newClient.mobile = mob;

                    try {
                        this.savingClient = true;

                        const res = await fetch(@js(route('clients.quick-store')), {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                ...this.newClient,
                                is_save: this.clientAutoSelect ? 1 : 0
                            })
                        });

                        const data = await res.json().catch(() => ({}));

                        if (!res.ok) {
                            this.newClientError = data?.message || 'Failed to save client.';
                            return;
                        }

                        this.clients.unshift(data.client);

                        if (this.clientAutoSelect) {
                            this.clientId = data.client.id;
                            this.clientDD.q = data.client.mobile
                                ? (data.client.name + ' (' + data.client.mobile + ')')
                                : data.client.name;
                        }

                        this.modals.client = false;
                    } catch (e) {
                        this.newClientError = 'Network error.';
                    } finally {
                        this.savingClient = false;
                    }
                },

                async saveItem() {
                    this.newItemError = '';

                    if (!String(this.newItem.name || '').trim()) {
                        this.newItemError = 'Service / charge name is required.';
                        return;
                    }

                    if (!this.newItem.category_id) {
                        this.newItemError = 'Please select service category.';
                        return;
                    }

                    if (this.newItem.type === 'service' && Number(this.newItem.price || 0) <= 0) {
                        this.newItemError = 'Default service rate is required.';
                        return;
                    }

                    try {
                        this.savingItem = true;

                        const res = await fetch(@js(route('items.store.ajax')), {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                ...this.newItem,
                                is_save: this.itemAutoSelect ? 1 : 0
                            })
                        });

                        const data = await res.json().catch(() => ({}));

                        if (!res.ok) {
                            if (data?.errors?.category_id?.length) {
                                this.newItemError = data.errors.category_id[0];
                            } else {
                                this.newItemError = data?.message || 'Failed to save hospital service.';
                            }
                            return;
                        }

                        data.item.type = data.item.type || this.newItem.type;
                        this.itemsData.unshift(data.item);

                        if (this.activeRowIndex !== null && this.items[this.activeRowIndex]) {
                            this.pickItem(this.activeRowIndex, data.item.id);
                        }

                        this.modals.item = false;
                    } catch (e) {
                        this.newItemError = 'Network error.';
                    } finally {
                        this.savingItem = false;
                    }
                },

                // ---------- FINAL SUBMIT ----------
                calc() {
                    return this.totalPayable();
                },

                beforeSubmit() {
                    const payload = (this.items || []).map(r => ({
                        item_id: r.item_id ?? null,
                        item_type: r.item_type ?? null,
                        description: r.description || '',
                        hsn: r.hsn || '',
                        quantity: Math.max(1, n(r.quantity, 1)),

                        // making_charge_type: r.making_charge_type || 'percent',

                        making_charge_type: r.making_charge_type || 'percentage',
                        making_rate: n(r.making_rate),
                        making_charge: n(r.making_rate),
                        gold_purity: r.gold_purity || null,
                        silver_purity: r.silver_purity || null,
                        gold_rate: n(r.gold_rate),
                        silver_rate: n(r.silver_rate),
                        silver_wt: n(r.silver_wt),
                        gold_wt: n(r.gold_wt),
                        gemstone_wt: n(r.gemstone_wt),
                        diamond_wt: n(r.diamond_wt),

                        gemstone_charge: n(r.gemstone_charge),
                        diamond_charge: n(r.diamond_charge),

                        service_rate: n(r.service_rate),
                        fixed_price: n(r.fixed_price),

                        discount: 0,
                        tax_percent: n(r.tax_percent),

                        amount_mode: r.amount_mode || 'auto',
                        manual_amount: n(r.manual_amount),

                        rate: this.lineBase(r),
                        tax_amount: this.lineTax(r),
                        amount: n(r.manual_amount) > 0 ? n(r.manual_amount) : this.lineAmount(r),
                    }));

                    document.getElementById('items_json').value = JSON.stringify(payload);

                    this.onReceivedInput();
                    this.$refs.form.submit();
                },

                submitForm() {
                    if (this.saving) return;

                    const g = normalizeGstin(this.hdr.gst_no);
                    const res = validateGstinLocal(g);

                    if (g && !res.ok) {
                        const ok = confirm("⚠️ GSTIN invalid lag raha hai.\n\n" + res.message + "\n\nPhir bhi Save karna hai?");
                        if (!ok) return;
                    }
                    this.saving = true;
                    this.$refs.form.requestSubmit();
                },
            };
        }
    </script>


@if($showInvoiceSuggestion ?? false)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            initializeCreateInvoiceGuide();
        });

        function getCreateInvoiceGuideElements() {
            return {
                guide: document.getElementById(
                    'createInvoiceSuggestionGuide'
                ),
                reopen: document.getElementById(
                    'createInvoiceSuggestionReopen'
                ),
            };
        }

        function initializeCreateInvoiceGuide() {
            const elements = getCreateInvoiceGuideElements();

            if (!elements.guide) {
                return;
            }

            const storageKey = elements.guide.dataset.storageKey;

            if (!storageKey) {
                elements.guide.classList.remove('hidden');

                if (elements.reopen) {
                    elements.reopen.classList.add('hidden');
                    elements.reopen.classList.remove('flex');
                }

                return;
            }

            const dismissed = localStorage.getItem(storageKey) === '1';

            if (dismissed) {
                elements.guide.classList.add('hidden');

                if (elements.reopen) {
                    elements.reopen.classList.remove('hidden');
                    elements.reopen.classList.add('flex');
                }
            } else {
                elements.guide.classList.remove('hidden');

                if (elements.reopen) {
                    elements.reopen.classList.add('hidden');
                    elements.reopen.classList.remove('flex');
                }
            }
        }

        function dismissCreateInvoiceGuide() {
            const elements = getCreateInvoiceGuideElements();

            if (!elements.guide) {
                return;
            }

            const storageKey = elements.guide.dataset.storageKey;

            if (storageKey) {
                localStorage.setItem(storageKey, '1');
            }

            elements.guide.classList.add('hidden');

            if (elements.reopen) {
                elements.reopen.classList.remove('hidden');
                elements.reopen.classList.add('flex');
            }
        }

        function showCreateInvoiceGuide() {
            const elements = getCreateInvoiceGuideElements();

            if (!elements.guide) {
                return;
            }

            const storageKey = elements.guide.dataset.storageKey;

            if (storageKey) {
                localStorage.removeItem(storageKey);
            }

            elements.guide.classList.remove('hidden');

            if (elements.reopen) {
                elements.reopen.classList.add('hidden');
                elements.reopen.classList.remove('flex');
            }

            elements.guide.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        }

        function focusCreateInvoiceForm() {
            const elements = getCreateInvoiceGuideElements();

            const partyInput = document.querySelector(
                'input[placeholder="Search patient by name, mobile or UHID"]'
            );

            if (partyInput) {
                partyInput.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                });

                setTimeout(function () {
                    partyInput.focus();
                }, 450);
            }

            if (
                elements.guide &&
                elements.guide.dataset.storageKey
            ) {
                localStorage.setItem(
                    elements.guide.dataset.storageKey,
                    '1'
                );
            }

            if (elements.guide) {
                elements.guide.classList.add('hidden');
            }

            if (elements.reopen) {
                elements.reopen.classList.remove('hidden');
                elements.reopen.classList.add('flex');
            }
        }
    </script>
@endif
</x-layouts.app>