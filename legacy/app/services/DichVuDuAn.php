<?php
/**
 * ProjectService – Nghiệp vụ quản lý dự án dùng chung.
 * Tích hợp Caching (Redis/File fallback).
 * Tuân thủ SOLID, Service Pattern.
 */
class ProjectService
{
    private ProjectRepository $projRepo;

    public function __construct()
    {
        $this->projRepo = new ProjectRepository();
    }

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
        
        $file = sys_get_temp_dir() . '/timnhadat_proj_' . md5($key) . '.cache';
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
        
        $file = sys_get_temp_dir() . '/timnhadat_proj_' . md5($key) . '.cache';
        file_put_contents($file, $json);
    }

    private function clearCache(string $key): void
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
        
        $file = sys_get_temp_dir() . '/timnhadat_proj_' . md5($key) . '.cache';
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    // ==========================================
    // PROJECTS BUSINESS LOGIC
    // ==========================================

    public function getProjects(string $search = '', bool $useCache = true): array
    {
        $cacheKey = 'projects_all_' . md5($search);
        if ($useCache && ($cached = $this->getCache($cacheKey)) !== null) {
            return $cached;
        }

        $list = array_map(static fn($item) => (array)$item, $this->projRepo->getAll($search));
        if ($useCache) {
            $this->setCache($cacheKey, $list);
        }
        return $list;
    }

    public function createProject(array $data, int $adminId): int
    {
        $id = $this->projRepo->create($data);
        if ($id) {
            $this->clearCache('projects_all_');
            LogService::write($adminId, 'create', 'projects', $id, "Tạo dự án mới: {$data['name']}");
        }
        return $id;
    }

    public function updateProject(int $id, array $data, int $adminId): bool
    {
        $ok = $this->projRepo->update($id, $data);
        if ($ok) {
            $this->clearCache('projects_all_');
            LogService::write($adminId, 'update', 'projects', $id, "Cập nhật dự án: {$data['name']}");
        }
        return $ok;
    }

    public function deleteProject(int $id, int $adminId): bool
    {
        // Validation: Không cho xóa nếu dự án đang được sử dụng trong tin đăng
        $project = $this->projRepo->findById($id);
        if ($project) {
            $db = new Database();
            // Kiểm tra chính xác bằng tên dự án trong cột ten_du_an (thay vì LIKE tieu_de)
            $db->query("SELECT COUNT(*) as total FROM du_an WHERE ten_du_an = :name AND deleted_at IS NULL");
            $db->bind(':name', $project->name);
            if ((int)$db->single()->total > 0) {
                return false;
            }
        }

        $ok = $this->projRepo->delete($id);
        if ($ok) {
            $this->clearCache('projects_all_');
            LogService::write($adminId, 'delete', 'projects', $id, "Xóa dự án: #{$id} - " . ($project->name ?? ''));
        }
        return $ok;
    }
}
