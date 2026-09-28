<?php
/**
 * MessageRepository – Thực hiện các câu lệnh SQL tác động lên bảng `chat_messages`.
 * Tuân thủ SOLID, Repository Pattern.
 */
class MessageRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function findById(int $id): ?stdClass
    {
        $this->db->query("SELECT * FROM chat_messages WHERE id = :id");
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        return $row ?: null;
    }

    public function insert(array $data): int
    {
        $this->db->query("INSERT INTO chat_messages (conversation_id, sender_id, sender_name, message, message_type, attachment, is_read)
                          VALUES (:conv_id, :sender_id, :sender_name, :message, :type, :attach, :read)");
        
        $this->db->bind(':conv_id',     $data['conversation_id']);
        $this->db->bind(':sender_id',   $data['sender_id'] ?? null);
        $this->db->bind(':sender_name', $data['sender_name'] ?? null);
        $this->db->bind(':message',     $data['message']);
        $this->db->bind(':type',        $data['message_type'] ?? 'text');
        $this->db->bind(':attach',      $data['attachment'] ?? null);
        $this->db->bind(':read',        $data['is_read'] ?? 0);

        if ($this->db->execute()) {
            $this->db->query("SELECT LAST_INSERT_ID() as last_id");
            return (int)($this->db->single()->last_id ?? 0);
        }
        return 0;
    }

    /**
     * Lấy lịch sử tin nhắn của cuộc trò chuyện.
     */
    public function getHistory(int $conversationId, int $limit = 50, int $offset = 0): array
    {
        $this->db->query("SELECT * FROM chat_messages 
                          WHERE conversation_id = :conv_id
                          ORDER BY id ASC 
                          LIMIT :limit OFFSET :offset");
        $this->db->bind(':conv_id', $conversationId);
        $this->db->bind(':limit', $limit);
        $this->db->bind(':offset', $offset);
        return $this->db->resultSet();
    }

    /**
     * Đánh dấu toàn bộ tin nhắn trong cuộc trò chuyện là đã đọc (ngoại trừ tin của chính người đọc).
     */
    public function markAsRead(int $conversationId, ?int $readerId): bool
    {
        if ($readerId !== null) {
            $this->db->query("UPDATE chat_messages 
                              SET is_read = 1 
                              WHERE conversation_id = :conv_id AND (sender_id <> :reader_id OR sender_id IS NULL) AND is_read = 0");
            $this->db->bind(':conv_id', $conversationId);
            $this->db->bind(':reader_id', $readerId);
        } else {
            // Guest reader
            $this->db->query("UPDATE chat_messages 
                              SET is_read = 1 
                              WHERE conversation_id = :conv_id AND sender_id IS NOT NULL AND is_read = 0");
            $this->db->bind(':conv_id', $conversationId);
        }
        return $this->db->execute();
    }

    /**
     * Thu hồi tin nhắn (xóa nội dung nhưng giữ dòng tin hiển thị dạng: 'Tin nhắn đã bị thu hồi').
     */
    public function revoke(int $id, int $senderId): bool
    {
        $this->db->query("UPDATE chat_messages 
                          SET message = 'Tin nhắn đã bị thu hồi.', message_type = 'system', attachment = NULL 
                          WHERE id = :id AND sender_id = :sender");
        $this->db->bind(':id', $id);
        $this->db->bind(':sender', $senderId);
        return $this->db->execute();
    }

    /**
     * Xóa hoàn toàn một tin nhắn.
     */
    public function delete(int $id, int $senderId): bool
    {
        $this->db->query("DELETE FROM chat_messages WHERE id = :id AND sender_id = :sender");
        $this->db->bind(':id', $id);
        $this->db->bind(':sender', $senderId);
        return $this->db->execute();
    }

    /**
     * Thống kê tổng số tin nhắn toàn cục (Analytics).
     */
    public function countAll(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM chat_messages");
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }
}
