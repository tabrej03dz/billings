@php
    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? $business ?? $inv->business;
    $c = $client ?? $inv->client;
    $rows = collect($items ?? []);
    $fmt2 = fn($n) => number_format((float) $n, 2, '.', '');
    $fmt3 = fn($n) => number_format((float) $n, 3, '.', '');
    $pct  = fn($p) => rtrim(rtrim(number_format((float) $p, 2, '.', ''), '0'), '.');
    $amount = (float) ($inv->total ?? $inv->grand_total ?? 0);
    $paid = (float) ($inv->received_amount ?? 0);
    $due = (float) ($inv->balance ?? max(0, $amount - $paid));

    $invoiceDate = $inv->invoice_date ?? $inv->created_at;
    $billDate = $invoiceDate ? \Carbon\Carbon::parse($invoiceDate)->format('d/m/Y') : '';

    // ---- Customer name / address / mobile ----
    $custName = trim((string) ($c->name ?? $c->client_name ?? $inv->client_name ?? ''));
    if ($custName === '') $custName = 'CASH CUSTOMER';

    $addrParts = array_filter([
        trim((string) ($c->address ?? $inv->client_address ?? '')),
        trim((string) ($c->city ?? '')),
        trim((string) ($c->state ?? '')),
    ], fn($v) => $v !== '');
    $custAddress = implode(', ', $addrParts);
    $pin = trim((string) ($c->pincode ?? ''));
    if ($pin !== '') $custAddress .= ($custAddress !== '' ? ' - ' : '') . $pin;

    $custMobile = trim((string) ($c->mobile ?? $c->phone ?? $c->contact ?? ''));

    $up = fn($s) => mb_strtoupper((string) $s, 'UTF-8');

    // ---- Amount in words ----
    $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
             'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    $two  = fn($n) => $n < 20
        ? $ones[$n]
        : $tens[intdiv($n, 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '');

    $words = trim((string) ($inv->amount_in_words ?? ''));
    if ($words === '') {
        $whole = (int) floor(abs($amount));
        $paise = (int) round((abs($amount) - $whole) * 100);
        $parts = [];
        if ($whole === 0) {
            $parts[] = 'Zero';
        } else {
            $crore = intdiv($whole, 10000000);
            $lakh  = intdiv($whole % 10000000, 100000);
            $thou  = intdiv($whole % 100000, 1000);
            $hund  = intdiv($whole % 1000, 100);
            $rest  = $whole % 100;
            if ($crore) $parts[] = $two($crore) . ' Crore';
            if ($lakh)  $parts[] = $two($lakh) . ' Lakh';
            if ($thou)  $parts[] = $two($thou) . ' Thousand';
            if ($hund)  $parts[] = $ones[$hund] . ' Hundred';
            if ($rest)  $parts[] = $two($rest);
        }
        if ($paise) $parts[] = 'And ' . $two($paise) . ' Paise';
        $parts[] = 'Only';
        $words = implode(',', $parts);
    }

    // ---- Item calculator (DB columns se) ----
    // invoice_items: metal_type, purity, metal_weight, gold_wt, silver_wt, making_*, quantity, amount
    $calc = function ($it) {
        $gold   = (float) ($it->gold_wt ?? 0);
        $silver = (float) ($it->silver_wt ?? 0);
        $mw     = (float) ($it->metal_weight ?? 0);
        $mtype  = strtolower(trim((string) ($it->metal_type ?? '')));

        // metal decide
        if ($gold > 0)        $metal = 'G';
        elseif ($silver > 0)  $metal = 'S';
        elseif ($mtype === 'gold')   $metal = 'G';
        elseif ($mtype === 'silver') $metal = 'S';
        else $metal = '';

        // gross weight
        $gross = $mw > 0 ? $mw : ($gold + $silver);
        $less  = (float) ($it->less_weight ?? 0);
        $net   = max(0, $gross - $less);

        // purity (Tunch)
        $purityRaw = trim((string) ($it->purity ?? ''));
        if ($purityRaw === '') {
            $purityRaw = trim((string) (
                $metal === 'G'
                    ? ($it->item->gold_purity ?? '')
                    : ($it->item->silver_purity ?? '')
            ));
        }

        // purity -> percent
        $pctVal = 0.0;
        if ($purityRaw !== '') {
            $num = (float) preg_replace('/[^0-9.]/', '', $purityRaw);
            if (preg_match('/k/i', $purityRaw) && $num > 0 && $num <= 24) {
                $pctVal = $num / 24 * 100;                 // 22K
            } elseif ($metal === 'G' && $num > 0 && $num <= 24) {
                $pctVal = $num / 24 * 100;                 // 22 = karat
            } elseif ($num > 0 && $num <= 100) {
                $pctVal = $num;                            // 92.5 / 91.6
            }
        }

        // fine weight
        $fine = $pctVal > 0 ? round($net * $pctVal / 100, 3) : $net;

        return [
            'metal'  => $metal,
            'gross'  => $gross,
            'less'   => $less,
            'net'    => $net,
            'purity' => $purityRaw,
            'fine_g' => $metal === 'G' ? $fine : 0,
            'fine_s' => $metal === 'S' ? $fine : 0,
        ];
    };

    // ---- Receipt (cash / online / card / cheque) ----
    $receiptCash = (float) ($payRow->cash_amount ?? 0)
                 + (float) ($payRow->online_amount ?? 0)
                 + (float) ($payRow->card_amount ?? 0)
                 + (float) ($payRow->cheque_amount ?? 0);

    // ---- Kacchi / Bhav rows (controller se aate hain) ----
    $metals = collect($receiptMetals ?? []);
    $isGold = fn($m) => strtoupper($m['metal'] ?? 'S') === 'G';

    // ---- Purana balance ----
    $prev     = $prevBalance ?? [];
    $prevGold = (float) ($prev['gold'] ?? 0);
    $prevFine = (float) ($prev['fine'] ?? 0);
    $prevAmt  = (float) ($prev['amount'] ?? 0);
    $prevRef  = trim((string) ($prev['ref'] ?? ''));

    // ---- Item totals ----
    $calcRows  = $rows->map(fn($it) => $calc($it));
    $totGross  = (float) $calcRows->sum('gross');
    $totLess   = (float) $calcRows->sum('less');
    $totNet    = (float) $calcRows->sum('net');
    $totQty    = (float) $rows->sum(fn($it) => (float) ($it->quantity ?? $it->qty ?? 0));
    $totLabour = (float) $rows->sum(fn($it) => (float) ($it->making_charge ?? 0));
    $totGold   = (float) $calcRows->sum('fine_g');
    $totSilver = (float) $calcRows->sum('fine_s');

    // ---- Kacchi / Bhav totals ----
    $fineOf = fn($m) => isset($m['fine'])
        ? (float) $m['fine']
        : round(((float) ($m['gross'] ?? 0)) * ((float) ($m['tunch'] ?? 0)) / 100, 3);

    $kGold   = (float) $metals->filter($isGold)->sum($fineOf);
    $kSilver = (float) $metals->reject($isGold)->sum($fineOf);

    $bGold   = (float) $metals->filter($isGold)->sum(fn($m) => (float) ($m['bhav_wt'] ?? $fineOf($m)));
    $bSilver = (float) $metals->reject($isGold)->sum(fn($m) => (float) ($m['bhav_wt'] ?? $fineOf($m)));

    $bhavAmt = (float) $metals->sum(fn($m) =>
        ((float) ($m['bhav_wt'] ?? $fineOf($m))) * ((float) ($m['bhav'] ?? 0)) / 1000
    );

    // ---- Net Total ----
    $netGold   = round($totGold   - $kGold   - $bGold   + $prevGold, 3);
    $netSilver = round($totSilver - $kSilver - $bSilver + $prevFine, 3);
    $netAmt    = round($amount - $receiptCash + $bhavAmt + $prevAmt, 2);

    // ---- GST / Tax summary ----
    $subtotal  = (float) ($inv->subtotal ?? 0);
    $discount  = (float) ($inv->discount_total ?? 0);
    $charges   = (float) ($inv->charge_total ?? 0);
    $taxable   = round(max(0, $subtotal - $discount + $charges), 2);

    $cgstAmt = (float) ($inv->cgst_amount ?? 0);
    $sgstAmt = (float) ($inv->sgst_amount ?? 0);
    $igstAmt = (float) ($inv->igst_amount ?? 0);
    $cgstPct = (float) ($inv->cgst_percent ?? 0);
    $sgstPct = (float) ($inv->sgst_percent ?? 0);
    $igstPct = (float) ($inv->igst_percent ?? 0);

    $gstTotal = round($cgstAmt + $sgstAmt + $igstAmt, 2);
    if ($gstTotal <= 0) {
        $gstTotal = round((float) ($inv->tax_amount ?? 0), 2);
    }

    $tcsAmt   = (float) ($inv->tcs_amount ?? 0);
    $roundOff = (float) ($inv->round_off ?? 0);

    // ---- Khaali rows ----
    $minRows = 11;
    $fill = max(0, $minRows - $rows->count());
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Rough Estimate - {{ $inv->invoice_number ?? $inv->id }}</title>
<!-- pdf-orientation: landscape -->
<style>
    body {font-family: dejavusans, sans-serif; font-size: 9px; color: #202020; margin: 0;}
    table {width: 100%; border-collapse: collapse;}

    .head td {vertical-align: top;}
    .devotion {text-align:center; font-size:9px; letter-spacing:.3px; margin-bottom:3px;}
    .heading {text-align:center; font-size:15px; font-weight:bold; text-decoration:underline;}
    .copy {text-align:right; font-size:9px;}

    .meta {border-collapse: separate; border-spacing: 0; margin-top: 6px;}
    .meta td.box {border:1px solid #555; border-radius:6px; padding:5px 8px; vertical-align:top; height:36px;}
    .meta td.gap {width:3%; border:none;}
    .label {font-weight:bold; text-decoration:underline; margin-bottom:2px;}
    .cname {font-size:10px; font-weight:bold;}

    .items {margin-top:6px; table-layout:fixed; border:1px solid #444;}
    .items th {border:1px solid #444; padding:4px 3px; font-weight:normal; text-align:center;}
    .items td {border-left:1px solid #444; padding:2px 4px; vertical-align:top; height:15px;}
    .l {text-align:left;} .r {text-align:right;} .ctr {text-align:center;}
    .items tr.total td {font-weight:bold; border-top:1px solid #444; border-bottom:1px solid #444;}
    .items tr.last td {border-bottom:1px solid #444;}
    .mk {font-size:8px; line-height:1.3;}

    .sum {table-layout:fixed;}
    .sum td {padding:3px 4px; vertical-align:top;}

    .bottom {margin-top:8px;}
    .bottom td {vertical-align:top;}
    .notes {font-size:9px; line-height:1.6;}
    .tax {border:1px solid #444;}
    .tax td {padding:3px 6px; border-bottom:1px dotted #bbb;}
    .tax tr.strong td {font-weight:bold; border-top:1px solid #444; border-bottom:1px solid #444; background:#f3f3f3;}
    .tax tr.gst td {background:#fafafa;}
    .tax tr.last td {border-bottom:none;}
</style>
</head>
<body>

<table class="head">
<tr>
    <td style="width:20%"></td>
    <td style="width:60%">
        <div class="devotion">Rough Estimate</div>
    </td>
    <td style="width:20%" class="copy">Customer Copy</td>
</tr>
</table>

<table class="meta">
<tr>
<td class="box" style="width:60%">
    <div class="label">Customer Name &amp; Address :</div>
    <div class="cname">{{ $up($custName) }}</div>
    @if($custAddress !== '')
        <div>{{ $up($custAddress) }}</div>
    @endif
    <div>Ph # {{ $custMobile }}</div>
</td>
<td class="gap"></td>
<td class="box" style="width:37%">
    Bill No. : <strong>{{ $inv->invoice_number ?? $inv->id }}</strong><br><br>
    Date : <strong>{{ $billDate }}</strong>
</td>
</tr>
</table>

<table class="items">
<colgroup>
    <col width="4%"><col width="22%"><col width="8%"><col width="7%"><col width="8%">
    <col width="5%"><col width="8%"><col width="12%"><col width="8%"><col width="8%"><col width="10%">
</colgroup>
<thead><tr>
<th>Type</th>
<th>Description</th>
<th>G. Wt.</th>
<th>L. Wt.</th>
<th>N. Wt.</th>
<th>Pcs</th>
<th>Tunch</th>
<th>Lbr</th>
<th>Gold</th>
<th>Silver</th>
<th>Amount<br>(Rupees)</th>
</tr></thead>
<tbody>

{{-- ITEMS --}}
@foreach($rows as $it)
@php
    $k = $calc($it);
    $qty  = (float) ($it->quantity ?? $it->qty ?? 0);
    $line = (float) ($it->amount ?? 0);

    // ---- Lbr: making charge type + rate + calculated amount ----
    $mkType = strtolower(trim((string) ($it->making_charge_type ?? '')));
    if ($mkType === 'percent') $mkType = 'percentage';
    $mkRate = (float) ($it->making_rate ?? 0);
    $mkAmt  = (float) ($it->making_charge ?? 0);
    $mkRateTxt = $pct($mkRate);

    $mkLine1 = '';
    $mkLine2 = '';
    if ($mkRate > 0 || $mkAmt > 0) {
        switch ($mkType) {
            case 'percentage':  $mkLine1 = $mkRateTxt . '% (Percent)'; break;
            case 'fixed':       $mkLine1 = $mkRateTxt . ' (Fixed)'; break;
            case 'per_gram':    $mkLine1 = $mkRateTxt . '/gm (Per Gram)'; break;
            case 'per_product': $mkLine1 = $mkRateTxt . '/pc (Per Product)'; break;
            default:            $mkLine1 = $mkRateTxt;
        }
        $mkLine2 = '= ' . $fmt2($mkAmt);
    }
@endphp
<tr>
<td class="ctr">{{ $k['metal'] }}</td>
<td>{{ $up($it->item->name ?? $it->description ?? 'ITEM') }}</td>
<td class="r">{{ $k['gross'] > 0 ? $fmt3($k['gross']) : '' }}</td>
<td class="r">{{ $k['less'] > 0 ? $fmt3($k['less']) : '' }}</td>
<td class="r">{{ $k['net'] > 0 ? $fmt3($k['net']) : '' }}</td>
<td class="ctr">{{ $qty > 0 ? $fmt2($qty) : '' }}</td>
<td class="ctr">{{ $k['purity'] }}</td>
<td class="ctr mk">
    @if($mkLine1 !== '')
        {{ $mkLine1 }}<br>{{ $mkLine2 }}
    @endif
</td>
<td class="r">{{ $k['fine_g'] > 0 ? $fmt3($k['fine_g']) : '' }}</td>
<td class="r">{{ $k['fine_s'] > 0 ? $fmt3($k['fine_s']) : '' }}</td>
<td class="r">{{ $fmt2($line) }}</td>
</tr>
@endforeach

{{-- KHAALI ROWS --}}
@for($i = 0; $i < $fill; $i++)
<tr>
<td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
</tr>
@endfor

{{-- ITEMS TOTAL --}}
<tr class="total">
<td></td>
<td></td>
<td class="r">{{ $totGross > 0 ? $fmt3($totGross) : '' }}</td>
<td class="r">{{ $totLess > 0 ? $fmt3($totLess) : '' }}</td>
<td class="r">{{ $totNet > 0 ? $fmt3($totNet) : '' }}</td>
<td class="ctr">{{ $totQty > 0 ? $fmt2($totQty) : '' }}</td>
<td></td>
<td class="r">{{ $totLabour > 0 ? $fmt2($totLabour) : '' }}</td>
<td class="r">{{ $totGold > 0 ? $fmt3($totGold) : '' }}</td>
<td class="r">{{ $totSilver > 0 ? $fmt3($totSilver) : '' }}</td>
<td class="r">{{ $fmt2($amount) }}</td>
</tr>

{{-- RECEIPT CASH --}}
@if($receiptCash > 0)
<tr>
<td></td>
<td>RECEIPT CASH</td>
<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
<td class="r">{{ $fmt2($receiptCash) }}</td>
</tr>
@endif

{{-- MR KACCHI + BHAV --}}
@foreach($metals as $m)
@php
    $mG    = $isGold($m);
    $mFine = $fineOf($m);
    $mBhav = (float) ($m['bhav'] ?? 0);
    $mBhWt = (float) ($m['bhav_wt'] ?? $mFine);
    $mAmt  = $mBhWt * $mBhav / 1000;
    $mName = $mG ? 'GOLD' : 'SILVER';
@endphp
<tr>
<td class="ctr">MR</td>
<td>KACCHI</td>
<td class="r">-{{ $fmt3($m['gross'] ?? 0) }}</td>
<td></td><td></td><td></td>
<td class="ctr">{{ $fmt2($m['tunch'] ?? 0) }}</td>
<td></td>
<td class="r">{{ $mG ? '-' . $fmt3($mFine) : '' }}</td>
<td class="r">{{ !$mG ? '-' . $fmt3($mFine) : '' }}</td>
<td></td>
</tr>
@if($mBhav > 0)
<tr>
<td></td>
<td>{{ substr($mName, 0, 1) }} {{ $mName }} BHAV @ {{ $fmt2($mBhav) }}</td>
<td></td><td></td><td></td><td></td><td></td><td></td>
<td class="r">{{ $mG ? '-' . $fmt3($mBhWt) : '' }}</td>
<td class="r">{{ !$mG ? '-' . $fmt3($mBhWt) : '' }}</td>
<td class="r">{{ $fmt2($mAmt) }}</td>
</tr>
@endif
@endforeach

{{-- PURANA BALANCE --}}
@if($prevGold != 0 || $prevFine != 0 || $prevAmt != 0)
<tr class="last">
<td></td>
<td>BAL @if($prevRef) [{{ $prevRef }}] @endif</td>
<td></td><td></td><td></td><td></td><td></td><td></td>
<td class="r">{{ $prevGold != 0 ? $fmt3($prevGold) : '' }}</td>
<td class="r">{{ $prevFine != 0 ? $fmt3($prevFine) : '' }}</td>
<td class="r">{{ $prevAmt != 0 ? $fmt2($prevAmt) : '' }}</td>
</tr>
@else
<tr class="last"><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
@endif

</tbody>
</table>

{{-- AMOUNT IN WORDS + NET TOTAL --}}
<table class="sum">
<colgroup>
    <col width="4%"><col width="22%"><col width="8%"><col width="7%"><col width="8%">
    <col width="5%"><col width="8%"><col width="12%"><col width="8%"><col width="8%"><col width="10%">
</colgroup>
<tr>
    <td colspan="5" class="l">Rs. {{ $words }}</td>
    <td colspan="3" class="r"><strong>Net Total</strong></td>
    <td class="r"><strong>{{ $netGold != 0 ? $fmt3($netGold) : '' }}</strong></td>
    <td class="r"><strong>{{ $fmt3($netSilver) }}</strong></td>
    <td class="r"><strong>{{ $fmt2($netAmt) }}</strong></td>
</tr>
</table>

{{-- NOTES (left) + GST / PAYMENT SUMMARY (right) --}}
<table class="bottom">
<tr>
    <td style="width:58%">
        <div class="notes">
            <strong>{{ $up($b->name ?? '') }}</strong><br>
            @if(!empty($b->mobile ?? $b->phone))
                Ph # {{ $b->mobile ?? $b->phone }}<br>
            @endif
            @if(!empty($b->gstin))
                GSTIN : {{ $b->gstin }}<br>
            @endif
            @if(!empty($inv->notes))
                {{ $inv->notes }}
            @endif
        </div>
    </td>
    <td style="width:2%"></td>
    <td style="width:40%">
        <table class="tax">
            @if($gstTotal > 0 || $discount > 0 || $charges > 0)
            <tr>
                <td>Taxable Amount</td>
                <td class="r">{{ $fmt2($taxable) }}</td>
            </tr>
            @endif

            @if($cgstAmt > 0)
            <tr class="gst">
                <td>CGST @if($cgstPct > 0) ({{ $pct($cgstPct) }}%) @endif</td>
                <td class="r">{{ $fmt2($cgstAmt) }}</td>
            </tr>
            @endif

            @if($sgstAmt > 0)
            <tr class="gst">
                <td>SGST @if($sgstPct > 0) ({{ $pct($sgstPct) }}%) @endif</td>
                <td class="r">{{ $fmt2($sgstAmt) }}</td>
            </tr>
            @endif

            @if($igstAmt > 0)
            <tr class="gst">
                <td>IGST @if($igstPct > 0) ({{ $pct($igstPct) }}%) @endif</td>
                <td class="r">{{ $fmt2($igstAmt) }}</td>
            </tr>
            @endif

            @if($gstTotal > 0)
            <tr>
                <td><strong>Total GST</strong></td>
                <td class="r"><strong>{{ $fmt2($gstTotal) }}</strong></td>
            </tr>
            @endif

            @if($tcsAmt > 0)
            <tr>
                <td>TCS</td>
                <td class="r">{{ $fmt2($tcsAmt) }}</td>
            </tr>
            @endif

            @if($roundOff != 0)
            <tr>
                <td>Round Off</td>
                <td class="r">{{ $fmt2($roundOff) }}</td>
            </tr>
            @endif

            <tr class="strong">
                <td>Grand Total</td>
                <td class="r">{{ $fmt2($amount) }}</td>
            </tr>
            <tr>
                <td>Received Amount</td>
                <td class="r">{{ $fmt2($paid) }}</td>
            </tr>
            <tr class="strong last">
                <td>Balance</td>
                <td class="r">{{ $fmt2($due) }}</td>
            </tr>
        </table>
    </td>
</tr>
</table>

</body>
</html>