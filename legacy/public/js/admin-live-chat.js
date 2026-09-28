(function () {
    if (!window.AdminLiveChatConfig) return;

    const cfg = window.AdminLiveChatConfig;
    const listEl = document.querySelector('[data-conversation-list]');
    const msgEl = document.querySelector('[data-admin-messages]');
    const replyForm = document.querySelector('[data-admin-reply]');
    const input = document.querySelector('[data-admin-input]');
    const titleEl = document.querySelector('[data-active-title]');
    const metaEl = document.querySelector('[data-active-meta]');
    const claimBtn = document.querySelector('[data-claim-chat]');
    const closeBtn = document.querySelector('[data-close-chat]');
    const filterWrap = document.querySelector('[data-chat-filters]');

    let activeId = null;
    let lastMessageId = 0;
    let filter = 'all';
    let pollTimer = setInterval(refreshAll, 3000);

    function post(url, body = {}) {
        const data = new URLSearchParams();
        data.set('_csrf_token', cfg.csrf);
        Object.keys(body).forEach(key => data.set(key, body[key]));
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: data.toString()
        }).then(res => res.json());
    }

    function refreshAll() {
        refreshList();
        if (activeId) loadMessages(activeId, true);
    }

    function refreshList() {
        fetch(cfg.baseUrl + '/conversations?status=' + encodeURIComponent(filter))
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;
                updateStats(data.stats);
                renderList(data.conversations);
            })
            .catch(() => {});
    }

    function updateStats(stats) {
        Object.keys(stats || {}).forEach(key => {
            const el = document.querySelector('[data-stat="' + key + '"]');
            if (el) el.textContent = stats[key];
        });
    }

    function renderList(conversations) {
        if (!conversations.length) {
            listEl.innerHTML = '<div class="p-4 text-muted text-center">Chưa có hội thoại.</div>';
            return;
        }

        listEl.innerHTML = conversations.map(conv => `
            <div class="lc-conv ${Number(conv.id) === Number(activeId) ? 'active' : ''}" data-conversation-id="${conv.id}">
                <div class="lc-conv-title">
                    <span>${escapeHtml(conv.customer_name)}</span>
                    ${conv.unread_admin > 0 ? `<span class="lc-unread">${conv.unread_admin}</span>` : ''}
                </div>
                <div class="lc-conv-meta">
                    <span class="lc-badge ${conv.status}">${conv.status}</span>
                    ${conv.last_message}
                </div>
            </div>
        `).join('');
    }

    function loadMessages(id, appendOnly) {
        const after = appendOnly ? lastMessageId : 0;
        fetch(cfg.baseUrl + '/messages/' + id + '?after_id=' + after)
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;
                activeId = id;
                updateActive(data.conversation);
                if (!appendOnly) {
                    msgEl.innerHTML = '';
                    lastMessageId = 0;
                }
                data.messages.forEach(addMessage);
                if (!msgEl.children.length) {
                    msgEl.innerHTML = '<div class="lc-empty">Chưa có tin nhắn trong hội thoại này.</div>';
                }
                if (data.messages.length || !appendOnly) scrollBottom();
            })
            .catch(() => {});
    }

    function updateActive(conv) {
        if (!conv) return;
        titleEl.textContent = conv.customer_name;
        metaEl.textContent = [conv.customer_email, conv.customer_phone, conv.staff_name ? 'Phụ trách: ' + conv.staff_name : 'Chưa có nhân viên nhận'].filter(Boolean).join(' · ');
        input.disabled = conv.status === 'closed';
        replyForm.querySelector('button').disabled = conv.status === 'closed';
        claimBtn.disabled = conv.status === 'closed';
        closeBtn.disabled = conv.status === 'closed';
    }

    function addMessage(msg) {
        const empty = msgEl.querySelector('.lc-empty');
        if (empty) empty.remove();
        if (msg.id <= lastMessageId) return;
        lastMessageId = msg.id;

        const row = document.createElement('div');
        row.className = 'lc-message' + (msg.is_mine ? ' mine' : '');
        row.innerHTML = `
            <div class="lc-bubble">
                <div>${msg.message}</div>
                <div class="lc-msg-meta">${escapeHtml(msg.sender_name)} · ${msg.created_at}</div>
            </div>
        `;
        msgEl.appendChild(row);
    }

    function scrollBottom() {
        msgEl.scrollTop = msgEl.scrollHeight;
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[char]);
    }

    listEl.addEventListener('click', event => {
        const item = event.target.closest('[data-conversation-id]');
        if (!item) return;
        activeId = Number(item.dataset.conversationId);
        lastMessageId = 0;
        document.querySelectorAll('.lc-conv').forEach(el => el.classList.remove('active'));
        item.classList.add('active');
        loadMessages(activeId, false);
    });

    replyForm.addEventListener('submit', event => {
        event.preventDefault();
        if (!activeId) return;
        const text = input.value.trim();
        if (!text) return;
        input.value = '';
        post(cfg.baseUrl + '/send/' + activeId, { message: text }).then(data => {
            if (data.success) {
                loadMessages(activeId, true);
                refreshList();
            } else {
                alert(data.message || 'Không gửi được tin nhắn.');
                input.value = text;
            }
        });
    });

    claimBtn.addEventListener('click', () => {
        if (!activeId) return;
        post(cfg.baseUrl + '/claim/' + activeId).then(() => {
            loadMessages(activeId, false);
            refreshList();
        });
    });

    closeBtn.addEventListener('click', () => {
        if (!activeId || !confirm('Đóng cuộc trò chuyện này?')) return;
        post(cfg.baseUrl + '/close/' + activeId).then(() => {
            loadMessages(activeId, false);
            refreshList();
        });
    });

    filterWrap.addEventListener('click', event => {
        const btn = event.target.closest('[data-status]');
        if (!btn) return;
        filter = btn.dataset.status;
        filterWrap.querySelectorAll('button').forEach(item => item.classList.remove('active'));
        btn.classList.add('active');
        refreshList();
    });
})();
