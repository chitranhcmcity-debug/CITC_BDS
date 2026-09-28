<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hình ảnh dự án
        Schema::create('hinh_anh_du_an', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ma_du_an');
            $table->string('duong_dan_anh', 255);
            $table->integer('thu_tu')->default(0);

            $table->foreign('ma_du_an')->references('id')->on('du_an')->cascadeOnDelete();
            $table->index('ma_du_an');
        });

        // Video dự án
        Schema::create('video_du_an', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ma_du_an');
            $table->string('duong_dan_video', 255);

            $table->foreign('ma_du_an')->references('id')->on('du_an')->cascadeOnDelete();
            $table->index('ma_du_an');
        });

        // Lịch hẹn xem nhà
        Schema::create('lich_hen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ma_du_an');
            $table->unsignedBigInteger('ma_nguoi_dung')->nullable();
            $table->string('ho_ten', 100);
            $table->string('so_dien_thoai', 20);
            $table->string('email', 100)->nullable();
            $table->date('ngay_xem');
            $table->time('gio_xem');
            $table->text('ghi_chu')->nullable();
            $table->enum('trang_thai', ['cho_duyet', 'da_xac_nhan', 'da_huy', 'hoan_thanh'])->default('cho_duyet');
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('ma_du_an')->references('id')->on('du_an')->cascadeOnDelete();
            $table->index('ma_du_an');
        });

        // Yêu thích
        Schema::create('yeu_thich', function (Blueprint $table) {
            $table->unsignedBigInteger('ma_nguoi_dung');
            $table->unsignedBigInteger('ma_du_an');
            $table->timestamp('ngay_tao')->useCurrent();

            $table->primary(['ma_nguoi_dung', 'ma_du_an']);
            $table->foreign('ma_nguoi_dung')->references('id')->on('nguoi_dung')->cascadeOnDelete();
            $table->foreign('ma_du_an')->references('id')->on('du_an')->cascadeOnDelete();
        });

        // Đánh giá
        Schema::create('danh_gia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ma_nguoi_dung');
            $table->unsignedBigInteger('ma_du_an');
            $table->integer('so_sao');
            $table->text('binh_luan')->nullable();
            $table->enum('trang_thai', ['cho_duyet', 'hien_thi', 'an'])->default('cho_duyet');
            $table->timestamp('ngay_tao')->useCurrent();

            $table->foreign('ma_nguoi_dung')->references('id')->on('nguoi_dung')->cascadeOnDelete();
            $table->foreign('ma_du_an')->references('id')->on('du_an')->cascadeOnDelete();
            $table->index('ma_du_an');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_gia');
        Schema::dropIfExists('yeu_thich');
        Schema::dropIfExists('lich_hen');
        Schema::dropIfExists('video_du_an');
        Schema::dropIfExists('hinh_anh_du_an');
    }
};
