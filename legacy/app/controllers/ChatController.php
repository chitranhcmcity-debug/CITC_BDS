<?php
/**
 * ChatController – Quản lý toàn bộ cổng giao tiếp phía Khách hàng / Thành viên.
 * Hỗ trợ bootstrap, gửi tin nhắn, tải file, đánh dấu đã đọc, polling & SSE realtime.
 */
class ChatController extends Controller
{
    private ConversationService $convSvc;
    private ChatService $chatSvc;
    private UploadService $uploadSvc;

    public function __construct()
    {
        require_once APP_ROOT . '/app/services/DichVuHoiThoai.php';
        require_once APP_ROOT . '/app/services/DichVuTroChuyen.php';
        require_once APP_ROOT . '/app/services/DichVuTaiLen.php';

        $this->convSvc = new ConversationService();
        $this->chatSvc = new ChatService();
        $this->uploadSvc = new UploadService();
    }

    /**
     * Giao diện Chat đầy đủ của Thành viên.
     * URL: GET /chat
     */
    public function index(): void
    {
        $userId = Session::get('user_id') ? (int)Session::get('user_id') : null;
        if (!$userId) {
            $this->redirect('nguoi-dung/dang-nhap');
        }

        $conversations = $this->convSvc->getRepo()->listByUser($userId);

        $this->view('chat/index', [
            'title' => 'Hộp thư hỗ trợ & trò chuyện – ' . SITE_NAME,
            'conversations' => $conversations
        ]);
    }

    /**
     * Khởi tạo hội thoại và lấy lịch sử tin nhắn.
     * URL: GET /chat/bootstrap
     */
    public function bootstrap(): void
    {
        $type = $_GET['type'] ?? 'customer_cskh';
        $sellerId = isset($_GET['seller_id']) && $_GET['seller_id'] !== '' ? (int)$_GET['seller_id'] : null;

        $userId = $this->currentUserId();
        $guestToken = $this->guestToken(true);

        $convId = $this->convSvc->getOrCreate($type, $userId, $guestToken, $sellerId);
        if ($convId <= 0) {
            $this->json(['success' => false, 'message' => 'Không thể khởi tạo cuộc chat.'], 500);
            return;
        }

        // Đánh dấu đã đọc
        $this->chatSvc->getMessageRepo()->markAsRead($convId, $userId);

        $conv = $this->convSvc->getRepo()->findById($convId);
        $messages = $this->chatSvc->getMessageRepo()->getHistory($convId);

        $this->json([
            'success'      => true,
            'conversation' => $conv,
            'messages'     => $this->formatMessages($messages),
            'csrf'         => Csrf::token()
        ]);
    }

    /**
     * Gửi tin nhắn.
     * URL: POST /chat/send
     */
    public function send(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed.'], 405);
            return;
        }

        if (!Csrf::verify(false)) {
            $this->json(['success' => false, 'message' => 'Yêu cầu bảo mật không hợp lệ.'], 403);
            return;
        }

