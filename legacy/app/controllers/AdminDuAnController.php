<?php
/**
 * Controller AdminProject - Quản lý tin đăng bất động sản (Admin).
 * URL: /admin/du-an/{method}/{id}
 *
 * Quyền truy cập: Chỉ Admin (role_id = 1)
 */
class AdminDuAnController extends Controller
{
    /**
     * Kiểm tra quyền Admin trước khi cho phép truy cập.
     */
    public function __construct()
    {
        Auth::requireRole(1);
    }

    // ==========================================
    // DANH SÁCH TIN ĐĂNG
    // ==========================================

    /**
     * Hiển thị danh sách tất cả tin đăng của tất cả người dùng.
     * Hiển thị số tin chờ duyệt để Admin biết có việc cần làm.
     *
     * URL: GET /admin/du-an
     */
    public function index(): void
    {
        $projectModel = $this->model('DuAn');

        $propertyTypes = $projectModel->layLoaiHinhAdmin();
        $requestedType = trim($_GET['property_type'] ?? '');
        $filters = [
            'keyword'       => trim($_GET['keyword'] ?? ''),
            'property_type' => in_array($requestedType, $propertyTypes, true) ? $requestedType : '',
            'status'        => in_array(($_GET['status'] ?? ''), ['cho_duyet', 'xuat_ban', 'nhap', 'da_ban'], true)
                ? $_GET['status']
                : '',
        ];

        $perPage = 10;
        $totalProjects = $projectModel->demAdminTheoBoLoc($filters);
        $totalPages = max(1, (int)ceil($totalProjects / $perPage));
        $currentPage = min(max(1, (int)($_GET['page'] ?? 1)), $totalPages);

        $data = [
            'title'          => 'Quản Lý Tin Đăng - ' . SITE_NAME,
            'projects'       => $projectModel->layAdminPhanTrang(
                $filters,
                $perPage,
                ($currentPage - 1) * $perPage
            ),
            'pendingCount'   => $projectModel->demChoDuyet(),
            'filters'        => $filters,
            'propertyTypes'  => $propertyTypes,
            'totalProjects'  => $totalProjects,
            'currentPage'    => $currentPage,
            'totalPages'     => $totalPages,
        ];

        $this->view('admin/du-an/index', $data);
    }

    // ==========================================
    // DUYỆT TIN ĐĂNG
    // ==========================================

    /**
     * Duyệt tin đăng: chuyển trạng_thai từ 'cho_duyet' sang 'xuat_ban'.
     * Sau khi duyệt, gửi thông báo đến người đăng.
     * Hỗ trợ cả AJAX và form POST thông thường.
     *
     * URL: POST /admin/du-an/approve/{id}
     *
     * @param int $id ID tin đăng cần duyệt
     */
    public function approve(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/du-an');
        }

        Csrf::verify();

        $projectModel = $this->model('DuAn');
        $userModel    = $this->model('NguoiDung');
        $tinDang      = $projectModel->findById($id);

        // Chỉ duyệt khi tin đang ở trạng thái 'cho_duyet'
        if (!$tinDang || $tinDang->trang_thai !== 'cho_duyet') {
            $this->jsonOrFlash('admin_msg', 'error', 'Tin đăng không tồn tại hoặc đã được xử lý.', 'alert alert-warning');
            $this->redirect('admin/du-an');
        }

        if ($projectModel->duyetTin($id)) {
            AuditMiddleware::enrich('Duyệt và xuất bản tin đăng', ['trang_thai'=>$tinDang->trang_thai], ['trang_thai'=>'xuat_ban'], 'du_an', $id);
            // Gửi thông báo cho người đăng tin
            $userModel->taoThongBao(
                $tinDang->ma_nguoi_dung,
                'Tin đăng được duyệt',
                "Tin đăng '{$tinDang->tieu_de}' của bạn đã được Admin duyệt và xuất bản thành công."
            );
            $this->jsonOrFlash('admin_msg', 'success', 'Đã duyệt tin đăng thành công!');
        } else {
            $this->jsonOrFlash('admin_msg', 'error', 'Có lỗi xảy ra khi duyệt tin!', 'alert alert-danger');
        }

