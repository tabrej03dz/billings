@php
    $termsText = $inv->terms ?? null;

    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? ($inv->business ?? null);
    $c = $client ?? ($inv->client ?? null);
    $items = $items ?? collect();

    $setting = $templateSetting ?? null;

    $primaryColor   = $setting->primary_color ?? '#1c1917';
    $textColor      = $setting->text_color ?? '#2b2b2b';
    $mutedColor     = $setting->muted_color ?? '#7c6f64';
    $borderColor    = $setting->border_color ?? '#d6c8b4';
    $secondaryColor = $setting->secondary_color ?? '#f4ead7';
    $lightBgColor   = $setting->light_bg_color ?? '#fffdf9';
    $softBgColor    = $setting->soft_bg_color ?? '#faf7f2';
    $accentColor    = $setting->accent_color ?? '#c9a227';
    $fontFamily     = $setting->font_family ?? 'DejaVu Sans';

    $showLogo      = (bool) ($setting->show_logo ?? true);
    $showSignature = (bool) ($setting->show_signature ?? true);
    $showTerms     = (bool) ($setting->show_terms ?? true);

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
        @page {
            margin: 0;
            size: A4;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            padding: 18px;
            font-family: "{{ $fontFamily }}", DejaVu Sans, sans-serif;
            font-size: 12px;
            background: {{ $softBgColor }};
            color: {{ $textColor }};
        }

        .page {
            background: #fff;
            border: 1px solid {{ $borderColor }};
            padding: 18px;
        }

        .hero {
            background: {{ $primaryColor }};
            color: #fff;
            padding: 18px;
            margin: -18px -18px 18px -18px;
        }

        .heroRow:after {
            content: "";
            display: block;
            clear: both;
        }

        .left {
            float: left;
            width: 65%;
        }

        .right {
            float: right;
            width: 30%;
            text-align: right;
        }

        .company {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }

        .docType {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
        }

        .goldLine {
            height: 3px;
            background: {{ $accentColor }};
            margin: 14px 0 18px 0;
        }

        .panel {
            border: 1px solid {{ $borderColor }};
            padding: 14px;
            margin-bottom: 14px;
            background: {{ $lightBgColor }};
        }

        .panelTitle {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: {{ $accentColor }};
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .items th {
            background: {{ $secondaryColor }};
            color: {{ $accentColor }};
            border-top: 2px solid {{ $accentColor }};
            border-bottom: 2px solid {{ $accentColor }};
            padding: 9px 8px;
            text-align: left;
        }

        .items td {
            border-bottom: 1px solid {{ $borderColor }};
            padding: 9px 8px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .desc {
            font-size: 10px;
            color: {{ $mutedColor }};
            margin-top: 3px;
        }

        .summary {
            width: 44%;
            margin-left: auto;
            margin-top: 16px;
        }

        .summary td {
            padding: 9px 10px;
            border: 1px solid {{ $borderColor }};
        }

        .summary .total {
            background: {{ $primaryColor }};
            color: #fff;
            font-weight: 700;
        }

        .bottomLeft {
            width: 56%;
            float: left;
        }

        .bottomRight {
            width: 38%;
            float: right;
            text-align: right;
        }

        .sign img {
            max-height: 48px;
        }

        .clearfix:after {
            content: "";
            display: block;
            clear: both;
        }

        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
<div class="page">
    <div class="hero no-break">
        <div class="heroRow">
            <div class="left">
                <div class="company">{{ $b->name ?? $b->business_name ?? 'Real Victory Groups' }}</div>
                <div>
                    {{ $b_addr }}
                    @if($b_city), {{ $b_city }}@endif
                    @if($b_state), {{ $b_state }}@endif
                    @if($b_pin) - {{ $b_pin }}@endif
                </div>
                <div style="margin-top:5px;">Mobile: {{ $b_mobile ?: '-' }} | GSTIN: {{ $b_gstin ?: '-' }}</div>
                <div>Email: {{ $b_email ?: '-' }}</div>
            </div>

            <div class="right">
                <div class="docType">{{ strtoupper($type) }}</div>
                <div>{{ $type != 'quotation' ? 'Invoice' : 'Quotation' }}</div>

                @if($showLogo && !empty($logo))
                    <img src="{{ $logo }}" alt="Logo" style="max-width:95px; max-height:65px; margin-top:8px;">
                @endif
            </div>
        </div>
    </div>

    <div class="goldLine"></div>

    <div class="panel no-break">
        <table>
            <tr>
                <td style="width:60%; vertical-align:top;">
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
                    <div class="panelTitle">{{ $type != 'quotation' ? 'Invoice' : 'Quotation' }} Details</div>
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
            @forelse($items as $it)
                @php
                    $name = $it->item->name ?? $it->name ?? '';
                    $desc = $it->description ?? '';
                    $note = trim((string)($it->note ?? $it->extra_line ?? ''));

                    $sac = $it->sac_code ?? $it->hsn_code ?? $it->sac ?? '';

                    $qty = (float)($it->quantity ?? 1);
                    $qty = $qty > 0 ? $qty : 1;

                    $lineBase = (float)($it->line_base ?? 0);

                    if ($lineBase <= 0) {
                        $r = (float)($it->rate ?? 0);
                        $lineBase = $r * $qty;
                    }

                    $showRate = $single ? $taxable : $lineBase;

                    $lineTax = (float)($it->tax_amount ?? 0);

                    if ($lineTax <= 0 && !empty($it->tax_percent)) {
                        $lineTax = round(($lineBase * (float)$it->tax_percent) / 100, 2);
                    }

                    $lineTotal = (float)($it->amount ?? $it->line_total ?? 0);

                    if ($lineTotal <= 0) {
                        $lineTotal = $lineBase + $lineTax;
                    }

                    if ($single) {
                        $showRate = $taxable;
                        $lineTax = $finalTax;
                        $lineTotal = $finalTotal;
                    }
                @endphp

                <tr>
                    <td>
                        <strong>{{ $name ?: '-' }}</strong>

                        @if($desc)
                            <div class="desc">{{ $desc }}</div>
                        @endif

                        @if($note)
                            <div class="desc">{{ $note }}</div>
                        @endif
                    </td>

                    <td>{{ $sac }}</td>
                    <td>{{ $qty }} {{ $it->unit ?? '' }}</td>
                    <td class="text-right">{{ $fmt2($showRate) }}</td>
                    <td class="text-right">{{ $fmt2($lineTax) }}</td>
                    <td class="text-right">{{ $fmt2($lineTotal) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;">No items found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary no-break">
        <tr>
            <td>Taxable Amount</td>
            <td class="text-right">₹ {{ $fmt2($taxable) }}</td>
        </tr>

        @if($isIGST)
            <tr>
                <td>IGST @if($taxPercent) ({{ $fmt0($taxPercent) }}%) @endif</td>
                <td class="text-right">₹ {{ $fmt2($igst_db) }}</td>
            </tr>
        @else
            <tr>
                <td>CGST @if($taxPercent) ({{ $fmt0($taxPercent / 2) }}%) @endif</td>
                <td class="text-right">₹ {{ $fmt2($cgst_db) }}</td>
            </tr>

            <tr>
                <td>SGST @if($taxPercent) ({{ $fmt0($taxPercent / 2) }}%) @endif</td>
                <td class="text-right">₹ {{ $fmt2($sgst_db) }}</td>
            </tr>
        @endif

        @if(!empty($discount_total) && (float)$discount_total > 0)
            <tr>
                <td>Discount</td>
                <td class="text-right">₹ {{ $fmt2($discount_total) }}</td>
            </tr>
        @endif

        @if(!empty($charges_total) && (float)$charges_total > 0)
            <tr>
                <td>Additional Charges</td>
                <td class="text-right">₹ {{ $fmt2($charges_total) }}</td>
            </tr>
        @endif

        @if(!empty($tcs_amount) && (float)$tcs_amount > 0)
            <tr>
                <td>TCS @if(!empty($tcs_percent)) ({{ $fmt2($tcs_percent) }}%) @endif</td>
                <td class="text-right">₹ {{ $fmt2($tcs_amount) }}</td>
            </tr>
        @endif

        @if(!empty($less_amount) && (float)$less_amount > 0)
            <tr>
                <td>Less Amount</td>
                <td class="text-right">₹ {{ $fmt2($less_amount) }}</td>
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

        <tr class="total">
            <td>Total</td>
            <td class="text-right">₹ {{ $fmt2($finalTotal) }}</td>
        </tr>
    </table>

    <div style="margin-top:18px;" class="panel no-break">
        <div class="panelTitle">Amount in Words</div>
        {{ inr_words($finalTotal) }} Only
    </div>

    <div style="margin-top:18px;" class="clearfix no-break">
        <div class="bottomLeft">
            @if($showTerms && !empty($termsText))
                <div class="panel">
                    <div class="panelTitle">Terms & Conditions</div>
                    {!! nl2br(e($termsText)) !!}
                </div>
            @endif
        </div>

        @if($showSignature)
            <div class="bottomRight sign">
                @if(!empty($invoiceSignatureUrl))
                    <img src="{{ $invoiceSignatureUrl }}" alt="Signature"><br>
                @elseif(!empty($sign))
                    <img src="{{ $sign }}" alt="Signature"><br>
                @endif

                <strong>Authorised Signatory</strong><br>
                {{ $b->name ?? $b->business_name ?? 'Real Victory Groups' }}
            </div>
        @endif
    </div>
</div>
</body>
</html>
