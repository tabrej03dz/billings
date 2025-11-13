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
            $table->enum('metal_type', ['gold', 'silver', 'other'])->default('gold');
            $table->string('purity')->nullable(); // e.g. "22K"
            $table->decimal('gross_weight', 12, 3)->nullable();
            $table->decimal('metal_weight', 12, 3)->nullable();
            $table->decimal('stone_weight', 12, 3)->nullable();
            $table->decimal('stone_charges', 12, 2)->nullable();
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
