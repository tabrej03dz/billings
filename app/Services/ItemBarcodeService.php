<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Support\Str;

class ItemBarcodeService
{
    /**
     * Item ka unique barcode generate karega.
     * Existing barcode ko change nahi karega.
     */
    public function generate(Item $item): string
    {
        if (!empty($item->barcode)) {
            return $item->barcode;
        }

        do {
            $businessPart = str_pad(
                (string) ($item->business_id ?? 0),
                4,
                '0',
                STR_PAD_LEFT
            );

            $itemPart = str_pad(
                (string) $item->id,
                8,
                '0',
                STR_PAD_LEFT
            );

            $randomPart = strtoupper(Str::random(4));

            $barcode = "ITM{$businessPart}{$itemPart}{$randomPart}";
        } while (
            Item::withoutGlobalScopes()
                ->where('barcode', $barcode)
                ->exists()
        );

        $item->forceFill([
            'barcode' => $barcode,
        ])->saveQuietly();

        return $barcode;
    }
}