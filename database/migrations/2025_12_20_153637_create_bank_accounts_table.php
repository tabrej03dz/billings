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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->index();

            // Label shown in dropdown (e.g. "Main UPI", "HDFC Current")
            $table->string('label')->nullable();

            $table->string('account_holder')->nullable();
            $table->string('account_no')->nullable();
            $table->string('ifsc', 20)->nullable();
            $table->string('bank_name', 120)->nullable();
            $table->string('branch', 120)->nullable();

            // For UPI option
            $table->string('upi_id', 120)->nullable();

            // Optional notes
            $table->text('notes')->nullable();

            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();

            // If you have businesses table:
            // $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();

            // Helpful uniqueness (optional):
            // UPI id unique per business (only when not null)
            $table->unique(['business_id','upi_id']);
            // account no unique per business (only when not null)
            $table->unique(['business_id','account_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
