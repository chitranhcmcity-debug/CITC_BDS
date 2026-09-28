<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class DuAn extends Model
{
    protected $table = 'du_an';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ma_danh_muc',
        'ma_nguoi_dung',
        'tieu_de',
        'duong_dan',
        'mo_ta',
        'noi_dung',
        'gia',
        'dien_tich',
        'vi_tri',
        'ban_do',
        'kinh_do',
        'vi_do',
        'tinh_thanh',
        'quan_huyen',
        'phuong_xa',
        'phap_ly',
        'mat_tien',
        'so_phong_ngu',
        'so_phong_wc',
        'huong_nha',
        'loai_bat_dong_san',
        'chinh_chu',
        'link_video',
        'link_tour_360',
        'luot_xem',
        'luot_click_sdt',
        'tai_lieu_pdf',
        'anh_thu_nho',
        'tien_ich',
        'trang_thai',
        'ly_do_tu_choi',
        'noi_bat',
        'goi_vip',
        'ngay_het_han_vip',
        'ngay_het_han',
        'tu_dong_gia_han_vip',
        'ngay_lam_moi',
    ];

    protected $casts = [
        'chinh_chu' => 'boolean',
        'noi_bat' => 'boolean',
        'tu_dong_gia_han_vip' => 'boolean',
        'goi_vip' => 'integer',
        'luot_xem' => 'integer',
        'luot_click_sdt' => 'integer',
        'ngay_het_han_vip' => 'datetime',
        'ngay_het_han' => 'datetime',
        'ngay_lam_moi' => 'datetime',
    ];

    /* ── Relationships ── */

    public function danhMuc(): BelongsTo
    {
        return $this->belongsTo(DanhMuc::class, 'ma_danh_muc');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_dung');
    }

    public function hinhAnhDuAn(): HasMany
    {
        return $this->hasMany(HinhAnhDuAn::class, 'ma_du_an');
    }

    public function videoDuAn(): HasMany
    {
        return $this->hasMany(VideoDuAn::class, 'ma_du_an');
    }

    public function lichHen(): HasMany
    {
        return $this->hasMany(LichHen::class, 'ma_du_an');
    }

    public function yeuThich(): BelongsToMany
    {
        return $this->belongsToMany(NguoiDung::class, 'yeu_thich', 'ma_du_an', 'ma_nguoi_dung')
            ->withPivot('ngay_tao');
    }

    public function danhGia(): HasMany
    {
        return $this->hasMany(DanhGia::class, 'ma_du_an');
    }

    /* ── Original Model Compatibility Methods ── */

    public function layNoiBat(int $limit = 6): array
    {
        return DB::select("
            SELECT p.*, c.ten AS ten_danh_muc, u.anh_dai_dien, u.ten AS ten_nguoi_dung,
                   CASE WHEN (p.ngay_het_han_vip IS NULL OR p.ngay_het_han_vip > NOW()) 
                        THEN p.goi_vip ELSE 0 END AS active_vip
            FROM du_an p
            JOIN danh_muc c ON p.ma_danh_muc = c.id
            LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
            WHERE p.trang_thai = 'xuat_ban'
              AND (p.ngay_het_han_vip IS NULL OR p.ngay_het_han_vip > NOW())
            ORDER BY p.goi_vip DESC, p.ngay_tao DESC
            LIMIT :limit
        ", ['limit' => $limit]);
    }

    public function layTopSeo(int $limit = 4): array
    {
        return DB::select("
            SELECT p.*, c.ten AS ten_danh_muc,
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
            LIMIT :limit
        ", ['limit' => $limit]);
    }

    public function layMoiNhat(int $limit = 6): array
    {
        return DB::select("
            SELECT p.*, c.ten AS ten_danh_muc,
                   u.ten AS ten_nguoi_dung, u.dien_thoai, u.anh_dai_dien,
                   CASE WHEN (p.ngay_het_han_vip IS NULL OR p.ngay_het_han_vip > NOW())
                        THEN p.goi_vip ELSE 0 END AS active_vip
            FROM du_an p
            JOIN danh_muc c ON p.ma_danh_muc = c.id
            LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
            WHERE p.trang_thai = 'xuat_ban'
            ORDER BY p.ngay_tao DESC
            LIMIT :limit
        ", ['limit' => $limit]);
    }

    public function layDanhSach(?string $loai = null, int $limit = 20): array
    {
        return $this->locNangCao(['loai' => $loai, 'limit' => $limit]);
    }

    public function locNangCao(array $filters = []): array
    {
        $loai = in_array($filters['loai'] ?? '', ['sale', 'rent'], true) ? $filters['loai'] : null;
        $giaMin = isset($filters['gia_min']) ? (int) $filters['gia_min'] : null;
        $giaMax = isset($filters['gia_max']) ? (int) $filters['gia_max'] : null;
        $dtMin = isset($filters['dt_min']) ? (float) $filters['dt_min'] : null;
        $dtMax = isset($filters['dt_max']) ? (float) $filters['dt_max'] : null;
        $phongNgu = isset($filters['phong_ngu']) ? (int) $filters['phong_ngu'] : null;
        $phongNguMin = isset($filters['phong_ngu_min']) ? (int) $filters['phong_ngu_min'] : null;
        $limit = min((int) ($filters['limit'] ?? 20), 100);
        $trang = max(1, (int) ($filters['trang'] ?? 1));
        $offset = ($trang - 1) * $limit;

        $query = "SELECT p.*, c.ten AS ten_danh_muc,
                         CASE WHEN (p.ngay_het_han_vip IS NULL OR p.ngay_het_han_vip > NOW())
                              THEN p.goi_vip ELSE 0 END AS active_vip
                  FROM du_an p
                  JOIN danh_muc c ON p.ma_danh_muc = c.id
                  WHERE p.trang_thai = 'xuat_ban'";

        $params = [];

        if ($loai) {
            if ($loai === 'sale') {
                $query .= ' AND p.loai_bat_dong_san LIKE :loai';
                $params['loai'] = '%bán%';
            } else {
                $query .= ' AND p.loai_bat_dong_san LIKE :loai';
                $params['loai'] = '%thuê%';
            }
        }

        if (! empty($filters['tinh'])) {
            $query .= ' AND p.tinh_thanh = :tinh';
            $params['tinh'] = $filters['tinh'];
        }

        if (! empty($filters['huong'])) {
            $query .= ' AND p.huong_nha = :huong';
            $params['huong'] = $filters['huong'];
        }

        if ($phongNgu !== null) {
            $query .= ' AND p.so_phong_ngu = :phongNgu';
            $params['phongNgu'] = $phongNgu;
        }

        if ($phongNguMin !== null) {
            $query .= ' AND p.so_phong_ngu >= :phongNguMin';
            $params['phongNguMin'] = $phongNguMin;
        }

        if (! empty($filters['tu_khoa'])) {
            $query .= ' AND (p.tieu_de LIKE :tuKhoa OR p.mo_ta LIKE :tuKhoa2 OR p.vi_tri LIKE :tuKhoa3)';
            $params['tuKhoa'] = '%'.$filters['tu_khoa'].'%';
            $params['tuKhoa2'] = '%'.$filters['tu_khoa'].'%';
            $params['tuKhoa3'] = '%'.$filters['tu_khoa'].'%';
        }

        // Logic comparison for numerical limits
        // (Note: since columns like 'gia' and 'dien_tich' might contain text in DB like "3 tỷ" or "80 m2",
        // original code uses specific extraction or assumes they are castable/numeric.
        // We replicate original query structure or casting here)
        if ($giaMin !== null) {
            $query .= ' AND CAST(p.gia AS UNSIGNED) >= :giaMin';
            $params['giaMin'] = $giaMin;
        }
        if ($giaMax !== null) {
            $query .= ' AND CAST(p.gia AS UNSIGNED) <= :giaMax';
            $params['giaMax'] = $giaMax;
        }
        if ($dtMin !== null) {
            $query .= ' AND CAST(p.dien_tich AS DECIMAL) >= :dtMin';
            $params['dtMin'] = $dtMin;
        }
        if ($dtMax !== null) {
            $query .= ' AND CAST(p.dien_tich AS DECIMAL) <= :dtMax';
            $params['dtMax'] = $dtMax;
        }

        $query .= ' ORDER BY active_vip DESC, p.ngay_tao DESC LIMIT :limit OFFSET :offset';
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        return DB::select($query, $params);
    }

    public function demTongLocNangCao(array $filters = []): int
    {
        $loai = in_array($filters['loai'] ?? '', ['sale', 'rent'], true) ? $filters['loai'] : null;
        $giaMin = isset($filters['gia_min']) ? (int) $filters['gia_min'] : null;
        $giaMax = isset($filters['gia_max']) ? (int) $filters['gia_max'] : null;
        $dtMin = isset($filters['dt_min']) ? (float) $filters['dt_min'] : null;
        $dtMax = isset($filters['dt_max']) ? (float) $filters['dt_max'] : null;
        $phongNgu = isset($filters['phong_ngu']) ? (int) $filters['phong_ngu'] : null;
        $phongNguMin = isset($filters['phong_ngu_min']) ? (int) $filters['phong_ngu_min'] : null;

        $query = "SELECT COUNT(*) as total
                  FROM du_an p
                  JOIN danh_muc c ON p.ma_danh_muc = c.id
                  WHERE p.trang_thai = 'xuat_ban'";

        $params = [];

        if ($loai) {
            if ($loai === 'sale') {
                $query .= ' AND p.loai_bat_dong_san LIKE :loai';
                $params['loai'] = '%bán%';
            } else {
                $query .= ' AND p.loai_bat_dong_san LIKE :loai';
                $params['loai'] = '%thuê%';
            }
        }

        if (! empty($filters['tinh'])) {
            $query .= ' AND p.tinh_thanh = :tinh';
            $params['tinh'] = $filters['tinh'];
        }

        if (! empty($filters['huong'])) {
            $query .= ' AND p.huong_nha = :huong';
            $params['huong'] = $filters['huong'];
        }

        if ($phongNgu !== null) {
            $query .= ' AND p.so_phong_ngu = :phongNgu';
            $params['phongNgu'] = $phongNgu;
        }

        if ($phongNguMin !== null) {
            $query .= ' AND p.so_phong_ngu >= :phongNguMin';
            $params['phongNguMin'] = $phongNguMin;
        }

        if (! empty($filters['tu_khoa'])) {
            $query .= ' AND (p.tieu_de LIKE :tuKhoa OR p.mo_ta LIKE :tuKhoa2 OR p.vi_tri LIKE :tuKhoa3)';
            $params['tuKhoa'] = '%'.$filters['tu_khoa'].'%';
            $params['tuKhoa2'] = '%'.$filters['tu_khoa'].'%';
            $params['tuKhoa3'] = '%'.$filters['tu_khoa'].'%';
        }

        if ($giaMin !== null) {
            $query .= ' AND CAST(p.gia AS UNSIGNED) >= :giaMin';
            $params['giaMin'] = $giaMin;
        }
        if ($giaMax !== null) {
            $query .= ' AND CAST(p.gia AS UNSIGNED) <= :giaMax';
            $params['giaMax'] = $giaMax;
        }
        if ($dtMin !== null) {
            $query .= ' AND CAST(p.dien_tich AS DECIMAL) >= :dtMin';
            $params['dtMin'] = $dtMin;
        }
        if ($dtMax !== null) {
            $query .= ' AND CAST(p.dien_tich AS DECIMAL) <= :dtMax';
            $params['dtMax'] = $dtMax;
        }

        $res = DB::select($query, $params);

        return (int) ($res[0]->total ?? 0);
    }

    public function demTheoThanhPho(array $danhSachTp, ?string $loai = null): array
    {
        $res = [];
        foreach ($danhSachTp as $tp) {
            $query = "SELECT COUNT(*) as total FROM du_an WHERE trang_thai = 'xuat_ban' AND tinh_thanh LIKE :tp";
            $params = ['tp' => '%'.$tp.'%'];
            if ($loai) {
                $query .= ' AND loai_bat_dong_san LIKE :loai';
                $params['loai'] = $loai === 'sale' ? '%bán%' : '%thuê%';
            }
            $count = DB::select($query, $params);
            $res[$tp] = (int) ($count[0]->total ?? 0);
        }

        return $res;
    }

    public function layTheoSlug(string $slug): mixed
    {
        $res = DB::select('SELECT * FROM du_an WHERE duong_dan = ? LIMIT 1', [$slug]);

        return $res ? $res[0] : null;
    }

    public function layAnhDuAn(int $maDuAn): array
    {
        return DB::select('SELECT * FROM hinh_anh_du_an WHERE ma_du_an = ? ORDER BY thu_tu ASC', [$maDuAn]);
    }

    public function tangLuotXem(int $id): bool
    {
        return DB::update('UPDATE du_an SET luot_xem = luot_xem + 1 WHERE id = ?', [$id]) > 0;
    }

    public function tongLuotXemTheoUser(int $userId): int
    {
        $res = DB::select('SELECT SUM(luot_xem) as total FROM du_an WHERE ma_nguoi_dung = ?', [$userId]);

        return (int) ($res[0]->total ?? 0);
    }

    public function demDangHoatDong(): int
    {
        $res = DB::select("SELECT COUNT(*) as total FROM du_an WHERE trang_thai = 'xuat_ban'");

        return (int) ($res[0]->total ?? 0);
    }

    public function taoTinDang(array $data): mixed
    {
        $id = DB::table('du_an')->insertGetId($data);

        return $id;
    }

    public function themAnh(int $maDuAn, string $duongDanAnh, int $thuTu = 0): bool
    {
        return DB::table('hinh_anh_du_an')->insert([
            'ma_du_an' => $maDuAn,
            'duong_dan_anh' => $duongDanAnh,
            'thu_tu' => $thuTu,
        ]);
    }

    public function xoaAnhCu(int $maDuAn): bool
    {
        return DB::table('hinh_anh_du_an')->where('ma_du_an', $maDuAn)->delete() >= 0;
    }

    public function capNhatBoiUser(int $id, int $userId, array $data): bool
    {
        return DB::table('du_an')->where('id', $id)->where('ma_nguoi_dung', $userId)->update($data) >= 0;
    }

    public function layTheoChuSoHuu(int $userId): array
    {
        return DB::select('SELECT * FROM du_an WHERE ma_nguoi_dung = ? ORDER BY ngay_tao DESC', [$userId]);
    }

    public function layTheoChuSoHuuPhanTrang(int $userId, int $limit, int $offset): array
    {
        return DB::select('SELECT * FROM du_an WHERE ma_nguoi_dung = :uid ORDER BY ngay_tao DESC LIMIT :limit OFFSET :offset', [
            'uid' => $userId,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function demTheoChuSoHuu(int $userId): int
    {
        $res = DB::select('SELECT COUNT(*) as total FROM du_an WHERE ma_nguoi_dung = ?', [$userId]);

        return (int) ($res[0]->total ?? 0);
    }

    public function layDaXuatBanCuaUser(int $userId): array
    {
        return DB::select("SELECT * FROM du_an WHERE ma_nguoi_dung = ? AND trang_thai = 'xuat_ban' ORDER BY ngay_tao DESC", [$userId]);
    }

    public function layTatCaVoiNguoiDung(): array
    {
        return DB::select('
            SELECT p.*, u.ten AS ten_nguoi_dung, u.email
            FROM du_an p
            LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
            ORDER BY p.ngay_tao DESC
        ');
    }

    public function layAdminPhanTrang(array $filters, int $limit, int $offset): array
    {
        $query = 'SELECT p.*, c.ten AS ten_danh_muc, u.ten AS ten_nguoi_dung
                  FROM du_an p
                  JOIN danh_muc c ON p.ma_danh_muc = c.id
                  LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
                  WHERE 1=1';
        $params = [];

        if (! empty($filters['trang_thai'])) {
            $query .= ' AND p.trang_thai = :status';
            $params['status'] = $filters['trang_thai'];
        }
        if (! empty($filters['goi_vip'])) {
            $query .= ' AND p.goi_vip = :vip';
            $params['vip'] = $filters['goi_vip'];
        }
        if (! empty($filters['tu_khoa'])) {
            $query .= ' AND (p.tieu_de LIKE :tuKhoa OR u.ten LIKE :tuKhoa2 OR u.email LIKE :tuKhoa3)';
            $params['tuKhoa'] = '%'.$filters['tu_khoa'].'%';
            $params['tuKhoa2'] = '%'.$filters['tu_khoa'].'%';
            $params['tuKhoa3'] = '%'.$filters['tu_khoa'].'%';
        }

        $query .= ' ORDER BY p.ngay_tao DESC LIMIT :limit OFFSET :offset';
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        return DB::select($query, $params);
    }

    public function demAdminTheoBoLoc(array $filters): int
    {
        $query = 'SELECT COUNT(*) as total
                  FROM du_an p
                  LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
                  WHERE 1=1';
        $params = [];

        if (! empty($filters['trang_thai'])) {
            $query .= ' AND p.trang_thai = :status';
            $params['status'] = $filters['trang_thai'];
        }
        if (! empty($filters['goi_vip'])) {
            $query .= ' AND p.goi_vip = :vip';
            $params['vip'] = $filters['goi_vip'];
        }
        if (! empty($filters['tu_khoa'])) {
            $query .= ' AND (p.tieu_de LIKE :tuKhoa OR u.ten LIKE :tuKhoa2 OR u.email LIKE :tuKhoa3)';
            $params['tuKhoa'] = '%'.$filters['tu_khoa'].'%';
            $params['tuKhoa2'] = '%'.$filters['tu_khoa'].'%';
            $params['tuKhoa3'] = '%'.$filters['tu_khoa'].'%';
        }

        $res = DB::select($query, $params);

        return (int) ($res[0]->total ?? 0);
    }

    public function layLoaiHinhAdmin(): array
    {
        return DB::select('SELECT loai_bat_dong_san, COUNT(*) as cnt FROM du_an GROUP BY loai_bat_dong_san');
    }

    public function demChoDuyet(): int
    {
        $res = DB::select("SELECT COUNT(*) as total FROM du_an WHERE trang_thai = 'cho_duyet'");

        return (int) ($res[0]->total ?? 0);
    }

    public function duyetTin(int $id): bool
    {
        return DB::update("UPDATE du_an SET trang_thai = 'xuat_ban' WHERE id = ?", [$id]) > 0;
    }

    public function thongKeTheoDanhMuc(): array
    {
        return DB::select('
            SELECT c.ten, COUNT(p.id) as cnt
            FROM danh_muc c
            LEFT JOIN du_an p ON p.ma_danh_muc = c.id
            GROUP BY c.id, c.ten
        ');
    }

    public function thongKeTheoThang(): array
    {
        return DB::select("
            SELECT DATE_FORMAT(ngay_tao, '%m/%Y') as period, COUNT(*) as cnt
            FROM du_an
            WHERE ngay_tao >= DATE_SUB(NOW(), INTERVAL 5 MONTH)
            GROUP BY DATE_FORMAT(ngay_tao, '%m/%Y'), DATE_FORMAT(ngay_tao, '%Y-%m')
            ORDER BY DATE_FORMAT(ngay_tao, '%Y-%m') ASC
        ");
    }

    public function layTinChoDuyetMoiNhat(int $limit = 5): array
    {
        return DB::select("
            SELECT p.*, c.ten AS ten_danh_muc, u.ten AS ten_nguoi_dung
            FROM du_an p
            JOIN danh_muc c ON p.ma_danh_muc = c.id
            LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
            WHERE p.trang_thai = 'cho_duyet'
            ORDER BY p.ngay_tao DESC
            LIMIT :limit
        ", ['limit' => $limit]);
    }

    public function tuChoiTin(int $id, string $reason = ''): bool
    {
        return DB::update("UPDATE du_an SET trang_thai = 'tu_choi', ly_do_tu_choi = ? WHERE id = ?", [$reason, $id]) > 0;
    }

    public function giaHanVip(int $id, string $newExpiryDate): bool
    {
        return DB::update('UPDATE du_an SET ngay_het_han_vip = ? WHERE id = ?', [$newExpiryDate, $id]) > 0;
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

    public function setAutoRenewVip(int $id, int $status): bool
    {
        return DB::update('UPDATE du_an SET tu_dong_gia_han_vip = ? WHERE id = ?', [$status, $id]) > 0;
    }

    public function layTinVipHetHanCanGiaHan(): array
    {
        return DB::select('SELECT * FROM du_an 
                           WHERE goi_vip > 0 
                             AND tu_dong_gia_han_vip = 1 
                             AND ngay_het_han_vip IS NOT NULL 
                             AND ngay_het_han_vip <= NOW()');
    }

    public function demMuaBan(): int
    {
        $res = DB::select("SELECT COUNT(*) as cnt FROM du_an WHERE trang_thai = 'xuat_ban' AND loai_bat_dong_san LIKE '%bán%'");

        return (int) ($res[0]->cnt ?? 0);
    }

    public function demChoThue(): int
    {
        $res = DB::select("SELECT COUNT(*) as cnt FROM du_an WHERE trang_thai = 'xuat_ban' AND loai_bat_dong_san LIKE '%thuê%'");

        return (int) ($res[0]->cnt ?? 0);
    }

    public function layThongKeTheoTinhThanh(): array
    {
        return DB::select("SELECT tinh_thanh, COUNT(*) as cnt FROM du_an WHERE trang_thai = 'xuat_ban' GROUP BY tinh_thanh");
    }

    public function layBannerImages(int $limit = 7): array
    {
        return DB::select("SELECT anh_thu_nho, tieu_de, duong_dan FROM du_an
                           WHERE trang_thai = 'xuat_ban'
                           AND anh_thu_nho IS NOT NULL
                           AND anh_thu_nho != ''
                           ORDER BY luot_xem DESC, noi_bat DESC
                           LIMIT ".(int) $limit);
    }

    public function tongLuotXem(): int
    {
        $res = DB::select('SELECT COALESCE(SUM(luot_xem), 0) as cnt FROM du_an');

        return (int) ($res[0]->cnt ?? 0);
    }

    public function demTinMoiTrongTuan(): int
    {
        $res = DB::select("SELECT COUNT(*) as cnt FROM du_an WHERE trang_thai = 'xuat_ban' AND ngay_tao >= DATE_SUB(NOW(), INTERVAL 7 DAY)");

        return (int) ($res[0]->cnt ?? 0);
    }

    /** Gom các chỉ số trang chủ vào một lần truy vấn thay vì quét bảng nhiều lần. */
    public function thongKeTrangChu(): object
    {
        $rows = DB::select("
            SELECT
                SUM(trang_thai = 'xuat_ban') AS tong_tin_dang,
                SUM(trang_thai = 'xuat_ban' AND loai_bat_dong_san LIKE '%bán%') AS tin_mua_ban,
                SUM(trang_thai = 'xuat_ban' AND loai_bat_dong_san LIKE '%thuê%') AS tin_cho_thue,
                COALESCE(SUM(luot_xem), 0) AS tong_luot_xem,
                SUM(trang_thai = 'xuat_ban' AND ngay_tao >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS tin_moi_tuan
            FROM du_an
        ");

        return $rows[0] ?? (object) [];
    }

    public function layThongTinChiTiet(string $slug): mixed
    {
        $res = DB::select("
            SELECT p.*,
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
               AND p.trang_thai = 'xuat_ban'
        ", ['slug' => $slug]);

        return $res ? $res[0] : null;
    }

    public function layTinLienQuan(int $excludeId, string $loaiBds, string $tinhThanh, string $gia = '', int $limit = 12): array
    {
        return DB::select("
             SELECT p.*, c.ten AS ten_danh_muc,
                    u.ten AS ten_nguoi_dung, u.anh_dai_dien,
                    CASE WHEN (p.ngay_het_han_vip IS NULL OR p.ngay_het_han_vip > NOW())
                         THEN p.goi_vip ELSE 0 END AS active_vip,
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
             LIMIT ".(int) $limit.'
        ', [
            'exclude_id' => $excludeId,
            'loai' => $loaiBds,
            'loai2' => $loaiBds,
            'tinh' => $tinhThanh,
            'tinh2' => $tinhThanh,
        ]);
    }
}
