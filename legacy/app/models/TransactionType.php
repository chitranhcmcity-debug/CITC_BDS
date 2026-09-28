<?php
/**
 * Model TransactionType – Ánh xạ tới bảng `loai_giao_dich` (Bán, Cho thuê...).
 * Tuân thủ SOLID, Model Layer.
 */
class TransactionType extends Model
{
    protected string $table = 'loai_giao_dich';

    public int $id = 0;
    public string $name = '';
    public string $slug = '';
    public ?string $code = null;
    public ?string $description = null;
    public int $sort_order = 0;
    public string $status = 'active';
    public string $created_at = '';

    public function __construct(array|object $data = [])
    {
        parent::__construct();
        if (empty($data)) return;
        $d = is_object($data) ? get_object_vars($data) : $data;

        $this->id          = (int)($d['id'] ?? 0);
        $this->name        = (string)($d['name'] ?? '');
        $this->slug        = (string)($d['slug'] ?? '');
        $this->code        = $d['code'] ?? null;
        $this->description = $d['description'] ?? null;
        $this->sort_order  = (int)($d['sort_order'] ?? 0);
        $this->status      = (string)($d['status'] ?? 'active');
        $this->created_at  = (string)($d['created_at'] ?? '');
    }
}
