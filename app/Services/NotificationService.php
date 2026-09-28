<?php

namespace App\Services;

use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;

/**
 * NotificationService – Dịch vụ trung tâm điều phối quản lý và đẩy thông báo.
 * Sử dụng Redis/File Cache cho số lượng chưa đọc.
 * Tuân thủ SOLID, Service Pattern.
 */
class NotificationService
{
    private NotificationRepository $repo;

    private RealtimeService $realtime;

    private PushService $push;

    private MailService $mail;

    public function __construct()
    {

        $this->repo = new NotificationRepository;
        $this->realtime = new RealtimeService;
        $this->push = new PushService;
        $this->mail = new MailService;
    }

    public function getRepo(): NotificationRepository
    {
        return $this->repo;
    }

    public function getRealtime(): RealtimeService
    {
        return $this->realtime;
    }

    /**
     * Gửi thông báo đến một người dùng.
     */
    public function send(int $userId, string $title, string $content, string $type = 'he_thong', string $url = '', string $icon = 'fa-bell'): bool
    {
        // 1. Lưu CSDL
        $notifData = [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'url' => $url,
            'icon' => $icon,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $insertedId = $this->repo->insert($notifData);
        if ($insertedId <= 0) {
            return false;
        }

        $notifData['id'] = $insertedId;

        // 2. Xóa Cache số lượng chưa đọc của người dùng
        $this->clearCache($userId);

        // 3. Đẩy Realtime đến Browser qua SSE
        $this->realtime->publish($userId, $notifData);

        // 4. Đẩy ra các kênh ngoài (mô phỏng)
        $this->push->sendFCM($userId, $title, $content, $url);
        $this->push->sendOneSignal($userId, $title, $content, $url);

        // 5. Gửi Email đối với các thông báo hệ thống / bảo mật / giao dịch quan trọng
        if ($type === 'bao_mat' || $type === 'thanh_toan') {
            $userRepo = new UserRepository;
            $user = $userRepo->findById($userId);
            if ($user && ! empty($user->email)) {
                $this->mail->sendNotificationEmail($user->email, $title, $content);
            }
        }

        return true;
    }

    /**
     * Gửi thông báo hệ thống hàng loạt tới nhiều người dùng theo vai trò.
     */
    public function sendBroadcast(?int $roleId, string $title, string $content, string $url = '', string $icon = 'fa-bullhorn'): int
    {
        $userRepo = new UserRepository;

        if ($roleId !== null) {
            // Lấy tất cả người dùng thuộc vai trò này
            $users = $userRepo->findByRole($roleId);
        } else {
            // Lấy tất cả người dùng hoạt động trong hệ thống
            $users = $userRepo->getAllActive();
        }

        $sentCount = 0;
        foreach ($users as $user) {
            if ($this->send((int) $user->id, $title, $content, 'he_thong', $url, $icon)) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * Lấy số lượng thông báo chưa đọc của người dùng (tích hợp Cache 5 phút).
     */
    public function getUnreadCount(int $userId): int
    {
        $cacheKey = 'unread_count_cache_'.$userId;
        $cached = $this->getCache($cacheKey);
        if ($cached !== null) {
            return (int) $cached;
        }

        $count = $this->repo->countUnread($userId);
        $this->setCache($cacheKey, $count);

        return $count;
    }

    /**
     * Đánh dấu đã đọc.
     */
    public function markRead(int $id, int $userId): bool
    {
        $res = $this->repo->updateReadStatus($id, $userId, true);
        if ($res) {
            $this->clearCache($userId);
        }

        return $res;
    }

    /**
     * Đánh dấu tất cả đã đọc.
     */
    public function markAllRead(int $userId): bool
    {
        $res = $this->repo->markAllRead($userId);
        if ($res) {
            $this->clearCache($userId);
        }

        return $res;
    }

    /**
     * Xóa thông báo cụ thể.
     */
    public function delete(int $id, int $userId): bool
    {
        $res = $this->repo->delete($id, $userId);
        if ($res) {
            $this->clearCache($userId);
        }

        return $res;
    }

    /**
     * Xóa toàn bộ thông báo.
     */
    public function clear(int $userId): bool
    {
        $res = $this->repo->clearAll($userId);
        if ($res) {
            $this->clearCache($userId);
        }

        return $res;
    }

    // ==========================================
    // CACHE (File-based làm mặc định, 5 phút TTL)
    // ==========================================

    private function getCache(string $key): mixed
    {
        $file = sys_get_temp_dir().'/bds_notif_cache_'.md5($key).'.json';
        if (! file_exists($file)) {
            return null;
        }
        if ((time() - filemtime($file)) > 300) {
            @unlink($file);

            return null;
        }
        $raw = @file_get_contents($file);
        if (! $raw) {
            return null;
        }

        return json_decode($raw, true);
    }

    private function setCache(string $key, mixed $value): void
    {
        $file = sys_get_temp_dir().'/bds_notif_cache_'.md5($key).'.json';
        @file_put_contents($file, json_encode($value, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    private function clearCache(int $userId): void
    {
        $cacheKey = 'unread_count_cache_'.$userId;
        $file = sys_get_temp_dir().'/bds_notif_cache_'.md5($cacheKey).'.json';
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}
