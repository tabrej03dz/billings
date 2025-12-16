@php
    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? ($inv->business ?? null);
    $c = $client ?? ($inv->client ?? null);

    $fmt0 = fn($v) => number_format((float)$v, 0, '.', '');
    $fmt2 = fn($v) => number_format((float)$v, 2, '.', '');
    $dmy  = fn($date) => $date ? \Carbon\Carbon::parse($date)->format('d-m-Y') : '';

    // totals
    $less    = (float)($less_amount ?? 0);
    $balance = (float)($balance_amount ?? $balance ?? 0);

    $cgst = (float)($cgst_amount ?? 0);
    $sgst = (float)($sgst_amount ?? 0);
    $igst = (float)($igst_amount ?? 0);

    $totalValue = (float)($grand_total ?? 0);
    $addr = $b->address ?? '';

    // ✅ SAME payment resolving logic
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
        body{ font-family: DejaVu Sans, Arial, sans-serif; font-size:10px; margin:0; }
        .page{ padding:18px; }

        table{ width:100%; border-collapse:collapse; }
        .no-border td{ border:0!important; }
        .b1{ border:1px solid #111; }

        .tbl th,.tbl td{ border:1px solid #111; padding:5px; font-size:9.5px; }
        .tbl th{ background:#f2f2f2; text-align:center; }

        .center{ text-align:center; }
        .right{ text-align:right; }
        .bold{ font-weight:700; }
        .tiny{ font-size:9px; }

        .totals td{ border:1px solid #111; padding:5px; }
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
    </style>
</head>

<body>
<div class="page">

    {{-- HEADER --}}
    <table class="no-border">
        <tr>
            <td style="width:25%">
                @if(!empty($logo))
                    <img src="{{ $logo }}" style="height:40px">
                @endif
            </td>
            <td class="center" style="width:50%">
                <div class="bold" style="font-size:16px;">{{ strtoupper($b->name ?? 'JEWELLERS') }}</div>
                <div class="tiny">TAX INVOICE</div>
            </td>
            <td style="width:25%" class="right"></td>
        </tr>
    </table>

    {{-- PARTY + META --}}
    <table class="no-border" style="margin-top:8px;">
        <tr>
            <td style="width:58%; padding-right:8px;">
                <table class="b1">
                    <tr>
                        <td class="bold">Name</td><td>{{ $c->name }}</td>
                        <td class="bold">State</td><td>{{ $c->state }}</td>
                    </tr>
                    <tr>
                        <td class="bold">Address</td><td>{{ $c->address }}</td>
                        <td class="bold">Code</td><td>{{ $c->state_code ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="bold">Phone</td><td>{{ $c->mobile }}</td>
                        <td class="bold">GSTIN</td><td>{{ $c->gstin ?: 'Unregistered' }}</td>
                    </tr>
                </table>
            </td>

            <td style="width:42%;">
                <table class="b1">
                    <tr><td class="bold">Date</td><td class="right">{{ $dmy($inv->invoice_date) }}</td></tr>
                    <tr><td class="bold">Bill No</td><td class="right">{{ $inv->invoice_number }}</td></tr>
                    <tr><td class="bold">GST No</td><td class="right">{{ $inv->gst_no ?? ($b->gstin ?? '-') }}</td></tr>
                    <tr><td class="bold">Transport</td><td class="right">{{ $inv->transport_mode ?? 'By Hand' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ITEMS --}}
    <table class="tbl" style="margin-top:10px;">
        <thead>
        <tr>
            <th>#</th><th>Description</th><th>HSN</th><th>Qty</th>
            <th>Gold Wt</th><th>Silver Wt</th><th>Amount</th>
        </tr>
        </thead>
        <tbody>
        @php $sumQty=0; $sumAmt=0; @endphp
        @foreach($items as $i=>$it)
            @php
                $qty=(float)($it->quantity ?? 1);
                $amt=(float)($it->amount ?? 0);
                $sumQty+=$qty; $sumAmt+=$amt;
            @endphp
            <tr class="center">
                <td>{{ $i+1 }}</td>
                <td style="text-align:left">{{ $it->description }}</td>
                <td>{{ $it->hsn_code }}</td>
                <td>{{ $fmt0($qty) }}</td>
                <td>{{ $fmt2($it->gold_wt ?? 0) }}</td>
                <td>{{ $fmt2($it->silver_wt ?? 0) }}</td>
                <td class="right bold">{{ $fmt0($amt) }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="3" class="right bold">Total</td>
            <td class="bold">{{ $fmt0($sumQty) }}</td>
            <td colspan="2"></td>
            <td class="right bold">{{ $fmt0($sumAmt) }}</td>
        </tr>
        </tbody>
    </table>

    {{-- PAYMENT + TOTALS --}}
    <table class="no-border" style="margin-top:10px;">
        <tr>
            <td style="width:58%; padding-right:8px;">

                <div class="bold">Payment Breakup</div>
                <table class="paytbl">
                    <tr><td class="k">Cash</td><td class="v">{{ $fmt0($cashAmt) }}</td></tr>
                    <tr><td class="k">UPI / Online</td><td class="v">{{ $fmt0($onlineAmt) }}</td></tr>
                    <tr><td class="k">Card</td><td class="v">{{ $fmt0($cardAmt) }}</td></tr>
                    <tr><td class="k">Cheque</td><td class="v">{{ $fmt0($chequeAmt) }}</td></tr>
                    <tr><td class="k">Credit Excess</td><td class="v">{{ $fmt0($creditExcess) }}</td></tr>
                    <tr><td class="k">Advance</td><td class="v">{{ $fmt0($advanceAmt) }}</td></tr>
                    <tr><td class="k bold">Total Received</td><td class="v">{{ $fmt0($receivedTot) }}</td></tr>
                </table>

                <div class="paynote">
                    <div class="bold">Payment Details</div>
                    <div>Online Mode: {{ $onlineMode ?: '-' }}</div>
                    <div>UPI ID: {{ $upiId ?: '-' }}</div>
                    <div>Online Ref: {{ $onlineRef ?: '-' }}</div>
                    <div>Card: {{ $cardLast4 ?: '-' }} {{ $cardRef }}</div>
                    <div>Cheque: {{ $chequeNo }} {{ $bankName }}</div>
                </div>

            </td>

            <td style="width:42%;">
                <table class="totals">
                    <tr><td class="label">Less</td><td class="val">{{ $fmt0($less) }}</td></tr>
                    <tr><td class="label">Balance</td><td class="val">{{ $fmt0($balance) }}</td></tr>
                    <tr><td class="label">CGST</td><td class="val">{{ $fmt0($cgst) }}</td></tr>
                    <tr><td class="label">SGST</td><td class="val">{{ $fmt0($sgst) }}</td></tr>
                    <tr><td class="label">IGST</td><td class="val">{{ $fmt0($igst) }}</td></tr>
                    <tr><td class="label bold">Total</td><td class="val">{{ $fmt0($totalValue) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <div class="footerline center">
        <b>{{ $addr }}</b>
        @if($b->mobile) , {{ $b->mobile }} @endif
        @if($b->email) , {{ $b->email }} @endif
    </div>

</div>
</body>
</html>
