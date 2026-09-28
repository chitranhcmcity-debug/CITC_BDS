<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class NguoiDung extends Authenticatable
{
    protected $table = 'nguoi_dung';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ma_vai_tro',
        'ten',
        'email',
        'mat_khau',
        'dien_thoai',
        'anh_dai_dien',
        'so_du',
        'luot_up_tin',
        'trang_thai',
        'remember_token',
        'email_verified_at',
        'auth_version',
        'google_id',
        'facebook_id',
    ];

    protected $hidden = [
        'mat_khau',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'so_du' => 'integer',
            'luot_up_tin' => 'integer',
            'auth_version' => 'integer',
        ];
    }

    /**
     * Laravel sử dụng 'password' mặc định, ta override để dùng 'mat_khau'.
     */
    public function getAuthPassword(): string
    {
        return $this->mat_khau;
    }

    /* ── Helpers ── */

    public function isAdmin(): bool
    {
        return (int) $this->ma_vai_tro === 1;
    }

    public function isActive(): bool
    {
        return $this->trang_thai === 'hoat_dong';
    }

    /* ── Relationships ── */

    public function vaiTro(): BelongsTo
    {
        return $this->belongsTo(VaiTro::class, 'ma_vai_tro');
    }

    public function duAn(): HasMany
    {
        return $this->hasMany(DuAn::class, 'ma_nguoi_dung');
    }

    public function baiViet(): HasMany
    {
        return $this->hasMany(BaiViet::class, 'ma_nguoi_dung');
    }

    public function yeuThich(): BelongsToMany
    {
        return $this->belongsToMany(DuAn::class, 'yeu_thich', 'ma_nguoi_dung', 'ma_du_an')
            ->withPivot('ngay_tao');
    }

    public function thongBao(): HasMany
    {
        return $this->hasMany(ThongBao::class, 'ma_nguoi_dung');
    }

    public function giaoDich(): HasMany
    {
        return $this->hasMany(GiaoDich::class, 'ma_nguoi_dung');
    }

    public function napTien(): HasMany
    {
        return $this->hasMany(NapTien::class, 'ma_nguoi_dung');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'user_one')
            ->orWhere('user_two', $this->id);
    }

    public function loginHistory(): HasMany
    {
        return $this->hasMany(LoginHistory::class, 'user_id');
    }

    /* ── Scopes ── */

    public function scopeHoatDong($query)
    {
        return $query->where('trang_thai', 'hoat_dong');
    }

    public function scopeAdmin($query)
    {
        return $query->where('ma_vai_tro', 1);
    }

    public function scopeClient($query)
    {
        return $query->where('ma_vai_tro', 2);
    }
}
