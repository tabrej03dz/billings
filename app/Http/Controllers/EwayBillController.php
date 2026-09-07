<?php

namespace App\Http\Controllers;

use App\Models\EwayBill;
use App\Models\Invoice;
use Illuminate\Http\Request;

class EwayBillController extends Controller
{
    /**
     * E-Way Bill form open
     */
    public function create(Invoice $invoice)
    {
        $invoice->load([
            'business',
            'client',
            'items',
            'ewayBill',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Agar pehle se E-Way Bill bana hua hai
        |--------------------------------------------------------------------------
        */
        if ($invoice->ewayBill) {
            return redirect()
                ->route('eway-bills.show', $invoice->ewayBill->id);
        }

        return view('eway_bills.create', compact('invoice'));
    }


    /**
     * Store E-Way Bill
     */
    public function store(Request $request, Invoice $invoice)
    {
        $invoice->load([
            'business',
            'client',
            'items',
            'ewayBill',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Duplicate E-Way Bill prevent
        |--------------------------------------------------------------------------
        */
        if ($invoice->ewayBill) {
            return redirect()
                ->route('eway-bills.show', $invoice->ewayBill->id)
                ->with('error', 'Is invoice ka E-Way Bill already bana hua hai.');
        }

        $validated = $request->validate([

            'supply_type' => [
                'required',
                'string',
                'max:20',
            ],

            'sub_supply_type' => [
                'required',
                'string',
                'max:100',
            ],

            'document_type' => [
                'required',
                'string',
                'max:50',
            ],

            'document_no' => [
                'required',
                'string',
                'max:100',
            ],

            'document_date' => [
                'required',
                'date',
            ],

            /*
            |--------------------------------------------------------------------------
            | From
            |--------------------------------------------------------------------------
            */

            'from_gstin' => [
                'nullable',
                'string',
                'max:20',
            ],

            'from_name' => [
                'required',
                'string',
                'max:255',
            ],

            'from_address' => [
                'required',
                'string',
            ],

            'from_place' => [
                'required',
                'string',
                'max:255',
            ],

            'from_state' => [
                'required',
                'string',
                'max:255',
            ],

            'from_state_code' => [
                'nullable',
                'string',
                'max:10',
            ],

            'from_pincode' => [
                'required',
                'digits:6',
            ],

            /*
            |--------------------------------------------------------------------------
            | To
            |--------------------------------------------------------------------------
            */

            'to_gstin' => [
                'nullable',
                'string',
                'max:20',
            ],

            'to_name' => [
                'required',
                'string',
                'max:255',
            ],

            'to_address' => [
                'required',
                'string',
            ],

            'to_place' => [
                'required',
                'string',
                'max:255',
            ],

            'to_state' => [
                'required',
                'string',
                'max:255',
            ],

            'to_state_code' => [
                'nullable',
                'string',
                'max:10',
            ],

            'to_pincode' => [
                'required',
                'digits:6',
            ],

            /*
            |--------------------------------------------------------------------------
            | Transport
            |--------------------------------------------------------------------------
            */

            'transport_mode' => [
                'required',
                'string',
                'max:50',
            ],

            'distance' => [
                'required',
                'integer',
                'min:1',
            ],

            'transporter_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'transporter_id' => [
                'nullable',
                'string',
                'max:50',
            ],

            'vehicle_no' => [
                'nullable',
                'string',
                'max:30',
            ],

            'vehicle_type' => [
                'nullable',
                'string',
                'max:30',
            ],

            'transport_doc_no' => [
                'nullable',
                'string',
                'max:100',
            ],

            'transport_doc_date' => [
                'nullable',
                'date',
            ],

            /*
            |--------------------------------------------------------------------------
            | E-Way Bill Number
            | Abhi manual rakha hai.
            |--------------------------------------------------------------------------
            */

            'eway_bill_no' => [
                'nullable',
                'string',
                'max:50',
            ],

            'eway_bill_date' => [
                'nullable',
                'date',
            ],

            'valid_upto' => [
                'nullable',
                'date',
            ],
        ]);

        $ewayBill = EwayBill::create([
            'business_id' => $invoice->business_id,
            'invoice_id' => $invoice->id,

            ...$validated,

            'status' => 'generated',
        ]);

        return redirect()
            ->route('eway-bills.show', $ewayBill->id)
            ->with('success', 'E-Way Bill successfully save ho gaya.');
    }


    /**
     * Show E-Way Bill
     */
    public function show(EwayBill $ewayBill)
    {
        $ewayBill->load([
            'invoice.business',
            'invoice.client',
            'invoice.items',
        ]);

        return view('eway_bills.show', compact('ewayBill'));
    }


    /**
     * Print page
     */
    public function print(EwayBill $ewayBill)
    {
        $ewayBill->load([
            'invoice.business',
            'invoice.client',
            'invoice.items',
        ]);

        return view('eway_bills.print', compact('ewayBill'));
    }
}