<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DanhGia extends Model
{
    protected $table = 'danh_gia';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = null;

    protected $fillable = [
        'ma_nguoi_dung',
        'ma_du_an',
        'so_sao',
        'binh_luan',
        'trang_thai',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_dung');
    }

    public function duAn(): BelongsTo
    {
        return $this->belongsTo(DuAn::class, 'ma_du_an');
    }
}
