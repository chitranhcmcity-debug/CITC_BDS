/**
 * news-detail.js – Frontend logic cho Module Tin tức
 * Xử lý: Reading progress, TOC scroll-spy, Like AJAX,
 *         Share buttons, Comment CRUD AJAX, Search autocomplete.
 */
(function () {
    'use strict';

    /* ── Config từ PHP ── */
    const CFG = window.NEWS_CONFIG || {};

    /* ===================================================
       TOAST NOTIFICATION
       =================================================== */
    function showToast(message, type = 'success', duration = 3000) {
        let toast = document.getElementById('newsToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'newsToast';
            toast.className = 'news-toast';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.className = `news-toast ${type} show`;
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => {
            toast.className = `news-toast ${type}`;
        }, duration);
    }

    /* ===================================================
       READING PROGRESS BAR
       =================================================== */
    function initReadingProgress() {
        const bar = document.getElementById('readingProgress');
        if (!bar) return;

        const article = document.querySelector('.article-content');
        if (!article) return;

        window.addEventListener('scroll', () => {
            const rect   = article.getBoundingClientRect();
            const total  = article.offsetHeight;
            const scrolled = -rect.top;
            const pct = Math.max(0, Math.min(100, (scrolled / (total - window.innerHeight)) * 100));
            bar.style.width = pct + '%';
        }, { passive: true });
    }

    /* ===================================================
       TOC SCROLL SPY
       =================================================== */
    function initTOCScrollSpy() {
        const tocLinks = document.querySelectorAll('.toc-list a[href^="#"]');
        if (!tocLinks.length) return;

        const headings = [...document.querySelectorAll('.article-content h2, .article-content h3')];
        if (!headings.length) return;

        const OFFSET = 100;

        window.addEventListener('scroll', () => {
            const scrollY = window.scrollY + OFFSET;
            let active = null;

            headings.forEach(h => {
                if (h.offsetTop <= scrollY) active = h.id;
            });

            tocLinks.forEach(a => {
                a.classList.toggle('toc-active', a.getAttribute('href') === '#' + active);
            });
        }, { passive: true });

        // Smooth scroll on click
        tocLinks.forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                const id = a.getAttribute('href').slice(1);
                const el = document.getElementById(id);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    history.replaceState(null, '', '#' + id);
                }
            });
        });
    }

    /* ===================================================
       LIKE AJAX
       =================================================== */
    function initLikeButton() {
        const btn = document.getElementById('likeBtn');
        if (!btn) return;

        btn.addEventListener('click', async () => {
            if (!CFG.userId) {
                showToast('Bạn cần đăng nhập để thích bài viết.', 'error');
                return;
            }
            btn.disabled = true;
            try {
                const fd = new FormData();
                fd.append('news_id', CFG.newsId);
                fd.append('csrf_token', CFG.csrfToken);

                const res  = await fetch(CFG.likeUrl, { method: 'POST', body: fd });
                const data = await res.json();

                if (data.success) {
                    const icon  = btn.querySelector('.like-icon');
                    const count = btn.querySelector('.like-count');
                    btn.classList.toggle('liked', data.liked);
                    if (icon) icon.className = 'like-icon ' + (data.liked ? 'fas fa-heart' : 'far fa-heart');
                    if (count) count.textContent = data.count;
                    showToast(data.liked ? 'Đã thích bài viết!' : 'Đã bỏ thích.', 'success');
                } else {
                    showToast(data.message || 'Lỗi. Thử lại sau.', 'error');
                }
            } catch {
                showToast('Kết nối thất bại.', 'error');
            } finally {
                btn.disabled = false;
            }
        });
    }

    /* ===================================================
       SHARE BUTTONS
       =================================================== */
    function initShareButtons() {
        const url   = encodeURIComponent(location.href);
        const title = encodeURIComponent(document.title);

        const shareTargets = {
            'share-facebook':   `https://www.facebook.com/sharer/sharer.php?u=${url}`,
            'share-twitter':    `https://twitter.com/intent/tweet?url=${url}&text=${title}`,
            'share-telegram':   `https://t.me/share/url?url=${url}&text=${title}`,
            'share-linkedin':   `https://www.linkedin.com/sharing/share-offsite/?url=${url}`,
            'share-messenger':  `fb-messenger://share?link=${url}`,
            'share-zalo':       `https://zalo.me/share/url?url=${url}&title=${title}`,
        };

        Object.entries(shareTargets).forEach(([id, href]) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('click', () => {
                window.open(href, '_blank', 'width=600,height=400');
                recordShare();
            });
        });

        // Copy link
        const copyBtn = document.getElementById('share-copy');
        if (copyBtn) {
            copyBtn.addEventListener('click', () => {
                navigator.clipboard.writeText(location.href).then(() => {
                    showToast('Đã sao chép liên kết!', 'success');
                    recordShare();
                }).catch(() => {
                    showToast('Không thể sao chép.', 'error');
                });
            });
        }
    }

    async function recordShare() {
        if (!CFG.newsId) return;
        try {
            const fd = new FormData();
            fd.append('news_id', CFG.newsId);
            await fetch(CFG.shareUrl, { method: 'POST', body: fd });
        } catch { /* silent */ }
    }

    /* ===================================================
       COMMENT SYSTEM
       =================================================== */
    function initComments() {
        const form = document.getElementById('commentForm');
        if (!form) return;

        form.addEventListener('submit', async e => {
            e.preventDefault();
            await submitComment(form, null);
        });

        // Delegation cho reply, edit, delete
        document.addEventListener('click', async e => {
            // Reply button
            if (e.target.closest('.btn-reply')) {
                const btn     = e.target.closest('.btn-reply');
                const id      = btn.dataset.commentId;
                const name    = btn.dataset.authorName;
                toggleReplyForm(id, name);
            }

            // Submit reply
            if (e.target.closest('.btn-submit-reply')) {
                const btn     = e.target.closest('.btn-submit-reply');
                const chaId   = btn.dataset.chaId;
                const form    = document.getElementById('replyForm_' + chaId);
                if (form) await submitComment(form, chaId);
            }

            // Delete
            if (e.target.closest('.btn-delete-comment')) {
                const btn = e.target.closest('.btn-delete-comment');
                const id  = btn.dataset.commentId;
                if (confirm('Xóa bình luận này?')) {
                    await deleteComment(id, btn);
                }
            }
        });
    }

    function toggleReplyForm(commentId, authorName) {
        let wrap = document.getElementById('replyWrap_' + commentId);
        if (wrap) {
            wrap.remove();
            return;
        }
        const parent = document.getElementById('comment_' + commentId);
        if (!parent) return;

        const repliesContainer = parent.querySelector('.comment-replies') || parent;
        wrap = document.createElement('div');
        wrap.id = 'replyWrap_' + commentId;
        wrap.className = 'mt-2';
        wrap.innerHTML = `
          <div class="comment-item">
            <div class="flex-grow-1">
              <div class="comment-form-wrap p-3" style="background:#f8faff">
                <textarea class="form-control mb-2" rows="2" placeholder="Trả lời ${authorName}..." id="replyText_${commentId}"></textarea>
                <button class="btn btn-primary btn-sm rounded-pill btn-submit-reply"
                        data-cha-id="${commentId}">Gửi</button>
                <button class="btn btn-light btn-sm rounded-pill ms-2"
                        onclick="document.getElementById('replyWrap_${commentId}').remove()">Hủy</button>
              </div>
            </div>
          </div>`;

        repliesContainer.appendChild(wrap);
    }

    async function submitComment(form, chaId) {
        const textarea = chaId
            ? document.getElementById('replyText_' + chaId)
            : form.querySelector('[name="noi_dung"]');
        if (!textarea || !textarea.value.trim()) {
            showToast('Vui lòng nhập nội dung bình luận.', 'error');
            return;
        }

        const fd = new FormData();
        fd.append('news_id',    CFG.newsId);
        fd.append('csrf_token', CFG.csrfToken);
        fd.append('noi_dung',   textarea.value.trim());
        if (chaId) fd.append('cha_id', chaId);

        const submitBtn = form.querySelector('[type=submit]') ||
                          document.querySelector('.btn-submit-reply[data-cha-id="' + chaId + '"]');
        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Đang gửi...'; }

        try {
            const res  = await fetch(CFG.commentUrl, { method: 'POST', body: fd });
            const data = await res.json();

            if (data.success) {
                textarea.value = '';
                showToast('Bình luận đã được đăng!', 'success');
                // Thêm comment mới vào DOM
                renderNewComment(data.comment, chaId);
                // Update count
                const countEl = document.getElementById('commentCount');
                if (countEl) countEl.textContent = parseInt(countEl.textContent || '0') + 1;
                // Remove reply wrap
                if (chaId) {
                    const wrap = document.getElementById('replyWrap_' + chaId);
                    if (wrap) wrap.remove();
                }
            } else {
                showToast(data.message || 'Lỗi gửi bình luận.', 'error');
            }
        } catch {
            showToast('Kết nối thất bại.', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = chaId ? 'Gửi' : 'Đăng bình luận';
            }
        }
    }

    function renderNewComment(comment, chaId) {
        if (!comment) return;
        const avatarUrl = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(comment.ten_nguoi_dung || 'U') + '&background=0ea5e9&color=fff&size=80';
        const html = `
          <div class="comment-item" id="comment_${comment.id}">
            <img src="${avatarUrl}" class="comment-avatar" alt="">
            <div class="flex-grow-1">
              <div class="comment-bubble border-primary" style="border-color:rgba(37,99,235,.3)!important;background:rgba(37,99,235,.04)">
                <span class="comment-author">${escHtml(comment.ten_nguoi_dung || 'Bạn')}</span>
                <span class="comment-date">Vừa xong</span>
                <div class="comment-text">${escHtml(comment.noi_dung || '')}</div>
                <div class="comment-actions">
                  <span>Bạn</span>
                  <button class="btn-delete-comment" data-comment-id="${comment.id}">Xóa</button>
                </div>
              </div>
              <div class="comment-replies"></div>
            </div>
          </div>`;

        if (chaId) {
            const parentReplies = document.querySelector('#comment_' + chaId + ' .comment-replies');
            if (parentReplies) parentReplies.insertAdjacentHTML('beforeend', html);
        } else {
            const list = document.getElementById('commentList');
            if (list) list.insertAdjacentHTML('afterbegin', html);
        }
    }

    async function deleteComment(id, btn) {
        const fd = new FormData();
        fd.append('comment_id', id);
        fd.append('csrf_token',  CFG.csrfToken);

        try {
            const res  = await fetch(CFG.deleteCommentUrl, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                const item = document.getElementById('comment_' + id);
                if (item) {
                    const bubble = item.querySelector('.comment-bubble');
                    if (bubble) {
                        bubble.className = 'comment-bubble deleted';
                        bubble.querySelector('.comment-text').textContent = '[Đã xóa]';
                        const actions = bubble.querySelector('.comment-actions');
                        if (actions) actions.remove();
                    }
                }
                showToast('Đã xóa bình luận.', 'success');
            } else {
                showToast(data.message || 'Không thể xóa.', 'error');
            }
        } catch {
            showToast('Kết nối thất bại.', 'error');
        }
    }

    /* ===================================================
       SEARCH AUTOCOMPLETE
       =================================================== */
    function initSearchAutocomplete() {
        const input = document.getElementById('searchInput');
        const box   = document.getElementById('searchAutocomplete');
        if (!input || !box) return;

        let timer;
        input.addEventListener('input', () => {
            clearTimeout(timer);
            const q = input.value.trim();
            if (q.length < 2) { box.classList.remove('show'); return; }

            timer = setTimeout(async () => {
                try {
                    const res  = await fetch(`${CFG.siteRoot}/tin-tuc/search?q=${encodeURIComponent(q)}&ajax=1`);
                    const data = await res.json();
                    if (data.results && data.results.length) {
                        box.innerHTML = data.results.map(r => `
                          <div class="autocomplete-item" data-url="${escHtml(CFG.siteRoot + '/tin-tuc/detail/' + r.duong_dan)}">
                            ${r.anh_thu_nho ? `<img src="${escHtml(r.anh_thu_nho)}" style="width:36px;height:28px;object-fit:cover;border-radius:4px;">` : '<i class="fas fa-newspaper text-muted"></i>'}
                            <span>${escHtml(r.tieu_de)}</span>
                          </div>`).join('');
                        box.classList.add('show');
                    } else {
                        box.classList.remove('show');
                    }
                } catch { box.classList.remove('show'); }
            }, 300);
        });

        box.addEventListener('click', e => {
            const item = e.target.closest('.autocomplete-item');
            if (item) location.href = item.dataset.url;
        });

        document.addEventListener('click', e => {
            if (!input.contains(e.target) && !box.contains(e.target)) {
                box.classList.remove('show');
            }
        });
    }

    /* ===================================================
       UTILS
       =================================================== */
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* ===================================================
       INIT
       =================================================== */
    document.addEventListener('DOMContentLoaded', () => {
        initReadingProgress();
        initTOCScrollSpy();
        initLikeButton();
        initShareButtons();
        initComments();
        initSearchAutocomplete();
    });
})();
