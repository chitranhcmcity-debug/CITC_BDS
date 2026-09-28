<?php

namespace App\Services;

/**
 * ContactValidation – Thực hiện xác thực dữ liệu liên hệ gửi lên và kiểm tra reCAPTCHA.
 */
class ContactValidation
{
    /**
     * Xác thực các trường dữ liệu form liên hệ.
     *
     * @return array Mảng chứa thông tin lỗi [trường => thông báo lỗi]
     */
    public function validate(array $data): array
    {
        $errors = [];

        // 1. Xác thực Họ và tên
        $fullname = trim($data['fullname'] ?? '');
        if ($fullname === '') {
            $errors['fullname'] = 'Vui lòng nhập họ và tên.';
        } elseif (mb_strlen($fullname) > 100) {
            $errors['fullname'] = 'Họ và tên không được vượt quá 100 ký tự.';
        }

        // 2. Xác thực Số điện thoại
        $phone = trim($data['phone'] ?? '');
        if ($phone === '') {
            $errors['phone'] = 'Vui lòng nhập số điện thoại.';
        } else {
            // Chuẩn hóa sđt
            $cleanPhone = preg_replace('/[\s.\-]/', '', $phone);
            if (! preg_match('/^(\+?84|0)[3-9][0-9]{8}$/', $cleanPhone)) {
                $errors['phone'] = 'Số điện thoại không đúng định dạng (Ví dụ: 0901234567).';
            }
        }

        // 3. Xác thực Email
        $email = trim($data['email'] ?? '');
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Địa chỉ email không đúng định dạng.';
        }

        // 4. Xác thực Chủ đề
        $subject = trim($data['subject'] ?? '');
        if (mb_strlen($subject) > 255) {
            $errors['subject'] = 'Tiêu đề yêu cầu không được vượt quá 255 ký tự.';
        }

        // 5. Xác thực Loại yêu cầu
        $validTypes = ['mua_nha', 'thue_nha', 'dang_ban', 'dang_cho_thue', 'hop_tac', 'khieu_nai', 'khac'];
        $type = $data['type'] ?? 'khac';
        if (! in_array($type, $validTypes, true)) {
            $errors['type'] = 'Loại nhu cầu liên hệ không hợp lệ.';
        }

        // 6. Xác thực Google reCAPTCHA nếu có cấu hình trong file .env
        $recaptchaSecret = getenv('RECAPTCHA_SECRET_KEY');
        if (! empty($recaptchaSecret)) {
            $captchaResponse = $_POST['g-recaptcha-response'] ?? '';
            if (empty($captchaResponse)) {
                $errors['recaptcha'] = 'Vui lòng xác minh bạn không phải là robot.';
            } else {
                $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
                $verifyResponse = file_get_contents($verifyUrl.'?secret='.$recaptchaSecret.'&response='.$captchaResponse);
                $responseData = json_decode($verifyResponse, true);
                if (empty($responseData['success'])) {
                    $errors['recaptcha'] = 'Xác minh reCAPTCHA thất bại, vui lòng thử lại.';
                }
            }
        }

        return $errors;
    }

    /**
     * Xác thực file đính kèm tải lên.
     *
     * @param  array  $file  Định dạng $_FILES['file']
     * @return string|null Thông báo lỗi nếu có, ngược lại trả về null
     */
    public function validateFile(array $file): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'Quá trình tải file lên gặp lỗi.';
        }

        // Kiểm tra dung lượng (10MB tối đa)
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return 'Dung lượng file vượt quá giới hạn 10MB cho phép.';
        }

        // Kiểm tra định dạng (PDF, DOCX, PNG, JPG, JPEG)
        $fileName = $file['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'docx', 'png', 'jpg', 'jpeg'];

        if (! in_array($ext, $allowedExtensions, true)) {
            return 'Định dạng file không hỗ trợ. Chỉ cho phép các định dạng: PDF, DOCX, PNG, JPG, JPEG.';
        }

        return null;
    }
}
