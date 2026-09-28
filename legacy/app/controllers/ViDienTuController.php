<?php
/**
 * Controller ViDienTu – Quản lý ví điện tử, nạp tiền PayOS, mua gói UP, nâng VIP và giới thiệu bạn bè.
 * URL: /vi-dien-tu (hoặc /wallet)
 */
class ViDienTuController extends Controller
{
    private WalletRepository $walletRepo;
    private DuAn $projectModel;

    public function __construct()
    {
        // BẮT BUỘC ĐĂNG NHẬP
        if (!Session::get('user_id')) {
            Session::flash('login_required', 'Vui lòng đăng nhập để truy cập Ví điện tử.');
            $this->redirect('nguoi-dung/dang-nhap');
            return;
        }

        require_once APP_ROOT . '/app/repositories/WalletRepository.php';
        $this->walletRepo = new WalletRepository();
        $this->projectModel = $this->model('DuAn');
    }

    /**
     * Giao diện chính của Ví.
     * URL: GET /vi-dien-tu
     */
    public function index(): void
    {
        $userId = (int)Session::get('user_id');
        $userModel = $this->model('NguoiDung');
        $tongLuotXem = $this->projectModel->tongLuotXemTheoUser($userId);

        // Lấy thống kê giảm giá theo lượt xem tích lũy
        [$giamGia, $mucTiep, $giamTiep] = $this->tinhThongTinGiamGia($tongLuotXem);

        // Lấy thông tin giới thiệu và lịch sử
        $refStats = $this->walletRepo->getReferralStats($userId);
        $history = $this->walletRepo->getHistory($userId, 10, 0);

        $data = [
            'title'        => 'Ví Điện Tử & Ưu Đãi - ' . SITE_NAME,
            'user'         => $userModel->layTheoId($userId),
            'balance'      => $this->walletRepo->getBalance($userId),
            'history'      => $history,
            'totalViews'   => $tongLuotXem,
            'discount'     => $giamGia,
            'nextGoal'     => $mucTiep,
            'nextDiscount' => $giamTiep,
            'refStats'     => $refStats,
            'upPackages'   => (new PricingService())->upPackages(),
            'bonusTiers'   => (new PricingService())->bonusTiers(),
        ];

        $this->view('vi-dien-tu/index', $data);
    }

    /**
     * Nạp tiền (GET hiển thị form, POST xử lý chuyển đến cổng PayOS).
     * URL: GET/POST /vi-dien-tu/deposit
     */
    public function deposit(): void
    {
        $userId = (int)Session::get('user_id');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $data = [
                'title' => 'Nạp Tiền Vào Ví - ' . SITE_NAME,
                'balance' => $this->walletRepo->getBalance($userId),
                'bonusTiers' => (new PricingService())->bonusTiers()
            ];
            $this->view('vi-dien-tu/deposit', $data);
            return;
        }

        Csrf::verify();

        $soTien = (int)($_POST['amount'] ?? 0);
        if ($soTien < 50000) {
            Session::flash('wallet_error', 'Số tiền nạp tối thiểu là 50,000 VNĐ');
            $this->redirect('vi-dien-tu/deposit');
            return;
        }

        $maGiaoDich = 'NAP' . $userId . time();
        $phanTramKhuyenMai = $this->tinhKhuyenMaiNap($soTien);
        $tongCong = $soTien + (int)($soTien * $phanTramKhuyenMai / 100);

        // Tạo yêu cầu nạp tiền trong DB để lấy ID làm orderCode cho PayOS
        $insertId = $this->walletRepo->createDepositRequest([
            'ma_nguoi_dung' => $userId,
            'so_tien'       => $soTien,
            'tong_cong'     => $tongCong,
            'phuong_thuc'   => 'payos',
            'ma_giao_dich'  => $maGiaoDich,
            'ghi_chu'       => 'Nạp tiền qua PayOS (KM ' . $phanTramKhuyenMai . '%)',
            'trang_thai'    => 'cho_duyet'
        ]);

