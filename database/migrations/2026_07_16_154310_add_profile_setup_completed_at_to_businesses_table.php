<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (!Schema::hasColumn('businesses', 'profile_setup_completed_at')) {
                $table->timestamp('profile_setup_completed_at')
                    ->nullable()
                    ->after('profile_setup_completed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (Schema::hasColumn('businesses', 'profile_setup_completed_at')) {
                $table->dropColumn('profile_setup_completed_at');
            }
        });
    }
};