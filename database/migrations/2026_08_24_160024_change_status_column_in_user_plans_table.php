<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Step 1: TINYINT को VARCHAR बनाएं।
         * Existing 0 और 1 अब string बन जाएंगे।
         */
        DB::statement("
            ALTER TABLE user_plans
            MODIFY status VARCHAR(20)
            NOT NULL DEFAULT 'active'
        ");

        /*
         * Step 2: पुरानी values convert करें।
         */
        DB::statement("
            UPDATE user_plans
            SET status = CASE
                WHEN status = '1' THEN 'active'
                WHEN status = '0' THEN 'inactive'
                WHEN status = 'active' THEN 'active'
                WHEN status = 'inactive' THEN 'inactive'
                WHEN status = 'trial' THEN 'trial'
                ELSE 'inactive'
            END
        ");

        /*
         * Step 3: अब सुरक्षित तरीके से ENUM बनाएं।
         */
        DB::statement("
            ALTER TABLE user_plans
            MODIFY status ENUM('active', 'inactive', 'trial')
            NOT NULL DEFAULT 'active'
        ");
    }

    public function down(): void
    {
        /*
         * ENUM को पहले VARCHAR में बदलें।
         */
        DB::statement("
            ALTER TABLE user_plans
            MODIFY status VARCHAR(20)
            NOT NULL DEFAULT '1'
        ");

        /*
         * Text values को वापस 0 और 1 में बदलें।
         * Trial को rollback में inactive यानी 0 माना जाएगा।
         */
        DB::statement("
            UPDATE user_plans
            SET status = CASE
                WHEN status = 'active' THEN '1'
                WHEN status = 'inactive' THEN '0'
                WHEN status = 'trial' THEN '0'
                ELSE '0'
            END
        ");

        /*
         * वापस TINYINT बनाएं।
         */
        DB::statement("
            ALTER TABLE user_plans
            MODIFY status TINYINT(1)
            NOT NULL DEFAULT 1
        ");
    }
};