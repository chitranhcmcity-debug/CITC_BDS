<?php
/**
 * AuthorService – Business logic của trang hồ sơ người đăng tin BĐS.
 *
 * Nguyên tắc SOLID:
 *  - Chỉ chứa nghiệp vụ, không SQL, không HTML.
 *  - Dependency Injection qua constructor.
 *  - Cache 5 phút (file-based, sẵn sàng nâng cấp Redis).
 */
class AuthorService
{
    private UserRepository     $userRepo;
    private PropertyRepository $propertyRepo;
    private FollowRepository   $followRepo;
    private ReviewRepository   $reviewRepo;
    private AnalyticsService   $analytics;

    private const CACHE_TTL    = 300; // 5 phút
    private const PER_PAGE     = 12;

    public function __construct(
        ?UserRepository     $userRepo     = null,
        ?PropertyRepository $propertyRepo = null,
        ?FollowRepository   $followRepo   = null,
        ?ReviewRepository   $reviewRepo   = null,
        ?AnalyticsService   $analytics    = null
    ) {
        $this->userRepo     = $userRepo     ?? new UserRepository();
        $this->propertyRepo = $propertyRepo ?? new PropertyRepository();
        $this->followRepo   = $followRepo   ?? new FollowRepository();
        $this->reviewRepo   = $reviewRepo   ?? new ReviewRepository();
        $this->analytics    = $analytics    ?? new AnalyticsService();
    }

    // ==========================================
    // HỒ SƠ NGƯỜI ĐĂNG
    // ==========================================

    /**
     * Lấy toàn bộ dữ liệu cần thiết cho trang hồ sơ.
     * Cache 5 phút. Trả null nếu user không tồn tại / bị khoá.
     *
     * @param  int   $userId    ID người đăng
     * @param  array $filters   Bộ lọc danh sách tin
     * @param  int   $page      Trang hiện tại
     * @param  int   $visitorId ID người đang xem (0 = khách)
     * @return array|null
     */
    public function getProfilePage(int $userId, array $filters = [], int $page = 1, int $visitorId = 0): ?array
    {
        // 1. Hồ sơ (cache riêng)
        $profile = $this->getProfile($userId);
        if (!$profile) {
            return null;
        }

        // 2. Thống kê (cache riêng)
        $stats = $this->getStatistics($userId);

        // 3. Danh sách tin (không cache – phụ thuộc filter)
        $offset  = ($page - 1) * self::PER_PAGE;
        $posts   = $this->propertyRepo->findByAuthorPaginated($userId, $filters, self::PER_PAGE, $offset);
        $total   = $this->propertyRepo->countByAuthor($userId, $filters);

        $pagination = $this->buildPagination($total, $page, self::PER_PAGE);

        // 4. Trạng thái follow của người xem
        $isFollowing = $visitorId > 0 && $visitorId !== $userId
            ? $this->followRepo->isFollowing($visitorId, $userId)
            : false;

        // 5. Đánh giá (trang 1)
        $reviews = $this->reviewRepo->getByAuthor($userId, 6, 0);
        $ratingDist = $this->reviewRepo->getRatingDistribution($userId);

        // 6. SEO data
        $seo = $this->buildSeoData($profile);

        return [
            'profile'     => $profile,
            'stats'       => $stats,
            'posts'       => $posts,
            'pagination'  => $pagination,
            'filters'     => $filters,
            'isFollowing' => $isFollowing,
            'reviews'     => $reviews,
            'ratingDist'  => $ratingDist,
            'seo'         => $seo,
            'perPage'     => self::PER_PAGE,
        ];
    }

    /**
     * Lấy hồ sơ công khai (cache 5 phút).
     *
     * @param  int $userId
     * @return object|null
     */
    public function getProfile(int $userId): ?object
    {
        $cacheKey = "author_profile_{$userId}";
        $cached   = $this->getCache($cacheKey);
        if ($cached !== null) {
            return (object)$cached;
        }

        $profile = $this->userRepo->findPublicProfile($userId);
        if (!$profile) {
            return null;
        }

        $this->setCache($cacheKey, (array)$profile);
        return $profile;
    }

