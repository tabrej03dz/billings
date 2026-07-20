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
            margin: 5mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            background: #f3f4f6;
            font-family: Arial, Helvetica, sans-serif;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
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
            font-weight: 600;
        }

        .print-button {
            color: #ffffff;
            background: #16a34a;
        }

        .close-button {
            color: #111827;
            background: #e5e7eb;
        }

        .sheet {
            display: grid;
            grid-template-columns: repeat(auto-fill, 50mm);
            align-items: start;
            gap: 3mm;
            padding: 5mm;
        }

        .label {
            width: 50mm;
            min-height: 30mm;
            padding: 2mm;
            overflow: hidden;
            text-align: center;
            background: #ffffff;
            border: 1px dashed #9ca3af;
            page-break-inside: avoid;
        }

        .item-name {
            overflow: hidden;
            font-size: 10px;
            font-weight: 700;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .barcode {
            margin-top: 2px;
            line-height: 0;
        }

        .barcode svg {
            width: 45mm !important;
            height: 16mm !important;
        }

        .barcode-number {
            margin-top: 2px;
            font-size: 8px;
            letter-spacing: 0.5px;
        }

        .item-meta {
            display: flex;
            justify-content: space-between;
            gap: 4px;
            margin-top: 2px;
            font-size: 9px;
        }

        @media print {
            body {
                background: #ffffff;
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
</div>

<div class="sheet">

    @foreach($items as $entry)

        @for($copy = 0; $copy < $entry['quantity']; $copy++)

            @php
                $item = $entry['item'];
            @endphp

            <div class="label">

                <div class="item-name">
                    {{ $item->name }}
                </div>

                <div class="barcode">
                    {!! DNS1D::getBarcodeSVG(
                        $item->barcode,
                        'C128',
                        1.5,
                        44,
                        '000000',
                        false
                    ) !!}
                </div>

                <div class="barcode-number">
                    {{ $item->barcode }}
                </div>

                <div class="item-meta">
                    <span>
                        {{ $item->sku ?: 'ID-' . $item->id }}
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
            setTimeout(function () {
                window.print();
            }, 300);
        });
    </script>
@endif

</body>
</html>