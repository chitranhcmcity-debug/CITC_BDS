<?php
/**
 * AuthorController – Trang hồ sơ người đăng tin BĐS.
 *
 * Routes:
 *   GET  /author/{id}            → index()    Trang hồ sơ chính
 *   POST /author/follow          → follow()   [AJAX + CSRF]
 *   POST /author/unfollow        → unfollow() [AJAX + CSRF]
 *   POST /author/review          → review()   [AJAX + CSRF]
 *   GET  /author/api-posts/{id}  → apiPosts() [JSON]
 *   GET  /author/api-stats/{id}  → apiStats() [JSON]
 */
class AuthorController extends Controller
{
    private AuthorService $authorService;

    public function __construct()
    {
        $this->authorService = new AuthorService();
    }

    // ==========================================
    // TRANG HỒ SƠ CHÍNH
    // ==========================================

    /**
     * Hiển thị trang hồ sơ người đăng.
     * URL: GET /author/{id}?type=sale&sort=new&page=1
     *
     * @param  int|null $userId ID người đăng
     */
    public function index(?int $userId = null): void
    {
        if (!$userId || $userId <= 0) {
            $this->redirect('du-an');
        }

        // Sanitise query params
        $filters = $this->sanitiseFilters($_GET);
        $page    = max(1, (int)($_GET['page'] ?? 1));

        // Visitor
        $visitorId = (int)(Session::get('user_id') ?? 0);

        // Lấy dữ liệu qua Service
        $data = $this->authorService->getProfilePage($userId, $filters, $page, $visitorId);
        if (!$data) {
            $this->redirect('du-an');
        }

        // Ghi analytics lượt xem hồ sơ
        if (!empty($data['posts'])) {
            $this->authorService->recordProfileView($userId, (int)$data['posts'][0]->id);
        }

        // Build view data
        $seo = $data['seo'];
        $this->view('author/index', array_merge($data, [
            'title'           => $seo['title'],
            'metaDescription' => $seo['description'],
            'canonicalUrl'    => $seo['canonical'],
            'ogImage'         => $seo['avatar'],
            'schemaJson'      => json_encode($seo['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'userId'          => $userId,
            'csrfToken'       => Csrf::token(),
        ]));
    }

    // ==========================================
    // FOLLOW / UNFOLLOW
    // ==========================================

    /**
     * Theo dõi người đăng (AJAX POST).
     * URL: POST /author/follow
     */
    public function follow(): void
    {
        $this->requireAjaxPost();

        $followingId = (int)($_POST['author_id'] ?? 0);
        $followerId  = (int)(Session::get('user_id') ?? 0);

        if (!$followerId) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập để theo dõi.'], 401);
        }

        $result = $this->authorService->follow($followerId, $followingId);
        $this->jsonResponse($result);
    }

    /**
     * Bỏ theo dõi người đăng (AJAX POST).
     * URL: POST /author/unfollow
     */
    public function unfollow(): void
    {
        $this->requireAjaxPost();

        $followingId = (int)($_POST['author_id'] ?? 0);
        $followerId  = (int)(Session::get('user_id') ?? 0);

        if (!$followerId) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
        }

        $result = $this->authorService->unfollow($followerId, $followingId);
        $this->jsonResponse($result);
    }

    // ==========================================
    // ĐÁNH GIÁ
    // ==========================================

    /**
     * Gửi đánh giá người đăng (AJAX POST).
     * URL: POST /author/review
     */
    public function review(): void
    {
        $this->requireAjaxPost();

        $reviewerId = (int)(Session::get('user_id') ?? 0);
        if (!$reviewerId) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập để đánh giá.'], 401);
        }

