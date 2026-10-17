@php
    $setting = $templateSetting ?? $setting ?? null;

    $primaryColor   = $setting->primary_color ?? '#243b53';
    $textColor      = $setting->text_color ?? '#111111';
    $mutedColor     = $setting->muted_color ?? '#64748b';
    $borderColor    = $setting->border_color ?? '#b6c2d1';
    $secondaryColor = $setting->secondary_color ?? '#f1f5f9';
    $lightBgColor   = $setting->light_bg_color ?? '#ffffff';
    $softBgColor    = $setting->soft_bg_color ?? '#eef2f7';
    $accentColor    = $setting->accent_color ?? '#243b53';
    $fontFamily     = $setting->font_family ?? 'DejaVu Sans';

    $showLogo      = (bool) ($setting->show_logo ?? false);
    $showSignature = (bool) ($setting->show_signature ?? true);
    $showTerms     = (bool) ($setting->show_terms ?? true);

    $templateId   = $billTemplate->id ?? $template->id;
    $templateName = $billTemplate->name ?? $template->name ?? 'Bank Ledger Invoice Template';
@endphp

<x-layouts.app :title="__('Customize Bank Ledger Template')">

<form method="POST" action="{{ route('bill-template.customize.save', $templateId) }}">
    @csrf

    <input type="hidden" name="template_id" value="{{ $templateId }}">

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">

        {{-- LEFT SETTINGS --}}
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
                <label class="text-sm font-semibold">Header / Grand Total Color</label>
                <input type="color" name="primary_color" id="primary_color" value="{{ $primaryColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Text Color</label>
                <input type="color" name="text_color" id="text_color" value="{{ $textColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Muted Label Color</label>
                <input type="color" name="muted_color" id="muted_color" value="{{ $mutedColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Border Color</label>
                <input type="color" name="border_color" id="border_color" value="{{ $borderColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Table Header Background</label>
                <input type="color" name="secondary_color" id="secondary_color" value="{{ $secondaryColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Invoice Background</label>
                <input type="color" name="light_bg_color" id="light_bg_color" value="{{ $lightBgColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Outer Page Background</label>
                <input type="color" name="soft_bg_color" id="soft_bg_color" value="{{ $softBgColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Accent / Table Line Color</label>
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

        {{-- RIGHT PREVIEW --}}
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
                        border:1px solid {{ $borderColor }};
                        padding:0;
                     ">

                    <table id="bankHead" style="width:100%; border-collapse:collapse; background:{{ $primaryColor }}; color:#fff;">
                        <tr>
                            <td style="padding:16px 20px; vertical-align:top;">
                                <div id="logoBox"
                                     style="
                                        display:{{ $showLogo ? 'inline-block' : 'none' }};
                                        border:1px dashed #fff;
                                        padding:8px 14px;
                                        margin-bottom:8px;
                                        font-size:11px;
                                        color:#fff;
                                     ">
                                    LOGO
                                </div>

                                <div style="font-size:24px; font-weight:700;">
                                    Real Victory Groups
                                </div>

                                <div style="margin-top:4px; line-height:1.5;">
                                    73 Basement, Ekta Enclave Society, Kanpur, Uttar Pradesh<br>
                                    Mobile: 7753800444 | GSTIN: 09ABCDE1234F1Z5
                                </div>
                            </td>

                            <td style="padding:16px 20px; vertical-align:top; text-align:right;">
                                <div style="font-size:28px; font-weight:700;">TAX</div>
                                <div style="font-size:12px;">No: INV-0001</div>
                            </td>
                        </tr>
                    </table>

                    <table id="infoTable" style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="width:40%; border-bottom:1px solid {{ $borderColor }}; padding:10px 14px; vertical-align:top;">
                                <div class="labelText">Account / Customer</div>
                                <strong>DEMO CUSTOMER</strong><br>
                                Sample Market Road, Lucknow
                            </td>

                            <td style="border-bottom:1px solid {{ $borderColor }}; padding:10px 14px; vertical-align:top;">
                                <div class="labelText">Date</div>
                                {{ now()->format('d/m/Y') }}
                            </td>

                            <td style="border-bottom:1px solid {{ $borderColor }}; padding:10px 14px; vertical-align:top;">
                                <div class="labelText">Mobile</div>
                                9876543210
                            </td>

                            <td style="border-bottom:1px solid {{ $borderColor }}; padding:10px 14px; vertical-align:top;">
                                <div class="labelText">GSTIN</div>
                                09XYZDE1234F1Z2
                            </td>
                        </tr>
                    </table>

                    <table id="itemsTable" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th>Particulars</th>
                                <th>SAC</th>
                                <th>Qty</th>
                                <th style="text-align:right;">Debit</th>
                                <th style="text-align:right;">Tax</th>
                                <th style="text-align:right;">Credit</th>
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

                    <div style="padding:0 20px 20px;">
                        <table id="summaryTable"
                               style="
                                    width:45%;
                                    margin-left:auto;
                                    margin-top:14px;
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

                    <div id="termsBox"
                         style="
                            display:{{ $showTerms ? 'block' : 'none' }};
                            padding:0 20px 14px;
                         ">
                        <strong>Terms & Conditions:</strong><br>
                        Payment due within 7 days. Subject to Kanpur jurisdiction.
                    </div>

                    <table id="footerTable"
                           style="
                                width:100%;
                                border-collapse:collapse;
                                padding:16px 20px;
                           ">
                        <tr>
                            <td style="padding:16px 20px;">
                                <strong>Amount in Words:</strong><br>
                                Seventeen Thousand Seven Hundred Rupees Only
                            </td>

                            <td id="signatureBox"
                                style="
                                    display:{{ $showSignature ? 'table-cell' : 'none' }};
                                    padding:16px 20px;
                                    text-align:right;
                                ">
                                <div style="height:48px; color:{{ $mutedColor }};">Signature</div>
                                <strong>Authorised Signatory</strong><br>
                                Real Victory Groups
                            </td>
                        </tr>
                    </table>

                </div>
            </div>
        </div>

    </div>
