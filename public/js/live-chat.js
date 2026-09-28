(function () {
    const root = document.querySelector('[data-live-chat-widget]');
    if (!root || !window.LiveChatConfig) return;

    const cfg = window.LiveChatConfig;
    const toggle = root.querySelector('[data-chat-toggle]');
    const closeBtn = root.querySelector('[data-chat-close]');
    const messagesEl = root.querySelector('[data-chat-messages]');
    const form = root.querySelector('[data-chat-form]');
    const input = root.querySelector('[data-chat-input]');
    const badge = root.querySelector('[data-chat-badge]');
    const subtitle = root.querySelector('[data-chat-subtitle]');

    let conversationId = null;
    let csrf = cfg.csrf || '';
    let lastMessageId = 0;
    let loaded = false;
    let pollTimer = null;
    let unread = 0;

    function setOpen(open) {
        root.classList.toggle('is-open', open);
        if (open) {
            unread = 0;
            updateBadge();
            bootstrap();
            input.focus();
            startPolling();
        } else {
            if (conversationId) {
                startPolling();
            } else {
                stopPolling();
            }
        }
    }

    function startPolling() {
        if (pollTimer) return;
        stopPolling();
        pollTimer = setInterval(poll, 3000);
    }

    function stopPolling() {
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = null;
    }

    function updateBadge() {
        if (!badge) return;
        badge.textContent = unread > 9 ? '9+' : String(unread);
        badge.style.display = unread > 0 ? 'flex' : 'none';
    }

    function setSubtitle(conversation) {
        if (!subtitle || !conversation) return;
        if (conversation.status === 'closed') {
            subtitle.textContent = 'Cuộc trò chuyện đã kết thúc';
        } else if (conversation.staff_name) {
            subtitle.textContent = conversation.staff_name + ' đang hỗ trợ bạn';
        } else {
            subtitle.textContent = 'Nhân viên sẽ phản hồi sớm';
        }
    }

    function bootstrap() {
        if (loaded) return;
        messagesEl.innerHTML = '<div class="live-chat-empty">Đang mở cuộc trò chuyện...</div>';
        fetch(cfg.bootstrapUrl, { credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Không thể mở chat.');
                loaded = true;
                conversationId = data.conversation.id;
                csrf = data.csrf || csrf;
                unread = 0;
                updateBadge();
                messagesEl.innerHTML = '';
                if (!data.messages.length) {
                    messagesEl.innerHTML = '<div class="live-chat-empty">Chào bạn, hãy gửi lời nhắn. Tư vấn viên sẽ phản hồi ngay khi có thể.</div>';
                } else {
                    data.messages.forEach(addMessage);
                }
                setSubtitle(data.conversation);
                scrollBottom();
            })
            .catch(() => {
                messagesEl.innerHTML = '<div class="live-chat-empty">Không thể mở live chat. Vui lòng thử lại sau.</div>';
            });
    }

    function loadStatus() {
        if (!cfg.statusUrl) return;
        fetch(cfg.statusUrl, { credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;
                csrf = data.csrf || csrf;
                if (!data.conversation) return;
                conversationId = data.conversation.id;
                unread = Number(data.conversation.unread_customer || 0);
                updateBadge();
                setSubtitle(data.conversation);
                startPolling();
            })
            .catch(() => {});
    }

    function poll() {
        if (!conversationId) return;
        const markRead = root.classList.contains('is-open') ? 1 : 0;
        fetch(cfg.pollUrl + '?conversation_id=' + conversationId + '&after_id=' + lastMessageId + '&mark_read=' + markRead, { credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;
                setSubtitle(data.conversation);
                const previousUnread = unread;
                data.messages.forEach(msg => {
                    addMessage(msg);
                });
                if (root.classList.contains('is-open')) {
                    unread = 0;
                } else if (data.conversation) {
                    unread = Number(data.conversation.unread_customer || 0);
                    if (unread > previousUnread) beep();
                }
                updateBadge();
                if (data.messages.length) scrollBottom();
            })
            .catch(() => {});
    }

    function send(message) {
        const body = new URLSearchParams();
        body.set('_csrf_token', csrf);
        body.set('message', message);

        return fetch(cfg.sendUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            credentials: 'same-origin',
            body: body.toString()
        }).then(res => res.json());
    }

    function addMessage(msg) {
        const empty = messagesEl.querySelector('.live-chat-empty');
        if (empty) empty.remove();

        if (msg.id <= lastMessageId) return;
        lastMessageId = msg.id;

        const item = document.createElement('div');
        item.className = 'live-chat-message' + (msg.is_mine ? ' is-mine' : '');
        item.innerHTML = `
            <div class="live-chat-bubble">
                <div>${msg.message}</div>
                <div class="live-chat-meta">${msg.sender_name} · ${msg.created_at}</div>
            </div>
        `;
        messagesEl.appendChild(item);
    }

    function scrollBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function beep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.frequency.value = 740;
            gain.gain.value = 0.04;
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            setTimeout(() => {
                osc.stop();
                ctx.close();
            }, 120);
        } catch (e) {}
    }

    root.addEventListener('click', event => {
        event.stopPropagation();
    });
    document.addEventListener('click', () => {
        if (root.classList.contains('is-open')) {
            setOpen(false);
        }
    });
    toggle.addEventListener('click', () => setOpen(true));
    closeBtn.addEventListener('click', () => setOpen(false));
    form.addEventListener('submit', event => {
        event.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        input.value = '';
        form.querySelector('button').disabled = true;
        send(text)
            .then(data => {
                if (data.success) {
                    poll();
                } else {
                    input.value = text;
                    alert(data.message || 'Không gửi được tin nhắn.');
                }
            })
            .finally(() => {
                form.querySelector('button').disabled = false;
                input.focus();
            });
    });
    loadStatus();
})();
