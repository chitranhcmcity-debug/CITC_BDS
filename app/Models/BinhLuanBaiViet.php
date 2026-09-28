<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BinhLuanBaiViet extends Model
{
    protected $table = 'binh_luan_bai_viet';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = null;

    protected $fillable = [
        'ma_bai_viet',
        'ma_nguoi_dung',
        'ma_cha',
        'noi_dung',
        'ten_hien_thi',
        'trang_thai',
    ];

    public function baiViet(): BelongsTo
    {
        return $this->belongsTo(BaiViet::class, 'ma_bai_viet');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_dung');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(BinhLuanBaiViet::class, 'ma_cha');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(BinhLuanBaiViet::class, 'ma_cha');
    }
}
