<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('du_an', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ma_danh_muc');
            $table->unsignedBigInteger('ma_nguoi_dung')->nullable();
            $table->string('tieu_de', 255);
            $table->string('duong_dan', 255)->unique();
            $table->text('mo_ta')->nullable();
            $table->longText('noi_dung')->nullable();
            $table->string('gia', 100)->nullable();
            $table->string('dien_tich', 100)->nullable();
            $table->string('vi_tri', 255)->nullable();
            $table->text('ban_do')->nullable();
            // Longitude needs three integer digits (for example 106.x in Vietnam).
            $table->decimal('kinh_do', 11, 8)->nullable();
            $table->decimal('vi_do', 10, 8)->nullable();
            $table->string('tinh_thanh', 100)->nullable();
            $table->string('quan_huyen', 100)->nullable();
            $table->string('phuong_xa', 100)->nullable();
            $table->enum('phap_ly', ['so_do', 'so_hong', 'giay_tay', 'cho_so'])->nullable();
            $table->string('mat_tien', 50)->nullable();
            $table->integer('so_phong_ngu')->nullable();
            $table->integer('so_phong_wc')->nullable();
            $table->string('huong_nha', 50)->nullable();
            $table->string('loai_bat_dong_san', 100)->nullable();
            $table->boolean('chinh_chu')->default(false);
            $table->string('link_video', 255)->nullable();
            $table->string('link_tour_360', 255)->nullable();
            $table->integer('luot_xem')->default(0);
            $table->integer('luot_click_sdt')->default(0);
            $table->string('tai_lieu_pdf', 255)->nullable();
            $table->string('anh_thu_nho', 255)->nullable();
            $table->text('tien_ich')->nullable();
            $table->enum('trang_thai', ['nhap', 'cho_duyet', 'xuat_ban', 'da_ban', 'tu_choi', 'an'])->default('nhap');
            $table->string('ly_do_tu_choi', 500)->nullable();
            $table->boolean('noi_bat')->default(false);
            $table->tinyInteger('goi_vip')->default(0);
            $table->dateTime('ngay_het_han_vip')->nullable();
            $table->dateTime('ngay_het_han')->nullable();
            $table->boolean('tu_dong_gia_han_vip')->default(false);
            $table->timestamp('ngay_lam_moi')->nullable();
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('ma_danh_muc')->references('id')->on('danh_muc');
            $table->foreign('ma_nguoi_dung')->references('id')->on('nguoi_dung')->nullOnDelete();
            $table->index('ma_danh_muc');
            $table->index('ma_nguoi_dung');
            $table->index(['ma_nguoi_dung', 'trang_thai', 'ngay_tao'], 'idx_dashboard_user_status_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('du_an');
    }
};
