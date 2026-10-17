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
        Schema::table('bill_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('bill_requests', 'idempotency_key')) {
                $table->string('idempotency_key')->nullable()->after('source_request_id');
            }

            $table->unique('idempotency_key', 'bill_requests_idempotency_key_unique');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->unique(
                ['bil_request_id', 'invoice_type'],
                'invoices_bill_request_type_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('bill_requests', function (Blueprint $table) {
            $table->dropUnique('bill_requests_idempotency_key_unique');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('invoices_bill_request_type_unique');
        });
    }
};
