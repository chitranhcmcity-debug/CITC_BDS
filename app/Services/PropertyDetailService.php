<?php

namespace App\Services;

use App\Models\Post;
use App\Repositories\PropertyRepository;
use App\Repositories\ReportRepository;

/**
 * PropertyDetailService – Business logic của trang chi tiết Bất động sản.
 *
 * Nguyên tắc SOLID:
 *  - Single Responsibility: Chỉ xử lý nghiệp vụ trang chi tiết.
 *  - Dependency Injection: Repository và Model được inject qua constructor.
 *  - Không SQL trực tiếp ở đây.
 *
 * Cache: 5 phút (PHP file cache đơn giản, sẵn sàng nâng cấp Redis).
 */
class PropertyDetailService
{
    private PropertyRepository $propertyRepo;

    private ReportRepository $reportRepo;

    private AnalyticsService $analyticsService;

    /** @var int Cache TTL tính bằng giây (5 phút) */
    private const CACHE_TTL = 300;

    public function __construct(
        ?PropertyRepository $propertyRepo = null,
        ?ReportRepository $reportRepo = null,
        ?AnalyticsService $analyticsService = null
    ) {
        $this->propertyRepo = $propertyRepo ?? new PropertyRepository;
        $this->reportRepo = $reportRepo ?? new ReportRepository;
        $this->analyticsService = $analyticsService ?? new AnalyticsService;
    }

    // ==========================================
    // LẤY THÔNG TIN CHI TIẾT
    // ==========================================

    /**
     * Lấy toàn bộ dữ liệu cần thiết cho trang chi tiết.
     * Kết quả được cache 5 phút để tránh query lặp khi load lại trang.
     *
     * @param  string  $slug  URL slug của tin đăng
     * @return array|null Null nếu không tìm thấy / tin bị khoá
     */
    public function getDetail(string $slug): ?array
    {
        // Kiểm tra slug hợp lệ (chỉ chứa chữ thường, số, dấu gạch ngang)
        if (! preg_match('/^[a-z0-9\-]+$/u', $slug)) {
            return null;
        }

        $cacheKey = 'property_detail_'.md5($slug);
        $cached = $this->getCache($cacheKey);

        if ($cached !== null) {
            // Convert 'post' back to stdClass object
            if (isset($cached['post']) && is_array($cached['post'])) {
                $cached['post'] = (object) $cached['post'];
            }
            // Convert each item in 'images' back to stdClass object
            if (isset($cached['images']) && is_array($cached['images'])) {
                foreach ($cached['images'] as $k => $img) {
                    $cached['images'][$k] = (object) $img;
                }
            }

            return $cached;
        }

        // Truy vấn CSDL qua Repository
        $post = $this->propertyRepo->findBySlug($slug);
        if (! $post) {
            return null;
        }

        // Lấy gallery
        $images = $this->propertyRepo->findImages((int) $post->id);

        // Build dữ liệu hoàn chỉnh
        $result = [
            'post' => $post,
            'images' => $images,
            'amenities' => $this->parseAmenities($post),
            'videoEmbed' => $this->buildVideoEmbed((string) ($post->link_video ?? '')),
            'mapData' => $this->buildMapData($post),
            'breadcrumb' => $this->buildBreadcrumb($post),
        ];

        $this->setCache($cacheKey, $result, self::CACHE_TTL);

        return $result;
    }

    /**
     * Lấy danh sách tin liên quan (cache 5 phút).
     *
     * @param  object  $post  Đối tượng tin đăng hiện tại
     */
    public function getRelated(object $post): array
    {
        $cacheKey = 'property_related_'.$post->id;
        $cached = $this->getCache($cacheKey);
        if ($cached !== null) {
            foreach ($cached as $key => $item) {
                $cached[$key] = (object) $item;
            }

            return $cached;
        }

        $related = $this->propertyRepo->findRelated(
            (int) $post->id,
            (string) ($post->loai_bat_dong_san ?? ''),
            (string) ($post->tinh_thanh ?? ''),
            12
        );

        $this->setCache($cacheKey, $related, self::CACHE_TTL);

        return $related;
    }

    // ==========================================
    // BÁO CÁO VI PHẠM
    // ==========================================

