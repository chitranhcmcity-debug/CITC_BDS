<?php
/**
 * Controller AdminDeposit - Quản lý yêu cầu nạp tiền (Admin duyệt).
 * URL: /admin/nap-tien/{method}/{id}
 *
 * Quyen truy cap: Chi Admin (role_id = 1)
 */
class AdminNapTienController extends Controller
{
    /** @var ViDienTu Model vi dien tu */
    private ViDienTu $walletModel;

    /**
     * Kiem tra quyen Admin va khoi tao ViDienTu Model.
     */
    public function __construct()
    {
        Auth::requireRole(1);
        $this->walletModel = $this->model('ViDienTu');
    }

    // ==========================================
    // DANH SÁCH YÊU CẦU NẠP TIỀN
    // ==========================================

    /**
     * Hien thi danh sach cac yeu cau nap tien dang cho duyet.
     * URL: GET /admin/nap-tien
     */
    public function index(): void
    {
        $data = [
            'title'    => 'Duyệt Nạp Tiền - ' . SITE_NAME,
            'deposits' => $this->walletModel->layChoNap(),
        ];

        $this->view('admin/nap-tien/index', $data);
    }

    // ==========================================
    // DUYỆT YÊU CẦU NẠP TIỀN
    // ==========================================

    /**
     * Admin duyet yeu cau nap tien: cong tien vao vi va gui thong bao.
     * Chi xu ly neu yeu cau dang o trang thai 'cho_duyet'.
     *
     * URL: POST /admin/nap-tien/approve/{id}
     *
     * @param int $id ID yeu cau nap tien
     */
    public function approve(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/nap-tien');
        }

        // BẢO MẬT: Kiểm tra CSRF
        Csrf::verify();

        $yeuCau = $this->walletModel->layYeuCauTheoId($id);

        if ($yeuCau && $yeuCau->trang_thai === 'cho_duyet') {
            try {
                $this->walletModel->beginTransaction();

                if ($this->walletModel->danhDauDaDuyetNeuDangCho($id)) {
                    // Cong so tien (bao gom khuyen mai) vao vi nguoi dung
                    if (!$this->walletModel->congSoDu($yeuCau->ma_nguoi_dung, (int)$yeuCau->tong_cong)) {
                        $this->walletModel->rollBack();
                        throw new RuntimeException('Không thể cộng số dư người dùng.');
                    }

                    // Gui thong bao cho nguoi dung
                    $userModel = $this->model('NguoiDung');
                    $userModel->taoThongBao(
                        $yeuCau->ma_nguoi_dung,
                        'Nạp tiền thành công',
                        'Bạn đã nạp thành công ' . number_format($yeuCau->tong_cong) . ' VNĐ vào tài khoản.'
                    );

                    $this->walletModel->commit();
                    Session::set('success', 'Đã duyệt và cộng ' . number_format($yeuCau->tong_cong) . ' VNĐ vào ví người dùng!');
                } else {
                    $this->walletModel->rollBack();
                    Session::set('error', 'Lỗi khi cập nhật trạng thái.');
                }
            } catch (Exception $e) {
                $this->walletModel->rollBack();
                error_log('GiaoDich Error approve deposit: ' . $e->getMessage());
                SystemLogger::error($e, 'payment', 'error', ['deposit_id'=>$id]);
                Session::set('error', 'Có lỗi hệ thống trong quá trình giao dịch.');
            }
        } else {
            Session::set('error', 'Giao dịch không tồn tại hoặc đã được xử lý trước đó.');
        }

        $this->redirect('admin/nap-tien');
    }

    // ==========================================
    // TỪ CHỐI YÊU CẦU NẠP TIỀN
    // ==========================================

    /**
     * Admin tu choi yeu cau nap tien va gui thong bao ly do cho nguoi dung.
     * URL: POST /admin/nap-tien/reject/{id}
     *
     * @param int $id ID yeu cau nap tien
     */
    public function reject(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/nap-tien');
        }

        // BẢO MẬT: Kiểm tra CSRF
        Csrf::verify();

        $yeuCau = $this->walletModel->layYeuCauTheoId($id);

        if ($yeuCau && $yeuCau->trang_thai === 'cho_duyet') {
            $this->walletModel->capNhatTrangThaiNap($id, 'tu_choi');

            // Gui thong bao cho nguoi dung biet yeu cau bi tu choi
            $userModel = $this->model('NguoiDung');
            $userModel->taoThongBao(
                $yeuCau->ma_nguoi_dung,
                'Nạp tiền thất bại',
                'Giao dịch nạp ' . number_format($yeuCau->tong_cong) . ' VNĐ của bạn đã bị từ chối. Vui lòng liên hệ bộ phận hỗ trợ.'
            );

            Session::set('success', 'Đã từ chối yêu cầu nạp tiền.');
        } else {
            Session::set('error', 'Giao dịch không tồn tại hoặc đã được xử lý trước đó.');
        }

        $this->redirect('admin/nap-tien');
    }
}
