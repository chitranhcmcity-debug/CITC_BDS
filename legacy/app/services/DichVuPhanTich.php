<?php
/**
 * AnalyticsService – Xử lý ghi nhận thống kê tương tác tin đăng (save, unsave, compare, share)
 * và tổng hợp các biểu đồ, số liệu cho trang Dashboard Analytics của người dùng.
 * Tuân thủ SOLID, Service Pattern.
 */
class AnalyticsService
{
    private AnalyticsRepository $repo;
    private StatisticService $statService;
    private ChartService $chartService;
    private ExportService $exportService;

    public function __construct()
    {
        // Tự động load AnalyticsRepository và các service con
        require_once APP_ROOT . '/app/repositories/AnalyticsRepository.php';
        require_once APP_ROOT . '/app/services/DichVuThongKe.php';
        require_once APP_ROOT . '/app/services/DichVuBieuDo.php';
        require_once APP_ROOT . '/app/services/DichVuXuatDuLieu.php';

        $this->repo = new AnalyticsRepository();
        $this->statService = new StatisticService($this->repo);
        $this->chartService = new ChartService($this->repo);
        $this->exportService = new ExportService();
    }

    public function getStatisticService(): StatisticService
    {
        return $this->statService;
    }

    public function getChartService(): ChartService
    {
        return $this->chartService;
    }

    public function getExportService(): ExportService
    {
        return $this->exportService;
    }

    public function getRepo(): AnalyticsRepository
    {
        return $this->repo;
    }

    /**
     * Ghi nhận sự kiện tương tác bất động sản (phương thức gốc của dự án).
     * Hỗ trợ giới hạn trùng lặp (deduplication) 30 phút cho lượt xem.
     */
    public function record(int $postId, string $type, ?int $actorUserId = null): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI';
        $visitorHash = hash('sha256', $ip . $agent);

        // 1. Kiểm tra chống spam view trùng lặp trong 30 phút
        if ($type === 'view') {
            if ($this->repo->hasRecentView($postId, $visitorHash, 30)) {
                return false;
            }
            // Tăng cột luot_xem trong bảng du_an
            $this->repo->incrementLegacyCounter($postId, 'luot_xem');
        }

        // Tăng cột số điện thoại nếu click xem SĐT/Gọi
        if ($type === 'call' || $type === 'phone') {
            $this->repo->incrementLegacyCounter($postId, 'luot_click_sdt');
        }

        // 2. Chèn bản ghi phân tích vào bảng post_analytics
        $post = $this->repo->findPost($postId);
        $ownerId = $post ? (int)$post->ma_nguoi_dung : null;

        // Xóa cache của trang chi tiết khi có lượt lưu hoặc chia sẻ
        if (in_array($type, ['save', 'share', 'unsave'], true)) {
            $this->invalidateDetailCache($postId);
        }

