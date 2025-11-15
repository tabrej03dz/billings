@php
    $b = $inv->business;
    $c = $inv->client;

    $fmt = fn($v) => number_format((float)$v, 2);
    $dateFmt = fn($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('d M Y') : '';
@endphp

<x-layouts.app :title="__('Invoice '.$inv->invoice_number)">
    {{-- Action Bar (screen पर दिखे, print में hide) --}}
    <div class="mb-3 no-print" style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100">
            Invoice #{{ $inv->invoice_number }}
        </h1>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('invoices.download',$inv->id) }}"
               class="px-3 py-2 rounded bg-emerald-600 text-white">
                Download PDF
            </a>
            <button onclick="window.print()"
                    class="px-3 py-2 rounded bg-gray-800 text-white">
                Print
            </button>
            <a href="{{ route('invoices.index') }}"
               class="px-3 py-2 rounded border border-gray-300 dark:border-neutral-700">
                Back
            </a>
        </div>
    </div>

    {{-- PDF-like Wrapper --}}
    <div class="print-wrap">
        <style>
            :root{
                --a4-w: 210mm;
                --a4-h: 297mm;
                --sheet-pad: 12mm;
            }

            * { box-sizing: border-box; }
            body { background: #f3f4f6; }
            .no-print { display: block; }

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

            .text-right { text-align:right; }
            .text-center { text-align:center; }
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
            .small-text { font-size:10px; }

            @media print {
                @page {
                    size: A4;
                    margin: 0;
                }
                html, body { background: #fff; }
                .no-print { display:none !important; }

                .print-wrap{
                    width: var(--a4-w);
                    margin:0 auto;
                }
                .print-page{
                    width: var(--a4-w);
                    min-height: var(--a4-h);
                    margin:0 auto;
                    padding: var(--sheet-pad);
                    box-shadow:none;
                    background:#fff;
                }

                thead { display: table-header-group; }
                tfoot { display: table-footer-group; }
                tr, img { page-break-inside: avoid; }

                body * { visibility: hidden !important; }
                .print-page, .print-page * {
                    visibility: visible !important;
                }
                .print-page {
                    position:absolute;
                    left:0; top:0;
                }
            }

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
                                @if($b->address)
                                    <div class="muted">{{ $b->address }}</div>
                                @endif
                                <div class="muted">
                                    @if($b->email) Email: {{ $b->email }} @endif
                                    @if($b->email && $b->mobile) | @endif
                                    @if($b->mobile) Phone: {{ $b->mobile }} @endif
                                </div>
                                @if($b->gstin)
                                    <div class="muted">GSTIN: {{ $b->gstin }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="no-border text-right" style="vertical-align:top;">
                        <h2>INVOICE</h2>
                        <div>Invoice No: <strong>{{ $inv->invoice_number }}</strong></div>
                        <div>Date: <strong>{{ $dateFmt($inv->invoice_date) }}</strong></div>
                        @if($inv->due_date)
                            <div>Due: <strong>{{ $dateFmt($inv->due_date) }}</strong></div>
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
                        @if($c->address)<div class="muted">{{ $c->address }}</div>@endif
                        @if($c->mobile)<div class="muted">Mobile: {{ $c->mobile }}</div>@endif
                        @if($c->gstin)<div class="muted">GSTIN: {{ $c->gstin }}</div>@endif
                    </td>
                    <td style="width:45%; vertical-align:top;">
                        <h4>Details</h4>
                        <div class="muted">Subtotal: ₹ {{ $fmt($subtotal) }}</div>
                        <div class="muted">Tax: ₹ {{ $fmt($taxTotal) }}</div>

                        @if(($discountTotal ?? 0) > 0)
                            <div class="muted">Discount: - ₹ {{ $fmt($discountTotal) }}</div>
                        @endif

                        @if(($chargesTotal ?? 0) > 0)
                            <div class="muted">Additional Charges: ₹ {{ $fmt($chargesTotal) }}</div>
                        @endif

                        @if(($tcsPercent ?? 0) > 0)
                            <div class="muted">
                                TCS ({{ $fmt($tcsPercent) }}%): ₹ {{ $fmt($tcsAmount) }}
                            </div>
                        @endif

                        @if(($roundOff ?? 0) != 0)
                            <div class="muted">
                                Round Off:
                                {{ ($roundOff >= 0 ? '+' : '−') }} ₹ {{ $fmt(abs($roundOff)) }}
                            </div>
                        @endif

                        <div class="muted">
                            Total: <strong>₹ {{ $fmt($grandTotal) }}</strong>
                        </div>
                        <div class="muted">Received: ₹ {{ $fmt($received) }}</div>
                        <div class="muted">
                            Balance: <strong>₹ {{ $fmt($balance) }}</strong>
                        </div>
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
                    @php
                        $qty    = (float)($it->quantity ?? 0);
                        $rate   = (float)($it->rate ?? 0);
                        $mk     = (float)($it->making_charge ?? 0);
                        $stone  = (float)($it->stone_charges ?? 0);
                        $disc   = (float)($it->discount ?? 0);
                        $tp     = (float)($it->tax_percent ?? 0);

                        $basePerUnit = $rate + $mk + $stone;
                        $base        = max(0, $qty * $basePerUnit - $disc);
                        $tax         = $base * ($tp/100);

                        $amount = $it->amount ?? ($base + $tax);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $i+1 }}</td>
                        <td>
                            {{ $it->description }}

                            {{-- Metal info --}}
                            @if($it->metal_type || $it->purity || $it->metal_weight || $it->metal_rate || $it->stone_charges)
                                <div class="muted small-text">
                                    @if($it->metal_type)
                                        {{ strtoupper($it->metal_type) }}
                                    @endif
                                    @if($it->purity)
                                        • {{ $it->purity }}
                                    @endif
                                    @if($it->metal_weight)
                                        • Wt: {{ $fmt($it->metal_weight) }} g
                                    @endif
                                    @if($it->metal_rate)
                                        • Rate: ₹ {{ $fmt($it->metal_rate) }}/g
                                    @endif
                                    @if($it->stone_charges)
                                        • Stone: ₹ {{ $fmt($it->stone_charges) }}
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="text-center">{{ $it->sac_code }}</td>
                        <td class="text-center">{{ $fmt($qty) }}</td>
                        <td class="text-right">₹ {{ $fmt($rate) }}</td>
                        <td class="text-right">₹ {{ $fmt($mk) }}</td>
                        <td class="text-center">{{ $fmt($tp) }}</td>
                        <td class="text-right">₹ {{ $fmt($amount) }}</td>
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
                            <div class="mt-10 mb-6"><strong>Terms &amp; Conditions</strong></div>
                            <div class="muted" style="white-space:pre-wrap;">{{ $inv->terms }}</div>
                        @endif
                    </td>
                    <td class="no-border" style="width:45%; vertical-align:top;">
                        <table class="totals" style="width:100%; margin-bottom:20px;">
                            <tr>
                                <td class="text-right"><strong>Subtotal</strong></td>
                                <td class="text-right">₹ {{ $fmt($subtotal) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right"><strong>Tax</strong></td>
                                <td class="text-right">₹ {{ $fmt($taxTotal) }}</td>
                            </tr>
                            @if(($discountTotal ?? 0) > 0)
                                <tr>
                                    <td class="text-right">Discount</td>
                                    <td class="text-right">- ₹ {{ $fmt($discountTotal) }}</td>
                                </tr>
                            @endif
                            @if(($chargesTotal ?? 0) > 0)
                                <tr>
                                    <td class="text-right">Additional Charges</td>
                                    <td class="text-right">₹ {{ $fmt($chargesTotal) }}</td>
                                </tr>
                            @endif
                            @if(($tcsPercent ?? 0) > 0)
                                <tr>
                                    <td class="text-right">
                                        TCS ({{ $fmt($tcsPercent) }}%)
                                    </td>
                                    <td class="text-right">₹ {{ $fmt($tcsAmount) }}</td>
                                </tr>
                            @endif
                            @if(($roundOff ?? 0) != 0)
                                <tr>
                                    <td class="text-right">Round Off</td>
                                    <td class="text-right">
                                        {{ ($roundOff >= 0 ? '+' : '−') }} ₹ {{ $fmt(abs($roundOff)) }}
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-right"><strong>Total</strong></td>
                                <td class="text-right"><strong>₹ {{ $fmt($grandTotal) }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-right">Received</td>
                                <td class="text-right">₹ {{ $fmt($received) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right"><strong>Balance</strong></td>
                                <td class="text-right"><strong>₹ {{ $fmt($balance) }}</strong></td>
                            </tr>
                        </table>

                        <div class="text-right">
                            @if(!empty($sign))
                                <img class="sign-img" src="{{ $sign }}" alt="Signature">
                            @endif
                            <div style="border-top:1px solid #999; margin-top:6px; padding-top:4px;">
                                Authorized Signatory
                            </div>
                            <div class="muted">{{ $b->name }}</div>
                        </div>
                    </td>
                </tr>
            </table>

            <p class="text-center muted" style="margin-top:20px;">Thank you for your business!</p>
        </div>
    </div>
</x-layouts.app>
