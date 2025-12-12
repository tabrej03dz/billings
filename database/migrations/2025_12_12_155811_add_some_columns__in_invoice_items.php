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
        Schema::table('invoice_items', function (Blueprint $table) {

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('hsn_code')->nullable(); // same as sac_code but rename if you want
            $table->decimal('making_rate', 12, 2)->nullable(); // (image me 6165)
            $table->decimal('gold_rate', 12, 2)->nullable();   // (image me 10125)
            $table->decimal('silver_rate', 12, 2)->nullable();

            $table->decimal('silver_wt_gm', 12, 3)->nullable();
            $table->decimal('gold_wt_gm', 12, 3)->nullable();
            $table->decimal('gem_wt_ct', 12, 3)->nullable();
            $table->decimal('diamond_wt_ct', 12, 3)->nullable();
        });
    }
};
