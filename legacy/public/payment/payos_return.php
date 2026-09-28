<?php
/**
 * Điểm nhận hướng sau khi thanh toán thành công hoặc hủy thanh toán tại PayOS
 * Đường dẫn: public/payment/payos_return.php
 */

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

    session_start();

    // 2. Nhận các tham số phản hồi từ URL của PayOS
    $orderCode = isset($_GET['orderCode']) ? (int)$_GET['orderCode'] : 0;
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $isCancel = isset($_GET['cancel']) && $_GET['cancel'] === 'true';

    if ($orderCode <= 0) {
        Session::set('error', 'Giao dịch không hợp lệ hoặc mã đơn hàng sai.');
        header('Location: ' . URL_ROOT . '/vi-dien-tu');
        exit;
    }

    $walletModel = new ViDienTu();
    $yeuCau = $walletModel->layYeuCauTheoId($orderCode);
    $currentUserId = (int)Session::get('user_id');

    if (!$yeuCau || $currentUserId <= 0 || (int)$yeuCau->ma_nguoi_dung !== $currentUserId) {
        Session::set('error', 'Không tìm thấy giao dịch thuộc tài khoản hiện tại.');
        header('Location: ' . URL_ROOT . '/vi-dien-tu');
        exit;
    }

    // Nếu người dùng chủ động bấm Hủy thanh toán tại giao diện PayOS
    if ($isCancel) {
        Session::set('error', 'Giao dịch nạp tiền qua cổng PayOS đã bị hủy bỏ.');
        header('Location: ' . URL_ROOT . '/vi-dien-tu');
        exit;
    }

    // 3. Gọi API kiểm tra trực tiếp trạng thái thanh toán từ PayOS để bảo đảm an toàn
    $payosResponse = paymentPayosGetPayment($orderCode);

    if (isset($payosResponse['data']['status'])
        && $payosResponse['data']['status'] === 'PAID'
        && (int)($payosResponse['data']['orderCode'] ?? 0) === $orderCode
        && (int)($payosResponse['data']['amount'] ?? 0) === (int)$yeuCau->so_tien) {
        // Lấy thông tin yêu cầu nạp tiền từ cơ sở dữ liệu
        if ($yeuCau) {
            // Nếu giao dịch chưa được duyệt (tức là Webhook chưa cập nhật kịp)
            if ($yeuCau->trang_thai === 'cho_duyet') {
                $walletModel->beginTransaction();

                // Cập nhật trạng thái thành 'da_duyet'
                if (!$walletModel->danhDauDaDuyetNeuDangCho((int)$yeuCau->id)) {
                    $walletModel->rollBack();
                    Session::set('success', 'Giao dịch đã được hệ thống xử lý trước đó.');
                    header('Location: ' . URL_ROOT . '/vi-dien-tu');
                    exit;
                }

                // Cộng số dư ví tài khoản (gồm khuyến mãi nếu có)
                if (!$walletModel->congSoDu((int)$yeuCau->ma_nguoi_dung, (int)$yeuCau->tong_cong)) {
                    throw new RuntimeException('Không thể cộng số dư cho người dùng.');
                }

                // Ghi nhật ký giao dịch
                $walletModel->ghiChiTieu([
                    'ma_nguoi_dung' => $yeuCau->ma_nguoi_dung,
                    'loai'          => 'nap_tien',
                    'mo_ta'         => "Nạp tiền tự động qua PayOS (Đồng bộ Return: #{$yeuCau->ma_giao_dich})",
                    'so_tien'       => $yeuCau->tong_cong,
                ]);

                // Tạo thông báo hệ thống
                $userModel = new NguoiDung();
                $userModel->taoThongBao(
                    $yeuCau->ma_nguoi_dung, 
                    'Nạp tiền thành công', 
                    'Tài khoản của bạn đã được cộng ' . number_format($yeuCau->tong_cong) . ' VNĐ thành công qua cổng thanh toán PayOS.'
                );

                $walletModel->commit();
            }

            Session::set('success', 'Nạp tiền thành công! Tài khoản đã được cộng ' . number_format($yeuCau->tong_cong) . ' VNĐ.');
        } else {
            Session::set('error', 'Không tìm thấy thông tin giao dịch tương ứng trên hệ thống.');
        }
    } else {
        Session::set('error', 'Thanh toán không thành công hoặc giao dịch đang chờ xử lý.');
    }

} catch (Exception $e) {
    if (isset($walletModel)) {
        $walletModel->rollBack();
    }
    error_log('PayOS Return Page Error: ' . $e->getMessage());
    SystemLogger::error($e, 'payment_return', 'error');
    SystemLogger::flush();
    Session::set('error', 'Đã xảy ra lỗi khi đồng bộ giao dịch. Vui lòng thử lại sau.');
}

// Chuyển hướng người dùng về lại trang ví tiền
header('Location: ' . URL_ROOT . '/vi-dien-tu');
exit;
