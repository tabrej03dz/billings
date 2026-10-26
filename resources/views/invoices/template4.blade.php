@php
    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? $business ?? $inv->business;
    $c = $client ?? $inv->client;
    $rows = collect($items ?? []);
    $fmt2 = fn($n) => number_format((float) $n, 2, '.', '');
    $fmt3 = fn($n) => number_format((float) $n, 3, '.', '');
    $amount = (float) ($inv->total ?? $inv->grand_total ?? 0);
    $paid = (float) ($inv->received_amount ?? 0);
    $due = (float) ($inv->balance ?? max(0, $amount - $paid));
    $invoiceDate = $inv->invoice_date ?? $inv->created_at;
    $billDate = $invoiceDate ? \Carbon\Carbon::parse($invoiceDate)->format('d/m/Y') : '';
    $words = trim((string) ($inv->amount_in_words ?? ''));
    if ($words === '' && class_exists(\NumberFormatter::class)) {
        $formatter = new \NumberFormatter('en_IN', \NumberFormatter::SPELLOUT);
        $whole = (int) floor(abs($amount));
        $paise = (int) round((abs($amount) - $whole) * 100);
        $words = ucfirst($formatter->format($whole)) . ' Rupees';
        if ($paise) $words .= ' And ' . ucfirst($formatter->format($paise)) . ' Paise';
        $words .= ' Only';
    }
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
    .devotion {text-align:center; font-size:9px; letter-spacing:.3px; margin-bottom:3px;}
    .heading {text-align:center; font-size:16px; font-weight:bold; text-decoration:underline; margin-bottom:6px;}
    .copy {float:right; font-size:9px;}
    .meta td {border:1px solid #777; padding:5px 8px; vertical-align:top; height:38px;}
    .label {font-weight:bold; text-decoration:underline;}
    .items {margin-top:6px; table-layout:fixed;}
    .items th {border-top:1px solid #777; border-bottom:1px solid #777; padding:4px 3px; font-weight:normal; text-align:center; background:#f6f6f6;}
    .items td {padding:3px 4px; vertical-align:top; border-bottom:1px dotted #ccc;}
    .items td + td, .items th + th {border-left:1px solid #b2b2b2;}
    .l {text-align:left;} .r {text-align:right;} .ctr {text-align:center;}
    .subtotal td {font-weight:bold; border-top:1px solid #777; border-bottom:1px solid #777;}
    .footer {margin-top:8px; border-top:1px solid #777; padding-top:5px;}
    .footer-table td {vertical-align:top;}
    .notes {font-size:9px; line-height:1.8;}
    .totals {width:100%;}
    .totals td {padding:2px 4px;}
    .totals .net td {border-top:1px solid #777; font-weight:bold;}
    .words {padding-top:8px; font-size:9px;}
    .bottom {margin-top:12px; font-size:9px; text-align:right;}
</style>
</head>
<body>
<div class="copy">Customer Copy</div>
<div class="devotion">"OM SHRI PADMAVATI MATA NAMAH"</div>
<div class="heading">Rough Estimate</div>

<table class="meta">
<tr>
<td style="width:65%">
    <div class="label">Customer Name &amp; Address :</div>
    <strong>{{ strtoupper($c->name ?? 'CASH CUSTOMER') }}</strong><br>
    {{ strtoupper($c->address ?? '') }}<br>
    Ph # {{ $c->mobile ?? $c->phone ?? '' }}
</td>
<td style="width:35%">
    Bill No.: <strong>{{ $inv->invoice_number ?? $inv->id }}</strong><br><br>
    Date : <strong>{{ $billDate }}</strong>
</td>
</tr>
</table>

<table class="items">
<thead><tr>
<th style="width:4%">Type</th>
<th style="width:26%">Description</th>
<th style="width:8%">G. Wt.</th>
<th style="width:8%">L. Wt.</th>
<th style="width:8%">N. Wt.</th>
<th style="width:5%">Pcs</th>
<th style="width:7%">Tunch</th>
<th style="width:8%">Lbr</th>
<th style="width:7%">Gold</th>
<th style="width:7%">Silver</th>
<th style="width:12%">Amount (Rs.)</th>
</tr></thead>
<tbody>
@forelse($rows as $it)
@php
    $gw = (float) ($it->gross_weight ?? $it->gross_wt ?? $it->gold_wt ?? $it->silver_wt ?? 0);
    $loss = (float) ($it->less_weight ?? $it->less_wt ?? 0);
    $nw = (float) ($it->net_weight ?? $it->net_wt ?? max(0, $gw - $loss));
    $qty = (float) ($it->quantity ?? $it->qty ?? 1);
    $goldWt = (float) ($it->gold_wt ?? 0);
    $silverWt = (float) ($it->silver_wt ?? 0);
    $metal = $goldWt > 0 ? 'G' : ($silverWt > 0 ? 'S' : '');
    $line = (float) ($it->amount ?? $it->line_total ?? $it->total ?? 0);
    $labor = (float) ($it->making_charge ?? $it->making_amount ?? 0);
    $tunch = $it->tunch ?? $it->purity ?? $it->karat ?? '';
@endphp
<tr>
<td class="ctr">{{ $metal }}</td>
<td>{{ strtoupper($it->item->name ?? $it->item_name ?? $it->name ?? 'ITEM') }}</td>
<td class="r">{{ $gw > 0 ? $fmt3($gw) : '' }}</td>
<td class="r">{{ $loss > 0 ? $fmt3($loss) : '' }}</td>
<td class="r">{{ $nw > 0 ? $fmt3($nw) : '' }}</td>
<td class="ctr">{{ $qty > 0 ? $fmt2($qty) : '' }}</td>
<td class="ctr">{{ $tunch }}</td>
<td class="r">{{ $labor > 0 ? $fmt2($labor) : '' }}</td>
<td class="r">{{ $goldWt > 0 ? $fmt3($goldWt) : '' }}</td>
<td class="r">{{ $silverWt > 0 ? $fmt3($silverWt) : '' }}</td>
<td class="r">{{ $fmt2($line) }}</td>
</tr>
@empty
<tr><td colspan="11" class="ctr">No items</td></tr>
@endforelse
<tr class="subtotal">
<td colspan="2" class="r">TOTAL</td>
<td class="r">{{ $fmt3($rows->sum(fn($it) => (float)($it->gross_weight ?? $it->gross_wt ?? $it->gold_wt ?? $it->silver_wt ?? 0))) }}</td>
<td colspan="2"></td>
<td class="ctr">{{ $fmt2($rows->sum(fn($it) => (float)($it->quantity ?? $it->qty ?? 1))) }}</td>
<td colspan="4"></td>
<td class="r">{{ $fmt2($amount) }}</td>
</tr>
</tbody>
</table>

<div class="footer">
  <table class="footer-table">
    <tr>
      <td style="width:60%">
        <div class="notes">
          <strong>{{ strtoupper($b->name ?? '') }}</strong><br>
          {{ $b->mobile ?? $b->phone ?? '' }}<br>
          {{ $inv->notes ?? '' }}
        </div>
      </td>
      <td style="width:40%">
        <table class="totals">
          <tr><td>Gross Total</td><td class="r">{{ $fmt2($amount) }}</td></tr>
          <tr><td>Received</td><td class="r">{{ $fmt2($paid) }}</td></tr>
          <tr class="net"><td>Net Balance</td><td class="r">{{ $fmt2($due) }}</td></tr>
        </table>
      </td>
    </tr>
  </table>
  <div class="words">Rs. {{ $words !== '' ? $words : $fmt2($amount) . ' Only' }}</div>
  <div class="bottom">Naam / Jama : __________________</div>
</div>
</body>
</html>