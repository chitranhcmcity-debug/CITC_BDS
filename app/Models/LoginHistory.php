<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    protected $table = 'login_history';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'browser',
        'platform',
        'device',
        'country',
        'status',
        'fail_reason',
        'user_agent',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }

    /* ── Original Compatibility properties ── */
    public function getOsAttribute()
    {
        return $this->platform;
    }

    public function setOsAttribute($value)
    {
        $this->attributes['platform'] = $value;
    }
}
