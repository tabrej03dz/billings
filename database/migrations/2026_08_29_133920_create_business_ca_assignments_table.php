<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_ca_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'user_id']);
            $table->index(['user_id', 'is_active']);
            $table->index(['business_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_ca_assignments');
    }
};
