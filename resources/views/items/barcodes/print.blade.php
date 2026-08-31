<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Barcode Labels</title>

    <style>
        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | Jewellery tag assumed size:
        |
        | Total Width  : 75mm
        | Total Height : 13mm
        |
        | Printed Part : 46mm
        | Blank Tail   : 29mm
        |
        | Printer ke actual sticker size ke hisaab se
        | sirf yahi dimensions later adjust karna.
        |
        */

        @page {
            size: 75mm 13mm;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;

            background: #ffffff;
            color: #000000;

            font-family: Arial, Helvetica, sans-serif;
        }


        /* =========================================================
           SCREEN TOOLBAR
        ========================================================= */

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 999;

            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;

            padding: 12px;

            background: #f8fafc;
            border-bottom: 1px solid #cbd5e1;
        }

        .toolbar button {
            border: 0;
            border-radius: 8px;

            padding: 9px 16px;

            font-size: 13px;
            font-weight: 700;

            cursor: pointer;
        }

        .print-btn {
            color: #ffffff;
            background: #16a34a;
        }

        .print-btn:hover {
            background: #15803d;
        }

        .close-btn {
            color: #111827;
            background: #e2e8f0;
        }

        .close-btn:hover {
            background: #cbd5e1;
        }

        .note {
            font-size: 12px;
            color: #64748b;
        }


        /* =========================================================
           SCREEN SHEET
        ========================================================= */

        .sheet {
            display: flex;
            flex-direction: column;
            align-items: flex-start;

            gap: 4mm;

            padding: 12px;

            background: #6b7075;
        }


        /* =========================================================
           ONE COMPLETE TAG
        ========================================================= */

        .tag {
            position: relative;

            display: flex;

            width: 75mm;
            height: 13mm;

            margin: 0;

            overflow: hidden;

            background: #ffffff;

            page-break-inside: avoid;
            break-inside: avoid;
        }


        /* =========================================================
           PRINTED PART
        ========================================================= */

        .print-part {
            display: flex;
            align-items: center;

            width: 46mm;
            height: 13mm;

            padding:
                0.55mm
                0.7mm
                0.45mm
                0.8mm;

            overflow: hidden;

            background: #ffffff;
        }


        /* =========================================================
           LEFT INFORMATION
        ========================================================= */

        .info {
            flex: 0 0 16mm;

            width: 16mm;
            height: 11.7mm;

            display: flex;
            flex-direction: column;
            justify-content: center;

            padding-right: 0.6mm;

            overflow: hidden;

            color: #000000;

            line-height: 1;
        }

        .mrp {
            width: 100%;

            overflow: hidden;

            font-size: 5.2pt;
            font-weight: 800;

            line-height: 1.05;

            white-space: nowrap;
        }

        .tax {
            width: 100%;

            margin-top: 0.22mm;

            overflow: hidden;

            font-size: 3.75pt;
            font-weight: 500;

            line-height: 1;

            white-space: nowrap;
        }

        .brand {
            width: 100%;

            margin-top: 0.55mm;

            overflow: hidden;

            font-size: 4.65pt;
            font-weight: 800;

            line-height: 1;

            white-space: nowrap;
        }

        .model {
            width: 100%;

            margin-top: 0.38mm;

            overflow: hidden;

            font-size: 3.65pt;
            font-weight: 500;

            line-height: 1;

            white-space: nowrap;
        }


        /* =========================================================
           BARCODE SIDE
        ========================================================= */

        .barcode-side {
            flex: 0 0 30mm;

            width: 30mm;
            height: 11.8mm;

            display: flex;
            flex-direction: column;

            align-items: center;
            justify-content: center;

            overflow: hidden;
        }


        /*
        |--------------------------------------------------------------------------
        | Barcode quiet zone
        |--------------------------------------------------------------------------
        |
        | Scanner ke liye left/right blank space important hai.
        |
        */

        .barcode-wrap {
            display: flex;

            align-items: center;
            justify-content: center;

            width: 29mm;
            height: 7.4mm;

            padding-left: 1.4mm;
            padding-right: 1.4mm;

            overflow: hidden;

            background: #ffffff;

            line-height: 0;
        }


        /*
        |--------------------------------------------------------------------------
        | Barcode SVG
        |--------------------------------------------------------------------------
        |
        | Vertical height control karenge.
        |
        | Horizontal width ko unnecessarily stretch nahi karenge.
        |
        */

        .barcode-wrap svg {
            display: block;

            width: auto !important;
            max-width: 26mm !important;

            height: 6.8mm !important;
            max-height: 6.8mm !important;

            overflow: visible !important;

            shape-rendering: crispEdges;
        }


        .barcode-number {
            width: 29mm;

            margin-top: 0.32mm;

            overflow: hidden;

            color: #000000;

            font-family: Arial, Helvetica, sans-serif;

            font-size: 4pt;
            font-weight: 700;

            line-height: 1;

            text-align: center;

            white-space: nowrap;
        }


        /* =========================================================
           INVALID / EMPTY BARCODE
        ========================================================= */

        .barcode-error {
            display: flex;

            align-items: center;
            justify-content: center;

            width: 27mm;
            height: 8mm;

            padding: 1mm;

            color: #b91c1c;

            font-size: 4pt;
            font-weight: 700;

            line-height: 1.2;

            text-align: center;
        }


        /* =========================================================
           BLANK JEWELLERY TAIL
        ========================================================= */

        .tail {
            flex: 0 0 29mm;

            width: 29mm;
            height: 13mm;

            background: #ffffff;
        }


        /* =========================================================
           SCREEN PREVIEW ONLY
        ========================================================= */

        @media screen {

            .tag {
                box-shadow:
                    0 2px 8px rgba(0, 0, 0, .35);
            }

            .print-part {
                border-right:
                    1px dashed #d1d5db;
            }
        }


        /* =========================================================
           ACTUAL PRINT
        ========================================================= */

        @media print {

            html,
            body {
                width: 75mm !important;

                margin: 0 !important;
                padding: 0 !important;

                background: #ffffff !important;
            }

            .toolbar {
                display: none !important;
            }

            .sheet {
                display: block;

                margin: 0 !important;
                padding: 0 !important;

                background: #ffffff !important;
            }

            .tag {
                width: 75mm !important;
                height: 13mm !important;

                margin: 0 !important;
                padding: 0 !important;

                box-shadow: none !important;

                page-break-after: always;
                break-after: page;
            }

            .tag:last-child {
                page-break-after: auto;
                break-after: auto;
            }

            .print-part {
                border: 0 !important;
            }
        }
    </style>
