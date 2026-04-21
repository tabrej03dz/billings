


@include('invoices.partials.shared_logic')

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ ucfirst($type) }} {{ $invoiceNo }}</title>
    <style>
        *{ box-sizing:border-box; }
        body{ font-family:"DejaVu Sans", sans-serif; font-size:12px; color:#000; margin:0; padding:18px; }
        table{ width:100%; border-collapse:collapse; }
        .top td{ vertical-align:top; }
        .title{ font-size:28px; font-weight:700; letter-spacing:1px; }
        .company{ font-size:20px; font-weight:700; }
        .bordered{ border:1px solid #000; }
        .box{ padding:10px; }
        .mt10{ margin-top:10px; }
        .mt15{ margin-top:15px; }
        .small{ font-size:11px; }
        .text-right{ text-align:right; }
        .text-center{ text-align:center; }
        .items th, .items td{ border:1px solid #000; padding:7px; }
        .items th{ background:#f1f1f1; }
        .totals{ width:40%; margin-left:auto; margin-top:12px; }
        .totals td{ border:1px solid #000; padding:7px; }
        .sign img{ max-height:45px; }
    </style>
</head>
<body>
    <table class="top">
        <tr>
            <td style="width:20%;">
                @if(!empty($logo))
                    <img src="{{ $logo }}" alt="Logo" style="max-width:110px; max-height:80px;">
                @endif
            </td>
            <td style="width:55%;">
                <div class="company">{{ $b->name ?? 'Real Victory Groups' }}</div>
                <div>{{ $b_addr }}@if($b_city), {{ $b_city }}@endif @if($b_state), {{ $b_state }}@endif @if($b_pin) - {{ $b_pin }}@endif</div>
                <div class="small">Mobile: {{ $b_mobile ?: '-' }}</div>
                <div class="small">Email: {{ $b_email ?: '-' }}</div>
                <div class="small">GSTIN: {{ $b_gstin ?: '-' }}</div>
            </td>
            <td style="width:25%;" class="text-right">
                <div class="title">{{ strtoupper($type) }}</div>
                <div class="small">{{ $type != 'quotation' ? 'INVOICE' : 'QUOTATION' }}</div>
            </td>
        </tr>
    </table>

    <table class="mt10">
        <tr>
            <td class="bordered box" style="width:60%;">
                <strong>Bill To</strong><br>
                <strong>{{ strtoupper($c->name ?? '-') }}</strong><br>
                {{ $c->address ?? '-' }} @if(!empty($c->city)), {{ $c->city }}@endif @if(!empty($c->state)), {{ $c->state }}@endif @if(!empty($c->pin)) - {{ $c->pin }}@endif<br>
                Mobile: {{ $mobile ?: '-' }}<br>
                GSTIN: {{ $gstin ?: '-' }}<br>
                PAN: {{ $pan ?: '-' }}<br>
                Place of Supply: {{ $pos ?: '-' }}
            </td>
            <td class="bordered box" style="width:40%;">
                <strong>{{ $type != 'quotation' ? 'Invoice' : 'Quotation' }} No:</strong> {{ $invoiceNo }}<br><br>
                <strong>Date:</strong> {{ $dmy($invoiceDate) }}<br><br>
                <strong>Document Type:</strong> {{ strtoupper($type) }}
            </td>
        </tr>
    </table>

    <table class="items mt15">
        <thead>
            <tr>
                <th style="width:35%;">Description</th>
                <th style="width:10%;">SAC</th>
                <th style="width:10%;">Qty</th>
                <th style="width:15%;">Rate</th>
                <th style="width:10%;">Tax</th>
                <th style="width:20%;">Amount</th>
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
                        <strong>{{ $name ?: '-' }}</strong><br>
                        @if($desc)<span class="small">{{ $desc }}</span><br>@endif
                        @if($note)<span class="small">{{ $note }}</span>@endif
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
        <tr><td><strong>Total</strong></td><td class="text-right"><strong>₹ {{ $fmt2($finalTotal) }}</strong></td></tr>
    </table>

    <div class="mt15">
        <strong>Amount in Words:</strong> {{ inr_words($finalTotal) }}
    </div>

    <table class="mt15">
        <tr>
            <td style="width:60%; vertical-align:top;">
                @if(!empty($inv->terms))
                    <strong>Terms & Conditions</strong><br>
                    {!! nl2br(e($inv->terms)) !!}
                @endif
            </td>
            <td style="width:40%; vertical-align:top;" class="text-right sign">
                @if(!empty($sign))
                    <img src="{{ $sign }}" alt="Signature"><br>
                @endif
                <strong>Authorised Signatory</strong><br>
                {{ $b->name ?? 'Real Victory Groups' }}
            </td>
        </tr>
    </table>
</body>
</html>