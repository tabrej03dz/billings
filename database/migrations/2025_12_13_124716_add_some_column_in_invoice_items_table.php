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
            $table->string('hsn_code')->nullable()->after('sac_code');

            // rates
            $table->decimal('making_rate', 12, 2)->nullable()->after('making_charge'); // if you use making per gm
            $table->decimal('gold_rate', 12, 2)->nullable()->after('metal_rate'); // rate per gram
            $table->decimal('silver_rate', 12, 2)->nullable()->after('gold_rate');

            // weights
            $table->decimal('gold_wt', 12, 3)->nullable()->after('metal_weight');
            $table->decimal('silver_wt', 12, 3)->nullable()->after('gold_wt');

            // stones
            $table->decimal('gemstone_wt_ct', 12, 3)->nullable()->after('stone_charges');
            $table->decimal('diamond_wt_ct', 12, 3)->nullable()->after('gemstone_wt_ct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            //
        });
    }
};
