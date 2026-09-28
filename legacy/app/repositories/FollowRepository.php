<?php
/**
 * FollowRepository – Quản lý quan hệ follow giữa người dùng.
 * Bảng: theo_doi
 */
class FollowRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Theo dõi người đăng.
     *
     * @param  int $followerId  ID người theo dõi
     * @param  int $followingId ID người được theo dõi
     * @return bool
     */
    public function follow(int $followerId, int $followingId): bool
    {
        // INSERT IGNORE để không lỗi khi đã follow rồi
        $this->db->query(
            "INSERT IGNORE INTO theo_doi (follower_id, following_id) VALUES (:fler, :fling)"
        );
        $this->db->bind(':fler',  $followerId,  PDO::PARAM_INT);
        $this->db->bind(':fling', $followingId, PDO::PARAM_INT);
        return $this->db->execute();
    }

    /**
     * Bỏ theo dõi.
     *
     * @param  int $followerId
     * @param  int $followingId
     * @return bool
     */
    public function unfollow(int $followerId, int $followingId): bool
    {
        $this->db->query(
            "DELETE FROM theo_doi WHERE follower_id = :fler AND following_id = :fling"
        );
        $this->db->bind(':fler',  $followerId,  PDO::PARAM_INT);
        $this->db->bind(':fling', $followingId, PDO::PARAM_INT);
        return $this->db->execute();
    }

    /**
     * Kiểm tra đã follow chưa.
     *
     * @param  int $followerId
     * @param  int $followingId
     * @return bool
     */
    public function isFollowing(int $followerId, int $followingId): bool
    {
        $this->db->query(
            "SELECT 1 FROM theo_doi
             WHERE follower_id = :fler AND following_id = :fling LIMIT 1"
        );
        $this->db->bind(':fler',  $followerId,  PDO::PARAM_INT);
        $this->db->bind(':fling', $followingId, PDO::PARAM_INT);
        return (bool)$this->db->single();
    }

    /**
     * Đếm số người theo dõi.
     *
     * @param  int $userId
     * @return int
     */
    public function countFollowers(int $userId): int
    {
        $this->db->query(
            "SELECT COUNT(*) AS n FROM theo_doi WHERE following_id = :uid"
        );
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);
        $row = $this->db->single();
        return (int)($row->n ?? 0);
    }
}
