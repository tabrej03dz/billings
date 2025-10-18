<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('gstin')->nullable();
            $table->string('pan')->nullable();
            $table->string('mobile')->nullable();
            $table->string('state')->nullable();

            $table->unique(['business_id','mobile']);
            $table->unique(['business_id','gstin']);
            $table->unique(['business_id','pan']);
            $table->index(['business_id','name']);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('clients');
    }
};
