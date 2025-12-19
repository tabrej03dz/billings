@php
    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? ($inv->business ?? null);
    $c = $client ?? ($inv->client ?? null);
    $items = $items ?? collect();

    $fmt0 = fn($v) => number_format((float)$v, 0, '.', '');
    $fmt2 = fn($v) => number_format((float)$v, 2, '.', '');
    $dmy  = fn($date) => $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '';

    // invoice totals (controller se aa rahe)
    $taxable = (float)($subtotal ?? 0);
    $igst    = (float)($igst_amount ?? $tax_total ?? 0);
    $total   = (float)($grand_total ?? 0);
    $received = (float)($received ?? 0);

    // payment row totals (aapka logic)
    $pay = $payRow ?? null;
    $cashAmt   = (float)($pay->cash_amount ?? 0);
    $onlineAmt = (float)($pay->online_amount ?? 0);
    $cardAmt   = (float)($pay->card_amount ?? 0);
    $chequeAmt = (float)($pay->cheque_amount ?? 0);
    $creditExcess = (float)($pay->credit_sales_excess_amount ?? 0);
    $advanceAmt   = (float)($pay->advance_amount ?? 0);

    $receivedTot = (float)($pay->received_total
        ?? ($cashAmt + $onlineAmt + $cardAmt + $chequeAmt + $creditExcess + $advanceAmt)
        ?? $received
    );

    $balanceNow = (float)($balance ?? max(0, $total - $receivedTot));

    // amount in words (simple INR words)
    function inr_words($number){
        $no = floor($number);
        if ($no <= 0) return 'Zero Rupees';
        $words = array(
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six', 7 => 'Seven',
            8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen',
            15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
            30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        );
        $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        $str = array();
        $i = 0;
        while($no > 0){
            $divider = ($i == 1) ? 10 : 100;
            $n = $no % $divider;
            $no = (int)($no / $divider);
            $i += ($divider == 10) ? 1 : 2;

            if($n){
                $plural = (($counter = count($str)) && $n > 9) ? '' : '';
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : '';
                if($n < 21){
                    $str[] = $words[$n].' '.$digits[$counter].$plural.' '.$hundred;
                }else{
                    $str[] = $words[(int)($n/10)*10].' '.$words[$n%10].' '.$digits[$counter].$plural.' '.$hundred;
                }
            }else{
                $str[] = null;
            }
        }
        $result = trim(implode('', array_reverse($str)));
        return $result.' Rupees';
    }

    // small helpers from image
    $invoiceNo  = $inv->invoice_number ?? $inv->invoice_no ?? '-';
    $invoiceDate = $inv->invoice_date ?? $inv->date ?? null;

    $gstin = $c->gstin ?? $c->gst_number ?? $c->gst ?? '';
    $pan   = $c->pan ?? $c->pan_number ?? '';
    $pos   = $c->place_of_supply ?? $c->state ?? '';
    $mobile = $c->mobile ?? $c->phone ?? $c->phone1 ?? '';

    // business meta
    $b_addr = trim((string)($b->address ?? ''));
    $b_city = trim((string)($b->city ?? ''));
    $b_pin  = trim((string)($b->pin ?? ''));
    $b_state= trim((string)($b->state ?? ''));
    $b_mobile = $b->mobile ?? $b->phone ?? '';
    $b_email  = $b->email ?? '';
    $b_gstin  = $b->gstin ?? ($inv->gst_no ?? '');

    // item/tax percent (image me 18%)
    $taxPercent = (float)($inv->tax_percent ?? 18);
