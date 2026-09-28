<?php
/**
 * Model KhachHang - Quản lý khách hàng tiềm năng liên hệ (leads).
 * Bang CSDL: khach_hang
 */
class KhachHang extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'khach_hang';

    }

    /**
     * Tao moi khach hang (lead) khi nguoi dung gui form lien he.
     *
     * @param  array $data ['name', 'email', 'phone', 'message', 'project_id']
     * @return bool        True neu tao thanh cong
     */
    public function taoLead(array $data): bool
    {
        $this->db->query("INSERT INTO khach_hang (ten, email, dien_thoai, tin_nhan, ma_du_an) 
                          VALUES (:ten, :email, :dien_thoai, :tin_nhan, :ma_du_an)");
        $this->db->bind(':ten',        $data['name']);
        $this->db->bind(':email',      $data['email']);
        $this->db->bind(':dien_thoai', $data['phone']);
        $this->db->bind(':tin_nhan',   $data['message']);
        $this->db->bind(':ma_du_an',   $data['project_id']);
        return $this->db->execute();
    }

    /** @deprecated Su dung taoLead() thay the */
    public function create(array $data): bool
    {
        return $this->taoLead($data);
    }

    /**
     * Lay danh sach tat ca khach hang (leads) kem theo thong tin du an va nguoi phu trach.
     *
     * @return array Danh sach khach hang
     */
    public function layTatCaLeads(): array
    {
        $this->db->query("SELECT kh.*, da.tieu_de AS tieu_de_du_an, da.duong_dan AS duong_dan_du_an, nd.ten AS ten_nguoi_phu_trach
                          FROM khach_hang kh
                          LEFT JOIN du_an da ON kh.ma_du_an = da.id
                          LEFT JOIN nguoi_dung nd ON kh.nguoi_phu_trach = nd.id
                          ORDER BY kh.ngay_tao DESC");
        return $this->db->resultSet();
    }

    /**
     * Lay thong tin khach hang theo ID.
     *
     * @param  int   $id ID khach hang
     * @return mixed     Doi tuong khach hang hoac false
     */
    public function layTheoId(int $id): mixed
    {
        $this->db->query("SELECT kh.*, da.tieu_de AS tieu_de_du_an, da.duong_dan AS duong_dan_du_an, nd.ten AS ten_nguoi_phu_trach
                          FROM khach_hang kh
                          LEFT JOIN du_an da ON kh.ma_du_an = da.id
                          LEFT JOIN nguoi_dung nd ON kh.nguoi_phu_trach = nd.id
                          WHERE kh.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Cap nhat thong tin khach hang (trang thai, nguoi phu trach, ghi chu, giai quyet, bao cao).
     *
     * @param  array $data ['id', 'trang_thai', 'nguoi_phu_trach', 'ghi_chu', 'da_giai_quyet', 'bao_cao_giai_quyet']
     * @return bool        True neu thanh cong
     */
    public function capNhatLead(array $data): bool
    {
        $this->db->query("UPDATE khach_hang SET 
                          trang_thai = :trang_thai, 
                          nguoi_phu_trach = :nguoi_phu_trach, 
                          ghi_chu = :ghi_chu,
                          da_giai_quyet = :da_giai_quyet,
                          bao_cao_giai_quyet = :bao_cao_giai_quyet
                          WHERE id = :id");
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':trang_thai', $data['trang_thai']);
        $this->db->bind(':nguoi_phu_trach', !empty($data['nguoi_phu_trach']) ? (int)$data['nguoi_phu_trach'] : null);
        $this->db->bind(':ghi_chu', $data['ghi_chu']);
        $this->db->bind(':da_giai_quyet', $data['da_giai_quyet']);
        $this->db->bind(':bao_cao_giai_quyet', $data['bao_cao_giai_quyet']);
        return $this->db->execute();
    }

    /**
     * Cap nhat rieng biet trang thai da giai quyet cua khach hang (tieu bieu cho AJAX toggle).
     *
     * @param  int  $id           ID khach hang
     * @param  int  $daGiaiQuyet  Trang thai (0 hoac 1)
     * @return bool               True neu cap nhat thanh cong
     */
    public function capNhatGiaiQuyet(int $id, int $daGiaiQuyet): bool
    {
        $this->db->query("UPDATE khach_hang SET da_giai_quyet = :da_giai_quyet WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':da_giai_quyet', $daGiaiQuyet);
        return $this->db->execute();
    }

    /**
     * Xoa khach hang theo ID.
     *
     * @param  int  $id ID khach hang
     * @return bool     True neu thanh cong
     */
    public function xoaLead(int $id): bool
    {
        $this->db->query("DELETE FROM khach_hang WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Lay thong ke nhanh so luong leads theo trang thai.
     *
     * @return array
     */
    public function layThongKe(): array
    {
        $this->db->query("SELECT 
                            COUNT(*) AS tong,
                            SUM(CASE WHEN trang_thai = 'moi' THEN 1 ELSE 0 END) AS moi,
                            SUM(CASE WHEN trang_thai IN ('da_lien_he', 'tiem_nang') THEN 1 ELSE 0 END) AS dang_cham_soc,
                            SUM(CASE WHEN trang_thai = 'thanh_cong' THEN 1 ELSE 0 END) AS thanh_cong,
                            SUM(CASE WHEN tin_nhan LIKE '%[ĐẶT LỊCH HẸN TƯ VẤN]%' THEN 1 ELSE 0 END) AS dat_lich,
                            SUM(CASE WHEN tin_nhan NOT LIKE '%[ĐẶT LỊCH HẸN TƯ VẤN]%' OR tin_nhan IS NULL THEN 1 ELSE 0 END) AS tuong_tac
                          FROM khach_hang");
        $stats = $this->db->single();
        return [
            'tong' => (int)($stats->tong ?? 0),
            'moi' => (int)($stats->moi ?? 0),
            'dang_cham_soc' => (int)($stats->dang_cham_soc ?? 0),
            'thanh_cong' => (int)($stats->thanh_cong ?? 0),
            'dat_lich' => (int)($stats->dat_lich ?? 0),
            'tuong_tac' => (int)($stats->tuong_tac ?? 0),
        ];
    }
}
