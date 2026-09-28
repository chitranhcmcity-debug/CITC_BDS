<?php
/**
 * ApprovalService – Nghiệp vụ phê duyệt và từ chối tin đăng bất động sản.
 * Tự động gửi thông báo thời gian thực và email cho người dùng khi được duyệt/từ chối.
 * Tuân thủ SOLID, Service Pattern.
 */
class ApprovalService
{
    private PropertyRepository $repo;
    private NotificationService $notifSvc;
    private MailService $mailSvc;

    public function __construct()
    {
        require_once APP_ROOT . '/app/repositories/PropertyRepository.php';
        require_once APP_ROOT . '/app/services/DichVuThongBao.php';
        require_once APP_ROOT . '/app/services/DichVuThuDienTu.php';

        $this->repo = new PropertyRepository();
        $this->notifSvc = new NotificationService();
        $this->mailSvc = new MailService();
    }

    /**
     * Phê duyệt tin đăng.
     */
    public function approve(int $id, int $adminId): bool
    {
        $post = $this->repo->adminFind($id);
        if (!$post || $post->trang_thai !== 'cho_duyet') return false;

        $ok = $this->repo->updateApprovalStatus($id, 'xuat_ban');
        if ($ok) {
            // Ghi System Log
            SystemLogger::admin(
                'approve', 
                'du_an', 
                'du_an', 
                $id, 
                "Phê duyệt tin đăng: #{$id} - {$post->tieu_de}", 
                (array)$post, 
                ['trang_thai' => 'xuat_ban'], 
                $adminId
            );

            // Gửi Notification và Email cho người đăng
            if ((int)$post->ma_nguoi_dung > 0) {
                $title = "Tin đăng của bạn đã được duyệt";
                $content = "Tin đăng '{$post->tieu_de}' đã được phê duyệt và hiển thị trên hệ thống TimNhaDat.site.";
                
                // Gửi Notification hệ thống
                $this->notifSvc->send((int)$post->ma_nguoi_dung, $title, $content, 'he_thong', '/du-an/detail/' . $post->duong_dan);
                
                // Gửi Email thông báo
                if (!empty($post->seller_email)) {
                    $this->mailSvc->sendNotificationEmail($post->seller_email, $title, $content);
                }
            }
        }

        return $ok;
    }

    /**
     * Từ chối tin đăng.
     */
    public function reject(int $id, int $adminId, string $reason): bool
    {
        $post = $this->repo->adminFind($id);
        if (!$post || $post->trang_thai !== 'cho_duyet' || trim($reason) === '') return false;

        $ok = $this->repo->updateApprovalStatus($id, 'tu_choi', $reason);
        if ($ok) {
            // Ghi System Log
            SystemLogger::admin(
                'reject', 
                'du_an', 
                'du_an', 
                $id, 
                "Từ chối tin đăng: #{$id} - {$post->tieu_de}. Lý do: {$reason}", 
                (array)$post, 
                ['trang_thai' => 'tu_choi', 'ly_do_tu_choi' => $reason], 
                $adminId
            );

            // Gửi Notification và Email cho người đăng
            if ((int)$post->ma_nguoi_dung > 0) {
                $title = "Tin đăng của bạn bị từ chối duyệt";
                $content = "Tin đăng '{$post->tieu_de}' bị từ chối duyệt. Lý do: {$reason}. Vui lòng chỉnh sửa lại tin đăng để gửi duyệt lại.";
                
                // Gửi Notification hệ thống
                $this->notifSvc->send((int)$post->ma_nguoi_dung, $title, $content, 'he_thong', '/nguoi-dung/myPost');
                
                // Gửi Email thông báo
                if (!empty($post->seller_email)) {
                    $this->mailSvc->sendNotificationEmail($post->seller_email, $title, $content);
                }
            }
        }

        return $ok;
    }
}
