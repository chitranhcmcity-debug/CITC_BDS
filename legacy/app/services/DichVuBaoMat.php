<?php
/**
 * SecurityService – Trích xuất thông tin thiết bị (OS, Browser, Device Type) từ User-Agent và xác định vị trí IP.
 * Tuân thủ SOLID, Service Pattern.
 */
class SecurityService
{
    /**
     * Phân tích User-Agent để lấy thông tin hệ điều hành (OS), trình duyệt (Browser), loại thiết bị (Device Type).
     */
    public function parseUserAgent(string $userAgent): array
    {
        $os = 'Unknown OS';
        $browser = 'Unknown Browser';
        $deviceType = 'desktop';

        // 1. Phân tích OS
        $osArray = [
            '/windows nt 10/i'      => 'Windows 10/11',
            '/windows nt 6.3/i'     => 'Windows 8.1',
            '/windows nt 6.2/i'     => 'Windows 8',
            '/windows nt 6.1/i'     => 'Windows 7',
            '/macintosh|mac os x/i' => 'macOS',
            '/android/i'            => 'Android',
            '/iphone/i'             => 'iPhone (iOS)',
            '/ipad/i'               => 'iPad (iOS)',
            '/linux/i'              => 'Linux',
            '/ubuntu/i'             => 'Ubuntu'
        ];

        foreach ($osArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $os = $value;
                break;
            }
        }

        // 2. Phân tích Browser
        $browserArray = [
            '/msie/i'      => 'Internet Explorer',
            '/firefox/i'   => 'Firefox',
            '/safari/i'    => 'Safari',
            '/chrome/i'    => 'Chrome',
            '/edge/i'      => 'Edge',
            '/opera/i'     => 'Opera',
            '/netscape/i'  => 'Netscape',
            '/maxthon/i'   => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i'    => 'Handheld Browser'
        ];

