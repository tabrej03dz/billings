@php
    $invoice  = $ewayBill->invoice;
    $business = $invoice->business;
    $client   = $invoice->client;

    $totalTaxable = $invoice->items->sum(function ($item) {
        return ($item->quantity ?? 0) * ($item->rate ?? 0);
    });

    $totalQty = $invoice->items->sum('quantity');
@endphp

<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">

    <title>
        E-Way Bill - {{ $ewayBill->eway_bill_no ?? $invoice->invoice_number }}
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        body {
            margin: 0;
            padding: 0;
            background: #f3f4f6;
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            font-size: 11px;
        }

        .toolbar {
            max-width: 210mm;
            margin: 12px auto;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .toolbar button,
        .toolbar a {
            border: 0;
            padding: 9px 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
        }

        .btn-print {
            background: #111827;
            color: #fff;
        }

        .btn-back {
            background: #4b5563;
            color: #fff;
        }

        .eway-page {
            width: 210mm;
            min-height: 297mm;
            margin: auto;
            background: #fff;
            padding: 8mm;
            box-shadow: 0 0 12px rgba(0,0,0,.10);
        }

        .title {
            text-align: center;
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: .4px;
        }

        .subtitle {
            text-align: center;
            font-size: 10px;
            margin-top: -5px;
            margin-bottom: 10px;
            color: #444;
        }

        .section-title {
            background: #f3f4f6;
            color: #111;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 7px;
            border: 1px solid #9ca3af;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            border: 1px solid #9ca3af;
            padding: 5px 6px;
            vertical-align: top;
        }

        .label {
            font-weight: 700;
            width: 15%;
            background: #f9fafb;
        }

        .value {
            width: 35%;
        }

        .address-grid td {
            width: 50%;
        }

        .address-heading {
            background: #f3f4f6;
            color: #111;
            font-size: 11px;
            font-weight: bold;
            padding: 4px 6px;
        }

        .address-box {
            line-height: 1.55;
            min-height: 92px;
        }

        .goods-table th {
            background: #f3f4f6;
            font-weight: 700;
            text-align: center;
            vertical-align: middle;
        }

        .goods-table td {
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: 700;
        }

        .total-row td {
            background: #f3f4f6;
            font-weight: 700;
        }

        .tax-summary td {
            text-align: center;
        }

        .tax-summary .head {
            font-weight: 700;
            background: #f3f4f6;
        }

        .status-generated {
            font-weight: 700;
            color: #111;
        }

        .status-cancelled {
            font-weight: 700;
            color: #b91c1c;
        }

        .ewb-number {
            font-size: 13px;
            font-weight: 800;
        }

        .footer-note {
            text-align: center;
            font-size: 9px;
            margin-top: 12px;
            color: #555;
        }

        @media print {

            body {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .eway-page {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
                min-height: auto;
            }
        }

    </style>

</head>

<body>


<div class="toolbar">

    <a
        href="{{ route('eway-bills.show', $ewayBill->id) }}"
        class="btn-back"
    >
        Back
    </a>

    <button
        type="button"
        onclick="window.print()"
        class="btn-print"
    >
        Print E-Way Bill
    </button>

</div>


<div class="eway-page">

    <div class="title">
        E-WAY BILL
    </div>

    <div class="subtitle">
        E-Way Bill Details
    </div>


    {{-- 1. E-WAY BILL DETAILS --}}
    <div class="section-title">
        1. E-Way Bill Details
    </div>

    <table>

        <tr>

            <td class="label">
                E-Way Bill No.
            </td>

            <td class="value ewb-number">
                {{ $ewayBill->eway_bill_no ?: '-' }}
            </td>

            <td class="label">
                Generated Date
            </td>

            <td class="value">
                {{ $ewayBill->eway_bill_date
                    ? $ewayBill->eway_bill_date->format('d/m/Y h:i A')
                    : '-' }}
            </td>

        </tr>


        <tr>

            <td class="label">
                Status
            </td>

            <td class="value">

                <span class="{{ $ewayBill->status === 'cancelled'
                    ? 'status-cancelled'
                    : 'status-generated' }}">

                    {{ strtoupper($ewayBill->status ?? 'generated') }}

                </span>

            </td>

            <td class="label">
                Valid Upto
            </td>

            <td class="value">

                {{ $ewayBill->valid_upto
                    ? $ewayBill->valid_upto->format('d/m/Y h:i A')
                    : '-' }}

            </td>

        </tr>


        <tr>

            <td class="label">
                Supply Type
            </td>

            <td class="value">
                {{ $ewayBill->supply_type ?: '-' }}

                @if($ewayBill->sub_supply_type)
                    - {{ $ewayBill->sub_supply_type }}
                @endif
            </td>

            <td class="label">
                Document Type
            </td>

            <td class="value">
                {{ $ewayBill->document_type ?: 'Tax Invoice' }}
            </td>

        </tr>


        <tr>

            <td class="label">
                Document No.
            </td>

            <td class="value">
                {{ $ewayBill->document_no ?: $invoice->invoice_number }}
            </td>

            <td class="label">
                Document Date
            </td>

            <td class="value">

                {{ $ewayBill->document_date
                    ? $ewayBill->document_date->format('d/m/Y')
                    : optional($invoice->invoice_date)->format('d/m/Y') }}

            </td>

        </tr>

    </table>


    {{-- 2. ADDRESS DETAILS --}}
    <div class="section-title">
        2. Address Details
    </div>

    <table class="address-grid">

        <tr>

            <td class="address-heading">
                From
            </td>

            <td class="address-heading">
                To
            </td>

        </tr>


        <tr>

            <td class="address-box">

                <strong>GSTIN:</strong>
                {{ $ewayBill->from_gstin ?: '-' }}

                <br>

                <strong>
                    {{ $ewayBill->from_name ?: ($business->name ?? '-') }}
                </strong>

                <br>

                {{ $ewayBill->from_address ?: '-' }}

                <br>

                {{ $ewayBill->from_place ?: '-' }}

                @if($ewayBill->from_pincode)
                    - {{ $ewayBill->from_pincode }}
                @endif

                <br>

                <strong>State:</strong>
                {{ $ewayBill->from_state ?: '-' }}

                @if($ewayBill->from_state_code)
                    ({{ $ewayBill->from_state_code }})
                @endif

                <br><br>

                <strong>Dispatch From:</strong>
                {{ $ewayBill->from_place ?: '-' }}

            </td>


            <td class="address-box">

                <strong>GSTIN:</strong>
                {{ $ewayBill->to_gstin ?: '-' }}

                <br>

                <strong>
                    {{ $ewayBill->to_name ?: ($client->name ?? '-') }}
                </strong>

                <br>

                {{ $ewayBill->to_address ?: '-' }}

                <br>

                {{ $ewayBill->to_place ?: '-' }}

                @if($ewayBill->to_pincode)
                    - {{ $ewayBill->to_pincode }}
                @endif

                <br>

                <strong>State:</strong>
                {{ $ewayBill->to_state ?: '-' }}

                @if($ewayBill->to_state_code)
                    ({{ $ewayBill->to_state_code }})
                @endif

                <br><br>

                <strong>Ship To:</strong>
                {{ $ewayBill->to_place ?: '-' }}

            </td>

        </tr>

    </table>


    {{-- 3. GOODS DETAILS --}}
    <div class="section-title">
        3. Goods Details
    </div>

    <table class="goods-table">

        <thead>

        <tr>

            <th style="width:5%;">
                #
            </th>

            <th style="width:12%;">
                HSN Code
            </th>

            <th>
                Product Name & Description
            </th>

            <th style="width:9%;">
                Qty
            </th>

            <th style="width:12%;">
                Rate
            </th>

            <th style="width:10%;">
                Tax Rate
            </th>

            <th style="width:14%;">
                Amount
            </th>

        </tr>

        </thead>


        <tbody>

        @foreach($invoice->items as $index => $item)

            <tr>

                <td class="text-center">
                    {{ $index + 1 }}
                </td>

                <td class="text-center">
                    {{ $item->sac_code ?: '-' }}
                </td>

                <td>
                    {{ $item->description ?: '-' }}
                </td>

                <td class="text-center">
                    {{ number_format((float)($item->quantity ?? 0), 2) }}
                </td>

                <td class="text-right">
                    ₹{{ number_format((float)($item->rate ?? 0), 2) }}
                </td>

                <td class="text-center">
                    {{ number_format((float)($item->tax_percent ?? 0), 2) }}%
                </td>

                <td class="text-right font-bold">
                    ₹{{ number_format((float)($item->amount ?? 0), 2) }}
                </td>

            </tr>

        @endforeach


        <tr class="total-row">

            <td colspan="3" class="text-right">
                Total
            </td>

            <td class="text-center">
                {{ number_format((float)$totalQty, 2) }}
            </td>

            <td></td>

            <td></td>

            <td class="text-right">
                ₹{{ number_format((float)($invoice->total ?? 0), 2) }}
            </td>

        </tr>

        </tbody>

    </table>


    {{-- TAX SUMMARY --}}
    <table class="tax-summary">

        <tr>

            <td class="head">
                Taxable Amount
            </td>

            <td class="head">
                CGST
            </td>

            <td class="head">
                SGST
            </td>

            <td class="head">
                IGST
            </td>

            <td class="head">
                Other
            </td>

            <td class="head">
                Total Invoice Amount
            </td>

        </tr>


        <tr>

            <td>
                ₹{{ number_format(
                    (float)($invoice->subtotal ?? $totalTaxable),
                    2
                ) }}
            </td>

            <td>
                ₹{{ number_format(
                    (float)($invoice->cgst_amount ?? 0),
                    2
                ) }}
            </td>

            <td>
                ₹{{ number_format(
                    (float)($invoice->sgst_amount ?? 0),
                    2
                ) }}
            </td>

            <td>
                ₹{{ number_format(
                    (float)($invoice->igst_amount ?? 0),
                    2
                ) }}
            </td>

            <td>
                ₹0.00
            </td>

            <td class="font-bold">
                ₹{{ number_format(
                    (float)($invoice->total ?? 0),
                    2
                ) }}
            </td>

        </tr>

    </table>


    {{-- 4. TRANSPORTATION DETAILS --}}
    <div class="section-title">
        4. Transportation Details
    </div>

    <table>

        <tr>

            <td class="label">
                Transport Mode
            </td>

            <td class="value">
                {{ $ewayBill->transport_mode ?: '-' }}
            </td>

            <td class="label">
                Distance
            </td>

            <td class="value">

                @if($ewayBill->distance)
                    {{ number_format($ewayBill->distance) }} KM
                @else
                    -
                @endif

            </td>

        </tr>


        <tr>

            <td class="label">
                Transporter Name
            </td>

            <td class="value">
                {{ $ewayBill->transporter_name ?: '-' }}
            </td>

            <td class="label">
                Transporter ID / GSTIN
            </td>

            <td class="value">
                {{ $ewayBill->transporter_id ?: '-' }}
            </td>

        </tr>


        <tr>

            <td class="label">
                Transport Doc No.
            </td>

            <td class="value">
                {{ $ewayBill->transport_doc_no ?: '-' }}
            </td>

            <td class="label">
                Transport Doc Date
            </td>

            <td class="value">

                {{ $ewayBill->transport_doc_date
                    ? $ewayBill->transport_doc_date->format('d/m/Y')
                    : '-' }}

            </td>

        </tr>

    </table>


    {{-- 5. VEHICLE DETAILS --}}
    <div class="section-title">
        5. Vehicle Details
    </div>

    <table class="goods-table">

        <thead>

        <tr>

            <th>
                Mode
            </th>

            <th>
                Vehicle / Transport Doc.
            </th>

            <th>
                From
            </th>

            <th>
                Vehicle Type
            </th>

            <th>
                Entered By
            </th>

        </tr>

        </thead>

        <tbody>

        <tr>

            <td class="text-center">
                {{ $ewayBill->transport_mode ?: '-' }}
            </td>

            <td class="text-center font-bold">
                {{ $ewayBill->vehicle_no ?: '-' }}
            </td>

            <td class="text-center">
                {{ $ewayBill->from_place ?: '-' }}
            </td>

            <td class="text-center">
                {{ $ewayBill->vehicle_type ?: '-' }}
            </td>

            <td class="text-center">
                {{ $business->name ?? '-' }}
            </td>

        </tr>

        </tbody>

    </table>


    <div class="footer-note">
        This E-Way Bill has been generated from the billing software
        for Invoice
        <strong>{{ $invoice->invoice_number }}</strong>.
    </div>

</div>

</body>
</html>