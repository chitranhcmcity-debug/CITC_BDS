<?php
/**
 * Model Message – Đại diện cho một tin nhắn đơn lẻ trong hội thoại Live Chat.
 * Tuân thủ SOLID, Model Layer.
 */
class Message
{
    public int $id;
    public int $conversation_id;
    public ?int $sender_id = null;
    public ?string $sender_name = null;
    public string $message;
    public string $message_type;
    public ?string $attachment = null;
    public bool $is_read = false;
    public string $created_at;

    public function __construct(array|object $data = [])
    {
        if (empty($data)) return;

        $dataArr = is_object($data) ? get_object_vars($data) : $data;

        $this->id = (int)($dataArr['id'] ?? 0);
        $this->conversation_id = (int)($dataArr['conversation_id'] ?? 0);
        $this->sender_id = isset($dataArr['sender_id']) ? (int)$dataArr['sender_id'] : null;
        $this->sender_name = $dataArr['sender_name'] ?? null;
        $this->message = (string)($dataArr['message'] ?? '');
        $this->message_type = (string)($dataArr['message_type'] ?? 'text');
        $this->attachment = $dataArr['attachment'] ?? null;
        $this->is_read = !empty($dataArr['is_read']);
        $this->created_at = (string)($dataArr['created_at'] ?? date('Y-m-d H:i:s'));
    }
}
