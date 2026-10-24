<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\SalesReturn;
use App\Services\SalesReturnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SalesReturnController extends Controller
{
    public function __construct(
        protected SalesReturnService $returnService
    ) {}

    private function businessId(Request $request): int
    {
        $user = $request->user();

        $id = $user->current_business_id
            ?? session('active_business_id')
            ?? $user->businesses()->pluck('businesses.id')->first();

        abort_unless($id, 403, 'Active business not found.');

        $hasAccess = $user->businesses()
            ->where('businesses.id', $id)
            ->exists();

        abort_unless(
            $hasAccess || $user->hasRole('super_admin'),
            403,
            'Business access denied.'
        );

        return (int) $id;
    }

    private function authorizeView(Request $request): void
    {
        abort_unless(
            $request->user()->can('show invoices'),
            403
        );
    }

    private function authorizeCreate(Request $request): void
    {
        abort_unless(
            $request->user()->can('create invoice'),
            403
        );
    }

    public function index(Request $request)
    {
        $this->authorizeView($request);

        $returns = SalesReturn::query()
            ->where('business_id', $this->businessId($request))
            ->with(['invoice', 'client'])
            ->latest('id')
            ->paginate(20);

        return view('sales-returns.index', compact('returns'));
    }

    // public function create(Request $request, Invoice $invoice)
    // {
    //     $this->authorizeCreate($request);

    //     abort_unless(
    //         (int) $invoice->business_id === $this->businessId($request),
    //         404
    //     );

    //     abort_unless($invoice->invoice_type === 'tax', 422);

    //     $invoice->load(['client', 'items.item']);

    //     return view('sales-returns.create', compact('invoice'));
    // }

    
public function create(Request $request, Invoice $invoice)
{
    $this->authorizeCreate($request);

    $businessId = $this->businessId($request);

    abort_unless(
        (int) $invoice->business_id === $businessId,
        404
    );

    abort_unless(
        $invoice->invoice_type === 'tax',
        422,
        'Only Tax Invoices can be returned.'
    );

    $invoice->load(['client', 'items.item']);

    $returnedQuantities = DB::table('sales_return_items as sri')
        ->join(
            'sales_returns as sr',
            'sr.id',
            '=',
            'sri.sales_return_id'
        )
        ->where('sr.invoice_id', $invoice->id)
        ->where('sr.business_id', $businessId)
        ->where('sr.status', 'approved')
        ->select(
            'sri.invoice_item_id',
            DB::raw('SUM(sri.quantity) as returned_qty')
        )
        ->groupBy('sri.invoice_item_id')
        ->pluck('returned_qty', 'invoice_item_id');

    foreach ($invoice->items as $item) {
        $soldQty = round((float) $item->quantity, 3);

        $returnedQty = round(
            (float) ($returnedQuantities[$item->id] ?? 0),
            3
        );

        $item->returnable_quantity = max(
            0,
            round($soldQty - $returnedQty, 3)
        );
    }

    return view('sales-returns.create', compact('invoice'));
}


    public function store(Request $request, Invoice $invoice)
    {
        $this->authorizeCreate($request);

        $businessId = $this->businessId($request);

        $data = $request->validate([
            'return_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.invoice_item_id' => [
                'required',
                'integer',
                Rule::exists('invoice_items', 'id')
                    ->where('invoice_id', $invoice->id),
            ],
            'items.*.quantity' => [
                'required', 'numeric', 'gt:0', 'decimal:0,3',
            ],
            'items.*.restock' => ['nullable', 'boolean'],
            'items.*.reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $return = DB::transaction(function () use (
            $invoice,
            $businessId,
            $request,
            $data
        ) {
            /*
             * Lock the original invoice to serialize concurrent
             * return creation for the same invoice.
             */
            $lockedInvoice = Invoice::query()
                ->where('business_id', $businessId)
                ->where('invoice_type', 'tax')
                ->lockForUpdate()
                ->findOrFail($invoice->id);

            $calculation = $this->returnService->calculate(
                $lockedInvoice,
                $data['items']
            );

            $return = SalesReturn::create([
                'business_id' => $businessId,
                'invoice_id' => $lockedInvoice->id,
                'client_id' => $lockedInvoice->client_id,
                'return_number' => 'TEMP-' . (string) \Illuminate\Support\Str::uuid(),
                'return_date' => $data['return_date'],
                'taxable_amount' => $calculation['taxable_amount'],
                'cgst_amount' => $calculation['cgst_amount'],
                'sgst_amount' => $calculation['sgst_amount'],
                'igst_amount' => $calculation['igst_amount'],
                'total_amount' => $calculation['total_amount'],
                'refund_amount' => 0,
                'refund_due' => 0,
                'refund_status' => 'pending',
                'status' => 'draft',
                'reason' => $data['reason'],
                'created_by' => $request->user()->id,
            ]);

            $return->update([
                'return_number' => 'SR-'
                    . now()->format('Ymd')
                    . '-'
                    . str_pad((string) $return->id, 6, '0', STR_PAD_LEFT),
            ]);

            $return->items()->createMany($calculation['items']);

            return $return;
        });

        return redirect()
            ->route('sales-returns.show', $return->id)
            ->with('success', 'Sales Return draft created successfully.');
    }

    public function show(Request $request, SalesReturn $salesReturn)
    {
        $this->authorizeView($request);

        abort_unless(
            (int) $salesReturn->business_id === $this->businessId($request),
            404
        );

        $salesReturn->load([
            'invoice',
            'client',
            'items.invoiceItem.item',
            'createdBy',
        ]);

        return view('sales-returns.show', compact('salesReturn'));
    }


    public function approve(
    Request $request,
    SalesReturn $salesReturn
) {
    $this->authorizeCreate($request);

    $businessId = $this->businessId($request);

    abort_unless(
        (int) $salesReturn->business_id === $businessId,
        404
    );

    try {
        $this->returnService->approve($salesReturn);

        return redirect()
            ->route('sales-returns.show', $salesReturn->id)
            ->with(
                'success',
                'Sales Return approved and stock updated successfully.'
            );

    } catch (\Illuminate\Validation\ValidationException $e) {
        throw $e;
    } catch (\Throwable $e) {
        report($e);

        return back()->withErrors([
            'return' => 'Unable to approve return. Please check the server logs.',
        ]);
    }
}



public function downloadCreditNote(
    Request $request,
    SalesReturn $salesReturn
) {
    $this->authorizeView($request);

    $businessId = $this->businessId($request);

    abort_unless(
        (int) $salesReturn->business_id === $businessId,
        404
    );

    abort_unless(
        $salesReturn->status === 'approved'
        && filled($salesReturn->credit_note_number),
        422,
        'Credit Note is not issued for this return.'
    );

    $salesReturn->load([
        'business',
        'invoice',
        'client',
        'items.invoiceItem.item',
    ]);

    abort_unless(
        (int) $salesReturn->invoice?->business_id === $businessId,
        404
    );

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
        'sales-returns.pdf',
        [
            'salesReturn' => $salesReturn,
        ]
    )->setPaper('a4', 'portrait');

    return $pdf->download(
        'Credit-Note-' . $salesReturn->id . '.pdf'
    );
}

}
