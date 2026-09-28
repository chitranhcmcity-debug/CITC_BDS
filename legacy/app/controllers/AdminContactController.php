<?php
/**
 * Controller AdminContact – Quản lý CRM và liên hệ của khách hàng trong trang Admin.
 * URL: /admin/contact
 */
class AdminContactController extends Controller
{
    private ContactRepository $contactRepo;

    public function __construct()
    {
        // Yêu cầu quyền Admin (role_id = 1)
        Auth::requireRole(1);

        require_once APP_ROOT . '/app/repositories/ContactRepository.php';
        $this->contactRepo = new ContactRepository();
    }

    /**
     * Hiển thị danh sách liên hệ lọc nâng cao & thống kê CRM.
     * URL: GET /admin/contact
     */
    public function index(): void
    {
        // 1. Phân trang & Bộ lọc
        $limit = 15;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $filters = [
            'status'         => $_GET['status'] ?? '',
            'type'           => $_GET['type'] ?? '',
            'assigned_admin' => $_GET['assigned_admin'] ?? '',
            'search'         => $_GET['search'] ?? '',
            'date_from'      => $_GET['date_from'] ?? '',
            'date_to'        => $_GET['date_to'] ?? ''
        ];

        // 2. Query danh sách
        $contacts = $this->contactRepo->getAll($filters, $limit, $offset);
        $totalItems = $this->contactRepo->countAll($filters);
        $totalPages = ceil($totalItems / $limit);

        // Nạp thêm đính kèm cho từng item
        foreach ($contacts as &$item) {
            $item = (array)$item;
            $item['attachments'] = $this->contactRepo->getAttachments((int)$item['id']);
        }

        // 3. Lấy danh sách admin để phân công
        $adminsList = $this->getAdminsList();

        // 4. Lấy dữ liệu thống kê phân tích CRM
        $analytics = $this->contactRepo->getAnalytics();

        $data = [
            'title'        => 'Quản lý Liên Hệ & CRM - ' . SITE_NAME,
            'contacts'     => $contacts,
            'totalItems'   => $totalItems,
            'totalPages'   => $totalPages,
            'currentPage'  => $page,
            'filters'      => $filters,
            'admins'       => $adminsList,
            'analytics'    => $analytics
        ];

        $this->view('admin/contact/index', $data);
    }

    /**
     * Cập nhật thông tin yêu cầu (phân công, ghi chú, trạng thái).
     * URL: POST /admin/contact/update
     */
    public function update($id = null): void
    {
        $id = $id ?: ($_POST['id'] ?? null);
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/contact');
            return;
        }

        Csrf::verify();

        $updateData = [
            'status'         => $_POST['status'] ?? 'moi',
            'assigned_admin' => !empty($_POST['assigned_admin']) ? (int)$_POST['assigned_admin'] : null,
            'note'           => trim($_POST['note'] ?? '')
        ];

        $ok = $this->contactRepo->update((int)$id, $updateData);
        if ($ok) {
            Session::flash('admin_contact_success', 'Cập nhật trạng thái yêu cầu #' . $id . ' thành công!');
        } else {
            Session::flash('admin_contact_error', 'Không có thông tin nào thay đổi.');
        }

