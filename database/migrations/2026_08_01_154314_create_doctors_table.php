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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->string('doctor_code')->nullable();
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();

            $table->string('qualification')->nullable();
            $table->string('specialization')->nullable();
            $table->string('registration_number')->nullable();

            $table->decimal('consultation_fee', 12, 2)
                ->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index([
                'business_id',
                'name',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
