@php
    $setting = $templateSetting ?? $setting ?? null;

    $primaryColor   = $setting->primary_color ?? '#000000';
    $textColor      = $setting->text_color ?? '#000000';
    $mutedColor     = $setting->muted_color ?? '#333333';
    $borderColor    = $setting->border_color ?? '#000000';
    $secondaryColor = $setting->secondary_color ?? '#e5e5e5';
    $lightBgColor   = $setting->light_bg_color ?? '#ffffff';
    $softBgColor    = $setting->soft_bg_color ?? '#ffffff';
    $accentColor    = $setting->accent_color ?? '#f5f5f5';
    $fontFamily     = $setting->font_family ?? 'DejaVu Sans';

    $showLogo      = (bool) ($setting->show_logo ?? true);
    $showSignature = (bool) ($setting->show_signature ?? true);
    $showTerms     = (bool) ($setting->show_terms ?? true);

    $templateId   = $billTemplate->id ?? $template->id;
    $templateName = $billTemplate->name ?? $template->name ?? 'GST Form Invoice Template';
@endphp

<x-layouts.app :title="__('Customize GST Form Template')">

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
                <label class="text-sm font-semibold">Main Border / Heading Color</label>
                <input type="color" name="primary_color" id="primary_color" value="{{ $primaryColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Text Color</label>
                <input type="color" name="text_color" id="text_color" value="{{ $textColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Description Text Color</label>
                <input type="color" name="muted_color" id="muted_color" value="{{ $mutedColor }}" class="w-full h-11 mt-2">
            </div>

            <div>
                <label class="text-sm font-semibold">Table Border Color</label>
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
                <label class="text-sm font-semibold">Title Bar Background</label>
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
                    padding:15px;
                    background:{{ $softBgColor }};
                    font-family:'{{ $fontFamily }}', DejaVu Sans, sans-serif;
                    font-size:11.5px;
                    color:{{ $textColor }};
                 ">

                <div id="formBox"
                     style="
                        border:2px solid {{ $borderColor }};
                        background:{{ $lightBgColor }};
                     ">

                    <div id="titleBox"
                         style="
                            text-align:center;
                            font-size:20px;
                            font-weight:700;
                            border-bottom:2px solid {{ $borderColor }};
                            padding:8px;
                            background:{{ $accentColor }};
                            color:{{ $primaryColor }};
                         ">
                        TAX INVOICE
                    </div>

                    <table class="previewTable">
                        <tr>
                            <td style="width:15%;">
                                <div id="logoBox"
                                     style="
                                        display:{{ $showLogo ? 'block' : 'none' }};
                                        width:80px;
                                        height:55px;
                                        line-height:55px;
                                        text-align:center;
                                        border:1px dashed {{ $borderColor }};
                                        color:{{ $mutedColor }};
                                     ">
                                    LOGO
                                </div>
                            </td>

                            <td style="width:55%;">
                                <div id="headName"
                                     style="
                                        font-size:18px;
                                        font-weight:700;
                                        color:{{ $primaryColor }};
                                     ">
                                    Real Victory Groups
                                </div>
                                73 Basement, Ekta Enclave Society, Kanpur, Uttar Pradesh<br>
                                Mobile: 7753800444<br>
                                Email: info@realvictorygroups.com<br>
                                GSTIN: 09ABCDE1234F1Z5
                            </td>

                            <td style="width:30%;">
                                Invoice No: <strong>INV-0001</strong><br>
                                Date: <strong>{{ now()->format('d/m/Y') }}</strong><br>
                                Type: <strong>TAX</strong>
                            </td>
                        </tr>
                    </table>

                    <table class="previewTable">
                        <tr>
                            <td style="width:50%;">
                                <strong>Details of Receiver / Billed To</strong><br>
                                Name: DEMO CUSTOMER<br>
                                Address: Sample Market Road, Lucknow<br>
                                State: Uttar Pradesh<br>
                                GSTIN: 09XYZDE1234F1Z2
                            </td>

                            <td style="width:50%;">
                                <strong>Place of Supply</strong><br>
                                Uttar Pradesh<br><br>
                                Mobile: 9876543210<br>
                                PAN: ABCDE1234F
                            </td>
                        </tr>
                    </table>

                    <table id="itemsTable" class="previewTable">
                        <thead>
                            <tr>
                                <th>Service Description</th>
                                <th>SAC/HSN</th>
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

                    <table id="summaryTable" class="previewTable">
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

                    <table class="previewTable">
                        <tr>
                            <td style="width:60%;">
                                <strong>Total Invoice Amount in Words</strong><br>
                                Seventeen Thousand Seven Hundred Rupees Only

                                <div id="termsBox"
                                     style="display:{{ $showTerms ? 'block' : 'none' }};">
                                    <br>
                                    <strong>Terms:</strong><br>
                                    Payment due within 7 days.<br>
                                    Goods once sold will not be returned.
                                </div>
                            </td>

                            <td id="signatureBox"
                                style="
                                    display:{{ $showSignature ? 'table-cell' : 'none' }};
                                    text-align:right;
                                    height:80px;
                                ">
                                <div style="height:45px; color:{{ $mutedColor }};">
                                    Signature
                                </div>
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
    .previewTable {
        width: 100%;
        border-collapse: collapse;
    }

    .previewTable td,
    .previewTable th {
        border: 1px solid {{ $borderColor }};
        padding: 6px;
        vertical-align: top;
    }

    #itemsTable th {
        background: {{ $secondaryColor }};
        text-align: left;
        color: {{ $primaryColor }};
    }

    .desc {
        font-size: 10px;
        color: {{ $mutedColor }};
    }

    #summaryTable {
        width: 100%;
    }

    #summaryTable .grand td {
        font-weight: 700;
        background: {{ $secondaryColor }};
        color: {{ $primaryColor }};
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
        const formBox = document.getElementById('formBox');
        const titleBox = document.getElementById('titleBox');
        const headName = document.getElementById('headName');

        preview.style.backgroundColor = softBgColor.value;
        preview.style.color = textColor.value;
        preview.style.fontFamily = `'${fontFamily.value}', DejaVu Sans, sans-serif`;

        formBox.style.backgroundColor = lightBgColor.value;
        formBox.style.border = `2px solid ${borderColor.value}`;

        titleBox.style.backgroundColor = accentColor.value;
        titleBox.style.borderBottom = `2px solid ${borderColor.value}`;
        titleBox.style.color = primaryColor.value;

        headName.style.color = primaryColor.value;

        document.querySelectorAll('.previewTable td, .previewTable th').forEach(el => {
            el.style.border = `1px solid ${borderColor.value}`;
        });

        document.querySelectorAll('#itemsTable th').forEach(th => {
            th.style.backgroundColor = secondaryColor.value;
            th.style.color = primaryColor.value;
        });

        document.querySelectorAll('.desc').forEach(el => {
            el.style.color = mutedColor.value;
        });

        document.querySelectorAll('#summaryTable .grand td').forEach(td => {
            td.style.backgroundColor = secondaryColor.value;
            td.style.color = primaryColor.value;
            td.style.fontWeight = '700';
        });

        document.getElementById('logoBox').style.display = showLogo.checked ? 'block' : 'none';
        document.getElementById('logoBox').style.border = `1px dashed ${borderColor.value}`;
        document.getElementById('logoBox').style.color = mutedColor.value;

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