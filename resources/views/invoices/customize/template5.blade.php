@php
    /*
    |--------------------------------------------------------------------------
    | Third template defaults
    |--------------------------------------------------------------------------
    | Ye defaults real PDF third template ke original colors ke same hain.
    | Agar database me setting na mile to customize page me bhi yahi colors aayenge.
    */
    $primaryColor   = $setting->primary_color ?? '#7c3aed';
    $textColor      = $setting->text_color ?? '#0f172a';
    $mutedColor     = $setting->muted_color ?? '#64748b';
    $borderColor    = $setting->border_color ?? '#cbd5e1';
    $secondaryColor = $setting->secondary_color ?? '#e5e7eb';
    $lightBgColor   = $setting->light_bg_color ?? '#ede9fe';
    $softBgColor    = $setting->soft_bg_color ?? '#f8fafc';
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
                <label class="text-sm font-semibold">Primary Color</label>
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
                <label class="text-sm font-semibold">Table Border Color</label>
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

                <div id="pageBox" style="background:#fff; border:1px solid {{ $secondaryColor }}; padding:16px;">
                    <table id="headerBox" style="width:100%; border-collapse:collapse; border:1px solid {{ $borderColor }}; margin-bottom:14px;">
                        <tr>
                            <td style="width:70%; vertical-align:top; padding:12px;">
                                <div id="companyName" style="font-size:22px; font-weight:700; color:{{ $primaryColor }};">
                                    {{ $business->name ?? $business->business_name ?? 'Real Victory Groups' }}
                                </div>
                                <div>73 Basement, Ekta Enclave Society, Lakhanpur, Kanpur, Uttar Pradesh - 208024</div>
                                <div style="margin-top:6px;">Mobile: 7753800444</div>
                                <div>Email: info@realvictorygroups.com</div>
                                <div>GSTIN: 09ABCDE1234F1Z5</div>
                            </td>
                            <td style="width:30%; text-align:right; padding:12px; vertical-align:top;">
                                <div id="logoBox"
                                     style="width:100px; height:55px; border:1px solid {{ $borderColor }}; display:inline-flex; align-items:center; justify-content:center; font-size:11px; margin-bottom:10px;">
                                    LOGO
                                </div>
                                <div id="docType" style="background:{{ $primaryColor }}; color:#fff; padding:10px; text-align:center; font-size:18px; font-weight:700;">
                                    TAX
                                </div>
                            </td>
                        </tr>
                    </table>

                    <table id="subBox" style="width:100%; border-collapse:collapse; border:1px solid {{ $borderColor }}; margin-bottom:14px;">
                        <tr>
                            <td style="width:50%; padding:10px; vertical-align:top; border-right:1px solid {{ $borderColor }};">
                                <div class="preview-label">Bill To</div>
                                <strong>DEMO CUSTOMER</strong><br>
                                Sample Market Road, Lucknow, Uttar Pradesh - 226001
                            </td>
                            <td style="width:25%; padding:10px; vertical-align:top; border-right:1px solid {{ $borderColor }};">
                                <div class="preview-label">Invoice No</div>
                                <strong>INV-0001</strong><br><br>
                                <div class="preview-label">Date</div>
                                <strong>{{ now()->format('d/m/Y') }}</strong>
                            </td>
                            <td style="width:25%; padding:10px; vertical-align:top;">
                                <div class="preview-label">Customer Info</div>
                                Mobile: 9876543210<br>
                                GSTIN: 09XYZDE1234F1Z2<br>
                                PAN: ABCDE1234F<br>
                                POS: Uttar Pradesh
                            </td>
                        </tr>
                    </table>

                    <table id="itemsTable" style="width:100%; border-collapse:collapse;">
                        <thead>
                        <tr>
                            <th style="width:34%;">Service Details</th>
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
                                <div class="preview-small">Monthly creative design package</div>
                            </td>
                            <td>998361</td>
                            <td>1</td>
                            <td style="text-align:right;">10000.00</td>
                            <td style="text-align:right;">1800.00<div class="preview-small">(18%)</div></td>
                            <td style="text-align:right;">11800.00</td>
                        </tr>
                        </tbody>
                    </table>

                    <table id="totalsTable" style="width:44%; margin-left:auto; margin-top:14px; border-collapse:collapse;">
                        <tr><td>Taxable Amount</td><td style="text-align:right;">₹ 10000.00</td></tr>
                        <tr><td>CGST</td><td style="text-align:right;">₹ 900.00</td></tr>
                        <tr><td>SGST</td><td style="text-align:right;">₹ 900.00</td></tr>
                        <tr><td>Received Amount</td><td style="text-align:right;">₹ 5000.00</td></tr>
                        <tr><td>Balance</td><td style="text-align:right;">₹ 6800.00</td></tr>
                        <tr class="preview-grand"><td>Total Amount</td><td style="text-align:right;">₹ 11800.00</td></tr>
                    </table>

                    <table style="width:100%; border-collapse:collapse; margin-top:16px;">
                        <tr>
                            <td id="termsBox" style="width:60%; vertical-align:top;">
                                <div class="preview-label">Amount in Words</div>
                                <strong>Eleven Thousand Eight Hundred Rupees</strong>

                                <div class="preview-label" style="margin-top:16px;">Terms & Conditions</div>
                                Payment due within 7 days.<br>Goods once sold will not be returned.
                            </td>

                            <td id="signatureBox" style="width:40%; text-align:right; vertical-align:top;">
                                <div style="height:50px;">Signature</div>
                                <div class="preview-label">Authorised Signatory</div>
                                <strong>{{ $business->name ?? $business->business_name ?? 'Real Victory Groups' }}</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    .preview-label{
        font-size:10px;
        text-transform:uppercase;
        color:{{ $mutedColor }};
        font-weight:700;
        margin-bottom:4px;
    }

    .preview-small{
        font-size:10px;
        color:{{ $mutedColor }};
    }

    #itemsTable th{
        background:{{ $lightBgColor }};
        color:{{ $primaryColor }};
        padding:8px;
        text-align:left;
        border:1px solid {{ $secondaryColor }};
    }

    #itemsTable td{
        padding:8px;
        border:1px solid {{ $secondaryColor }};
        vertical-align:top;
    }

    #totalsTable td{
        border:1px solid {{ $borderColor }};
        padding:8px;
    }

    #totalsTable .preview-grand td{
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
    const pageBox = document.getElementById('pageBox');
    const companyName = document.getElementById('companyName');
    const docType = document.getElementById('docType');
    const logoBox = document.getElementById('logoBox');
    const headerBox = document.getElementById('headerBox');
    const subBox = document.getElementById('subBox');
    const itemsTable = document.getElementById('itemsTable');
    const totalsTable = document.getElementById('totalsTable');

    function updatePreview() {
        preview.style.color = textColor.value;
        preview.style.backgroundColor = softBgColor.value;
        preview.style.fontFamily = `'${fontFamily.value}', 'DejaVu Sans', sans-serif`;

        pageBox.style.border = `1px solid ${secondaryColor.value}`;
        headerBox.style.border = `1px solid ${borderColor.value}`;
        subBox.style.border = `1px solid ${borderColor.value}`;

        subBox.querySelectorAll('td').forEach((td, index) => {
            td.style.borderRight = index < 2 ? `1px solid ${borderColor.value}` : 'none';
        });

        companyName.style.color = primaryColor.value;
        docType.style.backgroundColor = primaryColor.value;
        docType.style.color = '#ffffff';

        document.querySelectorAll('.preview-label, .preview-small').forEach(el => {
            el.style.color = mutedColor.value;
        });

        logoBox.style.border = `1px solid ${borderColor.value}`;

        itemsTable.querySelectorAll('thead th').forEach(th => {
            th.style.backgroundColor = lightBgColor.value;
            th.style.color = primaryColor.value;
            th.style.border = `1px solid ${secondaryColor.value}`;
        });

        itemsTable.querySelectorAll('tbody td').forEach(td => {
            td.style.border = `1px solid ${secondaryColor.value}`;
        });

        totalsTable.querySelectorAll('td').forEach(td => {
            td.style.border = `1px solid ${borderColor.value}`;
        });

        totalsTable.querySelectorAll('.preview-grand td').forEach(td => {
            td.style.backgroundColor = primaryColor.value;
            td.style.color = '#ffffff';
        });

        document.getElementById('logoBox').style.display =
            document.getElementById('show_logo').checked ? 'inline-flex' : 'none';

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
