<?php
/**
 * LocationService – Quản lý Tỉnh, Quận, Phường và tích hợp Caching.
 * Tuân thủ SOLID, Service Pattern.
 */
class LocationService
{
    private ProvinceRepository $provRepo;
    private DistrictRepository $distRepo;
    private WardRepository $wardRepo;

    public function __construct()
    {
        $this->provRepo = new ProvinceRepository();
        $this->distRepo = new DistrictRepository();
        $this->wardRepo = new WardRepository();
    }

    // ==========================================
    // GRACEFUL CACHING
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
        
        $file = sys_get_temp_dir() . '/timnhadat_loc_' . md5($key) . '.cache';
        if (file_exists($file) && (time() - filemtime($file) < 86400)) { // 24 hours cache
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
                    $redis->setex($key, 86400, $json);
                    return;
                }
            } catch (Throwable) {}
        }
        
        $file = sys_get_temp_dir() . '/timnhadat_loc_' . md5($key) . '.cache';
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
        
        $file = sys_get_temp_dir() . '/timnhadat_loc_' . md5($key) . '.cache';
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    // ==========================================
    // PROVINCES API & CRUD
    // ==========================================

    public function getProvinces(string $search = '', bool $useCache = true): array
    {
        $cacheKey = 'provinces_all_' . md5($search);
        if ($useCache && ($cached = $this->getCache($cacheKey)) !== null) {
            return $cached;
        }

        $list = array_map(static fn($item) => (array)$item, $this->provRepo->getAll($search));
        if ($useCache) {
            $this->setCache($cacheKey, $list);
        }
        return $list;
    }

    public function createProvince(array $data, int $adminId): int
    {
        $id = $this->provRepo->create($data);
        if ($id) {
            $this->clearCache('provinces_all_');
            LogService::write($adminId, 'create', 'provinces', $id, "Tạo tỉnh mới: {$data['name']}");
        }
        return $id;
    }

    // ==========================================
    // DISTRICTS API & CRUD
    // ==========================================

    public function getDistricts(string $search = ''): array
    {
        return array_map(static fn($item) => (array)$item, $this->distRepo->getAll($search));
    }

    public function getDistrictsByProvince(string $provinceCode, bool $useCache = true): array
    {
        $cacheKey = "districts_prov_{$provinceCode}";
        if ($useCache && ($cached = $this->getCache($cacheKey)) !== null) {
            return $cached;
        }

        $list = array_map(static fn($item) => (array)$item, $this->distRepo->getByProvince($provinceCode));
        if ($useCache) {
            $this->setCache($cacheKey, $list);
        }
        return $list;
    }

    public function createDistrict(array $data, int $adminId): int
    {
        $id = $this->distRepo->create($data);
        if ($id) {
            $this->clearCache("districts_prov_{$data['province_code']}");
            LogService::write($adminId, 'create', 'districts', $id, "Tạo quận mới: {$data['name']}");
        }
        return $id;
    }

    // ==========================================
    // WARDS API & CRUD
    // ==========================================

    public function getWards(string $search = ''): array
    {
        return array_map(static fn($item) => (array)$item, $this->wardRepo->getAll($search));
    }

    public function getWardsByDistrict(string $districtCode, bool $useCache = true): array
    {
        $cacheKey = "wards_dist_{$districtCode}";
        if ($useCache && ($cached = $this->getCache($cacheKey)) !== null) {
            return $cached;
        }

        $list = array_map(static fn($item) => (array)$item, $this->wardRepo->getByDistrict($districtCode));
        if ($useCache) {
            $this->setCache($cacheKey, $list);
        }
        return $list;
    }

    public function createWard(array $data, int $adminId): int
    {
        $id = $this->wardRepo->create($data);
        if ($id) {
            $this->clearCache("wards_dist_{$data['district_code']}");
            LogService::write($adminId, 'create', 'wards', $id, "Tạo phường mới: {$data['name']}");
        }
        return $id;
    }
}
