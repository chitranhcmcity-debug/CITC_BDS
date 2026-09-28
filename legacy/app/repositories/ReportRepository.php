<?php
/**
 * ReportRepository – Truy vấn bảng bao_cao_vi_pham.
 * Tuân thủ Repository Pattern: chỉ CSDL, không logic nghiệp vụ.
 */
class ReportRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function begin(): bool { return $this->db->beginTransaction(); }
    public function commit(): bool { return $this->db->commit(); }
    public function rollBack(): bool { return $this->db->rollBack(); }

    /** Lay mot bao cao dang cho xu ly, kem thong tin tin dang. */
    public function pendingById(int $id): ?object
    {
        $this->db->query("SELECT r.*, p.tieu_de AS post_title, p.trang_thai AS post_status,
                                 p.deleted_at AS post_deleted_at
                          FROM bao_cao_vi_pham r
                          JOIN du_an p ON p.id = r.ma_du_an
                          WHERE r.id = :id AND r.trang_thai = 'cho_xu_ly'
                          LIMIT 1");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $row = $this->db->single();
        return $row ?: null;
    }

    /**
     * Tạo báo cáo vi phạm mới.
     *
     * @param  array $data Dữ liệu báo cáo
     * @return bool
     */
    public function create(array $data): bool
    {
        $this->db->query(
            "INSERT INTO bao_cao_vi_pham
                (ma_du_an, ma_nguoi_dung, ho_ten, email, ly_do, mo_ta, ip_address, minh_chung)
             VALUES
                (:ma_du_an, :ma_nguoi_dung, :ho_ten, :email, :ly_do, :mo_ta, :ip, :minh_chung)"
        );
        $this->db->bind(':ma_du_an',      (int)($data['ma_du_an']));
        $this->db->bind(':ma_nguoi_dung', isset($data['ma_nguoi_dung']) ? (int)$data['ma_nguoi_dung'] : null);
        $this->db->bind(':ho_ten',        $data['ho_ten']  ?? null);
        $this->db->bind(':email',         $data['email']   ?? null);
        $this->db->bind(':ly_do',         $data['ly_do']   ?? 'khac');
        $this->db->bind(':mo_ta',         $data['mo_ta']   ?? null);
        $this->db->bind(':ip',            $data['ip_address'] ?? null);
        $this->db->bind(':minh_chung',    !empty($data['minh_chung'])
            ? json_encode(array_values($data['minh_chung']), JSON_UNESCAPED_SLASHES)
            : null);
        return $this->db->execute();
    }

    /**
     * Kiểm tra người dùng/IP đã báo cáo tin này trong 24h chưa.
     *
     * @param  int         $postId
     * @param  int|null    $userId
     * @param  string|null $ip
     * @return bool
     */
    public function hasReported(int $postId, ?int $userId, ?string $ip): bool
    {
        if ($userId) {
            $this->db->query(
                "SELECT 1 FROM bao_cao_vi_pham
                 WHERE ma_du_an = :pid AND ma_nguoi_dung = :uid
                   AND ngay_tao >= DATE_SUB(NOW(), INTERVAL 24 HOUR) LIMIT 1"
            );
            $this->db->bind(':pid', $postId, PDO::PARAM_INT);
            $this->db->bind(':uid', $userId, PDO::PARAM_INT);
        } else {
            $this->db->query(
                "SELECT 1 FROM bao_cao_vi_pham
                 WHERE ma_du_an = :pid AND ip_address = :ip
                   AND ngay_tao >= DATE_SUB(NOW(), INTERVAL 24 HOUR) LIMIT 1"
            );
            $this->db->bind(':pid', $postId, PDO::PARAM_INT);
            $this->db->bind(':ip', $ip ?? '');
        }
        return (bool)$this->db->single();
    }

    /**
     * Đếm số báo cáo của một tin đăng.
     *
     * @param  int $postId
     * @return int
     */
    public function countByPost(int $postId): int
    {
        $this->db->query("SELECT COUNT(*) AS n FROM bao_cao_vi_pham WHERE ma_du_an = :pid");
        $this->db->bind(':pid', $postId, PDO::PARAM_INT);
        $row = $this->db->single();
        return (int)($row->n ?? 0);
    }

    /**
     * Lấy danh sách báo cáo vi phạm phục vụ Admin.
     */
    public function adminList(): array
    {
        $this->db->query("SELECT r.*, p.tieu_de AS post_title, p.duong_dan AS post_slug, u.ten AS reporter_name
                          FROM bao_cao_vi_pham r
                          JOIN du_an p ON r.ma_du_an = p.id
                          LEFT JOIN nguoi_dung u ON r.ma_nguoi_dung = u.id
                          ORDER BY r.id DESC");
        return $this->db->resultSet();
    }

    /**
     * Xử lý báo cáo vi phạm.
     */
    public function adminResolve(int $id, string $status, string $note): bool
    {
        $this->db->query("UPDATE bao_cao_vi_pham 
                          SET trang_thai = :status, ghi_chu_admin = :note, ngay_cap_nhat = NOW() 
                          WHERE id = :id AND trang_thai = 'cho_xu_ly'");
        $this->db->bind(':status', $status);
        $this->db->bind(':note',   $note);
        $this->db->bind(':id',     $id, PDO::PARAM_INT);
        return $this->db->execute() && $this->db->rowCount() === 1;
    }
}
