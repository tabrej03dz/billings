<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #777;
            padding: 4px 6px;
        }
        th {
            background: #f0f0f0;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h1>Invoice</h1>
<p>Generated from Excel upload.</p>

<table>
    <thead>
    <tr>
        @foreach($header as $col)
            <th>{{ $col }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($rows as $row)
        <tr>
            @foreach($row as $cell)
                <td>{{ $cell }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
