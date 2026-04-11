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
        Schema::create('bill_requests', function (Blueprint $table) {
            $table->id();
            $table->string('source_software')->nullable();
            $table->string('source_request_id')->nullable();

            $table->unsignedBigInteger('source_customer_id')->nullable();
            $table->unsignedBigInteger('source_package_id')->nullable();
            $table->unsignedBigInteger('source_user_package_id')->nullable();
            $table->unsignedBigInteger('source_payment_id')->nullable();

            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_phone1')->nullable();
            $table->string('business_name')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('pin')->nullable();
            $table->text('address')->nullable();
            $table->string('gst_number', 15)->nullable();

            $table->string('package_name')->nullable();
            $table->decimal('package_price', 12, 2)->nullable();
            $table->integer('package_duration')->nullable();
            $table->decimal('selling_price', 12, 2)->nullable();

            $table->decimal('payment_amount', 12, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('bank')->nullable();
            $table->date('payment_date')->nullable();

            $table->string('activated_by')->nullable();
            $table->string('customer_type')->nullable();
            $table->unsignedBigInteger('old_customer_user_id')->nullable();

            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->text('remarks')->nullable();

            $table->json('full_payload')->nullable();
            $table->json('api_response')->nullable();

            $table->timestamp('requested_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_requests');
    }
};
