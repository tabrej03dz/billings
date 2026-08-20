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
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('number_of_office')
                ->default(1)
                ->after('duration_days');

            $table->unsignedInteger('number_of_user')
                ->default(1)
                ->after('number_of_office');
        });

        Schema::table('user_plans', function (Blueprint $table) {
            $table->unsignedInteger('number_of_office')
                ->default(1)
                ->after('plan_id');

            $table->unsignedInteger('number_of_user')
                ->default(1)
                ->after('number_of_office');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'number_of_office',
                'number_of_user',
            ]);
        });

        Schema::table('user_plans', function (Blueprint $table) {
            $table->dropColumn([
                'number_of_office',
                'number_of_user',
            ]);
        });
    }
};