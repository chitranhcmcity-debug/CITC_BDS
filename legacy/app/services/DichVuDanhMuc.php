<?php
/**
 * CategoryService – Quản lý nghiệp vụ danh mục, tiện ích, hướng, pháp lý, loại giao dịch.
 * Tích hợp cơ chế Graceful Cache Fallback (Redis / File Cache).
 * Tuân thủ SOLID, Service Pattern.
 */
class CategoryService
{
    public CategoryRepository $catRepo;
    public TransactionTypeRepository $ttRepo;
    public FacilityRepository $facRepo;
    public DirectionRepository $dirRepo;
    public LegalRepository $legalRepo;

    public function __construct()
    {
        $this->catRepo   = new CategoryRepository();
        $this->ttRepo    = new TransactionTypeRepository();
        $this->facRepo   = new FacilityRepository();
        $this->dirRepo   = new DirectionRepository();
        $this->legalRepo = new LegalRepository();
    }

    // ==========================================
    // CACHING UTILITIES (REDIS / FILE FALLBACK)
    // ==========================================

    private function getCache(string $key): ?array
    {
        if (class_exists('Redis')) {
            try {
                $redis = new Redis();
                if ($redis->connect('127.0.0.1', 6379)) {
                    $val = $redis->get($key);
                    return $val ? json_decode($val, true) : null;
                }
            } catch (Throwable) {}
        }
        
        $file = sys_get_temp_dir() . '/timnhadat_cache_' . md5($key) . '.cache';
        if (file_exists($file) && (time() - filemtime($file) < 3600)) {
            return json_decode(file_get_contents($file), true);
        }
        return null;
    }

    private function setCache(string $key, array $value): void
    {
        $json = json_encode($value);
        if (class_exists('Redis')) {
            try {
                $redis = new Redis();
                if ($redis->connect('127.0.0.1', 6379)) {
                    $redis->setex($key, 3600, $json);
                    return;
                }
            } catch (Throwable) {}
        }
        
        $file = sys_get_temp_dir() . '/timnhadat_cache_' . md5($key) . '.cache';
        file_put_contents($file, $json);
    }

    public function clearCache(string $key): void
    {
        if (class_exists('Redis')) {
            try {
                $redis = new Redis();
                if ($redis->connect('127.0.0.1', 6379)) {
                    $redis->del($key);
                    return;
                }
            } catch (Throwable) {}
        }
        
        $file = sys_get_temp_dir() . '/timnhadat_cache_' . md5($key) . '.cache';
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    // ==========================================
    // CATEGORIES BUSINESS LOGIC
    // ==========================================

    public function getCategories(string $search = '', bool $useCache = true): array
    {
        $cacheKey = 'categories_all_' . md5($search);
        if ($useCache && ($cached = $this->getCache($cacheKey)) !== null) {
            return $cached;
        }

        $list = $this->catRepo->getAll($search);
        $data = array_map(static fn($item) => (array)$item, $list);
        
        if ($useCache) {
            $this->setCache($cacheKey, $data);
        }
        return $data;
    }

    public function createCategory(array $data, int $adminId): int
    {
        $id = $this->catRepo->create($data);
        if ($id) {
            $this->clearCache('categories_all_');
            LogService::write($adminId, 'create', 'categories', $id, "Tạo danh mục mới: {$data['name']}");
        }
        return $id;
    }

    public function updateCategory(int $id, array $data, int $adminId): bool
    {
        $ok = $this->catRepo->update($id, $data);
        if ($ok) {
            $this->clearCache('categories_all_');
            LogService::write($adminId, 'update', 'categories', $id, "Cập nhật danh mục: {$data['name']}");
        }
        return $ok;
    }

    public function deleteCategory(int $id, int $adminId): bool
    {
        // Validation: Không cho xóa nếu đang sử dụng trong dự án / tin đăng
        $db = new Database();
        $db->query("SELECT COUNT(*) as total FROM du_an WHERE ma_danh_muc = :id");
        $db->bind(':id', $id);
        if ((int)$db->single()->total > 0) {
            return false;
        }

        $ok = $this->catRepo->delete($id);
        if ($ok) {
            $this->clearCache('categories_all_');
            LogService::write($adminId, 'delete', 'categories', $id, "Xóa danh mục ID: #{$id}");
        }
        return $ok;
    }

    public function changeCategoryStatus(int $id, string $status, int $adminId): bool
    {
        $ok = $this->catRepo->changeStatus($id, $status);
        if ($ok) {
            $this->clearCache('categories_all_');
            LogService::write($adminId, 'status', 'categories', $id, "Đổi trạng thái danh mục ID #{$id} thành {$status}");
        }
        return $ok;
    }

    // ==========================================
    // TRANSACTION TYPES BUSINESS LOGIC
    // ==========================================

    public function getTransactionTypes(string $search = ''): array
    {
        return array_map(static fn($item) => (array)$item, $this->ttRepo->getAll($search));
    }

    // ==========================================
    // FACILITIES BUSINESS LOGIC
    // ==========================================

    public function getFacilities(string $search = '', bool $useCache = true): array
    {
        $cacheKey = 'facilities_all_' . md5($search);
        if ($useCache && ($cached = $this->getCache($cacheKey)) !== null) {
            return $cached;
        }

        $list = array_map(static fn($item) => (array)$item, $this->facRepo->getAll($search));
        if ($useCache) {
            $this->setCache($cacheKey, $list);
        }
        return $list;
    }

    // ==========================================
    // DIRECTIONS BUSINESS LOGIC
    // ==========================================

    public function getDirections(string $search = ''): array
    {
        return array_map(static fn($item) => (array)$item, $this->dirRepo->getAll($search));
    }

    // ==========================================
    // LEGAL TYPES BUSINESS LOGIC
    // ==========================================

    public function getLegalTypes(string $search = ''): array
    {
        return array_map(static fn($item) => (array)$item, $this->legalRepo->getAll($search));
    }
}
