<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\StockMovement;
use Illuminate\Support\Collection;

class StockService
{
    public function recordPurchase(Purchase $purchase): void
    {
        foreach ($purchase->items as $row) {
            if (!$row->item_id) {
                continue;
            }

            $this->createMovement(
                itemId: $row->item_id,
                businessId: $purchase->business_id,
                qtyChange: (int) $row->qty, // +ve
                type: 'purchase',
                reference: $purchase,
                grossWeight: $row->gross_weight ?? null,
                metalWeight: $row->metal_weight ?? null,
                stoneWeight: $row->stone_weight ?? null,
                note: 'Purchase #' . ($purchase->invoice_no ?? $purchase->id)
            );
        }

        $this->refreshItemsStock($purchase->items->pluck('item'));
    }

    public function recordSale(Invoice $invoice): void
    {
        foreach ($invoice->items as $row) {
            // agar item master se link nahi hai to stock mat chhedo
            if (!$row->item_id) {
                continue;
            }

            $this->createMovement(
                itemId: $row->item_id,
                businessId: $invoice->business_id,
                // IMPORTANT: quantity column use karo, qty nahi
                qtyChange: -1 * (int) $row->quantity, // sale = negative
                type: 'sale',
                reference: $invoice,
                // aapke invoice_items me mostly metal_weight + stone_charges hai
                grossWeight: null,
                metalWeight: $row->metal_weight ?? null,
                stoneWeight: $row->stone_weight ?? ($row->stone_charges ?? null),
                note: 'Sales Invoice #' . ($invoice->invoice_number ?? $invoice->id)
            );
        }

        $this->refreshItemsStock($invoice->items->pluck('item'));
    }

    public function rollbackReference(object $reference): void
    {
        $class = get_class($reference);

        $items = method_exists($reference, 'items')
            ? $reference->items->pluck('item')
            : collect();

        StockMovement::where('reference_type', $class)
            ->where('reference_id', $reference->id)
            ->delete();

        $this->refreshItemsStock($items);
    }

    public function createAdjustment(Item $item, int $difference, string $note = null): void
    {
        if ($difference === 0) return;

        $this->createMovement(
            itemId: $item->id,
            businessId: $item->business_id,
            qtyChange: $difference,
            type: 'adjustment',
            reference: null,
            note: $note ?? 'Manual stock adjustment'
        );

        $item->refreshStockQty();
    }

    protected function createMovement(
        int $itemId,
        int $businessId,
        int $qtyChange,
        string $type,
        ?object $reference = null,
        ?float $grossWeight = null,
        ?float $metalWeight = null,
        ?float $stoneWeight = null,
        ?string $note = null,
    ): StockMovement {
        return StockMovement::create([
            'business_id'    => $businessId,
            'item_id'        => $itemId,
            'qty_change'     => $qtyChange,
            'gross_weight'   => $grossWeight,
            'metal_weight'   => $metalWeight,
            'stone_weight'   => $stoneWeight,
            'type'           => $type,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id'   => $reference?->id,
            'note'           => $note,
        ]);
    }

    protected function refreshItemsStock(Collection $items): void
    {
        $items->filter()->unique('id')->each(function (Item $item) {
            $item->refreshStockQty();
        });
    }



    public function recordOpening(Item $item, int $qty, ?string $note = null): void
    {
        if ($qty <= 0) {
            $item->refreshStockQty();
            return;
        }

        $this->createMovement(
            itemId: $item->id,
            businessId: $item->business_id,
            qtyChange: $qty,
            type: 'opening',
            reference: null,
            note: $note ?? 'Opening stock from item create'
        );

        $item->refreshStockQty();
    }

    public function setStockTo(Item $item, int $newQty, ?string $note = null): void
    {
        $item->refreshStockQty(); // ensure current correct
        $current = (int)($item->stock_qty ?? 0);

        $diff = $newQty - $current;
        if ($diff === 0) return;

        $this->createMovement(
            itemId: $item->id,
            businessId: $item->business_id,
            qtyChange: $diff, // + or -
            type: 'adjustment',
            reference: null,
            note: $note ?? "Stock set from {$current} to {$newQty}"
        );

        $item->refreshStockQty();
    }

}
