<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\StockMovement;
use Illuminate\Support\Collection;

class StockService
{
    /**
     * Stock precision:
     * 3 decimal places are enough for jewellery gram stock.
     * Example: 0.001 gm
     *
     * Normal businesses are also safe:
     * 2 pieces will simply be stored as 2.000.
     */
    private const STOCK_SCALE = 3;

    /**
     * Convert any stock value to a decimal-safe float.
     */
    protected function qty($value): float
    {
        return round((float) ($value ?? 0), self::STOCK_SCALE);
    }

    /**
     * Compare stock values safely up to 0.001 precision.
     */
    protected function sameQty($a, $b): bool
    {
        return abs($this->qty($a) - $this->qty($b)) < 0.0005;
    }

    public function recordPurchase(Purchase $purchase): void
    {
        $itemIds = collect();

        foreach ($purchase->items as $row) {
            if (!$row->item_id) {
                continue;
            }

            $qtyChange = $this->qty($row->qty);

            if ($this->sameQty($qtyChange, 0)) {
                continue;
            }

            $this->createMovement(
                itemId: (int) $row->item_id,
                businessId: (int) $purchase->business_id,
                qtyChange: $qtyChange,
                type: 'purchase',
                reference: $purchase,
                grossWeight: $row->gross_weight ?? null,
                metalWeight: $row->metal_weight ?? null,
                stoneWeight: $row->stone_weight ?? null,
                note: 'Purchase #' . ($purchase->invoice_no ?? $purchase->id)
            );

            $itemIds->push((int) $row->item_id);
        }

        $this->refreshItemsByIds($itemIds);
    }

    /**
     * Record invoice stock sale.
     *
     * IMPORTANT:
     * InvoiceController can pass an in-memory quantity such as 7.850
     * for gram-based jewellery items.
     *
     * We NEVER int-cast quantity here.
     */
    public function recordSale(Invoice $invoice): void
    {
        $itemIds = collect();

        foreach ($invoice->items as $row) {
            if (!$row->item_id) {
                continue;
            }

            $quantity = $this->qty($row->quantity);

            if ($quantity <= 0) {
                continue;
            }

            $this->createMovement(
                itemId: (int) $row->item_id,
                businessId: (int) $invoice->business_id,
                qtyChange: $this->qty(-1 * $quantity),
                type: 'sale',
                reference: $invoice,
                grossWeight: $row->gross_weight ?? null,
                metalWeight: $row->metal_weight ?? null,
                stoneWeight: $row->stone_weight ?? ($row->stone_charges ?? null),
                note: 'Sales Invoice #' . ($invoice->invoice_number ?? $invoice->id)
            );

            $itemIds->push((int) $row->item_id);
        }

        $this->refreshItemsByIds($itemIds);
    }

    /**
     * IMPORTANT FOR INVOICE UPDATE:
     * Removes previous sale movement(s) of this invoice and restores stock.
     *
     * Your InvoiceController already checks:
     * method_exists($stock, 'rollbackSale')
     *
     * Earlier this method did not exist, so update was deducting stock again.
     */
    public function rollbackSale(Invoice $invoice): void
    {
        $this->rollbackReference($invoice);
    }

