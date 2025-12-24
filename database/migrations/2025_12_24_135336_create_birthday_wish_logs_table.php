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
        Schema::create('birthday_wish_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('birthday_record_id')->index();
            $table->unsignedBigInteger('business_id')->nullable()->index();
            $table->string('phone', 30)->index();
            $table->date('wish_date')->index(); // actual date when wish attempted (today)
            $table->integer('wish_year')->index();
            $table->string('status', 20)->default('pending'); // success/failed
            $table->text('message')->nullable();
            $table->text('response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('birthday_wish_logs');
    }
};
