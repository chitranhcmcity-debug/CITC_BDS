<?php
/**
 * AuthValidation – Kiểm tra dữ liệu đầu vào cho module xác thực.
 * Trả về mảng lỗi, mảng rỗng = hợp lệ.
 * Không kết nối DB (dependency injection qua constructor).
 */
class AuthValidation
{
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /* ── ĐĂNG KÝ ── */

    public function validateRegister(array $data): array
    {
        $errors = [];

        // Họ tên
        $ten = trim($data['ten'] ?? '');
        if (empty($ten)) {
            $errors['ten'] = 'Vui lòng nhập họ tên.';
        } elseif (mb_strlen($ten) < 2 || mb_strlen($ten) > 100) {
            $errors['ten'] = 'Họ tên phải từ 2 đến 100 ký tự.';
        } elseif (!preg_match('/^[\p{L}\s]+$/u', $ten)) {
            $errors['ten'] = 'Họ tên chỉ chứa chữ cái và khoảng trắng.';
        }

        // Email
        $email = trim($data['email'] ?? '');
        if (empty($email)) {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Địa chỉ email không hợp lệ.';
        } elseif ($this->userRepo->findByEmail($email)) {
            $errors['email'] = 'Email này đã được đăng ký.';
        }

        // Số điện thoại
        $phone = trim($data['dien_thoai'] ?? '');
        if (!empty($phone)) {
            if (!$this->validatePhone($phone)) {
                $errors['dien_thoai'] = 'Số điện thoại không hợp lệ (VD: 0901234567).';
            } elseif ($this->userRepo->findByPhone($phone)) {
                $errors['dien_thoai'] = 'Số điện thoại này đã được đăng ký.';
            }
        }

        // Mật khẩu
        $pass = $data['mat_khau'] ?? '';
        $passErrors = $this->validatePassword($pass);
        if ($passErrors) {
            $errors['mat_khau'] = implode(' ', $passErrors);
        }

        // Nhập lại mật khẩu
        $confirm = $data['mat_khau_xac_nhan'] ?? '';
        if (empty($confirm)) {
            $errors['mat_khau_xac_nhan'] = 'Vui lòng nhập lại mật khẩu.';
        } elseif ($pass !== $confirm) {
            $errors['mat_khau_xac_nhan'] = 'Mật khẩu nhập lại không khớp.';
        }

        // Điều khoản
        if (empty($data['dong_y_dieu_khoan'])) {
            $errors['dong_y_dieu_khoan'] = 'Bạn cần đồng ý với Điều khoản sử dụng.';
        }

        return $errors;
    }

    /* ── ĐĂNG NHẬP ── */

    public function validateLogin(array $data): array
    {
        $errors = [];

        $identifier = trim($data['identifier'] ?? '');
        if (empty($identifier)) {
            $errors['identifier'] = 'Vui lòng nhập email hoặc số điện thoại.';
        } elseif (!filter_var($identifier, FILTER_VALIDATE_EMAIL) && !$this->validatePhone($identifier)) {
            $errors['identifier'] = 'Email hoặc số điện thoại không đúng định dạng.';
        }

        if (empty($data['mat_khau'])) {
            $errors['mat_khau'] = 'Vui lòng nhập mật khẩu.';
        }

        return $errors;
    }

    /* ── QUÊN MẬT KHẨU ── */

    public function validateForgotPassword(array $data): array
    {
        $errors = [];
        $identifier = trim($data['identifier'] ?? '');

        if (empty($identifier)) {
            $errors['identifier'] = 'Vui lòng nhập email hoặc số điện thoại.';
        } elseif (!filter_var($identifier, FILTER_VALIDATE_EMAIL) && !$this->validatePhone($identifier)) {
            $errors['identifier'] = 'Email hoặc số điện thoại không đúng định dạng.';
        }

        return $errors;
    }

    /* ── ĐẶT LẠI MẬT KHẨU ── */

    public function validateResetPassword(array $data): array
    {
        $errors = [];

        if (empty($data['token'])) {
            $errors['token'] = 'Token đặt lại mật khẩu không hợp lệ.';
        }

        $pass = $data['mat_khau'] ?? '';
        $passErrors = $this->validatePassword($pass);
        if ($passErrors) {
            $errors['mat_khau'] = implode(' ', $passErrors);
        }

        $confirm = $data['mat_khau_xac_nhan'] ?? '';
        if (empty($confirm)) {
            $errors['mat_khau_xac_nhan'] = 'Vui lòng nhập lại mật khẩu.';
        } elseif ($pass !== $confirm) {
            $errors['mat_khau_xac_nhan'] = 'Mật khẩu nhập lại không khớp.';
        }

        return $errors;
    }

    /* ── KIỂM TRA MẬT KHẨU MẠNH ── */

    /**
     * Kiểm tra độ mạnh mật khẩu.
     * @return string[] Mảng lỗi (rỗng = hợp lệ)
     */
    public function validatePassword(string $pass): array
    {
        $errors = [];
        if (strlen($pass) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
        }
        return $errors;
    }

    /**
     * Tính điểm độ mạnh mật khẩu (0-5).
     */
    public function passwordStrengthScore(string $pass): int
    {
        $score = 0;
        if (strlen($pass) >= 8)              $score++;
        if (preg_match('/[A-Z]/', $pass))    $score++;
        if (preg_match('/[a-z]/', $pass))    $score++;
        if (preg_match('/[0-9]/', $pass))    $score++;
        if (preg_match('/[^A-Za-z0-9]/', $pass)) $score++;
        return $score;
    }

    /* ── SỐ ĐIỆN THOẠI VIỆT NAM ── */

    public function validatePhone(string $phone): bool
    {
        // VD: 0901234567, +84901234567, 84901234567
        return (bool)preg_match('/^(\+?84|0)[3-9][0-9]{8}$/', preg_replace('/[\s\-\.]/', '', $phone));
    }

    /* ── SANITIZE ── */

    public function sanitizeIdentifier(string $value): string
    {
        $value = trim($value);
        // Nếu là email thì sanitize email, nếu là phone thì chỉ giữ số và +
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return filter_var($value, FILTER_SANITIZE_EMAIL) ?: '';
        }
        return preg_replace('/[^0-9+]/', '', $value);
    }

    public function sanitizeName(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }
}