    /**
     * Lấy thống kê người đăng (cache 5 phút).
     *
     * @param  int $userId
     * @return array
     */
    public function getStatistics(int $userId): array
    {
        $cacheKey = "author_stats_{$userId}";
        $cached   = $this->getCache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $raw = $this->propertyRepo->getAuthorStats($userId);

        $stats = [
            'tong_tin'       => (int)($raw->tong_tin       ?? 0),
            'dang_hien_thi'  => (int)($raw->dang_hien_thi  ?? 0),
            'da_ban'         => (int)($raw->da_ban          ?? 0),
            'da_cho_thue'    => (int)($raw->da_cho_thue     ?? 0),
            'tin_vip'        => (int)($raw->tin_vip         ?? 0),
            'tong_luot_xem'  => (int)($raw->tong_luot_xem  ?? 0),
            'tong_luot_chia_se' => (int)($raw->tong_luot_chia_se ?? 0),
        ];

        $this->setCache($cacheKey, $stats);
        return $stats;
    }

    // ==========================================
    // FOLLOW
    // ==========================================

    /**
     * Xử lý theo dõi người đăng.
     * Kiểm tra: đăng nhập, không tự follow, chưa follow.
     *
     * @param  int $followerId  ID người đang đăng nhập
     * @param  int $followingId ID người muốn theo dõi
     * @return array ['success' => bool, 'message' => string, 'count' => int]
     */
    public function follow(int $followerId, int $followingId): array
    {
        if ($followerId === $followingId) {
            return ['success' => false, 'message' => 'Không thể tự theo dõi chính mình.'];
        }

        if (!$this->userRepo->isActive($followingId)) {
            return ['success' => false, 'message' => 'Người dùng không tồn tại.'];
        }

        if ($this->followRepo->isFollowing($followerId, $followingId)) {
            return ['success' => false, 'message' => 'Bạn đã theo dõi người này rồi.'];
        }

        $ok = $this->followRepo->follow($followerId, $followingId);
        if ($ok) {
            $this->userRepo->updateFollowerCount($followingId, +1);
            $this->invalidateProfileCache($followingId);
            $count = $this->followRepo->countFollowers($followingId);

            // Ghi analytics
            $this->analytics->record($followingId, 'save', ['action' => 'follow']);

            return ['success' => true, 'message' => 'Đã theo dõi!', 'count' => $count];
        }
        return ['success' => false, 'message' => 'Có lỗi xảy ra. Vui lòng thử lại.'];
    }

    /**
     * Xử lý bỏ theo dõi người đăng.
     *
     * @param  int $followerId
     * @param  int $followingId
     * @return array
     */
    public function unfollow(int $followerId, int $followingId): array
    {
        if (!$this->followRepo->isFollowing($followerId, $followingId)) {
            return ['success' => false, 'message' => 'Bạn chưa theo dõi người này.'];
        }

        $ok = $this->followRepo->unfollow($followerId, $followingId);
        if ($ok) {
            $this->userRepo->updateFollowerCount($followingId, -1);
            $this->invalidateProfileCache($followingId);
            $count = $this->followRepo->countFollowers($followingId);
            return ['success' => true, 'message' => 'Đã bỏ theo dõi.', 'count' => $count];
        }
        return ['success' => false, 'message' => 'Có lỗi xảy ra.'];
    }

    // ==========================================
    // REVIEW
    // ==========================================

    /**
     * Gửi đánh giá người đăng.
     * Kiểm tra: đăng nhập, không tự đánh giá, 1 lần/người.
     *
     * @param  int   $reviewerId
     * @param  int   $authorId
     * @param  array $input ['so_sao' => 1-5, 'nhan_xet' => string]
     * @return array ['success' => bool, 'message' => string]
     */
    public function submitReview(int $reviewerId, int $authorId, array $input): array
    {
        if ($reviewerId === $authorId) {
            return ['success' => false, 'message' => 'Không thể tự đánh giá chính mình.'];
        }

        if (!$this->userRepo->isActive($authorId)) {
            return ['success' => false, 'message' => 'Người dùng không tồn tại.'];
        }

        if ($this->reviewRepo->hasReviewed($reviewerId, $authorId)) {
            return ['success' => false, 'message' => 'Bạn đã đánh giá người đăng này rồi.'];
        }

        $soSao = (int)($input['so_sao'] ?? 5);
        if ($soSao < 1 || $soSao > 5) {
            return ['success' => false, 'message' => 'Số sao không hợp lệ (1-5).'];
        }

        $ok = $this->reviewRepo->create([
            'reviewer_id' => $reviewerId,
            'author_id'   => $authorId,
            'so_sao'      => $soSao,
            'nhan_xet'    => htmlspecialchars(mb_substr($input['nhan_xet'] ?? '', 0, 500), ENT_QUOTES),
        ]);

        if ($ok) {
            $this->invalidateProfileCache($authorId);
            return ['success' => true, 'message' => 'Cảm ơn! Đánh giá của bạn đã được ghi nhận.'];
        }
        return ['success' => false, 'message' => 'Có lỗi xảy ra. Vui lòng thử lại.'];
    }

