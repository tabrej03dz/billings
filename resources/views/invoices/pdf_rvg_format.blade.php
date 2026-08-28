{{-- resources/views/invoices/pdf_simple.blade.php --}}

@php
    $ts = $templateSetting ?? null;

    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? ($inv->business ?? null);
    $c = $client ?? ($inv->client ?? null);
    $items = $items ?? collect();

    // $docType = strtolower((string)($type ?? 'invoice'));
    // $isQuotation = $docType === 'quotation';

    // $docLabel = $isQuotation ? 'QUOTATION' : 'TAX INVOICE';

    $docType = strtolower((string)(
        $type
        ?? ($inv->invoice_type ?? 'invoice')
    ));

    $isQuotation = $docType === 'quotation';
    $isProforma  = $docType === 'proforma';

    $gstEnabled = (bool) ($b->gst_enabled ?? false);
    $businessGstin = trim((string) ($b->gstin ?? ''));

    $isGstBusiness = $gstEnabled && $businessGstin !== '';

    if (!$isGstBusiness) {

        // Non-GST business ke liye sab jagah simple Invoice
        $docLabel = 'INVOICE';

    } else {

        // GST business ke liye proper document title
        $docLabel = match ($docType) {
            'quotation' => 'QUOTATION',
            'proforma'  => 'PROFORMA INVOICE',
            default     => 'TAX INVOICE',
        };
    }

    $termsText = $inv->terms ?? null;

    $primaryColor   = $ts->primary_color ?? '#d60000';
    $secondaryColor = $ts->secondary_color ?? '#dbd9d6';
    $textColor      = $ts->text_color ?? '#111111';

    // mPDF Hindi compatible font
    $fontFamily = 'freeserif';

    $showLogo      = $ts->show_logo ?? true;
    $showTagline   = $ts->show_tagline ?? true;
    $showSignature = $ts->show_signature ?? true;
    $showTerms     = $ts->show_terms ?? true;

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

    /*
    |--------------------------------------------------------------------------
    | Payment + Selected Bank
    |--------------------------------------------------------------------------
    */
    $payment = $paymentDetails ?? [];

    $paymentMethod = trim((string)(
        $payment['payment_method']
        ?? $payment['method']
        ?? ($inv->payment_method ?? '')
    ));

    $payCash = (float)(
        $payment['cash_amount']
        ?? ($pay->cash_amount ?? 0)
    );

    $payOnline = (float)(
        $payment['online_amount']
        ?? ($pay->online_amount ?? 0)
    );

    $payCard = (float)(
        $payment['card_amount']
        ?? ($pay->card_amount ?? 0)
    );

    $payCheque = (float)(
        $payment['cheque_amount']
        ?? ($pay->cheque_amount ?? 0)
    );

    $payCredit = (float)(
        $payment['credit_sales_excess_amount']
        ?? ($pay->credit_sales_excess_amount ?? 0)
    );

    $payAdvance = (float)(
        $payment['advance_amount']
        ?? ($pay->advance_amount ?? 0)
    );

    $payReceived = (float)(
        $payment['received_total']
        ?? ($pay->received_total ?? ($inv->received_amount ?? 0))
    );

    $onlineMode = trim((string)(
        $payment['online_mode']
        ?? ($pay->online_mode ?? '')
    ));

    $onlineRef = trim((string)(
        $payment['online_ref']
        ?? ($pay->online_ref ?? '')
    ));

    $paymentUpiId = trim((string)(
        $payment['upi_id']
        ?? ($pay->upi_id ?? '')
    ));

    $cardLast4 = trim((string)(
        $payment['card_last4']
        ?? ($pay->card_last4 ?? '')
    ));

    $cardRef = trim((string)(
        $payment['card_ref']
        ?? ($pay->card_ref ?? '')
    ));

    $chequeNo = trim((string)(
        $payment['cheque_no']
        ?? ($pay->cheque_no ?? '')
    ));

    $paymentNotes = trim((string)(
        $payment['notes']
        ?? ($pay->notes ?? '')
    ));

    $enteredBankName = trim((string)(
        $payment['bank_name']
        ?? ($pay->bank_name ?? '')
    ));

    /*
    |--------------------------------------------------------------------------
    | Selected Bank - robust fallback
    |--------------------------------------------------------------------------
    */
    $bank = $selectedBank ?? null;

    if (
        !$bank
        && !empty($inv->bank_account_id)
        && !empty($inv->business_id)
    ) {
        $bank = \App\Models\BankAccount::query()
            ->where('business_id', (int) $inv->business_id)
            ->where('id', (int) $inv->bank_account_id)
            ->first();
    }

    $bankName = trim((string)(
        $bank->bank_name
        ?? $enteredBankName
        ?? ''
    ));

    $bankLabel = trim((string)(
        $bank->label
        ?? ''
    ));

    $bankAccountHolder = trim((string)(
        $bank->account_holder
        ?? ''
    ));

    $bankAccountNumber = trim((string)(
        $bank->account_no
        ?? ''
    ));

    $bankIfsc = trim((string)(
        $bank->ifsc
        ?? ''
    ));

    $bankBranch = trim((string)(
        $bank->branch
        ?? ''
    ));

    $bankUpi = trim((string)(
        $bank->upi_id
        ?? ''
    ));

    $hasPaymentDetails =
        $paymentMethod !== ''
        || $payCash > 0
        || $payOnline > 0
        || $payCard > 0
        || $payCheque > 0
        || $payCredit > 0
        || $payAdvance > 0
        || $payReceived > 0
        || $onlineMode !== ''
        || $onlineRef !== ''
        || $paymentUpiId !== ''
        || $cardLast4 !== ''
        || $cardRef !== ''
        || $chequeNo !== ''
        || $paymentNotes !== '';

    $hasBankDetails =
        $bank !== null
        && (
            $bankName !== ''
            || $bankLabel !== ''
            || $bankAccountHolder !== ''
            || $bankAccountNumber !== ''
            || $bankIfsc !== ''
            || $bankBranch !== ''
            || $bankUpi !== ''
        );

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
                '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen',
                'Eighteen', 'Nineteen'
            ];

            $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

            $twoDigits = function ($n) use ($ones, $tens) {
                $n = (int)$n;

                if ($n === 0) {
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

    $logoSrc = $logo ?? null;

    if (!empty($logoSrc)) {
        $logoSrc = (string)$logoSrc;

        if (!\Illuminate\Support\Str::startsWith($logoSrc, ['http://', 'https://', 'data:', '/'])) {
            if (file_exists(public_path($logoSrc))) {
                $logoSrc = public_path($logoSrc);
            } elseif (file_exists(public_path('storage/' . ltrim($logoSrc, '/')))) {
                $logoSrc = public_path('storage/' . ltrim($logoSrc, '/'));
            }
        }
    }
@endphp




<!doctype html>
<html lang="hi">
<head>
    <meta charset="utf-8">
    <title>{{ $docLabel }} {{ $invoiceNo }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: {{ $fontFamily }}, sans-serif;
            font-size: 12px;
            color: {{ $textColor }};
            margin: 0;
            padding: 0;
        }

        .wrap {
            margin: 10px;
            padding: 10px 12px 12px 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
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

        .topTitleTable {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .topTitleTable td {
            vertical-align: top;
            padding: 0;
            font-size: 11px;
            line-height: 1.2;
        }

        .docTitleCell {
            width: 86px;
            text-align: left;
            font-weight: bold;
            white-space: nowrap;
        }

        .recipientCell {
            text-align: left;
            white-space: nowrap;
        }

        .taglineCell {
            text-align: right;
            font-weight: bold;
            white-space: nowrap;
            padding-top: 1px;
        }

        .smalltag {
            display: inline-block;
            border: 1px solid #999;
            padding: 2px 6px;
            font-size: 10.5px;
            color: #333;
            line-height: 1.1;
            white-space: nowrap;
        }

        .companyHeaderTable {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            table-layout: fixed;
        }

        .companyHeaderTable td {
            vertical-align: top;
            padding: 0;
        }

        .logoCell {
            width: 112px;
            text-align: left;
            vertical-align: top;
            padding-top: 2px;
            padding-right: 8px;
        }

        .logoImg {
            width: 108px;
            max-width: 108px;
            height: auto;
            display: block;
            margin: 0;
        }

        .companyCell {
            width: auto;
            text-align: left;
            vertical-align: top;
            padding-left: 8px;
            padding-top: 9px;
        }

        .companyName {
            font-size: 24px;
            line-height: 1.1;
            font-weight: bold;
            color: {{ $primaryColor }};
            letter-spacing: 0.5px;
            margin: 0 0 5px 0;
            padding: 0;
            text-align: left;
            white-space: nowrap;
        }

        .companyAddress {
            font-size: 14.5px;
            line-height: 1.25;
            margin: 0 0 2px 0;
            padding: 0;
            text-align: left;
        }

        .companyContact {
            font-size: 12.8px;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            text-align: left;
        }

        .companyContactLine {
            white-space: nowrap;
        }

        .line-red {
            border-top: 6px solid {{ $primaryColor }};
            margin: 0;
            padding: 0;
            height: 0;
            line-height: 0;
        }

        .greybar {
            background: {{ $secondaryColor }};
            padding: 8px 10px;
            margin: 0;
            font-size: 10.5px;
        }

        .greybar table td {
            padding: 0;
            vertical-align: middle;
        }

        .billto {
            font-size: 12.5px;
            margin-top: 4px;
            margin-bottom: 8px;
            line-height: 1.28;
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
            line-height: 1.25;
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

        .paymentBankWrap {
            margin-top: 12px;
            border-top: 2px solid {{ $primaryColor }};
            border-bottom: 2px solid {{ $primaryColor }};
        }

        .paymentBankTitle {
            background: {{ $secondaryColor }};
            font-weight: bold;
            font-size: 11.5px;
            padding: 6px 7px;
        }

        .paymentBankBlock {
            padding: 7px 7px;
            font-size: 10.8px;
            line-height: 1.45;
        }

        .paymentBankBlock + .paymentBankBlock {
            border-top: 1px solid #cccccc;
        }

        .paymentLabel {
            font-weight: bold;
        }
    </style>
</head>

<body>
<div class="wrap">

    {{-- HEADER TOP --}}
    <table class="topTitleTable">
        <tr>
            <td class="docTitleCell">
                {{ $docLabel }}
            </td>

            <td class="recipientCell">
                <span class="smalltag">ORIGINAL FOR RECIPIENT</span>
            </td>

            <td class="taglineCell">
                @if($showTagline && (($b->name ?? '') == 'Real Victory Groups'))
                    Think Outside The Box
                @endif
            </td>
        </tr>
    </table>

    {{-- COMPANY HEADER --}}
    {{-- <table class="companyHeaderTable">
        <tr>
            <td class="logoCell">
                @if($showLogo && !empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="Logo" class="logoImg" width="108">
                @endif
            </td>

            <td class="companyCell">
                <div class="companyName">
                    {{ $b->name ?? 'Real Victory Groups' }}
                </div>

                <div class="companyAddress">
                    {{ $b_addr }}
                    @if($b_city), {{ $b_city }}@endif
                    @if($b_state), {{ $b_state }}@endif
                    @if($b_pin) - {{ $b_pin }}@endif
                </div>

                <div class="companyContact">
                    <div class="companyContactLine">
                        <span class="bold">Mobile:</span> {{ $b_mobile ?: '-' }}
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <span class="bold">GSTIN:</span> {{ $b_gstin ?: '-' }}
                    </div>

                    <div>
                        <span class="bold">Email:</span> {{ $b_email ?: '-' }}
                    </div>
                </div>
            </td>
        </tr>
    </table> --}}

    {{-- COMPANY HEADER --}}
<table class="companyHeaderTable">
    <tr>

        <td class="logoCell">

            @if($showLogo && !empty($logoSrc))

                <img
                    src="{{ $logoSrc }}"
                    alt="Logo"
                    class="logoImg"
                    style="
                        width:108px;
                        max-width:108px;
                        height:auto;
                        display:block;
                    "
                >

            @endif

        </td>

        <td class="companyCell">

            <div class="companyName">
                {{ $b->name ?? 'Real Victory Groups' }}
            </div>

            <div class="companyAddress">

                {{ $b_addr }}

                @if($b_city)
                    , {{ $b_city }}
                @endif

                @if($b_state)
                    , {{ $b_state }}
                @endif

                @if($b_pin)
                    - {{ $b_pin }}
                @endif

            </div>

            <div class="companyContact">

                <div class="companyContactLine">

                    <span class="bold">
                        Mobile:
                    </span>

                    {{ $b_mobile ?: '-' }}

                    &nbsp;&nbsp;&nbsp;&nbsp;

                    <span class="bold">
                        GSTIN:
                    </span>

                    {{ $b_gstin ?: '-' }}

                </div>

                <div>

                    <span class="bold">
                        Email:
                    </span>

                    {{ $b_email ?: '-' }}

                </div>

            </div>

        </td>

    </tr>
</table>

    <div class="line-red"></div>

    {{-- INVOICE BAR --}}
    <div class="greybar">
        <table>
            <tr>
                <td class="bold" style="font-size:12px;">
                    {{ $isQuotation ? 'Quotation' : 'Invoice' }} No.:
                    <span class="muted" style="font-size:12px;">{{ $invoiceNo }}</span>
                </td>

                <td class="right bold" style="font-size:12px;">
                    {{ $isQuotation ? 'Quotation' : 'Invoice' }} Date:
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

            @if($hasPaymentDetails || $hasBankDetails)
                <div class="paymentBankWrap">
                    <div class="paymentBankTitle">
                        PAYMENT & BANK DETAILS
                    </div>

                    @if($hasPaymentDetails)
                        <div class="paymentBankBlock">
                            <div class="bold" style="margin-bottom:4px;">Payment Details</div>

                            @if($paymentMethod !== '')
                                <span class="paymentLabel">Payment Method:</span>
                                {{ strtoupper($paymentMethod) }}<br>
                            @endif

                            @if($payCash > 0)
                                <span class="paymentLabel">Cash:</span>
                                &#8377; {{ $fmt2($payCash) }}<br>
                            @endif

                            @if($payOnline > 0)
                                <span class="paymentLabel">Online / UPI:</span>
                                &#8377; {{ $fmt2($payOnline) }}<br>
                            @endif

                            @if($onlineMode !== '')
                                <span class="paymentLabel">Online Mode:</span>
                                {{ strtoupper($onlineMode) }}<br>
                            @endif

                            @if($onlineRef !== '')
                                <span class="paymentLabel">Online Ref:</span>
                                {{ $onlineRef }}<br>
                            @endif

                            @if($paymentUpiId !== '')
                                <span class="paymentLabel">UPI ID:</span>
                                {{ $paymentUpiId }}<br>
                            @endif

                            @if($payCard > 0)
                                <span class="paymentLabel">Card:</span>
                                &#8377; {{ $fmt2($payCard) }}<br>
                            @endif

                            @if($cardLast4 !== '')
                                <span class="paymentLabel">Card Last 4:</span>
                                {{ $cardLast4 }}<br>
                            @endif

                            @if($cardRef !== '')
                                <span class="paymentLabel">Card Ref:</span>
                                {{ $cardRef }}<br>
                            @endif

                            @if($payCheque > 0)
                                <span class="paymentLabel">Cheque:</span>
                                &#8377; {{ $fmt2($payCheque) }}<br>
                            @endif

                            @if($chequeNo !== '')
                                <span class="paymentLabel">Cheque No:</span>
                                {{ $chequeNo }}<br>
                            @endif

                            @if($payAdvance > 0)
                                <span class="paymentLabel">Advance:</span>
                                &#8377; {{ $fmt2($payAdvance) }}<br>
                            @endif

                            @if($payCredit > 0)
                                <span class="paymentLabel">Credit / Excess:</span>
                                &#8377; {{ $fmt2($payCredit) }}<br>
                            @endif

                            @if($payReceived > 0)
                                <span class="paymentLabel">Total Received:</span>
                                &#8377; {{ $fmt2($payReceived) }}<br>
                            @endif

                            @if($paymentNotes !== '')
                                <span class="paymentLabel">Payment Note:</span>
                                {{ $paymentNotes }}
                            @endif
                        </div>
                    @endif

                    <div class="paymentBankBlock">
                        <div class="bold" style="margin-bottom:4px;">Selected Bank Account</div>

                        @if($hasBankDetails)
                            @if($bankLabel !== '')
                                <span class="paymentLabel">Account Label:</span>
                                {{ $bankLabel }}<br>
                            @endif

                            @if($bankName !== '')
                                <span class="paymentLabel">Bank:</span>
                                {{ $bankName }}<br>
                            @endif

                            @if($bankAccountHolder !== '')
                                <span class="paymentLabel">Account Holder:</span>
                                {{ $bankAccountHolder }}<br>
                            @endif

                            @if($bankAccountNumber !== '')
                                <span class="paymentLabel">Account No:</span>
                                {{ $bankAccountNumber }}<br>
                            @endif

                            @if($bankIfsc !== '')
                                <span class="paymentLabel">IFSC:</span>
                                {{ $bankIfsc }}<br>
                            @endif

                            @if($bankBranch !== '')
                                <span class="paymentLabel">Branch:</span>
                                {{ $bankBranch }}<br>
                            @endif

                            @if($bankUpi !== '')
                                <span class="paymentLabel">Bank UPI ID:</span>
                                {{ $bankUpi }}<br>
                            @endif
                        @else
                            @if(!empty($inv->bank_account_id))
                                Bank account details not found.
                            @else
                                No bank account selected.
                            @endif
                        @endif
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