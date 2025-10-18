<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
//        Schema::create('invoices', function (Blueprint $table) {
//            $table->id();
//            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
//            $table->string('invoice_number'); // unique per business
//            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
//            $table->date('invoice_date');
//
//            $table->decimal('subtotal', 10, 2)->default(0);
//            $table->decimal('tax_amount', 10, 2)->default(0);
//            $table->decimal('total', 10, 2)->default(0);
//            $table->decimal('received_amount', 10, 2)->default(0);
//            $table->decimal('balance', 10, 2)->default(0);
//
//            $table->string('amount_in_words');
//
//            $table->unique(['business_id','invoice_number']);
//            $table->index(['business_id','client_id']);
//            $table->timestamps();
//        });



        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('invoice_prefix')->nullable(); // e.g. "RV/SL/25-26/"
            $table->string('invoice_number'); // unique per business
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->date('invoice_date');
            $table->integer('payment_terms')->default(0);
            $table->date('due_date')->nullable();

            // Totals
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_total', 10, 2)->default(0);
            $table->decimal('charge_total', 10, 2)->default(0);
            $table->decimal('tcs_percent', 5, 2)->default(0);
            $table->decimal('tcs_amount', 10, 2)->default(0);
            $table->decimal('round_off', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('received_amount', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);

            // Payment
            $table->string('payment_method')->nullable(); // Cash, UPI, Card, NEFT etc.

            // Extra info
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            // JSON storage for audit/flexibility
            $table->json('charges_json')->nullable();  // stores selected additional charges
            $table->json('items_json')->nullable();    // snapshot of line items for backup

            $table->string('amount_in_words')->nullable();

            $table->unique(['business_id','invoice_number']);
            $table->index(['business_id','client_id']);
            $table->timestamps();
        });

    }

    public function down(): void {
        Schema::dropIfExists('invoices');
    }
};
