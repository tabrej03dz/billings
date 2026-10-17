<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
         * पुरानी nullable purity values को empty string में बदलें।
         *
         * MySQL unique index में NULL values के साथ multiple rows
         * allow हो सकती हैं, इसलिए purity normalize करना जरूरी है।
         */
        DB::table('metal_rates')
            ->whereNull('purity')
            ->update([
                'purity' => '',
            ]);

        Schema::table('metal_rates', function (Blueprint $table) {
            /*
             * पुराना unique index:
             * rate_date + metal_type + purity
             */
            $table->dropUnique(
                'metal_rates_rate_date_metal_type_purity_unique'
            );
        });

        Schema::table('metal_rates', function (Blueprint $table) {
            /*
             * नया business-wise unique index
             */
            $table->unique(
                [
                    'business_id',
                    'rate_date',
                    'metal_type',
                    'purity',
                ],
                'metal_rates_business_date_metal_purity_unique'
            );

            $table->index(
                [
                    'business_id',
                    'rate_date',
                ],
                'metal_rates_business_date_index'
            );

            $table->index(
                [
                    'business_id',
                    'metal_type',
                    'is_active',
                ],
                'metal_rates_business_metal_active_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('metal_rates', function (Blueprint $table) {
            $table->dropUnique(
                'metal_rates_business_date_metal_purity_unique'
            );

            $table->dropIndex(
                'metal_rates_business_date_index'
            );

            $table->dropIndex(
                'metal_rates_business_metal_active_index'
            );

            $table->unique(
                [
                    'rate_date',
                    'metal_type',
                    'purity',
                ],
                'metal_rates_rate_date_metal_type_purity_unique'
            );
        });
    }
};