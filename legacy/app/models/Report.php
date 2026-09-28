<?php
/**
 * Model Report – Ánh xạ tới báo cáo vi phạm trong bảng `bao_cao_vi_pham`.
 * Tuân thủ SOLID, Model Layer.
 */
class Report extends Model
{
    protected string $table = 'bao_cao_vi_pham';

    /** @var array Trạng thái báo cáo hợp lệ */
    public const STATUSES = ['cho_xu_ly', 'da_xu_ly', 'da_huy'];

    /** @var array Lý do báo cáo hợp lệ */
    public const REASONS = [
        'thong_tin_sai'  => 'Thông tin không chính xác',
        'hinh_anh_sai'   => 'Hình ảnh sai / không liên quan',
        'gia_sai'        => 'Giá hiển thị sai',
        'lua_dao'        => 'Nghi ngờ lừa đảo',
        'tin_trung_lap'  => 'Tin đăng trùng lặp',
        'khac'           => 'Lý do khác',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Đếm báo cáo chờ xử lý.
     */
    public function countPending(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE trang_thai = 'cho_xu_ly'");
        return (int)$this->db->single()->total;
    }
}
