<?php
/**
 * ConversationRepository – Thực hiện các câu lệnh SQL tác động lên bảng `hoi_thoai`.
 * Tuân thủ SOLID, Repository Pattern.
 */
class ConversationRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function findById(int $id): ?stdClass
    {
        $this->db->query("SELECT c.*, 
                                 cu.ten as customer_name,
                                 se.ten as seller_name,
                                 st.ten as staff_name
                          FROM hoi_thoai c
                          LEFT JOIN nguoi_dung cu ON c.customer_id = cu.id
                          LEFT JOIN nguoi_dung se ON c.seller_id = se.id
                          LEFT JOIN nguoi_dung st ON c.staff_id = st.id
                          WHERE c.id = :id");
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        return $row ?: null;
    }

    /**
     * Tìm hội thoại đang hoạt động dựa trên các bên.
     */
    public function findActive(string $type, ?int $customerId, ?string $guestToken, ?int $sellerId = null): ?stdClass
    {
        $sql = "SELECT * FROM hoi_thoai WHERE type = :type AND status <> 'closed'";
        $binds = [':type' => $type];

        if ($customerId !== null) {
            $sql .= " AND customer_id = :cust_id";
            $binds[':cust_id'] = $customerId;
        } elseif ($guestToken !== null) {
            $sql .= " AND customer_guest_token = :guest";
            $binds[':guest'] = $guestToken;
        }

        if ($sellerId !== null) {
            $sql .= " AND seller_id = :seller_id";
            $binds[':seller_id'] = $sellerId;
        }

        $sql .= " ORDER BY id DESC LIMIT 1";

        $this->db->query($sql);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }

        $row = $this->db->single();
        return $row ?: null;
    }

    /**
     * Tạo mới cuộc hội thoại.
     */
    public function create(array $data): int
    {
        $this->db->query("INSERT INTO hoi_thoai (customer_id, customer_guest_token, seller_id, staff_id, type, status, title)
                          VALUES (:cust_id, :guest, :seller_id, :staff_id, :type, :status, :title)");
        
        $this->db->bind(':cust_id',  $data['customer_id'] ?? null);
        $this->db->bind(':guest',    $data['customer_guest_token'] ?? null);
        $this->db->bind(':seller_id', $data['seller_id'] ?? null);
        $this->db->bind(':staff_id',  $data['staff_id'] ?? null);
        $this->db->bind(':type',     $data['type']);
        $this->db->bind(':status',   $data['status'] ?? 'waiting');
        $this->db->bind(':title',    $data['title'] ?? null);

        if ($this->db->execute()) {
            $this->db->query("SELECT LAST_INSERT_ID() as last_id");
            return (int)($this->db->single()->last_id ?? 0);
        }
        return 0;
    }

    /**
     * Lấy các hội thoại liên quan đến người dùng hiện tại (bên mua, bên bán hoặc CSKH).
     */
    public function listByUser(int $userId): array
    {
        $this->db->query("SELECT c.*, 
                                 cu.ten as customer_name,
                                 se.ten as seller_name,
                                 st.ten as staff_name
                          FROM hoi_thoai c
                          LEFT JOIN nguoi_dung cu ON c.customer_id = cu.id
                          LEFT JOIN nguoi_dung se ON c.seller_id = se.id
                          LEFT JOIN nguoi_dung st ON c.staff_id = st.id
                          WHERE c.customer_id = :uid OR c.seller_id = :uid OR c.staff_id = :uid
                          ORDER BY c.is_pinned DESC, c.updated_at DESC");
        $this->db->bind(':uid', $userId);
        return $this->db->resultSet();
    }

    /**
     * Lấy danh sách hội thoại cho Admin/CSKH.
     */
    public function listAll(string $status = 'all', string $type = ''): array
    {
        $where = [];
        $binds = [];

        if (in_array($status, ['waiting', 'open', 'closed'], true)) {
            $where[] = "c.status = :status";
            $binds[':status'] = $status;
        }

        if (!empty($type)) {
            $where[] = "c.type = :type";
            $binds[':type'] = $type;
        }

        $sql = "SELECT c.*, 
                       cu.ten as customer_name,
                       se.ten as seller_name,
                       st.ten as staff_name
                FROM hoi_thoai c
                LEFT JOIN nguoi_dung cu ON c.customer_id = cu.id
                LEFT JOIN nguoi_dung se ON c.seller_id = se.id
                LEFT JOIN nguoi_dung st ON c.staff_id = st.id";

        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY c.is_pinned DESC, c.updated_at DESC LIMIT 100";

        $this->db->query($sql);
        foreach ($binds as $k => $v) {
            $this->db->bind($k, $v);
        }

        return $this->db->resultSet();
    }

    /**
     * Cập nhật trạng thái hội thoại.
     */
    public function updateStatus(int $id, string $status): bool
    {
        $this->db->query("UPDATE hoi_thoai SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Chuyển cuộc trò chuyện cho nhân viên khác.
     */
    public function transfer(int $id, int $staffId): bool
    {
        $this->db->query("UPDATE hoi_thoai SET staff_id = :staff, status = 'open' WHERE id = :id");
        $this->db->bind(':staff', $staffId);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Ghim cuộc trò chuyện.
     */
    public function pin(int $id, bool $pin): bool
    {
        $this->db->query("UPDATE hoi_thoai SET is_pinned = :pin WHERE id = :id");
        $this->db->bind(':pin', $pin ? 1 : 0);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
