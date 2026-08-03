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
        Schema::table('bill_templates', function (Blueprint $table) {
            $table->foreignId('business_type_id')->nullable()->after('id')->constrained('business_types')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_templates', function (Blueprint $table) {
            $table->dropForeign('bill_templates_business_type_id_foreign');
            $table->dropColumn('business_type_id');
        });
    }
};
