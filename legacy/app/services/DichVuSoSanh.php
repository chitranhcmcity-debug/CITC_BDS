<?php
/**
 * CompareService – Nghiệp vụ so sánh Bất động sản.
 * Giới hạn tối đa 4 BĐS, tự động đồng bộ session khi đăng nhập, ghi nhận Analytics.
 * Tuân thủ SOLID, Service Pattern.
 */
class CompareService
{
    private CompareRepository $repo;
    private AnalyticsService $analytics;
    private string $cachePrefix;

    public function __construct()
    {
        require_once APP_ROOT . '/app/repositories/CompareRepository.php';
        require_once APP_ROOT . '/app/services/DichVuPhanTich.php';
        $this->repo = new CompareRepository();
        $this->analytics = new AnalyticsService();
        $this->cachePrefix = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'timnhadat_comp_cache_';
    }

    /**
     * Thêm hoặc xóa tin đăng khỏi danh sách so sánh (Giới hạn tối đa 4 tin).
     */
    public function toggleCompare(int|string $userOrSession, int $postId, bool $isLoggedIn): array
    {
        // 1. Kiểm tra tin đăng tồn tại
        $db = new Database();
        $db->query("SELECT tieu_de FROM du_an WHERE id = :id AND deleted_at IS NULL LIMIT 1");
        $db->bind(':id', $postId);
        $post = $db->single();
        if (!$post) {
            return ['success' => false, 'message' => 'Tin đăng không tồn tại hoặc đã bị ẩn.'];
        }

        // 2. Kiểm tra trạng thái hiện tại
        $isComp = $this->repo->isCompared($userOrSession, $postId, $isLoggedIn);
        if ($isComp) {
            // Đã có -> Xóa khỏi so sánh
            $ok = $this->repo->remove($userOrSession, $postId, $isLoggedIn);
            if ($ok) {
                $this->clearCache($userOrSession, $isLoggedIn);
                return ['success' => true, 'action' => 'removed', 'message' => 'Đã xóa khỏi danh sách so sánh.'];
            }
        } else {
            // Chưa có -> Kiểm tra giới hạn 4 tin đăng
            $currentCount = $this->repo->countComparisonList($userOrSession, $isLoggedIn);
            if ($currentCount >= 4) {
                return ['success' => false, 'message' => 'Chỉ được so sánh tối đa 4 bất động sản.'];
            }

            // Tiến hành thêm vào so sánh
            $ok = $this->repo->add($userOrSession, $postId, $isLoggedIn);
            if ($ok) {
                $this->clearCache($userOrSession, $isLoggedIn);
                $this->analytics->logEvent($postId, 'compare', $isLoggedIn ? (int)$userOrSession : null);
                return ['success' => true, 'action' => 'added', 'message' => 'Đã thêm vào danh sách so sánh thành công.'];
            }
        }

        return ['success' => false, 'message' => 'Đã xảy ra lỗi hệ thống, vui lòng thử lại sau.'];
    }

    /**
     * Xóa một tin đăng khỏi danh sách so sánh.
     */
    public function removeCompare(int|string $userOrSession, int $postId, bool $isLoggedIn): bool
    {
        $ok = $this->repo->remove($userOrSession, $postId, $isLoggedIn);
        if ($ok) {
            $this->clearCache($userOrSession, $isLoggedIn);
        }
        return $ok;
    }

    /**
     * Xóa toàn bộ danh sách so sánh.
     */
    public function removeAllCompare(int|string $userOrSession, bool $isLoggedIn): bool
    {
        $ok = $this->repo->removeAll($userOrSession, $isLoggedIn);
        if ($ok) {
            $this->clearCache($userOrSession, $isLoggedIn);
        }
        return $ok;
    }

    /**
     * Lấy danh sách so sánh có Cache 5 phút.
     */
    public function getComparisonListWithCache(int|string $userOrSession, bool $isLoggedIn): array
    {
        $cacheKey = $this->getCacheKey($userOrSession, $isLoggedIn);
        $cached = $this->getCache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $data = $this->repo->getComparisonList($userOrSession, $isLoggedIn);
        $this->setCache($cacheKey, $data);
        return $data;
    }

    /**
     * Đồng bộ hóa danh sách so sánh từ session khách vãng lai sang tài khoản thành viên khi đăng nhập.
     */
    public function syncSessionToUser(string $sessionId, int $userId): void
    {
        $this->repo->syncSessionToUser($sessionId, $userId);
        $this->clearCache($sessionId, false);
        $this->clearCache($userId, true);
    }

    // =========================================================================
    // CÁC HÀM XỬ LÝ CACHE (FILE CACHE 5 PHÚT)
    // =========================================================================

    private function getCacheKey(int|string $userOrSession, bool $isLoggedIn): string
    {
        return ($isLoggedIn ? 'user_' : 'sess_') . md5((string)$userOrSession);
    }

    private function getCache(string $key): ?array
    {
        $file = $this->cachePrefix . $key . '.cache';
        if (!file_exists($file)) {
            return null;
        }
        // Cache hết hạn sau 5 phút (300 giây)
        if (time() - filemtime($file) > 300) {
            @unlink($file);
            return null;
        }
        $content = file_get_contents($file);
        return json_decode($content, false);
    }

    private function setCache(string $key, array $data): void
    {
        $file = $this->cachePrefix . $key . '.cache';
        if (!function_exists('file_put_to_file_safe')) {
            require_once APP_ROOT . '/app/services/DichVuYeuThich.php';
        }
        file_put_to_file_safe($file, json_encode($data));
    }

    private function clearCache(int|string $userOrSession, bool $isLoggedIn): void
    {
        $key = $this->getCacheKey($userOrSession, $isLoggedIn);
        $file = $this->cachePrefix . $key . '.cache';
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}
