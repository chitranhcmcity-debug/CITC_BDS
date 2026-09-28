<?php

namespace App\Repositories;

use App\Models\Database;
use PDO;

class RbacRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function roles(): array
    {
        $this->db->query('SELECT r.*,(SELECT COUNT(*) FROM nguoi_dung u WHERE u.ma_vai_tro=r.id) user_count,(SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id=r.id) permission_count FROM vai_tro r ORDER BY r.id');

        return $this->db->resultSet();
    }

    public function role(int $id): mixed
    {
        $this->db->query('SELECT * FROM vai_tro WHERE id=:id');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->single();
    }

    public function permissions(): array
    {
        $this->db->query('SELECT * FROM permissions ORDER BY module,action');

        return $this->db->resultSet();
    }

    public function permissionIds(int $role): array
    {
        $this->db->query('SELECT permission_id FROM role_permissions WHERE role_id=:r');
        $this->db->bind(':r', $role, PDO::PARAM_INT);

        return array_map('intval', array_column(array_map(fn ($x) => (array) $x, $this->db->resultSet()), 'permission_id'));
    }

    public function has(int $role, string $code): bool
    {
        if ($role === 1) {
            return true;
        }$this->db->query('SELECT 1 FROM role_permissions rp JOIN permissions p ON p.id=rp.permission_id WHERE rp.role_id=:r AND p.code=:c LIMIT 1');
        $this->db->bind(':r', $role, PDO::PARAM_INT);
        $this->db->bind(':c', $code);

        return (bool) $this->db->single();
    }

    public function createRole(array $d): int
    {
        $this->db->query('INSERT INTO vai_tro(ten,mo_ta,slug,is_system) VALUES(:n,:d,:s,0)');
        $this->db->bind(':n', $d['name']);
        $this->db->bind(':d', $d['description']);
        $this->db->bind(':s', $d['slug']);

        return $this->db->execute() ? (int) $this->db->lastInsertId() : 0;
    }

    public function updateRole(int $id, array $d): bool
    {
        $this->db->query('UPDATE vai_tro SET ten=:n,mo_ta=:d WHERE id=:id');
        $this->db->bind(':n', $d['name']);
        $this->db->bind(':d', $d['description']);
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    public function deleteRole(int $id): bool
    {
        $this->db->query('DELETE FROM vai_tro WHERE id=:id AND is_system=0 AND NOT EXISTS(SELECT 1 FROM nguoi_dung WHERE ma_vai_tro=:used)');
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $this->db->bind(':used', $id, PDO::PARAM_INT);

        return $this->db->execute() && $this->db->rowCount() > 0;
    }

    public function syncPermissions(int $role, array $ids): bool
    {
        $this->db->query('DELETE FROM role_permissions WHERE role_id=:r');
        $this->db->bind(':r', $role, PDO::PARAM_INT);
        if (! $this->db->execute()) {
            return false;
        }foreach ($ids as $id) {
            $this->db->query('INSERT IGNORE INTO role_permissions(role_id,permission_id) VALUES(:r,:p)');
            $this->db->bind(':r', $role, PDO::PARAM_INT);
            $this->db->bind(':p', $id, PDO::PARAM_INT);
            if (! $this->db->execute()) {
                return false;
            }
        }

        return true;
    }

    public function assignRole(int $user, int $role): bool
    {
        $this->db->query('UPDATE nguoi_dung SET ma_vai_tro=:r WHERE id=:u');
        $this->db->bind(':r', $role, PDO::PARAM_INT);
        $this->db->bind(':u', $user, PDO::PARAM_INT);
        if (! $this->db->execute() || $this->db->rowCount() < 1) {
            return false;
        }$this->db->query('DELETE FROM user_roles WHERE user_id=:u');
        $this->db->bind(':u', $user, PDO::PARAM_INT);
        $this->db->execute();
        $this->db->query('INSERT INTO user_roles(user_id,role_id) VALUES(:u,:r)');
        $this->db->bind(':u', $user, PDO::PARAM_INT);
        $this->db->bind(':r', $role, PDO::PARAM_INT);

        return $this->db->execute();
    }
}
