<?php

namespace App\Repositories;

use App\Models\Database;
use App\Models\The;
use PDO;

/** All SQL used by the analytics module lives in this repository. */
class AnalyticsRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function findPost(int $postId): object|false
    {
        $this->db->query('SELECT id, ma_nguoi_dung, tieu_de, anh_thu_nho, trang_thai, goi_vip,
                                 ngay_tao, ngay_het_han_vip, duong_dan
                          FROM du_an WHERE id = :id');
        $this->db->bind(':id', $postId, PDO::PARAM_INT);

        return $this->db->single();
    }

    public function findOwnedPost(int $postId, int $ownerId): object|false
    {
        $this->db->query('SELECT id, ma_nguoi_dung, tieu_de, anh_thu_nho, trang_thai, goi_vip,
                                 ngay_tao, ngay_het_han_vip, duong_dan
                          FROM du_an WHERE id = :id AND ma_nguoi_dung = :owner');
        $this->db->bind(':id', $postId, PDO::PARAM_INT);
        $this->db->bind(':owner', $ownerId, PDO::PARAM_INT);

        return $this->db->single();
    }

    public function hasRecentView(int $postId, string $visitorHash, int $minutes = 30): bool
    {
        $minutes = max(1, min(1440, $minutes));
        $this->db->query("SELECT 1 FROM post_analytics
                          WHERE post_id = :post AND type = 'view' AND visitor_hash = :visitor
                            AND created_at >= DATE_SUB(NOW(), INTERVAL {$minutes} MINUTE)
                          LIMIT 1");
        $this->db->bind(':post', $postId, PDO::PARAM_INT);
        $this->db->bind(':visitor', $visitorHash);

        return (bool) $this->db->single();
    }

    public function insert(array $event): bool
    {
        $this->db->query('INSERT IGNORE INTO post_analytics
            (post_id, user_id, actor_user_id, type, value, ip_address, user_agent,
             visitor_hash, referrer, source, metadata, dedupe_window, created_at)
            VALUES (:post, :owner, :actor, :type, :value, :ip, :agent,
                    :visitor, :referrer, :source, :metadata, :window, NOW())');
        $this->db->bind(':post', $event['post_id'], PDO::PARAM_INT);
        $this->db->bind(':owner', $event['user_id']);
        $this->db->bind(':actor', $event['actor_user_id']);
        $this->db->bind(':type', $event['type']);
        $this->db->bind(':value', $event['value'], PDO::PARAM_INT);
        $this->db->bind(':ip', $event['ip_address']);
        $this->db->bind(':agent', $event['user_agent']);
        $this->db->bind(':visitor', $event['visitor_hash']);
        $this->db->bind(':referrer', $event['referrer']);
        $this->db->bind(':source', $event['source']);
        $this->db->bind(':metadata', $event['metadata']);
        $this->db->bind(':window', $event['dedupe_window']);

        return $this->db->execute() && $this->db->rowCount() > 0;
    }

    public function incrementLegacyCounter(int $postId, string $column): void
    {
        if (! in_array($column, ['luot_xem', 'luot_click_sdt'], true)) {
            return;
        }
        $this->db->query("UPDATE du_an SET {$column} = COALESCE({$column}, 0) + 1 WHERE id = :id");
        $this->db->bind(':id', $postId, PDO::PARAM_INT);
        $this->db->execute();
    }

    public function summary(int $ownerId, string $start, string $end, string $postFilter = ''): array
    {
        [$filterSql, $filterValue] = $this->postFilter($postFilter);
        $this->db->query("SELECT
            COALESCE(SUM(CASE WHEN a.type='view' THEN a.value ELSE 0 END),0) AS views,
            COALESCE(SUM(CASE WHEN a.type='call' THEN a.value ELSE 0 END),0) AS calls,
            COALESCE(SUM(CASE WHEN a.type='chat' THEN a.value ELSE 0 END),0) AS chats,
            COALESCE(SUM(CASE WHEN a.type='save' THEN a.value ELSE 0 END),0) AS saves,
            COALESCE(SUM(CASE WHEN a.type='share' THEN a.value ELSE 0 END),0) AS shares,
            COALESCE(SUM(CASE WHEN a.type='phone' THEN a.value ELSE 0 END),0) AS phones,
            COALESCE(SUM(CASE WHEN a.type='zalo' THEN a.value ELSE 0 END),0) AS zalos,
            COALESCE(SUM(CASE WHEN a.type IN ('call','chat','zalo','contact') THEN a.value ELSE 0 END),0) AS contacts
          FROM post_analytics a
          JOIN du_an p ON p.id = a.post_id
          WHERE p.ma_nguoi_dung = :owner AND a.created_at BETWEEN :start AND :end {$filterSql}");
        $this->db->bind(':owner', $ownerId, PDO::PARAM_INT);
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        if ($filterValue !== null) {
            $this->db->bind(':post_filter', $filterValue, PDO::PARAM_INT);
        }
        $row = $this->db->single();

        return $row ? (array) $row : [];
    }

    public function postSummary(int $postId, string $start, string $end): array
    {
        $this->db->query("SELECT
            COALESCE(SUM(CASE WHEN type='view' THEN value ELSE 0 END),0) AS views,
            COALESCE(SUM(CASE WHEN type='call' THEN value ELSE 0 END),0) AS calls,
            COALESCE(SUM(CASE WHEN type='chat' THEN value ELSE 0 END),0) AS chats,
            COALESCE(SUM(CASE WHEN type='save' THEN value ELSE 0 END),0) AS saves,
            COALESCE(SUM(CASE WHEN type='share' THEN value ELSE 0 END),0) AS shares,
            COALESCE(SUM(CASE WHEN type='phone' THEN value ELSE 0 END),0) AS phones,
            COALESCE(SUM(CASE WHEN type='zalo' THEN value ELSE 0 END),0) AS zalos,
            COALESCE(SUM(CASE WHEN type IN ('call','chat','zalo','contact') THEN value ELSE 0 END),0) AS contacts
          FROM post_analytics WHERE post_id = :post AND created_at BETWEEN :start AND :end");
        $this->db->bind(':post', $postId, PDO::PARAM_INT);
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        $row = $this->db->single();

        return $row ? (array) $row : [];
    }

    public function dailySeries(int $ownerId, string $start, string $end, ?int $postId = null, string $postFilter = ''): array
    {
        [$filterSql, $filterValue] = $this->postFilter($postFilter);
        $postSql = $postId ? ' AND a.post_id = :post' : '';
        $this->db->query("SELECT DATE(a.created_at) AS period,
            SUM(CASE WHEN a.type='view' THEN a.value ELSE 0 END) AS views,
            SUM(CASE WHEN a.type='call' THEN a.value ELSE 0 END) AS calls,
            SUM(CASE WHEN a.type='chat' THEN a.value ELSE 0 END) AS chats,
            SUM(CASE WHEN a.type='save' THEN a.value ELSE 0 END) AS saves,
            SUM(CASE WHEN a.type='share' THEN a.value ELSE 0 END) AS shares
          FROM post_analytics a JOIN du_an p ON p.id=a.post_id
          WHERE p.ma_nguoi_dung=:owner AND a.created_at BETWEEN :start AND :end {$postSql} {$filterSql}
          GROUP BY DATE(a.created_at) ORDER BY period ASC");
        $this->db->bind(':owner', $ownerId, PDO::PARAM_INT);
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        if ($postId) {
            $this->db->bind(':post', $postId, PDO::PARAM_INT);
        }
        if ($filterValue !== null) {
            $this->db->bind(':post_filter', $filterValue, PDO::PARAM_INT);
        }

        return $this->db->resultSet();
    }

    public function monthlyViews(int $ownerId, ?int $postId = null): array
    {
        $postSql = $postId ? ' AND a.post_id = :post' : '';
        $this->db->query("SELECT DATE_FORMAT(a.created_at, '%Y-%m') AS period, SUM(a.value) AS views
          FROM post_analytics a JOIN du_an p ON p.id=a.post_id
          WHERE p.ma_nguoi_dung=:owner AND a.type='view'
            AND a.created_at >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 11 MONTH) {$postSql}
          GROUP BY DATE_FORMAT(a.created_at, '%Y-%m') ORDER BY period ASC");
        $this->db->bind(':owner', $ownerId, PDO::PARAM_INT);
        if ($postId) {
            $this->db->bind(':post', $postId, PDO::PARAM_INT);
        }

        return $this->db->resultSet();
    }

    public function sources(int $ownerId, string $start, string $end, ?int $postId = null): array
    {
        $postSql = $postId ? ' AND a.post_id = :post' : '';
        $this->db->query("SELECT a.source, SUM(a.value) AS total
          FROM post_analytics a JOIN du_an p ON p.id=a.post_id
          WHERE p.ma_nguoi_dung=:owner AND a.type='view' AND a.created_at BETWEEN :start AND :end {$postSql}
          GROUP BY a.source ORDER BY total DESC");
        $this->db->bind(':owner', $ownerId, PDO::PARAM_INT);
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        if ($postId) {
            $this->db->bind(':post', $postId, PDO::PARAM_INT);
        }

        return $this->db->resultSet();
    }

    public function topPosts(int $ownerId, string $start, string $end, string $sort, int $limit, int $offset = 0, string $postFilter = ''): array
    {
        $orderMap = [
            'view' => 'views DESC', 'contact' => 'contacts DESC', 'chat' => 'chats DESC',
            'save' => 'saves DESC', 'share' => 'shares DESC', 'conversion' => 'conversion_rate DESC',
        ];
        $order = $orderMap[$sort] ?? $orderMap['view'];
        [$filterSql, $filterValue] = $this->postFilter($postFilter);
        $this->db->query("SELECT p.id, p.tieu_de, p.anh_thu_nho, p.trang_thai, p.goi_vip, p.ngay_het_han_vip,
            COALESCE(SUM(CASE WHEN a.type='view' THEN a.value ELSE 0 END),0) AS views,
            COALESCE(SUM(CASE WHEN a.type='call' THEN a.value ELSE 0 END),0) AS calls,
            COALESCE(SUM(CASE WHEN a.type='chat' THEN a.value ELSE 0 END),0) AS chats,
            COALESCE(SUM(CASE WHEN a.type='save' THEN a.value ELSE 0 END),0) AS saves,
            COALESCE(SUM(CASE WHEN a.type='share' THEN a.value ELSE 0 END),0) AS shares,
            COALESCE(SUM(CASE WHEN a.type IN ('call','chat','zalo','contact') THEN a.value ELSE 0 END),0) AS contacts,
            CASE WHEN SUM(CASE WHEN a.type='view' THEN a.value ELSE 0 END) > 0
              THEN ROUND(100 * SUM(CASE WHEN a.type IN ('call','chat') THEN a.value ELSE 0 END) /
                   SUM(CASE WHEN a.type='view' THEN a.value ELSE 0 END), 2) ELSE 0 END AS conversion_rate
          FROM du_an p LEFT JOIN post_analytics a ON a.post_id=p.id AND a.created_at BETWEEN :start AND :end
          WHERE p.ma_nguoi_dung=:owner {$filterSql}
          GROUP BY p.id ORDER BY {$order}, p.id DESC LIMIT :limit OFFSET :offset");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        $this->db->bind(':owner', $ownerId, PDO::PARAM_INT);
        if ($filterValue !== null) {
            $this->db->bind(':post_filter', $filterValue, PDO::PARAM_INT);
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    public function countPosts(int $ownerId, string $postFilter = ''): int
    {
        [$filterSql, $filterValue] = $this->postFilter($postFilter);
        $this->db->query("SELECT COUNT(*) AS total FROM du_an p WHERE p.ma_nguoi_dung=:owner {$filterSql}");
        $this->db->bind(':owner', $ownerId, PDO::PARAM_INT);
        if ($filterValue !== null) {
            $this->db->bind(':post_filter', $filterValue, PDO::PARAM_INT);
        }
        $row = $this->db->single();

        return (int) ($row->total ?? 0);
    }

    private function postFilter(string $filter): array
    {
        return match ($filter) {
            'vip' => [' AND p.goi_vip > 0 AND p.ngay_het_han_vip >= NOW()', null],
            'normal' => [' AND (p.goi_vip = 0 OR p.goi_vip IS NULL)', null],
            'expired' => [' AND p.goi_vip > 0 AND p.ngay_het_han_vip < NOW()', null],
            default => ['', null],
        };
    }

    /**
     * Lấy thống kê tổng hợp tin đăng phục vụ Admin Dashboard.
     */
    public function getAdminStats(): array
    {
        $this->db->query("SELECT 
                            COUNT(*) AS total,
                            SUM(CASE WHEN trang_thai = 'cho_duyet' THEN 1 ELSE 0 END) AS pending,
                            SUM(CASE WHEN trang_thai = 'tu_choi' THEN 1 ELSE 0 END) AS rejected,
                            SUM(CASE WHEN goi_vip > 0 AND ngay_het_han_vip >= NOW() THEN 1 ELSE 0 END) AS vip,
                            SUM(CASE WHEN ngay_het_han < NOW() THEN 1 ELSE 0 END) AS expired,
                            SUM(CASE WHEN trang_thai = 'khoa' THEN 1 ELSE 0 END) AS locked
                          FROM du_an WHERE deleted_at IS NULL");
        $row = $this->db->single();

        return [
            'total' => (int) ($row->total ?? 0),
            'pending' => (int) ($row->pending ?? 0),
            'rejected' => (int) ($row->rejected ?? 0),
            'vip' => (int) ($row->vip ?? 0),
            'expired' => (int) ($row->expired ?? 0),
            'locked' => (int) ($row->locked ?? 0),
        ];
    }
}
