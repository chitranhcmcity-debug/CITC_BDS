<?php
/**
 * AdminLiveChatController – Phân hệ quản lý hội thoại CSKH phía Admin / Nhân viên.
 * Hỗ trợ tiếp nhận cuộc trò chuyện, cắt chuyển nhân viên, đóng cuộc chat và xuất lịch sử hội thoại.
 * Quyền truy cập: Quản trị viên & Nhân viên (Role ID = 1 hoặc nhân viên CSKH).
 */
class AdminLiveChatController extends Controller
{
    private ConversationService $convSvc;
    private ChatService $chatSvc;

    public function __construct()
    {
        require_once APP_ROOT . '/app/services/DichVuHoiThoai.php';
        require_once APP_ROOT . '/app/services/DichVuTroChuyen.php';

        $this->convSvc = new ConversationService();
        $this->chatSvc = new ChatService();
    }

    /**
     * Bảng điều khiển quản lý hội thoại Live Chat phía Admin.
     * URL: GET /admin/livechat
     */
    public function index(): void
    {
        $this->requireStaff();

        $status = $_GET['status'] ?? 'all';
        $type = $_GET['type'] ?? '';

        $conversations = $this->convSvc->getRepo()->listAll($status, $type);

        // Lấy danh sách nhân viên CSKH để chuyển nhượng cuộc hội thoại
        $userRepo = new UserRepository();
        $staffMembers = $userRepo->findByRole(1); // Trong hệ thống Role 1 là Admin/CSKH

        $this->view('chat/admin', [
            'title' => 'Quản lý Live Chat hỗ trợ - ' . SITE_NAME,
            'conversations' => $conversations,
            'staffMembers'  => $staffMembers,
            'status'        => $status,
            'type'          => $type
        ]);
    }

    /**
     * Nhân viên nhận tiếp quản cuộc hội thoại.
     * URL: POST /admin/livechat/claim/{id}
     */
    public function claim(?string $id = null): void
    {
        $this->requireStaff();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/live-chat');
        }
        Csrf::verify();

        $convId = (int)$id;
        $staffId = (int)Session::get('user_id');

        if ($this->convSvc->getRepo()->transfer($convId, $staffId)) {
            $_SESSION['flash_success'] = 'Đã tiếp nhận hỗ trợ cuộc trò chuyện thành công.';
        } else {
            $_SESSION['flash_error'] = 'Lỗi nhận cuộc trò chuyện.';
        }

        $this->redirect('admin/live-chat');
    }

    /**
     * Chuyển nhượng cuộc trò chuyện cho nhân viên khác.
     * URL: POST /admin/livechat/transfer
     */
    public function transfer(): void
    {
        $this->requireStaff();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/live-chat');
        }
        Csrf::verify();

        $convId = (int)($_POST['conversation_id'] ?? 0);
        $staffId = (int)($_POST['staff_id'] ?? 0);

        if ($convId > 0 && $staffId > 0) {
            if ($this->convSvc->transfer($convId, $staffId)) {
                $_SESSION['flash_success'] = 'Đã chuyển nhượng cuộc hội thoại thành công.';
            } else {
                $_SESSION['flash_error'] = 'Không thể chuyển cuộc hội thoại.';
            }
        } else {
            $_SESSION['flash_error'] = 'Thông tin chuyển nhượng không hợp lệ.';
        }

        $this->redirect('admin/live-chat');
    }

    /**
     * Ghim hoặc bỏ ghim cuộc trò chuyện.
     * URL: POST /admin/livechat/pin/{id}
     */
    public function pin(?string $id = null): void
    {
        $this->requireStaff();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/live-chat');
        }
        Csrf::verify();

        $convId = (int)$id;
        $conv = $this->convSvc->getRepo()->findById($convId);
        
        if ($conv) {
            $newPin = !$conv->is_pinned;
            $this->convSvc->pin($convId, $newPin);
            $_SESSION['flash_success'] = $newPin ? 'Đã ghim cuộc hội thoại.' : 'Đã bỏ ghim cuộc hội thoại.';
        } else {
            $_SESSION['flash_error'] = 'Không tìm thấy cuộc hội thoại.';
        }

        $this->redirect('admin/live-chat');
    }

    /**
     * Đóng cuộc hội thoại.
     * URL: POST /admin/livechat/close/{id}
     */
    public function close(?string $id = null): void
    {
        $this->requireStaff();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/live-chat');
        }
        Csrf::verify();

        $convId = (int)$id;
        if ($this->convSvc->close($convId)) {
            $_SESSION['flash_success'] = 'Đã kết thúc cuộc hội thoại hỗ trợ.';
        } else {
            $_SESSION['flash_error'] = 'Không thể đóng cuộc trò chuyện.';
        }

        $this->redirect('admin/live-chat');
    }

    /**
     * Xuất lịch sử cuộc trò chuyện ra tệp tải về (.txt).
     * URL: GET /admin/livechat/export/{id}
     */
    public function export(?string $id = null): void
    {
        $this->requireStaff();
        $convId = (int)$id;
        $conv = $this->convSvc->getRepo()->findById($convId);
        
        if (!$conv) {
            $_SESSION['flash_error'] = 'Không tìm thấy cuộc hội thoại để xuất lịch sử.';
            $this->redirect('admin/livechat');
            return;
        }

        $messages = $this->chatSvc->getMessageRepo()->getHistory($convId, 500, 0);

        // Chuẩn bị header tải về
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="chat_history_conv_' . $convId . '_' . date('Ymd_His') . '.txt"');

        echo "========================================================\n";
        echo "LỊCH SỬ HỘI THOẠI LIVE CHAT - TIMNHADAT.SITE\n";
        echo "Mã hội thoại: #" . $conv->id . "\n";
        echo "Loại hội thoại: " . strtoupper($conv->type) . "\n";
        echo "Thời gian tạo: " . date('d/m/Y H:i:s', strtotime($conv->created_at)) . "\n";
        echo "========================================================\n\n";

        foreach ($messages as $msg) {
            $time = date('d/m/Y H:i:s', strtotime($msg->created_at));
            $sender = $msg->sender_name ?: 'Hệ thống/Khách';
            echo "[{$time}] {$sender}: {$msg->message}\n";
            if ($msg->attachment) {
                echo "   -> Tệp đính kèm: " . URL_ROOT . '/public/uploads/chats/' . $msg->attachment . "\n";
            }
        }
        exit;
    }

    private function requireStaff(): void
    {
        // Admin (Role 1) có quyền truy cập quản trị chat
        if ((int)Session::get('user_id') <= 0 || (int)Session::get('user_role_id') !== 1) {
            $this->redirect('nguoi-dung/dang-nhap');
            exit;
        }
    }
}
