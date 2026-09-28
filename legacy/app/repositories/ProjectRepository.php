<?php
/**
 * ProjectRepository – Truy vấn CSDL cho bảng `projects` (Dự án dùng chung).
 * Tuân thủ SOLID, Repository Pattern.
 */
class ProjectRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getAll(string $search = ''): array
    {
        $sql = "SELECT * FROM projects";
        if (!empty($search)) {
            $sql .= " WHERE name LIKE :search OR investor LIKE :search OR address LIKE :search";
        }
        $sql .= " ORDER BY sort_order ASC, id DESC";

        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', '%' . $search . '%');
        }
        return $this->db->resultSet() ?: [];
    }

    public function findById(int $id): ?object
    {
        $this->db->query("SELECT * FROM projects WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $res = $this->db->single();
        return $res ? $res : null;
    }

    public function create(array $data): int
    {
        $this->db->query("
            INSERT INTO projects (name, investor, address, google_map, logo, image, description, facilities, sort_order, status) 
            VALUES (:name, :investor, :address, :google_map, :logo, :image, :description, :facilities, :sort_order, :status)
        ");
        $this->db->bind(':name',        $data['name']);
        $this->db->bind(':investor',    $data['investor'] ?? null);
        $this->db->bind(':address',     $data['address'] ?? null);
        $this->db->bind(':google_map',  $data['google_map'] ?? null);
        $this->db->bind(':logo',        $data['logo'] ?? null);
        $this->db->bind(':image',       $data['image'] ?? null);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':facilities',  $data['facilities'] ?? null);
        $this->db->bind(':sort_order',  (int)($data['sort_order'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':status',      $data['status'] ?? 'active');

        if ($this->db->execute()) {
            $this->db->query("SELECT LAST_INSERT_ID() as last_id");
            return (int)($this->db->single()->last_id ?? 0);
        }
        return 0;
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query("
            UPDATE projects 
            SET name = :name, investor = :investor, address = :address, google_map = :google_map, 
                logo = :logo, image = :image, description = :description, facilities = :facilities, 
                sort_order = :sort_order, status = :status
            WHERE id = :id
        ");
        $this->db->bind(':name',        $data['name']);
        $this->db->bind(':investor',    $data['investor'] ?? null);
        $this->db->bind(':address',     $data['address'] ?? null);
        $this->db->bind(':google_map',  $data['google_map'] ?? null);
        $this->db->bind(':logo',        $data['logo'] ?? null);
        $this->db->bind(':image',       $data['image'] ?? null);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':facilities',  $data['facilities'] ?? null);
        $this->db->bind(':sort_order',  (int)($data['sort_order'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':status',      $data['status'] ?? 'active');
        $this->db->bind(':id',          $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM projects WHERE id = :id");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->execute();
    }

    public function changeStatus(int $id, string $status): bool
    {
        $this->db->query("UPDATE projects SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->execute();
    }

    public function updateSortOrder(int $id, int $sortOrder): bool
    {
        $this->db->query("UPDATE projects SET sort_order = :sort_order WHERE id = :id");
        $this->db->bind(':sort_order', $sortOrder, PDO::PARAM_INT);
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->execute();
    }
}
