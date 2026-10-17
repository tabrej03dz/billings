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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
             $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('business_id')
                ->nullable()
                ->constrained('businesses')
                ->nullOnDelete();

            $table->string('session_key', 100)
                ->nullable()
                ->index();

            $table->string('route_name')
                ->nullable()
                ->index();

            $table->string('page_title')
                ->nullable();

            $table->text('url');

            $table->string('path')
                ->index();

            $table->string('method', 10)
                ->default('GET');

            $table->unsignedInteger('duration_seconds')
                ->default(0);

            $table->unsignedInteger('heartbeat_count')
                ->default(0);

            $table->timestamp('started_at')
                ->nullable()
                ->index();

            $table->timestamp('last_seen_at')
                ->nullable()
                ->index();

            $table->timestamp('ended_at')
                ->nullable();

            $table->string('ip_address', 45)
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            $table->string('device_type', 30)
                ->nullable()
                ->index();

            $table->string('browser', 60)
                ->nullable();

            $table->string('platform', 60)
                ->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'started_at',
            ]);

            $table->index([
                'route_name',
                'started_at',
            ]);

            $table->index([
                'business_id',
                'started_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
