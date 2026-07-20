<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Print Barcodes</title>

    <style>
        @page {
            size: auto;
            margin: 3mm;
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

        .close-button {
            color: #111827;
            background: #e5e7eb;
        }

        .print-note {
            color: #4b5563;
            font-size: 12px;
        }

        /*
        |--------------------------------------------------------------------------
        | Label sheet
        |--------------------------------------------------------------------------
        |
        | Fixed 70mm/80mm width remove kar di gayi hai.
        | Har label barcode ki natural width ke according expand hoga.
        |
        */

        .sheet {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: flex-start;
            gap: 4mm;
            padding: 4mm;
        }

        .label {
            display: inline-flex;
            flex-direction: column;
            align-items: center;

            width: max-content;
            min-width: max-content;
            min-height: 40mm;

            padding: 4mm 8mm;

            overflow: visible;
            text-align: center;
            white-space: nowrap;

            background: #ffffff;
            border: 1px dashed #9ca3af;

            page-break-inside: avoid;
            break-inside: avoid;
        }

        .item-name {
            width: 100%;
            margin-bottom: 2mm;

            overflow: hidden;
            color: #000000;

            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;

            text-align: center;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /*
         * Quiet zone:
         * Left aur right me clear white space rakha gaya hai.
         */
        .barcode-wrapper {
            display: inline-block;
            width: max-content;
            min-width: max-content;

            padding: 0 6mm;

            overflow: visible;
            background: #ffffff;
        }

        .barcode {
            display: inline-block;
            width: max-content;
            min-width: max-content;

            overflow: visible;
            line-height: 0;

            background: #ffffff;
        }

        /*
         * Important:
         * SVG ki width ko CSS se set nahi kiya gaya.
         * Generated SVG apni original width me render hoga.
         */
        .barcode svg {
            display: block;

            width: auto !important;
            max-width: none !important;
            min-width: 0 !important;

            height: 24mm !important;

            overflow: visible !important;
            shape-rendering: crispEdges;
        }

        .barcode-number {
            margin-top: 2mm;

            color: #000000;
            font-family: "Courier New", Courier, monospace;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .item-meta {
            display: flex;
            width: 100%;
            justify-content: space-between;
            gap: 10mm;

            margin-top: 2mm;

            color: #000000;
            font-size: 10px;
            font-weight: 600;
            line-height: 1.2;
        }

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
                gap: 3mm;
                padding: 0;
            }

            .label {
                width: max-content !important;
                min-width: max-content !important;

                padding: 3mm 7mm;

                overflow: visible !important;
                border: 0;
            }

            .barcode-wrapper,
            .barcode {
                width: max-content !important;
                min-width: max-content !important;
                overflow: visible !important;
            }

            .barcode svg {
                width: auto !important;
                max-width: none !important;
                height: 24mm !important;
                overflow: visible !important;
            }
        }
    </style>
</head>

<body>

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
        Print scale 100%, fit-to-page off aur margins minimum rakhein.
    </div>
</div>

<div class="sheet">

    @foreach($items as $entry)

        @php
            $item = $entry['item'];

            $copies = max(
                1,
                (int) ($entry['quantity'] ?? 1)
            );

            $barcodeValue = trim(
                (string) $item->barcode
            );
        @endphp

        @for($copy = 0; $copy < $copies; $copy++)

            <div class="label">

                <div class="item-name">
                    {{ $item->name }}
                </div>

                <div class="barcode-wrapper">
                    <div class="barcode">
                        {!! DNS1D::getBarcodeSVG(
                            $barcodeValue,
                            'C128',
                            3,
                            90,
                            '000000',
                            false
                        ) !!}
                    </div>
                </div>

                <div class="barcode-number">
                    {{ $barcodeValue }}
                </div>

                <div class="item-meta">
                    <span>
                        {{ $item->sku ?: 'ITEM-' . $item->id }}
                    </span>

                    <strong>
                        ₹{{ number_format((float) $item->price, 2) }}
                    </strong>
                </div>

            </div>

        @endfor

    @endforeach

</div>

@if($autoPrint)
    <script>
        window.addEventListener('load', function () {
            window.setTimeout(function () {
                window.print();
            }, 700);
        });
    </script>
@endif

</body>
</html>