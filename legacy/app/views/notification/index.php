<?php require_once '../app/views/layouts/header.php'; ?>
<link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/notifications.css?v=<?= filemtime(APP_ROOT . '/public/css/notifications.css') ?>">

<div class="container py-4 notifications-wrapper">
    <div class="row">
        <!-- Sidebar thành viên -->
        <?php require '../app/views/nguoi-dung/sidebar.php'; ?>

        <!-- Nội dung chính -->
        <main class="col-lg-9 col-md-8 col-12">
            <!-- Header tiêu đề và nút chức năng nhanh -->
            <section class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 bg-white p-3 rounded-3 shadow-sm">
                <div>
                    <h2 class="h5 fw-bold mb-1 text-dark"><i class="fa-solid fa-bell text-primary me-2"></i>Trung tâm thông báo</h2>
                    <p class="mb-0 small text-muted">
                        Bạn đang có <strong class="text-primary notif-unread-count-lbl"><?= $unread_count ?></strong> thông báo chưa đọc.
                    </p>
                </div>

                <div class="d-flex gap-2">
                    <button onclick="notifActionAll('read-all')" class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3">
                        <i class="fa-regular fa-envelope-open me-1"></i> Đọc tất cả
                    </button>
                    <button onclick="notifActionAll('clear')" class="btn btn-sm btn-outline-danger fw-bold rounded-pill px-3">
                        <i class="fa-regular fa-trash-can me-1"></i> Xóa tất cả
                    </button>
                </div>
            </section>

            <!-- Khối tìm kiếm thông báo -->
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-2.5">
                    <form method="GET" class="d-flex gap-2">
                        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                                   class="form-control border-start-0" placeholder="Tìm kiếm nội dung thông báo...">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary fw-semibold px-3">Tìm</button>
                    </form>
                </div>
            </div>

            <!-- Khối Bộ lọc phân loại -->
            <?php require '../app/views/notification/components/filter.php'; ?>

            <!-- Danh sách thông báo -->
            <div class="card notif-list-card bg-white shadow-sm rounded-3">
                <div class="card-body p-0" id="notificationListContainer">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-5">
                            <div class="fs-1 text-muted opacity-50 mb-2"><i class="fa-solid fa-folder-open"></i></div>
                            <h6 class="text-muted small">Không tìm thấy thông báo nào.</h6>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                            <?php require '../app/views/notification/components/item.php'; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Phân trang -->
                <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white border-0 py-3 text-center">
                        <nav>
                            <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
                                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                        <a class="page-link rounded-3 border-0" 
                                           href="?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&page=<?= $p ?>">
                                            <?= $p ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- SweetAlert2 & SSE Realtime Client Setup -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// 1. Kết nối SSE Realtime
const userId = <?= (int)Session::get('user_id') ?>;
// Long-lived SSE kept the PHP session locked and made profile navigation wait.
// Use lightweight visibility-aware polling on shared-hosting/PHP environments.
if (false && userId > 0) {
    const sseSource = new EventSource(`<?= URL_ROOT ?>/api/notifications/stream?uid=${userId}`);
    
    sseSource.addEventListener('notification', function(e) {
        const notif = JSON.parse(e.data);
        
        // Cập nhật Badge số lượng chưa đọc trên Header và trang chủ
        const headerBadge = document.querySelector('#notificationDropdown .badge');
        const listBadge = document.querySelector('.notif-unread-count-lbl');
        
        // Tăng số lượng chưa đọc
        let currentUnread = parseInt(headerBadge ? headerBadge.innerText : '0') || 0;
        currentUnread += 1;

        if (headerBadge) {
            headerBadge.innerText = currentUnread;
            headerBadge.style.display = 'inline-block';
        } else {
            // Nếu chưa có badge, tạo mới badge
            const bell = document.querySelector('#notificationDropdown');
            if (bell) {
                const span = document.createElement('span');
                span.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                span.style.fontSize = '0.65rem';
                span.innerText = '1';
                bell.appendChild(span);
            }
        }
        
        if (listBadge) {
            listBadge.innerText = currentUnread;
        }

        // Tự động đẩy thông báo Toast sang góc màn hình
        Swal.fire({
            title: notif.title,
            text: notif.content,
            icon: 'info',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4500,
            timerProgressBar: true
        });

        // Nếu người dùng đang đứng ở trang danh sách thông báo và lọc "Tất cả" hoặc "Chưa đọc", tự động render chèn dòng mới lên đầu
        const container = document.getElementById('notificationListContainer');
        const filterVal = '<?= $filter ?>';
        if (container && (filterVal === 'all' || filterVal === 'unread')) {
            // Xóa block trống nếu có
            const emptyBlock = container.querySelector('.text-center.py-5');
            if (emptyBlock) emptyBlock.remove();

            const row = document.createElement('div');
            row.className = 'notif-item d-flex align-items-start gap-3 unread';
            row.id = `notif-row-${notif.id}`;
            row.style.transition = 'all 0.5s ease';
            row.style.transform = 'translateY(-20px)';
            row.style.opacity = '0';
            
            const iconClass = 'notif-type-' + notif.type;
            const linkUrl = notif.url ? `<?= URL_ROOT ?>/nguoi-dung/notifications/${notif.id}` : `<?= URL_ROOT ?>/nguoi-dung/notifications/${notif.id}`;
            
            row.innerHTML = `
                <div class="notif-icon-box ${iconClass}"><i class="fa-solid ${notif.icon || 'fa-bell'}"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <h6 class="fw-bold mb-1 text-dark text-truncate">
                            <a href="${linkUrl}" class="text-decoration-none text-dark">${notif.title}</a>
                            <span class="notif-dot ms-1 notif-pulse"></span>
                        </h6>
                        <div class="dropdown opacity-75">
                            <button class="btn btn-link btn-sm text-muted p-0 border-0" type="button" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-1">
                                <li><button onclick="notifAction(${notif.id}, 'read')" class="dropdown-item py-1.5 small fw-semibold text-primary"><i class="fa-regular fa-envelope-open me-2"></i> Đánh dấu đã đọc</button></li>
                                <li><button onclick="notifAction(${notif.id}, 'delete')" class="dropdown-item py-1.5 small fw-semibold text-danger"><i class="fa-regular fa-trash-can me-2"></i> Xóa thông báo</button></li>
                            </ul>
                        </div>
                    </div>
                    <p class="text-secondary small mb-1 text-wrap text-break">${notif.content}</p>
                    <span class="text-muted fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.2px;"><i class="fa-regular fa-clock me-1"></i> Vừa xong</span>
                </div>
            `;
            container.insertBefore(row, container.firstChild);
            
            // Trigger animation
            setTimeout(() => {
                row.style.transform = 'translateY(0)';
                row.style.opacity = '1';
            }, 50);
        }
    });
}

