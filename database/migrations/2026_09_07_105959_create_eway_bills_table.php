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
        Schema::create('eway_bills', function (Blueprint $table) {

            $table->id();

            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('invoice_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('eway_bill_no')->nullable();
            $table->dateTime('eway_bill_date')->nullable();
            $table->dateTime('valid_upto')->nullable();

            $table->string('supply_type')->default('O');
            $table->string('sub_supply_type')->default('1');
            $table->string('document_type')->default('INV');

            $table->string('transporter_id')->nullable();
            $table->string('transporter_name')->nullable();

            $table->string('transport_mode')->nullable();

            $table->integer('distance')->nullable();

            $table->string('vehicle_no')->nullable();
            $table->string('vehicle_type')->nullable();

            $table->string('transport_doc_no')->nullable();
            $table->date('transport_doc_date')->nullable();

            $table->string('from_place')->nullable();
            $table->string('from_pincode')->nullable();

            $table->string('to_place')->nullable();
            $table->string('to_pincode')->nullable();

            $table->string('status')->default('draft');

            $table->text('cancel_reason')->nullable();

            $table->json('request_payload')->nullable();
            $table->json('api_response')->nullable();

            $table->timestamps();

            $table->unique('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eway_bills');
    }
};
