<?php
/**
 * AdminPropertyController – Quản lý tin đăng bất động sản dành cho Quản trị viên.
 * Cho phép xem danh sách, lọc, tìm kiếm, duyệt, từ chối, khóa, gia hạn, chuyển VIP và xử lý báo cáo vi phạm.
 * Quyền truy cập: Quản trị viên (Role ID = 1).
 */
class AdminPropertyController extends Controller
{
    private PropertyService $service;
    private ApprovalService $approvalSvc;
    private CategoryRepository $catRepo;
    private UserRepository $userRepo;
    private AnalyticsRepository $analyticsRepo;
    private ReportRepository $reportRepo;

    public function __construct()
    {
        require_once APP_ROOT . '/app/services/DichVuBatDongSan.php';
        require_once APP_ROOT . '/app/services/DichVuPheDuyet.php';
        require_once APP_ROOT . '/app/repositories/CategoryRepository.php';
        require_once APP_ROOT . '/app/repositories/UserRepository.php';
        require_once APP_ROOT . '/app/repositories/AnalyticsRepository.php';
        require_once APP_ROOT . '/app/repositories/ReportRepository.php';

        $this->service = new PropertyService();
        $this->approvalSvc = new ApprovalService();
        $this->catRepo = new CategoryRepository();
        $this->userRepo = new UserRepository();
        $this->analyticsRepo = new AnalyticsRepository();
        $this->reportRepo = new ReportRepository();
    }

    /**
     * Danh sách tin đăng bất động sản.
     * URL: GET /admin/du-an
     */
    public function index(): void
    {
        $this->requireAdmin();

        $page = min(500, max(1, (int)($_GET['page'] ?? 1)));
        $filters = [
            'status'           => $_GET['status'] ?? '',
            'vip_level'        => $_GET['vip_level'] ?? '',
            'category_id'      => $_GET['category_id'] ?? '',
            'transaction_type' => $_GET['transaction_type'] ?? '',
            'location'         => $_GET['location'] ?? '',
            'search'           => $_GET['search'] ?? '',
            'start_date'       => $_GET['start_date'] ?? '',
            'end_date'         => $_GET['end_date'] ?? ''
        ];

        $list = $this->service->adminList($filters, $page, 20);
        $total = $this->service->adminCount($filters);
        $totalPages = max(1, (int)ceil($total / 20));

        $stats = $this->analyticsRepo->getAdminStats();
        $categories = $this->catRepo->all();

        $this->view('admin/property/index', [
            'title'      => 'Quản lý tin đăng bất động sản - ' . SITE_NAME,
            'list'       => $list,
            'total'      => $total,
            'page'       => $page,
            'totalPages' => $totalPages,
            'stats'      => $stats,
            'categories' => $categories,
            'filters'    => $filters
        ]);
    }

    /**
     * Chi tiết tin đăng.
     * URL: GET /admin/du-an/detail/{id}
     */
    public function detail(?string $id = null): void
    {
        $this->requireAdmin();
        $postId = (int)$id;

        $post = $this->service->adminFind($postId);
        if (!$postId || !$post) {
            $_SESSION['flash_error'] = 'Tin đăng không tồn tại.';
            $this->redirect('admin/du-an');
            return;
        }

        $reportsCount = $this->reportRepo->countByPost($postId);

        $this->view('admin/property/detail', [
            'title'        => 'Chi tiết tin đăng #' . $postId . ' - ' . SITE_NAME,
            'post'         => $post,
            'reportsCount' => $reportsCount
        ]);
    }

    /**
     * Duyệt tin đăng.
     * URL: POST /admin/du-an/approve/{id}
     */
    public function approve(?string $id = null): void
    {
        $this->requireAdmin();
        $ajax = $this->isAjaxRequest();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify(false)) {
            if ($ajax) { $this->sendApprovalJson(false, 'Yêu cầu bảo mật không hợp lệ.', 403); return; }
            $this->redirect('admin/du-an');
            return;
        }

        $postId = (int)$id;
        $adminId = (int)Session::get('user_id');

