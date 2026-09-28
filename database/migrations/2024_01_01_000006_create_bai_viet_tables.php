<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bài viết / Tin tức
        Schema::create('bai_viet', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ma_nguoi_dung');
            $table->unsignedBigInteger('ma_danh_muc');
            $table->string('tieu_de', 255);
            $table->string('duong_dan', 255)->unique();
            $table->text('tom_tat')->nullable();
            $table->longText('noi_dung');
            $table->string('anh_thu_nho', 255)->nullable();
            $table->enum('trang_thai', ['nhap', 'xuat_ban'])->default('nhap');
            $table->integer('luot_xem')->default(0);
            $table->boolean('noi_bat')->default(false);
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('ma_nguoi_dung')->references('id')->on('nguoi_dung');
            $table->foreign('ma_danh_muc')->references('id')->on('danh_muc');
            $table->index('ma_nguoi_dung');
            $table->index('ma_danh_muc');
        });

        // Thẻ / Tags
        Schema::create('the', function (Blueprint $table) {
            $table->id();
            $table->string('ten', 50);
            $table->string('duong_dan', 50)->unique();
        });

        // Pivot: bài viết - thẻ
        Schema::create('bai_viet_the', function (Blueprint $table) {
            $table->unsignedBigInteger('ma_bai_viet');
            $table->unsignedBigInteger('ma_the');

            $table->primary(['ma_bai_viet', 'ma_the']);
            $table->foreign('ma_bai_viet')->references('id')->on('bai_viet')->cascadeOnDelete();
            $table->foreign('ma_the')->references('id')->on('the')->cascadeOnDelete();
            $table->index('ma_the');
        });

        // Bình luận bài viết
        Schema::create('binh_luan_bai_viet', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ma_bai_viet');
            $table->unsignedBigInteger('ma_nguoi_dung')->nullable();
            $table->unsignedBigInteger('ma_cha')->nullable();
            $table->text('noi_dung');
            $table->string('ten_hien_thi', 100)->nullable();
            $table->enum('trang_thai', ['cho_duyet', 'hien_thi', 'an'])->default('cho_duyet');
            $table->timestamp('ngay_tao')->useCurrent();

            $table->foreign('ma_bai_viet')->references('id')->on('bai_viet')->cascadeOnDelete();
            $table->foreign('ma_nguoi_dung')->references('id')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('ma_cha')->references('id')->on('binh_luan_bai_viet')->nullOnDelete();
            $table->index('ma_bai_viet');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('binh_luan_bai_viet');
        Schema::dropIfExists('bai_viet_the');
        Schema::dropIfExists('the');
        Schema::dropIfExists('bai_viet');
    }
};
