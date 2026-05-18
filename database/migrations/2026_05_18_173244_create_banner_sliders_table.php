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
        Schema::create('banner_sliders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();

            $table->string('image');
            $table->string('mobile_image')->nullable();

            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();

            $table->string('alt_text')->nullable();
            $table->text('description')->nullable();

            $table->integer('sort_order')->default(0);

            $table->boolean('is_active')->default(true);



            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_sliders');
    }
};
