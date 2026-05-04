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
        Schema::table('api_keys', function (Blueprint $table) {
            $table->string('anniversary_api')->nullable();
            $table->text('anniversary_wish_media_manager_video_url')->nullable();
            $table->date('anniversary_wish_video_url_updated_on')->nullable();
            $table->text('anniversary_wish_video_absolute_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            //
        });
    }
};
