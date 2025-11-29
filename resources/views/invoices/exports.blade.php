@php
    use Illuminate\Support\Str;
@endphp

<table>
    <thead>
    <tr>
        <th>Business ID</th>
        <th>Invoice No</th>
        <th>Invoice Date</th>
        <th>Due Date</th>
        <th>Payment Terms (days)</th>

        <th>Client Name</th>
        <th>Client ID</th>

        {{-- Money / totals --}}
        <th>Subtotal</th>
        <th>Discount Total</th>
        <th>Additional Charges</th>
        <th>Tax Amount</th>
        <th>TCS %</th>
        <th>TCS Amount</th>
        <th>Round Off</th>
        <th>Grand Total</th>

        <th>Received Amount</th>
        <th>Balance</th>

        {{-- Payment --}}
        <th>Payment Method</th>

        {{-- Extra info --}}
        <th>Amount in Words</th>
        <th>Notes</th>
        <th>Terms</th>

        {{-- Audit --}}
        <th>Created At</th>
        <th>Updated At</th>
    </tr>
    </thead>

    <tbody>
    @foreach($invoices as $inv)
        <tr>
            {{-- IDs --}}
            <td>{{ $inv->business_id }}</td>
            <td>
                {{ $inv->invoice_prefix ? $inv->invoice_prefix . $inv->invoice_number : $inv->invoice_number }}
            </td>
            <td>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d-M-Y') }}</td>
            <td>{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d-M-Y') : '' }}</td>
            <td>{{ $inv->payment_terms }}</td>

            {{-- Client --}}
            <td>{{ optional($inv->client)->name }}</td>
            <td>{{ $inv->client_id }}</td>

            {{-- Money / totals --}}
            <td>{{ $inv->subtotal }}</td>
            <td>{{ $inv->discount_total }}</td>
            <td>{{ $inv->charge_total }}</td>
            <td>{{ $inv->tax_amount }}</td>
            <td>{{ $inv->tcs_percent }}</td>
            <td>{{ $inv->tcs_amount }}</td>
            <td>{{ $inv->round_off }}</td>
            <td>{{ $inv->total }}</td>

            <td>{{ $inv->received_amount }}</td>
            <td>{{ $inv->balance }}</td>

            {{-- Payment --}}
            <td>{{ $inv->payment_method }}</td>

            {{-- Extra --}}
            <td>{{ $inv->amount_in_words }}</td>
            <td>{{ Str::limit($inv->notes, 120) }}</td>
            <td>{{ Str::limit($inv->terms, 120) }}</td>

        </tr>
    @endforeach
    </tbody>
</table>
