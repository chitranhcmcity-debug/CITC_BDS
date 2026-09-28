<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE du_an MODIFY kinh_do DECIMAL(11,8) NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE du_an SET kinh_do = NULL WHERE kinh_do < -99.99999999 OR kinh_do > 99.99999999');
        DB::statement('ALTER TABLE du_an MODIFY kinh_do DECIMAL(10,8) NULL');
    }
};
