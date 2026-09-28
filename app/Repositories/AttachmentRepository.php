<?php

namespace App\Repositories;

use App\Models\Database;

/**
 * AttachmentRepository – Quản lý việc truy xuất các tin nhắn chứa file đính kèm.
 * Tuân thủ SOLID, Repository Pattern.
 */
class AttachmentRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Danh sách tệp đính kèm trong cuộc hội thoại.
     */
    public function listByConversation(int $conversationId): array
    {
        $this->db->query("SELECT id, conversation_id, sender_id, sender_name, message, message_type, attachment, created_at 
                          FROM tin_nhan 
                          WHERE conversation_id = :conv_id AND attachment IS NOT NULL AND attachment <> ''
                          ORDER BY id DESC");
        $this->db->bind(':conv_id', $conversationId);

        return $this->db->resultSet();
    }
}
