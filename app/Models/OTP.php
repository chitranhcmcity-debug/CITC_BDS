<?php

namespace App\Models;

class OTP
{
    public int $id = 0;

    public int $user_id = 0;

    public string $purpose = 'verify_phone';

    public string $expires_at = '';

    public ?string $used_at = null;

    public int $attempts = 0;

    public string $created_at = '';

    public const PURPOSES = ['verify_phone', 'verify_email', 'reset_password', 'two_factor'];

    public function __construct(array|object $data = [])
    {
        if (empty($data)) {
            return;
        }
        $d = is_object($data) ? get_object_vars($data) : $data;

        $this->id = (int) ($d['id'] ?? 0);
        $this->user_id = (int) ($d['user_id'] ?? $d['ma_nguoi_dung'] ?? 0);
        $this->purpose = (string) ($d['purpose'] ?? $d['muc_dich'] ?? 'verify_phone');
        $this->expires_at = (string) ($d['expires_at'] ?? $d['het_han'] ?? '');
        $this->used_at = $d['used_at'] ?? $d['da_dung'] ?? null;
        $this->attempts = (int) ($d['attempts'] ?? $d['so_lan_thu'] ?? 0);
        $this->created_at = (string) ($d['created_at'] ?? $d['ngay_tao'] ?? '');
    }

    public function isExpired(): bool
    {
        return ! empty($this->expires_at) && strtotime($this->expires_at) < time();
    }

    public static function isValidPurpose(string $purpose): bool
    {
        return in_array($purpose, self::PURPOSES, true);
    }
}
