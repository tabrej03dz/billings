<?php

namespace App\Services;

use App\Models\Item;

class ItemBarcodeService
{
    /**
     * 12-digit numeric barcode generate karta hai.
     *
     * Format:
     * Business ID = 3 digits
     * Item ID     = 9 digits
     *
     * Example:
     * Business ID: 1
     * Item ID: 27
     * Barcode: 001000000027
     */
    public function generate(Item $item): string
    {
        if (!empty($item->barcode)) {
            return trim((string) $item->barcode);
        }

        $businessPart = str_pad(
            (string) ((int) $item->business_id % 1000),
            3,
            '0',
            STR_PAD_LEFT
        );

        $itemPart = str_pad(
            (string) $item->id,
            9,
            '0',
            STR_PAD_LEFT
        );

        $barcode = $businessPart . $itemPart;

        /*
         * Normally business ID + item ID unique hota hai.
         * Phir bhi safety ke liye duplicate check.
         */
        if (
            Item::withoutGlobalScopes()
                ->where('barcode', $barcode)
                ->where('id', '!=', $item->id)
                ->exists()
        ) {
            do {
                $barcode = str_pad(
                    (string) random_int(1, 999999999999),
                    12,
                    '0',
                    STR_PAD_LEFT
                );
            } while (
                Item::withoutGlobalScopes()
                    ->where('barcode', $barcode)
                    ->exists()
            );
        }

        $item->forceFill([
            'barcode' => $barcode,
        ])->saveQuietly();

        return $barcode;
    }
}