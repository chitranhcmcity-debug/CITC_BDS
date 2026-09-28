<?php
/**
 * Model LoginHistory – Đối tượng dữ liệu lịch sử đăng nhập.
 * Sử dụng bởi SecurityService & LoginHistoryRepository.
 * Tuân thủ SOLID, Model Layer.
 */
class LoginHistory
{
    public int $id = 0;
    public ?int $user_id = null;
    public ?string $email = null;
    public ?string $ip_address = null;
    public ?string $browser = null;
    public ?string $os = null;
    public ?string $device = null;
    public ?string $country = null;
    public string $status = 'success';
    public ?string $fail_reason = null;
    public string $created_at = '';

    public function __construct(array|object $data = [])
    {
        if (empty($data)) return;
        $d = is_object($data) ? get_object_vars($data) : $data;

        $this->id          = (int)($d['id'] ?? 0);
        $this->user_id     = isset($d['user_id']) ? (int)$d['user_id'] : null;
        $this->email       = $d['email'] ?? null;
        $this->ip_address  = $d['ip_address'] ?? null;
        $this->browser     = $d['browser'] ?? null;
        $this->os          = $d['os'] ?? $d['platform'] ?? null;
        $this->device      = $d['device'] ?? null;
        $this->country     = $d['country'] ?? null;
        $this->status      = (string)($d['status'] ?? 'success');
        $this->fail_reason = $d['fail_reason'] ?? null;
        $this->created_at  = (string)($d['created_at'] ?? '');
    }
}