</head>

<body>


{{-- =========================================================
     TOOLBAR
========================================================= --}}

<div class="toolbar">

    <button
        type="button"
        class="print-btn"
        onclick="window.print()"
    >
        🖨 Print Labels
    </button>


    <button
        type="button"
        class="close-btn"
        onclick="window.close()"
    >
        Close
    </button>


    <span class="note">
        Scale 100% • Margins None • Fit to page OFF
    </span>

</div>


{{-- =========================================================
     LABEL SHEET
========================================================= --}}

<div class="sheet">

@foreach($items as $entry)

    @php

        /*
        |--------------------------------------------------------------------------
        | Item
        |--------------------------------------------------------------------------
        */

        $item = $entry['item'];


        /*
        |--------------------------------------------------------------------------
        | Copies
        |--------------------------------------------------------------------------
        */

        $copies = max(
            1,
            min(
                200,
                (int) ($entry['quantity'] ?? 1)
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Barcode
        |--------------------------------------------------------------------------
        */

        $barcodeValue = trim(
            (string) ($item->barcode ?? '')
        );


        /*
        |--------------------------------------------------------------------------
        | Numeric barcode check
        |--------------------------------------------------------------------------
        |
        | New recommended format:
        |
        | 12 numeric digits
        |
        | Example:
        | 381482901245
        |
        */

        $is12DigitNumeric =
            preg_match(
                '/^[0-9]{12}$/',
                $barcodeValue
            ) === 1;


        /*
        |--------------------------------------------------------------------------
        | Other Code128-compatible barcode
        |--------------------------------------------------------------------------
        */

        $hasBarcode =
            $barcodeValue !== '';


        /*
        |--------------------------------------------------------------------------
        | Brand
        |--------------------------------------------------------------------------
        */

        $brand = trim(
            (string) (
                $item->brand
                ?? $item->name
                ?? ''
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Model / SKU
        |--------------------------------------------------------------------------
        */

        $model = trim(
            (string) (
                $item->model_no
                ?? $item->sku
                ?? ''
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Price
        |--------------------------------------------------------------------------
        */

        $price = (float) (
            $item->price
            ?? 0
        );

    @endphp


    @for($copy = 0; $copy < $copies; $copy++)


        <div class="tag">


            {{-- =================================================
                 PRINTABLE AREA
            ================================================== --}}

            <div class="print-part">


                {{-- =============================================
                     LEFT SIDE
                ============================================== --}}

                <div class="info">


                    <div class="mrp">

                        MRP:
                        ₹{{ number_format(
                            $price,
                            0
                        ) }}/-

                    </div>


                    <div class="tax">
                        Incl. of all Taxes
                    </div>


                    <div class="brand">

                        BRAND:

                        {{
                            \Illuminate\Support\Str::limit(
                                strtoupper($brand),
                                9,
                                ''
                            )
                        }}

                    </div>


                    @if($model !== '')

                        <div class="model">

                            M NO:

                            {{
                                \Illuminate\Support\Str::limit(
                                    strtoupper($model),
                                    8,
                                    ''
                                )
                            }}

                        </div>

                    @endif


                </div>


                {{-- =============================================
                     RIGHT BARCODE
                ============================================== --}}

                <div class="barcode-side">


                    @if($hasBarcode)


                        {{-- =====================================
                             12 DIGIT NUMERIC
                        ====================================== --}}

                        @if($is12DigitNumeric)


                            <div class="barcode-wrap">

                                {!! DNS1D::getBarcodeSVG(
                                    $barcodeValue,
                                    'C128C',
                                    1.15,
                                    34,
                                    '000000',
                                    false
                                ) !!}

                            </div>


                            <div class="barcode-number">
                                {{ $barcodeValue }}
                            </div>


                        @else


                            {{--
                            |--------------------------------------------------------------------------
                            | OLD / ALPHANUMERIC BARCODE
                            |--------------------------------------------------------------------------
                            |
                            | Existing ITM barcode delete nahi kar rahe.
                            |
                            | C128 fallback use hoga.
                            |
                            --}}


                            <div class="barcode-wrap">

                                {!! DNS1D::getBarcodeSVG(
                                    $barcodeValue,
                                    'C128',
                                    0.85,
                                    34,
                                    '000000',
                                    false
                                ) !!}

                            </div>


                            <div class="barcode-number">
                                {{ $barcodeValue }}
                            </div>


                        @endif


                    @else


                        <div class="barcode-error">

                            Barcode
                            <br>
                            Not Available

                        </div>


                    @endif


                </div>


            </div>


            {{-- =================================================
                 BLANK TAIL
            ================================================== --}}

            <div class="tail"></div>


        </div>


    @endfor


@endforeach

</div>


{{-- =========================================================
     AUTO PRINT
========================================================= --}}

@if(!empty($autoPrint))

    <script>

        window.addEventListener(
            'load',
            function () {

                window.setTimeout(
                    function () {

                        window.print();

                    },
                    600
                );

            }
        );

    </script>

@endif


</body>
</html>