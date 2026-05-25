@php
    $termsText = $inv->terms ?? null;

    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? ($inv->business ?? null);
    $c = $client ?? ($inv->client ?? null);
    $items = $items ?? collect();

    /*
    |--------------------------------------------------------------------------
    | Dynamic Template Setting
    |--------------------------------------------------------------------------
    | Controller se $templateSetting aa raha hai.
    | Agar customization record nahi mila to original default colors use honge.
    */
    $setting = $templateSetting ?? null;

    $primaryColor   = $setting->primary_color ?? '#111827';
    $textColor      = $setting->text_color ?? '#111827';
    $mutedColor     = $setting->muted_color ?? '#6b7280';
    $borderColor    = $setting->border_color ?? '#374151';
    $secondaryColor = $setting->secondary_color ?? '#d1d5db';
    $lightBgColor   = $setting->light_bg_color ?? '#9ca3af';
    $softBgColor    = $setting->soft_bg_color ?? '#ffffff';
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

    $pay = $payRow ?? null;
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

html,
body {
    margin: 0;
    padding: 0;
    width: 210mm;
    min-height: 297mm;
    font-family: "{{ $fontFamily }}", DejaVu Sans, sans-serif;
    font-size: 12px;
    color: {{ $textColor }};
}

.invoice-wrap {
    width: 210mm;
    display: table;
    table-layout: fixed;
}

.sidebar {
    display: table-cell;
    width: 28%;
    background: {{ $primaryColor }};
    color: #fff;
    padding: 22px 16px;
    vertical-align: top;
}

.content {
    display: table-cell;
    width: 72%;
    background: {{ $softBgColor }};
    padding: 25px 24px;
    vertical-align: top;
}

.logo {
    text-align: center;
    margin-bottom: 18px;
}

.logo img {
    max-width: 110px;
    max-height: 85px;
}

.company {
    font-size: 22px;
    font-weight: bold;
    line-height: 1.3;
    text-align: center;
    margin-bottom: 18px;
}

.line {
    border-top: 2px solid #fff;
    margin: 14px 0 20px;
}

.side-title {
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
    margin-top: 18px;
    margin-bottom: 8px;
}

.side-text {
    font-size: 11px;
    line-height: 1.65;
    color: {{ $secondaryColor }};
}