        $convId = (int)($_POST['conversation_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $msgType = trim($_POST['message_type'] ?? 'text');
        $attachment = trim($_POST['attachment'] ?? '');

        if ($convId <= 0) {
            $this->json(['success' => false, 'message' => 'Mã hội thoại không hợp lệ.'], 400);
            return;
        }

        if ($message === '' && $attachment === '') {
            $this->json(['success' => false, 'message' => 'Nội dung tin nhắn không được để trống.'], 422);
            return;
        }

        $userId = $this->currentUserId();
        $senderName = Session::get('user_name') ?: 'Khách hàng';

        $msgId = $this->chatSvc->sendMessage($convId, $userId, $senderName, $message, $msgType, $attachment);
        if ($msgId <= 0) {
            $this->json(['success' => false, 'message' => 'Lỗi gửi tin nhắn.'], 500);
            return;
        }

        $this->json([
            'success'    => true,
            'message_id' => $msgId
        ]);
    }

    /**
     * Upload File đính kèm trong cuộc chat.
     * URL: POST /chat/upload
     */
    public function upload(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed.'], 405);
            return;
        }

        $fileName = $this->uploadSvc->chatFile('file');
        if ($fileName) {
            $this->json([
                'success' => true,
                'file_name' => $fileName,
                'file_url' => URL_ROOT . '/public/uploads/chats/' . $fileName
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Tải file thất bại. Giới hạn 20MB và đúng định dạng cho phép.'], 400);
        }
    }

    /**
     * Đánh dấu đã đọc.
     * URL: POST /chat/read
     */
    public function read(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed.'], 405);
            return;
        }

        $convId = (int)($_POST['conversation_id'] ?? 0);
        if ($convId > 0) {
            $userId = $this->currentUserId();
            $this->chatSvc->getMessageRepo()->markAsRead($convId, $userId);
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'message' => 'Invalid ID.'], 400);
        }
    }

    /**
     * Đóng cuộc hội thoại.
     * URL: POST /chat/close
     */
    public function close(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed.'], 405);
            return;
        }

        $convId = (int)($_POST['conversation_id'] ?? 0);
        if ($convId > 0) {
            $this->convSvc->close($convId);
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'message' => 'Invalid ID.'], 400);
        }
    }

    /**
     * Polling lấy tin nhắn mới (Fallback khi không dùng SSE).
     * URL: GET /chat/poll/{conversationId}
     */
    public function poll(int $conversationId = 0): void
    {
        $convId = $conversationId ?: (int)($_GET['conversation_id'] ?? 0);
        $afterId = (int)($_GET['after_id'] ?? 0);

        if ($convId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid ID.'], 400);
            return;
        }

        $messages = $this->chatSvc->getMessageRepo()->getHistory($convId);
        $filtered = array_filter($messages, function($m) use ($afterId) {
            return (int)$m->id > $afterId;
        });

        $this->json([
            'success'  => true,
            'messages' => $this->formatMessages(array_values($filtered))
        ]);
    }

    /**
     * Server-Sent Events (SSE) Stream Realtime nhận tin nhắn tức thì.
     * URL: GET /chat/stream
     */
    public function stream(): void
    {
        @set_time_limit(0);
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        if (function_exists('ob_end_clean')) {
            @ob_end_clean();
        }
        ob_implicit_flush(true);

        $userId = $this->currentUserId();
        $guestToken = $this->guestToken(false);

        // File lắng nghe tin nhắn
        $bufferFile = '';
        if ($userId) {
            $bufferFile = sys_get_temp_dir() . '/chat_sse_user_' . $userId . '.json';
        } elseif ($guestToken) {
            $bufferFile = sys_get_temp_dir() . '/chat_sse_guest_' . md5($guestToken) . '.json';
        }

        // Thông báo kết nối
        echo "retry: 2000\n";
        echo "data: " . json_encode(['connected' => true]) . "\n\n";
        @ob_flush();
        flush();

        if ($bufferFile && file_exists($bufferFile)) {
            @unlink($bufferFile);
        }

        while (true) {
            if (connection_aborted()) {
                break;
            }

            if ($bufferFile && file_exists($bufferFile)) {
                $raw = @file_get_contents($bufferFile);
                if (!empty($raw)) {
                    @unlink($bufferFile);
                    $events = json_decode($raw, true) ?: [];
                    foreach ($events as $event) {
                        echo "event: chat_message\n";
                        echo "data: " . json_encode($event, JSON_UNESCAPED_UNICODE) . "\n\n";
                    }
                    @ob_flush();
                    flush();
                }
            }

            sleep(1);
        }
    }

    /**
     * Lấy thông tin trạng thái cuộc trò chuyện hiện tại.
     * URL: GET /chat/status
     */
    public function status(): void
    {
        $type = $_GET['type'] ?? 'customer_cskh';
        $sellerId = isset($_GET['seller_id']) && $_GET['seller_id'] !== '' ? (int)$_GET['seller_id'] : null;

        $userId = $this->currentUserId();
        $guestToken = $this->guestToken(false);

        $conv = $this->convSvc->getRepo()->findActive($type, $userId, $guestToken, $sellerId);
        $this->json([
            'success' => true,
            'active'  => $conv !== null,
            'conversation' => $conv
        ]);
    }

    private function currentUserId(): ?int
    {
        return Session::get('user_id') ? (int)Session::get('user_id') : null;
    }

    private function guestToken(bool $create): ?string
    {
        if (!Session::get('chat_guest_id') && $create) {
            Session::set('chat_guest_id', bin2hex(random_bytes(24)));
        }
        return Session::get('chat_guest_id') ?: null;
    }

    private function formatMessages(array $messages): array
    {
        $userId = $this->currentUserId();
        return array_map(function ($msg) use ($userId) {
            $isMine = false;
            if ($userId && (int)$msg->sender_id === $userId) {
                $isMine = true;
            } elseif (!$userId && $msg->sender_id === null) {
                $isMine = true;
            }

            return [
                'id'           => (int)$msg->id,
                'sender_id'    => $msg->sender_id,
                'sender_name'  => $msg->sender_name ?: 'Khách hàng',
                'message'      => htmlspecialchars($msg->message),
                'message_type' => $msg->message_type,
                'attachment'   => $msg->attachment,
                'is_read'      => (int)$msg->is_read === 1,
                'created_at'   => date('H:i d/m/Y', strtotime($msg->created_at)),
                'is_mine'      => $isMine
            ];
        }, $messages);
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
