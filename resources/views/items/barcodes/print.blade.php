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
            border-radius: 7px;
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
        */

        .sheet {
            display: grid;
            grid-template-columns: repeat(auto-fill, 70mm);
            align-items: start;
            justify-content: start;
            gap: 3mm;
            padding: 3mm;
        }

        .label {
            width: 70mm;
            min-height: 38mm;
            padding: 3mm 4mm;
            overflow: visible;
            text-align: center;
            background: #ffffff;
            border: 1px dashed #9ca3af;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .item-name {
            width: 100%;
            margin-bottom: 2mm;
            overflow: hidden;
            color: #000000;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /*
         * Quiet zone:
         * Barcode ke left aur right me white space hona zaruri hai.
         */
        .barcode-quiet-zone {
            display: flex;
            width: 100%;
            min-height: 20mm;
            align-items: center;
            justify-content: center;
            padding: 0 5mm;
            overflow: visible;
            background: #ffffff;
        }

        /*
         * Barcode PNG ko CSS se stretch/compress nahi karenge.
         * Native generated dimensions preserve rahengi.
         */
        .barcode-image {
            display: block;
            width: auto;
            max-width: 58mm;
            height: 19mm;
            object-fit: contain;
            image-rendering: crisp-edges;
            image-rendering: pixelated;
        }

        .barcode-number {
            margin-top: 1.5mm;
            color: #000000;
            font-family: "Courier New", Courier, monospace;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            line-height: 1.2;
        }

        .item-meta {
            display: flex;
            justify-content: space-between;
            gap: 5px;
            margin-top: 1.5mm;
            color: #000000;
            font-size: 9px;
            font-weight: 600;
            line-height: 1.2;
        }

        @media print {
            html,
            body {
                background: #ffffff !important;
            }

            .toolbar {
                display: none !important;
            }

            .sheet {
                gap: 2mm;
                padding: 0;
            }

            .label {
                border: 0;
            }

            .barcode-image {
                width: auto !important;
                max-width: 58mm !important;
                height: 19mm !important;
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
        Print scale 100%, margins none aur fit-to-page off rakhein.
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

            $barcodeValue = preg_replace(
                '/\D/',
                '',
                trim((string) $item->barcode)
            );

            /*
             * C128C ko even number of digits chahiye.
             */
            if (strlen($barcodeValue) % 2 !== 0) {
                $barcodeValue = '0' . $barcodeValue;
            }

            /*
             * PNG barcode:
             * Width factor = 3
             * Height       = 90 pixels
             *
             * PNG raster output browser SVG scaling issue se bachta hai.
             */
            $barcodePng = DNS1D::getBarcodePNG(
                $barcodeValue,
                'C128C',
                3,
                90,
                [0, 0, 0],
                true
            );
        @endphp

        @for($copy = 0; $copy < $copies; $copy++)

            <div class="label">

                <div class="item-name">
                    {{ $item->name }}
                </div>

                <div class="barcode-quiet-zone">
                    <img
                        src="data:image/png;base64,{{ $barcodePng }}"
                        alt="Barcode {{ $barcodeValue }}"
                        class="barcode-image"
                    >
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