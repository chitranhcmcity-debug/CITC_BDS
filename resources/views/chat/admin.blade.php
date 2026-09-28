<?php @include('admin.layouts.header') ?>
<link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/chat.css?v=<?= filemtime(APP_ROOT . '/public/css/chat.css') ?>">

<div class="content-wrapper p-3 bg-light">
    <!-- Tiêu đề trang -->
    <section class="content-header mb-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h1 class="h3 fw-bold text-dark mb-1"><i class="fa-solid fa-headset text-primary me-2"></i>Quản lý Live Chat hỗ trợ</h1>
                    <p class="text-muted mb-0 small">Tiếp quản, điều phối hội thoại hỗ trợ khách hàng thời gian thực.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Quản lý hội thoại -->
    <section class="content">
        <div class="row g-4">
            <!-- Danh sách hội thoại ở bên trái -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white pt-3 border-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-list-check text-success me-2"></i>Danh sách hội thoại</h5>
                        
                        <!-- Bộ lọc Trạng thái -->
                        <div class="d-flex gap-2">
                            <a href="?status=waiting" class="btn btn-xs btn-outline-warning py-1 px-2.5 rounded-pill fw-semibold <?= $status === 'waiting' ? 'active' : '' ?>">Chờ hỗ trợ</a>
                            <a href="?status=open" class="btn btn-xs btn-outline-primary py-1 px-2.5 rounded-pill fw-semibold <?= $status === 'open' ? 'active' : '' ?>">Đang chat</a>
                            <a href="?status=closed" class="btn btn-xs btn-outline-secondary py-1 px-2.5 rounded-pill fw-semibold <?= $status === 'closed' ? 'active' : '' ?>">Đã đóng</a>
                            <a href="?status=all" class="btn btn-xs btn-outline-dark py-1 px-2.5 rounded-pill fw-semibold <?= $status === 'all' ? 'active' : '' ?>">Tất cả</a>
                        </div>
                    </div>
                    
                    <div class="card-body p-0 mt-2">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                                <thead class="table-light text-secondary small fw-bold">
                                    <tr>
                                        <th class="ps-3" style="width: 60px;">ID</th>
                                        <th>Loại / Đối tượng</th>
                                        <th>Tin cuối cùng</th>
                                        <th>Nhân viên gán</th>
                                        <th class="text-end pe-3" style="width: 150px;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($conversations)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted small">
                                                Không tìm thấy hội thoại nào phù hợp bộ lọc.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($conversations as $c): ?>
                                            <tr class="<?= $c->is_pinned ? 'table-warning' : '' ?>" style="cursor: pointer;" onclick="loadAdminChatLog(<?= $c->id ?>)">
                                                <td class="ps-3 fw-bold">
                                                    #<?= $c->id ?>
                                                    <?php if ($c->is_pinned): ?>
                                                        <i class="fa-solid fa-thumbtack text-danger ms-1" title="Đã ghim"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong class="d-block text-dark">
                                                        <?php
                                                        if ($c->type === 'customer_seller') echo 'Khách ↔ Người bán';
                                                        else echo 'Khách ↔ CSKH';
                                                        ?>
                                                    </strong>
                                                    <span class="text-muted small"><?= htmlspecialchars($c->customer_name ?: 'Khách vãng lai') ?></span>
                                                </td>
                                                <td>
                                                    <span class="text-muted text-truncate d-block small" style="max-width: 180px;">
                                                        <?= htmlspecialchars($c->title ?: 'Đang chờ tin nhắn đầu...') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($c->staff_name): ?>
                                                        <span class="badge bg-success"><?= htmlspecialchars($c->staff_name) ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Chưa gán</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end pe-3" onclick="event.stopPropagation()">
                                                    <div class="d-flex gap-1.5 justify-content-end">
                                                        <!-- Nút ghim -->
                                                        <form action="<?= URL_ROOT ?>/admin/livechat/pin/<?= $c->id ?>" method="POST" class="d-inline">
                                                            <?= Csrf::field() ?>
                                                            <button type="submit" class="btn btn-xs btn-outline-secondary p-1 rounded" title="Ghim / Bỏ ghim">
                                                                <i class="fa-solid fa-thumbtack"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <!-- Nhận hỗ trợ -->
                                                        <?php if ($c->status !== 'closed' && (int)$c->staff_id !== (int)Session::get('user_id')): ?>
                                                            <form action="<?= URL_ROOT ?>/admin/livechat/claim/<?= $c->id ?>" method="POST" class="d-inline">
                                                                <?= Csrf::field() ?>
                                                                <button type="submit" class="btn btn-xs btn-outline-primary p-1 rounded fw-bold" style="font-size: 0.72rem;">
                                                                    Tiếp nhận
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>

                                                        <!-- Modal Chuyển giao -->
                                                        <?php if ($c->status !== 'closed'): ?>
                                                            <button onclick="openTransferModal(<?= $c->id ?>)" class="btn btn-xs btn-outline-warning p-1 rounded" title="Chuyển CSKH">
                                                                <i class="fa-solid fa-arrows-spin"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Khung trò chuyện CSKH chi tiết ở bên phải -->
            <div class="col-lg-5">
                <div id="adminChatEmptyState" class="card border-0 shadow-sm rounded-3 text-center py-5 d-flex align-items-center justify-content-center" style="min-height: 400px;">
                    <div class="fs-1 text-muted opacity-50 mb-3"><i class="fa-solid fa-headset"></i></div>
                    <h6 class="text-secondary fw-semibold">Chọn một cuộc hội thoại bên trái để tiếp quản chat.</h6>
                </div>

                <div id="adminChatBox" class="card border-0 shadow-sm rounded-3 d-none" style="height: 520px; display: flex; flex-direction: column; overflow: hidden;">
                    <!-- Header -->
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-2.5">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" id="adminActivePartnerName">Khách hàng</h6>
                            <span class="text-muted small" style="font-size: 0.75rem;" id="adminActiveConvId">Hội thoại #0</span>
                        </div>
                        <div class="d-flex gap-1.5">
                            <a href="#" id="adminExportHistoryBtn" class="btn btn-xs btn-outline-secondary py-1 px-2.5 rounded-pill fw-bold" style="font-size: 0.72rem;">
                                <i class="fa-solid fa-download"></i> Xuất lịch sử
                            </a>
                            <button onclick="closeAdminActiveChat()" class="btn btn-xs btn-danger py-1 px-2.5 rounded-pill fw-bold" style="font-size: 0.72rem;">
                                Đóng
                            </button>
                        </div>
                    </div>

                    <!-- Tin nhắn -->
                    <div class="chat-body-area flex-grow-1" id="adminChatLog">
                        <!-- Tin nhắn sẽ tự động render bằng AJAX / SSE -->
                    </div>

                    <!-- Footer nhập tin nhắn -->
                    <div class="chat-footer-area">
                        <!-- Gửi tệp đính kèm -->
                        <input type="file" id="adminFileUploadInput" class="d-none" onchange="uploadAdminAttachment()">
                        <button onclick="document.getElementById('adminFileUploadInput').click()" class="chat-btn-icon" title="Đính kèm tệp">
                            <i class="fa-solid fa-paperclip"></i>
                        </button>

                        <input type="text" id="adminMessageInput" class="chat-input-field" placeholder="Nhập tin phản hồi hỗ trợ..." onkeydown="handleAdminInputKey(event)">
                        
                        <button onclick="sendAdminMessage()" class="btn btn-success btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-paper-plane" style="font-size: 0.88rem;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Chuyển nhượng CSKH -->
