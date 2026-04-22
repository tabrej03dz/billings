@include('invoices.partials.shared_logic')

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ ucfirst($type) }} {{ $invoiceNo }}</title>
    <style>
        *{ box-sizing:border-box; }
        body{
            margin:0;
            padding:18px;
            font-family:"DejaVu Sans", sans-serif;
            font-size:12px;
            color:#111827;
            background:#fff;
        }
        table{ width:100%; border-collapse:collapse; }
        .header td, .box td, .items th, .items td, .totals td{
            border:1px solid #111827;
        }
        .header td{ padding:10px; vertical-align:top; }
        .company{ font-size:22px; font-weight:700; }
        .doc{
            text-align:center;
            font-size:24px;
            font-weight:700;
            background:#f3f4f6;
        }
        .box td{
            padding:10px;
            vertical-align:top;
        }
        .label{
            font-size:10px;
            text-transform:uppercase;
            color:#374151;
            font-weight:700;
            margin-bottom:5px;
        }
        .items th{
            padding:8px;
            background:#e5e7eb;
            text-align:left;
            font-size:11px;
        }
        .items td{
            padding:8px;
            vertical-align:top;
        }
        .text-right{ text-align:right; }
        .desc{ font-size:10px; color:#6b7280; margin-top:3px; }
        .totals{
            width:44%;
            margin-left:auto;
            margin-top:14px;
        }
        .totals td{ padding:8px; }
        .totals .total{
            font-weight:700;
            background:#111827;
            color:#fff;
        }
    </style>
</head>
<body>

<table class="header">
    <tr>
        <td style="width:18%;">
            @if(!empty($logo))
                <img src="{{ $logo }}" alt="Logo" style="max-width:100px; max-height:70px;">
            @endif
        </td>
        <td style="width:57%;">
            <div class="company">{{ $b->name ?? 'Real Victory Groups' }}</div>
            <div>{{ $b_addr }}@if($b_city), {{ $b_city }}@endif @if($b_state), {{ $b_state }}@endif @if($b_pin) - {{ $b_pin }}@endif</div>
            <div>Mobile: {{ $b_mobile ?: '-' }}</div>
            <div>Email: {{ $b_email ?: '-' }}</div>
            <div>GSTIN: {{ $b_gstin ?: '-' }}</div>
        </td>
        <td style="width:25%;" class="doc">
            {{ strtoupper($type) }}<br>
            <span style="font-size:12px;">{{ $type != 'quotation' ? 'Invoice' : 'Quotation' }}</span>
        </td>
    </tr>
</table>

<table class="box" style="margin-top:12px;">
    <tr>
        <td style="width:50%;">
            <div class="label">Bill To</div>
            <strong>{{ strtoupper($c->name ?? '-') }}</strong><br>
            {{ $c->address ?? '-' }}
            @if(!empty($c->city)), {{ $c->city }}@endif
            @if(!empty($c->state)), {{ $c->state }}@endif
            @if(!empty($c->pin)) - {{ $c->pin }}@endif
        </td>
        <td style="width:25%;">
            <div class="label">{{ $type != 'quotation' ? 'Invoice' : 'Quotation' }} No</div>
            <strong>{{ $invoiceNo }}</strong>
        </td>
        <td style="width:25%;">
            <div class="label">Date</div>
            <strong>{{ $dmy($invoiceDate) }}</strong>
        </td>
    </tr>
    <tr>
        <td>
            <div class="label">Customer Details</div>
            Mobile: {{ $mobile ?: '-' }}<br>
            GSTIN: {{ $gstin ?: '-' }}
        </td>
        <td>
            <div class="label">PAN</div>
            {{ $pan ?: '-' }}
        </td>
        <td>
            <div class="label">Place of Supply</div>
            {{ $pos ?: '-' }}
        </td>
    </tr>
</table>

<table class="items" style="margin-top:12px;">
    <thead>
    <tr>
        <th style="width:35%">Service</th>
        <th style="width:12%">SAC</th>
        <th style="width:10%">Qty</th>
        <th style="width:14%" class="text-right">Rate</th>
        <th style="width:12%" class="text-right">Tax</th>
        <th style="width:17%" class="text-right">Amount</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $it)
        @php
            $name = $it->item->name ?? '';
            $desc = $it->description ?? '';
            $note = trim((string)($it->note ?? $it->extra_line ?? ''));
            $sac  = $it->sac_code ?? $it->hsn_code ?? $it->sac ?? '';
            $qty  = (float)($it->quantity ?? 1); $qty = $qty > 0 ? $qty : 1;
            $lineBase = (float)($it->line_base ?? 0);
            if ($lineBase <= 0) {
                $r = (float)($it->rate ?? 0);
                $lineBase = $r * $qty;
            }
            $showRate = $single ? $taxable : $lineBase;
            $lineTax = round((($it->rate ?? 0) * ($it->tax_percent ?? 0))/100, 2);
            $lineTotal = (float)($it->amount ?? $it->line_total ?? 0);
            if ($lineTotal <= 0) $lineTotal = $lineBase + $lineTax;
            if ($single && $lineTax <= 0) $lineTax = $finalTax;
            if ($single && $lineTotal <= 0) $lineTotal = $finalTotal;
        @endphp
        <tr>
            <td>
                <strong>{{ $name ?: '-' }}</strong>
                @if($desc)<div class="desc">{{ $desc }}</div>@endif
                @if($note)<div class="desc">{{ $note }}</div>@endif
            </td>
            <td>{{ $sac }}</td>
            <td>{{ $qty }} {{ $it->unit ?? '' }}</td>
            <td class="text-right">{{ $fmt2($showRate) }}</td>
            <td class="text-right">{{ $fmt2($lineTax) }}</td>
            <td class="text-right">{{ $fmt2($lineTotal) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table class="totals">
    <tr><td>Taxable Amount</td><td class="text-right">₹ {{ $fmt2($taxable) }}</td></tr>
    @if($isIGST)
        <tr><td>IGST</td><td class="text-right">₹ {{ $fmt2($igst_db) }}</td></tr>
    @else
        <tr><td>CGST</td><td class="text-right">₹ {{ $fmt2($cgst_db) }}</td></tr>
        <tr><td>SGST</td><td class="text-right">₹ {{ $fmt2($sgst_db) }}</td></tr>
    @endif
    <tr><td>Received</td><td class="text-right">₹ {{ $fmt2($receivedTot) }}</td></tr>
    <tr><td>Balance</td><td class="text-right">₹ {{ $fmt2($balanceNow) }}</td></tr>
    <tr class="total"><td>Total</td><td class="text-right">₹ {{ $fmt2($finalTotal) }}</td></tr>
</table>

<table class="box" style="margin-top:14px;">
    <tr>
        <td style="width:60%;">
            <div class="label">Amount in Words</div>
            <strong>{{ inr_words($finalTotal) }}</strong>

            @if(!empty($inv->terms))
                <div class="label" style="margin-top:12px;">Terms & Conditions</div>
                {!! nl2br(e($inv->terms)) !!}
            @endif
        </td>
        <td style="width:40%; text-align:right;">
            @if(!empty($sign))
                <img src="{{ $sign }}" alt="Signature" style="max-height:50px;"><br>
            @endif
            <strong>Authorised Signatory</strong><br>
            {{ $b->name ?? 'Real Victory Groups' }}
        </td>
    </tr>
</table>

</body>
</html>