    /**
     * Xử lý báo cáo vi phạm từ người dùng.
     *
     * @param  int  $postId  ID tin đăng
     * @param  array  $input  Dữ liệu từ form
     * @param  string  $ip  Địa chỉ IP
     * @return array ['success' => bool, 'message' => string]
     */
    public function submitReport(int $postId, array $input, string $ip): array
    {
        // Kiểm tra tin tồn tại
        if (! $this->propertyRepo->exists($postId)) {
            return ['success' => false, 'message' => 'Tin đăng không tồn tại.'];
        }

        $userId = Session::get('user_id') ? (int) Session::get('user_id') : null;

        // Chống spam: 1 lần / 24h / IP hoặc user
        if ($this->reportRepo->hasReported($postId, $userId, $ip)) {
            return ['success' => false, 'message' => 'Bạn đã báo cáo tin đăng này trong 24 giờ qua.'];
        }

        // Validate lý do
        $validReasons = ['thong_tin_sai', 'hinh_anh_sai', 'gia_sai', 'lua_dao', 'tin_trung_lap', 'khac'];
        $lyDo = in_array($input['ly_do'] ?? '', $validReasons, true) ? $input['ly_do'] : 'khac';

        $ok = $this->reportRepo->create([
            'ma_du_an' => $postId,
            'ma_nguoi_dung' => $userId,
            'ho_ten' => htmlspecialchars(substr($input['ho_ten'] ?? '', 0, 100), ENT_QUOTES),
            'email' => filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL) ?: null,
            'ly_do' => $lyDo,
            'mo_ta' => htmlspecialchars(substr($input['mo_ta'] ?? '', 0, 1000), ENT_QUOTES),
            'ip_address' => $ip,
        ]);

        if ($ok) {
            return ['success' => true, 'message' => 'Cảm ơn! Báo cáo của bạn đã được ghi nhận.'];
        }

