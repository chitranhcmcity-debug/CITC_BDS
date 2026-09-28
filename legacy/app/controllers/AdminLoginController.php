<?php
/**
 * Controller AdminLogin - Xử lý đăng nhập và đăng xuất Admin.
 * URL: /admin/login
 */
class AdminLoginController extends Controller
{
    /** @var NguoiDung Model người dùng */
    private NguoiDung $userModel;

    /**
     * Khởi tạo model NguoiDung.
     */
    public function __construct()
    {
        $this->userModel = $this->model('NguoiDung');
    }

    // ==========================================
    // ĐĂNG NHẬP
    // ==========================================

    /**
     * Hiển thị form đăng nhập và xử lý đăng nhập.
     * Nếu đã đăng nhập, chuyển thẳng vào trang tổng quan.
     * URL: GET/POST /admin/login
     */
    public function index(): void
    {
        $this->redirect('nguoi-dung/dang-nhap');
    }

    // ==========================================
    // TẠO PHIÊN LÀM VIỆC
    // ==========================================

    /**
     * Tạo phiên làm việc sau khi đăng nhập thành công.
     * Su dung Auth::dangNhap() de dam bao:
     *   - Session Regeneration (chong Session Fixation)
     *   - Security Logging (ghi log su kien dang nhap)
     * Sau đó chuyển hướng theo vai trò người dùng.
     *
     * @param object $nguoiDung Đối tượng người dùng từ CSDL
     */
    private function taoPhienLamViec(object $nguoiDung): void
    {
        // Tạo session an toàn qua Auth.
        Auth::dangNhap($nguoiDung);

        // Admin => vao trang Admin Dashboard
        // Thành viên => vào trang tổng quan người dùng
        if ((int)$nguoiDung->ma_vai_tro === 1) {
            $this->redirect('admin/dashboard');
        } else {
            $this->redirect('nguoi-dung/dashboard');
        }
    }

    // ==========================================
    // ĐĂNG XUẤT
    // ==========================================

    /**
     * Xóa toàn bộ session và chuyển về trang đăng nhập.
     * URL: /admin/login/logout hoặc /nguoi-dung/logout
     */
    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }
        Csrf::verify();
        $userId = (int)(Session::get('user_id') ?: 0);
        if ($userId > 0) {
            (new AuthService())->logout($userId);
        }
        Auth::dangXuat();
        $this->redirect('');
    }
}