</form>

<style>
    .labelText {
        font-size:10px;
        text-transform:uppercase;
        color:{{ $mutedColor }};
        font-weight:700;
        margin-bottom:3px;
    }

    #itemsTable th {
        background:{{ $secondaryColor }};
        border-bottom:2px solid {{ $accentColor }};
        padding:9px;
        text-align:left;
        color:{{ $textColor }};
    }

    #itemsTable td {
        border-bottom:1px solid {{ $borderColor }};
        padding:9px;
        vertical-align:top;
    }

    .desc {
        font-size:10px;
        color:{{ $mutedColor }};
    }

    #summaryTable td {
        padding:8px;
        border-bottom:1px solid {{ $borderColor }};
    }

    #summaryTable .grand td {
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

    const showLogo = document.getElementById('show_logo');
    const showSignature = document.getElementById('show_signature');
    const showTerms = document.getElementById('show_terms');

    function updatePreview() {
        const preview = document.getElementById('invoicePreview');
        const pageBox = document.getElementById('pageBox');
        const bankHead = document.getElementById('bankHead');

        preview.style.backgroundColor = softBgColor.value;
        preview.style.color = textColor.value;
        preview.style.fontFamily = `'${fontFamily.value}', DejaVu Sans, sans-serif`;

        pageBox.style.backgroundColor = lightBgColor.value;
        pageBox.style.border = `1px solid ${borderColor.value}`;

        bankHead.style.backgroundColor = primaryColor.value;

        document.querySelectorAll('.labelText').forEach(el => {
            el.style.color = mutedColor.value;
        });

        document.querySelectorAll('#infoTable td').forEach(td => {
            td.style.borderBottom = `1px solid ${borderColor.value}`;
        });

        document.querySelectorAll('#itemsTable th').forEach(th => {
            th.style.backgroundColor = secondaryColor.value;
            th.style.borderBottom = `2px solid ${accentColor.value}`;
            th.style.color = textColor.value;
        });

        document.querySelectorAll('#itemsTable td').forEach(td => {
            td.style.borderBottom = `1px solid ${borderColor.value}`;
        });

        document.querySelectorAll('.desc').forEach(el => {
            el.style.color = mutedColor.value;
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
        document.getElementById('termsBox').style.display = showTerms.checked ? 'block' : 'none';
        document.getElementById('signatureBox').style.display = showSignature.checked ? 'table-cell' : 'none';
    }

    document.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });

    updatePreview();
</script>

</x-layouts.app>