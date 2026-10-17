@php
    $primaryColor   = $setting->primary_color ?? '#dc2626';
    $textColor      = $setting->text_color ?? '#1f2937';
    $mutedColor     = $setting->muted_color ?? '#4b5563';
    $borderColor    = $setting->border_color ?? '#e5e7eb';
    $lightBgColor   = $setting->light_bg_color ?? '#f9fafb';
    $softBgColor    = $setting->soft_bg_color ?? '#fef2f2';
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

            {{-- LEFT CONTROLS --}}
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
                    <label class="text-sm font-semibold">Primary Red Color</label>
                    <input type="color" name="primary_color" id="primary_color"
                           value="{{ $primaryColor }}"
                           class="w-full h-11 mt-2">
                </div>

                <div>
                    <label class="text-sm font-semibold">Text Color</label>
                    <input type="color" name="text_color" id="text_color"
                           value="{{ $textColor }}"
                           class="w-full h-11 mt-2">
                </div>

                <div>
                    <label class="text-sm font-semibold">Muted Text Color</label>
                    <input type="color" name="muted_color" id="muted_color"
                           value="{{ $mutedColor }}"
                           class="w-full h-11 mt-2">
                </div>

                <div>
                    <label class="text-sm font-semibold">Border Color</label>
                    <input type="color" name="border_color" id="border_color"
                           value="{{ $borderColor }}"
                           class="w-full h-11 mt-2">
                </div>

                <div>
                    <label class="text-sm font-semibold">Light Background</label>
                    <input type="color" name="light_bg_color" id="light_bg_color"
                           value="{{ $lightBgColor }}"
                           class="w-full h-11 mt-2">
                </div>

                <div>
                    <label class="text-sm font-semibold">Soft Red Background</label>
                    <input type="color" name="soft_bg_color" id="soft_bg_color"
                           value="{{ $softBgColor }}"
                           class="w-full h-11 mt-2">
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

                <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-lg font-semibold">
                    Save Customization
                </button>

                <button type="submit"
                        formaction="{{ route('bill-template.customize.reset', $templateId) }}"
                        formmethod="POST"
                        onclick="return confirm('Are you sure you want to reset this template customization?')"
                        class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-lg font-semibold">
                    @csrf
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
                     class="bg-white mx-auto shadow-xl"
                     style="
                        width:794px;
                        min-height:1123px;
                        padding:18px;
                        font-family:'{{ $fontFamily }}', 'DejaVu Sans', sans-serif;
                        font-size:12px;
                        color:{{ $textColor }};
                     ">

                    {{-- HEADER --}}
                    <div id="headerBox"
                         style="border-bottom:3px solid {{ $primaryColor }}; padding-bottom:12px; margin-bottom:14px;">

                        <div style="float:left; width:65%;">
                            <div id="badgeBox"
                                 style="
                                    display:inline-block;
                                    border:1px solid {{ $primaryColor }};
                                    color:{{ $primaryColor }};
                                    padding:4px 10px;
                                    font-size:10px;
                                    border-radius:20px;
                                    font-weight:700;
                                 ">
                                TAX INVOICE
                            </div>

                            <div id="companyName"
                                 style="
                                    font-size:24px;
                                    font-weight:700;
                                    color:{{ $primaryColor }};
                                    margin-bottom:6px;
                                    margin-top:6px;
                                 ">
                                {{ $business->name ?? $business->business_name ?? 'Real Victory Groups' }}
                            </div>

                            <div class="preview-muted">
                                73 Basement, Ekta Enclave Society, Lakhanpur, Kanpur, Uttar Pradesh - 208024
                            </div>

                            <div class="preview-muted" style="font-size:11px;">
                                Mobile: 7753800444 | GSTIN: 09ABCDE1234F1Z5
                            </div>

                            <div class="preview-muted" style="font-size:11px;">
                                Email: info@realvictorygroups.com
                            </div>
                        </div>

                        <div id="logoBox" style="float:right; width:30%; text-align:right;">
                            <div style="width:110px; height:80px; border:1px solid #ddd; display:inline-flex; align-items:center; justify-content:center; font-size:11px;">
                                LOGO
                            </div>
                        </div>

                        <div style="clear:both;"></div>
                    </div>

                    {{-- META --}}
                    <div id="metaBox"
                         style="
                            background:{{ $lightBgColor }};
                            border:1px solid {{ $borderColor }};
                            padding:10px;
                            border-radius:8px;
                            margin:14px 0;
                         ">
                        <table style="width:100%; border-collapse:collapse;">
                            <tr>
                                <td style="padding:4px 0;">
                                    <strong>Invoice No:</strong> INV-0001
                                </td>
                                <td style="padding:4px 0; text-align:right;">
                                    <strong>Date:</strong> {{ now()->format('d/m/Y') }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    {{-- BILL TO --}}
                    <div id="billToBox"
                         style="
                            border:1px solid {{ $borderColor }};
                            border-left:4px solid {{ $primaryColor }};
                            padding:12px;
                            border-radius:8px;
                            margin-bottom:14px;
                         ">
                        <div style="font-size:13px; font-weight:700; color:#111827; margin-bottom:8px; text-transform:uppercase;">
                            Bill To
                        </div>

                        <div>
                            <strong>DEMO CUSTOMER</strong>
                        </div>

                        <div class="preview-muted">
                            Sample Market Road, Lucknow, Uttar Pradesh - 226001
                        </div>

                        <div style="font-size:11px; margin-top:6px;">
                            <strong>Mobile:</strong> 9876543210 <br>
                            <strong>GSTIN:</strong> 09XYZDE1234F1Z2 <br>
                            <strong>PAN:</strong> ABCDE1234F <br>
                            <strong>Place of Supply:</strong> Uttar Pradesh
                        </div>
                    </div>

                    {{-- ITEMS --}}
                    <table id="itemsTable" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="width:34%; background:{{ $primaryColor }}; color:#fff; padding:9px 8px; font-size:11px; text-align:left;">Service</th>
                                <th style="width:12%; background:{{ $primaryColor }}; color:#fff; padding:9px 8px; font-size:11px; text-align:left;">SAC</th>
                                <th style="width:10%; background:{{ $primaryColor }}; color:#fff; padding:9px 8px; font-size:11px; text-align:left;">Qty</th>
                                <th style="width:12%; background:{{ $primaryColor }}; color:#fff; padding:9px 8px; font-size:11px; text-align:right;">Rate</th>
                                <th style="width:12%; background:{{ $primaryColor }}; color:#fff; padding:9px 8px; font-size:11px; text-align:right;">Tax</th>
                                <th style="width:20%; background:{{ $primaryColor }}; color:#fff; padding:9px 8px; font-size:11px; text-align:right;">Amount</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td style="border-bottom:1px solid {{ $borderColor }}; padding:8px; vertical-align:top;">
                                    <strong>Social Media Creative Package</strong>
                                    <div class="preview-muted" style="font-size:10px; margin-top:2px;">
                                        Monthly creative design package
                                    </div>
                                </td>
                                <td style="border-bottom:1px solid {{ $borderColor }}; padding:8px;">998361</td>
                                <td style="border-bottom:1px solid {{ $borderColor }}; padding:8px;">1</td>
                                <td style="border-bottom:1px solid {{ $borderColor }}; padding:8px; text-align:right;">10000.00</td>
                                <td style="border-bottom:1px solid {{ $borderColor }}; padding:8px; text-align:right;">
                                    1800.00
                                    <div class="preview-muted" style="font-size:10px;">(18%)</div>
                                </td>
                                <td style="border-bottom:1px solid {{ $borderColor }}; padding:8px; text-align:right;">11800.00</td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- SUMMARY --}}
                    <table id="summaryBox"
                           style="
                                width:42%;
                                margin-left:auto;
                                margin-top:16px;
                                border:1px solid {{ $borderColor }};
                                border-radius:8px;
                                overflow:hidden;
                                border-collapse:collapse;
                           ">
                        <tr>
                            <td style="background:{{ $softBgColor }}; font-weight:700; padding:8px 10px; border-bottom:1px solid {{ $borderColor }};">
                                Taxable Amount
                            </td>
                            <td style="background:{{ $softBgColor }}; font-weight:700; padding:8px 10px; border-bottom:1px solid {{ $borderColor }}; text-align:right;">
                                ₹ 10000.00
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:8px 10px; border-bottom:1px solid {{ $borderColor }};">CGST</td>
                            <td style="padding:8px 10px; border-bottom:1px solid {{ $borderColor }}; text-align:right;">₹ 900.00</td>
                        </tr>

                        <tr>
                            <td style="padding:8px 10px; border-bottom:1px solid {{ $borderColor }};">SGST</td>
                            <td style="padding:8px 10px; border-bottom:1px solid {{ $borderColor }}; text-align:right;">₹ 900.00</td>
                        </tr>

                        <tr>
                            <td style="padding:8px 10px; border-bottom:1px solid {{ $borderColor }};">Received Amount</td>
                            <td style="padding:8px 10px; border-bottom:1px solid {{ $borderColor }}; text-align:right;">₹ 5000.00</td>
                        </tr>

                        <tr>
                            <td style="padding:8px 10px; border-bottom:1px solid {{ $borderColor }};">Balance</td>
                            <td style="padding:8px 10px; border-bottom:1px solid {{ $borderColor }}; text-align:right;">₹ 6800.00</td>
                        </tr>

                        <tr>
                            <td style="background:{{ $primaryColor }}; color:#fff; font-weight:700; padding:8px 10px;">
                                Total Amount
                            </td>
                            <td style="background:{{ $primaryColor }}; color:#fff; font-weight:700; padding:8px 10px; text-align:right;">
                                ₹ 11800.00
                            </td>
                        </tr>
                    </table>

                    {{-- WORDS --}}
                    <div id="wordsBox"
                         style="
                            margin-top:12px;
                            padding:10px 12px;
                            background:{{ $lightBgColor }};
                            border-radius:8px;
                            border:1px solid {{ $borderColor }};
                         ">
                        <strong>Total Amount in Words:</strong><br>
                        Eleven Thousand Eight Hundred Rupees
                    </div>

                    {{-- FOOTER --}}
                    <div style="margin-top:18px;">
                        <div id="termsBox" style="float:left; width:52%;">
                            <div style="font-size:13px; font-weight:700; color:#111827; margin-bottom:8px; text-transform:uppercase;">
                                Terms & Conditions
                            </div>

                            <div class="preview-muted">
                                Payment due within 7 days. <br>
                                Goods once sold will not be returned.
                            </div>
                        </div>

                        <div id="signatureBox" style="float:right; width:38%; text-align:right;">
                            <div style="height:50px; margin-bottom:8px;">
                                Signature
                            </div>

                            <div>
                                <strong>Authorised Signatory</strong>
                            </div>

                            <div class="preview-muted">
                                {{ $business->name ?? $business->business_name ?? 'Real Victory Groups' }}
                            </div>
                        </div>

                        <div style="clear:both;"></div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        const primaryColor = document.getElementById('primary_color');
        const textColor = document.getElementById('text_color');
        const mutedColor = document.getElementById('muted_color');
        const borderColor = document.getElementById('border_color');
        const lightBgColor = document.getElementById('light_bg_color');
        const softBgColor = document.getElementById('soft_bg_color');
        const fontFamily = document.getElementById('font_family');

        const preview = document.getElementById('invoicePreview');
        const headerBox = document.getElementById('headerBox');
        const badgeBox = document.getElementById('badgeBox');
        const companyName = document.getElementById('companyName');
        const metaBox = document.getElementById('metaBox');
        const billToBox = document.getElementById('billToBox');
        const itemsTable = document.getElementById('itemsTable');
        const summaryBox = document.getElementById('summaryBox');
        const wordsBox = document.getElementById('wordsBox');

        function updatePreview() {
            preview.style.color = textColor.value;
            preview.style.fontFamily = `'${fontFamily.value}', 'DejaVu Sans', sans-serif`;

            document.querySelectorAll('.preview-muted').forEach(el => {
                el.style.color = mutedColor.value;
            });

            headerBox.style.borderBottom = `3px solid ${primaryColor.value}`;

            badgeBox.style.border = `1px solid ${primaryColor.value}`;
            badgeBox.style.color = primaryColor.value;

            companyName.style.color = primaryColor.value;

            metaBox.style.backgroundColor = lightBgColor.value;
            metaBox.style.border = `1px solid ${borderColor.value}`;

            billToBox.style.border = `1px solid ${borderColor.value}`;
            billToBox.style.borderLeft = `4px solid ${primaryColor.value}`;

            itemsTable.querySelectorAll('thead th').forEach(th => {
                th.style.backgroundColor = primaryColor.value;
                th.style.color = '#ffffff';
            });

            itemsTable.querySelectorAll('tbody td').forEach(td => {
                td.style.borderBottom = `1px solid ${borderColor.value}`;
            });

            summaryBox.style.border = `1px solid ${borderColor.value}`;

            summaryBox.querySelectorAll('td').forEach(td => {
                td.style.borderBottom = `1px solid ${borderColor.value}`;
            });

            summaryBox.querySelectorAll('tr:first-child td').forEach(td => {
                td.style.backgroundColor = softBgColor.value;
            });

            summaryBox.querySelectorAll('tr:last-child td').forEach(td => {
                td.style.backgroundColor = primaryColor.value;
                td.style.color = '#ffffff';
            });

            wordsBox.style.backgroundColor = lightBgColor.value;
            wordsBox.style.border = `1px solid ${borderColor.value}`;

            document.getElementById('logoBox').style.display =
                document.getElementById('show_logo').checked ? 'block' : 'none';

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