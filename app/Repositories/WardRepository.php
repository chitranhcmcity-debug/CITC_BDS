<?php

namespace App\Repositories;

use App\Models\Database;
use PDO;

/**
 * WardRepository – Truy vấn CSDL cho bảng `wards`.
 * Tuân thủ SOLID, Repository Pattern.
 */
class WardRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getAll(string $search = ''): array
    {
        $sql = 'SELECT w.*, d.name as district_name, p.name as province_name 
                FROM wards w 
                JOIN districts d ON w.district_code = d.code
                JOIN provinces p ON d.province_code = p.code';
        if (! empty($search)) {
            $sql .= ' WHERE w.name LIKE :search OR w.code LIKE :search OR d.name LIKE :search OR p.name LIKE :search';
        }
        $sql .= ' ORDER BY w.district_code ASC, w.sort_order ASC, w.name ASC';

        $this->db->query($sql);
        if (! empty($search)) {
            $this->db->bind(':search', '%'.$search.'%');
        }

        return $this->db->resultSet() ?: [];
    }

    public function findById(int $id): ?object
    {
        $this->db->query('SELECT * FROM wards WHERE id = :id LIMIT 1');
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $res = $this->db->single();

        return $res ? $res : null;
    }

    public function findByCode(string $code): ?object
    {
        $this->db->query('SELECT * FROM wards WHERE code = :code LIMIT 1');
        $this->db->bind(':code', $code);
        $res = $this->db->single();

        return $res ? $res : null;
    }

    public function getByDistrict(string $districtCode): array
    {
        $this->db->query("SELECT * FROM wards WHERE district_code = :dist AND status = 'active' ORDER BY sort_order ASC, name ASC");
        $this->db->bind(':dist', $districtCode);

        return $this->db->resultSet() ?: [];
    }

    public function create(array $data): int
    {
        $this->db->query('
            INSERT INTO wards (code, district_code, name, type, sort_order, status) 
            VALUES (:code, :district_code, :name, :type, :sort_order, :status)
        ');
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':district_code', $data['district_code']);
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
            UPDATE wards 
            SET code = :code, district_code = :district_code, name = :name, type = :type, 
                sort_order = :sort_order, status = :status
            WHERE id = :id
        ');
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':district_code', $data['district_code']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':type', $data['type'] ?? null);
        $this->db->bind(':sort_order', (int) ($data['sort_order'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':status', $data['status'] ?? 'active');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query('DELETE FROM wards WHERE id = :id');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }
}
