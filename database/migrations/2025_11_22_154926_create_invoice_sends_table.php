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
        Schema::create('invoice_sends', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();     // kisne send kiya
            $table->unsignedBigInteger('invoice_id')->nullable();  // kaun sa invoice

            // channel: whatsapp / email / sms etc.
            $table->string('channel')->default('whatsapp');

            // jisko bheja
            $table->string('recipient_phone')->nullable();
            $table->string('recipient_email')->nullable();

            // status/info
            $table->string('status')->default('pending');      // pending/success/failed
            $table->integer('response_code')->nullable();
            $table->string('provider_message_id')->nullable(); // agar provider koi id deta ho
            $table->text('error_message')->nullable();
            $table->string('file_url')->nullable();

            // extra meta
            $table->json('meta')->nullable();  // e.g. API payload snippet, template name etc.

            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // FKs
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('SET NULL');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('SET NULL');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_sends');
    }
};
