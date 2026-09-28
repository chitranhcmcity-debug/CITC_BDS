<?php
/**
 * Model Feedback – Đánh giá chất lượng câu trả lời của AI từ người dùng.
 * Tuân thủ SOLID, Model Layer.
 */
class Feedback
{
    public int $id;
    public int $conversation_id;
    public int $message_id;
    public int $rating; // 1 (hữu ích), -1 (không hữu ích)
    public ?string $feedback_text = null;
    public string $created_at;

    public function __construct(array|object $data = [])
    {
        if (empty($data)) return;

        $dataArr = is_object($data) ? get_object_vars($data) : $data;

        $this->id = (int)($dataArr['id'] ?? 0);
        $this->conversation_id = (int)($dataArr['conversation_id'] ?? 0);
        $this->message_id = (int)($dataArr['message_id'] ?? 0);
        $this->rating = (int)($dataArr['rating'] ?? 1);
        $this->feedback_text = $dataArr['feedback_text'] ?? null;
        $this->created_at = (string)($dataArr['created_at'] ?? date('Y-m-d H:i:s'));
    }
}
