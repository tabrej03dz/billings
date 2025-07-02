<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('client', 'items')->latest()->get();
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $clients = Client::where('business_id', auth()->user()->business_id);
        return view('invoices.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $invoice = Invoice::create($request->only([
            'invoice_number', 'client_id', 'invoice_date', 'subtotal', 'tax_amount',
            'total', 'received_amount', 'balance', 'amount_in_words'
        ]));

        foreach ($request->items as $item) {
            $invoice->items()->create($item);
        }

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('client', 'items');
        return view('invoices.show', compact('invoice'));
    }

//    use PDF; // if you're using barryvdh/laravel-dompdf

    public function download(Invoice $invoice)
    {
        $invoice->load('client', 'items');
        $pdf = PDF::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download("Invoice_{$invoice->invoice_number}.pdf");
    }
}
