<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $table = 'quyen_han';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'code',
        'module',
        'action',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(VaiTro::class, 'vai_tro_quyen_han', 'permission_id', 'role_id');
    }
}
