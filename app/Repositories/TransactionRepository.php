<?php

namespace App\Repositories;

use App\Models\Database;
use PDO;

/**
 * TransactionRepository – Quản lý lịch sử giao dịch nạp tiền và chi tiêu cho Admin.
 * Tuân thủ SOLID, Repository Pattern.
 */
class TransactionRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Lấy toàn bộ lịch sử giao dịch (bao gồm cả nạp tiền và chi tiêu) có phân trang.
     */
    public function getHistory(?int $userId = null, int $limit = 50, int $offset = 0): array
    {
        if ($userId !== null && $userId > 0) {
            $sql = "
                SELECT id, ma_nguoi_dung, so_tien AS amount, 'nap_tien' AS type, phuong_thuc AS method_or_desc, trang_thai AS status, ngay_tao
                FROM nap_tien 
                WHERE ma_nguoi_dung = :uid1
                UNION ALL
                SELECT id, ma_nguoi_dung, so_tien AS amount, loai AS type, mo_ta AS method_or_desc, 'da_duyet' AS status, ngay_tao
                FROM chi_tieu 
                WHERE ma_nguoi_dung = :uid2
                ORDER BY ngay_tao DESC
                LIMIT :limit OFFSET :offset
            ";
            $this->db->query($sql);
            $this->db->bind(':uid1', $userId, PDO::PARAM_INT);
            $this->db->bind(':uid2', $userId, PDO::PARAM_INT);
        } else {
            $sql = "
                SELECT id, ma_nguoi_dung, so_tien AS amount, 'nap_tien' AS type, phuong_thuc AS method_or_desc, trang_thai AS status, ngay_tao
                FROM nap_tien 
                UNION ALL
                SELECT id, ma_nguoi_dung, so_tien AS amount, loai AS type, mo_ta AS method_or_desc, 'da_duyet' AS status, ngay_tao
                FROM chi_tieu 
                ORDER BY ngay_tao DESC
                LIMIT :limit OFFSET :offset
            ";
            $this->db->query($sql);
        }

        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }

    /**
     * Đếm tổng số giao dịch (bao gồm cả nạp tiền và chi tiêu).
     */
    public function countHistory(?int $userId = null): int
    {
        if ($userId !== null && $userId > 0) {
            $this->db->query('
                SELECT (
                    (SELECT COUNT(*) FROM nap_tien WHERE ma_nguoi_dung = :uid1) + 
                    (SELECT COUNT(*) FROM chi_tieu WHERE ma_nguoi_dung = :uid2)
                ) as total
            ');
            $this->db->bind(':uid1', $userId);
            $this->db->bind(':uid2', $userId);
        } else {
            $this->db->query('
                SELECT (
                    (SELECT COUNT(*) FROM nap_tien) + 
                    (SELECT COUNT(*) FROM chi_tieu)
                ) as total
            ');
        }
        $res = $this->db->single();

        return $res ? (int) $res->total : 0;
    }
}
