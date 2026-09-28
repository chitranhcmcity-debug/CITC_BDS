<?php
/**
 * WalletRepository – Thao tác CSDL về Ví, Giao dịch, Gói lượt UP và Giới thiệu hoa hồng.
 * Hỗ trợ hoàn toàn tương thích ngược với các hàm cũ của PostService và DashboardService.
 */
class WalletRepository
{
    private Database $db;

    public function __construct(mixed $repo = null)
    {
        $this->db = new Database();
    }

    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollBack(): bool
    {
        return $this->db->rollBack();
    }

    /**
     * Lấy số dư hiện tại của người dùng.
     */
    public function getBalance(int $userId): int
    {
        $this->db->query("SELECT so_du FROM nguoi_dung WHERE id = :id");
        $this->db->bind(':id', $userId);
        $res = $this->db->single();
        return $res ? (int)$res->so_du : 0;
    }

    /**
     * Cộng tiền vào tài khoản người dùng.
     */
    public function addBalance(int $userId, int $amount): bool
    {
        $this->db->query("UPDATE nguoi_dung SET so_du = so_du + :amount WHERE id = :id");
        $this->db->bind(':amount', $amount);
        $this->db->bind(':id',     $userId);
        return $this->db->execute();
    }

    /**
     * Trừ tiền tài khoản người dùng an toàn (so_du >= amount).
     */
    public function subtractBalance(int $userId, int $amount): bool
    {
        $this->db->query("UPDATE nguoi_dung SET so_du = so_du - :debit WHERE id = :id AND so_du >= :minimum_balance");
        $this->db->bind(':debit',           $amount);
        $this->db->bind(':minimum_balance', $amount);
        $this->db->bind(':id',              $userId);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Lưu yêu cầu nạp tiền mới và trả về ID tự tăng.
     */
    public function createDepositRequest(array $data): int
    {
        $this->db->query("
            INSERT INTO nap_tien (ma_nguoi_dung, so_tien, tong_cong, phuong_thuc, ma_giao_dich, trang_thai, ghi_chu)
            VALUES (:ma_nguoi_dung, :so_tien, :tong_cong, :phuong_thuc, :ma_giao_dich, :trang_thai, :ghi_chu)
        ");
        $this->db->bind(':ma_nguoi_dung', $data['ma_nguoi_dung']);
        $this->db->bind(':so_tien',       $data['so_tien']);
        $this->db->bind(':tong_cong',     $data['tong_cong']);
        $this->db->bind(':phuong_thuc',   $data['phuong_thuc']);
        $this->db->bind(':ma_giao_dich',  $data['ma_giao_dich']);
        $this->db->bind(':trang_thai',    $data['trang_thai'] ?? 'cho_duyet');
        $this->db->bind(':ghi_chu',       $data['ghi_chu'] ?? null);
        
        $this->db->execute();
        return (int)$this->db->lastInsertId();
    }

    /**
     * Lấy yêu cầu nạp tiền theo ID.
     */
    public function getDepositRequest(int $id): ?object
    {
        $this->db->query("SELECT * FROM nap_tien WHERE id = :id");
        $this->db->bind(':id', $id);
        $res = $this->db->single();
        return $res ?: null;
    }

    /**
     * Cập nhật trạng thái yêu cầu nạp tiền.
     */
    public function updateDepositStatus(int $id, string $status): bool
    {
        $this->db->query("UPDATE nap_tien SET trang_thai = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id',     $id);
        return $this->db->execute();
    }

    /**
     * Đánh dấu duyệt giao dịch nạp tiền nếu trạng thái hiện tại là chờ duyệt (tránh Race Condition).
     */
    public function markDepositApproved(int $id): bool
    {
        $this->db->query("UPDATE nap_tien SET trang_thai = 'da_duyet' WHERE id = :id AND trang_thai = 'cho_duyet'");
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount() === 1;
    }

    /**
     * Lưu nhật ký giao dịch chi tiêu (chi_tieu).
     */
    public function createExpenseLog(array $data): bool
    {
        $this->db->query("
            INSERT INTO chi_tieu (ma_nguoi_dung, ma_du_an, loai, mo_ta, so_tien)
            VALUES (:ma_nguoi_dung, :ma_du_an, :loai, :mo_ta, :so_tien)
        ");
        $this->db->bind(':ma_nguoi_dung', $data['ma_nguoi_dung']);
        $this->db->bind(':ma_du_an',      $data['ma_du_an'] ?? null);
        $this->db->bind(':loai',          $data['loai']);
        $this->db->bind(':mo_ta',         $data['mo_ta']);
        $this->db->bind(':so_tien',       $data['so_tien']);
        return $this->db->execute();
    }

    /**
     * Lấy lịch sử giao dịch (bao gồm cả nạp tiền và chi tiêu) có phân trang.
     */
    public function getHistory(int $userId, int $limit = 20, int $offset = 0): array
    {
        $this->db->query("
            SELECT id, so_tien AS amount, 'nap_tien' AS type, phuong_thuc AS method_or_desc, trang_thai AS status, ngay_tao
            FROM nap_tien 
            WHERE ma_nguoi_dung = :deposit_uid
            UNION ALL
            SELECT id, so_tien AS amount, loai AS type, mo_ta AS method_or_desc, 'da_duyet' AS status, ngay_tao
            FROM chi_tieu 
            WHERE ma_nguoi_dung = :expense_uid
            ORDER BY ngay_tao DESC
            LIMIT :limit OFFSET :offset
        ");
        $this->db->bind(':deposit_uid', $userId, PDO::PARAM_INT);
        $this->db->bind(':expense_uid', $userId, PDO::PARAM_INT);
        $this->db->bind(':limit',       $limit, PDO::PARAM_INT);
        $this->db->bind(':offset',      $offset, PDO::PARAM_INT);
        return $this->db->resultSet() ?: [];
    }

    /**
     * Đếm tổng số giao dịch để phân trang.
     */
    public function countHistory(int $userId): int
    {
        $this->db->query("
            SELECT (
                (SELECT COUNT(*) FROM nap_tien WHERE ma_nguoi_dung = :uid1) + 
                (SELECT COUNT(*) FROM chi_tieu WHERE ma_nguoi_dung = :uid2)
            ) as total
        ");
        $this->db->bind(':uid1', $userId);
        $this->db->bind(':uid2', $userId);
        $res = $this->db->single();
        return $res ? (int)$res->total : 0;
    }

    /**
     * Lấy danh sách gói lượt UP tin đang hoạt động.
     */
    public function getUpPackages(): array
    {
        $this->db->query("SELECT * FROM up_packages WHERE status = 'active' ORDER BY token_count");
        return $this->db->resultSet() ?: [];
    }

    /**
     * Lấy chi tiết gói lượt UP theo ID.
     */
    public function getUpPackageById(int $id): ?object
    {
        $this->db->query("SELECT * FROM up_packages WHERE id = :id");
        $this->db->bind(':id', $id);
        $res = $this->db->single();
        return $res ?: null;
    }

    /**
     * Cộng lượt UP tin vào tài khoản người dùng.
     */
    public function addUserUpTurns(int $userId, int $turns): bool
    {
        $this->db->query("UPDATE nguoi_dung SET luot_up_tin = luot_up_tin + :turns WHERE id = :id");
        $this->db->bind(':turns', $turns);
        $this->db->bind(':id',    $userId);
        return $this->db->execute();
    }

    /**
     * Lấy thông tin hoa hồng giới thiệu của người dùng.
     */
    public function getReferralStats(int $userId): array
    {
        $this->db->query("SELECT referral_code, referred_by, referral_balance, total_referred FROM nguoi_dung WHERE id = :id");
        $this->db->bind(':id', $userId);
        $res = $this->db->single();
        return $res ? (array)$res : [
            'referral_code' => '',
            'referred_by' => null,
            'referral_balance' => 0,
            'total_referred' => 0
        ];
    }

    /**
     * Thêm bản ghi thưởng giới thiệu mới.
     */
    public function createRewardHistory(array $data): bool
    {
        $this->db->query("
            INSERT INTO reward_histories (referrer_id, referee_id, amount, status)
            VALUES (:referrer_id, :referee_id, :amount, 'completed')
        ");
        $this->db->bind(':referrer_id', $data['referrer_id']);
        $this->db->bind(':referee_id',  $data['referee_id']);
        $this->db->bind(':amount',      $data['amount']);
        return $this->db->execute();
    }

    /**
     * Lấy danh sách lịch sử thưởng giới thiệu của người dùng.
     */
    public function getRewardHistory(int $userId): array
    {
        $this->db->query("
            SELECT r.*, u.ten AS referee_name, u.email AS referee_email 
            FROM reward_histories r
            JOIN nguoi_dung u ON r.referee_id = u.id
            WHERE r.referrer_id = :uid 
            ORDER BY r.created_at DESC
        ");
        $this->db->bind(':uid', $userId);
        return $this->db->resultSet() ?: [];
    }

    /**
     * Rút tiền thưởng giới thiệu: trừ referral_balance và cộng so_du ví chính.
     */
    public function withdrawReferralReward(int $userId, int $amount): bool
    {
        // Kiểm tra số dư hoa hồng có đủ để rút không
        $this->db->query("
            UPDATE nguoi_dung 
            SET referral_balance = referral_balance - :amount,
                so_du = so_du + :amount
            WHERE id = :id AND referral_balance >= :min_amount
        ");
        $this->db->bind(':amount',     $amount);
        $this->db->bind(':min_amount', $amount);
        $this->db->bind(':id',         $userId);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Ghi nhận người giới thiệu khi đăng ký.
     */
    public function addReferralByCode(int $userId, string $referralCode): bool
    {
        // 1. Tìm người giới thiệu
        $this->db->query("SELECT id FROM nguoi_dung WHERE referral_code = :code");
        $this->db->bind(':code', $referralCode);
        $res = $this->db->single();
        $referrerId = $res ? (int)$res->id : null;
        
        if (!$referrerId || (int)$referrerId === $userId) {
            return false;
        }

        // 2. Cập nhật nguoi_dung được giới thiệu bởi
        $this->db->query("
            UPDATE nguoi_dung 
            SET referred_by = :referrer_id 
            WHERE id = :id AND referred_by IS NULL
        ");
        $this->db->bind(':referrer_id', $referrerId);
        $this->db->bind(':id',          $userId);
        
        if ($this->db->execute() && $this->db->rowCount() > 0) {
            // 3. Tăng tổng số người đã giới thiệu của người mời lên 1
            $this->db->query("UPDATE nguoi_dung SET total_referred = total_referred + 1 WHERE id = :referrer_id");
            $this->db->bind(':referrer_id', $referrerId);
            $this->db->execute();
            return true;
        }

        return false;
    }

    /**
     * Tìm giao dịch chi tiêu theo ID (hóa đơn).
     */
    public function getExpenseById(int $id): ?object
    {
        $this->db->query("
            SELECT c.*, u.ten AS user_name, u.email AS user_email, u.dien_thoai AS user_phone
            FROM chi_tieu c
            JOIN nguoi_dung u ON c.ma_nguoi_dung = u.id
            WHERE c.id = :id
        ");
        $this->db->bind(':id', $id);
        $res = $this->db->single();
        return $res ?: null;
    }

    /**
     * CRM Admin: Lọc tất cả giao dịch nạp tiền & chi tiêu.
     */
    public function getAdminTransactions(array $filters, int $limit = 20, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 't.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $where[] = 't.type = :type';
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(u.ten LIKE :search OR u.email LIKE :search OR t.method_or_desc LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Tạo Union query ảo để lọc tổng hợp cả 2 bảng
        $this->db->query("
            SELECT * FROM (
                SELECT id, ma_nguoi_dung, so_tien AS amount, 'nap_tien' AS type, phuong_thuc AS method_or_desc, trang_thai AS status, ngay_tao
                FROM nap_tien
                UNION ALL
                SELECT id, ma_nguoi_dung, so_tien AS amount, loai AS type, mo_ta AS method_or_desc, 'da_duyet' AS status, ngay_tao
                FROM chi_tieu
            ) AS t
            JOIN nguoi_dung u ON t.ma_nguoi_dung = u.id
            $whereClause
            ORDER BY t.ngay_tao DESC
            LIMIT :limit OFFSET :offset
        ");

        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        $this->db->bind(':limit',  $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }

    /**
     * CRM Admin: Đếm tổng giao dịch theo bộ lọc.
     */
    public function countAdminTransactions(array $filters): int
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 't.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $where[] = 't.type = :type';
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(u.ten LIKE :search OR u.email LIKE :search OR t.method_or_desc LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $this->db->query("
            SELECT COUNT(*) FROM (
                SELECT id, ma_nguoi_dung, so_tien AS amount, 'nap_tien' AS type, phuong_thuc AS method_or_desc, trang_thai AS status, ngay_tao
                FROM nap_tien
                UNION ALL
                SELECT id, ma_nguoi_dung, so_tien AS amount, loai AS type, mo_ta AS method_or_desc, 'da_duyet' AS status, ngay_tao
                FROM chi_tieu
            ) AS t
            JOIN nguoi_dung u ON t.ma_nguoi_dung = u.id
            $whereClause
        ");

        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        $res = $this->db->single();
        return $res ? (int)current((array)$res) : 0;
    }

    /**
     * CRM Admin: Thống kê doanh thu ví.
     */
    public function getRevenueStats(): array
    {
        $stats = [];

        // Doanh thu theo tháng
        $this->db->query("
            SELECT DATE_FORMAT(ngay_tao, '%Y-%m') AS month, SUM(so_tien) AS revenue 
            FROM nap_tien 
            WHERE trang_thai = 'da_duyet'
            GROUP BY month 
            ORDER BY month DESC 
            LIMIT 12
        ");
        $stats['monthly'] = $this->db->resultSet() ?: [];

        // Doanh thu theo ngày (30 ngày qua)
        $this->db->query("
            SELECT DATE(ngay_tao) AS date, SUM(so_tien) AS revenue 
            FROM nap_tien 
            WHERE trang_thai = 'da_duyet' AND ngay_tao >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY date 
            ORDER BY date DESC
        ");
        $stats['daily'] = $this->db->resultSet() ?: [];

        // Doanh thu theo loại chi tiêu (gói UP, VIP, vv.)
        $this->db->query("
            SELECT loai AS type, SUM(so_tien) AS total 
            FROM chi_tieu 
            GROUP BY type
        ");
        $stats['by_type'] = $this->db->resultSet() ?: [];

        return $stats;
    }

    // =========================================================================
    // HÀM TƯƠNG THÍCH NGƯỢC (BACKWARD COMPATIBILITY) CHO POST/DASHBOARD SERVICES
    // =========================================================================

    /**
     * Tìm thông tin người dùng (cho PostService).
     */
    public function user(int $userId): ?object
    {
        $this->db->query("SELECT * FROM nguoi_dung WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $userId);
        $res = $this->db->single();
        return $res ?: null;
    }

    /**
     * Trừ tiền trực tiếp (cho PostService).
     */
    public function debit(int $userId, int $amount): bool
    {
        return $this->subtractBalance($userId, $amount);
    }

    /**
     * Ghi nhận giao dịch (cho PostService).
     */
    public function record(int $userId, int $projectId, string $type, string $desc, int $amount): bool
    {
        return $this->createExpenseLog([
            'ma_nguoi_dung' => $userId,
            'ma_du_an'      => $projectId,
            'loai'          => $type,
            'mo_ta'         => $desc,
            'so_tien'       => $amount
        ]);
    }

    /**
     * Khấu trừ lượt UP (cho PostService).
     */
    public function consumeUp(int $userId): bool
    {
        $this->db->query("UPDATE nguoi_dung SET luot_up_tin = luot_up_tin - 1 WHERE id = :id AND luot_up_tin >= 1");
        $this->db->bind(':id', $userId);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Thống kê Ví tóm tắt (cho DashboardService).
     */
    public function summary(int $userId): array
    {
        $this->db->query("SELECT so_du, luot_up_tin FROM nguoi_dung WHERE id = :id");
        $this->db->bind(':id', $userId);
        $userObj = $this->db->single();
        
        $this->db->query("SELECT SUM(so_tien) AS total FROM nap_tien WHERE ma_nguoi_dung = :id AND trang_thai = 'da_duyet'");
        $this->db->bind(':id', $userId);
        $depObj = $this->db->single();
        
        $this->db->query("SELECT SUM(so_tien) AS total FROM chi_tieu WHERE ma_nguoi_dung = :id");
        $this->db->bind(':id', $userId);
        $spentObj = $this->db->single();

        return [
            'balance'         => $userObj ? (int)$userObj->so_du : 0,
            'up_turns'        => $userObj ? (int)$userObj->luot_up_tin : 0,
            'total_deposited' => $depObj ? (int)$depObj->total : 0,
            'total_spent'     => $spentObj ? (int)$spentObj->total : 0,
        ];
    }

    /**
     * Lịch sử giao dịch gần đây (cho DashboardService).
     */
    public function recentTransactions(int $userId, int $limit = 10): array
    {
        $history = $this->getHistory($userId, $limit, 0);
        $result = [];
        foreach ($history as $row) {
            $isEarning = ($row->type === 'nap_tien' || $row->type === 'thuong_chia_se');
            $result[] = (object)[
                'id'          => $row->id,
                'type'        => $row->type,
                'description' => $row->method_or_desc,
                'amount'      => (int)$row->amount,
                'status'      => $row->status,
                'direction'   => $isEarning ? 'in' : 'out',
                'ngay_tao'    => $row->ngay_tao
            ];
        }
        return $result;
    }
}
