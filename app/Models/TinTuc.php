<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class TinTuc extends Model
{
    protected $table = 'bai_viet';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ma_nguoi_dung',
        'ma_danh_muc',
        'tieu_de',
        'duong_dan',
        'tom_tat',
        'noi_dung',
        'anh_thu_nho',
        'trang_thai',
        'luot_xem',
        'noi_bat',
    ];

    protected $casts = [
        'noi_bat' => 'boolean',
        'luot_xem' => 'integer',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_dung');
    }

    public function danhMuc(): BelongsTo
    {
        return $this->belongsTo(DanhMuc::class, 'ma_danh_muc');
    }

    public function the(): BelongsToMany
    {
        return $this->belongsToMany(The::class, 'bai_viet_the', 'ma_bai_viet', 'ma_the');
    }

    public function binhLuan(): HasMany
    {
        return $this->hasMany(BinhLuanBaiViet::class, 'ma_bai_viet');
    }

    /* ── Original Model Compatibility Methods ── */

    public function layTatCa(?int $limit = null): array
    {
        $sql = 'SELECT b.*, d.ten AS ten_danh_muc, n.ten AS ten_tac_gia
                FROM bai_viet b
                LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
                LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
                ORDER BY b.ngay_tao DESC';

        if ($limit) {
            $sql .= ' LIMIT '.(int) $limit;
        }

        return DB::select($sql);
    }

    public function locAdmin(array $filters): array
    {
        $query = 'SELECT b.*, d.ten AS ten_danh_muc, n.ten AS ten_tac_gia
                  FROM bai_viet b
                  LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
                  LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
                  WHERE 1=1';
        $params = [];

        if (! empty($filters['keyword'])) {
            $keyword = '%'.$filters['keyword'].'%';
            $query .= ' AND (b.tieu_de LIKE :keyword_title OR n.ten LIKE :keyword_author';
            $params['keyword_title'] = $keyword;
            $params['keyword_author'] = $keyword;
            if (ctype_digit($filters['keyword'])) {
                $query .= ' OR b.id = :news_id';
                $params['news_id'] = (int) $filters['keyword'];
            }
            $query .= ')';
        }

        if (! empty($filters['status'])) {
            $query .= ' AND b.trang_thai = :status';
            $params['status'] = $filters['status'];
        }

        if (! empty($filters['category'])) {
            $query .= ' AND b.ma_danh_muc = :category';
            $params['category'] = (int) $filters['category'];
        }

        $query .= ' ORDER BY b.ngay_tao DESC';

        return DB::select($query, $params);
    }

    public function layDaXuatBan(?int $limit = null): array
    {
        $sql = "SELECT b.*, d.ten AS ten_danh_muc, n.ten AS ten_tac_gia
                FROM bai_viet b
                LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
                LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
                WHERE b.trang_thai = 'xuat_ban'
                ORDER BY b.ngay_tao DESC";

        if ($limit) {
            $sql .= ' LIMIT '.(int) $limit;
        }

        return DB::select($sql);
    }

    public function layNoiBat(int $limit = 1): mixed
    {
        $res = DB::select("
            SELECT b.*, d.ten AS ten_danh_muc, n.ten AS ten_tac_gia
            FROM bai_viet b
            LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
            LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
            WHERE b.trang_thai = 'xuat_ban' AND b.noi_bat = 1
            ORDER BY b.ngay_tao DESC
            LIMIT :limit
        ", ['limit' => $limit]);

        if ($limit === 1) {
            return $res ? $res[0] : null;
        }

        return $res;
    }

    public function layTheoSlug(string $slug): mixed
    {
        $res = DB::select('
            SELECT b.*, d.ten AS ten_danh_muc, d.duong_dan AS slug_danh_muc, n.ten AS ten_tac_gia
            FROM bai_viet b
            LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
            LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
            WHERE b.duong_dan = ? LIMIT 1
        ', [$slug]);

        return $res ? $res[0] : null;
    }

    public function layLienQuan(int $maDanhMuc, int $loaiTruId, int $limit = 4): array
    {
        return DB::select("
            SELECT b.*, d.ten AS ten_danh_muc, n.ten AS ten_tac_gia
            FROM bai_viet b
            LEFT JOIN danh_muc d ON b.ma_danh_muc = d.id
            LEFT JOIN nguoi_dung n ON b.ma_nguoi_dung = n.id
            WHERE b.trang_thai = 'xuat_ban'
              AND b.ma_danh_muc = :cat_id
              AND b.id != :ex_id
            ORDER BY b.ngay_tao DESC
            LIMIT :limit
        ", [
            'cat_id' => $maDanhMuc,
            'ex_id' => $loaiTruId,
            'limit' => $limit,
        ]);
    }

    public function them(array $data): bool
    {
        return DB::table('bai_viet')->insert($data);
    }

    public function capNhat(array $data): bool
    {
        if (empty($data['id'])) {
            return false;
        }
        $id = $data['id'];
        unset($data['id']);

        return DB::table('bai_viet')->where('id', $id)->update($data) >= 0;
    }

    public function tangLuotXem(int $id): bool
    {
        return DB::update('UPDATE bai_viet SET luot_xem = luot_xem + 1 WHERE id = ?', [$id]) > 0;
    }

    /* ── Alias mapping for backwards compatibility ── */
    public function getAllNews(?int $limit = null): array
    {
        return $this->layTatCa($limit);
    }

    public function getPublishedNews(?int $limit = null): array
    {
        return $this->layDaXuatBan($limit);
    }

    public function getFeaturedNews(int $limit = 1): mixed
    {
        return $this->layNoiBat($limit);
    }

    public function getBySlug(string $slug): mixed
    {
        return $this->layTheoSlug($slug);
    }

    public function getRelatedNews(int $catId, int $exId, int $limit = 4): array
    {
        return $this->layLienQuan($catId, $exId, $limit);
    }

    public function updateViewCount(int $id): bool
    {
        return $this->tangLuotXem($id);
    }
}
