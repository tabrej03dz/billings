<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }
        .bordered {
            border: 1px solid #ccc;
            border-collapse: collapse;
        }
        .bordered th, .bordered td {
            border: 1px solid #ccc;
            padding: 6px;
        }
        .header, .bank, .footer {
            width: 100%;
            margin-top: 15px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .logo { width: 80px; height: auto; }
        .gray-bg { background-color: #f2f2f2; }
        .title {
            color: red;
            font-weight: bold;
            font-size: 16px;
        }
    </style>
</head>
<body>

<table class="header">
    <tr>
        <td style="width: 70%;">
            <img src="{{ public_path('logo.png') }}" class="logo" alt="Logo"><br>
            <span class="title">Real Victory Groups</span><br>
            73 - Basement, Ekta Enclave Society, Lakhanpur, Kanpur (208024), Kanpur Nagar, Uttar Pradesh - 208024<br>
            Mobile: 7753800444 | GSTIN: 09CYMPP9152J2ZK<br>
            Email: info@realvictorygroups.com
        </td>
        <td style="text-align: right;">
            <h4>TAX INVOICE <small>(ORIGINAL FOR RECIPIENT)</small></h4>
            <strong>Invoice No:</strong> {{ $invoice->invoice_number }}<br>
            <strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
        </td>
    </tr>
</table>

<hr>

<h4>BILL TO</h4>
<table class="footer">
    <tr>
        <td>
            <strong>{{ $invoice->client->name }}</strong><br>
            {{ $invoice->client->address }}<br>
            Mobile: {{ $invoice->client->mobile }}<br>
            GSTIN: {{ $invoice->client->gstin }}<br>
            PAN: {{ $invoice->client->pan }}<br>
            State: {{ $invoice->client->state }}
        </td>
    </tr>
</table>

<h4>Invoice Items</h4>
<table class="bordered" width="100%">
    <thead class="gray-bg">
    <tr>
        <th>SERVICES</th>
        <th>SAC</th>
        <th>QTY.</th>
        <th>RATE</th>
        <th>TAX</th>
        <th>AMOUNT</th>
    </tr>
    </thead>
    <tbody>
    @foreach($invoice->items as $item)
        <tr>
            <td>{{ $item->description }}</td>
            <td>{{ $item->sac_code }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->rate, 2) }}</td>
            <td>{{ $item->tax_percent }}%</td>
            <td>{{ number_format($item->amount, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table class="footer" style="margin-top: 10px;">
    <tr>
        <td style="width: 70%;" class="text-right bold">SUBTOTAL</td>
        <td class="text-right">₹ {{ number_format($invoice->subtotal, 2) }}</td>
    </tr>
    <tr>
        <td class="text-right bold">IGST @18%</td>
        <td class="text-right">₹ {{ number_format($invoice->tax_amount, 2) }}</td>
    </tr>
    <tr>
        <td class="text-right bold">TOTAL AMOUNT</td>
        <td class="text-right">₹ {{ number_format($invoice->total, 2) }}</td>
    </tr>
    <tr>
        <td class="text-right bold">Received Amount</td>
        <td class="text-right">₹ {{ number_format($invoice->received_amount, 2) }}</td>
    </tr>
    <tr>
        <td class="text-right bold">Balance</td>
        <td class="text-right">₹ {{ number_format($invoice->balance, 2) }}</td>
    </tr>
</table>

<table class="bank" style="margin-top: 20px;">
    <tr>
        <td class="bold">BANK DETAILS</td>
    </tr>
    <tr>
        <td>
            Name: Real Victory Groups<br>
            IFSC Code: HDFC0004462<br>
            Account No: 50200052705777<br>
            Bank: HDFC Bank, KALYANPUR
        </td>
    </tr>
</table>

<p style="margin-top: 20px;"><strong>Total Amount (in words):</strong> {{ $invoice->amount_in_words }}</p>

<br><br><br>
<table class="footer">
    <tr>
        <td class="text-right">
            <strong>Authorised Signatory For</strong><br>
            Real Victory Groups
        </td>
    </tr>
</table>

</body>
</html>
