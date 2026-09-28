<?php

namespace App\Repositories;

use App\Models\Database;
use PDO;

class BackupRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function add(string $type, string $file, int $size, int $user): bool
    {
        $this->db->query('INSERT INTO backup_histories(type,file_name,file_size,created_by) VALUES(:t,:f,:s,:u)');
        $this->db->bind(':t', $type);
        $this->db->bind(':f', $file);
        $this->db->bind(':s', $size, PDO::PARAM_INT);
        $this->db->bind(':u', $user, PDO::PARAM_INT);

        return $this->db->execute();
    }

    public function all(): array
    {
        $this->db->query('SELECT b.*,u.ten creator FROM backup_histories b LEFT JOIN nguoi_dung u ON u.id=b.created_by ORDER BY b.id DESC LIMIT 100');

        return $this->db->resultSet();
    }

    public function find(int $id): mixed
    {
        $this->db->query('SELECT * FROM backup_histories WHERE id=:id');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->single();
    }

    public function delete(int $id): bool
    {
        $this->db->query('DELETE FROM backup_histories WHERE id=:id');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }
}
