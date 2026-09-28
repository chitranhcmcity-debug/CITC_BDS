<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LichHen extends Model
{
    protected $table = 'lich_hen';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ma_du_an',
        'ma_nguoi_dung',
        'ho_ten',
        'so_dien_thoai',
        'email',
        'ngay_xem',
        'gio_xem',
        'ghi_chu',
        'trang_thai',
    ];

    public function duAn(): BelongsTo
    {
        return $this->belongsTo(DuAn::class, 'ma_du_an');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_dung');
    }
}
