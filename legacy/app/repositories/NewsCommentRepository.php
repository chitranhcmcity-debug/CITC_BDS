<?php
/**
 * NewsCommentRepository – Bình luận bài viết (bảng news_comments).
 *
 * Chiến lược bình luận:
 *  - 2 level: root comment + reply (cha_id != null)
 *  - Hiển thị ngay (trang_thai='hien'), Admin ẩn sau
 *  - Soft-delete: đặt noi_dung = '[Đã xóa]' thay vì DELETE cứng (giữ reply thread)
 */
class NewsCommentRepository
{
    private Database $db;
    private int $perPage = 20;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Lấy danh sách comment của bài viết (chỉ root + reply visible).
     * Load tất cả trong 1 query, phân nhóm ở PHP.
     */
    public function findByNews(int $newsId, int $page = 1): array
    {
        $offset = ($page - 1) * $this->perPage;
        $this->db->query(
            "SELECT c.id, c.cha_id, c.noi_dung, c.trang_thai, c.ngay_tao, c.ngay_cap_nhat,
                    n.id AS nguoi_dung_id, n.ten AS ten_nguoi_dung, n.anh_dai_dien, n.ma_vai_tro
             FROM news_comments c
             JOIN nguoi_dung n ON n.id = c.nguoi_dung_id
             WHERE c.bai_viet_id = :newsId AND c.trang_thai = 'hien'
             ORDER BY c.cha_id ASC, c.ngay_tao ASC
             LIMIT :limit OFFSET :offset"
        );
        $this->db->bind(':newsId', $newsId, PDO::PARAM_INT);
        $this->db->bind(':limit',  $this->perPage, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset,         PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function countByNews(int $newsId): int
    {
        $this->db->query(
            "SELECT COUNT(*) FROM news_comments WHERE bai_viet_id = :newsId AND trang_thai = 'hien'"
        );
        $this->db->bind(':newsId', $newsId, PDO::PARAM_INT);
        return (int)$this->db->single()->{'COUNT(*)'};
    }

    /**
     * Thêm comment mới.
     *
     * @param array $data [bai_viet_id, nguoi_dung_id, noi_dung, cha_id]
     * @return int ID comment vừa tạo
     */
    public function create(array $data): int
    {
        $this->db->query(
            "INSERT INTO news_comments (bai_viet_id, nguoi_dung_id, noi_dung, cha_id)
             VALUES (:bai_viet_id, :nguoi_dung_id, :noi_dung, :cha_id)"
        );
        $this->db->bind(':bai_viet_id',   (int)$data['bai_viet_id'],  PDO::PARAM_INT);
        $this->db->bind(':nguoi_dung_id', (int)$data['nguoi_dung_id'], PDO::PARAM_INT);
        $this->db->bind(':noi_dung',      $data['noi_dung']);
        $this->db->bind(':cha_id',        $data['cha_id'] ? (int)$data['cha_id'] : null, PDO::PARAM_INT);
        $this->db->execute();

        $this->db->query("SELECT LAST_INSERT_ID() AS id");
        return (int)$this->db->single()->id;
    }

    /**
     * Sửa nội dung comment (chỉ chủ sở hữu).
     */
    public function update(int $id, string $content, int $userId): bool
    {
        $this->db->query(
            "UPDATE news_comments SET noi_dung = :noi_dung, ngay_cap_nhat = NOW()
             WHERE id = :id AND nguoi_dung_id = :userId AND trang_thai = 'hien'"
        );
        $this->db->bind(':noi_dung', $content);
        $this->db->bind(':id',     $id,     PDO::PARAM_INT);
        $this->db->bind(':userId', $userId, PDO::PARAM_INT);
        return $this->db->execute();
    }

    /**
     * Soft-delete comment: đổi nội dung thành '[Đã xóa]' để giữ reply thread.
     * Chủ sở hữu hoặc Admin.
     */
    public function softDelete(int $id, int $userId, bool $isAdmin = false): bool
    {
        $whereExtra = $isAdmin ? '' : ' AND nguoi_dung_id = :userId';
        $sql = "UPDATE news_comments SET noi_dung = '[Đã xóa]', ngay_cap_nhat = NOW()
                WHERE id = :id {$whereExtra}";
        $this->db->query($sql);
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        if (!$isAdmin) {
            $this->db->bind(':userId', $userId, PDO::PARAM_INT);
        }
        return $this->db->execute();
    }

    /**
     * Admin ẩn comment (đặt trang_thai='an').
     */
    public function hide(int $id): bool
    {
        $this->db->query("UPDATE news_comments SET trang_thai = 'an' WHERE id = :id");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->execute();
    }

    /** Admin hien lai comment da bi an. */
    public function unhide(int $id): bool
    {
        $this->db->query("UPDATE news_comments SET trang_thai = 'hien' WHERE id = :id");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->execute();
    }

    /**
     * Lấy comment theo ID (để verify quyền).
     */
    public function findById(int $id): mixed
    {
        $this->db->query("SELECT * FROM news_comments WHERE id = :id");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->single();
    }

    /**
     * Lấy danh sách comment cần duyệt / quản lý (Admin).
     */
    public function findForAdmin(string $status = '', int $limit = 50, int $offset = 0): array
    {
        $where = $status ? "WHERE c.trang_thai = :status" : '';
        $this->db->query(
            "SELECT c.*, b.tieu_de AS tieu_de_bai, b.duong_dan AS slug_bai,
                    n.ten AS ten_nguoi_dung
             FROM news_comments c
             JOIN bai_viet b ON b.id = c.bai_viet_id
             JOIN nguoi_dung n ON n.id = c.nguoi_dung_id
             {$where}
             ORDER BY c.ngay_tao DESC
             LIMIT :limit OFFSET :offset"
        );
        if ($status) $this->db->bind(':status', $status);
        $this->db->bind(':limit',  $limit,  PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Xóa cứng comment (Admin only).
     */
    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM news_comments WHERE id = :id");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->execute();
    }
}
