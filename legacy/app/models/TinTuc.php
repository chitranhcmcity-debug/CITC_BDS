<?php
/**
 * Model TinTuc - Quản lý bài viết / tin tức.
 * Bang CSDL: bai_viet
 *
 * Trang thai bai viet:
 *   'nhap'     = Bai nhap (chua xuat ban)
 *   'xuat_ban' = Da xuat ban (hien thi frontend)
 */
class TinTuc extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'bai_viet';
    }

    // ==========================================
    // TRUY VẤN DỮ LIỆU
    // ==========================================

    /**
     * Lay tat ca bai viet (ca nhap va da xuat ban), kem thong tin danh muc va tac gia.
     * Dung cho trang quan tri Admin.
     *
     * @param  int|null $limit So luong toi da (null = lay tat ca)
     * @return array           Danh sach bai viet
     */
    public function layTatCa(?int $limit = null): array
    {
        $sql = "SELECT b.*, d.ten AS ten_danh_muc, n.ten AS ten_tac_gia
                FROM bai_viet b
                LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
                LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
                ORDER BY b.ngay_tao DESC";

        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $this->db->query($sql);
        return $this->db->resultSet();
    }

    /** Loc danh sach bai viet trong trang quan tri. */
    public function locAdmin(array $filters): array
    {
        $conditions = [];
        $bindings = [];

        if ($filters['keyword'] !== '') {
            $keyword = '%' . $filters['keyword'] . '%';
            $conditions[] = "(b.tieu_de LIKE :keyword_title OR n.ten LIKE :keyword_author"
                          . (ctype_digit($filters['keyword']) ? " OR b.id = :news_id" : "") . ")";
            $bindings[':keyword_title'] = $keyword;
            $bindings[':keyword_author'] = $keyword;
            if (ctype_digit($filters['keyword'])) {
                $bindings[':news_id'] = (int)$filters['keyword'];
            }
        }

        if ($filters['category_id'] !== '') {
            $conditions[] = "b.ma_danh_muc = :category_id";
            $bindings[':category_id'] = (int)$filters['category_id'];
        }

        if ($filters['status'] !== '') {
            $conditions[] = "b.trang_thai = :status";
            $bindings[':status'] = $filters['status'];
        }

        if ($filters['featured'] !== '') {
            $conditions[] = "b.noi_bat = :featured";
            $bindings[':featured'] = (int)$filters['featured'];
        }

        $sql = "SELECT b.*, d.ten AS ten_danh_muc, n.ten AS ten_tac_gia
                FROM bai_viet b
                LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
                LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id";
        if ($conditions) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        $sql .= " ORDER BY b.ngay_tao DESC";

        $this->db->query($sql);
        foreach ($bindings as $parameter => $value) {
            $this->db->bind($parameter, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        return $this->db->resultSet();
    }

    /**
     * Lay cac bai viet da xuat ban. Dung cho trang frontend.
     *
     * @param  int|null $limit So luong toi da
     * @return array           Danh sach bai viet da xuat ban
     */
    public function layDaXuatBan(?int $limit = null): array
    {
        $sql = "SELECT b.*, d.ten AS ten_danh_muc, n.ten AS ten_tac_gia
                FROM bai_viet b
                LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
                LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
                WHERE b.trang_thai = 'xuat_ban'
                ORDER BY b.ngay_tao DESC";

        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $this->db->query($sql);
        return $this->db->resultSet();
    }

    /**
     * Lay bai viet noi bat (noi_bat = 1) da xuat ban.
     * Neu $limit = 1 tra ve 1 doi tuong, nguoc lai tra ve mang.
     *
     * @param  int   $limit So luong can lay
     * @return mixed        1 bai viet (object), mang bai viet, hoac false neu khong co
     */
    public function layNoiBat(int $limit = 1): mixed
    {
        $sql = "SELECT b.*, d.ten AS ten_danh_muc, n.ten AS ten_tac_gia
                FROM bai_viet b
                LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
                LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
                WHERE b.trang_thai = 'xuat_ban' AND b.noi_bat = 1
                ORDER BY b.ngay_tao DESC
                LIMIT " . (int)$limit;

        $this->db->query($sql);
        return $limit === 1 ? $this->db->single() : $this->db->resultSet();
    }

    /**
     * Tim bai viet theo duong dan (slug) URL.
     *
     * @param  string $slug Duong dan SEO (vi du: 'tin-tuc-bat-dong-san-2025')
     * @return mixed        Bai viet (object) hoac false neu khong tim thay
     */
    public function layTheoSlug(string $slug): mixed
    {
        $this->db->query("SELECT b.*, d.ten AS ten_danh_muc, n.ten AS ten_tac_gia
                          FROM bai_viet b
                          LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
                          LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
                          WHERE b.duong_dan = :slug");
        $this->db->bind(':slug', $slug);
        return $this->db->single();
    }

    /**
     * Lay cac bai viet lien quan (cung danh muc, loai tru bai hien tai).
     *
     * @param  int   $maDanhMuc ID danh muc
     * @param  int   $loaiTruId ID bai viet can loai tru
     * @param  int   $limit     So luong ket qua
     * @return array            Danh sach bai viet lien quan
     */
    public function layLienQuan(int $maDanhMuc, int $loaiTruId, int $limit = 4): array
    {
        $this->db->query("SELECT b.*, d.ten AS ten_danh_muc, n.ten AS ten_tac_gia
                          FROM bai_viet b
                          LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
                          LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
                          WHERE b.ma_danh_muc = :maDanhMuc 
                            AND b.id != :loaiTruId 
                            AND b.trang_thai = 'xuat_ban'
                          ORDER BY b.ngay_tao DESC
                          LIMIT " . (int)$limit);
        $this->db->bind(':maDanhMuc', $maDanhMuc);
        $this->db->bind(':loaiTruId', $loaiTruId);
        return $this->db->resultSet();
    }

    // ==========================================
    // THÊM / SỬA / XÓA
    // ==========================================

    /**
     * Them moi mot bai viet vao CSDL.
     *
     * @param  array $data Du lieu bai viet (cac truong tuong ung voi cot trong bang)
     * @return bool        True neu them thanh cong
     */
    public function them(array $data): bool
    {
        $this->db->query("INSERT INTO bai_viet 
            (ma_nguoi_dung, ma_danh_muc, tieu_de, duong_dan, tom_tat, noi_dung, anh_thu_nho, trang_thai, noi_bat)
            VALUES 
            (:ma_nguoi_dung, :ma_danh_muc, :tieu_de, :duong_dan, :tom_tat, :noi_dung, :anh_thu_nho, :trang_thai, :noi_bat)");

        $this->db->bind(':ma_nguoi_dung', $data['ma_nguoi_dung']);
        $this->db->bind(':ma_danh_muc',   $data['ma_danh_muc']);
        $this->db->bind(':tieu_de',        $data['tieu_de']);
        $this->db->bind(':duong_dan',      $data['duong_dan']);
        $this->db->bind(':tom_tat',        $data['tom_tat']);
        $this->db->bind(':noi_dung',       $data['noi_dung']);
        $this->db->bind(':anh_thu_nho',    $data['anh_thu_nho']);
        $this->db->bind(':trang_thai',     $data['trang_thai']);
        $this->db->bind(':noi_bat',        $data['noi_bat']);

        return $this->db->execute();
    }

    /**
     * Cap nhat thong tin bai viet theo ID.
     *
     * @param  array $data Du lieu can cap nhat, phai co truong 'id'
     * @return bool        True neu cap nhat thanh cong
     */
    public function capNhat(array $data): bool
    {
        $this->db->query("UPDATE bai_viet SET
            ma_danh_muc = :ma_danh_muc,
            tieu_de     = :tieu_de,
            duong_dan   = :duong_dan,
            tom_tat     = :tom_tat,
            noi_dung    = :noi_dung,
            anh_thu_nho = :anh_thu_nho,
            trang_thai  = :trang_thai,
            noi_bat     = :noi_bat
            WHERE id    = :id");

        $this->db->bind(':ma_danh_muc',  $data['ma_danh_muc']);
        $this->db->bind(':tieu_de',       $data['tieu_de']);
        $this->db->bind(':duong_dan',     $data['duong_dan']);
        $this->db->bind(':tom_tat',       $data['tom_tat']);
        $this->db->bind(':noi_dung',      $data['noi_dung']);
        $this->db->bind(':anh_thu_nho',   $data['anh_thu_nho']);
        $this->db->bind(':trang_thai',    $data['trang_thai']);
        $this->db->bind(':noi_bat',       $data['noi_bat']);
        $this->db->bind(':id',            $data['id']);

        return $this->db->execute();
    }

    /**
     * Tang luot xem cua bai viet len 1.
     *
     * @param  int  $id ID bai viet
     * @return bool     True neu cap nhat thanh cong
     */
    public function tangLuotXem(int $id): bool
    {
        $this->db->query("UPDATE bai_viet SET luot_xem = luot_xem + 1 WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Alias de tuong thich nguoc voi Controller cu
    public function getAllNews(?int $limit = null): array         { return $this->layTatCa($limit); }
    public function getPublishedNews(?int $limit = null): array  { return $this->layDaXuatBan($limit); }
    public function getFeaturedNews(int $limit = 1): mixed       { return $this->layNoiBat($limit); }
    public function getBySlug(string $slug): mixed               { return $this->layTheoSlug($slug); }
    public function getRelatedNews(int $catId, int $exId, int $limit = 4): array { return $this->layLienQuan($catId, $exId, $limit); }
    public function insert(array $data): bool                    { return $this->them($data); }
    public function update(array $data): bool                    { return $this->capNhat($data); }
    public function updateViewCount(int $id): bool               { return $this->tangLuotXem($id); }
}