.doc-title {
    font-size: 34px;
    font-weight: bold;
    color: {{ $primaryColor }};
    letter-spacing: 1px;
    border-bottom: 3px solid {{ $primaryColor }};
    padding-bottom: 8px;
    margin-bottom: 16px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

.meta td {
    border: 1px solid {{ $lightBgColor }};
    padding: 8px 10px;
    font-size: 12px;
}

.bill-box {
    border: 2px solid {{ $borderColor }};
    padding: 12px 14px;
    margin-top: 16px;
    margin-bottom: 16px;
    line-height: 1.6;
}

.bill-title {
    font-weight: bold;
    text-transform: uppercase;
    margin-bottom: 6px;
}

.items th {
    background: {{ $primaryColor }};
    color: #fff;
    padding: 9px 8px;
    font-size: 12px;
    text-align: left;
}

.items td {
    border-bottom: 1px solid {{ $secondaryColor }};
    padding: 8px;
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
    width: 48%;
    margin-left: auto;
    margin-top: 18px;
    border: 2px solid {{ $borderColor }};
}

.summary td {
    padding: 8px 10px;
    border-bottom: 1px solid {{ $lightBgColor }};
}

.summary .total td {
    background: {{ $primaryColor }};
    color: #fff;
    font-weight: bold;
    font-size: 14px;
}

.words {
    margin-top: 20px;
    line-height: 1.6;
}

.terms {
    margin-top: 18px;
    line-height: 1.6;
}

.sign {
    margin-top: 35px;
    text-align: right;
}

.sign img {
    max-height: 55px;
    margin-bottom: 6px;
}

.sign-line {
    border-top: 1px solid {{ $primaryColor }};
    width: 180px;
    margin-left: auto;
    padding-top: 8px;
}

.no-break {
    page-break-inside: avoid;
}
</style>
</head>

<body>

<div class="invoice-wrap">

    <div class="sidebar">

        @if($showLogo)
            <div class="logo">
                @if(!empty($logo))
                    <img src="{{ $logo }}" alt="Logo">
                @endif
            </div>
        @endif

        <div class="company">
            {{ $b->name ?? $b->business_name ?? 'Real Victory Groups' }}
        </div>

        <div class="line"></div>

        <div class="side-title">Address</div>
        <div class="side-text">
            {{ $b_addr ?: '-' }}
            @if($b_city), {{ $b_city }} @endif
            @if($b_state), {{ $b_state }} @endif
            @if($b_pin) - {{ $b_pin }} @endif
        </div>

        <div class="side-title">Contact</div>
        <div class="side-text">
            Mobile: {{ $b_mobile ?: '-' }}<br>
            Email: {{ $b_email ?: '-' }}<br>
            GSTIN: {{ $b_gstin ?: '-' }}
        </div>

        @if(!empty($inv->account_manager))
            <div class="side-title">Account Manager</div>
            <div class="side-text">
                {{ $inv->account_manager }}
            </div>
        @endif

    </div>

    <div class="content">

        <div class="doc-title">
            {{ strtoupper($type) }} {{ $type != 'quotation' ? 'INVOICE' : '' }}
        </div>

        <table class="meta no-break">
            <tr>
                <td>
                    <strong>{{ $type != 'quotation' ? 'Invoice' : 'Quotation' }} No.:</strong>
                    {{ $invoiceNo }}
                </td>
                <td>
                    <strong>Date:</strong>
                    {{ $dmy($invoiceDate) }}
                </td>
            </tr>
        </table>

        <div class="bill-box no-break">
            <div class="bill-title">Bill To</div>

            <strong>{{ strtoupper($c->name ?? '-') }}</strong><br>

            {{ $c->address ?? '-' }}
            @if(!empty($c->city)), {{ $c->city }} @endif
            @if(!empty($c->state)), {{ $c->state }} @endif
            @if(!empty($c->pin)) - {{ $c->pin }} @endif

            <br>
            Mobile: {{ $mobile ?: '-' }}<br>
            GSTIN: {{ $gstin ?: '-' }}<br>
            PAN: {{ $pan ?: '-' }}<br>
            Place: {{ $pos ?: '-' }}
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th style="width:32%;">Service</th>
                    <th style="width:13%;">HSN/SAC</th>
                    <th style="width:9%; text-align:center;">Qty</th>
                    <th style="width:14%;" class="text-right">Rate</th>
                    <th style="width:14%;" class="text-right">Tax</th>
                    <th style="width:18%;" class="text-right">Amount</th>
                </tr>
            </thead>

            <tbody>
                @forelse($items as $it)
                    @php
                        $name = $it->item->name ?? $it->name ?? 'Service';
                        $desc = $it->description ?? '';
                        $sac  = $it->sac_code ?? $it->hsn_code ?? $it->sac ?? '';
                        $qty  = (float)($it->quantity ?? 1);
                        $qty  = $qty > 0 ? $qty : 1;

                        $rate = (float)($it->rate ?? 0);
                        $lineBase = (float)($it->line_base ?? ($rate * $qty));

                        $lineTax = (float)($it->tax_amount ?? 0);

                        if ($lineTax <= 0 && !empty($it->tax_percent)) {
                            $lineTax = round(($lineBase * (float)$it->tax_percent) / 100, 2);
                        }

                        $lineTotal = (float)($it->amount ?? $it->line_total ?? 0);

                        if ($lineTotal <= 0) {
                            $lineTotal = $lineBase + $lineTax;
                        }

                        if ($single) {
                            $lineBase  = $taxable;
                            $lineTax   = $finalTax;
                            $lineTotal = $finalTotal;
                        }
                    @endphp

                    <tr>
                        <td>
                            <strong>{{ $name }}</strong>
                            @if($desc)
                                <div class="desc">{{ $desc }}</div>
                            @endif
                        </td>
                        <td>{{ $sac }}</td>
                        <td style="text-align:center;">{{ $qty }}</td>
                        <td class="text-right">{{ $fmt2($lineBase) }}</td>
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
                    <td>IGST</td>
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
                <td>Round Off</td>
                <td class="text-right">₹ {{ $fmt2($inv->round_off ?? 0) }}</td>
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

        <div class="words no-break">
            <strong>Total Amount (in words):</strong><br>
            {{ inr_words($finalTotal) }} Only
        </div>

        @if($showTerms && !empty($termsText))
            <div class="terms no-break">
                <strong>Terms & Conditions:</strong><br>
                {!! nl2br(e($termsText)) !!}
            </div>
        @endif

        @if($showSignature)
            <div class="sign no-break">
                @if(!empty($invoiceSignatureUrl))
                    <img src="{{ $invoiceSignatureUrl }}" alt="Signature"><br>
                @elseif(!empty($sign))
                    <img src="{{ $sign }}" alt="Signature"><br>
                @endif

                <div class="sign-line">
                    Authorised Signatory
                </div>
            </div>
        @endif

    </div>
</div>

</body>
</html>