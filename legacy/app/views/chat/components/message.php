<?php
/**
 * Component Hiển thị dòng tin nhắn trong cuộc trò chuyện (Hỗ trợ Ảnh, PDF, Word, Excel, Video, Emoji).
 */
$isMine = !empty($msg['is_mine']) || (isset($msg['sender_id']) && (int)$msg['sender_id'] === (int)Session::get('user_id'));
$isSystem = ($msg['message_type'] ?? 'text') === 'system';

$time = isset($msg['created_at']) ? date('H:i d/m', strtotime($msg['created_at'])) : '';
$senderName = $msg['sender_name'] ?? 'Khách';

$hasAttachment = !empty($msg['attachment']);
$attachIcon = 'fa-file';
$isImage = false;
$isVideo = false;

if ($hasAttachment) {
    require_once APP_ROOT . '/app/models/Attachment.php';
    $attachmentObj = new Attachment($msg['attachment']);
    $attachIcon = $attachmentObj->getIcon();
    if ($attachmentObj->fileType === 'image') {
        $isImage = true;
    } elseif ($attachmentObj->fileType === 'video') {
        $isVideo = true;
    }
}
?>

<?php if ($isSystem): ?>
    <!-- Tin nhắn Hệ thống/Thu hồi -->
    <div class="text-center my-3 small text-muted">
        <span class="bg-light px-3 py-1 rounded-pill border">
            <i class="fa-solid fa-circle-info me-1"></i> <?= htmlspecialchars($msg['message']) ?>
        </span>
    </div>
<?php else: ?>
    <!-- Tin nhắn thông thường -->
    <div class="chat-msg-row <?= $isMine ? 'mine' : 'other' ?>" id="chat-row-<?= $msg['id'] ?>">
        <div class="chat-msg-bubble">
            <!-- Tên người gửi (nếu là đối phương) -->
            <?php if (!$isMine): ?>
                <span class="d-block fw-bold mb-1 text-primary" style="font-size: 0.72rem;"><?= htmlspecialchars($senderName) ?></span>
            <?php endif; ?>

            <!-- Nội dung tin nhắn -->
            <div class="chat-msg-content text-start">
                <?= nl2br(htmlspecialchars($msg['message'])) ?>
            </div>

            <!-- Tệp đính kèm -->
            <?php if ($hasAttachment): ?>
                <div class="mt-2 text-start">
                    <?php if ($isImage): ?>
                        <a href="<?= URL_ROOT ?>/public/uploads/chats/<?= htmlspecialchars($msg['attachment']) ?>" target="_blank">
                            <img src="<?= URL_ROOT ?>/public/uploads/chats/<?= htmlspecialchars($msg['attachment']) ?>" 
                                 class="img-fluid rounded border shadow-sm" style="max-height: 180px; object-fit: cover;" alt="Hình ảnh">
                        </a>
                    <?php elseif ($isVideo): ?>
                        <video controls class="w-100 rounded border mt-1" style="max-height: 200px;">
                            <source src="<?= URL_ROOT ?>/public/uploads/chats/<?= htmlspecialchars($msg['attachment']) ?>" type="video/mp4">
                            Trình duyệt của bạn không hỗ trợ phát video này.
                        </video>
                    <?php else: ?>
                        <!-- Các loại tệp tin khác (PDF, Excel, Word...) -->
                        <a href="<?= URL_ROOT ?>/public/uploads/chats/<?= htmlspecialchars($msg['attachment']) ?>" 
                           target="_blank" class="d-flex align-items-center gap-2 p-2 rounded bg-white text-dark text-decoration-none border shadow-sm small">
                            <i class="fa-solid <?= $attachIcon ?> fs-5"></i>
                            <span class="text-truncate flex-grow-1" style="max-width: 180px;"><?= htmlspecialchars($msg['attachment']) ?></span>
                            <i class="fa-solid fa-download text-muted"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <span class="chat-msg-meta"><?= $time ?></span>
        </div>
    </div>
<?php endif; ?>
