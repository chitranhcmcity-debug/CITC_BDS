<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThongBao extends Model
{
    protected $table = 'thong_bao_nguoi_dung';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'content',
        'url',
        'icon',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }
}