        if ($this->approvalSvc->approve($postId, $adminId)) {
            $this->clearAdminDashboardCache();
            if ($ajax) { $this->sendApprovalJson(true, 'Đã duyệt và xuất bản tin đăng.'); return; }
            $_SESSION['flash_success'] = 'Đã duyệt tin đăng thành công.';
        } else {
            if ($ajax) { $this->sendApprovalJson(false, 'Tin không còn ở trạng thái chờ duyệt.', 409); return; }
            $_SESSION['flash_error'] = 'Lỗi phê duyệt tin đăng.';
        }

        $this->redirect('admin/du-an/detail/' . $postId);
    }

    /**
     * Từ chối duyệt tin đăng.
     * URL: POST /admin/du-an/reject/{id}
     */
    public function reject(?string $id = null): void
    {
        $this->requireAdmin();
        $ajax = $this->isAjaxRequest();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify(false)) {
            if ($ajax) { $this->sendApprovalJson(false, 'Yêu cầu bảo mật không hợp lệ.', 403); return; }
            $this->redirect('admin/du-an');
            return;
        }

        $postId = (int)$id;
        $adminId = (int)Session::get('user_id');
        $reason = trim((string)($_POST['reason'] ?? ''));
        if ($reason === '' || mb_strlen($reason) > 500) {
            if ($ajax) { $this->sendApprovalJson(false, 'Lý do từ chối phải từ 1 đến 500 ký tự.', 422); return; }
            $_SESSION['flash_error'] = 'Lý do từ chối không hợp lệ.';
            $this->redirect('admin/du-an/detail/'.$postId);
            return;
        }

        if ($this->approvalSvc->reject($postId, $adminId, $reason)) {
            $this->clearAdminDashboardCache();
            if ($ajax) { $this->sendApprovalJson(true, 'Đã từ chối tin đăng.'); return; }
            $_SESSION['flash_success'] = 'Đã từ chối duyệt tin đăng.';
        } else {
            if ($ajax) { $this->sendApprovalJson(false, 'Tin không còn ở trạng thái chờ duyệt.', 409); return; }
            $_SESSION['flash_error'] = 'Lỗi xử lý từ chối tin đăng.';
        }

        $this->redirect('admin/du-an/detail/' . $postId);
    }

    /**
     * Sửa tin đăng (GET form / POST update).
     * URL: GET/POST /admin/du-an/edit/{id}
     */
    public function edit(?string $id = null): void
    {
        $this->requireAdmin();
        $postId = (int)$id;

        $post = $this->service->adminFind($postId);
        if (!$postId || !$post) {
            $_SESSION['flash_error'] = 'Tin đăng không tồn tại.';
            $this->redirect('admin/du-an');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify()) {
                $_SESSION['flash_error'] = 'Yêu cầu bảo mật không hợp lệ.';
                $this->redirect('admin/du-an/edit/' . $postId);
                return;
            }

            // Chuẩn hóa dữ liệu cập nhật
            $data = [
                'category_id'      => (int)($_POST['category_id'] ?? 0),
                'title'            => trim($_POST['title'] ?? ''),
                'slug'             => trim($_POST['slug'] ?? ''),
                'description'      => trim($_POST['description'] ?? ''),
                'price'            => trim($_POST['price'] ?? '0'),
                'area'             => (float)($_POST['area'] ?? 0),
                'location'         => trim($_POST['location'] ?? ''),
                'province'         => trim($_POST['province'] ?? ''),
                'district'         => trim($_POST['district'] ?? ''),
                'ward'             => trim($_POST['ward'] ?? ''),
                'address'          => trim($_POST['address'] ?? ''),
                'property_type'    => trim($_POST['property_type'] ?? 'Căn hộ'),
                'transaction_type' => trim($_POST['transaction_type'] ?? 'ban'),
                'legal'            => trim($_POST['legal'] ?? ''),
                'direction'        => trim($_POST['direction'] ?? ''),
                'frontage'         => trim($_POST['frontage'] ?? ''),
                'bedrooms'         => $_POST['bedrooms'] !== '' ? (int)$_POST['bedrooms'] : null,
                'bathrooms'        => $_POST['bathrooms'] !== '' ? (int)$_POST['bathrooms'] : null,
                'video_url'        => trim($_POST['video_url'] ?? ''),
                'tour360'          => trim($_POST['tour360'] ?? ''),
                'contact_name'     => trim($_POST['contact_name'] ?? ''),
                'contact_phone'    => trim($_POST['contact_phone'] ?? ''),
                'project_name'     => trim($_POST['project_name'] ?? ''),
                'interior'         => trim($_POST['interior'] ?? ''),
                'width'            => $_POST['width'] !== '' ? (float)$_POST['width'] : null,
                'length'           => $_POST['length'] !== '' ? (float)$_POST['length'] : null,
                'floors'           => $_POST['floors'] !== '' ? (int)$_POST['floors'] : null,
                'construction_year'=> $_POST['construction_year'] !== '' ? (int)$_POST['construction_year'] : null,
                'status'           => trim($_POST['status'] ?? $post->trang_thai),
                'vip_level'        => (int)($_POST['vip_level'] ?? $post->goi_vip),
                'vip_expires_at'   => trim($_POST['vip_expires_at'] ?? $post->ngay_het_han_vip),
                'expires_at'       => trim($_POST['expires_at'] ?? $post->ngay_het_han),
                'meta_title'       => trim($_POST['meta_title'] ?? ''),
                'meta_description' => trim($_POST['meta_description'] ?? ''),
                'meta_keywords'    => trim($_POST['meta_keywords'] ?? '')
            ];

            if (empty($data['title']) || empty($data['slug'])) {
                $_SESSION['flash_error'] = 'Tiêu đề và đường dẫn không được để trống.';
                $this->redirect('admin/du-an/edit/' . $postId);
                return;
            }

            if ($this->service->adminUpdate($postId, $data)) {
                SystemLogger::admin(
                    'edit', 
                    'du_an', 
                    'du_an', 
                    $postId, 
                    "Chỉnh sửa tin đăng: #{$postId} - {$data['title']}", 
                    (array)$post, 
                    $data, 
                    (int)Session::get('user_id')
                );
                $_SESSION['flash_success'] = 'Cập nhật tin đăng thành công.';
                $this->redirect('admin/du-an/detail/' . $postId);
            } else {
                $_SESSION['flash_error'] = 'Lỗi cập nhật CSDL.';
                $this->redirect('admin/du-an/edit/' . $postId);
            }
            return;
        }

        $categories = $this->catRepo->all();
        $this->view('admin/property/edit', [
            'title'      => 'Chỉnh sửa tin đăng #' . $postId . ' - ' . SITE_NAME,
            'post'       => $post,
            'categories' => $categories
        ]);
    }

    /**
     * Xóa vĩnh viễn tin đăng và dữ liệu liên quan.
     * URL: POST /admin/du-an/delete/{id}
     */
    public function delete(?string $id = null): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
            $this->redirect('admin/du-an');
            return;
        }

        $postId = (int)$id;
        $post = $this->service->adminFind($postId);

        if ($post && $this->service->hardDelete($postId)) {
            SystemLogger::admin(
                'delete', 
                'du_an', 
                'du_an', 
                $postId, 
                "Xóa tin đăng: #{$postId}", 
                (array)$post, 
                [], 
                (int)Session::get('user_id')
            );
            $_SESSION['flash_success'] = 'Đã xóa vĩnh viễn tin đăng và toàn bộ dữ liệu liên quan.';
        } else {
            $_SESSION['flash_error'] = 'Lỗi xử lý xóa tin đăng.';
        }

        $this->redirect('admin/du-an');
    }

    /**
     * Ẩn tin đăng.
     * URL: POST /admin/du-an/hide/{id}
     */
    public function hide(?string $id = null): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
            $this->redirect('admin/du-an');
            return;
        }

        $postId = (int)$id;
        $post = $this->service->adminFind($postId);

        if ($this->service->toggleStatus($postId, 'hide')) {
            SystemLogger::admin('hide', 'du_an', 'du_an', $postId, "Ẩn tin đăng #{$postId}", (array)$post, ['trang_thai' => 'an'], (int)Session::get('user_id'));
            $_SESSION['flash_success'] = 'Đã ẩn tin đăng thành công.';
        } else {
            $_SESSION['flash_error'] = 'Lỗi ẩn tin.';
        }

        $this->redirect('admin/du-an/detail/' . $postId);
    }

    /**
     * Hiện tin đăng.
     * URL: POST /admin/du-an/show/{id}
     */
    public function show(?string $id = null): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
            $this->redirect('admin/du-an');
            return;
        }

        $postId = (int)$id;
        $post = $this->service->adminFind($postId);

        if ($this->service->toggleStatus($postId, 'show')) {
            SystemLogger::admin('show', 'du_an', 'du_an', $postId, "Hiển thị tin đăng #{$postId}", (array)$post, ['trang_thai' => 'xuat_ban'], (int)Session::get('user_id'));
            $_SESSION['flash_success'] = 'Đã mở hiển thị tin đăng.';
        } else {
            $_SESSION['flash_error'] = 'Lỗi hiển thị tin.';
        }

        $this->redirect('admin/du-an/detail/' . $postId);
    }

    /**
     * Khóa tin đăng.
     * URL: POST /admin/du-an/lock/{id}
     */
    public function lock(?string $id = null): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
            $this->redirect('admin/du-an');
            return;
        }

        $postId = (int)$id;
        $post = $this->service->adminFind($postId);

        if ($this->service->toggleStatus($postId, 'lock')) {
            SystemLogger::admin('lock', 'du_an', 'du_an', $postId, "Khóa tin đăng #{$postId}", (array)$post, ['trang_thai' => 'khoa'], (int)Session::get('user_id'));
            $_SESSION['flash_success'] = 'Đã khóa tin đăng thành công.';
        } else {
            $_SESSION['flash_error'] = 'Lỗi khóa tin.';
        }

        $this->redirect('admin/du-an/detail/' . $postId);
    }

    /**
     * Mở khóa tin đăng.
     * URL: POST /admin/du-an/unlock/{id}
     */
    public function unlock(?string $id = null): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
            $this->redirect('admin/du-an');
            return;
        }

        $postId = (int)$id;
        $post = $this->service->adminFind($postId);

        if ($this->service->toggleStatus($postId, 'unlock')) {
            SystemLogger::admin('unlock', 'du_an', 'du_an', $postId, "Mở khóa tin đăng #{$postId}", (array)$post, ['trang_thai' => 'xuat_ban'], (int)Session::get('user_id'));
            $_SESSION['flash_success'] = 'Đã mở khóa tin đăng.';
        } else {
            $_SESSION['flash_error'] = 'Lỗi mở khóa tin.';
        }

        $this->redirect('admin/du-an/detail/' . $postId);
    }

    /**
     * Thay đổi gói VIP của tin đăng.
     * URL: POST /admin/du-an/vip/{id}
     */
    public function vip(?string $id = null): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
            $this->redirect('admin/du-an');
            return;
        }

        $postId = (int)$id;
        $vipLevel = (int)($_POST['vip_level'] ?? 0);
        $days = (int)($_POST['days'] ?? 7);

        $post = $this->service->adminFind($postId);

        if ($this->service->changeVip($postId, $vipLevel, $days)) {
            SystemLogger::admin(
                'vip', 
                'du_an', 
                'du_an', 
                $postId, 
                "Chuyển gói VIP tin đăng: #{$postId} sang VIP {$vipLevel} ({$days} ngày)", 
                (array)$post, 
                ['goi_vip' => $vipLevel, 'days' => $days], 
                (int)Session::get('user_id')
            );
            $_SESSION['flash_success'] = 'Đổi gói VIP tin đăng thành công.';
        } else {
            $_SESSION['flash_error'] = 'Lỗi chuyển gói VIP.';
        }

        $this->redirect('admin/du-an/detail/' . $postId);
    }

    /**
     * Gia hạn tin đăng.
     * URL: POST /admin/du-an/renew/{id}
     */
    public function renew(?string $id = null): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
            $this->redirect('admin/du-an');
            return;
        }

        $postId = (int)$id;
        $days = (int)($_POST['days'] ?? 30);

        $post = $this->service->adminFind($postId);

        if ($this->service->renew($postId, $days)) {
            SystemLogger::admin(
                'renew', 
                'du_an', 
                'du_an', 
                $postId, 
                "Gia hạn hiển thị tin đăng #{$postId} thêm {$days} ngày", 
                (array)$post, 
                ['days' => $days], 
                (int)Session::get('user_id')
            );
            $_SESSION['flash_success'] = 'Gia hạn hiển thị tin đăng thành công.';
        } else {
            $_SESSION['flash_error'] = 'Lỗi gia hạn hiển thị.';
        }

        $this->redirect('admin/du-an/detail/' . $postId);
    }

    /**
     * Trang xử lý Báo cáo vi phạm.
     * URL: GET /admin/du-an/report
     */
    public function report(): void
    {
        $this->requireAdmin();

        $list = $this->reportRepo->adminList();

        $this->view('admin/property/report', [
            'title' => 'Quản lý báo cáo vi phạm - ' . SITE_NAME,
            'list'  => $list
        ]);
    }

    /**
     * Đánh dấu xử lý / hủy bỏ Báo cáo vi phạm.
     * URL: POST /admin/du-an/resolve-report/{id}
     */
    public function resolveReport(?string $id = null): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
            $this->redirect('admin/du-an/report');
            return;
        }

        $repId = (int)$id;
        $status = trim($_POST['status'] ?? 'da_xu_ly'); // da_xu_ly or da_huy
        $status = in_array($status, ['da_xu_ly', 'da_huy'], true) ? $status : 'da_huy';
        $note = trim($_POST['ghi_chu_admin'] ?? '');

        if ($note !== '' && $this->resolveReportedPost($repId, $status, $note)) {
            SystemLogger::admin(
                'resolve_report', 
                'bao_cao_vi_pham', 
                'bao_cao_vi_pham', 
                $repId, 
                "Xử lý báo cáo vi phạm #{$repId} sang trạng thái: {$status}", 
                [], 
                ['trang_thai' => $status, 'ghi_chu' => $note], 
                (int)Session::get('user_id')
            );
            $_SESSION['flash_success'] = 'Đã cập nhật trạng thái xử lý báo cáo vi phạm.';
        } else {
            $_SESSION['flash_error'] = 'Lỗi xử lý báo cáo vi phạm.';
        }

        $this->redirect('admin/du-an/report');
    }

    /**
     * Chap nhan: xoa mem tin va dong bao cao trong cung transaction.
     * Tu choi: chi dong bao cao; tin dang duoc giu nguyen.
     */
    private function resolveReportedPost(int $reportId, string $status, string $note): bool
    {
        $report = $this->reportRepo->pendingById($reportId);
        if (!$report || !$this->reportRepo->begin()) {
            return false;
        }

        $postId = (int)$report->ma_du_an;
        $post = $this->service->adminFind($postId);

        try {
            if ($status === 'da_xu_ly' && (!$post || !$this->service->softDeleteByReport($postId))) {
                $this->reportRepo->rollBack();
                return false;
            }

            if (!$this->reportRepo->adminResolve($reportId, $status, $note)) {
                $this->reportRepo->rollBack();
                return false;
            }

            if (!$this->reportRepo->commit()) {
                return false;
            }

            if ($status === 'da_xu_ly' && $post) {
                SystemLogger::admin(
                    'delete_reported_post',
                    'du_an',
                    'du_an',
                    $postId,
                    "Xóa mềm tin #{$postId} do chấp nhận báo cáo vi phạm #{$reportId}",
                    (array)$post,
                    ['trang_thai' => 'xoa', 'deleted_at' => date('Y-m-d H:i:s'), 'report_id' => $reportId],
                    (int)Session::get('user_id')
                );
            }

            $this->clearAdminDashboardCache();
            return true;
        } catch (Throwable $e) {
            $this->reportRepo->rollBack();
            error_log('[REPORT RESOLVE] ' . $e->getMessage());
            return false;
        }
    }

    private function requireAdmin(): void
    {
        if ((int)Session::get('user_id') <= 0 || (int)Session::get('user_role_id') !== 1) {
            $this->redirect('nguoi-dung/dang-nhap');
            exit;
        }
    }

    private function isAjaxRequest(): bool
    {
        return strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    }

    private function sendApprovalJson(bool $success, string $message, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status'=>$success?'success':'error','success'=>$success,'message'=>$message], JSON_UNESCAPED_UNICODE);
    }

    private function clearAdminDashboardCache(): void
    {
        foreach (glob(sys_get_temp_dir().'/admin_dashboard_*.json') ?: [] as $file) { @unlink($file); }
    }
}
