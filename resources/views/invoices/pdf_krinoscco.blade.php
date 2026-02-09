{{-- resources/views/invoices/pdf_receipt_exact.blade.php --}}
@php
    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? ($inv->business ?? null);
    $items = $items ?? ($inv->items ?? collect());

    // ======= IMPORTANT: receipt width control =======
    // 80mm thermal = mostly 42 chars, 58mm thermal = mostly 32 chars
    // Image looks like 80mm -> keep 42
    $W = 42;

    $line  = fn() => str_repeat('-', $W);
    $space = fn($n) => str_repeat(' ', max(0,(int)$n));

    $padR = fn($s, $n) => str_pad(mb_substr((string)$s, 0, $n), $n, ' ', STR_PAD_RIGHT);
    $padL = fn($s, $n) => str_pad(mb_substr((string)$s, 0, $n), $n, ' ', STR_PAD_LEFT);

    $money = fn($v) => number_format((float)$v, 2, '.', '');

    // center text exactly in width
    $center = function($s) use ($W){
        $s = trim((string)$s);
        $len = mb_strlen($s);
        if ($len >= $W) return mb_substr($s, 0, $W);
        $left = (int)floor(($W - $len)/2);
        return str_repeat(' ', $left).$s;
    };

    // wrap long text into receipt width
    $wrap = function($text, $max) {
        $text = trim((string)$text);
        if ($text === '') return [''];
        $words = preg_split('/\s+/', $text);
        $out = [];
        $cur = '';
        foreach($words as $w){
            $try = ($cur === '') ? $w : ($cur.' '.$w);
            if (mb_strlen($try) <= $max) $cur = $try;
            else { $out[] = $cur; $cur = $w; }
        }
        if ($cur !== '') $out[] = $cur;
        return $out;
    };

    // ======= HEADER (same as image) =======
    $hotelName = $b->name ?? 'Hotel Krinoscco';
    $subLine1  = $b->sub_line1 ?? '(A UNIT OF DPR ENTERPRISES PRIVATE';
    $subLine2  = $b->sub_line2 ?? 'LIMITED)';

    $addr1 = $b->address_line1 ?? 'Chowk- Ayodhya Road,';
    $addr2 = $b->address_line2 ?? 'Amaniganj,';
    $addr3 = $b->address_line3 ?? 'Ayodhya, Uttar Pradesh 224001';

    $tel   = $b->phone ?? $b->mobile ?? '7275092525';
    $gstin = $b->gstin ?? ($inv->gst_no ?? '09AADCD6632P1Z5');
    $state = $b->state ?? 'UTTAR PRADESH';

    // Outlet / Section like image "Crescent"
    $outlet = $inv->outlet_name ?? $inv->section_name ?? 'Crescent';

    // ======= BILL META (same keys) =======
    $billNo = $inv->invoice_number ?? $inv->bill_no ?? 'CR'.($inv->id ?? '0000');

    // image has date+time on same line
    $dt = $inv->bill_datetime ?? $inv->created_at ?? $inv->invoice_date ?? now();
    $dt = \Carbon\Carbon::parse($dt);
    $billDate = $dt->format('d/m/y');
    $billTime = $dt->format('h:i:s a');

    $tableNo = $inv->table_no ?? $inv->table ?? '';
    $steward = $inv->steward ?? $inv->waiter ?? '';
    $cover   = $inv->cover ?? $inv->pax ?? '';

    // ======= KOT =======
    $kots = [];
    if (!empty($inv->kots_json)) {
        $tmp = json_decode($inv->kots_json, true);
        if (is_array($tmp)) {
            $kots = collect($tmp)->map(fn($v)=>trim((string)$v))->filter()->unique()->values()->all();
        }
    }
    $kotLine = !empty($kots) ? implode(',', $kots) : '';

    // ======= TOTALS (same labels) =======
    $subTotal   = (float)($inv->subtotal ?? 0);
    $discount   = (float)($inv->discount_total ?? 0);

    // SGST/CGST like image (9% + 9%) or IGST
    $sgstPct = (float)($inv->sgst_percent ?? 0);
    $cgstPct = (float)($inv->cgst_percent ?? 0);
    $igstPct = (float)($inv->igst_percent ?? 0);

    $sgstAmt = (float)($inv->sgst_amount ?? 0);
    $cgstAmt = (float)($inv->cgst_amount ?? 0);
    $igstAmt = (float)($inv->igst_amount ?? 0);

    $roundOff  = (float)($inv->round_off ?? 0);
    $billAmt   = (float)($inv->total ?? 0);

    $received  = (float)($inv->received_amount ?? $billAmt);
    $balance   = (float)($inv->balance ?? 0);

    // Words (exact style like image)
    function words_receipt_rs($amount){
        $amount = (float)$amount;
        $rupees = (int) round($amount);

        $ones = ['', 'One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten',
            'Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
        $tens = ['', '', 'Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];

        $two = function($n) use ($ones,$tens){
            $n = (int)$n;
            if ($n == 0) return '';
            if ($n < 20) return $ones[$n];
            return trim($tens[(int)($n/10)] . ' ' . $ones[$n%10]);
        };

        $parts = [];
        if ($rupees >= 10000000) { $cr=(int)floor($rupees/10000000); $parts[]=$two($cr).' Crore'; $rupees%=10000000; }
        if ($rupees >= 100000)   { $lk=(int)floor($rupees/100000);   $parts[]=$two($lk).' Lakh';  $rupees%=100000; }
        if ($rupees >= 1000)     { $th=(int)floor($rupees/1000);     $parts[]=$two($th).' Thousand'; $rupees%=1000; }
        if ($rupees >= 100)      { $hd=(int)floor($rupees/100);      $parts[]=$ones[$hd].' Hundred'; $rupees%=100; }
        if ($rupees > 0)         { $parts[]=$two($rupees); }

        $w = trim(implode(' ', array_filter($parts)));
        if ($w === '') $w = 'Zero';
        return $w;
    }

    // Cashier line like image

    $cashier = $inv->createdBy->name;

    // ======= COLUMNS (match image) =======
    // ItemName(22) Qty(4) Rate(7) Amount(9) => 42
    $C_NAME = 22;
    $C_QTY  = 4;
    $C_RATE = 7;
    $C_AMT  = 9;
@endphp

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 8px 10px; }
        body{
            font-family: "DejaVu Sans Mono", "Courier New", monospace;
            font-size: 11px;
            color:#000;
        }
        .pre{ white-space: pre; line-height: 1.25; }
    </style>
</head>
<body>

<div class="pre">{{ $center($hotelName) }}</div>
<div class="pre">{{ $center($subLine1) }}</div>
<div class="pre">{{ $center($subLine2) }}</div>
<div class="pre">{{ $center($addr1) }}</div>
<div class="pre">{{ $center($addr2) }}</div>
<div class="pre">{{ $center($addr3) }}</div>
<div class="pre">{{ $center('Tel: '.$tel) }}</div>
<div class="pre">{{ $center('GSTIN: '.$gstin) }}</div>
<div class="pre">{{ $center('State : '.strtoupper($state)) }}</div>

<div class="pre"></div>
<div class="pre">{{ $center('TAX INVOICE') }}</div>
<div class="pre">{{ $center($outlet) }}</div>

<div class="pre">{{ $line() }}</div>

{{-- Bill No / Bill Date line EXACT style --}}
<div class="pre">
{{ $padR('Bill No: '.$billNo, 18) }}{{ $space(2) }}{{ $padR('Bill Date: '.$billDate.'  '.$billTime, 22) }}
</div>

<div class="pre">{{ $line() }}</div>

{{-- Table/Steward/Cover header --}}
<div class="pre">
{{ $padR('Table No', 10) }}{{ $space(1) }}{{ $padR('Steward', 10) }}{{ $space(1) }}{{ $padR('Cover', 6) }}
</div>
{{-- values row --}}
<div class="pre">
{{ $padR($tableNo ?: '-', 10) }}{{ $space(1) }}{{ $padR($steward ?: '-', 10) }}{{ $space(1) }}{{ $padR($cover ?: '-', 6) }}
</div>

<div class="pre">{{ $line() }}</div>

@if($kotLine !== '')
<div class="pre">Kot : {{ $kotLine }}</div>
<div class="pre">{{ $line() }}</div>
@endif

{{-- Item header --}}
<div class="pre">
{{ $padR('Item Name', $C_NAME) }}{{ $padL('Qty', $C_QTY) }}{{ $padL('Rate', $C_RATE) }}{{ $padL('Amount', $C_AMT) }}
</div>
<div class="pre">{{ $line() }}</div>

{{-- Items --}}
@foreach($items as $it)
@php
    $name = trim((string)($it->description ?? $it->name ?? ''));
    $qty  = (float)($it->quantity ?? 1); if ($qty <= 0) $qty = 1;

    // IMAGE like receipt expects rate = unit rate, amount = line total
    // Your DB: rate may be line base. So convert to unit rate:
    $rateStored = (float)($it->rate ?? 0);
    $unitRate = ($qty > 0) ? round($rateStored / $qty, 2) : round($rateStored, 2);

    $lineAmount = (float)($it->amount ?? 0);
    if ($lineAmount <= 0) $lineAmount = round($unitRate * $qty, 2);

    // Optional tag line like VEG/DRY (if you have)
    $tag = trim((string)($it->tag ?? $it->category_tag ?? '')); // e.g. VEG / DRY
    $sac = trim((string)($it->sac_code ?? $it->hsn_code ?? $it->sac ?? '')); // image uses SAC:xxxxxx

    $nameLines = $wrap($name, $C_NAME);
@endphp

<div class="pre">
{{ $padR($nameLines[0] ?? '', $C_NAME) }}{{ $padL((int)$qty, $C_QTY) }}{{ $padL($money($unitRate), $C_RATE) }}{{ $padL($money($lineAmount), $C_AMT) }}
</div>

{{-- Next lines exactly like image: VEG then SAC --}}
@if($tag !== '')
<div class="pre">{{ $padR($tag, $C_NAME) }}</div>
@endif
@if($sac !== '')
<div class="pre">{{ $padR('SAC:'.$sac, $C_NAME) }}</div>
@endif

@endforeach

<div class="pre">{{ $line() }}</div>

{{-- Totals block EXACT style --}}
<div class="pre">{{ $padR('Sub Total', $W-9) }}{{ $padL($money($subTotal), 9) }}</div>

@if($discount > 0)
{{-- if you want exact label like Discount(BV-10%,FD-10%) then store it in inv->discount_label --}}
@php $discLabel = $inv->discount_label ?? 'Discount'; @endphp
<div class="pre">{{ $padR($discLabel, $W-9) }}{{ $padL($money($discount), 9) }}</div>
@endif

@if($sgstAmt > 0)
<div class="pre">{{ $padR('SGST '.($sgstPct ? rtrim(rtrim($money($sgstPct),'0'),'.') : '').'%', $W-9) }}{{ $padL($money($sgstAmt), 9) }}</div>
@endif
@if($cgstAmt > 0)
<div class="pre">{{ $padR('CGST '.($cgstPct ? rtrim(rtrim($money($cgstPct),'0'),'.') : '').'%', $W-9) }}{{ $padL($money($cgstAmt), 9) }}</div>
@endif
@if($igstAmt > 0)
<div class="pre">{{ $padR('IGST '.($igstPct ? rtrim(rtrim($money($igstPct),'0'),'.') : '').'%', $W-9) }}{{ $padL($money($igstAmt), 9) }}</div>
@endif

@if(abs($roundOff) > 0.0001)
<div class="pre">{{ $padR('Round Off', $W-9) }}{{ $padL($money($roundOff), 9) }}</div>
@endif

<div class="pre"></div>
<div class="pre">{{ $line() }}</div>
<div class="pre">{{ $padR('Bill Amount', $W-9) }}{{ $padL($money($billAmt), 9) }}</div>
<div class="pre">{{ $line() }}</div>

<div class="pre"></div>

{{-- Amount in words EXACT line breaks like image --}}
@php
    $words = words_receipt_rs($billAmt);
    $w1 = 'Amount In Words : RS '.$words.' Only.';
    $wLines = $wrap($w1, $W);
@endphp
@foreach($wLines as $wl)
<div class="pre">{{ $wl }}</div>
@endforeach

<div class="pre"></div>
<div class="pre">{{ $padR('Settlements:', $W) }}</div>
<div class="pre">{{ $line() }}</div>

<div class="pre">{{ $padR('Cash', $W-9) }}{{ $padL($money($received), 9) }}</div>
<div class="pre">{{ $line() }}</div>

<div class="pre">{{ $padR('Total Settlement', $W-9) }}{{ $padL($money($received), 9) }}</div>
<div class="pre">{{ $line() }}</div>

<div class="pre">{{ $padR('Balance', $W-9) }}{{ $padL($money($balance), 9) }}</div>

<div class="pre"></div>

<div class="pre">Cashier: {{ $cashier }}Guest Signature</div>
<div class="pre">Reprint(1)</div>
<div class="pre">{{ $line() }}</div>

</body>
</html>
