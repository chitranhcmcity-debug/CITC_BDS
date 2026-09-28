<?php
/**
 * GuestMiddleware – Chỉ cho phép người CHƯA đăng nhập.
 * Dùng cho trang đăng nhập, đăng ký.
 */
class GuestMiddleware
{
    /**
     * Nếu đã đăng nhập → redirect dashboard phù hợp với role.
     */
    public static function handle(): void
    {
        if (!Session::get('user_id')) return;

        $roleId = (int)Session::get('user_role_id');
        $url    = $roleId === 1 ? URL_ROOT . '/admin/dashboard' : URL_ROOT . '/nguoi-dung/dashboard';
        header('Location: ' . $url);
        exit;
    }
}
