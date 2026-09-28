<?php

namespace App\Services;

use App\Models\Database;
use App\Repositories\UserRepository;

/**
 * UserService – Quản lý nghiệp vụ người dùng cho Admin.
 * Hỗ trợ tạo mới, cập nhật thông tin, khóa có thời hạn/vĩnh viễn, mở khóa, xóa mềm.
 * Tuân thủ SOLID, Service Pattern.
 */
class UserService
{
    private UserRepository $userRepo;

    private NotificationService $notifSvc;

    private MailService $mailSvc;

    public function __construct()
    {

        $this->userRepo = new UserRepository;
        $this->notifSvc = new NotificationService;
        $this->mailSvc = new MailService;
    }

    public function adminList(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        return $this->userRepo->adminList($filters, $perPage, $offset);
    }

    public function adminCount(array $filters = []): int
    {
        return $this->userRepo->adminCount($filters);
    }

    public function adminFind(int $id): ?stdClass
    {
        return $this->userRepo->adminFind($id);
    }

    /**
     * Admin tạo người dùng mới.
     */
    public function create(array $input): array
    {
        // 1. Validation dữ liệu đầu vào
        $errors = [];
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $name = trim($input['name'] ?? '');
        $password = trim($input['password'] ?? '');

        if (empty($name)) {
            $errors['name'] = 'Tên không được để trống.';
        }
        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ.';
        }
        if (empty($password) || strlen($password) < 6) {
            $errors['password'] = 'Mật khẩu phải chứa ít nhất 6 ký tự.';
        }

        if (! empty($email) && $this->userRepo->findByEmail($email)) {
            $errors['email'] = 'Email này đã được sử dụng trên hệ thống.';
        }
        if (! empty($phone) && $this->userRepo->findByPhone($phone)) {
            $errors['phone'] = 'Số điện thoại này đã được sử dụng.';
        }

        if (! empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // 2. Hash mật khẩu và lưu
        $hashed = password_hash($password, PASSWORD_ARGON2ID);
        $data = [
            'name' => htmlspecialchars($name),
            'email' => htmlspecialchars($email),
            'phone' => htmlspecialchars($phone),
            'password' => $hashed,
            'role_id' => (int) ($input['role_id'] ?? 3), // Mặc định sales/thành viên thường
            'status' => trim($input['status'] ?? 'hoat_dong'),
            'email_verified' => (int) ($input['email_verified'] ?? 0),
            'phone_verified' => (int) ($input['phone_verified'] ?? 0),
        ];

        $insertedId = $this->userRepo->adminCreate($data);
        if ($insertedId > 0) {
            return ['success' => true, 'id' => $insertedId];
        }

        return ['success' => false, 'errors' => ['general' => 'Không thể lưu thông tin vào cơ sở dữ liệu.']];
    }

    /**
     * Admin cập nhật thông tin hồ sơ của người dùng.
     */
    public function update(int $id, array $input): array
    {
        $user = $this->userRepo->findById($id);
        if (! $user) {
            return ['success' => false, 'errors' => ['general' => 'Người dùng không tồn tại.']];
        }

        $errors = [];
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $name = trim($input['name'] ?? '');

        if (empty($name)) {
            $errors['name'] = 'Tên không được để trống.';
        }
        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ.';
        }

        if (! empty($email) && strtolower($email) !== strtolower($user->email)) {
            if ($this->userRepo->findByEmail($email)) {
                $errors['email'] = 'Email này đã được sử dụng trên hệ thống.';
            }
        }
        if (! empty($phone) && $phone !== $user->dien_thoai) {
            if ($this->userRepo->findByPhone($phone)) {
                $errors['phone'] = 'Số điện thoại này đã được sử dụng.';
            }
        }

