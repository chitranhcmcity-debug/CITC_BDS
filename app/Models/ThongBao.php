<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThongBao extends Model
{
    protected $table = 'thong_bao';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = null;

    protected $fillable = [
        'ma_nguoi_dung',
        'tieu_de',
        'noi_dung',
        'da_doc',
    ];

    protected $casts = [
        'da_doc' => 'boolean',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_dung');
    }
}
