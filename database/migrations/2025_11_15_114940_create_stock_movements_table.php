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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();

            $table->integer('qty_change'); // +purchase, -sale

            // optional but useful for jewellery
            $table->decimal('gross_weight', 12, 3)->nullable();
            $table->decimal('metal_weight', 12, 3)->nullable();
            $table->decimal('stone_weight', 12, 3)->nullable();

            $table->enum('type', [
                'opening',
                'purchase',
                'purchase_return',
                'sale',
                'sale_return',
                'adjustment',
                'transfer_in',
                'transfer_out',
            ]);

            // polymorphic reference to any doc
            $table->nullableMorphs('reference'); // reference_type + reference_id

            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
