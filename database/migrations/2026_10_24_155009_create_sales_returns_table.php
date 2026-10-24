
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->restrictOnDelete();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->restrictOnDelete();

            $table->foreignId('client_id')
                ->nullable()
                ->constrained('clients')
                ->nullOnDelete();

            $table->string('return_number', 80);
            $table->date('return_date');

            $table->decimal('taxable_amount', 18, 2)->default(0);
            $table->decimal('cgst_amount', 18, 2)->default(0);
            $table->decimal('sgst_amount', 18, 2)->default(0);
            $table->decimal('igst_amount', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);

            $table->decimal('refund_amount', 18, 2)->default(0);
            $table->decimal('refund_due', 18, 2)->default(0);

            $table->string('refund_method', 30)
                ->nullable();

            $table->string('refund_status', 30)
                ->default('pending');

            $table->string('status', 30)
                ->default('draft');

            $table->text('reason')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['business_id', 'return_number'],
                'sales_returns_business_number_unique'
            );

            $table->index(['business_id', 'return_date']);
            $table->index(['invoice_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_returns');
    }
};
