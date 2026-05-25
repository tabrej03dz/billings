@php
    $primaryColor   = $setting->primary_color ?? '#d60000';
    $secondaryColor = $setting->secondary_color ?? '#dbd9d6';
    $textColor      = $setting->text_color ?? '#111111';
    $fontFamily     = $setting->font_family ?? 'DejaVu Sans';

    $showLogo      = $setting->show_logo ?? true;
    $showTagline   = $setting->show_tagline ?? true;
    $showSignature = $setting->show_signature ?? true;
    $showTerms     = $setting->show_terms ?? true;
@endphp

<x-layouts.app :title="__('Customize Bill Template')">
    <form method="POST" action="{{ route('bill-template.customize.save', $template) }}">
        @csrf

        <input type="hidden" name="template_id" value="{{ $billTemplate->id ?? $template->id }}">

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">

            {{-- LEFT CONTROLS --}}
            <div class="bg-white dark:bg-neutral-900 rounded-xl shadow p-5 space-y-5 h-fit">

                <div>
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                        Customize Template
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $billTemplate->name ?? $template->name }}
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
                    <label class="text-sm font-semibold">Primary Color</label>
                    <input type="color"
                           name="primary_color"
                           id="primary_color"
                           value="{{ $primaryColor }}"
                           class="w-full h-11 mt-2">
                </div>

                <div>
                    <label class="text-sm font-semibold">Bar Color</label>
                    <input type="color"
                           name="secondary_color"
                           id="secondary_color"
                           value="{{ $secondaryColor }}"
                           class="w-full h-11 mt-2">
                </div>

                <div>
                    <label class="text-sm font-semibold">Text Color</label>
                    <input type="color"
                           name="text_color"
                           id="text_color"
                           value="{{ $textColor }}"
                           class="w-full h-11 mt-2">
                </div>

                <div>
                    <label class="text-sm font-semibold">Font Family</label>
                    <select name="font_family"
                            id="font_family"
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
                    <input type="checkbox" name="show_tagline" id="show_tagline" value="1" @checked($showTagline)>
                    Show Tagline
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
                        formaction="{{ route('bill-template.customize.reset', $template->id) }}"
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
                     class="bg-white mx-auto shadow-xl text-[12px]"
                     style="
                        width:794px;
                        min-height:1123px;
                        padding:10px 12px 12px 12px;
                        color:{{ $textColor }};
                        font-family:'{{ $fontFamily }}', 'DejaVu Sans', sans-serif;
                     ">

                    {{-- HEADER --}}
                    <table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
                        <tr>
                            <td colspan="3" style="width:100%; vertical-align:top;">
                                <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span style="font-weight:700; font-size:11px;">
                                            TAX INVOICE
                                        </span>

                                        <span style="display:inline-block; border:1px solid #999; padding:2px 6px; font-size:11px; margin-left:6px; color:#333;">
                                            ORIGINAL FOR RECIPIENT
                                        </span>
                                    </div>

                                    <div id="taglineBox" style="font-weight:700; font-size:11px; text-align:right;">
                                        Think Outside The Box
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td id="logoBox" style="width:18%; vertical-align:top;">
                                <div style="height:75px; border:1px solid #ddd; display:flex; align-items:center; justify-content:center; font-size:11px;">
                                    LOGO
                                </div>
                            </td>

                            <td style="width:62%; vertical-align:top;">
                                <div id="companyName" style="font-size:24px; margin-top:6px; font-weight:700; color:{{ $primaryColor }};">
                                    {{ $business->name ?? $business->business_name ?? 'Real Victory Groups' }}
                                </div>

                                <div style="font-size:14.8px; margin-top:2px;">
                                    73 Basement, Ekta Enclave Society, Lakhanpur, Kanpur, Uttar Pradesh (208024)
                                </div>

                                <div style="font-size:12.8px; margin-top:2px;">
                                    <span style="font-weight:700;">Mobile:</span> 7753800444
                                    &nbsp;&nbsp;&nbsp;
                                    <span style="font-weight:700;">GSTIN:</span> 09ABCDE1234F1Z5
                                    <br>
                                    <span style="font-weight:700;">Email:</span> info@realvictorygroups.com
                                </div>
                            </td>

                            <td style="width:20%;"></td>
                        </tr>
                    </table>

                    <div id="mainLine" style="border-top:6px solid {{ $primaryColor }}; margin:0;"></div>

                    {{-- GREY BAR --}}
                    <div id="invoiceBar" style="background:{{ $secondaryColor }}; padding:8px 10px; margin:0; font-size:10.5px;">
                        <table style="width:100%; border-collapse:collapse;">
                            <tr>
                                <td style="font-weight:700; font-size:12px;">
                                    Invoice No.: <span>INV-0001</span>
                                </td>
                                <td style="text-align:right; font-weight:700; font-size:12px;">
                                    Invoice Date: <span>{{ now()->format('d/m/Y') }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    {{-- BILL TO --}}
                    <div style="font-size:12.5px; margin-bottom:8px; margin-top:8px;">
                        <div style="font-weight:700;">BILL TO</div>
                        <div style="font-size:10.5px; font-weight:700;">DEMO CUSTOMER</div>

                        <div style="font-size:12px;">
                            Sample Market Road, Lucknow, Uttar Pradesh, 226001
                        </div>

                        <div style="margin-top:2px; font-size:12.2px;">
                            <span style="font-weight:700;">Mobile:</span> 9876543210 <br>
                            <span style="font-weight:700;">GSTIN:</span> 09XYZDE1234F1Z2 <br>
                            <span style="font-weight:700;">PAN Number:</span> ABCDE1234F <br>
                            <span style="font-weight:700;">Place of Supply:</span> Uttar Pradesh
                        </div>
                    </div>

                    {{-- SERVICES --}}
                    <table id="serviceTable" style="width:100%; border-collapse:collapse; border-top:2px solid {{ $primaryColor }}; border-bottom:2px solid {{ $primaryColor }}; margin-top:6px; font-size:12px;">
                        <thead>
                            <tr>
                                <th style="width:52%; font-size:12px; text-align:left; padding:6px;">SERVICES</th>
                                <th style="width:10%; font-size:12px; text-align:left; padding:6px;">SAC</th>
                                <th style="width:10%; font-size:12px; text-align:left; padding:6px;">QTY.</th>
                                <th style="width:10%; font-size:12px; text-align:right; padding:6px;">RATE</th>
                                <th style="width:10%; font-size:12px; text-align:right; padding:6px;">TAX</th>
                                <th style="width:8%; font-size:12px; text-align:right; padding:6px;">AMOUNT</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td style="padding:6px; border-top:1px solid #cfcfcf;">
                                    <div style="font-weight:700;">Social Media Creative Package</div>
                                    <div style="font-size:10.3px; color:#555; margin-top:2px;">
                                        Monthly creative design package
                                    </div>
                                </td>
                                <td style="padding:6px; border-top:1px solid #cfcfcf;">998361</td>
                                <td style="padding:6px; border-top:1px solid #cfcfcf;">1</td>
                                <td style="padding:6px; border-top:1px solid #cfcfcf; text-align:right;">10000.00</td>
                                <td style="padding:6px; border-top:1px solid #cfcfcf; text-align:right;">
                                    1800.00
                                    <div style="font-size:12px;">(18%)</div>
                                </td>
                                <td style="padding:6px; border-top:1px solid #cfcfcf; text-align:right;">11800.00</td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- SUBTOTAL --}}
                    <table id="subtotalTable" style="width:100%; border-collapse:collapse; margin-top:14px; border-top:2px solid {{ $primaryColor }}; border-bottom:2px solid {{ $primaryColor }};">
                        <tr>
                            <td style="width:52%; padding:6px; font-weight:700;">SUBTOTAL</td>
                            <td style="width:10%; padding:6px; font-weight:700;">1</td>
                            <td style="width:8%; padding:6px;"></td>
                            <td style="width:10%; padding:6px;"></td>
                            <td style="width:10%; padding:6px; white-space:nowrap; text-align:right; font-weight:700;">₹ 1800.00</td>
                            <td style="width:10%; padding:6px; white-space:nowrap; text-align:right; font-weight:700;">₹ 11800.00</td>
                        </tr>
                    </table>

                    {{-- BOTTOM --}}
                    <div style="margin-top:10px; width:100%; display:block;">
                        <div id="termsBox" style="width:48%; float:left; font-size:12px;">
                            <div style="font-weight:700; margin-bottom:6px; font-size:12px;">
                                TERMS & CONDITIONS
                            </div>
                            <div style="line-height:1.4; font-size:12px;">
                                Payment due within 7 days. <br>
                                Goods once sold will not be returned.
                            </div>
                        </div>

                        <div style="width:52%; float:right;">
                            <table style="width:100%; border-collapse:collapse; font-size:12.5px;">
                                <tr>
                                    <td style="text-align:right; padding:4px 6px;">Taxable Amount</td>
                                    <td style="text-align:right; font-weight:700; width:120px; border-bottom:1px solid #666; padding:4px 6px;">₹ 10000.00</td>
                                </tr>

                                <tr>
                                    <td style="text-align:right; padding:4px 6px;">CGST</td>
                                    <td style="text-align:right; font-weight:700; width:120px; border-bottom:1px solid #666; padding:4px 6px;">₹ 900.00</td>
                                </tr>

                                <tr>
                                    <td style="text-align:right; padding:4px 6px;">SGST</td>
                                    <td style="text-align:right; font-weight:700; width:120px; border-bottom:1px solid #666; padding:4px 6px;">₹ 900.00</td>
                                </tr>

                                <tr>
                                    <td style="text-align:right; padding:4px 6px; font-weight:700;">Total Amount</td>
                                    <td style="text-align:right; font-weight:700; width:120px; border-bottom:1px solid #666; padding:4px 6px;">₹ 11800.00</td>
                                </tr>

                                <tr>
                                    <td style="text-align:right; padding:4px 6px;">Received Amount</td>
                                    <td style="text-align:right; font-weight:700; width:120px; border-bottom:1px solid #666; padding:4px 6px;">₹ 5000.00</td>
                                </tr>

                                <tr>
                                    <td style="text-align:right; padding:4px 6px; font-weight:700;">Balance</td>
                                    <td style="text-align:right; font-weight:700; width:120px; border-bottom:1px solid #666; padding:4px 6px;">₹ 6800.00</td>
                                </tr>
                            </table>

                            <div style="width:100%; margin-top:10px; font-size:12.2px; text-align:right;">
                                <div style="font-weight:700; margin-bottom:3px;">
                                    Total Amount (in words)
                                </div>
                                <div style="font-weight:700;">
                                    Eleven Thousand Eight Hundred Rupees
                                </div>
                            </div>

                            <div id="signatureBox" style="width:100%; margin-top:14px; text-align:right; font-size:10px;">
                                <div style="height:34px; margin-bottom:6px;">Signature</div>
                                <div style="font-weight:700; margin-top:6px; font-size:12px;">
                                    AUTHORISED SIGNATORY FOR
                                </div>
                                <div style="font-size:12px;">
                                    {{ $business->name ?? $business->business_name ?? 'Real Victory Groups' }}
                                </div>
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
        const secondaryColor = document.getElementById('secondary_color');
        const textColor = document.getElementById('text_color');
        const fontFamily = document.getElementById('font_family');

        const preview = document.getElementById('invoicePreview');
        const companyName = document.getElementById('companyName');
        const mainLine = document.getElementById('mainLine');
        const invoiceBar = document.getElementById('invoiceBar');
        const serviceTable = document.getElementById('serviceTable');
        const subtotalTable = document.getElementById('subtotalTable');

        function updatePreview() {
            preview.style.color = textColor.value;
            preview.style.fontFamily = `'${fontFamily.value}', 'DejaVu Sans', sans-serif`;

            companyName.style.color = primaryColor.value;
            mainLine.style.borderTop = `6px solid ${primaryColor.value}`;

            invoiceBar.style.backgroundColor = secondaryColor.value;

            serviceTable.style.borderTop = `2px solid ${primaryColor.value}`;
            serviceTable.style.borderBottom = `2px solid ${primaryColor.value}`;

            subtotalTable.style.borderTop = `2px solid ${primaryColor.value}`;
            subtotalTable.style.borderBottom = `2px solid ${primaryColor.value}`;

            document.getElementById('logoBox').style.display =
                document.getElementById('show_logo').checked ? 'table-cell' : 'none';

            document.getElementById('taglineBox').style.display =
                document.getElementById('show_tagline').checked ? 'block' : 'none';

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