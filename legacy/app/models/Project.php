<?php
/**
 * Model Project – Ánh xạ tới bảng `projects` (Dự án bất động sản).
 * Tuân thủ SOLID, Model Layer.
 */
class Project extends Model
{
    protected string $table = 'projects';

    public int $id = 0;
    public string $name = '';
    public ?string $investor = null;
    public ?string $address = null;
    public ?string $google_map = null;
    public ?string $logo = null;
    public ?string $image = null;
    public ?string $description = null;
    public ?string $facilities = null;
    public int $sort_order = 0;
    public string $status = 'active';
    public string $created_at = '';
    public string $updated_at = '';

    public function __construct(array|object $data = [])
    {
        parent::__construct();
        if (empty($data)) return;
        $d = is_object($data) ? get_object_vars($data) : $data;

        $this->id          = (int)($d['id'] ?? 0);
        $this->name        = (string)($d['name'] ?? '');
        $this->investor    = $d['investor'] ?? null;
        $this->address     = $d['address'] ?? null;
        $this->google_map  = $d['google_map'] ?? null;
        $this->logo        = $d['logo'] ?? null;
        $this->image       = $d['image'] ?? null;
        $this->description = $d['description'] ?? null;
        $this->facilities  = $d['facilities'] ?? null;
        $this->sort_order  = (int)($d['sort_order'] ?? 0);
        $this->status      = (string)($d['status'] ?? 'active');
        $this->created_at  = (string)($d['created_at'] ?? '');
        $this->updated_at  = (string)($d['updated_at'] ?? '');
    }

    /**
     * Giải mã danh sách tiện ích từ chuỗi JSON hoặc mảng cách nhau dấu phẩy.
     */
    public function getFacilitiesArray(): array
    {
        if (empty($this->facilities)) return [];
        $decoded = json_decode($this->facilities, true);
        if (is_array($decoded)) return $decoded;
        return array_map('trim', explode(',', $this->facilities));
    }
}
