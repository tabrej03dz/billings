


@include('invoices.partials.shared_logic')

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ ucfirst($type) }} {{ $invoiceNo }}</title>
    <style>
        *{ box-sizing:border-box; }
        body{ font-family:"DejaVu Sans", sans-serif; font-size:12px; color:#111827; margin:0; padding:20px; }
        table{ width:100%; border-collapse:collapse; }
        .top{ margin-bottom:18px; }
        .top td{ vertical-align:top; }
        .company{ font-size:26px; font-weight:700; }
        .docTitle{ font-size:22px; font-weight:700; text-align:right; }
        .muted{ color:#6b7280; }
        .line{ border-top:1px solid #d1d5db; margin:14px 0; }
        .label{ font-size:10px; color:#6b7280; text-transform:uppercase; letter-spacing:.8px; }
        .value{ font-size:12px; font-weight:700; }
        .infoGrid td{ padding:8px 0; vertical-align:top; }
        .items th{
            border-bottom:2px solid #111827;
            padding:8px 6px;
            text-align:left;
            font-size:11px;
            text-transform:uppercase;
        }
        .items td{
            border-bottom:1px solid #e5e7eb;
            padding:8px 6px;
            vertical-align:top;
        }
        .text-right{ text-align:right; }
        .small{ font-size:10px; color:#6b7280; }
        .totals{
            width:38%;
            margin-left:auto;
            margin-top:14px;
        }
        .totals td{
            padding:7px 0;
            border-bottom:1px solid #e5e7eb;
        }
        .grand td{
            font-size:14px;
            font-weight:700;
            border-bottom:2px solid #111827;
            padding-top:10px;
        }
    </style>
</head>
<body>
    <table class="top">
        <tr>
            <td style="width:65%;">
                <div class="company">{{ $b->name ?? 'Real Victory Groups' }}</div>
                <div class="muted">{{ $b_addr }}@if($b_city), {{ $b_city }}@endif @if($b_state), {{ $b_state }}@endif @if($b_pin) - {{ $b_pin }}@endif</div>
                <div class="muted">GSTIN: {{ $b_gstin ?: '-' }} | Mobile: {{ $b_mobile ?: '-' }}</div>
                <div class="muted">Email: {{ $b_email ?: '-' }}</div>
            </td>
            <td style="width:35%; text-align:right;">
                <div class="docTitle">{{ strtoupper($type) }}</div>
                <div class="muted">{{ $type != 'quotation' ? 'Invoice' : 'Quotation' }}</div>
                @if(!empty($logo))
                    <img src="{{ $logo }}" alt="Logo" style="max-width:95px; max-height:60px; margin-top:8px;">
                @endif
            </td>
        </tr>
    </table>

    <div class="line"></div>

    <table class="infoGrid">
        <tr>
            <td style="width:50%;">
                <div class="label">Bill To</div>
                <div class="value">{{ strtoupper($c->name ?? '-') }}</div>
                <div>{{ $c->address ?? '-' }} @if(!empty($c->city)), {{ $c->city }}@endif @if(!empty($c->state)), {{ $c->state }}@endif @if(!empty($c->pin)) - {{ $c->pin }}@endif</div>
                <div class="small" style="margin-top:4px;">
                    Mobile: {{ $mobile ?: '-' }} |
                    GSTIN: {{ $gstin ?: '-' }} |
                    PAN: {{ $pan ?: '-' }}
                </div>
            </td>
            <td style="width:25%;">
                <div class="label">Document No</div>
                <div class="value">{{ $invoiceNo }}</div>
            </td>
            <td style="width:25%;">
                <div class="label">Document Date</div>
                <div class="value">{{ $dmy($invoiceDate) }}</div>
            </td>
        </tr>
    </table>

    <div class="line"></div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:36%;">Service</th>
                <th style="width:12%;">SAC</th>
                <th style="width:10%;">Qty</th>
                <th style="width:12%;" class="text-right">Rate</th>
                <th style="width:12%;" class="text-right">Tax</th>
                <th style="width:18%;" class="text-right">Amount</th>
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
                        @if($desc)<div class="small">{{ $desc }}</div>@endif
                        @if($note)<div class="small">{{ $note }}</div>@endif
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
        <tr class="grand"><td>Total</td><td class="text-right">₹ {{ $fmt2($finalTotal) }}</td></tr>
    </table>

    <div style="margin-top:18px;">
        <div class="label">Amount in Words</div>
        <div class="value">{{ inr_words($finalTotal) }}</div>
    </div>

    <table style="margin-top:24px;">
        <tr>
            <td style="width:60%; vertical-align:top;">
                @if(!empty($inv->terms))
                    <div class="label">Terms & Conditions</div>
                    <div>{!! nl2br(e($inv->terms)) !!}</div>
                @endif
            </td>
            <td style="width:40%; text-align:right; vertical-align:top;">
                @if(!empty($sign))
                    <img src="{{ $sign }}" alt="Signature" style="max-height:45px;"><br>
                @endif
                <div class="label">Authorised Signatory</div>
                <div class="value">{{ $b->name ?? 'Real Victory Groups' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>