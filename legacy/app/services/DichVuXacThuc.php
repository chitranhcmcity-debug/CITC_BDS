<?php
/**
 * AuthService – Business Logic toàn bộ module xác thực.
 * Phối hợp các Repository, TokenService, OTPService, MailService, SecurityService.
 *
 * KHÔNG thao tác trực tiếp với $_SESSION hoặc HTTP headers.
 * Controller sẽ đọc kết quả và thực hiện redirect/session.
 */
class AuthService
{
    private UserRepository   $userRepo;
    private TokenService     $tokenSvc;
    private OTPService       $otpSvc;
    private MailService      $mailSvc;
    private SecurityService  $security;
    private LoginRepository  $loginRepo;

    public function __construct()
    {
        $this->userRepo  = new UserRepository();
        $this->tokenSvc  = new TokenService();
        $this->otpSvc    = new OTPService();
        $this->mailSvc   = new MailService();
        $this->security  = new SecurityService();
        $this->loginRepo = new LoginRepository();
    }

    /* ══════════════════════════════════════════════
       ĐĂNG KÝ
    ══════════════════════════════════════════════ */

    /**
     * Tạo tài khoản mới, gửi email xác thực.
     * @param array $data đã được validate bởi AuthValidation
     * @return array ['success'=>bool, 'message'=>string, 'user_id'=>int|null]
     */
    public function register(array $data): array
    {
        $hashedPass = password_hash($data['mat_khau'], PASSWORD_ARGON2ID);

        $userId = $this->userRepo->create([
            'ten'        => $data['ten'],
            'email'      => $data['email'],
            'dien_thoai' => $data['dien_thoai'] ?? '',
            'mat_khau'   => $hashedPass,
                'ma_vai_tro' => Role::MEMBER,
        ]);

        if (!$userId) {
            return ['success' => false, 'message' => 'Không thể tạo tài khoản. Vui lòng thử lại.', 'user_id' => null];
        }

        // Gửi OTP xác thực email; link cũ vẫn được hỗ trợ cho các email đã gửi.
        $user = $this->userRepo->findById($userId);
        $otpResult = $this->otpSvc->generate($user, 'login');

        return [
            'success' => true,
            'message' => !empty($otpResult['success'])
                ? $otpResult['message']
                : 'Tài khoản đã được tạo nhưng chưa thể gửi OTP. Vui lòng đăng nhập để yêu cầu gửi lại.',
            'user_id' => $userId,
            'otp_required' => !empty($otpResult['success']),
        ];
    }

    /* ══════════════════════════════════════════════
       ĐĂNG NHẬP
    ══════════════════════════════════════════════ */

