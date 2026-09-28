/**
 * author-profile.js
 * Module JS cho trang hồ sơ người đăng tin BĐS.
 *
 * Chức năng:
 *  1. Filter type tabs (Tất cả / Bán / Thuê) → submit form
 *  2. Sort select → auto submit
 *  3. Follow / Unfollow – AJAX + counter realtime
 *  4. Phone reveal – hiện số + ghi analytics
 *  5. Zalo click – ghi analytics
 *  6. Review modal – star picker + AJAX submit
 *  7. Copy profile link
 *  8. Toast notification
 */
(function () {
    'use strict';

    const CFG = window.AUTHOR_CONFIG || {};

    /* ======================================================
       1. TOAST NOTIFICATION
       ====================================================== */
    function showToast(msg, type = 'success') {
        const toast = document.getElementById('authorToast');
        if (!toast) return;
        toast.textContent = msg;
        toast.className = `author-toast ${type} show`;
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => toast.classList.remove('show'), 3500);
    }

    /* ======================================================
       2. FILTER TYPE TABS
       ====================================================== */
    document.querySelectorAll('[data-type]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('[data-type]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const typeInput = document.getElementById('typeInput');
            if (typeInput) typeInput.value = btn.dataset.type;
            const form = document.getElementById('filterForm');
            if (form) form.submit();
        });
    });

    /* ======================================================
       3. SORT AUTO SUBMIT
       ====================================================== */
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            document.getElementById('filterForm')?.submit();
        });
    }

    /* ======================================================
       4. FOLLOW / UNFOLLOW
       ====================================================== */
    const followBtn = document.getElementById('followBtn');
    if (followBtn) {
        followBtn.addEventListener('click', async () => {
            const isFollowing = followBtn.dataset.following === '1';
            const url         = isFollowing ? CFG.unfollowUrl : CFG.followUrl;
            const authorId    = followBtn.dataset.authorId;

            followBtn.disabled = true;

            try {
                const fd = new FormData();
                fd.append('author_id', authorId);
                fd.append('_token',    CFG.csrfToken);

                const res  = await fetch(url, { method: 'POST', body: fd });
                const data = await res.json();

                if (data.success) {
                    const nowFollowing = !isFollowing;
                    followBtn.dataset.following = nowFollowing ? '1' : '0';
                    followBtn.classList.toggle('following', nowFollowing);

                    const icon = followBtn.querySelector('i');
                    const txt  = followBtn.querySelector('span');
                    if (icon) icon.className = `fas ${nowFollowing ? 'fa-user-minus' : 'fa-user-plus'} me-2`;
                    if (txt)  txt.textContent = nowFollowing ? 'Đang theo dõi' : 'Theo dõi';

                    // Cập nhật counters
                    const count = Number(data.count ?? 0).toLocaleString('vi-VN');
                    document.getElementById('followerCount')?.innerText.replace(/./g, '');
                    ['followerCount', 'sideFollowerCount'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = count;
                    });

                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'error');
                }
            } catch (e) {
                showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
            } finally {
                followBtn.disabled = false;
            }
        });
    }

    /* ======================================================
       5. PHONE REVEAL
       ====================================================== */
    document.querySelectorAll('.phone-reveal-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const phone = btn.dataset.phone;
            const span  = btn.querySelector('.phone-text');

            if (btn.dataset.revealed) {
                // Gọi ngay
                window.location.href = 'tel:' + phone;
                return;
            }

            // Hiện số
            if (span) span.textContent = phone;
            btn.dataset.revealed = '1';
            btn.innerHTML = `<i class="fas fa-phone-volume me-2"></i><span class="phone-text">${phone}</span>`;

            // Thay onclick: lần sau gọi luôn
            btn.addEventListener('click', () => { window.location.href = 'tel:' + phone; }, { once: true });

            // Analytics
            fetch(CFG.siteRoot + '/analytics/view', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: 0, type: 'phone', author_id: btn.dataset.author }),
            }).catch(() => {});
        });
    });

    /* ======================================================
       6. ZALO CLICK ANALYTICS
       ====================================================== */
    document.querySelectorAll('[data-author]').forEach(el => {
        if (el.href?.includes('zalo.me')) {
            el.addEventListener('click', () => {
                fetch(CFG.siteRoot + '/analytics/view', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ type: 'zalo', author_id: el.dataset.author }),
                }).catch(() => {});
            });
        }
    });

    /* ======================================================
       7. STAR PICKER (REVIEW)
       ====================================================== */
    const starPicker = document.getElementById('starPicker');
    const reviewStar = document.getElementById('reviewStar');

    if (starPicker && reviewStar) {
        const stars = starPicker.querySelectorAll('i');

        function highlightStars(n) {
            stars.forEach((s, idx) => {
                s.className = idx < n ? 'fas fa-star' : 'far fa-star';
            });
        }

        stars.forEach((star, idx) => {
            star.addEventListener('mouseover', () => highlightStars(idx + 1));
            star.addEventListener('click', () => {
                reviewStar.value = idx + 1;
                highlightStars(idx + 1);
                document.getElementById('starError')?.classList.add('d-none');
            });
        });

        starPicker.addEventListener('mouseleave', () => {
            highlightStars(parseInt(reviewStar.value, 10) || 0);
        });
    }

    // Char count
    const reviewText = document.getElementById('reviewText');
    const charCount  = document.getElementById('reviewCharCount');
    if (reviewText && charCount) {
        reviewText.addEventListener('input', () => {
            charCount.textContent = reviewText.value.length;
        });
    }

    /* ======================================================
       8. SUBMIT REVIEW
       ====================================================== */
    const submitReviewBtn = document.getElementById('submitReviewBtn');
    if (submitReviewBtn) {
        submitReviewBtn.addEventListener('click', async () => {
            const so_sao   = parseInt(document.getElementById('reviewStar')?.value, 10) || 0;
            const nhan_xet = document.getElementById('reviewText')?.value.trim() || '';

            if (so_sao < 1 || so_sao > 5) {
                document.getElementById('starError')?.classList.remove('d-none');
                return;
            }

            submitReviewBtn.disabled = true;
            submitReviewBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang gửi...';

            try {
                const fd = new FormData();
                fd.append('author_id', submitReviewBtn.dataset.authorId);
                fd.append('so_sao',    so_sao);
                fd.append('nhan_xet',  nhan_xet);
                fd.append('_token',    CFG.csrfToken);

                const res  = await fetch(CFG.reviewUrl, { method: 'POST', body: fd });
                const data = await res.json();

                if (data.success) {
                    showToast(data.message, 'success');
                    document.getElementById('reviewForm')?.remove();
                    // Thêm review mới vào danh sách tạm thời (optimistic UI)
                    const reviewList = document.getElementById('reviewList');
                    if (reviewList) {
                        const card = document.createElement('div');
                        card.className = 'review-card';
                        const starsHtml = Array.from({length: 5}, (_, i) =>
                            `<i class="${i < so_sao ? 'fas' : 'far'} fa-star"></i>`).join('');
                        card.innerHTML = `
                            <div class="d-flex align-items-start gap-3">
                                <img src="https://ui-avatars.com/api/?size=40&background=e2e8f0&color=64748b" class="review-avatar" alt="">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <strong class="small">Bạn</strong>
                                        <small class="text-muted">Vừa xong</small>
                                    </div>
                                    <div class="stars-display my-1" style="font-size:0.75rem">${starsHtml}</div>
                                    ${nhan_xet ? `<p class="mb-0 small text-muted">${escapeHtml(nhan_xet)}</p>` : ''}
                                </div>
                            </div>`;
                        reviewList.prepend(card);
                    }
                } else {
                    showToast(data.message, 'error');
                    submitReviewBtn.disabled = false;
                    submitReviewBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Gửi đánh giá';
                }
            } catch (e) {
                showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
                submitReviewBtn.disabled = false;
                submitReviewBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Gửi đánh giá';
            }
        });
    }

    /* ======================================================
       9. COPY PROFILE LINK
       ====================================================== */
    document.getElementById('copyProfileLink')?.addEventListener('click', function () {
        const url = this.dataset.url;
        navigator.clipboard?.writeText(url).then(() => {
            showToast('Đã sao chép link hồ sơ!', 'success');
        }).catch(() => {
            const ta = document.createElement('textarea');
            ta.value = url;
            ta.style.opacity = '0'; ta.style.position = 'fixed';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            ta.remove();
            showToast('Đã sao chép link hồ sơ!', 'success');
        });
    });

    /* ======================================================
       HELPER: escapeHtml
       ====================================================== */
    function escapeHtml(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

})();
