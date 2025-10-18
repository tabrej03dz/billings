<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceNumber
{
    /**
     * Return FY string like "25-26" from any Y-m-d date.
     */
    public static function fyFromDate(string $date): string
    {
        $d = Carbon::parse($date);
        $yy = (int)$d->format('y');
        $mm = (int)$d->format('m');
        $start = ($mm >= 4) ? $yy : ($yy - 1);

        $a = str_pad(((($start + 100) % 100)), 2, '0', STR_PAD_LEFT);
        $b = str_pad(((($start + 1 + 100) % 100)), 2, '0', STR_PAD_LEFT);
        return "{$a}-{$b}";
    }

    /**
     * Make a suggested prefix for a given date & base (e.g. base "RV/SL" -> "RV/SL/25-26/").
     * Always ensures the trailing slash.
     */
    public static function previewPrefix(string $date, string $base = 'RV/SL'): string
    {
        $base = trim($base);
        $base = rtrim($base, '/');
        $fy   = self::fyFromDate($date);
        return "{$base}/{$fy}/";
    }

    /**
     * Build full invoice number string from series(prefix) + seq with padding,
     * and validate GST's 16-char limit (prefix + number + separators).
     *
     * @throws \RuntimeException if length exceeds 16 chars.
     */
    protected static function buildFull(string $series, int $seq, int $pad = 3): string
    {
        // Ensure the series ends with '/' or '-'
        if (!Str::endsWith($series, ['/','-'])) {
            $series = rtrim($series, '/') . '/';
        }

        $number = str_pad((string)$seq, max(1, $pad), '0', STR_PAD_LEFT);
        $full   = $series . $number;

        if (mb_strlen($full) > 16) {
            throw new \RuntimeException("Invoice number exceeds 16 characters: '{$full}'");
        }
        return $full;
    }

    /**
     * PREVIEW: Get the next invoice number WITHOUT incrementing the sequence.
     * (Used on the create form to show read-only preview)
     *
     * Returns: ['full' => 'RV/SL/25-26/001', 'prefix' => 'RV/SL/25-26/', 'seq' => 1]
     */
    public static function peek(int $businessId, string $invoiceDate, string $prefix, int $pad = 3): array
    {
        $prefix = trim($prefix);
        $fy     = self::fyFromDate($invoiceDate);

        $row = DB::table('invoice_sequences')
            ->where('business_id', $businessId)
            ->where('fy', $fy)
            ->where('series', $prefix)
            ->first();

        $nextSeq = $row ? (int)$row->next_seq : 1;

        $full = self::buildFull($prefix, $nextSeq, $pad);

        return [
            'full'   => $full,
            'prefix' => $prefix,
            'seq'    => $nextSeq,
        ];
    }

    /**
     * ALLOCATE: Atomically get-and-increment the next invoice number for (business, fy, prefix).
     * Use inside store() to write the final number.
     *
     * Returns: ['full' => 'RV/SL/25-26/001', 'prefix' => 'RV/SL/25-26/', 'seq' => 1]
     */
    public static function next(int $businessId, string $invoiceDate, ?string $customPrefix = null, int $pad = 3): array
    {
        $fy     = self::fyFromDate($invoiceDate);
        $series = $customPrefix ? trim($customPrefix) : self::previewPrefix($invoiceDate);

        return DB::transaction(function () use ($businessId, $fy, $series, $pad) {
            // Lock the specific row if exists
            $row = DB::table('invoice_sequences')
                ->where('business_id', $businessId)
                ->where('fy', $fy)
                ->where('series', $series)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                // Insert a new row with next_seq = 1
                DB::table('invoice_sequences')->insert([
                    'business_id' => $businessId,
                    'fy'          => $fy,
                    'series'      => $series,
                    'next_seq'    => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                $seq  = 1;
                $full = self::buildFull($series, $seq, $pad);

                // Bump it to 2 after allocating 1
                DB::table('invoice_sequences')
                    ->where('business_id', $businessId)
                    ->where('fy', $fy)
                    ->where('series', $series)
                    ->update([
                        'next_seq'   => 2,
                        'updated_at' => now(),
                    ]);

                return ['full' => $full, 'prefix' => $series, 'seq' => $seq];
            }

            // Already exists: allocate current next_seq, then increment
            $seq  = max(1, (int)$row->next_seq);
            $full = self::buildFull($series, $seq, $pad);

            DB::table('invoice_sequences')
                ->where('business_id', $businessId)
                ->where('fy', $fy)
                ->where('series', $series)
                ->update([
                    'next_seq'   => $seq + 1,
                    'updated_at' => now(),
                ]);

            return ['full' => $full, 'prefix' => $series, 'seq' => $seq];
        });
    }
}
