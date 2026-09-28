<?php
/**
 * Model Post – Ánh xạ tới tin đăng bất động sản trong bảng `du_an`.
 * Cung cấp CRUD cơ bản và các trạng thái tin đăng.
 * Tuân thủ SOLID, Model Layer.
 */
class Post extends Model
{
    protected string $table = 'du_an';

    /** @var array Trạng thái tin đăng hợp lệ */
    public const STATUSES = ['cho_duyet', 'da_duyet', 'tu_choi', 'het_han', 'da_an', 'da_ban'];

    /** @var array Loại VIP hợp lệ */
    public const VIP_TYPES = ['thuong', 'vip1', 'vip2', 'vip3'];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Đếm tổng tin đăng của người dùng.
     */
    public function countByUser(int $userId): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE ma_nguoi_dung = :uid");
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);
        return (int)$this->db->single()->total;
    }

    /**
     * Kiểm tra trạng thái hợp lệ.
     */
    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::STATUSES, true);
    }
}
