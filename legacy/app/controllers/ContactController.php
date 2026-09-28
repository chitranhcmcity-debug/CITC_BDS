<?php
/**
 * ContactController – Điều phối hoạt động gửi liên hệ, hiển thị lịch sử và CRM API.
 * URL: /contact (alias: /lien-he)
 */
class ContactController extends Controller
{
    private ContactService $contactSvc;
    private ContactRepository $contactRepo;

    public function __construct()
    {
        require_once APP_ROOT . '/app/services/DichVuLienHe.php';
        require_once APP_ROOT . '/app/repositories/ContactRepository.php';

        $this->contactSvc  = new ContactService();
        $this->contactRepo = new ContactRepository();
    }

    /**
     * Hiển thị trang liên hệ (GET)
     * URL: GET /contact
     */
    public function index(): void
    {
        // Cache thông tin công ty và cài đặt trong 5 phút
        $companyInfo = $this->getCompanySettingsCached();

        $data = [
            'title'        => SITE_NAME . ' - Liên Hệ & Yêu Cầu Tư Vấn',
            'fullname'     => '',
            'phone'        => '',
            'email'        => '',
            'subject'      => '',
            'content'      => '',
            'type'         => 'khac',
            'errors'       => [],
            'company'      => $companyInfo
        ];

        $this->view('contact/index', $data);
    }