    /**
     * Delete all stock movements belonging to a reference
     * and recalculate affected item stock.
     */
    public function rollbackReference(object $reference): void
    {
        $class = get_class($reference);

        /*
         * Get affected item IDs FROM STOCK MOVEMENTS itself.
         *
         * This is more reliable than $reference->items because during
         * invoice update old InvoiceItem rows may later be deleted/replaced.
         */
        $itemIds = StockMovement::query()
            ->where('reference_type', $class)
            ->where('reference_id', $reference->id)
            ->pluck('item_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        StockMovement::query()
            ->where('reference_type', $class)
            ->where('reference_id', $reference->id)
            ->delete();

        $this->refreshItemsByIds($itemIds);
    }

    public function createAdjustment(Item $item, $difference, string $note = null): void
    {
        $difference = $this->qty($difference);

        if ($this->sameQty($difference, 0)) {
            return;
        }

        $this->createMovement(
            itemId: (int) $item->id,
            businessId: (int) $item->business_id,
            qtyChange: $difference,
            type: 'adjustment',
            reference: null,
            note: $note ?? 'Manual stock adjustment'
        );

        $this->refreshItemStock($item);
    }

    protected function createMovement(
        int $itemId,
        int $businessId,
        float $qtyChange,
        string $type,
        ?object $reference = null,
        ?float $grossWeight = null,
        ?float $metalWeight = null,
        ?float $stoneWeight = null,
        ?string $note = null,
    ): StockMovement {
        /*
         * Format as 3-decimal string before DB insert.
         * Example:
         * 7.85   => "7.850"
         * 0.001  => "0.001"
         * -7.85  => "-7.850"
         */
        $qtyChange = number_format(
            $this->qty($qtyChange),
            self::STOCK_SCALE,
            '.',
            ''
        );

        return StockMovement::create([
            'business_id'    => $businessId,
            'item_id'        => $itemId,
            'qty_change'     => $qtyChange,

            'gross_weight'   => $grossWeight !== null
                ? number_format((float) $grossWeight, self::STOCK_SCALE, '.', '')
                : null,

            'metal_weight'   => $metalWeight !== null
                ? number_format((float) $metalWeight, self::STOCK_SCALE, '.', '')
                : null,

            'stone_weight'   => $stoneWeight !== null
                ? number_format((float) $stoneWeight, self::STOCK_SCALE, '.', '')
                : null,

            'type'           => $type,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id'   => $reference?->id,
            'note'           => $note,
        ]);
    }

    /**
     * Legacy collection refresh support.
     */
    protected function refreshItemsStock(Collection $items): void
    {
        $items
            ->filter()
            ->unique('id')
            ->each(function (Item $item) {
                $this->refreshItemStock($item);
            });
    }

    /**
     * Refresh stock for item IDs from DB.
     * This avoids dependency on preloaded item relation.
     */
    protected function refreshItemsByIds(Collection $itemIds): void
    {
        $ids = $itemIds
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        Item::query()
            ->whereIn('id', $ids)
            ->get()
            ->each(function (Item $item) {
                $this->refreshItemStock($item);
            });
    }

    /**
     * Recalculate item stock from stock_movements using decimal precision.
     *
     * We calculate here directly instead of trusting any old int-cast
     * implementation inside Item::refreshStockQty().
     */
    protected function refreshItemStock(Item $item): void
    {
        $stock = StockMovement::query()
            ->where('business_id', $item->business_id)
            ->where('item_id', $item->id)
            ->sum('qty_change');

        $stock = $this->qty($stock);

        /*
         * IMPORTANT:
         * items.stock_qty column MUST be DECIMAL(18,3).
         */
        $item->forceFill([
            'stock_qty' => number_format(
                $stock,
                self::STOCK_SCALE,
                '.',
                ''
            ),
        ])->saveQuietly();

        $item->refresh();
    }

    public function recordOpening(Item $item, $qty, ?string $note = null): void
    {
        $qty = $this->qty($qty);

        if ($qty <= 0) {
            $this->refreshItemStock($item);
            return;
        }

        $this->createMovement(
            itemId: (int) $item->id,
            businessId: (int) $item->business_id,
            qtyChange: $qty,
            type: 'opening',
            reference: null,
            note: $note ?? 'Opening stock from item create'
        );

        $this->refreshItemStock($item);
    }

    public function setStockTo(Item $item, $newQty, ?string $note = null): void
    {
        /*
         * IMPORTANT:
         * Old code had:
         * $current = (int)($item->stock_qty ?? 0);
         *
         * That converted 7.850 into 7.
         */
        $this->refreshItemStock($item);

        $current = $this->qty($item->stock_qty);
        $newQty  = $this->qty($newQty);

        $diff = $this->qty($newQty - $current);

        if ($this->sameQty($diff, 0)) {
            return;
        }

        $this->createMovement(
            itemId: (int) $item->id,
            businessId: (int) $item->business_id,
            qtyChange: $diff,
            type: 'adjustment',
            reference: null,
            note: $note ?? "Stock set from {$current} to {$newQty}"
        );

        $this->refreshItemStock($item);
    }
}