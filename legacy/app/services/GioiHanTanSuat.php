<?php

/** Rate limiter nhe, dung file ngoai public va khoa flock. */
class RateLimiter
{
    public static function tooManyAttempts(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $state = self::read($key, $windowSeconds);
        return count($state['attempts']) >= $maxAttempts;
    }

    public static function hit(string $key, int $windowSeconds): void
    {
        $path = self::path($key);
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0750, true);
        }

        $handle = fopen($path, 'c+');
        if (!$handle) {
            return;
        }
        try {
            flock($handle, LOCK_EX);
            $raw = stream_get_contents($handle);
            $data = json_decode($raw ?: '{}', true);
            $attempts = is_array($data['attempts'] ?? null) ? $data['attempts'] : [];
            $cutoff = time() - $windowSeconds;
            $attempts = array_values(array_filter($attempts, static fn($time) => (int)$time >= $cutoff));
            $attempts[] = time();
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode(['attempts' => $attempts]));
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public static function clear(string $key): void
    {
        $path = self::path($key);
        if (is_file($path)) {
            unlink($path);
        }
    }

    /** Số giây còn lại trước khi lần thử cũ nhất rời khỏi cửa sổ giới hạn. */
    public static function availableIn(string $key, int $windowSeconds = 900): int
    {
        $state = self::read($key, $windowSeconds);
        if (empty($state['attempts'])) {
            return 0;
        }
        return max(0, $windowSeconds - (time() - (int)min($state['attempts'])));
    }

    private static function read(string $key, int $windowSeconds): array
    {
        $path = self::path($key);
        if (!is_file($path)) {
            return ['attempts' => []];
        }
        $data = json_decode((string)file_get_contents($path), true);
        $cutoff = time() - $windowSeconds;
        $attempts = array_values(array_filter(
            is_array($data['attempts'] ?? null) ? $data['attempts'] : [],
            static fn($time) => (int)$time >= $cutoff
        ));
        return ['attempts' => $attempts];
    }

    private static function path(string $key): string
    {
        return APP_ROOT . '/logs/rate_limits/' . hash('sha256', $key) . '.json';
    }
}
