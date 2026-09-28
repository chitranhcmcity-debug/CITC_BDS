<?php
/**
 * Lớp Session - Quản lý phiên làm việc (session) người dùng.
 * Tất cả phương thức đều là static để gọi mà không cần khởi tạo đối tượng.
 *
 * Vi du su dung:
 *   Session::set('user_id', 5);
 *   $id = Session::get('user_id');
 *   Session::flash('success', 'Lưu thành công!');
 */
class Session
{
    // ==========================================
    // KHỞI TẠO
    // ==========================================

    /**
     * Khoi dong session neu chua bat dau.
     * Can goi o dau vao cua ung dung (index.php).
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // ==========================================
    // ĐỌC / GHI SESSION
    // ==========================================

    /**
     * Ghi gia tri vao session.
     *
     * @param string $key   Khoa session
     * @param mixed  $value Gia tri can luu
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Doc gia tri tu session.
     *
     * @param  string      $key Khoa session can lay
     * @return mixed|false      Gia tri neu ton tai, false neu khong co
     */
    public static function get(string $key): mixed
    {
        return $_SESSION[$key] ?? false;
    }

    /**
     * Xoa mot gia tri khoi session.
     *
     * @param string $key Khoa can xoa
     */
    public static function delete(string $key): void
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Pha huy toan bo session (dang xuat nguoi dung).
     */
    public static function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    // ==========================================
    // FLASH MESSAGE (Thông báo 1 lần)
    // ==========================================

    /**
     * Xu ly flash message - thong bao hien thi 1 lan roi tu dong xoa.
     *
     * - Ghi thong bao: truyen du ca $name, $message, $class
     * - Hien thi va xoa: chi truyen $name (khong truyen $message)
     *
     * Vi du ghi:   Session::flash('success', 'Lưu thành công!', 'alert alert-success');
     * Vi du hien:  Session::flash('success');
     *
     * @param string $name    Ten khoa session chua thong bao
     * @param string $message Noi dung thong bao (de trong khi hien thi)
     * @param string $class   CSS class cho div thong bao
     */
    public static function flash(
        string $name    = '',
        string $message = '',
        string $class   = 'alert alert-success'
    ): void {
        if (empty($name)) {
            return;
        }

        if (!empty($message) && empty($_SESSION[$name])) {
            // CHE DO GHI: Luu thong bao vao session
            $_SESSION[$name]             = $message;
            $_SESSION[$name . '_class']  = $class;
        } elseif (empty($message) && !empty($_SESSION[$name])) {
            // CHE DO HIEN THI: Xuat HTML va xoa khoi session
            $cssClass = $_SESSION[$name . '_class'] ?? 'alert alert-success';
            echo '<div class="' . $cssClass . '" id="msg-flash">' . $_SESSION[$name] . '</div>';
            unset($_SESSION[$name], $_SESSION[$name . '_class']);
        }
    }
}
