<?php

namespace App\Repositories;

use App\Models\Database;
use PDO;

/** Tất cả truy vấn tổng hợp của Dashboard thành viên. */
class DashboardRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function user(int $userId): ?object
    {
        $this->db->query('SELECT id, ten, email, dien_thoai, anh_dai_dien, so_du, luot_up_tin,
                                 ma_vai_tro, email_verified_at, trang_thai
                          FROM nguoi_dung WHERE id=:uid LIMIT 1');
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);

        return $this->db->single() ?: null;
    }

    public function statistics(int $userId): array
    {
        $this->db->query("SELECT
            COUNT(*) AS total_posts,
            SUM(p.trang_thai='xuat_ban') AS visible_posts,
            SUM(p.goi_vip>0 AND p.ngay_het_han_vip>=NOW()) AS vip_posts,
            SUM((p.ngay_het_han IS NOT NULL AND p.ngay_het_han<NOW()) OR (p.goi_vip>0 AND p.ngay_het_han_vip<NOW())) AS expired_posts,
            SUM(p.trang_thai='cho_duyet') AS pending_posts,
            SUM(p.trang_thai='tu_choi') AS rejected_posts,
            COALESCE(SUM(p.luot_xem),0) AS legacy_views,
            COALESCE(SUM(a.views),0) AS analytics_views,
            COALESCE(SUM(a.calls),0) AS total_calls,
            COALESCE(SUM(a.chats),0) AS total_chats,
            COALESCE(SUM(a.saves),0) AS total_saves,
            COALESCE(SUM(a.shares),0) AS total_shares
          FROM du_an p
          LEFT JOIN (
            SELECT post_id,
              SUM(type='view') AS views, SUM(type='call') AS calls,
              SUM(type='chat') AS chats, SUM(type='save') AS saves,
              SUM(type='share') AS shares
            FROM post_analytics GROUP BY post_id
          ) a ON a.post_id=p.id
          WHERE p.ma_nguoi_dung=:uid");
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);

        return (array) ($this->db->single() ?: []);
    }

    public function wallet(int $userId): array
    {
        $this->db->query("SELECT nd.so_du AS balance, nd.luot_up_tin AS up_turns,
            COALESCE((SELECT SUM(tong_cong) FROM nap_tien WHERE ma_nguoi_dung=nd.id AND trang_thai='da_duyet'),0) AS total_deposited,
            COALESCE((SELECT SUM(so_tien) FROM chi_tieu WHERE ma_nguoi_dung=nd.id),0) AS total_spent
          FROM nguoi_dung nd WHERE nd.id=:uid LIMIT 1");
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);

        return (array) ($this->db->single() ?: []);
    }

    public function recentPosts(int $userId, int $limit = 10): array
    {
        $this->db->query('SELECT id, tieu_de, duong_dan, anh_thu_nho, gia, ngay_tao, trang_thai,
                                 goi_vip, ngay_het_han_vip, ngay_het_han, ly_do_tu_choi
                          FROM du_an WHERE ma_nguoi_dung=:uid
                          ORDER BY ngay_tao DESC LIMIT :lim');
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);
        $this->db->bind(':lim', max(1, min($limit, 20)), PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }

    public function notifications(int $userId, int $limit = 10): array
    {
        $this->db->query('SELECT id, tieu_de, noi_dung, da_doc, ngay_tao
                          FROM thong_bao WHERE ma_nguoi_dung=:uid
                          ORDER BY ngay_tao DESC LIMIT :lim');
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);
        $this->db->bind(':lim', max(1, min($limit, 20)), PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }

    public function transactions(int $userId, int $limit = 10): array
    {
        $this->db->query("SELECT * FROM (
            SELECT id, ngay_tao, 'nap_tien' AS type, phuong_thuc AS description,
                   tong_cong AS amount, trang_thai AS status, 'credit' AS direction
            FROM nap_tien WHERE ma_nguoi_dung=:deposit_uid
            UNION ALL
            SELECT id, ngay_tao, loai AS type, mo_ta AS description,
                   so_tien AS amount, 'thanh_cong' AS status, 'debit' AS direction
            FROM chi_tieu WHERE ma_nguoi_dung=:expense_uid
          ) tx ORDER BY ngay_tao DESC LIMIT :lim");
        $this->db->bind(':deposit_uid', $userId, PDO::PARAM_INT);
        $this->db->bind(':expense_uid', $userId, PDO::PARAM_INT);
        $this->db->bind(':lim', max(1, min($limit, 20)), PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }

    public function chart(int $userId, int $days): array
    {
        $days = in_array($days, [7, 30, 90], true) ? $days : 30;
        $this->db->query("SELECT DATE(a.created_at) AS day,
            SUM(a.type='view') AS views, SUM(a.type='call') AS calls,
            SUM(a.type='chat') AS chats, SUM(a.type='save') AS saves,
            SUM(a.type='share') AS shares
          FROM post_analytics a JOIN du_an p ON p.id=a.post_id
          WHERE p.ma_nguoi_dung=:uid
            AND a.created_at>=DATE_SUB(CURDATE(), INTERVAL ".($days - 1).' DAY)
          GROUP BY DATE(a.created_at) ORDER BY day');
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }

    public function hidePost(int $userId, int $postId): bool
    {
        $this->db->query("UPDATE du_an SET trang_thai='an' WHERE id=:id AND ma_nguoi_dung=:uid");
        $this->db->bind(':id', $postId, PDO::PARAM_INT);
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);

        return $this->db->execute() && $this->db->rowCount() > 0;
    }

    public function deletePost(int $userId, int $postId): bool
    {
        $this->db->query('DELETE FROM du_an WHERE id=:id AND ma_nguoi_dung=:uid');
        $this->db->bind(':id', $postId, PDO::PARAM_INT);
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);

        return $this->db->execute() && $this->db->rowCount() > 0;
    }
}
