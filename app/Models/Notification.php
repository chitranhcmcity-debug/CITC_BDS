<?php

namespace App\Models;

class Notification
{
    public int $id;

    public ?int $user_id = null;

    public string $type;

    public string $title;

    public string $content;

    public ?string $url = null;

    public ?string $icon = null;

    public bool $is_read = false;

    public string $created_at;

    public string $updated_at;

    public function __construct(array|object $data = [])
    {
        if (empty($data)) {
            return;
        }

        $dataArr = is_object($data) ? get_object_vars($data) : $data;

        $this->id = (int) ($dataArr['id'] ?? 0);
        $this->user_id = isset($dataArr['user_id']) ? (int) $dataArr['user_id'] : null;
        $this->type = (string) ($dataArr['type'] ?? 'he_thong');
        $this->title = (string) ($dataArr['title'] ?? '');
        $this->content = (string) ($dataArr['content'] ?? '');
        $this->url = $dataArr['url'] ?? null;
        $this->icon = $dataArr['icon'] ?? null;
        $this->is_read = ! empty($dataArr['is_read']);
        $this->created_at = (string) ($dataArr['created_at'] ?? date('Y-m-d H:i:s'));
        $this->updated_at = (string) ($dataArr['updated_at'] ?? date('Y-m-d H:i:s'));
    }
}
