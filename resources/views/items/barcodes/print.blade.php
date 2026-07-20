<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Print Barcode Labels</title>

    <style>
        /*
        |--------------------------------------------------------------------------
        | Print configuration
        |--------------------------------------------------------------------------
        */

        @page {
            size: auto;
            margin: 4mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;

            color: #000000;
            background: #ffffff;

            font-family: Arial, Helvetica, sans-serif;
        }

        /*
        |--------------------------------------------------------------------------
        | Toolbar
        |--------------------------------------------------------------------------
        */

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;

            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;

            padding: 12px;

            background: #ffffff;
            border-bottom: 1px solid #d1d5db;
        }

        .toolbar button {
            padding: 10px 16px;

            border: 0;
            border-radius: 6px;

            cursor: pointer;

            font-size: 14px;
            font-weight: 700;
        }

        .print-button {
            color: #ffffff;
            background: #16a34a;
        }

        .print-button:hover {
            background: #15803d;
        }

        .close-button {
            color: #111827;
            background: #e5e7eb;
        }

        .close-button:hover {
            background: #d1d5db;
        }

        .print-note {
            color: #4b5563;
            font-size: 12px;
        }

        /*
        |--------------------------------------------------------------------------
        | Barcode sheet
        |--------------------------------------------------------------------------
        */

        .sheet {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: flex-start;

            gap: 5mm;
            padding: 5mm;
        }

        /*
         * Barcode ki width fixed nahi rakhi gayi.
         * Label generated barcode ki natural width ke according expand hoga.
         */
        .label {
            display: inline-flex;
            flex-direction: column;
            align-items: center;

            width: max-content;
            min-width: max-content;

            padding: 6mm 10mm;

            overflow: visible;

            color: #000000;
            text-align: center;
            white-space: nowrap;

            background: #ffffff;
            border: 1px dashed #9ca3af;

            page-break-inside: avoid;
            break-inside: avoid;
        }

        /*
        |--------------------------------------------------------------------------
        | Item name
        |--------------------------------------------------------------------------
        */

        .item-name {
            width: 100%;
            margin-bottom: 3mm;

            color: #000000;

            font-size: 17px;
            font-weight: 700;
            line-height: 1.2;

            text-align: center;
        }

        /*
        |--------------------------------------------------------------------------
        | Price
        |--------------------------------------------------------------------------
        */

        .item-price {
            width: 100%;
            margin-bottom: 3mm;

            color: #000000;

            font-size: 16px;
            font-weight: 700;
            line-height: 1.2;

            text-align: center;
        }

        /*
        |--------------------------------------------------------------------------
        | Barcode
        |--------------------------------------------------------------------------
        */

        /*
         * Barcode ke left aur right me large white quiet zone.
         * Scanner ke liye ye bahut zaruri hai.
         */
        .barcode-quiet-zone {
            display: inline-block;

            width: max-content;
            min-width: max-content;

            padding: 0 12mm;

            overflow: visible;

            line-height: 0;
            background: #ffffff;
        }

        /*
         * SVG ko CSS se compress nahi kiya jayega.
         *
         * width: auto
         * max-width: none
         *
         * Isse generated barcode apni natural width me rahega.
         */
        .barcode-quiet-zone svg {
            display: block;

            width: auto !important;
            max-width: none !important;
            min-width: 0 !important;

            height: auto !important;

            overflow: visible !important;

            shape-rendering: crispEdges;
        }

        /*
        |--------------------------------------------------------------------------
        | Barcode number
        |--------------------------------------------------------------------------
        */

        .barcode-number {
            margin-top: 3mm;

            color: #000000;

            font-family: Arial, Helvetica, sans-serif;
            font-size: 15px;
            font-weight: 500;
            letter-spacing: 0;
            line-height: 1.2;

            text-align: center;
        }

        /*
        |--------------------------------------------------------------------------
        | Item meta
        |--------------------------------------------------------------------------
        */

        .item-meta {
            display: flex;
            width: 100%;
            justify-content: space-between;
            align-items: center;

            gap: 20mm;
            margin-top: 3mm;

            color: #000000;

            font-size: 11px;
            font-weight: 600;
            line-height: 1.2;
        }

        /*
        |--------------------------------------------------------------------------
        | Invalid barcode
        |--------------------------------------------------------------------------
        */

        .invalid-barcode {
            min-width: 300px;
            padding: 20px;

            color: #b91c1c;
            background: #fef2f2;
            border: 1px solid #fecaca;

            font-size: 13px;
            font-weight: 700;
            line-height: 1.5;
        }

        /*
        |--------------------------------------------------------------------------
        | Print mode
        |--------------------------------------------------------------------------
        */

        @media print {
            html,
            body {
                margin: 0;
                padding: 0;

                background: #ffffff !important;
            }

            .toolbar {
                display: none !important;
            }

            .sheet {
                display: flex;
                flex-wrap: wrap;

                gap: 4mm;
                padding: 0;
            }

            .label {
                width: max-content !important;
                min-width: max-content !important;

                padding: 5mm 10mm;

                overflow: visible !important;

                border: 0;
            }

            .barcode-quiet-zone {
                width: max-content !important;
                min-width: max-content !important;

                overflow: visible !important;
            }

            .barcode-quiet-zone svg {
                width: auto !important;
                max-width: none !important;
                height: auto !important;

                overflow: visible !important;
            }
        }
    </style>
