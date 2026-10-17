@php
    $primaryColor   = $setting->primary_color ?? '#1c1917';
    $textColor      = $setting->text_color ?? '#2b2b2b';
    $mutedColor     = $setting->muted_color ?? '#7c6f64';
    $borderColor    = $setting->border_color ?? '#d6c8b4';
    $secondaryColor = $setting->secondary_color ?? '#f4ead7';
    $lightBgColor   = $setting->light_bg_color ?? '#fffdf9';
    $softBgColor    = $setting->soft_bg_color ?? '#faf7f2';
    $accentColor    = $setting->accent_color ?? '#c9a227';
    $fontFamily     = $setting->font_family ?? 'DejaVu Sans';

    $showLogo      = (bool) ($setting->show_logo ?? true);
    $showSignature = (bool) ($setting->show_signature ?? true);
    $showTerms     = (bool) ($setting->show_terms ?? true);

    $templateId   = $billTemplate->id ?? $template->id;
    $templateName = $billTemplate->name ?? $template->name ?? 'Premium Gold Invoice Template';
@endphp

<x-layouts.app :title="__('Customize Bill Template')">

<form method="POST" action="{{ route('bill-template.customize.save', $templateId) }}">
    @csrf
    <input type="hidden" name="template_id" value="{{ $templateId }}">

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-neutral-900 rounded-xl shadow p-5 space-y-5 h-fit">
            <div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">Customize Template</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $templateName }}</p>
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
                <label class="text-sm font-semibold">Hero / Total Color</label>
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
                <label class="text-sm font-semibold">Border Color</label>
                <input type="color" name="border_color" id="border_color" value="{{ $borderColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Table Header BG</label>
                <input type="color" name="secondary_color" id="secondary_color" value="{{ $secondaryColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Panel Background</label>
                <input type="color" name="light_bg_color" id="light_bg_color" value="{{ $lightBgColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Page Background</label>
                <input type="color" name="soft_bg_color" id="soft_bg_color" value="{{ $softBgColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Gold / Accent Line Color</label>
                <input type="color" name="accent_color" id="accent_color" value="{{ $accentColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Font Family</label>
                <select name="font_family" id="font_family" class="w-full border rounded-lg mt-2 px-3 py-2 dark:bg-neutral-800 dark:text-white">
                    <option value="DejaVu Sans" @selected($fontFamily == 'DejaVu Sans')>DejaVu Sans</option>
                    <option value="Arial" @selected($fontFamily == 'Arial')>Arial</option>
                    <option value="Times New Roman" @selected($fontFamily == 'Times New Roman')>Times New Roman</option>
                    <option value="Courier New" @selected($fontFamily == 'Courier New')>Courier New</option>
                </select>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="show_logo" id="show_logo" value="1" @checked($showLogo)> Show Logo
            </label>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="show_signature" id="show_signature" value="1" @checked($showSignature)> Show Signature
            </label>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="show_terms" id="show_terms" value="1" @checked($showTerms)> Show Terms
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

            <a href="{{ route('bill-templates.choose') }}" class="block text-center bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 rounded-lg font-semibold">
                Back
            </a>
        </div>

        <div class="xl:col-span-3 bg-gray-100 dark:bg-neutral-800 rounded-xl p-5 overflow-auto">
            <div id="invoicePreview"
                 style="width:794px; min-height:1123px; margin:auto; padding:18px; background:{{ $softBgColor }}; font-family:'{{ $fontFamily }}', DejaVu Sans, sans-serif; font-size:12px; color:{{ $textColor }};">

                <div id="pageBox" style="background:#fff; border:1px solid {{ $borderColor }}; padding:18px;">
                    <div id="heroBox" style="background:{{ $primaryColor }}; color:#fff; padding:18px; margin:-18px -18px 18px -18px;">
                        <div style="display:flex; justify-content:space-between; gap:20px;">
                            <div style="width:65%;">
                                <div class="heroText" style="font-size:24px; font-weight:700; color:#fff;">Real Victory Groups</div>
                                <div class="heroText">73 Basement, Ekta Enclave Society, Kanpur, Uttar Pradesh - 208024</div>
                                <div class="heroText" style="margin-top:5px;">Mobile: 7753800444 | GSTIN: 09ABCDE1234F1Z5</div>
                                <div class="heroText">Email: info@realvictorygroups.com</div>
                            </div>
                            <div style="width:30%; text-align:right;">
                                <div class="heroText" style="font-size:28px; font-weight:700; color:#fff;">TAX</div>
                                <div class="heroText">Invoice</div>
                                <div id="logoBox" style="display:{{ $showLogo ? 'inline-block' : 'none' }}; margin-top:8px; border:1px dashed #fff; padding:14px 18px; color:#fff;">LOGO</div>
                            </div>
                        </div>
                    </div>

                    <div id="goldLine" style="height:3px; background:{{ $accentColor }}; margin:14px 0 18px 0;"></div>

                    <div class="panelBox" style="border:1px solid {{ $borderColor }}; padding:14px; margin-bottom:14px; background:{{ $lightBgColor }};">
                        <table style="width:100%; border-collapse:collapse;">
                            <tr>
                                <td style="width:60%; vertical-align:top;">
                                    <div class="panelTitle">Bill To</div>
                                    <strong>DEMO CUSTOMER</strong><br>
                                    Sample Market Road, Lucknow, Uttar Pradesh - 226001
                                    <div style="margin-top:8px;">
                                        Mobile: 9876543210<br>
                                        GSTIN: 09XYZDE1234F1Z2<br>
                                        PAN: ABCDE1234F<br>
                                        POS: Uttar Pradesh
                                    </div>
                                </td>
                                <td style="width:40%; vertical-align:top;">
                                    <div class="panelTitle">Invoice Details</div>
                                    <strong>No:</strong> INV-0001<br><br>
                                    <strong>Date:</strong> {{ now()->format('d/m/Y') }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <table id="itemsTable" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="width:35%">Service</th>
                                <th style="width:12%">SAC</th>
                                <th style="width:10%">Qty</th>
                                <th style="width:13%; text-align:right;">Rate</th>
                                <th style="width:12%; text-align:right;">Tax</th>
                                <th style="width:18%; text-align:right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong>Social Media Creative Package</strong>
                                    <div class="desc">Monthly creative design package</div>
                                </td>
                                <td>998361</td>
                                <td>1</td>
                                <td style="text-align:right;">10000.00</td>
                                <td style="text-align:right;">1800.00</td>
                                <td style="text-align:right;">11800.00</td>
                            </tr>
                        </tbody>
                    </table>

                    <table id="summaryTable" style="width:44%; margin-left:auto; margin-top:16px; border-collapse:collapse;">
                        <tr><td>Taxable Amount</td><td style="text-align:right;">₹ 10000.00</td></tr>
                        <tr><td>CGST</td><td style="text-align:right;">₹ 900.00</td></tr>
                        <tr><td>SGST</td><td style="text-align:right;">₹ 900.00</td></tr>
                        <tr><td>Received</td><td style="text-align:right;">₹ 5000.00</td></tr>
                        <tr><td>Balance</td><td style="text-align:right;">₹ 6800.00</td></tr>
                        <tr class="total"><td>Total</td><td style="text-align:right;">₹ 11800.00</td></tr>
                    </table>

                    <div class="panelBox" style="border:1px solid {{ $borderColor }}; padding:14px; margin-top:18px; background:{{ $lightBgColor }};">
                        <div class="panelTitle">Amount in Words</div>
                        Eleven Thousand Eight Hundred Rupees Only
                    </div>

                    <div style="margin-top:18px; display:flex; justify-content:space-between; gap:20px;">
                        <div id="termsBox" class="panelBox" style="display:{{ $showTerms ? 'block' : 'none' }}; width:56%; border:1px solid {{ $borderColor }}; padding:14px; background:{{ $lightBgColor }};">
                            <div class="panelTitle">Terms & Conditions</div>
                            Payment due within 7 days.<br>
                            Goods once sold will not be returned.
                        </div>

                        <div id="signatureBox" style="display:{{ $showSignature ? 'block' : 'none' }}; width:38%; text-align:right;">
                            <div style="height:48px;">Signature</div>
                            <strong>Authorised Signatory</strong><br>
                            Real Victory Groups
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    .panelTitle {
        font-size:12px;
        font-weight:700;
        text-transform:uppercase;
        color:{{ $accentColor }};
        margin-bottom:8px;
    }

    #itemsTable th {
        background:{{ $secondaryColor }};
        color:{{ $accentColor }};
        border-top:2px solid {{ $accentColor }};
        border-bottom:2px solid {{ $accentColor }};
        padding:9px 8px;
        text-align:left;
    }

    #itemsTable td {
        border-bottom:1px solid {{ $borderColor }};
        padding:9px 8px;
        vertical-align:top;
    }

    .desc {
        font-size:10px;
        color:{{ $mutedColor }};
        margin-top:3px;
    }

    #summaryTable td {
        padding:9px 10px;
        border:1px solid {{ $borderColor }};
    }

    #summaryTable .total td {
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
    const accentColor = document.getElementById('accent_color');
    const fontFamily = document.getElementById('font_family');

    function updatePreview() {
        const preview = document.getElementById('invoicePreview');

        preview.style.backgroundColor = softBgColor.value;
        preview.style.color = textColor.value;
        preview.style.fontFamily = `'${fontFamily.value}', DejaVu Sans, sans-serif`;

        document.getElementById('heroBox').style.backgroundColor = primaryColor.value;
        document.getElementById('goldLine').style.backgroundColor = accentColor.value;
        document.getElementById('pageBox').style.border = `1px solid ${borderColor.value}`;

        document.querySelectorAll('.panelBox').forEach(el => {
            el.style.backgroundColor = lightBgColor.value;
            el.style.border = `1px solid ${borderColor.value}`;
        });

        document.querySelectorAll('.panelTitle').forEach(el => {
            el.style.color = accentColor.value;
        });

        document.querySelectorAll('.desc').forEach(el => {
            el.style.color = mutedColor.value;
        });

        document.querySelectorAll('#itemsTable th').forEach(th => {
            th.style.backgroundColor = secondaryColor.value;
            th.style.color = accentColor.value;
            th.style.borderTop = `2px solid ${accentColor.value}`;
            th.style.borderBottom = `2px solid ${accentColor.value}`;
        });

        document.querySelectorAll('#itemsTable td').forEach(td => {
            td.style.borderBottom = `1px solid ${borderColor.value}`;
        });

        document.querySelectorAll('#summaryTable td').forEach(td => {
            td.style.border = `1px solid ${borderColor.value}`;
        });

        document.querySelectorAll('#summaryTable .total td').forEach(td => {
            td.style.backgroundColor = primaryColor.value;
            td.style.color = '#fff';
        });

        document.getElementById('logoBox').style.display = document.getElementById('show_logo').checked ? 'inline-block' : 'none';
        document.getElementById('signatureBox').style.display = document.getElementById('show_signature').checked ? 'block' : 'none';
        document.getElementById('termsBox').style.display = document.getElementById('show_terms').checked ? 'block' : 'none';
    }

    document.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });

    updatePreview();
</script>

</x-layouts.app>
