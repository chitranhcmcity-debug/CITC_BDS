<?php
/**
 * LoginRepository – Ghi và đọc lịch sử đăng nhập từ bảng lich_su_dang_nhap.
 */
class LoginRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Ghi một bản ghi lịch sử đăng nhập.
     * @param array $data Keys: user_id, email, ip_address, user_agent, device_type,
     *                          os, browser, location, status, remember_me, notes
     */
    public function record(array $data): bool
    {
        $this->db->query(
            "INSERT INTO lich_su_dang_nhap
                (user_id, email, ip_address, user_agent, device_type, os, browser,
                 location, status, remember_me, notes)
             VALUES
                (:uid, :email, :ip, :ua, :device, :os, :browser,
                 :loc, :status, :rm, :notes)"
        );
        $this->db->bind(':uid',    $data['user_id']    ?? null);
        $this->db->bind(':email',  $data['email']       ?? null);
        $this->db->bind(':ip',     $data['ip_address']  ?? null);
        $this->db->bind(':ua',     $data['user_agent']  ?? null);
        $this->db->bind(':device', $data['device_type'] ?? 'unknown');
        $this->db->bind(':os',     $data['os']          ?? null);
        $this->db->bind(':browser',$data['browser']     ?? null);
        $this->db->bind(':loc',    $data['location']    ?? null);
        $this->db->bind(':status', $data['status']      ?? 'success');
        $this->db->bind(':rm',     $data['remember_me'] ?? 0);
        $this->db->bind(':notes',  $data['notes']       ?? null);
        return $this->db->execute();
    }

    /**
     * Lấy lịch sử đăng nhập của một user.
     */
    public function findByUser(int $userId, int $limit = 20): array
    {
        $this->db->query(
            "SELECT * FROM lich_su_dang_nhap
             WHERE user_id = :uid
             ORDER BY created_at DESC
             LIMIT :lim"
        );
        $this->db->bind(':uid', $userId);
        $this->db->bind(':lim', $limit, PDO::PARAM_INT);
        return $this->db->resultSet() ?: [];
    }

    /**
     * Lấy IP đăng nhập thành công gần nhất của user.
     */
    public function getLastSuccessfulIP(int $userId): ?string
    {
        $this->db->query(
            "SELECT ip_address FROM lich_su_dang_nhap
             WHERE user_id = :uid AND status = 'success'
             ORDER BY created_at DESC
             LIMIT 1"
        );
        $this->db->bind(':uid', $userId);
        $row = $this->db->single();
        return $row ? $row->ip_address : null;
    }

    /**
     * Lấy tất cả bản ghi cho Admin (phân trang).
     */
    public function getAll(int $offset = 0, int $limit = 30): array
    {
        $this->db->query(
            "SELECT lh.*, nd.ten AS ten_nguoi_dung
             FROM lich_su_dang_nhap lh
             LEFT JOIN nguoi_dung nd ON lh.user_id = nd.id
             ORDER BY lh.created_at DESC
             LIMIT :lim OFFSET :off"
        );
        $this->db->bind(':lim', $limit, PDO::PARAM_INT);
        $this->db->bind(':off', $offset, PDO::PARAM_INT);
        return $this->db->resultSet() ?: [];
    }
}
