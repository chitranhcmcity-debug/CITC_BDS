<?php
/**
 * Model Knowledge – Tệp tin tri thức (Knowledge Base) phục vụ đào tạo ngữ cảnh của AI.
 * Tuân thủ SOLID, Model Layer.
 */
class Knowledge
{
    public int $id;
    public string $category; // faq, policies, prices, v.v.
    public string $title;
    public string $content;
    public bool $is_active = true;
    public string $created_at;
    public string $updated_at;

    public function __construct(array|object $data = [])
    {
        if (empty($data)) return;

        $dataArr = is_object($data) ? get_object_vars($data) : $data;

        $this->id = (int)($dataArr['id'] ?? 0);
        $this->category = (string)($dataArr['category'] ?? 'faq');
        $this->title = (string)($dataArr['title'] ?? '');
        $this->content = (string)($dataArr['content'] ?? '');
        $this->is_active = !empty($dataArr['is_active']);
        $this->created_at = (string)($dataArr['created_at'] ?? date('Y-m-d H:i:s'));
        $this->updated_at = (string)($dataArr['updated_at'] ?? date('Y-m-d H:i:s'));
    }
}
