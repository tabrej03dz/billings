@php
    $termsText = $inv->terms ?? null;

    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? ($inv->business ?? null);
    $c = $client ?? ($inv->client ?? null);
    $items = $items ?? collect();

    $fmt0 = fn($v) => number_format((float)$v, 0, '.', '');
    $fmt2 = fn($v) => number_format((float)$v, 2, '.', '');
    $dmy  = fn($date) => $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '';

    $taxable      = (float)($subtotal ?? ($inv->subtotal ?? 0));
    $tax_total_db = (float)($tax_total ?? ($inv->tax_amount ?? 0));

    $cgst_db = (float)($cgst_amount ?? ($inv->cgst_amount ?? 0));
    $sgst_db = (float)($sgst_amount ?? ($inv->sgst_amount ?? 0));
    $igst_db = (float)($igst_amount ?? ($inv->igst_amount ?? 0));

    $grand_db = (float)($grand_total ?? ($inv->total ?? 0));
    $received_db = (float)($received ?? ($inv->received_amount ?? 0));

    $pay = $payRow ?? null;
    $cashAmt   = (float)($pay->cash_amount ?? 0);
    $onlineAmt = (float)($pay->online_amount ?? 0);
    $cardAmt   = (float)($pay->card_amount ?? 0);
    $chequeAmt = (float)($pay->cheque_amount ?? 0);
    $creditExcess = (float)($pay->credit_sales_excess_amount ?? 0);
    $advanceAmt   = (float)($pay->advance_amount ?? 0);

    $receivedTot = $inv->received_amount ?? 0;
    $balanceNow = (float)($balance ?? ($inv->balance ?? max(0, $grand_db - $receivedTot)));

    $taxPercent = (float)($inv->tax_percent ?? 0);
    $isIGST = $igst_db > 0;

    $finalTax = $isIGST
        ? $igst_db
        : (($cgst_db + $sgst_db) > 0 ? ($cgst_db + $sgst_db) : $tax_total_db);

    $finalTax   = round((float)$finalTax, 2);
    $finalTotal = round((float)$grand_db, 2);

    function inr_words($amount)
    {
        $amount = (float)$amount;
        $rupees = (int) floor($amount);
        $paise  = (int) round(($amount - $rupees) * 100);

        $ones = [
            '', 'One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten',
            'Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'
        ];
        $tens = ['', '', 'Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];

        $twoDigits = function($n) use ($ones,$tens){
            $n = (int)$n;
            if ($n == 0) return '';
            if ($n < 20) return $ones[$n];
            return trim($tens[(int)($n/10)] . ' ' . $ones[$n%10]);
        };

        $parts = [];

        if ($rupees >= 10000000) {
            $cr = (int) floor($rupees / 10000000);
            $parts[] = $twoDigits($cr) . ' Crore';
            $rupees = $rupees % 10000000;
        }
        if ($rupees >= 100000) {
            $lk = (int) floor($rupees / 100000);
            $parts[] = $twoDigits($lk) . ' Lakh';
            $rupees = $rupees % 100000;
        }
        if ($rupees >= 1000) {
            $th = (int) floor($rupees / 1000);
            $parts[] = $twoDigits($th) . ' Thousand';
            $rupees = $rupees % 1000;
        }
        if ($rupees >= 100) {
            $hd = (int) floor($rupees / 100);
            $parts[] = $ones[$hd] . ' Hundred';
            $rupees = $rupees % 100;
        }
        if ($rupees > 0) {
            $parts[] = $twoDigits($rupees);
        }

        $words = trim(implode(' ', array_filter($parts)));
        if ($words === '') $words = 'Zero';

        $result = $words . ' Rupees';
        if ($paise > 0) $result .= ' and ' . $twoDigits($paise) . ' Paise';
        return $result;
    }

    $invoiceNo   = $inv->invoice_number ?? $inv->invoice_no ?? '-';
    $invoiceDate = $inv->invoice_date ?? $inv->date ?? null;

    $gstin  = $c->gstin ?? $c->gst_number ?? $c->gst ?? '';
    $pan    = $c->pan ?? $c->pan_number ?? '';
    $pos    = $c->place_of_supply ?? $c->state ?? '';
    $mobile = $c->mobile ?? $c->phone ?? $c->phone1 ?? '';

    $b_addr   = trim((string)($b->address ?? ''));
    $b_city   = trim((string)($b->city ?? ''));
    $b_pin    = trim((string)($b->pin ?? ''));
    $b_state  = trim((string)($b->state ?? ''));
    $b_mobile = $b->mobile ?? $b->phone ?? '';
    $b_email  = $b->email ?? '';
    $b_gstin  = $b->gstin ?? ($inv->gst_no ?? '');

    $single = ($items->count() === 1);

    $invoiceSignature = $inv->signature ?? null;

    $invoiceSignatureUrl = $invoiceSignature
        ? (\Illuminate\Support\Str::startsWith($invoiceSignature, ['http://', 'https://'])
            ? $invoiceSignature
            : public_path('storage/' . $invoiceSignature))
        : null;
@endphp


{{-- @include('invoices.partials.shared_logic') --}}

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ ucfirst($type) }} {{ $type != 'quotation' ? 'Invoice' : '' }} {{ $invoiceNo }}</title>
    <style>
        *{ box-sizing:border-box; }
        body{
            font-family:"DejaVu Sans", sans-serif;
            font-size:12px;
            color:#1f2937;
            margin:0;
            padding:0;
        }
        .page{ padding:18px; }
        .header{
            border-bottom:3px solid #dc2626;
            padding-bottom:12px;
            margin-bottom:14px;
        }
        .row{ width:100%; }
        .left{ float:left; width:65%; }
        .right{ float:right; width:30%; text-align:right; }
        .clearfix::after{ content:""; display:block; clear:both; }

        .company{
            font-size:24px;
            font-weight:700;
            color:#dc2626;
            margin-bottom:6px;
        }
        .muted{ color:#4b5563; }
        .small{ font-size:11px; }
        .badge{
            display:inline-block;
            border:1px solid #dc2626;
            color:#dc2626;
            padding:4px 10px;
            font-size:10px;
            border-radius:20px;
            font-weight:700;
        }
        .metaBox{
            background:#f9fafb;
            border:1px solid #e5e7eb;
            padding:10px;
            border-radius:8px;
            margin:14px 0;
        }
        .metaBox td{ padding:4px 0; }
        .sectionTitle{
            font-size:13px;
            font-weight:700;
            color:#111827;
            margin-bottom:8px;
            text-transform:uppercase;
        }
        .billto{
            border:1px solid #e5e7eb;
            border-left:4px solid #dc2626;
            padding:12px;
            border-radius:8px;
            margin-bottom:14px;
        }
        table{ width:100%; border-collapse:collapse; }
        .items th{
            background:#dc2626;
            color:#fff;
            padding:9px 8px;
            font-size:11px;
            text-align:left;
        }
        .items td{
            border-bottom:1px solid #e5e7eb;
            padding:8px;
            vertical-align:top;
        }
        .text-right{ text-align:right; }
        .descSmall{ font-size:10px; color:#6b7280; margin-top:2px; }
        .summary{
            width:42%;
            margin-left:auto;
            margin-top:16px;
            border:1px solid #e5e7eb;
            border-radius:8px;
            overflow:hidden;
        }
        .summary td{
            padding:8px 10px;
            border-bottom:1px solid #e5e7eb;
        }
        .summary .head{
            background:#fef2f2;
            font-weight:700;
        }
        .summary .grand{
            background:#dc2626;
            color:#fff;
            font-weight:700;
        }
        .footer{
            margin-top:18px;
        }
        .terms{
            float:left;
            width:52%;
        }
        .sign{
            float:right;
            width:38%;
            text-align:right;
        }
        .sign img{ max-height:50px; margin-bottom:8px; }
        .words{
            margin-top:12px;
            padding:10px 12px;
            background:#f9fafb;
            border-radius:8px;
            border:1px solid #e5e7eb;
        }
    </style>
</head>
<body>
<div class="page">
    <div class="header clearfix">
        <div class="left">
            <div class="badge">{{ strtoupper($type) }} {{ $type != 'quotation' ? 'INVOICE' : '' }}</div>
            <div class="company">{{ $b->name ?? 'Real Victory Groups' }}</div>
            <div class="muted">{{ $b_addr }}@if($b_city), {{ $b_city }}@endif @if($b_state), {{ $b_state }}@endif @if($b_pin) - {{ $b_pin }}@endif</div>
            <div class="muted small">Mobile: {{ $b_mobile ?: '-' }} | GSTIN: {{ $b_gstin ?: '-' }}</div>
            <div class="muted small">Email: {{ $b_email ?: '-' }}</div>
        </div>
        <div class="right">
            @if(!empty($logo))
                <img src="{{ $logo }}" alt="Logo" style="max-width:110px; max-height:80px;">
            @endif
        </div>
    </div>

    <div class="metaBox">
        <table>
            <tr>
                <td><strong>{{ $type != 'quotation' ? 'Invoice' : 'Quotation' }} No:</strong> {{ $invoiceNo }}</td>
                <td class="text-right"><strong>Date:</strong> {{ $dmy($invoiceDate) }}</td>
            </tr>
        </table>
    </div>

    <div class="billto">
        <div class="sectionTitle">Bill To</div>
        <div><strong>{{ strtoupper($c->name ?? '-') }}</strong></div>
        <div class="muted">{{ $c->address ?? '-' }} @if(!empty($c->city)), {{ $c->city }}@endif @if(!empty($c->state)), {{ $c->state }}@endif @if(!empty($c->pin)) - {{ $c->pin }}@endif</div>
        <div class="small" style="margin-top:6px;">
            <strong>Mobile:</strong> {{ $mobile ?: '-' }}<br>
            <strong>GSTIN:</strong> {{ $gstin ?: '-' }}<br>
            <strong>PAN:</strong> {{ $pan ?: '-' }}<br>
            <strong>Place of Supply:</strong> {{ $pos ?: '-' }}
        </div>
    </div>

    <table class="items">
        <thead>
        <tr>
            <th style="width:34%">Service</th>
            <th style="width:12%">SAC</th>
            <th style="width:10%">Qty</th>
            <th style="width:12%" class="text-right">Rate</th>
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

    <table class="summary">
        <tr class="head"><td>Taxable Amount</td><td class="text-right">₹ {{ $fmt2($taxable) }}</td></tr>
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

    <div class="words">
        <strong>Total Amount in Words:</strong><br>
        {{ inr_words($finalTotal) }}
    </div>

    <div class="footer clearfix">
        <div class="terms">
            @if(!empty($inv->terms))
                <div class="sectionTitle">Terms & Conditions</div>
                <div class="muted">{!! nl2br(e($inv->terms)) !!}</div>
            @endif
        </div>
        <div class="sign">
            @if(!empty($invoiceSignatureUrl))
                <img src="{{ $invoiceSignatureUrl }}" alt="Signature">
            @endif

            <div><strong>Authorised Signatory</strong></div>
            <div class="muted">{{ $b->name ?? 'Real Victory Groups' }}</div>
        </div>
    </div>
</div>
</body>
</html>