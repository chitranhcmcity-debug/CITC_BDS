<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nguoi_dung', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ma_vai_tro');
            $table->string('ten', 100);
            $table->string('email', 100)->unique();
            $table->string('mat_khau', 255);
            $table->string('dien_thoai', 20)->nullable();
            $table->string('anh_dai_dien', 255)->nullable();
            $table->bigInteger('so_du')->default(0);
            $table->integer('luot_up_tin')->default(0);
            $table->enum('trang_thai', ['hoat_dong', 'ngung_hoat_dong'])->default('hoat_dong');
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->integer('auth_version')->default(1);
            $table->string('google_id', 100)->nullable();
            $table->string('facebook_id', 100)->nullable();
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('ma_vai_tro')->references('id')->on('vai_tro');
            $table->index('ma_vai_tro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nguoi_dung');
    }
};
