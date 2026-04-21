
@include('invoices.partials.shared_logic')

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ ucfirst($type) }} {{ $invoiceNo }}</title>
    <style>
        *{ box-sizing:border-box; }
        body{ font-family:"DejaVu Sans", sans-serif; font-size:12px; color:#1e293b; margin:0; padding:0; }
        .page{ padding:18px; }
        .hero{
            background:#1d4ed8;
            color:#fff;
            padding:16px;
            border-radius:0 0 14px 14px;
            margin-bottom:14px;
        }
        table{ width:100%; border-collapse:collapse; }
        .hero td{ vertical-align:top; }
        .company{ font-size:24px; font-weight:700; }
        .muted{ color:#475569; }
        .card{
            border:1px solid #cbd5e1;
            border-radius:10px;
            padding:12px;
            margin-bottom:14px;
        }
        .cardTitle{
            color:#1d4ed8;
            font-size:13px;
            font-weight:700;
            margin-bottom:8px;
            text-transform:uppercase;
        }
        .items th{
            background:#eff6ff;
            color:#1d4ed8;
            border-bottom:2px solid #1d4ed8;
            padding:8px;
            text-align:left;
            font-size:11px;
        }
        .items td{
            border-bottom:1px solid #e2e8f0;
            padding:8px;
            vertical-align:top;
        }
        .text-right{ text-align:right; }
        .descSmall{ font-size:10px; color:#64748b; }
        .summary{
            width:45%;
            margin-left:auto;
            margin-top:14px;
            border:1px solid #cbd5e1;
            border-radius:10px;
            overflow:hidden;
        }
        .summary td{ padding:8px 10px; border-bottom:1px solid #e2e8f0; }
        .summary .total{ background:#1d4ed8; color:#fff; font-weight:700; }
        .row:after{ content:""; display:block; clear:both; }
        .colL{ float:left; width:54%; }
        .colR{ float:right; width:42%; }
        .sign img{ max-height:48px; }
    </style>
</head>
<body>
<div class="page">
    <div class="hero">
        <table>
            <tr>
                <td style="width:70%;">
                    <div class="company">{{ $b->name ?? 'Real Victory Groups' }}</div>
                    <div>{{ $b_addr }}@if($b_city), {{ $b_city }}@endif @if($b_state), {{ $b_state }}@endif @if($b_pin) - {{ $b_pin }}@endif</div>
                    <div style="margin-top:5px;">Mobile: {{ $b_mobile ?: '-' }} | GSTIN: {{ $b_gstin ?: '-' }}</div>
                    <div>Email: {{ $b_email ?: '-' }}</div>
                </td>
                <td style="width:30%; text-align:right;">
                    <div style="font-size:24px; font-weight:700;">{{ strtoupper($type) }}</div>
                    <div>{{ $type != 'quotation' ? 'Invoice' : 'Quotation' }}</div>
                    @if(!empty($logo))
                        <img src="{{ $logo }}" alt="Logo" style="max-width:100px; max-height:70px; margin-top:8px;">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="row">
        <div class="colL">
            <div class="card">
                <div class="cardTitle">Bill To</div>
                <strong>{{ strtoupper($c->name ?? '-') }}</strong><br>
                {{ $c->address ?? '-' }}
                @if(!empty($c->city)), {{ $c->city }}@endif
                @if(!empty($c->state)), {{ $c->state }}@endif
                @if(!empty($c->pin)) - {{ $c->pin }}@endif
                <div style="margin-top:6px;">
                    Mobile: {{ $mobile ?: '-' }}<br>
                    GSTIN: {{ $gstin ?: '-' }}<br>
                    PAN: {{ $pan ?: '-' }}<br>
                    Place of Supply: {{ $pos ?: '-' }}
                </div>
            </div>
        </div>
        <div class="colR">
            <div class="card">
                <div class="cardTitle">Invoice Info</div>
                <strong>No:</strong> {{ $invoiceNo }}<br>
                <strong>Date:</strong> {{ $dmy($invoiceDate) }}<br>
                <strong>Status:</strong> {{ ucfirst($type) }}
            </div>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:35%;">Item</th>
                <th style="width:12%;">SAC</th>
                <th style="width:10%;">Qty</th>
                <th style="width:12%;" class="text-right">Rate</th>
                <th style="width:12%;" class="text-right">Tax</th>
                <th style="width:19%;" class="text-right">Amount</th>
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
                        @if($desc)<div class="descSmall">{{ $desc }}</div>@endif
                        @if($note)<div class="descSmall">{{ $note }}</div>@endif
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
        <tr class="total"><td>Total Amount</td><td class="text-right">₹ {{ $fmt2($finalTotal) }}</td></tr>
    </table>

    <div class="card" style="margin-top:14px;">
        <div class="cardTitle">Amount in Words</div>
        {{ inr_words($finalTotal) }}
    </div>

    <div class="row">
        <div class="colL">
            @if(!empty($inv->terms))
                <div class="card">
                    <div class="cardTitle">Terms & Conditions</div>
                    {!! nl2br(e($inv->terms)) !!}
                </div>
            @endif
        </div>
        <div class="colR sign" style="text-align:right;">
            @if(!empty($sign))
                <img src="{{ $sign }}" alt="Signature"><br>
            @endif
            <strong>Authorised Signatory</strong><br>
            {{ $b->name ?? 'Real Victory Groups' }}
        </div>
    </div>
</div>
</body>
</html>