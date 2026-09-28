<?php
/**
 * Controller NguoiDung - Xử lý các chức năng của người dùng đã đăng nhập.
 * URL: /nguoi-dung/{method}  hoặc /register, /login, /logout
 *
 * Quyền truy cập: Người dùng đã đăng nhập (trừ register, forgotPassword)
 */
class NguoiDungController extends Controller
{
    // ==========================================
    // DASHBOARD CỦA NGƯỜI DÙNG
    // ==========================================

    public function index(): void
    {
        if (Auth::isLoggedIn()) {
            $this->redirect('nguoi-dung/dashboard');
        } else {
            $this->redirect('nguoi-dung/dang-nhap');
        }
    }

    /**
     * Trang tong quan cua nguoi dung: hien thi thong tin ca nhan va danh sach tin dang.
     * URL: GET /nguoi-dung/dashboard
     */
    public function dashboard(): void
    {
        $this->kiemTraDangNhap();
        $userId      = (int)Session::get('user_id');
        $userModel   = $this->model('NguoiDung');
        $projectModel= $this->model('DuAn');

        // Chạy ngầm tự động gia hạn VIP (thụ động)
        try {
            require_once "../app/services/DichVuTuDongGiaHan.php";
            AutoRenewService::run();
        } catch (Exception $e) {
            error_log('Passive Auto Renew Error: ' . $e->getMessage());
        }

        $perPage      = 10;
        $totalProjects = $projectModel->demTheoChuSoHuu($userId);
        $totalPages   = max(1, (int)ceil($totalProjects / $perPage));
        $currentPage  = max(1, (int)($_GET['page'] ?? 1));
        $currentPage  = min($currentPage, $totalPages);

        $data = [
            'title'      => 'Trang Quản Lý - ' . SITE_NAME,
            'user'       => $userModel->layTheoId($userId),
            'myProjects' => $projectModel->layTheoChuSoHuuPhanTrang(
                $userId,
                $perPage,
                ($currentPage - 1) * $perPage
            ),
            'totalProjects' => $totalProjects,
            'currentPage'   => $currentPage,
            'totalPages'    => $totalPages,
            'totalViews'    => $projectModel->tongLuotXemTheoUser($userId),
            'vipPrices'     => (new PricingService())->vipDailyPrices(),
            'upPackages'    => (new PricingService())->upPackages(),
        ];
        $data['discount'] = $this->tinhMucGiamGia((int)$data['totalViews']);
        $data['analyticsQuick'] = (new AnalyticsService())->quick($userId);
        
        $this->view('nguoi-dung/dashboard', $data);
    }

    /** Dashboard analytics for every listing owned by the signed-in user. */
    public function analytics(): void
    {
        $this->kiemTraDangNhap();
        $analytics = (new AnalyticsService())->dashboard((int)Session::get('user_id'), $_GET);
        $this->view('nguoi-dung/analytics', [
            'title' => 'Thống kê hiệu quả tin đăng - ' . SITE_NAME,
            'analytics' => $analytics,
        ]);
    }

    /** Analytics details for one owned listing. */
    public function postAnalytics(int $id): void
    {
        $this->kiemTraDangNhap();
        $analytics = (new AnalyticsService())->postDashboard((int)Session::get('user_id'), $id, $_GET);
        if (!$analytics) {
            http_response_code(404);
            $this->view('errors/404', ['title'=>'Không tìm thấy tin đăng']);
            return;
        }
        $this->view('nguoi-dung/post_analytics', [
            'title' => 'Analytics tin đăng - ' . SITE_NAME,
            'analytics' => $analytics,
        ]);
    }

    // ==========================================
    // ĐĂNG KÝ TÀI KHOẢN
    // ==========================================

    /**
     * Đăng ký tài khoản – delegate sang AuthController (module mới).
     * URL: GET/POST /nguoi-dung/register
     */
    public function register(): void
    {
        $this->loadAuthController()->register();
    }

    // ==========================================
    // ĐĂNG TIN BẤT ĐỘNG SẢN
    // ==========================================