        $this->redirect('admin/du-an');
    }

    // ==========================================
    // TỪ CHỐI TIN ĐĂNG
    // ==========================================

    /**
     * Từ chối tin đăng và lưu lý do để thành viên có thể chỉnh sửa.
     * Hỗ trợ cả AJAX và form POST thông thường.
     *
     * URL: POST /admin/du-an/reject/{id}
     *
     * @param int $id ID tin đăng cần từ chối
     */
    public function reject(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/du-an');
        }

        Csrf::verify();

        $projectModel = $this->model('DuAn');
        $userModel    = $this->model('NguoiDung');
        $tinDang      = $projectModel->findById($id);

        if (!$tinDang || $tinDang->trang_thai !== 'cho_duyet') {
            $this->jsonOrFlash('admin_msg', 'error', 'Tin đăng không tồn tại hoặc đã được xử lý.', 'alert alert-warning');
            $this->redirect('admin/du-an');
        }

        $reason = trim((string)($_POST['reason'] ?? 'Tin đăng chưa đáp ứng tiêu chí kiểm duyệt.'));
        if ($projectModel->tuChoiTin($id, $reason)) {
            AuditMiddleware::enrich('Từ chối tin đăng', ['trang_thai'=>$tinDang->trang_thai], ['trang_thai'=>'tu_choi','ly_do_tu_choi'=>$reason], 'du_an', $id);
            // Gửi thông báo cho người đăng tin
            $userModel->taoThongBao(
                $tinDang->ma_nguoi_dung,
                'Tin đăng bị từ chối',
                "Tin đăng '{$tinDang->tieu_de}' của bạn chưa đạt yêu cầu. Lý do: {$reason}"
            );
            $this->jsonOrFlash('admin_msg', 'success', 'Đã từ chối tin đăng!');
        } else {
            $this->jsonOrFlash('admin_msg', 'error', 'Có lỗi xảy ra khi từ chối tin!', 'alert alert-danger');
        }

        $this->redirect('admin/du-an');
    }

    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/du-an');
        }

        Csrf::verify();
        $projectModel = $this->model('DuAn');
        $project = $projectModel->findById($id);
        if (!$project) {
            Session::flash('admin_msg', 'Tin đăng không tồn tại.', 'alert alert-warning');
        } else {
            // Xóa file ảnh gallery trên đĩa trước khi xóa DB (CASCADE sẽ xóa record)
            $db = new Database();
            $db->query("SELECT duong_dan_anh FROM hinh_anh_du_an WHERE ma_du_an = :id");
            $db->bind(':id', $id);
            $images = $db->resultSet();
            foreach ($images as $img) {
                $imgPath = APP_ROOT . '/public/' . ($img->duong_dan_anh ?? '');
                if (is_file($imgPath)) {
                    @unlink($imgPath);
                }
            }

            // Xóa ảnh thu nhỏ trên đĩa
            if (!empty($project->anh_thu_nho)) {
                $thumbPath = APP_ROOT . '/public/' . $project->anh_thu_nho;
                if (is_file($thumbPath)) {
                    @unlink($thumbPath);
                }
            }

            // Xóa bản ghi so_sanh liên kết (không có CASCADE)
            $db->query("DELETE FROM so_sanh WHERE ma_du_an = :id");
            $db->bind(':id', $id);
            $db->execute();

            if ($projectModel->delete($id)) {
                // Ghi log admin (dùng SystemLogger thay vì AuditMiddleware để nhất quán)
                SystemLogger::admin(
                    'delete',
                    'du_an',
                    'du_an',
                    $id,
                    "Xóa tin đăng: #{$id} - " . htmlspecialchars($project->tieu_de ?? ''),
                    (array)$project,
                    [],
                    (int)Session::get('user_id')
                );
                Session::flash('admin_msg', 'Đã xóa tin đăng thành công!');
            } else {
                Session::flash('admin_msg', 'Không thể xóa tin đăng.', 'alert alert-danger');
            }
        }
        $this->redirect('admin/du-an');
    }

    // ==========================================
    // HÀM HELPER
    // ==========================================

    /**
     * Phản hồi theo ngữ cảnh: JSON nếu là AJAX request, Flash session nếu là form thường.
     * Tránh lặp code kiểm tra AJAX ở mỗi hành động.
     *
     * @param string $flashKey  Khoá Session flash (khi không phải AJAX)
     * @param string $status    'success' hoặc 'error'
     * @param string $message   Nội dung thông báo
     * @param string $cssClass  CSS class cho flash message (mặc định success)
     */
    private function jsonOrFlash(
        string $flashKey,
        string $status,
        string $message,
        string $cssClass = 'alert alert-success'
    ): void {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => $status, 'message' => $message]);
            exit;
        }

        Session::flash($flashKey, $message, $cssClass);
    }
}
