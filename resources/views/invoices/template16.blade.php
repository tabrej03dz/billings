@php
    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? ($inv->business ?? null);
    $c = $client ?? ($inv->client ?? null);
    $items = $items ?? collect();

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

    $isIGST = $igstAmount > 0;

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

        return $result . ' Only';
    }
@endphp

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jewellery Invoice {{ $invoiceNo }}</title>

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
            @if($bCity), {{ $bCity }} @endif
            @if($bState), {{ $bState }} @endif
            <br>
            Mobile: {{ $bMobile ?: '-' }}
            @if($bEmail) | Email: {{ $bEmail }} @endif
            @if($bGstin) | GSTIN: {{ $bGstin }} @endif
        </div>

        <div class="invoice-title">Jewellery Tax Invoice</div>
    </div>

    <table class="meta-table" style="margin-top:10px;">
        <tr>
            <td style="width:50%;">
                <strong>Bill To:</strong><br>
                {{ strtoupper($cName) }}<br>
                {{ $cAddr }}<br>
                Mobile: {{ $cMobile ?: '-' }}<br>
                GSTIN: {{ $cGstin ?: '-' }}<br>
                PAN: {{ $cPan ?: '-' }}
            </td>

            <td style="width:25%;">
                <strong>Invoice No:</strong><br>
                {{ $invoiceNo }}<br><br>

                <strong>Payment Method:</strong><br>
                {{ $inv->payment_method ?? '-' }}
            </td>

            <td style="width:25%;">
                <strong>Invoice Date:</strong><br>
                {{ $dmy($invoiceDate) }}<br><br>

                <strong>Place of Supply:</strong><br>
                {{ $inv->place_of_supply_state ?? $c->state ?? '-' }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th style="width:22%;">Item Details</th>
            <th style="width:8%;">HSN</th>
            <th style="width:8%;">Purity</th>
            {{-- <th style="width:8%;">Gross Wt.</th> --}}
            <th style="width:8%;">Net Wt.</th>
            <th style="width:9%;">Gold Rate</th>
            <th style="width:9%;">Gold Value</th>
            <th style="width:8%;">Making</th>
            <th style="width:8%;">Gemstone</th>
            <th style="width:8%;">Diamond</th>
            <th style="width:8%;">Total</th>
        </tr>
        </thead>

        <tbody>
        @forelse($items as $index => $it)
            {{-- @php
                $name = $it->item->name ?? $it->name ?? $it->item_name ?? 'Jewellery Product';
                $desc = $it->description ?? '';
                $hsn = $it->hsn_code ?? $it->sac_code ?? $it->hsn ?? '-';

                $qty = (float)($it->quantity ?? 1);
                $unit = $it->unit ?? '';

                $grossWeight = $it->gross_weight ?? $it->gross_wt ?? null;
                $netWeight   = $it->net_weight ?? $it->net_wt ?? null;
                $lessWeight  = $it->less_weight ?? $it->less_wt ?? null;

                $purity = $it->purity ?? $it->karat ?? null;
                $huid   = $it->huid ?? $it->hallmark_uid ?? null;

                $goldRate   = $it->gold_rate ?? $it->metal_rate ?? $it->rate ?? 0;
                $goldAmount = $it->gold_amount ?? $it->metal_amount ?? null;

                $diamondAmount = $it->diamond_amount ?? 0;
                $stoneAmount   = $it->stone_amount ?? 0;
                $gemstonePrice = $it->gemstone_price ?? $it->gemstone_amount ?? 0;

                $makingCharge = $it->making_charge ?? $it->making_amount ?? 0;
                $makingPerGram = $it->making_per_gram ?? null;
                $wastage = $it->wastage ?? $it->wastage_percent ?? null;

                $taxPercent = (float)($it->tax_percent ?? $inv->tax_percent ?? 0);
                $taxAmount  = (float)($it->tax_amount ?? 0);

                $lineTotal = (float)($it->amount ?? $it->line_total ?? 0);

                if (!$goldAmount && $netWeight && $goldRate) {
                    $goldAmount = (float)$netWeight * (float)$goldRate;
                }

                if ($lineTotal <= 0) {
                    $lineTotal =
                        (float)($goldAmount ?? 0)
                        + (float)$diamondAmount
                        + (float)$stoneAmount
                        + (float)$gemstonePrice
                        + (float)$makingCharge
                        + (float)$taxAmount;
                }

                $stoneGemTotal = (float)$diamondAmount + (float)$stoneAmount + (float)$gemstonePrice;
            @endphp --}}


            @php
                $name = $it->item->name ?? $it->name ?? $it->item_name ?? 'Jewellery Product';
                $desc = $it->description ?? '';
                $hsn  = $it->hsn_code ?? $it->sac_code ?? $it->hsn ?? '-';

                $qty  = (float)($it->quantity ?? $it->qty ?? 1);
                $unit = $it->unit ?? '';

                $goldWeight   = (float)($it->gold_wt ?? $it->net_weight ?? $it->net_wt ?? 0);
                $silverWeight = (float)($it->silver_wt ?? 0);

                $grossWeight = $it->gross_weight ?? $it->gross_wt ?? $goldWeight;
                $netWeight   = $it->net_weight ?? $it->net_wt ?? $goldWeight;
                $lessWeight  = $it->less_weight ?? $it->less_wt ?? null;

                $purity = $it->purity ?? $it->karat ?? null;
                $huid   = $it->huid ?? $it->hallmark_uid ?? null;

                $goldRate   = (float)($it->gold_rate ?? 0);
                $silverRate = (float)($it->silver_rate ?? 0);

                $goldAmount   = (float)($it->gold_amount ?? 0);
                $silverAmount = (float)($it->silver_amount ?? 0);

                if ($goldAmount <= 0 && $goldWeight > 0 && $goldRate > 0) {
                    $goldAmount = $goldWeight * $goldRate;
                }

                if ($silverAmount <= 0 && $silverWeight > 0 && $silverRate > 0) {
                    $silverAmount = $silverWeight * $silverRate;
                }

                // $diamondAmount = (float)($it->diamond_amount ?? 0);
                // $stoneAmount   = (float)($it->stone_amount ?? 0);
                // $gemstonePrice = (float)($it->gemstone_price ?? $it->gemstone_amount ?? 0);



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

                $stoneGemTotal = $diamondAmount + $gemstonePrice;



                // making_charge database me percentage save hai
                $makingPercent = (float)($it->making_charge ?? $it->making_rate ?? $it->making_amount ?? 0);

                $metalAmount = $goldAmount + $silverAmount;

                // actual making amount
                $makingCharge = $metalAmount * ($makingPercent / 100);

                $makingPerGram = $it->making_per_gram ?? null;
                $wastage = $it->wastage ?? $it->wastage_percent ?? null;

                $taxPercent = (float)($it->tax_percent ?? $inv->tax_percent ?? 0);
                $taxAmount  = (float)($it->tax_amount ?? 0);

                $stoneGemTotal = $diamondAmount + $stoneAmount + $gemstonePrice;

                $lineTotal = (float)($it->amount ?? $it->line_total ?? 0);

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

                    @if($desc)
                        <div class="small-text">{{ $desc }}</div>
                    @endif

                    <div class="jewel-details">
                        @if($huid)
                            <strong>HUID:</strong> {{ $huid }}<br>
                        @endif

                        @if($qty)
                            <strong>Qty:</strong> {{ $qty }} {{ $unit }}<br>
                        @endif

                        @if($lessWeight)
                            <strong>Less Wt:</strong> {{ $fmt3($lessWeight) }} gm<br>
                        @endif

                        @if($wastage)
                            <strong>Wastage:</strong> {{ $wastage }}%
                        @endif
                    </div>
                </td>

                <td class="text-center">{{ $hsn }}</td>

                <td class="text-center">
                    {{ $purity ?: '-' }}
                </td>

                {{-- <td class="text-right">
                    {{ $grossWeight ? $fmt3($grossWeight) . ' gm' : '-' }}
                </td> --}}

                <td class="text-right">
                    {{ $netWeight ? $fmt3($netWeight) . ' gm' : '-' }}
                </td>

                <td class="text-right">
                    ₹ {{ $fmt2($goldRate) }}
                </td>

                <td class="text-right">
                    ₹ {{ $fmt2($goldAmount ?? 0) }}
                </td>

                <td class="text-right">
                    ₹ {{ $fmt2($makingCharge) }}

                    @if($makingPerGram)
                        <br>
                        <span class="small-text">₹{{ $fmt2($makingPerGram) }}/gm</span>
                    @endif
                </td>

                {{-- <td class="text-right">
                    ₹ {{ $fmt2($stoneGemTotal) }}

                    @if($diamondAmount)
                        <br><span class="small-text">Diamond: ₹{{ $fmt2($diamondAmount) }}</span>
                    @endif

                    @if($stoneAmount)
                        <br><span class="small-text">Stone: ₹{{ $fmt2($stoneAmount) }}</span>
                    @endif

                    @if($gemstonePrice)
                        <br><span class="small-text">Gem: ₹{{ $fmt2($gemstonePrice) }}</span>
                    @endif
                </td> --}}

                <td class="text-right">
                    @if($gemstonePrice > 0)
                        ₹ {{ $fmt2($gemstonePrice) }}
                    @else
                        -
                    @endif
                </td>

                <td class="text-right">
                    @if($diamondAmount > 0)
                        ₹ {{ $fmt2($diamondAmount) }}
                    @else
                        -
                    @endif
                </td>

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
                <td colspan="11" class="text-center">No jewellery item found</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td>Taxable Amount</td>
            <td class="text-right">₹ {{ $fmt2($taxable) }}</td>
        </tr>

        @if($discount > 0)
            <tr>
                <td>Discount</td>
                <td class="text-right">₹ {{ $fmt2($discount) }}</td>
            </tr>
        @endif

        @if($isIGST)
            <tr>
                <td>IGST</td>
                <td class="text-right">₹ {{ $fmt2($igstAmount) }}</td>
            </tr>
        @else
            <tr>
                <td>CGST</td>
                <td class="text-right">₹ {{ $fmt2($cgstAmount) }}</td>
            </tr>
            <tr>
                <td>SGST</td>
                <td class="text-right">₹ {{ $fmt2($sgstAmount) }}</td>
            </tr>
        @endif

        @if($roundOff != 0)
            <tr>
                <td>Round Off</td>
                <td class="text-right">₹ {{ $fmt2($roundOff) }}</td>
            </tr>
        @endif

        <tr>
            <td>Received Amount</td>
            <td class="text-right">₹ {{ $fmt2($received) }}</td>
        </tr>

        <tr>
            <td>Balance</td>
            <td class="text-right">₹ {{ $fmt2($balance) }}</td>
        </tr>

        <tr class="grand">
            <td>Total Amount</td>
            <td class="text-right">₹ {{ $fmt2($grandTotal) }}</td>
        </tr>
    </table>

    <div class="amount-words">
        <strong>Amount in Words:</strong>
        {{ $inv->amount_in_words ?: inr_words_jewellery($grandTotal) }}
    </div>

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