    /**
     * Xử lý gửi yêu cầu liên hệ / tư vấn (POST)
     * URL: POST /contact
     */
    public function store(): void
    {
        $isAjax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
                exit;
            }
            $this->redirect('contact');
            return;
        }

        // Kiểm tra bảo mật CSRF
        if (!Csrf::verify(false)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Phiên bảo mật hết hạn. Vui lòng tải lại trang.']);
                exit;
            }
            Session::flash('contact_error', 'Phiên bảo mật hết hạn. Vui lòng thử lại.');
            $this->redirect('contact');
            return;
        }

        // Xử lý gửi dữ liệu qua Service
        $result = $this->contactSvc->createContact($_POST, $_FILES);

        if ($result['success']) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => $result['message'],
                    'redirect' => URL_ROOT . '/contact/success'
                ]);
                exit;
            }
            Session::flash('contact_success_msg', $result['message']);
            $this->redirect('contact/success');
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => $result['errors']]);
                exit;
            }

            $companyInfo = $this->getCompanySettingsCached();
            $data = [
                'title'    => SITE_NAME . ' - Liên Hệ & Yêu Cầu Tư Vấn',
                'fullname' => $_POST['fullname'] ?? '',
                'phone'    => $_POST['phone'] ?? '',
                'email'    => $_POST['email'] ?? '',
                'subject'  => $_POST['subject'] ?? '',
                'content'  => $_POST['content'] ?? '',
                'type'     => $_POST['type'] ?? 'khac',
                'errors'   => $result['errors'],
                'company'  => $companyInfo
            ];

            $this->view('contact/index', $data);
        }
    }

    /**
     * Trang thông báo gửi liên hệ thành công (GET)
     * URL: GET /contact/success
     */
    public function success(): void
    {
        $data = [
            'title'   => 'Gửi yêu cầu thành công - ' . SITE_NAME,
            'message' => Session::flash('contact_success_msg') ?: 'Cảm ơn bạn đã gửi yêu cầu. Chúng tôi sẽ liên hệ trong thời gian sớm nhất.'
        ];
        $this->view('contact/success', $data);
    }

    /**
     * AJAX upload file độc lập phục vụ cho việc kéo thả đính kèm (nếu cần mở rộng)
     * URL: POST /contact/upload
     */
    public function upload(): void
    {
        header('Content-Type: application/json');
        if (empty($_FILES['file'])) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy file để tải lên.']);
            exit;
        }

        $validation = new ContactValidation();
        $fileErr = $validation->validateFile($_FILES['file']);
        if ($fileErr) {
            echo json_encode(['success' => false, 'message' => $fileErr]);
            exit;
        }

        $uploadDir = APP_ROOT . '/public/uploads/contacts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $originalName = $_FILES['file']['name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $randomName = 'contact_attachment_temp_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $destPath = $uploadDir . $randomName;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $destPath)) {
            echo json_encode([
                'success' => true,
                'file_name' => $originalName,
                'file_path' => 'contacts/' . $randomName,
                'file_type' => $_FILES['file']['type'],
                'file_size' => $_FILES['file']['size']
            ]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Lỗi lưu trữ file tải lên.']);
        exit;
    }

    /**
     * Lịch sử liên hệ của thành viên (GET)
     * URL: GET /contact/history
     */
    public function history(): void
    {
        // Yêu cầu đăng nhập thành viên
        if (!Session::get('user_id')) {
            Session::flash('login_required', 'Vui lòng đăng nhập để xem lịch sử yêu cầu tư vấn.');
            $this->redirect('nguoi-dung/dang-nhap');
            return;
        }

        $email = (string)Session::get('user_email');
        $phone = ''; // Có thể lấy thêm sđt từ user trong DB nếu cần thiết

        try {
            $db = new Database();
            $db->query("SELECT dien_thoai FROM nguoi_dung WHERE id = :id");
            $db->bind(':id', Session::get('user_id'));
            $phone = (string)$db->singleColumn();
        } catch (Exception $e) {
            error_log("History user query error: " . $e->getMessage());
        }

        // Phân trang
        $limit = 15;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $historyList = $this->contactRepo->getHistory($email, $phone, $limit, $offset);
        $totalHistory = $this->contactRepo->countHistory($email, $phone);
        $totalPages = ceil($totalHistory / $limit);

        // Nạp thêm file đính kèm cho từng yêu cầu
        foreach ($historyList as &$item) {
            $item = (array)$item;
            $item['attachments'] = $this->contactRepo->getAttachments((int)$item['id']);
        }

        $data = [
            'title'       => 'Lịch sử yêu cầu tư vấn - ' . SITE_NAME,
            'history'     => $historyList,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'totalItems'  => $totalHistory
        ];

        $this->view('contact/history', $data);
    }

    /**
     * Đọc thông tin cài đặt công ty từ bảng cai_dat và thực hiện cache 5 phút (300 giây).
     */
    private function getCompanySettingsCached(): array
    {
        $cacheKey = 'company_settings_cache';
        
        // Sử dụng session hoặc lưu bộ nhớ tạm PHP để mô phỏng bộ nhớ đệm
        if (isset($_SESSION[$cacheKey]) && (time() - $_SESSION[$cacheKey . '_time'] < 300)) {
            return $_SESSION[$cacheKey];
        }

        $settings = [];
        try {
            $db = new Database();
            $db->query("SELECT khoa_cai_dat, gia_tri_cai_dat FROM cai_dat");
            $rows = $db->resultSet() ?: [];
            foreach ($rows as $row) {
                $settings[$row->khoa_cai_dat] = $row->gia_tri_cai_dat;
            }
        } catch (Exception $e) {
            error_log("Get company settings error: " . $e->getMessage());
        }

        // Dự phòng nếu bảng cài đặt chưa có đủ key
        $fallback = [
            'site_name'        => $settings['site_name'] ?? 'Bất động sản CITC',
            'site_description' => $settings['site_description'] ?? 'Nền tảng bất động sản hàng đầu',
            'logo'             => $settings['logo'] ?? 'logo.png',
            'contact_email'    => $settings['contact_email'] ?? 'info@citc-bds.com',
            'contact_phone'    => $settings['contact_phone'] ?? '+84 123 456 789',
            'contact_address'  => $settings['contact_address'] ?? '123 Đường Ba Tháng Hai, Quận 10, TP. Hồ Chí Minh',
            'facebook_url'     => $settings['facebook_url'] ?? 'https://facebook.com/citcbds',
            'instagram_url'    => $settings['instagram_url'] ?? '#',
            'zalo_number'      => $settings['zalo_number'] ?? '0123456789',
            'working_hours'    => 'Thứ 2 - Thứ 7: 8:00 - 18:00'
        ];

        $_SESSION[$cacheKey] = $fallback;
        $_SESSION[$cacheKey . '_time'] = time();

        return $fallback;
    }
}
