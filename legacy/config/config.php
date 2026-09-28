<?php

define('APP_ROOT', dirname(dirname(__FILE__)));

// Path to target uploads directory (writes to shared root public/uploads if it exists)
$sharedUploadDir = dirname(APP_ROOT) . '/public/uploads';
if (is_dir($sharedUploadDir)) {
    define('UPLOAD_ROOT_DIR', $sharedUploadDir);
} else {
    define('UPLOAD_ROOT_DIR', APP_ROOT . '/public/uploads');
}


function load_env_file(string $path): void {
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, "\"'");

        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

function env(string $key, mixed $default = null): mixed {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    // Railway/dashboard values may carry a trailing newline or wrapping quotes.
    $value = trim($value);
    if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && str_ends_with($value, $value[0])) {
        $value = trim(substr($value, 1, -1));
    }
    return $value;
}

load_env_file(APP_ROOT . '/.env');

date_default_timezone_set((string)env('APP_TIMEZONE', 'Asia/Ho_Chi_Minh'));

define('URL_ROOT', rtrim((string)env('URL_ROOT', 'http://localhost/CITC_BDS'), '/'));
// Tên website = tên miền đang truy cập (bỏ "www." và cổng), trừ khi đặt SITE_NAME trong .env.
function current_site_name(string $default = 'TimNhaDat.site'): string {
    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    $host = preg_replace('/:\d+$/', '', $host);
    $host = preg_replace('/^www\./', '', $host);
    if ($host === '' || !preg_match('/^[a-z0-9.-]+$/', $host) || in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
        return $default;
    }
    return $host;
}
define('SITE_NAME', (string)env('SITE_NAME', current_site_name()));

// Database Configuration
define('DB_HOST', (string)env('DB_HOST', 'localhost'));
define('DB_USER', (string)env('DB_USER', 'root'));
define('DB_PASS', (string)env('DB_PASS', ''));
define('DB_NAME', (string)env('DB_NAME', 'citcbds'));
define('DB_CHARSET', (string)env('DB_CHARSET', 'utf8mb4'));

/**
 * Trả về URL đúng cho ảnh bất động sản.
 * - Nếu $path là URL đầy đủ (http/https), trả về trực tiếp.
 * - Nếu là tên file cục bộ, ghép với URL_ROOT/public/uploads/.
 * - Nếu rỗng, trả về ảnh mặc định.
 */
function img_url(string $path = '', string $default = ''): string {
    if (empty($path)) {
        return $default ?: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80';
    }
    // Kiểm tra URL tuyệt đối (Unsplash, external)
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    // File local trong thư mục uploads
    return URL_ROOT . '/public/uploads/' . $path;
}

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', (string)env('GOOGLE_CLIENT_ID', ''));
define('GOOGLE_CLIENT_SECRET', (string)env('GOOGLE_CLIENT_SECRET', ''));
define('GOOGLE_REDIRECT_URI', (string)env('GOOGLE_REDIRECT_URI', URL_ROOT . '/nguoi-dung/google-callback'));

// Facebook OAuth Configuration
define('FACEBOOK_APP_ID', (string)env('FACEBOOK_APP_ID', ''));
define('FACEBOOK_APP_SECRET', (string)env('FACEBOOK_APP_SECRET', ''));
define('FACEBOOK_REDIRECT_URI', (string)env('FACEBOOK_REDIRECT_URI', URL_ROOT . '/nguoi-dung/facebook-callback'));
