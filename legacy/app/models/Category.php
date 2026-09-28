<?php
/**
 * Model Category – Ánh xạ tới danh mục tin đăng trong bảng `categories`.
 * Tuân thủ SOLID, Model Layer.
 */
class Category extends Model
{
    protected string $table = 'categories';

    public int $id = 0;
    public string $name = '';
    public string $slug = '';
    public ?string $code = null;
    public ?string $icon = null;
    public ?string $description = null;
    public int $sort_order = 0;
    public ?string $color = null;
    public string $status = 'active';
    public string $created_at = '';
    public string $updated_at = '';

    public function __construct(array|object $data = [])
    {
        parent::__construct();
        if (empty($data)) return;
        $d = is_object($data) ? get_object_vars($data) : $data;

        $this->id          = (int)($d['id'] ?? 0);
        $this->name        = (string)($d['name'] ?? $d['ten'] ?? '');
        $this->slug        = (string)($d['slug'] ?? $d['duong_dan'] ?? '');
        $this->code        = $d['code'] ?? null;
        $this->icon        = $d['icon'] ?? null;
        $this->description = $d['description'] ?? $d['mo_ta'] ?? null;
        $this->sort_order  = (int)($d['sort_order'] ?? $d['thu_tu'] ?? 0);
        $this->color       = $d['color'] ?? $d['mau_sac'] ?? null;
        $this->status      = (string)($d['status'] ?? $d['trang_thai'] ?? 'active');
        $this->created_at  = (string)($d['created_at'] ?? $d['ngay_tao'] ?? '');
        $this->updated_at  = (string)($d['updated_at'] ?? '');
    }
}
