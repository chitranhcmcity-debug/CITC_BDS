/**
 * property-detail.js – Module JavaScript cho trang chi tiết BĐS
 *
 * Chức năng:
 *  - Gallery: SwiperJS + LightGallery (zoom, fullscreen, count)
 *  - Google Maps: Marker, Street View toggle
 *  - Mô tả: Xem thêm / Thu gọn
 *  - Báo cáo vi phạm: Modal + AJAX submit
 *  - Chia sẻ: Track analytics + mở popup
 *  - Phone reveal: Ẩn số, hiện khi click + ghi analytics
 *  - So sánh BĐS: Toggle local storage
 *  - AOS: Khởi tạo animation on scroll
 */

'use strict';

// ============================================================
// SWIPER GALLERY
// ============================================================
function initSwiperGallery() {
    const mainEl = document.getElementById('swiperMain');
    const thumbEl = document.getElementById('swiperThumbs');
    if (!mainEl) return;

    // Thumbnails swiper
    const thumbSwiper = thumbEl ? new Swiper(thumbEl, {
        spaceBetween: 8,
        slidesPerView: 'auto',
        freeMode: true,
        watchSlidesProgress: true,
    }) : null;

    // Main gallery swiper
    const mainSwiper = new Swiper(mainEl, {
        spaceBetween: 0,
        loop: true,
        lazy: { loadPrevNext: true },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination',
            type: 'fraction',
        },
        thumbs: thumbSwiper ? { swiper: thumbSwiper } : undefined,
        keyboard: { enabled: true },
        a11y: {
            prevSlideMessage: 'Ảnh trước',
            nextSlideMessage: 'Ảnh sau',
        },
    });

    return mainSwiper;
}

// ============================================================
// LIGHTGALLERY – Zoom & Fullscreen
// ============================================================
function initLightGallery() {
    const container = document.getElementById('galleryLightbox');
    if (!container || typeof lightGallery === 'undefined') return;

    lightGallery(container, {
        selector: '.lg-item',
        speed: 400,
        thumbnail: true,
        animateThumb: true,
        showZoomInOutIcons: true,
        actualSize: false,
        share: false,
        download: false,
        pager: false,
        counter: true,
        mobileSettings: {
            controls: true,
            showCloseIcon: true,
            download: false,
        },
    });
}

// ============================================================
// GOOGLE MAPS
// ============================================================
function initGoogleMap() {
    const mapEl = document.getElementById('propertyMap');
    if (!mapEl) return;

    const lat = parseFloat(mapEl.dataset.lat);
    const lng = parseFloat(mapEl.dataset.lng);
    if (isNaN(lat) || isNaN(lng)) {
        mapEl.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted"><i class="fa-solid fa-map-location-dot fa-2x me-2"></i>Chưa có tọa độ bản đồ.</div>';
        return;
    }

    const position = { lat, lng };

    // Custom map style (dark-friendly minimal)
    const mapStyles = [
        { featureType: 'poi', elementType: 'labels', stylers: [{ visibility: 'off' }] },
    ];

    const map = new google.maps.Map(mapEl, {
        zoom: 15,
        center: position,
        styles: mapStyles,
        mapTypeControl: false,
        streetViewControl: true,
        fullscreenControl: true,
    });

    // Custom marker
    const marker = new google.maps.Marker({
        position,
        map,
        title: mapEl.dataset.title || 'Bất động sản',
        animation: google.maps.Animation.DROP,
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
            scaledSize: new google.maps.Size(40, 40),
        },
    });

    // Info window
    const infoWindow = new google.maps.InfoWindow({
        content: `<div style="max-width:220px;font-size:0.9rem;font-weight:600">${mapEl.dataset.title || ''}</div>
                  <div style="font-size:0.8rem;color:#666">${mapEl.dataset.address || ''}</div>`,
    });

    marker.addListener('click', () => infoWindow.open(map, marker));
    infoWindow.open(map, marker);

    // Street View toggle
    const svBtn = document.getElementById('btnStreetView');
    if (svBtn) {
        svBtn.addEventListener('click', () => {
            const sv = new google.maps.StreetViewPanorama(mapEl, {
                position,
                pov: { heading: 165, pitch: 0 },
                zoom: 1,
            });
            map.setStreetView(sv);
        });
    }
}

// Expose globally so Google Maps callback can call it
window.initGoogleMap = initGoogleMap;

