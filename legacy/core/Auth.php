<?php
/**
 * Lớp Auth - Kiểm tra quyền truy cập và phân quyền người dùng.
 * Phiên bản bảo mật nâng cấp: Session Regeneration + Security Logging.
 *
 * Vai tro (role_id) trong he thong:
 *   1 = Super Admin (quan tri toan quyen)
 *   2 = Thành viên / NguoiDung (người dùng thường)
 */
class Auth
{
    public static function can(string $permission): bool
    {
        if (!self::isLoggedIn()) return false;
        return (new AuthorizationService())->can((int)Session::get('user_role_id'), $permission);
    }

    public static function requirePermission(string $permission): void
    {
        (new AuthorizationService())->require($permission);
    }
    // ==========================================
    // KIỂM TRA ĐĂNG NHẬP
    // ==========================================

    /**
     * Kiểm tra người dùng có đang đăng nhập hay không.
     *
     * @return bool True nếu đã đăng nhập
     */
    public static function isLoggedIn(): bool
    {
        $userId = (int)(Session::get('user_id') ?: 0);
        if ($userId <= 0) {
            return false;
        }

        // Mỗi session mang auth_version. Khi đổi/reset mật khẩu hoặc Admin buộc
        // đăng xuất, phiên bản trong CSDL tăng lên và mọi session cũ hết hiệu lực.
        try {
            $user = (new UserRepository())->findById($userId);
            $sessionVersion = (int)(Session::get('auth_version') ?: 1);
            if (!$user
                || $user->trang_thai !== 'hoat_dong'
                || $sessionVersion !== (int)($user->auth_version ?? 1)) {
                $_SESSION = [];
                return false;
            }
        } catch (Throwable $exception) {
            error_log('[AUTH] Không thể kiểm tra phiên đăng nhập: ' . $exception->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Yêu cầu đăng nhập trước khi truy cập trang.
     */
    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            Session::flash('error_msg', 'Bạn cần đăng nhập để xem trang này.', 'alert alert-danger');
            header('Location: ' . URL_ROOT . '/nguoi-dung/dang-nhap');
            exit;
        }
    }

    // ==========================================
    // PHÂN QUYỀN THEO VAI TRÒ
    // ==========================================

    /**
     * Yêu cầu người dùng có đúng vai trò mới được truy cập.
     *
     * @param int|array $roles ID vai tro cho phep
     */
    public static function requireRole(int|array $roles): void
    {
        self::requireLogin();

        $userRole = (int)Session::get('user_role_id');
        $roles    = (array)$roles;

        if (!in_array($userRole, $roles, true)) {
            self::logSecurityEvent('unauthorized_access', 'Role ' . $userRole . ' tried to access role-restricted area');
            Session::flash('error_msg', 'Bạn không có quyền truy cập trang này.', 'alert alert-danger');
            header('Location: ' . URL_ROOT);
            exit;
        }
    }

    // ==========================================
    // ĐĂNG NHẬP - SESSION REGENERATION (Chống Session Fixation)
    // ==========================================

    /**
     * Tạo phiên làm việc mới sau khi đăng nhập thành công.
     * session_regenerate_id(true): tạo Session ID mới và xóa session cũ
     * để chống tấn công cố định phiên (Session Fixation).
     *
     * @param object $nguoiDung Đối tượng người dùng từ CSDL
     */
    public static function dangNhap(object $nguoiDung): void
    {
        // Quan trọng: tạo lại Session ID để chống tấn công cố định phiên.
        session_regenerate_id(true);

        Session::set('user_id',      $nguoiDung->id);
        Session::set('user_email',   $nguoiDung->email);
        Session::set('user_name',    $nguoiDung->ten);
        Session::set('user_role_id', $nguoiDung->ma_vai_tro);
        // Lưu IP đăng nhập để hỗ trợ phát hiện chiếm đoạt phiên.
        Session::set('user_ip',      $_SERVER['REMOTE_ADDR'] ?? '');
        Session::set('auth_version',  (int)($nguoiDung->auth_version ?? 1));

        SystemLogger::login((string)$nguoiDung->email, 'success', null, (int)$nguoiDung->id);
        self::logSecurityEvent('login_success', 'NguoiDung #' . $nguoiDung->id . ' logged in');
    }

    // ==========================================
    // ĐĂNG XUẤT AN TOÀN
    // ==========================================

    /**
     * Hủy phiên làm việc an toàn: xóa session và cookie.
     */
    public static function dangXuat(): void
    {
        $userId = Session::get('user_id');
        $userEmail = (string)(Session::get('user_email') ?: '');
        SystemLogger::login($userEmail, 'logout', null, $userId ? (int)$userId : null);
        self::logSecurityEvent('logout', 'NguoiDung #' . $userId . ' logged out');

        // Xóa tất cả biến session.
        $_SESSION = [];

        // Xóa cookie session trên trình duyệt.
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
    }

    // ==========================================
    // KIỂM TRA IDOR (Insecure Direct Object Reference)
    // ==========================================

    /**
     * Xác minh người dùng có quyền thao tác trên tài nguyên hay không.
     * Dùng trước khi cho phép sửa hoặc xóa bản ghi.
     *
     * @param  int  $resourceOwnerId ID chu so huu tai nguyen (lay tu CSDL)
     * @return bool True neu la chu so huu hoac la Admin
     */
    public static function coChuyen(int $resourceOwnerId): bool
    {
        $userId = (int)Session::get('user_id');
        $role   = (int)Session::get('user_role_id');

        // Admin co quyen tren tat ca
        if ($role === 1) {
            return true;
        }

        // Kiểm tra người dùng có phải chủ sở hữu hay không.
        if ($userId !== $resourceOwnerId) {
            self::logSecurityEvent('idor_attempt',
                "NguoiDung #{$userId} tried to access resource owned by #{$resourceOwnerId}"
            );
            return false;
        }
        return true;
    }

    // ==========================================
    // GHI LOG BẢO MẬT
    // ==========================================

    /**
     * Ghi sự kiện bảo mật vào file log riêng, không hiển thị cho người dùng.
     *
     * @param string $event   Ten su kien (login_success, csrf_fail, idor_attempt...)
     * @param string $details Mo ta chi tiet
     */
    public static function logSecurityEvent(string $event, string $details = ''): void
    {
        $logFile = dirname(__DIR__) . '/logs/security.log';
        $ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $uri     = $_SERVER['REQUEST_URI'] ?? '';
        $time    = date('Y-m-d H:i:s');
        $line    = "[{$time}] [{$event}] IP={$ip} URI={$uri} | {$details}" . PHP_EOL;
        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
        SystemLogger::activity($event, 'security', 'security_event', null, $details);
    }
}
