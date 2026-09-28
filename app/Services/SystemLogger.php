<?php

namespace App\Services;

use App\Models\Database;
use App\Models\SystemLog;
use App\Models\The;
use App\Repositories\SystemLogRepository;

/**
 * Central structured logger. Events are buffered and flushed at shutdown so
 * normal requests do not wait for a database write in the middle of business logic.
 */
class SystemLogger
{
    private static array $buffer = [];

    private static bool $registered = false;

    private static bool $flushing = false;

    public static function activity(string $action, string $module, ?string $targetType = null, int|string|null $targetId = null, string $description = '', array $metadata = [], ?int $userId = null): void
    {
        self::queue('activity', self::common($action, $module, $targetType, $targetId, $description) + [
            'actor' => $userId ?? self::userId(),
            'metadata' => self::json(self::sanitize($metadata)),
        ]);
    }

    public static function admin(string $action, string $module, ?string $targetType = null, int|string|null $targetId = null, string $description = '', array $oldData = [], array $newData = [], ?int $adminId = null): void
    {
        self::queue('admin', self::common($action, $module, $targetType, $targetId, $description) + [
            'actor' => $adminId ?? self::userId(),
            'old_data' => self::json(self::sanitize($oldData)),
            'new_data' => self::json(self::sanitize($newData)),
        ]);
    }

    public static function login(string $email, string $status, ?string $reason = null, ?int $userId = null): void
    {
        if (! in_array($status, SystemLog::LOGIN_STATUSES, true)) {
            return;
        }
        $agent = self::agentInfo();
        if ($status === 'success' && $userId) {
            try {
                if (! (new SystemLogRepository)->hasSuccessfulLoginFromIp($userId, self::ip())) {
                    self::activity('new_ip_login', 'security', 'nguoi_dung', $userId, 'Đăng nhập thành công từ địa chỉ IP mới', ['ip' => self::ip()], $userId);
                }
            } catch (Throwable) {
                // Logging must never block authentication when the audit store is unavailable.
            }
        }
        self::queue('login', [
            'user_id' => $userId, 'email' => mb_substr(strtolower(trim($email)), 0, 190),
            'ip_address' => self::ip(), 'browser' => $agent['browser'], 'platform' => $agent['platform'],
            'device' => $agent['device'], 'country' => self::country(), 'status' => $status,
            'fail_reason' => $reason ? mb_substr($reason, 0, 500) : null, 'user_agent' => self::userAgent(),
        ]);
    }

    public static function error(Throwable|string $error, string $module = 'system', string $level = 'error', array $context = []): void
    {
        $level = in_array($level, SystemLog::ERROR_LEVELS, true) ? $level : 'error';
        $throwable = $error instanceof Throwable ? $error : null;
        self::queue('error', [
            'module' => mb_substr($module, 0, 80), 'level' => $level,
            'error_type' => $throwable ? $throwable::class : 'RuntimeError',
            'message' => mb_substr($throwable ? $throwable->getMessage() : (string) $error, 0, 10000),
            'stack_trace' => $throwable ? mb_substr($throwable->getTraceAsString(), 0, 65000) : null,
            'request_url' => mb_substr((string) ($_SERVER['REQUEST_URI'] ?? ''), 0, 1500),
            'request_method' => mb_substr((string) ($_SERVER['REQUEST_METHOD'] ?? 'CLI'), 0, 10),
            'user_id' => self::userId(), 'ip_address' => self::ip(),
            'context' => self::json(self::sanitize($context)),
        ]);
    }

    public static function flush(): void
    {
        if (self::$flushing || ! self::$buffer) {
            return;
        }
        self::$flushing = true;
        $events = self::$buffer;
        self::$buffer = [];
        try {
            $repository = new SystemLogRepository;
            foreach ($events as [$type,$payload]) {
                match ($type) {
                    'activity' => $repository->insertActivity($payload),
                    'admin' => $repository->insertAdmin($payload),
                    'login' => $repository->insertLogin($payload),
                    'error' => $repository->insertError($payload),
                };
            }
        } catch (Throwable $exception) {
            error_log('[STRUCTURED LOG FALLBACK] '.$exception->getMessage());
        } finally {
            self::$flushing = false;
        }
    }

    private static function queue(string $type, array $payload): void
    {
        self::$buffer[] = [$type, $payload];
        if (! self::$registered) {
            self::$registered = true;
            register_shutdown_function([self::class, 'flush']);
        }
    }

    private static function common(string $action, string $module, ?string $targetType, int|string|null $targetId, string $description): array
    {
        return [
            'action' => mb_substr($action, 0, 100), 'module' => mb_substr($module, 0, 80),
            'target_type' => $targetType ? mb_substr($targetType, 0, 80) : null,
            'target_id' => is_numeric($targetId) ? (int) $targetId : null,
            'description' => mb_substr($description, 0, 1000), 'ip_address' => self::ip(), 'user_agent' => self::userAgent(),
        ];
    }

    private static function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            $sensitive = array_filter(['password', 'pass', 'token', 'secret', 'api_key', 'checksum', 'authorization', 'csrf'], static fn ($needle) => str_contains($normalizedKey, $needle));
            if ($sensitive) {
                $data[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $data[$key] = self::sanitize($value);
            } elseif (is_string($value)) {
                $data[$key] = mb_substr($value, 0, 2000);
            }
        }

        return $data;
    }

    private static function json(array $data): ?string
    {
        return $data ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
    }

    private static function userId(): ?int
    {
        return ! empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    private static function ip(): string
    {
        return mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 45);
    }

    private static function userAgent(): string
    {
        return mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
    }

    private static function country(): ?string
    {
        $country = trim((string) ($_SERVER['HTTP_CF_IPCOUNTRY'] ?? $_SERVER['HTTP_X_COUNTRY_CODE'] ?? ''));

        return $country !== '' ? mb_substr($country, 0, 100) : null;
    }

    private static function agentInfo(): array
    {
        $ua = self::userAgent();
        $browser = match (true) {
            str_contains($ua, 'Edg/') => 'Edge', str_contains($ua, 'OPR/') => 'Opera',
            str_contains($ua, 'Chrome/') => 'Chrome', str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') => 'Safari', default => 'Unknown'
        };
        $platform = match (true) {
            preg_match('/iPhone|iPad|iPod/i', $ua) === 1 => 'iOS', str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Windows') => 'Windows', str_contains($ua, 'Mac OS') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux', default => 'Unknown'
        };
        $device = preg_match('/iPad|Tablet/i', $ua) ? 'Tablet' : (preg_match('/Mobile|Android|iPhone|iPod/i', $ua) ? 'Mobile' : 'Desktop');

        return compact('browser', 'platform', 'device');
    }
}
