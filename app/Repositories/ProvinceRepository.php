<?php

namespace App\Repositories;

use App\Models\Database;
use PDO;

/**
 * ProvinceRepository – Truy vấn CSDL cho bảng `provinces`.
 * Tuân thủ SOLID, Repository Pattern.
 */
class ProvinceRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getAll(string $search = ''): array
    {
        $sql = 'SELECT * FROM provinces';
        if (! empty($search)) {
            $sql .= ' WHERE name LIKE :search OR code LIKE :search';
        }
        $sql .= ' ORDER BY sort_order ASC, name ASC';

        $this->db->query($sql);
        if (! empty($search)) {
            $this->db->bind(':search', '%'.$search.'%');
        }

        return $this->db->resultSet() ?: [];
    }

    public function findById(int $id): ?object
    {
        $this->db->query('SELECT * FROM provinces WHERE id = :id LIMIT 1');
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $res = $this->db->single();

        return $res ? $res : null;
    }

    public function findByCode(string $code): ?object
    {
        $this->db->query('SELECT * FROM provinces WHERE code = :code LIMIT 1');
        $this->db->bind(':code', $code);
        $res = $this->db->single();

        return $res ? $res : null;
    }

    public function create(array $data): int
    {
        $this->db->query('
            INSERT INTO provinces (code, name, type, sort_order, status) 
            VALUES (:code, :name, :type, :sort_order, :status)
        ');
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':type', $data['type'] ?? null);
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
            UPDATE provinces 
            SET code = :code, name = :name, type = :type, sort_order = :sort_order, status = :status
            WHERE id = :id
        ');
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':type', $data['type'] ?? null);
        $this->db->bind(':sort_order', (int) ($data['sort_order'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':status', $data['status'] ?? 'active');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query('DELETE FROM provinces WHERE id = :id');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }
}
