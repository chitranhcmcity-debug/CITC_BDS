<?php
/**
 * Model Conversation – Đại diện cho cuộc hội thoại Live Chat (Khách-Bán, Khách-CSKH, v.v.).
 * Tuân thủ SOLID, Model Layer.
 */
class Conversation
{
    public int $id;
    public ?int $customer_id = null;
    public ?string $customer_guest_token = null;
    public ?int $seller_id = null;
    public ?int $staff_id = null;
    public string $type;
    public string $status;
    public ?string $title = null;
    public bool $is_pinned = false;
    public string $created_at;
    public string $updated_at;

    public function __construct(array|object $data = [])
    {
        if (empty($data)) return;

        $dataArr = is_object($data) ? get_object_vars($data) : $data;

        $this->id = (int)($dataArr['id'] ?? 0);
        $this->customer_id = isset($dataArr['customer_id']) ? (int)$dataArr['customer_id'] : null;
        $this->customer_guest_token = $dataArr['customer_guest_token'] ?? null;
        $this->seller_id = isset($dataArr['seller_id']) ? (int)$dataArr['seller_id'] : null;
        $this->staff_id = isset($dataArr['staff_id']) ? (int)$dataArr['staff_id'] : null;
        $this->type = (string)($dataArr['type'] ?? 'customer_cskh');
        $this->status = (string)($dataArr['status'] ?? 'waiting');
        $this->title = $dataArr['title'] ?? null;
        $this->is_pinned = !empty($dataArr['is_pinned']);
        $this->created_at = (string)($dataArr['created_at'] ?? date('Y-m-d H:i:s'));
        $this->updated_at = (string)($dataArr['updated_at'] ?? date('Y-m-d H:i:s'));
    }
}