    /**
     * Xác thực thông tin đăng nhập.
     * @param string $identifier email hoặc SĐT
     * @param string $password   mật khẩu thô
     * @param bool   $remember   ghi nhớ đăng nhập
     * @return array [
     *   'success'       => bool,
     *   'message'       => string,
     *   'user'          => object|null,
     *   'remember_token'=> string|null,
     *   'captcha_needed'=> bool,
     * ]
     */
    public function login(string $identifier, string $password, bool $remember = false): array
    {
        $ua      = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip      = $this->security->getClientIp();
        $uaInfo  = $this->security->parseUserAgent($ua);

        $loginData = [
            'ip_address'  => $ip,
            'user_agent'  => $ua,
            'device_type' => $uaInfo['device_type'],
            'os'          => $uaInfo['os'],
            'browser'     => $uaInfo['browser'],
            'remember_me' => $remember ? 1 : 0,
        ];

        // Tìm user
        $user = $this->userRepo->findByEmailOrPhone($identifier);

        if (!$user) {
            $this->loginRepo->record(array_merge($loginData, [
                'email'  => $identifier,
                'status' => 'failed',
                'notes'  => 'Không tìm thấy tài khoản',
            ]));
            return ['success' => false, 'message' => 'Email/SĐT hoặc mật khẩu không đúng.', 'user' => null, 'captcha_needed' => false];
        }

        $loginData['user_id'] = (int)$user->id;
        $loginData['email']   = $user->email;

        // Kiểm tra trạng thái tài khoản
        $statusError = $this->security->checkAccountStatus($user);
        if ($statusError) {
            $this->loginRepo->record(array_merge($loginData, ['status' => 'blocked', 'notes' => $statusError]));
            $captchaNeeded = (int)($user->login_attempts ?? 0) >= 3;
            return ['success' => false, 'message' => $statusError, 'user' => null, 'captcha_needed' => $captchaNeeded];
        }

        // Xác thực mật khẩu
        if (!password_verify($password, $user->mat_khau)) {
            $this->userRepo->incrementLoginAttempts((int)$user->id);
            // Reload để lấy attempts mới nhất
            $user = $this->userRepo->findById((int)$user->id);
            $attempts = (int)($user->login_attempts ?? 0);
            $captchaNeeded = $attempts >= 3;

            // Gửi email thông báo khóa nếu bị khóa
            if ($this->security->isLocked($user)) {
                $this->mailSvc->sendAccountLocked($user);
            }

            $this->loginRepo->record(array_merge($loginData, [
                'status' => 'failed',
                'notes'  => "Sai mật khẩu (lần {$attempts})",
            ]));

            return [
                'success'        => false,
                'message'        => 'Email/SĐT hoặc mật khẩu không đúng.',
                'user'           => null,
                'captcha_needed' => $captchaNeeded,
            ];
        }

        // Kiểm tra email đã xác thực chưa
        if (!$this->security->isEmailVerified($user)) {
            return [
                'success'        => false,
                'message'        => 'Tài khoản chưa xác thực email. Vui lòng kiểm tra hộp thư.',
                'user'           => null,
                'user_id'        => (int)$user->id,
                'captcha_needed' => false,
                'unverified'     => true,
            ];
        }

        // Đăng nhập thành công
        $this->userRepo->resetLoginAttempts((int)$user->id);
        $this->userRepo->updateLastLogin((int)$user->id, $ip);

        // Ghi nhớ đăng nhập
        $rememberToken = null;
        if ($remember) {
            $rememberToken = $this->tokenSvc->createRememberMeToken((int)$user->id);
            $this->userRepo->setRememberToken((int)$user->id, hash('sha256', $rememberToken));
        }

        // Phát hiện thiết bị mới
        $lastIP = $this->loginRepo->getLastSuccessfulIP((int)$user->id);
        if ($this->security->isNewDevice($loginData, $lastIP)) {
            $this->mailSvc->sendNewDeviceAlert($user, $loginData);
        }

        $this->loginRepo->record(array_merge($loginData, ['status' => 'success']));

        // Cập nhật hash mật khẩu nếu dùng bcrypt cũ (upgrade to argon2)
        if (password_get_info($user->mat_khau)['algoName'] !== 'argon2id') {
            $this->userRepo->updatePassword(
                (int)$user->id,
                password_hash($password, PASSWORD_ARGON2ID)
            );
        }

        return [
            'success'        => true,
            'message'        => 'Đăng nhập thành công!',
            'user'           => $user,
            'remember_token' => $rememberToken,
            'captcha_needed' => false,
        ];
    }

    /* ══════════════════════════════════════════════
       ĐĂNG XUẤT
    ══════════════════════════════════════════════ */