        return $this->repo->insert([
            'post_id'       => $postId,
            'user_id'       => $ownerId,
            'actor_user_id' => $actorUserId,
            'type'          => $type,
            'value'         => 1,
            'ip_address'    => $ip,
            'user_agent'    => substr($agent, 0, 500),
            'visitor_hash'  => $visitorHash,
            'referrer'      => substr($_SERVER['HTTP_REFERER'] ?? '', 0, 1000),
            'source'        => $this->detectSource(),
            'metadata'      => json_encode([]),
            'dedupe_window' => $type === 'view' ? 30 : null
        ]);
    }

    /**
     * Ghi nhận sự kiện tương tác nâng cao kèm metadata (cho CRM/Interactions).
     */
    public function logEvent(int $postId, string $type, ?int $actorUserId = null, int $value = 1, array $metadata = []): bool
    {
        $post = $this->repo->findPost($postId);
        $ownerId = $post ? (int)$post->ma_nguoi_dung : null;

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI';
        $visitorHash = hash('sha256', $ip . $agent);

        return $this->repo->insert([
            'post_id'       => $postId,
            'user_id'       => $ownerId,
            'actor_user_id' => $actorUserId,
            'type'          => $type,
            'value'         => $value,
            'ip_address'    => $ip,
            'user_agent'    => substr($agent, 0, 500),
            'visitor_hash'  => $visitorHash,
            'referrer'      => substr($_SERVER['HTTP_REFERER'] ?? '', 0, 1000),
            'source'        => $this->detectSource(),
            'metadata'      => json_encode($metadata),
            'dedupe_window' => null
        ]);
    }

    /**
     * Bảng tổng hợp nhanh (Hiệu quả hôm nay & Thay đổi so với hôm qua).
     */
    public function quick(int $userId): array
    {
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd   = date('Y-m-d 23:59:59');
        $yestStart  = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $yestEnd    = date('Y-m-d 23:59:59', strtotime('-1 day'));

        $todayStats = $this->repo->summary($userId, $todayStart, $todayEnd);
        $yestStats  = $this->repo->summary($userId, $yestStart, $yestEnd);

        // Tính CTR hôm nay
        $todayStats['ctr'] = ($todayStats['views'] ?? 0) > 0 
            ? (100 * (($todayStats['calls'] ?? 0) + ($todayStats['chats'] ?? 0)) / $todayStats['views']) 
            : 0;

        $changes = [];
        foreach (['views', 'calls', 'chats', 'saves', 'shares'] as $key) {
            $todayVal = (int)($todayStats[$key] ?? 0);
            $yestVal  = (int)($yestStats[$key] ?? 0);
            if ($yestVal > 0) {
                $changes[$key] = round(100 * ($todayVal - $yestVal) / $yestVal, 1);
            } else {
                $changes[$key] = $todayVal > 0 ? 100 : 0;
            }
        }

        return [
            'current' => $todayStats,
            'changes' => $changes
        ];
    }

    /**
     * Lấy dữ liệu cho toàn bộ Dashboard Analytics.
     * Tích hợp cơ chế Cache 5 phút.
     */
    public function dashboard(int $userId, array $params): array
    {
        $cacheKey = 'dashboard_' . $userId . '_' . md5(json_encode($params));
        $cached = $this->getCache($cacheKey);
        if ($cached !== null) {
            if (isset($cached['most_viewed']) && is_array($cached['most_viewed'])) {
                $cached['most_viewed'] = (object)$cached['most_viewed'];
            }
            if (isset($cached['best_conversion']) && is_array($cached['best_conversion'])) {
                $cached['best_conversion'] = (object)$cached['best_conversion'];
            }
            if (isset($cached['top_posts']) && is_array($cached['top_posts'])) {
                foreach ($cached['top_posts'] as $k => $post) {
                    $cached['top_posts'][$k] = (object)$post;
                }
            }
            return $cached;
        }

        $range = $this->parseRange($params);
        $postFilter = $params['post_filter'] ?? '';
        $sort = $params['sort'] ?? 'view';
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $summary = $this->statService->getSummary($userId, $range['from'], $range['to'], $postFilter);
        $topPosts = $this->repo->topPosts($userId, $range['from'], $range['to'], $sort, $limit, $offset, $postFilter);
        
        foreach ($topPosts as $k => $post) {
            $post->contacts = (int)$post->calls + (int)$post->chats + (int)($post->zalos ?? 0) + (int)($post->phones ?? 0);
            $post->conversion_rate = (float)$post->conversion_rate;
        }

        $totalPosts = $this->repo->countPosts($userId, $postFilter);
        $totalPages = max(1, (int)ceil($totalPosts / $limit));

        $daily = $this->chartService->getDailyData($userId, $range['from'], $range['to'], null, $postFilter);
        $monthly = $this->chartService->getMonthlyData($userId);
        $sources = $this->chartService->getSourcesData($userId, $range['from'], $range['to']);

        $mostViewed = $this->statService->getMostViewedPost($userId, $range['from'], $range['to'], $postFilter);
        $bestConversion = $this->statService->getBestConversionPost($userId, $range['from'], $range['to'], $postFilter);

        $result = [
            'summary'         => $summary,
            'range'           => $range,
            'post_filter'     => $postFilter,
            'most_viewed'     => $mostViewed,
            'best_conversion' => $bestConversion,
            'daily'           => $daily,
            'monthly'         => $monthly,
            'sources'         => $sources,
            'top_posts'       => $topPosts,
            'total_pages'     => $totalPages,
            'page'            => $page,
            'sort'            => $sort
        ];

        $this->setCache($cacheKey, $result);
        return $result;
    }

    /**
     * Lấy dữ liệu báo cáo chi tiết của một tin đăng cụ thể.
     * Tích hợp cơ chế Cache 5 phút.
     */
    public function postDashboard(int $userId, int $postId, array $params): ?array
    {
        $cacheKey = 'post_dashboard_' . $userId . '_' . $postId . '_' . md5(json_encode($params));
        $cached = $this->getCache($cacheKey);
        if ($cached !== null) {
            if (isset($cached['post']) && is_array($cached['post'])) {
                $cached['post'] = (object)$cached['post'];
            }
            return $cached;
        }

        $post = $this->repo->findOwnedPost($postId, $userId);
        if (!$post) return null;

        $range = $this->parseRange($params);
        $summary = $this->statService->getPostSummary($postId, $range['from'], $range['to']);
        $daily = $this->chartService->getDailyData($userId, $range['from'], $range['to'], $postId);
        $monthly = $this->chartService->getMonthlyData($userId, $postId);
        $sources = $this->chartService->getSourcesData($userId, $range['from'], $range['to'], $postId);

        $result = [
            'post'    => $post,
            'summary' => $summary,
            'range'   => $range,
            'daily'   => $daily,
            'monthly' => $monthly,
            'sources' => $sources
        ];

        $this->setCache($cacheKey, $result);
        return $result;
    }

    /**
     * Parse khoảng thời gian lọc dữ liệu.
     */
    public function parseRange(array $params): array
    {
        $period = $params['period'] ?? '30';
        $from = $params['from'] ?? '';
        $to = $params['to'] ?? '';

        $label = '30 ngày qua';
        $today = date('Y-m-d');

        switch ($period) {
            case 'today':
                $from = $today;
                $to = $today;
                $label = 'Hôm nay';
                break;
            case 'yesterday':
                $from = date('Y-m-d', strtotime('-1 day'));
                $to = $from;
                $label = 'Hôm qua';
                break;
            case '7':
                $from = date('Y-m-d', strtotime('-7 days'));
                $to = $today;
                $label = '7 ngày qua';
                break;
            case '30':
                $from = date('Y-m-d', strtotime('-30 days'));
                $to = $today;
                $label = '30 ngày qua';
                break;
            case '90':
                $from = date('Y-m-d', strtotime('-90 days'));
                $to = $today;
                $label = '90 ngày qua';
                break;
            case 'year':
                $from = date('Y-01-01');
                $to = $today;
                $label = 'Năm nay';
                break;
            case 'custom':
                if (empty($from)) $from = date('Y-m-d', strtotime('-30 days'));
                if (empty($to)) $to = $today;
                $label = 'Tùy chọn';
                break;
            default:
                $from = date('Y-m-d', strtotime('-30 days'));
                $to = $today;
                $label = '30 ngày qua';
                $period = '30';
                break;
        }

        return [
            'period' => $period,
            'from'   => $from . ' 00:00:00',
            'to'     => $to . ' 23:59:59',
            'label'  => $label
        ];
    }

    /**
     * Xác định nguồn truy cập từ Referrer.
     */
    private function detectSource(): string
    {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        if (empty($referrer)) return 'direct';
        $host = strtolower(parse_url($referrer, PHP_URL_HOST) ?? '');
        if (empty($host)) return 'direct';
        if (str_contains($host, 'facebook.com') || str_contains($host, 'fb.me')) return 'facebook';
        if (str_contains($host, 'zalo.')) return 'zalo';
        if (str_contains($host, 'google.')) return 'google';
        if (str_contains($host, 't.me') || str_contains($host, 'telegram.')) return 'telegram';
        return 'other';
    }

    // ==========================================
    // CACHE (File-based, 5 phút TTL)
    // ==========================================

    private function getCache(string $key): mixed
    {
        $file = sys_get_temp_dir() . '/bds_analytics_cache_' . md5($key) . '.json';
        if (!file_exists($file)) return null;
        if ((time() - filemtime($file)) > 300) {
            @unlink($file);
            return null;
        }
        $raw = @file_get_contents($file);
        if (!$raw) return null;
        return json_decode($raw, true);
    }

    private function setCache(string $key, mixed $value): void
    {
        $file = sys_get_temp_dir() . '/bds_analytics_cache_' . md5($key) . '.json';
        @file_put_contents($file, json_encode($value, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    private function invalidateDetailCache(int $postId): void
    {
        $relatedKey = 'property_related_' . $postId;
        $file = sys_get_temp_dir() . '/bds_cache_' . md5($relatedKey) . '.json';
        @unlink($file);
    }
}
