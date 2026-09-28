<?php
/**
 * Model ViDienTu - Quản lý ví điện tử, nạp tiền và giao dịch.
 * Bang CSDL: nap_tien (yeu cau nap), chi_tieu (lich su chi tieu)
 *
 * Trang thai nap tien:
 *   'cho_duyet' = Dang cho Admin duyet
 *   'da_duyet'  = Da duoc duyet, da cong vao vi
 *   'tu_choi'   = Bi tu choi
 */
class ViDienTu extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'nap_tien';
    }

    // ==========================================
    // YÊU CẦU NẠP TIỀN
    // ==========================================

    /**
     * Tao yeu cau nap tien moi (trang thai mac dinh: cho_duyet).
     *
     * @param  array $data ['ma_nguoi_dung', 'so_tien', 'tong_cong', 'phuong_thuc', 'ma_giao_dich', 'trang_thai', 'ghi_chu']
     * @return bool        True neu tao thanh cong
     */
    public function taoYeuCauNap(array $data): bool
    {
        $this->db->query("INSERT INTO nap_tien (ma_nguoi_dung, so_tien, tong_cong, phuong_thuc, ma_giao_dich, trang_thai, ghi_chu)
                          VALUES (:ma_nguoi_dung, :so_tien, :tong_cong, :phuong_thuc, :ma_giao_dich, :trang_thai, :ghi_chu)");
        $this->db->bind(':ma_nguoi_dung', $data['ma_nguoi_dung']);
        $this->db->bind(':so_tien',       $data['so_tien']);
        $this->db->bind(':tong_cong',     $data['tong_cong']);
        $this->db->bind(':phuong_thuc',   $data['phuong_thuc']);
        $this->db->bind(':ma_giao_dich',  $data['ma_giao_dich']);
        $this->db->bind(':trang_thai',    $data['trang_thai'] ?? 'cho_duyet');
        $this->db->bind(':ghi_chu',       $data['ghi_chu'] ?? null);
        return $this->db->execute();
    }

    /**
     * Tạo yêu cầu nạp tiền mới và trả về ID tự sinh (dành cho PayOS)
     */
    public function taoYeuCauNapTraVeId(array $data): int|false
    {
        $this->db->query("INSERT INTO nap_tien (ma_nguoi_dung, so_tien, tong_cong, phuong_thuc, ma_giao_dich, trang_thai, ghi_chu)
                          VALUES (:ma_nguoi_dung, :so_tien, :tong_cong, :phuong_thuc, :ma_giao_dich, :trang_thai, :ghi_chu)");
        $this->db->bind(':ma_nguoi_dung', $data['ma_nguoi_dung']);
        $this->db->bind(':so_tien',       $data['so_tien']);
        $this->db->bind(':tong_cong',     $data['tong_cong']);
        $this->db->bind(':phuong_thuc',   $data['phuong_thuc']);
        $this->db->bind(':ma_giao_dich',  $data['ma_giao_dich']);
        $this->db->bind(':trang_thai',    $data['trang_thai'] ?? 'cho_duyet');
        $this->db->bind(':ghi_chu',       $data['ghi_chu'] ?? null);
        if ($this->db->execute()) {
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Lay tat ca yeu cau nap tien dang cho Admin duyet.
     *
     * @return array Danh sach yeu cau cho duyet
     */
    public function layChoNap(): array
    {
        $this->db->query("SELECT n.*, u.ten AS ten_nguoi_dung, u.email
                          FROM nap_tien n
                          JOIN nguoi_dung u ON n.ma_nguoi_dung = u.id
                          WHERE n.trang_thai = 'cho_duyet'
                            AND n.phuong_thuc <> 'payos'
                          ORDER BY n.ngay_tao DESC");
        return $this->db->resultSet();
    }

    /**
     * Lay chi tiet mot yeu cau nap tien theo ID.
     *
     * @param  int           $id ID yeu cau
     * @return object|false      Thong tin yeu cau hoac false
     */
    public function layYeuCauTheoId(int $id): object|false
    {
        $this->db->query("SELECT * FROM nap_tien WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Cap nhat trang thai yeu cau nap tien (duyet hoac tu choi).
     *
     * @param  int    $id       ID yeu cau
     * @param  string $trangThai 'da_duyet' hoac 'tu_choi'
     * @return bool             True neu cap nhat thanh cong
     */
    public function capNhatTrangThaiNap(int $id, string $trangThai): bool
    {
        $this->db->query("UPDATE nap_tien SET trang_thai = :trang_thai WHERE id = :id");
        $this->db->bind(':trang_thai', $trangThai);
        $this->db->bind(':id',         $id);
        return $this->db->execute();
    }

    /** Chuyen trang thai theo kieu compare-and-set de tranh xu ly trung giao dich. */
    public function danhDauDaDuyetNeuDangCho(int $id): bool
    {
        $this->db->query("UPDATE nap_tien
                          SET trang_thai = 'da_duyet'
                          WHERE id = :id AND trang_thai = 'cho_duyet'");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        if (!$this->db->execute()) {
            return false;
        }
        return $this->db->rowCount() === 1;
    }

    // ==========================================
    // QUẢN LÝ SỐ DƯ
    // ==========================================

    /**
     * Cong tien vao so du cua nguoi dung.
     *
     * @param  int  $userId ID nguoi dung
     * @param  int  $soTien So tien can cong (don vi: VND)
     * @return bool         True neu cap nhat thanh cong
     */
    public function congSoDu(int $userId, int $soTien): bool
    {
        $this->db->query("UPDATE nguoi_dung SET so_du = so_du + :so_tien WHERE id = :id");
        $this->db->bind(':so_tien', $soTien);
        $this->db->bind(':id',      $userId);
        return $this->db->execute();
    }

    /**
     * Tru tien khoi so du cua nguoi dung (khi mua VIP / UP tin).
     * Su dung Atomic Update (AND so_du >= :so_tien) de chong Race Condition (Double-Spending).
     *
     * @param  int  $userId ID nguoi dung
     * @param  int  $soTien So tien can tru
     * @return bool         True neu tru thanh cong (du so du), False neu khong du
     */
    public function truSoDu(int $userId, int $soTien): bool
    {
        $this->db->query("UPDATE nguoi_dung SET so_du = so_du - :debit WHERE id = :id AND so_du >= :minimum_balance");
        $this->db->bind(':debit', $soTien);
        $this->db->bind(':minimum_balance', $soTien);
        $this->db->bind(':id',      $userId);
        $this->db->execute();
        
        // Neu rowCount() > 0 tuc la da tru thanh cong (so du duoc bao dam)
        return $this->db->rowCount() > 0;
    }

    // ==========================================
    // LỊCH SỬ GIAO DỊCH
    // ==========================================

    /**
     * Tao ban ghi chi tieu (ghi nhat ky khi mua VIP / UP / thuong).
     *
     * @param  array $data ['ma_nguoi_dung', 'loai', 'mo_ta', 'so_tien', 'ma_du_an'(tuy chon)]
     * @return bool        True neu tao thanh cong
     */
    public function ghiChiTieu(array $data): bool
    {
        $this->db->query("INSERT INTO chi_tieu (ma_nguoi_dung, ma_du_an, loai, mo_ta, so_tien)
                          VALUES (:ma_nguoi_dung, :ma_du_an, :loai, :mo_ta, :so_tien)");
        $this->db->bind(':ma_nguoi_dung', $data['ma_nguoi_dung']);
        $this->db->bind(':ma_du_an',      $data['ma_du_an'] ?? null);
        $this->db->bind(':loai',          $data['loai']);
        $this->db->bind(':mo_ta',         $data['mo_ta']);
        $this->db->bind(':so_tien',       $data['so_tien']);
        return $this->db->execute();
    }

    /**
     * Lay lich su giao dich cua nguoi dung (ca nap tien lan chi tieu).
     * Su dung UNION ALL de gop 2 bang lai voi nhau.
     *
     * @param  int   $userId ID nguoi dung
     * @return array         Danh sach giao dich sap xep moi nhat truoc
     */
    public function layLichSu(int $userId): array
    {
        $this->db->query("SELECT id, so_tien AS amount, 'nap_tien' AS type, phuong_thuc AS method, trang_thai AS status, ngay_tao
                          FROM nap_tien WHERE ma_nguoi_dung = :deposit_uid
                          UNION ALL
                          SELECT id, so_tien AS amount, loai AS type, mo_ta AS method, 'thanh_cong' AS status, ngay_tao
                          FROM chi_tieu WHERE ma_nguoi_dung = :expense_uid
                          ORDER BY ngay_tao DESC");
        $this->db->bind(':deposit_uid', $userId, PDO::PARAM_INT);
        $this->db->bind(':expense_uid', $userId, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Kiem tra nguoi dung co da nhan thuong chia se hom nay chua.
     * Moi nguoi chi duoc nhan thuong 1 lan/ngay.
     *
     * @param  int  $userId ID nguoi dung
     * @return bool         True neu da nhan roi, False neu chua
     */
    public function daNhanThuongHomNay(int $userId): bool
    {
        $this->db->query("SELECT id FROM chi_tieu 
                          WHERE ma_nguoi_dung = :uid 
                            AND loai = 'thuong_chia_se' 
                            AND DATE(ngay_tao) = CURDATE()");
        $this->db->bind(':uid', $userId);
        return (bool)$this->db->single();
    }

    // Alias tuong thich nguoc
    public function createDeposit(array $data): bool                    { return $this->taoYeuCauNap($data); }
    public function getPendingDeposits(): array                          { return $this->layChoNap(); }
    public function getDepositById(int $id): mixed                      { return $this->layYeuCauTheoId($id); }
    public function updateDepositStatus(int $id, string $status): bool  { return $this->capNhatTrangThaiNap($id, $status); }
    public function addBalance(int $uid, int $amount): bool             { return $this->congSoDu($uid, $amount); }
    public function subtractBalance(int $uid, int $amount): bool        { return $this->truSoDu($uid, $amount); }
    public function createTransaction(array $data): bool                { return $this->ghiChiTieu($data); }
    public function getUserHistory(int $uid): array                     { return $this->layLichSu($uid); }
    public function checkShareRewardToday(int $uid): bool               { return $this->daNhanThuongHomNay($uid); }
}
