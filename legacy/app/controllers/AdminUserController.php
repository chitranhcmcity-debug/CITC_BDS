<?php
/**
 * AdminUserController – Quản lý người dùng, ví số dư, phân quyền, lịch sử đăng nhập/giao dịch dành cho Admin.
 * Quyền truy cập: Quản trị viên (Role ID = 1).
 */
class AdminUserController extends Controller
{
    private UserService $userService;
    private SecurityService $securityService;
    private WalletService $walletService;
    private Role $roleModel;
    private LoginHistoryRepository $loginHistoryRepo;
    private TransactionRepository $transactionRepo;
    private NotificationService $notifService;

    public function __construct()
    {
        require_once APP_ROOT . '/app/services/DichVuNguoiDung.php';
        require_once APP_ROOT . '/app/services/DichVuBaoMat.php';
        require_once APP_ROOT . '/app/services/DichVuViDienTu.php';
        require_once APP_ROOT . '/app/models/Role.php';
        require_once APP_ROOT . '/app/repositories/LoginHistoryRepository.php';
        require_once APP_ROOT . '/app/repositories/TransactionRepository.php';
        require_once APP_ROOT . '/app/services/DichVuThongBao.php';

        $this->userService = new UserService();
        $this->securityService = new SecurityService();
        $this->walletService = new WalletService();
        $this->roleModel = new Role();
        $this->loginHistoryRepo = new LoginHistoryRepository();
        $this->transactionRepo = new TransactionRepository();
        $this->notifService = new NotificationService();
    }

    /**
     * Danh sách người dùng.
     * URL: GET /admin/nguoi-dung
     */
    public function index(): void
    {
        $this->requireAdmin();

        $page = min(500, max(1, (int)($_GET['page'] ?? 1)));
        $filters = [
            'role_id'        => $_GET['role_id'] ?? '',
            'status'         => $_GET['status'] ?? '',
            'email_verified' => $_GET['email_verified'] ?? '',
            'phone_verified' => $_GET['phone_verified'] ?? '',
            'min_balance'    => $_GET['min_balance'] ?? '',
            'start_date'     => $_GET['start_date'] ?? '',
            'search'         => $_GET['search'] ?? ''
        ];

        $list = $this->userService->adminList($filters, $page, 20);
        $total = $this->userService->adminCount($filters);
        $totalPages = max(1, (int)ceil($total / 20));

        $stats = $this->userService->adminStats();
        $roles = $this->roleModel->all();

        $this->view('admin/user/index', [
            'title'      => 'Quản lý người dùng - ' . SITE_NAME,
            'list'       => $list,
            'total'      => $total,
            'page'       => $page,
            'totalPages' => $totalPages,
            'stats'      => $stats,
            'roles'      => $roles,
            'filters'    => $filters
        ]);
    }

    /**
     * Chi tiết người dùng.
     * URL: GET /admin/nguoi-dung/detail/{id}
     */
    public function detail(?string $id = null): void
    {
        $this->requireAdmin();
        $userId = (int)$id;

        $user = $this->userService->adminFind($userId);
        if (!$userId || !$user) {
            $_SESSION['flash_error'] = 'Người dùng không tồn tại.';
            $this->redirect('admin/nguoi-dung');
            return;
        }

        // Lấy lịch sử đăng nhập & lịch sử giao dịch
        $loginHistory = $this->loginHistoryRepo->getHistory($userId, 15, 0);
        $transactions = $this->transactionRepo->getHistory($userId, 15, 0);

        $this->view('admin/user/detail', [
            'title'        => 'Chi tiết người dùng #' . $userId . ' - ' . SITE_NAME,
            'user'         => $user,
            'loginHistory' => $loginHistory,
            'transactions' => $transactions
        ]);
    }

    /**
     * Tạo người dùng mới (GET form / POST create).
     * URL: GET/POST /admin/nguoi-dung/create
     */
    public function create(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify()) {
                $_SESSION['flash_error'] = 'Yêu cầu bảo mật không hợp lệ.';
                $this->redirect('admin/nguoi-dung/create');
                return;
            }

