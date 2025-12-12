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
            $table->string('bill_no_display')->nullable();   // e.g. KJ/2025-2026/507
            $table->string('transport_mode')->nullable();    // e.g. By Hand
            $table->enum('reverse_charge', ['Y','N'])->default('N');

            // Tax breakup (kapoor bill jaisa)
            $table->decimal('cgst_percent', 5, 2)->default(0);
            $table->decimal('cgst_amount', 10, 2)->default(0);
            $table->decimal('sgst_percent', 5, 2)->default(0);
            $table->decimal('sgst_amount', 10, 2)->default(0);
            $table->decimal('igst_percent', 5, 2)->default(0);
            $table->decimal('igst_amount', 10, 2)->default(0);

            $table->decimal('less_amount', 10, 2)->default(0);
            $table->decimal('final_value', 10, 2)->default(0); // total value (image me 70800)
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
