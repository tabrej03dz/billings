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
//        Schema::create('invoice_items', function (Blueprint $table) {
//            $table->id();
//            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
//            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete(); // optional link to item master
//            $table->string('description');
//            $table->string('sac_code')->nullable();
//            $table->integer('quantity');
//            $table->decimal('rate', 10, 2);
//            $table->decimal('tax_percent', 5, 2)->default(0);
//            $table->decimal('amount', 10, 2);
//            $table->timestamps();
//        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('description');
            $table->string('sac_code')->nullable();
            $table->integer('quantity');
            $table->decimal('rate', 10, 2);
            $table->decimal('making_charge', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->default(0); // NEW
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
