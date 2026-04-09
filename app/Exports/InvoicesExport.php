<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesExport implements FromArray, ShouldAutoSize, WithStyles
{
    protected Collection $rows;
    protected string $reportTitle;
    protected array $filters;

    public function __construct($rows, string $reportTitle = 'Invoice Report', array $filters = [])
    {
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
            (($this->filters['from_date'] ?? '-') . ' to ' . ($this->filters['to_date'] ?? '-'))
        ];
        $data[] = ['Status', $this->filters['status'] ?: 'All'];
        $data[] = ['Search', $this->filters['search'] ?: '-'];
        $data[] = [];

        $data[] = [
            'S.No.',
            'Invoice Date',
            'Invoice No.',
            'Client Name',
            'Mobile',
            'GSTIN',
            'PAN',
            'Address',
            'Total',
            'Received',
            'Balance',
            'Status',
        ];

        foreach ($this->rows as $index => $row) {
            $status = 'Unpaid';

            if ((float) $row->balance <= 0) {
                $status = 'Paid';
            } elseif ((float) $row->received_amount > 0 && (float) $row->balance > 0) {
                $status = 'Partial';
            }

            $data[] = [
                $index + 1,
                optional($row->invoice_date)->format('Y-m-d') ?: $row->invoice_date,
                $row->invoice_number,
                optional($row->client)->name,
                optional($row->client)->mobile,
                optional($row->client)->gstin,
                optional($row->client)->pan,
                optional($row->client)->address,
                (float) $row->total,
                (float) $row->received_amount,
                (float) $row->balance,
                $status,
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            6 => ['font' => ['bold' => true]],
        ];
    }
}