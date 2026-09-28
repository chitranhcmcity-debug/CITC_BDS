<?php
/**
 * MailService – Quản lý việc gửi email xác thực tài khoản và gửi mã OTP qua SMTP.
 * Tuân thủ SOLID, Service Pattern.
 */
class MailService
{
    public function sendContactConfirmation(array $contact): bool
    {
        $email = trim((string)($contact['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
        $name = htmlspecialchars((string)($contact['fullname'] ?? 'Quý khách'));
        $subject = '[' . SITE_NAME . '] Đã tiếp nhận yêu cầu tư vấn';
        $content = '<p>Xin chào <strong>'.$name.'</strong>,</p><p>Chúng tôi đã tiếp nhận yêu cầu của bạn và sẽ liên hệ trong thời gian sớm nhất.</p>';
        return Email::send($email, $subject, $content, ['isHtml' => true]);
    }

    public function sendContactAdminNotification(array $contact, string $adminEmail, string $adminName): bool
    {
        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) return false;
        $customer = htmlspecialchars((string)($contact['fullname'] ?? 'Khách hàng'));
        $subject = '[' . SITE_NAME . '] Yêu cầu tư vấn mới được phân công';
        $content = '<p>Xin chào <strong>'.htmlspecialchars($adminName).'</strong>,</p><p>Bạn vừa được phân công hỗ trợ khách hàng <strong>'.$customer.'</strong>.</p>';
        return Email::send($adminEmail, $subject, $content, ['isHtml' => true]);
    }
    /**
     * Gửi email xác thực liên kết tài khoản.
     */
    public function sendVerificationEmail(string $email, string $token): bool
    {
        $verifyUrl = URL_ROOT . '/verify-email?email=' . urlencode($email) . '&token=' . $token;
        
        $subject = 'Xác thực tài khoản của bạn – TimNhaDat.site';
        $message = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;">
                <h2 style="color: #0ea5e9; text-align: center;">Xác Thực Email Của Bạn</h2>
                <p>Xin chào,</p>
                <p>Cảm ơn bạn đã sử dụng nền tảng TimNhaDat.site. Vui lòng bấm vào nút bên dưới để hoàn tất việc xác thực địa chỉ email cho tài khoản của bạn:</p>
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $verifyUrl . '" style="background: #0ea5e9; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 9999px; font-weight: bold; display: inline-block;">Xác thực tài khoản</a>
                </div>
                <p style="font-size: 12px; color: #64748b;">Nếu nút bên trên không hoạt động, bạn có thể sao chép liên kết dưới đây và dán vào trình duyệt:</p>
                <p style="font-size: 12px; color: #0ea5e9; word-break: break-all;">' . $verifyUrl . '</p>
                <p>Liên kết này sẽ có hiệu lực trong vòng 24 giờ.</p>
                <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                <p style="font-size: 11px; color: #94a3b8; text-align: center;">Đây là email tự động từ hệ thống. Vui lòng không phản hồi lại email này.</p>
            </div>
        ';

        require_once APP_ROOT . '/core/Email.php';
        return Email::send($email, $subject, $message, ['isHtml' => true]);
    }

    /**
     * Gửi mã OTP xác nhận tới Email.
     */
    public function sendOTPEmail(string $email, string $otp, string $purpose = 'reset_password'): bool
    {
        $isLoginVerification = $purpose === 'login';
        $subject = $isLoginVerification
            ? 'Mã OTP xác thực email và đăng nhập – TimNhaDat.site'
            : 'Mã OTP khôi phục mật khẩu – TimNhaDat.site';
        $description = $isLoginVerification
            ? 'Bạn đang xác thực địa chỉ email để đăng nhập vào TimNhaDat.site.'
            : 'Bạn đã yêu cầu khôi phục mật khẩu trên TimNhaDat.site.';
        $message = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;">
                <h2 style="color: #0ea5e9; text-align: center;">Mã Xác Thực OTP</h2>
                <p>Xin chào,</p>
                <p>' . $description . ' Dưới đây là mã OTP của bạn:</p>
                <div style="text-align: center; margin: 30px 0;">
                    <span style="font-size: 32px; font-weight: 800; letter-spacing: 6px; color: #0ea5e9; background: #f0f9ff; padding: 10px 20px; border-radius: 8px; border: 1px dashed #0ea5e9;">' . $otp . '</span>
                </div>
                <p>Mã OTP này có hiệu lực trong vòng <strong>5 phút</strong>. Tuyệt đối không chia sẻ mã này cho bất kỳ ai khác.</p>
                <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                <p style="font-size: 11px; color: #94a3b8; text-align: center;">Đây là email tự động từ hệ thống. Vui lòng không phản hồi lại email này.</p>
            </div>
        ';

        require_once APP_ROOT . '/core/Email.php';
        return Email::send($email, $subject, $message, ['isHtml' => true]);
    }

    /**
     * Gửi Email thông báo hoạt động hệ thống.
     */
    public function sendNotificationEmail(string $email, string $title, string $content): bool
    {
        $subject = '[' . SITE_NAME . '] Thông báo mới: ' . $title;
        $message = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;">
                <h3 style="color: #2563eb; margin-top: 0;">' . htmlspecialchars($title) . '</h3>
                <p>Xin chào,</p>
                <p>' . nl2br(htmlspecialchars($content)) . '</p>
                <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                <p style="font-size: 11px; color: #94a3b8; text-align: center;">Đây là email tự động từ trung tâm thông báo TimNhaDat.site. Vui lòng không trả lời.</p>
            </div>
        ';

        require_once APP_ROOT . '/core/Email.php';
        return Email::send($email, $subject, $message, ['isHtml' => true]);
    }

    /**
     * Gửi email xác thực tài khoản sau đăng ký.
     * AuthService gọi method này với (user object, token string).
     */
    public function sendEmailVerification(object $user, string $token): bool
    {
        $email = $user->email ?? '';
        if (empty($email)) return false;
        return $this->sendVerificationEmail($email, $token);
    }

    /**
     * Gửi email thông báo tài khoản bị khóa do đăng nhập sai quá nhiều.
     */
    public function sendAccountLocked(object $user): bool
    {
        $email = $user->email ?? '';
        if (empty($email)) return false;

        $subject = 'Cảnh báo bảo mật – Tài khoản tạm thời bị khóa';
        $name = htmlspecialchars($user->ten ?? 'Người dùng');
        $message = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #fca5a5; border-radius: 8px;">
                <h2 style="color: #dc2626; text-align: center;">⚠️ Tài Khoản Bị Khóa Tạm Thời</h2>
                <p>Xin chào ' . $name . ',</p>
                <p>Chúng tôi phát hiện có nhiều lần đăng nhập thất bại liên tiếp vào tài khoản của bạn. Để bảo vệ an toàn, tài khoản đã bị khóa tạm thời trong <strong>15 phút</strong>.</p>
                <p>Nếu bạn không thực hiện các lần đăng nhập này, vui lòng đổi mật khẩu ngay sau khi tài khoản được mở khóa.</p>
                <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                <p style="font-size: 11px; color: #94a3b8; text-align: center;">Đây là email tự động từ hệ thống. Vui lòng không phản hồi.</p>
            </div>
        ';

        require_once APP_ROOT . '/core/Email.php';
        return Email::send($email, $subject, $message, ['isHtml' => true]);
    }

    /**
     * Gửi email cảnh báo đăng nhập từ thiết bị/IP mới.
     */
    public function sendNewDeviceAlert(object $user, array $loginData): bool
    {
        $email = $user->email ?? '';
        if (empty($email)) return false;

        $subject = 'Cảnh báo bảo mật – Đăng nhập từ thiết bị mới';
        $name = htmlspecialchars($user->ten ?? 'Người dùng');
        $ip = htmlspecialchars($loginData['ip_address'] ?? 'Không xác định');
        $browser = htmlspecialchars($loginData['browser'] ?? 'Không xác định');
        $os = htmlspecialchars($loginData['os'] ?? 'Không xác định');
        $time = date('d/m/Y H:i:s');

        $message = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #fbbf24; border-radius: 8px;">
                <h2 style="color: #d97706; text-align: center;">🔔 Đăng Nhập Từ Thiết Bị Mới</h2>
                <p>Xin chào ' . $name . ',</p>
                <p>Chúng tôi phát hiện tài khoản của bạn vừa đăng nhập từ một thiết bị/IP mới:</p>
                <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                    <tr><td style="padding: 8px; border: 1px solid #e2e8f0; font-weight: bold;">IP:</td><td style="padding: 8px; border: 1px solid #e2e8f0;">' . $ip . '</td></tr>
                    <tr><td style="padding: 8px; border: 1px solid #e2e8f0; font-weight: bold;">Trình duyệt:</td><td style="padding: 8px; border: 1px solid #e2e8f0;">' . $browser . '</td></tr>
                    <tr><td style="padding: 8px; border: 1px solid #e2e8f0; font-weight: bold;">Hệ điều hành:</td><td style="padding: 8px; border: 1px solid #e2e8f0;">' . $os . '</td></tr>
                    <tr><td style="padding: 8px; border: 1px solid #e2e8f0; font-weight: bold;">Thời gian:</td><td style="padding: 8px; border: 1px solid #e2e8f0;">' . $time . '</td></tr>
                </table>
                <p>Nếu đây không phải bạn, hãy đổi mật khẩu ngay và liên hệ hỗ trợ.</p>
                <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                <p style="font-size: 11px; color: #94a3b8; text-align: center;">Đây là email tự động từ hệ thống. Vui lòng không phản hồi.</p>
            </div>
        ';

        require_once APP_ROOT . '/core/Email.php';
        return Email::send($email, $subject, $message, ['isHtml' => true]);
    }
}