// ============================================================
// MÔ TẢ – XEM THÊM / THU GỌN
// ============================================================
function initDescToggle() {
    const descEl  = document.getElementById('descContent');
    const btnMore = document.getElementById('btnDescMore');
    const btnLess = document.getElementById('btnDescLess');
    if (!descEl || !btnMore) return;

    const COLLAPSED_HEIGHT = 160; // px
    const fullHeight = descEl.scrollHeight;

    if (fullHeight <= COLLAPSED_HEIGHT) {
        if (btnMore) btnMore.style.display = 'none';
        return;
    }

    descEl.style.maxHeight = COLLAPSED_HEIGHT + 'px';
    descEl.style.overflow  = 'hidden';
    descEl.style.transition = 'max-height 0.4s ease';

    btnMore.addEventListener('click', () => {
        descEl.style.maxHeight = fullHeight + 'px';
        btnMore.style.display  = 'none';
        if (btnLess) btnLess.style.display = 'inline-flex';
    });

    if (btnLess) {
        btnLess.addEventListener('click', () => {
            descEl.style.maxHeight = COLLAPSED_HEIGHT + 'px';
            btnLess.style.display  = 'none';
            btnMore.style.display  = 'inline-flex';
        });
    }
}

// ============================================================
// PHONE REVEAL + ANALYTICS
// ============================================================
function initPhoneReveal() {
    const btnReveal  = document.getElementById('btnPhoneReveal');
    const btnCall    = document.getElementById('btnPhoneCall');
    if (!btnReveal || !btnCall) return;

    btnReveal.addEventListener('click', () => {
        btnReveal.classList.add('d-none');
        btnCall.classList.remove('d-none');
        btnCall.classList.add('animate__animated', 'animate__fadeIn');

        // Ghi analytics: phone reveal
        if (window.PostAnalyticsConfig) {
            fetch(window.PostAnalyticsConfig.endpoint + '/phone', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    _token: window.PostAnalyticsConfig.csrf,
                    post_id: window.PostAnalyticsConfig.postId,
                    type: 'phone',
                }),
            }).catch(() => {});
        }
    });

    // Ghi analytics: call
    btnCall.addEventListener('click', () => {
        if (window.PostAnalyticsConfig) {
            fetch(window.PostAnalyticsConfig.endpoint + '/call', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    _token: window.PostAnalyticsConfig.csrf,
                    post_id: window.PostAnalyticsConfig.postId,
                    type: 'call',
                }),
            }).catch(() => {});
        }
    });
}

// ============================================================
// SHARE – Mở popup + ghi analytics
// ============================================================
function initShareButtons() {
    document.querySelectorAll('[data-share-platform]').forEach(btn => {
        btn.addEventListener('click', function () {
            const platform = this.dataset.sharePlatform;
            const url      = this.dataset.shareUrl;

            if (platform === 'copy') {
                navigator.clipboard.writeText(window.PostAnalyticsConfig?.shareUrl || location.href)
                    .then(() => showToast('Đã sao chép liên kết!', 'success'))
                    .catch(() => showToast('Không thể sao chép.', 'danger'));
            } else if (url) {
                window.open(url, '_blank', 'width=600,height=500,noopener,noreferrer');
            }

            // Track analytics
            const postId = window.PostAnalyticsConfig?.postId;
            const csrf   = window.PostAnalyticsConfig?.csrf;
            if (postId && csrf) {
                fetch(location.origin + '/du-an/share/' + postId, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ _token: csrf, platform }),
                }).catch(() => {});
            }

            // Cập nhật counter UI
            const counter = document.getElementById('shareCount');
            if (counter) {
                counter.textContent = (parseInt(counter.textContent) || 0) + 1;
            }
        });
    });
}

// ============================================================
// REPORT MODAL – AJAX submit
// ============================================================
function initReportModal() {
    const form = document.getElementById('reportForm');
    if (!form) return;

    const evidenceInput = document.getElementById('reportEvidence');
    const evidencePreview = document.getElementById('reportEvidencePreview');
    evidenceInput?.addEventListener('change', function () {
        if (!evidencePreview) return;
        evidencePreview.innerHTML = '';
        const files = Array.from(this.files || []);
        if (files.length > 3) {
            this.value = '';
            evidencePreview.innerHTML = '<div class="text-danger small">Chỉ được chọn tối đa 3 ảnh.</div>';
            return;
        }
        for (const file of files) {
            if (file.size > 5 * 1024 * 1024 || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                this.value = '';
                evidencePreview.innerHTML = '<div class="text-danger small">Ảnh phải là JPG, PNG hoặc WebP và không quá 5 MB.</div>';
                return;
            }
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = 'Ảnh minh chứng đã chọn';
            img.className = 'rounded border object-fit-cover';
            img.style.width = '72px';
            img.style.height = '72px';
            img.addEventListener('load', () => URL.revokeObjectURL(img.src), { once: true });
            evidencePreview.appendChild(img);
        }
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const btn       = form.querySelector('[type="submit"]');
        const feedback  = document.getElementById('reportFeedback');
        const formData  = new FormData(form);

        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang gửi...';

        try {
            const res  = await fetch(form.action, { method: 'POST', body: formData });
            const json = await res.json();

            feedback.innerHTML = `<div class="alert alert-${json.success ? 'success' : 'danger'} mt-2 py-2">${json.message}</div>`;

            if (json.success) {
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
                    if (modal) modal.hide();
                    form.reset();
                    if (evidencePreview) evidencePreview.innerHTML = '';
                    feedback.innerHTML = '';
                }, 1800);
            }
        } catch (_) {
            feedback.innerHTML = '<div class="alert alert-danger mt-2 py-2">Có lỗi xảy ra. Vui lòng thử lại.</div>';
        } finally {
            btn.disabled  = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Gửi báo cáo';
        }
    });
}

