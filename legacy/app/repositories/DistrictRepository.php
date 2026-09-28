<?php
/**
 * DistrictRepository – Truy vấn CSDL cho bảng `districts`.
 * Tuân thủ SOLID, Repository Pattern.
 */
class DistrictRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getAll(string $search = ''): array
    {
        $sql = "SELECT d.*, p.name as province_name 
                FROM districts d 
                JOIN provinces p ON d.province_code = p.code";
        if (!empty($search)) {
            $sql .= " WHERE d.name LIKE :search OR d.code LIKE :search OR p.name LIKE :search";
        }
        $sql .= " ORDER BY d.province_code ASC, d.sort_order ASC, d.name ASC";

        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', '%' . $search . '%');
        }
        return $this->db->resultSet() ?: [];
    }

    public function findById(int $id): ?object
    {
        $this->db->query("SELECT * FROM districts WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $res = $this->db->single();
        return $res ? $res : null;
    }

    public function findByCode(string $code): ?object
    {
        $this->db->query("SELECT * FROM districts WHERE code = :code LIMIT 1");
        $this->db->bind(':code', $code);
        $res = $this->db->single();
        return $res ? $res : null;
    }

    public function getByProvince(string $provinceCode): array
    {
        $this->db->query("SELECT * FROM districts WHERE province_code = :prov AND status = 'active' ORDER BY sort_order ASC, name ASC");
        $this->db->bind(':prov', $provinceCode);
        return $this->db->resultSet() ?: [];
    }

    public function create(array $data): int
    {
        $this->db->query("
            INSERT INTO districts (code, province_code, name, type, sort_order, status) 
            VALUES (:code, :province_code, :name, :type, :sort_order, :status)
        ");
        $this->db->bind(':code',          $data['code']);
        $this->db->bind(':province_code', $data['province_code']);
        $this->db->bind(':name',          $data['name']);
        $this->db->bind(':type',          $data['type'] ?? null);
        $this->db->bind(':sort_order',    (int)($data['sort_order'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':status',        $data['status'] ?? 'active');

        if ($this->db->execute()) {
            $this->db->query("SELECT LAST_INSERT_ID() as last_id");
            return (int)($this->db->single()->last_id ?? 0);
        }
        return 0;
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query("
            UPDATE districts 
            SET code = :code, province_code = :province_code, name = :name, type = :type, 
                sort_order = :sort_order, status = :status
            WHERE id = :id
        ");
        $this->db->bind(':code',          $data['code']);
        $this->db->bind(':province_code', $data['province_code']);
        $this->db->bind(':name',          $data['name']);
        $this->db->bind(':type',          $data['type'] ?? null);
        $this->db->bind(':sort_order',    (int)($data['sort_order'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':status',        $data['status'] ?? 'active');
        $this->db->bind(':id',            $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM districts WHERE id = :id");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->execute();
    }
}
