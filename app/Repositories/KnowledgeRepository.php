<?php

namespace App\Repositories;

use App\Models\Database;

/**
 * KnowledgeRepository – Tương tác trực tiếp với bảng `knowledge_base` trong CSDL.
 * Tuân thủ SOLID, Repository Pattern.
 */
class KnowledgeRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function findById(int $id): ?stdClass
    {
        $this->db->query('SELECT * FROM knowledge_base WHERE id = :id');
        $this->db->bind(':id', $id);
        $row = $this->db->single();

        return $row ?: null;
    }

    public function listAll(string $category = ''): array
    {
        if (! empty($category)) {
            $this->db->query('SELECT * FROM knowledge_base WHERE category = :cat ORDER BY id DESC');
            $this->db->bind(':cat', $category);
        } else {
            $this->db->query('SELECT * FROM knowledge_base ORDER BY id DESC');
        }

        return $this->db->resultSet();
    }

    public function insert(array $data): int
    {
        $this->db->query('INSERT INTO knowledge_base (category, title, content, is_active)
                          VALUES (:cat, :title, :content, :active)');

        $this->db->bind(':cat', $data['category']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':content', $data['content']);
        $this->db->bind(':active', $data['is_active'] ?? 1);

        if ($this->db->execute()) {
            $this->db->query('SELECT LAST_INSERT_ID() as last_id');

            return (int) ($this->db->single()->last_id ?? 0);
        }

        return 0;
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query('UPDATE knowledge_base 
                          SET category = :cat, title = :title, content = :content, is_active = :active
                          WHERE id = :id');

        $this->db->bind(':cat', $data['category']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':content', $data['content']);
        $this->db->bind(':active', $data['is_active'] ?? 1);
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query('DELETE FROM knowledge_base WHERE id = :id');
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    /**
     * Nhận về toàn bộ tri thức đang kích hoạt ghép thành chuỗi ngữ cảnh.
     */
    public function getActiveContext(): string
    {
        $this->db->query('SELECT category, title, content FROM knowledge_base WHERE is_active = 1');
        $rows = $this->db->resultSet();

        $context = "\n\n--- CƠ SỞ TRI THỨC VÀ HƯỚNG DẪN HỆ THỐNG TIMNHADAT.SITE ---\n";
        foreach ($rows as $r) {
            $catName = match ($r->category) {
                'faq' => 'Câu hỏi thường gặp',
                'policies' => 'Chính sách mua bán',
                'prices' => 'Bảng giá dịch vụ',
                default => 'Thông tin chung'
            };
            $context .= "[{$catName}] **{$r->title}**\n";
            $context .= "Mô tả tri thức: {$r->content}\n\n";
        }

        return $context;
    }
}
