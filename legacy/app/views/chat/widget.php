<!-- Floating Widget Live Chat - TimNhaDat.site -->
<div class="chat-widget-container" id="chatWidgetContainer">
    <!-- Nút tròn nổi lên màn hình -->
    <div onclick="toggleChatWidget()" class="chat-widget-button" id="chatWidgetButton">
        <i class="fa-solid fa-comments"></i>
        <!-- Badge đếm tin chưa đọc (sẽ tự động tăng qua SSE) -->
        <span class="chat-badge d-none" id="chatWidgetBadge">0</span>
    </div>

    <!-- Hộp chat chi tiết (Ẩn mặc định) -->
    <div class="chat-widget-box d-none" id="chatWidgetBox">
        <!-- Header -->
        <div class="chat-widget-header">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.9rem;">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <div>
                    <h6 class="mb-0 text-white fw-bold" style="font-size: 0.88rem;">Hỗ trợ TimNhaDat.site</h6>
                    <small class="text-white-50" style="font-size: 0.68rem;"><i class="fa-solid fa-circle text-success me-1"></i>Đang trực tuyến</small>
                </div>
            </div>
            <button onclick="toggleChatWidget()" class="btn-close btn-close-white" style="font-size: 0.75rem;"></button>
        </div>

        <!-- Khối tin nhắn -->
        <div class="chat-body-area flex-grow-1" id="chatWidgetMessageLog" style="overflow-y: auto;">
            <!-- Tin nhắn mẫu chào khách -->
            <div class="chat-msg-row other">
                <div class="chat-msg-bubble">
                    <span class="d-block fw-bold mb-1 text-primary" style="font-size: 0.72rem;">AI Assistant</span>
                    Chào bạn! Sàn bất động sản TimNhaDat.site xin chào. Bạn cần hỗ trợ tìm kiếm nhà đất hay ký gửi dự án? Hãy để lại lời nhắn nhé!
                </div>
            </div>
        </div>

        <!-- Footer nhập tin nhắn -->
        <div class="chat-footer-area py-2">
            <!-- Nút Upload File -->
            <input type="file" id="widgetFileUploadInput" class="d-none" onchange="uploadWidgetAttachment()">
            <button onclick="document.getElementById('widgetFileUploadInput').click()" class="chat-btn-icon" title="Tải ảnh / tệp">
                <i class="fa-solid fa-paperclip" style="font-size: 1rem;"></i>
            </button>

            <!-- Input và phím gửi -->
            <input type="text" id="widgetMessageInput" class="chat-input-field py-1.5" placeholder="Nhập tin nhắn..." onkeydown="handleWidgetInputKey(event)">
            
            <button onclick="sendWidgetMessage()" class="btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="fa-solid fa-paper-plane" style="font-size: 0.75rem;"></i>
            </button>
        </div>
    </div>
</div>

<script>
let widgetConvId = null;
let widgetSseSource = null;
let widgetUploadedFile = null;

// Bật/Tắt widget chat
function toggleChatWidget() {
    const box = document.getElementById('chatWidgetBox');
    const button = document.getElementById('chatWidgetButton');
    
    if (box.classList.contains('d-none')) {
        box.classList.remove('d-none');
        // Đánh dấu đã xem
        document.getElementById('chatWidgetBadge').classList.add('d-none');
        document.getElementById('chatWidgetBadge').innerText = '0';

        // Lấy hoặc khởi tạo cuộc hội thoại CSKH (Type: customer_cskh)
        bootstrapWidgetConversation();
    } else {
        box.classList.add('d-none');
    }
}

// Khởi tạo cuộc hội thoại phía Widget
function bootstrapWidgetConversation() {
    if (widgetConvId) return;

    fetch('<?= URL_ROOT ?>/chat/bootstrap?type=customer_cskh')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                widgetConvId = data.conversation.id;
                
                // Render lịch sử tin nhắn
                const log = document.getElementById('chatWidgetMessageLog');
                log.innerHTML = ''; // Clear mẫu
                
                data.messages.forEach(msg => {
                    appendWidgetMessage(msg);
                });
                
                scrollWidgetToBottom();
                setupWidgetSse();
            }
        })
        .catch(err => console.error(err));
}

// Gửi tin nhắn phía Widget
function sendWidgetMessage() {
    const input = document.getElementById('widgetMessageInput');
    const msg = input.value.trim();
    if (!msg && !widgetUploadedFile) return;

    const formData = new FormData();
    formData.append('conversation_id', widgetConvId);
    formData.append('message', msg);
    formData.append('message_type', 'text');
    formData.append('attachment', '');

    if (widgetUploadedFile) {
        formData.append('attachment', widgetUploadedFile);
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
            widgetUploadedFile = null;
        }
    })
    .catch(err => console.error(err));
}

// Upload file phía Widget
function uploadWidgetAttachment() {
    const fileInput = document.getElementById('widgetFileUploadInput');
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
            widgetUploadedFile = data.file_name;
            sendWidgetMessage();
        } else {
            alert(data.message || 'Tải file đính kèm thất bại.');
        }
    })
    .catch(err => console.error(err));
}

function handleWidgetInputKey(e) {
    if (e.key === 'Enter') {
        sendWidgetMessage();
    }
}

// Kết nối SSE Realtime cho Widget
function setupWidgetSse() {
    if (widgetSseSource) return;

    widgetSseSource = new EventSource('<?= URL_ROOT ?>/chat/stream');
    widgetSseSource.addEventListener('chat_message', function(e) {
        const msg = JSON.parse(e.data);
        if (parseInt(msg.conversation_id) === parseInt(widgetConvId)) {
            appendWidgetMessage(msg);
            scrollWidgetToBottom();

            // Nếu Widget đang thu nhỏ, tăng badge thông báo
            const box = document.getElementById('chatWidgetBox');
            if (box.classList.contains('d-none')) {
                const badge = document.getElementById('chatWidgetBadge');
                let count = parseInt(badge.innerText) || 0;
                count++;
                badge.innerText = count;
                badge.classList.remove('d-none');
            } else {
                // Đánh dấu đã xem
                const readForm = new FormData();
                readForm.append('conversation_id', widgetConvId);
                fetch('<?= URL_ROOT ?>/api/chat/read', { method: 'POST', body: readForm });
            }
        }
    });
}

function appendWidgetMessage(msg) {
    if (document.getElementById(`widget-row-${msg.id}`)) return;

    const log = document.getElementById('chatWidgetMessageLog');
    const isMine = msg.is_mine;
    const row = document.createElement('div');
    row.className = `chat-msg-row ${isMine ? 'mine' : 'other'}`;
    row.id = `widget-row-${msg.id}`;

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
            <div class="chat-msg-content text-start">${escapeHtml(msg.message)}</div>
            ${attachmentMarkup}
        </div>
    `;

    log.appendChild(row);
}

function scrollWidgetToBottom() {
    const log = document.getElementById('chatWidgetMessageLog');
    log.scrollTop = log.scrollHeight;
}
</script>
