<?php
/**
 * Model dữ liệu yêu cầu tư vấn / liên hệ.
 */
class Contact
{
    public int $id;
    public string $fullname;
    public string $phone;
    public ?string $email = null;
    public ?string $subject = null;
    public ?string $content = null;
    public string $type = 'khac';
    public string $status = 'moi';
    public ?int $assigned_admin = null;
    public ?string $note = null;
    public ?string $ip_address = null;
    public ?string $browser = null;
    public ?string $device = null;
    public string $created_at;
    public string $updated_at;

    /** @var Attachment[] Danh sách file đính kèm */
    public array $attachments = [];
}
