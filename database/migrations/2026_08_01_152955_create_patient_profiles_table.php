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
        Schema::create('patient_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            $table->string('patient_code')->nullable();

            $table->date('date_of_birth')->nullable();
            $table->unsignedTinyInteger('age')->nullable();

            $table->enum('gender', [
                'male',
                'female',
                'other',
            ])->nullable();

            $table->string('blood_group', 10)->nullable();

            $table->string('guardian_name')->nullable();
            $table->string('guardian_relation')->nullable();

            $table->string('emergency_contact')->nullable();

            $table->text('allergies')->nullable();
            $table->text('medical_history')->nullable();

            $table->string('insurance_provider')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->string('insurance_tpa')->nullable();

            $table->string('government_health_id')->nullable();
            $table->string('abha_number')->nullable();

            $table->timestamps();

            $table->unique(['business_id', 'client_id']);
            $table->unique(['business_id', 'patient_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_profiles');
    }
};