// ============================================================
// SO SÁNH BĐS – Local Storage (tối đa 3 tin)
// ============================================================
function initCompare() {
    const btn = document.getElementById('btnCompare');
    if (!btn) return;

    const postId   = btn.dataset.postId;
    const MAX      = 3;
    const STORE_KEY = 'bds_compare';

    function getList()   { try { return JSON.parse(localStorage.getItem(STORE_KEY)) || []; } catch { return []; } }
    function saveList(l) { localStorage.setItem(STORE_KEY, JSON.stringify(l)); }

    function updateBtn() {
        const list     = getList();
        const inList   = list.includes(postId);
        btn.classList.toggle('btn-warning',   inList);
        btn.classList.toggle('btn-outline-warning', !inList);
        btn.innerHTML  = inList
            ? '<i class="fa-solid fa-check me-1"></i>Đã thêm so sánh'
            : '<i class="fa-solid fa-scale-balanced me-1"></i>So sánh BĐS';
    }

    updateBtn();

    btn.addEventListener('click', () => {
        const list   = getList();
        const index  = list.indexOf(postId);

        if (index === -1) {
            if (list.length >= MAX) {
                showToast(`Bạn chỉ có thể so sánh tối đa ${MAX} bất động sản.`, 'warning');
                return;
            }
            list.push(postId);
            showToast('Đã thêm vào danh sách so sánh!', 'success');
        } else {
            list.splice(index, 1);
            showToast('Đã bỏ khỏi danh sách so sánh.', 'info');
        }

        saveList(list);
        updateBtn();
    });
}

// ============================================================
// ZALO BUTTON
// ============================================================
function initZaloButton() {
    document.querySelectorAll('[data-analytics-event="zalo"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const url = this.dataset.url;
            if (url) window.open(url, '_blank', 'noopener,noreferrer');

            // Analytics
            if (window.PostAnalyticsConfig) {
                fetch(window.PostAnalyticsConfig.endpoint + '/zalo', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        _token: window.PostAnalyticsConfig.csrf,
                        post_id: window.PostAnalyticsConfig.postId,
                        type: 'zalo',
                    }),
                }).catch(() => {});
            }
        });
    });
}

// ============================================================
// TOAST NOTIFICATION
// ============================================================
function showToast(message, type = 'info') {
    const colors = {
        success: '#198754', danger: '#dc3545',
        warning: '#ffc107', info:   '#0dcaf0',
    };
    const color  = colors[type] || colors.info;
    const toast  = document.createElement('div');
    toast.style.cssText = `
        position:fixed;bottom:24px;right:24px;z-index:9999;
        background:${color};color:${type==='warning'?'#000':'#fff'};
        padding:12px 20px;border-radius:8px;
        box-shadow:0 4px 12px rgba(0,0,0,.2);
        font-size:0.9rem;font-weight:500;
        animation:slideInRight .3s ease;
        max-width:320px;word-wrap:break-word;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity .3s'; setTimeout(() => toast.remove(), 300); }, 3000);
}

// ============================================================
// INIT ON DOM READY
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    // AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 600, once: true, offset: 80 });
    }

    initSwiperGallery();
    initLightGallery();
    initDescToggle();
    initPhoneReveal();
    initShareButtons();
    initReportModal();
    initCompare();
    initZaloButton();

    // Lazy-load Google Maps on scroll/click
    const mapSection = document.getElementById('mapSection');
    if (mapSection) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const script = document.createElement('script');
                    script.src   = `https://maps.googleapis.com/maps/api/js?key=${window.GMAP_KEY || ''}&callback=initGoogleMap`;
                    script.async = true;
                    script.defer = true;
                    document.head.appendChild(script);
                    observer.unobserve(mapSection);
                }
            });
        }, { rootMargin: '200px' });
        observer.observe(mapSection);
    }
});
