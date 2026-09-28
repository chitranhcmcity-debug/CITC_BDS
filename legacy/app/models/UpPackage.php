<?php
/**
 * Model UpPackage – Gói lượt UP tin đăng.
 * Sử dụng bởi PostService & WalletRepository.
 * Tuân thủ SOLID, Model Layer.
 */
class UpPackage
{
    public int $id = 0;
    public string $name = '';
    public int $token_count = 0;
    public int $price = 0;
    public int $discount_percent = 0;
    public string $status = 'active';
    public string $created_at = '';

    /** @var array Trạng thái hợp lệ */
    public const STATUSES = ['active', 'inactive'];

    public function __construct(array|object $data = [])
    {
        if (empty($data)) return;
        $d = is_object($data) ? get_object_vars($data) : $data;

        $this->id               = (int)($d['id'] ?? 0);
        $this->name             = (string)($d['name'] ?? $d['ten'] ?? '');
        $this->token_count      = (int)($d['token_count'] ?? $d['so_luot'] ?? 0);
        $this->price            = (int)($d['price'] ?? $d['gia'] ?? 0);
        $this->discount_percent = (int)($d['discount_percent'] ?? $d['giam_gia'] ?? 0);
        $this->status           = (string)($d['status'] ?? $d['trang_thai'] ?? 'active');
        $this->created_at       = (string)($d['created_at'] ?? $d['ngay_tao'] ?? '');
    }

    /**
     * Tính giá sau giảm.
     */
    public function finalPrice(): int
    {
        if ($this->discount_percent <= 0) return $this->price;
        return (int)round($this->price * (100 - $this->discount_percent) / 100);
    }
}