<div class="modal fade" id="transferChatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form action="<?= URL_ROOT ?>/admin/livechat/transfer" method="POST">
            <?= Csrf::field() ?>
            <input type="hidden" name="conversation_id" id="transferConvIdField">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Chuyển nhượng cuộc hỗ trợ</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label small fw-semibold text-secondary mb-1">Chọn nhân viên CSKH</label>
                    <select name="staff_id" class="form-select form-select-sm" required>
                        <?php foreach ($staffMembers as $staff): ?>
                            <?php if ((int)$staff->id !== (int)Session::get('user_id')): ?>
                                <option value="<?= $staff->id ?>"><?= htmlspecialchars($staff->ten) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold">Xác nhận chuyển</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let adminConvId = null;
let adminSse = null;
let adminUploadedFile = null;

// Tải lịch sử chat phía Admin
function loadAdminChatLog(id) {
    adminConvId = id;
    
    document.getElementById('adminChatEmptyState').classList.add('d-none');
    document.getElementById('adminChatBox').classList.remove('d-none');

    const log = document.getElementById('adminChatLog');
    log.innerHTML = `<div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin text-success"></i> Đang tải...</div>`;

    document.getElementById('adminActiveConvId').innerText = 'Hội thoại #' + id;
    document.getElementById('adminExportHistoryBtn').href = `<?= URL_ROOT ?>/admin/livechat/export/${id}`;

    fetch(`<?= URL_ROOT ?>/api/chat/history?conversation_id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                log.innerHTML = '';
                data.messages.forEach(msg => {
                    appendAdminMessage(msg);
                });
                scrollAdminToBottom();
                setupAdminSse();
            }
        })
        .catch(err => console.error(err));
}

// Gửi tin nhắn phản hồi hỗ trợ
function sendAdminMessage() {
    const input = document.getElementById('adminMessageInput');
    const msg = input.value.trim();
    if (!msg && !adminUploadedFile) return;

    const formData = new FormData();
    formData.append('conversation_id', adminConvId);
    formData.append('message', msg);
    formData.append('message_type', 'text');
    formData.append('attachment', '');

    if (adminUploadedFile) {
        formData.append('attachment', adminUploadedFile);
        formData.append('message_type', 'file');
    }

    fetch('<?= URL_ROOT ?>/api/chat/send', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            adminUploadedFile = null;
        }
    })
    .catch(err => console.error(err));
}

// Upload file đính kèm phía Admin
function uploadAdminAttachment() {
    const fileInput = document.getElementById('adminFileUploadInput');
    if (!fileInput.files.length) return;

    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('file', file);

    fetch('<?= URL_ROOT ?>/api/chat/upload', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            adminUploadedFile = data.file_name;
            sendAdminMessage();
        } else {
            alert(data.message || 'Tải file đính kèm thất bại.');
        }
    })
    .catch(err => console.error(err));
}

function handleAdminInputKey(e) {
    if (e.key === 'Enter') {
        sendAdminMessage();
    }
}

// Đóng cuộc trò chuyện đang chọn
function closeAdminActiveChat() {
    if (!confirm('Bạn có chắc chắn muốn đóng cuộc hội thoại hỗ trợ này không?')) return;

    const formData = new FormData();
    formData.append('conversation_id', adminConvId);

    fetch('<?= URL_ROOT ?>/api/chat/close', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(err => console.error(err));
}

// Chuyển CSKH
function openTransferModal(id) {
    document.getElementById('transferConvIdField').value = id;
    const modal = new bootstrap.Modal(document.getElementById('transferChatModal'));
    modal.show();
}

// Lắng nghe realtime SSE phía Admin
function setupAdminSse() {
    if (adminSse) return;

    adminSse = new EventSource('<?= URL_ROOT ?>/chat/stream');
    adminSse.addEventListener('chat_message', function(e) {
        const msg = JSON.parse(e.data);
        if (parseInt(msg.conversation_id) === parseInt(adminConvId)) {
            appendAdminMessage(msg);
            scrollAdminToBottom();

            // Đánh dấu đã xem
            const readForm = new FormData();
            readForm.append('conversation_id', adminConvId);
            fetch('<?= URL_ROOT ?>/api/chat/read', { method: 'POST', body: readForm });
        }
    });
}

function appendAdminMessage(msg) {
    if (document.getElementById(`admin-row-${msg.id}`)) return;

    const log = document.getElementById('adminChatLog');
    const isMine = msg.is_mine || parseInt(msg.sender_id) === parseInt(<?= (int)Session::get('user_id') ?>);
    const row = document.createElement('div');
    row.className = `chat-msg-row ${isMine ? 'mine' : 'other'}`;
    row.id = `admin-row-${msg.id}`;

    let attachmentMarkup = '';
    if (msg.attachment) {
        const ext = msg.attachment.split('.').pop().toLowerCase();
        const path = `<?= URL_ROOT ?>/public/uploads/chats/${msg.attachment}`;
        if (['png', 'jpg', 'jpeg', 'webp', 'gif'].includes(ext)) {
            attachmentMarkup = `<div class="mt-2"><a href="${path}" target="_blank"><img src="${path}" class="img-fluid rounded border" style="max-height: 120px; object-fit: cover;"></a></div>`;
        } else {
            attachmentMarkup = `<div class="mt-2"><a href="${path}" target="_blank" class="d-flex align-items-center gap-1.5 p-1 rounded bg-white text-dark text-decoration-none border shadow-sm small"><i class="fa-solid fa-file text-secondary"></i><span class="text-truncate" style="max-width: 130px;">Tải file</span></a></div>`;
        }
    }

    row.innerHTML = `
        <div class="chat-msg-bubble">
            ${!isMine ? `<span class="d-block fw-bold mb-1 text-primary" style="font-size: 0.72rem;">${msg.sender_name}</span>` : ''}
            <div class="chat-msg-content text-start">${escapeHtml(msg.message)}</div>
            ${attachmentMarkup}
        </div>
    `;

    log.appendChild(row);
}

function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function scrollAdminToBottom() {
    const log = document.getElementById('adminChatLog');
    log.scrollTop = log.scrollHeight;
}
</script>

<?php @include('admin.layouts.footer') ?>
