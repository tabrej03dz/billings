<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_payment_links', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plan_id')
                ->constrained('plans')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 20);
            $table->string('customer_email')->nullable();

            $table->decimal('base_amount', 12, 2)->default(0);
            $table->decimal('gst_rate', 8, 2)->default(0);
            $table->decimal('gst_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->string('razorpay_payment_link_id')->nullable()->unique();
            $table->string('razorpay_reference_id')->unique();
            $table->text('short_url')->nullable();

            $table->string('status')->default('created');

            $table->string('razorpay_payment_id')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->json('gateway_response')->nullable();

            $table->foreignId('generated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'customer_phone',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_payment_links');
    }
};