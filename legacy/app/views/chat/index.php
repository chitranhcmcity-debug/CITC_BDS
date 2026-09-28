<?php require_once '../app/views/layouts/header.php'; ?>
<link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/chat.css?v=<?= filemtime(APP_ROOT . '/public/css/chat.css') ?>">

<div class="container py-4">
    <div class="row">
        <!-- Sidebar thành viên -->
        <?php require '../app/views/nguoi-dung/sidebar.php'; ?>

        <!-- Nội dung chat chính -->
        <main class="col-lg-9 col-md-8 col-12">
            <div class="row g-3">
                <!-- Danh sách hội thoại ở bên trái -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white pt-3 border-0">
                            <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-message text-primary me-2"></i>Trò chuyện</h5>
                        </div>
                        <div class="chat-sidebar-list p-0" style="max-height: 480px; overflow-y: auto;">
                            <?php if (empty($conversations)): ?>
                                <div class="text-center py-5 text-muted small">
                                    Chưa có cuộc trò chuyện nào.
                                </div>
                            <?php else: ?>
                                <?php foreach ($conversations as $conv): ?>
                                    <?php require '../app/views/chat/components/conversation.php'; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Khung tin nhắn chính -->
                <div class="col-md-7">
                    <!-- Trạng thái trống khi chưa chọn hội thoại -->
                    <div id="chatEmptyState" class="card border-0 shadow-sm rounded-3 text-center py-5 d-flex align-items-center justify-content-center" style="min-height: 400px;">
                        <div class="fs-1 text-muted opacity-50 mb-3"><i class="fa-regular fa-comments"></i></div>
                        <h6 class="text-secondary fw-semibold">Chọn một cuộc hội thoại từ danh sách để bắt đầu trò chuyện.</h6>
                    </div>

                    <!-- Hộp chat chi tiết -->
                    <div id="chatActiveBox" class="card border-0 shadow-sm rounded-3 d-none" style="height: 520px; overflow: hidden; display: flex; flex-direction: column;">
                        <!-- Header hiển thị thông tin đối tác -->
                        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-2.5">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark" id="chatPartnerName">CSKH Hỗ trợ</h6>
                                <span class="text-muted small" style="font-size: 0.75rem;" id="chatConversationTitle">Hỗ trợ trực tuyến</span>
                            </div>
                            <button onclick="closeCurrentChat()" class="btn btn-sm btn-outline-danger px-2 rounded-pill fw-bold" style="font-size: 0.72rem;">
                                <i class="fa-solid fa-xmark me-1"></i> Đóng
                            </button>
                        </div>

                        <!-- Khối chứa tin nhắn -->
                        <div class="chat-body-area flex-grow-1" id="chatMessageLog">
                            <!-- Tin nhắn load tự động qua JS -->
                        </div>

                        <!-- Form gửi tin nhắn -->
                        <div class="chat-footer-area">
                            <!-- Nút upload tệp đính kèm -->
                            <input type="file" id="chatFileUploadInput" class="d-none" onchange="uploadChatAttachment()">
                            <button onclick="document.getElementById('chatFileUploadInput').click()" class="chat-btn-icon" title="Tải ảnh / tệp">
                                <i class="fa-solid fa-paperclip"></i>
                            </button>

                            <!-- Nhập tin nhắn -->
                            <input type="text" id="chatMessageInput" class="chat-input-field" placeholder="Nhập tin nhắn..." onkeydown="handleChatInputKey(event)">
                            
                            <!-- Nút gửi -->
                            <button onclick="sendChatMessage()" class="btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="fa-solid fa-paper-plane" style="font-size: 0.88rem;"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Scripts tương tác AJAX & SSE -->
<script>
let currentConvId = null;
let currentAfterId = 0;
let sseSource = null;

