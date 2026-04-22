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
            background:#faf7f2;
            color:#2b2b2b;
        }
        .page{
            background:#fff;
            border:1px solid #d6c8b4;
            padding:18px;
        }
        .hero{
            background:#1c1917;
            color:#f5e7c6;
            padding:18px;
            margin:-18px -18px 18px -18px;
        }
        .heroRow:after{ content:""; display:block; clear:both; }
        .left{ float:left; width:65%; }
        .right{ float:right; width:30%; text-align:right; }
        .company{ font-size:24px; font-weight:700; color:#f5e7c6; }
        .docType{ font-size:28px; font-weight:700; color:#f5e7c6; }
        .goldLine{
            height:3px;
            background:#c9a227;
            margin:14px 0 18px 0;
        }
        .panel{
            border:1px solid #d6c8b4;
            padding:14px;
            margin-bottom:14px;
            background:#fffdf9;
        }
        .panelTitle{
            font-size:12px;
            font-weight:700;
            text-transform:uppercase;
            color:#8a6a14;
            margin-bottom:8px;
        }
        table{ width:100%; border-collapse:collapse; }
        .items th{
            background:#f4ead7;
            color:#6b4f00;
            border-top:2px solid #c9a227;
            border-bottom:2px solid #c9a227;
            padding:9px 8px;
            text-align:left;
        }
        .items td{
            border-bottom:1px solid #eadfcb;
            padding:9px 8px;
            vertical-align:top;
        }
        .text-right{ text-align:right; }
        .desc{ font-size:10px; color:#7c6f64; margin-top:3px; }
        .summary{
            width:44%;
            margin-left:auto;
            margin-top:16px;
        }
        .summary td{
            padding:9px 10px;
            border:1px solid #d6c8b4;
        }
        .summary .total{
            background:#1c1917;
            color:#f5e7c6;
            font-weight:700;
        }
        .bottomLeft{ width:56%; float:left; }
        .bottomRight{ width:38%; float:right; text-align:right; }
        .sign img{ max-height:48px; }
    </style>
</head>
<body>
<div class="page">
    <div class="hero">
        <div class="heroRow">
            <div class="left">
                <div class="company">{{ $b->name ?? 'Real Victory Groups' }}</div>
                <div>{{ $b_addr }}@if($b_city), {{ $b_city }}@endif @if($b_state), {{ $b_state }}@endif @if($b_pin) - {{ $b_pin }}@endif</div>
                <div style="margin-top:5px;">Mobile: {{ $b_mobile ?: '-' }} | GSTIN: {{ $b_gstin ?: '-' }}</div>
                <div>Email: {{ $b_email ?: '-' }}</div>
            </div>
            <div class="right">
                <div class="docType">{{ strtoupper($type) }}</div>
                <div>{{ $type != 'quotation' ? 'Invoice' : 'Quotation' }}</div>
                @if(!empty($logo))
                    <img src="{{ $logo }}" alt="Logo" style="max-width:95px; max-height:65px; margin-top:8px;">
                @endif
            </div>
        </div>
    </div>

    <div class="goldLine"></div>

    <div class="panel">
        <table>
            <tr>
                <td style="width:60%;">
                    <div class="panelTitle">Bill To</div>
                    <strong>{{ strtoupper($c->name ?? '-') }}</strong><br>
                    {{ $c->address ?? '-' }}
                    @if(!empty($c->city)), {{ $c->city }}@endif
                    @if(!empty($c->state)), {{ $c->state }}@endif
                    @if(!empty($c->pin)) - {{ $c->pin }}@endif
                    <div style="margin-top:8px;">
                        Mobile: {{ $mobile ?: '-' }}<br>
                        GSTIN: {{ $gstin ?: '-' }}<br>
                        PAN: {{ $pan ?: '-' }}<br>
                        POS: {{ $pos ?: '-' }}
                    </div>
                </td>
                <td style="width:40%; vertical-align:top;">
                    <div class="panelTitle">Invoice Details</div>
                    <strong>No:</strong> {{ $invoiceNo }}<br><br>
                    <strong>Date:</strong> {{ $dmy($invoiceDate) }}
                </td>
            </tr>
        </table>
    </div>

    <table class="items">
        <thead>
        <tr>
            <th style="width:35%">Service</th>
            <th style="width:12%">SAC</th>
            <th style="width:10%">Qty</th>
            <th style="width:13%" class="text-right">Rate</th>
            <th style="width:12%" class="text-right">Tax</th>
            <th style="width:18%" class="text-right">Amount</th>
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

    <div style="margin-top:18px;" class="panel">
        <div class="panelTitle">Amount in Words</div>
        {{ inr_words($finalTotal) }}
    </div>

    <div style="margin-top:18px;">
        <div class="bottomLeft">
            @if(!empty($inv->terms))
                <div class="panel">
                    <div class="panelTitle">Terms & Conditions</div>
                    {!! nl2br(e($inv->terms)) !!}
                </div>
            @endif
        </div>
        <div class="bottomRight sign">
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