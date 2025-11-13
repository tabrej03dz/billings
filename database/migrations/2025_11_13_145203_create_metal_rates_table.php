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
        Schema::create('metal_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->date('rate_date')->index();
            $table->enum('metal_type', ['gold', 'silver']);
            $table->string('purity')->nullable(); // e.g. "24K", "22K", "999"
            $table->decimal('rate_per_gram', 12, 2);
            $table->boolean('is_active')->default(true);

            $table->unique(['rate_date', 'metal_type', 'purity']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metal_rates');
    }
};
