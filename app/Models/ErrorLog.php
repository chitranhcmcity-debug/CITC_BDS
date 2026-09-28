<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErrorLog extends Model
{
    protected $table = 'error_logs';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = null;

    protected $fillable = [
        'module',
        'level',
        'error_type',
        'message',
        'stack_trace',
        'request_url',
        'request_method',
        'user_id',
        'ip_address',
        'context',
        'resolved',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'context' => 'array',
        'resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'resolved_by');
    }
}
