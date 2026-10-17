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
        Schema::create('hospital_beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->foreignId('room_id')
                ->nullable()
                ->constrained('hospital_rooms')
                ->nullOnDelete();

            $table->string('bed_number');

            $table->decimal('daily_charge', 12, 2)
                ->default(0);

            $table->enum('status', [
                'available',
                'occupied',
                'reserved',
                'maintenance',
            ])->default('available');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique([
                'business_id',
                'room_id',
                'bed_number',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_beds');
    }
};
