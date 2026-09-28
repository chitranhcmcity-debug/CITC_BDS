<?php
/**
 * StatisticService – Xử lý các phép toán thống kê, tỷ lệ chuyển đổi, CTR và xếp hạng tin đăng.
 * Tuân thủ SOLID, Service Pattern.
 */
class StatisticService
{
    private AnalyticsRepository $repo;

    public function __construct(AnalyticsRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Tính toán tổng quan chỉ số cho Dashboard.
     */
    public function getSummary(int $ownerId, string $start, string $end, string $postFilter = ''): array
    {
        $summary = $this->repo->summary($ownerId, $start, $end, $postFilter);
        
        $views = (int)($summary['views'] ?? 0);
        $calls = (int)($summary['calls'] ?? 0);
        $chats = (int)($summary['chats'] ?? 0);
        $saves = (int)($summary['saves'] ?? 0);
        $shares = (int)($summary['shares'] ?? 0);
        $phones = (int)($summary['phones'] ?? 0);
        $zalos = (int)($summary['zalos'] ?? 0);
        
        // Tổng số liên hệ (Lead) = call + chat + zalo + phone
        $contacts = $calls + $chats + $zalos + $phones;

        // Công thức CTR = (Call + Chat) / View
        $ctr = $views > 0 ? (100 * ($calls + $chats) / $views) : 0.0;

        // Công thức Conversion = (Call + Chat + Save) / View
        $conversionRate = $views > 0 ? (100 * ($calls + $chats + $saves) / $views) : 0.0;

        return array_merge($summary, [
            'contacts'        => $contacts,
            'ctr'             => round($ctr, 2),
            'conversion_rate' => round($conversionRate, 2),
        ]);
    }

    /**
     * Tính toán chỉ số thống kê chi tiết cho một tin đăng cụ thể.
     */
    public function getPostSummary(int $postId, string $start, string $end): array
    {
        $summary = $this->repo->postSummary($postId, $start, $end);

        $views = (int)($summary['views'] ?? 0);
        $calls = (int)($summary['calls'] ?? 0);
        $chats = (int)($summary['chats'] ?? 0);
        $saves = (int)($summary['saves'] ?? 0);
        $shares = (int)($summary['shares'] ?? 0);
        $phones = (int)($summary['phones'] ?? 0);
        $zalos = (int)($summary['zalos'] ?? 0);

        $contacts = $calls + $chats + $zalos + $phones;

        // Công thức CTR = (Call + Chat) / View
        $ctr = $views > 0 ? (100 * ($calls + $chats) / $views) : 0.0;

        // Công thức Conversion = (Call + Chat + Save) / View
        $conversionRate = $views > 0 ? (100 * ($calls + $chats + $saves) / $views) : 0.0;

        return array_merge($summary, [
            'contacts'        => $contacts,
            'ctr'             => round($ctr, 2),
            'conversion_rate' => round($conversionRate, 2),
        ]);
    }

    /**
     * Lấy bài viết xem nhiều nhất.
     */
    public function getMostViewedPost(int $ownerId, string $start, string $end, string $postFilter = ''): ?object
    {
        $posts = $this->repo->topPosts($ownerId, $start, $end, 'view', 1, 0, $postFilter);
        return $posts[0] ?? null;
    }

    /**
     * Lấy bài viết có tỷ lệ chuyển đổi tốt nhất.
     */
    public function getBestConversionPost(int $ownerId, string $start, string $end, string $postFilter = ''): ?object
    {
        // Ta lấy danh sách sắp xếp theo tỷ lệ chuyển đổi
        $posts = $this->repo->topPosts($ownerId, $start, $end, 'conversion', 1, 0, $postFilter);
        return $posts[0] ?? null;
    }
}