        $this->redirect('admin/contact');
    }

    /**
     * Xóa yêu cầu liên hệ.
     * URL: POST /admin/contact/delete
     */
    public function delete($id = null): void
    {
        $id = $id ?: ($_POST['id'] ?? null);
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/contact');
            return;
        }

        Csrf::verify();

        // Lấy thông tin liên hệ trước khi xóa để ghi log
        $contact = $this->contactRepo->findById((int)$id);

        // Lấy danh sách file để xóa vật lý trên đĩa trước khi xóa DB
        $attachments = $this->contactRepo->getAttachments((int)$id);
        foreach ($attachments as $file) {
            $filePath = APP_ROOT . '/public/uploads/' . $file->file_path;
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        $ok = $this->contactRepo->delete((int)$id);
        if ($ok) {
            // Ghi log admin
            SystemLogger::admin(
                'delete',
                'lien_he',
                'contacts',
                (int)$id,
                "Xóa yêu cầu liên hệ: #{$id}" . ($contact ? " - " . ($contact['fullname'] ?? '') : ''),
                $contact ?: [],
                [],
                (int)Session::get('user_id')
            );

            Session::flash('admin_contact_success', 'Xóa yêu cầu liên hệ thành công!');
        } else {
            Session::flash('admin_contact_error', 'Xóa yêu cầu thất bại.');
        }

        $this->redirect('admin/contact');
    }

    /**
     * Xuất danh sách liên hệ ra file CSV (Mở được bằng Excel).
     * URL: GET /admin/contact/export
     */
    public function export(): void
    {
        $filters = [
            'status'         => $_GET['status'] ?? '',
            'type'           => $_GET['type'] ?? '',
            'assigned_admin' => $_GET['assigned_admin'] ?? '',
            'search'         => $_GET['search'] ?? '',
            'date_from'      => $_GET['date_from'] ?? '',
            'date_to'        => $_GET['date_to'] ?? ''
        ];

        // Lấy toàn bộ không giới hạn để xuất file
        $contacts = $this->contactRepo->getAll($filters, 10000, 0);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="crm_contacts_' . date('Ymd_His') . '.csv"');

        $output = fopen('php://output', 'w');
        
        // Ghi BOM UTF-8 để Excel hiển thị đúng tiếng Việt
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Ghi dòng header
        fputcsv($output, [
            'ID', 
            'Họ và tên', 
            'Số điện thoại', 
            'Email', 
            'Nhu cầu', 
            'Tiêu đề', 
            'Nội dung', 
            'Trạng thái', 
            'Chuyên viên phụ trách', 
            'Ghi chú nội bộ', 
            'IP người gửi', 
            'Trình duyệt', 
            'Thiết bị', 
            'Ngày tạo'
        ]);

        foreach ($contacts as $c) {
            $c = (array)$c;
            $typeLabel = match($c['type']) {
                'mua_nha'       => 'Mua nhà',
                'thue_nha'      => 'Thuê nhà',
                'dang_ban'      => 'Đăng bán',
                'dang_cho_thue' => 'Đăng cho thuê',
                'hop_tac'       => 'Hợp tác',
                'khieu_nai'     => 'Khiếu nại',
                default         => 'Khác'
            };

            $statusLabel = match($c['status']) {
                'moi'          => 'Mới',
                'dang_xu_ly'   => 'Đang xử lý',
                'da_lien_he'   => 'Đã liên hệ',
                'hoan_thanh'   => 'Hoàn thành',
                'huy'          => 'Hủy',
                default        => 'Khác'
            };

            fputcsv($output, [
                $c['id'],
                $c['fullname'],
                $c['phone'],
                $c['email'],
                $typeLabel,
                $c['subject'],
                $c['content'],
                $statusLabel,
                $c['admin_name'] ?: 'Chưa phân công',
                $c['note'],
                $c['ip_address'],
                $c['browser'],
                $c['device'],
                $c['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Lấy danh sách chuyên viên admin.
     */
    private function getAdminsList(): array
    {
        try {
            $db = new Database();
            $db->query("SELECT id, ten, email FROM nguoi_dung WHERE ma_vai_tro = 1 AND trang_thai = 'hoat_dong'");
            return $db->resultSet() ?: [];
        } catch (Exception $e) {
            error_log("getAdminsList error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Gợi ý trả lời tự động bằng AI (Gemini).
     * URL: GET /admin/contact/aiSuggest
     */
    public function aiSuggest(): void
    {
        header('Content-Type: application/json');
        $content = $_GET['content'] ?? '';
        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Nội dung yêu cầu trống.']);
            exit;
        }

        require_once APP_ROOT . '/app/services/DichVuCRM.php';
        $crm = new CRMService();
        $suggestion = $crm->aiSuggestResponse($content);

        if ($suggestion) {
            echo json_encode(['success' => true, 'suggestion' => $suggestion]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể kết nối đến máy chủ AI.']);
        }
        exit;
    }
}
