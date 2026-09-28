<?php
/**
 * Controller LienHe - Xử lý form liên hệ / tư vấn của khách hàng.
 * URL: /lien-he  (alias: /contact)
 */
class LienHeController extends Controller
{
    /** @var KhachHang Model khách hàng tiềm năng (leads) */
    private KhachHang $leadModel;

    /**
     * Khởi tạo KhachHang Model.
     */
    public function __construct()
    {
        $this->leadModel = $this->model('KhachHang');
    }

    // ==========================================
    // TRANG LIÊN HỆ
    // ==========================================

    /**
     * Hiển thị form liên hệ (GET) và xử lý gửi yêu cầu tư vấn (POST).
     * URL: GET/POST /lien-he
     */
    public function index(): void
    {
        $data = [
            'title'     => SITE_NAME . ' - Liên Hệ',
            'name'      => '',
            'email'     => '',
            'phone'     => '',
            'message'   => '',
            'name_err'  => '',
            'phone_err' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view('lien-he/index', $data);
            return;
        }

        // Lọc dữ liệu POST (FILTER_DEFAULT an toàn trên PHP 8.1+)
        Csrf::verify();

        $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT) ?: [];

        $data['name']       = trim($_POST['name']    ?? '');
        $data['email']      = trim($_POST['email']   ?? '');
        $data['phone']      = trim($_POST['phone']   ?? '');
        $data['message']    = trim($_POST['message'] ?? '');
        $data['project_id'] = $_POST['project_id'] ?? null;

        // Validate các trường bắt buộc
        if (empty($data['name'])) {
            $data['name_err'] = 'Vui lòng nhập họ tên.';
        }
        if (empty($data['phone'])) {
            $data['phone_err'] = 'Vui lòng nhập số điện thoại.';
        }

        if (!empty($data['name_err']) || !empty($data['phone_err'])) {
            $this->view('lien-he/index', $data);
            return;
        }

        if (!$this->leadModel->create($data)) {
            die('Đã xảy ra lỗi khi gửi yêu cầu. Vui lòng thử lại.');
        }

        if (!empty($data['project_id'])) {
            (new AnalyticsService())->record((int)$data['project_id'], 'contact');
        }

        Session::flash('contact_success', 'Yêu cầu liên hệ của bạn đã được gửi thành công! Chúng tôi sẽ liên hệ lại sớm nhất.');

        // Chuyển hướng an toàn: chỉ dùng phần path, không cho redirect tới domain ngoài
        $redirectUrl = trim($_POST['redirect_url'] ?? 'contact');
        if (str_starts_with($redirectUrl, 'http')) {
            $parsed      = parse_url($redirectUrl);
            $redirectUrl = ltrim(($parsed['path'] ?? '') . (isset($parsed['query']) ? '?' . $parsed['query'] : ''), '/');
        }
        $this->redirect($redirectUrl);
    }
}
