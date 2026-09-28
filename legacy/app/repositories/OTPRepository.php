<?php
/**
 * OTPRepository – Quản lý mã OTP 6 chữ số trong bảng ma_otp.
 */
class OTPRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Tạo OTP mới 6 chữ số.
     * @param int    $userId
     * @param string $purpose  login | reset_password | phone_verify
     * @param int    $ttlMin   Thời gian hiệu lực (mặc định 5 phút)
     * @return string OTP 6 số
     */
    public function create(int $userId, string $purpose, int $ttlMin = 5): string
    {
        // Xóa OTP cũ chưa dùng cùng purpose
        $this->invalidatePending($userId, $purpose);

        $otp       = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$ttlMin} minutes"));

        $this->db->query(
            "INSERT INTO ma_otp (user_id, otp_code, purpose, expires_at, attempts)
             VALUES (:uid, :otp, :purpose, :exp, 0)"
        );
        $this->db->bind(':uid',     $userId);
        $this->db->bind(':otp',     hash('sha256', $otp));
        $this->db->bind(':purpose', $purpose);
        $this->db->bind(':exp',     $expiresAt);
        $this->db->execute();

        return $otp;
    }

    /**
     * Xác minh OTP. Tự động tăng attempts và đánh dấu used_at nếu đúng.
     * @return bool true nếu OTP hợp lệ
     */
    public function verify(int $userId, string $code, string $purpose): bool
    {
        $this->db->query(
            "SELECT * FROM ma_otp
             WHERE user_id  = :uid
               AND purpose  = :purpose
               AND used_at  IS NULL
               AND attempts < 5
               AND expires_at > NOW()
             ORDER BY created_at DESC
             LIMIT 1"
        );
        $this->db->bind(':uid',     $userId);
        $this->db->bind(':purpose', $purpose);
        $row = $this->db->single();

        if (!$row) return false;

        // Chặn brute-force: mỗi mã OTP chỉ được thử tối đa 5 lần.
        if ((int)$row->attempts >= 5) {
            $this->db->query("UPDATE ma_otp SET used_at = NOW() WHERE id = :id");
            $this->db->bind(':id', $row->id);
            $this->db->execute();
            return false;
        }

        // Tăng số lần thử.
        $this->db->query("UPDATE ma_otp SET attempts = attempts + 1 WHERE id = :id");
        $this->db->bind(':id', $row->id);
        $this->db->execute();

        // Kiểm tra code khớp
        if (!hash_equals((string)$row->otp_code, hash('sha256', $code))) return false;

        // Đánh dấu đã dùng
        $this->db->query("UPDATE ma_otp SET used_at = NOW() WHERE id = :id");
        $this->db->bind(':id', $row->id);
        $this->db->execute();

        return true;
    }

    /**
     * Kiểm tra cooldown gửi lại OTP (60 giây).
     */
    public function canResend(int $userId, string $purpose, int $cooldownSec = 60): bool
    {
        $this->db->query(
            "SELECT created_at FROM ma_otp
             WHERE user_id = :uid AND purpose = :purpose
             ORDER BY created_at DESC LIMIT 1"
        );
        $this->db->bind(':uid',     $userId);
        $this->db->bind(':purpose', $purpose);
        $row = $this->db->single();

        if (!$row) return true;
        $lastSent = strtotime($row->created_at);
        return (time() - $lastSent) >= $cooldownSec;
    }

    /**
     * Lấy số giây còn lại trước khi được gửi lại.
     */
    public function resendCooldownLeft(int $userId, string $purpose, int $cooldownSec = 60): int
    {
        $this->db->query(
            "SELECT created_at FROM ma_otp
             WHERE user_id = :uid AND purpose = :purpose
             ORDER BY created_at DESC LIMIT 1"
        );
        $this->db->bind(':uid',     $userId);
        $this->db->bind(':purpose', $purpose);
        $row = $this->db->single();

        if (!$row) return 0;
        $elapsed = time() - strtotime($row->created_at);
        return max(0, $cooldownSec - $elapsed);
    }

    /**
     * Vô hiệu hóa (xóa) các OTP đang chờ của user theo purpose.
     */
    public function invalidatePending(int $userId, string $purpose): bool
    {
        $this->db->query(
            "DELETE FROM ma_otp
             WHERE user_id = :uid AND purpose = :purpose AND used_at IS NULL"
        );
        $this->db->bind(':uid',     $userId);
        $this->db->bind(':purpose', $purpose);
        return $this->db->execute();
    }

    /**
     * Dọn dẹp OTP hết hạn.
     */
    public function deleteExpired(): int
    {
        $this->db->query("DELETE FROM ma_otp WHERE expires_at < NOW()");
        $this->db->execute();
        return $this->db->rowCount();
    }

    /** Lịch sử OTP dành cho quản trị; không trả về cột chứa mã đã băm. */
    public function findByUser(int $userId, int $limit = 20): array
    {
        $this->db->query(
            "SELECT id, user_id, purpose, expires_at, used_at, attempts, created_at
             FROM ma_otp
             WHERE user_id = :uid
             ORDER BY created_at DESC
             LIMIT :lim"
        );
        $this->db->bind(':uid', $userId);
        $this->db->bind(':lim', $limit, PDO::PARAM_INT);
        return $this->db->resultSet() ?: [];
    }
}
