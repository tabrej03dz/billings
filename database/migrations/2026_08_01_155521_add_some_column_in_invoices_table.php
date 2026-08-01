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
            $table->foreignId('patient_visit_id')
                ->nullable()
                ->after('client_id')
                ->constrained('patient_visits')
                ->nullOnDelete();

            $table->foreignId('doctor_id')
                ->nullable()
                ->after('patient_visit_id')
                ->constrained('doctors')
                ->nullOnDelete();

            $table->string('billing_category')
                ->nullable()
                ->after('doctor_id');

            $table->string('hospital_bill_type')
                ->nullable()
                ->after('billing_category');

            $table->json('hospital_details_json')
                ->nullable()
                ->after('hospital_bill_type');
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
