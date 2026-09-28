<?php
/**
 * Controller AdminBangGia - Quản lý cài đặt bảng giá và khuyến mãi.
 * URL: /admin/bang-gia
 *
 * Quyền truy cập: Chỉ Admin (role_id = 1)
 */
class AdminBangGiaController extends Controller
{
    /**
     * Kiểm tra quyền Admin trước khi cho phép truy cập.
     */
    public function __construct()
    {
        Auth::requireRole(1);
    }

    // ==========================================
    // HIỂN THỊ BẢNG GIÁ
    // ==========================================

    /**
     * Hiển thị trang cài đặt bảng giá VIP và khuyến mãi nạp tiền.
     * URL: GET /admin/bang-gia
     */
    public function index(): void
    {
        $data = [
            'title'    => 'Cài Đặt Báo Giá & Khuyến Mãi',
            'settings' => CaiDat::getAll(),
        ];
        $this->view('admin/bang-gia/index', $data);
    }

    // ==========================================
    // LƯU BẢNG GIÁ
    // ==========================================

    /**
     * Lưu cài đặt bảng giá mới.
     * URL: POST /admin/bang-gia/save
     */
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/bang-gia');
        }

        // Lọc dữ liệu POST an toàn (FILTER_DEFAULT là lựa chọn đúng trên PHP 8.1+)
        Csrf::verify();

        $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT) ?: [];

        $data = [
            'price_vip5'  => trim($_POST['price_vip5']  ?? ''),
            'price_vip4'  => trim($_POST['price_vip4']  ?? ''),
            'price_vip3'  => trim($_POST['price_vip3']  ?? ''),
            'price_vip2'  => trim($_POST['price_vip2']  ?? ''),
            'price_vip1'  => trim($_POST['price_vip1']  ?? ''),

            'price_up60'   => trim($_POST['price_up60']   ?? ''),
            'price_up150'  => trim($_POST['price_up150']  ?? ''),
            'price_up500'  => trim($_POST['price_up500']  ?? ''),
            'price_up750'  => trim($_POST['price_up750']  ?? ''),
            'price_up1500' => trim($_POST['price_up1500'] ?? ''),

            'bonus_tier1' => trim($_POST['bonus_tier1'] ?? ''),
            'bonus_tier2' => trim($_POST['bonus_tier2'] ?? ''),
            'bonus_tier3' => trim($_POST['bonus_tier3'] ?? ''),
            'bonus_tier4' => trim($_POST['bonus_tier4'] ?? ''),
            'bonus_tier5' => trim($_POST['bonus_tier5'] ?? ''),
            'bonus_tier6' => trim($_POST['bonus_tier6'] ?? ''),
        ];

        CaiDat::save($data);
        $this->redirect('admin/bang-gia');
    }
}
