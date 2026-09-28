<?php
/**
 * Model Transaction – Đối tượng dữ liệu giao dịch ví (nạp/chi).
 * Sử dụng bởi TransactionRepository & WalletService.
 * Tuân thủ SOLID, Model Layer.
 */
class Transaction
{
    public int $id = 0;
    public int $user_id = 0;
    public int $amount = 0;
    public string $type = 'nap_tien';
    public string $method_or_desc = '';
    public string $status = 'cho_duyet';
    public string $created_at = '';

    /** @var array Loại giao dịch nạp tiền */
    public const DEPOSIT_TYPES = ['nap_tien'];

    /** @var array Loại giao dịch chi tiêu */
    public const EXPENSE_TYPES = ['mua_vip', 'mua_up', 'gia_han', 'thuong_chia_se', 'rut_thuong', 'hoan_tien'];

    /** @var array Trạng thái hợp lệ */
    public const STATUSES = ['cho_duyet', 'da_duyet', 'tu_choi'];

    public function __construct(array|object $data = [])
    {
        if (empty($data)) return;
        $d = is_object($data) ? get_object_vars($data) : $data;

        $this->id             = (int)($d['id'] ?? 0);
        $this->user_id        = (int)($d['user_id'] ?? $d['ma_nguoi_dung'] ?? 0);
        $this->amount         = (int)($d['amount'] ?? $d['so_tien'] ?? 0);
        $this->type           = (string)($d['type'] ?? $d['loai'] ?? 'nap_tien');
        $this->method_or_desc = (string)($d['method_or_desc'] ?? $d['phuong_thuc'] ?? $d['mo_ta'] ?? '');
        $this->status         = (string)($d['status'] ?? $d['trang_thai'] ?? 'cho_duyet');
        $this->created_at     = (string)($d['created_at'] ?? $d['ngay_tao'] ?? '');
    }

    /**
     * Kiểm tra đây có phải giao dịch nạp tiền.
     */
    public function isDeposit(): bool
    {
        return in_array($this->type, self::DEPOSIT_TYPES, true);
    }
}
