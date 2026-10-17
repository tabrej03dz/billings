<table>

    {{-- Report heading --}}
    <tr>
        <th colspan="9"
            style="font-size:18px;font-weight:bold;text-align:center;">
            Client Purchase Report
        </th>
    </tr>

    <tr>
        <td colspan="9"></td>
    </tr>

    {{-- Client details --}}
    <tr>
        <th>Client Name</th>
        <td>{{ $client->name }}</td>

        <th>Mobile</th>
        <td>{{ $client->mobile ?? '-' }}</td>

        <th>GSTIN</th>
        <td>{{ $client->gstin ?? '-' }}</td>
    </tr>

    <tr>
        <th>PAN</th>
        <td>{{ $client->pan ?? '-' }}</td>

        <th>State</th>
        <td>{{ $client->state ?? '-' }}</td>

        <th>Report Generated</th>
        <td>{{ now()->format('d-m-Y h:i A') }}</td>
    </tr>

    <tr>
        <td colspan="9"></td>
    </tr>

    {{-- Applied filters --}}
    <tr>
        <th colspan="9"
            style="font-size:14px;font-weight:bold;">
            Applied Filters
        </th>
    </tr>

    <tr>
        <th>Period</th>
        <td>{{ $filters['period'] ?? 'All Time' }}</td>

        <th>From Date</th>
        <td>{{ $filters['date_from'] ?? '-' }}</td>

        <th>To Date</th>
        <td>{{ $filters['date_to'] ?? '-' }}</td>

        <th>Payment Status</th>
        <td>{{ $filters['payment_status'] ?? 'All' }}</td>
    </tr>

    <tr>
        <th>Invoice Search</th>
        <td>{{ $filters['invoice_no'] ?? '-' }}</td>

        <th>Item Search</th>
        <td>{{ $filters['item_search'] ?? '-' }}</td>

        <th>Sort</th>
        <td colspan="3">
            {{ $filters['sort_by'] ?? 'invoice_date' }}
            -
            {{ strtoupper($filters['sort_direction'] ?? 'desc') }}
        </td>
    </tr>

    <tr>
        <td colspan="9"></td>
    </tr>

    {{-- Summary --}}
    <tr>
        <th colspan="9"
            style="font-size:14px;font-weight:bold;">
            Summary
        </th>
    </tr>

    <tr>
        <th>Total Invoices</th>
        <td>{{ $summary['total_invoices'] }}</td>

        <th>Subtotal</th>
        <td>{{ number_format($summary['total_subtotal'], 2) }}</td>

        <th>Tax</th>
        <td>{{ number_format($summary['total_tax'], 2) }}</td>

        <th>Total</th>
        <td>{{ number_format($summary['total_amount'], 2) }}</td>
    </tr>

    <tr>
        <th>Received</th>
        <td>{{ number_format($summary['total_received'], 2) }}</td>

        <th>Balance</th>
        <td>{{ number_format($summary['total_balance'], 2) }}</td>
    </tr>

    <tr>
        <td colspan="9"></td>
    </tr>

    {{-- Invoice table --}}
    <thead>
    <tr style="font-weight:bold;">
        <th>#</th>
        <th>Date</th>
        <th>Invoice No.</th>
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
            <td>{{ $index + 1 }}</td>

            <td>
                {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') }}
            </td>

            <td>
                {{ $invoice->invoice_prefix }}{{ $invoice->invoice_number }}
            </td>

            <td>
                {{ $invoice->items_count }}
            </td>

            <td>
                {{ number_format($invoice->subtotal, 2, '.', '') }}
            </td>

            <td>
                {{ number_format($invoice->tax_amount, 2, '.', '') }}
            </td>

            <td>
                {{ number_format($invoice->total, 2, '.', '') }}
            </td>

            <td>
                {{ number_format($invoice->received_amount, 2, '.', '') }}
            </td>

            <td>
                {{ number_format($invoice->balance, 2, '.', '') }}
            </td>
        </tr>

    @empty

        <tr>
            <td colspan="9">
                No invoices found for selected filters.
            </td>
        </tr>

    @endforelse

    </tbody>

    {{-- Grand totals --}}
    @if($invoices->count())
        <tfoot>
        <tr style="font-weight:bold;">
            <td colspan="4">
                TOTAL
            </td>

            <td>
                {{ number_format($summary['total_subtotal'], 2, '.', '') }}
            </td>

            <td>
                {{ number_format($summary['total_tax'], 2, '.', '') }}
            </td>

            <td>
                {{ number_format($summary['total_amount'], 2, '.', '') }}
            </td>

            <td>
                {{ number_format($summary['total_received'], 2, '.', '') }}
            </td>

            <td>
                {{ number_format($summary['total_balance'], 2, '.', '') }}
            </td>
        </tr>
        </tfoot>
    @endif

</table>