    /**
     * Hien thi form dang tin (GET) va xu ly tao tin dang moi (POST).
     * Tinh muc giam gia theo tong luot xem, kiem tra so du vi va luot up tin.
     * URL: GET/POST /nguoi-dung/post
     */
    public function post(): void
    {
        $this->kiemTraDangNhap();
        $userId      = (int)Session::get('user_id');
        $projectModel= $this->model('DuAn');
        $walletModel = $this->model('ViDienTu');
        $userModel   = $this->model('NguoiDung');
        $categoryModel = $this->model('DanhMuc');
        $categories = $categoryModel->layTheoLoai('du_an');

        // Tinh uu dai dua tren tong luot xem cua nguoi dung
        $tongLuotXem = $projectModel->tongLuotXemTheoUser($userId);
        $giamGia     = $this->tinhMucGiamGia($tongLuotXem);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::verify();
            // Kiem tra CAPTCHA
            if (!$this->kiemTraCaptcha('captcha_post')) {
                Session::set('error', 'Mã xác nhận không chính xác!');
                $this->redirect('nguoi-dung/post');
                return;
            }

            $vipLevel     = (int)($_POST['vip_level'] ?? 0);
            $soNgay       = (int)($_POST['days'] ?? 0);
            $goiUpGia     = (int)($_POST['up_package'] ?? 0);
            $categoryId   = (int)($_POST['category'] ?? 0);
            $validCategoryIds = array_map(static fn($category) => (int)$category->id, $categories);
            $validUpPrices = array_values((new PricingService())->upPackages());

            if (!in_array($categoryId, $validCategoryIds, true)
                || !in_array($vipLevel, [0, 1, 2, 3, 4, 5], true)
                || ($vipLevel > 0 && !in_array($soNgay, [7, 15, 30], true))
                || ($goiUpGia > 0 && !in_array($goiUpGia, $validUpPrices, true))) {
                Session::set('error', 'Thông tin danh mục hoặc gói dịch vụ không hợp lệ.');
                $this->redirect('nguoi-dung/post');
                return;
            }
            $giaCuoi      = $this->tinhGiaCuoi($vipLevel, $soNgay, $goiUpGia, $giamGia);

            $nguoiDung = $userModel->layTheoId($userId);

            // Kiem tra so du va luot up tin
            if (!$this->kiemTraDuDieuKienDangTin($nguoiDung, $giaCuoi, $goiUpGia)) {
                $this->redirect('nguoi-dung/post');
                return;
            }

            // Upload anh va tao tin dang
            $anhUpload = $this->uploadNhieuAnh('images', '../public/uploads/');
            if (empty($anhUpload)) {
                Session::set('error', 'Vui lòng upload ít nhất 1 hình ảnh cho bất động sản!');
                $this->redirect('nguoi-dung/post');
                return;
            }

            $dataTinDang = $this->chuanBiDuLieuTinDang($userId, $anhUpload[0], $vipLevel, $soNgay);
            try {
                $projectModel->beginTransaction();
                $idTinMoi = $projectModel->taoTinDang($dataTinDang);
                if (!$idTinMoi) {
                    throw new RuntimeException('Không thể tạo tin đăng.');
                }

                foreach ($anhUpload as $idx => $tenAnh) {
                    if (!$projectModel->themAnh($idTinMoi, $tenAnh, $idx)) {
                        throw new RuntimeException('Không thể lưu hình ảnh tin đăng.');
                    }
                }

                if ($giaCuoi > 0) {
                    if (!$this->xuLyThanhToan($userId, $walletModel, $userModel, $vipLevel, $soNgay, $goiUpGia, $giaCuoi, $giamGia, $idTinMoi)) {
                        throw new RuntimeException('Không thể thanh toán gói dịch vụ.');
                    }
                }

                if (!$userModel->truLuotUp($userId)) {
                    throw new RuntimeException('Không đủ lượt đăng tin.');
                }

                $projectModel->commit();

                Session::set('success', 'Đăng tin thành công! Đang chờ Admin duyệt trong 24h.');
                $this->redirect('nguoi-dung/dashboard');
            } catch (Throwable $exception) {
                $projectModel->rollBack();
                foreach ($anhUpload as $uploadedFile) {
                    $path = '../public/uploads/' . $uploadedFile;
                    if (is_file($path)) {
                        unlink($path);
                    }
                }
                error_log('[CREATE PROJECT] ' . $exception->getMessage());
                Session::set('error', 'Lỗi khi lưu tin đăng. Vui lòng thử lại!');
                $this->redirect('nguoi-dung/post');
            }
            return;
        }