            $res = $this->userService->create($_POST);
            if ($res['success']) {
                SystemLogger::admin(
                    'create', 
                    'nguoi_dung', 
                    'nguoi_dung', 
                    $res['id'], 
                    "Admin tạo thành viên mới: #{$res['id']} - " . htmlspecialchars($_POST['email']), 
                    [], 
                    $_POST, 
                    (int)Session::get('user_id')
                );
                $_SESSION['flash_success'] = 'Đã tạo tài khoản thành viên thành công.';
                $this->redirect('admin/nguoi-dung/detail/' . $res['id']);
            } else {
                $_SESSION['flash_errors'] = $res['errors'] ?? [];
                $_SESSION['flash_old'] = $_POST;
                $this->redirect('admin/nguoi-dung/create');
            }
            return;
        }

        $roles = $this->roleModel->all();
        $this->view('admin/user/create', [
            'title' => 'Tạo người dùng mới - ' . SITE_NAME,
            'roles' => $roles
        ]);
    }

    /**
     * Chỉnh sửa thông tin hồ sơ (GET form / POST update).
     * URL: GET/POST /admin/nguoi-dung/update/{id}
     */
    public function update(?string $id = null): void
    {
        $this->requireAdmin();
        $userId = (int)$id;

        $user = $this->userService->adminFind($userId);
        if (!$userId || !$user) {
            $_SESSION['flash_error'] = 'Người dùng không tồn tại.';
            $this->redirect('admin/nguoi-dung');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify()) {
                $_SESSION['flash_error'] = 'Yêu cầu bảo mật không hợp lệ.';
                $this->redirect('admin/nguoi-dung/update/' . $userId);
                return;
            }

            $res = $this->userService->update($userId, $_POST);
            if ($res['success']) {
                SystemLogger::admin(
                    'edit', 
                    'nguoi_dung', 
                    'nguoi_dung', 
                    $userId, 
                    "Cập nhật hồ sơ người dùng #{$userId} - " . htmlspecialchars($_POST['name']), 
                    (array)$user, 
                    $_POST, 
                    (int)Session::get('user_id')
                );
                $_SESSION['flash_success'] = 'Cập nhật thông tin thành công.';
                $this->redirect('admin/nguoi-dung/detail/' . $userId);
            } else {
                $_SESSION['flash_errors'] = $res['errors'] ?? [];
                $this->redirect('admin/nguoi-dung/update/' . $userId);
            }
            return;
        }

        $roles = $this->roleModel->all();
        $this->view('admin/user/edit', [
            'title' => 'Chỉnh sửa người dùng #' . $userId . ' - ' . SITE_NAME,
            'user'  => $user,
            'roles' => $roles
        ]);
    }

    /**
     * Xóa tài khoản (mềm).
     * URL: POST /admin/nguoi-dung/delete/{id}
     */
    public function delete(?string $id = null): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
            $this->redirect('admin/nguoi-dung');
            return;
        }

        $userId = (int)$id;
        $adminId = (int)Session::get('user_id');

        if ($this->userService->delete($userId, $adminId)) {
            $_SESSION['flash_success'] = 'Đã xóa vĩnh viễn tài khoản người dùng.';
        } else {
            $_SESSION['flash_error'] = 'Lỗi xử lý xóa người dùng.';
        }

        $this->redirect('admin/nguoi-dung');
    }

    /**
     * Khóa tài khoản.
     * URL: POST /admin/nguoi-dung/lock/{id}
     */
    public function lock(?string $id = null): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
            $this->redirect('admin/nguoi-dung');
            return;
        }

        $userId = (int)$id;
        $adminId = (int)Session::get('user_id');
        $duration = (int)($_POST['duration'] ?? 0); // 0 = vĩnh viễn, else số ngày
        $reason = trim($_POST['reason'] ?? 'Vi phạm điều khoản.');

        if ($this->userService->lock($userId, $duration, $reason, $adminId)) {
            $_SESSION['flash_success'] = 'Đã khóa tài khoản người dùng thành công.';
        } else {
            $_SESSION['flash_error'] = 'Không thể khóa tài khoản.';
        }

        $this->redirect('admin/nguoi-dung/detail/' . $userId);
    }

    /**
     * Mở khóa tài khoản.
     * URL: POST /admin/nguoi-dung/unlock/{id}
     */
    public function unlock(?string $id = null): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
            $this->redirect('admin/nguoi-dung');
            return;
        }

        $userId = (int)$id;
        $adminId = (int)Session::get('user_id');

        if ($this->userService->unlock($userId, $adminId)) {
            $_SESSION['flash_success'] = 'Đã mở khóa tài khoản người dùng.';
        } else {
            $_SESSION['flash_error'] = 'Không thể mở khóa tài khoản.';
        }

        $this->redirect('admin/nguoi-dung/detail/' . $userId);
    }

    /**
     * Reset mật khẩu.
     * URL: POST /admin/nguoi-dung/resetPassword/{id}
     */
    public function resetPassword(?string $id = null): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
            $this->redirect('admin/nguoi-dung');
            return;
        }

        $userId = (int)$id;
        $adminId = (int)Session::get('user_id');
        $newPassword = trim($_POST['password'] ?? '');

        if (empty($newPassword)) {
            $_SESSION['flash_error'] = 'Mật khẩu mới không được để trống.';
            $this->redirect('admin/nguoi-dung/detail/' . $userId);
            return;
        }

        if ($this->securityService->adminResetPassword($userId, $newPassword, $adminId)) {
            $_SESSION['flash_success'] = 'Đã đặt lại mật khẩu mới cho thành viên.';
        } else {
            $_SESSION['flash_error'] = 'Lỗi đặt lại mật khẩu.';
        }

        $this->redirect('admin/nguoi-dung/detail/' . $userId);
    }

    /**
     * Cộng tiền vào ví.
     * URL: POST /admin/nguoi-dung/addMoney/{id}
     */
    public function addMoney(?string $id = null): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
            $this->redirect('admin/nguoi-dung');
            return;
        }

        $userId = (int)$id;
        $adminId = (int)Session::get('user_id');
        $amount = (int)($_POST['amount'] ?? 0);
        $reason = trim($_POST['reason'] ?? 'Cộng tiền ví hệ thống.');

        if ($amount <= 0) {
            $_SESSION['flash_error'] = 'Số tiền nạp phải lớn hơn 0.';
            $this->redirect('admin/nguoi-dung/detail/' . $userId);
            return;
        }

        if ($this->walletService->addMoney($userId, $amount, $reason, $adminId)) {
            $_SESSION['flash_success'] = 'Đã nạp tiền vào ví thành công.';
        } else {
            $_SESSION['flash_error'] = 'Lỗi cộng tiền vào ví.';
        }

        $this->redirect('admin/nguoi-dung/detail/' . $userId);
    }

    /**
     * Trừ tiền ví.
     * URL: POST /admin/nguoi-dung/subMoney/{id}
     */
    public function subMoney(?string $id = null): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
            $this->redirect('admin/nguoi-dung');
            return;
        }

        $userId = (int)$id;
        $adminId = (int)Session::get('user_id');
        $amount = (int)($_POST['amount'] ?? 0);
        $reason = trim($_POST['reason'] ?? 'Trừ tiền ví hệ thống.');

        if ($amount <= 0) {
            $_SESSION['flash_error'] = 'Số tiền trừ phải lớn hơn 0.';
            $this->redirect('admin/nguoi-dung/detail/' . $userId);
            return;
        }

        if ($this->walletService->subMoney($userId, $amount, $reason, $adminId)) {
            $_SESSION['flash_success'] = 'Đã trừ số dư ví thành viên thành công.';
        } else {
            $_SESSION['flash_error'] = 'Lỗi trừ số dư ví (kiểm tra lại số dư tài khoản).';
        }

        $this->redirect('admin/nguoi-dung/detail/' . $userId);
    }

    /**
     * Gửi thông báo tùy chỉnh cho người dùng.
     * URL: POST /admin/nguoi-dung/sendNotification/{id}
     */
    public function sendNotification(?string $id = null): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
            $this->redirect('admin/nguoi-dung');
            return;
        }

        $userId = (int)$id;
        $title = trim($_POST['title'] ?? 'Thông báo từ Ban quản trị');
        $content = trim($_POST['content'] ?? '');

        if (empty($content)) {
            $_SESSION['flash_error'] = 'Nội dung thông báo không được để trống.';
            $this->redirect('admin/nguoi-dung/detail/' . $userId);
            return;
        }

        if ($this->notifService->send($userId, $title, $content, 'he_thong')) {
            $_SESSION['flash_success'] = 'Đã gửi thông báo đến thành viên.';
        } else {
            $_SESSION['flash_error'] = 'Không thể gửi thông báo.';
        }

        $this->redirect('admin/nguoi-dung/detail/' . $userId);
    }

    private function requireAdmin(): void
    {
        if ((int)Session::get('user_id') <= 0 || (int)Session::get('user_role_id') !== 1) {
            $this->redirect('nguoi-dung/dang-nhap');
            exit;
        }
    }
}
