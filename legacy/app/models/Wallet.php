<?php
/**
 * Model Wallet – Đối tượng dữ liệu ví điện tử.
 * Sử dụng bởi WalletService & WalletRepository.
 * Tuân thủ SOLID, Model Layer.
 */
class Wallet
{
    public int $user_id = 0;
    public int $balance = 0;
    public int $up_turns = 0;
    public int $total_deposit = 0;
    public int $total_spent = 0;

    public function __construct(array|object $data = [])
    {
        if (empty($data)) return;
        $d = is_object($data) ? get_object_vars($data) : $data;

        $this->user_id       = (int)($d['user_id'] ?? $d['ma_nguoi_dung'] ?? $d['id'] ?? 0);
        $this->balance       = (int)($d['balance'] ?? $d['so_du'] ?? 0);
        $this->up_turns      = (int)($d['up_turns'] ?? $d['so_luot_up'] ?? 0);
        $this->total_deposit = (int)($d['total_deposit'] ?? 0);
        $this->total_spent   = (int)($d['total_spent'] ?? 0);
    }

    /**
     * Kiểm tra ví có đủ số dư không.
     */
    public function hasSufficientBalance(int $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Định dạng số dư hiển thị.
     */
    public function formattedBalance(): string
    {
        return number_format($this->balance) . ' đ';
    }
}