    // ==========================================
    // ANALYTICS
    // ==========================================

    /**
     * Ghi nhận lượt xem hồ sơ.
     * Dùng AnalyticsService để tránh N+1 và dedup 30 phút.
     *
     * @param  int $authorId   ID người được xem
     * @param  int $firstPostId ID tin đầu tiên (dùng để ghi analytics)
     */
    public function recordProfileView(int $authorId, int $firstPostId = 0): void
    {
        if ($firstPostId > 0) {
            $this->analytics->record($firstPostId, 'view', ['context' => 'author_profile']);
        }
    }

    // ==========================================
    // SEO
    // ==========================================

    /**
     * Build SEO metadata cho trang hồ sơ.
     *
     * @param  object $profile
     * @return array ['title', 'description', 'canonical', 'ogImage', 'schema']
     */
    public function buildSeoData(object $profile): array
    {
        $name      = htmlspecialchars($profile->ten ?? 'Người đăng', ENT_QUOTES);
        $loai      = match($profile->loai_tai_khoan ?? 'ca_nhan') {
            'moi_gioi' => 'Môi giới',
            'cong_ty'  => 'Công ty BĐS',
            default    => 'Cá nhân',
        };
        $khuVuc    = !empty($profile->khu_vuc_hoat_dong)
            ? htmlspecialchars($profile->khu_vuc_hoat_dong, ENT_QUOTES)
            : 'Toàn quốc';

        $title       = SITE_NAME . " - Tin đăng của {$name} | {$loai} BĐS {$khuVuc}";
        $description = !empty($profile->mo_ta_ca_nhan)
            ? mb_substr(strip_tags($profile->mo_ta_ca_nhan), 0, 160)
            : "Xem {$profile->tong_tin_dang} tin BĐS của {$name} - {$loai} hoạt động tại {$khuVuc}. Liên hệ trực tiếp qua TimNhaDat.site.";

        $canonical = URL_ROOT . '/author/' . $profile->id;

        $avatar = !empty($profile->anh_dai_dien)
            ? URL_ROOT . '/uploads/avatars/' . $profile->anh_dai_dien
            : 'https://ui-avatars.com/api/?name=' . urlencode($profile->ten ?? 'ND') . '&background=00a8a8&color=fff&size=200';

        // Schema.org Person
        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'Person',
            'name'     => $profile->ten ?? '',
            'url'      => $canonical,
            'image'    => $avatar,
            'jobTitle' => $loai . ' Bất động sản',
            'address'  => [
                '@type'           => 'PostalAddress',
                'addressLocality' => $khuVuc,
                'addressCountry'  => 'VN',
            ],
            'numberOfEmployees' => (int)($profile->tong_tin_dang ?? 0),
            'memberOf' => [
                '@type' => 'Organization',
                'name'  => SITE_NAME,
                'url'   => URL_ROOT,
            ],
        ];

        return compact('title', 'description', 'canonical', 'avatar', 'schema');
    }

    // ==========================================
    // PAGINATION
    // ==========================================

    /**
     * Build dữ liệu phân trang.
     *
     * @param  int $total
     * @param  int $currentPage
     * @param  int $perPage
     * @return array
     */
    public function buildPagination(int $total, int $currentPage, int $perPage): array
    {
        $totalPages = (int)ceil($total / max(1, $perPage));
        return [
            'total'       => $total,
            'perPage'     => $perPage,
            'currentPage' => $currentPage,
            'totalPages'  => $totalPages,
            'hasPrev'     => $currentPage > 1,
            'hasNext'     => $currentPage < $totalPages,
            'prevPage'    => $currentPage - 1,
            'nextPage'    => $currentPage + 1,
        ];
    }

    // ==========================================
    // CACHE
    // ==========================================

    private function getCache(string $key): mixed
    {
        $file = sys_get_temp_dir() . '/bds_author_' . md5($key) . '.json';
        if (!file_exists($file) || (time() - filemtime($file)) > self::CACHE_TTL) {
            return null;
        }
        $raw = @file_get_contents($file);
        return $raw ? json_decode($raw, true) : null;
    }

    private function setCache(string $key, mixed $value): void
    {
        $file = sys_get_temp_dir() . '/bds_author_' . md5($key) . '.json';
        @file_put_contents($file, json_encode($value, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    private function invalidateProfileCache(int $userId): void
    {
        foreach (['author_profile_', 'author_stats_'] as $prefix) {
            @unlink(sys_get_temp_dir() . '/bds_author_' . md5($prefix . $userId) . '.json');
        }
    }
}
