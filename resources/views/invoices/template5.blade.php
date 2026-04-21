
@include('invoices.partials.shared_logic')

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ ucfirst($type) }} {{ $invoiceNo }}</title>
    <style>
        *{ box-sizing:border-box; }
        body{ font-family:"DejaVu Sans", sans-serif; font-size:12px; background:#f8fafc; margin:0; padding:18px; color:#0f172a; }
        .page{
            background:#fff;
            border:1px solid #e2e8f0;
            padding:16px;
        }
        table{ width:100%; border-collapse:collapse; }
        .headerBox{
            border:1px solid #cbd5e1;
            margin-bottom:14px;
        }
        .headerBox td{ vertical-align:top; padding:12px; }
        .company{ font-size:22px; font-weight:700; color:#7c3aed; }
        .docType{
            background:#7c3aed;
            color:#fff;
            padding:10px;
            text-align:center;
            font-size:18px;
            font-weight:700;
        }
        .subBox{
            border:1px solid #cbd5e1;
            margin-bottom:14px;
        }
        .subBox td{
            padding:10px;
            vertical-align:top;
            border-right:1px solid #cbd5e1;
        }
        .subBox td:last-child{ border-right:none; }
        .label{ font-size:10px; text-transform:uppercase; color:#64748b; font-weight:700; margin-bottom:4px; }
        .items th{
            background:#ede9fe;
            color:#5b21b6;
            padding:8px;
            text-align:left;
            border:1px solid #ddd6fe;
        }
        .items td{
            padding:8px;
            border:1px solid #e5e7eb;
            vertical-align:top;
        }
        .text-right{ text-align:right; }
        .descSmall{ font-size:10px; color:#6b7280; }
        .totals{
            width:44%;
            margin-left:auto;
            margin-top:14px;
        }
        .totals td{
            border:1px solid #cbd5e1;
            padding:8px;
        }
        .totals .grand{
            background:#7c3aed;
            color:#fff;
            font-weight:700;
        }
    </style>
</head>
<body>
<div class="page">
    <table class="headerBox">
        <tr>
            <td style="width:70%;">
                <div class="company">{{ $b->name ?? 'Real Victory Groups' }}</div>
                <div>{{ $b_addr }}@if($b_city), {{ $b_city }}@endif @if($b_state), {{ $b_state }}@endif @if($b_pin) - {{ $b_pin }}@endif</div>
                <div style="margin-top:6px;">Mobile: {{ $b_mobile ?: '-' }}</div>
                <div>Email: {{ $b_email ?: '-' }}</div>
                <div>GSTIN: {{ $b_gstin ?: '-' }}</div>
            </td>
            <td style="width:30%; text-align:right;">
                @if(!empty($logo))
                    <img src="{{ $logo }}" alt="Logo" style="max-width:100px; max-height:70px;"><br><br>
                @endif
                <div class="docType">{{ strtoupper($type) }}</div>
            </td>
        </tr>
    </table>

    <table class="subBox">
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
                <strong>{{ $invoiceNo }}</strong><br><br>
                <div class="label">Date</div>
                <strong>{{ $dmy($invoiceDate) }}</strong>
            </td>
            <td style="width:25%;">
                <div class="label">Customer Info</div>
                Mobile: {{ $mobile ?: '-' }}<br>
                GSTIN: {{ $gstin ?: '-' }}<br>
                PAN: {{ $pan ?: '-' }}<br>
                POS: {{ $pos ?: '-' }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
        <tr>
            <th style="width:34%;">Service Details</th>
            <th style="width:10%;">SAC</th>
            <th style="width:10%;">Qty</th>
            <th style="width:14%;" class="text-right">Rate</th>
            <th style="width:12%;" class="text-right">Tax</th>
            <th style="width:20%;" class="text-right">Amount</th>
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
                <td class="text-right">{{ $fmt2($lineTax) }}<div class="descSmall">({{ $it->tax_percent ?? 0 }}%)</div></td>
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
        <tr><td>Received Amount</td><td class="text-right">₹ {{ $fmt2($receivedTot) }}</td></tr>
        <tr><td>Balance</td><td class="text-right">₹ {{ $fmt2($balanceNow) }}</td></tr>
        <tr class="grand"><td>Total Amount</td><td class="text-right">₹ {{ $fmt2($finalTotal) }}</td></tr>
    </table>

    <table style="margin-top:16px;">
        <tr>
            <td style="width:60%; vertical-align:top;">
                <div class="label">Amount in Words</div>
                <strong>{{ inr_words($finalTotal) }}</strong>

                @if(!empty($inv->terms))
                    <div class="label" style="margin-top:16px;">Terms & Conditions</div>
                    {!! nl2br(e($inv->terms)) !!}
                @endif
            </td>
            <td style="width:40%; text-align:right; vertical-align:top;">
                @if(!empty($sign))
                    <img src="{{ $sign }}" alt="Signature" style="max-height:50px;"><br>
                @endif
                <div class="label">Authorised Signatory</div>
                <strong>{{ $b->name ?? 'Real Victory Groups' }}</strong>
            </td>
        </tr>
    </table>
</div>
</body>
</html>