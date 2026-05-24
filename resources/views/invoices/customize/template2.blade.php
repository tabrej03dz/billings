@php
    $textColor      = $setting->text_color ?? '#000000';
    $borderColor    = $setting->border_color ?? '#000000';
    $headerBgColor  = $setting->light_bg_color ?? '#f1f1f1';
    $primaryColor   = $setting->primary_color ?? '#000000';
    $softBgColor    = $setting->soft_bg_color ?? '#ffffff';
    $mutedColor     = $setting->muted_color ?? '#000000';
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
                    <label class="text-sm font-semibold">Title / Primary Color</label>
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
                    <label class="text-sm font-semibold">Border Color</label>
                    <input type="color" name="border_color" id="border_color"
                           value="{{ $borderColor }}"
                           class="w-full h-11 mt-2">
                </div>

                <div>
                    <label class="text-sm font-semibold">Table Header Background</label>
                    <input type="color" name="light_bg_color" id="header_bg_color"
                           value="{{ $headerBgColor }}"
                           class="w-full h-11 mt-2">
                </div>

                <div>
                    <label class="text-sm font-semibold">Page Background</label>
                    <input type="color" name="soft_bg_color" id="soft_bg_color"
                           value="{{ $softBgColor }}"
                           class="w-full h-11 mt-2">
                </div>

                <div>
                    <label class="text-sm font-semibold">Muted Text Color</label>
                    <input type="color" name="muted_color" id="muted_color"
                           value="{{ $mutedColor }}"
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

                    {{-- TOP HEADER --}}
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td id="logoBox" style="width:20%; vertical-align:top;">
                                <div style="width:110px; height:80px; border:1px solid {{ $borderColor }}; display:flex; align-items:center; justify-content:center; font-size:11px;">
                                    LOGO
                                </div>
                            </td>

                            <td style="width:55%; vertical-align:top;">
                                <div id="companyName" style="font-size:20px; font-weight:700; color:{{ $primaryColor }};">
                                    {{ $business->name ?? $business->business_name ?? 'Real Victory Groups' }}
                                </div>
                                <div class="preview-muted">
                                    73 Basement, Ekta Enclave Society, Lakhanpur, Kanpur, Uttar Pradesh - 208024
                                </div>
                                <div class="preview-muted" style="font-size:11px;">Mobile: 7753800444</div>
                                <div class="preview-muted" style="font-size:11px;">Email: info@realvictorygroups.com</div>
                                <div class="preview-muted" style="font-size:11px;">GSTIN: 09ABCDE1234F1Z5</div>
                            </td>

                            <td style="width:25%; vertical-align:top; text-align:right;">
                                <div id="invoiceTitle" style="font-size:28px; font-weight:700; letter-spacing:1px; color:{{ $primaryColor }};">
                                    TAX
                                </div>
                                <div style="font-size:11px;">INVOICE</div>
                            </td>
                        </tr>
                    </table>

                    {{-- BILL TO + META --}}
                    <table style="width:100%; border-collapse:collapse; margin-top:10px;">
                        <tr>
                            <td class="borderedBox" style="width:60%; border:1px solid {{ $borderColor }}; padding:10px; vertical-align:top;">
                                <strong>Bill To</strong><br>
                                <strong>DEMO CUSTOMER</strong><br>
                                Sample Market Road, Lucknow, Uttar Pradesh - 226001<br>
                                Mobile: 9876543210<br>
                                GSTIN: 09XYZDE1234F1Z2<br>
                                PAN: ABCDE1234F<br>
                                Place of Supply: Uttar Pradesh
                            </td>

                            <td class="borderedBox" style="width:40%; border:1px solid {{ $borderColor }}; padding:10px; vertical-align:top;">
                                <strong>Invoice No:</strong> INV-0001<br><br>
                                <strong>Date:</strong> {{ now()->format('d/m/Y') }}<br><br>
                                <strong>Document Type:</strong> TAX
                            </td>
                        </tr>
                    </table>

                    {{-- ITEMS TABLE --}}
                    <table id="itemsTable" style="width:100%; border-collapse:collapse; margin-top:15px;">
                        <thead>
                            <tr>
                                <th style="width:35%; border:1px solid {{ $borderColor }}; background:{{ $headerBgColor }}; padding:7px;">Description</th>
                                <th style="width:10%; border:1px solid {{ $borderColor }}; background:{{ $headerBgColor }}; padding:7px;">SAC</th>
                                <th style="width:10%; border:1px solid {{ $borderColor }}; background:{{ $headerBgColor }}; padding:7px;">Qty</th>
                                <th style="width:15%; border:1px solid {{ $borderColor }}; background:{{ $headerBgColor }}; padding:7px;">Rate</th>
                                <th style="width:10%; border:1px solid {{ $borderColor }}; background:{{ $headerBgColor }}; padding:7px;">Tax</th>
                                <th style="width:20%; border:1px solid {{ $borderColor }}; background:{{ $headerBgColor }}; padding:7px;">Amount</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td style="border:1px solid {{ $borderColor }}; padding:7px;">
                                    <strong>Social Media Creative Package</strong><br>
                                    <span class="preview-muted" style="font-size:11px;">Monthly creative design package</span>
                                </td>
                                <td style="border:1px solid {{ $borderColor }}; padding:7px;">998361</td>
                                <td style="border:1px solid {{ $borderColor }}; padding:7px;">1</td>
                                <td style="border:1px solid {{ $borderColor }}; padding:7px; text-align:right;">10000.00</td>
                                <td style="border:1px solid {{ $borderColor }}; padding:7px; text-align:right;">1800.00</td>
                                <td style="border:1px solid {{ $borderColor }}; padding:7px; text-align:right;">11800.00</td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- TOTALS --}}
                    <table id="totalsTable"
                           style="width:40%; margin-left:auto; margin-top:12px; border-collapse:collapse;">
                        <tr>
                            <td style="border:1px solid {{ $borderColor }}; padding:7px;">Taxable Amount</td>
                            <td style="border:1px solid {{ $borderColor }}; padding:7px; text-align:right;">₹ 10000.00</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid {{ $borderColor }}; padding:7px;">CGST</td>
                            <td style="border:1px solid {{ $borderColor }}; padding:7px; text-align:right;">₹ 900.00</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid {{ $borderColor }}; padding:7px;">SGST</td>
                            <td style="border:1px solid {{ $borderColor }}; padding:7px; text-align:right;">₹ 900.00</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid {{ $borderColor }}; padding:7px;">Received</td>
                            <td style="border:1px solid {{ $borderColor }}; padding:7px; text-align:right;">₹ 5000.00</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid {{ $borderColor }}; padding:7px;">Balance</td>
                            <td style="border:1px solid {{ $borderColor }}; padding:7px; text-align:right;">₹ 6800.00</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid {{ $borderColor }}; padding:7px;"><strong>Total</strong></td>
                            <td style="border:1px solid {{ $borderColor }}; padding:7px; text-align:right;"><strong>₹ 11800.00</strong></td>
                        </tr>
                    </table>

                    <div style="margin-top:15px;">
                        <strong>Amount in Words:</strong> Eleven Thousand Eight Hundred Rupees
                    </div>

                    {{-- FOOTER --}}
                    <table style="width:100%; border-collapse:collapse; margin-top:15px;">
                        <tr>
                            <td id="termsBox" style="width:60%; vertical-align:top;">
                                <strong>Terms & Conditions</strong><br>
                                <span class="preview-muted">
                                    Payment due within 7 days.<br>
                                    Goods once sold will not be returned.
                                </span>
                            </td>

                            <td id="signatureBox" style="width:40%; vertical-align:top; text-align:right;">
                                <div style="height:45px;">Signature</div>
                                <strong>Authorised Signatory</strong><br>
                                {{ $business->name ?? $business->business_name ?? 'Real Victory Groups' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </form>

    <script>
        const primaryColor = document.getElementById('primary_color');
        const textColor = document.getElementById('text_color');
        const mutedColor = document.getElementById('muted_color');
        const borderColor = document.getElementById('border_color');
        const headerBgColor = document.getElementById('header_bg_color');
        const softBgColor = document.getElementById('soft_bg_color');
        const fontFamily = document.getElementById('font_family');

        const preview = document.getElementById('invoicePreview');
        const companyName = document.getElementById('companyName');
        const invoiceTitle = document.getElementById('invoiceTitle');
        const itemsTable = document.getElementById('itemsTable');
        const totalsTable = document.getElementById('totalsTable');

        function updatePreview() {
            preview.style.color = textColor.value;
            preview.style.backgroundColor = softBgColor.value;
            preview.style.fontFamily = `'${fontFamily.value}', 'DejaVu Sans', sans-serif`;

            companyName.style.color = primaryColor.value;
            invoiceTitle.style.color = primaryColor.value;

            document.querySelectorAll('.preview-muted').forEach(el => {
                el.style.color = mutedColor.value;
            });

            document.querySelectorAll('.borderedBox').forEach(el => {
                el.style.border = `1px solid ${borderColor.value}`;
            });

            document.querySelectorAll('#itemsTable th').forEach(th => {
                th.style.border = `1px solid ${borderColor.value}`;
                th.style.backgroundColor = headerBgColor.value;
            });

            document.querySelectorAll('#itemsTable td').forEach(td => {
                td.style.border = `1px solid ${borderColor.value}`;
            });

            document.querySelectorAll('#totalsTable td').forEach(td => {
                td.style.border = `1px solid ${borderColor.value}`;
            });

            document.querySelectorAll('#logoBox div').forEach(el => {
                el.style.border = `1px solid ${borderColor.value}`;
            });

            document.getElementById('logoBox').style.display =
                document.getElementById('show_logo').checked ? 'table-cell' : 'none';

            document.getElementById('signatureBox').style.display =
                document.getElementById('show_signature').checked ? 'table-cell' : 'none';

            document.getElementById('termsBox').style.display =
                document.getElementById('show_terms').checked ? 'table-cell' : 'none';
        }

        document.querySelectorAll('input, select').forEach(el => {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        });

        updatePreview();
    </script>

</x-layouts.app>