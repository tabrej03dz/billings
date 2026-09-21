@php
    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? ($inv->business ?? null);
    $c = $client ?? ($inv->client ?? null);
    $items = collect($items ?? []);

    $docType = strtolower((string) ($type ?? ($inv->invoice_type ?? 'invoice')));
    $gstEnabled = (bool) ($b->gst_enabled ?? false);
    $businessGstin = trim((string) ($b->gstin ?? ($inv->gst_no ?? '')));
    $isGstBusiness = $gstEnabled && $businessGstin !== '';

    $docLabel = !$isGstBusiness
        ? 'Jewellery Invoice'
        : match ($docType) {
            'quotation' => 'Quotation',
            'proforma'  => 'Proforma Invoice',
            default     => 'Tax Invoice',
        };

    $itemExtraPrices = $itemExtraPrices ?? [];
    $pay = $paymentDetails ?? [];

    $fmt2 = static fn ($value) => number_format((float) $value, 2, '.', ',');
    $fmt3 = static fn ($value) => number_format((float) $value, 3, '.', ',');
    $dateText = static function ($date): string {
        if (empty($date)) {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($date)->format('d-M-Y');
        } catch (\Throwable $e) {
            return (string) $date;
        }
    };

    $invoiceNo = $inv->invoice_number ?? $inv->invoice_no ?? '-';
    $invoiceDate = $inv->invoice_date ?? $inv->date ?? null;

    $taxable = (float) ($subtotal ?? ($inv->subtotal ?? 0));
    $discount = (float) ($inv->discount_total ?? $inv->discount ?? 0);
    $cgstAmount = (float) ($cgst_amount ?? ($inv->cgst_amount ?? 0));
    $sgstAmount = (float) ($sgst_amount ?? ($inv->sgst_amount ?? 0));
    $igstAmount = (float) ($igst_amount ?? ($inv->igst_amount ?? 0));
    $roundOff = (float) ($inv->round_off ?? 0);
    $grandTotal = (float) ($grand_total ?? ($inv->total ?? 0));
    $received = (float) ($received ?? ($inv->received_amount ?? 0));
    $balance = (float) ($balance ?? ($inv->balance ?? max(0, $grandTotal - $received)));

    $taxPercent = (float) ($inv->tax_percent ?? $inv->gst_percent ?? 0);
    $cgstPercent = (float) ($inv->cgst_percent ?? $inv->cgst_rate ?? ($taxPercent > 0 ? $taxPercent / 2 : 0));
    $sgstPercent = (float) ($inv->sgst_percent ?? $inv->sgst_rate ?? ($taxPercent > 0 ? $taxPercent / 2 : 0));
    $igstPercent = (float) ($inv->igst_percent ?? $inv->igst_rate ?? $taxPercent);

    if ($cgstPercent <= 0 && $taxable > 0 && $cgstAmount > 0) {
        $cgstPercent = ($cgstAmount / $taxable) * 100;
    }
    if ($sgstPercent <= 0 && $taxable > 0 && $sgstAmount > 0) {
        $sgstPercent = ($sgstAmount / $taxable) * 100;
    }
    if ($igstPercent <= 0 && $taxable > 0 && $igstAmount > 0) {
        $igstPercent = ($igstAmount / $taxable) * 100;
    }

    $bName = trim((string) ($b->name ?? $b->business_name ?? 'Your Jewellery Store'));
    $bAddr = trim((string) ($b->address ?? $b->business_address ?? ''));
    $bCity = trim((string) ($b->city ?? ''));
    $bState = trim((string) ($b->state ?? ''));
    $bStateCode = trim((string) ($b->state_code ?? $b->gst_state_code ?? ''));
    $bMobile = trim((string) ($b->mobile ?? $b->phone ?? ''));
    $bEmail = trim((string) ($b->email ?? ''));
    $bGstin = trim((string) ($b->gstin ?? ($inv->gst_no ?? '')));

    $businessAddress = collect([$bAddr, $bCity])->filter()->implode(', ');

    $cName = trim((string) ($c->name ?? 'Cash Customer'));
    $cAddr = trim((string) ($c->address ?? ''));
    $cCity = trim((string) ($c->city ?? ''));
    $cState = trim((string) ($c->state ?? ($inv->place_of_supply_state ?? '')));
    $cStateCode = trim((string) ($c->state_code ?? $c->gst_state_code ?? ''));
    $cMobile = trim((string) ($c->mobile ?? $c->phone ?? ''));
    $cGstin = trim((string) ($c->gstin ?? $c->gst_number ?? ''));
    $cPan = trim((string) ($c->pan ?? $c->pan_number ?? ''));
    $clientAddress = collect([$cAddr, $cCity])->filter()->implode(', ');

    $paymentMethod = trim((string) ($pay['method'] ?? ($inv->payment_method ?? '')));
    $buyerLedger = trim((string) ($inv->buyer_ledger_name ?? $inv->party_ledger ?? ''));
    if ($buyerLedger === '' && strtolower($paymentMethod) === 'cash') {
        $buyerLedger = 'CASH';
    }

    $deliveryNote = trim((string) ($inv->delivery_note ?? ''));
    $supplierReference = trim((string) ($inv->supplier_reference ?? $inv->supplier_ref ?? ''));
    $otherReference = trim((string) ($inv->other_reference ?? $inv->other_references ?? ''));
    $buyerOrderNo = trim((string) ($inv->buyer_order_no ?? $inv->order_no ?? ''));
    $buyerOrderDate = $inv->buyer_order_date ?? $inv->order_date ?? null;
    $dispatchDocumentNo = trim((string) ($inv->dispatch_document_no ?? $inv->dispatch_doc_no ?? ''));
    $deliveryNoteDate = $inv->delivery_note_date ?? null;
    $dispatchedThrough = trim((string) ($inv->dispatched_through ?? $inv->dispatch_through ?? ''));
    $destination = trim((string) ($inv->destination ?? ''));
    $termsOfDelivery = trim((string) ($inv->terms_of_delivery ?? ''));

    $bank = $selectedBank ?? null;
    $bankName = trim((string) ($bank->bank_name ?? ($pay['bank_name_entered'] ?? ($payRow->bank_name ?? ''))));
    $bankAccountHolder = trim((string) ($bank->account_holder ?? ''));
    $bankAccountNumber = trim((string) ($bank->account_no ?? ''));
    $bankIfsc = trim((string) ($bank->ifsc ?? ''));
    $bankUpi = trim((string) ($bank->upi_id ?? ($pay['upi_id'] ?? ($payRow->upi_id ?? ''))));
    $hasBankDetails = $bankName !== '' || $bankAccountHolder !== '' || $bankAccountNumber !== '' || $bankIfsc !== '' || $bankUpi !== '';

    if (!function_exists('invoice_amount_in_words_tally')) {
        function invoice_amount_in_words_tally($amount): string
        {
            $amount = round((float) $amount, 2);
            $rupees = (int) floor($amount);
            $paise = (int) round(($amount - $rupees) * 100);

            if (class_exists(\NumberFormatter::class)) {
                try {
                    $formatter = new \NumberFormatter('en_IN', \NumberFormatter::SPELLOUT);
                    $words = ucwords(str_replace('-', ' ', $formatter->format($rupees)));
                    $result = $words . ' Rupees';

                    if ($paise > 0) {
                        $result .= ' And ' . ucwords(str_replace('-', ' ', $formatter->format($paise))) . ' Paise';
                    }

                    return $result . ' Only';
                } catch (\Throwable $e) {
                    // The manual Indian-number fallback below will be used.
                }
            }

            $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
            $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

            $underHundred = static function (int $number) use ($ones, $tens): string {
                if ($number < 20) {
                    return $ones[$number];
                }

                return trim($tens[intdiv($number, 10)] . ' ' . $ones[$number % 10]);
            };

            $parts = [];
            foreach ([10000000 => 'Crore', 100000 => 'Lakh', 1000 => 'Thousand', 100 => 'Hundred'] as $value => $label) {
                if ($rupees >= $value) {
                    $chunk = intdiv($rupees, $value);
                    $parts[] = ($chunk < 100 ? $underHundred($chunk) : (string) $chunk) . ' ' . $label;
                    $rupees %= $value;
                }
            }
            if ($rupees > 0) {
                $parts[] = $underHundred($rupees);
            }

            $result = (implode(' ', $parts) ?: 'Zero') . ' Rupees';
            if ($paise > 0) {
                $result .= ' And ' . $underHundred($paise) . ' Paise';
            }

            return $result . ' Only';
        }
    }

    $itemRows = [];
    foreach ($items as $index => $it) {
        $name = trim((string) ($it->item->name ?? $it->name ?? $it->item_name ?? 'Jewellery Product'));
        $description = trim((string) ($it->description ?? ''));
        $hsn = trim((string) ($it->hsn_code ?? $it->sac_code ?? $it->hsn ?? ''));
        $purity = trim((string) ($it->purity ?? $it->karat ?? ''));
        $huid = trim((string) ($it->huid ?? $it->hallmark_uid ?? ''));

        $qty = (float) ($it->quantity ?? $it->qty ?? 1);
        $unit = trim((string) ($it->unit ?? 'PCS'));
        $goldWeight = (float) ($it->gold_wt ?? $it->net_weight ?? $it->net_wt ?? 0);
        $silverWeight = (float) ($it->silver_wt ?? 0);
        $netWeight = (float) ($it->net_weight ?? $it->net_wt ?? $goldWeight);

        $goldRate = (float) ($it->gold_rate ?? 0);
        $silverRate = (float) ($it->silver_rate ?? 0);
        $normalRate = (float) ($it->rate ?? $it->unit_price ?? $it->price ?? 0);

        $goldAmount = (float) ($it->gold_amount ?? 0);
        $silverAmount = (float) ($it->silver_amount ?? 0);

        if ($goldAmount <= 0 && $goldWeight > 0 && $goldRate > 0) {
            $goldAmount = $goldWeight * $goldRate;
        }
        if ($silverWeight > 0 && $silverRate > 0) {
            $silverAmount = $silverWeight * $silverRate;
        }

        $extraPrice = $itemExtraPrices[(int) ($it->item_id ?? 0)] ?? [];
        $diamondAmount = (float) ($it->diamond_charges ?? $it->diamond_price ?? $extraPrice['diamond_price'] ?? 0);
        $gemstoneAmount = (float) ($it->stone_charges ?? $it->gemstone_amount ?? $it->stone_amount ?? $it->stone_price ?? $extraPrice['gemstone_price'] ?? 0);
        $makingCharge = (float) ($it->making_charge ?? $it->making_amount ?? 0);
        $taxAmount = (float) ($it->tax_amount ?? 0);

        $lineTotal = (float) ($it->amount ?? $it->line_total ?? $it->total ?? 0);
        if ($lineTotal <= 0) {
            $lineTotal = $goldAmount + $silverAmount + $diamondAmount + $gemstoneAmount + $makingCharge + $taxAmount;
        }
        if ($lineTotal <= 0 && $normalRate > 0) {
            $lineTotal = $qty * $normalRate;
        }

        if ($silverWeight > 0) {
            $displayQty = $fmt3($silverWeight);
            $displayRate = $silverRate;
            $displayUnit = 'GMS';
        } elseif ($netWeight > 0) {
            $displayQty = $fmt3($netWeight);
            $displayRate = $goldRate;
            $displayUnit = 'GMS';
        } else {
            $displayQty = ($qty == (int) $qty) ? (string) (int) $qty : $fmt3($qty);
            $displayRate = $normalRate;
            $displayUnit = strtoupper($unit ?: 'PCS');
        }

        $details = [];
        if ($description !== '') $details[] = $description;
        if ($purity !== '') $details[] = 'Purity: ' . $purity;
        if ($huid !== '') $details[] = 'HUID: ' . $huid;
        if ($makingCharge > 0) $details[] = 'Making: Rs. ' . $fmt2($makingCharge);
        if ($gemstoneAmount > 0) $details[] = 'Gemstone: Rs. ' . $fmt2($gemstoneAmount);
        if ($diamondAmount > 0) $details[] = 'Diamond: Rs. ' . $fmt2($diamondAmount);

        $itemRows[] = [
            'serial' => $index + 1,
            'name' => $name,
            'details' => implode(' | ', $details),
            'hsn' => $hsn ?: '-',
            'quantity' => $displayQty,
            'rate' => $displayRate,
            'unit' => $displayUnit,
            'amount' => $lineTotal,
        ];
    }

    $itemCount = count($itemRows);
    $compactClass = $itemCount > 9 ? 'many-items' : '';
    $fillerHeight = max(5, 45 - ($itemCount * 5.8));
    $amountWords = trim((string) ($inv->amount_in_words ?? '')) ?: invoice_amount_in_words_tally($grandTotal);
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $docLabel }} {{ $invoiceNo }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 4mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            color: #000;
            background: #fff;
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 9px;
            line-height: 1.25;
        }

        .sheet {
            width: 100%;
            height: 288mm;
            border: 0.35mm solid #222;
            padding: 3mm;
            overflow: hidden;
            page-break-inside: avoid;
        }

        /* Pre-printed letterhead ke liye khaali jagah. */
        .letterhead-space {
            height: 37mm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
        }

        td,
        th {
            border: 0.25mm solid #333;
            vertical-align: top;
        }

        .invoice-title {
            border: 0.25mm solid #333;
            border-bottom: 0;
            text-align: center;
            font-weight: 700;
            font-size: 14px;
            padding: 3.1mm 1mm;
        }

        .party-meta td {
            padding: 1.7mm 2mm;
        }

        .party-cell {
            width: 51%;
        }

        .meta-cell {
            width: 24.5%;
            height: 10.5mm;
        }

        .seller-cell {
            height: 31.5mm;
        }

        .buyer-cell {
            height: 42mm;
        }

        .business-name,
        .buyer-name,
        .value-strong {
            font-weight: 700;
        }

        .business-name,
        .buyer-name {
            font-size: 10px;
            text-transform: uppercase;
        }

        .label {
            display: block;
            font-size: 8.6px;
            font-weight: 400;
            margin-bottom: 0.5mm;
        }

        .value-strong {
            font-size: 9.6px;
        }

        .items-table {
            margin-top: 3mm;
        }

        .items-table th {
            height: 13mm;
            padding: 2mm 1mm;
            text-align: center;
            vertical-align: middle;
            font-weight: 400;
            font-size: 9px;
        }

        .items-table td {
            padding: 1.6mm 1.5mm;
            vertical-align: middle;
            font-size: 8.8px;
        }

        .items-table tbody tr.item-row {
            page-break-inside: avoid;
        }

        .item-name {
            font-weight: 700;
            text-transform: uppercase;
        }

        .item-detail {
            margin-top: 0.6mm;
            font-size: 7px;
            line-height: 1.15;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .nowrap { white-space: nowrap; }
        .bold { font-weight: 700; }
        .italic { font-style: italic; }

        .filler-row td {
            height: {{ $fillerHeight }}mm;
            vertical-align: bottom;
        }

        .total-row td {
            padding: 1.7mm 1.5mm;
        }

        .tax-label {
            text-align: right;
            font-weight: 700;
            font-style: italic;
            font-size: 9.3px;
        }

        .grand-row td {
            font-size: 10px;
            font-weight: 700;
            padding-top: 2mm;
            padding-bottom: 2mm;
        }

        .bottom-table {
            margin-top: 0;
        }

        .bottom-table td {
            padding: 1.8mm 2mm;
            font-size: 8px;
        }

        .words-cell {
            height: 11mm;
        }

        .terms-cell {
            height: 21mm;
        }

        .signature-cell {
            height: 18mm;
            text-align: right;
            vertical-align: bottom;
        }

        .signature-image {
            max-width: 38mm;
            max-height: 12mm;
            margin-bottom: 1mm;
        }

        .declaration {
            font-size: 7px;
            margin-top: 1mm;
        }

        .many-items .items-table th {
            height: 10mm;
            padding: 1mm 0.6mm;
            font-size: 7.6px;
        }

        .many-items .items-table td {
            padding: 0.8mm 0.8mm;
            font-size: 7.2px;
        }

        .many-items .item-detail {
            font-size: 6px;
        }
    </style>
</head>

<body>
<div class="sheet {{ $compactClass }}">
    <div class="letterhead-space"></div>

    <div class="invoice-title">{{ $docLabel }}</div>

    <table class="party-meta">
        <tr>
            <td class="party-cell seller-cell" rowspan="3">
                <div class="business-name">{{ $bName }}</div>
                @if($businessAddress !== '')
                    <div>{{ $businessAddress }}</div>
                @endif
                @if($bGstin !== '')
                    <div>GSTIN/UIN: {{ $bGstin }}</div>
                @endif
                @if($bState !== '')
                    <div>State Name: {{ $bState }}@if($bStateCode !== ''), Code: {{ $bStateCode }}@endif</div>
                @endif
                @if($bMobile !== '')
                    <div>Mobile: {{ $bMobile }}</div>
                @endif
                @if($bEmail !== '')
                    <div>E-Mail: {{ $bEmail }}</div>
                @endif
            </td>

            <td class="meta-cell">
                <span class="label">Invoice No.</span>
                <span class="value-strong">{{ $invoiceNo }}</span>
            </td>
            <td class="meta-cell">
                <span class="label">Dated</span>
                <span class="value-strong">{{ $dateText($invoiceDate) }}</span>
            </td>
        </tr>
        <tr>
            <td class="meta-cell">
                <span class="label">Delivery Note</span>
                {{ $deliveryNote }}
            </td>
            <td class="meta-cell">
                <span class="label">Mode/Terms of Payment</span>
                {{ strtoupper($paymentMethod) }}
            </td>
        </tr>
        <tr>
            <td class="meta-cell">
                <span class="label">Supplier's Ref.</span>
                {{ $supplierReference }}
            </td>
            <td class="meta-cell">
                <span class="label">Other Reference(s)</span>
                {{ $otherReference }}
            </td>
        </tr>

        <tr>
            <td class="party-cell buyer-cell" rowspan="4">
                <span class="label">Buyer</span>
                @if($buyerLedger !== '')
                    <div class="buyer-name">{{ $buyerLedger }}</div>
                @endif
                <div class="buyer-name">{{ $cName }}</div>
                @if($clientAddress !== '')
                    <div>{{ $clientAddress }}</div>
                @endif
                @if($cMobile !== '')
                    <div>MOB: {{ $cMobile }}</div>
                @endif
                @if($cGstin !== '')
                    <div>GSTIN/UIN: {{ $cGstin }}</div>
                @endif
                @if($cPan !== '')
                    <div>PAN: {{ $cPan }}</div>
                @endif
                @if($cState !== '')
                    <div>State Name: {{ $cState }}@if($cStateCode !== ''), Code: {{ $cStateCode }}@endif</div>
                @endif
            </td>
            <td class="meta-cell">
                <span class="label">Buyer's Order No.</span>
                {{ $buyerOrderNo }}
            </td>
            <td class="meta-cell">
                <span class="label">Dated</span>
                {{ $dateText($buyerOrderDate) }}
            </td>
        </tr>
        <tr>
            <td class="meta-cell">
                <span class="label">Despatch Document No.</span>
                {{ $dispatchDocumentNo }}
            </td>
            <td class="meta-cell">
                <span class="label">Delivery Note Date</span>
                {{ $dateText($deliveryNoteDate) }}
            </td>
        </tr>
        <tr>
            <td class="meta-cell">
                <span class="label">Despatched through</span>
                {{ $dispatchedThrough }}
            </td>
            <td class="meta-cell">
                <span class="label">Destination</span>
                {{ $destination }}
            </td>
        </tr>
        <tr>
            <td class="meta-cell" colspan="2">
                <span class="label">Terms of Delivery</span>
                {{ $termsOfDelivery }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <colgroup>
            <col style="width:6%">
            <col style="width:35%">
            <col style="width:14%">
            <col style="width:13%">
            <col style="width:11%">
            <col style="width:6%">
            <col style="width:15%">
        </colgroup>
        <thead>
        <tr>
            <th>Sl<br>No.</th>
            <th>Description of Goods</th>
            <th>HSN/SAC</th>
            <th>Quantity</th>
            <th>Rate</th>
            <th>per</th>
            <th>Amount</th>
        </tr>
        </thead>
        <tbody>
        @forelse($itemRows as $row)
            <tr class="item-row">
                <td class="text-center">{{ $row['serial'] }}</td>
                <td>
                    <div class="item-name">{{ $row['name'] }}</div>
                    @if($row['details'] !== '')
                        <div class="item-detail">{{ $row['details'] }}</div>
                    @endif
                </td>
                <td class="text-center">{{ $row['hsn'] }}</td>
                <td class="text-right nowrap">{{ $row['quantity'] }} {{ $row['unit'] }}</td>
                <td class="text-right">{{ $row['rate'] > 0 ? $fmt2($row['rate']) : '-' }}</td>
                <td class="text-center">{{ $row['unit'] }}</td>
                <td class="text-right bold">{{ $fmt2($row['amount']) }}</td>
            </tr>
        @empty
            <tr class="item-row">
                <td class="text-center">1</td>
                <td class="item-name">No item found</td>
                <td class="text-center">-</td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-center">-</td>
                <td class="text-right">0.00</td>
            </tr>
        @endforelse

        <tr class="filler-row">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        @if($taxable > 0)
            <tr class="total-row">
                <td></td>
                <td colspan="5" class="text-right">Taxable Value</td>
                <td class="text-right">{{ $fmt2($taxable) }}</td>
            </tr>
        @endif

        @if($discount > 0)
            <tr class="total-row">
                <td></td>
                <td colspan="5" class="tax-label">Less: Discount</td>
                <td class="text-right bold">- {{ $fmt2($discount) }}</td>
            </tr>
        @endif

        @if($igstAmount > 0)
            <tr class="total-row">
                <td></td>
                <td colspan="3" class="tax-label">IGST {{ $fmt2($igstPercent) }}%</td>
                <td class="text-right">{{ $fmt2($igstPercent) }}</td>
                <td class="text-center">%</td>
                <td class="text-right bold">{{ $fmt2($igstAmount) }}</td>
            </tr>
        @else
            @if($sgstAmount > 0)
                <tr class="total-row">
                    <td></td>
                    <td colspan="3" class="tax-label">SGST {{ $fmt2($sgstPercent) }}%</td>
                    <td class="text-right">{{ $fmt2($sgstPercent) }}</td>
                    <td class="text-center">%</td>
                    <td class="text-right bold">{{ $fmt2($sgstAmount) }}</td>
                </tr>
            @endif
            @if($cgstAmount > 0)
                <tr class="total-row">
                    <td></td>
                    <td colspan="3" class="tax-label">CGST {{ $fmt2($cgstPercent) }}%</td>
                    <td class="text-right">{{ $fmt2($cgstPercent) }}</td>
                    <td class="text-center">%</td>
                    <td class="text-right bold">{{ $fmt2($cgstAmount) }}</td>
                </tr>
            @endif
        @endif

        @if($roundOff != 0)
            <tr class="total-row">
                <td></td>
                <td colspan="5" class="tax-label">Round Off</td>
                <td class="text-right bold">{{ $roundOff > 0 ? '+' : '' }}{{ $fmt2($roundOff) }}</td>
            </tr>
        @endif

        <tr class="grand-row">
            <td></td>
            <td colspan="3" class="text-right">Total</td>
            <td colspan="2"></td>
            <td class="text-right">Rs. {{ $fmt2($grandTotal) }}</td>
        </tr>
        </tbody>
    </table>

    <table class="bottom-table">
        <tr>
            <td class="words-cell" colspan="2">
                <span class="label">Amount Chargeable (in words)</span>
                <strong>INR {{ $amountWords }}</strong>
            </td>
        </tr>
        <tr>
            <td style="width:56%;" class="terms-cell">
                @if(!empty($inv->notes))
                    <strong>Notes:</strong> {!! nl2br(e($inv->notes)) !!}<br>
                @endif
                @if(!empty($inv->terms))
                    <strong>Terms &amp; Conditions:</strong><br>
                    {!! nl2br(e($inv->terms)) !!}
                @else
                    <strong>Declaration</strong>
                    <div class="declaration">We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.</div>
                @endif

                @if($received > 0 || $balance > 0)
                    <div style="margin-top:1.2mm;">
                        @if($received > 0)<strong>Received:</strong> Rs. {{ $fmt2($received) }}&nbsp;&nbsp;@endif
                        @if($balance > 0)<strong>Balance:</strong> Rs. {{ $fmt2($balance) }}@endif
                    </div>
                @endif
            </td>
            <td style="width:44%;" class="terms-cell">
                @if($hasBankDetails)
                    <strong>Bank Details</strong><br>
                    @if($bankName !== '')Bank: {{ $bankName }}<br>@endif
                    @if($bankAccountHolder !== '')A/c Holder: {{ $bankAccountHolder }}<br>@endif
                    @if($bankAccountNumber !== '')A/c No.: {{ $bankAccountNumber }}<br>@endif
                    @if($bankIfsc !== '')IFSC: {{ $bankIfsc }}<br>@endif
                    @if($bankUpi !== '')UPI: {{ $bankUpi }}@endif
                @endif
            </td>
        </tr>
        <tr>
            <td style="width:50%; height:18mm; vertical-align:bottom;">
                Customer's Seal and Signature
            </td>
            <td style="width:50%;" class="signature-cell">
                <strong>for {{ $bName }}</strong><br>
                @if(!empty($sign))
                    <img class="signature-image" src="{{ $sign }}" alt="Signature"><br>
                @else
                    <br><br>
                @endif
                Authorised Signatory
            </td>
        </tr>
    </table>
</div>
</body>
</html>