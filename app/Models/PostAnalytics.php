<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostAnalytics extends Model
{
    protected $table = 'thong_ke_bai_dang';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = null;

    public const TYPES = ['view', 'call', 'chat', 'save', 'share', 'phone', 'zalo', 'contact'];

    protected $fillable = [
        'post_id',
        'user_id',
        'actor_user_id',
        'type',
        'value',
        'ip_address',
        'user_agent',
        'visitor_hash',
        'referrer',
        'source',
        'metadata',
        'dedupe_window',
    ];

    protected $casts = [
        'metadata' => 'array',
        'value' => 'integer',
        'dedupe_window' => 'integer',
    ];

    public function duAn(): BelongsTo
    {
        return $this->belongsTo(DuAn::class, 'post_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'actor_user_id');
    }

    public static function isValidType(string $type): bool
    {
        return in_array($type, self::TYPES, true);
    }
}
