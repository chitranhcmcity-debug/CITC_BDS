<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminLog extends Model
{
    protected $table = 'nhat_ky_quan_tri';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = null;

    protected $fillable = [
        'admin_id',
        'action',
        'module',
        'target_type',
        'target_id',
        'description',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'target_id' => 'integer',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'admin_id');
    }
}
