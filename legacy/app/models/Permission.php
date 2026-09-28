<?php
/**
 * Model Permission – Ánh xạ tới bảng quyền `quyen_nhat_ky_he_thong`.
 * Tuân thủ SOLID, Model Layer.
 */
class Permission extends Model
{
    protected string $table = 'quyen_nhat_ky_he_thong';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Lấy danh sách quyền theo role ID.
     */
    public function findByRoleId(int $roleId): array
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE role_id = :role_id ORDER BY id ASC");
        $this->db->bind(':role_id', $roleId, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Kiểm tra role có quyền cụ thể.
     */
    public function hasPermission(int $roleId, string $permission): bool
    {
        $this->db->query("SELECT id FROM {$this->table} WHERE role_id = :role_id AND permission = :perm LIMIT 1");
        $this->db->bind(':role_id', $roleId, PDO::PARAM_INT);
        $this->db->bind(':perm', $permission);
        return $this->db->single() !== false;
    }
}
