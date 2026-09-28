<?php
/**
 * AnalyticsController – Controller điều hướng cho Dashboard Analytics thành viên.
 * Quyền truy cập: Thành viên đăng nhập (xem chính mình) & Admin (xem tất cả).
 */
class AnalyticsController extends Controller
{
    private AnalyticsService $service;

    public function __construct()
    {
        $this->service = new AnalyticsService();
    }

    /**
     * Hiển thị trang Dashboard Analytics tổng quan.
     * URL: GET /nguoi-dung/analytics
     */
    public function dashboard(): void
    {
        $this->requireLogin();
        $userId = (int)Session::get('user_id');

        $data = $this->service->dashboard($userId, $_GET);

        $this->view('analytics/dashboard', [
            'title'     => 'Thống kê hiệu quả tin đăng - ' . SITE_NAME,
            'analytics' => $data
        ]);
    }

    /**
     * Hiển thị báo cáo chi tiết cho một tin đăng cụ thể.
     * URL: GET /nguoi-dung/postAnalytics/{id}
     */
    public function detail(?string $id = null): void
    {
        $this->requireLogin();
        if (!$id) {
            $this->redirect('nguoi-dung/analytics');
        }

        $postId = (int)$id;
        $userId = (int)Session::get('user_id');
        $isAdmin = ((int)Session::get('user_role_id') === 1);

        // Lấy dữ liệu qua Service
        $data = $this->service->postDashboard($userId, $postId, $_GET);

        // Nếu admin truy cập mà không thuộc sở hữu của user đó, ta dùng Repo tìm kiếm không giới hạn chủ sở hữu
        if (!$data && $isAdmin) {
            $postRepo = new PropertyRepository();
            $post = $postRepo->findById($postId);
            if ($post) {
                // Tạo mock data postDashboard cho Admin
                $range = $this->service->parseRange($_GET);
                $summary = $this->service->getStatisticService()->getPostSummary($postId, $range['from'], $range['to']);
                $daily = $this->service->getChartService()->getDailyData((int)$post->ma_nguoi_dung, $range['from'], $range['to'], $postId);
                $monthly = $this->service->getChartService()->getMonthlyData((int)$post->ma_nguoi_dung, $postId);
                $sources = $this->service->getChartService()->getSourcesData((int)$post->ma_nguoi_dung, $range['from'], $range['to'], $postId);
                
                $data = [
                    'post'    => $post,
                    'summary' => $summary,
                    'range'   => $range,
                    'daily'   => $daily,
                    'monthly' => $monthly,
                    'sources' => $sources
                ];
            }
        }

        if (!$data) {
            $this->redirect('nguoi-dung/analytics');
        }

        $this->view('analytics/detail', [
            'title'     => 'Chi tiết hiệu quả bài đăng - ' . SITE_NAME,
            'analytics' => $data
        ]);
    }

    /**
     * API trả về dữ liệu biểu đồ cập nhật động qua AJAX.
     * URL: GET /nguoi-dung/analytics/chart
     */
    public function chart(): void
    {
        $this->requireLogin();
        $userId = (int)Session::get('user_id');
        $postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : null;

        $range = $this->service->parseRange($_GET);
        $postFilter = $_GET['post_filter'] ?? '';

        $daily = $this->service->getChartService()->getDailyData($userId, $range['from'], $range['to'], $postId, $postFilter);
        $sources = $this->service->getChartService()->getSourcesData($userId, $range['from'], $range['to'], $postId);

        $this->json([
            'success' => true,
            'daily'   => $daily,
            'sources' => $sources
        ]);
    }

    /**
     * Xuất tệp tin thống kê CSV, Excel hoặc in ấn PDF theo bộ lọc.
     * URL: GET /nguoi-dung/analytics/export
     */
    public function export(): void
    {
        $this->requireLogin();
        $userId = (int)Session::get('user_id');
        $format = $_GET['format'] ?? 'csv';
        $postFilter = $_GET['post_filter'] ?? '';

        $range = $this->service->parseRange($_GET);

        // Lấy tất cả tin đăng để xuất báo cáo (không giới hạn phân trang)
        $summary = $this->service->getStatisticService()->getSummary($userId, $range['from'], $range['to'], $postFilter);
        $topPosts = $this->service->getRepo()->topPosts($userId, $range['from'], $range['to'], 'view', 1000, 0, $postFilter);

        foreach ($topPosts as $post) {
            $post->contacts = (int)$post->calls + (int)$post->chats + (int)($post->zalos ?? 0) + (int)($post->phones ?? 0);
            $post->conversion_rate = (float)$post->conversion_rate;
        }

        $export = $this->service->getExportService();

        if ($format === 'excel') {
            $export->exportExcel($summary, $topPosts, $range);
        } elseif ($format === 'pdf') {
            $export->exportPDF($summary, $topPosts, $range);
        } else {
            $export->exportCSV($summary, $topPosts, $range);
        }
    }

    /**
     * Phương thức ghi nhận sự kiện tương tác bằng AJAX POST.
     * Hỗ trợ lưu trữ views, calls, chats, saves, shares, v.v.
     */
    public function recordView(): void { $this->event('view'); }
    public function call(): void { $this->event('call'); }
    public function chat(): void { $this->event('chat'); }
    public function save(): void { $this->event('save'); }
    public function share(): void { $this->event('share'); }
    public function phone(): void { $this->event('phone'); }
    public function zalo(): void { $this->event('zalo'); }

    private function event(string $type): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success'=>false, 'message'=>'Method not allowed.'], 405);
            return;
        }
        if (!Csrf::verify(false)) {
            $this->json(['success'=>false, 'message'=>'Yêu cầu bảo mật không hợp lệ.'], 403);
            return;
        }
        $postId = (int)($_POST['post_id'] ?? 0);
        $actorUserId = Session::get('user_id') ? (int)Session::get('user_id') : null;

        $accepted = $this->service->record($postId, $type, $actorUserId);
        $this->json(['success'=>true, 'recorded'=>$accepted]);
    }

    private function requireLogin(): void
    {
        if (!Session::get('user_id')) {
            if ($this->isAjax()) {
                $this->json(['success'=>false, 'message'=>'Unauthorized.'], 401);
                exit;
            }
            $this->redirect('nguoi-dung/dang-nhap');
        }
    }

    private function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
