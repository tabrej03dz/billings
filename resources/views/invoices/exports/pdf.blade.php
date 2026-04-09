<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
        }

        .header {
            margin-bottom: 16px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .meta {
            margin-bottom: 12px;
            line-height: 1.6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #cfcfcf;
            padding: 6px 8px;
            vertical-align: top;
        }

        th {
            background: #f2f2f2;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .small {
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $reportTitle }}</div>

        <div class="meta">
            <strong>Business:</strong> {{ $business->name ?? 'N/A' }}<br>
            <strong>Date Range:</strong> {{ $filters['from_date'] ?? '-' }} to {{ $filters['to_date'] ?? '-' }}<br>
            <strong>Status:</strong> {{ $filters['status'] ?: 'All' }}<br>
            <strong>Search:</strong> {{ $filters['search'] ?: '-' }}<br>
            <strong>Generated At:</strong> {{ now()->format('Y-m-d h:i A') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Invoice Date</th>
                <th>Invoice No.</th>
                <th>Client</th>
                <th>Mobile</th>
                <th>GSTIN</th>
                <th>PAN</th>
                <th>Total</th>
                <th>Received</th>
                <th>Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
                @php
                    $rowStatus = 'Unpaid';
                    if ((float) $row->balance <= 0) {
                        $rowStatus = 'Paid';
                    } elseif ((float) $row->received_amount > 0 && (float) $row->balance > 0) {
                        $rowStatus = 'Partial';
                    }
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($row->invoice_date)->format('Y-m-d') }}</td>
                    <td>{{ $row->invoice_number }}</td>
                    <td>{{ $row->client->name ?? '-' }}</td>
                    <td>{{ $row->client->mobile ?? '-' }}</td>
                    <td>{{ $row->client->gstin ?? '-' }}</td>
                    <td>{{ $row->client->pan ?? '-' }}</td>
                    <td class="right">{{ number_format((float) $row->total, 2) }}</td>
                    <td class="right">{{ number_format((float) $row->received_amount, 2) }}</td>
                    <td class="right">{{ number_format((float) $row->balance, 2) }}</td>
                    <td>{{ $rowStatus }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align:center;">No records found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="small" style="margin-top: 12px;">
        Total Records: {{ $rows->count() }}
    </div>
</body>
</html>