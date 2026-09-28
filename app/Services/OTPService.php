<?php

namespace App\Services;

use App\Models\Database;
use App\Models\OTP;

/**
 * OTPService – Sinh mã OTP 6 chữ số, lưu hash sha256 vào DB, validate thời gian sử dụng và số lần thử.
 * Tuân thủ SOLID, Service Pattern.
 */
class OTPService
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Sinh mã OTP 6 số và lưu vào CSDL (hạn 5 phút).
     * Trả về mã OTP thô để gửi đi (SMS/Email).
     */
    public function generateOTP(int $userId, string $purpose = 'phone_verify'): string
    {
        // 1. Sinh mã 6 chữ số ngẫu nhiên
        $rawOtp = (string) random_int(100000, 999999);
        $hashedOtp = hash('sha256', $rawOtp);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        // Vô hiệu hoá các OTP cũ cùng mục đích của người dùng này
        $this->db->query('
            UPDATE otp_codes 
            SET expires_at = NOW() 
            WHERE user_id = :uid AND purpose = :purpose AND used_at IS NULL AND expires_at > NOW()
        ');
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);
        $this->db->bind(':purpose', $purpose);
        $this->db->execute();

        // 2. Lưu OTP mới vào DB
        $this->db->query('
            INSERT INTO otp_codes (user_id, otp_code, purpose, expires_at, attempts, created_at)
            VALUES (:uid, :code, :purpose, :expires, 0, NOW())
        ');
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);
        $this->db->bind(':code', $hashedOtp);
        $this->db->bind(':purpose', $purpose);
        $this->db->bind(':expires', $expiresAt);
        $this->db->execute();

        return $rawOtp;
    }

    public function invalidateOTP(int $userId, string $purpose = 'phone_verify'): void
    {
        $this->db->query('UPDATE otp_codes SET expires_at = NOW() WHERE user_id = :uid AND purpose = :purpose AND used_at IS NULL');
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);
        $this->db->bind(':purpose', $purpose);
        $this->db->execute();
    }

    /**
     * Xác minh mã OTP do người dùng nhập.
     */
    public function verifyOTP(int $userId, string $rawOtp, string $purpose = 'phone_verify'): array
    {
        $hashedOtp = hash('sha256', trim($rawOtp));

        // 1. Tìm mã OTP hợp lệ gần nhất
        $this->db->query('
            SELECT * FROM otp_codes
            WHERE user_id = :uid AND purpose = :purpose AND used_at IS NULL AND expires_at > NOW()
            ORDER BY created_at DESC LIMIT 1
        ');
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);
        $this->db->bind(':purpose', $purpose);
        $otpRecord = $this->db->single();

        if (! $otpRecord) {
            return ['success' => false, 'message' => 'Mã OTP đã hết hạn hoặc không tồn tại. Vui lòng yêu cầu mã mới.'];
        }

        // 2. Kiểm tra giới hạn số lần thử (tối đa 3 lần)
        if ((int) $otpRecord->attempts >= 3) {
            // Vô hiệu hóa OTP ngay lập tức
            $this->db->query('UPDATE otp_codes SET expires_at = NOW() WHERE id = :id');
            $this->db->bind(':id', $otpRecord->id, PDO::PARAM_INT);
            $this->db->execute();

            return ['success' => false, 'message' => 'Mã OTP này đã bị khóa do nhập sai quá 3 lần. Vui lòng gửi lại mã mới.'];
        }

        // 3. So khớp mã OTP
        if (hash_equals($otpRecord->otp_code, $hashedOtp)) {
            // Xác thực thành công -> đánh dấu sử dụng
            $this->db->query('UPDATE otp_codes SET used_at = NOW() WHERE id = :id');
            $this->db->bind(':id', $otpRecord->id, PDO::PARAM_INT);
            $this->db->execute();

            return ['success' => true, 'message' => 'Mã OTP chính xác.'];
        } else {
            // Sai -> Tăng số lần thử
            $this->db->query('UPDATE otp_codes SET attempts = attempts + 1 WHERE id = :id');
            $this->db->bind(':id', $otpRecord->id, PDO::PARAM_INT);
            $this->db->execute();

            $remaining = 3 - ((int) $otpRecord->attempts + 1);
            if ($remaining <= 0) {
                // Vô hiệu hoá luôn
                $this->db->query('UPDATE otp_codes SET expires_at = NOW() WHERE id = :id');
                $this->db->bind(':id', $otpRecord->id, PDO::PARAM_INT);
                $this->db->execute();

                return ['success' => false, 'message' => 'Mã OTP đã bị khóa do nhập sai quá 3 lần. Vui lòng gửi lại mã mới.'];
            }

            return ['success' => false, 'message' => "Mã OTP không đúng. Bạn còn $remaining lần thử."];
        }
    }
}
