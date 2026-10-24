
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Credit Note</title>

    <style>
        @page {
            margin: 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #263b49;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .heading {
            font-size: 23px;
            font-weight: bold;
            color: #263b49;
        }

        .subtitle {
            font-size: 10px;
            color: #64748b;
            margin-top: 5px;
        }

        .business {
            margin-top: 12px;
            font-size: 15px;
            font-weight: bold;
        }

        .details {
            width: 100%;
            margin-bottom: 15px;
        }

        .details td {
            width: 50%;
            vertical-align: top;
            padding: 5px 8px;
        }

        .label {
            color: #64748b;
            font-size: 9px;
        }

        .value {
            font-weight: bold;
            margin-top: 3px;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .items th {
            background: #263b49;
            color: #ffffff;
            padding: 9px 6px;
            text-align: left;
            font-size: 10px;
        }

        .items td {
            border-bottom: 1px solid #e5e7eb;
            padding: 9px 6px;
        }

        .right {
            text-align: right !important;
        }

        .center {
            text-align: center !important;
        }

        .summary {
            width: 45%;
            margin-left: auto;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .summary td {
            padding: 7px 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .grand-total {
            background: #e0f2f1;
            color: #134e4a;
            font-weight: bold;
            font-size: 13px;
        }

        .reason {
            margin-top: 25px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
        }

        .footer {
            margin-top: 45px;
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
            font-size: 9px;
            color: #64748b;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="heading">CREDIT NOTE</div>

        <div class="subtitle">
            Sales Return / Original Invoice Adjustment
        </div>

        <div class="business">
            {{ $salesReturn->business?->name ?? 'Business' }}
        </div>

        @if($salesReturn->business?->gstin)
            <div class="subtitle">
                GSTIN: {{ $salesReturn->business->gstin }}
            </div>
        @endif
    </div>

    <table class="details">
        <tr>
            <td>
                <div class="label">CREDIT NOTE NUMBER</div>
                <div class="value">
                    {{ $salesReturn->credit_note_number }}
                </div>
            </td>

            <td>
                <div class="label">CREDIT NOTE DATE</div>
                <div class="value">
                    {{ $salesReturn->credit_note_date
                        ? \Illuminate\Support\Carbon::parse($salesReturn->credit_note_date)->format('d-m-Y')
                        : '-' }}
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="label">ORIGINAL INVOICE NUMBER</div>
                <div class="value">
                    {{ $salesReturn->invoice?->invoice_number ?? '-' }}
                </div>
            </td>

            <td>
                <div class="label">ORIGINAL INVOICE DATE</div>
                <div class="value">
                    {{ $salesReturn->invoice?->invoice_date
                        ? \Illuminate\Support\Carbon::parse($salesReturn->invoice->invoice_date)->format('d-m-Y')
                        : '-' }}
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="label">CUSTOMER NAME</div>
                <div class="value">
                    {{ $salesReturn->client?->name ?? '-' }}
                </div>
            </td>

            <td>
                <div class="label">SALES RETURN REFERENCE</div>
                <div class="value">
                    {{ $salesReturn->return_number }}
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>ITEM</th>
                <th class="right">QTY</th>
                <th class="right">TAXABLE</th>
                <th class="right">GST</th>
                <th class="right">TOTAL</th>
            </tr>
        </thead>

        <tbody>
            @foreach($salesReturn->items as $row)
                @php
                    $gst = (float) $row->cgst_amount
                        + (float) $row->sgst_amount
                        + (float) $row->igst_amount;
                @endphp

                <tr>
                    <td>
                        {{ $row->invoiceItem?->description
                            ?: ($row->invoiceItem?->item?->name ?? 'Item') }}
                    </td>

                    <td class="right">
                        {{ number_format((float) $row->quantity, 3) }}
                    </td>

                    <td class="right">
                        {{ number_format((float) $row->taxable_amount, 2) }}
                    </td>

                    <td class="right">
                        {{ number_format($gst, 2) }}
                    </td>

                    <td class="right">
                        {{ number_format((float) $row->total_amount, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td>Taxable Amount</td>
            <td class="right">
                {{ number_format((float) $salesReturn->taxable_amount, 2) }}
            </td>
        </tr>

        <tr>
            <td>CGST</td>
            <td class="right">
                {{ number_format((float) $salesReturn->cgst_amount, 2) }}
            </td>
        </tr>

        <tr>
            <td>SGST</td>
            <td class="right">
                {{ number_format((float) $salesReturn->sgst_amount, 2) }}
            </td>
        </tr>

        <tr>
            <td>IGST</td>
            <td class="right">
                {{ number_format((float) $salesReturn->igst_amount, 2) }}
            </td>
        </tr>

        <tr class="grand-total">
            <td>TOTAL CREDIT NOTE</td>
            <td class="right">
                {{ number_format((float) $salesReturn->total_amount, 2) }}
            </td>
        </tr>
    </table>

    @if($salesReturn->reason)
        <div class="reason">
            <strong>Reason for Return:</strong>
            <div style="margin-top: 5px;">
                {{ $salesReturn->reason }}
            </div>
        </div>
    @endif

    <div class="footer">
        <div>
            Reference: {{ $salesReturn->return_number }}
        </div>

        <div style="margin-top: 5px;">
            This credit note refers to the original invoice mentioned above.
        </div>

        <div style="margin-top: 5px;">
            Refund settlement, if applicable, is recorded separately.
        </div>
    </div>

</body>
</html>
