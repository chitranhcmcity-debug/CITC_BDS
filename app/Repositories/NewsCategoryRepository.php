<?php

namespace App\Repositories;

use App\Models\Database;

/**
 * NewsCategoryRepository – Truy cập danh mục bài viết.
 */
class NewsCategoryRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Lấy tất cả danh mục bài viết kèm số lượng bài đã xuất bản.
     */
    public function findAllWithCount(): array
    {
        $this->db->query(
            "SELECT d.id, d.ten, d.duong_dan, d.trang_thai,
                    COUNT(b.id) AS so_bai
             FROM danh_muc d
             LEFT JOIN bai_viet b ON b.ma_danh_muc = d.id AND b.trang_thai = 'xuat_ban'
             WHERE d.loai = 'bai_viet' AND d.trang_thai = 'hoat_dong'
             GROUP BY d.id
             ORDER BY so_bai DESC, d.ten ASC"
        );

        return $this->db->resultSet();
    }

    /**
     * Lấy danh mục theo slug.
     */
    public function findBySlug(string $slug): mixed
    {
        $this->db->query(
            "SELECT d.*, COUNT(b.id) AS so_bai
             FROM danh_muc d
             LEFT JOIN bai_viet b ON b.ma_danh_muc = d.id AND b.trang_thai = 'xuat_ban'
             WHERE d.duong_dan = :slug AND d.loai = 'bai_viet'
             GROUP BY d.id"
        );
        $this->db->bind(':slug', $slug);

        return $this->db->single();
    }

    /**
     * Lấy tất cả danh mục (không kèm count – dùng cho Admin dropdown).
     */
    public function findAll(): array
    {
        $this->db->query(
            "SELECT * FROM danh_muc WHERE loai = 'bai_viet' AND trang_thai = 'hoat_dong' ORDER BY ten ASC"
        );

        return $this->db->resultSet();
    }
}
