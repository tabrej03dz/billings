<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Client Purchase Report</title>

    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
        }

        h1 {
            margin: 0;
            font-size: 20px;
            text-align: center;
        }

        h3 {
            margin-bottom: 6px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .muted {
            color: #666;
        }

        .client-box,
        .filter-box {
            margin-top: 15px;
            border: 1px solid #ccc;
            padding: 10px;
        }

        .summary-table,
        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table {
            margin-top: 15px;
        }

        .summary-table td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        .report-table {
            margin-top: 15px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #bbb;
            padding: 6px;
        }

        .report-table th {
            background: #e9eeee;
        }

        .total-row {
            font-weight: bold;
            background: #f0f0f0;
        }

        .filter-item {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 5px;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #666;
        }
    </style>

</head>

<body>

<h1>Client Purchase Report</h1>

<div class="text-center muted">
    Generated:
    {{ now()->format('d-m-Y h:i A') }}
</div>


{{-- Client --}}
<div class="client-box">

    <strong>Client:</strong>
    {{ $client->name }}

    &nbsp;&nbsp;

    <strong>Mobile:</strong>
    {{ $client->mobile ?? '-' }}

    &nbsp;&nbsp;

    <strong>GSTIN:</strong>
    {{ $client->gstin ?? '-' }}

    <br><br>

    <strong>PAN:</strong>
    {{ $client->pan ?? '-' }}

    &nbsp;&nbsp;

    <strong>State:</strong>
    {{ $client->state ?? '-' }}

    @if($client->address)
        <br><br>

        <strong>Address:</strong>
        {{ $client->address }}
    @endif

</div>


{{-- Filters --}}
<div class="filter-box">

    <strong>Applied Filters:</strong>

    <br><br>

    <span class="filter-item">
        Period:
        {{ $filters['period'] ?? 'All Time' }}
    </span>

    <span class="filter-item">
        From:
        {{ $filters['date_from'] ?? '-' }}
    </span>

    <span class="filter-item">
        To:
        {{ $filters['date_to'] ?? '-' }}
    </span>

    <span class="filter-item">
        Invoice:
        {{ $filters['invoice_no'] ?? 'All' }}
    </span>

    <span class="filter-item">
        Item:
        {{ $filters['item_search'] ?? 'All' }}
    </span>

    <span class="filter-item">
        Payment:
        {{ $filters['payment_status'] ?? 'All' }}
    </span>

</div>


{{-- Summary --}}
<table class="summary-table">

    <tr>

        <td>
            <strong>Total Invoices</strong><br>
            {{ $summary['total_invoices'] }}
        </td>

        <td>
            <strong>Subtotal</strong><br>
            Rs. {{ number_format($summary['total_subtotal'], 2) }}
        </td>

        <td>
            <strong>Tax</strong><br>
            Rs. {{ number_format($summary['total_tax'], 2) }}
        </td>

    </tr>

    <tr>

        <td>
            <strong>Total Purchase</strong><br>
            Rs. {{ number_format($summary['total_amount'], 2) }}
        </td>

        <td>
            <strong>Amount Received</strong><br>
            Rs. {{ number_format($summary['total_received'], 2) }}
        </td>

        <td>
            <strong>Balance Pending</strong><br>
            Rs. {{ number_format($summary['total_balance'], 2) }}
        </td>

    </tr>

</table>


{{-- Invoice table --}}
<table class="report-table">

    <thead>
    <tr>
        <th>#</th>
        <th>Date</th>
        <th>Invoice</th>
        <th>Items</th>
        <th>Subtotal</th>
        <th>Tax</th>
        <th>Total</th>
        <th>Received</th>
        <th>Balance</th>
    </tr>
    </thead>

    <tbody>

    @forelse($invoices as $index => $invoice)

        <tr>

            <td class="text-center">
                {{ $index + 1 }}
            </td>

            <td>
                {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') }}
            </td>

            <td>
                {{ $invoice->invoice_prefix }}{{ $invoice->invoice_number }}
            </td>

            <td class="text-center">
                {{ $invoice->items_count }}
            </td>

            <td class="text-right">
                {{ number_format($invoice->subtotal, 2) }}
            </td>

            <td class="text-right">
                {{ number_format($invoice->tax_amount, 2) }}
            </td>

            <td class="text-right">
                {{ number_format($invoice->total, 2) }}
            </td>

            <td class="text-right">
                {{ number_format($invoice->received_amount, 2) }}
            </td>

            <td class="text-right">
                {{ number_format($invoice->balance, 2) }}
            </td>

        </tr>

    @empty

        <tr>
            <td colspan="9" class="text-center">
                No invoices found for selected filters.
            </td>
        </tr>

    @endforelse

    @if($invoices->count())

        <tr class="total-row">

            <td colspan="4">
                TOTAL
            </td>

            <td class="text-right">
                {{ number_format($summary['total_subtotal'], 2) }}
            </td>

            <td class="text-right">
                {{ number_format($summary['total_tax'], 2) }}
            </td>

            <td class="text-right">
                {{ number_format($summary['total_amount'], 2) }}
            </td>

            <td class="text-right">
                {{ number_format($summary['total_received'], 2) }}
            </td>

            <td class="text-right">
                {{ number_format($summary['total_balance'], 2) }}
            </td>

        </tr>

    @endif

    </tbody>

</table>


<div class="footer">

    Total Records:
    {{ $summary['total_invoices'] }}

    <br>

    Report generated on
    {{ now()->format('d-m-Y h:i A') }}

</div>

</body>
</html>