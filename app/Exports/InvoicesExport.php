<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesExport implements
    FromArray,
    ShouldAutoSize,
    WithStyles,
    WithColumnFormatting
{
    protected Collection $rows;
    protected string $reportTitle;
    protected array $filters;

    public function __construct(
        $rows,
        string $reportTitle = 'Invoice Report',
        array $filters = []
    ) {
        $this->rows = collect($rows);
        $this->reportTitle = $reportTitle;
        $this->filters = $filters;
    }

    public function array(): array
    {
        $data = [];

        $data[] = [$this->reportTitle];

        $data[] = [
            'Date Range',
            ($this->filters['from_date'] ?? '-')
                . ' to '
                . ($this->filters['to_date'] ?? '-'),
        ];

        $data[] = [
            'Status',
            ($this->filters['status'] ?? '') ?: 'All',
        ];

        $data[] = [
            'Search',
            ($this->filters['search'] ?? '') ?: '-',
        ];

        $data[] = [];

        // Row 6: Column headings
        $data[] = [
            'S.No.',
            'Invoice Date',
            'Invoice No.',
            'Client Name',
            'Mobile',
            'GSTIN',
            'PAN',
            'Address',
            'GST Amount',
            'IGST Amount',
            'CGST Amount',
            'SGST Amount',
            'Total',
            'Received',
            'Balance',
            'Status',
        ];

        foreach ($this->rows as $index => $row) {
            $balance = (float) $row->balance;
            $received = (float) $row->received_amount;

            $status = 'Unpaid';

            if ($balance <= 0) {
                $status = 'Paid';
            } elseif ($received > 0) {
                $status = 'Partial';
            }

            $data[] = [
                $index + 1,

                $row->invoice_date
                    ? Carbon::parse($row->invoice_date)->format('Y-m-d')
                    : '',

                $row->invoice_number,
                optional($row->client)->name,
                optional($row->client)->mobile,
                optional($row->client)->gstin,
                optional($row->client)->pan,
                optional($row->client)->address,

                // Saved invoice tax amounts
                (float) $row->tax_amount,
                (float) $row->igst_amount,
                (float) $row->cgst_amount,
                (float) $row->sgst_amount,

                (float) $row->total,
                $received,
                $balance,
                $status,
            ];
        }

        return $data;
    }

    public function columnFormats(): array
    {
        return [
            'I' => '#,##0.00', // GST
            'J' => '#,##0.00', // IGST
            'K' => '#,##0.00', // CGST
            'L' => '#,##0.00', // SGST
            'M' => '#,##0.00', // Total
            'N' => '#,##0.00', // Received
            'O' => '#,##0.00', // Balance
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
            ],

            6 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}