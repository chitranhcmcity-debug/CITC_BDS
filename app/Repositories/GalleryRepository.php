<?php

namespace App\Repositories;

use App\Models\Database;
use PDO;

/**
 * GalleryRepository – Truy vấn bảng `hinh_anh_du_an` quản lý hình ảnh tin đăng.
 * Tuân thủ Repository Pattern, chỉ chứa logic truy cập dữ liệu.
 */
class GalleryRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Lấy tất cả hình ảnh của một tin đăng, ưu tiên ảnh đại diện.
     */
    public function byPost(int $id): array
    {
        $this->db->query('
            SELECT * FROM hinh_anh_du_an 
            WHERE ma_du_an = :id 
            ORDER BY la_anh_dai_dien DESC, thu_tu, id
        ');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }

    /**
     * Thêm hình ảnh mới cho tin đăng.
     */
    public function add(int $postId, string $file, int $order, bool $cover = false): bool
    {
        $this->db->query('
            INSERT INTO hinh_anh_du_an (ma_du_an, duong_dan_anh, thu_tu, la_anh_dai_dien) 
            VALUES (:post, :file, :ord, :cover)
        ');
        $this->db->bind(':post', $postId, PDO::PARAM_INT);
        $this->db->bind(':file', $file);
        $this->db->bind(':ord', $order, PDO::PARAM_INT);
        $this->db->bind(':cover', $cover, PDO::PARAM_BOOL);

        return $this->db->execute();
    }

    /**
     * Xóa hình ảnh thuộc sở hữu của tin đăng, trả về đường dẫn file đã xóa.
     */
    public function deleteOwnedImage(int $imageId, int $postId): ?string
    {
        $this->db->query('
            SELECT duong_dan_anh FROM hinh_anh_du_an 
            WHERE id = :id AND ma_du_an = :post
        ');
        $this->db->bind(':id', $imageId, PDO::PARAM_INT);
        $this->db->bind(':post', $postId, PDO::PARAM_INT);
        $row = $this->db->single();

        if (! $row) {
            return null;
        }

        $this->db->query('DELETE FROM hinh_anh_du_an WHERE id = :id');
        $this->db->bind(':id', $imageId, PDO::PARAM_INT);

        return $this->db->execute() ? $row->duong_dan_anh : null;
    }
}
