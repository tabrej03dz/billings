
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->string('credit_note_number', 80)
                ->nullable()
                ->after('return_number');

            $table->date('credit_note_date')
                ->nullable()
                ->after('credit_note_number');

            $table->unique(
                ['business_id', 'credit_note_number'],
                'sales_returns_business_credit_note_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->dropUnique(
                'sales_returns_business_credit_note_unique'
            );

            $table->dropColumn([
                'credit_note_number',
                'credit_note_date',
            ]);
        });
    }
};
