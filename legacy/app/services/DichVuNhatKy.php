<?php
/**
 * LogService – Lưu trữ nhật ký hành động chỉnh sửa danh mục của Admin vào bảng `nhat_ky_he_thong`.
 * Tuân thủ SOLID, Service Pattern.
 */
class LogService
{
    /**
     * Ghi nhận hành động của Admin.
     */
    public static function write(int $adminId, string $action, string $modelType, ?int $modelId, string $description): bool
    {
        $db = new Database();
        
        // Xác định địa chỉ IP client
        $ip = '127.0.0.1';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $db->query("
            INSERT INTO nhat_ky_he_thong (user_id, action, model_type, model_id, description, ip_address) 
            VALUES (:uid, :act, :mtype, :mid, :desc, :ip)
        ");
        $db->bind(':uid',   $adminId, PDO::PARAM_INT);
        $db->bind(':act',   $action);
        $db->bind(':mtype', $modelType);
        $db->bind(':mid',   $modelId, PDO::PARAM_INT);
        $db->bind(':desc',  $description);
        $db->bind(':ip',    $ip);

        return $db->execute();
    }

    /**
     * Lấy danh sách logs cho trang quản trị.
     */
    public function getLogs(int $limit = 50, int $offset = 0): array
    {
        $db = new Database();
        $db->query("
            SELECT l.*, u.ten as admin_name 
            FROM nhat_ky_he_thong l
            LEFT JOIN nguoi_dung u ON l.user_id = u.id
            ORDER BY l.created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        $db->bind(':limit', $limit, PDO::PARAM_INT);
        $db->bind(':offset', $offset, PDO::PARAM_INT);
        return $db->resultSet() ?: [];
    }
}
