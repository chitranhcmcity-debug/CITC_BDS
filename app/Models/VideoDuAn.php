<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoDuAn extends Model
{
    protected $table = 'video_du_an';

    public $timestamps = false;

    protected $fillable = [
        'ma_du_an',
        'duong_dan_video',
    ];

    public function duAn(): BelongsTo
    {
        return $this->belongsTo(DuAn::class, 'ma_du_an');
    }
}
