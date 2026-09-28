<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vai_tro', function (Blueprint $table) {
            $table->id();
            $table->string('ten', 50);
            $table->string('mo_ta', 255)->nullable();
        });

        // Seed default roles
        DB::table('vai_tro')->insert([
            ['id' => 1, 'ten' => 'Admin', 'mo_ta' => 'Toan quyen truy cap he thong'],
            ['id' => 2, 'ten' => 'Client', 'mo_ta' => 'Nguoi dung dang tin va su dung dich vu'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('vai_tro');
    }
};
