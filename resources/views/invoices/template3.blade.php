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
            size: A4;
            margin: 5mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10px;
            color: #000;
            background: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .page-frame {
            width: 100%;
            border: 1px solid #000;
            padding: 3mm;
        }

        .top-space {
            height: 35mm;
        }

        .invoice-main {
            width: 100%;
            border: 1px solid #000;
        }

        .invoice-title {
            text-align: center;
            font-size: 17px;
            font-weight: bold;
            padding: 4mm 2mm;
            border-bottom: 1px solid #000;
        }

        .party-table {
            width: 100%;
            border-collapse: collapse;
        }

        .party-table > tbody > tr > td {
            vertical-align: top;
            padding: 0;
        }

        .party-left {
            width: 51%;
            border-right: 1px solid #000;
        }

        .party-right {
            width: 49%;
        }

        .seller-box {
            padding: 3mm;
            border-bottom: 1px solid #000;
            line-height: 1.4;
        }

        .buyer-box {
            padding: 3mm;
            line-height: 1.4;
        }

        .business-name {
            font-size: 12px;
            font-weight: bold;
        }

        .buyer-name {
            font-size: 11px;
            font-weight: bold;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            width: 50%;
            padding: 2mm;
            vertical-align: top;
            border-bottom: 1px solid #000;
            font-size: 9.5px;
        }

        .meta-table td:first-child {
            border-right: 1px solid #000;
        }

        .meta-table tr:last-child td {
            border-bottom: 0;
        }

        .meta-label {
            font-size: 9px;
        }

        .meta-value {
            font-size: 10px;
            font-weight: bold;
            margin-top: 1mm;
        }

        .items-table {
            margin-top: 3mm;
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 2mm 1.5mm;
            vertical-align: top;
        }

        .items-table th {
            text-align: center;
            font-weight: normal;
            font-size: 9.5px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .small {
            font-size: 8px;
            line-height: 1.4;
        }

        .tax-label {
            text-align: right;
            font-weight: bold;
            font-style: italic;
        }

        .amount-words {
            border: 1px solid #000;
            border-top: 0;
            padding: 2mm;
            font-size: 9px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            width: 50%;
            vertical-align: top;
            border: 1px solid #000;
            border-top: 0;
            padding: 2mm;
            font-size: 8.5px;
            line-height: 1.4;
        }

        .signature {
            text-align: right;
        }

        .signature img {
            max-height: 35px;
            max-width: 120px;
        }

        .computer-generated {
            text-align: center;
            font-size: 7px;
            margin-top: 2mm;
        }
    </style>
</head>

<body>

<div class="page-frame">

    <div class="top-space"></div>

    <div class="invoice-main">

        <div class="invoice-title">
            {{ $docLabel }}
        </div>

        <table class="party-table">
            <tr>

                <td class="party-left">

                    <div class="seller-box">

                        <div class="business-name">
                            {{ strtoupper($bName) }}
                        </div>

                        @if($bAddr)
                            {{ strtoupper($bAddr) }}<br>
                        @endif

                        @if($bCity)
                            {{ strtoupper($bCity) }}<br>
                        @endif

                        @if($bGstin)
                            GSTIN/UIN: {{ strtoupper($bGstin) }}<br>
                        @endif

                        @if($bState)
                            State Name : {{ $bState }}

                            @if($bStateCode)
                                , Code : {{ $bStateCode }}
                            @endif

                            <br>
                        @endif

                        @if($bEmail)
                            E-Mail : {{ $bEmail }}
                        @endif

                    </div>

                    <div class="buyer-box">

                        Buyer<br>

                        @if($cBusinessName)
                            <div class="buyer-name">
                                {{ strtoupper($cBusinessName) }}
                            </div>
                        @endif

                        <div class="buyer-name">
                            {{ strtoupper($cName) }}
                        </div>

                        @if($cAddr)
                            {{ strtoupper($cAddr) }}<br>
                        @endif

                        @if($cCity)
                            {{ strtoupper($cCity) }}<br>
                        @endif

                        @if($cMobile)
                            MOB---{{ $cMobile }}<br>
                        @endif

                        @if($cGstin)
                            GSTIN/UIN: {{ strtoupper($cGstin) }}<br>
                        @endif

                        @if($cState)
                            State Name : {{ $cState }}

                            @if($cStateCode)
                                , Code : {{ $cStateCode }}
                            @endif
                        @endif

                    </div>

                </td>

                <td class="party-right">

                    <table class="meta-table">

                        <tr>
                            <td>
                                <div class="meta-label">Invoice No.</div>

                                <div class="meta-value">
                                    {{ $invoiceNo }}
                                </div>
                            </td>

                            <td>
                                <div class="meta-label">Dated</div>

                                <div class="meta-value">
                                    {{ $invoiceDateFormat($invoiceDate) }}
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="meta-label">
                                    Delivery Note
                                </div>

                                @if($deliveryNote)
                                    <div class="meta-value">
                                        {{ $deliveryNote }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div class="meta-label">
                                    Mode/Terms of Payment
                                </div>

                                @if($paymentTerms)
                                    <div class="meta-value">
                                        {{ $paymentTerms }}
                                    </div>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="meta-label">
                                    Supplier's Ref.
                                </div>

                                @if($supplierRef)
                                    <div class="meta-value">
                                        {{ $supplierRef }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div class="meta-label">
                                    Other Reference(s)
                                </div>

                                @if($otherReference)
                                    <div class="meta-value">
                                        {{ $otherReference }}
                                    </div>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="meta-label">
                                    Buyer's Order No.
                                </div>

                                @if($buyerOrderNo)
                                    <div class="meta-value">
                                        {{ $buyerOrderNo }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div class="meta-label">
                                    Dated
                                </div>

                                @if($buyerOrderDate)
                                    <div class="meta-value">
                                        {{ $invoiceDateFormat($buyerOrderDate) }}
                                    </div>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="meta-label">
                                    Despatch Document No.
                                </div>

                                @if($dispatchDocumentNo)
                                    <div class="meta-value">
                                        {{ $dispatchDocumentNo }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div class="meta-label">
                                    Delivery Note Date
                                </div>

                                @if($deliveryNoteDate)
                                    <div class="meta-value">
                                        {{ $invoiceDateFormat($deliveryNoteDate) }}
                                    </div>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="meta-label">
                                    Despatched through
                                </div>

                                @if($dispatchedThrough)
                                    <div class="meta-value">
                                        {{ $dispatchedThrough }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div class="meta-label">
                                    Destination
                                </div>

                                @if($destination)
                                    <div class="meta-value">
                                        {{ $destination }}
                                    </div>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td colspan="2">
                                <div class="meta-label">
                                    Terms of Delivery
                                </div>

                                @if($termsOfDelivery)
                                    <div class="meta-value">
                                        {{ $termsOfDelivery }}
                                    </div>
                                @endif
                            </td>
                        </tr>

                    </table>

                </td>

            </tr>
        </table>

    </div>

    <div class="amount-words">
    Amount Chargeable (in words)<br>

    <strong>
        INR
        {{
            $inv->amount_in_words
            ?: tally_invoice_amount_words($grandTotal)
        }}
    </strong>
</div>

<table class="footer-table">
    <tr>

        <td>
            <strong>Declaration</strong><br>

            @if(!empty($inv->terms))
                {!! nl2br(e($inv->terms)) !!}
            @elseif(!empty($inv->notes))
                {!! nl2br(e($inv->notes)) !!}
            @else
                We declare that this invoice shows the actual
                price of the goods described and that all
                particulars are true and correct.
            @endif
        </td>

        <td class="signature">

            <strong>
                for {{ strtoupper($bName) }}
            </strong>

            <br><br>

            @if(!empty($sign))
                <img src="{{ $sign }}">
                <br>
            @endif

            <br>

            <strong>
                Authorised Signatory
            </strong>

        </td>

    </tr>
</table>

<div class="computer-generated">
    This is a Computer Generated Invoice
</div>

</div>

</body>
</html>

</body>
</html>