        $authorId = (int)($_POST['author_id'] ?? 0);
        $result   = $this->authorService->submitReview($reviewerId, $authorId, $_POST);
        $this->jsonResponse($result);
    }

    // ==========================================
    // JSON API
    // ==========================================

    /**
     * Trả về JSON danh sách tin (lazy-load / AJAX filter).
     * URL: GET /author/api-posts/{id}
     *
     * @param  int|null $userId
     */
    public function apiPosts(?int $userId = null): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$userId || $userId <= 0) {
            echo json_encode(['success' => false, 'data' => []]);
            exit;
        }

        $filters = $this->sanitiseFilters($_GET);
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $offset  = ($page - 1) * $perPage;

        $propertyRepo = new PropertyRepository();
        $posts  = $propertyRepo->findByAuthorPaginated($userId, $filters, $perPage, $offset);
        $total  = $propertyRepo->countByAuthor($userId, $filters);

        // Chỉ trả field cần thiết (không lộ thông tin nhạy cảm)
        $items = array_map(static fn($p) => [
            'id'                => $p->id,
            'tieu_de'           => $p->tieu_de,
            'duong_dan'         => $p->duong_dan,
            'gia'               => $p->gia,
            'dien_tich'         => $p->dien_tich,
            'vi_tri'            => $p->vi_tri,
            'anh_thu_nho'       => img_url($p->anh_thu_nho ?? ''),
            'loai_bat_dong_san' => $p->loai_bat_dong_san,
            'loai_giao_dich'    => $p->loai_giao_dich ?? '',
            'ten_danh_muc'      => $p->ten_danh_muc,
            'luot_xem'          => (int)$p->luot_xem,
            'active_vip'        => (int)($p->active_vip ?? 0),
            'ngay_tao'          => $p->ngay_tao,
        ], $posts);

        echo json_encode([
            'success'    => true,
            'data'       => $items,
            'total'      => $total,
            'page'       => $page,
            'totalPages' => (int)ceil($total / $perPage),
        ]);
        exit;
    }

    /**
     * Trả về JSON thống kê người đăng.
     * URL: GET /author/api-stats/{id}
     *
     * @param  int|null $userId
     */
    public function apiStats(?int $userId = null): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$userId || $userId <= 0) {
            echo json_encode(['success' => false]);
            exit;
        }

        $profile = $this->authorService->getProfile($userId);
        $stats   = $this->authorService->getStatistics($userId);

        if (!$profile) {
            echo json_encode(['success' => false]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data'    => array_merge($stats, [
                'so_follower'    => (int)($profile->so_follower    ?? 0),
                'diem_trung_binh'=> (float)($profile->diem_trung_binh ?? 0),
                'so_danh_gia'    => (int)($profile->so_danh_gia    ?? 0),
            ]),
        ]);
        exit;
    }

    // ==========================================
    // PRIVATE HELPERS
    // ==========================================

    /**
     * Validate và sanitise filter params từ $_GET.
     *
     * @param  array $get $_GET array
     * @return array      Sanitised filters
     */
    private function sanitiseFilters(array $get): array
    {
        $validTypes = ['sale', 'rent', ''];
        $validSorts = ['new', 'price_asc', 'price_desc', 'area_desc', 'views', 'vip'];

        return [
            'type'     => in_array($get['type'] ?? '', $validTypes, true) ? ($get['type'] ?? '') : '',
            'loai'     => htmlspecialchars(substr($get['loai'] ?? '', 0, 100), ENT_QUOTES),
            'gia_min'  => is_numeric($get['gia_min'] ?? '') ? (float)$get['gia_min'] : '',
            'gia_max'  => is_numeric($get['gia_max'] ?? '') ? (float)$get['gia_max'] : '',
            'dt_min'   => is_numeric($get['dt_min']  ?? '') ? (float)$get['dt_min']  : '',
            'dt_max'   => is_numeric($get['dt_max']  ?? '') ? (float)$get['dt_max']  : '',
            'vip_only' => !empty($get['vip_only']),
            'sort'     => in_array($get['sort'] ?? 'new', $validSorts, true) ? ($get['sort'] ?? 'new') : 'new',
        ];
    }

    /**
     * Đảm bảo request là AJAX POST có CSRF hợp lệ.
     * Thoát ngay nếu sai.
     */
    private function requireAjaxPost(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
        }

        if (!Csrf::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            $this->jsonResponse(['success' => false, 'message' => 'Token không hợp lệ. Vui lòng tải lại trang.']);
        }
    }

    /**
     * Output JSON và exit.
     *
     * @param  array $data
     * @param  int   $code HTTP status code
     */
    private function jsonResponse(array $data, int $code = 200): never
    {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