@endphp

    <!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax Invoice {{ $invoiceNo }}</title>
    <style>
        *{ box-sizing:border-box; }
        body{
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size:10px;
            color:#111;
            margin:0;
            padding:0;
        }
        .wrap{

            margin:10px;
            padding:10px 12px 12px 12px;
        }
        .topbar{
            border-top:4px solid #2b2b2b;
            margin:-10px -12px 8px -12px;
        }
        .muted{ color:#444; }
        .red{ color:#d60000; }
        .bold{ font-weight:700; }
        .right{ text-align:right; }
        .center{ text-align:center; }
        table{ width:100%; border-collapse:collapse; }

        .smalltag{
            display:inline-block;
            border:1px solid #999;
            padding:2px 6px;
            font-size:8.5px;
            margin-left:6px;
            color:#333;
        }
        .headerRow td{ vertical-align:top; }

        .line-red{
            border-top:3px solid #d60000;
            margin:8px 0 6px 0;
        }

        .greybar{
            background:#e9e9e9;
            padding:8px 10px;
            margin:6px 0 10px 0;
            font-size:9.5px;
        }

        .billto{
            font-size:9.5px;
            margin-bottom:8px;
        }
        .billto .name{ font-size:10.5px; font-weight:700; }

        .svc{
            border-top:2px solid #d60000;
            border-bottom:2px solid #d60000;
            margin-top:6px;
        }
        .svc th, .svc td{
            padding:6px 6px;
            font-size:9.5px;
        }
        .svc thead th{
            text-align:left;
            font-weight:700;
        }
        .svc thead th.right,
        .svc td.right{ text-align:right; }
        .svc tbody td{
            border-top:1px solid #cfcfcf;
            vertical-align:top;
        }
        .svc .descSmall{
            font-size:8.3px;
            color:#555;
            margin-top:2px;
        }

        .subtotalRow{
            border-top:2px solid #d60000;
            border-bottom:2px solid #d60000;
        }
        .subtotalRow td{
            padding:6px 6px;
            font-size:9.5px;
            font-weight:700;
        }

        .bottom{
            margin-top:10px;
        }

        .totalsBox{
            width:52%;
            margin-left:auto;
            font-size:9.5px;
        }
        .totalsBox td{
            padding:4px 6px;
        }
        .totalsBox .lab{
            text-align:right;
            color:#333;
        }
        .totalsBox .val{
            text-align:right;
            font-weight:700;
            width:120px;
            border-bottom:1px solid #666;
        }
        .totalsBox .strong .lab,
        .totalsBox .strong .val{ font-weight:700; }

        .amountWords{
            width:52%;
            margin-left:auto;
            margin-top:10px;
            font-size:9.2px;
        }
        .signArea{
            width:52%;
            margin-left:auto;
            margin-top:14px;
            text-align:right;
            font-size:9px;
        }
        .signImg{
            height:34px;
            width:auto;
            margin-bottom:6px;
        }
        .auth{
            font-weight:700;
            margin-top:6px;
        }
    </style>
</head>

<body>
<div class="wrap">
    <div class="topbar"></div>

    {{-- HEADER --}}
    <table class="headerRow">
        <tr>
            <td style="width:18%;">
                @if(!empty($logo))
                    <img src="{{ $logo }}" alt="Logo" style="height:auto;width:100%;">
                @endif
            </td>

            <td style="width:62%;">
                <div>
                    <span class="bold" style="font-size:11px;">TAX INVOICE</span>
                    <span class="smalltag">ORIGINAL FOR RECIPIENT</span>
                </div>

                <div class="red bold" style="font-size:18px; margin-top:6px;">
                    {{ $b->name ?? 'Real Victory Groups' }}
                </div>

                <div class="muted" style="font-size:8.8px; margin-top:2px;">
                    {{ $b_addr }}@if($b_city), {{ $b_city }}@endif @if($b_state), {{ $b_state }}@endif @if($b_pin) ({{ $b_pin }})@endif
                </div>

                <div class="muted" style="font-size:8.8px; margin-top:2px;">
                    Mobile: {{ $b_mobile ?: '-' }}
                    &nbsp;&nbsp;&nbsp; GSTIN: {{ $b_gstin ?: '-' }}
                    <br>
                    Email: {{ $b_email ?: '-' }}
                </div>
            </td>

            <td style="width:20%;" class="right">
                <div class="bold" style="font-size:9.5px;">Think Outside The Box</div>
            </td>
        </tr>
    </table>

    <div class="line-red"></div>

    {{-- GREY BAR (Invoice No + Date) --}}
    <div class="greybar">
        <table>
            <tr>
                <td class="bold">Invoice No.: <span class="muted">{{ $invoiceNo }}</span></td>
                <td class="right bold">Invoice Date: <span class="muted">{{ $dmy($invoiceDate) }}</span></td>
            </tr>
        </table>
    </div>

    {{-- BILL TO --}}
    <div class="billto">
        <div class="bold">BILL TO</div>
        <div class="name">{{ strtoupper($c->name ?? '-') }}</div>
        <div class="muted">
            {{ $c->address ?? '-' }}
            @if(!empty($c->city)), {{ $c->city }}@endif
            @if(!empty($c->state)), {{ $c->state }}@endif
            @if(!empty($c->pin)), {{ $c->pin }}@endif
        </div>
        <div class="muted" style="margin-top:2px;">
            Mobile: {{ $mobile ?: '-' }}<br>
            GSTIN: {{ $gstin ?: '-' }}<br>
            PAN Number: {{ $pan ?: '-' }}<br>
            Place of Supply: {{ $pos ?: '-' }}
        </div>
    </div>

    {{-- SERVICES TABLE --}}
    @php
        // single service style (image जैसा). Multiple items हों तो भी चल जाएगा.
        $calcTax = $igst > 0 ? $igst : ($taxable * ($taxPercent/100));
        $calcTax = round($calcTax, 2);
        $calcTotal = $total > 0 ? $total : ($taxable + $calcTax);

        // If only 1 line, show rate as taxable; else keep per-line rate.
        $single = ($items->count() === 1);
    @endphp

    <table class="svc">
        <thead>
        <tr>
            <th style="width:52%;">SERVICES</th>
            <th style="width:10%;">SAC</th>
            <th style="width:10%;">QTY.</th>
            <th class="right" style="width:10%;">RATE</th>
            <th class="right" style="width:10%;">TAX</th>
            <th class="right" style="width:8%;">AMOUNT</th>
        </tr>
        </thead>
        <tbody>
        @foreach($items as $it)
            @php
                $desc = trim((string)($it->description ?? $it->name ?? ''));
                $sac  = $it->sac_code ?? $it->hsn_code ?? $it->sac ?? '';
                $qty  = (float)($it->quantity ?? 1);
                $qty  = $qty > 0 ? $qty : 1;

                // Your earlier "base amount" logic -> use lineBase if exists, else rate*qty
                $lineBase = (float)($it->line_base ?? 0);
                if($lineBase <= 0){
                    $r = (float)($it->rate ?? 0);
                    $lineBase = $r * $qty;
                }

                // show like image: rate = taxable (single package)
                $showRate = $single ? $taxable : $lineBase;
            @endphp
            <tr>
                <td>
                    <div class="bold">{{ $desc ?: '-' }}</div>
                    @if(!empty($it->note))
                        <div class="descSmall">{{ $it->note }}</div>
                    @else
                        <div class="descSmall">
                            {{ $it->extra_line ?? '' }}
                        </div>
                    @endif
                </td>
                <td>{{ $sac }}</td>
                <td>{{ $qty }} {{ $it->unit ?? '' }}</td>
                <td class="right">{{ $fmt2($showRate) }}</td>
                <td class="right">{{ $fmt2($calcTax) }}<div class="muted" style="font-size:8px;">({{ $fmt0($taxPercent) }}%)</div></td>
                <td class="right">{{ $fmt0($calcTotal) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- SUBTOTAL LINE --}}
    <table class="subtotalRow" style="margin-top:14px;">
        <tr>
            <td style="width:52%;">SUBTOTAL</td>
            <td style="width:10%;">{{ $fmt0($items->count() ?: 1) }}</td>
            <td style="width:10%;"></td>
            <td style="width:10%;"></td>
            <td style="width:10%;" class="right">₹ {{ $fmt2($calcTax) }}</td>
            <td style="width:8%;" class="right">₹ {{ $fmt0($calcTotal) }}</td>
        </tr>
    </table>

    {{-- TOTALS (RIGHT) --}}
    <div class="bottom">
        <table class="totalsBox">
            <tr>
                <td class="lab">Taxable Amount</td>
                <td class="val">₹ {{ $fmt2($taxable) }}</td>
            </tr>
            <tr>
                <td class="lab">IGST @{{ $fmt0($taxPercent) }}%</td>
                <td class="val">₹ {{ $fmt2($calcTax) }}</td>
            </tr>
            <tr class="strong">
                <td class="lab">Total Amount</td>
                <td class="val">₹ {{ $fmt0($calcTotal) }}</td>
            </tr>
            <tr>
                <td class="lab">Received Amount</td>
                <td class="val">₹ {{ $fmt0($receivedTot) }}</td>
            </tr>
            <tr class="strong">
                <td class="lab">Balance</td>
                <td class="val">₹ {{ $fmt0($balanceNow) }}</td>
            </tr>
        </table>

        <div class="amountWords">
            <div class="bold muted" style="margin-bottom:3px;">Total Amount (in words)</div>
            <div class="bold">{{ inr_words($calcTotal) }}</div>
        </div>

        <div class="signArea">
            @if(!empty($sign))
                <img src="{{ $sign }}" class="signImg" alt="Signature">
            @endif
            <div class="auth">AUTHORISED SIGNATORY FOR</div>
            <div class="muted">{{ $b->name ?? 'Real Victory Groups' }}</div>
        </div>
    </div>

</div>
</body>
</html>
