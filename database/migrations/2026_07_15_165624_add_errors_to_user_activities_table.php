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
         Schema::table('user_activities', function (Blueprint $table) {
            $table->json('errors')
                ->nullable()
                ->after('heartbeat_count');

            $table->unsignedInteger('error_count')
                ->default(0)
                ->after('errors');

            $table->timestamp('last_error_at')
                ->nullable()
                ->after('error_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_activities', function (Blueprint $table) {
            //
        });
    }
};
