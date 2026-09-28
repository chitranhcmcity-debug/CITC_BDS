<?php
/**
 * ChatService – Quản lý gửi tin nhắn và đồng bộ hoá Realtime.
 * Tuân thủ SOLID, Service Pattern.
 */
class ChatService
{
    private MessageRepository $messageRepo;
    private ConversationRepository $convRepo;
    private AttachmentRepository $attachRepo;

    public function __construct()
    {
        require_once APP_ROOT . '/app/repositories/MessageRepository.php';
        require_once APP_ROOT . '/app/repositories/ConversationRepository.php';
        require_once APP_ROOT . '/app/repositories/AttachmentRepository.php';

        $this->messageRepo = new MessageRepository();
        $this->convRepo = new ConversationRepository();
        $this->attachRepo = new AttachmentRepository();
    }

    public function getMessageRepo(): MessageRepository
    {
        return $this->messageRepo;
    }

    public function getAttachRepo(): AttachmentRepository
    {
        return $this->attachRepo;
    }

    /**
     * Gửi tin nhắn mới trong hội thoại.
     */
    public function sendMessage(int $conversationId, ?int $senderId, string $senderName, string $message, string $messageType = 'text', ?string $attachment = null): int|false
    {
        $conv = $this->convRepo->findById($conversationId);
        if (!$conv) return false;

        $msgData = [
            'conversation_id' => $conversationId,
            'sender_id'       => $senderId,
            'sender_name'     => $senderName,
            'message'         => trim($message),
            'message_type'    => $messageType,
            'attachment'      => $attachment,
            'is_read'         => 0
        ];

        $insertedId = $this->messageRepo->insert($msgData);
        if ($insertedId <= 0) return false;

        $msgData['id'] = $insertedId;
        $msgData['created_at'] = date('Y-m-d H:i:s');

        // Cập nhật thời gian tương tác cuối trên hội thoại
        $db = new Database();
        $db->query("UPDATE chat_conversations SET updated_at = NOW() WHERE id = :id");
        $db->bind(':id', $conversationId);
        $db->execute();

        // ── ĐẨY REALTIME QUA SSE ──
        $this->dispatchRealtime($conv, $msgData);

        return $insertedId;
    }

    /**
     * Phân phối tin nhắn realtime tới các bên liên quan.
     */
    private function dispatchRealtime(object $conv, array $msg): void
    {
        $userIdsToNotify = [];
        $guestTokensToNotify = [];

        // Xác định ai sẽ nhận tin nhắn này (loại trừ người gửi)
        $senderId = $msg['sender_id'];

        if ($conv->type === 'customer_seller') {
            if ($senderId === $conv->customer_id) {
                if ($conv->seller_id) $userIdsToNotify[] = $conv->seller_id;
            } else {
                if ($conv->customer_id) $userIdsToNotify[] = $conv->customer_id;
                if ($conv->customer_guest_token) $guestTokensToNotify[] = $conv->customer_guest_token;
            }
        } elseif ($conv->type === 'customer_cskh') {
            if ($senderId === $conv->customer_id) {
                // Đẩy cho nhân viên hỗ trợ được gán, hoặc toàn bộ admin/staff nếu chưa gán
                if ($conv->staff_id) {
                    $userIdsToNotify[] = $conv->staff_id;
                } else {
                    // Lấy toàn bộ Admin/CSKH
                    $userRepo = new UserRepository();
                    $admins = $userRepo->findByRole(1); // Admin
                    foreach ($admins as $ad) {
                        $userIdsToNotify[] = (int)$ad->id;
                    }
                }
            } else {
                if ($conv->customer_id) $userIdsToNotify[] = $conv->customer_id;
                if ($conv->customer_guest_token) $guestTokensToNotify[] = $conv->customer_guest_token;
            }
        } elseif ($conv->type === 'cskh_admin') {
            if ($senderId === $conv->staff_id) {
                $userIdsToNotify[] = $conv->customer_id; // Chứa admin ID ở đầu kia
            } else {
                $userIdsToNotify[] = $conv->staff_id;
            }
        }

        // Ghi vào file buffer SSE
        foreach ($userIdsToNotify as $uid) {
            $this->publishSseUser($uid, $msg);
        }
        foreach ($guestTokensToNotify as $token) {
            $this->publishSseGuest($token, $msg);
        }
    }

    private function publishSseUser(int $userId, array $msg): void
    {
        $file = sys_get_temp_dir() . '/chat_sse_user_' . $userId . '.json';
        $events = [];
        if (file_exists($file)) {
            $raw = @file_get_contents($file);
            $events = json_decode($raw, true) ?: [];
        }
        $events[] = $msg;
        @file_put_contents($file, json_encode($events, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    private function publishSseGuest(string $token, array $msg): void
    {
        $file = sys_get_temp_dir() . '/chat_sse_guest_' . md5($token) . '.json';
        $events = [];
        if (file_exists($file)) {
            $raw = @file_get_contents($file);
            $events = json_decode($raw, true) ?: [];
        }
        $events[] = $msg;
        @file_put_contents($file, json_encode($events, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

}
