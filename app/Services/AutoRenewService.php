<?php

namespace App\Services;

use App\Models\DuAn;
use App\Models\NguoiDung;
use App\Models\ViDienTu;

/**
 * Dịch vụ xử lý tự động gia hạn VIP chạy ngầm (thụ động).
 */
class AutoRenewService
{
    public static function run(): void
    {

        $projectModel = new DuAn;
        $walletModel = new ViDienTu;
        $userModel = new NguoiDung;

        // Lấy danh sách tin đăng VIP đã hết hạn và bật tự động gia hạn
        $canGiaHan = $projectModel->layTinVipHetHanCanGiaHan();
        if (empty($canGiaHan)) {
            return;
        }

        $pricing = new PricingService;
        $soNgay = 7; // Số ngày gia hạn mặc định cho auto-renew

        foreach ($canGiaHan as $tinDang) {
            $projectId = (int) $tinDang->id;
            $userId = (int) $tinDang->ma_nguoi_dung;
            $vipLevel = (int) $tinDang->goi_vip;

            if ($vipLevel <= 0) {
                continue;
            }

            // Tính toán giá tiền và ưu đãi
            $tongLuotXem = $projectModel->tongLuotXemTheoUser($userId);
            $giamGia = $pricing->viewDiscount($tongLuotXem);
            $giaCuoi = $pricing->vipPrice($vipLevel, $soNgay, $giamGia);

            $nguoiDung = $userModel->layTheoId($userId);

            if (! $nguoiDung) {
                continue;
            }

            if ($giaCuoi > 0 && (int) $nguoiDung->so_du < $giaCuoi) {
                // Tắt tự động gia hạn và gửi thông báo nếu số dư không đủ
                try {
                    $walletModel->beginTransaction();
                    $projectModel->setAutoRenewVip($projectId, 0);
                    $userModel->taoThongBao(
                        $userId,
                        'Gia hạn VIP tự động thất bại',
                        "Hệ thống không thể tự động gia hạn tin đăng '".htmlspecialchars($tinDang->tieu_de)."' của bạn do số dư tài khoản không đủ. Tính năng tự động gia hạn đã được tắt."
                    );
                    $walletModel->commit();
                } catch (Exception $e) {
                    $walletModel->rollBack();
                    error_log('Auto Renew Failed Setup Error: '.$e->getMessage());
                    SystemLogger::error($e, 'auto_renew', 'error', ['post_id' => $tinDang->id ?? null]);
                }

                continue;
            }

            // Thực hiện gia hạn bằng Transaction để đảm bảo an toàn tài chính
            try {
                $walletModel->beginTransaction();

                // 1. Trừ tiền tài khoản
                if ($giaCuoi > 0 && ! $walletModel->truSoDu($userId, $giaCuoi)) {
                    $walletModel->rollBack();
                    // Tắt tự động gia hạn vì lỗi trừ tiền
                    $projectModel->setAutoRenewVip($projectId, 0);

                    continue;
                }

                // 2. Tính hạn gia hạn mới
                // Vì tin đăng này đã hết hạn, gia hạn sẽ được tính từ thời điểm hiện tại
                $newExpiry = date('Y-m-d H:i:s', strtotime("+{$soNgay} days"));
                $projectModel->giaHanVip($projectId, $newExpiry);

                // 3. Ghi nhật ký chi tiêu
                $walletModel->ghiChiTieu([
                    'ma_nguoi_dung' => $userId,
                    'ma_du_an' => $projectId,
                    'loai' => 'gia_han',
                    'mo_ta' => "Gia hạn tự động VIP {$vipLevel} - {$soNgay} ngày (giảm {$giamGia}%)",
                    'so_tien' => $giaCuoi,
                ]);

                // 4. Tạo thông báo thành công cho người dùng
                $userModel->taoThongBao(
                    $userId,
                    'Gia hạn VIP tự động thành công',
                    "Tin đăng '".htmlspecialchars($tinDang->tieu_de)."' đã được tự động gia hạn VIP {$vipLevel} thêm {$soNgay} ngày. Hạn mới đến: ".date('d/m/Y H:i', strtotime($newExpiry))
                );

                $walletModel->commit();
            } catch (Exception $e) {
                $walletModel->rollBack();
                error_log('Auto Renew Service Transaction Error: '.$e->getMessage());
                SystemLogger::error($e, 'auto_renew', 'error', ['post_id' => $tinDang->id ?? null]);
            }
        }
    }
}
