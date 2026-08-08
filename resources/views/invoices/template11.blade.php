@php
    $termsText = $inv->terms ?? null;

    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? ($inv->business ?? null);
    $c = $client ?? ($inv->client ?? null);
    $items = $items ?? collect();


    $docType = strtolower((string)($type ?? 'invoice'));

    $gstEnabled = (bool) ($b->gst_enabled ?? false);
    $businessGstin = trim((string) ($b->gstin ?? ''));

    $isGstBusiness = $gstEnabled && $businessGstin !== '';

    if (!$isGstBusiness) {
        $docLabel = 'INVOICE';
    } else {
        $docLabel = match ($docType) {
            'quotation' => 'QUOTATION',
            'proforma'  => 'PROFORMA INVOICE',
            default     => 'TAX INVOICE',
        };
    }

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
        $invoiceSignature = $inv->signature ?? null;

    $invoiceSignatureUrl = null;

    if (!empty($invoiceSignature)) {
        $invoiceSignature = ltrim((string) $invoiceSignature, '/');

        if (\Illuminate\Support\Str::startsWith(
            $invoiceSignature,
            ['http://', 'https://', 'data:']
        )) {
            $invoiceSignatureUrl = $invoiceSignature;
        } else {
            $possibleSignaturePaths = [
                public_path('storage/' . $invoiceSignature),
                public_path($invoiceSignature),
                storage_path('app/public/' . $invoiceSignature),
            ];

            foreach ($possibleSignaturePaths as $signaturePath) {
                if (is_file($signaturePath)) {
                    $invoiceSignatureUrl = $signaturePath;
                    break;
                }
            }
        }
    }
@endphp


{{-- @include('invoices.partials.shared_logic') --}}

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $docLabel }} {{ $invoiceNo }}</title>
<style>
*{box-sizing:border-box}
body{font-family:"DejaVu Sans",sans-serif;font-size:12px;margin:0;padding:18px;color:#111;background:#eef2f7}
.page{background:#fff;border:1px solid #b6c2d1;padding:0}
.bankHead{background:#243b53;color:#fff;padding:16px 20px}
.bankName{font-size:24px;font-weight:700}
.bankInvoice{font-size:28px;font-weight:700;text-align:right}
table{width:100%;border-collapse:collapse}
.info td{border-bottom:1px solid #d8dee8;padding:10px 14px;vertical-align:top}
.label{font-size:10px;text-transform:uppercase;color:#64748b;font-weight:700}
.items th{background:#f1f5f9;border-bottom:2px solid #243b53;padding:9px;text-align:left}
.items td{border-bottom:1px solid #e5e7eb;padding:9px;vertical-align:top}
.text-right{text-align:right}
.desc{font-size:10px;color:#64748b}
.summary{width:45%;margin-left:auto;margin-top:14px}
.summary td{padding:8px;border-bottom:1px solid #cbd5e1}
.summary .grand td{background:#243b53;color:#fff;font-weight:700}
.footer{
    width:100%;
    padding:16px 20px;
    table-layout:fixed;
}
.footer td{
    vertical-align:bottom;
}
.amountWordsCell{
    width:62%;
    padding-right:18px;
    line-height:1.35;
}
.sign{
    width:38%;
    text-align:right;
    vertical-align:bottom;
}
.signatureBox{
    width:150px;
    height:58px;
    margin-left:auto;
    margin-bottom:5px;
    text-align:center;
    overflow:hidden;
}
.signatureBox img{
    width:150px;
    height:58px;
    display:block;
    margin:0 0 0 auto;
}
.signatoryTitle{
    font-size:10px;
    font-weight:700;
    line-height:1.2;
}
.signatoryBusiness{
    font-size:9px;
    line-height:1.2;
}
</style>
</head>
<body>
<div class="page">
    <table class="bankHead">
        <tr>
            <td>
                <div class="bankName">{{ $b->name ?? 'Real Victory Groups' }}</div>
                {{ $b_addr }}@if($b_city), {{ $b_city }}@endif @if($b_state), {{ $b_state }}@endif<br>
                Mobile: {{ $b_mobile ?: '-' }} | GSTIN: {{ $b_gstin ?: '-' }}
            </td>
            <td class="bankInvoice">
                {{ $docLabel }}<br>
                <span style="font-size:12px;">No: {{ $invoiceNo }}</span>
            </td>
        </tr>
    </table>

    <table class="info">
        <tr>
            <td style="width:40%">
                <div class="label">Account / Customer</div>
                <strong>{{ strtoupper($c->name ?? '-') }}</strong><br>
                {{ $c->address ?? '-' }}
            </td>
            <td>
                <div class="label">Date</div>
                {{ $dmy($invoiceDate) }}
            </td>
            <td>
                <div class="label">Mobile</div>
                {{ $mobile ?: '-' }}
            </td>
            <td>
                <div class="label">GSTIN</div>
                {{ $gstin ?: '-' }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
        <tr>
            <th>Particulars</th>
            <th>SAC</th>
            <th>Qty</th>
            <th class="text-right">Debit</th>
            <th class="text-right">Tax</th>
            <th class="text-right">Credit</th>
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

    <div style="padding:0 20px 20px">
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
    </div>

    <table class="footer">
        <tr>
            <td class="amountWordsCell">
                <strong>Amount in Words:</strong><br>
                {{ inr_words($finalTotal) }}
            </td>

            <td class="sign">
                @if(!empty($invoiceSignatureUrl))
                    <div class="signatureBox">
                        <img
                            src="{{ $invoiceSignatureUrl }}"
                            alt="Signature"
                            width="150"
                            height="58"
                        >
                    </div>
                @endif

                <div class="signatoryTitle">Authorised Signatory</div>
                <div class="signatoryBusiness">{{ $b->name ?? '' }}</div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>