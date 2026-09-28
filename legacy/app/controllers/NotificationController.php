<?php
/**
 * NotificationController – Controller quản lý giao diện trung tâm thông báo của Thành viên.
 * Quyền truy cập: Thành viên đã đăng nhập.
 */
class NotificationController extends Controller
{
    private NotificationService $service;

    public function __construct()
    {
        $this->service = new NotificationService();
    }

    /**
     * Hiển thị danh sách thông báo của người dùng.
     * URL: GET /nguoi-dung/notifications
     */
    public function index(): void
    {
        $this->requireLogin();
        $userId = (int)Session::get('user_id');

        $filter = $_GET['filter'] ?? 'all';
        $search = trim($_GET['search'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        
        $limit  = 10;
        $offset = ($page - 1) * $limit;

        $repo = $this->service->getRepo();
        $total = $repo->countByUser($userId, $filter, $search);
        $notifications = $repo->paginateByUser($userId, $filter, $limit, $offset, $search);

        $totalPages = max(1, (int)ceil($total / $limit));

        $this->view('notification/index', [
            'title'         => 'Trung tâm thông báo - ' . SITE_NAME,
            'notifications' => $notifications,
            'filter'        => $filter,
            'search'        => $search,
            'page'          => $page,
            'total_pages'   => $totalPages,
            'total'         => $total,
            'unread_count'  => $this->service->getUnreadCount($userId)
        ]);
    }

    /**
     * Xem chi tiết một thông báo và tự động đánh dấu đã đọc.
     * URL: GET /nguoi-dung/notifications/{id}
     */
    public function detail(?string $id = null): void
    {
        $this->requireLogin();
        if (!$id) {
            $this->redirect('nguoi-dung/notifications');
        }

        $userId = (int)Session::get('user_id');
        $notifId = (int)$id;

        $notif = $this->service->getRepo()->findById($notifId, $userId);
        if (!$notif) {
            $this->redirect('nguoi-dung/notifications');
        }

        // Tự động đánh dấu đã đọc
        if ((int)$notif->is_read === 0) {
            $this->service->markRead($notifId, $userId);
        }

        $this->view('notification/detail', [
            'title'        => 'Chi tiết thông báo - ' . SITE_NAME,
            'notification' => $notif
        ]);
    }

    /**
     * API đánh dấu một thông báo đã đọc bằng POST.
     * URL: POST /nguoi-dung/notifications/read/{id}
     */
    public function markRead(?string $id = null): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed.'], 405);
            return;
        }

        $userId = (int)Session::get('user_id');
        $notifId = (int)$id;

        if ($this->service->markRead($notifId, $userId)) {
            $this->json(['success' => true, 'unread_count' => $this->service->getUnreadCount($userId)]);
        } else {
            $this->json(['success' => false, 'message' => 'Không thể cập nhật trạng thái thông báo.'], 400);
        }
    }

    /**
     * API đánh dấu tất cả đã đọc.
     * URL: POST /nguoi-dung/notifications/read-all
     */
    public function markAllRead(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed.'], 405);
            return;
        }

        $userId = (int)Session::get('user_id');
        if ($this->service->markAllRead($userId)) {
            if ($this->isAjax()) {
                $this->json(['success' => true]);
            } else {
                $this->redirect('nguoi-dung/notifications');
            }
        } else {
            $this->json(['success' => false, 'message' => 'Lỗi cập nhật CSDL.'], 500);
        }
    }

    /**
     * API xóa một thông báo.
     * URL: DELETE /nguoi-dung/notifications/{id}
     */
    public function delete(?string $id = null): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->json(['success' => false, 'message' => 'Method not allowed.'], 405);
            return;
        }

        $userId = (int)Session::get('user_id');
        $notifId = (int)$id;

        if ($this->service->delete($notifId, $userId)) {
            $this->json(['success' => true, 'unread_count' => $this->service->getUnreadCount($userId)]);
        } else {
            $this->json(['success' => false, 'message' => 'Không thể xóa thông báo.'], 400);
        }
    }

    /**
     * API xóa tất cả thông báo của người dùng hiện tại.
     * URL: DELETE /nguoi-dung/notifications/clear
     */
    public function clear(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->json(['success' => false, 'message' => 'Method not allowed.'], 405);
            return;
        }

        $userId = (int)Session::get('user_id');
        if ($this->service->clear($userId)) {
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'message' => 'Không thể dọn dẹp danh sách thông báo.'], 500);
        }
    }

    private function requireLogin(): void
    {
        if (!Session::get('user_id')) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Unauthorized.'], 401);
                exit;
            }
            $this->redirect('nguoi-dung/dang-nhap');
        }
    }

    private function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
