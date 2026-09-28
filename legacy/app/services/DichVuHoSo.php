<?php
/**
 * ProfileService – Nghiệp vụ quản lý hồ sơ cá nhân, đổi mật khẩu và cập nhật cài đặt bảo mật.
 * Tích hợp Cache 5 phút, tự động xoá cache khi thay đổi, ghi log bảo mật và bắn Notification.
 * Tuân thủ SOLID, Service Pattern.
 */
class ProfileService
{
    private UserRepository $userRepo;
    private string $cachePrefix;

    public function __construct()
    {
        require_once APP_ROOT . '/app/repositories/UserRepository.php';
        $this->userRepo = new UserRepository();
        $this->cachePrefix = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'timnhadat_user_cache_';
    }

    /**
     * Lấy thông tin chi tiết của người dùng có Cache 5 phút.
     */
    public function getUserWithCache(int $userId): ?object
    {
        $cacheFile = $this->cachePrefix . $userId . '.cache';
        
        // Đọc cache
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 300)) {
            $content = file_get_contents($cacheFile);
            if ($content !== false) {
                return json_decode($content);
            }
        }

        // Lấy DB và ghi đè cache
        $user = $this->userRepo->findById($userId);
        if ($user) {
            if (!function_exists('file_put_to_file_safe')) {
                require_once APP_ROOT . '/app/services/DichVuYeuThich.php';
            }
            file_put_to_file_safe($cacheFile, json_encode($user));
        }

        return $user ?: null;
    }

    /**
     * Xóa cache của một người dùng.
     */
    public function clearUserCache(int $userId): void
    {
        $cacheFile = $this->cachePrefix . $userId . '.cache';
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
    }

    /**
     * Cập nhật thông tin cá nhân.
     */
    public function updateProfile(int $userId, array $data): array
    {
        // Chỉ kiểm tra trùng khi người dùng thực sự đổi sang số điện thoại khác.
        // Dữ liệu cũ có thể đã trùng từ trước; không được chặn cập nhật các trường còn lại.
        $currentUser = $this->userRepo->findById($userId);
        $currentPhone = trim((string)($currentUser->dien_thoai ?? ''));
        $newPhone = trim((string)($data['dien_thoai'] ?? ''));
        if ($newPhone !== '' && $newPhone !== $currentPhone) {
            $existing = $this->userRepo->findByPhone($newPhone);
            if ($existing && (int)$existing->id !== $userId) {
                return ['success' => false, 'message' => 'Số điện thoại này đã được sử dụng bởi tài khoản khác.'];
            }
        }

        // 2. Cập nhật các trường
        $fields = [
            'ten' => $data['ten'] ?? '',
            'dien_thoai' => $data['dien_thoai'] ?? '',
            'ngay_sinh' => !empty($data['ngay_sinh']) ? $data['ngay_sinh'] : null,
            'gioi_tinh' => $data['gioi_tinh'] ?? null,
            'dia_chi' => $data['dia_chi'] ?? null,
            'nghe_nghiep' => $data['nghe_nghiep'] ?? null,
            'mo_ta_ca_nhan' => $data['mo_ta_ca_nhan'] ?? null,
            'khu_vuc_hoat_dong' => $data['khu_vuc_hoat_dong'] ?? null,
        ];

        $successCount = 0;
        foreach ($fields as $column => $value) {
            if ($this->userRepo->updateField($userId, $column, $value)) {
                $successCount++;
            }
        }

        if ($successCount > 0) {
            $this->clearUserCache($userId);
            
            // Ghi nhận Activity Log
            AuditMiddleware::enrich('Cập nhật hồ sơ cá nhân', [], $fields, 'nguoi_dung', $userId);
            
            // Gửi Notification
            $this->sendNotification($userId, 'Cập nhật thông tin', 'Thông tin hồ sơ cá nhân của bạn đã được cập nhật thành công.');
            
            return ['success' => true, 'message' => 'Cập nhật hồ sơ cá nhân thành công.'];
        }

        return ['success' => false, 'message' => 'Không có thông tin nào thay đổi hoặc có lỗi xảy ra.'];

    }

    /**
     * Cập nhật các cài đặt tài khoản (Bảo mật & Riêng tư).
     */
    public function updateSettings(int $userId, array $data): array
    {
        $settings = [
            'nhan_email'        => isset($data['nhan_email']) ? (int)$data['nhan_email'] : 1,
            'nhan_notification' => isset($data['nhan_notification']) ? (int)$data['nhan_notification'] : 1,
            'an_sdt'            => isset($data['an_sdt']) ? (int)$data['an_sdt'] : 0,
            'an_email'          => isset($data['an_email']) ? (int)$data['an_email'] : 0,
            'cho_phep_chat'     => isset($data['cho_phep_chat']) ? (int)$data['cho_phep_chat'] : 1,
            'cho_phep_goi'      => isset($data['cho_phep_goi']) ? (int)$data['cho_phep_goi'] : 1,
        ];

        foreach ($settings as $col => $val) {
            $this->userRepo->updateField($userId, $col, $val);
        }

        $this->clearUserCache($userId);
        
        // Ghi nhận Activity Log
        AuditMiddleware::enrich('Cập nhật cài đặt riêng tư', [], $settings, 'nguoi_dung', $userId);
        
        return ['success' => true, 'message' => 'Cập nhật cài đặt tài khoản thành công.'];
    }

    /**
     * Thay đổi mật khẩu người dùng.
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword, bool $logoutOthers): array
    {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Người dùng không tồn tại.'];
        }

        // 1. Kiểm tra mật khẩu hiện tại
        if (!password_verify($currentPassword, $user->mat_khau)) {
            return ['success' => false, 'message' => 'Mật khẩu hiện tại không chính xác.'];
        }

        // 2. Kiểm tra mật khẩu mới không được trùng mật khẩu cũ
        if ($currentPassword === $newPassword) {
            return ['success' => false, 'message' => 'Mật khẩu mới không được trùng với mật khẩu hiện tại.'];
        }

        // 3. Hash mật khẩu bằng bcrypt hoặc Argon2 (mặc định là PASSWORD_DEFAULT)
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        
        if ($this->userRepo->updatePassword($userId, $hashed)) {
            $this->clearUserCache($userId);
            
            // Ghi nhận sự kiện bảo mật
            AuditMiddleware::enrich('Đổi mật khẩu tài khoản', [], [], 'nguoi_dung', $userId);
            Auth::logSecurityEvent('password_changed', 'NguoiDung #' . $userId . ' changed password');
            
            // Gửi thông báo
            $this->sendNotification($userId, 'Thay đổi mật khẩu', 'Mật khẩu tài khoản của bạn đã được thay đổi thành công.');

            if ($logoutOthers) {
                // Tăng auth_version trong DB -> Đăng xuất toàn bộ thiết bị khác
                $this->userRepo->invalidateAllSessions($userId);
                
                // Cập nhật lại auth_version trong DB cho session hiện tại để giữ đăng nhập
                $freshUser = $this->userRepo->findById($userId);
                $newVersion = (int)($freshUser->auth_version ?? 1);
                Session::set('auth_version', $newVersion);
            }

            return ['success' => true, 'message' => 'Đổi mật khẩu thành công.'];
        }

        return ['success' => false, 'message' => 'Lỗi hệ thống khi cập nhật mật khẩu. Vui lòng thử lại.'];
    }

    /**
     * Gửi Notification hệ thống tới người dùng.
     */
    private function sendNotification(int $userId, string $title, string $content): void
    {
        $db = new Database();
        $db->query("
            INSERT INTO thong_bao (ma_nguoi_dung, tieu_de, noi_dung, da_doc, ngay_tao)
            VALUES (:uid, :title, :content, 0, NOW())
        ");
        $db->bind(':uid', $userId, PDO::PARAM_INT);
        $db->bind(':title', $title);
        $db->bind(':content', $content);
        $db->execute();
    }
}
