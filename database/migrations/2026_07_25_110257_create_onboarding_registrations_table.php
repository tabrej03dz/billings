<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_registrations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name')->nullable();
            $table->string('phone', 10)->unique();
            $table->timestamp('phone_verified_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Partial registration data
            |--------------------------------------------------------------------------
            |
            | User business ya billing step me jitna data bharega,
            | wahi JSON format me save ho jayega.
            |
            */

            $table->json('business_data')->nullable();
            $table->json('billing_data')->nullable();

            $table->unsignedTinyInteger('last_completed_step')->default(1);

            $table->string('registration_status')
                ->default('registered');

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_registrations');
    }
};