<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrinhChieu extends Model
{
    protected $table = 'trinh_chieu';

    public $timestamps = false;

    protected $fillable = [
        'tieu_de',
        'tieu_de_phu',
        'hinh_anh',
        'lien_ket',
        'thu_tu',
        'trang_thai',
    ];
}
