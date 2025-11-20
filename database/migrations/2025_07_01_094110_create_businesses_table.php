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
       Schema::create('businesses', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('logo')->nullable();
    $table->string('email')->unique();
    $table->string('mobile')->nullable()->unique();
    $table->string('gstin')->nullable();
    $table->boolean('gst_enabled')->default(true);

    $table->string('address')->nullable();
    $table->string('signature')->nullable();

    $table->enum('rounding_mode', ['none', 'nearest', 'up', 'down'])
        ->default('none');

    $table->decimal('rounding_step', 8, 2)->default(1.00);

    $table->text('terms')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
