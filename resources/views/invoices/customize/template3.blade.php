@php
    $primaryColor   = $setting->primary_color ?? '#1d4ed8';
    $textColor      = $setting->text_color ?? '#1e293b';
    $mutedColor     = $setting->muted_color ?? '#475569';
    $borderColor    = $setting->border_color ?? '#cbd5e1';
    $secondaryColor = $setting->secondary_color ?? '#e2e8f0';
    $lightBgColor   = $setting->light_bg_color ?? '#eff6ff';
    $softBgColor    = $setting->soft_bg_color ?? '#ffffff';
    $fontFamily     = $setting->font_family ?? 'DejaVu Sans';

    $showLogo      = $setting->show_logo ?? true;
    $showSignature = $setting->show_signature ?? true;
    $showTerms     = $setting->show_terms ?? true;

    $templateId = $billTemplate->id ?? $template->id;
    $templateName = $billTemplate->name ?? $template->name ?? 'Bill Template';
@endphp

<x-layouts.app :title="__('Customize Bill Template')">

<form method="POST" action="{{ route('bill-template.customize.save', $templateId) }}">
    @csrf

    <input type="hidden" name="template_id" value="{{ $templateId }}">

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">

        <div class="bg-white dark:bg-neutral-900 rounded-xl shadow p-5 space-y-5 h-fit">

            <div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                    Customize Template
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $templateName }}
                </p>
            </div>

            @if(session('success'))
                <div class="p-3 rounded bg-green-50 text-green-700 border border-green-200 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-3 rounded bg-red-50 text-red-700 border border-red-200 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-3 rounded bg-red-50 text-red-700 border border-red-200 text-sm">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label class="text-sm font-semibold">Primary Blue Color</label>
                <input type="color" name="primary_color" id="primary_color" value="{{ $primaryColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Text Color</label>
                <input type="color" name="text_color" id="text_color" value="{{ $textColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Muted Text Color</label>
                <input type="color" name="muted_color" id="muted_color" value="{{ $mutedColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Card Border Color</label>
                <input type="color" name="border_color" id="border_color" value="{{ $borderColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Inner Line Color</label>
                <input type="color" name="secondary_color" id="secondary_color" value="{{ $secondaryColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Table Header Background</label>
                <input type="color" name="light_bg_color" id="light_bg_color" value="{{ $lightBgColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Page Background</label>
                <input type="color" name="soft_bg_color" id="soft_bg_color" value="{{ $softBgColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Font Family</label>
                <select name="font_family" id="font_family"
                        class="w-full border rounded-lg mt-2 px-3 py-2 dark:bg-neutral-800 dark:text-white">
                    <option value="DejaVu Sans" @selected($fontFamily == 'DejaVu Sans')>DejaVu Sans</option>
                    <option value="Arial" @selected($fontFamily == 'Arial')>Arial</option>
                    <option value="Times New Roman" @selected($fontFamily == 'Times New Roman')>Times New Roman</option>
                    <option value="Courier New" @selected($fontFamily == 'Courier New')>Courier New</option>
                </select>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="show_logo" id="show_logo" value="1" @checked($showLogo)>
                Show Logo
            </label>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="show_signature" id="show_signature" value="1" @checked($showSignature)>
                Show Signature
            </label>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="show_terms" id="show_terms" value="1" @checked($showTerms)>
                Show Terms
            </label>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-lg font-semibold">
                Save Customization
            </button>

            <button type="submit"
                    formaction="{{ route('bill-template.customize.reset', $templateId) }}"
                    formmethod="POST"
                    onclick="return confirm('Are you sure you want to reset this template customization?')"
                    class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-lg font-semibold">
                Reset Default
            </button>

            <a href="{{ route('bill-templates.choose') }}"
               class="block text-center bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 rounded-lg font-semibold">
                Back
            </a>
        </div>

        <div class="xl:col-span-3 bg-gray-100 dark:bg-neutral-800 rounded-xl p-5 overflow-auto">

            <div id="invoicePreview"
                 class="mx-auto shadow-xl"
                 style="
                    width:794px;
                    min-height:1123px;
                    padding:18px;
                    font-family:'{{ $fontFamily }}', 'DejaVu Sans', sans-serif;
                    font-size:12px;
                    color:{{ $textColor }};
                    background:{{ $softBgColor }};
                 ">

                <div id="heroBox"
                     style="
                        background:{{ $primaryColor }};
                        color:#fff;
                        padding:16px;
                        border-radius:0 0 14px 14px;
                        margin-bottom:14px;
                     ">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="width:70%; vertical-align:top;">
                                <div style="font-size:24px; font-weight:700; color:#fff;">
                                    {{ $business->name ?? $business->business_name ?? 'Real Victory Groups' }}
                                </div>
                                <div>73 Basement, Ekta Enclave Society, Lakhanpur, Kanpur, Uttar Pradesh - 208024</div>
                                <div style="margin-top:5px;">Mobile: 7753800444 | GSTIN: 09ABCDE1234F1Z5</div>
                                <div>Email: info@realvictorygroups.com</div>
                            </td>

                            <td style="width:30%; text-align:right; vertical-align:top;">
                                <div style="font-size:24px; font-weight:700;">TAX</div>
                                <div>Invoice</div>

                                <div id="logoBox"
                                     style="width:100px; height:55px; border:1px solid rgba(255,255,255,.8); display:inline-flex; align-items:center; justify-content:center; font-size:11px; margin-top:8px;">
                                    LOGO
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div style="display:block; clear:both;">
                    <div style="float:left; width:54%;">
                        <div class="preview-card">
                            <div class="preview-card-title">Bill To</div>
                            <strong>DEMO CUSTOMER</strong><br>
                            <span class="preview-muted">Sample Market Road, Lucknow, Uttar Pradesh - 226001</span>
                            <div style="margin-top:6px;">
                                Mobile: 9876543210<br>
                                GSTIN: 09XYZDE1234F1Z2<br>
                                PAN: ABCDE1234F<br>
                                Place of Supply: Uttar Pradesh
                            </div>
                        </div>
                    </div>

                    <div style="float:right; width:42%;">
                        <div class="preview-card">
                            <div class="preview-card-title">Invoice Info</div>
                            <strong>No:</strong> INV-0001<br>
                            <strong>Date:</strong> {{ now()->format('d/m/Y') }}<br>
                            <strong>Status:</strong> Tax
                        </div>
                    </div>

                    <div style="clear:both;"></div>
                </div>

                <table id="itemsTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                    <tr>
                        <th style="width:35%;">Item</th>
                        <th style="width:12%;">SAC</th>
                        <th style="width:10%;">Qty</th>
                        <th style="width:12%; text-align:right;">Rate</th>
                        <th style="width:12%; text-align:right;">Tax</th>
                        <th style="width:19%; text-align:right;">Amount</th>
                    </tr>
                    </thead>

                    <tbody>
                    <tr>
                        <td>
                            <strong>Social Media Creative Package</strong>
                            <div class="preview-desc">Monthly creative design package</div>
                        </td>
                        <td>998361</td>
                        <td>1</td>
                        <td style="text-align:right;">10000.00</td>
                        <td style="text-align:right;">1800.00</td>
                        <td style="text-align:right;">11800.00</td>
                    </tr>
                    </tbody>
                </table>

                <table id="summaryBox"
                       style="
                            width:45%;
                            margin-left:auto;
                            margin-top:14px;
                            border:1px solid {{ $borderColor }};
                            border-radius:10px;
                            overflow:hidden;
                            border-collapse:collapse;
                       ">
                    <tr><td>Taxable Amount</td><td style="text-align:right;">₹ 10000.00</td></tr>
                    <tr><td>CGST</td><td style="text-align:right;">₹ 900.00</td></tr>
                    <tr><td>SGST</td><td style="text-align:right;">₹ 900.00</td></tr>
                    <tr><td>Received</td><td style="text-align:right;">₹ 5000.00</td></tr>
                    <tr><td>Balance</td><td style="text-align:right;">₹ 6800.00</td></tr>
                    <tr class="summary-total"><td>Total Amount</td><td style="text-align:right;">₹ 11800.00</td></tr>
                </table>

                <div class="preview-card" style="margin-top:14px;">
                    <div class="preview-card-title">Amount in Words</div>
                    Eleven Thousand Eight Hundred Rupees
                </div>

                <div style="display:block; clear:both;">
                    <div id="termsBox" style="float:left; width:54%;">
                        <div class="preview-card">
                            <div class="preview-card-title">Terms & Conditions</div>
                            <span class="preview-muted">
                                Payment due within 7 days.<br>
                                Goods once sold will not be returned.
                            </span>
                        </div>
                    </div>

                    <div id="signatureBox" style="float:right; width:42%; text-align:right;">
                        <div style="height:48px;">Signature</div>
                        <strong>Authorised Signatory</strong><br>
                        <span class="preview-muted">
                            {{ $business->name ?? $business->business_name ?? 'Real Victory Groups' }}
                        </span>
                    </div>

                    <div style="clear:both;"></div>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    .preview-card{
        border:1px solid {{ $borderColor }};
        border-radius:10px;
        padding:12px;
        margin-bottom:14px;
        background:#fff;
    }

    .preview-card-title{
        color:{{ $primaryColor }};
        font-size:13px;
        font-weight:700;
        margin-bottom:8px;
        text-transform:uppercase;
    }

    .preview-muted{
        color:{{ $mutedColor }};
    }

    .preview-desc{
        font-size:10px;
        color:{{ $mutedColor }};
    }

    #itemsTable th{
        background:{{ $lightBgColor }};
        color:{{ $primaryColor }};
        border-bottom:2px solid {{ $primaryColor }};
        padding:8px;
        text-align:left;
        font-size:11px;
    }

    #itemsTable td{
        border-bottom:1px solid {{ $secondaryColor }};
        padding:8px;
        vertical-align:top;
    }

    #summaryBox td{
        padding:8px 10px;
        border-bottom:1px solid {{ $secondaryColor }};
    }

    #summaryBox .summary-total td{
        background:{{ $primaryColor }};
        color:#fff;
        font-weight:700;
    }
