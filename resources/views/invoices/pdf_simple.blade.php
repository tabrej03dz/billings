@php
    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? ($inv->business ?? null);
    $c = $client ?? ($inv->client ?? null);

    $fmt0 = fn($v) => number_format((float)$v, 0, '.', '');
    $fmt2 = fn($v) => number_format((float)$v, 2, '.', '');
    $dmy  = fn($date) => $date ? \Carbon\Carbon::parse($date)->format('d-m-Y') : '';

    // totals (controller passed)
    $less    = (float)($less_amount ?? 0);
    $balance = (float)($balance_amount ?? $balance ?? 0);

    $cgst = (float)($cgst_amount ?? 0);
    $sgst = (float)($sgst_amount ?? 0);
    $igst = (float)($igst_amount ?? 0);

    $totalValue = (float)($grand_total ?? 0);
    $addr = $b->address ?? '';

    // ✅ payment resolving logic
    $pay = $payRow
        ?? ($payment ?? null)
        ?? ($inv->payment ?? null)
        ?? ($inv->invoicePayment ?? null)
        ?? ($inv->paymentRow ?? null)
        ?? null;

    $cashAmt   = (float)($pay->cash_amount ?? 0);
    $onlineAmt = (float)($pay->online_amount ?? 0);
    $cardAmt   = (float)($pay->card_amount ?? 0);
    $chequeAmt = (float)($pay->cheque_amount ?? 0);

    $creditExcess = (float)($pay->credit_sales_excess_amount ?? 0);
    $advanceAmt   = (float)($pay->advance_amount ?? 0);

    $receivedTot = (float)($pay->received_total
        ?? ($cashAmt + $onlineAmt + $cardAmt + $chequeAmt + $creditExcess + $advanceAmt)
    );

    $onlineMode = (string)($pay->online_mode ?? '');
    $upiId      = (string)($pay->upi_id ?? '');
    $onlineRef  = (string)($pay->online_ref ?? '');

    $cardLast4  = (string)($pay->card_last4 ?? '');
    $cardRef    = (string)($pay->card_ref ?? '');

    $chequeNo   = (string)($pay->cheque_no ?? '');
    $bankName   = (string)($pay->bank_name ?? '');
