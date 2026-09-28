<?php

class PostController extends Controller
{
    private PostService $service;

    public function __construct()
    {
        require_once APP_ROOT.'/app/validation/PostValidation.php';
        require_once APP_ROOT.'/app/middleware/AuthMiddleware.php';
        AuthMiddleware::handle(true);
        $this->service = new PostService;
    }

    public function index(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->store();

            return;
        }$this->create();
    }

    public function create(): void
    {
        $d = $this->service->formData($this->uid());
        $captcha = substr(str_shuffle('23456789ABCDEFGHJKLMNPQRSTUVWXYZ'), 0, 5);
        Session::set('captcha_post', $captcha);
        $this->view('nguoi-dung/post', ['title' => 'Đăng tin bất động sản – '.SITE_NAME, 'categories' => $d['categories'], 'vipPrices' => $d['vipPrices'], 'upPackages' => $d['upPackages'], 'upTurns' => (int) ($d['user']->luot_up_tin ?? 0), 'discount' => 0, 'totalViews' => 0, 'captcha' => $captcha, 'errors' => Session::get('post_errors') ?: [], 'old' => Session::get('post_old') ?: []]);
        Session::delete('post_errors');
        Session::delete('post_old');
    }

    public function store(): void
    {
        $this->postOnly();
        Csrf::verify();
        $r = $this->service->create($this->uid(), $_POST, false);
        $this->finish($r, 'nguoi-dung/post');
    }

    public function edit(int $id = 0): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->postOnly();
            Csrf::verify();
            $this->finish($this->service->update($this->uid(), $id, $_POST, false), 'nguoi-dung/editPost/'.$id);

            return;
        }$d = $this->service->formData($this->uid(), $id);
        if (! $d['project']) {
            $this->notFound();

            return;
        }$this->view('nguoi-dung/edit_post', ['title' => 'Chỉnh sửa tin đăng – '.SITE_NAME, 'project' => $d['project'], 'images' => $d['images'], 'categories' => $d['categories'], 'errors' => Session::get('post_errors') ?: []]);
        Session::delete('post_errors');
    }

    public function delete(int $id = 0): void
    {
        $this->postOnly();
        Csrf::verify();
        $this->flash($this->service->delete($this->uid(), $id), 'Đã xóa tin đăng.', 'Không thể xóa tin đăng.');
    }

    public function hide(int $id = 0): void
    {
        $this->postOnly();
        Csrf::verify();
        $this->flash($this->service->status($this->uid(), $id, 'an'), 'Đã ẩn tin đăng.', 'Không thể ẩn tin đăng.');
    }

    public function show(int $id = 0): void
    {
        $this->postOnly();
        Csrf::verify();
        $this->flash($this->service->status($this->uid(), $id, 'cho_duyet'), 'Tin đã được gửi duyệt lại.', 'Không thể hiện tin đăng.');
    }

    public function renew(int $id = 0): void
    {
        $this->postOnly();
        Csrf::verify();
        $id = $id ?: ((int) ($_POST['project_id'] ?? 0));
        $r = $this->service->renew($this->uid(), $id, (int) ($_POST['days'] ?? 0));
        $this->flash($r['success'], $r['message'], $r['message']);
    }

    public function up(int $id = 0): void
    {
        $this->postOnly();
        Csrf::verify();
        $id = $id ?: ((int) ($_POST['project_id'] ?? 0));
        $r = $this->service->up($this->uid(), $id);
        $this->flash($r['success'], $r['message'], $r['message']);
    }

    public function toggleAutoRenew(int $id = 0): void
    {
        $this->postOnly();
        Csrf::verify();
        $id = $id ?: ((int) ($_POST['project_id'] ?? 0));
        $ok = $this->service->toggleAuto($this->uid(), $id, (int) ($_POST['status'] ?? 0));
        $this->json(['success' => $ok, 'message' => $ok ? 'Đã cập nhật tự động gia hạn.' : 'Không thể cập nhật.'], $ok ? 200 : 400);
    }

    public function preview(): void
    {
        $this->postOnly();
        Csrf::verify();
        $this->view('post/preview', ['title' => 'Xem trước tin đăng', 'preview' => $_POST]);
    }

    public function deleteImage(int $id = 0): void
    {
        $this->postOnly();
        Csrf::verify();
        $postId = (int) ($_POST['post_id'] ?? 0);
        $post = (new PostRepository)->findOwned($postId, $this->uid());
        $ok = $post && (new ImageService)->deleteOwned($id, $postId);
        $this->json(['success' => (bool) $ok], $ok ? 200 : 404);
    }

    public function my(): void
    {
        $limit = 20;
        $currentPage = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->service->listPage($this->uid(), $currentPage, $limit);
        $totalPosts = $result['total'];
        $totalPages = (int) ceil($totalPosts / $limit);

        if ($totalPages > 0 && $currentPage > $totalPages) {
            $currentPage = $totalPages;
            $result = $this->service->listPage($this->uid(), $currentPage, $limit);
        }

        $this->view('post/list', [
            'title' => 'Quản lý tin đăng – '.SITE_NAME,
            'posts' => $result['items'],
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalPosts' => $totalPosts,
        ]);
    }

    private function finish(array $r, string $back): void
    {
        if (! $r['success']) {
            Session::set('post_errors', $r['errors'] ?? []);
            Session::set('post_old', $_POST);
            $this->redirect($back);
        }Session::set('success', $r['message']);
        $this->redirect('nguoi-dung/myPost');
    }

    private function flash(bool $ok, string $yes, string $no): void
    {
        Session::set($ok ? 'success' : 'error', $ok ? $yes : $no);
        $this->redirect('nguoi-dung/myPost');
    }

    private function uid(): int
    {
        return (int) Session::get('user_id');
    }

    private function postOnly(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->json(['success' => false, 'message' => 'Phương thức không được hỗ trợ.'], 405);
        }
    }

    private function json(array $d, int $s = 200): never
    {
        http_response_code($s);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($d,JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function notFound(): void
    {
        http_response_code(404);
        $this->view('errors/404',['title' => 'Không tìm thấy tin đăng']);
    }
}
