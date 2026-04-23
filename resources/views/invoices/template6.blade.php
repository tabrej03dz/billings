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
@endphp

{{-- @include('invoices.partials.shared_logic') --}}

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ ucfirst($type) }} {{ $invoiceNo }}</title>
    <style>
        *{ box-sizing:border-box; }
        body{
            margin:0;
            padding:0;
            font-family:"DejaVu Sans", sans-serif;
            font-size:12px;
            color:#1f2937;
        }
        .page{ width:100%; }
        .sidebar{
            width:26%;
            float:left;
            min-height:1000px;
            background:#111827;
            color:#fff;
            padding:24px 18px;
        }
        .content{
            width:74%;
            float:left;
            padding:24px 22px;
            background:#ffffff;
        }
        .clearfix::after{ content:""; display:block; clear:both; }
        .logo{ text-align:center; margin-bottom:20px; }
        .logo img{ max-width:120px; max-height:90px; }
        .company{
            font-size:22px;
            font-weight:700;
            line-height:1.3;
            margin-bottom:8px;
        }
        .sideLabel{
            font-size:10px;
            text-transform:uppercase;
            letter-spacing:1px;
            color:#9ca3af;
            margin-top:18px;
            margin-bottom:6px;
        }
        .sideText{ line-height:1.5; font-size:11px; }
        .docHead{
            border-bottom:3px solid #111827;
            padding-bottom:10px;
            margin-bottom:18px;
        }
        .docTitle{
            font-size:30px;
            font-weight:700;
            letter-spacing:1px;
            color:#111827;
        }
        .metaTable td{
            padding:6px 8px;
            border:1px solid #e5e7eb;
        }
        .metaTable{
            width:100%;
            border-collapse:collapse;
            margin-bottom:18px;
        }
        .billBox{
            border:2px solid #111827;
            padding:12px;
            margin-bottom:18px;
        }
        .billTitle{
            font-size:12px;
            font-weight:700;
            text-transform:uppercase;
            margin-bottom:8px;
            color:#111827;
        }
        table{ width:100%; border-collapse:collapse; }
        .items th{
            background:#111827;
            color:#fff;
            padding:9px 7px;
            font-size:11px;
            text-align:left;
        }
        .items td{
            border-bottom:1px solid #d1d5db;
            padding:8px 7px;
            vertical-align:top;
        }
        .text-right{ text-align:right; }
        .desc{ font-size:10px; color:#6b7280; margin-top:3px; }
        .summary{
            width:46%;
            margin-left:auto;
            margin-top:18px;
            border:2px solid #111827;
        }
        .summary td{
            padding:8px 10px;
            border-bottom:1px solid #d1d5db;
        }
        .summary .total{
            background:#111827;
            color:#fff;
            font-weight:700;
        }
        .footer{
            margin-top:20px;
        }
        .footerLeft{
            width:56%;
            float:left;
        }
        .footerRight{
            width:38%;
            float:right;
            text-align:right;
        }
        .sign img{ max-height:50px; }
    </style>
</head>
<body>
<div class="page clearfix">
    <div class="sidebar">
        <div class="logo">
            @if(!empty($logo))
                <img src="{{ $logo }}" alt="Logo">
            @endif
        </div>

        <div class="company">{{ $b->name ?? 'Real Victory Groups' }}</div>

        <div class="sideLabel">Address</div>
        <div class="sideText">
            {{ $b_addr }}@if($b_city), {{ $b_city }}@endif @if($b_state), {{ $b_state }}@endif @if($b_pin) - {{ $b_pin }}@endif
        </div>

        <div class="sideLabel">Contact</div>
        <div class="sideText">
            Mobile: {{ $b_mobile ?: '-' }}<br>
            Email: {{ $b_email ?: '-' }}<br>
            GSTIN: {{ $b_gstin ?: '-' }}
        </div>

        <div class="sideLabel">Amount in Words</div>
        <div class="sideText">{{ inr_words($finalTotal) }}</div>

        @if(!empty($inv->terms))
            <div class="sideLabel">Terms</div>
            <div class="sideText">{!! nl2br(e($inv->terms)) !!}</div>
        @endif
    </div>

    <div class="content">
        <div class="docHead">
            <div class="docTitle">{{ strtoupper($type) }} {{ $type != 'quotation' ? 'INVOICE' : '' }}</div>
        </div>

        <table class="metaTable">
            <tr>
                <td><strong>{{ $type != 'quotation' ? 'Invoice' : 'Quotation' }} No:</strong> {{ $invoiceNo }}</td>
                <td><strong>Date:</strong> {{ $dmy($invoiceDate) }}</td>
            </tr>
        </table>

        <div class="billBox">
            <div class="billTitle">Bill To</div>
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

        <div class="footer clearfix">
            <div class="footerLeft"></div>
            <div class="footerRight sign">
                @if(!empty($sign))
                    <img src="{{ $sign }}" alt="Signature"><br>
                @endif
                <strong>Authorised Signatory</strong><br>
                {{ $b->name ?? 'Real Victory Groups' }}
            </div>
        </div>
    </div>
</div>
</body>
</html>