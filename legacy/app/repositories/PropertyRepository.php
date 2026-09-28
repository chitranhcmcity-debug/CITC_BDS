<?php
/**
 * PropertyRepository – Tìm kiếm các bất động sản phù hợp trong bảng `du_an` (hoặc tin đăng) 
 * để AI giới thiệu và gửi đề xuất cho khách hàng.
 * Tuân thủ SOLID, Repository Pattern.
 */
class PropertyRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function exists(int $id): bool
    {
        $this->db->query('SELECT 1 FROM du_an WHERE id = :id LIMIT 1');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return (bool) $this->db->single();
    }

    public function findBySlug(string $slug): ?object
    {
        $this->db->query("SELECT p.*, c.ten AS ten_danh_muc, c.duong_dan AS slug_danh_muc,
                                u.ten AS ten_nguoi_dung, u.dien_thoai,
                                u.email AS email_nguoi_dung, u.anh_dai_dien,
                                u.ngay_tao AS ngay_tham_gia,
                                u.trang_thai AS trang_thai_nguoi_dung,
                                (SELECT COUNT(*) FROM du_an p2
                                 WHERE p2.ma_nguoi_dung = p.ma_nguoi_dung
                                   AND p2.trang_thai = 'xuat_ban') AS so_tin_dang,
                                (SELECT COUNT(*) FROM yeu_thich yt
                                 WHERE yt.ma_du_an = p.id) AS tong_luot_luu
                         FROM du_an p
                         JOIN danh_muc c ON p.ma_danh_muc = c.id
                         LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
                         WHERE p.duong_dan = :slug
                           AND p.trang_thai = 'xuat_ban'");
        $this->db->bind(':slug', $slug);
        $result = $this->db->single();

        return $result ?: null;
    }

    public function findImages(int $id): array
    {
        $this->db->query('SELECT * FROM hinh_anh_du_an
                          WHERE ma_du_an = :id
                          ORDER BY la_anh_dai_dien DESC, thu_tu, id');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }

    public function findRelated(int $excludeId, string $type, string $location, int $limit = 12): array
    {
        $this->db->query("SELECT * FROM du_an
                          WHERE id != :exclude_id
                            AND loai_bat_dong_san = :type
                            AND tinh_thanh = :location
                            AND trang_thai = 'xuat_ban'
                          ORDER BY id DESC LIMIT :limit");
        $this->db->bind(':exclude_id', $excludeId, PDO::PARAM_INT);
        $this->db->bind(':type', $type);
        $this->db->bind(':location', $location);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }

    public function findByUser(int $userId, int $excludeId, int $limit = 6): array
    {
        $this->db->query("SELECT * FROM du_an
                          WHERE ma_nguoi_dung = :user_id
                            AND id != :exclude_id
                            AND trang_thai = 'xuat_ban'
                          ORDER BY id DESC LIMIT :limit");
        $this->db->bind(':user_id', $userId, PDO::PARAM_INT);
        $this->db->bind(':exclude_id', $excludeId, PDO::PARAM_INT);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }

    public function incrementShare(int $id): bool
    {
        $this->db->query('UPDATE du_an SET luot_chia_se = COALESCE(luot_chia_se, 0) + 1 WHERE id = :id');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    public function ratingSummary(int $postId): array
    {
        $this->db->query("SELECT COALESCE(AVG(so_sao),0) average,COUNT(*) total FROM danh_gia WHERE ma_du_an=:post AND trang_thai='hien_thi'");
        $this->db->bind(':post', $postId, PDO::PARAM_INT);
        $row=(array)($this->db->single() ?: []);
        return ['average'=>round((float)($row['average']??0),1),'total'=>(int)($row['total']??0)];
    }

    public function userRating(int $postId, int $userId): int
    {
        $this->db->query('SELECT so_sao FROM danh_gia WHERE ma_du_an=:post AND ma_nguoi_dung=:user LIMIT 1');
        $this->db->bind(':post',$postId,PDO::PARAM_INT);$this->db->bind(':user',$userId,PDO::PARAM_INT);
        return (int)($this->db->single()->so_sao??0);
    }

    public function saveRating(int $postId, int $userId, int $stars): bool
    {
        $this->db->query("INSERT INTO danh_gia(ma_nguoi_dung,ma_du_an,so_sao,trang_thai) VALUES(:user,:post,:stars,'hien_thi') ON DUPLICATE KEY UPDATE so_sao=VALUES(so_sao),trang_thai='hien_thi',ngay_tao=CURRENT_TIMESTAMP");
        $this->db->bind(':user',$userId,PDO::PARAM_INT);$this->db->bind(':post',$postId,PDO::PARAM_INT);$this->db->bind(':stars',$stars,PDO::PARAM_INT);
        return $this->db->execute();
    }

    /**
     * Tìm 5 bất động sản phù hợp theo các tham số phân tích.
     */
    public function findRecommendations(?float $maxBudget, ?string $location, ?float $minArea, ?string $type, ?string $purpose): array
    {
        $sql = "SELECT id, tieu_de, gia, dien_tich, vi_tri, loai_bat_dong_san, duong_dan, so_phong_ngu 
                FROM du_an 
                WHERE 1=1";
        $binds = [];

        // 1. Loại giao dịch (bán / cho thuê)
        if (!empty($purpose)) {
            if ($purpose === 'thue') {
                $sql .= " AND (tieu_de LIKE :purpose OR loai_bat_dong_san LIKE :purpose2)";
                $binds[':purpose'] = '%thuê%';
                $binds[':purpose2'] = '%thuê%';
            } else {
                $sql .= " AND (tieu_de NOT LIKE :purpose AND loai_bat_dong_san NOT LIKE :purpose2)";
                $binds[':purpose'] = '%thuê%';
                $binds[':purpose2'] = '%thuê%';
            }
        }

        // 2. Ngân sách tối đa
        if ($maxBudget !== null && $maxBudget > 0) {
            $sql .= " AND gia <= :max_budget AND gia > 0";
            $binds[':max_budget'] = $maxBudget;
        }

        // 3. Vị trí địa lý
        if (!empty($location)) {
            $sql .= " AND vi_tri LIKE :location";
            $binds[':location'] = '%' . $location . '%';
        }

        // 4. Diện tích tối thiểu
        if ($minArea !== null && $minArea > 0) {
            $sql .= " AND dien_tich >= :min_area";
            $binds[':min_area'] = $minArea;
        }

        // 5. Loại hình nhà đất
        if (!empty($type)) {
            $sql .= " AND (tieu_de LIKE :type OR loai_bat_dong_san LIKE :type2)";
            $binds[':type']  = '%' . $type . '%';
            $binds[':type2'] = '%' . $type . '%';
        }

        $sql .= " ORDER BY id DESC LIMIT 5";

        $this->db->query($sql);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }

        return $this->db->resultSet();
    }

    /**
     * Lấy danh sách tin đăng phục vụ Admin (kèm phân trang, lọc, tìm kiếm).
     */
    public function adminList(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        [$sql, $binds] = $this->buildAdminQuery($filters);
        $sql .= " ORDER BY du_an.id DESC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Đếm tổng số tin đăng theo bộ lọc phục vụ Admin.
     */
    public function adminCount(array $filters = []): int
    {
        [$sql, $binds] = $this->buildAdminQuery($filters, true);

        $this->db->query($sql);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }

        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    /**
     * Tìm chi tiết tin đăng phục vụ Admin.
     */
    public function adminFind(int $id): ?stdClass
    {
        $this->db->query("SELECT du_an.*, u.ten AS seller_name, u.email AS seller_email, u.dien_thoai AS seller_phone, c.ten AS category_name
                          FROM du_an
                          LEFT JOIN nguoi_dung u ON du_an.ma_nguoi_dung = u.id
                          LEFT JOIN danh_muc c ON du_an.ma_danh_muc = c.id
                          WHERE du_an.id = :id");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $row = $this->db->single();
        return $row ?: null;
    }

    /**
     * Cập nhật trạng thái duyệt tin.
     */
    public function updateStatus(int $id, string $status, ?string $rejectReason = null): bool
    {
        $this->db->query("UPDATE du_an SET trang_thai = :status, ly_do_tu_choi = :reason WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':reason', $rejectReason);
        $this->db->bind(':id',     $id, PDO::PARAM_INT);
        return $this->db->execute();
    }

    /** Chuyển trạng thái duyệt nguyên tử, tránh bấm lặp hoặc duyệt nhầm trạng thái. */
    public function updateApprovalStatus(int $id, string $status, ?string $rejectReason = null): bool
    {
        $expirySql = $status === 'xuat_ban'
            ? ", ngay_het_han = CASE WHEN ngay_het_han IS NULL OR ngay_het_han < NOW() THEN DATE_ADD(NOW(), INTERVAL 90 DAY) ELSE ngay_het_han END"
            : '';
        $this->db->query("UPDATE du_an
            SET trang_thai = :status, ly_do_tu_choi = :reason{$expirySql}
            WHERE id = :id AND trang_thai = 'cho_duyet'");
        $this->db->bind(':status', $status);
        $this->db->bind(':reason', $rejectReason);
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->execute() && $this->db->rowCount() === 1;
    }

    /** Xoa vinh vien tin dang; cac bang lien ket duoc FK CASCADE/SET NULL xu ly. */
    public function hardDelete(int $id): bool
    {
        $this->db->query("DELETE FROM du_an WHERE id = :id");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->execute() && $this->db->rowCount() > 0;
    }

    /** Xoa mem tin do bao cao duoc chap nhan, giu ban ghi de phuc vu lich su. */
    public function softDeleteByReport(int $id): bool
    {
        $this->db->query("UPDATE du_an
                          SET trang_thai = 'xoa', deleted_at = NOW()
                          WHERE id = :id AND deleted_at IS NULL");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->execute() && $this->db->rowCount() === 1;
    }

    /**
     * Cập nhật gói VIP.
     */
    public function updateVip(int $id, int $vipLevel, string $expiry): bool
    {
        $this->db->query("UPDATE du_an SET goi_vip = :vip, ngay_het_han_vip = :expiry WHERE id = :id");
        $this->db->bind(':vip',    $vipLevel, PDO::PARAM_INT);
        $this->db->bind(':expiry', $expiry);
        $this->db->bind(':id',     $id, PDO::PARAM_INT);
        return $this->db->execute();
    }

    /**
     * Gia hạn thời hạn hiển thị tin đăng.
     */
    public function renew(int $id, string $expiry): bool
    {
        $this->db->query("UPDATE du_an SET ngay_het_han = :expiry WHERE id = :id");
        $this->db->bind(':expiry', $expiry);
        $this->db->bind(':id',     $id, PDO::PARAM_INT);
        return $this->db->execute();
    }

    /**
     * Admin cập nhật thông tin tin đăng.
     */
    public function adminUpdate(int $id, array $d): bool
    {
        $this->db->query("UPDATE du_an 
                          SET ma_danh_muc = :category, tieu_de = :title, duong_dan = :slug, mo_ta = :description,
                              gia = :price, dien_tich = :area, vi_tri = :location, tinh_thanh = :province,
                              quan_huyen = :district, phuong_xa = :ward, dia_chi = :address,
                              loai_bat_dong_san = :property_type, loai_giao_dich = :transaction_type,
                              phap_ly = :legal, huong_nha = :direction, mat_tien = :frontage,
                              so_phong_ngu = :bedrooms, so_phong_wc = :bathrooms, link_video = :video_url,
                              link_360 = :tour360, nguoi_lien_he = :contact, so_dien_thoai_lien_he = :phone,
                              ten_du_an = :project, noi_that = :interior, chieu_rong = :width,
                              chieu_dai = :length, so_tang = :floors, nam_xay_dung = :year,
                              trang_thai = :status, goi_vip = :vip, ngay_het_han_vip = :vip_expiry,
                              ngay_het_han = :expiry, meta_title = :meta_title, meta_description = :meta_description,
                              meta_keywords = :keywords
                          WHERE id = :id");

        $this->db->bind(':category',         (int)$d['category_id']);
        $this->db->bind(':title',            $d['title']);
        $this->db->bind(':slug',             $d['slug']);
        $this->db->bind(':description',      $d['description']);
        $this->db->bind(':price',            $d['price']);
        $this->db->bind(':area',             $d['area']);
        $this->db->bind(':location',         $d['location']);
        $this->db->bind(':province',         $d['province'] ?? null);
        $this->db->bind(':district',         $d['district'] ?? null);
        $this->db->bind(':ward',             $d['ward'] ?? null);
        $this->db->bind(':address',          $d['address'] ?? null);
        $this->db->bind(':property_type',    $d['property_type']);
        $this->db->bind(':transaction_type', $d['transaction_type'] ?? 'ban');
        $this->db->bind(':legal',            $d['legal'] ?? null);
        $this->db->bind(':direction',        $d['direction'] ?? null);
        $this->db->bind(':frontage',         $d['frontage'] ?? null);
        $this->db->bind(':bedrooms',         $d['bedrooms'] ? (int)$d['bedrooms'] : null);
        $this->db->bind(':bathrooms',        $d['bathrooms'] ? (int)$d['bathrooms'] : null);
        $this->db->bind(':video_url',        $d['video_url'] ?? null);
        $this->db->bind(':tour360',          $d['tour360'] ?? null);
        $this->db->bind(':contact',          $d['contact_name'] ?? null);
        $this->db->bind(':phone',            $d['contact_phone'] ?? null);
        $this->db->bind(':project',          $d['project_name'] ?? null);
        $this->db->bind(':interior',         $d['interior'] ?? null);
        $this->db->bind(':width',            $d['width'] ?? null);
        $this->db->bind(':length',           $d['length'] ?? null);
        $this->db->bind(':floors',           $d['floors'] ? (int)$d['floors'] : null);
        $this->db->bind(':year',             $d['construction_year'] ? (int)$d['construction_year'] : null);
        $this->db->bind(':status',           $d['status']);
        $this->db->bind(':vip',              (int)$d['vip_level']);
        $this->db->bind(':vip_expiry',       $d['vip_expires_at'] ?: null);
        $this->db->bind(':expiry',           $d['expires_at'] ?: null);
        $this->db->bind(':meta_title',       $d['meta_title'] ?? null);
        $this->db->bind(':meta_description', $d['meta_description'] ?? null);
        $this->db->bind(':keywords',         $d['meta_keywords'] ?? null);
        $this->db->bind(':id',               $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Hàm dùng chung xây dựng câu truy vấn SQL động cho Admin.
     */
    private function buildAdminQuery(array $filters, bool $isCount = false): array
    {
        $select = $isCount ? "SELECT COUNT(*) AS total" : "SELECT du_an.*, u.ten AS seller_name, c.ten AS category_name";
        $sql = "{$select} FROM du_an 
                LEFT JOIN nguoi_dung u ON du_an.ma_nguoi_dung = u.id
                LEFT JOIN danh_muc c ON du_an.ma_danh_muc = c.id
                WHERE du_an.deleted_at IS NULL";
        $binds = [];

        // 1. Lọc theo người đăng
        if (isset($filters['user_id']) && (int)$filters['user_id'] > 0) {
            $sql .= " AND du_an.ma_nguoi_dung = :user_id";
            $binds[':user_id'] = (int)$filters['user_id'];
        }

        // 2. Lọc theo VIP level
        if (isset($filters['vip_level']) && $filters['vip_level'] !== '') {
            $sql .= " AND du_an.goi_vip = :vip_level";
            $binds[':vip_level'] = (int)$filters['vip_level'];
        }

        // 3. Lọc theo trạng thái
        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND du_an.trang_thai = :status";
            $binds[':status'] = $filters['status'];
        }

        // 4. Lọc theo loại (giao dịch)
        if (isset($filters['transaction_type']) && $filters['transaction_type'] !== '') {
            $sql .= " AND du_an.loai_giao_dich = :trans_type";
            $binds[':trans_type'] = $filters['transaction_type'];
        }

        // 5. Lọc theo danh mục
        if (isset($filters['category_id']) && (int)$filters['category_id'] > 0) {
            $sql .= " AND du_an.ma_danh_muc = :cat_id";
            $binds[':cat_id'] = (int)$filters['category_id'];
        }

        // 6. Lọc theo khu vực
        if (!empty($filters['location'])) {
            $sql .= " AND du_an.vi_tri LIKE :location";
            $binds[':location'] = '%' . $filters['location'] . '%';
        }

        // 7. Lọc theo giá (ví dụ: CAST gia as unsigned)
        if (isset($filters['min_price']) && (float)$filters['min_price'] > 0) {
            $sql .= " AND du_an.gia_so >= :min_price";
            $binds[':min_price'] = (float)$filters['min_price'];
        }
        if (isset($filters['max_price']) && (float)$filters['max_price'] > 0) {
            $sql .= " AND du_an.gia_so <= :max_price";
            $binds[':max_price'] = (float)$filters['max_price'];
        }

        // 8. Lọc ngày đăng
        if (!empty($filters['start_date'])) {
            $sql .= " AND du_an.ngay_tao >= :start_date";
            $binds[':start_date'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND du_an.ngay_tao <= :end_date";
            $binds[':end_date'] = $filters['end_date'] . ' 23:59:59';
        }

        // 9. Tìm kiếm văn bản tự do
        if (!empty($filters['search'])) {
            $searchVal = '%' . $filters['search'] . '%';
            $sql .= " AND (du_an.id = :search_id 
                        OR du_an.tieu_de LIKE :search_title 
                        OR u.ten LIKE :search_user 
                        OR u.dien_thoai LIKE :search_phone 
                        OR u.email LIKE :search_email 
                        OR du_an.dia_chi LIKE :search_addr)";
            $binds[':search_id']    = (int)$filters['search'];
            $binds[':search_title'] = $searchVal;
            $binds[':search_user']  = $searchVal;
            $binds[':search_phone'] = $searchVal;
            $binds[':search_email'] = $searchVal;
            $binds[':search_addr']  = $searchVal;
        }

        return [$sql, $binds];
    }
}