let lastUnreadCount = <?= (int)($unread_count ?? 0) ?>;
async function pollUnreadNotifications() {
    if (document.hidden || userId <= 0) return;
    try {
        const response = await fetch('<?= URL_ROOT ?>/api/notifications/unread-count', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store'
        });
        if (!response.ok) return;
        const data = await response.json();
        const count = Number(data.unread_count || 0);
        document.querySelectorAll('.notif-unread-count-lbl').forEach(el => el.textContent = count);
        const headerBadge = document.querySelector('#notificationDropdown .badge');
        if (headerBadge) { headerBadge.textContent = count; headerBadge.style.display = count ? '' : 'none'; }
        if (count > lastUnreadCount) lastUnreadCount = count;
    } catch (_) {}
}
const notificationPollTimer = setInterval(pollUnreadNotifications, 20000);
window.addEventListener('pagehide', () => clearInterval(notificationPollTimer), { once: true });

// 2. Các hành động xử lý đơn lẻ (đọc, xóa)
function notifAction(id, action) {
    const url = action === 'read' 
        ? `<?= URL_ROOT ?>/nguoi-dung/notifications/read/${id}` 
        : `<?= URL_ROOT ?>/nguoi-dung/notifications/${id}`;
        
    const method = action === 'read' ? 'POST' : 'DELETE';

    fetch(url, {
        method: method,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Cập nhật số chưa đọc ở label
            const label = document.querySelector('.notif-unread-count-lbl');
            if (label) label.innerText = data.unread_count;

            const headerBadge = document.querySelector('#notificationDropdown .badge');
            if (headerBadge) {
                if (data.unread_count > 0) {
                    headerBadge.innerText = data.unread_count;
                } else {
                    headerBadge.style.display = 'none';
                }
            }

            const row = document.getElementById(`notif-row-${id}`);
            if (action === 'read') {
                if (row) {
                    row.classList.remove('unread');
                    const dot = row.querySelector('.notif-dot');
                    if (dot) dot.remove();
                    
                    // Ẩn nút "Đánh dấu đã đọc" trong dropdown
                    const readBtn = row.querySelector('button[onclick*="read"]');
                    if (readBtn) readBtn.closest('li').remove();
                }
            } else {
                // Xóa bỏ khỏi DOM
                if (row) {
                    row.style.opacity = '0';
                    row.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        row.remove();
                        if (document.querySelectorAll('.notif-item').length === 0) {
                            location.reload();
                        }
                    }, 350);
                }
            }
        }
    })
    .catch(err => console.error(err));
}

// 3. Đọc tất cả hoặc Xóa tất cả
function notifActionAll(action) {
    const isClear = action === 'clear';
    const text = isClear 
        ? 'Bạn có chắc chắn muốn XÓA TOÀN BỘ thông báo không? Hành động này không thể hoàn tác!' 
        : 'Bạn muốn đánh dấu ĐÃ ĐỌC tất cả thông báo chứ?';
        
    if (!confirm(text)) return;

    const url = isClear 
        ? '<?= URL_ROOT ?>/nguoi-dung/notifications/clear' 
        : '<?= URL_ROOT ?>/nguoi-dung/notifications/read-all';
        
    const method = isClear ? 'DELETE' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(err => console.error(err));
}
</script>
<?php require_once '../app/views/layouts/footer.php'; ?>
