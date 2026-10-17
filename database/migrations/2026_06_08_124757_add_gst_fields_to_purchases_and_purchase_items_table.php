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
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('subtotal', 12, 2)->default(0)->after('supplier_id');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('subtotal');

            $table->enum('tax_type', ['intra_state', 'inter_state'])->default('intra_state')->after('discount_amount');

            $table->decimal('cgst_amount', 12, 2)->default(0)->after('tax_type');
            $table->decimal('sgst_amount', 12, 2)->default(0)->after('cgst_amount');
            $table->decimal('igst_amount', 12, 2)->default(0)->after('sgst_amount');

            $table->decimal('round_off', 12, 2)->default(0)->after('igst_amount');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('total_amount');
            $table->decimal('due_amount', 12, 2)->default(0)->after('paid_amount');
            $table->string('bill_file')->nullable();

        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('taxable_amount', 12, 2)->default(0)->after('amount');

            $table->decimal('gst_rate', 5, 2)->default(0)->after('taxable_amount');

            $table->decimal('cgst_rate', 5, 2)->default(0)->after('gst_rate');
            $table->decimal('sgst_rate', 5, 2)->default(0)->after('cgst_rate');
            $table->decimal('igst_rate', 5, 2)->default(0)->after('sgst_rate');

            $table->decimal('cgst_amount', 12, 2)->default(0)->after('igst_rate');
            $table->decimal('sgst_amount', 12, 2)->default(0)->after('cgst_amount');
            $table->decimal('igst_amount', 12, 2)->default(0)->after('sgst_amount');

            $table->decimal('total_amount', 12, 2)->default(0)->after('igst_amount');
            $table->string('qty_unit')->default('pcs')->after('qty');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn([
                'taxable_amount',
                'gst_rate',
                'cgst_rate',
                'sgst_rate',
                'igst_rate',
                'cgst_amount',
                'sgst_amount',
                'igst_amount',
                'total_amount',
                
            ]);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'discount_amount',
                'tax_type',
                'cgst_amount',
                'sgst_amount',
                'igst_amount',
                'round_off',
                'paid_amount',
                'due_amount',
                'bill_file',
                'qty_unit',
            ]);
        });
    }


};
