<?php

namespace App\Repositories;

use App\Models\Database;
use App\Models\VaiTro;
use PDO;

/**
 * UserRepository – truy vấn CSDL bảng nguoi_dung.
 * Không chứa business logic. Chỉ CRUD thuần.
 * Bao gồm cả các method cho Author Profile (tương thích ngược).
 */
class UserRepository
{
    protected Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /* ── TÌM KIẾM ── */

    public function findById(int $id): mixed
    {
        $this->db->query('SELECT * FROM nguoi_dung WHERE id = :id LIMIT 1');
        $this->db->bind(':id', $id);
        $row = $this->db->single();

        return $row ?: null;
    }

    public function findByEmail(string $email): mixed
    {
        $this->db->query('SELECT * FROM nguoi_dung WHERE email = :email LIMIT 1');
        $this->db->bind(':email', trim($email));
        $row = $this->db->single();

        return $row ?: null;
    }

    public function findByPhone(string $phone): mixed
    {
        $this->db->query('SELECT * FROM nguoi_dung WHERE dien_thoai = :phone LIMIT 1');
        $this->db->bind(':phone', trim($phone));
        $row = $this->db->single();

        return $row ?: null;
    }

    /**
     * Tìm theo email hoặc số điện thoại (dùng cho login).
     */
    public function findByEmailOrPhone(string $identifier): mixed
    {
        $this->db->query(
            'SELECT * FROM nguoi_dung
             WHERE email = :id1 OR dien_thoai = :id2
             LIMIT 1'
        );
        $this->db->bind(':id1', trim($identifier));
        $this->db->bind(':id2', trim($identifier));
        $row = $this->db->single();

        return $row ?: null;
    }

    public function findByRememberToken(string $token): mixed
    {
        if (empty($token)) {
            return null;
        }
        $this->db->query(
            'SELECT * FROM nguoi_dung WHERE remember_token = :token LIMIT 1'
        );
        $this->db->bind(':token', $token);
        $row = $this->db->single();

        return $row ?: null;
    }

    /* ── TẠO MỚI ── */

    /**
     * Tạo tài khoản người dùng mới.
     *
     * @param  array  $data  ['ten','email','dien_thoai','mat_khau','ma_vai_tro']
     * @return int|false lastInsertId hoặc false
     */
    public function create(array $data): int|false
    {
        $this->db->query(
            "INSERT INTO nguoi_dung
                (ma_vai_tro, ten, email, dien_thoai, mat_khau, trang_thai, email_verified_at, login_attempts)
             VALUES
                (:role, :ten, :email, :phone, :pass, 'hoat_dong', NULL, 0)"
        );
        $this->db->bind(':role', $data['ma_vai_tro'] ?? VaiTro::MEMBER);
        $this->db->bind(':ten', $data['ten']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['dien_thoai'] ?? '');
        $this->db->bind(':pass', $data['mat_khau']);
        if (! $this->db->execute()) {
            return false;
        }

        return (int) $this->db->lastInsertId();
    }

    /* ── CẬP NHẬT ── */

