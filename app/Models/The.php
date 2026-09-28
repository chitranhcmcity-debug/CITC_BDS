<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class The extends Model
{
    protected $table = 'the';

    public $timestamps = false;

    protected $fillable = [
        'ten',
        'duong_dan',
    ];

    public function baiViet(): BelongsToMany
    {
        return $this->belongsToMany(BaiViet::class, 'bai_viet_the', 'ma_the', 'ma_bai_viet');
    }
}
