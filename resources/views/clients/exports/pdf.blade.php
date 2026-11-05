<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>{{ $reportTitle ?? 'Invoice Report' }}</title>

    <style>
        @page {
            margin: 18px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 7px;
            color: #222;
        }

        h1 {
            margin: 0 0 6px;
            text-align: center;
            font-size: 17px;
        }

        .generated {
            text-align: center;
            color: #666;
            margin-bottom: 12px;
        }

        .filters {
            border: 1px solid #ccc;
            padding: 8px;
            margin-bottom: 12px;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead {
            display: table-header-group;
        }

        th,
        td {
            border: 1px solid #bbb;
            padding: 4px 2px;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #e9eeee;
        }

        tr {
            page-break-inside: avoid;
        }

        .text-center {
            text-align: center;
        }

        .amount {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            background: #f0f0f0;
        }

        .footer {
            margin-top: 12px;
            text-align: right;
            color: #666;
            font-size: 8px;
        }
    </style>
</head>

<body>

<h1>{{ $reportTitle ?? 'Invoice Report' }}</h1>

<div class="generated">
    Generated: {{ now()->format('d-m-Y h:i A') }}
</div>

<div class="filters">
    <strong>From:</strong>
    {{ ($filters['from_date'] ?? '') ?: '-' }}

    &nbsp;&nbsp;

    <strong>To:</strong>
    {{ ($filters['to_date'] ?? '') ?: '-' }}

    &nbsp;&nbsp;

    <strong>Status:</strong>
    {{ ucfirst(($filters['status'] ?? '') ?: 'All') }}

    <br>

    <strong>Search:</strong>
    {{ ($filters['search'] ?? '') ?: '-' }}
</div>

<table>
    <thead>
        <tr>
            <th style="width: 3%;">#</th>
            <th style="width: 7%;">Date</th>
            <th style="width: 7%;">Invoice No.</th>
            <th style="width: 9%;">Client</th>
            <th style="width: 8%;">Mobile</th>
            <th style="width: 9%;">GSTIN</th>
            <th style="width: 7%;">PAN</th>
            <th style="width: 10%;">Address</th>
            <th style="width: 5%;">GST</th>
            <th style="width: 5%;">IGST</th>
            <th style="width: 5%;">CGST</th>
            <th style="width: 5%;">SGST</th>
            <th style="width: 5%;">Total</th>
            <th style="width: 5%;">Received</th>
            <th style="width: 5%;">Balance</th>
            <th style="width: 5%;">Status</th>
        </tr>
    </thead>

    <tbody>
        @forelse($rows as $index => $row)
            @php
                $balance = (float) $row->balance;
                $received = (float) $row->received_amount;

                $paymentStatus = 'Unpaid';

                if ($balance <= 0) {
                    $paymentStatus = 'Paid';
                } elseif ($received > 0) {
                    $paymentStatus = 'Partial';
                }
            @endphp

            <tr>
                <td class="text-center">
                    {{ $index + 1 }}
                </td>

                <td>
                    {{ $row->invoice_date
                        ? \Carbon\Carbon::parse($row->invoice_date)->format('d-m-Y')
                        : '-' }}
                </td>

                <td>{{ $row->invoice_number }}</td>

                <td>{{ optional($row->client)->name ?? '-' }}</td>

                <td>{{ optional($row->client)->mobile ?? '-' }}</td>

                <td>{{ optional($row->client)->gstin ?? '-' }}</td>

                <td>{{ optional($row->client)->pan ?? '-' }}</td>

                <td>{{ optional($row->client)->address ?? '-' }}</td>

                <td class="amount">
                    {{ number_format((float) $row->tax_amount, 2) }}
                </td>

                <td class="amount">
                    {{ number_format((float) $row->igst_amount, 2) }}
                </td>

                <td class="amount">
                    {{ number_format((float) $row->cgst_amount, 2) }}
                </td>

                <td class="amount">
                    {{ number_format((float) $row->sgst_amount, 2) }}
                </td>

                <td class="amount">
                    {{ number_format((float) $row->total, 2) }}
                </td>

                <td class="amount">
                    {{ number_format($received, 2) }}
                </td>

                <td class="amount">
                    {{ number_format($balance, 2) }}
                </td>

                <td class="text-center">
                    {{ $paymentStatus }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="16" class="text-center">
                    No invoices found for selected filters.
                </td>
            </tr>
        @endforelse

        @if($rows->count())
            <tr class="total-row">
                <td colspan="8">TOTAL</td>

                <td class="amount">
                    {{ number_format($rows->sum('tax_amount'), 2) }}
                </td>

                <td class="amount">
                    {{ number_format($rows->sum('igst_amount'), 2) }}
                </td>

                <td class="amount">
                    {{ number_format($rows->sum('cgst_amount'), 2) }}
                </td>

                <td class="amount">
                    {{ number_format($rows->sum('sgst_amount'), 2) }}
                </td>

                <td class="amount">
                    {{ number_format($rows->sum('total'), 2) }}
                </td>

                <td class="amount">
                    {{ number_format($rows->sum('received_amount'), 2) }}
                </td>

                <td class="amount">
                    {{ number_format($rows->sum('balance'), 2) }}
                </td>

                <td></td>
            </tr>
        @endif
    </tbody>
</table>

<div class="footer">
    Total Records: {{ $rows->count() }}
    <br>
    All amounts in INR.
</div>

</body>
</html>