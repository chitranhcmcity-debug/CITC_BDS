<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Giảm số bảng: gộp thong_bao vào thong_bao_nguoi_dung và xóa các bảng không còn dùng.
 * Nội dung tương đương database/giam_bang.sql (dùng cho database không chạy migration).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('thong_bao') && Schema::hasTable('thong_bao_nguoi_dung')) {
            DB::statement("
                INSERT INTO thong_bao_nguoi_dung (user_id, type, title, content, is_read, created_at)
                SELECT ma_nguoi_dung, 'he_thong', tieu_de, COALESCE(noi_dung, ''), da_doc, ngay_tao
                FROM thong_bao
            ");
        }

        Schema::dropIfExists('thong_bao');
        Schema::dropIfExists('video_du_an');
        Schema::dropIfExists('lich_hen');
        Schema::dropIfExists('trinh_chieu');
        Schema::dropIfExists('vai_tro_nguoi_dung');
    }

    public function down(): void
    {
        // Không khôi phục: dữ liệu thong_bao đã gộp, các bảng còn lại không được code sử dụng.
    }
};
