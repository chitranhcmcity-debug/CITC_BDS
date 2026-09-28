<?php
/**
 * ThrottleMiddleware – Rate limiting cho các action nhạy cảm.
 * Sử dụng RateLimiter (đã có sẵn trong project).
 */
class ThrottleMiddleware
{
    /**
     * Kiểm tra rate limit. Nếu vượt quá → trả về false.
     *
     * @param string $key        Định danh (vd: "login|127.0.0.1")
     * @param int    $maxAttempts Số lần tối đa
     * @param int    $decaySec   Cửa sổ thời gian (giây)
     * @return bool true = cho phép, false = bị chặn
     */
    public static function check(string $key, int $maxAttempts = 5, int $decaySec = 60): bool
    {
        return !RateLimiter::tooManyAttempts($key, $maxAttempts, $decaySec);
    }

    /**
     * Ghi nhận một lần thử (thất bại).
     */
    public static function hit(string $key, int $decaySec = 60): void
    {
        RateLimiter::hit($key, $decaySec);
    }

    /**
     * Xóa counter (sau khi thành công).
     */
    public static function clear(string $key): void
    {
        RateLimiter::clear($key);
    }

    /**
     * Lấy số giây còn lại trước khi được thử lại.
     */
    public static function availableIn(string $key): int
    {
        return RateLimiter::availableIn($key) ?? 0;
    }

    /**
     * Tạo key chuẩn hóa cho login throttle (IP-based).
     */
    public static function loginKey(string $identifier = ''): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return 'login|' . $ip . ($identifier ? '|' . md5($identifier) : '');
    }

    /**
     * Tạo key cho forgot password throttle.
     */
    public static function forgotKey(): string
    {
        return 'forgot|' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }

    /**
     * Tạo key cho register throttle.
     */
    public static function registerKey(): string
    {
        return 'register|' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }
}
