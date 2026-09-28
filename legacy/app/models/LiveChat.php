<?php

class LiveChat extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'live_chat_conversations';
    }

    public function getOrCreateConversation(array $identity): object|false
    {
        $userId = $identity['user_id'] ?? null;
        $guestToken = $identity['guest_token'] ?? null;

        if ($userId) {
            $this->db->query("SELECT * FROM live_chat_conversations
                              WHERE ma_nguoi_dung = :uid AND status <> 'closed'
                              ORDER BY id DESC LIMIT 1");
            $this->db->bind(':uid', (int)$userId);
        } else {
            $this->db->query("SELECT * FROM live_chat_conversations
                              WHERE guest_token = :token AND status <> 'closed'
                              ORDER BY id DESC LIMIT 1");
            $this->db->bind(':token', $guestToken);
        }

        $existing = $this->db->single();
        if ($existing) {
            return $existing;
        }

        $this->db->query("INSERT INTO live_chat_conversations
            (guest_token, ma_nguoi_dung, customer_name, customer_email, customer_phone, status, last_message_at)
            VALUES (:guest_token, :user_id, :name, :email, :phone, 'waiting', NOW())");
        $this->db->bind(':guest_token', $guestToken);
        $this->db->bind(':user_id', $userId ? (int)$userId : null);
        $this->db->bind(':name', $identity['name'] ?? null);
        $this->db->bind(':email', $identity['email'] ?? null);
        $this->db->bind(':phone', $identity['phone'] ?? null);

        if (!$this->db->execute()) {
            return false;
        }

        return $this->findConversation((int)$this->db->lastInsertId());
    }

    public function findConversation(int $id): object|false
    {
        $this->db->query("SELECT c.*, nd.ten AS staff_name
                          FROM live_chat_conversations c
                          LEFT JOIN nguoi_dung nd ON c.assigned_staff_id = nd.id
                          WHERE c.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function findActiveConversation(?int $userId, ?string $guestToken): object|false
    {
        if ($userId) {
            $this->db->query("SELECT c.*, nd.ten AS staff_name
                              FROM live_chat_conversations c
                              LEFT JOIN nguoi_dung nd ON c.assigned_staff_id = nd.id
                              WHERE c.ma_nguoi_dung = :uid AND c.status <> 'closed'
                              ORDER BY c.id DESC LIMIT 1");
            $this->db->bind(':uid', $userId);
            return $this->db->single();
        }

        if ($guestToken) {
            $this->db->query("SELECT c.*, nd.ten AS staff_name
                              FROM live_chat_conversations c
                              LEFT JOIN nguoi_dung nd ON c.assigned_staff_id = nd.id
                              WHERE c.guest_token = :token AND c.status <> 'closed'
                              ORDER BY c.id DESC LIMIT 1");
            $this->db->bind(':token', $guestToken);
            return $this->db->single();
        }

        return false;
    }

    public function userCanAccess(object $conversation, ?int $userId, ?string $guestToken): bool
    {
        if ($userId && (int)$conversation->ma_nguoi_dung === $userId) {
            return true;
        }
        return !$conversation->ma_nguoi_dung && $guestToken && hash_equals((string)$conversation->guest_token, $guestToken);
    }

    public function addMessage(int $conversationId, string $senderType, ?int $senderId, ?string $senderName, string $message): int|false
    {
        $message = trim($message);
        if ($message === '') {
            return false;
        }

        $isAdminSide = in_array($senderType, ['staff', 'admin'], true);

        try {
            $this->db->beginTransaction();

            $this->db->query("INSERT INTO live_chat_messages
                (conversation_id, sender_type, sender_id, sender_name, message, is_read_by_admin, is_read_by_customer)
                VALUES (:conversation_id, :sender_type, :sender_id, :sender_name, :message, :read_admin, :read_customer)");
            $this->db->bind(':conversation_id', $conversationId);
            $this->db->bind(':sender_type', $senderType);
            $this->db->bind(':sender_id', $senderId);
            $this->db->bind(':sender_name', $senderName);
            $this->db->bind(':message', $message);
            $this->db->bind(':read_admin', $isAdminSide ? 1 : 0);
            $this->db->bind(':read_customer', $isAdminSide ? 0 : 1);

            if (!$this->db->execute()) {
                $this->db->rollBack();
                return false;
            }

            $messageId = (int)$this->db->lastInsertId();
            $statusSql = $isAdminSide ? "status = IF(status = 'closed', 'open', status)" : "status = IF(status = 'closed', 'waiting', status)";
            $unreadColumn = $isAdminSide ? 'unread_customer' : 'unread_admin';

            $this->db->query("UPDATE live_chat_conversations
                              SET {$statusSql},
                                  last_message = :last_message,
                                  last_message_at = NOW(),
                                  {$unreadColumn} = {$unreadColumn} + 1,
                                  updated_at = NOW()
                              WHERE id = :id");
            $this->db->bind(':last_message', mb_substr($message, 0, 500, 'UTF-8'));
            $this->db->bind(':id', $conversationId);
            $this->db->execute();

            $this->db->commit();
            return $messageId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('LiveChat addMessage error: ' . $e->getMessage());
            SystemLogger::error($e, 'chat', 'error', ['conversation_id'=>$conversationId]);
            return false;
        }
    }

    public function getMessages(int $conversationId, int $afterId = 0): array
    {
        $this->db->query("SELECT * FROM live_chat_messages
                          WHERE conversation_id = :conversation_id AND id > :after_id AND is_internal = 0
                          ORDER BY id ASC LIMIT 100");
        $this->db->bind(':conversation_id', $conversationId);
        $this->db->bind(':after_id', $afterId);
        return $this->db->resultSet();
    }

    public function listConversations(string $status = 'all'): array
    {
        $where = '';
        if (in_array($status, ['waiting', 'open', 'closed'], true)) {
            $where = "WHERE c.status = :status";
        }

        $this->db->query("SELECT c.*, nd.ten AS staff_name
                          FROM live_chat_conversations c
                          LEFT JOIN nguoi_dung nd ON c.assigned_staff_id = nd.id
                          {$where}
                          ORDER BY c.priority DESC, c.last_message_at DESC, c.id DESC
                          LIMIT 100");
        if ($where) {
            $this->db->bind(':status', $status);
        }
        return $this->db->resultSet();
    }

    public function claimConversation(int $conversationId, int $staffId): bool
    {
        $this->db->query("UPDATE live_chat_conversations
                          SET assigned_staff_id = :staff_id, status = 'open', updated_at = NOW()
                          WHERE id = :id");
        $this->db->bind(':staff_id', $staffId);
        $this->db->bind(':id', $conversationId);
        return $this->db->execute();
    }

    public function closeConversation(int $conversationId): bool
    {
        $this->db->query("UPDATE live_chat_conversations
                          SET status = 'closed', unread_admin = 0, unread_customer = 0, closed_at = NOW(), updated_at = NOW()
                          WHERE id = :id");
        $this->db->bind(':id', $conversationId);
        return $this->db->execute();
    }

    public function markReadByAdmin(int $conversationId): bool
    {
        $this->db->query("UPDATE live_chat_conversations SET unread_admin = 0 WHERE id = :id");
        $this->db->bind(':id', $conversationId);
        $ok = $this->db->execute();

        $this->db->query("UPDATE live_chat_messages SET is_read_by_admin = 1 WHERE conversation_id = :id");
        $this->db->bind(':id', $conversationId);
        return $this->db->execute() && $ok;
    }

    public function markReadByCustomer(int $conversationId): bool
    {
        $this->db->query("UPDATE live_chat_conversations SET unread_customer = 0 WHERE id = :id");
        $this->db->bind(':id', $conversationId);
        $ok = $this->db->execute();

        $this->db->query("UPDATE live_chat_messages SET is_read_by_customer = 1 WHERE conversation_id = :id");
        $this->db->bind(':id', $conversationId);
        return $this->db->execute() && $ok;
    }

    public function countWaitingForAdmin(): int
    {
        $this->db->query("SELECT COUNT(*) AS count
                          FROM live_chat_conversations
                          WHERE status <> 'closed' AND unread_admin > 0");
        return (int)($this->db->single()->count ?? 0);
    }

    public function stats(): array
    {
        $this->db->query("SELECT
            COUNT(*) AS total,
            SUM(status = 'waiting') AS waiting,
            SUM(status = 'open') AS open_count,
            SUM(status = 'closed') AS closed_count,
            SUM(DATE(created_at) = CURDATE()) AS today
            FROM live_chat_conversations");
        $row = $this->db->single();
        return [
            'total' => (int)($row->total ?? 0),
            'waiting' => (int)($row->waiting ?? 0),
            'open' => (int)($row->open_count ?? 0),
            'closed' => (int)($row->closed_count ?? 0),
            'today' => (int)($row->today ?? 0),
            'waiting_for_admin' => $this->countWaitingForAdmin(),
        ];
    }
}
