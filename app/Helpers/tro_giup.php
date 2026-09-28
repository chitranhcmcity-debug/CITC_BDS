<?php

if (! defined('URL_ROOT')) {
    // This helper is Composer-autoloaded before Laravel has loaded .env/config.
    // Build the URL from the current web request so subdirectory installs such
    // as http://localhost/CITC_BDS keep their base path.
    $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = isset($_SERVER['SCRIPT_NAME'])
        ? str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']))
        : '';
    $basePath = ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '\\')
        ? ''
        : rtrim($scriptDir, '/');
    $url = $scheme.'://'.$host.$basePath;
    define('URL_ROOT', rtrim($url, '/'));
}

if (! defined('SITE_NAME')) {
    $name = 'TimNhaDat.site';
    if (function_exists('config')) {
        try {
            $name = config('app.name') ?: $name;
        } catch (Throwable $e) {
        }
    }
    define('SITE_NAME', $name);
}

if (! defined('APP_ROOT')) {
    define('APP_ROOT', dirname(dirname(dirname(__FILE__))));
}

if (! function_exists('img_url')) {
    function img_url(string $path = '', string $default = ''): string
    {
        if (empty($path)) {
            return $default ?: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80';
        }
        // Check absolute URL
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Local uploads folder
        return URL_ROOT.'/uploads/'.$path;
    }
}

// CSRF Compatibility class mapping to Laravel CSRF helpers
if (! class_exists('Csrf')) {
    class Csrf
    {
        public static function token()
        {
            return csrf_token();
        }

        public static function field()
        {
            return csrf_field().'<input type="hidden" name="_csrf_token" value="'.csrf_token().'">';
        }
    }
}

// SafeArray class to prevent "Undefined array key" warnings in old views
if (! class_exists('SafeArray')) {
    class SafeArray implements ArrayAccess
    {
        private array $data;

        public function __construct(array $data = [])
        {
            $this->data = $data;
        }

        public function offsetExists($offset): bool
        {
            return true;
        }

        #[ReturnTypeWillChange]
        public function offsetGet($offset): mixed
        {
            return $this->data[$offset] ?? null;
        }

        public function offsetSet($offset, $value): void
        {
            $this->data[$offset] = $value;
        }

        public function offsetUnset($offset): void
        {
            unset($this->data[$offset]);
        }
    }
}
