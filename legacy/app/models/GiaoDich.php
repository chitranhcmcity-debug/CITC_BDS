<?php
/**
 * Model GiaoDich - Báo cáo doanh thu và chi tiêu hệ thống.
 * Làm việc với 2 bảng: nap_tien (doanh thu), chi_tieu (chi phí VIP/UP).
 *
 * Dùng chủ yếu cho trang Báo Cáo của Admin.
 */
class GiaoDich extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'nap_tien';
    }

    // ==========================================
    // DOANH THU
    // ==========================================

    /**
     * Lấy doanh thu nạp tiền theo tháng (các giao dịch đã duyệt).
     *
     * @param  int   $months Số tháng cần lấy (tính từ hiện tại trở về)
     * @return array         Danh sách doanh thu theo tháng
     */
    public function getMonthlyRevenue(int $months = 6): array
    {
        $this->db->query("SELECT
            DATE_FORMAT(ngay_tao, '%m/%Y') as thang,
            DATE_FORMAT(ngay_tao, '%Y-%m') as thang_sort,
            COUNT(*) as so_giao_dich,
            SUM(so_tien) as tong_nap,
            SUM(tong_cong - so_tien) as tong_khuyen_mai,
            SUM(tong_cong) as tong_vao_tai_khoan
            FROM nap_tien
            WHERE trang_thai = 'da_duyet'
            AND ngay_tao >= DATE_SUB(NOW(), INTERVAL :months MONTH)
            GROUP BY DATE_FORMAT(ngay_tao, '%Y-%m'), DATE_FORMAT(ngay_tao, '%m/%Y')
            ORDER BY thang_sort DESC");
        $this->db->bind(':months', $months, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Lấy chi tiêu (mua VIP / UP) theo tháng.
     *
     * @param  int   $months Số tháng cần lấy
     * @return array         Danh sách chi tiêu theo tháng
     */
    public function getMonthlySpending(int $months = 6): array
    {
        $this->db->query("SELECT
            DATE_FORMAT(ngay_tao, '%m/%Y') as thang,
            DATE_FORMAT(ngay_tao, '%Y-%m') as thang_sort,
            SUM(CASE WHEN loai = 'mua_vip' THEN so_tien ELSE 0 END) as chi_vip,
            SUM(CASE WHEN loai = 'mua_up'  THEN so_tien ELSE 0 END) as chi_up,
            SUM(so_tien) as tong_chi
            FROM chi_tieu
            WHERE ngay_tao >= DATE_SUB(NOW(), INTERVAL :months MONTH)
            GROUP BY DATE_FORMAT(ngay_tao, '%Y-%m'), DATE_FORMAT(ngay_tao, '%m/%Y')
            ORDER BY thang_sort DESC");
        $this->db->bind(':months', $months, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Lấy tổng doanh thu ròng đã duyệt (đã trừ khuyến mãi và thuế 10%).
     *
     * @return float|int Tổng doanh thu ròng
     */
    public function getTotalRevenue(): float|int
    {
        $this->db->query("SELECT SUM(so_tien - (tong_cong - so_tien) - (so_tien * 0.1)) as total
                          FROM nap_tien WHERE trang_thai = 'da_duyet'");
        $row = $this->db->single();
        return $row->total ?? 0;
    }

    /**
     * Lấy doanh thu ròng tháng hiện tại.
     *
     * @return float|int Doanh thu ròng tháng này
     */
    public function getRevenueThisMonth(): float|int
    {
        $this->db->query("SELECT SUM(so_tien - (tong_cong - so_tien) - (so_tien * 0.1)) as total
                          FROM nap_tien
                          WHERE trang_thai = 'da_duyet'
                          AND MONTH(ngay_tao) = MONTH(NOW())
                          AND YEAR(ngay_tao)  = YEAR(NOW())");
        $row = $this->db->single();
        return $row->total ?? 0;
    }

    // ==========================================
    // CHI TIÊU & THỐNG KÊ
    // ==========================================

    /**
     * Đếm số giao dịch nạp tiền đang chờ duyệt.
     *
     * @return int Số giao dịch chờ duyệt
     */
    public function countPending(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM nap_tien WHERE trang_thai = 'cho_duyet'");
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    /**
     * Lấy tổng chi tiêu toàn bộ (mua VIP + mua UP).
     *
     * @return float|int Tổng chi tiêu
     */
    public function getTotalSpending(): float|int
    {
        $this->db->query("SELECT SUM(so_tien) as total FROM chi_tieu");
        $row = $this->db->single();
        return $row->total ?? 0;
    }

    /**
     * Lấy danh sách giao dịch nạp tiền gần nhất (kèm thông tin người dùng).
     *
     * @param  int   $limit Số lượng giao dịch cần lấy
     * @return array        Danh sách giao dịch gần nhất
     */
    public function getRecentTransactions(int $limit = 10): array
    {
        $this->db->query("SELECT n.*, u.ten as ten_nguoi_dung, u.email
                          FROM nap_tien n
                          JOIN nguoi_dung u ON n.ma_nguoi_dung = u.id
                          ORDER BY n.ngay_tao DESC
                          LIMIT :limit");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
}
