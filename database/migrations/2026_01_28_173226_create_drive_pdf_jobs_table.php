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
        Schema::create('drive_pdf_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('drive_file_id')->unique();
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigINteger('size')->default(0);
            $table->timestamp('drive_modified_at')->nullable();

            $table->string('to_number')->nullable();
            $table->text('caption')->nullable();

            $table->string('status')->default('pending'); // pending/sending/sent/failed
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drive_pdf_jobs');
    }
};
