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
        Schema::create('hospital_wards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('code')->nullable();
            $table->string('ward_type')->nullable();

            $table->decimal('daily_charge', 12, 2)
                ->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique([
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
        Schema::dropIfExists('hospital_wards');
    }
};
