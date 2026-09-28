<?php require_once '../app/views/admin/layouts/header.php'; ?>

<style>
.lc-admin-shell {
    display: grid;
    grid-template-columns: 360px minmax(0, 1fr);
    gap: 16px;
    min-height: 680px;
}
.lc-admin-sidebar,
.lc-admin-chat {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
}
.lc-admin-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 16px;
}
.lc-stat {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 13px 14px;
}
.lc-stat span {
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
}
.lc-stat strong {
    display: block;
    font-size: 1.45rem;
}
.lc-list-head,
.lc-chat-head {
    padding: 14px;
    border-bottom: 1px solid #e5e7eb;
    background: #f8fafc;
}
.lc-filter {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.lc-filter button {
    border: 1px solid #cbd5e1;
    background: #fff;
    border-radius: 999px;
    padding: 5px 10px;
    font-size: 0.82rem;
}
.lc-filter button.active {
    background: #0f766e;
    border-color: #0f766e;
    color: #fff;
}
.lc-conversations {
    overflow-y: auto;
    max-height: 590px;
}
.lc-conv {
    padding: 13px 14px;
    border-bottom: 1px solid #edf2f7;
    cursor: pointer;
}
.lc-conv:hover,
.lc-conv.active {
    background: #ecfeff;
}
.lc-conv-title {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    font-weight: 800;
}
.lc-conv-meta {
    color: #64748b;
    font-size: 0.78rem;
    margin-top: 3px;
}
.lc-badge {
    border-radius: 999px;
    padding: 2px 7px;
    font-size: 0.72rem;
    background: #e2e8f0;
}
.lc-badge.waiting { background: #fef3c7; color: #92400e; }
.lc-badge.open { background: #dcfce7; color: #166534; }
.lc-badge.closed { background: #e5e7eb; color: #374151; }
.lc-unread {
    background: #dc2626;
    color: #fff;
    border-radius: 999px;
    padding: 1px 7px;
    font-size: 0.72rem;
}
.lc-chat-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.lc-chat-actions {
    display: flex;
    gap: 8px;
}
.lc-messages {
    height: 520px;
    overflow-y: auto;
    padding: 16px;
    background: #f8fafc;
}
.lc-empty {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    text-align: center;
}
.lc-message {
    display: flex;
    margin-bottom: 10px;
}
.lc-message.mine {
    justify-content: flex-end;
}
.lc-bubble {
    max-width: 72%;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 13px;
    padding: 9px 11px;
}
.lc-message.mine .lc-bubble {
    background: #0f766e;
    color: #fff;
    border-color: #0f766e;
}
.lc-msg-meta {
    margin-top: 4px;
    font-size: 0.72rem;
    opacity: 0.68;
}
.lc-reply {
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 10px;
    padding: 12px;
}
.lc-reply input {
    flex: 1;
    border: 1px solid #cbd5e1;
    border-radius: 999px;
    padding: 10px 14px;
}
@media (max-width: 992px) {
    .lc-admin-shell,
    .lc-admin-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="lc-admin-stats">
    <div class="lc-stat"><span>Tổng chat</span><strong data-stat="total"><?= (int)$stats['total'] ?></strong></div>
    <div class="lc-stat"><span>Hôm nay</span><strong data-stat="today"><?= (int)$stats['today'] ?></strong></div>
    <div class="lc-stat"><span>Đang chờ</span><strong data-stat="waiting"><?= (int)$stats['waiting'] ?></strong></div>
    <div class="lc-stat"><span>Đang xử lý</span><strong data-stat="open"><?= (int)$stats['open'] ?></strong></div>
</div>

<div class="lc-admin-shell">
    <section class="lc-admin-sidebar">
        <div class="lc-list-head">
            <h5 class="fw-bold mb-3">Hội thoại</h5>
            <div class="lc-filter" data-chat-filters>
                <button type="button" class="active" data-status="all">Tất cả</button>
                <button type="button" data-status="waiting">Đang chờ</button>
                <button type="button" data-status="open">Đang xử lý</button>
                <button type="button" data-status="closed">Đã đóng</button>
            </div>
        </div>
        <div class="lc-conversations" data-conversation-list>
            <?php foreach ($conversations as $conversation): ?>
                <div class="lc-conv" data-conversation-id="<?= (int)$conversation->id ?>">
                    <div class="lc-conv-title">
                        <span><?= htmlspecialchars($conversation->customer_name ?: 'Khach #' . $conversation->id) ?></span>
                        <?php if ((int)$conversation->unread_admin > 0): ?>
                            <span class="lc-unread"><?= (int)$conversation->unread_admin ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="lc-conv-meta">
                        <span class="lc-badge <?= htmlspecialchars($conversation->status) ?>"><?= htmlspecialchars($conversation->status) ?></span>
                        <?= htmlspecialchars($conversation->last_message ?: 'Chua co tin nhan') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="lc-admin-chat">
        <div class="lc-chat-head">
            <div>
                <h5 class="fw-bold mb-1" data-active-title>Chọn một hội thoại</h5>
                <div class="text-muted small" data-active-meta>Live chat tự cập nhật mỗi 3 giây</div>
            </div>
            <div class="lc-chat-actions">
                <button type="button" class="btn btn-outline-success btn-sm" data-claim-chat disabled>Nhận chat</button>
                <button type="button" class="btn btn-outline-danger btn-sm" data-close-chat disabled>Đóng chat</button>
            </div>
        </div>
        <div class="lc-messages" data-admin-messages>
            <div class="lc-empty">Chọn hội thoại bên trái để bắt đầu trả lời.</div>
        </div>
        <form class="lc-reply" data-admin-reply>
            <input type="text" data-admin-input maxlength="2000" placeholder="Nhập phản hồi cho khách..." disabled>
            <button type="submit" class="btn btn-primary rounded-pill px-4" disabled>Gửi</button>
        </form>
    </section>
</div>

<script>
window.AdminLiveChatConfig = {
    baseUrl: '<?= URL_ROOT ?>/admin/live-chat',
    csrf: '<?= Csrf::token() ?>'
};
</script>
<script src="<?= URL_ROOT ?>/public/js/admin-live-chat.js?v=<?= filemtime(APP_ROOT . '/public/js/admin-live-chat.js') ?>"></script>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