        if (! empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $data = [
            'name' => htmlspecialchars($name),
            'email' => htmlspecialchars($email),
            'phone' => htmlspecialchars($phone),
            'role_id' => (int) ($input['role_id'] ?? $user->ma_vai_tro),
            'dob' => trim($input['dob'] ?? $user->ngay_sinh ?? ''),
            'gender' => trim($input['gender'] ?? $user->gioi_tinh ?? 'nam'),
            'address' => htmlspecialchars(trim($input['address'] ?? $user->dia_chi ?? '')),
            'job' => htmlspecialchars(trim($input['job'] ?? $user->nghe_nghiep ?? '')),
            'bio' => htmlspecialchars(trim($input['bio'] ?? $user->mo_ta_ca_nhan ?? '')),
            'region' => htmlspecialchars(trim($input['region'] ?? $user->khu_vuc_hoat_dong ?? '')),
            'status' => trim($input['status'] ?? $user->trang_thai),
            'email_verified' => (int) ($input['email_verified'] ?? $user->da_xac_thuc),
            'phone_verified' => (int) ($input['phone_verified'] ?? $user->phone_verified),
        ];

        if ($this->userRepo->adminUpdateProfile($id, $data)) {
            return ['success' => true];
        }

        return ['success' => false, 'errors' => ['general' => 'Không thể cập nhật cơ sở dữ liệu.']];
    }

    /**
     * Khóa tài khoản thành viên.
     */
    public function lock(int $id, int $durationDays, string $reason, int $adminId): bool
    {
        $user = $this->userRepo->findById($id);
        if (! $user) {
            return false;
        }

        // durationDays === 0 có nghĩa là khóa vĩnh viễn
        $lockedUntil = ($durationDays === 0)
            ? '2099-12-31 23:59:59'
            : date('Y-m-d H:i:s', strtotime("+{$durationDays} days"));

        $ok = $this->userRepo->adminLock($id, $lockedUntil);
        if ($ok) {
            // Ghi System Admin Log
            SystemLogger::admin(
                'lock',
                'nguoi_dung',
                'nguoi_dung',
                $id,
                "Khóa tài khoản người dùng #{$id} - {$user->ten}. Thời hạn: ".($durationDays === 0 ? 'Vĩnh viễn' : "{$durationDays} ngày").". Lý do: {$reason}",
                (array) $user,
                ['locked_until' => $lockedUntil, 'reason' => $reason],
                $adminId
            );

            // Gửi thông báo & email cảnh báo
            $title = 'Tài khoản của bạn đã bị khóa';
            $content = 'Tài khoản của bạn đã bị khóa bởi quản trị viên hệ thống. Thời gian khóa: '.($durationDays === 0 ? 'Vĩnh viễn' : "{$durationDays} ngày").'. Lý do: '.$reason;

            $this->notifSvc->send($id, $title, $content, 'bao_mat');
            if (! empty($user->email)) {
                $this->mailSvc->sendNotificationEmail($user->email, $title, $content);
            }
        }

        return $ok;
    }

    /**
     * Mở khóa tài khoản thành viên.
     */
    public function unlock(int $id, int $adminId): bool
    {
        $user = $this->userRepo->findById($id);
        if (! $user) {
            return false;
        }

        $ok = $this->userRepo->adminUnlock($id);
        if ($ok) {
            // Ghi System Admin Log
            SystemLogger::admin(
                'unlock',
                'nguoi_dung',
                'nguoi_dung',
                $id,
                "Mở khóa tài khoản người dùng #{$id} - {$user->ten}",
                (array) $user,
                [],
                $adminId
            );

            // Gửi thông báo & email
            $title = 'Tài khoản của bạn đã được mở khóa';
            $content = 'Tài khoản của bạn đã được mở khóa bởi quản trị viên hệ thống. Hiện tại bạn có thể đăng nhập bình thường.';

            $this->notifSvc->send($id, $title, $content, 'bao_mat');
            if (! empty($user->email)) {
                $this->mailSvc->sendNotificationEmail($user->email, $title, $content);
            }
        }

        return $ok;
    }

