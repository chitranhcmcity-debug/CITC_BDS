<?php
/**
 * WalletService – Nghiệp vụ cộng/trừ số dư ví điện tử của người dùng dành cho Admin.
 * Tự động ghi nhật ký giao dịch và gửi thông báo, email cảnh báo.
 * Tuân thủ SOLID, Service Pattern.
 */
class WalletService
{
    private WalletRepository $walletRepo;
    private UserRepository $userRepo;
    private NotificationService $notifSvc;
    private MailService $mailSvc;

    public function __construct()
    {
        require_once APP_ROOT . '/app/repositories/WalletRepository.php';
        require_once APP_ROOT . '/app/repositories/UserRepository.php';
        require_once APP_ROOT . '/app/services/DichVuThongBao.php';
        require_once APP_ROOT . '/app/services/DichVuThuDienTu.php';

        $this->walletRepo = new WalletRepository();
        $this->userRepo = new UserRepository();
        $this->notifSvc = new NotificationService();
        $this->mailSvc = new MailService();
    }

    /**
     * Admin cộng tiền vào tài khoản thành viên.
     */
    public function addMoney(int $userId, int $amount, string $reason, int $adminId): bool
    {
        $user = $this->userRepo->findById($userId);
        if (!$user) return false;

        $this->walletRepo->beginTransaction();
        try {
            // 1. Cộng số dư nguoi_dung.so_du
            if (!$this->walletRepo->addBalance($userId, $amount)) {
                throw new RuntimeException("Lỗi cộng số dư ví.");
            }

            // 2. Ghi nhận giao dịch nạp tiền
            $depositData = [
                'ma_nguoi_dung' => $userId,
                'so_tien'       => $amount,
                'tong_cong'     => $amount,
                'phuong_thuc'   => 'tien_mat',
                'ma_giao_dich'  => 'ADM_ADD_' . time() . '_' . rand(100, 999),
                'trang_thai'    => 'da_duyet',
                'ghi_chu'       => $reason
            ];
            if (!$this->walletRepo->createDepositRequest($depositData)) {
                throw new RuntimeException("Lỗi ghi nhật ký nạp tiền.");
            }

            // 3. Ghi System Admin Log
            SystemLogger::admin(
                'add_money', 
                'nguoi_dung', 
                'nguoi_dung', 
                $userId, 
                "Cộng số dư ví cho người dùng: #{$userId} - {$user->ten}. Số tiền: " . number_format($amount) . "đ. Lý do: {$reason}", 
                [], 
                ['amount' => $amount, 'reason' => $reason], 
                $adminId
            );

            $this->walletRepo->commit();

            // 4. Gửi thông báo SSE & Email
            $title = "Ví của bạn đã được cộng tiền";
            $content = "Tài khoản của bạn đã được quản trị viên cộng thêm " . number_format($amount) . "đ vào ví điện tử. Lý do: " . $reason;
            $this->notifSvc->send($userId, $title, $content, 'thanh_toan');

            if (!empty($user->email)) {
                $this->mailSvc->sendNotificationEmail($user->email, $title, $content);
            }

            return true;
        } catch (Throwable $e) {
            $this->walletRepo->rollBack();
            error_log('[ADD MONEY ERROR] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Admin trừ tiền khỏi tài khoản thành viên.
     */
    public function subMoney(int $userId, int $amount, string $reason, int $adminId): bool
    {
        $user = $this->userRepo->findById($userId);
        if (!$user) return false;

        $this->walletRepo->beginTransaction();
        try {
            // 1. Trừ số dư nguoi_dung.so_du (nếu đủ số dư)
            if (!$this->walletRepo->subtractBalance($userId, $amount)) {
                throw new RuntimeException("Số dư không đủ để thực hiện giao dịch trừ.");
            }

            // 2. Ghi nhật ký chi tiêu
            $expenseData = [
                'ma_nguoi_dung' => $userId,
                'ma_du_an'      => null,
                'loai'          => 'rut_thuong',
                'mo_ta'         => $reason,
                'so_tien'       => $amount
            ];
            if (!$this->walletRepo->createExpenseLog($expenseData)) {
                throw new RuntimeException("Lỗi ghi nhật ký chi tiêu.");
            }

            // 3. Ghi System Admin Log
            SystemLogger::admin(
                'sub_money', 
                'nguoi_dung', 
                'nguoi_dung', 
                $userId, 
                "Trừ số dư ví cho người dùng: #{$userId} - {$user->ten}. Số tiền: " . number_format($amount) . "đ. Lý do: {$reason}", 
                [], 
                ['amount' => $amount, 'reason' => $reason], 
                $adminId
            );

            $this->walletRepo->commit();

            // 4. Gửi thông báo SSE & Email
            $title = "Ví của bạn đã bị trừ tiền";
            $content = "Tài khoản của bạn đã bị quản trị viên trừ " . number_format($amount) . "đ khỏi ví điện tử. Lý do: " . $reason;
            $this->notifSvc->send($userId, $title, $content, 'thanh_toan');

            if (!empty($user->email)) {
                $this->mailSvc->sendNotificationEmail($user->email, $title, $content);
            }

            return true;
        } catch (Throwable $e) {
            $this->walletRepo->rollBack();
            error_log('[SUB MONEY ERROR] ' . $e->getMessage());
            return false;
        }
    }
}
