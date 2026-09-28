<?php
/**
 * Lớp CSRF - Bảo vệ chống Cross-Site Request Forgery.
 *
 * Cách hoạt động:
 *   1. Mỗi form POST có một token ẩn.
 *   2. Server tạo token ngẫu nhiên và lưu vào session.
 *   3. Khi gửi form, server so sánh token trong POST với session.
 *   4. Nếu không khớp, request bị từ chối.
 *
 * Tich hop:
 *   - Trong view: Csrf::field()  => in ra <input type="hidden">
 *   - Trong controller: Csrf::verify() => kiểm tra trước khi xử lý
 */
class Csrf
{
    /** @var string Ten khoa luu token trong session va trong POST form */
    private const TOKEN_KEY = '_csrf_token';

    // ==========================================
    // TẠO TOKEN
    // ==========================================

    /**
     * Lấy hoặc tạo CSRF token cho phiên hiện tại.
     * Token được tái sử dụng trong phiên để giảm tải.
     *
     * @return string Token dạng chuỗi hex 64 ký tự
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    // ==========================================
    // IN HIDDEN FIELD VÀO FORM
    // ==========================================

    /**
     * Tạo thẻ input ẩn để nhúng vào form POST.
     * Goi trong view: <?= Csrf::field() ?>
     *
     * @return string HTML hidden input chua CSRF token
     */
    public static function field(): string
    {
        return '<input type="hidden" name="' . self::TOKEN_KEY . '" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    // ==========================================
    // XÁC THỰC TOKEN
    // ==========================================

    /**
     * Kiểm tra CSRF token từ POST request có hợp lệ không.
     * Dùng hash_equals() để chống tấn công đo thời gian.
     *
     * @param  bool $dieOnFail True = kết thúc xử lý ngay nếu thất bại
     * @return bool            True nếu token hợp lệ
     */
    public static function verify(bool $dieOnFail = true): bool
    {
        $tokenGui  = $_POST[self::TOKEN_KEY] ?? '';
        $tokenLuu  = $_SESSION[self::TOKEN_KEY] ?? '';

        // So sánh trong thời gian cố định để giảm rủi ro timing attack.
        $hopLe = !empty($tokenGui) && !empty($tokenLuu) && hash_equals($tokenLuu, $tokenGui);

        if (!$hopLe) {
            // Ghi log cảnh báo CSRF.
            error_log('[SECURITY] CSRF token mismatch | IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ' | URI: ' . ($_SERVER['REQUEST_URI'] ?? ''));

            if ($dieOnFail) {
                http_response_code(403);
                die('Yêu cầu không hợp lệ (CSRF). Vui lòng tải lại trang và thử lại.');
            }
            return false;
        }

        return true;
    }

    // ==========================================
    // XOAY TOKEN (ROTATING)
    // ==========================================

    /**
     * Làm mới token sau request POST hợp lệ để giảm rủi ro bị đánh cắp.
     */
    public static function rotate(): void
    {
        unset($_SESSION[self::TOKEN_KEY]);
    }
}
