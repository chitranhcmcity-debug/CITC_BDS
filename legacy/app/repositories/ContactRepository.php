<?php
/**
 * ContactRepository – Quản lý thao tác cơ sở dữ liệu cho yêu cầu liên hệ và CRM.
 */
class ContactRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Tạo yêu cầu liên hệ mới.
     */
    public function create(array $data): int
    {
        $this->db->query("
            INSERT INTO contacts (fullname, phone, email, subject, content, type, status, ip_address, browser, device)
            VALUES (:fullname, :phone, :email, :subject, :content, :type, 'moi', :ip_address, :browser, :device)
        ");
        $this->db->bind(':fullname',   $data['fullname']);
        $this->db->bind(':phone',      $data['phone']);
        $this->db->bind(':email',      $data['email'] ?? null);
        $this->db->bind(':subject',    $data['subject'] ?? null);
        $this->db->bind(':content',    $data['content'] ?? null);
        $this->db->bind(':type',       $data['type'] ?? 'khac');
        $this->db->bind(':ip_address', $data['ip_address'] ?? null);
        $this->db->bind(':browser',    $data['browser'] ?? null);
        $this->db->bind(':device',     $data['device'] ?? null);

        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Lưu file đính kèm.
     */
    public function createAttachment(array $data): int
    {
        $this->db->query("
            INSERT INTO contact_attachments (contact_id, file_name, file_path, file_type, file_size)
            VALUES (:contact_id, :file_name, :file_path, :file_type, :file_size)
        ");
        $this->db->bind(':contact_id', $data['contact_id']);
        $this->db->bind(':file_name',  $data['file_name']);
        $this->db->bind(':file_path',  $data['file_path']);
        $this->db->bind(':file_type',  $data['file_type'] ?? null);
        $this->db->bind(':file_size',  $data['file_size']);

        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Tìm liên hệ theo ID.
     */
    public function findById(int $id): ?array
    {
        $this->db->query("
            SELECT c.*, u.ten AS admin_name, u.email AS admin_email 
            FROM contacts c
            LEFT JOIN nguoi_dung u ON c.assigned_admin = u.id
            WHERE c.id = :id
        ");
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        return $row ? (array)$row : null;
    }

    /**
     * Lấy các file đính kèm của một liên hệ.
     */
    public function getAttachments(int $contactId): array
    {
        $this->db->query("SELECT * FROM contact_attachments WHERE contact_id = :contact_id");
        $this->db->bind(':contact_id', $contactId);
        return $this->db->resultSet() ?: [];
    }

    /**
     * Lấy lịch sử yêu cầu của thành viên theo email hoặc số điện thoại.
     */
    public function getHistory(string $email, string $phone, int $limit = 20, int $offset = 0): array
    {
        $this->db->query("
            SELECT * FROM contacts 
            WHERE email = :email OR phone = :phone 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        $this->db->bind(':email',  $email);
        $this->db->bind(':phone',  $phone);
        $this->db->bind(':limit',  $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet() ?: [];
    }

    /**
     * Đếm tổng số lịch sử liên hệ của thành viên.
     */
    public function countHistory(string $email, string $phone): int
    {
        $this->db->query("SELECT COUNT(*) FROM contacts WHERE email = :email OR phone = :phone");
        $this->db->bind(':email', $email);
        $this->db->bind(':phone', $phone);
        return (int)$this->db->singleColumn();
    }

    /**
     * Cập nhật thông tin liên hệ (CRM - phân công, trạng thái, ghi chú).
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        if (array_key_exists('status', $data)) {
            $fields[] = 'status = :status';
            $params[':status'] = $data['status'];
        }
        if (array_key_exists('assigned_admin', $data)) {
            $fields[] = 'assigned_admin = :assigned_admin';
            $params[':assigned_admin'] = $data['assigned_admin'];
        }
        if (array_key_exists('note', $data)) {
            $fields[] = 'note = :note';
            $params[':note'] = $data['note'];
        }

        if (empty($fields)) {
            return false;
        }

        $this->db->query("UPDATE contacts SET " . implode(', ', $fields) . " WHERE id = :id");
        foreach ($params as $param => $val) {
            $this->db->bind($param, $val);
        }
        return $this->db->execute();
    }

    /**
     * Xóa liên hệ (Admin).
     */
    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM contacts WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Lấy danh sách liên hệ lọc nâng cao phục vụ trang CRM Admin.
     */
    public function getAll(array $filters, int $limit = 20, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'c.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $where[] = 'c.type = :type';
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['assigned_admin'])) {
            $where[] = 'c.assigned_admin = :assigned_admin';
            $params[':assigned_admin'] = (int)$filters['assigned_admin'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(c.fullname LIKE :search OR c.phone LIKE :search OR c.email LIKE :search OR c.subject LIKE :search OR c.content LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'c.created_at >= :date_from';
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'c.created_at <= :date_to';
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $this->db->query("
            SELECT c.*, u.ten AS admin_name 
            FROM contacts c
            LEFT JOIN nguoi_dung u ON c.assigned_admin = u.id
            $whereClause
            ORDER BY c.created_at DESC
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
     * Đếm tổng số liên hệ theo bộ lọc (phân trang).
     */
    public function countAll(array $filters): int
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $where[] = 'type = :type';
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['assigned_admin'])) {
            $where[] = 'assigned_admin = :assigned_admin';
            $params[':assigned_admin'] = (int)$filters['assigned_admin'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(fullname LIKE :search OR phone LIKE :search OR email LIKE :search OR subject LIKE :search OR content LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= :date_from';
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= :date_to';
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $this->db->query("SELECT COUNT(*) FROM contacts $whereClause");
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        return (int)$this->db->singleColumn();
    }

    /**
     * Thống kê báo cáo CRM.
     */
    public function getAnalytics(): array
    {
        $stats = [];

        // Thống kê theo trạng thái
        $this->db->query("SELECT status, COUNT(*) as count FROM contacts GROUP BY status");
        $stats['status'] = $this->db->resultSet() ?: [];

        // Thống kê theo loại yêu cầu
        $this->db->query("SELECT type, COUNT(*) as count FROM contacts GROUP BY type");
        $stats['type'] = $this->db->resultSet() ?: [];

        // Thống kê thiết bị phổ biến
        $this->db->query("SELECT device, COUNT(*) as count FROM contacts GROUP BY device");
        $stats['device'] = $this->db->resultSet() ?: [];

        return $stats;
    }
}
