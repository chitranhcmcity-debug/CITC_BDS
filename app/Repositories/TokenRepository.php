<?php

namespace App\Repositories;

use App\Models\Database;

/**
 * TokenRepository – Quản lý user_tokens (email_verify, reset_password, remember_me).
 */
class TokenRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Tạo token mới, trả về chuỗi token.
     *
     * @param  string  $type  email_verify | reset_password | remember_me
     * @param  int  $ttlMin  Thời gian hiệu lực (phút)
     */
    public function create(int $userId, string $type, int $ttlMin = 30): string
    {
        // Xóa token cũ cùng loại của user
        $this->deleteByUserAndType($userId, $type);

        $token = bin2hex(random_bytes(32)); // Token thô chỉ trả về cho người dùng một lần.
        $tokenHash = hash('sha256', $token);   // CSDL chỉ lưu bản băm để giảm rủi ro lộ token.
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$ttlMin} minutes"));

        $this->db->query(
            'INSERT INTO user_tokens (user_id, token, type, expires_at)
             VALUES (:uid, :tok, :type, :exp)'
        );
        $this->db->bind(':uid', $userId);
        $this->db->bind(':tok', $tokenHash);
        $this->db->bind(':type', $type);
        $this->db->bind(':exp', $expiresAt);
        $this->db->execute();

        return $token;
    }

    /**
     * Tìm token hợp lệ (chưa dùng, chưa hết hạn).
     */
    public function findValid(string $token, string $type): mixed
    {
        $this->db->query(
            'SELECT * FROM user_tokens
             WHERE token = :tok
               AND type  = :type
               AND used_at IS NULL
               AND expires_at > NOW()
             LIMIT 1'
        );
        $this->db->bind(':tok', hash('sha256', $token));
        $this->db->bind(':type', $type);
        $row = $this->db->single();

        return $this->db->rowCount() > 0 ? $row : null;
    }

    /**
     * Tìm token (bao gồm hết hạn) – dùng để kiểm tra token expired.
     */
    public function findAny(string $token, string $type): mixed
    {
        $this->db->query(
            'SELECT * FROM user_tokens WHERE token = :tok AND type = :type LIMIT 1'
        );
        $this->db->bind(':tok', hash('sha256', $token));
        $this->db->bind(':type', $type);
        $row = $this->db->single();

        return $this->db->rowCount() > 0 ? $row : null;
    }

    /**
     * Đánh dấu token đã sử dụng.
     */
    public function markUsed(int $id): bool
    {
        $this->db->query('UPDATE user_tokens SET used_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    /**
     * Xóa tất cả token của user theo type.
     */
    public function deleteByUserAndType(int $userId, string $type): bool
    {
        $this->db->query(
            'DELETE FROM user_tokens WHERE user_id = :uid AND type = :type'
        );
        $this->db->bind(':uid', $userId);
        $this->db->bind(':type', $type);

        return $this->db->execute();
    }

    /**
     * Xóa tất cả token của user (dùng khi reset password – đăng xuất khỏi tất cả thiết bị).
     */
    public function deleteAllByUser(int $userId): bool
    {
        $this->db->query('DELETE FROM user_tokens WHERE user_id = :uid');
        $this->db->bind(':uid', $userId);

        return $this->db->execute();
    }

    /**
     * Dọn dẹp token đã hết hạn (gọi định kỳ hoặc sau migration).
     */
    public function deleteExpired(): int
    {
        $this->db->query('DELETE FROM user_tokens WHERE expires_at < NOW()');
        $this->db->execute();

        return $this->db->rowCount();
    }
}
