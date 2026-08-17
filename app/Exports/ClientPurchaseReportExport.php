<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ClientPurchaseReportExport implements FromView, ShouldAutoSize
{
    protected $client;
    protected $invoices;
    protected array $summary;
    protected array $filters;

    public function __construct(
        $client,
        $invoices,
        array $summary,
        array $filters = []
    ) {
        $this->client   = $client;
        $this->invoices = $invoices;
        $this->summary  = $summary;
        $this->filters  = $filters;
    }

    public function view(): View
    {
        return view('clients.exports.excel', [
            'client'   => $this->client,
            'invoices' => $this->invoices,
            'summary'  => $this->summary,
            'filters'  => $this->filters,
        ]);
    }
}