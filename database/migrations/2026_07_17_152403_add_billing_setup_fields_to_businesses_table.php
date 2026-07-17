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
        /*
        |--------------------------------------------------------------------------
        | Businesses billing setup columns
        |--------------------------------------------------------------------------
        |
        | hasColumn() isliye use kiya gaya hai taaki jo columns database me
        | pehle se maujood hain unke kaaran duplicate column error na aaye.
        |
        */

        if (!Schema::hasColumn('businesses', 'gst_enabled')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->boolean('gst_enabled')
                    ->default(false)
                    ->after('gstin');
            });
        }

        if (!Schema::hasColumn('businesses', 'invoice_base_prefix')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->string('invoice_base_prefix', 50)
                    ->nullable()
                    ->default('INV')
                    ->after('pdf_template_id');
            });
        }

        if (!Schema::hasColumn('businesses', 'rounding_mode')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->enum('rounding_mode', [
                    'none',
                    'nearest',
                    'up',
                    'down',
                ])
                    ->default('none')
                    ->after('signature');
            });
        }

        if (!Schema::hasColumn('businesses', 'rounding_step')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->decimal('rounding_step', 8, 2)
                    ->default(1.00)
                    ->after('rounding_mode');
            });
        }

        if (!Schema::hasColumn('businesses', 'terms')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->text('terms')
                    ->nullable()
                    ->after('rounding_step');
            });
        }

        if (!Schema::hasColumn('businesses', 'terms_accepted')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->boolean('terms_accepted')
                    ->default(false)
                    ->after('terms');
            });
        }

        if (!Schema::hasColumn('businesses', 'terms_accepted_at')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->timestamp('terms_accepted_at')
                    ->nullable()
                    ->after('terms_accepted');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Remove only columns added by this migration
        |--------------------------------------------------------------------------
        */

        $columns = [
            'terms_accepted_at',
            'terms_accepted',
            'terms',
            'rounding_step',
            'rounding_mode',
            'invoice_base_prefix',
            'gst_enabled',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('businesses', $column)) {
                Schema::table('businesses', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};