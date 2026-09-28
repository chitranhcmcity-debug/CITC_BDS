<?php
/**
 * FavoriteService – Nghiệp vụ quản lý Tin đã lưu.
 * Tích hợp Cache 5 phút, tự động xóa cache khi thay đổi trạng thái, ghi nhận Analytics.
 * Tuân thủ SOLID, Service Pattern.
 */
class FavoriteService
{
    private FavoriteRepository $repo;
    private AnalyticsService $analytics;
    private string $cachePrefix;

    public function __construct()
    {
        require_once APP_ROOT . '/app/repositories/FavoriteRepository.php';
        require_once APP_ROOT . '/app/services/DichVuPhanTich.php';
        $this->repo = new FavoriteRepository();
        $this->analytics = new AnalyticsService();
        $this->cachePrefix = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'timnhadat_fav_cache_';
    }

    /**
     * Lưu hoặc bỏ lưu tin đăng.
     */
    public function toggleFavorite(int $userId, int $postId): array
    {
        // 1. Kiểm tra tin đăng tồn tại và chưa xóa
        $db = new Database();
        $db->query("SELECT tieu_de FROM du_an WHERE id = :id AND deleted_at IS NULL LIMIT 1");
        $db->bind(':id', $postId);
        $post = $db->single();
        if (!$post) {
            return ['success' => false, 'message' => 'Tin đăng không tồn tại hoặc đã bị ẩn.'];
        }

        // 2. Thực hiện lưu hoặc bỏ lưu
        $isFav = $this->repo->isFavorite($userId, $postId);
        if ($isFav) {
            // Đã lưu -> Tiến hành bỏ lưu
            $ok = $this->repo->remove($userId, $postId);
            if ($ok) {
                $this->clearUserCache($userId);
                $this->analytics->logEvent($postId, 'unsave', $userId);
                return ['success' => true, 'action' => 'removed', 'message' => 'Đã bỏ lưu tin đăng thành công.'];
            }
        } else {
            // Chưa lưu -> Tiến hành lưu
            $ok = $this->repo->add($userId, $postId);
            if ($ok) {
                $this->clearUserCache($userId);
                $this->analytics->logEvent($postId, 'save', $userId);
                return ['success' => true, 'action' => 'added', 'message' => 'Đã lưu tin đăng thành công.'];
            }
        }

        return ['success' => false, 'message' => 'Đã xảy ra lỗi hệ thống, vui lòng thử lại sau.'];
    }

    /**
     * Bỏ lưu tin đăng cụ thể.
     */
    public function removeFavorite(int $userId, int $postId): bool
    {
        $ok = $this->repo->remove($userId, $postId);
        if ($ok) {
            $this->clearUserCache($userId);
            $this->analytics->logEvent($postId, 'unsave', $userId);
        }
        return $ok;
    }

    /**
     * Bỏ lưu toàn bộ danh sách.
     */
    public function removeAllFavorites(int $userId): bool
    {
        // Lấy danh sách để ghi nhận analytics unsave cho từng tin đăng (nếu cần)
        $items = $this->repo->getFavorites($userId);
        $ok = $this->repo->removeAll($userId);
        if ($ok) {
            $this->clearUserCache($userId);
            foreach ($items as $item) {
                $this->analytics->logEvent((int)$item->id, 'unsave', $userId);
            }
        }
        return $ok;
    }

    /**
     * Lấy danh sách đã lưu với bộ lọc và Cache 5 phút.
     */
    public function getFavoritesWithCache(int $userId, array $filters = []): array
    {
        $cacheKey = $this->getCacheKey($userId, $filters);
        $cached = $this->getCache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $data = $this->repo->getFavorites($userId, $filters);
        $this->setCache($cacheKey, $data);
        return $data;
    }

    /**
     * Đếm tổng số tin đã lưu.
     */
    public function countFavorites(int $userId): int
    {
        return $this->repo->countFavorites($userId);
    }

    // =========================================================================
    // CÁC HÀM XỬ LÝ CACHE (FILE CACHE 5 PHÚT)
    // =========================================================================

    private function getCacheKey(int $userId, array $filters): string
    {
        return $userId . '_' . md5(serialize($filters));
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
        return json_decode($content, false); // Trả về dạng array các object stdClass giống PDO
    }

    private function setCache(string $key, array $data): void
    {
        $file = $this->cachePrefix . $key . '.cache';
        file_put_to_file_safe($file, json_encode($data));
    }

    private function clearUserCache(int $userId): void
    {
        $pattern = $this->cachePrefix . $userId . '_*.cache';
        $files = glob($pattern);
        if (is_array($files)) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }
}

// Hàm hỗ trợ ghi file an toàn tránh race condition
if (!function_exists('file_put_to_file_safe')) {
    function file_put_to_file_safe(string $filepath, string $data): bool
    {
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $temp = tempnam($dir, 'tmp_');
        if ($temp === false || file_put_contents($temp, $data) === false) {
            return false;
        }
        if (rename($temp, $filepath)) {
            return true;
        }
        @unlink($temp);
        return false;
    }
}
