@php
    $inv = $inv ?? $invoice;
    $biz = $biz ?? $business ?? $inv->business;
    $client = $client ?? $inv->client;
    $items = collect($items ?? $inv->items ?? []);
    $s = $pdfSettings ?? [];

    $setting = static function (string $key, $default = null) use ($s) {
        return array_key_exists($key, $s) ? $s[$key] : $default;
    };
    $enabled = static function (string $key, bool $default = true) use ($setting): bool {
        return filter_var($setting($key, $default), FILTER_VALIDATE_BOOLEAN);
    };
    $first = static function ($source, array $keys, $default = null) {
        foreach ($keys as $key) {
            $value = data_get($source, $key);
            if ($value !== null && $value !== '') return $value;
        }
        return $default;
    };
    $money = static fn ($value): string => number_format((float) $value, 2, '.', '');
    $date = static function ($value): string {
        if (!$value) return '-';
        try { return \Carbon\Carbon::parse($value)->format('d/m/Y'); }
        catch (\Throwable $e) { return (string) $value; }
    };

    $invoiceNo = $first($inv, ['invoice_number', 'invoice_no'], '-');
    $invoiceDate = $first($inv, ['invoice_date', 'date', 'created_at']);
    $docType = strtolower((string) $first($inv, ['invoice_type', 'type'], 'invoice'));
    $gstInvoice = trim((string) $first($biz, ['gstin'], $first($inv, ['gst_no'], ''))) !== '';
    $title = $setting('invoice_title') ?: match ($docType) {
        'quotation' => 'QUOTATION',
        'proforma' => 'PROFORMA INVOICE',
        default => $gstInvoice ? 'TAX INVOICE' : 'INVOICE',
    };

    $businessName = $first($biz, ['name', 'business_name'], 'Business');
    $businessAddress = $first($biz, ['address', 'full_address'], '');
    $businessCity = $first($biz, ['city'], '');
    $businessState = $first($biz, ['state.name', 'state'], '');
    $businessPincode = $first($biz, ['pincode', 'pin_code', 'postal_code'], '');
    $businessPhone = $first($biz, ['mobile', 'phone'], '');
    $businessEmail = $first($biz, ['email'], '');
    $businessGstin = $first($biz, ['gstin'], $first($inv, ['gst_no'], ''));
    $businessPan = $first($biz, ['pan', 'pan_number'], '');

    $clientName = $first($client, ['name', 'company_name'], '-');
    $clientAddress = $first($client, ['billing_address', 'address'], '');
    $clientCity = $first($client, ['city'], '');
    $clientState = $first($client, ['state.name', 'state'], '');
    $clientPincode = $first($client, ['pincode', 'pin_code', 'postal_code'], '');
    $clientPhone = $first($client, ['mobile', 'phone'], '');
    $clientEmail = $first($client, ['email'], '');
    $clientGstin = $first($client, ['gstin', 'gst_number'], '');
    $clientStateCode = $first($client, ['state_code', 'gst_state_code'], '');

    $shippingName = $first($inv, ['shipping_name', 'delivery_name'], $clientName);
    $shippingAddress = $first($inv, ['shipping_address', 'delivery_address'], $clientAddress);
    $shippingCity = $first($inv, ['shipping_city', 'delivery_city'], $clientCity);
    $shippingState = $first($inv, ['shipping_state', 'delivery_state'], $clientState);
    $shippingPincode = $first($inv, ['shipping_pincode', 'delivery_pincode'], $clientPincode);
    $shippingGstin = $first($inv, ['shipping_gstin', 'delivery_gstin'], $clientGstin);
    $shippingStateCode = $first($inv, ['shipping_state_code', 'delivery_state_code'], $clientStateCode);

    // $logo = $logo ?? $first($biz, ['logo', 'logo_path'], null);
    // $signature = $sign ?? $first($biz, ['signature', 'signature_path'], null);

    // $resolveImage = static function ($path) {
    //     if (!$path) return null;
    //     if (str_starts_with((string) $path, 'data:') || preg_match('~^https?://~i', (string) $path)) return $path;
    //     $clean = ltrim((string) $path, '/');
    //     foreach ([public_path($clean), storage_path('app/public/' . preg_replace('~^storage/~', '', $clean))] as $candidate) {
    //         if (is_file($candidate)) return $candidate;
    //     }
    //     return null;
    // };





     /*
    |--------------------------------------------------------------------------
    | Business Logo + Signature
    |--------------------------------------------------------------------------
    */

    $logo = $logo
        ?? $first($biz, [
            'logo',
            'business_logo',
            'logo_path',
        ], null);

    $signature = $sign
        ?? $first($biz, [
            'signature',
            'signature_path',
        ], null);


    /*
    |--------------------------------------------------------------------------
    | Robust PDF Image Resolver
    |--------------------------------------------------------------------------
    |
    | Supported DB values:
    |
    | logo.png
    | business/logo.png
    | storage/business/logo.png
    | public/storage/business/logo.png
    | /storage/business/logo.png
    | http://...
    | https://...
    | data:image/...
    |
    */

    $resolveImage = static function ($path) {

        if (empty($path)) {
            return null;
        }

        $path = trim((string) $path);

        /*
        |--------------------------------------------------------------------------
        | Base64 image
        |--------------------------------------------------------------------------
        */
        if (str_starts_with($path, 'data:')) {
            return $path;
        }

        /*
        |--------------------------------------------------------------------------
        | Remote URL
        |--------------------------------------------------------------------------
        */
        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize slashes
        |--------------------------------------------------------------------------
        */
        $cleanPath = ltrim(
            str_replace('\\', '/', $path),
            '/'
        );


        /*
        |--------------------------------------------------------------------------
        | Possible Paths
        |--------------------------------------------------------------------------
        */

        $possiblePaths = [];


        /*
        |--------------------------------------------------------------------------
        | Case 1:
        | storage/business/logo.png
        |--------------------------------------------------------------------------
        */
        if (str_starts_with($cleanPath, 'storage/')) {

            $withoutStorage = substr(
                $cleanPath,
                strlen('storage/')
            );

            $possiblePaths[] = public_path(
                'storage/' . $withoutStorage
            );

            $possiblePaths[] = storage_path(
                'app/public/' . $withoutStorage
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Case 2:
        | public/storage/business/logo.png
        |--------------------------------------------------------------------------
        */
        elseif (str_starts_with($cleanPath, 'public/storage/')) {

            $withoutPublicStorage = substr(
                $cleanPath,
                strlen('public/storage/')
            );

            $possiblePaths[] = public_path(
                'storage/' . $withoutPublicStorage
            );

            $possiblePaths[] = storage_path(
                'app/public/' . $withoutPublicStorage
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Case 3:
        | public/business/logo.png
        |--------------------------------------------------------------------------
        */
        elseif (str_starts_with($cleanPath, 'public/')) {

            $withoutPublic = substr(
                $cleanPath,
                strlen('public/')
            );

            $possiblePaths[] = public_path(
                $withoutPublic
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Case 4:
        | business/logo.png
        |--------------------------------------------------------------------------
        */
        else {

            // public/business/logo.png
            $possiblePaths[] = public_path(
                $cleanPath
            );

            // public/storage/business/logo.png
            $possiblePaths[] = public_path(
                'storage/' . $cleanPath
            );

            // storage/app/public/business/logo.png
            $possiblePaths[] = storage_path(
                'app/public/' . $cleanPath
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Already absolute server path
        |--------------------------------------------------------------------------
        */
        if (
            is_file($path)
            && file_exists($path)
        ) {
            return $path;
        }


        /*
        |--------------------------------------------------------------------------
        | Find Existing File
        |--------------------------------------------------------------------------
        */
        foreach ($possiblePaths as $candidate) {

            if (
                is_string($candidate)
                && file_exists($candidate)
                && is_file($candidate)
            ) {
                return $candidate;
            }
        }


        return null;
    };


    /*
    |--------------------------------------------------------------------------
    | Resolve Images
    |--------------------------------------------------------------------------
    */

    $logo = $resolveImage($logo);

    $signature = $resolveImage($signature);



    $logo = $resolveImage($logo);
    $signature = $resolveImage($signature);

    $taxableTotal = 0.0;
    $taxTotal = 0.0;
    $calculatedTotal = 0.0;
    $rows = [];

    foreach ($items as $index => $item) {
        $qty = (float) $first($item, ['quantity', 'qty'], 1);
        if ($qty <= 0) $qty = 1;

        $taxRate = (float) $first($item, ['tax_percent', 'tax_rate', 'gst_rate'], $first($inv, ['tax_percent'], 0));

        /*
         |------------------------------------------------------------------
         | Correct price calculation
         |------------------------------------------------------------------
         | `rate` is deliberately NOT the first choice. In the current DB it
         | can contain a calculated line value instead of one-unit price.
         |
         | Source priority:
         | 1. Invoice item's price/unit_price (actual form RATE / PRICE)
         | 2. Saved taxable amount divided by quantity
         | 3. GST-inclusive saved amount converted back to taxable/unit price
         | 4. Legacy rate only as the final fallback
         */
        $unitPrice = (float) $first($item, [
            'price',
            'unit_price',
            'sale_price',
            'item_price',
            'price_per_unit',
        ], 0);

        $savedTaxable = (float) $first($item, [
            'taxable_amount',
            'subtotal',
            'base_amount',
        ], 0);

        $savedLineTotal = (float) $first($item, [
            'amount',
            'line_total',
            'total',
            'total_amount',
        ], 0);

        if ($unitPrice <= 0 && $savedTaxable > 0) {
            $unitPrice = $savedTaxable / $qty;
        }

        if ($unitPrice <= 0 && $savedLineTotal > 0) {
            $gstFactor = 1 + ($taxRate / 100);
            $unitPrice = ($savedLineTotal / $gstFactor) / $qty;
        }

        if ($unitPrice <= 0) {
            $unitPrice = (float) $first($item, ['rate'], 0);
        }

        // Always rebuild the row using the actual per-unit price.
        $taxable = round($unitPrice * $qty, 2);
        $taxAmount = round($taxable * $taxRate / 100, 2);
        $lineTotal = round($taxable + $taxAmount, 2);

        $taxableTotal += $taxable;
        $taxTotal += $taxAmount;
        $calculatedTotal += $lineTotal;
        $rows[] = [
            'index' => $index + 1,
            'name' => $first($item, ['item.name', 'item_name', 'name', 'description'], 'Item'),
            'description' => $first($item, ['description'], ''),
            'hsn' => $first($item, ['hsn_code', 'hsn', 'sac_code', 'item.hsn_code'], '-'),
            'qty' => $qty,
            'unit' => $first($item, ['unit', 'item.unit'], ''),
            'rate' => $unitPrice,
            'tax_rate' => $taxRate,
            'taxable' => $taxable,
            'tax' => $taxAmount,
            'total' => $lineTotal,
        ];
    }

    $subtotal = (float) $first($inv, ['subtotal', 'taxable_amount'], $taxableTotal);
    if ($subtotal <= 0) $subtotal = $taxableTotal;
    $discount = (float) $first($inv, ['discount_total', 'discount_amount', 'discount'], 0);
    $cgst = (float) $first($inv, ['cgst_amount', 'cgst'], 0);
    $sgst = (float) $first($inv, ['sgst_amount', 'sgst'], 0);
    $igst = (float) $first($inv, ['igst_amount', 'igst'], 0);
    if (($cgst + $sgst + $igst) <= 0) {
        $sameState = $businessState && $clientState && mb_strtolower((string) $businessState) === mb_strtolower((string) $clientState);
        if ($sameState) { $cgst = $taxTotal / 2; $sgst = $taxTotal / 2; }
        else { $igst = $taxTotal; }
    }
    $roundOff = (float) $first($inv, ['round_off', 'rounding_amount'], 0);
    $grandTotal = (float) $first($inv, ['grand_total', 'total', 'total_amount'], $calculatedTotal - $discount + $roundOff);
    if ($grandTotal <= 0) $grandTotal = $calculatedTotal - $discount + $roundOff;

    $numberWords = static function ($amount): string {
        $amount = round((float) $amount, 2);
        $rupees = (int) floor($amount);
        $paise = (int) round(($amount - $rupees) * 100);
        $formatter = class_exists(\NumberFormatter::class) ? new \NumberFormatter('en_IN', \NumberFormatter::SPELLOUT) : null;
        $words = $formatter ? $formatter->format($rupees) : number_format($rupees, 0, '.', ',');
        $result = ucwords((string) $words) . ' Rupees';
        if ($paise > 0) {
            $p = $formatter ? $formatter->format($paise) : (string) $paise;
            $result .= ' And ' . ucwords((string) $p) . ' Paise';
        }
        return $result . ' Only';
    };
    $amountInWords = $first($inv, ['amount_in_words'], '') ?: $numberWords($grandTotal);

    $bank = $selectedBank ?? null;
    $bankName = $first($bank, ['bank_name', 'name'], '');
    $accountHolder = $first($bank, ['account_holder', 'account_name'], $businessName);
    $accountNo = $first($bank, ['account_no', 'account_number'], '');
    $ifsc = $first($bank, ['ifsc', 'ifsc_code'], '');
    $branch = $first($bank, ['branch', 'branch_name'], '');
    $upi = $first($bank, ['upi_id'], '');
    $hasBank = $bankName || $accountNo || $ifsc || $branch || $upi;

    $notes = $first($inv, ['notes', 'note'], '');
    $terms = $first($inv, ['terms', 'terms_and_conditions'], '');
    $primary = $setting('primary_color', '#111111');
    $border = $setting('border_color', '#555555');
    $headBg = $setting('header_background', '#eeeeee');
    $fontSize = max(7, min(12, (int) $setting('font_size', 9)));
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }} - {{ $invoiceNo }}</title>
    <style>
        @page { margin: 8mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: {{ $primary }}; font-family: dejavusans, sans-serif; font-size: {{ $fontSize }}px; }
        table { width: 100%; border-collapse: collapse; }
        .sheet { border: 0.35mm solid {{ $border }}; }
        .title { text-align: center; font-size: 15px; font-weight: bold; padding: 3px 2px; border-bottom: 0.25mm solid {{ $border }}; }
        .cell { border-right: 0.25mm solid {{ $border }}; border-bottom: 0.25mm solid {{ $border }}; padding: 4px 5px; vertical-align: top; line-height: 1.45; }
        .last { border-right: 0; }
        .seller-name { font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .label { font-weight: bold; }
        .party-title { font-size: 10px; font-weight: bold; text-decoration: underline; }
        .party-name { font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .items th { background: {{ $headBg }}; font-weight: bold; text-align: center; border-right: 0.25mm solid {{ $border }}; border-bottom: 0.25mm solid {{ $border }}; padding: 4px 2px; }
        .items td { border-right: 0.25mm solid {{ $border }}; border-bottom: 0.25mm solid {{ $border }}; padding: 4px 3px; vertical-align: top; }
        .items .last { border-right: 0; }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .muted { font-size: 8px; color: #333; }
        .summary-label { text-align: right; font-weight: bold; font-style: italic; }
        .grand td { font-weight: bold; background: {{ $headBg }}; }
        .words, .note { padding: 5px; border-bottom: 0.25mm solid {{ $border }}; line-height: 1.5; }
        .footer td { vertical-align: top; padding: 6px; }
        .bank-cell { width: 44%; border-right: 0.25mm solid {{ $border }}; }
        .sign-cell { width: 56%; text-align: center; }
        .signature { max-height: 38px; max-width: 150px; margin-top: 15px; }
        .signature-space { height: 42px; }
        .logo { max-width: 70px; max-height: 45px; float: left; margin-right: 7px; }
    </style>
</head>
<body>
<div class="sheet">
    <div class="title">{{ $title }}</div>

    <table>
        <tr>
            <td class="cell" style="width:58%;">
                @if($enabled('show_logo') && $logo)<img class="logo" src="{{ $logo }}">@endif
                <div class="seller-name">M/s. {{ $businessName }}</div>
                @if($enabled('show_business_address') && ($businessAddress || $businessCity || $businessState || $businessPincode))
                    {{ implode(', ', array_filter([$businessAddress, $businessCity, $businessState, $businessPincode])) }}<br>
                @endif
                @if($enabled('show_business_gstin') && $businessGstin)<span class="label">GSTIN:</span> {{ $businessGstin }}<br>@endif
                @if($businessPan)<span class="label">PAN:</span> {{ $businessPan }}<br>@endif
                @if($enabled('show_business_phone') && $businessPhone)<span class="label">Mob No:</span> {{ $businessPhone }}@endif
                @if($enabled('show_business_email') && $businessEmail)<br><span class="label">Email:</span> {{ $businessEmail }}@endif
            </td>
            <td class="cell last" style="width:42%; padding:0;">
                <table>
                    <tr><td class="cell" style="width:38%;"><span class="label">Invoice No.</span></td><td class="cell last">{{ $invoiceNo }}</td></tr>
                    <tr><td class="cell"><span class="label">Date</span></td><td class="cell last">{{ $date($invoiceDate) }}</td></tr>
                    @if($first($inv, ['reference_no', 'po_number'], ''))
                        <tr><td class="cell"><span class="label">Reference</span></td><td class="cell last">{{ $first($inv, ['reference_no', 'po_number']) }}</td></tr>
                    @endif
                </table>
            </td>
        </tr>
        <tr>
            <td class="cell" style="width:50%;">
                <div class="party-title">Bill To:</div>
                <div class="party-name">{{ $clientName }}</div>
                {{ implode(', ', array_filter([$clientAddress, $clientCity, $clientState, $clientPincode])) }}<br>
                @if($clientGstin)<span class="label">GSTIN:</span> {{ $clientGstin }}<br>@endif
                @if($clientState)<span class="label">State Name:</span> {{ $clientState }}@if($clientStateCode), Code: {{ $clientStateCode }}@endif<br>@endif
                @if($clientPhone)<span class="label">Contact:</span> {{ $clientPhone }}<br>@endif
                @if($clientEmail)<span class="label">E-Mail:</span> {{ $clientEmail }}@endif
            </td>
            <td class="cell last" style="width:50%;">
                @if($enabled('show_shipping_address'))
                    <div class="party-title">Delivered To:</div>
                    <div class="party-name">{{ $shippingName }}</div>
                    {{ implode(', ', array_filter([$shippingAddress, $shippingCity, $shippingState, $shippingPincode])) }}<br>
                    @if($shippingGstin)<span class="label">GSTIN:</span> {{ $shippingGstin }}<br>@endif
                    @if($shippingState)<span class="label">State Name:</span> {{ $shippingState }}@if($shippingStateCode), Code: {{ $shippingStateCode }}@endif@endif
                @endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
        <tr>
            <th style="width:4%;">Sr.<br>No.</th>
            <th style="width:27%;">Description of Goods</th>
            @if($enabled('show_hsn'))<th style="width:9%;">HSN/SAC</th>@endif
            <th style="width:7%;">Qty</th>
            @if($enabled('show_tax_columns'))<th style="width:7%;">GST<br>Rate</th>@endif
            <th style="width:11%;">Rate / Unit</th>
            <th style="width:12%;">Taxable Amt</th>
            @if($enabled('show_tax_columns'))<th style="width:10%;">GST Amt</th>@endif
            <th class="last" style="width:13%;">Total Amt</th>
        </tr>
        </thead>
        <tbody>
        @forelse($rows as $row)
            <tr>
                <td class="center">{{ $row['index'] }}</td>
                <td><span class="bold">{{ $row['name'] }}</span>@if($row['description'] && $row['description'] !== $row['name'])<br><span class="muted">{{ $row['description'] }}</span>@endif</td>
                @if($enabled('show_hsn'))<td class="center">{{ $row['hsn'] }}</td>@endif
                <td class="right">{{ rtrim(rtrim(number_format($row['qty'], 3, '.', ''), '0'), '.') }} {{ $row['unit'] }}</td>
                @if($enabled('show_tax_columns'))<td class="right">{{ $money($row['tax_rate']) }}%</td>@endif
                <td class="right">{{ $money($row['rate']) }}</td>
                <td class="right">{{ $money($row['taxable']) }}</td>
                @if($enabled('show_tax_columns'))<td class="right">{{ $money($row['tax']) }}</td>@endif
                <td class="right last">{{ $money($row['total']) }}</td>
            </tr>
        @empty
            <tr><td colspan="9" class="center last">No item found</td></tr>
        @endforelse

        @php
            // Columns before "Taxable Amt": Sr, Description, Qty, Rate,
            // plus optional HSN and GST Rate.
            $summaryLabelCols = 4
                + ($enabled('show_hsn') ? 1 : 0)
                + ($enabled('show_tax_columns') ? 1 : 0);

            // All visible columns except the final Total Amount column.
            $totalLabelCols = $summaryLabelCols
                + 1
                + ($enabled('show_tax_columns') ? 1 : 0);
        @endphp
        <tr>
            <td colspan="{{ $summaryLabelCols }}" class="summary-label">Taxable Amount</td>
            <td class="right">{{ $money($subtotal) }}</td>
            @if($enabled('show_tax_columns'))<td class="right">{{ $money($cgst + $sgst + $igst) }}</td>@endif
            <td class="right last">{{ $money($subtotal + $cgst + $sgst + $igst) }}</td>
        </tr>
        @if($discount > 0)<tr><td colspan="{{ $totalLabelCols }}" class="summary-label">Discount</td><td class="right last">- {{ $money($discount) }}</td></tr>@endif
        @if($cgst > 0)<tr><td colspan="{{ $totalLabelCols }}" class="summary-label">CGST</td><td class="right last">{{ $money($cgst) }}</td></tr>@endif
        @if($sgst > 0)<tr><td colspan="{{ $totalLabelCols }}" class="summary-label">SGST</td><td class="right last">{{ $money($sgst) }}</td></tr>@endif
        @if($igst > 0)<tr><td colspan="{{ $totalLabelCols }}" class="summary-label">IGST</td><td class="right last">{{ $money($igst) }}</td></tr>@endif
        @if($roundOff != 0)<tr><td colspan="{{ $totalLabelCols }}" class="summary-label">Round Off</td><td class="right last">{{ $money($roundOff) }}</td></tr>@endif
        <tr class="grand"><td colspan="{{ $totalLabelCols }}" class="summary-label">Grand Total</td><td class="right last">&#8377; {{ $money($grandTotal) }}</td></tr>
        </tbody>
    </table>

    <div class="words"><span class="label">Amount Chargeable (in words):</span><br><span class="bold">{{ strtoupper($amountInWords) }}</span></div>

    @if($enabled('show_notes') && $notes)<div class="note"><span class="label">Note:</span> {!! nl2br(e($notes)) !!}</div>@endif
    @if($enabled('show_terms') && $terms)<div class="note"><span class="label">Terms:</span> {!! nl2br(e($terms)) !!}</div>@endif

    <table class="footer">
        <tr>
            <td class="bank-cell">
                @if($enabled('show_bank_details') && $hasBank)
                    <span class="bold">COMPANY NAME: {{ strtoupper($accountHolder) }}</span><br>
                    @if($bankName)<span class="label">BANK:</span> {{ strtoupper($bankName) }}<br>@endif
                    @if($accountNo)<span class="label">A/C:</span> {{ $accountNo }}@if($ifsc) &nbsp; <span class="label">IFSC:</span> {{ $ifsc }}@endif<br>@endif
                    @if($branch)<span class="label">BRANCH:</span> {{ strtoupper($branch) }}<br>@endif
                    @if($upi)<span class="label">UPI:</span> {{ $upi }}@endif
                @endif
            </td>
            {{-- <td class="sign-cell">
                <div class="bold">For - M/s. {{ strtoupper($businessName) }}</div>
                @if($enabled('show_signature') && $signature)<img class="signature" src="{{ $signature }}">@else<div class="signature-space"></div>@endif
                <div class="bold">Authorised Signatory</div>
            </td> --}}

            <td class="sign-cell">

                @if($enabled('show_signature') && $signature)
                    <img class="signature" src="{{ $signature }}">
                @else
                    <div class="signature-space"></div>
                @endif

                <div class="bold">
                    For - M/s. {{ strtoupper($businessName) }}
                </div>

                <div class="bold">
                    Authorised Signatory
                </div>

            </td>
        </tr>
    </table>
    @if($setting('footer_text'))<div class="center muted" style="padding:4px; border-top:0.25mm solid {{ $border }};">{{ $setting('footer_text') }}</div>@endif
</div>
</body>
</html>