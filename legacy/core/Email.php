<?php
/**
 * Lớp Email Helper - Đóng gói thư viện PHPMailer gửi email qua SMTP hoặc fallback mail().
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

class Email
{
    /**
     * Gửi email sử dụng cấu hình SMTP hoặc tự động chuyển hướng sang hàm mail() mặc định.
     *
     * @param string $to Địa chỉ email nhận
     * @param string $subject Tiêu đề email
     * @param string $message Nội dung thư
     * @param array $options Các tùy chọn bổ sung:
     *                      - 'isHtml' (bool): Thư gửi định dạng HTML hay Plain Text. Mặc định là false.
     *                      - 'fromEmail' (string): Email người gửi tùy chỉnh.
     *                      - 'fromName' (string): Tên người gửi tùy chỉnh.
     *                      - 'replyTo' (string): Email nhận phản hồi.
     * @return bool Trả về true nếu gửi thư thành công, ngược lại trả về false.
     */
    public static function send(string $to, string $subject, string $message, array $options = []): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Lấy thông số SMTP từ Cài đặt hệ thống
            $smtpHost   = CaiDat::get('smtp_host');
            $smtpPort   = CaiDat::get('smtp_port');
            $smtpAuth   = CaiDat::get('smtp_auth');
            $smtpSecure = CaiDat::get('smtp_secure');
            $smtpUser   = CaiDat::get('smtp_user');
            $smtpPass   = CaiDat::get('smtp_pass');

            // Nếu chưa cấu hình SMTP Host, chuyển sang gửi bằng hàm mail() mặc định của PHP
            if (empty($smtpHost)) {
                error_log("Email Warning: SMTP Host chưa cấu hình. Hệ thống chuyển đổi sang hàm mail() mặc định.");
                
                $fromEmail = $options['fromEmail'] ?? CaiDat::get('smtp_from_email');
                if (empty($fromEmail)) {
                    $fromEmail = CaiDat::get('email') ?: 'no-reply@localhost';
                }
                
                $fromName = $options['fromName'] ?? CaiDat::get('smtp_from_name');
                if (empty($fromName)) {
                    $fromName = CaiDat::get('site_name') ?: 'Website';
                }
                
                $isHtml = $options['isHtml'] ?? false;
                $contentType = $isHtml ? 'text/html' : 'text/plain';
                
                // Mã hóa tiêu đề UTF-8
                $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
                
                $headers = "MIME-Version: 1.0\r\n"
                         . "Content-Type: {$contentType}; charset=utf-8\r\n"
                         . "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>\r\n"
                         . "Reply-To: {$fromEmail}\r\n"
                         . "X-Mailer: PHP/" . phpversion();
                         
                return @mail($to, $encodedSubject, $message, $headers);
            }

            // Thiết lập gửi qua SMTP
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = ($smtpAuth === '1' || $smtpAuth === 1 || $smtpAuth === true || $smtpAuth === 'true');
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;
            
            // Thiết lập phương thức bảo mật mã hóa
            if ($smtpSecure === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($smtpSecure === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }
            
            $mail->Port = !empty($smtpPort) ? (int)$smtpPort : 587;

            // Người gửi và người nhận
            $fromEmail = $options['fromEmail'] ?? CaiDat::get('smtp_from_email');
            if (empty($fromEmail)) {
                $fromEmail = CaiDat::get('email') ?: $smtpUser;
            }
            
            $fromName = $options['fromName'] ?? CaiDat::get('smtp_from_name');
            if (empty($fromName)) {
                $fromName = CaiDat::get('site_name') ?: 'Website';
            }

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            
            if (!empty($options['replyTo'])) {
                $mail->addReplyTo($options['replyTo']);
            } else {
                $mail->addReplyTo($fromEmail, $fromName);
            }

            // Thiết lập định dạng nội dung thư
            $isHtml = $options['isHtml'] ?? false;
            $mail->isHTML($isHtml);
            $mail->Subject = $subject;
            $mail->Body    = $message;

            // Nội dung dự phòng nếu trình duyệt email không hỗ trợ hiển thị HTML
            if ($isHtml) {
                $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<p>'], ["\n", "\n", "\n\n"], $message));
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}