        if ($insertId) {
            require_once APP_ROOT . '/config/payment.php';

            $orderData = [
                'orderCode'   => $insertId,
                'amount'      => $soTien,
                'description' => 'Nap tien CITC ' . $insertId,
                'cancelUrl'   => URL_ROOT . '/public/payment/payos_return.php?orderCode=' . $insertId . '&cancel=true',
                'returnUrl'   => URL_ROOT . '/public/payment/payos_return.php?orderCode=' . $insertId
            ];

            try {
                $payosResult = paymentPayosCreatePayment($orderData);
            } catch (Exception $exception) {
                $this->walletRepo->updateDepositStatus($insertId, 'tu_choi');
                error_log('[PayOS create payment error] ' . $exception->getMessage());
                Session::flash('wallet_error', 'Không thể kết nối với cổng thanh toán PayOS. Vui lòng thử lại sau.');
                $this->redirect('vi-dien-tu/deposit');
                return;
            }

            if (isset($payosResult['error']) && $payosResult['error'] == 0 && isset($payosResult['data']['checkoutUrl'])) {
                header('Location: ' . $payosResult['data']['checkoutUrl']);
                exit;
            } else {
                $this->walletRepo->updateDepositStatus($insertId, 'tu_choi');
                Session::flash('wallet_error', 'Không thể khởi tạo cổng thanh toán PayOS: ' . ($payosResult['message'] ?? 'Lỗi không xác định'));
                $this->redirect('vi-dien-tu/deposit');
                return;
            }
        } else {
            Session::flash('wallet_error', 'Không thể lưu yêu cầu nạp tiền vào hệ thống.');
            $this->redirect('vi-dien-tu/deposit');
            return;
        }
    }

    /**
     * Mua gói lượt UP tin.
     * URL: POST /vi-dien-tu/buyUpPackage
     */
    public function buyUpPackage(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('vi-dien-tu');
            return;
        }

        Csrf::verify();

        $goiId = (int)($_POST['package_id'] ?? 0);
        $pricing = new PricingService();
        $upPackages = $pricing->upPackages();

        if (!isset($upPackages[$goiId])) {
            Session::flash('wallet_error', 'Gói lượt UP tin không hợp lệ.');
            $this->redirect('vi-dien-tu/package');
            return;
        }

        $price = $upPackages[$goiId];
        $tokens = $goiId;
        $userId = (int)Session::get('user_id');

        try {
            $this->walletRepo->beginTransaction();

            // 1. Trừ tiền tài khoản (chống Race Condition)
            if (!$this->walletRepo->subtractBalance($userId, $price)) {
                $this->walletRepo->rollBack();
                Session::flash('wallet_error', 'Số dư tài khoản không đủ để mua gói này. Vui lòng nạp thêm!');
                $this->redirect('vi-dien-tu/deposit');
                return;
            }

            // 2. Cộng lượt UP
            $this->walletRepo->addUserUpTurns($userId, $tokens);

            // 3. Ghi log giao dịch
            $this->walletRepo->createExpenseLog([
                'ma_nguoi_dung' => $userId,
                'loai'          => 'mua_up',
                'mo_ta'         => "Mua gói {$tokens} lượt UP tin đăng",
                'so_tien'       => $price
            ]);

            // 4. Tạo thông báo
            $userModel = $this->model('NguoiDung');
            $userModel->taoThongBao($userId, 'Mua gói UP thành công', "Tài khoản của bạn đã mua thành công gói {$tokens} lượt UP tin với số tiền " . number_format($price) . " VNĐ.");

            $this->walletRepo->commit();
            Session::flash('wallet_success', "Mua gói UP tin thành công! Bạn nhận được {$tokens} lượt UP.");
        } catch (Exception $e) {
            $this->walletRepo->rollBack();
            error_log('GiaoDich Error buyUpPackage: ' . $e->getMessage());
            Session::flash('wallet_error', 'Lỗi giao dịch: ' . $e->getMessage());
        }

        $this->redirect('vi-dien-tu');
    }

    /**
     * Lịch sử giao dịch (bao gồm cả nạp tiền và chi tiêu) có phân trang.
     * URL: GET /vi-dien-tu/history
     */
    public function history(): void
    {
        $userId = (int)Session::get('user_id');

        // Phân trang
        $limit = 15;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $historyList = $this->walletRepo->getHistory($userId, $limit, $offset);
        $totalItems = $this->walletRepo->countHistory($userId);
        $totalPages = ceil($totalItems / $limit);

        $data = [
            'title'       => 'Lịch Sử Giao Dịch - ' . SITE_NAME,
            'history'     => $historyList,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'totalItems'  => $totalItems
        ];

        $this->view('vi-dien-tu/history', $data);
    }

    /**
     * Giao diện hiển thị chi tiết hóa đơn (in/lưu PDF).
     * URL: GET /vi-dien-tu/invoice/{id}
     */
    public function invoice($id = null): void
    {
        if (!$id || !is_numeric($id)) {
            $this->redirect('vi-dien-tu/history');
            return;
        }

        $expense = $this->walletRepo->getExpenseById((int)$id);
        if (!$expense || (int)$expense->ma_nguoi_dung !== (int)Session::get('user_id')) {
            Session::flash('wallet_error', 'Không tìm thấy hóa đơn hoặc bạn không có quyền xem.');
            $this->redirect('vi-dien-tu/history');
            return;
        }

        $data = [
            'title'   => 'Hóa Đơn Điện Tử #' . $expense->id . ' - ' . SITE_NAME,
            'invoice' => $expense
        ];

        $this->view('vi-dien-tu/invoice', $data);
    }

    /**
     * Xem danh sách gói lượt UP tin.
     * URL: GET /vi-dien-tu/package
     */
    public function package(): void
    {
        $userId = (int)Session::get('user_id');
        $pricing = new PricingService();

        $data = [
            'title'      => 'Gói Lượt UP Tin Đăng - ' . SITE_NAME,
            'balance'    => $this->walletRepo->getBalance($userId),
            'upPackages' => $pricing->upPackages()
        ];

        $this->view('vi-dien-tu/package', $data);
    }

    /**
     * Hệ thống giới thiệu bạn bè nhận hoa hồng & rút tiền thưởng.
     * URL: GET /vi-dien-tu/reward
     */
    public function reward(): void
    {
        $userId = (int)Session::get('user_id');
        $refStats = $this->walletRepo->getReferralStats($userId);
        $rewardList = $this->walletRepo->getRewardHistory($userId);

        $data = [
            'title'    => 'Giới Thiệu Nhận Thưởng - ' . SITE_NAME,
            'balance'  => $this->walletRepo->getBalance($userId),
            'refStats' => $refStats,
            'rewards'  => $rewardList
        ];

        $this->view('vi-dien-tu/reward', $data);
    }

    /**
     * Rút tiền thưởng giới thiệu về ví chính.
     * URL: POST /vi-dien-tu/withdrawReward
     */
    public function withdrawReward(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('vi-dien-tu/reward');
            return;
        }

        Csrf::verify();

        $userId = (int)Session::get('user_id');
        $refStats = $this->walletRepo->getReferralStats($userId);
        $amount = (int)($refStats['referral_balance'] ?? 0);

        if ($amount < 50000) {
            Session::flash('wallet_error', 'Số dư thưởng giới thiệu tối thiểu phải từ 50,000 VNĐ mới có thể rút.');
            $this->redirect('vi-dien-tu/reward');
            return;
        }

        try {
            $this->walletRepo->beginTransaction();

            // Rút hoa hồng và cộng vào ví chính
            if ($this->walletRepo->withdrawReferralReward($userId, $amount)) {
                
                // Ghi nhận nhật ký ví
                $this->walletRepo->createExpenseLog([
                    'ma_nguoi_dung' => $userId,
                    'loai'          => 'rut_thuong',
                    'mo_ta'         => "Rút " . number_format($amount) . "đ hoa hồng giới thiệu về ví chính",
                    'so_tien'       => -$amount // Lưu dấu âm hoặc dương tùy cấu trúc
                ]);

                // Tạo thông báo
                $userModel = $this->model('NguoiDung');
                $userModel->taoThongBao($userId, 'Rút tiền thưởng thành công', "Đã cộng thành công " . number_format($amount) . " VNĐ từ quỹ hoa hồng vào số dư Ví chính.");

                $this->walletRepo->commit();
                Session::flash('wallet_success', "Rút " . number_format($amount) . " VNĐ tiền thưởng về ví chính thành công!");
            } else {
                $this->walletRepo->rollBack();
                Session::flash('wallet_error', 'Giao dịch rút thưởng thất bại. Vui lòng kiểm tra lại số dư.');
            }
        } catch (Exception $e) {
            $this->walletRepo->rollBack();
            Session::flash('wallet_error', 'Lỗi hệ thống: ' . $e->getMessage());
        }

        $this->redirect('vi-dien-tu/reward');
    }

    /**
     * Áp dụng mã giới thiệu của người khác.
     * URL: POST /vi-dien-tu/applyReferralCode
     */
    public function applyReferralCode(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('vi-dien-tu/reward');
            return;
        }

        Csrf::verify();

        $userId = (int)Session::get('user_id');
        $code = trim($_POST['referral_code'] ?? '');

        if (empty($code)) {
            Session::flash('wallet_error', 'Vui lòng nhập mã giới thiệu.');
            $this->redirect('vi-dien-tu/reward');
            return;
        }

        $ok = $this->walletRepo->addReferralByCode($userId, $code);
        if ($ok) {
            Session::flash('wallet_success', 'Áp dụng mã giới thiệu thành công!');
        } else {
            Session::flash('wallet_error', 'Mã giới thiệu không hợp lệ, đã được sử dụng hoặc là mã của chính bạn.');
        }

        $this->redirect('vi-dien-tu/reward');
    }

    /**
     * Thanh toán nâng cấp/gia hạn gói VIP trực tiếp từ số dư Ví điện tử.
     * URL: POST /vi-dien-tu/payment
     */
    public function payment(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('vi-dien-tu');
            return;
        }

        Csrf::verify();

        $projectId = (int)($_POST['project_id'] ?? 0);
        $vipLevel  = (int)($_POST['vip_level'] ?? 5); // Level mặc định
        $days      = (int)($_POST['days'] ?? 7);

        if ($projectId <= 0 || $days <= 0 || $vipLevel < 1 || $vipLevel > 5) {
            Session::flash('wallet_error', 'Thông tin nâng cấp VIP không hợp lệ.');
            $this->redirect('vi-dien-tu');
            return;
        }

        $userId = (int)Session::get('user_id');
        $pricing = new PricingService();

        // Tính toán số tiền sau chiết khấu theo lượt xem
        $tongLuotXem = $this->projectModel->tongLuotXemTheoUser($userId);
        $discount = $pricing->viewDiscount($tongLuotXem);
        $finalPrice = $pricing->vipPrice($vipLevel, $days, $discount);

        try {
            $this->walletRepo->beginTransaction();

            // 1. Trừ tiền ví chính (chống Race Condition)
            if (!$this->walletRepo->subtractBalance($userId, $finalPrice)) {
                $this->walletRepo->rollBack();
                // Không đủ tiền, chuyển sang nạp tiền
                Session::flash('wallet_error', 'Số dư không đủ (' . number_format($finalPrice) . 'đ). Vui lòng nạp thêm tiền!');
                $this->redirect('vi-dien-tu/deposit?amount=' . $finalPrice);
                return;
            }

            // 2. Cập nhật trạng thái VIP của bài đăng du_an
            $this->db = new Database();
            // Lấy thời gian hết hạn VIP hiện tại nếu có
            $this->db->query("SELECT ngay_het_han_vip FROM du_an WHERE id = :id");
            $this->db->bind(':id', $projectId);
            $currentExpiry = $this->db->singleColumn();
            
            $baseTime = ($currentExpiry && strtotime($currentExpiry) > time()) ? strtotime($currentExpiry) : time();
            $newExpiry = date('Y-m-d H:i:s', strtotime("+{$days} days", $baseTime));

            $this->db->query("
                UPDATE du_an 
                SET goi_vip = :vip_level, 
                    ngay_het_han_vip = :new_expiry, 
                    trang_thai = 'xuat_ban' 
                WHERE id = :id AND ma_nguoi_dung = :uid
            ");
            $this->db->bind(':vip_level',  $vipLevel);
            $this->db->bind(':new_expiry', $newExpiry);
            $this->db->bind(':id',         $projectId);
            $this->db->bind(':uid',        $userId);
            
            if (!$this->db->execute()) {
                $this->walletRepo->rollBack();
                Session::flash('wallet_error', 'Không thể cập nhật thông tin bài đăng.');
                $this->redirect('vi-dien-tu');
                return;
            }

            // 3. Ghi log giao dịch
            $this->walletRepo->createExpenseLog([
                'ma_nguoi_dung' => $userId,
                'ma_du_an'      => $projectId,
                'loai'          => 'mua_vip',
                'mo_ta'         => "Nâng cấp VIP {$vipLevel} cho bài đăng #{$projectId} trong {$days} ngày",
                'so_tien'       => $finalPrice
            ]);

            // 4. Thưởng cho người giới thiệu (nếu có) - Nhận 10% hoa hồng khi người được mời thanh toán dịch vụ!
            $refStats = $this->walletRepo->getReferralStats($userId);
            if (!empty($refStats['referred_by'])) {
                $referrerId = (int)$refStats['referred_by'];
                $rewardAmount = (int)($finalPrice * 10 / 100); // 10% hoa hồng
                if ($rewardAmount > 0) {
                    // Cộng referral_balance cho người mời
                    $this->db->query("UPDATE nguoi_dung SET referral_balance = referral_balance + :reward WHERE id = :referrer_id");
                    $this->db->bind(':reward',      $rewardAmount);
                    $this->db->bind(':referrer_id', $referrerId);
                    $this->db->execute();

                    // Lưu lịch sử thưởng
                    $this->walletRepo->createRewardHistory([
                        'referrer_id' => $referrerId,
                        'referee_id'  => $userId,
                        'amount'      => $rewardAmount
                    ]);

                    // Gửi thông báo cho người mời
                    $userModel = $this->model('NguoiDung');
                    $userModel->taoThongBao($referrerId, 'Nhận hoa hồng giới thiệu', "Bạn nhận được " . number_format($rewardAmount) . " VNĐ hoa hồng do thành viên bạn giới thiệu thanh toán dịch vụ.");
                }
            }

            // 5. Tạo thông báo cho người dùng
            $userModel = $this->model('NguoiDung');
            $userModel->taoThongBao($userId, 'Nâng cấp VIP thành công', "Bài đăng #{$projectId} đã được nâng cấp lên VIP {$vipLevel} đến ngày " . date('d/m/Y H:i', strtotime($newExpiry)) . ".");

            $this->walletRepo->commit();
            Session::flash('wallet_success', "Nâng cấp VIP {$vipLevel} thành công!");
        } catch (Exception $e) {
            $this->walletRepo->rollBack();
            error_log('GiaoDich Error payment VIP: ' . $e->getMessage());
            Session::flash('wallet_error', 'Lỗi xử lý giao dịch nâng cấp VIP: ' . $e->getMessage());
        }

        $this->redirect('vi-dien-tu');
    }

    /**
     * Nhận thưởng chia sẻ lên MXH.
     * URL: POST /vi-dien-tu/rewardShare
     */
    public function rewardShare(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        Csrf::verify();

        $userId   = (int)Session::get('user_id');
        $platform = $_POST['platform'] ?? 'facebook';

        if ($this->walletRepo->getBalance($userId) !== null) {
            // Kiểm tra xem đã nhận thưởng hôm nay chưa
            $db = new Database();
            $db->query("
                SELECT id FROM chi_tieu 
                WHERE ma_nguoi_dung = :uid 
                  AND loai = 'thuong_chia_se' 
                  AND DATE(ngay_tao) = CURDATE()
            ");
            $db->bind(':uid', $userId);
            $hasClaimed = $db->single();

            if ($hasClaimed) {
                echo json_encode(['success' => false, 'message' => 'Bạn đã nhận thưởng chia sẻ hôm nay rồi. Hẹn gặp lại vào ngày mai nhé!']);
                return;
            }

            // Cộng 5,000đ và ghi log
            $this->walletRepo->addBalance($userId, 5000);
            $this->walletRepo->createExpenseLog([
                'ma_nguoi_dung' => $userId,
                'loai'          => 'thuong_chia_se',
                'mo_ta'         => 'Nhận thưởng chia sẻ website lên ' . ucfirst($platform),
                'so_tien'       => 5000
            ]);

            // Tạo thông báo
            $userModel = $this->model('NguoiDung');
            $userModel->taoThongBao($userId, 'Nhận thưởng chia sẻ', 'Chúc mừng bạn nhận được 5,000 VNĐ phần thưởng chia sẻ MXH.');

            echo json_encode(['success' => true, 'message' => 'Đã cộng 5,000 VNĐ vào Ví điện tử của bạn!']);
            return;
        }

        echo json_encode(['success' => false, 'message' => 'Giao dịch không thành công.']);
    }

    /**
     * Helper: Thống kê thông tin ưu đãi giảm giá theo lượt xem bài đăng.
     */
    private function tinhThongTinGiamGia(int $tongLuotXem): array
    {
        if ($tongLuotXem >= 5000) {
            return [15, 0, 0];
        } elseif ($tongLuotXem >= 1000) {
            return [5, 5000, 15];
        }
        return [0, 1000, 5];
    }

    /**
     * Helper: Tính khuyến mãi nạp tiền theo bảng hạn mức.
     */
    private function tinhKhuyenMaiNap(int $soTien): int
    {
        return (new PricingService())->depositBonusPercent($soTien);
    }
}
