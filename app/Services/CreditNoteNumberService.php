<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CreditNoteNumberService
{
    public function generate(int $businessId, string $date): string
    {
        /*
         * Call this method from inside the same DB transaction
         * that approves the Sales Return.
         */

        $year = (int) date('Y', strtotime($date));
        $month = (int) date('n', strtotime($date));

        $startYear = $month >= 4 ? $year : $year - 1;

        $financialYear = sprintf(
            '%02d-%02d',
            $startYear % 100,
            ($startYear + 1) % 100
        );

        /*
         * Insert if absent. The unique DB index protects
         * concurrent initialization of a sequence.
         */
        DB::table('credit_note_sequences')->insertOrIgnore([
            'business_id' => $businessId,
            'financial_year' => $financialYear,
            'last_number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sequence = DB::table('credit_note_sequences')
            ->where('business_id', $businessId)
            ->where('financial_year', $financialYear)
            ->lockForUpdate()
            ->first();

        if (!$sequence) {
            throw new \RuntimeException(
                'Credit Note sequence could not be initialized.'
            );
        }

        $next = (int) $sequence->last_number + 1;

        DB::table('credit_note_sequences')
            ->where('id', $sequence->id)
            ->update([
                'last_number' => $next,
                'updated_at' => now(),
            ]);

        return 'CN/'
            . $financialYear
            . '/'
            . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
