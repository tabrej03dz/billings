@php
    $termsText = $inv->terms ?? null;

    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? ($inv->business ?? null);
    $c = $client ?? ($inv->client ?? null);
    $items = $items ?? collect();

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
            return trim($tens[(int)($n/10)] . ' ' . $ones[$n%10]);
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
        if ($words === '') $words = 'Zero';

        $result = $words . ' Rupees';
        if ($paise > 0) $result .= ' and ' . $twoDigits($paise) . ' Paise';
        return $result;
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
@endphp

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>{{ strtoupper($type) }} {{ $invoiceNo }}</title>
<style>
*{box-sizing:border-box}
body{font-family:"DejaVu Sans",sans-serif;font-size:12px;margin:0;padding:18px;background:#fff;color:#111}
.sheet{border:1px solid #999;padding:14px}
.topline{text-align:center;border-bottom:1px solid #999;padding-bottom:8px;margin-bottom:10px}
.company{font-size:24px;font-weight:700}
.docTitle{font-size:18px;font-weight:700;border:1px solid #111;display:inline-block;padding:5px 18px;margin-top:8px}
table{width:100%;border-collapse:collapse}
.meta td{border:1px solid #999;padding:8px;vertical-align:top}
.items th{border:1px solid #999;background:#f9fafb;padding:8px;text-align:left}
.items td{border:1px solid #999;padding:8px;vertical-align:top}
.text-right{text-align:right}
.desc{font-size:10px;color:#555}
.summary{width:48%;margin-left:auto;margin-top:10px}
.summary td{border:1px solid #999;padding:8px}
.summary .grand td{font-weight:700;background:#f3f4f6}
.receiptBox{margin-top:12px;border:1px solid #999;padding:10px}
.footer td{padding-top:22px;vertical-align:bottom}
.sign{text-align:right}
.sign img{max-height:45px}
</style>
</head>
<body>
<div class="sheet">
    <div class="topline">
        @if(!empty($logo))
            <img src="{{ $logo }}" style="max-width:85px;max-height:60px;"><br>
        @endif

        <div class="company">{{ $b->name ?? 'Real Victory Groups' }}</div>

        {{ $b_addr }}@if($b_city), {{ $b_city }}@endif @if($b_state), {{ $b_state }}@endif<br>
        Mobile: {{ $b_mobile ?: '-' }} | Email: {{ $b_email ?: '-' }} | GSTIN: {{ $b_gstin ?: '-' }}<br>

        <div class="docTitle">{{ strtoupper($type) }} {{ $type != 'quotation' ? 'INVOICE' : '' }}</div>
    </div>

    <table class="meta">
        <tr>
            <td style="width:50%">
                <strong>Customer Name & Address</strong><br>
                {{ strtoupper($c->name ?? '-') }}<br>
                {{ $c->address ?? '-' }}
            </td>
            <td style="width:25%">
                <strong>No.</strong><br>
                {{ $invoiceNo }}
            </td>
            <td style="width:25%">
                <strong>Date</strong><br>
                {{ $dmy($invoiceDate) }}
            </td>
        </tr>
        <tr>
            <td>
                Mobile: {{ $mobile ?: '-' }}<br>
                GSTIN: {{ $gstin ?: '-' }}
            </td>
            <td>PAN: {{ $pan ?: '-' }}</td>
            <td>POS: {{ $pos ?: '-' }}</td>
        </tr>
    </table>

    <table class="items" style="margin-top:10px;">
        <thead>
        <tr>
            <th>Particulars</th>
            <th>SAC</th>
            <th>Qty</th>
            <th class="text-right">Rate</th>
            <th class="text-right">Tax</th>
            <th class="text-right">Amount</th>
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
                $qty  = $qty > 0 ? $qty : 1;

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
                    @if($desc)<div class="desc">{{ $desc }}</div>@endif
                    @if($note)<div class="desc">{{ $note }}</div>@endif
                </td>
                <td>{{ $sac }}</td>
                <td>{{ $qty }} {{ $it->unit ?? '' }}</td>
                <td class="text-right">{{ $fmt2($showRate) }}</td>
                <td class="text-right">{{ $fmt2($lineTax) }}</td>
                <td class="text-right">₹ {{ $fmt2($lineTotal) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr><td>Taxable Amount</td><td class="text-right">₹ {{ $fmt2($taxable) }}</td></tr>

        @if($isIGST)
            <tr><td>IGST</td><td class="text-right">₹ {{ $fmt2($igst_db) }}</td></tr>
        @else
            <tr><td>CGST</td><td class="text-right">₹ {{ $fmt2($cgst_db) }}</td></tr>
            <tr><td>SGST</td><td class="text-right">₹ {{ $fmt2($sgst_db) }}</td></tr>
        @endif

        <tr><td>Received Amount</td><td class="text-right">₹ {{ $fmt2($receivedTot) }}</td></tr>
        <tr><td>Balance</td><td class="text-right">₹ {{ $fmt2($balanceNow) }}</td></tr>
        <tr class="grand"><td>Total Amount</td><td class="text-right">₹ {{ $fmt2($finalTotal) }}</td></tr>
    </table>

    <div class="receiptBox">
        <strong>Received with thanks from:</strong> {{ strtoupper($c->name ?? '-') }}<br>
        <strong>Amount:</strong> ₹ {{ $fmt2($receivedTot) }}<br>
        <strong>Balance:</strong> ₹ {{ $fmt2($balanceNow) }}<br>
        <strong>Amount in Words:</strong> {{ inr_words($finalTotal) }}
    </div>

    @if(!empty($inv->terms))
        <div class="receiptBox">
            <strong>Terms & Conditions</strong><br>
            {!! nl2br(e($inv->terms)) !!}
        </div>
    @endif

    <table class="footer">
        <tr>
            <td>Customer Signature</td>
            <td class="sign">
                @if(!empty($sign))
                    <img src="{{ $sign }}"><br>
                @endif
                <strong>Authorised Signatory</strong><br>
                {{ $b->name ?? '' }}
            </td>
        </tr>
    </table>
</div>
</body>
</html>