@php
    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? ($inv->business ?? null);
    $c = $client ?? ($inv->client ?? null);
    $items = $items ?? collect();


    $docType = strtolower((string)($type ?? ($inv->invoice_type ?? 'invoice')));

    $gstEnabled = (bool) ($b->gst_enabled ?? false);
    $businessGstin = trim((string) ($b->gstin ?? ''));

    $isGstBusiness = $gstEnabled && $businessGstin !== '';

    if (!$isGstBusiness) {
        $docLabel = 'JEWELLERY INVOICE';
    } else {
        $docLabel = match ($docType) {
            'quotation' => 'QUOTATION',
            'proforma'  => 'PROFORMA INVOICE',
            default     => 'TAX INVOICE',
        };
    }


    $itemExtraPrices = $itemExtraPrices ?? [];

    $fmt2 = fn($v) => number_format((float)$v, 2, '.', '');
    $fmt3 = fn($v) => number_format((float)$v, 3, '.', '');
    $dmy  = fn($date) => $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '';

    $invoiceNo   = $inv->invoice_number ?? $inv->invoice_no ?? '-';
    $invoiceDate = $inv->invoice_date ?? $inv->date ?? null;

    $taxable     = (float)($subtotal ?? ($inv->subtotal ?? 0));
    $cgstAmount  = (float)($cgst_amount ?? ($inv->cgst_amount ?? 0));
    $sgstAmount  = (float)($sgst_amount ?? ($inv->sgst_amount ?? 0));
    $igstAmount  = (float)($igst_amount ?? ($inv->igst_amount ?? 0));
    $discount    = (float)($inv->discount_total ?? 0);
    $roundOff    = (float)($inv->round_off ?? 0);
    $grandTotal  = (float)($grand_total ?? ($inv->total ?? 0));
    $received    = (float)($received ?? ($inv->received_amount ?? 0));
    $balance     = (float)($balance ?? ($inv->balance ?? max(0, $grandTotal - $received)));

    $bName   = $b->name ?? 'Your Jewellery Store';
    $bAddr   = trim((string)($b->address ?? ''));
    $bCity   = trim((string)($b->city ?? ''));
    $bState  = trim((string)($b->state ?? ''));
    $bMobile = $b->mobile ?? $b->phone ?? '';
    $bEmail  = $b->email ?? '';
    $bGstin  = $b->gstin ?? ($inv->gst_no ?? '');

    $cName   = $c->name ?? '-';
    $cAddr   = $c->address ?? '-';
    $cMobile = $c->mobile ?? $c->phone ?? '';
    $cGstin  = $c->gstin ?? $c->gst_number ?? '';
    $cPan    = $c->pan ?? $c->pan_number ?? '';

    /*
    |--------------------------------------------------------------------------
    | Payment + selected bank data
    |--------------------------------------------------------------------------
    */
    $pay = $paymentDetails ?? [];

    $payCash = (float)($pay['cash_amount'] ?? ($payRow->cash_amount ?? 0));
    $payOnline = (float)($pay['online_amount'] ?? ($payRow->online_amount ?? 0));
    $payCard = (float)($pay['card_amount'] ?? ($payRow->card_amount ?? 0));
    $payCheque = (float)($pay['cheque_amount'] ?? ($payRow->cheque_amount ?? 0));
    $payCredit = (float)($pay['credit_sales_excess_amount'] ?? ($payRow->credit_sales_excess_amount ?? 0));
    $payAdvance = (float)($pay['advance_amount'] ?? ($payRow->advance_amount ?? 0));
    $payReceivedTotal = (float)($pay['received_total'] ?? ($payRow->received_total ?? $received));

    $onlineMode = trim((string)($pay['online_mode'] ?? ($payRow->online_mode ?? '')));
    $onlineRef = trim((string)($pay['online_ref'] ?? ($payRow->online_ref ?? '')));
    $upiId = trim((string)($pay['upi_id'] ?? ($payRow->upi_id ?? '')));
    $cardLast4 = trim((string)($pay['card_last4'] ?? ($payRow->card_last4 ?? '')));
    $cardRef = trim((string)($pay['card_ref'] ?? ($payRow->card_ref ?? '')));
    $chequeNo = trim((string)($pay['cheque_no'] ?? ($payRow->cheque_no ?? '')));
    $enteredBankName = trim((string)($pay['bank_name_entered'] ?? ($payRow->bank_name ?? '')));
    $paymentNotes = trim((string)($pay['notes'] ?? ($payRow->notes ?? '')));
    $paymentMethod = trim((string)($pay['method'] ?? ($inv->payment_method ?? '')));

    $bank = $selectedBank ?? null;

    $bankName = trim((string)(
        $bank->bank_name
        ?? $enteredBankName
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
        || $payReceivedTotal > 0
        || $onlineMode !== ''
        || $onlineRef !== ''
        || $upiId !== ''
        || $cardLast4 !== ''
        || $cardRef !== ''
        || $chequeNo !== ''
        || $paymentNotes !== '';

    $hasBankDetails =
        $bank !== null
        && (
            $bankName !== ''
            || $bankAccountHolder !== ''
            || $bankAccountNumber !== ''
            || $bankIfsc !== ''
            || $bankBranch !== ''
            || $bankUpi !== ''
        );

    if (!function_exists('inr_words_jewellery')) {
        function inr_words_jewellery($amount)
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

            if ($words === '') {
                $words = 'Zero';
            }

            $result = $words . ' Rupees';

            if ($paise > 0) {
                $result .= ' and ' . $twoDigits($paise) . ' Paise';
            }

            return $result . ' Only';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Dynamic Column Visibility
    |--------------------------------------------------------------------------
    | Agar kisi column ki poore invoice me value 0/null/empty hai,
    | to wo column header ke sath hide ho jayega.
    */

    $showHsn       = false;
    $showPurity    = false;
    $showNetWt     = false;
    $showGoldRate  = false;
    $showGoldValue = false;
    $showSilverValue = false;
    $showMaking    = false;
    $showGemstone  = false;
    $showDiamond   = false;

    foreach ($items as $itCheck) {
        $hsnCheck = $itCheck->hsn_code ?? $itCheck->sac_code ?? $itCheck->hsn ?? null;

        $purityCheck = $itCheck->purity ?? $itCheck->karat ?? null;

        $goldWeightCheck = (float)($itCheck->gold_wt ?? $itCheck->net_weight ?? $itCheck->net_wt ?? 0);
        $silverWeightCheck = (float)($itCheck->silver_wt ?? 0);

        $netWeightCheck = (float)($itCheck->net_weight ?? $itCheck->net_wt ?? $goldWeightCheck);

        $goldRateCheck = (float)($itCheck->gold_rate ?? 0);
        $silverRateCheck = (float)($itCheck->silver_rate ?? 0);

        $goldAmountCheck = (float)($itCheck->gold_amount ?? 0);
        $silverAmountCheck = (float)($itCheck->silver_amount ?? 0);

        if ($goldAmountCheck <= 0 && $goldWeightCheck > 0 && $goldRateCheck > 0) {
            $goldAmountCheck = $goldWeightCheck * $goldRateCheck;
        }

        // if ($silverAmountCheck <= 0 && $silverWeightCheck > 0 && $silverRateCheck > 0) {
        //     $silverAmountCheck = $silverWeightCheck * $silverRateCheck;
        // }

        if ($silverWeightCheck > 0 && $silverRateCheck > 0) {
            $silverAmountCheck = $silverWeightCheck * $silverRateCheck;
        }

        $itemIdCheck = (int)($itCheck->item_id ?? 0);
        $extraPriceCheck = $itemExtraPrices[$itemIdCheck] ?? [];

        $diamondAmountCheck = (float) (
            $itCheck->diamond_charges
            ?? $itCheck->diamond_price
            ?? $extraPriceCheck['diamond_price']
            ?? 0
        );

        $gemstonePriceCheck = (float) (
            $itCheck->stone_charges
            ?? $itCheck->gemstone_amount
            ?? $itCheck->stone_amount
            ?? $itCheck->stone_price
            ?? $extraPriceCheck['gemstone_price']
            ?? 0
        );

        // $metalAmountCheck = $goldAmountCheck + $silverAmountCheck;

        // $makingPercentCheck = (float)($itCheck->making_charge ?? $itCheck->making_rate ?? $itCheck->making_amount ?? 0);
        // $makingChargeCheck = $metalAmountCheck * ($makingPercentCheck / 100);


        $metalAmountCheck = $goldAmountCheck + $silverAmountCheck;

        $makingChargeCheck = (float) (
            $itCheck->making_charge
            ?? $itCheck->making_amount
            ?? 0
        );

        if (!empty($hsnCheck) && $hsnCheck !== '-') {
            $showHsn = true;
        }

        if (!empty($purityCheck) && $purityCheck !== '-') {
            $showPurity = true;
        }

        if ($netWeightCheck > 0) {
            $showNetWt = true;
        }

        if ($goldRateCheck > 0) {
            $showGoldRate = true;
        }

        if ($goldAmountCheck > 0) {
            $showGoldValue = true;
        }

        if ($silverWeightCheck > 0 && $silverRateCheck > 0 && $silverAmountCheck > 0) {
            $showSilverValue = true;
        }

        if ($makingChargeCheck > 0) {
            $showMaking = true;
        }

        if ($gemstonePriceCheck > 0) {
            $showGemstone = true;
        }

        if ($diamondAmountCheck > 0) {
            $showDiamond = true;
        }
    }

    $itemColspan = 3; // #, Item Details, Total

    if ($showHsn) {
        $itemColspan++;
    }

    if ($showPurity) {
        $itemColspan++;
    }

    if ($showNetWt) {
        $itemColspan++;
    }

    if ($showGoldRate) {
        $itemColspan++;
    }

    if ($showGoldValue) {
        $itemColspan++;
    }

    if ($showSilverValue) {
        $itemColspan++;
    }

    if ($showMaking) {
        $itemColspan++;
    }

    if ($showGemstone) {
        $itemColspan++;
    }

    if ($showDiamond) {
        $itemColspan++;
    }
@endphp

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $docLabel }} {{ $invoiceNo }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 14px;
            background: #ffffff;
            color: #111111;
        }

        .invoice-wrapper {
            border: 1px solid #222;
            padding: 12px;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #222;
            padding-bottom: 8px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .company-info {
            font-size: 11px;
            line-height: 17px;
        }

        .invoice-title {
            margin-top: 8px;
            display: inline-block;
            border: 1px solid #222;
            padding: 5px 18px;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            background: #f5f5f5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            border: 1px solid #444;
            padding: 7px;
            vertical-align: top;
            line-height: 17px;
        }

        .items-table {
            margin-top: 10px;
        }

        .items-table th {
            border: 1px solid #444;
            padding: 6px 4px;
            background: #f2f2f2;
            font-size: 10px;
            text-align: center;
        }

        .items-table td {
            border: 1px solid #444;
            padding: 6px 4px;
            vertical-align: top;
            font-size: 10px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .product-name {
            font-weight: bold;
            font-size: 11px;
        }

        .small-text {
            font-size: 9px;
            color: #333;
            line-height: 15px;
        }

        .jewel-details {
            margin-top: 4px;
            font-size: 9px;
            line-height: 15px;
        }

        .summary-table {
            width: 45%;
            margin-left: auto;
            margin-top: 10px;
        }

        .summary-table td {
            border: 1px solid #444;
            padding: 6px;
            font-size: 11px;
        }

        .summary-table .grand td {
            font-size: 13px;
            font-weight: bold;
            background: #f2f2f2;
        }

        .amount-words {
            margin-top: 10px;
            border: 1px solid #444;
            padding: 8px;
            font-size: 11px;
            line-height: 18px;
        }

        .payment-section {
            margin-top: 10px;
        }

        .payment-section td {
            border: 1px solid #444;
            padding: 7px;
            vertical-align: top;
            font-size: 10px;
            line-height: 16px;
        }

        .section-heading {
            font-size: 11px;
            font-weight: bold;
            background: #f2f2f2;
            padding: 5px 7px;
            border: 1px solid #444;
            border-bottom: 0;
        }

        .payment-label {
            font-weight: bold;
        }

        .terms-box {
            margin-top: 10px;
            border: 1px solid #444;
            padding: 8px;
            font-size: 10px;
            line-height: 16px;
        }

        .footer-table {
            margin-top: 20px;
        }

        .footer-table td {
            padding-top: 25px;
            vertical-align: bottom;
        }

        .sign-box {
            text-align: right;
        }

        .sign-box img {
            max-height: 45px;
        }
    </style>
</head>

<body>
<div class="invoice-wrapper">

    <div class="header">
        @if(!empty($logo))
            <img src="{{ $logo }}" style="max-width:90px; max-height:65px;"><br>
        @endif

        <div class="company-name">{{ $bName }}</div>

        <div class="company-info">
            {{ $bAddr }}

            @if($bCity)
                , {{ $bCity }}
            @endif

            @if($bState)
                , {{ $bState }}
            @endif

            <br>

            @if($bMobile)
                Mobile: {{ $bMobile }}
            @endif

            @if($bEmail)
                | Email: {{ $bEmail }}
            @endif

            @if($bGstin)
                | GSTIN: {{ $bGstin }}
            @endif
        </div>

        <div class="invoice-title">{{ $docLabel }}</div>
    </div>

    <table class="meta-table" style="margin-top:10px;">
        <tr>
            <td style="width:50%;">
                <strong>Bill To:</strong><br>
                {{ strtoupper($cName) }}<br>

                @if(!empty($cAddr) && $cAddr !== '-')
                    {{ $cAddr }}<br>
                @endif

                @if(!empty($cMobile))
                    Mobile: {{ $cMobile }}<br>
                @endif

                @if(!empty($cGstin))
                    GSTIN: {{ $cGstin }}<br>
                @endif

                @if(!empty($cPan))
                    PAN: {{ $cPan }}
                @endif
            </td>

            <td style="width:25%;">
                <strong>Invoice No:</strong><br>
                {{ $invoiceNo }}<br><br>

                @if($paymentMethod !== '')
                    <strong>Payment Method:</strong><br>
                    {{ strtoupper($paymentMethod) }}
                @endif
            </td>

            <td style="width:25%;">
                <strong>Invoice Date:</strong><br>
                {{ $dmy($invoiceDate) }}<br><br>

                @php
                    $placeOfSupply = $inv->place_of_supply_state ?? $c->state ?? null;
                @endphp

                @if(!empty($placeOfSupply))
                    <strong>Place of Supply:</strong><br>
                    {{ $placeOfSupply }}
                @endif
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th style="width:22%;">Item Details</th>

            @if($showHsn)
                <th style="width:8%;">HSN</th>
            @endif

            @if($showPurity)
                <th style="width:8%;">Purity</th>
            @endif

            @if($showNetWt)
                <th style="width:8%;">Net Wt.</th>
            @endif

            @if($showGoldRate)
                <th style="width:9%;">Gold Rate</th>
            @endif

            @if($showGoldValue)
                <th style="width:9%;">Gold Value</th>
            @endif

            @if($showSilverValue)
                <th style="width:9%;">Silver</th>
            @endif

            @if($showMaking)
                <th style="width:8%;">Making</th>
            @endif

            @if($showGemstone)
                <th style="width:8%;">Gemstone</th>
            @endif

            @if($showDiamond)
                <th style="width:8%;">Diamond</th>
            @endif

            <th style="width:8%;">Total</th>
        </tr>
        </thead>

        <tbody>
        @forelse($items as $index => $it)
            @php
                $name = $it->item->name ?? $it->name ?? $it->item_name ?? 'Jewellery Product';
                $desc = $it->description ?? '';

                $hsn = $it->hsn_code ?? $it->sac_code ?? $it->hsn ?? null;

                $qty = (float)($it->quantity ?? $it->qty ?? 1);
                $unit = $it->unit ?? '';

                $goldWeight = (float)($it->gold_wt ?? $it->net_weight ?? $it->net_wt ?? 0);
                $silverWeight = (float)($it->silver_wt ?? 0);

                $grossWeight = $it->gross_weight ?? $it->gross_wt ?? $goldWeight;
                $netWeight = (float)($it->net_weight ?? $it->net_wt ?? $goldWeight);
                $lessWeight = (float)($it->less_weight ?? $it->less_wt ?? 0);

                $purity = $it->purity ?? $it->karat ?? null;
                $huid = $it->huid ?? $it->hallmark_uid ?? null;

                $goldRate = (float)($it->gold_rate ?? 0);
                $silverRate = (float)($it->silver_rate ?? 0);

                $goldAmount = (float)($it->gold_amount ?? 0);
                $silverAmount = (float)($it->silver_amount ?? 0);

                if ($goldAmount <= 0 && $goldWeight > 0 && $goldRate > 0) {
                    $goldAmount = $goldWeight * $goldRate;
                }

                // if ($silverAmount <= 0 && $silverWeight > 0 && $silverRate > 0) {
                //     $silverAmount = $silverWeight * $silverRate;
                // }

                if ($silverWeight > 0 && $silverRate > 0) {
                    $silverAmount = $silverWeight * $silverRate;
                }

                $extraPrice = $itemExtraPrices[(int)($it->item_id ?? 0)] ?? [];

                $diamondAmount = (float) (
                    $it->diamond_charges
                    ?? $it->diamond_price
                    ?? $extraPrice['diamond_price']
                    ?? 0
                );

                $gemstonePrice = (float) (
                    $it->stone_charges
                    ?? $it->gemstone_amount
                    ?? $it->stone_amount
                    ?? $it->stone_price
                    ?? $extraPrice['gemstone_price']
                    ?? 0
                );

                $stoneAmount = 0;
                $stoneGemTotal = $diamondAmount + $stoneAmount + $gemstonePrice;

                /*
                |--------------------------------------------------------------------------
                | Making Charge
                |--------------------------------------------------------------------------
                | Aapke current logic ke hisab se making_charge DB me percentage save hai.
                | Isliye metal amount ka percentage nikal rahe hain.
                */
                // $makingPercent = (float)($it->making_charge ?? $it->making_rate ?? $it->making_amount ?? 0);

                // $metalAmount = $goldAmount + $silverAmount;

                // $makingCharge = $metalAmount * ($makingPercent / 100);

                // $makingPerGram = $it->making_per_gram ?? null;
                // $wastage = (float)($it->wastage ?? $it->wastage_percent ?? 0);

                // $taxPercent = (float)($it->tax_percent ?? $inv->tax_percent ?? 0);
                // $taxAmount = (float)($it->tax_amount ?? 0);

                // $lineTotal = (float)($it->amount ?? $it->line_total ?? 0);



                $metalAmount = $goldAmount + $silverAmount;

                // invoice_items table me making_rate already saved amount hai,
                // isliye yahan koi calculation nahi karni.
                $makingCharge = (float) (
                    $it->making_charge
                    ?? $it->making_amount
                    ?? 0
                );

                $makingPerGram = $it->making_per_gram ?? null;
                $wastage = (float)($it->wastage ?? $it->wastage_percent ?? 0);

                $taxPercent = (float)($it->tax_percent ?? $inv->tax_percent ?? 0);
                $taxAmount = (float)($it->tax_amount ?? 0);

                $lineTotal = (float)($it->amount ?? $it->line_total ?? $it->total ?? 0);

                if ($lineTotal <= 0) {
                    $lineTotal =
                        $metalAmount
                        + $stoneGemTotal
                        + $makingCharge
                        + $taxAmount;
                }
            @endphp

            <tr>
                <td class="text-center">{{ $index + 1 }}</td>

                <td>
                    <div class="product-name">{{ $name }}</div>

                    @if(!empty($desc))
                        <div class="small-text">{{ $desc }}</div>
                    @endif

                    <div class="jewel-details">
                        @if(!empty($huid))
                            <strong>HUID:</strong> {{ $huid }}<br>
                        @endif

                        @if($qty > 0)
                            <strong>Qty:</strong> {{ $qty }} {{ $unit }}<br>
                        @endif

                        @if($lessWeight > 0)
                            <strong>Less Wt:</strong> {{ $fmt3($lessWeight) }} gm<br>
                        @endif

                        @if($wastage > 0)
                            <strong>Wastage:</strong> {{ $wastage }}%
                        @endif
                    </div>
                </td>

                @if($showHsn)
                    <td class="text-center">
                        {{ !empty($hsn) && $hsn !== '-' ? $hsn : '-' }}
                    </td>
                @endif

                @if($showPurity)
                    <td class="text-center">
                        {{ !empty($purity) && $purity !== '-' ? $purity : '-' }}
                    </td>
                @endif

                @if($showNetWt)
                    <td class="text-right">
                        {{ $netWeight > 0 ? $fmt3($netWeight) . ' gm' : '-' }}
                    </td>
                @endif

                @if($showGoldRate)
                    <td class="text-right">
                        {{ $goldRate > 0 ? '₹ ' . $fmt2($goldRate) : '-' }}
                    </td>
                @endif

                @if($showGoldValue)
                    <td class="text-right">
                        {{ $goldAmount > 0 ? '₹ ' . $fmt2($goldAmount) : '-' }}
                    </td>
                @endif


                @if($showSilverValue)
                    <td class="text-right">
                        @if($silverWeight > 0 && $silverRate > 0)
                            ₹ {{ $fmt2($silverAmount) }}
                            
                        @else
                            -
                        @endif
                    </td>
                @endif

                @if($showMaking)
                    <td class="text-right">
                        @if($makingCharge > 0)
                            ₹ {{ $fmt2($makingCharge) }}

                            @if($makingPerGram)
                                <br>
                                <span class="small-text">₹{{ $fmt2($makingPerGram) }}/gm</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                @endif

                @if($showGemstone)
                    <td class="text-right">
                        {{ $gemstonePrice > 0 ? '₹ ' . $fmt2($gemstonePrice) : '-' }}
                    </td>
                @endif

                @if($showDiamond)
                    <td class="text-right">
                        {{ $diamondAmount > 0 ? '₹ ' . $fmt2($diamondAmount) : '-' }}
                    </td>
                @endif

                <td class="text-right">
                    ₹ {{ $fmt2($lineTotal) }}

                    @if($taxPercent > 0)
                        <br>
                        <span class="small-text">GST {{ $fmt2($taxPercent) }}%</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $itemColspan }}" class="text-center">No jewellery item found</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <table class="summary-table">
        @if($taxable > 0)
            <tr>
                <td>Taxable Amount</td>
                <td class="text-right">₹ {{ $fmt2($taxable) }}</td>
            </tr>
        @endif

        @if($discount > 0)
            <tr>
                <td>Discount</td>
                <td class="text-right">₹ {{ $fmt2($discount) }}</td>
            </tr>
        @endif

        @if($igstAmount > 0)
            <tr>
                <td>IGST</td>
                <td class="text-right">₹ {{ $fmt2($igstAmount) }}</td>
            </tr>
        @else
            @if($cgstAmount > 0)
                <tr>
                    <td>CGST</td>
                    <td class="text-right">₹ {{ $fmt2($cgstAmount) }}</td>
                </tr>
            @endif

            @if($sgstAmount > 0)
                <tr>
                    <td>SGST</td>
                    <td class="text-right">₹ {{ $fmt2($sgstAmount) }}</td>
                </tr>
            @endif
        @endif

        @if($roundOff != 0)
            <tr>
                <td>Round Off</td>
                <td class="text-right">₹ {{ $fmt2($roundOff) }}</td>
            </tr>
        @endif

        @if($received > 0)
            <tr>
                <td>Received Amount</td>
                <td class="text-right">₹ {{ $fmt2($received) }}</td>
            </tr>
        @endif

        @if($balance > 0)
            <tr>
                <td>Balance</td>
                <td class="text-right">₹ {{ $fmt2($balance) }}</td>
            </tr>
        @endif

        <tr class="grand">
            <td>Total Amount</td>
            <td class="text-right">₹ {{ $fmt2($grandTotal) }}</td>
        </tr>
    </table>

    @if($grandTotal > 0)
        <div class="amount-words">
            <strong>Amount in Words:</strong>
            {{ $inv->amount_in_words ?: inr_words_jewellery($grandTotal) }}
        </div>
    @endif

    @if($docType === 'tax' && ($hasPaymentDetails || $hasBankDetails))
        <div class="section-heading">Payment & Bank Details</div>

        <table class="payment-section" style="margin-top:0;">
            <tr>
                <td style="width:50%;">
                    <strong>Payment Details</strong><br>

                    @if($paymentMethod !== '')
                        <span class="payment-label">Payment Method:</span>
                        {{ strtoupper($paymentMethod) }}<br>
                    @endif

                    @if($payCash > 0)
                        <span class="payment-label">Cash:</span>
                        ₹ {{ $fmt2($payCash) }}<br>
                    @endif

                    @if($payOnline > 0)
                        <span class="payment-label">Online / UPI:</span>
                        ₹ {{ $fmt2($payOnline) }}<br>
                    @endif

                    @if($onlineMode !== '')
                        <span class="payment-label">Online Mode:</span>
                        {{ strtoupper($onlineMode) }}<br>
                    @endif

                    @if($onlineRef !== '')
                        <span class="payment-label">Online Ref:</span>
                        {{ $onlineRef }}<br>
                    @endif

                    @if($upiId !== '')
                        <span class="payment-label">UPI ID:</span>
                        {{ $upiId }}<br>
                    @endif

                    @if($payCard > 0)
                        <span class="payment-label">Card:</span>
                        ₹ {{ $fmt2($payCard) }}<br>
                    @endif

                    @if($cardLast4 !== '')
                        <span class="payment-label">Card Last 4:</span>
                        {{ $cardLast4 }}<br>
                    @endif

                    @if($cardRef !== '')
                        <span class="payment-label">Card Ref:</span>
                        {{ $cardRef }}<br>
                    @endif

                    @if($payCheque > 0)
                        <span class="payment-label">Cheque:</span>
                        ₹ {{ $fmt2($payCheque) }}<br>
                    @endif

                    @if($chequeNo !== '')
                        <span class="payment-label">Cheque No:</span>
                        {{ $chequeNo }}<br>
                    @endif

                    @if($payAdvance > 0)
                        <span class="payment-label">Advance:</span>
                        ₹ {{ $fmt2($payAdvance) }}<br>
                    @endif

                    @if($payCredit > 0)
                        <span class="payment-label">Credit / Excess:</span>
                        ₹ {{ $fmt2($payCredit) }}<br>
                    @endif

                    @if($payReceivedTotal > 0)
                        <span class="payment-label">Total Received:</span>
                        ₹ {{ $fmt2($payReceivedTotal) }}<br>
                    @endif

                    @if($paymentNotes !== '')
                        <span class="payment-label">Payment Note:</span>
                        {{ $paymentNotes }}
                    @endif
                </td>

                <td style="width:50%;">
                    <strong>Bank Account</strong><br>

                    @if($bankName !== '')
                        <span class="payment-label">Bank:</span>
                        {{ $bankName }}<br>
                    @endif

                    @if($bankAccountHolder !== '')
                        <span class="payment-label">Account Holder:</span>
                        {{ $bankAccountHolder }}<br>
                    @endif

                    @if($bankAccountNumber !== '')
                        <span class="payment-label">Account No:</span>
                        {{ $bankAccountNumber }}<br>
                    @endif

                    @if($bankIfsc !== '')
                        <span class="payment-label">IFSC:</span>
                        {{ $bankIfsc }}<br>
                    @endif

                    @if($bankBranch !== '')
                        <span class="payment-label">Branch:</span>
                        {{ $bankBranch }}<br>
                    @endif

                    @if($bankUpi !== '')
                        <span class="payment-label">Bank UPI ID:</span>
                        {{ $bankUpi }}<br>
                    @endif

                    @if(!$hasBankDetails)
                        No bank account selected.
                    @endif
                </td>
            </tr>
        </table>
    @endif

    @if(!empty($inv->notes))
        <div class="terms-box">
            <strong>Notes:</strong><br>
            {!! nl2br(e($inv->notes)) !!}
        </div>
    @endif

    @if(!empty($inv->terms))
        <div class="terms-box">
            <strong>Terms & Conditions:</strong><br>
            {!! nl2br(e($inv->terms)) !!}
        </div>
    @endif

    <table class="footer-table">
        <tr>
            <td style="width:50%;">
                <strong>Customer Signature</strong>
            </td>

            <td style="width:50%;" class="sign-box">
                @if(!empty($sign))
                    <img src="{{ $sign }}"><br>
                @endif

                <strong>Authorised Signatory</strong><br>
                {{ $bName }}
            </td>
        </tr>
    </table>

</div>
</body>
</html>