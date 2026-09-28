<?php

/** API JSON cho module tài khoản và xác thực. */
class ApiController extends Controller
{
    private AuthService $auth;

    public function __construct()
    {
        require_once APP_ROOT . '/app/validation/AuthValidation.php';
        require_once APP_ROOT . '/app/validation/DashboardValidation.php';
        require_once APP_ROOT . '/app/validation/PostValidation.php';
        require_once APP_ROOT . '/app/middleware/ThrottleMiddleware.php';
        $this->auth = new AuthService();
    }

    public function index(): void
    {
        $this->respond(['success' => true, 'message' => 'Auth API đang hoạt động.']);
    }

    public function login(): void
    {
        $this->requirePost();
        $input = $this->input();
        $identifier = trim((string)($input['identifier'] ?? $input['email'] ?? ''));
        $password = (string)($input['password'] ?? $input['mat_khau'] ?? '');

        $key = ThrottleMiddleware::loginKey();
        if (!ThrottleMiddleware::check($key, 5, 60)) {
            $this->respond(['success' => false, 'message' => 'Bạn đã thử quá nhiều lần. Vui lòng thử lại sau 1 phút.'], 429);
        }

        $result = $this->auth->login($identifier, $password, !empty($input['remember_me']));
        if (!$result['success']) {
            ThrottleMiddleware::hit($key);
            $this->respond(['success' => false, 'message' => $result['message']], 401);
        }

        ThrottleMiddleware::clear($key);
        $user = $result['user'];

        // Lưu session ID cũ để đồng bộ so sánh
        $oldSessionId = session_id();

        session_regenerate_id(true);
        Session::set('user_id', (int)$user->id);
        Session::set('user_email', (string)$user->email);
        Session::set('user_name', (string)$user->ten);
        Session::set('user_role_id', (int)$user->ma_vai_tro);
        Session::set('user_email_verified', !empty($user->email_verified_at));
        Session::set('user_ip', $_SERVER['REMOTE_ADDR'] ?? '');
        Session::set('auth_version', (int)($user->auth_version ?? 1));

        // Đồng bộ danh sách so sánh từ khách vãng lai
        require_once APP_ROOT . '/app/services/DichVuSoSanh.php';
        (new CompareService())->syncSessionToUser($oldSessionId, (int)$user->id);

        $this->respond([
            'success' => true,
            'message' => 'Đăng nhập thành công.',
            'csrf_token' => Csrf::token(),
            'user' => $this->publicUser($user),
            'remember_token' => $result['remember_token'] ?? null,
        ]);
    }

    public function register(): void
    {
        $this->requirePost();
        $input = $this->input();
        $data = [
            'ten' => trim((string)($input['ten'] ?? $input['name'] ?? '')),
            'email' => trim((string)($input['email'] ?? '')),
            'dien_thoai' => trim((string)($input['dien_thoai'] ?? $input['phone'] ?? '')),
            'mat_khau' => (string)($input['mat_khau'] ?? $input['password'] ?? ''),
            'mat_khau_xac_nhan' => (string)($input['mat_khau_xac_nhan'] ?? $input['password_confirmation'] ?? ''),
            'dong_y_dieu_khoan' => $input['dong_y_dieu_khoan'] ?? $input['terms'] ?? false,
        ];

        $validator = new AuthValidation(new UserRepository());
        $errors = $validator->validateRegister($data);
        if ($errors) {
            $this->respond(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $errors], 422);
        }

