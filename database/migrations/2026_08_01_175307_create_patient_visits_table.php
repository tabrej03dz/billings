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
        Schema::create('patient_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            $table->foreignId('doctor_id')
                ->nullable()
                ->constrained('doctors')
                ->nullOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('hospital_departments')
                ->nullOnDelete();

            $table->string('visit_number');

            $table->enum('visit_type', [
                'opd',
                'ipd',
                'emergency',
                'day_care',
                'diagnostic',
                'pharmacy',
            ])->default('opd');

            $table->dateTime('visit_at');

            $table->text('chief_complaint')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('remarks')->nullable();

            $table->foreignId('ward_id')
                ->nullable()
                ->constrained('hospital_wards')
                ->nullOnDelete();

            $table->foreignId('room_id')
                ->nullable()
                ->constrained('hospital_rooms')
                ->nullOnDelete();

            $table->foreignId('bed_id')
                ->nullable()
                ->constrained('hospital_beds')
                ->nullOnDelete();

            $table->dateTime('admitted_at')->nullable();
            $table->dateTime('discharged_at')->nullable();

            $table->enum('status', [
                'registered',
                'in_consultation',
                'admitted',
                'discharged',
                'cancelled',
            ])->default('registered');

            $table->timestamps();

            $table->unique([
                'business_id',
                'visit_number',
            ]);

            $table->index([
                'business_id',
                'client_id',
                'visit_at',
            ]);

            $table->index([
                'business_id',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_visits');
    }
};
