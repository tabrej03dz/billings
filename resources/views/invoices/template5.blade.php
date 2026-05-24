
@php
    $termsText = $inv->terms ?? null;

    $primaryColor   = $templateSetting->primary_color ?? '#7c3aed';
    $textColor      = $templateSetting->text_color ?? '#0f172a';
    $mutedColor     = $templateSetting->muted_color ?? '#64748b';
    $borderColor    = $templateSetting->border_color ?? '#cbd5e1';
    $secondaryColor = $templateSetting->secondary_color ?? '#e5e7eb';
    $lightBgColor   = $templateSetting->light_bg_color ?? '#ede9fe';
    $softBgColor    = $templateSetting->soft_bg_color ?? '#f8fafc';
    $fontFamily     = $templateSetting->font_family ?? 'DejaVu Sans';

    $showLogo      = $templateSetting->show_logo ?? true;
    $showSignature = $templateSetting->show_signature ?? true;
    $showTerms     = $templateSetting->show_terms ?? true;

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

    if (!function_exists('inr_words')) {
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
    <title>{{ ucfirst($type) }} {{ $invoiceNo }}</title>
    <style>
        *{ box-sizing:border-box; }
        body{ font-family:"{{ $fontFamily }}", "DejaVu Sans", sans-serif; font-size:12px; background:{{ $softBgColor }}; margin:0; padding:18px; color:{{ $textColor }}; }
        .page{
            background:#fff;
            border:1px solid {{ $secondaryColor }};
            padding:16px;
        }
        table{ width:100%; border-collapse:collapse; }
        .headerBox{
            border:1px solid {{ $borderColor }};
            margin-bottom:14px;
        }
        .headerBox td{ vertical-align:top; padding:12px; }
        .company{ font-size:22px; font-weight:700; color:{{ $primaryColor }}; }
        .docType{
            background:{{ $primaryColor }};
            color:#fff;
            padding:10px;
            text-align:center;
            font-size:18px;
            font-weight:700;
        }
        .subBox{
            border:1px solid {{ $borderColor }};
            margin-bottom:14px;
        }
        .subBox td{
            padding:10px;
            vertical-align:top;
            border-right:1px solid {{ $borderColor }};
        }
        .subBox td:last-child{ border-right:none; }
        .label{ font-size:10px; text-transform:uppercase; color:{{ $mutedColor }}; font-weight:700; margin-bottom:4px; }
        .items th{
            background:{{ $lightBgColor }};
            color:{{ $primaryColor }};
            padding:8px;
            text-align:left;
            border:1px solid {{ $secondaryColor }};
        }
        .items td{
            padding:8px;
            border:1px solid {{ $secondaryColor }};
            vertical-align:top;
        }
        .text-right{ text-align:right; }
        .descSmall{ font-size:10px; color:{{ $mutedColor }}; }
        .totals{
            width:44%;
            margin-left:auto;
            margin-top:14px;
        }
        .totals td{
            border:1px solid {{ $borderColor }};
            padding:8px;
        }
        .totals .grand{
            background:{{ $primaryColor }};
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
                @if($showLogo && !empty($logo))
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

                @if($showTerms && !empty($inv->terms))
                    <div class="label" style="margin-top:16px;">Terms & Conditions</div>
                    {!! nl2br(e($inv->terms)) !!}
                @endif
            </td>
            <td style="width:40%; text-align:right; vertical-align:top;">
                @if($showSignature)
                @if(!empty($invoiceSignatureUrl))
                    <img src="{{ $invoiceSignatureUrl }}" alt="Signature" style="max-height:50px;"><br>
                @endif

                <div class="label">Authorised Signatory</div>
                <strong>{{ $b->name ?? 'Real Victory Groups' }}</strong>
                @endif
            </td>
        </tr>
    </table>
</div>
</body>
</html>