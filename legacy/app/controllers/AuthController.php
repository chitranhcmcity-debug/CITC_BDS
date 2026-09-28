<?php
/**
 * AuthController – Xử lý toàn bộ xác thực người dùng.
 *
 * Routes:
 *   GET/POST /nguoi-dung/dang-nhap          → login()
 *   GET/POST /nguoi-dung/register           → register()
 *   POST     /nguoi-dung/logout             → logout()
 *   GET/POST /nguoi-dung/forgotPassword     → forgotPassword()
 *   GET/POST /nguoi-dung/resetPassword/{t}  → resetPassword()
 *   GET      /nguoi-dung/verify-email/{t}   → verifyEmail()
 *   POST     /nguoi-dung/verify-otp         → verifyOTP() [AJAX]
 *   POST     /nguoi-dung/resend-otp         → resendOtp() [AJAX]
 *   POST     /nguoi-dung/resend-verify      → resendVerify() [AJAX]
 *
 * Nguyên tắc: Controller chỉ nhận input, gọi Service, set session, redirect.
 */
class AuthController extends Controller
{
    private AuthService $authSvc;

    public function __construct()
    {
        // Load tất cả dependencies
        require_once APP_ROOT . '/app/repositories/UserRepository.php';
        require_once APP_ROOT . '/app/repositories/TokenRepository.php';
        require_once APP_ROOT . '/app/repositories/OTPRepository.php';
        require_once APP_ROOT . '/app/repositories/LoginRepository.php';
        require_once APP_ROOT . '/app/validation/AuthValidation.php';
        require_once APP_ROOT . '/app/services/DichVuBaoMat.php';
        require_once APP_ROOT . '/app/services/DichVuMaTruyCap.php';
        require_once APP_ROOT . '/app/services/DichVuOTP.php';
        require_once APP_ROOT . '/app/services/DichVuThuDienTu.php';
        require_once APP_ROOT . '/app/services/DichVuXacThuc.php';
        require_once APP_ROOT . '/app/middleware/GuestMiddleware.php';
        require_once APP_ROOT . '/app/middleware/ThrottleMiddleware.php';

        $this->authSvc = new AuthService();
    }

    /* ══════════════════════════════════════════════
       ĐĂNG NHẬP
    ══════════════════════════════════════════════ */

    public function login(): void
    {
        GuestMiddleware::handle();

        $data = [
            'title'          => 'Đăng nhập – ' . SITE_NAME,
            'identifier'     => '',
            'errors'         => [],
            'captcha_needed' => false,
            'csrf_token'     => Csrf::token(),
        ];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?login=1');
            return;
        }

        // Chỉ verify CSRF trên POST
        Csrf::verify();

