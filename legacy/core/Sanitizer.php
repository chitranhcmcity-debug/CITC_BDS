<?php
/**
 * Lớp Sanitizer - Tập trung toàn bộ logic lọc & mã hóa dữ liệu đầu vào.
 *
 * Nguyen tac su dung:
 *   - Moi du lieu tu $_POST/$_GET/$_FILES deu phai qua Sanitizer truoc khi xu ly
 *   - KHI LUU vao DB: loc HTML doc hai (strip tags hoac purify)
 *   - KHI HIEN THI ra HTML: html escape bang htmlspecialchars()
 *
 * QUAN TRONG: Sanitize khong thay the Prepared Statements!
 * Ca 2 bien phap phai duoc dung dong thoi.
 */
class Sanitizer
{
    // ==========================================
    // LỌC CHUỖI VĂN BẢN
    // ==========================================

    /**
     * Loc chuoi van ban thong thuong (tieu de, ten, dia chi, so dien thoai).
     * Xoa toan bo the HTML, cat khoang trang thua, lo bo ky tu dieu khien.
     *
     * @param  string $str Chuoi can loc
     * @param  int    $max Do dai toi da (0 = khong gioi han)
     * @return string      Chuoi da duoc loc sach
     */
    public static function chuoi(string $str, int $max = 0): string
    {
        // Xoa the HTML va PHP
        $str = strip_tags($str);
        // Xoa ky tu dieu khien (null byte, LF, CR dang tan cong)
        $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $str);
        // Cat khoang trang
        $str = trim($str);

        if ($max > 0) {
            $str = mb_substr($str, 0, $max, 'UTF-8');
        }
        return $str;
    }

    /**
     * Loc va ma hoa de hien thi an toan ra HTML (chong XSS khi echo ra view).
     *
     * @param  mixed  $str   Gia tri can hien thi
     * @return string        Chuoi da duoc HTML-escaped an toan
     */
    public static function html(mixed $str): string
    {
        return htmlspecialchars((string)$str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** Loc HTML bai viet theo whitelist de giu dinh dang ma khong cho script/event. */
    public static function noiDungHtml(string $html): string
    {
        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><blockquote><a><img><figure><figcaption><hr>';
        $html = strip_tags($html, $allowedTags);
        $html = preg_replace('/\s(?:on\w+|style|srcdoc)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/iu', '', $html) ?? '';
        $html = preg_replace('/\s(href|src)\s*=\s*(["\'])\s*(?:javascript|vbscript|data):.*?\2/iu', '', $html) ?? '';
        return trim($html);
    }

    // ==========================================
    // ÉP KIỂU SỐ (QUAN TRỌNG NHẤT CHỐNG SQL INJECTION)
    // ==========================================

    /**
     * Ep kieu so nguyen. Neu khong phai so -> tra ve gia tri mac dinh.
     * Dung cho: Gia, Dien tich, So phong ngu, ID, trang...
     *
     * @param  mixed $val     Gia tri can ep kieu
     * @param  int   $default Gia tri mac dinh neu khong hop le
     * @param  int   $min     Gia tri toi thieu (0 = khong gioi han min)
     * @return int            So nguyen da duoc kiem tra
     */
    public static function soNguyen(mixed $val, int $default = 0, int $min = 0): int
    {
        $num = filter_var($val, FILTER_VALIDATE_INT);
        if ($num === false) {
            return $default;
        }
        return max($min, (int)$num);
    }

    /**
     * Ep kieu so thuc (float). Dung cho dien tich, gia BDS...
     *
     * @param  mixed  $val     Gia tri can ep kieu
     * @param  float  $default Gia tri mac dinh neu khong hop le
     * @return float           So thuc da duoc kiem tra
     */
    public static function soThuc(mixed $val, float $default = 0.0): float
    {
        $num = filter_var($val, FILTER_VALIDATE_FLOAT);
        return $num !== false ? (float)$num : $default;
    }

    // ==========================================
    // LỌC EMAIL VÀ URL
    // ==========================================

    /**
     * Kiem tra va loc dia chi email hop le.
     *
     * @param  string      $email Email can kiem tra
     * @return string|null        Email sach hoac null neu khong hop le
     */
    public static function email(string $email): ?string
    {
        $email = trim(strtolower($email));
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * Loc URL an toan, chi cho phep http:// va https://.
     *
     * @param  string      $url URL can kiem tra
     * @return string|null      URL sach hoac null neu khong hop le
     */
    public static function url(string $url): ?string
    {
        $url = trim($url);
        return filter_var($url, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//i', $url)
            ? $url
            : null;
    }

    // ==========================================
    // LỌC MẢNG $_POST HÀN LOẠT
    // ==========================================

    /**
     * Loc toan bo mang $_POST theo schema do lap trinh vien dinh nghia.
     *
     * Vi du schema:
     *   [
     *     'ten'      => 'chuoi',
     *     'gia'      => 'so_nguyen',
     *     'dien_tich'=> 'so_thuc',
     *     'email'    => 'email',
     *   ]
     *
     * @param  array $schema Mang [ten_truong => kieu_loc]
     * @param  array $post   Mang du lieu can loc (mac dinh $_POST)
     * @return array         Mang da duoc loc theo schema
     */
    public static function post(array $schema, array $post = []): array
    {
        if (empty($post)) {
            $post = $_POST;
        }
        $ketQua = [];
        foreach ($schema as $truong => $kieu) {
            $giaTri = $post[$truong] ?? '';
            $ketQua[$truong] = match ($kieu) {
                'so_nguyen' => self::soNguyen($giaTri),
                'so_thuc'   => self::soThuc($giaTri),
                'email'     => self::email((string)$giaTri) ?? '',
                'url'       => self::url((string)$giaTri) ?? '',
                'html'      => self::html($giaTri),   // Hien thi, khong luu
                default     => self::chuoi((string)$giaTri), // 'chuoi' hoac bat ky gi khac
            };
        }
        return $ketQua;
    }
}
