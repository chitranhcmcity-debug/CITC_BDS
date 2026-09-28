<?php
/**
 * Webhook tiếp nhận thông báo thanh toán tự động từ PayOS
 * Đường dẫn: public/payment/payos_webhook.php
 */

header('Content-Type: application/json; charset=utf-8');

try {
    // 1. Nạp cấu hình ứng dụng và đăng ký autoload của hệ thống MVC
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../config/payment.php';

    spl_autoload_register(function (string $className): void {
        $paths = [
            APP_ROOT . '/core/'             . $className . '.php',
            APP_ROOT . '/app/controllers/' . $className . '.php',
            APP_ROOT . '/app/models/'      . $className . '.php',
            APP_ROOT . '/app/services/'    . $className . '.php',
            APP_ROOT . '/app/repositories/'. $className . '.php',
        ];
        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }
    });

    // 2. Đọc payload JSON gửi từ PayOS
    $rawInput = file_get_contents('php://input');
    $payload = json_decode($rawInput, true);

    if (!$payload) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 1, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    // 3. Xác thực chữ ký webhook từ PayOS (Bảo mật tối đa)
    if (!paymentPayosVerifyWebhook($payload)) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 2, 'message' => 'Chữ ký webhook không hợp lệ']);
        exit;
    }

    if (($payload['success'] ?? false) !== true
        || (string)($payload['code'] ?? '') !== '00'
        || (string)($payload['data']['code'] ?? '') !== '00') {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 5, 'message' => 'Giao dịch PayOS không thành công']);
        exit;
    }

    // 4. Lấy dữ liệu giao dịch
    $data = $payload['data'];
    $orderCode = (int)$data['orderCode']; // ID của yêu cầu nạp tiền trong DB của chúng ta
    $amountPaid = (int)$data['amount'];
    
    // Khởi tạo model Ví điện tử
    $walletModel = new ViDienTu();
    
    // Tìm yêu cầu nạp tiền tương ứng
    $yeuCau = $walletModel->layYeuCauTheoId($orderCode);
    if (!$yeuCau) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 3, 'message' => 'Không tìm thấy yêu cầu nạp tiền #' . $orderCode]);
        exit;
    }

    // Nếu giao dịch đã được duyệt trước đó, báo thành công ngay lập tức để tránh xử lý trùng
    if ($yeuCau->trang_thai === 'da_duyet') {
        echo json_encode(['error' => 0, 'message' => 'Giao dịch đã được cập nhật trước đó']);
        exit;
    }

    // So sánh số tiền thực nhận với số tiền yêu cầu trong DB
    if ($amountPaid !== (int)$yeuCau->so_tien) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 4, 'message' => 'Số tiền thanh toán không khớp']);
        exit;
    }

    // 5. Cập nhật trạng thái đơn hàng (nạp tiền) và cộng số dư
    $walletModel->beginTransaction();

    // Cập nhật trạng thái nạp thành 'da_duyet' (Thành công)
    if (!$walletModel->danhDauDaDuyetNeuDangCho((int)$yeuCau->id)) {
        $walletModel->rollBack();
        echo json_encode(['error' => 0, 'message' => 'Giao dịch đã được xử lý trước đó']);
        exit;
    }

    // Cộng số dư vào ví người dùng (Cộng số tiền kèm khuyến mãi nếu có)
    if (!$walletModel->congSoDu((int)$yeuCau->ma_nguoi_dung, (int)$yeuCau->tong_cong)) {
        throw new RuntimeException('Không thể cộng số dư cho người dùng.');
    }

    // Ghi log giao dịch (Cộng số dư tài khoản)
    $walletModel->ghiChiTieu([
        'ma_nguoi_dung' => $yeuCau->ma_nguoi_dung,
        'loai'          => 'nap_tien',
        'mo_ta'         => "Nạp tiền tự động qua PayOS (Mã GD: #{$yeuCau->ma_giao_dich})",
        'so_tien'       => $yeuCau->tong_cong,
    ]);

    // Tạo thông báo trong hệ thống cho người dùng
    $userModel = new NguoiDung();
    $userModel->taoThongBao(
        $yeuCau->ma_nguoi_dung, 
        'Nạp tiền thành công', 
        'Tài khoản của bạn đã được cộng ' . number_format($yeuCau->tong_cong) . ' VNĐ thành công qua cổng thanh toán PayOS.'
    );

    $walletModel->commit();
    SystemLogger::activity(
        'payment_completed', 'payment', 'nap_tien', (int)$yeuCau->id,
        'Thanh toán PayOS thành công',
        ['transaction_code'=>$yeuCau->ma_giao_dich,'amount'=>(int)$yeuCau->so_tien,'credited'=>(int)$yeuCau->tong_cong],
        (int)$yeuCau->ma_nguoi_dung
    );
    SystemLogger::flush();

    // 6. Gửi email xác nhận nạp tiền thành công
    $nguoiDung = $userModel->layTheoId($yeuCau->ma_nguoi_dung);
    if ($nguoiDung && !empty($nguoiDung->email)) {
        try {
            $to = $nguoiDung->email;
            $subject = 'Xác nhận nạp tiền thành công - ' . SITE_NAME;
            $message = "Xin chào {$nguoiDung->ten},\n\n"
                     . "Chúng tôi xác nhận đã nhận được khoản thanh toán nạp tiền của bạn qua cổng PayOS.\n\n"
                     . "Thông tin giao dịch:\n"
                     . "- Mã yêu cầu: #{$yeuCau->id}\n"
                     . "- Số tiền nạp: " . number_format($yeuCau->so_tien) . " đ\n"
                     . "- Số dư được cộng (gồm KM): " . number_format($yeuCau->tong_cong) . " đ\n"
                     . "- Thời gian: " . date('H:i d/m/Y') . "\n\n"
                     . "Số dư mới của bạn đã được cập nhật trên website. Xin chân thành cảm ơn bạn!\n\n"
                     . "Ban quản trị " . SITE_NAME;

            Email::send($to, $subject, $message);
        } catch (Exception $mailEx) {
            // Không chặn tiến trình nếu gửi mail lỗi
            error_log('Webhook Mail Error: ' . $mailEx->getMessage());
        }
    }

    echo json_encode(['error' => 0, 'message' => 'Cập nhật giao dịch thành công']);

} catch (Exception $e) {
    if (isset($walletModel)) {
        $walletModel->rollBack();
    }
    error_log('PayOS Webhook Exception: ' . $e->getMessage());
    SystemLogger::error($e, 'payment_webhook', 'critical');
    SystemLogger::flush();
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 99, 'message' => 'Lỗi hệ thống khi xử lý giao dịch']);
}
