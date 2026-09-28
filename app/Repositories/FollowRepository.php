<?php

namespace App\Repositories;

use App\Models\Database;
use PDO;

/**
 * FollowRepository – Quản lý quan hệ follow giữa người dùng.
 * Bảng: follows
 */
class FollowRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Theo dõi người đăng.
     *
     * @param  int  $followerId  ID người theo dõi
     * @param  int  $followingId  ID người được theo dõi
     */
    public function follow(int $followerId, int $followingId): bool
    {
        // INSERT IGNORE để không lỗi khi đã follow rồi
        $this->db->query(
            'INSERT IGNORE INTO follows (follower_id, following_id) VALUES (:fler, :fling)'
        );
        $this->db->bind(':fler', $followerId, PDO::PARAM_INT);
        $this->db->bind(':fling', $followingId, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Bỏ theo dõi.
     */
    public function unfollow(int $followerId, int $followingId): bool
    {
        $this->db->query(
            'DELETE FROM follows WHERE follower_id = :fler AND following_id = :fling'
        );
        $this->db->bind(':fler', $followerId, PDO::PARAM_INT);
        $this->db->bind(':fling', $followingId, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Kiểm tra đã follow chưa.
     */
    public function isFollowing(int $followerId, int $followingId): bool
    {
        $this->db->query(
            'SELECT 1 FROM follows
             WHERE follower_id = :fler AND following_id = :fling LIMIT 1'
        );
        $this->db->bind(':fler', $followerId, PDO::PARAM_INT);
        $this->db->bind(':fling', $followingId, PDO::PARAM_INT);

        return (bool) $this->db->single();
    }

    /**
     * Đếm số người theo dõi.
     */
    public function countFollowers(int $userId): int
    {
        $this->db->query(
            'SELECT COUNT(*) AS n FROM follows WHERE following_id = :uid'
        );
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);
        $row = $this->db->single();

        return (int) ($row->n ?? 0);
    }
}
