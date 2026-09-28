<?php

namespace App\Repositories;

use App\Models\Database;
use PDO;

/**
 * LoginHistoryRepository – Quản lý việc lưu và đọc lịch sử đăng nhập, quản lý phiên đăng nhập.
 * Tuân thủ SOLID, Repository Pattern.
 */
class LoginHistoryRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Ghi nhận lượt đăng nhập (thành công hoặc thất bại).
     */
    public function record(array $data): bool
    {
        $this->db->query('
            INSERT INTO login_history 
            (user_id, email, ip_address, browser, location, remember_me, notes, platform, 
             device, country, status, fail_reason, user_agent, device_type, os, created_at)
            VALUES 
            (:uid, :email, :ip, :browser, :loc, :rem, :notes, :platform, 
             :device, :country, :status, :fail, :agent, :dev_type, :os, NOW())
        ');
        $this->db->bind(':uid', $data['user_id'] ?? null, $data['user_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $this->db->bind(':email', $data['email'] ?? null);
        $this->db->bind(':ip', $data['ip_address'] ?? null);
        $this->db->bind(':browser', $data['browser'] ?? null);
        $this->db->bind(':loc', $data['location'] ?? null);
        $this->db->bind(':rem', $data['remember_me'] ?? 0, PDO::PARAM_INT);
        $this->db->bind(':notes', $data['notes'] ?? null);
        $this->db->bind(':platform', $data['platform'] ?? null);
        $this->db->bind(':device', $data['device'] ?? null);
        $this->db->bind(':country', $data['country'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'success');
        $this->db->bind(':fail', $data['fail_reason'] ?? null);
        $this->db->bind(':agent', $data['user_agent'] ?? null);
        $this->db->bind(':dev_type', $data['device_type'] ?? 'unknown');
        $this->db->bind(':os', $data['os'] ?? null);

        return $this->db->execute();
    }

    /**
     * Lấy lịch sử đăng nhập phân trang của một người dùng.
     */
    public function getHistory(int $userId, int $limit = 20, int $offset = 0): array
    {
        $this->db->query('
            SELECT * FROM login_history 
            WHERE user_id = :uid 
            ORDER BY created_at DESC 
            LIMIT :lim OFFSET :off
        ');
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);
        $this->db->bind(':lim', $limit, PDO::PARAM_INT);
        $this->db->bind(':off', $offset, PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }

    /**
     * Đếm tổng số bản ghi lịch sử đăng nhập.
     */
    public function countHistory(int $userId): int
    {
        $this->db->query('SELECT COUNT(*) AS total FROM login_history WHERE user_id = :uid');
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);
        $res = $this->db->single();

        return $res ? (int) $res->total : 0;
    }

    /**
     * Đánh dấu đăng xuất cho phiên đăng nhập hiện tại.
     */
    public function recordLogout(int $userId, string $ipAddress, string $userAgent): bool
    {
        $this->db->query("
            UPDATE login_history 
            SET status = 'logout' 
            WHERE user_id = :uid AND ip_address = :ip AND user_agent = :agent AND status = 'success'
            ORDER BY created_at DESC LIMIT 1
        ");
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);
        $this->db->bind(':ip', $ipAddress);
        $this->db->bind(':agent', $userAgent);

        return $this->db->execute();
    }
}
