{{-- resources/views/invoices/pdf_hospital.blade.php --}}

@php
    $ts = $templateSetting ?? null;

    /** @var \App\Models\Invoice $inv */
    $business = $biz ?? ($inv->business ?? null);
    $patient = $client ?? ($inv->client ?? null);
    $items = $items ?? ($inv->items ?? collect());

    $visit = $patientVisit
        ?? ($inv->relationLoaded('patientVisit') ? $inv->patientVisit : null)
        ?? ($inv->patientVisit ?? null);

    $doctor = $doctor
        ?? ($inv->relationLoaded('doctor') ? $inv->doctor : null)
        ?? ($visit?->doctor ?? null);

    $department = $department
        ?? ($visit?->department ?? null);

    $ward = $ward
        ?? ($visit?->ward ?? null);

    $room = $room
        ?? ($visit?->room ?? null);

    $bed = $bed
        ?? ($visit?->bed ?? null);

    /*
    |--------------------------------------------------------------------------
    | Hospital snapshot
    |--------------------------------------------------------------------------
    */
    $hospital = $inv->hospital_details_json ?? [];

    if (is_string($hospital)) {
        $decodedHospital = json_decode($hospital, true);
        $hospital = is_array($decodedHospital) ? $decodedHospital : [];
    }

    if (!is_array($hospital)) {
        $hospital = [];
    }

    $hospitalValue = static function (
        array $source,
        string $key,
        $fallback = null
    ) {
        $value = $source[$key] ?? null;

        return $value !== null && $value !== ''
            ? $value
            : $fallback;
    };

    /*
    |--------------------------------------------------------------------------
    | Document
    |--------------------------------------------------------------------------
    */
    $documentType = strtolower((string) (
        $type
        ?? $inv->invoice_type
        ?? 'tax'
    ));

    $isQuotation = $documentType === 'quotation';
    $isProforma = $documentType === 'proforma';

    $documentLabel = match (true) {
        $isQuotation => 'HOSPITAL QUOTATION',
        $isProforma => 'HOSPITAL PROFORMA',
        default => 'HOSPITAL BILL',
    };

    $visitType = strtolower((string) $hospitalValue(
        $hospital,
        'visit_type',
        $visit?->visit_type
            ?? $inv->hospital_bill_type
            ?? 'opd'
    ));

    $visitTypeLabel = match ($visitType) {
        'ipd' => 'IPD / ADMISSION',
        'emergency' => 'EMERGENCY',
        'day_care' => 'DAY CARE',
        'diagnostic' => 'DIAGNOSTIC',
        'pharmacy' => 'PHARMACY',
        default => 'OPD',
    };

    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    */
    $primaryColor = $ts->primary_color ?? '#087f8c';
    $secondaryColor = $ts->secondary_color ?? '#e8f7f8';
    $accentColor = '#0f766e';
    $borderColor = '#b7dfe2';
    $textColor = $ts->text_color ?? '#14212b';

    // mPDF Hindi-compatible font.
    $fontFamily = 'freeserif';

    $showLogo = $ts->show_logo ?? true;
    $showTagline = $ts->show_tagline ?? true;
    $showSignature = $ts->show_signature ?? true;
    $showTerms = $ts->show_terms ?? true;

    /*
    |--------------------------------------------------------------------------
    | Formatters
    |--------------------------------------------------------------------------
    */
    $formatMoney = static fn ($value) => number_format(
        (float) $value,
        2,
        '.',
        ''
    );

    $formatQuantity = static function ($value) {
        $number = (float) $value;

        return floor($number) == $number
            ? number_format($number, 0, '.', '')
            : number_format($number, 2, '.', '');
    };

    $formatDate = static function ($date) {
        if (!$date) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable $exception) {
            return (string) $date;
        }
    };

    $formatDateTime = static function ($date) {
        if (!$date) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y h:i A');
        } catch (\Throwable $exception) {
            return (string) $date;
        }
    };

    /*
    |--------------------------------------------------------------------------
    | Totals
    |--------------------------------------------------------------------------
    */
    $taxable = (float) (
        $subtotal
        ?? $inv->subtotal
        ?? 0
    );

    $discountTotal = (float) (
        $inv->discount_total
        ?? 0
    );

    $additionalChargeTotal = (float) (
        $inv->charge_total
        ?? 0
    );

    $taxTotal = (float) (
        $tax_total
        ?? $inv->tax_amount
        ?? 0
    );

    $cgstAmount = (float) (
        $cgst_amount
        ?? $inv->cgst_amount
        ?? 0
    );

    $sgstAmount = (float) (
        $sgst_amount
        ?? $inv->sgst_amount
        ?? 0
    );

    $igstAmount = (float) (
        $igst_amount
        ?? $inv->igst_amount
        ?? 0
    );

    $tcsAmount = (float) (
        $inv->tcs_amount
        ?? 0
    );

    $roundOff = (float) (
        $inv->round_off
        ?? 0
    );

    $grandTotal = (float) (
        $grand_total
        ?? $inv->total
        ?? 0
    );

    $receivedTotal = (float) (
        $inv->received_amount
        ?? 0
    );

    $balanceAmount = (float) (
        $balance
        ?? $inv->balance
        ?? max(0, $grandTotal - $receivedTotal)
    );

    $hasIGST = $igstAmount > 0;

    /*
    |--------------------------------------------------------------------------
    | Payment row
    |--------------------------------------------------------------------------
    */
    $payment = $payRow
        ?? ($inv->relationLoaded('payments') ? $inv->payments->last() : null)
        ?? ($inv->payment ?? null);

    $cashAmount = (float) (
        $payment?->cash_amount
        ?? 0
    );

    $onlineAmount = (float) (
        $payment?->online_amount
        ?? 0
    );

    $cardAmount = (float) (
        $payment?->card_amount
        ?? 0
    );

    $chequeAmount = (float) (
        $payment?->cheque_amount
        ?? 0
    );

    $advanceAmount = (float) (
        $payment?->advance_amount
        ?? 0
    );

    /*
    |--------------------------------------------------------------------------
    | Patient
    |--------------------------------------------------------------------------
    */
    $patientName = $patient->name ?? '-';
    $patientMobile = $patient->mobile
        ?? $patient->phone
        ?? '-';

    $patientAddress = trim(implode(', ', array_filter([
        $patient->address ?? null,
        $patient->city ?? null,
        $patient->state ?? null,
        $patient->pincode ?? $patient->pin ?? null,
    ])));

    $patientUhid = $hospitalValue(
        $hospital,
        'patient_uhid',
        $patient->patientProfile?->patient_code
            ?? $patient->patient_code
            ?? '-'
    );

    $patientAge = $hospitalValue(
        $hospital,
        'patient_age',
        $patient->patientProfile?->age
            ?? '-'
    );

    $patientGender = $hospitalValue(
        $hospital,
        'patient_gender',
        $patient->patientProfile?->gender
            ?? '-'
    );

    $bloodGroup = $hospitalValue(
        $hospital,
        'blood_group',
        $patient->patientProfile?->blood_group
            ?? '-'
    );

    $guardianName = $hospitalValue(
        $hospital,
        'guardian_name',
        $patient->patientProfile?->guardian_name
            ?? '-'
    );

    /*
    |--------------------------------------------------------------------------
    | Visit and clinical
    |--------------------------------------------------------------------------
    */
    $visitNumber = $hospitalValue(
        $hospital,
        'visit_number',
        $visit?->visit_number
            ?? '-'
    );

    $visitAt = $hospitalValue(
        $hospital,
        'visit_at',
        $visit?->visit_at
            ?? $inv->invoice_date
    );

    $admittedAt = $hospitalValue(
        $hospital,
        'admitted_at',
        $visit?->admitted_at
    );

    $dischargedAt = $hospitalValue(
        $hospital,
        'discharged_at',
        $visit?->discharged_at
    );

    $doctorName = $doctor->name
        ?? $hospitalValue(
            $hospital,
            'doctor_name',
            '-'
        );

    $doctorQualification = $doctor->qualification ?? null;
    $doctorSpecialization = $doctor->specialization ?? null;
    $doctorRegistration = $doctor->registration_number ?? null;

    $departmentName = $department->name
        ?? $hospitalValue(
            $hospital,
            'department_name',
            '-'
        );

    $wardName = $ward->name
        ?? $hospitalValue(
            $hospital,
            'ward_name',
            '-'
        );

    $roomNumber = $room->room_number
        ?? $room->name
        ?? $hospitalValue(
            $hospital,
            'room_number',
            '-'
        );

    $bedNumber = $bed->bed_number
        ?? $bed->name
        ?? $hospitalValue(
            $hospital,
            'bed_number',
            '-'
        );

    $referredBy = $hospitalValue(
        $hospital,
        'referred_by',
        '-'
    );

    $billingCategory = $hospitalValue(
        $hospital,
        'billing_category',
        $inv->billing_category
            ?? 'cash'
    );

    $chiefComplaint = $hospitalValue(
        $hospital,
        'chief_complaint',
        $visit?->chief_complaint
    );

    $diagnosis = $hospitalValue(
        $hospital,
        'diagnosis',
        $visit?->diagnosis
    );

    $clinicalNotes = $hospitalValue(
        $hospital,
        'notes',
        $visit?->remarks
    );

    $insuranceProvider = $hospitalValue(
        $hospital,
        'insurance_provider'
    );

    $insurancePolicyNumber = $hospitalValue(
        $hospital,
        'insurance_policy_number'
    );

    /*
    |--------------------------------------------------------------------------
    | Business
    |--------------------------------------------------------------------------
    */
    $businessAddress = trim(implode(', ', array_filter([
        $business->address ?? null,
        $business->city ?? null,
        $business->state ?? null,
        $business->pincode ?? $business->pin ?? null,
    ])));

    $businessMobile = $business->mobile
        ?? $business->phone
        ?? '-';

    $businessEmail = $business->email ?? '-';
    $businessGstin = $business->gstin ?? '-';
    $businessRegistration = $business->registration_number
        ?? $business->hospital_registration_number
        ?? null;

    /*
    |--------------------------------------------------------------------------
    | Invoice
    |--------------------------------------------------------------------------
    */
    $invoiceNumber = $inv->invoice_number
        ?? $inv->invoice_no
        ?? '-';

    $invoiceDate = $inv->invoice_date
        ?? $inv->date
        ?? null;

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    */
    $logoSource = $logo ?? null;

    if (!empty($logoSource)) {
        $logoSource = (string) $logoSource;

        if (!\Illuminate\Support\Str::startsWith(
            $logoSource,
            ['http://', 'https://', 'data:', '/']
        )) {
            if (file_exists(public_path($logoSource))) {
                $logoSource = public_path($logoSource);
            } elseif (
                file_exists(
                    public_path(
                        'storage/' . ltrim($logoSource, '/')
                    )
                )
            ) {
                $logoSource = public_path(
                    'storage/' . ltrim($logoSource, '/')
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Amount in words
    |--------------------------------------------------------------------------
    */
    if (!function_exists('hospital_inr_words')) {
        function hospital_inr_words($amount)
        {
            $amount = (float) $amount;
            $rupees = (int) floor($amount);
            $paise = (int) round(($amount - $rupees) * 100);

            $ones = [
                '',
                'One',
                'Two',
                'Three',
                'Four',
                'Five',
                'Six',
                'Seven',
                'Eight',
                'Nine',
                'Ten',
                'Eleven',
                'Twelve',
                'Thirteen',
                'Fourteen',
                'Fifteen',
                'Sixteen',
                'Seventeen',
                'Eighteen',
                'Nineteen',
            ];

            $tens = [
                '',
                '',
                'Twenty',
                'Thirty',
                'Forty',
                'Fifty',
                'Sixty',
                'Seventy',
                'Eighty',
                'Ninety',
            ];

            $twoDigits = static function ($number) use (
                $ones,
                $tens
            ) {
                $number = (int) $number;

                if ($number === 0) {
                    return '';
                }

                if ($number < 20) {
                    return $ones[$number];
                }

                return trim(
                    $tens[(int) ($number / 10)]
                    . ' '
                    . $ones[$number % 10]
                );
            };

            $parts = [];

            if ($rupees >= 10000000) {
                $crore = (int) floor(
                    $rupees / 10000000
                );

                $parts[] = $twoDigits($crore) . ' Crore';
                $rupees %= 10000000;
            }

            if ($rupees >= 100000) {
                $lakh = (int) floor(
                    $rupees / 100000
                );

                $parts[] = $twoDigits($lakh) . ' Lakh';
                $rupees %= 100000;
            }

            if ($rupees >= 1000) {
                $thousand = (int) floor(
                    $rupees / 1000
                );

                $parts[] = $twoDigits($thousand) . ' Thousand';
                $rupees %= 1000;
            }

            if ($rupees >= 100) {
                $hundred = (int) floor(
                    $rupees / 100
                );

                $parts[] = $ones[$hundred] . ' Hundred';
                $rupees %= 100;
            }

            if ($rupees > 0) {
                $parts[] = $twoDigits($rupees);
            }

            $words = trim(
                implode(
                    ' ',
                    array_filter($parts)
                )
            );

            if ($words === '') {
                $words = 'Zero';
            }

            $result = $words . ' Rupees';

            if ($paise > 0) {
                $result .= ' and '
                    . $twoDigits($paise)
                    . ' Paise';
            }

            return $result . ' Only';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Signature
    |--------------------------------------------------------------------------
    */
    $signature = $inv->signature ?? null;

    $signatureSource = $signature
        ? (
            \Illuminate\Support\Str::startsWith(
                $signature,
                ['http://', 'https://', 'data:']
            )
                ? $signature
                : public_path(
                    'storage/' . ltrim($signature, '/')
                )
        )
        : null;
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title>
        {{ $documentLabel }} {{ $invoiceNumber }}
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            color: {{ $textColor }};
            font-family: {{ $fontFamily }}, sans-serif;
            font-size: 10px;
            line-height: 1.35;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .page {
            padding: 8px 10px 10px;
        }

        .primary {
            color: {{ $primaryColor }};
        }

        .muted {
            color: #5c6973;
        }

        .bold {
            font-weight: bold;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .nowrap {
            white-space: nowrap;
        }

        .hospital-header {
            border: 1px solid {{ $borderColor }};
            border-bottom: 4px solid {{ $primaryColor }};
        }

        .hospital-header td {
            vertical-align: middle;
            padding: 8px;
        }

        .logo-cell {
            width: 105px;
            text-align: center;
        }

        .logo {
            max-width: 92px;
            max-height: 72px;
        }

        .hospital-name {
            margin: 0 0 3px;
            color: {{ $primaryColor }};
            font-size: 22px;
            line-height: 1.05;
            font-weight: bold;
            letter-spacing: .2px;
        }

        .hospital-subtitle {
            margin-bottom: 3px;
            color: {{ $accentColor }};
            font-size: 10px;
            font-weight: bold;
        }

        .hospital-address {
            font-size: 10px;
        }

        .document-box {
            width: 150px;
            border-left: 1px solid {{ $borderColor }};
            text-align: center;
        }

        .document-label {
            color: {{ $primaryColor }};
            font-size: 15px;
            font-weight: bold;
        }

        .visit-badge {
            display: inline-block;
            margin-top: 5px;
            padding: 3px 8px;
            border: 1px solid {{ $primaryColor }};
            border-radius: 10px;
            color: {{ $primaryColor }};
            font-size: 9px;
            font-weight: bold;
        }

        .invoice-meta {
            margin-top: 6px;
            border: 1px solid {{ $borderColor }};
            background: {{ $secondaryColor }};
        }

        .invoice-meta td {
            padding: 5px 7px;
            border-right: 1px solid {{ $borderColor }};
            font-size: 10px;
        }

        .invoice-meta td:last-child {
            border-right: 0;
        }

        .section {
            margin-top: 7px;
            border: 1px solid {{ $borderColor }};
        }

        .section-title {
            padding: 5px 7px;
            color: #ffffff;
            background: {{ $primaryColor }};
            font-size: 10px;
            font-weight: bold;
            letter-spacing: .2px;
        }

        .detail-table td {
            width: 25%;
            padding: 5px 7px;
            border-right: 1px solid #dcebed;
            border-bottom: 1px solid #dcebed;
            vertical-align: top;
        }

        .detail-table tr:last-child td {
            border-bottom: 0;
        }

        .detail-table td:last-child {
            border-right: 0;
        }

        .label {
            margin-bottom: 1px;
            color: #63727c;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .value {
            min-height: 13px;
            font-size: 10px;
            font-weight: bold;
        }

        .clinical-table td {
            width: 50%;
            padding: 6px 7px;
            border-right: 1px solid #dcebed;
            vertical-align: top;
        }

        .clinical-table td:last-child {
            border-right: 0;
        }

        .clinical-content {
            min-height: 30px;
            white-space: pre-line;
        }

        .services {
            margin-top: 7px;
            border: 1px solid {{ $borderColor }};
        }

        .services th {
            padding: 5px 4px;
            color: #ffffff;
            background: {{ $primaryColor }};
            border-right: 1px solid rgba(255, 255, 255, .25);
            font-size: 8.5px;
            text-transform: uppercase;
        }

        .services td {
            padding: 5px 4px;
            border-right: 1px solid #dcebed;
            border-bottom: 1px solid #dcebed;
            vertical-align: top;
            font-size: 9.5px;
        }

        .services th:last-child,
        .services td:last-child {
            border-right: 0;
        }

        .services tbody tr:nth-child(even) {
            background: #f4fbfb;
        }

        .service-name {
            font-weight: bold;
        }

        .service-description {
            margin-top: 1px;
            color: #63727c;
            font-size: 8.5px;
            white-space: pre-line;
        }

        .summary-layout {
            margin-top: 8px;
        }

        .summary-layout td {
            vertical-align: top;
        }

        .left-summary {
            width: 53%;
            padding-right: 8px;
        }

        .right-summary {
            width: 47%;
        }

        .payment-box,
        .notes-box,
        .total-box {
            border: 1px solid {{ $borderColor }};
        }

        .box-heading {
            padding: 4px 6px;
            background: {{ $secondaryColor }};
            color: {{ $primaryColor }};
            font-size: 9px;
            font-weight: bold;
        }

        .payment-table td,
        .total-table td {
            padding: 4px 6px;
            border-bottom: 1px solid #e2ecee;
        }

        .payment-table tr:last-child td,
        .total-table tr:last-child td {
            border-bottom: 0;
        }

        .total-table .grand td {
            color: #ffffff;
            background: {{ $primaryColor }};
            font-size: 11px;
            font-weight: bold;
        }

        .amount-words {
            margin-top: 6px;
            padding: 6px;
            border: 1px solid {{ $borderColor }};
            background: #f8fcfc;
        }

        .terms {
            margin-top: 6px;
            padding: 6px;
            border: 1px solid {{ $borderColor }};
            white-space: pre-line;
        }

        .signature-table {
            margin-top: 12px;
        }

        .signature-table td {
            width: 33.33%;
            height: 58px;
            padding: 4px;
            text-align: center;
            vertical-align: bottom;
        }

        .signature-image {
            max-width: 100px;
            max-height: 35px;
        }

        .signature-line {
            padding-top: 3px;
            border-top: 1px solid #66757f;
            font-size: 9px;
            font-weight: bold;
        }

        .footer-note {
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px solid {{ $borderColor }};
            color: #60717b;
            text-align: center;
            font-size: 8px;
        }
    </style>
</head>

<body>
<div class="page">

    {{-- Hospital Header --}}
    <table class="hospital-header">
        <tr>
            <td class="logo-cell">
                @if($showLogo && !empty($logoSource))
                    <img
                        src="{{ $logoSource }}"
                        alt="Hospital Logo"
                        class="logo"
                    >
                @endif
            </td>

            <td>
                <div class="hospital-name">
                    {{ $business->name ?? 'Hospital / Clinic' }}
                </div>

                @if($showTagline)
                    <div class="hospital-subtitle">
                        Patient Care • Diagnostics • Treatment • Billing
                    </div>
                @endif

                <div class="hospital-address">
                    {{ $businessAddress ?: '-' }}
                </div>

                <div class="hospital-address">
                    <span class="bold">Phone:</span>
                    {{ $businessMobile }}

                    &nbsp;&nbsp;

                    <span class="bold">Email:</span>
                    {{ $businessEmail }}
                </div>

                <div class="hospital-address">
                    @if($businessRegistration)
                        <span class="bold">Hospital Reg. No.:</span>
                        {{ $businessRegistration }}
                        &nbsp;&nbsp;
                    @endif

                    <span class="bold">GSTIN:</span>
                    {{ $businessGstin }}
                </div>
            </td>

            <td class="document-box">
                <div class="document-label">
                    {{ $documentLabel }}
                </div>

                <div class="visit-badge">
                    {{ $visitTypeLabel }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Invoice Meta --}}
    <table class="invoice-meta">
        <tr>
            <td style="width:25%;">
                <span class="bold">Bill No.:</span>
                {{ $invoiceNumber }}
            </td>

            <td style="width:25%;">
                <span class="bold">Bill Date:</span>
                {{ $formatDate($invoiceDate) }}
            </td>

            <td style="width:25%;">
                <span class="bold">Visit No.:</span>
                {{ $visitNumber }}
            </td>

            <td style="width:25%;">
                <span class="bold">Visit Date:</span>
                {{ $formatDateTime($visitAt) }}
            </td>
        </tr>
    </table>

    {{-- Patient Details --}}
    <div class="section">
        <div class="section-title">
            PATIENT INFORMATION
        </div>

        <table class="detail-table">
            <tr>
                <td>
                    <div class="label">Patient Name</div>
                    <div class="value">
                        {{ strtoupper($patientName) }}
                    </div>
                </td>

                <td>
                    <div class="label">UHID / Patient ID</div>
                    <div class="value">
                        {{ $patientUhid }}
                    </div>
                </td>

                <td>
                    <div class="label">Age / Gender</div>
                    <div class="value">
                        {{ $patientAge !== '-' ? $patientAge . ' Years' : '-' }}
                        /
                        {{ ucfirst((string) $patientGender) }}
                    </div>
                </td>

                <td>
                    <div class="label">Blood Group</div>
                    <div class="value">
                        {{ $bloodGroup }}
                    </div>
                </td>
            </tr>

            <tr>
                <td>
                    <div class="label">Mobile Number</div>
                    <div class="value">
                        {{ $patientMobile }}
                    </div>
                </td>

                <td>
                    <div class="label">Guardian / Attendant</div>
                    <div class="value">
                        {{ $guardianName }}
                    </div>
                </td>

                <td colspan="2">
                    <div class="label">Address</div>
                    <div class="value">
                        {{ $patientAddress ?: '-' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Visit Details --}}
    <div class="section">
        <div class="section-title">
            CONSULTATION / ADMISSION DETAILS
        </div>

        <table class="detail-table">
            <tr>
                <td>
                    <div class="label">Visit Type</div>
                    <div class="value">
                        {{ $visitTypeLabel }}
                    </div>
                </td>

                <td>
                    <div class="label">Consulting Doctor</div>
                    <div class="value">
                        {{ $doctorName }}

                        @if($doctorSpecialization)
                            <br>
                            <span class="muted">
                                {{ $doctorSpecialization }}
                            </span>
                        @endif
                    </div>
                </td>

                <td>
                    <div class="label">Department</div>
                    <div class="value">
                        {{ $departmentName }}
                    </div>
                </td>

                <td>
                    <div class="label">Referred By</div>
                    <div class="value">
                        {{ $referredBy }}
                    </div>
                </td>
            </tr>

            @if(in_array($visitType, ['ipd', 'emergency', 'day_care'], true))
                <tr>
                    <td>
                        <div class="label">Admission Date & Time</div>
                        <div class="value">
                            {{ $formatDateTime($admittedAt) }}
                        </div>
                    </td>

                    <td>
                        <div class="label">Discharge Date & Time</div>
                        <div class="value">
                            {{ $formatDateTime($dischargedAt) }}
                        </div>
                    </td>

                    <td>
                        <div class="label">Ward / Room</div>
                        <div class="value">
                            {{ $wardName }} / {{ $roomNumber }}
                        </div>
                    </td>

                    <td>
                        <div class="label">Bed Number</div>
                        <div class="value">
                            {{ $bedNumber }}
                        </div>
                    </td>
                </tr>
            @endif

            <tr>
                <td>
                    <div class="label">Billing Category</div>
                    <div class="value">
                        {{ strtoupper(str_replace('_', ' ', (string) $billingCategory)) }}
                    </div>
                </td>

                <td>
                    <div class="label">Insurance / TPA</div>
                    <div class="value">
                        {{ $insuranceProvider ?: '-' }}
                    </div>
                </td>

                <td>
                    <div class="label">Policy / Claim Number</div>
                    <div class="value">
                        {{ $insurancePolicyNumber ?: '-' }}
                    </div>
                </td>

                <td>
                    <div class="label">Doctor Registration</div>
                    <div class="value">
                        {{ $doctorRegistration ?: '-' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Clinical Details --}}
    @if($chiefComplaint || $diagnosis || $clinicalNotes)
        <div class="section">
            <div class="section-title">
                CLINICAL INFORMATION
            </div>

            <table class="clinical-table">
                <tr>
                    <td>
                        <div class="label">
                            Chief Complaint / Reason for Visit
                        </div>

                        <div class="clinical-content">
                            {{ $chiefComplaint ?: '-' }}
                        </div>
                    </td>

                    <td>
                        <div class="label">
                            Diagnosis
                        </div>

                        <div class="clinical-content">
                            {{ $diagnosis ?: '-' }}
                        </div>
                    </td>
                </tr>

                @if($clinicalNotes)
                    <tr>
                        <td colspan="2">
                            <div class="label">
                                Clinical / Billing Notes
                            </div>

                            <div class="clinical-content">
                                {{ $clinicalNotes }}
                            </div>
                        </td>
                    </tr>
                @endif
            </table>
        </div>
    @endif

    {{-- Hospital Services --}}
    <table class="services">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:34%;">Service / Particulars</th>
                <th style="width:11%;">SAC / HSN</th>
                <th style="width:9%;">Qty / Days</th>
                <th style="width:12%;" class="right">Unit Rate</th>
                <th style="width:9%;" class="right">Tax %</th>
                <th style="width:10%;" class="right">Tax</th>
                <th style="width:10%;" class="right">Amount</th>
            </tr>
        </thead>

        <tbody>
            @forelse($items as $index => $item)
                @php
                    $serviceName = $item->item->name
                        ?? $item->description
                        ?? 'Hospital Service';

                    $serviceDescription = trim((string) (
                        $item->description
                        ?? ''
                    ));

                    $sacCode = $item->sac_code
                        ?? $item->hsn_code
                        ?? '-';

                    $quantity = (float) (
                        $item->quantity
                        ?? 1
                    );

                    $quantity = $quantity > 0
                        ? $quantity
                        : 1;

                    /*
                     * Existing store function invoice_items.rate me full
                     * taxable line base save karta hai. Unit rate nikalne ke
                     * liye rate / quantity use kiya gaya hai.
                     */
                    $lineBase = (float) (
                        $item->rate
                        ?? 0
                    );

                    $unitRate = $quantity > 0
                        ? $lineBase / $quantity
                        : $lineBase;

                    $taxPercent = (float) (
                        $item->tax_percent
                        ?? 0
                    );

                    $lineTotal = (float) (
                        $item->amount
                        ?? 0
                    );

                    $lineTax = max(
                        0,
                        $lineTotal - $lineBase
                    );

                    if ($lineTotal <= 0) {
                        $lineTax = round(
                            $lineBase * ($taxPercent / 100),
                            2
                        );

                        $lineTotal = $lineBase + $lineTax;
                    }
                @endphp

                <tr>
                    <td class="center">
                        {{ $index + 1 }}
                    </td>

                    <td>
                        <div class="service-name">
                            {{ $serviceName }}
                        </div>

                        @if(
                            $serviceDescription !== ''
                            && $serviceDescription !== $serviceName
                        )
                            <div class="service-description">
                                {{ $serviceDescription }}
                            </div>
                        @endif
                    </td>

                    <td>
                        {{ $sacCode }}
                    </td>

                    <td class="center">
                        {{ $formatQuantity($quantity) }}
                        {{ $item->unit ?? '' }}
                    </td>

                    <td class="right nowrap">
                        &#8377; {{ $formatMoney($unitRate) }}
                    </td>

                    <td class="right">
                        {{ $formatMoney($taxPercent) }}
                    </td>

                    <td class="right nowrap">
                        &#8377; {{ $formatMoney($lineTax) }}
                    </td>

                    <td class="right nowrap">
                        &#8377; {{ $formatMoney($lineTotal) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center">
                        No hospital service/charge found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Payment and Totals --}}
    <table class="summary-layout">
        <tr>
            <td class="left-summary">

                <div class="payment-box">
                    <div class="box-heading">
                        PAYMENT DETAILS
                    </div>

                    <table class="payment-table">
                        <tr>
                            <td>Payment Method</td>
                            <td class="right bold">
                                {{ strtoupper((string) ($inv->payment_method ?? 'N/A')) }}
                            </td>
                        </tr>

                        @if($cashAmount > 0)
                            <tr>
                                <td>Cash</td>
                                <td class="right">
                                    &#8377; {{ $formatMoney($cashAmount) }}
                                </td>
                            </tr>
                        @endif

                        @if($onlineAmount > 0)
                            <tr>
                                <td>UPI / Online</td>
                                <td class="right">
                                    &#8377; {{ $formatMoney($onlineAmount) }}
                                </td>
                            </tr>
                        @endif

                        @if($cardAmount > 0)
                            <tr>
                                <td>Card</td>
                                <td class="right">
                                    &#8377; {{ $formatMoney($cardAmount) }}
                                </td>
                            </tr>
                        @endif

                        @if($chequeAmount > 0)
                            <tr>
                                <td>Cheque</td>
                                <td class="right">
                                    &#8377; {{ $formatMoney($chequeAmount) }}
                                </td>
                            </tr>
                        @endif

                        @if($advanceAmount > 0)
                            <tr>
                                <td>Advance Adjusted</td>
                                <td class="right">
                                    &#8377; {{ $formatMoney($advanceAmount) }}
                                </td>
                            </tr>
                        @endif

                        <tr>
                            <td class="bold">Amount Received</td>
                            <td class="right bold">
                                &#8377; {{ $formatMoney($receivedTotal) }}
                            </td>
                        </tr>

                        <tr>
                            <td class="bold">Balance Due</td>
                            <td class="right bold">
                                &#8377; {{ $formatMoney($balanceAmount) }}
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="amount-words">
                    <div class="label">
                        Amount in Words
                    </div>

                    <div class="bold">
                        {{ hospital_inr_words($grandTotal) }}
                    </div>
                </div>

                @if($showTerms && !empty($inv->terms))
                    <div class="terms">
                        <div class="label">
                            Terms & Conditions
                        </div>

                        {{ $inv->terms }}
                    </div>
                @endif
            </td>

            <td class="right-summary">
                <div class="total-box">
                    <div class="box-heading">
                        BILL SUMMARY
                    </div>

                    <table class="total-table">
                        <tr>
                            <td>Service Subtotal</td>
                            <td class="right">
                                &#8377; {{ $formatMoney($taxable) }}
                            </td>
                        </tr>

                        @if($discountTotal > 0)
                            <tr>
                                <td>Discount / Concession</td>
                                <td class="right">
                                    - &#8377; {{ $formatMoney($discountTotal) }}
                                </td>
                            </tr>
                        @endif

                        @if($additionalChargeTotal > 0)
                            <tr>
                                <td>Additional Charges</td>
                                <td class="right">
                                    &#8377; {{ $formatMoney($additionalChargeTotal) }}
                                </td>
                            </tr>
                        @endif

                        @if($hasIGST)
                            <tr>
                                <td>IGST</td>
                                <td class="right">
                                    &#8377; {{ $formatMoney($igstAmount) }}
                                </td>
                            </tr>
                        @else
                            @if($cgstAmount > 0)
                                <tr>
                                    <td>CGST</td>
                                    <td class="right">
                                        &#8377; {{ $formatMoney($cgstAmount) }}
                                    </td>
                                </tr>
                            @endif

                            @if($sgstAmount > 0)
                                <tr>
                                    <td>SGST</td>
                                    <td class="right">
                                        &#8377; {{ $formatMoney($sgstAmount) }}
                                    </td>
                                </tr>
                            @endif
                        @endif

                        @if(
                            !$hasIGST
                            && $cgstAmount <= 0
                            && $sgstAmount <= 0
                            && $taxTotal > 0
                        )
                            <tr>
                                <td>Tax</td>
                                <td class="right">
                                    &#8377; {{ $formatMoney($taxTotal) }}
                                </td>
                            </tr>
                        @endif

                        @if($tcsAmount > 0)
                            <tr>
                                <td>TCS</td>
                                <td class="right">
                                    &#8377; {{ $formatMoney($tcsAmount) }}
                                </td>
                            </tr>
                        @endif

                        @if(abs($roundOff) > 0)
                            <tr>
                                <td>Round Off</td>
                                <td class="right">
                                    &#8377; {{ $formatMoney($roundOff) }}
                                </td>
                            </tr>
                        @endif

                        <tr class="grand">
                            <td>GRAND TOTAL</td>
                            <td class="right">
                                &#8377; {{ $formatMoney($grandTotal) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Signatures --}}
    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-line">
                    Patient / Attendant Signature
                </div>
            </td>

            <td>
                <div class="signature-line">
                    Billing Executive
                </div>
            </td>

            <td>
                @if($showSignature && !empty($signatureSource))
                    <div>
                        <img
                            src="{{ $signatureSource }}"
                            alt="Signature"
                            class="signature-image"
                        >
                    </div>
                @endif

                <div class="signature-line">
                    Authorised Signatory
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        This is a computer-generated hospital bill. Please preserve it for medical and payment records.
    </div>
</div>
</body>
</html>