        // Rate limit
        $throttleKey = ThrottleMiddleware::loginKey();
        if (!ThrottleMiddleware::check($throttleKey, 5, 60)) {
            $data['errors']['general'] = 'Bạn đã thử quá nhiều lần. Vui lòng thử lại sau 1 phút.';
            $data['captcha_needed']    = true;
            if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $data['errors']['general'],
                    'errors' => $data['errors'],
                    'captcha_needed' => true
                ]);
                exit;
            }
            $this->view('auth/login', $data);
            return;
        }

        $identifier     = trim($_POST['identifier'] ?? '');
        $password       = $_POST['mat_khau'] ?? '';
        $remember       = !empty($_POST['remember_me']);
        $data['identifier'] = htmlspecialchars($identifier, ENT_QUOTES);

        // CAPTCHA được bật sau nhiều lần đăng nhập sai và phải được kiểm tra ở server.
        $expectedCaptcha = (string)(Session::get('auth_captcha') ?? '');
        if ($expectedCaptcha !== '') {
            $submittedCaptcha = strtolower(trim((string)($_POST['captcha'] ?? '')));
            Session::delete('auth_captcha');
            if ($submittedCaptcha === '' || !hash_equals($expectedCaptcha, $submittedCaptcha)) {
                ThrottleMiddleware::hit($throttleKey);
                $data['errors']['captcha'] = 'Mã xác nhận không đúng. Vui lòng thử lại.';
                $data['captcha_needed'] = true;
                if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => $data['errors']['captcha'],
                        'errors' => $data['errors'],
                        'captcha_needed' => true
                    ]);
                    exit;
                }
                $this->view('auth/login', $data);
                return;
            }
        }

        // Validate cơ bản
        if (empty($identifier) || empty($password)) {
            $data['errors']['general'] = 'Vui lòng điền đầy đủ thông tin.';
            if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $data['errors']['general'],
                    'errors' => $data['errors']
                ]);
                exit;
            }
            $this->view('auth/login', $data);
            return;
        }

        // Gọi AuthService
        $result = $this->authSvc->login($identifier, $password, $remember);

        if (!$result['success']) {
            ThrottleMiddleware::hit($throttleKey);
            $data['errors']['general']  = $result['message'];
            $data['captcha_needed']     = $result['captcha_needed'] ?? false;
            // Gợi ý resend verify nếu chưa verify email
            $data['unverified'] = $result['unverified'] ?? false;

            if (!empty($result['unverified']) && !empty($result['user_id'])) {
                $otpResult = $this->authSvc->startEmailLoginOTP((int)$result['user_id']);
                if (!empty($otpResult['success'])) {
                    Session::set('verify_login_user_id', (int)$result['user_id']);
                    Session::delete('forgot_user_id');
                    $result['otp_required'] = true;
                    $result['message'] = $otpResult['message'];
                    $data['errors']['general'] = $result['message'];
                }
            }

            if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $data['errors'],
                    'captcha_needed' => $data['captcha_needed'],
                    'unverified' => $data['unverified'],
                    'otp_required' => $result['otp_required'] ?? false,
                ]);
                exit;
            }

            if (!empty($result['otp_required'])) {
                $this->redirect('?otp=1');
                return;
            }

            $this->view('auth/login', $data);
            return;
        }

        // Đăng nhập thành công
        ThrottleMiddleware::clear($throttleKey);
        $user = $result['user'];

        // Lưu session ID cũ trước khi làm mới để đồng bộ so sánh
        $oldSessionId = session_id();

        // Tạo session an toàn
        session_regenerate_id(true);
        Session::set('user_id',             (int)$user->id);
        Session::set('user_email',          $user->email);
        Session::set('user_name',           $user->ten);
        Session::set('user_role_id',        (int)$user->ma_vai_tro);
        Session::set('user_email_verified', !empty($user->email_verified_at));
        Session::set('user_ip',             $_SERVER['REMOTE_ADDR'] ?? '');
        Session::set('auth_version',         (int)($user->auth_version ?? 1));

        // Đồng bộ danh sách so sánh từ khách vãng lai
        require_once APP_ROOT . '/app/services/DichVuSoSanh.php';
        (new CompareService())->syncSessionToUser($oldSessionId, (int)$user->id);

        // Remember Me cookie
        if (!empty($result['remember_token'])) {
            setcookie('remember_token', $result['remember_token'], [
                'expires'  => time() + 60 * 60 * 24 * 30,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => isset($_SERVER['HTTPS']),
            ]);
        }

        // Redirect theo role + intended URL
        $intended = filter_var($_GET['redirect'] ?? '', FILTER_SANITIZE_URL);
        $redirectUrl = '';
        if ($intended && str_starts_with($intended, '/')) {
            $basePath = (string)(parse_url($this->siteRoot(), PHP_URL_PATH) ?: '');
            if ($basePath !== '' && ($intended === $basePath || str_starts_with($intended, $basePath . '/'))) {
                $intended = substr($intended, strlen($basePath)) ?: '/';
            }
            $redirectUrl = rtrim($this->siteRoot(), '/') . '/' . ltrim($intended, '/');
        } else {
            if ((int)$user->ma_vai_tro === 1) {
                $redirectUrl = URL_ROOT . '/admin/dashboard';
            } else {
                $redirectUrl = URL_ROOT . '/nguoi-dung/post';
            }
        }

        if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'redirect' => $redirectUrl
            ]);
            exit;
        }

        header('Location: ' . $redirectUrl);
        exit;
    }

    /* ══════════════════════════════════════════════
       ĐĂNG KÝ
    ══════════════════════════════════════════════ */

    public function register(): void
    {
        GuestMiddleware::handle();

        $data = [
            'title'      => 'Đăng ký tài khoản – ' . SITE_NAME,
            'errors'     => [],
            'old'        => [],
            'csrf_token' => Csrf::token(),
        ];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?register=1');
            return;
        }

        Csrf::verify();

        $isAjax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');

        // Rate limit đăng ký
        $throttleKey = ThrottleMiddleware::registerKey();
        if (!ThrottleMiddleware::check($throttleKey, 5, 600)) {
            $msg = 'Bạn đã thử đăng ký quá nhiều lần. Vui lòng thử lại sau 10 phút.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => ['general' => $msg]]);
                exit;
            }
            $data['errors']['general'] = $msg;
            $this->view('auth/register', $data);
            return;
        }

        $input = [
            'ten'               => trim($_POST['ten'] ?? ''),
            'email'             => trim($_POST['email'] ?? ''),
            'dien_thoai'        => trim($_POST['dien_thoai'] ?? ''),
            'mat_khau'          => $_POST['mat_khau'] ?? '',
            'mat_khau_xac_nhan' => $_POST['mat_khau_xac_nhan'] ?? '',
            'dong_y_dieu_khoan' => $_POST['dong_y_dieu_khoan'] ?? '',
        ];
        $data['old'] = array_map('htmlspecialchars', $input);

        // Validation
        require_once APP_ROOT . '/app/repositories/UserRepository.php';
        $validator = new AuthValidation(new UserRepository());
        $errors    = $validator->validateRegister($input);

        if ($errors) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            }
            $data['errors'] = $errors;
            $this->view('auth/register', $data);
            return;
        }

        // Tạo tài khoản
        $result = $this->authSvc->register($input);

        if (!$result['success']) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => ['general' => $result['message']]]);
                exit;
            }
            $data['errors']['general'] = $result['message'];
            $this->view('auth/register', $data);
            return;
        }

        ThrottleMiddleware::hit($throttleKey);

        if (!empty($result['otp_required']) && !empty($result['user_id'])) {
            Session::set('verify_login_user_id', (int)$result['user_id']);
            Session::delete('forgot_user_id');
        }
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => $result['message'],
                'redirect' => URL_ROOT . (!empty($result['otp_required']) ? '/?otp=1' : '/?login=1'),
                'otp_required' => $result['otp_required'] ?? false,
            ]);
            exit;
        }

        Session::flash('success', $result['message'], 'alert alert-success');
        $this->redirect(!empty($result['otp_required']) ? '?otp=1' : '?login=1');
    }

    /* ══════════════════════════════════════════════
       ĐĂNG XUẤT
    ══════════════════════════════════════════════ */

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        Csrf::verify();

        $userId = (int)(Session::get('user_id') ?? 0);
        if ($userId) {
            $this->authSvc->logout($userId);
        }

        // Xóa session
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();

        // Xóa remember cookie
        setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/']);

        $this->redirect('');
    }

    /* ══════════════════════════════════════════════
       QUÊN MẬT KHẨU
    ══════════════════════════════════════════════ */

    public function forgotPassword(): void
    {
        $data = [
            'title'      => 'Quên mật khẩu – ' . SITE_NAME,
            'errors'     => [],
            'success'    => false,
            'csrf_token' => Csrf::token(),
        ];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?forgot=1');
            return;
        }

        Csrf::verify();

        $isAjax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');

        // Rate limit quên mật khẩu
        $throttleKey = ThrottleMiddleware::forgotKey();
        if (!ThrottleMiddleware::check($throttleKey, 5, 600)) {
            $msg = 'Bạn đã gửi quá nhiều yêu cầu. Thử lại sau 10 phút.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => ['general' => $msg]]);
                exit;
            }
            $data['errors']['general'] = $msg;
            $this->view('auth/forgot-password', $data);
            return;
        }

        $identifier = trim($_POST['identifier'] ?? '');

        require_once APP_ROOT . '/app/repositories/UserRepository.php';
        $validator = new AuthValidation(new UserRepository());
        $errors    = $validator->validateForgotPassword(['identifier' => $identifier]);

        if ($errors) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            }
            $data['errors'] = $errors;
            $this->view('auth/forgot-password', $data);
            return;
        }

        ThrottleMiddleware::hit($throttleKey, 600);
        $result = $this->authSvc->forgotPassword($identifier);

        // Lưu user_id vào session tạm để dùng cho OTP verify
        if (!empty($result['user_id'])) {
            Session::set('forgot_user_id', $result['user_id']);
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => $result['message'],
                'method' => $result['method'] ?? ''
            ]);
            exit;
        }

        $data['success'] = true;
        $data['message'] = $result['message'];

        if (($result['method'] ?? '') === 'otp') {
            $this->view('auth/verify-otp', [
                'title' => 'Xác thực OTP – ' . SITE_NAME,
                'purpose' => 'reset_password',
                'cooldown' => 60,
            ]);
            return;
        }

        $this->view('auth/forgot-password', $data);
    }

    /* ══════════════════════════════════════════════
       ĐẶT LẠI MẬT KHẨU
    ══════════════════════════════════════════════ */

    public function resetPassword(string $token = ''): void
    {
        $data = [
            'title'      => 'Đặt lại mật khẩu – ' . SITE_NAME,
            'token'      => htmlspecialchars($token, ENT_QUOTES),
            'errors'     => [],
            'csrf_token' => Csrf::token(),
        ];

        // GET: hiển thị form (validate token trước)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (!$token) {
                Session::flash('error', 'Link đặt lại mật khẩu không hợp lệ.', 'alert alert-danger');
                $this->redirect('nguoi-dung/forgotPassword');
                return;
            }
            $this->view('auth/reset-password', $data);
            return;
        }

        Csrf::verify();

        $token    = $_POST['token'] ?? '';
        $newPass  = $_POST['mat_khau'] ?? '';
        $confirm  = $_POST['mat_khau_xac_nhan'] ?? '';

        require_once APP_ROOT . '/app/repositories/UserRepository.php';
        $validator = new AuthValidation(new UserRepository());
        $errors    = $validator->validateResetPassword([
            'token'               => $token,
            'mat_khau'            => $newPass,
            'mat_khau_xac_nhan'   => $confirm,
        ]);

        if ($errors) {
            $data['errors'] = $errors;
            $data['token']  = htmlspecialchars($token, ENT_QUOTES);
            $this->view('auth/reset-password', $data);
            return;
        }

        $result = $this->authSvc->resetPassword($token, $newPass);

        if (!$result['success']) {
            $data['errors']['general'] = $result['message'];
            $data['token']             = htmlspecialchars($token, ENT_QUOTES);
            $this->view('auth/reset-password', $data);
            return;
        }

        Session::flash('success', $result['message'], 'alert alert-success');
        $this->redirect('nguoi-dung/dang-nhap');
    }

    /* ══════════════════════════════════════════════
       XÁC THỰC EMAIL
    ══════════════════════════════════════════════ */

    public function verifyEmail(string $token = ''): void
    {
        if (!$token) {
            $this->redirect('nguoi-dung/dang-nhap');
            return;
        }

        $result = $this->authSvc->verifyEmail($token);

        $this->view('auth/verify-email', [
            'title'   => 'Xác thực Email – ' . SITE_NAME,
            'success' => $result['success'],
            'message' => $result['message'],
            'expired' => $result['expired'] ?? false,
        ]);
    }

    public function emailVerificationNotice(): void
    {
        $userId = (int)(Session::get('verify_login_user_id') ?: Session::get('user_id') ?: 0);
        if (!$userId) {
            $this->redirect('?login=1');
            return;
        }

        $result = $this->authSvc->startEmailLoginOTP($userId);
        if (empty($result['success'])) {
            Session::flash('error_msg', $result['message'], 'alert alert-danger');
            $this->redirect('?login=1');
            return;
        }

        Session::set('verify_login_user_id', $userId);
        foreach (['user_id', 'user_email', 'user_name', 'user_role_id', 'user_email_verified', 'user_ip', 'auth_version'] as $key) {
            Session::delete($key);
        }
        $this->redirect('?otp=1');
    }

    /* ══════════════════════════════════════════════
       OTP
    ══════════════════════════════════════════════ */

    public function verifyOtp(): void
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
            return;
        }

        if (!Csrf::verify(false)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Phiên bảo mật không hợp lệ. Vui lòng tải lại trang.']);
            return;
        }

        $verifyLoginUserId = (int)(Session::get('verify_login_user_id') ?? 0);
        $userId  = $verifyLoginUserId ?: (int)(Session::get('forgot_user_id') ?? 0);
        $code    = trim($_POST['otp'] ?? '');

        if (!$userId || !$code) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            return;
        }

        if (!preg_match('/^\d{6}$/', $code)) {
            echo json_encode(['success' => false, 'message' => 'Mã OTP phải gồm đúng 6 chữ số.']);
            return;
        }

        if ($verifyLoginUserId) {
            $result = $this->authSvc->verifyEmailLoginOTP($userId, $code);
            if (!empty($result['success']) && !empty($result['user'])) {
                $user = $result['user'];
                $this->establishUserSession($user);
                Session::delete('verify_login_user_id');
                unset($result['user']);
                $result['authenticated'] = true;
                $result['redirect'] = (int)$user->ma_vai_tro === 1
                    ? URL_ROOT.'/admin/dashboard'
                    : URL_ROOT.'/nguoi-dung/post';
            }
            echo json_encode($result);
            return;
        }

        $result = $this->authSvc->verifyOTP($userId, $code, 'reset_password');
        if (!empty($result['success'])) {
            Session::set('otp_verified_user_id', $userId);
            $result['otp_verified'] = true;
        }
        if (!empty($result['reset_token'])) {
            $result['reset_url'] = $this->siteRoot() . '/nguoi-dung/resetPassword/' . rawurlencode($result['reset_token']);
            unset($result['reset_token']);
            Session::delete('forgot_user_id');
        }
        echo json_encode($result);
    }

    public function resetPasswordAjax(): void
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
            return;
        }

        if (!Csrf::verify(false)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Phiên bảo mật không hợp lệ. Vui lòng tải lại trang.']);
            return;
        }

        $userId = (int)(Session::get('otp_verified_user_id') ?? 0);
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Bạn chưa xác thực mã OTP hoặc phiên xác thực đã hết hạn.']);
            return;
        }

        $newPass = $_POST['mat_khau'] ?? '';
        $confirm = $_POST['mat_khau_xac_nhan'] ?? '';

        require_once APP_ROOT . '/app/repositories/UserRepository.php';
        $validator = new AuthValidation(new UserRepository());
        
        $errors = [];
        $passErrors = $validator->validatePassword($newPass);
        if ($passErrors) {
            $errors['mat_khau'] = implode(' ', $passErrors);
        }

        if (empty($confirm)) {
            $errors['mat_khau_xac_nhan'] = 'Vui lòng nhập lại mật khẩu.';
        } elseif ($newPass !== $confirm) {
            $errors['mat_khau_xac_nhan'] = 'Mật khẩu nhập lại không khớp.';
        }

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        // Cập nhật mật khẩu
        $hash = password_hash($newPass, PASSWORD_ARGON2ID);
        $userRepo = new UserRepository();
        $ok = $userRepo->updatePassword($userId, $hash);

        if (!$ok) {
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật mật khẩu. Vui lòng thử lại.']);
            return;
        }

        // Thu hồi token và session
        $tokenSvc = new TokenService(new TokenRepository());
        $tokenSvc->revokeAll($userId);
        $userRepo->invalidateAllSessions($userId);

        Session::delete('otp_verified_user_id');
        Session::delete('forgot_user_id');

        echo json_encode([
            'success' => true,
            'message' => 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập bằng mật khẩu mới.'
        ]);
    }

    public function resendOtp(): void
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false]);
            return;
        }

        if (!Csrf::verify(false)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Phiên bảo mật không hợp lệ.']);
            return;
        }

        $verifyLoginUserId = (int)(Session::get('verify_login_user_id') ?? 0);
        $userId  = $verifyLoginUserId ?: (int)(Session::get('forgot_user_id') ?? 0);
        $purpose = $verifyLoginUserId ? 'login' : 'reset_password';

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Phiên đã hết hạn. Vui lòng thử lại.']);
            return;
        }

        $user = $this->authSvc->getUserById($userId);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Tài khoản không tồn tại.']);
            return;
        }

        $otpSvc = new OTPService();
        $result = $otpSvc->generate($user, $purpose);
        echo json_encode($result);
    }

    public function resendVerify(): void
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false]);
            return;
        }

        if (!Csrf::verify(false)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Phiên bảo mật không hợp lệ.']);
            return;
        }

        $identifier = trim($_POST['identifier'] ?? '');
        if (empty($identifier)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập email.']);
            return;
        }

        $result = $this->authSvc->resendVerificationEmail($identifier);
        if (!empty($result['otp_required']) && !empty($result['user_id'])) {
            Session::set('verify_login_user_id', (int)$result['user_id']);
            Session::delete('forgot_user_id');
        }
        unset($result['user_id']);
        echo json_encode($result);
    }

    /** Tạo lại CAPTCHA đăng nhập và đồng bộ giá trị với session. */
    public function refreshCaptcha(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
            return;
        }
        if (!Csrf::verify(false)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Phiên bảo mật không hợp lệ.']);
            return;
        }
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $captcha = '';
        for ($i = 0; $i < 5; $i++) {
            $captcha .= $chars[random_int(0, strlen($chars) - 1)];
        }
        Session::set('auth_captcha', strtolower($captcha));
        echo json_encode(['success' => true, 'captcha' => $captcha]);
    }

    /* ══════════════════════════════════════════════
       ALIASES (router camelCase compatibility)
    ══════════════════════════════════════════════ */

    /** Alias: /nguoi-dung/dang-nhap → dangNhap() → login() */
    public function dangNhap(): void { $this->login(); }

    /** Alias: /nguoi-dung/dang-ky → dangKy() → register() */
    public function dangKy(): void { $this->register(); }

    /** Alias: /nguoi-dung/quen-mat-khau → quenMatKhau() → forgotPassword() */
    public function quenMatKhau(): void { $this->forgotPassword(); }

    public function socialRedirect(): void
    {
        $provider = trim($_GET['provider'] ?? 'google');
        if ($provider === 'google') {
            if (empty(GOOGLE_CLIENT_ID) || empty(GOOGLE_CLIENT_SECRET)) {
                $this->redirect('nguoi-dung/social-error?provider=google');
                return;
            }
            $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
                'client_id' => GOOGLE_CLIENT_ID,
                'redirect_uri' => GOOGLE_REDIRECT_URI,
                'response_type' => 'code',
                'scope' => 'openid profile email',
                'state' => 'google_oauth_state'
            ]);
            $this->redirectUrl($authUrl);
        } else {
            if (empty(FACEBOOK_APP_ID) || empty(FACEBOOK_APP_SECRET)) {
                $this->redirect('nguoi-dung/social-error?provider=facebook');
                return;
            }
            $authUrl = 'https://www.facebook.com/v12.0/dialog/oauth?' . http_build_query([
                'client_id' => FACEBOOK_APP_ID,
                'redirect_uri' => FACEBOOK_REDIRECT_URI,
                'scope' => 'email,public_profile',
                'state' => 'facebook_oauth_state'
            ]);
            $this->redirectUrl($authUrl);
        }
    }

    public function socialError(): void
    {
        $provider = trim($_GET['provider'] ?? 'google');
        $this->view('auth/social-error', [
            'provider' => $provider
        ]);
    }

    public function googleCallback(): void
    {
        $code = $_GET['code'] ?? '';
        if (empty($code)) {
            $this->redirect('nguoi-dung/dang-nhap');
            return;
        }

        // Exchange code for access token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'code' => $code,
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri' => GOOGLE_REDIRECT_URI,
            'grant_type' => 'authorization_code'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $accessToken = $data['access_token'] ?? '';

        if (empty($accessToken)) {
            $this->redirect('nguoi-dung/social-error?provider=google&error=token_failed');
            return;
        }

        // Fetch user info
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);

        $userInfoResponse = curl_exec($ch);
        curl_close($ch);

        $userInfo = json_decode($userInfoResponse, true);
        $email = $userInfo['email'] ?? '';
        $name = $userInfo['name'] ?? 'Google User';

        if (empty($email)) {
            $this->redirect('nguoi-dung/social-error?provider=google&error=no_email');
            return;
        }

        $this->handleSocialUserLogin($email, $name, 'Google');
    }

    public function facebookCallback(): void
    {
        $code = $_GET['code'] ?? '';
        if (empty($code)) {
            $this->redirect('nguoi-dung/dang-nhap');
            return;
        }

        // Exchange code for access token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/v12.0/oauth/access_token?' . http_build_query([
            'client_id' => FACEBOOK_APP_ID,
            'client_secret' => FACEBOOK_APP_SECRET,
            'redirect_uri' => FACEBOOK_REDIRECT_URI,
            'code' => $code
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $accessToken = $data['access_token'] ?? '';

        if (empty($accessToken)) {
            $this->redirect('nguoi-dung/social-error?provider=facebook&error=token_failed');
            return;
        }

        // Fetch user info
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/me?' . http_build_query([
            'fields' => 'id,name,email',
            'access_token' => $accessToken
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $userInfoResponse = curl_exec($ch);
        curl_close($ch);

        $userInfo = json_decode($userInfoResponse, true);
        $email = $userInfo['email'] ?? ($userInfo['id'] . '@facebook.com');
        $name = $userInfo['name'] ?? 'Facebook User';

        $this->handleSocialUserLogin($email, $name, 'Facebook');
    }

    private function handleSocialUserLogin(string $email, string $name, string $providerName): void
    {
        require_once APP_ROOT . '/app/repositories/UserRepository.php';
        $userRepo = new UserRepository();
        
        $user = $userRepo->findByEmail($email);
        if (!$user) {
            $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS);
            $stmtInsert = $pdo->prepare("
                INSERT INTO nguoi_dung (ten, email, mat_khau, ma_vai_tro, trang_thai, email_verified_at)
                VALUES (:name, :email, :password, 3, 'hoat_dong', NOW())
            ");
            $stmtInsert->execute([
                'name' => $name,
                'email' => $email,
                'password' => password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT)
            ]);
            $user = $userRepo->findByEmail($email);
        }
        
        session_regenerate_id(true);
        Session::set('user_id',             (int)$user->id);
        Session::set('user_email',          $user->email);
        Session::set('user_name',           $user->ten);
        Session::set('user_role_id',        (int)$user->ma_vai_tro);
        Session::set('user_email_verified', !empty($user->email_verified_at));
        Session::set('user_ip',             $_SERVER['REMOTE_ADDR'] ?? '');
        Session::set('auth_version',         (int)($user->auth_version ?? 1));
        
        Session::flash('success', 'Đăng nhập bằng ' . $providerName . ' thành công!', 'alert alert-success');
        
        echo "<script>
            if (window.opener) {
                window.opener.location.href = '" . URL_ROOT . "/nguoi-dung/post';
                window.close();
            } else {
                window.location.href = '" . URL_ROOT . "/nguoi-dung/post';
            }
        </script>";
        exit;
    }

    private function redirectUrl(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /** /nguoi-dung/auth → redirect to login */
    public function index(): void { $this->redirect('nguoi-dung/dang-nhap'); }

    /* ══════════════════════════════════════════════
       PRIVATE HELPERS
    ══════════════════════════════════════════════ */

    private function establishUserSession(object $user): void
    {
        $oldSessionId = session_id();
        session_regenerate_id(true);
        Session::set('user_id', (int)$user->id);
        Session::set('user_email', (string)$user->email);
        Session::set('user_name', (string)$user->ten);
        Session::set('user_role_id', (int)$user->ma_vai_tro);
        Session::set('user_email_verified', true);
        Session::set('user_ip', $_SERVER['REMOTE_ADDR'] ?? '');
        Session::set('auth_version', (int)($user->auth_version ?? 1));

        require_once APP_ROOT.'/app/services/DichVuSoSanh.php';
        (new CompareService())->syncSessionToUser($oldSessionId, (int)$user->id);
    }

    private function siteRoot(): string
    {
        return defined('URL_ROOT') ? URL_ROOT : '';
    }
}
