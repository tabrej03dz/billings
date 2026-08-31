<?php

namespace App\Services;

use App\Models\Item;

class ItemBarcodeService
{
    /**
     * Existing barcode ko kabhi change nahi karega.
     * Sirf blank barcode par naya 12 digit barcode banega.
     */
    public function generate(Item $item): string
    {
        /*
        |--------------------------------------------------------------------------
        | Preserve existing barcode
        |--------------------------------------------------------------------------
        */

        $existingBarcode = trim(
            (string) $item->barcode
        );

        if ($existingBarcode !== '') {
            return $existingBarcode;
        }

        /*
        |--------------------------------------------------------------------------
        | Generate short numeric barcode
        |--------------------------------------------------------------------------
        |
        | Jewellery labels ke liye short numeric barcode
        | long ITM barcode se better scan hota hai.
        |
        */

        do {

            $barcode = (string) random_int(
                100000000000,
                999999999999
            );

        } while (
            Item::query()
                ->where('barcode', $barcode)
                ->exists()
        );

        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        $item->forceFill([
            'barcode' => $barcode,
        ])->save();

        return $barcode;
    }
}