<?php
/**
 * NewsRepository – Truy cập dữ liệu bảng bai_viet.
 *
 * Tuân thủ: không xử lý Business Logic, chỉ DB access.
 * Pattern: Repository (inject PDO qua Database singleton).
 */
class NewsRepository
{
    private Database $db;
    private int $perPage = 12;

    public function __construct()
    {
        $this->db = new Database();
    }

    /* =====================================================
       TRUY VẤN DANH SÁCH
       ===================================================== */

    /**
     * Lấy danh sách bài viết đã xuất bản, hỗ trợ filter + phân trang.
     *
     * @param array $filters ['category_id','tag_id','keyword','sort']
     * @param int   $page    Trang hiện tại (1-based)
     * @param int   $perPage Số bài mỗi trang
     * @return array
     */
    public function findPublished(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        [$where, $binds] = $this->buildWhereClause($filters);
        $offset = ($page - 1) * $perPage;

        $sort = match($filters['sort'] ?? 'new') {
            'popular' => 'b.luot_xem DESC',
            'liked'   => 'b.luot_thich DESC',
            default   => 'b.ngay_tao DESC',
        };

        $sql = "SELECT b.id, b.tieu_de, b.duong_dan, b.tom_tat, b.anh_thu_nho,
                       b.trang_thai, b.noi_bat, b.luot_xem, b.luot_thich,
                       b.luot_binh_luan, b.luot_chia_se, b.thoi_gian_doc,
                       b.ngay_tao, b.ngay_cap_nhat,
                       d.ten AS ten_danh_muc, d.duong_dan AS slug_danh_muc,
                       n.id AS tac_gia_id, n.ten AS ten_tac_gia, n.anh_dai_dien AS avatar_tac_gia
                FROM bai_viet b
                LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
                LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
                {$where}
                ORDER BY {$sort}
                LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        foreach ($binds as $k => $v) {
            $this->db->bind($k, $v);
        }
        $this->db->bind(':limit',  $perPage, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset,  PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Đếm tổng số bài viết đã xuất bản (dùng cho pagination).
     */
    public function countPublished(array $filters = []): int
    {
        [$where, $binds] = $this->buildWhereClause($filters);
        $sql = "SELECT COUNT(*) FROM bai_viet b
                LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
                LEFT JOIN bai_viet_the bt ON bt.ma_bai_viet = b.id
                {$where}";
        $this->db->query($sql);
        foreach ($binds as $k => $v) {
            $this->db->bind($k, $v);
        }
        return (int)$this->db->single()->{'COUNT(*)'};
    }

    /**
     * Lấy chi tiết bài viết theo slug (JOIN đầy đủ).
     */
    public function findBySlug(string $slug): mixed
    {
        $this->db->query(
            "SELECT b.*, d.ten AS ten_danh_muc, d.duong_dan AS slug_danh_muc,
                    n.id AS tac_gia_id, n.ten AS ten_tac_gia, n.anh_dai_dien AS avatar_tac_gia,
                    n.mo_ta_ca_nhan AS bio_tac_gia, n.loai_tai_khoan
             FROM bai_viet b
             LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
             LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
             WHERE b.duong_dan = :slug
               AND b.trang_thai IN ('xuat_ban','lich_hen')"
        );
        $this->db->bind(':slug', $slug);
        return $this->db->single();
    }

    /**
     * Lấy bài viết theo danh mục (slug danh mục).
     */
    public function findByCategory(string $slug, int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;
        $this->db->query(
            "SELECT b.id, b.tieu_de, b.duong_dan, b.tom_tat, b.anh_thu_nho,
                    b.luot_xem, b.luot_binh_luan, b.thoi_gian_doc, b.ngay_tao,
                    d.ten AS ten_danh_muc, d.duong_dan AS slug_danh_muc,
                    n.ten AS ten_tac_gia
             FROM bai_viet b
             LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
             LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
             WHERE d.duong_dan = :slug AND b.trang_thai = 'xuat_ban'
             ORDER BY b.ngay_tao DESC
             LIMIT :limit OFFSET :offset"
        );
        $this->db->bind(':slug', $slug);
        $this->db->bind(':limit',  $perPage, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset,  PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function countByCategory(string $slug): int
    {
        $this->db->query(
            "SELECT COUNT(*) FROM bai_viet b
             JOIN danh_muc d ON b.ma_danh_muc = d.id
             WHERE d.duong_dan = :slug AND b.trang_thai = 'xuat_ban'"
        );
        $this->db->bind(':slug', $slug);
        return (int)$this->db->single()->{'COUNT(*)'};
    }

    /**
     * Lấy bài viết theo tag (slug tag).
     */
    public function findByTag(string $slug, int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;
        $this->db->query(
            "SELECT b.id, b.tieu_de, b.duong_dan, b.tom_tat, b.anh_thu_nho,
                    b.luot_xem, b.luot_binh_luan, b.thoi_gian_doc, b.ngay_tao,
                    d.ten AS ten_danh_muc, d.duong_dan AS slug_danh_muc,
                    n.ten AS ten_tac_gia
             FROM bai_viet b
             JOIN bai_viet_the bt ON bt.ma_bai_viet = b.id
             JOIN the t ON t.id = bt.ma_the
             LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
             LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
             WHERE t.duong_dan = :slug AND b.trang_thai = 'xuat_ban'
             ORDER BY b.ngay_tao DESC
             LIMIT :limit OFFSET :offset"
        );
        $this->db->bind(':slug', $slug);
        $this->db->bind(':limit',  $perPage, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset,  PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function countByTag(string $slug): int
    {
        $this->db->query(
            "SELECT COUNT(*) FROM bai_viet b
             JOIN bai_viet_the bt ON bt.ma_bai_viet = b.id
             JOIN the t ON t.id = bt.ma_the
             WHERE t.duong_dan = :slug AND b.trang_thai = 'xuat_ban'"
        );
        $this->db->bind(':slug', $slug);
        return (int)$this->db->single()->{'COUNT(*)'};
    }

    /**
     * Tìm kiếm fulltext bài viết.
     */
    public function search(string $keyword, int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;
        $kw = '%' . $keyword . '%';
        $this->db->query(
            "SELECT b.id, b.tieu_de, b.duong_dan, b.tom_tat, b.anh_thu_nho,
                    b.luot_xem, b.ngay_tao, b.thoi_gian_doc,
                    d.ten AS ten_danh_muc, d.duong_dan AS slug_danh_muc,
                    n.ten AS ten_tac_gia
             FROM bai_viet b
             LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
             LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
             LEFT JOIN bai_viet_the bt ON bt.ma_bai_viet = b.id
             LEFT JOIN the t ON t.id = bt.ma_the
             WHERE b.trang_thai = 'xuat_ban'
               AND (b.tieu_de LIKE :kw1 OR b.tom_tat LIKE :kw2 OR b.noi_dung LIKE :kw3 OR t.ten LIKE :kw4 OR d.ten LIKE :kw5)
             GROUP BY b.id
             ORDER BY b.noi_bat DESC, b.ngay_tao DESC
             LIMIT :limit OFFSET :offset"
        );
        $this->db->bind(':kw1', $kw); $this->db->bind(':kw2', $kw);
        $this->db->bind(':kw3', $kw); $this->db->bind(':kw4', $kw);
        $this->db->bind(':kw5', $kw);
        $this->db->bind(':limit',  $perPage, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset,  PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function countSearch(string $keyword): int
    {
        $kw = '%' . $keyword . '%';
        $this->db->query(
            "SELECT COUNT(DISTINCT b.id) as cnt FROM bai_viet b
             LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
             LEFT JOIN bai_viet_the bt ON bt.ma_bai_viet = b.id
             LEFT JOIN the t ON t.id = bt.ma_the
             WHERE b.trang_thai = 'xuat_ban'
               AND (b.tieu_de LIKE :kw1 OR b.tom_tat LIKE :kw2 OR t.ten LIKE :kw3 OR d.ten LIKE :kw4)"
        );
        $this->db->bind(':kw1', $kw); $this->db->bind(':kw2', $kw);
        $this->db->bind(':kw3', $kw); $this->db->bind(':kw4', $kw);
        $row = $this->db->single();
        return (int)($row->cnt ?? 0);
    }

    /* =====================================================
       SIDEBAR QUERIES
       ===================================================== */

    public function findLatest(int $limit = 5): array
    {
        $this->db->query(
            "SELECT id, tieu_de, duong_dan, anh_thu_nho, ngay_tao, luot_xem, thoi_gian_doc
             FROM bai_viet WHERE trang_thai = 'xuat_ban'
             ORDER BY ngay_tao DESC LIMIT :limit"
        );
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function findPopular(int $limit = 5): array
    {
        $this->db->query(
            "SELECT id, tieu_de, duong_dan, anh_thu_nho, ngay_tao, luot_xem, thoi_gian_doc
             FROM bai_viet WHERE trang_thai = 'xuat_ban'
             ORDER BY luot_xem DESC LIMIT :limit"
        );
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function findFeatured(int $limit = 3): array
    {
        $this->db->query(
            "SELECT id, tieu_de, duong_dan, anh_thu_nho, ngay_tao, luot_xem, thoi_gian_doc
             FROM bai_viet WHERE trang_thai = 'xuat_ban' AND noi_bat = 1
             ORDER BY ngay_tao DESC LIMIT :limit"
        );
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Bài liên quan: cùng danh mục hoặc cùng tag, loại trừ bài hiện tại.
     */
    public function findRelated(int $newsId, int $categoryId, array $tagIds = [], int $limit = 6): array
    {
        $tagIn = !empty($tagIds)
            ? 'OR bt.ma_the IN (' . implode(',', array_map('intval', $tagIds)) . ')'
            : '';

        $this->db->query(
            "SELECT DISTINCT b.id, b.tieu_de, b.duong_dan, b.anh_thu_nho,
                    b.tom_tat, b.ngay_tao, b.luot_xem, b.thoi_gian_doc,
                    b.noi_bat,
                    d.ten AS ten_danh_muc
             FROM bai_viet b
             LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
             LEFT JOIN bai_viet_the bt ON bt.ma_bai_viet = b.id
             WHERE b.id != :newsId
               AND b.trang_thai = 'xuat_ban'
               AND (b.ma_danh_muc = :catId {$tagIn})
             ORDER BY b.noi_bat DESC, b.ngay_tao DESC
             LIMIT :limit"
        );
        $this->db->bind(':newsId', $newsId, PDO::PARAM_INT);
        $this->db->bind(':catId',  $categoryId, PDO::PARAM_INT);
        $this->db->bind(':limit',  $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /* =====================================================
       CẬP NHẬT COUNTER
       ===================================================== */

    public function incrementView(int $id): bool
    {
        $this->db->query("UPDATE bai_viet SET luot_xem = luot_xem + 1 WHERE id = :id");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->execute();
    }

    public function incrementShare(int $id): bool
    {
        $this->db->query("UPDATE bai_viet SET luot_chia_se = luot_chia_se + 1 WHERE id = :id");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->execute();
    }

    public function incrementComment(int $id): bool
    {
        $this->db->query("UPDATE bai_viet SET luot_binh_luan = luot_binh_luan + 1 WHERE id = :id");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->execute();
    }

    public function decrementComment(int $id): bool
    {
        $this->db->query("UPDATE bai_viet SET luot_binh_luan = GREATEST(0, luot_binh_luan - 1) WHERE id = :id");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->execute();
    }

    public function setLikeCount(int $id, int $count): bool
    {
        $this->db->query("UPDATE bai_viet SET luot_thich = :count WHERE id = :id");
        $this->db->bind(':count', $count, PDO::PARAM_INT);
        $this->db->bind(':id',    $id,    PDO::PARAM_INT);
        return $this->db->execute();
    }

    /* =====================================================
       RSS FEED
       ===================================================== */

    public function findForRss(int $limit = 20): array
    {
        $this->db->query(
            "SELECT b.id, b.tieu_de, b.duong_dan, b.tom_tat, b.anh_thu_nho,
                    b.ngay_tao, b.ngay_cap_nhat, b.meta_description,
                    d.ten AS ten_danh_muc, n.ten AS ten_tac_gia
             FROM bai_viet b
             LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
             LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
             WHERE b.trang_thai = 'xuat_ban'
             ORDER BY b.ngay_tao DESC LIMIT :limit"
        );
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /* =====================================================
       PRIVATE HELPER
       ===================================================== */

    /**
     * Xây dựng WHERE clause từ filters.
     * Trả về [whereString, bindings].
     */
    private function buildWhereClause(array $filters): array
    {
        $conditions = ["b.trang_thai = 'xuat_ban'"];
        $binds = [];

        if (!empty($filters['category_id'])) {
            $conditions[] = 'b.ma_danh_muc = :category_id';
            $binds[':category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['tag_id'])) {
            // JOIN tag sẽ được thêm vào WHERE
            $conditions[] = 'bt.ma_the = :tag_id';
            $binds[':tag_id'] = (int)$filters['tag_id'];
        }

        if (!empty($filters['keyword'])) {
            $kw = '%' . $filters['keyword'] . '%';
            $conditions[] = '(b.tieu_de LIKE :kw OR b.tom_tat LIKE :kw2)';
            $binds[':kw']  = $kw;
            $binds[':kw2'] = $kw;
        }

        // Join tag nếu cần
        $tagJoin = !empty($filters['tag_id'])
            ? 'LEFT JOIN bai_viet_the bt ON bt.ma_bai_viet = b.id'
            : '';

        $whereStr = 'WHERE ' . implode(' AND ', $conditions);
        return [$tagJoin . ' ' . $whereStr, $binds];
    }
}
