
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE stock_movements
            MODIFY COLUMN type ENUM(
                'opening',
                'purchase',
                'purchase_return',
                'sale',
                'sale_return',
                'adjustment',
                'transfer_in',
                'transfer_out'
            ) NOT NULL
        ");
    }


    public function down(): void
    {
        /*
         * Existing sale_return records ko preserve karne ke liye
         * automatic rollback mein enum remove nahi kar rahe.
         */
    }
};
