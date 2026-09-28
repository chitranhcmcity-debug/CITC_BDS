<?php

/**
 * ProfileController – Quản lý Hồ sơ cá nhân, Đổi mật khẩu, Lịch sử đăng nhập, Xác thực OTP và cài đặt riêng tư.
 * Tuân thủ SOLID, tách biệt rõ ràng Controller, Service, Repository.
 */
class ProfileController extends Controller
{
    private ProfileService $profileSvc;

    private OTPService $otpSvc;

    private MailService $mailSvc;

    private SMSService $smsSvc;

    private SecurityService $securitySvc;

    private LoginHistoryRepository $historyRepo;

    public function __construct()
    {
        require_once APP_ROOT.'/app/middleware/AuthMiddleware.php';
        require_once APP_ROOT.'/app/services/DichVuHoSo.php';
        require_once APP_ROOT.'/app/services/DichVuOTP.php';
        require_once APP_ROOT.'/app/services/DichVuThuDienTu.php';
        require_once APP_ROOT.'/app/services/DichVuSMS.php';
        require_once APP_ROOT.'/app/services/DichVuBaoMat.php';
        require_once APP_ROOT.'/app/repositories/LoginHistoryRepository.php';

        $this->profileSvc = new ProfileService;
        $this->otpSvc = new OTPService;
        $this->mailSvc = new MailService;
        $this->smsSvc = new SMSService;
        $this->securitySvc = new SecurityService;
        $this->historyRepo = new LoginHistoryRepository;
    }

