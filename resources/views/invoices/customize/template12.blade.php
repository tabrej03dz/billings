@php
    $setting = $templateSetting ?? $setting ?? null;

    $primaryColor   = $setting->primary_color ?? '#ef4444';
    $textColor      = $setting->text_color ?? '#111111';
    $mutedColor     = $setting->muted_color ?? '#777777';
    $borderColor    = $setting->border_color ?? '#fecaca';
    $secondaryColor = $setting->secondary_color ?? '#fee2e2';
    $lightBgColor   = $setting->light_bg_color ?? '#ffffff';
    $softBgColor    = $setting->soft_bg_color ?? '#ffffff';
    $accentColor    = $setting->accent_color ?? '#991b1b';
    $fontFamily     = $setting->font_family ?? 'DejaVu Sans';

    $showLogo      = (bool) ($setting->show_logo ?? true);
    $showSignature = (bool) ($setting->show_signature ?? true);
    $showTerms     = (bool) ($setting->show_terms ?? true);

    $templateId   = $billTemplate->id ?? $template->id;
    $templateName = $billTemplate->name ?? $template->name ?? 'Red Step Invoice Template';
@endphp

<x-layouts.app :title="__('Customize Red Step Template')">

<form method="POST" action="{{ route('bill-template.customize.save', $templateId) }}">
    @csrf

    <input type="hidden" name="template_id" value="{{ $templateId }}">

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">

        {{-- LEFT CUSTOMIZATION PANEL --}}
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
                <label class="text-sm font-semibold">Main Red / Step Color</label>
                <input type="color" name="primary_color" id="primary_color" value="{{ $primaryColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Text Color</label>
                <input type="color" name="text_color" id="text_color" value="{{ $textColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Muted Description Color</label>
                <input type="color" name="muted_color" id="muted_color" value="{{ $mutedColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Border / Row Line Color</label>
                <input type="color" name="border_color" id="border_color" value="{{ $borderColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Table Header Background</label>
                <input type="color" name="secondary_color" id="secondary_color" value="{{ $secondaryColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Invoice Paper Background</label>
                <input type="color" name="light_bg_color" id="light_bg_color" value="{{ $lightBgColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Outer Page Background</label>
                <input type="color" name="soft_bg_color" id="soft_bg_color" value="{{ $softBgColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Table Header Text Color</label>
                <input type="color" name="accent_color" id="accent_color" value="{{ $accentColor }}" class="w-full h-11 mt-2">
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

            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-lg font-semibold">
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

        {{-- RIGHT LIVE PREVIEW --}}
        <div class="xl:col-span-3 bg-gray-100 dark:bg-neutral-800 rounded-xl p-5 overflow-auto">

            <div id="invoicePreview"
                 style="
                    width:794px;
                    min-height:1123px;
                    margin:auto;
                    padding:18px;
                    background:{{ $softBgColor }};
                    font-family:'{{ $fontFamily }}', DejaVu Sans, sans-serif;
                    font-size:12px;
                    color:{{ $textColor }};
                 ">

                <div id="pageBox"
                     style="
                        background:{{ $lightBgColor }};
                        border-left:10px solid {{ $primaryColor }};
                        padding:18px 22px;
                     ">

                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="vertical-align:top;">
                                <div id="logoBox"
                                     style="
                                        display:{{ $showLogo ? 'inline-block' : 'none' }};
                                        width:90px;
                                        height:55px;
                                        line-height:55px;
                                        text-align:center;
                                        border:1px dashed {{ $borderColor }};
                                        color:{{ $mutedColor }};
                                        margin-bottom:6px;
                                     ">
                                    LOGO
                                </div>

                                <div id="companyName"
                                     style="
                                        font-size:26px;
                                        font-weight:700;
                                        color:{{ $textColor }};
                                     ">
                                    Real Victory Groups
                                </div>

                                <div>
                                    73 Basement, Ekta Enclave Society, Kanpur, Uttar Pradesh<br>
                                    7753800444 | info@realvictorygroups.com
                                </div>
                            </td>

                            <td id="docType"
                                style="
                                    font-size:32px;
                                    font-weight:700;
                                    text-align:right;
                                    color:{{ $primaryColor }};
                                    vertical-align:top;
                                ">
                                TAX
                            </td>
                        </tr>
                    </table>

                    {{-- STEP 1 --}}
                    <div class="step">
                        <div class="dot"></div>
                        <div class="stepTitle">Invoice Details</div>
                        No: <strong>INV-0001</strong> &nbsp;&nbsp; Date: <strong>{{ now()->format('d/m/Y') }}</strong>
                    </div>

                    {{-- STEP 2 --}}
                    <div class="step">
                        <div class="dot"></div>
                        <div class="stepTitle">Customer Details</div>
                        <strong>DEMO CUSTOMER</strong><br>
                        Sample Market Road, Lucknow, Uttar Pradesh<br>
                        Mobile: 9876543210 | GSTIN: 09XYZDE1234F1Z2 | PAN: ABCDE1234F
                    </div>

                    {{-- STEP 3 --}}
                    <div class="step">
                        <div class="dot"></div>
                        <div class="stepTitle">Billing Items</div>

                        <table id="itemsTable" style="width:100%; border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>SAC</th>
                                    <th>Qty</th>
                                    <th style="text-align:right;">Rate</th>
                                    <th style="text-align:right;">Tax</th>
                                    <th style="text-align:right;">Amount</th>
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
                                    <td style="text-align:right;">₹ 11800.00</td>
                                </tr>

                                <tr>
                                    <td>
                                        <strong>Website Maintenance</strong>
                                        <div class="desc">Basic monthly support</div>
                                    </td>
                                    <td>998313</td>
                                    <td>1</td>
                                    <td style="text-align:right;">5000.00</td>
                                    <td style="text-align:right;">900.00</td>
                                    <td style="text-align:right;">₹ 5900.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- STEP 4 --}}
                    <div class="step">
                        <div class="dot"></div>
                        <div class="stepTitle">Final Summary</div>

                        <table id="summaryTable"
                               style="
                                    width:42%;
                                    margin-left:auto;
                                    border-collapse:collapse;
                               ">
                            <tr>
                                <td>Taxable Amount</td>
                                <td style="text-align:right;">₹ 15000.00</td>
                            </tr>
                            <tr>
                                <td>CGST</td>
                                <td style="text-align:right;">₹ 1350.00</td>
                            </tr>
                            <tr>
                                <td>SGST</td>
                                <td style="text-align:right;">₹ 1350.00</td>
                            </tr>
                            <tr>
                                <td>Received Amount</td>
                                <td style="text-align:right;">₹ 10000.00</td>
                            </tr>
                            <tr>
                                <td>Balance</td>
                                <td style="text-align:right;">₹ 7700.00</td>
                            </tr>
                            <tr class="grand">
                                <td>Total Amount</td>
                                <td style="text-align:right;">₹ 17700.00</td>
                            </tr>
                        </table>
                    </div>

                    {{-- STEP 5 --}}
                    <div class="step">
                        <div class="dot"></div>
                        <div class="stepTitle">Amount in Words</div>
                        Seventeen Thousand Seven Hundred Rupees Only
                    </div>

                    {{-- STEP 6 --}}
                    <div id="termsBox"
                         class="step"
                         style="display:{{ $showTerms ? 'block' : 'none' }};">
                        <div class="dot"></div>
                        <div class="stepTitle">Terms & Conditions</div>
                        Payment due within 7 days.<br>
                        Subject to Kanpur jurisdiction.
                    </div>

                    <div id="signatureBox"
                         style="
                            display:{{ $showSignature ? 'block' : 'none' }};
                            text-align:right;
                            margin-top:18px;
                         ">
                        <div style="height:48px; color:{{ $mutedColor }};">
                            Signature
                        </div>
                        <strong>Authorised Signatory</strong><br>
                        Real Victory Groups
                    </div>

                </div>
            </div>

        </div>
    </div>
