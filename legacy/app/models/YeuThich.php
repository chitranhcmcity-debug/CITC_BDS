<?php
/**
 * Model YeuThich - Quản lý danh sách bất động sản đã lưu của người dùng.
 * Bảng CSDL: yeu_thich
 *
 * Mỗi bản ghi là cặp (ma_nguoi_dung, ma_du_an) – quan hệ nhiều-nhiều.
 */
class YeuThich extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'yeu_thich';
    }

    // ==========================================
    // KIỂM TRA & LƯU TIN
    // ==========================================

    /**
     * Kiểm tra người dùng đã lưu tin BĐS này chưa.
     *
     * @param  int  $maNguoiDung ID người dùng
     * @param  int  $maDuAn      ID dự án / tin BĐS
     * @return bool              True nếu đã lưu
     */
    public function kiemTraDaLuu(int $maNguoiDung, int $maDuAn): bool
    {
        $this->db->query("SELECT 1 FROM yeu_thich WHERE ma_nguoi_dung = :ma_nguoi_dung AND ma_du_an = :ma_du_an");
        $this->db->bind(':ma_nguoi_dung', $maNguoiDung);
        $this->db->bind(':ma_du_an', $maDuAn);
        return (bool)$this->db->single();
    }

    /**
     * Lưu tin BĐS vào danh sách yêu thích.
     *
     * @param  int  $maNguoiDung ID người dùng
     * @param  int  $maDuAn      ID dự án / tin BĐS
     * @return bool              True nếu lưu thành công
     */
    public function luuTin(int $maNguoiDung, int $maDuAn): bool
    {
        $this->db->query("INSERT INTO yeu_thich (ma_nguoi_dung, ma_du_an) VALUES (:ma_nguoi_dung, :ma_du_an)");
        $this->db->bind(':ma_nguoi_dung', $maNguoiDung);
        $this->db->bind(':ma_du_an', $maDuAn);
        return $this->db->execute();
    }

    /**
     * Bỏ lưu tin BĐS khỏi danh sách yêu thích.
     *
     * @param  int  $maNguoiDung ID người dùng
     * @param  int  $maDuAn      ID dự án / tin BĐS
     * @return bool              True nếu xóa thành công
     */
    public function boLuuTin(int $maNguoiDung, int $maDuAn): bool
    {
        $this->db->query("DELETE FROM yeu_thich WHERE ma_nguoi_dung = :ma_nguoi_dung AND ma_du_an = :ma_du_an");
        $this->db->bind(':ma_nguoi_dung', $maNguoiDung);
        $this->db->bind(':ma_du_an', $maDuAn);
        return $this->db->execute();
    }

    // ==========================================
    // LẤY DANH SÁCH ĐÃ LƯU
    // ==========================================

    /**
     * Lấy toàn bộ tin BĐS mà người dùng đã lưu (kèm thông tin tin đăng).
     *
     * @param  int   $maNguoiDung ID người dùng
     * @return array              Danh sách tin BĐS đã lưu, mới nhất trước
     */
    public function layDanhSachDaLuu(int $maNguoiDung): array
    {
        $this->db->query("SELECT p.*, c.ten AS ten_danh_muc, u.ten AS ten_nguoi_dung, y.ngay_tao as ngay_luu
                          FROM yeu_thich y
                          JOIN du_an p ON y.ma_du_an = p.id
                          JOIN danh_muc c ON p.ma_danh_muc = c.id
                          LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
                          WHERE y.ma_nguoi_dung = :ma_nguoi_dung
                          ORDER BY y.ngay_tao DESC");
        $this->db->bind(':ma_nguoi_dung', $maNguoiDung);
        return $this->db->resultSet();
    }

    public function demTheoNguoiDung(int $maNguoiDung): int
    {
        $this->db->query("SELECT COUNT(*) AS total FROM yeu_thich WHERE ma_nguoi_dung = :uid");
        $this->db->bind(':uid', $maNguoiDung, PDO::PARAM_INT);
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }
}