        foreach ($browserArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $browser = $value;
                break;
            }
        }

        // Tránh nhầm lẫn Safari và Chrome (Chrome gửi cả Safari trong UA của nó)
        if (str_contains(strtolower($userAgent), 'chrome') && str_contains(strtolower($userAgent), 'safari')) {
            $browser = 'Chrome';
        }
        if (str_contains(strtolower($userAgent), 'edge') || str_contains(strtolower($userAgent), 'edg')) {
            $browser = 'Edge';
        }

        // 3. Xác định Device Type
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobi))/i', $userAgent)) {
            $deviceType = 'tablet';
        } elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $userAgent)) {
            $deviceType = 'mobile';
        }

        return [
            'os'          => $os,
            'browser'     => $browser,
            'device_type' => $deviceType,
            'platform'    => $os
        ];
    }

    /**
     * Lấy địa chỉ IP thực của client.
     * Kiểm tra proxy headers trước, fallback sang REMOTE_ADDR.
     */
    public function getClientIp(): string
    {
        // Ưu tiên header proxy (nếu server đứng sau load balancer / reverse proxy)
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            $value = $_SERVER[$header] ?? null;
            if (!empty($value)) {
                // X-Forwarded-For có thể chứa nhiều IP, lấy cái đầu tiên
                $ips = explode(',', $value);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Kiểm tra trạng thái tài khoản trước khi cho phép đăng nhập.
     * Trả về null nếu tài khoản hợp lệ, hoặc chuỗi lỗi nếu bị chặn.
     */
    public function checkAccountStatus(object $user): ?string
    {
        $status = $user->trang_thai ?? $user->status ?? 'hoat_dong';

        if ($status === 'ngung_hoat_dong' || $status === 'inactive') {
            return 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.';
        }

        if ($status === 'bi_cam' || $status === 'banned') {
            return 'Tài khoản của bạn đã bị cấm truy cập hệ thống.';
        }

        // Kiểm tra khóa do đăng nhập sai quá nhiều lần
        if ($this->isLocked($user)) {
            return 'Tài khoản tạm thời bị khóa do đăng nhập sai quá nhiều lần. Vui lòng thử lại sau 15 phút.';
        }

        return null;
    }

    /**
     * Kiểm tra tài khoản có bị khóa tạm thời do đăng nhập sai nhiều lần.
     * Khóa nếu >= 5 lần sai và lần sai cuối cùng chưa quá 15 phút.
     */
    public function isLocked(object $user): bool
    {
        $maxAttempts = 5;
        $lockoutMinutes = 15;

        $attempts = (int)($user->login_attempts ?? 0);
        if ($attempts < $maxAttempts) {
            return false;
        }

        // Kiểm tra thời gian khóa (nếu có trường locked_until hoặc last_failed_at)
        $lockedUntil = $user->locked_until ?? null;
        if ($lockedUntil && strtotime($lockedUntil) > time()) {
            return true;
        }

        $lastFailed = $user->last_failed_at ?? $user->ngay_cap_nhat ?? null;
        if ($lastFailed) {
            $diff = time() - strtotime($lastFailed);
            return $diff < ($lockoutMinutes * 60);
        }

        return $attempts >= $maxAttempts;
    }

    /**
     * Kiểm tra email đã xác thực chưa.
     */
    public function isEmailVerified(object $user): bool
    {
        // Kiểm tra trường email_verified hoặc email_verified_at
        if (isset($user->email_verified)) {
            return (bool)$user->email_verified;
        }
        if (isset($user->email_verified_at) && !empty($user->email_verified_at)) {
            return true;
        }
        if (isset($user->xac_thuc_email)) {
            return (bool)$user->xac_thuc_email;
        }
        // Mặc định coi là đã xác thực nếu không có trường
        return true;
    }

    /**
     * Kiểm tra có phải thiết bị mới (IP khác lần đăng nhập gần nhất).
     */
    public function isNewDevice(array $loginData, ?string $lastIP): bool
    {
        if (empty($lastIP)) {
            return false; // Lần đầu tiên đăng nhập, không coi là thiết bị mới
        }
        $currentIP = $loginData['ip_address'] ?? '';
        return $currentIP !== $lastIP;
    }

    /**
     * Xác định vị trí địa lý dựa trên IP Address.
     * Vì chạy localhost, mặc định trả về vị trí giả lập hoặc gọi API nếu có mạng.
     */
    public function getLocationFromIp(string $ip): array
    {
        $location = 'Hà Nội, Việt Nam';
        $country = 'Việt Nam';

        if ($ip === '127.0.0.1' || $ip === '::1') {
            return ['location' => 'Localhost', 'country' => 'Localhost'];
        }

        // Thử lấy thông tin từ ip-api.com (timeout ngắn để tránh treo trang)
        try {
            $ctx = stream_context_create(['http' => ['timeout' => 1.5]]);
            $resJson = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,city", false, $ctx);
            if ($resJson !== false) {
                $res = json_decode($resJson);
                if (($res->status ?? '') === 'success') {
                    $location = ($res->city ?? 'Unknown City') . ', ' . ($res->country ?? 'Unknown Country');
                    $country = $res->country ?? 'Unknown Country';
                }
            }
        } catch (Exception $e) {
            // Fallback default
        }

        return ['location' => $location, 'country' => $country];
    }

    /**
     * Admin đặt lại mật khẩu cho người dùng.
     */
    public function adminResetPassword(int $userId, string $newPassword, int $adminId): bool
    {
        require_once APP_ROOT . '/app/repositories/UserRepository.php';
        $userRepo = new UserRepository();

        $user = $userRepo->findById($userId);
        if (!$user) return false;

        $hash = password_hash($newPassword, PASSWORD_ARGON2ID);
        $ok = $userRepo->adminResetPassword($userId, $hash);
        if ($ok) {
            SystemLogger::admin(
                'reset_password', 
                'nguoi_dung', 
                'nguoi_dung', 
                $userId, 
                "Đặt lại mật khẩu cho người dùng: #{$userId} - {$user->ten}", 
                [], 
                [], 
                $adminId
            );

            // Gửi email & notification
            require_once APP_ROOT . '/app/services/DichVuThongBao.php';
            require_once APP_ROOT . '/app/services/DichVuThuDienTu.php';
            
            $notifSvc = new NotificationService();
            $mailSvc = new MailService();

            $title = "Mật khẩu của bạn đã được thay đổi";
            $content = "Mật khẩu tài khoản của bạn đã được thay đổi bởi quản trị viên hệ thống. Vui lòng liên hệ hỗ trợ nếu bạn không yêu cầu thay đổi này.";
            
            $notifSvc->send($userId, $title, $content, 'bao_mat');
            if (!empty($user->email)) {
                $mailSvc->sendNotificationEmail($user->email, $title, $content);
            }
        }
        return $ok;
    }
}
