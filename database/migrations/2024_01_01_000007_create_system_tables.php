<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Khách hàng / Leads
        Schema::create('khach_hang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ma_du_an')->nullable();
            $table->unsignedBigInteger('nguoi_phu_trach')->nullable();
            $table->string('ten', 100);
            $table->string('email', 100)->nullable();
            $table->string('dien_thoai', 20);
            $table->text('tin_nhan')->nullable();
            $table->enum('trang_thai', ['moi', 'da_lien_he', 'tiem_nang', 'that_bai', 'thanh_cong'])->default('moi');
            $table->boolean('da_giai_quyet')->default(false);
            $table->text('bao_cao_giai_quyet')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('ma_du_an')->references('id')->on('du_an')->nullOnDelete();
            $table->foreign('nguoi_phu_trach')->references('id')->on('nguoi_dung')->nullOnDelete();
            $table->index('ma_du_an');
            $table->index('nguoi_phu_trach');
        });

        // Trình chiếu / Banner / Slider
        Schema::create('trinh_chieu', function (Blueprint $table) {
            $table->id();
            $table->string('tieu_de', 255)->nullable();
            $table->string('tieu_de_phu', 255)->nullable();
            $table->string('hinh_anh', 255);
            $table->string('lien_ket', 255)->nullable();
            $table->integer('thu_tu')->default(0);
            $table->enum('trang_thai', ['hoat_dong', 'ngung_hoat_dong'])->default('hoat_dong');
        });

        // Cài đặt hệ thống
        Schema::create('cai_dat', function (Blueprint $table) {
            $table->id();
            $table->string('khoa_cai_dat', 50)->unique();
            $table->text('gia_tri_cai_dat')->nullable();
        });

        // Seed settings
        DB::table('cai_dat')->insert([
            ['khoa_cai_dat' => 'site_name', 'gia_tri_cai_dat' => 'Bat dong san CITC'],
            ['khoa_cai_dat' => 'site_description', 'gia_tri_cai_dat' => 'Nen tang bat dong san hang dau'],
            ['khoa_cai_dat' => 'logo', 'gia_tri_cai_dat' => 'logo.png'],
            ['khoa_cai_dat' => 'contact_email', 'gia_tri_cai_dat' => 'info@citc-bds.com'],
            ['khoa_cai_dat' => 'contact_phone', 'gia_tri_cai_dat' => '+84 123 456 789'],
            ['khoa_cai_dat' => 'contact_address', 'gia_tri_cai_dat' => '123 Main St, City, Country'],
            ['khoa_cai_dat' => 'facebook_url', 'gia_tri_cai_dat' => '#'],
            ['khoa_cai_dat' => 'twitter_url', 'gia_tri_cai_dat' => '#'],
            ['khoa_cai_dat' => 'instagram_url', 'gia_tri_cai_dat' => '#'],
            ['khoa_cai_dat' => 'zalo_number', 'gia_tri_cai_dat' => '0123456789'],
        ]);

        // Thông báo
        Schema::create('thong_bao', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ma_nguoi_dung');
            $table->string('tieu_de', 255);
            $table->text('noi_dung');
            $table->boolean('da_doc')->default(false);
            $table->timestamp('ngay_tao')->useCurrent();

            $table->foreign('ma_nguoi_dung')->references('id')->on('nguoi_dung')->cascadeOnDelete();
            $table->index(['ma_nguoi_dung', 'da_doc', 'ngay_tao'], 'idx_thong_bao_user_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thong_bao');
        Schema::dropIfExists('cai_dat');
        Schema::dropIfExists('trinh_chieu');
        Schema::dropIfExists('khach_hang');
    }
};
