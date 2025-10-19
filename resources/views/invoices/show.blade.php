@php
    $b = $inv->business;
    $c = $inv->client;
@endphp

<x-layouts.app :title="__('Invoice '.$inv->invoice_number)">
    {{-- Action Bar (screen पर दिखे, print में hide) --}}
    <div class="mb-3 no-print" style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100">Invoice #{{ $inv->invoice_number }}</h1>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('invoices.download',$inv->id) }}" class="px-3 py-2 rounded bg-emerald-600 text-white">Download PDF</a>
            <button onclick="window.print()" class="px-3 py-2 rounded bg-gray-800 text-white">Print</button>
            <a href="{{ route('invoices.index') }}" class="px-3 py-2 rounded border border-gray-300 dark:border-neutral-700">Back</a>
        </div>
    </div>

    {{-- PDF-like Wrapper --}}
    <div class="print-wrap">
        <style>
            /* ===== A4 sheet sizing (screen + print identical) ===== */
            :root{
                --a4-w: 210mm;     /* width  */
                --a4-h: 297mm;     /* height */
                --sheet-pad: 12mm; /* inner padding of the sheet */
            }

            * { box-sizing: border-box; }
            body { background: #f3f4f6; } /* light gray on screen */
            .no-print { display: block; }

            /* A4 sheet on screen */
            .print-page{
                width: var(--a4-w);
                min-height: var(--a4-h);
                margin: 0 auto 20px auto;
                background: #fff;
                color:#111;
                font-family: DejaVu Sans, Arial, sans-serif;
                font-size: 12px;
                padding: var(--sheet-pad);
                box-shadow: 0 4px 24px rgba(0,0,0,.12);
                position: relative;
            }

            /* General visuals */
            .row:after { content:""; display:block; clear:both; }
            .text-right { text-align:right; } .text-center { text-align:center; }
            .muted { color:#555; }
            h1,h2,h3,h4 { margin:0 0 6px 0; }
            table { width:100%; border-collapse: collapse; }
            th, td { padding: 8px; border:1px solid #ddd; vertical-align: top; }
            thead th { background:#f2f2f2; }
            .no-border td, .no-border th { border:0 !important; }
            .mb-10{ margin-bottom:10px; } .mb-6{ margin-bottom:6px; } .mt-10{ margin-top:10px; }
            .brand { display:flex; align-items:center; gap:10px; }
            .brand-logo { height: 50px; width:auto; }
            .sign-img { height: 50px; width:auto; }
            .totals td { padding:6px 8px; }

            /* PRINT: keep the exact same sheet size + padding */
            @media print {
                @page {
                    size: A4;
                    margin: 0; /* important: no outer page margin so the sheet padding stays identical */
                }
                html, body { background: #fff; }
                .no-print { display: none !important; }

                /* Keep the same sheet box on print */
                .print-wrap{
                    width: var(--a4-w);
                    margin: 0 auto;
                }
                .print-page{
                    width: var(--a4-w);
                    min-height: var(--a4-h);
                    margin: 0 auto;     /* center on page */
                    padding: var(--sheet-pad);
                    box-shadow: none;    /* no shadow on paper */
                    background:#fff;
                }

                /* Keep table headers on each page */
                thead { display: table-header-group; }
                tfoot { display: table-footer-group; }
                tr, img { page-break-inside: avoid; }

                @media print {
                    @page { size: A4; margin: 0; }          /* A4, outer margin 0 */
                    body * { visibility: hidden !important; }
                    .print-page, .print-page * {
                        visibility: visible !important;
                    }
                    .print-page {
                        position: absolute;
                        left: 0; top: 0;
                        width: 210mm;               /* same as on screen */
                        min-height: 297mm;
                        padding: 12mm;              /* jo padding screen par hai wohi */
                        background: #fff;
                        box-shadow: none !important;
                    }
                    .no-print { display: none !important; }  /* action bar wagaira hide */
                }
            }

            /* Optional utility for manual breaks */
            .page-break { page-break-after: always; }
        </style>

        <div class="print-page">
            {{-- Header: Logo + Business + Invoice Meta --}}
            <table class="no-border" style="margin-bottom:8px;">
                <tr class="no-border">
                    <td class="no-border" style="vertical-align:top;">
                        <div class="brand">
                            @if(!empty($logo))
                                <img class="brand-logo" src="{{ $logo }}" alt="Logo">
                            @endif
                            <div>
                                <h2>{{ $b->name }}</h2>
                                <div class="muted">{{ $b->address }}</div>
                                <div class="muted">Email: {{ $b->email }} | Phone: {{ $b->mobile }}</div>
                                @if($b->gstin)<div class="muted">GSTIN: {{ $b->gstin }}</div>@endif
                            </div>
                        </div>
                    </td>
                    <td class="no-border text-right" style="vertical-align:top;">
                        <h2>INVOICE</h2>
                        <div>Invoice No: <strong>{{ $inv->invoice_number }}</strong></div>
                        <div>Date: <strong>{{ \Illuminate\Support\Carbon::parse($inv->invoice_date)->format('d M Y') }}</strong></div>
                        @if($inv->due_date)
                            <div>Due: <strong>{{ \Illuminate\Support\Carbon::parse($inv->due_date)->format('d M Y') }}</strong></div>
                        @endif
                    </td>
                </tr>
            </table>

            {{-- Bill To + Summary --}}
            <table class="no-border mb-10">
                <tr>
                    <td style="width:55%; vertical-align:top;">
                        <h4>Bill To</h4>
                        <div><strong>{{ $c->name }}</strong></div>
                        <div class="muted">{{ $c->address }}</div>
                        <div class="muted">Mobile: {{ $c->mobile }}</div>
                        @if($c->gstin)<div class="muted">GSTIN: {{ $c->gstin }}</div>@endif
                    </td>
                    <td style="width:45%; vertical-align:top;">
                        <h4>Details</h4>
                        <div class="muted">Subtotal: ₹ {{ number_format($inv->subtotal,2) }}</div>
                        <div class="muted">Tax: ₹ {{ number_format($inv->tax_amount,2) }}</div>

                        {{-- Optional: Discount / Charges / TCS / Round Off --}}
                        @if(($inv->discount_total ?? 0) > 0)
                            <div class="muted">Discount: - ₹ {{ number_format($inv->discount_total,2) }}</div>
                        @endif
                        @if(isset($charges) && $charges->count())
                            <div class="muted">Additional Charges:</div>
                            <ul style="margin:0 0 6px 16px; padding:0;">
                                @foreach($charges as $ch)
                                    <li class="muted">{{ $ch->name }} — ₹ {{ number_format($ch->amount,2) }}</li>
                                @endforeach
                            </ul>
                        @endif
                        @if(($inv->tcs_percent ?? 0) > 0)
                            <div class="muted">TCS ({{ number_format($inv->tcs_percent,2) }}%): ₹ {{ number_format($inv->tcs_amount,2) }}</div>
                        @endif
                        @if(($inv->round_off ?? 0) != 0)
                            <div class="muted">Round Off: ₹ {{ number_format($inv->round_off,2) }}</div>
                        @endif

                        <div class="muted">Total: ₹ {{ number_format($inv->total,2) }}</div>
                        <div class="muted">Received: ₹ {{ number_format($inv->received_amount,2) }}</div>
                        <div class="muted">Balance: ₹ {{ number_format($inv->balance,2) }}</div>
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
                @foreach($inv->items as $i => $it)
                    <tr>
                        <td class="text-center">{{ $i+1 }}</td>
                        <td>{{ $it->description }}</td>
                        <td class="text-center">{{ $it->sac_code }}</td>
                        <td class="text-center">{{ $it->quantity }}</td>
                        <td class="text-right">₹ {{ number_format($it->rate,2) }}</td>
                        <td class="text-right">₹ {{ number_format($it->making_charge,2) }}</td>
                        <td class="text-center">{{ number_format($it->tax_percent,2) }}</td>
                        <td class="text-right">₹ {{ number_format($it->amount,2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{-- Notes + Totals + Signature --}}
            <table class="no-border" style="margin-top:10px;">
                <tr>
                    <td class="no-border" style="width:55%; vertical-align:top;">
                        @if($inv->notes)
                            <div class="mb-6"><strong>Notes</strong></div>
                            <div class="muted" style="white-space:pre-wrap;">{{ $inv->notes }}</div>
                        @endif

                        @if($inv->terms)
                            <div class="mt-10 mb-6"><strong>Terms & Conditions</strong></div>
                            <div class="muted" style="white-space:pre-wrap;">{{ $inv->terms }}</div>
                        @endif
                    </td>
                    <td class="no-border" style="width:45%; vertical-align:top;">
                        <table class="totals" style="width:100%; margin-bottom:20px;">
                            <tr><td class="text-right"><strong>Subtotal</strong></td><td class="text-right">₹ {{ number_format($inv->subtotal,2) }}</td></tr>
                            <tr><td class="text-right"><strong>Tax</strong></td><td class="text-right">₹ {{ number_format($inv->tax_amount,2) }}</td></tr>
                            @if(($inv->discount_total ?? 0) > 0)
                                <tr><td class="text-right">Discount</td><td class="text-right">- ₹ {{ number_format($inv->discount_total,2) }}</td></tr>
                            @endif
                            @if(isset($charges) && $charges->count())
                                @foreach($charges as $ch)
                                    <tr><td class="text-right">{{ $ch->name }}</td><td class="text-right">₹ {{ number_format($ch->amount,2) }}</td></tr>
                                @endforeach
                            @endif
                            @if(($inv->tcs_percent ?? 0) > 0)
                                <tr><td class="text-right">TCS ({{ number_format($inv->tcs_percent,2) }}%)</td><td class="text-right">₹ {{ number_format($inv->tcs_amount,2) }}</td></tr>
                            @endif
                            @if(($inv->round_off ?? 0) != 0)
                                <tr><td class="text-right">Round Off</td><td class="text-right">₹ {{ number_format($inv->round_off,2) }}</td></tr>
                            @endif
                            <tr><td class="text-right"><strong>Total</strong></td><td class="text-right"><strong>₹ {{ number_format($inv->total,2) }}</strong></td></tr>
                            <tr><td class="text-right">Received</td><td class="text-right">₹ {{ number_format($inv->received_amount,2) }}</td></tr>
                            <tr><td class="text-right"><strong>Balance</strong></td><td class="text-right"><strong>₹ {{ number_format($inv->balance,2) }}</strong></td></tr>
                        </table>

                        <div class="text-right">
                            @if(!empty($sign))
                                <img class="sign-img" src="{{ $sign }}" alt="Signature">
                            @endif
                            <div style="border-top:1px solid #999; margin-top:6px; padding-top:4px;">Authorized Signatory</div>
                            <div class="muted">{{ $b->name }}</div>
                        </div>
                    </td>
                </tr>
            </table>

            <p class="text-center muted" style="margin-top:20px;">Thank you for your business!</p>
        </div>
    </div>
</x-layouts.app>