</form>

<style>
    .step {
        position: relative;
        margin-bottom: 16px;
        padding-left: 28px;
    }

    .dot {
        position: absolute;
        left: 0;
        top: 2px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: {{ $primaryColor }};
    }

    .stepTitle {
        font-size: 11px;
        color: {{ $primaryColor }};
        text-transform: uppercase;
        font-weight: 700;
        margin-bottom: 5px;
    }

    #itemsTable th {
        background: {{ $secondaryColor }};
        color: {{ $accentColor }};
        padding: 9px;
        text-align: left;
        border-bottom: 2px solid {{ $primaryColor }};
    }

    #itemsTable td {
        padding: 9px;
        border-bottom: 1px solid {{ $borderColor }};
        vertical-align: top;
    }

    .desc {
        font-size: 10px;
        color: {{ $mutedColor }};
    }

    #summaryTable td {
        padding: 8px;
        border-bottom: 1px solid {{ $borderColor }};
    }

    #summaryTable .grand td {
        background: {{ $primaryColor }};
        color: #fff;
        font-weight: 700;
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

    const showLogo = document.getElementById('show_logo');
    const showSignature = document.getElementById('show_signature');
    const showTerms = document.getElementById('show_terms');

    function updatePreview() {
        const preview = document.getElementById('invoicePreview');
        const pageBox = document.getElementById('pageBox');

        preview.style.backgroundColor = softBgColor.value;
        preview.style.color = textColor.value;
        preview.style.fontFamily = `'${fontFamily.value}', DejaVu Sans, sans-serif`;

        pageBox.style.backgroundColor = lightBgColor.value;
        pageBox.style.borderLeft = `10px solid ${primaryColor.value}`;

        document.getElementById('docType').style.color = primaryColor.value;

        document.querySelectorAll('.dot').forEach(el => {
            el.style.backgroundColor = primaryColor.value;
        });

        document.querySelectorAll('.stepTitle').forEach(el => {
            el.style.color = primaryColor.value;
        });

        document.querySelectorAll('.desc').forEach(el => {
            el.style.color = mutedColor.value;
        });

        document.querySelectorAll('#itemsTable th').forEach(th => {
            th.style.backgroundColor = secondaryColor.value;
            th.style.color = accentColor.value;
            th.style.borderBottom = `2px solid ${primaryColor.value}`;
        });

        document.querySelectorAll('#itemsTable td').forEach(td => {
            td.style.borderBottom = `1px solid ${borderColor.value}`;
        });

        document.querySelectorAll('#summaryTable td').forEach(td => {
            td.style.borderBottom = `1px solid ${borderColor.value}`;
        });

        document.querySelectorAll('#summaryTable .grand td').forEach(td => {
            td.style.backgroundColor = primaryColor.value;
            td.style.color = '#fff';
            td.style.fontWeight = '700';
        });

        document.getElementById('logoBox').style.display = showLogo.checked ? 'inline-block' : 'none';
        document.getElementById('logoBox').style.border = `1px dashed ${borderColor.value}`;
        document.getElementById('logoBox').style.color = mutedColor.value;

        document.getElementById('signatureBox').style.display = showSignature.checked ? 'block' : 'none';
        document.getElementById('termsBox').style.display = showTerms.checked ? 'block' : 'none';
    }

    document.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });

    updatePreview();
</script>

</x-layouts.app>