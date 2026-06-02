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
        Schema::create('plan_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plan_id')
                ->nullable()
                ->constrained('plans')
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('transaction_id')->nullable()->index();

            // pending, success, failed, refunded
            $table->string('payment_status')->default('pending')->index();

            // razorpay, cashfree, stripe, manual etc.
            $table->string('payment_gateway')->nullable();

            $table->string('name')->nullable();
            $table->string('email')->nullable()->index();

            // upi, card, netbanking, wallet, cash etc.
            $table->string('payment_method')->nullable();

            $table->decimal('amount', 10, 2)->default(0);

            // Extra gateway response store karne ke liye useful
            $table->json('gateway_response')->nullable();

            $table->timestamps();

            $table->index(['plan_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_payments');
    }
};
