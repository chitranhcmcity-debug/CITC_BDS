<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DanhMuc extends Model
{
    protected $table = 'danh_muc';

    public $timestamps = false;

    protected $fillable = [
        'ten',
        'duong_dan',
        'loai',
        'trang_thai',
    ];

    public function duAn(): HasMany
    {
        return $this->hasMany(DuAn::class, 'ma_danh_muc');
    }

    public function baiViet(): HasMany
    {
        return $this->hasMany(BaiViet::class, 'ma_danh_muc');
    }
}
