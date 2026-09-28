<?php
/**
 * Model Facility – Ánh xạ tới bảng tiện ích `facilities`.
 * Tuân thủ SOLID, Model Layer.
 */
class Facility extends Model
{
    protected string $table = 'facilities';

    public int $id = 0;
    public string $name = '';
    public string $slug = '';
    public ?string $icon = null;
    public int $sort_order = 0;
    public string $status = 'active';

    public function __construct(array|object $data = [])
    {
        parent::__construct();
        if (empty($data)) return;
        $d = is_object($data) ? get_object_vars($data) : $data;

        $this->id         = (int)($d['id'] ?? 0);
        $this->name       = (string)($d['name'] ?? '');
        $this->slug       = (string)($d['slug'] ?? '');
        $this->icon       = $d['icon'] ?? null;
        $this->sort_order = (int)($d['sort_order'] ?? 0);
        $this->status     = (string)($d['status'] ?? 'active');
    }
}
