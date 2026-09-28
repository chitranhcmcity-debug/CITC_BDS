@include('layouts.header')

<style>
    body { background-color: #f6f5f4; }
    
    /* Cover Image with linear gradient overlay */
    .author-cover {
        background-image: linear-gradient(180deg, rgba(10, 21, 48, 0.15) 0%, rgba(10, 21, 48, 0.75) 100%), url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1600&q=80');
        background-position: center 35%;
        background-repeat: no-repeat;
        background-size: cover;
        height: 280px;
        position: relative;
        border-radius: 16px 16px 0 0;
        overflow: hidden;
    }
    
    /* Elegant radial accent inside cover */
    .cover-accent-gradient {
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(86, 69, 212, 0.3) 0%, rgba(86, 69, 212, 0) 70%);
        filter: blur(50px);
        pointer-events: none;
    }
    
    .cover-tag {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(10, 21, 48, 0.6);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #ffffff;
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        z-index: 5;
    }
    
    /* Profile Wrapper Card */
    .author-profile {
        background: #ffffff;
        padding: 35px 40px;
        position: relative;
        border-radius: 0 0 16px 16px;
        box-shadow: 0 10px 30px rgba(10, 21, 48, 0.04);
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 30px;
        border: 1px solid var(--notion-hairline);
        border-top: none;
    }

    /* Beautiful Avatar with double borders and hover animations */
    .author-avatar-wrap {
        width: 145px;
        height: 145px;
        border-radius: 50%;
        background: linear-gradient(135deg, #5645d4, #1aae39, #dd5b00);
        padding: 4px;
        box-shadow: 0 8px 24px rgba(86, 69, 212, 0.25);
        margin-top: -95px; /* Overlap cover */
        flex-shrink: 0;
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.4s ease;
        position: relative;
        z-index: 2;
    }
    
    .author-avatar-inner {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: #ffffff;
        overflow: hidden;
        border: 3px solid #ffffff;
    }
    
    .author-avatar-inner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .author-avatar-wrap:hover {
        transform: translateY(-8px) scale(1.03);
        box-shadow: 0 16px 36px rgba(86, 69, 212, 0.4);
    }
    
    .author-avatar-wrap:hover .author-avatar-inner img {
        transform: scale(1.1);
    }
    
    /* Info area adjustments */
    .author-info {
        flex-grow: 1;
        min-width: 250px;
    }
    
    .author-name-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 6px;
    }
    
    .author-name {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--bs-body-color);
        letter-spacing: -0.5px;
        margin: 0;
    }
    
    .author-desc {
        font-size: 0.95rem;
        color: var(--notion-text-slate);
        margin-bottom: 12px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .author-phone {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--bs-warning);
        margin-bottom: 15px;
    }
    
    /* Trust Metrics block */
    .author-stats {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .stat-item {
        background: var(--notion-surface);
        border: 1px solid var(--notion-hairline);
        border-radius: 12px;
        padding: 12px 24px;
        min-width: 110px;
        transition: all 0.3s ease;
    }
    
    .stat-item:hover {
        transform: translateY(-3px);
        border-color: var(--bs-primary);
        box-shadow: 0 6px 16px rgba(86, 69, 212, 0.08);
    }
    
    .stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--bs-primary);
    }
    
    .stat-label {
        font-size: 0.8rem;
        color: var(--notion-text-steel);
        margin-top: 2px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Premium Action Buttons */
    .author-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    
    .btn-action-call {
        background: var(--bs-primary);
        border-color: var(--bs-primary);
        color: #ffffff !important;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 8px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(86, 69, 212, 0.2) !important;
    }
    
    .btn-action-call:hover {
        background: #4534b3;
        border-color: #4534b3;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(86, 69, 212, 0.3) !important;
    }
    
    .btn-action-zalo {
        background: #ffffff;
        border: 1px solid var(--bs-primary);
        color: var(--bs-primary) !important;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 8px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-action-zalo:hover {
        background: rgba(86, 69, 212, 0.05);
        transform: translateY(-2px);
    }
    
    /* Responsive stacking */
    @media (max-width: 991px) {
        .author-profile {
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 30px 20px;
            gap: 20px;
        }
        
        .author-avatar-wrap {
            margin-top: -90px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .author-name-row {
            justify-content: center;
        }
        
        .author-desc {
            justify-content: center;
        }
        
        .author-stats {
            justify-content: center;
            width: 100%;
            margin-top: 10px;
        }
        
        .author-actions {
            justify-content: center;
            width: 100%;
            margin-top: 5px;
        }
    }
    
    /* Property Card styles similar to the index page */
    .property-card { border: none; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: 0.3s; height: 100%; background: #fff; text-decoration: none; display: block; color: inherit; }
    .property-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); color: inherit; }
    .card-img-wrapper { position: relative; height: 220px; }
    .card-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
    
    .owner-avatar-mini {
        position: absolute;
        bottom: -15px;
        right: 15px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 2px solid #fff;
        background: #fff;
        overflow: hidden;
    }
    .owner-avatar-mini img { width: 100%; height: 100%; object-fit: cover; }
    
    .card-body-custom { padding: 20px 15px; }
    .price-area { display: flex; align-items: center; gap: 15px; margin-bottom: 10px; }
    .price-text { font-size: 1.1rem; font-weight: 700; color: #e74c3c; }
    .area-text { font-size: 1rem; font-weight: 700; color: #e74c3c; }
    .property-title-card { font-size: 0.95rem; font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; }
    .property-location { font-size: 0.85rem; color: #6c757d; display: flex; align-items: flex-start; gap: 5px; }
    .property-location i { margin-top: 3px; }
</style>

<div class="container py-4 mb-5 mt-3">
    
    <!-- Profile Header -->
    <div class="mb-5 shadow-sm rounded-4 overflow-hidden border">
        <div class="author-cover">
            <div class="cover-accent-gradient"></div>
            <div class="cover-tag">
                <i class="fa-solid fa-house-circle-check me-1"></i>
                <?= ($author->ma_vai_tro == 1) ? 'Đối tác VIP' : 'Nhà môi giới' ?>
            </div>
        </div>
        <div class="author-profile">
            <?php 
            if ($author->ma_vai_tro == 1) {
                $avatar = URL_ROOT . '/public/images/favicon.png?v=2';
            } else {
                $avatar = !empty($author->anh_dai_dien) ? URL_ROOT . '/uploads/avatars/' . $author->anh_dai_dien : 'https://ui-avatars.com/api/?name=' . urlencode($author->ten ?? 'NguoiDung') . '&background=5645d4&color=fff&bold=true';
            }
            ?>
            <div class="author-avatar-wrap">
                <div class="author-avatar-inner">
                    <img src="<?= $avatar ?>" alt="Avatar">
                </div>
            </div>
            <div class="author-info">
                <div class="author-name-row">
                    <h1 class="author-name"><?= htmlspecialchars($author->ten) ?></h1>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-circle-check text-success"></i> Đã xác minh
                    </span>
                    <?php if ($author->ma_vai_tro == 1): ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-crown text-primary"></i> Admin
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="author-desc">
                    <i class="fa-solid fa-award text-primary"></i>
                    <span>Chuyên viên tư vấn bất động sản uy tín</span>
                </div>
                
                <div class="author-actions">
                    <?php if (!empty($author->dien_thoai)): ?>
                        <a href="tel:<?= $author->dien_thoai ?>" class="btn-action-call btn">
                            <i class="fa-solid fa-phone"></i> Gọi ngay: <?= htmlspecialchars($author->dien_thoai) ?>
                        </a>
                        <a href="https://zalo.me/<?= preg_replace('/\D/', '', $author->dien_thoai) ?>" target="_blank" class="btn-action-zalo btn">
                            <i class="fa-solid fa-comment-dots"></i> Chat qua Zalo
                        </a>
                    <?php else: ?>
                        <span class="text-muted"><i class="fa-solid fa-phone-slash me-1"></i> Chưa cập nhật số điện thoại</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="author-stats">
                <div class="stat-item text-center">
                    <div class="stat-value"><?= count($projects) ?></div>
                    <div class="stat-label">Tin đăng</div>
                </div>
                <div class="stat-item text-center">
                    <div class="stat-value">
                        <i class="fa-solid fa-star text-warning me-0.5"></i> 5.0
                    </div>
                    <div class="stat-label">Uy tín</div>
                </div>
                <div class="stat-item text-center">
                    <div class="stat-value">
                        <?= date('m/Y', strtotime($author->ngay_tao ?? '2026-01-01')) ?>
                    </div>
                    <div class="stat-label">Tham gia</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Listings Grid -->
    <h4 class="fw-bold mb-4">Danh sách tin đăng (<?= count($projects) ?>)</h4>
    
    <div class="row g-4">
        <?php if (!empty($projects)): ?>
            <?php foreach ($projects as $p): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="<?= URL_ROOT ?>/du-an/detail/<?= $p->duong_dan ?>" class="property-card border">
                        <div class="card-img-wrapper">
                            <img src="<?= URL_ROOT ?>/uploads/<?= $p->anh_thu_nho ?>" onerror="this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=500'" alt="DuAn">
                            <div class="owner-avatar-mini shadow-sm">
                                <img src="<?= $avatar ?>" alt="Avatar">
                            </div>
                        </div>
                        <div class="card-body-custom">
                            <div class="price-area">
                                <span class="price-text"><?= $p->gia ?: 'Thỏa thuận' ?></span>
                                <span class="area-text"><?= $p->dien_tich ?: '---' ?></span>
                            </div>
                            <div class="property-title-card"><?= htmlspecialchars($p->tieu_de) ?></div>
                            <div class="property-location">
                                <i class="fa-solid fa-location-dot"></i>
                                <span><?= htmlspecialchars(str_replace(',', ' -', $p->vi_tri)) ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted fs-5">Người dùng này chưa có tin đăng nào.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

@include('layouts.footer')
