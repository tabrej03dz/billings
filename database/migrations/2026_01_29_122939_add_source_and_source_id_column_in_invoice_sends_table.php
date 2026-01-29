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
        Schema::table('invoice_sends', function (Blueprint $table) {
            $table->string('source')->nullable();
            $table->string('source_id')->nullable();
            $table->unique(['user_id','source','source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_sends', function (Blueprint $table) {
            //
        });
    }
};
