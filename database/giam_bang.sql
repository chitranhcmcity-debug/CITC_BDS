-- Giảm số bảng: gộp thong_bao vào thong_bao_nguoi_dung và xóa các bảng không còn dùng.
-- Chạy MỘT lần trên database đang dùng (ví dụ Railway), cùng lúc deploy code mới.
-- Tương đương migration 2026_09_28_000001_drop_unused_tables_and_merge_thong_bao.php

INSERT INTO thong_bao_nguoi_dung (user_id, type, title, content, is_read, created_at)
SELECT ma_nguoi_dung, 'he_thong', tieu_de, COALESCE(noi_dung, ''), da_doc, ngay_tao
FROM thong_bao;

DROP TABLE IF EXISTS thong_bao, video_du_an, lich_hen, trinh_chieu, vai_tro_nguoi_dung;
