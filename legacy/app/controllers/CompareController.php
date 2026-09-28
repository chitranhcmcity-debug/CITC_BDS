<?php
/**
 * CompareController – Quản lý danh sách so sánh và hiển thị bảng so sánh BĐS.
 * Hỗ trợ cả khách vãng lai và thành viên đã đăng nhập.
 * Tuân thủ SOLID, tách biệt rõ ràng Controller, Service, Repository.
 */
class CompareController extends Controller
{
    private CompareService $service;

    public function __construct()
    {
        require_once APP_ROOT . '/app/services/DichVuSoSanh.php';
        $this->service = new CompareService();
    }

    /**
     * Hiển thị bảng so sánh các bất động sản đã chọn (tối đa 4).
     */
    public function index(): void
    {
        $isLoggedIn = (bool)Session::get('user_id');
        $userOrSession = $isLoggedIn ? (int)Session::get('user_id') : session_id();

        // Lấy danh sách tin đăng so sánh
        $items = $this->service->getComparisonListWithCache($userOrSession, $isLoggedIn);

        $this->view('compare/index', [
            'title' => 'So sánh bất động sản – TimNhaDat.site',
            'items' => $items
        ]);
    }

    /**
     * API Thêm/Xóa tin đăng khỏi so sánh.
     */
    public function toggle(int $postId = 0): void
    {
        $isLoggedIn = (bool)Session::get('user_id');
        $userOrSession = $isLoggedIn ? (int)Session::get('user_id') : session_id();

        $result = $this->service->toggleCompare($userOrSession, $postId, $isLoggedIn);
        
        if ($result['success']) {
            $this->json($result);
        } else {
            $this->json($result, 400);
        }
    }

    /**
     * Xóa một tin đăng khỏi danh sách so sánh.
     */
    public function delete(int $postId = 0): void
    {
        $isLoggedIn = (bool)Session::get('user_id');
        $userOrSession = $isLoggedIn ? (int)Session::get('user_id') : session_id();

        $ok = $this->service->removeCompare($userOrSession, $postId, $isLoggedIn);
        if ($ok) {
            Session::set('success', 'Đã xóa tin đăng khỏi danh sách so sánh.');
        } else {
            Session::set('error', 'Không thể xóa khỏi so sánh.');
        }
        $this->redirect('nguoi-dung/compare');
    }

    /**
     * Xóa toàn bộ danh sách so sánh.
     */
    public function deleteAll(): void
    {
        $isLoggedIn = (bool)Session::get('user_id');
        $userOrSession = $isLoggedIn ? (int)Session::get('user_id') : session_id();

        $ok = $this->service->removeAllCompare($userOrSession, $isLoggedIn);
        if ($ok) {
            Session::set('success', 'Đã xóa toàn bộ danh sách so sánh.');
        } else {
            Session::set('error', 'Không thể xóa danh sách so sánh.');
        }
        $this->redirect('nguoi-dung/compare');
    }
}
