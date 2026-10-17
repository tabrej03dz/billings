@php
    // Same original defaults as real PDF file
    $primaryColor   = $setting->primary_color ?? '#111827';
    $textColor      = $setting->text_color ?? '#111827';
    $mutedColor     = $setting->muted_color ?? '#6b7280';
    $borderColor    = $setting->border_color ?? '#374151';
    $secondaryColor = $setting->secondary_color ?? '#d1d5db';
    $lightBgColor   = $setting->light_bg_color ?? '#9ca3af';
    $softBgColor    = $setting->soft_bg_color ?? '#ffffff';
    $fontFamily     = $setting->font_family ?? 'DejaVu Sans';

    $showLogo      = $setting->show_logo ?? true;
    $showSignature = $setting->show_signature ?? true;
    $showTerms     = $setting->show_terms ?? true;

    $templateId = $billTemplate->id ?? $template->id;
    $templateName = $billTemplate->name ?? $template->name ?? 'Sidebar Bill Template';
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
                <div class="p-3 rounded bg-green-50 text-green-700 border border-green-200 text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-3 rounded bg-red-50 text-red-700 border border-red-200 text-sm">{{ session('error') }}</div>
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

            <div><label class="text-sm font-semibold">Primary / Sidebar Color</label><input type="color" name="primary_color" id="primary_color" value="{{ $primaryColor }}" class="w-full h-11 mt-2"></div>
            <div><label class="text-sm font-semibold">Text Color</label><input type="color" name="text_color" id="text_color" value="{{ $textColor }}" class="w-full h-11 mt-2"></div>
            <div><label class="text-sm font-semibold">Muted Text Color</label><input type="color" name="muted_color" id="muted_color" value="{{ $mutedColor }}" class="w-full h-11 mt-2"></div>
            <div><label class="text-sm font-semibold">Main Border Color</label><input type="color" name="border_color" id="border_color" value="{{ $borderColor }}" class="w-full h-11 mt-2"></div>
            <div><label class="text-sm font-semibold">Inner Line Color</label><input type="color" name="secondary_color" id="secondary_color" value="{{ $secondaryColor }}" class="w-full h-11 mt-2"></div>
            <div><label class="text-sm font-semibold">Light Border Color</label><input type="color" name="light_bg_color" id="light_bg_color" value="{{ $lightBgColor }}" class="w-full h-11 mt-2"></div>
            <div><label class="text-sm font-semibold">Content Background</label><input type="color" name="soft_bg_color" id="soft_bg_color" value="{{ $softBgColor }}" class="w-full h-11 mt-2"></div>

            <div>
                <label class="text-sm font-semibold">Font Family</label>
                <select name="font_family" id="font_family" class="w-full border rounded-lg mt-2 px-3 py-2 dark:bg-neutral-800 dark:text-white">
                    <option value="DejaVu Sans" @selected($fontFamily == 'DejaVu Sans')>DejaVu Sans</option>
                    <option value="Arial" @selected($fontFamily == 'Arial')>Arial</option>
                    <option value="Times New Roman" @selected($fontFamily == 'Times New Roman')>Times New Roman</option>
                    <option value="Courier New" @selected($fontFamily == 'Courier New')>Courier New</option>
                </select>
            </div>

            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="show_logo" id="show_logo" value="1" @checked($showLogo)> Show Logo</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="show_signature" id="show_signature" value="1" @checked($showSignature)> Show Signature</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="show_terms" id="show_terms" value="1" @checked($showTerms)> Show Terms</label>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-lg font-semibold">Save Customization</button>

            <button type="submit" formaction="{{ route('bill-template.customize.reset', $templateId) }}" formmethod="POST" onclick="return confirm('Are you sure you want to reset this template customization?')" class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-lg font-semibold">Reset Default</button>

            <a href="{{ route('bill-templates.choose') }}" class="block text-center bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 rounded-lg font-semibold">Back</a>
        </div>

        <div class="xl:col-span-3 bg-gray-100 dark:bg-neutral-800 rounded-xl p-5 overflow-auto">
            <div id="invoicePreview" class="mx-auto shadow-xl" style="width:794px; min-height:1123px; display:table; table-layout:fixed; font-family:'{{ $fontFamily }}', DejaVu Sans, sans-serif; font-size:12px; color:{{ $textColor }};">

                <div id="sidebar" style="display:table-cell; width:28%; background:{{ $primaryColor }}; color:#fff; padding:22px 16px; vertical-align:top;">
                    <div id="logoBox" style="text-align:center; margin-bottom:18px; border:1px solid #fff; padding:18px 8px; font-size:12px;">LOGO</div>
                    <div style="font-size:22px; font-weight:bold; line-height:1.3; text-align:center; margin-bottom:18px;">{{ $business->name ?? $business->business_name ?? 'Real Victory Groups' }}</div>
                    <div id="sideLine" style="border-top:2px solid #fff; margin:14px 0 20px;"></div>
                    <div class="side-title">Address</div>
                    <div class="side-text">73 Basement, Ekta Enclave Society, Lakhanpur, Kanpur, Uttar Pradesh - 208024</div>
                    <div class="side-title">Contact</div>
                    <div class="side-text">Mobile: 7753800444<br>Email: info@realvictorygroups.com<br>GSTIN: 09ABCDE1234F1Z5</div>
                    <div class="side-title">Account Manager</div>
                    <div class="side-text">Demo Manager</div>
                </div>

                <div id="content" style="display:table-cell; width:72%; background:{{ $softBgColor }}; padding:35px 28px; vertical-align:top;">
                    <div id="docTitle" style="font-size:34px; font-weight:bold; color:{{ $primaryColor }}; letter-spacing:1px; border-bottom:3px solid {{ $primaryColor }}; padding-bottom:8px; margin-bottom:16px;">TAX INVOICE</div>

                    <table id="metaTable" style="width:100%; border-collapse:collapse;">
                        <tr><td><strong>Invoice No.:</strong> INV-0001</td><td><strong>Date:</strong> {{ now()->format('d/m/Y') }}</td></tr>
                    </table>

                    <div id="billBox" style="border:2px solid {{ $borderColor }}; padding:12px 14px; margin-top:16px; margin-bottom:16px; line-height:1.6;">
                        <div style="font-weight:bold; text-transform:uppercase; margin-bottom:6px;">Bill To</div>
                        <strong>DEMO CUSTOMER</strong><br>
                        Sample Market Road, Lucknow, Uttar Pradesh - 226001<br>
                        Mobile: 9876543210<br>GSTIN: 09XYZDE1234F1Z2<br>PAN: ABCDE1234F<br>Place: Uttar Pradesh
                    </div>

                    <table id="itemsTable" style="width:100%; border-collapse:collapse;">
                        <thead><tr><th>Service</th><th>HSN/SAC</th><th style="text-align:center;">Qty</th><th style="text-align:right;">Rate</th><th style="text-align:right;">Tax</th><th style="text-align:right;">Amount</th></tr></thead>
                        <tbody><tr><td><strong>Social Media Creative Package</strong><div class="desc">Monthly creative design package</div></td><td>998361</td><td style="text-align:center;">1</td><td style="text-align:right;">10000.00</td><td style="text-align:right;">1800.00</td><td style="text-align:right;">11800.00</td></tr></tbody>
                    </table>

                    <table id="summaryTable" style="width:48%; margin-left:auto; margin-top:18px; border-collapse:collapse; border:2px solid {{ $borderColor }};">
                        <tr><td>Taxable Amount</td><td style="text-align:right;">₹ 10000.00</td></tr>
                        <tr><td>CGST (9%)</td><td style="text-align:right;">₹ 900.00</td></tr>
                        <tr><td>SGST (9%)</td><td style="text-align:right;">₹ 900.00</td></tr>
                        <tr><td>Round Off</td><td style="text-align:right;">₹ 0.00</td></tr>
                        <tr><td>Balance</td><td style="text-align:right;">₹ 6800.00</td></tr>
                        <tr class="total"><td>Total</td><td style="text-align:right;">₹ 11800.00</td></tr>
                    </table>

                    <div style="margin-top:20px; line-height:1.6;"><strong>Total Amount (in words):</strong><br>Eleven Thousand Eight Hundred Rupees Only</div>

                    <div id="termsBox" style="text-align:left; margin-top:18px; line-height:1.6;"><strong>Terms & Conditions:</strong><br>Payment due within 7 days.<br>Goods once sold will not be returned.</div>

                    <div id="signatureBox" style="margin-top:35px; text-align:right;"><div style="height:50px;">Signature</div><div id="signLine" style="border-top:1px solid {{ $primaryColor }}; width:180px; margin-left:auto; padding-top:8px;">Authorised Signatory</div></div>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    .side-title{font-size:12px;font-weight:bold;text-transform:uppercase;margin-top:18px;margin-bottom:8px;}
    .side-text{font-size:11px;line-height:1.65;color:{{ $secondaryColor }};}
    #metaTable td{border:1px solid {{ $lightBgColor }};padding:8px 10px;font-size:12px;}
    #itemsTable th{background:{{ $primaryColor }};color:#fff;padding:9px 8px;font-size:12px;text-align:left;}
    #itemsTable td{border-bottom:1px solid {{ $secondaryColor }};padding:8px;vertical-align:top;}
    .desc{font-size:10px;color:{{ $mutedColor }};margin-top:3px;}
    #summaryTable td{padding:8px 10px;border-bottom:1px solid {{ $lightBgColor }};}
    #summaryTable .total td{background:{{ $primaryColor }};color:#fff;font-weight:bold;font-size:14px;}
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
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const docTitle = document.getElementById('docTitle');
    const metaTable = document.getElementById('metaTable');
    const billBox = document.getElementById('billBox');
    const itemsTable = document.getElementById('itemsTable');
    const summaryTable = document.getElementById('summaryTable');
    const signLine = document.getElementById('signLine');

    function updatePreview(){
        preview.style.color = textColor.value;
        preview.style.fontFamily = `'${fontFamily.value}', DejaVu Sans, sans-serif`;
        sidebar.style.backgroundColor = primaryColor.value;
        content.style.backgroundColor = softBgColor.value;
        docTitle.style.color = primaryColor.value;
        docTitle.style.borderBottom = `3px solid ${primaryColor.value}`;
        billBox.style.border = `2px solid ${borderColor.value}`;
        summaryTable.style.border = `2px solid ${borderColor.value}`;
        signLine.style.borderTop = `1px solid ${primaryColor.value}`;

        document.querySelectorAll('.side-text').forEach(el => el.style.color = secondaryColor.value);
        document.querySelectorAll('.desc').forEach(el => el.style.color = mutedColor.value);
        metaTable.querySelectorAll('td').forEach(td => td.style.border = `1px solid ${lightBgColor.value}`);
        itemsTable.querySelectorAll('thead th').forEach(th => th.style.backgroundColor = primaryColor.value);
        itemsTable.querySelectorAll('tbody td').forEach(td => td.style.borderBottom = `1px solid ${secondaryColor.value}`);
        summaryTable.querySelectorAll('td').forEach(td => td.style.borderBottom = `1px solid ${lightBgColor.value}`);
        summaryTable.querySelectorAll('.total td').forEach(td => { td.style.backgroundColor = primaryColor.value; td.style.color = '#fff'; });

        document.getElementById('logoBox').style.display = document.getElementById('show_logo').checked ? 'block' : 'none';
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
