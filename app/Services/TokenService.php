<?php

namespace App\Services;

use App\Repositories\TokenRepository;

/**
 * TokenService – Tạo và xác thực các loại token (email verify, reset password, remember me).
 */
class TokenService
{
    private TokenRepository $tokenRepo;

    public function __construct()
    {
        $this->tokenRepo = new TokenRepository;
    }

    /* ── TẠO TOKEN ── */

    /**
     * Tạo token xác thực email (30 phút).
     */
    public function createEmailVerifyToken(int $userId): string
    {
        return $this->tokenRepo->create($userId, 'email_verify', 30);
    }

    /**
     * Tạo token đặt lại mật khẩu (30 phút).
     */
    public function createPasswordResetToken(int $userId): string
    {
        return $this->tokenRepo->create($userId, 'reset_password', 30);
    }

    /**
     * Tạo token "Ghi nhớ đăng nhập" (30 ngày).
     */
    public function createRememberMeToken(int $userId): string
    {
        return $this->tokenRepo->create($userId, 'remember_me', 60 * 24 * 30);
    }

    /* ── XÁC THỰC TOKEN ── */

    /**
     * Xác thực token xác thực email.
     *
     * @return object|null Bản ghi token hoặc null nếu không hợp lệ
     */
    public function validateEmailToken(string $token): ?object
    {
        return $this->tokenRepo->findValid($token, 'email_verify') ?: null;
    }

    /**
     * Xác thực token đặt lại mật khẩu.
     */
    public function validateResetToken(string $token): ?object
    {
        return $this->tokenRepo->findValid($token, 'reset_password') ?: null;
    }

    /**
     * Xác thực token "Ghi nhớ đăng nhập".
     */
    public function validateRememberToken(string $token): ?object
    {
        return $this->tokenRepo->findValid($token, 'remember_me') ?: null;
    }

    /* ── ĐÁNH DẤU ĐÃ DÙNG ── */

    public function markUsed(int $tokenId): bool
    {
        return $this->tokenRepo->markUsed($tokenId);
    }

    /* ── XÓA ── */

    /**
     * Xóa tất cả token của user (dùng sau khi đặt lại mật khẩu).
     */
    public function revokeAll(int $userId): bool
    {
        return $this->tokenRepo->deleteAllByUser($userId);
    }

    /**
     * Xóa remember-me token của user.
     */
    public function revokeRememberMe(int $userId): bool
    {
        return $this->tokenRepo->deleteByUserAndType($userId, 'remember_me');
    }

    /* ── KIỂM TRA TRẠNG THÁI TOKEN ── */

    /**
     * Kiểm tra token tồn tại nhưng đã hết hạn.
     */
    public function isExpired(string $token, string $type): bool
    {
        $row = $this->tokenRepo->findAny($token, $type);
        if (! $row) {
            return false;
        }

        return strtotime($row->expires_at) <= time();
    }

    /* ── DỌN DẸP ── */

    public function cleanExpired(): int
    {
        return $this->tokenRepo->deleteExpired();
    }
}
