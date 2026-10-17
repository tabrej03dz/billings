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
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'patient_visit_id')) {
                $table->foreignId('patient_visit_id')
                    ->nullable()
                    ->after('client_id')
                    ->constrained('patient_visits')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('invoices', 'doctor_id')) {
                $table->foreignId('doctor_id')
                    ->nullable()
                    ->after('patient_visit_id')
                    ->constrained('doctors')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('invoices', 'billing_category')) {
                $table->string('billing_category', 50)
                    ->nullable()
                    ->after('doctor_id');
            }

            if (!Schema::hasColumn('invoices', 'hospital_bill_type')) {
                $table->string('hospital_bill_type', 50)
                    ->nullable()
                    ->after('billing_category');
            }

            if (!Schema::hasColumn('invoices', 'hospital_details_json')) {
                $table->json('hospital_details_json')
                    ->nullable()
                    ->after('hospital_bill_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            //
        });
    }
};
