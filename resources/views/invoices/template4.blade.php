@php
    $termsText = $inv->terms ?? null;

    $primaryColor   = $templateSetting->primary_color ?? '#111827';
    $textColor      = $templateSetting->text_color ?? '#111827';
    $mutedColor     = $templateSetting->muted_color ?? '#6b7280';
    $borderColor    = $templateSetting->border_color ?? '#d1d5db';
    $secondaryColor = $templateSetting->secondary_color ?? '#e5e7eb';
    $softBgColor    = $templateSetting->soft_bg_color ?? '#ffffff';
    $fontFamily     = $templateSetting->font_family ?? 'DejaVu Sans';

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
    $receivedTot = $inv->received_amount ?? 0;
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

            $twoDigits = function($n) use ($ones,$tens){
                $n = (int)$n;
                if ($n == 0) return '';
                if ($n < 20) return $ones[$n];
                return trim($tens[(int)($n / 10)] . ' ' . $ones[$n % 10]);
            };

            $parts = [];

            if ($rupees >= 10000000) {
                $cr = (int) floor($rupees / 10000000);
                $parts[] = $twoDigits($cr) . ' Crore';
                $rupees %= 10000000;
            }

            if ($rupees >= 100000) {
                $lk = (int) floor($rupees / 100000);
                $parts[] = $twoDigits($lk) . ' Lakh';
                $rupees %= 100000;
            }

            if ($rupees >= 1000) {
                $th = (int) floor($rupees / 1000);
                $parts[] = $twoDigits($th) . ' Thousand';
                $rupees %= 1000;
            }

            if ($rupees >= 100) {
                $hd = (int) floor($rupees / 100);
                $parts[] = $ones[$hd] . ' Hundred';
                $rupees %= 100;
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
            padding:20px;
        }

        table{ width:100%; border-collapse:collapse; }

        .top{ margin-bottom:18px; }
        .top td{ vertical-align:top; }

        .company{
            font-size:26px;
            font-weight:700;
            color:{{ $primaryColor }};
        }

        .docTitle{
            font-size:22px;
            font-weight:700;
            text-align:right;
            color:{{ $primaryColor }};
        }

        .muted{ color:{{ $mutedColor }}; }

        .line{
            border-top:1px solid {{ $borderColor }};
            margin:14px 0;
        }

        .label{
            font-size:10px;
            color:{{ $mutedColor }};
            text-transform:uppercase;
            letter-spacing:.8px;
        }

        .value{
            font-size:12px;
            font-weight:700;
            color:{{ $primaryColor }};
        }

        .infoGrid td{
            padding:8px 0;
            vertical-align:top;
        }

        .items th{
            border-bottom:2px solid {{ $primaryColor }};
            padding:8px 6px;
            text-align:left;
            font-size:11px;
            text-transform:uppercase;
            color:{{ $primaryColor }};
        }

        .items td{
            border-bottom:1px solid {{ $secondaryColor }};
            padding:8px 6px;
            vertical-align:top;
            color:{{ $textColor }};
        }

        .text-right{ text-align:right; }

        .small{
            font-size:10px;
            color:{{ $mutedColor }};
        }

        .totals{
            width:38%;
            margin-left:auto;
            margin-top:14px;
        }

        .totals td{
            padding:7px 0;
            border-bottom:1px solid {{ $secondaryColor }};
            color:{{ $textColor }};
        }

        .grand td{
            font-size:14px;
            font-weight:700;
            border-bottom:2px solid {{ $primaryColor }};
            padding-top:10px;
            color:{{ $primaryColor }};
        }
    </style>
</head>

<body>

<table class="top">
    <tr>
        <td style="width:65%;">
            <div class="company">{{ $b->name ?? 'Real Victory Groups' }}</div>

            <div class="muted">
                {{ $b_addr }}
                @if($b_city), {{ $b_city }}@endif
                @if($b_state), {{ $b_state }}@endif
                @if($b_pin) - {{ $b_pin }}@endif
            </div>

            <div class="muted">
                GSTIN: {{ $b_gstin ?: '-' }} | Mobile: {{ $b_mobile ?: '-' }}
            </div>

            <div class="muted">
                Email: {{ $b_email ?: '-' }}
            </div>
        </td>

        <td style="width:35%; text-align:right;">
            <div class="docTitle">{{ strtoupper($type) }}</div>

            <div class="muted">
                {{ $type != 'quotation' ? 'Invoice' : 'Quotation' }}
            </div>

            @if($showLogo && !empty($logo))
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

            <div>
                {{ $c->address ?? '-' }}
                @if(!empty($c->city)), {{ $c->city }}@endif
                @if(!empty($c->state)), {{ $c->state }}@endif
                @if(!empty($c->pin)) - {{ $c->pin }}@endif
            </div>

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

            if ($lineTotal <= 0) $lineTotal = $lineBase + $lineTax;
            if ($single && $lineTax <= 0) $lineTax = $finalTax;
            if ($single && $lineTotal <= 0) $lineTotal = $finalTotal;
        @endphp

        <tr>
            <td>
                <strong>{{ $name ?: '-' }}</strong>

                @if($desc)
                    <div class="small">{{ $desc }}</div>
                @endif

                @if($note)
                    <div class="small">{{ $note }}</div>
                @endif
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

    <tr class="grand">
        <td>Total</td>
        <td class="text-right">₹ {{ $fmt2($finalTotal) }}</td>
    </tr>
</table>

<div style="margin-top:18px;">
    <div class="label">Amount in Words</div>
    <div class="value">{{ inr_words($finalTotal) }}</div>
</div>

<table style="margin-top:24px;">
    <tr>
        <td style="width:60%; vertical-align:top;">
            @if($showTerms && !empty($inv->terms))
                <div class="label">Terms & Conditions</div>
                <div>{!! nl2br(e($inv->terms)) !!}</div>
            @endif
        </td>

        <td style="width:40%; text-align:right; vertical-align:top;">
            @if($showSignature)
                @if(!empty($invoiceSignatureUrl))
                    <img src="{{ $invoiceSignatureUrl }}" alt="Signature" style="max-height:45px;"><br>
                @endif

                <div class="label">Authorised Signatory</div>
                <div class="value">{{ $b->name ?? 'Real Victory Groups' }}</div>
            @endif
        </td>
    </tr>
</table>

</body>
</html>