    public function updateField(int $id, string $column, mixed $value): bool
    {
        // Whitelist cột được phép cập nhật để chống SQL injection
        $allowed = [
            'ten', 'email', 'dien_thoai', 'mat_khau', 'anh_dai_dien', 'mo_ta_ca_nhan',
            'trang_thai', 'email_verified_at', 'remember_token', 'locked_until',
            'login_attempts', 'phone_verified', 'last_login_at', 'last_login_ip',
            'da_xac_thuc', 'loai_tai_khoan', 'khu_vuc_hoat_dong', 'zalo', 'auth_version',
            'ngay_sinh', 'gioi_tinh', 'dia_chi', 'nghe_nghiep',
            'nhan_email', 'nhan_notification', 'an_sdt', 'an_email',
            'cho_phep_chat', 'cho_phep_goi',
        ];
        if (! in_array($column, $allowed, true)) {
            return false;
        }

        $this->db->query("UPDATE nguoi_dung SET `{$column}` = :val WHERE id = :id");
        $this->db->bind(':val', $value);
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    public function updatePassword(int $id, string $hashedPassword): bool
    {
        return $this->updateField($id, 'mat_khau', $hashedPassword);
    }

    public function markEmailVerified(int $id): bool
    {
        $this->db->query(
            'UPDATE nguoi_dung
             SET email_verified_at = NOW(), da_xac_thuc = 1
             WHERE id = :id'
        );
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    public function setRememberToken(int $id, ?string $token): bool
    {
        return $this->updateField($id, 'remember_token', $token);
    }

    /** Tăng phiên bản xác thực để vô hiệu hóa mọi session đang tồn tại. */
    public function invalidateAllSessions(int $id): bool
    {
        $this->db->query(
            'UPDATE nguoi_dung
             SET auth_version = auth_version + 1, remember_token = NULL
             WHERE id = :id'
        );
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    /* ── KHÓA TÀI KHOẢN ── */

    /**
     * Tăng số lần đăng nhập sai. Tự động khóa nếu >= $maxAttempts.
     */
    public function incrementLoginAttempts(int $id, int $maxAttempts = 5, int $lockMinutes = 15): void
    {
        $this->db->query('UPDATE nguoi_dung SET login_attempts = login_attempts + 1 WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();

        $user = $this->findById($id);
        if ($user && (int) $user->login_attempts >= $maxAttempts) {
            $lockUntil = date('Y-m-d H:i:s', strtotime("+{$lockMinutes} minutes"));
            $this->db->query(
                'UPDATE nguoi_dung SET locked_until = :lu, login_attempts = 0 WHERE id = :id'
            );
            $this->db->bind(':lu', $lockUntil);
            $this->db->bind(':id', $id);
            $this->db->execute();
        }
    }

    public function resetLoginAttempts(int $id): bool
    {
        $this->db->query(
            'UPDATE nguoi_dung SET login_attempts = 0, locked_until = NULL WHERE id = :id'
        );
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    public function lock(int $id, int $minutes = 15): bool
    {
        $lockUntil = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));
        $this->db->query(
            'UPDATE nguoi_dung SET locked_until = :lu, login_attempts = 0 WHERE id = :id'
        );
        $this->db->bind(':lu', $lockUntil);
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    public function unlock(int $id): bool
    {
        $this->db->query(
            'UPDATE nguoi_dung SET locked_until = NULL, login_attempts = 0 WHERE id = :id'
        );
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    /* ── LAST LOGIN ── */

    public function updateLastLogin(int $id, string $ip): bool
    {
        $this->db->query(
            'UPDATE nguoi_dung SET last_login_at = NOW(), last_login_ip = :ip WHERE id = :id'
        );
        $this->db->bind(':ip', $ip);
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    /* ── AUTHOR PROFILE (backward compat) ── */

    public function getPublicProfile(int $id): mixed
    {
        $this->db->query(
            "SELECT id, ten, email, dien_thoai, anh_dai_dien, mo_ta_ca_nhan,
                    khu_vuc_hoat_dong, loai_tai_khoan, zalo, hien_thi_email,
                    so_nguoi_theo_doi, ty_le_phan_hoi, gio_phan_hoi_tb,
                    trang_thai, ngay_tao, da_xac_thuc
             FROM nguoi_dung
             WHERE id = :id AND trang_thai = 'hoat_dong'
             LIMIT 1"
        );
        $this->db->bind(':id', $id);
        $row = $this->db->single();

        return $this->db->rowCount() > 0 ? $row : null;
    }

    public function isFollowing(int $followerId, int $followedId): bool
    {
        $this->db->query(
            'SELECT COUNT(*) AS cnt FROM theo_doi
             WHERE follower_id = :fid AND followed_id = :uid'
        );
        $this->db->bind(':fid', $followerId);
        $this->db->bind(':uid', $followedId);
        $row = $this->db->single();

        return (int) ($row->cnt ?? 0) > 0;
    }

    public function follow(int $followerId, int $followedId): bool
    {
        $this->db->query(
            'INSERT IGNORE INTO theo_doi (follower_id, followed_id) VALUES (:fid, :uid)'
        );
        $this->db->bind(':fid', $followerId);
        $this->db->bind(':uid', $followedId);
        $ok = $this->db->execute();
        if ($ok) {
            $this->db->query(
                'UPDATE nguoi_dung SET so_nguoi_theo_doi = so_nguoi_theo_doi + 1 WHERE id = :uid'
            );
            $this->db->bind(':uid', $followedId);
            $this->db->execute();
        }

        return $ok;
    }

    public function unfollow(int $followerId, int $followedId): bool
    {
        $this->db->query(
            'DELETE FROM theo_doi WHERE follower_id = :fid AND followed_id = :uid'
        );
        $this->db->bind(':fid', $followerId);
        $this->db->bind(':uid', $followedId);
        $ok = $this->db->execute();
        if ($ok) {
            $this->db->query(
                'UPDATE nguoi_dung SET so_nguoi_theo_doi = GREATEST(so_nguoi_theo_doi - 1, 0) WHERE id = :uid'
            );
            $this->db->bind(':uid', $followedId);
            $this->db->execute();
        }

        return $ok;
    }

    /* ── ADMIN PAGINATION ── */

    public function getAllPaginated(int $offset = 0, int $limit = 20, array $filters = []): array
    {
        $where = [];
        $binds = [];
        if (! empty($filters['keyword'])) {
            $where[] = '(ten LIKE :kw OR email LIKE :kw2 OR dien_thoai LIKE :kw3)';
            $k = '%'.$filters['keyword'].'%';
            $binds[':kw'] = $k;
            $binds[':kw2'] = $k;
            $binds[':kw3'] = $k;
        }
        if (! empty($filters['status'])) {
            $where[] = 'trang_thai = :status';
            $binds[':status'] = $filters['status'];
        }
        if (! empty($filters['role_id'])) {
            $where[] = 'ma_vai_tro = :role';
            $binds[':role'] = (int) $filters['role_id'];
        }
        $sql = 'SELECT * FROM nguoi_dung';
        if ($where) {
            $sql .= ' WHERE '.implode(' AND ', $where);
        }
        $sql .= ' ORDER BY ngay_tao DESC LIMIT :limit OFFSET :offset';
        $binds[':limit'] = $limit;
        $binds[':offset'] = $offset;
        $this->db->query($sql);
        foreach ($binds as $k => $v) {
            $this->db->bind($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        return $this->db->resultSet() ?: [];
    }

    public function countAll(array $filters = []): int
    {
        $where = [];
        $binds = [];
        if (! empty($filters['keyword'])) {
            $where[] = '(ten LIKE :kw OR email LIKE :kw2 OR dien_thoai LIKE :kw3)';
            $k = '%'.$filters['keyword'].'%';
            $binds[':kw'] = $k;
            $binds[':kw2'] = $k;
            $binds[':kw3'] = $k;
        }
        if (! empty($filters['status'])) {
            $where[] = 'trang_thai = :status';
            $binds[':status'] = $filters['status'];
        }
        if (! empty($filters['role_id'])) {
            $where[] = 'ma_vai_tro = :role';
            $binds[':role'] = (int) $filters['role_id'];
        }
        $sql = 'SELECT COUNT(*) AS cnt FROM nguoi_dung';
        if ($where) {
            $sql .= ' WHERE '.implode(' AND ', $where);
        }
        $this->db->query($sql);
        foreach ($binds as $k => $v) {
            $this->db->bind($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        return (int) ($this->db->single()->cnt ?? 0);
    }

    public function lastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }

    public function getAllActive(): array
    {
        $this->db->query("SELECT id, email, ma_vai_tro FROM nguoi_dung WHERE trang_thai = 'hoat_dong'");

        return $this->db->resultSet();
    }

    public function findByRole(int $roleId): array
    {
        $this->db->query("SELECT id, email, ma_vai_tro FROM nguoi_dung WHERE ma_vai_tro = :role AND trang_thai = 'hoat_dong'");

        return $this->db->resultSet();
    }

    /**
     * Lấy danh sách người dùng phục vụ Admin (kèm phân trang, lọc, tìm kiếm).
     */
    public function adminList(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        [$sql, $binds] = $this->buildAdminUserQuery($filters);
        $sql .= ' ORDER BY u.id DESC LIMIT :limit OFFSET :offset';

        $this->db->query($sql);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Đếm tổng số người dùng theo bộ lọc phục vụ Admin.
     */
    public function adminCount(array $filters = []): int
    {
        [$sql, $binds] = $this->buildAdminUserQuery($filters, true);

        $this->db->query($sql);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }

        $row = $this->db->single();

        return (int) ($row->total ?? 0);
    }

    /**
     * Tìm thông tin chi tiết một người dùng cho Admin (kèm thống kê tin, nạp tiền).
     */
    public function adminFind(int $id): ?stdClass
    {
        $this->db->query("SELECT u.*, r.ten AS role_name,
                                 (SELECT COUNT(*) FROM du_an WHERE ma_nguoi_dung = u.id AND deleted_at IS NULL) AS post_count,
                                 (SELECT COUNT(*) FROM du_an WHERE ma_nguoi_dung = u.id AND goi_vip > 0 AND ngay_het_han_vip >= NOW() AND deleted_at IS NULL) AS vip_post_count,
                                 (SELECT COALESCE(SUM(so_tien), 0) FROM nap_tien WHERE ma_nguoi_dung = u.id AND trang_thai = 'da_duyet') AS total_deposit
                          FROM nguoi_dung u
                          LEFT JOIN vai_tro r ON u.ma_vai_tro = r.id
                          WHERE u.id = :id LIMIT 1");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $row = $this->db->single();

        return $row ?: null;
    }

    /**
     * Khóa tài khoản người dùng đến thời hạn chỉ định.
     */
    public function adminLock(int $id, ?string $lockedUntil): bool
    {
        $this->db->query('UPDATE nguoi_dung SET locked_until = :until WHERE id = :id');
        $this->db->bind(':until', $lockedUntil);
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Mở khóa tài khoản người dùng.
     */
    public function adminUnlock(int $id): bool
    {
        $this->db->query('UPDATE nguoi_dung SET locked_until = NULL, login_attempts = 0 WHERE id = :id');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Đặt lại mật khẩu người dùng.
     */
    public function adminResetPassword(int $id, string $hashedPassword): bool
    {
        $this->db->query('UPDATE nguoi_dung SET mat_khau = :password WHERE id = :id');
        $this->db->bind(':password', $hashedPassword);
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Admin cập nhật thông tin hồ sơ của người dùng.
     */
    public function adminUpdateProfile(int $id, array $d): bool
    {
        $this->db->query('UPDATE nguoi_dung 
                          SET ten = :name, email = :email, dien_thoai = :phone, ma_vai_tro = :role_id,
                              ngay_sinh = :dob, gioi_tinh = :gender, dia_chi = :address,
                              nghe_nghiep = :job, mo_ta_ca_nhan = :bio, khu_vuc_hoat_dong = :region,
                              trang_thai = :status, da_xac_thuc = :email_verified, phone_verified = :phone_verified
                          WHERE id = :id');

        $this->db->bind(':name', $d['name']);
        $this->db->bind(':email', $d['email']);
        $this->db->bind(':phone', $d['phone'] ?? null);
        $this->db->bind(':role_id', (int) $d['role_id']);
        $this->db->bind(':dob', $d['dob'] ?: null);
        $this->db->bind(':gender', $d['gender'] ?? null);
        $this->db->bind(':address', $d['address'] ?? null);
        $this->db->bind(':job', $d['job'] ?? null);
        $this->db->bind(':bio', $d['bio'] ?? null);
        $this->db->bind(':region', $d['region'] ?? null);
        $this->db->bind(':status', $d['status']);
        $this->db->bind(':email_verified', (int) ($d['email_verified'] ?? 0));
        $this->db->bind(':phone_verified', (int) ($d['phone_verified'] ?? 0));
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Admin tạo người dùng mới.
     */
    public function adminCreate(array $d): int
    {
        $this->db->query('INSERT INTO nguoi_dung 
                          (ten, email, dien_thoai, mat_khau, ma_vai_tro, trang_thai, da_xac_thuc, phone_verified, ngay_tao)
                          VALUES (:name, :email, :phone, :password, :role_id, :status, :email_verified, :phone_verified, NOW())');

        $this->db->bind(':name', $d['name']);
        $this->db->bind(':email', $d['email']);
        $this->db->bind(':phone', $d['phone'] ?? null);
        $this->db->bind(':password', $d['password']);
        $this->db->bind(':role_id', (int) $d['role_id']);
        $this->db->bind(':status', $d['status'] ?? 'hoat_dong');
        $this->db->bind(':email_verified', (int) ($d['email_verified'] ?? 0));
        $this->db->bind(':phone_verified', (int) ($d['phone_verified'] ?? 0));

        if ($this->db->execute()) {
            return (int) $this->db->lastInsertId();
        }

        return 0;
    }

    /**
     * Admin xóa người dùng (mềm: cập nhật trạng thái ngừng hoạt động).
     */
    public function adminDelete(int $id): bool
    {
        $this->db->query("UPDATE nguoi_dung SET trang_thai = 'ngung_hoat_dong' WHERE id = :id");
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Admin xóa vĩnh viễn người dùng (hard delete).
     */
    public function adminHardDelete(int $id): bool
    {
        $this->db->query('DELETE FROM nguoi_dung WHERE id = :id');
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Thống kê báo cáo (Analytics) người dùng cho Admin.
     */
    public function adminCountStats(): array
    {
        $this->db->query("SELECT 
                            COUNT(*) AS total,
                            SUM(CASE WHEN ngay_tao >= CURDATE() THEN 1 ELSE 0 END) AS new_today,
                            SUM(CASE WHEN trang_thai = 'hoat_dong' THEN 1 ELSE 0 END) AS active,
                            SUM(CASE WHEN locked_until > NOW() THEN 1 ELSE 0 END) AS locked
                          FROM nguoi_dung");
        $row = $this->db->single();

        // Top 5 người dùng đăng nhiều tin nhất
        $this->db->query('SELECT u.id, u.ten, u.email, COUNT(p.id) AS count
                          FROM nguoi_dung u
                          JOIN du_an p ON p.ma_nguoi_dung = u.id
                          WHERE p.deleted_at IS NULL
                          GROUP BY u.id
                          ORDER BY count DESC LIMIT 5');
        $topPosters = $this->db->resultSet() ?: [];

        // Top 5 người dùng nạp tiền nhiều nhất
        $this->db->query("SELECT u.id, u.ten, u.email, SUM(n.so_tien) AS total_amount
                          FROM nguoi_dung u
                          JOIN nap_tien n ON n.ma_nguoi_dung = u.id
                          WHERE n.trang_thai = 'da_duyet'
                          GROUP BY u.id
                          ORDER BY total_amount DESC LIMIT 5");
        $topDepositors = $this->db->resultSet() ?: [];

        return [
            'total' => (int) ($row->total ?? 0),
            'new_today' => (int) ($row->new_today ?? 0),
            'active' => (int) ($row->active ?? 0),
            'locked' => (int) ($row->locked ?? 0),
            'top_posters' => $topPosters,
            'top_depositors' => $topDepositors,
        ];
    }

    /**
     * Hàm dùng chung xây dựng câu truy vấn SQL động cho Admin quản lý User.
     */
    private function buildAdminUserQuery(array $filters, bool $isCount = false): array
    {
        $select = $isCount ? 'SELECT COUNT(*) AS total' : 'SELECT u.*, r.ten AS role_name';
        $sql = "{$select} FROM nguoi_dung u 
                LEFT JOIN vai_tro r ON u.ma_vai_tro = r.id
                WHERE 1=1";
        $binds = [];

        // 1. Lọc theo vai trò
        if (isset($filters['role_id']) && $filters['role_id'] !== '') {
            $sql .= ' AND u.ma_vai_tro = :role_id';
            $binds[':role_id'] = (int) $filters['role_id'];
        }

        // 2. Lọc theo trạng thái hoạt động / khóa
        if (isset($filters['status']) && $filters['status'] !== '') {
            if ($filters['status'] === 'da_khoa') {
                $sql .= ' AND u.locked_until > NOW()';
            } elseif ($filters['status'] === 'hoat_dong') {
                $sql .= " AND u.trang_thai = 'hoat_dong' AND (u.locked_until IS NULL OR u.locked_until <= NOW())";
            } else {
                $sql .= ' AND u.trang_thai = :status';
                $binds[':status'] = $filters['status'];
            }
        }

        // 3. Lọc theo xác thực email
        if (isset($filters['email_verified']) && $filters['email_verified'] !== '') {
            $sql .= ' AND u.da_xac_thuc = :email_verified';
            $binds[':email_verified'] = (int) $filters['email_verified'];
        }

        // 4. Lọc theo xác thực số điện thoại
        if (isset($filters['phone_verified']) && $filters['phone_verified'] !== '') {
            $sql .= ' AND u.phone_verified = :phone_verified';
            $binds[':phone_verified'] = (int) $filters['phone_verified'];
        }

        // 5. Lọc theo số dư tối thiểu
        if (isset($filters['min_balance']) && (int) $filters['min_balance'] > 0) {
            $sql .= ' AND u.so_du >= :min_balance';
            $binds[':min_balance'] = (int) $filters['min_balance'];
        }

        // 6. Lọc theo ngày đăng ký
        if (! empty($filters['start_date'])) {
            $sql .= ' AND u.ngay_tao >= :start_date';
            $binds[':start_date'] = $filters['start_date'].' 00:00:00';
        }

        // 7. Tìm kiếm tự do
        if (! empty($filters['search'])) {
            $searchVal = '%'.$filters['search'].'%';
            $sql .= ' AND (u.id = :search_id 
                        OR u.ten LIKE :search_name 
                        OR u.email LIKE :search_email 
                        OR u.dien_thoai LIKE :search_phone)';
            $binds[':search_id'] = (int) $filters['search'];
            $binds[':search_name'] = $searchVal;
            $binds[':search_email'] = $searchVal;
            $binds[':search_phone'] = $searchVal;
        }

        return [$sql, $binds];
    }
}
