<?php

namespace App\Services;

/**
 * UploadService – Quản lý tải lên tệp tin ảnh, video và tệp tin chat.
 * Hỗ trợ chuyển đổi WebP, lọc mã độc, và giới hạn kích thước tệp.
 * Tuân thủ SOLID, Service Pattern.
 */
class UploadService
{
    private string $imageDir;

    private string $videoDir;

    private string $chatDir;

    public function __construct()
    {
        $this->imageDir = APP_ROOT.'/public/uploads/';
        $this->videoDir = APP_ROOT.'/public/uploads/videos/';
        $this->chatDir = APP_ROOT.'/public/uploads/chats/';
    }

    /**
     * Upload danh sách ảnh dự án, tin đăng (chuyển sang WebP).
     */
    public function images(string $field = 'images', int $max = 10): array
    {
        if (empty($_FILES[$field]['name'][0])) {
            return [];
        }

        $out = [];
        $count = min(count($_FILES[$field]['name']), $max);
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        for ($i = 0; $i < $count; $i++) {
            if (($_FILES[$field]['error'][$i] ?? 1) !== UPLOAD_ERR_OK) {
                continue;
            }

            $tmp = $_FILES[$field]['tmp_name'][$i];
            $size = (int) $_FILES[$field]['size'][$i];

            if ($size <= 0 || $size > 5 * 1024 * 1024 || ! is_uploaded_file($tmp)) {
                continue;
            }

            $mime = $finfo->file($tmp);
            if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                continue;
            }

            $name = 'bds_'.bin2hex(random_bytes(12)).'.webp';
            if ($this->toWebp($tmp, $this->imageDir.$name)) {
                $out[] = $name;
            }
        }

        return $out;
    }

    /**
     * Upload video dự án/tin đăng.
     */
    public function video(string $field = 'video_file'): ?string
    {
        if (empty($_FILES[$field]['name']) || ($_FILES[$field]['error'] ?? 1) !== UPLOAD_ERR_OK) {
            return null;
        }

        $tmp = $_FILES[$field]['tmp_name'];
        if (! is_uploaded_file($tmp) || (int) $_FILES[$field]['size'] > 50 * 1024 * 1024) {
            return null;
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if ($mime !== 'video/mp4') {
            return null;
        }

        if (! is_dir($this->videoDir)) {
            mkdir($this->videoDir, 0755, true);
        }
        $name = 'video_'.bin2hex(random_bytes(12)).'.mp4';

        return move_uploaded_file($tmp, $this->videoDir.$name) ? 'videos/'.$name : null;
    }

    /**
     * Upload tệp đính kèm trong Live Chat (Giới hạn 20MB, lọc định dạng an toàn).
     */
    public function chatFile(string $field = 'file'): ?string
    {
        if (empty($_FILES[$field]['name']) || ($_FILES[$field]['error'] ?? 1) !== UPLOAD_ERR_OK) {
            return null;
        }

        $tmp = $_FILES[$field]['tmp_name'];
        $size = (int) $_FILES[$field]['size'];
        $originalName = $_FILES[$field]['name'];

        // 1. Giới hạn dung lượng 20MB
        if ($size <= 0 || $size > 20 * 1024 * 1024 || ! is_uploaded_file($tmp)) {
            return null;
        }

        // 2. Lọc đuôi file hợp lệ
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'mp4'];
        if (! in_array($ext, $allowedExtensions, true)) {
            return null;
        }

        // 3. Kiểm tra MIME Type thực tế
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        $allowedMimes = [
            'image/png', 'image/jpeg', 'image/pjpeg', 'image/webp',
            'application/pdf',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'video/mp4', 'video/quicktime',
        ];
        if (! in_array($mime, $allowedMimes, true)) {
            return null;
        }

        // 4. Di chuyển vào thư mục lưu trữ chats
        if (! is_dir($this->chatDir)) {
            mkdir($this->chatDir, 0755, true);
        }

        $safeName = 'chat_'.bin2hex(random_bytes(12)).'.'.$ext;
        if (move_uploaded_file($tmp, $this->chatDir.$safeName)) {
            return $safeName;
        }

        return null;
    }

    /**
     * Xóa ảnh đăng bài.
     */
    public function delete(string $file): void
    {
        $path = realpath($this->imageDir.$file);
        $base = realpath($this->imageDir);
        if ($path && $base && str_starts_with($path, $base) && is_file($path)) {
            @unlink($path);
        }
    }

    private function toWebp(string $src, string $dest): bool
    {
        if (! is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0755, true);
        }

        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return move_uploaded_file($src, $dest);
        }

        $raw = file_get_contents($src);
        $im = $raw !== false ? @imagecreatefromstring($raw) : false;
        if (! $im) {
            return false;
        }

        $w = imagesx($im);
        $h = imagesy($im);
        $scale = min(1, 1920 / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));

        $canvas = imagecreatetruecolor($nw, $nh);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $ok = imagewebp($canvas, $dest, 82);
        imagedestroy($canvas);
        imagedestroy($im);

        return $ok;
    }
}
