<?php

namespace App\Repositories;

use App\Models\Database;
use PDO;

/**
 * FavoriteRepository – Quản lý các truy vấn CSDL liên quan đến Tin đã lưu (Yêu thích).
 * Tuân thủ SOLID, tách biệt Repository Pattern.
 */
class FavoriteRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Kiểm tra tin đăng đã được người dùng lưu hay chưa.
     */
    public function isFavorite(int $userId, int $postId): bool
    {
        $this->db->query('SELECT 1 FROM yeu_thich WHERE ma_nguoi_dung = :uid AND ma_du_an = :pid LIMIT 1');
        $this->db->bind(':uid', $userId);
        $this->db->bind(':pid', $postId);

        return (bool) $this->db->single();
    }

    /**
     * Lưu tin đăng vào danh sách yêu thích.
     */
    public function add(int $userId, int $postId): bool
    {
        $this->db->query('INSERT INTO yeu_thich (ma_nguoi_dung, ma_du_an) VALUES (:uid, :pid)');
        $this->db->bind(':uid', $userId);
        $this->db->bind(':pid', $postId);

        return $this->db->execute();
    }

    /**
     * Bỏ lưu tin đăng khỏi danh sách yêu thích.
     */
    public function remove(int $userId, int $postId): bool
    {
        $this->db->query('DELETE FROM yeu_thich WHERE ma_nguoi_dung = :uid AND ma_du_an = :pid');
        $this->db->bind(':uid', $userId);
        $this->db->bind(':pid', $postId);

        return $this->db->execute();
    }

    /**
     * Xóa toàn bộ danh sách tin đã lưu của người dùng.
     */
    public function removeAll(int $userId): bool
    {
        $this->db->query('DELETE FROM yeu_thich WHERE ma_nguoi_dung = :uid');
        $this->db->bind(':uid', $userId);

        return $this->db->execute();
    }

    /**
     * Lấy danh sách tin đăng đã lưu kèm bộ lọc nâng cao.
     * Tránh N+1 query bằng cách JOIN trực tiếp các bảng liên quan.
     */
    public function getFavorites(int $userId, array $filters = []): array
    {
        $where = ['y.ma_nguoi_dung = :uid', 'p.deleted_at IS NULL'];
        $params = [':uid' => $userId];

        // Lọc theo loại giao dịch
        if (! empty($filters['transaction_type'])) {
            $where[] = 'p.loai_giao_dich = :transaction_type';
            $params[':transaction_type'] = $filters['transaction_type'];
        }

        // Lọc theo gói VIP
        if (isset($filters['vip_level']) && $filters['vip_level'] !== '') {
            $where[] = 'p.goi_vip = :vip_level';
            $params[':vip_level'] = (int) $filters['vip_level'];
        }

        // Lọc theo khoảng giá
        if (isset($filters['price_min']) && $filters['price_min'] !== '') {
            $where[] = 'p.gia >= :price_min';
            $params[':price_min'] = (float) $filters['price_min'];
        }
        if (isset($filters['price_max']) && $filters['price_max'] !== '') {
            $where[] = 'p.gia <= :price_max';
            $params[':price_max'] = (float) $filters['price_max'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT p.*, c.ten AS ten_danh_muc, u.ten AS ten_nguoi_dung, y.ngay_tao AS ngay_luu
            FROM yeu_thich y
            JOIN du_an p ON y.ma_du_an = p.id
            JOIN danh_muc c ON p.ma_danh_muc = c.id
            LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
            WHERE $whereClause
            ORDER BY y.ngay_tao DESC
        ";

        $this->db->query($sql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }

        return $this->db->resultSet() ?: [];
    }

    /**
     * Đếm tổng số tin đã lưu của người dùng.
     */
    public function countFavorites(int $userId): int
    {
        $this->db->query('SELECT COUNT(*) AS total FROM yeu_thich WHERE ma_nguoi_dung = :uid');
        $this->db->bind(':uid', $userId);
        $res = $this->db->single();

        return $res ? (int) $res->total : 0;
    }

    /**
     * Admin CRM: Lấy Top tin đăng được lưu nhiều nhất hệ thống.
     */
    public function getMostSavedProperties(int $limit = 10): array
    {
        $this->db->query('
            SELECT p.id, p.tieu_de, p.duong_dan, p.gia, p.dien_tich, p.anh_thu_nho, p.goi_vip,
                   COUNT(y.ma_du_an) AS save_count, u.ten AS nguoi_dang
            FROM yeu_thich y
            JOIN du_an p ON y.ma_du_an = p.id
            LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
            WHERE p.deleted_at IS NULL
            GROUP BY p.id
            ORDER BY save_count DESC, p.id DESC
            LIMIT :limit
        ');
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }
}
