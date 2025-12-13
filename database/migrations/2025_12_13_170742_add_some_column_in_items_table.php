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
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('gold_weight', 12, 3)->nullable();
            $table->string('gold_purity')->nullable();
            $table->decimal('silver_weight', 12, 3)->nullable();
            $table->string('silver_purity')->nullable();
            $table->decimal('diamond_weight', 12, 3)->nullable();
            $table->decimal('diamond_charges', 12, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            //
        });
    }
};
