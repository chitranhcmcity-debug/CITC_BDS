<?php

namespace App\Repositories;

use App\Models\Database;
use PDO;

/**
 * CategoryRepository – Truy vấn cơ sở dữ liệu cho bảng `nhom_danh_muc`.
 * Hỗ trợ các phương thức CRUD, đổi trạng thái và tương thích ngược với hệ thống cũ.
 * Tuân thủ SOLID, Repository Pattern.
 */
class CategoryRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Tương thích ngược: Lấy tất cả danh mục đang hoạt động dạng [id, ten, duong_dan].
     */
    public function all(): array
    {
        $this->db->query("
            SELECT id, name as ten, slug as duong_dan 
            FROM nhom_danh_muc 
            WHERE status = 'active' 
            ORDER BY sort_order ASC, name ASC
        ");

        return $this->db->resultSet() ?: [];
    }

    /**
     * Kiểm tra tồn tại danh mục hoạt động.
     */
    public function exists(int $id): bool
    {
        $this->db->query("SELECT 1 FROM nhom_danh_muc WHERE id = :id AND status = 'active'");
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return (bool) $this->db->single();
    }

    /**
     * Lấy danh sách toàn bộ danh mục kèm tìm kiếm và sắp xếp.
     */
    public function getAll(string $search = ''): array
    {
        $sql = 'SELECT * FROM nhom_danh_muc';
        if (! empty($search)) {
            $sql .= ' WHERE name LIKE :search OR slug LIKE :search OR code LIKE :search';
        }
        $sql .= ' ORDER BY sort_order ASC, id DESC';

        $this->db->query($sql);
        if (! empty($search)) {
            $this->db->bind(':search', '%'.$search.'%');
        }

        return $this->db->resultSet() ?: [];
    }

    /**
     * Tìm danh mục theo ID.
     */
    public function findById(int $id): ?object
    {
        $this->db->query('SELECT * FROM nhom_danh_muc WHERE id = :id LIMIT 1');
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $res = $this->db->single();

        return $res ? $res : null;
    }

    /**
     * Tìm danh mục theo Slug.
     */
    public function findBySlug(string $slug): ?object
    {
        $this->db->query('SELECT * FROM nhom_danh_muc WHERE slug = :slug LIMIT 1');
        $this->db->bind(':slug', $slug);
        $res = $this->db->single();

        return $res ? $res : null;
    }

    /**
     * Tạo danh mục mới.
     */
    public function create(array $data): int
    {
        $this->db->query('
            INSERT INTO nhom_danh_muc (name, slug, code, icon, description, sort_order, color, status) 
            VALUES (:name, :slug, :code, :icon, :description, :sort_order, :color, :status)
        ');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':slug', $data['slug']);
        $this->db->bind(':code', $data['code'] ?? null);
        $this->db->bind(':icon', $data['icon'] ?? null);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':sort_order', (int) ($data['sort_order'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':color', $data['color'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'active');

        if ($this->db->execute()) {
            $this->db->query('SELECT LAST_INSERT_ID() as last_id');

            return (int) ($this->db->single()->last_id ?? 0);
        }

        return 0;
    }

    /**
     * Cập nhật danh mục.
     */
    public function update(int $id, array $data): bool
    {
        $this->db->query('
            UPDATE nhom_danh_muc 
            SET name = :name, slug = :slug, code = :code, icon = :icon, 
                description = :description, sort_order = :sort_order, color = :color, status = :status
            WHERE id = :id
        ');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':slug', $data['slug']);
        $this->db->bind(':code', $data['code'] ?? null);
        $this->db->bind(':icon', $data['icon'] ?? null);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':sort_order', (int) ($data['sort_order'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':color', $data['color'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'active');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Xóa danh mục.
     */
    public function delete(int $id): bool
    {
        $this->db->query('DELETE FROM nhom_danh_muc WHERE id = :id');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Đổi trạng thái danh mục.
     */
    public function changeStatus(int $id, string $status): bool
    {
        $this->db->query('UPDATE nhom_danh_muc SET status = :status WHERE id = :id');
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Cập nhật thứ tự sắp xếp.
     */
    public function updateSortOrder(int $id, int $sortOrder): bool
    {
        $this->db->query('UPDATE nhom_danh_muc SET sort_order = :sort_order WHERE id = :id');
        $this->db->bind(':sort_order', $sortOrder, PDO::PARAM_INT);
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }
}
