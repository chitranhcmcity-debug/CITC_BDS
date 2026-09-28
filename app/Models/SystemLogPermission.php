<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemLogPermission extends Model
{
    protected $table = 'quyen_nhat_ky_he_thong';

    protected $primaryKey = ['role_id', 'log_type'];

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'log_type',
        'can_view',
        'can_export',
        'can_manage',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_export' => 'boolean',
        'can_manage' => 'boolean',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(VaiTro::class, 'role_id');
    }
}
