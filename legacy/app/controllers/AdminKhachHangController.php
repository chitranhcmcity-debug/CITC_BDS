<?php
/**
 * Controller AdminKhachHang - Quản lý liên hệ của khách hàng tiềm năng.
 * URL: /admin/khach-hang
 *
 * Quyen truy cap: Chi Admin (role_id = 1)
 */
class AdminKhachHangController extends Controller
{
    /** @var KhachHang Model quản lý khách hàng tiềm năng (leads) */
    private KhachHang $leadModel;

    /** @var NguoiDung Model người dùng (nhân viên phụ trách) */
    private NguoiDung $userModel;

    public function __construct()
    {
        Auth::requireRole(1);
        $this->leadModel = $this->model('KhachHang');
        $this->userModel = $this->model('NguoiDung');
    }

    /**
     * Hien thi danh sach yeu cau lien he va thong ke.
     * URL: GET /admin/khach-hang
     */
    public function index(): void
    {
        $leads = $this->leadModel->layTatCaLeads();
        $staff = $this->userModel->layTatCa();
        $stats = $this->leadModel->layThongKe();

        $data = [
            'title' => 'Quản Lý Khách Hàng & Liên Hệ - ' . SITE_NAME,
            'leads' => $leads,
            'staff' => $staff,
            'stats' => $stats,
        ];

        $this->view('admin/khach-hang/index', $data);
    }

    /**
     * AJAX Endpoint de cap nhat trang thai, nguoi phu trach, ghi chu, giai quyet va bao cao.
     * URL: POST /admin/khach-hang/update/{id}
     *
     * @param int $id ID khach hang
     */
    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify(false)) {
                echo json_encode(['success' => false, 'message' => 'Yeu cau bao mat khong hop le. Vui long tai lai trang.']);
                return;
            }

            // Loc du lieu
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT) ?: [];

            $trangThai = trim($_POST['trang_thai'] ?? '');
            $nguoiPhuTrach = !empty($_POST['nguoi_phu_trach']) ? (int)$_POST['nguoi_phu_trach'] : null;
            $ghiChu = trim($_POST['ghi_chu'] ?? '');
            $daGiaiQuyet = isset($_POST['da_giai_quyet']) ? 1 : 0;
            $baoCaoGiaiQuyet = trim($_POST['bao_cao_giai_quyet'] ?? '');

            // Valid trang thai enum
            $validStatus = ['moi', 'da_lien_he', 'tiem_nang', 'that_bai', 'thanh_cong'];
            if (!in_array($trangThai, $validStatus)) {
                echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ!']);
                return;
            }

            $updateData = [
                'id' => $id,
                'trang_thai' => $trangThai,
                'nguoi_phu_trach' => $nguoiPhuTrach,
                'ghi_chu' => $ghiChu,
                'da_giai_quyet' => $daGiaiQuyet,
                'bao_cao_giai_quyet' => $baoCaoGiaiQuyet
            ];

            if ($this->leadModel->capNhatLead($updateData)) {
                $updatedLead = $this->leadModel->layTheoId($id);
                echo json_encode([
                    'success' => true,
                    'message' => 'Cập nhật thông tin khách hàng thành công!',
                    'lead' => $updatedLead
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu!']);
            }
            return;
        }

        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ!']);
    }

    /**
     * AJAX Endpoint de bat/tat nhanh trang thai giai quyet.
     * URL: POST /admin/khach-hang/toggle-resolve/{id}
     *
     * @param int $id ID khach hang
     */
    public function toggleResolve(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify(false)) {
                echo json_encode(['success' => false, 'message' => 'Yeu cau bao mat khong hop le. Vui long tai lai trang.']);
                return;
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT) ?: [];
            $daGiaiQuyet = isset($_POST['da_giai_quyet']) ? (int)$_POST['da_giai_quyet'] : 0;

            if ($this->leadModel->capNhatGiaiQuyet($id, $daGiaiQuyet)) {
                $lead = $this->leadModel->layTheoId($id);
                echo json_encode([
                    'success' => true,
                    'message' => $daGiaiQuyet ? 'Đã đánh dấu yêu cầu là Đã giải quyết!' : 'Đã mở lại yêu cầu khách hàng!',
                    'lead' => $lead
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật CSDL!']);
            }
            return;
        }
        echo json_encode(['success' => false, 'message' => 'Phương thức không hỗ trợ!']);
    }

    /**
     * Xoa khach hang khoi danh sach.
     * URL: POST /admin/khach-hang/delete/{id}
     *
     * @param int $id ID khach hang
     */
    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::verify();

            // Lấy dữ liệu khách hàng trước khi xóa để ghi log
            $lead = $this->leadModel->layTheoId($id);

            if ($this->leadModel->xoaLead($id)) {
                // Ghi log admin
                SystemLogger::admin(
                    'delete',
                    'khach_hang',
                    'khach_hang',
                    $id,
                    "Xóa yêu cầu khách hàng: #{$id}" . ($lead ? " - {$lead->ten}" : ''),
                    $lead ? (array)$lead : [],
                    [],
                    (int)Session::get('user_id')
                );

                Session::flash('khach_hang_msg', 'Xóa yêu cầu khách hàng thành công!');
            } else {
                Session::flash('khach_hang_msg', 'Có lỗi xảy ra khi xóa khách hàng!', 'alert alert-danger');
            }
        }
        $this->redirect('admin/khach-hang');
    }
}
