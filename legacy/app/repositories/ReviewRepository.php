<?php
/**
 * ReviewRepository – Truy vấn đánh giá người đăng tin BĐS.
 * Bảng: danh_gia_nguoi_dung
 */
class ReviewRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Lấy danh sách đánh giá của một người đăng (có phân trang).
     *
     * @param  int $authorId
     * @param  int $limit
     * @param  int $offset
     * @return array
     */
    public function getByAuthor(int $authorId, int $limit = 10, int $offset = 0): array
    {
        $this->db->query(
            "SELECT r.*, u.ten AS ten_reviewer, u.anh_dai_dien AS avatar_reviewer,
                    u.loai_tai_khoan AS loai_reviewer
             FROM danh_gia_nguoi_dung r
             JOIN nguoi_dung u ON r.reviewer_id = u.id
             WHERE r.author_id = :aid AND r.trang_thai = 'hien_thi'
             ORDER BY r.ngay_tao DESC
             LIMIT :lim OFFSET :off"
        );
        $this->db->bind(':aid', $authorId, PDO::PARAM_INT);
        $this->db->bind(':lim', $limit,    PDO::PARAM_INT);
        $this->db->bind(':off', $offset,   PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Đánh giá trung bình (1-5 sao).
     *
     * @param  int $authorId
     * @return float
     */
    public function getAvgRating(int $authorId): float
    {
        $this->db->query(
            "SELECT ROUND(AVG(so_sao), 1) AS avg_sao
             FROM danh_gia_nguoi_dung
             WHERE author_id = :aid AND trang_thai = 'hien_thi'"
        );
        $this->db->bind(':aid', $authorId, PDO::PARAM_INT);
        $row = $this->db->single();
        return (float)($row->avg_sao ?? 0);
    }

    /**
     * Đếm số đánh giá.
     *
     * @param  int $authorId
     * @return int
     */
    public function countByAuthor(int $authorId): int
    {
        $this->db->query(
            "SELECT COUNT(*) AS n FROM danh_gia_nguoi_dung
             WHERE author_id = :aid AND trang_thai = 'hien_thi'"
        );
        $this->db->bind(':aid', $authorId, PDO::PARAM_INT);
        $row = $this->db->single();
        return (int)($row->n ?? 0);
    }

    /**
     * Tạo đánh giá mới.
     *
     * @param  array $data ['reviewer_id', 'author_id', 'so_sao', 'nhan_xet']
     * @return bool
     */
    public function create(array $data): bool
    {
        $this->db->query(
            "INSERT INTO danh_gia_nguoi_dung
                (reviewer_id, author_id, so_sao, nhan_xet)
             VALUES
                (:reviewer_id, :author_id, :so_sao, :nhan_xet)"
        );
        $this->db->bind(':reviewer_id', (int)$data['reviewer_id'], PDO::PARAM_INT);
        $this->db->bind(':author_id',   (int)$data['author_id'],   PDO::PARAM_INT);
        $this->db->bind(':so_sao',      (int)$data['so_sao'],      PDO::PARAM_INT);
        $this->db->bind(':nhan_xet',    $data['nhan_xet'] ?? null);
        return $this->db->execute();
    }

    /**
     * Kiểm tra đã đánh giá chưa (1 người chỉ đánh giá 1 lần).
     *
     * @param  int $reviewerId
     * @param  int $authorId
     * @return bool
     */
    public function hasReviewed(int $reviewerId, int $authorId): bool
    {
        $this->db->query(
            "SELECT 1 FROM danh_gia_nguoi_dung
             WHERE reviewer_id = :rid AND author_id = :aid LIMIT 1"
        );
        $this->db->bind(':rid', $reviewerId, PDO::PARAM_INT);
        $this->db->bind(':aid', $authorId,   PDO::PARAM_INT);
        return (bool)$this->db->single();
    }

    /**
     * Phân phối sao (1 đến 5) của một người đăng.
     *
     * @param  int $authorId
     * @return array ['sao_1' => x, 'sao_2' => y, ...]
     */
    public function getRatingDistribution(int $authorId): array
    {
        $this->db->query(
            "SELECT so_sao, COUNT(*) AS so_luot
             FROM danh_gia_nguoi_dung
             WHERE author_id = :aid AND trang_thai = 'hien_thi'
             GROUP BY so_sao"
        );
        $this->db->bind(':aid', $authorId, PDO::PARAM_INT);
        $rows = $this->db->resultSet();

        $dist = ['1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0];
        foreach ($rows as $r) {
            $dist[(string)$r->so_sao] = (int)$r->so_luot;
        }
        return $dist;
    }
}
