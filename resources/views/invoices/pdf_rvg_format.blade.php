{{-- resources/views/invoices/pdf_simple.blade.php --}}

@php
    $ts = $templateSetting ?? null;

    $termsText = $inv->terms ?? null;

    // ✅ Template setting defaults
    $primaryColor   = $ts->primary_color ?? '#d60000';
    $secondaryColor = $ts->secondary_color ?? '#dbd9d6';
    $textColor      = $ts->text_color ?? '#111111';

    // ✅ Hindi PDF ke liye force font
    // $fontFamily = 'NotoSansDevanagari';

    // $fontFamily = 'NotoSansDevanagari';
    $fontFamily = 'freeserif';

    $showLogo      = $ts->show_logo ?? true;
    $showTagline   = $ts->show_tagline ?? true;
    $showSignature = $ts->show_signature ?? true;
    $showTerms     = $ts->show_terms ?? true;

    // ✅ Windows/Linux dono ke liye path safe
    // $fontRegularPath = str_replace('\\', '/', public_path('fonts/NotoSansDevanagari-Regular.ttf'));
    // $fontBoldPath    = str_replace('\\', '/', public_path('fonts/NotoSansDevanagari-Bold.ttf'));
    // $fontRegularPath = str_replace('\\', '/', public_path('storage/fonts/NotoSansDevanagari-Regular.ttf'));
    // $fontBoldPath    = str_replace('\\', '/', public_path('storage/fonts/NotoSansDevanagari-Bold.ttf'));
    $fontRegularPath = str_replace('\\', '/', storage_path('fonts/NotoSansDevanagari-Regular.ttf'));
$fontBoldPath    = str_replace('\\', '/', storage_path('fonts/NotoSansDevanagari-Bold.ttf'));
@endphp

@php
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

    $pay = $payRow ?? null;

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

                if ($n == 0) {
                    return '';
                }

                if ($n < 20) {
                    return $ones[$n];
                }

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

            if ($words === '') {
                $words = 'Zero';
            }

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
@endphp

