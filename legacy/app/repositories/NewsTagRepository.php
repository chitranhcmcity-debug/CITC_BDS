<?php
/**
 * NewsTagRepository – Truy cập bảng the (tag) và bai_viet_the.
 */
class NewsTagRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Lấy tất cả tag của một bài viết.
     */
    public function findByNews(int $newsId): array
    {
        $this->db->query(
            "SELECT t.* FROM the t
             JOIN bai_viet_the bt ON bt.ma_the = t.id
             WHERE bt.ma_bai_viet = :newsId
             ORDER BY t.ten ASC"
        );
        $this->db->bind(':newsId', $newsId, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Lấy tag phổ biến kèm số bài (tag cloud sidebar).
     */
    public function findPopular(int $limit = 20): array
    {
        $this->db->query(
            "SELECT t.id, t.ten, t.duong_dan, COUNT(bt.ma_bai_viet) AS so_bai
             FROM the t
             JOIN bai_viet_the bt ON bt.ma_the = t.id
             JOIN bai_viet b ON b.id = bt.ma_bai_viet AND b.trang_thai = 'xuat_ban'
             GROUP BY t.id
             ORDER BY so_bai DESC, t.ten ASC
             LIMIT :limit"
        );
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Lấy tag theo slug.
     */
    public function findBySlug(string $slug): mixed
    {
        $this->db->query(
            "SELECT t.*, COUNT(bt.ma_bai_viet) AS so_bai
             FROM the t
             LEFT JOIN bai_viet_the bt ON bt.ma_the = t.id
             WHERE t.duong_dan = :slug
             GROUP BY t.id"
        );
        $this->db->bind(':slug', $slug);
        return $this->db->single();
    }

    /**
     * Lấy tất cả tag (cho Admin dropdown / autocomplete).
     */
    public function findAll(): array
    {
        $this->db->query("SELECT * FROM the ORDER BY ten ASC");
        return $this->db->resultSet();
    }

    /**
     * Tạo tag mới nếu chưa tồn tại, trả về ID.
     */
    public function findOrCreate(string $name): int
    {
        $slug = $this->makeSlug($name);

        $this->db->query("SELECT id FROM the WHERE duong_dan = :slug");
        $this->db->bind(':slug', $slug);
        $row = $this->db->single();
        if ($row) {
            return (int)$row->id;
        }

        $this->db->query("INSERT INTO the (ten, duong_dan) VALUES (:ten, :slug)");
        $this->db->bind(':ten',  $name);
        $this->db->bind(':slug', $slug);
        $this->db->execute();

        $this->db->query("SELECT LAST_INSERT_ID() AS id");
        return (int)$this->db->single()->id;
    }

    /**
     * Đồng bộ tag của bài viết: xóa cũ, thêm mới.
     *
     * @param int   $newsId
     * @param int[] $tagIds
     */
    public function syncTags(int $newsId, array $tagIds): void
    {
        // Xóa hết tag cũ
        $this->db->query("DELETE FROM bai_viet_the WHERE ma_bai_viet = :newsId");
        $this->db->bind(':newsId', $newsId, PDO::PARAM_INT);
        $this->db->execute();

        // Thêm tag mới
        foreach (array_unique($tagIds) as $tagId) {
            $this->db->query(
                "INSERT IGNORE INTO bai_viet_the (ma_bai_viet, ma_the) VALUES (:newsId, :tagId)"
            );
            $this->db->bind(':newsId', $newsId, PDO::PARAM_INT);
            $this->db->bind(':tagId',  (int)$tagId, PDO::PARAM_INT);
            $this->db->execute();
        }
    }

    /**
     * Lấy các ID tag của bài viết.
     */
    public function getTagIds(int $newsId): array
    {
        $this->db->query("SELECT ma_the FROM bai_viet_the WHERE ma_bai_viet = :newsId");
        $this->db->bind(':newsId', $newsId, PDO::PARAM_INT);
        $rows = $this->db->resultSet();
        return array_column($rows, 'ma_the');
    }

    /* =====================================================
       PRIVATE
       ===================================================== */

    private function makeSlug(string $name): string
    {
        $slug = mb_strtolower(trim($name));
        $slug = str_replace(
            ['á','à','ả','ã','ạ','ă','ắ','ặ','ằ','ẳ','ẵ','â','ấ','ầ','ẩ','ẫ','ậ',
             'đ','é','è','ẻ','ẽ','ẹ','ê','ế','ề','ể','ễ','ệ',
             'í','ì','ỉ','ĩ','ị','ó','ò','ỏ','õ','ọ','ô','ố','ồ','ổ','ỗ','ộ','ơ','ớ','ờ','ở','ỡ','ợ',
             'ú','ù','ủ','ũ','ụ','ư','ứ','ừ','ử','ữ','ự','ý','ỳ','ỷ','ỹ','ỵ'],
            ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
             'd','e','e','e','e','e','e','e','e','e','e','e',
             'i','i','i','i','i','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
             'u','u','u','u','u','u','u','u','u','u','u','y','y','y','y','y'],
            $slug
        );
        return preg_replace('/[^a-z0-9]+/', '-', $slug);
    }
}
