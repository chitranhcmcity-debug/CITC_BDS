<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HinhAnhDuAn extends Model
{
    protected $table = 'hinh_anh_du_an';

    public $timestamps = false;

    protected $fillable = [
        'ma_du_an',
        'duong_dan_anh',
        'thu_tu',
    ];

    public function duAn(): BelongsTo
    {
        return $this->belongsTo(DuAn::class, 'ma_du_an');
    }
}