@endphp

    <!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $inv->invoice_number }}</title>

    <style>
        *{ box-sizing:border-box; }
        body{
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size:10px;
            color:#111;
            margin:0;
        }
        .page{  padding: 150px 20px 30px 40px; }

        table{ width:100%; border-collapse:collapse; }

        .no-border td,.no-border th{ border:0!important; }
        .b1{ border:1px solid #111; }

        .tbl th,.tbl td{
            border:1px solid #111;
            padding:5px 4px;
            font-size:9.5px;
        }
        .tbl th{
            background:#f2f2f2;
            font-weight:700;
            text-align:center;
        }

        .center{ text-align:center; }
        .right{ text-align:right; }
        .bold{ font-weight:700; }
        .muted{ color:#333; }
        .tiny{ font-size:9px; }

        .totals td{ border:1px solid #111; padding:5px; font-size:9.5px; }
        .totals .label{ width:70%; }
        .totals .val{ width:30%; text-align:right; font-weight:700; }

        .paytbl td{
            border:1px solid #111;
            padding:5px 6px;
            font-size:9.5px;
        }
        .paytbl .k{ width:55%; }
        .paytbl .v{ width:45%; text-align:right; font-weight:700; }

        .paynote{
            border:1px solid #111;
            padding:6px;
            font-size:9px;
            margin-top:6px;
        }

        .footerline{
            margin-top:10px;
            border-top:1px solid #111;
            padding-top:6px;
            font-size:9.5px;
        }

        .tag{
            display:inline-block;
            border:1px solid #111;
            padding:2px 10px;
            font-size:9px;
            font-weight:700;
            margin-top:5px;
        }
    </style>
</head>

<body>
<div class="page">

    {{-- SIMPLE HEADER (NO LETTERHEAD) --}}
    <table class="no-border" style="margin-bottom:6px;">
        <tr class="no-border">
            <td class="no-border" style="width:25%; vertical-align:top;">
                @if(!empty($logo))
                    <img src="{{ $logo }}" style="height:40px; width:auto;" alt="Logo">
                @endif
                <div class="tiny muted" style="margin-top:2px;">Hallmark Jewellery</div>
            </td>

            <td class="no-border center" style="width:50%; vertical-align:top;">
                <div class="bold" style="font-size:16px;">{{ strtoupper($b->name ?? 'JEWELLERS') }}</div>
                <div class="tag">TAX INVOICE</div>
            </td>

            <td class="no-border right" style="width:25%; vertical-align:top;">
                <div class="bold">Certified Diamond</div>
                <div class="tiny muted">Jewellery</div>
            </td>
        </tr>
    </table>

    {{-- PARTY + META --}}
    <table class="no-border" style="margin-bottom:8px;">
        <tr class="no-border">
            <td class="no-border" style="width:58%; vertical-align:top; padding-right:8px;">
                <table class="b1" style="font-size:9.5px;">
                    <tr>
                        <td style="padding:4px; width:14%;" class="bold">Name:</td>
                        <td style="padding:4px; width:36%;">{{ $c->name ?? '-' }}</td>
                        <td style="padding:4px; width:14%;" class="bold">State:</td>
                        <td style="padding:4px; width:36%;">{{ $c->state ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px;" class="bold">Add:</td>
                        <td style="padding:4px;">{{ $c->address ?? '-' }}</td>
                        <td style="padding:4px;" class="bold">Code:</td>
                        <td style="padding:4px;">{{ $c->state_code ?? ($c->code ?? '-') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px;" class="bold">Ph:</td>
                        <td style="padding:4px;">{{ $c->mobile ?? '-' }}</td>
                        <td style="padding:4px;" class="bold">GSTIN:</td>
                        <td style="padding:4px;">{{ $c->gstin ?: 'Unregistered' }}</td>
                    </tr>
                </table>
            </td>

            <td class="no-border" style="width:42%; vertical-align:top;">
                <table class="b1" style="font-size:9.5px;">
                    <tr><td style="padding:4px;" class="bold">Date:</td><td style="padding:4px;" class="right">{{ $dmy($inv->invoice_date) }}</td></tr>
                    <tr><td style="padding:4px;" class="bold">Bill No.</td><td style="padding:4px;" class="right">{{ $inv->invoice_number }}</td></tr>
                    <tr><td style="padding:4px;" class="bold">GST No.</td><td style="padding:4px;" class="right">{{ $inv->gst_no ?? ($b->gstin ?? '-') }}</td></tr>
                    <tr><td style="padding:4px;" class="bold">Transport Mode:</td><td style="padding:4px;" class="right">{{ $inv->transport_mode ?? 'By Hand' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ✅ ITEMS TABLE (SAME AS KAPOOR: Amount = BASE ONLY) --}}
    <table class="tbl">
        <thead>
        <tr>
            <th style="width:4%;">S.No.</th>
            <th style="width:18%;">Description</th>
            <th style="width:7%;">HSN</th>
            <th style="width:8%;">Making</th>
            <th style="width:9%;">Gold Rate</th>
            <th style="width:9%;">Silver Rate</th>
            <th style="width:8%;">Silver Wt</th>
            <th style="width:8%;">Gold Wt</th>
            <th style="width:9%;">Gem Wt(Ct)</th>
            <th style="width:9%;">Dia Wt(Ct)</th>
            <th style="width:12%;">Amount</th>
        </tr>
        </thead>

        <tbody>
        @php
            $sumBase = 0;
            $sumSilver = 0; $sumGold=0; $sumGem=0; $sumDia=0;
        @endphp

        @foreach(($items ?? []) as $i => $it)
            @php
                $qty = (float)($it->quantity ?? 1);
                $qty = $qty > 0 ? $qty : 1;

                $goldWt   = (float)($it->gold_wt ?? 0);
                $silverWt = (float)($it->silver_wt ?? 0);

                if(!$goldWt && ($it->metal_type ?? '') === 'gold')  $goldWt = (float)($it->metal_weight ?? 0);
                if(!$silverWt && ($it->metal_type ?? '') === 'silver') $silverWt = (float)($it->metal_weight ?? 0);

                $goldRate   = (float)($it->gold_rate ?? 0);
                $silverRate = (float)($it->silver_rate ?? 0);

                $metalPerUnit = ($goldWt*$goldRate) + ($silverWt*$silverRate);
                if($metalPerUnit <= 0){
                    $metalPerUnit = (float)($it->rate ?? 0);
                }

                $making = (float)($it->making_charge ?? $it->making_rate ?? 0);
                $stone  = (float)($it->stone_charges ?? 0);
                $disc   = (float)($it->discount ?? 0);

                $lineBase = max(0, ($qty * ($metalPerUnit + $making + $stone)) - $disc);

                $sumBase += $lineBase;

                $sumSilver += $silverWt;
                $sumGold   += $goldWt;
                $sumGem    += (float)($it->gemstone_wt_ct ?? 0);
                $sumDia    += (float)($it->diamond_wt_ct ?? 0);
            @endphp

            <tr class="center">
                <td>{{ $i+1 }}</td>
                <td style="text-align:left;">{{ $it->description ?? '' }}</td>
                <td>{{ $it->hsn_code ?? $it->sac_code ?? '' }}</td>

                <td class="right">{{ $fmt0($it->making_rate ?? $it->making_charge ?? 0) }}</td>
                <td class="right">{{ $fmt0($goldRate) }}</td>
                <td class="right">{{ $fmt0($silverRate) }}</td>
                <td class="right">{{ $fmt2($silverWt) }}</td>
                <td class="right">{{ $fmt2($goldWt) }}</td>
                <td class="right">{{ $fmt2($it->gemstone_wt_ct ?? 0) }}</td>
                <td class="right">{{ $fmt2($it->diamond_wt_ct ?? 0) }}</td>

                <td class="right bold">{{ $fmt0($lineBase) }}</td>
            </tr>
        @endforeach

        <tr>
            <td colspan="6" class="right bold">Total Wt./Amount</td>
            <td class="right bold">{{ $fmt2($sumSilver) }}</td>
            <td class="right bold">{{ $fmt2($sumGold) }}</td>
            <td class="right bold">{{ $fmt2($sumGem) }}</td>
            <td class="right bold">{{ $fmt2($sumDia) }}</td>
            <td class="right bold">{{ $fmt0($sumBase) }}</td>
        </tr>
        </tbody>
    </table>

    {{-- reverse charge + totals + payment breakup --}}
    <table class="no-border" style="margin-top:8px;">
        <tr class="no-border">

            {{-- LEFT --}}
            <td class="no-border" style="width:58%; vertical-align:top; padding-right:8px;">
                <div class="b1" style="padding:6px; font-size:9.5px;">
                    <span class="bold">Reverse Charge (Y/N)</span>
                    <span style="float:right;">{{ !empty($inv->reverse_charge) ? 'Y' : 'N' }}</span>
                </div>

                <div style="margin-top:8px;" class="bold">Payment Breakup:</div>

                <table class="paytbl" style="margin-top:4px;">
                    <tr><td class="k">Cash</td><td class="v">{{ $fmt0($cashAmt) }}</td></tr>
                    <tr><td class="k">UPI / Online</td><td class="v">{{ $fmt0($onlineAmt) }}</td></tr>
                    <tr><td class="k">Card</td><td class="v">{{ $fmt0($cardAmt) }}</td></tr>
                    <tr><td class="k">Cheque</td><td class="v">{{ $fmt0($chequeAmt) }}</td></tr>
                    <tr><td class="k">Credit Sales Excess</td><td class="v">{{ $fmt0($creditExcess) }}</td></tr>
                    <tr><td class="k">Advance</td><td class="v">{{ $fmt0($advanceAmt) }}</td></tr>
                    <tr><td class="k bold">Total Received</td><td class="v">{{ $fmt0($receivedTot) }}</td></tr>
                </table>

                <div class="paynote">
                    <div class="bold" style="margin-bottom:4px;">Payment Details:</div>
                    <div>Online Mode: {{ $onlineMode ?: '-' }}</div>
                    <div>UPI ID: {{ $upiId ?: '-' }}</div>
                    <div>Online Ref: {{ $onlineRef ?: '-' }}</div>
                    <div>Card Last4: {{ $cardLast4 ?: '-' }} {{ $cardRef ? " (Ref: $cardRef)" : '' }}</div>
                    <div>Cheque No: {{ $chequeNo ?: '-' }} {{ $bankName ? " (Bank: $bankName)" : '' }}</div>
                </div>
            </td>

            {{-- RIGHT --}}
            <td class="no-border" style="width:42%; vertical-align:top;">
                <table class="totals">
                    <tr><td class="label">Less :</td><td class="val">{{ $fmt0($less) }}</td></tr>
                    <tr><td class="label bold">Balance Amount :</td><td class="val">{{ $fmt0($balance) }}</td></tr>
                    <tr><td class="label">CGST :</td><td class="val">{{ $fmt0($cgst) }}</td></tr>
                    <tr><td class="label">SGST :</td><td class="val">{{ $fmt0($sgst) }}</td></tr>
                    <tr><td class="label">IGST :</td><td class="val">{{ $fmt0($igst) }}</td></tr>
                    <tr><td class="label bold">Total Value :</td><td class="val">{{ $fmt0($totalValue) }}</td></tr>
                </table>

                <table class="no-border" style="margin-top:6px; font-size:9.5px;">
                    <tr class="no-border">
                        <td class="no-border" style="width:50%;">Advance (Shown Above):</td>
                        <td class="no-border right" style="width:50%;">{{ $fmt0($advanceAmt) }}</td>
                    </tr>
                </table>
            </td>

        </tr>
    </table>

    {{-- ✅ TERMS --}}
    @if(!empty($b->terms))
        <div class="tiny" style="margin-top:10px;">
            <div class="bold" style="color:#111; margin-bottom:3px;">Terms & Conditions</div>
            <div style="white-space: pre-line;">
                {!! nl2br(e($b->terms)) !!}
            </div>
        </div>
    @endif

    <div class="center bold" style="margin-top:10px; font-size:14px;">
        PRAISE THE LORD
    </div>

    {{-- footer --}}
    <div class="footerline center">
        <div class="bold">
            {{ $addr }}
            @if(!empty($b->mobile)) , {{ $b->mobile }} @endif
            @if(!empty($b->email)) , E-mail : {{ $b->email }} @endif
        </div>
    </div>

</div>
</body>
</html>