// Chọn hội thoại từ danh sách bên trái
function selectConversation(id) {
    currentConvId = id;
    
    // Cập nhật trạng thái active trong sidebar list
    document.querySelectorAll('.chat-sidebar-item').forEach(item => item.classList.remove('active'));
    const selectedItem = document.getElementById(`conv-item-${id}`);
    if (selectedItem) selectedItem.classList.add('active');

    // Ẩn Trạng thái trống, hiện hộp chat
    document.getElementById('chatEmptyState').classList.add('d-none');
    document.getElementById('chatActiveBox').classList.remove('d-none');

    // Reset lịch sử tin nhắn
    const log = document.getElementById('chatMessageLog');
    log.innerHTML = `<div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin text-primary"></i> Đang tải hội thoại...</div>`;

    // Gọi API lấy lịch sử tin nhắn của cuộc trò chuyện
    fetch(`<?= URL_ROOT ?>/api/chat/history?conversation_id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                log.innerHTML = '';
                currentAfterId = 0;
                
                // Tiêu đề
                const firstMsg = data.messages[0];
                document.getElementById('chatPartnerName').innerText = selectedItem.querySelector('h6').innerText;
                document.getElementById('chatConversationTitle').innerText = 'Hội thoại #' + id;

                // Render tin nhắn
                data.messages.forEach(msg => {
                    appendMessageToLog(msg);
                });

                scrollToBottom();
                setupSseRealtime();
            }
        })
        .catch(err => console.error(err));
}

// Xử lý gửi tin nhắn
function sendChatMessage() {
    const input = document.getElementById('chatMessageInput');
    const msg = input.value.trim();
    if (!msg && !uploadedFile) return;

    const formData = new FormData();
    formData.append('conversation_id', currentConvId);
    formData.append('message', msg);
    formData.append('message_type', 'text');
    formData.append('attachment', '');

    // Nếu có file upload sẵn
    if (uploadedFile) {
        formData.append('attachment', uploadedFile);
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
            uploadedFile = null;
            // Tin nhắn sẽ tự động render khi nhận được qua SSE realtime
        }
    })
    .catch(err => console.error(err));
}

// Upload file đính kèm
let uploadedFile = null;
function uploadChatAttachment() {
    const fileInput = document.getElementById('chatFileUploadInput');
    if (!fileInput.files.length) return;

    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('file', file);

    // Gửi AJAX tải file
    fetch('<?= URL_ROOT ?>/api/chat/upload', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            uploadedFile = data.file_name;
            // Tự động chèn tin nhắn chứa tệp vừa upload lên
            sendChatMessage();
        } else {
            alert(data.message || 'Tải tệp lên thất bại.');
        }
    })
    .catch(err => console.error(err));
}

// Đóng cuộc trò chuyện
function closeCurrentChat() {
    if (!confirm('Bạn có muốn đóng cuộc trò chuyện hỗ trợ này không?')) return;

    const formData = new FormData();
    formData.append('conversation_id', currentConvId);

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

// Phím Enter gửi tin nhắn
function handleChatInputKey(event) {
    if (event.key === 'Enter') {
        sendChatMessage();
    }
}

// Thiết lập kết nối SSE Realtime Chat
function setupSseRealtime() {
    if (sseSource) {
        sseSource.close();
    }

    sseSource = new EventSource('<?= URL_ROOT ?>/chat/stream');
    sseSource.addEventListener('chat_message', function(e) {
        const msg = JSON.parse(e.data);
        if (parseInt(msg.conversation_id) === parseInt(currentConvId)) {
            appendMessageToLog(msg);
            scrollToBottom();
            
            // Đánh dấu đã xem
            const readForm = new FormData();
            readForm.append('conversation_id', currentConvId);
            fetch('<?= URL_ROOT ?>/api/chat/read', { method: 'POST', body: readForm });
        }
    });
}

function appendMessageToLog(msg) {
    // Tránh trùng lặp tin nhắn đã load
    if (document.getElementById(`chat-row-${msg.id}`)) return;

    const log = document.getElementById('chatMessageLog');
    const isMine = msg.is_mine || parseInt(msg.sender_id) === parseInt(<?= (int)Session::get('user_id') ?>);
    const row = document.createElement('div');
    row.className = `chat-msg-row ${isMine ? 'mine' : 'other'}`;
    row.id = `chat-row-${msg.id}`;

    let attachmentMarkup = '';
    if (msg.attachment) {
        const ext = msg.attachment.split('.').pop().toLowerCase();
        const path = `<?= URL_ROOT ?>/public/uploads/chats/${msg.attachment}`;
        if (['png', 'jpg', 'jpeg', 'webp', 'gif'].includes(ext)) {
            attachmentMarkup = `<div class="mt-2"><a href="${path}" target="_blank"><img src="${path}" class="img-fluid rounded border" style="max-height: 150px; object-fit: cover;"></a></div>`;
        } else if (ext === 'mp4') {
            attachmentMarkup = `<div class="mt-2"><video controls class="w-100 rounded border" style="max-height: 160px;"><source src="${path}" type="video/mp4"></video></div>`;
        } else {
            attachmentMarkup = `<div class="mt-2"><a href="${path}" target="_blank" class="d-flex align-items-center gap-2 p-1.5 rounded bg-white text-dark text-decoration-none border shadow-sm small"><i class="fa-solid fa-file-pdf text-danger fs-5"></i><span class="text-truncate" style="max-width: 150px;">${msg.attachment}</span></a></div>`;
        }
    }

    row.innerHTML = `
        <div class="chat-msg-bubble">
            ${!isMine ? `<span class="d-block fw-bold mb-1 text-primary" style="font-size: 0.72rem;">${msg.sender_name}</span>` : ''}
            <div class="chat-msg-content text-start">${escapeHtml(msg.message)}</div>
            ${attachmentMarkup}
            <span class="chat-msg-meta text-end">${msg.created_at || 'Vừa xong'}</span>
        </div>
    `;

    log.appendChild(row);
    currentAfterId = Math.max(currentAfterId, msg.id);
}

function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function scrollToBottom() {
    const log = document.getElementById('chatMessageLog');
    log.scrollTop = log.scrollHeight;
}
</script>
<?php require_once '../app/views/layouts/footer.php'; ?>