<!doctype html>
<html lang="hi">
<head>
    <meta charset="utf-8">
    <title>{{ ucfirst($type) }} {{ $type != 'quotation' ? 'Invoice' : '' }} {{ $invoiceNo }}</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: freeserif, sans-serif;
            font-size: 12px;
            color: {{ $textColor }};
            margin: 0;
            padding: 0;
        }

        .wrap {
            margin: 10px;
            padding: 10px 12px 12px 12px;
        }

        .muted {
            color: {{ $textColor }};
        }

        .red {
            color: {{ $primaryColor }};
        }

        .bold {
            font-weight: bold;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .smalltag {
            display: inline-block;
            border: 1px solid #999;
            padding: 2px 6px;
            font-size: 10.5px;
            margin-left: 6px;
            color: #333;
        }

        .headerRow td {
            vertical-align: top;
            font-size: 12px;
        }

        .line-red {
            border-top: 6px solid {{ $primaryColor }};
            margin: 0;
        }

        .greybar {
            background: {{ $secondaryColor }};
            padding: 8px 10px;
            margin: 0;
            font-size: 10.5px;
        }

        .billto {
            font-size: 12.5px;
            margin-bottom: 8px;
        }

        .billto .name {
            font-size: 10.5px;
            font-weight: bold;
        }

        .svc {
            border-top: 2px solid {{ $primaryColor }};
            border-bottom: 2px solid {{ $primaryColor }};
            margin-top: 6px;
            font-size: 12px;
        }

        .svc th,
        .svc td {
            padding: 6px 6px;
            font-size: 12.5px;
        }

        .svc thead th {
            text-align: left;
            font-weight: bold;
        }

        .svc thead th.right,
        .svc td.right {
            text-align: right;
        }

        .svc tbody td {
            border-top: 1px solid #cfcfcf;
            vertical-align: top;
        }

        .svc .descSmall {
            font-size: 10.3px;
            color: #555;
            margin-top: 2px;
        }

        .subtotalRow {
            border-top: 2px solid {{ $primaryColor }};
            border-bottom: 2px solid {{ $primaryColor }};
        }

        .subtotalRow td {
            padding: 6px 6px;
            font-size: 12.5px;
            font-weight: bold;
        }

        .bottom {
            margin-top: 10px;
            width: 100%;
            display: block;
        }

        .bottom:after {
            content: "";
            display: block;
            clear: both;
        }

        .leftCol {
            width: 48%;
            float: left;
        }

        .rightCol {
            width: 52%;
            float: right;
        }

        .termsLeft {
            font-size: 12px;
        }

        .termsTitle {
            font-weight: bold;
            margin-bottom: 6px;
        }

        .termsText {
            line-height: 1.4;
            white-space: normal;
        }

        .totalsBox {
            width: 100%;
            margin-left: 0;
            font-size: 12.5px;
        }

        .totalsBox td {
            padding: 4px 6px;
        }

        .totalsBox .lab {
            text-align: right;
            color: {{ $textColor }};
        }

        .totalsBox .val {
            text-align: right;
            font-weight: bold;
            width: 120px;
            border-bottom: 1px solid #666;
            white-space: nowrap;
        }

        .totalsBox .strong .lab,
        .totalsBox .strong .val {
            font-weight: bold;
        }

        .amountWords {
            width: 100%;
            margin-left: 0;
            margin-top: 10px;
            font-size: 12.2px;
            text-align: right;
        }

        .signArea {
            width: 100%;
            margin-left: 0;
            margin-top: 14px;
            text-align: right;
            font-size: 10px;
        }

        .signImg {
            height: 34px;
            width: auto;
            margin-bottom: 6px;
        }

        .auth {
            font-weight: bold;
            margin-top: 6px;
        }
    </style>
</head>

<body>
<div class="wrap">

    {{-- HEADER --}}
    <table class="headerRow" style="width:100%; border-collapse:collapse; margin-bottom:8px;">
        <tr>
            <td colspan="3" style="width:100%; vertical-align:top;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="text-align:left; width:60%;">
                            <span class="bold" style="font-size:11px;">
                                {{ strtoupper($type) }} {{ $type != 'quotation' ? 'INVOICE' : '' }}
                            </span>

                            <span class="smalltag" style="font-size:11px;">
                                ORIGINAL FOR RECIPIENT
                            </span>
                        </td>

                        <td style="text-align:right; width:40%;">
                            @if($showTagline && (($b->name ?? '') == 'Real Victory Groups'))
                                <span class="bold" style="font-size:11px;">
                                    Think Outside The Box
                                </span>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td style="width:18%; vertical-align:top;">
                @if($showLogo && !empty($logo))
                    <img src="{{ $logo }}" alt="Logo" style="height:auto;width:100%;">
                @endif
            </td>

            <td style="width:62%; vertical-align:top;">
                <div class="red bold" style="font-size:24px; margin-top:6px;">
                    {{ $b->name ?? 'Real Victory Groups' }}
                </div>

                <div class="muted" style="font-size:14.8px; margin-top:2px;">
                    {{ $b_addr }}
                    @if($b_city), {{ $b_city }}@endif
                    @if($b_state), {{ $b_state }}@endif
                    @if($b_pin) ({{ $b_pin }})@endif
                </div>

                <div class="muted" style="font-size:12.8px; margin-top:2px;">
                    <span class="bold">Mobile:</span> {{ $b_mobile ?: '-' }}
                    &nbsp;&nbsp;&nbsp;
                    <span class="bold">GSTIN:</span> {{ $b_gstin ?: '-' }}
                    <br>
                    <span class="bold">Email:</span> {{ $b_email ?: '-' }}
                </div>
            </td>

            <td style="width:20%;"></td>
        </tr>
    </table>

    <div class="line-red"></div>

    {{-- INVOICE BAR --}}
    <div class="greybar">
        <table>
            <tr>
                <td class="bold" style="font-size:12px;">
                    {{ $type != 'quotation' ? 'Invoice' : 'Quotation' }} No.:
                    <span class="muted" style="font-size:12px;">{{ $invoiceNo }}</span>
                </td>

                <td class="right bold" style="font-size:12px;">
                    {{ $type != 'quotation' ? 'Invoice' : 'Quotation' }} Date:
                    <span class="muted" style="font-size:12px;">{{ $dmy($invoiceDate) }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- BILL TO --}}
    <div class="billto">
        <div class="bold">BILL TO</div>

        <div class="name">
            {{ strtoupper($c->name ?? '-') }}
        </div>

        <div class="muted" style="font-size:12px;">
            {{ $c->address ?? '-' }}
            @if(!empty($c->city)), {{ $c->city }}@endif
            @if(!empty($c->state)), {{ $c->state }}@endif
            @if(!empty($c->pin)), {{ $c->pin }}@endif
        </div>

        <div class="muted" style="margin-top:2px; font-size:12.2px;">
            <span class="bold" style="font-size:12px;">Mobile:</span> {{ $mobile ?: '-' }}<br>
            <span class="bold" style="font-size:12px;">GSTIN:</span> {{ $gstin ?: '-' }}<br>
            <span class="bold" style="font-size:12px;">PAN Number:</span> {{ $pan ?: '-' }}<br>
            <span class="bold" style="font-size:12px;">Place of Supply:</span> {{ $pos ?: '-' }}
        </div>
    </div>

    {{-- SERVICES TABLE --}}
    @php
        $single = ($items->count() === 1);
    @endphp

    <table class="svc">
        <thead>
            <tr>
                <th style="width:52%; font-size:12px;">SERVICES</th>
                <th style="width:10%; font-size:12px;">SAC</th>
                <th style="width:10%; font-size:12px;">QTY.</th>
                <th class="right" style="width:10%; font-size:12px;">RATE</th>
                <th class="right" style="width:10%; font-size:12px;">TAX</th>
                <th class="right" style="width:8%; font-size:12px;">AMOUNT</th>
            </tr>
        </thead>

        <tbody>
            @foreach($items as $it)
                @php
                    $name = $it->item->name ?? '';
                    $desc = $it->description ?? '';
                    $note = trim((string)($it->note ?? $it->extra_line ?? ''));
                    $sac  = $it->sac_code ?? $it->hsn_code ?? $it->sac ?? '';

                    $qty = (float)($it->quantity ?? 1);
                    $qty = $qty > 0 ? $qty : 1;

                    $lineBase = (float)($it->line_base ?? 0);

                    if ($lineBase <= 0) {
                        $r = (float)($it->rate ?? 0);
                        $lineBase = $r * $qty;
                    }

                    $showRate = $single ? $taxable : $lineBase;

                    $lineTax = (float)($it->tax_amount ?? $it->line_tax ?? 0);

                    if ($lineTax <= 0 && isset($it->rate, $it->tax_percent)) {
                        $lineTax = ((float)$it->rate * (float)$it->tax_percent) / 100;
                    }

                    $lineTax = round($lineTax, 2);

                    $lineTotal = (float)($it->amount ?? $it->line_total ?? 0);

                    if ($lineTotal <= 0) {
                        $lineTotal = $lineBase + $lineTax;
                    }

                    $lineTotal = round($lineTotal, 2);

                    if ($single && $lineTax <= 0) {
                        $lineTax = $finalTax;
                    }

                    if ($single && $lineTotal <= 0) {
                        $lineTotal = $finalTotal;
                    }
                @endphp

                <tr>
                    <td>
                        <div class="bold">{{ $name ?: '-' }}</div>

                        @if(!empty($desc))
                            <div class="descSmall">{{ $desc }}</div>
                        @endif

                        @if(!empty($note))
                            <div class="descSmall">{{ $note }}</div>
                        @endif
                    </td>

                    <td>{{ $sac }}</td>
                    <td>{{ $qty }} {{ $it->unit ?? '' }}</td>
                    <td class="right">{{ $fmt2($showRate) }}</td>

                    <td class="right">
                        {{ $fmt2($lineTax) }}

                        <div class="muted" style="font-size:12px;">
                            ({{ $it->tax_percent ?? 0 }}%)
                        </div>
                    </td>

                    <td class="right">{{ $fmt2($lineTotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- SUBTOTAL --}}
    <table class="subtotalRow" style="margin-top:14px;">
        <tr>
            <td style="width:52%;">SUBTOTAL</td>
            <td style="width:10%;">{{ $fmt0($items->count() ?: 1) }}</td>
            <td style="width:8%;"></td>
            <td style="width:10%;"></td>
            <td style="width:10%; white-space:nowrap;" class="right">
                &#8377; {{ $fmt2($finalTax) }}
            </td>
            <td style="width:10%; white-space:nowrap;" class="right">
                &#8377; {{ $fmt2($finalTotal) }}
            </td>
        </tr>
    </table>

    {{-- BOTTOM --}}
    <div class="bottom">

        {{-- LEFT --}}
        <div class="leftCol">
            @if($showTerms && !empty($inv->terms))
                <div class="termsLeft">
                    <div class="termsTitle" style="font-size:12px;">
                        TERMS & CONDITIONS
                    </div>

                    <div class="termsText" style="font-size:12px;">
                        {!! nl2br(e($inv->terms)) !!}
                    </div>
                </div>
            @endif
        </div>

        {{-- RIGHT --}}
        <div class="rightCol">
            <table class="totalsBox">
                <tr>
                    <td class="lab">Taxable Amount</td>
                    <td class="val">&#8377; {{ $fmt2($taxable) }}</td>
                </tr>

                @if($isIGST)
                    <tr>
                        <td class="lab">IGST</td>
                        <td class="val">&#8377; {{ $fmt2($igst_db) }}</td>
                    </tr>
                @else
                    <tr>
                        <td class="lab">CGST</td>
                        <td class="val">&#8377; {{ $fmt2($cgst_db) }}</td>
                    </tr>

                    <tr>
                        <td class="lab">SGST</td>
                        <td class="val">&#8377; {{ $fmt2($sgst_db) }}</td>
                    </tr>
                @endif

                <tr class="strong">
                    <td class="lab">Total Amount</td>
                    <td class="val">&#8377; {{ $fmt2($finalTotal) }}</td>
                </tr>

                <tr>
                    <td class="lab">Received Amount</td>
                    <td class="val">&#8377; {{ $fmt2($receivedTot) }}</td>
                </tr>

                <tr class="strong">
                    <td class="lab">Balance</td>
                    <td class="val">&#8377; {{ $fmt2($balanceNow) }}</td>
                </tr>
            </table>

            <div class="amountWords">
                <div class="bold muted" style="margin-bottom:3px;">
                    Total Amount (in words)
                </div>

                <div class="bold">
                    {{ inr_words($finalTotal) }}
                </div>
            </div>

            @php
                $invoiceSignature = $inv->signature ?? null;

                $invoiceSignatureUrl = $invoiceSignature
                    ? (\Illuminate\Support\Str::startsWith($invoiceSignature, ['http://', 'https://'])
                        ? $invoiceSignature
                        : public_path('storage/' . $invoiceSignature))
                    : null;
            @endphp

            <div class="signArea">
                @if($showSignature && !empty($invoiceSignatureUrl))
                    <img src="{{ $invoiceSignatureUrl }}" class="signImg" alt="Signature">
                @endif

                <div class="auth" style="font-size:12px;">
                    AUTHORISED SIGNATORY FOR
                </div>

                <div class="muted" style="font-size:12px;">
                    {{ $b->name ?? 'Real Victory Groups' }}
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>