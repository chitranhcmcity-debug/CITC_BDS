<?php
/**
 * Model Ward – Ánh xạ tới bảng phường/xã `wards`.
 * Tuân thủ SOLID, Model Layer.
 */
class Ward extends Model
{
    protected string $table = 'wards';

    public int $id = 0;
    public string $code = '';
    public string $district_code = '';
    public string $name = '';
    public ?string $type = null;
    public int $sort_order = 0;
    public string $status = 'active';

    public function __construct(array|object $data = [])
    {
        parent::__construct();
        if (empty($data)) return;
        $d = is_object($data) ? get_object_vars($data) : $data;

        $this->id            = (int)($d['id'] ?? 0);
        $this->code          = (string)($d['code'] ?? '');
        $this->district_code = (string)($d['district_code'] ?? '');
        $this->name          = (string)($d['name'] ?? '');
        $this->type          = $d['type'] ?? null;
        $this->sort_order    = (int)($d['sort_order'] ?? 0);
        $this->status        = (string)($d['status'] ?? 'active');
    }
}