    public function logout(int $userId): void
    {
        $user = $this->userRepo->findById($userId);

        // Xóa remember token
        $this->tokenSvc->revokeRememberMe($userId);
        $this->userRepo->setRememberToken($userId, null);

        // Ghi logout history
        $uaInfo = $this->security->parseUserAgent($_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->loginRepo->record([
            'user_id'     => $userId,
            'email'       => $user->email ?? '',
            'ip_address'  => $this->security->getClientIp(),
            'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'device_type' => $uaInfo['device_type'],
            'os'          => $uaInfo['os'],
            'browser'     => $uaInfo['browser'],
            'status'      => 'logout',
        ]);
    }

    /* ══════════════════════════════════════════════
       QUÊN MẬT KHẨU
    ══════════════════════════════════════════════ */

    /**
     * Xử lý quên mật khẩu: gửi OTP + link reset.
     * @return array ['success'=>bool, 'message'=>string, 'method'=>'email'|'otp', 'user_id'=>int|null]
     */
    public function forgotPassword(string $identifier): array
    {
        $user = $this->userRepo->findByEmailOrPhone(trim($identifier));

        if (!$user) {
            // Trả về success để tránh user enumeration
            return [
                'success' => true,
                'message' => 'Nếu tài khoản tồn tại, mã OTP sẽ được gửi đến email của bạn.',
                'user_id' => null,
            ];
        }

        // Tạo mã OTP đặt lại mật khẩu (đã tự động gửi qua email trong service)
        $otpResult = $this->otpSvc->generate($user, 'reset_password');

        if (empty($otpResult['success'])) {
            return [
                'success' => false,
                'message' => $otpResult['message'] ?? 'Không thể gửi mã OTP. Vui lòng thử lại sau.'
            ];
        }

        return [
            'success' => true,
            'message' => $otpResult['message'] ?? 'Mã OTP đặt lại mật khẩu đã được gửi đến email của bạn.',
            'method'  => 'otp',
            'user_id' => (int)$user->id,
        ];
    }

    /* ══════════════════════════════════════════════
       ĐẶT LẠI MẬT KHẨU
    ══════════════════════════════════════════════ */

    /**
     * @param string $token      Token từ URL
     * @param string $newPassword Mật khẩu mới (đã được kiểm tra hợp lệ)
     */
    public function resetPassword(string $token, string $newPassword): array
    {
        $tokenRecord = $this->tokenSvc->validateResetToken($token);

        if (!$tokenRecord) {
            // Kiểm tra xem token có tồn tại nhưng đã hết hạn không
            $expired = $this->tokenSvc->isExpired($token, 'reset_password');
            $msg = $expired
                ? 'Link đặt lại mật khẩu đã hết hạn (30 phút). Vui lòng yêu cầu lại.'
                : 'Link đặt lại mật khẩu không hợp lệ hoặc đã được sử dụng.';
            return ['success' => false, 'message' => $msg];
        }

        $userId = (int)$tokenRecord->user_id;

        // Cập nhật mật khẩu
        $hash = password_hash($newPassword, PASSWORD_ARGON2ID);
        $ok   = $this->userRepo->updatePassword($userId, $hash);

        if (!$ok) {
            return ['success' => false, 'message' => 'Không thể cập nhật mật khẩu. Vui lòng thử lại.'];
        }

        // Chỉ thu hồi token và session sau khi cập nhật mật khẩu thành công.
        $this->tokenSvc->revokeAll($userId);
        $this->userRepo->invalidateAllSessions($userId);

        return [
            'success' => true,
            'message' => 'Mật khẩu đã được đặt lại thành công! Vui lòng đăng nhập lại.',
        ];
    }

    /* ══════════════════════════════════════════════
       XÁC THỰC EMAIL
    ══════════════════════════════════════════════ */

    public function verifyEmail(string $token): array
    {
        $tokenRecord = $this->tokenSvc->validateEmailToken($token);

        if (!$tokenRecord) {
            $expired = $this->tokenSvc->isExpired($token, 'email_verify');
            $msg = $expired
                ? 'Link xác thực đã hết hạn (30 phút). Vui lòng yêu cầu gửi lại.'
                : 'Link xác thực không hợp lệ hoặc đã được sử dụng.';
            return ['success' => false, 'message' => $msg, 'expired' => $expired];
        }

        $userId = (int)$tokenRecord->user_id;
        $this->tokenSvc->markUsed((int)$tokenRecord->id);
        $this->userRepo->markEmailVerified($userId);

        return ['success' => true, 'message' => 'Email đã được xác thực thành công! Bạn có thể đăng nhập.'];
    }

    /**
     * Gửi lại email xác thực.
     */
    public function resendVerificationEmail(string $identifier): array
    {
        $user = $this->userRepo->findByEmailOrPhone($identifier);
        if (!$user) {
            return ['success' => true, 'message' => 'Nếu tài khoản tồn tại và chưa xác thực, email sẽ được gửi lại.'];
        }
        if (!empty($user->email_verified_at)) {
            return ['success' => false, 'message' => 'Email đã được xác thực. Bạn có thể đăng nhập bình thường.'];
        }
        $result = $this->otpSvc->generate($user, 'login');
        if (!empty($result['success'])) {
            $result['user_id'] = (int)$user->id;
            $result['otp_required'] = true;
        }
        return $result;
    }

    public function startEmailLoginOTP(int $userId): array
    {
        $user = $this->userRepo->findById($userId);
        if (!$user || !empty($user->email_verified_at)) {
            return ['success' => false, 'message' => 'Tài khoản không cần xác thực email.'];
        }

        return $this->otpSvc->generate($user, 'login');
    }

    public function verifyEmailLoginOTP(int $userId, string $code): array
    {
        if (!$this->otpSvc->verify($userId, $code, 'login')) {
            return ['success' => false, 'message' => 'Mã OTP không đúng hoặc đã hết hạn.'];
        }

        if (!$this->userRepo->markEmailVerified($userId)) {
            return ['success' => false, 'message' => 'Không thể xác thực tài khoản. Vui lòng thử lại.'];
        }

        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Tài khoản không tồn tại.'];
        }
        $this->userRepo->updateLastLogin($userId, $this->security->getClientIp());

        return ['success' => true, 'message' => 'Xác thực email thành công.', 'user' => $user];
    }

