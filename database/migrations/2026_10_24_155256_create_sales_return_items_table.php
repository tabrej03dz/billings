
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sales_return_id')
                ->constrained('sales_returns')
                ->cascadeOnDelete();

            $table->foreignId('invoice_item_id')
                ->constrained('invoice_items')
                ->restrictOnDelete();

            $table->foreignId('item_id')
                ->nullable()
                ->constrained('items')
                ->restrictOnDelete();

            $table->decimal('quantity', 18, 3);
            $table->decimal('unit_price', 18, 4);

            $table->decimal('taxable_amount', 18, 2);
            $table->decimal('cgst_amount', 18, 2)->default(0);
            $table->decimal('sgst_amount', 18, 2)->default(0);
            $table->decimal('igst_amount', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2);

            $table->boolean('restock')->default(false);
            $table->text('reason')->nullable();

            $table->timestamps();

            $table->unique(
                ['sales_return_id', 'invoice_item_id'],
                'sales_return_item_unique'
            );

            $table->index('invoice_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_return_items');
    }
};