</head>

<body>

{{-- ================= TOOLBAR ================= --}}

<div class="toolbar">

    <button
        type="button"
        class="print-button"
        onclick="window.print()"
    >
        Print Labels
    </button>

    <button
        type="button"
        class="close-button"
        onclick="window.close()"
    >
        Close
    </button>

    <div class="print-note">
        Print scale 100%, fit-to-page off, margins minimum.
    </div>

</div>

{{-- ================= LABEL SHEET ================= --}}

<div class="sheet">

    @foreach($items as $entry)

        @php
            $item = $entry['item'];

            $copies = max(
                1,
                (int) ($entry['quantity'] ?? 1)
            );

            $barcodeValue = strtoupper(
                trim((string) $item->barcode)
            );

            /*
             * Valid barcode format:
             *
             * ITM + exactly 15 numeric digits
             */
            $validBarcode = preg_match(
                '/^ITM[0-9]{15}$/',
                $barcodeValue
            );
        @endphp

        @for($copy = 0; $copy < $copies; $copy++)

            <div class="label">

                {{-- Item name --}}

                <div class="item-name">
                    {{ $item->name }}
                </div>

                {{-- Price name ke neeche --}}

                <div class="item-price">
                    Rs. {{ number_format((float) $item->price, 2) }}
                </div>

                @if($validBarcode)

                    {{--
                    |--------------------------------------------------------------------------
                    | Code 128 Auto
                    |--------------------------------------------------------------------------
                    |
                    | C128 use kiya gaya hai, C128B nahi.
                    |
                    | C128 automatically:
                    | - ITM letters ko Code Set B me encode karega.
                    | - Long numeric part ko compact Code Set C me encode karega.
                    |
                    | Bar width: 4
                    | Height: 150
                    |
                    | Isko CSS se compress nahi kiya gaya.
                    |
                    --}}

                    <div class="barcode-quiet-zone">
                        {!! DNS1D::getBarcodeSVG(
                            $barcodeValue,
                            'C128',
                            4,
                            150,
                            '000000',
                            false
                        ) !!}
                    </div>

                    {{-- Barcode text --}}

                    <div class="barcode-number">
                        {{ $barcodeValue }}
                    </div>

                @else

                    <div class="invalid-barcode">
                        Invalid or old barcode:
                        <br>
                        {{ $barcodeValue ?: 'Empty barcode' }}
                        <br><br>
                        Please regenerate this item's barcode.
                    </div>

                @endif

                {{-- Bottom information --}}

                <div class="item-meta">

                    <span>
                        {{ $item->sku ?: 'ITEM-' . $item->id }}
                    </span>

                    <strong>
                        Rs. {{ number_format((float) $item->price, 2) }}
                    </strong>

                </div>

            </div>

        @endfor

    @endforeach

</div>

{{-- ================= AUTO PRINT ================= --}}

@if($autoPrint)
    <script>
        window.addEventListener('load', function () {
            window.setTimeout(function () {
                window.print();
            }, 900);
        });
    </script>
@endif

</body>
</html>