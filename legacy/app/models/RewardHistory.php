<?php
/**
 * Model RewardHistory – Lịch sử nhận thưởng giới thiệu (referral).
 * Sử dụng bởi WalletRepository & ViDienTuController.
 * Tuân thủ SOLID, Model Layer.
 */
class RewardHistory
{
    public int $id = 0;
    public int $referrer_id = 0;
    public int $referee_id = 0;
    public int $amount = 0;
    public string $status = 'completed';
    public string $created_at = '';

    /** @var array Trạng thái hợp lệ */
    public const STATUSES = ['completed', 'withdrawn', 'pending'];

    public function __construct(array|object $data = [])
    {
        if (empty($data)) return;
        $d = is_object($data) ? get_object_vars($data) : $data;

        $this->id          = (int)($d['id'] ?? 0);
        $this->referrer_id = (int)($d['referrer_id'] ?? $d['ma_nguoi_gioi_thieu'] ?? 0);
        $this->referee_id  = (int)($d['referee_id'] ?? $d['ma_nguoi_duoc_gioi_thieu'] ?? 0);
        $this->amount      = (int)($d['amount'] ?? $d['so_tien'] ?? 0);
        $this->status      = (string)($d['status'] ?? $d['trang_thai'] ?? 'completed');
        $this->created_at  = (string)($d['created_at'] ?? $d['ngay_tao'] ?? '');
    }
}
