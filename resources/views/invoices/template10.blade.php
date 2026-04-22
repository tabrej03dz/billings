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
            background:#f9fafb;
            color:#111827;
        }
        .page{
            max-width:760px;
            margin:0 auto;
            background:#fff;
            border:1px dashed #9ca3af;
            padding:20px;
        }
        .center{ text-align:center; }
        .logo img{ max-width:90px; max-height:70px; }
        .company{ font-size:24px; font-weight:700; margin-top:6px; }
        .subtitle{ font-size:11px; color:#6b7280; line-height:1.5; }
        .doc{
            margin:16px auto;
            display:inline-block;
            padding:8px 18px;
            border:2px solid #111827;
            font-size:20px;
            font-weight:700;
        }
        .divider{
            border-top:1px dashed #9ca3af;
            margin:14px 0;
        }
        table{ width:100%; border-collapse:collapse; }
        .meta td{
            padding:6px 0;
        }
        .label{
            font-weight:700;
            width:35%;
        }
        .items th{
            border-top:1px solid #111827;
            border-bottom:1px solid #111827;
            padding:8px 6px;
            text-align:left;
            font-size:11px;
        }
        .items td{
            border-bottom:1px dashed #cbd5e1;
            padding:8px 6px;
            vertical-align:top;
        }
        .text-right{ text-align:right; }
        .desc{ font-size:10px; color:#6b7280; margin-top:3px; }
        .summary td{
            padding:7px 4px;
            border-bottom:1px dashed #cbd5e1;
        }
        .summary .total td{
            border-top:2px solid #111827;
            border-bottom:2px solid #111827;
            font-weight:700;
            font-size:14px;
        }
        .sign{ margin-top:30px; text-align:right; }
        .sign img{ max-height:45px; }
    </style>
</head>
<body>
<div class="page">
    <div class="center logo">
        @if(!empty($logo))
            <img src="{{ $logo }}" alt="Logo">
        @endif
    </div>

    <div class="center company">{{ $b->name ?? 'Real Victory Groups' }}</div>
    <div class="center subtitle">
        {{ $b_addr }}@if($b_city), {{ $b_city }}@endif @if($b_state), {{ $b_state }}@endif @if($b_pin) - {{ $b_pin }}@endif<br>
        Mobile: {{ $b_mobile ?: '-' }} | Email: {{ $b_email ?: '-' }}<br>
        GSTIN: {{ $b_gstin ?: '-' }}
    </div>

    <div class="center">
        <div class="doc">{{ strtoupper($type) }}</div>
    </div>

    <div class="divider"></div>

    <table class="meta">
        <tr>
            <td class="label">{{ $type != 'quotation' ? 'Invoice' : 'Quotation' }} No</td>
            <td>{{ $invoiceNo }}</td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td>{{ $dmy($invoiceDate) }}</td>
        </tr>
        <tr>
            <td class="label">Bill To</td>
            <td>
                <strong>{{ strtoupper($c->name ?? '-') }}</strong><br>
                {{ $c->address ?? '-' }}
                @if(!empty($c->city)), {{ $c->city }}@endif
                @if(!empty($c->state)), {{ $c->state }}@endif
                @if(!empty($c->pin)) - {{ $c->pin }}@endif
            </td>
        </tr>
        <tr>
            <td class="label">Customer Info</td>
            <td>
                Mobile: {{ $mobile ?: '-' }}<br>
                GSTIN: {{ $gstin ?: '-' }}<br>
                PAN: {{ $pan ?: '-' }}<br>
                POS: {{ $pos ?: '-' }}
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="items">
        <thead>
        <tr>
            <th style="width:34%">Service</th>
            <th style="width:10%">SAC</th>
            <th style="width:10%">Qty</th>
            <th style="width:14%" class="text-right">Rate</th>
            <th style="width:12%" class="text-right">Tax</th>
            <th style="width:20%" class="text-right">Amount</th>
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

    <div class="divider"></div>

    <table class="summary">
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

    <div class="divider"></div>

    <div>
        <strong>Amount in Words:</strong><br>
        {{ inr_words($finalTotal) }}
    </div>

    @if(!empty($inv->terms))
        <div class="divider"></div>
        <div>
            <strong>Terms & Conditions</strong><br>
            {!! nl2br(e($inv->terms)) !!}
        </div>
    @endif

    <div class="sign">
        @if(!empty($sign))
            <img src="{{ $sign }}" alt="Signature"><br>
        @endif
        <strong>Authorised Signatory</strong><br>
        {{ $b->name ?? 'Real Victory Groups' }}
    </div>
</div>
</body>
</html>