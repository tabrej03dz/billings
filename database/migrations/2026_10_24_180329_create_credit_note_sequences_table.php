
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_note_sequences', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->string('financial_year', 10);

            $table->unsignedBigInteger('last_number')
                ->default(0);

            $table->timestamps();

            $table->unique(
                ['business_id', 'financial_year'],
                'credit_note_sequence_business_fy_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_note_sequences');
    }
};
