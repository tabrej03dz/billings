<?php

namespace App\Http\Controllers;

use App\Models\EwayBill;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EwayBillController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Active Business ID
    |--------------------------------------------------------------------------
    */

    private function activeBusinessId(): ?int
    {
        return session('active_business_id')
            ?? session('current_business_id')
            ?? auth()->user()?->current_business_id
            ?? auth()->user()?->businesses?->first()?->id;
    }


    /*
    |--------------------------------------------------------------------------
    | Manage / List E-Way Bills
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $businessId = $this->activeBusinessId();

        $query = EwayBill::query()
            ->with([
                'invoice.client',
                'invoice.business',
            ])
            ->latest('id');

        /*
        |--------------------------------------------------------------------------
        | Business Wise Data
        |--------------------------------------------------------------------------
        */

        if ($businessId) {
            $query->where('business_id', $businessId);
        }


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where('eway_bill_no', 'like', "%{$search}%")
                    ->orWhere('document_no', 'like', "%{$search}%")
                    ->orWhere('from_name', 'like', "%{$search}%")
                    ->orWhere('to_name', 'like', "%{$search}%")
                    ->orWhere('vehicle_no', 'like', "%{$search}%")
                    ->orWhereHas('invoice', function ($invoiceQuery) use ($search) {
                        $invoiceQuery
                            ->where('invoice_number', 'like', "%{$search}%")
                            ->orWhereHas('client', function ($clientQuery) use ($search) {
                                $clientQuery->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                );
                            });
                    });
            });
        }


        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }


        /*
        |--------------------------------------------------------------------------
        | Date From
        |--------------------------------------------------------------------------
        */

        if ($request->filled('from_date')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->from_date
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Date To
        |--------------------------------------------------------------------------
        */

        if ($request->filled('to_date')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->to_date
            );
        }


        $ewayBills = $query
            ->paginate(20)
            ->withQueryString();

        return view(
            'eway_bills.index',
            compact('ewayBills')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Form
    |--------------------------------------------------------------------------
    */

    public function create(Invoice $invoice)
    {
        $businessId = $this->activeBusinessId();

        if (
            $businessId &&
            (int) $invoice->business_id !== (int) $businessId
        ) {
            abort(403);
        }

        $invoice->load([
            'business',
            'client',
            'items',
            'ewayBill',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Already Exists
        |--------------------------------------------------------------------------
        */

        if ($invoice->ewayBill) {
            return redirect()
                ->route(
                    'eway-bills.show',
                    $invoice->ewayBill->id
                );
        }

        return view(
            'eway_bills.create',
            compact('invoice')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        Invoice $invoice
    ) {
        $businessId = $this->activeBusinessId();

        if (
            $businessId &&
            (int) $invoice->business_id !== (int) $businessId
        ) {
            abort(403);
        }

        $invoice->load([
            'business',
            'client',
            'items',
            'ewayBill',
        ]);

        if ($invoice->ewayBill) {
            return redirect()
                ->route(
                    'eway-bills.show',
                    $invoice->ewayBill->id
                )
                ->with(
                    'error',
                    'Is invoice ka E-Way Bill already bana hua hai.'
                );
        }

        $validated = $this->validateEwayBill(
            $request
        );

        $ewayBill = DB::transaction(
            function () use (
                $validated,
                $invoice
            ) {

                return EwayBill::create([
                    'business_id' =>
                        $invoice->business_id,

                    'invoice_id' =>
                        $invoice->id,

                    ...$validated,

                    'status' =>
                        $validated['status']
                        ?? 'generated',
                ]);
            }
        );

        return redirect()
            ->route(
                'eway-bills.show',
                $ewayBill->id
            )
            ->with(
                'success',
                'E-Way Bill successfully save ho gaya.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(EwayBill $ewayBill)
    {
        $this->authorizeBusiness(
            $ewayBill
        );

        $ewayBill->load([
            'invoice.business',
            'invoice.client',
            'invoice.items',
        ]);

        return view(
            'eway_bills.show',
            compact('ewayBill')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(EwayBill $ewayBill)
    {
        $this->authorizeBusiness(
            $ewayBill
        );

        $ewayBill->load([
            'invoice.business',
            'invoice.client',
            'invoice.items',
        ]);

        return view(
            'eway_bills.edit',
            compact('ewayBill')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        EwayBill $ewayBill
    ) {
        $this->authorizeBusiness(
            $ewayBill
        );

        $validated = $this->validateEwayBill(
            $request
        );

        $ewayBill->update(
            $validated
        );

        return redirect()
            ->route(
                'eway-bills.show',
                $ewayBill->id
            )
            ->with(
                'success',
                'E-Way Bill successfully update ho gaya.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(
        EwayBill $ewayBill
    ) {
        $this->authorizeBusiness(
            $ewayBill
        );

        $ewayBill->delete();

        return redirect()
            ->route('eway-bills.index')
            ->with(
                'success',
                'E-Way Bill delete ho gaya.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Print
    |--------------------------------------------------------------------------
    */

    public function print(
        EwayBill $ewayBill
    ) {
        $this->authorizeBusiness(
            $ewayBill
        );

        $ewayBill->load([
            'invoice.business',
            'invoice.client',
            'invoice.items',
        ]);

        return view(
            'eway_bills.print',
            compact('ewayBill')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Common Validation
    |--------------------------------------------------------------------------
    */

    private function validateEwayBill(
        Request $request
    ): array {
        return $request->validate([

            'supply_type' => [
                'required',
                'string',
                'max:50',
            ],

            'sub_supply_type' => [
                'required',
                'string',
                'max:100',
            ],

            'document_type' => [
                'required',
                'string',
                'max:100',
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
            | FROM
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
            | TO
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
            | TRANSPORT
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
                'max:50',
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
            | E-WAY DETAILS
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

            'status' => [
                'nullable',
                'in:draft,generated,cancelled,failed',
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Business Security
    |--------------------------------------------------------------------------
    */

    private function authorizeBusiness(
        EwayBill $ewayBill
    ): void {
        $businessId =
            $this->activeBusinessId();

        if (
            $businessId &&
            (int) $ewayBill->business_id
                !== (int) $businessId
        ) {
            abort(403);
        }
    }
}