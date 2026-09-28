<?php
/**
 * AdminNotificationsController – Quản lý việc gửi thông báo toàn hệ thống từ quản trị viên.
 * Quyền truy cập: Quản trị viên (Role ID = 1).
 */
class AdminNotificationsController extends Controller
{
    private NotificationService $service;

    public function __construct()
    {
        $this->service = new NotificationService();
    }

    /**
     * Hiển thị bảng điều khiển thông báo phía Admin.
     * URL: GET /admin/notifications
     */
    public function index(): void
    {
        $this->requireAdmin();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $repo = $this->service->getRepo();
        $stats = $repo->getStatistics();
        $recentSent = $repo->getAdminRecentSent($limit, $offset);

        // Đếm tổng số để phân trang
        $db = new Database();
        $db->query("SELECT COUNT(*) as total FROM notifications");
        $totalRow = $db->single();
        $total = (int)($totalRow->total ?? 0);
        $totalPages = max(1, (int)ceil($total / $limit));

        $this->view('admin/notifications', [
            'title'       => 'Quản lý thông báo hệ thống - ' . SITE_NAME,
            'stats'       => $stats,
            'recent'      => $recentSent,
            'page'        => $page,
            'total_pages' => $totalPages
        ]);
    }

    /**
     * Tạo và phát đi thông báo hệ thống.
     * URL: POST /admin/notifications/send
     */
    public function send(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/notifications');
        }

        if (!Csrf::verify()) {
            $_SESSION['flash_error'] = 'Yêu cầu bảo mật không hợp lệ.';
            $this->redirect('admin/notifications');
        }

        $targetType = $_POST['target_type'] ?? 'all';
        $roleId     = isset($_POST['role_id']) && $_POST['role_id'] !== '' ? (int)$_POST['role_id'] : null;
        $specificUid = isset($_POST['user_id']) && $_POST['user_id'] !== '' ? (int)$_POST['user_id'] : null;

        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $url     = trim($_POST['url'] ?? '');
        $icon    = trim($_POST['icon'] ?? 'fa-bullhorn');

        if (empty($title) || empty($content)) {
            $_SESSION['flash_error'] = 'Tiêu đề và nội dung không được để trống.';
            $this->redirect('admin/notifications');
        }

        $sentCount = 0;
        if ($targetType === 'user' && $specificUid > 0) {
            // Gửi cho một thành viên cụ thể
            if ($this->service->send($specificUid, $title, $content, 'he_thong', $url, $icon)) {
                $sentCount = 1;
            }
        } elseif ($targetType === 'role' && $roleId > 0) {
            // Gửi cho vai trò cụ thể
            $sentCount = $this->service->sendBroadcast($roleId, $title, $content, $url, $icon);
        } else {
            // Gửi cho toàn bộ hệ thống
            $sentCount = $this->service->sendBroadcast(null, $title, $content, $url, $icon);
        }

        if ($sentCount > 0) {
            $_SESSION['flash_success'] = "Đã phát đi thành công thông báo tới {$sentCount} người dùng.";
        } else {
            $_SESSION['flash_error'] = "Không có người nhận nào được tìm thấy hoặc lỗi gửi thông báo.";
        }

        $this->redirect('admin/notifications');
    }

    /**
     * Thu hồi / Xóa thông báo đã gửi.
     * URL: POST /admin/notifications/retract/{id}
     */
    public function retract(?string $id = null): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/notifications');
        }

        if (!Csrf::verify()) {
            $_SESSION['flash_error'] = 'Yêu cầu bảo mật không hợp lệ.';
            $this->redirect('admin/notifications');
        }

        $notifId = (int)$id;
        if ($this->service->getRepo()->deleteExpiredNotification($notifId)) {
            $_SESSION['flash_success'] = 'Đã thu hồi thông báo thành công.';
        } else {
            $_SESSION['flash_error'] = 'Không thể thu hồi thông báo.';
        }

        $this->redirect('admin/notifications');
    }

    private function requireAdmin(): void
    {
        if ((int)Session::get('user_id') <= 0 || (int)Session::get('user_role_id') !== 1) {
            $this->redirect('nguoi-dung/dang-nhap');
        }
    }
}
