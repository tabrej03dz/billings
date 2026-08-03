<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('doctor_department')) {
            Schema::create('doctor_department', function (Blueprint $table) {
                $table->id();

                $table->foreignId('doctor_id')
                    ->constrained('doctors')
                    ->cascadeOnDelete();

                $table->foreignId('department_id')
                    ->constrained('hospital_departments')
                    ->cascadeOnDelete();

                $table->timestamps();

                $table->unique([
                    'doctor_id',
                    'department_id',
                ], 'doctor_department_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_department');
    }
};