    /**
     * Xóa người dùng (cứng - xóa vĩnh viễn khỏi DB).
     * Dọn dẹp dữ liệu liên kết trước khi xóa.
     */
    public function delete(int $id, int $adminId): bool
    {
        $user = $this->userRepo->findById($id);
        if (! $user) {
            return false;
        }

        // Không cho phép tự xóa chính mình
        if ($id === $adminId) {
            return false;
        }

        // Dọn dẹp dữ liệu liên kết KHÔNG có ON DELETE CASCADE/SET NULL
        // Mỗi query bọc try-catch vì bảng có thể chưa tồn tại
        $db = new Database;

        // bai_viet.ma_nguoi_dung - KHÔNG có ON DELETE
        try {
            $db->query('DELETE nc FROM binh_luan_tin_tuc nc INNER JOIN bai_viet bv ON bv.id = nc.bai_viet_id WHERE bv.ma_nguoi_dung = :uid');
            $db->bind(':uid', $id, PDO::PARAM_INT);
            $db->execute();
        } catch (Throwable) {
        }

        try {
            $db->query('DELETE nl FROM luot_thich_tin_tuc nl INNER JOIN bai_viet bv ON bv.id = nl.bai_viet_id WHERE bv.ma_nguoi_dung = :uid');
            $db->bind(':uid', $id, PDO::PARAM_INT);
            $db->execute();
        } catch (Throwable) {
        }

        try {
            $db->query('DELETE FROM bai_viet WHERE ma_nguoi_dung = :uid');
            $db->bind(':uid', $id, PDO::PARAM_INT);
            $db->execute();
        } catch (Throwable) {
        }

        // news_comments
        try {
            $db->query('DELETE FROM binh_luan_tin_tuc WHERE nguoi_dung_id = :uid');
            $db->bind(':uid', $id, PDO::PARAM_INT);
            $db->execute();
        } catch (Throwable) {
        }

        // news_likes
        try {
            $db->query('DELETE FROM luot_thich_tin_tuc WHERE nguoi_dung_id = :uid');
            $db->bind(':uid', $id, PDO::PARAM_INT);
            $db->execute();
        } catch (Throwable) {
        }

        // follows
        try {
            $db->query('DELETE FROM theo_doi WHERE follower_id = :uid OR following_id = :uid2');
            $db->bind(':uid', $id, PDO::PARAM_INT);
            $db->bind(':uid2', $id, PDO::PARAM_INT);
            $db->execute();
        } catch (Throwable) {
        }

        // user_tokens
        try {
            $db->query('DELETE FROM ma_truy_cap_nguoi_dung WHERE user_id = :uid');
            $db->bind(':uid', $id, PDO::PARAM_INT);
            $db->execute();
        } catch (Throwable) {
        }

        // otp_codes
        try {
            $db->query('DELETE FROM ma_otp WHERE user_id = :uid');
            $db->bind(':uid', $id, PDO::PARAM_INT);
            $db->execute();
        } catch (Throwable) {
        }

        // notifications
        try {
            $db->query('DELETE FROM thong_bao_nguoi_dung WHERE user_id = :uid');
            $db->bind(':uid', $id, PDO::PARAM_INT);
            $db->execute();
        } catch (Throwable) {
        }

        // chat_messages
        try {
            $db->query('DELETE FROM tin_nhan WHERE sender_id = :uid');
            $db->bind(':uid', $id, PDO::PARAM_INT);
            $db->execute();
        } catch (Throwable) {
        }

        // live_chat_conversations
        try {
            $db->query('UPDATE hoi_thoai_truc_tuyen SET ma_nguoi_dung = NULL WHERE ma_nguoi_dung = :uid');
            $db->bind(':uid', $id, PDO::PARAM_INT);
            $db->execute();
        } catch (Throwable) {
        }

        // Xóa file ảnh đại diện trên đĩa
        if (! empty($user->anh_dai_dien) && $user->anh_dai_dien !== 'default.png') {
            $avatarPath = APP_ROOT.'/public/uploads/avatars/'.$user->anh_dai_dien;
            if (is_file($avatarPath)) {
                @unlink($avatarPath);
            }
        }

        // Xóa vĩnh viễn user (các bảng có CASCADE/SET NULL sẽ tự xử lý)
        $ok = $this->userRepo->adminHardDelete($id);
        if ($ok) {
            // Ghi System Admin Log
            SystemLogger::admin(
                'hard_delete',
                'nguoi_dung',
                'nguoi_dung',
                $id,
                "Xóa vĩnh viễn tài khoản người dùng #{$id} - {$user->ten} ({$user->email})",
                (array) $user,
                [],
                $adminId
            );
        }

        return $ok;
    }

    /**
     * Trích xuất tổng kết Analytics cho Admin Dashboard.
     */
    public function adminStats(): array
    {
        return $this->userRepo->adminCountStats();
    }
}
