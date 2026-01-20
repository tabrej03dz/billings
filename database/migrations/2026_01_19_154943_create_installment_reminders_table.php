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
        Schema::create('installment_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('contact_number');
            $table->date('reminder_date');
            $table->time('reminder_time');
            $table->string('snme_number')->nullable();
            $table->decimal('installment_amount', 12, 2);
            $table->date('installment_date');
            $table->string('status')->default('uploaded');
            $table->text('response')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_reminders');
    }
};
