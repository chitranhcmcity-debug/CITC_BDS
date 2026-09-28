<?php
/**
 * Model Role – Ánh xạ tới vai trò người dùng trong bảng `vai_tro`.
 * Tuân thủ SOLID, Model Layer.
 */
class Role extends Model
{
    protected string $table = 'vai_tro';

    /** @var int Mã vai trò Quản trị viên */
    public const ADMIN = 1;

    /** @var int Mã vai trò Biên tập viên */
    public const EDITOR = 2;

    /** @var int Mã vai trò Thành viên thường */
    public const MEMBER = 3;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Lấy tất cả vai trò trong hệ thống.
     */
    public function all(): array
    {
        $this->db->query("SELECT * FROM {$this->table} ORDER BY id ASC");
        return $this->db->resultSet();
    }

    /**
     * Tìm vai trò theo tên.
     */
    public function findByName(string $name): object|false
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE ten = :name LIMIT 1");
        $this->db->bind(':name', $name);
        return $this->db->single();
    }
}