        return ['success' => false, 'message' => 'Có lỗi xảy ra. Vui lòng thử lại.'];
    }

    // ==========================================
    // CHIA SẺ
    // ==========================================

    /**
     * Ghi nhận lượt chia sẻ (tăng counter + analytics event).
     *
     * @param  int  $postId  ID tin đăng
     * @param  string  $platform  Nền tảng chia sẻ (facebook, zalo, ...)
     */
    public function recordShare(int $postId, string $platform = 'other'): bool
    {
        if (! $this->propertyRepo->exists($postId)) {
            return false;
        }
        // Tăng counter trên bảng du_an
        $this->propertyRepo->incrementShare($postId);
        // Ghi analytics với metadata platform
        $this->analyticsService->record($postId, 'share', ['platform' => $platform]);
        // Xoá cache để lần sau load lại số mới
        $this->invalidateCache($postId);

        return true;
    }

    // ==========================================
    // XỬ LÝ DỮ LIỆU
    // ==========================================

    /**
     * Parse chuỗi tiện ích JSON hoặc text từ cột tien_ich / tien_ich_json.
     * Trả về mảng [['ten' => ..., 'icon' => ..., 'khoang_cach' => ...], ...]
     */
    public function parseAmenities(object $post): array
    {
        // Thử đọc JSON mới trước
        $jsonRaw = $post->tien_ich_json ?? '';
        if ($jsonRaw) {
            $decoded = json_decode($jsonRaw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Fallback: Parse chuỗi tien_ich cũ (dạng text phân cách dòng)
        $raw = $post->tien_ich ?? '';
        if (! $raw) {
            return [];
        }

        // Bản đồ icon cho từng loại tiện ích
        $iconMap = [
            'trường' => ['icon' => 'fa-school',          'color' => '#4CAF50'],
            'bệnh viện' => ['icon' => 'fa-hospital',        'color' => '#F44336'],
            'siêu thị' => ['icon' => 'fa-cart-shopping',   'color' => '#2196F3'],
            'chợ' => ['icon' => 'fa-store',           'color' => '#FF9800'],
            'công viên' => ['icon' => 'fa-tree',            'color' => '#4CAF50'],
            'bến xe' => ['icon' => 'fa-bus',             'color' => '#9C27B0'],
            'metro' => ['icon' => 'fa-train-subway',    'color' => '#00BCD4'],
            'sân bay' => ['icon' => 'fa-plane',           'color' => '#607D8B'],
        ];

        $items = [];
        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if (! $line) {
                continue;
            }

            $icon = 'fa-map-marker-alt';
            $color = '#6c757d';

            foreach ($iconMap as $keyword => $meta) {
                if (mb_stripos($line, $keyword) !== false) {
                    $icon = $meta['icon'];
                    $color = $meta['color'];
                    break;
                }
            }

            $items[] = [
                'ten' => htmlspecialchars($line, ENT_QUOTES),
                'icon' => $icon,
                'color' => $color,
                'khoang_cach' => '',
            ];
        }

        return $items;
    }

    /**
     * Xây dựng embed URL video từ link YouTube / MP4 / iframe.
     *
     * @return array|null ['type' => 'youtube'|'mp4'|'iframe', 'src' => string]
     */
    public function buildVideoEmbed(string $url): ?array
    {
        if (! $url) {
            return null;
        }

        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_\-]{11})/', $url, $m)) {
            return [
                'type' => 'youtube',
                'src' => 'https://www.youtube.com/embed/'.$m[1].'?rel=0&autoplay=0',
            ];
        }

        // MP4
        if (preg_match('/\.mp4(\?.*)?$/i', $url)) {
            return ['type' => 'mp4', 'src' => $url];
        }

        // Iframe mặc định
        return ['type' => 'iframe', 'src' => $url];
    }

    /**
     * Xây dựng dữ liệu bản đồ từ thông tin tin đăng.
     */
    public function buildMapData(object $post): array
    {
        return [
            'lat' => $post->vi_do ?? null,
            'lng' => $post->kinh_do ?? null,
            'title' => htmlspecialchars($post->tieu_de ?? '', ENT_QUOTES),
            'address' => htmlspecialchars($post->vi_tri ?? '', ENT_QUOTES),
            'hasMap' => ! empty($post->vi_do) && ! empty($post->kinh_do),
        ];
    }

    /**
     * Xây dựng breadcrumb cho tin đăng.
     *
     * @return array [['label' => ..., 'url' => ...], ...]
     */
    public function buildBreadcrumb(object $post): array
    {
        $crumbs = [
            ['label' => 'Trang chủ',          'url' => URL_ROOT],
            ['label' => 'Bất động sản',        'url' => URL_ROOT.'/du-an'],
        ];

        if (! empty($post->loai_bat_dong_san)) {
            $crumbs[] = [
                'label' => htmlspecialchars($post->loai_bat_dong_san, ENT_QUOTES),
                'url' => URL_ROOT.'/du-an?q='.urlencode($post->loai_bat_dong_san),
            ];
        }

        if (! empty($post->tinh_thanh)) {
            $crumbs[] = [
                'label' => htmlspecialchars($post->tinh_thanh, ENT_QUOTES),
                'url' => URL_ROOT.'/du-an?province='.urlencode($post->tinh_thanh),
            ];
        }

        $crumbs[] = [
            'label' => htmlspecialchars($post->tieu_de ?? '', ENT_QUOTES),
            'url' => null, // trang hiện tại
        ];

        return $crumbs;
    }

    // ==========================================
    // CACHE (File-based, sẵn sàng thay Redis)
    // ==========================================

    /**
     * Đọc giá trị cache.
     *
     * @return mixed|null
     */
    private function getCache(string $key): mixed
    {
        $file = sys_get_temp_dir().'/bds_cache_'.md5($key).'.json';
        if (! file_exists($file)) {
            return null;
        }
        if ((time() - filemtime($file)) > self::CACHE_TTL) {
            @unlink($file);

            return null;
        }
        $raw = @file_get_contents($file);
        if (! $raw) {
            return null;
        }

        return json_decode($raw, true);
    }

    /**
     * Lưu giá trị vào cache.
     *
     * @param  int  $ttl  Không dùng trong file cache (dùng filemtime)
     */
    private function setCache(string $key, mixed $value, int $ttl = self::CACHE_TTL): void
    {
        $file = sys_get_temp_dir().'/bds_cache_'.md5($key).'.json';
        @file_put_contents($file, json_encode($value, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    /**
     * Xoá cache của tin đăng (sau khi cập nhật share/save).
     */
    private function invalidateCache(int $postId): void
    {
        // Xoá theo pattern không khả thi với file cache đơn giản.
        // Khi nâng cấp Redis, dùng: Redis::del("property_detail_*{$postId}*")
        // Tạm thời xoá cache related vì nó chứa counter:
        $relatedKey = 'property_related_'.$postId;
        $file = sys_get_temp_dir().'/bds_cache_'.md5($relatedKey).'.json';
        @unlink($file);
    }
}
