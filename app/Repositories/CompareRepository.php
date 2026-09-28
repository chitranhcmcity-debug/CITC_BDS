<?php

namespace App\Repositories;

use App\Models\Database;
use PDO;

/**
 * CompareRepository – Quản lý các truy vấn CSDL liên quan đến So sánh Bất động sản.
 * Tuân thủ SOLID, tách biệt Repository Pattern.
 */
class CompareRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Kiểm tra tin đăng đã có trong danh sách so sánh chưa.
     */
    public function isCompared(int|string $userOrSession, int $postId, bool $isLoggedIn): bool
    {
        if ($isLoggedIn) {
            $this->db->query('SELECT 1 FROM so_sanh WHERE ma_nguoi_dung = :uid AND ma_du_an = :pid LIMIT 1');
            $this->db->bind(':uid', (int) $userOrSession);
        } else {
            $this->db->query('SELECT 1 FROM so_sanh WHERE session_id = :sid AND ma_du_an = :pid LIMIT 1');
            $this->db->bind(':sid', (string) $userOrSession);
        }
        $this->db->bind(':pid', $postId);

        return (bool) $this->db->single();
    }

    /**
     * Thêm tin đăng vào danh sách so sánh.
     */
    public function add(int|string $userOrSession, int $postId, bool $isLoggedIn): bool
    {
        if ($isLoggedIn) {
            $this->db->query("INSERT INTO so_sanh (ma_nguoi_dung, session_id, ma_du_an) VALUES (:uid, '', :pid)");
            $this->db->bind(':uid', (int) $userOrSession);
        } else {
            $this->db->query('INSERT INTO so_sanh (ma_nguoi_dung, session_id, ma_du_an) VALUES (NULL, :sid, :pid)');
            $this->db->bind(':sid', (string) $userOrSession);
        }
        $this->db->bind(':pid', $postId);

        return $this->db->execute();
    }

    /**
     * Xóa tin đăng khỏi danh sách so sánh.
     */
    public function remove(int|string $userOrSession, int $postId, bool $isLoggedIn): bool
    {
        if ($isLoggedIn) {
            $this->db->query('DELETE FROM so_sanh WHERE ma_nguoi_dung = :uid AND ma_du_an = :pid');
            $this->db->bind(':uid', (int) $userOrSession);
        } else {
            $this->db->query('DELETE FROM so_sanh WHERE session_id = :sid AND ma_du_an = :pid');
            $this->db->bind(':sid', (string) $userOrSession);
        }
        $this->db->bind(':pid', $postId);

        return $this->db->execute();
    }

    /**
     * Xóa toàn bộ danh sách so sánh.
     */
    public function removeAll(int|string $userOrSession, bool $isLoggedIn): bool
    {
        if ($isLoggedIn) {
            $this->db->query('DELETE FROM so_sanh WHERE ma_nguoi_dung = :uid');
            $this->db->bind(':uid', (int) $userOrSession);
        } else {
            $this->db->query('DELETE FROM so_sanh WHERE session_id = :sid');
            $this->db->bind(':sid', (string) $userOrSession);
        }

        return $this->db->execute();
    }

    /**
     * Lấy danh sách tin đăng trong bảng so sánh (tối đa 4 tin).
     */
    public function getComparisonList(int|string $userOrSession, bool $isLoggedIn): array
    {
        if ($isLoggedIn) {
            $sql = '
                SELECT p.*, c.ten AS ten_danh_muc, u.ten AS ten_nguoi_dung
                FROM so_sanh s
                JOIN du_an p ON s.ma_du_an = p.id
                JOIN danh_muc c ON p.ma_danh_muc = c.id
                LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
                WHERE s.ma_nguoi_dung = :uid AND p.deleted_at IS NULL
                ORDER BY s.ngay_tao DESC
            ';
            $this->db->query($sql);
            $this->db->bind(':uid', (int) $userOrSession);
        } else {
            $sql = '
                SELECT p.*, c.ten AS ten_danh_muc, u.ten AS ten_nguoi_dung
                FROM so_sanh s
                JOIN du_an p ON s.ma_du_an = p.id
                JOIN danh_muc c ON p.ma_danh_muc = c.id
                LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
                WHERE s.session_id = :sid AND p.deleted_at IS NULL
                ORDER BY s.ngay_tao DESC
            ';
            $this->db->query($sql);
            $this->db->bind(':sid', (string) $userOrSession);
        }

        return $this->db->resultSet() ?: [];
    }

    /**
     * Đếm số lượng tin trong danh sách so sánh.
     */
    public function countComparisonList(int|string $userOrSession, bool $isLoggedIn): int
    {
        if ($isLoggedIn) {
            $this->db->query('SELECT COUNT(*) AS total FROM so_sanh WHERE ma_nguoi_dung = :uid');
            $this->db->bind(':uid', (int) $userOrSession);
        } else {
            $this->db->query('SELECT COUNT(*) AS total FROM so_sanh WHERE session_id = :sid');
            $this->db->bind(':sid', (string) $userOrSession);
        }
        $res = $this->db->single();

        return $res ? (int) $res->total : 0;
    }

    /**
     * Đồng bộ hóa danh sách so sánh của khách vãng lai khi họ đăng nhập.
     */
    public function syncSessionToUser(string $sessionId, int $userId): void
    {
        // 1. Lấy danh sách từ session hiện có
        $sessionItems = $this->getComparisonList($sessionId, false);
        if (empty($sessionItems)) {
            return;
        }

        foreach ($sessionItems as $item) {
            // Kiểm tra xem user đã có tin này trong so sánh chưa
            if (! $this->isCompared($userId, (int) $item->id, true)) {
                // Kiểm tra xem danh sách so sánh của user đã đầy (4 tin) chưa
                if ($this->countComparisonList($userId, true) < 4) {
                    $this->add($userId, (int) $item->id, true);
                }
            }
        }

        // 2. Xóa các tin trong session sau khi đã đồng bộ
        $this->removeAll($sessionId, false);
    }

    /**
     * Admin CRM: Lấy Top tin đăng được so sánh nhiều nhất hệ thống.
     */
    public function getMostComparedProperties(int $limit = 10): array
    {
        $this->db->query('
            SELECT p.id, p.tieu_de, p.duong_dan, p.gia, p.dien_tich, p.anh_thu_nho, p.goi_vip,
                   COUNT(s.ma_du_an) AS compare_count, u.ten AS nguoi_dang
            FROM so_sanh s
            JOIN du_an p ON s.ma_du_an = p.id
            LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
            WHERE p.deleted_at IS NULL
            GROUP BY p.id
            ORDER BY compare_count DESC, p.id DESC
            LIMIT :limit
        ');
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }
}