</style>

<script>
    const primaryColor = document.getElementById('primary_color');
    const textColor = document.getElementById('text_color');
    const mutedColor = document.getElementById('muted_color');
    const borderColor = document.getElementById('border_color');
    const secondaryColor = document.getElementById('secondary_color');
    const lightBgColor = document.getElementById('light_bg_color');
    const softBgColor = document.getElementById('soft_bg_color');
    const fontFamily = document.getElementById('font_family');

    const preview = document.getElementById('invoicePreview');
    const heroBox = document.getElementById('heroBox');
    const itemsTable = document.getElementById('itemsTable');
    const summaryBox = document.getElementById('summaryBox');

    function updatePreview() {
        preview.style.color = textColor.value;
        preview.style.backgroundColor = softBgColor.value;
        preview.style.fontFamily = `'${fontFamily.value}', 'DejaVu Sans', sans-serif`;

        heroBox.style.backgroundColor = primaryColor.value;

        document.querySelectorAll('.preview-muted, .preview-desc').forEach(el => {
            el.style.color = mutedColor.value;
        });

        document.querySelectorAll('.preview-card').forEach(el => {
            el.style.border = `1px solid ${borderColor.value}`;
        });

        document.querySelectorAll('.preview-card-title').forEach(el => {
            el.style.color = primaryColor.value;
        });

        itemsTable.querySelectorAll('thead th').forEach(th => {
            th.style.backgroundColor = lightBgColor.value;
            th.style.color = primaryColor.value;
            th.style.borderBottom = `2px solid ${primaryColor.value}`;
        });

        itemsTable.querySelectorAll('tbody td').forEach(td => {
            td.style.borderBottom = `1px solid ${secondaryColor.value}`;
        });

        summaryBox.style.border = `1px solid ${borderColor.value}`;

        summaryBox.querySelectorAll('td').forEach(td => {
            td.style.borderBottom = `1px solid ${secondaryColor.value}`;
        });

        summaryBox.querySelectorAll('.summary-total td').forEach(td => {
            td.style.backgroundColor = primaryColor.value;
            td.style.color = '#ffffff';
        });

        document.getElementById('logoBox').style.display =
            document.getElementById('show_logo').checked ? 'inline-flex' : 'none';

        document.getElementById('signatureBox').style.display =
            document.getElementById('show_signature').checked ? 'block' : 'none';

        document.getElementById('termsBox').style.display =
            document.getElementById('show_terms').checked ? 'block' : 'none';
    }

    document.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });

    updatePreview();
</script>

</x-layouts.app>