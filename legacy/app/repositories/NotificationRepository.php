<?php
/**
 * NotificationRepository – Tương tác trực tiếp với bảng `notifications` trong CSDL.
 * Tuân thủ SOLID, Repository Pattern.
 */
class NotificationRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Tìm thông báo theo ID.
     */
    public function findById(int $id, ?int $userId = null): ?stdClass
    {
        if ($userId !== null) {
            $this->db->query("SELECT * FROM notifications WHERE id = :id AND (user_id = :uid OR user_id IS NULL)");
            $this->db->bind(':id', $id);
            $this->db->bind(':uid', $userId);
        } else {
            $this->db->query("SELECT * FROM notifications WHERE id = :id");
            $this->db->bind(':id', $id);
        }
        return $this->db->single();
    }

    /**
     * Lấy các thông báo gần đây của người dùng (cho Dashboard widget).
     *
     * @param int $userId  ID người dùng
     * @param int $limit   Số lượng tối đa
     * @return array Danh sách thông báo gần nhất
     */
    public function recentByUser(int $userId, int $limit = 10): array
    {
        $this->db->query("
            SELECT * FROM notifications 
            WHERE user_id = :uid OR user_id IS NULL
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet() ?: [];
    }

    /**
     * Phân trang thông báo của người dùng kèm bộ lọc.
     */
    public function paginateByUser(int $userId, string $filter, int $limit, int $offset, string $search = ''): array
    {
        $sql = "SELECT * FROM notifications WHERE (user_id = :uid OR user_id IS NULL)";
        $sql .= $this->buildFilterClause($filter);

        if (!empty($search)) {
            $sql .= " AND (title LIKE :search1 OR content LIKE :search2)";
        }

        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        $this->db->bind(':uid', $userId);
        $this->db->bind(':limit', $limit);
        $this->db->bind(':offset', $offset);

        if (!empty($search)) {
            $this->db->bind(':search1', '%' . $search . '%');
            $this->db->bind(':search2', '%' . $search . '%');
        }

        return $this->db->resultSet();
    }

    /**
     * Đếm tổng số thông báo của người dùng kèm bộ lọc.
     */
    public function countByUser(int $userId, string $filter, string $search = ''): int
    {
        $sql = "SELECT COUNT(*) as total FROM notifications WHERE (user_id = :uid OR user_id IS NULL)";
        $sql .= $this->buildFilterClause($filter);

        if (!empty($search)) {
            $sql .= " AND (title LIKE :search1 OR content LIKE :search2)";
        }

        $this->db->query($sql);
        $this->db->bind(':uid', $userId);

        if (!empty($search)) {
            $this->db->bind(':search1', '%' . $search . '%');
            $this->db->bind(':search2', '%' . $search . '%');
        }

        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    /**
     * Đếm số lượng chưa đọc.
     */
    public function countUnread(int $userId): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM notifications WHERE user_id = :uid AND is_read = 0");
        $this->db->bind(':uid', $userId);
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    /**
     * Tạo thông báo mới.
     */
    public function insert(array $data): int
    {
        $this->db->query("INSERT INTO notifications (user_id, type, title, content, url, icon, is_read, created_at)
                          VALUES (:uid, :type, :title, :content, :url, :icon, :is_read, :created)");
        
        $this->db->bind(':uid',     $data['user_id']);
        $this->db->bind(':type',    $data['type']);
        $this->db->bind(':title',   $data['title']);
        $this->db->bind(':content', $data['content']);
        $this->db->bind(':url',     $data['url'] ?? null);
        $this->db->bind(':icon',    $data['icon'] ?? null);
        $this->db->bind(':is_read', $data['is_read'] ?? 0);
        $this->db->bind(':created', $data['created_at'] ?? date('Y-m-d H:i:s'));

        if ($this->db->execute()) {
            // Lấy ID vừa chèn
            $this->db->query("SELECT LAST_INSERT_ID() as last_id");
            $row = $this->db->single();
            return (int)($row->last_id ?? 0);
        }
        return 0;
    }

    /**
     * Cập nhật trạng thái đọc của thông báo.
     */
    public function updateReadStatus(int $id, int $userId, bool $isRead): bool
    {
        $this->db->query("UPDATE notifications SET is_read = :read WHERE id = :id AND (user_id = :uid OR user_id IS NULL)");
        $this->db->bind(':read', $isRead ? 1 : 0);
        $this->db->bind(':id', $id);
        $this->db->bind(':uid', $userId);
        return $this->db->execute();
    }

    /**
     * Đánh dấu tất cả là đã đọc.
     */
    public function markAllRead(int $userId): bool
    {
        $this->db->query("UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0");
        $this->db->bind(':uid', $userId);
        return $this->db->execute();
    }

    /**
     * Xóa thông báo cụ thể.
     */
    public function delete(int $id, int $userId): bool
    {
        $this->db->query("DELETE FROM notifications WHERE id = :id AND user_id = :uid");
        $this->db->bind(':id', $id);
        $this->db->bind(':uid', $userId);
        return $this->db->execute();
    }

    /**
     * Xóa tất cả thông báo của người dùng.
     */
    public function clearAll(int $userId): bool
    {
        $this->db->query("DELETE FROM notifications WHERE user_id = :uid");
        $this->db->bind(':uid', $userId);
        return $this->db->execute();
    }

    /**
     * Lấy thống kê mở thông báo (Open Rate Analytics) dành cho Admin.
     */
    public function getStatistics(): array
    {
        $this->db->query("SELECT COUNT(*) as total,
                                 SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_count,
                                 SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_count
                          FROM notifications");
        $row = $this->db->single();
        $total = (int)($row->total ?? 0);
        $read = (int)($row->read_count ?? 0);
        $unread = (int)($row->unread_count ?? 0);
        $openRate = $total > 0 ? round((100 * $read) / $total, 1) : 0.0;

        return [
            'total'       => $total,
            'read_count'  => $read,
            'unread_count'=> $unread,
            'open_rate'   => $openRate
        ];
    }

    /**
     * Lấy các thông báo được tạo gần đây bởi Admin để hiển thị/thu hồi.
     */
    public function getAdminRecentSent(int $limit, int $offset): array
    {
        $this->db->query("SELECT * FROM notifications 
                          ORDER BY created_at DESC 
                          LIMIT :limit OFFSET :offset");
        $this->db->bind(':limit', $limit);
        $this->db->bind(':offset', $offset);
        return $this->db->resultSet();
    }

    /**
     * Thu hồi/Xóa thông báo nếu chưa đọc (hoặc thu hồi bắt buộc).
     */
    public function deleteExpiredNotification(int $id): bool
    {
        $this->db->query("DELETE FROM notifications WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // ==========================================
    // TRỢ GIÚP XÂY DỰNG CLAUSE SQL FILTER
    // ==========================================

    private function buildFilterClause(string $filter): string
    {
        return match ($filter) {
            'unread'      => " AND is_read = 0",
            'read'        => " AND is_read = 1",
            'he_thong'    => " AND type = 'he_thong'",
            'tin_dang'    => " AND type = 'tin_dang'",
            'thanh_toan'  => " AND type = 'thanh_toan'",
            'chat'        => " AND type = 'chat'",
            'bao_mat'     => " AND type = 'bao_mat'",
            default       => ""
        };
    }
}
