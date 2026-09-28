<?php
/**
 * Model Attachment – Cung cấp helper quản lý tệp đính kèm tin nhắn (Ảnh, PDF, Word...).
 * Tuân thủ SOLID, Model Layer.
 */
class Attachment
{
    public string $fileName;
    public string $fileUrl;
    public string $fileType;

    public function __construct(string $fileName = '')
    {
        $this->fileName = $fileName;
        $this->fileUrl = !empty($fileName) ? URL_ROOT . '/public/uploads/chats/' . $fileName : '';
        $this->fileType = $this->detectType();
    }

    /**
     * Phân loại tệp tin dựa trên đuôi mở rộng.
     */
    public function detectType(): string
    {
        if (empty($this->fileName)) return 'unknown';
        $ext = strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION));

        if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true)) {
            return 'image';
        }
        if ($ext === 'pdf') {
            return 'pdf';
        }
        if (in_array($ext, ['doc', 'docx'], true)) {
            return 'word';
        }
        if (in_array($ext, ['xls', 'xlsx', 'csv'], true)) {
            return 'excel';
        }
        if (in_array($ext, ['mp4', 'mov', 'avi', 'mkv'], true)) {
            return 'video';
        }
        return 'file';
    }

    /**
     * Lấy FontAwesome icon tương ứng với loại tệp.
     */
    public function getIcon(): string
    {
        return match ($this->fileType) {
            'image' => 'fa-file-image text-success',
            'pdf'   => 'fa-file-pdf text-danger',
            'word'  => 'fa-file-word text-primary',
            'excel' => 'fa-file-excel text-success',
            'video' => 'fa-file-video text-warning',
            default => 'fa-file text-secondary'
        };
    }
}
