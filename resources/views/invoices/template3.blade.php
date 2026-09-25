@php
    /** @var \App\Models\Invoice $inv */

    $b = $biz ?? ($inv->business ?? null);
    $c = $client ?? ($inv->client ?? null);
    $items = $items ?? collect();

    if (!($items instanceof \Illuminate\Support\Collection)) {
        $items = collect($items);
    }

    $docType = strtolower((string)($type ?? ($inv->invoice_type ?? 'invoice')));

    $gstEnabled = (bool)($b->gst_enabled ?? false);
    $businessGstin = trim((string)($b->gstin ?? ''));

    if (!$gstEnabled || $businessGstin === '') {
        $docLabel = 'JEWELLERY INVOICE';
    } else {
        $docLabel = match ($docType) {
            'quotation' => 'QUOTATION',
            'proforma'  => 'PROFORMA INVOICE',
            default     => 'TAX INVOICE',
        };
    }

    $fmt2 = fn($v) => number_format((float)$v, 2, '.', ',');
    $fmt3 = fn($v) => number_format((float)$v, 3, '.', '');

    $dateFmt = function ($date) {
        if (!$date) return '';

        try {
            return \Carbon\Carbon::parse($date)->format('d-M-Y');
        } catch (\Throwable $e) {
            return (string)$date;
        }
    };

    $invoiceNo   = $inv->invoice_number ?? $inv->invoice_no ?? '-';
    $invoiceDate = $inv->invoice_date ?? $inv->date ?? $inv->created_at ?? null;

    $taxable    = (float)($subtotal ?? ($inv->subtotal ?? 0));
    $cgstAmount = (float)($cgst_amount ?? ($inv->cgst_amount ?? 0));
    $sgstAmount = (float)($sgst_amount ?? ($inv->sgst_amount ?? 0));
    $igstAmount = (float)($igst_amount ?? ($inv->igst_amount ?? 0));
    $discount   = (float)($inv->discount_total ?? $inv->discount ?? 0);
    $roundOff   = (float)($inv->round_off ?? 0);
    $grandTotal = (float)($grand_total ?? ($inv->total ?? $inv->grand_total ?? 0));

    $received = (float)($received ?? ($inv->received_amount ?? 0));
    $balance  = (float)($balance ?? ($inv->balance ?? max(0, $grandTotal - $received)));

    $bName   = $b->name ?? $b->business_name ?? 'Your Jewellery Store';
    $bAddr   = trim((string)($b->address ?? $b->business_address ?? ''));
    $bCity   = trim((string)($b->city ?? ''));
    $bState  = trim((string)($b->state ?? ''));
    $bCode   = trim((string)($b->state_code ?? $b->gst_state_code ?? ''));
    $bMobile = trim((string)($b->mobile ?? $b->phone ?? ''));
    $bEmail  = trim((string)($b->email ?? ''));
    $bGstin  = trim((string)($b->gstin ?? ($inv->gst_no ?? '')));

    $cName   = $c->name ?? 'CASH';
    $cAddr   = trim((string)($c->address ?? ''));
    $cCity   = trim((string)($c->city ?? ''));
    $cState  = trim((string)($c->state ?? ''));
    $cCode   = trim((string)($c->state_code ?? $c->gst_state_code ?? ''));
    $cMobile = trim((string)($c->mobile ?? $c->phone ?? ''));
    $cGstin  = trim((string)($c->gstin ?? $c->gst_number ?? ''));

    $paymentMethod = trim((string)($inv->payment_method ?? ''));

    $cgstPercent = $taxable > 0 ? (($cgstAmount / $taxable) * 100) : 0;
    $sgstPercent = $taxable > 0 ? (($sgstAmount / $taxable) * 100) : 0;
    $igstPercent = $taxable > 0 ? (($igstAmount / $taxable) * 100) : 0;

    if (!function_exists('simple_invoice_words')) {
        function simple_invoice_words($amount)
        {
            $amount = round((float)$amount, 2);
            $rupees = (int)floor($amount);
            $paise  = (int)round(($amount - $rupees) * 100);

            $ones = [
                '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven',
                'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen',
                'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'
            ];

            $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

            $two = function ($n) use ($ones, $tens) {
                $n = (int)$n;
                if ($n < 20) return $ones[$n] ?? '';
                return trim(($tens[(int)floor($n / 10)] ?? '') . ' ' . ($ones[$n % 10] ?? ''));
            };

            $three = function ($n) use ($ones, $two) {
                $n = (int)$n;
                $out = '';

                if ($n >= 100) {
                    $out .= ($ones[(int)floor($n / 100)] ?? '') . ' Hundred ';
                    $n %= 100;
                }

                if ($n > 0) {
                    $out .= $two($n);
                }

                return trim($out);
            };

            $parts = [];

            if ($rupees >= 10000000) {
                $v = (int)floor($rupees / 10000000);
                $parts[] = $three($v) . ' Crore';
                $rupees %= 10000000;
            }

            if ($rupees >= 100000) {
                $v = (int)floor($rupees / 100000);
                $parts[] = $three($v) . ' Lakh';
                $rupees %= 100000;
            }

            if ($rupees >= 1000) {
                $v = (int)floor($rupees / 1000);
                $parts[] = $three($v) . ' Thousand';
                $rupees %= 1000;
            }

            if ($rupees > 0) {
                $parts[] = $three($rupees);
            }

            $words = trim(implode(' ', array_filter($parts)));
            if ($words === '') $words = 'Zero';

            $result = $words . ' Rupees';

            if ($paise > 0) {
                $result .= ' and ' . $two($paise) . ' Paise';
            }

            return $result . ' Only';
        }
    }
