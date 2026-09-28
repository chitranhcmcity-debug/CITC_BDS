<?php
/**
 * Controller AdminWallet – Quản lý CRM ví điện tử, duyệt nạp, hoàn tiền, báo cáo thống kê cho Admin.
 * URL: /admin/wallet/{method}/{id}
 */
class AdminWalletController extends Controller
{
    private WalletRepository $walletRepo;

    public function __construct()
    {
        // Yêu cầu quyền admin (role_id = 1)
        Auth::requireRole(1);

        require_once APP_ROOT . '/app/repositories/WalletRepository.php';
        $this->walletRepo = new WalletRepository();
    }

    /**
     * Dashboard & Danh sách giao dịch.
     * URL: GET /admin/wallet
     */
    public function index(): void
    {
        // Nhận bộ lọc
        $filters = [
            'status' => isset($_GET['status']) ? trim($_GET['status']) : '',
            'type'   => isset($_GET['type']) ? trim($_GET['type']) : '',
            'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
        ];

        // Phân trang
        $limit = 20;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        // Lấy dữ liệu
        $transactions = $this->walletRepo->getAdminTransactions($filters, $limit, $offset);
        $totalItems = $this->walletRepo->countAdminTransactions($filters);
        $totalPages = ceil($totalItems / $limit);

        // Lấy thống kê doanh thu vẽ biểu đồ Chart.js
        $stats = $this->walletRepo->getRevenueStats();

        // Tính tổng doanh thu tích lũy từ nạp tiền
        $db = new Database();
        $db->query("SELECT SUM(so_tien) FROM nap_tien WHERE trang_thai = 'da_duyet'");
        $totalRevenue = (int)$db->singleColumn();

        // Tổng số dư ví đang lưu hành của tất cả user
        $db->query("SELECT SUM(so_du) FROM nguoi_dung");
        $totalUserBalances = (int)$db->singleColumn();

        $data = [
            'title'             => 'Quản Lý Ví & Giao Dịch - Admin ' . SITE_NAME,
            'transactions'      => $transactions,
            'filters'           => $filters,
            'currentPage'       => $page,
            'totalPages'        => $totalPages,
            'totalItems'        => $totalItems,
            'stats'             => $stats,
            'totalRevenue'      => $totalRevenue,
            'totalUserBalances' => $totalUserBalances
        ];

        $this->view('admin/wallet/index', $data);
    }

    /**
     * Phê duyệt yêu cầu nạp tiền thủ công (chuyển khoản ngân hàng).
     * URL: POST /admin/wallet/approve/{id}
     */
    public function approve(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/wallet');
            return;
        }

        Csrf::verify();

        $yeuCau = $this->walletRepo->getDepositRequest($id);
        if (!$yeuCau || $yeuCau->trang_thai !== 'cho_duyet') {
            Session::flash('admin_wallet_error', 'Giao dịch không tồn tại hoặc đã được xử lý.');
            $this->redirect('admin/wallet');
            return;
        }

        try {
            $this->walletRepo->beginTransaction();

            if ($this->walletRepo->markDepositApproved($id)) {
                // Cộng số dư ví (bao gồm khuyến mãi)
                $this->walletRepo->addBalance($yeuCau->ma_nguoi_dung, (int)$yeuCau->tong_cong);

                // Ghi nhật ký chi tiêu dạng nạp tiền
                $this->walletRepo->createExpenseLog([
                    'ma_nguoi_dung' => $yeuCau->ma_nguoi_dung,
                    'loai'          => 'nap_tien',
                    'mo_ta'         => "Nạp tiền thủ công được Admin duyệt (Mã GD: #{$yeuCau->ma_giao_dich})",
                    'so_tien'       => $yeuCau->tong_cong,
                ]);

                // Tạo thông báo
                $userModel = $this->model('NguoiDung');
                $userModel->taoThongBao($yeuCau->ma_nguoi_dung, 'Nạp tiền thành công', 'Yêu cầu nạp tiền #' . $yeuCau->id . ' của bạn đã được duyệt. Số tiền đã cộng vào ví của bạn.');

                $this->walletRepo->commit();
                Session::flash('admin_wallet_success', 'Đã duyệt yêu cầu nạp tiền #' . $id . ' thành công!');
            } else {
                $this->walletRepo->rollBack();
                Session::flash('admin_wallet_error', 'Duyệt giao dịch thất bại.');
            }
        } catch (Exception $e) {
            $this->walletRepo->rollBack();
            error_log('[Admin Wallet approve error] ' . $e->getMessage());
            Session::flash('admin_wallet_error', 'Lỗi hệ thống: ' . $e->getMessage());
        }

