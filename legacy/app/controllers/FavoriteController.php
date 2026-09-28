<?php
/**
 * FavoriteController – Quản lý danh sách yêu thích và các hành động lưu/bỏ lưu tin đăng.
 * Tuân thủ SOLID, tách biệt rõ ràng Controller, Service, Repository.
 */
class FavoriteController extends Controller
{
    private FavoriteService $service;

    public function __construct()
    {
        require_once APP_ROOT . '/app/middleware/AuthMiddleware.php';
        require_once APP_ROOT . '/app/services/DichVuYeuThich.php';
        
        // Khởi tạo nghiệp vụ
        $this->service = new FavoriteService();
    }

    /**
     * Hiển thị danh sách tin đã lưu của người dùng.
     */
    public function index(): void
    {
        AuthMiddleware::handle(true);
        $userId = (int)Session::get('user_id');

        // Thu thập các bộ lọc từ GET request
        $filters = [
            'transaction_type' => $_GET['transaction_type'] ?? null,
            'vip_level'        => isset($_GET['vip_level']) && $_GET['vip_level'] !== '' ? (int)$_GET['vip_level'] : null,
            'price_min'        => isset($_GET['price_min']) && $_GET['price_min'] !== '' ? (float)$_GET['price_min'] : null,
            'price_max'        => isset($_GET['price_max']) && $_GET['price_max'] !== '' ? (float)$_GET['price_max'] : null,
        ];

        // Lấy danh sách qua Service (có Cache)
        $favorites = $this->service->getFavoritesWithCache($userId, $filters);

        $this->view('favorite/index', [
            'title'     => 'Tin đăng đã lưu – TimNhaDat.site',
            'favorites' => $favorites,
            'filters'   => $filters,
            'errors'    => Session::get('errors') ?: []
        ]);
        Session::delete('errors');
    }

    /**
     * API Toggle Lưu/Bỏ lưu tin đăng.
     */
    public function toggle(int $postId = 0): void
    {
        // 1. Kiểm tra đăng nhập dạng API
        if (!Session::get('user_id')) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập để lưu tin.'], 401);
            return;
        }

        $userId = (int)Session::get('user_id');
        $result = $this->service->toggleFavorite($userId, $postId);
        
        if ($result['success']) {
            $this->json($result);
        } else {
            $this->json($result, 400);
        }
    }

    /**
     * Bỏ lưu một tin đăng cụ thể (Form POST/DELETE hoặc Redirect).
     */
    public function delete(int $postId = 0): void
    {
        AuthMiddleware::handle(true);
        $userId = (int)Session::get('user_id');

        $ok = $this->service->removeFavorite($userId, $postId);
        if ($ok) {
            Session::set('success', 'Đã bỏ lưu tin đăng.');
        } else {
            Session::set('error', 'Không thể bỏ lưu tin đăng.');
        }
        $this->redirect('nguoi-dung/daLuu');
    }

    /**
     * Bỏ lưu toàn bộ danh sách tin đã lưu.
     */
    public function deleteAll(): void
    {
        AuthMiddleware::handle(true);
        $userId = (int)Session::get('user_id');

        $ok = $this->service->removeAllFavorites($userId);
        if ($ok) {
            Session::set('success', 'Đã xóa sạch danh sách tin đăng đã lưu.');
        } else {
            Session::set('error', 'Không thể xóa danh sách đã lưu.');
        }
        $this->redirect('nguoi-dung/daLuu');
    }
}