    /* ══════════════════════════════════════════════
       OTP
    ══════════════════════════════════════════════ */

    public function verifyOTP(int $userId, string $code, string $purpose): array
    {
        $ok = $this->otpSvc->verify($userId, $code, $purpose);
        if (!$ok) {
            return ['success' => false, 'message' => 'Mã OTP không đúng hoặc đã hết hạn.'];
        }
        $result = ['success' => true, 'message' => 'OTP hợp lệ.'];
        if ($purpose === 'reset_password') {
            $result['reset_token'] = $this->tokenSvc->createPasswordResetToken($userId);
        }
        return $result;
    }

    /* ══════════════════════════════════════════════
       GHI NHỚ ĐĂNG NHẬP – TỰ ĐỘNG ĐĂNG NHẬP
    ══════════════════════════════════════════════ */

    /**
     * Thử đăng nhập tự động từ cookie remember_me.
     * @return array|null Người dùng và token mới nếu token hợp lệ
     */
    public function loginFromCookie(string $cookieToken): ?array
    {
        $tokenRecord = $this->tokenSvc->validateRememberToken($cookieToken);
        if (!$tokenRecord) return null;

        $user = $this->userRepo->findById((int)$tokenRecord->user_id);
        if (!$user || $user->trang_thai !== 'hoat_dong' || !$this->security->isEmailVerified($user)) return null;

        // Xoay token sau mỗi lần tự đăng nhập; token cũ không thể tái sử dụng.
        $this->tokenSvc->markUsed((int)$tokenRecord->id);
        $newToken = $this->tokenSvc->createRememberMeToken((int)$user->id);
        $this->userRepo->setRememberToken((int)$user->id, hash('sha256', $newToken));

        return ['user' => $user, 'token' => $newToken];
    }

    /* ══════════════════════════════════════════════
       HELPER: LẤY USER
    ══════════════════════════════════════════════ */

    public function getUserById(int $id): ?object
    {
        return $this->userRepo->findById($id);
    }
}
