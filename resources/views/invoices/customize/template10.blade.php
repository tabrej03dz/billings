@php
    $setting = $templateSetting ?? $setting ?? null;

    $primaryColor   = $setting->primary_color ?? '#111827';
    $textColor      = $setting->text_color ?? '#111827';
    $mutedColor     = $setting->muted_color ?? '#6b7280';
    $borderColor    = $setting->border_color ?? '#9ca3af';
    $secondaryColor = $setting->secondary_color ?? '#f9fafb';
    $lightBgColor   = $setting->light_bg_color ?? '#ffffff';
    $softBgColor    = $setting->soft_bg_color ?? '#f9fafb';
    $accentColor    = $setting->accent_color ?? '#111827';
    $fontFamily     = $setting->font_family ?? 'DejaVu Sans';

    $showLogo      = (bool) ($setting->show_logo ?? true);
    $showSignature = (bool) ($setting->show_signature ?? true);
    $showTerms     = (bool) ($setting->show_terms ?? true);

    $templateId   = $billTemplate->id ?? $template->id;
    $templateName = $billTemplate->name ?? $template->name ?? 'Simple Dashed Bill Template';
@endphp

<x-layouts.app :title="__('Customize Simple Dashed Template')">

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
                <label class="text-sm font-semibold">Main Text / Heading Color</label>
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
                <label class="text-sm font-semibold">Dashed Border Color</label>
                <input type="color" name="border_color" id="border_color" value="{{ $borderColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Table Header Background</label>
                <input type="color" name="secondary_color" id="secondary_color" value="{{ $secondaryColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Bill Paper Background</label>
                <input type="color" name="light_bg_color" id="light_bg_color" value="{{ $lightBgColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Outer Page Background</label>
                <input type="color" name="soft_bg_color" id="soft_bg_color" value="{{ $softBgColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Document Badge / Accent Color</label>
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
                    padding:20px;
                    background:{{ $softBgColor }};
                    font-family:'{{ $fontFamily }}', DejaVu Sans, sans-serif;
                    font-size:11px;
                    color:{{ $textColor }};
                 ">

                <div id="pageBox"
                     style="
                        max-width:760px;
                        margin:0 auto;
                        background:{{ $lightBgColor }};
                        border:1px dashed {{ $borderColor }};
                        padding:20px;
                     ">

                    <div style="text-align:center;">
                        <div id="logoBox"
                             style="
                                display:{{ $showLogo ? 'inline-block' : 'none' }};
                                width:90px;
                                height:60px;
                                line-height:60px;
                                border:1px dashed {{ $borderColor }};
                                color:{{ $mutedColor }};
                                font-size:11px;
                                margin-bottom:6px;
                             ">
                            LOGO
                        </div>
                    </div>

                    <div id="companyName"
                         style="
                            text-align:center;
                            font-size:24px;
                            font-weight:700;
                            margin-top:6px;
                            color:{{ $primaryColor }};
                         ">
                        Real Victory Groups
                    </div>

                    <div class="subtitle"
                         style="
                            text-align:center;
                            font-size:11px;
                            color:{{ $mutedColor }};
                            line-height:1.5;
                         ">
                        73 Basement, Ekta Enclave Society, Kanpur, Uttar Pradesh - 208024<br>
                        Mobile: 7753800444 | Email: info@realvictorygroups.com<br>
                        GSTIN: 09ABCDE1234F1Z5
                    </div>

                    <div style="text-align:center;">
                        <div id="docBadge"
                             style="
                                margin:16px auto;
                                display:inline-block;
                                padding:8px 18px;
                                border:2px solid {{ $accentColor }};
                                color:{{ $accentColor }};
                                font-size:20px;
                                font-weight:700;
                             ">
                            TAX
                        </div>
                    </div>

                    <div class="divider"
                         style="border-top:1px dashed {{ $borderColor }}; margin:14px 0;"></div>

                    <table class="metaTable" style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td class="label">Invoice No</td>
                            <td>INV-0001</td>
                        </tr>
                        <tr>
                            <td class="label">Date</td>
                            <td>{{ now()->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Bill To</td>
                            <td>
                                <strong>DEMO CUSTOMER</strong><br>
                                Sample Market Road, Lucknow, Uttar Pradesh - 226001
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Customer Info</td>
                            <td>
                                Mobile: 9876543210<br>
                                GSTIN: 09XYZDE1234F1Z2<br>
                                PAN: ABCDE1234F<br>
                                POS: Uttar Pradesh
                            </td>
                        </tr>
                    </table>

                    <div class="divider"
                         style="border-top:1px dashed {{ $borderColor }}; margin:14px 0;"></div>

                    <table id="itemsTable" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="width:34%;">Service</th>
                                <th style="width:10%;">SAC</th>
                                <th style="width:10%;">Qty</th>
                                <th style="width:14%; text-align:right;">Rate</th>
                                <th style="width:12%; text-align:right;">Tax</th>
                                <th style="width:20%; text-align:right;">Amount</th>
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

                            <tr>
                                <td>
                                    <strong>Website Maintenance</strong>
                                    <div class="desc">Basic monthly support</div>
                                </td>
                                <td>998313</td>
                                <td>1</td>
                                <td style="text-align:right;">5000.00</td>
                                <td style="text-align:right;">900.00</td>
                                <td style="text-align:right;">5900.00</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="divider"
                         style="border-top:1px dashed {{ $borderColor }}; margin:14px 0;"></div>

                    <table id="summaryTable" style="width:100%; border-collapse:collapse;">
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
                            <td>Received</td>
                            <td style="text-align:right;">₹ 10000.00</td>
                        </tr>
                        <tr>
                            <td>Balance</td>
                            <td style="text-align:right;">₹ 7700.00</td>
                        </tr>
                        <tr class="total">
                            <td>Total</td>
                            <td style="text-align:right;">₹ 17700.00</td>
                        </tr>
                    </table>

                    <div class="divider"
                         style="border-top:1px dashed {{ $borderColor }}; margin:14px 0;"></div>

                    <div>
                        <strong>Amount in Words:</strong><br>
                        Seventeen Thousand Seven Hundred Rupees Only
                    </div>

                    <div id="termsBox"
                         style="display:{{ $showTerms ? 'block' : 'none' }};">
                        <div class="divider"
                             style="border-top:1px dashed {{ $borderColor }}; margin:14px 0;"></div>

                        <div>
                            <strong>Terms & Conditions</strong><br>
                            Payment due within 7 days.<br>
                            Goods once sold will not be returned.
                        </div>
                    </div>

                    <div id="signatureBox"
                         style="
                            display:{{ $showSignature ? 'block' : 'none' }};
                            margin-top:30px;
                            text-align:right;
                         ">
                        <div style="height:45px; color:{{ $mutedColor }};">Signature</div>
                        <strong>Authorised Signatory</strong><br>
                        Real Victory Groups
                    </div>

                </div>
            </div>
        </div>
    </div>
</form>

<style>
    .metaTable td {
        padding:6px 0;
    }

    .metaTable .label {
        font-weight:700;
        width:35%;
        color:{{ $primaryColor }};
    }

    #itemsTable th {
        background:{{ $secondaryColor }};
        border-top:1px solid {{ $accentColor }};
        border-bottom:1px solid {{ $accentColor }};
        color:{{ $primaryColor }};
        padding:8px 6px;
        text-align:left;
        font-size:11px;
    }

    #itemsTable td {
        border-bottom:1px dashed {{ $borderColor }};
        padding:8px 6px;
        vertical-align:top;
    }

    .desc {
        font-size:10px;
        color:{{ $mutedColor }};
        margin-top:3px;
    }

    #summaryTable td {
        padding:7px 4px;
        border-bottom:1px dashed {{ $borderColor }};
    }

    #summaryTable .total td {
        border-top:2px solid {{ $accentColor }};
        border-bottom:2px solid {{ $accentColor }};
        font-weight:700;
        font-size:14px;
        color:{{ $primaryColor }};
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
        const logoBox = document.getElementById('logoBox');
        const companyName = document.getElementById('companyName');
        const docBadge = document.getElementById('docBadge');

        preview.style.backgroundColor = softBgColor.value;
        preview.style.color = textColor.value;
        preview.style.fontFamily = `'${fontFamily.value}', DejaVu Sans, sans-serif`;

        pageBox.style.backgroundColor = lightBgColor.value;
        pageBox.style.border = `1px dashed ${borderColor.value}`;

        companyName.style.color = primaryColor.value;

        docBadge.style.border = `2px solid ${accentColor.value}`;
        docBadge.style.color = accentColor.value;

        logoBox.style.display = showLogo.checked ? 'inline-block' : 'none';
        logoBox.style.border = `1px dashed ${borderColor.value}`;
        logoBox.style.color = mutedColor.value;

        document.querySelectorAll('.subtitle').forEach(el => {
            el.style.color = mutedColor.value;
        });

        document.querySelectorAll('.metaTable .label').forEach(el => {
            el.style.color = primaryColor.value;
        });

        document.querySelectorAll('.divider').forEach(el => {
            el.style.borderTop = `1px dashed ${borderColor.value}`;
        });

        document.querySelectorAll('.desc').forEach(el => {
            el.style.color = mutedColor.value;
        });

        document.querySelectorAll('#itemsTable th').forEach(th => {
            th.style.backgroundColor = secondaryColor.value;
            th.style.color = primaryColor.value;
            th.style.borderTop = `1px solid ${accentColor.value}`;
            th.style.borderBottom = `1px solid ${accentColor.value}`;
        });

        document.querySelectorAll('#itemsTable td').forEach(td => {
            td.style.borderBottom = `1px dashed ${borderColor.value}`;
        });

        document.querySelectorAll('#summaryTable td').forEach(td => {
            td.style.borderBottom = `1px dashed ${borderColor.value}`;
        });

        document.querySelectorAll('#summaryTable .total td').forEach(td => {
            td.style.borderTop = `2px solid ${accentColor.value}`;
            td.style.borderBottom = `2px solid ${accentColor.value}`;
            td.style.color = primaryColor.value;
        });

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