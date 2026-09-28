<?php

/**
 * Model DuAn - Quản lý tin đăng bất động sản.
 * Bang CSDL: du_an
 *
 * Trang thai tin dang:
 *   'cho_duyet' = Dang cho Admin duyet
 *   'xuat_ban'  = Da duoc duyet, hien thi frontend
 *   'nhap'      = Bai nhap, an
 *
 * Goi VIP (goi_vip):
 *   0 = Thuong, 1 = VIP1, 2 = VIP2, ... 5 = VIP5
 */
class DuAn extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'du_an';
    }

    // ==========================================
    // TRUY VẤN CHO FRONTEND
    // ==========================================

    /**
     * Lay tin dang noi bat cho trang chu.
     * Uu tien goi VIP cao, loc tin con han hoac khong gioi han.
     *
     * @param  int  $limit  So luong can lay
     * @return array Danh sach tin noi bat
     */
    public function layNoiBat(int $limit = 6): array
    {
        $this->db->query("SELECT p.*, c.ten AS ten_danh_muc, u.anh_dai_dien, u.ten AS ten_nguoi_dung,
                          CASE WHEN (p.ngay_het_han_vip IS NULL OR p.ngay_het_han_vip > NOW()) 
                               THEN p.goi_vip ELSE 0 END AS active_vip
                          FROM du_an p
                          JOIN danh_muc c ON p.ma_danh_muc = c.id
                          LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
                          WHERE p.trang_thai = 'xuat_ban'
                            AND (p.ngay_het_han_vip IS NULL OR p.ngay_het_han_vip > NOW())
                          ORDER BY p.goi_vip DESC, p.ngay_tao DESC
                          LIMIT :limit");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Lay tin dang theo thu tu SEO: VIP > noi bat > luot xem > moi nhat.
     * Dung cho khu vuc "Tin duoc tim nhieu" tren trang chu.
     *
     * @param  int  $limit  So luong can lay
     * @return array Danh sach tin SEO cao
     */
    public function layTopSeo(int $limit = 4): array
    {
        $this->db->query("SELECT p.*, c.ten AS ten_danh_muc,
                          CASE WHEN (p.ngay_het_han_vip IS NULL OR p.ngay_het_han_vip > NOW())
                               THEN p.goi_vip ELSE 0 END AS active_vip
                          FROM du_an p
                          JOIN danh_muc c ON p.ma_danh_muc = c.id
                          WHERE p.trang_thai = 'xuat_ban'
                          ORDER BY
                            CASE WHEN (p.ngay_het_han_vip IS NULL OR p.ngay_het_han_vip > NOW())
                                 THEN p.goi_vip ELSE 0 END DESC,
                            p.noi_bat DESC,
                            p.luot_xem DESC,
                            p.ngay_tao DESC
                          LIMIT :limit");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Lay cac tin moi nhat da xuat ban.
     *
     * @param  int  $limit  So luong can lay
     * @return array Danh sach tin moi
     */
    public function layMoiNhat(int $limit = 6): array
    {
        $this->db->query("SELECT p.*, c.ten AS ten_danh_muc,
                          u.ten AS ten_nguoi_dung, u.dien_thoai, u.anh_dai_dien,
                          CASE WHEN (p.ngay_het_han_vip IS NULL OR p.ngay_het_han_vip > NOW())
                               THEN p.goi_vip ELSE 0 END AS active_vip
                          FROM du_an p
                          JOIN danh_muc c ON p.ma_danh_muc = c.id
                          LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
                          WHERE p.trang_thai = 'xuat_ban'
                          ORDER BY p.ngay_tao DESC
                          LIMIT :limit");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Lay danh sach tin dang co the loc theo loai (ban/cho thue).
     * Tin VIP duoc uu tien hien thi truoc.
     *
     * @param  string|null  $loai  'sale' = ban, 'rent' = cho thue, null = tat ca
     * @param  int  $limit  So luong ket qua
     * @return array Danh sach tin dang
     */
    public function layDanhSach(?string $loai = null, int $limit = 20): array
    {
        // Uy quyen sang locNangCao voi bo loc don gian
        return $this->locNangCao(['loai' => $loai, 'limit' => $limit]);
    }

    /**
     * Loc nang cao tin dang - CHONG SQL INJECTION hoan toan.
     *
     * Nguyen tac bao mat:
     *   1. Tat ca tham so so (gia, dien_tich, so_phong) duoc EP KIEU NGUYEN truoc khi bind
     *   2. Tat ca gia tri LIKE duoc bind qua PDO (khong cong chuoi)
     *   3. Gia tri loai/loai_bds duoc kiem tra WHITELIST truoc khi dua vao SQL
     *   4. Limit duoc ep kieu int, gioi han toi da 100 de tranh dump toan bo bang
     *
     * @param  array  $filters  [
     *                          'loai'       => 'sale'|'rent'|null,      // Loai giao dich
     *                          'tinh'       => string|null,              // Ten tinh/TP
     *                          'gia_min'    => int|null,                 // Gia toi thieu (VND)
     *                          'gia_max'    => int|null,                 // Gia toi da
     *                          'dt_min'     => float|null,               // Dien tich toi thieu (m2)
     *                          'dt_max'     => float|null,               // Dien tich toi da
     *                          'phong_ngu'  => int|null,                 // So phong ngu
     *                          'phong_ngu_min' => int|null,              // So phong ngu toi thieu
     *                          'huong'      => string|null,              // Huong nha
     *                          'tu_khoa'    => string|null,              // Tu khoa tim kiem
     *                          'limit'      => int,                      // So ket qua (mac dinh 20, toi da 100)
     *                          'trang'      => int,                      // Trang phan trang (mac dinh 1)
     *                          ]
     * @return array Danh sach tin dang da loc
     */
    public function locNangCao(array $filters = []): array
    {
        // --- EP KIEU NGHIEM NGAT (OWASP A03 - Injection Prevention) ---
        $loai = in_array($filters['loai'] ?? '', ['sale', 'rent'], true) ? $filters['loai'] : null;
        $giaMin = isset($filters['gia_min']) ? (int) $filters['gia_min'] : null;
        $giaMax = isset($filters['gia_max']) ? (int) $filters['gia_max'] : null;
        $dtMin = isset($filters['dt_min']) ? (float) $filters['dt_min'] : null;
        $dtMax = isset($filters['dt_max']) ? (float) $filters['dt_max'] : null;
        $phongNgu = isset($filters['phong_ngu']) ? (int) $filters['phong_ngu'] : null;
        $phongNguMin = isset($filters['phong_ngu_min']) ? (int) $filters['phong_ngu_min'] : null;
        $limit = min((int) ($filters['limit'] ?? 20), 100); // Gioi han toi da 100
        $trang = max(1, (int) ($filters['trang'] ?? 1));
        $offset = ($trang - 1) * $limit;

        // Whitelist huong nha (chi cho phep gia tri co dinh, tranh injection)
        $huongWhitelist = ['Đông', 'Tây', 'Nam', 'Bắc', 'Đông Nam', 'Đông Bắc', 'Tây Nam', 'Tây Bắc'];
        $huong = in_array($filters['huong'] ?? '', $huongWhitelist, true) ? $filters['huong'] : null;

        // Tinh/TP va tu khoa: chi strip_tags, PDO tu xu ly escape khi bind
        $tinh = isset($filters['tinh']) ? strip_tags(trim($filters['tinh'])) : null;
        $quan = isset($filters['quan']) ? strip_tags(trim($filters['quan'])) : null;
        $phuong = isset($filters['phuong']) ? strip_tags(trim($filters['phuong'])) : null;
        $tuKhoa = isset($filters['tu_khoa']) ? strip_tags(trim($filters['tu_khoa'])) : null;
        $giaExpr = "(CASE
                    WHEN LOWER(p.gia) LIKE '%triệu%' THEN CAST(REPLACE(p.gia, ',', '.') AS DECIMAL(12,2)) * 1000000
                    WHEN LOWER(p.gia) LIKE '%tỷ%' THEN CAST(REPLACE(p.gia, ',', '.') AS DECIMAL(12,2)) * 1000000000
                    ELSE CAST(REPLACE(p.gia, ',', '.') AS DECIMAL(12,2))
                END)";

        // --- XAY DUNG SQL VOI PLACEHOLDER (KHONG CONG CHUOI DU LIEU) ---
        $sql = "SELECT p.*, c.ten AS ten_danh_muc, u.anh_dai_dien, u.ten AS ten_nguoi_dung, u.dien_thoai,
                CASE WHEN (p.ngay_het_han_vip IS NULL OR p.ngay_het_han_vip > NOW())
                     THEN p.goi_vip ELSE 0 END AS active_vip
                FROM du_an p
                JOIN danh_muc c ON p.ma_danh_muc = c.id
                LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
                WHERE p.trang_thai = 'xuat_ban'";

        $binds = []; // Mang tham so se bind sau

        // Loc loai giao dich
        if ($loai === 'rent') {
            $sql .= " AND (p.loai_bat_dong_san LIKE :loai_bds OR p.loai_bat_dong_san = 'Nhà đất cho thuê')";
            $binds[':loai_bds'] = ['val' => '%cho thuê%', 'type' => PDO::PARAM_STR];
        } elseif ($loai === 'sale') {
            $sql .= ' AND p.loai_bat_dong_san NOT LIKE :loai_bds';
            $binds[':loai_bds'] = ['val' => '%cho thuê%', 'type' => PDO::PARAM_STR];
        }

        // Loc tinh/TP (PDO tu xu ly escape ky tu dac biet)
        if (! empty($tinh)) {
            $sql .= ' AND p.tinh_thanh LIKE :tinh';
            $binds[':tinh'] = ['val' => '%'.$tinh.'%', 'type' => PDO::PARAM_STR];
        }
        if (! empty($quan)) {
            $sql .= ' AND p.vi_tri LIKE :quan';
            $binds[':quan'] = ['val' => '%'.$quan.'%', 'type' => PDO::PARAM_STR];
        }
        if (! empty($phuong)) {
            $sql .= ' AND p.vi_tri LIKE :phuong';
            $binds[':phuong'] = ['val' => '%'.$phuong.'%', 'type' => PDO::PARAM_STR];
        }

        // Loc khoang gia (da ep kieu int - an toan tuyet doi)
        if ($giaMin !== null) {
            $sql .= " AND {$giaExpr} >= :gia_min";
            $binds[':gia_min'] = ['val' => $giaMin, 'type' => PDO::PARAM_INT];
        }
        if ($giaMax !== null && $giaMax > 0) {
            $sql .= " AND {$giaExpr} <= :gia_max";
            $binds[':gia_max'] = ['val' => $giaMax, 'type' => PDO::PARAM_INT];
        }

        // Loc khoang dien tich (ep kieu float, bind duoi dang string vi PDO khong co PARAM_FLOAT)
        if ($dtMin !== null) {
            $sql .= ' AND p.dien_tich >= :dt_min';
            $binds[':dt_min'] = ['val' => (string) $dtMin, 'type' => PDO::PARAM_STR];
        }
        if ($dtMax !== null && $dtMax > 0) {
            $sql .= ' AND p.dien_tich <= :dt_max';
            $binds[':dt_max'] = ['val' => (string) $dtMax, 'type' => PDO::PARAM_STR];
        }

        // Loc so phong ngu (ep kieu int)
        if ($phongNgu !== null && $phongNgu > 0) {
            $sql .= ' AND p.so_phong_ngu = :phong_ngu';
            $binds[':phong_ngu'] = ['val' => $phongNgu, 'type' => PDO::PARAM_INT];
        }
        if ($phongNguMin !== null && $phongNguMin > 0) {
            $sql .= ' AND p.so_phong_ngu >= :phong_ngu_min';
            $binds[':phong_ngu_min'] = ['val' => $phongNguMin, 'type' => PDO::PARAM_INT];
        }

        // Loc huong nha (da qua whitelist nen an toan)
        if ($huong !== null) {
            $sql .= ' AND p.huong_nha = :huong';
            $binds[':huong'] = ['val' => $huong, 'type' => PDO::PARAM_STR];
        }

        // Tim kiem tu khoa (tieu de hoac vi tri)
        if (! empty($tuKhoa)) {
            $sql .= ' AND (p.tieu_de LIKE :tu_khoa OR p.vi_tri LIKE :tu_khoa2 OR p.tinh_thanh LIKE :tu_khoa3)';
            $binds[':tu_khoa'] = ['val' => '%'.$tuKhoa.'%', 'type' => PDO::PARAM_STR];
            $binds[':tu_khoa2'] = ['val' => '%'.$tuKhoa.'%', 'type' => PDO::PARAM_STR];
            $binds[':tu_khoa3'] = ['val' => '%'.$tuKhoa.'%', 'type' => PDO::PARAM_STR];
        }

        // Sap xep: VIP cao -> ngay tao moi nhat
        $sql .= ' ORDER BY
                    CASE WHEN (p.ngay_het_han_vip IS NULL OR p.ngay_het_han_vip > NOW())
                         THEN p.goi_vip ELSE 0 END DESC,
                    p.ngay_tao DESC
                  LIMIT :limit OFFSET :offset';

        $binds[':limit'] = ['val' => $limit,  'type' => PDO::PARAM_INT];
        $binds[':offset'] = ['val' => $offset, 'type' => PDO::PARAM_INT];

        // Thuc thi voi tat ca bind parameters
        $this->db->query($sql);
        foreach ($binds as $param => $config) {
            $this->db->bind($param, $config['val'], $config['type']);
        }

        return $this->db->resultSet();
    }

    /**
     * Dem tong so tin dang theo bo loc (dung cho phan trang).
     * Dung cac tieu chi loc giong locNangCao nhung chi dem, khong lay du lieu.
     *
     * @param  array  $filters  Cac tieu chi loc (giong locNangCao, bo qua limit/trang)
     * @return int Tong so tin thoa man bo loc
     */
    public function demTongLocNangCao(array $filters = []): int
    {
        $loai = in_array($filters['loai'] ?? '', ['sale', 'rent'], true) ? $filters['loai'] : null;
        $giaMin = isset($filters['gia_min']) ? (int) $filters['gia_min'] : null;
        $giaMax = isset($filters['gia_max']) ? (int) $filters['gia_max'] : null;
        $dtMin = isset($filters['dt_min']) ? (float) $filters['dt_min'] : null;
        $dtMax = isset($filters['dt_max']) ? (float) $filters['dt_max'] : null;
        $phongNgu = isset($filters['phong_ngu']) ? (int) $filters['phong_ngu'] : null;
        $phongNguMin = isset($filters['phong_ngu_min']) ? (int) $filters['phong_ngu_min'] : null;

        $huongWhitelist = ['Đông', 'Tây', 'Nam', 'Bắc', 'Đông Nam', 'Đông Bắc', 'Tây Nam', 'Tây Bắc'];
        $huong = in_array($filters['huong'] ?? '', $huongWhitelist, true) ? $filters['huong'] : null;
        $tinh = isset($filters['tinh']) ? strip_tags(trim($filters['tinh'])) : null;
        $quan = isset($filters['quan']) ? strip_tags(trim($filters['quan'])) : null;
        $phuong = isset($filters['phuong']) ? strip_tags(trim($filters['phuong'])) : null;
        $tuKhoa = isset($filters['tu_khoa']) ? strip_tags(trim($filters['tu_khoa'])) : null;
        $giaExpr = "(CASE
                    WHEN LOWER(p.gia) LIKE '%triệu%' THEN CAST(REPLACE(p.gia, ',', '.') AS DECIMAL(12,2)) * 1000000
                    WHEN LOWER(p.gia) LIKE '%tỷ%' THEN CAST(REPLACE(p.gia, ',', '.') AS DECIMAL(12,2)) * 1000000000
                    ELSE CAST(REPLACE(p.gia, ',', '.') AS DECIMAL(12,2))
                END)";

        $sql = "SELECT COUNT(*) FROM du_an p WHERE p.trang_thai = 'xuat_ban'";
        $binds = [];

        if ($loai === 'rent') {
            $sql .= " AND (p.loai_bat_dong_san LIKE :loai_bds OR p.loai_bat_dong_san = 'Nhà đất cho thuê')";
            $binds[':loai_bds'] = ['val' => '%cho thuê%', 'type' => PDO::PARAM_STR];
        } elseif ($loai === 'sale') {
            $sql .= ' AND p.loai_bat_dong_san NOT LIKE :loai_bds';
            $binds[':loai_bds'] = ['val' => '%cho thuê%', 'type' => PDO::PARAM_STR];
        }
        if (! empty($tinh)) {
            $sql .= ' AND p.tinh_thanh LIKE :tinh';
            $binds[':tinh'] = ['val' => '%'.$tinh.'%', 'type' => PDO::PARAM_STR];
        }
        if (! empty($quan)) {
            $sql .= ' AND p.vi_tri LIKE :quan';
            $binds[':quan'] = ['val' => '%'.$quan.'%', 'type' => PDO::PARAM_STR];
        }
        if (! empty($phuong)) {
            $sql .= ' AND p.vi_tri LIKE :phuong';
            $binds[':phuong'] = ['val' => '%'.$phuong.'%', 'type' => PDO::PARAM_STR];
        }
        if ($giaMin !== null) {
            $sql .= " AND {$giaExpr} >= :gia_min";
            $binds[':gia_min'] = ['val' => $giaMin, 'type' => PDO::PARAM_INT];
        }
        if ($giaMax !== null && $giaMax > 0) {
            $sql .= " AND {$giaExpr} <= :gia_max";
            $binds[':gia_max'] = ['val' => $giaMax, 'type' => PDO::PARAM_INT];
        }
        if ($dtMin !== null) {
            $sql .= ' AND p.dien_tich >= :dt_min';
            $binds[':dt_min'] = ['val' => (string) $dtMin, 'type' => PDO::PARAM_STR];
        }
        if ($dtMax !== null && $dtMax > 0) {
            $sql .= ' AND p.dien_tich <= :dt_max';
            $binds[':dt_max'] = ['val' => (string) $dtMax, 'type' => PDO::PARAM_STR];
        }
        if ($phongNgu !== null && $phongNgu > 0) {
            $sql .= ' AND p.so_phong_ngu = :phong_ngu';
            $binds[':phong_ngu'] = ['val' => $phongNgu, 'type' => PDO::PARAM_INT];
        }
        if ($phongNguMin !== null && $phongNguMin > 0) {
            $sql .= ' AND p.so_phong_ngu >= :phong_ngu_min';
            $binds[':phong_ngu_min'] = ['val' => $phongNguMin, 'type' => PDO::PARAM_INT];
        }
        if ($huong !== null) {
            $sql .= ' AND p.huong_nha = :huong';
            $binds[':huong'] = ['val' => $huong, 'type' => PDO::PARAM_STR];
        }
        if (! empty($tuKhoa)) {
            $sql .= ' AND (p.tieu_de LIKE :tu_khoa OR p.vi_tri LIKE :tu_khoa2 OR p.tinh_thanh LIKE :tu_khoa3)';
            $binds[':tu_khoa'] = ['val' => '%'.$tuKhoa.'%', 'type' => PDO::PARAM_STR];
            $binds[':tu_khoa2'] = ['val' => '%'.$tuKhoa.'%', 'type' => PDO::PARAM_STR];
            $binds[':tu_khoa3'] = ['val' => '%'.$tuKhoa.'%', 'type' => PDO::PARAM_STR];
        }

        $this->db->query($sql);
        foreach ($binds as $param => $config) {
            $this->db->bind($param, $config['val'], $config['type']);
        }

        return (int) $this->db->single()->{'COUNT(*)'};
    }

    /**
     * Dem so tin dang theo tung thanh pho.
     * Quet tat ca tin va kiem tra chuoi vi_tri co chua ten thanh pho hay khong.
     *
     * @param  array  $danhSachTp  Mang ten cac thanh pho can dem
     * @param  string|null  $loai  Loai tin (sale/rent/null)
     * @return array Mang [ten_tp => so_luong]
     */
    public function demTheoThanhPho(array $danhSachTp, ?string $loai = null): array
    {
        $sql = "SELECT tinh_thanh FROM du_an WHERE trang_thai = 'xuat_ban'";
        if ($loai === 'rent') {
            $sql .= " AND (loai_bat_dong_san LIKE '%cho thuê%')";
        } elseif ($loai === 'sale') {
            $sql .= " AND loai_bat_dong_san NOT LIKE '%cho thuê%'";
        }

        $this->db->query($sql);
        $danhSach = $this->db->resultSet();

        $ketQua = [];
        foreach ($danhSachTp as $tp) {
            $soLuong = 0;
            foreach ($danhSach as $item) {
                if ($item->tinh_thanh && mb_stripos($item->tinh_thanh, $tp) !== false) {
                    $soLuong++;
                }
            }
            $ketQua[$tp] = $soLuong;
        }

        return $ketQua;
    }

    /**
     * Tim tin dang theo slug (duong dan URL SEO).
     *
     * @param  string  $slug  Duong dan URL (vi du: 'can-ho-vinhomes-grand-park')
     * @return mixed Tin dang (object) kem thong tin nguoi dang, hoac false
     */
    public function layTheoSlug(string $slug): mixed
    {
        $this->db->query("SELECT p.*, c.ten AS ten_danh_muc, u.ten AS ten_nguoi_dung, u.dien_thoai, u.anh_dai_dien
                          FROM du_an p
                          JOIN danh_muc c ON p.ma_danh_muc = c.id
                          LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
                          WHERE p.duong_dan = :slug AND p.trang_thai = 'xuat_ban'");
        $this->db->bind(':slug', $slug);

        return $this->db->single();
    }

    /**
     * Lay danh sach anh cua mot tin dang, sap xep theo thu tu.
     *
     * @param  int  $maDuAn  ID tin dang
     * @return array Danh sach anh
     */
    public function layAnhDuAn(int $maDuAn): array
    {
        $this->db->query('SELECT * FROM hinh_anh_du_an WHERE ma_du_an = :ma_du_an ORDER BY thu_tu ASC');
        $this->db->bind(':ma_du_an', $maDuAn);

        return $this->db->resultSet();
    }

    /**
     * Tang luot xem cua tin dang len 1.
     * Dung ket hop kiem tra session de tranh gian lan.
     *
     * @param  int  $id  ID tin dang
     * @return bool True neu cap nhat thanh cong
     */
    public function tangLuotXem(int $id): bool
    {
        $this->db->query('UPDATE du_an SET luot_xem = luot_xem + 1 WHERE id = :id');
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    /**
     * Lay tong so luot xem cua tat ca tin dang cua mot nguoi dung.
     * Dung de tinh muc giam gia khi dang tin moi.
     *
     * @param  int  $userId  ID nguoi dung
     * @return int Tong luot xem
     */
    public function tongLuotXemTheoUser(int $userId): int
    {
        $this->db->query('SELECT SUM(luot_xem) AS tong FROM du_an WHERE ma_nguoi_dung = :uid');
        $this->db->bind(':uid', $userId);
        $row = $this->db->single();

        return (int) ($row->tong ?? 0);
    }

    /**
     * Dem so tin dang dang xuat ban (trang thai = xuat_ban).
     *
     * @return int So tin dang hoat dong
     */
    public function demDangHoatDong(): int
    {
        $this->db->query("SELECT COUNT(*) AS total FROM du_an WHERE trang_thai = 'xuat_ban'");
        $row = $this->db->single();

        return (int) ($row->total ?? 0);
    }

    // ==========================================
    // THÊM / SỬA TIN (USER)
    // ==========================================

    /**
     * Tao moi mot tin dang bat dong san.
     * Trang thai mac dinh la 'cho_duyet' - can Admin duyet.
     *
     * @param  array  $data  Du lieu tin dang
     * @return mixed ID (int) cua tin moi hoac false neu that bai
     */
    public function taoTinDang(array $data): mixed
    {
        $this->db->query("INSERT INTO du_an
            (tieu_de, duong_dan, mo_ta, gia, dien_tich, vi_tri, tinh_thanh, loai_bat_dong_san,
             anh_thu_nho, huong_nha, so_phong_ngu, so_phong_wc, link_video, vi_do, kinh_do,
             trang_thai, ma_nguoi_dung, ma_danh_muc, goi_vip, ngay_het_han_vip)
            VALUES
            (:tieu_de, :duong_dan, :mo_ta, :gia, :dien_tich, :vi_tri, :tinh_thanh, :loai_bat_dong_san,
             :anh_thu_nho, :huong_nha, :so_phong_ngu, :so_phong_wc, :link_video, :vi_do, :kinh_do,
             'cho_duyet', :ma_nguoi_dung, :ma_danh_muc, :goi_vip, :ngay_het_han_vip)");

        $this->db->bind(':tieu_de', $data['tieu_de']);
        $this->db->bind(':duong_dan', $data['duong_dan']);
        $this->db->bind(':mo_ta', $data['mo_ta'] ?? '');
        $this->db->bind(':gia', $data['gia'] ?? 0);
        $this->db->bind(':dien_tich', $data['dien_tich'] ?? 0);
        $this->db->bind(':vi_tri', $data['vi_tri'] ?? '');
        $this->db->bind(':tinh_thanh', $data['tinh_thanh'] ?? '');
        $this->db->bind(':loai_bat_dong_san', $data['loai_bat_dong_san']);
        $this->db->bind(':anh_thu_nho', $data['anh_thu_nho'] ?? '');
        $this->db->bind(':huong_nha', $data['huong_nha'] ?? '');
        $this->db->bind(':so_phong_ngu', (int) ($data['so_phong_ngu'] ?? 0));
        $this->db->bind(':so_phong_wc', (int) ($data['so_phong_wc'] ?? 0));
        $this->db->bind(':link_video', $data['link_video'] ?? '');
        $this->db->bind(':vi_do', $data['vi_do'] ?? null);
        $this->db->bind(':kinh_do', $data['kinh_do'] ?? null);
        $this->db->bind(':ma_nguoi_dung', $data['ma_nguoi_dung']);
        $this->db->bind(':ma_danh_muc', $data['ma_danh_muc']);
        $this->db->bind(':goi_vip', (int) ($data['goi_vip'] ?? 0));
        $this->db->bind(':ngay_het_han_vip', $data['ngay_het_han_vip'] ?? null);

        if ($this->db->execute()) {
            return (int) $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Them anh vao bang hinh_anh_du_an.
     *
     * @param  int  $maDuAn  ID tin dang
     * @param  string  $duongDanAnh  Ten file anh
     * @param  int  $thuTu  Vi tri thu tu hien thi
     * @return bool True neu them thanh cong
     */
    public function themAnh(int $maDuAn, string $duongDanAnh, int $thuTu = 0): bool
    {
        $this->db->query('INSERT INTO hinh_anh_du_an (ma_du_an, duong_dan_anh, thu_tu) 
                          VALUES (:ma_du_an, :duong_dan_anh, :thu_tu)');
        $this->db->bind(':ma_du_an', $maDuAn);
        $this->db->bind(':duong_dan_anh', $duongDanAnh);
        $this->db->bind(':thu_tu', $thuTu);

        return $this->db->execute();
    }

    /**
     * Xoa tat ca anh cua mot tin dang (dung khi cap nhat anh moi).
     *
     * @param  int  $maDuAn  ID tin dang
     * @return bool True neu xoa thanh cong
     */
    public function xoaAnhCu(int $maDuAn): bool
    {
        $this->db->query('DELETE FROM hinh_anh_du_an WHERE ma_du_an = :ma_du_an');
        $this->db->bind(':ma_du_an', $maDuAn);

        return $this->db->execute();
    }

    /**
     * Cap nhat thong tin co ban cua tin dang (nguoi dung tu sua).
     * Chi cho phep sua: tieu_de, gia, dien_tich, vi_tri, mo_ta, anh_thu_nho.
     *
     * @param  int  $id  ID tin dang
     * @param  int  $userId  ID nguoi dung (xac minh so huu)
     * @param  array  $data  Du lieu cap nhat
     * @return bool True neu cap nhat thanh cong
     */
    public function capNhatBoiUser(int $id, int $userId, array $data): bool
    {
        $sql = 'UPDATE du_an SET tieu_de = :tieu_de, gia = :gia, dien_tich = :dien_tich, vi_tri = :vi_tri, mo_ta = :mo_ta';
        if (! empty($data['anh_thu_nho'])) {
            $sql .= ', anh_thu_nho = :anh_thu_nho';
        }
        $sql .= ' WHERE id = :id AND ma_nguoi_dung = :uid';

        $this->db->query($sql);
        $this->db->bind(':tieu_de', $data['tieu_de']);
        $this->db->bind(':gia', $data['gia']);
        $this->db->bind(':dien_tich', $data['dien_tich']);
        $this->db->bind(':vi_tri', $data['vi_tri']);
        $this->db->bind(':mo_ta', $data['mo_ta']);
        if (! empty($data['anh_thu_nho'])) {
            $this->db->bind(':anh_thu_nho', $data['anh_thu_nho']);
        }
        $this->db->bind(':id', $id);
        $this->db->bind(':uid', $userId);

        return $this->db->execute();
    }

    // ==========================================
    // TRUY VẤN CỦA NGƯỜI DÙNG
    // ==========================================

    /**
     * Lay tat ca tin dang cua mot nguoi dung (ca da duyet lan chua duyet).
     *
     * @param  int  $userId  ID nguoi dung
     * @return array Danh sach tin dang
     */
    public function layTheoChuSoHuu(int $userId): array
    {
        $this->db->query('SELECT * FROM du_an WHERE ma_nguoi_dung = :uid ORDER BY ngay_tao DESC');
        $this->db->bind(':uid', $userId);

        return $this->db->resultSet();
    }

    /** Lay tin dang cua nguoi dung theo tung trang. */
    public function layTheoChuSoHuuPhanTrang(int $userId, int $limit, int $offset): array
    {
        $this->db->query('SELECT * FROM du_an
                          WHERE ma_nguoi_dung = :uid
                          ORDER BY ngay_tao DESC
                          LIMIT :limit OFFSET :offset');
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /** Dem tong so tin dang cua nguoi dung. */
    public function demTheoChuSoHuu(int $userId): int
    {
        $this->db->query('SELECT COUNT(*) AS total FROM du_an WHERE ma_nguoi_dung = :uid');
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);
        $row = $this->db->single();

        return (int) ($row->total ?? 0);
    }

    /**
     * Lay chi tin dang da xuat ban cua mot nguoi dung (hien thi trang tac gia).
     *
     * @param  int  $userId  ID nguoi dung
     * @return array Danh sach tin dang da xuat ban
     */
    public function layDaXuatBanCuaUser(int $userId): array
    {
        $this->db->query("SELECT * FROM du_an WHERE ma_nguoi_dung = :uid AND trang_thai = 'xuat_ban' ORDER BY ngay_tao DESC");
        $this->db->bind(':uid', $userId);

        return $this->db->resultSet();
    }

    // ==========================================
    // PHƯƠNG THỨC ADMIN
    // ==========================================

    /**
     * Lay tat ca tin dang kem thong tin nguoi dang (Admin xem toan bo).
     *
     * @return array Danh sach tat ca tin dang
     */
    public function layTatCaVoiNguoiDung(): array
    {
        $this->db->query('SELECT p.*, u.ten AS ten_nguoi_dung
                          FROM du_an p
                          LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
                          ORDER BY p.ngay_tao DESC');

        return $this->db->resultSet();
    }

    /** Lay danh sach tin dang Admin theo bo loc va phan trang. */
    public function layAdminPhanTrang(array $filters, int $limit, int $offset): array
    {
        [$whereSql, $bindings] = $this->taoDieuKienLocAdmin($filters);
        $this->db->query("SELECT p.*, u.ten AS ten_nguoi_dung
                          FROM du_an p
                          LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
                          {$whereSql}
                          ORDER BY p.ngay_tao DESC
                          LIMIT :limit OFFSET :offset");
        foreach ($bindings as $parameter => $value) {
            $this->db->bind($parameter, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /** Dem tong so tin dang khop bo loc cua Admin. */
    public function demAdminTheoBoLoc(array $filters): int
    {
        [$whereSql, $bindings] = $this->taoDieuKienLocAdmin($filters);
        $this->db->query("SELECT COUNT(*) AS total
                          FROM du_an p
                          LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
                          {$whereSql}");
        foreach ($bindings as $parameter => $value) {
            $this->db->bind($parameter, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $row = $this->db->single();

        return (int) ($row->total ?? 0);
    }

    /** Lay cac loai hinh dang co du lieu de hien thi trong bo loc. */
    public function layLoaiHinhAdmin(): array
    {
        $this->db->query("SELECT DISTINCT loai_bat_dong_san AS value
                          FROM du_an
                          WHERE loai_bat_dong_san IS NOT NULL AND loai_bat_dong_san <> ''
                          ORDER BY loai_bat_dong_san ASC");

        return array_map(static fn ($row) => (string) $row->value, $this->db->resultSet());
    }

    /** Tao menh de WHERE va cac tham so dung chung cho truy van Admin. */
    private function taoDieuKienLocAdmin(array $filters): array
    {
        $conditions = [];
        $bindings = [];

        if ($filters['keyword'] !== '') {
            $keyword = '%'.$filters['keyword'].'%';
            $conditions[] = '(p.tieu_de LIKE :keyword_title
                              OR u.ten LIKE :keyword_user
                              OR u.email LIKE :keyword_email'
                          .(ctype_digit($filters['keyword']) ? ' OR p.id = :project_id' : '').')';
            $bindings[':keyword_title'] = $keyword;
            $bindings[':keyword_user'] = $keyword;
            $bindings[':keyword_email'] = $keyword;
            if (ctype_digit($filters['keyword'])) {
                $bindings[':project_id'] = (int) $filters['keyword'];
            }
        }

        if ($filters['property_type'] !== '') {
            $conditions[] = 'p.loai_bat_dong_san = :property_type';
            $bindings[':property_type'] = $filters['property_type'];
        }

        if ($filters['status'] !== '') {
            $conditions[] = 'p.trang_thai = :status';
            $bindings[':status'] = $filters['status'];
        }

        return [$conditions ? 'WHERE '.implode(' AND ', $conditions) : '', $bindings];
    }

    /**
     * Dem so tin dang dang cho duyet.
     * Dung de hien thi badge thong bao tren menu Admin.
     *
     * @return int So tin cho duyet
     */
    public function demChoDuyet(): int
    {
        $this->db->query("SELECT COUNT(*) AS total FROM du_an WHERE trang_thai = 'cho_duyet'");
        $row = $this->db->single();

        return (int) ($row->total ?? 0);
    }

    /**
     * Duyet tin dang: chuyen trang_thai tu 'cho_duyet' sang 'xuat_ban'.
     *
     * @param  int  $id  ID tin dang can duyet
     * @return bool True neu duyet thanh cong
     */
    public function duyetTin(int $id): bool
    {
        $this->db->query("UPDATE du_an SET trang_thai = 'xuat_ban' WHERE id = :id");
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    // ==========================================
    // THỐNG KÊ & BẢNG ĐIỀU KHIỂN (DASHBOARD)
    // ==========================================

    /**
     * Thong ke so luong tin dang theo danh muc (cho bieu do Doughnut)
     */
    public function thongKeTheoDanhMuc(): array
    {
        $this->db->query('SELECT c.ten as danh_muc, COUNT(p.id) as so_luong
                          FROM du_an p
                          JOIN danh_muc c ON p.ma_danh_muc = c.id
                          GROUP BY p.ma_danh_muc, c.ten');

        return $this->db->resultSet();
    }

    /**
     * Thong ke so luong tin dang va du an moi theo 6 thang gan nhat (cho bieu do Line)
     */
    public function thongKeTheoThang(): array
    {
        $this->db->query("SELECT DATE_FORMAT(p.ngay_tao, '%m/%Y') as thang, 
                                 SUM(CASE WHEN c.ten LIKE '%Dự án%' THEN 1 ELSE 0 END) as du_an_moi,
                                 SUM(CASE WHEN c.ten NOT LIKE '%Dự án%' THEN 1 ELSE 0 END) as tin_dang_moi
                          FROM du_an p
                          LEFT JOIN danh_muc c ON p.ma_danh_muc = c.id
                          WHERE p.ngay_tao >= DATE_SUB(NOW(), INTERVAL 5 MONTH)
                          GROUP BY DATE_FORMAT(p.ngay_tao, '%m/%Y'), DATE_FORMAT(p.ngay_tao, '%Y-%m')
                          ORDER BY DATE_FORMAT(p.ngay_tao, '%Y-%m') ASC");

        return $this->db->resultSet();
    }

    /**
     * Lay danh sach cac tin dang cho duyet moi nhat
     */
    public function layTinChoDuyetMoiNhat(int $limit = 5): array
    {
        $this->db->query("SELECT p.*, c.ten AS ten_danh_muc, u.ten AS ten_nguoi_dung, u.anh_dai_dien 
                          FROM du_an p
                          JOIN danh_muc c ON p.ma_danh_muc = c.id
                          LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
                          WHERE p.trang_thai = 'cho_duyet'
                          ORDER BY p.ngay_tao DESC
                          LIMIT :limit");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Từ chối tin đăng: chuyển trạng thái sang 'nhap' để người dùng có thể chỉnh sửa lại.
     *
     * @param  int  $id  ID tin đăng cần từ chối
     * @return bool True nếu cập nhật thành công
     */
    public function tuChoiTin(int $id, string $reason = ''): bool
    {
        $this->db->query("UPDATE du_an SET trang_thai = 'tu_choi', ly_do_tu_choi = :reason WHERE id = :id");
        $this->db->bind(':reason', trim($reason));
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    /**
     * Gia hạn VIP cho tin đăng bằng cách cập nhật ngày hết hạn mới.
     *
     * @param  int  $id  ID tin đăng
     * @param  string  $newExpiryDate  Ngày hết hạn mới (Y-m-d H:i:s)
     * @return bool True nếu cập nhật thành công
     */
    public function giaHanVip(int $id, string $newExpiryDate): bool
    {
        $this->db->query('UPDATE du_an SET ngay_het_han_vip = :ngay_het_han_vip WHERE id = :id');
        $this->db->bind(':ngay_het_han_vip', $newExpiryDate);
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    // Alias tuong thich nguoc
    public function getFeatured(int $limit = 6): array
    {
        return $this->layNoiBat($limit);
    }

    public function getTopSeo(int $limit = 4): array
    {
        return $this->layTopSeo($limit);
    }

    public function getLatest(int $limit = 6): array
    {
        return $this->layMoiNhat($limit);
    }

    public function getProjects(?string $type = null, int $limit = 20): array
    {
        return $this->layDanhSach($type, $limit);
    }

    public function getCountsByCities(array $cities, ?string $type = null): array
    {
        return $this->demTheoThanhPho($cities, $type);
    }

    public function getBySlug(string $slug): mixed
    {
        return $this->layTheoSlug($slug);
    }

    public function getImages(int $id): array
    {
        return $this->layAnhDuAn($id);
    }

    public function incrementView(int $id): bool
    {
        return $this->tangLuotXem($id);
    }

    public function getTotalViews(int $uid): int
    {
        return $this->tongLuotXemTheoUser($uid);
    }

    public function countActive(): int
    {
        return $this->demDangHoatDong();
    }

    public function createProject(array $data): int|false
    {
        return $this->taoTinDang($data);
    }

    public function getAllWithUser(): array
    {
        return $this->layTatCaVoiNguoiDung();
    }

    public function getPendingCount(): int
    {
        return $this->demChoDuyet();
    }

    public function approveById(int $id): bool
    {
        return $this->duyetTin($id);
    }

    public function getProjectsByUser(int $uid): array
    {
        return $this->layDaXuatBanCuaUser($uid);
    }

    public function getAllProjectsByUser(int $uid): array
    {
        return $this->layTheoChuSoHuu($uid);
    }

    public function updateProjectByUser(int $id, int $uid, array $data): bool
    {
        return $this->capNhatBoiUser($id, $uid, $data);
    }

    /**
     * Cập nhật trạng thái tự động gia hạn VIP.
     *
     * @param  int  $id  ID tin đăng
     * @param  int  $status  1 = Bật, 0 = Tắt
     * @return bool True nếu thành công
     */
    public function setAutoRenewVip(int $id, int $status): bool
    {
        $this->db->query('UPDATE du_an SET tu_dong_gia_han_vip = :status WHERE id = :id');
        $this->db->bind(':status', $status, PDO::PARAM_INT);
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Lấy danh sách các tin đăng VIP đã hết hạn cần được tự động gia hạn.
     *
     * @return array Danh sách tin đăng
     */
    public function layTinVipHetHanCanGiaHan(): array
    {
        $this->db->query('SELECT * FROM du_an 
                          WHERE goi_vip > 0 
                            AND tu_dong_gia_han_vip = 1 
                            AND ngay_het_han_vip IS NOT NULL 
                            AND ngay_het_han_vip <= NOW()');

        return $this->db->resultSet();
    }

    /**
     * Đếm số lượng tin mua bán (đã xuất bản).
     */
    public function demMuaBan(): int
    {
        $this->db->query("SELECT COUNT(*) as cnt FROM du_an WHERE trang_thai = 'xuat_ban' AND loai_bat_dong_san LIKE '%bán%'");
        $row = $this->db->single();

        return (int) ($row->cnt ?? 0);
    }

    /**
     * Đếm số lượng tin cho thuê (đã xuất bản).
     */
    public function demChoThue(): int
    {
        $this->db->query("SELECT COUNT(*) as cnt FROM du_an WHERE trang_thai = 'xuat_ban' AND loai_bat_dong_san LIKE '%thuê%'");
        $row = $this->db->single();

        return (int) ($row->cnt ?? 0);
    }

    /**
     * Lấy thống kê số tin đăng đã xuất bản theo từng tỉnh thành.
     */
    public function layThongKeTheoTinhThanh(): array
    {
        $this->db->query("SELECT tinh_thanh, COUNT(*) as cnt FROM du_an WHERE trang_thai = 'xuat_ban' GROUP BY tinh_thanh");

        return $this->db->resultSet();
    }

    /**
     * Lấy danh sách ảnh banner từ các tin đăng đẹp nhất.
     */
    public function layBannerImages(int $limit = 7): array
    {
        $this->db->query("SELECT anh_thu_nho, tieu_de, duong_dan FROM du_an
                          WHERE trang_thai = 'xuat_ban'
                          AND anh_thu_nho IS NOT NULL
                          AND anh_thu_nho != ''
                          ORDER BY luot_xem DESC, noi_bat DESC
                          LIMIT :limit");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Lấy tổng lượt xem của tất cả các tin đăng.
     */
    public function tongLuotXem(): int
    {
        $this->db->query('SELECT COALESCE(SUM(luot_xem), 0) as cnt FROM du_an');
        $row = $this->db->single();

        return (int) ($row->cnt ?? 0);
    }

    /**
     * Đếm số tin đăng mới đăng trong tuần qua.
     */
    public function demTinMoiTrongTuan(): int
    {
        $this->db->query("SELECT COUNT(*) as cnt FROM du_an WHERE trang_thai = 'xuat_ban' AND ngay_tao >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $row = $this->db->single();

        return (int) ($row->cnt ?? 0);
    }

    /** Gom các chỉ số trang chủ vào một lần truy vấn thay vì quét bảng nhiều lần. */
    public function thongKeTrangChu(): object
    {
        $this->db->query("
            SELECT
                SUM(trang_thai = 'xuat_ban') AS tong_tin_dang,
                SUM(trang_thai = 'xuat_ban' AND loai_bat_dong_san LIKE '%bán%') AS tin_mua_ban,
                SUM(trang_thai = 'xuat_ban' AND loai_bat_dong_san LIKE '%thuê%') AS tin_cho_thue,
                COALESCE(SUM(luot_xem), 0) AS tong_luot_xem,
                SUM(trang_thai = 'xuat_ban' AND ngay_tao >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS tin_moi_tuan
            FROM du_an
        ");

        return $this->db->single() ?: (object) [];
    }

    // ==========================================
    // MODULE CHI TIẾT BẤT ĐỘNG SẢN
    // ==========================================

    /**
     * Lấy chi tiết đầy đủ tin đăng theo slug.
     * JOIN thêm: thông tin người đăng, danh mục, số tin người đăng.
     *
     * @param  string  $slug  Đường dẫn URL
     * @return mixed Object chi tiết hoặc false
     */
    public function layThongTinChiTiet(string $slug): mixed
    {
        $this->db->query(
            "SELECT p.*,
                    c.ten          AS ten_danh_muc,
                    c.duong_dan    AS slug_danh_muc,
                    u.ten          AS ten_nguoi_dung,
                    u.dien_thoai,
                    u.email        AS email_nguoi_dung,
                    u.anh_dai_dien,
                    u.ngay_tao     AS ngay_tham_gia,
                    u.trang_thai   AS trang_thai_nguoi_dung,
                    (SELECT COUNT(*) FROM du_an p2
                     WHERE p2.ma_nguoi_dung = p.ma_nguoi_dung
                       AND p2.trang_thai = 'xuat_ban')  AS so_tin_dang,
                    (SELECT COUNT(*) FROM yeu_thich yt
                     WHERE yt.ma_du_an = p.id)          AS tong_luot_luu
             FROM du_an p
             JOIN danh_muc c ON p.ma_danh_muc = c.id
             LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
             WHERE p.duong_dan = :slug
               AND p.trang_thai = 'xuat_ban'"
        );
        $this->db->bind(':slug', $slug);

        return $this->db->single();
    }

    /**
     * Lấy 12 tin liên quan theo khu vực, loại BĐS, khoảng giá.
     * Loại bỏ tin hiện tại. Ưu tiên: cùng tỉnh > cùng loại BĐS > gần giá.
     *
     * @param  int  $excludeId  ID tin đang xem (loại trừ)
     * @param  string  $loaiBds  Loại bất động sản
     * @param  string  $tinhThanh  Tỉnh / thành phố
     * @param  string  $gia  Giá (string, dùng để so sánh tương đối)
     * @param  int  $limit  Số tin cần lấy
     * @return array Danh sách tin liên quan
     */
    public function layTinLienQuan(
        int $excludeId,
        string $loaiBds,
        string $tinhThanh,
        string $gia = '',
        int $limit = 12
    ): array {
        $this->db->query(
            "SELECT p.*, c.ten AS ten_danh_muc,
                    u.ten AS ten_nguoi_dung, u.anh_dai_dien,
                    CASE WHEN (p.ngay_het_han_vip IS NULL OR p.ngay_het_han_vip > NOW())
                         THEN p.goi_vip ELSE 0 END AS active_vip,
                    -- Điểm liên quan: cùng tỉnh = 10, cùng loại = 5
                    ((p.tinh_thanh = :tinh) * 10 + (p.loai_bat_dong_san = :loai) * 5) AS relevance_score
             FROM du_an p
             JOIN danh_muc c ON p.ma_danh_muc = c.id
             LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
             WHERE p.trang_thai = 'xuat_ban'
               AND p.id != :exclude_id
               AND (p.tinh_thanh = :tinh2 OR p.loai_bat_dong_san = :loai2)
             ORDER BY relevance_score DESC,
                      CASE WHEN (p.ngay_het_han_vip IS NULL OR p.ngay_het_han_vip > NOW())
                           THEN p.goi_vip ELSE 0 END DESC,
                      p.ngay_tao DESC
             LIMIT :lim"
        );
        $this->db->bind(':exclude_id', $excludeId, PDO::PARAM_INT);
        $this->db->bind(':tinh', $tinhThanh);
        $this->db->bind(':tinh2', $tinhThanh);
        $this->db->bind(':loai', $loaiBds);
        $this->db->bind(':loai2', $loaiBds);
        $this->db->bind(':lim', $limit, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Lấy các tin đăng khác của cùng người đăng (trừ tin hiện tại).
     *
     * @param  int  $maNguoiDung  ID người đăng
     * @param  int  $excludeId  ID tin hiện tại (loại trừ)
     * @param  int  $limit  Số tin cần lấy
     */
    public function layTinCungNguoiDang(int $maNguoiDung, int $excludeId, int $limit = 6): array
    {
        $this->db->query(
            "SELECT p.*, c.ten AS ten_danh_muc
             FROM du_an p
             JOIN danh_muc c ON p.ma_danh_muc = c.id
             WHERE p.ma_nguoi_dung = :uid
               AND p.trang_thai = 'xuat_ban'
               AND p.id != :exclude_id
             ORDER BY p.ngay_tao DESC
             LIMIT :lim"
        );
        $this->db->bind(':uid', $maNguoiDung, PDO::PARAM_INT);
        $this->db->bind(':exclude_id', $excludeId, PDO::PARAM_INT);
        $this->db->bind(':lim', $limit, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Tăng lượt chia sẻ.
     *
     * @param  int  $id  ID tin đăng
     */
    public function tangLuotChiaSe(int $id): bool
    {
        $this->db->query('UPDATE du_an SET luot_chia_se = luot_chia_se + 1 WHERE id = :id');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Đếm lượt lưu (yêu thích) của một tin đăng.
     *
     * @param  int  $id  ID tin đăng
     */
    public function demLuotLuu(int $id): int
    {
        $this->db->query('SELECT COUNT(*) AS total FROM yeu_thich WHERE ma_du_an = :id');
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $row = $this->db->single();

        return (int) ($row->total ?? 0);
    }
}
