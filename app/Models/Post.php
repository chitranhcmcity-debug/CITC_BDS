<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    protected $table = 'du_an';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    public const STATUSES = ['cho_duyet', 'da_duyet', 'tu_choi', 'het_han', 'da_an', 'da_ban'];

    public const VIP_TYPES = ['thuong', 'vip1', 'vip2', 'vip3'];

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

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_dung');
    }

    public function countByUser(int $userId): int
    {
        $res = DB::select('SELECT COUNT(*) as total FROM du_an WHERE ma_nguoi_dung = ?', [$userId]);

        return (int) ($res[0]->total ?? 0);
    }

    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::STATUSES, true);
    }
}
