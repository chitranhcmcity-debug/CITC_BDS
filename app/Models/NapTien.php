<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NapTien extends Model
{
    protected $table = 'nap_tien';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = null;

    protected $fillable = [
        'ma_nguoi_dung',
        'so_tien',
        'so_tien_khuyen_mai',
        'tong_cong',
        'phuong_thuc',
        'ma_giao_dich',
        'trang_thai',
        'ghi_chu',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_dung');
    }
}