        $result = $this->auth->register($data);
        $this->respond($result, $result['success'] ? 201 : 500);
    }

    public function logout(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $userId = (int)(Session::get('user_id') ?: 0);
        if ($userId > 0) {
            $this->auth->logout($userId);
        }
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $this->respond(['success' => true, 'message' => 'Đăng xuất thành công.']);
    }

    public function forgotPassword(): void
    {
        $this->requirePost();
        $input = $this->input();
        $identifier = trim((string)($input['identifier'] ?? $input['email'] ?? $input['phone'] ?? ''));
        $validator = new AuthValidation(new UserRepository());
        $errors = $validator->validateForgotPassword(['identifier' => $identifier]);
        if ($errors) {
            $this->respond(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $errors], 422);
        }
        $result = $this->auth->forgotPassword($identifier);
        if (!empty($result['user_id'])) {
            Session::set('forgot_user_id', (int)$result['user_id']);
        }
        unset($result['user_id']);
        $result['csrf_token'] = Csrf::token();
        $this->respond($result);
    }

    public function resetPassword(): void
    {
        $this->requirePost();
        $input = $this->input();
        $data = [
            'token' => (string)($input['token'] ?? ''),
            'mat_khau' => (string)($input['mat_khau'] ?? $input['password'] ?? ''),
            'mat_khau_xac_nhan' => (string)($input['mat_khau_xac_nhan'] ?? $input['password_confirmation'] ?? ''),
        ];
        $errors = (new AuthValidation(new UserRepository()))->validateResetPassword($data);
        if ($errors) {
            $this->respond(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $errors], 422);
        }
        $result = $this->auth->resetPassword($data['token'], $data['mat_khau']);
        $this->respond($result, $result['success'] ? 200 : 400);
    }

    public function verifyEmail(): void
    {
        $this->requirePost();
        $input = $this->input();
        $result = $this->auth->verifyEmail(trim((string)($input['token'] ?? '')));
        $this->respond($result, $result['success'] ? 200 : 400);
    }

    public function verifyOtp(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $input = $this->input();
        $userId = (int)(Session::get('forgot_user_id') ?: 0);
        $code = trim((string)($input['otp'] ?? ''));
        if ($userId <= 0 || !preg_match('/^\d{6}$/', $code)) {
            $this->respond(['success' => false, 'message' => 'Phiên hoặc mã OTP không hợp lệ.'], 422);
        }
        $result = $this->auth->verifyOTP($userId, $code, 'reset_password');
        if (!empty($result['reset_token'])) {
            Session::delete('forgot_user_id');
        }
        $this->respond($result, $result['success'] ? 200 : 400);
    }

    /**
     * GET /api/dashboard[/statistics|chart|recent-post|notification]
     */
    public function dashboard(string $resource = ''): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            $this->respond(['success'=>false,'message'=>'Phương thức không được hỗ trợ.'],405);
        }
        if (!Auth::isLoggedIn()) {
            $this->respond(['success'=>false,'message'=>'Bạn chưa đăng nhập.'],401);
        }
        if ((int)Session::get('user_role_id') !== Role::MEMBER || !Session::get('user_email_verified')) {
            $this->respond(['success'=>false,'message'=>'Tài khoản không có quyền truy cập Dashboard thành viên.'],403);
        }
        $service = new DashboardService();
        $userId = (int)Session::get('user_id');
        $period = DashboardValidation::period($_GET['period'] ?? 30);
        $data = match ($resource) {
            'statistics' => $service->statistics($userId),
            'chart' => $service->chart($userId,$period),
            'recent-post', 'recent' => $service->recent($userId),
            'notification', 'notifications' => $service->notifications($userId),
            'transactions' => $service->transactions($userId),
            '', 'index' => $service->dashboard($userId,$period),
            default => null,
        };
        if ($data === null) $this->respond(['success'=>false,'message'=>'Không tìm thấy tài nguyên Dashboard.'],404);
        $this->respond(['success'=>true,'period'=>$period,'data'=>$data]);
    }

    /** API quản lý tin: /api/post/{action}/{id?}. */
    public function post(string $action='',string $id=''):void
    {
        if(!Auth::isLoggedIn())$this->respond(['success'=>false,'message'=>'Bạn chưa đăng nhập.'],401);
        if((int)Session::get('user_role_id')!==Role::MEMBER||!Session::get('user_email_verified'))$this->respond(['success'=>false,'message'=>'Không có quyền truy cập.'],403);
        $method=$_SERVER['REQUEST_METHOD']??'GET';$input=$this->input();$service=new PostService();$uid=(int)Session::get('user_id');
        if($action==='my'&&$method==='GET')$this->respond(['success'=>true,'data'=>$service->list($uid)]);
        if(!in_array($action,['create','update','delete','renew','up','upload'],true))$this->respond(['success'=>false,'message'=>'Không tìm thấy API tin đăng.'],404);
        $this->requireCsrf();
        if($action==='create'&&$method==='POST'){$r=$service->create($uid,$input,!empty($input['draft']));$this->respond($r,$r['success']?201:422);}
        if($action==='update'&&in_array($method,['PUT','POST'],true)){$r=$service->update($uid,(int)$id,$input,!empty($input['draft']));$this->respond($r,$r['success']?200:422);}
        if($action==='delete'&&in_array($method,['DELETE','POST'],true)){$ok=$service->delete($uid,(int)$id);$this->respond(['success'=>$ok],$ok?200:404);}
        if($action==='renew'&&$method==='POST'){$r=$service->renew($uid,(int)($input['id']??$id),(int)($input['days']??0));$this->respond($r,$r['success']?200:422);}
        if($action==='up'&&$method==='POST'){$r=$service->up($uid,(int)($input['id']??$id));$this->respond($r,$r['success']?200:422);}
        if($action==='upload'&&$method==='POST'){$files=(new UploadService())->images();$this->respond(['success'=>(bool)$files,'files'=>$files],$files?200:422);}
        $this->respond(['success'=>false,'message'=>'Phương thức không được hỗ trợ.'],405);
    }

    private function input(): array
    {
        $contentType = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));
        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode((string)file_get_contents('php://input'), true);
            return is_array($decoded) ? $decoded : [];
        }
        return $_POST;
    }

    private function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->respond(['success' => false, 'message' => 'Phương thức không được hỗ trợ.'], 405);
        }
    }

    private function requireCsrf(): void
    {
        $headerToken = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $postToken = (string)($_POST['_csrf_token'] ?? $_POST['csrf_token'] ?? '');
        $sessionToken = (string)($_SESSION['_csrf_token'] ?? '');
        $token = $headerToken !== '' ? $headerToken : $postToken;
        if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
            $this->respond(['success' => false, 'message' => 'Phiên bảo mật không hợp lệ.'], 403);
        }
    }

    private function publicUser(object $user): array
    {
        return [
            'id' => (int)$user->id,
            'name' => (string)$user->ten,
            'email' => (string)$user->email,
            'phone' => (string)($user->dien_thoai ?? ''),
            'role_id' => (int)$user->ma_vai_tro,
            'email_verified' => !empty($user->email_verified_at),
        ];
    }

    private function respond(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /* ══════════════════════════════════════════════
       RESTFUL CONTACT CRM API
       ══════════════════════════════════════════════ */

    /**
     * Route: /api/contact/{id}
     */
    public function contact($id = null): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        switch ($method) {
            case 'POST':
                $this->createContactApi();
                break;
            case 'GET':
                $this->requireAdmin();
                if ($id !== null && is_numeric($id)) {
                    $this->getContactDetailsApi((int)$id);
                } else {
                    $this->getContactsListApi();
                }
                break;
            case 'PUT':
                $this->requireAdmin();
                if ($id !== null && is_numeric($id)) {
                    $this->updateContactApi((int)$id);
                } else {
                    $this->respond(['success' => false, 'message' => 'Thiếu ID liên hệ.'], 400);
                }
                break;
            case 'DELETE':
                $this->requireAdmin();
                if ($id !== null && is_numeric($id)) {
                    $this->deleteContactApi((int)$id);
                } else {
                    $this->respond(['success' => false, 'message' => 'Thiếu ID liên hệ.'], 400);
                }
                break;
            default:
                $this->respond(['success' => false, 'message' => 'Phương thức không được hỗ trợ.'], 405);
        }
    }

    private function requireAdmin(): void
    {
        $roleId = (int)(Session::get('user_role_id') ?: 0);
        if ($roleId !== 1) {
            $this->respond(['success' => false, 'message' => 'Quyền truy cập bị từ chối.'], 403);
        }
    }

    private function createContactApi(): void
    {
        require_once APP_ROOT . '/app/services/DichVuLienHe.php';
        $contactSvc = new ContactService();
        $input = $this->input();
        
        $result = $contactSvc->createContact($input, $_FILES);
        if ($result['success']) {
            $this->respond([
                'success' => true,
                'message' => $result['message'],
                'contact_id' => $result['contact_id']
            ], 201);
        } else {
            $this->respond([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $result['errors']
            ], 422);
        }
    }

    private function getContactsListApi(): void
    {
        require_once APP_ROOT . '/app/repositories/ContactRepository.php';
        $contactRepo = new ContactRepository();

        $filters = [
            'status'         => $_GET['status'] ?? '',
            'type'           => $_GET['type'] ?? '',
            'assigned_admin' => $_GET['assigned_admin'] ?? '',
            'search'         => $_GET['search'] ?? '',
            'date_from'      => $_GET['date_from'] ?? '',
            'date_to'        => $_GET['date_to'] ?? ''
        ];

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        $list = $contactRepo->getAll($filters, $limit, $offset);
        $total = $contactRepo->countAll($filters);

        $this->respond([
            'success' => true,
            'data' => $list,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    private function getContactDetailsApi(int $id): void
    {
        require_once APP_ROOT . '/app/repositories/ContactRepository.php';
        $contactRepo = new ContactRepository();

        $contact = $contactRepo->findById($id);
        if (!$contact) {
            $this->respond(['success' => false, 'message' => 'Yêu cầu liên hệ không tồn tại.'], 404);
        }

        $contact['attachments'] = $contactRepo->getAttachments($id);
        $this->respond([
            'success' => true,
            'data' => $contact
        ]);
    }

    private function updateContactApi(int $id): void
    {
        require_once APP_ROOT . '/app/repositories/ContactRepository.php';
        $contactRepo = new ContactRepository();

        $contact = $contactRepo->findById($id);
        if (!$contact) {
            $this->respond(['success' => false, 'message' => 'Yêu cầu liên hệ không tồn tại.'], 404);
        }

        $input = $this->input();
        $updateData = [];

        if (isset($input['status'])) {
            $updateData['status'] = $input['status'];
        }
        if (isset($input['assigned_admin'])) {
            $updateData['assigned_admin'] = (int)$input['assigned_admin'];
        }
        if (isset($input['note'])) {
            $updateData['note'] = $input['note'];
        }

        $ok = $contactRepo->update($id, $updateData);
        $this->respond([
            'success' => $ok,
            'message' => $ok ? 'Cập nhật yêu cầu thành công.' : 'Không có thông tin thay đổi.'
        ]);
    }

    private function deleteContactApi(int $id): void
    {
        require_once APP_ROOT . '/app/repositories/ContactRepository.php';
        $contactRepo = new ContactRepository();

        $contact = $contactRepo->findById($id);
        if (!$contact) {
            $this->respond(['success' => false, 'message' => 'Yêu cầu liên hệ không tồn tại.'], 404);
        }

        $ok = $contactRepo->delete($id);
        $this->respond([
            'success' => $ok,
            'message' => $ok ? 'Xóa yêu cầu liên hệ thành công.' : 'Xóa thất bại.'
        ]);
    }

    // =========================================================================
    // API TIN ĐÃ LƯU (FAVORITES) & SO SÁNH (COMPARE)
    // =========================================================================

    /**
     * API Tin đã lưu (Favorites)
     * GET /api/favorite
     * POST /api/favorite/toggle
     * DELETE /api/favorite/{id}
     * DELETE /api/favorite/all
     */
    public function favorite(string $action = ''): void
    {
        require_once APP_ROOT . '/app/services/DichVuYeuThich.php';
        $service = new FavoriteService();

        // 1. Phân biệt theo request method và action segment
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'GET') {
            if (!Session::get('user_id')) {
                $this->respond(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
            }
            $userId = (int)Session::get('user_id');
            // Bộ lọc từ query string
            $filters = [
                'transaction_type' => $_GET['transaction_type'] ?? null,
                'vip_level'        => isset($_GET['vip_level']) && $_GET['vip_level'] !== '' ? (int)$_GET['vip_level'] : null,
                'price_min'        => isset($_GET['price_min']) && $_GET['price_min'] !== '' ? (float)$_GET['price_min'] : null,
                'price_max'        => isset($_GET['price_max']) && $_GET['price_max'] !== '' ? (float)$_GET['price_max'] : null,
            ];
            $data = $service->getFavoritesWithCache($userId, $filters);
            $this->respond(['success' => true, 'favorites' => $data]);
            return;
        }

        if ($method === 'POST' && $action === 'toggle') {
            if (!Session::get('user_id')) {
                $this->respond(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
            }
            $userId = (int)Session::get('user_id');
            $input = $this->input();
            $postId = (int)($input['post_id'] ?? 0);
            $result = $service->toggleFavorite($userId, $postId);
            $this->respond($result, $result['success'] ? 200 : 400);
            return;
        }

        if ($method === 'DELETE') {
            if (!Session::get('user_id')) {
                $this->respond(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
            }
            $userId = (int)Session::get('user_id');

            if ($action === 'all') {
                $ok = $service->removeAllFavorites($userId);
                $this->respond(['success' => $ok, 'message' => $ok ? 'Đã xóa sạch danh sách lưu.' : 'Xóa thất bại.']);
                return;
            }

            if (is_numeric($action)) {
                $postId = (int)$action;
                $ok = $service->removeFavorite($userId, $postId);
                $this->respond(['success' => $ok, 'message' => $ok ? 'Đã bỏ lưu tin đăng.' : 'Bỏ lưu thất bại.']);
                return;
            }
        }

        $this->respond(['success' => false, 'message' => 'API endpoint không hợp lệ.'], 404);
    }

    /**
     * API So sánh (Compare)
     * GET /api/compare
     * POST /api/compare/toggle
     * DELETE /api/compare/{id}
     * DELETE /api/compare/all
     */
    public function compare(string $action = ''): void
    {
        require_once APP_ROOT . '/app/services/DichVuSoSanh.php';
        $service = new CompareService();

        $isLoggedIn = (bool)Session::get('user_id');
        $userOrSession = $isLoggedIn ? (int)Session::get('user_id') : session_id();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'GET') {
            $data = $service->getComparisonListWithCache($userOrSession, $isLoggedIn);
            $this->respond(['success' => true, 'compare_list' => $data]);
            return;
        }

        if ($method === 'POST' && $action === 'toggle') {
            $input = $this->input();
            $postId = (int)($input['post_id'] ?? 0);
            $result = $service->toggleCompare($userOrSession, $postId, $isLoggedIn);
            $this->respond($result, $result['success'] ? 200 : 400);
            return;
        }

        if ($method === 'DELETE') {
            if ($action === 'all') {
                $ok = $service->removeAllCompare($userOrSession, $isLoggedIn);
                $this->respond(['success' => $ok, 'message' => $ok ? 'Đã xóa sạch danh sách so sánh.' : 'Xóa thất bại.']);
                return;
            }

            if (is_numeric($action)) {
                $postId = (int)$action;
                $ok = $service->removeCompare($userOrSession, $postId, $isLoggedIn);
                $this->respond(['success' => $ok, 'message' => $ok ? 'Đã xóa khỏi so sánh.' : 'Xóa thất bại.']);
                return;
            }
        }

        $this->respond(['success' => false, 'message' => 'API endpoint không hợp lệ.'], 404);
    }

    // =========================================================================
    // API HỒ SƠ CÁ NHÂN & BẢO MẬT (PROFILE)
    // =========================================================================

    /**
     * API Hồ sơ cá nhân & Bảo mật
     * GET /api/profile
     * PUT /api/profile
     * POST /api/profile/avatar
     * POST /api/profile/change-password
     * GET /api/profile/login-history
     * POST /api/profile/logout-all
     * POST /api/profile/send-otp
     * POST /api/profile/verify-otp
     */
    public function profile(string $action = ''): void
    {
        if (!Session::get('user_id')) {
            $this->respond(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
            return;
        }

        $userId = (int)Session::get('user_id');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        require_once APP_ROOT . '/app/services/DichVuHoSo.php';
        require_once APP_ROOT . '/app/services/DichVuOTP.php';
        require_once APP_ROOT . '/app/services/DichVuThuDienTu.php';
        require_once APP_ROOT . '/app/services/DichVuSMS.php';
        require_once APP_ROOT . '/app/repositories/LoginHistoryRepository.php';

        $profileSvc = new ProfileService();
        $otpSvc = new OTPService();
        $mailSvc = new MailService();
        $smsSvc = new SMSService();
        $historyRepo = new LoginHistoryRepository();

        if ($method === 'GET') {
            if ($action === 'login-history') {
                $page = max(1, (int)($_GET['page'] ?? 1));
                $limit = 15;
                $offset = ($page - 1) * $limit;
                $history = $historyRepo->getHistory($userId, $limit, $offset);
                $this->respond(['success' => true, 'login_history' => $history]);
                return;
            }

            if ($action === '') {
                $user = $profileSvc->getUserWithCache($userId);
                $this->respond(['success' => true, 'user' => $user]);
                return;
            }
        }

        if ($method === 'PUT' && $action === '') {
            $input = $this->input();
            $res = $profileSvc->updateProfile($userId, $input);
            $this->respond($res, $res['success'] ? 200 : 400);
            return;
        }

        if ($method === 'POST') {
            if ($action === 'avatar') {
                $input = $this->input();
                $croppedBase64 = $input['avatar_cropped'] ?? '';
                $fileName = '';

                if (!empty($croppedBase64)) {
                    $data = preg_replace('#^data:image/\w+;base64,#i', '', $croppedBase64);
                    $imgBinary = base64_decode($data);
                    if ($imgBinary === false) {
                        $this->respond(['success' => false, 'message' => 'Dữ liệu ảnh không hợp lệ.'], 400);
                        return;
                    }
                    $fileName = 'avatar_' . $userId . '_' . bin2hex(random_bytes(8)) . '.webp';
                    $uploadDir = APP_ROOT . '/public/uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                    $im = imagecreatefromstring($imgBinary);
                    if ($im !== false) {
                        $canvas = imagecreatetruecolor(300, 300);
                        imagealphablending($canvas, false);
                        imagesavealpha($canvas, true);
                        imagecopyresampled($canvas, $im, 0, 0, 0, 0, 300, 300, imagesx($im), imagesy($im));
                        imagewebp($canvas, $uploadDir . $fileName, 80);
                        imagedestroy($canvas);
                        imagedestroy($im);
                    } else {
                        $this->respond(['success' => false, 'message' => 'Không thể xử lý dữ liệu ảnh.'], 400);
                        return;
                    }
                } else {
                    $this->respond(['success' => false, 'message' => 'Thiếu dữ liệu ảnh đại diện.'], 400);
                    return;
                }

                $userRepo = new UserRepository();
                $oldUser = $profileSvc->getUserWithCache($userId);
                $oldAvatar = $oldUser->anh_dai_dien ?? '';

                if ($userRepo->updateField($userId, 'anh_dai_dien', $fileName)) {
                    if (!empty($oldAvatar) && $oldAvatar !== 'default-avatar.png') {
                        $oldPath = APP_ROOT . '/public/uploads/' . $oldAvatar;
                        if (file_exists($oldPath)) @unlink($oldPath);
                    }
                    $profileSvc->clearUserCache($userId);
                    $this->respond(['success' => true, 'message' => 'Cập nhật ảnh đại diện thành công.', 'avatar_url' => URL_ROOT . '/public/uploads/' . $fileName]);
                } else {
                    $this->respond(['success' => false, 'message' => 'Lỗi lưu ảnh đại diện.'], 500);
                }
                return;
            }

            if ($action === 'change-password') {
                $input = $this->input();
                $currentPassword = $input['current_password'] ?? '';
                $newPassword = $input['new_password'] ?? '';
                $logoutOthers = !empty($input['logout_others']);

                if (strlen($newPassword) < 6) {
                    $this->respond(['success' => false, 'message' => 'Mật khẩu mới phải từ 6 ký tự trở lên.'], 400);
                    return;
                }

                $res = $profileSvc->changePassword($userId, $currentPassword, $newPassword, $logoutOthers);
                $this->respond($res, $res['success'] ? 200 : 400);
                return;
            }

            if ($action === 'logout-all') {
                $input = $this->input();
                $keepCurrent = !empty($input['keep_current']);
                
                $userRepo = new UserRepository();
                $userRepo->invalidateAllSessions($userId);

                if ($keepCurrent) {
                    $user = $userRepo->findById($userId);
                    Session::set('auth_version', (int)($user->auth_version ?? 1));
                } else {
                    Session::destroy();
                }

                $this->respond(['success' => true, 'message' => 'Đăng xuất tất cả thiết bị thành công.']);
                return;
            }

            if ($action === 'send-otp') {
                $user = $profileSvc->getUserWithCache($userId);
                if (!$user || empty($user->dien_thoai)) {
                    $this->respond(['success' => false, 'message' => 'Số điện thoại trống.'], 400);
                    return;
                }
                $lastSentAt = (int)(Session::get('phone_otp_sent_at') ?: 0);
                $retryAfter = 60 - (time() - $lastSentAt);
                if ($retryAfter > 0) {
                    $this->respond(['success' => false, 'message' => "Vui lòng chờ {$retryAfter} giây trước khi gửi lại mã OTP."], 429);
                    return;
                }
                $otp = $otpSvc->generateOTP($userId, 'phone_verify');
                $delivery = $smsSvc->sendOTP((string)$user->dien_thoai, $otp);
                if (!$delivery['success']) {
                    $otpSvc->invalidateOTP($userId, 'phone_verify');
                    $this->respond($delivery, 502);
                    return;
                }
                Session::set('phone_otp_sent_at', time());
                $response = ['success' => true, 'message' => $delivery['message'], 'delivery' => $delivery['delivery'] ?? 'sms'];
                if (($delivery['delivery'] ?? '') === 'local_preview') $response['debug_otp'] = $otp;
                $this->respond($response);
                return;
            }

            if ($action === 'verify-otp') {
                $input = $this->input();
                $otp = trim($input['otp'] ?? '');
                $res = $otpSvc->verifyOTP($userId, $otp, 'phone_verify');

                if ($res['success']) {
                    $userRepo = new UserRepository();
                    $userRepo->updateField($userId, 'phone_verified', 1);
                    $profileSvc->clearUserCache($userId);
                }
                $this->respond($res, $res['success'] ? 200 : 400);
                return;
            }
        }

        $this->respond(['success' => false, 'message' => 'API endpoint không hợp lệ.'], 404);
    }

    /**
     * API RESTful cho Module Analytics.
     * URL: GET/POST /api/analytics/{action}/{id}
     */
    public function analytics(string $action = '', ...$params): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $userId = Session::get('user_id') ? (int)Session::get('user_id') : null;

        // 1. GET requests
        if ($method === 'GET') {
            if (!$userId) {
                $this->respond(['success' => false, 'message' => 'Bạn cần đăng nhập.'], 401);
                return;
            }

            require_once APP_ROOT . '/app/services/DichVuPhanTich.php';
            $analyticsSvc = new AnalyticsService();

            if ($action === 'dashboard') {
                $data = $analyticsSvc->dashboard($userId, $_GET);
                $this->respond(['success' => true, 'data' => $data]);
                return;
            }

            if ($action === 'post') {
                $postId = (int)($params[0] ?? 0);
                if ($postId <= 0) {
                    $this->respond(['success' => false, 'message' => 'Mã tin đăng không hợp lệ.'], 400);
                    return;
                }
                $data = $analyticsSvc->postDashboard($userId, $postId, $_GET);
                if (!$data) {
                    $this->respond(['success' => false, 'message' => 'Không tìm thấy tin đăng.'], 404);
                    return;
                }
                $this->respond(['success' => true, 'data' => $data]);
                return;
            }
        }

        // 2. POST requests (ghi nhận lượt click, view)
        if ($method === 'POST') {
            $input = $this->input();
            $postId = (int)($input['post_id'] ?? $_POST['post_id'] ?? 0);

            if ($postId <= 0) {
                $this->respond(['success' => false, 'message' => 'Mã tin đăng không hợp lệ.'], 400);
                return;
            }

            $validActions = ['view', 'call', 'chat', 'save', 'share', 'phone', 'zalo'];
            if (!in_array($action, $validActions, true)) {
                $this->respond(['success' => false, 'message' => 'Hành động không hợp lệ.'], 404);
                return;
            }

            require_once APP_ROOT . '/app/services/DichVuPhanTich.php';
            $analyticsSvc = new AnalyticsService();
            $accepted = $analyticsSvc->record($postId, $action, $userId);
            
            $this->respond([
                'success'  => true,
                'recorded' => $accepted,
                'message'  => $accepted ? 'Ghi nhận sự kiện thành công.' : 'Trùng lặp sự kiện trong vòng 30 phút (Spam/F5).'
            ]);
            return;
        }

        $this->respond(['success' => false, 'message' => 'API endpoint không hợp lệ.'], 404);
    }

    /**
     * API RESTful cho Module Trung tâm Thông báo.
     * URL: GET/POST/DELETE /api/notifications/{action}/{id}
     */
    public function notifications(string $action = '', ...$params): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $userId = Session::get('user_id') ? (int)Session::get('user_id') : null;

        // 1. Kết nối SSE Realtime
        if ($action === 'stream') {
            if (!$userId) {
                $this->respond(['success' => false, 'message' => 'Bạn cần đăng nhập.'], 401);
                return;
            }
            require_once APP_ROOT . '/app/services/DichVuThongBao.php';
            $notifSvc = new NotificationService();
            $notifSvc->getRealtime()->streamConnection($userId);
            return;
        }

        // Các hành động sau bắt buộc đăng nhập
        if (!$userId) {
            $this->respond(['success' => false, 'message' => 'Bạn cần đăng nhập.'], 401);
            return;
        }

        require_once APP_ROOT . '/app/services/DichVuThongBao.php';
        $notifSvc = new NotificationService();

        // 2. GET Requests
        if ($method === 'GET') {
            if ($action === 'unread-count') {
                $count = $notifSvc->getUnreadCount($userId);
                $this->respond(['success' => true, 'unread_count' => $count]);
                return;
            }

            if ($action === '' || $action === 'list') {
                $filter = $_GET['filter'] ?? 'all';
                $search = trim($_GET['search'] ?? '');
                $page   = max(1, (int)($_GET['page'] ?? 1));
                $limit  = 15;
                $offset = ($page - 1) * $limit;

                $repo = $notifSvc->getRepo();
                $notifications = $repo->paginateByUser($userId, $filter, $limit, $offset, $search);
                $total = $repo->countByUser($userId, $filter, $search);

                $this->respond([
                    'success'       => true,
                    'notifications' => $notifications,
                    'total'         => $total,
                    'unread_count'  => $notifSvc->getUnreadCount($userId)
                ]);
                return;
            }
        }

        // 3. POST Requests (đánh dấu đã đọc)
        if ($method === 'POST') {
            if ($action === 'read') {
                $notifId = (int)($params[0] ?? 0);
                if ($notifId <= 0) {
                    $this->respond(['success' => false, 'message' => 'Mã thông báo không hợp lệ.'], 400);
                    return;
                }
                if ($notifSvc->markRead($notifId, $userId)) {
                    $this->respond(['success' => true, 'unread_count' => $notifSvc->getUnreadCount($userId)]);
                    return;
                }
                $this->respond(['success' => false, 'message' => 'Không tìm thấy thông báo hoặc lỗi CSDL.'], 404);
                return;
            }

            if ($action === 'read-all') {
                if ($notifSvc->markAllRead($userId)) {
                    $this->respond(['success' => true, 'unread_count' => 0]);
                    return;
                }
                $this->respond(['success' => false, 'message' => 'Lỗi CSDL.'], 500);
                return;
            }
        }

        // 4. DELETE Requests (xóa thông báo)
        if ($method === 'DELETE') {
            // Nếu action là số nguyên (mã thông báo)
            if (ctype_digit($action)) {
                $notifId = (int)$action;
                if ($notifSvc->delete($notifId, $userId)) {
                    $this->respond(['success' => true, 'unread_count' => $notifSvc->getUnreadCount($userId)]);
                    return;
                }
                $this->respond(['success' => false, 'message' => 'Không tìm thấy thông báo hoặc lỗi CSDL.'], 404);
                return;
            }

            if ($action === 'clear') {
                if ($notifSvc->clear($userId)) {
                    $this->respond(['success' => true, 'unread_count' => 0]);
                    return;
                }
                $this->respond(['success' => false, 'message' => 'Lỗi CSDL.'], 500);
                return;
            }
        }

        $this->respond(['success' => false, 'message' => 'API endpoint không hợp lệ.'], 404);
    }

    /**
     * API RESTful cho Module Live Chat.
     * URL: GET/POST /api/chat/{action}
     */
    public function chat(string $action = '', ...$params): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $userId = Session::get('user_id') ? (int)Session::get('user_id') : null;
        $guestToken = Session::get('chat_guest_id') ?: null;

        require_once APP_ROOT . '/app/services/DichVuHoiThoai.php';
        require_once APP_ROOT . '/app/services/DichVuTroChuyen.php';
        require_once APP_ROOT . '/app/services/DichVuTaiLen.php';

        $convSvc = new ConversationService();
        $chatSvc = new ChatService();
        $uploadSvc = new UploadService();

        // 1. GET Requests
        if ($method === 'GET') {
            if ($action === '' || $action === 'list') {
                if (!$userId) {
                    $this->respond(['success' => false, 'message' => 'Bạn cần đăng nhập.'], 401);
                    return;
                }
                $list = $convSvc->getRepo()->listByUser($userId);
                $this->respond(['success' => true, 'conversations' => $list]);
                return;
            }

            if ($action === 'history') {
                $convId = (int)($_GET['conversation_id'] ?? 0);
                if ($convId <= 0) {
                    $this->respond(['success' => false, 'message' => 'Mã hội thoại không hợp lệ.'], 400);
                    return;
                }
                
                // Xác thực quyền truy cập
                $conv = $convSvc->getRepo()->findById($convId);
                if (!$conv) {
                    $this->respond(['success' => false, 'message' => 'Không tìm thấy cuộc trò chuyện.'], 404);
                    return;
                }
                
                if ($userId !== (int)$conv->customer_id && $userId !== (int)$conv->seller_id && $userId !== (int)$conv->staff_id && $guestToken !== $conv->customer_guest_token) {
                    $this->respond(['success' => false, 'message' => 'Bạn không có quyền truy cập cuộc trò chuyện này.'], 403);
                    return;
                }

                $messages = $chatSvc->getMessageRepo()->getHistory($convId);
                $this->respond(['success' => true, 'messages' => $messages]);
                return;
            }
        }

        // 2. POST Requests
        if ($method === 'POST') {
            if ($action === 'send') {
                $convId = (int)($_POST['conversation_id'] ?? 0);
                $message = trim($_POST['message'] ?? '');
                $msgType = trim($_POST['message_type'] ?? 'text');
                $attachment = trim($_POST['attachment'] ?? '');

                if ($convId <= 0 || ($message === '' && $attachment === '')) {
                    $this->respond(['success' => false, 'message' => 'Thông tin không hợp lệ.'], 400);
                    return;
                }

                $senderName = Session::get('user_name') ?: 'Khách hàng';
                $msgId = $chatSvc->sendMessage($convId, $userId, $senderName, $message, $msgType, $attachment);
                
                if ($msgId > 0) {
                    $this->respond(['success' => true, 'message_id' => $msgId]);
                } else {
                    $this->respond(['success' => false, 'message' => 'Không thể gửi tin nhắn.'], 500);
                }
                return;
            }

            if ($action === 'read') {
                $convId = (int)($_POST['conversation_id'] ?? 0);
                if ($convId <= 0) {
                    $this->respond(['success' => false, 'message' => 'Mã hội thoại không hợp lệ.'], 400);
                    return;
                }
                $chatSvc->getMessageRepo()->markAsRead($convId, $userId);
                $this->respond(['success' => true]);
                return;
            }

            if ($action === 'upload') {
                $fileName = $uploadSvc->chatFile('file');
                if ($fileName) {
                    $this->respond([
                        'success' => true,
                        'file_name' => $fileName,
                        'file_url' => URL_ROOT . '/public/uploads/chats/' . $fileName
                    ]);
                } else {
                    $this->respond(['success' => false, 'message' => 'Upload tệp đính kèm thất bại.'], 400);
                }
                return;
            }

            if ($action === 'close') {
                $convId = (int)($_POST['conversation_id'] ?? 0);
                if ($convId <= 0) {
                    $this->respond(['success' => false, 'message' => 'Mã hội thoại không hợp lệ.'], 400);
                    return;
                }
                $convSvc->close($convId);
                $this->respond(['success' => true]);
                return;
            }
        }

        $this->respond(['success' => false, 'message' => 'API endpoint không hợp lệ.'], 404);
    }

    /**
     * API RESTful quản lý bất động sản dành cho Admin.
     * URL: GET/POST /api/admin/property/{action}
     */
    public function adminProperty(string $action = '', ...$params): void
    {
        // 1. Xác thực quyền Admin
        if ((int)Session::get('user_id') <= 0 || (int)Session::get('user_role_id') !== 1) {
            $this->respond(['success' => false, 'message' => 'Bạn không có quyền truy cập.'], 403);
            return;
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $adminId = (int)Session::get('user_id');

        require_once APP_ROOT . '/app/services/DichVuBatDongSan.php';
        require_once APP_ROOT . '/app/services/DichVuPheDuyet.php';
        
        $propertySvc = new PropertyService();
        $approvalSvc = new ApprovalService();

        // 2. Xử lý GET Requests
        if ($method === 'GET') {
            if ($action === '' || $action === 'list') {
                $filters = [
                    'status'           => $_GET['status'] ?? '',
                    'vip_level'        => $_GET['vip_level'] ?? '',
                    'category_id'      => $_GET['category_id'] ?? '',
                    'transaction_type' => $_GET['transaction_type'] ?? '',
                    'search'           => $_GET['search'] ?? ''
                ];
                $page = max(1, (int)($_GET['page'] ?? 1));
                $list = $propertySvc->adminList($filters, $page, 50);
                $total = $propertySvc->adminCount($filters);

                $this->respond(['success' => true, 'properties' => $list, 'total' => $total]);
                return;
            }

            if (ctype_digit($action)) {
                $postId = (int)$action;
                $post = $propertySvc->adminFind($postId);
                if ($post) {
                    $this->respond(['success' => true, 'property' => $post]);
                } else {
                    $this->respond(['success' => false, 'message' => 'Tin đăng không tồn tại.'], 404);
                }
                return;
            }
        }

        // 3. Xử lý POST Requests
        if ($method === 'POST') {
            if ($action === 'approve') {
                $postId = (int)($_POST['id'] ?? 0);
                if ($approvalSvc->approve($postId, $adminId)) {
                    $this->respond(['success' => true, 'message' => 'Duyệt tin thành công.']);
                } else {
                    $this->respond(['success' => false, 'message' => 'Không thể duyệt tin đăng.'], 500);
                }
                return;
            }

            if ($action === 'reject') {
                $postId = (int)($_POST['id'] ?? 0);
                $reason = trim($_POST['reason'] ?? 'Tin đăng không hợp lệ.');
                if ($approvalSvc->reject($postId, $adminId, $reason)) {
                    $this->respond(['success' => true, 'message' => 'Đã từ chối duyệt tin.']);
                } else {
                    $this->respond(['success' => false, 'message' => 'Lỗi từ chối tin.'], 500);
                }
                return;
            }

            if ($action === 'delete') {
                $postId = (int)($_POST['id'] ?? 0);
                if ($propertySvc->hardDelete($postId)) {
                    $this->respond(['success' => true, 'message' => 'Xóa tin thành công.']);
                } else {
                    $this->respond(['success' => false, 'message' => 'Lỗi xóa tin.'], 500);
                }
                return;
            }

            if ($action === 'hide') {
                $postId = (int)($_POST['id'] ?? 0);
                if ($propertySvc->toggleStatus($postId, 'hide')) {
                    $this->respond(['success' => true, 'message' => 'Đã ẩn tin đăng.']);
                } else {
                    $this->respond(['success' => false, 'message' => 'Lỗi ẩn tin.'], 500);
                }
                return;
            }

            if ($action === 'show') {
                $postId = (int)($_POST['id'] ?? 0);
                if ($propertySvc->toggleStatus($postId, 'show')) {
                    $this->respond(['success' => true, 'message' => 'Đã hiển thị tin đăng.']);
                } else {
                    $this->respond(['success' => false, 'message' => 'Lỗi hiển thị tin.'], 500);
                }
                return;
            }

            if ($action === 'vip') {
                $postId = (int)($_POST['id'] ?? 0);
                $vipLevel = (int)($_POST['vip_level'] ?? 0);
                $days = (int)($_POST['days'] ?? 7);

                if ($propertySvc->changeVip($postId, $vipLevel, $days)) {
                    $this->respond(['success' => true, 'message' => 'Cập nhật VIP thành công.']);
                } else {
                    $this->respond(['success' => false, 'message' => 'Lỗi đổi gói VIP.'], 500);
                }
                return;
            }
        }

        $this->respond(['success' => false, 'message' => 'API endpoint không hợp lệ.'], 404);
    }

    /**
     * API RESTful quản lý người dùng dành cho Admin.
     * URL: GET/POST/PUT/DELETE /api/admin/users/{action}/{subAction}
     */
    public function adminUsers(string $action = '', string $subAction = ''): void
    {
        // 1. Xác thực quyền Admin
        if ((int)Session::get('user_id') <= 0 || (int)Session::get('user_role_id') !== 1) {
            $this->respond(['success' => false, 'message' => 'Bạn không có quyền truy cập.'], 403);
            return;
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $adminId = (int)Session::get('user_id');

        require_once APP_ROOT . '/app/services/DichVuNguoiDung.php';
        require_once APP_ROOT . '/app/services/DichVuBaoMat.php';
        require_once APP_ROOT . '/app/services/DichVuViDienTu.php';

        $userSvc = new UserService();
        $securitySvc = new SecurityService();
        $walletSvc = new WalletService();

        // 2. Xử lý GET Requests
        if ($method === 'GET') {
            if ($action === '' || $action === 'list') {
                $filters = [
                    'role_id'        => $_GET['role_id'] ?? '',
                    'status'         => $_GET['status'] ?? '',
                    'email_verified' => $_GET['email_verified'] ?? '',
                    'phone_verified' => $_GET['phone_verified'] ?? '',
                    'search'         => $_GET['search'] ?? '',
                    'min_balance'    => $_GET['min_balance'] ?? '',
                    'start_date'     => $_GET['start_date'] ?? ''
                ];
                $page = max(1, (int)($_GET['page'] ?? 1));
                $users = $userSvc->adminList($filters, $page, 50);
                $total = $userSvc->adminCount($filters);

                $this->respond(['success' => true, 'users' => $users, 'total' => $total]);
                return;
            }

            if (ctype_digit($action)) {
                $userId = (int)$action;
                $user = $userSvc->adminFind($userId);
                if ($user) {
                    $this->respond(['success' => true, 'user' => $user]);
                } else {
                    $this->respond(['success' => false, 'message' => 'Người dùng không tồn tại.'], 404);
                }
                return;
            }
        }

        // 3. Xử lý POST / PUT / DELETE
        if ($method === 'POST') {
            if ($action === 'create') {
                $res = $userSvc->create($_POST);
                if ($res['success']) {
                    $this->respond(['success' => true, 'message' => 'Tạo người dùng thành công.', 'user_id' => $res['id']]);
                } else {
                    $this->respond(['success' => false, 'errors' => $res['errors'] ?? [], 'message' => 'Lỗi xác thực dữ liệu.'], 400);
                }
                return;
            }

            if ($action === 'lock') {
                $userId = (int)($_POST['id'] ?? 0);
                $duration = (int)($_POST['duration'] ?? 0); // 0 = permanent, else days
                $reason = trim($_POST['reason'] ?? 'Vi phạm điều khoản.');

                if ($userSvc->lock($userId, $duration, $reason, $adminId)) {
                    $this->respond(['success' => true, 'message' => 'Khóa tài khoản thành công.']);
                } else {
                    $this->respond(['success' => false, 'message' => 'Không thể khóa tài khoản.'], 500);
                }
                return;
            }

            if ($action === 'unlock') {
                $userId = (int)($_POST['id'] ?? 0);
                if ($userSvc->unlock($userId, $adminId)) {
                    $this->respond(['success' => true, 'message' => 'Mở khóa tài khoản thành công.']);
                } else {
                    $this->respond(['success' => false, 'message' => 'Không thể mở khóa tài khoản.'], 500);
                }
                return;
            }

            if ($action === 'reset-password') {
                $userId = (int)($_POST['id'] ?? 0);
                $newPassword = trim($_POST['password'] ?? '');

                if (empty($newPassword)) {
                    $this->respond(['success' => false, 'message' => 'Mật khẩu mới không được để trống.'], 400);
                    return;
                }

                if ($securitySvc->adminResetPassword($userId, $newPassword, $adminId)) {
                    $this->respond(['success' => true, 'message' => 'Đổi mật khẩu thành công.']);
                } else {
                    $this->respond(['success' => false, 'message' => 'Lỗi đặt lại mật khẩu.'], 500);
                }
                return;
            }

            if ($action === 'add-money') {
                $userId = (int)($_POST['id'] ?? 0);
                $amount = (int)($_POST['amount'] ?? 0);
                $reason = trim($_POST['reason'] ?? 'Admin cộng tiền thủ công.');

                if ($amount <= 0) {
                    $this->respond(['success' => false, 'message' => 'Số tiền phải lớn hơn 0.'], 400);
                    return;
                }

                if ($walletSvc->addMoney($userId, $amount, $reason, $adminId)) {
                    $this->respond(['success' => true, 'message' => 'Cộng tiền thành công.']);
                } else {
                    $this->respond(['success' => false, 'message' => 'Lỗi nạp tiền vào ví.'], 500);
                }
                return;
            }

            if ($action === 'sub-money') {
                $userId = (int)($_POST['id'] ?? 0);
                $amount = (int)($_POST['amount'] ?? 0);
                $reason = trim($_POST['reason'] ?? 'Admin trừ tiền thủ công.');

                if ($amount <= 0) {
                    $this->respond(['success' => false, 'message' => 'Số tiền phải lớn hơn 0.'], 400);
                    return;
                }

                if ($walletSvc->subMoney($userId, $amount, $reason, $adminId)) {
                    $this->respond(['success' => true, 'message' => 'Trừ tiền thành công.']);
                } else {
                    $this->respond(['success' => false, 'message' => 'Lỗi trừ tiền ví (Có thể số dư không đủ).'], 500);
                }
                return;
            }

            if ($action === 'update' && ctype_digit($subAction)) {
                $userId = (int)$subAction;
                $res = $userSvc->update($userId, $_POST);
                if ($res['success']) {
                    $this->respond(['success' => true, 'message' => 'Cập nhật hồ sơ thành công.']);
                } else {
                    $this->respond(['success' => false, 'errors' => $res['errors'] ?? [], 'message' => 'Lỗi cập nhật.'], 400);
                }
                return;
            }
        }

        if ($method === 'PUT' && $action === 'update' && ctype_digit($subAction)) {
            $userId = (int)$subAction;
            parse_str(file_get_contents("php://input"), $putData);
            $res = $userSvc->update($userId, $putData);
            if ($res['success']) {
                $this->respond(['success' => true, 'message' => 'Cập nhật hồ sơ thành công.']);
            } else {
                $this->respond(['success' => false, 'errors' => $res['errors'] ?? [], 'message' => 'Lỗi cập nhật.'], 400);
            }
            return;
        }

        if ($method === 'DELETE' && $action === 'delete' && ctype_digit($subAction)) {
            $userId = (int)$subAction;
            if ($userSvc->delete($userId, $adminId)) {
                $this->respond(['success' => true, 'message' => 'Xóa tài khoản thành công.']);
            } else {
                $this->respond(['success' => false, 'message' => 'Không thể xóa tài khoản.'], 500);
            }
            return;
        }

        $this->respond(['success' => false, 'message' => 'API endpoint không hợp lệ.'], 404);
    }

    /**
     * API Tỉnh/Quận/Phường.
     * GET /api/location/province
     * GET /api/location/district/{province}
     * GET /api/location/ward/{district}
     */
    public function location(string $subType = '', string $parentCode = ''): void
    {
        $service = new LocationService();
        if ($subType === 'province') {
            $this->respond(['success' => true, 'data' => $service->getProvinces()]);
        } elseif ($subType === 'district' && !empty($parentCode)) {
            $this->respond(['success' => true, 'data' => $service->getDistrictsByProvince($parentCode)]);
        } elseif ($subType === 'ward' && !empty($parentCode)) {
            $this->respond(['success' => true, 'data' => $service->getWardsByDistrict($parentCode)]);
        }
        $this->respond(['success' => false, 'message' => 'Địa chỉ không hợp lệ.'], 404);
    }

    /**
     * API quản trị danh mục dùng chung.
     * GET /api/admin/categories
     * POST /api/admin/categories
     * PUT /api/admin/categories/{id}
     * DELETE /api/admin/categories/{id}
     */
    public function admin(string $type = '', string $id = ''): void
    {
        if (in_array($type, ['roles','permissions','assign-role','assign-permission'], true)) {
            if (!Session::get('user_id') || (int)Session::get('user_role_id') !== 1) $this->respond(['success'=>false,'message'=>'Chỉ Super Admin được quản lý RBAC.'],403);
            $method=$_SERVER['REQUEST_METHOD']??'GET';$repo=new RbacRepository();$service=new RoleService($repo);$admin=(int)Session::get('user_id');
            if($type==='roles'&&$method==='GET')$this->respond(['success'=>true,'data'=>$repo->roles()]);
            if($type==='permissions'&&$method==='GET')$this->respond(['success'=>true,'data'=>$repo->permissions()]);
            if($type==='roles'&&in_array($method,['POST','PUT'],true)){if(!Csrf::verify(false))$this->respond(['success'=>false,'message'=>'CSRF không hợp lệ.'],403);$input=$method==='PUT'?(json_decode(file_get_contents('php://input'),true)?:[]):$_POST;$result=$service->save($id!==''?(int)$id:null,$input,$admin);$this->respond($result,$result['success']?200:422);}
            if($type==='roles'&&$method==='DELETE'){$ok=$service->delete((int)$id,$admin);$this->respond(['success'=>$ok],$ok?200:422);}
            if($type==='assign-role'&&$method==='POST'){if(!Csrf::verify(false))$this->respond(['success'=>false,'message'=>'CSRF không hợp lệ.'],403);$ok=$service->assignUser((int)($_POST['user_id']??0),(int)($_POST['role_id']??0),$admin);$this->respond(['success'=>$ok],$ok?200:422);}
            if($type==='assign-permission'&&$method==='POST'){if(!Csrf::verify(false))$this->respond(['success'=>false,'message'=>'CSRF không hợp lệ.'],403);$ok=$service->permissions((int)($_POST['role_id']??0),$_POST['permissions']??[],$admin);$this->respond(['success'=>$ok],$ok?200:422);}
            $this->respond(['success'=>false,'message'=>'API RBAC không hợp lệ.'],405);
        }
        if ($type === 'settings') {
            if (!Session::get('user_id') || (int)Session::get('user_role_id') !== 1) $this->respond(['success'=>false,'message'=>'Không có quyền truy cập.'],403);
            $method=$_SERVER['REQUEST_METHOD']??'GET';
            if($method==='GET'){
                $settings=(new SettingService())->all();
                foreach(['smtp_pass','payos_api_key','payos_checksum_key'] as$secret)unset($settings[$secret]);
                $this->respond(['success'=>true,'data'=>$settings]);
            }
            if($method==='POST'&&$id==='cache-clear'){if(!Csrf::verify(false))$this->respond(['success'=>false,'message'=>'CSRF không hợp lệ.'],403);$n=(new SystemCacheService())->clear((string)($_POST['type']??'all'));$this->respond(['success'=>true,'files'=>$n]);}
            if($method==='POST'&&$id==='backup'){if(!Csrf::verify(false))$this->respond(['success'=>false,'message'=>'CSRF không hợp lệ.'],403);$ok=(new SystemBackupService())->create((string)($_POST['type']??'database'),(int)Session::get('user_id'));$this->respond(['success'=>$ok],$ok?200:500);}
            if($method==='POST'){if(!Csrf::verify(false))$this->respond(['success'=>false,'message'=>'CSRF không hợp lệ.'],403);$result=(new SettingService())->update($_POST,(int)Session::get('user_id'));$this->respond($result,$result['success']?200:422);}
            $this->respond(['success'=>false,'message'=>'Method không được hỗ trợ.'],405);
        }
        if ($type === 'report') {
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
                $this->respond(['success' => false, 'message' => 'Method không được hỗ trợ.'], 405);
            }
            if (!Session::get('user_id') || (int)Session::get('user_role_id') !== 1) {
                $this->respond(['success' => false, 'message' => 'Không có quyền truy cập.'], 403);
            }
            $sections = ['dashboard','revenue','users','posts','transactions','chat'];
            $section = in_array($id, $sections, true) ? $id : 'dashboard';
            $this->respond(['success' => true, 'data' => (new AdminReportService())->build($section, $_GET)]);
        }
        if ($type === 'categories') {
            $this->adminCategories($id);
            return;
        }
        $this->respond(['success' => false, 'message' => 'API Admin không hợp lệ.'], 404);
    }

    private function adminCategories(string $id = ''): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $service = new CategoryService();
        $adminId = (int)(Session::get('user_id') ?: 0);

        if ($method === 'GET') {
            if ($id !== '') {
                $item = $service->catRepo->findById((int)$id);
                if ($item) {
                    $this->respond(['success' => true, 'data' => $item]);
                } else {
                    $this->respond(['success' => false, 'message' => 'Không tìm thấy danh mục.'], 404);
                }
            }
            $this->respond(['success' => true, 'data' => $service->getCategories()]);
        }

        // POST / PUT / DELETE yêu cầu quyền Admin (role_id = 1)
        if ((int)Session::get('user_role_id') !== 1) {
            $this->respond(['success' => false, 'message' => 'Quyền truy cập bị từ chối.'], 403);
        }

        if ($method === 'POST') {
            $input = $this->input();
            if (empty($input['name'])) {
                $this->respond(['success' => false, 'message' => 'Tên danh mục là bắt buộc.'], 400);
            }
            // Tự tạo slug nếu thiếu
            if (empty($input['slug'])) {
                $input['slug'] = $this->createSlug($input['name']);
            }
            $idCreated = $service->createCategory($input, $adminId);
            if ($idCreated) {
                $this->respond(['success' => true, 'id' => $idCreated, 'message' => 'Tạo danh mục thành công.'], 201);
            } else {
                $this->respond(['success' => false, 'message' => 'Không thể tạo danh mục.'], 500);
            }
        }

        if ($method === 'PUT') {
            $input = $this->input();
            if (empty($input['name'])) {
                $this->respond(['success' => false, 'message' => 'Tên danh mục là bắt buộc.'], 400);
            }
            if (empty($input['slug'])) {
                $input['slug'] = $this->createSlug($input['name']);
            }
            $ok = $service->updateCategory((int)$id, $input, $adminId);
            if ($ok) {
                $this->respond(['success' => true, 'message' => 'Cập nhật danh mục thành công.']);
            } else {
                $this->respond(['success' => false, 'message' => 'Không thể cập nhật danh mục.'], 500);
            }
        }

        if ($method === 'DELETE') {
            $ok = $service->deleteCategory((int)$id, $adminId);
            if ($ok) {
                $this->respond(['success' => true, 'message' => 'Xóa danh mục thành công.']);
            } else {
                $this->respond(['success' => false, 'message' => 'Không thể xóa danh mục này do đang được liên kết sử dụng.'], 400);
            }
        }
    }
}
