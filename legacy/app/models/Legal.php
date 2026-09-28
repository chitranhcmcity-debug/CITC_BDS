<?php
/**
 * Model Legal – Ánh xạ tới pháp lý bất động sản `loai_phap_ly`.
 * Tuân thủ SOLID, Model Layer.
 */
class Legal extends Model
{
    protected string $table = 'loai_phap_ly';

    public int $id = 0;
    public string $name = '';
    public string $code = '';
    public int $sort_order = 0;
    public string $status = 'active';

    public function __construct(array|object $data = [])
    {
        parent::__construct();
        if (empty($data)) return;
        $d = is_object($data) ? get_object_vars($data) : $data;

        $this->id         = (int)($d['id'] ?? 0);
        $this->name       = (string)($d['name'] ?? '');
        $this->code       = (string)($d['code'] ?? '');
        $this->sort_order = (int)($d['sort_order'] ?? 0);
        $this->status     = (string)($d['status'] ?? 'active');
    }
}
