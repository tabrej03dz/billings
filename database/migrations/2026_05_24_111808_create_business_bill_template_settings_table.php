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
        Schema::create('business_bill_template_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('bill_template_id')->constrained('bill_templates')->cascadeOnDelete();

            $table->string('primary_color')->default('#d60000');
            $table->string('secondary_color')->nullable();
            $table->string('text_color')->default('#111111');

            $table->string('font_family')->default('DejaVu Sans');

            $table->boolean('show_logo')->default(true);
            $table->boolean('show_tagline')->default(true);
            $table->boolean('show_signature')->default(true);
            $table->boolean('show_terms')->default(true);


            $table->string('muted_color')->nullable();
            $table->string('border_color')->nullable();
            $table->string('light_bg_color')->nullable();
            $table->string('soft_bg_color')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'bill_template_id'], 'business_template_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_bill_template_settings');
    }
};
