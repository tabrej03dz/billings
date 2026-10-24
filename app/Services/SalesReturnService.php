<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\SalesReturn;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesReturnService
{
    /**
     * All monetary calculations use original invoice values.
     * No current gold/silver market rates are used.
     */
    public function calculate(
        Invoice $invoice,
        array $requestedItems
    ): array {
        if ($invoice->invoice_type !== 'tax') {
            throw ValidationException::withMessages([
                'invoice' => 'Only Tax Invoices can be returned.',
            ]);
        }

        /*
         * Existing invoice-level adjustments require a separate
         * allocation policy. Block them instead of issuing an
         * inaccurate credit note.
         */
        $unsupportedAdjustments = [
            'discount_total',
            'charge_total',
            'tcs_amount',
            'round_off',
        ];

        foreach ($unsupportedAdjustments as $field) {
            if (abs((float) ($invoice->{$field} ?? 0)) > 0.009) {
                throw ValidationException::withMessages([
                    'invoice' =>
                        "Invoice has {$field}. Credit Note allocation "
                        . "for this adjustment is not configured yet.",
                ]);
            }
        }

        $result = [];
        $taxableTotal = 0;
        $cgstTotal = 0;
        $sgstTotal = 0;
        $igstTotal = 0;
        $grandTotal = 0;

        $seenIds = [];

        foreach ($requestedItems as $requestRow) {
            $invoiceItemId = (int) $requestRow['invoice_item_id'];
            $returnQty = round((float) $requestRow['quantity'], 3);

            if (in_array($invoiceItemId, $seenIds, true)) {
                throw ValidationException::withMessages([
                    'items' => 'Duplicate Invoice Item is not allowed.',
                ]);
            }

            $seenIds[] = $invoiceItemId;

            $item = InvoiceItem::query()
                ->where('invoice_id', $invoice->id)
                ->where('id', $invoiceItemId)
                ->firstOrFail();

            $soldQty = round((float) $item->quantity, 3);

            $alreadyReturned = DB::table('sales_return_items as sri')
                ->join(
                    'sales_returns as sr',
                    'sr.id',
                    '=',
                    'sri.sales_return_id'
                )
                ->where('sr.invoice_id', $invoice->id)
                ->where('sri.invoice_item_id', $item->id)
                ->where('sr.status', 'approved')
                ->sum('sri.quantity');

            $availableQty = round(
                $soldQty - (float) $alreadyReturned,
                3
            );

            if (
                $soldQty <= 0 ||
                $returnQty <= 0 ||
                $returnQty > $availableQty + 0.0001
            ) {
                throw ValidationException::withMessages([
                    'items' =>
                        "Item #{$item->id}: Maximum return quantity "
                        . "is {$availableQty}.",
                ]);
            }

            /*
             * invoice_items.rate is the full taxable line amount.
             * invoice_items.amount includes line tax.
             */
            $originalTaxable = round((float) $item->rate, 2);
            $originalTotal = round((float) $item->amount, 2);

            $originalTax = round(
                $originalTotal - $originalTaxable,
                2
            );

            if ($originalTaxable < 0 || $originalTax < -0.01) {
                throw ValidationException::withMessages([
                    'items' => "Invalid original amounts for item #{$item->id}.",
                ]);
            }

            $ratio = $returnQty / $soldQty;

            /*
             * For the final remaining quantity, allocate the
             * remaining original value to avoid rounding drift.
             */
            $isFinalReturn = abs($returnQty - $availableQty) < 0.0001;

            $previousTaxable = (float) DB::table('sales_return_items as sri')
                ->join('sales_returns as sr', 'sr.id', '=', 'sri.sales_return_id')
                ->where('sr.invoice_id', $invoice->id)
                ->where('sri.invoice_item_id', $item->id)
                ->where('sr.status', 'approved')
                ->sum('sri.taxable_amount');

            $previousTotal = (float) DB::table('sales_return_items as sri')
                ->join('sales_returns as sr', 'sr.id', '=', 'sri.sales_return_id')
                ->where('sr.invoice_id', $invoice->id)
                ->where('sri.invoice_item_id', $item->id)
                ->where('sr.status', 'approved')
                ->sum('sri.total_amount');

            $taxable = $isFinalReturn
                ? round($originalTaxable - $previousTaxable, 2)
                : round($originalTaxable * $ratio, 2);

            $lineTotal = $isFinalReturn
                ? round($originalTotal - $previousTotal, 2)
                : round($originalTotal * $ratio, 2);

            $tax = round($lineTotal - $taxable, 2);

            $cgst = 0;
            $sgst = 0;
            $igst = 0;

            /*
             * Original invoice determines whether GST was
             * CGST/SGST or IGST.
             */
            if (
                (float) $invoice->cgst_amount > 0 ||
                (float) $invoice->sgst_amount > 0
            ) {
                $cgst = round($tax / 2, 2);
                $sgst = round($tax - $cgst, 2);
            } else {
                $igst = $tax;
            }

            $result[] = [
                'invoice_item_id' => $item->id,
                'item_id' => $item->item_id,
                'quantity' => $returnQty,
                'unit_price' => round($originalTaxable / $soldQty, 4),
                'taxable_amount' => $taxable,
                'cgst_amount' => $cgst,
                'sgst_amount' => $sgst,
                'igst_amount' => $igst,
                'total_amount' => $lineTotal,
                'restock' => (bool) ($requestRow['restock'] ?? false),
                'reason' => $requestRow['reason'] ?? null,
            ];

            $taxableTotal += $taxable;
            $cgstTotal += $cgst;
            $sgstTotal += $sgst;
            $igstTotal += $igst;
            $grandTotal += $lineTotal;
        }

        return [
            'items' => $result,
            'taxable_amount' => round($taxableTotal, 2),
            'cgst_amount' => round($cgstTotal, 2),
            'sgst_amount' => round($sgstTotal, 2),
            'igst_amount' => round($igstTotal, 2),
            'total_amount' => round($grandTotal, 2),
        ];
    }

    /**
     * Restore stock for a finalised return.
     *
     * Must be called inside the same database transaction
     * that approves the return.
     */
    public function restoreStock(SalesReturn $salesReturn): void
    {
        $salesReturn->loadMissing('items.invoiceItem.item');

        foreach ($salesReturn->items as $row) {
            if (!$row->restock || !$row->item_id) {
                continue;
            }

            $invoiceItem = $row->invoiceItem;
            $masterItem = $invoiceItem?->item;

            if (!$invoiceItem || !$masterItem) {
                throw new \RuntimeException(
                    "Missing stock item for return row {$row->id}."
                );
            }

            if ((int) $masterItem->business_id !==
                (int) $salesReturn->business_id) {
                throw new \RuntimeException(
                    'Business mismatch in return stock.'
                );
            }

            if (strtolower((string) $masterItem->type) === 'service') {
                continue;
            }

            $alreadyExists = StockMovement::query()
                ->where('business_id', $salesReturn->business_id)
                ->where('item_id', $masterItem->id)
                ->where('reference_type', SalesReturn::class)
                ->where('reference_id', $salesReturn->id)
                ->exists();

            if ($alreadyExists) {
                throw new \RuntimeException(
                    "Return stock already recorded for item {$masterItem->id}."
                );
            }

            $billQty = round((float) $row->quantity, 3);
            $stockQty = $billQty;

            $unit = strtolower(trim((string) ($masterItem->unit ?? '')));
            $unit = preg_replace('/[^a-z]/', '', $unit);

            if (in_array($unit, ['g', 'gm', 'gms', 'gram', 'grams'], true)) {
                $goldWt = max(0, (float) ($invoiceItem->gold_wt ?? 0));
                $silverWt = max(0, (float) ($invoiceItem->silver_wt ?? 0));

                $metalType = strtolower(
                    trim((string) ($masterItem->metal_type ?? ''))
                );

                if ($metalType === 'silver') {
                    $weight = $silverWt > 0 ? $silverWt : $goldWt;
                } else {
                    $weight = $goldWt > 0 ? $goldWt : $silverWt;
                }

                if ($weight > 0) {
                    $stockQty = round($weight * $billQty, 3);
                }
            }

            StockMovement::create([
                'business_id' => $salesReturn->business_id,
                'item_id' => $masterItem->id,
                'qty_change' => $stockQty,
                'type' => 'sale_return',
                'reference_type' => SalesReturn::class,
                'reference_id' => $salesReturn->id,
                'note' => 'Sales Return #' . $salesReturn->return_number,
            ]);

            /*
             * Use the existing StockService stock recalculation.
             * setStockTo() sees the newly inserted movement and
             * refreshes the cached stock_qty without adding another
             * movement when balances already match.
             */
            $currentStock = StockMovement::query()
                ->where('business_id', $masterItem->business_id)
                ->where('item_id', $masterItem->id)
                ->sum('qty_change');

            app(StockService::class)->setStockTo(
                $masterItem,
                $currentStock,
                'Refresh after sales return'
            );
        }
    }


    public function approve(
        \App\Models\SalesReturn $salesReturn
    ): \App\Models\SalesReturn {
        return \Illuminate\Support\Facades\DB::transaction(
            function () use ($salesReturn) {

                $invoice = \App\Models\Invoice::query()
                    ->where('business_id', $salesReturn->business_id)
                    ->lockForUpdate()
                    ->findOrFail($salesReturn->invoice_id);

                $return = \App\Models\SalesReturn::query()
                    ->where('business_id', $salesReturn->business_id)
                    ->where('invoice_id', $invoice->id)
                    ->lockForUpdate()
                    ->findOrFail($salesReturn->id);

                if ($return->status !== 'draft') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'return' => 'Only draft returns can be approved.',
                    ]);
                }

                $return->load('items.invoiceItem.item');

                if ($return->items->isEmpty()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => 'No return items found.',
                    ]);
                }

                /*
                * Recalculate everything from original invoice.
                * This prevents stale draft amounts and double returns.
                */
                $requestedItems = $return->items->map(function ($row) {
                    return [
                        'invoice_item_id' => $row->invoice_item_id,
                        'quantity' => $row->quantity,
                        'restock' => $row->restock,
                        'reason' => $row->reason,
                    ];
                })->all();

                $calculation = $this->calculate(
                    $invoice,
                    $requestedItems
                );

                $stockService = app(\App\Services\StockService::class);

                foreach ($calculation['items'] as $calculatedRow) {
                    $returnItem = $return->items->firstWhere(
                        'invoice_item_id',
                        $calculatedRow['invoice_item_id']
                    );

                    if (!$returnItem) {
                        throw new \RuntimeException(
                            'Return item mismatch.'
                        );
                    }

                    $returnItem->update($calculatedRow);

                    if (!$returnItem->restock || !$returnItem->item_id) {
                        continue;
                    }

                    $invoiceItem = $returnItem->invoiceItem;
                    $masterItem = $invoiceItem?->item;

                    if (!$invoiceItem || !$masterItem) {
                        throw new \RuntimeException(
                            'Original stock item not found.'
                        );
                    }

                    $returnQty = round(
                        (float) $returnItem->quantity,
                        3
                    );

                    $stockQty = $returnQty;

                    /*
                    * Jewellery gram conversion.
                    * Use original invoice item weight.
                    */
                    $unit = strtolower(
                        trim((string) ($masterItem->unit ?? ''))
                    );

                    $unit = preg_replace('/[^a-z]/', '', $unit);

                    if (in_array(
                        $unit,
                        ['g', 'gm', 'gms', 'gram', 'grams'],
                        true
                    )) {
                        $metalType = strtolower(
                            trim((string) ($masterItem->metal_type ?? ''))
                        );

                        $goldWeight = (float) (
                            $invoiceItem->gold_wt ?? 0
                        );

                        $silverWeight = (float) (
                            $invoiceItem->silver_wt ?? 0
                        );

                        $weightPerUnit = $metalType === 'silver'
                            ? ($silverWeight > 0 ? $silverWeight : $goldWeight)
                            : ($goldWeight > 0 ? $goldWeight : $silverWeight);

                        if ($weightPerUnit <= 0) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'stock' =>
                                    'Original jewellery weight is missing for item '
                                    . $invoiceItem->id,
                            ]);
                        }

                        $stockQty = round(
                            $weightPerUnit * $returnQty,
                            3
                        );
                    }

                    $stockService->recordReturnStock(
                        $return,
                        $returnItem,
                        $stockQty
                    );
                }

                
                $creditNoteNumber = app(
                    \App\Services\CreditNoteNumberService::class
                )->generate(
                    (int) $return->business_id,
                    now()->toDateString()
                );

                $return->update([
                    'taxable_amount' => $calculation['taxable_amount'],
                    'cgst_amount' => $calculation['cgst_amount'],
                    'sgst_amount' => $calculation['sgst_amount'],
                    'igst_amount' => $calculation['igst_amount'],
                    'total_amount' => $calculation['total_amount'],

                    'credit_note_number' => $creditNoteNumber,
                    'credit_note_date' => now()->toDateString(),

                    'refund_due' => $calculation['total_amount'],
                    'refund_amount' => 0,
                    'refund_status' => 'pending',

                    'status' => 'approved',
                ]);


                return $return->fresh(['items', 'invoice']);
            },
            3
        );
    }
}
