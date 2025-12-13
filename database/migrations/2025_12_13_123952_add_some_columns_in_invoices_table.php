<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('transport_mode')->nullable()->after('payment_method'); // By Hand / Courier
                $table->boolean('reverse_charge')->default(false)->after('transport_mode');

                $table->string('place_of_supply_state')->nullable()->after('reverse_charge'); // e.g. UP
                $table->string('place_of_supply_code', 5)->nullable()->after('place_of_supply_state'); // e.g. 09

                // tax breakup store (optional but helpful)
                $table->decimal('cgst_percent', 5, 2)->default(0)->after('tax_amount');
                $table->decimal('cgst_amount', 10, 2)->default(0)->after('cgst_percent');
                $table->decimal('sgst_percent', 5, 2)->default(0)->after('cgst_amount');
                $table->decimal('sgst_amount', 10, 2)->default(0)->after('sgst_percent');
                $table->decimal('igst_percent', 5, 2)->default(0)->after('sgst_amount');
                $table->decimal('igst_amount', 10, 2)->default(0)->after('igst_percent');

                // “Less” (if you want exactly like sample)
                $table->decimal('less_amount', 10, 2)->default(0)->after('round_off');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            //
        });
    }
};
