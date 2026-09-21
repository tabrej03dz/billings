@php
    /** @var \App\Models\Invoice $inv */

    $b = $biz ?? ($inv->business ?? null);
    $c = $client ?? ($inv->client ?? null);

    $items = $items ?? collect();

    if (!($items instanceof \Illuminate\Support\Collection)) {
        $items = collect($items);
    }

    /*
    |--------------------------------------------------------------------------
    | Invoice Type
    |--------------------------------------------------------------------------
    */

    $docType = strtolower((string) (
        $type
        ?? $inv->invoice_type
        ?? 'invoice'
    ));

    $gstEnabled = (bool) ($b->gst_enabled ?? false);
    $businessGstin = trim((string) ($b->gstin ?? ''));

    $isGstBusiness = $gstEnabled && $businessGstin !== '';

    if (!$isGstBusiness) {
        $docLabel = 'Jewellery Invoice';
    } else {
        $docLabel = match ($docType) {
            'quotation' => 'Quotation',
            'proforma'  => 'Proforma Invoice',
            default     => 'Tax Invoice',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    $fmt2 = fn ($value) => number_format(
        (float) $value,
        2,
        '.',
        ','
    );

    $fmt3 = fn ($value) => number_format(
        (float) $value,
        3,
        '.',
        ''
    );

    $invoiceDateFormat = function ($date) {
        if (!$date) {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($date)->format('d-M-Y');
        } catch (\Throwable $e) {
            return (string) $date;
        }
    };

    $firstPositive = function (array $values, $default = 0) {
        foreach ($values as $value) {
            if (
                $value !== null
                && $value !== ''
                && is_numeric($value)
                && (float) $value > 0
            ) {
                return (float) $value;
            }
        }

        return (float) $default;
    };

    /*
    |--------------------------------------------------------------------------
    | Invoice Data
    |--------------------------------------------------------------------------
    */

    $invoiceNo = $inv->invoice_number
        ?? $inv->invoice_no
        ?? '-';

    $invoiceDate = $inv->invoice_date
        ?? $inv->date
        ?? $inv->created_at
        ?? null;

    $deliveryNote = trim((string) (
        $inv->delivery_note
        ?? ''
    ));

    $paymentTerms = trim((string) (
        $inv->payment_terms
        ?? $inv->terms_of_payment
        ?? $inv->payment_method
        ?? ''
    ));

    $supplierRef = trim((string) (
        $inv->supplier_reference
        ?? $inv->supplier_ref
        ?? ''
    ));

    $otherReference = trim((string) (
        $inv->other_reference
        ?? $inv->reference
        ?? ''
    ));

    $buyerOrderNo = trim((string) (
        $inv->buyer_order_no
        ?? $inv->order_no
        ?? ''
    ));

    $buyerOrderDate = $inv->buyer_order_date
        ?? $inv->order_date
        ?? null;

    $dispatchDocumentNo = trim((string) (
        $inv->dispatch_document_no
        ?? $inv->dispatch_doc_no
        ?? ''
    ));

    $deliveryNoteDate = $inv->delivery_note_date
        ?? null;

    $dispatchedThrough = trim((string) (
        $inv->dispatched_through
        ?? ''
    ));

    $destination = trim((string) (
        $inv->destination
        ?? ''
    ));

    $termsOfDelivery = trim((string) (
        $inv->terms_of_delivery
        ?? ''
    ));

    /*
    |--------------------------------------------------------------------------
    | Amounts
    |--------------------------------------------------------------------------
    */

    $taxable = (float) (
        $subtotal
        ?? $inv->subtotal
        ?? 0
    );

    $cgstAmount = (float) (
        $cgst_amount
        ?? $inv->cgst_amount
        ?? 0
    );

    $sgstAmount = (float) (
        $sgst_amount
        ?? $inv->sgst_amount
        ?? 0
    );

    $igstAmount = (float) (
        $igst_amount
        ?? $inv->igst_amount
        ?? 0
    );

    $discount = (float) (
        $inv->discount_total
        ?? $inv->discount
        ?? 0
    );

    $roundOff = (float) (
        $inv->round_off
        ?? 0
    );

    $grandTotal = (float) (
        $grand_total
        ?? $inv->total
        ?? $inv->grand_total
        ?? 0
    );

    $received = (float) (
        $received
        ?? $inv->received_amount
        ?? 0
    );

    $balance = (float) (
        $balance
        ?? $inv->balance
        ?? max(0, $grandTotal - $received)
    );

    /*
    |--------------------------------------------------------------------------
    | GST Percentage
    |--------------------------------------------------------------------------
    */

    $cgstPercent = 0;
    $sgstPercent = 0;
    $igstPercent = 0;

    if ($taxable > 0) {
        if ($cgstAmount > 0) {
            $cgstPercent = ($cgstAmount / $taxable) * 100;
        }

        if ($sgstAmount > 0) {
            $sgstPercent = ($sgstAmount / $taxable) * 100;
        }

        if ($igstAmount > 0) {
            $igstPercent = ($igstAmount / $taxable) * 100;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Business Details
    |--------------------------------------------------------------------------
    */

    $bName = trim((string) (
        $b->name
        ?? $b->business_name
        ?? 'Your Jewellery Store'
    ));

    $bAddr = trim((string) (
        $b->address
        ?? $b->business_address
        ?? ''
    ));

    $bCity = trim((string) (
        $b->city
        ?? ''
    ));

    $bState = trim((string) (
        $b->state
        ?? ''
    ));

    $bStateCode = trim((string) (
        $b->state_code
        ?? $b->gst_state_code
        ?? ''
    ));

    $bMobile = trim((string) (
        $b->mobile
        ?? $b->phone
        ?? $b->business_phone
        ?? ''
    ));

    $bEmail = trim((string) (
        $b->email
        ?? $b->business_email
        ?? ''
    ));

    $bGstin = trim((string) (
        $b->gstin
        ?? $inv->gst_no
        ?? ''
    ));

    /*
    |--------------------------------------------------------------------------
    | Buyer Details
    |--------------------------------------------------------------------------
    */

    $cName = trim((string) (
        $c->name
        ?? '-'
    ));

    $cBusinessName = trim((string) (
        $c->business_name
        ?? ''
    ));

    $cAddr = trim((string) (
        $c->address
        ?? ''
    ));

    $cCity = trim((string) (
        $c->city
        ?? ''
    ));

    $cState = trim((string) (
        $c->state
        ?? ''
    ));

    $cStateCode = trim((string) (
        $c->state_code
        ?? $c->gst_state_code
        ?? ''
    ));

    $cMobile = trim((string) (
        $c->mobile
        ?? $c->phone
        ?? ''
    ));

    $cEmail = trim((string) (
        $c->email
        ?? ''
    ));

    $cGstin = trim((string) (
        $c->gstin
        ?? $c->gst_number
        ?? ''
    ));

    $cPan = trim((string) (
        $c->pan
        ?? $c->pan_number
        ?? ''
    ));

    /*
    |--------------------------------------------------------------------------
    | Place of Supply
    |--------------------------------------------------------------------------
    */

    $placeOfSupply = trim((string) (
        $inv->place_of_supply_state
        ?? $cState
        ?? ''
    ));

    /*
    |--------------------------------------------------------------------------
    | Payment Details
    |--------------------------------------------------------------------------
    */

    $pay = $paymentDetails ?? [];

    $payCash = (float) (
        $pay['cash_amount']
        ?? ($payRow->cash_amount ?? 0)
    );

    $payOnline = (float) (
        $pay['online_amount']
        ?? ($payRow->online_amount ?? 0)
    );

    $payCard = (float) (
        $pay['card_amount']
        ?? ($payRow->card_amount ?? 0)
    );

    $payCheque = (float) (
        $pay['cheque_amount']
        ?? ($payRow->cheque_amount ?? 0)
    );

    $payCredit = (float) (
        $pay['credit_sales_excess_amount']
        ?? ($payRow->credit_sales_excess_amount ?? 0)
    );

    $payAdvance = (float) (
        $pay['advance_amount']
        ?? ($payRow->advance_amount ?? 0)
    );

    $payReceivedTotal = (float) (
        $pay['received_total']
        ?? ($payRow->received_total ?? $received)
    );

    $onlineMode = trim((string) (
        $pay['online_mode']
        ?? ($payRow->online_mode ?? '')
    ));

    $onlineRef = trim((string) (
        $pay['online_ref']
        ?? ($payRow->online_ref ?? '')
    ));

    $upiId = trim((string) (
        $pay['upi_id']
        ?? ($payRow->upi_id ?? '')
    ));

    $cardLast4 = trim((string) (
        $pay['card_last4']
        ?? ($payRow->card_last4 ?? '')
    ));

    $cardRef = trim((string) (
        $pay['card_ref']
        ?? ($payRow->card_ref ?? '')
    ));

    $chequeNo = trim((string) (
        $pay['cheque_no']
        ?? ($payRow->cheque_no ?? '')
    ));

    $paymentNotes = trim((string) (
        $pay['notes']
        ?? ($payRow->notes ?? '')
    ));

    $paymentMethod = trim((string) (
        $pay['method']
        ?? $inv->payment_method
        ?? ''
    ));

    /*
    |--------------------------------------------------------------------------
    | Bank
    |--------------------------------------------------------------------------
    */

    $bank = $selectedBank ?? null;

    $bankName = trim((string) (
        $bank->bank_name
        ?? ($pay['bank_name_entered'] ?? '')
        ?? ''
    ));

    $bankAccountHolder = trim((string) (
        $bank->account_holder
        ?? ''
    ));

    $bankAccountNumber = trim((string) (
        $bank->account_no
        ?? ''
    ));

    $bankIfsc = trim((string) (
        $bank->ifsc
        ?? ''
    ));

    $bankBranch = trim((string) (
        $bank->branch
        ?? ''
    ));

    $bankUpi = trim((string) (
        $bank->upi_id
        ?? ''
    ));

    /*
    |--------------------------------------------------------------------------
    | Amount In Words
    |--------------------------------------------------------------------------
    */

    if (!function_exists('tally_invoice_amount_words')) {
        function tally_invoice_amount_words($amount)
        {
            $amount = round((float) $amount, 2);

            $rupees = (int) floor($amount);
            $paise = (int) round(
                ($amount - $rupees) * 100
            );

            $ones = [
                '',
                'One',
                'Two',
                'Three',
                'Four',
                'Five',
                'Six',
                'Seven',
                'Eight',
                'Nine',
                'Ten',
                'Eleven',
                'Twelve',
                'Thirteen',
                'Fourteen',
                'Fifteen',
                'Sixteen',
                'Seventeen',
                'Eighteen',
                'Nineteen',
            ];

            $tens = [
                '',
                '',
                'Twenty',
                'Thirty',
                'Forty',
                'Fifty',
                'Sixty',
                'Seventy',
                'Eighty',
                'Ninety',
            ];

            $convertBelowHundred = function ($number) use (
                $ones,
                $tens
            ) {
                $number = (int) $number;

                if ($number < 20) {
                    return $ones[$number] ?? '';
                }

                return trim(
                    ($tens[(int) floor($number / 10)] ?? '')
                    . ' '
                    . ($ones[$number % 10] ?? '')
                );
            };

            $convertBelowThousand = function ($number) use (
                $ones,
                $convertBelowHundred
            ) {
                $number = (int) $number;

                $result = '';

                if ($number >= 100) {
                    $result .=
                        ($ones[(int) floor($number / 100)] ?? '')
                        . ' Hundred ';

                    $number %= 100;
                }

                if ($number > 0) {
                    $result .= $convertBelowHundred($number);
                }

                return trim($result);
            };

            $parts = [];

            if ($rupees >= 10000000) {
                $crore = (int) floor(
                    $rupees / 10000000
                );

                $parts[] =
                    $convertBelowThousand($crore)
                    . ' Crore';

                $rupees %= 10000000;
            }

            if ($rupees >= 100000) {
                $lakh = (int) floor(
                    $rupees / 100000
                );

                $parts[] =
                    $convertBelowThousand($lakh)
                    . ' Lakh';

                $rupees %= 100000;
            }

            if ($rupees >= 1000) {
                $thousand = (int) floor(
                    $rupees / 1000
                );

                $parts[] =
                    $convertBelowThousand($thousand)
                    . ' Thousand';

                $rupees %= 1000;
            }

            if ($rupees > 0) {
                $parts[] =
                    $convertBelowThousand($rupees);
            }

            $words = trim(
                implode(' ', array_filter($parts))
            );

            if ($words === '') {
                $words = 'Zero';
            }

            $result = $words . ' Rupees';

            if ($paise > 0) {
                $result .=
                    ' and '
                    . $convertBelowHundred($paise)
                    . ' Paise';
            }

            return trim($result) . ' Only';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Total Qty
    |--------------------------------------------------------------------------
    */

    $totalQty = 0;

    foreach ($items as $it) {
        $qtyForTotal = $firstPositive([
            $it->net_weight ?? null,
            $it->net_wt ?? null,
            $it->silver_wt ?? null,
            $it->gold_wt ?? null,
            $it->quantity ?? null,
            $it->qty ?? null,
        ]);

        $totalQty += $qtyForTotal;
    }
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        {{ $docLabel }} {{ $invoiceNo }}
    </title>

    <style>
        @page {
            size: A4 portrait;
            margin: 5mm 5mm 6mm 5mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            padding: 0;
            margin: 0;
        }

        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 10px;
            color: #000000;
            background: #ffffff;
        }

        /*
        |--------------------------------------------------------------------------
        | Main Page
        |--------------------------------------------------------------------------
        */

        .page-frame {
            width: 100%;
            min-height: 285mm;
            border: 0.7px solid #111111;
            padding: 3mm;
        }

        /*
         * Image jaisa upper blank area.
         */
        .top-blank-space {
            height: 36mm;
        }

        .invoice-box {
            width: 100%;
            border: 0.7px solid #111111;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        td,
        th {
            color: #000000;
        }

        /*
        |--------------------------------------------------------------------------
        | Invoice Heading
        |--------------------------------------------------------------------------
        */

        .title-table td {
            height: 13mm;
            text-align: center;
            vertical-align: middle;
            font-size: 17px;
            font-weight: bold;
            border-bottom: 0.7px solid #111111;
        }

        /*
        |--------------------------------------------------------------------------
        | Party & Invoice Meta
        |--------------------------------------------------------------------------
        */

        .party-meta-table {
            table-layout: fixed;
        }

        .party-meta-table td {
            vertical-align: top;
        }

        .seller-buyer {
            width: 51%;
            border-right: 0.7px solid #111111;
        }

        .meta-area {
            width: 49%;
        }

        .seller-section {
            min-height: 39mm;
            padding: 3mm 2mm 2mm 2mm;
            border-bottom: 0.7px solid #111111;
            font-size: 10.5px;
            line-height: 1.35;
        }

        .buyer-section {
            min-height: 56mm;
            padding: 2mm;
            font-size: 10.5px;
            line-height: 1.35;
        }

        .business-name {
            font-size: 11.5px;
            font-weight: bold;
        }

        .buyer-label {
            font-size: 10px;
            margin-bottom: 1mm;
        }

        .buyer-name {
            font-size: 11.5px;
            font-weight: bold;
        }

        /*
        |--------------------------------------------------------------------------
        | Right Side Metadata
        |--------------------------------------------------------------------------
        */

        .meta-grid {
            table-layout: fixed;
        }

        .meta-grid td {
            width: 50%;
            height: 15.7mm;
            padding: 2mm 2.2mm;
            vertical-align: top;
            border-bottom: 0.7px solid #111111;
            font-size: 10px;
            line-height: 1.2;
        }

        .meta-grid td:first-child {
            border-right: 0.7px solid #111111;
        }

        .meta-grid .large-meta-row td {
            height: 20mm;
        }

        .meta-grid .terms-row td {
            height: 18mm;
            border-bottom: 0;
        }

        .meta-label {
            display: block;
            font-size: 10px;
            font-weight: normal;
        }

        .meta-value {
            display: block;
            margin-top: 1mm;
            font-size: 10.5px;
            font-weight: bold;
        }

        /*
        |--------------------------------------------------------------------------
        | Items Table
        |--------------------------------------------------------------------------
        */

        .items-wrap {
            margin-top: 3mm;
        }

        .items-table {
            width: 100%;
            table-layout: fixed;
        }

        .items-table th,
        .items-table td {
            border: 0.7px solid #111111;
        }

        .items-table th {
            height: 14mm;
            padding: 1.5mm 1mm;
            font-size: 10px;
            font-weight: normal;
            text-align: center;
            vertical-align: middle;
        }

        .items-table td {
            padding: 1.7mm 1.5mm;
            font-size: 10px;
            vertical-align: top;
        }

        .col-sl {
            width: 6%;
        }

        .col-description {
            width: 35.5%;
        }

        .col-hsn {
            width: 14%;
        }

        .col-qty {
            width: 13%;
        }

        .col-rate {
            width: 11%;
        }

        .col-per {
            width: 6%;
        }

        .col-amount {
            width: 14.5%;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .nowrap {
            white-space: nowrap;
        }

        .item-name {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }

        .item-extra {
            font-size: 8px;
            line-height: 1.35;
            margin-top: 1mm;
        }

        .amount-cell {
            font-weight: bold;
            text-align: right;
        }

        /*
        |--------------------------------------------------------------------------
        | Empty Body Space
        |--------------------------------------------------------------------------
        */

        .items-empty-area td {
            height: 35mm;
            border-top: 0;
            border-bottom: 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Tax Rows
        |--------------------------------------------------------------------------
        */

        .subtotal-row td,
        .tax-row td,
        .grand-row td,
        .discount-row td,
        .round-row td {
            height: 8mm;
            vertical-align: middle;
        }

        .subtotal-row td {
            font-size: 10px;
        }

        .tax-title {
            text-align: right;
            font-weight: bold;
            font-style: italic;
            font-size: 10.5px;
            padding-right: 8mm !important;
        }

        .tax-rate {
            text-align: right;
        }

        .grand-row td {
            font-weight: bold;
            font-size: 11px;
        }

        .grand-label {
            text-align: right;
            padding-right: 4mm !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Bottom Information
        |--------------------------------------------------------------------------
        */

        .bottom-section {
            page-break-inside: avoid;
        }

        .words-table td {
            border: 0.7px solid #111111;
            border-top: 0;
            padding: 2mm;
            font-size: 9.5px;
            line-height: 1.4;
        }

        .section-label {
            font-size: 8.5px;
        }

        .section-value {
            font-weight: bold;
            margin-top: 1mm;
        }

        /*
        |--------------------------------------------------------------------------
        | Payment / Bank
        |--------------------------------------------------------------------------
        */

        .bank-payment-table {
            table-layout: fixed;
        }

        .bank-payment-table td {
            width: 50%;
            min-height: 28mm;
            padding: 2mm;
            vertical-align: top;
            border: 0.7px solid #111111;
            border-top: 0;
            font-size: 8.5px;
            line-height: 1.45;
        }

        .bank-payment-table td:first-child {
            border-right: 0;
        }

        .small-heading {
            font-size: 9px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 1mm;
        }

        /*
        |--------------------------------------------------------------------------
        | Declaration / Signature
        |--------------------------------------------------------------------------
        */

        .footer-info-table {
            table-layout: fixed;
        }

        .footer-info-table td {
            border: 0.7px solid #111111;
            border-top: 0;
            vertical-align: top;
            padding: 2mm;
            font-size: 8.5px;
            line-height: 1.35;
        }

        .footer-left {
            width: 58%;
        }

        .footer-right {
            width: 42%;
            text-align: right;
        }

        .signature-area {
            height: 27mm;
            position: relative;
        }

        .signature-image {
            max-width: 35mm;
            max-height: 13mm;
            margin-top: 2mm;
        }

        .signature-text {
            margin-top: 7mm;
            font-weight: bold;
        }

        /*
        |--------------------------------------------------------------------------
        | Footer
        |--------------------------------------------------------------------------
        */

        .computer-generated {
            text-align: center;
            font-size: 7.5px;
            margin-top: 1.5mm;
        }
    </style>
</head>

<body>

<div class="page-frame">

    {{-- ============================================================= --}}
    {{-- TOP BLANK SPACE LIKE REFERENCE IMAGE --}}
    {{-- ============================================================= --}}

    <div class="top-blank-space"></div>

    <div class="invoice-box">

        {{-- ========================================================= --}}
        {{-- TITLE --}}
        {{-- ========================================================= --}}

        <table class="title-table">
            <tr>
                <td>
                    {{ $docLabel }}
                </td>
            </tr>
        </table>


        {{-- ========================================================= --}}
        {{-- SELLER / BUYER + META INFORMATION --}}
        {{-- ========================================================= --}}

        <table class="party-meta-table">
            <tr>

                {{-- LEFT SIDE --}}
                <td class="seller-buyer">

                    {{-- SELLER --}}
                    <div class="seller-section">

                        <div class="business-name">
                            {{ strtoupper($bName) }}
                        </div>

                        @if($bAddr !== '')
                            <div>
                                {{ strtoupper($bAddr) }}
                            </div>
                        @endif

                        @if($bCity !== '')
                            <div>
                                {{ strtoupper($bCity) }}
                            </div>
                        @endif

                        @if($bMobile !== '')
                            <div>
                                Mob : {{ $bMobile }}
                            </div>
                        @endif

                        @if($bGstin !== '')
                            <div>
                                GSTIN/UIN:
                                {{ strtoupper($bGstin) }}
                            </div>
                        @endif

                        @if($bState !== '')
                            <div>
                                State Name :
                                &nbsp;&nbsp;
                                {{ $bState }}

                                @if($bStateCode !== '')
                                    , Code : {{ $bStateCode }}
                                @endif
                            </div>
                        @endif

                        @if($bEmail !== '')
                            <div>
                                E-Mail :
                                {{ $bEmail }}
                            </div>
                        @endif

                    </div>


                    {{-- BUYER --}}
                    <div class="buyer-section">

                        <div class="buyer-label">
                            Buyer
                        </div>

                        @if($cBusinessName !== '')
                            <div class="buyer-name">
                                {{ strtoupper($cBusinessName) }}
                            </div>
                        @endif

                        <div
                            class="{{ $cBusinessName === '' ? 'buyer-name' : '' }}"
                        >
                            {{ strtoupper($cName) }}
                        </div>

                        @if($cAddr !== '')
                            <div>
                                {{ strtoupper($cAddr) }}
                            </div>
                        @endif

                        @if($cCity !== '')
                            <div>
                                {{ strtoupper($cCity) }}
                            </div>
                        @endif

                        @if($cMobile !== '')
                            <div>
                                MOB---{{ $cMobile }}
                            </div>
                        @endif

                        @if($cGstin !== '')
                            <div>
                                GSTIN/UIN:
                                {{ strtoupper($cGstin) }}
                            </div>
                        @endif

                        @if($cPan !== '')
                            <div>
                                PAN:
                                {{ strtoupper($cPan) }}
                            </div>
                        @endif

                        @if($cState !== '')
                            <div>
                                State Name :
                                &nbsp;&nbsp;
                                {{ $cState }}

                                @if($cStateCode !== '')
                                    , Code : {{ $cStateCode }}
                                @endif
                            </div>
                        @endif

                    </div>

                </td>


                {{-- RIGHT SIDE --}}
                <td class="meta-area">

                    <table class="meta-grid">

                        {{-- Invoice No / Date --}}
                        <tr>
                            <td>
                                <span class="meta-label">
                                    Invoice No.
                                </span>

                                <span class="meta-value">
                                    {{ $invoiceNo }}
                                </span>
                            </td>

                            <td>
                                <span class="meta-label">
                                    Dated
                                </span>

                                <span class="meta-value">
                                    {{ $invoiceDateFormat($invoiceDate) }}
                                </span>
                            </td>
                        </tr>


                        {{-- Delivery / Payment --}}
                        <tr>
                            <td>
                                <span class="meta-label">
                                    Delivery Note
                                </span>

                                @if($deliveryNote !== '')
                                    <span class="meta-value">
                                        {{ $deliveryNote }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span class="meta-label">
                                    Mode/Terms of Payment
                                </span>

                                @if($paymentTerms !== '')
                                    <span class="meta-value">
                                        {{ $paymentTerms }}
                                    </span>
                                @endif
                            </td>
                        </tr>


                        {{-- Supplier Ref / Other Ref --}}
                        <tr>
                            <td>
                                <span class="meta-label">
                                    Supplier's Ref.
                                </span>

                                @if($supplierRef !== '')
                                    <span class="meta-value">
                                        {{ $supplierRef }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span class="meta-label">
                                    Other Reference(s)
                                </span>

                                @if($otherReference !== '')
                                    <span class="meta-value">
                                        {{ $otherReference }}
                                    </span>
                                @endif
                            </td>
                        </tr>


                        {{-- Buyer Order --}}
                        <tr>
                            <td>
                                <span class="meta-label">
                                    Buyer's Order No.
                                </span>

                                @if($buyerOrderNo !== '')
                                    <span class="meta-value">
                                        {{ $buyerOrderNo }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span class="meta-label">
                                    Dated
                                </span>

                                @if($buyerOrderDate)
                                    <span class="meta-value">
                                        {{ $invoiceDateFormat($buyerOrderDate) }}
                                    </span>
                                @endif
                            </td>
                        </tr>


                        {{-- Dispatch --}}
                        <tr>
                            <td>
                                <span class="meta-label">
                                    Despatch Document No.
                                </span>

                                @if($dispatchDocumentNo !== '')
                                    <span class="meta-value">
                                        {{ $dispatchDocumentNo }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span class="meta-label">
                                    Delivery Note Date
                                </span>

                                @if($deliveryNoteDate)
                                    <span class="meta-value">
                                        {{ $invoiceDateFormat($deliveryNoteDate) }}
                                    </span>
                                @endif
                            </td>
                        </tr>


                        {{-- Through / Destination --}}
                        <tr>
                            <td>
                                <span class="meta-label">
                                    Despatched through
                                </span>

                                @if($dispatchedThrough !== '')
                                    <span class="meta-value">
                                        {{ $dispatchedThrough }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span class="meta-label">
                                    Destination
                                </span>

                                @if($destination !== '')
                                    <span class="meta-value">
                                        {{ $destination }}
                                    </span>
                                @elseif($cCity !== '')
                                    <span class="meta-value">
                                        {{ $cCity }}
                                    </span>
                                @endif
                            </td>
                        </tr>


                        {{-- Terms --}}
                        <tr class="terms-row">
                            <td colspan="2">
                                <span class="meta-label">
                                    Terms of Delivery
                                </span>

                                @if($termsOfDelivery !== '')
                                    <span class="meta-value">
                                        {{ $termsOfDelivery }}
                                    </span>
                                @endif
                            </td>
                        </tr>

                    </table>

                </td>

            </tr>
        </table>

    </div>


    {{-- ============================================================= --}}
    {{-- PRODUCT TABLE --}}
    {{-- ============================================================= --}}

    <div class="items-wrap">

        <table class="items-table">

            <colgroup>
                <col class="col-sl">
                <col class="col-description">
                <col class="col-hsn">
                <col class="col-qty">
                <col class="col-rate">
                <col class="col-per">
                <col class="col-amount">
            </colgroup>

            <thead>
            <tr>
                <th>
                    Sl<br>No.
                </th>

                <th>
                    Description of Goods
                </th>

                <th>
                    HSN/SAC
                </th>

                <th>
                    Quantity
                </th>

                <th>
                    Rate
                </th>

                <th>
                    per
                </th>

                <th>
                    Amount
                </th>
            </tr>
            </thead>

            <tbody>

            @forelse($items as $index => $it)

                @php
                    /*
                    |--------------------------------------------------------------------------
                    | Item Name
                    |--------------------------------------------------------------------------
                    */

                    $itemName = trim((string) (
                        $it->item->name
                        ?? $it->name
                        ?? $it->item_name
                        ?? 'Jewellery Product'
                    ));

                    $description = trim((string) (
                        $it->description
                        ?? ''
                    ));

                    $hsn = trim((string) (
                        $it->hsn_code
                        ?? $it->sac_code
                        ?? $it->hsn
                        ?? ''
                    ));

                    $purity = trim((string) (
                        $it->purity
                        ?? $it->karat
                        ?? ''
                    ));

                    $huid = trim((string) (
                        $it->huid
                        ?? $it->hallmark_uid
                        ?? ''
                    ));

                    /*
                    |--------------------------------------------------------------------------
                    | Weights
                    |--------------------------------------------------------------------------
                    */

                    $netWeight = $firstPositive([
                        $it->net_weight ?? null,
                        $it->net_wt ?? null,
                        $it->silver_wt ?? null,
                        $it->gold_wt ?? null,
                    ]);

                    $normalQty = $firstPositive([
                        $it->quantity ?? null,
                        $it->qty ?? null,
                    ]);

                    if ($netWeight > 0) {
                        $quantity = $netWeight;
                    } elseif ($normalQty > 0) {
                        $quantity = $normalQty;
                    } else {
                        $quantity = 1;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Metal Rate
                    |--------------------------------------------------------------------------
                    */

                    $goldRate = (float) (
                        $it->gold_rate
                        ?? 0
                    );

                    $silverRate = (float) (
                        $it->silver_rate
                        ?? 0
                    );

                    $genericRate = (float) (
                        $it->rate
                        ?? $it->unit_price
                        ?? $it->price
                        ?? 0
                    );

                    $rate = $firstPositive([
                        $silverRate,
                        $goldRate,
                        $genericRate,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Unit
                    |--------------------------------------------------------------------------
                    */

                    $unit = strtoupper(
                        trim((string) (
                            $it->unit
                            ?? ''
                        ))
                    );

                    if ($unit === '') {
                        $unit = $netWeight > 0
                            ? 'GMS'
                            : 'NOS';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Item Amount
                    |--------------------------------------------------------------------------
                    */

                    $lineTotal = (float) (
                        $it->amount
                        ?? $it->line_total
                        ?? $it->total
                        ?? 0
                    );

                    if ($lineTotal <= 0 && $quantity > 0 && $rate > 0) {
                        $lineTotal = $quantity * $rate;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Extra Jewellery Info
                    |--------------------------------------------------------------------------
                    */

                    $grossWeight = $firstPositive([
                        $it->gross_weight ?? null,
                        $it->gross_wt ?? null,
                    ]);

                    $lessWeight = $firstPositive([
                        $it->less_weight ?? null,
                        $it->less_wt ?? null,
                    ]);

                    $makingCharge = (float) (
                        $it->making_charge
                        ?? $it->making_amount
                        ?? 0
                    );

                    $makingRate = (float) (
                        $it->making_rate
                        ?? 0
                    );

                    $makingType = trim((string) (
                        $it->making_charge_type
                        ?? ''
                    ));
                @endphp

                <tr>

                    {{-- SL --}}
                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>


                    {{-- DESCRIPTION --}}
                    <td>
                        <div class="item-name">
                            {{ $itemName }}
                        </div>

                        @if($description !== '')
                            <div class="item-extra">
                                {{ $description }}
                            </div>
                        @endif

                        @if(
                            $purity !== ''
                            || $huid !== ''
                            || $grossWeight > 0
                            || $lessWeight > 0
                            || $makingCharge > 0
                            || $makingRate > 0
                        )
                            <div class="item-extra">

                                @if($purity !== '')
                                    Purity:
                                    {{ $purity }}
                                @endif

                                @if($huid !== '')
                                    @if($purity !== '')
                                        |
                                    @endif

                                    HUID:
                                    {{ $huid }}
                                @endif

                                @if($grossWeight > 0)
                                    <br>
                                    Gross Wt:
                                    {{ $fmt3($grossWeight) }}
                                    GMS
                                @endif

                                @if($lessWeight > 0)
                                    |
                                    Less Wt:
                                    {{ $fmt3($lessWeight) }}
                                    GMS
                                @endif

                                @if($makingCharge > 0)
                                    <br>
                                    Making:
                                    ₹{{ $fmt2($makingCharge) }}
                                @elseif($makingRate > 0)
                                    <br>
                                    Making Rate:
                                    {{ $fmt2($makingRate) }}

                                    @if(
                                        strtolower($makingType)
                                        === 'percentage'
                                    )
                                        %
                                    @endif
                                @endif

                            </div>
                        @endif
                    </td>


                    {{-- HSN --}}
                    <td class="text-center">
                        {{ $hsn !== '' ? $hsn : '-' }}
                    </td>


                    {{-- QUANTITY --}}
                    <td class="text-right nowrap">
                        {{ $fmt3($quantity) }}
                        {{ $unit }}
                    </td>


                    {{-- RATE --}}
                    <td class="text-right nowrap">
                        {{ $rate > 0 ? $fmt2($rate) : '-' }}
                    </td>


                    {{-- PER --}}
                    <td class="text-center">
                        {{ $rate > 0 ? $unit : '' }}
                    </td>


                    {{-- AMOUNT --}}
                    <td class="amount-cell nowrap">
                        {{ $fmt2($lineTotal) }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td
                        colspan="7"
                        class="text-center"
                        style="height:15mm; vertical-align:middle;"
                    >
                        No item found
                    </td>
                </tr>

            @endforelse


            {{-- ===================================================== --}}
            {{-- SUBTOTAL --}}
            {{-- ===================================================== --}}

            @if($taxable > 0)

                <tr class="subtotal-row">

                    <td></td>

                    <td></td>

                    <td></td>

                    <td></td>

                    <td></td>

                    <td></td>

                    <td class="text-right">
                        {{ $fmt2($taxable) }}
                    </td>

                </tr>

            @endif


            {{-- ===================================================== --}}
            {{-- DISCOUNT --}}
            {{-- ===================================================== --}}

            @if($discount > 0)

                <tr class="discount-row">

                    <td></td>

                    <td
                        colspan="3"
                        class="tax-title"
                    >
                        Discount
                    </td>

                    <td></td>

                    <td></td>

                    <td class="amount-cell">
                        -{{ $fmt2($discount) }}
                    </td>

                </tr>

            @endif


            {{-- ===================================================== --}}
            {{-- CGST --}}
            {{-- ===================================================== --}}

            @if($cgstAmount > 0)

                <tr class="tax-row">

                    <td></td>

                    <td
                        colspan="3"
                        class="tax-title"
                    >
                        CGST
                        {{ $fmt2($cgstPercent) }}
                        %
                    </td>

                    <td class="tax-rate">
                        {{ $fmt2($cgstPercent) }}
                    </td>

                    <td class="text-center">
                        %
                    </td>

                    <td class="amount-cell">
                        {{ $fmt2($cgstAmount) }}
                    </td>

                </tr>

            @endif


            {{-- ===================================================== --}}
            {{-- SGST --}}
            {{-- ===================================================== --}}

            @if($sgstAmount > 0)

                <tr class="tax-row">

                    <td></td>

                    <td
                        colspan="3"
                        class="tax-title"
                    >
                        SGST
                        {{ $fmt2($sgstPercent) }}
                        %
                    </td>

                    <td class="tax-rate">
                        {{ $fmt2($sgstPercent) }}
                    </td>

                    <td class="text-center">
                        %
                    </td>

                    <td class="amount-cell">
                        {{ $fmt2($sgstAmount) }}
                    </td>

                </tr>

            @endif


            {{-- ===================================================== --}}
            {{-- IGST --}}
            {{-- ===================================================== --}}

            @if($igstAmount > 0)

                <tr class="tax-row">

                    <td></td>

                    <td
                        colspan="3"
                        class="tax-title"
                    >
                        IGST
                        {{ $fmt2($igstPercent) }}
                        %
                    </td>

                    <td class="tax-rate">
                        {{ $fmt2($igstPercent) }}
                    </td>

                    <td class="text-center">
                        %
                    </td>

                    <td class="amount-cell">
                        {{ $fmt2($igstAmount) }}
                    </td>

                </tr>

            @endif


            {{-- ===================================================== --}}
            {{-- ROUND OFF --}}
            {{-- ===================================================== --}}

            @if(abs($roundOff) > 0.0001)

                <tr class="round-row">

                    <td></td>

                    <td
                        colspan="3"
                        class="tax-title"
                    >
                        Round Off
                    </td>

                    <td></td>

                    <td></td>

                    <td class="amount-cell">
                        {{ $fmt2($roundOff) }}
                    </td>

                </tr>

            @endif


            {{-- ===================================================== --}}
            {{-- GRAND TOTAL --}}
            {{-- ===================================================== --}}

            <tr class="grand-row">

                <td></td>

                <td
                    colspan="2"
                    class="grand-label"
                >
                    Total
                </td>

                <td class="text-right nowrap">
                    @if($totalQty > 0)
                        {{ $fmt3($totalQty) }}
                    @endif
                </td>

                <td></td>

                <td></td>

                <td class="amount-cell">
                    ₹ {{ $fmt2($grandTotal) }}
                </td>

            </tr>

            </tbody>

        </table>

    </div>


    {{-- ============================================================= --}}
    {{-- BOTTOM SECTIONS --}}
    {{-- ============================================================= --}}

    <div class="bottom-section">

        {{-- AMOUNT IN WORDS --}}
        <table class="words-table">
            <tr>
                <td>

                    <div class="section-label">
                        Amount Chargeable (in words)
                    </div>

                    <div class="section-value">
                        INR
                        {{
                            $inv->amount_in_words
                            ?: tally_invoice_amount_words($grandTotal)
                        }}
                    </div>

                </td>
            </tr>
        </table>


        {{-- ========================================================= --}}
        {{-- PAYMENT + BANK --}}
        {{-- ========================================================= --}}

        @if(
            $paymentMethod !== ''
            || $payCash > 0
            || $payOnline > 0
            || $payCard > 0
            || $payCheque > 0
            || $payAdvance > 0
            || $payCredit > 0
            || $bankName !== ''
            || $bankAccountNumber !== ''
            || $bankIfsc !== ''
            || $bankUpi !== ''
        )

            <table class="bank-payment-table">
                <tr>

                    {{-- PAYMENT --}}
                    <td>

                        <div class="small-heading">
                            Payment Details
                        </div>

                        @if($paymentMethod !== '')
                            Payment Mode:
                            <strong>
                                {{ strtoupper($paymentMethod) }}
                            </strong>
                            <br>
                        @endif

                        @if($payCash > 0)
                            Cash:
                            ₹ {{ $fmt2($payCash) }}
                            <br>
                        @endif

                        @if($payOnline > 0)
                            Online/UPI:
                            ₹ {{ $fmt2($payOnline) }}
                            <br>
                        @endif

                        @if($onlineMode !== '')
                            Online Mode:
                            {{ strtoupper($onlineMode) }}
                            <br>
                        @endif

                        @if($onlineRef !== '')
                            Ref:
                            {{ $onlineRef }}
                            <br>
                        @endif

                        @if($upiId !== '')
                            UPI ID:
                            {{ $upiId }}
                            <br>
                        @endif

                        @if($payCard > 0)
                            Card:
                            ₹ {{ $fmt2($payCard) }}
                            <br>
                        @endif

                        @if($cardLast4 !== '')
                            Card Last 4:
                            {{ $cardLast4 }}
                            <br>
                        @endif

                        @if($cardRef !== '')
                            Card Ref:
                            {{ $cardRef }}
                            <br>
                        @endif

                        @if($payCheque > 0)
                            Cheque:
                            ₹ {{ $fmt2($payCheque) }}
                            <br>
                        @endif

                        @if($chequeNo !== '')
                            Cheque No:
                            {{ $chequeNo }}
                            <br>
                        @endif

                        @if($payAdvance > 0)
                            Advance:
                            ₹ {{ $fmt2($payAdvance) }}
                            <br>
                        @endif

                        @if($payCredit > 0)
                            Credit:
                            ₹ {{ $fmt2($payCredit) }}
                            <br>
                        @endif

                        @if($payReceivedTotal > 0)
                            Total Received:
                            ₹ {{ $fmt2($payReceivedTotal) }}
                            <br>
                        @endif

                        @if($balance > 0)
                            Balance:
                            ₹ {{ $fmt2($balance) }}
                            <br>
                        @endif

                        @if($paymentNotes !== '')
                            Notes:
                            {{ $paymentNotes }}
                        @endif

                    </td>


                    {{-- BANK --}}
                    <td>

                        <div class="small-heading">
                            Company's Bank Details
                        </div>

                        @if($bankAccountHolder !== '')
                            A/c Holder's Name :
                            <strong>
                                {{ $bankAccountHolder }}
                            </strong>
                            <br>
                        @endif

                        @if($bankName !== '')
                            Bank Name :
                            <strong>
                                {{ $bankName }}
                            </strong>
                            <br>
                        @endif

                        @if($bankAccountNumber !== '')
                            A/c No. :
                            <strong>
                                {{ $bankAccountNumber }}
                            </strong>
                            <br>
                        @endif

                        @if($bankBranch !== '')
                            Branch :
                            {{ $bankBranch }}
                            <br>
                        @endif

                        @if($bankIfsc !== '')
                            IFS Code :
                            <strong>
                                {{ $bankIfsc }}
                            </strong>
                            <br>
                        @endif

                        @if($bankUpi !== '')
                            UPI ID :
                            {{ $bankUpi }}
                        @endif

                    </td>

                </tr>
            </table>

        @endif


        {{-- ========================================================= --}}
        {{-- DECLARATION + SIGNATURE --}}
        {{-- ========================================================= --}}

        <table class="footer-info-table">
            <tr>

                <td class="footer-left">

                    @if(!empty($inv->terms))

                        <div class="small-heading">
                            Terms & Conditions
                        </div>

                        {!! nl2br(e($inv->terms)) !!}

                    @elseif(!empty($inv->notes))

                        <div class="small-heading">
                            Declaration
                        </div>

                        {!! nl2br(e($inv->notes)) !!}

                    @else

                        <div class="small-heading">
                            Declaration
                        </div>

                        We declare that this invoice shows
                        the actual price of the goods
                        described and that all particulars
                        are true and correct.

                    @endif

                </td>


                <td class="footer-right">

                    <strong>
                        for {{ strtoupper($bName) }}
                    </strong>

                    <div class="signature-area">

                        @if(!empty($sign))
                            <img
                                src="{{ $sign }}"
                                class="signature-image"
                                alt="Signature"
                            >
                        @endif

                        <div class="signature-text">
                            Authorised Signatory
                        </div>

                    </div>

                </td>

            </tr>
        </table>

    </div>


    <div class="computer-generated">
        This is a Computer Generated Invoice
    </div>

</div>

</body>
</html>