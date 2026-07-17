<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->unsignedTinyInteger('profile_completion')
                ->default(0)
                ->after('type');

            $table->boolean('profile_setup_completed')
                ->default(false)
                ->after('profile_completion');

            $table->timestamp('profile_suggestion_dismissed_at')
                ->nullable()
                ->after('profile_setup_completed');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'profile_completion',
                'profile_setup_completed',
                'profile_suggestion_dismissed_at',
            ]);
        });
    }
};