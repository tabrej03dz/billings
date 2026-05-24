@php
    $termsText = $inv->terms ?? null;

    // Dynamic setting with same default colors of this black/grey template
    $primaryColor  = $templateSetting->primary_color ?? '#000000';
    $textColor     = $templateSetting->text_color ?? '#000000';
    $mutedColor    = $templateSetting->muted_color ?? '#000000';
    $borderColor   = $templateSetting->border_color ?? '#000000';
    $lightBgColor  = $templateSetting->light_bg_color ?? '#f1f1f1';
    $softBgColor   = $templateSetting->soft_bg_color ?? '#ffffff';
    $fontFamily    = $templateSetting->font_family ?? 'DejaVu Sans';

    $showLogo      = $templateSetting->show_logo ?? true;
    $showSignature = $templateSetting->show_signature ?? true;
    $showTerms     = $templateSetting->show_terms ?? true;

    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? ($inv->business ?? null);
    $c = $client ?? ($inv->client ?? null);
    $items = $items ?? collect();

    $fmt2 = fn($v) => number_format((float)$v, 2, '.', '');
    $dmy  = fn($date) => $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '';

    $taxable      = (float)($subtotal ?? ($inv->subtotal ?? 0));
    $tax_total_db = (float)($tax_total ?? ($inv->tax_amount ?? 0));

    $cgst_db = (float)($cgst_amount ?? ($inv->cgst_amount ?? 0));
    $sgst_db = (float)($sgst_amount ?? ($inv->sgst_amount ?? 0));
    $igst_db = (float)($igst_amount ?? ($inv->igst_amount ?? 0));

    $grand_db = (float)($grand_total ?? ($inv->total ?? 0));
    $receivedTot = (float)($inv->received_amount ?? 0);
    $balanceNow = (float)($balance ?? ($inv->balance ?? max(0, $grand_db - $receivedTot)));

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

            $twoDigits = function($n) use ($ones, $tens) {
                $n = (int)$n;
                if ($n == 0) return '';
                if ($n < 20) return $ones[$n];
                return trim($tens[(int)($n / 10)] . ' ' . $ones[$n % 10]);
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

            if ($paise > 0) {
                $result .= ' and ' . $twoDigits($paise) . ' Paise';
            }

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

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ ucfirst($type) }} {{ $invoiceNo }}</title>

    <style>
        *{ box-sizing:border-box; }

        body{
            font-family:"{{ $fontFamily }}", "DejaVu Sans", sans-serif;
            font-size:12px;
            color:{{ $textColor }};
            background:{{ $softBgColor }};
            margin:0;
            padding:18px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        .top td{
            vertical-align:top;
        }

        .title{
            font-size:28px;
            font-weight:700;
            letter-spacing:1px;
            color:{{ $primaryColor }};
        }

        .company{
            font-size:20px;
            font-weight:700;
            color:{{ $primaryColor }};
        }

        .bordered{
            border:1px solid {{ $borderColor }};
        }

        .box{
            padding:10px;
        }

        .mt10{
            margin-top:10px;
        }

        .mt15{
            margin-top:15px;
        }

        .small{
            font-size:11px;
            color:{{ $mutedColor }};
        }

        .muted{
            color:{{ $mutedColor }};
        }

        .text-right{
            text-align:right;
        }

        .text-center{
            text-align:center;
        }

        .items th,
        .items td{
            border:1px solid {{ $borderColor }};
            padding:7px;
            color:{{ $textColor }};
        }

        .items th{
            background:{{ $lightBgColor }};
            color:{{ $primaryColor }};
            font-weight:700;
        }

        .totals{
            width:40%;
            margin-left:auto;
            margin-top:12px;
        }

        .totals td{
            border:1px solid {{ $borderColor }};
            padding:7px;
            color:{{ $textColor }};
        }

        .sign img{
            max-height:45px;
        }

        .primary-text{
            color:{{ $primaryColor }};
        }
    </style>
</head>

<body>

<table class="top">
    <tr>
        <td style="width:20%;">
            @if($showLogo && !empty($logo))
                <img src="{{ $logo }}" alt="Logo" style="max-width:110px; max-height:80px;">
            @endif
        </td>

        <td style="width:55%;">
            <div class="company">
                {{ $b->name ?? 'Real Victory Groups' }}
            </div>

            <div class="muted">
                {{ $b_addr }}
                @if($b_city), {{ $b_city }}@endif
                @if($b_state), {{ $b_state }}@endif
                @if($b_pin) - {{ $b_pin }}@endif
            </div>

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
            <strong class="primary-text">Bill To</strong><br>

            <strong>{{ strtoupper($c->name ?? '-') }}</strong><br>

            <span class="muted">
                {{ $c->address ?? '-' }}
                @if(!empty($c->city)), {{ $c->city }}@endif
                @if(!empty($c->state)), {{ $c->state }}@endif
                @if(!empty($c->pin)) - {{ $c->pin }}@endif
            </span><br>

            Mobile: {{ $mobile ?: '-' }}<br>
            GSTIN: {{ $gstin ?: '-' }}<br>
            PAN: {{ $pan ?: '-' }}<br>
            Place of Supply: {{ $pos ?: '-' }}
        </td>

        <td class="bordered box" style="width:40%;">
            <strong>{{ $type != 'quotation' ? 'Invoice' : 'Quotation' }} No:</strong>
            {{ $invoiceNo }}<br><br>

            <strong>Date:</strong>
            {{ $dmy($invoiceDate) }}<br><br>

            <strong>Document Type:</strong>
            {{ strtoupper($type) }}
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

                $qty  = (float)($it->quantity ?? 1);
                $qty = $qty > 0 ? $qty : 1;

                $lineBase = (float)($it->line_base ?? 0);

                if ($lineBase <= 0) {
                    $r = (float)($it->rate ?? 0);
                    $lineBase = $r * $qty;
                }

                $showRate = $single ? $taxable : $lineBase;

                $lineTax = round((($it->rate ?? 0) * ($it->tax_percent ?? 0)) / 100, 2);

                $lineTotal = (float)($it->amount ?? $it->line_total ?? 0);

                if ($lineTotal <= 0) {
                    $lineTotal = $lineBase + $lineTax;
                }

                if ($single && $lineTax <= 0) {
                    $lineTax = $finalTax;
                }

                if ($single && $lineTotal <= 0) {
                    $lineTotal = $finalTotal;
                }
            @endphp

            <tr>
                <td>
                    <strong>{{ $name ?: '-' }}</strong><br>

                    @if($desc)
                        <span class="small">{{ $desc }}</span><br>
                    @endif

                    @if($note)
                        <span class="small">{{ $note }}</span>
                    @endif
                </td>

                <td>{{ $sac }}</td>

                <td>{{ $qty }} {{ $it->unit ?? '' }}</td>

                <td class="text-right">
                    {{ $fmt2($showRate) }}
                </td>

                <td class="text-right">
                    {{ $fmt2($lineTax) }}
                </td>

                <td class="text-right">
                    {{ $fmt2($lineTotal) }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr>
        <td>Taxable Amount</td>
        <td class="text-right">₹ {{ $fmt2($taxable) }}</td>
    </tr>

    @if($isIGST)
        <tr>
            <td>IGST</td>
            <td class="text-right">₹ {{ $fmt2($igst_db) }}</td>
        </tr>
    @else
        <tr>
            <td>CGST</td>
            <td class="text-right">₹ {{ $fmt2($cgst_db) }}</td>
        </tr>

        <tr>
            <td>SGST</td>
            <td class="text-right">₹ {{ $fmt2($sgst_db) }}</td>
        </tr>
    @endif

    <tr>
        <td>Received</td>
        <td class="text-right">₹ {{ $fmt2($receivedTot) }}</td>
    </tr>

    <tr>
        <td>Balance</td>
        <td class="text-right">₹ {{ $fmt2($balanceNow) }}</td>
    </tr>

    <tr>
        <td><strong>Total</strong></td>
        <td class="text-right"><strong>₹ {{ $fmt2($finalTotal) }}</strong></td>
    </tr>
</table>

<div class="mt15">
    <strong class="primary-text">Amount in Words:</strong>
    {{ inr_words($finalTotal) }}
</div>

<table class="mt15">
    <tr>
        <td style="width:60%; vertical-align:top;">
            @if($showTerms && !empty($inv->terms))
                <strong class="primary-text">Terms & Conditions</strong><br>
                <span class="muted">{!! nl2br(e($inv->terms)) !!}</span>
            @endif
        </td>

        <td style="width:40%; vertical-align:top;" class="text-right sign">
            @if($showSignature)
                @if(!empty($invoiceSignatureUrl))
                    <img src="{{ $invoiceSignatureUrl }}" alt="Signature"><br>
                @endif

                <strong>Authorised Signatory</strong><br>
                <span class="muted">{{ $b->name ?? 'Real Victory Groups' }}</span>
            @endif
        </td>
    </tr>
</table>

</body>
</html>