<?php
/**
 * AuthMiddleware – Bảo vệ route yêu cầu đăng nhập.
 * Hỗ trợ: session login + cookie remember-me auto-login.
 */
class AuthMiddleware
{
    /**
     * Kiểm tra đăng nhập. Nếu chưa, redirect về trang đăng nhập.
     * Tự động thử remember-me cookie nếu chưa có session.
     *
     * @param bool $requireVerified Yêu cầu email đã xác thực
     */
    public static function handle(bool $requireVerified = false): void
    {
        // 1. Thử auto-login từ remember-me cookie
        if (!Auth::isLoggedIn()) {
            self::tryRememberMeLogin();
        }

        // 2. Vẫn chưa đăng nhập → redirect
        if (!Auth::isLoggedIn()) {
            Session::flash('error_msg', 'Bạn cần đăng nhập để truy cập trang này.', 'alert alert-warning');
            $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '');
            header('Location: ' . URL_ROOT . '/nguoi-dung/dang-nhap?redirect=' . $redirect);
            exit;
        }

        // 3. Kiểm tra email đã xác thực (nếu cần)
        if ($requireVerified) {
            $verified = Session::get('user_email_verified');
            if (!$verified) {
                Session::flash('error_msg', 'Bạn cần xác thực email trước khi tiếp tục.', 'alert alert-warning');
                header('Location: ' . URL_ROOT . '/nguoi-dung/verify-email-notice');
                exit;
            }
        }
    }

    /**
     * Thử đăng nhập tự động từ cookie `remember_token`.
     */
    private static function tryRememberMeLogin(): void
    {
        $cookieToken = $_COOKIE['remember_token'] ?? '';
        if (empty($cookieToken)) return;

        // Load AuthService
        $serviceFile = APP_ROOT . '/app/services/DichVuXacThuc.php';
        if (!class_exists('AuthService') && file_exists($serviceFile)) {
            require_once APP_ROOT . '/app/repositories/UserRepository.php';
            require_once APP_ROOT . '/app/repositories/TokenRepository.php';
            require_once APP_ROOT . '/app/repositories/LoginRepository.php';
            require_once APP_ROOT . '/app/repositories/OTPRepository.php';
            require_once APP_ROOT . '/app/services/DichVuBaoMat.php';
            require_once APP_ROOT . '/app/services/DichVuMaTruyCap.php';
            require_once APP_ROOT . '/app/services/DichVuOTP.php';
            require_once APP_ROOT . '/app/services/DichVuThuDienTu.php';
            require_once $serviceFile;
        }

        try {
            $authService = new AuthService();
            $remembered = $authService->loginFromCookie($cookieToken);

            if ($remembered) {
                $user = $remembered['user'];
                $newToken = $remembered['token'];
                session_regenerate_id(true);
                Session::set('user_id',             (int)$user->id);
                Session::set('user_email',          $user->email);
                Session::set('user_name',           $user->ten);
                Session::set('user_role_id',        (int)$user->ma_vai_tro);
                Session::set('user_email_verified', !empty($user->email_verified_at));
                Session::set('user_ip',             $_SERVER['REMOTE_ADDR'] ?? '');
                Session::set('auth_version',         (int)($user->auth_version ?? 1));

                // Làm mới cookie thêm 30 ngày
                setcookie('remember_token', $newToken, [
                    'expires'  => time() + 60 * 60 * 24 * 30,
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                    'secure'   => isset($_SERVER['HTTPS']),
                ]);
            } else {
                // Token không hợp lệ → xóa cookie
                setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/']);
            }
        } catch (Throwable $e) {
            error_log('[AuthMiddleware] Remember-me error: ' . $e->getMessage());
        }
    }

    /**
     * Tiện ích: Yêu cầu quyền Admin.
     */
    public static function requireAdmin(): void
    {
        self::handle();
        if ((int)Session::get('user_role_id') !== 1) {
            http_response_code(403);
            header('Location: ' . URL_ROOT . '/');
            exit;
        }
    }
}
