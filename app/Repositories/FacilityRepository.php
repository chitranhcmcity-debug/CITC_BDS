<?php

namespace App\Repositories;

use App\Models\Database;
use PDO;

/**
 * FacilityRepository – Truy vấn CSDL cho bảng `tien_ich`.
 * Tuân thủ SOLID, Repository Pattern.
 */
class FacilityRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getAll(string $search = ''): array
    {
        $sql = 'SELECT * FROM tien_ich';
        if (! empty($search)) {
            $sql .= ' WHERE name LIKE :search OR slug LIKE :search';
        }
        $sql .= ' ORDER BY sort_order ASC, id DESC';

        $this->db->query($sql);
        if (! empty($search)) {
            $this->db->bind(':search', '%'.$search.'%');
        }

        return $this->db->resultSet() ?: [];
    }

    public function findById(int $id): ?object
    {
        $this->db->query('SELECT * FROM tien_ich WHERE id = :id LIMIT 1');
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $res = $this->db->single();

        return $res ? $res : null;
    }

    public function findBySlug(string $slug): ?object
    {
        $this->db->query('SELECT * FROM tien_ich WHERE slug = :slug LIMIT 1');
        $this->db->bind(':slug', $slug);
        $res = $this->db->single();

        return $res ? $res : null;
    }

    public function create(array $data): int
    {
        $this->db->query('
            INSERT INTO tien_ich (name, slug, icon, sort_order, status) 
            VALUES (:name, :slug, :icon, :sort_order, :status)
        ');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':slug', $data['slug']);
        $this->db->bind(':icon', $data['icon'] ?? null);
        $this->db->bind(':sort_order', (int) ($data['sort_order'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':status', $data['status'] ?? 'active');

        if ($this->db->execute()) {
            $this->db->query('SELECT LAST_INSERT_ID() as last_id');

            return (int) ($this->db->single()->last_id ?? 0);
        }

        return 0;
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query('
            UPDATE tien_ich 
            SET name = :name, slug = :slug, icon = :icon, sort_order = :sort_order, status = :status
            WHERE id = :id
        ');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':slug', $data['slug']);
        $this->db->bind(':icon', $data['icon'] ?? null);
        $this->db->bind(':sort_order', (int) ($data['sort_order'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':status', $data['status'] ?? 'active');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query('DELETE FROM tien_ich WHERE id = :id');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    public function changeStatus(int $id, string $status): bool
    {
        $this->db->query('UPDATE tien_ich SET status = :status WHERE id = :id');
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    public function updateSortOrder(int $id, int $sortOrder): bool
    {
        $this->db->query('UPDATE tien_ich SET sort_order = :sort_order WHERE id = :id');
        $this->db->bind(':sort_order', $sortOrder, PDO::PARAM_INT);
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }
}
