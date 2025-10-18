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
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('fy', 7);            // e.g. "25-26"
            $table->string('series', 100);      // e.g. "RV/SL/25-26/"
            $table->unsignedBigInteger('next_seq')->default(1);
            $table->timestamps();

            $table->unique(['business_id', 'fy', 'series'], 'uniq_business_fy_series');
            $table->index(['business_id', 'fy']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_sequences');
    }
};
