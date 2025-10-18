@php
    // Compact vars from controller
    $b  = $biz ?? $inv->business;
    $c  = $client ?? $inv->client;

    // Safe helpers
    $fmt = fn($v) => number_format((float)$v, 2);
    $d   = fn($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('d M Y') : '';
@endphp
    <!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $inv->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
        .wrap { width: 100%; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .muted { color:#555; }
        h1,h2,h3,h4 { margin: 0 0 6px 0; }
        table { width:100%; border-collapse: collapse; }
        th, td { padding: 8px; border:1px solid #ddd; }
        thead th { background:#f2f2f2; }
        .no-border td, .no-border th { border:0; }
        .totals td { padding:6px 8px; }
        .mb-10{ margin-bottom:10px; } .mb-6{ margin-bottom:6px; } .mt-10{ margin-top:10px; }
        .brand { display:flex; align-items:center; gap:10px; }
        .brand-logo { height: 50px; width:auto; }
        .sign-img { height: 50px; width:auto; }
    </style>
</head>
<body>
<div class="wrap">

    {{-- Header: Logo + Business info + Invoice meta --}}
    <table class="no-border" style="margin-bottom:8px;">
        <tr class="no-border">
            <td class="no-border" style="vertical-align:top;">
                <div class="brand">
                    @if(!empty($logo))
                        <img class="brand-logo" src="{{ $logo }}" alt="Logo">
                    @endif
                    <div>
                        <h2>{{ $b->name ?? '—' }}</h2>
                        @if(!empty($b?->address))<div class="muted">{{ $b->address }}</div>@endif
                        <div class="muted">
                            @if(!empty($b?->email)) Email: {{ $b->email }} @endif
                            @if(!empty($b?->email) && !empty($b?->mobile)) | @endif
                            @if(!empty($b?->mobile)) Phone: {{ $b->mobile }} @endif
                        </div>
                        @if(!empty($b?->gstin))<div class="muted">GSTIN: {{ $b->gstin }}</div>@endif
                    </div>
                </div>
            </td>
            <td class="no-border text-right" style="vertical-align:top;">
                <h2>INVOICE</h2>
                <div>Invoice No: <strong>{{ $inv->invoice_number }}</strong></div>
                <div>Date: <strong>{{ $d($inv->invoice_date) }}</strong></div>
                @if(!empty($inv->due_date))
                    <div>Due: <strong>{{ $d($inv->due_date) }}</strong></div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Bill To + Summary --}}
    <table class="no-border mb-10">
        <tr>
            <td style="width:55%; vertical-align:top;">
                <h4>Bill To</h4>
                <div><strong>{{ $c->name ?? '—' }}</strong></div>
                @if(!empty($c?->address))<div class="muted">{{ $c->address }}</div>@endif
                @if(!empty($c?->mobile)) <div class="muted">Mobile: {{ $c->mobile }}</div> @endif
                @if(!empty($c?->gstin))  <div class="muted">GSTIN: {{ $c->gstin }}</div> @endif
            </td>
            <td style="width:45%; vertical-align:top;">
                <h4>Details</h4>
                <div class="muted">Subtotal: ₹ {{ $fmt($subtotal) }}</div>
                <div class="muted">Tax: ₹ {{ $fmt($tax_total) }}</div>
                @if(($discount_total ?? 0) > 0)
                    <div class="muted">Discount: - ₹ {{ $fmt($discount_total) }}</div>
                @endif
                @if(($charges_total ?? 0) > 0)
                    <div class="muted">Additional Charges: ₹ {{ $fmt($charges_total) }}</div>
                @endif
                @if(($tcs_percent ?? 0) > 0)
                    <div class="muted">TCS ({{ $fmt($tcs_percent) }}%): ₹ {{ $fmt($tcs_amount) }}</div>
                @endif
                @if(($round_off ?? 0) != 0)
                    <div class="muted">Round Off: {{ ($round_off >= 0 ? '+' : '−') }} ₹ {{ $fmt(abs($round_off)) }}</div>
                @endif
                <div class="muted">Total: <strong>₹ {{ $fmt($grand_total) }}</strong></div>
                <div class="muted">Received: ₹ {{ $fmt($received) }}</div>
                <div class="muted">Balance: <strong>₹ {{ $fmt($balance) }}</strong></div>
            </td>
        </tr>
    </table>

    {{-- Items --}}
    <table>
        <thead>
        <tr>
            <th style="width:30px;">#</th>
            <th>Item / Description</th>
            <th style="width:80px;">HSN/SAC</th>
            <th style="width:50px;">Qty</th>
            <th style="width:80px;">Rate</th>
            <th style="width:80px;">Making</th>
            <th style="width:70px;">Tax %</th>
            <th style="width:90px;">Amount</th>
        </tr>
        </thead>
        <tbody>
        @foreach(($items ?? []) as $i => $it)
            @php
                $qty   = (float)($it->quantity ?? 0);
                $rate  = (float)($it->rate ?? 0);
                $mk    = (float)($it->making_charge ?? 0);
                $tp    = (float)($it->tax_percent ?? 0);
                $base  = max(0, $qty * ($rate + $mk));
                $tax   = $base * ($tp/100);
                // Prefer DB amount if present, else compute
                $amount = $it->amount ?? ($base + $tax);
            @endphp
            <tr>
                <td class="text-center">{{ $i+1 }}</td>
                <td>{{ $it->description ?? ($it->item?->name ?? '') }}</td>
                <td class="text-center">{{ $it->sac_code ?? '' }}</td>
                <td class="text-center">{{ $fmt($qty) }}</td>
                <td class="text-right">₹ {{ $fmt($rate) }}</td>
                <td class="text-right">₹ {{ $fmt($mk) }}</td>
                <td class="text-center">{{ $fmt($tp) }}</td>
                <td class="text-right">₹ {{ $fmt($amount) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Additional Charges (if any) --}}
    @if(($charges ?? collect())->count() > 0)
        <table class="no-border" style="margin-top:8px;">
            <tr>
                <td class="no-border" style="padding:0;">
                    <table>
                        <thead>
                        <tr>
                            <th>Additional Charge</th>
                            <th style="width:120px;" class="text-right">Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($charges as $ch)
                            <tr>
                                <td>{{ $ch->name }}</td>
                                <td class="text-right">₹ {{ $fmt($ch->amount ?? 0) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    @endif

    {{-- Notes + Totals + Signature --}}
    <table class="no-border" style="margin-top:10px;">
        <tr>
            <td class="no-border" style="width:55%; vertical-align:top;">
                @if(!empty($inv->notes))
                    <div class="mb-6"><strong>Notes</strong></div>
                    <div class="muted">{{ $inv->notes }}</div>
                @endif

                @if(!empty($inv->terms))
                    <div class="mt-10 mb-6"><strong>Terms &amp; Conditions</strong></div>
                    <div class="muted">{{ $inv->terms }}</div>
                @endif
            </td>
            <td class="no-border" style="width:45%; vertical-align:top;">
                <table class="totals" style="width:100%; margin-bottom:20px;">
                    <tr><td class="text-right"><strong>Subtotal</strong></td><td class="text-right">₹ {{ $fmt($subtotal) }}</td></tr>
                    <tr><td class="text-right"><strong>Tax</strong></td><td class="text-right">₹ {{ $fmt($tax_total) }}</td></tr>
                    @if(($discount_total ?? 0) > 0)
                        <tr><td class="text-right">Discount</td><td class="text-right">- ₹ {{ $fmt($discount_total) }}</td></tr>
                    @endif
                    @if(($charges_total ?? 0) > 0)
                        <tr><td class="text-right">Additional Charges</td><td class="text-right">₹ {{ $fmt($charges_total) }}</td></tr>
                    @endif
                    @if(($tcs_percent ?? 0) > 0)
                        <tr><td class="text-right">TCS ({{ $fmt($tcs_percent) }}%)</td><td class="text-right">₹ {{ $fmt($tcs_amount) }}</td></tr>
                    @endif
                    @if(($round_off ?? 0) != 0)
                        <tr><td class="text-right">Round Off</td><td class="text-right">{{ ($round_off >= 0 ? '+' : '−') }} ₹ {{ $fmt(abs($round_off)) }}</td></tr>
                    @endif
                    <tr><td class="text-right"><strong>Total</strong></td><td class="text-right"><strong>₹ {{ $fmt($grand_total) }}</strong></td></tr>
                    <tr><td class="text-right">Received</td><td class="text-right">₹ {{ $fmt($received) }}</td></tr>
                    <tr><td class="text-right"><strong>Balance</strong></td><td class="text-right"><strong>₹ {{ $fmt($balance) }}</strong></td></tr>
                </table>

                {{-- Signature block --}}
                <div class="text-right">
                    @if(!empty($sign))
                        <img class="sign-img" src="{{ $sign }}" alt="Signature">
                    @endif
                    <div style="border-top:1px solid #999; margin-top:6px; padding-top:4px;">
                        Authorized Signatory
                    </div>
                    <div class="muted">{{ $b->name ?? '' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <p class="text-center muted" style="margin-top:20px;">Thank you for your business!</p>
</div>
</body>
</html>
