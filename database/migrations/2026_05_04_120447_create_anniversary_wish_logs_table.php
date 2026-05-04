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
        Schema::create('anniversary_wish_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('anniversary_id');
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('phone');
            $table->date('wish_date');
            $table->integer('wish_year');
            $table->string('status')->default('pending');
            $table->text('message')->nullable();
            $table->text('response')->nullable();
            $table->timestamps();

            $table->foreign('anniversary_id')
                ->references('id')
                ->on('anniversaries')
                ->cascadeOnDelete();

            $table->unique(['anniversary_id', 'wish_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anniversary_wish_logs');
    }
};