    /**
     * Bộ định tuyến Profile (GET hiển thị, POST cập nhật).
     * URL: /nguoi-dung/profile
     */
    public function profileRouter(): void
    {
        AuthMiddleware::handle(true);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update();
        } else {
            $this->index();
        }
    }

    /**
     * GET /nguoi-dung/profile
     * Hiển thị trang Hồ sơ cá nhân.
     */
    public function index(): void
    {
        $userId = (int) Session::get('user_id');
        $user = $this->profileSvc->getUserWithCache($userId);

        $this->view('profile/index', [
            'title' => 'Hồ Sơ Cá Nhân – TimNhaDat.site',
            'user' => $user,
        ]);
    }

    /**
     * POST /nguoi-dung/profile
     * Xử lý cập nhật thông tin cá nhân hoặc cài đặt tài khoản.
     */
    public function update(): void
    {
        Csrf::verify();
        $userId = (int) Session::get('user_id');

        // Xác định loại cập nhật: 'profile' (thông tin) hoặc 'settings' (cài đặt riêng tư)
        $action = $_POST['action'] ?? 'profile';

        if ($action === 'settings') {
            // Cập nhật cài đặt riêng tư
            $settingsData = [
                'nhan_email' => isset($_POST['nhan_email']) ? 1 : 0,
                'nhan_notification' => isset($_POST['nhan_notification']) ? 1 : 0,
                'an_sdt' => isset($_POST['an_sdt']) ? 1 : 0,
                'an_email' => isset($_POST['an_email']) ? 1 : 0,
                'cho_phep_chat' => isset($_POST['cho_phep_chat']) ? 1 : 0,
                'cho_phep_goi' => isset($_POST['cho_phep_goi']) ? 1 : 0,
            ];
            $res = $this->profileSvc->updateSettings($userId, $settingsData);
        } else {
            // Cập nhật thông tin cá nhân
            $name = trim($_POST['ten'] ?? '');
            $phone = trim($_POST['dien_thoai'] ?? '');

            // Validation cơ bản
            if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
                Session::set('error', 'Họ tên phải từ 2 đến 100 ký tự.');
                $this->redirect('nguoi-dung/profile');

                return;
            }

            if (! empty($phone) && ! preg_match('/^[0-9]{10,11}$/', $phone)) {
                Session::set('error', 'Số điện thoại không đúng định dạng (10 - 11 chữ số).');
                $this->redirect('nguoi-dung/profile');

                return;
            }

            $birthDateInput = trim((string) ($_POST['ngay_sinh'] ?? ''));
            $birthDate = null;
            if ($birthDateInput !== '') {
                $parsedBirthDate = DateTime::createFromFormat('!d/m/Y', $birthDateInput);
                $dateErrors = DateTime::getLastErrors();
                $isValidDate = $parsedBirthDate !== false
                    && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))
                    && $parsedBirthDate->format('d/m/Y') === $birthDateInput
                    && $parsedBirthDate <= new DateTime('today');
                if (! $isValidDate) {
                    Session::set('error', 'Ngày sinh không hợp lệ. Vui lòng nhập theo định dạng dd/mm/yyyy.');
                    $this->redirect('nguoi-dung/profile');

                    return;
                }
                $birthDate = $parsedBirthDate->format('Y-m-d');
            }

            $profileData = [
                'ten' => $name,
                'dien_thoai' => $phone,
                'ngay_sinh' => $birthDate,
                'gioi_tinh' => $_POST['gioi_tinh'] ?? null,
                'dia_chi' => trim($_POST['dia_chi'] ?? ''),
                'nghe_nghiep' => trim($_POST['nghe_nghiep'] ?? ''),
                'mo_ta_ca_nhan' => trim($_POST['mo_ta_ca_nhan'] ?? ''),
                'khu_vuc_hoat_dong' => trim($_POST['khu_vuc_hoat_dong'] ?? ''),
            ];

            $res = $this->profileSvc->updateProfile($userId, $profileData);
        }

        if ($res['success']) {
            Session::set('success', $res['message']);
            // Cập nhật lại tên hiển thị trong Session
            if ($action !== 'settings') {
                Session::set('user_name', trim($_POST['ten']));
            }
        } else {
            Session::set('error', $res['message']);
        }

        $this->redirect('nguoi-dung/profile');
    }

    /**
     * POST /nguoi-dung/uploadAvatar
     * Tải lên và xử lý ảnh đại diện (Resizing, Crop, Compress WebP).
     */
    public function uploadAvatar(): void
    {
        AuthMiddleware::handle(true);
        Csrf::verify();
        $userId = (int) Session::get('user_id');

        $user = $this->profileSvc->getUserWithCache($userId);
        if (! $user) {
            $this->redirect('nguoi-dung/profile');

            return;
        }

        // Kiểm tra xem có dữ liệu ảnh cắt sẵn Base64 hay không (từ CropperJS)
        $croppedBase64 = $_POST['avatar_cropped'] ?? '';
        $maxBytes = 2 * 1024 * 1024;
        $raw = '';

        if (! empty($croppedBase64)) {
            if (strlen($croppedBase64) > (int) ceil($maxBytes * 4 / 3) + 128
                || ! preg_match('#^data:image/(?:jpeg|png|webp);base64,([A-Za-z0-9+/=\r\n]+)$#', $croppedBase64, $matches)) {
                Session::set('error', 'Dữ liệu ảnh không hợp lệ.');
                $this->redirect('nguoi-dung/profile');

                return;
            }
            $raw = base64_decode($matches[1], true) ?: '';
        } else {
            // Tải file thường nếu không dùng CropperJS
            if (empty($_FILES['avatar']['name']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                Session::set('error', 'Vui lòng chọn ảnh đại diện để tải lên.');
                $this->redirect('nguoi-dung/profile');

                return;
            }

            $tmp = $_FILES['avatar']['tmp_name'];
            $size = $_FILES['avatar']['size'];
            if ($size <= 0 || $size > $maxBytes) {
                Session::set('error', 'Dung lượng ảnh tối đa là 2MB.');
                $this->redirect('nguoi-dung/profile');

                return;
            }

            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
            if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                Session::set('error', 'Định dạng ảnh chỉ chấp nhận JPG, PNG, WEBP.');
                $this->redirect('nguoi-dung/profile');

                return;
            }

            $raw = file_get_contents($tmp) ?: '';
        }

        $imageInfo = $raw !== '' ? @getimagesizefromstring($raw) : false;
        $width = (int) ($imageInfo[0] ?? 0);
        $height = (int) ($imageInfo[1] ?? 0);
        if (strlen($raw) > $maxBytes || ! $imageInfo || $width <= 0 || $height <= 0
            || $width > 8000 || $height > 8000 || ($width * $height) > 24000000) {
            Session::set('error', 'Ảnh không hợp lệ hoặc có kích thước quá lớn.');
            $this->redirect('nguoi-dung/profile');

            return;
        }

        $uploadDir = rtrim(UPLOAD_ROOT_DIR, '/\\').DIRECTORY_SEPARATOR;
        if ((! is_dir($uploadDir) && ! mkdir($uploadDir, 0755, true)) || ! is_writable($uploadDir)) {
            Session::set('error', 'Thư mục lưu ảnh không khả dụng.');
            $this->redirect('nguoi-dung/profile');

            return;
        }

        $fileName = 'avatar_'.$userId.'_'.bin2hex(random_bytes(8)).'.webp';
        $newPath = $uploadDir.$fileName;
        $im = @imagecreatefromstring($raw);
        $canvas = $im !== false ? imagecreatetruecolor(300, 300) : false;
        if ($im === false || $canvas === false) {
            if ($im !== false) {
                imagedestroy($im);
            }
            Session::set('error', 'Không thể xử lý ảnh đại diện.');
            $this->redirect('nguoi-dung/profile');

            return;
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $im, 0, 0, 0, 0, 300, 300, $width, $height);
        $saved = imagewebp($canvas, $newPath, 80);
        imagedestroy($canvas);
        imagedestroy($im);
        if (! $saved || ! is_file($newPath)) {
            @unlink($newPath);
            Session::set('error', 'Không thể lưu ảnh đại diện.');
            $this->redirect('nguoi-dung/profile');

            return;
        }

        // Cập nhật CSDL
        $userRepo = new UserRepository;
        $oldAvatar = $user->anh_dai_dien ?? '';

        if ($userRepo->updateField($userId, 'anh_dai_dien', $fileName)) {
            // Xóa ảnh cũ trên ổ đĩa để tránh tốn dung lượng
            if (! empty($oldAvatar) && $oldAvatar !== 'default-avatar.png') {
                $oldPath = $uploadDir.basename($oldAvatar);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $this->profileSvc->clearUserCache($userId);

            // Ghi nhận Activity log
            AuditMiddleware::enrich('Cập nhật ảnh đại diện', [], ['avatar' => $fileName], 'nguoi_dung', $userId);

            Session::set('success', 'Đổi ảnh đại diện thành công!');
        } else {
            @unlink($newPath);
            Session::set('error', 'Lỗi lưu thông tin ảnh đại diện vào hệ thống.');
        }

        $this->redirect('nguoi-dung/profile');
    }

    /**
     * GET/POST /nguoi-dung/change_password
     * Thay đổi mật khẩu người dùng.
     */
    public function changePassword(): void
    {
        AuthMiddleware::handle(true);

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->view('profile/change-password', [
                'title' => 'Đổi Mật Khẩu – TimNhaDat.site',
            ]);

            return;
        }

        Csrf::verify();
        $userId = (int) Session::get('user_id');

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $logoutOthers = isset($_POST['logout_others']);

        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            Session::set('error', 'Vui lòng điền đầy đủ tất cả các trường mật khẩu.');
            $this->redirect('nguoi-dung/change_password');

            return;
        }

        if (strlen($newPassword) < 6) {
            Session::set('error', 'Mật khẩu mới phải từ 6 ký tự trở lên.');
            $this->redirect('nguoi-dung/change_password');

            return;
        }

        if ($newPassword !== $confirmPassword) {
            Session::set('error', 'Mật khẩu xác nhận không trùng khớp.');
            $this->redirect('nguoi-dung/change_password');

            return;
        }

        $res = $this->profileSvc->changePassword($userId, $currentPassword, $newPassword, $logoutOthers);

        if ($res['success']) {
            Session::set('success', $res['message']);
        } else {
            Session::set('error', $res['message']);
        }

        $this->redirect('nguoi-dung/change_password');
    }

    /**
     * GET /nguoi-dung/loginHistory
     * Hiển thị danh sách lịch sử đăng nhập.
     */
    public function loginHistory(): void
    {
        AuthMiddleware::handle(true);
        $userId = (int) Session::get('user_id');

        // Phân trang lịch sử đăng nhập
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $history = $this->historyRepo->getHistory($userId, $limit, $offset);
        $total = $this->historyRepo->countHistory($userId);
        $totalPages = max(1, (int) ceil($total / $limit));

        $this->view('profile/login-history', [
            'title' => 'Lịch Sử Đăng Nhập – TimNhaDat.site',
            'history' => $history,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * POST /nguoi-dung/logoutAllDevice
     * Đăng xuất khỏi mọi thiết bị khác hoặc tất cả.
     */
    public function logoutAllDevice(): void
    {
        AuthMiddleware::handle(true);
        Csrf::verify();
        $userId = (int) Session::get('user_id');

        $keepCurrent = isset($_POST['keep_current']) && $_POST['keep_current'] == '1';

        $userRepo = new UserRepository;
        $userRepo->invalidateAllSessions($userId);

        // Ghi nhận sự kiện hoạt động
        AuditMiddleware::enrich('Đăng xuất tất cả thiết bị', [], ['keep_current' => $keepCurrent], 'nguoi_dung', $userId);

        if ($keepCurrent) {
            // Đồng bộ phiên hiện hành bằng cách lấy auth_version mới nhất từ DB
            $user = $userRepo->findById($userId);
            Session::set('auth_version', (int) ($user->auth_version ?? 1));
            Session::set('success', 'Đã đăng xuất tài khoản khỏi tất cả các thiết bị khác.');
            $this->redirect('nguoi-dung/profile#security-tab');
        } else {
            // Đăng xuất hoàn toàn cả thiết bị hiện tại
            Session::destroy();
            $this->redirect('nguoi-dung/dang-nhap');
        }
    }

    /**
     * POST /nguoi-dung/sendVerifyEmail
     * Tạo liên kết xác thực tài khoản và gửi qua Email.
     */
    public function verifyEmail(): void
    {
        AuthMiddleware::handle(true);
        Csrf::verify();
        $userId = (int) Session::get('user_id');

        $user = $this->profileSvc->getUserWithCache($userId);
        if (! $user || empty($user->email)) {
            $this->json(['success' => false, 'message' => 'Email của tài khoản trống hoặc không hợp lệ.'], 400);

            return;
        }

        // Tạo token xác thực
        $rawToken = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $rawToken);
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Lưu vào bảng ma_truy_cap_nguoi_dung
        $db = new Database;
        // Vô hiệu hóa các token xác thực email cũ
        $db->query("UPDATE ma_truy_cap_nguoi_dung SET expires_at = NOW() WHERE user_id = :uid AND type = 'email_verify' AND used_at IS NULL");
        $db->bind(':uid', $userId);
        $db->execute();

        $db->query("
            INSERT INTO ma_truy_cap_nguoi_dung (user_id, token, type, expires_at, created_at)
            VALUES (:uid, :token, 'email_verify', :expires, NOW())
        ");
        $db->bind(':uid', $userId);
        $db->bind(':token', $hashedToken);
        $db->bind(':expires', $expires);

        if ($db->execute()) {
            // Gửi email
            $ok = $this->mailSvc->sendVerificationEmail($user->email, $rawToken);
            if ($ok) {
                $this->json(['success' => true, 'message' => 'Đã gửi email xác thực, vui lòng kiểm tra hộp thư đến của bạn.']);
            } else {
                $this->json(['success' => false, 'message' => 'Lỗi kết nối máy chủ gửi thư SMTP. Vui lòng thử lại sau.'], 500);
            }
        } else {
            $this->json(['success' => false, 'message' => 'Lỗi hệ thống khi sinh mã xác thực.'], 500);
        }
    }

    /**
     * POST /nguoi-dung/sendOTP
     * Gửi mã OTP xác thực SĐT.
     */
    public function sendOTP(): void
    {
        AuthMiddleware::handle(true);
        Csrf::verify();
        $userId = (int) Session::get('user_id');

        $user = $this->profileSvc->getUserWithCache($userId);
        if (! $user) {
            $this->json(['success' => false, 'message' => 'Người dùng không tồn tại.'], 400);

            return;
        }

        if (empty($user->dien_thoai)) {
            $this->json(['success' => false, 'message' => 'Vui lòng cập nhật số điện thoại trước khi xác thực.'], 400);

            return;
        }

        $lastSentAt = (int) (Session::get('phone_otp_sent_at') ?: 0);
        $retryAfter = 60 - (time() - $lastSentAt);
        if ($retryAfter > 0) {
            $this->json(['success' => false, 'message' => "Vui lòng chờ {$retryAfter} giây trước khi gửi lại mã OTP."], 429);

            return;
        }

        // Sinh mã OTP và gửi trực tiếp đến số điện thoại qua SMS gateway.
        $otp = $this->otpSvc->generateOTP($userId, 'phone_verify');
        $delivery = $this->smsSvc->sendOTP((string) $user->dien_thoai, $otp);
        if (! $delivery['success']) {
            $this->otpSvc->invalidateOTP($userId, 'phone_verify');
            $this->json($delivery, 502);

            return;
        }

        Session::set('phone_otp_sent_at', time());
        $response = [
            'success' => true,
            'message' => $delivery['message'],
            'delivery' => $delivery['delivery'] ?? 'sms',
        ];

        // Chỉ hiển thị mã xem trước khi service xác nhận đang ở local.
        if (($delivery['delivery'] ?? '') === 'local_preview') {
            $response['debug_otp'] = $otp;
        }

        $this->json($response);
    }

    /**
     * POST /nguoi-dung/verifyOTP
     * Xác thực số điện thoại bằng mã OTP.
     */
    public function verifyOTP(): void
    {
        AuthMiddleware::handle(true);
        Csrf::verify();
        $userId = (int) Session::get('user_id');

        $otp = trim($_POST['otp'] ?? '');
        if (empty($otp)) {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập mã OTP.'], 400);

            return;
        }

        $res = $this->otpSvc->verifyOTP($userId, $otp, 'phone_verify');

        if ($res['success']) {
            // Cập nhật CSDL đánh dấu đã xác minh số điện thoại
            $userRepo = new UserRepository;
            $userRepo->updateField($userId, 'phone_verified', 1);
            $this->profileSvc->clearUserCache($userId);

            // Ghi nhận hoạt động bảo mật
            AuditMiddleware::enrich('Xác thực số điện thoại', [], [], 'nguoi_dung', $userId);

            $this->json(['success' => true, 'message' => 'Xác thực số điện thoại thành công!']);
        } else {
            $this->json(['success' => false, 'message' => $res['message']], 400);
        }
    }

    private function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
