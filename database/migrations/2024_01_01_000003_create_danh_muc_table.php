<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc', function (Blueprint $table) {
            $table->id();
            $table->string('ten', 100);
            $table->string('duong_dan', 100)->unique();
            $table->enum('loai', ['du_an', 'bai_viet']);
            $table->enum('trang_thai', ['hoat_dong', 'ngung_hoat_dong'])->default('hoat_dong');
            $table->timestamp('ngay_tao')->useCurrent();
        });

        // Seed default categories
        DB::table('danh_muc')->insert([
            ['ten' => 'Chung cu', 'duong_dan' => 'chung-cu', 'loai' => 'du_an'],
            ['ten' => 'Biet thu', 'duong_dan' => 'biet-thu', 'loai' => 'du_an'],
            ['ten' => 'Thuong mai', 'duong_dan' => 'thuong-mai', 'loai' => 'du_an'],
            ['ten' => 'Tin tuc bat dong san', 'duong_dan' => 'tin-tuc-bat-dong-san', 'loai' => 'bai_viet'],
            ['ten' => 'Cap nhat thi truong', 'duong_dan' => 'cap-nhat-thi-truong', 'loai' => 'bai_viet'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc');
    }
};
