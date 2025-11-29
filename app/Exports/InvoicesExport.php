<?php

namespace App\Exports;

use App\Models\Invoice;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class InvoicesExport implements FromView
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $query = Invoice::with('client')->latest();

        // Search
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Date range
        if (!empty($this->filters['from_date'])) {
            $query->whereDate('invoice_date', '>=', $this->filters['from_date']);
        }

        if (!empty($this->filters['to_date'])) {
            $query->whereDate('invoice_date', '<=', $this->filters['to_date']);
        }

        // Status
        if (!empty($this->filters['status'])) {
            $status = $this->filters['status'];
            $query->where(function ($q) use ($status) {
                if ($status === 'paid') {
                    $q->where('balance', '<=', 0);
                } elseif ($status === 'partial') {
                    $q->where('received_amount', '>', 0)->where('balance', '>', 0);
                } elseif ($status === 'unpaid') {
                    $q->where('received_amount', '<=', 0);
                }
            });
        }

        $invoices = $query->get();

        return view('invoices.exports', compact('invoices'));
    }
}
