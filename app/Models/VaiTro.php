<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VaiTro extends Model
{
    public const SUPER_ADMIN = 1;

    public const EDITOR = 2;

    public const MEMBER = 3;

    public const ADMIN = 4;

    public const MODERATOR = 5;

    public const SUPPORT = 6;

    public const GUEST = 7;

    protected $table = 'vai_tro';

    public $timestamps = false;

    protected $fillable = ['ten', 'mo_ta'];

    /* ── Relationships ── */

    public function nguoiDung(): HasMany
    {
        return $this->hasMany(NguoiDung::class, 'ma_vai_tro');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }

    public function logPermissions(): HasMany
    {
        return $this->hasMany(SystemLogPermission::class, 'role_id');
    }
}
