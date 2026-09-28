<?php
/**
 * Model NguoiDung - Quản lý người dùng hệ thống.
 * Bang CSDL: nguoi_dung
 *
 * Vai tro (ma_vai_tro):
 *   1 = Admin (quan tri vien)
 *   2 = Client (nguoi dung thuong)
 */
class NguoiDung extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'nguoi_dung';
    }

    // ==========================================
    // XÁC THỰC ĐĂNG NHẬP
    // ==========================================

    /**
     * Tim nguoi dung theo dia chi email.
     *
     * @param  string $email Email can tim
     * @return mixed         Doi tuong nguoi dung hoac false neu khong tim thay
     */
    public function timTheoEmail(string $email): mixed
    {
        $this->db->query("SELECT * FROM nguoi_dung WHERE email = :email");
        $this->db->bind(':email', $email);
        $row = $this->db->single();
        return $this->db->rowCount() > 0 ? $row : false;
    }

    /**
     * Xac thuc dang nhap bang email va mat khau.
     * Su dung password_verify() de kiem tra mat khau da ma hoa bcrypt.
     *
     * @param  string $email    Email nguoi dung
     * @param  string $matKhau  Mat khau nguyen ban
     * @return mixed            Doi tuong nguoi dung neu dung, false neu sai
     */
    public function dangNhap(string $email, string $matKhau): mixed
    {
        $nguoiDung = $this->timTheoEmail($email);
        if (!$nguoiDung) {
            return false;
        }
        return password_verify($matKhau, $nguoiDung->mat_khau) ? $nguoiDung : false;
    }

    /**
     * Dang ky tai khoan moi (role_id = 2 - Client mac dinh).
     *
     * @param  array $data ['name', 'email', 'password' (da hash), 'phone', 'role_id']
     * @return bool        True neu dang ky thanh cong
     */
    public function dangKy(array $data): bool
    {
        $this->db->query("INSERT INTO nguoi_dung (ma_vai_tro, ten, email, mat_khau, dien_thoai, trang_thai)
                          VALUES (:role_id, :ten, :email, :mat_khau, :dien_thoai, 'hoat_dong')");
        $this->db->bind(':role_id',    $data['role_id']);
        $this->db->bind(':ten',        $data['name']);
        $this->db->bind(':email',      $data['email']);
        $this->db->bind(':mat_khau',   $data['password']);
        $this->db->bind(':dien_thoai', $data['phone']);
        return $this->db->execute();
    }

    // ==========================================
    // QUẢN LÝ NGƯỜI DÙNG (ADMIN)
    // ==========================================

    /**
     * Lay danh sach tat ca nguoi dung (moi nhat truoc).
     *
     * @return array Danh sach nguoi dung
     */
    public function layTatCa(): array
    {
        $this->db->query("SELECT * FROM nguoi_dung ORDER BY ma_vai_tro = 1 DESC, ngay_tao DESC");
        return $this->db->resultSet();
    }

    /**
     * Loc danh sach nguoi dung theo tu khoa, vai tro va trang thai.
     *
     * @param array $filters ['keyword', 'role_id', 'status']
     */
    public function locNguoiDung(array $filters): array
    {
        $conditions = [];
        $bindings = [];

        if ($filters['keyword'] !== '') {
            $conditions[] = "(ten LIKE :keyword_name OR email LIKE :keyword_email OR dien_thoai LIKE :keyword_phone"
                          . (ctype_digit($filters['keyword']) ? " OR id = :user_id" : "") . ")";
            $keyword = '%' . $filters['keyword'] . '%';
            $bindings[':keyword_name'] = $keyword;
            $bindings[':keyword_email'] = $keyword;
            $bindings[':keyword_phone'] = $keyword;
            if (ctype_digit($filters['keyword'])) {
                $bindings[':user_id'] = (int)$filters['keyword'];
            }
        }

        if ($filters['role_id'] !== '') {
            $conditions[] = "ma_vai_tro = :role_id";
            $bindings[':role_id'] = (int)$filters['role_id'];
        }

        if ($filters['status'] !== '') {
            $conditions[] = "trang_thai = :status";
            $bindings[':status'] = $filters['status'];
        }

        $sql = "SELECT * FROM nguoi_dung";
        if ($conditions) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        $sql .= " ORDER BY ma_vai_tro = 1 DESC, ngay_tao DESC";

        $this->db->query($sql);
        foreach ($bindings as $parameter => $value) {
            $this->db->bind($parameter, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        return $this->db->resultSet();
    }

    /**
     * Lay thong tin nguoi dung theo ID.
     *
     * @param  int   $id ID nguoi dung
     * @return mixed     Thong tin nguoi dung (object) hoac false
     */
    public function layTheoId(int $id): mixed
    {
        $this->db->query("SELECT * FROM nguoi_dung WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Cap nhat thong tin nguoi dung (Admin su dung, co the doi role va trang_thai).
     *
     * @param  array $data ['id', 'name', 'email', 'phone', 'role_id', 'status']
     * @return bool        True neu cap nhat thanh cong
     */
    public function capNhatNguoiDung(array $data): bool
    {
        $this->db->query("UPDATE nguoi_dung SET
            ma_vai_tro = :role_id,
            ten        = :ten,
            email      = :email,
            dien_thoai = :dien_thoai,
            trang_thai = :trang_thai
            WHERE id   = :id");
        $this->db->bind(':id',        $data['id']);
        $this->db->bind(':role_id',   $data['role_id']);
        $this->db->bind(':ten',       $data['name']);
        $this->db->bind(':email',     $data['email']);
        $this->db->bind(':dien_thoai',$data['phone']);
        $this->db->bind(':trang_thai',$data['status']);
        return $this->db->execute();
    }

    /**
     * Cap nhat ho ten va so dien thoai (nguoi dung tu cap nhat ho so ca nhan).
     *
     * @param  array $data ['id', 'name', 'phone']
     * @return bool        True neu cap nhat thanh cong
     */
    public function capNhatHoSo(array $data): bool
    {
        $this->db->query("UPDATE nguoi_dung SET ten = :ten, dien_thoai = :dien_thoai WHERE id = :id");
        $this->db->bind(':id',        $data['id']);
        $this->db->bind(':ten',       $data['name']);
        $this->db->bind(':dien_thoai',$data['phone']);
        return $this->db->execute();
    }

    /**
     * Doi mat khau nguoi dung (mat khau moi phai duoc hash truoc khi truyen vao).
     *
     * @param  int    $id         ID nguoi dung
     * @param  string $matKhauMoi Mat khau moi da duoc password_hash()
     * @return bool               True neu doi thanh cong
     */
    public function doiMatKhau(int $id, string $matKhauMoi): bool
    {
        $this->db->query("UPDATE nguoi_dung SET mat_khau = :mat_khau WHERE id = :id");
        $this->db->bind(':id',       $id);
        $this->db->bind(':mat_khau', $matKhauMoi);
        return $this->db->execute();
    }

    /**
     * Xoa nguoi dung khoi he thong theo ID.
     *
     * @param  int  $id ID nguoi dung can xoa
     * @return bool     True neu xoa thanh cong
     */
    public function xoaNguoiDung(int $id): bool
    {
        $this->db->query("DELETE FROM nguoi_dung WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // ==========================================
    // THÔNG BÁO (NOTIFICATIONS)
    // ==========================================

    /**
     * Tao thong bao moi cho nguoi dung.
     *
     * @param  int    $userId  ID nguoi nhan thong bao
     * @param  string $tieuDe  Tieu de thong bao
     * @param  string $noiDung Noi dung chi tiet
     * @return bool            True neu tao thanh cong
     */
    public function taoThongBao(int $userId, string $tieuDe, string $noiDung): bool
    {
        $this->db->query("INSERT INTO thong_bao_nguoi_dung (user_id, type, title, content, is_read) 
                          VALUES (:uid, 'he_thong', :title, :content, 0)");
        $this->db->bind(':uid',     $userId);
        $this->db->bind(':title',   $tieuDe);
        $this->db->bind(':content', $noiDung);
        return $this->db->execute();
    }

    /**
     * Dem so thong bao chua doc cua nguoi dung.
     *
     * @param  int $userId ID nguoi dung
     * @return int         So thong bao chua doc
     */
    public function demThongBaoChuaDoc(int $userId): int
    {
        $this->db->query("SELECT COUNT(*) AS so_luong FROM thong_bao_nguoi_dung WHERE user_id = :uid AND is_read = 0");
        $this->db->bind(':uid', $userId);
        $row = $this->db->single();
        return (int)($row->so_luong ?? 0);
    }

    /**
     * Lay 10 thong bao moi nhat cua nguoi dung.
     *
     * @param  int   $userId ID nguoi dung
     * @return array         Danh sach thong bao
     */
    public function layThongBao(int $userId): array
    {
        $this->db->query("SELECT id, user_id as ma_nguoi_dung, type, title as tieu_de, content as noi_dung, url, icon, is_read as da_doc, created_at as ngay_tao 
                          FROM thong_bao_nguoi_dung 
                          WHERE user_id = :uid 
                          ORDER BY created_at DESC 
                          LIMIT 10");
        $this->db->bind(':uid', $userId);
        return $this->db->resultSet();
    }

    /**
     * Danh dau tat ca thong bao cua nguoi dung la da doc.
     *
     * @param  int  $userId ID nguoi dung
     * @return bool         True neu cap nhat thanh cong
     */
    public function danhDauDaDoc(int $userId): bool
    {
        $this->db->query("UPDATE thong_bao_nguoi_dung SET is_read = 1 WHERE user_id = :uid");
        $this->db->bind(':uid', $userId);
        return $this->db->execute();
    }

    // ==========================================
    // LƯỢT UP TIN
    // ==========================================

    /**
     * Cong them luot up tin cho nguoi dung (sau khi mua goi).
     *
     * @param  int  $userId ID nguoi dung
     * @param  int  $luot   So luot can cong them
     * @return bool         True neu cap nhat thanh cong
     */
    public function congLuotUp(int $userId, int $luot): bool
    {
        $this->db->query("UPDATE nguoi_dung SET luot_up_tin = luot_up_tin + :luot WHERE id = :id");
        $this->db->bind(':luot', $luot);
        $this->db->bind(':id',   $userId);
        return $this->db->execute();
    }

    /**
     * Tru luot up tin khi nguoi dung dang tin (tru 1 luot moi lan dang).
     *
     * @param  int  $userId ID nguoi dung
     * @return bool         True neu cap nhat thanh cong
     */
    public function truLuotUp(int $userId): bool
    {
        $this->db->query("UPDATE nguoi_dung SET luot_up_tin = luot_up_tin - 1 WHERE id = :id AND luot_up_tin > 0");
        $this->db->bind(':id', $userId);
        return $this->db->execute() && $this->db->rowCount() === 1;
    }

    // Alias tuong thich nguoc
    public function findByEmail(string $email): mixed               { return $this->timTheoEmail($email); }
    public function login(string $email, string $pass): mixed       { return $this->dangNhap($email, $pass); }
    public function register(array $data): bool                     { return $this->dangKy($data); }
    public function getAllUsers(): array                             { return $this->layTatCa(); }
    public function getUserById(int $id): mixed                     { return $this->layTheoId($id); }
    public function updateUser(array $data): bool                   { return $this->capNhatNguoiDung($data); }
    public function updateProfile(array $data): bool                { return $this->capNhatHoSo($data); }
    public function updatePassword(int $id, string $pass): bool     { return $this->doiMatKhau($id, $pass); }
    public function deleteUser(int $id): bool                       { return $this->xoaNguoiDung($id); }
    public function createNotification(int $uid, string $t, string $c): bool { return $this->taoThongBao($uid, $t, $c); }
    public function getUnreadNotificationsCount(int $uid): int      { return $this->demThongBaoChuaDoc($uid); }
    public function getNotifications(int $uid): array               { return $this->layThongBao($uid); }
    public function markNotificationsAsRead(int $uid): bool         { return $this->danhDauDaDoc($uid); }
    public function addUpTurns(int $uid, int $luot): bool           { return $this->congLuotUp($uid, $luot); }
}
