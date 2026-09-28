<?php
/**
 * TinTucController – Frontend Module Tin tức.
 *
 * Tuân thủ: Controller chỉ điều phối HTTP, không có Business Logic.
 * Mọi xử lý delegate sang NewsService.
 *
 * Routes:
 *   GET  /tin-tuc                        → index()
 *   GET  /tin-tuc/detail/{slug}          → detail(slug)
 *   GET  /tin-tuc/category/{slug}        → category(slug)
 *   GET  /tin-tuc/tag/{slug}             → tag(slug)
 *   GET  /tin-tuc/search                 → search()
 *   POST /tin-tuc/comment                → comment()  AJAX
 *   POST /tin-tuc/like                   → like()     AJAX
 *   POST /tin-tuc/share                  → share()    AJAX
 *   GET  /tin-tuc/rss                    → rss()
 */
class TinTucController extends Controller
{
    private NewsService $newsService;

    public function __construct()
    {
        $this->newsService = new NewsService();
    }

    /* =====================================================
       DANH SÁCH
       ===================================================== */

    /**
     * Trang danh sách tin tức.
     * URL: GET /tin-tuc[?sort=popular&category_id=1&keyword=...]
     */
    public function index(): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'sort'        => in_array($_GET['sort'] ?? '', ['popular','liked','new']) ? $_GET['sort'] : 'new',
            'category_id' => (int)($_GET['category_id'] ?? 0) ?: null,
            'keyword'     => mb_substr(trim($_GET['keyword'] ?? ''), 0, 100),
        ];

        $data = $this->newsService->getListPage($filters, $page);
        $this->view('tin-tuc/index', $data);
    }

    /* =====================================================
       CHI TIẾT
       ===================================================== */

    /**
     * Trang chi tiết bài viết.
     * URL: GET /tin-tuc/detail/{slug}
     */
    public function detail(string $slug = ''): void
    {
        if (empty($slug)) {
            $this->redirect('tin-tuc');
        }

        $data = $this->newsService->getDetailPage($slug);
        if (empty($data)) {
            $this->notFound();
        }

        // Ghi nhận lượt xem (anti-spam)
        $this->newsService->recordView((int)$data['post']->id);

        // Trạng thái like của user hiện tại
        $userId = (int)(Session::get('user_id') ?? 0);
        $data['hasLiked'] = $userId
            ? $this->newsService->hasLiked((int)$data['post']->id, $userId)
            : false;
        $data['userId']       = $userId;
        $data['csrfToken']    = Csrf::token();
        $data['currentUserId']= $userId;

        $this->view('tin-tuc/detail', $data);
    }

    /* =====================================================
       DANH MỤC
       ===================================================== */

    /**
     * Trang danh mục bài viết.
     * URL: GET /tin-tuc/category/{slug}
     */
    public function category(string $slug = ''): void
    {
        if (empty($slug)) $this->redirect('tin-tuc');

        $page = max(1, (int)($_GET['page'] ?? 1));
        $data = $this->newsService->getCategoryPage($slug, $page);

        if (empty($data)) $this->notFound();

        $this->view('tin-tuc/category', $data);
    }

    /* =====================================================
       TAG
       ===================================================== */

    /**
     * Trang tag bài viết.
     * URL: GET /tin-tuc/tag/{slug}
     */
    public function tag(string $slug = ''): void
    {
        if (empty($slug)) $this->redirect('tin-tuc');

        $page = max(1, (int)($_GET['page'] ?? 1));
        $data = $this->newsService->getTagPage($slug, $page);

        if (empty($data)) $this->notFound();

        $this->view('tin-tuc/tag', $data);
    }

    /* =====================================================
       TÌM KIẾM
       ===================================================== */

    /**
     * Trang tìm kiếm.
     * URL: GET /tin-tuc/search?q={keyword}
     */
    public function search(): void
    {
        $keyword = mb_substr(trim($_GET['q'] ?? ''), 0, 100);
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $data    = $this->newsService->getSearchPage($keyword, $page);
        $this->view('tin-tuc/search', $data);
    }

    /* =====================================================
       AJAX: BÌNH LUẬN
       ===================================================== */

    /**
     * Đăng bình luận (AJAX, POST).
     * URL: POST /tin-tuc/comment
     */
    public function comment(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit;
        }

        try {
            Csrf::verify();
        } catch (Throwable) {
            echo json_encode(['success' => false, 'message' => 'CSRF token không hợp lệ.']);
            exit;
        }

        $userId = (int)(Session::get('user_id') ?? 0);
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để bình luận.']);
            exit;
        }

        $newsId = (int)($_POST['news_id'] ?? 0);
        if (!$newsId) {
            echo json_encode(['success' => false, 'message' => 'Bài viết không hợp lệ.']);
            exit;
        }

        $result = $this->newsService->submitComment($newsId, $userId, [
            'noi_dung' => $_POST['noi_dung'] ?? '',
            'cha_id'   => $_POST['cha_id'] ?? null,
        ]);

        echo json_encode($result);
        exit;
    }

    /* =====================================================
       AJAX: THÍCH
       ===================================================== */

    /**
     * Toggle like bài viết (AJAX, POST).
     * URL: POST /tin-tuc/like
     */
    public function like(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit;
        }

        try { Csrf::verify(); } catch (Throwable) {
            echo json_encode(['success' => false, 'message' => 'CSRF không hợp lệ.']);
            exit;
        }

        $userId = (int)(Session::get('user_id') ?? 0);
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Cần đăng nhập để thích.']);
            exit;
        }

        $newsId = (int)($_POST['news_id'] ?? 0);
        if (!$newsId) {
            echo json_encode(['success' => false, 'message' => 'Bài viết không hợp lệ.']);
            exit;
        }

        $result = $this->newsService->toggleLike($newsId, $userId);
        echo json_encode(['success' => true, ...$result]);
        exit;
    }

    /* =====================================================
       AJAX: CHIA SẺ
       ===================================================== */

    /**
     * Ghi nhận lượt chia sẻ (AJAX, POST).
     * URL: POST /tin-tuc/share
     */
    public function share(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false]);
            exit;
        }

        $newsId = (int)($_POST['news_id'] ?? 0);
        if ($newsId) {
            $this->newsService->recordShare($newsId);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    /* =====================================================
       RSS FEED
       ===================================================== */

    /**
     * RSS Feed XML.
     * URL: GET /tin-tuc/rss
     */
    public function rss(): void
    {
        header('Content-Type: application/rss+xml; charset=utf-8');
        header('Cache-Control: public, max-age=1800');
        echo $this->newsService->getRssFeed();
        exit;
    }

    /* =====================================================
       PRIVATE HELPER
       ===================================================== */

    private function notFound(): void
    {
        http_response_code(404);
        $this->redirect('tin-tuc');
    }
}
