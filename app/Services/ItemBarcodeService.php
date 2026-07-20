<?php

namespace App\Services;

use App\Models\Item;

class ItemBarcodeService
{
    /**
     * Flutter-compatible Code 128 barcode value generate karta hai.
     *
     * Format:
     * ITM + 15 numeric digits
     *
     * Example:
     * ITM361784544869296
     */
    public function generate(Item $item): string
    {
        /*
         * Existing valid barcode ko change nahi karega.
         */
        if (
            !empty($item->barcode)
            && preg_match(
                '/^ITM[0-9]{15}$/',
                trim((string) $item->barcode)
            )
        ) {
            return trim((string) $item->barcode);
        }

        do {
            /*
             * Business ID: 3 digits
             *
             * Business ID 1:
             * 001
             */
            $businessPart = str_pad(
                (string) ((int) $item->business_id % 1000),
                3,
                '0',
                STR_PAD_LEFT
            );

            /*
             * Item ID: 6 digits
             *
             * Item ID 76:
             * 000076
             */
            $itemPart = str_pad(
                (string) ((int) $item->id % 1000000),
                6,
                '0',
                STR_PAD_LEFT
            );

            /*
             * Random numeric part: 6 digits
             */
            $randomPart = str_pad(
                (string) random_int(0, 999999),
                6,
                '0',
                STR_PAD_LEFT
            );

            /*
             * Total:
             *
             * ITM
             * + 3 business digits
             * + 6 item digits
             * + 6 random digits
             *
             * ITM + 15 numeric digits
             */
            $barcode = 'ITM'
                . $businessPart
                . $itemPart
                . $randomPart;

        } while (
            Item::withoutGlobalScopes()
                ->where('barcode', $barcode)
                ->where('id', '!=', $item->id)
                ->exists()
        );

        $item->forceFill([
            'barcode' => $barcode,
        ])->saveQuietly();

        return $barcode;
    }
}