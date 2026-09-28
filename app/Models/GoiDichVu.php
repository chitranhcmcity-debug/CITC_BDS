<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoiDichVu extends Model
{
    protected $table = 'goi_dich_vu';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = null;

    protected $fillable = [
        'ten',
        'mo_ta',
        'gia',
        'so_ngay',
        'cap_do',
        'trang_thai',
    ];

    protected $casts = [
        'gia' => 'integer',
        'so_ngay' => 'integer',
        'cap_do' => 'integer',
    ];
}