        // GET: Hien thi form
        $captcha = $this->taoMaBaoMat();
        Session::set('captcha_post', $captcha);
        $this->view('nguoi-dung/post', [
            'title'      => 'Đăng Tin BĐS - ' . SITE_NAME,
            'discount'   => $giamGia,
            'totalViews' => $tongLuotXem,
            'captcha'    => $captcha,
            'vipPrices'  => (new PricingService())->vipDailyPrices(),
            'upPackages' => (new PricingService())->upPackages(),
            'upTurns'    => (int)($userModel->layTheoId($userId)->luot_up_tin ?? 0),
            'categories' => $categories,
        ]);
    }

    // ==========================================
    // SỬA TIN ĐĂNG
    // ==========================================

    /**
     * Hien thi form sua tin (GET) va xu ly cap nhat tin dang (POST).
     * Chi cho phep sua tin dang cua chinh minh (kiem tra ma_nguoi_dung).
     * URL: GET/POST /nguoi-dung/editPost/{id}
     *
     * @param int $id ID tin dang can sua
     */
    public function editPost(int $id): void
    {
        $this->kiemTraDangNhap();
        $userId       = (int)Session::get('user_id');
        $projectModel = $this->model('DuAn');

        // Lay tin dang va kiem tra so huu qua Model (khong truy van DB truc tiep trong Controller)
        $tinDang = $projectModel->findById($id);

        if (!$tinDang || (int)$tinDang->ma_nguoi_dung !== $userId) {
            Session::set('error', 'Tin đăng không tồn tại hoặc bạn không có quyền sửa!');
            $this->redirect('nguoi-dung/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::verify();
            // Xu ly vi tri moi
            $viTriMoi = !empty($_POST['province'])
                ? trim($_POST['province']) . ' ' . trim($_POST['district'] ?? '') . ' ' . trim($_POST['ward'] ?? '') . ' ' . trim($_POST['address'] ?? '')
                : trim($_POST['vi_tri_old'] ?? '');

            $data = [
                'tieu_de'    => trim($_POST['title'] ?? ''),
                'gia'        => trim($_POST['gia'] ?? 0),
                'dien_tich'  => trim($_POST['dien_tich'] ?? 0),
                'vi_tri'     => $viTriMoi,
                'mo_ta'      => trim($_POST['mo_ta'] ?? ''),
                'anh_thu_nho'=> '',
            ];

            // Upload anh moi neu co
            $anhMoi = $this->uploadNhieuAnh('images', '../public/uploads/');
            if (!empty($anhMoi)) {
                $data['anh_thu_nho'] = $anhMoi[0];
            }

            if ($projectModel->capNhatBoiUser($id, $userId, $data)) {
                // Cap nhat anh neu co anh moi
                if (!empty($anhMoi)) {
                    $projectModel->xoaAnhCu($id);
                    foreach ($anhMoi as $idx => $tenAnh) {
                        $projectModel->themAnh($id, $tenAnh, $idx);
                    }
                }
                Session::set('success', 'Cập nhật tin đăng thành công!');
            } else {
                Session::set('error', 'Có lỗi xảy ra khi cập nhật tin!');
            }
            $this->redirect('nguoi-dung/dashboard');
            return;
        }

        $this->view('nguoi-dung/edit_post', [
            'title'   => 'Sửa Tin Đăng - ' . SITE_NAME,
            'project' => $tinDang,
            'images'  => $projectModel->layAnhDuAn($id),
        ]);
    }

    // ==========================================
    // HỒ SƠ CÁ NHÂN
    // ==========================================

    /**
     * Hien thi va cap nhat ho so ca nhan (ten, so dien thoai).
     * URL: GET/POST /nguoi-dung/profile
     */
    public function profile(): void
    {
        $this->kiemTraDangNhap();
        $userId    = (int)Session::get('user_id');
        $userModel = $this->model('NguoiDung');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::verify();
            $data = [
                'id'    => $userId,
                'name'  => trim($_POST['name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
            ];
            if ($userModel->capNhatHoSo($data)) {
                AuditMiddleware::enrich('Cập nhật hồ sơ cá nhân', [], ['name'=>$data['name'],'phone'=>$data['phone']], 'nguoi_dung', $userId);
                Session::set('user_name', $data['name']);
                Session::set('success', 'Cập nhật thông tin thành công!');
            } else {
                Session::set('error', 'Có lỗi xảy ra, vui lòng thử lại.');
            }
            $this->redirect('nguoi-dung/profile');
            return;
        }

        $this->view('nguoi-dung/profile', [
            'title' => 'Hồ Sơ Cá Nhân - ' . SITE_NAME,
            'user'  => $userModel->layTheoId($userId),
        ]);
    }

    // ==========================================
    // ĐỔI MẬT KHẨU
    // ==========================================

    /**
     * Hien thi form doi mat khau (GET) va xu ly doi mat khau (POST).
     * Yeu cau xac thuc mat khau hien tai truoc khi doi.
     * URL: GET/POST /nguoi-dung/change_password
     */
    public function change_password(): void
    {
        $this->kiemTraDangNhap();
        $userId    = (int)Session::get('user_id');
        $userModel = $this->model('NguoiDung');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Bao ve CSRF
            Csrf::verify();

            // Sanitize input
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT) ?: [];
            $matKhauHienTai = trim($_POST['current_password'] ?? '');
            $matKhauMoi     = trim($_POST['new_password'] ?? '');
            $xacNhan        = trim($_POST['confirm_password'] ?? '');
            $nguoiDung      = $userModel->layTheoId($userId);

            if (empty($matKhauHienTai) || empty($matKhauMoi) || empty($xacNhan)) {
                Session::set('error', 'Vui lòng điền đầy đủ thông tin!');
            } elseif (!password_verify($matKhauHienTai, $nguoiDung->mat_khau)) {
                Session::set('error', 'Mật khẩu hiện tại không chính xác!');
            } elseif ($matKhauMoi === $matKhauHienTai) {
                Session::set('error', 'Mật khẩu mới không được trùng với mật khẩu hiện tại!');
            } elseif ($matKhauMoi !== $xacNhan) {
                Session::set('error', 'Mật khẩu xác nhận không khớp!');
            } elseif (strlen($matKhauMoi) < 6) {
                Session::set('error', 'Mật khẩu mới phải có ít nhất 6 ký tự!');
            } else {
                $userModel->doiMatKhau($userId, password_hash($matKhauMoi, PASSWORD_DEFAULT));
                AuditMiddleware::enrich('Đổi mật khẩu tài khoản', [], [], 'nguoi_dung', $userId);
                Auth::logSecurityEvent('password_changed', 'NguoiDung #' . $userId . ' changed password');
                Session::set('success', 'Đổi mật khẩu thành công! Vui lòng đăng nhập lại nếu cần.');
            }
            $this->redirect('nguoi-dung/change_password');
            return;
        }

        $this->view('nguoi-dung/change_password', ['title' => 'Đổi Mật Khẩu - ' . SITE_NAME]);
    }

    // ==========================================
    // ĐĂNG XUẤT
    // ==========================================

    /**
     * Huy session va chuyen huong ve trang chu.
     * URL: GET /nguoi-dung/logout
     */
    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }
        // Dùng chung luồng logout mới để thu hồi remember token và ghi lịch sử.
        $this->loadAuthController()->logout();
    }

    // ==========================================
    // QUÊN MẬT KHẨU
    // ==========================================

    /**
     * Khôi phục mật khẩu – delegate sang AuthController mới.
     * URL: GET/POST /nguoi-dung/forgotPassword
     */
    public function forgotPassword(): void
    {
        $this->loadAuthController()->forgotPassword();
    }

    /**
     * @deprecated Giữ lại để backward compat nếu cần. Logic đã chuyển sang AuthService.
     */
    private function forgotPassword_legacy_doNotCall(): void
    {
        $resetState = Session::get('password_reset');
        if (!is_array($resetState)) {
            $resetState = [];
        }

        // Huy luong cu neu ma xac thuc hoac quyen dat lai da het han.
        if (!empty($resetState['expires_at']) && time() > (int)$resetState['expires_at']) {
            Session::delete('password_reset');
            $resetState = [];
        }

        $data = [
            'title' => 'Quên Mật Khẩu - ' . SITE_NAME,
            'step'  => $resetState['step'] ?? 'email',
            'email' => $resetState['email'] ?? '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::verify();
            $action = $_POST['action'] ?? 'request_code';
            $userModel = $this->model('NguoiDung');

            if ($action === 'restart') {
                Session::delete('password_reset');
                $data['step'] = 'email';
                $data['email'] = '';
            } elseif ($action === 'request_code') {
                $email = strtolower(trim($_POST['email'] ?? ''));
                $nguoiDung = filter_var($email, FILTER_VALIDATE_EMAIL)
                    ? $userModel->timTheoEmail($email)
                    : false;

                if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $data['error'] = 'Vui lòng nhập địa chỉ email hợp lệ!';
                    $data['step'] = 'email';
                } elseif (!$nguoiDung) {
                    $data['error'] = 'Không tìm thấy tài khoản với email này!';
                    $data['step'] = 'email';
                } elseif (!empty($resetState['last_sent_at'])
                    && ($resetState['email'] ?? '') === $email
                    && time() - (int)$resetState['last_sent_at'] < 60) {
                    $waitSeconds = 60 - (time() - (int)$resetState['last_sent_at']);
                    $data['error'] = "Vui lòng chờ {$waitSeconds} giây trước khi gửi lại mã.";
                    $data['step'] = 'code';
                    $data['email'] = $email;
                } else {
                    $verificationCode = (string)random_int(100000, 999999);
                    $subject = 'Mã xác thực khôi phục mật khẩu - ' . SITE_NAME;
                    $safeName = htmlspecialchars((string)$nguoiDung->ten, ENT_QUOTES, 'UTF-8');
                    $message = "Xin chào {$safeName},<br><br>"
                             . "Mã xác thực để đặt lại mật khẩu của bạn là:<br>"
                             . "<div style='font-size:30px;font-weight:bold;letter-spacing:8px;color:#0d6efd;margin:18px 0'>{$verificationCode}</div>"
                             . "Mã này có hiệu lực trong 10 phút. Không cung cấp mã cho bất kỳ ai.<br><br>"
                             . "Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này.<br><br>"
                             . "Ban quản trị " . SITE_NAME;

                    if (Email::send($email, $subject, $message, ['isHtml' => true])) {
                        $resetState = [
                            'step'         => 'code',
                            'user_id'      => (int)$nguoiDung->id,
                            'email'        => $email,
                            'code_hash'    => password_hash($verificationCode, PASSWORD_DEFAULT),
                            'expires_at'   => time() + 600,
                            'attempts'     => 0,
                            'last_sent_at' => time(),
                        ];
                        Session::set('password_reset', $resetState);
                        $data['step'] = 'code';
                        $data['email'] = $email;
                        $data['success'] = 'Mã xác thực đã được gửi đến email của bạn. Mã có hiệu lực trong 10 phút.';
                    } else {
                        $data['error'] = 'Không thể gửi mã xác thực. Vui lòng liên hệ quản trị viên hoặc thử lại sau.';
                        $data['step'] = 'email';
                    }
                }
            } elseif ($action === 'verify_code') {
                $resetState = Session::get('password_reset');
                $code = trim($_POST['verification_code'] ?? '');

                if (!is_array($resetState) || ($resetState['step'] ?? '') !== 'code') {
                    $data['error'] = 'Yêu cầu xác thực không hợp lệ. Vui lòng gửi mã mới.';
                    $data['step'] = 'email';
                } elseif (time() > (int)$resetState['expires_at']) {
                    Session::delete('password_reset');
                    $data['error'] = 'Mã xác thực đã hết hạn. Vui lòng gửi mã mới.';
                    $data['step'] = 'email';
                } elseif (!preg_match('/^\d{6}$/', $code)) {
                    $data['error'] = 'Mã xác thực phải gồm đúng 6 chữ số.';
                    $data['step'] = 'code';
                    $data['email'] = $resetState['email'];
                } elseif (!password_verify($code, $resetState['code_hash'])) {
                    $resetState['attempts'] = (int)$resetState['attempts'] + 1;
                    if ($resetState['attempts'] >= 5) {
                        Session::delete('password_reset');
                        $data['error'] = 'Bạn đã nhập sai quá 5 lần. Vui lòng gửi mã xác thực mới.';
                        $data['step'] = 'email';
                    } else {
                        Session::set('password_reset', $resetState);
                        $remainingAttempts = 5 - $resetState['attempts'];
                        $data['error'] = "Mã xác thực không đúng. Bạn còn {$remainingAttempts} lần thử.";
                        $data['step'] = 'code';
                        $data['email'] = $resetState['email'];
                    }
                } else {
                    $resetState['step'] = 'password';
                    $resetState['verified_at'] = time();
                    unset($resetState['code_hash'], $resetState['attempts']);
                    Session::set('password_reset', $resetState);
                    $data['step'] = 'password';
                    $data['email'] = $resetState['email'];
                    $data['success'] = 'Xác thực thành công. Vui lòng tạo mật khẩu mới.';
                }
            } elseif ($action === 'reset_password') {
                $resetState = Session::get('password_reset');
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if (!is_array($resetState)
                    || ($resetState['step'] ?? '') !== 'password'
                    || empty($resetState['verified_at'])
                    || time() - (int)$resetState['verified_at'] > 600) {
                    Session::delete('password_reset');
                    $data['error'] = 'Phiên đặt lại mật khẩu đã hết hạn. Vui lòng xác thực lại.';
                    $data['step'] = 'email';
                } elseif (strlen($newPassword) < 8) {
                    $data['error'] = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
                    $data['step'] = 'password';
                    $data['email'] = $resetState['email'];
                } elseif ($newPassword !== $confirmPassword) {
                    $data['error'] = 'Mật khẩu xác nhận không khớp.';
                    $data['step'] = 'password';
                    $data['email'] = $resetState['email'];
                } elseif ($userModel->doiMatKhau(
                    (int)$resetState['user_id'],
                    password_hash($newPassword, PASSWORD_DEFAULT)
                )) {
                    Session::delete('password_reset');
                    session_regenerate_id(true);
                    $data['step'] = 'complete';
                    $data['success'] = 'Cập nhật mật khẩu thành công! Bạn có thể đăng nhập bằng mật khẩu mới.';
                } else {
                    $data['error'] = 'Không thể cập nhật mật khẩu. Vui lòng thử lại.';
                    $data['step'] = 'password';
                    $data['email'] = $resetState['email'];
                }
            }
        }

        $this->view('nguoi-dung/forgot_password', $data);
    }

    // ==========================================
    // AJAX - ĐÁNH DẤU THÔNG BÁO ĐÃ ĐỌC
    // ==========================================

    /**
     * Endpoint AJAX: danh dau tat ca thong bao cua nguoi dung la da doc.
     * Chi xu ly khi method la POST va nguoi dung da dang nhap.
     * URL: POST /nguoi-dung/markNotificationsRead
     */
    public function markNotificationsRead(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Session::get('user_id')) {
            Csrf::verify();
            $userModel = $this->model('NguoiDung');
            $userModel->danhDauDaDoc((int)Session::get('user_id'));
            (new DashboardService())->invalidate((int)Session::get('user_id'));
            echo json_encode(['status' => 'success']);
        }
    }

    // ==========================================
    // HÀM HELPER DÙNG NỘI BỘ
    // ==========================================

    /**
     * Kiểm tra người dùng đã đăng nhập chưa.
     * Nếu chưa, chuyển hướng về trang đăng nhập.
     */
    private function kiemTraDangNhap(): void
    {
        if (!Auth::isLoggedIn()) {
            $this->redirect('nguoi-dung/dang-nhap');
        }
    }

    /**
     * Tạo mã bảo mật CAPTCHA ngẫu nhiên 5 ký tự.
     *
     * @return string Chuỗi ngẫu nhiên 5 ký tự chữ-số
     */
    private function taoMaBaoMat(): string
    {
        return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 5);
    }

    /**
     * Kiểm tra mã CAPTCHA từ $_POST với giá trị trong session.
     *
     * @param  string $sessionKey Khoa session chua CAPTCHA
     * @return bool               True neu CAPTCHA dung
     */
    private function kiemTraCaptcha(string $sessionKey): bool
    {
        $input   = strtolower(trim($_POST['captcha'] ?? ''));
        $session = strtolower(Session::get($sessionKey) ?? '');
        return !empty($input) && $input === $session;
    }

    /**
     * Tinh muc giam gia theo tong luot xem cua nguoi dung.
     * >= 5000 luot xem: giam 15%, >= 1000 luot xem: giam 5%.
     *
     * @param  int $tongLuotXem Tong luot xem tich luy
     * @return int              Phan tram giam gia (0, 5, hoac 15)
     */
    private function tinhMucGiamGia(int $tongLuotXem): int
    {
        return (new PricingService())->viewDiscount($tongLuotXem);
    }

    /**
     * Tinh gia cuoi sau khi ap dung giam gia va cong goi UP tin.
     *
     * @param  int $vipLevel  Cap do VIP (0-5)
     * @param  int $soNgay    So ngay VIP
     * @param  int $goiUpGia  Gia goi UP tin (0 neu khong mua)
     * @param  int $giamGia   Phan tram giam gia
     * @return int            Gia cuoi phai thanh toan
     */
    private function tinhGiaCuoi(int $vipLevel, int $soNgay, int $goiUpGia, int $giamGia): int
    {
        return (new PricingService())->postingPrice($vipLevel, $soNgay, $goiUpGia, $giamGia);
    }

    /**
     * Kiem tra nguoi dung du dieu kien de dang tin hay khong.
     * Dieu kien: du so du vi va con luot up tin.
     *
     * @param  object $nguoiDung Doi tuong nguoi dung
     * @param  int    $giaCuoi   Gia cuoi can thanh toan
     * @param  int    $goiUpGia  Gia goi UP tin (0 neu khong mua them)
     * @return bool              True neu du dieu kien
     */
    private function kiemTraDuDieuKienDangTin(object $nguoiDung, int $giaCuoi, int $goiUpGia): bool
    {
        if ($giaCuoi > 0 && $nguoiDung->so_du < $giaCuoi) {
            Session::set('error', 'Số dư tài khoản không đủ. Vui lòng nạp thêm tiền!');
            return false;
        }
        if ($nguoiDung->luot_up_tin < 1 && $goiUpGia == 0) {
            Session::set('error', 'Bạn đã hết lượt đăng tin. Vui lòng mua gói UP tin!');
            return false;
        }
        return true;
    }

    /**
     * Upload nhieu anh tu mot input file (name="images[]").
     * Tra ve mang cac ten file da duoc luu.
     *
     * @param  string $fieldName  Ten truong file input
     * @param  string $uploadDir  Thu muc luu (duong dan tuyet doi)
     * @return array              Mang ten file da upload thanh cong
     */
    private function uploadNhieuAnh(string $fieldName, string $uploadDir): array
    {
        if (!isset($_FILES[$fieldName]) || empty($_FILES[$fieldName]['name'][0])) {
            return [];
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $anhDaUp = [];
        $soFile  = min(count($_FILES[$fieldName]['name']), 10); // Toi da 10 anh
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        $maxSize = 5 * 1024 * 1024;
        $finfo   = new finfo(FILEINFO_MIME_TYPE);

        for ($i = 0; $i < $soFile; $i++) {
            $tmpName = $_FILES[$fieldName]['tmp_name'][$i];
            $error   = $_FILES[$fieldName]['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            $size    = (int)($_FILES[$fieldName]['size'][$i] ?? 0);

            if ($error !== UPLOAD_ERR_OK || !$tmpName || !is_uploaded_file($tmpName)) {
                continue;
            }
            if ($size <= 0 || $size > $maxSize) {
                continue;
            }

            $mime = $finfo->file($tmpName) ?: '';
            if (!isset($allowed[$mime])) {
                continue;
            }

            $tenFile = 'bds_' . bin2hex(random_bytes(12)) . '_' . time() . '_' . $i . '.' . $allowed[$mime];

            if (move_uploaded_file($tmpName, $uploadDir . $tenFile)) {
                $anhDaUp[] = $tenFile;
            }
        }
        return $anhDaUp;
    }

    /**
     * Chuan bi mang du lieu tin dang tu du lieu POST.
     *
     * @param  int    $userId   ID nguoi dang tin
     * @param  string $anhDai   Ten file anh dai dien
     * @param  int    $vipLevel Cap do VIP
     * @param  int    $soNgay   So ngay VIP
     * @return array            Mang du lieu tin dang
     */
    private function chuanBiDuLieuTinDang(int $userId, string $anhDai, int $vipLevel, int $soNgay): array
    {
        $tieuDe = trim($_POST['title'] ?? 'Tin đăng mới');
        $slug   = $this->createSlug($tieuDe) . '-' . bin2hex(random_bytes(4));
        $propertyType = trim($_POST['type'] ?? '');
        if (!in_array($propertyType, ['Nhà đất bán', 'Nhà đất cho thuê'], true)) {
            $propertyType = 'Nhà đất bán';
        }

        $latitude = filter_var($_POST['latitude'] ?? null, FILTER_VALIDATE_FLOAT);
        $longitude = filter_var($_POST['longitude'] ?? null, FILTER_VALIDATE_FLOAT);
        $latitude = $latitude !== false && $latitude >= -90 && $latitude <= 90 ? $latitude : null;
        $longitude = $longitude !== false && $longitude >= -180 && $longitude <= 180 ? $longitude : null;

        $videoUrl = trim($_POST['link_video'] ?? '');
        if ($videoUrl !== '' && !filter_var($videoUrl, FILTER_VALIDATE_URL)) {
            $videoUrl = '';
        }

        $ngayHetHanVip = null;
        if ($vipLevel > 0 && $soNgay > 0) {
            $ngayHetHanVip = date('Y-m-d H:i:s', strtotime("+{$soNgay} days"));
        }

        return [
            'tieu_de'           => $tieuDe,
            'duong_dan'         => $slug,
            'loai_bat_dong_san' => $propertyType,
            'ma_danh_muc'       => (int)($_POST['category'] ?? 1),
            'ma_nguoi_dung'     => $userId,
            'goi_vip'           => $vipLevel,
            'ngay_het_han_vip'  => $ngayHetHanVip,
            'mo_ta'             => trim($_POST['mo_ta'] ?? ''),
            'gia'               => $this->chuanHoaGia($_POST['gia'] ?? '', $_POST['don_vi_gia'] ?? ''),
            'dien_tich'         => max(0, (float)str_replace(',', '.', trim($_POST['dien_tich'] ?? '0'))),
            'vi_tri'            => trim($_POST['province'] ?? '') . ' ' . trim($_POST['district'] ?? '') . ' ' . trim($_POST['ward'] ?? '') . ' ' . trim($_POST['address'] ?? ''),
            'tinh_thanh'        => trim($_POST['tinh_thanh'] ?? ''),
            'huong_nha'         => trim($_POST['huong_nha'] ?? ''),
            'so_phong_ngu'      => (int)($_POST['so_phong_ngu'] ?? 0),
            'so_phong_wc'       => (int)($_POST['so_phong_wc'] ?? 0),
            'link_video'        => $videoUrl,
            'vi_do'             => $latitude,
            'kinh_do'           => $longitude,
            'anh_thu_nho'       => $anhDai,
        ];
    }

    private function chuanHoaGia(mixed $rawPrice, mixed $unit): int
    {
        $normalized = preg_replace('/[^0-9,.]/', '', (string)$rawPrice) ?? '';
        $value = (float)str_replace(',', '.', $normalized);
        $multiplier = match ((string)$unit) {
            'Triệu', 'Triệu/m2' => 1000000,
            'Tỷ' => 1000000000,
            default => 1,
        };
        return max(0, (int)round($value * $multiplier));
    }

    /**
     * Xu ly thanh toan sau khi dang tin: tru so du vi va ghi nhat ky giao dich.
     * Neu co mua goi UP tin, tu dong cong luot up tin.
     */
    private function xuLyThanhToan(
        int       $userId,
        ViDienTu  $walletModel,
        NguoiDung $userModel,
        int       $vipLevel,
        int       $soNgay,
        int       $goiUpGia,
        int       $giaCuoi,
        int       $giamGia,
        int       $maDuAn
    ): bool {
        $moTa = "Mua VIP {$vipLevel} - {$soNgay} ngày (giảm {$giamGia}%)";

        if ($goiUpGia > 0) {
            $moTa .= ' + Gói Up Tin (' . number_format($goiUpGia) . 'đ)';
            // Cong luot UP tin tuong ung theo bang gia
            $soLuotUp = array_search($goiUpGia, (new PricingService())->upPackages(), true);
            if ($soLuotUp !== false) {
                if (!$userModel->congLuotUp($userId, (int)$soLuotUp)) {
                    return false;
                }
            }
        }

        if (!$walletModel->truSoDu($userId, $giaCuoi)) {
            return false;
        }
        if (!$walletModel->ghiChiTieu([
            'ma_nguoi_dung' => $userId,
            'ma_du_an'      => $maDuAn,
            'loai'          => 'mua_vip',
            'mo_ta'         => $moTa,
            'so_tien'       => $giaCuoi,
        ])) {
            return false;
        }
        return true;
    }

    // ==========================================
    // BĐS ĐÃ LƯU
    // ==========================================

    public function daLuu(): void
    {
        Auth::requireLogin();
        $yeuThichModel = $this->model('YeuThich');
        $tinDaLuu = $yeuThichModel->layDanhSachDaLuu(Session::get('user_id'));

        $data = [
            'title' => 'Tin BĐS Đã Lưu',
            'saved_projects' => $tinDaLuu
        ];
        $this->view('nguoi-dung/da-luu', $data);
    }

    public function toggleLuu(int $maDuAn): void
    {
        Auth::requireLogin();
        Csrf::verify();
        $yeuThichModel = $this->model('YeuThich');
        $userId = Session::get('user_id');

        if ($yeuThichModel->kiemTraDaLuu($userId, $maDuAn)) {
            $yeuThichModel->boLuuTin($userId, $maDuAn);
            Session::flash('msg', 'Đã bỏ lưu tin BĐS.');
        } else {
            if ($yeuThichModel->luuTin($userId, $maDuAn)) {
                (new AnalyticsService())->record($maDuAn, 'save');
                Session::flash('msg', 'Đã lưu tin BĐS thành công!');
            }
        }
        
        if (!empty($_POST['redirect_to']) && $_POST['redirect_to'] === 'daLuu') {
            $this->redirect('nguoi-dung/daLuu');
        } else {
            $this->redirect('du-an/detail/' . ($_POST['slug'] ?? ''));
        }
    }

    /**
     * Xử lý gia hạn VIP của tin đăng (Client).
     * URL: POST /nguoi-dung/renewVipPost
     */
    public function renewVipPost(): void
    {
        $this->kiemTraDangNhap();
        $userId       = (int)Session::get('user_id');
        $projectModel = $this->model('DuAn');
        $walletModel  = $this->model('ViDienTu');
        $userModel    = $this->model('NguoiDung');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('nguoi-dung/dashboard');
            return;
        }

        // BẢO MẬT: Kiểm tra CSRF Token
        Csrf::verify();

        $projectId = (int)($_POST['project_id'] ?? 0);
        $soNgay    = (int)($_POST['days'] ?? 0);

        // Kiểm tra số ngày hợp lệ
        if (!in_array($soNgay, [7, 15, 30], true)) {
            Session::set('error', 'Số ngày gia hạn không hợp lệ.');
            $this->redirect('nguoi-dung/dashboard');
            return;
        }

        // Lấy tin đăng và kiểm tra sở hữu
        $tinDang = $projectModel->findById($projectId);
        if (!$tinDang || (int)$tinDang->ma_nguoi_dung !== $userId) {
            Session::set('error', 'Tin đăng không tồn tại hoặc bạn không có quyền sở hữu!');
            $this->redirect('nguoi-dung/dashboard');
            return;
        }

        $vipLevel = (int)$tinDang->goi_vip;
        if ($vipLevel <= 0) {
            Session::set('error', 'Tin đăng này không phải là tin VIP.');
            $this->redirect('nguoi-dung/dashboard');
            return;
        }

        // Tính ưu đãi và giá tiền gia hạn
        $tongLuotXem = $projectModel->tongLuotXemTheoUser($userId);
        $giamGia     = $this->tinhMucGiamGia($tongLuotXem);
        
        $giaCuoi     = (new PricingService())->vipPrice($vipLevel, $soNgay, $giamGia);

        $nguoiDung   = $userModel->layTheoId($userId);

        if ($giaCuoi > 0 && $nguoiDung->so_du < $giaCuoi) {
            Session::set('error', 'Số dư tài khoản không đủ. Vui lòng nạp thêm tiền!');
            $this->redirect('nguoi-dung/dashboard');
            return;
        }

        // BẢO MẬT: Sử dụng Database Transactions để đảm bảo tính nguyên tử
        try {
            $walletModel->beginTransaction();

            // 1. Trừ tiền nguyên tử
            if ($giaCuoi > 0 && !$walletModel->truSoDu($userId, $giaCuoi)) {
                $walletModel->rollBack();
                Session::set('error', 'Số dư không đủ. Vui lòng nạp thêm tiền!');
                $this->redirect('nguoi-dung/dashboard');
                return;
            }

            // 2. Tính ngày hết hạn mới
            // Nếu ngày hết hạn cũ vẫn chưa tới, gia hạn cộng dồn từ ngày hết hạn cũ.
            // Ngược lại, gia hạn cộng dồn từ thời điểm hiện tại.
            $currentExpiry = $tinDang->ngay_het_han_vip;
            if ($currentExpiry && strtotime($currentExpiry) > time()) {
                $newExpiry = date('Y-m-d H:i:s', strtotime($currentExpiry . " +{$soNgay} days"));
            } else {
                $newExpiry = date('Y-m-d H:i:s', strtotime("+{$soNgay} days"));
            }

            // Cập nhật ngày hết hạn VIP
            $projectModel->giaHanVip($projectId, $newExpiry);

            // 3. Ghi nhật ký chi tiêu
            $walletModel->ghiChiTieu([
                'ma_nguoi_dung' => $userId,
                'ma_du_an'      => $projectId,
                'loai'          => 'gia_han',
                'mo_ta'         => "Gia hạn VIP {$vipLevel} - {$soNgay} ngày (giảm {$giamGia}%)",
                'so_tien'       => $giaCuoi,
            ]);

            // 4. Tạo thông báo cho người dùng
            $userModel->taoThongBao(
                $userId,
                'Gia hạn VIP thành công',
                "Tin đăng '" . htmlspecialchars($tinDang->tieu_de) . "' đã được gia hạn VIP {$vipLevel} thêm {$soNgay} ngày. Hạn mới đến: " . date('d/m/Y H:i', strtotime($newExpiry))
            );

            $walletModel->commit();
            Session::set('success', 'Gia hạn VIP thành công!');
        } catch (Exception $e) {
            $walletModel->rollBack();
            error_log('Renew VIP Error: ' . $e->getMessage());
            Session::set('error', 'Có lỗi xảy ra trong quá trình xử lý giao dịch. Vui lòng thử lại!');
        }

        $this->redirect('nguoi-dung/dashboard');
    }

    /**
     * Bật/tắt tự động gia hạn VIP bằng AJAX.
     * URL: POST /nguoi-dung/toggleAutoRenewVip
     */
    public function toggleAutoRenewVip(): void
    {
        $this->kiemTraDangNhap();
        $userId       = (int)Session::get('user_id');
        $projectModel = $this->model('DuAn');

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            return;
        }

        // Kiểm tra CSRF Token
        try {
            Csrf::verify();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi bảo mật (CSRF). Vui lòng tải lại trang.']);
            return;
        }

        $projectId = (int)($_POST['project_id'] ?? 0);
        $status    = (int)($_POST['status'] ?? 0); // 1 = Bật, 0 = Tắt

        // Kiểm tra quyền sở hữu
        $tinDang = $projectModel->findById($projectId);
        if (!$tinDang || (int)$tinDang->ma_nguoi_dung !== $userId) {
            echo json_encode(['success' => false, 'message' => 'Tin đăng không tồn tại hoặc bạn không có quyền sở hữu!']);
            return;
        }

        if ((int)$tinDang->goi_vip <= 0) {
            echo json_encode(['success' => false, 'message' => 'Tin đăng này không phải là tin VIP.']);
            return;
        }

        // Cập nhật trạng thái
        if ($projectModel->setAutoRenewVip($projectId, $status)) {
            $msg = $status === 1 ? 'Đã bật tự động gia hạn VIP thành công!' : 'Đã tắt tự động gia hạn VIP thành công!';
            echo json_encode(['success' => true, 'message' => $msg]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra trong quá trình cập nhật. Vui lòng thử lại!']);
        }
    }

    // ==========================================
    // AUTH DELEGATION (Module Tài khoản mới)
    // ==========================================

    /**
     * Đăng nhập người dùng (trang mới thiết kế premium).
     * URL: GET/POST /nguoi-dung/dang-nhap
     * Delegates toàn bộ sang AuthController.
     */
    public function dangNhap(): void
    {
        $this->loadAuthController()->login();
    }

    /**
     * URL: GET /nguoi-dung/verify-email/{token}
     */
    public function verifyEmail(string $token = ''): void
    {
        $token = $token !== '' ? $token : trim((string) ($_GET['token'] ?? ''));
        $this->loadAuthController()->verifyEmail($token);
    }

    /**
     * Trang nhắc xác thực email dành cho tài khoản chưa được kích hoạt.
     * URL: GET /nguoi-dung/verify-email-notice
     */
    public function verifyEmailNotice(): void
    {
        $this->loadAuthController()->emailVerificationNotice();
    }

    public function resetPassword(string $token = ''): void
    {
        $this->loadAuthController()->resetPassword($token);
    }

    /**
     * URL: POST /nguoi-dung/reset-password-ajax
     */
    public function resetPasswordAjax(): void
    {
        $this->loadAuthController()->resetPasswordAjax();
    }

    /**
     * URL: GET /nguoi-dung/social-login
     */
    public function socialLogin(): void
    {
        $this->loadAuthController()->socialLogin();
    }

    /**
     * URL: GET /nguoi-dung/social-redirect
     */
    public function socialRedirect(): void
    {
        $this->loadAuthController()->socialRedirect();
    }

    /**
     * URL: GET /nguoi-dung/social-error
     */
    public function socialError(): void
    {
        $this->loadAuthController()->socialError();
    }

    /**
     * URL: GET /nguoi-dung/google-callback
     */
    public function googleCallback(): void
    {
        $this->loadAuthController()->googleCallback();
    }

    /**
     * URL: GET /nguoi-dung/facebook-callback
     */
    public function facebookCallback(): void
    {
        $this->loadAuthController()->facebookCallback();
    }

    /**
     * URL: POST /nguoi-dung/verify-otp
     */
    public function verifyOtp(): void
    {
        $this->loadAuthController()->verifyOtp();
    }

    /**
     * URL: POST /nguoi-dung/resend-otp
     */
    public function resendOtp(): void
    {
        $this->loadAuthController()->resendOtp();
    }

    /**
     * URL: POST /nguoi-dung/resend-verify
     */
    public function resendVerify(): void
    {
        $this->loadAuthController()->resendVerify();
    }

    public function refreshCaptcha(): void
    {
        $this->loadAuthController()->refreshCaptcha();
    }

    // (forgotPassword() ở trên - đã delegate sang AuthController)

    // (logout() ở line 398 xử lý - không cần delegation sang AuthController)

    /** Lazy-load AuthController (chỉ load khi cần). */
    private function loadAuthController(): AuthController
    {
        if (!class_exists('AuthController')) {
            require_once APP_ROOT . '/app/controllers/AuthController.php';
        }
        return new AuthController();
    }
}
