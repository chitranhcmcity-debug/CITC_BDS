<?php
/**
 * Model BaoCao - Quản lý các truy vấn thống kê dữ liệu báo cáo kinh doanh.
 * Không ánh xạ trực tiếp tới một bảng đơn lẻ mà tổng hợp thông tin từ nhiều bảng.
 */
class BaoCao extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Đếm tổng số người dùng hệ thống ngoại trừ Admin.
     */
    public function demNguoiDung(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM nguoi_dung WHERE ma_vai_tro != 1");
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    /**
     * Đếm số người dùng đăng ký mới trong tháng này (ngoại trừ Admin).
     */
    public function demNguoiDungMoiThangNay(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM nguoi_dung
                          WHERE MONTH(ngay_tao) = MONTH(NOW()) AND YEAR(ngay_tao) = YEAR(NOW())
                          AND ma_vai_tro != 1");
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    /**
     * Đếm tổng số tin đăng trên hệ thống.
     */
    public function demTongTinDang(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM du_an");
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    /**
     * Đếm số tin đăng đang hoạt động (đã xuất bản).
     */
    public function demTinDangHoatDong(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM du_an WHERE trang_thai = 'xuat_ban'");
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    /**
     * Đếm số tin đăng chờ phê duyệt (ở trạng thái nháp).
     */
    public function demTinDangChoDuyet(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM du_an WHERE trang_thai = 'nhap'");
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    /**
     * Tổng số lượt xem tin đăng.
     */
    public function tongLuotXemTin(): int
    {
        $this->db->query("SELECT SUM(luot_xem) as total FROM du_an");
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    /**
     * Đếm tổng số khách hàng tiềm năng gửi liên hệ.
     */
    public function demTongKhachHang(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM khach_hang");
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    /**
     * Đếm số lượng khách hàng mới phát sinh trong tháng này.
     */
    public function demKhachHangMoiThangNay(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM khach_hang
                          WHERE MONTH(ngay_tao) = MONTH(NOW()) AND YEAR(ngay_tao) = YEAR(NOW())");
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    /**
     * Đếm số lượng giao dịch/lead chốt thành công.
     */
    public function demKhachHangThanhCong(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM khach_hang WHERE trang_thai = 'thanh_cong'");
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    /**
     * Lấy thống kê số lượng tin đăng phát sinh và lượt xem theo từng tháng (6 tháng gần nhất).
     */
    public function layThongKeTinTheoThang(int $months = 6): array
    {
        $this->db->query("SELECT
            DATE_FORMAT(ngay_tao, '%m/%Y') as thang,
            DATE_FORMAT(ngay_tao, '%Y-%m') as thang_sort,
            COUNT(*) as tong_tin,
            SUM(CASE WHEN trang_thai = 'xuat_ban' THEN 1 ELSE 0 END) as da_duyet,
            SUM(CASE WHEN trang_thai = 'nhap' THEN 1 ELSE 0 END) as cho_duyet,
            SUM(luot_xem) as luot_xem
            FROM du_an
            WHERE ngay_tao >= DATE_SUB(NOW(), INTERVAL :months MONTH)
            GROUP BY DATE_FORMAT(ngay_tao, '%Y-%m'), DATE_FORMAT(ngay_tao, '%m/%Y')
            ORDER BY thang_sort DESC");
        $this->db->bind(':months', $months);
        return $this->db->resultSet();
    }

    /**
     * Thống kê số lượng khách hàng phân bổ theo các trạng thái CRM.
     */
    public function layKhachHangTheoTrangThai(): array
    {
        $this->db->query("SELECT trang_thai, COUNT(*) as so_luong FROM khach_hang GROUP BY trang_thai");
        return $this->db->resultSet();
    }

    /**
     * Lấy Top tin đăng có số lượng lượt xem nhiều nhất.
     */
    public function layTopTinXemNhieu(int $limit = 5): array
    {
        $this->db->query("SELECT tieu_de, luot_xem, trang_thai, vi_tri FROM du_an ORDER BY luot_xem DESC LIMIT :limit");
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}
