<?php
/**
 * Model Prompt – System Instruction cấu hình cho hành vi của AI.
 * Tuân thủ SOLID, Model Layer.
 */
class Prompt
{
    public int $id;
    public string $name;
    public string $content;
    public bool $is_active = true;
    public string $created_at;

    public function __construct(array|object $data = [])
    {
        if (empty($data)) return;

        $dataArr = is_object($data) ? get_object_vars($data) : $data;

        $this->id = (int)($dataArr['id'] ?? 0);
        $this->name = (string)($dataArr['name'] ?? 'system_instruction');
        $this->content = (string)($dataArr['content'] ?? '');
        $this->is_active = !empty($dataArr['is_active']);
        $this->created_at = (string)($dataArr['created_at'] ?? date('Y-m-d H:i:s'));
    }
}