@endphp

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $docLabel }} {{ $invoiceNo }}</title>

    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 12px;
            color: #000;
            background: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .top-space {
            height: 100px;
        }

        .title {
            border: 1px solid #000;
            text-align: center;
            font-size: 17px;
            font-weight: bold;
            padding: 9px 4px;
        }

        .meta-table td {
            border: 1px solid #000;
            vertical-align: top;
            padding: 7px;
            line-height: 15px;
        }

        .seller-buyer {
            width: 50%;
        }

        .meta-small {
            width: 25%;
        }

        .name {
            font-size: 11px;
            font-weight: bold;
        }

        .items-table {
            margin-top: 10px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 5px 4px;
            vertical-align: top;
            font-size: 9px;
        }

        .items-table th {
            text-align: center;
            font-weight: normal;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .small {
            font-size: 8px;
            line-height: 12px;
        }

        .words-box {
            border: 1px solid #000;
            border-top: 0;
            padding: 7px;
            font-size: 9px;
            line-height: 14px;
        }

        .footer-table td {
            border: 1px solid #000;
            border-top: 0;
            padding: 7px;
            vertical-align: top;
            font-size: 8px;
            line-height: 13px;
        }

        .sign {
            text-align: right;
        }

        .sign img {
            max-width: 100px;
            max-height: 35px;
        }

        .generated {
            margin-top: 8px;
            text-align: center;
            font-size: 7px;
        }
    </style>
</head>

<body>

<div class="top-space"></div>

<div class="title">
    {{ $docLabel }}
</div>

<table class="meta-table">
    <tr>
        <td class="seller-buyer">
            <div class="name">{{ strtoupper($bName) }}</div>

            @if($bAddr !== '')
                {{ strtoupper($bAddr) }}<br>
            @endif

            @if($bCity !== '')
                {{ strtoupper($bCity) }}<br>
            @endif

            @if($bGstin !== '')
                GSTIN/UIN: {{ strtoupper($bGstin) }}<br>
            @endif

            @if($bState !== '')
                State Name : {{ $bState }}
                @if($bCode !== '')
                    , Code : {{ $bCode }}
                @endif
                <br>
            @endif

            @if($bMobile !== '')
                Mobile : {{ $bMobile }}<br>
            @endif

            @if($bEmail !== '')
                E-Mail : {{ $bEmail }}
            @endif
        </td>

        <td class="meta-small">
            Invoice No.<br>
            <strong>{{ $invoiceNo }}</strong><br><br>

            Delivery Note<br><br>

            Supplier's Ref.
        </td>

        <td class="meta-small">
            Dated<br>
            <strong>{{ $dateFmt($invoiceDate) }}</strong><br><br>

            Mode/Terms of Payment<br>
            @if($paymentMethod !== '')
                <strong>{{ strtoupper($paymentMethod) }}</strong>
            @endif
            <br><br>

            Other Reference(s)
        </td>
    </tr>

    <tr>
        <td class="seller-buyer">
            Buyer<br>

            <div class="name">{{ strtoupper($cName) }}</div>

            @if($cAddr !== '')
                {{ strtoupper($cAddr) }}<br>
            @endif

            @if($cCity !== '')
                {{ strtoupper($cCity) }}<br>
            @endif

            @if($cMobile !== '')
                MOB---{{ $cMobile }}<br>
            @endif

            @if($cGstin !== '')
                GSTIN/UIN: {{ strtoupper($cGstin) }}<br>
            @endif

            @if($cState !== '')
                State Name : {{ $cState }}
                @if($cCode !== '')
                    , Code : {{ $cCode }}
                @endif
            @endif
        </td>

        <td class="meta-small">
            Buyer's Order No.<br><br>
            Despatch Document No.<br><br>
            Despatched through<br><br>
            Terms of Delivery
        </td>

        <td class="meta-small">
            Dated<br><br>
            Delivery Note Date<br><br>
            Destination
        </td>
    </tr>
</table>

<table class="items-table">
    <thead>
    <tr>
        <th style="width:6%;">Sl<br>No.</th>
        <th style="width:35%;">Description of Goods</th>
        <th style="width:14%;">HSN/SAC</th>
        <th style="width:13%;">Quantity</th>
        <th style="width:11%;">Rate</th>
        <th style="width:6%;">per</th>
        <th style="width:15%;">Amount<br><span class="small">Without GST</span></th>
    </tr>
    </thead>

    <tbody>
    @forelse($items as $index => $it)
        @php
            $name = $it->item->name ?? $it->name ?? $it->item_name ?? 'Jewellery Product';
            $desc = trim((string)($it->description ?? ''));
            $hsn  = $it->hsn_code ?? $it->sac_code ?? $it->hsn ?? '-';

            $purity = $it->purity ?? $it->karat ?? '';
            $huid   = $it->huid ?? $it->hallmark_uid ?? '';

            $goldWeight   = (float)($it->gold_wt ?? 0);
            $silverWeight = (float)($it->silver_wt ?? 0);
            $netWeight    = (float)($it->net_weight ?? $it->net_wt ?? 0);

            if ($netWeight <= 0) {
                $netWeight = $silverWeight > 0 ? $silverWeight : $goldWeight;
            }

            $qty = (float)($it->quantity ?? $it->qty ?? 1);
            $quantity = $netWeight > 0 ? $netWeight : ($qty > 0 ? $qty : 1);

            $goldRate   = (float)($it->gold_rate ?? 0);
            $silverRate = (float)($it->silver_rate ?? 0);
            $normalRate = (float)($it->rate ?? $it->unit_price ?? $it->price ?? 0);

            if ($silverWeight > 0 && $silverRate > 0) {
                $rate = $silverRate;
            } elseif ($goldRate > 0) {
                $rate = $goldRate;
            } else {
                $rate = $normalRate;
            }

            $unit = strtoupper(trim((string)($it->unit ?? '')));
            if ($unit === '') {
                $unit = $netWeight > 0 ? 'GMS' : 'GMS';
            }

            /*
             |------------------------------------------------------------------
             | Item Amount WITHOUT GST
             |------------------------------------------------------------------
             | Priority:
             | 1. If taxable/base amount is already stored, use it directly.
             | 2. Otherwise, if tax amount is stored, remove tax from gross amount.
             | 3. Otherwise, remove GST using item GST rate.
             | 4. If no stored amount exists, use quantity x rate as base amount.
             */

            $grossLineTotal = (float)($it->amount ?? $it->line_total ?? $it->total ?? 0);

            $itemTaxableAmount = (float)(
                $it->taxable_amount
                ?? $it->taxable_value
                ?? $it->base_amount
                ?? $it->amount_before_tax
                ?? $it->subtotal
                ?? 0
            );

            $itemTaxAmount = (float)(
                $it->tax_amount
                ?? $it->gst_amount
                ?? 0
            );

            if ($itemTaxAmount <= 0) {
                $itemTaxAmount =
                    (float)($it->cgst_amount ?? 0)
                    + (float)($it->sgst_amount ?? 0)
                    + (float)($it->igst_amount ?? 0);
            }

            $itemGstPercent = (float)(
                $it->tax_percent
                ?? $it->tax_rate
                ?? $it->gst_percent
                ?? $it->gst_rate
                ?? 0
            );

            // If item GST rate is not saved, use invoice GST percentage.
            if ($itemGstPercent <= 0) {
                $itemGstPercent = $igstPercent > 0
                    ? $igstPercent
                    : ($cgstPercent + $sgstPercent);
            }

            if ($itemTaxableAmount <= 0) {
                if ($grossLineTotal > 0 && $itemTaxAmount > 0 && $grossLineTotal >= $itemTaxAmount) {
                    // Example: 1030 inclusive - 30 GST = 1000 taxable amount.
                    $itemTaxableAmount = $grossLineTotal - $itemTaxAmount;
                } elseif ($grossLineTotal > 0 && $itemGstPercent > 0) {
                    // Gross amount is GST inclusive, so reverse-calculate base amount.
                    $itemTaxableAmount = $grossLineTotal / (1 + ($itemGstPercent / 100));
                } elseif ($grossLineTotal > 0) {
                    // No GST data available; treat stored amount as base amount.
                    $itemTaxableAmount = $grossLineTotal;
                } elseif ($quantity > 0 && $rate > 0) {
                    // Rate is treated as GST-exclusive base rate.
                    $itemTaxableAmount = $quantity * $rate;
                }
            }

            $lineTotal = $itemTaxableAmount;
        @endphp

        <tr>
            <td class="text-center">{{ $index + 1 }}</td>

            <td>
                <strong>{{ strtoupper($name) }}</strong>

                @if($desc !== '')
                    <div class="small">{{ $desc }}</div>
                @endif

                @if($purity !== '' || $huid !== '')
                    <div class="small">
                        @if($purity !== '')
                            Purity: {{ $purity }}
                        @endif

                        @if($huid !== '')
                            @if($purity !== '') | @endif
                            HUID: {{ $huid }}
                        @endif
                    </div>
                @endif
            </td>

            <td class="text-center">
                {{ $hsn ?: '-' }}
            </td>

            <td class="text-right">
                {{ $fmt3($quantity) }} {{ $unit }}
            </td>

            <td class="text-right">
                {{ $rate > 0 ? $fmt2($rate) : '-' }}
            </td>

            <td class="text-center">
                {{ $rate > 0 ? $unit : '' }}
            </td>

            <td class="text-right bold">
                {{ $fmt2($lineTotal) }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="text-center">
                No item found
            </td>
        </tr>
    @endforelse

    @if($taxable > 0)
        <tr>
            <td colspan="6" class="text-right bold">Taxable Amount</td>
            <td class="text-right bold">{{ $fmt2($taxable) }}</td>
        </tr>
    @endif

    @if($discount > 0)
        <tr>
            <td colspan="6" class="text-right bold">Discount</td>
            <td class="text-right bold">-{{ $fmt2($discount) }}</td>
        </tr>
    @endif

    @if($cgstAmount > 0)
        <tr>
            <td colspan="4" class="text-right bold">
                CGST {{ $fmt2($cgstPercent) }} %
            </td>
            <td class="text-right">{{ $fmt2($cgstPercent) }}</td>
            <td class="text-center">%</td>
            <td class="text-right bold">{{ $fmt2($cgstAmount) }}</td>
        </tr>
    @endif

    @if($sgstAmount > 0)
        <tr>
            <td colspan="4" class="text-right bold">
                SGST {{ $fmt2($sgstPercent) }} %
            </td>
            <td class="text-right">{{ $fmt2($sgstPercent) }}</td>
            <td class="text-center">%</td>
            <td class="text-right bold">{{ $fmt2($sgstAmount) }}</td>
        </tr>
    @endif

    @if($igstAmount > 0)
        <tr>
            <td colspan="4" class="text-right bold">
                IGST {{ $fmt2($igstPercent) }} %
            </td>
            <td class="text-right">{{ $fmt2($igstPercent) }}</td>
            <td class="text-center">%</td>
            <td class="text-right bold">{{ $fmt2($igstAmount) }}</td>
        </tr>
    @endif

    @if(abs($roundOff) > 0.0001)
        <tr>
            <td colspan="6" class="text-right bold">Round Off</td>
            <td class="text-right bold">{{ $fmt2($roundOff) }}</td>
        </tr>
    @endif

    <tr>
        <td colspan="6" class="text-right bold">Total</td>
        <td class="text-right bold">Rs. {{ $fmt2($grandTotal) }}</td>
    </tr>
    </tbody>
</table>

<div class="words-box">
    Amount Chargeable (in words)<br>
    <strong>
        INR {{ $inv->amount_in_words ?: simple_invoice_words($grandTotal) }}
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
                We declare that this invoice shows the actual price of the goods described
                and that all particulars are true and correct.
            @endif
        </td>

        <td class="sign">
            <strong>for {{ strtoupper($bName) }}</strong><br><br>

            @if(!empty($sign))
                <img src="{{ $sign }}" alt=""><br>
            @endif

            <br>
            <strong>Authorised Signatory</strong>
        </td>
    </tr>
</table>

<div class="generated">
    This is a Computer Generated Invoice
</div>

</body>
</html>
