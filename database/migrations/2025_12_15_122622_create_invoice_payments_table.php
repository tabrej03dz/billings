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
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();

            // Screenshot: Total Value
            $table->decimal('total_value', 12, 2)->default(0);

            // Screenshot: Payment Adjustments
            $table->decimal('cash_amount',   12, 2)->default(0);
            $table->decimal('online_amount', 12, 2)->default(0); // UPI/Bank/Online/Cheque received (as online)
            $table->decimal('card_amount',   12, 2)->default(0);
            $table->decimal('cheque_amount', 12, 2)->default(0);

            // Online / UPI / Bank refs
            $table->string('online_mode', 30)->nullable(); // upi|bank|neft|rtgs|imps|wallet|other
            $table->string('online_ref', 100)->nullable(); // UTR/Txn/Ref no
            $table->string('upi_id', 100)->nullable();

            // Card info (never store full card)
            $table->string('card_last4', 4)->nullable();
            $table->string('card_ref', 100)->nullable();

            // Cheque info (optional)
            $table->string('cheque_no', 50)->nullable();
            $table->string('bank_name', 100)->nullable();

            // Screenshot: Credit Sales/Excess Amt, Advance
            $table->decimal('credit_sales_excess_amount', 12, 2)->default(0);
            $table->decimal('advance_amount', 12, 2)->default(0);

            // Total received in this payment entry
            $table->decimal('received_total', 12, 2)->default(0);

            $table->text('notes')->nullable();
            $table->json('meta')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'invoice_id']);
            $table->index(['business_id', 'client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
