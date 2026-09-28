<?php

/** Dashboard tổng hợp dành riêng cho thành viên đã xác thực email. */
class DashboardController extends Controller
{
    private DashboardService $service;

    public function __construct()
    {
        require_once APP_ROOT . '/app/validation/DashboardValidation.php';
        require_once APP_ROOT . '/app/middleware/AuthMiddleware.php';
        $this->guard();
        $this->service = new DashboardService();
    }

    public function index(): void
    {
        $period = DashboardValidation::period($_GET['period'] ?? 30);
        $dashboard = $this->service->dashboard($this->userId(), $period);
        $this->view('dashboard/index', [
            'title' => 'Dashboard thành viên – ' . SITE_NAME,
            'dashboard' => $dashboard,
        ]);
    }

    public function statistics(): void
    {
        $this->requireGet();
        $this->json(['success'=>true,'data'=>$this->service->statistics($this->userId())]);
    }

    public function recent(): void
    {
        $this->requireGet();
        $this->json(['success'=>true,'data'=>$this->service->recent($this->userId())]);
    }

    public function chart(): void
    {
        $this->requireGet();
        $period = DashboardValidation::period($_GET['period'] ?? 30);
        $this->json(['success'=>true,'period'=>$period,'data'=>$this->service->chart($this->userId(),$period)]);
    }

    public function hidePost(int $id = 0): void
    {
        $this->requirePost();
        $ok = $this->service->hidePost($this->userId(), DashboardValidation::postId($id));
        Session::flash($ok?'success':'error', $ok?'Đã ẩn tin đăng.':'Không thể ẩn tin đăng.', $ok?'alert alert-success':'alert alert-danger');
        $this->redirect('nguoi-dung/dashboard');
    }

    public function deletePost(int $id = 0): void
    {
        $this->requirePost();
        $ok = $this->service->deletePost($this->userId(), DashboardValidation::postId($id));
        Session::flash($ok?'success':'error', $ok?'Đã xóa tin đăng.':'Không thể xóa tin đăng.', $ok?'alert alert-success':'alert alert-danger');
        $this->redirect('nguoi-dung/dashboard');
    }

    private function guard(): void
    {
        AuthMiddleware::handle(true);
        if ((int)Session::get('user_role_id') === 1) {
            header('Location: ' . URL_ROOT . '/admin/dashboard');
            exit;
        }
    }

    private function userId(): int { return (int)Session::get('user_id'); }
    private function requireGet(): void
    {
        if (($_SERVER['REQUEST_METHOD']??'GET')!=='GET') $this->json(['success'=>false,'message'=>'Phương thức không được hỗ trợ.'],405);
    }
    private function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD']??'GET')!=='POST') $this->json(['success'=>false,'message'=>'Phương thức không được hỗ trợ.'],405);
        Csrf::verify();
    }
    private function json(array $data,int $status=200): never
    {
        http_response_code($status); header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }
}