        $this->redirect('admin/wallet');
    }

    /**
     * Từ chối yêu cầu nạp tiền.
     * URL: POST /admin/wallet/reject/{id}
     */
    public function reject(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/wallet');
            return;
        }

        Csrf::verify();

        $yeuCau = $this->walletRepo->getDepositRequest($id);
        if (!$yeuCau || $yeuCau->trang_thai !== 'cho_duyet') {
            Session::flash('admin_wallet_error', 'Giao dịch không tồn tại hoặc đã được xử lý.');
            $this->redirect('admin/wallet');
            return;
        }

        if ($this->walletRepo->updateDepositStatus($id, 'tu_choi')) {
            // Gửi thông báo
            $userModel = $this->model('NguoiDung');
            $userModel->taoThongBao($yeuCau->ma_nguoi_dung, 'Yêu cầu nạp tiền bị từ chối', 'Yêu cầu nạp tiền #' . $yeuCau->id . ' của bạn đã bị từ chối bởi quản trị viên.');
            
            Session::flash('admin_wallet_success', 'Đã từ chối giao dịch #' . $id . '.');
        } else {
            Session::flash('admin_wallet_error', 'Không thể từ chối giao dịch.');
        }

        $this->redirect('admin/wallet');
    }

    /**
     * Hoàn tiền giao dịch chi tiêu (chỉ áp dụng với mua_up hoặc mua_vip).
     * URL: POST /admin/wallet/refund/{id}
     */
    public function refund(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/wallet');
            return;
        }

        Csrf::verify();

        $expense = $this->walletRepo->getExpenseById($id);
        if (!$expense) {
            Session::flash('admin_wallet_error', 'Không tìm thấy thông tin giao dịch.');
            $this->redirect('admin/wallet');
            return;
        }

        // Chỉ cho phép hoàn tiền với mua VIP hoặc mua UP
        if ($expense->loai !== 'mua_vip' && $expense->loai !== 'mua_up') {
            Session::flash('admin_wallet_error', 'Không hỗ trợ hoàn tiền cho loại giao dịch này.');
            $this->redirect('admin/wallet');
            return;
        }

        try {
            $this->walletRepo->beginTransaction();

            // Cộng lại tiền vào ví người dùng
            $this->walletRepo->addBalance($expense->ma_nguoi_dung, (int)abs($expense->so_tien));

            // Ghi nhận dòng hoàn tiền dạng chi tiêu âm
            $this->walletRepo->createExpenseLog([
                'ma_nguoi_dung' => $expense->ma_nguoi_dung,
                'loai'          => 'hoan_tien',
                'mo_ta'         => "Hoàn trả tiền cho giao dịch #{$expense->id} (" . htmlspecialchars($expense->mo_ta) . ")",
                'so_tien'       => -(int)abs($expense->so_tien) // Giá trị âm thể hiện việc cộng lại/hoặc phân loại đặc thù
            ]);

            // Gửi thông báo cho người dùng
            $userModel = $this->model('NguoiDung');
            $userModel->taoThongBao($expense->ma_nguoi_dung, 'Được hoàn tiền giao dịch', 'Bạn đã được hoàn trả ' . number_format(abs($expense->so_tien)) . ' VNĐ vào tài khoản cho giao dịch #' . $expense->id . '.');

            $this->walletRepo->commit();
            Session::flash('admin_wallet_success', 'Hoàn tiền giao dịch #' . $id . ' thành công!');
        } catch (Exception $e) {
            $this->walletRepo->rollBack();
            error_log('[Admin Wallet refund error] ' . $e->getMessage());
            Session::flash('admin_wallet_error', 'Lỗi hoàn tiền: ' . $e->getMessage());
        }

        $this->redirect('admin/wallet');
    }

    /**
     * Xuất Excel (CSV) lịch sử giao dịch.
     * URL: GET /admin/wallet/export
     */
    public function export(): void
    {
        $filters = [
            'status' => isset($_GET['status']) ? trim($_GET['status']) : '',
            'type'   => isset($_GET['type']) ? trim($_GET['type']) : '',
            'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
        ];

        // Lấy toàn bộ giao dịch không giới hạn
        $transactions = $this->walletRepo->getAdminTransactions($filters, 5000, 0);

        // Xuất file CSV chất lượng cao
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=BaoCaoGiaoDich_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');
        // Ghi BOM để Excel hiển thị đúng tiếng Việt UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header columns
        fputcsv($output, ['STT', 'Mã Giao Dịch', 'Thời Gian', 'Khách Hàng', 'Email', 'Loại Giao Dịch', 'Nội Dung', 'Số Tiền (VND)', 'Trạng Thái']);

        $i = 1;
        foreach ($transactions as $t) {
            $typeText = match($t->type) {
                'nap_tien' => 'Nạp tiền',
                'mua_up' => 'Mua lượt UP',
                'mua_vip' => 'Mua gói VIP',
                'thuong_chia_se' => 'Thưởng chia sẻ',
                'rut_thuong' => 'Rút hoa hồng',
                default => $t->type
            };

            $statusText = match($t->status) {
                'moi', 'cho_duyet' => 'Đang chờ duyệt',
                'da_duyet', 'thanh_cong' => 'Thành công',
                'tu_choi', 'that_bai' => 'Thất bại/Từ chối',
                'hoan_tien' => 'Đã hoàn tiền',
                'huy' => 'Đã hủy',
                default => $t->status
            };

            fputcsv($output, [
                $i++,
                $t->id,
                date('d/m/Y H:i', strtotime($t->ngay_tao)),
                $t->ten,
                $t->email,
                $typeText,
                $t->method_or_desc,
                number_format($t->amount),
                $statusText
            ]);
        }

        fclose($output);
        exit;
    }
}
